#!/bin/bash

# 清理测试账号离线游戏包购买记录脚本
# 功能：清空 offline_game_order 表中的测试记录，并退还扣除的积分

# 从配置文件读取数据库连接信息
config="/home/judge/etc/judge.conf"
DB_HOST=`cat $config|grep 'OJ_HOST_NAME' |awk -F= '{print $2}'`
DB_USER=`cat $config|grep 'OJ_USER_NAME' |awk -F= '{print $2}'`
DB_PASS=`cat $config|grep 'OJ_PASSWORD' |awk -F= '{print $2}'`
DB_NAME=`cat $config|grep 'OJ_DB_NAME' |awk -F= '{print $2}'`

# 模式：user=只清理指定用户（默认），all=清空全部
MODE="${1:-user}"
USER_ID="${2:-zezhang}"

echo "========================================"
echo "离线游戏包购买记录清理工具"
echo "========================================"
echo ""
echo "数据库：$DB_NAME"
if [ "$MODE" = "user" ]; then
    echo "目标用户：$USER_ID"
    echo "模式：清理指定用户记录并退还积分"
else
    echo "模式：清空全部记录并退还积分"
fi
echo ""

# 1. 查看待清理的记录内容
echo "正在查询待清理的离线游戏包购买记录..."

if [ "$MODE" = "user" ]; then
    echo -e "\n===== offline_game_order表记录（用户: $USER_ID）====="
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT id, user_id, school_name, room_name, order_no, point_amount, create_time FROM offline_game_order WHERE user_id = '$USER_ID' ORDER BY create_time DESC;"
    RECORD_COUNT=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COUNT(*) FROM offline_game_order WHERE user_id = '$USER_ID';" | tail -1)
    TOTAL_POINTS=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COALESCE(SUM(point_amount), 0) FROM offline_game_order WHERE user_id = '$USER_ID';" | tail -1)
else
    echo -e "\n===== offline_game_order表全部记录 ====="
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT id, user_id, school_name, room_name, order_no, point_amount, create_time FROM offline_game_order ORDER BY create_time DESC;"
    RECORD_COUNT=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COUNT(*) FROM offline_game_order;" | tail -1)
    TOTAL_POINTS=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COALESCE(SUM(point_amount), 0) FROM offline_game_order;" | tail -1)
fi

echo -e "\n待清理的记录总数：$RECORD_COUNT 条"
echo "待退还的积分总数：$TOTAL_POINTS 分"

if [ "$RECORD_COUNT" = "0" ]; then
    echo "没有需要清理的记录"
    exit 0
fi

# 2. 确认是否执行清理
read -p "确认执行清理操作（删除订单 + 退还 $TOTAL_POINTS 积分）？(y/N): " CONFIRM
if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "取消清理操作"
    exit 0
fi

# 3. 执行清理操作（事务处理）
echo ""
echo "正在执行清理操作..."

if [ "$MODE" = "user" ]; then
    # 获取订单号列表（用于积分退还记录）
    ORDER_LIST=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -N -e "SELECT order_no FROM offline_game_order WHERE user_id = '$USER_ID';" 2>/dev/null)

    # 删除订单记录
    echo "正在删除 $USER_ID 的离线游戏包购买记录..."
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "DELETE FROM offline_game_order WHERE user_id = '$USER_ID';"

    # 更新用户积分（退还扣除的积分）
    echo "正在退还积分（+$TOTAL_POINTS）..."
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "UPDATE users SET point = point + $TOTAL_POINTS WHERE user_id = '$USER_ID';"

    # 记录积分退还日志（每笔订单一条记录）
    echo "正在记录积分退还日志..."
    for ORDER_NO in $ORDER_LIST; do
        mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "INSERT INTO point_log (user_id, change_point, balance, type, relation_id, remark) VALUES ('$USER_ID', 50, (SELECT point FROM users WHERE user_id = '$USER_ID'), 4, '$ORDER_NO', '测试数据清理：退还离线游戏兑换积分');"
    done
else
    # 清空全部模式
    # 获取所有订单号
    ORDER_LIST=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -N -e "SELECT order_no FROM offline_game_order;" 2>/dev/null)

    # 删除全部订单
    echo "正在清空全部离线游戏包购买记录..."
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "DELETE FROM offline_game_order;"

    # 更新所有受影响用户的积分
    echo "正在退还积分..."
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "
        UPDATE users u
        INNER JOIN (
            SELECT user_id, SUM(point_amount) as total_points
            FROM offline_game_order
            GROUP BY user_id
        ) o ON u.user_id = o.user_id
        SET u.point = u.point + o.total_points;"
fi

# 4. 优化表结构
echo "正在优化表结构..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "OPTIMIZE TABLE offline_game_order;"

# 5. 验证结果
echo ""
echo "========================================"
echo "清理完成！"
echo "========================================"
echo ""
echo "操作摘要："
echo "- 删除订单记录：$RECORD_COUNT 条"
echo "- 退还积分总数：$TOTAL_POINTS 分"
echo ""
echo "当前用户积分余额："
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT user_id, point FROM users WHERE user_id = '$USER_ID';"
echo ""
echo "最近积分变动记录："
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT id, user_id, change_point, balance, remark, create_time FROM point_log WHERE user_id = '$USER_ID' ORDER BY id DESC LIMIT 3;"
