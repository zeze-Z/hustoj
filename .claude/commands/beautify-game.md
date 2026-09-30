---
description: 按统一规范美化游戏页面 UI（一屏自适应+童趣手绘+anime.js/rough.js 动效+礼花）
argument-hint: 游戏页面文件名，如 memory_game.php
---

按 HUSTOJ 游戏页面统一规范美化以下页面的 UI：

**目标页面：** {{$1:（未提供页面名，先向用户确认要美化哪个游戏页）}}

## 执行纪律（先读这条——防过度阅读/过度思考）

批量任务（21 个游戏页）。**耗时真因是过度阅读 + 脑内方案评审，不是写代码慢**：tangram 一役写码只花 1 轮，前面 12 轮全耗在读和试上；另一次改左右分栏，读得很省却又在脑内比了两套布局、手推了 4 档 clamp 数值，被中断后 3 个 Edit + 1 个脚本 2 分钟做完。

1. **动手前双预算：≤3 个文件 / ≤800 行，且思考 ≤1 轮**，超了立刻停。最小阅读集：目标页全文 + 样板 `matchstick_math.html` **只 Grep 3 段**（script 块 / `.gs-sketch` CSS / `win`+`showVictory`）+ `game_sketch.js` 末尾导出表。
   - **禁止脑内方案对比**：想写「方案 A vs B」就停——**选有仓库先例的那个**（分栏抄 snake/minesweeper 的 `.game-layout`，标题通栏在上；规范没写死的取默认值）。布局类改动从读完目标页到第一个 Edit ≤1 轮工具调用。
   - **尺寸/一屏问题禁止手推数值**：落盘后写 ~20 行脚本跑 4 档仿真，1 秒出结果；思考里推 clamp 是纯浪费。按钮撑破/图标 em 清零这类边角**不预先求解**，仿真或冒烟报了再修。
2. **本文件即规格**。不要再开 `memory/game-ui-pattern.md`「验证规范是否属实」（内容大量重复）。
3. **模板壳默认不读**（`header.php`/`css.php`/`learning-arcade.css`/`style.css`）。只在报错或规则冲突时定点 Grep 10 行。
4. **必问用户的一步放最前**（配色/主题确认），一次 AskUserQuestion 问清，前后不串行插探索。
5. **一次 Write 全量落盘**整页，不要分段反复 Edit。
6. **配方式收尾，禁止试探**。已知坑重踩一次 = 白扔 2 轮：
   ```bash
   export MSYS2_ARG_CONV_EXCL="*" MSYS_NO_PATHCONV=1   # Windows Git Bash 必带，否则 /home/... → D:/Program Files/Git/home/...
   bash deploy_test_env.sh -f trunk/web/template/syzoj/xx.html
   multipass exec web-2204 -- sudo -S php -l /home/judge/src/web/xx.php <<< "judge"      # 本地无 php，一律虚机跑
   multipass exec web-2204 -- sudo -S md5sum /home/judge/src/web/template/syzoj/xx.html <<< "judge"
   # 页面冒烟：curl 在虚机内跑（主机→虚机 IP 常不通），UA 必带浏览器（默认 UA 被 nginx 反爬 403）
   multipass exec web-2204 -- bash -c 'curl -s -A "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36" -o /tmp/p.html -w "HTTP %{http_code}\n" http://127.0.0.1/xx.php'
   # 抽 inline JS 做 node --check：输出文件用相对路径（/tmp/ 会被 MSYS 转成 D:\tmp\，node 找不到）
   ```
7. **先交付后复盘**：memory/复盘放在产品落盘并部署之后。

## 总要求

1. **童趣**：玩法元素（道具/棋盘/卡片/边框）rough.js 手绘——歪扭线条、蜡笔填充、形状各异像手画
2. **可爱**：圆角、糖果色、俏皮微动效、emoji 点缀，反馈文案口语化（"再想想~"）
3. **精美**：手绘**只用玩法元素**，UI chrome（统计卡/按钮/弹窗/标题）保持玻璃拟态——"手绘玩具放在光洁桌面"
4. **一屏**：移动端 + web 端都要一屏展示完（见「一屏适配」硬规则）
5. **缓存**：共享层固定 URL 全游戏统一，浏览器缓存复用

