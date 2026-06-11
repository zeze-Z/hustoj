<?php
require("admin-header.php");
require_once("../include/set_get_key.php");

// 权限检查已在 admin-header.php 中处理
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

$view_title = $MSG_ADD . " " . $MSG_COURSE;

// 处理表单提交
if (isset($_POST['do'])) {
    require_once("../include/check_post_key.php");

    $title = trim($_POST['title']);
    $subject_id = intval($_POST['subject_id']);
    $tags = trim($_POST['tags']);
    $lesson_count = intval($_POST['lesson_count']);
    $description = trim($_POST['description']);
    // 价格以积分为单位（1积分=1元），仅接受非负整数，拒绝小数
    $preview_price_raw = trim((string)$_POST['preview_price']);
    $source_price_raw = trim((string)$_POST['source_price']);
    if ($preview_price_raw === '') { $preview_price_raw = '0'; }
    if ($source_price_raw === '') { $source_price_raw = '0'; }
    if (!preg_match('/^\d+$/', $preview_price_raw)) {
        echo "<script>alert('完整预览版价格必须为非负整数（积分），不接受小数'); history.go(-1);</script>";
        exit();
    }
    if (!preg_match('/^\d+$/', $source_price_raw)) {
        echo "<script>alert('原文件版价格必须为非负整数（积分），不接受小数'); history.go(-1);</script>";
        exit();
    }
    $preview_price = intval($preview_price_raw);
    $source_price = intval($source_price_raw);
    $status = isset($_POST['status']) ? intval($_POST['status']) : 0;
    $courseware_preview_url = trim($_POST['courseware_preview_url']);
    $lesson_plan_preview_url = trim($_POST['lesson_plan_preview_url']);
    $courseware_full_preview_url = trim($_POST['courseware_full_preview_url']);
    $lesson_plan_full_preview_url = trim($_POST['lesson_plan_full_preview_url']);
    $courseware_link = trim($_POST['courseware_link']);
    $lesson_plan_link = trim($_POST['lesson_plan_link']);
    $link_expire_date = trim($_POST['link_expire_date']);
    if (empty($link_expire_date)) {
        $link_expire_date = null;
    }
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

    if ($preview_price < 0) {
        echo "<script>alert('完整预览版价格必须 >= 0'); history.go(-1);</script>";
        exit();
    }

    if ($source_price < 0) {
        echo "<script>alert('原文件版价格必须 >= 0'); history.go(-1);</script>";
        exit();
    }

    // 强制校验：原文件版价格必须 >= 完整预览版价格
    if ($source_price < $preview_price) {
        echo "<script>alert('原文件版价格必须大于等于完整预览版价格'); history.go(-1);</script>";
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

    if (!empty($courseware_full_preview_url) && !filter_var($courseware_full_preview_url, FILTER_VALIDATE_URL)) {
        echo "<script>alert('$MSG_COURSEWARE $MSG_FULL_PREVIEW_URL is invalid'); history.go(-1);</script>";
        exit();
    }

    if (!empty($courseware_full_preview_url) && !validate_preview_domain($courseware_full_preview_url)) {
        echo "<script>alert('$MSG_COURSEWARE $MSG_FULL_PREVIEW_URL only allows kdocs.cn domain'); history.go(-1);</script>";
        exit();
    }

    if (!empty($lesson_plan_full_preview_url) && !filter_var($lesson_plan_full_preview_url, FILTER_VALIDATE_URL)) {
        echo "<script>alert('$MSG_LESSON_PLAN $MSG_FULL_PREVIEW_URL is invalid'); history.go(-1);</script>";
        exit();
    }

    if (!empty($lesson_plan_full_preview_url) && !validate_preview_domain($lesson_plan_full_preview_url)) {
        echo "<script>alert('$MSG_LESSON_PLAN $MSG_FULL_PREVIEW_URL only allows kdocs.cn domain'); history.go(-1);</script>";
        exit();
    }

    if (!empty($courseware_link) && !filter_var($courseware_link, FILTER_VALIDATE_URL)) {
        echo "<script>alert('$MSG_COURSEWARE 原文件链接 is invalid'); history.go(-1);</script>";
        exit();
    }

    if (!empty($lesson_plan_link) && !filter_var($lesson_plan_link, FILTER_VALIDATE_URL)) {
        echo "<script>alert('$MSG_LESSON_PLAN 原文件链接 is invalid'); history.go(-1);</script>";
        exit();
    }

    $sql = "INSERT INTO `course` (`title`, `subject_id`, `tags`, `lesson_count`, `description`, `preview_price`, `source_price`, `status`, `courseware_preview_url`, `lesson_plan_preview_url`, `courseware_full_preview_url`, `lesson_plan_full_preview_url`, `courseware_link`, `lesson_plan_link`, `link_expire_date`, `sort_order`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    try {
        pdo_query($sql, $title, $subject_id, $tags, $lesson_count, $description, $preview_price, $source_price, $status, $courseware_preview_url, $lesson_plan_preview_url, $courseware_full_preview_url, $lesson_plan_full_preview_url, $courseware_link, $lesson_plan_link, $link_expire_date, $sort_order);
        echo "<script>alert('$MSG_ADD $MSG_SUCCESS'); window.location.href='course_list.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('$MSG_ADD $MSG_FAILED: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "'); history.go(-1);</script>";
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
    <form action="course_add.php" method="post" class="form-horizontal">
        <?php require_once("../include/set_post_key.php"); ?>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSE_TITLE ?> <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="text" name="title" class="form-control" placeholder="<?php echo $MSG_COURSE_TITLE ?>" maxlength="255" required>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSE_SUBJECT ?> <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <select name="subject_id" class="form-control" required>
                    <option value="">-- <?php echo $MSG_COURSE_SUBJECT ?> --</option>
                    <?php foreach ($subject_list as $subject): ?>
                        <option value="<?php echo $subject['id'] ?>">
                            <?php echo htmlentities($subject['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_TAGS ?></label>
            <div class="col-sm-6">
                <input type="text" name="tags" class="form-control" placeholder="Tag1, Tag2, Tag3" maxlength="255">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LESSON_COUNT ?></label>
            <div class="col-sm-6">
                <input type="number" name="lesson_count" class="form-control" value="0" min="0">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_DESCRIPTION ?></label>
            <div class="col-sm-6">
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">完整预览版价格（积分，1积分=1元）</label>
            <div class="col-sm-6">
                <input type="number" name="preview_price" class="form-control" value="0" min="0" step="1">
                <small class="text-muted">0表示免费；大于0表示需消耗对应积分，1积分=1元</small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">原文件版价格（积分，1积分=1元）</label>
            <div class="col-sm-6">
                <input type="number" name="source_price" class="form-control" value="0" min="0" step="1">
                <small class="text-muted">0表示免费；大于0表示需消耗对应积分，1积分=1元</small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_STATUS ?></label>
            <div class="col-sm-6">
                <label class="radio-inline">
                    <input type="radio" name="status" value="1" checked> <?php echo $MSG_AVAILABLE ?>
                </label>
                <label class="radio-inline">
                    <input type="radio" name="status" value="0"> <?php echo $MSG_RESERVED ?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSEWARE ?> <?php echo $MSG_PREVIEW_URL ?></label>
            <div class="col-sm-6">
                <input type="url" name="courseware_preview_url" class="form-control" placeholder="金山文档URL" maxlength="500">
                <small class="text-muted">未购买用户可见的免费预览链接</small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LESSON_PLAN ?> <?php echo $MSG_PREVIEW_URL ?></label>
            <div class="col-sm-6">
                <input type="url" name="lesson_plan_preview_url" class="form-control" placeholder="金山文档URL" maxlength="500">
                <small class="text-muted">未购买用户可见的免费预览链接</small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSEWARE ?> <?php echo $MSG_FULL_PREVIEW_URL ?></label>
            <div class="col-sm-6">
                <input type="url" name="courseware_full_preview_url" class="form-control" placeholder="金山文档完整版URL" maxlength="500">
                <small class="text-muted">购买预览版后用户可见的完整版课件预览链接</small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LESSON_PLAN ?> <?php echo $MSG_FULL_PREVIEW_URL ?></label>
            <div class="col-sm-6">
                <input type="url" name="lesson_plan_full_preview_url" class="form-control" placeholder="金山文档完整版URL" maxlength="500">
                <small class="text-muted">购买预览版后用户可见的完整版教案预览链接</small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_COURSEWARE ?> 原文件链接</label>
            <div class="col-sm-6">
                <input type="url" name="courseware_link" class="form-control" placeholder="百度网盘/下载链接" maxlength="500">
                <small class="text-muted">购买原文件权限后用户可见的下载链接</small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LESSON_PLAN ?> 原文件链接</label>
            <div class="col-sm-6">
                <input type="url" name="lesson_plan_link" class="form-control" placeholder="百度网盘/下载链接" maxlength="500">
                <small class="text-muted">购买原文件权限后用户可见的下载链接</small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_LINK_EXPIRE_DATE ?></label>
            <div class="col-sm-6">
                <input type="date" name="link_expire_date" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $MSG_SORT ?></label>
            <div class="col-sm-6">
                <input type="number" name="sort_order" class="form-control" value="0" min="0">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="do" value="true" class="btn btn-primary">
                    <i class="glyphicon glyphicon-ok"></i> <?php echo $MSG_SUBMIT ?>
                </button>
                <a href="course_list.php" class="btn btn-default">
                    <i class="glyphicon glyphicon-arrow-left"></i> <?php echo $MSG_BACK ?>
                </a>
            </div>
        </div>
    </form>
</div>

<?php require("admin-footer.php"); ?>
