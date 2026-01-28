<?php 
// 设置页面标题
$page_title = "Flappy Bird 强化学习";
$iframe_src = "https://www.openinnolab.org.cn/qLearningFlappyBird?entrance=aiExp";
$show_title = "Flappy Bird - $OJ_NAME"; 
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<div class="padding">
    <h2 class="ui header">
        <i class="gamepad icon"></i>
        <div class="content">
            <?php echo $page_title; ?>
        </div>
    </h2>
    
    <div style="margin-bottom: 25px; max-width: 1200px; margin: 0 auto;">
        <div class="ui segment">
            <div style="width: 100%; height: 700px; overflow: hidden; position: relative;">
                <iframe id="flappyBirdFrame" src="<?php echo $iframe_src; ?>" allowfullscreen scrolling="yes" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
    
    <script>
        // 确保内嵌页面填充满整个iframe窗口
        function resizeIframe() {
            const iframe = document.getElementById('flappyBirdFrame');
            if (!iframe) return;
            
            const container = iframe.parentElement;
            
            // 设置iframe尺寸为容器尺寸
            iframe.style.width = `${container.offsetWidth}px`;
            iframe.style.height = `${container.offsetHeight}px`;
            
            // 重置所有变换
            iframe.style.transform = 'none';
            iframe.style.transformOrigin = 'top left';
        }
        
        // 页面加载完成后调整iframe大小
        window.addEventListener('load', resizeIframe);
        
        // 窗口大小变化时调整iframe大小
        window.addEventListener('resize', resizeIframe);
    </script>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>