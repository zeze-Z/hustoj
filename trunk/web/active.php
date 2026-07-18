<?php
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
require_once("./include/const.inc.php");
require_once("./include/my_func.inc.php");

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
            // 发放 20 积分（新用户注册奖励）-- 仅在未领取时发放，防止重复
            if ($reward_claimed == 0) {
                $register_point_reward = 20;
                point_tx_begin();
                $point_result = point_apply_change(
                    $user_id,
                    $register_point_reward,
                    POINT_LOG_TYPE_SYSTEM,
                    null,
                    '新用户注册奖励'
                );
                if ($point_result['success']) {
                    point_tx_commit();
                    // 标记已领取，防止登录补发重复
                    pdo_query("UPDATE `users` SET `new_user_reward_claimed`=1 WHERE `user_id`=?", $user_id);
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
