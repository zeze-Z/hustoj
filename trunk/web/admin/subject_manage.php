<?php
/**
 * 学科分类管理页面
 * 支持：列表展示、添加、编辑、启用/禁用、删除、拖拽排序
 */
require("admin-header.php");
require_once("../include/set_get_key.php");

// 权限检查
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

// 处理添加/编辑提交
if (isset($_POST['do'])) {
    require_once("../include/check_post_key.php");

    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    $name = trim($_POST['name']);
    $sort_order = intval($_POST['sort_order']);
    $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

    // 校验名称
    if (empty($name)) {
        echo "<script>alert('学科名称不能为空'); history.go(-1);</script>";
        exit();
    }

    if (mb_strlen($name) > 100) {
        echo "<script>alert('学科名称不能超过100个字符'); history.go(-1);</script>";
        exit();
    }

    if ($action === 'add') {
        // 检查名称是否重复
        $check = pdo_query("SELECT COUNT(*) AS cnt FROM `course_subject` WHERE `name` = ?", $name);
        if ($check[0]['cnt'] > 0) {
            echo "<script>alert('学科名称已存在'); history.go(-1);</script>";
            exit();
        }

        $sql = "INSERT INTO `course_subject` (`name`, `sort_order`, `status`) VALUES (?, ?, ?)";
        try {
            pdo_query($sql, $name, $sort_order, $status);
            echo "<script>alert('添加成功'); window.location.href='subject_manage.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('添加失败: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "'); history.go(-1);</script>";
        }
    } elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        if ($id <= 0) {
            echo "<script>alert('参数错误'); history.go(-1);</script>";
            exit();
        }

        // 检查名称是否重复（排除自身）
        $check = pdo_query("SELECT COUNT(*) AS cnt FROM `course_subject` WHERE `name` = ? AND `id` != ?", $name, $id);
        if ($check[0]['cnt'] > 0) {
            echo "<script>alert('学科名称已存在'); history.go(-1);</script>";
            exit();
        }

        $sql = "UPDATE `course_subject` SET `name` = ?, `sort_order` = ?, `status` = ? WHERE `id` = ?";
        try {
            pdo_query($sql, $name, $sort_order, $status, $id);
            echo "<script>alert('修改成功'); window.location.href='subject_manage.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('修改失败: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "'); history.go(-1);</script>";
        }
    }
    exit();
}

