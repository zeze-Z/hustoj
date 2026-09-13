#!/bin/sh

# nginx配置部署脚本：将同目录下 sites-enabled/default 部署到 /etc/nginx/sites-enabled/default
# 配置唯一数据源 = 本脚本同目录的 sites-enabled/default，改配置请改该文件后重跑本脚本
# 使用方法: sudo sh fix_nginx_conf.sh
#
# 安全策略：
#   1. 时间戳备份（/var/backups/nginx-conf/，保留最近5份），不覆盖单一备份
#   2. 部署前预检：root 权限、nginx 已安装、业务证书齐全、PHP-FPM socket 真实存在
#   3. 临时文件 + mv 原子替换，兼容 sites-enabled/default 为软链接的原厂布局（不穿透软链）
#   4. nginx -t 失败 / reload 失败 / error.log 出现 emerg / 探活失败 均自动回滚并恢复服务
#   5. 已运行时优先 reload（零停机），仅 nginx 未运行时 start
#
# POSIX sh 兼容，可通过 sh / bash 执行

set -u

# ---------- 颜色输出 ----------
info() { printf '\033[1;33m%s\033[0m\n' "$*"; }
ok()   { printf '\033[0;32m%s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m%s\033[0m\n' "$*" >&2; }
err()  { printf '\033[0;31m%s\033[0m\n' "$*" >&2; }
die()  { err "$*"; exit 1; }

info "开始配置 Nginx..."

# ---------- 路径与全局变量 ----------
SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
SRC_CONF="$SCRIPT_DIR/sites-enabled/default"
CONF_DST="/etc/nginx/sites-enabled/default"
BACKUP_DIR="/var/backups/nginx-conf"
SSL_DIR="/etc/nginx/ssl"
ERR_LOG="/var/log/nginx/error.log"
TMP_CONF="/etc/nginx/.default.deploy.$$"
TMP_SED="/etc/nginx/.default.deploy.$$.sed"
LATEST_BACKUP=""

cleanup() {
    rm -f "$TMP_CONF" "$TMP_SED"
}
trap cleanup EXIT HUP INT TERM

# ---------- 0. 基础预检 ----------
[ "$(id -u)" -eq 0 ] || die "请使用 sudo/root 运行此脚本"

[ -f "$SRC_CONF" ] || die "未找到配置源文件：$SRC_CONF
配置文件必须与本脚本放在同一目录结构（install/nginx/sites-enabled/）下"

command -v nginx >/dev/null 2>&1 || die "未找到 nginx 命令，请确认 nginx 已安装"
[ -d /etc/nginx/sites-enabled ] || die "目录不存在：/etc/nginx/sites-enabled，请确认 nginx 已按 Debian/Ubuntu 布局安装"
rm -f /etc/nginx/sites-enabled/default.deploy.*
if ! command -v systemctl >/dev/null 2>&1; then
    warn "未找到 systemctl，服务应用环节将回退使用 nginx 原生命令"
fi

# ---------- 1. 时间戳备份（备份目录在 sites-enabled 之外，不会被 include 加载） ----------
info "备份原配置..."
if [ -f "$CONF_DST" ]; then
    mkdir -p "$BACKUP_DIR" || die "无法创建备份目录：$BACKUP_DIR"
    BK="$BACKUP_DIR/default.$(date +%Y%m%d-%H%M%S).$$"
    if [ -L "$CONF_DST" ]; then
        info "当前配置是软链接（-> $(readlink "$CONF_DST" 2>/dev/null)），备份其实际内容，部署后替换为普通文件"
    fi
    cp -f "$CONF_DST" "$BK" || die "备份失败，中止部署（线上配置未做任何改动）"
    chmod 600 "$BK" 2>/dev/null || true
    LATEST_BACKUP="$BK"
    ok "原配置已备份到 $BK"
elif [ -L "$CONF_DST" ]; then
    warn "现有软链接已损坏（-> $(readlink "$CONF_DST" 2>/dev/null)），无有效内容可备份，将直接替换"
else
    warn "原配置文件不存在，跳过备份（新机首部署）"
fi

