<?php
require_once('../include/db_info.inc.php');
require_once('../include/setlang.php');
require_once('../include/my_func.inc.php');

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    http_response_code(403);
    exit('Forbidden');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: point_card_list.php');
    exit();
}
require_once('../include/check_post_key.php');

$ids = isset($_POST['card_id']) && is_array($_POST['card_id']) ? $_POST['card_id'] : [];
$clean = [];
foreach ($ids as $v) {
    $iv = intval($v);
    if ($iv > 0) $clean[] = $iv;
}
if (empty($clean)) {
    header('Location: point_card_list.php');
    exit();
}
$place = implode(',', array_fill(0, count($clean), '?'));

// 只允许禁用未兑换（status=0），已兑换/已禁用不动
$stmt = pdo_query("UPDATE point_card SET status = 2, update_time = NOW()
            WHERE status = 0 AND id IN ($place)", $clean);
$disabled_count = $stmt->rowCount();

// 飞书通知：充值卡禁用（不发送 card_secret，仅 ID 摘要）
if (intval($disabled_count) > 0) {
    $admin_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
    send_point_card_disable_notify($clean, $disabled_count, $admin_id);
}

header('Location: point_card_list.php');
exit();
