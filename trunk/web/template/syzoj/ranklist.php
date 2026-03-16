<?php $show_title="$MSG_RANKLIST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<style>
/* 排行榜样式 */
.ranklist-page {
    padding: 20px 0;
}

/* 前三名领奖台样式 */
.podium-section {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 40px;
    padding: 30px 0;
}

.podium-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.podium-item:hover {
    transform: translateY(-5px);
}

.podium-rank {
    font-size: 3em;
    font-weight: bold;
    margin-bottom: 10px;
}

.podium-rank.gold {
    color: #FFD700;
    text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);
}

.podium-rank.silver {
    color: #C0C0C0;
    text-shadow: 0 2px 4px rgba(192, 192, 192, 0.3);
}

.podium-rank.bronze {
    color: #CD7F32;
    text-shadow: 0 2px 4px rgba(205, 127, 50, 0.3);
}

.podium-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2em;
    font-weight: bold;
    margin-bottom: 10px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.podium-item.gold .podium-avatar {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
}

.podium-item.silver .podium-avatar {
    background: linear-gradient(135deg, #C0C0C0 0%, #A8A8A8 100%);
    box-shadow: 0 4px 15px rgba(192, 192, 192, 0.4);
}

.podium-item.bronze .podium-avatar {
    background: linear-gradient(135deg, #CD7F32 0%, #B87333 100%);
    box-shadow: 0 4px 15px rgba(205, 127, 50, 0.4);
}

.podium-user {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.podium-nick {
    color: #666;
    font-size: 0.9em;
    margin-bottom: 5px;
}

.podium-solved {
    color: #27ae60;
    font-weight: 600;
    font-size: 1.1em;
}

.podium-platform {
    width: 120px;
    margin-top: 10px;
    border-radius: 8px 8px 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.5em;
}

.podium-platform.gold {
    height: 80px;
    background: linear-gradient(180deg, #FFD700 0%, #FFA500 100%);
}

.podium-platform.silver {
    height: 60px;
    background: linear-gradient(180deg, #C0C0C0 0%, #A8A8A8 100%);
}

.podium-platform.bronze {
    height: 45px;
    background: linear-gradient(180deg, #CD7F32 0%, #B87333 100%);
}

/* 进度条样式 */
.progress-bar-container {
    width: 100%;
    height: 8px;
    background: #e8e8e8;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 5px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
    transition: width 0.5s ease;
}

.progress-bar.high {
    background: linear-gradient(90deg, #27ae60 0%, #2ecc71 100%);
}

.progress-bar.medium {
    background: linear-gradient(90deg, #f39c12 0%, #e67e22 100%);
}

.progress-bar.low {
    background: linear-gradient(90deg, #e74c3c 0%, #c0392b 100%);
}

/* 表格行优化 */
.rank-row {
    transition: background 0.2s ease;
}

.rank-row:hover {
    background: #f8f9fa !important;
}

/* 排名徽章 */
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.95em;
}

.rank-badge.gold {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.4);
}

.rank-badge.silver {
    background: linear-gradient(135deg, #C0C0C0 0%, #A8A8A8 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(192, 192, 192, 0.4);
}

.rank-badge.bronze {
    background: linear-gradient(135deg, #CD7F32 0%, #B87333 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(205, 127, 50, 0.4);
}

.rank-badge.normal {
    background: #f0f0f0;
    color: #666;
}

/* 响应式调整 */
@media (max-width: 768px) {
    .podium-section {
        gap: 10px;
    }

    .podium-avatar {
        width: 60px;
        height: 60px;
        font-size: 1.5em;
    }

    .podium-platform {
        width: 80px;
    }

    .podium-platform.gold {
        height: 60px;
    }

    .podium-platform.silver {
        height: 45px;
    }

    .podium-platform.bronze {
        height: 35px;
    }
}
</style>

<div class="padding">
    <div class="ranklist-page">

    <!-- 前三名领奖台（仅在第一页显示） -->
    <?php if ($rank == 0 && $rows_cnt >= 3) { ?>
    <div class="podium-section">
        <!-- 第二名 -->
        <?php if ($rows_cnt >= 2) {
            $row2 = $result[1];
            $user_initial = mb_substr($row2['user_id'], 0, 1, 'UTF-8');
        ?>
        <a href="userinfo.php?user=<?php echo htmlentities($row2['user_id'], ENT_QUOTES, 'UTF-8'); ?>" class="podium-item silver">
            <div class="podium-rank silver">2</div>
            <div class="podium-avatar"><?php echo $user_initial; ?></div>
            <div class="podium-user"><?php echo htmlentities($row2['user_id'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="podium-nick"><?php echo htmlentities($row2['nick'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="podium-solved"><i class="checkmark icon"></i> <?php echo $row2['solved']; ?></div>
            <div class="podium-platform silver">2</div>
        </a>
        <?php } ?>

        <!-- 第一名 -->
        <?php if ($rows_cnt >= 1) {
            $row1 = $result[0];
            $user_initial = mb_substr($row1['user_id'], 0, 1, 'UTF-8');
        ?>
        <a href="userinfo.php?user=<?php echo htmlentities($row1['user_id'], ENT_QUOTES, 'UTF-8'); ?>" class="podium-item gold">
            <div class="podium-rank gold">1</div>
            <div class="podium-avatar"><?php echo $user_initial; ?></div>
            <div class="podium-user"><?php echo htmlentities($row1['user_id'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="podium-nick"><?php echo htmlentities($row1['nick'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="podium-solved"><i class="checkmark icon"></i> <?php echo $row1['solved']; ?></div>
            <div class="podium-platform gold">1</div>
        </a>
        <?php } ?>

        <!-- 第三名 -->
        <?php if ($rows_cnt >= 3) {
            $row3 = $result[2];
            $user_initial = mb_substr($row3['user_id'], 0, 1, 'UTF-8');
        ?>
        <a href="userinfo.php?user=<?php echo htmlentities($row3['user_id'], ENT_QUOTES, 'UTF-8'); ?>" class="podium-item bronze">
            <div class="podium-rank bronze">3</div>
            <div class="podium-avatar"><?php echo $user_initial; ?></div>
            <div class="podium-user"><?php echo htmlentities($row3['user_id'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="podium-nick"><?php echo htmlentities($row3['nick'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="podium-solved"><i class="checkmark icon"></i> <?php echo $row3['solved']; ?></div>
            <div class="podium-platform bronze">3</div>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

	<!-- 时间范围和搜索 -->
	<div style="margin-bottom: 20px;">
		<div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
			<div style="display: flex; gap: 8px;">
				<a href="ranklist.php?scope=d" class="ui mini button <?php echo $scope=='d'?'primary':''; ?>"><?php echo $MSG_DAY?></a>
				<a href="ranklist.php?scope=w" class="ui mini button <?php echo $scope=='w'?'primary':''; ?>"><?php echo $MSG_WEEK?></a>
				<a href="ranklist.php?scope=m" class="ui mini button <?php echo $scope=='m'?'primary':''; ?>"><?php echo $MSG_MONTH?></a>
				<a href="ranklist.php?scope=y" class="ui mini button <?php echo $scope=='y'?'primary':''; ?>"><?php echo $MSG_YEAR?></a>
			</div>
			<div style="margin-left: auto; display: flex; gap: 10px; flex-wrap: wrap;">
			  <form action="ranklist.php" class="ui mini form" method="get" role="form">
				<div class="ui action left icon input inline" style="width: 180px;">
				  <i class="search icon"></i><input name="prefix" placeholder="<?php echo $MSG_USER?>" type="text" value="<?php echo htmlentities(isset($_GET['prefix'])?$_GET['prefix']:"",ENT_QUOTES,"utf-8") ?>">
				  <button class="ui mini button" type="submit"><?php echo $MSG_SEARCH?></button>
				</div>
			  </form>
			   <form action="ranklist.php" class="ui mini form" method="get" role="form">
				  <div class="ui action left icon input inline" style="width: 180px;">
					<i class="search icon"></i><input name="group_name" placeholder="<?php echo $MSG_GROUP_NAME ?>" type="text" value="<?php echo htmlentities(isset($_GET['group_name']) ? $_GET['group_name'] : "", ENT_QUOTES, "utf-8") ?>">
					<button class="ui mini button" type="submit"><?php echo $MSG_SEARCH ?></button>
				  </div>
				</form>
			</div>
		</div>
	</div>

    <!-- 排行榜表格 -->
    <div class="ui segment" style="border-radius: 12px; border: 1px solid #e8e8e8;">
	    <table class="ui very basic center aligned table" style="table-layout: fixed;">
	        <thead>
	        <tr>
	            <th style="width: 80px;"><?php echo $MSG_Number?></th>
	            <th style="width: 160px;"><?php echo $MSG_USER?></th>
	            <th><?php echo $MSG_NICK?></th>
				<th style="width: 120px;"><?php echo $MSG_GROUP_NAME?></th>
                <th style="width: 140px;"><?php echo $MSG_SOVLED?></th>
                <th style="width: 100px;"><?php echo $MSG_SUBMIT?></th>
                <th style="width: 140px;"><?php echo $MSG_RATIO?></th>
	        </tr>
	        </thead>
	        <tbody>
          <?php
          // 获取第一名的解题数作为进度条基准
          $max_solved = 0;
          if ($rows_cnt > 0) {
              $max_solved = max(array_column($result, 'solved'));
              if ($max_solved == 0) $max_solved = 1;
          }

          foreach($view_rank as $idx => $row){
              $current_rank = $rank - $rows_cnt + $idx + 1;
              $rank_class = '';
              if ($current_rank == 1) $rank_class = 'gold';
              else if ($current_rank == 2) $rank_class = 'silver';
              else if ($current_rank == 3) $rank_class = 'bronze';
              else $rank_class = 'normal';

              // 获取解题数计算进度条
              $solved_num = intval($result[$idx]['solved'] ?? 0);
              $submit_num = intval($result[$idx]['submit'] ?? 0);
              $progress_percent = $max_solved > 0 ? ($solved_num / $max_solved * 100) : 0;
              $progress_class = 'high';
              if ($progress_percent < 30) $progress_class = 'low';
              else if ($progress_percent < 60) $progress_class = 'medium';

              // 计算通过率
              $ratio_class = 'high';
              if ($submit_num > 0) {
                  $ratio = $solved_num / $submit_num;
                  if ($ratio < 0.3) $ratio_class = 'low';
                  else if ($ratio < 0.6) $ratio_class = 'medium';
              }
          ?>
	        <tr class="rank-row">
	            <td>
                    <span class="rank-badge <?php echo $rank_class; ?>">
                        <?php echo $current_rank; ?>
                    </span>
                </td>
	            <td>
                    <div style="text-align: left; padding-left: 10px;">
                        <?php echo $row[1]; ?>
                    </div>
                </td>
	            <td>
                    <div style="text-align: left;">
                        <?php echo $row[2]; ?>
                    </div>
                </td>
				<td><?php echo $row[3]; ?></td>
                <td>
                    <div style="text-align: left; padding: 0 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #27ae60;"><?php echo $row[4]; ?></span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar <?php echo $progress_class; ?>" style="width: <?php echo $progress_percent; ?>%;"></div>
                        </div>
                    </div>
                </td>
                <td><?php echo $row[5]; ?></td>
                <td>
                    <span style="color: <?php echo $ratio_class == 'high' ? '#27ae60' : ($ratio_class == 'medium' ? '#f39c12' : '#e74c3c'); ?>; font-weight: 600;">
                        <?php echo $row[6]; ?>
                    </span>
                </td>
	        </tr>
          <?php
          }
          ?>
	        </tbody>
	    </table>
    </div>
    <br>
    <div style="margin-bottom: 30px; ">

  <div style="text-align: center; ">
	<div class="ui pagination" style="box-shadow: none; ">
    <?php
    for($i = 0; $i <$view_total ; $i += $page_size) {
    $str= "<a class=\"ui button\" href='./ranklist.php?start=" . strval ( $i ).($scope?"&scope=$scope":"") . "'>";
    $str.= strval ( $i + 1 );
    $str.= "-";
    $str.= strval ( $i + $page_size );
    $str.= "</a>";
    echo $str;
    }
    ?>
	</div>
  </div>
</div>
</div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
