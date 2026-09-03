<?php
/**
 * 教师推广积分奖励 — 核心结算库（V2.7）
 *
 * 业务规则见 .claude/plans/教师推广积分奖励_需求.md (v1.2)：
 *   - 教师找客服导入学生建立关联（users.bind_teacher_id）
 *   - 管理员在「教师推广统计」页手动点「统计上周数据」生成上周快照
 *   - 每周若 60% 关联学生登录过（floor取整）→ 教师得 5 积分
 *   - 连续 4 周每周达标每周发，断周即结束（已发不回收），最多 20 积分
 *   - 结算时间窗：上一个完整自然周（上周一00:00 ~ 上周日23:59）
 *   - "本周登录过"用 users.accesstime 判定（login.php:143 登录后更新）
 *
 * 两步分离设计：
 *   步骤1 teacher_promo_gen_snapshot()：统计上周生成快照写 stat（INSERT IGNORE 防重）
 *   步骤2 teacher_promo_grant()：发分 + UPDATE stat + 发邮件
 *
 * 并发/防重：uk_teacher_week 唯一键保证同一教师同一周只生成一条快照；
 *           grant 二次校验 is_qualified=1 且 is_granted=0 防篡改重复发分。
 */

require_once(__DIR__ . '/my_func.inc.php');

// 活动参数
if (!defined('TEACHER_PROMO_WEEKS'))         define('TEACHER_PROMO_WEEKS',         4);    // 连续4周
if (!defined('TEACHER_PROMO_WEEKLY_POINT'))  define('TEACHER_PROMO_WEEKLY_POINT',  5);    // 每周5积分
if (!defined('TEACHER_PROMO_RATIO'))         define('TEACHER_PROMO_RATIO',         0.6);  // 60%阈值
if (!defined('TEACHER_PROMO_MAX_BIND'))      define('TEACHER_PROMO_MAX_BIND',      200);  // 关联学生上限

// 周期状态常量（与 teacher_promo_stat.status 对应）
if (!defined('TEACHER_PROMO_IN_PROGRESS')) define('TEACHER_PROMO_IN_PROGRESS', 0); // 进行中
if (!defined('TEACHER_PROMO_COMPLETED'))   define('TEACHER_PROMO_COMPLETED',   1); // 已完成(满4周)
if (!defined('TEACHER_PROMO_BROKEN'))      define('TEACHER_PROMO_BROKEN',      2); // 已中断(某周未达标)

/**
 * 计算"上一个完整自然周"的周一日期（YYYY-MM-DD）。
 * MySQL CURDATE()/DATE_SUB 已能算，但本函数供 PHP 侧日志/邮件文案使用。
 * @return string YYYY-MM-DD
 */
function teacher_promo_last_week_start() {
    // 上周一 = 今天 - (今天是周几-1) - 7天；PHP date('N') 1=周一..7=周日
    $dow = intval(date('N')); // 1..7
    $last_monday_ts = strtotime("-" . ($dow - 1 + 7) . " days");
    return date('Y-m-d', $last_monday_ts);
}

/**
 * 取某教师关联的学生 user_id 列表（受上限约束）。
 * @param string $teacher_id
 * @param int    $limit      最大返回数（默认 TEACHER_PROMO_MAX_BIND）
 * @return array ['user_id' => ..., ...] 行数组
 */
function get_teacher_bound_students($teacher_id, $limit = null) {
    if (empty($teacher_id)) return array();
    if ($limit === null) $limit = TEACHER_PROMO_MAX_BIND;
    $sql = "SELECT `user_id` FROM `users`
             WHERE `bind_teacher_id` = ? AND `defunct` = 'N'
             ORDER BY `user_id` LIMIT " . intval($limit);
    return pdo_query($sql, $teacher_id);
}

/**
 * 统计某教师关联学生中，accesstime 落在指定时间窗内的数量（"上周登录过"的人数）。
 * 时间窗用 MySQL 端 DATE(accesstime) 比较，避免时区偏差。
 * 口径必须与 get_teacher_bound_students() 一致：先取按 user_id 排序的前 $limit 个
 * 关联学生（分母受 TEACHER_PROMO_MAX_BIND 上限约束），再统计其中时间窗内登录过的人数，
 * 否则关联学生超上限时分子(active)会大于分母(bound)，60% 达标判定失真。
 * @param string $teacher_id
 * @param string $week_start YYYY-MM-DD（周一）
 * @param string $week_end   YYYY-MM-DD（周日）
 * @param int    $limit      关联学生上限
 * @return int
 */
