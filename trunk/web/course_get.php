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
require_once('./include/course_mail.php');

// 检查登录状态
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header("location:loginpage.php");
    exit();
}

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 初始化错误消息
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

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $pay_channel = isset($_POST['pay_channel']) ? trim($_POST['pay_channel']) : '';

    // 验证邮箱格式
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = $MSG_EMAIL_INVALID;
    } else {
        // 查询课程信息
        $course_sql = "SELECT * FROM course WHERE id = ? AND status = 1";
        $course_result = pdo_query($course_sql, $course_id);

        if (empty($course_result)) {
            $error_message = $MSG_COURSE_NOT_AVAILABLE;
        } else {
            $course = $course_result[0];
            $is_paid = floatval($course['price']) > 0;

            // 检查是否已获取过
            $order_sql = "SELECT * FROM course_order WHERE user_id = ? AND course_id = ?";
            $order_result = pdo_query($order_sql, $user_id, $course_id);

            if (!empty($order_result)) {
                $order = $order_result[0];
                if ($order['pay_status'] == 1) {
                    // 已获取，可重发邮件
                    // 检查发送频率限制（1分钟内只能发一次）
                    $last_sent = isset($order['mail_sent_at']) ? strtotime($order['mail_sent_at']) : 0;
                    $can_send = (time() - $last_sent) >= 60;

                    if ($can_send) {
                        $course_data = array(
                            'title' => $course['title'],
                            'courseware_link' => $course['courseware_link'],
                            'courseware_code' => $course['courseware_code'],
                            'lesson_plan_link' => $course['lesson_plan_link'],
                            'lesson_plan_code' => $course['lesson_plan_code']
                        );

                        if (send_course_mail($email, $course_data)) {
                            pdo_query(
                                "UPDATE course_order SET mail_status = 1, mail_sent_at = NOW() WHERE id = ?",
                                $order['id']
                            );
                            $success_message = $MSG_RESEND_SUCCESS;
                        } else {
                            $error_message = $MSG_MAIL_SEND_FAILED;
                        }
                    } else {
                        $error_message = $MSG_MAIL_TOO_FREQUENT;
                    }
                } else {
                    // 订单未支付，复用已有订单
                    if ($is_paid && !empty($pay_channel)) {
                        $pay_form = initiate_alipay_payment($order, $course);
                        if ($pay_form) {
                            echo $pay_form;
                            exit();
                        } else {
                            $error_message = "支付系统未配置，请联系管理员";
                        }
                    } else if (!$is_paid) {
                        // 免费课程，直接将已有订单标记为已支付并发送邮件
                        pdo_query(
                            "UPDATE course_order SET pay_status = 1, pay_time = NOW(), pay_channel = 'free' WHERE id = ?",
                            $order['id']
                        );
                        $course_data = array(
                            'title' => $course['title'],
                            'courseware_link' => $course['courseware_link'],
                            'courseware_code' => $course['courseware_code'],
                            'lesson_plan_link' => $course['lesson_plan_link'],
                            'lesson_plan_code' => $course['lesson_plan_code']
                        );
                        if (send_course_mail($email, $course_data)) {
                            pdo_query(
                                "UPDATE course_order SET mail_status = 1, mail_sent_at = NOW() WHERE id = ?",
                                $order['id']
                            );
                            $success_message = $MSG_GET_SUCCESS;
                        } else {
                            $error_message = $MSG_GET_SUCCESS_NO_MAIL;
                            pdo_query(
                                "UPDATE course_order SET mail_status = 2 WHERE id = ?",
                                $order['id']
                            );
                        }
                    } else {
                        $error_message = $is_paid ? "请选择支付方式" : $MSG_ORDER_UNPAID;
                    }
                }
            } else {
                // 未获取过，创建订单
                $order_no = 'CO' . time() . rand(1000, 9999);

                if ($is_paid) {
                    // 付费课程，创建待支付订单
                    pdo_query(
                        "INSERT INTO course_order (order_no, user_id, course_id, amount, email, pay_status, pay_time, pay_channel, mail_status)
                         VALUES (?, ?, ?, ?, ?, 0, NULL, 'alipay', 0)",
                        $order_no, $user_id, $course_id, $course['price'], $email
                    );

                    // 发起支付宝支付
                    $new_order = array(
                        'order_no' => $order_no,
                        'amount' => $course['price']
                    );
                    $pay_form = initiate_alipay_payment($new_order, $course);
                    if ($pay_form) {
                        echo $pay_form;
                        exit();
                    } else {
                        $error_message = "支付系统未配置，请联系管理员";
                    }
                } else {
                    // 免费课程，直接创建订单并发送邮件
                    pdo_query(
                        "INSERT INTO course_order (order_no, user_id, course_id, amount, email, pay_status, pay_time, pay_channel, mail_status)
                         VALUES (?, ?, ?, 0, ?, 1, NOW(), 'free', 0)",
                        $order_no, $user_id, $course_id, $email
                    );

                    $course_data = array(
                        'title' => $course['title'],
                        'courseware_link' => $course['courseware_link'],
                        'courseware_code' => $course['courseware_code'],
                        'lesson_plan_link' => $course['lesson_plan_link'],
                        'lesson_plan_code' => $course['lesson_plan_code']
                    );

                    if (send_course_mail($email, $course_data)) {
                        pdo_query(
                            "UPDATE course_order SET mail_status = 1, mail_sent_at = NOW() WHERE order_no = ?",
                            $order_no
                        );
                        $success_message = $MSG_GET_SUCCESS;
                    } else {
                        $error_message = $MSG_GET_SUCCESS_NO_MAIL;
                        pdo_query(
                            "UPDATE course_order SET mail_status = 2 WHERE order_no = ?",
                            $order_no
                        );
                    }
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

        // 检查是否已成功获取（pay_status = 1）
        $order_sql = "SELECT * FROM course_order WHERE user_id = ? AND course_id = ? AND pay_status = 1";
        $order_result = pdo_query($order_sql, $user_id, $course_id);
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
$view_is_paid = isset($course) && floatval($course['price']) > 0;
$view_error = $error_message;
$view_success = $success_message;
$page_title = "$MSG_COURSE_GET - $OJ_NAME";

require("template/" . $OJ_TEMPLATE . "/course_get.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
