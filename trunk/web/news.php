<?php
////////////////////////////Common head
$cache_time = 10;
$OJ_CACHE_SHARE = false;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
$view_title = "动态 - " . $OJ_NAME;

///////////////////////////MAIN
$per = 15;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

$total = 0;
$r = pdo_query("SELECT COUNT(*) AS cnt FROM `news` WHERE `defunct`='N' AND `title` NOT LIKE 'faqs.%'");
if ($r) $total = intval($r[0]['cnt']);
$pages = intval(ceil($total / $per));
if ($pages < 1) $pages = 1;
if ($page > $pages) $page = $pages;

$sid = ($page - 1) * $per;
$sql = "SELECT `news_id`,`user_id`,`title`,`content`,`time`,`importance` FROM `news` "
     . "WHERE `defunct`='N' AND `title` NOT LIKE 'faqs.%' "
     . "ORDER BY `importance` DESC,`time` DESC LIMIT " . $sid . "," . $per;
$result = pdo_query($sql);

$view_news = array();
if ($result) {
    foreach ($result as $row) {
        $cover = "";
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $row['content'], $m)) {
            $cover = htmlentities($m[1], ENT_QUOTES, 'UTF-8');
        }
        $summary = mb_substr(strip_tags($row['content']), 0, 90, 'UTF-8');
        $view_news[] = array(
            "news_id" => intval($row['news_id']),
            "title" => htmlentities($row['title'], ENT_QUOTES, 'UTF-8'),
            "date" => date('Y-m-d', strtotime($row['time'])),
            "summary" => htmlentities($summary, ENT_QUOTES, 'UTF-8'),
            "cover" => $cover,
            "top" => intval($row['importance']) > 0
        );
    }
}

// 分页窗口（供模板 pagination 使用，写法对齐 template/syzoj/index.php）
$pagesperframe = 5;
$frame = intval(ceil($page / $pagesperframe));
$spage = ($frame - 1) * $pagesperframe + 1;
$epage = min($spage + $pagesperframe - 1, $pages);

/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/news.php");
/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>
