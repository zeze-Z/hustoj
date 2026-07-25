<?php
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
require_once("./include/const.inc.php");
require_once("./include/my_func.inc.php");
require_once("./include/login_reward.php");

session_start();

/**
 * 用户账户激活功能
 * 通过激活码激活被禁用的用户账户，激活成功后自动登录并发放积分
 */
$code = trim($_GET['code']);

// 检查是否开启邮件确认功能且激活码不为空
if (isset($OJ_EMAIL_CONFIRM) && $OJ_EMAIL_CONFIRM && strlen($code) == 18) {
    // 先查询用户信息（含新用户奖励领取标记，用于积分幂等发放）
    $sql = "SELECT `user_id`, `nick`, `new_user_reward_claimed` FROM `users` WHERE `activecode`=? AND `activecode`!='' AND `defunct`='Y'";
    $user_info = pdo_query($sql, $code);

    if (count($user_info) > 0) {
        $user_id = $user_info[0]['user_id'];
        $nick = $user_info[0]['nick'];
        $reward_claimed = $user_info[0]['new_user_reward_claimed'];

        // 激活账号
        $sql = "UPDATE `users` SET `defunct`='N', `activecode`='' WHERE `activecode`=? AND `activecode`!='' AND `defunct`='Y'";
        $result = pdo_query($sql, $code);

        if ($result > 0) {
            // 发放 6 积分（新用户注册奖励）-- 仅在未领取时发放，防止重复
            if ($reward_claimed == 0) {
                $register_point_reward = 6;
                point_tx_begin();
                $point_result = point_apply_change(
                    $user_id,
                    $register_point_reward,
                    POINT_LOG_TYPE_SYSTEM,
                    null,
                    '新用户注册奖励'
                );
                if ($point_result['success']) {
                    // 播种签到基线（含 new_user_reward_claimed=1 + 3个签到字段），与6分发放同事务保证原子
                    seed_login_reward($user_id);
                    point_tx_commit();
                } else {
                    point_tx_rollback();
                    // 积分发放失败不阻断激活流程，仅记录日志
                    error_log("Active: failed to grant {$register_point_reward} points to user {$user_id}: " . $point_result['message']);
                }
            }

            // 自动登录
            $_SESSION[$OJ_NAME . '_' . 'user_id'] = $user_id;
            $_SESSION[$OJ_NAME . '_' . 'nick'] = $nick;

            // 查询用户权限
            $sql = "SELECT `rightstr` FROM `privilege` WHERE `user_id`=?";
            $priv_result = pdo_query($sql, $user_id);
            foreach ($priv_result as $row) {
                $_SESSION[$OJ_NAME . '_' . $row['rightstr']] = true;
            }
            $_SESSION[$OJ_NAME . '_' . 'ac'] = array();
            $_SESSION[$OJ_NAME . '_' . 'sub'] = array();

            // 记录登录日志（与 login.php 同格式）。
            // 必须写：init.php 的 IP 一致性检查($OJ_LIMIT_TO_1_IP)会取 loginlog 最新 IP 比对，
            // 激活自动登录若不写 loginlog，lastip 为空 != 当前 IP，welcome.php 会被 init.php 自动登出。
            $login_ip = isset($ip) ? $ip : $_SERVER['REMOTE_ADDR'];
            pdo_query("INSERT INTO `loginlog`(user_id,password,ip,time) VALUES(?,'login ok',?,NOW())", $user_id, $login_ip);

            // 跳转到 welcome.php
            header("Location: welcome.php?status=activated");
            exit(0);
        }
    }
}

// 激活失败
header("Location: welcome.php?status=failed&reason=expired");
exit(0);
?>
