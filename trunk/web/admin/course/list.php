<?php
require("../admin-header.php");
require_once("../../include/set_get_key.php");

// 强制加载语言文件
if (isset($OJ_LANG)) {
    require_once("../../lang/$OJ_LANG.php");
}

// 权限检查：仅管理员可访问
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../../loginpage.php'>Please Login First!</a>";
    exit(1);
}
?>

<title><?php echo $MSG_COURSE . "-" . $MSG_LIST ?></title>
<hr>
<center><h3><?php echo $MSG_COURSE . "-" . $MSG_LIST ?></h3></center>

<?php
// 查询总数
$sql = "SELECT COUNT(*) AS ids FROM `course`";
try {
    $result = pdo_query($sql);
    $row = $result[0];
    $ids = intval($row['ids']);
} catch (Exception $e) {
    echo "<script>alert('数据库查询失败: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "'); history.go(-1);</script>";
    exit(1);
}

$idsperpage = 25;
$pages = intval(ceil($ids / $idsperpage));

if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
} else {
    $page = 1;
}

$pagesperframe = 5;
$frame = intval(ceil($page / $pagesperframe));

$spage = ($frame - 1) * $pagesperframe + 1;
$epage = min($spage + $pagesperframe - 1, $pages);
$sid = ($page - 1) * $idsperpage;

// 查询课程列表（关联学科表）
$sql = "SELECT c.*, s.name as subject_name
        FROM `course` c
        LEFT JOIN `course_subject` s ON c.subject_id = s.id
        ORDER BY c.sort_order ASC, c.id DESC
        LIMIT $sid, $idsperpage";
try {
    $result = pdo_query($sql);
} catch (Exception $e) {
    echo "<script>alert('数据库查询失败: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "'); history.go(-1);</script>";
    exit(1);
}
?>

<div class="padding">
    <center>
        <table width="100%" border="1" style="text-align:center;">
            <tr style='height:22px;'>
                <td>ID</td>
                <td><?php echo $MSG_COURSE_TITLE ?></td>
                <td><?php echo $MSG_COURSE_SUBJECT ?></td>
                <td><?php echo $MSG_PRICE ?></td>
                <td><?php echo $MSG_LINK_EXPIRE_DATE ?></td>
                <td><?php echo $MSG_STATUS ?></td>
                <td><?php echo $MSG_OPERATOR ?></td>
            </tr>
            <?php
            foreach ($result as $row) {
            ?>
            <tr style='height:22px;' course_id='<?php echo $row['id'] ?>'>
                <td><?php echo $row['id'] ?></td>
                <td><?php echo htmlentities($row['title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php echo htmlentities($row['subject_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php echo $row['price'] ?></td>
                <td><?php echo $row['link_expire_date'] ?></td>
                <td>
                    <?php if ($row['status'] == 1) { ?>
                        <span class="green"><?php echo $MSG_AVAILABLE ?></span>
                    <?php } else { ?>
                        <span class="red"><?php echo $MSG_RESERVED ?></span>
                    <?php } ?>
                </td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id'] ?>"><?php echo $MSG_EDIT ?></a>
                    <?php if ($row['status'] == 1) { ?>
                        | <a href="#" onclick="changeStatus(<?php echo $row['id'] ?>, 0, '<?php echo $MSG_RESERVED ?>')"><?php echo $MSG_RESERVED ?></a>
                    <?php } else { ?>
                        | <a href="#" onclick="changeStatus(<?php echo $row['id'] ?>, 1, '<?php echo $MSG_AVAILABLE ?>')"><?php echo $MSG_AVAILABLE ?></a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </center>
</div>

<?php
// 分页
echo "<div style='display:inline;'>";
echo "<nav class='center'>";
echo "<ul class='pagination pagination-sm'>";
echo "<li class='page-item'><a href='list.php?page=" . (strval(1)) . "'>&lt;&lt;</a></li>";
echo "<li class='page-item'><a href='list.php?page=" . ($page == 1 ? strval(1) : strval($page - 1)) . "'>&lt;</a></li>";

for ($i = $spage; $i <= $epage; $i++) {
    echo "<li class='" . ($page == $i ? "active " : "") . "page-item'><a title='go to page' href='list.php?page=$i'>$i</a></li>";
}

echo "<li class='page-item'><a href='list.php?page=" . ($page == $pages ? strval($page) : strval($page + 1)) . "'>&gt;</a></li>";
echo "<li class='page-item'><a href='list.php?page=" . (strval($pages)) . "'>&gt;&gt;</a></li>";
echo "</ul>";
echo "</nav>";
echo "</div>";
?>

<script>
function changeStatus(id, status, actionName) {
    if (confirm('Confirm to ' + actionName + ' this course?')) {
        <?php require_once("../../include/set_post_key.php"); ?>
        $.post("status_change.php", {
            id: id,
            status: status,
            postkey: "<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']; ?>"
        }, function(data) {
            if (data === 'success') {
                window.location.reload();
            } else {
                alert('Operation failed: ' + data);
            }
        });
    }
}
</script>

<?php require("../admin-footer.php"); ?>
