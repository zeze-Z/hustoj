<?php
require_once("./include/db_info.inc.php");
if (isset($OJ_REGISTER) && !$OJ_REGISTER) exit(0);
require_once("./include/my_func.inc.php");
require_once('./include/setlang.php');
require_once("./include/email.class.php");     // 新版本的邮件发送信息请填写到db_info.inc.php
require_once(dirname(__FILE__)."/include/feishu_notify.php");
if (isset($OJ_CSRF) && $OJ_CSRF) require_once("./include/csrf_check.php");

// 初始化错误信息和计数器
$err_str = "";
$err_cnt = 0;
$len;

// 获取并验证用户提交的注册信息
$user_id = trim($_POST['user_id']);
$len = mb_strlen($user_id);
$email = trim($_POST['email']);

// 获取角色参数，默认为student
$role = isset($_POST['role']) ? trim($_POST['role']) : 'student';
if (!in_array($role, ['teacher', 'student'])) {
    $role = 'student';
}

// 处理学校：优先使用下拉选择的学校ID
$school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;
$school = "";
if ($school_id > 0) {
    // 根据school_id获取学校名称
    require_once("./include/school.php");
    $school = getSchoolName($school_id);
} else {
    // 兼容旧的文本输入
    $school = trim($_POST['school']);
}

// 如果启用验证码，则获取验证码
if (isset($OJ_VCODE) && $OJ_VCODE) $vcode = trim($_POST['vcode']);

// 验证验证码是否正确
if ($OJ_VCODE && ($vcode != $_SESSION[$OJ_NAME . '_' . "vcode"] || $vcode == "" || $vcode == null)) {
    $_SESSION[$OJ_NAME . '_' . "vcode"] = null;
    $err_str = $err_str . "Verification Code Wrong!\\n";
    $err_cnt++;
    $_SESSION[$OJ_NAME . '_' . "vfail"] = true;
}

// 检查登录模块是否为hustoj，如果不是则禁止注册
if ($OJ_LOGIN_MOD != "hustoj") {
    $err_str = $err_str . "$MSG_SYSTEM $MSG_DISABLE $MSG_REGISTER \\n";
    $err_cnt++;
}

// 验证用户名长度
if ($len > 48) {
    $err_str = $err_str . "$MSG_USER_ID $MSG_TOO_LONG !\\n";
    $err_cnt++;
} else if ($len < 3) {
    $err_str = $err_str . " $MSG_WARNING_USER_ID_SHORT\\n";
    $err_cnt++;
}

// 验证用户名格式是否有效
if (!is_valid_user_name($user_id)) {
    $err_str = $err_str . "$MSG_USER_ID $MSG_WRONG !\\n";
    $err_cnt++;
}

// 昵称自动使用用户名（简化注册流程，不再要求用户填写昵称）
$nick = $user_id;

// 检查用户名、学校、昵称是否包含不良词汇
if (has_bad_words($user_id)) {
    $err_str = $err_str . $MSG_USER_ID . "$MSG_TOO_BAD!\\n";
    $err_cnt++;
}
if (has_bad_words($school)) {
    $err_str = $err_str . $MSG_SCHOOL . " $MSG_TOO_BAD!\\n";
    $err_cnt++;
}
if (has_bad_words($nick)) {
    $err_str = $err_str . $MSG_NICK . " $MSG_TOO_BAD!\\n";
    $err_cnt++;
}

// 验证两次输入的密码是否一致
if (strcmp($_POST['password'], $_POST['rptpassword']) != 0) {
    $err_str = $err_str . "$MSG_WARNING_REPEAT_PASSWORD_DIFF!\\n";
    $err_cnt++;
}

// 验证密码长度是否小于6位
if (strlen($_POST['password']) < 6) {
    $err_cnt++;
    $err_str = $err_str . "$MSG_WARNING_PASSWORD_SHORT \\n";
}

// 验证学校名称长度（仅在有旧文本输入时校验）
if (!empty($_POST['school'])) {
    $len = mb_strlen($_POST['school']);
    if ($len > 20) {
        $err_str = $err_str . "$MSG_SCHOOL $MSG_TOO_LONG!\\n";
        $err_cnt++;
    }
}

