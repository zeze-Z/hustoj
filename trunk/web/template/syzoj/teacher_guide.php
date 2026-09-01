<?php $show_title="学生账号开通指南 - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php $guide_qq = isset($OJ_CUSTOMER_QQ) && $OJ_CUSTOMER_QQ ? $OJ_CUSTOMER_QQ : "2326077585"; $guide_qq_h = htmlentities($guide_qq, ENT_QUOTES, 'UTF-8'); ?>

<style>
/* ===== 教师账号开通指南页面样式 ===== */
.guide-page {
    padding: 40px 0;
    max-width: 1000px;
    margin: 0 auto;
}

.guide-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 40px 30px;
    text-align: center;
    color: #fff;
    margin-bottom: 40px;
}

.guide-hero h1 {
    color: #fff;
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 10px 0;
}

.guide-hero p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.05rem;
    margin: 0;
}

.guide-section {
    margin-bottom: 40px;
}

.guide-section-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
}

/* 步骤卡片 */
.steps {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.step-card {
    background: #fff;
    border-radius: 12px;
    padding: 25px 22px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e8e8e8;
    position: relative;
}

.step-num {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
}

.step-card h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 10px 0;
}

.step-card p {
    font-size: 0.93rem;
    color: #666;
    line-height: 1.6;
    margin: 0 0 14px 0;
}

/* 按钮 */
.btn-qq {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-qq:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
    color: #fff;
    text-decoration: none;
}

.btn-outline {
    background: #fff;
    color: #667eea;
    border: 1.5px solid #667eea;
}

.btn-outline:hover {
    background: #EEF2FF;
    color: #667eea;
    text-decoration: none;
}

/* 模板说明 */
.template-box {
    background: #f8f9ff;
    border: 1px solid #e0e4f7;
    border-radius: 12px;
    padding: 20px;
    margin-top: 15px;
}

.template-box table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.template-box th, .template-box td {
    border: 1px solid #d6dbef;
    padding: 8px 12px;
    text-align: left;
    background: #fff;
}

.template-box th {
    background: #eef1ff;
    font-weight: 600;
    white-space: nowrap;
}

/* 补充通道 */
.alt-box {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #fefce8;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 18px 20px;
}

.alt-box .alt-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.alt-box .alt-text {
    font-size: 0.95rem;
    color: #555;
    line-height: 1.6;
}

/* FAQ */
.faq-item {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 12px;
}

.faq-item .faq-q {
    font-weight: 600;
    color: #333;
    margin-bottom: 6px;
}

.faq-item .faq-a {
    font-size: 0.93rem;
    color: #666;
    line-height: 1.6;
}

/* 底部CTA */
.guide-cta {
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 32px 20px;
    color: #fff;
}

.guide-cta h3 {
    color: #fff;
    margin: 0 0 8px 0;
}

.guide-cta p {
    color: rgba(255, 255, 255, 0.9);
    margin: 0 0 18px 0;
}

.guide-cta .btn-qq {
    background: #fff;
    color: #667eea;
    font-size: 1rem;
    padding: 10px 24px;
}

