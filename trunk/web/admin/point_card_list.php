<?php
require("admin-header.php");
require_once("../include/set_get_key.php");
require_once("../include/set_post_key.php");
require_once("../include/my_func.inc.php");

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

$batch_no = isset($_GET['batch_no']) ? trim($_GET['batch_no']) : '';
$card_no  = isset($_GET['card_no']) ? trim($_GET['card_no']) : '';
$status   = isset($_GET['status']) && $_GET['status'] !== '' ? intval($_GET['status']) : -1;
$user_kw  = isset($_GET['redeem_user_id']) ? trim($_GET['redeem_user_id']) : '';
$page     = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 30;
$offset   = ($page - 1) * $per_page;

$where = ' WHERE 1=1';
$params = [];
if ($batch_no !== '') { $where .= ' AND batch_no = ?'; $params[] = $batch_no; }
if ($card_no  !== '') { $where .= ' AND card_no LIKE ?'; $params[] = '%' . $card_no . '%'; }
if ($status === 0 || $status === 1 || $status === 2) { $where .= ' AND status = ?'; $params[] = $status; }
if ($user_kw !== '') { $where .= ' AND redeem_user_id LIKE ?'; $params[] = '%' . $user_kw . '%'; }

$count_rows = pdo_query("SELECT COUNT(*) AS total FROM point_card $where", $params);
$total = isset($count_rows[0]['total']) ? intval($count_rows[0]['total']) : 0;
$total_pages = $total > 0 ? (int)ceil($total / $per_page) : 0;

$rows = pdo_query(
    "SELECT id, batch_no, card_no, card_secret, status, redeem_user_id, redeem_time, redeem_ip, create_time
       FROM point_card $where
      ORDER BY id DESC
      LIMIT $per_page OFFSET $offset",
    $params
);
if (!is_array($rows)) $rows = [];

$postkey = isset($_SESSION[$OJ_NAME.'_'.'postkey']) ? $_SESSION[$OJ_NAME.'_'.'postkey'] : '';
$status_map = [0 => '未兑换', 1 => '已兑换', 2 => '已禁用'];
?>
<title>充值卡管理</title>
<hr>
<center><h3>充值卡管理</h3></center>

