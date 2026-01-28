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
} elseif ($type == 'gesture') {
    $page_title = "AI训练-手势识别";
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
    
    <?php if ($type == 'recognition' || $type == 'gesture') { ?>
    <div style="margin-bottom: 25px; max-width: 1000px; margin: 0 auto;">
        <div class="ui segment">
            <!-- 左右分栏布局 -->
            <div class="ui two column grid">
                <!-- 左边：图像识别区域 -->
                <div class="column">
                    <h3 class="ui header" style="color: #2185d0; text-align: center; margin-bottom: 10px;">
                        <?php echo $page_title; ?>
                    </h3>
                    <p style="text-align: center; color: #666; margin-bottom: 20px;">
                        <?php echo $type == 'gesture' ? '上传手势图片，智能识别手势类型和含义描述' : '上传图片，智能识别图片中的物体名称和详细描述'; ?>
                    </p>
                    
                    <!-- 拖拽上传区域 -->
                    <div id="dropZone" style="border: 2px dashed #ccc; border-radius: 8px; padding: 50px; text-align: center; cursor: pointer; margin-bottom: 20px;">
                        <div id="uploadIcon" style="font-size: 60px; color: #ccc; margin-bottom: 20px;">
                            📷
                        </div>
                        <p id="uploadText" style="color: #666; font-size: 16px;">
                            点击或拖拽图片到此处
                        </p>
                        <p style="color: #999; font-size: 14px; margin-top: 10px;">
                            <?php echo $type == 'gesture' ? '支持 JPG、PNG、BMP 格式，最短边像素不低于50px，文件大小不超过4MB' : '支持 JPG、PNG、BMP 格式，大小不超过8M，最短边像素不低于15px'; ?>
                        </p>
                        <input type="file" id="imageUpload" accept="image/*" style="display: none;">
                    </div>
                    
                    <!-- 文件名称显示已移除，改为在图片下方动态显示 -->
                    
                    <!-- 开始识别按钮 -->
                    <div style="text-align: center;">
                        <button class="ui large primary button" id="recognizeBtn" disabled>
                            <i class="search icon"></i>开始识别
                        </button>
                    </div>
                </div>
                
                <!-- 右边：识别结果区域 -->
                <div class="column">
                    <h3 class="ui header" style="color: #2185d0; text-align: center; margin-bottom: 10px;">
                        识别结果
                    </h3>
                    <p style="text-align: center; color: #666; margin-bottom: 20px;">
                        识别的内容将显示在下方区域
                    </p>
                    
                    <!-- 识别结果显示区域 -->
                    <div id="resultArea" style="min-height: 400px; border: 2px solid #eee; border-radius: 8px; padding: 40px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <div style="font-size: 60px; color: #ddd; margin-bottom: 20px;">
                            🖼️
                        </div>
                        <p style="color: #999; font-size: 16px;">
                            请上传图片开始识别
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- 加载状态 -->
            <div id="loadingArea" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.8); display: none; z-index: 9999;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                    <div class="ui active massive loader"></div>
                    <p style="margin-top: 20px; font-size: 18px; color: #666;">正在识别中，请稍候...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 全局变量
        let selectedFile = null;
        
        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('dropZone');
            const imageUpload = document.getElementById('imageUpload');
            const recognizeBtn = document.getElementById('recognizeBtn');
            
            // 点击上传区域触发文件选择
            dropZone.addEventListener('click', function() {
                imageUpload.click();
            });
            
            // 文件选择处理
            imageUpload.addEventListener('change', function(event) {
                handleFileSelect(event.target.files[0]);
            });
            
            // 拖拽上传功能
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropZone.style.borderColor = '#2185d0';
                dropZone.style.backgroundColor = 'rgba(33, 133, 208, 0.05)';
            });
            
            dropZone.addEventListener('dragleave', function() {
                resetDropZone();
            });
            
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                resetDropZone();
                if (e.dataTransfer.files.length > 0) {
                    handleFileSelect(e.dataTransfer.files[0]);
                }
            });
            
            // 识别按钮点击处理
            recognizeBtn.addEventListener('click', async function() {
                await recognizeImage();
            });
        });
        
        // 重置拖拽区域样式
        function resetDropZone() {
            const dropZone = document.getElementById('dropZone');
            dropZone.style.borderColor = '#ccc';
            dropZone.style.backgroundColor = 'transparent';
        }
        
        // 处理文件选择
        function handleFileSelect(file) {
            if (!file) return;
            
            // 验证文件类型
            const validTypes = ['image/jpeg', 'image/png', 'image/bmp'];
            if (!validTypes.includes(file.type)) {
                alert('请选择 JPG、PNG 或 BMP 格式的图片');
                return;
            }
            
            // 根据类型设置不同的验证规则
            const maxSize = '<?php echo $type == 'gesture' ? '4' : '8'; ?>';
            const minDimension = '<?php echo $type == 'gesture' ? '50' : '15'; ?>';
            
            // 验证文件大小
            if (file.size > maxSize * 1024 * 1024) { // 4MB for gesture, 8MB for others
                alert('图片大小不能超过 ' + maxSize + 'MB');
                return;
            }
            
            selectedFile = file;
            
            // 更新界面
            const uploadIcon = document.getElementById('uploadIcon');
            const uploadText = document.getElementById('uploadText');
            const recognizeBtn = document.getElementById('recognizeBtn');
            
            // 创建图片预览并压缩
            const reader = new FileReader();
            reader.onload = function(e) {
                // 创建图片对象
                const img = new Image();
                img.onload = function() {
                    // 显示已选择文件信息
                    uploadIcon.style.display = 'none';
                    uploadText.style.display = 'none';
                    
                    // 创建图片预览元素
                    let imgPreview = document.getElementById('imgPreview');
                    if (!imgPreview) {
                        imgPreview = document.createElement('img');
                        imgPreview.id = 'imgPreview';
                        imgPreview.style.maxWidth = '100%';
                        imgPreview.style.maxHeight = '250px';
                        imgPreview.style.borderRadius = '5px';
                        imgPreview.style.objectFit = 'contain';
                        dropZone.appendChild(imgPreview);
                    }
                    
                    // 压缩图片以减少内存占用
                    const canvas = document.createElement('canvas');
                    const maxDimension = 800; // 最大宽度或高度
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > height && width > maxDimension) {
                        height = Math.round(height * (maxDimension / width));
                        width = maxDimension;
                    } else if (height > maxDimension) {
                        width = Math.round(width * (maxDimension / height));
                        height = maxDimension;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    // 绘制压缩后的图片
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // 转换为base64格式并设置给预览图片
                    imgPreview.src = canvas.toDataURL('image/jpeg', 0.8);
                    imgPreview.style.display = 'block';
                    
                    // 创建或更新图片下方的文件名显示
                    let imgFileName = document.getElementById('imgFileName');
                    if (!imgFileName) {
                        imgFileName = document.createElement('p');
                        imgFileName.id = 'imgFileName';
                        imgFileName.style.color = '#666';
                        imgFileName.style.fontSize = '14px';
                        imgFileName.style.marginTop = '10px';
                        imgFileName.style.textAlign = 'center';
                        dropZone.appendChild(imgFileName);
                    }
                    
                    imgFileName.textContent = file.name;
                    imgFileName.style.display = 'block';
                    
                    recognizeBtn.disabled = false;
                };
                
                img.src = e.target.result;
            };
            
            reader.readAsDataURL(file);
        }
        
        // 识别图片
        async function recognizeImage() {
            if (!selectedFile) {
                alert('请先选择一张图片');
                return;
            }
            
            // 显示加载状态
            document.getElementById('loadingArea').style.display = 'block';
            
            try {
                const formData = new FormData();
                formData.append('file', selectedFile);
                formData.append('type', 'image');
                
                // 根据类型选择不同的API接口
                const apiUrl = '<?php echo $type == 'gesture' ? 'https://aitools.techsong.cn/api/gesture-recognition.php' : 'https://aitools.techsong.cn/api/image-recognition.php'; ?>';
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    body: formData,
                    mode: 'cors',
                    credentials: 'omit'
                });
                
                const result = await response.json();
                
                // 显示结果
                displayResult(result);
            } catch (error) {
                displayResult({
                    success: false,
                    message: error.message
                });
            } finally {
                // 隐藏加载状态
                document.getElementById('loadingArea').style.display = 'none';
            }
        }
        
        // 显示识别结果
        function displayResult(result) {
            const resultArea = document.getElementById('resultArea');
            
            if (result.success) {
                const recognitionResult = result.result;
                
                // 检查是否有错误信息（手势识别的特殊情况）
                if (recognitionResult['错误']) {
                    // 显示错误信息
                    resultArea.innerHTML = `
                        <div style="color: #db2828;">
                            <div style="font-size: 60px; margin-bottom: 20px;">⚠️</div>
                            <h4 style="margin-bottom: 10px;">识别结果</h4>
                            <p>${recognitionResult['错误']}</p>
                        </div>
                    `;
                    resultArea.style.borderColor = '#db2828';
                    resultArea.style.backgroundColor = 'rgba(219, 40, 40, 0.02)';
                    return;
                }
                
                let html = `
                    <div style="text-align: left; max-height: 400px; overflow-y: auto;">
                        <h4 style="color: #21ba45; margin-bottom: 20px;">识别成功！</h4>
                `;
                
                // 图像识别字段
                if (recognitionResult['物体名称']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>物体名称：</strong>
                        <span style="font-size: 20px; color: #2185d0;">${recognitionResult['物体名称']}</span>
                    </div>`;
                }
                
                // 手势识别字段
                if (recognitionResult['手势类型']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>手势类型：</strong>
                        <span style="font-size: 20px; color: #2185d0;">${recognitionResult['手势类型']}</span>
                    </div>`;
                }
                
                // 英文名称（手势识别）
                if (recognitionResult['英文名称']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>英文名称：</strong>
                        <span>${recognitionResult['英文名称']}</span>
                    </div>`;
                }
                
                // 置信度（通用）
                if (recognitionResult['置信度']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>置信度：</strong>
                        <span>${recognitionResult['置信度']}</span>
                    </div>`;
                }
                
                // 详细描述（图像识别）
                if (recognitionResult['详细描述']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>详细描述：</strong>
                        <p style="margin-top: 5px; line-height: 1.6;">${recognitionResult['详细描述']}</p>
                    </div>`;
                }
                
                // 手势含义（手势识别）
                if (recognitionResult['手势含义']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>手势含义：</strong>
                        <p style="margin-top: 5px; line-height: 1.6;">${recognitionResult['手势含义']}</p>
                    </div>`;
                }
                
                // 常见用途（手势识别）
                if (recognitionResult['常见用途']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>常见用途：</strong>
                        <p style="margin-top: 5px; line-height: 1.6;">${recognitionResult['常见用途']}</p>
                    </div>`;
                }
                
                // 百科链接（图像识别）
                if (recognitionResult['百科链接']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>百科链接：</strong>
                        <a href="${recognitionResult['百科链接']}" target="_blank" style="color: #2185d0;">${recognitionResult['百科链接']}</a>
                    </div>`;
                }
                
                // 其他可能（图像识别）
                if (recognitionResult['其他可能']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>其他可能：</strong>
                        <p style="margin-top: 5px;">${recognitionResult['其他可能']}</p>
                    </div>`;
                }
                
                // 识别时间（通用）
                if (recognitionResult['识别时间']) {
                    html += `<div style="margin-bottom: 15px;">
                        <strong>识别时间：</strong>
                        <span>${recognitionResult['识别时间']}</span>
                    </div>`;
                }
                
                html += '</div>';
                
                // 更新结果区域
                resultArea.innerHTML = html;
                resultArea.style.borderColor = '#21ba45';
                resultArea.style.backgroundColor = 'rgba(33, 186, 69, 0.02)';
            } else {
                // 显示错误信息
                resultArea.innerHTML = `
                    <div style="color: #db2828;">
                        <div style="font-size: 60px; margin-bottom: 20px;">❌</div>
                        <h4 style="margin-bottom: 10px;">识别失败</h4>
                        <p>错误信息：${result.message || '未知错误'}</p>
                    </div>
                `;
                resultArea.style.borderColor = '#db2828';
                resultArea.style.backgroundColor = 'rgba(219, 40, 40, 0.02)';
            }
        }
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