function get_teacher_active_count($teacher_id, $week_start, $week_end, $limit = null) {
    if (empty($teacher_id) || empty($week_start) || empty($week_end)) return 0;
    if ($limit === null) $limit = TEACHER_PROMO_MAX_BIND;
    $sql = "SELECT COUNT(*) AS cnt FROM (
                SELECT `accesstime` FROM `users`
                 WHERE `bind_teacher_id` = ? AND `defunct` = 'N'
                 ORDER BY `user_id`
                 LIMIT " . intval($limit) . "
            ) t
             WHERE DATE(`accesstime`) BETWEEN ? AND ?";
    $rows = pdo_query($sql, $teacher_id, $week_start, $week_end);
    if (empty($rows)) return 0;
    return intval($rows[0]['cnt']);
}

/**
 * 步骤1：生成上周结算快照（管理员点「统计上周数据」按钮触发）。
 * 遍历所有 bind_teacher_id 关联了学生的教师（去重），对上周生成一条 stat 快照。
 * 已有上周记录的跳过（uk_teacher_week 防重）。
 *
 * @param string|null $week_start 上周一日期，null=自动算上周
 * @return array ['generated'=>生成条数, 'skipped'=>跳过条数, 'details'=>[...]]
 */
function teacher_promo_gen_snapshot($week_start = null) {
    if ($week_start === null) $week_start = teacher_promo_last_week_start();
    $week_end = date('Y-m-d', strtotime($week_start . " +6 days"));

    // 所有有关联学生的教师（去重），且教师本身存在、未禁用、role='teacher'
    // 注意：系统教师身份用 users.role 字段判定，privilege 表不一定有 rightstr='teacher' 记录
    $teachers = pdo_query(
        "SELECT DISTINCT u.bind_teacher_id AS teacher_id
           FROM `users` u
           JOIN `users` t ON t.user_id = u.bind_teacher_id AND t.defunct = 'N' AND t.role = 'teacher'
          WHERE u.bind_teacher_id IS NOT NULL AND u.bind_teacher_id <> ''"
    );
    if (empty($teachers)) {
        return ['generated' => 0, 'skipped' => 0, 'details' => []];
    }

    $generated = 0;
    $skipped = 0;
    $details = array();

    foreach ($teachers as $t) {
        $teacher_id = $t['teacher_id'];

        // 查该教师最新 stat 记录，判定是否允许生成上周快照
        $latest = pdo_query(
            "SELECT `week_no`, `status`, `week_start`
               FROM `teacher_promo_stat`
              WHERE `teacher_id` = ?
              ORDER BY `id` DESC LIMIT 1",
            $teacher_id
        );

        // 已有上周记录 → 跳过（uk_teacher_week 双保险，这里先 SELECT 减少写冲突）
        $exists = pdo_query(
            "SELECT 1 FROM `teacher_promo_stat`
              WHERE `teacher_id` = ? AND `week_start` = ? LIMIT 1",
            $teacher_id, $week_start
        );
        if (!empty($exists)) {
            $skipped++;
            $details[] = ['teacher_id' => $teacher_id, 'action' => 'skip_exists'];
            continue;
        }

        // 确定本周次 week_no
        if (empty($latest)) {
            // 无任何记录 → 首次起算，week_no=1
            $week_no = 1;
        } else {
            $last_status = intval($latest[0]['status']);
            $last_week_no = intval($latest[0]['week_no']);
            // 周期已结束 → 跳过，不再生成新快照
            if ($last_status === TEACHER_PROMO_COMPLETED || $last_status === TEACHER_PROMO_BROKEN) {
                $skipped++;
                $details[] = ['teacher_id' => $teacher_id, 'action' => 'skip_ended', 'status' => $last_status];
                continue;
            }
            $week_no = $last_week_no + 1;
            if ($week_no > TEACHER_PROMO_WEEKS) {
                $skipped++;
                $details[] = ['teacher_id' => $teacher_id, 'action' => 'skip_over_weeks'];
                continue;
            }
        }

        // 关联学生数
        $bound_rows = get_teacher_bound_students($teacher_id);
        $bound_count = count($bound_rows);
        if ($bound_count === 0) {
            $skipped++;
            $details[] = ['teacher_id' => $teacher_id, 'action' => 'skip_no_student'];
            continue;
        }

        // 达标判定
        $threshold = (int)floor($bound_count * TEACHER_PROMO_RATIO);
        $active_count = get_teacher_active_count($teacher_id, $week_start, $week_end);
        $is_qualified = ($active_count >= $threshold) ? 1 : 0;

        // 状态：达标且最后一周→完成；达标→进行中；未达标→中断
        if ($is_qualified && $week_no >= TEACHER_PROMO_WEEKS) {
            $status = TEACHER_PROMO_COMPLETED;
        } elseif ($is_qualified) {
            $status = TEACHER_PROMO_IN_PROGRESS;
        } else {
            $status = TEACHER_PROMO_BROKEN;
        }

        // INSERT IGNORE：uk_teacher_week 防并发/重复生成
        $ret = pdo_query(
            "INSERT IGNORE INTO `teacher_promo_stat`
                (`teacher_id`, `week_start`, `week_no`, `bound_count`,
                 `active_count`, `threshold`, `is_qualified`, `is_granted`,
                 `grant_point`, `status`, `gen_time`)
             VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?, NOW())",
            $teacher_id, $week_start, $week_no, $bound_count,
            $active_count, $threshold, $is_qualified, $status
        );
        if ($ret > 0) {
            $generated++;
            $details[] = [
                'teacher_id' => $teacher_id, 'action' => 'generated',
                'week_no' => $week_no, 'bound' => $bound_count,
                'active' => $active_count, 'threshold' => $threshold,
                'qualified' => $is_qualified, 'status' => $status,
            ];
        } else {
            $skipped++;
            $details[] = ['teacher_id' => $teacher_id, 'action' => 'skip_insert_ignore'];
        }
    }

    return ['generated' => $generated, 'skipped' => $skipped, 'details' => $details];
}

