-- V1.1.1_20260417_problem_score_analysis.sql
-- 选择题分值和解析字段

-- 1. 添加分值字段
ALTER TABLE `problem` ADD COLUMN `score` INT(11) NOT NULL DEFAULT 0 COMMENT '题目分值' AFTER `options`;

-- 2. 添加解析字段
ALTER TABLE `problem` ADD COLUMN `analysis` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '答案解析' AFTER `answer`;

-- 回滚SQL
-- ALTER TABLE `problem` DROP COLUMN `analysis`;
-- ALTER TABLE `problem` DROP COLUMN `score`;
