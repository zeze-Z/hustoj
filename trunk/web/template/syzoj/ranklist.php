<?php $show_title="$MSG_RANKLIST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<style>
/* ===== 排行榜 · 糖果贴纸盒风格（与顶栏天空蓝统一） ===== */
@import url('fonts/zcool-kuaile.css');

.ranklist-page {
    --sky: #67a0d5;
    --sky-deep: #3a6da8;
    --sky-light: #78b0d9;
    --gold: #f4cd75;
    --gold-deep: #d9a441;
    --silver: #c9d2dc;
    --silver-deep: #9aa6b4;
    --bronze: #d89568;
    --bronze-deep: #a86a3d;
    --peach: #e2726d;
    --mint: #7FD4B5;
    --mint-deep: #2E8B6F;
    --cream: #FFF9EC;
    --text: #3d4f6b;
    --text-soft: #7a8ba3;
    --border: #3d4f6b;
    --shadow: rgba(61, 79, 107, 0.18);
    --radius: 18px;

    padding: 24px 16px 48px;
    max-width: 1240px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", "Helvetica Neue", sans-serif;
    color: var(--text);
    background: var(--cream);
    background-image:
        radial-gradient(circle at 1px 1px, rgba(103, 160, 213, 0.18) 1.5px, transparent 0),
        radial-gradient(circle at 1px 1px, rgba(244, 205, 117, 0.16) 1.5px, transparent 0);
    background-size: 28px 28px, 28px 28px;
    background-position: 0 0, 14px 14px;
    border-radius: 28px;
    border: 2.5px solid var(--border);
    box-shadow: 6px 6px 0 var(--shadow);
    position: relative;
}

/* 页头标题 */
.ranklist-title {
    font-family: 'ZCOOL KuaiLe', -apple-system, sans-serif;
    font-size: 2.4rem;
    color: var(--sky-deep);
    text-align: center;
    margin: 4px 0 6px;
    letter-spacing: 1.5px;
    text-shadow: 3px 3px 0 #fff, 5px 5px 0 rgba(103, 160, 213, 0.28);
    font-weight: 400;
}
.ranklist-title::before, .ranklist-title::after {
    content: '✦';
    color: var(--gold);
    font-size: 1.3rem;
    margin: 0 12px;
    vertical-align: middle;
    animation: rank-twinkle 2s ease-in-out infinite;
    text-shadow: none;
}
.ranklist-title::after { animation-delay: 1s; }
@keyframes rank-twinkle {
    0%, 100% { opacity: 0.6; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.25); }
}

/* ===== 前三名领奖台 ===== */
.podium-section {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 18px;
    margin: 28px 0 36px;
    padding: 10px 0 0;
    flex-wrap: wrap;
}
.podium-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    transition: transform 0.25s ease;
    color: inherit;
}
.podium-item:hover { transform: translateY(-5px); }

.podium-avatar {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 2em;
    font-weight: bold;
    margin-bottom: 10px;
    border: 3px solid var(--border);
    box-shadow: 3px 3px 0 var(--shadow);
}
.podium-item.gold .podium-avatar {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
}
.podium-item.silver .podium-avatar {
    background: linear-gradient(135deg, var(--silver) 0%, var(--silver-deep) 100%);
}
.podium-item.bronze .podium-avatar {
    background: linear-gradient(135deg, var(--bronze) 0%, var(--bronze-deep) 100%);
}

.podium-rank-badge {
    font-family: 'ZCOOL KuaiLe', sans-serif;
    font-size: 2.4em;
    font-weight: 400;
    line-height: 1;
    margin-bottom: 6px;
    text-shadow: 2px 2px 0 #fff;
}
.podium-item.gold .podium-rank-badge { color: var(--gold-deep); }
.podium-item.silver .podium-rank-badge { color: var(--silver-deep); }
.podium-item.bronze .podium-rank-badge { color: var(--bronze-deep); }

