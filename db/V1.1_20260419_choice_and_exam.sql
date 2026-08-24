-- V1.1_20260419_choice_and_exam.sql
-- 选择题功能 + 考试模块

-- 1. 选择题功能
ALTER TABLE problem ADD COLUMN `problem_type` ENUM('programming','choice_single','choice_multi','judge') NOT NULL DEFAULT 'programming' COMMENT '题目类型' AFTER title;
ALTER TABLE problem ADD COLUMN `options` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '选择题/判断题选项，JSON格式' AFTER source;
ALTER TABLE problem ADD COLUMN `answer` VARCHAR(500) DEFAULT NULL COMMENT '正确答案（选择题）/ OJ配置（编程题）' AFTER source;
ALTER TABLE solution MODIFY COLUMN pass_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '通过率百分比';
ALTER TABLE problem MODIFY COLUMN title TEXT NOT NULL;

-- 2. 考试模块
CREATE TABLE `exam` (
    `exam_id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '试卷标题',
    `description` TEXT DEFAULT NULL COMMENT '试卷说明',
    `creator_id` CHAR(48) NOT NULL DEFAULT '' COMMENT '创建者ID',
    `total_score` INT(11) NOT NULL DEFAULT 100 COMMENT '总分',
    `duration_min` INT(11) NOT NULL DEFAULT 60 COMMENT '时长（分钟）',
    `start_time` DATETIME NOT NULL DEFAULT '2099-01-01 00:00:00' COMMENT '开始时间',
    `end_time` DATETIME NOT NULL DEFAULT '2099-01-01 00:00:00' COMMENT '结束时间',
    `defunct` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'Y删除/N正常',
    `school_id` INT(11) DEFAULT NULL COMMENT '所属学校',
    `is_public` CHAR(1) NOT NULL DEFAULT 'Y' COMMENT 'Y公开/N私有',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`exam_id`),
    KEY `idx_creator` (`creator_id`),
    KEY `idx_school` (`school_id`),
    KEY `idx_defunct` (`defunct`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `exam_problem` (
    `ep_id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_id` INT(11) NOT NULL COMMENT '试卷ID',
    `problem_id` INT(11) NOT NULL COMMENT '题目ID',
    `score` INT(11) NOT NULL DEFAULT 10 COMMENT '本题分值',
    `num` INT(11) NOT NULL DEFAULT 0 COMMENT '题目序号',
    PRIMARY KEY (`ep_id`),
    KEY `idx_exam` (`exam_id`),
    KEY `idx_problem` (`problem_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `exam_attend` (
    `ea_id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_id` INT(11) NOT NULL COMMENT '试卷ID',
    `user_id` CHAR(48) NOT NULL COMMENT '用户ID',
    `start_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '开始时间',
    `submitted` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'Y已交卷/N未交',
    `total_score` INT(11) DEFAULT 0 COMMENT '最终总分',
    `score_calculated` TINYINT DEFAULT 0 COMMENT '0未计算/1完成/2计算中',
    PRIMARY KEY (`ea_id`),
    UNIQUE KEY `uk_exam_user` (`exam_id`, `user_id`),
    KEY `idx_exam` (`exam_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `exam_result` (
    `er_id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_id` INT(11) NOT NULL COMMENT '试卷ID',
    `user_id` CHAR(48) NOT NULL COMMENT '用户ID',
    `problem_id` INT(11) NOT NULL COMMENT '题目ID',
    `user_answer` CHAR(200) NOT NULL DEFAULT '' COMMENT '用户答案',
    `is_correct` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'Y正确/N错误',
    `score` INT(11) NOT NULL DEFAULT 0 COMMENT '本题得分',
    `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`er_id`),
    UNIQUE KEY `uk_exam_user_problem` (`exam_id`, `user_id`, `problem_id`),
    KEY `idx_exam_user` (`exam_id`, `user_id`),
    KEY `idx_exam` (`exam_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE solution ADD COLUMN `exam_id` INT(11) DEFAULT 0 COMMENT '所属考试ID，0为非考试提交';

-- 回滚SQL
-- ALTER TABLE solution DROP COLUMN exam_id;
-- DROP TABLE IF EXISTS exam_result;
-- DROP TABLE IF EXISTS exam_attend;
-- DROP TABLE IF EXISTS exam_problem;
-- DROP TABLE IF EXISTS exam;
-- ALTER TABLE problem MODIFY COLUMN title VARCHAR(200) NOT NULL;
-- ALTER TABLE solution MODIFY COLUMN pass_rate DECIMAL(4,3) UNSIGNED NOT NULL DEFAULT 0;
-- ALTER TABLE problem DROP COLUMN answer;
-- ALTER TABLE problem DROP COLUMN options;
-- ALTER TABLE problem DROP COLUMN problem_type;
