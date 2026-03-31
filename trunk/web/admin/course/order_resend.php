<?php
/**
 * 手动重发课程邮件
 * 管理员操作：重新发送已支付订单的邮件
 */

require_once('../../include/db_info.inc.php');
require_once('../../include/setlang.php');
require_once('../../include/course_mail.php');

// 强制加载语言文件
if (isset($OJ_LANG)) {
    require_once("../../lang/$OJ_LANG.php");
}

// 权限检查：仅管理员可访问
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../../loginpage.php'>Please Login First!</a>";
    exit(1);
}

// GET 请求验证
require_once('../../include/check_get_key.php');

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    echo "<script>alert('无效请求'); window.location.href='order.php';</script>";
    exit(1);
}

// 查询订单
$order_sql = "SELECT o.*, c.title as course_title, c.courseware_link, c.courseware_code,
              c.lesson_plan_link, c.lesson_plan_code
              FROM course_order o
              LEFT JOIN course c ON o.course_id = c.id
              WHERE o.id = ?";
$order_result = pdo_query($order_sql, $order_id);

if (empty($order_result)) {
    echo "<script>alert('订单不存在'); window.location.href='order.php';</script>";
    exit(1);
}

$order = $order_result[0];

// 检查订单状态
if ($order['pay_status'] != 1) {
    echo "<script>alert('订单未支付，无法发送邮件'); window.location.href='order.php';</script>";
    exit(1);
}

// 构建课程数据
$course_data = array(
    'title' => $order['course_title'],
    'courseware_link' => $order['courseware_link'],
    'courseware_code' => $order['courseware_code'],
    'lesson_plan_link' => $order['lesson_plan_link'],
    'lesson_plan_code' => $order['lesson_plan_code']
);

// 发送邮件
if (send_course_mail($order['email'], $course_data)) {
    // 更新邮件发送状态
    pdo_query(
        "UPDATE course_order SET mail_status = 1, mail_sent_at = NOW() WHERE id = ?",
        $order_id
    );
    echo "<script>alert('邮件发送成功'); window.location.href='order.php';</script>";
} else {
    // 更新邮件发送失败状态
    pdo_query(
        "UPDATE course_order SET mail_status = 2 WHERE id = ?",
        $order_id
    );
    echo "<script>alert('邮件发送失败，请检查SMTP配置'); window.location.href='order.php';</script>";
}