# ---------- 2. 自签名证书（default_server 的 443 握手占位，IP 访问回 403 用） ----------
info "检查自签名证书..."
mkdir -p "$SSL_DIR" || die "无法创建目录：$SSL_DIR"
gen_self_signed() {
    command -v openssl >/dev/null 2>&1 || die "未找到 openssl，无法生成自签名证书"
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$SSL_DIR/default.key" -out "$SSL_DIR/default.crt" \
        -subj "/CN=default" >/dev/null 2>&1 || die "自签名证书生成失败"
    chmod 600 "$SSL_DIR/default.key"   # 私钥按惯例收紧（openssl 各版本默认权限不一致）
    chmod 644 "$SSL_DIR/default.crt"
}
if [ -f "$SSL_DIR/default.crt" ] && [ -f "$SSL_DIR/default.key" ]; then
    if openssl x509 -checkend 0 -noout -in "$SSL_DIR/default.crt" >/dev/null 2>&1; then
        ok "自签名证书已存在且未过期，跳过生成"
    else
        info "自签名证书已过期，重新生成..."
        gen_self_signed
        ok "自签名证书已重新生成"
    fi
else
    gen_self_signed
    ok "自签名证书已生成"
fi

# ---------- 3. 业务证书预检：源配置引用的所有 ssl_certificate/key 必须已就位 ----------
MISSING_CERT=""
for cert in $(grep -E '^[[:space:]]*ssl_certificate(_key)?[[:space:]]' "$SRC_CONF" \
              | sed -e 's/^[[:space:]]*//' -e 's/;.*$//' | awk '{print $2}'); do
    if [ ! -f "$cert" ]; then
        MISSING_CERT="$MISSING_CERT
  $cert"
    fi
done
[ -z "$MISSING_CERT" ] || die "以下证书文件缺失，nginx -t 必然失败，请先放置证书后再运行：$MISSING_CERT"

# ---------- 4. 检测运行中的 PHP-FPM socket（必须真实存在，杜绝"绿灯成功实则全站 502"） ----------
info "检测 PHP-FPM socket..."
PHP_SOCK=""
# 4.1 版本特定 socket（php8.1-fpm.sock 等）；多版本共存时优先 systemd active 的那个
for s in /var/run/php/php[0-9]*-fpm.sock; do
    [ -S "$s" ] || continue
    [ -z "$PHP_SOCK" ] && PHP_SOCK="$s"
    ver=$(basename "$s" | sed 's/^php\(.*\)-fpm\.sock$/\1/')
    if command -v systemctl >/dev/null 2>&1 && systemctl is-active --quiet "php$ver-fpm" 2>/dev/null; then
        PHP_SOCK="$s"
        break
    fi
done
# 4.2 通用软链接（部分发行版 php-fpm.sock -> phpX.Y-fpm.sock）
if [ -z "$PHP_SOCK" ] && [ -S /var/run/php/php-fpm.sock ]; then
    PHP_SOCK="/var/run/php/php-fpm.sock"
fi
# 4.3 apt 源探测版本：仅在对应 socket 真实存在时采纳，绝不凭包名猜测写入
if [ -z "$PHP_SOCK" ] && command -v apt-cache >/dev/null 2>&1; then
    PHP_VER=$(apt-cache search php-fpm 2>/dev/null | grep -oe '[0-9]\.[0-9]' | head -n 1)
    if [ -n "$PHP_VER" ] && [ -S "/var/run/php/php$PHP_VER-fpm.sock" ]; then
        PHP_SOCK="/var/run/php/php$PHP_VER-fpm.sock"
    fi
fi
[ -n "$PHP_SOCK" ] || die "未检测到运行中的 PHP-FPM socket（/var/run/php/php*-fpm.sock）。
请先安装并启动 php-fpm（如：apt install php-fpm && systemctl start php8.1-fpm）后再运行本脚本。"
ok "将使用 PHP-FPM socket: $PHP_SOCK"

