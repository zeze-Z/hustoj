<?php
/**
 * 新客连续登录分批奖励（V2.4）
 *
 * 业务规则见 docs/需求_20积分分批发放活动.md (v1.2) 第四节：
 *   - 注册/激活发 6 分，并播种签到基线（last_login_reward_date=当天, login_streak=0, status=0）
 *   - 其后连续登录 7 天，每天 2 分（共 14），第 7 天标记完成
 *   - 断签即结束（status=2），不再发放；严格起始：播种次日未登录即断签（v1.2）
 *
 * 并发安全：grant_login_streak_reward() 用条件 UPDATE 抢"今日名额"，天然防双发。
 * 健壮性：任何异常只记日志、不抛出，确保不影响正常登录（验收7）。
 */

require_once(__DIR__ . '/my_func.inc.php');

// 签到状态常量
if (!defined('LOGIN_REWARD_IN_PROGRESS')) define('LOGIN_REWARD_IN_PROGRESS', 0); // 进行中/未开始
if (!defined('LOGIN_REWARD_COMPLETED'))    define('LOGIN_REWARD_COMPLETED', 1);    // 已完成（满7天）
if (!defined('LOGIN_REWARD_BROKEN'))       define('LOGIN_REWARD_BROKEN', 2);       // 已断签结束

// 活动参数
if (!defined('LOGIN_STREAK_TARGET'))       define('LOGIN_STREAK_TARGET', 7);       // 连续登录目标天数
if (!defined('LOGIN_STREAK_DAILY_POINT'))  define('LOGIN_STREAK_DAILY_POINT', 2);  // 每日签到积分
if (!defined('LOGIN_REWARD_CARD_LINGER_DAYS')) define('LOGIN_REWARD_CARD_LINGER_DAYS', 3); // 终态(完成/断签)后签到卡仍展示的天数，超过则隐藏

/**
 * 注册/激活发放6分后播种签到基线（登录补发、激活补发同样调用）。
 * 内部按 login_reward_status 判定，确保存量用户不受影响（验收：只有V2.4后新用户进入签到）：
 *   - status=0 (进行中/真正新用户)：播种基线 -> last_login_reward_date=当天, login_streak=0,
 *                                     login_reward_status=0, new_user_reward_claimed=1
 *   - status=2 (存量/灰区被V2.4迁移排除)：仅置 new_user_reward_claimed=1，不动签到字段（不进签到）
 *   +6 仍照发（属 V2.1 应得权益），仅"进签到"按 status 闸门。
 *
 * 注意：本函数不开事务；每个用户仅命中下面两条 UPDATE 之一，故无事务也安全。
 *       调用方若在 6分发放事务内调用，两条 UPDATE 自然随事务提交/回滚。
 *
 * @param string $user_id
 */
function seed_login_reward($user_id) {
    if (empty($user_id)) return;
    // 真正新用户(status=0)：播种签到基线进入签到 + 标记已领取
    pdo_query(
        "UPDATE `users`
            SET `last_login_reward_date` = CURDATE(),
                `login_streak` = 0,
                `login_reward_status` = ?,
                `new_user_reward_claimed` = 1
          WHERE `user_id` = ? AND `login_reward_status` = ?",
        LOGIN_REWARD_IN_PROGRESS, $user_id, LOGIN_REWARD_IN_PROGRESS
    );
    // 存量/灰区被V2.4迁移排除(status=2)：仅标记已领取，不进签到
    // （严格保证"只有V2.4后新用户看到签到"；+6仍照发，属V2.1应得权益）
    pdo_query(
        "UPDATE `users` SET `new_user_reward_claimed` = 1
          WHERE `user_id` = ? AND `login_reward_status` <> ?",
        $user_id, LOGIN_REWARD_IN_PROGRESS
    );
}

/**
 * 发放每日连续登录奖励（登录成功后调用）。
 * 详见需求文档第四节 step1~7；step7 为条件 UPDATE。
 *
 * @param string $user_id
 * @return array {granted,points,streak,status,reason}
 */
