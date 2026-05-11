-- V1.6_20260511_register_role.sql
-- 注册功能优化：用户角色选择

-- 1. users表新增role字段
ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'student' COMMENT '用户角色：teacher/student' AFTER `school_id`;

-- 回滚脚本（取消注释后执行）
-- ALTER TABLE `users` DROP COLUMN `role`;
