-- 新增字段：标记新用户是否已领取注册奖励
-- 用于跨设备登录时检测未领取福利，显示积分动画

ALTER TABLE `users` ADD COLUMN `new_user_reward_claimed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '新用户奖励是否已领取：0-未领取，1-已领取' AFTER `point`;

-- 回滚SQL
-- ALTER TABLE `users` DROP COLUMN `new_user_reward_claimed`;