/**
 * 步骤2：发放某教师某周推广奖励（管理员点行尾「奖励推广」按钮触发）。
 * 仅对 is_qualified=1 且 is_granted=0 的快照发分；后端二次校验防篡改。
 *
 * @param string $teacher_id
 * @param string $week_start YYYY-MM-DD（周一）
 * @return array ['success'=>bool, 'message'=>string]
 */
function teacher_promo_grant($teacher_id, $week_start) {
    if (empty($teacher_id) || empty($week_start)) {
        return ['success' => false, 'message' => '参数无效'];
    }

    // 取对应快照
    $rows = pdo_query(
        "SELECT * FROM `teacher_promo_stat`
          WHERE `teacher_id` = ? AND `week_start` = ? LIMIT 1",
        $teacher_id, $week_start
    );
    if (empty($rows)) {
        return ['success' => false, 'message' => '未找到该周结算记录，请先点「统计上周数据」'];
    }
    $row = $rows[0];

    // 二次校验：仅达标且未发可发分（前端按钮置灰，后端再防篡改）
    if (intval($row['is_qualified']) !== 1) {
        return ['success' => false, 'message' => '该教师本周未达标，不可发放'];
    }
    if (intval($row['is_granted']) === 1) {
        return ['success' => false, 'message' => '该教师本周已发放，不可重复'];
    }

    $week_no = intval($row['week_no']);
    $point = TEACHER_PROMO_WEEKLY_POINT;

    // 事务发分
    point_tx_begin();
    $apply = point_apply_change(
        $teacher_id, $point, POINT_LOG_TYPE_PROMO, $week_start,
        '推广周奖励 第' . $week_no . '周'
    );
    if (!$apply['success']) {
        point_tx_rollback();
        return ['success' => false, 'message' => '积分发放失败：' . $apply['message']];
    }

    // 更新快照为已发放（条件 is_granted=0 防并发双发，检查 rowCount 二次确认）
    $affected = pdo_query(
        "UPDATE `teacher_promo_stat`
            SET `is_granted` = 1, `grant_point` = ?, `settle_time` = NOW()
          WHERE `id` = ? AND `is_granted` = 0",
        $point, intval($row['id'])
    );
    if ($affected <= 0) {
        // 并发已被抢走（另一请求已将该条 is_granted 改为1），回滚积分
        point_tx_rollback();
        return ['success' => false, 'message' => '该教师本周已发放，不可重复（并发冲突）'];
    }
    point_tx_commit();

    // 发邮件通知教师（发分已提交，邮件失败只记日志不回滚积分）
    teacher_promo_notify_email($teacher_id, $week_no, $point, $apply['balance']);

    return ['success' => true, 'message' => '发放成功', 'point' => $point, 'balance' => $apply['balance']];
}

/**
 * 推送推广奖励通知邮件给教师。
 * 复用 include/email.class.php 的 email() 函数（参考 register.php）。
 * @param string $teacher_id
 * @param int    $week_no   当前周次
 * @param int    $point     本次发放积分
 * @param int    $balance   发放后余额
 */