## 主题与配色

**每个游戏都有各自的主题背景**，参考最新 4 款：

| 游戏 | 主题 | 背景基调 |
|---|---|---|
| matchstick_math | 小火苗的夜课 | 深夜蓝 `#1E2A52`→`#121A38` + 琥珀光 |
| tangram | 七巧板小工坊 | 米黄纸 `#FAF3E3`→`#F6EAD2` + 点纹 |
| coordinate_quest | 坐标藏宝图 | 湖蓝 `#2f7ea5` + 沙金 `#E3B23C` |
| balance_scale | 羊皮纸天平 | 奶油纸 `#FFF8F0`→`#FBF0DC` |

- **背景色必须与主题呼应**：`.xx-bg-decor`（底色渐变 + 点纹 + 两个浮动光斑）+ CSS 变量（`--xx-bg1/bg2/ink/muted` + 主色系）一游一套，跟着主题走，**不要 21 款全铺同一个色**。
- **找不到主题色时 → 统一粉蓝**（默认兜底，糖果感）：
  ```
  主色 粉 #F49AC2  ·  辅色 蓝 #4FA3C7  ·  暖对比 琥珀 #F2B441
  背景 linear-gradient(150deg,#FDF0F6,#EAF4FB)  ·  墨色 #4A3728
  ```
- **配色原则**：主色系 + 一个暖色对比色，避免全冷色。童趣感：糖果色饱和度略高、大圆角、hover 果冻形变。

## 一屏适配（移动端 + web 端，硬规则）

目标：**首屏不滚动**看完标题/统计/棋盘/按钮。tangram 实测踩过，逐条照做：

1. **`.xx-wrap` 高度 = 视口 − 顶部导航实际占位**，用实测覆盖写死值（`hide_chrome` 时自动退化为 0）：
   ```js
   var top = Math.max(0, Math.ceil(wrap.getBoundingClientRect().top));
   wrap.style.height = 'calc(100dvh - ' + top + 'px)';
   ```
   **禁止 `min-height:100dvh`**：`body` 有 `margin-top:60px`，叠起来结构上就不可能一屏。
2. **`.game-container` 用 `max-height:100%`**（跟随 wrap），不要 `max-height:100dvh`——后者会把导航高算漏，底部必然溢出。
3. **棋盘/网格尺寸从 wrap 内容盒反推**，不要用 `window.innerHeight` 直算（会漏导航 60px + wrap padding ≈ 87px）：
   ```js
   availForCard = wrap.clientHeight - wrapPadT - wrapPadB;
   stageBudget  = availForCard - cardPadT - cardPadB - 非舞台chrome高 - 间距;
   GS = clamp((stageBudget - 常数) / 系数, 下限, availW);   // 常数/系数按舞台结构解出
   ```
   非舞台 chrome 高**实测**（`card.children` 里排除舞台的 `offsetHeight` 之和），别写死 60/70/52 这类估值——统计卡/副标题一改就错。
4. **网格/卡片游戏**（配对、拼图）用「预渲染测量」：`innerHTML=''` 之后、建卡片之前测非网格子元素高度，用 `--card-size`/`--grid-cols`/`--grid-rows` 控网格，`.card` 设 `aspect-ratio:auto`。**所有难度档位都要跑**这套计算。
5. **全 `clamp()` 自适应**，不写固定 px；矮屏（`max-height:560px`）隐藏副标题、压缩统计卡——隐藏后 `offsetHeight=0`，第 3 条的实测会自动让出空间。
6. **兜底**：`.game-container{max-height:100%; overflow-y:auto}`，极端小屏允许内部滚动，但正常档位必须零滚动。
7. **验收**：写完跑数值仿真，至少 4 档视口不溢出再交：
   ```
   375×667 / 390×844 / 1366×768 / 1920×1080   →  overflow:false
   ```

## 共享层（强制，全部复用）

所有游戏页引用**完全相同的 URL**——首游加载写入缓存，后续游戏零重复下载：

