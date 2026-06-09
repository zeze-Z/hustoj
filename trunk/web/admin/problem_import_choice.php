<?php
require_once("../include/db_info.inc.php");
require_once("admin-header.php");

if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'contest_creator']) || isset($_SESSION[$OJ_NAME.'_problem_importer']))) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

require_once("../include/set_post_key.php");

$msg = "";
$success = 0;
$failed = 0;
$skipped = 0;
$errors = [];

// 下载模板
if (isset($_GET['action']) && $_GET['action'] == 'download') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="选择题导入模板.csv"');
    header('Cache-Control: max-age=0');
    // 输出表头
    $header = ["题目类型","题目内容","选项A","选项B","选项C","选项D","选项E","正确答案","分值","难度","竞赛来源","标签/分类","解析"];
    $fp = fopen('php://output', 'w');
    // 处理中文乱码
    fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($fp, $header);
    // 示例行
    $example = [
        ['单选','1+1等于多少？','1','2','3','4','','B','2','简单','','数学,一年级','基础计算题'],
        ['多选','以下哪些是质数？','1','2','3','4','','BC','3','中等','','数学',''],
        ['判断','2+2等于4','','','','','','A','1','简单','','判断对错题'],
        ['单选','以下哪个是排序算法？','冒泡排序','二分查找','深度优先','广度优先','','A','2','中等','GESP','算法','GESP真题'],
        ['判断','C语言中数组下标从1开始','','','','','','B','1','简单','CSP-J','编程基础','CSP-J真题']
    ];
    foreach ($example as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
    exit;
}

