<?php 
ini_set("memory_limit", "1024M");
ini_set("max_execution_time", "600");
ini_set("display_errors", "Off");
error_reporting(0);

require_once("../include/db_info.inc.php");
require_once ("../include/my_func.inc.php");
if (file_exists('../include/school.php')) {
    require_once('../include/school.php');
}
if(isset($OJ_LOG_ENABLED) && $OJ_LOG_ENABLED){
	$params = json_encode($_REQUEST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	$logger->info($params);
}

// 未登录则跳转到登录页
if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])||isset($_SESSION[$OJ_NAME.'_'.'problem_editor'])||isset($_SESSION[$OJ_NAME.'_'.'password_setter']))){
	echo "<!DOCTYPE html><html><head><meta charset='utf-8'><script>window.top.location='../loginpage.php';</script></head></html>";
	exit(1);
}

if(file_exists("../template/$OJ_TEMPLATE/css.php")) require_once("../template/$OJ_TEMPLATE/css.php");
if(file_exists("../lang/$OJ_LANG.php")) require_once("../lang/$OJ_LANG.php");
?>
<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel=stylesheet href='../include/hoj.css' type='text/css'>
<script src="../template/bs3/jquery.min.js"></script>
<script>
$("document").ready(function (){
  $("form").append("<div id='csrf' />");
  $("#csrf").load("../csrf.php");
});

</script>
<iframe src="../session.php" height=0px width=0px ></iframe>


