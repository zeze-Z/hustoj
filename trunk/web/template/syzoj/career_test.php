<?php
$show_title="测一测你该不该考编 - $OJ_NAME";
// 传播型 H5 极简壳：隐藏 OJ 顶栏与页脚主体（header.php/footer.php 按 $hide_chrome 分支，保留骨架+限流+极简署名）
$hide_chrome = true;
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
// 页面无表单、无入库、无用户态数据：仅注入站点名给分享卡片文案（JSON 十六进制转义防注入）
$cs_json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<style>
/* ===== 测一测你该不该考编（前缀 cs-）解忧杂货铺 / 手写便签风，移动端 375px 起 ===== */
:root{
    --cs-paper:#F4F1EA; --cs-note:#FFFDF6; --cs-ink:#28282A; --cs-ink-soft:#6B6659;
    --cs-pen:#2F5DA8; --cs-stamp:#C8402F; --cs-sticky:#F7D774; --cs-mint:#7FB8A2;
    --cs-tape:#B9B2A6; --cs-line:#E5DFD1;
    --cs-accent:#C8402F; --cs-accent-fg:#FFF6EF;
    --cs-body:"PingFang SC","Microsoft YaHei",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}
.cs-page{
    max-width:560px; margin:0 auto; padding:18px 14px 46px;
    min-height:calc(100vh - 48px); /* 极简壳后仅剩署名行高度 */
    color:var(--cs-ink); font-family:var(--cs-body); line-height:1.65;
    background-color:var(--cs-paper);
    background-image:repeating-linear-gradient(transparent 0 31px, rgba(40,40,42,.055) 31px 32px);
}
.cs-page *{box-sizing:border-box}
.cs-state{display:none}
.cs-state.is-on{display:block}
.cs-card{background:var(--cs-note);border:1.5px solid rgba(40,40,42,.75);border-radius:14px;
    box-shadow:0 3px 0 rgba(40,40,42,.14), 0 10px 24px rgba(40,40,42,.07)}