/* 响应式 */
@media (max-width: 768px) {
    .guide-page {
        padding: 20px 15px;
    }
    .guide-hero {
        padding: 30px 18px;
    }
    .guide-hero h1 {
        font-size: 1.5rem;
    }
    .steps {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="guide-page">
    <!-- 头部 -->
    <div class="guide-hero">
        <h1>👨‍🏫 学生账号开通指南</h1>
        <p>教师专属通道：联系客服 QQ，3 步批量开通全班学生账号</p>
    </div>

    <!-- 三步流程 -->
    <div class="guide-section">
        <h2 class="guide-section-title">📋 开通流程</h2>
        <div class="steps">
            <div class="step-card">
                <div class="step-num">1</div>
                <h3>打开QQ添加客服</h3>
                <p>打开 <strong>QQ 软件</strong>，在搜索框输入客服QQ号 <strong><?php echo $guide_qq_h;?></strong>，点击"添加"即可。</p>
                <button type="button" class="btn-qq" onclick="copyGuideQQ(event)"><i class="qq icon"></i>一键复制QQ号</button>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <h3>填写学生名单</h3>
                <p>按模板整理学生信息（<b>学号/姓名/学校/班级必填</b>），发给客服。密码可不填，默认"学号后 6 位"。</p>
                <a href="user_import_template.csv" download class="btn-qq"><i class="download icon"></i>下载名单模板</a>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <h3>收到账号并分发</h3>
                <p>客服开通完成后回传《账号清单》（含初始密码），您把账号发给学生即可登录使用。</p>
            </div>
        </div>

        <!-- 名单模板格式说明 -->
        <div class="template-box">
            <div style="font-weight: 600; margin-bottom: 10px;">📄 名单模板字段说明（<a href="user_import_template.csv" download>下载模板</a>）</div>
            <table>
                <tr><th>学号</th><th>姓名</th><th>密码</th><th>学校</th><th>邮箱</th><th>班级</th><th>有效期</th></tr>
                <tr><td>必填，登录账号</td><td>必填，昵称</td><td>可留空，默认学号后6位</td><td>必填</td><td>选填</td><td>必填，如 3年级1班</td><td>选填，留空默认按平台规则</td></tr>
            </table>
            <div style="margin-top: 10px; font-size: 0.88rem; color: #888;">请按上方字段整理学生名单（<b>第一行保留字段名</b>），Excel / CSV 均可，发给客服即可。学号、姓名属于个人信息，请通过 QQ 私聊发送，勿公开发到群聊或评论区。</div>
        </div>
    </div>

    <!-- 补充通道 -->
    <div class="guide-section">
        <h2 class="guide-section-title">🔁 补充方式：学生自行注册</h2>
        <div class="alt-box">
            <div class="alt-icon">👨‍🎓</div>
            <div class="alt-text">
                学生也可在平台自行注册使用，您只需把平台地址发给学生即可。
                <a href="registerpage.php" style="font-weight: 600;">前往注册页 →</a>
            </div>
        </div>
    </div>

    <!-- 常见问题 -->
    <div class="guide-section">
        <h2 class="guide-section-title">❓ 常见问题</h2>
        <div class="faq-item">
            <div class="faq-q">Q1：初始密码是什么？</div>
            <div class="faq-a">批量开通的账号默认初始密码为<b>学号后 6 位</b>（学号不足 6 位则为整个学号）。老师可在名单中为个别学生单独指定密码。</div>
        </div>
        <div class="faq-item">
            <div class="faq-q">Q2：学生忘记密码怎么办？</div>
            <div class="faq-a">请联系客服 QQ（<?php echo $guide_qq_h;?>）重置，或由平台统一处理。</div>
        </div>
        <div class="faq-item">
            <div class="faq-q">Q3：学生账号有有效期限制吗？</div>
            <div class="faq-a">平台支持账号有效期设置，客服开通时可统一设定，到期后可联系续期。</div>
        </div>
        <div class="faq-item">
            <div class="faq-q">Q4：账号开通需要多久？</div>
            <div class="faq-a">名单提交客服后，一般 <b>24 小时内</b>完成开通并回传账号清单。</div>
        </div>
    </div>

    <!-- 底部CTA -->
    <div class="guide-cta">
        <h3>准备好了？打开 QQ 软件，搜索客服QQ添加</h3>
        <p>客服QQ：<?php echo $guide_qq_h;?> ｜ 工作日在线</p>
        <button type="button" class="btn-qq" style="background: #fff; color: #667eea; border: 1.5px solid #fff;" onclick="copyGuideQQ(event)"><i class="qq icon"></i>一键复制QQ号</button>
    </div>
</div>

<script>
function copyGuideQQ(e) {
    if (e) e.preventDefault();
    var qq = "<?php echo $guide_qq_h;?>";
    var done = function () { alert("客服QQ已复制：" + qq); };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(qq).then(done).catch(function () { guideFallbackCopy(qq, done); });
    } else {
        guideFallbackCopy(qq, done);
    }
}
function guideFallbackCopy(text, done) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); } catch (err) {}
    document.body.removeChild(ta);
    done();
}
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>