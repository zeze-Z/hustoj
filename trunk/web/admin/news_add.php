<?php
ob_start();
require_once ("admin-header.php");
require_once("../include/check_post_key.php");
if(!(isset($_SESSION[$OJ_NAME.'_'.'administrator']))){
  echo "<a href='../loginpage.php'>Please Login First!</a>";
  exit(1);
}

require_once("../include/db_info.inc.php");
require_once("../include/my_func.inc.php");

//contest_id
$title = $_POST['title'];
$content = $_POST['content'];
$top = isset($_POST['top']) && $_POST['top']=="on" ? 1 : 0;

$user_id = $_SESSION[$OJ_NAME.'_'.'user_id'];




// 判断是更新还是插入
if (isset($_POST['news_id']) && $_POST['news_id'] != '') {
    // 更新新闻
    $news_id = intval($_POST['news_id']);
    $sql = "UPDATE news SET `title`=?,`content`=?,`time`=now(),`importance`=? WHERE `news_id`=?";
    pdo_query($sql,$title,$content,$top,$news_id);
} else {
    // 插入新新闻
    $sql = "INSERT INTO news(`user_id`,`title`,`content`,`time`,`importance`) VALUES(?,?,?,now(),?)";
    pdo_query($sql,$user_id,$title,$content,$top);
}
$sessionDataKey = $OJ_NAME.'_'."_MENU_NEWS_CACHE";
unset($_SESSION[$sessionDataKey]);

echo "<script>alert('保存成功！');window.location.href=\"news_list.php\";</script>";
?>
