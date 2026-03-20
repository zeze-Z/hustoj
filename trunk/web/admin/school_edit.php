<?php
require("admin-header.php");
require_once("../include/set_get_key.php");

// 权限检查
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator']) && !isSchoolAdmin()) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

if (isset($OJ_LANG)) {
    require_once("../lang/$OJ_LANG.php");
}

$school_id = intval($_GET['id']);

// 学校管理员只能编辑本校
if (isSchoolAdmin() && !isSuperAdmin()) {
    $my_school_id = getCurrentUserSchoolId();
    if ($school_id != $my_school_id) {
        echo "<script>alert('Permission denied'); history.go(-1);</script>";
        exit();
    }
}

// 获取学校信息
$sql = "SELECT * FROM `school` WHERE `id` = ?";
$result = pdo_query($sql, $school_id);
if (count($result) == 0) {
    echo "<script>alert('School not found'); history.go(-1);</script>";
    exit();
}
$school = $result[0];

$view_title = $MSG_EDIT." ".$MSG_SCHOOL;

// 处理表单提交
if (isset($_POST['do'])) {
    require_once("../include/check_post_key.php");
    
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    if (empty($name)) {
        echo "<script>alert('$MSG_SCHOOL $MSG_NAME $MSG_CANNOT_EMPTY'); history.go(-1);</script>";
        exit();
    }
    
    $result = updateSchool($school_id, $name, $code, $status);
    
    if ($result) {
        echo "<script>alert('$MSG_EDIT $MSG_SUCCESS'); window.location.href='school_list.php';</script>";
    } else {
        echo "<script>alert('$MSG_EDIT $MSG_FAILED'); history.go(-1);</script>";
    }
    exit();
}
?>

<title><?php echo $view_title?></title>
<hr>
<center><h3><?php echo $view_title?></h3></center>

<div class="padding">
    <form action="school_edit.php?id=<?php echo $school_id?>" method="post" class="form-horizontal">
        <?php require_once("../include/csrf.php"); ?>
        
        <div class="form-group">
            <label class="col-sm-2 control-label">ID</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" value="<?php echo $school['id']?>" disabled>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_SCHOOL.$MSG_NAME?></label>
            <div class="col-sm-6">
                <input type="text" name="name" class="form-control" value="<?php echo $school['name']?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_SCHOOL?> Code</label>
            <div class="col-sm-6">
                <input type="text" name="code" class="form-control" value="<?php echo $school['code']?>">
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_STATUS?></label>
            <div class="col-sm-6">
                <label>
                    <input type="checkbox" name="status" value="1" <?php echo $school['status']==1?'checked':''?>> <?php echo $MSG_ENABLED?>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="do" value="true" class="btn btn-primary">
                    <i class="glyphicon glyphicon-ok"></i> <?php echo $MSG_SUBMIT?>
                </button>
                <a href="school_list.php" class="btn btn-default">
                    <i class="glyphicon glyphicon-arrow-left"></i> <?php echo $MSG_BACK?>
                </a>
            </div>
        </div>
    </form>
</div>

<?php require("admin-footer.php");?>
