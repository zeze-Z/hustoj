#!/bin/bash

# 清理zezhang账号的测试记录脚本

# 从配置文件读取数据库连接信息
config="/home/judge/etc/judge.conf"
DB_HOST=`cat $config|grep 'OJ_HOST_NAME' |awk -F= '{print $2}'`
DB_USER=`cat $config|grep 'OJ_USER_NAME' |awk -F= '{print $2}'`
DB_PASS=`cat $config|grep 'OJ_PASSWORD' |awk -F= '{print $2}'`
DB_NAME=`cat $config|grep 'OJ_DB_NAME' |awk -F= '{print $2}'`

# 要清理的用户名
USER_ID="zezhang"

echo "开始清理 $USER_ID 的测试记录..."

# 1. 查看待清理的记录内容
echo "正在查询待清理的记录..."

echo "\n===== solution表记录 ====="
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT solution_id, user_id, problem_id, language, result, ip, in_date FROM solution WHERE user_id = '$USER_ID' ORDER BY in_date DESC;"

echo "\n===== source_code表记录 ====="
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT sc.solution_id, s.user_id, SUBSTRING(sc.source, 1, 150) as source_preview FROM source_code sc JOIN solution s ON sc.solution_id = s.solution_id WHERE s.user_id = '$USER_ID' ORDER BY s.in_date DESC;"

# 统计记录数量
SOLUTION_COUNT=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COUNT(*) FROM solution WHERE user_id = '$USER_ID';" | tail -1)
SOURCE_COUNT=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COUNT(*) FROM source_code WHERE solution_id IN (SELECT solution_id FROM solution WHERE user_id = '$USER_ID');" | tail -1)

echo "\n待清理的记录总数："
echo "- solution表：$SOLUTION_COUNT 条记录"
echo "- source_code表：$SOURCE_COUNT 条记录"

# 2. 确认是否执行清理
read -p "确认执行清理操作？(y/N): " CONFIRM
if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "取消清理操作"
    exit 0
fi

# 3. 执行清理操作
echo "正在删除solution表中的记录..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "DELETE FROM solution WHERE user_id = '$USER_ID';"

echo "正在删除source_code表中的相关记录..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "DELETE FROM source_code WHERE solution_id IN (SELECT solution_id FROM solution WHERE user_id = '$USER_ID');"

# 3. 优化表结构（可选）
echo "正在优化表结构..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "OPTIMIZE TABLE solution, source_code;"

echo "清理完成！已删除 $USER_ID 的所有测试记录。"
