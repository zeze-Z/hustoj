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

    // 每个教师只统计最近一次登录的IP（避免同一教师多IP重复统计）
    // 子查询：先找出每个教师最近一次登录的时间
    $time_condition_inner = "";
    $time_condition_outer = "";
    $params_inner = [];
    $params_outer = [];
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $from)) {
        $time_condition_inner .= " AND time >= ?";
        $time_condition_outer .= " AND l.time >= ?";
        $params_inner[] = $from . ' 00:00:00';
        $params_outer[] = $from . ' 00:00:00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $to)) {
        $time_condition_inner .= " AND time <= ?";
        $time_condition_outer .= " AND l.time <= ?";
        $params_inner[] = $to . ' 23:59:59';
        $params_outer[] = $to . ' 23:59:59';
    }
    $params = array_merge($params_inner, $params_outer);

    $sql = "SELECT ip, COUNT(*) as user_count
            FROM (
                SELECT l.user_id, l.ip
                FROM loginlog l
                JOIN (
                    SELECT user_id, MAX(time) as max_time
                    FROM loginlog
                    WHERE password = 'login ok'
                      AND ip IS NOT NULL AND ip != ''
                      $time_condition_inner
                    GROUP BY user_id
                ) latest ON l.user_id = latest.user_id AND l.time = latest.max_time
                JOIN users u ON l.user_id = u.user_id
                WHERE u.role = 'teacher'
                  AND l.password = 'login ok'
                  AND l.ip IS NOT NULL
                  AND l.ip != ''
                  $time_condition_outer
            ) t
            GROUP BY ip
            ORDER BY user_count DESC
            LIMIT 50";

    $result = pdo_query($sql, $params);
    if (!is_array($result)) $result = [];

    // 收集所有IP并查询归属地
    $ips = array_column($result, 'ip');
    $ip_locations = IpLocation::batchGetLocation($ips);

    // 按城市或省份统计
    // batchGetLocation 返回格式: [ip => "国家省市区"] 的扁平字符串
    // 示例: "中国北京", "中国广东广州", "中国湖北Shizishan", "中国河南省郑州市"
    $stats = [];
    foreach ($result as $row) {
        $ip = $row['ip'];
        $count = intval($row['user_count']);
        $location = isset($ip_locations[$ip]) ? $ip_locations[$ip] : '未知';

        $key = $location;
        if ($location !== '未知' && $location !== '内网') {
            // 去掉国家前缀（"中国"2个汉字，"美利坚"3个汉字等）
            // 使用固定的常见国家名匹配，避免贪心匹配到省名
            $region = preg_replace('/^(中国|美国|日本|韩国|英国|法国|德国|俄罗斯|加拿大|澳大利亚|巴西|印度)/u', '', $location);
            // fallback: 如果没匹配到已知国家名，取前2个汉字作为国家名
            if ($region === $location) {
                $region = preg_replace('/^[\x{4e00}-\x{9fff}]{2}/u', '', $location);
            }

            if ($stat_type === 'province') {
                // 省份：取第一个行政单位（通常是前2个汉字）
                // "广东广州市" → "广东", "北京市" → "北京", "四川成都" → "四川"
                // 先尝试匹配"省"、"自治区"等明确后缀
                if (preg_match('/^(.{2,3}?)(?:省|自治区|特别行政区)/u', $region, $m)) {
                    $key = $m[1];
                } else {
                    // 没有明确省后缀，取前2个汉字
                    // "广东广州市" → "广东", "北京市" → "北京", "四川成都" → "四川"
                    $key = mb_substr($region, 0, 2);
                }
            } else {
                // 城市：取最后一个"市"前的最后部分，或去掉省份前缀
                // "广东广州市" → "广州", "北京市" → "北京", "四川成都" → "成都"
                // "湖北Shizishan" → "Shizishan"
                $pos = mb_strrpos($region, '市');
                if ($pos !== false) {
                    // "广东广州市" → "广东广州" → 取最后2字 → "广州"
                    // "北京市" → "北京" → 取最后2字 → "北京"
                    $clean = mb_substr($region, 0, $pos);
                    if (mb_strlen($clean) >= 2) {
                        $key = mb_substr($clean, -2);
                    } else {
                        $key = $clean;
                    }
                } else {
                    // 没有"市"，如"四川成都"、"湖北Shizishan"
                    // 去掉前2个汉字的省份前缀
                    $key = mb_substr($region, 2);
                    if (!$key) $key = $region;
                }
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
