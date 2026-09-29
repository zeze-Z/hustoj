<?php $show_title="$MSG_COURSE_LIST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<style>
/* ============================================================
   课件云铺 · 仅作用于 course.php 内容区
   延续顶栏手绘天空皮肤（holiday-header.css），把天空带进内容区
   移动端改用「便签行」密度模型 + 云目录带，解决一屏只见 1-2 个课件
   ============================================================ */
.cw-page {
  --cw-sky: #5B9CD8;
  --cw-sky-deep: #2F6FA8;
  --cw-ink: #23415E;
  --cw-ink-soft: #5C7693;
  --cw-sun: #FFC94D;
  --cw-sun-deep: #B98200;
  --cw-coral: #FF8A6B;
  --cw-coral-deep: #C4472A;
  --cw-mint: #3FBFA8;
  --cw-mint-deep: #1F7A6B;
  --cw-paper: #EAF3FB;
  --cw-cloud: #FFFFFF;
  --cw-line: #D2E3F2;
  --cw-round: "Yuanti SC", "YouYuan", ui-rounded, "Varela Round", "PingFang SC", "Microsoft YaHei", sans-serif;
  --cw-body-font: Lato, -apple-system, "PingFang SC", "Source Han Sans SC", "Noto Sans CJK SC", "Microsoft Yahei", "Hiragino Sans GB", sans-serif;
  --cw-shadow: 0 2px 0 rgba(43, 90, 138, .10), 0 12px 24px -16px rgba(43, 90, 138, .45);
  --cw-shadow-up: 0 3px 0 rgba(43, 90, 138, .12), 0 20px 32px -20px rgba(43, 90, 138, .55);

  margin: 12px 0 26px;
  padding: clamp(14px, 2.4vw, 26px);
  border: 1px solid var(--cw-line);
  border-radius: clamp(16px, 2.6vw, 26px);
  background:
    radial-gradient(130% 58% at 50% -12%, #FFFFFF 0%, rgba(255, 255, 255, 0) 64%),
    var(--cw-paper);
  color: var(--cw-ink);
  font-family: var(--cw-body-font);
}
.cw-page a { color: var(--cw-sky-deep); }
.cw-page :focus-visible {
  outline: 3px solid var(--cw-sun);
  outline-offset: 3px;
  border-radius: 6px;
}

/* ---------- 页头 ---------- */
.cw-head { margin-bottom: 14px; }
.cw-title {
  margin: 0 0 4px;
  font-family: var(--cw-round);
  font-size: clamp(1.5rem, 5vw, 2.25rem);
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: .01em;
  color: var(--cw-ink);
}
.cw-title .cw-title-mark {
  display: inline-block;
  margin-right: 6px;
  transform: rotate(-6deg);
  color: var(--cw-sky);
}
.cw-sub-note {
  margin: 0 0 14px;
  font-size: .875rem;
  color: var(--cw-ink-soft);
}

/* 搜索 + 订单 */
.cw-tools {
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  gap: 10px;
}
.cw-search {
  display: flex;
  flex: 1 1 260px;
  min-width: 0;
  gap: 8px;
}
.cw-input {
  flex: 1 1 auto;
  min-width: 0;
  height: 42px;
  padding: 0 14px;
  font-family: inherit;
  font-size: .9375rem;
  color: var(--cw-ink);
  background: var(--cw-cloud);
  border: 1.5px solid var(--cw-line);
  border-radius: 14px;
  transition: border-color .16s ease, box-shadow .16s ease;
}
.cw-input::placeholder { color: #93AEC6; }
.cw-input:focus {
  outline: none;
  border-color: var(--cw-sky);
  box-shadow: 0 0 0 3px rgba(91, 156, 216, .22);
}
.cw-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  height: 42px;
  padding: 0 16px;
  font-family: var(--cw-round);
  font-size: .875rem;
  font-weight: 700;
  line-height: 1;
  text-decoration: none;
  white-space: nowrap;
  cursor: pointer;
  border: 1.5px solid transparent;
  border-radius: 14px;
  transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
}
.cw-btn:active { transform: translateY(2px); box-shadow: none !important; }
.cw-btn--sky {
  background: var(--cw-sky);
  color: #fff;
  box-shadow: 0 3px 0 var(--cw-sky-deep);
}
.cw-btn--sky:hover { background: var(--cw-sky-deep); color: #fff; }
.cw-btn--sun {
  background: var(--cw-sun);
  color: var(--cw-sun-deep);
  box-shadow: 0 3px 0 var(--cw-sun-deep);
}
.cw-btn--sun:hover { color: var(--cw-sun-deep); filter: brightness(1.04); }
.cw-btn--mint {
  background: var(--cw-mint);
  color: #fff;
  box-shadow: 0 3px 0 var(--cw-mint-deep);
}
.cw-btn--mint:hover { background: var(--cw-mint-deep); color: #fff; }
.cw-btn--ghost {
  background: var(--cw-cloud);
  color: var(--cw-ink-soft);
  border-color: var(--cw-line);
  box-shadow: 0 3px 0 var(--cw-line);
}
.cw-btn--ghost:hover { color: var(--cw-sky-deep); border-color: var(--cw-sky); }
.cw-btn--sm { height: 34px; padding: 0 13px; font-size: .8125rem; border-radius: 11px; }

/* ---------- 云目录带（导航 + 总览） ---------- */
.cw-index {
  position: sticky;
  top: 0;
  z-index: 30;
  margin-bottom: 14px;
  padding: 10px 12px 12px;
  background: rgba(255, 255, 255, .94);
  border: 1.5px solid var(--cw-line);
  border-radius: 16px;
  box-shadow: 0 8px 20px -16px rgba(43, 90, 138, .55);
}
@supports ((backdrop-filter: blur(10px)) or (-webkit-backdrop-filter: blur(10px))) {
  .cw-index {
    background: rgba(255, 255, 255, .82);
    -webkit-backdrop-filter: blur(12px) saturate(1.2);
    backdrop-filter: blur(12px) saturate(1.2);
  }
}
/* 目录带底缘云弧：与封面云弧呼应，是本页唯一的大胆造型 */
.cw-index::after {
  content: '';
  position: absolute;
  left: -6px;
  right: -6px;
  bottom: -1px;
  height: 12px;
  background-image: radial-gradient(circle 8px at 8px 12px, var(--cw-paper) 7.5px, rgba(255, 255, 255, 0) 8px);
  background-size: 16px 12px;
  background-repeat: repeat-x;
  pointer-events: none;
}
.cw-index-in { position: relative; z-index: 1; }
.cw-subjects {
  display: flex;
  gap: 7px;
  overflow-x: auto;
  overscroll-behavior-x: contain;
  scrollbar-width: none;
  padding: 1px 1px 3px;
}
.cw-subjects::-webkit-scrollbar { display: none; }
.cw-chip {
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 7px 14px;
  font-family: var(--cw-round);
  font-size: .8125rem;
  font-weight: 700;
  line-height: 1.2;
  white-space: nowrap;
  text-decoration: none;
  color: var(--cw-ink-soft);
  background: var(--cw-cloud);
  border: 1.5px solid var(--cw-line);
  border-radius: 999px;
  cursor: pointer;
  transition: transform .16s ease, background .16s ease, color .16s ease, border-color .16s ease, box-shadow .16s ease;
}
.cw-chip:hover {
  transform: translateY(-2px);
  color: var(--cw-sky-deep);
  border-color: var(--cw-sky);
  box-shadow: 0 3px 0 rgba(91, 156, 216, .3);
}
.cw-chip.is-on {
  color: #fff;
  background: hsl(var(--cw-hue, 208) 64% 50%);
  border-color: transparent;
  box-shadow: 0 3px 0 hsl(var(--cw-hue, 208) 64% 38%);
}
.cw-chip.is-on:hover { transform: translateY(-2px); color: #fff; }

/* 总览条：一眼看清总量、当前筛选、当前页码 */
.cw-glance {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 10px;
  margin-top: 9px;
  padding-top: 9px;
  border-top: 1.5px dashed var(--cw-line);
  font-family: var(--cw-round);
  font-size: .75rem;
  color: var(--cw-ink-soft);
}
.cw-glance b {
  font-family: var(--cw-round);
  font-size: .9375rem;
  font-weight: 800;
  color: var(--cw-ink);
}
.cw-glance .cw-dot { color: var(--cw-line); }
.cw-glance .cw-filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 999px;
  background: rgba(255, 201, 77, .3);
  color: var(--cw-sun-deep);
  font-weight: 700;
}
.cw-glance .cw-spacer { flex: 1 1 auto; }

/* ---------- 标签筛选贴纸 ---------- */
.cw-tagbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
  padding: 10px 13px;
  background: var(--cw-cloud);
  border: 1.5px dashed var(--cw-sun);
  border-radius: 14px;
  font-family: var(--cw-round);
  font-size: .8125rem;
  color: var(--cw-sun-deep);
}
.cw-tagbar strong {
  padding: 2px 9px;
  border-radius: 8px;
  background: var(--cw-sun);
  color: var(--cw-sun-deep);
  font-weight: 800;
  transform: rotate(-2deg);
}
.cw-tagbar a { color: var(--cw-sky-deep); font-weight: 700; }

/* ---------- 卡片网格：桌面竖卡 / 移动便签行 ---------- */
.cw-grid {
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  margin-bottom: 18px;
}
@media (max-width: 1199px) { .cw-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
@media (max-width: 979px)  { .cw-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 767px)  { .cw-grid { grid-template-columns: 1fr; gap: 10px; } }

.cw-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  grid-template-areas: 'cover' 'body' 'act';
  position: relative;
  height: 100%;
  background: var(--cw-cloud);
  border: 1.5px solid var(--cw-line);
  border-radius: 16px;
  box-shadow: var(--cw-shadow);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.cw-card:hover,
.cw-card:focus-within {
  transform: translateY(-3px);
  border-color: hsl(var(--cw-hue, 208) 55% 72%);
  box-shadow: var(--cw-shadow-up);
}
.cw-card:focus-within { outline: 3px solid var(--cw-sun); outline-offset: 3px; }

/* 封面：无封面时用学科色相渐变占位，扫一眼即可分区 */
.cw-cover {
  grid-area: cover;
  position: relative;
  height: 132px;
  overflow: hidden;
  border-radius: 14px 14px 0 0;
  background: linear-gradient(142deg,
      hsl(var(--cw-hue, 208) 68% 66%),
      hsl(var(--cw-hue, 208) 56% 44%));
  display: flex;
  align-items: center;
  justify-content: center;
}
.cw-cover .cw-cover-icon {
  font-size: 2.6em;
  color: rgba(255, 255, 255, .82);
  text-shadow: 0 2px 0 rgba(0, 0, 0, .12);
}
.cw-cover img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
/* 云朵扇贝缘：封面坐在一朵云上（画在封面内，不依赖 overflow） */
.cw-cover::after {
  content: '';
  position: absolute;
  left: -8px;
  right: -8px;
  bottom: -9px;
  height: 20px;
  background-image: radial-gradient(circle 10px at 10px 19px, var(--cw-cloud) 9.5px, rgba(255, 255, 255, 0) 10px);
  background-size: 20px 20px;
  background-repeat: repeat-x;
  pointer-events: none;
  z-index: 2;
}

/* 手贴贴纸：系列 / 上新 */
.cw-sticker {
  position: absolute;
  top: 9px;
  left: 9px;
  z-index: 3;
  padding: 3px 9px;
  font-family: var(--cw-round);
  font-size: .6875rem;
  font-weight: 800;
  line-height: 1.35;
  letter-spacing: .02em;
  border-radius: 9px 11px 11px 6px;
  transform: rotate(-3deg);
}
.cw-sticker--series {
  background: var(--cw-sun);
  color: var(--cw-sun-deep);
  box-shadow: 0 2px 0 rgba(185, 130, 0, .4);
}
.cw-sticker--new {
  left: auto;
  right: 9px;
  background: var(--cw-coral);
  color: #fff;
  box-shadow: 0 2px 0 rgba(196, 71, 42, .4);
  transform: rotate(3deg);
}

/* 内容区 */
.cw-body {
  grid-area: body;
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 12px 14px 4px;
  min-width: 0;
}
.cw-sub { line-height: 1; }
.cw-tag {
  display: inline-block;
  padding: 3px 9px;
  font-family: var(--cw-round);
  font-size: .6875rem;
  font-weight: 700;
  line-height: 1.4;
  border-radius: 999px;
  color: hsl(var(--cw-hue, 208) 62% 32%);
  background: hsl(var(--cw-hue, 208) 72% 92%);
  text-decoration: none;
}
a.cw-tag:hover { color: hsl(var(--cw-hue, 208) 62% 26%); background: hsl(var(--cw-hue, 208) 72% 86%); }
.cw-name {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
  font-family: var(--cw-round);
  font-size: 1.0625rem;
  font-weight: 700;
  line-height: 1.35;
  color: var(--cw-ink);
  text-decoration: none;
  word-break: break-word;
}
.cw-name:hover { color: var(--cw-sky-deep); text-decoration: underline; text-underline-offset: 3px; }
.cw-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}
.cw-tags .cw-mini {
  padding: 2px 8px;
  font-family: var(--cw-round);
  font-size: .6875rem;
  font-weight: 700;
  line-height: 1.5;
  border-radius: 999px;
  color: var(--cw-ink-soft);
  background: var(--cw-paper);
  border: 1px solid var(--cw-line);
  text-decoration: none;
}
.cw-tags .cw-mini:hover { color: var(--cw-sky-deep); border-color: var(--cw-sky); }

/* 元信息：课时 / 价格 / 下载 / 购买态 */
.cw-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px 9px;
  margin-top: auto;
  padding-top: 4px;
  font-family: var(--cw-round);
  font-size: .75rem;
  font-weight: 700;
  color: var(--cw-ink-soft);
}
.cw-meta .cw-sep { color: var(--cw-line); }
.cw-price--free { color: var(--cw-mint-deep); }
.cw-price--paid { color: var(--cw-coral-deep); }
.cw-owned {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: .6875rem;
}
.cw-owned--yes { color: var(--cw-mint-deep); background: rgba(63, 191, 168, .18); }
.cw-owned--no { color: var(--cw-ink-soft); background: var(--cw-paper); }

/* 行动区 */
.cw-act {
  grid-area: act;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  padding: 8px 14px 13px;
  border-top: 1.5px solid var(--cw-line);
}

/* 系列卡片：书堆叠加底座，表示「这一叠是同一个系列」
   两张旋转错位的色卡探出在主卡右下方，读作一叠课 */
.cw-card--series {
  border-color: rgba(185, 130, 0, .5);
  border-width: 2px;
  isolation: isolate;
  /* 右/下留白给叠加卡探出，避免压到相邻卡片 */
  margin: 0 16px 18px 0;
  height: auto;
}
.cw-stack {
  pointer-events: none;
  position: absolute;
  box-sizing: border-box;
  z-index: 0;
  border: 2px solid;
  border-radius: 16px;
  transition: transform .28s ease, filter .28s ease, box-shadow .28s ease;
}
.cw-stack--back {
  inset: 16px -14px -16px 14px;
  background: #FFD98A;
  border-color: #E0A93F;
  transform: rotate(3deg);
  filter: drop-shadow(0 10px 8px rgba(140, 95, 0, .22));
}
.cw-stack--mid {
  inset: 8px -7px -9px 7px;
  background: #FFE9BC;
  border-color: #EDC273;
  transform: rotate(1.6deg);
  filter: drop-shadow(0 7px 7px rgba(140, 95, 0, .18));
}
.cw-stack--front {
  inset: 0;
  background: #FFFAF0;
  border-color: transparent;
  transform: rotate(0);
  box-shadow: 0 5px 14px rgba(140, 95, 0, .16);
}
/* 卡片自身内容浮在叠加卡之上 */
.cw-card--series > *:not(.cw-stack) {
  position: relative;
  z-index: 1;
}
/* 主卡描边压在叠加卡之上：保证「最上面那张卡」轮廓完整，叠加才读得出来
   inset:-2px 让描边盒与主卡 border box 重合，而不是画出双层边 */
.cw-card--series::after {
  content: '';
  position: absolute;
  inset: -2px;
  z-index: 3;
  pointer-events: none;
  box-sizing: border-box;
  border: 2px solid rgba(185, 130, 0, .5);
  border-radius: 16px;
}
/* 悬停时叠加卡再错开一点，回应操作 */
.cw-card--series:hover .cw-stack--back,
.cw-card--series:focus-within .cw-stack--back {
  transform: translate(3px, 3px) rotate(3deg);
  filter: drop-shadow(0 13px 10px rgba(140, 95, 0, .25));
}
.cw-card--series:hover .cw-stack--mid,
.cw-card--series:focus-within .cw-stack--mid {
  transform: translate(2px, 2px) rotate(1.6deg);
  filter: drop-shadow(0 9px 9px rgba(140, 95, 0, .21));
}
.cw-card--series .cw-cover {
  background: linear-gradient(142deg,
      hsl(var(--cw-hue, 208) 70% 70%),
      hsl(var(--cw-hue, 208) 52% 42%));
}
.cw-card--series .cw-name { color: var(--cw-ink); }
.cw-series-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.cw-series-stats .cw-pill {
  padding: 3px 10px;
  font-family: var(--cw-round);
  font-size: .75rem;
  font-weight: 800;
  border-radius: 999px;
  color: var(--cw-sun-deep);
  background: rgba(255, 201, 77, .32);
  border: 1px solid rgba(185, 130, 0, .28);
}

/* ---------- 移动端：便签行（一屏 6-8 条） ---------- */
@media (max-width: 767px) {
  .cw-page {
    margin: 8px 0 18px;
    padding: 12px 10px 18px;
    border-radius: 16px;
  }
  .cw-title { font-size: 1.5rem; }
  .cw-sub-note { display: none; }
  .cw-tools { gap: 8px; }
  .cw-search { flex: 1 1 100%; }
  .cw-btn--order { width: 100%; }

  .cw-index {
    padding: 8px 9px 11px;
    border-radius: 14px;
    top: 0;
  }
  .cw-chip { padding: 6px 12px; font-size: .78125rem; }
  .cw-glance { font-size: .71875rem; gap: 4px 8px; }
  .cw-glance b { font-size: .875rem; }

  .cw-card {
    grid-template-columns: 92px minmax(0, 1fr);
    grid-template-rows: auto auto;
    grid-template-areas:
      'cover body'
      'cover act';
    height: auto;
    border-radius: 14px;
  }
  .cw-cover {
    height: 100%;
    min-height: 92px;
    border-radius: 13px 0 0 13px;
    border-right: 1.5px solid var(--cw-line);
  }
  .cw-card--series .cw-cover { border-right-width: 2px; }
  /* 叠加卡在窄屏收窄探出量，保证不出横向滚动 */
  .cw-card--series {
    margin: 0 11px 13px 0;
    border-radius: 14px;
  }
  .cw-stack { border-radius: 14px; }
  .cw-stack--back { inset: 9px -8px -10px 8px; }
  .cw-stack--mid { inset: 5px -4px -5px 4px; }
  .cw-card--series::after { border-radius: 14px; }
  .cw-cover .cw-cover-icon { font-size: 1.9em; }
  .cw-cover::after {
    left: auto;
    right: -10px;
    top: -8px;
    bottom: -8px;
    width: 16px;
    height: auto;
    background-image: radial-gradient(circle 8px at 14px 8px, var(--cw-cloud) 7.5px, rgba(255, 255, 255, 0) 8px);
    background-size: 16px 16px;
    background-repeat: repeat-y;
  }
  .cw-sticker { top: 6px; left: 6px; padding: 2px 6px; font-size: .625rem; }
  .cw-sticker--new { left: auto; right: 6px; }
  .cw-body { gap: 4px; padding: 8px 10px 2px 11px; }
  .cw-name { font-size: .9375rem; line-height: 1.32; }
  .cw-tags { display: none; }
  .cw-meta {
    gap: 3px 7px;
    padding-top: 2px;
    font-size: .6875rem;
  }
  .cw-owned--no { display: none; }
  .cw-act {
    padding: 2px 10px 9px 11px;
    border-top: 0;
    justify-content: space-between;
  }
  .cw-act .cw-btn { height: 32px; padding: 0 12px; font-size: .75rem; border-radius: 10px; }
}

/* ---------- 空态：给方向，不只给氛围 ---------- */
.cw-empty {
  margin-bottom: 18px;
  padding: 38px 18px;
  text-align: center;
  background: var(--cw-cloud);
  border: 1.5px dashed var(--cw-line);
  border-radius: 18px;
}
.cw-empty .cw-empty-art {
  font-size: 2.6rem;
  line-height: 1;
  display: block;
  margin-bottom: 10px;
}
.cw-empty p {
  margin: 0 0 14px;
  font-family: var(--cw-round);
  font-size: 1.0625rem;
  font-weight: 700;
  color: var(--cw-ink);
}

/* ---------- 分页 ---------- */
.cw-pager-wrap { margin: 6px 0 20px; text-align: center; }
.cw-pager {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 6px;
}
.cw-pager a,
.cw-pager span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 36px;
  height: 36px;
  padding: 0 10px;
  font-family: var(--cw-round);
  font-size: .8125rem;
  font-weight: 700;
  text-decoration: none;
  color: var(--cw-ink-soft);
  background: var(--cw-cloud);
  border: 1.5px solid var(--cw-line);
  border-radius: 11px;
  transition: transform .16s ease, box-shadow .16s ease;
}
.cw-pager a:hover {
  transform: translateY(-2px);
  color: var(--cw-sky-deep);
  border-color: var(--cw-sky);
  box-shadow: 0 3px 0 rgba(91, 156, 216, .28);
}
.cw-pager .is-on {
  color: #fff;
  background: var(--cw-sky);
  border-color: transparent;
  box-shadow: 0 3px 0 var(--cw-sky-deep);
  pointer-events: none;
}
.cw-pager .is-off {
  opacity: .42;
  pointer-events: none;
  color: var(--cw-ink-soft);
  background: transparent;
}
.cw-pager-count {
  margin-top: 9px;
  font-family: var(--cw-round);
  font-size: .75rem;
  color: var(--cw-ink-soft);
}

