<?php
require_once("admin-header.php");
echo "Session status: ";
echo session_status() == PHP_SESSION_ACTIVE ? "ACTIVE" : "NOT ACTIVE";
echo "<br>";
echo "OJ_NAME: " . $OJ_NAME . "<br>";
echo "OJ_LANG: " . $OJ_LANG . "<br>";
echo "OJ_TEMPLATE: " . $OJ_TEMPLATE . "<br>";
echo "Session administrator: " . (isset($_SESSION[$OJ_NAME.'_'.'administrator']) ? "SET" : "NOT SET") . "<br>";
echo "MSG_ADMIN: " . (isset($MSG_ADMIN) ? $MSG_ADMIN : "NOT DEFINED") . "<br>";
echo "MSG_USER: " . (isset($MSG_USER) ? $MSG_USER : "NOT DEFINED") . "<br>";
?>
