# OJ 功能发布步骤

## 当前版本：V1.9（2026-06-07）

---

## 版本历史

| 版本 | 日期 | 功能 |
|------|------|------|
| V1.0 | 2026-03-17 | 初始版本（基础OJ） |
| V1.0 | 2026-03-31 | 课件商城模块 |
| V1.0 | 2026-04-11 | 多学校隔离功能 |
| V1.1 | 2026-04-19 | 选择题功能 + 考试模块（合并发布） |
| V2.0 | 2026-04-17 | 选择题分值/解析/竞赛来源字段（补充归档） |
| V1.2 | 2026-05-01 | 课件预览付费改造 |
| V1.3 | 2026-05-05 | 删除提取码字段（百度网盘→金山文档） |
| V1.4 | 2026-05-07 | 题目增加level字段（难度等级1-5） |
| V1.6 | 2026-05-11 | 注册功能优化：用户角色选择、邮箱唯一性校验、教师/学生区分通知 |
| V1.7 | 2026-05-12 | 成都TOP50中小学数据入库 |
| V1.8 | 2026-05-16 | 课件列表页添加课程下载次数展示 |
| V1.9 | 2026-06-07 | 平台积分充值与课件积分支付体系 |
| V2.1 | 2026-07-16 | 新用户注册奖励字段 new_user_reward_claimed |
| V2.2 | 2026-07-18 | 修复存量用户被误判为新用户跳转 welcome |
| V2.4 | 2026-07-24 | 新客连续登录分批奖励（20积分分批发放） |

---

## 发布前检查清单

- [ ] 所有 P0 测试用例通过（见下方冒烟测试表）
- [ ] 本地 `git push` 成功
- [ ] 确认生产环境数据库备份
- [ ] 确认生产机代码目录

---

## 发布流程

### Step 1 — 数据库变更

> 按版本时间顺序执行 SQL 文件

```bash
# 1. 基础OJ初始化（首次部署执行）
mysql -u root -p < db/V1.0_20260317_db_init.sql

# 2. 课件商城模块
mysql -u root -p jol < db/V1.0_20260331_course_module.sql

# 3. 多学校隔离功能
mysql -u root -p jol < db/V1.0_20260411_school_mode.sql

# 4. 选择题功能 + 考试模块
mysql -u root -p jol < db/V1.1_20260419_choice_and_exam.sql

# 4.1 选择题分值/解析/竞赛来源字段（补充归档）
mysql -u root -p jol < db/V2.0_20260417_problem_score_analysis.sql

# 5. 课件预览付费改造
mysql -u root -p jol < db/V1.2_2026-05-01_courseware_preview_upgrade.sql

# 6. 题目难度等级
mysql -u root -p jol < db/V1.5_2026-05-07_add_problem_level.sql

# 7. 注册功能优化
mysql -u root -p jol < db/V1.6_2026-05-11_register_role.sql

# 8. 成都TOP50中小学数据入库
mysql -u root -p jol < db/V1.7_20260512_add_chengdu_schools.sql

# 9. 课件列表页添加课程下载次数展示
mysql -u root -p jol < db/V1.8_20260516_course_download_count.sql

# 10. 平台积分充值与课件积分支付体系（V1.9）
mysql -u root -p jol < db/V1.9_20260607_point_payment.sql

# 11. 新用户注册奖励字段（V2.1）
mysql -u root -p jol < db/V2.1_20260716_add_new_user_reward_claimed.sql

# 12. 修复存量用户被误判为新用户跳转 welcome（V2.2）
mysql -u root -p jol < db/V2.2_20260718_fix_existing_users_reward_claimed.sql

# 13. 新客连续登录分批奖励（V2.4）
#     ⚠️ 强制执行：存量 UPDATE 必须跑，否则存量用户下次登录被误发2分
mysql -u root -p jol < db/V2.4_20260724_login_streak.sql
```

