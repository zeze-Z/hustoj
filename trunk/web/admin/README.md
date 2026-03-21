# 管理后台开发指引

## 目录结构

```
trunk/web/admin/
├── admin-header.php    # 公共头部（权限检查、加载资源）
├── admin-footer.php    # 公共尾部
├── menu.php           # 左侧菜单（旧版，bs3模板）
├── menu2.php          # 左侧菜单（syzoj模板，当前使用）
├── ajax.php           # AJAX 处理
├── xxx_list.php       # 列表页
├── xxx_add.php        # 新增页
├── xxx_edit.php       # 编辑页
├── xxx_del.php        # 删除处理
└── xxx_df_change.php  # 状态切换（如启用/禁用）
```

> ⚠️ **重要**：syzoj 模板使用 `menu.php` 作为后台菜单入口，新增菜单请同时修改 `menu.php` 和 `menu2.php`！

## 页面模板

### 1. 列表页 (xxx_list.php)

```php
<?php
require("admin-header.php");           // 权限检查、加载资源
require_once("../include/set_get_key.php");  // CSRF 密钥

// 权限检查（可选，admin-header 已包含基础权限）
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

if (isset($OJ_LANG)) {
    require_once("../lang/$OJ_LANG.php");
}
?>

<title><?php echo $MSG_xxx . "-" . $MSG_LIST ?></title>
<hr>
<center><h3><?php echo $MSG_xxx . "-" . $MSG_LIST ?></h3></center>

<div class="padding">
    <!-- 搜索表单 -->
    <form action="xxx_list.php" method="get" class="form-inline">
        <input type="text" name="keyword" class="form-control" placeholder="关键词">
        <button type="submit" class="btn btn-primary"><?php echo $MSG_SEARCH ?></button>
    </form>

    <!-- 列表表格 -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>名称</th>
                <th><?php echo $MSG_OPERATOR ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row) { ?>
            <tr>
                <td><?php echo $row['id'] ?></td>
                <td><?php echo htmlentities($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a href="xxx_edit.php?id=<?php echo $row['id'] ?>" class="btn btn-sm btn-primary"><?php echo $MSG_EDIT ?></a>
                    <a href="xxx_del.php?id=<?php echo $row['id'] ?>&getkey=<?php echo $_SESSION[$OJ_NAME.'_getkey'] ?>" 
                       class="btn btn-sm btn-danger" onclick="return confirm('<?php echo $MSG_CONFIRM_DELETE ?>')"><?php echo $MSG_DELETE ?></a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- 分页 -->
    <?php include("../include/page.php"); ?>
</div>

<?php require("admin-footer.php"); ?>
```

### 2. 新增页 (xxx_add.php)

```php
<?php
require("admin-header.php");
require_once("../include/set_get_key.php");

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

if (isset($OJ_LANG)) {
    require_once("../lang/$OJ_LANG.php");
}

$view_title = $MSG_ADD . " " . $MSG_xxx;

// 处理提交
if (isset($_POST['do'])) {
    require_once("../include/check_post_key.php");
    
    $name = trim($_POST['name']);
    
    if (empty($name)) {
        echo "<script>alert('名称不能为空'); history.go(-1);</script>";
        exit();
    }
    
    // 执行插入
    $sql = "INSERT INTO `xxx` (`name`) VALUES (?)";
    pdo_query($sql, $name);
    
    echo "<script>alert('$MSG_ADD $MSG_SUCCESS'); window.location.href='xxx_list.php';</script>";
    exit();
}
?>

<title><?php echo $view_title ?></title>
<hr>
<center><h3><?php echo $view_title ?></h3></center>

<div class="padding">
    <form action="xxx_add.php" method="post" class="form-horizontal">
        <?php require_once("../include/csrf.php"); ?>
        
        <div class="form-group">
            <label class="col-sm-2 control-label">名称</label>
            <div class="col-sm-6">
                <input type="text" name="name" class="form-control" required>
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="do" value="true" class="btn btn-primary"><?php echo $MSG_SUBMIT ?></button>
                <a href="xxx_list.php" class="btn btn-default"><?php echo $MSG_BACK ?></a>
            </div>
        </div>
    </form>
</div>

<?php require("admin-footer.php"); ?>
```

### 3. 编辑页 (xxx_edit.php)

```php
<?php
require("admin-header.php");
require_once("../include/set_get_key.php");

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

if (isset($OJ_LANG)) {
    require_once("../lang/$OJ_LANG.php");
}

$id = intval($_GET['id']);

// 获取数据
$sql = "SELECT * FROM `xxx` WHERE `id` = ?";
$result = pdo_query($sql, $id);
$row = $result[0];

$view_title = $MSG_EDIT . " " . $MSG_xxx;

// 处理提交
if (isset($_POST['do'])) {
    require_once("../include/check_post_key.php");
    
    $name = trim($_POST['name']);
    
    $sql = "UPDATE `xxx` SET `name` = ? WHERE `id` = ?";
    pdo_query($sql, $name, $id);
    
    echo "<script>alert('$MSG_EDIT $MSG_SUCCESS'); window.location.href='xxx_list.php';</script>";
    exit();
}
?>

<title><?php echo $view_title ?></title>
<hr>
<center><h3><?php echo $view_title ?></h3></center>

<div class="padding">
    <form action="xxx_edit.php?id=<?php echo $id ?>" method="post" class="form-horizontal">
        <?php require_once("../include/csrf.php"); ?>
        
        <div class="form-group">
            <label class="col-sm-2 control-label">名称</label>
            <div class="col-sm-6">
                <input type="text" name="name" class="form-control" value="<?php echo $row['name'] ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="do" value="true" class="btn btn-primary"><?php echo $MSG_SUBMIT ?></button>
                <a href="xxx_list.php" class="btn btn-default"><?php echo $MSG_BACK ?></a>
            </div>
        </div>
    </form>
</div>

<?php require("admin-footer.php"); ?>
```

