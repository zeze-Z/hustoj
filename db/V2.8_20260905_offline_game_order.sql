-- V2.8: 离线游戏兑换订单表
-- 用途：记录用户积分兑换离线游戏安装包的订单和授权信息

CREATE TABLE IF NOT EXISTS `offline_game_order` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(48) NOT NULL COMMENT '兑换用户',
  `school_name` VARCHAR(100) NOT NULL COMMENT '学校名称',
  `room_name` VARCHAR(100) NOT NULL COMMENT '机房名称',
  `license_code` TEXT NOT NULL COMMENT 'JSON授权码（含签名）',
  `expire_date` DATE NOT NULL COMMENT '授权有效期',
  `order_no` VARCHAR(32) NOT NULL COMMENT '订单号',
  `point_amount` INT NOT NULL DEFAULT 50 COMMENT '消耗积分',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  UNIQUE KEY `uk_user_order` (`user_id`, `order_no`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expire_date` (`expire_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='离线游戏兑换订单';

-- 回滚SQL
-- DROP TABLE IF EXISTS `offline_game_order`;
