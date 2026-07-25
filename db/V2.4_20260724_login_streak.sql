-- ============================================================
-- V2.4  2026-07-24  新客连续登录分批奖励（20积分分批发放）
-- ============================================================
-- 目标：新用户注册/激活发6分，其后连续登录7天每天2分（共14），合计20分。
--       断签即结束（login_reward_status=2），不再发放。
-- 关联需求：docs/需求_20积分分批发放活动.md (v1.1)
-- ============================================================

-- 1) 新增3个字段
ALTER TABLE `users`
  ADD COLUMN `login_streak` INT NOT NULL DEFAULT 0
    COMMENT '连续登录天数（0=尚未开始签到）' AFTER `new_user_reward_claimed`,
  ADD COLUMN `last_login_reward_date` DATE NULL DEFAULT NULL
    COMMENT '最后领取登录奖励日期（领6分当天即播种为当天，兼做每日幂等键）' AFTER `login_streak`,
  ADD COLUMN `login_reward_status` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT '签到状态：0-进行中/未开始，1-已完成(满7天)，2-已断签结束' AFTER `last_login_reward_date`;

-- 2) 存量用户全部排除（强制执行）
--    功能全新，无任何存量处于"签到进行中"。默认值为0(进行中)，
--    若不迁移会导致存量用户下次登录被误发2分。
--    一句搞定：不依赖 created_at/reg_time 等时间字段，不会误伤边界新用户。
UPDATE `users` SET `login_reward_status` = 2 WHERE `login_reward_status` = 0;

-- 说明：不创建 idx_login_reward_date 索引。
--   签到判定热路径为 SELECT/UPDATE ... WHERE user_id=?（主键已索引），
--   last_login_reward_date 无范围/批量查询需求，加索引只增加登录写开销。

-- ============================================================
-- 回滚（按逆序）
-- ============================================================
-- ALTER TABLE `users`
--   DROP COLUMN `login_reward_status`,
--   DROP COLUMN `last_login_reward_date`,
--   DROP COLUMN `login_streak`;
