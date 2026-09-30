<?php $show_title="$MSG_PROBLEMS - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<style>
/* ===== 题库页 · 天空笔记本风格（与顶部菜单栏配色统一） ===== */
@import url('<?php echo $OJ_CDN_URL?>template/<?php echo $OJ_TEMPLATE?>/fonts/zcool-kuaile.css?v=1');

.ps-page {
    --ps-sky: #67a0d5;
    --ps-sky-deep: #3a6da8;
    --ps-sky-light: #78b0d9;
    --ps-cream: #fff8dd;
    --ps-gold: #f4cd75;
    --ps-gold-deep: #d9a841;
    --ps-coral: #e2726d;
    --ps-mint: #7FD4B5;
    --ps-mint-deep: #2E8B6F;
    --ps-text: #3d4f6b;
    --ps-text-soft: #7a8ba3;
    --ps-border: #3d4f6b;
    --ps-shadow: rgba(61, 79, 107, 0.12);
    --ps-radius: 16px;

    padding: 20px 16px 40px;
    max-width: 1200px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", "Helvetica Neue", sans-serif;
    color: var(--ps-text);
}

/* ===== 知识点标签云 ===== */
.ps-tag-cloud {
    background: linear-gradient(135deg, #FFF9EC 0%, #F0F6FC 100%);
    border: 2.5px solid var(--ps-border);
    border-radius: var(--ps-radius);
    padding: 18px 20px 16px;
    margin-bottom: 20px;
    box-shadow: 4px 4px 0 var(--ps-shadow);
    position: relative;
    overflow: hidden;
}
.ps-tag-cloud::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--ps-sky), var(--ps-gold), var(--ps-coral), var(--ps-mint));
}

.ps-tag-cloud-head {
    display: flex;
    align-items: center;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 8px;
}
.ps-tag-cloud-head > .tags.icon {
    color: var(--ps-sky-deep);
    font-size: 1.2em;
    margin: 0 !important;
}
.ps-tag-cloud-title {
    font-family: 'ZCOOL KuaiLe', sans-serif;
    font-size: 1.15rem;
    font-weight: 400;
    color: var(--ps-text);
    margin-left: 6px;
}
.ps-tag-cloud-hint {
    margin-left: auto;
    color: var(--ps-text-soft);
    font-size: 0.85em;
}

