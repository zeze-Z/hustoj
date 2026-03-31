<?php
require("../admin-header.php");
require_once("../../include/set_get_key.php");

// 强制加载语言文件
if (isset($OJ_LANG)) {
    require_once("../../lang/$OJ_LANG.php");
}

// 权限检查：仅管理员可访问
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../../loginpage.php'>Please Login First!</a>";
    exit(1);
}

// 处理筛选条件
$pay_status_filter = isset($_GET['pay_status']) ? intval($_GET['pay_status']) : -1;

// 构建查询条件
$where_conditions = array();
if ($pay_status_filter >= 0) {
    $where_conditions[] = "o.pay_status = $pay_status_filter";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// 查询总数
$sql = "SELECT COUNT(*) AS ids FROM `course_order` o $where_clause";
try {
    $result = pdo_query($sql);
    $row = $result[0];
    $ids = intval($row['ids']);
} catch (Exception $e) {
    echo "<script>alert('数据库查询失败: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "'); history.go(-1);</script>";
    exit(1);
}

// 统计数据
$stats_sql = "SELECT
    COUNT(*) as total_orders,
    SUM(CASE WHEN pay_status = 1 THEN amount ELSE 0 END) as total_income
    FROM course_order o";
$stats_result = pdo_query($stats_sql);
$stats = $stats_result[0];
$total_orders = intval($stats['total_orders']);
$total_income = floatval($stats['total_income']);

$idsperpage = 25;
$pages = intval(ceil($ids / $idsperpage));

if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
} else {
    $page = 1;
}

$pagesperframe = 5;
$frame = intval(ceil($page / $pagesperframe));

$spage = ($frame - 1) * $pagesperframe + 1;
$epage = min($spage + $pagesperframe - 1, $pages);
$sid = ($page - 1) * $idsperpage;

// 查询订单列表
$sql = "SELECT o.*, c.title as course_title
        FROM `course_order` o
        LEFT JOIN `course` c ON o.course_id = c.id
        $where_clause
        ORDER BY o.id DESC
        LIMIT $sid, $idsperpage";
try {
    $result = pdo_query($sql);
} catch (Exception $e) {
    echo "<script>alert('数据库查询失败: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "'); history.go(-1);</script>";
    exit(1);
}

// 定义支付状态映射
$pay_status_map = array(
    0 => '待支付',
    1 => '已支付'
);

// 定义邮件状态映射
$mail_status_map = array(
    0 => '未发送',
    1 => '已发送',
    2 => '发送失败'
);

// 定义支付渠道映射
$pay_channel_map = array(
    'free' => '免费',
    'wxpay' => '微信',
    'alipay' => '支付宝'
);
?>

<title><?php echo "课程订单" ?></title>
<hr>
<center><h3>课程订单</h3></center>

<div class="padding">
    <!-- 筛选条件 -->
    <div style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
        <form method="GET" action="order.php" style="display: inline;">
            <label>支付状态：
                <select name="pay_status" style="padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="-1" <?php echo $pay_status_filter == -1 ? 'selected' : ''; ?>>全部</option>
                    <option value="0" <?php echo $pay_status_filter == 0 ? 'selected' : ''; ?>>待支付</option>
                    <option value="1" <?php echo $pay_status_filter == 1 ? 'selected' : ''; ?>>已支付</option>
                </select>
            </label>
            <button type="submit" class="ui primary small button">筛选</button>
            <a href="order.php" class="ui secondary small button">重置</a>
        </form>
    </div>

    <!-- 统计信息 -->
    <div style="margin-bottom: 15px; padding: 10px; background: #e8f4fd; border-radius: 8px; border-left: 4px solid #2185d0;">
        <strong>统计：</strong>
        总订单数：<span style="color: #2185d0;"><?php echo $total_orders; ?></span>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        总收入：<span style="color: #e64a19; font-weight: bold;">¥<?php echo number_format($total_income, 2); ?></span>
    </div>

    <center>
        <table width="100%" border="1" style="text-align:center; border-collapse: collapse;">
            <tr style='height:30px; background: #f5f5f5;'>
                <td>ID</td>
                <td>订单号</td>
                <td>用户ID</td>
                <td>课程名称</td>
                <td>金额</td>
                <td>支付渠道</td>
                <td>支付状态</td>
                <td>邮件状态</td>
                <td>创建时间</td>
                <td>操作</td>
            </tr>
            <?php
            foreach ($result as $row) {
                $row_bg = ($row['pay_status'] == 0) ? '#ffebee' : '#fff';
                $mail_resend_btn = ($row['pay_status'] == 1 && $row['mail_status'] != 1)
                    ? '<a href="order_resend.php?id=' . $row['id'] . '" class="ui orange mini button" onclick="return confirm(\'确定重新发送邮件？\');">手动重发</a>'
                    : '';
            ?>
            <tr style='height:30px; background: <?php echo $row_bg; ?>;' order_id='<?php echo $row['id'] ?>'>
                <td><?php echo $row['id'] ?></td>
                <td><?php echo htmlentities($row['order_no'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php echo htmlentities($row['user_id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php echo htmlentities($row['course_title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>¥<?php echo number_format(floatval($row['amount']), 2) ?></td>
                <td><?php echo isset($pay_channel_map[$row['pay_channel']]) ? $pay_channel_map[$row['pay_channel']] : '-' ?></td>
                <td><?php echo $pay_status_map[$row['pay_status']] ?></td>
                <td><?php echo $mail_status_map[$row['mail_status']] ?></td>
                <td><?php echo $row['created_at'] ?></td>
                <td><?php echo $mail_resend_btn ?></td>
            </tr>
            <?php } ?>
        </table>

        <!-- 分页 -->
        <div style="margin-top: 15px;">
            <?php
            if ($page > 1) {
                echo '<a href="order.php?page=' . ($page - 1) . '&pay_status=' . $pay_status_filter . '" class="ui secondary small button">上一页</a>';
            }
            ?>
            <span style="margin: 0 10px;">第 <?php echo $page ?> / <?php echo $pages ?> 页</span>
            <?php
            if ($page < $pages) {
                echo '<a href="order.php?page=' . ($page + 1) . '&pay_status=' . $pay_status_filter . '" class="ui secondary small button">下一页</a>';
            }
            ?>
        </div>
    </center>
</div>

<?php require_once("../admin-footer.php");?>
