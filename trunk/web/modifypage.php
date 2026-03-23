<?php $cache_time = 10;
$OJ_CACHE_SHARE = false;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
$view_title = "Welcome To Online Judge";
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    $redirect = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'index.php';
    header("Location: loginpage.php?redirect=" . urlencode($redirect));
    exit(0);
}

$sql = "SELECT `school`,`school_id`,`nick`,`email` FROM `users` WHERE `user_id`=?";
$result = pdo_query($sql, $_SESSION[$OJ_NAME . '_' . 'user_id']);
$row = $result[0];

// 获取学校列表（用于下拉选择）
$school_list = array();
if (file_exists('./include/school.php')) {
    require_once('./include/school.php');
    $school_list = getSchoolList(true);
}


/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/modifypage.php");
/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>