function grant_login_streak_reward($user_id) {
    $result = [
        'granted' => false, 'points' => 0, 'streak' => 0,
        'status'  => LOGIN_REWARD_IN_PROGRESS, 'reason' => '',
    ];
    if (empty($user_id)) {
        $result['reason'] = 'empty user_id';
        return $result;
    }

    try {
        // 日期统一取 MySQL 端，避免 PHP/MySQL 时区偏差导致跨天误判
        $rows = pdo_query(
            "SELECT `login_streak`, `last_login_reward_date`, `login_reward_status`,
                    CURDATE() AS today, DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday
             FROM `users` WHERE `user_id` = ?",
            $user_id
        );
        if (empty($rows)) {
            $result['reason'] = 'user not found';
            return $result;
        }
        $row = $rows[0];
        $status = intval($row['login_reward_status']);
        $streak = intval($row['login_streak']);
        $last   = $row['last_login_reward_date']; // 'YYYY-MM-DD' 或 null
        $today  = $row['today'];
        $yesterday = $row['yesterday'];

        $result['streak'] = $streak;
        $result['status'] = $status;

        // step1: 已完成/断签 -> 跳过
        if ($status !== LOGIN_REWARD_IN_PROGRESS) {
            $result['reason'] = 'not in progress';
            return $result;
        }
        // step2: 今日已领 -> 跳过
        if ($last !== null && $last === $today) {
            $result['reason'] = 'already claimed today';
            return $result;
        }

        // step3~4: 计算新 streak / 是否断签（严格连续，v1.2）
        // 统一规则：last=昨天才连续（含首次签到：播种次日登录，streak 0 -> 1）；
        // 播种后隔天/多天首次登录同样视为断签，直接结束不发。
        if ($last === $yesterday) {
            // 连续
            $new_streak = $streak + 1;
        } else {
            // 断签（含首次跳过）-> 结束，不发
            pdo_query(
                "UPDATE `users` SET `login_reward_status` = ?
                 WHERE `user_id` = ? AND `login_reward_status` = ?
                   AND (`last_login_reward_date` IS NULL OR `last_login_reward_date` <> ?)",
                LOGIN_REWARD_BROKEN, $user_id, LOGIN_REWARD_IN_PROGRESS, $today
            );
            $result['status'] = LOGIN_REWARD_BROKEN;
            $result['reason'] = 'streak broken';
            return $result;
        }

        // step5~6: 发2积分；满7天标记完成
        $new_status = ($new_streak >= LOGIN_STREAK_TARGET)
            ? LOGIN_REWARD_COMPLETED
            : LOGIN_REWARD_IN_PROGRESS;
        $points = LOGIN_STREAK_DAILY_POINT;

        // step7: 条件 UPDATE 抢今日名额（防并发双发）；成功后再发积分，整段事务保证原子
        point_tx_begin();
        $affected = pdo_query(
            "UPDATE `users`
                SET `login_streak` = ?,
                    `last_login_reward_date` = CURDATE(),
                    `login_reward_status` = ?
              WHERE `user_id` = ?
                AND `login_reward_status` = ?
                AND (`last_login_reward_date` IS NULL OR `last_login_reward_date` <> CURDATE())",
            $new_streak, $new_status, $user_id, LOGIN_REWARD_IN_PROGRESS
        );

        // pdo_query 异常时返回 -1
        if ($affected > 0) {
            $point_result = point_apply_change(
                $user_id, $points, POINT_LOG_TYPE_SYSTEM, null, '连续登录奖励'
            );
            if ($point_result['success']) {
                point_tx_commit();
                $result['granted'] = true;
                $result['points'] = $points;
                $result['streak'] = $new_streak;
                $result['status'] = $new_status;
                $result['reason'] = 'granted';
            } else {
                point_tx_rollback();
                $result['reason'] = 'point_apply_change failed: ' . $point_result['message'];
                error_log("login_reward: point_apply_change failed for {$user_id}: " . $point_result['message']);
            }
        } else {
            // 并发已被今日名额抢走，或状态刚被改为非0 -> 不发
            point_tx_rollback();
            $result['reason'] = ($affected === -1) ? 'update error' : 'today slot already taken (concurrent)';
        }
    } catch (Exception $e) {
        point_tx_rollback();
        $result['reason'] = 'exception: ' . $e->getMessage();
        error_log("login_reward: exception for {$user_id}: " . $e->getMessage());
    }

    return $result;
}

