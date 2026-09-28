<?php
$show_title="8 道题，鉴定你的陪读精神状态 - $OJ_NAME";
// 传播型 H5 极简壳：隐藏 OJ 顶栏与页脚主体（header.php/footer.php 按 $hide_chrome 分支，保留骨架+限流+极简署名）
$hide_chrome = true;
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
// 页面无表单、无入库、无用户态数据：仅注入站点名给分享卡片文案（JSON 十六进制转义防注入）
$hm_json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<style>
/* ===== 陪读精神状态（前缀 hm-）客厅夜战情景喜剧，移动端 375px 起 ===== */
:root{
    --hm-cream:#FFF3DC; --hm-paper:#FFFDF4; --hm-ink:#4A3418; --hm-ink-soft:#8A6A3C;
    --hm-lamp:#F6B24B; --hm-lamp-deep:#E09A2E; --hm-red:#D9503F;
    --hm-line:#EBD9B4; --hm-tape:#E8C98A;
    --hm-accent:#D9503F; --hm-accent-fg:#FFF6EF;
    --hm-body:"PingFang SC","Microsoft YaHei",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}
html,body{height:100%}
.hm-page{
    max-width:560px; margin:0 auto; padding:30px 14px 46px;
    min-height:100%;
    color:var(--hm-ink); font-family:var(--hm-body); line-height:1.65;
    /* 暖黄渐变底 + 一处台灯 radial 光晕（左上灯罩打光） */
    background-color:var(--hm-cream);
    background-image:
        radial-gradient(340px 240px at 8% -4%, rgba(246,178,75,.55), rgba(246,178,75,0) 70%),
        linear-gradient(180deg, #FFF7E6 0%, #FFF3DC 55%, #FDECC8 100%);
}
.hm-page *{box-sizing:border-box}
.hm-state{display:none}
.hm-state.is-on{display:block}
/* 粗描边圆角卡片：分格符号语言的载体 */
.hm-card{
    position:relative; background:var(--hm-paper);
    border:2.5px solid var(--hm-ink); border-radius:18px;
    box-shadow:0 4px 0 rgba(74,52,24,.18), 0 12px 26px rgba(74,52,24,.08);
}
.hm-h3{
    position:relative; display:inline-block; margin:0 0 10px; padding-left:15px;
    font-size:1.04rem; font-weight:800; color:var(--hm-ink); letter-spacing:.02em;
}
.hm-h3::before{
    content:""; position:absolute; left:0; top:.3em; width:9px; height:9px;
    background:var(--hm-lamp); border:1.5px solid var(--hm-ink); transform:rotate(10deg);
}
.hm-btn{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;min-height:52px;padding:10px 16px;border-radius:14px;
    border:2.5px solid var(--hm-ink);font-family:var(--hm-body);font-weight:800;font-size:1.02rem;
    cursor:pointer;text-decoration:none;transition:transform .12s ease, box-shadow .12s ease;
}
.hm-btn:active{transform:translate(2px,2px);box-shadow:none!important}
.hm-btn:focus-visible,a.hm-btn:focus-visible{outline:3px solid var(--hm-lamp-deep);outline-offset:3px}
.hm-btn--go{background:var(--hm-lamp);color:var(--hm-ink);font-size:1.16rem;box-shadow:0 4px 0 var(--hm-lamp-deep)}
.hm-btn--tv{background:var(--hm-red);color:#FFF6EF;box-shadow:0 4px 0 #9E3427}
.hm-btn--ghost{background:var(--hm-paper);color:var(--hm-ink);box-shadow:0 3px 0 rgba(74,52,24,.3)}
/* 拟声词贴纸 */
.hm-sfx{
    position:absolute; top:-13px; right:12px; z-index:2;
    padding:3px 11px; background:var(--hm-lamp); border:2px solid var(--hm-ink);
    border-radius:999px; font-size:.8rem; font-weight:800; color:var(--hm-ink);
    transform:rotate(4deg); box-shadow:0 2px 0 rgba(74,52,24,.25);
}
/* 对话气泡尾巴 */
.hm-tail::after{
    content:""; position:absolute; left:34px; bottom:-15px;
    width:26px; height:26px; background:var(--hm-paper);
    border-right:2.5px solid var(--hm-ink); border-bottom:2.5px solid var(--hm-ink);
    transform:rotate(48deg) skew(8deg); border-radius:0 0 8px 0;
}

/* ---- 落地页 ---- */
.hm-cover{padding:30px 20px 24px; transform:rotate(-.8deg)}
.hm-sticker-lamp{
    position:absolute; top:-16px; left:16px;
    padding:5px 13px; background:var(--hm-red); color:#FFF6EF;
    border:2px solid var(--hm-ink); border-radius:10px;
    font-size:.82rem; font-weight:800; transform:rotate(-5deg);
    box-shadow:0 3px 0 rgba(74,52,24,.25);
}
.hm-title{
    font-weight:800;font-size:clamp(1.8rem,8vw,2.35rem);
    line-height:1.32;margin:8px 0 0;color:var(--hm-ink);letter-spacing:.01em;
}
.hm-title mark{background:linear-gradient(transparent 50%, #F9D487 50%);color:inherit;padding:0 .06em}
.hm-sub{margin:12px 0 0;font-size:.98rem;font-weight:700;color:var(--hm-ink-soft)}
.hm-hook{
    margin:14px 0 0;padding:12px 15px;border-radius:12px;
    background:#FFF7E3;border:2px dashed var(--hm-lamp-deep);
    font-size:.92rem;font-weight:700;color:var(--hm-ink);
}
.hm-count{
    display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;
    margin:16px 0 4px;padding:12px 16px;border:2px solid var(--hm-ink);
    border-radius:12px;background:#FFF1CF;font-weight:700;color:var(--hm-ink);
    transform:rotate(.6deg);
}
.hm-count b{font-weight:800;font-size:2.4rem;line-height:1;color:var(--hm-red);letter-spacing:.01em}
.hm-cast{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0 4px;padding:0;list-style:none}
.hm-cast li{
    display:flex;align-items:center;gap:6px;padding:5px 11px 5px 7px;
    background:var(--hm-paper);border:2px solid var(--hm-ink);border-radius:10px;
    font-size:.84rem;font-weight:800;box-shadow:0 2px 0 rgba(74,52,24,.16);
}
.hm-cast li:nth-child(odd){transform:rotate(-1.6deg)}
.hm-cast li:nth-child(even){transform:rotate(1.4deg)}
.hm-cast li i{
    display:grid;place-items:center;width:24px;height:24px;border-radius:7px;
    border:1.5px solid var(--hm-ink);font-style:normal;font-size:.9rem;
}
.hm-meta{margin:12px 0 0;text-align:center;font-size:.86rem;color:var(--hm-ink-soft);font-weight:700}
.hm-landing-note{margin:14px 0 0;text-align:center;font-size:.8rem;color:var(--hm-ink-soft)}

/* ---- 答题页 ---- */
.hm-qbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px}
.hm-dots{display:flex;gap:6px;flex:1}
.hm-dots span{flex:1;height:7px;border-radius:99px;background:#F0DFB8;transition:background .2s ease}
.hm-dots span.is-done{background:var(--hm-ink)}
.hm-dots span.is-cur{background:var(--hm-red);box-shadow:0 0 0 2px rgba(74,52,24,.22)}
.hm-qnum{font-size:.85rem;font-weight:800;color:var(--hm-ink-soft);white-space:nowrap}
.hm-back{
    background:none;border:none;padding:10px 8px;min-height:44px;font:inherit;font-size:.88rem;font-weight:700;
    color:var(--hm-lamp-deep);cursor:pointer;text-decoration:underline;
}
.hm-back:focus-visible{outline:3px solid var(--hm-lamp-deep);outline-offset:2px;border-radius:6px}
.hm-qwrap{transform:rotate(-.7deg)}
.hm-qcard{padding:22px 16px 18px}
.hm-qcard.is-in{animation:hm-slide .26s ease both}
@keyframes hm-slide{from{opacity:0;transform:translateX(22px)}to{opacity:1;transform:none}}
.hm-qscene{
    display:inline-block;padding:3px 9px;font-size:.8rem;font-weight:800;color:var(--hm-ink);
    background:var(--hm-tape);border:1.5px solid var(--hm-ink);border-radius:6px;
    transform:rotate(-1.5deg);
}
.hm-qtitle{
    font-weight:800;font-size:1.3rem;line-height:1.5;
    margin:12px 0 16px;color:var(--hm-ink);
}
.hm-opts{display:flex;flex-direction:column;gap:12px;margin:0;padding:0;list-style:none}
.hm-opt{
    display:flex;align-items:center;gap:12px;width:100%;min-height:56px;
    padding:12px 14px;text-align:left;background:var(--hm-paper);color:var(--hm-ink);
    border:2px solid rgba(74,52,24,.5);border-radius:14px;
    font:inherit;font-size:.97rem;font-weight:700;line-height:1.5;cursor:pointer;
    transition:border-color .15s ease, background .15s ease, transform .12s ease;
}
.hm-opt:hover{border-color:var(--hm-ink);background:#FFFBEF}
.hm-opt:focus-visible{outline:3px solid var(--hm-lamp-deep);outline-offset:2px}
.hm-opt.is-picked{
    border-color:var(--hm-red);background:#FFF0E6;transform:translate(2px,2px);
    box-shadow:0 2px 0 rgba(74,52,24,.2);
}
.hm-opt b{
    flex:0 0 auto;display:grid;place-items:center;width:30px;height:30px;border-radius:9px;
    border:2px solid var(--hm-ink);color:var(--hm-ink);font-weight:800;font-size:.96rem;
    background:var(--hm-lamp);
}
.hm-opt.is-picked b{background:var(--hm-red);color:#FFF6EF;border-color:var(--hm-ink)}
.hm-qfoot{margin:14px 0 0;text-align:center;font-size:.82rem;color:var(--hm-ink-soft);font-weight:700}

/* ---- 结果页 ---- */
.hm-block{margin-bottom:18px}
/* 电视机框：结果页收口的视觉锤 */
.hm-tv-stage{position:relative}
.hm-tv{
    position:relative; padding:16px 14px 26px;
    background:linear-gradient(180deg,#7A4E24,#5E3A18);
    border:3px solid var(--hm-ink); border-radius:24px;
    box-shadow:0 6px 0 rgba(74,52,24,.3), 0 16px 30px rgba(74,52,24,.16);
    transform:rotate(-.6deg);
}
.hm-tv::before,.hm-tv::after{
    content:""; position:absolute; bottom:-13px; width:34px; height:14px;
    background:#5E3A18; border:3px solid var(--hm-ink); border-top:none; border-radius:0 0 8px 8px;
}
.hm-tv::before{left:36px; transform:skewX(-14deg)}
.hm-tv::after{right:36px; transform:skewX(14deg)}
.hm-tv-ant{
    position:absolute; top:-30px; left:50%; width:120px; height:34px;
    margin-left:-60px; pointer-events:none;
}
.hm-tv-knob{
    position:absolute; right:16px; bottom:7px; display:flex; gap:7px;
}
.hm-tv-knob i{width:11px;height:11px;border-radius:50%;background:var(--hm-lamp);border:2px solid var(--hm-ink)}
.hm-tv-knob i:nth-child(2){background:var(--hm-red)}
.hm-tv-screen{
    position:relative; overflow:hidden;
    background:linear-gradient(180deg,#FFF8E7,#FCE7B6);
    border:3px solid var(--hm-ink); border-radius:14px;
    padding:20px 14px 18px; text-align:center;
}
/* 显像管扫描线（极淡，不干扰阅读） */
.hm-tv-screen::after{
    content:""; position:absolute; inset:0; pointer-events:none;
    background:repeating-linear-gradient(transparent 0 5px, rgba(74,52,24,.05) 5px 6px);
}
.hm-tv-screen.is-on{animation:hm-boot .5s ease both}
@keyframes hm-boot{
    0%{filter:brightness(2.2);transform:scaleY(.04)}
    45%{filter:brightness(1.3);transform:scaleY(1)}
    100%{filter:none;transform:none}
}
.hm-hero{position:relative;display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;z-index:1}
.hm-ring-wrap{position:relative;width:150px;text-align:center}
.hm-ring{width:150px;height:150px;display:block;transform:rotate(-90deg)}
.hm-ring circle{fill:none;stroke-width:13;stroke-linecap:round}
.hm-ring .hm-ring-bg{stroke:#F0DFB8}
.hm-ring .hm-ring-arc{stroke:var(--hm-accent)}
.hm-ring-num{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.hm-ring-num span{font-weight:800;font-size:2.55rem;line-height:1;color:var(--hm-ink)}
.hm-ring-num i{font-style:normal;font-size:.84rem;font-weight:800;color:var(--hm-ink-soft)}
.hm-ring-cap{margin:6px 0 0;font-size:.82rem;font-weight:800;color:var(--hm-ink-soft);text-align:center}
.hm-avatar{
    width:76px;height:76px;margin:0 auto 8px;border-radius:18px;display:grid;place-items:center;
    font-size:2.3rem;background:var(--hm-accent);border:2.5px solid var(--hm-ink);
    box-shadow:0 4px 0 rgba(74,52,24,.35);transform:rotate(-4deg);
}
.hm-lead{margin:0;font-size:.9rem;font-weight:800;color:var(--hm-ink-soft)}
.hm-name{
    font-weight:800;font-size:2rem;line-height:1.25;
    margin:2px 0 4px;color:var(--hm-ink);letter-spacing:.02em;
}
.hm-epithet{margin:0;font-size:.98rem;font-weight:700;color:var(--hm-accent)}
.hm-tags,.hm-pills{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin:0;padding:0;list-style:none}
.hm-tags{margin-bottom:10px}
.hm-tag{
    display:inline-block;padding:6px 14px;border-radius:10px;background:var(--hm-paper);
    color:var(--hm-ink);border:2.5px solid var(--hm-accent);font-weight:800;font-size:.9rem;
    box-shadow:0 3px 0 rgba(74,52,24,.16);
}
.hm-tags li:nth-child(1){transform:rotate(-2deg)}
.hm-tags li:nth-child(2){transform:rotate(1.6deg)}
.hm-tags li:nth-child(3){transform:rotate(-1.2deg)}
.hm-pill{
    display:inline-block;padding:5px 12px;border:2px dashed var(--hm-tape);
    border-radius:999px;color:var(--hm-ink-soft);font-size:.85rem;font-weight:700;background:#FFF7E3;
}
/* 金句：台灯照亮的便签 */
.hm-quote{
    position:relative;padding:22px 18px 16px;background:#FBE0A0;border-radius:8px;
    border:2.5px solid var(--hm-ink);box-shadow:0 6px 16px rgba(74,52,24,.16);
    transform:rotate(-.7deg);overflow:hidden;
}
.hm-quote::before{
    content:"";position:absolute;top:-11px;left:50%;width:96px;height:26px;margin-left:-48px;
    background:rgba(232,201,138,.95);border-left:1px dashed rgba(74,52,24,.3);border-right:1px dashed rgba(74,52,24,.3);
    transform:rotate(2.5deg);
}
.hm-quote blockquote{position:relative;margin:0;font-weight:800;font-size:1.18rem;line-height:1.65;color:var(--hm-ink)}
.hm-quote cite{display:block;margin-top:8px;font-style:normal;font-size:.84rem;font-weight:700;color:#8A6A3C;text-align:right}
/* 理由：横线便签纸 */
.hm-reason{
    padding:14px 16px;border:2.5px solid var(--hm-ink);border-radius:14px;
    background:repeating-linear-gradient(var(--hm-paper) 0 31px, var(--hm-line) 31px 32px);
    font-size:.95rem;line-height:32px;color:#5C452A;transform:rotate(.4deg);
}
.hm-radar{width:100%;height:auto;display:block}
.hm-second{
    display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:14px;
    background:var(--hm-paper);border:2.5px dashed var(--hm-ink);
    box-shadow:0 3px 0 rgba(74,52,24,.12);transform:rotate(-.5deg);
}
.hm-second-emoji{
    flex:0 0 auto;width:50px;height:50px;border-radius:12px;display:grid;place-items:center;
    font-size:1.5rem;border:2.5px solid var(--hm-ink);
}
.hm-second-body{flex:1;min-width:0}
.hm-second-body p{margin:0}
.hm-second-label{font-size:.8rem;font-weight:800;color:var(--hm-lamp-deep)}
.hm-second-name{font-weight:800;font-size:1.2rem;color:var(--hm-ink)}
.hm-second-desc{font-size:.84rem;color:var(--hm-ink-soft)}
.hm-second-pct{flex:0 0 auto;font-weight:800;font-size:1.4rem;color:var(--hm-red)}
.hm-slogan{
    margin:0 0 12px;padding:13px 15px;border-radius:12px;
    background:var(--hm-ink);color:#FFF3DC;font-weight:800;font-size:1rem;
    text-align:center;letter-spacing:.02em;transform:rotate(-.4deg);
}
.hm-links{display:flex;flex-direction:column;gap:10px;margin:0;padding:0;list-style:none}
.hm-link{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:14px;
    background:var(--hm-paper);border:2px solid rgba(74,52,24,.45);
    text-decoration:none;color:var(--hm-ink);
    transition:border-color .15s ease, transform .15s ease;
}
.hm-link:hover{border-color:var(--hm-ink);text-decoration:none;transform:translateY(-2px)}
.hm-link:focus-visible{outline:3px solid var(--hm-lamp-deep);outline-offset:2px}
.hm-link-ico{
    flex:0 0 auto;width:42px;height:42px;border-radius:12px;display:grid;place-items:center;
    border:2px solid var(--hm-ink);
}
.hm-link-ico svg{width:22px;height:22px}
.hm-link-t{font-weight:800;font-size:.96rem}
.hm-link-d{font-size:.81rem;color:var(--hm-ink-soft);line-height:1.45}
.hm-link-chev{margin-left:auto;color:var(--hm-ink-soft);flex:0 0 auto}
.hm-login-hint{
    margin:12px 0 0;padding:11px 14px;border-radius:12px;background:#FFF7E3;
    border:2px dashed var(--hm-tape);font-size:.87rem;font-weight:700;color:var(--hm-ink-soft);text-align:center;
}
.hm-login-hint a{color:var(--hm-red);font-weight:800}
.hm-actions{display:flex;flex-direction:column;gap:10px}
.hm-note{margin:16px 0 0;text-align:center;font-size:.78rem;color:var(--hm-ink-soft)}

/* ---- 分享卡片弹层 ---- */
.hm-modal{
    position:fixed;inset:0;z-index:2000;display:none;align-items:flex-start;justify-content:center;
    padding:24px 14px;overflow-y:auto;background:rgba(74,52,24,.6);
}
.hm-modal.is-on{display:flex}
.hm-modal-box{
    width:100%;max-width:420px;background:var(--hm-cream);border:2.5px solid var(--hm-ink);
    border-radius:16px;box-shadow:0 10px 34px rgba(0,0,0,.35);padding:14px 14px 18px;
}
.hm-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.hm-modal-head span{font-weight:800;font-size:1.08rem;color:var(--hm-ink)}
.hm-x{
    width:44px;height:44px;border-radius:12px;border:2.5px solid var(--hm-ink);background:var(--hm-paper);
    font-size:1.3rem;line-height:1;color:var(--hm-ink);cursor:pointer;
}
.hm-x:focus-visible{outline:3px solid var(--hm-lamp-deep);outline-offset:2px}
.hm-share-img{display:block;width:100%;height:auto;border-radius:12px;border:2px solid var(--hm-ink);background:#fff}
.hm-modal-hint{margin:10px 0 12px;text-align:center;font-size:.85rem;font-weight:700;color:var(--hm-ink-soft)}

@media (min-width: 640px){
    .hm-page{padding-top:26px}
    .hm-cover{padding:36px 32px 30px}
}

@media (prefers-reduced-motion: reduce){
    .hm-page *,.hm-page *::before,.hm-page *::after{
        animation-duration:.001s!important;animation-iteration-count:1!important;transition-duration:.001s!important;
    }
}
</style>

<div class="hm-page">

    <!-- ============ 状态一：落地页 ============ -->
    <section id="hm-landing" class="hm-state is-on" aria-label="测试介绍">
        <div class="hm-card hm-cover">
            <span class="hm-sticker-lamp">台灯已开 · 夜战开始</span>
            <h1 class="hm-title">8 道题，鉴定你的<mark>陪读精神状态</mark></h1>
            <p class="hm-sub">不写作业母慈子孝，一写作业鸡飞狗跳？</p>
            <p class="hm-hook">每晚 19:00–21:00，你家客厅上演的是温情片还是动作片？</p>
            <p class="hm-count">已有 <b id="hm-total">15873</b> 位家长测过</p>
            <ul class="hm-cast" id="hm-landing-cast"></ul>
            <button type="button" class="hm-btn hm-btn--go" id="hm-start" style="margin-top:16px">开始测试</button>
            <p class="hm-meta">8 道题 · 约 1 分钟 · 无需登录</p>
        </div>
        <p class="hm-landing-note">纯属娱乐，结果仅供开心参考</p>
    </section>

    <!-- ============ 状态二：答题页 ============ -->
    <section id="hm-quiz" class="hm-state" aria-label="答题">
        <div class="hm-qbar">
            <div class="hm-dots" id="hm-dots" aria-hidden="true"></div>
            <span class="hm-qnum" id="hm-qnum">1 / 8</span>
        </div>
        <button type="button" class="hm-back" id="hm-back" style="display:none">← 上一题</button>
        <div class="hm-qwrap">
            <div class="hm-card hm-qcard hm-tail" id="hm-qcard">
                <span class="hm-sfx" id="hm-sfx" aria-hidden="true">吼——</span>
                <span class="hm-qscene" id="hm-qscene"></span>
                <h2 class="hm-qtitle" id="hm-qtitle"></h2>
                <ul class="hm-opts" id="hm-opts"></ul>
            </div>
        </div>
        <p class="hm-qfoot">凭第一直觉选，别陪太久</p>
    </section>

    <!-- ============ 状态三：结果页（顺序固定） ============ -->
    <section id="hm-result" class="hm-state" aria-label="测试结果">

        <!-- 1 电视机框：称号揭晓 + 匹配度环 -->
        <div class="hm-block hm-tv-stage">
            <svg class="hm-tv-ant" viewBox="0 0 120 34" aria-hidden="true">
                <line x1="60" y1="34" x2="24" y2="4" stroke="#4A3418" stroke-width="4" stroke-linecap="round"/>
                <line x1="60" y1="34" x2="96" y2="6" stroke="#4A3418" stroke-width="4" stroke-linecap="round"/>
                <circle cx="24" cy="4" r="4.5" fill="#D9503F" stroke="#4A3418" stroke-width="2.5"/>
                <circle cx="96" cy="6" r="4.5" fill="#F6B24B" stroke="#4A3418" stroke-width="2.5"/>
            </svg>
            <div class="hm-tv">
                <div class="hm-tv-screen" id="hm-tv-screen">
                    <div class="hm-hero">
                        <div class="hm-ring-wrap">
                            <svg class="hm-ring" viewBox="0 0 150 150" aria-hidden="true">
                                <circle class="hm-ring-bg" cx="75" cy="75" r="63"/>
                                <circle class="hm-ring-arc" id="hm-ring-arc" cx="75" cy="75" r="63"/>
                            </svg>
                            <div class="hm-ring-num"><span id="hm-pct">0</span><i>匹配度 %</i></div>
                            <p class="hm-ring-cap">陪读人格匹配度</p>
                        </div>
                        <div>
                            <div class="hm-avatar" id="hm-avatar" aria-hidden="true"></div>
                            <p class="hm-lead">你家客厅的常驻主角是——</p>
                            <h2 class="hm-name" id="hm-name"></h2>
                            <p class="hm-epithet" id="hm-epithet"></p>
                        </div>
                    </div>
                </div>
                <span class="hm-tv-knob" aria-hidden="true"><i></i><i></i></span>
            </div>
        </div>

        <!-- 2 人设标签 + 关键词胶囊 -->
        <div class="hm-block">
            <ul class="hm-tags" id="hm-tags"></ul>
            <ul class="hm-pills" id="hm-pills"></ul>
        </div>

        <!-- 3 金句 -->
        <div class="hm-block hm-quote">
            <blockquote id="hm-quote"></blockquote>
            <cite id="hm-quote-by"></cite>
        </div>

        <!-- 4 个性化理由 -->
        <div class="hm-block">
            <h3 class="hm-h3">客厅观察员给你的理由</h3>
            <div class="hm-reason" id="hm-reason"></div>
        </div>

        <!-- 5 五维雷达 -->
        <div class="hm-block">
            <h3 class="hm-h3">你的五维陪读画像</h3>
            <svg class="hm-radar" id="hm-radar" viewBox="0 0 320 258" role="img" aria-label="五维陪读画像雷达图"></svg>
        </div>

        <!-- 6 第二人格 -->
        <div class="hm-block">
            <div class="hm-second">
                <div class="hm-second-emoji" id="hm-second-emoji" aria-hidden="true"></div>
                <div class="hm-second-body">
                    <p class="hm-second-label">你的隐藏第二人格</p>
                    <p class="hm-second-name" id="hm-second-name"></p>
                    <p class="hm-second-desc" id="hm-second-desc">换个答法，TA 可能会反超</p>
                </div>
                <div class="hm-second-pct" id="hm-second-pct"></div>
            </div>
        </div>

        <!-- 7 转化区 -->
        <div class="hm-block">
            <p class="hm-slogan">吼是本能，陪是选择——先给今晚留点余地</p>
            <ul class="hm-links">
                <li>
                    <a class="hm-link" href="timetable.php">
                        <span class="hm-link-ico" style="background:#FBE0A0">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="10" y="16" width="44" height="38" rx="5" fill="none" stroke="#4A3418" stroke-width="5"/>
                                <line x1="10" y1="28" x2="54" y2="28" stroke="#4A3418" stroke-width="5"/>
                                <line x1="24" y1="11" x2="24" y2="20" stroke="#4A3418" stroke-width="5" stroke-linecap="round"/>
                                <line x1="40" y1="11" x2="40" y2="20" stroke="#4A3418" stroke-width="5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="hm-link-t">课表生成器</span><br>
                            <span class="hm-link-d">给娃排一张有留白的作业时间表</span>
                        </span>
                        <svg class="hm-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="hm-link" href="course.php">
                        <span class="hm-link-ico" style="background:#D9EBE2">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <path d="M10 14 h18 a6 6 0 0 1 6 6 v30 a6 6 0 0 0 -6 -6 H10 Z" fill="none" stroke="#4A3418" stroke-width="5" stroke-linejoin="round"/>
                                <path d="M54 14 H36 a6 6 0 0 0 -6 6 v30 a6 6 0 0 1 6 -6 h18 Z" fill="none" stroke="#4A3418" stroke-width="5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="hm-link-t">课件</span><br>
                            <span class="hm-link-d">用课件让娃自己学一会儿，你喘口气</span>
                        </span>
                        <svg class="hm-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="hm-link" href="more.php#games">
                        <span class="hm-link-ico" style="background:#E7DEF6">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="8" y="20" width="48" height="28" rx="12" fill="none" stroke="#4A3418" stroke-width="5"/>
                                <line x1="18" y1="29" x2="18" y2="39" stroke="#4A3418" stroke-width="5" stroke-linecap="round"/>
                                <line x1="13" y1="34" x2="23" y2="34" stroke="#4A3418" stroke-width="5" stroke-linecap="round"/>
                                <circle cx="44" cy="31" r="3.4" fill="#4A3418"/>
                                <circle cx="51" cy="38" r="3.4" fill="#4A3418"/>
                            </svg>
                        </span>
                        <span>
                            <span class="hm-link-t">课前小游戏</span><br>
                            <span class="hm-link-d">让娃先去放电 10 分钟，回来再战</span>
                        </span>
                        <svg class="hm-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
            </ul>
            <p class="hm-login-hint"><a href="loginpage.php">登录</a> 绑定班级，看娃的学习动态</p>
        </div>

        <!-- 8 操作区 -->
        <div class="hm-block hm-actions">
            <button type="button" class="hm-btn hm-btn--tv" id="hm-share-btn">生成分享卡片</button>
            <button type="button" class="hm-btn hm-btn--ghost" id="hm-again-btn">重新测试</button>
        </div>

        <!-- 9 免责 -->
        <p class="hm-note">仅供娱乐参考 · 本测试不采集任何个人信息</p>
    </section>
</div>

<!-- ============ 状态四：分享卡片预览 ============ -->
<div class="hm-modal" id="hm-share" role="dialog" aria-modal="true" aria-label="分享卡片预览">
    <div class="hm-modal-box">
        <div class="hm-modal-head">
            <span>你的分享卡片</span>
            <button type="button" class="hm-x" id="hm-share-close" aria-label="关闭">&times;</button>
        </div>
        <img class="hm-share-img" id="hm-share-img" alt="陪读精神状态分享卡片">
        <p class="hm-modal-hint" id="hm-share-hint">正在生成…</p>
        <button type="button" class="hm-btn hm-btn--ghost" id="hm-share-ok">完成</button>
    </div>
</div>

<script>
window.HMS = {
    siteName: <?php echo json_encode($OJ_NAME, $hm_json_flags); ?>
};
</script>
<!-- 通关礼花：canvas-confetti CDN + 项目公共 game_confetti.js（CDN 失败则静默降级） -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/game_confetti.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qrcode.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qr_helper.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/homework_mood_test_data.js"></script>
<script>
(function () {
    'use strict';
    var D = window.HM_DATA;
    if (!D) return;
    var DIM_KEYS = D.DIMENSIONS.map(function (d) { return d.key; });
    var LETTERS = ['A', 'B', 'C', 'D'];
    // 拟声词装饰（纯展示，页面不感知权重）
    var SFX = ['b～d？', '顶嘴', '嗡——', '咔哒', '窸窸窣窣', '嘘——', '哇哦', '扑通'];
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var state = { mode: 'landing', qi: 0, answers: [], locked: false, lastResult: null };

    var $ = function (id) { return document.getElementById(id); };

    /* ---------------- 状态机 ---------------- */
    function show(mode) {
        state.mode = mode;
        ['landing', 'quiz', 'result'].forEach(function (m) {
            $('hm-' + m).classList.toggle('is-on', m === mode);
        });
        var page = document.querySelector('.hm-page');
        window.scrollTo(0, page ? Math.max(0, page.offsetTop - 70) : 0);
    }

    /* ---------------- 落地页 ---------------- */
    function renderLanding() {
        $('hm-total').textContent = D.MATCHED_TOTAL;
        var ul = $('hm-landing-cast');
        ul.innerHTML = '';
        D.PERSONAS.forEach(function (p) {
            var li = document.createElement('li');
            var i = document.createElement('i');
            i.style.background = p.color;
            i.textContent = p.emoji;
            li.appendChild(i);
            li.appendChild(document.createTextNode(p.name));
            ul.appendChild(li);
        });
    }

    /* ---------------- 答题页 ---------------- */
    function renderQuiz(animate) {
        var q = D.QUESTIONS[state.qi];
        $('hm-qnum').textContent = (state.qi + 1) + ' / ' + D.QUESTIONS.length;
        $('hm-back').style.display = state.qi > 0 ? '' : 'none';
        $('hm-sfx').textContent = SFX[state.qi % SFX.length];

        var dots = $('hm-dots');
        dots.innerHTML = '';
        D.QUESTIONS.forEach(function (_, i) {
            var s = document.createElement('span');
            if (i < state.qi) s.className = 'is-done';
            if (i === state.qi) s.className = 'is-cur';
            dots.appendChild(s);
        });

        $('hm-qscene').textContent = '场景 · ' + q.scene;
        $('hm-qtitle').textContent = q.title;

        var ul = $('hm-opts');
        ul.innerHTML = '';
        q.options.forEach(function (opt, i) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'hm-opt';
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

        var card = $('hm-qcard');
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
     * → 固定锚点（0.67~1.00）映射到 MATCH_MIN~MATCH_MAX（70%~97%），越界钳制。
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

        // 个性化理由：intro + raw>0 的高分维度变体（2~3 维）+ outro；绝不取 raw=0 维度的句子
        var order = DIM_KEYS.slice().sort(function (a, b) { return raw[b] - raw[a]; });
        var pos = order.filter(function (k) { return raw[k] > 0; });
        var useN = (pos.length >= 3 && raw[pos[2]] >= raw[pos[0]] * 0.35) ? 3 : 2;
        if (useN > pos.length) useN = pos.length;
        var dims = pos.slice(0, useN);
        var picked = [];
        function takeVariant(dim) {
            var vs = top.desc[dim];
            return (vs && vs.length) ? vs[Math.floor(Math.random() * vs.length)] : '';
        }
        function assemble() {
            var p = [top.desc.intro];
            picked.forEach(function (s) { if (s) p.push(s); });
            p.push(top.desc.outro);
            return p.join('');
        }
        dims.forEach(function (dim) { picked.push(takeVariant(dim)); });
        var reason = assemble();
        // 不足 80 字：先补剩余高分维度，再用高分维度里尚未用过的另一变体补齐
        var bi = dims.length, guard = 0;
        while (reason.length < 80 && guard++ < 12) {
            if (bi < pos.length) {
                picked.push(takeVariant(pos[bi++]));
            } else {
                var filled = false;
                for (var di = 0; di < pos.length && !filled; di++) {
                    var alt = top.desc[pos[di]] || [];
                    for (var vi = 0; vi < alt.length; vi++) {
                        if (picked.indexOf(alt[vi]) === -1) { picked.push(alt[vi]); filled = true; break; }
                    }
                }
                if (!filled) break; // 变体已用尽，防死循环
            }
            reason = assemble();
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
        var page = document.querySelector('.hm-page');
        page.style.setProperty('--hm-accent', top.color);
        page.style.setProperty('--hm-accent-fg', top.fg);

        // 环形
        var arc = $('hm-ring-arc');
        var C = 2 * Math.PI * 63;
        arc.style.strokeDasharray = C.toFixed(2);
        arc.style.strokeDashoffset = C.toFixed(2);

        // 电视机开机一击（一次）
        var screen = $('hm-tv-screen');
        screen.classList.remove('is-on');
        void screen.offsetWidth;
        screen.classList.add('is-on');

        // 称号
        $('hm-avatar').textContent = top.emoji;
        $('hm-name').textContent = top.name;
        $('hm-epithet').textContent = top.epithet;

        // 标签 / 胶囊
        fillList('hm-tags', 'hm-tag', top.tags);
        fillList('hm-pills', 'hm-pill', top.pills);

        // 金句
        $('hm-quote').textContent = top.quote;
        $('hm-quote-by').textContent = '—— ' + top.name + ' 的口头禅';

        // 理由
        $('hm-reason').textContent = res.reason;

        // 雷达
        renderRadar(res.raw, top.color);

        // 第二人格
        $('hm-second-emoji').textContent = res.second.emoji;
        $('hm-second-emoji').style.background = res.second.color;
        $('hm-second-name').textContent = res.second.name;
        $('hm-second-pct').textContent = res.secondPct + '%';
        // 同分时避免"TA 可能会反超"与相同百分比自相矛盾
        $('hm-second-desc').textContent = (res.secondPct >= res.topPct)
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

    var hmRingRaf = 0;
    function revealRing(pct) {
        var arc = $('hm-ring-arc');
        var num = $('hm-pct');
        var C = 2 * Math.PI * 63;
        var target = C * (1 - pct / 100);
        if (hmRingRaf) { cancelAnimationFrame(hmRingRaf); hmRingRaf = 0; } // 重测速进结果页时取消旧动画
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
            if (t < 1) hmRingRaf = requestAnimationFrame(step);
            else { num.textContent = pct; hmRingRaf = 0; }
        }
        arc.style.strokeDashoffset = C.toFixed(2);
        hmRingRaf = requestAnimationFrame(step);
    }

    /* ---------------- 五维雷达（SVG） ---------------- */
    function renderRadar(raw, color) {
        var svg = $('hm-radar');
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
            out.push('<polygon points="' + poly(pts) + '" fill="none" stroke="#E3CFA6" stroke-width="1.5"/>');
        });
        // 轴线
        for (var i = 0; i < n; i++) {
            var p = pt(i, R);
            out.push('<line x1="' + cx + '" y1="' + cy + '" x2="' + p[0].toFixed(1) + '" y2="' + p[1].toFixed(1) + '" stroke="#E3CFA6" stroke-width="1.5"/>');
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
                '" font-size="15" font-weight="700" fill="#4A3418" font-family="PingFang SC, Microsoft YaHei, sans-serif">' +
                D.DIMENSIONS[i].label + '</text>');
        }
        svg.innerHTML = out.join('');
    }

    /* ---------------- 分享卡片（Canvas 750×1334）
     * roundRect / wrapLines / makeQrCanvas 全部用 qr_helper.js 的全局版本，禁止本地拷贝。
     */
    function drawShareCard(res, url) {
        var W = 750, H = 1334;
        var c = document.createElement('canvas');
        c.width = W; c.height = H;
        var g = c.getContext('2d');
        var top = res.top;
        var bold = function (px) { return '800 ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };
        var reg = function (px, w) { return (w || 400) + ' ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };

        // 暖黄渐变底 + 左上台灯光晕
        var bg = g.createLinearGradient(0, 0, 0, H);
        bg.addColorStop(0, '#FFF7E6');
        bg.addColorStop(0.55, '#FFF3DC');
        bg.addColorStop(1, '#FDECC8');
        g.fillStyle = bg; g.fillRect(0, 0, W, H);
        var glow = g.createRadialGradient(120, 40, 10, 120, 40, 420);
        glow.addColorStop(0, 'rgba(246,178,75,.6)');
        glow.addColorStop(1, 'rgba(246,178,75,0)');
        g.fillStyle = glow; g.fillRect(0, 0, W, 620);

        var cx = W / 2;
        var padL = 74, maxW = 602;

        // 顶部站点名
        g.textAlign = 'center'; g.textBaseline = 'alphabetic';
        g.font = reg(28, 700); g.fillStyle = '#8A6A3C';
        g.fillText(((window.HMS && window.HMS.siteName) || '') + ' · 陪读精神状态鉴定', cx, 96);

        // 电视机框（分享卡的视觉锤）
        var tvX = 60, tvY = 132, tvW = 630, tvH = 470;
        roundRect(g, tvX, tvY, tvW, tvH, 30);
        var tvg = g.createLinearGradient(0, tvY, 0, tvY + tvH);
        tvg.addColorStop(0, '#7A4E24'); tvg.addColorStop(1, '#5E3A18');
        g.fillStyle = tvg; g.fill();
        g.lineWidth = 5; g.strokeStyle = '#4A3418'; g.stroke();
        // 天线
        g.strokeStyle = '#4A3418'; g.lineWidth = 7; g.lineCap = 'round';
        g.beginPath(); g.moveTo(cx, tvY); g.lineTo(cx - 78, tvY - 58); g.stroke();
        g.beginPath(); g.moveTo(cx, tvY); g.lineTo(cx + 78, tvY - 54); g.stroke();
        g.fillStyle = '#D9503F'; g.beginPath(); g.arc(cx - 78, tvY - 58, 10, 0, 6.3); g.fill();
        g.fillStyle = '#F6B24B'; g.beginPath(); g.arc(cx + 78, tvY - 54, 10, 0, 6.3); g.fill();
        // 屏幕
        var scX = tvX + 22, scY = tvY + 22, scW = tvW - 44, scH = tvH - 44;
        roundRect(g, scX, scY, scW, scH, 18);
        var scg = g.createLinearGradient(0, scY, 0, scY + scH);
        scg.addColorStop(0, '#FFF8E7'); scg.addColorStop(1, '#FCE7B6');
        g.fillStyle = scg; g.fill();
        g.lineWidth = 4; g.strokeStyle = '#4A3418'; g.stroke();
        // 扫描线（极淡）
        g.save();
        g.beginPath(); g.rect(scX, scY, scW, scH); g.clip();
        g.strokeStyle = 'rgba(74,52,24,.05)'; g.lineWidth = 2;
        for (var sy = scY + 6; sy < scY + scH; sy += 10) {
            g.beginPath(); g.moveTo(scX, sy); g.lineTo(scX + scW, sy); g.stroke();
        }
        g.restore();

        // 屏内：称号
        g.textAlign = 'center';
        g.font = '54px sans-serif';
        g.fillText(top.emoji, cx, scY + 96);
        g.font = reg(27, 700); g.fillStyle = '#8A6A3C';
        g.fillText('你家客厅的常驻主角是', cx, scY + 140);
        g.font = bold(72); g.fillStyle = '#4A3418';
        var nameTxt = top.name;
        if (g.measureText(nameTxt).width > scW - 60) g.font = bold(Math.floor(72 * (scW - 60) / g.measureText(nameTxt).width));
        g.fillText(nameTxt, cx, scY + 224);
        g.font = reg(27, 700); g.fillStyle = top.color;
        g.fillText(top.epithet, cx, scY + 268);

        // 匹配度徽章
        var badgeTxt = '陪读人格匹配度 ' + res.topPct + '%';
        g.font = bold(32);
        var bw = g.measureText(badgeTxt).width + 54, bh = 64;
        var bx = cx - bw / 2, by = scY + 306;
        roundRect(g, bx + 4, by + 5, bw, bh, 32); g.fillStyle = 'rgba(74,52,24,.75)'; g.fill();
        roundRect(g, bx, by, bw, bh, 32);
        g.fillStyle = '#F6B24B'; g.fill();
        g.lineWidth = 4; g.strokeStyle = '#4A3418'; g.stroke();
        g.fillStyle = '#4A3418';
        g.fillText(badgeTxt, cx, by + 44);

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
        var ty = 646, rx = cx - total / 2;
        tags.forEach(function (t, i) {
            roundRect(g, rx, ty, tw[i], 56, 12);
            g.fillStyle = '#FFFDF4'; g.fill();
            g.lineWidth = 3; g.strokeStyle = top.color; g.stroke();
            g.fillStyle = '#4A3418';
            g.textAlign = 'center';
            g.fillText(t, rx + tw[i] / 2, ty + 37);
            rx += tw[i] + gap;
        });

        // 金句便签（胶带 + 微旋转）
        var qTop = 748;
        g.save();
        g.translate(cx, qTop + 92); g.rotate(-0.014);
        roundRect(g, -maxW / 2, -92, maxW, 184, 10);
        g.fillStyle = '#FBE0A0'; g.fill();
        g.lineWidth = 4; g.strokeStyle = '#4A3418'; g.stroke();
        g.fillStyle = 'rgba(232,201,138,.95)'; g.fillRect(-66, -106, 132, 30);
        g.restore();
        g.fillStyle = '#4A3418'; g.textAlign = 'center';
        // 金句最多 2 行：超了先缩字号，保证不与署名行（qTop+162）基线重合
        var qfont = 37;
        g.font = reg(qfont, 800);
        var qlines = wrapLines(g, '「' + top.quote + '」', maxW - 70);
        while (qlines.length > 2 && qfont > 27) {
            qfont -= 2;
            g.font = reg(qfont, 800);
            qlines = wrapLines(g, '「' + top.quote + '」', maxW - 70);
        }
        qlines = qlines.slice(0, 2);
        var qy = qTop + 64;
        qlines.forEach(function (ln) { g.fillText(ln, cx, qy); qy += 48; });
        g.font = reg(25, 700); g.fillStyle = '#8A6A3C';
        g.fillText('—— ' + top.name + ' 的口头禅', cx, qTop + 162);

        // 钩子文案（按 Top1 人格动态取 share，缺失时兜底咆哮帝后版）
        // 字号自适应收进 1 行，副行基线按主行实际底沿 + ≥10px 间隙动态排，防叠印、防撞二维码区
        g.fillStyle = '#D9503F';
        var shareTxt = top.share || '鉴定完毕：我是咆哮帝后，今晚开始立地成佛。';
        var hfont = 31;
        g.font = bold(hfont);
        while (g.measureText(shareTxt).width > maxW && hfont > 22) { hfont -= 1; g.font = bold(hfont); }
        var hy = 1004; // 钩子主行基线（始终 1 行）
        g.fillText(shareTxt, cx, hy);
        var hy2 = hy + Math.round(hfont * 0.3) + 10 + 20; // 主行底沿 + 间隙≥10 + 副行字高 20（hfont≤31 时 hy2≤1043，底沿≈1049，QR 区 1096 起）
        g.font = reg(26, 700); g.fillStyle = '#8A6A3C';
        g.fillText('8 道题见分晓，你家客厅今晚演哪出？', cx, hy2);

        // 底部二维码（qr_helper 全局 makeQrCanvas，主题深棕）
        var qr = makeQrCanvas(url, 144, '#4A3418');
        var qx = padL, qy2 = 1096;
        if (qr) {
            g.imageSmoothingEnabled = false;
            g.drawImage(qr, qx, qy2, 144, 144);
            g.imageSmoothingEnabled = true;
            g.textAlign = 'left';
            g.font = bold(29); g.fillStyle = '#4A3418';
            g.fillText('扫码测一测', qx + 170, qy2 + 58);
            g.font = reg(23, 700); g.fillStyle = '#8A6A3C';
            g.fillText('8 道题，鉴定你的陪读精神状态', qx + 170, qy2 + 98);
        }
        g.textAlign = 'center';
        g.font = reg(22); g.fillStyle = '#A8906A';
        g.fillText('仅供娱乐参考 · 不采集任何个人信息', cx, 1276);

        return c.toDataURL('image/png');
    }

    var sharePrevFocus = null;

    function openShare() {
        var modal = $('hm-share');
        var img = $('hm-share-img');
        var hint = $('hm-share-hint');
        sharePrevFocus = document.activeElement;
        modal.classList.add('is-on');
        $('hm-share-close').focus();
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
        $('hm-share').classList.remove('is-on');
        if (sharePrevFocus && sharePrevFocus.focus) sharePrevFocus.focus();
        sharePrevFocus = null;
    }

    /* ---------------- 绑定 ---------------- */
    $('hm-start').addEventListener('click', startOver);
    $('hm-back').addEventListener('click', back);
    $('hm-again-btn').addEventListener('click', startOver);
    $('hm-share-btn').addEventListener('click', openShare);
    $('hm-share-close').addEventListener('click', closeShare);
    $('hm-share-ok').addEventListener('click', closeShare);
    $('hm-share').addEventListener('click', function (e) { if (e.target === this) closeShare(); });
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
