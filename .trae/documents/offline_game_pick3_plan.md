# 29积分任选3款离线游戏促销包 实施计划

## 需求与决策

more.php「课前游戏集合 · 离线安装包」广告条新增促销 SKU：**29 积分任选 3 款游戏**，与现有 50 积分全套（17 款）并存。

已确认的产品决策：

1. 促销包**每用户限购 1 份**（复用现有"同商品未过期订单"防重）。
2. 已拥有全套有效授权的用户**仍允许**购买促销包（不做跨商品拦截）。
3. 本期**不做**促销包补差价升级全套（升级按全套全价兑换，全套授权天然覆盖促销包）。
4. 所有价格/划线价/有效期**只存在 `point_goods` 表**，后台可改，代码与 JS 一律不写死 29/60 等数字。

## Repository Research（现状链路）

- 横幅与兑换弹窗：[more.php](file:///d:/source_code/hustoj/trunk/web/template/syzoj/more.php)（模板顶部查 `point_get_goods('offline_game')`，价格直接读商品行；弹窗 fetch 提交到 `point_goods_redeem.php`）。
- 兑换 API：[point_goods_redeem.php](file:///d:/source_code/hustoj/trunk/web/point_goods_redeem.php) — 登录/CSRF/10 秒防重放 → 按 `product_key` 查商品与路由 `point_goods_routes()` → 事务内行锁扣积分（`point_apply_change`）→ 调 Python 签 RSA-PSS 授权码 → 原子防重写 `point_goods_order`。
- 商品/订单管理：后台 [point_goods_edit.php](file:///d:/source_code/hustoj/trunk/web/admin/point_goods_edit.php) 可直接录入新商品行，无需改表结构。
- 授权签发：[generate_license.py](file:///d:/source_code/hustoj/trunk/web/offline-games/admin/generate_license.py) 当前签名串固定为 `school|room|expire`，license JSON 字段 school/room/expire/created/version/signature。
- 离线验签：[js/auth.js](file:///d:/source_code/hustoj/trunk/web/offline-games/js/auth.js) — 三条通道（localStorage / 随包 `window.OG_LICENSE_DATA` / license.dat），`checkAuth()` 仅 index.html 自动跳转；**17 个游戏页各自内联调用了 `Auth.checkAuth()`，失败显示遮罩**（具备按游戏鉴权的挂载点）。file:// 下 Web Crypto 不可用时走 [js/rsa_verify.js](file:///d:/source_code/hustoj/trunk/web/offline-games/js/rsa_verify.js) 纯 JS 验签。
- 游戏主页 [index.html](file:///d:/source_code/hustoj/trunk/web/offline-games/index.html) 有 17 张 `<a href="games/{id}.html" class="game-card">` 卡片，目前无 data-game 标识。
- 激活页 [games/activate.html](file:///d:/source_code/hustoj/trunk/web/offline-games/games/activate.html) 验证后只展示学校/机房/到期时间。
- 交付物为**同一个通用离线 zip**（网盘链接，存于商品 `download_url`），授权码在本地激活。打包脚本 [build_package.sh](file:///d:/source_code/hustoj/trunk/web/offline-games/build_package.sh) 不预签授权。

**核心方案**：不为每个用户定制打包。授权码 JSON 新增 `games` 数组（3 个游戏 id），签名串扩展为 `school|room|expire|g1,g2,g3`（id 排序后逗号拼接）；离线包更新后，主页只展示已授权游戏、游戏页按 id 鉴权。**无 `games` 字段的旧授权码按旧签名串验签并视为全套**，保证已签发授权全部继续有效。

游戏 id 取游戏 html 文件名（去 .html），共 17 个：puzzle_game、clock_reading、math_game、color_match、guess_number、memory_game、sequence_memory、snake、bead_game、number_puzzle、idiom_chain、minesweeper、keyboard_game、balloon_typing、frog_typing、coding_game、ai_drawing_game。

## Files and Modules

- `trunk/web/include/my_func.inc.php`
  - `point_goods_routes()` 新增促销包路由（order_prefix `GP`，need_school_room=true）。
  - 新增 `point_offline_game_catalog()`：返回 17 款 `id => 名称`（服务端白名单与前端选择 UI 的唯一数据源）。
- `trunk/web/point_goods_redeem.php`
  - 接收并校验 `games` 参数（仅促销包需要：白名单、去重后恰好 3 个）。
  - 授权码签发分支：促销包追加 `--games` 参数；积分扣减/订单/飞书通知走原通用流程（新增分支，不改通用逻辑）。
  - 飞书通知 remark 附所选游戏名。
- `trunk/web/template/syzoj/more.php`
  - 顶部再查促销商品行与用户的促销包有效订单；价格全部来自商品行。
  - 横幅：增加促销套餐价格入口（双套餐胶囊/角标）。
  - 弹窗：套餐切换（任选3款 / 全套17款）、17 款游戏选择网格（恰好选 3 款）、价格与按钮随套餐联动、积分不足态。
  - "我的授权码"：两种订单可能并存，结果区提供授权切换（默认全套在前），促销授权显示所选游戏名。
  - JS 提交增加 `product_key` + `games[]`；所有价格经 json_encode 从 PHP 输出。
- `trunk/web/offline-games/admin/generate_license.py`
  - 新增可选 `--games id1,id2,id3`：内置 id 白名单校验，签名串追加排序后的游戏列表，JSON 增加 `games` 字段；不传时输出与现在完全一致。
- `trunk/web/offline-games/js/auth.js`
  - `validateLicense()` 兼容新旧两种签名串；返回 license 原样带 games。
  - 新增游戏级判定（无 games/空/`*` 视为全套）；`checkAuth(expectedGame)` 返回 `allowed`。
  - 内置 id→中文名映射（供主页/游戏页/激活页共用）。
- `trunk/web/offline-games/index.html`
  - 17 张卡片加 `data-game`；验签后仅显示授权范围内卡片，促销包追加一张"升级全套"引导卡。
- `trunk/web/offline-games/games/*.html`（17 个游戏页）
  - 内联 `checkAuth()` 调用传入当前游戏 id；授权有效但不含该游戏时复用现有遮罩，提示授权范围与升级入口。
- `trunk/web/offline-games/games/activate.html`
  - 验证通过后展示授权范围（全套或 3 款游戏名）。
- `trunk/web/offline-games/README.md`：补充促销包授权说明。
- 数据库（后台操作，不改代码）：`point_goods` 新增一行 `offline_game_pick3`，price=29，original_price 由你在后台填写（建议 60），validity_days=365，download_url 与全套同一网盘链接，status=1。
- 发布操作：`bash build_package.sh --generic` 重新打通用包 → 上传网盘替换旧 zip（链接若变更需同步更新两个商品行的 download_url）。

## Implementation Steps

1. **服务端目录与路由**：my_func.inc.php 增加 `point_offline_game_catalog()` 与路由项。
2. **签发脚本**：generate_license.py 支持 `--games`，命令行本地验证两种授权均能生成、字段/签名串正确。
3. **兑换 API**：point_goods_redeem.php 增加 games 入参白名单校验与签发参数；限购1份沿用现有防重；本地 `php -l`。
4. **前端弹窗/横幅**：模板内套餐切换 + 游戏选择网格 + 动态价格 + 订单展示；价格只从商品行读。
5. **离线包鉴权**：auth.js 签名兼容与游戏级判定；index.html 卡片过滤；17 个游戏页传 id；activate.html 展示范围。
6. **后台商品录入**：给出字段值，由后台页面新增 `offline_game_pick3` 商品（生产库不直接操作）。
7. **打包发布**：重新生成通用 zip 并更换网盘文件（上线促销兑换前完成）。
8. **README 文档更新**。

## Dependencies and Considerations

- 发布顺序硬约束：**促销包上线兑换前，网盘 zip 必须已是新版**。旧版离线包的 auth.js 不认识 games 字段，新促销授权码在旧包里会按"无 games"误放行为全套；旧授权码在新包中始终有效（向后兼容）。
- file:// 下 Chrome/Edge 走 localStorage 通道、Firefox 可能走文件通道、纯 JS 验签回退（rsa_verify.js）三条路径都要用促销授权实测；签名串变长不影响 RSA-PSS 算法。
- 17 个游戏页逐个改动易遗漏，改完用 grep 校验每个 html 都传入了自身游戏 id。
- 促销包与全套订单可并存：同机激活时哪份授权后激活就生效哪份（以 license.js/localStorage 为准）；全套是超集，激活全套即解锁全部。
- 模板缓存 more.php `$cache_time=30`、OPcache：部署后按既有流程 reset。
- 安全：games 参数服务端白名单严格校验（非法 id 直接"商品配置异常"），shell 调用继续用 escapeshellarg；Python 端再兜一层 id 校验。

## Validation

- `php -l` 校验所有改动 PHP；python 编译检查 generate_license.py。
- 签发三套样本：全套（无 --games）、促销（3 款）、篡改 games 后重打包，验证：旧串/新串验签通过、篡改签名失败。
- web-2204 虚机部署后浏览器实测 more.php：未登录/已登录/积分不足、套餐切换价格联动、必须选满 3 款才能提交、选满后其余置灰。
- 测试积分账号真实兑换促销包：扣 29 积分、`point_goods_order` 行 product_key=offline_game_pick3 且 license_code 含 games；重复兑换被 -2 拦截；全套兑换回归正常。
- file:// 实测新包：促销授权主页仅 3 张卡、直开第 4 款 html 被遮罩；全套授权 17 款正常；旧格式授权码在新包中仍全套正常；激活页授权范围展示正确。
- 检查飞书通知内容含所选游戏名。

## Risks

- 网盘换包不及时 → 促销授权误放行全套：上线 checklist 强制先换包再上架商品（商品可先建为下架状态）。
- 游戏页漏改导致绕过：grep 逐页核验 + 手工逐一直开 html 抽查。
- 授权码 license_code JSON 变长：point_goods_order 该列现为文本型，增量很小，实施时先确认列类型。
- 老用户换新包后激活操作变化：激活流程不变，仅多出授权范围展示；README 同步说明。
