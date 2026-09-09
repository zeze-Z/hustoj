<?php
ob_start(); // admin-header.php 有 HTML 输出，缓冲以保证 GET 动作处理后可 302 重定向（同 news_list.php 先例）
require("admin-header.php");
require_once("../include/set_get_key.php");
require_once("../include/set_post_key.php");
require_once("../include/my_func.inc.php");

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

// GET 页内动作：上架/下架切换（check_get_key 校验 + PRG 302 防重放，同 news_list.php 模式）
if (isset($_GET['op']) && $_GET['op'] === 'toggle' && isset($_GET['id']) && intval($_GET['id']) > 0) {
    require_once("../include/check_get_key.php");
    $gid = intval($_GET['id']);
    $r = pdo_query("SELECT status FROM point_goods WHERE id=?", $gid);
    if (isset($r[0])) {
        $new_status = intval($r[0]['status']) === 1 ? 0 : 1;
        pdo_query("UPDATE point_goods SET status=? WHERE id=?", $new_status, $gid);
    }
    header("Location: point_goods_list.php");
    exit();
}

// 销量统计：按商品聚合订单（订单数 + 消耗积分），无订单的商品不出现在结果里
$stat_map = [];
$stat_rows = pdo_query("SELECT product_key, COUNT(*) AS cnt, SUM(point_amount) AS pts FROM point_goods_order GROUP BY product_key");
if (is_array($stat_rows)) {
    foreach ($stat_rows as $s) {
        $stat_map[$s['product_key']] = array('cnt' => intval($s['cnt']), 'pts' => intval($s['pts']));
    }
}

$rows = pdo_query("SELECT * FROM point_goods ORDER BY status DESC, sort ASC, id ASC");
if (!is_array($rows)) $rows = [];

$getkey = isset($_SESSION[$OJ_NAME.'_'.'getkey']) ? $_SESSION[$OJ_NAME.'_'.'getkey'] : '';
// 已接入履约路由的商品 key：读取共享路由表（include/my_func.inc.php 的 point_goods_routes()），
// 与 point_goods_redeem.php 同源，新增路由无需在此同步
$redeem_routes = array_keys(point_goods_routes());
?>
<title>积分商品管理</title>
<hr>
<center><h3>积分商品管理</h3></center>

<div style="margin: 10px;">
  <div style="margin-bottom:10px;">
    <a class="btn btn-success btn-sm" href="point_goods_edit.php"><i class="glyphicon glyphicon-plus"></i> 新增商品</a>
    <span style="margin-left:15px;color:#999;">共 <?php echo count($rows); ?> 个商品；上架中的商品才会出现在前端兑换入口。</span>
  </div>

  <table class="table table-bordered table-condensed">
    <thead>
      <tr>
        <th>图标</th>
        <th>名称</th>
        <th>product_key</th>
        <th style="text-align:right;">价格(积分)</th>
        <th style="text-align:right;">划线价</th>
        <th style="text-align:right;">有效期(天)</th>
        <th style="text-align:right;">排序</th>
        <th style="text-align:right;">销量</th>
        <th>状态</th>
        <th>操作</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="10" style="text-align:center;color:#999;">暂无商品，点击右上角"新增商品"创建</td></tr>
      <?php else: foreach ($rows as $g):
          $gid = intval($g['id']);
          $on_sale = intval($g['status']) === 1;
          $key = (string)$g['product_key'];
          $stat = isset($stat_map[$key]) ? $stat_map[$key] : array('cnt' => 0, 'pts' => 0);
          $routed = in_array($key, $redeem_routes, true);
      ?>
        <tr<?php if (!$on_sale) echo ' style="color:#999;"'; ?>>
          <td><?php echo $g['icon'] !== '' ? htmlentities($g['icon'], ENT_QUOTES, 'UTF-8') : '-'; ?></td>
          <td><a href="point_goods_edit.php?id=<?php echo $gid; ?>"><?php echo htmlentities($g['title'], ENT_QUOTES, 'UTF-8'); ?></a></td>
          <td style="font-family:monospace;">
            <?php echo htmlentities($key, ENT_QUOTES, 'UTF-8'); ?>
            <?php if (!$routed): ?>
              <span class="label label-warning" title="point_goods_redeem.php 未注册该 key 的履约路由，兑换会被拒绝">未接入履约</span>
            <?php endif; ?>
          </td>
          <td style="text-align:right;font-weight:600;"><?php echo intval($g['price']); ?></td>
          <td style="text-align:right;color:#999;">
            <?php echo ($g['original_price'] !== null && intval($g['original_price']) > 0) ? intval($g['original_price']) : '-'; ?>
          </td>
          <td style="text-align:right;"><?php echo intval($g['validity_days']); ?></td>
          <td style="text-align:right;"><?php echo intval($g['sort']); ?></td>
          <td style="text-align:right;"><?php echo intval($stat['cnt']); ?> 单 · <?php echo intval($stat['pts']); ?> 积分</td>
          <td>
            <?php if ($on_sale): ?>
              <span class="label label-success">上架</span>
            <?php else: ?>
              <span class="label label-default">下架</span>
            <?php endif; ?>
          </td>
          <td>
            <a class="btn btn-default btn-xs" href="point_goods_edit.php?id=<?php echo $gid; ?>">编辑</a>
            <a class="btn btn-xs <?php echo $on_sale ? 'btn-warning' : 'btn-info'; ?>"
               href="point_goods_list.php?op=toggle&id=<?php echo $gid; ?>&getkey=<?php echo htmlentities($getkey, ENT_QUOTES, 'UTF-8'); ?>"
               onclick="return confirm('确认<?php echo $on_sale ? '下架' : '上架'; ?>该商品？');"><?php echo $on_sale ? '下架' : '上架'; ?></a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php require("admin-footer.php"); ?>
