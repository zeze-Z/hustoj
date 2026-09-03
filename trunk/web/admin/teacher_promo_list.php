<?php
/**
 * 教师推广统计（管理端）
 * V2.7 — 管理员查看各教师推广数据，手动统计上周、手动发放奖励
 *
 * 权限：仅 administrator
 * CSRF：POST 操作走 check_post_key.php
 */
require("admin-header.php");
require_once("../include/my_func.inc.php");
require_once("../include/teacher_promo.php");

if (!isset($_SESSION[$OJ_NAME . '_' . 'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

$msg = '';
$msg_type = '';

// ===== POST 处理 =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once("../include/check_post_key.php");
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'gen_snapshot') {
        // 统计上周数据
        $result = teacher_promo_gen_snapshot();
        $msg_type = 'success';
        $msg = "统计完成：生成 {$result['generated']} 条，跳过 {$result['skipped']} 条";
    } elseif ($action === 'grant') {
        // 奖励推广（发分）
        $grant_teacher = isset($_POST['teacher_id']) ? trim($_POST['teacher_id']) : '';
        $grant_week    = isset($_POST['week_start']) ? trim($_POST['week_start']) : '';
        $result = teacher_promo_grant($grant_teacher, $grant_week);
        $msg_type = $result['success'] ? 'success' : 'error';
        $msg = $result['success']
            ? "发放成功：{$grant_teacher} +{$result['point']}分，余额 {$result['balance']}"
            : $result['message'];
    } else {
        $msg_type = 'error';
        $msg = '未知操作';
    }
}

// ===== 读取列表数据（只读，不触发统计）=====
$promo_list = get_teacher_promo_list();
$last_week_start = teacher_promo_last_week_start();
$last_week_end = date('Y-m-d', strtotime($last_week_start . " +6 days"));

// 生成 CSRF postkey（一次性 token，每页面加载生成一次，表单提交后 check_post_key 会消耗并重载）
// 注意：不能对多个表单分别 require_once set_post_key.php —— require_once 只首次执行，
// 后续表单拿不到 postkey 输入框会导致 CSRF 校验失败。这里统一生成后在每个表单直接 echo。
if (!isset($_SESSION[$OJ_NAME . '_' . 'postkey'])) {
    $_SESSION[$OJ_NAME . '_' . 'postkey'] = strtoupper(substr(MD5($_SESSION[$OJ_NAME . '_' . 'user_id'] . rand(0, 9999999)), 0, 10));
}
$postkey = $_SESSION[$OJ_NAME . '_' . 'postkey'];
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <title>教师推广统计</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 20px; }
        .alert { padding: 12px 18px; border-radius: 6px; margin-bottom: 18px; }
        .alert-success { background: #eafaf1; border: 1px solid #a9dfbf; color: #27ae60; }
        .alert-error   { background: #fdecea; border: 1px solid #f5c6cb; color: #c0392b; }
        .toolbar { margin-bottom: 18px; display: flex; align-items: center; gap: 16px; }
        .btn { padding: 8px 18px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #667eea; color: #fff; }
        .btn-primary:hover { background: #5a6fd6; }
        .btn-success { background: #27ae60; color: #fff; }
        .btn-success:hover { background: #219a52; }
        .btn:disabled { background: #ccc; color: #999; cursor: not-allowed; }
        .info-text { color: #888; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        th { background: #f0f0f5; padding: 10px 12px; text-align: left; font-size: 13px; color: #666; border-bottom: 1px solid #e0e0e0; }
        td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #333; }
        tr:hover td { background: #fafbff; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
        .badge-green  { background: #e8f8f0; color: #27ae60; }
        .badge-red    { background: #fdecea; color: #c0392b; }
        .badge-gray   { background: #f0f0f5; color: #888; }
        .badge-blue   { background: #e8f0fe; color: #667eea; }
        .empty-msg { text-align: center; padding: 40px; color: #aaa; font-size: 15px; }
    </style>
</head>
<body>
<div class="container">
    <h1>📢 教师推广统计</h1>

    <?php if ($msg) { ?>
    <div class="alert alert-<?php echo $msg_type === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php } ?>

    <div class="toolbar">
        <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="gen_snapshot">
            <button type="submit" class="btn btn-primary">📊 统计上周数据</button>
            <input type="hidden" name="postkey" value="<?php echo $postkey; ?>">
        </form>
        <span class="info-text">统计时间窗：<?php echo htmlentities($last_week_start, ENT_QUOTES, 'UTF-8'); ?> ~ <?php echo htmlentities($last_week_end, ENT_QUOTES, 'UTF-8'); ?>（上周一 ~ 上周日）</span>
    </div>

    <?php if (empty($promo_list)) { ?>
    <div class="empty-msg">暂无数据。请先点「统计上周数据」生成结算快照。</div>
    <?php } else { ?>
    <table>
        <thead>
            <tr>
                <th>教师</th>
                <th>关联学生数</th>
                <th>上周活跃数</th>
                <th>达标阈值</th>
                <th>达标状态</th>
                <th>周次</th>
                <th>发放状态</th>
                <th>累计推广积分</th>
                <th>结算周</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($promo_list as $row) {
            $tid = $row['teacher_id'];
            $nick = isset($row['nick']) ? $row['nick'] : '';
            $bound = intval($row['bound_count']);
            $active = intval($row['active_count']);
            $threshold = intval($row['threshold']);
            $qualified = intval($row['is_qualified']);
            $granted = intval($row['is_granted']);
            $week_no = intval($row['week_no']);
            $status = intval($row['status']);
            $total_granted = intval($row['total_granted']);
            $ws = $row['week_start'];

            // 达标状态标签
            if ($qualified) {
                $q_badge = '<span class="badge badge-green">✓ 达标</span>';
            } else {
                $q_badge = '<span class="badge badge-red">✗ 未达标</span> <span style="color:#999;font-size:12px;">(差' . ($threshold - $active) . '人)</span>';
            }

            // 周次
            $w_badge = '<span class="badge badge-blue">' . $week_no . '/' . TEACHER_PROMO_WEEKS . '</span>';

            // 发放/周期状态
            if ($granted) {
                $g_badge = '<span class="badge badge-green">已发放 +' . intval($row['grant_point']) . '</span>';
            } elseif ($status === TEACHER_PROMO_BROKEN) {
                $g_badge = '<span class="badge badge-red">已中断</span>';
            } elseif ($status === TEACHER_PROMO_COMPLETED) {
                $g_badge = '<span class="badge badge-gray">已完成</span>';
            } else {
                $g_badge = '<span class="badge badge-gray">待发放</span>';
            }

            // 按钮：达标且未发可点，其余置灰
            $can_grant = ($qualified && !$granted && $status !== TEACHER_PROMO_BROKEN && $status !== TEACHER_PROMO_COMPLETED);
        ?>
            <tr>
                <td><strong><?php echo htmlentities($tid, ENT_QUOTES, 'UTF-8'); ?></strong><?php if ($nick) echo ' / ' . htmlentities($nick, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo $bound; ?></td>
                <td><?php echo $active; ?></td>
                <td><?php echo $threshold; ?> <span style="color:#999;font-size:11px;">(<?php echo intval(TEACHER_PROMO_RATIO*100); ?>%)</span></td>
                <td><?php echo $q_badge; ?></td>
                <td><?php echo $w_badge; ?></td>
                <td><?php echo $g_badge; ?></td>
                <td><?php echo $total_granted; ?></td>
                <td style="font-size:12px;color:#888;"><?php echo htmlentities($ws, ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                <?php if ($can_grant) { ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="grant">
                        <input type="hidden" name="teacher_id" value="<?php echo htmlentities($tid, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="week_start" value="<?php echo htmlentities($ws, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-success">💰 奖励推广</button>
                        <input type="hidden" name="postkey" value="<?php echo $postkey; ?>">
                    </form>
                <?php } else { ?>
                    <button type="button" class="btn" disabled>💰 奖励推广</button>
                <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>
</div>
</body>
</html>
