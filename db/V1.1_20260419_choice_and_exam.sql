-- =============================================
-- 版本：V1.1
-- 日期：2026-04-19
-- 功能：选择题功能 + 考试（组卷+答题）模块上线
-- 执行说明：增量变更，不影响现有数据，可直接执行
-- 执行前提：基于 HustOJ 原始数据库（已有 problem / solution 表）
-- =============================================

-- ===========================================================
-- 第一部分：选择题功能（problem 表扩展）
-- ===========================================================

-- 1.1 problem 表新增题型字段
-- -------------------------------------------------------
-- problem_type: 题目类型
--   - programming:   编程题（默认，兼容存量数据）
--   - choice_single: 单选题
--   - choice_multi:  多选题
--   - judge:         判断题
ALTER TABLE problem
ADD COLUMN problem_type ENUM('programming','choice_single','choice_multi','judge')
NOT NULL DEFAULT 'programming'
COMMENT '题目类型：编程题/单选题/多选题/判断题'
AFTER title;

-- 1.2 problem 表新增选项字段（JSON 格式）
-- -------------------------------------------------------
-- 仅选择题/判断题需要，编程题该字段为 NULL
-- 示例：[{"key":"A","content":"选项内容"},{"key":"B","content":"..."}]
-- judge 题只保留 key:"T" 和 key:"F"
ALTER TABLE problem
ADD COLUMN options LONGTEXT
CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
COMMENT '选择题/判断题选项，JSON 格式'
AFTER source;

-- 1.3 problem 表扩大 answer 字段
-- -------------------------------------------------------
-- 编程题：answer 字段原为 varchar(200)，存放 OJ 配置
-- 选择题：answer 存放正确答案（如 "C"、"ABD"），扩大到 500 容纳任意组合
ALTER TABLE problem
MODIFY COLUMN answer VARCHAR(500) DEFAULT NULL
COMMENT '正确答案（选择题）/ OJ配置（编程题）';

-- 1.4 solution 表修改 pass_rate 字段
-- -------------------------------------------------------
-- 原始定义：decimal(4,3) UNSIGNED，最大值 9.999，无法存储 100
-- 选择题全对时 pass_rate=100，需改为 decimal(5,2)，最大值 999.99
ALTER TABLE solution
MODIFY COLUMN pass_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00
COMMENT '通过率百分比，100=全对，0=全错';

-- ===========================================================
-- 第二部分：考试模块（4 张新表 + 1 个字段）
-- ===========================================================

