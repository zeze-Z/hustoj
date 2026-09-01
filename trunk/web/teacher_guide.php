<?php
$cache_time = 600;
$OJ_CACHE_SHARE = false;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/memcache.php');
require_once('./include/setlang.php');
$view_title = "学生账号开通指南 - " . $OJ_NAME;

/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/teacher_guide.php");

/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>