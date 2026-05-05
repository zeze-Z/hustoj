-- V1.1_20260419_choice_and_exam.sql
-- 选择题功能 + 考试模块

-- 1. 选择题功能（幂等）
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='problem' AND COLUMN_NAME='problem_type');
SET @sql = IF(@cnt=0, 'ALTER TABLE problem ADD COLUMN `problem_type` ENUM(''programming'',''choice_single'',''choice_multi'',''judge'') NOT NULL DEFAULT ''programming'' COMMENT ''题目类型'' AFTER title', 'DO 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='problem' AND COLUMN_NAME='options');
SET @sql = IF(@cnt=0, 'ALTER TABLE problem ADD COLUMN `options` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT ''选择题/判断题选项，JSON格式'' AFTER source', 'DO 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 注意：pass_rate原始类型是decimal(4,3)，V1.1改为decimal(5,2)
ALTER TABLE solution MODIFY COLUMN pass_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '通过率百分比';

SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='problem' AND COLUMN_NAME='title' AND COLUMN_TYPE='text');
SET @sql = IF(@cnt=0, 'ALTER TABLE problem MODIFY COLUMN title TEXT NOT NULL', 'DO 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = NULL;
SET @cnt = NULL;

-- 2. 考试模块表（CREATE TABLE IF NOT EXISTS 本身幂等）
CREATE TABLE IF NOT EXISTS `exam` (
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

CREATE TABLE IF NOT EXISTS `exam_problem` (
    `ep_id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_id` INT(11) NOT NULL COMMENT '试卷ID',
    `problem_id` INT(11) NOT NULL COMMENT '题目ID',
    `score` INT(11) NOT NULL DEFAULT 10 COMMENT '本题分值',
    `num` INT(11) NOT NULL DEFAULT 0 COMMENT '题目序号',
    PRIMARY KEY (`ep_id`),
    KEY `idx_exam` (`exam_id`),
    KEY `idx_problem` (`problem_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `exam_attend` (
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

CREATE TABLE IF NOT EXISTS `exam_result` (
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

SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='solution' AND COLUMN_NAME='exam_id');
SET @sql = IF(@cnt=0, 'ALTER TABLE solution ADD COLUMN `exam_id` INT(11) DEFAULT 0 COMMENT ''所属考试ID，0为非考试提交''', 'DO 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = NULL;
SET @cnt = NULL;

-- 回滚SQL
-- ALTER TABLE solution DROP COLUMN IF EXISTS exam_id;
-- DROP TABLE IF EXISTS exam_result;
-- DROP TABLE IF EXISTS exam_attend;
-- DROP TABLE IF EXISTS exam_problem;
-- DROP TABLE IF EXISTS exam;
-- ALTER TABLE problem MODIFY COLUMN title VARCHAR(200) NOT NULL;
-- ALTER TABLE solution MODIFY COLUMN pass_rate DECIMAL(4,3) UNSIGNED NOT NULL DEFAULT 0;
-- ALTER TABLE problem DROP COLUMN IF EXISTS options;
-- ALTER TABLE problem DROP COLUMN IF EXISTS problem_type;
