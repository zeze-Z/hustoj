---
description: 在 web-2204 测试环境对当前改动做端到端验证：部署 → 清缓存 → browser-use 跑流程 → 汇报结果
argument-hint: 可选测试场景描述，如"存量用户登录不再跳welcome"
---

按 HUSTOJ 测试规范在 web-2204 测试环境验证当前改动：

**测试场景：** {{$1:（未指定场景，先向用户确认要验证什么行为）}}

执行步骤：

1. **确认改动范围**：`git diff --stat` 看本次改了哪些文件
2. **同步到测试虚机**：先传到 /tmp（`multipass transfer [本地文件] web-2204:/tmp/[文件名]`），再 mv 到部署路径 `/home/judge/src/web/`
3. **清缓存**：`multipass exec web-2204 -- sudo -S php -r 'opcache_reset();' <<< "judge"`（无需重启 php-fpm）
4. **端到端验证**：用 browser-use skill 驱动页面，按场景走流程
   - 普通用户：zezhang / zezhang123
   - 管理员：admin / admin123
5. **DB 状态核对**（按需）：用 Bash 只读查 `jol` 库确认字段/状态。**注意 jol 表是 MyISAM，勿用事务回滚测试**
6. **汇报结果**：
   - 通过：明确写"通过" + 关键验证点
   - 失败：复现步骤 + 现象 + file:line 定位建议，交回主会话/coder 处理

注意：只做验证与只读检查，不修改业务代码；测试中产生的测试数据在汇报里说明，由用户决定是否清理。
