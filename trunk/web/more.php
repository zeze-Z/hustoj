<?php
$cache_time = 30;
$OJ_CACHE_SHARE = false;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/memcache.php');
require_once('./include/my_func.inc.php');
require_once('./include/setlang.php');

// 生成CSRF postkey：保证直奔 more.php 的用户也有 postkey，兑换表单不被"页面已过期"拒绝。
// 采用控制器内生成（同 admin/teacher_promo_list.php 的做法），不 include set_post_key.php——
// 后者会向输出缓冲 echo 一个隐藏 input，落在模板 <!DOCTYPE html> 之前会触发怪异模式。
if (!isset($_SESSION[$OJ_NAME . '_' . 'postkey'])) {
    $_SESSION[$OJ_NAME . '_' . 'postkey'] = strtoupper(substr(MD5(($_SESSION[$OJ_NAME . '_' . 'user_id'] ?? '') . rand(0, 9999999)), 0, 10));
}

$view_title = "更多功能 - " . $OJ_NAME;

/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/more.php");

/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>