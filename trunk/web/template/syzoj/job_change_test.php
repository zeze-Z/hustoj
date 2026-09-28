<?php
$show_title="测一测你该不该跳槽 - $OJ_NAME";
// 传播型 H5 极简壳：隐藏 OJ 顶栏与页脚主体（header.php/footer.php 按 $hide_chrome 分支，保留骨架+限流+极简署名）
$hide_chrome = true;
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
// 页面无表单、无入库、无用户态数据：仅注入站点名给分享卡片文案（JSON 十六进制转义防注入）
$jc_json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<style>
/* ===== 测一测你该不该跳槽（前缀 jc-）登机牌/工牌单一载体，移动端 375px 起 ===== */
:root{
    --jc-blue:#1B4B8F; --jc-blue-deep:#12345F; --jc-blue-soft:#E4ECF7;
    --jc-paper:#F2F6FA; --jc-card:#FFFFFF;
    --jc-red:#C8402F; --jc-red-deep:#96301F;
    --jc-ink:#14243D; --jc-ink-soft:#5A6B85;
    --jc-line:rgba(20,36,61,.22);
    --jc-accent:#1B4B8F; --jc-accent-fg:#FFFFFF;
    --jc-body:"PingFang SC","Microsoft YaHei",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}
.jc-page{
    max-width:560px; margin:0 auto; padding:18px 14px 46px;
    min-height:calc(100vh - 48px); /* 极简壳后仅剩署名行高度 */
    color:var(--jc-ink); font-family:var(--jc-body); line-height:1.65;
    background-color:var(--jc-paper);
    background-image:repeating-linear-gradient(135deg, rgba(27,75,143,.045) 0 2px, transparent 2px 18px);
}
.jc-page *{box-sizing:border-box}
/* 极简壳：body 高度必须 100%（header.php 的 html 为 position:fixed;overflow:hidden），禁 auto */
html,body{height:100%}
.jc-state{display:none}
.jc-state.is-on{display:block}
.jc-card{background:var(--jc-card);border:2px solid rgba(20,36,61,.6);border-radius:16px;
    box-shadow:0 3px 0 rgba(20,36,61,.12), 0 10px 24px rgba(20,36,61,.07)}
