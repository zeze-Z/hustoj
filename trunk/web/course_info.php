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

// 获取用户是否已购买课程
$is_purchased = false;
if (isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    $user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
    $order_sql = "SELECT id FROM course_order WHERE user_id = ? AND course_id = ? AND pay_status = 1";
    $order_result = pdo_query($order_sql, $user_id, $course_id);
    $is_purchased = !empty($order_result);
}

// 根据购买状态决定传给模板的预览URL（核心安全逻辑：未购买用户HTML中不含完整版URL）
if ($is_purchased && !empty($course['courseware_full_preview_url'])) {
    $view_courseware_url = validatePreviewUrl($course['courseware_full_preview_url']);
} else {
    $view_courseware_url = validatePreviewUrl($course['courseware_preview_url']);
}

if ($is_purchased && !empty($course['lesson_plan_full_preview_url'])) {
    $view_lesson_plan_url = validatePreviewUrl($course['lesson_plan_full_preview_url']);
} else {
    $view_lesson_plan_url = validatePreviewUrl($course['lesson_plan_preview_url']);
}

// 是否存在完整版预览（用于控制购买提示遮罩）
$view_has_full_courseware = !empty($course['courseware_full_preview_url']);
$view_has_full_lesson_plan = !empty($course['lesson_plan_full_preview_url']);

// 模板变量
$view_course = $course;
$view_is_purchased = $is_purchased;
$page_title = "$MSG_COURSE: " . htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8') . " - $OJ_NAME";

require("template/" . $OJ_TEMPLATE . "/course_info.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
