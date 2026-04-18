-- =============================================
-- 版本：V1.1
-- 日期：2026-04-18
-- 功能：考试（组卷+答题）模块上线
-- 执行说明：增量变更，不影响现有数据，可直接执行
-- 执行前提：无
-- =============================================

-- -------------------------------------------------------
-- 1. exam 表——试卷主表
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

-- -------------------------------------------------------
-- 2. exam_problem 表——试卷题目关联
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

-- -------------------------------------------------------
-- 3. exam_attend 表——考试参与记录
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

-- -------------------------------------------------------
-- 4. exam_result 表——每题答题结果（选择题/判断题）
-- -------------------------------------------------------
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
    KEY idx_exam_user (exam_id, user_id),
    KEY idx_exam (exam_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -------------------------------------------------------
-- 5. solution 表——新增 exam_id 字段关联考试提交
-- -------------------------------------------------------
-- 0 = 非考试提交，正数 = 所属考试ID
ALTER TABLE solution
ADD COLUMN exam_id INT(11) DEFAULT 0 COMMENT '所属考试ID，0为非考试提交';

-- -------------------------------------------------------
-- 回滚 SQL（如需回滚执行以下语句）
-- -------------------------------------------------------
-- ALTER TABLE solution DROP COLUMN exam_id;
-- （前4张表请手动 DROP TABLE 谨慎操作）
