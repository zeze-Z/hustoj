<?php
$show_title="教师解压日历 - $OJ_NAME";
// 传播型 H5 极简壳：隐藏 OJ 顶栏与页脚主体（header.php/footer.php 按 $hide_chrome 分支，保留骨架+限流+极简署名）
$hide_chrome = true;
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
// 页面无表单、无入库、无用户态数据：仅注入站点名给分享卡片文案（JSON 十六进制转义防注入）
$mc_json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<style>
/* ===== 教师解压日历（前缀 mc-）日历手账/便签 UI，移动端 375px 起 ===== */
:root{
    --mc-paper:#FFF7EA; --mc-paper-deep:#FBEEDA;
    --mc-ink:#3B2E1E; --mc-ink-soft:#8A7A62;
    --mc-card:#FFFFFF; --mc-line:rgba(59,46,30,.18);
    --mc-accent:#E8833A; --mc-accent2:#E4573D; --mc-green:#5AA469;
    --mc-tape:rgba(255,205,120,.75);
    --mc-body:"PingFang SC","Microsoft YaHei",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}
.mc-page{
    max-width:560px; margin:0 auto; padding:18px 14px 46px;
    min-height:calc(100vh - 48px); /* 极简壳后仅剩署名行高度 */
    color:var(--mc-ink); font-family:var(--mc-body); line-height:1.65;
    background-color:var(--mc-paper);
    background-image:
        linear-gradient(rgba(59,46,30,.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(59,46,30,.05) 1px, transparent 1px);
    background-size:26px 26px;
}
.mc-page *{box-sizing:border-box}
.mc-state{display:none}
.mc-state.is-on{display:block}
.mc-card{background:var(--mc-card);border:2px solid rgba(59,46,30,.5);border-radius:16px;
    box-shadow:0 3px 0 rgba(59,46,30,.14), 0 10px 24px rgba(59,46,30,.07); position:relative}
.mc-card::before{ /* 便签胶带 */
    content:"";position:absolute;top:-9px;left:50%;transform:translateX(-50%) rotate(-2deg);
    width:86px;height:20px;background:var(--mc-tape);border-radius:3px;
    box-shadow:0 1px 2px rgba(59,46,30,.16);
}
.mc-h3{
    position:relative;display:inline-block;margin:0 0 10px;padding-left:18px;
    font-size:1.02rem;font-weight:800;color:var(--mc-ink);letter-spacing:.02em;
}
.mc-h3::before{content:"✦";position:absolute;left:0;top:.05em;font-size:.86rem;color:var(--mc-accent)}
.mc-btn{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;min-height:52px;padding:10px 16px;border-radius:14px;
    border:2px solid var(--mc-ink);font-family:var(--mc-body);font-weight:800;font-size:1.02rem;
    cursor:pointer;text-decoration:none;transition:transform .12s ease, box-shadow .12s ease;
}
.mc-btn:active{transform:translate(2px,2px);box-shadow:none!important}
.mc-btn:focus-visible,a.mc-btn:focus-visible{outline:3px solid var(--mc-accent);outline-offset:3px}
.mc-btn--go{
    background:linear-gradient(100deg, #FFB25E 0%, var(--mc-accent2) 100%);
    color:#fff;font-size:1.14rem;box-shadow:0 4px 0 rgba(59,46,30,.55);
}
.mc-btn--warm{background:var(--mc-accent);color:#fff;box-shadow:0 4px 0 #B9611F}
.mc-btn--ghost{background:#fff;color:var(--mc-ink);box-shadow:0 3px 0 rgba(59,46,30,.3)}

/* ---- 开场页 ---- */
.mc-note{
    position:relative;overflow:hidden;padding:26px 20px 24px;border-radius:18px;
    background:linear-gradient(165deg,#FFF3D9 0%,#FFE6BF 100%);
    border:3px solid #C9A45E;box-shadow:inset 0 0 0 2px rgba(255,255,255,.6), 0 10px 26px rgba(59,46,30,.18);
}
.mc-note::after{ /* 撕边便签线 */
    content:"";position:absolute;left:0;right:0;bottom:0;height:10px;
    background:repeating-linear-gradient(90deg, rgba(201,164,94,.35) 0 8px, transparent 8px 16px);
}
.mc-doodle{position:absolute;pointer-events:none;opacity:.6}
.mc-doodle--sun{top:14px;right:14px;width:52px;transform:rotate(6deg)}
.mc-doodle--clip{bottom:24px;left:16px;width:38px;transform:rotate(-8deg)}
.mc-kicker{margin:0 0 8px;font-size:.86rem;font-weight:800;color:#B9611F;letter-spacing:.08em}
.mc-title{
    font-weight:800;font-size:clamp(1.8rem,8vw,2.35rem);
    line-height:1.32;margin:0;color:var(--mc-ink);letter-spacing:.01em;
}
.mc-title mark{
    background:linear-gradient(transparent 54%, rgba(255,138,101,.85) 54%);color:#3B2E1E;
    padding:0 .08em;border-radius:4px;
}
.mc-sub{margin:12px 0 0;font-size:.98rem;color:#6B5B42}
.mc-hook{margin:10px 0 0;font-size:.9rem;color:#8A7A62;border-left:3px solid rgba(232,131,58,.8);padding-left:10px}
/* 便签涂鸦日历（开场视觉引子） */
.mc-cal-doodle{display:block;width:min(220px,66%);margin:18px auto 4px}
.mc-avatars{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0 4px;padding:0;list-style:none}
.mc-avatars li{
    display:flex;align-items:center;gap:6px;padding:5px 11px 5px 8px;
    background:#fff;border:2px solid rgba(59,46,30,.45);border-radius:999px;
    font-size:.85rem;font-weight:800;box-shadow:0 2px 0 rgba(59,46,30,.14);
}
.mc-avatars li:nth-child(odd){transform:rotate(-1.6deg)}
.mc-avatars li:nth-child(even){transform:rotate(1.4deg)}
.mc-start{margin-top:16px}
.mc-meta{margin:12px 0 0;text-align:center;font-size:.86rem;color:var(--mc-ink-soft)}
.mc-landing-note{margin:14px 0 0;text-align:center;font-size:.8rem;color:var(--mc-ink-soft)}

/* ---- 主界面：当日心情大卡 ---- */
.mc-block{margin-bottom:18px}
.mc-hero{padding:22px 16px 18px;border-radius:18px;
    background:linear-gradient(165deg,#FFFFFF 0%,#FFF9EE 100%);
    border:3px solid rgba(59,46,30,.55);box-shadow:0 4px 0 rgba(59,46,30,.12), 0 12px 26px rgba(59,46,30,.08);
    position:relative;overflow:hidden}
.mc-hero::before{
    content:"";position:absolute;top:-9px;right:22px;transform:rotate(3deg);
    width:78px;height:20px;background:rgba(160,214,255,.8);border-radius:3px;
    box-shadow:0 1px 2px rgba(59,46,30,.16);
}
.mc-hero-top{display:flex;align-items:center;gap:14px}
.mc-hero-emoji{
    flex:0 0 auto;width:74px;height:74px;border-radius:50%;display:grid;place-items:center;
    font-size:2.3rem;border:3px solid rgba(59,46,30,.75);
    box-shadow:0 3px 0 rgba(59,46,30,.28);background:#FFE6BF;
    transition:background .25s ease;
}
.mc-hero-meta{flex:1;min-width:0}
.mc-hero-date{margin:0;font-size:.82rem;font-weight:800;color:var(--mc-ink-soft);letter-spacing:.02em}
.mc-hero-name{margin:2px 0 0;font-weight:800;font-size:1.6rem;line-height:1.25;color:var(--mc-ink)}
.mc-hero-tag{margin:2px 0 0;font-size:.84rem;font-weight:700;color:var(--mc-accent2)}
.mc-hero-pct{flex:0 0 auto;text-align:right;font-weight:800;line-height:1}
.mc-hero-pct span{font-size:2.5rem;letter-spacing:.01em}
.mc-hero-pct i{font-style:normal;font-size:1rem;font-weight:800;color:var(--mc-accent)}
.mc-meter{height:14px;border-radius:99px;background:#F1E6D2;border:2px solid rgba(59,46,30,.3);
    margin:14px 0 12px;overflow:hidden}
.mc-meter span{display:block;height:100%;border-radius:99px;background:var(--mc-green);
    transition:width .4s ease, background .3s ease}
.mc-hero-main{margin:0;font-size:1.02rem;font-weight:700;line-height:1.7;color:var(--mc-ink)}
.mc-hero-roast{margin:8px 0 0;font-size:.86rem;color:var(--mc-ink-soft);text-align:right;font-style:italic}
.mc-chips{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0 0}
.mc-chip{
    display:inline-block;padding:5px 12px;border-radius:999px;font-size:.82rem;font-weight:700;
    border:2px solid rgba(59,46,30,.35);background:#FFF4E2;color:var(--mc-ink);
}
.mc-chip--do{border-color:rgba(90,164,105,.7);background:#EDF7EE;color:#2F7A44}
.mc-chip--dont{border-color:rgba(228,87,61,.6);background:#FDEEEA;color:#B23A26}
.mc-chip--lucky{border-color:rgba(232,131,58,.7);background:#FFF2E3;color:#B9611F}
.mc-advice{margin:12px 0 0;padding:10px 12px;border-radius:10px;background:#F6EFE3;
    border:2px dashed rgba(59,46,30,.3);font-size:.88rem;font-weight:700;color:#6B5B42}
.mc-egg{margin:10px 0 0;padding:9px 12px;border-radius:10px;background:#FFE9E4;
    border:2px solid rgba(228,87,61,.55);font-size:.86rem;font-weight:800;color:#B23A26}

/* ---- emoji 5 选 1 + 称呼 ---- */
.mc-emos{display:flex;gap:8px;margin:0;padding:0;list-style:none}
.mc-emos li{flex:1}
.mc-emo{
    display:flex;flex-direction:column;align-items:center;gap:2px;width:100%;min-height:66px;
    padding:8px 2px;border-radius:14px;background:#fff;cursor:pointer;
    border:2px solid rgba(59,46,30,.35);font:inherit;transition:transform .12s ease, border-color .15s ease;
}
.mc-emo:focus-visible{outline:3px solid var(--mc-accent);outline-offset:2px}
.mc-emo b{font-size:1.5rem;font-weight:400;line-height:1.2}
.mc-emo i{font-style:normal;font-size:.72rem;font-weight:800;color:var(--mc-ink-soft)}
.mc-emo.is-picked{border-color:var(--mc-accent2);background:#FFF2E9;transform:translateY(-2px);
    box-shadow:0 3px 0 rgba(232,131,58,.35)}
.mc-emo.is-picked i{color:var(--mc-accent2)}
.mc-nick{display:flex;align-items:center;gap:10px;margin-top:12px}
.mc-nick label{font-size:.86rem;font-weight:800;color:var(--mc-ink-soft);white-space:nowrap}
.mc-nick input{
    flex:1;min-width:0;min-height:44px;padding:8px 12px;border-radius:12px;
    border:2px solid rgba(59,46,30,.35);background:#fff;font:inherit;font-size:.95rem;color:var(--mc-ink);
}
.mc-nick input:focus-visible{outline:3px solid var(--mc-accent);outline-offset:1px;border-color:var(--mc-accent)}

/* ---- 月历热力网格 ---- */
.mc-cal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.mc-cal-head span{font-weight:800;font-size:1.05rem;color:var(--mc-ink)}
.mc-nav{
    width:44px;height:44px;border-radius:11px;border:2px solid var(--mc-ink);background:#fff;
    font-size:1.25rem;line-height:1;color:var(--mc-ink);cursor:pointer;
}
.mc-nav:focus-visible{outline:3px solid var(--mc-accent);outline-offset:2px}
.mc-cal-week,.mc-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:6px}
.mc-cal-week span{text-align:center;font-size:.78rem;font-weight:800;color:var(--mc-ink-soft);margin-bottom:2px}
.mc-cell{
    position:relative;aspect-ratio:1/1;min-height:38px;border-radius:10px;border:2px solid rgba(59,46,30,.3);
    font:inherit;font-size:.86rem;font-weight:800;color:#fff;text-shadow:0 1px 2px rgba(0,0,0,.38);
    cursor:pointer;padding:0;transition:transform .1s ease;
}
.mc-cell:focus-visible{outline:3px solid var(--mc-accent);outline-offset:2px}
.mc-cell.is-blank{visibility:hidden;pointer-events:none;background:none!important;border:none}
.mc-cell:not(.is-blank):hover{transform:translateY(-2px)}
.mc-cell.is-today{border-color:var(--mc-ink);box-shadow:inset 0 0 0 2px rgba(255,255,255,.75)}
.mc-cell.is-sel{box-shadow:0 0 0 3px var(--mc-accent2);border-color:#fff}
.mc-cell.is-hol::after{
    content:"";position:absolute;top:3px;right:4px;width:6px;height:6px;border-radius:50%;
    background:#fff;box-shadow:0 0 0 1.5px rgba(59,46,30,.55);
}
.mc-legend{display:flex;align-items:center;gap:8px;margin-top:10px;font-size:.76rem;font-weight:700;color:var(--mc-ink-soft)}
.mc-legend-bar{flex:0 0 auto;width:110px;height:10px;border-radius:99px;border:1.5px solid rgba(59,46,30,.35);
    background:linear-gradient(90deg, hsl(0,72%,58%), hsl(70,72%,58%), hsl(140,72%,58%))}
.mc-cal-hint{margin:8px 0 0;font-size:.78rem;color:var(--mc-ink-soft);text-align:center}

/* ---- 解压小互动：捏泡泡（气泡膜材质） ---- */
.mc-bubble-tip{margin:0 0 10px;font-size:.88rem;font-weight:700;color:var(--mc-ink-soft)}
.mc-bubble-tip b{color:var(--mc-accent2);font-size:1rem}
.mc-bubble-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0 0 6px}
.mc-bubble-head .mc-h3{margin:0}
.mc-sound-btn{
    padding:2px 10px;border-radius:999px;cursor:pointer;flex-shrink:0;
    border:1.5px solid rgba(59,46,30,.35);background:rgba(255,255,255,.7);
    font-size:.82rem;line-height:1.5;
}
.mc-sound-btn:focus-visible{outline:3px solid var(--mc-accent);outline-offset:2px}
.mc-sound-btn[aria-pressed="false"]{opacity:.55}
/* 捏爆全部泡泡后的祝福语 */
.mc-blessing{
    margin:6px 0 0;padding:24px 16px;border-radius:14px;text-align:center;
    background:linear-gradient(160deg, #FFF3E2 0%, #FFE8CC 100%);
    border:2px dashed rgba(201,164,94,.9);
}
.mc-blessing-label{margin:0 0 10px;font-size:.82rem;font-weight:800;color:#B9611F;letter-spacing:.14em}
.mc-blessing-text{margin:0 0 18px;font-size:1.14rem;font-weight:800;color:var(--mc-ink);line-height:1.75}
.mc-blessing .mc-btn{max-width:220px;margin:0 auto}

/* ---- 底部引流入口 ---- */
.mc-slogan{margin:0 0 12px;font-size:.95rem;font-weight:800;color:var(--mc-ink);text-align:center}
.mc-links{display:flex;flex-direction:column;gap:10px;margin:0;padding:0;list-style:none}
.mc-link{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:12px;
    background:#fff;border:2px solid rgba(59,46,30,.35);
    text-decoration:none;color:var(--mc-ink);
    transition:border-color .15s ease, transform .15s ease;
}
.mc-link:hover{border-color:var(--mc-accent);text-decoration:none;transform:translateY(-2px)}
.mc-link:focus-visible{outline:3px solid var(--mc-accent);outline-offset:2px}
.mc-link-ico{
    flex:0 0 auto;width:42px;height:42px;border-radius:11px;display:grid;place-items:center;
    border:2px solid rgba(59,46,30,.6);
}
.mc-link-ico svg{width:22px;height:22px}
.mc-link-t{font-weight:800;font-size:.98rem}
.mc-link-d{font-size:.82rem;color:var(--mc-ink-soft);line-height:1.45}
.mc-link-chev{margin-left:auto;color:var(--mc-ink-soft);flex:0 0 auto}
.mc-login-hint{
    margin:12px 0 0;padding:11px 14px;border-radius:12px;background:#FFF3E2;
    border:2px dashed rgba(59,46,30,.4);font-size:.88rem;font-weight:700;color:var(--mc-ink-soft);text-align:center;
}
.mc-login-hint a{color:var(--mc-accent2);font-weight:800}
/* 气泡膜垫片：半透明塑料膜压在纸面上 */
.mc-bubbles{
    display:grid;grid-template-columns:repeat(4,1fr);gap:12px;
    padding:16px 12px;border-radius:14px;
    background:
        linear-gradient(162deg, rgba(255,255,255,.78) 0%, rgba(214,240,246,.5) 55%, rgba(190,228,238,.55) 100%),
        repeating-linear-gradient(0deg, rgba(59,46,30,.045) 0 1px, transparent 1px 24px),
        repeating-linear-gradient(90deg, rgba(59,46,30,.045) 0 1px, transparent 1px 24px);
    border:1px solid rgba(255,255,255,.85);
    box-shadow:0 2px 0 rgba(59,46,30,.1), 0 14px 26px rgba(59,46,30,.1), inset 0 1px 0 rgba(255,255,255,.95);
}
.mc-bubble-host{position:relative;aspect-ratio:1/1}
.mc-bubble{
    position:absolute;inset:0;border-radius:50%;cursor:pointer;padding:0;
    border:1px solid rgba(255,255,255,.88);
    background:radial-gradient(circle at 30% 24%,
        rgba(255,255,255,.96) 0%, rgba(255,255,255,.38) 17%, rgba(255,255,255,.1) 40%,
        rgba(146,214,228,.24) 68%, rgba(108,184,204,.42) 100%);
    box-shadow:
        inset 0 -7px 11px rgba(255,255,255,.6),
        inset 0 7px 11px rgba(88,148,168,.2),
        0 6px 10px rgba(59,46,30,.16),
        0 1px 2px rgba(59,46,30,.12);
    transition:transform .13s ease, box-shadow .13s ease, opacity .18s ease;
}
/* 主高光 + 次高光：穹顶反光 */
.mc-bubble::before{
    content:"";position:absolute;left:17%;top:13%;width:36%;height:27%;
    border-radius:50%;background:radial-gradient(closest-side, rgba(255,255,255,.98), rgba(255,255,255,0));
    transform:rotate(-18deg);pointer-events:none;
}
.mc-bubble::after{
    content:"";position:absolute;right:17%;bottom:19%;width:19%;height:13%;
    border-radius:50%;background:radial-gradient(closest-side, rgba(255,255,255,.85), rgba(255,255,255,0));
    transform:rotate(-24deg);pointer-events:none;
}
.mc-bubble:hover{transform:translateY(-2px) scale(1.035)}
.mc-bubble:focus-visible{outline:3px solid var(--mc-accent);outline-offset:3px}
/* 按压：穹顶被捏扁 */
.mc-bubble.is-squeeze{
    transform:scale(1.14,.78);
    box-shadow:
        inset 0 -2px 4px rgba(255,255,255,.45),
        inset 0 5px 13px rgba(88,148,168,.3),
        0 2px 4px rgba(59,46,30,.13);
    transition:transform .07s ease-in, box-shadow .07s ease-in;
}
/* 捏破瞬间：先压瘪再膨胀白闪（可见的爆炸过程） */
@keyframes mc-burst{
    0%{transform:scale(.82,.72);filter:brightness(1)}
    40%{transform:scale(1.34,1.2);filter:brightness(1.75)}
    100%{transform:scale(1.46,1.32);opacity:.15;filter:brightness(2)}
}
.mc-bubble.is-burst{
    animation:mc-burst .13s ease-out forwards;
    background:radial-gradient(circle at 50% 46%,
        rgba(255,255,255,1) 0%, rgba(214,240,246,.95) 48%, rgba(126,196,214,.8) 100%);
    box-shadow:0 0 26px rgba(126,196,214,.85), 0 0 46px rgba(255,255,255,.6), inset 0 -4px 8px rgba(255,255,255,.8);
    pointer-events:none;
}
/* 爆炸后残留：压瘪起皱的膜片（真实气泡膜不消失） */
.mc-bubble.is-pop{
    transform:scale(.88,.82);opacity:.62;cursor:default;pointer-events:none;
    background:
        repeating-linear-gradient(112deg, rgba(255,255,255,.3) 0 3px, rgba(255,255,255,0) 3px 8px),
        repeating-linear-gradient(-28deg, rgba(88,148,168,.14) 0 2px, rgba(255,255,255,0) 2px 9px),
        radial-gradient(circle at 50% 46%, rgba(255,255,255,.52) 0%, rgba(146,214,228,.15) 74%);
    border-color:rgba(118,168,184,.35);
    box-shadow:inset 0 1px 3px rgba(59,46,30,.18), inset 0 -1px 2px rgba(255,255,255,.45);
}
.mc-bubble.is-pop::before{display:none}
.mc-bubble.is-pop::after{ /* 压瘪后的残余反光 */
    left:22%;top:28%;width:24%;height:11%;transform:rotate(-8deg);opacity:.5;
}
@keyframes mc-pop-ring{
    0%{transform:scale(.45);opacity:.85}
    100%{transform:scale(2.9);opacity:0}
}
.mc-pop-ring{
    position:absolute;inset:-8px;border-radius:50%;
    border:3px solid rgba(255,255,255,.98);box-shadow:0 0 0 2px rgba(126,196,214,.65), 0 0 22px rgba(126,196,214,.5);
    animation:mc-pop-ring .42s ease-out both;pointer-events:none;
}
@keyframes mc-pop-ring2{
    0%{transform:scale(.25);opacity:.8}
    100%{transform:scale(1.8);opacity:0}
}
.mc-pop-ring2{
    position:absolute;inset:6%;border-radius:50%;
    border:3px solid rgba(126,196,214,.95);
    animation:mc-pop-ring2 .34s ease-out both;pointer-events:none;
}
@keyframes mc-flash{
    0%{opacity:1;transform:scale(.6)}
    100%{opacity:0;transform:scale(1.6)}
}
.mc-flash{
    position:absolute;inset:-20%;border-radius:50%;
    background:radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(214,240,246,.7) 45%, rgba(255,255,255,0) 74%);
    animation:mc-flash .26s ease-out both;pointer-events:none;
}
@keyframes mc-drop-fly{
    0%{transform:translate(0,0) scale(1);opacity:1}
    65%{opacity:.85}
    100%{transform:translate(var(--dx),var(--dy)) scale(.18);opacity:0}
}
.mc-drop{
    position:absolute;left:50%;top:50%;width:11px;height:11px;margin:-5.5px 0 0 -5.5px;
    border-radius:50% 50% 50% 7px;
    background:radial-gradient(circle at 32% 30%, rgba(255,255,255,.98), rgba(126,196,214,.82));
    animation:mc-drop-fly .62s cubic-bezier(.12,.6,.3,1) both;pointer-events:none;
}
@keyframes mc-shard-fly{
    0%{transform:translate(0,0) rotate(var(--rot)) scale(1);opacity:1}
    100%{transform:translate(var(--dx),var(--dy)) rotate(calc(var(--rot) + 170deg)) scale(.4);opacity:0}
}
.mc-shard{
    position:absolute;left:50%;top:50%;width:18px;height:13px;margin:-6.5px 0 0 -9px;
    background:linear-gradient(135deg, rgba(255,255,255,.95), rgba(126,196,214,.6));
    clip-path:polygon(8% 18%, 88% 0%, 100% 62%, 46% 100%, 0% 68%);
    animation:mc-shard-fly .72s cubic-bezier(.12,.6,.3,1) both;pointer-events:none;
}
/* 爆炸后残留的大块薄膜碎片（静态留在膜上） */
.mc-shard-left{
    position:absolute;pointer-events:none;opacity:.75;
    background:linear-gradient(135deg, rgba(255,255,255,.88) 0%, rgba(126,196,214,.5) 55%, rgba(255,255,255,.62) 100%);
    border:1px solid rgba(255,255,255,.72);
    box-shadow:0 2px 4px rgba(59,46,30,.2), inset 0 1px 0 rgba(255,255,255,.85);
}
.mc-shard-left:nth-child(3n){clip-path:polygon(12% 8%, 92% 0%, 100% 58%, 55% 100%, 4% 72%)}
.mc-shard-left:nth-child(3n+1){clip-path:polygon(0% 22%, 78% 0%, 100% 70%, 42% 100%)}

/* ---- 操作区 ---- */
.mc-actions{display:flex;flex-direction:column;gap:10px}
.mc-note-bottom{margin:16px 0 0;text-align:center;font-size:.78rem;color:var(--mc-ink-soft)}

/* ---- 分享卡片弹层 ---- */
.mc-modal{
    position:fixed;inset:0;z-index:2000;display:none;align-items:flex-start;justify-content:center;
    padding:24px 14px;overflow-y:auto;background:rgba(59,46,30,.62);
}
.mc-modal.is-on{display:flex}
.mc-modal-box{
    width:100%;max-width:420px;background:var(--mc-paper);border:2px solid var(--mc-ink);
    border-radius:16px;box-shadow:0 10px 34px rgba(0,0,0,.35);padding:14px 14px 18px;
}
.mc-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.mc-modal-head span{font-weight:800;font-size:1.1rem;color:var(--mc-ink)}
.mc-x{
    width:44px;height:44px;border-radius:11px;border:2px solid var(--mc-ink);background:#fff;
    font-size:1.3rem;line-height:1;color:var(--mc-ink);cursor:pointer;
}
.mc-x:focus-visible{outline:3px solid var(--mc-accent);outline-offset:2px}
.mc-share-img{display:block;width:100%;height:auto;border-radius:12px;border:2px solid rgba(59,46,30,.6);background:#fff}
.mc-modal-hint{margin:10px 0 12px;text-align:center;font-size:.86rem;font-weight:700;color:var(--mc-ink-soft)}

@media (min-width: 640px){
    .mc-page{padding-top:26px}
    .mc-note{padding:34px 30px 30px}
}
@media (prefers-reduced-motion: reduce){
    .mc-page *,.mc-page *::before,.mc-page *::after{
        animation-duration:.001s!important;animation-iteration-count:1!important;transition-duration:.001s!important;
    }
}
</style>

<div class="mc-page">

    <!-- ============ 状态一：开场 ============ -->
    <section id="mc-landing" class="mc-state is-on" aria-label="日历介绍">
        <div class="mc-note">
            <svg class="mc-doodle mc-doodle--sun" viewBox="0 0 48 48" aria-hidden="true">
                <circle cx="24" cy="24" r="10" fill="none" stroke="#E8833A" stroke-width="3"/>
                <path d="M24 4 v6 M24 38 v6 M4 24 h6 M38 24 h6 M10 10 l4 4 M34 34 l4 4 M38 10 l-4 4 M14 34 l-4 4"
                      stroke="#E8833A" stroke-width="3" stroke-linecap="round"/>
            </svg>
            <svg class="mc-doodle mc-doodle--clip" viewBox="0 0 32 48" aria-hidden="true">
                <path d="M10 44 V12 a6 6 0 0 1 12 0 v26 a9 9 0 0 1 -18 0 V14"
                      fill="none" stroke="#8A7A62" stroke-width="3" stroke-linecap="round"/>
            </svg>
            <p class="mc-kicker">教师趣味测评 · 解压日历</p>
            <h1 class="mc-title">教师<mark>解压日历</mark></h1>
            <p class="mc-sub">日历知道你今天为什么不想上班——节前狂欢、收假忧郁、周一丧，全都安排明白了。</p>
            <p class="mc-hook">把情绪交给日历，把解压留给自己。点开就是你的今日份心情。</p>
            <svg class="mc-cal-doodle" viewBox="0 0 240 150" aria-hidden="true">
                <rect x="8" y="24" width="224" height="116" rx="12" fill="#FFFFFF" stroke="#3B2E1E" stroke-width="4"/>
                <rect x="8" y="24" width="224" height="30" rx="12" fill="#FFB25E" stroke="#3B2E1E" stroke-width="4"/>
                <line x1="60" y1="16" x2="60" y2="38" stroke="#3B2E1E" stroke-width="5" stroke-linecap="round"/>
                <line x1="180" y1="16" x2="180" y2="38" stroke="#3B2E1E" stroke-width="5" stroke-linecap="round"/>
                <rect x="26" y="66" width="34" height="24" rx="5" fill="#F1E6D2"/>
                <rect x="70" y="66" width="34" height="24" rx="5" fill="#FDEEEA"/>
                <rect x="114" y="66" width="34" height="24" rx="5" fill="#EDF7EE"/>
                <rect x="158" y="66" width="34" height="24" rx="5" fill="#F1E6D2"/>
                <rect x="26" y="100" width="34" height="24" rx="5" fill="#FFF2E3"/>
                <rect x="70" y="100" width="34" height="24" rx="5" fill="#F1E6D2"/>
                <rect x="114" y="100" width="34" height="24" rx="5" fill="#FFE9E4"/>
                <rect x="158" y="100" width="34" height="24" rx="5" fill="#EDF7EE"/>
                <path d="M164 108 l10 8 16 -14" fill="none" stroke="#5AA469" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <ul class="mc-avatars" id="mc-landing-emos" aria-hidden="true"></ul>
        <button type="button" class="mc-btn mc-btn--go mc-start" id="mc-start">看看我今天的状态</button>
        <p class="mc-meta">今日心情 · 月历回看 · 约 1 分钟 · 无需登录</p>
        <p class="mc-landing-note">仅供娱乐参考，不采集任何个人信息</p>
    </section>

    <!-- ============ 状态二：主界面 ============ -->
    <section id="mc-main" class="mc-state" aria-label="解压日历主界面">

        <!-- 1 当日心情大卡 -->
        <div class="mc-block mc-hero">
            <div class="mc-hero-top">
                <div class="mc-hero-emoji" id="mc-hero-emoji" aria-hidden="true">😐</div>
                <div class="mc-hero-meta">
                    <p class="mc-hero-date" id="mc-hero-date"></p>
                    <h2 class="mc-hero-name" id="mc-hero-name"></h2>
                    <p class="mc-hero-tag" id="mc-hero-tag"></p>
                </div>
                <p class="mc-hero-pct"><span id="mc-hero-pct">0</span><i>%</i></p>
            </div>
            <div class="mc-meter" role="img" aria-label="今日电量"><span id="mc-meter-fill"></span></div>
            <p class="mc-hero-main" id="mc-hero-main"></p>
            <p class="mc-hero-roast" id="mc-hero-roast"></p>
            <div class="mc-chips">
                <span class="mc-chip mc-chip--do" id="mc-chip-do"></span>
                <span class="mc-chip mc-chip--dont" id="mc-chip-dont"></span>
                <span class="mc-chip mc-chip--lucky" id="mc-chip-lucky"></span>
            </div>
            <p class="mc-advice" id="mc-advice"></p>
            <p class="mc-egg" id="mc-egg" hidden></p>
        </div>

        <!-- 2 emoji 5 选 1 + 称呼 -->
        <div class="mc-block mc-card" style="padding:16px 14px 16px">
            <h3 class="mc-h3">此刻的你，是哪个表情？</h3>
            <ul class="mc-emos" id="mc-emos" role="radiogroup" aria-label="选择心情表情"></ul>
            <div class="mc-nick">
                <label for="mc-nick-input">称呼（选填）</label>
                <input id="mc-nick-input" type="text" maxlength="8" placeholder="王老师" autocomplete="off">
            </div>
        </div>

        <!-- 3 月历热力网格 -->
        <div class="mc-block mc-card" style="padding:16px 14px 16px">
            <div class="mc-cal-head">
                <button type="button" class="mc-nav" id="mc-prev" aria-label="上个月">&lsaquo;</button>
                <span id="mc-cal-title"></span>
                <button type="button" class="mc-nav" id="mc-next" aria-label="下个月">&rsaquo;</button>
            </div>
            <div class="mc-cal-week" aria-hidden="true">
                <span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span>
            </div>
            <div class="mc-cal-grid" id="mc-cal-grid"></div>
            <div class="mc-legend"><span class="mc-legend-bar"></span><span>电量低 → 电量高（白点 = 法定节假日）</span></div>
            <p class="mc-cal-hint">点选任意一天，预览那天的心情</p>
        </div>

        <!-- 4 解压小互动 -->
        <div class="mc-block mc-card" style="padding:16px 14px 16px">
            <div class="mc-bubble-head">
                <h3 class="mc-h3">解压小互动 · 捏泡泡</h3>
                <button type="button" class="mc-sound-btn" id="mc-sound-btn" aria-pressed="true" aria-label="捏破音效开关">🔊 音效</button>
            </div>
            <p class="mc-bubble-tip" id="mc-bubble-tip">按住捏扁，松手捏爆，坏情绪一起带走（还剩 <b id="mc-bubble-left">12</b> 个）</p>
            <div class="mc-bubbles" id="mc-bubbles"></div>
            <div class="mc-blessing" id="mc-blessing" hidden>
                <p class="mc-blessing-label">今日祝福</p>
                <p class="mc-blessing-text" id="mc-blessing-text"></p>
                <button type="button" class="mc-btn mc-btn--ghost" id="mc-blessing-again">再捏一卷</button>
            </div>
        </div>

        <!-- 5 底部引流入口 -->
        <div class="mc-block mc-card" style="padding:16px 14px 16px">
            <p class="mc-slogan">日历替你算心情，工具替你省力气</p>
            <ul class="mc-links">
                <li>
                    <a class="mc-link" href="timetable.php">
                        <span class="mc-link-ico" style="background:#E8F5E9">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="10" y="16" width="44" height="38" rx="5" fill="none" stroke="#3B2E1E" stroke-width="5"/>
                                <line x1="10" y1="28" x2="54" y2="28" stroke="#3B2E1E" stroke-width="5"/>
                                <line x1="24" y1="11" x2="24" y2="20" stroke="#3B2E1E" stroke-width="5" stroke-linecap="round"/>
                                <line x1="40" y1="11" x2="40" y2="20" stroke="#3B2E1E" stroke-width="5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="mc-link-t">课程表生成器</span><br>
                            <span class="mc-link-d">给自己排一张有留白的课表</span>
                        </span>
                        <svg class="mc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="mc-link" href="course.php">
                        <span class="mc-link-ico" style="background:#FFF8E1">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <path d="M10 14 h18 a6 6 0 0 1 6 6 v30 a6 6 0 0 0 -6 -6 H10 Z" fill="none" stroke="#3B2E1E" stroke-width="5" stroke-linejoin="round"/>
                                <path d="M54 14 H36 a6 6 0 0 0 -6 6 v30 a6 6 0 0 1 6 -6 h18 Z" fill="none" stroke="#3B2E1E" stroke-width="5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="mc-link-t">课件库</span><br>
                            <span class="mc-link-d">少熬夜，课件直接拿去用</span>
                        </span>
                        <svg class="mc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="mc-link" href="more.php#games">
                        <span class="mc-link-ico" style="background:#E0F2F1">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="8" y="20" width="48" height="28" rx="12" fill="none" stroke="#3B2E1E" stroke-width="5"/>
                                <line x1="18" y1="29" x2="18" y2="39" stroke="#3B2E1E" stroke-width="5" stroke-linecap="round"/>
                                <line x1="13" y1="34" x2="23" y2="34" stroke="#3B2E1E" stroke-width="5" stroke-linecap="round"/>
                                <circle cx="44" cy="31" r="3.4" fill="#3B2E1E"/>
                                <circle cx="51" cy="38" r="3.4" fill="#3B2E1E"/>
                            </svg>
                        </span>
                        <span>
                            <span class="mc-link-t">课前小游戏</span><br>
                            <span class="mc-link-d">教师省电模式：学生玩，你趁机歇会儿</span>
                        </span>
                        <svg class="mc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="mc-link" href="more.php#quiz">
                        <span class="mc-link-ico" style="background:#FCE4EC">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="12" y="10" width="40" height="44" rx="7" fill="none" stroke="#3B2E1E" stroke-width="5"/>
                                <text x="32" y="42" font-family="Arial, sans-serif" font-size="26" font-weight="700" text-anchor="middle" fill="#3B2E1E">?</text>
                            </svg>
                        </span>
                        <span>
                            <span class="mc-link-t">去测一测</span><br>
                            <span class="mc-link-d">考编决策、教师电量、教育家人格……更多扎心小测试</span>
                        </span>
                        <svg class="mc-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
            </ul>
            <p class="mc-login-hint"><a href="loginpage.php">登录</a> 解锁更多工具（课程管理、作业布置…）</p>
        </div>

        <!-- 6 操作区 -->
        <div class="mc-block mc-actions">
            <button type="button" class="mc-btn mc-btn--warm" id="mc-share-btn">生成分享卡片</button>
            <button type="button" class="mc-btn mc-btn--ghost" id="mc-back-btn">回到开场</button>
        </div>

        <p class="mc-note-bottom">仅供娱乐参考 · 本工具不采集任何个人信息</p>
    </section>
</div>

<!-- ============ 状态三：分享卡片预览 ============ -->
<div class="mc-modal" id="mc-share" role="dialog" aria-modal="true" aria-label="分享卡片预览">
    <div class="mc-modal-box">
        <div class="mc-modal-head">
            <span>你的分享卡片</span>
            <button type="button" class="mc-x" id="mc-share-close" aria-label="关闭">&times;</button>
        </div>
        <img class="mc-share-img" id="mc-share-img" alt="教师解压日历分享卡片">
        <p class="mc-modal-hint" id="mc-share-hint">正在生成…</p>
        <button type="button" class="mc-btn mc-btn--ghost" id="mc-share-ok">完成</button>
    </div>
</div>

<script>
window.TMC = {
    siteName: <?php echo json_encode($OJ_NAME, $mc_json_flags); ?>
};
</script>
<!-- 通关礼花已按需求移除（捏爆全部泡泡改为送祝福语） -->
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qrcode.min.js"></script>
<!-- 共享 Canvas/二维码工具（makeQrCanvas/roundRect/wrapLines），须在 qrcode.min.js 之后 -->
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qr_helper.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/teacher_mood_calendar_data.js"></script>
<script>
(function () {
    'use strict';
    var D = window.TMC_DATA;
    if (!D) return;
    var $ = function (id) { return document.getElementById(id); };
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var WEEK_CN = ['日', '一', '二', '三', '四', '五', '六'];

    var state = {
        mode: 'landing',
        sel: '',          // 选中的日期 YYYY-MM-DD
        emo: 0,           // 默认第一个 😄 还不错（电量微调 +3）
        cy: 0, cm: 0,     // 月历视图年/月
        left: 0,          // 剩余未捏泡泡数
        popped: 0
    };

    /* ---------------- 日期工具（手写解析，避开时区） ---------------- */
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function fmt(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
    function parse(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
    function addDays(s, n) { var d = parse(s); d.setDate(d.getDate() + n); return fmt(d); }
    function todayStr() { return fmt(new Date()); }
    function cnDate(s) {
        var d = parse(s);
        return d.getFullYear() + ' 年 ' + (d.getMonth() + 1) + ' 月 ' + d.getDate() + ' 日 星期' + WEEK_CN[d.getDay()];
    }

    /* ---------------- 状态引擎（命中即止，优先级 1→7） ---------------- */
    var HOLI = {}, MAKEUP = {};
    D.HOLIDAYS.forEach(function (h) {
        h.days.forEach(function (ds) { HOLI[ds] = h; });
        (h.makeUp || []).forEach(function (ds) { MAKEUP[ds] = true; });
    });

    function weekdayOf(ds) { return parse(ds).getDay(); }
    function isHoliday(ds) { return !!HOLI[ds]; }
    function isWorkday(ds) {
        if (HOLI[ds]) return false;
        var wd = weekdayOf(ds);
        return wd >= 1 && wd <= 5 || !!MAKEUP[ds];
    }
    // 节前第 N 个工作日（向前扫到最近节假日，含当日；超出 3 个返回 0）
    function preDays(ds) {
        if (!isWorkday(ds)) return 0;
        var d = addDays(ds, 1), n = 0;
        for (var i = 0; i < 14; i++) {
            if (HOLI[d]) return n + 1;
            if (isWorkday(d)) n++;
            if (n + 1 > 3) return 0;
            d = addDays(d, 1);
        }
        return 0;
    }
    // 节后第 N 个工作日
    function postDays(ds) {
        if (!isWorkday(ds)) return 0;
        var d = addDays(ds, -1), n = 0;
        for (var i = 0; i < 14; i++) {
            if (HOLI[d]) return n + 1;
            if (isWorkday(d)) n++;
            if (n + 1 > 3) return 0;
            d = addDays(d, -1);
        }
        return 0;
    }
    function dayDiff(a, b) { return Math.round((parse(b) - parse(a)) / 86400000); }

    // 寒暑假窗口：pre = 假前 2 周 / in = 假中 / post = 假后 1 周（寒暑假窗口内的法定节假日走寒暑假规则）
    function windowOf(ds) {
        for (var i = 0; i < D.BREAKS.length; i++) {
            var b = D.BREAKS[i];
            if (ds >= b.preStart && ds <= b.preEnd) return { t: 'pre', b: b };
            if (ds >= b.start && ds <= b.end) return { t: 'in', b: b };
            if (ds >= b.postStart && ds <= b.postEnd) return { t: 'post', b: b };
        }
        return null;
    }

    function classify(ds) {
        var w = windowOf(ds);
        if (w) {
            if (w.t === 'pre') {
                var left = dayDiff(ds, w.b.start); // 距放假还有 N 天
                var pct = 35 + Math.round(20 * Math.max(0, Math.min(13, left - 1)) / 13);
                return { key: 'prevac', name: '期末冲刺', pct: pct, meta: '距' + w.b.name + '还有 ' + left + ' 天' };
            }
            if (w.t === 'in') {
                var leftEnd = dayDiff(ds, w.b.end) + 1; // 含今天还剩 N 天
                if (leftEnd <= 3) return { key: 'vacend', name: '假期余额不足', pct: 70, meta: w.b.name + '最后 ' + leftEnd + ' 天' };
                return { key: 'vac', name: '躺平充电', pct: 90, meta: w.b.name + '第 ' + (dayDiff(w.b.start, ds) + 1) + ' 天' };
            }
            var idx = dayDiff(w.b.postStart, ds) + 1;
            var pct2 = 25 + Math.round(30 * Math.max(0, Math.min(6, idx - 1)) / 6);
            return { key: 'postvac', name: '开学焦虑', pct: pct2, meta: '开学第 ' + idx + ' 天' };
        }
        var hol = HOLI[ds];
        if (hol) return { key: 'holiday', name: '假期充电', pct: 85, meta: hol.name + ' · 放假中', hol: hol };
        var pre = preDays(ds);
        if (pre > 0) {
            var T1 = [120, 105, 90]; // 距假期越近越嗨：第 1 个工作日（最后一天）= 120%，第 3 个 = 90%
            return { key: 'pre', name: '节前狂欢', pct: T1[pre - 1], meta: '节前第 ' + pre + ' 个工作日' };
        }
        var post = postDays(ds);
        if (post > 0) {
            var T2 = [20, 35, 50];
            return { key: 'post', name: '收假忧郁', pct: T2[post - 1], meta: '收假第 ' + post + ' 个工作日' };
        }
        var wd = weekdayOf(ds);
        var BASE = {
            1: ['mon', '周一丧', 30], 2: ['tue', '爬坡日', 55], 3: ['wed', '半场续航', 50],
            4: ['thu', '黎明前', 75], 5: ['fri', '周五嗨', 95],
            6: ['weekend', '自留地', 80], 0: ['weekend', '自留地', 80]
        };
        var b = BASE[wd];
        return {
            key: b[0], name: b[1], pct: b[2],
            meta: (wd === 0 || wd === 6) ? '周末 · 自留地' : '星期' + '一二三四五'[wd - 1]
        };
    }

    /* ---------------- 文案槽位：hash(yyyy-mm-dd|槽位salt) 取模（同天稳定/跨天轮换） ---------------- */
    function hashStr(s) {
        var h = 5381, i;
        for (i = 0; i < s.length; i++) h = ((h << 5) + h + s.charCodeAt(i)) >>> 0;
        return h;
    }
    function pick(arr, ds, salt) { return arr[hashStr(ds + '|' + salt) % arr.length]; }

    function clampPct(n) { return Math.max(5, Math.min(130, Math.round(n))); }
    function curPct(cls) {
        return clampPct(cls.pct + D.EMOJIS[state.emo].d);
    }
    // 热力色：20% → 红，120% → 绿
    function heatColor(pct) {
        var t = Math.max(0, Math.min(1, (pct - 20) / 100));
        return 'hsl(' + Math.round(t * 140) + ',72%,58%)';
    }

    /* ---------------- 状态机 ---------------- */
    function show(mode) {
        state.mode = mode;
        ['landing', 'main'].forEach(function (m) {
            $('mc-' + m).classList.toggle('is-on', m === mode);
        });
        var page = document.querySelector('.mc-page');
        window.scrollTo(0, page ? Math.max(0, page.offsetTop - 70) : 0);
    }

    /* ---------------- 开场页 ---------------- */
    function renderLanding() {
        var ul = $('mc-landing-emos');
        ul.innerHTML = '';
        D.EMOJIS.forEach(function (em) {
            var li = document.createElement('li');
            var i = document.createElement('i');
            i.style.cssText = 'font-style:normal;font-size:1rem';
            i.textContent = em.e;
            li.appendChild(i);
            li.appendChild(document.createTextNode(em.label));
            ul.appendChild(li);
        });
    }

    /* ---------------- 当日心情大卡 ---------------- */
    function renderMood() {
        var ds = state.sel;
        var cls = classify(ds);
        var pct = curPct(cls);
        var heat = heatColor(pct);

        $('mc-hero-emoji').textContent = D.EMOJIS[state.emo].e;
        $('mc-hero-emoji').style.background = heat;
        $('mc-hero-date').textContent = cnDate(ds) + (ds === todayStr() ? ' · 今天' : '');
        $('mc-hero-name').textContent = cls.name;
        $('mc-hero-tag').textContent = cls.meta;
        $('mc-hero-pct').textContent = pct;
        $('mc-meter-fill').style.width = Math.min(100, pct / 130 * 100) + '%';
        $('mc-meter-fill').style.background = heat;
        $('mc-hero-main').textContent = pick(D.MAIN[cls.key], ds, 'main');
        $('mc-hero-roast').textContent = '—— ' + pick(D.ROAST, ds, 'roast');
        $('mc-chip-do').textContent = pick(D.DOS, ds, 'do');
        $('mc-chip-dont').textContent = pick(D.DONTS, ds, 'dont');
        $('mc-chip-lucky').textContent = '幸运物：' + pick(D.LUCKY, ds, 'lucky');
        $('mc-advice').textContent = '解压建议：' + pick(D.ADVICE, ds, 'advice');

        var egg = $('mc-egg');
        if (cls.hol && cls.hol.egg) { egg.textContent = cls.hol.egg; egg.hidden = false; }
        else { egg.hidden = true; }
    }

    /* ---------------- emoji 5 选 1 ---------------- */
    function renderEmos() {
        var ul = $('mc-emos');
        ul.innerHTML = '';
        D.EMOJIS.forEach(function (em, i) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mc-emo' + (i === state.emo ? ' is-picked' : '');
            btn.setAttribute('role', 'radio');
            btn.setAttribute('aria-checked', i === state.emo ? 'true' : 'false');
            var b = document.createElement('b');
            b.textContent = em.e;
            var it = document.createElement('i');
            it.textContent = em.label + (em.d ? (em.d > 0 ? ' +' + em.d + '%' : ' ' + em.d + '%') : '');
            btn.appendChild(b);
            btn.appendChild(it);
            btn.addEventListener('click', function () {
                state.emo = i;
                renderEmos();
                renderMood();
            });
            li.appendChild(btn);
            ul.appendChild(li);
        });
    }

    /* ---------------- 月历热力网格 ---------------- */
    function renderCal() {
        $('mc-cal-title').textContent = state.cy + ' 年 ' + state.cm + ' 月';
        var grid = $('mc-cal-grid');
        grid.innerHTML = '';
        var firstWd = new Date(state.cy, state.cm - 1, 1).getDay();
        var days = new Date(state.cy, state.cm, 0).getDate();
        var today = todayStr(), i;
        for (i = 0; i < firstWd; i++) {
            var blank = document.createElement('span');
            blank.className = 'mc-cell is-blank';
            grid.appendChild(blank);
        }
        for (i = 1; i <= days; i++) {
            (function (day) {
                var ds = state.cy + '-' + pad(state.cm) + '-' + pad(day);
                var cls = classify(ds);
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mc-cell';
                btn.textContent = day;
                btn.style.background = heatColor(cls.pct);
                btn.title = cnDate(ds) + ' · ' + cls.name + ' ' + cls.pct + '%';
                btn.setAttribute('aria-label', btn.title);
                if (ds === today) btn.classList.add('is-today');
                if (ds === state.sel) btn.classList.add('is-sel');
                if (isHoliday(ds)) btn.classList.add('is-hol');
                btn.addEventListener('click', function () {
                    state.sel = ds;
                    renderMood();
                    renderCal();
                });
                grid.appendChild(btn);
            })(i);
        }
    }

    function shiftMonth(n) {
        var m = state.cm + n, y = state.cy;
        if (m < 1) { m = 12; y--; }
        if (m > 12) { m = 1; y++; }
        state.cm = m; state.cy = y;
        renderCal();
    }

    /* ---------------- 解压小互动：捏泡泡（气泡膜） ---------------- */
    var BUBBLE_N = 12;
    var soundOn = true;
    var popAudio = null;
    /* 爆炸音效：Web Audio 合成「水泡」（Q 弹上滑 + 短噪声点缀），无外部音频文件 */
    function popSound() {
        if (!soundOn) return;
        try {
            var AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            if (!popAudio) popAudio = new AC();
            if (popAudio.state === 'suspended') popAudio.resume();
            var t = popAudio.currentTime;
            // 正弦上滑「咕噜」
            var osc = popAudio.createOscillator(); osc.type = 'sine';
            osc.frequency.setValueAtTime(220 + Math.random() * 40, t);
            osc.frequency.exponentialRampToValueAtTime(620 + Math.random() * 60, t + .07);
            var g = popAudio.createGain();
            g.gain.setValueAtTime(3, t);            // 音量 ×10
            g.gain.exponentialRampToValueAtTime(.01, t + .1);
            osc.connect(g); g.connect(popAudio.destination);
            osc.start(t); osc.stop(t + .11);
            // 短噪声点缀
            var len = Math.floor(popAudio.sampleRate * .03);
            var buf = popAudio.createBuffer(1, len, popAudio.sampleRate);
            var data = buf.getChannelData(0);
            for (var i = 0; i < len; i++) data[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / len, 2.8);
            var src = popAudio.createBufferSource(); src.buffer = buf;
            var f = popAudio.createBiquadFilter(); f.type = 'bandpass';
            f.frequency.value = 900; f.Q.value = 1.2;
            var ng = popAudio.createGain(); ng.gain.value = 1;
            src.connect(f); f.connect(ng); ng.connect(popAudio.destination);
            src.start(t); src.stop(t + .05);
        } catch (err) { /* 静默降级 */ }
    }
    function popBubblesFx(host) {
        if (reduceMotion || !host) return;
        // 白闪
        var flash = document.createElement('span');
        flash.className = 'mc-flash';
        host.appendChild(flash);
        setTimeout(function () { if (flash.parentNode) flash.parentNode.removeChild(flash); }, 300);
        // 双层冲击波
        var ring = document.createElement('span');
        ring.className = 'mc-pop-ring';
        host.appendChild(ring);
        setTimeout(function () { if (ring.parentNode) ring.parentNode.removeChild(ring); }, 460);
        var ring2 = document.createElement('span');
        ring2.className = 'mc-pop-ring2';
        host.appendChild(ring2);
        setTimeout(function () { if (ring2.parentNode) ring2.parentNode.removeChild(ring2); }, 380);
        var d, ang, dist;
        // 破裂水珠 8 粒
        for (d = 0; d < 8; d++) {
            var drop = document.createElement('span');
            drop.className = 'mc-drop';
            ang = (Math.PI * 2 * d) / 8 + (Math.random() * .5 - .25);
            dist = 36 + Math.random() * 34;
            drop.style.setProperty('--dx', Math.cos(ang) * dist + 'px');
            drop.style.setProperty('--dy', Math.sin(ang) * dist + 'px');
            host.appendChild(drop);
            (function (el) { setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 660); })(drop);
        }
        // 飞散薄膜碎片 6 片
        for (d = 0; d < 6; d++) {
            var shard = document.createElement('span');
            shard.className = 'mc-shard';
            ang = (Math.PI * 2 * d) / 6 + 0.4 + (Math.random() * .5 - .25);
            dist = 30 + Math.random() * 32;
            shard.style.setProperty('--dx', Math.cos(ang) * dist + 'px');
            shard.style.setProperty('--dy', Math.sin(ang) * dist + 'px');
            shard.style.setProperty('--rot', Math.round(Math.random() * 360) + 'deg');
            host.appendChild(shard);
            (function (el) { setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 760); })(shard);
        }
    }
    /* 爆炸后残留：大块撕裂薄膜碎片，静态留在膜上 */
    function addLeftoverShards(host) {
        if (!host) return;
        for (var s = 0; s < 3; s++) {
            var left = document.createElement('span');
            left.className = 'mc-shard-left';
            left.style.width = (20 + Math.random() * 18) + 'px';
            left.style.height = (13 + Math.random() * 13) + 'px';
            left.style.left = (Math.random() * 62) + '%';
            left.style.top = (Math.random() * 62) + '%';
            left.style.transform = 'rotate(' + Math.round(Math.random() * 360) + 'deg)';
            host.appendChild(left);
        }
    }
    function renderBubbles() {
        var box = $('mc-bubbles');
        box.innerHTML = '';
        box.style.display = '';
        var tip = $('mc-bubble-tip');
        if (tip) tip.style.display = '';
        var bl = $('mc-blessing');
        if (bl) bl.hidden = true;
        state.left = BUBBLE_N;
        $('mc-bubble-left').textContent = BUBBLE_N;
        for (var i = 0; i < BUBBLE_N; i++) {
            var host = document.createElement('span');
            host.className = 'mc-bubble-host';
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'mc-bubble';
            b.setAttribute('aria-label', '捏破泡泡');
            // 按压：穹顶捏扁（触感反馈）
            b.addEventListener('pointerdown', function (e) {
                e.currentTarget.classList.add('is-squeeze');
            });
            b.addEventListener('pointerup', function (e) {
                e.currentTarget.classList.remove('is-squeeze');
            });
            b.addEventListener('pointerleave', function (e) {
                e.currentTarget.classList.remove('is-squeeze');
            });
            b.addEventListener('click', function (e) {
                var t = e.currentTarget;
                if (t.classList.contains('is-pop') || t.classList.contains('is-burst')) return;
                t.classList.remove('is-squeeze');
                popSound();
                if (navigator.vibrate) { try { navigator.vibrate(18); } catch (err) {} }
                state.left--;
                state.popped++;
                $('mc-bubble-left').textContent = state.left;
                function finish() {
                    t.classList.remove('is-burst');
                    t.classList.add('is-pop');
                    addLeftoverShards(t.parentNode);
                    if (state.left <= 0) setTimeout(showBlessing, 650); // 等爆炸动效收尾后送祝福
                }
                if (reduceMotion) { finish(); return; }
                popBubblesFx(t.parentNode);  // 白闪/冲击波/粒子立即炸开
                t.classList.add('is-burst'); // 压瘪→膨胀白闪 .13s
                setTimeout(finish, 130);
            });
            host.appendChild(b);
            box.appendChild(host);
        }
    }
    /* 捏爆全部泡泡：随机送一句祝福语（替换原礼花效果） */
    function showBlessing() {
        var tip = $('mc-bubble-tip'), box = $('mc-bubbles'), bl = $('mc-blessing');
        if (tip) tip.style.display = 'none';
        if (box) box.style.display = 'none';
        if (!bl) return;
        var pool = (window.TMC_DATA && window.TMC_DATA.BLESSINGS) || [];
        $('mc-blessing-text').textContent = pool.length
            ? pool[Math.floor(Math.random() * pool.length)]
            : '今天也辛苦了，去喝口热水吧';
        bl.hidden = false;
    }
    (function () {
        var again = $('mc-blessing-again');
        if (again) again.addEventListener('click', renderBubbles);
    })();
    (function () {
        var sb = $('mc-sound-btn');
        if (!sb) return;
        sb.addEventListener('click', function () {
            soundOn = !soundOn;
            sb.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
            sb.textContent = soundOn ? '🔊 音效' : '🔇 音效';
        });
    })();

    /* ---------------- 分享卡片（Canvas 750×1334）
     * roundRect / wrapLines / makeQrCanvas 均来自共享 js/qr_helper.js（不拷贝）
     * 主视觉 = 当日心情大卡 + 本周电量 sparkline
     */
    function weekOf(ds) {
        var wd = weekdayOf(ds);
        var monday = addDays(ds, -((wd + 6) % 7));
        var out = [], i;
        for (i = 0; i < 7; i++) out.push(addDays(monday, i));
        return out;
    }

    function drawShareCard(ds, url) {
        var W = 750, H = 1334;
        var c = document.createElement('canvas');
        c.width = W; c.height = H;
        var g = c.getContext('2d');
        var cls = classify(ds);
        var pct = curPct(cls);
        var heat = heatColor(pct);
        var nick = ($('mc-nick-input').value || '').trim().slice(0, 8);
        var bold = function (px) { return '800 ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };
        var reg = function (px, w) { return (w || 400) + ' ' + px + 'px "PingFang SC", "Microsoft YaHei", sans-serif'; };
        var cx = W / 2, maxW = 576;

        // 手账纸底 + 方格
        g.fillStyle = '#FFF7EA'; g.fillRect(0, 0, W, H);
        g.strokeStyle = 'rgba(59,46,30,.055)'; g.lineWidth = 1.5;
        var x, y;
        for (x = 0; x < W; x += 34) { g.beginPath(); g.moveTo(x, 0); g.lineTo(x, H); g.stroke(); }
        for (y = 0; y < H; y += 34) { g.beginPath(); g.moveTo(0, y); g.lineTo(W, y); g.stroke(); }
        roundRect(g, 40, 44, W - 80, H - 88, 26);
        g.lineWidth = 4; g.strokeStyle = 'rgba(59,46,30,.65)'; g.stroke();

        g.textAlign = 'center'; g.textBaseline = 'alphabetic';

        // 顶部站点名
        g.font = reg(28, 700); g.fillStyle = '#B9611F';
        g.fillText(((window.TMC && window.TMC.siteName) || '') + ' · 教师解压日历', cx, 124);

        // 个性化称呼（选填，填写后在分享卡显性展示）
        if (nick) {
            g.font = bold(30); g.fillStyle = '#B9611F';
            var nTxt = '「' + nick + '」的今日心情卡';
            if (g.measureText(nTxt).width > maxW) g.font = bold(Math.floor(30 * maxW / g.measureText(nTxt).width));
            g.fillText(nTxt, cx, 176);
        }

        // 1 当日心情主视觉：大 emoji
        g.save();
        g.beginPath(); g.arc(cx, 262, 80, 0, Math.PI * 2);
        g.fillStyle = heat; g.fill();
        g.lineWidth = 6; g.strokeStyle = 'rgba(59,46,30,.75)'; g.stroke();
        g.clip();
        g.textBaseline = 'middle'; g.textAlign = 'center';
        g.font = '78px "PingFang SC", "Microsoft YaHei", sans-serif';
        g.fillText(D.EMOJIS[state.emo].e, cx, 270);
        g.restore();
        g.textBaseline = 'alphabetic';

        // 状态名（超宽等比缩）
        g.font = bold(76); g.fillStyle = '#3B2E1E';
        if (g.measureText(cls.name).width > maxW) g.font = bold(Math.floor(76 * maxW / g.measureText(cls.name).width));
        g.fillText(cls.name, cx, 392);
        g.fillStyle = '#8A7A62';
        var dTxt = cnDate(ds) + ' · ' + cls.meta;
        var dFont = 28;
        g.font = reg(dFont, 700);
        while (g.measureText(dTxt).width > maxW && dFont > 19) {
            dFont -= 1;
            g.font = reg(dFont, 700);
        }
        g.fillText(dTxt, cx, 436);

        // 电量胶囊
        roundRect(g, cx - 170, 466, 340, 66, 33);
        g.fillStyle = heat; g.fill();
        g.lineWidth = 3; g.strokeStyle = 'rgba(59,46,30,.6)'; g.stroke();
        g.font = bold(36); g.fillStyle = '#3B2E1E';
        g.fillText('今日电量 ' + pct + '%', cx, 510);

        // 2 主文案 + 吐槽后缀（金句位，2 行封顶）
        var mainTxt = pick(D.MAIN[cls.key], ds, 'main');
        var roastTxt = '—— ' + pick(D.ROAST, ds, 'roast');
        var qTop = 560;
        roundRect(g, cx - maxW / 2, qTop, maxW, 160, 14);
        g.fillStyle = '#FFF1DA'; g.fill();
        g.lineWidth = 3; g.strokeStyle = 'rgba(201,164,94,.9)'; g.stroke();
        g.fillStyle = '#3B2E1E';
        var mfont = 34;
        g.font = reg(mfont, 700);
        var mlines = wrapLines(g, mainTxt, maxW - 64);
        while (mlines.length > 2 && mfont > 26) {
            mfont -= 2;
            g.font = reg(mfont, 700);
            mlines = wrapLines(g, mainTxt, maxW - 64);
        }
        mlines = mlines.slice(0, 2);
        var my = qTop + (mlines.length > 1 ? 62 : 78);
        mlines.forEach(function (ln) { g.fillText(ln, cx, my); my += 46; });
        g.font = reg(24, 400); g.fillStyle = '#8A7A62';
        var rlines = wrapLines(g, roastTxt, maxW - 64).slice(0, 1);
        g.fillText(rlines[0] || '', cx, qTop + 138);

        // 3 本周电量 sparkline（周一 → 周日）
        var cardX = cx - maxW / 2, cardY = 744, cardW = maxW, cardH = 260;
        roundRect(g, cardX, cardY, cardW, cardH, 14);
        g.fillStyle = '#FFFFFF'; g.fill();
        g.lineWidth = 3; g.strokeStyle = 'rgba(59,46,30,.4)'; g.stroke();
        g.textAlign = 'left';
        g.font = reg(25, 800); g.fillStyle = '#3B2E1E';
        g.fillText('本周电量曲线（周一 → 周日）', cardX + 26, cardY + 44);

        var week = weekOf(ds);
        var vals = week.map(function (d) { return curPct(classify(d)); });
        var chX = cardX + 50, chW = cardW - 92, chTop = cardY + 66, chH = 138;
        var vMax = 130, vMin = 0;
        function px(i) { return chX + i * (chW / 6); }
        function py(v) { return chTop + chH - (Math.max(vMin, Math.min(vMax, v)) - vMin) / (vMax - vMin) * chH; }
        // 参考线（100% 满电）
        g.strokeStyle = 'rgba(59,46,30,.18)'; g.lineWidth = 1.5;
        g.setLineDash([6, 6]);
        g.beginPath(); g.moveTo(chX - 8, py(100)); g.lineTo(chX + chW + 8, py(100)); g.stroke();
        g.setLineDash([]);
        // 曲线
        g.strokeStyle = '#E8833A'; g.lineWidth = 4; g.lineJoin = 'round';
        g.beginPath();
        vals.forEach(function (v, i) { if (i === 0) g.moveTo(px(i), py(v)); else g.lineTo(px(i), py(v)); });
        g.stroke();
        // 数据点（选中日高亮）
        vals.forEach(function (v, i) {
            g.beginPath(); g.arc(px(i), py(v), week[i] === ds ? 8 : 5.5, 0, Math.PI * 2);
            g.fillStyle = heatColor(v); g.fill();
            g.lineWidth = week[i] === ds ? 3.5 : 2;
            g.strokeStyle = week[i] === ds ? '#3B2E1E' : 'rgba(255,255,255,.9)';
            g.stroke();
        });
        // 选中日数值
        var si = week.indexOf(ds);
        if (si >= 0) {
            g.textAlign = 'center';
            g.font = bold(24); g.fillStyle = '#3B2E1E';
            g.fillText(vals[si] + '%', px(si), Math.max(chTop + 20, py(vals[si]) - 16));
        }
        // 星期标签
        g.font = reg(21, 700); g.fillStyle = '#8A7A62';
        week.forEach(function (d, i) {
            g.textAlign = 'center';
            g.fillText('周' + WEEK_CN[weekdayOf(d)], px(i), cardY + cardH - 24);
            if (d === ds) {
                g.font = bold(21); g.fillStyle = '#B9611F';
                g.fillText('▲', px(i), cardY + cardH - 48);
                g.font = reg(21, 700); g.fillStyle = '#8A7A62';
            }
        });

        // 4 今日宜 / 忌（单行，超宽缩字号）
        g.textAlign = 'center';
        var doTxt = pick(D.DOS, ds, 'do'), dontTxt = pick(D.DONTS, ds, 'dont');
        var ndTxt = doTxt + ' ｜ ' + dontTxt;
        var ndFont = 25;
        g.font = reg(ndFont, 700);
        while (g.measureText(ndTxt).width > maxW && ndFont > 18) {
            ndFont -= 1;
            g.font = reg(ndFont, 700);
        }
        g.fillStyle = '#6B5B42';
        g.fillText(ndTxt, cx, 1042);

        // 5 钩子文案（带称呼，单行超宽缩字号）
        var who = nick || '我';
        var hook = who + '的今天是【' + cls.name + '】，电量 ' + pct + '%——你的情绪天气如何？';
        var hFont = 30;
        g.font = bold(hFont);
        while (g.measureText(hook).width > maxW && hFont > 20) {
            hFont -= 1;
            g.font = bold(hFont);
        }
        g.fillStyle = '#E4573D';
        g.fillText(hook, cx, 1086);

        // 6 底部二维码（共享封装，手账暖棕主题色）
        var qr = makeQrCanvas(url, 144, '#8A5A2B');
        var qx = 96, qy2 = 1116;
        roundRect(g, qx - 12, qy2 - 12, 168, 168, 14);
        g.fillStyle = '#ffffff'; g.fill();
        if (qr) {
            g.imageSmoothingEnabled = false;
            g.drawImage(qr, qx, qy2, 144, 144);
            g.imageSmoothingEnabled = true;
            g.textAlign = 'left';
            g.font = bold(29); g.fillStyle = '#3B2E1E';
            g.fillText('扫码看看今日心情', qx + 196, qy2 + 62);
            g.font = reg(23, 700); g.fillStyle = '#8A7A62';
            g.fillText('教师解压日历 · 无需登录', qx + 196, qy2 + 102);
        }
        g.textAlign = 'center';
        g.font = reg(22); g.fillStyle = '#8A7A62';
        g.fillText('仅供娱乐参考 · 不采集任何个人信息', cx, 1312);

        return c.toDataURL('image/png');
    }

    /* ---------------- 分享弹层（焦点暂存/恢复/Esc/遮罩关闭） ---------------- */
    var sharePrevFocus = null;

    function openShare() {
        var modal = $('mc-share');
        var img = $('mc-share-img');
        var hint = $('mc-share-hint');
        sharePrevFocus = document.activeElement;
        modal.classList.add('is-on');
        $('mc-share-close').focus();
        hint.textContent = '正在生成…';
        img.removeAttribute('src');

        var url = window.location.href.split('#')[0];
        var ready = (document.fonts && document.fonts.ready)
            ? Promise.race([document.fonts.ready, new Promise(function (r) { setTimeout(r, 1200); })])
            : Promise.resolve();

        ready.then(function () {
            var dataUrl;
            try { dataUrl = drawShareCard(state.sel, url); }
            catch (e) { hint.textContent = '生成失败了，请刷新后再试一次'; return; }
            if (!dataUrl) { hint.textContent = '生成失败了，请刷新后再试一次'; return; }
            img.src = dataUrl;
            var wx = /MicroMessenger/i.test(navigator.userAgent || '');
            hint.textContent = wx ? '长按上方图片保存到相册' : '长按 / 右键图片即可保存';
        });
    }

    function closeShare() {
        $('mc-share').classList.remove('is-on');
        if (sharePrevFocus && sharePrevFocus.focus) sharePrevFocus.focus();
        sharePrevFocus = null;
    }

    /* ---------------- 绑定 ---------------- */
    $('mc-start').addEventListener('click', function () { show('main'); });
    $('mc-back-btn').addEventListener('click', function () { show('landing'); });
    $('mc-share-btn').addEventListener('click', openShare);
    $('mc-share-close').addEventListener('click', closeShare);
    $('mc-share-ok').addEventListener('click', closeShare);
    $('mc-share').addEventListener('click', function (e) { if (e.target === this) closeShare(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeShare();
    });
    $('mc-prev').addEventListener('click', function () { shiftMonth(-1); });
    $('mc-next').addEventListener('click', function () { shiftMonth(1); });

    /* ---------------- 初始化 ---------------- */
    var now = new Date();
    state.sel = todayStr();
    state.cy = now.getFullYear();
    state.cm = now.getMonth() + 1;
    renderLanding();
    renderEmos();
    renderMood();
    renderCal();
    renderBubbles();

    // 供浏览器 console 按日期跑规则引擎用例
    window.TMC_CLASSIFY = classify;
})();
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