.ps-tag-cloud-body {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

/* 覆盖 Semantic UI label 的标签样式 */
.ps-tag-cloud-body .ui.label {
    font-size: 0.8rem !important;
    padding: 5px 13px !important;
    border-radius: 999px !important;
    border: 1.5px solid !important;
    border-width: 1.5px !important;
    font-weight: 600 !important;
    cursor: pointer;
    transition: transform 0.18s ease, box-shadow 0.18s ease !important;
    line-height: 1.4 !important;
}
.ps-tag-cloud-body .ui.label:hover {
    transform: translateY(-2px) scale(1.04);
    box-shadow: 2px 3px 0 var(--ps-shadow);
}

/* ===== 搜索 + 工具栏 ===== */
.ps-toolbar {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.ps-search-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.ps-search-form .ui.search,
.ps-id-form .ui.search {
    margin: 0 !important;
    height: auto !important;
}
.ps-search-form .ui.input input,
.ps-id-form .ui.input input {
    border: 2px solid #d6e2ef !important;
    border-radius: 999px !important;
    padding: 8px 16px !important;
    font-size: 0.9rem !important;
    color: var(--ps-text) !important;
    background: #fff !important;
    transition: all 0.2s !important;
    height: 38px !important;
}
.ps-search-form .ui.input input:focus,
.ps-id-form .ui.input input:focus {
    border-color: var(--ps-sky) !important;
    box-shadow: 0 0 0 3px rgba(103,160,213,0.15) !important;
}
.ps-search-form .ui.input input::placeholder,
.ps-id-form .ui.input input::placeholder {
    color: #b0bccc !important;
}
.ps-search-form .ui.left.icon.input > i.icon,
.ps-id-form .ui.icon.input > i.icon {
    color: var(--ps-sky) !important;
    opacity: 0.7;
}

.ps-tools {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

#show_tag {
    margin: 0 !important;
}
#show_tag label {
    color: var(--ps-text-soft) !important;
    font-size: 0.88rem !important;
}

.ps-all-tags-btn {
    background: linear-gradient(135deg, var(--ps-mint), var(--ps-mint-deep)) !important;
    color: #fff !important;
    border: 2px solid var(--ps-border) !important;
    box-shadow: 2px 2px 0 var(--ps-shadow) !important;
    border-radius: 999px !important;
    font-weight: 600 !important;
    transition: transform 0.18s, box-shadow 0.18s !important;
}
.ps-all-tags-btn:hover {
    transform: translateY(-2px) rotate(-1deg) !important;
    box-shadow: 3px 3px 0 var(--ps-shadow) !important;
}

/* ===== 翻页 ===== */
.ps-pagination-top,
.ps-pagination-bottom {
    text-align: center;
    margin-bottom: 24px;
}
.ps-pagination-bottom { margin-top: 12px; }

.ps-page .ui.pagination.menu {
    background: #fff !important;
    border: 2.5px solid var(--ps-border) !important;
    border-radius: 999px !important;
    box-shadow: 3px 3px 0 var(--ps-shadow) !important;
    padding: 3px !important;
    gap: 2px;
}
.ps-page .ui.pagination.menu .item {
    border-radius: 999px !important;
    color: var(--ps-text) !important;
    font-weight: 600 !important;
    font-size: 0.88rem !important;
    min-width: 38px;
    text-align: center;
    transition: all 0.18s ease !important;
    border: none !important;
    margin: 0 !important;
}
.ps-page .ui.pagination.menu .item:hover {
    background: #F0F6FC !important;
    color: var(--ps-sky-deep) !important;
}
.ps-page .ui.pagination.menu .item.active {
    background: linear-gradient(135deg, var(--ps-sky), var(--ps-sky-light)) !important;
    color: #fff !important;
    box-shadow: 0 2px 6px rgba(103,160,213,0.35) !important;
}
.ps-page .ui.pagination.menu .item.disabled {
    opacity: 0.4 !important;
    pointer-events: none !important;
}

/* ===== 题目表格 ===== */
.ps-table-wrap {
    background: #fff;
    border: 2.5px solid var(--ps-border);
    border-radius: var(--ps-radius);
    box-shadow: 4px 4px 0 var(--ps-shadow);
    overflow: hidden;
    margin-bottom: 8px;
}

.ps-table {
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
}

.ps-table thead th {
    background: linear-gradient(135deg, var(--ps-sky-deep), var(--ps-sky)) !important;
    color: var(--ps-cream) !important;
    font-family: 'ZCOOL KuaiLe', sans-serif !important;
    font-size: 1rem !important;
    font-weight: 400 !important;
    padding: 14px 12px !important;
    border-bottom: 2.5px solid var(--ps-border) !important;
    letter-spacing: 0.5px;
}
.ps-table thead th:first-child { border-radius: 0 !important; }

.ps-table tbody tr {
    transition: background 0.15s ease, transform 0.15s ease;
    border-bottom: 1.5px solid #eef2f8 !important;
}
.ps-table tbody tr:nth-child(even) {
    background: #FFFDF6;
}
.ps-table tbody tr:nth-child(odd) {
    background: #fff;
}
.ps-table tbody tr:hover {
    background: #F0F6FC !important;
}
.ps-table tbody tr:last-child {
    border-bottom: none !important;
}

.ps-table tbody td {
    padding: 12px 10px !important;
    color: var(--ps-text) !important;
    font-size: 0.9rem !important;
    vertical-align: middle !important;
}

.ps-table tbody td b {
    color: var(--ps-sky-deep);
    font-size: 1rem;
}

/* 状态图标圆形徽章 */
.ps-table .status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    font-size: 0.85rem;
}
.ps-table .status.accepted {
    background: #E8FBF3;
    color: var(--ps-mint-deep);
    border: 1.5px solid var(--ps-mint-deep);
}
.ps-table .status.wrong_answer {
    background: #FFF0EE;
    color: var(--ps-coral);
    border: 1.5px solid var(--ps-coral);
}

/* 题目标题链接 */
.ps-table td a[href^="problem.php"] {
    color: var(--ps-sky-deep) !important;
    font-weight: 600;
    transition: color 0.15s;
}
.ps-table td a[href^="problem.php"]:hover {
    color: var(--ps-coral) !important;
    text-decoration: none;
}

