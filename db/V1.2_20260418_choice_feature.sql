-- =============================================
-- 版本：V1.2
-- 日期：2026-04-18
-- 功能：选择题功能上线（题型+选项+答案+判题+展示）
-- 执行说明：增量变更，不影响现有数据，可直接执行
-- 执行前提：已完成 V1.1 考试模块（如未执行，先执行 V1.1）
-- =============================================

-- -------------------------------------------------------
-- 1. problem 表新增题型字段
-- -------------------------------------------------------
-- problem_type: 题目类型
--   - programming:  编程题（默认）
--   - choice_single: 单选题
--   - choice_multi:  多选题
--   - judge:         判断题
ALTER TABLE problem
ADD COLUMN problem_type ENUM('programming','choice_single','choice_multi','judge')
NOT NULL DEFAULT 'programming'
COMMENT '题目类型：编程题/单选题/多选题/判断题'
AFTER title;

-- -------------------------------------------------------
-- 2. problem 表新增选项字段（JSON 格式）
-- -------------------------------------------------------
-- 仅选择题/判断题需要，示例格式：
--   [{"key":"A","content":"选项内容"},{"key":"B","content":"..."}]
-- judge 题只保留一个 key:"T" 和 key:"F"
ALTER TABLE problem
ADD COLUMN options LONGTEXT
CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
COMMENT '选择题/判断题选项，JSON 格式'
AFTER source;

-- -------------------------------------------------------
-- 3. problem 表扩大 answer 字段
-- -------------------------------------------------------
-- 编程题：answer 字段原为 NULL 或存放 OJ 配置
-- 选择题：answer 存放正确答案（如 "C"、"AB"），500 足够容纳任意组合
-- 原字段为 varchar(200)，改为 varchar(500) 避免溢出
ALTER TABLE problem
MODIFY COLUMN answer VARCHAR(500) DEFAULT NULL
COMMENT '正确答案（选择题）/ OJ配置（编程题）';

-- -------------------------------------------------------
-- 回滚 SQL（如需回滚执行以下语句）
-- -------------------------------------------------------
-- ALTER TABLE problem DROP COLUMN problem_type;
-- ALTER TABLE problem DROP COLUMN options;
-- ALTER TABLE problem MODIFY COLUMN answer VARCHAR(200) DEFAULT NULL COMMENT '正确答案';
