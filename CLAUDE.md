# CLAUDE.md

本文件为 Claude Code (claude.ai/code) 在此代码库中工作时提供指导。

## 项目概述

HUSTOJ 是一个广泛使用的开源在线评测系统，用于程序设计竞赛、算法训练和代码自动评测。它具有基于 PHP 的 Web 前端和 C/C++ 编写的评测后端。

**关键技术栈：**
- 前端：PHP、Nginx、MySQL/MariaDB
- 后端：C/C++（judge_client、judged）
- 模板：多种主题（syzoj 是默认和主要模板）
- 容器支持：Docker/Podman 用于安全评测

## 重要开发规则

### 模板修改限制
**关键：只能修改 `syzoj` 模板。不要修改任何其他模板（mdui、sweet、sidebar、bs3、bshark 等）。**

用户仅使用 syzoj 模板作为学生端界面。所有修改应仅限于 `trunk/web/template/syzoj/` 目录。

### 当前分支
- 活跃开发分支：`my_oj`
- 主分支：`master`

## 目录结构

```
hustoj/
├── trunk/
│   ├── core/              # 评测后端（C/C++）
│   │   ├── judge_client/  # 评测客户端守护进程
│   │   ├── judged/        # 评测中心
│   │   └── sim/           # 沙箱隔离
│   ├── install/           # 安装脚本
│   └── web/               # Web 前端（PHP）
│       ├── admin/         # 管理后台
│       ├── include/       # 核心 PHP 库
│       ├── template/      # 前端主题
│       │   └── syzoj/    # 主要模板 - 只修改这个
│       └── upload/        # 用户上传
├── docs/                  # 文档
├── wiki/                  # 技术文档
└── README.md              # 项目主 README
```

## 关键配置文件

安装后，重要配置文件位于：

- `/home/judge/etc/judge.conf` - 评测服务器配置
- `/home/judge/src/web/include/db_info.inc.php` - Web 应用设置
- `/etc/nginx/sites-enabled/default` - Nginx 配置

## 编译后端

```bash
cd trunk/core/judge_client
make
cd ../judged
make
```

## 安装

对于 Ubuntu 22.04+/24.04/Debian 12（推荐）：

```bash
wget http://dl.hustoj.com/install.sh
sudo bash install.sh
```

安装后，使用用户名 `admin` 注册即可自动获得管理员权限。

## 常用维护命令

```bash
# 备份
sudo bash /home/judge/src/install/bak.sh

# 更新/修复
sudo bash /home/judge/src/install/fixing.sh

# 恢复备份
sudo bash /home/judge/src/install/restore.sh <backup_file>

# 恢复nginx配置
sudo bash /home/judge/src/install/fix_nginx_conf.sh
```

## 近期功能（2026年）

- 基于 AI 的题目分类和生成
- 异步 AI API 集成
- 基于 GUI 的 AI 辅助题目创建
- Podman 容器化支持
- PHP 8.4 兼容性

## 项目背景与进行中的工作

本仓库有一个持续的推广和优化计划，重点关注：

### 已完成的优化（在 my_oj 分支上）
1. 首页游客访问修复 - 访客无需登录即可查看首页
2. 游客可访问 more.php
3. 首页添加功能介绍轮播图（4张幻灯片：海量题库、秒级判题、学习路径、竞赛活动）
4. 轮播图 UI 修复（z-index、按钮点击事件）

### 待优化项（来自推广计划）
- 用户体验：微信/钉钉登录、游客体验模式、PWA 支持
- 内容：分级题库、趣味编程题、学习路径
- 功能：班级管理、作业系统、错题本、AI 提示
- 推广：自定义品牌、学校定制页面、邀请码
- 技术：一键部署、离线安装包、健康检查页面

## 开发工作流

1. 始终在 `my_oj` 分支上工作
2. 前端修改只改动 `trunk/web/template/syzoj/` 中的文件
3. 提交前本地测试修改
4. 提交信息中引用优化进度

## 重要链接

- [常见问题](http://hustoj.com)
- [线上地址](https://aioj.top/)
- [Wiki](https://zhblue.github.io/hustoj)
- [问题追踪](https://github.com/zhblue/hustoj/issues)
- 官方 QQ 群：23361372
