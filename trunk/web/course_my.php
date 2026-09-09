<?php
/**
 * 我的订单页面
 * 显示用户的课件订单与离线游戏安装包订单
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

// 积分商品订单（含已过期历史；同用户未过期订单唯一，总量小，不分页）
$view_og_orders = pdo_query(
    "SELECT order_no, product_key, school_name, room_name, license_code, expire_date, point_amount, create_time
       FROM `point_goods_order` WHERE user_id = ? ORDER BY id DESC LIMIT 50",
    $user_id
);

// 商品 key → 名称映射（订单列表/详情展示用，兜底覆盖内置商品）
$view_goods_titles = [];
$_goods_rows = pdo_query("SELECT product_key, title FROM `point_goods`");
if (is_array($_goods_rows)) {
    foreach ($_goods_rows as $_goods_row) {
        $view_goods_titles[$_goods_row['product_key']] = $_goods_row['title'];
    }
}
$view_goods_titles += ['offline_game' => '离线游戏安装包'];

// 商品 key → 下载链接映射（详情弹窗下载按钮跟随商品表配置；无链接的商品隐藏按钮）
$view_goods_urls = [];
$_goods_url_rows = pdo_query("SELECT product_key, download_url FROM `point_goods` WHERE download_url <> ''");
if (is_array($_goods_url_rows)) {
    foreach ($_goods_url_rows as $_goods_url_row) {
        $view_goods_urls[$_goods_url_row['product_key']] = $_goods_url_row['download_url'];
    }
}

// 模板变量
$view_courses = $courses;
$view_total = $total;
$view_page = $page;
$view_total_pages = $total_pages;
$view_error = isset($error_message) ? $error_message : '';
$view_success = isset($success_message) ? $success_message : '';
$page_title = "$MSG_MY_COURSE - $OJ_NAME";

// 教师推广奖励说明卡片显示判定：系统教师身份用 users.role='teacher'（privilege 表不一定有 rightstr='teacher' 记录）
$_role_rows = pdo_query("SELECT `role` FROM `users` WHERE `user_id` = ? AND `defunct` = 'N' LIMIT 1", $user_id);
$view_is_teacher = (!empty($_role_rows) && isset($_role_rows[0]['role']) && $_role_rows[0]['role'] === 'teacher')
    || isset($_SESSION[$OJ_NAME.'_'.'administrator']);

require("template/" . $OJ_TEMPLATE . "/course_my.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