<div style="margin: 10px;">
  <form method="GET" action="point_card_list.php" class="form-inline" style="margin-bottom:10px;">
    批次：<input type="text" name="batch_no" value="<?php echo htmlentities($batch_no, ENT_QUOTES, 'UTF-8'); ?>" placeholder="如 PC20260607...">
    卡号：<input type="text" name="card_no" value="<?php echo htmlentities($card_no, ENT_QUOTES, 'UTF-8'); ?>">
    状态：
    <select name="status">
      <option value="">全部</option>
      <option value="0" <?php if ($status === 0) echo 'selected'; ?>>未兑换</option>
      <option value="1" <?php if ($status === 1) echo 'selected'; ?>>已兑换</option>
      <option value="2" <?php if ($status === 2) echo 'selected'; ?>>已禁用</option>
    </select>
    兑换用户：<input type="text" name="redeem_user_id" value="<?php echo htmlentities($user_kw, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" class="btn btn-primary btn-sm">查询</button>
    <a class="btn btn-default btn-sm" href="point_card_list.php">重置</a>
    <span style="margin-left:20px;color:#999;">共 <?php echo $total; ?> 条；每张卡固定 <?php echo intval(POINT_CARD_VALUE); ?> 积分。</span>
  </form>

  <!-- 批量生成 -->
  <form method="POST" action="point_card_generate.php" class="form-inline" style="margin-bottom:10px; border:1px dashed #ccc; padding:10px;">
    <input type="hidden" name="postkey" value="<?php echo htmlentities($postkey, ENT_QUOTES, 'UTF-8'); ?>">
    <strong>批量生成：</strong>
    数量：<input type="number" name="count" min="1" max="1000" value="50" style="width:80px;">
    批次号(可选)：<input type="text" name="batch_no" placeholder="留空则自动生成 PC+YmdHis">
    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('确认批量生成充值卡？');">
      <i class="glyphicon glyphicon-plus"></i> 生成
    </button>
  </form>

  <!-- 批量操作工具栏 -->
  <div style="margin-bottom:8px;">
    <button type="button" class="btn btn-info btn-sm" onclick="copySelected()">
      <i class="glyphicon glyphicon-copy"></i> 复制选中卡号卡密
    </button>
    <button type="button" class="btn btn-warning btn-sm" onclick="disableSelected()">
      <i class="glyphicon glyphicon-ban-circle"></i> 禁用选中(仅未兑换)
    </button>
    <span id="copy_tip" style="margin-left:10px;color:#52c41a;"></span>
  </div>

  <form id="batch_form" method="POST" action="point_card_disable.php">
    <input type="hidden" name="postkey" value="<?php echo htmlentities($postkey, ENT_QUOTES, 'UTF-8'); ?>">
    <table class="table table-bordered table-condensed" id="card_table">
      <thead>
        <tr>
          <th><input type="checkbox" id="chk_all" onchange="toggleAll(this)"></th>
          <th>ID</th>
          <th>卡号</th>
          <th>卡密(脱敏)</th>
          <th>积分</th>
          <th>状态</th>
          <th>批次</th>
          <th>兑换用户</th>
          <th>兑换时间</th>
          <th>兑换IP</th>
          <th>创建时间</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="11" style="text-align:center;color:#999;">暂无数据</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r):
            $sec = (string)$r['card_secret'];
            $mask = strlen($sec) > 6
                ? substr($sec, 0, 2) . str_repeat('*', max(2, strlen($sec) - 4)) . substr($sec, -2)
                : str_repeat('*', strlen($sec));
            $is_unused = intval($r['status']) === 0;
          ?>
            <tr>
              <td>
                <input type="checkbox" name="card_id[]" value="<?php echo intval($r['id']); ?>"
                  class="row_chk"
                  data-cardno="<?php echo htmlentities($r['card_no'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-secret="<?php echo htmlentities($sec, ENT_QUOTES, 'UTF-8'); ?>"
                  data-status="<?php echo intval($r['status']); ?>">
              </td>
              <td><?php echo intval($r['id']); ?></td>
              <td style="font-family:monospace;"><?php echo htmlentities($r['card_no'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td style="font-family:monospace;color:#999;"><?php echo htmlentities($mask, ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo intval(POINT_CARD_VALUE); ?></td>
              <td><?php echo htmlentities($status_map[$r['status']] ?? '未知', ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities($r['batch_no'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities((string)$r['redeem_user_id'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities((string)$r['redeem_time'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities((string)$r['redeem_ip'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities((string)$r['create_time'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </form>

  <?php if ($total_pages > 1):
      $base = 'point_card_list.php?batch_no=' . urlencode($batch_no)
            . '&card_no=' . urlencode($card_no)
            . '&status=' . ($status === -1 ? '' : $status)
            . '&redeem_user_id=' . urlencode($user_kw);
  ?>
    <div style="text-align:center;">
      <?php if ($page > 1): ?>
        <a class="btn btn-default btn-sm" href="<?php echo $base; ?>&page=<?php echo $page - 1; ?>">上一页</a>
      <?php endif; ?>
      <span style="margin: 0 10px;">第 <?php echo $page; ?> / <?php echo $total_pages; ?> 页</span>
      <?php if ($page < $total_pages): ?>
        <a class="btn btn-default btn-sm" href="<?php echo $base; ?>&page=<?php echo $page + 1; ?>">下一页</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<script>
function toggleAll(el){
  var boxes = document.querySelectorAll('.row_chk');
  for (var i=0;i<boxes.length;i++) boxes[i].checked = el.checked;
}
function getSelected(filterUnused){
  var out = [];
  var boxes = document.querySelectorAll('.row_chk');
  for (var i=0;i<boxes.length;i++){
    if (!boxes[i].checked) continue;
    if (filterUnused && boxes[i].getAttribute('data-status') !== '0') continue;
    out.push(boxes[i]);
  }
  return out;
}
function copySelected(){
  var boxes = getSelected(false);
  if (boxes.length === 0){ alert('请先勾选要复制的卡片'); return; }
  var lines = [];
  for (var i=0;i<boxes.length;i++){
    lines.push(boxes[i].getAttribute('data-cardno') + ' ' + boxes[i].getAttribute('data-secret'));
  }
  var text = lines.join('\n');
  var done = function(){
    var tip = document.getElementById('copy_tip');
    tip.textContent = '已复制 ' + boxes.length + ' 行到剪切板';
    setTimeout(function(){ tip.textContent=''; }, 4000);
  };
  if (navigator.clipboard && window.isSecureContext){
    navigator.clipboard.writeText(text).then(done, function(){ fallbackCopy(text, done); });
  } else {
    fallbackCopy(text, done);
  }
}
function fallbackCopy(text, cb){
  var ta = document.createElement('textarea');
  ta.value = text; ta.style.position='fixed'; ta.style.opacity='0';
  document.body.appendChild(ta); ta.select();
  try { document.execCommand('copy'); cb(); }
  catch(e){ alert('复制失败，请手动复制'); }
  finally { document.body.removeChild(ta); }
}
function disableSelected(){
  var boxes = getSelected(true);
  if (boxes.length === 0){ alert('请勾选未兑换状态的卡片'); return; }
  if (!confirm('确认禁用所选 ' + boxes.length + ' 张未兑换卡？')) return;
  document.getElementById('batch_form').submit();
}
</script>

<?php require("admin-footer.php"); ?>