| 文件 | URL（`$OJ_TEMPLATE` 照抄） | 缓存 |
|---|---|---|
| anime.js v4.5.0 | `js/anime.min.js?v=4.5.0` | `?v=` 版本 pin，升级才改 |
| rough.js 4.6.6 | `js/rough.min.js?v=4.6.6` | 同上 |
| **game_sketch.js** | `game_sketch.js` | 无 query，ETag 协商 304 |
| canvas-confetti | jsdelivr 固定 `@1.9.3` | CDN 缓存 |
| game_confetti.js | `game_confetti.js` | 无 query，同上 |

```html
<!-- 顺序固定：anime → rough → sketch封装 → confetti → confetti封装 -->
<script src="template/<?php echo $OJ_TEMPLATE?>/js/anime.min.js?v=4.5.0"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/rough.min.js?v=4.6.6"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/game_sketch.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script src="template/<?php echo $OJ_TEMPLATE?>/game_confetti.js"></script>
```

- **game_sketch.js 是共享封装核心**（缺形状给它**加原语**，不重造轮子）：形状 `matchstick`/`frame`/`rect`/`circle`/`ellipse`/`polygon`/`path`/`line`；火焰 `flame`/`flameInto`/`litMatch`；工具 `seedFrom`/`svgInto`。内部 `tagFill` 给 fill 路径打 `gs-fill`，状态变色只覆盖 fill 不碰 stroke。
- **封装层故意不带 `?v=`**（ETag 协商 304，改动即时生效）；库文件冻结上游版才用 `?v=`。**不要**给不同游戏用不同 query。
- **seed 确定性**：rough 形状必传 `seed`（`GameSketch.seedFrom('状态串')`），否则重绘随机歪；同形状多件传不同 seed 才像手画。
- **降级双保险**：`GameSketch.available===false` 或 `window.anime` 缺失 → 全退回纯 CSS，逻辑不受影响；`prefers-reduced-motion` 兜底；编排一律包 `canAnimate()`。
- anime.js 是 **v4 API**：`A.animate(el,{...})`/`A.stagger()`（不是 v3 的 `anime({...})`）。rough 形状返回 SVG `<g>`，opts：`fill/fillStyle:'solid'/stroke/strokeWidth/roughness/bowing/seed`。

## 试点坑位（实测踩过，逐条避开）

1. **`gs-sketch` 类挂 body**，不要挂游戏容器：标题图标、通关弹窗常在棋盘**外面**，挂内层时 `.gs-sketch .xx` 够不着。写 `document.body.classList.toggle('gs-sketch', sketch)`。
2. **隐藏元素量不出尺寸**：弹窗 `display:none` 期间 `offsetWidth=0`，`svgInto` 返回 null——手绘必须在 `classList.remove('hidden')` **之后**画。
3. **图标盒尺寸别用 `em`**：降级 `font-size:0` 藏 emoji 会把 `width:1.7em` 一起清零——盒尺寸用 `clamp(px)` 独立于 font-size。
4. **SVG 动画锚点**：火焰/图标层要 `transform-box:fill-box; transform-origin:50% 100%`（从底部长），否则像果冻不像火。
5. **rough 形状先入 DOM 再测量**：`appendChild` 后才有 `offsetWidth`，先建槽位再调 `GameSketch.*`。
6. **emoji 只作降级**：`.gs-sketch .xx-icon{font-size:0}` 藏 emoji、rough SVG 接管；无 rough.js 时 emoji 自然显示，不删 HTML。
7. **几何 transform 与动画别抢同一个元素**：`style.transform` 管 translate/rotate 时，scale/抖动动画作用在**内层 svg**，否则互相覆盖。

## UI chrome（逐条对照，不要漏）