.podium-user {
    font-weight: 700;
    color: var(--text);
    margin-bottom: 2px;
    font-size: 1.05em;
    word-break: break-all;
    max-width: 130px;
    text-align: center;
}
.podium-nick {
    color: var(--text-soft);
    font-size: 0.85em;
    margin-bottom: 6px;
    max-width: 130px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.podium-solved {
    color: var(--mint-deep);
    font-weight: 700;
    font-size: 1.05em;
    background: #E8FBF3;
    padding: 3px 12px;
    border-radius: 999px;
    border: 2px solid var(--mint-deep);
}

.podium-platform {
    width: 120px;
    margin-top: 12px;
    border-radius: 14px 14px 6px 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: bold;
    font-size: 1.6em;
    border: 3px solid var(--border);
    border-bottom: none;
    box-shadow: 3px 0 0 var(--shadow);
    font-family: 'ZCOOL KuaiLe', sans-serif;
}
.podium-platform.gold {
    height: 82px;
    background: linear-gradient(180deg, var(--gold) 0%, var(--gold-deep) 100%);
}
.podium-platform.silver {
    height: 62px;
    background: linear-gradient(180deg, var(--silver) 0%, var(--silver-deep) 100%);
}
.podium-platform.bronze {
    height: 46px;
    background: linear-gradient(180deg, var(--bronze) 0%, var(--bronze-deep) 100%);
}

/* ===== 时间范围胶囊 + 搜索区 ===== */
.ranklist-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    margin-bottom: 24px;
    padding: 14px 16px;
    background: #fff;
    border: 2.5px solid var(--border);
    border-radius: var(--radius);
    box-shadow: 4px 4px 0 var(--shadow);
}
.scope-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.scope-pill {
    padding: 8px 18px;
    border-radius: 999px;
    background: #fff;
    border: 2px solid var(--border);
    color: var(--text);
    font-weight: 600;
    font-size: 0.92rem;
    text-decoration: none;
    box-shadow: 2px 2px 0 var(--shadow);
    transition: all 0.2s ease;
    white-space: nowrap;
}
.scope-pill:hover {
    transform: translateY(-1px);
    background: #D4F0E4;
    color: var(--mint-deep);
    box-shadow: 3px 3px 0 var(--shadow);
}
.scope-pill.active {
    background: linear-gradient(135deg, var(--sky) 0%, var(--sky-light) 100%);
    color: #fff;
    border-color: var(--sky-deep);
    box-shadow: 2px 2px 0 var(--sky-deep);
    font-weight: 700;
}

.ranklist-search {
    margin-left: auto;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    min-width: 0;
}
/* 每个搜索表单作为独立 flex 项，避免互相挤压重叠 */
.ranklist-search > form {
    flex: 0 0 auto;
    min-width: 0;
    margin: 0;
}
.ranklist-search .ui.action.input {
    width: 200px !important;
    max-width: 100%;
    box-sizing: border-box;
    display: inline-flex !important;
    overflow: hidden;
}
.ranklist-search .ui.action.input input {
    border-radius: 999px 0 0 999px !important;
    border: 2px solid var(--border) !important;
    border-right: none !important;
    box-sizing: border-box;
    /* 关键：覆盖 Semantic UI 的 width:100%，改为 flex 伸缩，为按钮留出空间 */
    width: auto !important;
    flex: 1 1 0% !important;
    min-width: 0;
}
.ranklist-search .ui.action.input .button {
    border-radius: 0 999px 999px 0 !important;
    background: linear-gradient(135deg, var(--sky) 0%, var(--sky-light) 100%) !important;
    color: #fff !important;
    border: 2px solid var(--border) !important;
    font-weight: 600;
    flex: 0 0 auto !important;
    margin: 0 !important;
}

/* ===== 排行榜表格卡片 ===== */
.ranklist-card {
    background: #fff;
    border: 2.5px solid var(--border);
    border-radius: var(--radius);
    box-shadow: 5px 5px 0 var(--shadow);
    overflow: hidden;
}
.ranklist-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.ranklist-table thead th {
    background: linear-gradient(135deg, var(--sky) 0%, var(--sky-light) 100%);
    color: #fff;
    padding: 14px 10px;
    font-weight: 700;
    font-size: 0.95rem;
    text-align: center;
    border-bottom: 3px solid var(--border);
    letter-spacing: 0.5px;
}
.ranklist-table tbody td {
    padding: 12px 10px;
    border-bottom: 1.5px dashed #e3e9f2;
    text-align: center;
    vertical-align: middle;
    word-break: break-word;
}
.ranklist-table tbody tr:last-child td { border-bottom: none; }
.ranklist-table tbody tr:hover {
    background: #F0F6FC;
}

/* 排名徽章 */
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    font-weight: 700;
    font-size: 0.95em;
    border: 2px solid var(--border);
    color: #fff;
}
.rank-badge.gold {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
    box-shadow: 2px 2px 0 var(--gold-deep);
}
.rank-badge.silver {
    background: linear-gradient(135deg, var(--silver) 0%, var(--silver-deep) 100%);
    box-shadow: 2px 2px 0 var(--silver-deep);
}
.rank-badge.bronze {
    background: linear-gradient(135deg, var(--bronze) 0%, var(--bronze-deep) 100%);
    box-shadow: 2px 2px 0 var(--bronze-deep);
}
.rank-badge.normal {
    background: #eef3f9;
    color: var(--text-soft);
    box-shadow: 2px 2px 0 var(--shadow);
}

