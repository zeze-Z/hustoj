<?php require("header.php");

// 离线游戏兑换：获取用户登录状态和积分余额
$og_logged_in = isset($_SESSION[$OJ_NAME . '_' . 'user_id']);
$og_user_id = $og_logged_in ? $_SESSION[$OJ_NAME . '_' . 'user_id'] : '';
$og_balance = $og_logged_in ? intval(point_get_balance($og_user_id)) : 0;
$og_price = 50;

// 检查是否已有未过期订单
$og_has_order = false;
$og_order_expire = '';
$og_order_no = '';
$og_order_school = '';
$og_order_room = '';
$og_license_code = '';
if ($og_logged_in) {
    $og_rows = pdo_query(
        "SELECT order_no, expire_date, school_name, room_name, license_code FROM `offline_game_order`
          WHERE user_id = ? AND expire_date >= CURDATE()
          ORDER BY id DESC LIMIT 1",
        $og_user_id
    );
    if (!empty($og_rows)) {
        $og_has_order = true;
        $og_order_expire = $og_rows[0]['expire_date'];
        $og_order_no = $og_rows[0]['order_no'];
        $og_order_school = $og_rows[0]['school_name'];
        $og_order_room = $og_rows[0]['room_name'];
        $og_license_code = $og_rows[0]['license_code'];
    }
}

$og_download_url = 'https://pan.baidu.com/s/1Q0SuVxrL84WWOVeIbFztnA?pwd=hmkm';

// 生成postkey（供弹窗表单使用）
$og_postkey = '';
if (isset($_SESSION[$OJ_NAME.'_'.'postkey'])) {
    $og_postkey = $_SESSION[$OJ_NAME.'_'.'postkey'];
}
?>

<style>
/* 更多功能页面样式 */
.more-page {
    padding: 20px 0 40px;
    max-width: 1200px;
    margin: 0 auto;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
    text-align: center;
}

.page-subtitle {
    font-size: 1.1rem;
    color: #666;
    text-align: center;
    margin-bottom: 25px;
}

/* 一级 Tab 导航 */
.tabs {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 6px;
    border-bottom: 2px solid #e8e8e8;
    margin-bottom: 30px;
    padding: 0 10px;
}

.tab-item {
    padding: 12px 24px;
    cursor: pointer;
    color: #666;
    font-size: 1.05rem;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    white-space: nowrap;
    user-select: none;
    margin-bottom: -2px;
}

.tab-item:hover {
    color: #667eea;
}

.tab-item.active {
    color: #667eea;
    font-weight: 600;
    border-bottom-color: #667eea;
}

/* Tab 面板 */
.tab-panel {
    display: none;
}

.tab-panel.active {
    display: block;
}

/* 二级 Tab（小游戏内部分类） */
.sub-tabs {
    display: flex;
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.sub-tab-item {
    padding: 8px 20px;
    cursor: pointer;
    color: #666;
    font-size: 0.95rem;
    font-weight: 500;
    border-radius: 20px;
    background: #f3f4f6;
    transition: all 0.2s ease;
    white-space: nowrap;
    user-select: none;
}

.sub-tab-item:hover {
    color: #667eea;
    background: #ede9fe;
}

.sub-tab-item.active {
    color: #fff;
    background: linear-gradient(135deg, #667eea, #764ba2);
    font-weight: 600;
}

.sub-panel {
    display: none;
}

.sub-panel.active {
    display: block;
}

.section {
    margin-bottom: 50px;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
    display: flex;
    align-items: center;
    gap: 10px;
}

.auth-tag {
    font-size: 0.85rem;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: normal;
}

.tag-public {
    background-color: #dcfce7;
    color: #166534;
}

.tag-private {
    background-color: #fef3c7;
    color: #92400e;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.card {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid #e8e8e8;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
    border-color: #667eea;
    text-decoration: none;
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.card-icon svg {
    width: 34px;
    height: 34px;
    display: block;
}

.card-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.card-desc {
    font-size: 0.95rem;
    color: #666;
    line-height: 1.5;
}

/* ===== 小游戏 Tab 布局 ===== */
/* 必须用 .tab-panel.active#panel-games 提升 specificity，
   否则 #panel-games 的 display:flex 会覆盖 .tab-panel { display:none }，
   导致 games 面板永远无法隐藏、其他 tab 看起来"打不开" */
.tab-panel.active#panel-games {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* 二级Tab */
.tab-panel.active#panel-games > .og-subtabs-row {
    margin-bottom: 0;
}

/* ===== 离线游戏横幅（顶部全宽卡片） ===== */
.og-banner-top {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 45%, #f093fb 100%);
    border-radius: 14px;
    padding: 14px 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(102, 126, 234, 0.35);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 20px;
    animation: og-banner-pulse 3s ease-in-out infinite;
}

@keyframes og-banner-pulse {
    0%, 100% { box-shadow: 0 8px 28px rgba(102, 126, 234, 0.35); }
    50% { box-shadow: 0 8px 36px rgba(102, 126, 234, 0.5); }
}

.og-banner-top:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 36px rgba(102, 126, 234, 0.5);
}

.og-banner-top::before {
    content: '';
    position: absolute;
    top: -60px; right: -40px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 65%);
    border-radius: 50%;
    pointer-events: none;
}

.og-banner-top::after {
    content: '';
    position: absolute;
    bottom: -40px; left: 30%;
    width: 150px; height: 150px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 65%);
    border-radius: 50%;
    pointer-events: none;
}

.og-banner-top-badge {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: #78350f;
    font-size: 1rem;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 5px;
    letter-spacing: 0.5px;
    white-space: nowrap;
    margin-right: 6px;
}

@keyframes og-badge-bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.og-banner-top-icon {
    width: 50px; height: 50px;
    min-width: 50px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    animation: og-icon-float 3s ease-in-out infinite;
}

@keyframes og-icon-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.og-banner-top-icon svg {
    width: 28px; height: 28px;
    fill: #fff;
}

.og-banner-top-close {
    position: absolute;
    top: 6px; right: 8px;
    width: 20px; height: 20px;
    background: rgba(255,255,255,0.25);
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
    z-index: 10;
    line-height: 1;
}

.og-banner-top-close:hover {
    background: rgba(255,255,255,0.4);
    transform: scale(1.1);
}

.og-banner-top-content {
    flex: 1;
    min-width: 0;
    position: relative;
    z-index: 1;
}

.og-banner-top-title {
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: 6px;
    text-shadow: 0 1px 4px rgba(0,0,0,0.12);
}

.og-banner-top-desc {
    font-size: 0.85rem;
    opacity: 0.9;
    line-height: 1.4;
    margin-bottom: 0;
}

.og-banner-top-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.og-banner-top-tags-inline {
    display: inline-flex;
    gap: 6px;
    margin-left: 10px;
    vertical-align: middle;
}

