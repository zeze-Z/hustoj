<?php
/**
 * 老师登录IP统计 + 教师活跃度 - AJAX接口
 * 支持两种action：ip_stats（IP归属地统计）和 activity（教师活跃度查询）
 */
ini_set("display_errors", "Off");
error_reporting(0);
ob_implicit_flush(false);

require_once("../include/db_info.inc.php");
require_once("../include/school.php");
require_once("../include/ip_location.php");

header('Content-Type: application/json; charset=utf-8');

// 权限控制（直接用session判断，不依赖set_get_key）
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo json_encode(['error' => '无权限，请重新登录']);
    exit;
}

// 入参
$action = isset($_GET['action']) ? trim($_GET['action']) : 'ip_stats';
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';
$stat_type = isset($_GET['stat_type']) ? trim($_GET['stat_type']) : 'city';
$days = isset($_GET['days']) ? intval($_GET['days']) : 1;

// 默认查询最近30天
$default_from = date('Y-m-d', strtotime('-30 days'));
if ($from === '' && $to === '') {
    $from = $default_from;
    $to = date('Y-m-d');
}

// 检查表是否存在
$existing_tables = null;
$table_exists = function ($name) use (&$existing_tables) {
    if ($existing_tables === null) {
        $existing_tables = [];
        $tbl_res = pdo_query("SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE()");
        if (is_array($tbl_res)) {
            foreach ($tbl_res as $trow) {
                $existing_tables[] = strtolower(current($trow));
            }
        }
    }
    return in_array(strtolower($name), $existing_tables, true);
};

// 根据action分发处理
if ($action === 'activity') {
    // ============ 教师活跃度查询 ============
    if (!$table_exists('loginlog') || !$table_exists('users')) {
        echo json_encode(['error' => '数据库表不存在']);
        exit;
    }

    // 限制days范围
    if ($days < 1) $days = 1;
    if ($days > 30) $days = 30;

    // 查询老师总数（未禁用）
    $total_sql = "SELECT COUNT(*) as cnt FROM users WHERE role = 'teacher' AND defunct = 'N'";
    $total_row = pdo_query($total_sql);
    $total_teachers = isset($total_row[0]['cnt']) ? intval($total_row[0]['cnt']) : 0;

    // 查询在指定天数内有登录记录的老师数
    $active_sql = "SELECT COUNT(DISTINCT l.user_id) as cnt
                    FROM loginlog l
                    JOIN users u ON l.user_id = u.user_id
                    WHERE u.role = 'teacher'
                      AND u.defunct = 'N'
                      AND l.password = 'login ok'
                      AND l.time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
    $active_row = pdo_query($active_sql, $days);
    $active_teachers = isset($active_row[0]['cnt']) ? intval($active_row[0]['cnt']) : 0;

    // 计算未登录人数
    $inactive_teachers = max(0, $total_teachers - $active_teachers);

    // 生成时间段描述
    if ($days === 1) {
        $period = '当天';
    } else {
        $period = '近' . $days . '天';
    }

    echo json_encode([
        'total' => $total_teachers,
        'active' => $active_teachers,
        'inactive' => $inactive_teachers,
        'days' => $days,
        'period' => $period
    ], JSON_UNESCAPED_UNICODE);

} else {
    // ============ IP归属地统计 ============
    if (!$table_exists('loginlog') || !$table_exists('users')) {
        echo json_encode(['stats' => [], 'total' => 0, 'chart_labels' => [], 'chart_data' => [], 'from' => $from, 'to' => $to, 'stat_type' => $stat_type, 'count' => 0]);
        exit;
    }

    $sql = "SELECT l.ip, COUNT(DISTINCT l.user_id) as user_count
            FROM loginlog l
            JOIN users u ON l.user_id = u.user_id
            WHERE u.role = 'teacher'
              AND l.password = 'login ok'
              AND l.ip IS NOT NULL
              AND l.ip != ''";

    $params = [];
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $from)) {
        $sql .= " AND l.time >= ?";
        $params[] = $from . ' 00:00:00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $to)) {
        $sql .= " AND l.time <= ?";
        $params[] = $to . ' 23:59:59';
    }

    $sql .= " GROUP BY l.ip ORDER BY user_count DESC LIMIT 50";

    $result = pdo_query($sql, $params);
    if (!is_array($result)) $result = [];

    // 收集所有IP并查询归属地
    $ips = array_column($result, 'ip');
    $ip_locations = IpLocation::batchGetLocation($ips);

    // 按城市或省份统计
    $stats = [];
    foreach ($result as $row) {
        $ip = $row['ip'];
        $count = intval($row['user_count']);
        $location = $ip_locations[$ip] ?? ['regionName' => '未知', 'city' => '未知'];

        if ($stat_type === 'province') {
            $key = $location['regionName'];
        } else {
            $key = $location['city'];
            if ($key === '未知') {
                $key = $location['regionName'];
            }
        }

        if (!isset($stats[$key])) {
            $stats[$key] = 0;
        }
        $stats[$key] += $count;
    }

    // 按数量排序
    arsort($stats);

    // 准备返回数据
    $chart_labels = array_keys($stats);
    $chart_data = array_values($stats);
    $total = array_sum($stats);

    echo json_encode([
        'stats' => $stats,
        'chart_labels' => $chart_labels,
        'chart_data' => $chart_data,
        'total' => $total,
        'from' => $from,
        'to' => $to,
        'stat_type' => $stat_type,
        'count' => count($result)
    ], JSON_UNESCAPED_UNICODE);
}

exit;
