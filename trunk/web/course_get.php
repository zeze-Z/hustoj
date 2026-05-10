<?php
/**
 * 课程获取页面
 * 免费课程获取、付费课程购买
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/cache_start.php');
require_once('./include/setlang.php');
require_once("./include/set_get_key.php");

// 检查登录状态
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header("location:loginpage.php");
    exit();
}

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$license_type = isset($_GET['type']) ? intval($_GET['type']) : 2;
$is_upgrade = isset($_GET['upgrade']) ? intval($_GET['upgrade']) : 0;

if (!in_array($license_type, [1, 2])) {
    $license_type = 2;
}

if ($is_upgrade && $license_type != 2) {
    $is_upgrade = 0;
}

$error_message = '';
$success_message = '';
$redirect_url = '';

/**
 * 支付宝 RSA2 签名函数
 * @param array $params 参数数组
 * @param string $private_key 商户私钥
 * @return string 签名
 */
function alipay_sign($params, $private_key) {
    // 过滤空值和sign/sign_type参数
    $filtered = array();
    foreach ($params as $k => $v) {
        if ($v !== '' && $v !== null && $k != 'sign' && $k != 'sign_type') {
            $filtered[$k] = $v;
        }
    }
    // 按字母顺序排序
    ksort($filtered);
    // 拼接字符串
    $string = '';
    foreach ($filtered as $k => $v) {
        $string .= "$k=$v&";
    }
    $string = trim($string, '&');

    // 签名
    $private_key = str_replace(["\r\n", "\n", "\r"], '', $private_key);
    openssl_sign($string, $sign, $private_key, OPENSSL_ALGO_SHA256);
    return base64_encode($sign);
}

/**
 * 发起支付宝电脑网站支付请求
 * @param array $order 订单数据
 * @param array $course 课程数据
 * @return string|false 返回表单HTML或false
 */
function initiate_alipay_payment($order, $course) {
    global $ALIPAY_APP_ID, $ALIPAY_PRIVATE_KEY, $ALIPAY_GATEWAY_URL, $ALIPAY_NOTIFY_URL, $ALIPAY_RETURN_URL;

    // 检查配置是否完整
    if (empty($ALIPAY_APP_ID) || empty($ALIPAY_PRIVATE_KEY)) {
        error_log("支付宝配置不完整");
        return false;
    }

    // 构建业务参数
    $biz_content = array(
        'out_trade_no' => $order['order_no'],
        'total_amount' => number_format(floatval($order['amount']), 2, '.', ''),
        'subject' => $course['title'],
        'product_code' => 'FAST_INSTANT_TRADE_PAY'
    );

    // 构建公共参数
    $params = array(
        'app_id' => $ALIPAY_APP_ID,
        'method' => 'alipay.trade.page.pay',
        'charset' => 'UTF-8',
        'sign_type' => 'RSA2',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0',
        'notify_url' => $ALIPAY_NOTIFY_URL,
        'return_url' => $ALIPAY_RETURN_URL,
        'biz_content' => json_encode($biz_content, JSON_UNESCAPED_UNICODE)
    );

    // 生成签名
    $params['sign'] = alipay_sign($params, $ALIPAY_PRIVATE_KEY);

    // 构建自动提交表单
    $form = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>正在跳转到支付宝...</title>
</head>
<body>
    <div style="text-align: center; margin-top: 100px; font-family: Arial, sans-serif;">
        <p>正在跳转到支付宝，请稍候...</p>
    </div>
    <form id="alipay_form" method="post" action="' . htmlspecialchars($ALIPAY_GATEWAY_URL, ENT_QUOTES, 'UTF-8') . '">';

    foreach ($params as $k => $v) {
        $form .= '<input type="hidden" name="' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '">';
    }

    $form .= '</form>
    <script type="text/javascript">
        document.getElementById("alipay_form").submit();
    </script>
</body>
</html>';

    return $form;
}

