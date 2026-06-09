<?php
require("admin-header.php");
require_once("../include/set_get_key.php");
require_once("../include/set_post_key.php");
require_once("../include/my_func.inc.php");

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

$msg = '';
$msg_type = '';
$last_user = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('../include/check_post_key.php');
    $target_user = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
    $delta       = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    $reason      = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    $admin_id    = $_SESSION[$OJ_NAME.'_'.'user_id'];

    $result = point_admin_adjust($admin_id, $target_user, $delta, $reason);
    $msg_type = $result['success'] ? 'success' : 'error';
    if ($result['success']) {
        $msg = "调整成功，用户名（用户ID）：{$target_user}，当前余额：" . intval($result['balance']);
    } else {
        $msg = $result['message'];
    }
    $last_user = $target_user;
}

$current_balance = '';
if ($last_user !== '') {
    $current_balance = intval(point_get_balance($last_user));
}

$postkey = isset($_SESSION[$OJ_NAME.'_'.'postkey']) ? $_SESSION[$OJ_NAME.'_'.'postkey'] : '';
?>
<title>手动调整积分</title>
<hr>
<center><h3>手动调整积分</h3></center>

<div style="margin:20px auto; max-width: 720px;">
  <?php if ($msg !== ''): ?>
    <div class="alert alert-<?php echo $msg_type === 'success' ? 'success' : 'danger'; ?>">
      <?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="point_adjust.php" class="form-horizontal">
    <input type="hidden" name="postkey" value="<?php echo htmlentities($postkey, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="form-group">
      <label class="col-sm-3 control-label">用户名（用户ID）</label>
      <div class="col-sm-7">
        <input type="text" class="form-control" name="user_id" required
               placeholder="请输入登录用户名，不是昵称"
               value="<?php echo htmlentities($last_user, ENT_QUOTES, 'UTF-8'); ?>">
        <p class="help-block">这里填写 users.user_id（登录用户名），不是昵称。</p>
        <?php if ($last_user !== ''): ?>
          <p class="help-block">当前余额：<strong><?php echo intval($current_balance); ?></strong> 积分</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">调整积分</label>
      <div class="col-sm-7">
        <input type="number" class="form-control" name="delta" required
               placeholder="正数加积分，负数扣积分；不可为 0">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">原因</label>
      <div class="col-sm-7">
        <textarea class="form-control" name="reason" rows="3" required
                  placeholder="必填，会写入积分流水备注"></textarea>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-offset-3 col-sm-7">
        <button type="submit" class="btn btn-primary" onclick="return confirm('确认调整该用户积分？');">
          <i class="glyphicon glyphicon-ok"></i> 确认调整
        </button>
        <a class="btn btn-default" href="point_log_list.php?user_id=<?php echo urlencode($last_user); ?>&type=<?php echo POINT_LOG_TYPE_ADMIN; ?>">
          查看该用户调整记录
        </a>
      </div>
    </div>
  </form>
</div>

<?php require("admin-footer.php"); ?>
