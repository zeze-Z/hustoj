<?php
/**
 * 我的获取页面
 * 显示用户已获取的所有课程
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/cache_start.php');
require_once('./include/setlang.php');
require_once("./include/set_get_key.php");

// 检查登录状态
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header("location:loginpage.php");
    exit();
}

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// 处理重新发送邮件请求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once('./include/check_post_key.php');
    require_once('./include/course_mail.php');

    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($order_id > 0 && !empty($email)) {
        // 查询订单信息
        $order_sql = "SELECT co.*, c.* FROM course_order co
                      INNER JOIN course c ON co.course_id = c.id
                      WHERE co.id = ? AND co.user_id = ? AND co.pay_status = 1";
        $result = pdo_query($order_sql, $order_id, $user_id);

        if (!empty($result)) {
            $order = $result[0];

            // 检查发送频率限制（1分钟内只能发一次）
            $last_sent = isset($order['mail_sent_at']) ? strtotime($order['mail_sent_at']) : 0;
            $can_send = (time() - $last_sent) >= 60;

            if ($can_send) {
                $course_data = array(
                    'title' => $order['title'],
                    'courseware_link' => $order['courseware_link'],
                    'courseware_code' => $order['courseware_code'],
                    'lesson_plan_link' => $order['lesson_plan_link'],
                    'lesson_plan_code' => $order['lesson_plan_code']
                );

                if (send_course_mail($email, $course_data)) {
                    // 更新邮件发送状态
                    pdo_query(
                        "UPDATE course_order SET mail_status = 1, mail_sent_at = NOW() WHERE id = ?",
                        $order_id
                    );
                    $success_message = $MSG_RESEND_SUCCESS;
                } else {
                    $error_message = $MSG_MAIL_SEND_FAILED;
                }
            } else {
                $error_message = $MSG_MAIL_TOO_FREQUENT;
            }
        } else {
            $error_message = $MSG_ORDER_NOT_FOUND;
        }
    } else {
        $error_message = $MSG_INVALID_REQUEST;
    }
}

// 查询已获取课程总数
$count_sql = "SELECT COUNT(*) as total FROM course_order WHERE user_id = ? AND pay_status = 1";
$count_result = pdo_query($count_sql, $user_id);
$total = $count_result[0]['total'];
$total_pages = $total > 0 ? ceil($total / $per_page) : 0;

// 查询已获取课程列表
$sql = "SELECT co.*, c.title, c.price
        FROM course_order co
        INNER JOIN course c ON co.course_id = c.id
        WHERE co.user_id = ? AND co.pay_status = 1
        ORDER BY co.created_at DESC
        LIMIT $per_page OFFSET $offset";
$courses = pdo_query($sql, $user_id);

// 模板变量
$view_courses = $courses;
$view_total = $total;
$view_page = $page;
$view_total_pages = $total_pages;
$view_error = isset($error_message) ? $error_message : '';
$view_success = isset($success_message) ? $success_message : '';
$page_title = "$MSG_MY_COURSE - $OJ_NAME";

require("template/" . $OJ_TEMPLATE . "/course_my.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
