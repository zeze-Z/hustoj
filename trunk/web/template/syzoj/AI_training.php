<?php 
// 根据参数确定显示内容
$type = isset($_GET['type']) ? $_GET['type'] : 'image';

// 设置页面标题
if ($type == 'handpose') {
    $page_title = "AI训练-手势分类";
    $iframe_src = "https://www.openinnolab.org.cn/handposeClassifier";
} else {
    $page_title = "AI训练-图像分类";
    $iframe_src = "https://www.openinnolab.org.cn/imageSorter";
}

$show_title = "AI训练 - $OJ_NAME"; 
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<div class="padding">
    <h2 class="ui header">
        <i class="settings icon"></i>
        <div class="content">
            <?php echo $page_title; ?>
        </div>
    </h2>
    
    <div style="margin-bottom: 25px; text-align: center; width: 100%; height: calc(100vh - 200px); overflow: hidden;">
        <div class="ui segment" style="width: 100%; height: 100%; margin: 0; padding: 0; overflow: hidden; position: relative;">
            <iframe id="aiTrainingFrame" src="<?php echo $iframe_src; ?>" allowfullscreen scrolling="yes" style="width: 100%; height: 100%; border: none;"></iframe>
        </div>
    </div>

    <script>
        // 确保内嵌页面填充满整个iframe窗口
        function resizeIframe() {
            const iframe = document.getElementById('aiTrainingFrame');
            if (!iframe) return;
            
            const container = iframe.parentElement;
            
            // 设置iframe尺寸为容器尺寸
            iframe.style.width = `${container.offsetWidth}px`;
            iframe.style.height = `${container.offsetHeight}px`;
            
            // 重置所有变换
            iframe.style.transform = 'none';
            iframe.style.transformOrigin = 'top left';
            
            // 尝试让iframe内容自适应大小
            try {
                const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
                if (iframeDocument) {
                    // 设置iframe内容的视口元标签（如果存在）
                    const viewportMeta = iframeDocument.querySelector('meta[name="viewport"]');
                    if (viewportMeta) {
                        viewportMeta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
                    }
                    
                    // 设置body和html的样式
                    iframeDocument.body.style.margin = '0';
                    iframeDocument.body.style.padding = '0';
                    iframeDocument.body.style.height = '100%';
                    iframeDocument.body.style.overflow = 'hidden';
                    
                    iframeDocument.documentElement.style.margin = '0';
                    iframeDocument.documentElement.style.padding = '0';
                    iframeDocument.documentElement.style.height = '100%';
                    iframeDocument.documentElement.style.overflow = 'hidden';
                    
                    // 调整所有顶级元素的大小
                    const topElements = iframeDocument.body.children;
                    for (let i = 0; i < topElements.length; i++) {
                        const element = topElements[i];
                        element.style.maxWidth = '100%';
                        element.style.maxHeight = '100%';
                    }
                }
            } catch (e) {
                // 跨域访问可能会被阻止，此时忽略
            }
        }
        
        // 页面加载完成后调整
        window.addEventListener('load', () => {
            setTimeout(resizeIframe, 1000);
        });
        
        // 窗口大小改变时调整
        window.addEventListener('resize', resizeIframe);
        
        // iframe加载完成后调整
        document.getElementById('aiTrainingFrame').addEventListener('load', resizeIframe);
    </script>
    
    <div style="margin-bottom: 30px; ">
        <div style="text-align: center; ">
            <button class="ui mini button" onclick="window.history.back()">
                <i class="arrow left icon"></i>
                返回
            </button>
        </div>
    </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>