/* 未公开标签 */
.ps-table .ui.red.label {
    background: var(--ps-coral) !important;
    color: #fff !important;
    border-radius: 999px !important;
    font-size: 0.7rem !important;
    padding: 2px 8px !important;
    margin-left: 6px !important;
    border: 1px solid var(--ps-border) !important;
}

/* AC/Submit 链接 */
.ps-table td a[href^="status.php"] {
    color: var(--ps-text-soft) !important;
    font-weight: 600;
    font-size: 0.85rem;
}
.ps-table td a[href^="status.php"]:hover {
    color: var(--ps-sky-deep) !important;
}

/* 通过率进度条 */
.ps-table .progress {
    height: 18px !important;
    border-radius: 999px !important;
    background: #eef2f8 !important;
    overflow: hidden;
    margin-bottom: 0 !important;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);
}
.ps-table .progress-bar {
    height: 100% !important;
    border-radius: 999px !important;
    font-size: 0.68rem !important;
    line-height: 18px !important;
    color: #fff !important;
    font-weight: 700;
    text-align: center;
    transition: width 0.4s ease;
}
.ps-table .progress-bar-success {
    background: linear-gradient(90deg, var(--ps-mint), var(--ps-mint-deep)) !important;
}
.ps-table .progress-bar-danger {
    background: linear-gradient(90deg, var(--ps-coral), #c25d58) !important;
}
.ps-table .progress-bar-striped {
    background-image: linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent) !important;
    background-size: 1rem 1rem !important;
}

/* ===== 移动端响应式 ===== */
@media (max-width: 768px) {
    .ps-page {
        padding: 14px 10px 32px;
    }

    .ps-tag-cloud {
        padding: 14px 14px 12px;
    }
    .ps-tag-cloud-title { font-size: 1rem; }
    .ps-tag-cloud-hint {
        width: 100%;
        margin-left: 0;
        font-size: 0.78em;
    }
    .ps-tag-cloud-body .ui.label {
        font-size: 0.74rem !important;
        padding: 4px 10px !important;
    }

    .ps-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .ps-search-group {
        width: 100%;
    }
    .ps-search-form .ui.search,
    .ps-id-form .ui.search {
        width: 100% !important;
    }
    .ps-search-form { flex: 1; }
    .ps-id-form { width: 100px; flex-shrink: 0; }
    .ps-tools {
        justify-content: space-between;
        width: 100%;
    }
}

