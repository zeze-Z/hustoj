-- ============================================
-- 多学校隔离功能 - 数据库升级脚本
-- 基于 jol_full_structure-20260317.sql
-- 执行方式: mysql -u root -p jol < this_file.sql
-- ============================================

SET NAMES utf8mb4;

-- 1. 创建学校表
DROP TABLE IF EXISTS `school`;
CREATE TABLE `school` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '学校名称',
  `code` varchar(50) NOT NULL COMMENT '学校代码',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态:0禁用,1启用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. 初始化成都东辰学校（幂等：使用INSERT IGNORE）
INSERT IGNORE INTO `school` (`id`, `name`, `code`, `status`) VALUES 
(1, '成都东辰', 'chengdu_dongchen', 1);

-- 3. 用户表扩展
-- 扩展 school 字段长度（MODIFY本身幂等）
ALTER TABLE `users` MODIFY COLUMN `school` varchar(100) NOT NULL DEFAULT '' COMMENT '学校名称(冗余)';

-- 新增 school_id（幂等）
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `school_id` int DEFAULT NULL COMMENT '所属学校ID' AFTER `school`;

-- 添加索引（幂等）
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_school_id` (`school_id`);

-- 4. 迁移现有用户到成都东辰（幂等：仅更新school_id为NULL的记录）
UPDATE `users` SET `school_id` = 1, `school` = '成都东辰' WHERE `school_id` IS NULL;

-- 5. 题目表扩展
ALTER TABLE `problem` ADD COLUMN IF NOT EXISTS `school_id` int DEFAULT NULL COMMENT '创建学校ID' AFTER `source`;
ALTER TABLE `problem` ADD COLUMN IF NOT EXISTS `is_public` tinyint NOT NULL DEFAULT '0' COMMENT '是否公开:0否,1是' AFTER `school_id`;
ALTER TABLE `problem` ADD INDEX IF NOT EXISTS `idx_school_id` (`school_id`);

-- 6. 比赛表扩展
ALTER TABLE `contest` ADD COLUMN IF NOT EXISTS `school_id` int DEFAULT NULL COMMENT '创建学校ID' AFTER `user_id`;
ALTER TABLE `contest` ADD COLUMN IF NOT EXISTS `is_public` tinyint NOT NULL DEFAULT '0' COMMENT '是否公开:0否,1是' AFTER `school_id`;
ALTER TABLE `contest` ADD INDEX IF NOT EXISTS `idx_school_id` (`school_id`);

-- 7. 新闻表扩展
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `school_id` int DEFAULT NULL COMMENT '创建学校ID' AFTER `defunct`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `is_public` tinyint NOT NULL DEFAULT '0' COMMENT '是否公开:0否,1是' AFTER `school_id`;
ALTER TABLE `news` ADD INDEX IF NOT EXISTS `idx_school_id` (`school_id`);

-- 8. 现有题目/比赛/新闻默认归属成都东辰并公开（幂等：仅更新school_id为NULL的记录）
UPDATE `problem` SET `school_id` = 1, `is_public` = 1 WHERE `school_id` IS NULL;
UPDATE `contest` SET `school_id` = 1, `is_public` = 1 WHERE `school_id` IS NULL;
UPDATE `news` SET `school_id` = 1, `is_public` = 1 WHERE `school_id` IS NULL;

-- 9. 写入版本记录（幂等：使用INSERT IGNORE）
INSERT IGNORE INTO `news` (`user_id`, `title`, `content`, `time`, `importance`, `menu`, `defunct`) VALUES 
('system', '系统升级', '已启用多学校隔离功能', NOW(), 0, 0, 'N');

SELECT '多学校隔离功能升级完成!' AS result;

-- =============================================
-- 回滚 SQL（如需回滚执行以下语句，按顺序执行）
-- =============================================
--
-- -- 1. 删除版本记录
-- DELETE FROM `news` WHERE `user_id`='system' AND `title`='系统升级' AND `content`='已启用多学校隔离功能';
--
-- -- 2. 恢复数据（取消更新）
-- UPDATE `news` SET `school_id` = NULL, `is_public` = 0 WHERE `school_id` = 1;
-- UPDATE `contest` SET `school_id` = NULL, `is_public` = 0 WHERE `school_id` = 1;
-- UPDATE `problem` SET `school_id` = NULL, `is_public` = 0 WHERE `school_id` = 1;
-- UPDATE `users` SET `school_id` = NULL, `school` = '' WHERE `school_id` = 1;
--
-- -- 3. 删除各表新增字段和索引
-- ALTER TABLE `news` DROP INDEX IF EXISTS `idx_school_id`, DROP COLUMN IF EXISTS `is_public`, DROP COLUMN IF EXISTS `school_id`;
-- ALTER TABLE `contest` DROP INDEX IF EXISTS `idx_school_id`, DROP COLUMN IF EXISTS `is_public`, DROP COLUMN IF EXISTS `school_id`;
-- ALTER TABLE `problem` DROP INDEX IF EXISTS `idx_school_id`, DROP COLUMN IF EXISTS `is_public`, DROP COLUMN IF EXISTS `school_id`;
-- ALTER TABLE `users` DROP INDEX IF EXISTS `idx_school_id`, DROP COLUMN IF EXISTS `school_id`;
--
-- -- 4. 恢复 users.school 字段长度
-- ALTER TABLE `users` MODIFY COLUMN `school` varchar(20) NOT NULL DEFAULT '';
--
-- -- 5. 删除学校表
-- DROP TABLE IF EXISTS `school`;