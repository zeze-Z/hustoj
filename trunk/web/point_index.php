<?php
/**
 * 我的积分中心
 *  - 展示余额、发卡网充值入口、兑换表单
 *  - 展示积分流水与课件购买记录
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');
require_once('./include/cache_start.php');
require_once('./include/setlang.php');
require_once('./include/set_post_key.php');

if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header('location:loginpage.php?url=point_index.php');
    exit();
}

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];

// flash 信息（兑换结果回跳后展示）
$view_flash_type = isset($_GET['msg_type']) ? trim($_GET['msg_type']) : '';
$view_flash      = isset($_GET['msg']) ? trim($_GET['msg']) : '';
if (!in_array($view_flash_type, ['success', 'error'], true)) {
    $view_flash_type = '';
    $view_flash = '';
}

// 当前积分余额
$view_balance = intval(point_get_balance($user_id));

// 流水分页与筛选
$type_filter = isset($_GET['type']) ? intval($_GET['type']) : 0;
$valid_types = [
    POINT_LOG_TYPE_CARD,
    POINT_LOG_TYPE_COURSE,
    POINT_LOG_TYPE_ADMIN,
    POINT_LOG_TYPE_SYSTEM,
];
if (!in_array($type_filter, $valid_types, true)) {
    $type_filter = 0;
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_sql = "WHERE user_id = ?";
$params = [$user_id];
if ($type_filter > 0) {
    $where_sql .= " AND type = ?";
    $params[] = $type_filter;
}

$count_rows = pdo_query("SELECT COUNT(*) AS total FROM point_log $where_sql", $params);
$total = isset($count_rows[0]['total']) ? intval($count_rows[0]['total']) : 0;
$total_pages = $total > 0 ? (int)ceil($total / $per_page) : 0;

$logs_sql = "SELECT id, change_point, balance, type, relation_id, remark, create_time
             FROM point_log $where_sql
             ORDER BY id DESC
             LIMIT $per_page OFFSET $offset";
$view_logs = pdo_query($logs_sql, $params);
if (!is_array($view_logs)) $view_logs = [];

// 已购买课件（不再使用 c.price，统一使用 course_order.amount / license_type / pay_channel）
$courses_sql = "SELECT co.order_no, co.course_id, co.license_type, co.pay_channel,
                       co.amount, co.pay_status, co.pay_time, co.created_at,
                       c.title AS course_title
                  FROM course_order co
                  INNER JOIN course c ON co.course_id = c.id
                 WHERE co.user_id = ? AND co.pay_status = 1
                 ORDER BY co.id DESC
                 LIMIT 50";
$view_courses = pdo_query($courses_sql, $user_id);
if (!is_array($view_courses)) $view_courses = [];

$view_user_id         = $user_id;
$view_type_filter     = $type_filter;
$view_page            = $page;
$view_total_pages     = $total_pages;
$view_total           = $total;
$view_card_value      = POINT_CARD_VALUE;
$view_faka_url        = 'https://www.qianxun1688.com/details/8E870BDF';
$view_postkey         = isset($_SESSION[$OJ_NAME.'_'.'postkey']) ? $_SESSION[$OJ_NAME.'_'.'postkey'] : '';
$page_title           = '我的积分 - ' . $OJ_NAME;

require('template/' . $OJ_TEMPLATE . '/point_index.php');

if (file_exists('./include/cache_end.php')) {
    require_once('./include/cache_end.php');
}
