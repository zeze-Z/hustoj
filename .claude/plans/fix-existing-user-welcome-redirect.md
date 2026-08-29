# 修复：存量用户登录被误跳 welcome 页面

## Bug 根因
`users.new_user_reward_claimed` 字段（V2.1, 2026-07-16 上线，`TINYINT(1) NOT NULL DEFAULT 0`）用于标记新用户是否已领取 20 积分注册奖励。

- 新用户注册时默认 0；邮箱激活时 `active.php` 发放 20 积分并置 1；`login.php:158-184` 保留"登录补发"兜底分支，对仍是 0 的已激活用户补发并置 1，再跳 `welcome.php?status=activated`。
- 问题：`ALTER TABLE` 加字段时 `DEFAULT 0`，使所有**存量用户**该字段也是 0。`login.php:159` 的 `new_user_reward_claimed == 0` 对存量用户成立，于是被误判为"未领奖励的新用户"，跳转 welcome，并在余额为 0 时误发 20 积分。

## 影响范围
- 存量用户登录被跳 welcome（已发生）
- 余额为 0 的存量用户被误发 20 积分（point_log 备注「新用户注册奖励（登录补发）」）
- 已登录过的存量用户字段已被 `login.php:180` 无条件置 1，下次不再跳；但**尚未登录过的存量用户**字段仍为 0，下次登录会再次触发 → 必须修复数据

## 修复方案（纯数据迁移，login.php 不动）

### 1. 新建 SQL 迁移文件 `db/V2.2_20260718_fix_existing_users_reward_claimed.sql`
把功能上线前注册的存量用户置为已领取（1），上线后注册的新用户保持 0 走正常流程。

```sql
-- 修复：存量用户登录被误判为新用户跳转 welcome 的问题
-- new_user_reward_claimed 字段（V2.1, 2026-07-16 上线）默认 0，
-- 导致功能上线前注册的存量用户登录时被 login.php 误判为"未领取新用户奖励"，
-- 跳转 welcome.php 并可能误发 20 积分。
-- 将功能上线前注册的存量用户标记为已领取（1），不再触发补发分支；
-- 上线后（reg_time >= 2026-07-16）注册的新用户保持 0，走正常激活/补发流程。

UPDATE `users`
SET `new_user_reward_claimed` = 1
WHERE `new_user_reward_claimed` = 0
  AND (`reg_time` < '2026-07-16 00:00:00' OR `reg_time` IS NULL);

-- 回滚SQL（仅还原存量用户为 0，不影响上线后已正常领取奖励的新用户）
-- UPDATE `users` SET `new_user_reward_claimed` = 0
-- WHERE (`reg_time` < '2026-07-16 00:00:00' OR `reg_time` IS NULL);
```

边界说明：
- `reg_time` 由 `register.php:214` 用 `NOW()` 写入，上线后注册的新用户必有值且 `>= 2026-07-16`，不会被误更新
- `reg_time IS NULL` 覆盖迁移/初始用户（db_init 中 `reg_time DEFAULT NULL`），均为存量用户

### 2. 更新 `db/RELEASE_STEPS.md`
- 版本历史表追加：`V2.2 | 2026-07-18 | 修复存量用户被误判为新用户跳转 welcome`
- 发布流程追加执行命令：`mysql -u root -p jol < db/V2.2_20260718_fix_existing_users_reward_claimed.sql`

### 3. 误发积分
按确认**不回收**。`login.php` / `active.php` / `register.php` 代码均不动。

## 验证步骤（测试虚机 web-2204）
1. 构造存量用户：`UPDATE users SET new_user_reward_claimed=0, reg_time='2026-06-01' WHERE user_id='zezhang';`
2. 执行迁移 SQL，确认该用户 `new_user_reward_claimed` 变为 1
3. 用 zezhang 登录，确认**不再跳转** welcome，正常进入原页面
4. 反向验证：构造新用户 `reg_time='2026-07-17'` 且字段 0，执行迁移 SQL 后字段仍为 0，登录仍能走补发/激活流程
5. 提交代码（含 SQL 归档与 RELEASE_STEPS.md）

## 不改动
- `login.php` / `active.php` / `register.php` 逻辑均不动
