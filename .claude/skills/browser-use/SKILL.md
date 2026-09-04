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
