-- V1.2_20260501_courseware_preview_upgrade.sql
-- 课件预览付费改造

ALTER TABLE course ADD COLUMN `courseware_full_preview_url` VARCHAR(500) DEFAULT '' COMMENT '课件完整预览URL' AFTER courseware_preview_url;
ALTER TABLE course ADD COLUMN `lesson_plan_full_preview_url` VARCHAR(500) DEFAULT '' COMMENT '教案完整预览URL' AFTER lesson_plan_preview_url;

-- 回滚SQL
-- ALTER TABLE course DROP COLUMN IF EXISTS lesson_plan_full_preview_url;
-- ALTER TABLE course DROP COLUMN IF EXISTS courseware_full_preview_url;
