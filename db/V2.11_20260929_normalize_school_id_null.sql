-- V2.11: 历史用户 school_id 规范化（0 → NULL）
-- 背景：register.php / admin/user_import.php 在用户未选学校时写入 school_id=0，
--      与 users.school_id 的 schema DEFAULT NULL 不一致；
--      include/school.php 的 getSchoolSQLFilter 用 IS NULL 识别"无学校"用户，
--      school_id=0 的数据无法被 IS NULL 分支匹配，导致过滤逻辑在某些场景失效。
-- 本次迁移将 school_id=0 的存量用户统一改为 NULL，让代码与数据契约一致。
-- 幂等：无 school_id=0 的行时 UPDATE 0 行，可重复执行。

UPDATE `users` SET `school_id` = NULL WHERE `school_id` = 0;

-- 回滚说明：NULL 才是正确语义，通常不需要回滚。
-- 若确需恢复 0 标记（不推荐），请在执行前先备份受影响 user_id 列表：
--   SELECT user_id FROM `users` WHERE `school_id` = 0;
-- 再按列表 UPDATE 回 0，避免误伤其他真正为 NULL 的用户。
