<?php
/**
 * 邮件内容测试
 */

require_once __DIR__ . '/MailContentGenerator.php';

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

// 字符串包含断言
function assert_contains($haystack, $needle, $test_name) {
    assert_test(strpos($haystack, $needle) !== false, $test_name);
}

echo "=== 邮件内容测试 ===\n\n";

// 准备测试数据
$course_data = [
    'title' => 'Python编程入门教程',
    'courseware_link' => 'https://kdocs.cn/l/python_courseware',
    'courseware_code' => 'PY123',
    'lesson_plan_link' => 'https://kdocs.cn/l/python_lesson',
    'lesson_plan_code' => 'PL456',
];

$oj_name = '我的OJ';
$valid_days = 30;

// ========== 生成邮件内容 ==========
echo "--- 邮件内容生成 ---\n";
$content = generate_course_mail_content($course_data, $oj_name, $valid_days);
$subject = generate_course_mail_subject($course_data['title'], $oj_name);

// ========== 邮件主题测试 ==========
echo "\n--- 邮件主题 ---\n";
assert_contains($subject, '[我的OJ]', "邮件主题 - 包含平台名称");
assert_contains($subject, 'Python编程入门教程', "邮件主题 - 包含课程名称");

// ========== 邮件内容测试 ==========
echo "\n--- 邮件内容 ---\n";

// 1. 包含课程名称
assert_contains($content, 'Python编程入门教程', "邮件内容 - 包含课程名称");

// 2. 包含课件下载链接
assert_contains($content, $course_data['courseware_link'], "邮件内容 - 包含课件下载链接");

// 3. 包含教案下载链接
assert_contains($content, $course_data['lesson_plan_link'], "邮件内容 - 包含教案下载链接");

// 4. 包含课件提取码
assert_contains($content, $course_data['courseware_code'], "邮件内容 - 包含课件提取码");

// 5. 包含教案提取码
assert_contains($content, $course_data['lesson_plan_code'], "邮件内容 - 包含教案提取码");

// 6. 包含有效期说明
assert_test(preg_match('/有效期至\s*\d{4}-\d{2}-\d{2}/', $content), "邮件内容 - 包含有效期说明");

// 7. 包含版权提示
assert_contains($content, '版权声明', "邮件内容 - 包含版权声明");
assert_contains($content, '仅供购买用户个人使用', "邮件内容 - 包含版权提示");

// 8. 包含平台名称
assert_contains($content, $oj_name, "邮件内容 - 包含平台名称");

// ========== XSS 防护测试 ==========
echo "\n--- XSS防护 ---\n";
$malicious_data = [
    'title' => '<script>alert("XSS")</script>',
    'courseware_link' => 'https://kdocs.cn/l/safe',
    'courseware_code' => 'CODE',
    'lesson_plan_link' => 'https://kdocs.cn/l/safe',
    'lesson_plan_code' => 'CODE',
];

$safe_content = generate_course_mail_content($malicious_data);
assert_test(strpos($safe_content, '<script>') === false, "XSS防护 - script标签被转义");
assert_test(strpos($safe_content, '&lt;script&gt;') !== false, "XSS防护 - 标签被HTML转义");

// ========== 空数据测试 ==========
echo "\n--- 边界情况 ---\n";
$empty_data = [
    'title' => '',
    'courseware_link' => '',
    'courseware_code' => '',
    'lesson_plan_link' => '',
    'lesson_plan_code' => '',
];

$empty_content = generate_course_mail_content($empty_data);
assert_test(strlen($empty_content) > 0, "边界情况 - 空数据也能生成内容");

// ========== 输出汇总 ==========
echo "\n总计: $pass_count 通过, $fail_count 失败\n";

// 返回退出码
exit($fail_count > 0 ? 1 : 0);
