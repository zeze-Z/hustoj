<?php
require("admin-header.php");
require_once("../include/set_get_key.php");

// 权限检查已在 admin-header.php 中处理
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

$course_id = intval($_GET['id']);

// 获取课程信息
$sql = "SELECT * FROM `course` WHERE `id` = ?";
$result = pdo_query($sql, $course_id);

if (count($result) == 0) {
    echo "<script>alert('Course not found'); history.go(-1);</script>";
    exit();
}

$row = $result[0];
$view_title = $MSG_EDIT . " " . $MSG_COURSE;

// 处理表单提交
if (isset($_POST['do'])) {
    require_once("../include/check_post_key.php");

    $title = trim($_POST['title']);
    $subject_id = intval($_POST['subject_id']);
    $tags = trim($_POST['tags']);
    $lesson_count = intval($_POST['lesson_count']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $status = isset($_POST['status']) ? 1 : 0;
    $courseware_preview_url = trim($_POST['courseware_preview_url']);
    $lesson_plan_preview_url = trim($_POST['lesson_plan_preview_url']);
    $courseware_link = trim($_POST['courseware_link']);
    $courseware_code = trim($_POST['courseware_code']);
    $lesson_plan_link = trim($_POST['lesson_plan_link']);
    $lesson_plan_code = trim($_POST['lesson_plan_code']);
    $link_expire_date = trim($_POST['link_expire_date']);
    $sort_order = intval($_POST['sort_order']);

    // 校验
    if (empty($title)) {
        echo "<script>alert('$MSG_COURSE_TITLE$MSG_CANNOT_EMPTY'); history.go(-1);</script>";
        exit();
    }

    if ($subject_id <= 0) {
        echo "<script>alert('$MSG_COURSE_SUBJECT$MSG_CANNOT_EMPTY'); history.go(-1);</script>";
        exit();
    }

    if ($price < 0) {
        echo "<script>alert('$MSG_PRICE must be >= 0'); history.go(-1);</script>";
        exit();
    }

    // URL格式校验
    if (!empty($courseware_preview_url) && !filter_var($courseware_preview_url, FILTER_VALIDATE_URL)) {
        echo "<script>alert('$MSG_COURSEWARE preview URL is invalid'); history.go(-1);</script>";
        exit();
    }

    if (!empty($lesson_plan_preview_url) && !filter_var($lesson_plan_preview_url, FILTER_VALIDATE_URL)) {
        echo "<script>alert('$MSG_LESSON_PLAN preview URL is invalid'); history.go(-1);</script>";
        exit();
    }

    // 预览链接域名白名单校验（仅允许kdocs.cn）
    function validate_preview_domain($url) {
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) return false;
        $host = strtolower($parsed['host']);
        return $host === 'kdocs.cn' || substr($host, -9) === '.kdocs.cn';
    }

    if (!empty($courseware_preview_url) && !validate_preview_domain($courseware_preview_url)) {
        echo "<script>alert('$MSG_COURSEWARE preview URL only allows kdocs.cn domain'); history.go(-1);</script>";
        exit();
    }

    if (!empty($lesson_plan_preview_url) && !validate_preview_domain($lesson_plan_preview_url)) {
        echo "<script>alert('$MSG_LESSON_PLAN preview URL only allows kdocs.cn domain'); history.go(-1);</script>";
        exit();
    }

    if (!empty($courseware_link) && !filter_var($courseware_link, FILTER_VALIDATE_URL)) {
        echo "<script>alert('$MSG_COURSEWARE download link is invalid'); history.go(-1);</script>";
        exit();
    }

    if (!empty($lesson_plan_link) && !filter_var($lesson_plan_link, FILTER_VALIDATE_URL)) {
        echo "<script>alert('$MSG_LESSON_PLAN download link is invalid'); history.go(-1);</script>";
        exit();
    }

    $sql = "UPDATE `course` SET `title` = ?, `subject_id` = ?, `tags` = ?, `lesson_count` = ?, `description` = ?, `price` = ?, `status` = ?, `courseware_preview_url` = ?, `lesson_plan_preview_url` = ?, `courseware_link` = ?, `courseware_code` = ?, `lesson_plan_link` = ?, `lesson_plan_code` = ?, `link_expire_date` = ?, `sort_order` = ? WHERE `id` = ?";

    try {
        pdo_query($sql, $title, $subject_id, $tags, $lesson_count, $description, $price, $status, $courseware_preview_url, $lesson_plan_preview_url, $courseware_link, $courseware_code, $lesson_plan_link, $lesson_plan_code, $link_expire_date, $sort_order, $course_id);
        echo "<script>alert('$MSG_EDIT $MSG_SUCCESS'); window.location.href='course_list.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('$MSG_EDIT $MSG_FAILED: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "'); history.go(-1);</script>";
    }
    exit();
}

// 获取学科列表
$sql = "SELECT * FROM `course_subject` WHERE `status` = 1 ORDER BY `sort_order` ASC, `id` ASC";
$subject_list = pdo_query($sql);
?>

