<?php
$show_title="你的教师电量还剩几格 - $OJ_NAME";
// 传播型 H5 极简壳：隐藏 OJ 顶栏与页脚主体（header.php/footer.php 按 $hide_chrome 分支，保留骨架+限流+极简署名）
$hide_chrome = true;
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
// 页面无表单、无入库、无用户态数据：仅注入站点名给分享卡片文案（JSON 十六进制转义防注入）
$tb_json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<style>
/* ===== 你的教师电量还剩几格（前缀 tb-）黑板涂鸦 + 电池充电 UI，移动端 375px 起 ===== */
:root{
    --tb-board:#2E4A3F; --tb-board-deep:#23392F; --tb-chalk:#F5F8F2; --tb-paper:#FAFCF7;
    --tb-ink:#1E2B24; --tb-ink-soft:#5E6E64;
    --tb-green:#4CAF50; --tb-yellow:#FFC107; --tb-warn:#E4573D;
    --tb-line:rgba(46,74,63,.16);
    --tb-accent:#4CAF50; --tb-accent-fg:#FFFFFF;
    --tb-body:"PingFang SC","Microsoft YaHei",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}
.tb-page{
    max-width:560px; margin:0 auto; padding:18px 14px 46px;
    min-height:calc(100vh - 48px); /* 极简壳后仅剩署名行高度 */
    color:var(--tb-ink); font-family:var(--tb-body); line-height:1.65;
    background-color:var(--tb-paper);
    background-image:radial-gradient(rgba(46,74,63,.11) 1px, transparent 1px);
    background-size:22px 22px;
}
.tb-page *{box-sizing:border-box}
.tb-state{display:none}
.tb-state.is-on{display:block}
.tb-card{background:#fff;border:2px solid rgba(46,74,63,.55);border-radius:16px;
    box-shadow:0 3px 0 rgba(46,74,63,.14), 0 10px 24px rgba(46,74,63,.07)}
