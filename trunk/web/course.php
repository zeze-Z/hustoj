<?php
/**
 * 课程列表页面
 * 支持按学科、标签筛选
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/cache_start.php');
require_once('./include/setlang.php');
require_once("./include/set_get_key.php");

$page_title = "$MSG_COURSE_LIST - $OJ_NAME";

// 获取学科列表（只显示启用的）
$subject_id = isset($_GET['subject']) ? intval($_GET['subject']) : 0;
$tag_filter = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// 查询启用的学科
$subjects_sql = "SELECT id, name FROM course_subject WHERE status = 1 ORDER BY sort_order ASC, id ASC";
$subjects = pdo_query($subjects_sql);

// 构建课程查询条件
$where_conditions = array("c.status = 1");
$params = array();

// 学科过滤
if ($subject_id > 0) {
    $where_conditions[] = "c.subject_id = ?";
    $params[] = $subject_id;
}

// 标签过滤
if (!empty($tag_filter)) {
    $where_conditions[] = "c.tags LIKE ?";
    $params[] = "%" . $tag_filter . "%";
}

// 搜索关键词
if (!empty($search_keyword)) {
    $where_conditions[] = "(c.title LIKE ? OR c.tags LIKE ? OR c.description LIKE ?)";
    $params[] = "%" . $search_keyword . "%";
    $params[] = "%" . $search_keyword . "%";
    $params[] = "%" . $search_keyword . "%";
}

$where_sql = implode(" AND ", $where_conditions);

// 查询课程列表
$sql = "SELECT c.*, s.name as subject_name
        FROM course c
        INNER JOIN course_subject s ON c.subject_id = s.id
        WHERE $where_sql
        ORDER BY c.sort_order ASC, c.id ASC";
$courses = pdo_query($sql, ...$params);

// 获取当前用户已购买的课程
$purchased_courses = array();
if (isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    $user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
    $order_sql = "SELECT course_id FROM course_order WHERE user_id = ? AND pay_status = 1";
    $order_result = pdo_query($order_sql, $user_id);
    foreach ($order_result as $order) {
        $purchased_courses[$order['course_id']] = true;
    }
}

// 模板变量
$view_subjects = $subjects;
$view_courses = $courses;
$view_purchased = $purchased_courses;
$view_current_subject = $subject_id;
$view_current_tag = $tag_filter;
$view_search_keyword = $search_keyword;

require("template/" . $OJ_TEMPLATE . "/course.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
