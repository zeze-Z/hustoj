<?php
$OJ_CACHE_SHARE = false;
$cache_time = 30;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once("./include/my_func.inc.php");
require_once('./include/setlang.php');
$view_title = "测一测你该不该跳槽 - " . $OJ_NAME;
require("template/" . $OJ_TEMPLATE . "/job_change_test.php");
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>