/* ---------- 创作者入驻 ---------- */
.cw-invite {
  padding: 16px 18px;
  background: var(--cw-cloud);
  border: 1.5px solid var(--cw-line);
  border-radius: 18px;
  text-align: center;
  box-shadow: var(--cw-shadow);
}
.cw-invite-title {
  margin: 0 0 5px;
  font-family: var(--cw-round);
  font-size: 1.0625rem;
  font-weight: 800;
  color: var(--cw-ink);
}
.cw-invite-sub {
  margin: 0 0 8px;
  font-size: .875rem;
  color: var(--cw-ink-soft);
}
.cw-invite-qq {
  font-family: var(--cw-round);
  font-size: 1rem;
  color: var(--cw-ink);
}
.cw-invite-qq .cw-qq-num {
  color: var(--cw-sky-deep);
  cursor: pointer;
  font-weight: 800;
  text-decoration: underline dotted var(--cw-sky);
  text-underline-offset: 3px;
  /* 允许长按手动选择复制（防止继承 user-select:none 被阻断） */
  -webkit-user-select: text;
  user-select: text;
}

/* ---------- 返回顶部 ---------- */
.cw-top {
  position: fixed;
  right: 14px;
  bottom: 18px;
  z-index: 60;
  width: 48px;
  height: 48px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-family: var(--cw-round);
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--cw-sky-deep);
  background: var(--cw-cloud);
  border: 1.5px solid var(--cw-line);
  border-radius: 16px;
  box-shadow: var(--cw-shadow);
  cursor: pointer;
  opacity: 0;
  visibility: hidden;
  transform: translateY(8px);
  transition: opacity .2s ease, transform .2s ease, visibility .2s;
}
.cw-top.is-show { opacity: 1; visibility: visible; transform: none; }
.cw-top:hover { border-color: var(--cw-sky); transform: translateY(-2px); }

