#!/bin/bash

# nginx配置修改脚本
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

# 1. 备份原配置
echo -e "${YELLOW}备份原配置...${NC}"
if [ -f /etc/nginx/sites-enabled/default ]; then
    cp /etc/nginx/sites-enabled/default /etc/nginx/default.bak
    echo -e "${GREEN}原配置已备份到 /etc/nginx/default.bak${NC}"
else
    echo -e "${YELLOW}原配置文件不存在，跳过备份${NC}"
fi

# 2. 写入新的nginx配置
echo -e "${YELLOW}写入新的nginx配置...${NC}"
cat > /etc/nginx/sites-enabled/default << 'EOF'
# 1. 核心：所有IP访问/未绑定域名访问 → 自动跳转到你的域名（HTTPS）
server {
	listen 80 default_server;
	listen [::]:80 default_server;
	server_name _;
	# 跳转到HTTPS的域名（替换成你的实际域名）
	return 301 https://aioj.top$request_uri;
}

# 2. 域名的HTTP访问 → 强制跳转到HTTPS
server {
	listen 80;
	listen [::]:80;
	server_name aioj.top;  # 替换成你的实际域名
	return 301 https://$host$request_uri;
}

# 3. HTTPS主配置（443端口）- 保留所有HUSTOJ原有配置
server {
	listen 443 ssl http2 default_server;
	listen [::]:443 ssl http2 default_server;

	# 你的域名
	server_name aioj.top;  # 替换成你的实际域名

	# 腾讯云SSL证书路径（替换成你证书的实际路径）
	ssl_certificate /etc/nginx/ssl/aioj.crt;        # 证书文件
	ssl_certificate_key /etc/nginx/ssl/aioj.key;    # 私钥文件

	# HTTPS安全配置
	ssl_protocols TLSv1.2 TLSv1.3;
	ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;
	ssl_prefer_server_ciphers off;
	ssl_session_cache shared:SSL:10m;
	ssl_session_timeout 10m;

	# 原HUSTOJ配置 - 完全保留
	root /home/judge/src/web;
	index loginpage.php index.php index.htm index.nginx-debian.html;

	location / {
		try_files $uri $uri/ =404;
		limit_conn perip 10;    # 单个客户端IP连接数限制
		limit_conn perserver 100; # 服务器总连接数限制
	}

	# PHP解析配置 - 完全保留
	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
		limit_conn perip 10;    # 单个客户端IP连接数限制
		limit_conn perserver 100; # 服务器总连接数限制
	}

	# 禁止访问.htaccess文件
	location ~ /\.ht {
		deny all;
	}
}
EOF

echo -e "${GREEN}nginx配置已写入${NC}"

# 3. 检查配置语法
echo -e "${YELLOW}检查nginx配置语法...${NC}"
if nginx -t; then
    echo -e "${GREEN}配置语法检查通过！${NC}"
else
    echo -e "${RED}配置语法错误！请检查配置。已备份的原配置位于 /etc/nginx/default.bak${NC}"
    exit 1
fi

# 4. 重启nginx
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
