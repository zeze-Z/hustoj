#!/bin/bash

# 清理测试账号的课件订单记录脚本
# 功能：清空指定用户在 course_order 表中的记录，并同步调整 download_count

# 从配置文件读取数据库连接信息
config="/home/judge/etc/judge.conf"
DB_HOST=`cat $config|grep 'OJ_HOST_NAME' |awk -F= '{print $2}'`
DB_USER=`cat $config|grep 'OJ_USER_NAME' |awk -F= '{print $2}'`
DB_PASS=`cat $config|grep 'OJ_PASSWORD' |awk -F= '{print $2}'`
DB_NAME=`cat $config|grep 'OJ_DB_NAME' |awk -F= '{print $2}'`

# 要清理的用户名（默认为 zezhang）
USER_ID="${1:-zezhang}"

echo "========================================"
echo "课件订单记录清理工具"
echo "========================================"
echo ""
echo "目标用户：$USER_ID"
echo "数据库：$DB_NAME"
echo ""

# 1. 查看待清理的记录内容
echo "正在查询待清理的课件订单记录..."

echo -e "\n===== course_order表记录 ====="
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT id, order_no, course_id, license_type, amount, pay_status, pay_channel, created_at FROM course_order WHERE user_id = '$USER_ID' ORDER BY created_at DESC;"

# 统计记录数量
ORDER_COUNT=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COUNT(*) FROM course_order WHERE user_id = '$USER_ID';" | tail -1)

echo -e "\n待清理的记录总数："
echo "- course_order表：$ORDER_COUNT 条记录"

# 2. 确认是否执行清理
read -p "确认执行清理操作？(y/N): " CONFIRM
if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "取消清理操作"
    exit 0
fi

# 3. 执行清理操作前，先调整 download_count
echo "正在调整课程下载次数..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME <<EOF
-- 先找出需要调整的课程
SET @affected_courses = (
    SELECT COUNT(DISTINCT course_id) 
    FROM course_order 
    WHERE user_id = '$USER_ID' AND counted = 1
);

-- 更新这些课程的 download_count，只对 counted = 1 的订单进行减一
UPDATE course c
INNER JOIN (
    SELECT DISTINCT course_id 
    FROM course_order 
    WHERE user_id = '$USER_ID' AND counted = 1
) co ON c.id = co.course_id
SET c.download_count = GREATEST(0, c.download_count - 1);

SELECT @affected_courses AS '受影响的课程数';
EOF

# 4. 删除 course_order 表中的记录
echo "正在删除 course_order 表中的记录..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "DELETE FROM course_order WHERE user_id = '$USER_ID';"

# 5. 优化表结构（可选）
echo "正在优化表结构..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "OPTIMIZE TABLE course_order, course;"

echo ""
echo "========================================"
echo "清理完成！"
echo "========================================"
echo "- 已删除 $USER_ID 的所有课件订单记录"
echo "- 已同步调整相关课程的 download_count"
