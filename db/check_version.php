<?php
/**
 * HUSTOJ 数据库版本检查脚本
 * 功能：快速检测已执行的SQL文件
 */

$config_file = '/home/judge/etc/judge.conf';

// 从配置文件读取数据库连接信息
function get_db_config($config_file) {
    if (!file_exists($config_file)) {
        die("配置文件不存在: $config_file\n");
    }
    
    $config = [];
    $lines = file($config_file);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $config[trim($key)] = trim($value);
        }
    }
    
    return [
        'host' => $config['OJ_HOST_NAME'] ?? 'localhost',
        'user' => $config['OJ_USER_NAME'] ?? 'root',
        'pass' => $config['OJ_PASSWORD'] ?? '',
        'name' => $config['OJ_DB_NAME'] ?? 'jol',
        'port' => $config['OJ_PORT_NUMBER'] ?? '3306'
    ];
}

// 检查表是否存在
function table_exists($pdo, $table_name) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table_name'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// 检查字段是否存在
function column_exists($pdo, $table_name, $column_name) {
    try {
        $stmt = $pdo->query("DESCRIBE $table_name $column_name");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// 检查版本表是否存在
function check_db_version_table($pdo) {
    if (table_exists($pdo, 'db_version')) {
        $stmt = $pdo->query("SELECT script_name FROM db_version ORDER BY execute_time");
        $scripts = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $scripts;
    }
    return null;
}

// 主函数
function main() {
    global $config_file;
    
    echo "========================================\n";
    echo "    HUSTOJ 数据库版本检查\n";
    echo "========================================\n\n";
    
    // 读取数据库配置
    echo "1. 读取数据库配置...\n";
    $db_config = get_db_config($config_file);
    echo "   数据库: {$db_config['host']}:{$db_config['port']}/{$db_config['name']}\n";
    echo "   用户: {$db_config['user']}\n";
    
    // 连接数据库
    echo "\n2. 连接数据库...\n";
    try {
        $pdo = new PDO(
            "mysql:host={$db_config['host']};port={$db_config['port']};charset=utf8mb4",
            $db_config['user'],
            $db_config['pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("USE {$db_config['name']}");
        echo "   ✅ 连接成功\n";
    } catch (PDOException $e) {
        echo "   ❌ 连接失败: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // 检查版本表
    echo "\n3. 检查版本记录表...\n";
    $version_scripts = check_db_version_table($pdo);
    if ($version_scripts !== null) {
        echo "   ✅ 已存在版本记录表，已执行的脚本：\n";
        foreach ($version_scripts as $script) {
            echo "      - $script\n";
        }
        exit(0);
    } else {
        echo "   ⚠️  版本记录表不存在，开始检测已执行的SQL...\n";
    }
    
    // 检测各个版本的特征表/字段
    echo "\n4. 检测已执行的SQL文件...\n";
    
    $versions = [
        'V1.0_20260317_db_init.sql' => [
            'desc' => '基础OJ初始化',
            'check' => function($pdo) { return table_exists($pdo, 'problem') && table_exists($pdo, 'solution') && table_exists($pdo, 'users'); }
        ],
        'V1.0_20260331_course_module.sql' => [
            'desc' => '课件商城模块',
            'check' => function($pdo) { return table_exists($pdo, 'course') && table_exists($pdo, 'course_subject') && table_exists($pdo, 'course_order'); }
        ],
        'V1.0_20260411_school_mode.sql' => [
            'desc' => '多学校隔离功能',
            'check' => function($pdo) { return table_exists($pdo, 'school') && column_exists($pdo, 'users', 'school_id') && column_exists($pdo, 'problem', 'is_public'); }
        ],
        'V1.1_20260419_choice_and_exam.sql' => [
            'desc' => '选择题功能 + 考试模块',
            'check' => function($pdo) { return table_exists($pdo, 'exam') && table_exists($pdo, 'exam_attend') && column_exists($pdo, 'problem', 'problem_type'); }
        ],
        'V1.2_20260501_courseware_preview_upgrade.sql' => [
            'desc' => '课件预览升级',
            'check' => function($pdo) { return column_exists($pdo, 'course', 'courseware_preview_url') && column_exists($pdo, 'course', 'lesson_plan_preview_url'); }
        ],
        'V1.3_20260505_remove_extraction_codes.sql' => [
            'desc' => '移除提取码',
            'check' => function($pdo) { return !column_exists($pdo, 'course', 'courseware_code') && !column_exists($pdo, 'course', 'lesson_plan_code'); }
        ],
        'V1.4_20260505_multi_license.sql' => [
            'desc' => '多许可证支持',
            'check' => function($pdo) { return table_exists($pdo, 'license') && table_exists($pdo, 'license_order'); }
        ]
    ];
    
    $executed = [];
    $not_executed = [];
    
    foreach ($versions as $script => $info) {
        if ($info['check']($pdo)) {
            $executed[] = ['script' => $script, 'desc' => $info['desc']];
        } else {
            $not_executed[] = ['script' => $script, 'desc' => $info['desc']];
        }
    }
    
    echo "\n   ✅ 已执行的SQL文件：\n";
    foreach ($executed as $item) {
        echo "      - {$item['script']} ({$item['desc']})\n";
    }
    
    echo "\n   ❌ 未执行的SQL文件：\n";
    foreach ($not_executed as $item) {
        echo "      - {$item['script']} ({$item['desc']})\n";
    }
    
    // 建议操作
    if (!empty($executed)) {
        echo "\n5. 建议操作：\n";
        echo "   如果要使用升级脚本，请先初始化已执行的记录：\n";
        echo "   ./upgrade.sh --init " . implode(' ', array_column($executed, 'script')) . "\n";
    }
    
    echo "\n========================================\n";
}

main();
?>