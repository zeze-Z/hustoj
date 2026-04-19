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

-- 2. 初始化成都东辰学校
INSERT INTO `school` (`id`, `name`, `code`, `status`) VALUES 
(1, '成都东辰', 'chengdu_dongchen', 1);

-- 3. 用户表扩展
-- 扩展 school 字段长度
ALTER TABLE `users` MODIFY COLUMN `school` varchar(100) NOT NULL DEFAULT '' COMMENT '学校名称(冗余)';

-- 新增 school_id（检查是否已存在）
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
  AND table_name = 'users' 
  AND column_name = 'school_id';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `users` ADD COLUMN `school_id` int DEFAULT NULL COMMENT ''所属学校ID'' AFTER `school`',
    'SELECT ''Column school_id already exists in users table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加索引（检查是否已存在）
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists 
FROM information_schema.statistics 
WHERE table_schema = DATABASE() 
  AND table_name = 'users' 
  AND index_name = 'idx_school_id';

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `users` ADD KEY `idx_school_id` (`school_id`)',
    'SELECT ''Index idx_school_id already exists in users table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. 迁移现有用户到成都东辰
UPDATE `users` SET `school_id` = 1, `school` = '成都东辰' WHERE `school_id` IS NULL;

-- 5. 题目表扩展
-- 添加 school_id
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
  AND table_name = 'problem' 
  AND column_name = 'school_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `problem` ADD COLUMN `school_id` int DEFAULT NULL COMMENT ''创建学校ID'' AFTER `source`',
    'SELECT ''Column school_id already exists in problem table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加 is_public
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
  AND table_name = 'problem' 
  AND column_name = 'is_public';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `problem` ADD COLUMN `is_public` tinyint NOT NULL DEFAULT ''0'' COMMENT ''是否公开:0否,1是'' AFTER `school_id`',
    'SELECT ''Column is_public already exists in problem table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加索引
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists 
FROM information_schema.statistics 
WHERE table_schema = DATABASE() 
  AND table_name = 'problem' 
  AND index_name = 'idx_school_id';

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `problem` ADD KEY `idx_school_id` (`school_id`)',
    'SELECT ''Index idx_school_id already exists in problem table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. 比赛表扩展
-- 添加 school_id
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
  AND table_name = 'contest' 
  AND column_name = 'school_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `contest` ADD COLUMN `school_id` int DEFAULT NULL COMMENT ''创建学校ID'' AFTER `user_id`',
    'SELECT ''Column school_id already exists in contest table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加 is_public
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
  AND table_name = 'contest' 
  AND column_name = 'is_public';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `contest` ADD COLUMN `is_public` tinyint NOT NULL DEFAULT ''0'' COMMENT ''是否公开:0否,1是'' AFTER `school_id`',
    'SELECT ''Column is_public already exists in contest table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加索引
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists 
FROM information_schema.statistics 
WHERE table_schema = DATABASE() 
  AND table_name = 'contest' 
  AND index_name = 'idx_school_id';

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `contest` ADD KEY `idx_school_id` (`school_id`)',
    'SELECT ''Index idx_school_id already exists in contest table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. 新闻表扩展
-- 添加 school_id
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
  AND table_name = 'news' 
  AND column_name = 'school_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `news` ADD COLUMN `school_id` int DEFAULT NULL COMMENT ''创建学校ID'' AFTER `defunct`',
    'SELECT ''Column school_id already exists in news table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加 is_public
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
  AND table_name = 'news' 
  AND column_name = 'is_public';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `news` ADD COLUMN `is_public` tinyint NOT NULL DEFAULT ''0'' COMMENT ''是否公开:0否,1是'' AFTER `school_id`',
    'SELECT ''Column is_public already exists in news table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加索引
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists 
FROM information_schema.statistics 
WHERE table_schema = DATABASE() 
  AND table_name = 'news' 
  AND index_name = 'idx_school_id';

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `news` ADD KEY `idx_school_id` (`school_id`)',
    'SELECT ''Index idx_school_id already exists in news table''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 8. 现有题目/比赛/新闻默认归属成都东辰并公开
UPDATE `problem` SET `school_id` = 1, `is_public` = 1 WHERE `school_id` IS NULL;
UPDATE `contest` SET `school_id` = 1, `is_public` = 1 WHERE `school_id` IS NULL;
UPDATE `news` SET `school_id` = 1, `is_public` = 1 WHERE `school_id` IS NULL;

-- 9. 写入版本记录
INSERT INTO `news` (`user_id`, `title`, `content`, `time`, `importance`, `menu`, `defunct`) VALUES 
('system', '系统升级', '已启用多学校隔离功能', NOW(), 0, 0, 'N');

SELECT '多学校隔离功能升级完成!' AS result;
