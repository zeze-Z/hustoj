<?php
/**
 * 课程列表页面
 * 支持按学科、标签筛选；未筛选时按“系列课程:<名称>”标签聚合展示系列卡片
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/cache_start.php');
require_once('./include/setlang.php');
require_once("./include/set_get_key.php");
require_once('./include/my_func.inc.php');

// 学生登录后无权访问课件中心，游客和教师/管理员可查看
if (isset($_SESSION[$OJ_NAME . '_' . 'user_id']) && !is_teacher_or_admin()) {
    header('Location: index.php');
    exit;
}

$page_title = "$MSG_COURSE_LIST - $OJ_NAME";

$series_prefix = '系列课程:';

// 系列名称统一取冒号后的 trim 值，兼容“系列课程:Python”和“系列课程: Python”。
$normalize_series_name = function ($token) use ($series_prefix) {
    $token = trim((string)$token);
    if (strncmp($token, $series_prefix, strlen($series_prefix)) !== 0) {
        return '';
    }
    return trim(substr($token, strlen($series_prefix)));
};
$course_series_name = function ($tags) use ($normalize_series_name) {
    foreach (explode(',', (string)$tags) as $tag) {
        $name = $normalize_series_name($tag);
        if ($name !== '') {
            return $name;
        }
    }
    return '';
};
// 普通标签按完整 token 匹配；系列标签按规范化后的系列名精确匹配。
$course_has_tag = function ($tags, $target) use ($normalize_series_name, $series_prefix, $course_series_name) {
    $target = trim((string)$target);
    if ($target === '') {
        return false;
    }
    if (strncmp($target, $series_prefix, strlen($series_prefix)) === 0) {
        $target_name = $normalize_series_name($target);
        return $target_name !== '' && $course_series_name($tags) === $target_name;
    }
    foreach (explode(',', (string)$tags) as $tag) {
        if (trim($tag) === $target) {
            return true;
        }
    }
    return false;
};

$subject_id = isset($_GET['subject']) ? intval($_GET['subject']) : 0;
$tag_filter = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// 系列标签筛选属精确 token；普通标签/关键词筛选保持课程级列表；默认与学科+默认组合启用聚合
$series_filter = (strncmp($tag_filter, $series_prefix, strlen($series_prefix)) === 0)
    ? trim(substr($tag_filter, strlen($series_prefix)))
    : '';
$aggregate = ($tag_filter === '' && $search_keyword === '');

// 查询启用的学科
$subjects_sql = "SELECT id, name FROM course_subject WHERE status = 1 ORDER BY sort_order ASC, id ASC";
$subjects = pdo_query($subjects_sql);

// 构建课程查询条件（标签 token 精确过滤在 PHP 侧完成）
$where_conditions = array("c.status = 1");
$params = array();

if ($subject_id > 0) {
    $where_conditions[] = "c.subject_id = ?";
    $params[] = $subject_id;
}

if ($search_keyword !== '') {
    $where_conditions[] = "(c.title LIKE ? OR c.tags LIKE ? OR c.description LIKE ?)";
    $params[] = "%" . $search_keyword . "%";
    $params[] = "%" . $search_keyword . "%";
    $params[] = "%" . $search_keyword . "%";
}

$where_sql = implode(" AND ", $where_conditions);

// 分页
$page_size = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

// 查询课程列表（is_new 标记近30天内上架的课件）
$sql = "SELECT c.*, s.name as subject_name,
        (c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS is_new
        FROM course c
        INNER JOIN course_subject s ON c.subject_id = s.id
        WHERE $where_sql
        ORDER BY c.sort_order ASC, c.id ASC";
$matched_courses = pdo_query($sql, ...$params);
if (!is_array($matched_courses)) {
    $matched_courses = array();
}

// 标签筛选：按完整 token 精确匹配（系列标签同样精确到系列名）
if ($tag_filter !== '') {
    $matched_courses = array_values(array_filter($matched_courses, function ($course) use ($course_has_tag, $tag_filter) {
        return $course_has_tag($course['tags'] ?? '', $tag_filter);
    }));
}

if ($aggregate) {
    // 聚合：同系列合成一个系列卡片，代表课取系列内排序第一的课程；无系列课程保持单课
    $grouped = array();
    foreach ($matched_courses as $course) {
        $series_name = $course_series_name($course['tags'] ?? '');
        if ($series_name === '') {
            $course['is_series'] = false;
            $grouped[] = $course;
            continue;
        }
        $key = 'series:' . $series_name;
        if (!isset($grouped[$key])) {
            $course['is_series'] = true;
            $course['series_name'] = $series_name;
            $course['series_course_count'] = 0;
            $course['series_lesson_count'] = 0;
            $grouped[$key] = $course;
        }
        // 系列卡片不使用代表课的价格/购买状态，这里仅汇总计数
        $grouped[$key]['series_course_count']++;
        $grouped[$key]['series_lesson_count'] += intval($course['lesson_count']);
    }
    $view_pool = array_values($grouped);
} else {
    $view_pool = $matched_courses;
}

// 先聚合出卡片集合，再计数分页，保证系列不跨页、不出现空白页
$total = count($view_pool);
$total_pages = $total > 0 ? intval(ceil($total / $page_size)) : 1;
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $page_size;
$courses = array_slice($view_pool, $offset, $page_size);

// 获取当前用户已购买的课程（仅用于单课卡片，系列卡片不展示购买状态）
$purchased_courses = array();
if (isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    $user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
    $order_sql = "SELECT course_id FROM course_order WHERE user_id = ? AND pay_status = 1";
    $order_result = pdo_query($order_sql, $user_id);
    foreach ($order_result as $order) {
        $purchased_courses[$order['course_id']] = true;
    }
}

// 注入封面图路径（约定 upload/course_cover/{id}.jpg，无封面为空串，模板回退渐变+图标占位）
if (is_array($courses)) {
    foreach ($courses as $i => $c) {
        $courses[$i]['cover_url'] = get_course_cover($c['id']);
    }
}

// 模板变量
$view_subjects = $subjects;
$view_courses = $courses;
$view_purchased = $purchased_courses;
$view_current_subject = $subject_id;
$view_current_tag = $tag_filter;
$view_search_keyword = $search_keyword;
$view_page = $page;
$view_total_pages = $total_pages;
$view_total_courses = $total;
$view_aggregate = $aggregate;

require("template/" . $OJ_TEMPLATE . "/course.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
