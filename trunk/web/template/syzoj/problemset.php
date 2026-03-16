<?php $show_title="$MSG_PROBLEMS - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">

  <!-- 知识点标签快捷搜索 -->
  <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 12px;">
    <div style="display: flex; align-items: center; margin-bottom: 12px;">
      <i class="tags icon" style="color: #667eea; font-size: 1.2em;"></i>
      <span style="font-weight: 600; color: #333; margin-left: 8px;">知识点标签快速筛选</span>
      <span style="margin-left: auto; color: #888; font-size: 0.9em;">点击标签搜索相关题目</span>
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
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

  <div class="ui grid" style="margin-bottom: 10px; ">
    <div class="row" style="white-space: nowrap; ">
      <div class="seven wide column">
          <form action="" method="get">
            <div class="ui search" style="width: 280px; height: 28px; margin-top: -5.3px;float:left ">
              <div class="ui left icon input" style="width: 100%; ">
                <input class="prompt" style="width: 100%; " type="text" placeholder="搜索题目/知识点/来源…" name="search"
		       value="<?php if(isset($_GET['search']))echo htmlentities($_GET['search'],ENT_QUOTES,'UTF-8') ?>"
                >

                <i class="search icon"></i>
              </div>
              <div class="results" style="width: 100%; "></div>
            </div>
          </form>

          <form action="problem.php" method="get">
            <div class="ui search" style="width: 120px; height: 28px; margin-top: -5.3px; ">
              <div class="ui icon input" style="width: 100%; ">
                <input class="prompt" style="width: 100%; " type="text" value="" placeholder="ID" name="id">
                <i class="search icon"></i>
              </div>
              <div class="results" style="width: 100%; "></div>
            </div>
          </form>

      </div>


      <div class="nine wide right aligned column">
     
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
        <div style="margin-left: 10px; display: inline-block; ">
               <a style="margin-left: 10px; " href="category.php" class="ui labeled icon mini green button"><i class="plus icon"></i> <?php echo $MSG_SHOW_ALL_TAGS;?></a>
          
        </div>

      </div>
    </div>
  </div>

<?php if (!isset($_GET['list'])){ ?>

<div style="margin-bottom: 30px; ">
    
    <?php
      if(!isset($page)) $page=1;
      $page=intval($page);
      $section=8;
      $start=$page>$section?$page-$section:1;
      $end=$page+$section>$view_total_page?$view_total_page:$page+$section;
    ?>
<div style="text-align: center; ">
  <div class="ui pagination menu" style="box-shadow: none; ">
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


  <table class="ui very basic center aligned table">
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
  </table><br>
<?php if (!isset($_GET['list'])){ ?>
  <div style="margin-bottom: 30px; ">
    
    <?php
      if(!isset($page)) $page=1;
      $page=intval($page);
      $section=8;
      $start=$page>$section?$page-$section:1;
      $end=$page+$section>$view_total_page?$view_total_page:$page+$section;
    ?>
<div style="text-align: center; ">
  <div class="ui pagination menu" style="box-shadow: none; ">
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
<?php } ?>
<script type="text/javascript" src="include/jquery.tablesorter.js"></script>

</div>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
   
