<?php
require_once('../include/db_info.inc.php');
require_once('../include/setlang.php');
require_once('../include/my_func.inc.php');

if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    http_response_code(403);
    exit('Forbidden');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: point_card_list.php');
    exit();
}
require_once('../include/check_post_key.php');

$count = isset($_POST['count']) ? intval($_POST['count']) : 0;
if ($count < 1 || $count > 1000) {
    echo "<script>alert('生成数量必须在 1~1000 之间');history.go(-1);</script>";
    exit(1);
}

$batch_no = isset($_POST['batch_no']) ? trim($_POST['batch_no']) : '';
if ($batch_no === '') {
    $batch_no = 'PC' . date('YmdHis');
}
if (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $batch_no)) {
    echo "<script>alert('批次号只能使用字母、数字、_、-，长度不超过 64');history.go(-1);</script>";
    exit(1);
}

// 生成（带去重重试），事务批量插入
$ok = 0;
$max_retry = 5;
try {
    point_tx_begin();
    for ($i = 0; $i < $count; $i++) {
        $card_no = '';
        $card_secret = '';
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
            // pdo_query for INSERT returns lastInsertId; 0/empty means IGNORE-ed due to conflict
            if (!empty($rc)) {
                $inserted = true;
                $ok++;
            }
        }
        if (!$inserted) {
            throw new Exception('卡号冲突重试超过上限，请重试');
        }
    }
    point_tx_commit();
} catch (Exception $e) {
    point_tx_rollback();
    // 错误信息不包含卡密
    echo "<script>alert('生成失败：" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "');history.go(-1);</script>";
    exit(1);
}

header('Location: point_card_list.php?batch_no=' . urlencode($batch_no));
exit();