/* 用户名列 */
.rank-user-cell {
    text-align: left !important;
    padding-left: 16px !important;
    font-weight: 600;
}
.rank-user-cell a { color: var(--sky-deep); }
.rank-nick-cell {
    text-align: left !important;
    color: var(--text-soft);
}

/* 解题进度条 */
.solved-cell {
    text-align: left !important;
    padding: 0 14px !important;
}
.solved-num {
    font-weight: 700;
    color: var(--mint-deep);
    display: block;
    margin-bottom: 4px;
}
.progress-bar-container {
    width: 100%;
    height: 10px;
    background: #eef3f9;
    border-radius: 999px;
    overflow: hidden;
    border: 1.5px solid var(--border);
}
.progress-bar {
    height: 100%;
    border-radius: 999px;
    transition: width 0.5s ease;
}
.progress-bar.high {
    background: linear-gradient(90deg, var(--mint) 0%, var(--mint-deep) 100%);
}
.progress-bar.medium {
    background: linear-gradient(90deg, var(--gold) 0%, var(--gold-deep) 100%);
}
.progress-bar.low {
    background: linear-gradient(90deg, var(--peach) 0%, #c85550 100%);
}

/* 通过率 */
.ratio-high { color: var(--mint-deep); font-weight: 700; }
.ratio-medium { color: var(--gold-deep); font-weight: 700; }
.ratio-low { color: var(--peach); font-weight: 700; }

/* ===== 分页 ===== */
.ranklist-pager {
    text-align: center;
    margin-top: 28px;
}
.ranklist-pager .ui.pagination .ui.button {
    border-radius: 999px !important;
    border: 2px solid var(--border) !important;
    background: #fff !important;
    color: var(--text) !important;
    font-weight: 600;
    margin: 4px;
    box-shadow: 2px 2px 0 var(--shadow);
    transition: all 0.2s ease;
}
.ranklist-pager .ui.pagination .ui.button:hover {
    background: linear-gradient(135deg, var(--sky) 0%, var(--sky-light) 100%) !important;
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 3px 3px 0 var(--sky-deep);
}

/* ===== 移动端适配 ===== */
@media (max-width: 768px) {
    .ranklist-page {
        padding: 18px 12px 36px;
        border-radius: 20px;
        box-shadow: 4px 4px 0 var(--shadow);
    }
    .ranklist-title { font-size: 1.8rem; }

    .podium-section { gap: 10px; }
    .podium-avatar { width: 58px; height: 58px; font-size: 1.5em; }
    .podium-rank-badge { font-size: 1.8em; }
    .podium-user { font-size: 0.9em; max-width: 90px; }
    .podium-nick { max-width: 90px; font-size: 0.75em; }
    .podium-platform { width: 78px; font-size: 1.3em; }
    .podium-platform.gold { height: 60px; }
    .podium-platform.silver { height: 46px; }
    .podium-platform.bronze { height: 36px; }

    .ranklist-search { margin-left: 0; width: 100%; flex-direction: column; align-items: stretch; }
    .ranklist-search > form { flex: 1 1 100%; width: 100%; }
    .ranklist-search .ui.action.input { width: 100% !important; }

    /* 表格转卡片 */
    .ranklist-table thead { display: none; }
    .ranklist-table, .ranklist-table tbody, .ranklist-table tr, .ranklist-table td {
        display: block;
        width: 100%;
    }
    .ranklist-table tbody tr {
        border: 2px solid var(--border);
        border-radius: 14px;
        margin: 12px 8px;
        padding: 10px 14px;
        background: #fff;
        box-shadow: 3px 3px 0 var(--shadow);
        position: relative;
    }
    .ranklist-table tbody td {
        border: none;
        padding: 6px 0;
        text-align: right !important;
        min-height: 22px;
    }
    .ranklist-table tbody td::before {
        content: attr(data-label);
        float: left;
        font-weight: 700;
        color: var(--sky-deep);
        font-size: 0.82rem;
    }
    .rank-user-cell, .rank-nick-cell, .solved-cell {
        text-align: right !important;
        padding-left: 0 !important;
    }
    .rank-badge { float: right; }
}

@media (max-width: 480px) {
    .podium-platform { width: 64px; }
    .podium-user { max-width: 74px; }
    .podium-nick { max-width: 74px; }
    .scope-pill { padding: 7px 14px; font-size: 0.82rem; }
}
</style>

<div class="padding">
    <div class="ranklist-page">

    <h1 class="ranklist-title"><?php echo $MSG_RANKLIST ?></h1>

    <!-- 前三名领奖台（仅在第一页显示） -->
    <?php if ($rank == 0 && $rows_cnt >= 3) { ?>
    <div class="podium-section">
        <!-- 第二名 -->
        <?php if ($rows_cnt >= 2) {
            $row2 = $result[1];
            $user_initial = mb_substr($row2['user_id'], 0, 1, 'UTF-8');
        ?>
        <a href="userinfo.php?user=<?php echo htmlentities($row2['user_id'], ENT_QUOTES, 'UTF-8'); ?>" class="podium-item silver">
            <div class="podium-rank-badge">2</div>
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
            <div class="podium-rank-badge">1</div>
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
            <div class="podium-rank-badge">3</div>
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
	<div class="ranklist-toolbar">
		<div class="scope-pills">
			<a href="ranklist.php?scope=d" class="scope-pill <?php echo $scope=='d'?'active':''; ?>"><?php echo $MSG_DAY?></a>
			<a href="ranklist.php?scope=w" class="scope-pill <?php echo $scope=='w'?'active':''; ?>"><?php echo $MSG_WEEK?></a>
			<a href="ranklist.php?scope=m" class="scope-pill <?php echo $scope=='m'?'active':''; ?>"><?php echo $MSG_MONTH?></a>
			<a href="ranklist.php?scope=y" class="scope-pill <?php echo $scope=='y'?'active':''; ?>"><?php echo $MSG_YEAR?></a>
		</div>
		<div class="ranklist-search">
		  <form action="ranklist.php" class="ui mini form" method="get" role="form">
			<div class="ui action left icon input inline">
			  <i class="search icon"></i><input name="prefix" placeholder="<?php echo $MSG_USER?>" type="text" value="<?php echo htmlentities(isset($_GET['prefix'])?$_GET['prefix']:"",ENT_QUOTES,"utf-8") ?>">
			  <button class="ui mini button" type="submit"><?php echo $MSG_SEARCH?></button>
			</div>
		  </form>
		   <form action="ranklist.php" class="ui mini form" method="get" role="form">
			  <div class="ui action left icon input inline">
				<i class="search icon"></i><input name="group_name" placeholder="<?php echo $MSG_GROUP_NAME ?>" type="text" value="<?php echo htmlentities(isset($_GET['group_name']) ? $_GET['group_name'] : "", ENT_QUOTES, "utf-8") ?>">
				<button class="ui mini button" type="submit"><?php echo $MSG_SEARCH ?></button>
			  </div>
			</form>
		</div>
	</div>

    <!-- 排行榜表格 -->
    <div class="ranklist-card">
	    <table class="ranklist-table">
	        <thead>
	        <tr>
	            <th style="width: 80px;"><?php echo $MSG_Number?></th>
	            <th style="width: 160px;"><?php echo $MSG_USER?></th>
	            <th><?php echo $MSG_NICK?></th>
				<th style="width: 120px;"><?php echo $MSG_GROUP_NAME?></th>
                <th style="width: 160px;"><?php echo $MSG_SOVLED?></th>
                <th style="width: 100px;"><?php echo $MSG_SUBMIT?></th>
                <th style="width: 120px;"><?php echo $MSG_RATIO?></th>
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
	        <tr>
	            <td data-label="<?php echo $MSG_Number?>">
                    <span class="rank-badge <?php echo $rank_class; ?>">
                        <?php echo $current_rank; ?>
                    </span>
                </td>
	            <td class="rank-user-cell" data-label="<?php echo $MSG_USER?>">
                    <?php echo $row[1]; ?>
                </td>
	            <td class="rank-nick-cell" data-label="<?php echo $MSG_NICK?>">
                    <?php echo $row[2]; ?>
                </td>
				<td data-label="<?php echo $MSG_GROUP_NAME?>"><?php echo $row[3]; ?></td>
                <td class="solved-cell" data-label="<?php echo $MSG_SOVLED?>">
                    <span class="solved-num"><?php echo $row[4]; ?></span>
                    <div class="progress-bar-container">
                        <div class="progress-bar <?php echo $progress_class; ?>" style="width: <?php echo $progress_percent; ?>%;"></div>
                    </div>
                </td>
                <td data-label="<?php echo $MSG_SUBMIT?>"><?php echo $row[5]; ?></td>
                <td data-label="<?php echo $MSG_RATIO?>">
                    <span class="ratio-<?php echo $ratio_class; ?>">
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

    <div class="ranklist-pager">
	<div class="ui pagination" style="box-shadow: none;">
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

<?php include("template/$OJ_TEMPLATE/footer.php");?>
