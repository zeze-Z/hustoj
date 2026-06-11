<?php 
  require_once("../include/db_info.inc.php");
  require_once("../lang/$OJ_LANG.php");
  require_once("../include/const.inc.php");

  require_once("admin-header.php");
  if(!(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator']))){
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
  }
  
  // 加载学校相关函数
  if (file_exists("../include/school.php")) {
      require_once("../include/school.php");
      $school_list = getSchoolList(true);
  }
  
  echo "<center><h3>"."Edit-".$MSG_CONTEST."</h3></center>";
  include_once("kindeditor.php") ;
?>
<html>
<head>
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Cache-Control" content="no-cache">
  <meta http-equiv="Content-Language" content="zh-cn">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Edit Contest</title>
</head>
<hr>

<body leftmargin="30" >
<?php
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
  $langmask=0;
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

  $cid=intval($_POST['cid']);

  if(!(isset($_SESSION[$OJ_NAME.'_'."m$cid"])||isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator']))) exit();

  $description = str_replace("<p>", "", $description); 
  $description = str_replace("</p>", "<br />", $description);
  $description = str_replace(",", "&#44;", $description);
  //echo "$subnet[$contest_type]";
  
  // 获取学校和公开设置
  $school_id = isset($_POST['school_id']) && $_POST['school_id'] !== '' ? intval($_POST['school_id']) : null;
  $is_public = isset($_POST['is_public']) ? 1 : 0;

  $sql = "UPDATE `contest` SET `title`=?,`description`=?,`start_time`=?,`end_time`=?,`private`=?,`langmask`=?,`password`=?,subnet=?,contest_type=?,`school_id`=?,`is_public`=? WHERE `contest_id`=?";
  //echo $sql;
  pdo_query($sql,$title,$description,$starttime,$endtime,$private,$langmask,$password,$subnet,$contest_type,$school_id,$is_public,$cid);

  $sql = "DELETE FROM `contest_problem` WHERE `contest_id`=?";
  pdo_query($sql,$cid);
  $plist=trim($_POST['cproblem']);
  $pieces = explode(',', $plist);

  if(count($pieces)>0 && strlen($pieces[0])>0){
    $sql_1 = "INSERT INTO `contest_problem`(`contest_id`,`problem_id`,`num`) VALUES (?,?,?)";
    
    $plist="";
    pdo_query("update solution set num=-1 where contest_id=?",$cid);
    $num=0;
    for($i=0; $i<count($pieces); $i++){
      $sql="select problem_id from problem where problem_id=?";
      $pid=intval($pieces[$i]);
      $has=pdo_query($sql,$pid);
      if(count($has) > 0) {
         if($plist) $plist.=",";
         $plist.=intval($pieces[$i]);
         pdo_query($sql_1,$cid,$pieces[$i],$num);
	 $sql="UPDATE `contest_problem` SET `c_accepted`=(select count(1) FROM `solution` WHERE `problem_id`=? and contest_id=? AND `result`=4) WHERE `problem_id`=? and contest_id=?";
	 pdo_query($sql,$pid,$cid,$pid,$cid);
	 $sql="UPDATE `contest_problem` SET `c_submit`=(select count(1) FROM `solution` WHERE `problem_id`=? and contest_id=?) WHERE `problem_id`=? and contest_id=?";
	 pdo_query($sql,$pid,$cid,$pid,$cid);
      	 $sql_2 = "update solution set num=? where contest_id=? and problem_id=?;";
      	 pdo_query($sql_2,$num,$cid,$pid);
         $num++;
      }else{
         print("Problem not exists:".$pieces[$i]."<br>\n");
      }
    }

    $sql = "update `problem` set defunct='N' where `problem_id` in ($plist)";
    pdo_query($sql) ;
  }

  $sql = "DELETE FROM `privilege` WHERE `rightstr`=?";
  pdo_query($sql,"c$cid");
  $pieces = explode("\n", trim($_POST['ulist']));
  $pieces = array_unique($pieces);
  if(count($pieces)>0 && strlen($pieces[0])>0){
    $sql_1 = "INSERT INTO `privilege`(`user_id`,`rightstr`) VALUES (?,?)";
    for($i=0; $i<count($pieces); $i++){
      pdo_query($sql_1,trim($pieces[$i]),"c$cid") ;
    }
  }

  echo "<script>window.location.href=\"contest_list.php\";</script>";
  exit();
}else{
  $cid = intval($_GET['cid']);
  $sql = "select * FROM `contest` WHERE `contest_id`=?";
  $result = pdo_query($sql,$cid);

  if(count($result)!=1){
    echo "No such Contest!";
    exit(0);
  }

  $row = $result[0];
  $starttime = $row['start_time'];
  $endtime = $row['end_time'];
  $private = $row['private'];
  $password = $row['password'];
  $langmask = $row['langmask'];
  $subnet= $row['subnet'];
  $contest_type= $row['contest_type'];
  $description = $row['description'];
  $title = htmlentities($row['title'],ENT_QUOTES,"UTF-8");

  $plist = "";
  $sql = "select `problem_id` FROM `contest_problem` WHERE `contest_id`=? ORDER BY `num`";
  $result=pdo_query($sql,$cid);

  foreach($result as $row){
    if($plist) $plist .= ",";
    $plist.=$row[0];
  }

  $ulist = "";
  $sql = "select `user_id` FROM `privilege` WHERE `rightstr`=? order by user_id";
  $result = pdo_query($sql,"c$cid");

  foreach($result as $row){
    if($ulist) $ulist .= "\n";
    $ulist .= $row[0];
  } 
}
?>

<div class="padding">
  <form method=POST>
    <?php require_once("../include/set_post_key.php");?>
    <input type=hidden name='cid' value=<?php echo $cid?>>
    <p align=left>
      <?php echo "<h3>".$MSG_CONTEST."-".$MSG_TITLE."</h3>"?>
      <input class="input input-xxlarge" style="width:100%;" type=text name=title value="<?php echo $title?>"><br><br>
    </p>
    <p align=left>
      <?php echo $MSG_CONTEST.$MSG_Start?>:
      <input class=input-large type=date name='startdate' value='<?php echo substr($starttime,0,10)?>' size=4 >
      Hour: <input class=input-mini type=text name=shour size=2 value='<?php echo substr($starttime,11,2)?>'>&nbsp;
      Minute: <input class=input-mini type=text name=sminute value='<?php echo substr($starttime,14,2)?>' size=2 >
    </p>
    <p align=left>
      <?php echo $MSG_CONTEST.$MSG_End?>:
      <input class=input-large type=date name='enddate' value='<?php echo substr($endtime,0,10)?>' size=4 >
      Hour: <input class=input-mini type=text name=ehour size=2 value='<?php echo substr($endtime,11,2)?>'>&nbsp;
      Minute: <input class=input-mini type=text name=eminute value='<?php echo substr($endtime,14,2)?>' size=2 >
    </p>
    <br>
    <p align=left>
      <?php echo $MSG_CONTEST."-".$MSG_PROBLEM_ID?>
      <?php echo "( Add problemIDs with coma , )"?>
      <button type="button" class="btn btn-default" style="margin-left:8px;" onclick="openProblemSearch()">🔍 筛选题目</button>
      <br>
      <input id="plist" onchange="showTitles()" class=input-xxlarge type=text style="width:100%" name=cproblem value='<?php echo $plist?>'>
      <div id="ptitles"></div>
    </p>
    <br><?php echo $MSG_SUBNET ?>
      <input class=input-xxlarge type=text style="width:100%" name=subnet value='<?php echo htmlentities($subnet)?>' placeholder='0.0.0.0/0'>
    <p align=left>
      <?php echo "<h4>".$MSG_CONTEST."-".$MSG_Description."</h4>"?>
      <textarea class=kindeditor rows=13 name=description cols=80>
        <?php echo htmlentities($description,ENT_QUOTES,'UTF-8')?>
      </textarea>
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
              $contest_lock = (~((int)$contest_type))&((1<<$locks_count)-1);

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
              <input type=text name=password style="width:150px;" value='<?php echo htmlentities($password,ENT_QUOTES,'utf-8')?>'>
            </p>
          </td>
        </tr>
        <tr>
          <td height="*">
            <p align=left>
              <?php echo $MSG_CONTEST."-".$MSG_USER?>
              <?php echo "( Add private contest's userIDs with newline &#47;n )"?>
              <br>
              <textarea name='ulist' rows='10' style='width:100%;' placeholder='user1<?php echo "\n"?>user2<?php echo "\n"?>user3<?php echo "\n"?>
              <?php echo $MSG_PRIVATE_USERS_ADD?><?php echo "\n"?>'><?php if(isset($ulist)){ echo $ulist;}?></textarea>
            </p>
          </td>
        </tr>
      </table>

      <?php if(isset($school_list) && is_array($school_list)): ?>
      <p align=left>
        <?php echo "<h4>".$MSG_SCHOOL."</h4>"?>
        <select name="school_id" class="form-control" style="width:100%;">
          <option value="">选择学校</option>
          <?php foreach ($school_list as $school): ?>
            <option value="<?php echo $school['id'] ?>" <?php echo ($row['school_id'] == $school['id']) ? 'selected' : ''; ?>>
              <?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </p>
      <p align=left>
        <label>
          <input type="checkbox" name="is_public" value="1" <?php echo (!empty($row['is_public'])) ? 'checked' : ''; ?>> 公开比赛（允许其他学校访问）
        </label>
        <span style="font-size:12px;color:#666;">（控制哪些学校的用户能在竞赛列表中看到这个比赛）</span>
      </p>
      <?php endif; ?>

      <div align=center>
        <?php require_once("../include/set_post_key.php");?>
        <input type=submit value='<?php echo $MSG_SAVE?>' name=submit> <input type=reset value=Reset name=reset>
      </div>
    </p>
  </form>
</div>
<script>
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
</body>
</html>


