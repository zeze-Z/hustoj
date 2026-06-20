#!/bin/bash

# 清理测试账号的普通题目记录和竞赛真题答题记录脚本
# 用法：
#   ./cleanup_zezhang_solution.sh                 # 清理 zezhang 的普通题目 + 竞赛真题记录
#   ./cleanup_zezhang_solution.sh zezhang all     # 清理普通题目 + 竞赛真题记录
#   ./cleanup_zezhang_solution.sh zezhang problem # 只清理普通题目记录
#   ./cleanup_zezhang_solution.sh zezhang true    # 只清理竞赛真题记录

set -e

# 从配置文件读取数据库连接信息
config="/home/judge/etc/judge.conf"
DB_HOST=`cat $config|grep 'OJ_HOST_NAME' |awk -F= '{print $2}'`
DB_USER=`cat $config|grep 'OJ_USER_NAME' |awk -F= '{print $2}'`
DB_PASS=`cat $config|grep 'OJ_PASSWORD' |awk -F= '{print $2}'`
DB_NAME=`cat $config|grep 'OJ_DB_NAME' |awk -F= '{print $2}'`

# 要清理的用户名（默认为 zezhang）
USER_ID="${1:-zezhang}"
# 清理范围：all/problem/true，默认 all
CLEAN_MODE="${2:-all}"

if [ "$CLEAN_MODE" != "all" ] && [ "$CLEAN_MODE" != "problem" ] && [ "$CLEAN_MODE" != "true" ]; then
    echo "清理范围参数错误：$CLEAN_MODE"
    echo "可选值：all、problem、true"
    exit 1
fi

MYSQL_CMD="mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME"

# 与 PHP 中 is_true_question_contest_title() 保持一致
TRUE_QUESTION_CONDITION="(c.title LIKE '%真题%' OR c.title LIKE '%GESP%' OR c.title LIKE '%CSP%' OR c.title LIKE '%蓝桥杯%' OR c.title LIKE '%NOIP%' OR c.title LIKE '%CCF%')"

if [ "$CLEAN_MODE" = "problem" ]; then
    SCOPE_CONDITION="(s.contest_id IS NULL OR s.contest_id=0)"
    SCOPE_NAME="普通题目记录"
elif [ "$CLEAN_MODE" = "true" ]; then
    SCOPE_CONDITION="(s.contest_id>0 AND $TRUE_QUESTION_CONDITION)"
    SCOPE_NAME="竞赛真题答题记录"
else
    SCOPE_CONDITION="((s.contest_id IS NULL OR s.contest_id=0) OR (s.contest_id>0 AND $TRUE_QUESTION_CONDITION))"
    SCOPE_NAME="普通题目记录 + 竞赛真题答题记录"
fi

echo "========================================"
echo "测试账号题目/竞赛真题记录清理工具"
echo "========================================"
echo "目标用户：$USER_ID"
echo "清理范围：$SCOPE_NAME"
echo "数据库：$DB_NAME"
echo ""

# 1. 查看待清理的记录内容
echo "正在查询待清理记录..."

$MYSQL_CMD <<EOF
DROP TEMPORARY TABLE IF EXISTS tmp_cleanup_solution_ids;
CREATE TEMPORARY TABLE tmp_cleanup_solution_ids AS
SELECT s.solution_id
FROM solution s
LEFT JOIN contest c ON s.contest_id=c.contest_id
WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION;

SELECT '===== 待清理 solution 记录 =====' AS '';
SELECT s.solution_id,
       s.user_id,
       s.problem_id,
       s.contest_id,
       c.title AS contest_title,
       s.num,
       s.language,
       s.result,
       s.pass_rate,
       s.ip,
       s.in_date
FROM solution s
LEFT JOIN contest c ON s.contest_id=c.contest_id
INNER JOIN tmp_cleanup_solution_ids t ON s.solution_id=t.solution_id
ORDER BY s.in_date DESC;

