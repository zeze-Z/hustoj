<?php require_once("admin-header.php");
require_once("../include/email.class.php");
if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']))) {
	echo "<a href='../loginpage.php'>Please Login First!</a>";
	exit(1);
}
?>

<title>Privilege Add</title>
<hr>
<center><h3><?php echo $MSG_USER."-".$MSG_PRIVILEGE."-".$MSG_ADD?></h3></center>

<div class="padding">

<?php
if (isset($_POST['do'])) {
	require_once("../include/check_post_key.php");

	$user_id = trim($_POST['user_id']);
	$rightstr = trim($_POST['rightstr']);
	$valuestr = "true";
	if(isset($_POST['valuestr']))
		$valuestr = $_POST['valuestr'];
	
	if (isset($_POST['contest']))
		$rightstr = "c$rightstr";

	if (isset($_POST['psv']))
		$rightstr = "s$rightstr";

	$sql = "insert into `privilege`(user_id,rightstr,valuestr,defunct) values(?,?,?,'N')";
	$link= 'http://'.$_SERVER['HTTP_HOST'].'/';
        $msg = $_SESSION[$OJ_NAME.'_user_id']." $MSG_ADD $rightstr [$valuestr] $MSG_PRIVILEGE -> $user_id @  ".date('Y-m-d h:i:s a', time());
        $msg .="\n\nmessage from site: $link";
        if(!empty($user_id)) $rows = pdo_query($sql,$user_id,$rightstr,$valuestr);

        // 飞书通知：权限变更
        require_once(dirname(__FILE__)."/../include/feishu_notify.php");
        feishu_notify(
            '权限变更',
            "**操作**: 授予权限\n" .
            "**目标用户**: " . htmlentities($user_id, ENT_QUOTES, 'UTF-8') . "\n" .
            "**权限**: $rightstr\n" .
            "**值**: $valuestr\n" .
            "**操作人**: " . ($_SESSION[$OJ_NAME.'_user_id'] ?? 'system'),
            'warn'
        );

        // 教师权限开通：发送邮件通知用户
        if ($rightstr === 'teacher' && !empty($user_id)) {
            // 查询用户邮箱
            $user_email_sql = "SELECT email, nick FROM users WHERE user_id = ?";
            $user_email_result = pdo_query($user_email_sql, $user_id);
            
            if (!empty($user_email_result) && !empty($user_email_result[0]['email'])) {
                $user_email = $user_email_result[0]['email'];
                $user_nick = !empty($user_email_result[0]['nick']) ? $user_email_result[0]['nick'] : $user_id;
                
                // 构建HTML邮件内容
                $mail_html = "
                <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px;'>
                    <div style='background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                        <!-- 头部 -->
                        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                            <h1 style='color: white; margin: 0; font-size: 24px;'>🎉 恭喜！您已获得教师权限</h1>
                        </div>
                        
                        <!-- 内容区 -->
                        <div style='padding: 30px;'>
                            <p style='font-size: 16px; color: #333; line-height: 1.8;'>亲爱的 <strong>$user_nick</strong> 老师：</p>
                            
                            <p style='font-size: 16px; color: #333; line-height: 1.8;'>
                                您的 <strong>教师权限</strong> 已由管理员开通，现在您可以使用以下功能：
                            </p>
                            
                            <!-- 功能列表 -->
                            <div style='background: #f8f9ff; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px;'>
                                <h3 style='color: #667eea; margin-top: 0;'>✨ 您的专属权益</h3>
                                <ul style='color: #555; line-height: 2; margin: 0; padding-left: 20px;'>
                                    <li>免费获取海量优质课件与教案</li>
                                    <li>参与课件共创，获取收益分成</li>
                                    <li>创建并管理您班级的学生账号</li>
                                    <li>实时查看学生的答题情况与学习进度</li>
                                    <li>定制您学校的专属题库</li>
                                </ul>
                                <p style='color: #888; font-size: 13px; margin-top: 12px;'>注：创建学生账号、定制题库需联系客服开通</p>
                            </div>
                            
                            <!-- 收益说明 -->
                            <div style='background: #f0fff4; border-left: 4px solid #52c41a; padding: 20px; margin: 20px 0; border-radius: 4px;'>
                                <h3 style='color: #52c41a; margin-top: 0;'>💰 收益分成说明</h3>
                                <p style='color: #555; line-height: 1.8; margin: 0;'>
                                    您将优质课件上架到平台后，其他教师购买课件时，您将获得一定比例的收益分成。
                                    具体规则请咨询客服QQ。
                                </p>
                            </div>
                            
                            <!-- 操作引导 -->
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='".$link."' style='display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600;'>
                                    🚀 立即前往平台
                                </a>
                            </div>
                            
                            <p style='color: #999; font-size: 14px; line-height: 1.8; margin-top: 30px;'>
                                如有任何疑问，请联系客服QQ：2326077585
                            </p>
                        </div>
                        
                        <!-- 底部 -->
                        <div style='background: #f5f5f5; padding: 15px; text-align: center; border-top: 1px solid #e0e0e0;'>
                            <p style='color: #999; font-size: 12px; margin: 0;'>
                                此邮件由 $OJ_NAME 平台自动发送，请勿直接回复。<br>
                                © $OJ_NAME 版权所有
                            </p>
                        </div>
                    </div>
                </div>";
                
                // 发送HTML邮件
                $mail_subject = $OJ_NAME . ' - 教师权限已开通';
                email($user_email, $mail_subject, "您的教师权限已开通，登录平台后可使用课件相关功能。", $mail_html);
            }
        }

        if ($OJ_ADMIN=="root@localhost"){
                $sql="select email from users where user_id=? ";
                $OJ_ADMIN=pdo_query($sql,$_SESSION[$OJ_NAME.'_user_id'])[0][0];
		//email($OJ_ADMIN,"Privilege Add Warning!",$msg);
        }else{
//        	 	email($OJ_ADMIN,"Privilege Add Warning!",$msg);
	}
        echo "<center><h4 class='text-danger'>User ".htmlentities($_POST['user_id'], ENT_QUOTES, 'UTF-8')."'s Privilege Added!</h4></center>";
}
?>

