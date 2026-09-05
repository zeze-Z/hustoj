<?php
/**
 * 学科管理AJAX接口
 * 支持：更新名称、切换状态、删除、排序
 */
require_once("../include/db_info.inc.php");
require_once("../include/const.inc.php");
require_once("../include/setlang.php");
session_start();

// 设置JSON响应头
header('Content-Type: application/json; charset=utf-8');

// 权限检查
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit();
}

// CSRF校验
require_once("../include/check_post_key.php");

// 获取操作类型
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

switch ($action) {
    case 'update':
        handleUpdate();
        break;
    case 'status':
        handleStatusChange();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'sort':
        handleSort();
        break;
    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
        break;
}

/**
 * 更新学科名称
 */
function handleUpdate() {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        return;
    }

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => '学科名称不能为空']);
        return;
    }

    if (mb_strlen($name) > 100) {
        echo json_encode(['success' => false, 'message' => '学科名称不能超过100个字符']);
        return;
    }

    // 检查名称是否重复（排除自身）
    $check = pdo_query("SELECT COUNT(*) AS cnt FROM `course_subject` WHERE `name` = ? AND `id` != ?", $name, $id);
    if ($check[0]['cnt'] > 0) {
        echo json_encode(['success' => false, 'message' => '学科名称已存在']);
        return;
    }

    try {
        pdo_query("UPDATE `course_subject` SET `name` = ? WHERE `id` = ?", $name, $id);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '更新失败: ' . $e->getMessage()]);
    }
}

/**
 * 切换学科状态
 */
function handleStatusChange() {
    $id = intval($_POST['id']);
    $status = intval($_POST['status']);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        return;
    }

    if ($status !== 0 && $status !== 1) {
        echo json_encode(['success' => false, 'message' => '状态值无效']);
        return;
    }

    try {
        pdo_query("UPDATE `course_subject` SET `status` = ? WHERE `id` = ?", $status, $id);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '操作失败: ' . $e->getMessage()]);
    }
}

/**
 * 删除学科
 */
function handleDelete() {
    $id = intval($_POST['id']);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        return;
    }

    // 检查是否有关联课程
    $check = pdo_query("SELECT COUNT(*) AS cnt FROM `course` WHERE `subject_id` = ?", $id);
    if ($check[0]['cnt'] > 0) {
        echo json_encode(['success' => false, 'message' => '该学科下还有 ' . $check[0]['cnt'] . ' 个课程，无法删除']);
        return;
    }

    try {
        pdo_query("DELETE FROM `course_subject` WHERE `id` = ?", $id);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
    }
}

/**
 * 更新排序
 */
function handleSort() {
    $ids = isset($_POST['ids']) ? $_POST['ids'] : array();

    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => '参数错误']);
        return;
    }

    try {
        foreach ($ids as $index => $id) {
            $id = intval($id);
            $sort_order = $index + 1;
            pdo_query("UPDATE `course_subject` SET `sort_order` = ? WHERE `id` = ?", $sort_order, $id);
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '排序保存失败: ' . $e->getMessage()]);
    }
}