SELECT '===== 待清理源码预览（source_code/source_code_user） =====' AS '';
SELECT x.source_table,
       x.solution_id,
       SUBSTRING(x.source, 1, 150) AS source_preview
FROM (
    SELECT 'source_code' AS source_table, sc.solution_id, sc.source
    FROM source_code sc
    INNER JOIN (SELECT solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) t ON sc.solution_id=t.solution_id
    UNION ALL
    SELECT 'source_code_user' AS source_table, scu.solution_id, scu.source
    FROM source_code_user scu
    INNER JOIN (SELECT solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) t ON scu.solution_id=t.solution_id
) x
ORDER BY x.solution_id DESC;

SELECT '===== 待清理记录统计 =====' AS '';
SELECT 'solution' AS table_name, COUNT(*) AS count FROM (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) t
UNION ALL
SELECT 'source_code', COUNT(*) FROM source_code WHERE solution_id IN (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION)
UNION ALL
SELECT 'source_code_user', COUNT(*) FROM source_code_user WHERE solution_id IN (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION)
UNION ALL
SELECT 'compileinfo', COUNT(*) FROM compileinfo WHERE solution_id IN (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION)
UNION ALL
SELECT 'runtimeinfo', COUNT(*) FROM runtimeinfo WHERE solution_id IN (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION)
UNION ALL
SELECT 'custominput', COUNT(*) FROM custominput WHERE solution_id IN (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION)
UNION ALL
SELECT 'sim', COUNT(*) FROM sim WHERE s_id IN (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) OR sim_s_id IN (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION);
EOF

SOLUTION_COUNT=$($MYSQL_CMD -N -B -e "SELECT COUNT(*) FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION;")

if [ "$SOLUTION_COUNT" = "0" ]; then
    echo ""
    echo "没有找到需要清理的记录。"
    exit 0
fi

echo ""
echo "待清理 solution 记录：$SOLUTION_COUNT 条"

# 2. 确认是否执行清理
read -p "确认执行清理操作？(y/N): " CONFIRM
if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "取消清理操作"
    exit 0
fi

# 3. 执行清理操作
# 注意：必须先固化 solution_id 并删除关联表，再删除 solution 表；否则按 solution 子查询会找不到关联源码。
echo "正在删除关联记录和 solution 记录..."

$MYSQL_CMD <<EOF
START TRANSACTION;

DELETE FROM source_code WHERE solution_id IN (SELECT solution_id FROM (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) tmp_ids);
DELETE FROM source_code_user WHERE solution_id IN (SELECT solution_id FROM (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) tmp_ids);
DELETE FROM compileinfo WHERE solution_id IN (SELECT solution_id FROM (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) tmp_ids);
DELETE FROM runtimeinfo WHERE solution_id IN (SELECT solution_id FROM (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) tmp_ids);
DELETE FROM custominput WHERE solution_id IN (SELECT solution_id FROM (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) tmp_ids);
DELETE FROM sim WHERE s_id IN (SELECT solution_id FROM (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) tmp_ids) OR sim_s_id IN (SELECT solution_id FROM (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) tmp_ids);
DELETE FROM solution WHERE solution_id IN (SELECT solution_id FROM (SELECT s.solution_id FROM solution s LEFT JOIN contest c ON s.contest_id=c.contest_id WHERE s.user_id='$USER_ID' AND $SCOPE_CONDITION) tmp_ids);

COMMIT;
EOF

# 4. 优化表结构（可选）
echo "正在优化表结构..."
$MYSQL_CMD -e "OPTIMIZE TABLE solution, source_code, source_code_user, compileinfo, runtimeinfo, custominput, sim;"

echo ""
echo "========================================"
echo "清理完成！"
echo "========================================"
echo "- 用户：$USER_ID"
echo "- 范围：$SCOPE_NAME"
echo "- 已删除 solution 记录：$SOLUTION_COUNT 条"
