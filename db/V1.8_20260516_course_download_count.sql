-- V1.8_20260516_course_download_count.sql
-- 课程下载次数统计功能

-- 1. 课程表新增下载次数字段
ALTER TABLE course ADD COLUMN `download_count` int(11) NOT NULL DEFAULT 0 COMMENT '课程下载次数' AFTER `lesson_count`;

-- 2. 订单表新增是否已计数字段（用于去重，同一用户同一课程只计数一次）
ALTER TABLE course_order ADD COLUMN `counted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已计入下载次数：0=未计数 1=已计数' AFTER `mail_status`;

-- 回滚脚本（取消注释后执行）
-- ALTER TABLE course DROP COLUMN `download_count`;
-- ALTER TABLE course_order DROP COLUMN `counted`;
