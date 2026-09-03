# HUSTOJ 项目指南

## 基础信息

- 路径: `/Users/zhangmaofan/PycharmProjects/hustoj`
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

### 标准流水线：主会话规划 → coder 编码 → reviewer 验收

1. **规划（主会话完成）**
   - 大需求（多文件 / DB 变更 / 权限、支付等安全面）先走 EnterPlanMode，产出 `.claude/plans/{任务}.md`：目标 / 根因 / 方案与取舍 / 改动清单 file:line / 是否需 SQL 归档 / 验证步骤 / 验收标准，获批后实施
   - 小需求（单文件、无 DB 变更）主会话直接实现
2. **编码（coder 子代理，deepseek-v4-flash）**
   - 触发条件：方案已明确。输入 = plan 文件路径 + 验收标准
   - 遇方案歧义立即停止回报主会话，不自行拍板
   - 输出 = file:line 摘要 + 自测命令与结果，不带回整段文件内容
3. **验收（reviewer 子代理，glm-5.3）**
   - 输入 = git diff + plan 文件；只验收不修改
   - 输出 = 按严重度排序的问题清单 + `BLOCKING` / `NON-BLOCKING` 结论
   - `BLOCKING` → 主会话打回 coder 修复后复验；`NON-BLOCKING` → 放行并记入跟进

### 省 token 原则

- 子代理只回传结论（file:line 级摘要），禁止整文件 dump 回主会话
- 只读探索优先 Grep/Glob，避免全量 Read
- 小改动（单行修复、样式微调）跳过 coder/reviewer，主会话直接改并用 `php -l` 自测
- 委派前先判断：这个子代理能否拿到主会话没有的信息？拿不到就不派

## 测试环境

- 测试虚机：web-2204，部署路径：/home/judge/src/web/，sudo密码：judge
- 虚机执行命令格式：`multipass exec web-2204 -- sudo -S [shell命令] <<< "judge"`
- 测试账号：教师用户zezhang/zezhang123，学生用户test/test123，管理员admin/admin123
- 文件同步：先传文件到虚机/tmp目录`multipass transfer [本地文件路径] web-2204:/tmp/[文件名]`，再mv到部署路径
- 代码更新生效：执行`php -r 'opcache_reset();'`即可，无需重启php-fpm服务（避免服务名找不到的坑）

