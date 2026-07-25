<?php
/**
 * 我的积分中心
 *  - 展示余额、发卡网充值入口、兑换表单
 *  - 展示统一的积分流水（含课件购买、可直接跳转课件详情）
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');
require_once("./include/login_reward.php");
require_once('./include/cache_start.php');
require_once('./include/setlang.php');
require_once('./include/set_post_key.php');

if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header('location:loginpage.php?url=point_index.php');
    exit();
}

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];

// 签到进度（积分中心常驻展示，供后续登录的用户随时查看签到状态）
$view_streak_info   = get_login_reward_info($user_id);
$view_reward_points = 6;

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

// 流水主表 LEFT JOIN 课件订单 + 课件标题：
//   - type=2 (课件购买) 时 relation_id 是 order_no，可关联到 course_order
//   - 其他类型不会命中 JOIN，course_* 字段为 NULL
$where_sql = "WHERE pl.user_id = ?";
$params = [$user_id];
if ($type_filter > 0) {
    $where_sql .= " AND pl.type = ?";
    $params[] = $type_filter;
}

$count_rows = pdo_query("SELECT COUNT(*) AS total FROM point_log pl $where_sql", $params);
$total = isset($count_rows[0]['total']) ? intval($count_rows[0]['total']) : 0;
$total_pages = $total > 0 ? (int)ceil($total / $per_page) : 0;

$logs_sql = "SELECT pl.id, pl.change_point, pl.balance, pl.type, pl.relation_id,
                    pl.remark, pl.create_time,
                    co.course_id    AS co_course_id,
                    co.license_type AS co_license_type,
                    co.pay_channel  AS co_pay_channel,
                    c.title         AS co_course_title
               FROM point_log pl
          LEFT JOIN course_order co
                 ON pl.type = " . POINT_LOG_TYPE_COURSE . "
                AND pl.relation_id = co.order_no
                AND co.user_id = pl.user_id
          LEFT JOIN course c
                 ON c.id = co.course_id
              $where_sql
           ORDER BY pl.id DESC
              LIMIT $per_page OFFSET $offset";
$view_logs = pdo_query($logs_sql, $params);
if (!is_array($view_logs)) $view_logs = [];

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