// 处理POST请求（获取课程或发起支付）
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once('./include/check_post_key.php');

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $pay_channel = isset($_POST['pay_channel']) ? trim($_POST['pay_channel']) : '';
    $license_type = isset($_POST['license_type']) ? intval($_POST['license_type']) : 2;
    $is_upgrade = isset($_POST['upgrade']) ? intval($_POST['upgrade']) : 0;

    if (!in_array($license_type, [1, 2])) {
        $license_type = 2;
    }

    if ($is_upgrade && $license_type != 2) {
        $is_upgrade = 0;
    }

    // 查询课程信息
    $course_sql = "SELECT * FROM course WHERE id = ? AND status = 1";
    $course_result = pdo_query($course_sql, $course_id);

    if (empty($course_result)) {
        $error_message = $MSG_COURSE_NOT_AVAILABLE;
    } else {
        $course = $course_result[0];
        $preview_price = floatval($course['preview_price']);
        $source_price = floatval($course['source_price']);
        $is_free_course = ($preview_price == 0 && $source_price == 0);

        /**
         * 发送飞书订单通知
         */
        function send_order_feishu_notify($course, $user_id, $order_no, $license_type, $amount, $pay_channel, $is_upgrade, $preview_price, $source_price) {
            if (!file_exists('./include/feishu_notify.php')) {
                return;
            }
            require_once('./include/feishu_notify.php');

            $license_text = '';
            if ($is_upgrade) {
                $license_text = '原文件版(升级)';
            } else {
                switch ($license_type) {
                    case 1: $license_text = '预览版'; break;
                    case 2: $license_text = '原文件版'; break;
                    default: $license_text = '完整版'; break;
                }
            }

            $is_free = ($preview_price == 0 && $source_price == 0);
            $pay_text = $pay_channel === 'free' ? '免费' : ($pay_channel === 'alipay' ? '支付宝' : $pay_channel);
            $amount_text = $is_free ? '免费' : '¥' . number_format($amount, 2);

            $content = "**课程名称**：{$course['title']}\n"
                     . "**用户ID**：{$user_id}\n"
                     . "**订单号**：{$order_no}\n"
                     . "**权限类型**：{$license_text}\n"
                     . "**支付金额**：{$amount_text}\n"
                     . "**支付方式**：{$pay_text}";

            feishu_notify('课程订单通知', $content, 'info');
        }

        function set_success_and_redirect($course_id, $success_msg) {
            global $success_message, $redirect_url;
            $success_message = $success_msg;
            $redirect_url = 'course_info.php?id=' . $course_id;
        }

        // 免费课程：创建单条权限记录（简化版：只需一条记录）
        if ($is_free_course && !$is_upgrade) {
            // 检查是否已获取过任意权限
            $check_sql = "SELECT id FROM course_order WHERE user_id = ? AND course_id = ? AND pay_status = 1";
            $check_result = pdo_query($check_sql, $user_id, $course_id);

            if (!empty($check_result)) {
                set_success_and_redirect($course_id, $MSG_GET_SUCCESS);
            } else {
                $order_no = 'CO' . time() . rand(1000, 9999);

                // 创建一条预览版权限记录（免费课程只给预览版权限，以后可以升级到原文件版）
                pdo_query(
                    "INSERT INTO course_order (order_no, user_id, course_id, license_type, amount, pay_status, pay_time, pay_channel, mail_status)
                     VALUES (?, ?, ?, 1, 0, 1, NOW(), 'free', 0)",
                    $order_no, $user_id, $course_id
                );

                // 发送飞书通知
                send_order_feishu_notify($course, $user_id, $order_no, 1, 0, 'free', false, $preview_price, $source_price);

                set_success_and_redirect($course_id, $MSG_GET_SUCCESS);
            }
        } else {
            // 付费课程或升级购买
            if ($is_upgrade) {
                $check_sql = "SELECT id FROM course_order WHERE user_id = ? AND course_id = ? AND license_type = 1 AND pay_status = 1";
                $check_result = pdo_query($check_sql, $user_id, $course_id);
                if (empty($check_result)) {
                    $error_message = "升级失败：您还未购买预览版权限";
                } else {
                    $amount = $source_price - $preview_price;
                    if ($amount <= 0) {
                        $error_message = "升级失败：价格计算错误";
                    }
                    $license_name = "原文件版(升级)";
                }
            } else {
                switch ($license_type) {
                    case 1:
                        $amount = $preview_price;
                        $license_name = "预览版";
                        break;
                    case 2:
                    default:
                        $amount = $source_price;
                        $license_name = "原文件版";
                        break;
                }
            }

            $is_paid = $amount > 0;

            // 检查是否已获取过该类型权限
            $order_sql = "SELECT * FROM course_order WHERE user_id = ? AND course_id = ? AND license_type = ?";
            $order_result = pdo_query($order_sql, $user_id, $course_id, $license_type);

            if (!empty($order_result)) {
                $order = $order_result[0];
                if ($order['pay_status'] == 1) {
                    set_success_and_redirect($course_id, $MSG_ALREADY_ACQUIRED_HINT);
                } else {
                    // 订单未支付，复用已有订单发起支付
                    if ($is_paid && !empty($pay_channel)) {
                        $pay_form = initiate_alipay_payment($order, $course);
                        if ($pay_form) {
                            echo $pay_form;
                            exit();
                        } else {
                            $error_message = "支付系统未配置，请联系管理员";
                        }
                    } else if (!$is_paid) {
                        pdo_query(
                            "UPDATE course_order SET pay_status = 1, pay_time = NOW(), pay_channel = 'free' WHERE id = ?",
                            $order['id']
                        );
                        set_success_and_redirect($course_id, $MSG_GET_SUCCESS);
                    } else {
                        $error_message = "请选择支付方式";
                    }
                }
            } else {
                $order_no = 'CO' . time() . rand(1000, 9999);

                if ($is_paid) {
                    // 付费课程，创建待支付订单
                    pdo_query(
                        "INSERT INTO course_order (order_no, user_id, course_id, license_type, amount, pay_status, pay_time, pay_channel, mail_status)
                         VALUES (?, ?, ?, ?, ?, 0, NULL, 'alipay', 0)",
                        $order_no, $user_id, $course_id, $license_type, $amount
                    );

                    // 发起支付宝支付
                    $new_order = array(
                        'order_no' => $order_no,
                        'amount' => $amount
                    );
                    $pay_form = initiate_alipay_payment($new_order, $course);
                    if ($pay_form) {
                        echo $pay_form;
                        exit();
                    } else {
                        $error_message = "支付系统未配置，请联系管理员";
                    }
                } else {
                    // 免费课程
                    pdo_query(
                        "INSERT INTO course_order (order_no, user_id, course_id, license_type, amount, pay_status, pay_time, pay_channel, mail_status)
                         VALUES (?, ?, ?, ?, 0, 1, NOW(), 'free', 0)",
                        $order_no, $user_id, $course_id, $license_type
                    );

                    // 发送飞书通知
                    send_order_feishu_notify($course, $user_id, $order_no, $license_type, 0, 'free', false, $preview_price, $source_price);

                    set_success_and_redirect($course_id, $MSG_GET_SUCCESS);
                }
            }
        }
    }
}

