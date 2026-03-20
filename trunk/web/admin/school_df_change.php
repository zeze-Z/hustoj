<?php
require("admin-header.php");
require_once("../include/check_get_key.php");

// 仅超管可切换状态
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    exit();
}

$id = intval($_GET['id']);

if ($id <= 0) {
    exit();
}

// 切换状态
$sql = "UPDATE `school` SET `status` = NOT `status` WHERE `id` = ?";
pdo_query($sql, $id);
?>

<script>history.go(-1);</script>
