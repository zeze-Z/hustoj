<?php
/**
 * 课程数据模型测试
 */

require_once __DIR__ . '/CourseModel.php';

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

echo "=== 课程数据模型测试 ===\n\n";

// ========== 价格校验测试 ==========
echo "--- 价格校验 ---\n";
assert_test(validate_price(0), "价格校验 - 合法价格(0)");
assert_test(validate_price(19.9), "价格校验 - 合法价格(19.9)");
assert_test(validate_price(100), "价格校验 - 合法价格(100)");
assert_test(!validate_price(-1), "价格校验 - 非法价格(-1)");
assert_test(!validate_price(-0.01), "价格校验 - 非法价格(-0.01)");

// ========== 标签解析测试 ==========
echo "\n--- 标签解析 ---\n";
$tags1 = parse_tags('入门,Python,机器学习');
assert_test($tags1 === ['入门', 'Python', '机器学习'], "标签解析 - 逗号分隔");

$tags2 = parse_tags('编程');
assert_test($tags2 === ['编程'], "标签解析 - 单个标签");

$tags3 = parse_tags('');
assert_test($tags3 === [], "标签解析 - 空字符串");

$tags4 = parse_tags('  入门 ,  Python  ');
assert_test($tags4 === ['入门', 'Python'], "标签解析 - 带空格");

// ========== 免费课程判断测试 ==========
echo "\n--- 免费课程判断 ---\n";
assert_test(is_free_course(0), "免费课程判断 - 0元为免费");
assert_test(!is_free_course(0.01), "免费课程判断 - 0.01元非免费");
assert_test(!is_free_course(19.9), "免费课程判断 - 19.9元非免费");

// ========== URL 校验测试 ==========
echo "\n--- URL校验 ---\n";
assert_test(validate_url('https://www.example.com'), "URL校验 - https URL");
assert_test(validate_url('http://example.com/path'), "URL校验 - http URL");
assert_test(validate_url('https://kdocs.cn/l/example'), "URL校验 - kdocs URL");
assert_test(!validate_url('not-a-url'), "URL校验 - 非法字符串");
assert_test(!validate_url(''), "URL校验 - 空字符串");

// ========== kdocs 域名校验测试 ==========
echo "\n--- kdocs域名校验 ---\n";
assert_test(validate_kdocs_url('https://kdocs.cn/l/example'), "kdocs校验 - 主域名");
assert_test(validate_kdocs_url('https://share.kdocs.cn/l/example'), "kdocs校验 - 子域名");
assert_test(validate_kdocs_url('https://docs.kdocs.cn/l/example'), "kdocs校验 - docs子域名");
assert_test(!validate_kdocs_url('https://www.example.com'), "kdocs校验 - 非kdocs域名");
assert_test(!validate_kdocs_url('not-a-url'), "kdocs校验 - 非法URL");

// ========== 输出汇总 ==========
echo "\n总计: $pass_count 通过, $fail_count 失败\n";

// 返回退出码
exit($fail_count > 0 ? 1 : 0);
