<?php
/**
 * 飞书 Webhook 机器人通知
 * 用于推送平台运营关键消息到飞书群
 */

/**
 * 发送飞书 Webhook 通知
 *
 * @param string $title    消息标题
 * @param string $content  消息正文（支持换行符）
 * @param string $level    级别: info | warn | error（影响卡片颜色）
 * @return bool 是否发送成功
 */
function feishu_notify($title, $content, $level = 'info') {
    global $OJ_NAME, $FEISHU_WEBHOOK_URL;
    $server_ip = gethostbyname(gethostname());

    // 未配置 webhook 则静默跳过
    if (empty($FEISHU_WEBHOOK_URL)) {
        return false;
    }

    // 测试环境IP检测：过滤 192.168.x.x 网段
    $isTestEnv = false;
    // 检测访问者IP
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    if (strpos($client_ip, '192.168.') === 0 || strpos($client_ip, '127.0.') === 0) {
        $isTestEnv = true;
    }
    
    // 如果是测试环境，直接不发送告警
    if ($isTestEnv) {
        return false;
    }

    // 颜色映射
    $colors = [
        'info'  => 'blue',
        'warn'  => 'orange',
        'error' => 'red',
    ];
    $color = $colors[$level] ?? 'blue';

    // 读取来源 IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';

    // 构造飞书卡片消息
    $card = [
        'msg_type' => 'interactive',
        'card' => [
            'config' => ['wide_screen_mode' => true],
            'header' => [
                'title' => ['tag' => 'plain_text', 'content' => "[$OJ_NAME|$server_ip] $title"],
                'template' => $color,
            ],
            'elements' => [
                [
                    'tag' => 'div',
                    'text' => ['tag' => 'lark_md', 'content' => $content],
                ],
                [
                    'tag' => 'div',
                    'text' => [
                        'tag' => 'plain_text',
                        'content' => date('Y-m-d H:i:s') . ' | IP: ' . $ip,
                    ],
                    'fields' => [],
                ],
            ],
        ],
    ];

    $payload = json_encode($card, JSON_UNESCAPED_UNICODE);

    // 使用 cURL 发送
    if (!function_exists('curl_init')) {
        error_log("[feishu_notify] curl not available");
        return false;
    }

    $ch = curl_init($FEISHU_WEBHOOK_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
    ]);

    $resp = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err || $http_code !== 200) {
        error_log("[feishu_notify] failed: http=$http_code err=$err resp=$resp");
        return false;
    }

    return true;
}
