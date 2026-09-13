<?php
$cache_time = 30;
$OJ_CACHE_SHARE = false;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/memcache.php');
require_once('./include/my_func.inc.php');
require_once('./include/setlang.php');
$view_title = "课程表生成 - " . $OJ_NAME;
// 登录态：游客可完整使用，登录后导出高清版（客户端渲染，仅作导出分辨率开关）
$view_logged_in = isset($_SESSION[$OJ_NAME.'_'.'user_id']);

// 生成CSRF postkey（登录用户付费去码导出 timetable_qr_free.php 用）：
// 控制器内生成，不 include set_post_key.php——后者会向输出缓冲 echo 隐藏 input，
// 落在模板 <!DOCTYPE html> 之前会触发怪异模式（同 more.php 控制器做法）
if ($view_logged_in && !isset($_SESSION[$OJ_NAME.'_'.'postkey'])) {
    $_SESSION[$OJ_NAME.'_'.'postkey'] = strtoupper(substr(MD5(($_SESSION[$OJ_NAME.'_'.'user_id'] ?? '') . rand(0, 9999999)), 0, 10));
}
// 去码 CTA 展示用的余额（游客不注入：游客页共享缓存，不得含用户态数据）
$view_tt_balance = $view_logged_in ? point_get_balance($_SESSION[$OJ_NAME.'_'.'user_id']) : null;

/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/timetable.php");

/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>
