<?php
require_once("../include/db_info.inc.php");
require_once("../include/check_post_key.php");

header('Content-Type: application/json; charset=utf-8');

function course_sort_response($success, $message = '') {
    echo json_encode(array('success' => $success, 'message' => $message));
    exit(0);
}

// 仅管理员可操作
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    course_sort_response(false, 'Permission denied');
}

$idsperpage = 25;
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : array();

if ($page <= 0 || count($ids) == 0) {
    course_sort_response(false, 'Invalid parameters');
}

$posted_ids = array();
$posted_id_map = array();
foreach ($ids as $id) {
    $course_id = intval($id);
    if ($course_id <= 0 || isset($posted_id_map[$course_id])) {
        course_sort_response(false, 'Invalid parameters');
    }
    $posted_ids[] = $course_id;
    $posted_id_map[$course_id] = true;
}

$result = pdo_query("SELECT `id` FROM `course` ORDER BY `sort_order` ASC, `id` DESC");
if ($result === -1) {
    course_sort_response(false, 'Database error');
}

$all_ids = array();
foreach ($result as $row) {
    $all_ids[] = intval($row['id']);
}

$start = ($page - 1) * $idsperpage;
$current_page_ids = array_slice($all_ids, $start, $idsperpage);

if (count($current_page_ids) != count($posted_ids)) {
    course_sort_response(false, 'Course list changed, please reload');
}

$current_id_map = array();
foreach ($current_page_ids as $id) {
    $current_id_map[$id] = true;
}

foreach ($posted_ids as $id) {
    if (!isset($current_id_map[$id])) {
        course_sort_response(false, 'Course list changed, please reload');
    }
}

array_splice($all_ids, $start, count($posted_ids), $posted_ids);

$ret = pdo_query("START TRANSACTION");
if ($ret === -1) {
    course_sort_response(false, 'Database error');
}

foreach ($all_ids as $index => $id) {
    $ret = pdo_query("UPDATE `course` SET `sort_order` = ? WHERE `id` = ?", $index + 1, $id);
    if ($ret === -1) {
        pdo_query("ROLLBACK");
        course_sort_response(false, 'Database error');
    }
}

$ret = pdo_query("COMMIT");
if ($ret === -1) {
    course_sort_response(false, 'Database error');
}

course_sort_response(true);
