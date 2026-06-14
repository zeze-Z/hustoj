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

  if(!isset($title)) $title = "";
  if(!isset($private)) $private = 0;
  if(!isset($description)) $description = "";
  if(!isset($plist)) $plist = "";
  if(!isset($ulist)) $ulist = "";
  if(!isset($langmask)) $langmask = 0;

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

  <style>
    .contest-add-wrap {
      max-width: 1180px;
      margin: 0 auto 40px;
      padding: 0 15px;
    }
    .contest-add-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      margin-bottom: 16px;
      overflow: hidden;
    }
    .contest-add-card .card-title {
      margin: 0;
      padding: 12px 16px;
      background: #f8fafc;
      border-bottom: 1px solid #e5e7eb;
      font-size: 16px;
      font-weight: 600;
      color: #1f2937;
    }
    .contest-add-card .card-body {
      padding: 16px;
    }
    .contest-form-row {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }
    .contest-form-group {
      flex: 1;
      min-width: 240px;
    }
    .contest-form-group.full {
      flex-basis: 100%;
    }
    .contest-form-group label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
      color: #374151;
    }
    .contest-form-group .help-block {
      margin: 6px 0 0;
      color: #6b7280;
      font-size: 12px;
      line-height: 1.5;
    }
    .time-inline {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
    }
    .time-inline input[type=date] {
      min-width: 160px;
    }
    .time-inline input[type=text] {
      width: 52px;
      text-align: center;
    }
    .contest-grid-2 {
      display: grid;
      grid-template-columns: minmax(260px, 1fr) minmax(320px, 1.4fr);
      gap: 16px;
    }
    .contest-lock-list {
      display: grid;
      grid-template-columns: repeat(2, minmax(240px, 1fr));
      gap: 8px 14px;
    }
    .contest-check-item {
      display: flex;
      align-items: flex-start;
      gap: 6px;
      line-height: 1.5;
      margin: 0;
      font-weight: normal;
    }
    .contest-check-item input {
      margin-top: 3px;
    }
    .contest-lang-select {
      width: 100%;
      min-height: 230px;
    }
    .contest-user-tools {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 8px;
    }
    .contest-actions {
      position: sticky;
      bottom: 0;
      z-index: 20;
      margin-top: 18px;
      padding: 14px 16px;
      text-align: center;
      background: rgba(248,250,252,0.96);
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
    }
    .contest-actions input[type=submit] {
      min-width: 140px;
      padding: 8px 28px;
      font-size: 15px;
    }
    #ptitles {
      margin-top: 8px;
      padding: 8px 10px;
      border: 1px dashed #d1d5db;
      border-radius: 4px;
      background: #fafafa;
      min-height: 38px;
      max-height: 160px;
      overflow-y: auto;
      line-height: 1.7;
    }
    @media (max-width: 900px) {
      .contest-grid-2 {
        grid-template-columns: 1fr;
      }
      .contest-lock-list {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <div class="contest-add-wrap">
    <form method=POST>
      <div class="contest-add-card">
        <h4 class="card-title">基本信息</h4>
        <div class="card-body">
          <div class="contest-form-row">
            <div class="contest-form-group full">
              <label><?php echo $MSG_CONTEST."-".$MSG_TITLE?></label>
              <input class="form-control input input-xxlarge" type=text name=title value="<?php echo htmlentities($title, ENT_QUOTES, 'UTF-8')?>" placeholder="请输入竞赛真题标题">
            </div>
          </div>

          <div class="contest-form-row">
            <div class="contest-form-group">
              <label><?php echo $MSG_CONTEST.$MSG_Start?></label>
              <div class="time-inline">
                <input class="form-control input-large" type=date name='startdate' value='<?php echo date('Y').'-'. date('m').'-'.date('d')?>' size=4>
                <span>时</span><input class="form-control input-mini" type=text name=shour size=2 value=<?php echo date('H')?>>
                <span>分</span><input class="form-control input-mini" type=text name=sminute value=00 size=2>
              </div>
            </div>
            <div class="contest-form-group">
              <label><?php echo $MSG_CONTEST.$MSG_End?></label>
              <div class="time-inline">
                <input class="form-control input-large" type=date name='enddate' value='<?php echo date('Y').'-'. date('m').'-'.date('d')?>' size=4>
                <span>时</span><input class="form-control input-mini" type=text name=ehour size=2 value=<?php echo (date('H')+4)%24?>>
                <span>分</span><input class="form-control input-mini" type=text name=eminute value=00 size=2>
              </div>
            </div>
          </div>

          <div class="contest-form-row">
            <div class="contest-form-group full">
              <label><?php echo $MSG_CONTEST."-".$MSG_PROBLEM_ID?></label>
              <div class="input-group">
                <input id="plist" onchange="showTitles()" class="form-control input-xxlarge" placeholder="Example:1000,1001,1002" type=text name=cproblem value="<?php echo htmlentities($plist, ENT_QUOTES, 'UTF-8')?>">
                <span class="input-group-btn">
                  <button type="button" class="btn btn-default" onclick="openProblemSearch()">🔍 筛选题目</button>
                </span>
              </div>
              <p class="help-block">多个题号使用英文逗号分隔；从问题列表进入时会自动带入所选题目。</p>
              <div id="ptitles"></div>
            </div>
          </div>

          <div class="contest-form-row">
            <div class="contest-form-group full">
              <label><?php echo $MSG_SUBNET ?></label>
              <input class="form-control input-xxlarge" type=text name=subnet value='' placeholder='0.0.0.0/0'>
              <p class="help-block">可限制允许参加的 IP 网段；不限制时保持默认即可。</p>
            </div>
          </div>
        </div>
      </div>

      <div class="contest-add-card">
        <h4 class="card-title"><?php echo $MSG_CONTEST."-".$MSG_Description?></h4>
        <div class="card-body">
          <textarea class=kindeditor rows=13 name=description cols=80><?php echo $description?></textarea>
        </div>
      </div>

      <div class="contest-add-card">
        <h4 class="card-title">规则设置</h4>
        <div class="card-body contest-grid-2">
          <div class="contest-form-group">
            <label><?php echo $MSG_CONTEST."-".$MSG_LANG?></label>
            <p class="help-block"><?php echo $MSG_PLS_ADD?>（按住 Ctrl/Command 可多选）</p>
            <select name="lang[]" multiple="multiple" class="contest-lang-select form-control">
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
          </div>

          <div class="contest-form-group">
            <label><?php echo $MSG_FORBIDDEN?></label>
            <p class="help-block">默认采用考试防作弊配置，可按实际场景取消勾选。</p>
            <div class="contest-lock-list">
            <?php
              $locks_count = count($contest_locks);
              $contest_lock = 0;
              $contest_type = 303;
              for($i=0; $i<$locks_count; $i++){
                echo "<label class='contest-check-item'><input type=checkbox name='contest_type[]' value=$i ".( $contest_type&(1<<$i)?"checked":"")."> <span>".$contest_locks[$i]."</span></label>";
              }
            ?>
            </div>
          </div>
        </div>
      </div>

      <div class="contest-add-card">
        <h4 class="card-title">访问权限</h4>
        <div class="card-body">
          <div class="contest-form-row">
            <div class="contest-form-group">
              <label><?php echo $MSG_CONTEST."-".$MSG_Public?></label>
              <select name=private class="form-control" style="max-width:220px;">
                <option value=0 <?php echo $private=='0'?'selected=selected':''?>><?php echo $MSG_Public?></option>
                <option value=1 <?php echo $private=='1'?'selected=selected':''?>><?php echo $MSG_Private?></option>
              </select>
              <p class="help-block">控制谁能参加竞赛；私有竞赛需要密码或参赛用户权限。</p>
            </div>
            <div class="contest-form-group">
              <label><?php echo $MSG_CONTEST."-".$MSG_PASSWORD?></label>
              <input type=text name=password class="form-control" style="max-width:260px;" value="">
              <p class="help-block">私有竞赛可设置访问密码。</p>
            </div>
          </div>

          <div class="contest-form-row">
            <div class="contest-form-group full">
              <label><?php echo $MSG_CONTEST."-".$MSG_USER?></label>
              <div class="contest-user-tools">
                <span class="help-block" style="margin:0;">每行一个用户 ID</span>
                <select id="copy_from" class="form-control" style="width:auto;" onchange="copy_user_from_contest($(this).val());">
                  <option value=0><?php echo $MSG_COPY_USER_LIST_FROM_CONTEST ?></option>
                  <?php
                    $contests="0";
                    foreach($_SESSION as $right=>$value){
                      if(strpos($right,$OJ_NAME.'_m')===0){
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
              </div>
              <textarea id="ulist" name='ulist' rows='8' class="form-control" placeholder='user1&#10;user2&#10;user3&#10;<?php echo $MSG_PRIVATE_USERS_ADD?>'><?php echo htmlentities($ulist, ENT_QUOTES, 'UTF-8');?></textarea>
            </div>
          </div>
        </div>
      </div>

      <?php if(isset($school_list) && is_array($school_list)): ?>
      <div class="contest-add-card">
        <h4 class="card-title"><?php echo $MSG_SCHOOL?></h4>
        <div class="card-body">
          <div class="contest-form-row">
            <div class="contest-form-group">
              <label><?php echo $MSG_SCHOOL?></label>
              <select name="school_id" class="form-control">
                <option value="">选择学校</option>
                <?php foreach ($school_list as $school): ?>
                  <option value="<?php echo $school['id'] ?>" <?php echo ($current_user_school_id == $school['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="contest-form-group">
              <label>公开范围</label>
              <label class="contest-check-item" style="margin-top:8px;">
                <input type="checkbox" name="is_public" value="1"> <span>公开比赛（允许其他学校访问）</span>
              </label>
              <p class="help-block">控制哪些学校的用户能在竞赛列表中看到这个比赛。</p>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="contest-actions">
        <?php require_once("../include/set_post_key.php");?>
        <input type=submit class="btn btn-primary" value='<?php echo $MSG_SAVE?>' name=submit>
      </div>
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
    window._ps_selected_order = [];
    for(let i=0;i<pids.length;i++){
      window._ps_selected[pids[i]]=true;
      window._ps_selected_order.push(pids[i]);
    }
    updateSelectedList();
  }

  function closeProblemSearch(){
    $("#ps_dialog").remove();
  }

  function confirmProblemSelect(){
    let pids = [];
    (window._ps_selected_order || []).forEach(function(pid){
      if(window._ps_selected[pid]) pids.push(parseInt(pid));
    });
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
      if(!window._ps_selected[pid]){
        window._ps_selected[pid]=true;
        if(!window._ps_selected_order) window._ps_selected_order=[];
        window._ps_selected_order.push(pid);
      }
    }else{
      delete window._ps_selected[pid];
      window._ps_selected_order=(window._ps_selected_order || []).filter(function(v){return parseInt(v)!==parseInt(pid);});
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
    (window._ps_selected_order || []).forEach(function(pid){
      if(window._ps_selected[pid]) pids.push(parseInt(pid));
    });
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
    window._ps_selected_order=(window._ps_selected_order || []).filter(function(v){return parseInt(v)!==parseInt(pid);});
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
