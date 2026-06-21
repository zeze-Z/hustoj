<?php 
  require_once("../include/db_info.inc.php");
  require_once("admin-header.php");
  if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'contest_creator']) || isset($_SESSION[$OJ_NAME.'_'.'problem_editor']))) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
  }
  
  // 加载学校相关函数
  if (file_exists("../include/school.php")) {
      require_once("../include/school.php");
      $school_list = getSchoolList(true);
      $current_user_school_id = getCurrentUserSchoolId();
  }
?>
	  
<html>
<head>
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Cache-Control" content="no-cache">
  <meta http-equiv="Content-Language" content="zh-cn">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Problem Add</title>
</head>	  
	  <?php
  echo "<center><h3>".$MSG_PROBLEM."-".$MSG_ADD."</h3></center>";
  include_once("kindeditor.php") ;
  $source=pdo_query("select source from problem order by problem_id desc limit 1"); //默认续用最后一次的分类标签
  if(!empty($source)&&isset($source[0]))$source=$source[0][0];else $source="";
?>

<hr>
<body leftmargin="30" >
  <div id="main" class="padding">
    <form method=POST action=problem_add.php>
      <input type=hidden name=problem_id value="New Problem">
        <p align=left>
  <div class="ui toggle checkbox">
        <input type="checkbox" id="preview-toggle" checked>
        <label for="preview-toggle">题目预览</label>
    </div>
          <?php echo "<h3>".$MSG_TITLE."</h3>"?>
          <input class="input input-large" style="width:100%;" type=text name='title' id='title' > 
		<input class="btn btn-success" type=submit value='<?php echo $MSG_SAVE?>' name=submit> 
	  <input class='btn btn-primary' id='ai_bt' type=button value='AI一下' onclick='ai_gen()' >
		<input class='btn btn-danger'  type=reset value='<?php echo $MSG_RESET?>' >
	</p>
        <p align=left>
          <h4>题型</h4>
          <select name="problem_type" id="problem_type" class="form-control" onchange="toggleProblemFields()">
            <option value="programming">编程题</option>
            <option value="choice_single">单选题</option>
            <option value="choice_multi">多选题</option>
            <option value="judge">判断题</option>
          </select>
        </p>
        <p align=left>
          <h4>分值</h4>
          <input type="number" name="score" class="form-control" min="0" max="1000" value="100">
        </p>

        <div id="programming_fields">
          <p align=left>
            <?php echo $MSG_Time_Limit?>
            <input class="input input-mini" type=number min="0.001" max="300" step="0.001" name=time_limit size=20 value=1> sec
            <?php echo $MSG_Memory_Limit?>
            <input class="input input-mini" type=number min="1" max="2048" step="1" name=memory_limit size=20 value=128> MiB
          </p>
        </div>
        
        <!-- 选择题选项区域 -->
        <div id="choice_fields" style="display:none;">
          <h4>选项</h4>
          <div id="options_container">
            <div class="option_item">
              <label>A: </label>
              <input type="text" name="option_content[]" class="form-control" style="width:90%; display:inline-block;" placeholder="选项A内容">
              <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)" style="display:none;">删除</button>
            </div>
            <div class="option_item">
              <label>B: </label>
              <input type="text" name="option_content[]" class="form-control" style="width:90%; display:inline-block;" placeholder="选项B内容">
              <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)" style="display:none;">删除</button>
            </div>
            <div class="option_item">
              <label>C: </label>
              <input type="text" name="option_content[]" class="form-control" style="width:90%; display:inline-block;" placeholder="选项C内容">
              <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)" style="display:none;">删除</button>
            </div>
            <div class="option_item">
              <label>D: </label>
              <input type="text" name="option_content[]" class="form-control" style="width:90%; display:inline-block;" placeholder="选项D内容">
              <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)" style="display:none;">删除</button>
            </div>
          </div>
          <button type="button" class="btn btn-success btn-sm" onclick="addOption()">添加选项</button>
          
          <h4>正确答案</h4>
          <div id="answer_container">
            <!-- 单选/判断题用radio，多选用checkbox，动态生成 -->
          </div>

          <h4>答案解析</h4>
          <textarea name="analysis" class="kindeditor" rows=5 cols=80></textarea>
        </div>
        <p align=left>
          <?php echo "<h4>".$MSG_Description."(<64kB)</h4>"?>
	  <textarea class="kindeditor" rows=13 name=description cols=80><span class='md auto_select'>&nbsp;
