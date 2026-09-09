<?php
ob_start(); // admin-header.php 有 HTML 输出，缓冲以保证 GET 动作处理后可 302 重定向（同 news_add.php 先例）
require("admin-header.php");
require_once("../include/set_get_key.php");

if(!isset($_SESSION[$OJ_NAME.'_'.'administrator'])){
  echo "<a href='../loginpage.php'>Please Login First!</a>";
  exit(1);
}

if(isset($OJ_LANG)){
  require_once("../lang/$OJ_LANG.php");
}

// 删除公告后清理其引用的上传文件：仅限 web 根 upload/ 目录内、扩展名白名单、
// 且未被其他公告/题目/竞赛引用(按文件名模糊匹配, upload_json 保留原文件名,
// Copy 功能会复制内容引用同一文件, 宁可误留不可误删)
function clean_orphan_uploads($content){
  $webroot = dirname(__DIR__);
  $uproot  = realpath($webroot . '/upload');
  if($uproot === false || $content == "") return;
  if(!preg_match_all('/src=["\']([^"\']+)["\']/i', $content, $m)) return;
  $allow = array('gif','jpg','jpeg','png','bmp','swf','flv','mp3','wav','wma','wmv','mid',
                 'avi','mpg','asf','rm','rmvb','mp4','pdf','doc','docx','xls','xlsx','ppt',
                 'htm','html','txt','zip','rar','gz','bz2');
  foreach(array_unique($m[1]) as $src){
    $p = parse_url($src, PHP_URL_PATH);
    if($p === false || $p === null || $p === "") continue;
    $p = rawurldecode($p);
    if(strpos($p, '/upload/') === 0){
      // site 绝对路径 /upload/...
    }elseif(strpos($p, 'upload/') === 0){
      $p = '/' . $p;
    }else{
      continue;   // 外站图片或非上传资源, 不处理
    }
    $real = realpath($webroot . $p);
    if($real === false) continue;
    if(strpos($real, $uproot . DIRECTORY_SEPARATOR) !== 0) continue;  // 防目录穿越
    $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
    if(!in_array($ext, $allow)) continue;
    $base = basename($real);
    $r = pdo_query("SELECT COUNT(*) AS c FROM `news` WHERE `content` LIKE ?", "%".$base."%");
    if(intval($r[0]['c']) > 0) continue;
    $r = pdo_query("SELECT COUNT(*) AS c FROM `problem` WHERE `description` LIKE ? OR `input` LIKE ? OR `output` LIKE ? OR `hint` LIKE ?",
                   "%".$base."%", "%".$base."%", "%".$base."%", "%".$base."%");
    if(intval($r[0]['c']) > 0) continue;
    $r = pdo_query("SELECT COUNT(*) AS c FROM `contest` WHERE `description` LIKE ?", "%".$base."%");
    if(intval($r[0]['c']) > 0) continue;
    @unlink($real);
  }
}

// GET 自处理动作：置顶切换 / 物理删除 / 上下线切换（check_get_key 校验，PRG 302 回列表防重放）
if(isset($_GET['op']) && isset($_GET['id'])){
  require_once("../include/check_get_key.php");
  $op = $_GET['op'];
  $nid = intval($_GET['id']);
  if($op == 'top'){
    $r = pdo_query("SELECT `importance` FROM `news` WHERE `news_id`=?",$nid);
    $imp = (isset($r[0]) && intval($r[0]['importance'])>0) ? 0 : 1;
    pdo_query("UPDATE `news` SET `importance`=? WHERE `news_id`=?",$imp,$nid);
  }else if($op == 'delete'){
    $r = pdo_query("SELECT `content` FROM `news` WHERE `news_id`=?",$nid);
    $content = isset($r[0]['content']) ? $r[0]['content'] : "";
    pdo_query("DELETE FROM `news` WHERE `news_id`=?",$nid);
    clean_orphan_uploads($content);   // 行已删除, 剩余引用检查后清理上传文件
  }else if($op == 'df'){
    $r = pdo_query("SELECT `defunct` FROM `news` WHERE `news_id`=?",$nid);
    if(isset($r[0])){
      $nd = ($r[0]['defunct']=='Y') ? 'N' : 'Y';
      pdo_query("UPDATE `news` SET `defunct`=? WHERE `news_id`=?",$nd,$nid);
    }
  }
  unset($_SESSION[$OJ_NAME.'_'."_MENU_NEWS_CACHE"]);
  header("Location: news_list.php");
  exit();
}
?>

