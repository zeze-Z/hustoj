# HUSTOJ 项目 Claude Code 指南

## 项目信息
- **路径**: `/Users/zhangmaofan/PycharmProjects/hustoj`
- **分支**: `my_oj`
- **模板**: syzoj

## 目录结构
```
trunk/web/
├── admin/           # 管理后台 (menu2.php 是菜单)
├── include/          # 核心函数库 (school.php)
├── template/syzoj/   # 前端模板
└── lang/            # 语言包 (cn.php, en.php)
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
