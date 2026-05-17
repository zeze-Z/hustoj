<?php
/**
 * 充值卡兑换端点：POST 提交卡号/卡密，调用 point_redeem_card。
 * 异常文案中不包含卡密，绝不写日志。
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');
require_once('./include/setlang.php');

if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header('location:loginpage.php?url=point_index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location:point_index.php');
    exit();
}

require_once('./include/check_post_key.php');

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
$card_no = isset($_POST['card_no']) ? trim((string)$_POST['card_no']) : '';
$card_secret = isset($_POST['card_secret']) ? trim((string)$_POST['card_secret']) : '';
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

if ($card_no === '' || $card_secret === '') {
    $msg = '请填写卡号和卡密';
    $msg_type = 'error';
} else {
    $result = point_redeem_card($user_id, $card_no, $card_secret, $ip);
    if ($result['success']) {
        $msg_type = 'success';
        $add = isset($result['add']) ? intval($result['add']) : POINT_CARD_VALUE;
        $msg = '兑换成功，已到账 ' . $add . ' 积分';
    } else {
        $msg_type = 'error';
        $msg = isset($result['message']) ? $result['message'] : '兑换失败，请稍后重试';
    }
}

// 不在 URL 中回显卡密，仅回显结果文案
header('Location: point_index.php?msg_type=' . urlencode($msg_type) . '&msg=' . urlencode($msg));
exit();
