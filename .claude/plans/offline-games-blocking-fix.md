# 离线游戏 BLOCKING 问题修复计划

## 背景

reviewer 端到端验收结论 **BLOCKING**：2 严重 + 3 高 + 4 中 + 2 低。本计划逐项给出修复方向与验收标准，全部闭环后复验。

## 修复项

### A1 [严重1+高3] auth.js 授权读取改 localStorage 优先

**根因**：`js/auth.js` 用 `fetch('./license.dat')`，fetch 按页面基址解析 → games/ 下解析成 `games/license.dat`（路径断链）；且 Chrome/Edge 在 file:// 下 fetch/XHR 全被禁（status 0 / TypeError）→ 激活死循环。

**修复**：
- `activate.html` 验证成功后把授权 JSON 存入 localStorage（key 建议 `og_license`），保留"手动创建 license.dat"指引作为兜底说明
- `js/auth.js` 校验逻辑改为：**优先读 localStorage** → 命中且验签通过即放行；未命中再尝试读文件兜底（Firefox 等允许 file:// 读取的场景）。文件兜底需按序尝试多级相对路径 `['license.dat', '../license.dat']`（去重），适配根目录页与 games/ 子页两种深度
- 验签逻辑本身（RSA-PSS）不动
- `transform_games.sh` 中注入游戏页的遮罩文案同步修改：未授权提示从"请将 license.dat 放到离线包根目录"改为"请打开本包 activate.html 完成激活"（localStorage 是主路径）；已注入的 `games/*.html` 17 个文件用同样文案同步修补
- `index.html` 若有独立读取逻辑，统一走 auth.js 新逻辑

**验收**：auth.js 存在 localStorage 读取分支且优先于文件读取；文件兜底含 `../license.dat`；activate.html 有 localStorage 写入；games/*.html 与 transform_games.sh 文案一致。

### A2 [严重2] 私钥移出 web 根

**根因**：redeem 运行时 exec `admin/generate_license.py`，私钥必须随站点部署在 web 目录，仅 .htaccess 保护；php-fpm/nginx 部署下 .htaccess 无效 → 私钥可被直接下载。

**修复**：
- `admin/generate_license.py`：私钥路径支持 `--private-key` 参数，默认值改为 web 根外 `/home/judge/etc/offline_games/private_key.pem`；公钥同理支持 `--public-key`（公钥留在原位无风险，默认可不变）
- `offline_game_redeem.php`：顶部加配置常量 `OG_KEY_DIR`（默认 `/home/judge/etc/offline_games`），exec 时显式传 `--private-key`；密钥不存在时返回通用失败信息（不向前端泄漏服务器路径），可写日志
- 新增 `trunk/web/offline-games/.htaccess`：`Require all denied`（Apache 下连同 dist/ 子目录一并拒绝；offline-games 下内容本就只通过网盘 zip 分发，无需公网访问）
- `admin/.htaccess` 保留不动
- nginx 配置示例与私钥生成/部署步骤写入 README（见 A6，文档统一处理）

**验收**：代码中无私钥指向 web 目录的引用；offline-games/.htaccess 存在；redeem 显式传密钥路径；缺密钥时行为可控。

### A3 [高4] more.php 生成 postkey

**根因**：syzoj 前台只有 point_index.php 设置过 postkey，直奔 more.php 的用户 `ogPostkey=''` → CSRF 校验必然拒绝且刷新无效。

**修复**：`trunk/web/more.php` 控制器加 `require_once('./include/set_post_key.php');`（该页单表单，无多表单 require_once 丢 key 坑）。

**验收**：登录后直接访问 more.php，渲染输出含 postkey；兑换不再报"页面已过期"。

### A4 [高5] build_package.sh 输出位置与目录防护

**根因**：授权模式打包把真实签名 license.dat 写到 `trunk/web/offline-games/` 根（web 可下载 = 免费激活）；dist/ 无访问限制。

**修复**：
- license.dat 输出到打包临时目录并随包进 dist 内的包根，**不落** offline-games 根目录
- 打包脚本开头确保 `offline-games/.htaccess` 存在（A2 已建静态文件，脚本做存在性检查即可）

**验收**：授权模式打包后 offline-games 根目录无新生成 license.dat；zip 包内 license.dat 位于包根。

### A5 [中6] redeem INSERT 防重校验修复

**根因**：`pdo_query` 对 INSERT 返回 lastInsertId；插入 0 行时返回上一条 point_log 的 id（正数）→ `$insert <= 0` 永不成立。

**修复**：订单 INSERT 改 `$dbh->prepare` + `execute` + `rowCount()`（或插入后回查 order_no 存在），0 行时 rollback 并返回友好错误。

**验收**：判断基于真实影响行数；失败路径不扣积分不发放授权码。

### A6 [中7+低10] 文档与文案修正

- 四份文档（README.md / 快速入门指南.md / 激活帮助.html / 用户手册_离线游戏激活指南.md）统一：按钮名"验证授权码"、激活步骤"手动创建 license.dat"或 activate.html 自动激活（与 A1 实现保持一致）、获取方式"aioj.top → 更多 → 积分兑换"
- README：游戏清单按 `games/` 实际 17 款修正（删"射箭游戏"、补漏）、目录结构与实际一致（删不存在的 css/）
- README 新增"服务器部署"小节：私钥生成与存放路径（/home/judge/etc/offline_games/）、nginx deny 示例（`location /offline-games/ { deny all; }` 或 return 404）
- `template/syzoj/more.php` 横幅"安全授权"→"授权管理"

**验收**：文档描述与代码实际行为逐条一致。

### A7 [中8] RELEASE_STEPS.md 登记 V2.8

补 V2.8 offline_game_order 章节（变更内容 + 执行 SQL + 回滚引用）。

### A8 [低9] redeem tempnam 清理

- `tempnam()` 创建的 og_XXXXXX 空文件与 python 写出的 og_XXXXXX.dat 都要在 finally 中 unlink
- **决策已定**：exec 保留在事务内（保证授权码生成失败时不扣分的顺序安全），加一行注释说明锁持有的取舍，不重构

**验收**：兑换成功/失败路径均无 /tmp 孤儿文件残留。

## 统一约束

- 只改上述文件；不碰 syzoj 以外模板、不碰 games 游戏玩法逻辑（仅授权遮罩文案）
- PHP 改动必须在虚机 lint：`multipass transfer <file> web-2204:/tmp/<name> && multipass exec web-2204 -- php -l /tmp/<name>`
- shell 脚本 `bash -n` 自检；python 脚本在虚机 `python3 -m py_compile`
- dist/ 是构建产物，本次不重新打包（下次打包自动生效），报告中注明

## 总验收标准

reviewer 对照本清单逐项复验，全部"已修复"或"明确处置"才算闭环。
