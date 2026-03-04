<?php
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
require_once("./include/const.inc.php");
require_once("./include/my_func.inc.php");

/**
 * 用户账户激活功能
 * 通过激活码激活被禁用的用户账户
 *
 * 该脚本接收GET参数中的激活码，验证后将用户状态从禁用(Y)更新为启用(N)
 * 并清空激活码字段，完成账户激活流程
 */
$code = trim($_GET['code']);

// 检查是否开启邮件确认功能且激活码不为空
if (isset($OJ_EMAIL_CONFIRM)) {
    if ($OJ_EMAIL_CONFIRM && strlen($code) == 18 ) {
        // 更新用户表，将指定激活码的禁用账户重新激活
        $sql = "update `users` set defunct='N',activecode=''  WHERE `activecode`=? and `activecode`!='' and defunct='Y' ";
        $result = pdo_query($sql, $code);
    }
}

// 检查激活是否成功
if (isset($result) && $result > 0) {
    // 激活成功，显示弹窗提示
    print "<script language='javascript'>\n";
    print "alert('账号激活成功，请登录');\n";
    print "window.location.href='loginpage.php';\n";
    print "</script>";
} else {
    // 激活失败或已激活，直接跳转
    header("location:loginpage.php");
}
?>
