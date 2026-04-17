<?php
require_once("../include/common.php");
$OJ_NAME = isset($OJ_NAME) ? $OJ_NAME : 'AI-OJ';
if (!isset($_SESSION[$OJ_NAME . '_' . 'administrator']) && !isset($_SESSION[$OJ_NAME . '_' . 'contest_creator'])) {
    header("Location: ../loginpage.php");
    exit;
}
$eid = intval($_GET['eid'] ?? 0);
$exam = null;
if ($eid > 0) {
    $rows = pdo_query("SELECT * FROM exam WHERE exam_id=?", $eid);
    if (!empty($rows)) $exam = $rows[0];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $exam ? "编辑试卷" : "创建试卷"; ?></title>
    <link rel="stylesheet" href="../template/syzoj/css/style.css?v=0.1">
    <link href="../template/syzoj/css/semantic.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .container { max-width: 900px; margin: 20px auto; }
        .block.header { background: #fff; border: 1px solid #d9d9d9; border-bottom: none; padding: 12px 20px; }
        .attached.segment { border: 1px solid #d9d9d9; background: #fff; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .form-group textarea { height: 80px; }
        #selected-problems .prob-item { background: #e8f4ff; border: 1px solid #b8d4ff; border-radius: 4px; padding: 8px 12px; margin: 5px 0; display: flex; align-items: center; gap: 10px; }
        #selected-problems .prob-item .score { width: 60px; text-align: center; }
    </style>
</head>
<body>
<?php include("../template/syzoj/header.php"); ?>
<div class="container">
    <div class="block header"><h3><?php echo $exam ? "编辑试卷" : "创建试卷"; ?></h3></div>
    <div class="attached segment">
        <form id="exam-form">
            <input type="hidden" name="eid" value="<?php echo $eid; ?>">
            <div class="form-group">
                <label>试卷标题 *</label>
                <input name="title" value="<?php echo htmlspecialchars($exam['title'] ?? ''); ?>" required placeholder="如：2026年春季C++期末测验">
            </div>
            <div class="form-group">
                <label>说明</label>
                <textarea name="description" placeholder="考试说明"><?php echo htmlspecialchars($exam['description'] ?? ''); ?></textarea>
            </div>
            <div style="display:flex; gap:15px;">
                <div class="form-group" style="flex:1;">
                    <label>总分</label>
                    <input name="total_score" type="number" value="<?php echo $exam['total_score'] ?? 100; ?>" min="1" max="1000">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>时长（分钟）</label>
                    <input name="duration_min" type="number" value="<?php echo $exam['duration_min'] ?? 60; ?>" min="1" max="600">
                </div>
            </div>
            <div style="display:flex; gap:15px;">
                <div class="form-group" style="flex:1;">
                    <label>开始时间</label>
                    <input name="start_time" type="datetime-local" value="<?php echo $exam ? str_replace(' ', 'T', substr($exam['start_time'],0,16)) : date('Y-m-d\TH:i'); ?>">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>结束时间</label>
                    <input name="end_time" type="datetime-local" value="<?php echo $exam ? str_replace(' ', 'T', substr($exam['end_time'],0,16)) : date('Y-m-d\TH:i', strtotime('+7 days')); ?>">
                </div>
            </div>
            <div class="form-group">
                <label>允许学校（留空则所有学校可见）</label>
                <select name="school_id">
                    <option value="">— 全部学校 —</option>
                    <?php
                    $schools = pdo_query("SELECT school_id, school_name FROM school ORDER BY school_id");
                    foreach ($schools as $s) {
                        $sel = ($exam && $exam['school_id'] == $s['school_id']) ? 'selected' : '';
                        echo "<option value='{$s['school_id']}' $sel>{$s['school_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>是否公开</label>
                <select name="is_public">
                    <option value="Y" <?php echo ($exam && $exam['is_public']=='N') ? '' : 'selected'; ?>>是（其他学校可见）</option>
                    <option value="N" <?php echo ($exam && $exam['is_public']=='N') ? 'selected' : ''; ?>>否（仅本校可见）</option>
                </select>
            </div>
            <button type="submit" class="ui primary button">保存基本信息</button>
            <?php if ($eid > 0) { ?>
            <a href="exam_list.php" class="ui button">返回列表</a>
            <?php } ?>
        </form>
    </div>

    <?php if ($eid > 0) { ?>
    <div class="block header" style="margin-top:20px;"><h3>添加题目</h3></div>
    <div class="attached segment">
        <div class="form-group">
            <input id="search-kw" placeholder="搜索题目标题或来源（如CSP/GESP）" style="width:250px;">
            <select id="filter-type" style="width:120px; display:inline;">
                <option value="">全部题型</option>
                <option value="programming">编程题</option>
                <option value="choice_single">单选题</option>
                <option value="choice_multi">多选题</option>
                <option value="judge">判断题</option>
            </select>
            <select id="filter-level" style="width:80px; display:inline;">
                <option value="">难度</option>
                <?php for ($i=1;$i<=8;$i++) echo "<option value='$i'>L$i</option>"; ?>
            </select>
            <button onclick="searchProblems()" class="ui button">搜索</button>
        </div>
        <div id="search-results" style="max-height:300px; overflow-y:auto; margin-top:10px;"></div>
    </div>

    <div class="block header" style="margin-top:20px;"><h3>已选题目（<span id="prob-count">0</span>题）</h3></div>
    <div class="attached segment" id="selected-problems">
        <?php
        $ep_rows = pdo_query("SELECT ep.*, p.title, p.problem_type FROM exam_problem ep JOIN problem p ON ep.problem_id=p.problem_id WHERE ep.exam_id=? ORDER BY ep.num", $eid);
        foreach ($ep_rows as $idx => $ep) {
            $type_map = ['programming'=>'编程','choice_single'=>'单选','choice_multi'=>'多选','judge'=>'判断'];
            $ptype = $type_map[$ep['problem_type']] ?? '编程';
            echo "<div class='prob-item' id='epi_{$ep['ep_id']}'>
                <span style='flex:1;'>{$ep['num']}. [{$ptype}] {$ep['title']} (PID:{$ep['problem_id']})</span>
                <input class='score' type='number' value='{$ep['score']}' min='1' onchange='updateScore({$ep['ep_id']},this.value)'>
                <button onclick='removeProb({$ep['ep_id']})' class='ui red mini button'>移除</button>
            </div>";
        }
        ?>
    </div>
    <?php } ?>
</div>
<script src="../template/syzoj/js/jquery.min.js"></script>
<script>
const eid = <?php echo $eid; ?>;

$('#exam-form').on('submit', function(ev) {
    ev.preventDefault();
    var fd = new FormData(this);
    var obj = {};
    fd.forEach((v,k) => obj[k] = v);
    // 转换datetime-local
    if (obj.start_time) obj.start_time = obj.start_time.replace('T',' ') + ':00';
    if (obj.end_time) obj.end_time = obj.end_time.replace('T',' ') + ':00';
    fetch('exam_api.php?action=save_exam', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(obj)
    }).then(r => r.json()).then(d => {
        if (d.ok) {
            alert('保存成功！');
            if (!eid) location.href = 'exam_add.php?eid=' + d.eid;
            else location.reload();
        } else alert('失败：' + d.error);
    });
});

function searchProblems() {
    var kw = $('#search-kw').val();
    var type = $('#filter-type').val();
    var level = $('#filter-level').val();
    $('#search-results').html('<i>搜索中...</i>');
    fetch('exam_api.php?action=search_problems&kw=' + encodeURIComponent(kw) + '&type=' + type + '&level=' + level)
        .then(r => r.json())
        .then(data => {
            if (!data.length) { $('#search-results').html('<i>未找到题目</i>'); return; }
            var typeMap = {'programming':'编程','choice_single':'单选','choice_multi':'多选','judge':'判断'};
            var html = '<table class="ui table"><thead><tr><th></th><th>ID</th><th>标题</th><th>题型</th><th>难度</th><th>来源</th></tr></thead><tbody>';
            data.forEach(p => {
                html += '<tr><td><input type="checkbox" value="'+p.problem_id+'" class="prob-cb"></td>'+
                    '<td>'+p.problem_id+'</td><td>'+p.title+'</td>'+
                    '<td>'+(typeMap[p.problem_type]||'编程')+'</td><td>'+(p.level||'-')+'</td><td><small>'+(p.source||'')+'</small></td></tr>';
            });
            html += '</tbody></table><button onclick="addSelected()" class="ui primary button" style="margin-top:10px;">添加到试卷</button>';
            $('#search-results').html(html);
        });
}

function addSelected() {
    var ids = [];
    $('.prob-cb:checked').each(function() { ids.push(this.value); });
    if (!ids.length) { alert('请先勾选题目'); return; }
    fetch('exam_api.php?action=add_problems', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({exam_id: eid, problem_ids: ids})
    }).then(r => r.json()).then(d => {
        if (d.ok) { alert('添加成功'); location.reload(); }
        else alert(d.error);
    });
}

function removeProb(ep_id) {
    if (!confirm('确认移除？')) return;
    fetch('exam_api.php?action=remove_problem', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ep_id: ep_id})
    }).then(r => r.json()).then(d => {
        if (d.ok) { document.getElementById('epi_'+ep_id).remove(); updateCount(); }
    });
}

function updateScore(ep_id, score) {
    fetch('exam_api.php?action=update_score', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ep_id: ep_id, score: score})
    });
}

function updateCount() {
    document.getElementById('prob-count').textContent = document.querySelectorAll('#selected-problems .prob-item').length;
}
updateCount();
</script>
</body>
</html>