-- 2.1 exam 表——试卷主表
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS exam (
    exam_id       INT(11)      NOT NULL AUTO_INCREMENT,
    title         VARCHAR(200) NOT NULL DEFAULT ''        COMMENT '试卷标题',
    description   TEXT         DEFAULT NULL               COMMENT '试卷说明',
    creator_id    CHAR(48)     NOT NULL DEFAULT ''         COMMENT '创建者ID',
    total_score   INT(11)      NOT NULL DEFAULT 100        COMMENT '总分',
    duration_min  INT(11)      NOT NULL DEFAULT 60         COMMENT '时长（分钟）',
    start_time    DATETIME     NOT NULL DEFAULT '2099-01-01 00:00:00' COMMENT '开始时间',
    end_time      DATETIME     NOT NULL DEFAULT '2099-01-01 00:00:00' COMMENT '结束时间',
    defunct       CHAR(1)      NOT NULL DEFAULT 'N'        COMMENT 'Y删除/N正常',
    school_id     INT(11)      DEFAULT NULL               COMMENT '所属学校',
    is_public     CHAR(1)      NOT NULL DEFAULT 'Y'        COMMENT 'Y公开/N私有',
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (exam_id),
    KEY idx_creator  (creator_id),
    KEY idx_school   (school_id),
    KEY idx_defunct  (defunct)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2.2 exam_problem 表——试卷题目关联
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS exam_problem (
    ep_id       INT(11) NOT NULL AUTO_INCREMENT,
    exam_id     INT(11) NOT NULL COMMENT '试卷ID',
    problem_id  INT(11) NOT NULL COMMENT '题目ID',
    score       INT(11) NOT NULL DEFAULT 10 COMMENT '本题分值',
    num         INT(11) NOT NULL DEFAULT 0  COMMENT '题目序号（1起）',
    PRIMARY KEY (ep_id),
    KEY idx_exam    (exam_id),
    KEY idx_problem (problem_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2.3 exam_attend 表——考试参与记录
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS exam_attend (
    ea_id             INT(11)  NOT NULL AUTO_INCREMENT,
    exam_id           INT(11)  NOT NULL COMMENT '试卷ID',
    user_id           CHAR(48) NOT NULL COMMENT '用户ID',
    start_time        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '开始时间',
    submitted         CHAR(1)  NOT NULL DEFAULT 'N' COMMENT 'Y已交卷/N未交',
    total_score       INT(11) DEFAULT 0 COMMENT '最终总分（含编程题）',
    score_calculated   TINYINT DEFAULT 0 COMMENT '0未计算/1完成/2计算中',
    PRIMARY KEY (ea_id),
    UNIQUE KEY uk_exam_user (exam_id, user_id),
    KEY idx_exam (exam_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2.4 exam_result 表——每题答题结果
-- -------------------------------------------------------
-- 注意：uk_exam_user_problem 唯一索引是必须的
-- 代码 exam_do.php 使用 ON DUPLICATE KEY UPDATE 重复交卷时更新而非插入
CREATE TABLE IF NOT EXISTS exam_result (
    er_id         INT(11)  NOT NULL AUTO_INCREMENT,
    exam_id       INT(11)  NOT NULL COMMENT '试卷ID',
    user_id       CHAR(48) NOT NULL COMMENT '用户ID',
    problem_id    INT(11)  NOT NULL COMMENT '题目ID',
    user_answer   CHAR(200) NOT NULL DEFAULT '' COMMENT '用户答案',
    is_correct    CHAR(1)  NOT NULL DEFAULT 'N' COMMENT 'Y正确/N错误',
    score         INT(11) NOT NULL DEFAULT 0 COMMENT '本题得分',
    submitted_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (er_id),
    UNIQUE KEY uk_exam_user_problem (exam_id, user_id, problem_id),
    KEY idx_exam_user (exam_id, user_id),
    KEY idx_exam (exam_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2.5 solution 表——新增 exam_id 字段关联考试提交
-- -------------------------------------------------------
-- 0 = 非考试提交（包括独立选择题提交），正数 = 所属考试ID
ALTER TABLE solution
ADD COLUMN exam_id INT(11) DEFAULT 0 COMMENT '所属考试ID，0为非考试提交';

-- =============================================
-- 回滚 SQL（如需回滚执行以下语句，按逆序执行）
-- =============================================
--
-- -- 2.5 回滚
-- ALTER TABLE solution DROP COLUMN exam_id;
--
-- -- 2.4 回滚（谨慎！会丢失答题数据）
-- DROP TABLE IF EXISTS exam_result;
--
-- -- 2.3 回滚（谨慎！会丢失参考数据）
-- DROP TABLE IF EXISTS exam_attend;
--
-- -- 2.2 回滚（谨慎！会丢试卷-题目关联）
-- DROP TABLE IF EXISTS exam_problem;
--
-- -- 2.1 回滚（谨慎！会丢试卷数据）
-- DROP TABLE IF EXISTS exam;
--
-- -- 1.4 回滚
-- ALTER TABLE solution MODIFY COLUMN pass_rate DECIMAL(4,3) UNSIGNED NOT NULL DEFAULT 0;
--
-- -- 1.3 回滚
-- ALTER TABLE problem MODIFY COLUMN answer VARCHAR(200) DEFAULT NULL;
--
-- -- 1.2 回滚
-- ALTER TABLE problem DROP COLUMN options;
--
-- -- 1.1 回滚
-- ALTER TABLE problem DROP COLUMN problem_type;