&nbsp;</span></textarea><br>
        </p>
        <div id="programming_extra_fields">
        <p align=left>
          <?php echo "<h4>".$MSG_Input."(<64kB)</h4>"?>
          <textarea class="kindeditor" rows=13 name=input cols=80><span class='md'>
</span></textarea><br></textarea><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Output."(<64kB)</h4>"?>
          <textarea  class="kindeditor" rows=13 name=output cols=80><span class='md'>
</span></textarea><br></textarea><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Sample_Input."(<64kB)</h4>"?>
          <textarea  class="input input-large" style="width:100%;" rows=13 name=sample_input></textarea><br><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Sample_Output."(<64kB)</h4>"?>
          <textarea  class="input input-large" style="width:100%;" rows=13 name=sample_output></textarea><br><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Test_Input."</h4>"?>
          <?php echo "(".$MSG_HELP_MORE_TESTDATA_LATER.")"?><br>
          <textarea class="input input-large" style="width:100%;" rows=13 name=test_input></textarea><br><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Test_Output."</h4>"?>
          <?php echo "(".$MSG_HELP_MORE_TESTDATA_LATER.")"?><br>
          <textarea class="input input-large" style="width:100%;" rows=13 name=test_output></textarea><br><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_HINT."(<64kB)</h4>"?>
          <textarea class="kindeditor" rows=13 name=hint cols=80><span class='md'>
</span></textarea><br></textarea><br>
        </p>
        <p>
          <?php echo "<h4>".$MSG_SPJ."</h4>"?>
	  <input type=radio name=spj value='0' checked ><?php echo $MSG_NJ?> 更多测试数据，在题目添加后补充。<br>
	  <input type=radio name=spj value='1' ><?php echo $MSG_SPJ?> <?php echo "(".$MSG_HELP_SPJ.")"?><br>
	  <input type=radio name=spj value='2' ><?php echo $MSG_RTJ?><br>
        </p>
        </div>
        <p align=left>
          <?php echo "<h4>竞赛来源</h4>"?>
          <select name="contest_source" class="form-control" style="width:100%;">
            <option value="">无</option>
            <option value="蓝桥杯">蓝桥杯</option>
            <option value="CSP-J">CSP-J</option>
            <option value="CSP-S">CSP-S</option>
            <option value="GESP">GESP</option>
            <option value="NOIP">NOIP</option>
            <option value="其他">其他</option>
          </select>
          <br><br>
          <?php echo "<h4>".$MSG_SOURCE."</h4>"?>
          <textarea name=source style="width:100%;" rows=1 placeholder="多个分类/来源用逗号或空格分隔，比如：蓝桥杯2023 数学 基础题"><?php echo htmlentities($source,ENT_QUOTES,'UTF-8') ?></textarea><br><br>
        </p>
        <p align=left>
          <?php echo "<h4>难度</h4>"?>
          <select name="level" class="form-control">
            <option value="1">1 - 入门</option>
            <option value="2">2 - 入门</option>
            <option value="3">3 - 基础</option>
            <option value="4">4 - 基础</option>
            <option value="5">5 - 进阶</option>
            <option value="6">6 - 进阶</option>
            <option value="7">7 - 竞赛</option>
            <option value="8">8 - 竞赛</option>
          </select>
          <br><br>
        </p>
        <p align=left><?php echo "<h4>".$MSG_CONTEST."</h4>"?>
          <select name=contest_id>
            <?php
            $sql="SELECT `contest_id`,`title` FROM `contest` order by `contest_id` DESC";
            $result=pdo_query($sql);
            echo "<option value=''>无</option>";
            if (count($result)==0) {
            }
            else {
              foreach ($result as $row) {
                echo "<option value='{$row['contest_id']}'>{$row['contest_id']} {$row['title']}</option>";
              }
            }?>
          </select>
        </p>
        
        <?php if(isset($school_list) && is_array($school_list)): ?>
        <p align=left>
          <?php echo "<h4>".$MSG_SCHOOL."</h4>"?>
          <select name="school_id" class="form-control" style="width:100%;">
            <option value="">选择学校</option>
            <?php foreach ($school_list as $school): ?>
              <option value="<?php echo $school['id'] ?>" <?php echo ($current_user_school_id == $school['id']) ? 'selected' : ''; ?>>
                <?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </p>
        <p align=left>
          <label>
            <input type="checkbox" name="is_public" value="1"> 公开题目（允许其他学校访问）
          </label>
        </p>
        <?php endif; ?>

        <div align=center>
          <?php require_once("../include/set_post_key.php");?>
          <input type=submit value='<?php echo $MSG_SAVE?>' name=submit>
        </div>
     
    </form>
  </div>