.jc-h3{
    position:relative; display:inline-block; margin:0 0 10px; padding-left:17px;
    font-size:1.06rem; font-weight:800; color:var(--jc-ink); letter-spacing:.02em;
}
.jc-h3::before{
    content:""; position:absolute; left:0; top:.3em; width:9px; height:9px;
    background:var(--jc-red); border-radius:2px;
}
.jc-btn{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;min-height:52px;padding:10px 16px;border-radius:12px;
    border:2px solid var(--jc-ink);font-family:var(--jc-body);font-weight:800;font-size:1.02rem;
    cursor:pointer;text-decoration:none;transition:transform .12s ease, box-shadow .12s ease;
}
.jc-btn:active{transform:translate(2px,2px);box-shadow:none!important}
.jc-btn:focus-visible,a.jc-btn:focus-visible{outline:3px solid var(--jc-red);outline-offset:3px}
.jc-btn--go{background:var(--jc-blue);color:#fff;font-size:1.14rem;box-shadow:0 4px 0 var(--jc-blue-deep)}
.jc-btn--red{background:var(--jc-red);color:#FFF6EF;box-shadow:0 4px 0 var(--jc-red-deep)}
.jc-btn--ghost{background:var(--jc-card);color:var(--jc-ink);box-shadow:0 3px 0 rgba(20,36,61,.28)}

/* 撕票虚线 + 两侧半圆缺口（登机牌贯穿元素） */
.jc-tear{
    position:relative;height:0;border-top:2px dashed rgba(20,36,61,.42);
    margin:16px -20px;
}
.jc-tear::before,.jc-tear::after{
    content:"";position:absolute;top:-12px;width:24px;height:24px;border-radius:50%;
    background:var(--jc-paper);border:2px solid rgba(20,36,61,.6);
}
.jc-tear::before{left:-14px;clip-path:inset(0 0 0 50%)}
.jc-tear::after{right:-14px;clip-path:inset(0 50% 0 0)}

/* ---- 落地页：登机牌 ---- */
.jc-ticket{overflow:hidden}
.jc-ticket-head{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    margin:0;padding:12px 18px;background:var(--jc-blue);color:#fff;
}
.jc-brand{display:flex;align-items:center;gap:8px;font-size:.82rem;font-weight:800;letter-spacing:.1em}
.jc-brand svg{width:20px;height:20px;flex:0 0 auto}
.jc-flightno{font-size:.82rem;font-weight:800;letter-spacing:.12em;opacity:.92}
.jc-ticket-body{padding:20px 20px 4px}
.jc-title{
    font-weight:800;font-size:clamp(1.8rem,8vw,2.35rem);
    line-height:1.32;margin:0;color:var(--jc-ink);letter-spacing:.01em;
}
.jc-title mark{
    background:none;color:inherit;padding:0 .04em;
    border-bottom:5px solid var(--jc-red);
}
.jc-sub{margin:12px 0 0;font-size:1rem;font-weight:700;color:var(--jc-blue)}
.jc-hook{
    margin:10px 0 0;font-size:.9rem;color:var(--jc-ink-soft);
    border-left:3px solid var(--jc-red);padding-left:10px;
}
/* 航班信息栏 */
.jc-flightinfo{
    display:grid;grid-template-columns:repeat(3,1fr);gap:0;
    margin:16px 0 0;border:1.5px solid var(--jc-line);border-radius:10px;overflow:hidden;
}
.jc-flightinfo > div{padding:8px 10px;border-right:1.5px dashed var(--jc-line)}
.jc-flightinfo > div:last-child{border-right:none}
.jc-flightinfo dt{margin:0;font-size:.68rem;font-weight:800;letter-spacing:.08em;color:var(--jc-ink-soft)}
.jc-flightinfo dd{margin:2px 0 0;font-size:.88rem;font-weight:800;color:var(--jc-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.jc-ticket-stub{padding:4px 20px 20px}
.jc-count{
    display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;
    margin:0 0 4px;padding:0;font-weight:700;color:var(--jc-ink);font-size:.95rem;
}
.jc-count b{
    font-weight:800;font-size:2.4rem;line-height:1;color:var(--jc-blue);letter-spacing:.01em;
}
.jc-avatars{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0 4px;padding:0;list-style:none}
.jc-avatars li{
    display:flex;align-items:center;gap:6px;padding:5px 11px 5px 8px;
    background:var(--jc-blue-soft);border:1.5px solid rgba(27,75,143,.45);border-radius:8px;
    font-size:.84rem;font-weight:800;color:var(--jc-ink);
}
.jc-avatars li:nth-child(odd){transform:rotate(-1.4deg)}
.jc-avatars li:nth-child(even){transform:rotate(1.2deg)}
.jc-start{margin-top:14px}
.jc-meta{margin:12px 0 0;text-align:center;font-size:.86rem;color:var(--jc-ink-soft)}
.jc-landing-note{margin:14px 0 0;text-align:center;font-size:.8rem;color:var(--jc-ink-soft)}

/* ---- 答题页 ---- */
.jc-qbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px}
.jc-dots{display:flex;gap:6px;flex:1}
.jc-dots span{flex:1;height:7px;border-radius:99px;background:#D9E2EE;transition:background .2s ease}
.jc-dots span.is-done{background:var(--jc-blue)}
.jc-dots span.is-cur{background:var(--jc-red);box-shadow:0 0 0 2px rgba(200,64,47,.28)}
.jc-qnum{font-size:.85rem;font-weight:800;color:var(--jc-ink-soft);white-space:nowrap}
.jc-back{
    background:none;border:none;padding:10px 8px;min-height:44px;font:inherit;font-size:.88rem;font-weight:700;
    color:var(--jc-blue);cursor:pointer;text-decoration:underline;
}
.jc-back:focus-visible{outline:3px solid var(--jc-blue);outline-offset:2px;border-radius:6px}
.jc-qcard{padding:0 0 18px;overflow:hidden}
.jc-qhead{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding:9px 16px;background:var(--jc-blue);color:#fff;
    font-size:.78rem;font-weight:800;letter-spacing:.08em;
}
.jc-qcard-in{padding:16px 16px 0}
.jc-qcard.is-in{animation:jc-slide .26s ease both}
@keyframes jc-slide{from{opacity:0;transform:translateX(22px)}to{opacity:1;transform:none}}
/* 场景 = 航段票签（左侧撕口虚线） */
.jc-qscene{
    display:inline-block;padding:3px 10px 3px 12px;font-size:.82rem;font-weight:800;
    color:var(--jc-blue);background:var(--jc-blue-soft);
    border-left:3px dashed var(--jc-red);border-radius:0 999px 999px 0;
}
.jc-qtitle{font-weight:800;font-size:1.28rem;line-height:1.5;margin:12px 0 16px;color:var(--jc-ink)}
.jc-opts{display:flex;flex-direction:column;gap:12px;margin:0;padding:0;list-style:none}
.jc-opt{
    display:flex;align-items:center;gap:12px;width:100%;min-height:56px;
    padding:12px 14px;text-align:left;background:var(--jc-card);color:var(--jc-ink);
    border:1.5px solid rgba(20,36,61,.35);border-radius:12px;
    font:inherit;font-size:.98rem;font-weight:700;line-height:1.5;cursor:pointer;
    transition:border-color .15s ease, background .15s ease, transform .12s ease;
}
.jc-opt:hover{border-color:var(--jc-blue);background:#FBFDFF}
.jc-opt:focus-visible{outline:3px solid var(--jc-blue);outline-offset:2px}
.jc-opt.is-picked{
    border-color:var(--jc-red);background:#FDF1EE;transform:translate(2px,2px);
    box-shadow:0 2px 0 rgba(20,36,61,.18);
}
/* 选项字母 = 座位号牌 */
.jc-opt b{
    flex:0 0 auto;display:grid;place-items:center;width:32px;height:32px;
    border:2px solid var(--jc-blue);border-radius:8px 8px 8px 2px;
    color:var(--jc-blue);font-weight:800;font-size:.96rem;background:var(--jc-blue-soft);
}
.jc-opt.is-picked b{border-color:var(--jc-red);color:#fff;background:var(--jc-red)}
.jc-qfoot{margin:14px 0 0;text-align:center;font-size:.82rem;color:var(--jc-ink-soft)}

/* ---- 结果页 ---- */
.jc-block{margin-bottom:18px}
/* 1 工牌挂绳 + 工牌（称号揭晓，视觉锤） */
.jc-badge-wrap{position:relative;padding-top:30px}
.jc-lanyard{position:absolute;top:0;left:0;right:0;height:30px}
.jc-lanyard::before{ /* 挂绳 */
    content:"";position:absolute;left:50%;top:-18px;width:30px;height:44px;
    transform:translateX(-50%);border-radius:5px;
    background:repeating-linear-gradient(45deg, var(--jc-blue) 0 7px, var(--jc-blue-deep) 7px 14px);
    border:1.5px solid rgba(20,36,61,.5);
}
.jc-lanyard::after{ /* 挂扣 */
    content:"";position:absolute;left:50%;top:24px;transform:translateX(-50%);
    width:38px;height:16px;background:#CBD6E4;border:2px solid rgba(20,36,61,.65);border-radius:5px;
}
.jc-badge{position:relative;padding:22px 16px 20px;text-align:center;overflow:hidden}
.jc-badge::before{ /* 工牌顶部打孔 */
    content:"";position:absolute;top:7px;left:50%;transform:translateX(-50%);
    width:44px;height:8px;border-radius:99px;background:var(--jc-paper);
    border:1.5px solid rgba(20,36,61,.4);
}
.jc-badge-head{
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    margin:-6px 0 12px;font-size:.7rem;font-weight:800;letter-spacing:.1em;color:var(--jc-ink-soft);
}
.jc-avatar{
    width:72px;height:72px;margin:0 auto 10px;border-radius:14px;display:grid;place-items:center;
    font-size:2.1rem;background:var(--jc-accent);color:var(--jc-accent-fg);
    border:2px solid var(--jc-ink);box-shadow:0 4px 0 rgba(20,36,61,.32);
}
.jc-lead{margin:0;font-size:.94rem;font-weight:700;color:var(--jc-ink-soft)}
.jc-name{font-weight:800;font-size:2.05rem;line-height:1.25;margin:2px 0 4px;color:var(--jc-ink);letter-spacing:.02em}
.jc-epithet{margin:0;font-size:1rem;font-weight:700;color:var(--jc-accent)}
/* 值机印章（红色，全页唯一重锤） */
.jc-stamp{
    display:inline-grid;place-items:center;margin-top:14px;padding:7px 18px;
    border:3.5px solid var(--jc-red);border-radius:8px;
    box-shadow:inset 0 0 0 2.5px var(--jc-card), inset 0 0 0 5px var(--jc-red);
    color:var(--jc-red);background:rgba(200,64,47,.05);
    font-weight:800;font-size:1.24rem;letter-spacing:.16em;line-height:1.1;
    transform:rotate(-7deg);opacity:.94;
}
.jc-stamp.is-slam{animation:jc-slam .55s cubic-bezier(.18,1.5,.4,1) both}
@keyframes jc-slam{
    0%{transform:rotate(-7deg) scale(2.6);opacity:0}
    55%{opacity:1}
    100%{transform:rotate(-7deg) scale(1);opacity:.94}
}
/* 2 匹配环（登机牌存根联上的里程环） */
.jc-ring-block{text-align:center;padding:18px 14px 16px}
.jc-ring-wrap{position:relative;width:158px;margin:0 auto}
.jc-ring{width:158px;height:158px;display:block;transform:rotate(-90deg)}
.jc-ring circle{fill:none;stroke-width:13;stroke-linecap:round}
.jc-ring .jc-ring-bg{stroke:#DCE5F0}
.jc-ring .jc-ring-arc{stroke:var(--jc-accent)}
.jc-ring-num{
    position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;
}
.jc-ring-num span{font-weight:800;font-size:2.7rem;line-height:1;color:var(--jc-ink)}
.jc-ring-num i{font-style:normal;font-size:.86rem;font-weight:800;color:var(--jc-ink-soft)}
.jc-ring-cap{margin:8px 0 0;font-size:.85rem;font-weight:800;color:var(--jc-ink-soft)}
/* 3 标签胶囊 */
.jc-tags,.jc-pills{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin:0;padding:0;list-style:none}
.jc-tags{margin-bottom:10px}
.jc-tag{
    display:inline-block;padding:6px 14px;border-radius:9px;background:var(--jc-card);
    color:var(--jc-ink);border:2px solid var(--jc-accent);font-weight:800;font-size:.92rem;
    box-shadow:0 3px 0 rgba(20,36,61,.14);
}
.jc-tags li:nth-child(1){transform:rotate(-2deg)}
.jc-tags li:nth-child(2){transform:rotate(1.5deg)}
.jc-tags li:nth-child(3){transform:rotate(-1deg)}
.jc-pill{
    display:inline-block;padding:5px 12px;border:1.5px dashed rgba(20,36,61,.45);
    border-radius:999px;color:var(--jc-ink-soft);font-size:.85rem;font-weight:700;background:var(--jc-blue-soft);
}
/* 4 金句：撕下来的登机牌存根 */
.jc-quote{
    position:relative;padding:24px 18px 16px;background:var(--jc-blue);
    border-radius:14px;box-shadow:0 8px 20px rgba(18,52,95,.28);overflow:hidden;
}
.jc-quote::before{ /* 存根顶部白撕口 */
    content:"";position:absolute;left:0;right:0;top:9px;
    border-top:2px dashed rgba(255,255,255,.55);
}
.jc-quote::after{
    content:"";position:absolute;left:-11px;top:-2px;width:22px;height:22px;border-radius:50%;
    background:var(--jc-paper);
}
.jc-quote blockquote{
    position:relative;margin:0;font-weight:800;font-size:1.2rem;line-height:1.7;color:#fff;
}
.jc-quote cite{display:block;margin-top:8px;font-style:normal;font-size:.85rem;font-weight:700;color:#BFD3EE;text-align:right}
/* 5 理由（左侧红条 + 白卡，无横线底纹） */
.jc-reason{
    padding:14px 16px 14px 18px;border:1.5px solid rgba(20,36,61,.3);border-left:5px solid var(--jc-red);
    border-radius:10px;background:var(--jc-card);
    font-size:.96rem;line-height:1.75;color:#2B3A52;
}
.jc-radar{width:100%;height:auto;display:block}
/* 7 第二人格 */
.jc-second{
    display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:14px;
    background:var(--jc-card);border:1.5px dashed rgba(20,36,61,.5);
    box-shadow:0 3px 0 rgba(20,36,61,.1);
}
.jc-second-emoji{
    flex:0 0 auto;width:50px;height:50px;border-radius:12px;display:grid;place-items:center;
    font-size:1.5rem;border:2px solid rgba(20,36,61,.65);color:#fff;
}
.jc-second-body{flex:1;min-width:0}
.jc-second-body p{margin:0}
.jc-second-label{font-size:.8rem;font-weight:800;color:var(--jc-blue)}
.jc-second-name{font-weight:800;font-size:1.24rem;color:var(--jc-ink)}
.jc-second-desc{font-size:.85rem;color:var(--jc-ink-soft)}
.jc-second-pct{flex:0 0 auto;font-weight:800;font-size:1.45rem;color:var(--jc-red)}
/* 8 转化区 */
.jc-slogan{
    margin:0 0 12px;padding:13px 15px;border-radius:12px;
    background:var(--jc-blue);color:#fff;font-weight:800;font-size:1.02rem;
    text-align:center;letter-spacing:.02em;
}
.jc-links{display:flex;flex-direction:column;gap:10px;margin:0;padding:0;list-style:none}
.jc-link{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:12px;
    background:var(--jc-card);border:1.5px solid rgba(20,36,61,.3);
    text-decoration:none;color:var(--jc-ink);
    transition:border-color .15s ease, transform .15s ease;
}
.jc-link:hover{border-color:var(--jc-blue);text-decoration:none;transform:translateY(-2px)}
.jc-link:focus-visible{outline:3px solid var(--jc-blue);outline-offset:2px}
.jc-link-ico{
    flex:0 0 auto;width:42px;height:42px;border-radius:10px;display:grid;place-items:center;
    border:2px solid rgba(20,36,61,.55);
}
.jc-link-ico svg{width:22px;height:22px}
.jc-link-t{font-weight:800;font-size:.98rem}
.jc-link-d{font-size:.82rem;color:var(--jc-ink-soft);line-height:1.45}
.jc-link-chev{margin-left:auto;color:var(--jc-ink-soft);flex:0 0 auto}
.jc-login-hint{
    margin:12px 0 0;padding:11px 14px;border-radius:10px;background:var(--jc-blue-soft);
    border:1.5px dashed rgba(27,75,143,.45);font-size:.88rem;font-weight:700;color:var(--jc-ink-soft);text-align:center;
}
.jc-login-hint a{color:var(--jc-blue);font-weight:800}
.jc-actions{display:flex;flex-direction:column;gap:10px}
.jc-note{margin:16px 0 0;text-align:center;font-size:.78rem;color:var(--jc-ink-soft)}

/* ---- 分享卡片弹层 ---- */
.jc-modal{
    position:fixed;inset:0;z-index:2000;display:none;align-items:flex-start;justify-content:center;
    padding:24px 14px;overflow-y:auto;background:rgba(20,36,61,.62);
}
.jc-modal.is-on{display:flex}
.jc-modal-box{
    width:100%;max-width:420px;background:var(--jc-paper);border:2px solid var(--jc-ink);
    border-radius:16px;box-shadow:0 10px 34px rgba(0,0,0,.35);padding:14px 14px 18px;
}
.jc-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.jc-modal-head span{font-weight:800;font-size:1.1rem;color:var(--jc-ink)}
.jc-x{
    width:44px;height:44px;border-radius:10px;border:2px solid var(--jc-ink);background:var(--jc-card);
    font-size:1.3rem;line-height:1;color:var(--jc-ink);cursor:pointer;
}
.jc-x:focus-visible{outline:3px solid var(--jc-blue);outline-offset:2px}
.jc-share-img{display:block;width:100%;height:auto;border-radius:12px;border:1.5px solid rgba(20,36,61,.55);background:#fff}
.jc-modal-hint{margin:10px 0 12px;text-align:center;font-size:.86rem;font-weight:700;color:var(--jc-ink-soft)}

@media (min-width: 640px){
    .jc-page{padding-top:26px}
    .jc-ticket-body{padding:26px 28px 4px}
    .jc-ticket-stub{padding:4px 28px 26px}
}
@media (prefers-reduced-motion: reduce){
    .jc-page *,.jc-page *::before,.jc-page *::after{
        animation-duration:.001s!important;animation-iteration-count:1!important;transition-duration:.001s!important;
    }
}
</style>

<div class="jc-page">

    <!-- ============ 状态一：落地页（登机牌） ============ -->
    <section id="jc-landing" class="jc-state is-on" aria-label="测试介绍">
        <div class="jc-card jc-ticket">
            <div class="jc-ticket-head">
                <span class="jc-brand">
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M4 26 L44 12 l-4 10 -14 4 -8 12 -4 -1 3 -12 -11 2 Z" fill="none" stroke="#fff" stroke-width="3" stroke-linejoin="round"/>
                    </svg>
                    BOARDING PASS · 职场航线
                </span>
                <span class="jc-flightno">FLIGHT JC-08</span>
            </div>
            <div class="jc-ticket-body">
                <h1 class="jc-title">测一测，你该不该<mark>跳槽</mark>？</h1>
                <p class="jc-sub">留下还是走？8 道题亮出你的底牌</p>
                <p class="jc-hook">刷招聘软件到凌晨的你，缺的不是机会，是一个说得出口的答案。</p>
                <dl class="jc-flightinfo">
                    <div><dt>航线</dt><dd>当下 → 下一站</dd></div>
                    <div><dt>座位</dt><dd>自己挑</dd></div>
                    <div><dt>状态</dt><dd>待值机</dd></div>
                </dl>
            </div>
            <div class="jc-tear" aria-hidden="true"></div>
            <div class="jc-ticket-stub">
                <p class="jc-count">已有 <b id="jc-total">8341</b> 人完成值机</p>
                <ul class="jc-avatars" id="jc-landing-avatars"></ul>
                <button type="button" class="jc-btn jc-btn--go jc-start" id="jc-start">开始测试</button>
                <p class="jc-meta">8 道题 · 约 1 分钟 · 无需登录</p>
            </div>
        </div>
        <p class="jc-landing-note">纯属娱乐，结果仅供开心参考</p>
    </section>

    <!-- ============ 状态二：答题页 ============ -->
    <section id="jc-quiz" class="jc-state" aria-label="答题">
        <div class="jc-qbar">
            <div class="jc-dots" id="jc-dots" aria-hidden="true"></div>
            <span class="jc-qnum" id="jc-qnum">1 / 8</span>
        </div>
        <button type="button" class="jc-back" id="jc-back" style="display:none">← 上一题</button>
        <div class="jc-card jc-qcard" id="jc-qcard">
            <div class="jc-qhead"><span>FLIGHT JC-08</span><span id="jc-qhead-no">BOARDING 1/8</span></div>
            <div class="jc-qcard-in">
                <span class="jc-qscene" id="jc-qscene"></span>
                <h2 class="jc-qtitle" id="jc-qtitle"></h2>
                <ul class="jc-opts" id="jc-opts"></ul>
            </div>
        </div>
        <p class="jc-qfoot">凭第一直觉选，别想太久</p>
    </section>

    <!-- ============ 状态三：结果页（顺序固定） ============ -->
    <section id="jc-result" class="jc-state" aria-label="测试结果">

        <!-- 1 工牌揭晓（挂绳 + 工牌 + 值机印章） -->
        <div class="jc-block jc-badge-wrap">
            <div class="jc-lanyard" aria-hidden="true"></div>
            <div class="jc-card jc-badge">
                <div class="jc-badge-head"><span>EMPLOYEE PASS</span><span id="jc-badge-no">JC-08</span></div>
                <div class="jc-avatar" id="jc-avatar" aria-hidden="true"></div>
                <p class="jc-lead">你的跳槽人格是——</p>
                <h2 class="jc-name" id="jc-name"></h2>
                <p class="jc-epithet" id="jc-epithet"></p>
                <div class="jc-stamp" id="jc-stamp" role="img" aria-label="值机印章"></div>
            </div>
        </div>

        <!-- 2 匹配度环 -->
        <div class="jc-block jc-card jc-ring-block">
            <div class="jc-ring-wrap">
                <svg class="jc-ring" viewBox="0 0 158 158" aria-hidden="true">
                    <circle class="jc-ring-bg" cx="79" cy="79" r="66"/>
                    <circle class="jc-ring-arc" id="jc-ring-arc" cx="79" cy="79" r="66"/>
                </svg>
                <div class="jc-ring-num"><span id="jc-pct">0</span><i>匹配度 %</i></div>
            </div>
            <p class="jc-ring-cap">跳槽人格匹配度</p>
        </div>

        <!-- 3 人设标签 + 关键词胶囊 -->
        <div class="jc-block">
            <ul class="jc-tags" id="jc-tags"></ul>
            <ul class="jc-pills" id="jc-pills"></ul>
        </div>

        <!-- 4 金句（登机牌存根） -->
        <div class="jc-block jc-quote">
            <blockquote id="jc-quote"></blockquote>
            <cite id="jc-quote-by"></cite>
        </div>

        <!-- 5 个性化理由 -->
        <div class="jc-block">
            <h3 class="jc-h3">值机台给你的理由</h3>
            <div class="jc-reason" id="jc-reason"></div>
        </div>

        <!-- 6 五维雷达 -->
        <div class="jc-block">
            <h3 class="jc-h3">你的五维职业倾向</h3>
            <svg class="jc-radar" id="jc-radar" viewBox="0 0 320 258" role="img" aria-label="五维职业倾向雷达图"></svg>
        </div>

        <!-- 7 第二人格 -->
        <div class="jc-block">
            <div class="jc-second">
                <div class="jc-second-emoji" id="jc-second-emoji" aria-hidden="true"></div>
                <div class="jc-second-body">
                    <p class="jc-second-label">你的隐藏第二人格</p>
                    <p class="jc-second-name" id="jc-second-name"></p>
                    <p class="jc-second-desc" id="jc-second-desc">换个答法，TA 可能会反超</p>
                </div>
                <div class="jc-second-pct" id="jc-second-pct"></div>
            </div>
        </div>

        <!-- 8 转化区 -->
        <div class="jc-block">
            <p class="jc-slogan">留下或离开，想清楚了再登机</p>
            <ul class="jc-links">
                <li>
                    <a class="jc-link" href="timetable.php">
                        <span class="jc-link-ico" style="background:#E4ECF7">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="10" y="16" width="44" height="38" rx="5" fill="none" stroke="#14243D" stroke-width="5"/>
                                <line x1="10" y1="28" x2="54" y2="28" stroke="#14243D" stroke-width="5"/>
                                <line x1="24" y1="11" x2="24" y2="20" stroke="#14243D" stroke-width="5" stroke-linecap="round"/>
                                <line x1="40" y1="11" x2="40" y2="20" stroke="#14243D" stroke-width="5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="jc-link-t">课程表生成器</span><br>
                            <span class="jc-link-d">给自己排一张有留白的课表</span>
                        </span>
                        <svg class="jc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="jc-link" href="course.php">
                        <span class="jc-link-ico" style="background:#FDF1EE">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <path d="M10 14 h18 a6 6 0 0 1 6 6 v30 a6 6 0 0 0 -6 -6 H10 Z" fill="none" stroke="#14243D" stroke-width="5" stroke-linejoin="round"/>
                                <path d="M54 14 H36 a6 6 0 0 0 -6 6 v30 a6 6 0 0 1 6 -6 h18 Z" fill="none" stroke="#14243D" stroke-width="5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="jc-link-t">课件库</span><br>
                            <span class="jc-link-d">现成课件直接拿，先给今晚续命</span>
                        </span>
                        <svg class="jc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="jc-link" href="more.php#games">
                        <span class="jc-link-ico" style="background:#EAF3EC">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="8" y="20" width="48" height="28" rx="12" fill="none" stroke="#14243D" stroke-width="5"/>
                                <line x1="18" y1="29" x2="18" y2="39" stroke="#14243D" stroke-width="5" stroke-linecap="round"/>
                                <line x1="13" y1="34" x2="23" y2="34" stroke="#14243D" stroke-width="5" stroke-linecap="round"/>
                                <circle cx="44" cy="31" r="3.4" fill="#14243D"/>
                                <circle cx="51" cy="38" r="3.4" fill="#14243D"/>
                            </svg>
                        </span>
                        <span>
                            <span class="jc-link-t">课前小游戏</span><br>
                            <span class="jc-link-d">课前小游戏回血，顺带给学生热身</span>
                        </span>
                        <svg class="jc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
            </ul>
            <p class="jc-login-hint"><a href="loginpage.php">登录</a> 解锁更多工具（课程管理、作业布置…）</p>
        </div>

        <!-- 9 操作区 -->
        <div class="jc-block jc-actions">
            <button type="button" class="jc-btn jc-btn--red" id="jc-share-btn">生成分享卡片</button>
            <button type="button" class="jc-btn jc-btn--ghost" id="jc-again-btn">重新测试</button>
        </div>

        <!-- 10 免责 -->
        <p class="jc-note">仅供娱乐参考 · 本测试不采集任何个人信息</p>
    </section>
</div>

<!-- ============ 状态四：分享卡片预览 ============ -->
<div class="jc-modal" id="jc-share" role="dialog" aria-modal="true" aria-label="分享卡片预览">
    <div class="jc-modal-box">
        <div class="jc-modal-head">
            <span>你的分享卡片</span>
            <button type="button" class="jc-x" id="jc-share-close" aria-label="关闭">&times;</button>
        </div>
        <img class="jc-share-img" id="jc-share-img" alt="跳槽决策分享卡片">
        <p class="jc-modal-hint" id="jc-share-hint">正在生成…</p>
        <button type="button" class="jc-btn jc-btn--ghost" id="jc-share-ok">完成</button>
    </div>
</div>

<script>
window.JCS = {
    siteName: <?php echo json_encode($OJ_NAME, $jc_json_flags); ?>
};
</script>
<!-- 通关礼花：canvas-confetti CDN + 项目公共 game_confetti.js（CDN 失败则静默降级） -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/game_confetti.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qrcode.min.js"></script>
<!-- 共享 Canvas/二维码工具（makeQrCanvas/roundRect/wrapLines），须在 qrcode.min.js 之后 -->
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qr_helper.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/job_change_test_data.js"></script>
<script>
(function () {
    'use strict';
    var D = window.JC_DATA;
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
            $('jc-' + m).classList.toggle('is-on', m === mode);
        });
        var page = document.querySelector('.jc-page');
        window.scrollTo(0, page ? Math.max(0, page.offsetTop - 70) : 0);
    }

    /* ---------------- 落地页 ---------------- */
    function renderLanding() {
        $('jc-total').textContent = D.MATCHED_TOTAL;
        var ul = $('jc-landing-avatars');
        ul.innerHTML = '';
        D.PERSONAS.forEach(function (p) {
            var li = document.createElement('li');
            li.appendChild(document.createTextNode(p.name + ' · ' + p.stamp));
            ul.appendChild(li);
        });
    }

    /* ---------------- 答题页 ---------------- */
    function renderQuiz(animate) {
        var q = D.QUESTIONS[state.qi];
        $('jc-qnum').textContent = (state.qi + 1) + ' / ' + D.QUESTIONS.length;
        $('jc-qhead-no').textContent = 'BOARDING ' + (state.qi + 1) + '/' + D.QUESTIONS.length;
        $('jc-back').style.display = state.qi > 0 ? '' : 'none';

        var dots = $('jc-dots');
        dots.innerHTML = '';
        D.QUESTIONS.forEach(function (_, i) {
            var s = document.createElement('span');
            if (i < state.qi) s.className = 'is-done';
            if (i === state.qi) s.className = 'is-cur';
            dots.appendChild(s);
        });

        $('jc-qscene').textContent = '航段 · ' + q.scene;
        $('jc-qtitle').textContent = q.title;

        var ul = $('jc-opts');
        ul.innerHTML = '';
        q.options.forEach(function (opt, i) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'jc-opt';
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

        var card = $('jc-qcard');
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

        // 个性化理由：intro + 得分最高 2 个维度各 1 变体 + outro（不足 80 字则升到 3 维，上限 150 字内）
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
        var page = document.querySelector('.jc-page');
        page.style.setProperty('--jc-accent', top.color);
        page.style.setProperty('--jc-accent-fg', top.fg);

        // 工牌揭晓 + 值机印章（盖章一击，一次）
        $('jc-avatar').textContent = top.emoji;
        $('jc-name').textContent = top.name;
        $('jc-epithet').textContent = top.epithet;
        var stamp = $('jc-stamp');
        stamp.textContent = top.stamp;
        stamp.classList.remove('is-slam');
        void stamp.offsetWidth;
        stamp.classList.add('is-slam');

        // 匹配环
        var arc = $('jc-ring-arc');
        var C = 2 * Math.PI * 66;
        arc.style.strokeDasharray = C.toFixed(2);
        arc.style.strokeDashoffset = C.toFixed(2);

        // 标签 / 胶囊
        fillList('jc-tags', 'jc-tag', top.tags);
        fillList('jc-pills', 'jc-pill', top.pills);

        // 金句
        $('jc-quote').textContent = top.quote;
        $('jc-quote-by').textContent = '—— ' + top.name + ' 的口头禅';

        // 理由
        $('jc-reason').textContent = res.reason;

        // 雷达
        renderRadar(res.raw, top.color);

        // 第二人格
        $('jc-second-emoji').textContent = res.second.emoji;
        $('jc-second-emoji').style.background = res.second.color;
        $('jc-second-name').textContent = res.second.name;
        $('jc-second-pct').textContent = res.secondPct + '%';
        // 同分时避免"TA 可能会反超"与相同百分比自相矛盾
        $('jc-second-desc').textContent = (res.secondPct >= res.topPct)
            ? '和你不相上下，换个答法见分晓' : '换个答法，TA 可能会反超';

        show('result');
        revealRing(res.topPct);
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

    var jcRingRaf = 0;
    function revealRing(pct) {
        var arc = $('jc-ring-arc');
        var num = $('jc-pct');
        var C = 2 * Math.PI * 66;
        var target = C * (1 - pct / 100);
        if (jcRingRaf) { cancelAnimationFrame(jcRingRaf); jcRingRaf = 0; } // 重测速进结果页时取消旧动画
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
            if (t < 1) jcRingRaf = requestAnimationFrame(step);
            else { num.textContent = pct; jcRingRaf = 0; }
        }
        arc.style.strokeDashoffset = C.toFixed(2);
        jcRingRaf = requestAnimationFrame(step);
    }

    /* ---------------- 五维雷达（SVG） ---------------- */
    function renderRadar(raw, color) {
        var svg = $('jc-radar');
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
        var grid = 'rgba(20,36,61,.24)';
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
                '" font-size="15" font-weight="700" fill="#14243D" font-family="PingFang SC, Microsoft YaHei, sans-serif">' +
                D.DIMENSIONS[i].label + '</text>');
        }
        svg.innerHTML = out.join('');
    }

    /* ---------------- 分享卡片（Canvas 750×1334，登机牌）
     * roundRect / wrapLines / makeQrCanvas 均来自共享 js/qr_helper.js（不本地拷贝）
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

        // 纸底（登机牌底纹斜纹）
        g.fillStyle = '#F2F6FA'; g.fillRect(0, 0, W, H);
        g.strokeStyle = 'rgba(27,75,143,.06)'; g.lineWidth = 2;
        for (var s = -H; s < W; s += 26) { g.beginPath(); g.moveTo(s, 0); g.lineTo(s + H, H); g.stroke(); }

        // 登机牌主体
        var tx = 44, ty = 44, tw = W - 88, th = 1246;
        roundRect(g, tx, ty, tw, th, 26);
        g.fillStyle = '#FFFFFF'; g.fill();
        g.lineWidth = 4; g.strokeStyle = 'rgba(20,36,61,.75)'; g.stroke();

        // 蓝色抬头条
        g.save();
        roundRect(g, tx, ty, tw, th, 26); g.clip();
        g.fillStyle = '#1B4B8F'; g.fillRect(tx, ty, tw, 104);
        g.restore();
        g.textAlign = 'center'; g.textBaseline = 'alphabetic';
        g.font = reg(27, 800); g.fillStyle = '#FFFFFF';
        g.fillText('✈ BOARDING PASS · ' + ((window.JCS && window.JCS.siteName) || '') + ' 跳槽人格测评', cx, ty + 64);

        // 航班信息栏
        var infoY = ty + 148;
        var cells = [
            ['航线', '当下 → 下一站'],
            ['座位', '自己挑'],
            ['状态', top.stamp]
        ];
        var cellW = tw / 3;
        cells.forEach(function (cell, ci) {
            var cxx = tx + cellW * ci;
            if (ci > 0) {
                g.strokeStyle = 'rgba(20,36,61,.3)'; g.lineWidth = 2;
                g.setLineDash([7, 7]);
                g.beginPath(); g.moveTo(cxx, infoY - 34); g.lineTo(cxx, infoY + 24); g.stroke();
                g.setLineDash([]);
            }
            g.font = reg(21, 700); g.fillStyle = '#5A6B85';
            g.fillText(cell[0], cxx + cellW / 2, infoY - 8);
            g.fillStyle = '#14243D';
            var v = cell[1], vfs = 26;
            g.font = bold(vfs);
            while (g.measureText(v).width > cellW - 24 && vfs > 16) { vfs -= 2; g.font = bold(vfs); }
            g.fillText(v, cxx + cellW / 2, infoY + 24);
        });

        // 撕票虚线 + 红色缺口
        var tearY = ty + 200;
        g.strokeStyle = 'rgba(20,36,61,.45)'; g.lineWidth = 3; g.setLineDash([12, 10]);
        g.beginPath(); g.moveTo(tx + 10, tearY); g.lineTo(tx + tw - 10, tearY); g.stroke();
        g.setLineDash([]);
        g.fillStyle = '#F2F6FA';
        g.beginPath(); g.arc(tx, tearY, 16, 0, Math.PI * 2); g.fill();
        g.strokeStyle = 'rgba(20,36,61,.75)'; g.lineWidth = 3;
        g.beginPath(); g.arc(tx, tearY, 16, -Math.PI / 2, Math.PI / 2); g.stroke();
        g.beginPath(); g.arc(tx + tw, tearY, 16, Math.PI / 2, Math.PI * 1.5); g.stroke();

        // 值机印章（红，右上）
        g.save();
        g.translate(tx + tw - 130, tearY + 96);
        g.rotate(-0.14);
        roundRect(g, -104, -46, 208, 92, 12);
        g.fillStyle = 'rgba(200,64,47,.05)'; g.fill();
        g.lineWidth = 6; g.strokeStyle = '#C8402F'; g.stroke();
        roundRect(g, -92, -34, 184, 68, 8);
        g.lineWidth = 3.5; g.stroke();
        g.fillStyle = '#C8402F'; g.font = bold(40);
        g.fillText(top.stamp, 0, 14);
        g.restore();

        // 人格称号
        g.font = reg(29, 700); g.fillStyle = '#5A6B85';
        g.textAlign = 'left';
        g.fillText('我的跳槽人格是', tx + 44, tearY + 60);
        g.font = bold(86); g.fillStyle = '#14243D';
        var nameTxt = top.name;
        if (g.measureText(nameTxt).width > maxW - 60) g.font = bold(Math.floor(86 * (maxW - 60) / g.measureText(nameTxt).width));
        g.fillText(nameTxt, tx + 44, tearY + 156);
        g.font = reg(28, 700); g.fillStyle = top.color;
        g.fillText(top.epithet, tx + 44, tearY + 204);

        // 匹配度徽章
        var badgeTxt = '跳槽人格匹配度 ' + res.topPct + '%';
        g.font = bold(32);
        var bw = g.measureText(badgeTxt).width + 52, bh = 62;
        var bx = tx + 44, by = tearY + 236;
        roundRect(g, bx + 4, by + 5, bw, bh, 31); g.fillStyle = 'rgba(20,36,61,.75)'; g.fill();
        roundRect(g, bx, by, bw, bh, 31);
        g.fillStyle = '#E4ECF7'; g.fill();
        g.lineWidth = 4; g.strokeStyle = '#1B4B8F'; g.stroke();
        g.fillStyle = '#1B4B8F';
        g.fillText(badgeTxt, bx + 26, by + 42);

        // 3 个人设标签：一行，放不下自动缩字号
        var gap = 16, tags = top.tags, tfont = 30, tw2, total;
        function measureTags(fs) {
            g.font = bold(fs);
            var w = tags.map(function (t) { return g.measureText(t).width + 36; });
            return { w: w, sum: w.reduce(function (a, b) { return a + b; }, 0) + gap * (tags.length - 1) };
        }
        var m = measureTags(tfont);
        while (m.sum > maxW && tfont > 19) { tfont -= 2; m = measureTags(tfont); }
        tw2 = m.w; total = m.sum;
        var ty2 = by + bh + 34, rx = cx - total / 2;
        tags.forEach(function (t, i) {
            roundRect(g, rx, ty2, tw2[i], 56, 10);
            g.fillStyle = '#FFFFFF'; g.fill();
            g.lineWidth = 3; g.strokeStyle = top.color; g.stroke();
            g.fillStyle = '#14243D';
            g.textAlign = 'center';
            g.fillText(t, rx + tw2[i] / 2, ty2 + 37);
            rx += tw2[i] + gap;
        });

        // 金句（蓝色存根，最多 2 行防撞署名）
        var qTop = ty2 + 96;
        roundRect(g, tx + 34, qTop, tw - 68, 176, 14);
        g.fillStyle = '#1B4B8F'; g.fill();
        g.strokeStyle = 'rgba(255,255,255,.6)'; g.lineWidth = 2.5; g.setLineDash([10, 9]);
        g.beginPath(); g.moveTo(tx + 52, qTop + 22); g.lineTo(tx + tw - 52, qTop + 22); g.stroke();
        g.setLineDash([]);
        g.fillStyle = '#FFFFFF';
        var qfont = 37;
        g.font = reg(qfont, 800);
        var qlines = wrapLines(g, '「' + top.quote + '」', maxW - 90);
        while (qlines.length > 2 && qfont > 28) {
            qfont -= 2;
            g.font = reg(qfont, 800);
            qlines = wrapLines(g, '「' + top.quote + '」', maxW - 90);
        }
        qlines = qlines.slice(0, 2);
        var qy = qTop + 78;
        g.textAlign = 'center';
        qlines.forEach(function (ln) { g.fillText(ln, cx, qy); qy += 48; });
        g.font = reg(25, 700); g.fillStyle = '#BFD3EE';
        g.fillText('—— ' + top.name + ' 的口头禅', cx, qTop + 154);

        // 分享钩子文案（既定分享文案）
        var hookY = qTop + 244;
        g.font = bold(33); g.fillStyle = '#C8402F';
        var hook = wrapLines(g, '测完了，我的辞职信还在草稿箱。你是哪种？8 道题见分晓', maxW).slice(0, 2);
        var hy = hookY;
        hook.forEach(function (ln) { g.fillText(ln, cx, hy); hy += 46; });

        // 底部二维码（共享封装，航空蓝主题色）
        var qr = makeQrCanvas(url, 144, '#1B4B8F');
        var qx = tx + 44, qy2 = ty + th - 216;
        roundRect(g, qx - 12, qy2 - 12, 168, 168, 14);
        g.fillStyle = '#FFFFFF'; g.fill();
        g.lineWidth = 2.5; g.strokeStyle = 'rgba(20,36,61,.35)'; g.stroke();
        if (qr) {
            g.imageSmoothingEnabled = false;
            g.drawImage(qr, qx, qy2, 144, 144);
            g.imageSmoothingEnabled = true;
            g.textAlign = 'left';
            g.font = bold(29); g.fillStyle = '#14243D';
            g.fillText('扫码测一测', qx + 196, qy2 + 62);
            g.font = reg(23, 700); g.fillStyle = '#5A6B85';
            g.fillText('留下还是走？8 道题见分晓', qx + 196, qy2 + 102);
        }
        g.textAlign = 'center';
        g.font = reg(22); g.fillStyle = '#5A6B85';
        g.fillText('仅供娱乐参考 · 不采集任何个人信息', cx, ty + th - 26);

        return c.toDataURL('image/png');
    }

    var sharePrevFocus = null;

    function openShare() {
        var modal = $('jc-share');
        var img = $('jc-share-img');
        var hint = $('jc-share-hint');
        sharePrevFocus = document.activeElement;
        modal.classList.add('is-on');
        $('jc-share-close').focus();
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
        $('jc-share').classList.remove('is-on');
        if (sharePrevFocus && sharePrevFocus.focus) sharePrevFocus.focus();
        sharePrevFocus = null;
    }

    /* ---------------- 绑定 ---------------- */
    $('jc-start').addEventListener('click', startOver);
    $('jc-back').addEventListener('click', back);
    $('jc-again-btn').addEventListener('click', startOver);
    $('jc-share-btn').addEventListener('click', openShare);
    $('jc-share-close').addEventListener('click', closeShare);
    $('jc-share-ok').addEventListener('click', closeShare);
    $('jc-share').addEventListener('click', function (e) { if (e.target === this) closeShare(); });
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
