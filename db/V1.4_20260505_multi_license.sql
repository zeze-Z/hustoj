-- V1.4_20260505_multi_license.sql
-- 多权限体系升级

-- 1. 课程表新增预览版和原文件价格字段
ALTER TABLE course ADD COLUMN `preview_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '预览版价格' AFTER `price`;
ALTER TABLE course ADD COLUMN `source_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '原文件价格' AFTER `preview_price`;

-- 2. 订单表新增权限类型字段
ALTER TABLE course_order ADD COLUMN `license_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '权限类型：1=仅预览版 2=仅原文件 3=完整版(预览+原文件)' AFTER `course_id`;

-- 3. 修改唯一键（先删旧索引，再加新索引）
ALTER TABLE course_order DROP INDEX `uk_user_course`;
ALTER TABLE course_order ADD UNIQUE KEY `uk_user_course_license` (`user_id`, `course_id`, `license_type`);

-- 4. 历史数据兼容：原有订单默认设为完整版(3)
UPDATE course_order SET license_type = 3 WHERE pay_status = 1;

-- 回滚脚本（取消注释后执行）
-- ALTER TABLE course DROP COLUMN `preview_price`, DROP COLUMN `source_price`;
-- ALTER TABLE course_order DROP COLUMN `license_type`;
-- ALTER TABLE course_order DROP INDEX `uk_user_course_license`;
-- ALTER TABLE course_order ADD UNIQUE KEY `uk_user_course` (`user_id`, `course_id`);