.cs-h3{
    position:relative; display:inline-block; margin:0 0 10px; padding-left:16px;
    font-size:1.06rem; font-weight:800; color:var(--cs-ink); letter-spacing:.02em;
}
.cs-h3::before{
    content:""; position:absolute; left:0; top:.32em; width:8px; height:8px;
    background:var(--cs-stamp); transform:rotate(12deg);
}
.cs-btn{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;min-height:52px;padding:10px 16px;border-radius:12px;
    border:2px solid var(--cs-ink);font-family:var(--cs-body);font-weight:800;font-size:1.02rem;
    cursor:pointer;text-decoration:none;transition:transform .12s ease, box-shadow .12s ease;
}
.cs-btn:active{transform:translate(2px,2px);box-shadow:none!important}
.cs-btn:focus-visible,a.cs-btn:focus-visible{outline:3px solid var(--cs-pen);outline-offset:3px}
.cs-btn--go{background:var(--cs-stamp);color:#FFF6EF;font-size:1.16rem;box-shadow:0 4px 0 #8E2A1D}
.cs-btn--mint{background:var(--cs-mint);color:#16352B;box-shadow:0 4px 0 #5A8F7C}
.cs-btn--ghost{background:var(--cs-note);color:var(--cs-ink);box-shadow:0 3px 0 rgba(40,40,42,.3)}

/* ---- 落地页：胶带贴住的便签 ---- */
.cs-cover{position:relative;padding:30px 20px 24px;transform:rotate(-.7deg)}
.cs-cover::before{
    content:"";position:absolute;top:-15px;left:50%;width:132px;height:32px;
    margin-left:-66px;background:rgba(185,178,166,.8);
    border-left:1px dashed rgba(40,40,42,.25);border-right:1px dashed rgba(40,40,42,.25);
    transform:rotate(-2.5deg);box-shadow:0 2px 5px rgba(40,40,42,.14);
}
.cs-cover-kicker{margin:0 0 8px;font-size:.88rem;font-weight:700;color:var(--cs-pen);letter-spacing:.04em}
.cs-title{
    font-weight:800;font-size:clamp(1.85rem,8.2vw,2.4rem);
    line-height:1.3;margin:0;color:var(--cs-ink);letter-spacing:.01em;
}
.cs-title mark{background:linear-gradient(transparent 52%, var(--cs-sticky) 52%);color:inherit;padding:0 .06em}
.cs-sub{margin:12px 0 0;font-size:.98rem;color:var(--cs-ink-soft)}
.cs-count{
    display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;
    margin:18px 0 4px;padding:12px 16px;border:1.5px dashed var(--cs-tape);
    border-radius:10px;background:#FBF8EF;font-weight:700;color:var(--cs-ink);
}
.cs-count b{
    font-weight:800;font-size:2.5rem;line-height:1;color:var(--cs-stamp);letter-spacing:.01em;
}
.cs-avatars{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0 4px;padding:0;list-style:none}
.cs-avatars li{
    display:flex;align-items:center;gap:6px;padding:5px 11px 5px 7px;
    background:var(--cs-note);border:1.5px solid rgba(40,40,42,.7);border-radius:8px;
    font-size:.85rem;font-weight:800;box-shadow:0 2px 0 rgba(40,40,42,.16);
}
.cs-avatars li:nth-child(odd){transform:rotate(-1.8deg)}
.cs-avatars li:nth-child(even){transform:rotate(1.5deg)}
.cs-avatars li i{
    display:grid;place-items:center;width:24px;height:24px;border-radius:6px;
    border:1.5px solid rgba(40,40,42,.5);font-style:normal;font-size:.9rem;
}
.cs-meta{margin:12px 0 0;text-align:center;font-size:.86rem;color:var(--cs-ink-soft)}
.cs-start{margin-top:16px}
.cs-landing-note{margin:14px 0 0;text-align:center;font-size:.8rem;color:var(--cs-ink-soft)}

/* ---- 答题页 ---- */
.cs-qbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px}
.cs-dots{display:flex;gap:6px;flex:1}
.cs-dots span{flex:1;height:7px;border-radius:99px;background:#E3DCC9;transition:background .2s ease}
.cs-dots span.is-done{background:var(--cs-ink)}
.cs-dots span.is-cur{background:var(--cs-sticky);box-shadow:0 0 0 2px rgba(40,40,42,.25)}
.cs-qnum{font-size:.85rem;font-weight:800;color:var(--cs-ink-soft);white-space:nowrap}
.cs-back{
    background:none;border:none;padding:10px 8px;min-height:44px;font:inherit;font-size:.88rem;font-weight:700;
    color:var(--cs-pen);cursor:pointer;text-decoration:underline;
}
.cs-back:focus-visible{outline:3px solid var(--cs-pen);outline-offset:2px;border-radius:6px}
.cs-qcard{padding:20px 16px 18px}
.cs-qcard.is-in{animation:cs-slide .26s ease both}
@keyframes cs-slide{from{opacity:0;transform:translateX(22px)}to{opacity:1;transform:none}}
.cs-qscene{
    display:inline-block;padding:3px 2px;font-size:.82rem;font-weight:800;color:var(--cs-pen);
    border-bottom:2px dashed rgba(47,93,168,.5);
}
.cs-qtitle{
    font-weight:800;font-size:1.32rem;line-height:1.5;
    margin:12px 0 16px;color:var(--cs-ink);
}
.cs-opts{display:flex;flex-direction:column;gap:12px;margin:0;padding:0;list-style:none}
.cs-opt{
    display:flex;align-items:center;gap:12px;width:100%;min-height:56px;
    padding:12px 14px;text-align:left;background:var(--cs-note);color:var(--cs-ink);
    border:1.5px solid rgba(40,40,42,.45);border-radius:10px;
    font:inherit;font-size:.98rem;font-weight:700;line-height:1.5;cursor:pointer;
    transition:border-color .15s ease, background .15s ease, transform .12s ease;
}
.cs-opt:hover{border-color:var(--cs-ink);background:#fff}
.cs-opt:focus-visible{outline:3px solid var(--cs-pen);outline-offset:2px}
.cs-opt.is-picked{
    border-color:var(--cs-stamp);background:#FCF3E9;transform:translate(2px,2px);
    box-shadow:0 2px 0 rgba(40,40,42,.2);
}
.cs-opt b{
    flex:0 0 auto;display:grid;place-items:center;width:30px;height:30px;border-radius:8px;
    border:2px solid var(--cs-pen);color:var(--cs-pen);font-weight:800;font-size:.98rem;
    background:#EDF2FB;
}
.cs-opt.is-picked b{border-color:var(--cs-stamp);color:#FFF6EF;background:var(--cs-stamp)}
.cs-qfoot{margin:14px 0 0;text-align:center;font-size:.82rem;color:var(--cs-ink-soft)}

/* ---- 结果页 ---- */
.cs-block{margin-bottom:18px}
.cs-hero{position:relative;display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap}
.cs-ring-wrap{position:relative;width:158px;text-align:center}
.cs-ring{width:158px;height:158px;display:block;transform:rotate(-90deg)}
.cs-ring circle{fill:none;stroke-width:13;stroke-linecap:round}
.cs-ring .cs-ring-bg{stroke:#E7E1D3}
.cs-ring .cs-ring-arc{stroke:var(--cs-accent)}
.cs-ring-num{
    position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;
}
.cs-ring-num span{font-weight:800;font-size:2.7rem;line-height:1;color:var(--cs-ink)}
.cs-ring-num i{font-style:normal;font-size:.86rem;font-weight:800;color:var(--cs-ink-soft)}
.cs-ring-cap{margin:6px 0 0;font-size:.85rem;font-weight:800;color:var(--cs-ink-soft);text-align:center}
/* 红印章立场徽章：全页唯一重锤，盖下一击 */
.cs-seal{
    display:grid;place-items:center;width:116px;height:116px;padding:6px;
    border:4px solid var(--cs-stamp);border-radius:12px;
    box-shadow:inset 0 0 0 3px var(--cs-note), inset 0 0 0 5.5px var(--cs-stamp);
    color:var(--cs-stamp);background:rgba(200,64,47,.05);
    font-weight:800;font-size:1.5rem;letter-spacing:.14em;line-height:1.1;text-align:center;
    transform:rotate(-9deg);opacity:.94;
}
.cs-seal.is-slam{animation:cs-slam .55s cubic-bezier(.18,1.5,.4,1) both}
@keyframes cs-slam{
    0%{transform:rotate(-9deg) scale(2.7);opacity:0}
    55%{opacity:1}
    100%{transform:rotate(-9deg) scale(1);opacity:.94}
}
.cs-identity{text-align:center;padding:18px 16px 20px}
.cs-avatar{
    width:74px;height:74px;margin:0 auto 10px;border-radius:16px;display:grid;place-items:center;
    font-size:2.2rem;background:var(--cs-accent);border:2px solid var(--cs-ink);
    box-shadow:0 4px 0 rgba(40,40,42,.35);transform:rotate(-4deg);
}
.cs-lead{margin:0;font-size:.95rem;font-weight:700;color:var(--cs-ink-soft)}
.cs-name{
    font-weight:800;font-size:2.15rem;line-height:1.25;
    margin:2px 0 4px;color:var(--cs-ink);letter-spacing:.02em;
}
.cs-epithet{margin:0;font-size:1rem;font-weight:700;color:var(--cs-accent)}
.cs-tags,.cs-pills{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin:0;padding:0;list-style:none}
.cs-tags{margin-bottom:10px}
.cs-tag{
    display:inline-block;padding:6px 14px;border-radius:8px;background:var(--cs-note);
    color:var(--cs-ink);border:2px solid var(--cs-accent);font-weight:800;font-size:.92rem;
    box-shadow:0 3px 0 rgba(40,40,42,.16);
}
.cs-tags li:nth-child(1){transform:rotate(-2deg)}
.cs-tags li:nth-child(2){transform:rotate(1.5deg)}
.cs-tags li:nth-child(3){transform:rotate(-1deg)}
.cs-pill{
    display:inline-block;padding:5px 12px;border:1.5px dashed var(--cs-tape);
    border-radius:999px;color:var(--cs-ink-soft);font-size:.85rem;font-weight:700;background:#FBF8EF;
}
/* 金句：贴在墙上的便签 */
.cs-quote{
    position:relative;padding:22px 18px 16px;background:#FBE7A8;border-radius:6px;
    border:1.5px solid rgba(40,40,42,.55);box-shadow:0 6px 16px rgba(40,40,42,.14);
    transform:rotate(-.6deg);overflow:hidden;
}
.cs-quote::before{
    content:"";position:absolute;top:-12px;left:50%;width:104px;height:28px;margin-left:-52px;
    background:rgba(185,178,166,.85);transform:rotate(2deg);
}
.cs-quote blockquote{
    position:relative;margin:0;font-weight:800;font-size:1.24rem;line-height:1.65;color:var(--cs-ink);
}
.cs-quote cite{display:block;margin-top:8px;font-style:normal;font-size:.85rem;font-weight:700;color:#7A6A33;text-align:right}
/* 理由：横线信纸 */
.cs-reason{
    padding:14px 16px;border:1.5px solid rgba(40,40,42,.4);border-radius:10px;
    background:repeating-linear-gradient(var(--cs-note) 0 31px, var(--cs-line) 31px 32px);
    font-size:.96rem;line-height:32px;color:#3A3A3C;
}
.cs-radar{width:100%;height:auto;display:block}
.cs-second{
    display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:12px;
    background:var(--cs-note);border:1.5px dashed rgba(40,40,42,.6);
    box-shadow:0 3px 0 rgba(40,40,42,.12);
}
.cs-second-emoji{
    flex:0 0 auto;width:50px;height:50px;border-radius:12px;display:grid;place-items:center;
    font-size:1.5rem;border:2px solid rgba(40,40,42,.7);
}
.cs-second-body{flex:1;min-width:0}
.cs-second-body p{margin:0}
.cs-second-label{font-size:.8rem;font-weight:800;color:var(--cs-pen)}
.cs-second-name{font-weight:800;font-size:1.24rem;color:var(--cs-ink)}
.cs-second-desc{font-size:.85rem;color:var(--cs-ink-soft)}
.cs-second-pct{flex:0 0 auto;font-weight:800;font-size:1.45rem;color:var(--cs-stamp)}
.cs-slogan{
    margin:0 0 12px;padding:13px 15px;border-radius:10px;
    background:var(--cs-ink);color:#FBF8EF;font-weight:800;font-size:1.02rem;
    text-align:center;letter-spacing:.02em;transform:rotate(-.4deg);
}
.cs-links{display:flex;flex-direction:column;gap:10px;margin:0;padding:0;list-style:none}
.cs-link{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;
    background:var(--cs-note);border:1.5px solid rgba(40,40,42,.35);
    text-decoration:none;color:var(--cs-ink);
    transition:border-color .15s ease, transform .12s ease;
}
.cs-link:hover{border-color:var(--cs-ink);text-decoration:none;transform:translateY(-2px)}
.cs-link:focus-visible{outline:3px solid var(--cs-pen);outline-offset:2px}
.cs-link-ico{
    flex:0 0 auto;width:42px;height:42px;border-radius:10px;display:grid;place-items:center;
    border:2px solid rgba(40,40,42,.7);
}
.cs-link-ico svg{width:22px;height:22px}
.cs-link-t{font-weight:800;font-size:.98rem}
.cs-link-d{font-size:.82rem;color:var(--cs-ink-soft);line-height:1.45}
.cs-link-chev{margin-left:auto;color:var(--cs-ink-soft);flex:0 0 auto}
.cs-login-hint{
    margin:12px 0 0;padding:11px 14px;border-radius:10px;background:#FBF8EF;
    border:1.5px dashed var(--cs-tape);font-size:.88rem;font-weight:700;color:var(--cs-ink-soft);text-align:center;
}
.cs-login-hint a{color:var(--cs-pen);font-weight:800}
.cs-actions{display:flex;flex-direction:column;gap:10px}
.cs-note{margin:16px 0 0;text-align:center;font-size:.78rem;color:var(--cs-ink-soft)}

/* ---- 分享卡片弹层 ---- */
.cs-modal{
    position:fixed;inset:0;z-index:2000;display:none;align-items:flex-start;justify-content:center;
    padding:24px 14px;overflow-y:auto;background:rgba(40,40,42,.6);
}
.cs-modal.is-on{display:flex}
.cs-modal-box{
    width:100%;max-width:420px;background:var(--cs-paper);border:1.5px solid var(--cs-ink);
    border-radius:14px;box-shadow:0 10px 34px rgba(0,0,0,.35);padding:14px 14px 18px;
}
.cs-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.cs-modal-head span{font-weight:800;font-size:1.1rem;color:var(--cs-ink)}
.cs-x{
    width:44px;height:44px;border-radius:10px;border:2px solid var(--cs-ink);background:var(--cs-note);
    font-size:1.3rem;line-height:1;color:var(--cs-ink);cursor:pointer;
}
.cs-x:focus-visible{outline:3px solid var(--cs-pen);outline-offset:2px}
.cs-share-img{display:block;width:100%;height:auto;border-radius:10px;border:1.5px solid rgba(40,40,42,.7);background:#fff}
.cs-modal-hint{margin:10px 0 12px;text-align:center;font-size:.86rem;font-weight:700;color:var(--cs-ink-soft)}

@media (min-width: 640px){
    .cs-page{padding-top:26px}
    .cs-cover{padding:36px 32px 30px}
}

@media (prefers-reduced-motion: reduce){
    .cs-page *,.cs-page *::before,.cs-page *::after{
        animation-duration:.001s!important;animation-iteration-count:1!important;transition-duration:.001s!important;
    }
}
</style>

<div class="cs-page">

    <!-- ============ 状态一：落地页 ============ -->
    <section id="cs-landing" class="cs-state is-on" aria-label="测试介绍">
        <div class="cs-card cs-cover">
            <p class="cs-cover-kicker">解忧杂货铺 · 教师趣味测评</p>
            <h1 class="cs-title">测一测，<br>你该不该<mark>考编</mark>？</h1>
            <p class="cs-sub">8 道职场瞬间，亮出你的职业底牌——该考、别考，还是再想想。</p>
            <p class="cs-count">已有 <b id="cs-total">12653</b> 位老师测过</p>
            <ul class="cs-avatars" id="cs-landing-avatars"></ul>
            <button type="button" class="cs-btn cs-btn--go cs-start" id="cs-start">开始测试</button>
            <p class="cs-meta">8 道题 · 约 1 分钟 · 无需登录</p>
        </div>
        <p class="cs-landing-note">纯属娱乐，结果仅供开心参考</p>
    </section>

    <!-- ============ 状态二：答题页 ============ -->
    <section id="cs-quiz" class="cs-state" aria-label="答题">
        <div class="cs-qbar">
            <div class="cs-dots" id="cs-dots" aria-hidden="true"></div>
            <span class="cs-qnum" id="cs-qnum">1 / 8</span>
        </div>
        <button type="button" class="cs-back" id="cs-back" style="display:none">← 上一题</button>
        <div class="cs-card cs-qcard" id="cs-qcard">
            <span class="cs-qscene" id="cs-qscene"></span>
            <h2 class="cs-qtitle" id="cs-qtitle"></h2>
            <ul class="cs-opts" id="cs-opts"></ul>
        </div>
        <p class="cs-qfoot">凭第一直觉选，别想太久</p>
    </section>

    <!-- ============ 状态三：结果页（顺序固定） ============ -->
    <section id="cs-result" class="cs-state" aria-label="测试结果">

        <!-- 1 红印章立场徽章 + 匹配度环形 -->
        <div class="cs-block cs-hero">
            <div class="cs-ring-wrap">
                <svg class="cs-ring" viewBox="0 0 158 158" aria-hidden="true">
                    <circle class="cs-ring-bg" cx="79" cy="79" r="66"/>
                    <circle class="cs-ring-arc" id="cs-ring-arc" cx="79" cy="79" r="66"/>
                </svg>
                <div class="cs-ring-num"><span id="cs-pct">0</span><i>匹配度 %</i></div>
                <p class="cs-ring-cap">编制人格匹配度</p>
            </div>
            <div class="cs-seal" id="cs-seal" role="img" aria-label="立场印章"></div>
        </div>

        <!-- 2 职业底牌揭晓 -->
        <div class="cs-block cs-card cs-identity">
            <div class="cs-avatar" id="cs-avatar" aria-hidden="true"></div>
            <p class="cs-lead">你的职业底牌是——</p>
            <h2 class="cs-name" id="cs-name"></h2>
            <p class="cs-epithet" id="cs-epithet"></p>
        </div>

        <!-- 3 人设标签 + 关键词胶囊 -->
        <div class="cs-block">
            <ul class="cs-tags" id="cs-tags"></ul>
            <ul class="cs-pills" id="cs-pills"></ul>
        </div>

        <!-- 4 金句（被允许感） -->
        <div class="cs-block cs-quote">
            <blockquote id="cs-quote"></blockquote>
            <cite id="cs-quote-by"></cite>
        </div>

        <!-- 5 个性化理由 -->
        <div class="cs-block">
            <h3 class="cs-h3">杂货店长给你的理由</h3>
            <div class="cs-reason" id="cs-reason"></div>
        </div>

        <!-- 6 五维雷达 -->
        <div class="cs-block">
            <h3 class="cs-h3">你的五维职业倾向</h3>
            <svg class="cs-radar" id="cs-radar" viewBox="0 0 320 258" role="img" aria-label="五维职业倾向雷达图"></svg>
        </div>

        <!-- 7 第二人格 -->
        <div class="cs-block">
            <div class="cs-second">
                <div class="cs-second-emoji" id="cs-second-emoji" aria-hidden="true"></div>
                <div class="cs-second-body">
                    <p class="cs-second-label">你的隐藏第二人格</p>
                    <p class="cs-second-name" id="cs-second-name"></p>
                    <p class="cs-second-desc" id="cs-second-desc">换个答法，TA 可能会反超</p>
                </div>
                <div class="cs-second-pct" id="cs-second-pct"></div>
            </div>
        </div>

        <!-- 8 转化区 -->
        <div class="cs-block">
            <p class="cs-slogan">无论考不考编，先把眼前这届学生教好</p>
            <ul class="cs-links">
                <li>
                    <a class="cs-link" href="timetable.php">
                        <span class="cs-link-ico" style="background:#FBE7A8">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="10" y="16" width="44" height="38" rx="5" fill="none" stroke="#28282A" stroke-width="5"/>
                                <line x1="10" y1="28" x2="54" y2="28" stroke="#28282A" stroke-width="5"/>
                                <line x1="24" y1="11" x2="24" y2="20" stroke="#28282A" stroke-width="5" stroke-linecap="round"/>
                                <line x1="40" y1="11" x2="40" y2="20" stroke="#28282A" stroke-width="5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="cs-link-t">课程表生成器</span><br>
                            <span class="cs-link-d">挑个主题模板，一键生成可打印课表</span>
                        </span>
                        <svg class="cs-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="cs-link" href="course.php">
                        <span class="cs-link-ico" style="background:#D9EBE2">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <path d="M10 14 h18 a6 6 0 0 1 6 6 v30 a6 6 0 0 0 -6 -6 H10 Z" fill="none" stroke="#28282A" stroke-width="5" stroke-linejoin="round"/>
                                <path d="M54 14 H36 a6 6 0 0 0 -6 6 v30 a6 6 0 0 1 6 -6 h18 Z" fill="none" stroke="#28282A" stroke-width="5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="cs-link-t">课件库</span><br>
                            <span class="cs-link-d">现成课件直接拿去上课，省下备课时间</span>
                        </span>
                        <svg class="cs-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="cs-link" href="more.php#games">
                        <span class="cs-link-ico" style="background:#E3E0F2">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="8" y="20" width="48" height="28" rx="12" fill="none" stroke="#28282A" stroke-width="5"/>
                                <line x1="18" y1="29" x2="18" y2="39" stroke="#28282A" stroke-width="5" stroke-linecap="round"/>
                                <line x1="13" y1="34" x2="23" y2="34" stroke="#28282A" stroke-width="5" stroke-linecap="round"/>
                                <circle cx="44" cy="31" r="3.4" fill="#28282A"/>
                                <circle cx="51" cy="38" r="3.4" fill="#28282A"/>
                            </svg>
                        </span>
                        <span>
                            <span class="cs-link-t">课前小游戏</span><br>
                            <span class="cs-link-d">上课前热个身，学生立刻安静下来</span>
                        </span>
                        <svg class="cs-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
            </ul>
            <p class="cs-login-hint"><a href="loginpage.php">登录</a> 解锁更多教学工具（课程管理、作业布置…）</p>
        </div>

        <!-- 9 操作区 -->
        <div class="cs-block cs-actions">
            <button type="button" class="cs-btn cs-btn--mint" id="cs-share-btn">生成分享卡片</button>
            <button type="button" class="cs-btn cs-btn--ghost" id="cs-again-btn">重新测试</button>
        </div>

        <!-- 10 免责 -->
        <p class="cs-note">仅供娱乐参考 · 本测试不采集任何个人信息</p>
    </section>
</div>

<!-- ============ 状态四：分享卡片预览 ============ -->
<div class="cs-modal" id="cs-share" role="dialog" aria-modal="true" aria-label="分享卡片预览">
    <div class="cs-modal-box">
        <div class="cs-modal-head">
            <span>你的分享卡片</span>
            <button type="button" class="cs-x" id="cs-share-close" aria-label="关闭">&times;</button>
        </div>
        <img class="cs-share-img" id="cs-share-img" alt="考编决策分享卡片">
        <p class="cs-modal-hint" id="cs-share-hint">正在生成…</p>
        <button type="button" class="cs-btn cs-btn--ghost" id="cs-share-ok">完成</button>
    </div>
</div>

<script>
window.CTS = {
    siteName: <?php echo json_encode($OJ_NAME, $cs_json_flags); ?>
};
</script>
<!-- 通关礼花：canvas-confetti CDN + 项目公共 game_confetti.js（CDN 失败则静默降级） -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/game_confetti.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qrcode.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/career_test_data.js"></script>
<script>
(function () {
    'use strict';
    var D = window.CT_DATA;
    if (!D) return;
    var DIM_KEYS = D.DIMENSIONS.map(function (d) { return d.key; });
    var LETTERS = ['A', 'B', 'C', 'D'];
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var state = { mode: 'landing', qi: 0, answers: [], locked: false, lastResult: null };

    var $ = function (id) { return document.getElementById(id); };

    /* ---------------- 状态机 ---------------- */
    function show(mode) {
        state.mode = mode;
        ['landing', 'quiz', 'result'].forEach(function (m) {
            $('cs-' + m).classList.toggle('is-on', m === mode);
        });
        var page = document.querySelector('.cs-page');
        window.scrollTo(0, page ? Math.max(0, page.offsetTop - 70) : 0);
    }

    /* ---------------- 落地页 ---------------- */
    function renderLanding() {
        $('cs-total').textContent = D.MATCHED_TOTAL;
        var ul = $('cs-landing-avatars');
        ul.innerHTML = '';
        D.PERSONAS.forEach(function (p) {
            var li = document.createElement('li');
            var i = document.createElement('i');
            i.style.background = p.color;
            i.textContent = p.emoji;
            li.appendChild(i);
            li.appendChild(document.createTextNode(p.name + ' · ' + p.seal));
            ul.appendChild(li);
        });
    }

    /* ---------------- 答题页 ---------------- */
    function renderQuiz(animate) {
        var q = D.QUESTIONS[state.qi];
        $('cs-qnum').textContent = (state.qi + 1) + ' / ' + D.QUESTIONS.length;
        $('cs-back').style.display = state.qi > 0 ? '' : 'none';

        var dots = $('cs-dots');
        dots.innerHTML = '';
        D.QUESTIONS.forEach(function (_, i) {
            var s = document.createElement('span');
            if (i < state.qi) s.className = 'is-done';
            if (i === state.qi) s.className = 'is-cur';
            dots.appendChild(s);
        });

        $('cs-qscene').textContent = '场景 · ' + q.scene;
        $('cs-qtitle').textContent = q.title;

        var ul = $('cs-opts');
        ul.innerHTML = '';
        q.options.forEach(function (opt, i) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cs-opt';
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

        var card = $('cs-qcard');
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

        // 个性化理由：得分最高 2~3 维度（不足 80 字则升到 3 维）
        var order = DIM_KEYS.slice().sort(function (a, b) { return raw[b] - raw[a]; });
        var useN = (raw[order[2]] > 0 && raw[order[2]] >= raw[order[0]] * 0.35) ? 3 : 2;
        var parts = [top.desc.intro];
        order.slice(0, useN).forEach(function (dim) {
            var vs = top.desc[dim];
            if (vs && vs.length) parts.push(vs[Math.floor(Math.random() * vs.length)]);
        });
        parts.push(top.desc.outro);
        var reason = parts.join('');
        if (reason.length < 80 && useN === 2 && raw[order[2]] > 0) {
            parts = [top.desc.intro];
            order.slice(0, 3).forEach(function (dim) {
                var vs = top.desc[dim];
                if (vs && vs.length) parts.push(vs[Math.floor(Math.random() * vs.length)]);
            });
            parts.push(top.desc.outro);
            reason = parts.join('');
        }

        return {
            raw: raw, sims: sims, top: top, second: second,
            topPct: sims[0].pct, secondPct: sims[1].pct, reason: reason
        };
    }

    /* ---------------- 结果页 ---------------- */
    function renderResult(res) {
        state.lastResult = res;
        var top = res.top;
        var page = document.querySelector('.cs-page');
        page.style.setProperty('--cs-accent', top.color);
        page.style.setProperty('--cs-accent-fg', top.fg);

        // 环形
        var arc = $('cs-ring-arc');
        var C = 2 * Math.PI * 66;
        arc.style.strokeDasharray = C.toFixed(2);
        arc.style.strokeDashoffset = C.toFixed(2);

        // 印章（盖下一击，一次）
        var seal = $('cs-seal');
        seal.textContent = top.seal;
        seal.classList.remove('is-slam');
        void seal.offsetWidth;
        seal.classList.add('is-slam');

        // 底牌
        $('cs-avatar').textContent = top.emoji;
        $('cs-name').textContent = top.name;
        $('cs-epithet').textContent = top.epithet;

        // 标签 / 胶囊
        fillList('cs-tags', 'cs-tag', top.tags);
        fillList('cs-pills', 'cs-pill', top.pills);

        // 金句
        $('cs-quote').textContent = top.quote;
        $('cs-quote-by').textContent = '—— ' + top.name + ' 的口头禅';

        // 理由
        $('cs-reason').textContent = res.reason;

        // 雷达
        renderRadar(res.raw, top.color);

        // 第二人格
        $('cs-second-emoji').textContent = res.second.emoji;
        $('cs-second-emoji').style.background = res.second.color;
        $('cs-second-name').textContent = res.second.name;
        $('cs-second-pct').textContent = res.secondPct + '%';
        // 同分时避免"TA 可能会反超"与相同百分比自相矛盾
        $('cs-second-desc').textContent = (res.secondPct >= res.topPct)
            ? '和你不相上下，换个答法见分晓' : '换个答法，TA 可能会反超';

        show('result');
        revealRing(res.sims[0].pct);
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

    var csRingRaf = 0;
    function revealRing(pct) {
        var arc = $('cs-ring-arc');
        var num = $('cs-pct');
        var C = 2 * Math.PI * 66;
        var target = C * (1 - pct / 100);
        if (csRingRaf) { cancelAnimationFrame(csRingRaf); csRingRaf = 0; } // 重测速进结果页时取消旧动画
        if (reduceMotion) {
            arc.style.strokeDashoffset = target.toFixed(2);
            num.textContent = pct;
            return;
        }
        // 单次编排：数字与环形同步 900ms 揭晓
        var start = null, dur = 900;
        function step(ts) {
            if (start === null) start = ts;
            var t = Math.min(1, (ts - start) / dur);
            var e = 1 - Math.pow(1 - t, 3);
            arc.style.strokeDashoffset = (C * (1 - (pct * e) / 100)).toFixed(2);
            num.textContent = Math.round(pct * e);
            if (t < 1) csRingRaf = requestAnimationFrame(step);
            else { num.textContent = pct; csRingRaf = 0; }
        }
        arc.style.strokeDashoffset = C.toFixed(2);
        csRingRaf = requestAnimationFrame(step);
    }

    /* ---------------- 五维雷达（SVG） ---------------- */
    function renderRadar(raw, color) {
        var svg = $('cs-radar');
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
        // 网格三层
        [0.34, 0.67, 1].forEach(function (f) {
            var pts = [];
            for (var i = 0; i < n; i++) pts.push(R * f);
            out.push('<polygon points="' + poly(pts) + '" fill="none" stroke="#D8D2C4" stroke-width="1.5"/>');
        });
        // 轴线
        for (var i = 0; i < n; i++) {
            var p = pt(i, R);
            out.push('<line x1="' + cx + '" y1="' + cy + '" x2="' + p[0].toFixed(1) + '" y2="' + p[1].toFixed(1) + '" stroke="#D8D2C4" stroke-width="1.5"/>');
        }
        // 数据多边形（最小 0.16 半径，避免零值塌成点）
        var dataR = DIM_KEYS.map(function (k) { return Math.max(0.16, raw[k] / max) * R; });
        out.push('<polygon points="' + poly(dataR) + '" fill="' + color + '" fill-opacity=".28" stroke="' + color + '" stroke-width="3" stroke-linejoin="round"/>');
        for (i = 0; i < n; i++) {
            var q = pt(i, dataR[i]);
            out.push('<circle cx="' + q[0].toFixed(1) + '" cy="' + q[1].toFixed(1) + '" r="5" fill="#fff" stroke="' + color + '" stroke-width="3"/>');
        }
        // 轴标签
        for (i = 0; i < n; i++) {
            var lp = pt(i, R + 26);
            var anchor = 'middle';
            if (lp[0] > cx + 8) anchor = 'start';
            else if (lp[0] < cx - 8) anchor = 'end';
            var dy = 5;
            if (lp[1] < cy - 40) dy = 12; // 顶标签基线须落回 viewBox 内，防中文字形顶部被裁
            if (lp[1] > cy + 40) dy = 12;
            out.push('<text x="' + lp[0].toFixed(1) + '" y="' + (lp[1] + dy).toFixed(1) + '" text-anchor="' + anchor +
                '" font-size="15" font-weight="700" fill="#28282A" font-family="PingFang SC, Microsoft YaHei, sans-serif">' +
                D.DIMENSIONS[i].label + '</text>');
        }
        svg.innerHTML = out.join('');
    }

    /* ---------------- 分享卡片（Canvas 750×1334） ---------------- */
    function roundRect(g, x, y, w, h, r) {
        g.beginPath();
        g.moveTo(x + r, y);
        g.arcTo(x + w, y, x + w, y + h, r);
        g.arcTo(x + w, y + h, x, y + h, r);
        g.arcTo(x, y + h, x, y, r);
        g.arcTo(x, y, x + w, y, r);
        g.closePath();
    }

    function wrapLines(g, text, maxW) {
        var lines = [], line = '';
        for (var i = 0; i < text.length; i++) {
            var t = line + text[i];
            if (g.measureText(t).width > maxW && line) { lines.push(line); line = text[i]; }
            else line = t;
        }
        if (line) lines.push(line);
        return lines;
    }

    function makeQrCanvas(text, size) {
        try {
            if (typeof QRCode === 'undefined') return null; // qrcode.min.js 缺失时静默跳过
            var holder = document.createElement('div');
            new QRCode(holder, {
                text: text, width: size, height: size,
                colorDark: '#28282A', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            return holder.querySelector('canvas');
        } catch (e) { return null; }
    }

    function drawShareCard(res, url) {
        var W = 750, H = 1334;
        var c = document.createElement('canvas');
        c.width = W; c.height = H;
        var g = c.getContext('2d');
        var top = res.top;
        var bold = function (px) { return '800 ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };
        var reg = function (px, w) { return (w || 400) + ' ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };

        // 纸底 + 横线（便签纸）
        g.fillStyle = '#F4F1EA'; g.fillRect(0, 0, W, H);
        g.strokeStyle = 'rgba(40,40,42,.07)'; g.lineWidth = 1.5;
        for (var y = 34; y < H; y += 38) { g.beginPath(); g.moveTo(0, y); g.lineTo(W, y); g.stroke(); }
        // 便签卡
        roundRect(g, 52, 60, 660, 1214, 26); g.fillStyle = '#FFFDF6'; g.fill();
        g.lineWidth = 4; g.strokeStyle = 'rgba(40,40,42,.85)'; g.stroke();
        // 胶带
        g.save(); g.translate(W / 2, 52); g.rotate(-0.045);
        g.fillStyle = 'rgba(185,178,166,.85)'; g.fillRect(-80, -16, 160, 34); g.restore();

        var cx = W / 2;
        var padL = 92, maxW = 576;

        // 顶部站点名
        g.textAlign = 'center'; g.textBaseline = 'alphabetic';
        g.font = reg(28, 700); g.fillStyle = '#6B6659';
        g.fillText(((window.CTS && window.CTS.siteName) || '') + ' · 考编决策趣味测评', cx, 136);

        // 红印章立场徽章（分享卡的视觉锤）
        var sw = 250, sh = 168, sx = cx - sw / 2, sy = 176;
        g.save();
        g.translate(cx, sy + sh / 2);
        g.rotate(-0.13);
        g.strokeStyle = '#C8402F'; g.fillStyle = 'rgba(200,64,47,.06)';
        roundRect(g, -sw / 2, -sh / 2, sw, sh, 16);
        g.fill(); g.lineWidth = 7; g.stroke();
        roundRect(g, -sw / 2 + 13, -sh / 2 + 13, sw - 26, sh - 26, 9);
        g.lineWidth = 4; g.stroke();
        g.fillStyle = '#C8402F'; g.font = bold(66);
        g.fillText(top.seal, 0, 23);
        g.restore();

        // 人格称号
        g.font = reg(30, 700); g.fillStyle = '#6B6659';
        g.fillText('我的职业底牌是', cx, 412);
        g.font = bold(94);
        g.fillStyle = '#28282A';
        var nameTxt = top.name;
        if (g.measureText(nameTxt).width > maxW) g.font = bold(Math.floor(94 * maxW / g.measureText(nameTxt).width));
        g.fillText(nameTxt, cx, 512);
        g.font = reg(29, 700); g.fillStyle = top.color;
        g.fillText(top.epithet, cx, 560);

        // 匹配度徽章
        var badgeTxt = '编制人格匹配度 ' + res.sims[0].pct + '%';
        g.font = bold(33);
        var bw = g.measureText(badgeTxt).width + 52, bh = 62;
        var bx = cx - bw / 2, by = 596;
        roundRect(g, bx + 4, by + 5, bw, bh, 31); g.fillStyle = 'rgba(40,40,42,.8)'; g.fill();
        roundRect(g, bx, by, bw, bh, 31);
        g.fillStyle = '#F7D774'; g.fill();
        g.lineWidth = 4; g.strokeStyle = '#28282A'; g.stroke();
        g.fillStyle = '#28282A';
        g.fillText(badgeTxt, cx, by + 43);

        // 3 个人设标签：居中一行，放不下时自动缩字号
        var gap = 16, tags = top.tags, tfont = 30, tw, total;
        function measureTags(fs) {
            g.font = bold(fs);
            var w = tags.map(function (t) { return g.measureText(t).width + 36; });
            return { w: w, sum: w.reduce(function (a, b) { return a + b; }, 0) + gap * (tags.length - 1) };
        }
        var m = measureTags(tfont);
        while (m.sum > maxW && tfont > 19) { tfont -= 2; m = measureTags(tfont); }
        tw = m.w; total = m.sum;
        var ty = 706, rx = cx - total / 2;
        tags.forEach(function (t, i) {
            roundRect(g, rx, ty, tw[i], 56, 10);
            g.fillStyle = '#FFFDF6'; g.fill();
            g.lineWidth = 3; g.strokeStyle = top.color; g.stroke();
            g.fillStyle = '#28282A';
            g.fillText(t, rx + tw[i] / 2, ty + 37);
            rx += tw[i] + gap;
        });

        // 金句便签
        var qTop = 800;
        g.save();
        g.translate(cx, qTop + 92); g.rotate(-0.014);
        roundRect(g, -maxW / 2, -92, maxW, 184, 8);
        g.fillStyle = '#FBE7A8'; g.fill();
        g.lineWidth = 3; g.strokeStyle = 'rgba(40,40,42,.6)'; g.stroke();
        g.fillStyle = 'rgba(185,178,166,.9)'; g.fillRect(-66, -106, 132, 30);
        g.restore();
        g.fillStyle = '#28282A';
        // 金句最多 2 行：超了先缩字号，保证不与署名行（qTop+162）基线重合
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
        g.font = reg(25, 700); g.fillStyle = '#7A6A33';
        g.fillText('—— ' + top.name + ' 的口头禅', cx, qTop + 162);

        // 钩子文案
        g.font = bold(31); g.fillStyle = '#C8402F';
        var hook = wrapLines(g, '我测出来是【' + top.name + '】，' + top.seal + '你是哪种？8 道题见分晓', maxW).slice(0, 2);
        var hy = 1032;
        hook.forEach(function (ln) { g.fillText(ln, cx, hy); hy += 44; });

        // 底部二维码
        var qr = makeQrCanvas(url, 142);
        var qx = padL, qy2 = 1096;
        if (qr) {
            g.imageSmoothingEnabled = false;
            g.drawImage(qr, qx, qy2, 142, 142);
            g.imageSmoothingEnabled = true;
            g.textAlign = 'left';
            g.font = bold(29); g.fillStyle = '#28282A';
            g.fillText('扫码测一测', qx + 166, qy2 + 58);
            g.font = reg(23, 700); g.fillStyle = '#6B6659';
            g.fillText('8 道题，你该不该考编？', qx + 166, qy2 + 98);
        }
        g.textAlign = 'center';
        g.font = reg(22); g.fillStyle = '#9B958A';
        g.fillText('仅供娱乐参考 · 不采集任何个人信息', cx, 1258);

        return c.toDataURL('image/png');
    }

    var sharePrevFocus = null;

    function openShare() {
        var modal = $('cs-share');
        var img = $('cs-share-img');
        var hint = $('cs-share-hint');
        sharePrevFocus = document.activeElement;
        modal.classList.add('is-on');
        $('cs-share-close').focus();
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
        $('cs-share').classList.remove('is-on');
        if (sharePrevFocus && sharePrevFocus.focus) sharePrevFocus.focus();
        sharePrevFocus = null;
    }

    /* ---------------- 绑定 ---------------- */
    $('cs-start').addEventListener('click', startOver);
    $('cs-back').addEventListener('click', back);
    $('cs-again-btn').addEventListener('click', startOver);
    $('cs-share-btn').addEventListener('click', openShare);
    $('cs-share-close').addEventListener('click', closeShare);
    $('cs-share-ok').addEventListener('click', closeShare);
    $('cs-share').addEventListener('click', function (e) { if (e.target === this) closeShare(); });
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
