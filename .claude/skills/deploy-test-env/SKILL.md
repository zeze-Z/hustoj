---
name: deploy-test-env
description: 把改动文件部署到 web-2204 测试虚机（/home/judge/src/web/）并清缓存。当需要同步代码到测试环境、传文件到虚机、部署后跑 php -l 自测时使用。
allowed-tools: Bash(bash deploy_test_env.sh:*), Bash(multipass:*), Bash(git status:*), Bash(git diff:*)
---

# 部署文件到 web-2204 测试环境

引擎 = 仓库根 `deploy_test_env.sh`（已内置 MSYS 路径转换规避、/tmp 中转、chown www-data、远端字节数校验、自动 opcache_reset）。**不要手动 multipass transfer**，除非脚本本身不可用（见文末兜底）。

## 步骤

1. **取改动清单**：`git status --porcelain` 或 `git diff --name-only`，只部署本次相关的 `trunk/web/` 下文件
2. **部署**（仓库根执行，可多文件）：
   ```bash
   bash deploy_test_env.sh [-f] trunk/web/xxx.php ...
   ```
   - 模板（`template/syzoj/`）或页面内容改动**必带 `-f`**（重启 php8.1-fpm 清 APCu 页面缓存）；纯 PHP 逻辑改动不用，脚本已自动 opcache_reset
   - **页面内容改动不生效的排查**：带 `$cache_time` 的页面（如 teacher_guide.php）内容缓存在 FPM 的 APCu（`include/cache_start.php`），CLI 的 `opcache_reset` / `apcu_clear_cache` 清不掉（不同 SAPI 内存独立）——响应末尾有 `<!-- cached -->` 注释即为缓存页，`-f` 重启 php8.1-fpm 即可（服务名是 `php8.1-fpm` 不是 `php-fpm`）
   - 脚本自带远端字节数校验：输出无 `!!` 且退出码 0 即部署成功
   - 环境变量可覆盖默认值：`REPO VM SUDOPW WEBROOT FPM`
3. **语法自测**（`php -l` 必须在虚机跑，本机无 PHP）：
   ```bash
   multipass exec web-2204 -- sudo -S php -l /home/judge/src/web/xxx.php <<< "judge"
   ```

## 环境

- 虚机 web-2204，web 根 `/home/judge/src/web`，sudo 密码 judge
- 虚机上校验/读取部署文件必须 sudo（属主 www-data，非 sudo 会 Permission denied 误判为文件不存在）
- 避免虚机→本机回传大文件（multipass 可能 0 字节/挂起）

## 脚本不可用时手动兜底

先传虚机 /tmp 再 mv。Windows Git Bash 两大坑：
- `multipass transfer` 源路径必须用相对路径（`/d/...` 会被 MSYS 转成 `D:/...`，盘符冒号被当实例名报错）
- 须先 `export MSYS2_ARG_CONV_EXCL="*" MSYS_NO_PATHCONV=1`（否则 exec 里的 `/tmp`、`/home` 路径被转成 `C:/...`，虚机内 mv/stat 找不到文件）
