<?php
/**
 * 离线游戏兑换API
 * POST：扣积分 + 生成授权码 + 返回下载链接
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');

// 离线游戏密钥目录：必须位于 web 根目录之外（私钥不允许随站点部署在 web 目录下）
// nginx 等不支持 .htaccess 的环境同样安全；缺私钥时返回通用错误并写日志，不向前端泄漏路径
define('OG_KEY_DIR', '/home/judge/etc/offline_games');
define('OG_PRIVATE_KEY', OG_KEY_DIR . '/private_key.pem');

header('Content-Type: application/json');

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

// CSRF校验
if (!isset($_SESSION[$OJ_NAME.'_'.'postkey']) || !isset($_POST['postkey']) || $_SESSION[$OJ_NAME.'_'.'postkey'] != $_POST['postkey']) {
    echo json_encode(['code' => -1, 'msg' => '页面已过期，请刷新后重试']);
    exit();
}

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
$school_name = isset($_POST['school_name']) ? trim($_POST['school_name']) : '';
$room_name = isset($_POST['room_name']) ? trim($_POST['room_name']) : '';

// 防重放：同一用户 10 秒内不得重复提交
$rate_key = $OJ_NAME . '_og_rate_' . $user_id;
if (isset($_SESSION[$rate_key]) && (time() - $_SESSION[$rate_key]) < 10) {
    echo json_encode(['code' => -1, 'msg' => '操作过于频繁，请10秒后重试']);
    exit();
}
$_SESSION[$rate_key] = time();

// 参数校验
if ($school_name === '' || $room_name === '') {
    echo json_encode(['code' => -1, 'msg' => '请填写学校名称和机房名称']);
    exit();
}

if (mb_strlen($school_name) > 100 || mb_strlen($room_name) > 100) {
    echo json_encode(['code' => -1, 'msg' => '学校名称或机房名称过长']);
    exit();
}

// 离线游戏价格（积分）
$point_amount = 50;
$expire_days = 365; // 1年有效期

// 生成订单号（事务外预生成，事务内防重）
$order_no = 'OG' . time() . random_int(1000, 9999);

// 计算过期日期
$expire_date = date('Y-m-d', strtotime("+{$expire_days} days"));

// 事务：扣积分 + 写订单
try {
    point_tx_begin();

    // 锁定用户并检查余额
    $balance = point_lock_user($user_id);
    if ($balance === false) {
        point_tx_rollback();
        echo json_encode(['code' => -1, 'msg' => '用户不存在']);
        exit();
    }

    // 【竞态防护】在事务内、行锁之后检查是否已有未过期订单
    // 此处与后续 INSERT ... WHERE NOT EXISTS 形成双重防重
    $existing_in_tx = pdo_query(
        "SELECT id, expire_date FROM `offline_game_order`
          WHERE user_id = ? AND expire_date >= CURDATE()
          LIMIT 1 FOR UPDATE",
        $user_id
    );
    if (!empty($existing_in_tx)) {
        point_tx_rollback();
        $expire = $existing_in_tx[0]['expire_date'];
        echo json_encode([
            'code' => -2,
            'msg' => "您已拥有离线游戏授权，有效期至 {$expire}，无需重复兑换"
        ]);
        exit();
    }

    if ($balance < $point_amount) {
        point_tx_rollback();
        echo json_encode([
            'code' => -3,
            'msg' => "积分不足，需要 {$point_amount} 积分，当前余额 {$balance} 积分"
        ]);
        exit();
    }

    // 扣积分
    $apply = point_apply_change(
        $user_id,
        -$point_amount,
        POINT_LOG_TYPE_SYSTEM,
        $order_no,
        '离线游戏兑换：' . mb_substr($school_name, 0, 30) . '-' . mb_substr($room_name, 0, 30)
    );

    if (!$apply['success']) {
        point_tx_rollback();
        echo json_encode(['code' => -1, 'msg' => '积分扣减失败：' . $apply['message']]);
        exit();
    }

    // 调用Python生成授权码（RSA-PSS签名）
    $script_path = __DIR__ . '/offline-games/admin/generate_license.py';
    if (!file_exists($script_path)) {
        point_tx_rollback();
        error_log("offline_game_redeem: generate_license.py not found for user {$user_id}");
        echo json_encode(['code' => -1, 'msg' => '授权码生成服务配置异常，请联系管理员']);
        exit();
    }

    // 私钥部署在 web 根之外的 OG_KEY_DIR；缺失时仅返回通用失败信息（不向前端泄漏服务器路径）
    if (!is_file(OG_PRIVATE_KEY)) {
        point_tx_rollback();
        error_log("offline_game_redeem: private key missing, expected at " . OG_PRIVATE_KEY . " (user: {$user_id})");
        echo json_encode(['code' => -1, 'msg' => '授权码生成服务配置异常，请联系管理员']);
        exit();
    }

    $safe_school = escapeshellarg($school_name);
    $safe_room = escapeshellarg($room_name);
    $safe_expire = escapeshellarg($expire_date);
    // tempnam 会先创建 og_XXXXXX 空文件，python 再写出 og_XXXXXX.dat，两个文件都要清理。
    // 清理必须用 register_shutdown_function：PHP 的 exit() 不执行 finally 块，
    // 而 shutdown 回调在正常结束/exit/致命错误所有路径都会运行，不留孤儿文件
    $temp_base = tempnam(sys_get_temp_dir(), 'og_');
    $output_file = $temp_base . '.dat';
    register_shutdown_function(function () use ($temp_base, $output_file) {
        @unlink($output_file);
        @unlink($temp_base);
    });

    // 【已定决策】exec 保留在事务内：授权码生成失败时直接回滚、不扣积分，
    // 避免"先扣分后补偿"的对账复杂度；代价是用户行锁被 python 启动耗时拉长（百 ms 级），
    // 兑换属低频操作，可接受。
    $cmd = "python3 " . escapeshellarg($script_path)
         . " --private-key " . escapeshellarg(OG_PRIVATE_KEY)
         . " --school {$safe_school}"
         . " --room {$safe_room}"
         . " --expire {$safe_expire}"
         . " --output " . escapeshellarg($output_file)
         . " 2>&1";

    exec($cmd, $output, $return_code);

    if ($return_code !== 0 || !file_exists($output_file)) {
        // 尝试用 python（不带3）
        $cmd = "python " . escapeshellarg($script_path)
             . " --private-key " . escapeshellarg(OG_PRIVATE_KEY)
             . " --school {$safe_school}"
             . " --room {$safe_room}"
             . " --expire {$safe_expire}"
             . " --output " . escapeshellarg($output_file)
             . " 2>&1";
        exec($cmd, $output, $return_code);
    }

    if ($return_code !== 0 || !file_exists($output_file)) {
        point_tx_rollback();
        error_log("offline_game_redeem: python failed for user {$user_id}: " . implode("\n", $output));
        echo json_encode(['code' => -1, 'msg' => '授权码生成失败，请联系管理员']);
        exit();
    }

    $license_code = file_get_contents($output_file);

    // 验证生成的license是合法JSON
    $license_json = json_decode($license_code, true);
    if (!$license_json || !isset($license_json['signature'])) {
        point_tx_rollback();
        echo json_encode(['code' => -1, 'msg' => '授权码格式异常，请联系管理员']);
        exit();
    }

    // 写入订单（原子防重：NOT EXISTS 保证同一用户不能有未过期订单）
    // 注意：pdo_query 对 INSERT 返回 lastInsertId，0 行插入时会误返回上一条 point_log 的自增 id，
    // 必须用 PDO rowCount() 取真实影响行数来判断防重是否生效
    $dbh = _point_ensure_dbh();
    $stmt = $dbh->prepare(
        "INSERT INTO `offline_game_order`
            (user_id, school_name, room_name, license_code, expire_date, order_no, point_amount, create_time)
         SELECT ?, ?, ?, ?, ?, ?, ?, NOW()
         FROM DUAL
         WHERE NOT EXISTS (
           SELECT 1 FROM `offline_game_order`
           WHERE user_id = ? AND expire_date >= CURDATE()
         )"
    );
    $stmt->execute([
        $user_id, $school_name, $room_name, $license_code,
        $expire_date, $order_no, $point_amount,
        $user_id
    ]);
    $insert = $stmt->rowCount();

    if ($insert <= 0) {
        point_tx_rollback();
        error_log("offline_game_redeem: duplicate order blocked for user {$user_id}, order_no {$order_no}");
        echo json_encode(['code' => -1, 'msg' => '订单创建失败，请勿重复兑换']);
        exit();
    }

    point_tx_commit();

    // 返回成功结果
    echo json_encode([
        'code' => 0,
        'msg' => '兑换成功',
        'data' => [
            'license_code' => $license_code,
            'school_name' => $school_name,
            'room_name' => $room_name,
            'expire_date' => $expire_date,
            'download_url' => 'https://pan.baidu.com/s/1M5u9sYrioG123AFO4ob81g?pwd=fi7u',
            'balance' => $apply['balance'],
        ]
    ]);

} catch (Exception $e) {
    point_tx_rollback();
    error_log("offline_game_redeem exception for {$user_id}: " . $e->getMessage());
    echo json_encode(['code' => -1, 'msg' => '系统繁忙，请稍后再试']);
}