// 查询学科列表（LEFT JOIN一次性统计关联课程数，避免循环内逐行查询的N+1问题）
$subjects = pdo_query("SELECT cs.*, COUNT(c.id) AS course_count
    FROM `course_subject` cs
    LEFT JOIN `course` c ON cs.id = c.subject_id
    GROUP BY cs.id
    ORDER BY cs.sort_order ASC, cs.id ASC");
?>

<title>学科管理</title>
<hr>
<center><h3>学科管理</h3></center>

<?php require_once("../include/set_post_key.php"); ?>

<style>
    .subject-table { width: 100%; border-collapse: collapse; }
    .subject-table th, .subject-table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
    .subject-table th { background: #f5f5f5; }
    .subject-table tr:hover { background: #f9f9f9; }
    .drag-handle { cursor: move; color: #337ab7; }
    .dragging { opacity: 0.5; }
    .btn-sm { padding: 4px 8px; font-size: 12px; }
    .inline-edit { display: none; }
    .inline-edit input { width: 200px; }
    .status-on { color: #5cb85c; }
    .status-off { color: #d9534f; }
</style>

<div class="padding">
    <!-- 添加学科按钮 -->
    <div style="margin-bottom: 15px;">
        <button class="btn btn-success btn-sm" onclick="showAddForm()">
            <i class="glyphicon glyphicon-plus"></i> 添加学科
        </button>
    </div>

    <!-- 添加学科表单（默认隐藏） -->
    <div id="add-form" style="display: none; margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
        <form action="subject_manage.php" method="post" style="display: inline;">
            <input type="hidden" name="do" value="true">
            <input type="hidden" name="action" value="add">
            <?php require_once("../include/set_post_key.php"); ?>
            <label>学科名称：</label>
            <input type="text" name="name" required maxlength="100" style="width: 200px;">
            <label>排序号：</label>
            <input type="number" name="sort_order" value="0" style="width: 80px;">
            <label>状态：</label>
            <select name="status">
                <option value="1">启用</option>
                <option value="0">禁用</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">保存</button>
            <button type="button" class="btn btn-default btn-sm" onclick="hideAddForm()">取消</button>
        </form>
    </div>

    <!-- 学科列表 -->
    <table class="subject-table" id="subject-table">
        <thead>
            <tr>
                <th style="width: 60px;">排序</th>
                <th style="width: 60px;">ID</th>
                <th>学科名称</th>
                <th style="width: 80px;">状态</th>
                <th style="width: 150px;">关联课程数</th>
                <th style="width: 200px;">操作</th>
            </tr>
        </thead>
        <tbody id="subject-sort-body">
            <?php foreach ($subjects as $subject):
                $course_count = intval($subject['course_count']);
            ?>
            <tr data-id="<?php echo $subject['id'] ?>" draggable="true">
                <td class="drag-handle">≡</td>
                <td><?php echo $subject['id'] ?></td>
                <td>
                    <span class="display-mode"><?php echo htmlentities($subject['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="inline-edit">
                        <input type="text" class="edit-name" value="<?php echo htmlentities($subject['name'], ENT_QUOTES, 'UTF-8') ?>" maxlength="100">
                    </span>
                </td>
                <td>
                    <?php if ($subject['status'] == 1): ?>
                        <span class="status-on">✓ 启用</span>
                    <?php else: ?>
                        <span class="status-off">✗ 禁用</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $course_count ?></td>
                <td>
                    <span class="display-mode">
                        <button class="btn btn-primary btn-xs" onclick="editSubject(this)">编辑</button>
                        <?php if ($subject['status'] == 1): ?>
                            <button class="btn btn-warning btn-xs" onclick="toggleStatus(<?php echo $subject['id'] ?>, 0)">禁用</button>
                        <?php else: ?>
                            <button class="btn btn-success btn-xs" onclick="toggleStatus(<?php echo $subject['id'] ?>, 1)">启用</button>
                        <?php endif; ?>
                        <?php if ($course_count == 0): ?>
                            <button class="btn btn-danger btn-xs" onclick="deleteSubject(<?php echo $subject['id'] ?>)">删除</button>
                        <?php else: ?>
                            <button class="btn btn-default btn-xs" disabled title="存在关联课程，无法删除">删除</button>
                        <?php endif; ?>
                    </span>
                    <span class="inline-edit">
                        <button class="btn btn-primary btn-xs" onclick="saveEdit(this, <?php echo $subject['id'] ?>)">保存</button>
                        <button class="btn btn-default btn-xs" onclick="cancelEdit(this)">取消</button>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
var postkey = "<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']; ?>";

// 显示/隐藏添加表单
function showAddForm() {
    document.getElementById('add-form').style.display = 'block';
}
function hideAddForm() {
    document.getElementById('add-form').style.display = 'none';
}

// 编辑模式
function editSubject(btn) {
    var row = btn.closest('tr');
    row.querySelector('.display-mode').style.display = 'none';
    row.querySelectorAll('.inline-edit').forEach(function(el) { el.style.display = 'inline'; });
}

// 取消编辑
function cancelEdit(btn) {
    var row = btn.closest('tr');
    row.querySelector('.display-mode').style.display = '';
    row.querySelectorAll('.inline-edit').forEach(function(el) { el.style.display = 'none'; });
    // 恢复原值
    var originalName = row.querySelector('.edit-name').defaultValue;
    row.querySelector('.edit-name').value = originalName;
}

// 保存编辑
function saveEdit(btn, id) {
    var row = btn.closest('tr');
    var name = row.querySelector('.edit-name').value.trim();

    if (!name) {
        alert('学科名称不能为空');
        return;
    }

    // 禁用按钮防止重复提交
    var saveBtn = row.querySelector('.inline-edit .btn-primary');
    saveBtn.disabled = true;
    saveBtn.textContent = '保存中...';

    $.post('subject_api.php', {
        action: 'update',
        id: id,
        name: name,
        postkey: postkey
    }, function(data) {
        if (data.success) {
            // 直接刷新页面
            window.location.reload();
        } else {
            saveBtn.disabled = false;
            saveBtn.textContent = '保存';
            alert(data.message || '修改失败');
        }
    }, 'json');
}

// 切换状态
function toggleStatus(id, status) {
    $.post('subject_api.php', {
        action: 'status',
        id: id,
        status: status,
        postkey: postkey
    }, function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || '操作失败');
        }
    }, 'json');
}

// 删除学科
function deleteSubject(id) {
    if (!confirm('确定要删除这个学科吗？')) {
        return;
    }

    $.post('subject_api.php', {
        action: 'delete',
        id: id,
        postkey: postkey
    }, function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || '删除失败');
        }
    }, 'json');
}

// 拖拽排序
var draggedRow = null;
var orderChanged = false;

$('#subject-sort-body tr').on('dragstart', function(e) {
    draggedRow = this;
    orderChanged = false;
    $(this).addClass('dragging');
    e.originalEvent.dataTransfer.effectAllowed = 'move';
    e.originalEvent.dataTransfer.setData('text/plain', $(this).data('id'));
});

$('#subject-sort-body tr').on('dragover', function(e) {
    e.preventDefault();
    if (!draggedRow || draggedRow === this) return;

    var rect = this.getBoundingClientRect();
    var next = (e.originalEvent.clientY - rect.top) > (rect.height / 2);
    if (next) {
        $(this).after(draggedRow);
    } else {
        $(this).before(draggedRow);
    }
    orderChanged = true;
});

$('#subject-sort-body tr').on('dragend', function() {
    $(this).removeClass('dragging');
    draggedRow = null;
    if (orderChanged) {
        saveOrder();
    }
});

function saveOrder() {
    var ids = [];
    $('#subject-sort-body tr').each(function() {
        ids.push($(this).data('id'));
    });

    $.post('subject_api.php', {
        action: 'sort',
        ids: ids,
        postkey: postkey
    }, function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || '排序保存失败');
        }
    }, 'json');
}
</script>

<?php require("admin-footer.php"); ?>