// 验证邮箱长度
$len = mb_strlen($_POST['email']);
if ($len > 100) {
    $err_str = $err_str . "$MSG_EMAIL $MSG_TOO_LONG!\\n";
    $err_cnt++;
}

// 验证邮箱格式
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err_str = $err_str . "邮箱格式不正确！\\n";
    $err_cnt++;
}

// 如果存在错误信息，则显示错误并返回
if ($err_cnt > 0) {
    print "<script language='javascript'>\n";
    print "alert('";
    print $err_str;
    print "');\n history.go(-1);\n</script>";
    exit(0);

}

// 生成加密密码
$password = pwGen($_POST['password']);

// 检查用户名是否已存在
$sql = "SELECT `user_id` FROM `users` WHERE `users`.`user_id` = ?";
$result = pdo_query($sql, $user_id);
$rows_cnt = count($result);
if ($rows_cnt == 1) {
    print "<script language='javascript'>\n";
    print "alert('$MSG_USER_ID Existed!\\n');\n";
    print "history.go(-1);\n</script>";
    exit(0);
}

// 检查邮箱是否已存在（邮箱唯一性校验）
$sql = "SELECT `user_id` FROM `users` WHERE `users`.`email` = ?";
$result = pdo_query($sql, $email);
$rows_cnt = count($result);
if ($rows_cnt == 1) {
    print "<script language='javascript'>\n";
    print "alert('该邮箱已注册，请直接登录！\\n');\n";
    print "window.location.href='loginpage.php';\n</script>";
    exit(0);
}

// 检查特殊用户ID是否冲突
if ($domain == $DOMAIN && $OJ_NAME == $user_id) {
    print "<script language='javascript'>\n";
    print "alert('$MSG_USER_ID Existed!\\n');\n";
    print "history.go(-1);\n</script>";
    exit(0);
}

// 对用户输入进行HTML实体编码以防止XSS攻击
$nick = (htmlentities($nick, ENT_QUOTES, "UTF-8"));
$school = (htmlentities($school, ENT_QUOTES, "UTF-8"));
$email = (htmlentities($email, ENT_QUOTES, "UTF-8"));
$ip = ($_SERVER['REMOTE_ADDR']);

// 获取真实IP地址，处理代理服务器情况
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty(trim($_SERVER['HTTP_X_FORWARDED_FOR']))) {
    $REMOTE_ADDR = $_SERVER['HTTP_X_FORWARDED_FOR'];
    $tmp_ip = explode(',', $REMOTE_ADDR);
    $ip = (htmlentities($tmp_ip[0], ENT_QUOTES, "UTF-8"));
} else if (isset($_SERVER['HTTP_X_REAL_IP']) && !empty(trim($_SERVER['HTTP_X_REAL_IP']))) {
    $REMOTE_ADDR = $_SERVER['HTTP_X_REAL_IP'];
    $tmp_ip = explode(',', $REMOTE_ADDR);
    $ip = (htmlentities($tmp_ip[0], ENT_QUOTES, "UTF-8"));
}

// 检查IP是否已经注册过
if (isset($OJ_REG_SPEED) && $OJ_REG_SPEED > 0) {

    // 查询最近1小时内该IP地址已经注册的用户数量
    $sql = "SELECT COUNT(*) FROM `users` WHERE (`ip` = ? or email = ? ) AND `reg_time` > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    $result = pdo_query($sql, $ip, $email);
    $count = intval($result[0][0]);

    if ($count > $OJ_REG_SPEED) {
        // 如果数量大于$OJ_REG_SPEED ，则表示该IP地址在最近1小时内已经注册过$OJ_REG_SPEED个账户
        $warning = "$ip 正在快速注册大量新账号，请确认是否存在攻击行为。若能确认是攻击行为，可以用sudo iptables -A INPUT -s  $ip  -j DROP 命令 封禁IP。";
        // if ($OJ_ADMIN != "root@localhost") email($OJ_ADMIN, "系统警告,疑似攻击!", $warning . "\n from $domain");   //只有设置好的才发送邮件
        feishu_notify('系统警告,疑似攻击!', $warning . "\n from $domain", 'warn');
        print "<script language='javascript'>\n";
        print "alert('您的IP地址或Email已经注册过" . $OJ_REG_SPEED . "个账户，请稍后再试。\\n');\n";
        print "history.go(-1);\n</script>";
        exit(0);
    }
}

