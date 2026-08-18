<?php
/**
 * 积分与课件业务飞书通知封装。
 *
 * 仅负责拼装业务消息，实际 Webhook 发送复用 feishu_notify.php。
 * 通知失败不得影响主业务；卡密等敏感信息不得进入通知内容。
 */

/**
 * 安全发送飞书通知：通知失败不得影响主业务。
 */
function _point_feishu_notify($title, $content, $level = 'info') {
    try {
        require_once(__DIR__ . '/feishu_notify.php');
        if (function_exists('feishu_notify')) {
            return feishu_notify($title, $content, $level);
        }
    } catch (Throwable $e) {
        // 通知失败静默忽略，避免影响主业务
    }
    return false;
}

/**
 * 拼接飞书通知字段行。
 */
function _point_feishu_line($label, $value) {
    return '- ' . $label . '：' . (string)$value;
}

/**
 * 摘要展示 ID 列表，避免批量操作通知过长。
 */
function _point_feishu_summary_ids($ids, $limit = 20) {
    if (!is_array($ids)) {
        return '';
    }
    $clean = [];
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id > 0) $clean[] = $id;
    }
    $total = count($clean);
    if ($total === 0) return '';
    $show = array_slice($clean, 0, $limit);
    $text = implode(',', $show);
    if ($total > $limit) {
        $text .= ' ... 共' . $total . '个';
    }
    return $text;
}

/**
 * 课件订单飞书通知（支持积分购买和免费获取）。
 */
function send_order_feishu_notify($course, $user_id, $order_no, $license_type, $amount, $pay_channel, $is_upgrade = false, $preview_price = 0, $source_price = 0) {
    // 仅处理积分支付和免费获取，第三方支付由回调流程处理
    if ($pay_channel !== 'point' && $pay_channel !== 'free') {
        return false;
    }
    $course_id = isset($course['course_id']) ? intval($course['course_id']) : (isset($course['id']) ? intval($course['id']) : 0);
    $course_title = isset($course['title']) ? mb_substr((string)$course['title'], 0, 120) : ('课程#' . $course_id);
    $license_text = intval($license_type) === 2 ? '原文件版' : '完整预览版';

    if ($pay_channel === 'free') {
        $title = '课件免费获取成功';
        $content = implode("\n", [
            _point_feishu_line('用户', $user_id),
            _point_feishu_line('订单号', $order_no),
            _point_feishu_line('课件', $course_title . ' #' . $course_id),
            _point_feishu_line('权限', $license_text),
            _point_feishu_line('获取方式', '免费获取'),
            _point_feishu_line('预览价/原文件价', $preview_price . ' / ' . $source_price),
        ]);
    } else {
        $title = '课件积分购买成功';
        $content = implode("\n", [
            _point_feishu_line('用户', $user_id),
            _point_feishu_line('订单号', $order_no),
            _point_feishu_line('课件', $course_title . ' #' . $course_id),
            _point_feishu_line('权限', $license_text),
            _point_feishu_line('消耗积分', intval($amount)),
            _point_feishu_line('支付渠道', '积分支付'),
            _point_feishu_line('是否升级', $is_upgrade ? '是' : '否'),
            _point_feishu_line('预览价/原文件价', $preview_price . ' / ' . $source_price),
        ]);
    }
    return _point_feishu_notify($title, $content, 'info');
}

/**
 * 积分充值卡生成成功通知（不发送 card_secret）。
 */
function send_point_card_generate_success_notify($batch_no, $count, $admin_id = '') {
    $count = intval($count);
    $content = implode("\n", [
        _point_feishu_line('管理员', $admin_id),
        _point_feishu_line('批次号', $batch_no),
        _point_feishu_line('生成数量', $count),
        _point_feishu_line('单张面额', POINT_CARD_VALUE),
        _point_feishu_line('总积分面额', $count * POINT_CARD_VALUE),
    ]);
    return _point_feishu_notify('积分充值卡生成成功', $content, 'info');
}

/**
 * 充值卡禁用通知：批量禁用不发送卡密，仅发送数量和 ID 摘要。
 */
function send_point_card_disable_notify($ids, $disabled_count, $admin_id = '') {
    $content = implode("\n", [
        _point_feishu_line('管理员', $admin_id),
        _point_feishu_line('提交数量', is_array($ids) ? count($ids) : 0),
        _point_feishu_line('实际禁用数量', intval($disabled_count)),
        _point_feishu_line('卡片ID摘要', _point_feishu_summary_ids($ids)),
    ]);
    return _point_feishu_notify('积分充值卡禁用', $content, 'warn');
}

/**
 * 充值卡兑换成功通知（不发送 card_secret）。
 */
function send_point_card_redeem_success_notify($user_id, $card_no, $add, $balance, $ip = '') {
    $content = implode("\n", [
        _point_feishu_line('用户', $user_id),
        _point_feishu_line('卡号', $card_no),
        _point_feishu_line('到账积分', intval($add)),
        _point_feishu_line('当前余额', intval($balance)),
        _point_feishu_line('兑换IP', $ip),
    ]);
    return _point_feishu_notify('积分充值卡兑换成功', $content, 'info');
}

/**
 * 积分业务异常通知（自动过滤 card_secret 等敏感字段）。
 */
function send_point_business_exception_notify($scene, $message, $context = [], $level = 'error') {
    $lines = [
        _point_feishu_line('场景', $scene),
        _point_feishu_line('信息', mb_substr((string)$message, 0, 200)),
    ];
    if (is_array($context)) {
        foreach ($context as $key => $value) {
            $lower_key = strtolower((string)$key);
            if (strpos($lower_key, 'secret') !== false || strpos($lower_key, 'password') !== false || strpos($lower_key, 'pass') !== false) {
                continue;
            }
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $lines[] = _point_feishu_line($key, mb_substr((string)$value, 0, 200));
        }
    }
    return _point_feishu_notify('积分业务异常', implode("\n", $lines), $level);
}

/**
 * 管理员手动调整积分成功通知。
 */
function send_point_admin_adjust_success_notify($admin_id, $target_user_id, $delta, $reason, $balance) {
    $delta = intval($delta);
    $content = implode("\n", [
        _point_feishu_line('管理员', $admin_id),
        _point_feishu_line('目标用户', $target_user_id),
        _point_feishu_line('调整积分', $delta),
        _point_feishu_line('当前余额', intval($balance)),
        _point_feishu_line('原因', mb_substr((string)$reason, 0, 200)),
    ]);
    return _point_feishu_notify('管理员手动调整积分成功', $content, $delta < 0 ? 'warn' : 'info');
}
