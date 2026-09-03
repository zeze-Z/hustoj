-- ============================================================
-- V2.7  2026-09-02  教师推广积分奖励
-- ============================================================
-- 目标：教师找客服导入班级学生建立关联（users.bind_teacher_id），
--       管理员在「教师推广统计」页手动结算上周数据；
--       每周若 60% 关联学生登录过（floor取整）→ 教师得 5 积分，
--       连续 4 周每周达标每周发，断周即结束，最多 20 积分。
-- 关联需求：.claude/plans/教师推广积分奖励_需求.md (v1.2)
-- 兼容 MySQL 8.0.28
-- ============================================================

-- 1) users 新增 bind_teacher_id 字段（学生归属教师 user_id）
--    无替代字段（school_id 是学校非教师，group_name 是自由文本不可靠），必须新增。
ALTER TABLE `users`
  ADD COLUMN `bind_teacher_id` VARCHAR(48) NULL DEFAULT NULL
    COMMENT '学生归属教师user_id（教师推广关联）' AFTER `role`;

-- 2) 新增教师推广周结算状态表
--    必要性：断周判定、按钮置灰（未达标/已完成/已中断）、当前周次/4 展示都需要
--    "即使未发分也有结算记录"。point_log 只在发分成功时写流水，查不出"未达标的那周"，
--    无法替代。uk_teacher_week 防同一周重复生成快照。
CREATE TABLE IF NOT EXISTS `teacher_promo_stat` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `teacher_id` VARCHAR(48) NOT NULL COMMENT '教师user_id',
  `week_start` DATE NOT NULL COMMENT '结算周起始日(周一日期)',
  `week_no` TINYINT NOT NULL COMMENT '周期内周次 1-4',
  `bound_count` INT NOT NULL DEFAULT 0 COMMENT '当周关联学生总数',
  `active_count` INT NOT NULL DEFAULT 0 COMMENT '当周登录过的学生数（accesstime落在时间窗内）',
  `threshold` INT NOT NULL DEFAULT 0 COMMENT '达标阈值=floor(bound_count*0.6)',
  `is_qualified` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否达标 0/1',
  `is_granted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否已发放 0/1',
  `grant_point` INT NOT NULL DEFAULT 0 COMMENT '发放积分数',
  `settle_time` DATETIME NULL DEFAULT NULL COMMENT '发分结算时间',
  `gen_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '快照生成时间',
  `status` TINYINT NOT NULL DEFAULT 0 COMMENT '周期状态：0进行中，1已完成(满4周)，2已中断(某周未达标)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_teacher_week` (`teacher_id`, `week_start`),
  KEY `idx_week_start` (`week_start`),
  KEY `idx_teacher_status` (`teacher_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='教师推广周结算状态记录';

-- 说明：不创建 teacher_promo_log（明细事件账）。
--   point_log(type=5) 记录每笔发分流水，teacher_promo_stat 记录每周结算状态，
--   审计链路已完整，无需额外表。

-- ============================================================
-- 回滚（按逆序）
-- ============================================================
-- DROP TABLE IF EXISTS `teacher_promo_stat`;
-- ALTER TABLE `users` DROP COLUMN `bind_teacher_id`;
