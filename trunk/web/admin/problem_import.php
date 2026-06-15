<?php 
  require_once("../include/db_info.inc.php");
  require_once("admin-header.php");

  if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'contest_creator']) || isset($_SESSION[$OJ_NAME.'_problem_importer']))) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
  }

  function writable($path) {
    $ret = false;
    $fp = fopen($path."/testifwritable.tst","w");
    $ret = !($fp===false);

    if($fp!=false) {
	    fclose($fp);
    	    unlink($path."/testifwritable.tst");
    }
    return $ret;
  }

  $maxfile = min(ini_get("upload_max_filesize"), ini_get("post_max_size"));

  echo "<center><h3>".$MSG_PROBLEM."-".$MSG_IMPORT."</h3></center>";

?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Cache-Control" content="no-cache">
  <meta http-equiv="Content-Language" content="zh-cn">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Problem Import</title>
</head>
<body leftmargin="30">
  <div class="container">
    <?php 
    $show_form = true;

    if (!isset($OJ_SAE) || !$OJ_SAE) {
      if (!writable($OJ_DATA)) {
        echo "- You need to add  $OJ_DATA into your open_basedir setting of php.ini,<br>
        or you need to execute:<br>
        <b>chmod 775 -R $OJ_DATA && chgrp -R ".get_current_user()." $OJ_DATA</b><br>
        you can't use import function at this time.<br>"; 

        if($OJ_LANG == "cn")
          echo "权限异常，请先去执行sudo chmod 775 -R $OJ_DATA <br> 和 sudo chgrp -R ".get_current_user()." $OJ_DATA <br>";
	  
        $show_form = false;
	if(get_current_user()=="www")
	  echo "如果你是宝塔用户，请关闭宝塔的跨站防护功能，如果你是lnmp或者centos用户，请禁用open_basedir。如果坚持使用，请将/home/jduge/data目录加进去。";
      }
	    

      if (!file_exists("../upload"))
				mkdir("../upload");

      if (!writable("../upload")) {
        echo "../upload is not writable, <b>chmod 770</b> to it.<br>";
        $show_form = false;
      }
    }
    ?>

    <?php if ($show_form) { ?>
    <h4>常用导入入口</h4>

    <div class='well well-sm'>
      <b>编程题导入：FPS XML / ZIP XML</b><br>
      适用于标准编程题，包含题面、输入输出说明、样例、测试数据。<br>
      推荐用于 GESP/CSP/蓝桥杯等真题中的编程题导入。支持上传单个 <code>.xml</code> 文件，或包含一个/多个 XML 文件的 <code>.zip</code> 压缩包（不支持子目录）。分值可在 XML 的 <code>&lt;score&gt;</code> 标签中填写。<br><br>
      <form class='form-inline' action='problem_import_xml.php' method=post enctype="multipart/form-data">
        <div class='form-group'>
          <input class='form-control' type=file name=fps accept=".xml,.zip">
          <button class='btn btn-success btn-sm' type=submit>导入编程题</button>
        </div>
        <?php require("../include/set_post_key.php");?>
      </form>
    </div>

    <div class='well well-sm'>
      <b>选择题导入：CSV</b><br>
      适用于单选题、多选题、判断题批量导入。<br>
      推荐用于 GESP/CSP/蓝桥杯等真题中的客观题导入。请先下载模板，按模板填写后保存为 CSV UTF-8 格式。<br><br>
      <form class='form-inline' action='problem_import_choice.php' method=post enctype="multipart/form-data">
        <div class='form-group'>
          <a class='btn btn-info btn-sm' href='problem_import_choice.php?action=download' target='_blank'>下载选择题模板(CSV)</a>
          <input class='form-control ml-2' type=file name=csv_file accept=".csv">
          <button class='btn btn-success btn-sm ml-2' type=submit name="do_import" value="1">导入选择题</button>
        </div>
        <?php require("../include/set_post_key.php");?>
      </form>
    </div>

    <hr>
    <h4>第三方 OJ 兼容导入入口</h4>
    <div class='alert alert-warning'>
      以下入口仅用于从其它 OJ 导出的历史题库包迁移，格式要求各不相同，平时导入竞赛真题不建议使用。<br>
      如果你手上是本项目模板生成的真题文件，请优先使用上面的“编程题导入”和“选择题导入”。
    </div>

    <div class='well well-sm'>
      <b>QDUOJ JSON ZIP</b><br>
      适用于 QDUOJ 导出的 JSON 压缩包，未严格测试；不适用于普通 FPS XML 或选择题 CSV。<br><br>
      <form class='form-inline' action='problem_import_qduoj.php' method=post enctype="multipart/form-data">
        <div class='form-group'>
          <input class='form-control' type=file name=fps accept=".zip">
          <button class='btn btn-default btn-sm' type=submit>导入 QDUOJ 包</button>
        </div>
        <?php require("../include/set_post_key.php");?>
      </form>
    </div>

    <div class='well well-sm'>
      <b>SYZOJ ZIP</b><br>
      适用于 SYZOJ 导出的题目压缩包；不适用于普通 FPS XML 或选择题 CSV。<br><br>
      <form class='form-inline' action='problem_import_syzoj.php' method=post enctype="multipart/form-data">
        <div class='form-group'>
          <input class='form-control' type=file name=fps accept=".zip">
          <button class='btn btn-default btn-sm' type=submit>导入 SYZOJ 包</button>
        </div>
        <?php require("../include/set_post_key.php");?>
      </form>
    </div>

    <div class='well well-sm'>
      <b>HydroOJ ZIP</b><br>
      适用于 HydroOJ 导出的题目压缩包；不适用于普通 FPS XML 或选择题 CSV。<br><br>
      <form class='form-inline' action='problem_import_hydro.php' method=post enctype="multipart/form-data">
        <div class='form-group'>
          <input class='form-control' type=file name=fps accept=".zip">
          <button class='btn btn-default btn-sm' type=submit>导入 HydroOJ 包</button>
        </div>
        <?php require("../include/set_post_key.php");?>
      </form>
    </div>

    <div class='well well-sm'>
      <b>HOJ ZIP</b><br>
      适用于 HOJ 导出的题目压缩包；不适用于普通 FPS XML 或选择题 CSV。<br><br>
      <form class='form-inline' action='problem_import_hoj.php' method=post enctype="multipart/form-data">
        <div class='form-group'>
          <input class='form-control' type=file name=fps accept=".zip">
          <button class='btn btn-default btn-sm' type=submit>导入 HOJ 包</button>
        </div>
        <?php require("../include/set_post_key.php");?>
      </form>
    </div>

    <div class='well well-sm'>
      <b>TYVJ ZIP</b><br>
      适用于 TYVJ 导出的题目压缩包；不适用于普通 FPS XML 或选择题 CSV。<br><br>
      <form class='form-inline' action='problem_import_tyvj.php' method=post enctype="multipart/form-data">
        <div class='form-group'>
          <input class='form-control' type=file name=fps accept=".zip">
          <button class='btn btn-default btn-sm' type=submit>导入 TYVJ 包</button>
        </div>
        <?php require("../include/set_post_key.php");?>
      </form>
    </div>

    <div class='well well-sm'>
      <b>Markdown ZIP</b><br>
      适用于由 Markdown 文件组成的压缩包，约定每个 Markdown 文件首行为题目标题；不适用于普通 FPS XML 或选择题 CSV。<br><br>
      <form class='form-inline' action='problem_import_md.php' method=post enctype="multipart/form-data">
        <div class='form-group'>
          <input class='form-control' type=file name=fps accept=".zip">
          <button class='btn btn-default btn-sm' type=submit>导入 Markdown 包</button>
        </div>
        <?php require("../include/set_post_key.php");?>
      </form>
    </div>

    <?php } ?>

    <br><br>

    <?php if ($OJ_LANG == "cn") { ?>
    免费题目<a href="https://github.com/zhblue/freeproblemset/tree/master/fps-examples" target="_blank">下载</a>
    <?php } ?>

    <br><br>

    - Import FPS data, please make sure you file is smaller than [<?php echo $maxfile?>] or set upload_max_filesize and post_max_size in <span style='color:blue'>php.ini</span><br>
    - If you fail on import big files[10M+],try enlarge your [memory_limit] setting in <span style='color:blue'>php.ini</span><br>
    - To find the php configuration file, use <span style='color:blue'> find /etc -name php.ini </span>

  </div>

</body>
</html>

