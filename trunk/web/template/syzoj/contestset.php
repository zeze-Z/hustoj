<?php $show_title="$MSG_CONTEST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">
<div class="ui grid" style="margin-bottom: 10px; ">
    <div class="row" style="white-space: nowrap; ">
      <div class="seven wide column">
          <form method=get action=contest.php>
            <div class="ui action input" style="width: 520px; max-width: 100%; margin-top: -5.3px;">
              <input class="prompt" type="text" value="<?php echo isset($keyword_text) ? htmlentities($keyword_text, ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="<?php echo $MSG_CONTEST_NAME ?> …" name="keyword">
              <?php if(isset($_GET['my'])){ ?>
                <input type="hidden" name="my" value="1">
              <?php } ?>
              <button class="ui primary button" type="submit"><i class="search icon"></i><?php echo $MSG_SEARCH?></button>
            </div>
          </form>

      </div>

      <div class="nine wide right aligned column">

      </div>
    </div>
  </div>

      <div style="margin-bottom: 30px; ">
    
    <?php
      if(!isset($page)) $page=1;
      $page=intval($page);
      $section=8;
      $start=$page>$section?$page-$section:1;
      $end=$page+$section>$view_total_page?$view_total_page:$page+$section;
      $MY=isset($_GET['my'])?"&my":"";
    ?>
<div style="text-align: center; ">
  <div class="ui pagination menu" style="box-shadow: none; ">
    <a class="<?php if($page==1) echo "disabled "; ?>icon item" href="<?php if($page<>1) echo "contest.php?page=".strval($page-1).$MY ?>" id="page_prev">
      <i class="left chevron icon"></i>
    </a>
    <?php
      for ($i=$start;$i<=$end;$i++){
        echo "<a class=\"".($page==$i?"active ":"")."item\" href=\"contest.php?page=".$i.$MY."\">".$i."</a>";
      }
    ?>
    <a class="<?php if($page==$view_total_page) echo "disabled "; ?> icon item" href="<?php if($page<>$view_total_page) echo "contest.php?page=".strval($page+1).$MY; ?>" id="page_next">
    <i class="right chevron icon"></i>
    </a>
  </div>
</div>

</div>
    <table class="ui very basic center aligned table">
      <thead>
        <tr>
          <th><?php echo $MSG_CONTEST_ID?></th>
          <th><?php echo $MSG_CONTEST_NAME?></th>
          <th><?php echo $MSG_TIME?></th>
          <th><?php echo $MSG_CONTEST_OPEN?></th>
          <th><?php echo $MSG_CONTEST_CREATOR?></th>
        </tr>
      </thead>
      <tbody>
          <?php
            foreach($view_contest as $row){
              echo "<tr>";
              foreach($row as $table_cell){
                echo "<td>";
                echo "\t".$table_cell;
                echo "</td>";
              }
              echo "</tr>";
            }
          ?>
      </tbody>
    </table>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
