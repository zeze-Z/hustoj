-- V1.3_20260505_remove_extraction_codes.sql
-- 删除课程提取码字段（百度网盘改为金山文档）
-- 执行时间：2026-05-05

ALTER TABLE course DROP COLUMN courseware_code;
ALTER TABLE course DROP COLUMN lesson_plan_code;

-- 回滚SQL（数据不可恢复，慎用！）
-- ALTER TABLE course ADD COLUMN courseware_code VARCHAR(50) DEFAULT '';
-- ALTER TABLE course ADD COLUMN lesson_plan_code VARCHAR(50) DEFAULT '';
-- UPDATE course SET courseware_code='', lesson_plan_code='' WHERE courseware_code IS NULL;
