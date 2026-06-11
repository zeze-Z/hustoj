<?php
require_once("../include/db_info.inc.php");
require_once("admin-header.php");
require_once("../include/my_func.inc.php");

if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'problem_editor']))) {
  echo "<a href='../loginpage.php'>Please Login First!</a>";
  exit(1);
}

// 加载学校相关函数
if (file_exists("../include/school.php")) {
    require_once("../include/school.php");
    $school_list = getSchoolList(true);
}
?>
  <html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Edit Problem</title>
</head>
<hr>
  
  <?php
echo "<center><h3>"."Edit-".$MSG_PROBLEM."</h3></center>";
include_once("kindeditor.php") ;
?>


<body leftmargin="30" >
  <div id="main" class="container">
    <?php
    if (isset($_GET['id'])) {
      ;//require_once("../include/check_get_key.php");
        $pid=intval($_GET['id']);
        if(! (isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'."p".$pid])) ){
                echo "No Privilege.";
                exit(0);
        }
    ?>

    <form method=POST action=problem_edit.php>
      <?php
      $sql = "SELECT * FROM `problem` WHERE `problem_id`=?";
      $result = pdo_query($sql,intval($_GET['id']));
      $row = $result[0];
      ?>

      <input type=hidden name=problem_id value='<?php echo $row['problem_id']?>'>
      <p align=left>
        <center>
          <h3>
          <?php echo $row['problem_id']?>: <input class="input input-xxlarge" style='width:90%' type=text name=title value='<?php echo htmlentities($row['title'],ENT_QUOTES,"UTF-8")?>'>
          </h3>
        </center>
      </p>
        <p align=left>
          <h4>题型</h4>
          <select name="problem_type" id="problem_type" class="form-control" onchange="toggleProblemFields()">
            <option value="programming" <?php if($row['problem_type'] == 'programming') echo 'selected'?>>编程题</option>
            <option value="choice_single" <?php if($row['problem_type'] == 'choice_single') echo 'selected'?>>单选题</option>
            <option value="choice_multi" <?php if($row['problem_type'] == 'choice_multi') echo 'selected'?>>多选题</option>
            <option value="judge" <?php if($row['problem_type'] == 'judge') echo 'selected'?>>判断题</option>
          </select>
          <br><br>
        </p>
        
        <div id="programming_fields" <?php if($row['problem_type'] != 'programming') echo 'style="display:none;"'?>>
          <p align=left>
            <?php echo $MSG_Time_Limit?>
            <input class="input input-mini" type=number min="0.001" max="300" step="0.001" name=time_limit size=20 value="<?php echo $row['time_limit']?>"> sec
            <?php echo $MSG_Memory_Limit?>
            <input class="input input-mini" type=number min="1" max="1024" step="1" name=memory_limit size=20 value="<?php echo $row['memory_limit']?>"> MiB
          </p>
        </div>
        
        <!-- 选择题选项区域 -->
        <div id="choice_fields" <?php if($row['problem_type'] == 'programming') echo 'style="display:none;"'?>>
          <h4>选项</h4>
          <div id="options_container">
            <?php
            $options = json_decode($row['options'], true);
            if (empty($options)) {
              // 默认4个选项
              $options = array(
                array('label' => 'A', 'content' => ''),
                array('label' => 'B', 'content' => ''),
                array('label' => 'C', 'content' => ''),
                array('label' => 'D', 'content' => '')
              );
            }
            foreach ($options as $index => $option):
              $readonly = ($row['problem_type'] == 'judge') ? 'readonly' : '';
              $delete_style = ($row['problem_type'] == 'judge' || $index < 2) ? 'style="display:none;"' : '';
            ?>
            <div class="option_item">
              <label><?php echo $option['label'] ?>: </label>
              <input type="text" name="option_content[]" class="form-control" style="width:90%; display:inline-block;" 
                     value="<?php echo htmlentities($option['content'], ENT_QUOTES, 'UTF-8') ?>" <?php echo $readonly ?>>
              <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)" <?php echo $delete_style ?>>删除</button>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if ($row['problem_type'] != 'judge'): ?>
          <button type="button" class="btn btn-success btn-sm" onclick="addOption()">添加选项</button>
          <?php endif; ?>
          <br><br>
          
          <h4>正确答案</h4>
          <div id="answer_container">
            <?php
            $answer = $row['answer'];
            if ($row['problem_type'] == 'choice_single' || $row['problem_type'] == 'judge') {
              foreach ($options as $index => $option) {
                $checked = ($answer == $option['label']) ? 'checked' : '';
                echo "
                <label style=\"margin-right: 20px;\">
                  <input type=\"radio\" name=\"answer\" value=\"{$option['label']}\" $checked> {$option['label']}
                </label>
                ";
              }
            } else if ($row['problem_type'] == 'choice_multi') {
              $answer_arr = str_split($answer);
              foreach ($options as $index => $option) {
                $checked = in_array($option['label'], $answer_arr) ? 'checked' : '';
                echo "
                <label style=\"margin-right: 20px;\">
                  <input type=\"checkbox\" name=\"answer[]\" value=\"{$option['label']}\" $checked> {$option['label']}
                </label>
                ";
              }
            }
            ?>
          </div>
          <br><br>
          
          <h4>分值</h4>
          <input type="number" name="score" class="form-control" min="1" max="100" value="<?php echo $row['score'] ?>">
          <br><br>
          
          <h4>答案解析</h4>
          <textarea name="analysis" class="kindeditor" rows=5 cols=80><?php echo htmlentities($row['analysis'], ENT_QUOTES, 'UTF-8') ?></textarea>
          <br><br>
        </div>
      <p align=left>
        <?php echo "<h4>".$MSG_Description."</h4>"?>
        <textarea class="kindeditor" rows=13 name=description cols=80><?php echo htmlentities($row['description'],ENT_QUOTES,"UTF-8")?></textarea><br>
      </p>

      <p align=left>
        <?php echo "<h4>".$MSG_Input."</h4>"?>
        <textarea class="kindeditor" rows=13 name=input cols=80><?php echo htmlentities($row['input'],ENT_QUOTES,"UTF-8")?></textarea><br>
      </p>

      <p align=left>
        <?php echo "<h4>".$MSG_Output."</h4>"?>
        <textarea  class="kindeditor" rows=13 name=output cols=80><?php echo htmlentities($row['output'],ENT_QUOTES,"UTF-8")?></textarea><br>
      </p>

      <p align=left>
        <?php echo "<h4>".$MSG_Sample_Input."</h4>"?>
        <textarea  class="input input-large" style="width:100%;" rows=13 name=sample_input><?php echo htmlentities($row['sample_input'],ENT_QUOTES,"UTF-8")?></textarea><br><br>
      </p>

      <p align=left>
        <?php echo "<h4>".$MSG_Sample_Output."</h4>"?>
        <textarea  class="input input-large" style="width:100%;" rows=13 name=sample_output><?php echo htmlentities($row['sample_output'],ENT_QUOTES,"UTF-8")?></textarea><br><br>
      </p>

      <p align=left>
        <?php echo "<h4>".$MSG_HINT."</h4>"?>
        <textarea class="kindeditor" rows=13 name=hint cols=80><?php echo htmlentities($row['hint'],ENT_QUOTES,"UTF-8")?></textarea><br>
      </p>

      <p>
        <?php echo "<h4>".$MSG_SPJ."</h4>"?>
        <?php echo "(".$MSG_HELP_SPJ.")"?><br>
        <input type=radio name=spj value='0' <?php echo $row['spj']=="0"?"checked":""?> title='Normal Judger'><?php echo $MSG_NJ?><br>
        <input type=radio name=spj value='1' <?php echo $row['spj']=="1"?"checked":""?> title='Special Judger'><?php echo $MSG_SPJ?><br>
        <input type=radio name=spj value='2' <?php echo $row['spj']=="2"?"checked":""?> title='Raw Text Judger' ><?php echo $MSG_RTJ?><br>
      </p>

      <p align=left>
        <?php echo "<h4>竞赛来源</h4>"?>
        <select name="contest_source" class="form-control" style="width:100%;">
          <option value="">无</option>
          <option value="蓝桥杯" <?php if(str_contains($row['source'], "蓝桥杯")) echo "selected"?>>蓝桥杯</option>
          <option value="CSP-J" <?php if(str_contains($row['source'], "CSP-J")) echo "selected"?>>CSP-J</option>
          <option value="CSP-S" <?php if(str_contains($row['source'], "CSP-S")) echo "selected"?>>CSP-S</option>
          <option value="GESP" <?php if(str_contains($row['source'], "GESP")) echo "selected"?>>GESP</option>
          <option value="NOIP" <?php if(str_contains($row['source'], "NOIP")) echo "selected"?>>NOIP</option>
          <option value="其他" <?php if(str_contains($row['source'], "其他")) echo "selected"?>>其他</option>
        </select>
        <br><br>
        <?php echo "<h4>".$MSG_SOURCE."</h4>"?>
        <textarea name=source style="width:100%;" rows=1 placeholder="多个分类/来源用逗号或空格分隔，比如：蓝桥杯2023 数学 基础题"><?php echo htmlentities($row['source'],ENT_QUOTES,"UTF-8")?></textarea><br>

        <?php echo "<h4>难度</h4>"?>
        <select name="level" class="form-control">
          <option value="1" <?php if($row['level']==1) echo "selected"?>>1 - 入门</option>
          <option value="2" <?php if($row['level']==2) echo "selected"?>>2 - 入门</option>
          <option value="3" <?php if($row['level']==3) echo "selected"?>>3 - 基础</option>
          <option value="4" <?php if($row['level']==4) echo "selected"?>>4 - 基础</option>
          <option value="5" <?php if($row['level']==5) echo "selected"?>>5 - 进阶</option>
          <option value="6" <?php if($row['level']==6) echo "selected"?>>6 - 进阶</option>
          <option value="7" <?php if($row['level']==7) echo "selected"?>>7 - 竞赛</option>
          <option value="8" <?php if($row['level']==8) echo "selected"?>>8 - 竞赛</option>
        </select>
        <br><br>

        <?php echo "<h4>".$MSG_CONTEST."</h4>"?>
        <select name=contest_id>
          <?php
          // 查询题目当前关联的竞赛
          $sql="SELECT contest_id FROM contest_problem WHERE problem_id = ?";
          $result_cp=pdo_query($sql, [$pid]);
          $current_contest_id = !empty($result_cp) ? $result_cp[0]['contest_id'] : null;

          $sql="SELECT `contest_id`,`title` FROM `contest` order by `contest_id` DESC";
          $result_contest=pdo_query($sql);
          echo "<option value='0'>无</option>";
          if (count($result_contest)>0) {
            foreach ($result_contest as $row_contest) {
              $selected = ($current_contest_id == $row_contest['contest_id']) ? "selected" : "";
              echo "<option value='{$row_contest['contest_id']}' $selected>{$row_contest['contest_id']} {$row_contest['title']}</option>";
            }
          }?>
        </select>
        <br><br>

        <?php echo "<h4>".$MSG_REMOTE_OJ."</h4>"?>
        <input name=remote_oj value='<?php echo htmlentities((string)$row['remote_oj'],ENT_QUOTES,"UTF-8")?>' placeholder='<?php echo $MSG_HELP_LOCAL_EMPTY ?>' >
        <input name=remote_id value='<?php echo htmlentities((string)$row['remote_id'],ENT_QUOTES,"UTF-8")?>' placeholder='<?php echo $MSG_HELP_LOCAL_EMPTY ?>' ><br>
      </p>

      <?php if(isset($school_list) && is_array($school_list)): ?>
      <p align=left>
        <?php echo "<h4>".$MSG_SCHOOL."</h4>"?>
        <select name="school_id" class="form-control" style="width:100%;">
          <option value="">选择学校</option>
          <?php foreach ($school_list as $school): ?>
            <option value="<?php echo $school['id'] ?>" <?php echo ($row['school_id'] == $school['id']) ? 'selected' : ''; ?>>
              <?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </p>
      <p align=left>
        <label>
          <input type="checkbox" name="is_public" value="1" <?php echo (!empty($row['is_public'])) ? 'checked' : ''; ?>> 公开题目（允许其他学校访问）
        </label>
      </p>
      <?php endif; ?>

      <div align=center>
        <?php require_once("../include/set_post_key.php");?>
        <input class="btn btn-success" type=submit value='<?php echo $MSG_SAVE?>' name=submit>
      </div>
    </form>

  </div>

<script src="<?php echo $OJ_CDN_URL."/template/bs3/"?>marked.min.js"></script>
<script>
  function str_split(str) {
    return str.split('');
  }
  
  function toggleProblemFields() {
    var problemType = document.getElementById('problem_type').value;
    var programmingFields = document.getElementById('programming_fields');
    var choiceFields = document.getElementById('choice_fields');
    var answerContainer = document.getElementById('answer_container');
    var addOptionBtn = document.querySelector('#choice_fields .btn-success');
    
    if (problemType == 'programming') {
      programmingFields.style.display = 'block';
      choiceFields.style.display = 'none';
    } else {
      programmingFields.style.display = 'none';
      choiceFields.style.display = 'block';
      
      // 生成答案选择项
      answerContainer.innerHTML = '';
      var options = document.querySelectorAll('#options_container .option_item input');
      var currentAnswer = '<?php echo $row['answer'] ?>';
      var currentAnswerArr = str_split(currentAnswer);
      
      if (problemType == 'choice_single' || problemType == 'judge') {
        // 单选或判断题用radio
        if (problemType == 'judge') {
          // 判断题只有对/错两个选项
          document.getElementById('options_container').innerHTML = `
            <div class="option_item">
              <label>A: </label>
              <input type="text" name="option_content[]" class="form-control" style="width:90%; display:inline-block;" value="对" readonly>
            </div>
            <div class="option_item">
              <label>B: </label>
              <input type="text" name="option_content[]" class="form-control" style="width:90%; display:inline-block;" value="错" readonly>
            </div>
          `;
          options = document.querySelectorAll('#options_container .option_item input');
          if (addOptionBtn) addOptionBtn.style.display = 'none';
        } else {
          if (addOptionBtn) addOptionBtn.style.display = 'block';
        }
        
        for (var i = 0; i < options.length; i++) {
          var label = String.fromCharCode(65 + i); // A, B, C, D...
          var checked = (currentAnswer == label) ? 'checked' : '';
          answerContainer.innerHTML += `
            <label style="margin-right: 20px;">
              <input type="radio" name="answer" value="${label}" ${checked}> ${label}
            </label>
          `;
        }
      } else if (problemType == 'choice_multi') {
        // 多选题用checkbox
        if (addOptionBtn) addOptionBtn.style.display = 'block';
        for (var i = 0; i < options.length; i++) {
          var label = String.fromCharCode(65 + i); // A, B, C, D...
          var checked = currentAnswerArr.includes(label) ? 'checked' : '';
          answerContainer.innerHTML += `
            <label style="margin-right: 20px;">
              <input type="checkbox" name="answer[]" value="${label}" ${checked}> ${label}
            </label>
          `;
        }
      }
    }
  }
  
  function addOption() {
    var container = document.getElementById('options_container');
    var optionCount = container.querySelectorAll('.option_item').length;
    var label = String.fromCharCode(65 + optionCount); // A, B, C, D...
    
    var newOption = document.createElement('div');
    newOption.className = 'option_item';
    newOption.innerHTML = `
      <label>${label}: </label>
      <input type="text" name="option_content[]" class="form-control" style="width:90%; display:inline-block;" placeholder="选项${label}内容">
      <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">删除</button>
    `;
    
    container.appendChild(newOption);
    
    // 更新答案选项
    toggleProblemFields();
  }
  
  function removeOption(btn) {
    var container = document.getElementById('options_container');
    var optionItems = container.querySelectorAll('.option_item');
    
    if (optionItems.length <= 2) {
      alert('至少需要2个选项');
      return;
    }
    
    btn.parentElement.remove();
    
    // 重新排序标签
    optionItems = container.querySelectorAll('.option_item');
    for (var i = 0; i < optionItems.length; i++) {
      var label = String.fromCharCode(65 + i);
      optionItems[i].querySelector('label').textContent = label + ': ';
    }
    
    // 更新答案选项
    toggleProblemFields();
  }
  
  function transform(){
        let height=document.body.clientHeight;
        let width=parseInt(document.body.clientWidth*0.6);
        let width2=parseInt(document.body.clientWidth*0.4);
	if(width<500) width2=300;
	let submitURL="../problem.php?id=<?php echo $pid ?>";
        console.log(width);
        let main=$("#main");
        let problem=main.html();
                main.removeClass("container");
                main.css("width",width2);
                main.css("margin-left","10px");
                main.parent().append("<div id='preview' class='container' style='opacity:0.95;position:fixed;z-index:1000;top:49px;right:0px;width:"+width+"px'></div>");
                $("#preview").html("<iframe id='previewFrame' src='"+submitURL+"&spa' width='100%' height='"+height+"px' style='border:none;'></iframe>");
        $("#submit").remove();
        setTimeout('hide()',1500);	
	$("input").keyup(sync);
	$("textarea").keyup(sync);
  }
  function hide(){
	let preview=$("#previewFrame").contents();
	preview.find(".ui.buttons").hide();
	preview.find("span.ui.label").eq(2).hide();
	preview.find("span.ui.label").eq(3).hide();
	preview.find("span.ui.label").eq(4).hide();
	preview.find("span.ui.label").eq(5).hide();
	preview.find("#show_tag_div").parent().hide();
	sync();
//	preview.find("h1:first").parent().parent().hide();
  }
  function sync(){
	console.log("sync...");
	let preview=$("#previewFrame").contents();
	let title=$("input[name=title]").val();
	preview.find("h1:first").html(title);
	let time=$("input[name=time_limit]").val();
	preview.find("span.ui.label").eq(0).html("<?php echo $MSG_Time_Limit ?>："+time);
	let memory=$("input[name=memory_limit]").val();
	preview.find("span.ui.label").eq(1).html("<?php echo $MSG_Memory_Limit ?>："+memory);
	
	let description=$("textarea").eq(1).val();
	preview.find("#description").html(description);
	preview.find("#description .md").each(function(){
		if($("#previewFrame")[0] != undefined) $("#previewFrame")[0].contentWindow.MathJax.typeset();
		$(this).html(marked.parse($(this).html()));
	});
  
	let input=$("textarea").eq(3).val();
	preview.find("#input").html(input);
	preview.find("#input .md").each(function(){
		if($("#previewFrame")[0] != undefined) $("#previewFrame")[0].contentWindow.MathJax.typeset();
		$(this).html(marked.parse($(this).html()));
	});
	let output=$("textarea").eq(5).val();
	preview.find("#output").html(output);
	preview.find("#output .md").each(function(){
		if($("#previewFrame")[0] != undefined) $("#previewFrame")[0].contentWindow.MathJax.typeset();
		$(this).html(marked.parse($(this).html()));
	});

	let sinput=$("textarea").eq(6).val();
	preview.find("#sinput").html(sinput);
	let soutput=$("textarea").eq(7).val();
	preview.find("#soutput").html(soutput);
	let hint=$("textarea").eq(9).val();
	preview.find("#hint").html(hint);
	preview.find("#hint .md").each(function(){
		if($("#previewFrame")[0] != undefined) $("#previewFrame")[0].contentWindow.MathJax.typeset();
		$(this).html(marked.parse($(this).html()));
	});
	if($("#previewFrame")[0] != undefined) $("#previewFrame")[0].contentWindow.MathJax.typeset();
  }
  $(document).ready(function(){
  	 <?php if (!(isset($OJ_OLD_FASHINED) && $OJ_OLD_FASHINED ) ) echo " transform();" ?>
  
  }); 
</script>
    <?php
    }
    else {
      require_once("../include/check_post_key.php");
      $id = intval($_POST['problem_id']);
      if(! (isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'."p".$id])) ){
                echo "No Privilege.";
                exit(0)    ;
      }
      if (!(isset($_SESSION[$OJ_NAME.'_'."p$id"]) || isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'problem_editor']) )) exit();

      $title = $_POST['title'];
      $title = str_replace(",", "&#44;", $title);

      $time_limit = $_POST['time_limit'];

      $memory_limit = $_POST['memory_limit'];

      $description = $_POST['description'];
     // $description = str_replace("<p>", "", $description);
     // $description = str_replace("</p>", "<br />", $description);
    //  $description = str_replace(",", "&#44;", $description);

      $input = $_POST['input'];
     // $input = str_replace("<p>", "", $input);
     // $input = str_replace("</p>", "<br />", $input);
    //  $input = str_replace(",", "&#44;", $input);

      $output = $_POST['output'];
     // $output = str_replace("<p>", "", $output);
     // $output = str_replace("</p>", "<br />", $output);
     // $output = str_replace(",", "&#44;", $output);

      $sample_input = $_POST['sample_input'];
      $sample_output = $_POST['sample_output'];
      //if ($sample_input=="") $sample_input="\n";
      //if ($sample_output=="") $sample_output="\n";

      $hint = $_POST['hint'];
     // $hint = str_replace("<p>", "", $hint);
    //  $hint = str_replace("</p>", "<br />", $hint);
    //  $hint = str_replace(",", "&#44;", $hint);

      $source = $_POST['source'];
      $remote_oj= $_POST['remote_oj'];
      $remote_id = $_POST['remote_id'];
      $spj = $_POST['spj'];
      
      // 添加竞赛来源到source字段
      if(isset($_POST['contest_source']) && $_POST['contest_source']!=""){
          $contest_source = $_POST['contest_source'];
          if($source != "") $source .= " ";
          $source .= $contest_source;
      }
      
      // 获取难度
      $level = intval($_POST['level']);
      if($level < 1 || $level >8) $level = 1;

      if (false) {
        $title = stripslashes($title);
        $time_limit = stripslashes($time_limit);
        $memory_limit = stripslashes($memory_limit);
        $description = stripslashes($description);
        $input = stripslashes($input);
        $output = stripslashes($output);
        $sample_input = stripslashes($sample_input);
        $sample_output = stripslashes($sample_output);
        //$test_input = stripslashes($test_input);
        //$test_output = stripslashes($test_output);
        $hint = stripslashes($hint);
        $source = stripslashes($source);
        $spj = stripslashes($spj);
      }

      $title = ($title);
      $description = ($description);
      $input = ($input);
      $output = ($output);
      $hint = ($hint);
      $basedir = $OJ_DATA."/$id";

      echo "Problem Updated!<br>";

      if ($sample_input && file_exists($basedir."/sample.in")) {
        //mkdir($basedir);
        $fp = @fopen($basedir."/sample.in","w");
        if($fp){
            fputs($fp,preg_replace("(\r\n)","\n",$sample_input));
            fclose($fp);
        }

        $fp = @fopen($basedir."/sample.out","w");
        if($fp){
            fputs($fp,preg_replace("(\r\n)","\n",$sample_output));
            fclose($fp);
        }
      }

      $spj = intval($spj);

      // 处理选择题相关字段
      $problem_type = isset($_POST['problem_type']) ? $_POST['problem_type'] : 'programming';
      $options = array();
      $answer = '';
      $analysis = '';
      $score = 0;

      if ($problem_type != 'programming') {
          // 处理选项
          if (isset($_POST['option_content']) && is_array($_POST['option_content'])) {
              foreach ($_POST['option_content'] as $index => $content) {
                  if (!empty($content)) {
                      $label = chr(65 + $index);
                      $options[] = array(
                          'label' => $label,
                          'content' => $content
                      );
                  }
              }
          }
          
          // 处理答案
          if ($problem_type == 'choice_single' || $problem_type == 'judge') {
              $answer = isset($_POST['answer']) ? $_POST['answer'] : '';
          } else if ($problem_type == 'choice_multi') {
              if (isset($_POST['answer']) && is_array($_POST['answer'])) {
                  sort($_POST['answer']);
                  $answer = implode('', $_POST['answer']);
              }
          }
          
          // 处理解析和分值
          $analysis = isset($_POST['analysis']) ? $_POST['analysis'] : '';
          $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
      }

      // 转换选项为JSON
      $options_json = !empty($options) ? json_encode($options, JSON_UNESCAPED_UNICODE) : null;

      // 获取学校和公开设置
      $school_id = isset($_POST['school_id']) && $_POST['school_id'] !== '' ? intval($_POST['school_id']) : null;
      $is_public = isset($_POST['is_public']) ? 1 : 0;

      // 标准化来源：支持逗号/空格分隔，自动去重、统一成英文逗号
      function normalize_source($str) {
          if (empty($str)) return '';
          // 先把中文逗号、空格都替换成英文逗号
          $str = str_replace(['，', ' '], ',', $str);
          // 多个连续逗号合并成一个
          $str = preg_replace('/,+/', ',', $str);
          // 去掉首尾逗号
          $str = trim($str, ',');
          // 去重
          $arr = array_unique(explode(',', $str));
          return implode(',', $arr);
      }

      $source = isset($_POST['source']) ? normalize_source(trim($_POST['source'])) : '';

      $sql = "UPDATE `problem` SET `title`=?,`time_limit`=?,`memory_limit`=?, `description`=?,`input`=?,`output`=?,`sample_input`=?,`sample_output`=?,`hint`=?,`source`=?,`spj`=?,remote_oj=?,remote_id=?,`in_date`=NOW(),`school_id`=?,`is_public`=?,`level`=?,`problem_type`=?,`options`=?,`answer`=?,`analysis`=?,`score`=? WHERE `problem_id`=?";

      //echo "SQL: " . $sql . "<br>";
      //echo "Params: remote_oj=[$remote_oj] (" . strlen($remote_oj) . "), remote_id=[$remote_id] (" . strlen($remote_id) . ")<br>";
      @pdo_query($sql,$title,$time_limit,$memory_limit,$description,$input,$output,$sample_input,$sample_output,$hint,$source,$spj,$remote_oj,$remote_id,$school_id,$is_public,$level,$problem_type,$options_json,$answer,$analysis,$score,$id);

      // 处理竞赛关联
      if (isset($_POST['contest_id'])) {
          $new_contest_id = intval($_POST['contest_id']);

          // 先查询题目当前关联的竞赛
          $sql = "SELECT contest_id FROM contest_problem WHERE problem_id = ?";
          $result = pdo_query($sql, $id);
          $current_contest_id = !empty($result) ? $result[0]['contest_id'] : null;

          if ($new_contest_id > 0 && $new_contest_id != $current_contest_id) {
              // 如果有新的竞赛关联，先删除旧关联，再添加新关联
              if ($current_contest_id) {
                  $sql = "DELETE FROM contest_problem WHERE problem_id = ? AND contest_id = ?";
                  pdo_query($sql, $id, $current_contest_id);
              }

              // 获取新竞赛的题目数量
              $sql = "SELECT count(*) FROM contest_problem WHERE contest_id = ?";
              $result = pdo_query($sql, $new_contest_id);
              $num = $result[0][0];

              // 添加到新竞赛
              $sql = "INSERT INTO contest_problem (problem_id, contest_id, num) VALUES (?, ?, ?)";
              pdo_query($sql, $id, $new_contest_id, $num);
          } elseif (empty($new_contest_id) && $current_contest_id) {
              // 如果选择了"无"，删除当前关联
              $sql = "DELETE FROM contest_problem WHERE problem_id = ? AND contest_id = ?";
              pdo_query($sql, $id, $current_contest_id);
          }
      }
  
      echo "Edit OK!<br>";
      echo "<a href='../problem.php?id=$id'>See The Problem!</a>";
    }
    ?>
</body>
</html>

