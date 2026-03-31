<?php
// CourseModel.php - 课程业务逻辑纯函数

function validate_price($price) {
    return is_numeric($price) && floatval($price) >= 0;
}

function parse_tags($tags_str) {
    if (empty(trim($tags_str))) return [];
    return array_filter(array_map('trim', explode(',', $tags_str)));
}

function is_free($price) {
    return floatval($price) == 0;
}

function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function validate_kdocs_url($url) {
    if (empty($url)) return true; // 允许为空
    return (bool) preg_match('/^https?:\/\/[^\/]*kdocs\.cn\//', $url);
}