.og-banner-top-tag {
    font-size: 0.65rem;
    padding: 2px 8px;
    border-radius: 12px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    white-space: nowrap;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.og-banner-top-action {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    z-index: 1;
}

.og-banner-top-price {
    text-align: right;
    line-height: 1.1;
    display: flex;
    align-items: center;
    gap: 6px;
}

.og-banner-top-price-old {
    font-size: 0.85rem;
    text-decoration: line-through;
    opacity: 0.7;
    font-weight: 500;
}

.og-banner-top-price-num {
    font-size: 1.8rem;
    font-weight: 800;
    text-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.og-banner-top-price-unit {
    font-size: 0.7rem;
    opacity: 0.85;
}

.og-banner-top-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    color: #667eea;
    padding: 12px 24px;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all 0.25s;
    box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    white-space: nowrap;
    text-decoration: none;
}

.og-banner-top-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.og-banner-top-btn-success {
    background: rgba(255,255,255,0.22);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.4);
}

/* 横幅移动端适配 */
@media (max-width: 768px) {
    .og-banner-top {
        flex-direction: column;
        text-align: center;
        padding: 24px 20px;
        gap: 16px;
    }

    .og-banner-top-tags {
        justify-content: center;
    }

    .og-banner-top-badge {
        position: relative;
        top: auto;
        right: auto;
        display: inline-block;
        margin-bottom: 8px;
    }
}

/* ===== 离线游戏兑换弹窗 ===== */
/* 遮罩：毛玻璃 + 淡入 */
.og-modal-mask {
    display: none;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 99999 !important;
    padding: 20px;
    box-sizing: border-box !important;
    animation: og-fade-in 0.25s ease;
    margin: 0 !important;
}

@keyframes og-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

.og-modal-mask.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-direction: column !important;
}

/* 弹窗主体：flex 居中 + 弹性弹入 */
.og-modal {
    background: #fff;
    border-radius: 20px;
    max-width: 480px !important;
    width: 100% !important;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    box-shadow: 0 24px 64px rgba(15, 23, 42, 0.35);
    position: relative !important;
    top: auto !important;
    left: auto !important;
    right: auto !important;
    transform: none !important;
    margin: 0 !important;
    float: none !important;
    animation: og-modal-in 0.35s cubic-bezier(0.34, 1.4, 0.64, 1);
}