function teacher_promo_notify_email($teacher_id, $week_no, $point, $balance) {
    if (empty($teacher_id)) return;
    global $OJ_NAME;
    $site_name = !empty($OJ_NAME) ? $OJ_NAME : 'AI-OJ';
    $site_domain = 'aioj.top';
    try {
        $rows = pdo_query("SELECT `email`, `nick` FROM `users` WHERE `user_id` = ? LIMIT 1", $teacher_id);
        if (empty($rows) || empty($rows[0]['email'])) return;
        $email_addr = $rows[0]['email'];
        $nick = $rows[0]['nick'] ? $rows[0]['nick'] : $teacher_id;

        $subject = "🎉 {$site_name} 教师推广奖励到账通知";
        $text = "【{$site_name}】亲爱的 {$nick} 老师，您好！\n\n" .
            "在您导入学生账号后，您的学生活跃度达标，为您发放 {$point} 积分。\n" .
            "备注：连续活跃4周，每周都有奖励哦！\n" .
            "积分余额：{$balance}\n\n" .
            "继续鼓励学生每周登录，可累计获得更多奖励。\n" .
            "如有疑问请联系客服。";
        $html = "<div style='font-family:\"PingFang SC\",\"Microsoft YaHei\",\"Helvetica Neue\",sans-serif;max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(102,126,234,0.12);'>"
              // 顶部彩条
              . "<div style='height:6px;background:linear-gradient(90deg,#667eea,#764ba2,#f093fb);'></div>"
              // 头部：平台名 + 标题
              . "<div style='padding:28px 36px 16px;text-align:center;'>"
              .   "<div style='font-size:22px;font-weight:700;color:#667eea;letter-spacing:2px;margin-bottom:8px;'>🎓 {$site_name}</div>"
              .   "<div style='font-size:48px;line-height:1;'>🎉</div>"
              .   "<h2 style='margin:12px 0 0;color:#667eea;font-size:22px;font-weight:700;'>推广奖励到账啦！</h2>"
              . "</div>"
              // 正文
              . "<div style='padding:0 36px 28px;'>"
              .   "<p style='font-size:15px;color:#333;line-height:1.8;'>亲爱的 <strong style='color:#667eea;'>" . htmlspecialchars($nick, ENT_QUOTES, 'UTF-8') . "</strong> 老师，您好！</p>"
              .   "<div style='background:linear-gradient(135deg,#f0f3ff,#fce4f8);border-radius:12px;padding:20px 24px;margin:16px 0;text-align:center;'>"
              .     "<div style='font-size:14px;color:#888;margin-bottom:6px;'>本次发放积分</div>"
              .     "<div style='font-size:36px;font-weight:800;color:#667eea;'>+{$point} <span style='font-size:16px;font-weight:400;color:#999;'>积分</span></div>"
              .   "</div>"
              .   "<p style='font-size:15px;color:#333;line-height:1.8;'>在您导入学生账号后，您的学生活跃度达标，为您发放 <strong style='color:#667eea;'>{$point} 积分</strong>。</p>"
              .   "<div style='background:#fff8e1;border-left:4px solid #ffb300;border-radius:0 8px 8px 0;padding:12px 16px;margin:16px 0;'>"
              .     "<span style='font-size:14px;color:#e65100;'>💡 备注：连续活跃4周，每周都有奖励哦！</span>"
              .   "</div>"
              .   "<p style='font-size:14px;color:#888;margin-top:20px;'>💰 当前积分余额：<strong style='color:#333;'>{$balance}</strong></p>"
              . "</div>"
              // 底部：平台名 + 链接
              . "<div style='background:#f9f9fb;padding:16px 36px;text-align:center;border-top:1px solid #eee;'>"
              .   "<p style='font-size:13px;color:#666;margin:0 0 4px;'><strong>{$site_name}</strong> · 教师推广奖励</p>"
              .   "<p style='font-size:12px;color:#bbb;margin:0;'>继续鼓励学生每周登录，可累计获得更多奖励 💪<br>如有疑问请联系客服 · <a href='https://{$site_domain}' style='color:#667eea;text-decoration:none;'>{$site_domain}</a></p>"
              . "</div>"
              . "</div>";

        if (file_exists(__DIR__ . '/email.class.php')) {
            require_once(__DIR__ . '/email.class.php');
            if (function_exists('email')) {
                email($email_addr, $subject, $text, $html);
            }
        }
    } catch (Exception $e) {
        error_log("teacher_promo notify email failed for {$teacher_id}: " . $e->getMessage());
    }
}

/**
 * 读取统计页列表数据：每位教师最新一周快照 + 累计已发积分。
 * 用于 admin/teacher_promo_list.php 展示，打开页面只读调用，不触发统计。
 *
 * @return array 每行含 teacher_id/nick/bound_count/active_count/threshold/
 *               is_qualified/is_granted/week_no/status/grant_point/week_start/total_granted
 */
function get_teacher_promo_list() {
    // 取每个教师最新一条 stat（用子查询取 max(id)）
    $sql = "SELECT s.*, u.nick,
                   (SELECT SUM(s2.grant_point) FROM teacher_promo_stat s2
                     WHERE s2.teacher_id = s.teacher_id AND s2.is_granted = 1) AS total_granted
              FROM `teacher_promo_stat` s
              JOIN `users` u ON u.user_id = s.teacher_id
             WHERE s.id = (SELECT MAX(id) FROM `teacher_promo_stat`
                            WHERE teacher_id = s.teacher_id)
             ORDER BY s.id DESC";
    $rows = pdo_query($sql);
    if (!is_array($rows)) return array();
    return $rows;
}