<div>
<form method="post" class="form-horizontal">
	<?php require_once("../include/set_post_key.php");?>
	<center><label class="text-info"><?php echo $MSG_HELP_ADD_PRIVILEGE?></label></center>
	<div class="form-group">
		<label class="col-sm-offset-3 col-sm-3 control-label"><?php echo $MSG_USER_ID?></label>
		<?php if(isset($_GET['uid'])) { ?>
		<div class="col-sm-3"><input name="user_id" class="form-control" value="<?php echo htmlentities($_GET['uid'], ENT_QUOTES, 'UTF-8');?>" type="text" required ></div>
  	<?php } else if(isset($_POST['user_id'])) { ?>
		<div class="col-sm-3"><input name="user_id" class="form-control" value="<?php echo htmlentities($_POST['user_id'], ENT_QUOTES, 'UTF-8');?>" type="text" required ></div>
		<?php } else { ?>
		<div class="col-sm-3"><input name="user_id" class="form-control" placeholder="<?php echo $MSG_USER_ID."*"?>" type="text" required ></div>
		<?php } ?>
	</div>

	<div class="form-group">
		<label class="col-sm-offset-3 col-sm-3 control-label"><?php echo $MSG_PRIVILEGE_TYPE?></label>
		<select class="col-sm-3" name="rightstr" onchange="show_value_input(this.value)" >
		<?php
			$rightarray = array("administrator","teacher","problem_editor","problem_importer","tag_adder","problem_verifiter","source_browser","contest_creator","user_adder","http_judge","password_setter","printer","balloon","vip",'problem_start','problem_end','service_port');
			while ($val=current($rightarray)) {
                                $key=key($rightarray);
                                if (isset($rightstr) && ($rightstr == $val)) {
                                        echo '<option value="'.$val.'" selected>'.$val.'</option>';
                                } else {
                                        echo '<option value="'.$val.'">'.$val.'</option>';
                                }
                                next($rightarray);
                        }
		?>
		</select>
		<div class="col-sm-offset-9"><input id='value_input' type="text" class="form-control" name="valuestr" value="true"></div>
	</div>
	<script>
		function show_value_input(new_value){
			if(new_value=='problem_start'||new_value=='problem_end') {
				$("#value_input").val("1000");
				$("#value_input").show();
			}else{
				$("#value_input").val("true");
				$("#value_input").hide();
			}
		}
		$(document).ready(function(){
			 $("#value_input").hide();
		});
	</script>
	<div class="form-group">
		<div class="col-sm-offset-4 col-sm-2">
			<input type='hidden' name='do' value='do'>
			<button type="submit" name="do" value="do" class="btn btn-default btn-block" ><?php echo $MSG_SAVE?></button>
		</div>
		<div class="col-sm-2">
			<button type="reset" class="btn btn-default btn-block"><?php echo $MSG_RESET?></button>
		</div>
	</div>
