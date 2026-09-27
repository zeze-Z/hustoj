<?php
$show_title="测一测你是哪位教育家 - $OJ_NAME";
// 传播型 H5 极简壳：隐藏 OJ 顶栏与页脚主体（header.php/footer.php 按 $hide_chrome 分支，保留骨架+限流+极简署名）
$hide_chrome = true;
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
// 页面无表单、无入库、无用户态数据：仅注入站点名给分享卡片文案（JSON 十六进制转义防注入）
$ts_json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<style>
/* ===== 测一测你是哪位教育家（前缀 ts-）绘本贴纸风，移动端 375px 起 ===== */
:root{
    --ts-paper:#FFF8F0; --ts-ink:#2B4C6F; --ts-ink-soft:#56708C;
    --ts-coral:#FF7B54; --ts-coral-ink:#B83E1B; --ts-honey:#FFC93C; --ts-mint:#6BCB9A; --ts-blush:#FF9FB2;
    --ts-line:#F0E1CE; --ts-accent:#FF7B54; --ts-accent-fg:#FFFFFF;
    --ts-display:'ZCOOL KuaiLe',"PingFang SC","Microsoft YaHei",sans-serif;
    --ts-body:"PingFang SC","Microsoft YaHei",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    --ts-hard:4px 4px 0 rgba(43,76,111,.85);
}
.ts-page{
    max-width:560px; margin:0 auto; padding:18px 14px 46px;
    min-height:calc(100vh - 48px); /* 极简壳后仅剩署名行高度 */
    background-color:var(--ts-paper);
    background-image:radial-gradient(rgba(43,76,111,.09) 1.3px, transparent 1.3px);
    background-size:24px 24px;
    color:var(--ts-ink); font-family:var(--ts-body); line-height:1.65;
}
.ts-page *{box-sizing:border-box}
.ts-state{display:none}
.ts-state.is-on{display:block}
.ts-card{background:#fff;border:2px solid var(--ts-ink);border-radius:20px;box-shadow:var(--ts-hard)}
.ts-h3{font-family:var(--ts-display);font-size:1.18rem;font-weight:400;margin:0 0 10px;color:var(--ts-ink);letter-spacing:.02em}
.ts-wave{display:block;width:100%;height:12px;margin:16px 0}
.ts-btn{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;min-height:52px;padding:10px 16px;border-radius:16px;
    border:2px solid var(--ts-ink);font-family:var(--ts-body);font-weight:800;font-size:1.02rem;
    cursor:pointer;text-decoration:none;transition:transform .12s ease, box-shadow .12s ease;
}
.ts-btn:active{transform:translate(3px,3px);box-shadow:1px 1px 0 rgba(43,76,111,.85)!important}
.ts-btn:focus-visible,a.ts-btn:focus-visible{outline:3px solid var(--ts-coral);outline-offset:3px}
.ts-btn--go{background:var(--ts-coral);color:var(--ts-ink);font-size:1.2rem;box-shadow:var(--ts-hard)}
.ts-btn--mint{background:var(--ts-mint);color:#1F3D2E;box-shadow:var(--ts-hard)}
.ts-btn--ghost{background:#fff;color:var(--ts-ink);box-shadow:2px 2px 0 rgba(43,76,111,.55)}

/* ---- 落地页 ---- */
.ts-cover{padding:26px 20px 22px;transform:rotate(-.6deg)}
.ts-cover-kicker{margin:0 0 6px;font-size:.9rem;font-weight:700;color:var(--ts-coral-ink);letter-spacing:.06em}
.ts-title{
    font-family:var(--ts-display);font-weight:400;font-size:clamp(1.9rem,8.4vw,2.5rem);
    line-height:1.25;margin:0;color:var(--ts-ink);
}
.ts-title em{font-style:normal;color:var(--ts-coral-ink)}
.ts-sub{margin:10px 0 0;font-size:.98rem;color:var(--ts-ink-soft)}
.ts-count{
    display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;
    margin:18px 0 4px;padding:12px 16px;border:2px dashed rgba(43,76,111,.4);
    border-radius:16px;background:#FFFDF8;font-weight:700;color:var(--ts-ink);
}
.ts-count b{
    font-family:var(--ts-display);font-weight:400;font-size:2.6rem;line-height:1;
    color:var(--ts-coral-ink);text-shadow:2px 2px 0 rgba(255,201,60,.65);
}
.ts-avatars{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0 4px;padding:0;list-style:none}
.ts-avatars li{
    display:flex;align-items:center;gap:6px;padding:5px 11px 5px 6px;
    background:#fff;border:2px solid var(--ts-ink);border-radius:999px;
    font-size:.85rem;font-weight:800;box-shadow:2px 2px 0 rgba(43,76,111,.6);
}
.ts-avatars li:nth-child(odd){transform:rotate(-1.6deg)}
.ts-avatars li:nth-child(even){transform:rotate(1.4deg)}
.ts-avatars i{
    display:grid;place-items:center;width:24px;height:24px;border-radius:50%;
    border:1.5px solid var(--ts-ink);font-style:normal;font-size:.9rem;
}
.ts-meta{margin:12px 0 0;text-align:center;font-size:.86rem;color:var(--ts-ink-soft)}
.ts-start{margin-top:16px}
.ts-landing-note{margin:14px 0 0;text-align:center;font-size:.8rem;color:var(--ts-ink-soft)}

/* ---- 答题页 ---- */
.ts-qbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}
.ts-dots{display:flex;gap:6px;flex:1}
.ts-dots span{flex:1;height:7px;border-radius:99px;background:#EADBC6;transition:background .2s ease}
.ts-dots span.is-done{background:var(--ts-ink)}
.ts-dots span.is-cur{background:var(--ts-honey);box-shadow:0 0 0 2px rgba(43,76,111,.25)}
.ts-qnum{font-size:.85rem;font-weight:800;color:var(--ts-ink-soft);white-space:nowrap}
.ts-back{
    background:none;border:none;padding:10px 8px;min-height:44px;font:inherit;font-size:.88rem;font-weight:700;
    color:var(--ts-ink-soft);cursor:pointer;text-decoration:underline;
}
.ts-back:focus-visible{outline:3px solid var(--ts-coral);outline-offset:2px;border-radius:6px}
.ts-qcard{padding:20px 16px 18px}
.ts-qcard.is-in{animation:ts-pop .26s ease both}
@keyframes ts-pop{from{opacity:0;transform:translateY(14px) rotate(.4deg)}to{opacity:1;transform:none}}
.ts-qscene{
    display:inline-block;padding:4px 12px;border-radius:999px;background:var(--ts-honey);
    border:2px solid var(--ts-ink);font-size:.78rem;font-weight:800;transform:rotate(-2deg);
    box-shadow:2px 2px 0 rgba(43,76,111,.7);
}
.ts-qtitle{
    font-family:var(--ts-display);font-weight:400;font-size:1.4rem;line-height:1.45;
    margin:14px 0 16px;color:var(--ts-ink);
}
.ts-opts{display:flex;flex-direction:column;gap:12px;margin:0;padding:0;list-style:none}
.ts-opt{
    display:flex;align-items:center;gap:12px;width:100%;min-height:58px;
    padding:12px 14px;text-align:left;background:#FFFDF8;color:var(--ts-ink);
    border:2px solid rgba(43,76,111,.35);border-radius:16px;
    font:inherit;font-size:.98rem;font-weight:700;line-height:1.5;cursor:pointer;
    transition:border-color .15s ease, background .15s ease, transform .12s ease;
}
.ts-opt:hover{border-color:var(--ts-ink);background:#fff}
.ts-opt:focus-visible{outline:3px solid var(--ts-coral);outline-offset:2px}
.ts-opt.is-picked{border-color:var(--ts-ink);background:#FFF1DF;transform:translate(2px,2px);box-shadow:2px 2px 0 rgba(43,76,111,.6)}
.ts-opt b{
    flex:0 0 auto;display:grid;place-items:center;width:32px;height:32px;border-radius:50%;
    border:2px solid var(--ts-ink);font-family:var(--ts-display);font-weight:400;font-size:1rem;
    background:#fff;
}
.ts-opts li:nth-child(1) .ts-opt b{background:var(--ts-honey)}
.ts-opts li:nth-child(2) .ts-opt b{background:var(--ts-mint)}
.ts-opts li:nth-child(3) .ts-opt b{background:var(--ts-blush)}
.ts-opts li:nth-child(4) .ts-opt b{background:#A9C6F0}
.ts-qfoot{margin:14px 0 0;text-align:center;font-size:.82rem;color:var(--ts-ink-soft)}

/* ---- 结果页 ---- */
.ts-block{margin-bottom:18px}
.ts-ring-wrap{position:relative;width:172px;margin:4px auto 0;text-align:center}
.ts-ring{width:172px;height:172px;display:block;transform:rotate(-90deg)}
.ts-ring circle{fill:none;stroke-width:13;stroke-linecap:round}
.ts-ring .ts-ring-bg{stroke:#F1E2CD}
.ts-ring .ts-ring-arc{stroke:var(--ts-accent)}
.ts-ring-num{
    position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;
}
.ts-ring-num span{font-family:var(--ts-display);font-weight:400;font-size:3rem;line-height:1;color:var(--ts-ink)}
.ts-ring-num i{font-style:normal;font-size:.9rem;font-weight:800;color:var(--ts-ink-soft)}
.ts-ring-cap{margin-top:6px;font-size:.85rem;font-weight:800;color:var(--ts-ink-soft)}
.ts-identity{text-align:center;padding:18px 16px 20px}
.ts-avatar{
    width:78px;height:78px;margin:0 auto 10px;border-radius:50%;display:grid;place-items:center;
    font-size:2.4rem;background:var(--ts-accent);border:2px solid var(--ts-ink);
    box-shadow:3px 3px 0 rgba(43,76,111,.85);transform:rotate(-5deg);
}
.ts-lead{margin:0;font-size:.95rem;font-weight:700;color:var(--ts-ink-soft)}
.ts-name{
    font-family:var(--ts-display);font-weight:400;font-size:2.3rem;line-height:1.2;
    margin:2px 0 4px;color:var(--ts-ink);
}
.ts-epithet{margin:0;font-size:1rem;font-weight:700;color:var(--ts-accent);filter:brightness(.6)}
.ts-tags,.ts-pills{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin:0;padding:0;list-style:none}
.ts-tags{margin-bottom:10px}
.ts-tag{
    display:inline-block;padding:6px 14px;border-radius:999px;background:#fff;
    color:var(--ts-ink);border:2.5px solid var(--ts-accent);font-weight:800;font-size:.92rem;
    box-shadow:3px 3px 0 rgba(43,76,111,.85);
}
.ts-tags li:nth-child(1){transform:rotate(-2deg)}
.ts-tags li:nth-child(2){transform:rotate(1.5deg)}
.ts-tags li:nth-child(3){transform:rotate(-1deg)}
.ts-pill{
    display:inline-block;padding:5px 12px;border:2px dashed rgba(43,76,111,.45);
    border-radius:999px;color:var(--ts-ink-soft);font-size:.85rem;font-weight:700;background:#FFFDF8;
}
.ts-quote{position:relative;padding:20px 18px 16px;background:#FFFDF8;border-radius:18px;
    border:2px solid var(--ts-ink);box-shadow:var(--ts-hard);overflow:hidden}
.ts-quote::before{
    content:"“";position:absolute;top:-14px;left:8px;font-family:var(--ts-display);
    font-size:5.4rem;line-height:1;color:rgba(255,123,84,.35);
}
.ts-quote blockquote{
    position:relative;margin:0;font-family:var(--ts-display);font-weight:400;
    font-size:1.32rem;line-height:1.6;color:var(--ts-ink);
}
.ts-quote cite{display:block;margin-top:8px;font-style:normal;font-size:.88rem;font-weight:800;color:var(--ts-ink-soft);text-align:right}
.ts-reason{
    padding:16px 16px;border-radius:16px;background:#FFFDF8;border:2px dashed rgba(43,76,111,.4);
    font-size:.97rem;line-height:1.85;color:#3E5872;
}
.ts-radar{width:100%;height:auto;display:block}
.ts-second{
    display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:16px;
    background:#fff;border:2px solid var(--ts-ink);box-shadow:var(--ts-hard);
}
.ts-second-emoji{
    flex:0 0 auto;width:52px;height:52px;border-radius:50%;display:grid;place-items:center;
    font-size:1.6rem;border:2px solid var(--ts-ink);
}
.ts-second-body{flex:1;min-width:0}
.ts-second-body p{margin:0}
.ts-second-label{font-size:.8rem;font-weight:800;color:var(--ts-ink-soft)}
.ts-second-name{font-family:var(--ts-display);font-weight:400;font-size:1.28rem;color:var(--ts-ink)}
.ts-second-desc{font-size:.85rem;color:var(--ts-ink-soft)}
.ts-second-pct{flex:0 0 auto;font-family:var(--ts-display);font-weight:400;font-size:1.5rem;color:var(--ts-coral-ink)}
.ts-links{display:flex;flex-direction:column;gap:10px;margin:0;padding:0;list-style:none}
.ts-link{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:16px;
    background:#fff;border:2px solid rgba(43,76,111,.3);text-decoration:none;color:var(--ts-ink);
    transition:border-color .15s ease, transform .12s ease;
}
.ts-link:hover{border-color:var(--ts-ink);text-decoration:none;transform:translateY(-2px)}
.ts-link:focus-visible{outline:3px solid var(--ts-coral);outline-offset:2px}
.ts-link-ico{
    flex:0 0 auto;width:42px;height:42px;border-radius:12px;display:grid;place-items:center;
    border:2px solid var(--ts-ink);
}
.ts-link-ico svg{width:22px;height:22px}
.ts-link-t{font-weight:800;font-size:.98rem}
.ts-link-d{font-size:.82rem;color:var(--ts-ink-soft);line-height:1.45}
.ts-link-chev{margin-left:auto;color:var(--ts-ink-soft);flex:0 0 auto}
.ts-login-hint{
    margin:12px 0 0;padding:11px 14px;border-radius:14px;background:#FFF1DF;
    border:2px dashed rgba(43,76,111,.35);font-size:.88rem;font-weight:700;color:#6C5A3E;text-align:center;
}
.ts-login-hint a{color:var(--ts-coral-ink);font-weight:800}
.ts-actions{display:flex;flex-direction:column;gap:10px}
.ts-note{margin:16px 0 0;text-align:center;font-size:.78rem;color:var(--ts-ink-soft)}

/* ---- 分享卡片弹层 ---- */
.ts-modal{
    position:fixed;inset:0;z-index:2000;display:none;align-items:flex-start;justify-content:center;
    padding:24px 14px;overflow-y:auto;background:rgba(23,35,63,.62);
}
.ts-modal.is-on{display:flex}
.ts-modal-box{
    width:100%;max-width:420px;background:var(--ts-paper);border:2px solid var(--ts-ink);
    border-radius:20px;box-shadow:6px 6px 0 rgba(0,0,0,.3);padding:14px 14px 18px;
}
.ts-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.ts-modal-head span{font-family:var(--ts-display);font-weight:400;font-size:1.15rem;color:var(--ts-ink)}
.ts-x{
    width:44px;height:44px;border-radius:50%;border:2px solid var(--ts-ink);background:#fff;
    font-size:1.3rem;line-height:1;color:var(--ts-ink);cursor:pointer;
}
.ts-x:focus-visible{outline:3px solid var(--ts-coral);outline-offset:2px}
.ts-share-img{display:block;width:100%;height:auto;border-radius:12px;border:2px solid var(--ts-ink);background:#fff}
.ts-modal-hint{margin:10px 0 12px;text-align:center;font-size:.86rem;font-weight:700;color:var(--ts-ink-soft)}

@media (min-width: 640px){
    .ts-page{padding-top:26px}
    .ts-cover{padding:34px 32px 28px}
}

@media (prefers-reduced-motion: reduce){
    .ts-page *,.ts-page *::before,.ts-page *::after{
        animation-duration:.001s!important;animation-iteration-count:1!important;transition-duration:.001s!important;
    }
}
</style>

<div class="ts-page">

    <!-- ============ 状态一：落地页 ============ -->
    <section id="ts-landing" class="ts-state is-on" aria-label="测试介绍">
        <div class="ts-card ts-cover">
            <p class="ts-cover-kicker">教师趣味测评</p>
            <h1 class="ts-title">测一测，<br>你是哪位<em>教育家</em>？</h1>
            <svg class="ts-wave" viewBox="0 0 120 10" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 5 Q 7.5 0 15 5 T 30 5 T 45 5 T 60 5 T 75 5 T 90 5 T 105 5 T 120 5"
                      fill="none" stroke="#2B4C6F" stroke-opacity=".4" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
            <p class="ts-sub">8 个课堂瞬间，测出你教学灵魂背后的那位伟人。</p>
            <p class="ts-count">已有 <b id="ts-total">7821</b> 位老师测过</p>
            <ul class="ts-avatars" id="ts-landing-avatars"></ul>
            <button type="button" class="ts-btn ts-btn--go ts-start" id="ts-start">开始测试</button>
            <p class="ts-meta">8 道题 · 约 1 分钟 · 无需登录</p>
        </div>
        <p class="ts-landing-note">纯属娱乐，结果仅供开心参考</p>
    </section>

    <!-- ============ 状态二：答题页 ============ -->
    <section id="ts-quiz" class="ts-state" aria-label="答题">
        <div class="ts-qbar">
            <div class="ts-dots" id="ts-dots" aria-hidden="true"></div>
            <span class="ts-qnum" id="ts-qnum">1 / 8</span>
        </div>
        <button type="button" class="ts-back" id="ts-back" style="display:none">← 上一题</button>
        <div class="ts-card ts-qcard" id="ts-qcard">
            <span class="ts-qscene" id="ts-qscene"></span>
            <h2 class="ts-qtitle" id="ts-qtitle"></h2>
            <ul class="ts-opts" id="ts-opts"></ul>
        </div>
        <p class="ts-qfoot">凭第一直觉选，别想太久</p>
    </section>

    <!-- ============ 状态三：结果页 ============ -->
    <section id="ts-result" class="ts-state" aria-label="测试结果">

        <!-- 1 匹配度环形 -->
        <div class="ts-block">
            <div class="ts-ring-wrap">
                <svg class="ts-ring" viewBox="0 0 172 172" aria-hidden="true">
                    <circle class="ts-ring-bg" cx="86" cy="86" r="72"/>
                    <circle class="ts-ring-arc" id="ts-ring-arc" cx="86" cy="86" r="72"/>
                </svg>
                <div class="ts-ring-num"><span id="ts-pct">0</span><i>匹配度 %</i></div>
            </div>
            <p class="ts-ring-cap">和这位教育家的相似度</p>
        </div>

        <!-- 2 教育家揭晓 -->
        <div class="ts-block ts-card ts-identity">
            <div class="ts-avatar" id="ts-avatar" aria-hidden="true"></div>
            <p class="ts-lead">你的教学灵魂是——</p>
            <h2 class="ts-name" id="ts-name"></h2>
            <p class="ts-epithet" id="ts-epithet"></p>
        </div>

        <!-- 3 人设标签 + 关键词胶囊 -->
        <div class="ts-block">
            <ul class="ts-tags" id="ts-tags"></ul>
            <ul class="ts-pills" id="ts-pills"></ul>
        </div>

        <!-- 4 名言金句 -->
        <div class="ts-block ts-quote">
            <blockquote id="ts-quote"></blockquote>
            <cite id="ts-quote-by"></cite>
        </div>

        <!-- 5 个性化理由 -->
        <div class="ts-block">
            <h3 class="ts-h3">为什么是你</h3>
            <div class="ts-reason" id="ts-reason"></div>
        </div>

        <!-- 6 六维雷达 -->
        <div class="ts-block">
            <h3 class="ts-h3">你的六维教学力</h3>
            <svg class="ts-radar" id="ts-radar" viewBox="0 0 320 258" role="img" aria-label="六维教学力雷达图"></svg>
        </div>

        <!-- 7 第二人格 -->
        <div class="ts-block">
            <div class="ts-second">
                <div class="ts-second-emoji" id="ts-second-emoji" aria-hidden="true"></div>
                <div class="ts-second-body">
                    <p class="ts-second-label">你的隐藏第二人格</p>
                    <p class="ts-second-name" id="ts-second-name"></p>
                    <p class="ts-second-desc" id="ts-second-desc">换个答法，TA 可能会反超</p>
                </div>
                <div class="ts-second-pct" id="ts-second-pct"></div>
            </div>
        </div>

        <!-- 8 转化区 -->
        <div class="ts-block">
            <h3 class="ts-h3">测试完，顺手玩点好的</h3>
            <ul class="ts-links">
                <li>
                    <a class="ts-link" href="timetable.php">
                        <span class="ts-link-ico" style="background:#FFE7B8">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="10" y="16" width="44" height="38" rx="5" fill="none" stroke="#2B4C6F" stroke-width="5"/>
                                <line x1="10" y1="28" x2="54" y2="28" stroke="#2B4C6F" stroke-width="5"/>
                                <line x1="24" y1="11" x2="24" y2="20" stroke="#2B4C6F" stroke-width="5" stroke-linecap="round"/>
                                <line x1="40" y1="11" x2="40" y2="20" stroke="#2B4C6F" stroke-width="5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="ts-link-t">课程表生成器</span><br>
                            <span class="ts-link-d">挑个主题模板，一键生成可打印课表</span>
                        </span>
                        <svg class="ts-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="ts-link" href="course.php">
                        <span class="ts-link-ico" style="background:#CDEBDD">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <path d="M10 14 h18 a6 6 0 0 1 6 6 v30 a6 6 0 0 0 -6 -6 H10 Z" fill="none" stroke="#2B4C6F" stroke-width="5" stroke-linejoin="round"/>
                                <path d="M54 14 H36 a6 6 0 0 0 -6 6 v30 a6 6 0 0 1 6 -6 h18 Z" fill="none" stroke="#2B4C6F" stroke-width="5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="ts-link-t">课件库</span><br>
                            <span class="ts-link-d">现成课件直接拿去上课，省下备课时间</span>
                        </span>
                        <svg class="ts-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
                <li>
                    <a class="ts-link" href="more.php#games">
                        <span class="ts-link-ico" style="background:#FFD6DD">
                            <svg viewBox="0 0 64 64" aria-hidden="true">
                                <rect x="8" y="20" width="48" height="28" rx="12" fill="none" stroke="#2B4C6F" stroke-width="5"/>
                                <line x1="18" y1="29" x2="18" y2="39" stroke="#2B4C6F" stroke-width="5" stroke-linecap="round"/>
                                <line x1="13" y1="34" x2="23" y2="34" stroke="#2B4C6F" stroke-width="5" stroke-linecap="round"/>
                                <circle cx="44" cy="31" r="3.4" fill="#2B4C6F"/>
                                <circle cx="51" cy="38" r="3.4" fill="#2B4C6F"/>
                            </svg>
                        </span>
                        <span>
                            <span class="ts-link-t">课前小游戏</span><br>
                            <span class="ts-link-d">上课前热个身，学生立刻安静下来</span>
                        </span>
                        <svg class="ts-link-chev" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3l6 5-6 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </li>
            </ul>
            <p class="ts-login-hint"><a href="loginpage.php">登录</a> 解锁更多教学工具（课程管理、作业布置…）</p>
        </div>

        <!-- 9 操作区 -->
        <div class="ts-block ts-actions">
            <button type="button" class="ts-btn ts-btn--mint" id="ts-share-btn">生成分享卡片</button>
            <button type="button" class="ts-btn ts-btn--ghost" id="ts-again-btn">重新测试</button>
        </div>

        <!-- 10 免责 -->
        <p class="ts-note">仅供娱乐参考 · 本测试不采集任何个人信息</p>
    </section>
</div>

<!-- ============ 状态四：分享卡片预览 ============ -->
<div class="ts-modal" id="ts-share" role="dialog" aria-modal="true" aria-label="分享卡片预览">
    <div class="ts-modal-box">
        <div class="ts-modal-head">
            <span>你的分享卡片</span>
            <button type="button" class="ts-x" id="ts-share-close" aria-label="关闭">&times;</button>
        </div>
        <img class="ts-share-img" id="ts-share-img" alt="教学人格分享卡片">
        <p class="ts-modal-hint" id="ts-share-hint">正在生成…</p>
        <button type="button" class="ts-btn ts-btn--ghost" id="ts-share-ok">完成</button>
    </div>
</div>

<script>
window.TTS = {
    siteName: <?php echo json_encode($OJ_NAME, $ts_json_flags); ?>
};
</script>
<link rel="stylesheet" href="template/<?php echo $OJ_TEMPLATE?>/fonts/zcool-kuaile.css">
<!-- 通关礼花：canvas-confetti CDN + 项目公共 game_confetti.js（CDN 失败则静默降级） -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/game_confetti.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/qrcode.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/teacher_test_data.js"></script>
<script>
(function () {
    'use strict';
    var D = window.TT_DATA;
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
            $('ts-' + m).classList.toggle('is-on', m === mode);
        });
        var page = document.querySelector('.ts-page');
        window.scrollTo(0, page ? Math.max(0, page.offsetTop - 70) : 0);
    }

    /* ---------------- 落地页 ---------------- */
    function renderLanding() {
        $('ts-total').textContent = D.MATCHED_TOTAL;
        var ul = $('ts-landing-avatars');
        ul.innerHTML = '';
        D.EDUCATORS.forEach(function (e) {
            var li = document.createElement('li');
            var i = document.createElement('i');
            i.style.background = e.color;
            i.textContent = e.emoji;
            li.appendChild(i);
            li.appendChild(document.createTextNode(e.name));
            ul.appendChild(li);
        });
    }

    /* ---------------- 答题页 ---------------- */
    function renderQuiz(animate) {
        var q = D.QUESTIONS[state.qi];
        $('ts-qnum').textContent = (state.qi + 1) + ' / ' + D.QUESTIONS.length;
        $('ts-back').style.display = state.qi > 0 ? '' : 'none';

        var dots = $('ts-dots');
        dots.innerHTML = '';
        D.QUESTIONS.forEach(function (_, i) {
            var s = document.createElement('span');
            if (i < state.qi) s.className = 'is-done';
            if (i === state.qi) s.className = 'is-cur';
            dots.appendChild(s);
        });

        $('ts-qscene').textContent = '场景 ' + (state.qi + 1) + ' · ' + q.scene;
        $('ts-qtitle').textContent = q.title;

        var ul = $('ts-opts');
        ul.innerHTML = '';
        q.options.forEach(function (opt, i) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ts-opt';
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

        var card = $('ts-qcard');
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
     * 选项权重 → 6 维用户向量（L2 归一）→ 与 6 位教育家画像向量余弦相似度
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
        var sims = D.EDUCATORS.map(function (e) {
            var v = l2norm(e.vector), s = 0;
            DIM_KEYS.forEach(function (key) { s += u[key] * v[key]; });
            return { key: e.key, cos: s };
        });
        var LO = 0.67, HI = 1.00, MIN = D.MATCH_MIN, MAX = D.MATCH_MAX;
        sims.forEach(function (s) {
            s.pct = Math.max(MIN, Math.min(MAX, Math.round(MIN + (s.cos - LO) / (HI - LO) * (MAX - MIN))));
        });
        sims.sort(function (a, b) { return b.cos - a.cos; });

        var byKey = {};
        D.EDUCATORS.forEach(function (e) { byKey[e.key] = e; });
        var top = byKey[sims[0].key], second = byKey[sims[1].key];

        // 个性化理由：得分最高 2~3 维度（不足 80 字则升到 3 维）
        var order = DIM_KEYS.slice().sort(function (a, b) { return raw[b] - raw[a]; });
        var useN = (raw[order[2]] > 0 && raw[order[2]] >= raw[order[0]] * 0.35) ? 3 : 2;
        var pickDims = order.slice(0, useN);
        var parts = [top.desc.intro];
        pickDims.forEach(function (dim) {
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
        var page = document.querySelector('.ts-page');
        page.style.setProperty('--ts-accent', top.color);
        page.style.setProperty('--ts-accent-fg', top.fg);

        // 环形
        var arc = $('ts-ring-arc');
        var C = 2 * Math.PI * 72;
        arc.style.strokeDasharray = C.toFixed(2);
        arc.style.strokeDashoffset = C.toFixed(2);

        // 身份
        $('ts-avatar').textContent = top.emoji;
        $('ts-name').textContent = top.name;
        $('ts-epithet').textContent = top.epithet;

        // 标签 / 胶囊
        fillList('ts-tags', 'ts-tag', top.tags);
        fillList('ts-pills', 'ts-pill', top.pills);

        // 名言
        $('ts-quote').textContent = top.quote;
        $('ts-quote-by').textContent = '—— ' + top.name;

        // 理由
        $('ts-reason').textContent = res.reason;

        // 雷达
        renderRadar(res.raw, top.color);

        // 第二人格
        $('ts-second-emoji').textContent = res.second.emoji;
        $('ts-second-emoji').style.background = res.second.color;
        $('ts-second-name').textContent = res.second.name;
        $('ts-second-pct').textContent = res.secondPct + '%';
        // 同分时避免"TA 可能会反超"与相同百分比自相矛盾
        $('ts-second-desc').textContent = (res.secondPct >= res.topPct)
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

    function revealRing(pct) {
        var arc = $('ts-ring-arc');
        var num = $('ts-pct');
        var C = 2 * Math.PI * 72;
        var target = C * (1 - pct / 100);
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
            if (t < 1) requestAnimationFrame(step);
            else num.textContent = pct;
        }
        arc.style.strokeDashoffset = C.toFixed(2);
        requestAnimationFrame(step);
    }

    /* ---------------- 六维雷达（SVG） ---------------- */
    function renderRadar(raw, color) {
        var svg = $('ts-radar');
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
            out.push('<polygon points="' + poly(pts) + '" fill="none" stroke="#DCCBB4" stroke-width="1.5"/>');
        });
        // 轴线
        for (var i = 0; i < n; i++) {
            var p = pt(i, R);
            out.push('<line x1="' + cx + '" y1="' + cy + '" x2="' + p[0].toFixed(1) + '" y2="' + p[1].toFixed(1) + '" stroke="#DCCBB4" stroke-width="1.5"/>');
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
                '" font-size="15" font-weight="700" fill="#2B4C6F" font-family="PingFang SC, Microsoft YaHei, sans-serif">' +
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
                colorDark: '#2B4C6F', colorLight: '#ffffff',
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
        var body = '800 30px "PingFang SC", "Microsoft YaHei", sans-serif';
        var display = function (px) { return px + 'px "ZCOOL KuaiLe", "PingFang SC", "Microsoft YaHei", sans-serif'; };

        // 纸底 + 点阵
        g.fillStyle = '#FFF8F0'; g.fillRect(0, 0, W, H);
        g.fillStyle = 'rgba(43,76,111,.08)';
        for (var y = 14; y < H; y += 26) {
            for (var x = 14; x < W; x += 26) { g.beginPath(); g.arc(x, y, 1.7, 0, Math.PI * 2); g.fill(); }
        }
        // 贴纸卡（硬阴影）
        roundRect(g, 48, 54, 664, 1240, 40); g.fillStyle = '#2B4C6F'; g.fill();
        roundRect(g, 40, 44, 664, 1240, 40); g.fillStyle = '#FFFFFF'; g.fill();
        g.lineWidth = 5; g.strokeStyle = '#2B4C6F'; g.stroke();

        var cx = 40 + 664 / 2; // 372
        var padL = 88, maxW = 568;

        // 顶部站点名
        g.textAlign = 'center'; g.textBaseline = 'alphabetic';
        g.font = '700 28px "PingFang SC", "Microsoft YaHei", sans-serif';
        g.fillStyle = '#5E7C9B';
        g.fillText(((window.TTS && window.TTS.siteName) || '') + ' 趣味教学人格测评', cx, 128);

        // 头像贴纸
        g.beginPath(); g.arc(cx, 262, 88, 0, Math.PI * 2);
        g.fillStyle = top.color; g.fill();
        g.lineWidth = 6; g.strokeStyle = '#2B4C6F'; g.stroke();
        g.font = '88px "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif';
        g.fillText(top.emoji, cx, 294);

        // 标题
        g.font = '700 32px "PingFang SC", "Microsoft YaHei", sans-serif';
        g.fillStyle = '#5E7C9B';
        g.fillText('我的教学灵魂是', cx, 412);

        g.font = display(104);
        g.fillStyle = '#2B4C6F';
        var nameTxt = top.name;
        if (g.measureText(nameTxt).width > maxW) g.font = display(Math.floor(104 * maxW / g.measureText(nameTxt).width));
        g.fillText(nameTxt, cx, 512);

        g.font = '700 30px "PingFang SC", "Microsoft YaHei", sans-serif';
        g.fillStyle = top.color;
        g.fillText(top.epithet, cx, 562);

        // 匹配度徽章
        var badgeTxt = '匹配度 ' + res.sims[0].pct + '%';
        g.font = '800 34px "PingFang SC", "Microsoft YaHei", sans-serif';
        var bw = g.measureText(badgeTxt).width + 56, bh = 66;
        var bx = cx - bw / 2, by = 596;
        roundRect(g, bx + 5, by + 6, bw, bh, 33); g.fillStyle = 'rgba(43,76,111,.85)'; g.fill();
        roundRect(g, bx, by, bw, bh, 33);
        g.fillStyle = '#FFC93C'; g.fill();
        g.lineWidth = 4; g.strokeStyle = '#2B4C6F'; g.stroke();
        g.fillStyle = '#2B4C6F';
        g.fillText(badgeTxt, cx, by + 45);

        // 3 个人设标签：居中一行，放不下时自动缩字号
        var gap = 16, tags = top.tags, tfont = 30, tw, total;
        function measureTags(fs) {
            g.font = '800 ' + fs + 'px "PingFang SC", "Microsoft YaHei", sans-serif';
            var w = tags.map(function (t) { return g.measureText(t).width + 40; });
            return { w: w, sum: w.reduce(function (a, b) { return a + b; }, 0) + gap * (tags.length - 1) };
        }
        var m = measureTags(tfont);
        while (m.sum > maxW && tfont > 20) { tfont -= 2; m = measureTags(tfont); }
        tw = m.w; total = m.sum;
        var ty = 700, rx = cx - total / 2;
        tags.forEach(function (t, i) {
            roundRect(g, rx, ty, tw[i], 56, 28);
            g.fillStyle = top.color; g.fill();
            g.lineWidth = 3.5; g.strokeStyle = '#2B4C6F'; g.stroke();
            g.fillStyle = top.fg;
            g.fillText(t, rx + tw[i] / 2, ty + 38);
            rx += tw[i] + gap;
        });

        // 名言框
        var qTop = 780;
        g.save();
        g.setLineDash([12, 9]);
        roundRect(g, padL, qTop, maxW, 170, 24);
        g.fillStyle = '#FFFDF8'; g.fill();
        g.lineWidth = 4; g.strokeStyle = 'rgba(43,76,111,.6)'; g.stroke();
        g.restore();
        g.font = display(38);
        g.fillStyle = '#2B4C6F';
        var qlines = wrapLines(g, '「' + top.quote + '」', maxW - 64).slice(0, 3);
        var qy = qTop + 58;
        qlines.forEach(function (ln) { g.fillText(ln, cx, qy); qy += 50; });
        g.font = '700 26px "PingFang SC", "Microsoft YaHei", sans-serif';
        g.fillStyle = '#5E7C9B';
        g.fillText('—— ' + top.name, cx, qTop + 148);

        // 钩子文案
        g.font = '800 32px "PingFang SC", "Microsoft YaHei", sans-serif';
        g.fillStyle = '#B83E1B';
        var hook = wrapLines(g, '我的教学灵魂是' + top.name + '，测测你是哪位教育家？', maxW).slice(0, 2);
        var hy = 992;
        hook.forEach(function (ln) { g.fillText(ln, cx, hy); hy += 44; });

        // 底部二维码
        var qr = makeQrCanvas(url, 150);
        var qx = padL, qy2 = 1064;
        if (qr) {
            g.imageSmoothingEnabled = false;
            g.drawImage(qr, qx, qy2, 150, 150);
            g.imageSmoothingEnabled = true;
            g.textAlign = 'left';
            g.font = '800 30px "PingFang SC", "Microsoft YaHei", sans-serif';
            g.fillStyle = '#2B4C6F';
            g.fillText('扫码测一测', qx + 176, qy2 + 62);
            g.font = '700 24px "PingFang SC", "Microsoft YaHei", sans-serif';
            g.fillStyle = '#5E7C9B';
            g.fillText('你是哪位教育家？', qx + 176, qy2 + 104);
        }
        g.textAlign = 'center';
        g.font = '400 22px "PingFang SC", "Microsoft YaHei", sans-serif';
        g.fillStyle = '#9BAABD';
        g.fillText('仅供娱乐参考 · 不采集任何个人信息', cx, 1252);

        return c.toDataURL('image/png');
    }

    var sharePrevFocus = null;

    function openShare() {
        var modal = $('ts-share');
        var img = $('ts-share-img');
        var hint = $('ts-share-hint');
        sharePrevFocus = document.activeElement;
        modal.classList.add('is-on');
        $('ts-share-close').focus();
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
        $('ts-share').classList.remove('is-on');
        if (sharePrevFocus && sharePrevFocus.focus) sharePrevFocus.focus();
        sharePrevFocus = null;
    }

    /* ---------------- 绑定 ---------------- */
    $('ts-start').addEventListener('click', startOver);
    $('ts-back').addEventListener('click', back);
    $('ts-again-btn').addEventListener('click', startOver);
    $('ts-share-btn').addEventListener('click', openShare);
    $('ts-share-close').addEventListener('click', closeShare);
    $('ts-share-ok').addEventListener('click', closeShare);
    $('ts-share').addEventListener('click', function (e) { if (e.target === this) closeShare(); });
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
