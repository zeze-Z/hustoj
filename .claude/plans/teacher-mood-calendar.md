# 教师解压日历 实施 Plan

## Context

more.php#teacher（教师服务 Tab）新增一个纯前端趣味减压工具卡片：根据日历自动判断老师当日工作/假期状态（周一丧、周五嗨、节前狂欢、收假忧郁、期末冲刺、开学焦虑……），生成情绪化的当日心情状态卡片，支持交互与分享/保存图片。定位为传播向轻工具（无需登录、无 DB），与既有「考编决策」「教育家人格」测评页同一流量位。

## 已确认决策

1. 交互深度 = **B 案**：月历 + 心情 emoji 5 选 1 + 解压小互动（捏泡泡/戳气球）
2. 节假日/寒暑假数据 = **硬编码**（独立数据 JS，一行可改）
3. 分享卡 = 当日心情卡 + **本周心情曲线小图**
4. 命名 = **教师解压日历**
5. 特殊窗口 = 法定节假日**前后 3 天**；寒暑假**前 2 周、后 1 周**
6. 文案 = **多槽位组合生成**（千人千面，见下）
7. 未明确答复的 3 个小决策按建议默认：寒暑假窗口用估算值硬编码（数据文件可改）、加「假期余额不足」、加节日彩蛋文案

## 状态引擎（纯 JS，命中即止）

数据表（`teacher_mood_calendar_data.js`）：
- 表 1 法定节假日：2026 全年 + 2027 元旦/寒假，含调休上班日（以国务院公告为准写入）
- 表 2 寒暑假窗口（估算，可一行改）：
  - 暑假 `2026-07-06 ~ 2026-08-30`（假前 2 周 ≈ 6/22–7/5；假后 1 周 ≈ 8/31–9/6）
  - 寒假 `2027-01-29 ~ 2027-02-28`（假前 2 周 ≈ 1/15–1/28；假后 1 周 ≈ 3/1–3/7）

| 优先级 | 规则 | 状态示例 | 电量 |
|---|---|---|---|
| 1 | 法定节假日当天（学期中） | 假期充电 | 85% |
| 2 | 节前第 1~3 个工作日 | 逐日升温，最后工作日狂欢峰值 | 90→120% |
| 3 | 节后第 1~3 个工作日 | 收假忧郁→缓慢回血 | 20→50% |
| 4 | 寒暑假前 2 周 | 期末冲刺·改卷地狱（带放假倒计时） | 35~55% |
| 5 | 寒暑假中 | 躺平充电；最后 3 天「假期余额不足」 | 90%/70% |
| 6 | 寒暑假后 1 周 | 开学焦虑，逐日回血 | 25~55% |
| 7 | 其余（星期基线） | 周一 30 / 周二 55 / 周三 50 / 周四 75 / 周五 95 / 周末 80 | — |

重叠裁决：寒暑假窗口内的法定节假日（春节/元旦）走寒暑假规则；春节/国庆当天叠加彩蛋文案。

## 文案系统（千人千面）

固定状态名（每规则 1 个）+ 多槽位组合，各槽位独立文案池，按 `hash(yyyy-mm-dd + 槽位salt) % pool.length` 取模（同天稳定可复现/可断言，跨天轮换）：

- 主文案池（每规则 6~10 条）
- 吐槽后缀池、今日宜/忌池、幸运物池、解压建议池（跨规则复用）
- 自选 emoji 5 选 1（进卡片与分享图）；可选称呼输入（如「王老师」）进分享图
- 电量 = 规则值 ± emoji 微调（如 😭 -5% / 🥳 +5%）
- 组合空间数千观感；全部本地数组，无 AI 无接口

## 改动清单

