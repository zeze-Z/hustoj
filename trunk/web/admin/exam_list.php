<?php
require_once("admin-header.php");
$allow_check = true;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>试卷管理</title>
    <link rel="stylesheet" href="../template/syzoj/css/style.css?v=0.1">
    <link href="../template/syzoj/css/semantic.min.css" rel="stylesheet">
</head>
<body>
<?php include("../template/syzoj/menu.php"); ?>
<div class="container" style="margin-top: 20px;">
    <div class="ui basic segment">
        <div class="ui top attached block header">
            <h3>试卷管理</h3>
        </div>
        <div class="ui bottom attached segment">
            <a href="exam_add.php" class="ui primary button"><i class="plus icon"></i>创建试卷</a>
            <table class="ui table" style="margin-top:15px;">
                <thead>
                    <tr>
                        <th>ID</th><th>标题</th><th>总分</th><th>时长</th><th>时间范围</th><th>题目</th><th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT * FROM exam WHERE defunct='N' ORDER BY exam_id DESC";
                $exams = pdo_query($sql);
                foreach ($exams as $e) {
                    $now = date('Y-m-d H:i:s');
                    if ($e['start_time'] > $now) {
                        $status = "<span class='ui blue label'>未开始</span>";
                    } elseif ($e['end_time'] < $now) {
                        $status = "<span class='ui grey label'>已结束</span>";
                    } else {
                        $status = "<span class='ui green label'>进行中</span>";
                    }
                    $problem_count = pdo_query("SELECT COUNT(*) FROM exam_problem WHERE exam_id=?", $e['exam_id'])[0][0];
                    echo "<tr>
                        <td>{$e['exam_id']}</td>
                        <td><a href='exam_add.php?eid={$e['exam_id']}'>{$e['title']}</a></td>
                        <td>{$e['total_score']}分</td>
                        <td>{$e['duration_min']}分钟</td>
                        <td>{$e['start_time']}<br>{$e['end_time']}</td>
                        <td>$status<br><small>{$problem_count}题</small></td>
                        <td>
                            <a href='exam_add.php?eid={$e['exam_id']}' class='ui small button'>编辑</a>
                            <a href='../exam_view.php?eid={$e['exam_id']}' target='_blank' class='ui small button'>预览</a>
                            <a href='exam_result.php?eid={$e['exam_id']}' class='ui small button'>成绩</a>
                            <a href='exam_del.php?eid={$e['exam_id']}' onclick=\"return confirm('确认删除？')\" class='ui small red button'>删除</a>
                        </td>
                    </tr>";
                }
                if (empty($exams)) {
                    echo "<tr><td colspan='7' style='text-align:center;color:#999;'>暂无试卷，<a href='exam_add.php'>去创建</a></td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
