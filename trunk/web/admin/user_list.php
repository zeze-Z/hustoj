<?php
require("admin-header.php");
require_once("../include/set_get_key.php");

// 引入学校函数库
require_once("../include/school.php");

if(!(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'password_setter']))){
  echo "<a href='../loginpage.php'>Please Login First!</a>";
  exit(1);
}
if(isset($OJ_LANG)){
  require_once("../lang/$OJ_LANG.php");
}
?>
<title>User List</title>
<hr>
<center><h3><?php echo $MSG_USER."-".$MSG_LIST?></h3></center>
<div>
<?php
// 学校管理员只能看本校用户
$school_filter = getUserSchoolFilter();

$sql = "select COUNT('user_id') AS ids FROM `users` WHERE 1=1 $school_filter";
$result = pdo_query($sql);
$row = $result[0];
$ids = intval($row['ids']);
$idsperpage = 25;
$pages = intval(ceil($ids/$idsperpage));
if(isset($_GET['page'])){ 
  $page = intval($_GET['page']);
}else{ 
  $page = 1;
}
$pagesperframe = 5;
$frame = intval(ceil($page/$pagesperframe));
$spage = ($frame-1)*$pagesperframe+1;
$epage = min($spage+$pagesperframe-1, $pages);
$sid = ($page-1)*$idsperpage;
$sql = "";
$gkeyword="";
$trash="";
if(isset($_GET['keyword']) && $_GET['keyword']!=""){
  $gkeyword = $_GET['keyword'];
  $keyword = "%$gkeyword%";
  $sql = "select `user_id`,`nick`,email,`accesstime`,`reg_time`,`expiry_date`,`ip`,`school`,`group_name`,`defunct`,`role` FROM `users` WHERE (user_id LIKE ?) OR (nick LIKE ?) OR (school LIKE ?)  OR (group_name LIKE ?) or (ip like ?) ORDER BY `user_id` DESC";
  $result = pdo_query($sql,$keyword,$keyword,$keyword,$keyword,$keyword);
}else if(isset($_GET['trash'])){
  $trash="&trash";
  $sql = "select `user_id`,`nick`,email,`accesstime`,`reg_time`,`expiry_date`,`ip`,`school`,`group_name`,`defunct`,`role` FROM `users` where defunct='Y' ORDER BY `accesstime` DESC LIMIT $sid, $idsperpage";
  $result = pdo_query($sql);
}else{
  $sql = "select `user_id`,`nick`,email,`accesstime`,`reg_time`,`expiry_date`,`ip`,`school`,`group_name`,`defunct`,`role` FROM `users` where defunct='N' ORDER BY `accesstime` DESC LIMIT $sid, $idsperpage";
  $result = pdo_query($sql);
}
?>

<center>
<form action=user_list.php class="form-search form-inline">
  <input type="text" name="keyword"  value="<?php echo htmlentities($gkeyword,ENT_QUOTES) ?>"  class="form-control search-query" placeholder="<?php echo $MSG_USER_ID.', '.$MSG_NICK.', '.$MSG_SCHOOL?>">
  <button type="submit" class="form-control"><?php echo $MSG_SEARCH?></button>
  <a href="user_list.php?trash" title="<?php echo $MSG_VIEW_DISABLED_USER?>" ><i class='icon large trash color grey' ></i></a>
</form>
</center>

<center>
  <div style="overflow-x:auto;">
  <table width=100% border=1 style="text-align:center; font-size:12px;" class="ui striped aligned table compact">
<thead>
    <tr>
    <th><?php echo $MSG_USER_ID?></th>
      <th><?php echo $MSG_NICK?></th>
      <th>角色</th>
      <th>IP</th>
      <th><?php echo $MSG_EMAIL?></th>
      <th><?php echo $MSG_SCHOOL?></th>
      <th><?php echo $MSG_GROUP_NAME?></th>
      <th><?php echo $MSG_LAST_LOGIN?></th>
      <th><?php echo $MSG_REGISTER?></th>
      <th><?php echo $MSG_EXPIRY_DATE?></th>
      <th><?php echo $MSG_STATUS?></th>
      <th><?php echo $MSG_ADMIN ?></th>
      <th><?php echo $MSG_SETPASSWORD?></th>
      <th><?php echo $MSG_PRIVILEGE."-".$MSG_ADD ?></th>
      <th>审计</th>
      </tr>
