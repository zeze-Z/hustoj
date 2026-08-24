<?php
/**
 * 课程详情页面
 * 展示课程信息、课件/教案预览
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');
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
$preview_price = floatval($course['preview_price']);
$source_price = floatval($course['source_price']);
$is_free = ($preview_price == 0 && $source_price == 0);

// 获取用户权限（使用统一公共函数）
$user_id = isset($_SESSION[$OJ_NAME . '_' . 'user_id']) ? $_SESSION[$OJ_NAME . '_' . 'user_id'] : 0;
$permission = get_user_course_permission($user_id, $course_id);

// 管理员豁免（自动拥有全部权限）统一在 get_user_course_permission() 中处理
$has_preview_license = $permission['has_full_preview'];
$has_source_license = $permission['has_source'];
$has_only_preview = $permission['has_full_preview'] && !$permission['has_source'];
$upgrade_price = $permission['upgrade_price'];

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

// 腾讯文档链接（用于按钮直接打开）
$view_courseware_full_preview_url = validatePreviewUrl($course['courseware_full_preview_url']);
$view_lesson_plan_full_preview_url = validatePreviewUrl($course['lesson_plan_full_preview_url']);
$view_courseware_link = validatePreviewUrl($course['courseware_link']);
$view_lesson_plan_link = validatePreviewUrl($course['lesson_plan_link']);

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
$view_upgrade_price = $upgrade_price; // 升级补差价金额（公共函数统一计算，避免负数）
$view_is_free = $is_free;
$view_has_source_resource = $permission['has_source_resource'];
$view_is_full_preview_free = $permission['is_full_preview_free'];
$view_is_source_free = $permission['is_source_free'];
$page_title = "$MSG_COURSE: " . htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8') . " - $OJ_NAME";

require("template/" . $OJ_TEMPLATE . "/course_info.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
