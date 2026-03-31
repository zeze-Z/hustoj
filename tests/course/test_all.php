<?php
// test_all.php - 课件商城模块单元测试

require_once __DIR__ . '/CourseModel.php';
require_once __DIR__ . '/PaymentHelper.php';
require_once __DIR__ . '/MailHelper.php';

$pass = 0;
$fail = 0;

function assert_true($desc, $result) {
    global $pass, $fail;
    if ($result) {
        echo "[PASS] $desc\n";
        $pass++;
    } else {
        echo "[FAIL] $desc\n";
        $fail++;
    }
}

function assert_equal($desc, $actual, $expected) {
    global $pass, $fail;
    if ($actual === $expected) {
        echo "[PASS] $desc\n";
        $pass++;
    } else {
        echo "[FAIL] $desc (期望: " . var_export($expected, true) . ", 实际: " . var_export($actual, true) . ")\n";
        $fail++;
    }
}

// =============================================
echo "\n=== 课程数据模型测试 ===\n";
// =============================================

// 价格校验
assert_true("价格校验 - 合法价格(19.9)",    validate_price(19.9));
assert_true("价格校验 - 合法价格(0)",        validate_price(0));
assert_true("价格校验 - 非法价格(-1)",       !validate_price(-1));
assert_true("价格校验 - 非法价格(非数字)",   !validate_price('abc'));

// 标签解析
assert_equal("标签解析 - 逗号分隔",
    array_values(parse_tags('入门,Python,机器学习')),
    ['入门', 'Python', '机器学习']
);
assert_equal("标签解析 - 空字符串返回空数组",
    parse_tags(''),
    []
);
assert_equal("标签解析 - 去除首尾空格",
    array_values(parse_tags(' 入门 , Python ')),
    ['入门', 'Python']
);

// 免费课程判断
assert_true("免费课程判断 - price=0 为免费",  is_free(0));
assert_true("免费课程判断 - price=0.00 为免费", is_free(0.00));
assert_true("免费课程判断 - price=19.9 非免费", !is_free(19.9));

// URL 校验
assert_true("URL校验 - 合法URL",             validate_url('https://www.kdocs.cn/l/abc123'));
assert_true("URL校验 - 非法URL",             !validate_url('not-a-url'));
assert_true("URL校验 - 空字符串",            !validate_url(''));

// kdocs 域名校验
assert_true("kdocs域名校验 - 合法kdocs链接",  validate_kdocs_url('https://www.kdocs.cn/l/abc'));
assert_true("kdocs域名校验 - 非kdocs域名",    !validate_kdocs_url('https://evil.com/fake'));
assert_true("kdocs域名校验 - 空值允许",       validate_kdocs_url(''));

// =============================================
echo "\n=== 支付签名测试 ===\n";
// =============================================

$key = 'test_secret_key';
$params = [
    'out_trade_no' => 'ORDER20260331001',
    'total_fee'    => '1990',
    'body'         => '课程购买',
    'mch_id'       => '10001',
];

$sign = generate_sign($params, $key);

// 签名生成
assert_true("签名生成 - 返回32位大写字符串",
    strlen($sign) === 32 && strtoupper($sign) === $sign
);

// 签名验证
assert_true("签名验证 - 正确签名验证通过",
    verify_sign($params, $key, $sign)
);
assert_true("签名验证 - 错误签名验证失败",
    !verify_sign($params, $key, 'WRONGSIGN00000000000000000000000')
);

// 空值过滤
$params_with_empty = array_merge($params, ['empty_field' => '', 'sign' => 'old_sign']);
$sign2 = generate_sign($params_with_empty, $key);
assert_equal("签名生成 - 空值和sign字段被过滤，结果与原签名一致",
    $sign2, $sign
);

// 金额校验
assert_true("金额校验 - 误差0.00合法",   validate_amount(19.90, 19.90));
assert_true("金额校验 - 误差0.01合法",   validate_amount(19.90, 19.91));
assert_true("金额校验 - 误差0.02非法",   !validate_amount(19.90, 19.92));
assert_true("金额校验 - 金额被篡改非法", !validate_amount(19.90, 1.00));

// =============================================
echo "\n=== 邮件内容测试 ===\n";
// =============================================

$course = [
    'title'           => '第1课：什么是人工智能',
    'courseware_link' => 'https://pan.baidu.com/s/abc123',
    'courseware_code' => 'abcd',
    'lesson_plan_link'=> 'https://pan.baidu.com/s/def456',
    'lesson_plan_code'=> 'efgh',
];

$body = build_mail_body($course, 365);

assert_true("邮件内容 - 包含课程名称",       strpos($body, '第1课：什么是人工智能') !== false);
assert_true("邮件内容 - 包含课件下载链接",   strpos($body, 'pan.baidu.com/s/abc123') !== false);
assert_true("邮件内容 - 包含课件提取码",     strpos($body, 'abcd') !== false);
assert_true("邮件内容 - 包含教案下载链接",   strpos($body, 'pan.baidu.com/s/def456') !== false);
assert_true("邮件内容 - 包含教案提取码",     strpos($body, 'efgh') !== false);
assert_true("邮件内容 - 包含有效期说明",     strpos($body, '365') !== false);
assert_true("邮件内容 - 包含版权提示",       strpos($body, '请勿传播') !== false);

// =============================================
echo "\n=== 测试汇总 ===\n";
echo "通过: $pass  失败: $fail  总计: " . ($pass + $fail) . "\n";
exit($fail > 0 ? 1 : 0);
