<?php
require_once("admin-header.php");
$eid = intval($_GET['eid'] ?? 0);
if (!$eid) exit("参数错误");

$exam = pdo_query("SELECT * FROM exam WHERE exam_id=?", $eid);
if (empty($exam)) exit("试卷不存在");
$exam = $exam[0];

$problems = pdo_query("SELECT ep.*, p.title FROM exam_problem ep JOIN problem p ON ep.problem_id=p.problem_id WHERE ep.exam_id=? ORDER BY ep.num", $eid);
$total_score = 0;
foreach ($problems as $p) $total_score += intval($p['score']);

// 查所有参考学生
$attendees = pdo_query("SELECT a.*, u.nick FROM exam_attend a LEFT JOIN users u ON a.user_id=u.user_id WHERE a.exam_id=? ORDER BY a.submitted DESC, a.user_id", $eid);

$results = [];
foreach ($attendees as $att) {
    $er_rows = pdo_query("SELECT problem_id, is_correct, score FROM exam_result WHERE exam_id=? AND user_id=?", $eid, $att['user_id']);
    $er_map = [];
    $obt = 0;
    foreach ($er_rows as $er) {
        $er_map[$er['problem_id']] = $er;
        $obt += intval($er['score']);
    }
    $results[] = ['att' => $att, 'map' => $er_map, 'obt' => $obt];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>成绩管理 - <?php echo htmlspecialchars($exam['title']); ?></title>
    <link rel="stylesheet" href="template/syzoj/css/style.css?v=0.1">
    <link href="template/syzoj/css/semantic.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .container { max-width: 1400px; margin: 20px auto; }
        .block.header { background: #fff; border: 1px solid #d9d9d9; border-bottom: none; padding: 12px 20px; }
        .attached.segment { background: #fff; border: 1px solid #d9d9d9; padding: 15px; overflow-x:auto; }
        .score-cell { font-weight: bold; }
    </style>
</head>
<body>
<?php require_once("template/syzoj/menu.php"); ?>
<div class="container">
    <div class="block header">
        <h3><?php echo htmlspecialchars($exam['title']); ?> - 成绩管理</h3>
        <p style="color:#666; margin:5px 0 0;">
            总分：<?php echo $total_score; ?>分 | 题目：<?php echo count($problems); ?>题 |
            参考人数：<?php echo count($attendees); ?> |
            <a href="exam_edit.php?eid=<?php echo $eid; ?>">编辑试卷</a>
        </p>
    </div>
    <div class="attached segment">
        <table class="ui table">
            <thead>
                <tr>
                    <th>学号</th><th>昵称</th><th>提交状态</th>
                    <?php foreach ($problems as $p) {
                        echo "<th style='min-width:50px;'>{$p['num']}<br><small>({$p['score']}分)</small><br><small style='color:#888;'>{$p['title']}</small></th>";
                    } ?>
                    <th>总分<br><?php echo $total_score; ?>分</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($results as $r) {
                $att = $r['att'];
                $er_map = $r['map'];
                $obt = $r['obt'];
                echo "<tr>";
                echo "<td>{$att['user_id']}</td>";
                echo "<td>{$att['nick']}</td>";
                echo "<td>" . ($att['submitted'] == 'Y' ? "<span class='ui green label'>已交</span>" : "<span class='ui orange label'>未交</span>") . "</td>";
                foreach ($problems as $p) {
                    $er = $er_map[$p['problem_id']] ?? null;
                    if (!$er) {
                        echo "<td style='color:#ccc;'>-</td>";
                    } else {
                        $color = $er['is_correct'] == 'Y' ? 'green' : 'red';
                        echo "<td class='score-cell' style='color:$color;'>{$er['score']}</td>";
                    }
                }
                $pct = $total_score > 0 ? round($obt / $total_score * 100) : 0;
                $score_color = $pct >= 60 ? 'green' : ($pct >= 40 ? 'orange' : 'red');
                echo "<td class='score-cell' style='color:$score_color;'>$obt / $total_score<br><small>($pct%)</small></td>";
                echo "</tr>";
            }
            if (empty($results)) {
                echo "<tr><td colspan='100' style='text-align:center; color:#999;'>暂无参考记录</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
