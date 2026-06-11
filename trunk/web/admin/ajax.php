<?php
ini_set("display_errors", "Off");  //set this to "On" for debugging  ,especially when no reason blank shows up.
require_once("../include/db_info.inc.php");
if(!(isset($_SESSION[$OJ_NAME.'_administrator'])||isset($_SESSION[$OJ_NAME.'_problem_editor'])||isset($_SESSION[$OJ_NAME.'_contest_creator'])||isset($_SESSION[$OJ_NAME.'_tag_adder']))){
  echo "<a href='../loginpage.php'>Please Login First!</a>";
  exit(1);  
}
function try_ajax($tb,$fd,$pr){
	global $OJ_NAME,$_SESSION,$_POST;
	$m=$_POST["m"];	
	if($m==$tb."_update_".$fd  && ( isset($_SESSION[$OJ_NAME.'_'.$pr]) )){
                $data_id=$_POST[$tb.'_id'];
                $new_value=$_POST[$fd];
		if($tb=="user") $tb_name="users";
		else $tb_name=$tb;
                $sql="update ".$tb_name." set `".$fd."`=? where ".$tb."_id=?";
                echo pdo_query($sql,$new_value,$data_id);
        }
}
function uniqueSource($str) {
    // 用正则分割字符串，支持多个连续空格
    $arr = preg_split('/\s+/', trim($str));

    // 去除数组中的重复项
    $uniqueArr = array_unique($arr);

    // 重新拼接为空格分割的字符串
    $result = implode(' ', $uniqueArr);

    return $result;
}
if($_SERVER['REQUEST_METHOD']=="POST"){
	$m=$_POST["m"];	
	if($m=="problem_set_source" && ( isset($_SESSION[$OJ_NAME.'_administrator']) || isset($_SESSION[$OJ_NAME.'_problem_editor']) || isset($_SESSION[$OJ_NAME.'_tag_adder']) ) ){
		$pid=intval($_POST['pid']);
		$new_source=uniqueSource($_POST['ns']);	
		$sql= "update problem set source=? where problem_id=?";		
		echo pdo_query($sql,$new_source,$pid);
		//echo $sql." [".$new_source."]";
	}
	if($m=="problem_add_source" && ( isset($_SESSION[$OJ_NAME.'_administrator']) || isset($_SESSION[$OJ_NAME.'_problem_editor']) || isset($_SESSION[$OJ_NAME.'_tag_adder']) ) ){
		$pid=intval($_POST['pid']);
		$new_source=($_POST['ns']);	
		$old_source=pdo_query("select source from problem where problem_id=?",$pid)[0][0];
		$new_source=uniqueSource($new_source." ".$old_source);
		$sql= "update problem set source=? where problem_id=?";		
		echo pdo_query($sql,$new_source,$pid);
		//echo $sql;
	}
	if($m=="problem_update_time" && ( isset($_SESSION[$OJ_NAME.'_administrator']) || isset($_SESSION[$OJ_NAME.'_problem_editor']) ) ){
		$pid=intval($_POST['pid']);
		$time=intval($_POST['t']);	
		$sql= "update problem set time_limit=? where problem_id=?";		
		echo pdo_query($sql,$time,$pid);
	}
	if($m=="problem_get_title"  && ( isset($_SESSION[$OJ_NAME.'_administrator']) || isset($_SESSION[$OJ_NAME.'_problem_editor']) )){
                $pid=intval($_POST['pid']);
                $sql= "select title,source from problem where problem_id=?";
                $row=mysql_query_cache($sql,$pid)[0];
                echo $row['title']."&nbsp;&nbsp;<span class='label label-success'>".$row['source']."</span>";
	}
	
        if($m=="user_update_nick"  && ( isset($_SESSION[$OJ_NAME.'_administrator']) )){
                $user_id=$_POST['user_id'];
                $nick=$_POST['nick'];
                $sql= "update users set nick=? where user_id=?";
                echo pdo_query($sql,$nick,$user_id);
		$sql= "update solution set nick=? where user_id=?";
                pdo_query($sql,$nick,$user_id);
        }
	if($m=="user_update_email"  && ( isset($_SESSION[$OJ_NAME.'_administrator']) )){
                $user_id=$_POST['user_id'];
                $email=$_POST['email'];
                $sql="update users set email=? where user_id=?";
                echo pdo_query($sql,$email,$user_id);
	}
	/*
        if($m=="user_update_expiry_date"  && ( isset($_SESSION[$OJ_NAME.'_administrator']) )){
                $user_id=$_POST['user_id'];
                $expiry_date=$_POST['expiry_date'];
                $sql= "update users set expiry_date=? where user_id=?";
                echo pdo_query($sql,$expiry_date,$user_id);
        }
	if($m=="user_update_school"  && ( isset($_SESSION[$OJ_NAME.'_administrator']) )){
                $user_id=$_POST['user_id'];
                $school=$_POST['school'];
                $sql= "update users set school=? where user_id=?";
                echo pdo_query($sql,$school,$user_id);
        }
	if($m=="user_update_group_name"  && ( isset($_SESSION[$OJ_NAME.'_administrator']) )){
                $user_id=$_POST['user_id'];
                $group_name=$_POST['group_name'];
                $sql="update users set group_name=? where user_id=?";
                echo pdo_query($sql,$group_name,$user_id);
	}
	 */
	// try_ajax("user","nick","administrator");
	try_ajax("user","expiry_date","administrator");
	// try_ajax("user","school","administrator"); // 已废弃，改用下面的 user_update_school_schoolid
	try_ajax("user","group_name","administrator");
	
	// 用户学校更新（同时更新 school_id 和 school）
	if($m=="user_update_school_schoolid" && isset($_SESSION[$OJ_NAME.'_administrator'])){
		$user_id=$_POST['user_id'];
		$school_id=intval($_POST['school_id']);
		
		// 引入学校函数库
		if (file_exists('../include/school.php')) {
			require_once('../include/school.php');
			// 使用 setUserSchool 函数同时更新 school_id 和 school
			$result = setUserSchool($user_id, $school_id);
			echo $result ? 1 : 0;
		} else {
			// 如果 school.php 不存在，回退到只更新 school 名称
			$school=$_POST['school'];
			$sql= "update users set school=? where user_id=?";
			echo pdo_query($sql,$school,$user_id);
		}
	}
	try_ajax("news","importance","administrator");
	try_ajax("problem","time_limit","administrator");
        try_ajax("problem","memory_limit","administrator");

	if($m=="get_user_list_of_contest"  && ( isset($_SESSION[$OJ_NAME.'_administrator'])||isset($_SESSION[$OJ_NAME.'_contest_creator']) )){
			$contest_id=$_POST['contest_id'];
			$sql= "select distinct user_id from privilege where rightstr=? ";
			$users=pdo_query($sql,"c".$contest_id);
			foreach($users as $user){
					echo $user['user_id']."\r\n";
			}
	}

	if($m=="problem_search" && ( isset($_SESSION[$OJ_NAME.'_administrator'])||isset($_SESSION[$OJ_NAME.'_contest_creator'])||isset($_SESSION[$OJ_NAME.'_problem_editor']) )){
		$keyword=isset($_POST['keyword'])?trim($_POST['keyword']):"";
		$source=isset($_POST['source'])?trim($_POST['source']):"";
		$problem_type=isset($_POST['problem_type'])?trim($_POST['problem_type']):"";
		$level=isset($_POST['level'])?trim($_POST['level']):"";
		$limit=isset($_POST['limit'])?intval($_POST['limit']):50;
		if($limit>200)$limit=200;

		$where=array();
		$params=array();

		// 与 problem_list.php 保持一致：5个字段模糊搜索
		if($keyword!=""){
			$where[]="(problem_id LIKE ? OR title LIKE ? OR description LIKE ? OR source LIKE ? OR hint LIKE ?)";
			$kw="%$keyword%";
			$params=array_merge($params,array($kw,$kw,$kw,$kw,$kw));
		}
		// 与 problem_list.php 保持一致：source 模糊搜索
		if($source!=""){
			$where[]="source LIKE ?";
			$params[]="%$source%";
		}
		// 与 problem_list.php 保持一致：problem_type 精确匹配
		if($problem_type!=""){
			$where[]="problem_type=?";
			$params[]=$problem_type;
		}
		// 与 problem_list.php 保持一致：level 用 BETWEEN 方式
		if($level!=""){
			$lv=intval($level);
			$where[]="level BETWEEN ? AND ?";
			$params[]=$lv;
			$params[]=$lv+1;
		}

		// 加载学校相关函数，添加学校隔离
		if (file_exists("../include/school.php")) {
			require_once("../include/school.php");
			$school_filter = getSchoolSQLFilter('', 'school_id', 'is_public');
			if ($school_filter) {
				$where[] = substr($school_filter, 5); // 去掉开头的 ' AND '
			}
		}

		// 统一添加 1=1 确保有 WHERE 条件，简化 SQL 拼接逻辑；
		// LIMIT 使用 intval 直接拼接而非参数绑定：pdo_query 通过 execute(array) 传参时，
		// PDO 会把所有值当作字符串处理（如 LIMIT '50'），在 MariaDB 中会触发语法错误；
		// $limit 已通过 intval + 200 上限双重校验，拼接无注入风险
		if(count($where)==0){
			$where[] = "1=1";
		}
		$sql="SELECT `problem_id`,`title`,`source`,`level`,`problem_type` FROM `problem` WHERE ".implode(" AND ",$where)." ORDER BY `problem_id` ".($keyword||$source||$problem_type||$level ? "ASC" : "DESC")." LIMIT ".intval($limit);

		// 使用 ...$params 展开参数
		if(count($params)>0){
			$problems=pdo_query($sql,...$params);
		}else{
			$problems=pdo_query($sql);
		}

		header('Content-Type: text/html; charset=utf-8');
		if(count($problems)==0){
			echo "<tr><td colspan='6' align='center' style='padding:20px;color:#999;'>未找到匹配的题目</td></tr>";
		}else{
			foreach($problems as $p){
				$ptype=$p['problem_type'];
				$type_text=$ptype;
				if($ptype=="programming")$type_text="编程";
				else if($ptype=="choice_single")$type_text="单选";
				else if($ptype=="choice_multi")$type_text="多选";
				else if($ptype=="judge")$type_text="判断";
				echo "<tr>".
						"<td><input type='checkbox' class='ps_cb' value='".intval($p['problem_id'])."' onclick='toggleProblem(".intval($p['problem_id']).",this)'></td>".
						"<td>".intval($p['problem_id'])."</td>".
						"<td>".htmlentities($p['title'],ENT_QUOTES,'UTF-8')."</td>".
						"<td>".htmlentities($p['source'],ENT_QUOTES,'UTF-8')."</td>".
						"<td>".htmlentities($type_text,ENT_QUOTES,'UTF-8')."</td>".
						"<td>".intval($p['level'])."</td>".
					 "</tr>";
			}
		}
	}

}

