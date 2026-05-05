<?php
/**
 * HUSTOJ 数据库升级脚本
 * 功能：自动记录当前数据库版本，增量执行后续SQL文件
 * 
 * 使用方法：
 *   ./upgrade.sh                    # 执行增量升级
 *   ./upgrade.sh --init <script1> <script2>...  # 初始化已执行的脚本记录（不执行）
 *   ./upgrade.sh --help             # 显示帮助信息
 */

// 配置
$config_file = '/home/judge/etc/judge.conf';
$db_dir = dirname(__FILE__);

// 显示帮助信息
function show_help() {
    echo "HUSTOJ 数据库升级脚本\n";
    echo "========================\n\n";
    echo "使用方法：\n";
    echo "  ./upgrade.sh                    # 执行增量升级\n";
    echo "  ./upgrade.sh --init <脚本1> <脚本2>...  # 初始化已执行的脚本记录\n";
    echo "  ./upgrade.sh --help             # 显示帮助信息\n\n";
    echo "示例：\n";
    echo "  ./upgrade.sh --init V1.0_20260317_db_init.sql V1.0_20260331_course_module.sql\n";
    exit(0);
}

// 初始化已执行的脚本记录
function init_executed_scripts($pdo, $scripts) {
    create_version_table($pdo);
    
    foreach ($scripts as $script) {
        $script = trim($script);
        if (empty($script)) continue;
        
        try {
            $sql = "INSERT IGNORE INTO db_version (script_name, execute_time) VALUES (?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$script]);
            
            if ($stmt->rowCount() > 0) {
                echo "  ✅ 已记录: $script\n";
            } else {
                echo "  ⚠️  已存在: $script\n";
            }
        } catch (PDOException $e) {
            echo "  ❌ 记录失败: $script - " . $e->getMessage() . "\n";
        }
    }
}

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

// 获取已执行的SQL文件列表
function get_executed_scripts($pdo) {
    try {
        $stmt = $pdo->query("SELECT script_name FROM db_version ORDER BY execute_time DESC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // 表不存在，返回空数组
        return [];
    }
}

// 记录已执行的SQL文件
function log_executed_script($pdo, $script_name) {
    $sql = "INSERT INTO db_version (script_name, execute_time) VALUES (?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$script_name]);
}

// 创建版本记录表
function create_version_table($pdo) {
    $sql = "
        CREATE TABLE IF NOT EXISTS db_version (
            id INT AUTO_INCREMENT PRIMARY KEY,
            script_name VARCHAR(255) NOT NULL UNIQUE COMMENT '执行的SQL文件名',
            execute_time DATETIME NOT NULL COMMENT '执行时间',
            UNIQUE KEY uk_script_name (script_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据库版本记录表'
    ";
    $pdo->exec($sql);
}

// 扫描目录获取SQL文件列表（按版本排序）
function get_sql_files($dir) {
    $files = glob($dir . '/V*.sql');
    if ($files === false) {
        return [];
    }
    
    // 按版本号排序
    usort($files, function($a, $b) {
        $version_a = basename($a, '.sql');
        $version_b = basename($b, '.sql');
        return version_compare($version_a, $version_b);
    });
    
    return $files;
}

// 执行SQL文件
function execute_sql_file($pdo, $file_path) {
    echo "正在执行: " . basename($file_path) . "...\n";
    
    $sql = file_get_contents($file_path);
    if ($sql === false) {
        echo "  ❌ 无法读取文件\n";
        return false;
    }
    
    try {
        // 先移除所有单行注释（-- 开头的注释）
        $sql = preg_replace('/--.*$/m', '', $sql);
        // 移除多行注释（/* ... */）
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        // 移除空行
        $sql = preg_replace('/^\s*$/m', '', $sql);
        
        // 按分号分割SQL语句
        $statements = explode(';', $sql);
        
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt !== '') {
                $pdo->exec($stmt);
            }
        }
        
        echo "  ✅ 执行成功\n";
        return true;
    } catch (PDOException $e) {
        echo "  ❌ 执行失败: " . $e->getMessage() . "\n";
        return false;
    }
}

// 主函数
function main() {
    global $config_file, $db_dir;
    
    // 处理命令行参数
    $argv = $_SERVER['argv'];
    $argc = $_SERVER['argc'];
    
    // 显示帮助
    if ($argc > 1 && ($argv[1] == '--help' || $argv[1] == '-h')) {
        show_help();
    }
    
    // 初始化模式
    if ($argc > 2 && $argv[1] == '--init') {
        $scripts = array_slice($argv, 2);
        
        echo "========================================\n";
        echo "    HUSTOJ 数据库升级脚本 - 初始化模式\n";
        echo "========================================\n\n";
        echo "将以下脚本标记为已执行（不会实际执行SQL）：\n";
        foreach ($scripts as $script) {
            echo "  - $script\n";
        }
        echo "\n确认继续？(y/N): ";
        $input = trim(fgets(STDIN));
        if (strtolower($input) !== 'y') {
            echo "操作已取消\n";
            exit(0);
        }
        
        // 读取数据库配置
        echo "\n1. 读取数据库配置...\n";
        $db_config = get_db_config($config_file);
        
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
        
        // 初始化记录
        echo "\n3. 初始化已执行脚本记录...\n";
        init_executed_scripts($pdo, $scripts);
        
        echo "\n========================================\n";
        echo "初始化完成！\n";
        echo "========================================\n";
        exit(0);
    }
    
    // 正常升级模式
    echo "========================================\n";
    echo "    HUSTOJ 数据库升级脚本\n";
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
        
        // 选择数据库
        $pdo->exec("USE {$db_config['name']}");
        echo "   ✅ 连接成功\n";
    } catch (PDOException $e) {
        echo "   ❌ 连接失败: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // 创建版本记录表
    echo "\n3. 检查版本记录表...\n";
    create_version_table($pdo);
    echo "   ✅ 版本记录表已就绪\n";
    
    // 获取已执行的脚本列表
    echo "\n4. 获取已执行的脚本...\n";
    $executed = get_executed_scripts($pdo);
    echo "   已执行 " . count($executed) . " 个脚本\n";
    
    // 获取所有SQL文件
    echo "\n5. 扫描SQL文件...\n";
    $sql_files = get_sql_files($db_dir);
    echo "   发现 " . count($sql_files) . " 个SQL文件\n";
    
    // 执行未执行的脚本
    echo "\n6. 执行未执行的脚本...\n";
    $success_count = 0;
    $fail_count = 0;
    
    foreach ($sql_files as $file) {
        $script_name = basename($file);
        
        if (in_array($script_name, $executed)) {
            echo "   - {$script_name}: 已跳过（已执行）\n";
            continue;
        }
        
        if (execute_sql_file($pdo, $file)) {
            log_executed_script($pdo, $script_name);
            $success_count++;
        } else {
            $fail_count++;
            // 遇到错误时询问是否继续
            echo "\n是否继续执行后续脚本？(y/N): ";
            $input = trim(fgets(STDIN));
            if (strtolower($input) !== 'y') {
                echo "用户终止执行\n";
                break;
            }
        }
    }
    
    // 输出统计
    echo "\n========================================\n";
    echo "升级完成！\n";
    echo "成功: {$success_count} 个脚本\n";
    echo "失败: {$fail_count} 个脚本\n";
    echo "已执行: " . (count($executed) + $success_count) . " 个脚本\n";
    echo "========================================\n";
}

// 运行主函数
main();
?>