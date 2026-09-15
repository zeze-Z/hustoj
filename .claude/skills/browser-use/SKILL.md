---
name: browser-use
description: Automates browser interactions for web testing, form filling, screenshots, and data extraction. Use when the user needs to navigate websites, interact with web pages, fill forms, take screenshots, or extract information from web pages.
allowed-tools: Bash(browser-use:*)
---

# Browser Automation with browser-use CLI

The `browser-use` command provides fast, persistent browser automation via CDP (Chrome DevTools Protocol). It maintains browser sessions across commands, enabling complex multi-step workflows.

## Prerequisites

`browser-use` connects to an **already-running Chrome** instance via CDP port 9222. It does NOT launch its own browser. You must start Chrome with remote debugging enabled first.

### Step 1: Start Chrome with remote debugging (每次用 browser-use 前确保在跑)

```bash
# 检查端口是否已占用
curl -s http://localhost:9222/json/version && echo "Chrome 已在跑，跳过启动" || {
  # 跨平台启动独立 Chrome 实例
  if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "cygwin" || "$OSTYPE" == "win32" ]]; then
    # Windows (Git Bash / MSYS2)
    "$LOCALAPPDATA/Google/Chrome/Application/chrome.exe" \
      --remote-debugging-port=9222 \
      --user-data-dir="$HOME/browseruse-chrome-profile" \
      --no-first-run --no-default-browser-check about:blank &
  elif [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
      --remote-debugging-port=9222 \
      --user-data-dir="$HOME/browseruse-chrome-profile" \
      --no-first-run --no-default-browser-check about:blank &
  else
    # Linux
    google-chrome \
      --remote-debugging-port=9222 \
      --user-data-dir="$HOME/browseruse-chrome-profile" \
      --no-first-run --no-default-browser-check about:blank &
  fi
  sleep 2
  curl -s http://localhost:9222/json/version
}
```

- 独立 profile 目录：`~/browseruse-chrome-profile`（无登录态/书签，与日常 Chrome 隔离）
- 验证端口：`curl -s http://localhost:9222/json/version` 应返回 JSON

### Step 2: Confirm Chrome is listening

```bash
curl -s http://localhost:9222/json/version
```

Expected output:
```json
{
   "Browser": "Chrome/xxx.x.xxxx.xx",
   "Protocol-Version": "1.3",
   ...
}
```

## Core Workflow

所有 browser-use 调用都使用 **Python heredoc 语法**：

```bash
browser-use <<'PY'
ensure_real_tab()
goto_url("https://example.com")
wait_for_load()
print(page_info())
PY
```

**不要使用** `browser-use open <url>`、`browser-use state`、`--browser chromium` 等语法——这些在当前版本不存在。

## Helper Functions

### Navigation
- `goto_url(url)` — 导航到 URL
- `new_tab(url)` — 新标签页打开
- `back()` — 后退

### Page Info
- `page_info()` — 返回当前页面 URL、标题、视口大小等
- `capture_screenshot()` — 截图（base64）
- `capture_screenshot("path.png")` — 截图保存到文件

### Interaction
- `click_at_xy(x, y)` — 点击坐标
- `fill_input(selector, text)` — 填充表单输入框
- `type_text(text)` — 在当前焦点元素中输入文字
- `press_key(key)` — 按键（如 `"Enter"`, `"Tab"`）
- `scroll(x, y)` — 滚动

### Tab Management
- `list_tabs()` — 列出所有标签页
- `switch_tab(target)` — 切换标签页
- `close_tab(target)` — 关闭标签页

### JavaScript & CDP
- `js(code)` — 执行 JavaScript 并返回结果
- `cdp(method, ...)` — 直接调用 CDP 命令
- `wait_for_element(selector)` — 等待元素出现

### Complete Reference
```bash
browser-use skill show
```

## Common Patterns

### Take a screenshot of a page
```bash
browser-use <<'PY'
ensure_real_tab()
goto_url("http://localhost/page.php")
wait_for_load()
print(page_info())
capture_screenshot("/tmp/screenshot.png")
PY
```

