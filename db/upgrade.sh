#!/bin/bash
# HUSTOJ 数据库升级脚本 - Shell包装

cd "$(dirname "$0")"
php upgrade.php "$@"