/* ===== 超窄屏：题目表格转卡片，横向信息条 + 全宽标题/进度 ===== */
@media (max-width: 640px) {
    .ps-table-wrap { overflow: visible; }
    .ps-table, .ps-table tbody { display: block !important; }
    .ps-table thead { display: none !important; }

    /* 每一行 = 一张横向信息卡，flex 换行排列 */
    .ps-table tbody tr {
        display: flex !important;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px 10px;
        padding: 13px 14px !important;
        border-bottom: 1.5px solid #eef2f8 !important;
        background: #fff;
    }
    .ps-table tbody tr:nth-child(even) { background: #FFFDF6; }
    .ps-table tbody tr:hover { background: #F0F6FC; }

    .ps-table tbody td {
        display: block !important;
        padding: 0 !important;
        border: none !important;
        font-size: 0.86rem !important;
        line-height: 1.4;
        text-align: left !important;
    }
    /* 移除上一版的伪字段名标签（状态/№/通过），卡片靠图标和 # 自解释 */
    .ps-table tbody td::before { content: none !important; }

    /* 状态徽章：圆形图标，不伸缩 */
    .ps-table tbody td .status { margin: 0; flex: 0 0 auto; }

    /* 题号加 # 前缀 */
    .ps-table tbody td b { font-size: 1rem; }
    .ps-table tbody td b::before {
        content: "#";
        color: var(--ps-text-soft);
        font-weight: 600;
        margin-right: 1px;
    }

    /* 标题单元格：链接与“未公开”内联排列，标签块自然换到下一行 */
    .ps-table .show_tag_controled {
        float: none !important;
        width: 100% !important;
        margin-top: 6px;
        text-align: left;
    }

    /* 通过率进度条全宽 */
    .ps-table .progress {
        width: 100% !important;
        height: 20px !important;
        margin-bottom: 0 !important;
    }

    /* === 已登录（DOM顺序：状态① ID② 标题③ AC/Submit④ 通过率⑤）===
       视觉顺序（全部靠左）：① 状态 ② #ID ④ AC/Submit
                 ③ 标题（整行）  ⑤ 通过率（整行） */
    .ps-has-status tbody td:nth-child(1) { order: 1; flex: 0 0 auto; }
    .ps-has-status tbody td:nth-child(2) { order: 2; flex: 0 0 auto; }
    .ps-has-status tbody td:nth-child(4) { order: 3; flex: 0 0 auto; }
    .ps-has-status tbody td:nth-child(3) { order: 4; flex: 0 0 100%; padding-top: 2px !important; }
    .ps-has-status tbody td:nth-child(5) { order: 5; flex: 0 0 100%; }

    /* === 未登录（DOM顺序：ID① 标题② AC/Submit③ 通过率④）===
       视觉顺序（全部靠左）：① #ID ③ AC/Submit  ② 标题（整行）  ④ 通过率（整行） */
    .ps-table:not(.ps-has-status) tbody td:nth-child(1) { order: 1; flex: 0 0 auto; }
    .ps-table:not(.ps-has-status) tbody td:nth-child(3) { order: 2; flex: 0 0 auto; }
    .ps-table:not(.ps-has-status) tbody td:nth-child(2) { order: 3; flex: 0 0 100%; padding-top: 2px !important; }
    .ps-table:not(.ps-has-status) tbody td:nth-child(4) { order: 4; flex: 0 0 100%; }
}

@media (max-width: 380px) {
    .ps-tag-cloud-body .ui.label {
        font-size: 0.7rem !important;
        padding: 3px 8px !important;
    }
}
</style>
<div class="padding ps-page">

  <!-- 知识点标签快捷搜索 -->
  <div class="ps-tag-cloud">
    <div class="ps-tag-cloud-head">
      <i class="tags icon"></i>
      <span class="ps-tag-cloud-title">知识点标签快速筛选</span>
      <span class="ps-tag-cloud-hint">点击标签搜索相关题目</span>
    </div>
    <div class="ps-tag-cloud-body">
      <?php
      // 常用算法和数据结构标签
      $common_tags = array(
        '入门' => '入门',
        '数组' => '数组',
        '字符串' => '字符串',
        '排序' => '排序',
        '查找' => '查找',
        '贪心' => '贪心',
        '动态规划' => '动态规划',
        'DP' => 'DP',
        '搜索' => '搜索',
        'DFS' => 'DFS',
        'BFS' => 'BFS',
        '图论' => '图论',
        '最短路' => '最短路',
        '最小生成树' => '最小生成树',
        '树' => '树',
        '链表' => '链表',
        '栈' => '栈',
        '队列' => '队列',
        '哈希' => '哈希',
        '二分' => '二分',
        '数学' => '数学',
        '数论' => '数论',
        '几何' => '几何',
        '模拟' => '模拟',
        '暴力' => '暴力'
      );
      $tag_colors = array(
        'red', 'orange', 'yellow', 'olive', 'green', 'teal', 'blue', 'violet', 'purple', 'pink', 'brown', 'grey'
      );
      $color_idx = 0;
      foreach ($common_tags as $tag_display => $tag_search) {
        $color = $tag_colors[$color_idx % count($tag_colors)];
        $color_idx++;
        ?>
        <a href="problemset.php?search=<?php echo urlencode($tag_search); ?>"
           class="ui tiny label"
           style="background: <?php echo $color; ?>15; color: <?php echo $color; ?>; border: 1px solid <?php echo $color; ?>30; padding: 6px 12px; cursor: pointer; transition: all 0.2s;"
           onmouseover="this.style.background='<?php echo $color; ?>25'; this.style.transform='scale(1.05)';"
           onmouseout="this.style.background='<?php echo $color; ?>15'; this.style.transform='scale(1)';">
          <?php echo htmlentities($tag_display, ENT_QUOTES, 'UTF-8'); ?>
        </a>
      <?php } ?>
    </div>
  </div>

  <!-- 搜索 + 工具栏 -->
  <div class="ps-toolbar">
    <div class="ps-search-group">
      <form action="" method="get" class="ps-search-form">
        <div class="ui search" style="width: 280px;">
          <div class="ui left icon input" style="width: 100%;">
            <input class="prompt" style="width: 100%;" type="text" placeholder="搜索题目/知识点/来源…" name="search"
             value="<?php if(isset($_GET['search'])) echo htmlentities($_GET['search'], ENT_QUOTES, 'UTF-8'); else if(isset($_GET['source'])) echo htmlentities($_GET['source'], ENT_QUOTES, 'UTF-8'); ?>"
            >
            <i class="search icon"></i>
          </div>
          <div class="results" style="width: 100%;"></div>
        </div>
      </form>

      <form action="problem.php" method="get" class="ps-id-form">
        <div class="ui search" style="width: 120px;">
          <div class="ui icon input" style="width: 100%;">
            <input class="prompt" style="width: 100%;" type="text" value="" placeholder="ID" name="id">
            <i class="search icon"></i>
          </div>
          <div class="results" style="width: 100%;"></div>
        </div>
      </form>
    </div>

    <div class="ps-tools">
      <div class="ui toggle checkbox" id="show_tag">
        <style id="show_tag_style"></style>
        <script>
        if (localStorage.getItem('show_tag') != '0') {
          document.write('<input type="checkbox" checked>');
          document.getElementById('show_tag_style').innerHTML = '.show_tag_controled { white-space: nowrap; overflow: hidden; }';
        } else {
          document.write('<input type="checkbox">');
          document.getElementById('show_tag_style').innerHTML = '.show_tag_controled { width: 0; white-space: nowrap; overflow: hidden; }';
        }
        </script>

        <script>
        $(function () {
          $('#show_tag').checkbox('setting', 'onChange', function () {
            let checked = $('#show_tag').checkbox('is checked');
            localStorage.setItem('show_tag', checked ? '1' : '0');
            if (checked) {
              document.getElementById('show_tag_style').innerHTML = '.show_tag_controled { white-space: nowrap; overflow: hidden; }';
            } else {
              document.getElementById('show_tag_style').innerHTML = '.show_tag_controled { width: 0; white-space: nowrap; overflow: hidden; }';
            }
          });
        });
        </script>
        <label><?php echo $MSG_SHOW_TAGS;?></label>

      </div>
      <div style="display: inline-block;">
        <a href="category.php" class="ui labeled icon mini green button ps-all-tags-btn"><i class="plus icon"></i> <?php echo $MSG_SHOW_ALL_TAGS;?></a>
      </div>
    </div>
  </div>

<?php if (!isset($_GET['list'])){ ?>

  <div class="ps-pagination-top">

    <?php
      if(!isset($page)) $page=1;
      $page=intval($page);
      $section=8;
      $start=$page>$section?$page-$section:1;
      $end=$page+$section>$view_total_page?$view_total_page:$page+$section;
    ?>
  <div style="text-align: center;">
  <div class="ui pagination menu" style="box-shadow: none;">
    <a href="problemset.php?page=1" class="icon item">
      <i class="fast backward icon"></i>
    </a>
    <a class="<?php if($page==1) echo "disabled "; ?>icon item" href="<?php if($page<>1) echo "problemset.php?page=".($page-1).htmlentities($postfix,ENT_QUOTES,'UTF-8'); ?>" id="page_prev">
      <i class="left chevron icon"></i>
    </a>
    <?php
      for ($i=$start;$i<=$end;$i++){
        echo "<a class=\"".($page==$i?"active ":"")."item\" href=\"problemset.php?page=".$i.htmlentities($postfix,ENT_QUOTES,'UTF-8')."\">".$i."</a>";
      }
    ?>
    <a class="<?php if($page==$view_total_page) echo "disabled "; ?> icon item" href="<?php if($page<>$view_total_page) echo "problemset.php?page=".($page+1).htmlentities($postfix,ENT_QUOTES,'UTF-8'); ?>" id="page_next">
    <i class="right chevron icon"></i>
    </a>
    <a href="problemset.php?page=<?php echo $view_total_page?>" class="icon item">
      <i class="fast forward icon"></i>
    </a>
  </div>
</div>
  </div>
<?php } ?>


  <div class="ps-table-wrap">
  <table class="ui very basic center aligned table ps-table <?php if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])) echo 'ps-has-status'; ?>">
    <thead>
      <tr>

        <?php if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])){?>
          <th class="one wide"><?php echo $MSG_STATUS?></th>
        <?php } ?>
        <th class="one wide"><?php echo $MSG_PROBLEM_ID?></th>
        <th class="left aligned"><?php echo $MSG_TITLE?></th>
        <th class="one wide"><?php echo $MSG_SOVLED."/".$MSG_SUBMIT?></th>

        <th class="one wide"><?php echo $MSG_PASS_RATE?></th>
      </tr>
    </thead>
    <tbody>
    <?php
          $color=array("blue","teal","orange","pink","olive","red","yellow","green","purple");
          $tcolor=0;
          $i=0;
          foreach ($result as $row){
		echo "<tr>";
            if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])){

              if (isset($sub_arr[$row['problem_id']])){
                if (isset($acc_arr[$row['problem_id']]))
                  echo "<td><span class=\"status accepted\"><i class=\"checkmark icon\"></i></span></td>";
                else
                  echo "<td><span class=\"status wrong_answer\"><i class=\"remove icon\"></i></span></td>";
              }else{
                echo "<td><span class=\"status\"><i class=\"icon\"></i></span></td>";
              }
            }

             echo  "<td><b>".$row['problem_id']."</b></td>";
             echo "<td class=\"left aligned\">";
             echo "<a style=\"vertical-align: middle; \" href=\"problem.php?id=".$row['problem_id']."\">";
             echo $row['title'];
             echo "</a>";
             if($row['defunct']=='Y')
              {echo "<a href=admin/problem_df_change.php?id=".$row['problem_id']."&getkey=".$_SESSION[$OJ_NAME.'_'.'getkey'].">".("<span class=\"ui tiny red label\">未公开</span>")."</a>";}

              echo "<div class=\"show_tag_controled\" style=\"float: right; \">";
              //echo "<span class=\"ui header\">";
              echo  $view_problemset[$i][3];
              //echo "</span></div>";
	      echo "</div>";
            echo "</td>";
          echo "<td><a href=\"status.php?problem_id=".$row['problem_id']."&jresult=4\">".$row['accepted']."/".$row['submit']."</a></td>";
           // echo "<td><a href='status.php?problem_id=".$row['problem_id']."'>".$row['submit']."</a></td>";
            if ($row['submit'] == 0) {
    echo '<td><div class="progress" style="margin-bottom:-20px; "><div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%;">0.000%</div></div></td>';
} else {
    $percentage = round(100 * $row['accepted'] / $row['submit'], 3);
    echo '<td><div class="progress" style="margin-bottom:-20px;"><div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="'.$percentage.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%;">'.$percentage.'%</div></div></td>';
}
            echo  "</tr>";
            $i++;
          }
        ?>



    </tbody>
  </table>
  </div>
  <br>
