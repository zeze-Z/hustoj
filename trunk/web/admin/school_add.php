<?php
require("admin-header.php");
require_once("../include/set_get_key.php");

// 强制加载语言文件
if (isset($OJ_LANG)) {
    require_once("../lang/$OJ_LANG.php");
}

// 仅超管可添加学校
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

$view_title = $MSG_ADD . " " . $MSG_SCHOOL;

// 处理表单提交
if (isset($_POST['do'])) {
    require_once("../include/check_post_key.php");

    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($name)) {
        echo "<script>alert('$MSG_SCHOOL_NAME$MSG_CANNOT_EMPTY'); history.go(-1);</script>";
        exit();
    }

    $result = addSchool($name, $code, $status);

    if ($result) {
        echo "<script>alert('$MSG_ADD $MSG_SUCCESS'); window.location.href='school_list.php';</script>";
    } else {
        echo "<script>alert('$MSG_ADD $MSG_FAILED'); history.go(-1);</script>";
    }
    exit();
}
?>

<title><?php echo $view_title ?></title>
<hr>
<center><h3><?php echo $view_title ?></h3></center>

<div class="padding">
    <form action="school_add.php" method="post" class="form-horizontal">
        <?php require_once("../include/set_post_key.php"); ?>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_SCHOOL_NAME ?></label>
            <div class="col-sm-6">
                <input type="text" name="name" class="form-control" placeholder="<?php echo $MSG_SCHOOL_NAME ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_SCHOOL ?> Code</label>
            <div class="col-sm-6">
                <input type="text" name="code" class="form-control" placeholder="Code">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_STATUS ?></label>
            <div class="col-sm-6">
                <label>
                    <input type="checkbox" name="status" value="1" checked> <?php echo $MSG_ENABLED ?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="do" value="true" class="btn btn-primary">
                    <i class="glyphicon glyphicon-ok"></i> <?php echo $MSG_SUBMIT ?>
                </button>
                <a href="school_list.php" class="btn btn-default">
                    <i class="glyphicon glyphicon-arrow-left"></i> <?php echo $MSG_BACK ?>
                </a>
            </div>
        </div>
    </form>
</div>

<?php require("admin-footer.php"); ?>