// 生成激活码或设置为空
if (isset($OJ_EMAIL_CONFIRM) && $OJ_EMAIL_CONFIRM)
    $_SESSION[$OJ_NAME . '_' . 'activecode'] = getToken(18);
else
    $_SESSION[$OJ_NAME . '_' . 'activecode'] = "";

// 根据是否需要确认设置用户状态
if (isset($OJ_REG_NEED_CONFIRM) && $OJ_REG_NEED_CONFIRM) $defunct = "Y";
else $defunct = "N";

// 插入新用户到数据库（包含 school_id 和 role）
$sql = "INSERT INTO `users`("
        . "`user_id`,`email`,`ip`,`accesstime`,`password`,`reg_time`,`nick`,`school`,`school_id`,`role`,`group_name`,`defunct`,activecode)"
        . "VALUES(?,?,?,NOW(),?,NOW(),?,?,?,?,?,?,?)";
$rows = pdo_query($sql, $user_id, $email, $ip, $password, $nick, $school, $school_id, $role, getMappedSpecial($user_id), $defunct, $_SESSION[$OJ_NAME . '_' . 'activecode']);

// 检查数据库插入是否成功
if ($rows === -1 || $rows === false) {
    print "<script language='javascript'>\n";
    print "alert('数据库错误，注册失败，请联系客服！');\n";
    print "history.go(-1);\n</script>";
    exit(0);
}

// 注册成功，自动发放 20 积分（新用户注册奖励）
// 积分系统使用 users.point 字段 + point_log 流水表（POINT_LOG_TYPE_SYSTEM=4）
$register_point_reward = 20;
point_tx_begin();
$point_result = point_apply_change(
    $user_id,
    $register_point_reward,
    POINT_LOG_TYPE_SYSTEM,
    null,
    '新用户注册奖励'
);
if ($point_result['success']) {
    point_tx_commit();
} else {
    point_tx_rollback();
    // 积分发放失败不阻断注册流程，仅记录日志
    error_log("Register: failed to grant {$register_point_reward} points to user {$user_id}: " . $point_result['message']);
}

// 飞书通知：区分教师/学生
require_once(dirname(__FILE__)."/include/feishu_notify.php");
if ($role === 'teacher') {
    feishu_notify(
        '🔔 教师入驻',
        "**用户ID**: $user_id\n" .
        "**昵称**: $nick\n" .
        "**学校**: $school" . ($school_id ? " (ID:$school_id)" : "") . "\n" .
        "**邮箱**: $email\n" .
        "**IP**: $ip\n" .
        "**时间**: " . date('Y-m-d H:i') . "\n\n" .
        "[建议] → 24h 内主动联系，确认教师入驻意向",
        'warn'
    );
} else {
    feishu_notify(
        '新用户注册（学生）',
        "**用户ID**: $user_id\n" .
        "**昵称**: $nick\n" .
        "**学校**: $school" . ($school_id ? " (ID:$school_id)" : "") . "\n" .
        "**邮箱**: $email\n" .
        "**IP**: $ip\n" .
        "**时间**: " . date('Y-m-d H:i'),
        'info'
    );
}

