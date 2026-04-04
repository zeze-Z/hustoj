<?php
// PaymentHelper.php - 支付签名函数

function generate_sign($params, $key) {
    ksort($params);
    $parts = [];
    foreach ($params as $k => $v) {
        if ($v !== '' && $v !== null && $k !== 'sign') {
            $parts[] = "$k=$v";
        }
    }
    $string = implode('&', $parts) . '&key=' . $key;
    return strtoupper(md5($string));
}

function verify_sign($params, $key, $remote_sign) {
    return generate_sign($params, $key) === $remote_sign;
}

function validate_amount($order_amount, $callback_amount) {
    $order = round(floatval($order_amount) * 100);
    $callback = round(floatval($callback_amount) * 100);
    // 转为整数比较，容差 1 分
    return abs($order - $callback) <= 1;
}
