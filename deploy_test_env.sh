#!/bin/bash
# deploy_test_env.sh —— 上传指定文件到 web-2204 测试虚机并刷新 PHP 缓存
# 跨平台: Windows Git Bash / macOS 通用; 仓库根自动探测(脚本放仓库根目录)
# 用法: bash deploy_test_env.sh [-f] <trunk/web/下的文件...>   (可多个, 支持绝对路径)
#       -f = 追加重启 php8.1-fpm, 模板/页面内容改动清 APCu 缓存时用
# 环境变量可覆盖默认值: REPO VM SUDOPW WEBROOT FPM
# 示例: bash deploy_test_env.sh -f trunk/web/news.php trunk/web/admin/news_list.php

REPO="${REPO:-$(cd "$(dirname "$BASH_SOURCE")" && pwd)}"   # 仓库根 = 脚本所在目录
VM="${VM:-web-2204}"
SUDOPW="${SUDOPW:-judge}"
WEBROOT="${WEBROOT:-/home/judge/src/web}"
FPM="${FPM:-php8.1-fpm}"

# Windows Git Bash 下必须禁用 MSYS 参数路径转换(macOS 下无害):
# 1) exec 的 /tmp/... /home/... 会被转成 C:/Users/..., VM 内 mv/stat 找不到文件
# 2) transfer 的 /d/... 源会被转成 D:/..., 盘符冒号被 multipass 当实例名报错
#    (源路径下面统一用相对形式规避)
export MSYS2_ARG_CONV_EXCL="*" MSYS_NO_PATHCONV=1

RESTART_FPM=0
[[ "$1" == "-f" ]] && { RESTART_FPM=1; shift; }
[[ $# -eq 0 ]] && { echo "用法: bash deploy_test_env.sh [-f] <trunk/web/下的文件...>"; exit 1; }

fail=0
for f in "$@"; do
  # 路径归一化: 反斜杠转正斜杠; D:/x 或 D:\x → /d/x; 其余视为仓库相对路径
  p="${f//\\//}"
  case "$p" in
    /*) ;;
    [a-zA-Z]:/*) drive=$(printf '%s' "${p:0:1}" | tr '[:upper:]' '[:lower:]'); p="/$drive${p:2}" ;;
    *) p="$REPO/$p" ;;
  esac

  if [[ ! -f "$p" ]]; then echo "!! 文件不存在: $f"; fail=1; continue; fi

  rel="${p#"$REPO/trunk/web/"}"    # 相对 web 根的目标路径
  if [[ "$rel" == "$p" ]]; then echo "!! 不在 trunk/web/ 下，跳过: $f"; fail=1; continue; fi

  echo ">> 部署 $rel"
  dir=$(dirname "$p"); base=$(basename "$p")
  localsize=$(wc -c < "$p" | tr -d '[:space:]')
  ( cd "$dir" && multipass transfer "$base" "$VM:/tmp/_deploy_" ) \
    && multipass exec "$VM" -- sudo -S mkdir -p "$(dirname "$WEBROOT/$rel")" <<< "$SUDOPW" \
    && multipass exec "$VM" -- sudo -S mv /tmp/_deploy_ "$WEBROOT/$rel" <<< "$SUDOPW" \
    && multipass exec "$VM" -- sudo -S chown www-data:www-data "$WEBROOT/$rel" <<< "$SUDOPW" \
    || { echo "!! 上传失败: $f"; fail=1; continue; }
  # 校验远端字节数(防 multipass transfer 0 字节坑); 文件属主 www-data, 校验也须 sudo
  remotesize=$(multipass exec "$VM" -- sudo -S stat -c %s "$WEBROOT/$rel" 2>/dev/null <<< "$SUDOPW")
  if [[ "$remotesize" != "$localsize" ]]; then
    echo "!! 大小不一致: 本地 $localsize / 远端 ${remotesize:-?} : $f"; fail=1
  fi
done

echo ">> opcache_reset"
multipass exec "$VM" -- sudo -S php -r 'opcache_reset();' <<< "$SUDOPW"

if [[ "$RESTART_FPM" == 1 ]]; then
  echo ">> 重启 $FPM (清 APCu)"
  multipass exec "$VM" -- sudo -S systemctl restart "$FPM" <<< "$SUDOPW" \
    || echo "!! fpm 重启失败(检查服务名, 可用 FPM=xxx 环境变量覆盖)"
fi

exit $fail
