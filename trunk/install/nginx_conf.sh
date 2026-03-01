##
# nginx配置修改命令：
# sudo cp /etc/nginx/sites-enabled/default /etc/nginx/default.bak
# sudo vim /etc/nginx/sites-enabled/default
# # 检查配置语法（关键！有错误会提示）
# sudo nginx -t
# # 语法无错则重启Nginx
# sudo systemctl restart nginx
# 以下为oj系统的nginx配置文件内容：/etc/nginx/sites-enabled/default
##

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