/* ---------- 载入落位：整页只做这一次编排，不做每卡入场 ---------- */
@media (prefers-reduced-motion: no-preference) {
  .cw-head { animation: cw-settle .46s cubic-bezier(.2, .8, .25, 1) both; }
  .cw-index-in { animation: cw-settle .46s cubic-bezier(.2, .8, .25, 1) .07s both; }
}
@keyframes cw-settle {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: none; }
}
@media (prefers-reduced-motion: reduce) {
  .cw-page *,
  .cw-page *::before,
  .cw-page *::after {
    animation: none !important;
    transition: none !important;
    scroll-behavior: auto !important;
  }
  .cw-card:hover,
  .cw-card:focus-within,
  .cw-chip:hover,
  .cw-pager a:hover,
  .cw-top:hover,
  .cw-btn:active { transform: none; }
  /* 叠加卡保留基础旋转角度，只取消悬停位移 */
  .cw-card--series:hover .cw-stack--back,
  .cw-card--series:focus-within .cw-stack--back { transform: rotate(3deg); filter: drop-shadow(0 10px 8px rgba(140, 95, 0, .22)); }
  .cw-card--series:hover .cw-stack--mid,
  .cw-card--series:focus-within .cw-stack--mid { transform: rotate(1.6deg); filter: drop-shadow(0 7px 7px rgba(140, 95, 0, .18)); }
}
</style>