# ---------- 4.5 旧版 nginx（如 Ubuntu22.04 自带 1.18）mime.types 缺 woff2，
#           缺失时 woff2 以 application/octet-stream 返回，部分浏览器拒绝加载 Web 字体 ----------
MIME_TYPES="/etc/nginx/mime.types"
if [ -f "$MIME_TYPES" ] && ! grep -q 'woff2' "$MIME_TYPES"; then
    info "mime.types 缺少 woff2 映射，补充 font/woff2 ..."
    awk '/^\}/&&!d{print "    font/woff2                                woff2;";d=1} {print}' \
        "$MIME_TYPES" > "$MIME_TYPES.tmp" && mv -f "$MIME_TYPES.tmp" "$MIME_TYPES" \
        || die "mime.types 补充 woff2 失败"
    ok "已补充 font/woff2 MIME 映射"
fi

# ---------- 5. 原子部署：先写临时文件并完成全部改写/校验，最后 mv 一次性替换 ----------
info "部署nginx配置（$SRC_CONF → $CONF_DST）..."
cp -f "$SRC_CONF" "$TMP_CONF" || die "写入临时配置失败：$TMP_CONF"
chmod 644 "$TMP_CONF" || die "设置临时配置权限失败"

# 配置模板中 php7.4 为占位符，替换为实际运行版本
ACTUAL_SOCK=$(basename "$PHP_SOCK")
if [ "$ACTUAL_SOCK" != "php7.4-fpm.sock" ]; then
    sed "s|php7.4-fpm.sock|$ACTUAL_SOCK|g" "$TMP_CONF" > "$TMP_SED" || die "fastcgi_pass 替换失败"
    mv -f "$TMP_SED" "$TMP_CONF" || die "fastcgi_pass 替换落盘失败"
    ok "已将占位符 php7.4-fpm.sock 替换为 $ACTUAL_SOCK"
fi

# 部署后断言：最终配置里的 fastcgi socket 必须真实可连，否则中止（此时线上配置尚未改动）
DEPLOY_SOCK=$(sed -n 's/.*fastcgi_pass[[:space:]]*unix:\([^;]*\);.*/\1/p' "$TMP_CONF" | head -n 1)
[ -S "$DEPLOY_SOCK" ] || die "待部署配置引用的 PHP-FPM socket 不存在：$DEPLOY_SOCK
中止部署以防上线后所有 PHP 页面 502，请检查 PHP-FPM 监听方式。"

# mv 替换软链接本身（不穿透），同文件系统内原子生效
mv -f "$TMP_CONF" "$CONF_DST" || die "配置替换失败：$CONF_DST"
ok "nginx配置已写入"

# ---------- 6. limit_conn 依赖提醒（只统计非注释行，避免注释里出现关键字而误判） ----------
if ! grep -v '^[[:space:]]*#' /etc/nginx/nginx.conf 2>/dev/null | grep -q 'limit_conn_zone'; then
    warn "警告：/etc/nginx/nginx.conf 未定义 limit_conn_zone，nginx -t 将报 unknown zone"
    warn "请参考本目录 nginx.conf 的 http 块补充："
    warn "  limit_conn_zone \$binary_remote_addr zone=perip:10m;"
    warn "  limit_conn_zone \$server_name zone=perserver:10m;"
fi

# ---------- 回滚函数：恢复最近备份，并用旧配置尽力恢复在线服务 ----------
rollback() {
    err "部署失败，正在回滚..."
    if [ -n "$LATEST_BACKUP" ] && [ -f "$LATEST_BACKUP" ]; then
        if cp -f "$LATEST_BACKUP" "$TMP_CONF" 2>/dev/null && chmod 644 "$TMP_CONF" 2>/dev/null && mv -f "$TMP_CONF" "$CONF_DST"; then
            ok "已恢复备份配置：$LATEST_BACKUP"
        else
            cp -f "$LATEST_BACKUP" "$CONF_DST" 2>/dev/null && chmod 644 "$CONF_DST" 2>/dev/null
            ok "已直接恢复备份配置：$LATEST_BACKUP"
        fi
    else
        rm -f "$CONF_DST"
        warn "无备份可恢复，已移除刚写入的配置文件"
    fi
    # 用回滚后的配置恢复服务；先 nginx -t 把关，避免坏备份雪上加霜
    if nginx -t >/dev/null 2>&1; then
        if command -v systemctl >/dev/null 2>&1; then
            systemctl reload nginx >/dev/null 2>&1 || systemctl restart nginx >/dev/null 2>&1 \
                || nginx -s reload >/dev/null 2>&1 \
                || warn "服务未能自动恢复，请人工检查：systemctl status nginx / journalctl -u nginx"
        else
            nginx -s reload >/dev/null 2>&1 || warn "服务未能自动恢复，请人工检查 nginx 进程"
        fi
    else
        warn "回滚后的配置仍未通过 nginx -t，请人工检查！（reload 未发生时运行中的 worker 不受影响）"
    fi
    exit 1
}