**验证：**
```sql
-- 基础表
SHOW TABLES IN jol LIKE 'problem';
SHOW TABLES IN jol LIKE 'solution';
SHOW TABLES IN jol LIKE 'db_version';  -- 版本控制表

-- 课件商城模块
SHOW TABLES IN jol LIKE 'course%';
-- 预期输出：course, course_subject, course_order

-- 多学校隔离功能
SHOW TABLES IN jol LIKE 'school';
DESCRIBE jol.users school_id;       -- int(11)
DESCRIBE jol.problem school_id;     -- int(11)
DESCRIBE jol.problem is_public;     -- tinyint(4)

-- 考试模块
SHOW TABLES IN jol LIKE 'exam%';
-- 预期输出：exam, exam_attend, exam_problem, exam_result

-- 选择题功能
DESCRIBE jol.problem problem_type;   -- ENUM('programming','choice_single','choice_multi','judge')
DESCRIBE jol.problem options;        -- longtext
DESCRIBE jol.problem answer;         -- varchar(500)

-- 选择题分值/解析/竞赛来源字段
DESCRIBE jol.problem score;          -- int(11), Default: 0, Comment: 题目分值
DESCRIBE jol.problem analysis;       -- text, Comment: 答案解析
DESCRIBE jol.problem contest_source; -- varchar(100), Comment: 竞赛来源
DESCRIBE jol.contest_problem score;  -- int(11), Default: 0, Comment: 竞赛中该题分值

-- solution 表变更
DESCRIBE jol.solution pass_rate;     -- decimal(5,2)
DESCRIBE jol.solution exam_id;       -- int(11)

-- exam_result 唯一索引（ON DUPLICATE KEY UPDATE 依赖此索引）
SHOW INDEX FROM jol.exam_result WHERE Key_name='uk_exam_user_problem';

-- 课件预览付费改造
DESCRIBE jol.course courseware_full_preview_url;   -- varchar(500)
DESCRIBE jol.course lesson_plan_full_preview_url;  -- varchar(500)

-- 题目难度等级
DESCRIBE jol.problem level;     -- int(11), Default: 1, Comment: 难度等级(1-5)

-- 注册功能优化
DESCRIBE jol.users role;         -- varchar(20), Default: student, Comment: 用户角色：teacher/student

-- 成都TOP50中小学数据入库
SELECT COUNT(*) FROM jol.school WHERE code LIKE 'chengdu_%';  -- 预期：56（原有6条+新增50条）

-- 课件下载次数功能
DESCRIBE jol.course download_count;     -- int(11), Default: 0, Comment: 课程下载次数
DESCRIBE jol.course_order counted;       -- tinyint(1), Default: 0, Comment: 是否已计入下载次数

-- 平台积分充值与课件积分支付体系（V1.9）
-- users 表已转为 InnoDB 并新增 point 字段
SELECT ENGINE FROM information_schema.TABLES
 WHERE TABLE_SCHEMA='jol' AND TABLE_NAME='users';   -- 预期：InnoDB
DESCRIBE jol.users point;                            -- int(11), NOT NULL, Default: 0, Comment: 平台积分余额

-- 新建积分相关表
SHOW TABLES IN jol LIKE 'point_%';
-- 预期输出：point_card, point_log

-- point_card 关键字段与索引
DESCRIBE jol.point_card;
SHOW INDEX FROM jol.point_card WHERE Key_name='uk_card_no';        -- UNIQUE
SHOW INDEX FROM jol.point_card WHERE Key_name='idx_batch_no';
SHOW INDEX FROM jol.point_card WHERE Key_name='idx_status';
SHOW INDEX FROM jol.point_card WHERE Key_name='idx_redeem_user_id';

-- point_log 关键字段与索引
DESCRIBE jol.point_log;
SHOW INDEX FROM jol.point_log WHERE Key_name='idx_user_id';
SHOW INDEX FROM jol.point_log WHERE Key_name='idx_type';
SHOW INDEX FROM jol.point_log WHERE Key_name='idx_create_time';

-- 引擎与字符集检查
SELECT TABLE_NAME, ENGINE, TABLE_COLLATION
  FROM information_schema.TABLES
 WHERE TABLE_SCHEMA='jol' AND TABLE_NAME IN ('point_card','point_log');
-- 预期：ENGINE=InnoDB, TABLE_COLLATION=utf8mb4_*

-- 修复存量用户误跳 welcome（V2.2）
-- 执行迁移后，功能上线前注册的存量用户应全部标记为已领取（1），不再触发 login.php 补发分支
SELECT COUNT(*) FROM jol.users
 WHERE new_user_reward_claimed=0
   AND (reg_time < '2026-07-16 00:00:00' OR reg_time IS NULL);
-- 预期：0

-- 新客连续登录分批奖励（V2.4）
DESCRIBE jol.users login_streak;              -- int, NOT NULL, Default: 0
DESCRIBE jol.users last_login_reward_date;   -- date, NULL
DESCRIBE jol.users login_reward_status;      -- tinyint(1), NOT NULL, Default: 0
-- 存量用户全部排除：迁移后 login_reward_status=0 的记录应为 0
SELECT COUNT(*) FROM jol.users WHERE login_reward_status = 0;
-- 预期：0（迁移后存量全部为2；功能上线后新注册用户才为0）
```

