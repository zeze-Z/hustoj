<?php $show_title="$MSG_STATUS - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<script src="<?php echo $OJ_CDN_URL?>template/<?php echo $OJ_TEMPLATE?>/js/textFit.min.js"></script>
<style>
/* ===== 评测状态页 · 晴空调度台风格（与顶部菜单栏 / problemset 配色统一） ===== */
@import url('fonts/zcool-kuaile.css');

.st-page {
    --st-sky: #67a0d5;
    --st-sky-deep: #3a6da8;
    --st-sky-light: #78b0d9;
    --st-cream: #fff8dd;
    --st-gold: #f4cd75;
    --st-gold-deep: #d9a841;
    --st-coral: #e2726d;
    --st-coral-deep: #c25d58;
    --st-mint: #7FD4B5;
    --st-mint-deep: #2E8B6F;
    --st-text: #3d4f6b;
    --st-text-soft: #7a8ba3;
    --st-border: #3d4f6b;
    --st-shadow: rgba(61, 79, 107, 0.12);
    --st-radius: 16px;

    padding: 20px 16px 40px;
    max-width: 1240px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", "Helvetica Neue", sans-serif;
    color: var(--st-text);
}

/* ===== 筛选卡片 ===== */
.st-filter-card {
    background: linear-gradient(135deg, #FFF9EC 0%, #F0F6FC 100%);
    border: 2.5px solid var(--st-border);
    border-radius: var(--st-radius);
    padding: 18px 20px;
    margin-bottom: 20px;
    box-shadow: 4px 4px 0 var(--st-shadow);
    position: relative;
    overflow: hidden;
}
.st-filter-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--st-sky), var(--st-gold), var(--st-coral), var(--st-mint));
}

.st-filter {
    position: relative;
    z-index: 1;
    margin: 0 !important;
}

.st-filter-grid {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px 16px;
}

.st-f-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.st-filter label {
    font-size: 0.88rem !important;
    font-weight: 600;
    color: var(--st-text);
    margin: 0 !important;
}

.st-filter input[type="text"] {
    border: 2px solid #d6e2ef !important;
    border-radius: 10px !important;
    padding: 7px 12px !important;
    font-size: 0.88rem !important;
    color: var(--st-text) !important;
    background: #fff !important;
    width: 90px !important;
    min-width: 90px;
    transition: all 0.2s !important;
}
.st-filter input[type="text"]:focus {
    border-color: var(--st-sky) !important;
    box-shadow: 0 0 0 3px rgba(103,160,213,0.15) !important;
    outline: none !important;
}

.st-filter select {
    border: 2px solid #d6e2ef !important;
    border-radius: 10px !important;
    padding: 7px 10px !important;
    font-size: 0.85rem !important;
    color: var(--st-text) !important;
    background: #fff !important;
    cursor: pointer;
    transition: all 0.2s !important;
}
.st-filter select:focus {
    border-color: var(--st-sky) !important;
    box-shadow: 0 0 0 3px rgba(103,160,213,0.15) !important;
    outline: none !important;
}

/* 查询按钮 */
.st-submit {
    background: linear-gradient(135deg, var(--st-sky), var(--st-sky-light)) !important;
    color: #fff !important;
    border: 2px solid var(--st-border) !important;
    box-shadow: 2px 2px 0 var(--st-shadow) !important;
    border-radius: 999px !important;
    font-weight: 700 !important;
    padding: 8px 20px !important;
    transition: transform 0.18s, box-shadow 0.18s !important;
    margin-left: 4px !important;
}
.st-submit:hover {
    transform: translateY(-2px) rotate(-1deg) !important;
    box-shadow: 3px 3px 0 var(--st-shadow) !important;
}

