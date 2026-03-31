<?php
/**
 * 支付签名测试
 */

require_once __DIR__ . '/PaymentHelper.php';

// 测试结果统计
$pass_count = 0;
$fail_count = 0;

// 测试断言函数
function assert_test($condition, $test_name) {
    global $pass_count, $fail_count;
    if ($condition) {
        echo "[PASS] $test_name\n";
        $pass_count++;
    } else {
        echo "[FAIL] $test_name\n";
        $fail_count++;
    }
}

// 等值断言函数
function assert_equals($actual, $expected, $test_name) {
    assert_test($actual === $expected, $test_name);
}

echo "=== 支付签名测试 ===\n\n";

// ========== 签名生成测试 ==========
echo "--- 签名生成 ---\n";
$key = 'test_key_12345';

$params1 = ['out_trade_no' => 'ORDER001', 'total_fee' => '1990'];
$sign1 = generate_yungouos_sign($params1, $key);
echo "生成签名1: $sign1\n";
assert_test(strlen($sign1) === 32, "签名生成 - MD5长度32位");
assert_test(ctype_upper($sign1), "签名生成 - 大写格式");

$params2 = ['total_fee' => '1990', 'out_trade_no' => 'ORDER001'];
$sign2 = generate_yungouos_sign($params2, $key);
assert_equals($sign1, $sign2, "签名生成 - 排序一致性");

// ========== 签名验证测试 ==========
echo "\n--- 签名验证 ---\n";
$remote_sign = $sign1;
assert_test(verify_yungouos_sign($params1, $key, $remote_sign), "签名验证 - 正确签名通过");

$wrong_sign = 'WRONGSIGN000000000000000000000000';
assert_test(!verify_yungouos_sign($params1, $key, $wrong_sign), "签名验证 - 错误签名失败");

$wrong_key = 'wrong_key_67890';
assert_test(!verify_yungouos_sign($params1, $wrong_key, $remote_sign), "签名验证 - 错误密钥失败");

// ========== 空值过滤测试 ==========
echo "\n--- 空值过滤 ---\n";
$params_with_null = [
    'out_trade_no' => 'ORDER001',
    'total_fee' => '1990',
    'empty_field' => '',
    'null_field' => null,
];
$sign_filtered = generate_yungouos_sign($params_with_null, $key);
$sign_base = generate_yungouos_sign(['out_trade_no' => 'ORDER001', 'total_fee' => '1990'], $key);
assert_equals($sign_filtered, $sign_base, "空值过滤 - 空字符串被过滤");

$params_with_sign = [
    'out_trade_no' => 'ORDER001',
    'total_fee' => '1990',
    'sign' => 'DUMMY_SIGN',
];
$sign_without_sign = generate_yungouos_sign($params_with_sign, $key);
assert_equals($sign_without_sign, $sign_base, "空值过滤 - sign参数被过滤");

// ========== 金额校验测试 ==========
echo "\n--- 金额校验 ---\n";
assert_test(validate_amount(19.90, 19.90), "金额校验 - 精确相等");
assert_test(validate_amount(19.90, 19.91), "金额校验 - 0.01误差内");
assert_test(validate_amount(19.90, 19.89), "金额校验 - 0.01误差内(负)");
assert_test(validate_amount(100.00, 100.005), "金额约 - 0.005误差内");
assert_test(!validate_amount(19.90, 20.00), "金额校验 - 0.10误差超过阈值");
assert_test(!validate_amount(19.90, 19.80), "金额校验 - -0.10误差超过阈值");

// 特殊情况：浮点数精度
assert_test(validate_amount(0.1 + 0.2, 0.3), "金额校验 - 浮点数精度问题");

// ========== 输出汇总 ==========
echo "\n总计: $pass_count 通过, $fail_count 失败\n";

// 返回退出码
exit($fail_count > 0 ? 1 : 0);