| 文件 | 动作 | 内容 |
|---|---|---|
| `trunk/web/teacher_mood_calendar.php` | 新增 | 薄壳入口，照抄 `teacher_battery_test.php` L2-11（cache_start → db_info/my_func/setlang → require 模板 → cache_end），无登录校验 |
| `trunk/web/template/syzoj/teacher_mood_calendar.php` | 新增 | 三幕式页面（详见下方复用清单） |
| `trunk/web/template/syzoj/js/teacher_mood_calendar_data.js` | 新增 | IIFE 挂 `global.TMC_DATA`：节假日/寒暑假表 + 各槽位文案池（文案不进 PHP 模板） |
| `trunk/web/template/syzoj/more.php` | 修改 | ① panel-teacher cards-grid 末尾（L1942 `</div>` 前）加 NEW 卡片（主入口，按需求）；② panel-quiz 末尾（L2021 前）同卡双挂——与 career_test/teacher_style_test 双挂惯例一致 |
| `js/qr_helper.js`、`qrcode.min.js`、canvas-confetti、`game_confetti.js` | 复用 | **严禁拷贝 qr_helper.js 第四份**（其头注释明令）；分享卡套路照抄 `teacher_battery_test.php` |

无 SQL 变更、无 DB、不动 syzoj 以外模板。

## 复用清单（file:line，源自实测）

**照抄（几乎逐行）**
- 入口薄壳：`teacher_battery_test.php` L2-11；模板头 `$show_title` + `$hide_chrome=true` + header/footer include（模板 L1-6/L1029）
- 站点名注入：模板 L8-10 JSON_HEX_* flags + L506-510 `window.TBS` 模式 → 新页 `window.TMC`（PHP 输出 `json_encode($OJ_NAME)` 防注入）
- script 引入顺序（L511-517）：canvas-confetti CDN @1.9.3 → `game_confetti.js` → `qrcode.min.js` → `qr_helper.js` → 数据 JS；礼花调用带 `typeof window.launchConfetti==='function'` 守卫（L779 模式）
- 状态机 `show()`（L533-540，`.is-on` 切换 + scrollTo）；弹层焦点暂存/恢复/Esc/遮罩关闭（L974-1018）；`aria-modal`
- 分享卡 `drawShareCard`（L848-972）：750×1334 Canvas、`document.fonts.ready` + 1200ms `Promise.race`、标题超宽 `measureText` 等比缩、`wrapLines` 金句 2 行封顶、`makeQrCanvas(url,144,色)` 白底圆角垫底、微信 UA 换「长按保存」提示（桌面右键保存，不生成下载链接）；qr_helper 三 API：`makeQrCanvas(text,size,colorDark)`、`roundRect`、`wrapLines`
- more.php NEW 卡片：骨架照 L1954-1967（**带 NEW 角标必须给 `a.card` 加 `style="position: relative;"`**，badge CSS 在 L322-334）
- 免责句保留（结果页 + 分享卡底部），系列定位「不采集任何个人信息」

**新写**
- **月历热力网格**：全库无现成月历组件（timetable.js 是课程表贴图渲染器，不可用）——7 列 × N 行布局、月切换、每日状态上色、点选切换预览，页面内联 CSS + 类前缀 `mc-`（`:root` 变量 + `.mc-state`/`.mc-btn`/`.mc-modal` 三档按钮，375px 移动优先，单文件前缀隔离）
- 主题视觉：新皮「日历手账/便签」风（tb- 黑板涂鸦、cs- 解忧杂货铺各不相同，本页自成一套）
- 状态引擎 + 文案槽位组合逻辑（内联 IIFE，数据只读 `TMC_DATA`）
- 分享卡主视觉改造：电池/圆环 → 当日心情主视觉 + **本周电量 sparkline**（Canvas 线图，非 echarts）

## 验证

1. `php -l` 自测；`deploy-test-env.sh` 部署 + `-f` 清缓存
2. curl 带浏览器 UA 验证游客可访问入口页与新页
3. 规则引擎批量断言（浏览器 console 按日期跑用例）：
   - 2026-09-28 = 节前第 3 天；09-30 = 狂欢 120%；10-08 = 收假 20%
   - 暑假前 2 周任一天 = 期末冲刺；开学第 1 天 = 开学焦虑
   - 月历点选未来周五 vs 下周一对比
4. tester browser-use：入口卡片 → 生成 → 点选他日 → emoji 选择 → 解压互动 → 生成分享卡（含曲线）→ 截图断言（图片断言等 naturalWidth）

## 验收标准

- more.php 教师 Tab 卡片可点进新页，游客可访问
- 各规则用例状态/电量/文案与引擎一致；同日刷新文案稳定，跨日不同
- 分享卡含当日状态 + 本周曲线 + 称呼/emoji + 二维码，保存正常
- reviewer 验收 NON-BLOCKING 或问题清零后放行
