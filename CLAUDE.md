# HUSTOJ 项目指南

## 基础信息

- 路径: 多端开发——Mac `/Users/zhangmaofan/PycharmProjects/hustoj`、Windows `D:\source_code\hustoj`
- 分支: `my_oj`
- 模板: syzoj
- 数据库: jol

## 目录结构

```
├── db/              # SQL变更归档
└── trunk/web/
    ├── admin/       # 管理后台（考试相关功能：exam_*.php）
    ├── include/     # 核心函数库（school.php 学校隔离逻辑）
    ├── template/    # 前端模板
    ├── lang/        # 多语言包
    └── exam_*.php   # 学生端考试功能
```

## 核心规范

### SQL变更（强制）

- 统一归档到`db/`目录，命名格式：`V{版本号}_{日期}_{功能描述}.sql`
- 使用标准DDL语法，兼容 MySQL 8.0.28
- 文件末尾附回滚SQL
- 同步更新`db/RELEASE_STEPS.md`发布流程
- **触发条件**：以下场景必须同步创建SQL归档文件：
  - ALTER TABLE（新增/修改/删除字段、索引）
  - CREATE TABLE / DROP TABLE
  - 数据迁移或批量UPDATE
- **自检方法**：commit前检查代码中是否新增了`ADD COLUMN`、`MODIFY COLUMN`等DDL操作

### 代码规范

- 模板只改 syzoj（`template/syzoj/`），mdui/sweet/sidebar/bs3/bshark 等其他模板绝对不动（用户只用 syzoj 作学生端；`.claude/hooks/guard_template.sh` 会对 Edit/Write 机械拦截）
- 管理后台权限控制统一使用`$_SESSION[$OJ_NAME.'_'.'administrator']`
- 管理后台菜单配置文件是`admin/menu2.php`（不是menu.php，避免踩坑）
- 管理后台表单提交必须引入`check_post_key.php`做CSRF校验
- SQL必须使用参数化查询，避免注入
- XSS防护使用`htmlentities($str, ENT_QUOTES, 'UTF-8')`

### 权限控制

- 游客白名单统一配置在`template/syzoj/header.php`，仅允许访问首页、题目列表、新闻等只读页面
- 非白名单页面自动跳转到登录页，登录后返回原页面

## 多代理工作流

主会话 = 编排者 + 规划者 + 决策者，**规划不外派**；子代理只做窄执行/验收，只回传 file:line 级结论。操作细则在 `/implement` `/review` `/test` `/release` 与 `.claude/agents/*.md`，用到再展开。

- 小需求（单文件、无 DB/权限变更）主会话直接改 + `php -l` 自测；大需求先 EnterPlanMode 出 `.claude/plans/{任务}.md`（目标/根因/方案取舍/改动清单 file:line/是否需 SQL 归档/验证步骤/验收标准）
- **派 reviewer 仅限**：安全面（权限/支付/上传/CSRF）、DB 变更、多文件业务逻辑、coder 代笔批量改动。文案/样式/模板展示层改动一律不派，主会话按 `reviewer.md` 清单自查
- **验证路径二选一**：浏览器 UI 流派 tester；后端 curl / 登录态 POST / DB 断言由主会话按 deploy-test-env skill 配方直接跑（不派 tester）
- coder 完成后 reviewer + tester 同一消息并行派发（互不依赖）；委派前先问「这个子代理能否拿到主会话没有的信息？」，拿不到就不派
- reviewer 输入必须含未跟踪新文件（`git status --porcelain` 的 ?? 条目，**git diff 看不到**）+ **可判定断言**（"第 N 行的 X 是否仍在且已转义"），禁开放式「是否等价/是否正确」比对题（无收敛点会拖到超时）；阅读配额 ≤1000 行
- tester prompt 只给场景/账号/入口 URL/一两条坑，塞无关信息会让 flash 模型陷入重试循环
- **卡死保险丝（双层）**：三个子代理 frontmatter 已配 `maxTurns`（reviewer 40 / coder 80 / tester 80），触顶自动 `error_max_turns` 终止返回；再叠人工兜底——派发记时刻，超 10 分钟即 TaskStop 由主会话接管（子代理输出只在结束时落盘、中途恒 0 字节，故这是 SLA 超时判，不是存活探测）；**子代理运行期间不要改被审文件**（它拿的是派发时快照，改完结论即失效）
- 省 token：子代理禁整文件 dump 回主会话，只读探索优先 Grep/Glob，规划期宽搜索派 Explore，一个需求一个会话完成即 `/clear`

## 测试环境

- 测试虚机：web-2204，部署路径：/home/judge/src/web/，sudo密码：judge
- 虚机执行命令格式：`multipass exec web-2204 -- sudo -S [shell命令] <<< "judge"`
- 测试账号：教师用户zezhang/zezhang123，学生用户test/test123，管理员admin/admin123
- 文件部署/缓存清理/`php -l`自测/VM 内 curl 模拟登录与表单验证：按 `.claude/skills/deploy-test-env/SKILL.md` 执行（引擎 = 仓库根`deploy_test_env.sh`；模板/页面内容改动须`-f`重启php8.1-fpm清APCu缓存；虚机 IP 动态须`multipass list`查；页面级 GET 须带浏览器 UA，curl 默认 UA 会被 nginx 反爬 403；复杂脚本本地写文件+base64 传输，禁止内联 multipass exec）
