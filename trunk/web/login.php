<?php
require_once("./include/db_info.inc.php");
require_once("./include/my_func.inc.php");
require_once("./include/login_reward.php");
require_once('./include/setlang.php');
require_once(dirname(__FILE__)."/include/feishu_notify.php");
if (isset($OJ_CSRF) && $OJ_CSRF) require_once("./include/csrf_check.php");
$use_cookie = false;
$login = false;

// 获取客户端IP
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty(trim($_SERVER['HTTP_X_FORWARDED_FOR']))) {
    $tmp_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($tmp_ip[0]);
} else if (isset($_SERVER['HTTP_X_REAL_IP']) && !empty(trim($_SERVER['HTTP_X_REAL_IP']))) {
    $tmp_ip = explode(',', $_SERVER['HTTP_X_REAL_IP']);
    $ip = trim($tmp_ip[0]);
}
if ($OJ_LONG_LOGIN && isset($_COOKIE[$OJ_NAME . "_user"]) && isset($_COOKIE[$OJ_NAME . "_check"])) {
    $C_check = $_COOKIE[$OJ_NAME . "_check"];
    $C_user = $_COOKIE[$OJ_NAME . "_user"];
    $use_cookie = true;
    $C_num = strlen($C_check) - 1;
    $C_num = ($C_num * $C_num) % 7;
    if ($C_check[strlen($C_check) - 1] != $C_num) {
        setcookie($OJ_NAME . "_check", "", 0);
        setcookie($OJ_NAME . "_user", "", 0);
        echo "<script>\n alert('Cookie失效或错误!(-1)'); \n history.go(-1); \n </script>";
        exit(0);
    }
    $C_info = pdo_query("SELECT `password`,`accesstime` FROM `users` WHERE `user_id`=? and defunct='N'", $C_user)[0];
    $C_len = strlen($C_info[1]);
    for ($i = 0; $i < strlen($C_info[0]); $i++) {
        $tp = ord($C_info[0][$i]);
        $C_res .= chr(39 + ($tp * $tp + ord($C_info[1][$i % $C_len]) * $tp) % 88);
    }
    if (substr($C_check, 0, -1) == sha1($C_res))
        $login = $C_user;
    else {
        setcookie($OJ_NAME . "_check", "", 0);
        setcookie($OJ_NAME . "_user", "", 0);
        echo "<script>\n alert('Cookie失效或错误!(-2)'); \n history.go(-1); \n </script>";
        exit(0);
    }
}
$vcode = "";
if (!$use_cookie) {
    if (isset($_POST['vcode'])) $vcode = trim($_POST['vcode']);
    if ($OJ_VCODE && ($vcode != $_SESSION[$OJ_NAME . '_' . "vcode"] || $vcode == "" || $vcode == null)) {
        $_SESSION[$OJ_NAME . '_' . "vfail"] = true;
        echo "<script language='javascript'>\n";
        echo "alert('Verify Code Wrong!');\n";
        echo "history.go(-1);\n";
        echo "</script>";
        exit(0);
    }
    $view_errors = "";
    require_once("./include/login-" . $OJ_LOGIN_MOD . ".php");
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];
    $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime("-5 minutes"));
    $failed = pdo_query("SELECT
                        (SELECT COUNT(1) FROM loginlog WHERE user_id=? AND password='login fail' AND time>=?) as user_fail,
                        (SELECT COUNT(1) FROM loginlog WHERE ip=? AND password='login fail' AND time>=?) as ip_fail;", $user_id, $fiveMinutesAgo, $ip, $fiveMinutesAgo);
    if (isset($OJ_LOGIN_FAIL_LIMIT) && ($OJ_LOGIN_FAIL_LIMIT > 0) && ($failed[0][0] > $OJ_LOGIN_FAIL_LIMIT || $failed[0][1] > $OJ_LOGIN_FAIL_LIMIT * 4)) {
        $view_errors = "Failed login too frequently!";
        require("template/" . $OJ_TEMPLATE . "/error.php");
        exit(0);
    }
    $login = check_login($user_id, $password);
}
if ($login) {
    //提取组名
    session_regenerate_id();
    $group_name = "";
    $school_id = null;
    $school = "";
    
    // 异地登录检测（仅监控 admin 用户）
    $lastLogin = pdo_query("SELECT ip, time FROM loginlog WHERE user_id=? AND password='login ok' ORDER BY time DESC LIMIT 1", $login);
    if (!empty($lastLogin) && $login === 'admin') {
        $lastIp = $lastLogin[0]['ip'];
        $lastTime = $lastLogin[0]['time'];

        // 简单异地检测：IP网段不同即视为异地（可替换为IP地理库实现更精确的城市级检测）
        $currentIpSegments = explode('.', $ip);
        $lastIpSegments = explode('.', $lastIp);
        $isRemoteLogin = ($currentIpSegments[0] != $lastIpSegments[0] || $currentIpSegments[1] != $lastIpSegments[1]);

        // 异地登录检测：IP段不同即发送告警
        if ($isRemoteLogin) {
            $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
            feishu_notify(
                "⚠️ 异地登录告警",
                "**告警类型**: 异地登录检测\n" .
                "**用户ID**: $login\n" .
                "**当前登录IP**: $ip\n" .
                "**上次登录IP**: $lastIp\n" .
                "**上次登录时间**: $lastTime\n" .
                "**User Agent**: $ua\n" .
                "**检测时间**: " . date('Y-m-d H:i:s') . "\n" .
                "**建议**: 如非本人操作，请立即修改密码并联系管理员",
                'warn'
            );
        }
    }
    $group_row = pdo_query("select group_name,nick,school_id,school,defunct,new_user_reward_claimed from users where user_id=?", $login);
    if (!empty($group_row)) {
        $group_name = $group_row[0]['group_name'];
        $school_id = $group_row[0]['school_id'];
        $school = $group_row[0]['school'];
        $_SESSION[$OJ_NAME . '_nick'] = $group_row[0]['nick'];
        $_SESSION[$OJ_NAME . '_group_name'] = $group_name;
        // 存储学校信息到 session
        $_SESSION[$OJ_NAME . '_school_id'] = $school_id;
        $_SESSION[$OJ_NAME . '_school'] = $school;
    }
    if (empty($group_name)) {
        $sql = "SELECT * FROM `privilege` WHERE `user_id`=?";
        $_SESSION[$OJ_NAME . '_' . 'user_id'] = $login;
        $result = pdo_query($sql, $login);
    } else {  // 如果去掉下面的 and rightstr like 'c%' 则能获得该组的所有权限，如：在teacher组可以有teacher用户的所有权限。管理方便，但需谨慎使用。
        $sql = "SELECT * FROM `privilege` WHERE `user_id`=? or (user_id=? and rightstr like 'c%' )";
        $_SESSION[$OJ_NAME . '_' . 'user_id'] = $login;
        $result = pdo_query($sql, $login, $group_name);
    }
    // 对用户权限进行session转存
    foreach ($result as $row) {
        if (isset($row['valuestr']))
            $_SESSION[$OJ_NAME . '_' . $row['rightstr']] = $row['valuestr'];
        else
            $_SESSION[$OJ_NAME . '_' . $row['rightstr']] = true;
    }
    if (isset($_SESSION[$OJ_NAME . '_vip'])) {  // VIP mark can access all [VIP] marked contest vip权限用户可以参加所有标记了[VIP]字样的比赛
        $sql = "select contest_id from contest where title like '%[VIP]%'";
        $result = pdo_query($sql);
        foreach ($result as $row) {
            $_SESSION[$OJ_NAME . '_c' . $row['contest_id']] = true;
        }
    }

    $sql = "update users set accesstime=now() where user_id=?";
    $result = pdo_query($sql, $login);

    if ($OJ_LONG_LOGIN) {
        $C_info = pdo_query("SELECT `password` , `accesstime` FROM`users` WHERE`user_id`=? and defunct='N'", $login)[0];
        $C_len = strlen($C_info[1]);
        $C_res = "";
        for ($i = 0; $i < strlen($C_info[0]); $i++) {
            $tp = ord($C_info[0][$i]);
            $C_res .= chr(39 + ($tp * $tp + ord($C_info[1][$i % $C_len]) * $tp) % 88);
        }
        $C_res = sha1($C_res);
        $C_time = time() + 86400 * $OJ_KEEP_TIME;
        setcookie($OJ_NAME . "_user", $login, $C_time);
        setcookie($OJ_NAME . "_check", $C_res . (strlen($C_res) * strlen($C_res)) % 7, $C_time);
    }
    // 检测未领取新用户奖励（登录后补发场景）：发放6分并播种签到基线
    if (!empty($group_row) && $group_row[0]['defunct'] == 'N' && $group_row[0]['new_user_reward_claimed'] == 0) {
        // 仅当前积分为0时发放6分，避免重复发放
        $current_points = point_get_balance($login);
        $reward_granted = false;
        if ($current_points == 0) {
            $register_point_reward = 6;
            point_tx_begin();
            $point_result = point_apply_change(
                $login,
                $register_point_reward,
                POINT_LOG_TYPE_SYSTEM,
                null,
                '新用户注册奖励（登录补发）'
            );
            if ($point_result['success']) {
                // 播种签到基线（含 new_user_reward_claimed=1 + 3个签到字段），与6分发放同事务保证原子
                seed_login_reward($login);
                point_tx_commit();
                $reward_granted = true;
            } else {
                point_tx_rollback();
                error_log("Login: failed to grant points to user {$login}: " . $point_result['message']);
            }
        }
        // 无论6分是否发放，都标记已处理+播种签到基线，避免每次登录重复进入此分支并保证签到流程可用
        if (!$reward_granted) {
            seed_login_reward($login);
        }

        header("Location: welcome.php?status=activated");
        exit(0);
    }

    // 普通登录：发放每日连续登录奖励（仅进行中且当日未领时发2分；失败/异常不阻断登录）
    $streak_result = grant_login_streak_reward($login);

    echo "<script language='javascript'>\n";
    // 获取 redirect 参数，优先使用 POST，其次 GET
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : (isset($_GET['redirect']) ? $_GET['redirect'] : '');
    if ($redirect) {
        // 验证 redirect 参数，防止开放重定向（允许查询参数 ?=;&）
        if (strpos($redirect, '://') !== false || !preg_match('/^[\/a-zA-Z0-9._?=&-]+$/', $redirect)) {
            $redirect = '';
        }
    }

    // 若本日发放了连续登录奖励，先经 welcome 弹窗展示礼花效果，再由弹窗跳到目标页
    if (!empty($streak_result['granted'])) {
        $final_target = $redirect;
        if (!$final_target) {
            if (isset($_SESSION[$OJ_NAME . "_administrator"])) $final_target = 'admin';
            else if (isset($_SESSION[$OJ_NAME . "_contest_creator"])) $final_target = 'contest.php?my';
            else $final_target = 'index.php';
        }
        $redirect = 'welcome.php?status=streak&points=' . intval($streak_result['points'])
                  . '&redirect=' . rawurlencode($final_target);
    }

    if ($redirect) {
        // 使用 window.top 确保在顶层窗口跳转
        echo "window.top.location.href='" . htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') . "';\n";
    } else if (isset($_SESSION[$OJ_NAME . "_administrator"])) {
        echo "window.top.location.href='admin';\n";
    } else if (isset($_SESSION[$OJ_NAME . "_contest_creator"])) {
        echo "window.top.location.href='contest.php?my';\n";
    } else if ($OJ_NEED_LOGIN) {
        echo "window.top.location.href='index.php';\n";
    } else {
        echo "window.top.location.href='index.php';\n";
    }
    echo "</script>";
} else {
    // 登录失败暴力破解检测
    $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime("-5 minutes"));
    $failedStats = pdo_query("SELECT
                        (SELECT COUNT(1) FROM loginlog WHERE user_id=? AND password='login fail' AND time>=?) as user_fail,
                        (SELECT COUNT(1) FROM loginlog WHERE ip=? AND password='login fail' AND time>=?) as ip_fail;
                        ", $user_id, $fiveMinutesAgo, $ip, $fiveMinutesAgo);
    $userFailCount = intval($failedStats[0]['user_fail']);
    $ipFailCount = intval($failedStats[0]['ip_fail']);
    
    // 登录失败超过阈值（默认10次）发送飞书告警
    $failThreshold = isset($OJ_LOGIN_FAIL_ALERT_THRESHOLD) ? intval($OJ_LOGIN_FAIL_ALERT_THRESHOLD) : 10;
    if ($userFailCount >= $failThreshold || $ipFailCount >= $failThreshold * 4) {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
        feishu_notify(
            "🔴 登录失败暴力破解告警",
            "**告警类型**: 暴力破解检测\n" .
            "**用户ID**: $user_id\n" .
            "**攻击IP**: $ip\n" .
            "**5分钟内失败次数**：用户=$userFailCount，IP=$ipFailCount\n" .
            "**User Agent**: $ua\n" .
            "**检测时间**: " . date('Y-m-d H:i:s') . "\n" .
            "**建议**: 如非本人操作，可考虑封禁IP或强制用户修改密码",
            'warn'
        );
    }

    if (isset($OJ_LOG_ENABLED) && $OJ_LOG_ENABLED) {
        $params = json_encode($_REQUEST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $logger->info($params);
    }
    if ($view_errors) {
        require("template/" . $OJ_TEMPLATE . "/error.php");
    } else {
        echo "<script language='javascript'>\n";
        echo "alert('UserName or Password Wrong!');\n";
        echo "history.go(-1);\n";
        echo "</script>";
    }
}
$sql="INSERT INTO `loginlog`(user_id,password,ip,time) VALUES(?,'login ok',?,NOW())";
pdo_query($sql,$user_id,$ip);
