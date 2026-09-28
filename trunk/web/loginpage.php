<?php
$cache_time = 1;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/memcache.php');
require_once('./include/setlang.php');
require_once('./include/bbcode.php');

require_once("./include/db_info.inc.php");
require_once("./include/setlang.php");
require_once("./include/my_func.inc.php");
$view_title = "LOGIN";

// 验证 redirect 参数：防开放重定向 + 过滤 session.php 等工具端点（自动登出常由保活请求触发，redirect 会被记成 session.php）
$redirect = sanitize_login_redirect(isset($_GET['redirect']) ? $_GET['redirect'] : '');

if (isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    // 已登录，跳转到指定页面或首页
    $target = $redirect ? $redirect : 'index.php';
    echo "<script>window.top.location.href='" . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . "';</script>";
    echo "<a href=logout.php>Please logout First!</a>";
    exit(1);
}

/////////////////////////Template
if ($OJ_LONG_LOGIN == true && isset($_COOKIE[$OJ_NAME . "_user"]) && isset($_COOKIE[$OJ_NAME . "_check"])) {
    $redirect_url = $redirect ? "&redirect=" . urlencode($redirect) : '';
    ?>
    <script>
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'login.php?<?php echo $redirect_url; ?>', true);
        xhr.send();
        setTimeout(function() {
            <?php if ($redirect) { ?>
                window.top.location.href = "<?php echo htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'); ?>";
            <?php } else { ?>
                window.top.location.href = 'index.php';
            <?php } ?>
        }, 1500);
    </script>
    <?php
} else {
    require("template/" . $OJ_TEMPLATE . "/loginpage.php");
}
/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>
