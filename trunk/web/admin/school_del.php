<?php
require("admin-header.php");
require_once("../include/set_get_key.php");

// 仅超管可删除学校
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

// CSRF检查
require_once("../include/check_get_key.php");

$school_id = intval($_GET['id']);

if ($school_id <= 0) {
    echo "<script>alert('Invalid ID'); history.go(-1);</script>";
    exit();
}

// 检查是否有用户关联
$sql = "SELECT COUNT(*) as cnt FROM `users` WHERE `school_id` = ?";
$result = pdo_query($sql, $school_id);
$cnt = $result[0]['cnt'];

if ($cnt > 0) {
    echo "<script>alert('Cannot delete: $cnt users are associated with this school'); history.go(-1);</script>";
    exit();
}

// 执行删除
$sql = "DELETE FROM `school` WHERE `id` = ?";
$result = pdo_query($sql, $school_id);

if ($result !== false) {
    echo "<script>alert('$MSG_DELETE $MSG_SUCCESS'); window.location.href='school_list.php';</script>";
} else {
    echo "<script>alert('$MSG_DELETE $MSG_FAILED'); history.go(-1);</script>";
}
?>