# OJ 功能发布步骤

## 当前版本：V1.6（2026-05-11）

---

## 版本历史

| 版本 | 日期 | 功能 |
|------|------|------|
| V1.0 | 2026-03-17 | 初始版本（基础OJ） |
| V1.0 | 2026-03-31 | 课件商城模块 |
| V1.0 | 2026-04-11 | 多学校隔离功能 |
| V1.1 | 2026-04-19 | 选择题功能 + 考试模块（合并发布） |
| V1.2 | 2026-05-01 | 课件预览付费改造 |
| V1.3 | 2026-05-05 | 删除提取码字段（百度网盘→金山文档） |
| V1.4 | 2026-05-07 | 题目增加level字段（难度等级1-5） |
| V1.6 | 2026-05-11 | 注册功能优化：用户角色选择、邮箱唯一性校验、教师/学生区分通知 |

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

# 5. 课件预览付费改造
mysql -u root -p jol < db/V1.2_2026-05-01_courseware_preview_upgrade.sql

# 6. 题目难度等级
mysql -u root -p jol < db/V1.5_2026-05-07_add_problem_level.sql

# 7. 注册功能优化
mysql -u root -p jol < db/V1.6_2026-05-11_register_role.sql
```

**验证：**
```sql
-- 基础表
SHOW TABLES IN jol LIKE 'problem';
SHOW TABLES IN jol LIKE 'solution';

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
```

### 数据库
```
db/V1.0_20260317_db_init.sql          # 基础OJ初始化
 db/V1.0_20260331_course_module.sql    # 课件商城模块
 db/V1.0_20260411_school_mode.sql      # 多学校隔离功能
 db/V1.1_20260419_choice_and_exam.sql  # 合并SQL：选择题 + 考试模块
 db/V1.2_20260501_courseware_preview_upgrade.sql  # 课件预览付费改造
db/V1.5_20260507_add_problem_level.sql       # 题目难度等级 level
```
