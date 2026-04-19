# HUSTOJ 项目 Claude Code 指南

## 项目信息
- **路径**: `/Users/zhangmaofan/PycharmProjects/hustoj`
- **分支**: `my_oj`
- **模板**: syzoj

## 目录结构
```
# 根目录结构
├── db/                  # 数据库脚本归档目录（SQL变更必须归档到此，支持幂等）
│   ├── course_module_init.sql           # 课程模块初始化SQL
│   ├── V1.1_20260419_choice_and_exam.sql # 选择题+考试模块合并SQL
│   └── RELEASE_STEPS.md                 # 发布流程与冒烟测试
└── trunk/web/
    ├── admin/           # 管理后台 (menu2.php 是菜单)
    │   ├── exam_del.php  # 新增：试卷软删除功能
    │   └── exam_result.php # 修改：管理端成绩页，支持编程题自动算分
    ├── include/          # 核心函数库 (school.php)
    ├── template/syzoj/   # 前端模板
    ├── lang/            # 语言包 (cn.php, en.php)
    ├── exam_do.php      # 修改：答题提交逻辑，支持编程题关联考试
    └── exam_result.php  # 新增：学生端成绩查询页，支持编程题自动算分
```

## 常用 Git 命令
```bash
git status
git add -A && git commit -m "提交信息"
git push
git pull
```

## 代码规范

### 管理后台 (admin/)
遵循 `admin/README.md` 规范：
- 页面: list/add/edit/del/df_change
- 菜单: `menu2.php` (syzoj 模板)
- 架构: frameset (target="main")
- 权限: `$_SESSION[$OJ_NAME.'_'.'administrator']`
- CSRF: `check_post_key.php` / `csrf.php`
- SQL: 参数化查询
- XSS: `htmlentities($str, ENT_QUOTES, 'UTF-8')`

### 语言变量
在 `lang/cn.php` 和 `lang/en.php` 中定义：
```php
$MSG_SCHOOL = "学校";
$MSG_ADD = "添加";
$MSG_EDIT = "编辑";
```

## 数据库
- 数据库名称：jol
- school 表: 学校信息
- users.school_id: 用户所属学校
- problem.school_id / is_public: 题目归属
- contest.school_id / is_public: 比赛归属

### SQL 变更归档规范（强制）

**所有涉及 SQL 的变更必须归档到 `db/` 目录下，不得遗漏。**

1. **文件命名**：`V{版本号}_{日期}_{功能描述}.sql`
   - 示例：`V1.1_20260419_choice_and_exam.sql`
2. **SQL 必须支持幂等执行**（可重入），同一文件执行多次不报错：
   - `ADD COLUMN` → 用 `information_schema.COLUMNS` 判断列是否存在，存在则 SKIP
   - `ADD INDEX / UNIQUE KEY` → 用 `information_schema.STATISTICS` 判断索引是否存在
   - `MODIFY COLUMN` → 天然幂等，直接执行
   - `CREATE TABLE` → 使用 `IF NOT EXISTS`
3. **文件末尾附回滚 SQL**（注释形式），按逆序排列
4. **更新 `db/RELEASE_STEPS.md`**：同步发布流程和冒烟测试用例

## 配置文件
- `include/db_info.inc.php`: 数据库配置
- `include/school.php`: 学校管理函数

## 测试环境操作规范
本地编码完成后，必须将代码同步到测试虚机web-2204进行自测验证：

### 浏览器测试
访问测试虚机地址进行系统测试：
- 普通用户：zezhang / zezhang123
- 管理员：admin / admin123

### 虚机操作
- 虚机执行命令格式：`multipass exec web-2204 -- sudo -S [shell命令] <<< "judge"`
- 虚机上OJ部署路径：/home/judge/src/web/
- sudo密码：`judge`
- 文件传输到/tmp：`multipass transfer [本地文件路径] web-2204:/tmp/[文件名]`
- 从/tmp复制到目标：`multipass exec web-2204 -- sudo -S cp /tmp/[文件名] /home/judge/src/web/[目标路径] <<< "judge"`