//发送激活邮件
if (isset($OJ_EMAIL_CONFIRM) && $OJ_EMAIL_CONFIRM) {
    $link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "active.php?code=" . $_SESSION[$OJ_NAME . '_' . 'activecode'];
    
    if ($role === 'teacher') {
        // 教师激活邮件
        $mail_subject = "$OJ_NAME" . "教师账号激活 — 开启智能化教学管理";
        $mail_text = "亲爱的老师，您好！\n\n" .
            "恭喜您完成注册，欢迎加入$OJ_NAME教学平台！\n\n" .
            "您的专属功能：\n" .
            "📚 课件中心 — 上传、管理、销售您的教学课件\n" .
            "📝 作业系统 — 在线布置编程作业，自动评测学生代码\n" .
            "📊 学生管理 — 查看学生学习进度和作业提交情况\n" .
            "🎮 趣味编程 — 游戏化教学工具，提升学生学习兴趣\n" .
            "💰 收益中心 — 课件销售数据透明，收益即时提现\n\n" .
            "⚠️ 重要提示：\n" .
            "目前课件上传功能需由管理员审核后开放。\n" .
            "激活账号后，请联系客服 QQ：2326077585 申请教师权限。\n\n" .
            "请点击以下链接激活账号：\n" . $link . "\n\n" .
            "激活后即可登录，如有任何问题，欢迎联系我们。\n\n" .
            "$OJ_NAME" . "教学平台";
        
        // HTML邮件内容
        $mail_html = "<div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px;'>
            <div style='background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 24px;'>$OJ_NAME教师账号激活</h1>
                </div>
                <div style='padding: 30px;'>
                    <p style='font-size: 16px; color: #333; line-height: 1.8;'>亲爱的 <strong>$nick</strong> 老师：</p>
                    <p style='font-size: 16px; color: #333; line-height: 1.8;'>恭喜您完成注册，欢迎加入$OJ_NAME教学平台！</p>
                    <div style='background: #f8f9ff; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px;'>
                        <h3 style='color: #667eea; margin-top: 0;'>您的专属功能</h3>
                        <ul style='color: #555; line-height: 2; margin: 0; padding-left: 20px;'>
                            <li>📚 课件中心 — 上传、管理、销售您的教学课件</li>
                            <li>📝 作业系统 — 在线布置编程作业，自动评测学生代码</li>
                            <li>📊 学生管理 — 查看学生学习进度和作业提交情况</li>
                            <li>🎮 趣味编程 — 游戏化教学工具，提升学生学习兴趣</li>
                            <li>💰 收益中心 — 课件销售数据透明，收益即时提现</li>
                        </ul>
                    </div>
                    <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px;'>
                        <h3 style='color: #856404; margin-top: 0;'>重要提示</h3>
                        <p style='color: #856404; line-height: 1.8; margin: 0;'>
                            目前课件上传功能需由管理员审核后开放。<br>
                            激活账号后，请联系客服 QQ：2326077585 申请教师权限。
                        </p>
                    </div>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$link' style='display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600;'>
                            点击激活账号
                        </a>
                    </div>
                    <p style='color: #999; font-size: 14px; line-height: 1.8; margin-top: 30px;'>
                        激活后即可登录，如有任何问题，欢迎联系我们。
                    </p>
                </div>
                <div style='background: #f5f5f5; padding: 15px; text-align: center; border-top: 1px solid #e0e0e0;'>
                    <p style='color: #999; font-size: 12px; margin: 0;'>
                        此邮件由$OJ_NAME平台自动发送，请勿直接回复。<br>
                        © $OJ_NAME版权所有
                    </p>
                </div>
            </div>
        </div>";
        
        email($email, $mail_subject, $mail_text, $mail_html);
    } else {
        // 学生激活邮件
        $mail_subject = "$OJ_NAME" . "账号激活 — 开始你的编程学习之旅";
        $mail_text = "亲爱的同学，您好！\n\n" .
            "欢迎加入$OJ_NAME教学平台！\n\n" .
            "平台特色：\n" .
            "🎮 趣味编程 — 边玩边学，轻松入门编程\n" .
            "📚 海量题库 — 从基础到进阶，满足不同学习需求\n" .
            "⚡ 在线评测 — 提交代码即时获得反馈\n" .
            "🏆 竞赛活动 — 参与各类编程竞赛，提升技能\n\n" .
            "请点击以下链接激活账号：\n" . $link . "\n\n" .
            "激活后即可登录，开启你的编程之旅！\n\n" .
            "$OJ_NAME" . "教学平台";
        
        // HTML邮件内容
        $mail_html = "<div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px;'>
            <div style='background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                <div style='background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 30px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 24px;'>$OJ_NAME账号激活</h1>
                </div>
                <div style='padding: 30px;'>
                    <p style='font-size: 16px; color: #333; line-height: 1.8;'>亲爱的 <strong>$nick</strong> 同学：</p>
                    <p style='font-size: 16px; color: #333; line-height: 1.8;'>欢迎加入$OJ_NAME教学平台！</p>
                    <div style='background: #f0fff4; border-left: 4px solid #38ef7d; padding: 20px; margin: 20px 0; border-radius: 4px;'>
                        <h3 style='color: #11998e; margin-top: 0;'>平台特色</h3>
                        <ul style='color: #555; line-height: 2; margin: 0; padding-left: 20px;'>
                            <li>🎮 趣味编程 — 边玩边学，轻松入门编程</li>
                            <li>📚 海量题库 — 从基础到进阶，满足不同学习需求</li>
                            <li>⚡ 在线评测 — 提交代码即时获得反馈</li>
                            <li>🏆 竞赛活动 — 参与各类编程竞赛，提升技能</li>
                        </ul>
                    </div>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$link' style='display: inline-block; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600;'>
                            点击激活账号
                        </a>
                    </div>
                    <p style='color: #999; font-size: 14px; line-height: 1.8; margin-top: 30px;'>
                        激活后即可登录，开启你的编程之旅！如有任何问题，欢迎联系我们。
                    </p>
                </div>
                <div style='background: #f5f5f5; padding: 15px; text-align: center; border-top: 1px solid #e0e0e0;'>
                    <p style='color: #999; font-size: 12px; margin: 0;'>
                        此邮件由$OJ_NAME平台自动发送，请勿直接回复。<br>
                        © $OJ_NAME版权所有
                    </p>
                </div>
            </div>
        </div>";
        
        email($email, $mail_subject, $mail_text, $mail_html);
    }

    print "<script src='https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js'></script>\n";
    print "<script src='template/$OJ_TEMPLATE/game_confetti.js'></script>\n";
    print "<script language='javascript'>\n";
    print "launchConfetti();\n";
    print "alert('注册成功！请前往邮箱激活账号，激活后即可获得20积分奖励。');\n";
    print "window.location.href='loginpage.php';\n";
    print "</script>";
    exit(0);
}

