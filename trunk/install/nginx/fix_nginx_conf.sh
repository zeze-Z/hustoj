#!/bin/bash

# nginx配置部署脚本：将同目录下 sites-enabled/default 拷贝到 /etc/nginx/sites-enabled/default
# 配置唯一数据源 = 本脚本同目录的 sites-enabled/default，改配置请改该文件后重跑本脚本
# 使用方法: sudo bash fix_nginx_conf.sh

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}开始配置 Nginx...${NC}"

# 检查是否以root权限运行
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}请使用 sudo 运行此脚本${NC}"
    exit 1
fi

# 定位配置源文件（与本脚本同目录的 sites-enabled/default）
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
SRC_CONF="$SCRIPT_DIR/sites-enabled/default"
CONF_DST="/etc/nginx/sites-enabled/default"

if [ ! -f "$SRC_CONF" ]; then
    echo -e "${RED}未找到配置源文件：$SRC_CONF${NC}"
    echo -e "${RED}配置文件必须与本脚本放在同一目录结构（install/nginx/sites-enabled/）下${NC}"
    exit 1
fi

# 1. 备份原配置
echo -e "${YELLOW}备份原配置...${NC}"
if [ -f "$CONF_DST" ]; then
    cp "$CONF_DST" /etc/nginx/default.bak
    echo -e "${GREEN}原配置已备份到 /etc/nginx/default.bak${NC}"
else
    echo -e "${YELLOW}原配置文件不存在，跳过备份${NC}"
fi

# 2. 生成自签名证书（用于IP访问的443端口）
echo -e "${YELLOW}生成自签名证书...${NC}"
mkdir -p /etc/nginx/ssl
if [ ! -f /etc/nginx/ssl/default.crt ]; then
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/nginx/ssl/default.key -out /etc/nginx/ssl/default.crt -subj "/CN=default" 2>/dev/null
    chmod 600 /etc/nginx/ssl/default.key   # 私钥按惯例收紧（openssl 各版本默认权限不一致）
    echo -e "${GREEN}自签名证书已生成${NC}"
else
    echo -e "${YELLOW}自签名证书已存在，跳过生成${NC}"
fi

# 3. 部署新配置（拷贝同目录快照，不在脚本内嵌配置，避免双份维护漂移）
echo -e "${YELLOW}部署nginx配置（$SRC_CONF → $CONF_DST）...${NC}"
cp "$SRC_CONF" "$CONF_DST"
chmod 644 "$CONF_DST"   # 权限确定性兜底（cp 新建文件时继承源权限，显式归一 644 root:root）
echo -e "${GREEN}nginx配置已写入${NC}"

# 前置提醒：配置里的 limit_conn 依赖 http 级 limit_conn_zone 定义（参考本目录 nginx.conf）
if ! grep -q "limit_conn_zone" /etc/nginx/nginx.conf 2>/dev/null; then
    echo -e "${YELLOW}警告：/etc/nginx/nginx.conf 未定义 limit_conn_zone，nginx -t 可能报 unknown zone；请参考本目录 nginx.conf 的 http 块补充${NC}"
fi

# 4. 检查配置语法（失败自动回滚，避免坏配置残留导致下次重启 nginx 后站点不可用）
echo -e "${YELLOW}检查nginx配置语法...${NC}"
if nginx -t; then
    echo -e "${GREEN}配置语法检查通过！${NC}"
else
    echo -e "${RED}配置语法错误！正在回滚...${NC}"
    if [ -f /etc/nginx/default.bak ]; then
        cp /etc/nginx/default.bak "$CONF_DST"
        chmod 644 "$CONF_DST"
        echo -e "${YELLOW}已恢复备份配置${NC}"
    else
        rm -f "$CONF_DST"
        echo -e "${YELLOW}无备份可恢复，已移除刚写入的配置文件${NC}"
    fi
    exit 1
fi

# 5. 重启nginx
echo -e "${YELLOW}重启nginx...${NC}"
if systemctl restart nginx; then
    echo -e "${GREEN}nginx重启成功！${NC}"
else
    echo -e "${RED}nginx重启失败！请检查错误日志${NC}"
    exit 1
fi

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Nginx配置完成！${NC}"
echo -e "${GREEN}========================================${NC}"
