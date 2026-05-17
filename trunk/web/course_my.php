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

// 查询已获取课程总数
$count_sql = "SELECT COUNT(*) as total FROM course_order WHERE user_id = ? AND pay_status = 1";
$count_result = pdo_query($count_sql, $user_id);
$total = $count_result[0]['total'];
$total_pages = $total > 0 ? ceil($total / $per_page) : 0;

// 查询已获取课程列表（积分支付改造后：不再使用 c.price，统一展示 co.amount/license_type/pay_channel）
$sql = "SELECT co.*, c.title
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