/**
 * 读取用户签到展示信息（welcome.php 等前端用）。
 * @param string $user_id
 * @return array|null {login_streak,last_login_reward_date,login_reward_status,today}
 */
function get_login_reward_info($user_id) {
    if (empty($user_id)) return null;
    $rows = pdo_query(
        "SELECT `login_streak`, `last_login_reward_date`, `login_reward_status`, CURDATE() AS today,
                DATEDIFF(CURDATE(), `last_login_reward_date`) AS days_since_last
         FROM `users` WHERE `user_id` = ?",
        $user_id
    );
    if (empty($rows)) return null;
    return $rows[0];
}

/**
 * 渲染签到进度卡 HTML（welcome.php 激活页 / point_index.php 积分中心共用，
 * 给后续登录的用户一个常驻可查的签到状态入口）。
 * @param array|null $streak_info get_login_reward_info() 的返回
 * @param int $reward_points 注册奖励积分（文案用，默认6）
 * @return string HTML（无数据返回空串）
 */
function login_reward_streak_card_html($streak_info, $reward_points = 6) {
    if (empty($streak_info)) return '';
    $target = LOGIN_STREAK_TARGET;
    $daily  = LOGIN_STREAK_DAILY_POINT;
    $total  = $target * $daily;

    $s_streak = intval($streak_info['login_streak']);
    $s_status = intval($streak_info['login_reward_status']);
    $s_today  = ($streak_info['last_login_reward_date'] !== null
                 && $streak_info['last_login_reward_date'] === $streak_info['today']);

    // 终态(完成/断签)后只展示 LINGER_DAYS 天，超过则隐藏卡片（进行中常驻）。
    // last_login_reward_date 为 NULL（存量老用户 status=2 排除态）亦隐藏。
    if ($s_status === LOGIN_REWARD_COMPLETED || $s_status === LOGIN_REWARD_BROKEN) {
        $days_since = array_key_exists('days_since_last', $streak_info) ? $streak_info['days_since_last'] : null;
        if ($days_since === null || intval($days_since) >= LOGIN_REWARD_CARD_LINGER_DAYS) {
            return '';
        }
    }

    if ($s_status === 1) {           // 已完成
        $title = "🎉 {$target}天签到完成";
        $desc  = "七天签到任务达成，恭喜获得{$total}积分奖励！";
        $pct   = 100;
    } elseif ($s_status === 2) {      // 断签
        $title = '⚠️ 签到已中断';
        $desc  = "连续签到遗憾中断了，快去关注小红书解锁更多福利吧！";
        $pct   = min(100, $s_streak * 100 / $target);
    } else {                          // 进行中
        $pct = min(100, $s_streak * 100 / $target);
        if ($s_streak === 0) {
            $title = "🎁 注册奖励 {$reward_points} 积分已到账";
            $desc  = $s_today ? "明天起连续登录每天 +{$daily} 积分，连签{$target}天共得{$total}积分"
                              : "今日登录即可领{$daily}积分，连签{$target}天共得{$total}积分";
        } else {
            $title = $s_today ? "✅ 今日已签到 +{$daily} 积分" : "🔥 今日登录可领 +{$daily} 积分";
            $desc  = "已连续 {$s_streak}/{$target} 天，" . ($s_today ? '明天记得继续哦' : '今日登录即可累计');
        }
    }

    $pct     = intval($pct);
    $title_h = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $desc_h  = htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');

    $html  = '<div style="max-width:700px;margin:0 auto 24px;background:#fff;border-radius:16px;padding:24px 28px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border:1px solid #f0f0f0;">';
    $html .= '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">';
    $html .= '<div style="font-size:17px;font-weight:600;color:#333;">' . $title_h . '</div>';
    $html .= '<div style="font-size:14px;color:#888;">' . $s_streak . '/' . $target . ' 天</div>';
    $html .= '</div>';
    $html .= '<div style="background:#f0f0f5;border-radius:8px;height:10px;overflow:hidden;margin-bottom:12px;">';
    $html .= '<div style="width:' . $pct . '%;height:100%;background:linear-gradient(90deg,#667eea 0%,#764ba2 100%);transition:width .6s ease;"></div>';
    $html .= '</div>';
    $html .= '<div style="font-size:14px;color:#888;line-height:1.6;">' . $desc_h . '</div>';
    $html .= '</div>';
    return $html;
}
