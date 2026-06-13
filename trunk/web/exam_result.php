<?php
require_once "include/db_info.inc.php";
require_once "include/my_func.inc.php";

// 登录检查
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    $redirect = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'index.php';
    header("Location: loginpage.php?redirect=" . urlencode($redirect));
    exit;
}

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
$eid = intval($_GET['eid'] ?? 0);

if (!$eid) {
    $view_errors = "参数错误";
    require "template/" . $OJ_TEMPLATE . "/error.php";
    exit;
}

// 检查试卷是否存在
$exam = pdo_query("SELECT * FROM exam WHERE exam_id=?", $eid);
if (empty($exam)) {
    $view_errors = "试卷不存在";
    require "template/" . $OJ_TEMPLATE . "/error.php";
    exit;
}
$exam = $exam[0];

// 检查是否是本人成绩，加行锁避免并发计算冲突
$attend = pdo_query("SELECT * FROM exam_attend WHERE exam_id=? AND user_id=? FOR UPDATE", $eid, $user_id);
if (empty($attend)) {
    $view_errors = "你没有参加该考试，无权查看成绩";
    require "template/" . $OJ_TEMPLATE . "/error.php";
    exit;
}
$attend = $attend[0];
$total_obtained = intval($attend['total_score']);
$is_calculated = intval($attend['score_calculated']);

