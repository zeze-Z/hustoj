<?php
/**
 * 课程获取页面（积分支付版）
 * 免费课程领取、付费课程统一使用平台积分支付，不再走支付宝渠道。
 */

require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');
require_once('./include/cache_start.php');
require_once('./include/setlang.php');
require_once("./include/set_get_key.php");

// 检查登录状态
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header("location:loginpage.php");
    exit();
}

$user_id = $_SESSION[$OJ_NAME.'_'.'user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$license_type = isset($_GET['type']) ? intval($_GET['type']) : 1; // 默认完整预览版
$is_upgrade = isset($_GET['upgrade']) ? intval($_GET['upgrade']) : 0;

$error_message = '';
$success_message = '';
$redirect_url = '';

// 参数合法性校验
if (!in_array($license_type, [1, 2])) {
    $license_type = 1; // 非法值默认完整预览版
}

// 升级只能是升级到原文件版
if ($is_upgrade && $license_type != 2) {
    $is_upgrade = 0;
}

/**
 * 成功跳转设置（POST 与 GET 共用）
 */
function set_success_and_redirect($course_id, $success_msg) {
    global $success_message, $redirect_url;
    $success_message = $success_msg;
    $redirect_url = 'course_info.php?id=' . $course_id;
}

// ---------------------------------------------------------------
// POST：积分支付 / 免费领取（不再发起支付宝）
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once('./include/check_post_key.php');

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $license_type = isset($_POST['license_type']) ? intval($_POST['license_type']) : 1;
    $is_upgrade = isset($_POST['upgrade']) ? intval($_POST['upgrade']) : 0;

    if (!in_array($license_type, [1, 2])) {
        $license_type = 1;
    }
    if ($is_upgrade && $license_type != 2) {
        $is_upgrade = 0;
    }

    if ($course_id <= 0) {
        $error_message = $MSG_COURSE_NOT_AVAILABLE;
    } else {
        // 服务端重新计算权限与价格（不信任前端 POST 字段）
        $permission = get_user_course_permission($user_id, $course_id);
        $course = $permission['course'];

        if (empty($course)) {
            $error_message = $MSG_COURSE_NOT_AVAILABLE;
        } else {
            $has_permission = ($license_type == 1 && $permission['has_full_preview']) ||
                              ($license_type == 2 && $permission['has_source']);
            if ($has_permission) {
                set_success_and_redirect($course_id, $MSG_ALREADY_ACQUIRED_HINT);
            } else {
                $amount = calculate_course_price($course, $license_type, $is_upgrade);
                $point_amount = intval(ceil(max(0.0, floatval($amount))));

                if ($point_amount <= 0) {
                    // 免费课程 / 升级差价为 0：直接授予权限
                    $grant = grant_course_license($user_id, $course_id, $license_type, array(
                        'pay_channel' => 'free',
                        'amount' => 0,
                        'is_upgrade' => $is_upgrade,
                    ));
                    if ($grant['success']) {
                        set_success_and_redirect($course_id, $MSG_GET_SUCCESS);
                    } else {
                        $error_message = $grant['message'];
                    }
                } else {
                    // 积分支付：服务端再次重算价格 / 校验余额 / 扣减积分 / 登记订单
                    $pay = point_pay_for_course($user_id, $course_id, $license_type, $is_upgrade ? true : false);
                    if ($pay['success']) {
                        set_success_and_redirect($course_id, '积分支付成功，已为您开通权限');
                    } else {
                        $error_message = $pay['message'];
                    }
                }
            }
        }
    }
}

// ---------------------------------------------------------------
// GET：展示积分支付确认页
// ---------------------------------------------------------------
$preview_price = 0;
$source_price = 0;
$amount = 0;
$license_name = '';
$required_points = 0;
$user_balance = 0;
$balance_after = 0;
$enough_balance = false;
$is_acquired = false;
$is_paid = false;
$course = null;

if ($course_id <= 0) {
    if (empty($error_message)) {
        $error_message = $MSG_COURSE_NOT_AVAILABLE;
    }
} else if (empty($success_message)) {
    $permission = get_user_course_permission($user_id, $course_id);
    $course = $permission['course'];

    if (empty($course)) {
        $error_message = $MSG_COURSE_NOT_AVAILABLE;
    } else {
        // 升级 / 重复购买校验
        if ($is_upgrade) {
            if (!$permission['has_full_preview']) {
                $error_message = '升级失败：您还未拥有完整预览版权限';
            } elseif ($permission['has_source']) {
                $error_message = '您已经拥有原文件版权限，无需重复升级';
            } elseif ($permission['upgrade_price'] <= 0 && floatval($permission['source_price']) > 0) {
                $error_message = '升级失败：价格计算错误';
            }
        } else {
            if (($license_type == 1 && $permission['has_full_preview']) ||
                ($license_type == 2 && $permission['has_source'])) {
                $is_acquired = true;
            }
        }

        $preview_price = floatval($permission['full_preview_price']);
        $source_price = floatval($permission['source_price']);

        // 计算应付积分（1 积分 = 1 元，向上取整）
        if ($is_upgrade) {
            $amount = floatval($permission['upgrade_price']);
            $license_name = '原文件版(升级)';
        } else {
            switch ($license_type) {
                case 1:
                    $amount = $preview_price;
                    $license_name = '完整预览版';
                    break;
                case 2:
                default:
                    $amount = $source_price;
                    $license_name = '原文件版';
                    break;
            }
        }
        if ($amount < 0) $amount = 0;
        $required_points = intval(ceil(floatval($amount)));
        $is_paid = $required_points > 0;

        // 查询用户积分余额
        $user_balance = intval(point_get_balance($user_id));
        $balance_after = $user_balance - $required_points;
        $enough_balance = $user_balance >= $required_points;
    }
}

// 模板变量
$view_course = $course;
$view_is_acquired = $is_acquired;
$view_is_paid = $is_paid;
$view_license_type = $license_type;
$view_license_name = $license_name;
$view_required_points = $required_points;
$view_preview_price = $preview_price;
$view_source_price = $source_price;
$view_upgrade_deduct_points = $is_upgrade ? intval(ceil(max(0.0, floatval($preview_price)))) : 0;
$view_user_balance = $user_balance;
$view_balance_after = $balance_after;
$view_enough_balance = $enough_balance;
$view_is_upgrade = $is_upgrade;
$view_error = $error_message;
$view_success = $success_message;
$view_redirect_url = $redirect_url;
$page_title = "$MSG_COURSE_GET - $OJ_NAME";

require("template/" . $OJ_TEMPLATE . "/course_get.php");

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
