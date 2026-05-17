-- V1.9_20260607_point_payment.sql
-- 平台积分充值与课件积分支付体系
-- 兼容 MySQL 8.0.28
--
-- 变更内容：
--   1. users 表转为 InnoDB 引擎，并新增 point 字段（平台积分余额）
--   2. 新建 point_card 表（积分充值卡）
--   3. 新建 point_log 表（积分流水）

-- =============================================================
-- 1. users 表：转 InnoDB 并新增积分余额字段
-- =============================================================

-- 1.1 将 users 表存储引擎转为 InnoDB（若已为 InnoDB 则相当于无操作）
ALTER TABLE `users` ENGINE=InnoDB;

-- 1.2 新增积分余额字段
--     MySQL 8.0.28 不支持 ADD COLUMN IF NOT EXISTS 语法；
--     如目标库已存在 `point` 字段，请将下面这条语句注释后再执行。
ALTER TABLE `users`
  ADD COLUMN `point` INT NOT NULL DEFAULT 0 COMMENT '平台积分余额';

-- =============================================================
-- 2. 充值卡表 point_card
-- =============================================================

CREATE TABLE IF NOT EXISTS `point_card` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `batch_no` VARCHAR(64) NOT NULL COMMENT '生成批次号',
  `card_no` VARCHAR(64) NOT NULL COMMENT '卡号',
  `card_secret` VARCHAR(64) NOT NULL COMMENT '卡密',
  `status` TINYINT NOT NULL DEFAULT 0 COMMENT '状态：0未兑换 1已兑换 2已禁用',
  `redeem_user_id` VARCHAR(48) DEFAULT NULL COMMENT '兑换用户ID',
  `redeem_time` DATETIME DEFAULT NULL COMMENT '兑换时间',
  `redeem_ip` VARCHAR(45) DEFAULT NULL COMMENT '兑换IP',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_card_no` (`card_no`),
  KEY `idx_batch_no` (`batch_no`),
  KEY `idx_status` (`status`),
  KEY `idx_redeem_user_id` (`redeem_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分充值卡';

-- =============================================================
-- 3. 积分流水表 point_log
-- =============================================================

CREATE TABLE IF NOT EXISTS `point_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(48) NOT NULL COMMENT '用户ID',
  `change_point` INT NOT NULL COMMENT '积分变化，正数收入，负数支出',
  `balance` INT NOT NULL COMMENT '交易后余额',
  `type` TINYINT NOT NULL COMMENT '类型：1充值卡兑换 2课件购买 3管理员调整 4系统操作',
  `relation_id` VARCHAR(64) DEFAULT NULL COMMENT '关联业务ID，如卡号、课件订单号',
  `remark` VARCHAR(255) DEFAULT NULL COMMENT '备注',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分流水';

-- =============================================================
-- 回滚脚本（取消注释后按逆序执行）
-- =============================================================
-- 说明：回滚脚本不会自动恢复 users 表的原存储引擎。
--      若原 users 表为 MyISAM 且确需恢复，请人工评估数据与外键影响后执行：
--          ALTER TABLE `users` ENGINE=MyISAM;
--      生产环境通常应继续使用 InnoDB，不建议回退到 MyISAM。

-- DROP TABLE IF EXISTS `point_log`;
-- DROP TABLE IF EXISTS `point_card`;
-- ALTER TABLE `users` DROP COLUMN `point`;
