#!/bin/sh
# 先安装依赖环境
sudo apt-get -y install libmysqlclient-dev

sudo apt-get -y install debhelper build-essential:native libmysqlclient-dev libmysql++-dev libmariadb-dev-compat devscripts debhelper
set -ex \
&& git clone https://github.com/zeze-Z/hustoj.git \
&& git clone https://github.com/zeze-Z/hustoj-deb-ubuntu.git \
&& mv hustoj/trunk/* hustoj-deb-ubuntu \
&& cd hustoj-deb-ubuntu \
&& echo "=== 查看 debian/rules 文件内容 ===" \
&& cat debian/rules \
&& echo "=== 查看当前目录结构 ===" \
&& ls -la \
&& echo "=== 开始构建 deb 包 ===" \
&& dpkg-buildpackage
cd ..
#sudo dpkg -i *.deb || sudo apt-get install -f -y
exit 0