// GET请求：查询课程信息
if ($course_id > 0 && empty($error_message) && empty($success_message)) {
    $sql = "SELECT c.*, s.name as subject_name
            FROM course c
            INNER JOIN course_subject s ON c.subject_id = s.id
            WHERE c.id = ? AND c.status = 1";
    $result = pdo_query($sql, $course_id);

    if (empty($result)) {
        $error_message = $MSG_COURSE_NOT_AVAILABLE;
    } else {
        $course = $result[0];
        $preview_price = floatval($course['preview_price']);
        $source_price = floatval($course['source_price']);

        // 升级购买校验
        if ($is_upgrade) {
            // 检查是否已拥有预览版权限
            $check_sql = "SELECT id FROM course_order WHERE user_id = ? AND course_id = ? AND license_type = 1 AND pay_status = 1";
            $check_result = pdo_query($check_sql, $user_id, $course_id);
            if (empty($check_result)) {
                $error_message = "升级失败：您还未购买预览版权限";
            } else {
                // 计算差价
                $amount = $source_price - $preview_price;
                if ($amount <= 0) {
                    $error_message = "升级失败：价格计算错误";
                }
                $license_name = "原文件版(升级)";
            }
        } else {
            // 正常购买价格计算
            switch ($license_type) {
                case 1:
                    $amount = $preview_price;
                    $license_name = "预览版";
                    break;
                case 2:
                default:
                    $amount = $source_price;
                    $license_name = "原文件版";
                    break;
            }
        }
        $is_paid = $amount > 0;

        // 检查是否已成功获取该类型权限（pay_status = 1）
        $order_sql = "SELECT * FROM course_order WHERE user_id = ? AND course_id = ? AND license_type = ? AND pay_status = 1";
        $order_result = pdo_query($order_sql, $user_id, $course_id, $license_type);
        $is_acquired = !empty($order_result);

        // 如果已获取，获取订单信息用于重发邮件
        if ($is_acquired) {
            $order = $order_result[0];
        }
    }
}

// 模板变量
$view_course = isset($course) ? $course : null;
$view_is_acquired = isset($is_acquired) ? $is_acquired : false;
$view_is_paid = isset($is_paid) ? $is_paid : false;
$view_license_type = $license_type;
$view_license_name = isset($license_name) ? $license_name : "";
$view_amount = isset($amount) ? $amount : 0;
$view_is_upgrade = $is_upgrade;
$view_error = $error_message;
$view_success = $success_message;
$view_redirect_url = $redirect_url;
$page_title = "$MSG_COURSE_GET - $OJ_NAME";

require("template/" . $OJ_TEMPLATE . "/course_get.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