<title>News List</title>
<hr>
<center><h3><?php echo $MSG_NEWS."-".$MSG_LIST?></h3></center>

<div class='padding'>

<?php
$sql = "SELECT COUNT('news_id') AS ids FROM `news`";
$result = pdo_query($sql);
$row = $result[0];

$ids = intval($row['ids']);

$idsperpage = 25;
$pages = intval(ceil($ids/$idsperpage));

if(isset($_GET['page'])){ $page = intval($_GET['page']);}
else{ $page = 1;}

$pagesperframe = 5;
$frame = intval(ceil($page/$pagesperframe));

$spage = ($frame-1)*$pagesperframe+1;
$epage = min($spage+$pagesperframe-1, $pages);

$sid = ($page-1)*$idsperpage;

$sql = "";
if(isset($_GET['keyword']) && $_GET['keyword']!=""){
  $keyword = $_GET['keyword'];
  $keyword = "%$keyword%";
  $sql = "SELECT `news_id`,`user_id`,`title`,`time`,`defunct`,`importance` FROM `news` WHERE (title LIKE ?) OR (content LIKE ?) ORDER BY `news_id` DESC";
  $result = pdo_query($sql,$keyword,$keyword);
}else{
  $sql = "SELECT `news_id`,`user_id`,`title`,`time`,`defunct`,`importance` FROM `news` ORDER BY `news_id` DESC LIMIT $sid, $idsperpage";
  $result = pdo_query($sql);
}
?>

<center>
<form action=news_list.php class="form-search form-inline">
  <input type="text" name=keyword class="form-control search-query" placeholder="<?php echo $MSG_TITLE.', '.$MSG_CONTENTS?>">
  <button type="submit" class="form-control"><?php echo $MSG_SEARCH?></button>
</form>
</center>

<center>
  <table width=100% border=1 style="text-align:center;">
    <tr style='height:22px;'>
      <td>ID</td>
      <td>TITLE</td>
      <td>UPDATE</td>
      <td>NOW</td>
      <td>COPY</td>
      <td>TOP</td>
      <td>DEL</td>
    </tr>
    <?php
    foreach($result as $row){
      $nid = $row['news_id'];
      $getkey = $_SESSION[$OJ_NAME.'_'.'getkey'];
      echo "<tr style='height:22px;' news_id='".$nid."'>";
        echo "<td>".$nid."</td>";
        echo "<td><a href='news_add_page.php?id=".$nid."'>".htmlentities($row['title']==""?"Empty":$row['title'],ENT_QUOTES,'UTF-8')."</a>"."</td>";
        echo "<td>".$row['time']."</td>";
        echo "<td><a href='news_list.php?op=df&id=".$nid."&getkey=".$getkey."'>".($row['defunct']=="N"?"<span class=green>On</span>":"<span class=red>Off</span>")."</a>"."</td>";
        echo "<td><a href='news_add_page.php?cid=".$nid."'>Copy</a></td>";
        echo "<td><a href='news_list.php?op=top&id=".$nid."&getkey=".$getkey."'>".(intval($row['importance'])>0?"<span class=red>是</span>":"<span style='color:grey'>否</span>")."</a></td>";
        echo "<td><a href='news_list.php?op=delete&id=".$nid."&getkey=".$getkey."' onclick=\"return confirm('确定物理删除该公告？不可恢复！')\">删除</a></td>";
      echo "</tr>";
    }
    ?>
  </table>
</center>
- <?php echo $MSG_HELP_ADD_FAQS?>

<?php
if(!(isset($_GET['keyword']) && $_GET['keyword']!=""))
{
  echo "<div style='display:inline;'>";
  echo "<nav class='center'>";
  echo "<ul class='pagination pagination-sm'>";
  echo "<li class='page-item'><a href='news_list.php?page=".(strval(1))."'>&lt;&lt;</a></li>";
  echo "<li class='page-item'><a href='news_list.php?page=".($page==1?strval(1):strval($page-1))."'>&lt;</a></li>";
  for($i=$spage; $i<=$epage; $i++){
    echo "<li class='".($page==$i?"active ":"")."page-item'><a title='go to page' href='news_list.php?page=".$i."'>".$i."</a></li>";
  }
  echo "<li class='page-item'><a href='news_list.php?page=".($page==$pages?strval($page):strval($page+1))."'>&gt;</a></li>";
  echo "<li class='page-item'><a href='news_list.php?page=".(strval($pages))."'>&gt;&gt;</a></li>";
  echo "</ul>";
  echo "</nav>";
  echo "</div>";
}
?>

</div>

