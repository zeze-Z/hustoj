---
description: 在 web-2204 测试环境对当前改动做端到端验证：派 tester 子代理执行 部署 → 清缓存 → browser-use 跑流程，主会话只收结论
argument-hint: 可选测试场景描述，如"存量用户登录不再跳welcome"
---

对当前改动做端到端验证。**执行全部交给 tester 子代理，主会话不直接跑部署/浏览器/DB 命令**（测试中间输出大，落主会话上下文费 token）。

**测试场景：** {{$1:（未指定场景，先向用户确认要验证什么行为）}}

**先判断要不要派**：只有浏览器 UI 流（登录点击、表单填写、截图断言）才派 tester。后端 curl / 登录态 POST / 上传拒绝 / DB 断言由主会话按 `.claude/skills/deploy-test-env/SKILL.md` 的「curl 模拟配方」直接跑，输出收敛为 PASS/FAIL 结论行——不要派 tester（主会话已具备全部信息，中转无增益）。

步骤：

1. **主会话**：`git diff --stat` 确认改动文件清单
2. **派 tester 子代理**（Agent tool，subagent_type: tester），输入 = 测试场景 + 改动文件清单 + 验收标准（有 plan 文件附路径）。**prompt 保持精简**：场景清单 + 账号 + 入口 URL + 一两条实测坑提示，不塞 curl 配方等无关信息（信息过载会让 flash 模型陷入重试循环）
3. **收结论**：tester 回传 通过（逐条验证点）或 失败（复现步骤 + 现象 + file:line 定位建议）
4. **决策**：失败 → 主会话定位原因，打回 coder 修复后重测；通过 → 汇报用户放行
