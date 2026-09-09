-- V2.9: 积分商品通用化
-- 1) 新建 point_goods 商品表，离线游戏离线包为首条商品记录
-- 2) offline_game_order 通用化：加 product_key 列后重命名为 point_goods_order
-- 3) 历史离线游戏流水 point_log type 4→6（积分商品），使流水页正确归类

-- ============ 1. 商品表 ============
CREATE TABLE IF NOT EXISTS `point_goods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '自增ID',
  `product_key` VARCHAR(32) NOT NULL COMMENT '商品唯一标识（履约路由键，如 offline_game）',
  `title` VARCHAR(100) NOT NULL COMMENT '商品名称',
  `description` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '商品描述（前端横幅/弹窗展示）',
  `icon` VARCHAR(16) NOT NULL DEFAULT '' COMMENT '图标（emoji，可为空）',
  `price` INT NOT NULL DEFAULT 0 COMMENT '兑换价格（积分）',
  `original_price` INT NULL DEFAULT NULL COMMENT '划线价（积分，NULL=不展示划线价）',
  `download_url` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '下载链接',
  `validity_days` INT NOT NULL DEFAULT 365 COMMENT '有效期（天）',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态：1=上架 0=下架',
  `sort` INT NOT NULL DEFAULT 0 COMMENT '排序权重（小的在前）',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  UNIQUE KEY `uk_product_key` (`product_key`),
  KEY `idx_status_sort` (`status`, `sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分商品表';

-- 首条商品：值迁移自硬编码。INSERT IGNORE：重跑不覆盖管理员已改配置
INSERT IGNORE INTO `point_goods`
  (`product_key`, `title`, `description`, `icon`, `price`, `original_price`,
   `download_url`, `validity_days`, `status`, `sort`)
VALUES
  ('offline_game', '课前游戏集合 · 离线安装包',
   '机房没网也能玩！包含全部17款教育游戏的离线版本，适合无网络的教学环境',
   '📦', 99, 199,
   'https://pan.baidu.com/s/1myPtSsTkkTfrAr5QgnIWsQ?pwd=wzdf',
   365, 1, 0);

-- ============ 2. 订单表通用化 ============
-- ADD COLUMN 带默认值：存量行（仅测试环境测试单）自动回填
ALTER TABLE `offline_game_order`
  ADD COLUMN `product_key` VARCHAR(32) NOT NULL DEFAULT 'offline_game' COMMENT '商品标识（关联 point_goods.product_key）' AFTER `user_id`,
  ADD INDEX `idx_product_key` (`product_key`);

RENAME TABLE `offline_game_order` TO `point_goods_order`;

-- ============ 3. 历史流水类型对齐 ============
UPDATE `point_log` SET `type` = 6
 WHERE `type` = 4 AND `change_point` < 0 AND `relation_id` LIKE 'OG%';

-- ============ 回滚SQL（逆序执行） ============
-- RENAME TABLE `point_goods_order` TO `offline_game_order`;
-- ALTER TABLE `offline_game_order` DROP INDEX `idx_product_key`, DROP COLUMN `product_key`;
-- UPDATE `point_log` SET `type` = 4 WHERE `type` = 6 AND `change_point` < 0 AND `relation_id` LIKE 'OG%';
-- DELETE FROM `point_goods` WHERE `product_key` = 'offline_game';
-- DROP TABLE IF EXISTS `point_goods`;
