<?php
/**
 * 用户关键操作记录（审计）
 * 只读聚合四张现成日志表，按时间倒序展示某用户的关键行为：
 *   loginlog     -> 登录成功 / 登录失败 / 注册
 *   point_log    -> 积分变动（充值卡 / 课件 / 管理员调整 / 系统）
 *   solution     -> 题目提交及判题结果
 *   course_order -> 课件权限获取（免费领取 / 积分购买，含升级）
 * 不新建表、不埋点；权限与学校隔离与 user_list.php 保持一致。
 */
require("admin-header.php");
require_once("../include/set_get_key.php");
require_once("../include/my_func.inc.php");
require_once("../include/const.inc.php");   // $judge_result / $judge_color

// 权限：与 user_list.php 一致（administrator 或 password_setter）
if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'password_setter']))) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}
if (isset($OJ_LANG)) {
    require_once("../lang/$OJ_LANG.php");
}

// ---- 入参 ----
$target_user = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
$cat         = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$from        = isset($_GET['from']) ? trim($_GET['from']) : '';
$to          = isset($_GET['to']) ? trim($_GET['to']) : '';
$page        = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page    = 50;
$offset      = ($page - 1) * $per_page;

// 默认只查询最近 90 天
$default_from = date('Y-m-d', strtotime('-90 days'));
if ($from === '' && $to === '') {
    $from = $default_from;
    $to   = date('Y-m-d');
}

$valid_cats = ['', 'login', 'point', 'submit', 'course'];
if (!in_array($cat, $valid_cats, true)) $cat = '';

// ---- 校验目标用户存在 & 学校隔离 ----
$deny_msg = '';
if ($target_user === '') {
    $deny_msg = '未指定用户';
} else {
    $urow = pdo_query("SELECT user_id, nick, school_id FROM `users` WHERE user_id = ?", $target_user);
    if (empty($urow)) {
        $deny_msg = '用户不存在';
    } else {
        // 学校模式：非超管只能审计本校用户
        if (!empty($OJ_SCHOOL_MODE) && !isSuperAdmin()) {
            $my_school   = getCurrentUserSchoolId();
            $target_sch  = $urow[0]['school_id'];
            if ($my_school != $target_sch) {
                $deny_msg = '无权限查看该用户的操作记录';
            }
        }
    }
}

// 标签映射
$cat_label = [
    'login'  => '登录',
    'point'  => '积分',
    'submit' => '提交',
    'course' => '课件',
];
$cat_color = [
    'login'  => 'label label-default',
    'point'  => 'label label-info',
    'submit' => 'label label-success',
    'course' => 'label label-warning',
];
$point_type_map = [
    POINT_LOG_TYPE_CARD   => '充值卡兑换',
    POINT_LOG_TYPE_COURSE => '课件购买',
    POINT_LOG_TYPE_ADMIN  => '管理员调整',
    POINT_LOG_TYPE_SYSTEM => '系统操作',
];
$license_map = [1 => '完整预览版', 2 => '原文件版', 3 => '完整版'];
$channel_map = ['free' => '免费', 'point' => '积分支付'];
?>
<title>用户操作记录</title>
<hr>
<center><h3>用户操作记录<?php if (!$deny_msg && !empty($urow)) {
    echo ' - ' . htmlentities($target_user, ENT_QUOTES, 'UTF-8')
       . (!empty($urow[0]['nick']) ? '（' . htmlentities($urow[0]['nick'], ENT_QUOTES, 'UTF-8') . '）' : '');
} ?></h3></center>

<?php if ($deny_msg): ?>
<div style="margin:30px;text-align:center;color:#d4380d;font-size:16px;">
  <?php echo htmlentities($deny_msg, ENT_QUOTES, 'UTF-8'); ?>
  <div style="margin-top:15px;"><a class="btn btn-default btn-sm" href="user_list.php">返回用户列表</a></div>
</div>
<?php require("admin-footer.php"); return; endif; ?>

