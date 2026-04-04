<?php
/**
 * 支付宝支付回调处理
 * 接收支付宝的异步通知，验证签名并更新订单状态
 */

require_once('./include/db_info.inc.php');
require_once('./include/course_mail.php');

/**
 * 支付宝签名验证函数
 * @param array $params 参数数组
 * @param string $public_key 支付宝公钥
 * @param string $remote_sign 远程签名
 * @return bool
 */
function verify_alipay_sign($params, $public_key, $remote_sign) {
    // 过滤空值、sign和sign_type参数
    $filtered = array();
    foreach ($params as $k => $v) {
        if ($v !== '' && $v !== null && $k != 'sign' && $k != 'sign_type') {
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
    $string = trim($string, '&');

    // 验证签名
    $public_key = str_replace(["\r\n", "\n", "\r"], '', $public_key);
    $remote_sign = base64_decode($remote_sign);
    return openssl_verify($string, $remote_sign, $public_key, OPENSSL_ALGO_SHA256) === 1;
}

// 记录回调日志
function log_notify($message) {
    $log_file = '/tmp/course_notify.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// 获取请求参数（支付宝可能是POST或GET）
$params = array_merge($_GET, $_POST);
$out_trade_no = isset($params['out_trade_no']) ? trim($params['out_trade_no']) : '';
$total_amount = isset($params['total_amount']) ? floatval($params['total_amount']) : 0;
$trade_no = isset($params['trade_no']) ? trim($params['trade_no']) : '';
$trade_status = isset($params['trade_status']) ? trim($params['trade_status']) : '';
$app_id = isset($params['app_id']) ? trim($params['app_id']) : '';
$sign = isset($params['sign']) ? trim($params['sign']) : '';

// 记录原始请求
log_notify("收到支付宝回调: out_trade_no=$out_trade_no, total_amount=$total_amount, trade_no=$trade_no, trade_status=$trade_status, app_id=$app_id");

// 验证签名
if (empty($sign) || !verify_alipay_sign($params, $ALIPAY_PUBLIC_KEY, $sign)) {
    log_notify("支付宝签名验证失败: out_trade_no=$out_trade_no");
    echo 'FAIL';
    exit();
}

// 验证APP_ID
if ($app_id !== $ALIPAY_APP_ID) {
    log_notify("APP_ID不匹配: 回调APP_ID=$app_id, 配置APP_ID=$ALIPAY_APP_ID");
    echo 'FAIL';
    exit();
}

// 检查支付状态
if ($trade_status != 'TRADE_SUCCESS' && $trade_status != 'TRADE_FINISHED') {
    log_notify("支付未成功: out_trade_no=$out_trade_no, trade_status=$trade_status");
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
$order_amount = floatval($order['amount']);
if (abs($order_amount - $total_amount) > 0.01) {
    log_notify("金额不匹配: 订单金额=$order_amount, 回调金额=$total_amount, out_trade_no=$out_trade_no");
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