// 记录登录日志
$sql = "INSERT INTO `loginlog`(user_id,password,ip,time) VALUES(?,?,?,NOW())";
pdo_query($sql, $user_id, "no save", $ip);

// 如果不需要确认注册，则自动登录用户
if (!isset($OJ_REG_NEED_CONFIRM) || !$OJ_REG_NEED_CONFIRM) {
    $sql = "SELECT `user_id` FROM `users` WHERE `users`.`user_id` = ?";
    $result = pdo_query($sql, $user_id);
    $rows_cnt = count($result);
    if ($rows_cnt == 1) {
        $_SESSION[$OJ_NAME . '_' . 'user_id'] = $user_id;
        $_SESSION[$OJ_NAME . '_' . 'nick'] = $nick;
        $sql = "SELECT `rightstr` FROM `privilege` WHERE `user_id`=?";
        //echo $sql."<br />";
        $result = pdo_query($sql, $_SESSION[$OJ_NAME . '_' . 'user_id']);
        foreach ($result as $row) {
            $_SESSION[$OJ_NAME . '_' . $row['rightstr']] = true;
            //echo $_SESSION[$OJ_NAME.'_'.$row['rightstr']]."<br />";
        }
        $_SESSION[$OJ_NAME . '_' . 'ac'] = array();
        $_SESSION[$OJ_NAME . '_' . 'sub'] = array();
        if ($OJ_SaaS_ENABLE && $domain == $DOMAIN) header("location:modifypage.php#MyOJ");
         else header("location:index.php");
    }
}else{
    ?>
<script>
    alert("<?php echo "$MSG_SYSTEM $MSG_Pending $MSG_ADMIN / $MSG_EMAIL $MSG_ACTIVE_YOUR_ACCOUNT";?>");
    history.go(-2);
</script>
   <?php
}




