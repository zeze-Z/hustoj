<?php
$show_title="测一测你家到底该不该鸡娃 - $OJ_NAME";
// 传播型 H5 极简壳：隐藏 OJ 顶栏与页脚主体（header.php/footer.php 按 $hide_chrome 分支，保留骨架+限流+极简署名）
$hide_chrome = true;
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
// 页面无表单、无入库、无用户态数据：仅注入站点名给分享卡片文案（JSON 十六进制转义防注入）
$tp_json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<style>
/* ===== 测一测你家到底该不该鸡娃（前缀 tp-）家庭账本 / 计算器风，移动端 375px 起 =====
   主色：账本米白 #F7F3E8 · 账线绿黑 #2F4F3E · 计算器键灰 #E8E4DA */
:root{
    --tp-paper:#F7F3E8; --tp-sheet:#FFFDF7; --tp-ink:#2F4F3E; --tp-ink-soft:#6E7C72;
    --tp-key:#E8E4DA; --tp-key-deep:#D9D4C7; --tp-line:#DCD6C6;
    --tp-accent:#2F4F3E; --tp-accent-fg:#FFFDF7;
    --tp-warm:#B98B4A;
    --tp-body:"PingFang SC","Microsoft YaHei",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}
html,body{height:100%} /* 极简壳：header.php 的 html 为 fixed，body 必须 100% 当滚动容器，禁 auto */
.tp-page{
    max-width:560px; margin:0 auto; padding:18px 14px 46px;
    min-height:calc(100vh - 48px); /* 极简壳后仅剩署名行高度 */
    color:var(--tp-ink); font-family:var(--tp-body); line-height:1.65;
    font-variant-numeric:tabular-nums;
    background-color:var(--tp-paper);
    /* 账本横格 */
    background-image:repeating-linear-gradient(transparent 0 31px, rgba(47,79,62,.06) 31px 32px);
}
.tp-page *{box-sizing:border-box}
.tp-state{display:none}
.tp-state.is-on{display:block}
.tp-card{background:var(--tp-sheet);border:1.5px solid rgba(47,79,62,.7);border-radius:10px;
    box-shadow:0 3px 0 rgba(47,79,62,.12), 0 10px 22px rgba(47,79,62,.06)}