</thead>

    <?php
    foreach($result as $row){
      echo "<tr>";
        echo "<td><a href='../userinfo.php?user=".htmlentities(urlencode($row['user_id']))."'>".$row['user_id']."</a></td>";
        if($row['nick']=="") $row['nick']="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<td><span fd='nick' user_id='".$row['user_id']."'>".$row['nick']."</span></td>";
        echo "<td><span fd='role' user_id='".$row['user_id']."'>".htmlentities($row['role'],ENT_QUOTES,'UTF-8')."</span></td>";
        echo "<td><a href='user_list.php?keyword=".htmlentities(urlencode($row['ip']))."' >".$row['ip']."</td>";
        if($row['email']=="") $row['email']="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<td><span fd='email' user_id='".$row['user_id']."'>".$row['email']."</span></td>";
        if($row['school']=="") $row['school']="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<td><span fd='school' user_id='".$row['user_id']."'>".$row['school']."</span></td>";
        if($row['group_name']=="") $row['group_name']="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<td><span fd='group_name' user_id='".$row['user_id']."'>".$row['group_name']."</span></td>";
        echo "<td>".$row['accesstime']."</td>";
        echo "<td>".$row['reg_time']."</td>";
        $color="red";
        $edate= new DateTime($row['expiry_date']);
        $tomorrow= new DateTime(add_days(7));   // 7 日临期预警蓝色
        $today= new DateTime(add_days(0));
        if($edate>$tomorrow) $color="green";
        else if($edate>=$today) $color="blue";
        echo "<td><span fd='expiry_date' user_id='".$row['user_id']."' class='".$color."' >".$row['expiry_date']."</span></td>";

        echo "<td>".($row['defunct']=="N"?"<span class=green >$MSG_NORMAL</span>":"<span class=red>$MSG_DELETED</span>")."</td>";
      if(isset($_SESSION[$OJ_NAME.'_'.'administrator']) && $row['user_id']!=$_SESSION[$OJ_NAME."_user_id"]){
        echo "<td><a href=user_df_change.php?cid=".$row['user_id']."&getkey=".$_SESSION[$OJ_NAME.'_'.'getkey'].">".
           ($row['defunct']=="N"?"<span class='label label-danger' title='$MSG_CLICK_TO_DELETE'>$MSG_CLICK_TO_DELETE</span>":"<span class='label label-success' title='$MSG_CLICK_TO_RECOVER'>$MSG_CLICK_TO_RECOVER</span>")
            ."</a></td>";
      }else{
      	   echo "<td>&nbsp;</td>";
      }
        echo "<td><a class='label label-warning' href=changepass.php?uid=".$row['user_id']."&getkey=".$_SESSION[$OJ_NAME.'_'.'getkey'].">".$MSG_RESET."</a></td>";
        echo "<td><a class='label label-success' href=privilege_add.php?uid=".$row['user_id']."&getkey=".$_SESSION[$OJ_NAME.'_'.'getkey'].">".$MSG_ADD."</a></td>";
        echo "<td><a class='label label-info' href=user_action_log.php?user_id=".htmlentities(urlencode($row['user_id']))."&getkey=".$_SESSION[$OJ_NAME.'_'.'getkey']." title='查看用户操作记录'>审计</a></td>";
      echo "</tr>";
    } ?>
  </table>
  </div>
</center>

<?php
if(!(isset($_GET['keyword']) && $_GET['keyword']!=""))
{
  echo "<div style='display:inline;'>";
  echo "<nav class='center'>";
  echo "<ul class='pagination pagination-sm'>";
  echo "<li class='page-item'><a href='user_list.php?page=".(strval(1))."$trash'>&lt;&lt;</a></li>";
  echo "<li class='page-item'><a href='user_list.php?page=".($page==1?strval(1):strval($page-1))."$trash'>&lt;</a></li>";
  for($i=$spage; $i<=$epage; $i++){
    echo "<li class='".($page==$i?"active ":"")."page-item'><a title='go to page' href='user_list.php?page=".$i."$trash'>".$i."</a></li>";
  }
  echo "<li class='page-item'><a href='user_list.php?page=".($page==$pages?strval($page):strval($page+1))."$trash'>&gt;</a></li>";
  echo "<li class='page-item'><a href='user_list.php?page=".(strval($pages))."$trash'>&gt;&gt;</a></li>";
  echo "</ul>";
  echo "</nav>";
  echo "</div>";
}
?>

</div>
<script>
function admin_mod(){
    // 字段编辑配置：fd 名 -> 控件配置
    // 新增可编辑字段只需在此声明，并在 ajax.php 配套调用 try_ajax("user", fd, "administrator")
    const FIELD_CFG = {
        nick:        {type:'text',  size:2,  cls:'input-mini'},
        email:       {type:'text',  size:20, cls:'input-large'},
        school:      {type:'text',  size:20, cls:'input-large'}, // 清空文本时 try_ajax 内同步 school_id=NULL
        group_name:  {type:'text',  size:20, cls:'input-large'},
        expiry_date: {type:'date',  size:2,  cls:'input-mini'},
        role:        {type:'select', options:['teacher','student']}
    };

    $("span[fd]").each(function(){
        let sp=$(this);
        let user_id=sp.attr('user_id');
        let fd=sp.attr('fd');
        let cfg=FIELD_CFG[fd];
        if(!cfg) return; // 未配置的字段不支持内联编辑

        sp.dblclick(function(){
            let cur=sp.text().trim();
            let html="<form onsubmit='return false;'>"
                + "<input type=hidden name='m' value='user_update_"+fd+"'>"
                + "<input type='hidden' name='user_id' value='"+user_id+"'>";
            if(cfg.type==='select'){
                let list=cfg.options.slice();
                if(cur && list.indexOf(cur)<0) list.unshift(cur); // 当前值不在选项内则保留，避免显示丢失
                let opts='';
                for(let i=0;i<list.length;i++){
                    opts+="<option value='"+list[i]+"' "+(list[i]===cur?"selected":"")+">"+list[i]+"</option>";
                }
                html+="<select name='"+fd+"' class='"+cfg.cls+"'>"+opts+"</select>";
            } else {
                html+="<input type='"+cfg.type+"' name='"+fd+"' value='"+cur+"' class='"+cfg.cls+"' size="+cfg.size+" >";
            }
            html+="</form>";
            sp.html(html);
            let el=sp.find("[name='"+fd+"']");
            el.focus();
            if(el[0] && typeof el[0].select==='function') el[0].select();
            el.change(function(){
                let v=sp.find("[name='"+fd+"']").val();
                $.post("ajax.php",sp.find("form").serialize()).done(function(){
                    console.log("new "+fd+":"+v);
                    sp.html(v===''?'&nbsp;':v);
                });
            });
        });
    });
}
$(document).ready(function(){
        admin_mod();
});

</script>
