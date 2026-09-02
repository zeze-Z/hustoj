---
description: 按多代理工作流实现需求：评估规模 → 规划 → coder 编码 → reviewer 验收 → 放行/打回
argument-hint: 需求描述，如"修复存量用户登录跳 welcome"
---

按 HUSTOJ 多代理工作流实现以下需求：

**需求：** {{$1:（未提供需求描述，先向用户澄清要做什么）}}

执行步骤：

1. **评估规模**：涉及多文件 / DB 变更 / 权限、支付等安全面 → 大需求；否则视为小需求
2. **规划（主会话完成，不派子代理）**
   - 大需求：EnterPlanMode 产出 `.claude/plans/{任务}.md`（目标 / 根因 / 方案取舍 / 改动清单 file:line / 是否需 SQL 归档 / 验证步骤 / 验收标准），获批后实施
   - 小需求：主会话直接实现，用 `php -l` 自测
3. **编码**：方案明确后派 coder 子代理，输入 = plan 文件路径 + 验收标准；要求回传 file:line 摘要 + 自测命令与结果
4. **验收**：派 reviewer 子代理，输入 = git diff + plan 文件；要求输出按严重度排序的问题清单 + BLOCKING / NON-BLOCKING 结论
5. **收尾**：BLOCKING → 打回 coder 修复后复验；NON-BLOCKING / 通过 → 汇总放行

全程遵守 CLAUDE.md 省 token 原则：子代理只回结论（file:line 级），禁止整文件 dump 回主会话；只读探索优先 Grep/Glob。
