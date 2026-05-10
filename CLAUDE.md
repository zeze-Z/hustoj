# HUSTOJ 项目指南

## 基础信息

- 路径: `/Users/zhangmaofan/PycharmProjects/hustoj`
- 分支: `my_oj`
- 模板: syzoj
- 数据库: jol

## 目录结构

```
├── db/              # SQL变更归档
└── trunk/web/
    ├── admin/       # 管理后台（考试相关功能：exam_*.php）
    ├── include/     # 核心函数库（school.php 学校隔离逻辑）
    ├── template/    # 前端模板
    ├── lang/        # 多语言包
    └── exam_*.php   # 学生端考试功能
```

## 核心规范

### SQL变更（强制）

- 统一归档到`db/`目录，命名格式：`V{版本号}_{日期}_{功能描述}.sql`
- 使用标准DDL语法，兼容 MySQL 8.0.28
- 文件末尾附回滚SQL
- 同步更新`db/RELEASE_STEPS.md`发布流程

### 代码规范

- 管理后台权限控制统一使用`$_SESSION[$OJ_NAME.'_'.'administrator']`
- 管理后台菜单配置文件是`admin/menu2.php`（不是menu.php，避免踩坑）
- 管理后台表单提交必须引入`check_post_key.php`做CSRF校验
- SQL必须使用参数化查询，避免注入
- XSS防护使用`htmlentities($str, ENT_QUOTES, 'UTF-8')`

### 权限控制

- 游客白名单统一配置在`template/syzoj/header.php`，仅允许访问首页、题目列表、新闻等只读页面
- 非白名单页面自动跳转到登录页，登录后返回原页面

## 测试环境

- 测试虚机：web-2204，部署路径：/home/judge/src/web/，sudo密码：judge
- 虚机执行命令格式：`multipass exec web-2204 -- sudo -S [shell命令] <<< "judge"`
- 测试账号：普通用户zezhang/zezhang123，管理员admin/admin123
- 文件同步：先传文件到虚机/tmp目录`multipass transfer [本地文件路径] web-2204:/tmp/[文件名]`，再mv到部署路径
- 代码更新生效：执行`php -r 'opcache_reset();'`即可，无需重启php-fpm服务（避免服务名找不到的坑）

