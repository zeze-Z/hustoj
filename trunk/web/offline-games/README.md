# HUSTOJ 离线游戏

基于浏览器的编程教学游戏，支持离线使用。

## 🎮 包含游戏（17款）

- 🎨 AI猜猜画（ai_drawing_game）
- 🎈 气球打字（balloon_typing）
- 🔵 拼豆游戏（bead_game）
- ⏰ 时钟认读（clock_reading）
- 💻 编程启蒙（coding_game）
- 🎨 颜色匹配（color_match）
- 🐸 青蛙过河闯关（frog_typing）
- 🔢 猜数字游戏（guess_number）
- 🔤 成语接龙（idiom_chain）
- ⌨️ 二年级汉字打字小乐园（keyboard_game）
- 🧮 数学闯关（math_game）
- 🃏 卡片配对（memory_game）
- 💣 扫雷（minesweeper）
- 🔢 数字华容道（number_puzzle）
- 🧩 拼图游戏（puzzle_game）
- 🧠 序列记忆（sequence_memory）
- 🐍 贪吃蛇（snake）

## 🚀 快速开始

### 1. 获取授权码

1. 登录 OJ 网站（aioj.top）
2. 进入「更多」页面，找到「离线游戏」
3. 点击积分兑换（消耗 50 积分），填写**学校名称** + **机房名称**
4. 兑换成功后获得 JSON 授权码，同时通过网盘链接下载离线游戏包

### 2. 激活游戏

1. 解压游戏包
2. 打开 `games/activate.html`，粘贴授权码，点击「验证授权码」
3. 验证通过后点击「⬇️ 下载 license.js」，将下载的文件放入离线包 `js` 目录（与 `auth.js` 同级）
4. 教师机本机已同步激活，打开 `index.html` 即可进入游戏

### 3. 开始游戏

打开 `index.html`，选择游戏开始！（未激活时会自动跳转到激活页）

### 📦 分发到学生机（推荐）

1. 在教师机 `games/activate.html` 验证授权码后，下载 `license.js` 放入离线包 `js` 目录（与 `auth.js` 同级）
2. 将整个离线包拷贝到学生机
3. 学生双击 `index.html` 即可直接进入游戏，无需任何操作

> 原理：页面通过 `<script src="js/license.js">` 加载本地授权文件（不受浏览器 file:// 限制），`js/auth.js` 自动验签后放行；验证通过不写入浏览器，续期换包后新 `license.js` 直接生效。

### 🔄 备用激活方式

更换浏览器或清除浏览器数据会导致浏览器内保存的授权丢失，此时有两种恢复方式：

- **推荐**：重新打开 `games/activate.html`，粘贴同一授权码再次验证即可（授权码绑定学校+机房，同机房可复用）
- **备用**：在 `games/activate.html` 页面按提示，将授权内容保存为 `license.dat` 文件，放到离线包根目录（与 `index.html` 同级）。`js/auth.js` 会在浏览器内未命中授权时自动读取该文件兜底

## 📁 目录结构

```
hustoj-games/
├── index.html             # 游戏主页
├── games/                 # 17款游戏页面 + activate.html 激活页
└── js/                    # JavaScript文件（auth.js 授权校验、license.js 授权文件）
```

> 以上为离线包（zip）内的实际内容；本 README 及用户手册、快速入门等文档位于源码仓库，不随离线包分发。

> 说明：`js/license.js`（授权包装文件，在 `games/activate.html` 验证授权码后下载获得）放入后，整包拷贝到学生机即可双击 `index.html` 直接使用；授权版离线包在打包时已自动内置该文件。

## 📚 文档

- [用户手册_离线游戏激活指南.md](用户手册_离线游戏激活指南.md) - 详细使用说明
- [快速入门指南.md](快速入门指南.md) - 3分钟快速上手

## 🔐 授权机制

- 采用RSA-2048数字签名保护
- 每个授权码绑定特定学校和机房
- 有效期为1年
- 支持离线验证（验签在本地浏览器完成）

## 🛠️ 服务器部署（管理员）

### 私钥管理

- 签名私钥**必须存放在 web 根目录之外**，约定路径：`/home/judge/etc/offline_games/private_key.pem`
- 生成方式：
  ```bash
  openssl genrsa -out private_key.pem 2048
  openssl rsa -in private_key.pem -pubout -out public_key.pem
  ```
- ⚠️ **严禁将私钥放在 web 目录下**（否则可能被直接下载）。仓库内 `offline-games/admin/` 仅保留公钥 `public_key.pem`（前端验签用，公开无风险）

### 授权生成

- `admin/generate_license.py` 与 `build_package.sh` 均支持 `--private-key` 参数指定私钥路径
- 网站兑换入口 `trunk/web/point_goods_redeem.php`（原 offline_game_redeem.php，V2.9 起通用化）通过配置常量 `OG_KEY_DIR`（默认 `/home/judge/etc/offline_games`）显式传入私钥路径

### Web 服务器防护

`offline-games/` 目录内容仅通过网盘 zip 分发，无需公网直接访问：

- **Apache**：`trunk/web/offline-games/.htaccess` 已配置拒绝访问，无需额外操作
- **Nginx**：需在站点配置中自行添加：
  ```nginx
  location /offline-games/ {
      return 404;   # 或 deny all;
  }
  ```

## 🌐 离线使用

授权码获取需联网（网站积分兑换），激活与游戏均完全离线运行，无需连接互联网。教师机验证一次并下载 `license.js` 放入 `js` 目录后，整包拷贝到学生机即可双击 `index.html` 直接使用。

## 📞 技术支持

- **QQ**：2326077585
- **邮箱**：[填写客服邮箱]

## 📄 许可证

Copyright © 2026 HUSTOJ. All rights reserved.
