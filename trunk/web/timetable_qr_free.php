<?php
/**
 * 课程表导出付费去码 API
 * POST：校验登录 / CSRF / 限频后扣 1 积分，返回新余额；无商品、无订单、无履约。
 * 实际去码由前端扣费成功后以 badge:false 重新渲染完成（纯客户端软闸门，
 * 强度与定价匹配，见 template/syzoj/js/timetable.js 的 exportQrFree）。
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');

header('Content-Type: application/json');

// 去码单价（积分/次）
$cost = 1;

// 检查登录
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    echo json_encode(['code' => -1, 'msg' => '请先登录']);
    exit();
}

// 只允许POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => -1, 'msg' => '请求方法错误']);
    exit();
}

// CSRF校验（JSON API 模式：不 unset，session postkey 保持稳定供本页多次请求复用）
if (!isset($_SESSION[$OJ_NAME.'_'.'postkey']) || !isset($_POST['postkey']) || $_SESSION[$OJ_NAME.'_'.'postkey'] != $_POST['postkey']) {
    echo json_encode(['code' => -1, 'msg' => '页面已过期，请刷新后重试']);
    exit();
}

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];

// 防双击双扣：同一用户 10 秒内不得重复扣费
$rate_key = $OJ_NAME . '_tt_qr_rate_' . $user_id;
if (isset($_SESSION[$rate_key]) && (time() - $_SESSION[$rate_key]) < 10) {
    echo json_encode(['code' => -1, 'msg' => '操作过于频繁，请10秒后重试']);
    exit();
}
$_SESSION[$rate_key] = time();

// 事务：锁定用户行 → 校验余额 → 扣 1 积分 + 写 type=7 流水（无订单行，relation_id 仅作流水关联号）
try {
    point_tx_begin();

    $balance = point_lock_user($user_id);
    if ($balance === false) {
        point_tx_rollback();
        echo json_encode(['code' => -1, 'msg' => '用户不存在']);
        exit();
    }

    if ($balance < $cost) {
        point_tx_rollback();
        echo json_encode([
            'code' => -3,
            'msg' => "积分不足，需要 {$cost} 积分，当前余额 {$balance} 积分"
        ]);
        exit();
    }

    $ref = 'TT' . time() . random_int(1000, 9999);
    $apply = point_apply_change(
        $user_id,
        -$cost,
        POINT_LOG_TYPE_TIMETABLE_QRFREE,
        $ref,
        '课程表去码导出'
    );

    if (!$apply['success']) {
        point_tx_rollback();
        echo json_encode(['code' => -1, 'msg' => '积分扣减失败：' . $apply['message']]);
        exit();
    }

    point_tx_commit();

    // 飞书通知：课程表去码扣费成功（失败静默不影响主业务）
    require_once('./include/feishu_notify.php');
    feishu_notify(
        '课程表去码导出',
        "**用户**: {$user_id}\n" .
        "**流水号**: {$ref}\n" .
        "**消耗积分**: {$cost}\n" .
        "**当前余额**: {$apply['balance']}",
        'info'
    );

    echo json_encode([
        'code' => 0,
        'msg' => 'ok',
        'data' => ['balance' => $apply['balance']]
    ]);

} catch (Exception $e) {
    point_tx_rollback();
    error_log("timetable_qr_free exception for {$user_id}: " . $e->getMessage());
    echo json_encode(['code' => -1, 'msg' => '系统繁忙，请稍后再试']);
}
