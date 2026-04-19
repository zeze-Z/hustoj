<?php
require_once "include/db_info.inc.php";
require_once "include/my_func.inc.php";
require_once "include/school.php";

// 登录检查
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header("Location: loginpage.php");
    exit;
}

$eid = intval($_GET['eid'] ?? 0);
if (!$eid) exit("参数错误");

$exam = pdo_query("SELECT * FROM exam WHERE exam_id=?", $eid);
if (empty($exam)) exit("试卷不存在");
$exam = $exam[0];

// 权限检查
if (!canAccessData($exam['school_id'], $exam['is_public'] == 'Y')) {
    $view_errors = "您无权访问该试卷";
    require "template/" . $OJ_TEMPLATE . "/error.php";
    exit;
}

$now = date('Y-m-d H:i:s');
$is_started = $now >= $exam['start_time'];
$is_ended = $now >= $exam['end_time'];

// 获取试卷题目
$problems = pdo_query("SELECT ep.*, p.title, p.options, p.answer, p.problem_type, p.description
    FROM exam_problem ep JOIN problem p ON ep.problem_id=p.problem_id
    WHERE ep.exam_id=? ORDER BY ep.num", $eid);

$OJ_NAME = isset($OJ_NAME) ? $OJ_NAME : 'AI-OJ';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($exam['title']); ?></title>
    <link rel="stylesheet" href="template/syzoj/css/style.css?v=0.1">
    <link href="template/syzoj/css/semantic.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .container { max-width: 900px; margin: 20px auto; }
        .exam-header { background: #fff; border: 1px solid #d9d9d9; border-radius: 4px; padding: 20px; margin-bottom: 15px; text-align: center; }
        .exam-header h2 { margin: 0 0 10px; }
        .exam-meta { color: #666; font-size: 14px; }
        .prob-card { background: #fff; border: 1px solid #d9d9d9; border-radius: 4px; padding: 20px; margin-bottom: 15px; }
        .prob-title { font-size: 16px; font-weight: bold; margin-bottom: 12px; }
        .prob-content { line-height: 1.8; margin-bottom: 15px; white-space: pre-wrap; }
        .option-item { margin: 8px 0; line-height: 1.6; cursor: pointer; }
        .option-item:hover { background: #f5f5f5; padding: 4px 8px; border-radius: 4px; }
        .option-item input { margin-right: 8px; }
        .prob-footer { border-top: 1px solid #eee; padding-top: 10px; margin-top: 15px; color: #888; font-size: 13px; }
        .submit-row { text-align: center; padding: 20px; }
        .alert-box { padding: 15px 20px; border-radius: 4px; margin-bottom: 15px; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; }
    </style>
</head>
<body>
<?php include("template/syzoj/header.php"); ?>
<div class="container">
    <div class="exam-header">
        <h2><?php echo htmlspecialchars($exam['title']); ?></h2>
        <?php if ($exam['description']) { ?>
        <p style="color:#666; margin:10px 0;"><?php echo htmlspecialchars($exam['description']); ?></p>
        <?php } ?>
        <div class="exam-meta">
            <span>总分：<?php echo $exam['total_score']; ?>分</span>
            &nbsp;|&nbsp;
            <span>时长：<?php echo $exam['duration_min']; ?>分钟</span>
            &nbsp;|&nbsp;
            <span>题目：<?php echo count($problems); ?>题</span>
            &nbsp;|&nbsp;
            <span><?php echo $exam['start_time']; ?> ~ <?php echo $exam['end_time']; ?></span>
        </div>
        <?php if (!$is_started) { ?>
        <div class="alert-box alert-warning">
            ⏳ 考试尚未开始，请于 <?php echo $exam['start_time']; ?> 后进入
        </div>
        <?php } elseif ($is_ended) { ?>
        <div class="alert-box alert-warning">
            ⏰ 考试已结束（结束于 <?php echo $exam['end_time']; ?>）
        </div>
        <?php } else { ?>
        <div class="alert-box alert-warning" id="timer-box">
            ⏱️ 剩余时间：<strong id="timer">--:--</strong>
        </div>
        <?php } ?>
    </div>

    <?php if ($is_started && !$is_ended) { ?>
    <form id="exam-form" method="post" action="exam_do.php">
        <input type="hidden" name="eid" value="<?php echo $eid; ?>">

        <?php foreach ($problems as $idx => $p) {
            $options = json_decode($p['options'], true);
            $is_choice = $p['problem_type'] != 'programming';
        ?>
        <div class="prob-card">
            <div class="prob-title">
                <span><?php echo $p['num']; ?>.</span>
                <span>[<?php echo $p['problem_type'] == 'choice_single' ? '单选题' : ($p['problem_type'] == 'choice_multi' ? '多选题' : ($p['problem_type'] == 'judge' ? '判断题' : '编程题')); ?>]</span>
                <span style="float:right; font-size:13px; color:#888;"><?php echo $p['score']; ?>分</span>
            </div>
            <div class="prob-content"><?php echo bbcode_to_html($p['description']); ?></div>

            <?php if ($is_choice && !empty($options)) { ?>
            <div class="options-area">
                <?php foreach ($options as $opt) { ?>
                <label class="option-item">
                    <input type="<?php echo $p['problem_type'] == 'choice_multi' ? 'checkbox' : 'radio'; ?>"
                           name="answer[<?php echo $p['problem_id']; ?>]<?php echo $p['problem_type'] == 'choice_multi' ? '[]' : ''; ?>"
                           value="<?php echo $opt['label']; ?>">
                    <strong><?php echo $opt['label']; ?>.</strong> <?php echo $opt['content']; ?>
                </label>
                <?php } ?>
            </div>
            <?php } elseif ($p['problem_type'] == 'programming') { ?>
            <p style="color:#888; font-size:13px;">编程题请在下方提交代码</p>
            <textarea name="code[<?php echo $p['problem_id']; ?>]" style="width:100%; height:120px; font-family:monospace;" placeholder="在此输入代码..."></textarea>
            <?php } ?>

            <div class="prob-footer">题目ID: <?php echo $p['problem_id']; ?></div>
        </div>
        <?php } ?>

        <div class="submit-row">
            <button type="submit" class="ui primary button" style="padding:12px 40px; font-size:16px;"
                    onclick="return confirm('确认交卷？交卷后无法修改。')">交卷</button>
        </div>
    </form>
    <?php } ?>
</div>

<script src="template/syzoj/js/jquery.min.js"></script>
<script>
<?php if ($is_started && !$is_ended) { ?>
(function() {
    var startTime = new Date('<?php echo $exam['start_time']; ?>'.replace(/-/g, '/'));
    var endTime = new Date('<?php echo $exam['end_time']; ?>'.replace(/-/g, '/'));
    var durationMin = <?php echo intval($exam['duration_min']); ?>;
    var examEnd = new Date(startTime.getTime() + durationMin * 60000);
    var actualEnd = endTime < examEnd ? endTime : examEnd;

    function updateTimer() {
        var now = new Date();
        var diff = actualEnd - now;
        if (diff <= 0) {
            document.getElementById('timer').textContent = '00:00';
            document.getElementById('timer-box').className = 'alert-box alert-warning';
            document.getElementById('timer-box').innerHTML = '⏰ 考试时间到，正在自动交卷...';
            document.forms['exam-form'].submit();
            return;
        }
        var m = Math.floor(diff / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        document.getElementById('timer').textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');

        // 最后5分钟提醒
        if (m == 5 && s == 0) {
            alert('⏰ 考试还有5分钟结束，请尽快完成作答并交卷。');
        }
    }
    updateTimer();
    setInterval(updateTimer, 1000);
})();
<?php } ?>
</script>
</body>
</html>
