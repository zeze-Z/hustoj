<?php
require_once("./include/db_info.inc.php");
require_once("./include/const.inc.php");
require_once("./include/feishu_notify.php");

header('Content-Type: application/json');

// 检查用户登录状态
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    echo json_encode([
        'code' => -1,
        'msg' => '请先登录'
    ]);
    exit();
}

// 只允许POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'code' => -1,
        'msg' => '请求方法错误'
    ]);
    exit();
}

// 校验CSRF令牌
if (!isset($_SESSION[$OJ_NAME.'_'.'postkey']) || !isset($_POST['postkey']) || $_SESSION[$OJ_NAME.'_'.'postkey'] != $_POST['postkey']) {
    echo json_encode([
        'code' => -1,
        'msg' => '页面已过期，请刷新后重试'
    ]);
    exit();
}
unset($_SESSION[$OJ_NAME.'_'.'postkey']);

// 获取参数
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$email = trim($_POST['email'] ?? '');

// 参数校验
if (empty($title) || empty($content)) {
    echo json_encode([
        'code' => -1,
        'msg' => '标题和内容都不能为空'
    ]);
    exit();
}

// 长度校验并截断
$title = mb_substr($title, 0, 50);
$content = mb_substr($content, 0, 200);

// 邮箱校验
if (!empty($email) && !preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
    echo json_encode([
        'code' => -1,
        'msg' => '请输入正确的邮箱地址'
    ]);
    exit();
}

// XSS清洗：去掉HTML标签，转义特殊字符
$title = htmlspecialchars(strip_tags($title), ENT_QUOTES, 'UTF-8');
$content = htmlspecialchars(strip_tags($content), ENT_QUOTES, 'UTF-8');
if (!empty($email)) {
    $email = htmlspecialchars(strip_tags($email), ENT_QUOTES, 'UTF-8');
}

// 获取用户信息
$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
$nick = $_SESSION[$OJ_NAME . '_nick'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'];
$time = date('Y-m-d H:i:s');

// 构造飞书通知内容
$notify_content = "**用户信息**：\n";
$notify_content .= "- 用户ID：$user_id\n";
if (!empty($nick)) {
    $notify_content .= "- 昵称：$nick\n";
}
if (!empty($email)) {
    $notify_content .= "- 邮箱：$email\n";
}
$notify_content .= "- IP地址：$ip\n";
$notify_content .= "- 提交时间：$time\n\n";
$notify_content .= "**标题**：$title\n\n";
$notify_content .= "**内容**：\n$content";

// 发送飞书通知
$result = feishu_notify("用户反馈 - $title", $notify_content, 'info');

if ($result) {
    echo json_encode([
        'code' => 0,
        'msg' => '提交成功，感谢您的反馈！'
    ]);
} else {
    echo json_encode([
        'code' => -1,
        'msg' => '提交失败，请稍后重试'
    ]);
}
?>