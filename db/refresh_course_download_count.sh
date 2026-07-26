#!/bin/bash

# 批量刷新课件（course）下载数量 download_count 为 [10, 80] 区间随机整数
# 说明：FLOOR(10 + RAND() * 71)
#   RAND() ∈ [0, 1) => *71 ∈ [0, 71) => +10 ∈ [10, 81) => FLOOR ∈ {10..80}，两端闭区间

# 从配置文件读取数据库连接信息
config="/home/judge/etc/judge.conf"
DB_HOST=`cat $config | grep 'OJ_HOST_NAME' | awk -F= '{print $2}'`
DB_USER=`cat $config | grep 'OJ_USER_NAME' | awk -F= '{print $2}'`
DB_PASS=`cat $config | grep 'OJ_PASSWORD' | awk -F= '{print $2}'`
DB_NAME=`cat $config | grep 'OJ_DB_NAME' | awk -F= '{print $2}'`

# 解析参数：-y / --yes 跳过交互确认（用于非交互/sudo 场景）
ASSUME_YES=0
for arg in "$@"; do
    case "$arg" in
        -y|--yes) ASSUME_YES=1 ;;
        *) echo "未知参数：$arg"; exit 1 ;;
    esac
done

echo "========================================"
echo "课件下载数量批量刷新工具"
echo "========================================"
echo ""
echo "目标表：course.download_count"
echo "刷新区间：[10, 80] 随机整数"
echo "数据库：$DB_NAME"
echo ""

# 1. 查看刷新前状态
echo "正在查询刷新前状态..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
  "SELECT COUNT(*) AS total, MIN(download_count) AS min_cnt, MAX(download_count) AS max_cnt, AVG(download_count) AS avg_cnt FROM course;"

# 2. 确认是否执行
if [ "$ASSUME_YES" -eq 1 ]; then
    CONFIRM="y"
else
    read -p "确认将所有 course.download_count 刷新为 [10, 80] 随机值？(y/N): " CONFIRM
fi
if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "取消刷新操作"
    exit 0
fi

# 3. 批量刷新为 [10, 80] 区间随机整数
echo "正在批量刷新 download_count ..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
  "UPDATE course SET download_count = FLOOR(10 + RAND() * 71);"

# 4. 查看刷新后状态
echo ""
echo "刷新后状态："
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
  "SELECT COUNT(*) AS total, MIN(download_count) AS min_cnt, MAX(download_count) AS max_cnt, AVG(download_count) AS avg_cnt FROM course;"

echo ""
echo "========================================"
echo "刷新完成！"
echo "========================================"
echo "- 已将所有 course.download_count 刷新为 [10, 80] 随机值"
