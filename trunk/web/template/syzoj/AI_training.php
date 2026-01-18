<?php 
// 根据参数确定显示内容
$type = isset($_GET['type']) ? $_GET['type'] : 'image';

// 设置页面标题
if ($type == 'handpose') {
    $page_title = "AI训练-手势分类";
    $iframe_src = "https://www.openinnolab.org.cn/handposeClassifier";
} elseif ($type == 'audio') {
    $page_title = "AI训练-语音分类";
    $iframe_src = "https://www.openinnolab.org.cn/audioSorter";
} elseif ($type == 'recognition') {
    $page_title = "AI训练-图像识别";
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
    
    <?php if ($type == 'recognition') { ?>
    <div style="margin-bottom: 25px; text-align: center; max-width: 800px; margin: 0 auto;">
        <div class="ui segment">
            <div class="ui form">
                <div class="field">
                    <label><i class="file image icon"></i>选择图片进行识别</label>
                    <div class="ui fluid action input">
                        <input type="text" id="fileName" placeholder="未选择文件" readonly>
                        <label for="imageUpload" class="ui button positive">
                            <i class="upload icon"></i>浏览
                        </label>
                        <input type="file" id="imageUpload" accept="image/*" style="display: none;">
                    </div>
                </div>
                
                <div class="field" style="margin-top: 20px;">
                    <button class="ui large primary button" id="recognizeBtn">
                        <i class="search icon"></i>开始识别
                    </button>
                </div>
            </div>
            
            <!-- 图片预览区域 -->
            <div id="imagePreview" style="margin-top: 20px; display: none;">
                <div class="ui message">
                    <h4 class="ui header">图片预览</h4>
                    <img id="previewImg" src="" alt="预览图片" style="max-width: 100%; max-height: 400px; border-radius: 5px;">
                </div>
            </div>
            
            <!-- 识别结果区域 -->
            <div id="resultArea" style="margin-top: 20px; display: none;">
                <div class="ui message">
                    <h4 class="ui header">识别结果</h4>
                    <div id="resultContent" style="font-size: 18px; line-height: 1.6;"></div>
                </div>
            </div>
            
            <!-- 加载状态 -->
            <div id="loadingArea" style="margin-top: 20px; display: none;">
                <div class="ui active inverted dimmer">
                    <div class="ui text loader">正在识别中，请稍候...</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 文件选择处理
        document.getElementById('imageUpload').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // 更新文件名显示
            document.getElementById('fileName').value = file.name;
            
            // 显示图片预览
            const previewImg = document.getElementById('previewImg');
            const imagePreview = document.getElementById('imagePreview');
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            
            reader.readAsDataURL(file);
        });
        
        // 识别按钮点击处理
        document.getElementById('recognizeBtn').addEventListener('click', async function() {
            const fileInput = document.getElementById('imageUpload');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('请先选择一张图片');
                return;
            }
            
            // 显示加载状态
            document.getElementById('loadingArea').style.display = 'block';
            document.getElementById('resultArea').style.display = 'none';
            
            try {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('type', 'image');
                
                const response = await fetch('https://aitools.techsong.cn/api/image-recognition.php', {
                    method: 'POST',
                    body: formData,
                    mode: 'cors',
                    credentials: 'omit'
                });
                
                const result = await response.json();
                
                // 显示结果
                const resultContent = document.getElementById('resultContent');
                const resultArea = document.getElementById('resultArea');
                
                if (result.success) {
                    resultArea.className = 'ui positive message';
                    const recognitionResult = result.result;
                    let html = '<p><strong>识别成功！</strong></p>';
                    html += '<div class="ui divided list">';
                    
                    // 物体名称
                    if (recognitionResult['物体名称']) {
                        html += `<div class="item">
                            <div class="header">物体名称</div>
                            <div class="description">${recognitionResult['物体名称']}</div>
                        </div>`;
                    }
                    
                    // 置信度
                    if (recognitionResult['置信度']) {
                        html += `<div class="item">
                            <div class="header">置信度</div>
                            <div class="description">${recognitionResult['置信度']}</div>
                        </div>`;
                    }
                    
                    // 详细描述
                    if (recognitionResult['详细描述']) {
                        html += `<div class="item">
                            <div class="header">详细描述</div>
                            <div class="description">${recognitionResult['详细描述']}</div>
                        </div>`;
                    }
                    
                    // 百科链接
                    if (recognitionResult['百科链接']) {
                        html += `<div class="item">
                            <div class="header">百科链接</div>
                            <div class="description"><a href="${recognitionResult['百科链接']}" target="_blank">${recognitionResult['百科链接']}</a></div>
                        </div>`;
                    }
                    
                    // 其他可能
                    if (recognitionResult['其他可能']) {
                        html += `<div class="item">
                            <div class="header">其他可能</div>
                            <div class="description">${recognitionResult['其他可能']}</div>
                        </div>`;
                    }
                    
                    // 识别时间
                    if (recognitionResult['识别时间']) {
                        html += `<div class="item">
                            <div class="header">识别时间</div>
                            <div class="description">${recognitionResult['识别时间']}</div>
                        </div>`;
                    }
                    
                    html += '</div>';
                    resultContent.innerHTML = html;
                } else {
                    resultArea.className = 'ui negative message';
                    resultContent.innerHTML = '<p><strong>识别失败</strong></p>' +
                                             '<p><strong>错误信息：</strong>' + result.message + '</p>';
                }
                
                resultArea.style.display = 'block';
            } catch (error) {
                const resultContent = document.getElementById('resultContent');
                const resultArea = document.getElementById('resultArea');
                resultArea.className = 'ui negative message';
                resultContent.innerHTML = '<p><strong>识别失败</strong></p>' +
                                         '<p><strong>错误信息：</strong>' + error.message + '</p>';
                resultArea.style.display = 'block';
            } finally {
                // 隐藏加载状态
                document.getElementById('loadingArea').style.display = 'none';
            }
        });
    </script>
<?php } else { ?>
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
<?php } ?>
    
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