.tb-h3{
    position:relative; display:inline-block; margin:0 0 10px; padding-left:18px;
    font-size:1.06rem; font-weight:800; color:var(--tb-ink); letter-spacing:.02em;
}
.tb-h3::before{
    content:"⚡"; position:absolute; left:0; top:.1em; font-size:.86rem;
}
.tb-btn{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;min-height:52px;padding:10px 16px;border-radius:14px;
    border:2px solid var(--tb-ink);font-family:var(--tb-body);font-weight:800;font-size:1.02rem;
    cursor:pointer;text-decoration:none;transition:transform .12s ease, box-shadow .12s ease;
}
.tb-btn:active{transform:translate(2px,2px);box-shadow:none!important}
.tb-btn:focus-visible,a.tb-btn:focus-visible{outline:3px solid var(--tb-green);outline-offset:3px}
.tb-btn--go{
    background:linear-gradient(100deg, var(--tb-green) 0%, var(--tb-yellow) 100%);
    color:#16241D;font-size:1.16rem;box-shadow:0 4px 0 rgba(30,43,36,.55);
}
.tb-btn--mint{background:var(--tb-green);color:#fff;box-shadow:0 4px 0 #3B8B40}
.tb-btn--ghost{background:#fff;color:var(--tb-ink);box-shadow:0 3px 0 rgba(46,74,63,.3)}

/* ---- 落地页：黑板报 ---- */
.tb-board{
    position:relative;overflow:hidden;padding:26px 20px 24px;border-radius:18px;
    background:
        repeating-linear-gradient(115deg, rgba(255,255,255,.028) 0 3px, transparent 3px 26px),
        linear-gradient(160deg, var(--tb-board) 0%, var(--tb-board-deep) 100%);
    border:3px solid #1B2E25;box-shadow:inset 0 0 0 2px rgba(245,248,242,.14), 0 10px 26px rgba(30,43,36,.28);
}
.tb-board::after{ /* 黑板下沿粉笔槽 */
    content:"";position:absolute;left:0;right:0;bottom:0;height:9px;
    background:linear-gradient(180deg, rgba(245,248,242,.16), rgba(245,248,242,.05));
}
.tb-doodle{position:absolute;pointer-events:none;opacity:.55}
.tb-doodle--bolt{top:14px;right:14px;width:54px;transform:rotate(8deg)}
.tb-doodle--star{bottom:26px;left:16px;width:40px;transform:rotate(-6deg)}
.tb-kicker{margin:0 0 8px;font-size:.86rem;font-weight:800;color:#A8D8B4;letter-spacing:.08em}
.tb-title{
    font-weight:800;font-size:clamp(1.8rem,8vw,2.35rem);
    line-height:1.32;margin:0;color:var(--tb-chalk);letter-spacing:.01em;
    text-shadow:0 1px 0 rgba(0,0,0,.25);
}
.tb-title mark{
    background:linear-gradient(transparent 54%, rgba(255,193,7,.85) 54%);color:#1E2B24;
    padding:0 .08em;border-radius:4px;
}
.tb-sub{margin:12px 0 0;font-size:.98rem;color:#CFE3D5}
.tb-hook{margin:10px 0 0;font-size:.9rem;color:#9FC6A9;border-left:3px solid rgba(255,193,7,.7);padding-left:10px}
/* 粉笔电池涂鸦（落地页视觉引子，结果页大电池是主锤） */
.tb-batt-doodle{display:block;width:min(240px,72%);margin:18px auto 4px}
.tb-count{
    display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;
    margin:16px 0 4px;padding:12px 16px;border:2px dashed rgba(46,74,63,.35);
    border-radius:12px;background:#fff;font-weight:700;color:var(--tb-ink);
}
.tb-count b{
    font-weight:800;font-size:2.5rem;line-height:1;
    background:linear-gradient(100deg, var(--tb-green), var(--tb-yellow));
    -webkit-background-clip:text;background-clip:text;color:transparent;letter-spacing:.01em;
}
.tb-avatars{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0 4px;padding:0;list-style:none}
.tb-avatars li{
    display:flex;align-items:center;gap:6px;padding:5px 11px 5px 8px;
    background:#fff;border:2px solid rgba(46,74,63,.5);border-radius:999px;
    font-size:.85rem;font-weight:800;box-shadow:0 2px 0 rgba(46,74,63,.14);
}
.tb-avatars li:nth-child(odd){transform:rotate(-1.6deg)}
.tb-avatars li:nth-child(even){transform:rotate(1.4deg)}
.tb-start{margin-top:16px}
.tb-meta{margin:12px 0 0;text-align:center;font-size:.86rem;color:var(--tb-ink-soft)}
.tb-landing-note{margin:14px 0 0;text-align:center;font-size:.8rem;color:var(--tb-ink-soft)}

/* ---- 答题页 ---- */
.tb-qbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px}
.tb-dots{display:flex;gap:6px;flex:1}
.tb-dots span{flex:1;height:7px;border-radius:99px;background:#E1EAE0;transition:background .2s ease}
.tb-dots span.is-done{background:var(--tb-board)}
.tb-dots span.is-cur{background:linear-gradient(90deg,var(--tb-green),var(--tb-yellow));box-shadow:0 0 0 2px rgba(46,74,63,.22)}
.tb-qnum{font-size:.85rem;font-weight:800;color:var(--tb-ink-soft);white-space:nowrap}
.tb-back{
    background:none;border:none;padding:10px 8px;min-height:44px;font:inherit;font-size:.88rem;font-weight:700;
    color:#3B8B40;cursor:pointer;text-decoration:underline;
}
.tb-back:focus-visible{outline:3px solid var(--tb-green);outline-offset:2px;border-radius:6px}
.tb-qcard{padding:20px 16px 18px}
.tb-qcard.is-in{animation:tb-slide .26s ease both}
@keyframes tb-slide{from{opacity:0;transform:translateX(22px)}to{opacity:1;transform:none}}
.tb-qscene{
    display:inline-block;padding:3px 10px;font-size:.82rem;font-weight:800;color:#2E4A3F;
    background:rgba(76,175,80,.14);border:1.5px solid rgba(76,175,80,.5);border-radius:999px;
}
.tb-qtitle{font-weight:800;font-size:1.3rem;line-height:1.5;margin:12px 0 16px;color:var(--tb-ink)}
.tb-opts{display:flex;flex-direction:column;gap:12px;margin:0;padding:0;list-style:none}
.tb-opt{
    display:flex;align-items:center;gap:12px;width:100%;min-height:56px;
    padding:12px 14px;text-align:left;background:#fff;color:var(--tb-ink);
    border:2px solid rgba(46,74,63,.4);border-radius:14px;
    font:inherit;font-size:.98rem;font-weight:700;line-height:1.5;cursor:pointer;
    transition:border-color .15s ease, background .15s ease, transform .12s ease;
}
.tb-opt:hover{border-color:var(--tb-board);background:#FDFEFB}
.tb-opt:focus-visible{outline:3px solid var(--tb-green);outline-offset:2px}
.tb-opt.is-picked{
    border-color:var(--tb-green);background:#F1FAF1;transform:translate(2px,2px);
    box-shadow:0 2px 0 rgba(46,74,63,.2);
}
/* 选项字母 = 一节小电池 */
.tb-opt b{
    flex:0 0 auto;position:relative;display:grid;place-items:center;width:34px;height:26px;
    border:2px solid var(--tb-board);border-radius:6px;color:#2E4A3F;
    font-weight:800;font-size:.92rem;background:#EDF4EC;
}
.tb-opt b::after{
    content:"";position:absolute;right:-6px;top:50%;transform:translateY(-50%);
    width:4px;height:11px;background:var(--tb-board);border-radius:0 3px 3px 0;
}
.tb-opt.is-picked b{
    border-color:#3B8B40;color:#fff;
    background:linear-gradient(100deg, var(--tb-green), var(--tb-yellow));
}
.tb-opt.is-picked b::after{background:#3B8B40}
.tb-qfoot{margin:14px 0 0;text-align:center;font-size:.82rem;color:var(--tb-ink-soft)}

/* ---- 结果页 ---- */
.tb-block{margin-bottom:18px}
/* 1 大电池：匹配度载体（全页唯一重锤，替代环形匹配度） */
.tb-hero{text-align:center;padding:20px 14px 18px;border-radius:18px;
    background:linear-gradient(165deg, var(--tb-board) 0%, var(--tb-board-deep) 100%);
    border:3px solid #1B2E25;box-shadow:inset 0 0 0 2px rgba(245,248,242,.14), 0 10px 26px rgba(30,43,36,.26);
}
.tb-hero-cap{margin:0 0 14px;font-size:.9rem;font-weight:800;color:#A8D8B4;letter-spacing:.06em}
.tb-batt{
    position:relative;width:min(320px,88%);margin:0 auto;
}
.tb-batt-shell{
    display:flex;gap:7px;height:110px;padding:10px;
    background:#F7FAF5;border:4px solid #16241D;border-radius:18px;
}
.tb-batt-shell::after{
    content:"";position:absolute;right:-13px;top:50%;transform:translateY(-50%);
    width:12px;height:46px;background:#16241D;border-radius:0 9px 9px 0;
}
.tb-cell{
    flex:1;border-radius:7px;background:#E4EBE2;border:2px solid rgba(30,43,36,.16);
}
.tb-cell.is-fill{animation:tb-pop .28s ease both}
@keyframes tb-pop{from{transform:scaleY(.55);opacity:.5}to{transform:none;opacity:1}}
.tb-pct{
    display:flex;align-items:baseline;justify-content:center;gap:6px;margin:16px 0 0;color:var(--tb-chalk);
}
.tb-pct span{font-weight:800;font-size:3rem;line-height:1;letter-spacing:.01em}
.tb-pct i{font-style:normal;font-size:1.1rem;font-weight:800;color:var(--tb-yellow)}
.tb-pct-cap{margin:6px 0 0;font-size:.85rem;font-weight:700;color:#9FC6A9}
.tb-pct-bars{margin:4px 0 0;font-size:.92rem;font-weight:800;color:var(--tb-yellow)}

/* 2 人格揭晓 */
.tb-identity{text-align:center;padding:18px 16px 20px}
.tb-avatar{
    width:76px;height:76px;margin:0 auto 10px;border-radius:18px;display:grid;place-items:center;
    font-size:2.2rem;background:var(--tb-accent);border:2px solid var(--tb-ink);
    box-shadow:0 4px 0 rgba(30,43,36,.35);transform:rotate(-4deg);color:var(--tb-accent-fg);
}
.tb-lead{margin:0;font-size:.95rem;font-weight:700;color:var(--tb-ink-soft)}
.tb-name{font-weight:800;font-size:2.1rem;line-height:1.25;margin:2px 0 4px;color:var(--tb-ink);letter-spacing:.02em}
.tb-epithet{margin:0;font-size:1rem;font-weight:700;color:var(--tb-accent)}
.tb-tags,.tb-pills{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin:0;padding:0;list-style:none}
.tb-tags{margin-bottom:10px}
.tb-tag{
    display:inline-block;padding:6px 14px;border-radius:999px;background:#fff;
    color:var(--tb-ink);border:2px solid var(--tb-accent);font-weight:800;font-size:.92rem;
    box-shadow:0 3px 0 rgba(46,74,63,.14);
}
.tb-tags li:nth-child(1){transform:rotate(-2deg)}
.tb-tags li:nth-child(2){transform:rotate(1.5deg)}
.tb-tags li:nth-child(3){transform:rotate(-1deg)}
.tb-pill{
    display:inline-block;padding:5px 12px;border:2px dashed rgba(46,74,63,.4);
    border-radius:999px;color:var(--tb-ink-soft);font-size:.85rem;font-weight:700;background:#F4F9F3;
}
/* 4 金句：黑板上的粉笔话 */
.tb-quote{
    position:relative;padding:22px 18px 16px;background:linear-gradient(165deg, var(--tb-board), var(--tb-board-deep));
    border-radius:16px;border:3px solid #1B2E25;box-shadow:0 8px 20px rgba(30,43,36,.22);
}
.tb-quote::after{
    content:"";position:absolute;left:34px;bottom:-14px;
    border:8px solid transparent;border-top-color:var(--tb-board-deep);
}
.tb-quote blockquote{
    position:relative;margin:0;font-weight:800;font-size:1.2rem;line-height:1.7;color:var(--tb-chalk);
    text-shadow:0 1px 0 rgba(0,0,0,.28);
}
.tb-quote cite{display:block;margin-top:8px;font-style:normal;font-size:.85rem;font-weight:700;color:#A8D8B4;text-align:right}
/* 5 理由：横线备课本 */
.tb-reason{
    padding:14px 16px;border:2px solid rgba(46,74,63,.35);border-radius:12px;
    background:repeating-linear-gradient(#fff 0 31px, rgba(46,74,63,.14) 31px 32px);
    font-size:.96rem;line-height:32px;color:#33413A;
}
.tb-radar{width:100%;height:auto;display:block}
/* 7 隐藏第二人格 */
.tb-second{
    display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:14px;
    background:#fff;border:2px dashed rgba(46,74,63,.55);
    box-shadow:0 3px 0 rgba(46,74,63,.12);
}
.tb-second-emoji{
    flex:0 0 auto;width:50px;height:50px;border-radius:12px;display:grid;place-items:center;
    font-size:1.5rem;border:2px solid rgba(30,43,36,.7);
}
.tb-second-body{flex:1;min-width:0}
.tb-second-body p{margin:0}
.tb-second-label{font-size:.8rem;font-weight:800;color:#3B8B40}
.tb-second-name{font-weight:800;font-size:1.24rem;color:var(--tb-ink)}
.tb-second-desc{font-size:.85rem;color:var(--tb-ink-soft)}
.tb-second-pct{flex:0 0 auto;font-weight:800;font-size:1.45rem;color:var(--tb-warn)}
/* 8 转化区 */
.tb-slogan{
    margin:0 0 12px;padding:13px 15px;border-radius:12px;
    background:var(--tb-board);color:var(--tb-chalk);font-weight:800;font-size:1.02rem;
    text-align:center;letter-spacing:.02em;
}
.tb-links{display:flex;flex-direction:column;gap:10px;margin:0;padding:0;list-style:none}
.tb-link{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:12px;
    background:#fff;border:2px solid rgba(46,74,63,.35);
    text-decoration:none;color:var(--tb-ink);
    transition:border-color .15s ease, transform .15s ease;
}
.tb-link:hover{border-color:var(--tb-board);text-decoration:none;transform:translateY(-2px)}
.tb-link:focus-visible{outline:3px solid var(--tb-green);outline-offset:2px}
.tb-link-ico{
    flex:0 0 auto;width:42px;height:42px;border-radius:11px;display:grid;place-items:center;
    border:2px solid rgba(30,43,36,.65);
}
.tb-link-ico svg{width:22px;height:22px}
.tb-link-t{font-weight:800;font-size:.98rem}
.tb-link-d{font-size:.82rem;color:var(--tb-ink-soft);line-height:1.45}
.tb-link-chev{margin-left:auto;color:var(--tb-ink-soft);flex:0 0 auto}
.tb-login-hint{
    margin:12px 0 0;padding:11px 14px;border-radius:12px;background:#F4F9F3;
    border:2px dashed rgba(46,74,63,.4);font-size:.88rem;font-weight:700;color:var(--tb-ink-soft);text-align:center;
}
.tb-login-hint a{color:#3B8B40;font-weight:800}
.tb-actions{display:flex;flex-direction:column;gap:10px}
.tb-note{margin:16px 0 0;text-align:center;font-size:.78rem;color:var(--tb-ink-soft)}

/* ---- 分享卡片弹层 ---- */
.tb-modal{
    position:fixed;inset:0;z-index:2000;display:none;align-items:flex-start;justify-content:center;
    padding:24px 14px;overflow-y:auto;background:rgba(30,43,36,.62);
}
.tb-modal.is-on{display:flex}
.tb-modal-box{
    width:100%;max-width:420px;background:var(--tb-paper);border:2px solid var(--tb-ink);
    border-radius:16px;box-shadow:0 10px 34px rgba(0,0,0,.35);padding:14px 14px 18px;
}
.tb-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.tb-modal-head span{font-weight:800;font-size:1.1rem;color:var(--tb-ink)}
.tb-x{
    width:44px;height:44px;border-radius:11px;border:2px solid var(--tb-ink);background:#fff;
    font-size:1.3rem;line-height:1;color:var(--tb-ink);cursor:pointer;
}
.tb-x:focus-visible{outline:3px solid var(--tb-green);outline-offset:2px}
.tb-share-img{display:block;width:100%;height:auto;border-radius:12px;border:2px solid rgba(46,74,63,.6);background:#fff}
.tb-modal-hint{margin:10px 0 12px;text-align:center;font-size:.86rem;font-weight:700;color:var(--tb-ink-soft)}

@media (min-width: 640px){
    .tb-page{padding-top:26px}
    .tb-board{padding:34px 30px 30px}
}
@media (prefers-reduced-motion: reduce){
    .tb-page *,.tb-page *::before,.tb-page *::after{
        animation-duration:.001s!important;animation-iteration-count:1!important;transition-duration:.001s!important;
    }
}
</style>

<div class="tb-page">

    <!-- ============ 状态一：落地页 ============ -->
    <section id="tb-landing" class="tb-state is-on" aria-label="测试介绍">
        <div class="tb-board">
            <svg class="tb-doodle tb-doodle--bolt" viewBox="0 0 48 64" aria-hidden="true">
                <path d="M28 4 L10 36 h12 L18 60 L40 24 H26 Z" fill="none" stroke="#F5F8F2" stroke-width="3" stroke-linejoin="round"/>
            </svg>
            <svg class="tb-doodle tb-doodle--star" viewBox="0 0 48 48" aria-hidden="true">
                <path d="M24 4 l5 13 14 1 -11 9 4 14 -12 -8 -12 8 4 -14 -11 -9 14 -1 Z" fill="none" stroke="#F5F8F2" stroke-width="3" stroke-linejoin="round"/>
            </svg>
            <p class="tb-kicker">教师趣味测评 · 电量检测站</p>
            <h1 class="tb-title">你的教师电量<br>还剩<mark>几格</mark>？</h1>
            <p class="tb-sub">开学第 8 周，你的电还撑得到寒假吗</p>
            <p class="tb-hook">上辈子杀猪，这辈子教书？先别对号入座——测测是什么在偷偷给你充电。</p>
            <svg class="tb-batt-doodle" viewBox="0 0 260 96" aria-hidden="true">
                <rect x="6" y="18" width="222" height="62" rx="14" fill="none" stroke="#F5F8F2" stroke-width="4"/>
                <rect x="232" y="36" width="18" height="26" rx="6" fill="none" stroke="#F5F8F2" stroke-width="4"/>
                <rect x="22" y="32" width="36" height="34" rx="7" fill="#4CAF50" opacity=".85"/>
                <rect x="66" y="32" width="36" height="34" rx="7" fill="#8BC34A" opacity=".8"/>
                <rect x="110" y="32" width="36" height="34" rx="7" fill="#FFC107" opacity=".8"/>
                <path d="M178 26 l-14 24 h12 l-6 20 18 -26 h-12 Z" fill="none" stroke="#FFC107" stroke-width="3.5" stroke-linejoin="round"/>
            </svg>
        </div>
        <p class="tb-count">已有 <b id="tb-total">8437</b> 位老师测过</p>
        <ul class="tb-avatars" id="tb-landing-avatars"></ul>
        <button type="button" class="tb-btn tb-btn--go tb-start" id="tb-start">开始检测电量</button>
        <p class="tb-meta">8 道题 · 约 1 分钟 · 无需登录</p>
        <p class="tb-landing-note">纯属娱乐，结果仅供开心参考</p>
    </section>

    <!-- ============ 状态二：答题页 ============ -->
    <section id="tb-quiz" class="tb-state" aria-label="答题">
        <div class="tb-qbar">
            <div class="tb-dots" id="tb-dots" aria-hidden="true"></div>
            <span class="tb-qnum" id="tb-qnum">1 / 8</span>
        </div>
        <button type="button" class="tb-back" id="tb-back" style="display:none">← 上一题</button>
        <div class="tb-card tb-qcard" id="tb-qcard">
            <span class="tb-qscene" id="tb-qscene"></span>
            <h2 class="tb-qtitle" id="tb-qtitle"></h2>
            <ul class="tb-opts" id="tb-opts"></ul>
        </div>
        <p class="tb-qfoot">凭第一直觉选，别想太久</p>
    </section>

    <!-- ============ 状态三：结果页（顺序固定） ============ -->
    <section id="tb-result" class="tb-state" aria-label="测试结果">

        <!-- 1 大电池（视觉锤 + 匹配度载体） -->
        <div class="tb-block tb-hero">
            <p class="tb-hero-cap">你的人格匹配电量</p>
            <div class="tb-batt" role="img" aria-label="人格匹配电量">
                <div class="tb-batt-shell" id="tb-batt-shell">
                    <span class="tb-cell"></span><span class="tb-cell"></span><span class="tb-cell"></span>
                    <span class="tb-cell"></span><span class="tb-cell"></span><span class="tb-cell"></span>
                    <span class="tb-cell"></span><span class="tb-cell"></span><span class="tb-cell"></span>
                    <span class="tb-cell"></span>
                </div>
            </div>
            <p class="tb-pct"><span id="tb-pct">0</span><i>%</i></p>
            <p class="tb-pct-cap">匹配度 · 电量格数即匹配度</p>
            <p class="tb-pct-bars" id="tb-bars">10 格中点亮 0 格</p>
        </div>

        <!-- 2 人格揭晓 -->
        <div class="tb-block tb-card tb-identity">
            <div class="tb-avatar" id="tb-avatar" aria-hidden="true"></div>
            <p class="tb-lead">你的剩余电量人格是——</p>
            <h2 class="tb-name" id="tb-name"></h2>
            <p class="tb-epithet" id="tb-epithet"></p>
        </div>

        <!-- 3 人设标签 + 关键词胶囊 -->
        <div class="tb-block">
            <ul class="tb-tags" id="tb-tags"></ul>
            <ul class="tb-pills" id="tb-pills"></ul>
        </div>

        <!-- 4 金句 -->
        <div class="tb-block tb-quote">
            <blockquote id="tb-quote"></blockquote>
            <cite id="tb-quote-by"></cite>
        </div>

        <!-- 5 个性化理由 -->
        <div class="tb-block">
            <h3 class="tb-h3">电量检测报告</h3>
            <div class="tb-reason" id="tb-reason"></div>
        </div>

        <!-- 6 五维雷达 -->
        <div class="tb-block">
            <h3 class="tb-h3">你的五维电量分布</h3>
            <svg class="tb-radar" id="tb-radar" viewBox="0 0 320 258" role="img" aria-label="五维电量分布雷达图"></svg>
        </div>

        <!-- 7 第二人格 -->
        <div class="tb-block">
            <div class="tb-second">
                <div class="tb-second-emoji" id="tb-second-emoji" aria-hidden="true"></div>
                <div class="tb-second-body">
                    <p class="tb-second-label">你的隐藏第二人格</p>
                    <p class="tb-second-name" id="tb-second-name"></p>
                    <p class="tb-second-desc" id="tb-second-desc">换个答法，TA 可能会反超</p>
                </div>
                <div class="tb-second-pct" id="tb-second-pct"></div>
            </div>
        </div>

        <!-- 8 转化区 -->
        <div class="tb-block">
            <p class="tb-slogan">格子可以慢慢充，别一个人硬撑</p>
            <ul class="tb-links">
                <li>
                    <a class="tb-link" href="timetable.php">
                        <span class="tb-link-ico" style="background:#E8F5E9">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="10" y="16" width="44" height="38" rx="5" fill="none" stroke="#1E2B24" stroke-width="5"/>
                                <line x1="10" y1="28" x2="54" y2="28" stroke="#1E2B24" stroke-width="5"/>
                                <line x1="24" y1="11" x2="24" y2="20" stroke="#1E2B24" stroke-width="5" stroke-linecap="round"/>
                                <line x1="40" y1="11" x2="40" y2="20" stroke="#1E2B24" stroke-width="5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="tb-link-t">课程表生成器</span><br>
                            <span class="tb-link-d">给自己排一张有留白的课表</span>
                        </span>
                        <svg class="tb-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="tb-link" href="course.php">
                        <span class="tb-link-ico" style="background:#FFF8E1">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <path d="M10 14 h18 a6 6 0 0 1 6 6 v30 a6 6 0 0 0 -6 -6 H10 Z" fill="none" stroke="#1E2B24" stroke-width="5" stroke-linejoin="round"/>
                                <path d="M54 14 H36 a6 6 0 0 0 -6 6 v30 a6 6 0 0 1 6 -6 h18 Z" fill="none" stroke="#1E2B24" stroke-width="5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="tb-link-t">课件库</span><br>
                            <span class="tb-link-d">少熬夜，课件直接拿去用</span>
                        </span>
                        <svg class="tb-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="tb-link" href="more.php#games">
                        <span class="tb-link-ico" style="background:#E0F2F1">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="8" y="20" width="48" height="28" rx="12" fill="none" stroke="#1E2B24" stroke-width="5"/>
                                <line x1="18" y1="29" x2="18" y2="39" stroke="#1E2B24" stroke-width="5" stroke-linecap="round"/>
                                <line x1="13" y1="34" x2="23" y2="34" stroke="#1E2B24" stroke-width="5" stroke-linecap="round"/>
                                <circle cx="44" cy="31" r="3.4" fill="#1E2B24"/>
                                <circle cx="51" cy="38" r="3.4" fill="#1E2B24"/>
                            </svg>
                        </span>
                        <span>
                            <span class="tb-link-t">课前小游戏</span><br>
                            <span class="tb-link-d">教师省电模式：学生玩，你趁机歇会儿</span>
                        </span>
                        <svg class="tb-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
            </ul>
            <p class="tb-login-hint"><a href="loginpage.php">登录</a> 解锁更多教学工具（课程管理、作业布置…）</p>
        </div>

        <!-- 9 操作区 -->
        <div class="tb-block tb-actions">
            <button type="button" class="tb-btn tb-btn--mint" id="tb-share-btn">生成分享卡片</button>
            <button type="button" class="tb-btn tb-btn--ghost" id="tb-again-btn">重新检测</button>
        </div>

        <!-- 10 免责 -->
        <p class="tb-note">仅供娱乐参考 · 本测试不采集任何个人信息</p>
    </section>
</div>

<!-- ============ 状态四：分享卡片预览 ============ -->
<div class="tb-modal" id="tb-share" role="dialog" aria-modal="true" aria-label="分享卡片预览">
    <div class="tb-modal-box">
        <div class="tb-modal-head">
            <span>你的分享卡片</span>
            <button type="button" class="tb-x" id="tb-share-close" aria-label="关闭">&times;</button>
        </div>
        <img class="tb-share-img" id="tb-share-img" alt="教师电量测试分享卡片">
        <p class="tb-modal-hint" id="tb-share-hint">正在生成…</p>
        <button type="button" class="tb-btn tb-btn--ghost" id="tb-share-ok">完成</button>
    </div>
</div>

<script>
window.TBS = {
    siteName: <?php echo json_encode($OJ_NAME, $tb_json_flags); ?>
};
</script>
<!-- 通关礼花：canvas-confetti CDN + 项目公共 game_confetti.js（CDN 失败则静默降级） -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/game_confetti.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qrcode.min.js"></script>
<!-- 共享 Canvas/二维码工具（makeQrCanvas/roundRect/wrapLines），须在 qrcode.min.js 之后 -->
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qr_helper.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/teacher_battery_test_data.js"></script>
<script>
(function () {
    'use strict';
    var D = window.TB_DATA;
    if (!D) return;
    var DIM_KEYS = D.DIMENSIONS.map(function (d) { return d.key; });
    var LETTERS = ['A', 'B', 'C', 'D'];
    var CELL_TOTAL = 10; // 大电池 10 格，点亮格数 = 匹配度载体
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var state = { mode: 'landing', qi: 0, answers: [], locked: false, lastResult: null };

    var $ = function (id) { return document.getElementById(id); };

    /* ---------------- 状态机 ---------------- */
    function show(mode) {
        state.mode = mode;
        ['landing', 'quiz', 'result'].forEach(function (m) {
            $('tb-' + m).classList.toggle('is-on', m === mode);
        });
        var page = document.querySelector('.tb-page');
        window.scrollTo(0, page ? Math.max(0, page.offsetTop - 70) : 0);
    }

    /* ---------------- 落地页 ---------------- */
    function renderLanding() {
        $('tb-total').textContent = D.MATCHED_TOTAL;
        var ul = $('tb-landing-avatars');
        ul.innerHTML = '';
        D.PERSONAS.forEach(function (p) {
            var li = document.createElement('li');
            var i = document.createElement('i');
            i.style.cssText = 'font-style:normal;font-size:1rem';
            i.textContent = p.emoji;
            li.appendChild(i);
            li.appendChild(document.createTextNode(p.name));
            ul.appendChild(li);
        });
    }

    /* ---------------- 答题页 ---------------- */
    function renderQuiz(animate) {
        var q = D.QUESTIONS[state.qi];
        $('tb-qnum').textContent = (state.qi + 1) + ' / ' + D.QUESTIONS.length;
        $('tb-back').style.display = state.qi > 0 ? '' : 'none';

        var dots = $('tb-dots');
        dots.innerHTML = '';
        D.QUESTIONS.forEach(function (_, i) {
            var s = document.createElement('span');
            if (i < state.qi) s.className = 'is-done';
            if (i === state.qi) s.className = 'is-cur';
            dots.appendChild(s);
        });

        $('tb-qscene').textContent = '场景 · ' + q.scene;
        $('tb-qtitle').textContent = q.title;

        var ul = $('tb-opts');
        ul.innerHTML = '';
        q.options.forEach(function (opt, i) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tb-opt';
            var b = document.createElement('b');
            b.textContent = LETTERS[i];
            var span = document.createElement('span');
            span.textContent = opt.text;
            btn.appendChild(b);
            btn.appendChild(span);
            // 回退改答时回显原选择：点击已选项即可原路前进
            if (state.answers[state.qi] === i) btn.classList.add('is-picked');
            btn.addEventListener('click', function () { pick(i, btn); });
            li.appendChild(btn);
            ul.appendChild(li);
        });

        var card = $('tb-qcard');
        if (animate && !reduceMotion) {
            card.classList.remove('is-in');
            void card.offsetWidth; // 重排以重放进入动画
            card.classList.add('is-in');
        }
    }

    function pick(i, btn) {
        if (state.locked) return;
        state.locked = true;
        btn.classList.add('is-picked');
        state.answers[state.qi] = i;
        var delay = reduceMotion ? 0 : 240;
        setTimeout(function () {
            if (state.qi < D.QUESTIONS.length - 1) {
                state.qi++;
                renderQuiz(true);
                state.locked = false;
            } else {
                state.locked = false;
                finish();
            }
        }, delay);
    }

    function back() {
        if (state.qi === 0 || state.locked) return;
        state.qi--;
        renderQuiz(true);
    }

    function startOver() {
        state.answers = [];
        state.qi = 0;
        state.locked = false;
        show('quiz');
        renderQuiz(true);
    }

    /* ---------------- 匹配算法 ----------------
     * 选项权重 → 5 维用户向量（L2 归一）→ 与 5 种人格画像向量余弦相似度
     * → min-max 归一化（锚点 0.67~1.00）映射到 70%~97%，越界钳制。
     */
    function l2norm(vec) {
        var sum = 0, k;
        for (var i = 0; i < DIM_KEYS.length; i++) { k = DIM_KEYS[i]; sum += (vec[k] || 0) * (vec[k] || 0); }
        var n = Math.sqrt(sum) || 1, out = {};
        for (i = 0; i < DIM_KEYS.length; i++) { k = DIM_KEYS[i]; out[k] = (vec[k] || 0) / n; }
        return out;
    }

    function computeResult() {
        var raw = {}, i, k;
        for (i = 0; i < DIM_KEYS.length; i++) raw[DIM_KEYS[i]] = 0;
        state.answers.forEach(function (ai, qi) {
            var w = D.QUESTIONS[qi].options[ai].w;
            DIM_KEYS.forEach(function (key) { raw[key] += (w[key] || 0); });
        });
        var u = l2norm(raw);
        var sims = D.PERSONAS.map(function (p) {
            var v = l2norm(p.vector), s = 0;
            DIM_KEYS.forEach(function (key) { s += u[key] * v[key]; });
            return { key: p.key, cos: s };
        });
        var LO = 0.67, HI = 1.00, MIN = D.MATCH_MIN, MAX = D.MATCH_MAX;
        sims.forEach(function (s) {
            s.pct = Math.max(MIN, Math.min(MAX, Math.round(MIN + (s.cos - LO) / (HI - LO) * (MAX - MIN))));
        });
        sims.sort(function (a, b) { return b.cos - a.cos; });

        var byKey = {};
        D.PERSONAS.forEach(function (p) { byKey[p.key] = p; });
        var top = byKey[sims[0].key], second = byKey[sims[1].key];

        // 个性化理由：只取实测得分 > 0 的维度（raw=0 的维度不出现在理由里，防与画像矛盾）
        var order = DIM_KEYS.filter(function (k) { return raw[k] > 0; })
            .sort(function (a, b) { return raw[b] - raw[a]; });
        var useN = (order.length >= 3 && raw[order[2]] >= raw[order[0]] * 0.35) ? 3 : 2;
        if (order.length < 2) useN = order.length;

        var chosen = {}; // dim -> 已选变体下标（补字时取"另一变体"）
        var parts = [top.desc.intro];
        function takeDim(dim) {
            var vs = top.desc[dim];
            if (!vs || !vs.length) return;
            var i = (typeof chosen[dim] === 'number')
                ? (chosen[dim] + 1) % vs.length
                : Math.floor(Math.random() * vs.length);
            chosen[dim] = i;
            parts.push(vs[i]);
        }
        order.slice(0, useN).forEach(takeDim);
        var reason = parts.join('') + top.desc.outro;
        // 不足 80 字：先升到第 3 个高分维度；仍不足则用最高分维度的另一变体补齐
        if (reason.length < 80 && useN === 2 && order.length > 2) {
            takeDim(order[2]);
            reason = parts.join('') + top.desc.outro;
            useN = 3;
        }
        if (reason.length < 80 && order.length) {
            takeDim(order[0]);
            reason = parts.join('') + top.desc.outro;
        }

        return {
            raw: raw, sims: sims, top: top, second: second,
            topPct: sims[0].pct, secondPct: sims[1].pct, reason: reason
        };
    }

    /* ---------------- 电量格（匹配度载体） ---------------- */
    function cellColor(i) {
        // #4CAF50 → #FFC107 线性插值，第 0 格最绿、第 9 格最黄
        var c1 = [76, 175, 80], c2 = [255, 193, 7];
        var t = CELL_TOTAL <= 1 ? 0 : i / (CELL_TOTAL - 1);
        var r = Math.round(c1[0] + (c2[0] - c1[0]) * t);
        var g = Math.round(c1[1] + (c2[1] - c1[1]) * t);
        var b = Math.round(c1[2] + (c2[2] - c1[2]) * t);
        return 'rgb(' + r + ',' + g + ',' + b + ')';
    }

    function fillCells(shown, pct) {
        var cells = $('tb-batt-shell').children;
        for (var i = 0; i < cells.length; i++) {
            var on = i < shown;
            if (on !== cells[i].classList.contains('is-fill')) {
                cells[i].classList.toggle('is-fill', on);
                cells[i].style.background = on ? cellColor(i) : '';
            }
        }
        $('tb-bars').textContent = CELL_TOTAL + ' 格中点亮 ' + shown + ' 格';
        $('tb-pct').textContent = pct;
    }

    var tbBattRaf = 0;
    function revealBattery(pct) {
        var target = Math.max(1, Math.min(CELL_TOTAL, Math.round(pct / 10)));
        if (tbBattRaf) { cancelAnimationFrame(tbBattRaf); tbBattRaf = 0; } // 重测速进结果页时取消旧动画
        if (reduceMotion) { fillCells(target, pct); return; }
        var start = null, dur = 900;
        fillCells(0, 0);
        function step(ts) {
            if (start === null) start = ts;
            var t = Math.min(1, (ts - start) / dur);
            var e = 1 - Math.pow(1 - t, 3);
            fillCells(Math.round(target * e), Math.round(pct * e));
            if (t < 1) tbBattRaf = requestAnimationFrame(step);
            else { fillCells(target, pct); tbBattRaf = 0; }
        }
        tbBattRaf = requestAnimationFrame(step);
    }

    /* ---------------- 结果页 ---------------- */
    function renderResult(res) {
        state.lastResult = res;
        var top = res.top;
        var page = document.querySelector('.tb-page');
        page.style.setProperty('--tb-accent', top.color);
        page.style.setProperty('--tb-accent-fg', top.fg);

        // 大电池（视觉锤 + 匹配度）
        revealBattery(res.topPct);

        // 人格
        $('tb-avatar').textContent = top.emoji;
        $('tb-name').textContent = top.name;
        $('tb-epithet').textContent = top.epithet;

        // 标签 / 胶囊
        fillList('tb-tags', 'tb-tag', top.tags);
        fillList('tb-pills', 'tb-pill', top.pills);

        // 金句
        $('tb-quote').textContent = top.quote;
        $('tb-quote-by').textContent = '—— ' + top.name + ' 的口头禅';

        // 理由
        $('tb-reason').textContent = res.reason;

        // 雷达
        renderRadar(res.raw, top.color);

        // 第二人格
        $('tb-second-emoji').textContent = res.second.emoji;
        $('tb-second-emoji').style.background = res.second.color;
        $('tb-second-name').textContent = res.second.name;
        $('tb-second-pct').textContent = res.secondPct + '%';
        // 同分时避免"TA 可能会反超"与相同百分比自相矛盾
        $('tb-second-desc').textContent = (res.secondPct >= res.topPct)
            ? '和你不相上下，换个答法见分晓' : '换个答法，TA 可能会反超';

        show('result');
        if (typeof window.launchConfetti === 'function') window.launchConfetti();
    }

    function fillList(id, cls, arr) {
        var ul = $(id);
        ul.innerHTML = '';
        arr.forEach(function (t) {
            var li = document.createElement('li');
            li.className = cls;
            li.textContent = t;
            ul.appendChild(li);
        });
    }

    /* ---------------- 五维雷达（SVG） ---------------- */
    function renderRadar(raw, color) {
        var svg = $('tb-radar');
        var W = 320, H = 258, cx = 160, cy = 126, R = 92;
        var n = DIM_KEYS.length;
        var max = 0;
        DIM_KEYS.forEach(function (k) { if (raw[k] > max) max = raw[k]; });
        max = max || 1;

        function pt(i, r) {
            var a = -Math.PI / 2 + i * 2 * Math.PI / n;
            return [cx + r * Math.cos(a), cy + r * Math.sin(a)];
        }
        function poly(radii) {
            return radii.map(function (r, i) { var p = pt(i, r); return p[0].toFixed(1) + ',' + p[1].toFixed(1); }).join(' ');
        }
        var out = [];
        var grid = 'rgba(46,74,63,.3)';
        // 网格三层
        [0.34, 0.67, 1].forEach(function (f) {
            var pts = [];
            for (var i = 0; i < n; i++) pts.push(R * f);
            out.push('<polygon points="' + poly(pts) + '" fill="none" stroke="' + grid + '" stroke-width="1.5"/>');
        });
        // 轴线
        for (var i = 0; i < n; i++) {
            var p = pt(i, R);
            out.push('<line x1="' + cx + '" y1="' + cy + '" x2="' + p[0].toFixed(1) + '" y2="' + p[1].toFixed(1) + '" stroke="' + grid + '" stroke-width="1.5"/>');
        }
        // 数据多边形（最小 0.16 半径，避免零值塌成点）
        var dataR = DIM_KEYS.map(function (k) { return Math.max(0.16, raw[k] / max) * R; });
        out.push('<polygon points="' + poly(dataR) + '" fill="' + color + '" fill-opacity=".28" stroke="' + color + '" stroke-width="3" stroke-linejoin="round"/>');
        for (i = 0; i < n; i++) {
            var q = pt(i, dataR[i]);
            out.push('<circle cx="' + q[0].toFixed(1) + '" cy="' + q[1].toFixed(1) + '" r="5" fill="#fff" stroke="' + color + '" stroke-width="3"/>');
        }
        // 轴标签（顶标签 dy +12，防 viewBox 裁切中文字形）
        for (i = 0; i < n; i++) {
            var lp = pt(i, R + 26);
            var anchor = 'middle';
            if (lp[0] > cx + 8) anchor = 'start';
            else if (lp[0] < cx - 8) anchor = 'end';
            var dy = 5;
            if (lp[1] < cy - 40) dy = 12;
            if (lp[1] > cy + 40) dy = 12;
            out.push('<text x="' + lp[0].toFixed(1) + '" y="' + (lp[1] + dy).toFixed(1) + '" text-anchor="' + anchor +
                '" font-size="15" font-weight="700" fill="#1E2B24" font-family="PingFang SC, Microsoft YaHei, sans-serif">' +
                D.DIMENSIONS[i].label + '</text>');
        }
        svg.innerHTML = out.join('');
    }

    /* ---------------- 分享卡片（Canvas 750×1334）
     * roundRect / wrapLines / makeQrCanvas 均来自共享 js/qr_helper.js（不再本地拷贝）
     */
    function drawShareCard(res, url) {
        var W = 750, H = 1334;
        var c = document.createElement('canvas');
        c.width = W; c.height = H;
        var g = c.getContext('2d');
        var top = res.top;
        var bold = function (px) { return '800 ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };
        var reg = function (px, w) { return (w || 400) + ' ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };
        var cx = W / 2, maxW = 576;

        // 黑板底 + 粉笔划痕
        g.fillStyle = '#2E4A3F'; g.fillRect(0, 0, W, H);
        g.strokeStyle = 'rgba(245,248,242,.05)'; g.lineWidth = 2;
        for (var s = -H; s < W; s += 46) { g.beginPath(); g.moveTo(s, 0); g.lineTo(s + H, H); g.stroke(); }
        roundRect(g, 40, 44, W - 80, H - 88, 26);
        g.lineWidth = 4; g.strokeStyle = 'rgba(245,248,242,.4)'; g.stroke();

        g.textAlign = 'center'; g.textBaseline = 'alphabetic';

        // 顶部站点名
        g.font = reg(28, 700); g.fillStyle = '#A8D8B4';
        g.fillText(((window.TBS && window.TBS.siteName) || '') + ' · 教师电量趣味测评', cx, 124);

        // 大电池（匹配度载体）
        var bx = 96, by = 168, bw = 540, bh = 150, pad = 14;
        roundRect(g, bx, by, bw, bh, 22);
        g.fillStyle = '#F7FAF5'; g.fill();
        g.lineWidth = 7; g.strokeStyle = '#16241D'; g.stroke();
        roundRect(g, bx + bw + 4, by + bh / 2 - 30, 24, 60, 9);
        g.fillStyle = '#16241D'; g.fill();
        var target = Math.max(1, Math.min(CELL_TOTAL, Math.round(res.topPct / 10)));
        var innerW = bw - pad * 2, gap = 9;
        var cellW = (innerW - gap * (CELL_TOTAL - 1)) / CELL_TOTAL;
        for (var i = 0; i < CELL_TOTAL; i++) {
            var cxx = bx + pad + i * (cellW + gap), cyy = by + pad, chh = bh - pad * 2;
            roundRect(g, cxx, cyy, cellW, chh, 8);
            if (i < target) { g.fillStyle = cellColor(i); }
            else { g.fillStyle = '#E4EBE2'; }
            g.fill();
            g.lineWidth = 2; g.strokeStyle = 'rgba(30,43,36,.22)'; g.stroke();
        }

        // 匹配度数字
        g.font = bold(96); g.fillStyle = '#F5F8F2';
        g.fillText(res.topPct + '%', cx, 420);
        g.font = reg(30, 700); g.fillStyle = '#FFC107';
        g.fillText('匹配度 · 电量 ' + target + '/' + CELL_TOTAL + ' 格', cx, 466);

        // 人格称号
        g.font = reg(30, 700); g.fillStyle = '#A8D8B4';
        g.fillText('我的剩余电量人格是', cx, 540);
        g.font = bold(92); g.fillStyle = '#F5F8F2';
        var nameTxt = top.name;
        if (g.measureText(nameTxt).width > maxW) g.font = bold(Math.floor(92 * maxW / g.measureText(nameTxt).width));
        g.fillText(nameTxt, cx, 634);
        g.font = reg(29, 700); g.fillStyle = top.color;
        g.fillText(top.epithet, cx, 682);

        // 3 个人设标签：居中一行，放不下时自动缩字号
        var gapT = 16, tags = top.tags, tfont = 30, tw, total;
        function measureTags(fs) {
            g.font = bold(fs);
            var w = tags.map(function (t) { return g.measureText(t).width + 36; });
            return { w: w, sum: w.reduce(function (a, b) { return a + b; }, 0) + gapT * (tags.length - 1) };
        }
        var m = measureTags(tfont);
        while (m.sum > maxW && tfont > 19) { tfont -= 2; m = measureTags(tfont); }
        tw = m.w; total = m.sum;
        var ty = 726, rx = cx - total / 2;
        tags.forEach(function (t, i2) {
            roundRect(g, rx, ty, tw[i2], 56, 28);
            g.fillStyle = 'rgba(245,248,242,.1)'; g.fill();
            g.lineWidth = 3; g.strokeStyle = 'rgba(245,248,242,.65)'; g.stroke();
            g.fillStyle = '#F5F8F2';
            g.fillText(t, rx + tw[i2] / 2, ty + 37);
            rx += tw[i2] + gapT;
        });

        // 金句（粉笔话，最多 2 行防撞署名）
        var qTop = 830;
        roundRect(g, cx - maxW / 2, qTop, maxW, 172, 14);
        g.fillStyle = 'rgba(35,57,47,.85)'; g.fill();
        g.lineWidth = 3; g.strokeStyle = 'rgba(245,248,242,.4)'; g.stroke();
        g.fillStyle = '#F5F8F2';
        var qfont = 37;
        g.font = reg(qfont, 800);
        var qlines = wrapLines(g, '「' + top.quote + '」', maxW - 70);
        while (qlines.length > 2 && qfont > 28) {
            qfont -= 2;
            g.font = reg(qfont, 800);
            qlines = wrapLines(g, '「' + top.quote + '」', maxW - 70);
        }
        qlines = qlines.slice(0, 2);
        var qy = qTop + 66;
        qlines.forEach(function (ln) { g.fillText(ln, cx, qy); qy += 48; });
        g.font = reg(25, 700); g.fillStyle = '#A8D8B4';
        g.fillText('—— ' + top.name + ' 的口头禅', cx, qTop + 150);

        // 钩子文案（规格动态格数句，完整不截断；缩字号保证 ≤2 行，与二维码白底 y=1104 保持 ≥12px）
        g.fillStyle = '#FFC107';
        var hookTxt = '我测出来是【' + top.name + '】，还剩 ' + target + ' 格电，谁来给我快充？';
        var hfont = 31;
        g.font = bold(hfont);
        var hook = wrapLines(g, hookTxt, maxW);
        while (hook.length > 2 && hfont > 20) {
            hfont -= 2;
            g.font = bold(hfont);
            hook = wrapLines(g, hookTxt, maxW);
        }
        hook = hook.slice(0, 2); // 上面缩字号已保证 ≤2 行，此处仅兜底
        var hy = 1038;
        hook.forEach(function (ln) { g.fillText(ln, cx, hy); hy += 42; });

        // 底部二维码（共享封装，黑板墨绿主题色）
        var qr = makeQrCanvas(url, 144, '#2E4A3F');
        var qx = 96, qy2 = 1116;
        roundRect(g, qx - 12, qy2 - 12, 168, 168, 14);
        g.fillStyle = '#ffffff'; g.fill();
        if (qr) {
            g.imageSmoothingEnabled = false;
            g.drawImage(qr, qx, qy2, 144, 144);
            g.imageSmoothingEnabled = true;
            g.textAlign = 'left';
            g.font = bold(29); g.fillStyle = '#F5F8F2';
            g.fillText('扫码测一测', qx + 196, qy2 + 62);
            g.font = reg(23, 700); g.fillStyle = '#A8D8B4';
            g.fillText('你的教师电量还剩几格？', qx + 196, qy2 + 102);
        }
        g.textAlign = 'center';
        g.font = reg(22); g.fillStyle = '#9FC6A9';
        g.fillText('仅供娱乐参考 · 不采集任何个人信息', cx, 1312);

        return c.toDataURL('image/png');
    }

    var sharePrevFocus = null;

    function openShare() {
        var modal = $('tb-share');
        var img = $('tb-share-img');
        var hint = $('tb-share-hint');
        sharePrevFocus = document.activeElement;
        modal.classList.add('is-on');
        $('tb-share-close').focus();
        hint.textContent = '正在生成…';
        img.removeAttribute('src');

        var url = window.location.href.split('#')[0];
        var ready = (document.fonts && document.fonts.ready)
            ? Promise.race([document.fonts.ready, new Promise(function (r) { setTimeout(r, 1200); })])
            : Promise.resolve();

        ready.then(function () {
            var dataUrl;
            try { dataUrl = drawShareCard(state.lastResult, url); }
            catch (e) { hint.textContent = '生成失败了，请刷新后再试一次'; return; }
            if (!dataUrl) { hint.textContent = '生成失败了，请刷新后再试一次'; return; }
            img.src = dataUrl;
            var wx = /MicroMessenger/i.test(navigator.userAgent || '');
            hint.textContent = wx ? '长按上方图片保存到相册' : '长按 / 右键图片即可保存';
        });
    }

    function closeShare() {
        $('tb-share').classList.remove('is-on');
        if (sharePrevFocus && sharePrevFocus.focus) sharePrevFocus.focus();
        sharePrevFocus = null;
    }

    /* ---------------- 绑定 ---------------- */
    $('tb-start').addEventListener('click', startOver);
    $('tb-back').addEventListener('click', back);
    $('tb-again-btn').addEventListener('click', startOver);
    $('tb-share-btn').addEventListener('click', openShare);
    $('tb-share-close').addEventListener('click', closeShare);
    $('tb-share-ok').addEventListener('click', closeShare);
    $('tb-share').addEventListener('click', function (e) { if (e.target === this) closeShare(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeShare();
    });

    // 答完 8 题 → 计算 + 渲染结果
    function finish() {
        renderResult(computeResult());
    }

    renderLanding();
})();
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
