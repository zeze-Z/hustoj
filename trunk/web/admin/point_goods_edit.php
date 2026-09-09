<?php
ob_start(); // admin-header.php 有 HTML 输出，缓冲保证输出可控（同 point_goods_list.php）
require("admin-header.php");
require("../include/set_post_key.php"); // 顶部生成 postkey；echo 的 hidden input 位置不符但 admin 框架页容忍（point_card_list.php 同模式）

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}
require_once("../include/my_func.inc.php");

$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($edit_id < 0) $edit_id = 0;
$is_edit = $edit_id > 0;

$goods = array(
    'id' => 0, 'product_key' => '', 'title' => '', 'description' => '', 'icon' => '',
    'price' => '', 'original_price' => '', 'download_url' => '',
    'validity_days' => 365, 'sort' => 0, 'status' => 1,
);

if ($is_edit) {
    $r = pdo_query("SELECT * FROM point_goods WHERE id=?", $edit_id);
    if (!isset($r[0])) {
        echo "<div style='margin:10px;'><div class='alert alert-warning'>商品不存在（id={$edit_id}）。
              <a href='point_goods_list.php'>返回列表</a></div></div>";
        require("admin-footer.php");
        exit();
    }
    $goods = $r[0];
}

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF 校验：失败静默 exit(1)；成功后 session postkey 被销毁，重绘须重新生成（见下方 require 非 require_once）
    require_once("../include/check_post_key.php");

    // 编辑模式不接收 POST 的 product_key，固定用 DB 回填值，防止改坏履约路由/订单关联
    $f_pkey   = $is_edit ? (string)$goods['product_key']
                         : (isset($_POST['product_key']) ? trim(strval($_POST['product_key'])) : '');
    $f_title  = isset($_POST['title']) ? trim(strval($_POST['title'])) : '';
    $f_desc   = isset($_POST['description']) ? trim(strval($_POST['description'])) : '';
    $f_icon   = isset($_POST['icon']) ? trim(strval($_POST['icon'])) : '';
    $f_price  = isset($_POST['price']) ? trim(strval($_POST['price'])) : '';
    $f_orig   = isset($_POST['original_price']) ? trim(strval($_POST['original_price'])) : '';
    $f_url    = isset($_POST['download_url']) ? trim(strval($_POST['download_url'])) : '';
    $f_days   = isset($_POST['validity_days']) ? trim(strval($_POST['validity_days'])) : '';
    $f_sort   = isset($_POST['sort']) ? trim(strval($_POST['sort'])) : '0';
    $f_status = isset($_POST['status']) ? intval($_POST['status']) : 1;
    if ($f_sort === '') $f_sort = '0';

    if ($f_title === '')                                  $errors[] = '商品名称不能为空';
    elseif (mb_strlen($f_title, 'UTF-8') > 100)           $errors[] = '商品名称不能超过100字';
    if (mb_strlen($f_desc, 'UTF-8') > 500)                $errors[] = '商品描述不能超过500字';
    if (mb_strlen($f_icon, 'UTF-8') > 16)                 $errors[] = '图标不能超过16个字符';
    if (!preg_match('/^\d+$/', $f_price) || intval($f_price) < 1)
                                                          $errors[] = '兑换价格必须为正整数（≥1）';
    if ($f_orig !== '' && (!preg_match('/^\d+$/', $f_orig) || intval($f_orig) < 1))
                                                          $errors[] = '划线价须为正整数或留空（留空不展示）';
    if (!preg_match('/^\d+$/', $f_days) || intval($f_days) < 1 || intval($f_days) > 3650)
                                                          $errors[] = '有效期须为 1~3650 的整数天';
    if (mb_strlen($f_url, 'UTF-8') > 500)                 $errors[] = '下载链接不能超过500字符';
    elseif ($f_url !== '' && !preg_match('#^(https?://|/)#i', $f_url))
                                                          $errors[] = '下载链接须以 http://、https:// 或 / 开头';
    if (!preg_match('/^-?\d+$/', $f_sort))                $errors[] = '排序须为整数';
    if ($f_status !== 0 && $f_status !== 1)               $errors[] = '状态非法';
    if (!$is_edit) {
        if ($f_pkey === '')                               $errors[] = '商品标识 product_key 不能为空';
        elseif (!preg_match('/^[a-z0-9_]{1,32}$/', $f_pkey))
                                                          $errors[] = '商品标识仅允许小写字母/数字/下划线，长度1~32';
    }

    if (empty($errors)) {
        $orig_val = ($f_orig === '') ? null : intval($f_orig);
        if ($is_edit) {
            try {
                pdo_query("UPDATE point_goods SET title=?,description=?,icon=?,price=?,original_price=?,
                           download_url=?,validity_days=?,sort=?,status=? WHERE id=?",
                    $f_title, $f_desc, $f_icon, intval($f_price), $orig_val,
                    $f_url, intval($f_days), intval($f_sort), $f_status, $edit_id);
            } catch (Exception $e) {
                // pdo_query 对 SQL 异常不走正常返回；捕获后转友好错误重绘，避免 admin 框架页 500
                $errors[] = '保存失败：数据库错误，请重试';
            }
            if (empty($errors)) {
                echo "<script>alert('保存成功');location.href='point_goods_list.php';</script>";
                exit();
            }
        } else {
            // 唯一键冲突先 SELECT 预检给出友好提示；INSERT 仍包 try/catch 兜住预检后的并发竞态
            $dup = pdo_query("SELECT id FROM point_goods WHERE product_key=?", $f_pkey);
            if (isset($dup[0])) {
                $errors[] = '商品标识 product_key 已存在：' . $f_pkey;
            } else {
                try {
                    pdo_query("INSERT INTO point_goods
                               (product_key,title,description,icon,price,original_price,download_url,validity_days,status,sort)
                               VALUES (?,?,?,?,?,?,?,?,?,?)",
                        $f_pkey, $f_title, $f_desc, $f_icon, intval($f_price), $orig_val,
                        $f_url, intval($f_days), $f_status, intval($f_sort));
                } catch (Exception $e) {
                    $errors[] = '新增失败：product_key 可能已被并发创建，请刷新重试';
                }
                if (empty($errors)) {
                    echo "<script>alert('新增成功');location.href='point_goods_list.php';</script>";
                    exit();
                }
            }
        }
    }

    // 校验/入库失败：POST 值回填重绘（编辑模式 product_key 保持 DB 回填值）
    $goods = array_merge($goods, array(
        'title' => $f_title, 'description' => $f_desc, 'icon' => $f_icon,
        'price' => $f_price, 'original_price' => $f_orig, 'download_url' => $f_url,
        'validity_days' => $f_days, 'sort' => $f_sort, 'status' => $f_status,
    ));
    if (!$is_edit) $goods['product_key'] = $f_pkey;
}