### 4. 删除页 (xxx_del.php)

```php
<?php
require("admin-header.php");
require_once("../include/check_get_key.php");  // CSRF 检查

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

$id = intval($_GET['id']);

// 执行删除
$sql = "DELETE FROM `xxx` WHERE `id` = ?";
pdo_query($sql, $id);

echo "<script>alert('删除成功'); window.location.href='xxx_list.php';</script>";
?>
```

### 5. 状态切换 (xxx_df_change.php)

```php
<?php
require("admin-header.php");
require_once("../include/check_get_key.php");

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    exit();
}

$id = intval($_GET['id']);

// 切换状态
$sql = "UPDATE `xxx` SET `defunct` = NOT `defunct` WHERE `id` = ?";
pdo_query($sql, $id);
?>

<script>history.go(-1);</script>
```

## 常用变量

| 变量 | 含义 |
|------|------|
| `$OJ_NAME` | 系统名称 |
| `$MSG_xxx` | 语言变量（在 lang/cn.php, lang/en.php 中定义） |
| `$_SESSION[$OJ_NAME.'_'.'administrator']` | 管理员权限 |
| `$_SESSION[$OJ_NAME.'_'.'user_id']` | 当前用户ID |
| `$_SESSION[$OJ_NAME.'_'.'getkey']` | CSRF 密钥 |

## 常用语言变量

```php
$MSG_LIST = "列表";
$MSG_ADD = "添加";
$MSG_EDIT = "编辑";
$MSG_DELETE = "删除";
$MSG_SUBMIT = "提交";
$MSG_BACK = "返回";
$MSG_SEARCH = "搜索";
$MSG_OPERATOR = "操作";
$MSG_CONFIRM_DELETE = "确定要删除吗？";
$MSG_SUCCESS = "成功";
$MSG_FAILED = "失败";
$MSG_NAME = "名称";
$MSG_STATUS = "状态";
```

## 数据库操作

```php
// 查询
$result = pdo_query("SELECT * FROM `table` WHERE id = ?", $id);
$row = $result[0];

// 插入
pdo_query("INSERT INTO `table` (`name`) VALUES (?)", $name);

// 更新
pdo_query("UPDATE `table` SET `name` = ? WHERE `id` = ?", $name, $id);

// 删除
pdo_query("DELETE FROM `table` WHERE `id` = ?", $id);

// 获取最后插入ID
$id = pdo_last_insert_id();
```

## 菜单添加

⚠️ **注意**：OJ 使用的是 `syzoj` 模板，对应的管理菜单是 `menu2.php`（不是 `menu.php`）。

### menu2.php 添加菜单

```php
<?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])){?>
    <a class="dropdown-item btn-sm" href="xxx_list.php" target="main" title="<?php echo $MSG_xxx.$MSG_ADMIN ?>">
        <i class="glyphicon glyphicon-xxx"></i> <?php echo $MSG_xxx ?>
    </a>
<?php }?>
```

### menu.php（旧版，非syzoj模板）

```php
<li><a href="xxx_list.php" target="main" title="<?php echo $MSG_xxx.$MSG_ADMIN ?>">
    <i class="glyphicon glyphicon-xxx"></i><?php echo $MSG_xxx ?>
</a></li>
```

## 注意事项

1. **CSRF 防护**：表单使用 `require_once("../include/csrf.php")`，提交时用 `check_post_key.php`
2. **权限检查**：所有页面开头检查管理员权限
3. **语言支持**：使用 `$MSG_xxx` 变量，不要硬编码中文
4. **SQL 安全**：使用参数化查询，禁止字符串拼接
5. **XSS 防护**：输出用 `htmlentities($str, ENT_QUOTES, 'UTF-8')`

---

## Frameset 后台架构

管理后台使用 **frameset + iframe** 架构，浏览器 URL 不会变化。

### 架构说明

```
┌─────────────────────────────────────────────┐
│  frameset (外层框架)                         │
│  ┌────────────┬──────────────────────────┐  │
│  │ menu.php   │ main (iframe)             │  │
│  │ 左侧菜单    │ 右侧内容区                 │  │
│  │            │                          │  │
│  │ <a target=│ user_list.php            │  │
│  │   "main">  │ news_list.php            │  │
│  │            │ school_list.php          │  │
│  └────────────┴──────────────────────────┘  │
└─────────────────────────────────────────────┘
```

### 实现方式

**入口文件** `index.php`:
```html
<frameset cols="16%,*">
    <frame name="menu" src="menu.php">  <!-- 左侧菜单 -->
    <frame name="main" src="help.php">   <!-- 右侧内容 -->
</frameset>
```

**菜单链接** `menu.php`:
```html
<li>
    <a href="xxx_list.php" target="main" title="xxx管理">
        <i class="glyphicon glyphicon-xxx"></i>xxx列表
    </a>
</li>
```

### 为什么这样设计

| 优点 | 说明 |
|------|------|
| 无刷新 | 点击菜单只刷新右侧 iframe，左侧菜单保持 |
| 状态保持 | 左侧菜单展开状态不会因刷新丢失 |
| 减少请求 | 只需加载一次左侧菜单 HTML/CSS |
| 历史干净 | 不会产生大量历史记录，避免误点返回键 |
| 简单稳定 | 比 SPA 更容易实现和维护 |

### 后续开发要求

⚠️ **新增管理功能时，必须遵循此架构**：
1. 页面在 iframe 中打开（`target="main"`）
2. 不要使用 SPA 或 React/Vue 重构
3. 保持与现有页面风格一致
