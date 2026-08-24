-- V2.0_20260417_problem_score_analysis.sql
-- 选择题分值、解析字段、竞赛来源、竞赛题目分值
-- 注意：answer 字段由 V1.1（选择题功能）新增，本脚本不再重复添加

-- 1. 添加分值字段
ALTER TABLE `problem` ADD COLUMN `score` INT(11) NOT NULL DEFAULT 0 COMMENT '题目分值' AFTER `options`;

-- 2. 添加解析字段
ALTER TABLE `problem` ADD COLUMN `analysis` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '答案解析' AFTER `answer`;

-- 3. 添加竞赛来源字段
ALTER TABLE `problem` ADD COLUMN `contest_source` VARCHAR(100) DEFAULT NULL COMMENT '竞赛来源（蓝桥杯/CSP等）' AFTER `source`;

-- 4. 竞赛题目表添加分值字段
ALTER TABLE `contest_problem` ADD COLUMN `score` INT(11) DEFAULT 0 COMMENT '竞赛中该题分值' AFTER `c_submit`;

-- 回滚SQL
-- ALTER TABLE `contest_problem` DROP COLUMN `score`;
-- ALTER TABLE `problem` DROP COLUMN `contest_source`;
-- ALTER TABLE `problem` DROP COLUMN `analysis`;
-- ALTER TABLE `problem` DROP COLUMN `score`;
-- DROP TABLE IF EXISTS `db_version`;