// 非 require_once：POST 失败重绘时 session postkey 已被 check_post_key 销毁，此处必须重新生成
// （本仓库已知坑：require_once 复用已销毁的 postkey 会导致重绘后的表单提交被静默拒绝）
require("../include/set_post_key.php");
$postkey = isset($_SESSION[$OJ_NAME.'_'.'postkey']) ? $_SESSION[$OJ_NAME.'_'.'postkey'] : '';
?>
<title><?php echo $is_edit ? '编辑积分商品' : '新增积分商品'; ?></title>
<hr>
<center><h3><?php echo $is_edit ? '编辑积分商品' : '新增积分商品'; ?></h3></center>

<div style="margin: 10px;">
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="max-width:720px;">
      <ul style="margin:0 0 0 18px;">
        <?php foreach ($errors as $err): ?>
          <li><?php echo htmlentities($err, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="point_goods_edit.php<?php if ($is_edit) echo '?id=' . $edit_id; ?>" style="max-width:720px;">
    <input type="hidden" name="postkey" value="<?php echo htmlentities($postkey, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="form-group" style="margin-bottom:10px;">
      <label>商品标识 product_key <?php if ($is_edit) echo '<span style="color:#999;font-weight:normal;">（创建后不可修改）</span>'; ?></label>
      <input type="text" name="product_key" maxlength="32" class="form-control"
             value="<?php echo htmlentities($goods['product_key'], ENT_QUOTES, 'UTF-8'); ?>"
             placeholder="如 offline_game，仅小写字母/数字/下划线，履约路由依据"
             <?php if ($is_edit) echo 'readonly disabled'; ?> style="max-width:360px;<?php if ($is_edit) echo 'background:#eee;'; ?>">
      <?php if (!$is_edit): ?>
        <span style="color:#999;font-size:12px;">须与 point_goods_redeem.php 履约路由表中的 key 一致，否则前端无法兑换</span>
      <?php endif; ?>
    </div>

    <div class="form-group" style="margin-bottom:10px;">
      <label>商品名称 *</label>
      <input type="text" name="title" maxlength="100" class="form-control" required
             value="<?php echo htmlentities($goods['title'], ENT_QUOTES, 'UTF-8'); ?>" style="max-width:520px;">
    </div>

    <div class="form-group" style="margin-bottom:10px;">
      <label>商品描述</label>
      <textarea name="description" rows="3" maxlength="500" class="form-control"
                style="max-width:520px;"><?php echo htmlentities($goods['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
      <span style="color:#999;font-size:12px;">前端横幅/弹窗展示，≤500字</span>
    </div>

    <div class="form-group" style="margin-bottom:10px;">
      <label>图标（emoji）</label>
      <input type="text" name="icon" maxlength="16" class="form-control"
             value="<?php echo htmlentities($goods['icon'], ENT_QUOTES, 'UTF-8'); ?>" style="max-width:120px;">
    </div>

    <div class="form-group" style="margin-bottom:10px;">
      <label>兑换价格（积分）*</label>
      <input type="number" name="price" min="1" step="1" class="form-control" required
             value="<?php echo htmlentities(strval($goods['price']), ENT_QUOTES, 'UTF-8'); ?>" style="max-width:160px;">
    </div>

    <div class="form-group" style="margin-bottom:10px;">
      <label>划线价（积分，留空不展示）</label>
      <input type="number" name="original_price" min="1" step="1" class="form-control"
             value="<?php echo htmlentities(strval($goods['original_price']), ENT_QUOTES, 'UTF-8'); ?>"
             placeholder="留空 = 不展示划线价" style="max-width:160px;">
    </div>

    <div class="form-group" style="margin-bottom:10px;">
      <label>下载链接</label>
      <input type="text" name="download_url" maxlength="500" class="form-control"
             value="<?php echo htmlentities($goods['download_url'], ENT_QUOTES, 'UTF-8'); ?>" style="max-width:640px;">
      <span style="color:#999;font-size:12px;">兑换成功后展示给用户，≤500字符</span>
    </div>

    <div class="form-group" style="margin-bottom:10px;">
      <label>有效期（天）</label>
      <input type="number" name="validity_days" min="1" max="3650" step="1" class="form-control"
             value="<?php echo htmlentities(strval($goods['validity_days']), ENT_QUOTES, 'UTF-8'); ?>" style="max-width:160px;">
    </div>

    <div class="form-group" style="margin-bottom:10px;">
      <label>排序（小的在前）</label>
      <input type="number" name="sort" step="1" class="form-control"
             value="<?php echo htmlentities(strval($goods['sort']), ENT_QUOTES, 'UTF-8'); ?>" style="max-width:160px;">
    </div>

    <div class="form-group" style="margin-bottom:10px;">
      <label>状态</label>
      <select name="status" class="form-control" style="max-width:160px;">
        <option value="1" <?php if (intval($goods['status']) === 1) echo 'selected'; ?>>上架</option>
        <option value="0" <?php if (intval($goods['status']) !== 1) echo 'selected'; ?>>下架</option>
      </select>
    </div>

    <?php if ($is_edit): ?>
      <p style="color:#999;font-size:12px;">
        创建时间：<?php echo htmlentities((string)$goods['create_time'], ENT_QUOTES, 'UTF-8'); ?>
        　更新时间：<?php echo htmlentities((string)$goods['update_time'], ENT_QUOTES, 'UTF-8'); ?>
      </p>
    <?php endif; ?>

    <button type="submit" class="btn btn-success btn-sm"><i class="glyphicon glyphicon-save"></i> 保存</button>
    <a class="btn btn-default btn-sm" href="point_goods_list.php">返回列表</a>
  </form>
</div>

<?php require("admin-footer.php"); ?>
