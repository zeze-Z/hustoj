-- 新增字段：记录用户注册设备指纹与注册IP（区分登录IP），用于限制同一设备短期重复注册薅羊毛
ALTER TABLE `users`
    ADD COLUMN `register_fingerprint` VARCHAR(64) NULL DEFAULT NULL COMMENT '注册设备指纹（UA+分辨率+语言+时区+Canvas哈希）' AFTER `ip`,
    ADD COLUMN `reg_ip` VARCHAR(64) NULL DEFAULT NULL COMMENT '注册时真实IP（与登录IP字段`ip`解耦）' AFTER `register_fingerprint`;

-- 索引：加速7天内同指纹注册查询
CREATE INDEX `idx_register_fingerprint_reg_time` ON `users`(`register_fingerprint`, `reg_time`);

-- 回滚SQL
-- ALTER TABLE `users` DROP COLUMN `register_fingerprint`, DROP COLUMN `reg_ip`;
