<?php
$show_title="测一测你真的懂你家孩子吗 - $OJ_NAME";
// 传播型 H5 极简壳：隐藏 OJ 顶栏与页脚主体（header.php/footer.php 按 $hide_chrome 分支，保留骨架+限流+极简署名）
$hide_chrome = true;
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
// 页面无表单、无入库、无用户态数据：仅注入站点名给分享卡片文案（JSON 十六进制转义防注入）
$kc_json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<style>
/* ===== 测一测你真的懂你家孩子吗（前缀 kc-）拍立得 / 成长相册风，移动端 375px 起 ===== */
:root{
    --kc-bg:#FAF3E8; --kc-frame:#FFFDF9; --kc-photo:#F1E7D6; --kc-photo-2:#EADFCB;
    --kc-ink:#3E2C20; --kc-soft:#8C7A67; --kc-brown:#8A5A3B; --kc-tape:#F2C6C2;
    --kc-line:#E6DAC6; --kc-shadow:rgba(62,44,32,.16);
    --kc-accent:#8A5A3B; --kc-accent-fg:#FFF8F0;
    --kc-body:"PingFang SC","Microsoft YaHei",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    --kc-hand:"Kaiti SC","STKaiti","KaiTi","Songti SC",serif;
}
html,body{height:100%} /* 极简壳：header.php 的 html 为 fixed，body 必须 100% 当滚动容器，禁 auto */
.kc-page{
    max-width:560px; margin:0 auto; padding:20px 14px 48px;
    min-height:calc(100vh - 48px); /* 极简壳后仅剩署名行高度 */
    color:var(--kc-ink); font-family:var(--kc-body); line-height:1.65;
    background-color:var(--kc-bg);
    background-image:radial-gradient(120% 55% at 50% -8%, #FFFDF6 0%, rgba(255,253,246,0) 62%);
}
.kc-page *{box-sizing:border-box}
.kc-state{display:none}
.kc-state.is-on{display:block}
.kc-h3{
    position:relative; display:block; margin:0 0 10px; padding-left:17px;
    font-size:1.04rem; font-weight:800; color:var(--kc-ink); letter-spacing:.02em;
}
.kc-h3::before{
    content:""; position:absolute; left:0; top:.42em; width:14px; height:5px;
    background:var(--kc-tape); transform:rotate(-8deg); box-shadow:0 1px 2px var(--kc-shadow);
}
.kc-btn{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;min-height:52px;padding:10px 16px;border-radius:6px;
    border:2px solid var(--kc-ink);font-family:var(--kc-body);font-weight:800;font-size:1.02rem;
    cursor:pointer;text-decoration:none;transition:transform .12s ease, box-shadow .12s ease;
}
.kc-btn:active{transform:translate(2px,2px);box-shadow:none!important}
.kc-btn:focus-visible,a.kc-btn:focus-visible{outline:3px solid var(--kc-brown);outline-offset:3px}
.kc-btn--go{background:var(--kc-brown);color:#FFF8F0;font-size:1.14rem;box-shadow:0 4px 0 #63402A}
.kc-btn--mint{background:var(--kc-tape);color:#5A3426;box-shadow:0 4px 0 #D9A39E}
.kc-btn--ghost{background:var(--kc-frame);color:var(--kc-ink);box-shadow:0 3px 0 rgba(62,44,32,.28)}

/* ---- 相纸白框 + 胶带贴角（全页唯一重装饰） ---- */
.kc-pola{position:relative;background:var(--kc-frame);padding:14px 14px 16px;
    border:1px solid rgba(62,44,32,.12);border-radius:3px;
    box-shadow:0 4px 0 rgba(62,44,32,.08), 0 14px 28px rgba(62,44,32,.13)}
.kc-pola::before,.kc-pola::after{
    content:"";position:absolute;top:-13px;width:92px;height:26px;
    background:rgba(242,198,194,.88);
    border-left:1px dashed rgba(138,90,59,.28);border-right:1px dashed rgba(138,90,59,.28);
    box-shadow:0 2px 5px rgba(62,44,32,.14);
}
.kc-pola::before{left:-26px;transform:rotate(-24deg)}
.kc-pola::after{right:-26px;transform:rotate(22deg)}
.kc-photo{
    position:relative;background:linear-gradient(160deg,var(--kc-photo) 0%,var(--kc-photo-2) 100%);
    border-radius:2px;padding:26px 18px 22px;text-align:center;overflow:hidden;
}
.kc-photo::after{ /* 相角淡影，模拟相纸压纹 */
    content:"";position:absolute;inset:0;pointer-events:none;
    background:linear-gradient(180deg,rgba(255,255,255,.35),rgba(62,44,32,.05));
}
.kc-caption{
    margin:12px 2px 2px;min-height:26px;text-align:center;
    font-family:var(--kc-hand);font-size:1rem;color:var(--kc-brown);letter-spacing:.06em;
}

/* ---- 落地页 ---- */
.kc-cover{transform:rotate(-.8deg);margin-bottom:26px}
.kc-cover .kc-photo{padding:30px 18px 26px}
.kc-kicker{margin:0 0 10px;font-family:var(--kc-hand);font-size:.94rem;color:var(--kc-brown);letter-spacing:.1em}
.kc-title{
    position:relative;z-index:1;
    font-weight:800;font-size:clamp(1.78rem,7.8vw,2.3rem);
    line-height:1.32;margin:0;color:var(--kc-ink);letter-spacing:.01em;
}
.kc-title mark{background:linear-gradient(transparent 54%, var(--kc-tape) 54%);color:inherit;padding:0 .06em}
.kc-sub{position:relative;z-index:1;margin:14px auto 0;max-width:24em;font-size:.97rem;color:#5C4B3C}
.kc-count{
    display:flex;align-items:baseline;justify-content:center;gap:8px;flex-wrap:wrap;
    margin:20px 0 4px;padding:12px 16px;border:1.5px dashed var(--kc-brown);
    border-radius:4px;background:#FFFCF4;font-weight:700;color:var(--kc-ink);
    transform:rotate(.5deg);
}
.kc-count b{font-weight:800;font-size:2.4rem;line-height:1;color:var(--kc-brown)}
.kc-avatars{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin:16px 0 4px;padding:0;list-style:none}
.kc-avatars li{
    display:flex;align-items:center;gap:7px;padding:6px 11px 6px 8px;
    background:var(--kc-frame);border:1px solid rgba(62,44,32,.2);border-radius:3px;
    font-size:.83rem;font-weight:800;box-shadow:0 2px 6px rgba(62,44,32,.1);
}
.kc-avatars li:nth-child(odd){transform:rotate(-2deg)}
.kc-avatars li:nth-child(even){transform:rotate(1.6deg)}
.kc-avatars li i{
    display:grid;place-items:center;width:26px;height:26px;border-radius:3px;
    border:1px solid rgba(62,44,32,.25);font-style:normal;font-size:.95rem;
}
.kc-meta{margin:12px 0 0;text-align:center;font-size:.86rem;color:var(--kc-soft)}
.kc-landing-note{margin:14px 0 0;text-align:center;font-family:var(--kc-hand);font-size:.85rem;color:var(--kc-soft)}

/* ---- 答题页 ---- */
.kc-qbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px}
.kc-dots{display:flex;gap:6px;flex:1}
.kc-dots span{flex:1;height:7px;border-radius:99px;background:#E9DFCB;transition:background .2s ease}
.kc-dots span.is-done{background:var(--kc-brown)}
.kc-dots span.is-cur{background:var(--kc-tape);box-shadow:0 0 0 2px rgba(138,90,59,.3)}
.kc-qnum{font-size:.85rem;font-weight:800;color:var(--kc-soft);white-space:nowrap}
.kc-back{
    background:none;border:none;padding:10px 8px;min-height:44px;font:inherit;font-size:.88rem;font-weight:700;
    color:var(--kc-brown);cursor:pointer;text-decoration:underline;
}
.kc-back:focus-visible{outline:3px solid var(--kc-brown);outline-offset:2px;border-radius:6px}
.kc-qcard{margin-bottom:16px;transform:rotate(-.5deg)}
.kc-qcard.is-in{animation:kc-slide .26s ease both}
@keyframes kc-slide{from{opacity:0;transform:translateX(22px) rotate(-.5deg)}to{opacity:1;transform:rotate(-.5deg)}}
.kc-qscene{
    display:inline-block;margin:0 0 10px;padding:3px 10px;
    font-family:var(--kc-hand);font-size:.86rem;font-weight:700;color:var(--kc-brown);
    background:rgba(255,255,255,.66);border:1px solid rgba(138,90,59,.35);border-radius:2px;
    transform:rotate(-1.5deg);letter-spacing:.08em;
}
.kc-qtitle{
    position:relative;z-index:1;
    font-weight:800;font-size:1.28rem;line-height:1.55;
    margin:0;color:var(--kc-ink);text-align:center;
}
.kc-opts{display:flex;flex-direction:column;gap:12px;margin:0;padding:0;list-style:none}
.kc-opt{
    position:relative;display:flex;align-items:center;gap:12px;width:100%;min-height:56px;
    padding:12px 14px 12px 20px;text-align:left;background:var(--kc-frame);color:var(--kc-ink);
    border:1px solid rgba(62,44,32,.2);border-radius:3px;
    font:inherit;font-size:.98rem;font-weight:700;line-height:1.5;cursor:pointer;
    box-shadow:0 3px 10px rgba(62,44,32,.09);
    transition:border-color .15s ease, background .15s ease, transform .12s ease;
}
.kc-opt::before{ /* 选项左侧小胶带 */
    content:"";position:absolute;left:-8px;top:50%;width:22px;height:14px;margin-top:-7px;
    background:rgba(242,198,194,.9);transform:rotate(-8deg);
    box-shadow:0 1px 3px rgba(62,44,32,.16);
}
.kc-opt:hover{border-color:var(--kc-brown);background:#fff}
.kc-opt:focus-visible{outline:3px solid var(--kc-brown);outline-offset:2px}
.kc-opt.is-picked{
    border-color:var(--kc-brown);background:#FFF6EC;transform:translate(2px,2px);
    box-shadow:0 2px 0 rgba(62,44,32,.18);
}
.kc-opt b{
    flex:0 0 auto;display:grid;place-items:center;width:30px;height:30px;border-radius:3px;
    border:1.5px solid var(--kc-brown);color:var(--kc-brown);font-weight:800;font-size:.96rem;
    background:#FBEFE4;font-family:var(--kc-hand);
}
.kc-opt.is-picked b{border-color:var(--kc-brown);color:#FFF8F0;background:var(--kc-brown)}
.kc-qfoot{margin:14px 0 0;text-align:center;font-family:var(--kc-hand);font-size:.85rem;color:var(--kc-soft)}

/* ---- 结果页 ---- */
.kc-block{margin-bottom:18px}
.kc-hero{display:flex;flex-direction:column;align-items:center;gap:20px}
.kc-pola-hero{width:100%;max-width:340px;transform:rotate(-1.4deg);animation:kc-develop .7s ease both}
@keyframes kc-develop{from{opacity:0;transform:rotate(-1.4deg) scale(.94);filter:saturate(.3)}to{opacity:1;transform:rotate(-1.4deg) scale(1);filter:none}}
.kc-hero-photo{display:flex;flex-direction:column;align-items:center;gap:6px;padding:34px 14px 28px}
.kc-hero-emoji{
    position:relative;z-index:1;width:104px;height:104px;display:grid;place-items:center;
    font-size:3.4rem;background:var(--kc-frame);border:1px solid rgba(62,44,32,.18);border-radius:4px;
    box-shadow:0 6px 18px rgba(62,44,32,.18);transform:rotate(3deg);
}
.kc-lead{position:relative;z-index:1;margin:8px 0 0;font-family:var(--kc-hand);font-size:.94rem;color:#6A5748;letter-spacing:.08em}
.kc-name{
    position:relative;z-index:1;
    font-weight:800;font-size:2.05rem;line-height:1.25;
    margin:2px 0 4px;color:var(--kc-ink);letter-spacing:.02em;
}
.kc-epithet{position:relative;z-index:1;margin:0;font-size:.98rem;font-weight:700;color:var(--kc-accent)}
.kc-ring-wrap{position:relative;width:170px;text-align:center}
.kc-ring{width:170px;height:170px;display:block;transform:rotate(-90deg)}
.kc-ring circle{fill:none;stroke-width:13;stroke-linecap:round}
.kc-ring .kc-ring-bg{stroke:#EADFCA}
.kc-ring .kc-ring-arc{stroke:var(--kc-accent)}
.kc-ring-num{
    position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;
}
.kc-ring-num span{font-weight:800;font-size:2.8rem;line-height:1;color:var(--kc-ink)}
.kc-ring-num i{font-style:normal;font-size:.86rem;font-weight:800;color:var(--kc-soft)}
.kc-ring-cap{margin:6px 0 0;font-family:var(--kc-hand);font-size:.88rem;color:var(--kc-soft);text-align:center}
.kc-tags,.kc-pills{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin:0;padding:0;list-style:none}
.kc-tags{margin-bottom:10px}
.kc-tag{
    display:inline-block;padding:6px 14px;border-radius:3px;background:var(--kc-frame);
    color:var(--kc-ink);border:1.5px solid var(--kc-accent);font-weight:800;font-size:.92rem;
    box-shadow:0 3px 8px rgba(62,44,32,.12);
}
.kc-tags li:nth-child(1){transform:rotate(-2deg)}
.kc-tags li:nth-child(2){transform:rotate(1.5deg)}
.kc-tags li:nth-child(3){transform:rotate(-1deg)}
.kc-pill{
    display:inline-block;padding:5px 12px;border:1.5px dashed var(--kc-brown);
    border-radius:999px;color:var(--kc-soft);font-size:.85rem;font-weight:700;background:#FFFCF4;
    font-family:var(--kc-hand);
}
/* 金句：贴着胶带的相纸便签 */
.kc-quote{
    position:relative;padding:24px 18px 16px;background:var(--kc-frame);border-radius:3px;
    border:1px solid rgba(62,44,32,.16);box-shadow:0 6px 18px rgba(62,44,32,.13);
    transform:rotate(-.7deg);
}
.kc-quote::before{
    content:"";position:absolute;top:-13px;left:50%;width:118px;height:28px;margin-left:-59px;
    background:rgba(242,198,194,.9);transform:rotate(2deg);
    border-left:1px dashed rgba(138,90,59,.3);border-right:1px dashed rgba(138,90,59,.3);
    box-shadow:0 2px 5px rgba(62,44,32,.14);
}
.kc-quote blockquote{
    position:relative;margin:0;font-family:var(--kc-hand);
    font-weight:700;font-size:1.26rem;line-height:1.7;color:var(--kc-ink);
}
.kc-quote cite{display:block;margin-top:8px;font-style:normal;font-size:.84rem;font-weight:700;color:var(--kc-soft);text-align:right;font-family:var(--kc-body)}
/* 理由：铅笔写在相册背面 */
.kc-reason{
    padding:15px 16px;border:1.5px dashed rgba(138,90,59,.5);border-radius:3px;
    background:repeating-linear-gradient(#FFFCF4 0 32px, #EFE6D3 32px 33px);
    font-size:.95rem;line-height:32px;color:#4A3A2C;
}
.kc-radar{width:100%;height:auto;display:block}
.kc-second{
    display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:3px;
    background:var(--kc-frame);border:1px solid rgba(62,44,32,.18);
    box-shadow:0 4px 12px rgba(62,44,32,.1);
}
.kc-second-emoji{
    flex:0 0 auto;width:52px;height:52px;border-radius:4px;display:grid;place-items:center;
    font-size:1.6rem;border:1px solid rgba(62,44,32,.25);background:#F7F1E4;
    transform:rotate(-3deg);
}
.kc-second-body{flex:1;min-width:0}
.kc-second-body p{margin:0}
.kc-second-label{font-family:var(--kc-hand);font-size:.82rem;font-weight:700;color:var(--kc-brown);letter-spacing:.06em}
.kc-second-name{font-weight:800;font-size:1.2rem;color:var(--kc-ink)}
.kc-second-desc{font-size:.84rem;color:var(--kc-soft)}
.kc-second-pct{flex:0 0 auto;font-weight:800;font-size:1.4rem;color:var(--kc-accent)}

/* ---- 转化区：今晚就能做的一件小事（本款亮点） ---- */
.kc-tonight{
    position:relative;margin:0 0 14px;padding:16px 16px 14px;border-radius:3px;
    background:#FDEFEA;border:1.5px solid var(--kc-tape);
    box-shadow:0 4px 14px rgba(62,44,32,.1);
}
.kc-tonight::before{
    content:"";position:absolute;top:-12px;left:22px;width:74px;height:24px;
    background:rgba(242,198,194,.95);transform:rotate(-4deg);
    box-shadow:0 2px 4px rgba(62,44,32,.14);
}
.kc-tonight-label{
    margin:0 0 6px;font-family:var(--kc-hand);font-size:1.02rem;font-weight:700;
    color:#9A5B4C;letter-spacing:.06em;
}
.kc-tonight-text{margin:0;font-size:.96rem;line-height:1.7;color:#4A3325;font-weight:600}
.kc-links{display:flex;flex-direction:column;gap:10px;margin:0;padding:0;list-style:none}
.kc-link{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:3px;
    background:var(--kc-frame);border:1px solid rgba(62,44,32,.18);
    box-shadow:0 3px 10px rgba(62,44,32,.08);
    text-decoration:none;color:var(--kc-ink);
    transition:border-color .15s ease, transform .15s ease;
}
.kc-link:hover{border-color:var(--kc-brown);text-decoration:none;transform:translateY(-2px)}
.kc-link:focus-visible{outline:3px solid var(--kc-brown);outline-offset:2px}
.kc-link-ico{
    flex:0 0 auto;width:44px;height:44px;border-radius:4px;display:grid;place-items:center;
    border:1px solid rgba(62,44,32,.2);background:#FBF3E4;
}
.kc-link-ico svg{width:24px;height:24px}
.kc-link-t{font-weight:800;font-size:.98rem}
.kc-link-d{font-size:.82rem;color:var(--kc-soft);line-height:1.45}
.kc-link-chev{margin-left:auto;color:var(--kc-soft);flex:0 0 auto}
.kc-login-hint{
    margin:12px 0 0;padding:11px 14px;border-radius:3px;background:#FFFCF4;
    border:1.5px dashed var(--kc-brown);font-size:.88rem;font-weight:700;color:var(--kc-soft);text-align:center;
}
.kc-login-hint a{color:var(--kc-brown);font-weight:800}
.kc-actions{display:flex;flex-direction:column;gap:10px}
.kc-note{margin:16px 0 0;text-align:center;font-family:var(--kc-hand);font-size:.8rem;color:var(--kc-soft)}

/* ---- 分享卡片弹层 ---- */
.kc-modal{
    position:fixed;inset:0;z-index:2000;display:none;align-items:flex-start;justify-content:center;
    padding:24px 14px;overflow-y:auto;background:rgba(62,44,32,.62);
}
.kc-modal.is-on{display:flex}
.kc-modal-box{
    width:100%;max-width:420px;background:var(--kc-bg);border:1.5px solid var(--kc-ink);
    border-radius:8px;box-shadow:0 10px 34px rgba(0,0,0,.35);padding:14px 14px 18px;
}
.kc-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.kc-modal-head span{font-weight:800;font-size:1.1rem;color:var(--kc-ink)}
.kc-x{
    width:44px;height:44px;border-radius:6px;border:2px solid var(--kc-ink);background:var(--kc-frame);
    font-size:1.3rem;line-height:1;color:var(--kc-ink);cursor:pointer;
}
.kc-x:focus-visible{outline:3px solid var(--kc-brown);outline-offset:2px}
.kc-share-img{display:block;width:100%;height:auto;border-radius:4px;border:1.5px solid rgba(62,44,32,.3);background:#fff}
.kc-modal-hint{margin:10px 0 12px;text-align:center;font-size:.86rem;font-weight:700;color:var(--kc-soft)}

@media (min-width: 640px){
    .kc-page{padding-top:28px}
    .kc-cover .kc-photo{padding:36px 32px 30px}
}

@media (prefers-reduced-motion: reduce){
    .kc-page *,.kc-page *::before,.kc-page *::after{
        animation-duration:.001s!important;animation-iteration-count:1!important;transition-duration:.001s!important;
    }
}
</style>

<div class="kc-page">

    <!-- ============ 状态一：落地页 ============ -->
    <section id="kc-landing" class="kc-state is-on" aria-label="测试介绍">
        <div class="kc-pola kc-cover">
            <div class="kc-photo">
                <p class="kc-kicker">成长相册 · 亲子默契特辑</p>
                <h1 class="kc-title">测一测，<br>你真的<mark>懂你家孩子</mark>吗？</h1>
                <p class="kc-sub">朝夕相处 ≠ 真的了解。8 道生活小题，看看孩子心里的你，和你以为的你，差多远。</p>
            </div>
            <p class="kc-caption">「别急着回答，你未必是最懂娃的人」</p>
        </div>
        <p class="kc-count">已有 <b id="kc-total">8642</b> 位家长翻过这本相册</p>
        <ul class="kc-avatars" id="kc-landing-avatars"></ul>
        <button type="button" class="kc-btn kc-btn--go" id="kc-start">翻开第一页</button>
        <p class="kc-meta">8 道题 · 约 1 分钟 · 无需登录 · 可以和另一半一起测</p>
        <p class="kc-landing-note">纯属娱乐，结果仅供开心参考</p>
    </section>

    <!-- ============ 状态二：答题页 ============ -->
    <section id="kc-quiz" class="kc-state" aria-label="答题">
        <div class="kc-qbar">
            <div class="kc-dots" id="kc-dots" aria-hidden="true"></div>
            <span class="kc-qnum" id="kc-qnum">1 / 8</span>
        </div>
        <button type="button" class="kc-back" id="kc-back" style="display:none">← 上一题</button>
        <div class="kc-pola kc-qcard" id="kc-qcard">
            <div class="kc-photo">
                <span class="kc-qscene" id="kc-qscene"></span>
                <h2 class="kc-qtitle" id="kc-qtitle"></h2>
            </div>
            <p class="kc-caption" id="kc-qcap">第 1 张相片背面</p>
        </div>
        <ul class="kc-opts" id="kc-opts"></ul>
        <p class="kc-qfoot">凭第一直觉选，孩子比你想的更简单</p>
    </section>

    <!-- ============ 状态三：结果页（顺序固定） ============ -->
    <section id="kc-result" class="kc-state" aria-label="测试结果">

        <!-- 1 拍立得人格照（视觉锤） + 匹配度环 -->
        <div class="kc-block kc-hero">
            <div class="kc-pola kc-pola-hero" id="kc-hero">
                <div class="kc-photo kc-hero-photo">
                    <div class="kc-hero-emoji" id="kc-avatar" aria-hidden="true"></div>
                    <p class="kc-lead">你家的这一页写的是——</p>
                    <h2 class="kc-name" id="kc-name"></h2>
                    <p class="kc-epithet" id="kc-epithet"></p>
                </div>
                <p class="kc-caption" id="kc-hero-cap">翻到这一页，答案自己浮出来了</p>
            </div>
            <div class="kc-ring-wrap">
                <svg class="kc-ring" viewBox="0 0 170 170" aria-hidden="true">
                    <circle class="kc-ring-bg" cx="85" cy="85" r="71"/>
                    <circle class="kc-ring-arc" id="kc-ring-arc" cx="85" cy="85" r="71"/>
                </svg>
                <div class="kc-ring-num"><span id="kc-pct">0</span><i>匹配度 %</i></div>
                <p class="kc-ring-cap">和这一页的默契程度</p>
            </div>
        </div>

        <!-- 2 人设标签 + 关键词胶囊 -->
        <div class="kc-block">
            <ul class="kc-tags" id="kc-tags"></ul>
            <ul class="kc-pills" id="kc-pills"></ul>
        </div>

        <!-- 3 金句 -->
        <div class="kc-block kc-quote">
            <blockquote id="kc-quote"></blockquote>
            <cite id="kc-quote-by"></cite>
        </div>

        <!-- 4 个性化理由 -->
        <div class="kc-block">
            <h3 class="kc-h3">相册背面的铅笔字</h3>
            <div class="kc-reason" id="kc-reason"></div>
        </div>

        <!-- 5 五维雷达 -->
        <div class="kc-block">
            <h3 class="kc-h3">你家的五维默契底片</h3>
            <svg class="kc-radar" id="kc-radar" viewBox="0 0 320 258" role="img" aria-label="五维默契雷达图"></svg>
        </div>

        <!-- 6 第二人格（隐藏款） -->
        <div class="kc-block">
            <div class="kc-second">
                <div class="kc-second-emoji" id="kc-second-emoji" aria-hidden="true"></div>
                <div class="kc-second-body">
                    <p class="kc-second-label">压在箱底的第二张底片</p>
                    <p class="kc-second-name" id="kc-second-name"></p>
                    <p class="kc-second-desc" id="kc-second-desc">换个答法，TA 可能会反超</p>
                </div>
                <div class="kc-second-pct" id="kc-second-pct"></div>
            </div>
        </div>

        <!-- 7 转化区：今晚就能做的一件小事 + 3 导流 -->
        <div class="kc-block">
            <div class="kc-tonight">
                <p class="kc-tonight-label">今晚就能做的一件小事</p>
                <p class="kc-tonight-text" id="kc-tonight"></p>
            </div>
            <ul class="kc-links">
                <li>
                    <a class="kc-link" href="more.php#games">
                        <span class="kc-link-ico" style="background:#FBE7DC">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="8" y="20" width="48" height="28" rx="12" fill="none" stroke="#8A5A3B" stroke-width="5"/>
                                <line x1="18" y1="29" x2="18" y2="39" stroke="#8A5A3B" stroke-width="5" stroke-linecap="round"/>
                                <line x1="13" y1="34" x2="23" y2="34" stroke="#8A5A3B" stroke-width="5" stroke-linecap="round"/>
                                <circle cx="44" cy="31" r="3.4" fill="#8A5A3B"/>
                                <circle cx="51" cy="38" r="3.4" fill="#8A5A3B"/>
                            </svg>
                        </span>
                        <span>
                            <span class="kc-link-t">课前小游戏当破冰</span><br>
                            <span class="kc-link-d">陪娃先玩一局，话匣子比讲道理好开</span>
                        </span>
                        <svg class="kc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="kc-link" href="course.php">
                        <span class="kc-link-ico" style="background:#F4EEE0">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <path d="M10 14 h18 a6 6 0 0 1 6 6 v30 a6 6 0 0 0 -6 -6 H10 Z" fill="none" stroke="#8A5A3B" stroke-width="5" stroke-linejoin="round"/>
                                <path d="M54 14 H36 a6 6 0 0 0 -6 6 v30 a6 6 0 0 1 6 -6 h18 Z" fill="none" stroke="#8A5A3B" stroke-width="5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="kc-link-t">课件亲子共学</span><br>
                            <span class="kc-link-d">挑个课件一起看，让他当你的小老师</span>
                        </span>
                        <svg class="kc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="kc-link" href="loginpage.php">
                        <span class="kc-link-ico" style="background:#E7EFE9">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="10" y="12" width="44" height="40" rx="5" fill="none" stroke="#8A5A3B" stroke-width="5"/>
                                <line x1="20" y1="42" x2="20" y2="30" stroke="#8A5A3B" stroke-width="5" stroke-linecap="round"/>
                                <line x1="32" y1="42" x2="32" y2="22" stroke="#8A5A3B" stroke-width="5" stroke-linecap="round"/>
                                <line x1="44" y1="42" x2="44" y2="34" stroke="#8A5A3B" stroke-width="5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="kc-link-t">登录看学习记录</span><br>
                            <span class="kc-link-d">看看他真实的做题轨迹，比问「今天怎么样」更管用</span>
                        </span>
                        <svg class="kc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
            </ul>
            <p class="kc-login-hint"><a href="loginpage.php">登录</a> 后绑定班级，随时翻看孩子的学习记录</p>
        </div>

        <!-- 8 操作区 -->
        <div class="kc-block kc-actions">
            <button type="button" class="kc-btn kc-btn--mint" id="kc-share-btn">生成分享卡片</button>
            <button type="button" class="kc-btn kc-btn--ghost" id="kc-again-btn">重新测试</button>
        </div>

        <!-- 9 免责 -->
        <p class="kc-note">仅供娱乐参考 · 本测试不采集任何个人信息</p>
    </section>
</div>

<!-- ============ 状态四：分享卡片预览 ============ -->
<div class="kc-modal" id="kc-share" role="dialog" aria-modal="true" aria-label="分享卡片预览">
    <div class="kc-modal-box">
        <div class="kc-modal-head">
            <span>你的分享卡片</span>
            <button type="button" class="kc-x" id="kc-share-close" aria-label="关闭">&times;</button>
        </div>
        <img class="kc-share-img" id="kc-share-img" alt="懂孩子测试分享卡片">
        <p class="kc-modal-hint" id="kc-share-hint">正在生成…</p>
        <button type="button" class="kc-btn kc-btn--ghost" id="kc-share-ok">完成</button>
    </div>
</div>

<script>
window.KCS = {
    siteName: <?php echo json_encode($OJ_NAME, $kc_json_flags); ?>
};
</script>
<!-- 通关礼花：canvas-confetti CDN + 项目公共 game_confetti.js（CDN 失败则静默降级） -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/game_confetti.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qrcode.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qr_helper.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/know_child_test_data.js"></script>
<script>
(function () {
    'use strict';
    var D = window.KC_DATA;
    if (!D) return;
    var DIM_KEYS = D.DIMENSIONS.map(function (d) { return d.key; });
    var LETTERS = ['A', 'B', 'C', 'D'];
    // 可放礼花的正向人格（lingxi 心有灵犀 / momo 默默守护 / yongli 用力过猛）
    var CONFETTI_POSITIVE = { lingxi: 1, momo: 1, yongli: 1 };
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var state = { mode: 'landing', qi: 0, answers: [], locked: false, lastResult: null };

    var $ = function (id) { return document.getElementById(id); };

    /* ---------------- 状态机 ---------------- */
    function show(mode) {
        state.mode = mode;
        ['landing', 'quiz', 'result'].forEach(function (m) {
            $('kc-' + m).classList.toggle('is-on', m === mode);
        });
        var page = document.querySelector('.kc-page');
        window.scrollTo(0, page ? Math.max(0, page.offsetTop - 70) : 0);
    }

    /* ---------------- 落地页 ---------------- */
    function renderLanding() {
        $('kc-total').textContent = D.MATCHED_TOTAL;
        var ul = $('kc-landing-avatars');
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
        $('kc-qnum').textContent = (state.qi + 1) + ' / ' + D.QUESTIONS.length;
        $('kc-back').style.display = state.qi > 0 ? '' : 'none';

        var dots = $('kc-dots');
        dots.innerHTML = '';
        D.QUESTIONS.forEach(function (_, i) {
            var s = document.createElement('span');
            if (i < state.qi) s.className = 'is-done';
            if (i === state.qi) s.className = 'is-cur';
            dots.appendChild(s);
        });

        $('kc-qscene').textContent = '场景 · ' + q.scene;
        $('kc-qtitle').textContent = q.title;
        $('kc-qcap').textContent = '第 ' + (state.qi + 1) + ' 张相片 · ' + q.scene;

        var ul = $('kc-opts');
        ul.innerHTML = '';
        q.options.forEach(function (opt, i) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'kc-opt';
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

        var card = $('kc-qcard');
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

        // 个性化理由：得分最高 2~3 个维度（不足 80 字则升到 3 维）
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
        var page = document.querySelector('.kc-page');
        page.style.setProperty('--kc-accent', top.color);
        page.style.setProperty('--kc-accent-fg', top.fg);

        // 拍立得人格照：强制重播「显影」入场动画（重测时也播一次）
        var hero = $('kc-hero');
        hero.style.animation = 'none';
        void hero.offsetWidth; // 重排以重放动画
        hero.style.animation = '';

        // 环形
        var arc = $('kc-ring-arc');
        var C = 2 * Math.PI * 71;
        arc.style.strokeDasharray = C.toFixed(2);
        arc.style.strokeDashoffset = C.toFixed(2);

        // 人格照
        $('kc-avatar').textContent = top.emoji;
        $('kc-name').textContent = top.name;
        $('kc-epithet').textContent = top.epithet;

        // 标签 / 胶囊
        fillList('kc-tags', 'kc-tag', top.tags);
        fillList('kc-pills', 'kc-pill', top.pills);

        // 金句
        $('kc-quote').textContent = top.quote;
        $('kc-quote-by').textContent = '—— 某页相片背面的字';

        // 理由
        $('kc-reason').textContent = res.reason;

        // 雷达
        renderRadar(res.raw, top.color);

        // 第二人格
        $('kc-second-emoji').textContent = res.second.emoji;
        $('kc-second-name').textContent = res.second.name;
        $('kc-second-pct').textContent = res.secondPct + '%';
        // 同分时避免「TA 可能会反超」与相同百分比自相矛盾
        $('kc-second-desc').textContent = (res.secondPct >= res.topPct)
            ? '和你不相上下，换个答法见分晓' : '换个答法，TA 可能会反超';

        // 今晚就能做的一件小事（愧疚转行动）
        $('kc-tonight').textContent = top.tonight || '';

        show('result');
        revealRing(res.sims[0].pct);
        // 礼花只给正向人格绽放；扎心人格（身在心不在/严慈错位）放礼花调性打架
        if (CONFETTI_POSITIVE[top.key] && typeof window.launchConfetti === 'function') window.launchConfetti();
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

    var kcRingRaf = 0;
    function revealRing(pct) {
        var arc = $('kc-ring-arc');
        var num = $('kc-pct');
        var C = 2 * Math.PI * 71;
        var target = C * (1 - pct / 100);
        if (kcRingRaf) { cancelAnimationFrame(kcRingRaf); kcRingRaf = 0; } // 重测速进结果页时取消旧动画
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
            if (t < 1) kcRingRaf = requestAnimationFrame(step);
            else { num.textContent = pct; kcRingRaf = 0; }
        }
        arc.style.strokeDashoffset = C.toFixed(2);
        kcRingRaf = requestAnimationFrame(step);
    }

    /* ---------------- 五维雷达（SVG） ---------------- */
    function renderRadar(raw, color) {
        var svg = $('kc-radar');
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
            out.push('<polygon points="' + poly(pts) + '" fill="none" stroke="#DED2BC" stroke-width="1.5"/>');
        });
        // 轴线
        for (var i = 0; i < n; i++) {
            var p = pt(i, R);
            out.push('<line x1="' + cx + '" y1="' + cy + '" x2="' + p[0].toFixed(1) + '" y2="' + p[1].toFixed(1) + '" stroke="#DED2BC" stroke-width="1.5"/>');
        }
        // 数据多边形（最小 0.16 半径，避免零值塌成点）
        var dataR = DIM_KEYS.map(function (k) { return Math.max(0.16, raw[k] / max) * R; });
        out.push('<polygon points="' + poly(dataR) + '" fill="' + color + '" fill-opacity=".28" stroke="' + color + '" stroke-width="3" stroke-linejoin="round"/>');
        for (i = 0; i < n; i++) {
            var q = pt(i, dataR[i]);
            out.push('<circle cx="' + q[0].toFixed(1) + '" cy="' + q[1].toFixed(1) + '" r="5" fill="#fff" stroke="' + color + '" stroke-width="3"/>');
        }
        // 轴标签（顶/底标签 dy +12 防 viewBox 裁切）
        for (i = 0; i < n; i++) {
            var lp = pt(i, R + 26);
            var anchor = 'middle';
            if (lp[0] > cx + 8) anchor = 'start';
            else if (lp[0] < cx - 8) anchor = 'end';
            var dy = 5;
            if (lp[1] < cy - 40) dy = 12;
            if (lp[1] > cy + 40) dy = 12;
            out.push('<text x="' + lp[0].toFixed(1) + '" y="' + (lp[1] + dy).toFixed(1) + '" text-anchor="' + anchor +
                '" font-size="15" font-weight="700" fill="#3E2C20" font-family="PingFang SC, Microsoft YaHei, sans-serif">' +
                D.DIMENSIONS[i].label + '</text>');
        }
        svg.innerHTML = out.join('');
    }

    /* ---------------- 分享卡片（Canvas 750×1334，拍立得风） ----------------
     * roundRect / wrapLines / makeQrCanvas 由 js/qr_helper.js 全局提供，禁止本地拷贝。
     */

    function drawShareCard(res, url) {
        var W = 750, H = 1334;
        var c = document.createElement('canvas');
        c.width = W; c.height = H;
        var g = c.getContext('2d');
        var top = res.top;
        var cx = W / 2, padL = 86, maxW = 578;
        var bold = function (px) { return '800 ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };
        var reg = function (px, w) { return (w || 400) + ' ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };
        var hand = function (px, w) { return (w || 700) + ' ' + px + 'px "Kaiti SC", "STKaiti", "KaiTi", serif'; };

        // 暖米底
        g.fillStyle = '#FAF3E8'; g.fillRect(0, 0, W, H);
        // 相纸白框
        roundRect(g, 46, 56, 658, 1220, 8);
        g.fillStyle = '#FFFDF9'; g.fill();
        g.lineWidth = 3; g.strokeStyle = 'rgba(62,44,32,.35)'; g.stroke();
        // 顶角胶带
        g.save();
        g.translate(120, 58); g.rotate(-0.42);
        g.fillStyle = 'rgba(242,198,194,.92)'; g.fillRect(-70, -15, 140, 30); g.restore();
        g.save();
        g.translate(630, 58); g.rotate(0.38);
        g.fillStyle = 'rgba(242,198,194,.92)'; g.fillRect(-70, -15, 140, 30); g.restore();

        g.textAlign = 'center'; g.textBaseline = 'alphabetic';

        // 顶部站点名
        g.font = reg(27, 700); g.fillStyle = '#8C7A67';
        g.fillText(((window.KCS && window.KCS.siteName) || '') + ' · 懂孩子趣味测评', cx, 130);

        // 相片区（拍立得照片）
        roundRect(g, padL, 152, maxW, 300, 4);
        g.fillStyle = '#F1E7D6'; g.fill();
        g.fillStyle = 'rgba(255,255,255,.4)'; g.fillRect(padL, 152, maxW, 70);
        g.font = '400 150px "Apple Color Emoji","Segoe UI Emoji","Noto Color Emoji",sans-serif';
        g.fillText(top.emoji, cx, 372);
        g.font = hand(30, 700); g.fillStyle = '#8A5A3B';
        g.fillText('你家的这一页写的是——', cx, 512);

        // 人格称号
        g.font = bold(88); g.fillStyle = '#3E2C20';
        var nameTxt = top.name;
        if (g.measureText(nameTxt).width > maxW) g.font = bold(Math.floor(88 * maxW / g.measureText(nameTxt).width));
        g.fillText(nameTxt, cx, 602);
        g.font = reg(27, 700); g.fillStyle = top.color;
        var epTxt = top.epithet;
        if (g.measureText(epTxt).width > maxW) g.font = reg(Math.floor(27 * maxW / g.measureText(epTxt).width), 700);
        g.fillText(epTxt, cx, 646);

        // 匹配度徽章
        var badgeTxt = '人格匹配度 ' + res.sims[0].pct + '%';
        g.font = bold(32);
        var bw = g.measureText(badgeTxt).width + 52, bh = 60;
        var bx = cx - bw / 2, by = 674;
        roundRect(g, bx + 4, by + 5, bw, bh, 30); g.fillStyle = 'rgba(62,44,32,.22)'; g.fill();
        roundRect(g, bx, by, bw, bh, 30);
        g.fillStyle = '#F2C6C2'; g.fill();
        g.lineWidth = 3.5; g.strokeStyle = '#3E2C20'; g.stroke();
        g.fillStyle = '#3E2C20';
        g.fillText(badgeTxt, cx, by + 42);

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
        var ty = 768, rx = cx - total / 2;
        tags.forEach(function (t, i) {
            roundRect(g, rx, ty, tw[i], 54, 6);
            g.fillStyle = '#FFFDF9'; g.fill();
            g.lineWidth = 3; g.strokeStyle = top.color; g.stroke();
            g.fillStyle = '#3E2C20';
            g.fillText(t, rx + tw[i] / 2, ty + 36);
            rx += tw[i] + gap;
        });

        // 金句便签（顶部一条胶带）
        var qTop = 866;
        g.save();
        g.translate(cx, qTop + 84); g.rotate(-0.014);
        roundRect(g, -maxW / 2, -84, maxW, 168, 5);
        g.fillStyle = '#FFFCF4'; g.fill();
        g.lineWidth = 3; g.strokeStyle = 'rgba(138,90,59,.5)'; g.stroke();
        g.fillStyle = 'rgba(242,198,194,.95)'; g.fillRect(-62, -98, 124, 28);
        g.restore();
        g.fillStyle = '#3E2C20';
        // 金句最多 2 行：超了先缩字号，保证不与署名行（qTop+150）基线相撞
        var qfont = 36;
        g.font = hand(qfont, 700);
        var qlines = wrapLines(g, '「' + top.quote + '」', maxW - 70);
        while (qlines.length > 2 && qfont > 26) {
            qfont -= 2;
            g.font = hand(qfont, 700);
            qlines = wrapLines(g, '「' + top.quote + '」', maxW - 70);
        }
        qlines = qlines.slice(0, 2);
        var qy = qTop + 58;
        qlines.forEach(function (ln) { g.fillText(ln, cx, qy); qy += 46; });
        g.font = reg(24, 700); g.fillStyle = '#8C7A67';
        g.fillText('—— 某页相片背面的字', cx, qTop + 150);

        // 钩子文案：必须压成单行（第 2 行基线会落进二维码带 y1096 被白底盖字），
        // 放不下就逐级缩字号到一行放下；基线 1076，字底 ~1082，距 QR 顶 1096 留 14px
        g.fillStyle = '#8A5A3B';
        var hookTxt = '我测出来是【' + top.name + '】，有点扎心…';
        var hfont = 30;
        g.font = bold(hfont);
        while (g.measureText(hookTxt).width > maxW && hfont > 20) {
            hfont -= 1;
            g.font = bold(hfont);
        }
        g.fillText(hookTxt, cx, 1076);

        // 底部二维码（共享封装 makeQrCanvas）
        var qr = makeQrCanvas(url, 144, '#8A5A3B');
        var qx = padL, qy2 = 1096;
        if (qr) {
            g.imageSmoothingEnabled = false;
            g.drawImage(qr, qx, qy2, 144, 144);
            g.imageSmoothingEnabled = true;
            g.textAlign = 'left';
            g.font = bold(29); g.fillStyle = '#3E2C20';
            g.fillText('扫码测一测', qx + 166, qy2 + 58);
            g.font = reg(23, 700); g.fillStyle = '#8C7A67';
            g.fillText('8 道题，你真的懂娃吗？', qx + 166, qy2 + 98);
            g.fillText('今晚就能做一件小事', qx + 166, qy2 + 132);
        }
        g.textAlign = 'center';
        g.font = reg(21); g.fillStyle = '#9B9184';
        g.fillText('仅供娱乐参考 · 不采集任何个人信息', cx, 1262);

        return c.toDataURL('image/png');
    }

    var sharePrevFocus = null;

    function openShare() {
        var modal = $('kc-share');
        var img = $('kc-share-img');
        var hint = $('kc-share-hint');
        sharePrevFocus = document.activeElement;
        modal.classList.add('is-on');
        $('kc-share-close').focus();
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
        $('kc-share').classList.remove('is-on');
        if (sharePrevFocus && sharePrevFocus.focus) sharePrevFocus.focus();
        sharePrevFocus = null;
    }

    /* ---------------- 绑定 ---------------- */
    $('kc-start').addEventListener('click', startOver);
    $('kc-back').addEventListener('click', back);
    $('kc-again-btn').addEventListener('click', startOver);
    $('kc-share-btn').addEventListener('click', openShare);
    $('kc-share-close').addEventListener('click', closeShare);
    $('kc-share-ok').addEventListener('click', closeShare);
    $('kc-share').addEventListener('click', function (e) { if (e.target === this) closeShare(); });
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
