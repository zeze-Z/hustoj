<?php
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
require_once("./include/const.inc.php");
require_once("./include/my_func.inc.php");
require_once("./include/login_reward.php");

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$reason = isset($_GET['reason']) ? trim($_GET['reason']) : '';

$reward_points = 6; // 注册/激活奖励积分
$cur_user_id = isset($_SESSION[$OJ_NAME.'_'.'user_id']) ? $_SESSION[$OJ_NAME.'_'.'user_id'] : '';
$streak_info = get_login_reward_info($cur_user_id);

// 弹窗参数：activated=新手6分；streak=连续登录2分
$show_modal = false;
$modal_points = 0;
$modal_title  = '';
$modal_btn    = '太棒了，去看看';
$modal_onclose_js = ''; // 弹窗关闭后执行的 JS

if ($status === 'activated') {
    $show_modal = true;
    $modal_points = $reward_points;
    $modal_title  = '恭喜获得新手积分奖励';
    $modal_onclose_js = "$('#welcome-content').fadeIn(300);"; // 关闭后展示新手福利内容
} elseif ($status === 'streak') {
    $show_modal = true;
    $modal_points = isset($_GET['points']) ? max(1, intval($_GET['points'])) : 2;
    $modal_title  = '连续登录奖励';
    $modal_btn    = '继续';
    // 关闭后跳转到 redirect（校验防开放重定向）或首页
    $sr = isset($_GET['redirect']) ? trim($_GET['redirect']) : '';
    if ($sr !== '' && (strpos($sr, '://') !== false || !preg_match('#^[\/a-zA-Z0-9._?=&-]+$#', $sr))) {
        $sr = '';
    }
    $streak_target = $sr !== '' ? $sr : 'index.php';
    $modal_onclose_js = "window.top.location.href='" . htmlspecialchars($streak_target, ENT_QUOTES, 'UTF-8') . "';";
}

$show_title = "欢迎加入 - $OJ_NAME";
include("template/$OJ_TEMPLATE/header.php");
?>

<?php if ($status === 'activated'): ?>
<!-- ========== 新手福利内容（激活弹窗关闭后显示） ========== -->
<div id="welcome-content" style="display: none;">
    <!-- Hero区域 -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 50px 40px; text-align: center; margin-bottom: 40px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); animation: pulse 4s ease-in-out infinite;"></div>
        <div style="font-size: 64px; margin-bottom: 20px; position: relative;">🎓</div>
        <h1 style="color: #fff; font-size: 32px; margin: 0 0 12px; font-weight: 700; position: relative;">欢迎加入 <?php echo $OJ_NAME; ?></h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 18px; margin: 0; position: relative;">您的编程教学之旅，从这里启航</p>
    </div>

    <!-- 签到进度卡 -->
    <?php echo login_reward_streak_card_html($streak_info, $reward_points); ?>

    <!-- 功能入口 -->
    <div style="max-width: 900px; margin: 0 auto;">
        <h2 style="text-align: center; font-size: 24px; color: #333; margin-bottom: 30px; font-weight: 600;">
            <span style="display: inline-block; border-bottom: 3px solid #667eea; padding-bottom: 8px;">探索平台功能</span>
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
            <!-- 课件中心 -->
            <a href="course.php" style="text-decoration: none; color: inherit;">
                <div style="background: #fff; border-radius: 16px; padding: 32px 24px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer; border: 1px solid #f0f0f0;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 40px rgba(102,126,234,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'">
                    <div style="font-size: 48px; margin-bottom: 16px;">📚</div>
                    <div style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 8px;">课件中心</div>
                    <div style="font-size: 14px; color: #888; line-height: 1.6;">海量精品课件<br>即点即学</div>
                </div>
            </a>
            <!-- 积分中心 -->
            <a href="point_index.php" style="text-decoration: none; color: inherit;">
                <div style="background: #fff; border-radius: 16px; padding: 32px 24px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer; border: 1px solid #f0f0f0;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 40px rgba(245,166,35,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'">
                    <div style="font-size: 48px; margin-bottom: 16px;">💎</div>
                    <div style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 8px;">积分中心</div>
                    <div style="font-size: 14px; color: #888; line-height: 1.6;">查看积分<br>兑换课程</div>
                </div>
            </a>
            <!-- 趣味游戏 -->
            <a href="more.php" style="text-decoration: none; color: inherit;">
                <div style="background: #fff; border-radius: 16px; padding: 32px 24px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer; border: 1px solid #f0f0f0;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 40px rgba(16,185,129,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'">
                    <div style="font-size: 48px; margin-bottom: 16px;">🎮</div>
                    <div style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 8px;">趣味游戏</div>
                    <div style="font-size: 14px; color: #888; line-height: 1.6;">边玩边学<br>轻松入门</div>
                </div>
            </a>
            <!-- 竞赛真题 -->
            <a href="contest.php?my" style="text-decoration: none; color: inherit;">
                <div style="background: #fff; border-radius: 16px; padding: 32px 24px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer; border: 1px solid #f0f0f0;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 40px rgba(59,130,246,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'">
                    <div style="font-size: 48px; margin-bottom: 16px;">🏆</div>
                    <div style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 8px;">竞赛真题</div>
                    <div style="font-size: 14px; color: #888; line-height: 1.6;">实战演练<br>提升技能</div>
                </div>
            </a>
        </div>
        <!-- 底部按钮 -->
        <div style="text-align: center; margin-top: 50px;">
            <a href="index.php" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 14px 48px; border-radius: 8px; font-size: 16px; font-weight: 600; text-decoration: none; box-shadow: 0 4px 15px rgba(102,126,234,0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102,126,234,0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102,126,234,0.4)'">
                开始探索 ->
            </a>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
    </style>