<title><?php echo $view_title ?></title>
<hr>
<center><h3><?php echo $view_title ?></h3></center>

<div class="padding">
    <form action="course_edit.php?id=<?php echo $course_id ?>" method="post" class="form-horizontal">
        <?php require_once("../include/set_post_key.php"); ?>

        <div class="form-group">
            <label class="col-sm-2 control-label">ID</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" value="<?php echo $course_id ?>" disabled>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSE_TITLE ?> <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="text" name="title" class="form-control" value="<?php echo htmlentities($row['title'], ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSE_SUBJECT ?> <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <select name="subject_id" class="form-control" required>
                    <option value="">-- <?php echo $MSG_COURSE_SUBJECT ?> --</option>
                    <?php foreach ($subject_list as $subject): ?>
                        <option value="<?php echo $subject['id'] ?>" <?php echo ($row['subject_id'] == $subject['id']) ? 'selected' : '' ?>>
                            <?php echo htmlentities($subject['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_TAGS ?></label>
            <div class="col-sm-6">
                <input type="text" name="tags" class="form-control" value="<?php echo htmlentities($row['tags'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Tag1, Tag2, Tag3" maxlength="255">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LESSON_COUNT ?></label>
            <div class="col-sm-6">
                <input type="number" name="lesson_count" class="form-control" value="<?php echo $row['lesson_count'] ?>" min="0">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_DESCRIPTION ?></label>
            <div class="col-sm-6">
                <textarea name="description" class="form-control" rows="4"><?php echo htmlentities($row['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_PRICE ?></label>
            <div class="col-sm-6">
                <input type="number" name="price" class="form-control" value="<?php echo $row['price'] ?>" min="0" step="0.01">
                <small class="text-muted"><?php echo "0" . " " . $MSG_FREE ?></small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_STATUS ?></label>
            <div class="col-sm-6">
                <label class="radio-inline">
                    <input type="radio" name="status" value="1" <?php echo $row['status'] == 1 ? 'checked' : '' ?>> <?php echo $MSG_AVAILABLE ?>
                </label>
                <label class="radio-inline">
                    <input type="radio" name="status" value="0" <?php echo $row['status'] == 0 ? 'checked' : '' ?>> <?php echo $MSG_RESERVED ?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSEWARE ?> <?php echo $MSG_PREVIEW_URL ?></label>
            <div class="col-sm-6">
                <input type="url" name="courseware_preview_url" class="form-control" value="<?php echo htmlentities($row['courseware_preview_url'], ENT_QUOTES, 'UTF-8') ?>" placeholder="金山文档URL" maxlength="500">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LESSON_PLAN ?> <?php echo $MSG_PREVIEW_URL ?></label>
            <div class="col-sm-6">
                <input type="url" name="lesson_plan_preview_url" class="form-control" value="<?php echo htmlentities($row['lesson_plan_preview_url'], ENT_QUOTES, 'UTF-8') ?>" placeholder="金山文档URL" maxlength="500">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSEWARE ?> <?php echo $MSG_DOWNLOAD_LINK ?></label>
            <div class="col-sm-6">
                <input type="url" name="courseware_link" class="form-control" value="<?php echo htmlentities($row['courseware_link'], ENT_QUOTES, 'UTF-8') ?>" placeholder="百度网盘URL" maxlength="500">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSEWARE ?> <?php echo $MSG_ACCESS_CODE ?></label>
            <div class="col-sm-6">
                <input type="text" name="courseware_code" class="form-control" value="<?php echo htmlentities($row['courseware_code'], ENT_QUOTES, 'UTF-8') ?>" maxlength="50">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LESSON_PLAN ?> <?php echo $MSG_DOWNLOAD_LINK ?></label>
            <div class="col-sm-6">
                <input type="url" name="lesson_plan_link" class="form-control" value="<?php echo htmlentities($row['lesson_plan_link'], ENT_QUOTES, 'UTF-8') ?>" placeholder="百度网盘URL" maxlength="500">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LESSON_PLAN ?> <?php echo $MSG_ACCESS_CODE ?></label>
            <div class="col-sm-6">
                <input type="text" name="lesson_plan_code" class="form-control" value="<?php echo htmlentities($row['lesson_plan_code'], ENT_QUOTES, 'UTF-8') ?>" maxlength="50">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LINK_EXPIRE_DATE ?></label>
            <div class="col-sm-6">
                <input type="date" name="link_expire_date" class="form-control" value="<?php echo $row['link_expire_date'] ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_SORT ?></label>
            <div class="col-sm-6">
                <input type="number" name="sort_order" class="form-control" value="<?php echo $row['sort_order'] ?>" min="0">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="do" value="true" class="btn btn-primary">
                    <i class="glyphicon glyphicon-ok"></i> <?php echo $MSG_SUBMIT ?>
                </button>
                <a href="list.php" class="btn btn-default">
                    <i class="glyphicon glyphicon-arrow-left"></i> <?php echo $MSG_BACK ?>
                </a>
            </div>
        </div>
    </form>
</div>

<?php require("admin-footer.php"); ?>
