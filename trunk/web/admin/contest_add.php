<?php
  
  require_once("../include/db_info.inc.php");
  require_once("../lang/$OJ_LANG.php");
  require_once("../include/const.inc.php");
  require_once("admin-header.php");
   header("Cache-control:private"); 
if(!(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator']))){
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
  }
  
  // 加载学校相关函数
  if (file_exists("../include/school.php")) {
      require_once("../include/school.php");
      $school_list = getSchoolList(true);
      $current_user_school_id = getCurrentUserSchoolId();
  }
  
  echo "<center><h3>".$MSG_CONTEST."-".$MSG_ADD."</h3></center>";
  include_once("kindeditor.php") ;
?>

<body leftmargin="30" >
<?php
$description = "";
if(isset($_POST['startdate'])){
  require_once("../include/check_post_key.php");

  $starttime = $_POST['startdate']." ".intval($_POST['shour']).":".intval($_POST['sminute']).":00";
  $endtime = $_POST['enddate']." ".intval($_POST['ehour']).":".intval($_POST['eminute']).":00";
  //echo $starttime;
  //echo $endtime;

  $title = $_POST['title'];
  $private = $_POST['private'];
  $password = $_POST['password'];
  $description = $_POST['description'];
  
  if(false){
    $title = stripslashes($title);
    $private = stripslashes($private);
    $password = stripslashes($password);
    $description = stripslashes($description);
  }

  $lang = $_POST['lang'];
  $langmask = 0;
  foreach($lang as $t){
    $langmask += 1<<$t;
  } 

  $langmask = ((1<<count($language_ext))-1)&(~$langmask);
  //echo $langmask; 

  $subnet= $_POST['subnet'];
  $contest_types= $_POST['contest_type'];
  $contest_type=0;
  foreach($contest_types as $t){
    $contest_type |= 1<<$t;
  } 
  $sql = "INSERT INTO `contest`(`title`,`start_time`,`end_time`,`private`,`langmask`,`description`,`password`,subnet,contest_type,`user_id`,`school_id`,`is_public`)
          VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";

  $description = str_replace("<p>", "", $description); 
  $description = str_replace("</p>", "<br />", $description);
  $description = str_replace(",", "&#44; ", $description);
  $user_id=$_SESSION[$OJ_NAME.'_'.'user_id'];
  
  // 获取学校和公开设置
  $school_id = isset($_POST['school_id']) && $_POST['school_id'] !== '' ? intval($_POST['school_id']) : null;
  $is_public = isset($_POST['is_public']) ? 1 : 0;
  
 // echo $sql.$title.$starttime.$endtime.$private.$langmask.$description.$password,$user_id;
  $cid = pdo_query($sql,$title,$starttime,$endtime,$private,$langmask,$description,$password,$subnet,$contest_type,$user_id,$school_id,$is_public) ;
  echo "Add Contest ".$cid;

  $sql = "DELETE FROM `contest_problem` WHERE `contest_id`=$cid";
  $plist = trim($_POST['cproblem']);
  $pieces = explode(",",$plist );
  $pieces = array_unique($pieces);
  if(count($pieces)>0 && intval($pieces[0])>0){
     
     
    $sql_1 = "INSERT INTO `contest_problem`(`contest_id`,`problem_id`,`num`) VALUES (?,?,?)";
    $plist="";
    $pid=0;
    for($i=0; $i<count($pieces); $i++){
      $sql="select problem_id from problem where problem_id=?";
      $has=pdo_query($sql,$pieces[$i]);
      if(count($has) > 0) {
         if($plist) $plist.=",";
         $plist.=intval($pieces[$i]);
         pdo_query($sql_1,$cid,$pieces[$i],$pid);
         $pid++;
      }else{
         print("Problem not exists:".$pieces[$i]."<br>\n");
      }
    }
    //echo $sql_1;
    $sql = "UPDATE `problem` SET defunct='N' WHERE `problem_id` IN ($plist)";
    pdo_query($sql) ;
  }

  $sql = "DELETE FROM `privilege` WHERE `rightstr`=?";
  pdo_query($sql,"c$cid");

  $sql = "INSERT INTO `privilege` (`user_id`,`rightstr`) VALUES(?,?)";
  pdo_query($sql,$_SESSION[$OJ_NAME.'_'.'user_id'],"m$cid");

  $_SESSION[$OJ_NAME.'_'."m$cid"] = true;
  $pieces = explode("\n", trim($_POST['ulist']));

  if(count($pieces)>0 && strlen($pieces[0])>0){
    $sql_1 = "INSERT INTO `privilege`(`user_id`,`rightstr`) VALUES (?,?)";
    for($i=0; $i<count($pieces); $i++){
      pdo_query($sql_1,trim($pieces[$i]),"c$cid") ;
    }
  }
  echo "<script>window.location.href=\"contest_list.php\";</script>";
}
else{
  if(isset($_GET['cid'])){
    $cid = intval($_GET['cid']);
    $sql = "select * FROM contest WHERE `contest_id`=?";
    $result = pdo_query($sql,$cid);
    $row = $result[0];
    $title = $row['title']."-Copy";

    $private = $row['private'];
    $langmask = $row['langmask'];
    $description = $row['description'];

    $plist = "";
    $sql = "select `problem_id` FROM `contest_problem` WHERE `contest_id`=? ORDER BY `num`";
    $result = pdo_query($sql,$cid);
    foreach($result as $row){
      if($plist) $plist = $plist.',';
      $plist = $plist.$row[0];
    }

    $ulist = "";
    $sql = "select `user_id` FROM `privilege` WHERE `rightstr`=? order by user_id";
    $result = pdo_query($sql,"c$cid");

    foreach($result as $row){
      if($ulist) $ulist .= "\n";
      $ulist .= $row[0];
    }
  }
  else if(isset($_POST['problem2contest'])){
    $plist = "";
    
    sort($_POST['pid']);
    foreach($_POST['pid'] as $i){       
      if($plist)
      $plist.=','.intval($i);
      else
        $plist=$i;
    }
  $plist = trim($_POST['hlist']);
  $pieces = explode(",",$plist );
  $pieces = array_unique($pieces);
  if($pieces[0]=="") unset($pieces[0]);
  $plist=implode(",",$pieces);

  }else if(isset($_GET['spid'])){
    //require_once("../include/check_get_key.php");
    $spid = intval($_GET['spid']);

    $plist = "";
    $sql = "select `problem_id` FROM `problem` WHERE `problem_id`>=? ";
    $result = pdo_query($sql,$spid);
    foreach($result as $row){
      if($plist) $plist.=',';
      $plist.=$row[0];
    }
  }

?>
<html>
<head>
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Cache-Control" content="no-cache">
  <meta http-equiv="Content-Language" content="zh-cn">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Contest Add</title>
</head>
<hr>

<?php 
  include_once("kindeditor.php") ;
?>

  <div class="padding">
    <form method=POST>
    <p align=left>
      <?php echo "<h3>".$MSG_CONTEST."-".$MSG_TITLE."</h3>"?>
      <input class="input input-xxlarge" style="width:100%;" type=text name=title value="<?php echo isset($title)?$title:""?>"><br><br>
    </p>
    <p align=left>
      <?php echo $MSG_CONTEST.$MSG_Start?>:
      <input class=input-large type=date name='startdate' value='<?php echo date('Y').'-'. date('m').'-'.date('d')?>' size=4 >
      Hour: <input class=input-mini type=text name=shour size=2 value=<?php echo date('H')?>>&nbsp;
      Minute: <input class=input-mini type=text name=sminute value=00 size=2 >
    </p>
    <p align=left>
      <?php echo $MSG_CONTEST.$MSG_End?>:
      <input class=input-large type=date name='enddate' value='<?php echo date('Y').'-'. date('m').'-'.date('d')?>' size=4 >
      Hour: <input class=input-mini type=text name=ehour size=2 value=<?php echo (date('H')+4)%24?>>&nbsp;
      Minute: <input class=input-mini type=text name=eminute value=00 size=2 >
    </p>
    <br>
    <p align=left>
      <?php echo $MSG_CONTEST."-".$MSG_PROBLEM_ID?>
      <?php echo "( Add problemIDs with coma , )"?>
      <button type="button" class="btn btn-default" style="margin-left:8px;" onclick="openProblemSearch()">🔍 筛选题目</button>
      <br>
      <input id="plist" onchange="showTitles()" class=input-xxlarge placeholder="Example:1000,1001,1002" type=text style="width:100%" name=cproblem value="<?php echo isset($plist)?$plist:""?>">
      <div id="ptitles"></div>
    </p>
    <br><?php echo $MSG_SUBNET ?>
      <input class=input-xxlarge type=text style="width:100%" name=subnet value='' placeholder='0.0.0.0/0'>
    <br>
    <p align=left>
      <?php echo "<h4>".$MSG_CONTEST."-".$MSG_Description."</h4>"?>
      <textarea class=kindeditor rows=13 name=description cols=80><?php echo isset($description)?$description:""?></textarea>
      <br>
      <table width="100%">
        <tr>
          <td rowspan=2>
            <p aligh=left>
              <?php echo $MSG_CONTEST."-".$MSG_LANG?>
              <?php echo "( Add PLs with Ctrl+click )"?><br>
              <?php echo $MSG_PLS_ADD?><br>
              <select name="lang[]" multiple="multiple" style="height:220px">
              <?php
              $lang_count = count($language_ext);
              $lang = (~((int)$langmask))&((1<<$lang_count)-1);

              if(isset($_COOKIE['lastlang'])) $lastlang=$_COOKIE['lastlang'];
              else $lastlang = 0;

              for($i=0; $i<$lang_count; $i++){
		       if( (1<<$i) & $OJ_LANGMASK ) continue;
                       echo "<option value=$i ".( $lang&(1<<$i)?"selected":"").">".$language_name[$i]."</option>";
              }
              ?>
              </select>
            </p>
          </td>
          <td rowspan=2>
            <p aligh=left>
              <?php echo $MSG_FORBIDDEN?><br>
              <?php
              $locks_count = count($contest_locks);
              $contest_lock = 0;
              $contest_type = 32;
              for($i=0; $i<$locks_count; $i++){
                echo "<input type=checkbox name='contest_type[]'  value=$i ".( $contest_type&(1<<$i)?"checked":"").">".$contest_locks[$i]."<br>";
              }
              ?>
            </p>
          </td>
          <td height="10px">
            <p align=left>
              <?php echo $MSG_CONTEST."-".$MSG_Public?>:
              <select name=private style="width:150px;">
                <option value=0 <?php echo $private=='0'?'selected=selected':''?>><?php echo $MSG_Public?></option>
                <option value=1 <?php echo $private=='1'?'selected=selected':''?>><?php echo $MSG_Private?></option>
              </select>
              <span style="font-size:12px;color:#666;">（控制谁能参加竞赛，私有竞赛需要密码或权限）</span>
              <br>
              <?php echo $MSG_CONTEST."-".$MSG_PASSWORD?>:
              <input type=text name=password style="width:150px;" value="">
            </p>
          </td>
        </tr>
        <tr>
          <td height="*">
            <p align=left>
              <?php echo $MSG_CONTEST."-".$MSG_USER?>
              <?php echo "( Add private contest's userIDs with newline &#92;n )"?>
              <select id="copy_from" onchange="copy_user_from_contest($(this).val());" >
              <option value=0 ><?php echo $MSG_COPY_USER_LIST_FROM_CONTEST ?></option>
                        <?php
                                $contests="0";
                                foreach($_SESSION as $right=>$value){
                                        if(strpos($right,$OJ_NAME.'_m')===0){
                                        //      echo "<option>".substr($right,strlen($OJ_NAME."_m"))."[".strpos($right,$OJ_NAME.'_m')."]</option>";
                                                $contests.=",".substr($right,strlen($OJ_NAME."_m"));
                                        }
                                }
                                $contests=pdo_query("select contest_id,title from contest where contest_id in ($contests)  order by contest_id desc limit 20 ");
                                if(!empty($contests)){

                                        foreach( $contests as $contest ){
                                            echo "<option value='".$contest['contest_id']."'>".$contest['title']."</option>";
                                        }

                                }
                        ?>
                </select>

              <br>
              <textarea id="ulist" name='ulist' rows='10' style='width:100%;' placeholder='user1<?php echo "\n"?>user2<?php echo "\n"?>user3<?php echo "\n"?>
              <?php echo $MSG_PRIVATE_USERS_ADD?><?php echo "\n"?>'><?php if(isset($ulist)){ echo $ulist;}?></textarea>
            </p>
          </td>
        </tr>
      </table>

      <div align=center>
        <?php require_once("../include/set_post_key.php");?>
        <input type=submit value='<?php echo $MSG_SAVE?>' name=submit>
      </div>
    </p>
    
    <?php if(isset($school_list) && is_array($school_list)): ?>
    <p align=left>
      <?php echo "<h4>".$MSG_SCHOOL."</h4>"?>
      <select name="school_id" class="form-control" style="width:100%;">
        <option value="">选择学校</option>
        <?php foreach ($school_list as $school): ?>
          <option value="<?php echo $school['id'] ?>" <?php echo ($current_user_school_id == $school['id']) ? 'selected' : ''; ?>>
            <?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>
    <p align=left>
      <label>
        <input type="checkbox" name="is_public" value="1"> 公开比赛（允许其他学校访问）
      </label>
      <span style="font-size:12px;color:#666;">（控制哪些学校的用户能在竞赛列表中看到这个比赛）</span>
    </p>
    <?php endif; ?>
  </form>
</div>

<script>
  function copy_user_from_contest(cid){
      $("#ulist").val($.ajax({url:"ajax.php",method:"post",data:{"contest_id":cid,"m":"get_user_list_of_contest"},async:false}).responseText);
  }

  function openProblemSearch(){
    let pids = getCurrentPids();

    let html = '<div id="ps_dialog" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;">'
      + '<div style="background:#fff;width:90%;max-width:1100px;height:85vh;border-radius:8px;display:flex;flex-direction:column;box-shadow:0 4px 20px rgba(0,0,0,0.2);overflow:hidden;">'
      + '<div style="padding:15px 20px;border-bottom:1px solid #e5e5e5;background:#f8f9fa;font-size:16px;font-weight:bold;display:flex;justify-content:space-between;align-items:center;">'
      +   '题目筛选'
      +   '<button type="button" class="close" onclick="closeProblemSearch()" style="font-size:20px;border:none;background:none;cursor:pointer;">&times;</button>'
      + '</div>'
      + '<div style="display:flex;flex:1;min-height:0;">'
      +   '<div style="flex:1;padding:15px;border-right:1px solid #e5e5e5;display:flex;flex-direction:column;min-width:0;">'
      +     '<div style="margin-bottom:12px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">'
      +       '<input type=text id="ps_keyword" placeholder="题目ID / 标题 / 描述 / 来源 / 提示" style="width:220px;" class="input-large form-control">&nbsp;'
      +       '<select id="ps_source" class="form-control" style="width:100px;"><option value="">所有来源</option><option value="蓝桥杯">蓝桥杯</option><option value="CSP-J">CSP-J</option><option value="CSP-S">CSP-S</option><option value="GESP">GESP</option><option value="NOIP">NOIP</option><option value="其他">其他</option></select>&nbsp;'
      +       '<select id="ps_level" class="form-control" style="width:100px;"><option value="">所有难度</option><option value="1">入门1-2</option><option value="3">基础3-4</option><option value="5">进阶5-6</option><option value="7">竞赛7-8</option></select>&nbsp;'
      +       '<select id="ps_type" class="form-control" style="width:90px;"><option value="">所有题型</option><option value="programming">编程题</option><option value="choice_single">单选题</option><option value="choice_multi">多选题</option><option value="judge">判断题</option></select>&nbsp;'
      +       '<button type="button" class="btn btn-primary" onclick="searchProblems()">搜索</button>&nbsp;'
      +       '<button type="button" class="btn btn-default" onclick="selectAllProblems(true)" style="min-width:60px;">全选</button>&nbsp;'
      +       '<button type="button" class="btn btn-default" onclick="selectAllProblems(false)" style="min-width:60px;">清空</button>'
      +     '</div>'
      +     '<div style="flex:1;overflow-y:auto;border:1px solid #ddd;border-radius:4px;">'
      +       '<table class="table table-striped table-hover" style="margin:0;">'
      +         '<thead style="background:#f5f5f5;position:sticky;top:0;z-index:10;"><tr><th style="width:40px;">选</th><th style="width:70px;">ID</th><th>标题</th><th style="width:130px;">来源</th><th style="width:60px;">题型</th><th style="width:50px;">难度</th></tr></thead>'
      +         '<tbody id="ps_result"><tr><td colspan=\'6\' align=\'center\' style=\'padding:30px;color:#999;\'>请输入搜索条件并点击搜索</td></tr></tbody>'
      +       '</table>'
      +     '</div>'
      +   '</div>'
      +   '<div style="width:300px;padding:15px;display:flex;flex-direction:column;min-width:0;background:#fafafa;">'
      +     '<div style="font-weight:bold;margin-bottom:8px;font-size:14px;">已选题目 (<span id="ps_selected_count">0</span>)</div>'
      +     '<div id="ps_selected_list" style="flex:1;overflow-y:auto;border:1px solid #ddd;padding:10px;background:#fff;border-radius:4px;font-size:13px;line-height:1.8;"></div>'
      +     '<div style="margin-top:12px;color:#888;font-size:12px;">点击左侧复选框添加/移除题目</div>'
      +   '</div>'
      + '</div>'
      + '<div style="padding:15px 20px;border-top:1px solid #e5e5e5;background:#f8f9fa;text-align:right;">'
      +   '<button type="button" class="btn btn-default" onclick="closeProblemSearch()">取消</button>&nbsp;'
      +   '<button type="button" class="btn btn-primary" onclick="confirmProblemSelect()">确认添加</button>'
      + '</div>'
      + '</div></div>';

    $("body").append(html);

    window._ps_selected = {};
    for(let i=0;i<pids.length;i++) window._ps_selected[pids[i]]=true;
    updateSelectedList();
  }

  function closeProblemSearch(){
    $("#ps_dialog").remove();
  }

  function confirmProblemSelect(){
    let pids = [];
    for(let pid in window._ps_selected){
      if(window._ps_selected[pid]) pids.push(parseInt(pid));
    }
    pids.sort(function(a,b){return a-b;});
    $("#plist").val(pids.join(","));
    closeProblemSearch();
    showTitles();
  }

  function getCurrentPids(){
    let val=$("#plist").val().trim();
    if(!val)return [];
    return val.split(",").map(function(v){return parseInt(v.trim());}).filter(function(v){return !isNaN(v)&&v>0;});
  }

  function searchProblems(){
    let keyword=$("#ps_keyword").val();
    let source=$("#ps_source").val();
    let level=$("#ps_level").val();
    let ptype=$("#ps_type").val();
    let html=$.ajax({url:"ajax.php",method:"post",data:{"m":"problem_search","keyword":keyword,"source":source,"level":level,"problem_type":ptype,"limit":100},async:false}).responseText;
    $("#ps_result").html(html);
    syncCheckedState();
  }

  function toggleProblem(pid,cb){
    if(cb.checked){
      window._ps_selected[pid]=true;
    }else{
      delete window._ps_selected[pid];
    }
    updateSelectedList();
  }

  function selectAllProblems(checked){
    $(".ps_cb").each(function(i,e){
      if(e.checked!==checked){
        e.checked=checked;
        toggleProblem(parseInt(e.value),e);
      }
    });
  }

  function syncCheckedState(){
    $(".ps_cb").each(function(i,e){
      let pid=parseInt(e.value);
      if(window._ps_selected && window._ps_selected[pid]) e.checked=true;
      else e.checked=false;
    });
  }

  function updateSelectedList(){
    let pids=[];
    for(let pid in window._ps_selected){
      if(window._ps_selected[pid]) pids.push(parseInt(pid));
    }
    pids.sort(function(a,b){return a-b;});
    $("#ps_selected_count").text(pids.length);
    let html="";
    for(let i=0;i<pids.length;i++){
      html += '<div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px dotted #eee;padding:2px 0;"><span>#'+pids[i]+'</span><a href="#" onclick="removeSelected('+pids[i]+');return false;" style="color:#c00;text-decoration:none;">✕</a></div>';
    }
    if(pids.length===0) html='<div style="color:#999;text-align:center;padding:20px 0;">尚未选择题目</div>';
    $("#ps_selected_list").html(html);
  }

  function removeSelected(pid){
    delete window._ps_selected[pid];
    updateSelectedList();
    syncCheckedState();
  }

	function showTitles(){
		let ts=$("#ptitles");
		let pids=$("#plist").val().split(",");
		let html="";
		pids.forEach(function(v,i,a){
			let title=$.ajax({url:"ajax.php",method:"post",data:{"pid":v,"m":"problem_get_title"},async:false}).responseText;
			html+=(v)+":<a href='../problem.php?id="+v+"' target='_blank'>"+title+"</a><br>\n";
			console.log(v);
		});
		ts.html(html);
	}
	$(document).ready(function(){
		showTitles();
	});

</script>
<?php }

?>
</body>
</html>
