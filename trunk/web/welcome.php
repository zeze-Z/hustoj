<?php
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
require_once("./include/const.inc.php");
require_once("./include/my_func.inc.php");

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$reason = isset($_GET['reason']) ? trim($_GET['reason']) : '';

$show_title = "欢迎加入 - $OJ_NAME";
include("template/$OJ_TEMPLATE/header.php");
?>

<?php if ($status === 'activated'): ?>
<!-- ========== 激活成功 ========== -->
<div class="padding">
    <div class="ui success message">
        <div class="header">🎉 账号激活成功！</div>
        <p>欢迎加入 <?php echo $OJ_NAME; ?>，您的编程教学之旅即将开始。</p>
    </div>

    <!-- 积分动画弹窗 -->
    <div id="points-modal" class="ui modal" style="display: block; position: relative; margin-top: 30px; border-radius: .28571429rem; box-shadow: 0 1px 3px 0 #d4d4d5, 0 0 0 1px #d4d4d5; background: #fff; overflow: hidden;">
        <div class="header" style="padding: 1.25rem 1.5rem; font-size: 1.28571429em; font-weight: 700; border-bottom: 1px solid rgba(34,36,38,.15);">积分奖励</div>
        <div class="content" style="text-align: center; padding: 40px;">
            <div id="points-display" style="font-size: 72px; font-weight: bold; color: #21ba45;">0</div>
            <div id="points-message" style="font-size: 18px; margin-top: 20px;">积分正在发放中...</div>
        </div>
        <div class="actions" style="text-align: center; padding: 20px; border-top: 1px solid rgba(34,36,38,.15);">
            <button id="confirm-btn" class="ui primary button" style="display: none;">确认</button>
        </div>
    </div>

    <!-- 新手福利内容（弹窗关闭后显示） -->
    <div id="welcome-content" style="display: none;">
        <h2>新手福利</h2>
        <div class="ui four cards">
            <div class="card">
                <div class="content">
                    <div class="header">📚 免费浏览课件</div>
                    <div class="description">海量教学课件，即点即看</div>
                </div>
                <div class="extra content">
                    <a href="course.php" class="ui button">去浏览</a>
                </div>
            </div>
            <div class="card">
                <div class="content">
                    <div class="header">💎 积分兑换课程</div>
                    <div class="description">使用积分兑换精品课程</div>
                </div>
                <div class="extra content">
                    <a href="course.php" class="ui button">去兑换</a>
                </div>
            </div>
            <div class="card">
                <div class="content">
                    <div class="header">🎮 趣味编程游戏</div>
                    <div class="description">边玩边学，轻松入门</div>
                </div>
                <div class="extra content">
                    <a href="puzzle_game.php" class="ui button">去玩游戏</a>
                </div>
            </div>
            <div class="card">
                <div class="content">
                    <div class="header">👨‍🏫 创建班级</div>
                    <div class="description">管理学生，布置作业</div>
                </div>
                <div class="extra content">
                    <a href="class.php" class="ui button">去创建</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 积分动画
function animatePoints() {
    var current = 0;
    var target = 20;
    var duration = 1500; // 1.5秒
    var startTime = Date.now();

    function update() {
        var elapsed = Date.now() - startTime;
        var progress = Math.min(elapsed / duration, 1);
        current = Math.floor(progress * target);
        document.getElementById('points-display').textContent = current;

        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            document.getElementById('points-display').textContent = target;
            document.getElementById('points-message').textContent = '您已获得 20 积分奖励！';
            document.getElementById('confirm-btn').style.display = 'inline-block';
        }
    }
    update();
}

// 页面加载后启动动画
$(document).ready(function() {
    animatePoints();

    // 确认按钮点击事件
    $('#confirm-btn').click(function() {
        $('#points-modal').fadeOut(300, function() {
            $('#welcome-content').fadeIn(300);
        });
    });
});
</script>

<?php elseif ($status === 'failed'): ?>
<!-- ========== 激活失败 ========== -->
<div class="padding">
    <div class="ui error message">
        <div class="header">❌ 激活失败</div>
        <p>激活链接已过期或无效，请重新注册或联系客服。</p>
    </div>
    <div style="text-align: center; margin-top: 30px;">
        <a href="registerpage.php" class="ui primary button">重新注册</a>
        <a href="loginpage.php" class="ui button">去登录</a>
    </div>
</div>

<?php else: ?>
<!-- ========== 其他情况（直接访问 welcome.php） ========== -->
<div class="padding">
    <?php if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
        <!-- 已登录 -->
        <div class="ui info message">
            <div class="header">欢迎回来，<?php echo htmlspecialchars($_SESSION[$OJ_NAME.'_'.'nick']); ?>！</div>
            <p>您当前积分：<?php echo point_get_balance($_SESSION[$OJ_NAME.'_'.'user_id']); ?></p>
        </div>
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="ui primary button">进入首页</a>
            <a href="course.php" class="ui button">浏览课件</a>
        </div>
    <?php else: ?>
        <!-- 未登录 -->
        <div class="ui warning message">
            <div class="header">请先登录</div>
            <p>登录后即可查看您的新手福利。</p>
        </div>
        <div style="text-align: center; margin-top: 30px;">
            <a href="loginpage.php" class="ui primary button">去登录</a>
            <a href="registerpage.php" class="ui button">去注册</a>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
