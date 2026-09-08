# 离线游戏 license.js 随包授权：学生机双击即用

## 目标

教师机在 activate.html 验证授权码后，把授权文件保存进离线包 → 整包拷贝到学生机 → **学生双击 index.html 零操作直接进游戏**（不再逐台粘贴授权码）。

## 背景（为什么是 license.js）

- 现机制：localStorage（本机浏览器）为主路径 + license.dat fetch/XHR 兜底
- **Chrome/Edge 在 file:// 下禁止 fetch/XHR**（上轮 review 实证）→ 学生机自动读不到 license.dat，只能逐台粘贴
- File System Access API 在 file:// 页不暴露；`<input type=file>` 需手动选文件
- **`<script src>` 加载同目录本地文件不受 file:// 限制** —— 本包已在用（js/jquery.min.js、js/auth.js 都这么加载且实测通过）
- 方案：授权内容包成 `license.js`（`window.OG_LICENSE_DATA = {...}`），随包放包根，页面经 script 标签注入，auth.js 验签后放行

## 方案与取舍

**授权读取链（新顺序）**：
1. localStorage（已激活老机器，行为不变）
2. **`window.OG_LICENSE_DATA`（license.js，新增）**
3. license.dat fetch/XHR（Firefox 兜底，保留不动）

关键取舍：
- **script 授权验证通过后不写 localStorage** —— 包内文件每次加载重新验签；续期换包后新 license.js 直接生效，不会被本机旧 localStorage 遮蔽（localStorage 过期 → 落到 license.js → 放行）
- 不做"导入授权文件"按钮 —— script 通道已覆盖分发场景，少一个概念
- license.dat 兜底链保留（无害，Firefox 仍受益），UI 不再宣传
- activate.html 不加载 license.js（它是激活入口，无需读取）

安全评估：license.js 明文随包 = 原 license.dat 同级暴露，威胁模型不变；防伪靠 RSA-PSS 签名（覆盖 `school|room|expire`，auth.js:231），篡改任意字符验签即败；内容由 `JSON.stringify` 生成、独立 .js 文件无 `</script>` 内联注入面。**无服务端改动、无 DB 变更。**

## 改动清单