---

### Step 2 — 代码部署

> 生产机路径：`/home/judge/src/web/`

**方式 A：Git 拉取（推荐）**
```bash
ssh judge@<生产机IP>
cd /home/judge/src
git pull origin my_oj
```

**方式 B：手动同步（Git 不同步时）**
```bash
cd /Users/zhangmaofan/PycharmProjects/hustoj
rsync -av --exclude='.git/' --exclude='.DS_Store' \
  trunk/web/submit.php \
  trunk/web/exam_do.php \
  trunk/web/exam_view.php \
  trunk/web/exam_result.php \
  trunk/web/template/syzoj/problem.php \
  trunk/web/include/problem.php \
  trunk/web/admin/exam_list.php \
  trunk/web/admin/exam_add.php \
  trunk/web/admin/exam_api.php \
  trunk/web/admin/exam_result.php \
  trunk/web/admin/exam_del.php \
  trunk/web/admin/menu2.php \
  trunk/web/admin/problem_add.php \
  trunk/web/admin/problem_add_page.php \
  trunk/web/admin/problem_list.php \
  trunk/web/lang/cn.php \
  judge@<生产机IP>:/home/judge/src/web/
```

---

### Step 3 — 刷新缓存

```bash
ssh judge@<生产机IP> "sudo service php-fpm restart"
```

---

### Step 4 — 冒烟测试（必做）

| # | 测试项 | 操作 | 预期 |
|---|--------|------|------|
| 1 | 选择题未登录提交 | POST submit.php problem_type=choice_single | 302 → loginpage |
| 2 | 选择题正确提交 | 登录后提交正确答案 | result=4, pass_rate=100 |
| 3 | 选择题错误提交 | 登录后提交错误答案 | result=6, pass_rate=0 |
| 4 | 编程题伪造choice | POST 编程题 + problem_type=choice_single | 走编程题流程（非choice分支） |
| 5 | solution 表写入 | 查最新 solution 记录 | result/pass_rate/exam_id 正确 |
| 6 | 创建试卷 | exam_add.php 保存 | ok:true + exam 表有记录 |
| 7 | 试卷删除 | exam_del.php | defunct='Y' |
| 8 | API 未登录 | POST exam_api.php | JSON 错误 |
| 9 | 学生交卷-全对 | exam_do.php 提交正确答案 | 正确率 100% + exam_result 有记录 |
| 10 | 学生交卷-全错 | exam_do.php 提交错误答案 | 正确率 0% |
| 11 | 重复交卷 | 同一试卷交卷两次 | exam_result 更新而非重复插入 |
| 12 | 积分字段默认值 | `SELECT MIN(point), MAX(point) FROM users;` | 历史用户 point 全部为 0 |
| 13 | 充值卡兑换 | 用户输入有效卡号卡密兑换 | users.point +10、point_card.status=1、point_log 新增一条 type=1 记录 |
| 14 | 重复兑换防御 | 已兑换卡密再次兑换 | 失败提示且不重复加积分 |
| 15 | 课件积分购买 | 用户余额足购买课件 | users.point 扣减、course_order 写入、point_log 新增 type=2 记录 |
| 16 | 积分不足购买 | 余额不足购买课件 | 拒绝并提示充值，不写订单 |
| 17 | 管理员手动调整 | 后台对用户加/扣分 | users.point 变化、point_log type=3 记录 remark |

