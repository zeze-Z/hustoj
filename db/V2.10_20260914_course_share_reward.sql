-- V2.10: 课件分享返佣
-- 1) users 拆分双钱包：新增 point_recharged（充值积分余额），point 仍为总余额（充值+赠送）
--    存量余额一律视为赠送积分（point_recharged 保持默认 0），存量用户充值后才有返佣基数
-- 2) course_order 加分享归因字段：referrer_id（分享者）、share_reward（返佣积分，NULL=未发放）

-- ============ 1. 双钱包字段 ============
ALTER TABLE `users`
  ADD COLUMN `point_recharged` INT NOT NULL DEFAULT 0 COMMENT '充值积分余额（存量余额视为赠送，充值后累计；不参与分享返佣基数的赠送部分）' AFTER `point`;

-- ============ 2. 分享归因字段 ============
ALTER TABLE `course_order`
  ADD COLUMN `referrer_id` VARCHAR(48) NULL COMMENT '分享归因：分享者 user_id（7天cookie归因）' AFTER `counted`,
  ADD COLUMN `share_reward` INT NULL COMMENT '分享返佣积分（NULL=未发放，>=0=已发放）' AFTER `referrer_id`,
  ADD INDEX `idx_referrer` (`referrer_id`);

-- ============ 回滚SQL（逆序执行） ============
-- ALTER TABLE `course_order` DROP INDEX `idx_referrer`, DROP COLUMN `share_reward`, DROP COLUMN `referrer_id`;
-- ALTER TABLE `users` DROP COLUMN `point_recharged`;
