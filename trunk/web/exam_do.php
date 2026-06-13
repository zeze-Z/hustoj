<?php
require_once "include/db_info.inc.php";
require_once "include/my_func.inc.php";
$OJ_NAME = isset($OJ_NAME) ? $OJ_NAME : 'AI-OJ';

// 登录检查
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    header("Location: loginpage.php");
    exit;
}

$eid = intval($_POST['eid'] ?? 0);
if (!$eid) exit("参数错误");

$exam = pdo_query("SELECT * FROM exam WHERE exam_id=?", $eid);
if (empty($exam)) exit("试卷不存在");
$exam = $exam[0];

$now = date('Y-m-d H:i:s');
$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'] ?? 'guest_' . substr(md5(uniqid()), 0, 8);

// 记录参考；已交卷则不允许重复提交
pdo_query("INSERT IGNORE INTO exam_attend(exam_id, user_id) VALUES(?,?)", $eid, $user_id);
$attend = pdo_query("SELECT submitted FROM exam_attend WHERE exam_id=? AND user_id=?", $eid, $user_id);
if (!empty($attend) && $attend[0]['submitted'] == 'Y') {
    exit("已交卷，不能重复提交");
}

// 获取试卷题目
$problems = pdo_query("SELECT ep.*, p.title, p.answer, p.problem_type
    FROM exam_problem ep JOIN problem p ON ep.problem_id=p.problem_id
    WHERE ep.exam_id=? ORDER BY ep.num", $eid);

$total_score = 0;
$total_obtained = 0;
$details = [];
$user_answers = $_POST['answer'] ?? [];
$user_codes = $_POST['code'] ?? [];

foreach ($problems as $p) {
    $pid = $p['problem_id'];
    $correct = strtoupper(trim($p['answer']));
    $user_ans = '';

    if ($p['problem_type'] == 'programming') {
        // 编程题：固定使用 C++，保存源码并投递到 OJ 判题队列
        $code = trim($user_codes[$pid] ?? '');
        $user_ans = $code === '' ? '（未提交代码）' : '代码已提交，等待判题';
        $is_correct = 'N';
        $score = 0;

        if ($code !== '') {
            $lang = 1; // C++
            $ip = $_SERVER['REMOTE_ADDR'];
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $tmp_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip = htmlentities($tmp_ip[0], ENT_QUOTES, "UTF-8");
            }
            $len = strlen($code);

            if ($len > 65536) {
                exit("题目 {$p['num']} 代码过长，请控制在 64KB 以内");
            }

            $nick_row = pdo_query("SELECT nick FROM users WHERE user_id=?", $user_id);
            $nick = !empty($nick_row) ? $nick_row[0]['nick'] : $user_id;
            if (empty($nick)) $nick = $user_id;

            $sql = "INSERT INTO solution(problem_id,user_id,nick,in_date,language,ip,code_length,result,exam_id) VALUES(?,?,?,NOW(),?,?,?,0,?)";
            $insert_id = pdo_query($sql, $pid, $user_id, $nick, $lang, $ip, $len, $eid);

            pdo_query("INSERT INTO source_code_user(solution_id,source) VALUES(?,?)", $insert_id, $code);
            pdo_query("INSERT INTO source_code(solution_id,source) VALUES(?,?)", $insert_id, $code);
            pdo_query("UPDATE problem SET submit=submit+1 WHERE problem_id=?", $pid);

            if (isset($OJ_REDIS) && $OJ_REDIS) {
                try {
                    $redis = new Redis();
                    $redis->connect($OJ_REDISSERVER, $OJ_REDISPORT);
                    if (isset($OJ_REDISAUTH)) {
                        $redis->auth($OJ_REDISAUTH);
                    }
                    $redis->lpush($OJ_REDISQNAME, $insert_id);
                    $redis->close();
                } catch (Exception $e) {
                    // Redis 推送失败，静默降级，继续依赖 UDP 或被动判题
                }
            }
            if (isset($OJ_UDP) && $OJ_UDP) {
                trigger_judge($insert_id);
            }
        }

        // 提交新代码后重置成绩计算状态，下次查询自动重算
        pdo_query("UPDATE exam_attend SET score_calculated=0 WHERE exam_id=? AND user_id=?", $eid, $user_id);
    } else {
        // 选择/判断题：立即判分
        if (is_array($user_answers[$pid] ?? null)) {
            $ua = $user_answers[$pid];
            sort($ua);
            $user_ans = implode('', $ua);
        } else {
            $user_ans = strtoupper(trim($user_answers[$pid] ?? ''));
        }
        $is_correct = ($user_ans == $correct) ? 'Y' : 'N';
        $score = $is_correct == 'Y' ? intval($p['score']) : 0;
        // 记录答案
        $sql = "INSERT INTO exam_result(exam_id,user_id,problem_id,user_answer,is_correct,score)
                VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE user_answer=VALUES(user_answer),is_correct=VALUES(is_correct),score=VALUES(score),submitted_at=NOW()";
        pdo_query($sql, $eid, $user_id, $pid, $user_ans, $is_correct, $score);
    }

    $total_score += intval($p['score']);
    $total_obtained += $score;
    $details[] = [
        'num' => $p['num'],
        'pid' => $pid,
        'title' => $p['title'],
        'type' => $p['problem_type'],
        'correct' => $correct,
        'user_ans' => $user_ans,
        'is_correct' => $is_correct,
        'score' => $score,
        'max_score' => intval($p['score']),
    ];
}

// 更新参考状态
pdo_query("UPDATE exam_attend SET submitted='Y' WHERE exam_id=? AND user_id=?", $eid, $user_id);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>交卷结果 - <?php echo htmlspecialchars($exam['title']); ?></title>
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
    </div>

    <h3 style="color:#fff; padding:8px 15px; background:#2185d0; border-radius:4px;">答题详情</h3>
    <?php foreach ($details as $d) { ?>
    <div class="detail-card">
        <div class="prob-title">
            <?php echo $d['num']; ?>. <?php echo htmlspecialchars($d['title']); ?>
            <span style="float:right;"><?php echo $d['score']; ?> / <?php echo $d['max_score']; ?>分</span>
        </div>
        <div style="margin-top:5px;">
            <?php if ($d['type'] == 'programming') { ?>
                判题状态：<strong style="color:#f2711c"><?php echo htmlspecialchars($d['user_ans']); ?></strong>
                <span style="float:right;"><span class="ui orange label">判题中</span></span>
            <?php } else { ?>
                你的答案：<strong style="color:<?php echo $d['is_correct']=='Y'?'green':'red'; ?>"><?php echo htmlspecialchars($d['user_ans']) ?: '（未作答）'; ?></strong>
                &nbsp;&nbsp;正确答案：<strong style="color:green"><?php echo htmlspecialchars($d['correct']); ?></strong>
                <span style="float:right;">
                    <span class="ui <?php echo $d['is_correct']=='Y'?'green':'red'; ?> label"><?php echo $d['is_correct']=='Y'?'正确':'错误'; ?></span>
                </span>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <div style="text-align:center; margin-top:20px;">
        <a href="exam_view.php?eid=<?php echo $eid; ?>" class="ui button">返回试卷</a>
    </div>
</div>
</body>
</html>