### Fill a form and submit
```bash
browser-use <<'PY'
ensure_real_tab()
goto_url("http://localhost/login.php")
wait_for_load()
fill_input("#username", "admin")
fill_input("#password", "admin123")
click_at_xy(400, 300)  # 点击登录按钮（先用 page_info() 确认坐标）
wait_for_load()
print(page_info())
PY
```

### Multiple pages sequentially
```bash
browser-use <<'PY'
ensure_real_tab()
goto_url("http://localhost/page1.php")
wait_for_load()
print(page_info())
goto_url("http://localhost/page2.php")
wait_for_load()
print(page_info())
PY
```

## Global Options

| Option | Description |
|--------|-------------|
| `--session NAME` | Use named session (default: "default") |

## Troubleshooting

- **`active browser connections — 0`** → 独立 Chrome 没在跑，用上面的命令重新启动
- **`chrome running` 但连不上** → 确认 9222 端口在监听：`netstat -ano | grep 9222`（Windows）或 `lsof -i :9222`（Mac/Linux）
- **daemon 相关错误 / CDP `_send` 报错** → `browser-use --reload` 重启 daemon 后重试
- **Chrome 弹出 "Allow remote debugging?"** → 使用独立 Chrome 实例（带 `--remote-debugging-port=9222`）不会弹此窗；如果弹了点 Allow 即可
- **Windows 上 Chrome 跳到 Microsoft Store** → 必须用完整路径启动，不要用开始菜单快捷方式

## Cleanup

```bash
browser-use close
```

## JS heredoc 写法约束（2026-09-14 实测踩坑）

`browser-use <<'PY'` 里的 `js("...")` 是最易翻车的工具。以下约束避开本次撞过的语法/编码坑：

### 1. JS 必须写成单行，禁止 for 循环 + 花括号嵌套

heredoc 里多行 JS 的花括号与 Python/-shell 转义互相干扰，报 `SyntaxError: Unexpected token }` / `Illegal return statement`。

```python
# ❌ 错：for 循环体花括号 + 多行，必断
info = js("""
var arr = Array.from(document.querySelectorAll('a,button'));
var out = [];
for(var i=0;i<arr.length;i++){ var t=arr[i].textContent; out.push(i+':'+t); }
return out.join(',');
""")

# ✅ 对：单行 + 链式 map/filter/join 替代循环
info = js("return Array.from(document.querySelectorAll('a,button')).map(function(e,i){return i+':'+e.textContent.trim();}).join(',');")
```

### 2. `js()` 表达式内禁用裸 `return`

`js()` 把表达式包进 `(function(){ ... })()`，所以 `return` 只在函数体内部合法。**整段 JS 必须是一个能被函数体包裹的表达式**——用 `return ...` 收尾可以，但不要在语句中间出现裸 return。最稳的写法：单行 `return <表达式>;`。

### 3. JS 字符串内禁用 `\n` 转义

heredoc `'PY'` 下 `\n` 不会被解释成换行，而是变成字面 `\n` 进 JS 字符串，触发 `SyntaxError: Invalid or unexpected token`。需要换行分隔用 `','` 或 `'##'` 等 ASCII 分隔符，回到 Python 再 `split`。

### 4. 返回值含中文/特殊字符 → 用 `str()` 包裹并截断

JS 返回含未配对代理对（surrogate）的文本会让 browser-harness 抛 `UnicodeEncodeError: surrogates not allowed`。截图文件名也避免含中文路径。防御写法：

```python
print("RESULT:" + str(info)[:500])
```

### 5. 表单提交后不要立即 `page_info()`

`HTMLFormElement.prototype.submit.call(f)` 触发导航，此时 `document.documentElement` 瞬时为 null，`page_info()` 报 `Cannot read properties of null (reading 'scrollWidth')`。先 `wait_for_load()` 再 `page_info()`。

## HUSTOJ 表单交互适配（项目专属，2026-09-14 实测）

HUSTOJ 的登录页和管理后台表单有几个反自动化陷阱，`fill_input` + `click_at_xy` 常规套路会静默失败（点了按钮但不跳转、session 没建立）。

### 1. 登录必须先 `jsMd5()` 再提交

登录表单 `#login` 的 `onsubmit="return jsMd5();"` 会在提交前把密码字段 MD5 加密。绕过 onsubmit 直接 `form.submit()` 会提交明文密码，与数据库存的 MD5 不匹配 → 登录失败但无报错（停在 loginpage.php）。

