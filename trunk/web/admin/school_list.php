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
<title><?php echo $MSG_SCHOOL."-".$MSG_LIST?></title>
<hr>
<center><h3><?php echo $MSG_SCHOOL."-".$MSG_LIST?></h3></center>

<?php
// 学校管理员只能看本校
$school_filter = '';
if (isSchoolAdmin() && !isSuperAdmin()) {
    $school_id = getCurrentUserSchoolId();
    $school_filter = " WHERE id = $school_id";
}

$sql = "SELECT COUNT(*) AS ids FROM `school`" . $school_filter;
$result = pdo_query($sql);
$row = $result[0];
$ids = intval($row['ids']);
$idsperpage = 25;
$pages = intval(ceil($ids/$idsperpage));

if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
} else {
    $page = 1;
}

$pagesperframe = 5;
$frame = intval(ceil($page/$pagesperframe));
$spage = ($frame-1)*$pagesperframe+1;
$epage = min($spage+$pagesperframe-1, $pages);
$sid = ($page-1)*$idsperpage;

$sql = "SELECT * FROM `school`" . $school_filter . " ORDER BY `id` ASC LIMIT $sid, $idsperpage";
$result = pdo_query($sql);
?>

<div class="padding">
    <form action="school_add.php" method="post" style="margin-bottom: 10px;">
        <?php require_once("../include/csrf.php"); ?>
        <button type="submit" class="btn btn-success">
            <i class="glyphicon glyphicon-plus"></i> <?php echo $MSG_ADD." ".$MSG_SCHOOL?>
        </button>
    </form>

    <table class="table table-striped" style="width: 100%;">
        <thead>
            <tr>
                <th>ID</th>
                <th><?php echo $MSG_SCHOOL_NAME?></th>
                <th><?php echo $MSG_SCHOOL?>Code</th>
                <th><?php echo $MSG_STATUS?></th>
                <th><?php echo $MSG_USER.$MSG_COUNT?></th>
                <th><?php echo $MSG_OPERATOR?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row) { 
                // 统计用户数
                $user_count = pdo_query("SELECT COUNT(*) as cnt FROM `users` WHERE `school_id` = ?", $row['id'])[0]['cnt'];
            ?>
            <tr>
                <td><?php echo $row['id']?></td>
                <td><?php echo $row['name']?></td>
                <td><?php echo $row['code']?></td>
                <td>
                    <?php if ($row['status'] == 1) { ?>
                        <span class="label label-success"><?php echo $MSG_AVAILABLE?></span>
                    <?php } else { ?>
                        <span class="label label-default"><?php echo $MSG_RESERVED?></span>
                    <?php } ?>
                </td>
                <td><?php echo $user_count?></td>
                <td>
                    <a href="school_edit.php?id=<?php echo $row['id']?>" class="btn btn-sm btn-primary">
                        <i class="glyphicon glyphicon-edit"></i>
                    </a>
                    <?php if ($user_count == 0) { ?>
                    <a href="school_del.php?id=<?php echo $row['id']?>&getkey=<?php echo $_SESSION[$OJ_NAME.'_getkey']?>" 
                       class="btn btn-sm btn-danger" onclick="return confirm('<?php echo $MSG_CONFIRM_DELETE?>');">
                        <i class="glyphicon glyphicon-trash"></i>
                    </a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
// 分页
echo "<center>";
if ($page > 1) {
    echo "<a href='school_list.php?page=1'>|&lt;</a> ";
    echo "<a href='school_list.php?page=".($page-1)."'>&lt;</a> ";
}
for ($i = $spage; $i <= $epage; $i++) {
    if ($i == $page) {
        echo "<b>$i</b> ";
    } else {
        echo "<a href='school_list.php?page=$i'>$i</a> ";
    }
}
if ($page < $pages) {
    echo "<a href='school_list.php?page=".($page+1)."'>&gt;</a> ";
    echo "<a href='school_list.php?page=$pages'>&gt;|</a> ";
}
echo "</center>";
?>

<?php require("admin-footer.php");?>
