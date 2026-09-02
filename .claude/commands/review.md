---
description: 只做验收：派 reviewer 子代理审查当前改动，输出问题清单与 BLOCKING/NON-BLOCKING 结论（不编码）
argument-hint: 可选审查范围，如"git diff"或具体文件路径；缺省为当前分支未提交改动
---

按 HUSTOJ 多代理工作流执行验收（只验收，不编码）：

**审查范围：** {{$1:当前分支未提交改动（git diff）}}

执行步骤：

1. 确定审查范围：
   - 未指定：默认 `git diff`（当前未提交改动）；若为空则审查最近一次提交 `git show --stat HEAD` 的改动
   - 指定了文件或范围：按给定范围审查
2. 若有相关 `.claude/plans/*.md` 验收标准，一并作为审查依据
3. 派 reviewer 子代理（glm-5.3），输入 = 审查范围 + 验收标准，要求输出：
   - 按严重度排序的问题清单，每条附 file:line 与修复建议
   - 末尾结论：BLOCKING / NON-BLOCKING；无问题则明确写"通过"
4. 汇总给用户：BLOCKING 问题列出并建议打回 coder 修复；NON-BLOCKING 问题列出，可放行后跟进