// 处理上传
if (isset($_POST['do_import'])) {
    if (!isset($_FILES['csv_file']['tmp_name'])) {
        $msg = "<div class='alert alert-danger'>请先选择要上传的文件</div>";
    } else {
        $file = $_FILES['csv_file']['tmp_name'];
        $ext = pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) != 'csv') {
            $msg = "<div class='alert alert-danger'>仅支持CSV格式文件</div>";
        } else {
            try {
                $fp = fopen($file, 'r');
                // 跳过BOM头
                $bom = fread($fp, 3);
                if ($bom != chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                    rewind($fp);
                }
                // 跳过表头（第一行）
                fgetcsv($fp);
                $row_num = 2;
                while (($rowData = fgetcsv($fp)) !== false) {
                    // 字段对应：A类型 B题目 C选项A D选项B E选项C F选项D G选项E H答案 I分值 J难度 K竞赛来源 L标签/分类 M解析
                    $type = trim($rowData[0] ?? '');
                    $title = trim($rowData[1] ?? '');
                    $optA = trim($rowData[2] ?? '');
                    $optB = trim($rowData[3] ?? '');
                    $optC = trim($rowData[4] ?? '');
                    $optD = trim($rowData[5] ?? '');
                    $optE = trim($rowData[6] ?? '');
                    $answer = strtoupper(trim($rowData[7] ?? ''));
                    $score = intval($rowData[8] ?? 1);
                    $difficulty = trim($rowData[9] ?? '简单');
                    $contest_source = trim($rowData[10] ?? '');
                    $tags = trim($rowData[11] ?? '');
                    $analysis = trim($rowData[12] ?? '');

                    // 基础校验
                    if (empty($type) || empty($title) || empty($answer) || $score <= 0) {
                        $errors[] = "第{$row_num}行：必填字段缺失";
                        $failed++;
                        $row_num++;
                        continue;
                    }

                    // 类型转换
                    $type_map = [
                        '单选' => 'choice_single',
                        '多选' => 'choice_multi',
                        '判断' => 'judge'
                    ];
                    if (!isset($type_map[$type])) {
                        $errors[] = "第{$row_num}行：题目类型错误，仅支持单选/多选/判断";
                        $failed++;
                        $row_num++;
                        continue;
                    }
                    $problem_type = $type_map[$type];

                    // 答案格式校验
                    $valid_chars = str_split('ABCDE');
                    $answer_chars = str_split($answer);
                    foreach ($answer_chars as $c) {
                        if (!in_array($c, $valid_chars)) {
                            $errors[] = "第{$row_num}行：答案格式错误，仅支持字母A-E";
                            $failed++;
                            $row_num++;
                            continue 2;
                        }
                    }
                    if ($problem_type == 'choice_single' || $problem_type == 'judge') {
                        if (strlen($answer) != 1) {
                            $errors[] = "第{$row_num}行：单选/判断题答案只能是单个字母";
                            $failed++;
                            $row_num++;
                            continue;
                        }
                    }

                    // 选项处理
                    $options = [];
                    if ($problem_type == 'judge') {
                        // 判断题自动生成选项
                        $options = [
                            ['label' => 'A', 'content' => '正确'],
                            ['label' => 'B', 'content' => '错误']
                        ];
                    } else {
                        if ($optA === '' || $optB === '') {
                            $errors[] = "第{$row_num}行：单选/多选题至少需要填写选项A和B";
                            $failed++;
                            $row_num++;
                            continue;
                        }
                        if ($optA !== '') $options[] = ['label' => 'A', 'content' => $optA];
                        if ($optB !== '') $options[] = ['label' => 'B', 'content' => $optB];
                        if ($optC !== '') $options[] = ['label' => 'C', 'content' => $optC];
                        if ($optD !== '') $options[] = ['label' => 'D', 'content' => $optD];
                        if ($optE !== '') $options[] = ['label' => 'E', 'content' => $optE];
                    }
                    $options_json = json_encode($options, JSON_UNESCAPED_UNICODE);

                    // 多选答案自动排序
                    if ($problem_type == 'choice_multi') {
                        sort($answer_chars);
                        $answer = implode('', $answer_chars);
                    }

                    // 难度转换
                    $diff_map = ['简单' => 1, '中等' => 2, '困难' => 3];
                    $difficulty = $diff_map[$difficulty] ?? 1;

                    // 拼接来源字段：竞赛来源 + 标签/分类
                    $source_parts = [];
                    if (!empty($contest_source)) $source_parts[] = $contest_source;
                    if (!empty($tags)) $source_parts[] = $tags;
                    $source = implode(' ', $source_parts);

                    // 重复校验
                    $sql = "SELECT problem_id FROM problem WHERE title = ? AND answer = ? AND problem_type = ?";
                    $res = pdo_query($sql, $title, $answer, $problem_type);
                    if (count($res) > 0) {
                        $skipped++;
                        $row_num++;
                        continue;
                    }

                    // 插入数据库
                    $sql = "INSERT INTO problem (title, description, input, output, spj, hint, problem_type, options, answer, score, level, source, analysis, time_limit, memory_limit, in_date, defunct)
                            VALUES (?, '', '', '', 0, '', ?, ?, ?, ?, ?, ?, ?, 1, 128, NOW(), 'N')";
                    $res = pdo_query($sql, $title, $problem_type, $options_json, $answer, $score, $difficulty, $source, $analysis);
                    if ($res) {
                        $success++;
                    } else {
                        $errors[] = "第{$row_num}行：数据库插入失败";
                        $failed++;
                    }
                    $row_num++;
                }
                fclose($fp);

                $msg = "<div class='alert alert-success'>导入完成！成功：{$success} 条，失败：{$failed} 条，跳过重复：{$skipped} 条</div>";
                if (!empty($errors)) {
                    $msg .= "<div class='alert alert-warning'><b>失败详情：</b><br>" . implode('<br>', $errors) . "</div>";
                }

            } catch (Exception $e) {
                $msg = "<div class='alert alert-danger'>解析文件失败：" . $e->getMessage() . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>选择题批量导入</title>
    <link rel="stylesheet" href="../template/syzoj/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h3>选择题批量导入</h3>
        <hr>
        <?php echo $msg; ?>

        <div class="card">
            <div class="card-body">
                <a href="?action=download" class="btn btn-primary mb-3">
                    <i class="glyphicon glyphicon-download"></i> 下载导入模板(CSV)
                </a>
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>选择CSV文件</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                    <button type="submit" name="do_import" class="btn btn-success">开始导入</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">使用说明</div>
            <div class="card-body">
                <ul>
                    <li>请先下载CSV模板，用Excel/WPS打开编辑，保存时选择「CSV UTF-8」格式</li>
                    <li>判断题不需要填写选项，系统自动生成「正确/错误」两个选项，答案填A=正确，B=错误</li>
                    <li>多选题答案不需要按顺序填写，系统会自动排序</li>
                    <li>「竞赛来源」可选填：蓝桥杯、CSP-J、CSP-S、GESP、NOIP、其他</li>
                    <li>「标签/分类」可填知识点标签，多个用空格或逗号分隔，如：数学 动态规划</li>
                    <li>竞赛来源和标签/分类会自动合并写入题目的标签/分类字段</li>
                    <li>重复题目（题目内容+答案+类型完全相同）会自动跳过</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
<?php require_once("admin-footer.php"); ?>
