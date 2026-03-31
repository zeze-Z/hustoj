<?php
require_once("../../include/check_post_key.php");
require_once("../../include/db_info.inc.php");

// 仅管理员可操作
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "Permission denied";
    exit(1);
}

$id = intval($_POST['id']);
$status = intval($_POST['status']);

if ($id <= 0 || !in_array($status, [0, 1])) {
    echo "Invalid parameters";
    exit(1);
}

$sql = "UPDATE `course` SET `status` = ? WHERE `id` = ?";

try {
    pdo_query($sql, $status, $id);
    echo "success";
} catch (Exception $e) {
    echo "Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