---

## 回滚方案

### 代码回滚
```bash
cd /home/judge/src && git reset --hard <上一个稳定commit>
```

### 数据库回滚
SQL 文件末尾附有完整回滚语句，按逆序执行。

> ⚠️ exam / exam_attend / exam_problem / exam_result 四张表 DROP TABLE 需手动确认。

---

## 本次发布文件清单

### 新增文件
```
trunk/web/exam_view.php          # 学生端：试卷预览+答题入口
trunk/web/exam_do.php            # 学生端：交卷+判分+结果展示
trunk/web/exam_result.php        # 学生端：成绩查询
trunk/web/admin/exam_list.php    # 管理端：试卷列表
trunk/web/admin/exam_add.php     # 管理端：创建/编辑试卷
trunk/web/admin/exam_api.php     # 管理端：试卷 CRUD API
trunk/web/admin/exam_del.php     # 管理端：试卷软删除
trunk/web/admin/exam_result.php  # 管理端：学生成绩查看
```

### 修改文件
```
trunk/web/submit.php              # 选择题分支 + 登录校验 + type 防伪造
trunk/web/include/problem.php     # 题型筛选
trunk/web/admin/problem_add.php   # 添加题型/选项字段
trunk/web/admin/problem_add_page.php # 选择题选项录入 UI
trunk/web/admin/problem_list.php  # 题型筛选
trunk/web/admin/menu2.php         # 管理菜单
trunk/web/lang/cn.php             # 语言包
trunk/web/template/syzoj/problem.php # 前端选择题展示
trunk/web/course_get.php          # 添加下载计数逻辑
trunk/web/course_notify.php       # 支付回调添加下载计数
trunk/web/template/syzoj/course.php # 前端展示下载次数
```

### 数据库
```
db/V1.0_20260317_db_init.sql          # 基础OJ初始化
 db/V1.0_20260331_course_module.sql    # 课件商城模块
 db/V1.0_20260411_school_mode.sql      # 多学校隔离功能
 db/V1.1_20260419_choice_and_exam.sql  # 合并SQL：选择题 + 考试模块
 db/V1.2_20260501_courseware_preview_upgrade.sql  # 课件预览付费改造
db/V1.5_20260507_add_problem_level.sql       # 题目难度等级 level
db/V1.8_20260516_course_download_count.sql  # 课件下载次数功能
```

---

## V1.9 发布文件清单（平台积分充值与课件积分支付体系）

### 数据库
```
db/V1.9_20260607_point_payment.sql   # users 转 InnoDB + point 字段；新建 point_card / point_log 表
```

### 新增文件（随后续任务补充）
> 本次 V1.9 包含多个开发任务，下列文件清单将由后续任务完成时补全；
> 这里仅列出数据库变更，应用层文件清单以最终发布前的实际改动为准。

### 修改文件（随后续任务补充）
> 同上，待应用层任务完成后补全（如：用户菜单/我的积分页/课件支付改造/后台积分管理等）。
