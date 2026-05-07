-- V1.5_20260507_add_problem_level.sql
-- 功能：problem 表增加难度等级字段 level
-- 依赖：无
-- 兼容：MySQL 8.0.28

-- 添加 level 字段
ALTER TABLE `problem` ADD COLUMN `level` int(11) NOT NULL DEFAULT 1 COMMENT '难度等级(1-5)' AFTER `defunct`;

-- 回滚
-- ALTER TABLE `problem` DROP COLUMN `level`;
