# OJ 功能发布步骤

## 当前版本：V1.2（2026-04-18）

---

## 版本历史

| 版本 | 日期 | 功能 |
|------|------|------|
| V1.0 | — | 初始版本（基础OJ） |
| V1.1 | 2026-04-18 | 考试模块（组卷+答题） |
| V1.2 | 2026-04-18 | 选择题功能（题型+选项+判题+展示） |

---

## 发布前检查清单

- [ ] 所有 P0 测试用例通过
- [ ] 本地 `git push` 成功
- [ ] 确认生产环境数据库备份
- [ ] 确认生产机代码目录

---

## 发布流程（两个需求合并发布）

### Step 1 — 数据库变更（按顺序执行）

> 两个需求的 SQL 独立，互不影响，按以下顺序执行：

```bash
# 1.1 考试模块建表（V1.1 先执行，因为 V1.2 依赖 solution.exam_id）
mysql -u root -p jol < db/V1.1_20260418_exam_feature.sql

# 1.2 选择题字段扩展（V1.2）
mysql -u root -p jol < db/V1.2_20260418_choice_feature.sql
```

**验证：**
```sql
-- 确认新增表存在
SHOW TABLES IN jol LIKE 'exam%';
-- 确认 problem 表新字段
DESCRIBE jol.problem problem_type;
DESCRIBE jol.problem options;
-- 确认 solution 表 exam_id
DESCRIBE jol.solution exam_id;
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
# 本地打包待发布文件
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
  trunk/web/lang/cn.php \
  judge@<生产机IP>:/home/judge/src/web/
```

---

### Step 3 — 验证部署

```bash
# 检查文件时间戳
ssh judge@<生产机IP> "ls -la /home/judge/src/web/submit.php /home/judge/src/web/exam_do.php"

# 刷新缓存（如有 opcache）
ssh judge@<生产机IP> "sudo service php-fpm restart"
```

---

### Step 4 — 冒烟测试（必做）

| # | 测试项 | 命令/操作 | 预期 |
|---|--------|----------|------|
| 1 | 选择题未登录提交 | `curl -X POST url/submit.php -d "problem_type=choice_single"` | 302 → loginpage |
| 2 | 选择题正确提交 | 登录后提交正确答案 | result=4, pass_rate=100 |
| 3 | 选择题错误提交 | 登录后提交错误答案 | result=6, pass_rate=0 |
| 4 | solution 表写入 | `mysql -e "SELECT result FROM solution ORDER BY solution_id DESC LIMIT 1"` | 有记录 |
| 5 | 试卷列表页 | 管理员访问 exam_list.php | 显示列表 |
| 6 | 创建试卷 | exam_add.php 填必填保存 | ok:true |
| 7 | API 未登录 | `curl -X POST exam_api.php` | JSON 错误 |
| 8 | 学生交卷 | exam_do.php 提交选择题 | exam_result 有记录 |

---

## 回滚方案

### 代码回滚
```bash
# 方式 A: Git
cd /home/judge/src && git reset --hard <上一个稳定commit>

# 方式 B: 恢复旧文件
rsync /path/to/backup/submit.php judge@<生产机>:/home/judge/src/web/
```

### 数据库回滚
```bash
# 查看 SQL 回滚语句（每个 Vx.x 文件末尾附有回滚 SQL）
mysql -u root -p jol < db/V1.x_xxx.sql 回滚部分
```

> ⚠️ V1.1 的 exam/exam_attend/exam_problem/exam_result 四张表 DROP TABLE 操作需**手动确认**，不可直接执行。

---

## 本次发布文件清单

### 新增文件
```
trunk/web/exam_view.php          # 学生端：试卷预览+答题入口
trunk/web/exam_do.php           # 学生端：交卷+判分+结果展示
trunk/web/exam_result.php       # 学生端：成绩查询
trunk/web/admin/exam_list.php   # 管理端：试卷列表
trunk/web/admin/exam_add.php    # 管理端：创建/编辑试卷
trunk/web/admin/exam_api.php    # 管理端：试卷 CRUD API
trunk/web/admin/exam_del.php    # 管理端：试卷软删除
trunk/web/admin/exam_result.php # 管理端：学生成绩查看
```

### 修改文件
```
trunk/web/submit.php             # 选择题处理分支 + 登录校验 + type 防伪造
trunk/web/include/problem.php    # problem_list.php 题型筛选
trunk/web/include/my_func.inc.php # 支持编程题考试关联
trunk/web/admin/problem_add.php  # 添加题型/选项字段
trunk/web/admin/problem_add_page.php # 选择题选项录入 UI
trunk/web/admin/problem_list.php  # 题型筛选
trunk/web/admin/menu2.php       # 管理菜单
trunk/web/lang/cn.php           # 语言包
trunk/web/template/syzoj/problem.php # 前端选择题展示
```

### 数据库
```
db/V1.1_20260418_exam_feature.sql  # 考试模块建表 + solution.exam_id
db/V1.2_20260418_choice_feature.sql # problem.type/options/answer 扩展
```