<script src="<?php echo $OJ_CDN_URL."/template/bs3/"?>marked.min.js"></script>
<script>
  function toggleProblemFields() {
    var problemType = document.getElementById('problem_type').value;
    var programmingFields = document.getElementById('programming_fields');
    var programmingExtraFields = document.getElementById('programming_extra_fields');
    var choiceFields = document.getElementById('choice_fields');
    var answerContainer = document.getElementById('answer_container');

    if (problemType == 'programming') {
      programmingFields.style.display = 'block';
      programmingExtraFields.style.display = 'block';
      choiceFields.style.display = 'none';
    } else {
      programmingFields.style.display = 'none';
      programmingExtraFields.style.display = 'none';
      choiceFields.style.display = 'block';
      
      // 生成答案选择项
      answerContainer.innerHTML = '';
      var options = document.querySelectorAll('#options_container .option_item input');
      
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
        }
        
        for (var i = 0; i < options.length; i++) {
          var label = String.fromCharCode(65 + i); // A, B, C, D...
          answerContainer.innerHTML += `
            <label style="margin-right: 20px;">
              <input type="radio" name="answer" value="${label}"> ${label}
            </label>
          `;
        }
      } else if (problemType == 'choice_multi') {
        // 多选题用checkbox
        for (var i = 0; i < options.length; i++) {
          var label = String.fromCharCode(65 + i); // A, B, C, D...
          answerContainer.innerHTML += `
            <label style="margin-right: 20px;">
              <input type="checkbox" name="answer[]" value="${label}"> ${label}
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
        let totalHeight=window.innerHeight;
        let totalWidth=window.innerWidth;
        let editorWidth=parseInt(totalWidth*0.45);
        let previewWidth=totalWidth-editorWidth-30;
        let panelTop=120;
        let panelHeight=totalHeight-panelTop-30;
	if(previewWidth<500) {editorWidth=350; previewWidth=totalWidth-380;}
        let submitURL="../problem.php?id=1000";
        console.log("editorWidth:"+editorWidth+" previewWidth:"+previewWidth+" height:"+panelHeight);
        let main=$("#main");
        let problem=main.html();
                main.removeClass("container");
                main.css("width",editorWidth);
                main.css("margin-left","10px");
                main.css("max-height",panelHeight+"px");
                main.css("overflow-y","auto");
                main.parent().append("<div id='preview' class='container' style='opacity:0.95;position:fixed;z-index:1000;top:"+panelTop+"px;left:"+(editorWidth+15)+"px;width:"+previewWidth+"px;height:"+panelHeight+"px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;background:#fff;'></div>");
                $("#preview").html("<iframe id='previewFrame' src='"+submitURL+"&spa' width='100%' height='"+panelHeight+"px' style='border:none;'></iframe>");
        $("#submit").remove();
        setTimeout('hide()',1500);	
	$("input").keyup(sync);
	$("textarea").keyup(sync);
	$("#problem_type, #options_container, #answer_container").on("change keyup input",sync);
  }
  function hide(){
	let preview=$("#previewFrame").contents();
	if(preview.find(".ui.buttons").length) preview.find(".ui.buttons").hide();
	preview.find("span.ui.label").eq(2).hide();
	preview.find("span.ui.label").eq(3).hide();
	preview.find("span.ui.label").eq(4).hide();
	preview.find("span.ui.label").eq(5).hide();
	if(preview.find("#show_tag_div").length) preview.find("#show_tag_div").parent().hide();
	sync();
  }
  function sync(){
	console.log("sync...");
	let preview=$("#previewFrame").contents();
	let title=$("input[name=title]").val();
	preview.find("h1:first").html(title);
	let problemType=$("#problem_type").val();
	var grid=preview.find(".ui.grid").eq(1);
	var fw=$("#previewFrame")[0];
	var hasMathJax=fw!=undefined&&fw.contentWindow.MathJax!=undefined;

	if(problemType=="programming"){
		// 编程题：重建预览内容
		var time=$("input[name=time_limit]").val();
		var memory=$("input[name=memory_limit]").val();
		var description=$("textarea[name=description]").val()||"";
		var input=$("textarea[name=input]").val()||"";
		var output=$("textarea[name=output]").val()||"";
		var sinput=$("textarea[name=sample_input]").val()||"";
		var soutput=$("textarea[name=sample_output]").val()||"";
		var hint=$("textarea[name=hint]").val()||"";
		var esc=function(s){ return $("<div/>").text(s).html(); };

		var headerHtml="<div class='row' style='margin-top:-15px'>"
			+"<span class='ui label'><?php echo $MSG_Time_Limit ?>："+esc(time)+"</span> "
			+"<span class='ui label'><?php echo $MSG_Memory_Limit ?>："+esc(memory)+"</span>"
			+"</div>";

		var bodyHtml="";
		if(description) bodyHtml+="<div class='row'><div class='column'>"
			+"<h4 class='ui top attached block header'><?php echo $MSG_Description?></h4>"
			+"<div id='description' class='ui bottom attached segment font-content'>"+description+"</div></div></div>";
		if(input) bodyHtml+="<div class='row'><div class='column'>"
			+"<h4 class='ui top attached block header'><?php echo $MSG_Input?></h4>"
			+"<div id='input' class='ui bottom attached segment font-content'>"+input+"</div></div></div>";
		if(output) bodyHtml+="<div class='row'><div class='column'>"
			+"<h4 class='ui top attached block header'><?php echo $MSG_Output?></h4>"
			+"<div id='output' class='ui bottom attached segment font-content'>"+output+"</div></div></div>";
		if(sinput) bodyHtml+="<div class='row'><div class='column'>"
			+"<h4 class='ui top attached block header'><?php echo $MSG_Sample_Input?></h4>"
			+"<div class='ui bottom attached segment font-content'>"
			+"<pre style='margin:0'><code>"+esc(sinput)+"</code></pre></div></div></div>";
		if(soutput) bodyHtml+="<div class='row'><div class='column'>"
			+"<h4 class='ui top attached block header'><?php echo $MSG_Sample_Output?></h4>"
			+"<div class='ui bottom attached segment font-content'>"
			+"<pre style='margin:0'><code>"+esc(soutput)+"</code></pre></div></div></div>";
		if(hint) bodyHtml+="<div class='row'><div class='column'>"
			+"<h4 class='ui top attached block header'><?php echo $MSG_HINT?></h4>"
			+"<div id='hint' class='ui bottom attached segment font-content'>"+hint+"</div></div></div>";

		grid.html(headerHtml+bodyHtml);
		// 渲染markdown
		grid.find("#description .md, #input .md, #output .md, #hint .md").each(function(){
			$(this).html(marked.parse($(this).html()));
		});
		if(hasMathJax) fw.contentWindow.MathJax.typeset();
	} else {
		// 非编程题：重建预览内容
		var typeLabel=problemType=="choice_single"?"单选题":(problemType=="choice_multi"?"多选题":"判断题");
		var inputType=problemType=="choice_multi"?"checkbox":"radio";

		// 获取选项
		var optionLabels=[];
		if(problemType=="judge"){
			optionLabels=[{label:"A",content:"对"},{label:"B",content:"错"}];
		} else {
			var optionInputs=document.querySelectorAll("#options_container .option_item");
			for(var i=0;i<optionInputs.length;i++){
				var inp=optionInputs[i].querySelector("input[name='option_content[]']");
				var lbl=String.fromCharCode(65+i);
				optionLabels.push({label:lbl,content:inp?inp.value:""});
			}
		}

		// 获取当前答案
		var currentAnswer="";
		if(problemType=="choice_multi"){
			$("#answer_container input:checked").each(function(){ currentAnswer+=$(this).val(); });
		} else {
			var checked=$("#answer_container input:checked");
			if(checked.length) currentAnswer=checked.val();
		}

		// 构建选项HTML
		var optionsHtml="";
		for(var i=0;i<optionLabels.length;i++){
			var opt=optionLabels[i];
			var isChecked=currentAnswer.indexOf(opt.label)!==-1?"checked":"";
			optionsHtml+="<div style='padding:8px 12px;margin-bottom:6px;border:1px solid #e8e8e8;border-radius:4px;background:#fafafa;'>"
				+"<label style='font-weight:normal;cursor:pointer;display:flex;align-items:flex-start;'>"
				+"<input type='"+inputType+"' name='preview_answer' value='"+opt.label+"' "+isChecked+" disabled style='margin-right:8px;margin-top:3px;'>"
				+"<span><strong>"+opt.label+".</strong> "+$("<div/>").text(opt.content).html()+"</span>"
				+"</label></div>";
		}

		// 构建答案显示
		var answerHtml="";
		if(currentAnswer) answerHtml="<p style='margin:0;'><strong>正确答案：</strong>"+$("<div/>").text(currentAnswer).html()+"</p>";

		// 构建解析
		var analysisContent=$("textarea[name=analysis]").val()||"";
		var analysisHtml="";
		if(analysisContent) analysisHtml="<div style='border-top:1px solid #eee;padding-top:12px;margin-top:12px;'>"
			+"<p style='margin:0 0 8px;'><strong>答案解析：</strong></p><div class='font-content'>"+analysisContent+"</div></div>";

		// 构建description
		var descriptionContent=$("textarea[name=description]").val()||"";
		var descriptionHtml="";
		if(descriptionContent) descriptionHtml="<div style='border-bottom:1px solid #eee;padding-bottom:12px;margin-bottom:12px;'>"
			+"<h4 class='ui top attached block header'><?php echo $MSG_Description?></h4>"
			+"<div class='ui bottom attached segment font-content'>"+descriptionContent+"</div></div>";

		// 重建预览内容（替换第二个.ui.grid，即内容区域）
		grid.html(
			"<div class='row'><div class='column'>"
			+"<h4 class='ui top attached block header'>"+typeLabel+"</h4>"
			+"<div class='ui bottom attached segment font-content'>"
			+descriptionHtml
			+"<div style='margin-top:12px;'>"+optionsHtml+"</div>"
			+"<div style='margin-top:12px;padding-top:12px;border-top:1px solid #eee;'>"+answerHtml+"</div>"
			+analysisHtml
			+"</div></div></div>"
		);

		// 隐藏编程题特有的label和按钮
		preview.find("span.ui.label").each(function(){ $(this).hide(); });
		if(preview.find("#submit-buttons").length) preview.find("#submit-buttons").hide();
		if(preview.find("#show_tag_div").length) preview.find("#show_tag_div").parent().hide();

		if(hasMathJax) fw.contentWindow.MathJax.typeset();
	}
  }
 
   $(document).ready(function(){
            // 默认开启预览功能
           <?php if (!(isset($OJ_OLD_FASHINED) && $OJ_OLD_FASHINED )) echo " transform();" ?>
            
            // 监听checkbox的点击事件
            $('#preview-toggle').change(function() {
                if(this.checked) {
                    transform();
                } else {
                    // 假设这里是关闭预览的函数
                    untransform();
                }
            });
        });
function untransform() {
    console.log("预览关闭");
    // 恢复原始的 #main 元素样式
    let main = $("#main");
    main.addClass("padding");
    main.css("width", "");
    main.css("margin-left", "");

    // 移除预览的 iframe
    $("#preview").remove();

  
    // 移除同步事件
    $("input").off('keyup', sync);
    $("textarea").off('keyup', sync);
}
function fill_data( data ){

   let title=$('#title').val();
	    console.log(title);
		if(title==""){
				$('#title').val(data);
		}else{
		    // 尝试以JSON格式解析AI输出内容
		    let parsedData = null;
		    
		    // 先检查是否是分隔符格式
		    if(data.indexOf('###TITLE###') !== -1){
			console.log("检测到分隔符格式");
			let sections = data.split('###');
			parsedData = {};
			
			for(let i = 0; i < sections.length; i++){
			    let section = sections[i].trim();
			    if(section.startsWith('TITLE###')){
				parsedData.title = section.replace('TITLE###', '').trim();
			    } else if(section.startsWith('DESCRIPTION###')){
				parsedData.description = section.replace('DESCRIPTION###', '').trim();
			    } else if(section.startsWith('INPUT###')){
				parsedData.input = section.replace('INPUT###', '').trim();
			    } else if(section.startsWith('OUTPUT###')){
				parsedData.output = section.replace('OUTPUT###', '').trim();
			    } else if(section.startsWith('SAMPLE_INPUT###')){
				parsedData.sample_input = section.replace('SAMPLE_INPUT###', '').trim();
			    } else if(section.startsWith('SAMPLE_OUTPUT###')){
				parsedData.sample_output = section.replace('SAMPLE_OUTPUT###', '').trim();
			    } else if(section.startsWith('HINT###')){
				parsedData.hint = section.replace('HINT###', '').trim();
			    }
			}
		    } else {
			// 尝试解析JSON
			try {
			    console.log("尝试解析JSON格式");
			    parsedData = JSON.parse(data);
			    console.log("JSON解析成功");
			} catch(e) {
			    console.log("JSON解析失败,使用旧格式");
			    // 如果解析失败,按旧格式处理
			    let description = "<span class='md'>" + data + "</span>";
			    $("textarea[name='description']").val(description);
			    parsedData = null;
			}
		    }
		    
		    // 如果成功解析出结构化数据,填充表单
		    if(parsedData && typeof parsedData === 'object') {
			console.log("=== 解析后的数据 ===");
			console.log(parsedData);
			
			// 填充到对应的表单字段
			if(parsedData.title) {
			    $('#title').val(parsedData.title);
			    console.log("填充title:", parsedData.title);
			}
			
			// 处理description - 需要特殊处理kindeditor
			if(parsedData.description) {
			    let descContent = "<span class='md'>" + parsedData.description + "</span>";
			    // 先尝试设置textarea的值
			    $("textarea[name='description']").val(descContent);
			    // 如果有kindeditor实例,也设置它的值
			    try {
				if(typeof KindEditor !== 'undefined') {
				    let editor = KindEditor.instances[0]; // 第一个编辑器是description
				    if(editor) {
					editor.html(descContent);
					console.log("通过KindEditor设置description成功");
				    }
				}
			    } catch(e) {
				console.log("KindEditor设置失败,已使用textarea方式:", e);
			    }
			    console.log("填充description");
			}
			
			// 处理input - 也是kindeditor
			if(parsedData.input) {
			    let inputContent = "<span class='md'>" + parsedData.input + "</span>";
			    $("textarea[name='input']").val(inputContent);
			    try {
				if(typeof KindEditor !== 'undefined') {
				    let editor = KindEditor.instances[1]; // 第二个编辑器是input
				    if(editor) {
					editor.html(inputContent);
				    }
				}
			    } catch(e) {
				console.log("KindEditor input设置失败:", e);
			    }
			    console.log("填充input");
			}
			
			// 处理output - 也是kindeditor
			if(parsedData.output) {
			    let outputContent = "<span class='md'>" + parsedData.output + "</span>";
			    $("textarea[name='output']").val(outputContent);
			    try {
				if(typeof KindEditor !== 'undefined') {
				    let editor = KindEditor.instances[2]; // 第三个编辑器是output
				    if(editor) {
					editor.html(outputContent);
				    }
				}
			    } catch(e) {
				console.log("KindEditor output设置失败:", e);
			    }
			    console.log("填充output");
			}
			
			if(parsedData.sample_input) {
			    $("textarea[name='sample_input']").val(parsedData.sample_input);
			    console.log("填充sample_input");
			}
			if(parsedData.sample_output) {
			    $("textarea[name='sample_output']").val(parsedData.sample_output);
			    console.log("填充sample_output");
			}
			
			// 处理hint - 也是kindeditor
			if(parsedData.hint) {
			    let hintContent = "<span class='md'>" + parsedData.hint + "</span>";
			    $("textarea[name='hint']").val(hintContent);
			    try {
				if(typeof KindEditor !== 'undefined') {
				    let editor = KindEditor.instances[3]; // 第四个编辑器是hint
				    if(editor) {
					editor.html(hintContent);
				    }
				}
			    } catch(e) {
				console.log("KindEditor hint设置失败:", e);
			    }
			    console.log("填充hint");
			}
		     }

   	}
	window.setTimeout('sync()',1000);
	$('#ai_bt').prop('disabled', false);;
	$('#ai_bt').val('AI一下');
}
function pull_result(id){
	console.log(id);
    $.ajax({
	url: '../aiapi/ajax.php', 
	type: 'GET',
	data: { id: id },
	success: function(data) {
		if(data=="waiting"){
			window.setTimeout('pull_result('+id+')',1000);
		}else{
			fill_data(data);
		}
	},
	error: function() {
	    $('#ai_bt').val('获取数据失败');
	$('#ai_bt').prop('disabled', false);
	}
    });
}
	function ai_gen(filename){
		    let oldval=$('#ai_bt').val();
		    $('#ai_bt').val('AI思考中...请稍候...');
		    $('#ai_bt').prop('disabled', true);;
		    let title=$('#title').val();
		    $.ajax({
		    	url: '../<?php echo $OJ_AI_API_URL?>', 
			type: 'GET',
			data: { title: title },
			success: function(data) {
				if(parseInt(data)>0)
					window.setTimeout('pull_result('+data+')',1000);
			},
			error: function() {
			    $('#ai_bt').val('获取数据失败');
		    	$('#ai_bt').prop('disabled', false);
			}
		    });
	}

</script>
</body>
</html>
