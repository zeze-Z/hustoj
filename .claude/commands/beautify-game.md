---
description: 按统一规范美化游戏页面 UI（一屏自适应+靠上+玻璃拟态+动效+礼花）
argument-hint: 游戏页面文件名，如 memory_game.php
---

按 HUSTOJ 游戏页面统一规范美化以下页面的 UI：

**目标页面：** {{$1:（未提供页面名，先向用户确认要美化哪个游戏页）}}

## 工作流程

1. **读取规范**：先读 `C:\Users\zhang\.claude\projects\D--source-code-hustoj\memory\game-ui-pattern.md`，这是从 clock_reading / guess_number / memory_game 三个游戏提炼的共性规范，**严格遵守**。
2. **读现有页面**：`trunk/web/template/syzoj/<对应>.html`，理解游戏结构（统计项、难度档位、反馈区、结束弹窗、是否有网格/卡片）。
3. **按规范改造**（逐条对照，不要漏）：
   - 外层 `.xx-wrap`（`align-items: flex-start` 靠上，**不要 center**）+ `.xx-bg-decor` 背景装饰层 + `.game-container` 玻璃拟态容器
   - 容器顶部彩虹条 `::before` + `max-height: 100dvh; overflow-y: auto` 兜底
   - 标题渐变文字：`-webkit-text-fill-color: transparent !important` + `color: transparent !important`（**两个 !important 都要加**，否则 MDC 全局样式覆盖变黑）
   - 统计栏 grid + 顶部彩色边 + emoji 图标 + 渐变数字
   - 难度按钮药丸形，困难档用红/橙对比色区分
   - 主按钮实心渐变 + 次级按钮（重新开始等）描边白底
   - 反馈动效：反馈区弹性弹入 + 彩色背景 + 输入框 flash + 统计数字 bounce + 按钮波纹
   - 结束弹窗：pop-in 动画 + 顶部彩虹条 + 渐变标题；弹窗和礼花**同时出现**
   - 通关礼花：复用 `canvas-confetti` CDN + `template/<?php echo $OJ_TEMPLATE?>/game_confetti.js`；移动端若礼花偏底，写页面内 `launchConfettiFromCard()` 从弹窗中心绽开，**不要改共享的 game_confetti.js**
4. **有网格/卡片的游戏**（配对、拼图等）：用第十一节的"预渲染测量"算法——在 `innerHTML=''` 之后、创建卡片之前，测量非网格子元素高度算出剩余空间，再用 CSS 变量 `--card-size`/`--grid-cols`/`--grid-rows` 控制网格，`.card` 设 `aspect-ratio: auto` 覆盖默认 `1`。**所有难度档位都要跑这套动态计算**，不要只给某个档位做。
5. **移动端**：全部 `clamp()` 自适应，不写固定 px；超小屏/矮屏压缩统计卡。
6. **配色**：与用户确认主色系；原则是"主色系 + 一个暖色对比色"，避免全冷色（全青/全蓝显单调）。参考表见规范第十三节。
7. **自测**：`php -l`（若有 PHP）+ 浏览器开 `?v=N` 防缓存查看效果；移动端用 browser-use 的窄视口截图核对一屏显示。
8. **同步测试环境**：改完按 CLAUDE.md 流程同步到 web-2204 虚机，让用户验证。

## 布局参考：拼豆游戏（puzzle_game.html）

所有游戏页面统一采用**左右分栏布局**，参考 `puzzle_game.html` 的 `.game-layout` 结构：

```
.game-layout          ← flex 容器，横向排列
├── .game-sidebar     ← 左侧工具栏（固定宽度 clamp(240px, 28vw, 300px)）
│   ├── 统计栏（.stats-bar，grid 双列）
│   ├── 难度选择（.difficulty-pills，药丸按钮组）
│   ├── 主按钮（开始/重新开始）
│   └── 底部提示文字
└── .xx-main          ← 右侧游戏区域（flex: 1，自适应填满）
    └── 游戏主内容（画布/网格/卡片等）
```

- **左侧 sidebar**：放所有控制元素（统计、难度、按钮、提示），宽度固定不参与弹性
- **右侧 main**：放游戏核心内容（canvas / 拼图网格 / 卡片区域），用 `flex: 1` + `min-width: 0` 自适应填满剩余空间
- **移动端**（≤768px）：`.game-layout` 切换为 `flex-direction: column`，sidebar 在上、游戏区在下

## 约束

- 只改 `template/syzoj/` 下对应文件，**不碰其他模板**
- **不改共享的 `game_confetti.js`**，per-game 覆盖写在本页 JS 里
- CSS 类名用游戏缩写前缀（如 `gn-`/`mg-`/`sk-`），避免跨游戏冲突
- 不要引入新框架，纯 CSS + 原生 JS
