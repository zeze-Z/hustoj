<?php $show_title="课程表生成 - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
// 控制器传入登录态；游客可完整使用，登录后导出高清版
$tt_logged_in = !empty($view_logged_in);
$tt_json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
// 付费去码导出（1积分/次）：登录态取 session postkey + 控制器注入的余额；游客均为 null
$tt_postkey = ($tt_logged_in && isset($_SESSION[$OJ_NAME.'_'.'postkey'])) ? $_SESSION[$OJ_NAME.'_'.'postkey'] : null;
$tt_balance = $tt_logged_in && isset($view_tt_balance) ? intval($view_tt_balance) : null;
?>

<style>
/* ===== 课程表生成器页面样式（前缀 tt-）===== */
.tt-page {
    padding: 24px 0 48px;
    max-width: 1100px;
    margin: 0 auto;
}
.tt-hero {
    background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
    border-radius: 16px;
    padding: 26px 22px;
    text-align: center;
    color: #fff;
    margin-bottom: 24px;
}
.tt-hero h1 {
    color: #fff;
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0 0 8px 0;
}
.tt-hero p {
    color: rgba(255, 255, 255, 0.92);
    font-size: 0.98rem;
    margin: 0;
}
.tt-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}
.tt-card {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}
.tt-sec-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #333;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
/* 主题选择 */
.tt-theme-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
}
.tt-theme-item {
    border: 2.5px solid #eee;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    background: #fafafa;
    transition: border-color .2s, transform .2s;
    text-align: center;
    padding: 0 0 8px 0;
}
.tt-theme-item:hover { transform: translateY(-2px); }
.tt-theme-item.active { border-color: #f59e0b; }
.tt-theme-item img {
    width: 100%;
    display: block;
    aspect-ratio: 3 / 2;
    object-fit: cover;
}
.tt-theme-item .tt-theme-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #444;
    padding-top: 6px;
}
/* 横竖屏切换 */
.tt-orient-row {
    display: flex;
    gap: 10px;
    margin-bottom: 6px;
}
.tt-orient-btn {
    flex: 1;
    padding: 10px 0;
    border: 1.5px solid #e2e2e2;
    border-radius: 10px;
    background: #fff;
    font-size: 0.95rem;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all .2s;
}
.tt-orient-btn.active {
    border-color: #f59e0b;
    color: #b45309;
    background: #fffbeb;
}
/* 填写网格 */
.tt-grid-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.tt-grid {
    display: grid;
    grid-template-columns: 34px repeat(5, minmax(52px, 1fr));
    gap: 5px;
    min-width: 320px;
}
.tt-grid-head {
    font-size: 0.85rem;
    font-weight: 700;
    color: #92600a;
    text-align: center;
    padding: 4px 0;
}
.tt-row-label {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 700;
    color: #a17419;
    background: #fdf3df;
    border-radius: 8px;
}
.tt-group-label {
    grid-column: 1 / -1;
    font-size: 0.82rem;
    font-weight: 700;
    color: #3f7d4e;
    background: #eaf6ee;
    border-radius: 8px;
    padding: 4px 10px;
    margin-top: 4px;
}
.tt-cell {
    min-height: 44px;
    border: 1.5px dashed #e5d5ae;
    border-radius: 8px;
    background: #faf6ec;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
    color: #4a3b2a;
    text-align: center;
    padding: 2px;
    line-height: 1.25;
    word-break: break-all;
    transition: border-color .15s, background .15s;
}
.tt-cell:hover { border-color: #f0b429; background: #fdf8ea; }
.tt-cell.filled { border-style: solid; border-color: #ecd9a8; background: #fdf6e3; }
.tt-cell.filled,
.tt-chip,
#tt-editor-input {
    font-family: 'ZCOOL KuaiLe', "Yuanti SC", "幼圆", "YouYuan", "PingFang SC", "Microsoft YaHei", sans-serif;
}
.tt-cell .tt-plus { color: #d9c48a; font-weight: 400; }
/* 按钮区 */
.tt-btn-row {
    display: flex;
    gap: 10px;
    margin-top: 18px;
    flex-wrap: wrap;
}
.tt-btn {
    border: none;
    border-radius: 10px;
    padding: 12px 22px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.tt-btn-primary {
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff;
    flex: 1;
    justify-content: center;
}
.tt-btn-primary:hover { box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4); color: #fff; }
.tt-btn-ghost {
    background: #fff;
    color: #b45309;
    border: 1.5px solid #f0c36d;
}
.tt-btn-text {
    background: none;
    color: #999;
    font-size: 0.88rem;
    font-weight: 400;
    padding: 12px 8px;
}
.tt-btn-text:hover { color: #c0392b; }
/* 预览区 */
.tt-preview-col { min-width: 0; }
.tt-preview-card { position: static; }
.tt-preview-wrap {
    border-radius: 10px;
    overflow: hidden;
    background: #f4f4f4;
    text-align: center;
}
#tt-preview { width: 100%; display: block; }
.tt-preview-hint {
    font-size: 0.85rem;
    color: #999;
    margin-top: 10px;
    text-align: center;
}
.tt-login-nudge {
    margin-top: 14px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.9rem;
    color: #92600a;
    text-align: center;
}
.tt-login-nudge a { color: #b45309; font-weight: 700; }
/* 编辑底部面板 / 弹窗遮罩 */
.tt-mask {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 9999;
}
.tt-mask.show { display: block; }
.tt-sheet {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    background: #fff;
    border-radius: 18px 18px 0 0;
    padding: 18px 18px calc(18px + env(safe-area-inset-bottom));
    z-index: 10000;
    max-height: 75vh;
    overflow-y: auto;
    transform: translateY(100%);
    transition: transform .25s ease;
}
.tt-sheet.show { transform: translateY(0); }
.tt-sheet-title {
    font-size: 0.98rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.tt-sheet-close {
    border: none;
    background: none;
    font-size: 1.3rem;
    color: #999;
    cursor: pointer;
    line-height: 1;
    padding: 4px;
}
.tt-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 14px;
}
.tt-chip {
    border: 1.5px solid #ecd9a8;
    background: #fdf6e3;
    color: #4a3b2a;
    border-radius: 20px;
    padding: 7px 14px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
}
.tt-chip:hover { border-color: #f0b429; background: #fff8e6; }
.tt-editor-row {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}
.tt-editor-row input {
    flex: 1;
    border: 1.5px solid #e2e2e2;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.95rem;
    outline: none;
}
.tt-editor-row input:focus { border-color: #f0b429; }
.tt-editor-row button {
    border: none;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
}
#tt-editor-ok { background: #f59e0b; color: #fff; }
#tt-editor-clear { background: #f3f4f6; color: #666; }
/* 导出结果弹窗 */
.tt-modal {
    position: fixed;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%) scale(.95);
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    z-index: 10000;
    width: min(92vw, 560px);
    max-height: 86vh;
    overflow-y: auto;
    opacity: 0;
    pointer-events: none;
    transition: all .2s;
}
.tt-modal.show { opacity: 1; pointer-events: auto; transform: translate(-50%, -50%) scale(1); }
.tt-modal h3 {
    margin: 0 0 12px 0;
    font-size: 1.1rem;
    color: #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.tt-modal img {
    width: 100%;
    border-radius: 10px;
    border: 1px solid #eee;
    display: block;
}
.tt-modal-hint {
    font-size: 0.88rem;
    color: #888;
    margin: 12px 0 0 0;
    text-align: center;
}
.tt-modal-nudge {
    margin-top: 14px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 14px;
    text-align: center;
    font-size: 0.92rem;
    color: #92600a;
}
.tt-modal-nudge a,
.tt-modal-nudge button {
    display: inline-block;
    margin-top: 10px;
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 26px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
}
/* toast */
#tt-toast {
    position: fixed;
    left: 50%;
    bottom: 15vh;
    transform: translateX(-50%);
    background: rgba(30, 30, 30, 0.85);
    color: #fff;
    padding: 10px 20px;
    border-radius: 22px;
    font-size: 0.9rem;
    z-index: 10001;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s;
}
#tt-toast.show { opacity: 1; }
/* 桌面两栏 */
@media (min-width: 992px) {
    .tt-layout { grid-template-columns: 1.05fr 0.95fr; align-items: start; }
    .tt-preview-col { position: sticky; top: 80px; }
}
</style>

<div class="tt-page">
    <div class="tt-hero">
        <h1>📅 课程表生成器</h1>
        <p>挑主题、填课表，一键生成可打印课程表 —— 开学季教师专属福利</p>
    </div>

    <div class="tt-layout">
        <!-- 左：编辑区 -->
        <div class="tt-form-col">
            <div class="tt-card">
                <div class="tt-sec-title">🎨 选择主题</div>
                <div class="tt-theme-list" id="tt-theme-list"></div>
            </div>

            <div class="tt-card" style="margin-top: 16px;">
                <div class="tt-sec-title">🧭 纸张方向</div>
                <div class="tt-orient-row">
                    <button type="button" class="tt-orient-btn" id="tt-orient-h">🖥 横屏（A4 横放打印）</button>
                    <button type="button" class="tt-orient-btn" id="tt-orient-s">📱 竖屏（A4 竖放打印）</button>
                </div>
            </div>

            <div class="tt-card" style="margin-top: 16px;">
                <div class="tt-sec-title">✏️ 填写课表 <span style="font-weight:400;font-size:0.82rem;color:#999;">点击格子填写，自动保存草稿</span></div>
                <div class="tt-grid-scroll">
                    <div class="tt-grid" id="tt-grid"></div>
                </div>
                <div class="tt-btn-row">
                    <button type="button" class="tt-btn tt-btn-primary" id="tt-btn-export">🖼 生成并导出课程表</button>
                    <button type="button" class="tt-btn tt-btn-ghost" id="tt-btn-copy">🔗 复制链接</button>
                    <button type="button" class="tt-btn tt-btn-text" id="tt-btn-clear">清空课表</button>
                </div>
            </div>
        </div>

        <!-- 右：预览区 -->
        <div class="tt-preview-col">
            <div class="tt-card tt-preview-card">
                <div class="tt-sec-title">👁 实时预览</div>
                <div class="tt-preview-wrap"><canvas id="tt-preview"></canvas></div>
                <div class="tt-preview-hint">预览即最终效果 · 导出图底部居中自带二维码，转发给同事即可一起用</div>
                <?php if (!$tt_logged_in): ?>
                <div class="tt-login-nudge">💡 游客导出为<b>标清版</b>，<a href="loginpage.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">登录</a>后免费导出<b>高清打印版</b></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 单元格编辑面板 -->
<div class="tt-mask" id="tt-editor-mask"></div>
<div class="tt-sheet" id="tt-editor">
    <div class="tt-sheet-title">
        <span id="tt-editor-pos">周一 · 上午第1节</span>
        <button type="button" class="tt-sheet-close" id="tt-editor-close" aria-label="关闭">&times;</button>
    </div>
    <div class="tt-chips" id="tt-editor-chips"></div>
    <div class="tt-editor-row">
        <input type="text" id="tt-editor-input" maxlength="10" placeholder="自定义（如：3.2班、自习）">
        <button type="button" id="tt-editor-ok">填写</button>
        <button type="button" id="tt-editor-clear">清除</button>
    </div>
</div>

<!-- 导出结果弹窗 -->
<div class="tt-mask" id="tt-modal-mask"></div>
<div class="tt-modal" id="tt-modal">
    <h3><span>✅ 课程表已生成</span><button type="button" class="tt-sheet-close" id="tt-modal-close" aria-label="关闭">&times;</button></h3>
    <img id="tt-modal-img" alt="课程表导出结果">
    <p class="tt-modal-hint">手机端：长按上方图片保存到相册；电脑端：已自动下载到本地</p>
    <div class="tt-modal-nudge" id="tt-modal-qrfree" style="display:none;"></div>
    <div class="tt-modal-nudge" id="tt-modal-extra" style="display:none;"></div>
</div>

<div id="tt-toast"></div>

<script>
var TT_LOGGED_IN = <?php echo json_encode($tt_logged_in, $tt_json_flags); ?>;
var TT_SITE_NAME = <?php echo json_encode($OJ_NAME, $tt_json_flags); ?>;
var TT_POSTKEY = <?php echo json_encode($tt_postkey, $tt_json_flags); ?>;
var TT_BALANCE = <?php echo json_encode($tt_balance, $tt_json_flags); ?>;
</script>
<link rel="stylesheet" href="template/<?php echo $OJ_TEMPLATE?>/fonts/zcool-kuaile.css?v=1">
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qrcode.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/timetable.js?v=0.9"></script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
