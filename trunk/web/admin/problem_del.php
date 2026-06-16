<?php
 require_once("admin-header.php");
ini_set("display_errors","On");

// CSRF 校验
if (isset($_POST['pid'])) {
    require_once("../include/check_post_key.php");
} else if (isset($_GET['id'])) {
    require_once("../include/check_get_key.php");
}

// 权限检查
$can_delete = false;
if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    $can_delete = true;
} else if (isset($_POST['pid']) && is_array($_POST['pid'])) {
    // 批量删除时检查每个题目的权限
    $can_delete = true;
    foreach ($_POST['pid'] as $pid) {
        if (!isset($_SESSION[$OJ_NAME.'_'."p".intval($pid)])) {
            $can_delete = false;
            break;
        }
    }
} else if (isset($_GET['id'])) {
    // 单个删除时检查权限
    $pid = intval($_GET['id']);
    if (isset($_SESSION[$OJ_NAME.'_'."p".$pid])) {
        $can_delete = true;
    }
}

if (!$can_delete) {
  echo "<a href='../loginpage.php'>Please Login First!</a>";
  exit(1);
}

function recursiveDelete($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    recursiveDelete($path);
                } else {
                    unlink($path);
                }
            }
        }
        rmdir($dir);
    }
}

// 获取要删除的题目ID列表
$ids_to_delete = array();
if (isset($_POST['pid']) && is_array($_POST['pid'])) {
    // 批量删除
    foreach ($_POST['pid'] as $pid) {
        $pid = intval($pid);
        if ($pid > 0) {
            $ids_to_delete[] = $pid;
        }
    }
} else if (isset($_GET['id'])) {
    // 单个删除
    $id = intval($_GET['id']);
    if ($id > 0) {
        $ids_to_delete[] = $id;
    }
}

if (!empty($ids_to_delete) && strlen($OJ_DATA) > 8) {
    foreach ($ids_to_delete as $id) {
        // 删除测试数据目录
        $basedir = "$OJ_DATA/$id";
        if (strlen($basedir) > 16 && $id > 0) {
            recursiveDelete($basedir);
        }

        // 删除题目记录
        $sql = "delete FROM `problem` WHERE `problem_id`=?";
        pdo_query($sql, $id);

        // 删除权限记录
        $sql = "delete from `privilege` where `rightstr`=? ";
        pdo_query($sql, "p$id");

        // 更新提交记录中的题目ID
        $sql = "update solution set problem_id=0 where `problem_id`=? ";
        pdo_query($sql, $id);
    }

    // 更新自增ID
    $sql="select max(problem_id) FROM `problem`" ;
    $result=pdo_query($sql);
    $row=$result[0];
    $max_id=$row[0];
    $max_id++;
    if($max_id<1000)$max_id=1000;

    $sql="ALTER TABLE problem AUTO_INCREMENT = $max_id";
    pdo_query($sql);
    ?>
    <script language=javascript>
            history.go(-1);
    </script>
<?php
  } else {
?>
        <script language=javascript>
                alert("操作失败！");
                history.go(-1);
        </script>
  <?php
  }
?>