/* AWT 实时调度徽章 */
.st-awt {
    display: inline-flex !important;
    align-items: center;
    gap: 7px !important;
    background: #FFFBEB !important;
    color: var(--st-gold-deep) !important;
    border: 2px solid var(--st-gold) !important;
    border-radius: 999px !important;
    box-shadow: 2px 2px 0 var(--st-shadow) !important;
    font-weight: 700 !important;
    font-size: 0.78rem !important;
    padding: 7px 14px !important;
    margin: 0 !important;
}
.st-awt::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--st-mint-deep);
    box-shadow: 0 0 0 0 rgba(46,139,111,0.5);
    animation: st-pulse 1.8s ease-out infinite;
}
@keyframes st-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(46,139,111,0.5); }
    70%  { box-shadow: 0 0 0 7px rgba(46,139,111,0); }
    100% { box-shadow: 0 0 0 0 rgba(46,139,111,0); }
}

/* ===== 评测表格 ===== */
.st-table-wrap {
    background: #fff;
    border: 2.5px solid var(--st-border);
    border-radius: var(--st-radius);
    box-shadow: 4px 4px 0 var(--st-shadow);
    overflow-x: auto;
    margin-bottom: 18px;
}

#result-tab.st-table {
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
    white-space: nowrap;
}

#result-tab.st-table thead th {
    background: linear-gradient(135deg, var(--st-sky-deep), var(--st-sky)) !important;
    color: var(--st-cream) !important;
    font-family: 'ZCOOL KuaiLe', sans-serif !important;
    font-size: 0.95rem !important;
    font-weight: 400 !important;
    padding: 13px 12px !important;
    border-bottom: 2.5px solid var(--st-border) !important;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

#result-tab.st-table tbody tr {
    border-bottom: 1.5px solid #eef2f8 !important;
    transition: background 0.15s ease;
}
#result-tab.st-table tbody tr:nth-child(even) { background: #FFFDF6; }
#result-tab.st-table tbody tr:hover { background: #F0F6FC !important; }

#result-tab.st-table tbody td {
    padding: 11px 10px !important;
    color: var(--st-text) !important;
    font-size: 0.88rem !important;
    vertical-align: middle !important;
}

#result-tab.st-table tbody td a {
    color: var(--st-sky-deep);
    transition: color 0.15s;
}
#result-tab.st-table tbody td a:hover { color: var(--st-coral); }

/* ===== 评测结果徽章（Bootstrap label 类，由后端/auto_refresh.js 生成） ===== */
.st-table .label {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    border: 1.5px solid;
    line-height: 1.5;
    white-space: nowrap;
}
.st-table .label-success {
    background: #E8FBF3; color: var(--st-mint-deep); border-color: var(--st-mint-deep);
}
.st-table .label-danger {
    background: #FFF0EE; color: var(--st-coral-deep); border-color: var(--st-coral);
}
.st-table .label-warning {
    background: #FFF7E6; color: #B87309; border-color: var(--st-gold);
}
.st-table .label-info {
    background: #EBF3FB; color: var(--st-sky-deep); border-color: var(--st-sky);
}
.st-table .label-gray,
.st-table .lable-gray {
    background: #F1F3F7; color: var(--st-text-soft); border-color: #c3cfdd;
}
/* loader 转圈图标与徽章对齐 */
.st-table .td_result img {
    vertical-align: -3px;
    margin-left: 5px;
}

/* ===== 翻页 ===== */
.st-pagination {
    text-align: center;
    margin-bottom: 20px;
}
.st-page .ui.pagination.menu {
    background: #fff !important;
    border: 2.5px solid var(--st-border) !important;
    border-radius: 999px !important;
    box-shadow: 3px 3px 0 var(--st-shadow) !important;
    padding: 3px !important;
    gap: 2px;
}
.st-page .ui.pagination.menu .item {
    border-radius: 999px !important;
    color: var(--st-text) !important;
    font-weight: 600 !important;
    font-size: 0.85rem !important;
    min-width: 52px;
    text-align: center;
    padding: 8px 16px !important;
    transition: all 0.18s ease !important;
    border: none !important;
}
.st-page .ui.pagination.menu .item:hover {
    background: #F0F6FC !important;
    color: var(--st-sky-deep) !important;
}

