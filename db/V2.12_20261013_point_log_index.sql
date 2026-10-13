-- V2.12: point_log 表索引优化
-- 背景：include/my_func.inc.php 的 _point_gift_alive_batches() 查询
--      SELECT ... FROM point_log
--       WHERE user_id = ? AND change_point > 0
--       ORDER BY create_time ASC, id ASC
--      原索引 idx_user_id(user_id) 单列不含 create_time，
--      优化器在数据量增长后会走全表扫描 + filesort（测试环境 EXPLAIN 已确认）。
-- 方案：用复合索引 idx_user_time(user_id, create_time) 替换 idx_user_id。
--      - 最左前缀 (user_id) 完全覆盖原 idx_user_id 的所有精确匹配用途
--        （user_action_log.php / point_index.php 等均只按 user_id 等值过滤）
--      - InnoDB 二级索引叶节点自动附加主键 id，实际等价 (user_id, create_time, id)，
--        可完全支持 ORDER BY create_time ASC, id ASC 免 filesort
--      - admin/point_log_list.php 的 user_id LIKE '%...%' 前导通配符本就不用 idx_user_id
-- 执行前提：V1.9 已建表并存在 idx_user_id（全新部署顺序执行 V1.9 → V2.12 亦满足）。
-- 非幂等：ALTER TABLE 不支持 IF EXISTS，重复执行会报错；已执行过的库请勿再跑。

-- 1. 删除冗余的单列索引（已被复合索引最左前缀覆盖）
ALTER TABLE `point_log` DROP INDEX `idx_user_id`;

-- 2. 新增复合索引
ALTER TABLE `point_log`
  ADD INDEX `idx_user_time` (`user_id`, `create_time`);

-- 回滚说明：
--   ALTER TABLE `point_log` DROP INDEX `idx_user_time`;
--   ALTER TABLE `point_log` ADD INDEX `idx_user_id` (`user_id`);
