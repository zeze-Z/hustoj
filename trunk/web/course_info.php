<?php
/**
 * 课程详情页面
 * 展示课程信息、课件/教案预览
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/cache_start.php');
require_once('./include/setlang.php');

// 获取课程ID
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    echo "<script>alert('课程ID无效'); history.back();</script>";
    exit();
}

// 查询课程信息
$sql = "SELECT c.*, s.name as subject_name
        FROM course c
        INNER JOIN course_subject s ON c.subject_id = s.id
        WHERE c.id = ? AND c.status = 1";
$result = pdo_query($sql, $course_id);

if (empty($result)) {
    echo "<script>alert('课程不存在或已下架'); history.back();</script>";
    exit();
}

$course = $result[0];

// 安全检查：预览链接域名白名单
function validatePreviewUrl($url) {
    if (empty($url)) return '';
    // 只允许 kdocs.cn 域名
    if (preg_match('#^https?://[^/]*kdocs\.cn/#', $url)) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
    return '';
}

// 权限判断（多权限体系）
$has_preview_license = false; // 预览版权限：能看完整预览
$has_source_license = false;  // 原文件权限：能下载原文件
$has_only_preview = false;    // 是否仅拥有预览版（用于升级判断）
$preview_price = floatval($course['preview_price']);
$source_price = floatval($course['source_price']);
$is_free = ($preview_price == 0 && $source_price == 0);

if (isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    $user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];

    // 查询用户该课程的所有已支付权限
    $order_sql = "SELECT license_type FROM course_order WHERE user_id = ? AND course_id = ? AND pay_status = 1";
    $order_result = pdo_query($order_sql, $user_id, $course_id);

    foreach ($order_result as $order) {
        $type = intval($order['license_type']);
        if ($type == 1) $has_preview_license = true;
        if ($type == 2) {
            $has_source_license = true;
            $has_preview_license = true; // 原文件版包含预览版权限
        }
    }

    // 判断是否仅拥有预览版（没有原文件版）
    $has_only_preview = $has_preview_license && !$has_source_license;
}

// 根据权限返回对应预览URL
if ($has_preview_license && !empty($course['courseware_full_preview_url'])) {
    $view_courseware_url = validatePreviewUrl($course['courseware_full_preview_url']);
} else {
    $view_courseware_url = validatePreviewUrl($course['courseware_preview_url']);
}

if ($has_preview_license && !empty($course['lesson_plan_full_preview_url'])) {
    $view_lesson_plan_url = validatePreviewUrl($course['lesson_plan_full_preview_url']);
} else {
    $view_lesson_plan_url = validatePreviewUrl($course['lesson_plan_preview_url']);
}

// 模板变量
$view_course = $course;
$view_has_preview_license = $has_preview_license;
$view_has_source_license = $has_source_license;
$view_has_only_preview = $has_only_preview;
$view_is_purchased = $has_preview_license || $has_source_license; // 兼容旧逻辑
$view_has_full_courseware = !empty($course['courseware_full_preview_url']);
$view_has_full_lesson_plan = !empty($course['lesson_plan_full_preview_url']);
$view_preview_price = $preview_price;
$view_source_price = $source_price;
$view_upgrade_price = $source_price - $preview_price; // 升级补差价金额
$page_title = "$MSG_COURSE: " . htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8') . " - $OJ_NAME";

require("template/" . $OJ_TEMPLATE . "/course_info.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
