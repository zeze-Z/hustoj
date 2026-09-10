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

## 多代理工作流（Anthropic 推荐配置）

主会话 = 编排者 + 规划者 + 最终决策者（大模型）。**规划在主线完成，不派子代理**；子代理只做"窄而专"的执行与验收，交接靠结构化结论控制 token 开销。

### 标准流水线：主会话规划 → coder 编码 → reviewer 验收 → tester 验证

1. **规划（主会话完成）**
   - 大需求（多文件 / DB 变更 / 权限、支付等安全面）先走 EnterPlanMode，产出 `.claude/plans/{任务}.md`：目标 / 根因 / 方案与取舍 / 改动清单 file:line / 是否需 SQL 归档 / 验证步骤 / 验收标准，获批后实施
   - 小需求（单文件、无 DB 变更）主会话直接实现
2. **编码（coder 子代理，glm-5.3-flash）**
   - 触发条件：方案已明确。输入 = plan 文件路径 + 验收标准
   - 遇方案歧义立即停止回报主会话，不自行拍板
   - 输出 = file:line 摘要 + 自测命令与结果，不带回整段文件内容
3. **验收（reviewer 子代理，glm-5.3）——与步骤4并行派发**
   - 输入 = `git diff HEAD` + 未跟踪新文件清单（`git status --porcelain` 的 ?? 条目，**git diff 看不到未跟踪文件**）+ plan 文件；只验收不修改
   - 输出 = 按严重度排序的问题清单 + `BLOCKING` / `NON-BLOCKING` 结论
   - `BLOCKING` → 主会话打回 coder 修复后复验；`NON-BLOCKING` → 放行并记入跟进
   - 分级：仅文案/样式/模板展示层且单文件、无 SQL/权限逻辑的改动，不派 reviewer，主会话按 reviewer.md 清单自查
4. **端到端验证（tester 子代理，glm-5.3-flash）——与步骤3并行派发**
   - 触发条件：改动涉及页面/流程/DB 行为，需真实环境验证时（即 `/test` 命令）
   - 输入 = 测试场景 + 改动文件清单（有 plan 则附路径与验收标准）；只验证不修改业务代码
   - 输出 = 通过（逐条验证点）/ 失败（复现步骤 + 现象 + file:line 定位建议）
   - 主会话只派发与决策，不亲自跑部署/浏览器/DB 命令
   - reviewer 本地只读、tester 虚机部署互不依赖：coder 完成后同一消息双派发，墙钟减半

### 省 token 原则

- 子代理只回传结论（file:line 级摘要），禁止整文件 dump 回主会话
- 只读探索优先 Grep/Glob，避免全量 Read
- 小改动（单行修复、样式微调）跳过 coder/reviewer，主会话直接改并用 `php -l` 自测
- 端到端测试（部署、浏览器操作、截图等大输出操作）一律派 tester 子代理执行，中间输出不进主会话上下文
- 一个需求一个会话：完成即 `/clear`，别把上一个需求的上下文带进下一个（主会话上下文是最大的 recurring token 开销）
- 规划期的宽搜索（找全部用例、跨文件排查）派 Explore 子代理只回收结论，决策与方案留主线
- 委派前先判断：这个子代理能否拿到主会话没有的信息？拿不到就不派

## 测试环境

- 测试虚机：web-2204，部署路径：/home/judge/src/web/，sudo密码：judge
- 虚机执行命令格式：`multipass exec web-2204 -- sudo -S [shell命令] <<< "judge"`
- 测试账号：教师用户zezhang/zezhang123，学生用户test/test123，管理员admin/admin123
- 文件部署/缓存清理/`php -l`自测：按 `.claude/skills/deploy-test-env/SKILL.md` 执行（引擎 = 仓库根`deploy_test_env.sh`；模板/页面内容改动须`-f`重启php8.1-fpm清APCu缓存）