- `.xx-wrap`（`align-items:flex-start` 靠上，**不要 center**）+ `.xx-bg-decor` + `.game-container` 玻璃拟态 + 顶部彩虹条
- 标题渐变文字：`-webkit-text-fill-color:transparent !important` + `color:transparent !important`（**两个 !important 都要加**，否则 MDC 全局样式压黑）
- 统计栏 grid + 顶部彩色边 + emoji 图标 + 渐变数字
- 难度按钮药丸形，困难档红/橙对比色；主按钮实心渐变 + 次级按钮描边白底 + 按钮波纹
- 反馈条弹性弹入 + 彩色背景（成功绿/失败红/提示橙）+ 统计 bounce
- 结束弹窗 pop-in + 顶部彩虹条 + 渐变标题；弹窗与礼花**同时出现**；礼花走 `game_confetti.js`，移动端偏底时页内写 `launchConfettiFromCard()` 从弹窗中心绽开
- anime.js 编排：通关 stagger、弹窗 `ease:'out(3)'`、错误抖动 `x:[0,-8,7,-5,4,0]`、统计 `scale:[1,1.22,1]`

**布局按游戏形态选**：棋盘/大网格用单列宽卡（matchstick/tangram，`max-width:720-920px`）；信息密集的游戏可左右分栏 `.game-layout`（sidebar `clamp(240px,28vw,300px)` + main `flex:1;min-width:0`），≤768px 切单列、sidebar 在上。

## 工作流程

1. **读规范**：本文件即规格，读完直接进第 2 步。
2. **读目标页**：理解结构（统计项/难度档位/反馈区/结束弹窗/玩法元素形状）。样板只 Grep 片段。
3. **定主题**：AskUserQuestion 问清主题/主色系（无主题则用统一粉蓝兜底）。
4. **接入共享层**：5 行 script + sketch 模式 CSS（隐藏 CSS 平面版、状态色只改 `.gs-fill`）。
5. **玩法元素手绘化**：列清核心玩法元素用 GameSketch 原语画（七巧板 `polygon`、藏宝图 `path`/`frame`、砝码 `rect`/`circle`），填色路径打 `gs-fill`，状态变色走 CSS 覆盖 `fill`。
6. **UI chrome + 一屏适配**：照上面两节逐条做。
7. **自测**：`node --check`（抽 inline script，相对路径）+ 虚机内 `php -l` + **一屏数值仿真 4 档**。
8. **部署**：按「执行纪律」第 6 条配方，md5 对账 + 页面冒烟通过后通知用户验收。

## 改造范围（syzoj 模板下 21 个游戏页）

| 状态 | 游戏 |
|---|---|
| ✅ 已完成（样板） | matchstick_math |
| ☑ 已改造（一屏已修，待验收） | tangram |
| ☐ 待改造 | coordinate_quest、balance_scale |
| ☐ 待改造 | memory_game、guess_number、clock_reading、puzzle_game、math_game |
| ☐ 待改造 | minesweeper、snake、number_puzzle、color_match、sequence_memory |
| ☐ 待改造 | bead_game、balloon_typing、frog_typing、keyboard_game、idiom_chain、coding_game、ai_drawing_game |

每次命令只做 1-2 款，改完同步测试环境再做下一批。缺形状加 GameSketch 原语。

## 参考样板：matchstick_math.html（**只 Grep 以下片段，不通读全文**）

- **共享层接入**：头部 5 行 script + sketch 模式 CSS
- **玩法手绘**：`render()` 的 `GameSketch.matchstick/frame` 接线（先入 DOM 再测量、seed 稳定）
- **标题图标**：`GameSketch.litMatch` + emoji 降级（`.ms-title-icon` 结构）
- **弹窗火焰**：`GameSketch.flameInto` + 三层焰异相摇曳 + spring 绽放（隐藏后再画）
- **编排**：`spawnGold` 金粒、`win()` 点燃 stagger、`showVictory` 弹窗与礼花同帧
- **降级写法**：`canAnimate()` 双保险、`.gs-flicker` CSS 错相闪

## 约束

- 只改 `template/syzoj/` 下对应文件，**不碰其他模板**
- **不改** `game_confetti.js`；`game_sketch.js`/`js/*.min.js` 是共享层——新原语可加，破坏性变更不可；per-game 覆盖写在本页 JS 里
- CSS 类名用游戏缩写前缀（如 `gn-`/`mg-`/`tg-`），避免跨游戏冲突
- 允许引入的库**仅限** anime.js / rough.js / canvas-confetti；**不要引入其他框架**（纯 CSS + 原生 JS 为底，库只做动效与手绘）
