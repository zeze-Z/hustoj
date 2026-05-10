<?php
$OJ_CACHE_SHARE = false;
$cache_time = 30;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');

$view_title = "AI猜画游戏";

/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/ai_drawing_game.html");
/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>