```python
# ✅ 正确登录套路
result = js("""
var f = document.querySelector('#login');
f.querySelector('#username').value = 'admin';
f.querySelector('#password').value = 'admin123';
jsMd5();  // 关键：触发密码 MD5 加密
HTMLFormElement.prototype.submit.call(f);  // 见下条：绕过 name=submit 覆盖
return 'submitted';
""")
```

虚机内 **curl 登录同理**：必须先 `md5sum` 密码再 POST，否则 PHPSESSID 不写入 cookie jar、session 建立失败（响应是 `<script>window.top.location.href='admin';</script>` 但后续请求判定未登录）：
```bash
PWDMD5=$(echo -n "admin123" | md5sum | awk '{print $1}')
curl -s -c $COOKIE -d "user_id=admin&password=$PWDMD5" "http://127.0.0.1/login.php"
```

### 2. `name="submit"` 覆盖 `form.submit` → 用原型方法

HUSTOJ 登录按钮是 `<button name="submit" type="submit">`，DOM 里 `form.submit` 被这个按钮元素覆盖成 `undefined`/元素引用，调用 `form.submit()` 报 `submit is not a function`。用原型方法绕过：

```python
HTMLFormElement.prototype.submit.call(form)  # 而非 form.submit()
```

### 3. 多表单页面：`fill_input` 选择器必须限定表单范围

登录页、课程详情页常带「联系我们」表单 `#contactForm`（含 postkey/title/email 字段）与目标表单并存。`fill_input("input[name='user_id']")` 会匹配到第一个表单的同名字段（联系表单也有 input），填错地方。`document.querySelector('form')` 同样取到的是第一个表单。

**解决**：用 `js()` 按 form id 精确选：
```python
# 登录表单是 #login，联系表单是 #contactForm
js("document.querySelector('#login input[name=user_id]').value = 'test'")
# 管理后台调整积分表单：按 form method=POST + 含 input[name=course_id] 定位
js("var f=document.querySelectorAll('form')[1]; HTMLFormElement.prototype.submit.call(f);")
```

### 4. httponly cookie（如 `course_referrer_id`）JS 删不掉

分享归因 cookie `course_referrer_id` 是 httponly + samesite=Lax，`document.cookie` 读不到、JS `document.cookie=` 也覆盖不了。CDP `Network.deleteCookies` 传 `{sessionId}` 会报 `Message may have string 'sessionId' property`。

**测"无 cookie/无分享链接"场景**：`cdp("Network.clearBrowserCookies")` 清全部 cookie（代价：丢失登录态，需重新登录）。比 `deleteCookies` 单删一个可靠。

```python
cdp("Network.clearBrowserCookies")  # 清全部，之后重新登录就是干净无归因状态
```

### 5. 提取表单 postkey 要限定到目标表单

页面有多个表单时 `grep -oP 'name="postkey" value="\K[^"]*'` 会取到第一个（往往是联系表单的），提交后 `check_post_key.php` 校验失败。按表单 action 上下文提取：

```bash
# ❌ 错：取到 contactForm 的 postkey
PK=$(curl ... | grep -oP 'name="postkey" value="\K[^"]*' | head -1)
# ✅ 对：取 point_adjust 表单 action 附近的 postkey
PK=$(curl ... | grep -A2 'action="point_adjust' | grep -oP 'value="\K[^"]*' | head -1)
```

### 6. 找按钮坐标的可靠套路

`click_at_xy` 需要坐标，但按钮可能被 Semantic UI 包裹多层。先 JS 算中心点再点击：

```python
center = js("var b=document.querySelector('#login button[type=submit]'); var r=b.getBoundingClientRect(); return Math.round(r.x+r.width/2)+','+Math.round(r.y+r.height/2);")
# center = "685,327"
click_at_xy(685, 327)
```

列页面所有可点元素（找购买/获取按钮）：
```python
js("return Array.from(document.querySelectorAll('a,button')).map(function(e,i){var t=e.textContent.trim().substring(0,25);var h=(e.href||'').substring(0,60);return i+':'+t+'|'+h;}).filter(function(s){return s.indexOf('course_get')>=0;}).join('##');")
```