<?php if (!isset($_GET['list'])){ ?>
  <div class="ps-pagination-bottom">

    <?php
      if(!isset($page)) $page=1;
      $page=intval($page);
      $section=8;
      $start=$page>$section?$page-$section:1;
      $end=$page+$section>$view_total_page?$view_total_page:$page+$section;
    ?>
<div style="text-align: center;">
  <div class="ui pagination menu" style="box-shadow: none;">
    <a class="<?php if($page==1) echo "disabled "; ?>icon item" href="<?php if($page<>1) echo "problemset.php?page=".($page-1).htmlentities($postfix,ENT_QUOTES,'UTF-8'); ?>" id="page_prev">
      <i class="left chevron icon"></i>
    </a>
    <?php
      for ($i=$start;$i<=$end;$i++){
        echo "<a class=\"".($page==$i?"active ":"")."item\" href=\"problemset.php?page=".$i.htmlentities($postfix,ENT_QUOTES,'UTF-8')."\">".$i."</a>";
      }
    ?>
    <a class="<?php if($page==$view_total_page) echo "disabled "; ?> icon item" href="<?php if($page<>$view_total_page) echo "problemset.php?page=".($page+1).htmlentities($postfix,ENT_QUOTES,'UTF-8'); ?>" id="page_next">
    <i class="right chevron icon"></i>
    </a>
  </div>
</div>
  </div>
<?php } ?>
<script type="text/javascript" src="include/jquery.tablesorter.js"></script>

</div>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
