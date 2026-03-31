<?php
/**
 * YunGouOS 支付回调处理
 * 接收支付平台的异步通知，验证签名并更新订单状态
 */

require_once('./include/db_info.inc.php');
require_once('./include/course_mail.php');

/**
 * YunGouOS 签名验证函数
 * @param array $params 参数数组
 * @param string $key 商户密钥
 * @param string $remote_sign 远程签名
 * @return bool
 */
function verify_yungouos_sign($params, $key, $remote_sign) {
    // 过滤空值和sign参数
    $filtered = array();
    foreach ($params as $k => $v) {
        if ($v !== '' && $v !== null && $k != 'sign') {
            $filtered[$k] = $v;
        }
    }
    // 按字母顺序排序
    ksort($filtered);
    // 拼接字符串
    $string = '';
    foreach ($filtered as $k => $v) {
        $string .= "$k=$v&";
    }
    $string = trim($string, '&') . $key;
    // MD5加密并转大写
    $local_sign = strtoupper(md5($string));
    return $local_sign === $remote_sign;
}

// 记录回调日志
function log_notify($message) {
    $log_file = '/tmp/course_notify.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// 获取POST参数
$out_trade_no = isset($_POST['out_trade_no']) ? trim($_POST['out_trade_no']) : '';
$total_fee = isset($_POST['total_fee']) ? floatval($_POST['total_fee']) / 100 : 0; // 转换为元
$trade_no = isset($_POST['trade_no']) ? trim($_POST['trade_no']) : '';
$code = isset($_POST['code']) ? intval($_POST['code']) : 0;
$sign = isset($_POST['sign']) ? trim($_POST['sign']) : '';

// 记录原始请求
log_notify("收到回调: out_trade_no=$out_trade_no, total_fee=$total_fee, trade_no=$trade_no, code=$code");

// 验证签名
if (!verify_yungouos_sign($_POST, $YUNGOUOS_KEY, $sign)) {
    log_notify("签名验证失败: out_trade_no=$out_trade_no");
    echo 'FAIL';
    exit();
}

// 检查支付状态
if ($code != 1) {
    log_notify("支付未成功: out_trade_no=$out_trade_no, code=$code");
    echo 'FAIL';
    exit();
}

// 查询订单
$order_sql = "SELECT * FROM course_order WHERE order_no = ?";
$order_result = pdo_query($order_sql, $out_trade_no);

if (empty($order_result)) {
    log_notify("订单不存在: out_trade_no=$out_trade_no");
    echo 'FAIL';
    exit();
}

$order = $order_result[0];

// 幂等处理：已支付的订单不重复处理
if ($order['pay_status'] == 1) {
    log_notify("订单已支付，跳过处理: out_trade_no=$out_trade_no");
    echo 'SUCCESS';
    exit();
}

// 验证金额
if (abs(floatval($order['amount']) - $total_fee) > 0.01) {
    log_notify("金额不匹配: 订单金额={$order['amount']}, 回调金额=$total_fee, out_trade_no=$out_trade_no");
    echo 'FAIL';
    exit();
}

// 更新订单状态
try {
    pdo_query(
        "UPDATE course_order SET pay_status = 1, pay_time = NOW(), trade_no = ? WHERE order_no = ?",
        $trade_no, $out_trade_no
    );
    log_notify("订单状态已更新: out_trade_no=$out_trade_no");

    // 查询课程信息
    $course_sql = "SELECT * FROM course WHERE id = ?";
    $course_result = pdo_query($course_sql, $order['course_id']);

    if (!empty($course_result)) {
        $course = $course_result[0];

        // 发送邮件
        $course_data = array(
            'title' => $course['title'],
            'courseware_link' => $course['courseware_link'],
            'courseware_code' => $course['courseware_code'],
            'lesson_plan_link' => $course['lesson_plan_link'],
            'lesson_plan_code' => $course['lesson_plan_code']
        );

        if (send_course_mail($order['email'], $course_data)) {
            pdo_query(
                "UPDATE course_order SET mail_status = 1, mail_sent_at = NOW() WHERE order_no = ?",
                $out_trade_no
            );
            log_notify("邮件发送成功: out_trade_no=$out_trade_no, email={$order['email']}");
        } else {
            pdo_query(
                "UPDATE course_order SET mail_status = 2 WHERE order_no = ?",
                $out_trade_no
            );
            log_notify("邮件发送失败: out_trade_no=$out_trade_no");
        }
    }

    echo 'SUCCESS';
} catch (Exception $e) {
    log_notify("处理失败: " . $e->getMessage());
    echo 'FAIL';
}
