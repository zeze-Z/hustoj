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
assert_true("价格校验 - 合法价格(0.00)",     validate_price(0.00));
assert_true("价格校验 - 合法价格(字符串数字)", validate_price('99.9'));
assert_true("价格校验 - 非法价格(-1)",       !validate_price(-1));
assert_true("价格校验 - 非法价格(-0.1)",     !validate_price(-0.1));
assert_true("价格校验 - 非法价格(非数字)",   !validate_price('abc'));
assert_true("价格校验 - 非法价格(null)",     !validate_price(null));
assert_true("价格校验 - 非法价格(空字符串)", !validate_price(''));

// 标签解析
assert_equal("标签解析 - 逗号分隔",
    array_values(parse_tags('入门,Python,机器学习学习')),
    ['入门', 'Python', '机器学习学习']
);
assert_equal("标签解析 - 空字符串返回空数组",
    parse_tags(''),
    []
);
assert_equal("标签解析 - 纯空格返回空数组",
    parse_tags('   '),
    []
);
assert_equal("标签解析 - 去除首尾空格",
    array_values(parse_tags(' 入门 , Python ')),
    ['入门', 'Python']
);
assert_equal("标签解析 - 过滤空标签",
    array_values(parse_tags('a,,b')),
    ['a', 'b']
);
assert_equal("标签解析 - 单个标签",
    array_values(parse_tags('Python')),
    ['Python']
);

// 免费课程判断
assert_true("免费课程判断 - price=0 为免费",  is_free(0));
assert_true("免费课程判断 - price=0.00 为免费", is_free(0.00));
assert_true("免费课程判断 - price='0' 为免费", is_free('0'));
assert_true("免费课程判断 - price=19.9 非免费", !is_free(19.9));
assert_true("免费课程判断 - price=0.01 非免费", !is_free(0.01));

// URL 校验
assert_true("URL校验 - 合法https URL",     validate_url('https://www.kdocs.cn/l/abc123'));
assert_true("URL校验 - 合法http URL",      validate_url('http://example.com/path'));
assert_true("URL校验 - 非法URL",           !validate_url('not-a-url'));
assert_true("URL校验 - 空字符串",          !validate_url(''));
assert_true("URL校验 - 缺少协议",          !validate_url('www.example.com'));

// kdocs 域名校验
assert_true("kdocs域名校验 - 合法kdocs链接(www)",  validate_kdocs_url('https://www.kdocs.cn/l/abc'));
assert_true("kdocs域名校验 - 合法kdocs链接(无www)", validate_kdocs_url('https://kdocs.cn/l/abc'));
assert_true("kdocs域名校验 - 合法kdocs链接(http)",  validate_kdocs_url('http://kdocs.cn/l/abc'));
assert_true("kdocs域名校验 - 非kdocs域名",        !validate_kdocs_url('https://evil.com/fake'));
assert_true("kdocs域名校验 - kdocs子域名",       !validate_kdocs_url('https://evil.kdocs.com/l/abc'));
assert_true("kdocs域名校验 - 空值允许",           validate_kdocs_url(''));
assert_true("kdocs域名校验 - 缺少路径",           !validate_kdocs_url('https://www.kdocs.cn'));

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
assert_true("签名生成 - 相同参数生成相同签名",
    generate_sign($params, $key) === $sign
);

// 签名验证
assert_true("签名验证 - 正确签名验证通过",
    verify_sign($params, $key, $sign)
);
assert_true("签名验证 - 错误签名验证失败",
    !verify_sign($params, $key, 'WRONGSIGN00000000000000000000000')
);
assert_true("签名验证 - 空签名验证失败",
    !verify_sign($params, $key, '')
);

// 空值过滤
$params_with_empty = array_merge($params, ['empty_field' => '', 'sign' => 'old_sign']);
$sign2 = generate_sign($params_with_empty, $key);
assert_equal("签名生成 - 空值和sign字段被过滤，结果与原签名一致",
    $sign2, $sign
);

// null值过滤
$params_with_null = array_merge($params, ['null_field' => null]);
$sign3 = generate_sign($params_with_null, $key);
assert_equal("签名生成 - null值被过滤",
    $sign3, $sign
);

// 参数排序验证
$params_unordered = ['z' => '1', 'a' => '2', 'm' => '3'];
$sign_ordered = generate_sign($params_unordered, $key);
$params_reordered = ['m' => '3', 'a' => '2', 'z' => '1'];
$sign_reordered = generate_sign($params_reordered, $key);
assert_true("签名生成 - 参数按字典序排序",
    $sign_ordered === $sign_reordered
);

// 金额校验
assert_true("金额校验 - 误差0.00合法",   validate_amount(19.90, 19.90));
assert_true("金额校验 - 误差0.01合法",   validate_amount(19.90, 19.91));
assert_true("金额校验 - 误差0.02非法",   !validate_amount(19.90, 19.92));
assert_true("金额校验 - 金额被篡改非法", !validate_amount(19.90, 1.00));
assert_true("金额校验 - 字符串数字比较", validate_amount('19.90', '19.91'));
assert_true("金额校验 - 负数差异非法",   !validate_amount(19.90, -1.00));

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

// HTML结构验证
assert_true("邮件内容 - 包含html标签",        strpos($body, '<html>') !== false);
assert_true("邮件内容 - 包含body标签",        strpos($body, '<body>') !== false);

// XSS防护验证
$course_xss = [
    'title'           => '<script>alert(1)</script>',
    'courseware_link' => 'https://example.com',
    'courseware_code' => 'code',
    'lesson_plan_link'=> 'https://example.com',
    'lesson_plan_code'=> 'code',
];
$body_xss = build_mail_body($course_xss, 365);
assert_true("邮件内容 - XSS防护 - script标签被转义",
    strpos($body_xss, '<script>') === false
);
assert_true("邮件内容 - XSS防护 - 包含转义后的安全内容",
    strpos($body_xss, '&lt;script&gt;') !== false
);

// 默认有效期测试
$body_default = build_mail_body($course);
assert_true("邮件内容 - 默认有效期365天", strpos($body_default, '365') !== false);

// 缺失字段测试
$course_partial = [
    'title' => '测试课程',
];
$body_partial = build_mail_body($course_partial, 30);
assert_true("邮件内容 - 缺失字段也能生成", strlen($body_partial) > 0);
assert_true("邮件内容 - 部分字段有效期正确", strpos($body_partial, '30') !== false);

// =============================================
echo "\n=== 测试汇总 ===\n";
echo "通过: $pass  失败: $fail  总计: " . ($pass + $fail) . "\n";
exit($fail > 0 ? 1 : 0);
