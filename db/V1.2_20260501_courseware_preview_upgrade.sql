-- =============================================
-- 版本：V1.2
-- 日期：2026-05-01
-- 功能：课件预览付费改造 — 新增完整版预览URL字段
-- 执行说明：增量变更，可重入执行，不影响现有数据
-- =============================================

USE jol;

-- 新增课件完整版预览链接（部分预览沿用现有 courseware_preview_url 字段）
ALTER TABLE `course`
  ADD COLUMN IF NOT EXISTS `courseware_full_preview_url` VARCHAR(500) DEFAULT NULL COMMENT '课件完整版预览链接(付费后可见)',
  ADD COLUMN IF NOT EXISTS `lesson_plan_full_preview_url` VARCHAR(500) DEFAULT NULL COMMENT '教案完整版预览链接(付费后可见)';

-- =============================================
-- 回滚 SQL（如需回滚执行以下语句）
-- =============================================
--
-- ALTER TABLE `course` DROP COLUMN IF EXISTS `courseware_full_preview_url`;
-- ALTER TABLE `course` DROP COLUMN IF EXISTS `lesson_plan_full_preview_url`;