# ---------- 7. 语法检查（失败自动回滚） ----------
info "检查nginx配置语法..."
if nginx -t; then
    ok "配置语法检查通过！"
else
    rollback
fi

# ---------- 8. 零停机应用：已运行则 reload，未运行则 start ----------
# 记录 error.log 位置，reload 后只检查新增日志，用于发现 nginx -t 测不出的 bind 类失败
# （如 IPv6 被禁用时 listen [::]: 失败，master 会保留旧配置且 systemctl reload 仍返回 0）
LOG_POS=0
[ -r "$ERR_LOG" ] && LOG_POS=$(wc -c < "$ERR_LOG")

info "应用新配置..."
NGINX_WAS_ACTIVE=0
if command -v systemctl >/dev/null 2>&1 && systemctl is-active --quiet nginx 2>/dev/null; then
    NGINX_WAS_ACTIVE=1
    if ! systemctl reload nginx; then
        err "reload 失败（可能是端口被占用/bind 冲突），旧 worker 仍在运行"
        rollback
    fi
    ok "nginx 已 reload（零停机）"
else
    warn "nginx 当前未运行，执行 start..."
    if command -v systemctl >/dev/null 2>&1; then
        systemctl start nginx || rollback
    else
        nginx || rollback
    fi
    ok "nginx 已启动"
fi

# ---------- 9. 生效校验：新增日志无 emerg + 本地 HTTPS 探活 ----------
sleep 1
if [ "$NGINX_WAS_ACTIVE" -eq 1 ] && [ -r "$ERR_LOG" ]; then
    if tail -c "+$((LOG_POS + 1))" "$ERR_LOG" 2>/dev/null | grep -q '\[emerg\]'; then
        err "reload 后 error.log 出现 emerg，新配置可能未真正生效（旧 worker 仍在服务）"
        tail -c "+$((LOG_POS + 1))" "$ERR_LOG" | grep '\[emerg\]' | tail -n 3 | sed 's/^/  /' >&2
        rollback
    fi
fi

probe_nginx() {
    # 能收到任意 HTTP 状态码即说明 worker 正常响应（default_server 对 IP 直访回 403 属预期）
    if command -v curl >/dev/null 2>&1; then
        code=$(curl -k -s -o /dev/null --max-time 5 -w '%{http_code}' https://127.0.0.1/ 2>/dev/null || echo 000)
        case "$code" in
            [1-5]??) return 0 ;;
            *) return 1 ;;
        esac
    fi
    # 无 curl 时退化为服务状态判断
    if command -v systemctl >/dev/null 2>&1; then
        systemctl is-active --quiet nginx
    else
        return 0
    fi
}
info "部署后探活..."
if probe_nginx; then
    ok "探活通过，nginx 正常响应"
else
    err "探活失败：应用新配置后 nginx 无 HTTP 响应"
    rollback
fi

# ---------- 10. 备份轮转：仅保留最近 5 份（成功部署后才执行） ----------
if [ -d "$BACKUP_DIR" ]; then
    ls -1t "$BACKUP_DIR"/default.* 2>/dev/null | tail -n +6 | while IFS= read -r old_bak; do
        rm -f "$old_bak"
    done
    ok "旧备份已轮转（保留最近5份，目录：$BACKUP_DIR）"
fi

ok "========================================"
ok "Nginx配置完成！"
ok "========================================"
