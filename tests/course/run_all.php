<?php
/**
 * 运行所有课程模块单元测试
 */

$tests = [
    '课程数据模型测试' => __DIR__ . '/test_course_model.php',
    '支付签名测试' => __DIR__ . '/test_payment.php',
    '邮件内容测试' => __DIR__ . '/test_mail.php',
];

$total_pass = 0;
$total_fail = 0;
$failed_tests = [];

foreach ($tests as $name => $test_file) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "运行: $name\n";
    echo str_repeat('=', 60) . "\n";

    ob_start();
    $exit_code = 0;
    $output = exec('php ' . escapeshellarg($test_file), $lines, $exit_code);
    ob_end_clean();

    echo implode("\n", $lines) . "\n";

    if ($exit_code === 0) {
        // 从输出中提取通过/失败数量
        foreach ($lines as $line) {
            if (preg_match('/总计:\s*(\d+)\s*通过,\s*(\d+)\s*失败/', $line, $matches)) {
                $total_pass += intval($matches[1]);
                $total_fail += intval($matches[2]);
                break;
            }
        }
    } else {
        $total_fail++;
        $failed_tests[] = $name;
    }
}

// 输出总体汇总
echo "\n" . str_repeat('=', 60) . "\n";
echo "总体测试结果\n";
echo str_repeat('=', 60) . "\n";
echo "总计: $total_pass 通过, $total_fail 失败\n";

if (!empty($failed_tests)) {
    echo "\n失败的测试:\n";
    foreach ($failed_tests as $failed_test) {
        echo "  - $failed_test\n";
    }
}

// 返回退出码
exit($total_fail > 0 ? 1 : 0);
