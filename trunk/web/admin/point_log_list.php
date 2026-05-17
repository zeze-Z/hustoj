<?php
require("admin-header.php");
require_once("../include/set_get_key.php");
require_once("../include/my_func.inc.php");

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

$user_kw  = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
$type     = isset($_GET['type']) && $_GET['type'] !== '' ? intval($_GET['type']) : 0;
$from     = isset($_GET['from']) ? trim($_GET['from']) : '';
$to       = isset($_GET['to']) ? trim($_GET['to']) : '';
$page     = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 50;
$offset   = ($page - 1) * $per_page;

$valid_types = [POINT_LOG_TYPE_CARD, POINT_LOG_TYPE_COURSE, POINT_LOG_TYPE_ADMIN, POINT_LOG_TYPE_SYSTEM];
if (!in_array($type, $valid_types, true)) $type = 0;

$where = ' WHERE 1=1';
$params = [];
if ($user_kw !== '') { $where .= ' AND user_id LIKE ?'; $params[] = '%' . $user_kw . '%'; }
if ($type > 0)       { $where .= ' AND type = ?';       $params[] = $type; }
if (preg_match('/^\d{4}-\d{2}-\d{2}/', $from)) { $where .= ' AND create_time >= ?'; $params[] = $from; }
if (preg_match('/^\d{4}-\d{2}-\d{2}/', $to))   { $where .= ' AND create_time <= ?'; $params[] = $to; }

$count_rows = pdo_query("SELECT COUNT(*) AS total FROM point_log $where", $params);
$total = isset($count_rows[0]['total']) ? intval($count_rows[0]['total']) : 0;
$total_pages = $total > 0 ? (int)ceil($total / $per_page) : 0;

$rows = pdo_query(
    "SELECT id, user_id, change_point, balance, type, relation_id, remark, create_time
       FROM point_log $where
      ORDER BY id DESC
      LIMIT $per_page OFFSET $offset",
    $params
);
if (!is_array($rows)) $rows = [];

$type_map = [
    POINT_LOG_TYPE_CARD => '充值卡兑换',
    POINT_LOG_TYPE_COURSE => '课件购买',
    POINT_LOG_TYPE_ADMIN => '管理员调整',
    POINT_LOG_TYPE_SYSTEM => '系统操作',
];
?>
<title>积分流水</title>
<hr>
<center><h3>积分流水</h3></center>

<div style="margin:10px;">
  <form method="GET" action="point_log_list.php" class="form-inline" style="margin-bottom:10px;">
    用户：<input type="text" name="user_id" value="<?php echo htmlentities($user_kw, ENT_QUOTES, 'UTF-8'); ?>">
    类型：
    <select name="type">
      <option value="">全部</option>
      <?php foreach ($type_map as $tk => $tv): ?>
        <option value="<?php echo $tk; ?>" <?php if ($type == $tk) echo 'selected'; ?>>
          <?php echo htmlentities($tv, ENT_QUOTES, 'UTF-8'); ?>
        </option>
      <?php endforeach; ?>
    </select>
    起：<input type="text" name="from" placeholder="YYYY-MM-DD" value="<?php echo htmlentities($from, ENT_QUOTES, 'UTF-8'); ?>">
    止：<input type="text" name="to" placeholder="YYYY-MM-DD" value="<?php echo htmlentities($to, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" class="btn btn-primary btn-sm">查询</button>
    <a class="btn btn-default btn-sm" href="point_log_list.php">重置</a>
    <span style="margin-left:15px;color:#999;">共 <?php echo $total; ?> 条</span>
  </form>

  <table class="table table-bordered table-condensed">
    <thead>
      <tr>
        <th>ID</th>
        <th>用户</th>
        <th>类型</th>
        <th style="text-align:right;">变动</th>
        <th style="text-align:right;">余额</th>
        <th>关联</th>
        <th>备注</th>
        <th>时间</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" style="text-align:center;color:#999;">暂无流水</td></tr>
      <?php else: foreach ($rows as $r):
          $change = intval($r['change_point']);
          $sign = $change > 0 ? '+' : '';
          $color = $change > 0 ? '#52c41a' : '#d4380d';
          $ttext = isset($type_map[$r['type']]) ? $type_map[$r['type']] : '未知';
      ?>
        <tr>
          <td><?php echo intval($r['id']); ?></td>
          <td><?php echo htmlentities($r['user_id'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlentities($ttext, ENT_QUOTES, 'UTF-8'); ?></td>
          <td style="text-align:right;color:<?php echo $color; ?>;font-weight:600;"><?php echo $sign . $change; ?></td>
          <td style="text-align:right;"><?php echo intval($r['balance']); ?></td>
          <td><?php echo htmlentities((string)$r['relation_id'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlentities((string)$r['remark'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlentities((string)$r['create_time'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <?php if ($total_pages > 1):
      $base = 'point_log_list.php?user_id=' . urlencode($user_kw)
            . '&type=' . ($type > 0 ? $type : '')
            . '&from=' . urlencode($from)
            . '&to=' . urlencode($to);
  ?>
    <div style="text-align:center;">
      <?php if ($page > 1): ?>
        <a class="btn btn-default btn-sm" href="<?php echo $base; ?>&page=<?php echo $page - 1; ?>">上一页</a>
      <?php endif; ?>
      <span style="margin:0 10px;">第 <?php echo $page; ?> / <?php echo $total_pages; ?> 页</span>
      <?php if ($page < $total_pages): ?>
        <a class="btn btn-default btn-sm" href="<?php echo $base; ?>&page=<?php echo $page + 1; ?>">下一页</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php require("admin-footer.php"); ?>
