<?php
require("admin-header.php");
require_once("../include/set_get_key.php");

// 权限检查：仅超管和学校管理员可访问
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator']) && !isSchoolAdmin()) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

if (isset($OJ_LANG)) {
    require_once("../lang/$OJ_LANG.php");
}
?>

<title><?php echo $MSG_SCHOOL . "-" . $MSG_LIST ?></title>
<hr>
<center><h3><?php echo $MSG_SCHOOL . "-" . $MSG_LIST ?></h3></center>

<?php
// 学校管理员只能看本校
$school_filter = '';
if (isSchoolAdmin() && !isSuperAdmin()) {
    $school_id = getCurrentUserSchoolId();
    $school_filter = " WHERE id = " . intval($school_id);
}

$sql = "SELECT COUNT(*) AS ids FROM `school`" . $school_filter;
$result = pdo_query($sql);
$row = $result[0];

$ids = intval($row['ids']);
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

$sql = "SELECT * FROM `school`" . $school_filter . " ORDER BY `id` ASC LIMIT $sid, $idsperpage";
$result = pdo_query($sql);
?>

<div class="padding">
    <form action="school_add.php" method="post" style="margin-bottom: 10px;">
        <?php require_once("../include/csrf.php"); ?>
        <button type="submit" class="btn btn-success">
            <i class="glyphicon glyphicon-plus"></i> <?php echo $MSG_ADD . " " . $MSG_SCHOOL ?>
        </button>
    </form>

    <center>
        <table width="100%" border="1" style="text-align:center;">
            <tr style='height:22px;'>
                <td>ID</td>
                <td><?php echo $MSG_SCHOOL_NAME ?></td>
                <td><?php echo $MSG_SCHOOL ?> Code</td>
                <td><?php echo $MSG_STATUS ?></td>
                <td><?php echo $MSG_USER_COUNT ?></td>
                <td><?php echo $MSG_OPERATOR ?></td>
            </tr>
            <?php
            foreach ($result as $row) {
                // 统计用户数
                $user_count = pdo_query("SELECT COUNT(*) as cnt FROM `users` WHERE `school_id` = ?", $row['id']);
                $user_count = $user_count[0]['cnt'];
            ?>
            <tr style='height:22px;' school_id='<?php echo $row['id'] ?>'>
                <td><?php echo $row['id'] ?></td>
                <td><?php echo htmlentities($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php echo htmlentities($row['code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ($row['status'] == 1) { ?>
                        <span class="green"><?php echo $MSG_AVAILABLE ?></span>
                    <?php } else { ?>
                        <span class="red"><?php echo $MSG_RESERVED ?></span>
                    <?php } ?>
                </td>
                <td><?php echo $user_count ?></td>
                <td>
                    <a href="school_edit.php?id=<?php echo $row['id'] ?>"><?php echo $MSG_EDIT ?></a>
                    <?php if ($user_count == 0) { ?>
                        |
                        <a href="school_del.php?id=<?php echo $row['id'] ?>&getkey=<?php echo $_SESSION[$OJ_NAME.'_getkey'] ?>" 
                           onclick="return confirm('<?php echo $MSG_CONFIRM_DELETE ?>')"><?php echo $MSG_DELETE ?></a>
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
echo "<li class='page-item'><a href='school_list.php?page=" . (strval(1)) . "'>&lt;&lt;</a></li>";
echo "<li class='page-item'><a href='school_list.php?page=" . ($page == 1 ? strval(1) : strval($page - 1)) . "'>&lt;</a></li>";

for ($i = $spage; $i <= $epage; $i++) {
    echo "<li class='" . ($page == $i ? "active " : "") . "page-item'><a title='go to page' href='school_list.php?page=$i'>$i</a></li>";
}

echo "<li class='page-item'><a href='school_list.php?page=" . ($page == $pages ? strval($page) : strval($page + 1)) . "'>&gt;</a></li>";
echo "<li class='page-item'><a href='school_list.php?page=" . (strval($pages)) . "'>&gt;&gt;</a></li>";
echo "</ul>";
echo "</nav>";
echo "</div>";
?>

<?php require("admin-footer.php"); ?>