/* ===== 移动端 ===== */
@media (max-width: 768px) {
    .st-page { padding: 14px 10px 32px; }

    .st-filter-card { padding: 14px; }
    .st-filter-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .st-f-item {
        flex-direction: column;
        align-items: stretch;
        gap: 4px;
        white-space: normal;
    }
    .st-f-item .field,
    .st-f-item select,
    .st-f-item input[type="text"] { width: 100% !important; box-sizing: border-box; }
    .st-f-actions {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .st-submit { margin-left: 0 !important; flex: 1; min-width: 120px; }
}

@media (max-width: 640px) {
    /* 表格转卡片：仍保留全部 td 的 DOM 顺序（auto_refresh.js 依赖 cells[] 索引） */
    .st-table-wrap { overflow: visible; }
    #result-tab.st-table,
    #result-tab.st-table tbody { display: block !important; }
    #result-tab.st-table thead { display: none !important; }

    /* 桌面专属列在卡片中隐藏 */
    #result-tab.st-table .desktop-only { display: none !important; }

    #result-tab.st-table tbody tr {
        display: flex !important;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px 12px;
        padding: 12px 14px !important;
        white-space: normal;
    }
    #result-tab.st-table tbody td {
        display: block !important;
        padding: 0 !important;
        font-size: 0.84rem !important;
        text-align: left !important;
        line-height: 1.45;
    }

    /* 结果行：全宽彩色横幅（DOM cells[4] = 第5列） */
    #result-tab.st-table tbody td:nth-child(5) {
        order: 1;
        flex: 0 0 100%;
        margin-bottom: 2px;
    }
    #result-tab.st-table tbody td:nth-child(5) .label {
        width: 100%;
        justify-content: flex-start;
        padding: 6px 12px;
        font-size: 0.82rem;
    }

    /* 用户 cells[1] / 昵称 cells[2]（第2、3列） */
    #result-tab.st-table tbody td:nth-child(2) {
        order: 2;
        font-weight: 700;
    }
    #result-tab.st-table tbody td:nth-child(3) {
        order: 3;
        color: var(--st-text-soft);
        font-weight: 400 !important;
        font-size: 0.8rem !important;
    }

    /* 题目 cells[3]（第4列，全宽）/ 语言 cells[7]（第8列，全宽） */
    #result-tab.st-table tbody td:nth-child(4) {
        order: 4;
        flex: 0 0 100%;
        font-weight: 700;
    }
    #result-tab.st-table tbody td:nth-child(4)::before {
        content: "题目 ";
        color: var(--st-text-soft);
        font-weight: 400;
        font-size: 0.74rem;
        margin-right: 2px;
    }
    #result-tab.st-table tbody td:nth-child(8) {
        order: 5;
        flex: 0 0 100%;
        color: var(--st-text-soft);
        font-size: 0.78rem !important;
    }

    /* 内存 cells[5] / 耗时 cells[6]（第6、7列，同一行），提交时间 cells[9]（第10列，全宽） */
    #result-tab.st-table tbody td:nth-child(6),
    #result-tab.st-table tbody td:nth-child(7) {
        order: 6;
        display: inline-flex !important;
        align-items: baseline;
        gap: 3px;
        color: var(--st-text);
    }
    #result-tab.st-table tbody td:nth-child(6)::before { content: "内存"; }
    #result-tab.st-table tbody td:nth-child(7)::before { content: "耗时"; }
    #result-tab.st-table tbody td:nth-child(6)::before,
    #result-tab.st-table tbody td:nth-child(7)::before {
        color: var(--st-text-soft);
        font-size: 0.72rem;
        font-weight: 400;
    }
    #result-tab.st-table tbody td:nth-child(10) {
        order: 7;
        flex: 0 0 100%;
        color: var(--st-text-soft);
        font-size: 0.76rem !important;
        margin-top: 2px;
    }
}

