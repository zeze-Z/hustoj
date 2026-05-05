-- 多权限体系升级

-- 1. 课程表新增预览版和原文件价格字段（幂等）
ALTER TABLE course ADD COLUMN IF NOT EXISTS `preview_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '预览版价格' AFTER `price`;
ALTER TABLE course ADD COLUMN IF NOT EXISTS `source_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '原文件价格' AFTER `preview_price`;

-- 2. 订单表新增权限类型字段（幂等）
ALTER TABLE course_order ADD COLUMN IF NOT EXISTS `license_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '权限类型：1=仅预览版 2=仅原文件 3=完整版(预览+原文件)' AFTER `course_id`;

-- 3. 修改唯一键（删除旧索引并添加新索引）
ALTER TABLE course_order DROP INDEX IF EXISTS `uk_user_course`;
ALTER TABLE course_order ADD UNIQUE KEY IF NOT EXISTS `uk_user_course_license` (`user_id`, `course_id`, `license_type`);

-- 4. 历史数据兼容：原有订单默认设为完整版(3)（幂等：仅更新license_type为默认值1的记录）
UPDATE course_order SET license_type = 3 WHERE pay_status = 1 AND license_type = 1;

-- 回滚脚本（取消注释后执行）
-- ALTER TABLE course DROP COLUMN IF EXISTS `preview_price`, DROP COLUMN IF EXISTS `source_price`;
-- ALTER TABLE course_order DROP COLUMN IF EXISTS `license_type`;
-- ALTER TABLE course_order DROP INDEX IF EXISTS `uk_user_course_license`;
-- ALTER TABLE course_order ADD UNIQUE KEY IF NOT EXISTS `uk_user_course` (`user_id`, `course_id`);