| # | 文件 | 改动 |
|---|---|---|
| 1 | `trunk/web/offline-games/js/auth.js` | checkAuth 在 localStorage 块（:250-259）与文件兜底（:261-273）之间插入 script 授权分支：`typeof window.OG_LICENSE_DATA !== 'undefined'` 守卫 → validateLicense → 有效即返回（**不 persistLicense**），无效则携带 message 继续；注释说明通道与不落 localStorage 的理由 |
| 2 | `trunk/web/offline-games/activate.html` | ① 验证成功分支（:304-324）：`save-section` 重构为两个板块 —— **「分发到学生机（推荐）」**：下载 license.js 按钮（Blob `application/javascript` + `a.download='license.js'` + revokeObjectURL）+ 从 `location.pathname` 计算包根绝对路径显示（`/D:/...` → `D:\...\`，非 Windows 保持正斜杠）+ 一键复制路径 + 4 步引导（下载→移入包根→整包拷贝→学生机双击即用）；**「方式二（备用）」**：保留现有 textarea+复制授权码文本（单机重激活用）。② 页面底部"激活步骤"ol（:245-253）同步新流程 |
| 3 | `trunk/web/offline-games/index.html` | :328 前加 `<script src="license.js"></script>`（须在 js/auth.js 之前）；overlay 备用文案（:322）更新为 license.js 语义 |
| 4 | `trunk/web/offline-games/transform_games.sh` | 注入壳（STYLE heredoc）:152-154 的 script 链中、`rsa_verify.js` 之前加 `<script src="../license.js"></script>`；overlay 文案（:149）微调。**改完重跑脚本再生成 17 个游戏页** |
| 5 | `trunk/web/offline-games/build_package.sh` | 授权模式（:116-130）生成 license.dat 后追加包装：`{ echo "window.OG_LICENSE_DATA ="; cat license.dat; echo ";"; } > license.js`；使用说明输出（:242-256）两种模式的流程文案按新机制重写（通用版：教师验证后下载 license.js 入包→整包拷贝；授权版：包内自带，双击即用） |
| 6 | `trunk/web/template/syzoj/more.php` | 兑换流程 4 步（:1681-1697）与激活步骤 3 步（:1762-1777）文案：第 3/4 步改为"验证后下载 license.js 放入包根目录"→"整包拷贝学生机，双击 index.html 直接使用"。控制器 `trunk/web/more.php` 是 shim，不动 |
| 7 | 文档 | `README.md`（快速开始/备用激活方式 :44-51、目录结构说明 :65）、`快速入门指南.md`（第2/3步+提示 :14-30）、`激活帮助.html`（四.5"自动激活"、五.错误备用 :336、六.离线使用步骤 :349-355）同步新流程 |

## SQL 归档

**不需要** —— 纯前端 HTML/JS/shell + 模板文案，无 DDL、无服务端逻辑改动。

## 实施流程（按 CLAUDE.md 流水线）

1. coder 子代理按本计划实施 #1-#7（方案已明确，遇歧义停止回报）
2. 主会话：spot-check + `node --check` 校验 auth.js + 沙箱验证 transform（重定向 SRC/DST 跑一遍确认注入正确、其余 16 游戏仅新增 1 行 script）→ 正式重跑 `transform_games.sh`
3. 生成测试授权：node crypto（RSA-PSS/SHA-256/salt=32）用仓库 `etc/offline_games/private_key.pem` 签发测试 license.js 放入 dist 包根
4. reviewer 子代理静态验收（git diff + 本计划）
5. 用户浏览器手动验证（checklist 见下）→ 通过后 `build_package.sh --generic` 重打包（Windows Git Bash，terser 未装会自动跳过）

## 验证步骤

**静态/自动**：
- `node --check` auth.js；激活页/首页无 PHP 残留
- 沙箱+正式 transform 后：17 个游戏页各恰好 1 处 `../license.js` 引用且位于 auth.js 之前；与旧版 diff 仅此差异
- dist 重打包后结构断言：index.html/activate.html 含 `license.js` 引用、包内无源码目录泄漏

**浏览器手动（用户提供，~2 分钟）**：
1. 清空网站 localStorage（DevTools → Application → Local Storage → 删除 `og_license`）→ 双击 index.html：**应直接进游戏**，底部显示授权条（模拟学生机零操作）
2. 移走 license.js 再开 index.html：应显示激活遮罩（原兜底行为不回归）
3. 改 license.js 里任意一个字符（如学校名）再开：应显示"签名无效"遮罩
4. activate.html 粘贴授权码 → 验证通过 → 出现下载按钮和包根路径 → 下载得到的 license.js 内容正确

## 验收标准

1. 无 localStorage + 包根有 license.js → 双击 index.html 直接进游戏 ✅（核心目标）
2. 无 license.js / license.js 被篡改 / 已过期 → 激活遮罩，提示文案正确（不误放行）
3. 已激活机器（有效 localStorage）行为不变；localStorage 过期但 license.js 有效（续期场景）→ 可进游戏
4. auth.js 未把 script 授权写入 localStorage（diff 确认）；license.dat fetch 兜底链保留
5. 17 个游戏页重生成后除新增 license.js 引用外与当前版本一致；activate/index 无外链/PHP 残留
6. more.php 与三份文档步骤文案与新流程一致，无"每台学生机粘贴授权码"类旧表述残留
7. reviewer 结论 NON-BLOCKING 及以下

## 变更记录

**2026-09-08 用户指令（布局调整）**：license.js 放离线包 `js/` 目录（与 auth.js 同级）；activate.html 放 `games/` 目录。连带调整：
- 源文件移动 `offline-games/activate.html` → `offline-games/games/activate.html`，其内部引用 `js/*` → `../js/*`
- index.html：`src="js/license.js"`、激活链接 `games/activate.html`；auth.js 未授权重定向 `games/activate.html`
- transform 注入：`<script src="../js/license.js">`、overlay 链接同目录 `activate.html`
- build_package.sh：license.js 生成到 `$PACKAGE_DIR/js/`（需先 mkdir -p，terser 跳过该文件）；activate.html 随 games/ 目录整体拷贝（删除单独 cp）
- activate.html 路径计算：剥离末尾 `games/` 得包根，UI 显示 `包根\js` 目录
- 文档（含计划遗漏的 用户手册_离线游戏激活指南.md，即 reviewer LOW-1）与 more.php 步骤文案同步

## 发布提醒（顺延既有事项）

- dist 需 `build_package.sh --generic` 重打包 + 重传网盘（兑换页硬编码链接指向旧包，本来就在发布清单里）
- nginx `location /offline-games/ { return 404; }` 仍未配