<div class="padding cw-page">

  <!-- 页头：标题 + 搜索 -->
  <header class="cw-head">
    <h1 class="cw-title"><span class="cw-title-mark" aria-hidden="true">✦</span><?php echo $MSG_COURSE_LIST; ?></h1>
    <p class="cw-sub-note">在云端挑一份趁手的课件，搜名字、标签都能找到。</p>
    <div class="cw-tools">
      <form action="course.php" method="get" class="cw-search">
        <?php if ($view_current_subject > 0): ?>
        <input type="hidden" name="subject" value="<?php echo $view_current_subject; ?>">
        <?php endif; ?>
        <input class="cw-input" type="text" name="search"
               placeholder="搜索课程名称、标签..."
               value="<?php echo htmlspecialchars($view_search_keyword ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <button class="cw-btn cw-btn--sky" type="submit">搜索</button>
      </form>
      <?php if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
      <a href="course_my.php" class="cw-btn cw-btn--sun cw-btn--order">我的订单</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- 云目录带：学科胶囊 + 总览条（移动端 sticky，随时可见） -->
  <div class="cw-index">
    <div class="cw-index-in">
      <nav class="cw-subjects" aria-label="<?php echo $MSG_COURSE_SUBJECT; ?>">
        <a class="cw-chip <?php echo $view_current_subject == 0 ? 'is-on' : ''; ?>"
           style="--cw-hue: 208"
           href="course.php"><?php echo $MSG_ALL; ?></a>
        <?php foreach ($view_subjects as $subject):
          $cw_hue = (intval($subject['id']) * 47) % 360;
        ?>
          <a class="cw-chip <?php echo $view_current_subject == $subject['id'] ? 'is-on' : ''; ?>"
             style="--cw-hue: <?php echo $cw_hue; ?>"
             href="course.php?subject=<?php echo $subject['id']; ?>">
            <?php echo htmlspecialchars($subject['name'], ENT_QUOTES, 'UTF-8'); ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <div class="cw-glance">
        <span>共 <b><?php echo $view_total_courses; ?></b> <?php echo $view_aggregate ? '个课程卡片' : '个课件'; ?></span>
        <span class="cw-dot" aria-hidden="true">•</span>
        <span>第 <b><?php echo $view_page; ?></b> / <?php echo $view_total_pages; ?> 页</span>
        <?php if ($view_current_subject > 0): ?>
        <span class="cw-dot" aria-hidden="true">•</span>
        <span class="cw-filter-tag">学科筛选中</span>
        <?php endif; ?>
        <?php if (!empty($view_current_tag)): ?>
        <span class="cw-filter-tag"><?php echo htmlspecialchars($view_current_tag, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
        <?php if (!empty($view_search_keyword)): ?>
        <span class="cw-filter-tag">“<?php echo htmlspecialchars($view_search_keyword, ENT_QUOTES, 'UTF-8'); ?>”</span>
        <?php endif; ?>
        <span class="cw-spacer"></span>
      </div>
    </div>
  </div>

  <!-- 当前标签筛选显示 -->
  <?php if (!empty($view_current_tag)): ?>
  <div class="cw-tagbar">
    <strong>标签筛选</strong>
    <span><?php echo htmlspecialchars($view_current_tag, ENT_QUOTES, 'UTF-8'); ?></span>
    <a href="course.php<?php echo $view_current_subject > 0 ? '?subject=' . $view_current_subject : ''; ?>">
      清除筛选
    </a>
  </div>
  <?php endif; ?>

  <!-- 课程卡片列表 -->
  <?php if (empty($view_courses)): ?>
    <div class="cw-empty">
      <span class="cw-empty-art" aria-hidden="true">☁️</span>
      <p>海量优质课件正在赶来</p>
      <a class="cw-btn cw-btn--sky" href="course.php"><?php echo $MSG_ALL; ?><?php echo $MSG_COURSE_LIST; ?></a>
    </div>
  <?php else: ?>
    <div class="cw-grid">
      <?php foreach ($view_courses as $course):
        $cw_hue = (intval($course['subject_id']) * 47) % 360;
        if (!empty($course['is_series'])):
          $series_query = array('tag' => '系列课程:' . $course['series_name']);
          if ($view_current_subject > 0) $series_query['subject'] = $view_current_subject;
          $series_link = 'course.php?' . http_build_query($series_query);
      ?>
        <article class="cw-card cw-card--series" style="--cw-hue: <?php echo $cw_hue; ?>"
                 tabindex="0" role="link"
                 onclick="if (event.target.tagName !== 'A') window.location.href='<?php echo htmlspecialchars($series_link, ENT_QUOTES, 'UTF-8'); ?>';"
                 onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='<?php echo htmlspecialchars($series_link, ENT_QUOTES, 'UTF-8'); ?>'; }">
          <span class="cw-stack cw-stack--back" aria-hidden="true"></span>
          <span class="cw-stack cw-stack--mid" aria-hidden="true"></span>
          <span class="cw-stack cw-stack--front" aria-hidden="true"></span>
          <div class="cw-cover">
            <span class="cw-sticker cw-sticker--series">系列课程</span>
            <i class="book icon cw-cover-icon" aria-hidden="true"></i>
            <?php if (!empty($course['cover_url'])): ?>
            <img src="<?php echo htmlspecialchars($course['cover_url'], ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($course['series_name'], ENT_QUOTES, 'UTF-8'); ?>"
                 loading="lazy" decoding="async"
                 onerror="this.style.display='none'">
            <?php endif; ?>
          </div>

          <div class="cw-body">
            <div class="cw-sub">
              <span class="cw-tag"><?php echo htmlspecialchars($course['subject_name'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <a href="<?php echo htmlspecialchars($series_link, ENT_QUOTES, 'UTF-8'); ?>"
               class="cw-name"><?php echo htmlspecialchars($course['series_name'], ENT_QUOTES, 'UTF-8'); ?></a>

            <?php
              $series_first_tags = array();
              if (!empty($course['tags'])) {
                  foreach (explode(',', $course['tags']) as $series_tag) {
                      $series_tag = trim($series_tag);
                      if ($series_tag === '' || strpos($series_tag, '系列课程:') === 0) continue;
                      $series_first_tags[] = $series_tag;
                  }
              }
            ?>
            <?php if (!empty($series_first_tags)): ?>
            <div class="cw-tags" aria-label="系列第一课标签">
              <?php foreach (array_slice($series_first_tags, 0, 4) as $series_tag): ?>
              <span class="cw-mini"><?php echo htmlspecialchars($series_tag, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="cw-series-stats">
              <span class="cw-pill"><i class="book icon" aria-hidden="true"></i><?php echo intval($course['series_course_count']); ?> 门课程</span>
              <span class="cw-pill"><i class="clock icon" aria-hidden="true"></i><?php echo intval($course['series_lesson_count']); ?> 课时</span>
            </div>
          </div>

          <div class="cw-act">
            <a href="<?php echo htmlspecialchars($series_link, ENT_QUOTES, 'UTF-8'); ?>" class="cw-btn cw-btn--sky cw-btn--sm">
              查看系列课程
            </a>
          </div>
        </article>
      <?php else:
        $is_purchased = isset($view_purchased[$course['id']]);
        $preview_price = floatval($course['preview_price']);
        $source_price = floatval($course['source_price']);
        $min_price = min($preview_price, $source_price);
        $is_free = $preview_price == 0 && $source_price == 0;
        // 是否存在完整预览版（有完整预览链接才算有预览版，与详情页 view_has_full_preview 口径一致）
        $has_preview = !empty($course['courseware_full_preview_url']) || !empty($course['lesson_plan_full_preview_url']);
      ?>
        <article class="cw-card" style="--cw-hue: <?php echo $cw_hue; ?>">
          <div class="cw-cover">
            <?php if (!empty($course['is_new'])): ?>
            <span class="cw-sticker cw-sticker--new">上新</span>
            <?php endif; ?>
            <i class="book icon cw-cover-icon" aria-hidden="true"></i>
            <?php if (!empty($course['cover_url'])): ?>
            <img src="<?php echo htmlspecialchars($course['cover_url'], ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>"
                 loading="lazy" decoding="async"
                 onerror="this.style.display='none'">
            <?php endif; ?>
          </div>

          <div class="cw-body">
            <div class="cw-sub">
              <a class="cw-tag"
                 href="course.php<?php echo $view_current_subject > 0 ? '?subject=' . $view_current_subject : ''; ?>">
                <?php echo htmlspecialchars($course['subject_name'], ENT_QUOTES, 'UTF-8'); ?>
              </a>
            </div>

            <a href="course_info.php?id=<?php echo $course['id']; ?>"
               class="cw-name"><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></a>

            <?php if (!empty($course['tags'])):
              $tags = explode(',', $course['tags']);
            ?>
              <div class="cw-tags">
                <?php foreach ($tags as $tag):
                  $tag = trim($tag);
                  if (empty($tag) || strpos($tag, '系列课程:') === 0) continue;
                ?>
                  <a href="course.php<?php echo $view_current_subject > 0 ? '?subject=' . $view_current_subject . '&' : '?'; ?>tag=<?php echo urlencode($tag); ?>"
                     class="cw-mini">
                    <?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="cw-meta">
              <span><i class="clock icon" aria-hidden="true"></i><?php echo $MSG_LESSON_COUNT; ?>: <?php echo intval($course['lesson_count']); ?></span>
              <span class="cw-sep" aria-hidden="true">·</span>
              <?php if ($is_free): ?>
                <span class="cw-price--free"><?php echo $MSG_FREE; ?></span>
              <?php elseif ($has_preview && $preview_price == 0 && $source_price > 0): ?>
                <span class="cw-price--free">预览免费</span>
              <?php elseif ($has_preview && $preview_price > 0 && $source_price > 0): ?>
                <span class="cw-price--paid"><?php echo intval($min_price); ?> 积分起</span>
              <?php else: ?>
                <span class="cw-price--paid"><?php echo intval(max($preview_price, $source_price)); ?> 积分</span>
              <?php endif; ?>
              <span class="cw-sep" aria-hidden="true">·</span>
              <span><i class="download icon" aria-hidden="true"></i>已下载 <?php echo intval($course['download_count']); ?> 次</span>
              <?php if ($is_purchased): ?>
                <span class="cw-owned cw-owned--yes"><i class="checkmark icon" aria-hidden="true"></i><?php echo $MSG_ACQUIRED; ?></span>
              <?php else: ?>
                <span class="cw-owned cw-owned--no"><?php echo $MSG_NOT_ACQUIRED; ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="cw-act">
            <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
              <a href="course_info.php?id=<?php echo $course['id']; ?>" class="cw-btn cw-btn--sky cw-btn--sm">查看详情</a>
            <?php elseif ($is_purchased): ?>
              <a href="course_info.php?id=<?php echo $course['id']; ?>" class="cw-btn cw-btn--mint cw-btn--sm">已拥有</a>
            <?php else: ?>
              <a href="course_info.php?id=<?php echo $course['id']; ?>" class="cw-btn cw-btn--sky cw-btn--sm">去查看</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endif; endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- 分页导航 -->
  <?php if ($view_total_pages > 1): ?>
    <?php
      // 保留当前筛选参数，只替换 page
      $base_params = array();
      if ($view_current_subject > 0) $base_params['subject'] = $view_current_subject;
      if (!empty($view_current_tag)) $base_params['tag'] = $view_current_tag;
      if (!empty($view_search_keyword)) $base_params['search'] = $view_search_keyword;
      $page_link = function($p) use ($base_params) {
        $params = array_merge($base_params, array('page' => $p));
        return 'course.php?' . http_build_query($params);
      };
      // 显示页码范围：当前页前后各 2 页，首尾必现
      $start = max(1, $view_page - 2);
      $end = min($view_total_pages, $view_page + 2);
      if ($start > 1) $start = min($start, $end - 4 >= 1 ? $end - 4 : $start);
      if ($end < $view_total_pages) $end = max($end, $start + 4 <= $view_total_pages ? $start + 4 : $end);
      $start = max(1, $start);
      $end = min($view_total_pages, $end);
    ?>
    <div class="cw-pager-wrap">
      <nav class="cw-pager" aria-label="分页">
        <?php if ($view_page > 1): ?>
          <a href="<?php echo $page_link($view_page - 1); ?>" aria-label="上一页">‹</a>
        <?php else: ?>
          <span class="is-off">‹</span>
        <?php endif; ?>

        <?php if ($start > 1): ?>
          <a href="<?php echo $page_link(1); ?>">1</a>
          <?php if ($start > 2): ?>
            <span class="is-off">…</span>
          <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
          <a class="<?php echo $i == $view_page ? 'is-on' : ''; ?>"
             href="<?php echo $page_link($i); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($end < $view_total_pages): ?>
          <?php if ($end < $view_total_pages - 1): ?>
            <span class="is-off">…</span>
          <?php endif; ?>
          <a href="<?php echo $page_link($view_total_pages); ?>"><?php echo $view_total_pages; ?></a>
        <?php endif; ?>

        <?php if ($view_page < $view_total_pages): ?>
          <a href="<?php echo $page_link($view_page + 1); ?>" aria-label="下一页">›</a>
        <?php else: ?>
          <span class="is-off">›</span>
        <?php endif; ?>
      </nav>
      <div class="cw-pager-count">
        <?php if (!empty($view_aggregate)): ?>
          共 <?php echo $view_total_courses; ?> 个课程卡片（系列已聚合），第 <?php echo $view_page; ?>/<?php echo $view_total_pages; ?> 页
        <?php else: ?>
          共 <?php echo $view_total_courses; ?> 个课件，第 <?php echo $view_page; ?>/<?php echo $view_total_pages; ?> 页
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- 创作者入驻引导 -->
  <div class="cw-invite">
    <p class="cw-invite-title">🎁 有优质课件资源，欢迎联系我们，助您变现！</p>
    <p class="cw-invite-sub">平台优质流量 + 垂直精准客群 = 课件精准投放。</p>
    <div class="cw-invite-qq">
      <i class="qq icon" style="color: #12b7f5;" aria-hidden="true"></i>
      咨询客服QQ：<strong onclick="copyCustomerQQ(this)" title="点击复制QQ号" class="cw-qq-num"><?php echo htmlentities($OJ_CUSTOMER_QQ, ENT_QUOTES, 'UTF-8');?></strong>
    </div>
  </div>

</div>

<button type="button" class="cw-top" aria-label="返回顶部">↑</button>

<script>
(function () {
  var btn = document.querySelector('.cw-top');
  if (!btn) return;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function scrollY() {
    return window.pageYOffset
      || (document.body && document.body.scrollTop)
      || (document.documentElement && document.documentElement.scrollTop)
      || 0;
  }
  function onScroll() {
    btn.classList.toggle('is-show', scrollY() > 320);
  }
  // 本页滚动容器是 body（header.php 固定了 html），用捕获监听兜住所有滚动源
  document.addEventListener('scroll', onScroll, true);

  btn.addEventListener('click', function () {
    var behavior = reduce ? 'auto' : 'smooth';
    if (document.scrollingElement && document.scrollingElement.scrollTo) {
      document.scrollingElement.scrollTo({ top: 0, behavior: behavior });
    }
    if (document.body && document.body.scrollTo) {
      document.body.scrollTo({ top: 0, behavior: behavior });
    }
    window.scrollTo({ top: 0, behavior: behavior });
  });

  onScroll();
})();
</script>

<?php /* 复制客服QQ的公共方法 copyCustomerQQ() 定义在 footer.php，全站复用 */ ?>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
