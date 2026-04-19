<?php
require_once "include/db_info.inc.php";
require_once "include/my_func.inc.php";
require_once "include/school.php";

// 登录检查
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header("Location: loginpage.php");
    exit;
}

$now = date('Y-m-d H:i:s');
$school_filter = getSchoolSQLFilter('', 'school_id', 'is_public');
$sql = "SELECT * FROM exam WHERE defunct='N' AND start_time <= ? AND end_time >= ? $school_filter ORDER BY exam_id DESC";
$exams = pdo_query($sql, $now, $now);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>考试列表</title>
    <link rel="stylesheet" href="template/syzoj/css/style.css?v=0.1">
    <link href="template/syzoj/css/semantic.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .container { max-width: 900px; margin: 20px auto; }
        .exam-card { background: #fff; border: 1px solid #d9d9d9; border-radius: 8px; padding: 20px; margin-bottom: 15px; }
        .exam-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .exam-meta { color: #666; font-size: 14px; margin-bottom: 15px; }
        .exam-actions { text-align: right; }
    </style>
</head>
<body>
<?php include("template/syzoj/header.php"); ?>
<div class="container">
    <h2 style="margin-bottom:20px;">可参加的考试</h2>

    <?php foreach ($exams as $e) {
        $problem_count = pdo_query("SELECT COUNT(*) FROM exam_problem WHERE exam_id=?", $e['exam_id'])[0][0];
    ?>
    <div class="exam-card">
        <div class="exam-title"><?php echo htmlspecialchars($e['title']); ?></div>
        <div class="exam-meta">
            <span>总分：<?php echo $e['total_score']; ?>分</span>
            <span style="margin:0 10px;">|</span>
            <span>时长：<?php echo $e['duration_min']; ?>分钟</span>
            <span style="margin:0 10px;">|</span>
            <span>题目：<?php echo $problem_count; ?>题</span>
            <br>
            <span>考试时间：<?php echo $e['start_time']; ?> ~ <?php echo $e['end_time']; ?></span>
        </div>
        <p><?php echo htmlspecialchars($e['description']); ?></p>
        <div class="exam-actions">
            <a href="exam_view.php?eid=<?php echo $e['exam_id']; ?>" class="ui primary button">进入考试</a>
        </div>
    </div>
    <?php } ?>

    <?php if (empty($exams)) { ?>
    <div style="text-align:center; padding:50px; color:#999;">
        当前没有可参加的考试
    </div>
    <?php } ?>
</div>
</body>
</html>