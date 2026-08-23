-- =============================================
-- V2.5 2026-08-23 课件学科Tab重命名
-- 功能：course.php 学科Tab 美术->新课标解读，音乐->小学电子教材
-- 兼容 MySQL 8.0.28
-- =============================================

UPDATE `course_subject` SET `name` = '新课标解读' WHERE `name` = '美术';
UPDATE `course_subject` SET `name` = '小学电子教材' WHERE `name` = '音乐';

-- =============================================
-- 回滚 SQL（如需回滚执行以下语句）
-- =============================================
--
-- UPDATE `course_subject` SET `name` = '美术' WHERE `name` = '新课标解读';
-- UPDATE `course_subject` SET `name` = '音乐' WHERE `name` = '小学电子教材';