@media (max-width: 380px) {
    .st-filter-grid { grid-template-columns: 1fr; }
}
</style>
<div class="padding st-page">

  <!-- <form action="" class="ui mini form" method="get" role="form" id="form"> -->
  <form id=simform class="ui mini form st-filter" action="status.php" method="get">
    <div class="st-filter-card">
    <div class="st-filter-grid">
      <div class="st-f-item">
      <label><?php echo $MSG_PROBLEM_ID?>：</label>
      <div class="field"><input name="problem_id" type="text" value="<?php echo isset($problem_id)?htmlspecialchars($problem_id, ENT_QUOTES):"" ?>"></div>
      </div>
      <div class="st-f-item">
        <label><?php echo $MSG_USER?>：</label>
        <div class="field"><input name="user_id" type="text" value="<?php echo  isset($user_id)?htmlspecialchars($user_id, ENT_QUOTES):"" ?>"></div>
      </div>

      <div class="st-f-item">
        <label><?php echo $MSG_SCHOOL?>：</label>
        <div class="field"><input name="school" type="text" value="<?php echo isset($school)?htmlspecialchars($school, ENT_QUOTES):"" ?>"></div>
      </div>
      <div class="st-f-item">
        <label><?php echo $MSG_GROUP_NAME?>：</label>
        <div class="field"><input name="group_name" type="text" value="<?php echo isset($group_name)?htmlspecialchars($group_name, ENT_QUOTES):"" ?>"></div>
      </div>
      <div class="st-f-item">
        <label><?php echo $MSG_LANG?>：</label>
        <select class="form-control" size="1" name="language">
          <option value="-1">All</option>
          <?php
          if(isset($_GET['language'])){
            $selectedLang=intval($_GET['language']);
          }else{
            $selectedLang=-1;
          }
          $lang_count=count($language_ext);
          $langmask=$OJ_LANGMASK;
          $lang=(~((int)$langmask))&((1<<($lang_count))-1);
          for($i=0;$i<$lang_count;$i++){
            if($lang&(1<<$i))
            echo"<option value=$i ".( $selectedLang==$i?"selected":"").">
            ".$language_name[$i]."
            </option>";
          }
          ?>
        </select>
      </div>
      <div class="st-f-item">
        <label>状态：</label>
        <select class="form-control" size="1" name="jresult">
          <?php if (isset($_GET['jresult'])) $jresult_get=intval($_GET['jresult']);
          else $jresult_get=-1;
          if ($jresult_get>=12||$jresult_get<0) $jresult_get=-1;
          if ($jresult_get==-1) echo "<option value='-1' selected>All</option>";
          else echo "<option value='-1'>All</option>";
          for ($j=0;$j<12;$j++){
          $i=($j+4)%12;
          if ($i==$jresult_get) echo "<option value='".strval($jresult_get)."' selected>".$jresult[$i]."</option>";
          else echo "<option value='".strval($i)."'>".$jresult[$i]."</option>";
          }
          echo "</select>";
          ?>
        </div>
          <?php if(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'source_browser'])){
            if(isset($_GET['showsim']))
            $showsim=intval($_GET['showsim']);
            else
            $showsim=0;
            echo "<div class=\"st-f-item\"><label>相似度：</label>";
          echo "
          <select id=\"appendedInputButton\" class=\"form-control\" name=showsim onchange=\"document.getElementById('simform').submit();\">
          <option value=0 ".($showsim==0?'selected':'').">All</option>
          <option value=80 ".($showsim==80?'selected':'').">80</option>
          <option value=85 ".($showsim==85?'selected':'').">85</option>
          <option value=90 ".($showsim==90?'selected':'').">90</option>
          <option value=95 ".($showsim==95?'selected':'').">95</option>
          <option value=100 ".($showsim==100?'selected':'').">100</option>
          </select></div>";
          }
          ?>
      <div class="st-f-actions">
      <button class="ui labeled icon mini green button st-submit" type="submit">
        <i class="search icon"></i>
       <?php echo $MSG_SEARCH;?>
      </button>
                <span class='ui mini grey button st-awt'>AWT:<?php echo round($avg_delay,2)?>s </span>
                 <script>var AWT=<?php echo round($avg_delay*500,0) ?>;</script>
      </div>
    </div>
    </div>
  </form>


  <div class="st-table-wrap">
  <table id="result-tab" class="ui very basic center aligned table st-table">
    <thead>
      <tr>
        <th class='desktop-only item'><?php echo $MSG_RUNID?></th>
        <th><?php echo $MSG_USER?></th>
        <th><?php echo $MSG_NICK?></th>
        <th><?php echo $MSG_PROBLEM_ID?></th>
        <th><?php echo $MSG_RESULT?></th>
        <th><?php echo $MSG_MEMORY?></th>
        <th><?php echo $MSG_TIME?></th>
        <th ><?php echo $MSG_LANG?></th>
        <th  class='desktop-only item'><?php echo $MSG_CODE_LENGTH?></th>
        <th ><?php echo $MSG_SUBMIT_TIME?></th>
       <?php    if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
                                                        echo "<th class='desktop-only item'>";
                                                                echo $MSG_JUDGER;
                                                        echo "</th>";
                                                } ?>
      </tr>
    </thead>
    <tbody  style='font-weight:700' >
      <!-- <tr v-for="item in items" :config="displayConfig" :show-rejudge="false" :data="item" is='submission-item'>
          </tr> -->
    <?php
    foreach($view_status as $row){
    $i=0;
    echo "<tr>";
    foreach($row as $table_cell){
      if ($i==4)
        echo "<td class='td_result'>";
      else if($i==0 || $i>7 && $i!=9)
        echo "<td class='desktop-only item '>";
      else
        echo "<td>";
      echo $table_cell;
      echo "</td>";
      $i++;
    }
    echo "</tr>\n";
    }
    ?>

    </tbody>
  </table>
  </div>

  <div class="st-pagination">

  <div style="text-align: center;">
        <div class="ui pagination menu" style="box-shadow: none;">
          <a class="icon item" href="<?php echo "status.php?".$str2;?>" id="page_prev">
    Top
          </a>
          <?php
      if (isset($_GET['prevtop']))
      echo "<a class=\"item\" href=\"status.php?".$str2."&top=".intval($_GET['prevtop'])."\">Prev</a>";
      else
      echo "<a class=\"item\" href=\"status.php?".$str2."&top=".($top+20)."\">Prev</a>";

      ?>

          <a class="icon item" href="<?php echo "status.php?".$str2."&top=".$bottom."&prevtop=$top"; ?>" id="page_next">
            Next
          </a>
        </div>
  </div>
</div>

<script>
        var i = 0;
        var judge_result = [<?php
        foreach ($judge_result as $result) {
                echo "'$result',";
        } ?>
        ''];

        var judge_color = [<?php
        foreach ($judge_color as $result) {
                echo "'$result',";
        } ?>
        ''];
   var oj_mark='<?php echo $OJ_MARK ?>';
   var user_id="<?php if (isset($_SESSION[$OJ_NAME."_user_id"]) && $OJ_FANCY_RESULT ) echo $_SESSION[$OJ_NAME."_user_id"]; ?>";
   var fancy_mp3="<?php if (isset($_SESSION[$OJ_NAME."_user_id"]) && $OJ_FANCY_RESULT ) echo $OJ_FANCY_MP3; ?>";

</script>
        <script src="template/<?php echo $OJ_TEMPLATE?>/auto_refresh.js?v=0.522" ></script>

</div>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