// 未计算则动态计算总分
if ($is_calculated == 0) {
    // 标记为计算中，避免并发重复计算
    pdo_query("UPDATE exam_attend SET score_calculated=2 WHERE exam_id=? AND user_id=?", $eid, $user_id);

    // 获取成绩详情
    $problems = pdo_query("SELECT ep.*, ep.score AS max_score, p.title, p.answer, p.problem_type,
        er.user_answer, er.is_correct, er.score AS obtained_score
        FROM exam_problem ep
        JOIN problem p ON ep.problem_id=p.problem_id
        LEFT JOIN exam_result er ON er.exam_id=ep.exam_id AND er.problem_id=ep.problem_id AND er.user_id=?
        WHERE ep.exam_id=? ORDER BY ep.num", $user_id, $eid);

    $total_score = 0;
    $total_obtained = 0;
    $has_pending_judge = false; // 是否有还在判分的编程题

    foreach ($problems as &$p) {
        $total_score += intval($p['max_score']);
        if ($p['problem_type'] == 'programming') {
            // 编程题查solution表最高得分
            $sol = pdo_query("SELECT result, pass_rate FROM solution
                WHERE exam_id=? AND user_id=? AND problem_id=?
                ORDER BY CASE WHEN result=4 THEN 1 ELSE 0 END DESC, pass_rate DESC, solution_id DESC LIMIT 1", $eid, $user_id, $p['problem_id']);

            if (!empty($sol)) {
                $res = $sol[0];
                if (intval($res['result']) < 4) { // 0等待判题/1正在判题
                    $has_pending_judge = true;
                    $p['score'] = 0;
                    $p['is_correct'] = 'N';
                    $p['judge_status'] = '判题中';
                } else if (intval($res['result']) == 4) { // 正确，满分
                    $p['score'] = intval($p['max_score']);
                    $p['is_correct'] = 'Y';
                    $p['judge_status'] = '正确';
                    $total_obtained += intval($p['score']);
                } else { // 错误，按通过率算分
                    $pass_rate = exam_pass_rate($res['pass_rate']);
                    $get_score = intval($p['max_score'] * $pass_rate);
                    $p['score'] = $get_score;
                    $p['is_correct'] = $get_score > 0 ? 'P' : 'N'; // P部分正确
                    $p['judge_status'] = $get_score > 0 ? '部分正确' : '错误';
                    $total_obtained += $get_score;
                }
            } else {
                $p['score'] = 0;
                $p['is_correct'] = 'N';
                $p['judge_status'] = '未提交';
            }
        } else {
            // 客观题直接读成绩
            $p['score'] = intval($p['obtained_score'] ?? 0);
            $total_obtained += $p['score'];
        }
    }

    // 无待判分的题目则缓存计算结果，下次直接读取
    if (!$has_pending_judge) {
        pdo_query("UPDATE exam_attend SET total_score=?, score_calculated=1 WHERE exam_id=? AND user_id=?",
            $total_obtained, $eid, $user_id);
    } else {
        // 还有待判分的，重置为未计算，下次查询继续刷新
        pdo_query("UPDATE exam_attend SET score_calculated=0 WHERE exam_id=? AND user_id=?", $eid, $user_id);
    }
} else {
    // 已计算，直接读取题目详情
    $problems = pdo_query("SELECT ep.*, ep.score AS max_score, p.title, p.answer, p.problem_type,
        er.user_answer, er.is_correct, er.score AS obtained_score
        FROM exam_problem ep
        JOIN problem p ON ep.problem_id=p.problem_id
        LEFT JOIN exam_result er ON er.exam_id=ep.exam_id AND er.problem_id=ep.problem_id AND er.user_id=?
        WHERE ep.exam_id=? ORDER BY ep.num", $user_id, $eid);

    $total_score = 0;
    foreach ($problems as &$p) {
        $total_score += intval($p['max_score']);
        if ($p['problem_type'] == 'programming') {
            // 已缓存总分的情况下，也显示编程题状态和本题得分
            $sol = pdo_query("SELECT result, pass_rate FROM solution
                WHERE exam_id=? AND user_id=? AND problem_id=?
                ORDER BY CASE WHEN result=4 THEN 1 ELSE 0 END DESC, pass_rate DESC, solution_id DESC LIMIT 1", $eid, $user_id, $p['problem_id']);
            if (!empty($sol)) {
                $res = $sol[0];
                if (intval($res['result']) == 4) {
                    $p['score'] = intval($p['max_score']);
                    $p['is_correct'] = 'Y';
                    $p['judge_status'] = '正确';
                } else if (intval($res['result']) > 4) {
                    $rate = exam_pass_rate($res['pass_rate']);
                    $p['score'] = intval($p['max_score'] * $rate);
                    $p['is_correct'] = $p['score'] > 0 ? 'P' : 'N';
                    $p['judge_status'] = $p['score'] > 0 ? '部分正确' : '错误';
                } else {
                    $p['score'] = 0;
                    $p['is_correct'] = 'N';
                    $p['judge_status'] = '判题中';
                }
            } else {
                $p['score'] = 0;
                $p['is_correct'] = 'N';
                $p['judge_status'] = '未提交';
            }
        } else {
            $p['score'] = intval($p['obtained_score'] ?? 0);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>考试成绩 - <?php echo htmlspecialchars($exam['title']); ?></title>
    <link rel="stylesheet" href="template/syzoj/css/style.css?v=0.1">
    <link href="template/syzoj/css/semantic.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .container { max-width: 900px; margin: 20px auto; }
        .score-box { background: #fff; border: 1px solid #d9d9d9; border-radius: 4px; padding: 30px; text-align: center; margin-bottom: 15px; }
        .big-score { font-size: 48px; font-weight: bold; color: #2185d0; }
        .detail-card { background: #fff; border: 1px solid #d9d9d9; border-radius: 4px; padding: 15px 20px; margin-bottom: 10px; }
        .detail-card .prob-title { font-weight: bold; margin-bottom: 8px; }
    </style>
</head>
<body>
<?php include("template/syzoj/header.php"); ?>
<div class="container">
    <div class="score-box">
        <h2><?php echo htmlspecialchars($exam['title']); ?></h2>
        <h3>你的得分</h3>
        <div class="big-score"><?php echo $total_obtained; ?> / <?php echo $total_score; ?></div>
        <p style="color:#888; margin-top:10px;">正确率 <?php echo $total_score > 0 ? round($total_obtained / $total_score * 100) : 0; ?>%</p>
        <p style="color:#888;">考试时长：<?php echo $exam['duration_min']; ?>分钟</p>
    </div>

    <h3 style="color:#fff; padding:8px 15px; background:#2185d0; border-radius:4px;">答题详情</h3>
    <?php foreach ($problems as $d) { ?>
    <div class="detail-card">
        <div class="prob-title">
            <?php echo $d['num']; ?>. <?php echo htmlspecialchars($d['title']); ?>
            <span style="float:right;"><?php echo intval($d['score'] ?? 0); ?> / <?php echo intval($d['max_score']); ?>分</span>
        </div>
        <div style="margin-top:5px;">
            <?php if ($d['problem_type'] == 'programming'): ?>
                判题状态：<strong style="color:<?php
                    if ($d['is_correct'] == 'Y') echo 'green';
                    else if ($d['is_correct'] == 'P') echo '#f2711c';
                    else echo 'red';
                ?>"><?php echo $d['judge_status']; ?></strong>
            <?php else: ?>
                你的答案：<strong style="color:<?php
                    if ($d['is_correct'] == 'Y') echo 'green';
                    else if ($d['is_correct'] == 'P') echo '#f2711c';
                    else echo 'red';
                ?>"><?php echo htmlspecialchars($d['user_answer'] ?? '（未作答）'); ?></strong>
                &nbsp;&nbsp;正确答案：<strong style="color:green"><?php echo htmlspecialchars($d['answer']); ?></strong>
            <?php endif; ?>
            <span style="float:right;">
                <span class="ui
                <?php
                    if ($d['is_correct'] == 'Y') echo 'green';
                    else if ($d['is_correct'] == 'P') echo 'orange';
                    else echo 'red';
                ?> label">
                <?php
                    if ($d['is_correct'] == 'Y') echo '正确';
                    else if ($d['is_correct'] == 'P') echo '部分正确';
                    else echo '错误';
                ?></span>
            </span>
        </div>
    </div>
    <?php } ?>

    <div style="text-align:center; margin-top:20px;">
        <a href="exam_view.php?eid=<?php echo $eid; ?>" class="ui button">返回试卷</a>
        <a href="index.php" class="ui primary button">返回首页</a>
    </div>
</div>
</body>
</html>