.tp-h3{
    position:relative; display:inline-block; margin:0 0 10px; padding:0 0 4px;
    font-size:1.04rem; font-weight:800; color:var(--tp-ink); letter-spacing:.02em;
    border-bottom:3px double var(--tp-ink); /* 账本双线栏头 */
}
.tp-btn{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;min-height:52px;padding:10px 16px;border-radius:10px;
    border:2px solid var(--tp-ink);font-family:var(--tp-body);font-weight:800;font-size:1.02rem;
    cursor:pointer;text-decoration:none;transition:transform .12s ease, box-shadow .12s ease;
}
.tp-btn:active{transform:translate(2px,2px);box-shadow:none!important}
.tp-btn:focus-visible,a.tp-btn:focus-visible{outline:3px solid var(--tp-accent);outline-offset:3px}
.tp-btn--go{background:var(--tp-accent);color:var(--tp-accent-fg);font-size:1.14rem;box-shadow:0 4px 0 #1E342A}
.tp-btn--key{background:var(--tp-key);color:var(--tp-ink);box-shadow:0 4px 0 var(--tp-key-deep)}
.tp-btn--ghost{background:var(--tp-sheet);color:var(--tp-ink);box-shadow:0 3px 0 rgba(47,79,62,.28)}

/* ---- 落地页：账本封面 ---- */
.tp-cover{position:relative;padding:26px 18px 22px;overflow:hidden}
.tp-cover::after{ /* 账本装订孔边 */
    content:"";position:absolute;top:0;bottom:0;left:9px;width:3px;
    background:repeating-linear-gradient(to bottom, rgba(47,79,62,.35) 0 9px, transparent 9px 18px);
}
.tp-cover-kicker{margin:0 0 8px;font-size:.85rem;font-weight:800;color:var(--tp-ink-soft);letter-spacing:.16em}
.tp-title{
    font-weight:800;font-size:clamp(1.8rem,8vw,2.35rem);
    line-height:1.32;margin:0;color:var(--tp-ink);
}
.tp-title mark{
    background:none;color:inherit;padding:0 .04em;
    border-bottom:4px double var(--tp-warm); /* 账本红线代替荧光笔 */
}
.tp-sub{margin:12px 0 0;font-size:1rem;font-weight:800;color:var(--tp-ink)}
.tp-hook{margin:6px 0 0;font-size:.9rem;color:var(--tp-ink-soft)}
/* 试算清单：科目 —— 点线 —— 状态 */
.tp-ledger{margin:16px 0 0;padding:0;list-style:none;border-top:2px solid var(--tp-ink)}
.tp-ledger li{
    display:flex;align-items:baseline;gap:8px;padding:9px 2px 8px;
    font-size:.93rem;font-weight:700;border-bottom:1px dashed var(--tp-line);
}
.tp-ledger li i{flex:1;border-bottom:2px dotted var(--tp-key-deep);transform:translateY(-4px)}
.tp-ledger li b{font-weight:800;font-size:.83rem;color:var(--tp-ink-soft)}
.tp-count{
    display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;
    margin:16px 0 4px;font-weight:800;color:var(--tp-ink);font-size:.95rem;
}
.tp-count b{font-weight:800;font-size:2.4rem;line-height:1;color:var(--tp-ink);letter-spacing:.01em}
.tp-avatars{display:flex;flex-wrap:wrap;gap:8px;margin:10px 0 4px;padding:0;list-style:none}
.tp-avatars li{
    display:flex;align-items:center;gap:6px;padding:5px 11px 5px 7px;
    background:var(--tp-key);border:1.5px solid rgba(47,79,62,.55);border-radius:6px;
    font-size:.84rem;font-weight:800;
}
.tp-avatars li i{
    display:grid;place-items:center;width:24px;height:24px;border-radius:5px;
    border:1.5px solid rgba(47,79,62,.5);font-style:normal;font-size:.9rem;background:var(--tp-sheet);
}
.tp-start{margin-top:16px}
.tp-meta{margin:12px 0 0;text-align:center;font-size:.85rem;color:var(--tp-ink-soft)}
.tp-landing-note{margin:12px 0 0;text-align:center;font-size:.78rem;color:var(--tp-ink-soft)}

/* ---- 答题页 ---- */
.tp-qbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px}
.tp-dots{display:flex;gap:6px;flex:1}
.tp-dots span{flex:1;height:7px;border-radius:99px;background:var(--tp-key);transition:background .2s ease}
.tp-dots span.is-done{background:var(--tp-accent)}
.tp-dots span.is-cur{background:var(--tp-warm);box-shadow:0 0 0 2px rgba(47,79,62,.25)}
.tp-qnum{font-size:.85rem;font-weight:800;color:var(--tp-ink-soft);white-space:nowrap}
.tp-back{
    background:none;border:none;padding:10px 8px;min-height:44px;font:inherit;font-size:.88rem;font-weight:700;
    color:var(--tp-accent);cursor:pointer;text-decoration:underline;
}
.tp-back:focus-visible{outline:3px solid var(--tp-accent);outline-offset:2px;border-radius:6px}
.tp-qcard{padding:18px 16px 18px}
.tp-qcard.is-in{animation:tp-slide .26s ease both}
@keyframes tp-slide{from{opacity:0;transform:translateX(22px)}to{opacity:1;transform:none}}
.tp-qscene{
    display:inline-block;padding:3px 8px;font-size:.8rem;font-weight:800;color:var(--tp-ink);
    background:var(--tp-key);border:1.5px solid rgba(47,79,62,.5);border-radius:5px;
}
.tp-qtitle{
    font-weight:800;font-size:1.3rem;line-height:1.5;
    margin:12px 0 16px;color:var(--tp-ink);
}
.tp-opts{display:flex;flex-direction:column;gap:11px;margin:0;padding:0;list-style:none}
/* 选项 = 计算器按键 */
.tp-opt{
    display:flex;align-items:center;gap:12px;width:100%;min-height:56px;
    padding:11px 13px;text-align:left;background:var(--tp-key);color:var(--tp-ink);
    border:1.5px solid rgba(47,79,62,.5);border-radius:9px;
    font:inherit;font-size:.98rem;font-weight:700;line-height:1.5;cursor:pointer;
    box-shadow:0 3px 0 var(--tp-key-deep);
    transition:border-color .15s ease, background .15s ease, transform .12s ease;
}
.tp-opt:hover{border-color:var(--tp-ink);background:#EFEBE1}
.tp-opt:focus-visible{outline:3px solid var(--tp-accent);outline-offset:2px}
.tp-opt.is-picked{
    border-color:var(--tp-accent);background:#E4EDE6;transform:translate(2px,2px);
    box-shadow:0 1px 0 var(--tp-key-deep);
}
.tp-opt b{
    flex:0 0 auto;display:grid;place-items:center;width:30px;height:30px;border-radius:6px;
    border:1.5px solid rgba(47,79,62,.6);color:var(--tp-ink);font-weight:800;font-size:.95rem;
    background:var(--tp-sheet);box-shadow:0 2px 0 rgba(47,79,62,.18);
}
.tp-opt.is-picked b{border-color:var(--tp-accent);color:var(--tp-accent-fg);background:var(--tp-accent)}
.tp-qfoot{margin:14px 0 0;text-align:center;font-size:.82rem;color:var(--tp-ink-soft)}

/* ---- 结果页 ---- */
.tp-block{margin-bottom:18px}
/* 视觉锤：结算小票 */
.tp-receipt{position:relative;padding:18px 16px 0;background:var(--tp-sheet);
    border:1.5px solid rgba(47,79,62,.75);border-radius:8px;overflow:hidden;
    box-shadow:0 4px 0 rgba(47,79,62,.1), 0 12px 26px rgba(47,79,62,.07)}
.tp-receipt-head{display:flex;justify-content:space-between;gap:10px;
    font-size:.76rem;font-weight:800;color:var(--tp-ink-soft);letter-spacing:.06em}
.tp-receipt-rule{margin:10px -16px 0;border-top:2px dashed rgba(47,79,62,.45)}
.tp-receipt-no{margin:10px 0 0;font-size:.8rem;font-weight:700;color:var(--tp-ink-soft);text-align:center}
.tp-identity{text-align:center;padding:10px 0 4px}
.tp-avatar{
    width:72px;height:72px;margin:0 auto 10px;border-radius:14px;display:grid;place-items:center;
    font-size:2.1rem;background:var(--tp-accent);border:2px solid var(--tp-ink);
    box-shadow:0 4px 0 rgba(47,79,62,.3);transform:rotate(-3deg);
}
.tp-lead{margin:0;font-size:.92rem;font-weight:700;color:var(--tp-ink-soft)}
.tp-name{
    font-weight:800;font-size:2.1rem;line-height:1.25;
    margin:2px 0 4px;color:var(--tp-ink);letter-spacing:.01em;
}
.tp-epithet{margin:0 0 6px;font-size:1rem;font-weight:700;color:var(--tp-accent)}
.tp-barcode{
    height:34px;margin:6px -16px 0;
    background:repeating-linear-gradient(to right,
        var(--tp-ink) 0 3px, transparent 3px 6px, var(--tp-ink) 6px 8px, transparent 8px 13px,
        var(--tp-ink) 13px 15px, transparent 15px 19px);
    opacity:.82;
}
/* 匹配度环 */
.tp-ring-wrap{position:relative;width:170px;margin:0 auto;text-align:center}
.tp-ring{width:170px;height:170px;display:block;transform:rotate(-90deg)}
.tp-ring circle{fill:none;stroke-width:13;stroke-linecap:round}
.tp-ring .tp-ring-bg{stroke:var(--tp-key)}
.tp-ring .tp-ring-arc{stroke:var(--tp-accent)}
.tp-ring-num{
    position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;
}
.tp-ring-num span{font-weight:800;font-size:2.8rem;line-height:1;color:var(--tp-ink)}
.tp-ring-num i{font-style:normal;font-size:.84rem;font-weight:800;color:var(--tp-ink-soft)}
.tp-ring-cap{margin:4px 0 0;font-size:.85rem;font-weight:800;color:var(--tp-ink-soft);text-align:center}
.tp-tags,.tp-pills{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin:0;padding:0;list-style:none}
.tp-tags{margin-bottom:10px}
.tp-tag{
    display:inline-block;padding:6px 14px;border-radius:6px;background:var(--tp-sheet);
    color:var(--tp-ink);border:2px solid var(--tp-accent);font-weight:800;font-size:.92rem;
    box-shadow:0 3px 0 rgba(47,79,62,.14);
}
.tp-pill{
    display:inline-block;padding:5px 12px;border:1.5px dashed rgba(47,79,62,.55);
    border-radius:999px;color:var(--tp-ink-soft);font-size:.85rem;font-weight:700;background:var(--tp-key);
}
/* 金句：账本批注条 */
.tp-quote{
    position:relative;padding:16px 16px 14px;background:var(--tp-key);border-radius:8px;
    border:1.5px solid rgba(47,79,62,.55);border-left:6px solid var(--tp-accent);
}
.tp-quote blockquote{
    margin:0;font-weight:800;font-size:1.18rem;line-height:1.6;color:var(--tp-ink);
}
.tp-quote cite{display:block;margin-top:6px;font-style:normal;font-size:.83rem;font-weight:700;color:var(--tp-ink-soft);text-align:right}
/* 对冲固定句：金句区 / 理由段尾 / 页脚三处逐字锁定 */
.tp-guard{
    margin:10px 0 0;padding:9px 11px;font-size:.86rem;line-height:1.6;font-weight:700;
    color:var(--tp-ink-soft);background:rgba(255,253,247,.7);
    border:1px dashed rgba(47,79,62,.4);border-radius:6px;
}
.tp-quote .tp-guard{background:rgba(255,253,247,.75)}
/* 理由：分录说明 */
.tp-reason{
    padding:14px 16px;border:1.5px solid rgba(47,79,62,.4);border-radius:8px;
    background:var(--tp-sheet);font-size:.95rem;line-height:1.75;color:#3A4A41;
}
.tp-radar{width:100%;height:auto;display:block}
.tp-second{
    display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:9px;
    background:var(--tp-sheet);border:1.5px dashed rgba(47,79,62,.6);
    box-shadow:0 3px 0 rgba(47,79,62,.1);
}
.tp-second-emoji{
    flex:0 0 auto;width:50px;height:50px;border-radius:10px;display:grid;place-items:center;
    font-size:1.5rem;border:2px solid rgba(47,79,62,.6);background:var(--tp-key);
}
.tp-second-body{flex:1;min-width:0}
.tp-second-body p{margin:0}
.tp-second-label{font-size:.8rem;font-weight:800;color:var(--tp-ink-soft)}
.tp-second-name{font-weight:800;font-size:1.22rem;color:var(--tp-ink)}
.tp-second-desc{font-size:.85rem;color:var(--tp-ink-soft)}
.tp-second-pct{flex:0 0 auto;font-weight:800;font-size:1.42rem;color:var(--tp-accent)}
.tp-slogan{
    margin:0 0 12px;padding:13px 15px;border-radius:8px;
    background:var(--tp-ink);color:#F4F1E6;font-weight:800;font-size:1.02rem;
    text-align:center;letter-spacing:.02em;
}
.tp-links{display:flex;flex-direction:column;gap:10px;margin:0;padding:0;list-style:none}
.tp-link{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:9px;
    background:var(--tp-sheet);border:1.5px solid rgba(47,79,62,.4);
    text-decoration:none;color:var(--tp-ink);
    transition:border-color .15s ease, transform .15s ease;
}
.tp-link:hover{border-color:var(--tp-ink);text-decoration:none;transform:translateY(-2px)}
.tp-link:focus-visible{outline:3px solid var(--tp-accent);outline-offset:2px}
.tp-link-ico{
    flex:0 0 auto;width:42px;height:42px;border-radius:9px;display:grid;place-items:center;
    border:1.5px solid rgba(47,79,62,.55);background:var(--tp-key);
}
.tp-link-ico svg{width:22px;height:22px}
.tp-link-t{font-weight:800;font-size:.98rem}
.tp-link-d{font-size:.82rem;color:var(--tp-ink-soft);line-height:1.45}
.tp-link-chev{margin-left:auto;color:var(--tp-ink-soft);flex:0 0 auto}
.tp-actions{display:flex;flex-direction:column;gap:10px}
.tp-note{margin:16px 0 0;text-align:center;font-size:.78rem;line-height:1.6;color:var(--tp-ink-soft)}

/* ---- 分享卡片弹层 ---- */
.tp-modal{
    position:fixed;inset:0;z-index:2000;display:none;align-items:flex-start;justify-content:center;
    padding:24px 14px;overflow-y:auto;background:rgba(47,79,62,.62);
}
.tp-modal.is-on{display:flex}
.tp-modal-box{
    width:100%;max-width:420px;background:var(--tp-paper);border:1.5px solid var(--tp-ink);
    border-radius:12px;box-shadow:0 10px 34px rgba(0,0,0,.35);padding:14px 14px 18px;
}
.tp-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.tp-modal-head span{font-weight:800;font-size:1.1rem;color:var(--tp-ink)}
.tp-x{
    width:44px;height:44px;border-radius:8px;border:2px solid var(--tp-ink);background:var(--tp-sheet);
    font-size:1.3rem;line-height:1;color:var(--tp-ink);cursor:pointer;
}
.tp-x:focus-visible{outline:3px solid var(--tp-accent);outline-offset:2px}
.tp-share-img{display:block;width:100%;height:auto;border-radius:8px;border:1.5px solid rgba(47,79,62,.6);background:#fff}
.tp-modal-hint{margin:10px 0 12px;text-align:center;font-size:.86rem;font-weight:700;color:var(--tp-ink-soft)}

@media (min-width: 640px){
    .tp-page{padding-top:26px}
    .tp-cover{padding:32px 30px 26px}
}

@media (prefers-reduced-motion: reduce){
    .tp-page *,.tp-page *::before,.tp-page *::after{
        animation-duration:.001s!important;animation-iteration-count:1!important;transition-duration:.001s!important;
    }
}
</style>

<div class="tp-page">

    <!-- ============ 状态一：落地页 ============ -->
    <section id="tp-landing" class="tp-state is-on" aria-label="测试介绍">
        <div class="tp-card tp-cover">
            <p class="tp-cover-kicker">家庭账本 · 教育投入试算</p>
            <h1 class="tp-title">测一测，你家到底<mark>该不该鸡娃</mark>？</h1>
            <p class="tp-sub">8 道题，算算你家的鸡娃性价比</p>
            <p class="tp-hook">报班、刷题、抢跑，还是放养、阅读、慢慢来？先记账，再下结论。</p>
            <ul class="tp-ledger" aria-label="试算科目">
                <li><span>鸡娃倾向</span><i></i><b>待测算</b></li>
                <li><span>焦虑跟风</span><i></i><b>待测算</b></li>
                <li><span>预算理性</span><i></i><b>待测算</b></li>
                <li><span>长线耐心</span><i></i><b>待测算</b></li>
                <li><span>尊重放养</span><i></i><b>待测算</b></li>
            </ul>
            <p class="tp-count">已有 <b id="tp-total">9842</b> 位家长记过这笔账</p>
            <ul class="tp-avatars" id="tp-landing-avatars"></ul>
            <button type="button" class="tp-btn tp-btn--go tp-start" id="tp-start">开始试算</button>
            <p class="tp-meta">8 道题 · 约 1 分钟 · 无需登录</p>
        </div>
        <p class="tp-landing-note">全程本地计算，不采集任何个人信息</p>
    </section>

    <!-- ============ 状态二：答题页 ============ -->
    <section id="tp-quiz" class="tp-state" aria-label="答题">
        <div class="tp-qbar">
            <div class="tp-dots" id="tp-dots" aria-hidden="true"></div>
            <span class="tp-qnum" id="tp-qnum">1 / 8</span>
        </div>
        <button type="button" class="tp-back" id="tp-back" style="display:none">← 上一题</button>
        <div class="tp-card tp-qcard" id="tp-qcard">
            <span class="tp-qscene" id="tp-qscene"></span>
            <h2 class="tp-qtitle" id="tp-qtitle"></h2>
            <ul class="tp-opts" id="tp-opts"></ul>
        </div>
        <p class="tp-qfoot">凭第一直觉选，别对账太久</p>
    </section>

    <!-- ============ 状态三：结果页（顺序固定） ============ -->
    <section id="tp-result" class="tp-state" aria-label="测试结果">

        <!-- 1 立场揭晓（视觉锤：结算小票） -->
        <div class="tp-block tp-receipt" id="tp-receipt">
            <div class="tp-receipt-head"><span>家庭鸡娃账本</span><span>结算单</span></div>
            <div class="tp-receipt-rule"></div>
            <p class="tp-receipt-no">NO. TP-2026 · 本期试算</p>
            <div class="tp-identity">
                <div class="tp-avatar" id="tp-avatar" aria-hidden="true"></div>
                <p class="tp-lead">你的鸡娃立场是——</p>
                <h2 class="tp-name" id="tp-name"></h2>
                <p class="tp-epithet" id="tp-epithet"></p>
            </div>
            <div class="tp-barcode" aria-hidden="true"></div>
        </div>

        <!-- 2 匹配度环 -->
        <div class="tp-block">
            <div class="tp-ring-wrap">
                <svg class="tp-ring" viewBox="0 0 170 170" aria-hidden="true">
                    <circle class="tp-ring-bg" cx="85" cy="85" r="72"/>
                    <circle class="tp-ring-arc" id="tp-ring-arc" cx="85" cy="85" r="72"/>
                </svg>
                <div class="tp-ring-num"><span id="tp-pct">0</span><i>匹配度 %</i></div>
                <p class="tp-ring-cap">立场匹配度</p>
            </div>
        </div>

        <!-- 3 人设标签 + 关键词胶囊 -->
        <div class="tp-block">
            <ul class="tp-tags" id="tp-tags"></ul>
            <ul class="tp-pills" id="tp-pills"></ul>
        </div>

        <!-- 4 金句（含锁定对冲句①） -->
        <div class="tp-block tp-quote">
            <blockquote id="tp-quote"></blockquote>
            <cite id="tp-quote-by"></cite>
            <p class="tp-guard">测出『全面鸡娃』也别连夜加报两个班：本测试只负责说实话，不负责销课。</p>
        </div>

        <!-- 5 个性化理由（段尾含锁定对冲句②） -->
        <div class="tp-block">
            <h3 class="tp-h3">账房给你的分录说明</h3>
            <div class="tp-reason">
                <div id="tp-reason"></div>
                <p class="tp-guard">测出『摇摆跟风』很正常，说明你还在听、还在想；拿不定主意的时候，少报一个班就是稳赚。</p>
            </div>
        </div>

        <!-- 6 五维雷达 -->
        <div class="tp-block">
            <h3 class="tp-h3">你的五维试算表</h3>
            <svg class="tp-radar" id="tp-radar" viewBox="0 0 320 258" role="img" aria-label="五维鸡娃倾向雷达图"></svg>
        </div>

        <!-- 7 第二人格（隐藏） -->
        <div class="tp-block">
            <div class="tp-second">
                <div class="tp-second-emoji" id="tp-second-emoji" aria-hidden="true"></div>
                <div class="tp-second-body">
                    <p class="tp-second-label">你的隐藏第二人格</p>
                    <p class="tp-second-name" id="tp-second-name"></p>
                    <p class="tp-second-desc" id="tp-second-desc">换个答法，TA 可能会反超</p>
                </div>
                <div class="tp-second-pct" id="tp-second-pct"></div>
            </div>
        </div>

        <!-- 8 转化区 -->
        <div class="tp-block">
            <p class="tp-slogan">账要一笔一笔算，娃要一天一天陪</p>
            <ul class="tp-links">
                <li>
                    <a class="tp-link" href="course.php">
                        <span class="tp-link-ico">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <path d="M10 14 h18 a6 6 0 0 1 6 6 v30 a6 6 0 0 0 -6 -6 H10 Z" fill="none" stroke="#2F4F3E" stroke-width="5" stroke-linejoin="round"/>
                                <path d="M54 14 H36 a6 6 0 0 0 -6 6 v30 a6 6 0 0 1 6 -6 h18 Z" fill="none" stroke="#2F4F3E" stroke-width="5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="tp-link-t">课件省妈</span><br>
                            <span class="tp-link-d">现成课件直接用，晚上辅导作业少费一半口舌</span>
                        </span>
                        <svg class="tp-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="tp-link" href="more.php#games">
                        <span class="tp-link-ico">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="8" y="20" width="48" height="28" rx="12" fill="none" stroke="#2F4F3E" stroke-width="5"/>
                                <line x1="18" y1="29" x2="18" y2="39" stroke="#2F4F3E" stroke-width="5" stroke-linecap="round"/>
                                <line x1="13" y1="34" x2="23" y2="34" stroke="#2F4F3E" stroke-width="5" stroke-linecap="round"/>
                                <circle cx="44" cy="31" r="3.4" fill="#2F4F3E"/>
                                <circle cx="51" cy="38" r="3.4" fill="#2F4F3E"/>
                            </svg>
                        </span>
                        <span>
                            <span class="tp-link-t">娃放电</span><br>
                            <span class="tp-link-d">课前小游戏放电 10 分钟，回来写作业更坐得住</span>
                        </span>
                        <svg class="tp-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="tp-link" href="loginpage.php">
                        <span class="tp-link-ico">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="10" y="12" width="44" height="40" rx="6" fill="none" stroke="#2F4F3E" stroke-width="5"/>
                                <path d="M20 38 l9 -10 7 7 10 -13" fill="none" stroke="#2F4F3E" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="44" cy="24" r="3.4" fill="#2F4F3E"/>
                            </svg>
                        </span>
                        <span>
                            <span class="tp-link-t">登录看学习投入产出</span><br>
                            <span class="tp-link-d">登录后看这学期娃花了多少时间、进了多少分</span>
                        </span>
                        <svg class="tp-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
            </ul>
        </div>

        <!-- 9 操作区 -->
        <div class="tp-block tp-actions">
            <button type="button" class="tp-btn tp-btn--key" id="tp-share-btn">生成分享卡片</button>
            <button type="button" class="tp-btn tp-btn--ghost" id="tp-again-btn">重新试算</button>
        </div>

        <!-- 10 页脚免责（锁定原句③，逐字） -->
        <p class="tp-note">本测试仅供家庭娱乐参考，不构成任何教育投资建议——教育没有标准答案，适合你家的就是好答案。</p>
    </section>
</div>

<!-- ============ 状态四：分享卡片预览 ============ -->
<div class="tp-modal" id="tp-share" role="dialog" aria-modal="true" aria-label="分享卡片预览">
    <div class="tp-modal-box">
        <div class="tp-modal-head">
            <span>你的分享卡片</span>
            <button type="button" class="tp-x" id="tp-share-close" aria-label="关闭">&times;</button>
        </div>
        <img class="tp-share-img" id="tp-share-img" alt="鸡娃立场分享卡片">
        <p class="tp-modal-hint" id="tp-share-hint">正在生成…</p>
        <button type="button" class="tp-btn tp-btn--ghost" id="tp-share-ok">完成</button>
    </div>
</div>

<script>
window.TPS = {
    siteName: <?php echo json_encode($OJ_NAME, $tp_json_flags); ?>
};
</script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qrcode.min.js"></script>
<!-- 共享二维码/圆角/折行工具（禁止本地再拷贝 roundRect/wrapLines/makeQrCanvas） -->
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qr_helper.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/tiger_parent_test_data.js"></script>
<script>
(function () {
    'use strict';
    var D = window.TP_DATA;
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
            $('tp-' + m).classList.toggle('is-on', m === mode);
        });
        var page = document.querySelector('.tp-page');
        window.scrollTo(0, page ? Math.max(0, page.offsetTop - 70) : 0);
    }

    /* ---------------- 落地页 ---------------- */
    function renderLanding() {
        $('tp-total').textContent = D.MATCHED_TOTAL;
        var ul = $('tp-landing-avatars');
        ul.innerHTML = '';
        D.PERSONAS.forEach(function (p) {
            var li = document.createElement('li');
            var i = document.createElement('i');
            i.textContent = p.emoji;
            li.appendChild(i);
            li.appendChild(document.createTextNode(p.name));
            ul.appendChild(li);
        });
    }

    /* ---------------- 答题页 ---------------- */
    function renderQuiz(animate) {
        var q = D.QUESTIONS[state.qi];
        $('tp-qnum').textContent = (state.qi + 1) + ' / ' + D.QUESTIONS.length;
        $('tp-back').style.display = state.qi > 0 ? '' : 'none';

        var dots = $('tp-dots');
        dots.innerHTML = '';
        D.QUESTIONS.forEach(function (_, i) {
            var s = document.createElement('span');
            if (i < state.qi) s.className = 'is-done';
            if (i === state.qi) s.className = 'is-cur';
            dots.appendChild(s);
        });

        $('tp-qscene').textContent = '科目 · ' + q.scene;
        $('tp-qtitle').textContent = q.title;

        var ul = $('tp-opts');
        ul.innerHTML = '';
        q.options.forEach(function (opt, i) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tp-opt';
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

        var card = $('tp-qcard');
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
     * 选项权重 → 5 维用户向量（L2 归一）→ 与 5 种立场画像向量余弦相似度
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
        var page = document.querySelector('.tp-page');
        page.style.setProperty('--tp-accent', top.color);
        page.style.setProperty('--tp-accent-fg', top.fg);

        // 环形
        var arc = $('tp-ring-arc');
        var C = 2 * Math.PI * 72;
        arc.style.strokeDasharray = C.toFixed(2);
        arc.style.strokeDashoffset = C.toFixed(2);

        // 立场揭晓
        $('tp-avatar').textContent = top.emoji;
        $('tp-name').textContent = top.name;
        $('tp-epithet').textContent = top.epithet;

        // 标签 / 胶囊
        fillList('tp-tags', 'tp-tag', top.tags);
        fillList('tp-pills', 'tp-pill', top.pills);

        // 金句（锁定对冲句①为静态节点，随区块展示）
        $('tp-quote').textContent = top.quote;
        $('tp-quote-by').textContent = '—— ' + top.name;

        // 理由（锁定对冲句②为静态节点，位于段尾）
        $('tp-reason').textContent = res.reason;

        // 雷达
        renderRadar(res.raw, top.color);

        // 第二人格
        $('tp-second-emoji').textContent = res.second.emoji;
        $('tp-second-emoji').style.background = res.second.color;
        $('tp-second-emoji').style.color = res.second.fg;
        $('tp-second-name').textContent = res.second.name;
        $('tp-second-pct').textContent = res.secondPct + '%';
        // 同分时避免"TA 可能会反超"与相同百分比自相矛盾
        $('tp-second-desc').textContent = (res.secondPct >= res.topPct)
            ? '和你不相上下，换个答法见分晓' : '换个答法，TA 可能会反超';

        show('result');
        revealRing(res.sims[0].pct);
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

    var tpRingRaf = 0;
    function revealRing(pct) {
        var arc = $('tp-ring-arc');
        var num = $('tp-pct');
        var C = 2 * Math.PI * 72;
        var target = C * (1 - pct / 100);
        if (tpRingRaf) { cancelAnimationFrame(tpRingRaf); tpRingRaf = 0; } // 重测速进结果页时取消旧动画
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
            if (t < 1) tpRingRaf = requestAnimationFrame(step);
            else { num.textContent = pct; tpRingRaf = 0; }
        }
        arc.style.strokeDashoffset = C.toFixed(2);
        tpRingRaf = requestAnimationFrame(step);
    }

    /* ---------------- 五维雷达（SVG） ---------------- */
    function renderRadar(raw, color) {
        var svg = $('tp-radar');
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
            out.push('<polygon points="' + poly(pts) + '" fill="none" stroke="#D3CCBB" stroke-width="1.5"/>');
        });
        // 轴线
        for (var i = 0; i < n; i++) {
            var p = pt(i, R);
            out.push('<line x1="' + cx + '" y1="' + cy + '" x2="' + p[0].toFixed(1) + '" y2="' + p[1].toFixed(1) + '" stroke="#D3CCBB" stroke-width="1.5"/>');
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
                '" font-size="15" font-weight="700" fill="#2F4F3E" font-family="PingFang SC, Microsoft YaHei, sans-serif">' +
                D.DIMENSIONS[i].label + '</text>');
        }
        svg.innerHTML = out.join('');
    }

    /* ---------------- 分享卡片（Canvas 750×1334，账本结算单风）
     * 圆角/折行/二维码均用 qr_helper.js 全局封装，禁止本地拷贝 */
    function drawShareCard(res, url) {
        var W = 750, H = 1334;
        var c = document.createElement('canvas');
        c.width = W; c.height = H;
        var g = c.getContext('2d');
        var top = res.top;
        var bold = function (px) { return '800 ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };
        var reg = function (px, w) { return (w || 400) + ' ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };

        // 账本米白底 + 横格 + 左侧装订线
        g.fillStyle = '#F7F3E8'; g.fillRect(0, 0, W, H);
        g.strokeStyle = 'rgba(47,79,62,.07)'; g.lineWidth = 1.5;
        for (var y = 36; y < H; y += 38) { g.beginPath(); g.moveTo(0, y); g.lineTo(W, y); g.stroke(); }
        g.strokeStyle = 'rgba(47,79,62,.28)'; g.lineWidth = 3;
        g.beginPath(); g.moveTo(56, 0); g.lineTo(56, H); g.stroke();
        g.lineWidth = 1.5;
        g.beginPath(); g.moveTo(63, 0); g.lineTo(63, H); g.stroke();

        // 结算单纸
        var px = 84, pw = 590, py = 64, ph = 1116;
        roundRect(g, px, py, pw, ph, 10); g.fillStyle = '#FFFDF7'; g.fill();
        g.lineWidth = 3; g.strokeStyle = 'rgba(47,79,62,.8)'; g.stroke();

        var cx = px + pw / 2;
        var maxW = pw - 76;

        g.textAlign = 'center'; g.textBaseline = 'alphabetic';

        // 单头
        g.font = reg(27, 700); g.fillStyle = '#2F4F3E';
        g.fillText('家庭鸡娃账本 · 结算单', cx, 128);
        g.font = reg(21); g.fillStyle = '#6E7C72';
        g.fillText(((window.TPS && window.TPS.siteName) || '') + ' · NO. TP-2026 · 本期试算', cx, 164);
        // 虚线撕口
        g.save();
        g.setLineDash([10, 8]); g.strokeStyle = 'rgba(47,79,62,.5)'; g.lineWidth = 2.5;
        g.beginPath(); g.moveTo(px + 20, 190); g.lineTo(px + pw - 20, 190); g.stroke();
        g.restore();

        // 立场称号（分享卡视觉锤）
        g.font = reg(25, 700); g.fillStyle = '#6E7C72';
        g.fillText('本期结论', cx, 244);
        g.font = bold(88);
        g.fillStyle = '#2F4F3E';
        var nameTxt = top.name;
        if (g.measureText(nameTxt).width > maxW) g.font = bold(Math.floor(88 * maxW / g.measureText(nameTxt).width));
        g.fillText(nameTxt, cx, 336);
        g.font = reg(27, 700); g.fillStyle = top.color;
        g.fillText(top.epithet, cx, 382);

        // 匹配度徽章
        var badgeTxt = '立场匹配度 ' + res.topPct + '%';
        g.font = bold(31);
        var bw = g.measureText(badgeTxt).width + 52, bh = 60;
        var bx = cx - bw / 2, by = 416;
        roundRect(g, bx + 4, by + 5, bw, bh, 30); g.fillStyle = 'rgba(47,79,62,.75)'; g.fill();
        roundRect(g, bx, by, bw, bh, 30);
        g.fillStyle = '#E8E4DA'; g.fill();
        g.lineWidth = 4; g.strokeStyle = '#2F4F3E'; g.stroke();
        g.fillStyle = '#2F4F3E';
        g.fillText(badgeTxt, cx, by + 42);

        // 3 个人设标签：居中一行，放不下时自动缩字号
        var gap = 14, tags = top.tags, tfont = 29, tw, total;
        function measureTags(fs) {
            g.font = bold(fs);
            var w = tags.map(function (t) { return g.measureText(t).width + 34; });
            return { w: w, sum: w.reduce(function (a, b) { return a + b; }, 0) + gap * (tags.length - 1) };
        }
        var m = measureTags(tfont);
        while (m.sum > maxW && tfont > 18) { tfont -= 2; m = measureTags(tfont); }
        tw = m.w; total = m.sum;
        var ty = 516, rx = cx - total / 2;
        tags.forEach(function (t, i) {
            roundRect(g, rx, ty, tw[i], 54, 8);
            g.fillStyle = '#FFFDF7'; g.fill();
            g.lineWidth = 3; g.strokeStyle = top.color; g.stroke();
            g.fillStyle = '#2F4F3E';
            g.fillText(t, rx + tw[i] / 2, ty + 36);
            rx += tw[i] + gap;
        });

        // 金句（最多 2 行，防撞署名）
        var qTop = 616;
        roundRect(g, px + 38, qTop, maxW, 170, 8);
        g.fillStyle = '#E8E4DA'; g.fill();
        g.lineWidth = 3; g.strokeStyle = 'rgba(47,79,62,.55)'; g.stroke();
        g.fillStyle = '#2F4F3E';
        var qfont = 35;
        g.font = reg(qfont, 800);
        var qlines = wrapLines(g, '「' + top.quote + '」', maxW - 56);
        while (qlines.length > 2 && qfont > 26) {
            qfont -= 2;
            g.font = reg(qfont, 800);
            qlines = wrapLines(g, '「' + top.quote + '」', maxW - 56);
        }
        qlines = qlines.slice(0, 2);
        var qy = qTop + (qlines.length > 1 ? 66 : 88);
        qlines.forEach(function (ln) { g.fillText(ln, cx, qy); qy += 46; });
        g.font = reg(22, 700); g.fillStyle = '#6E7C72';
        g.fillText('—— ' + top.name, cx, qTop + 148);

        // 钩子文案（分享文案）
        g.font = bold(30); g.fillStyle = top.color;
        var hook = wrapLines(g, '我测出来是【' + top.name + '】，测完我家该『半鸡』，谁来监督执行？', maxW).slice(0, 2);
        var hy = 848;
        hook.forEach(function (ln) { g.fillText(ln, cx, hy); hy += 44; });

        // 二维码
        var qr = makeQrCanvas(url, 144, '#2F4F3E');
        var qx = px + 44, qy2 = 936;
        if (qr) {
            g.imageSmoothingEnabled = false;
            g.drawImage(qr, qx, qy2, 144, 144);
            g.imageSmoothingEnabled = true;
            g.textAlign = 'left';
            g.font = bold(29); g.fillStyle = '#2F4F3E';
            g.fillText('扫码测一测', qx + 168, qy2 + 56);
            g.font = reg(22, 700); g.fillStyle = '#6E7C72';
            g.fillText('8 道题，算算你家的鸡娃性价比', qx + 168, qy2 + 96);
        }
        // 条码装饰 + 免责
        g.textAlign = 'center';
        g.save();
        g.beginPath();
        var bcY = 1108, bcX = px + 60, bcW = pw - 120;
        g.rect(bcX, bcY, bcW, 34); g.clip();
        g.fillStyle = '#2F4F3E';
        var bx2 = bcX, seed = 7;
        while (bx2 < bcX + bcW) {
            seed = (seed * 31 + 17) % 97;
            var barW = 2 + (seed % 5);
            g.fillRect(bx2, bcY, barW, 34);
            bx2 += barW + 3 + (seed % 4);
        }
        g.restore();
        g.font = reg(21); g.fillStyle = '#6E7C72';
        g.fillText('仅供娱乐参考 · 不采集任何个人信息', cx, 1180);

        return c.toDataURL('image/png');
    }

    var sharePrevFocus = null;

    function openShare() {
        var modal = $('tp-share');
        var img = $('tp-share-img');
        var hint = $('tp-share-hint');
        sharePrevFocus = document.activeElement;
        modal.classList.add('is-on');
        $('tp-share-close').focus();
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
        $('tp-share').classList.remove('is-on');
        if (sharePrevFocus && sharePrevFocus.focus) sharePrevFocus.focus();
        sharePrevFocus = null;
    }

    /* ---------------- 绑定 ---------------- */
    $('tp-start').addEventListener('click', startOver);
    $('tp-back').addEventListener('click', back);
    $('tp-again-btn').addEventListener('click', startOver);
    $('tp-share-btn').addEventListener('click', openShare);
    $('tp-share-close').addEventListener('click', closeShare);
    $('tp-share-ok').addEventListener('click', closeShare);
    $('tp-share').addEventListener('click', function (e) { if (e.target === this) closeShare(); });
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
