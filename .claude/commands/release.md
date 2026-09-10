---
description: 发布收尾检查：全量清点、SQL 归档校验、RELEASE_STEPS 更新、模板范围、虚机 php -l 绿灯，全过才提交
argument-hint: 可选提交说明；缺省按改动内容生成 conventional commit
---

按 HUSTOJ 发布纪律做收尾检查（只检查与提交，不改业务代码）：

**提交说明：** {{$1:未指定，按改动内容生成 conventional commit（fix/feat/style/chore/docs 前缀 + 中文描述，与近期提交风格一致）}}

执行步骤：

1. **全量清点**：`git status --porcelain` + `git diff --stat HEAD` 列出全部改动（**含 ?? 未跟踪新文件**），逐个确认与本次需求一致；出现计划外文件先停下问用户
2. **SQL 归档**（改动含 DDL / 数据迁移时）：`db/V{版本号}_{日期}_{功能描述}.sql` 存在、文件末尾附回滚SQL、`db/RELEASE_STEPS.md` 已同步更新——缺任一项停下补齐再继续（commit 时 guard_sql_archive hook 也会机械拦截）
3. **模板范围**：改动清单里没有 `template/` 下非 syzoj 目录的文件（guard_template hook 平时已拦截，此处对清单复核一遍）
4. **php -l 绿灯**：改动的所有 .php 文件按 `.claude/skills/deploy-test-env/SKILL.md` 在 web-2204 逐一 `php -l` 通过
5. **提交**：全绿后 `git add` 相关文件（未跟踪新文件务必显式 add，勿遗漏）并提交
6. **汇报**：提交哈希 + 改动统计 + 检查清单逐项结果；任一项红 → 停下报用户，不提交
