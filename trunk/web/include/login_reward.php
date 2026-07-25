<?php
/**
 * 新客连续登录分批奖励（V2.4）
 *
 * 业务规则见 docs/需求_20积分分批发放活动.md (v1.1) 第四节：
 *   - 注册/激活发 6 分，并播种签到基线（last_login_reward_date=当天, login_streak=0, status=0）
 *   - 其后连续登录 7 天，每天 2 分（共 14），第 7 天标记完成
 *   - 断签即结束（status=2），不再发放
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

/**
 * 注册/激活发放6分后播种签到基线（登录补发同样调用）。
 *   last_login_reward_date = 当天   -> 防止注册/激活当天重复发 + 兼做每日幂等键
 *   login_streak            = 0      -> 尚未开始签到
 *   login_reward_status     = 0      -> 进行中
 *   new_user_reward_claimed = 1      -> 6分已发（与 V2.1 标记统一，替代各处单独 UPDATE）
 *
 * 注意：本函数不开事务，调用方应在自身的 6分发放事务内调用以保证原子性。
 *
 * @param string $user_id
 */
function seed_login_reward($user_id) {
    if (empty($user_id)) return;
    pdo_query(
        "UPDATE `users`
            SET `last_login_reward_date` = CURDATE(),
                `login_streak` = 0,
                `login_reward_status` = ?,
                `new_user_reward_claimed` = 1
          WHERE `user_id` = ?",
        LOGIN_REWARD_IN_PROGRESS, $user_id
    );
}

/**
 * 发放每日连续登录奖励（登录成功后调用）。
 * 详见需求文档第四节 step1~8；step8 为条件 UPDATE。
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

        // step3~5: 计算新 streak / 是否断签
        if ($streak === 0) {
            // 首次签到：领6后任意一天均可开始（宽松起始）
            $new_streak = 1;
        } elseif ($last === $yesterday) {
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

        // step6~7: 发2积分；满7天标记完成
        $new_status = ($new_streak >= LOGIN_STREAK_TARGET)
            ? LOGIN_REWARD_COMPLETED
            : LOGIN_REWARD_IN_PROGRESS;
        $points = LOGIN_STREAK_DAILY_POINT;

        // step8: 条件 UPDATE 抢今日名额（防并发双发）；成功后再发积分，整段事务保证原子
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
        "SELECT `login_streak`, `last_login_reward_date`, `login_reward_status`, CURDATE() AS today
         FROM `users` WHERE `user_id` = ?",
        $user_id
    );
    if (empty($rows)) return null;
    return $rows[0];
}