@keyframes og-modal-in {
    from { opacity: 0; transform: scale(0.92) translateY(16px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

/* 关闭按钮：悬浮于渐变头部之上 */
.og-modal-close {
    position: absolute;
    top: 14px; right: 14px;
    z-index: 5;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.28);
    color: #fff;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s;
}

.og-modal-close:hover {
    background: rgba(255, 255, 255, 0.32);
    transform: rotate(90deg);
}

/* 弹窗渐变头部（表单态） */
.og-modal-header {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px 20px 0 0;
    padding: 24px 28px 24px 28px;
    padding-right: 52px;
    display: flex;
    align-items: center;
    gap: 16px;
    color: #fff;
}

.og-modal-header::before,
.og-result-header::before {
    content: '';
    position: absolute;
    top: -60px; right: -40px;
    width: 180px; height: 180px;
    background: radial-gradient(circle, rgba(255,255,255,0.16) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.og-modal-header::after {
    content: '';
    position: absolute;
    bottom: -70px; left: -30px;
    width: 160px; height: 160px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.og-modal-logo {
    position: relative;
    z-index: 1;
    flex-shrink: 0;
    width: 52px; height: 52px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.og-modal-title {
    position: relative;
    z-index: 1;
    margin: 0 0 2px;
    font-size: 1.2rem;
    font-weight: 700;
    color: #fff;
}

.og-modal-subtitle {
    position: relative;
    z-index: 1;
    margin: 0;
    font-size: 0.82rem;
    color: rgba(255,255,255,0.82);
}

.og-modal-header-text {
    position: relative;
    z-index: 1;
    min-width: 0;
}

/* 弹窗内容区：统一水平内边距（修复原内容贴边问题） */
.og-modal-body {
    padding: 22px 28px 28px;
}

/* 特性标签（标题下方横排） */
.og-feature-tags {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 8px;
    position: relative;
    z-index: 1;
}

.og-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    border: 1px solid transparent;
    white-space: nowrap;
}

.og-tag-blue  { background: rgba(255,255,255,0.2); color: #fff; border-color: rgba(255,255,255,0.35); }
.og-tag-green { background: rgba(255,255,255,0.18); color: #fff; border-color: rgba(255,255,255,0.3); }
.og-tag-amber { background: rgba(255,255,255,0.16); color: #fff; border-color: rgba(255,255,255,0.28); }

/* 信息卡片（兑换流程 / 激活步骤） */
.og-info-card {
    background: linear-gradient(135deg, #f8f9ff, #f1f4ff);
    border: 1px solid #e6eaff;
    border-radius: 14px;
    padding: 16px 18px;
    margin-bottom: 20px;
}

.og-info-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 12px;
}

.og-info-badge {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px; height: 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border-radius: 50%;
    font-size: 0.72rem;
    font-style: italic;
    font-weight: 700;
}

/* 步骤列表（带连接线） */
.og-steps {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.og-step {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding-left: 32px;
}

.og-step-num {
    position: absolute;
    left: 0;
    top: 1px;
    flex-shrink: 0;
    width: 22px; height: 22px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(102,126,234,0.35);
}

.og-step:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 26px;
    bottom: -12px;
    width: 2px;
    background: linear-gradient(to bottom, #c7d2fe, rgba(199, 210, 254, 0.25));
}

.og-step-text {
    font-size: 0.83rem;
    color: #555;
    line-height: 1.6;
}

/* 表单 */
.og-form-group {
    margin-bottom: 14px;
}

.og-form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 7px;
    font-size: 0.9rem;
}

.og-form-group input {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 0.95rem;
    color: #1f2937;
    background: #fafbff;
    transition: all 0.2s;
    box-sizing: border-box;
}

.og-form-group input::placeholder {
    color: #b0b7c3;
}

.og-form-group input:focus {
    outline: none;
    border-color: #667eea;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
}

/* 余额 / 费用 对比条 */
.og-balance-row {
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 14px 16px;
    background: #fff;
    border: 1px solid #eceef5;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    margin: 4px 0 16px;
}

.og-balance-item {
    flex: 1;
    text-align: center;
}

.og-balance-label {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-bottom: 3px;
}

.og-balance-value {
    font-size: 1.25rem;
    font-weight: 800;
    color: #1f2937;
}

.og-balance-value span {
    font-size: 0.72rem;
    font-weight: 500;
    color: #9ca3af;
    margin-left: 2px;
}

.og-balance-price {
    color: #667eea;
}

.og-balance-divider {
    width: 1px;
    height: 32px;
    background: linear-gradient(to bottom, #f3f4f6, #e5e7eb, #f3f4f6);
}

/* 积分不足警告 */
.og-warn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 11px 14px;
    margin-bottom: 16px;
    font-size: 0.85rem;
    color: #92400e;
    line-height: 1.5;
}

.og-warn a {
    color: #667eea;
    font-weight: 600;
    text-decoration: underline;
}

/* 主按钮 */
.og-submit-btn {
    width: 100%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border: none;
    padding: 13px;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 6px 18px rgba(102, 126, 234, 0.35);
}

.og-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(102, 126, 234, 0.45);
}

.og-submit-btn:active:not(:disabled) {
    transform: translateY(0);
}

.og-submit-btn:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* 成功结果 */
.og-result {
    display: none;
}

.og-result.show {
    display: block;
    animation: og-fade-up 0.35s ease;
}

@keyframes og-fade-up {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.og-result-header {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #34d399 0%, #16a34a 100%);
    border-radius: 20px 20px 0 0;
    padding: 30px 28px 26px;
    text-align: center;
    color: #fff;
}

.og-result-icon {
    position: relative;
    z-index: 1;
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.2);
    border: 1.5px solid rgba(255,255,255,0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    animation: og-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both;
}

@keyframes og-pop {
    0% { transform: scale(0); }
    100% { transform: scale(1); }
}

.og-check-path {
    stroke-dasharray: 30;
    stroke-dashoffset: 30;
    animation: og-check-draw 0.45s ease 0.4s forwards;
}

@keyframes og-check-draw {
    to { stroke-dashoffset: 0; }
}

.og-result-title {
    position: relative;
    z-index: 1;
    margin: 0 0 4px;
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
}

.og-result-subtitle {
    position: relative;
    z-index: 1;
    margin: 0;
    font-size: 0.82rem;
    color: rgba(255,255,255,0.85);
}

/* 授权码 */
.og-license-label {
    font-size: 0.82rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 8px;
}

.og-license-box {
    font-family: Consolas, Monaco, 'Courier New', monospace;
    font-size: 0.8rem;
    word-break: break-all;
    color: #333;
    line-height: 1.6;
    background: #f8f9fc;
    border: 1.5px dashed #c7d2fe;
    border-radius: 12px;
    padding: 12px 14px;
    max-height: 110px;
    overflow-y: auto;
}

/* 复制按钮（次级按钮） */
.og-copy-btn {
    display: block;
    width: 100%;
    background: #eef2ff;
    color: #4f46e5;
    border: 1px solid #e0e7ff;
    padding: 10px;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 10px;
}

.og-copy-btn:hover {
    background: #e0e7ff;
}

.og-copy-btn.og-copied {
    background: #ecfdf5;
    color: #059669;
    border-color: #a7f3d0;
}

/* 下载按钮（主按钮） */
.og-download-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    padding: 13px;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
    margin: 4px 0 16px;
    transition: all 0.25s;
    box-shadow: 0 6px 18px rgba(34, 197, 94, 0.3);
}

.og-download-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(34, 197, 94, 0.42);
    text-decoration: none;
    color: #fff;
}

/* 激活步骤紧凑版 */
.og-info-card-compact {
    margin-bottom: 0;
    padding: 14px 16px;
}

.og-info-card-compact .og-steps {
    gap: 10px;
}

.og-info-card-compact .og-step {
    padding-left: 28px;
}

.og-info-card-compact .og-step-num {
    width: 20px; height: 20px;
    font-size: 0.68rem;
}

.og-info-card-compact .og-step:not(:last-child)::before {
    top: 24px;
    bottom: -10px;
}

.og-info-card-compact .og-step-text {
    font-size: 0.8rem;
    color: #666;
}

/* 弹窗移动端适配 */
@media (max-width: 768px) {
    .og-modal-body {
        padding: 18px 20px 22px;
    }

    .og-modal-header,
    .og-result-header {
        padding: 26px 20px 22px;
    }
}

@media (max-width: 768px) {
    .more-page {
        padding: 20px 15px;
    }

    .page-title {
        font-size: 2rem;
    }

    .cards-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .tabs {
        justify-content: flex-start;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        gap: 4px;
        padding-bottom: 2px;
    }

    .tab-item {
        padding: 10px 16px;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .sub-tabs {
        justify-content: flex-start;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        gap: 4px;
    }

    .sub-tab-item {
        padding: 7px 16px;
        font-size: 0.88rem;
        flex-shrink: 0;
    }
}
</style>

<div class="more-page">
    <!-- 一级 Tab 导航 -->
    <div class="tabs">
        <div class="tab-item active" data-tab="games" onclick="switchTab('games')">🎮 小游戏</div>
        <div class="tab-item" data-tab="ai" onclick="switchTab('ai')">🤖 AI</div>
        <div class="tab-item" data-tab="coding" onclick="switchTab('coding')">💻 编程</div>
        <div class="tab-item" data-tab="teacher" onclick="switchTab('teacher')">👨‍🏫 教师服务</div>
    </div>

    <!-- ============ 小游戏 Tab ============ -->
    <div class="tab-panel active" id="panel-games">
        <!-- 离线游戏推广横幅（二级Tab上方） -->
        <div class="og-banner-top" id="og-banner" onclick="<?php if (!$og_logged_in): ?>location.href='loginpage.php?return=more.php%23games'<?php else: ?>ogOpenModal()<?php endif; ?>">
            <div class="og-banner-top-close" onclick="event.stopPropagation(); document.getElementById('og-banner').style.display='none';">✕</div>
            <div class="og-banner-top-icon">
                <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <rect x="8" y="18" width="48" height="34" rx="6" fill="#fff" fill-opacity="0.95"/>
                    <rect x="8" y="18" width="48" height="10" rx="6" fill="#fff"/>
                    <path d="M8 24 h48" stroke="rgba(102,126,234,0.2)" stroke-width="1"/>
                    <rect x="14" y="32" width="16" height="3" rx="1.5" fill="#667eea" fill-opacity="0.5"/>
                    <rect x="14" y="38" width="10" height="3" rx="1.5" fill="#667eea" fill-opacity="0.3"/>
                    <circle cx="46" cy="36" r="8" fill="#667eea" fill-opacity="0.15"/>
                    <path d="M43 36 l2 2 4-4" stroke="#667eea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="24" y="12" width="16" height="8" rx="3" fill="#fff" fill-opacity="0.9"/>
                    <path d="M28 16 h8" stroke="#667eea" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="og-banner-top-content">
                <div class="og-banner-top-title">
                    📦 课前游戏集合 · 离线安装包
                    <span class="og-banner-top-tags-inline">
                        <span class="og-banner-top-tag">✅ 无需联网</span>
                        <span class="og-banner-top-tag">✅ 单机运行</span>
                        <span class="og-banner-top-tag">✅ 授权管理</span>
                        <span class="og-banner-top-tag">✅ 一年有效期</span>
                    </span>
                </div>
                <div class="og-banner-top-desc">机房没网也能玩！包含全部17款教育游戏的离线版本，适合无网络的教学环境</div>
            </div>
            <div class="og-banner-top-action" onclick="event.stopPropagation()">
                <div class="og-banner-top-price">
                    <span class="og-banner-top-badge">🔥 限时特惠</span>
                    <span class="og-banner-top-price-old">199</span>
                    <span class="og-banner-top-price-num">50</span>
                    <span class="og-banner-top-price-unit">积分/年</span>
                </div>
                <?php if (!$og_logged_in): ?>
                    <button class="og-banner-top-btn" onclick="location.href='loginpage.php?return=more.php%23games'">登录后兑换</button>
                <?php elseif ($og_has_order): ?>
                    <button class="og-banner-top-btn og-banner-top-btn-success" onclick="ogOpenModal()">🔑 我的授权码</button>
                <?php else: ?>
                    <button class="og-banner-top-btn" onclick="ogOpenModal()">立即兑换</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- 二级 Tab -->
        <div class="og-subtabs-row">
            <div class="sub-tabs">
                <div class="sub-tab-item active" data-subtab="lower" onclick="switchSubTab('lower')">🎒 低年级专区（1-3年级）</div>
                <div class="sub-tab-item" data-subtab="upper" onclick="switchSubTab('upper')">📚 高年级专区（4-6年级）</div>
                <div class="sub-tab-item" data-subtab="typing" onclick="switchSubTab('typing')">⌨️ 打字练习</div>
            </div>
        </div>

        <!-- 二级面板：低年级专区 -->
        <div class="sub-panel active" id="subpanel-lower">
        <div class="section">
            <h2 class="section-title">🎒 低年级专区（1-3年级） <span class="auth-tag tag-public">无需登录</span></h2>
        <div class="cards-grid">
            <a href="puzzle_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #eab308, #f97316);">
                    <svg viewBox="0 0 64 64">
                        <rect x="10" y="10" width="26" height="26" rx="6" fill="#fff"/>
                        <circle cx="36" cy="23" r="6.5" fill="#fff"/>
                        <rect x="28" y="28" width="26" height="26" rx="6" fill="#fff"/>
                        <circle cx="36" cy="41" r="5.5" fill="rgba(0,0,0,0.3)"/>
                    </svg>
                </div>
                <div class="card-title">拼图游戏</div>
                <div class="card-desc">拖动拼图碎片还原图片，训练观察力与空间思维（适合1-3年级）</div>
            </a>

            <a href="clock_reading.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                    <svg viewBox="0 0 64 64">
                        <circle cx="32" cy="32" r="23" fill="#fff"/>
                        <circle cx="32" cy="32" r="18" fill="none" stroke="rgba(0,0,0,0.12)" stroke-width="2"/>
                        <path d="M32 14 v18 l11 8" stroke="rgba(0,0,0,0.5)" stroke-width="4.5" stroke-linecap="round" fill="none"/>
                        <circle cx="32" cy="32" r="3.5" fill="rgba(0,0,0,0.5)"/>
                    </svg>
                </div>
                <div class="card-title">时钟认读</div>
                <div class="card-desc">学习看时钟，认识整点和半点（适合1-2年级）</div>
            </a>

            <a href="math_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f97316, #dc2626);">
                    <svg viewBox="0 0 64 64">
                        <rect x="15" y="8" width="34" height="48" rx="7" fill="#fff"/>
                        <rect x="19" y="13" width="26" height="11" rx="2.5" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="23" cy="34" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="32" cy="34" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="41" cy="34" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="23" cy="45" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="32" cy="45" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="41" cy="45" r="3" fill="rgba(0,0,0,0.35)"/>
                    </svg>
                </div>
                <div class="card-title">数学闯关</div>
                <div class="card-desc">挑战数学题，闯过一关又一关（适合2-4年级）</div>
            </a>

            <a href="color_match.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                    <svg viewBox="0 0 64 64">
                        <path d="M32 8 a24 24 0 1 0 24 24 l-8 0 a8 8 0 0 1 -8 -8 a8 8 0 0 0 -8 -8 h-2 a10 10 0 0 0 2 -8 z" fill="#fff"/>
                        <circle cx="20" cy="20" r="3" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="34" cy="14" r="3" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="44" cy="26" r="3" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="18" cy="36" r="3" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="40" cy="44" r="4" fill="rgba(255,255,255,0.5)"/>
                    </svg>
                </div>
                <div class="card-title">颜色匹配</div>
                <div class="card-desc">认识颜色，点击正确的颜色名称（适合1-2年级）</div>
            </a>

            <a href="guess_number.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <svg viewBox="0 0 64 64">
                        <circle cx="30" cy="28" r="19" fill="#fff"/>
                        <text x="30" y="38" font-family="Arial, sans-serif" font-size="27" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.5)">?</text>
                        <rect x="42" y="42" width="16" height="16" rx="3" fill="rgba(0,0,0,0.25)"/>
                        <circle cx="46" cy="46" r="1.8" fill="#fff"/>
                        <circle cx="54" cy="54" r="1.8" fill="#fff"/>
                        <circle cx="54" cy="46" r="1.8" fill="#fff"/>
                        <circle cx="46" cy="54" r="1.8" fill="#fff"/>
                    </svg>
                </div>
                <div class="card-title">猜数字</div>
                <div class="card-desc">动动脑筋，猜出隐藏的神秘数字（适合1-3年级）</div>
            </a>

            <a href="memory_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #6366f1, #06b6d4);">
                    <svg viewBox="0 0 64 64">
                        <rect x="10" y="16" width="24" height="32" rx="5" fill="#fff" transform="rotate(-8 22 32)"/>
                        <rect x="32" y="20" width="24" height="32" rx="5" fill="#fff" transform="rotate(7 44 36)"/>
                        <path d="M39 34 l3 4 5 1 -4 4 1 5 -5 -3 -5 3 1 -5 -4 -4 5 -1 z" fill="rgba(0,0,0,0.3)"/>
                    </svg>
                </div>
                <div class="card-title">卡片配对</div>
                <div class="card-desc">翻开卡片，找出相同的图案配对（适合1-3年级）</div>
            </a>

            <a href="sequence_memory.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #a855f7, #ec4899);">
                    <svg viewBox="0 0 64 64">
                        <path d="M8 44 h44" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
                        <circle cx="16" cy="44" r="5" fill="#fff"/>
                        <circle cx="32" cy="24" r="5" fill="#fff"/>
                        <circle cx="48" cy="44" r="5" fill="#fff"/>
                        <path d="M32 29 v15" stroke="#fff" stroke-width="3"/>
                    </svg>
                </div>
                <div class="card-title">序列记忆</div>
                <div class="card-desc">记住颜色顺序并重复，挑战你的记忆力（适合1-4年级）</div>
            </a>

        </div>
    </div>
        </div><!-- /subpanel-lower -->

        <!-- 二级面板：高年级专区 -->
        <div class="sub-panel" id="subpanel-upper">
    <!-- 高年级专区（4-6年级） -->
    <div class="section">
        <h2 class="section-title">📚 高年级专区（4-6年级） <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="snake.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                    <svg viewBox="0 0 64 64">
                        <path d="M12 46 q10 -16 20 -4 q10 12 20 -6" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round"/>
                        <circle cx="52" cy="36" r="6.5" fill="#fff"/>
                        <circle cx="49.5" cy="34" r="2.2" fill="rgba(0,0,0,0.45)"/>
                        <path d="M58 33 l5 -4" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="18" cy="22" r="5.5" fill="#fff"/>
                        <path d="M15 17 q2 -4 6 -2" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="card-title">贪吃蛇</div>
                <div class="card-desc">控制小蛇吃食物变长，不要撞墙（适合3-6年级）</div>
            </a>

            <a href="bead_game.php" class="card">
                <div class="card-icon" style="background: #d1d5db;">
                    <svg viewBox="0 0 64 64">
                        <circle cx="16" cy="16" r="5" fill="#ef4444"/>
                        <circle cx="32" cy="16" r="5" fill="#f59e0b"/>
                        <circle cx="48" cy="16" r="5" fill="#22c55e"/>
                        <circle cx="16" cy="32" r="5" fill="#3b82f6"/>
                        <circle cx="32" cy="32" r="5" fill="#a855f7"/>
                        <circle cx="48" cy="32" r="5" fill="#ec4899"/>
                        <circle cx="16" cy="48" r="5" fill="#14b8a6"/>
                        <circle cx="32" cy="48" r="5" fill="#f97316"/>
                        <circle cx="48" cy="48" r="5" fill="#6366f1"/>
                    </svg>
                </div>
                <div class="card-title">拼豆游戏</div>
                <div class="card-desc">上传图片或选默认图，照着像素图拼豆子，创作专属图案（适合4-6年级）</div>
            </a>

            <a href="number_puzzle.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #14b8a6, #0891b2);">
                    <svg viewBox="0 0 64 64">
                        <rect x="10" y="10" width="44" height="44" rx="7" fill="#fff"/>
                        <path d="M10 24.7 h44 M10 39.3 h44 M24.7 10 v44 M39.3 10 v44" stroke="rgba(0,0,0,0.12)" stroke-width="2"/>
                        <text x="17.3" y="20.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">1</text>
                        <text x="32" y="20.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">2</text>
                        <text x="46.7" y="20.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">3</text>
                        <text x="17.3" y="35.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">4</text>
                        <text x="32" y="35.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">5</text>
                        <text x="46.7" y="35.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">6</text>
                        <text x="17.3" y="50.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">7</text>
                        <text x="32" y="50.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">8</text>
                    </svg>
                </div>
                <div class="card-title">数字华容道</div>
                <div class="card-desc">滑动数字块，按1-15顺序排列（适合3-6年级）</div>
            </a>

            <a href="idiom_chain.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #d97706, #b45309);">
                    <svg viewBox="0 0 64 64">
                        <path d="M32 16 L10 12 v36 l22 4 z" fill="#fff"/>
                        <path d="M32 16 L54 12 v36 l-22 4 z" fill="#fff"/>
                        <path d="M32 16 v36" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>
                        <path d="M14 18 h12 M14 24 h12 M14 30 h10" stroke="rgba(0,0,0,0.3)" stroke-width="2" stroke-linecap="round"/>
                        <path d="M38 18 h12 M38 24 h12 M40 30 h10" stroke="rgba(0,0,0,0.3)" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="card-title">成语接龙</div>
                <div class="card-desc">60秒限时挑战，成语知识大比拼（适合3-6年级）</div>
            </a>

            <a href="minesweeper.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #6b7280, #374151);">
                    <svg viewBox="0 0 64 64">
                        <g stroke="#fff" stroke-width="4" stroke-linecap="round">
                            <line x1="32" y1="8" x2="32" y2="17"/>
                            <line x1="32" y1="47" x2="32" y2="56"/>
                            <line x1="8" y1="32" x2="17" y2="32"/>
                            <line x1="47" y1="32" x2="56" y2="32"/>
                            <line x1="15" y1="15" x2="22" y2="22"/>
                            <line x1="42" y1="42" x2="49" y2="49"/>
                            <line x1="15" y1="49" x2="22" y2="42"/>
                            <line x1="42" y1="22" x2="49" y2="15"/>
                        </g>
                        <circle cx="32" cy="32" r="12" fill="#fff"/>
                        <circle cx="32" cy="32" r="4.5" fill="rgba(0,0,0,0.5)"/>
                    </svg>
                </div>
                <div class="card-title">扫雷</div>
                <div class="card-desc">经典扫雷，找出所有隐藏的地雷（适合4-6年级）</div>
            </a>
        </div>
    </div>
        </div><!-- /subpanel-upper -->

        <!-- 二级面板：打字练习 -->
        <div class="sub-panel" id="subpanel-typing">
    <!-- 打字练习 -->
    <div class="section">
        <h2 class="section-title">⌨️ 打字练习 <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="keyboard_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    <svg viewBox="0 0 64 64">
                        <rect x="8" y="16" width="48" height="33" rx="6" fill="#fff"/>
                        <g fill="rgba(0,0,0,0.3)">
                            <rect x="12" y="20" width="8" height="6" rx="1.5"/>
                            <rect x="23" y="20" width="8" height="6" rx="1.5"/>
                            <rect x="34" y="20" width="8" height="6" rx="1.5"/>
                            <rect x="45" y="20" width="8" height="6" rx="1.5"/>
                            <rect x="12" y="29" width="8" height="6" rx="1.5"/>
                            <rect x="23" y="29" width="8" height="6" rx="1.5"/>
                            <rect x="34" y="29" width="8" height="6" rx="1.5"/>
                            <rect x="45" y="29" width="8" height="6" rx="1.5"/>
                            <rect x="12" y="38" width="20" height="6" rx="1.5"/>
                            <rect x="35" y="38" width="18" height="6" rx="1.5"/>
                        </g>
                    </svg>
                </div>
                <div class="card-title">打字游戏</div>
                <div class="card-desc">在游戏中提升打字速度与准确率（适合3-6年级）</div>
            </a>

            <a href="balloon_typing.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f43f5e, #db2777);">
                    <svg viewBox="0 0 64 64">
                        <ellipse cx="32" cy="27" rx="16" ry="19" fill="#fff"/>
                        <path d="M32 45 l-3.5 4.5 h7 z" fill="#fff"/>
                        <path d="M32 50 q-3 12 -9 14" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <path d="M27 13 q3 -5 6 0" stroke="rgba(0,0,0,0.3)" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <ellipse cx="26" cy="21" rx="4" ry="7" fill="rgba(255,255,255,0.6)" transform="rotate(18 26 21)"/>
                    </svg>
                </div>
                <div class="card-title">气球打字</div>
                <div class="card-desc">打字击破上升的气球，练习键盘输入（适合3-6年级）</div>
            </a>

            <a href="frog_typing.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #22c55e, #15803d);">
                    <svg viewBox="0 0 64 64">
                        <ellipse cx="15" cy="46" rx="8" ry="6" fill="#fff"/>
                        <ellipse cx="49" cy="46" rx="8" ry="6" fill="#fff"/>
                        <ellipse cx="32" cy="39" rx="17" ry="14" fill="#fff"/>
                        <ellipse cx="32" cy="43" rx="10" ry="8" fill="rgba(0,0,0,0.15)"/>
                        <circle cx="23" cy="24" r="8" fill="#fff"/>
                        <circle cx="41" cy="24" r="8" fill="#fff"/>
                        <circle cx="24" cy="25" r="3" fill="rgba(0,0,0,0.5)"/>
                        <circle cx="42" cy="25" r="3" fill="rgba(0,0,0,0.5)"/>
                        <path d="M26 44 q6 5 12 0" stroke="rgba(0,0,0,0.35)" stroke-width="2" fill="none" stroke-linecap="round"/>
                        <circle cx="18" cy="42" r="2.5" fill="rgba(0,0,0,0.2)"/>
                        <circle cx="46" cy="42" r="2.5" fill="rgba(0,0,0,0.2)"/>
                    </svg>
                </div>
                <div class="card-title">青蛙过河</div>
                <div class="card-desc">打对石头上的词让青蛙过河，闯关收集星星解锁关卡（适合3-6年级）</div>
            </a>
        </div>
    </div>
        </div><!-- /subpanel-typing -->

    </div><!-- /panel-games -->

    <!-- ============ AI Tab ============ -->
    <div class="tab-panel" id="panel-ai">
    <!-- AI训练 -->
    <div class="section">
        <h2 class="section-title">🤖 AI训练 <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="AI_training.php?type=image" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #06b6d4, #3b82f6);">
                    <svg viewBox="0 0 64 64">
                        <rect x="12" y="12" width="40" height="40" rx="7" fill="#fff"/>
                        <circle cx="22" cy="24" r="4" fill="rgba(0,0,0,0.28)"/>
                        <path d="M16 44 l12 -12 7 7 6 -6 9 11 z" fill="rgba(0,0,0,0.28)"/>
                    </svg>
                </div>
                <div class="card-title">图像分类</div>
                <div class="card-desc">训练AI模型识别不同类别的图像内容</div>
            </a>

            <a href="AI_training.php?type=handpose" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f97316, #f43f5e);">
                    <svg viewBox="0 0 64 64">
                        <rect x="14" y="20" width="8" height="24" rx="4" fill="#fff"/>
                        <rect x="24" y="14" width="8" height="30" rx="4" fill="#fff"/>
                        <rect x="34" y="12" width="8" height="32" rx="4" fill="#fff"/>
                        <rect x="44" y="20" width="8" height="24" rx="4" fill="#fff"/>
                        <path d="M14 44 h38 v2 a9 9 0 0 1 -9 9 h-20 a9 9 0 0 1 -9 -9 z" fill="#fff"/>
                        <ellipse cx="10" cy="38" rx="4" ry="9" fill="#fff" transform="rotate(-25 10 38)"/>
                    </svg>
                </div>
                <div class="card-title">手势分类</div>
                <div class="card-desc">训练AI模型识别各种手势动作</div>
            </a>

            <!-- 三方API已下线，暂时隐藏：语音分类、图像识别、手势识别 -->
            <!--
            <a href="AI_training.php?type=audio" class="card">
                <div class="card-icon">
                    <i class="microphone icon"></i>
                </div>
                <div class="card-title">语音分类</div>
                <div class="card-desc">训练AI模型识别不同的语音特征</div>
            </a>

            <a href="AI_training.php?type=recognition" class="card">
                <div class="card-icon">
                    <i class="eye icon"></i>
                </div>
                <div class="card-title">图像识别</div>
                <div class="card-desc">体验先进的图像识别与分析技术</div>
            </a>

            <a href="AI_training.php?type=gesture" class="card">
                <div class="card-icon">
                    <i class="hand rock icon"></i>
                </div>
                <div class="card-title">手势识别</div>
                <div class="card-desc">实时识别手势动作，体验交互乐趣</div>
            </a>
            -->
        </div>
    </div>

    <!-- AI体验 -->
    <div class="section">
        <h2 class="section-title">✨ AI体验 <span class="auth-tag tag-public">无需登录</span></h2>
        <div class="cards-grid">
            <a href="javascript:openAIExperience()" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #8b5cf6, #d946ef);">
                    <svg viewBox="0 0 64 64">
                        <rect x="30" y="6" width="4" height="12" fill="#fff"/>
                        <circle cx="32" cy="4" r="3" fill="#fff"/>
                        <rect x="13" y="18" width="38" height="32" rx="10" fill="#fff"/>
                        <circle cx="25" cy="32" r="4.5" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="39" cy="32" r="4.5" fill="rgba(0,0,0,0.35)"/>
                        <rect x="25" y="43" width="14" height="3.5" rx="1.75" fill="rgba(0,0,0,0.35)"/>
                    </svg>
                </div>
                <div class="card-title">AI进阶</div>
                <div class="card-desc">体验前沿大语言模型的强大能力</div>
            </a>

            <a href="ai_drawing_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #ec4899, #f43f5e);">
                    <svg viewBox="0 0 64 64">
                        <rect x="26" y="14" width="12" height="30" rx="5" fill="#fff"/>
                        <path d="M26 16 l-4 -4 a10 10 0 0 1 12 -12 l4 4 a8 8 0 0 1 -4 8 q-4 4 -8 4 z" fill="#fff"/>
                        <path d="M50 8 l2.5 5 5 2.5 -5 2.5 -2.5 5 -2.5 -5 -5 -2.5 5 -2.5 z" fill="#fff"/>
                    </svg>
                </div>
                <div class="card-title">AI猜猜画</div>
                <div class="card-desc">画图让AI识别，认识人工智能（适合1-6年级）</div>
            </a>
        </div>
    </div>
    </div><!-- /panel-ai -->

    <!-- ============ 编程 Tab ============ -->
    <div class="tab-panel" id="panel-coding">
    <!-- 编程学习 -->
    <div class="section">
        <h2 class="section-title">💻 编程学习 <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="coding_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                    <svg viewBox="0 0 64 64">
                        <path d="M19 16 L7 32 L19 48" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M45 16 L57 32 L45 48" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M37 13 L27 51" stroke="#fff" stroke-width="5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="card-title">编程启蒙</div>
                <div class="card-desc">拖拽积木学习编程，控制小猫走迷宫（适合3-6年级）</div>
            </a>

            <a href="https://turbowarp.org/editor" target="_blank" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                    <svg viewBox="0 0 64 64">
                        <circle cx="27" cy="34" r="15" fill="#fff"/>
                        <path d="M15 26 L8 12 l14 6 z" fill="#fff"/>
                        <path d="M39 26 l7 -14 -14 6 z" fill="#fff"/>
                        <circle cx="22" cy="33" r="4" fill="rgba(0,0,0,0.45)"/>
                        <circle cx="32" cy="33" r="4" fill="rgba(0,0,0,0.45)"/>
                        <path d="M27 42 q5 5 10 0" stroke="rgba(0,0,0,0.35)" stroke-width="2" fill="none" stroke-linecap="round"/>
                        <circle cx="24" cy="38" r="1.6" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="30" cy="38" r="1.6" fill="rgba(0,0,0,0.3)"/>
                        <path d="M12 48 l-5 3 M42 48 l5 3" stroke="rgba(0,0,0,0.3)" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="card-title">Scratch在线编程</div>
                <div class="card-desc">在线Scratch编辑器，轻松创作作品（适合3-6年级）</div>
            </a>

            <!-- Scratch案例暂未上线，暂时隐藏 -->
            <!--
            <a href="scratch.php" class="card">
                <div class="card-icon">
                    <i class="code icon"></i>
                </div>
                <div class="card-title">Scratch案例</div>
                <div class="card-desc">丰富的Scratch编程案例学习</div>
            </a>
            -->
        </div>
    </div>
    </div><!-- /panel-coding -->

    <!-- ============ 教师服务 Tab ============ -->
    <div class="tab-panel" id="panel-teacher">
    <!-- 教师服务 -->
    <div class="section">
        <h2 class="section-title">👨‍🏫 教师服务 <span class="auth-tag tag-public">无需登录</span></h2>
        <div class="cards-grid">
            <a href="teacher_guide.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    <svg viewBox="0 0 64 64">
                        <path d="M32 10 L8 22 L32 34 L56 22 Z" fill="#fff"/>
                        <rect x="12" y="33" width="40" height="5" rx="2.5" fill="#fff"/>
                        <circle cx="32" cy="40" r="6" fill="#fff"/>
                        <path d="M50 22 v10 a5 5 0 0 1 -5 5" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round"/>
                        <circle cx="43" cy="39" r="2.5" fill="#fff"/>
                    </svg>
                </div>
                <div class="card-title">学生账号批量开通</div>
                <div class="card-desc">教师专属通道：联系客服QQ，一次开通全班学生账号（适合班级/年级统一使用）</div>
            </a>
        </div>
    </div>
    </div><!-- /panel-teacher -->
</div>

<!-- 离线游戏兑换弹窗 -->
<div class="og-modal-mask" id="og-modal-mask">
    <div class="og-modal" role="dialog" aria-modal="true" aria-labelledby="og-modal-title">
        <button type="button" class="og-modal-close" onclick="ogCloseModal()" aria-label="关闭">&times;</button>

        <!-- 兑换表单 -->
        <div id="og-form-section">
            <!-- 顶部渐变头部 -->
            <div class="og-modal-header">
                <div class="og-modal-logo">
                    <svg viewBox="0 0 64 64" width="32" height="32" fill="none">
                        <rect x="8" y="18" width="48" height="34" rx="6" fill="#fff" fill-opacity="0.95"/>
                        <rect x="8" y="18" width="48" height="10" rx="6" fill="#fff"/>
                        <path d="M8 24 h48" stroke="rgba(102,126,234,0.2)" stroke-width="1"/>
                        <rect x="14" y="32" width="16" height="3" rx="1.5" fill="#667eea" fill-opacity="0.5"/>
                        <rect x="14" y="38" width="10" height="3" rx="1.5" fill="#667eea" fill-opacity="0.3"/>
                        <circle cx="46" cy="36" r="8" fill="#667eea" fill-opacity="0.15"/>
                        <path d="M43 36 l2 2 4-4" stroke="#667eea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="24" y="12" width="16" height="8" rx="3" fill="#fff" fill-opacity="0.9"/>
                        <path d="M28 16 h8" stroke="#667eea" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="og-modal-header-text">
                    <h3 class="og-modal-title" id="og-modal-title">离线游戏安装包</h3>
                    <p class="og-modal-subtitle">50积分兑换 · 一年有效期</p>
                    <!-- 特性标签 -->
                    <div class="og-feature-tags">
                        <span class="og-tag og-tag-blue">🎮 17款游戏</span>
                        <span class="og-tag og-tag-green">📶 无需联网</span>
                        <span class="og-tag og-tag-amber">🔒 授权管理</span>
                    </div>
                </div>
            </div>

            <div class="og-modal-body">
                <!-- 兑换流程 -->
                <div class="og-info-card">
                    <div class="og-info-title">
                        <span class="og-info-badge">i</span>
                        兑换流程
                    </div>
                    <div class="og-steps">
                        <div class="og-step">
                            <span class="og-step-num">1</span>
                            <span class="og-step-text">填写学校和机房名称，系统生成专属授权码</span>
                        </div>
                        <div class="og-step">
                            <span class="og-step-num">2</span>
                            <span class="og-step-text">下载安装包，解压到教师机</span>
                        </div>
                        <div class="og-step">
                            <span class="og-step-num">3</span>
                            <span class="og-step-text">在 games/activate.html 验证授权码，下载 license.js 放入 js 目录</span>
                        </div>
                        <div class="og-step">
                            <span class="og-step-num">4</span>
                            <span class="og-step-text">整包拷贝到学生机，双击 index.html 直接使用（无需再激活）</span>
                        </div>
                    </div>
                </div>

                <!-- 填写信息 -->
                <div class="og-form-group">
                    <label for="og-school">学校名称</label>
                    <input type="text" id="og-school" placeholder="如：XX市第一小学" maxlength="100">
                </div>
                <div class="og-form-group">
                    <label for="og-room">机房名称</label>
                    <input type="text" id="og-room" placeholder="如：计算机教室1" maxlength="100">
                </div>

                <!-- 余额与费用 -->
                <div class="og-balance-row">
                    <div class="og-balance-item">
                        <div class="og-balance-label">当前余额</div>
                        <div class="og-balance-value"><?php echo $og_balance; ?> <span>积分</span></div>
                    </div>
                    <div class="og-balance-divider"></div>
                    <div class="og-balance-item">
                        <div class="og-balance-label">兑换费用</div>
                        <div class="og-balance-value og-balance-price">50 <span>积分</span></div>
                    </div>
                </div>

                <?php if ($og_balance < $og_price): ?>
                <div class="og-warn">
                    <span>⚠️</span>
                    <span>积分不足，请先<a href="point_index.php">兑换充值卡</a></span>
                </div>
                <?php endif; ?>

                <button type="button" class="og-submit-btn" id="og-submit-btn" onclick="ogSubmit()"
                    <?php if ($og_balance < $og_price) echo 'disabled'; ?>>
                    📦 确认兑换（50积分）
                </button>
            </div>
        </div>

        <!-- 兑换成功结果 -->
        <div class="og-result" id="og-result-section">
            <!-- 成功头部 -->
            <div class="og-result-header">
                <div class="og-result-icon">
                    <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path class="og-check-path" d="M20 6L9 17l-5-5"/>
                    </svg>
                </div>
                <h4 class="og-result-title" id="og-result-title">兑换成功</h4>
                <p class="og-result-subtitle" id="og-result-subtitle">请复制授权码并下载安装包</p>
            </div>

            <div class="og-modal-body">
                <!-- 授权码 -->
                <div>
                    <div class="og-license-label">授权码</div>
                    <div class="og-license-box" id="og-license-box"></div>
                    <button type="button" class="og-copy-btn" id="og-copy-btn" onclick="ogCopyLicense()">📋 复制授权码</button>
                </div>

                <!-- 下载按钮 -->
                <a id="og-download-link" href="#" target="_blank" class="og-download-link">⬇️ 离线包下载</a>

                <!-- 激活步骤 -->
                <div class="og-info-card og-info-card-compact">
                    <div class="og-info-title">激活步骤</div>
                    <div class="og-steps">
                        <div class="og-step">
                            <span class="og-step-num">1</span>
                            <span class="og-step-text">离线包下载并解压</span>
                        </div>
                        <div class="og-step">
                            <span class="og-step-num">2</span>
                            <span class="og-step-text">复制上述授权码</span>
                        </div>
                        <div class="og-step">
                            <span class="og-step-num">3</span>
                            <span class="og-step-text">解压后的离线包中，双击打开 index.html，按照引导激活离线包</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ===== 离线游戏兑换 =====
var ogLoggedin = <?php echo $og_logged_in ? 'true' : 'false'; ?>;
var ogPostkey = '<?php echo addslashes($og_postkey); ?>';
var ogBalance = <?php echo $og_balance; ?>;
var ogPrice = <?php echo $og_price; ?>;
var ogHasOrder = <?php echo $og_has_order ? 'true' : 'false'; ?>;
var ogMyLicense = <?php echo json_encode($og_license_code, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var ogDownloadUrl = <?php echo json_encode($og_download_url, JSON_HEX_TAG); ?>;
var ogOrderExpire = <?php echo json_encode($og_order_expire); ?>;
var ogOrderSchool = <?php echo json_encode($og_order_school); ?>;
var ogOrderRoom = <?php echo json_encode($og_order_room); ?>;

// 弹窗顶部标题/副标题（兑换表单态）
function ogShowForm() {
    document.getElementById('og-form-section').style.display = '';
    document.getElementById('og-result-section').classList.remove('show');
    document.getElementById('og-school').value = '';
    document.getElementById('og-room').value = '';
}

// 结果视图：复用兑换成功结果区（新兑换 / 我的授权码 共用）
function ogShowResult(title, subtitle, licenseCode, downloadUrl) {
    document.getElementById('og-form-section').style.display = 'none';
    document.getElementById('og-result-title').textContent = title;
    document.getElementById('og-result-subtitle').textContent = subtitle;
    document.getElementById('og-license-box').textContent = licenseCode;
    document.getElementById('og-download-link').href = downloadUrl;
    document.getElementById('og-result-section').classList.add('show');
}

// "我的授权码"视图：展示已有授权码与下载链接
function ogShowMyLicense() {
    ogShowResult('🔑 我的授权码',
        ogOrderSchool + ' · ' + ogOrderRoom + ' · 有效期至 ' + ogOrderExpire,
        ogMyLicense, ogDownloadUrl);
}

function ogOpenModal() {
    document.getElementById('og-modal-mask').classList.add('show');
    // 锁定背景滚动
    document.body.style.overflow = 'hidden';
    if (ogHasOrder) {
        ogShowMyLicense();
    } else {
        ogShowForm();
    }
}

function ogCloseModal() {
    document.getElementById('og-modal-mask').classList.remove('show');
    // 恢复背景滚动
    document.body.style.overflow = '';
}

// 点击遮罩关闭
document.getElementById('og-modal-mask').addEventListener('click', function(e) {
    if (e.target === this) ogCloseModal();
});

// ESC 键关闭弹窗
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('og-modal-mask').classList.contains('show')) {
        ogCloseModal();
    }
});

function ogSubmit() {
    var school = document.getElementById('og-school').value.trim();
    var room = document.getElementById('og-room').value.trim();

    if (!school) { alert('请输入学校名称'); return; }
    if (!room) { alert('请输入机房名称'); return; }

    var btn = document.getElementById('og-submit-btn');
    btn.disabled = true;
    btn.textContent = '正在处理...';

    var formData = new FormData();
    formData.append('school_name', school);
    formData.append('room_name', room);
    formData.append('postkey', ogPostkey);

    fetch('offline_game_redeem.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.code === 0) {
            // 成功：更新本地状态，弹窗切换为结果视图
            ogHasOrder = true;
            ogMyLicense = data.data.license_code;
            ogDownloadUrl = data.data.download_url;
            ogOrderExpire = data.data.expire_date;
            ogOrderSchool = data.data.school_name;
            ogOrderRoom = data.data.room_name;

            ogShowResult('兑换成功', '请复制授权码并下载安装包',
                data.data.license_code, data.data.download_url);

            // 更新横幅按钮为"我的授权码"
            var bannerAction = document.querySelector('.og-banner-top-action');
            if (bannerAction) {
                bannerAction.innerHTML = '<div class="og-banner-top-price"><span class="og-banner-top-price-num">✓</span><span class="og-banner-top-price-unit">已兑换</span></div><button class="og-banner-top-btn og-banner-top-btn-success" onclick="ogOpenModal()">🔑 我的授权码</button>';
            }
        } else {
            alert(data.msg);
            btn.disabled = false;
            btn.textContent = '📦 确认兑换（50积分）';
        }
    })
    .catch(function(err) {
        console.error('ogSubmit error:', err);
        alert('网络错误，请稍后重试');
        btn.disabled = false;
        btn.textContent = '📦 确认兑换（50积分）';
    });
}

function ogCopyLicense() {
    var text = document.getElementById('og-license-box').textContent;
    var btn = document.getElementById('og-copy-btn');

    function ogCopied() {
        // 按钮内联反馈，避免 alert 打断
        btn.classList.add('og-copied');
        btn.textContent = '✓ 已复制到剪贴板';
        setTimeout(function() {
            btn.classList.remove('og-copied');
            btn.textContent = '📋 复制授权码';
        }, 2000);
    }

    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(ogCopied).catch(function() {
            alert('复制失败，请手动选择授权码复制');
        });
    } else {
        // 降级方案
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            ogCopied();
        } catch (err) {
            alert('复制失败，请手动选择授权码复制');
        }
        document.body.removeChild(ta);
    }
}
</script>