<div style="margin:10px;">
  <form method="GET" action="user_action_log.php" class="form-inline" style="margin-bottom:10px;">
    <input type="hidden" name="user_id" value="<?php echo htmlentities($target_user, ENT_QUOTES, 'UTF-8'); ?>">
    类别：
    <select name="cat">
      <option value="">全部</option>
      <?php foreach ($cat_label as $ck => $cv): ?>
        <option value="<?php echo $ck; ?>" <?php if ($cat === $ck) echo 'selected'; ?>>
          <?php echo htmlentities($cv, ENT_QUOTES, 'UTF-8'); ?>
        </option>
      <?php endforeach; ?>
    </select>
    起：<input type="text" name="from" placeholder="YYYY-MM-DD" value="<?php echo htmlentities($from, ENT_QUOTES, 'UTF-8'); ?>">
    止：<input type="text" name="to" placeholder="YYYY-MM-DD" value="<?php echo htmlentities($to, ENT_QUOTES, 'UTF-8'); ?>">
    <span style="color:#999; font-size:0.9em; margin-left:8px;">（大范围查询可能较慢）</span>
    <button type="submit" class="btn btn-primary btn-sm">查询</button>
    <a class="btn btn-default btn-sm" href="user_action_log.php?user_id=<?php echo urlencode($target_user); ?>">重置</a>
    <a class="btn btn-default btn-sm" href="user_list.php">返回用户列表</a>
  </form>

  <?php
  // ---- 检查各日志表是否存在，缺失的表自动跳过 ----
  // 注：pdo_query 对非 SELECT 开头的语句（如 SHOW TABLES）返回行数而非结果集，
  // 故改用 information_schema（标准 SELECT）做存在性检查。
  $existing_tables = [];
  $tbl_res = pdo_query("SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE()");
  if (is_array($tbl_res)) {
      foreach ($tbl_res as $trow) {
          $existing_tables[] = strtolower(current($trow));
      }
  }
  $table_exists = function ($name) use ($existing_tables) {
      return in_array(strtolower($name), $existing_tables, true);
  };

  // ---- 构造 UNION 子查询（按选定类别裁剪，未选则全量；表不存在则跳过） ----
  // loginlog 只保留有审计价值的记录：登录失败 / 每个 IP 首次出现（新设备新地点）/ 最近一次成功登录，
  // 折叠同 IP 的重复成功登录噪音。窗口函数需 MySQL 8 / MariaDB 10.2+。
  $subs = [];
  $params = [];
  if (($cat === '' || $cat === 'login') && $table_exists('loginlog')) {
      $subs[] = "SELECT time AS occur_time, 'login' AS category, password AS code1, '' AS code2, '' AS detail, '' AS amount, ip, CAST(log_id AS CHAR) AS ref
                   FROM (
                     SELECT log_id, time, password, ip,
                       ROW_NUMBER() OVER (PARTITION BY user_id, ip, password ORDER BY time ASC)  AS first_seen,
                       ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY time DESC)              AS latest
                     FROM loginlog WHERE user_id = ?
                   ) lf
                  WHERE password = 'login fail'
                     OR (password = 'login ok' AND (first_seen = 1 OR latest = 1))";
      $params[] = $target_user;
  }
  if (($cat === '' || $cat === 'point') && $table_exists('point_log')) {
      $subs[] = "SELECT create_time AS occur_time, 'point' AS category, CAST(type AS CHAR) AS code1, '' AS code2, IFNULL(remark,'') AS detail, CAST(change_point AS CHAR) AS amount, '' AS ip, CAST(id AS CHAR) AS ref FROM point_log WHERE user_id = ?";
      $params[] = $target_user;
  }
  if (($cat === '' || $cat === 'submit') && $table_exists('solution')) {
      $subs[] = "SELECT in_date AS occur_time, 'submit' AS category, CAST(result AS CHAR) AS code1, '' AS code2, CAST(problem_id AS CHAR) AS detail, '' AS amount, ip, CAST(solution_id AS CHAR) AS ref FROM solution WHERE user_id = ?";
      $params[] = $target_user;
  }
  if (($cat === '' || $cat === 'course') && $table_exists('course_order')) {
      $subs[] = "SELECT co.pay_time AS occur_time, 'course' AS category, co.pay_channel AS code1, CAST(co.license_type AS CHAR) AS code2, IFNULL(c.title,'') AS detail, CAST(co.amount AS CHAR) AS amount, '' AS ip, co.order_no AS ref FROM course_order co LEFT JOIN course c ON co.course_id = c.id WHERE co.user_id = ? AND co.pay_status = 1";
      $params[] = $target_user;
  }

  $where = ' WHERE 1=1';
  if (preg_match('/^\d{4}-\d{2}-\d{2}/', $from)) { $where .= ' AND occur_time >= ?'; $params[] = $from . ' 00:00:00'; }
  if (preg_match('/^\d{4}-\d{2}-\d{2}/', $to))   { $where .= ' AND occur_time <= ?'; $params[] = $to . ' 23:59:59'; }

  $union = implode(' UNION ALL ', $subs);
  $count_sql = "SELECT COUNT(*) AS total FROM ($union) t $where";
  $count_row = pdo_query($count_sql, $params);
  $total = isset($count_row[0]['total']) ? intval($count_row[0]['total']) : 0;
  $total_pages = $total > 0 ? (int)ceil($total / $per_page) : 0;
  if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
  $offset = ($page - 1) * $per_page;

  $list_sql = "SELECT * FROM ($union) t $where ORDER BY occur_time DESC LIMIT $per_page OFFSET $offset";
  $rows = pdo_query($list_sql, $params);
  if (!is_array($rows)) $rows = [];
  ?>

  <div style="margin-bottom:8px;color:#999;">共 <?php echo $total; ?> 条记录</div>
  <table class="table table-bordered table-condensed">
    <thead>
      <tr>
        <th style="width:150px;">时间</th>
        <th style="width:70px;">类别</th>
        <th>操作详情</th>
        <th style="width:140px;">IP</th>
        <th style="width:160px;">关联</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="5" style="text-align:center;color:#999;">暂无操作记录</td></tr>
      <?php else: foreach ($rows as $r):
          $summary = '';
          switch ($r['category']) {
              case 'login':
                  if ($r['code1'] === 'login fail') {
                      $summary = '登录失败';
                  } else if ($r['code1'] === 'login ok') {
                      $summary = '登录成功';
                  } else {
                      $summary = $r['code1'];
                  }
                  break;
              case 'point':
                  $tname = isset($point_type_map[$r['code1']]) ? $point_type_map[$r['code1']] : '未知类型';
                  $chg = intval($r['amount']);
                  $sign = $chg > 0 ? '+' : '';
                  $summary = $tname . ' ' . $sign . $chg . ' 积分';
                  if ($r['detail'] !== '') $summary .= '（' . $r['detail'] . '）';
                  break;
              case 'submit':
                  $rlabel = isset($judge_result[$r['code1']]) ? $judge_result[$r['code1']] : ('结果码' . $r['code1']);
                  $summary = '提交题目 #' . $r['detail'] . ' → ' . $rlabel;
                  break;
              case 'course':
                  $lic = isset($license_map[$r['code2']]) ? $license_map[$r['code2']] : '权限';
                  $ch  = isset($channel_map[$r['code1']]) ? $channel_map[$r['code1']] : $r['code1'];
                  $amt = ($r['code1'] === 'point' && $r['amount'] !== '') ? (' ' . $r['amount'] . ' 积分') : '';
                  $summary = '获取课件' . ($r['detail'] !== '' ? '《' . $r['detail'] . '》' : '') . ' ' . $lic . '（' . $ch . $amt . '）';
                  break;
          }
          $badge = isset($cat_color[$r['category']]) ? $cat_color[$r['category']] : 'label label-default';
          $btext = isset($cat_label[$r['category']]) ? $cat_label[$r['category']] : $r['category'];
      ?>
        <tr>
          <td><?php echo htmlentities((string)$r['occur_time'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><span class="<?php echo $badge; ?>"><?php echo htmlentities($btext, ENT_QUOTES, 'UTF-8'); ?></span></td>
          <td><?php echo htmlentities($summary, ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlentities((string)$r['ip'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlentities((string)$r['ref'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <?php if ($total_pages > 1):
      $base = 'user_action_log.php?user_id=' . urlencode($target_user)
            . '&cat=' . urlencode($cat)
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
