<?php
/**
 * 充值卡 CLI 批量生成脚本
 *
 * 用法：
 *   php scripts/generate_point_cards.php <count> [batch_no]
 *
 *   <count>     必填，生成数量，1~10000
 *   [batch_no]  可选，批次号；不传时使用 PC + YmdHis
 *
 * STDOUT 输出每行 “卡号 卡密”，可重定向到文件后导入发卡网。
 * STDERR 输出汇总信息（数量、批次号、耗时）。
 *
 * 安全要求：
 *   - 必须使用 random_int，不使用 rand / mt_rand
 *   - 卡密绝不写入 error_log
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

if ($argc < 2) {
    fwrite(STDERR, "Usage: php " . basename(__FILE__) . " <count> [batch_no]\n");
    exit(1);
}

$count = intval($argv[1]);
if ($count < 1 || $count > 10000) {
    fwrite(STDERR, "Error: count must be in [1, 10000]\n");
    exit(1);
}
$batch_no = isset($argv[2]) ? trim($argv[2]) : '';
if ($batch_no === '') {
    $batch_no = 'PC' . date('YmdHis');
}
if (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $batch_no)) {
    fwrite(STDERR, "Error: batch_no only allows [A-Za-z0-9_-], length <= 64\n");
    exit(1);
}

// 定位 web 目录并加载配置与 helper
//   - 开发仓库结构：scripts/../trunk/web
//   - 生产部署结构：scripts/../web
$candidates = array(
    realpath(__DIR__ . '/../trunk/web'),
    realpath(__DIR__ . '/../web'),
);
$web_root = null;
foreach ($candidates as $c) {
    if ($c && is_file($c . '/include/db_info.inc.php')) {
        $web_root = $c;
        break;
    }
}
if (!$web_root) {
    fwrite(STDERR, "Error: cannot locate web directory (tried trunk/web and web).\n");
    exit(1);
}
chdir($web_root);

require_once($web_root . '/include/db_info.inc.php');
require_once($web_root . '/include/my_func.inc.php');

$start = microtime(true);
$ok = 0;
$max_retry = 8;

try {
    point_tx_begin();
    for ($i = 0; $i < $count; $i++) {
        $inserted = false;
        for ($r = 0; $r < $max_retry && !$inserted; $r++) {
            $card_no = point_generate_card_no($batch_no);
            $card_secret = point_generate_card_secret();
            $rc = pdo_query(
                "INSERT IGNORE INTO point_card
                    (batch_no, card_no, card_secret, status, create_time, update_time)
                 VALUES (?, ?, ?, 0, NOW(), NOW())",
                $batch_no, $card_no, $card_secret
            );
            if (!empty($rc)) {
                $inserted = true;
                $ok++;
                fwrite(STDOUT, $card_no . ' ' . $card_secret . "\n");
            }
        }
        if (!$inserted) {
            throw new Exception("card_no conflict after retries; abort.");
        }
    }
    point_tx_commit();
} catch (Exception $e) {
    point_tx_rollback();
    // 不在错误日志中写入卡密
    fwrite(STDERR, "FAILED: " . $e->getMessage() . "\n");
    exit(1);
}

$elapsed = round((microtime(true) - $start) * 1000);
fwrite(STDERR, "OK: generated {$ok} cards, batch={$batch_no}, elapsed={$elapsed}ms\n");
exit(0);
