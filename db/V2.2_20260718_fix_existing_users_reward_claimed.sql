-- 修复：存量用户登录被误判为新用户跳转 welcome 的问题
-- new_user_reward_claimed 字段（V2.1, 2026-07-16 上线）默认 0，
-- 导致功能上线前注册的存量用户登录时被 login.php 误判为"未领取新用户奖励"，
-- 跳转 welcome.php 并可能误发 20 积分。
-- 将功能上线前注册的存量用户标记为已领取（1），不再触发补发分支；
-- 上线后（reg_time >= 2026-07-16）注册的新用户保持 0，走正常激活/补发流程。
-- reg_time 由 register.php 用 NOW() 写入，上线后注册的新用户必有值且 >= 上线日，
-- 不会被误更新；reg_time IS NULL 覆盖迁移/初始存量用户（db_init 中 reg_time DEFAULT NULL）。

UPDATE `users`
SET `new_user_reward_claimed` = 1
WHERE `new_user_reward_claimed` = 0
  AND (`reg_time` < '2026-07-16 00:00:00' OR `reg_time` IS NULL);

-- 回滚SQL（仅还原存量用户为 0，不影响上线后已正常领取奖励的新用户）
-- UPDATE `users` SET `new_user_reward_claimed` = 0
-- WHERE (`reg_time` < '2026-07-16 00:00:00' OR `reg_time` IS NULL);