<script>
// 复现header中的openAIExperience函数
function openAIExperience() {
    window.open('https://yiyan.baidu.com/', '_blank');
}

// ===== Tab 切换逻辑 =====
// 一级 Tab 切换
function switchTab(tabName) {
    // 切换一级 Tab 高亮
    document.querySelectorAll('.tabs .tab-item').forEach(function(el) {
        el.classList.toggle('active', el.getAttribute('data-tab') === tabName);
    });
    // 切换一级面板显示
    document.querySelectorAll('.tab-panel').forEach(function(el) {
        el.classList.toggle('active', el.id === 'panel-' + tabName);
    });
    // 切换到小游戏时，默认显示低年级子 Tab
    if (tabName === 'games') {
        switchSubTab('lower');
    }
    // 更新 URL hash，便于直链与刷新保持
    if (history.replaceState) {
        history.replaceState(null, '', '#' + tabName);
    }
}

// 二级 Tab 切换（仅小游戏面板内）
function switchSubTab(subName) {
    var gamesPanel = document.getElementById('panel-games');
    if (!gamesPanel) return;
    gamesPanel.querySelectorAll('.sub-tabs .sub-tab-item').forEach(function(el) {
        el.classList.toggle('active', el.getAttribute('data-subtab') === subName);
    });
    gamesPanel.querySelectorAll('.sub-panel').forEach(function(el) {
        el.classList.toggle('active', el.id === 'subpanel-' + subName);
    });
}

// 页面加载时根据 URL hash 定位 Tab，默认小游戏
(function() {
    var validTabs = ['games', 'ai', 'coding', 'teacher'];
    var hash = (window.location.hash || '').replace('#', '');
    var initTab = validTabs.indexOf(hash) >= 0 ? hash : 'games';
    switchTab(initTab);
})();
</script>

<?php require("footer.php"); ?>