</form>
</div>

<br>

<div>
<form method="post" class="form-horizontal">
	<?php require_once("../include/set_post_key.php");?>
	<center><label class="text-info"><?php echo $MSG_HELP_ADD_CONTEST_USER?></label></center>
	<div class="form-group">
		<label class="col-sm-offset-3 col-sm-3 control-label"><?php echo $MSG_USER_ID?></label>
		<?php if(isset($_GET['uid'])) { ?>
		<div class="col-sm-3"><input name="user_id" class="form-control" value="<?php echo htmlentities($_GET['uid'], ENT_QUOTES, 'UTF-8');?>" type="text" required ></div>
  	<?php } else if(isset($_POST['user_id'])) { ?>
		<div class="col-sm-3"><input name="user_id" class="form-control" value="<?php echo htmlentities($_POST['user_id'], ENT_QUOTES, 'UTF-8');?>" type="text" required ></div>
		<?php } else { ?>
		<div class="col-sm-3"><input name="user_id" class="form-control" placeholder="<?php echo $MSG_USER_ID."*"?>" type="text" required ></div>
		<?php } ?>
	</div>

	<div class="form-group">
		<label class="col-sm-offset-3 col-sm-3 control-label"><?php echo $MSG_CONTEST_ID?></label>
		<div class="col-sm-3"><input name="rightstr" class="form-control" placeholder="<?php echo $MSG_CONTEST_ID."*"?>" type="text"></div>
	</div>

	<div class="form-group">
		<div class="col-sm-offset-4 col-sm-2">
			<input type='hidden' name='do' value='do'>
			<button type="submit" name="contest" value="do" class="btn btn-default btn-block" ><?php echo $MSG_SAVE?></button>
			<input type=hidden name="postkey" value="<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']?>">
		</div>
		<div class="col-sm-2">
			<button type="reset" class="btn btn-default btn-block"><?php echo $MSG_RESET?></button>
		</div>
	</div>
</form>
</div>

<br>

<div>
<form method="post" class="form-horizontal">
	<?php require_once("../include/set_post_key.php");?>
	<center><label class="text-info"><?php echo $MSG_HELP_ADD_SOLUTION_VIEW?></label></center>
	<div class="form-group">
		<label class="col-sm-offset-3 col-sm-3 control-label"><?php echo $MSG_USER_ID?></label>
		<?php if(isset($_GET['uid'])) { ?>
		<div class="col-sm-3"><input name="user_id" class="form-control" value="<?php echo htmlentities($_GET['uid'], ENT_QUOTES, 'UTF-8');?>" type="text" required ></div>
  	<?php } else if(isset($_POST['user_id'])) { ?>
		<div class="col-sm-3"><input name="user_id" class="form-control" value="<?php echo htmlentities($_POST['user_id'], ENT_QUOTES, 'UTF-8');?>" type="text" required ></div>
		<?php } else { ?>
		<div class="col-sm-3"><input name="user_id" class="form-control" placeholder="<?php echo $MSG_USER_ID."*"?>" type="text" required ></div>
		<?php } ?>
	</div>

	<div class="form-group">
		<label class="col-sm-offset-3 col-sm-3 control-label"><?php echo $MSG_PROBLEM_ID?></label>
		<div class="col-sm-3"><input name="rightstr" class="form-control" placeholder="<?php echo $MSG_PROBLEM_ID."*"?>" type="text"></div>
	</div>

	<div class="form-group">
		<div class="col-sm-offset-4 col-sm-2">
			<input type='hidden' name='do' value='do'>
			<button type="submit" name="psv" value="do" class="btn btn-default btn-block" ><?php echo $MSG_SAVE?></button>
			<input type=hidden name="postkey" value="<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']?>">
			</div>
		<div class="col-sm-2">
			<button type="reset" class="btn btn-default btn-block"><?php echo $MSG_RESET?></button>
		</div>
	</div>
</form>
</div>

</div>