</div>
<?php endif; ?>

<?php if ($show_modal): ?>
<!-- ========== 积分发放弹窗（注册6分 / 连续登录2分 共用，礼花效果） ========== -->
<!-- 半透明遮罩层 -->
<div id="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; backdrop-filter: blur(3px);"></div>

<!-- 积分动画弹窗 -->
<div id="points-modal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; background: rgba(255,255,255,0.92); border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; min-width: 380px; max-width: 90vw; backdrop-filter: blur(10px);">
    <div style="background: linear-gradient(135deg, rgba(102,126,234,0.9) 0%, rgba(118,75,162,0.9) 100%); padding: 30px; text-align: center;">
        <div style="font-size: 48px; margin-bottom: 10px;">🎉</div>
        <div style="font-size: 20px; color: #fff; font-weight: 600;"><?php echo htmlspecialchars($modal_title); ?></div>
    </div>
    <div style="text-align: center; padding: 40px 30px;">
        <div style="position: relative; display: inline-block;">
            <span style="font-size: 14px; color: #999; vertical-align: top;">+</span>
            <span id="points-display" style="font-size: 80px; font-weight: bold; color: #f5a623; line-height: 1; text-shadow: 0 2px 4px rgba(245,166,35,0.3);">0</span>
            <span style="font-size: 24px; color: #666; vertical-align: bottom; margin-left: 4px;">积分</span>
        </div>
        <div id="points-message" style="font-size: 16px; margin-top: 15px; color: #666;">积分正在发放中...</div>
    </div>
    <div style="text-align: center; padding: 0 30px 30px;">
        <button id="confirm-btn" class="ui primary button" style="display: none; padding: 12px 60px; font-size: 16px; border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; cursor: pointer;"><?php echo htmlspecialchars($modal_btn); ?></button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE; ?>/game_confetti.js"></script>
<script>
// 积分动画（匀速递增，每50ms +1，共1秒）
function animatePoints() {
    var current = 0;
    var target = <?php echo intval($modal_points); ?>;
    var interval = 50; // 每50ms递增一次
    var display = document.getElementById('points-display');
    var message = document.getElementById('points-message');
    var btn = document.getElementById('confirm-btn');

    var timer = setInterval(function() {
        current++;
        display.textContent = current;
        if (current >= target) {
            clearInterval(timer);
            message.textContent = '您已获得 ' + target + ' 积分奖励！';
            btn.style.display = 'inline-block';
        }
    }, interval);
}

// 页面加载后启动动画
$(document).ready(function() {
    // 显示弹窗和遮罩
    $('#modal-overlay').fadeIn(200);
    $('#points-modal').fadeIn(300);

    // 延迟启动积分动画和礼花
    setTimeout(function() {
        animatePoints();
        // 礼花置于顶层（z-index: 10000）
        if (typeof confetti === 'function') {
            var duration = 1000;
            var end = Date.now() + duration;
            (function frame() {
                confetti({
                    particleCount: 5,
                    spread: 60,
                    origin: { y: 0.6 },
                    colors: ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
                    disableForReducedMotion: true,
                    zIndex: 10000
                });
                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            }());
        }
    }, 300);

    // 确认按钮点击事件
    $('#confirm-btn').click(function() {
        $('#points-modal').fadeOut(300);
        $('#modal-overlay').fadeOut(300, function() {
            <?php echo $modal_onclose_js; ?>
        });
    });

    // 点击遮罩层也可关闭
    $('#modal-overlay').click(function() {
        $('#points-modal').fadeOut(300);
        $('#modal-overlay').fadeOut(300, function() {
            <?php echo $modal_onclose_js; ?>
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
