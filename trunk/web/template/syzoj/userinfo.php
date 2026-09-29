<?php $show_title="$MSG_USERINFO - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<link rel="stylesheet" href="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE/fonts/zcool-kuaile.css?v=1"?>">
<style>
/* ===== 个人主页 · 与顶部天空蓝菜单统一的贴纸卡片风格 ===== */
.userinfo-page {
    --u-ink: #3d4f6b;
    --u-ink-soft: #7a8ba3;
    --u-sky: #67a0d5;
    --u-sky-deep: #5b90cd;
    --u-sky-dark: #3a6da8;
    --u-sky-light: #78b0d9;
    --u-cream: #fff8dd;
    --u-gold: #f4cd75;
    --u-coral: #e2726d;
    --u-mint: #7FD4B5;
    --u-mint-deep: #2E8B6F;
    --u-lavender: #B4A7E6;
    --u-shadow: rgba(61, 79, 107, 0.16);

    padding: 22px 16px 40px;
    max-width: 1120px;
    margin: 0 auto 20px;
    color: var(--u-ink);
    background: #FFF9EC;
    background-image:
        radial-gradient(circle at 1px 1px, rgba(103, 160, 213, 0.18) 1.5px, transparent 0),
        radial-gradient(circle at 1px 1px, rgba(244, 205, 117, 0.16) 1.5px, transparent 0);
    background-size: 26px 26px;
    background-position: 0 0, 13px 13px;
    border-radius: 24px;
    font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", "Helvetica Neue", sans-serif;
}

/* === 入场动效 === */
@keyframes u-pop {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.profile-hero { animation: u-pop 0.45s ease both; }
.u-panel { animation: u-pop 0.45s ease both; }
.panels .u-panel:nth-child(2) { animation-delay: 0.05s; }
.panels .u-panel:nth-child(3) { animation-delay: 0.1s; }
.panels .u-panel:nth-child(4) { animation-delay: 0.15s; }
.panels .u-panel:nth-child(5) { animation-delay: 0.2s; }
.panels .u-panel:nth-child(6) { animation-delay: 0.25s; }
.panels .u-panel:nth-child(7) { animation-delay: 0.3s; }

/* === 顶部个人名片 === */
.profile-hero {
    background: #fff;
    border: 2.5px solid var(--u-ink);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 4px 4px 0 var(--u-shadow);
    margin-bottom: 20px;
}
.hero-top {
    position: relative;
    display: flex;
    gap: 22px;
    align-items: center;
    padding: 22px 26px 18px;
    background: linear-gradient(100deg, var(--u-sky-deep) 0%, var(--u-sky) 52%, var(--u-sky-light) 100%);
}
.hero-top::before,
.hero-top::after {
    content: '✦';
    position: absolute;
    color: var(--u-cream);
    opacity: 0.55;
    pointer-events: none;
}
.hero-top::before { top: 10px; right: 28px; font-size: 1.1rem; }
.hero-top::after { bottom: 12px; right: 90px; font-size: 0.8rem; }

.avatar-img {
    width: 96px;
    height: 96px;
    flex: none;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    background: #dbe7f2;
}
.avatar-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.hero-info { flex: 1; min-width: 0; }
.hero-nick {
    font-family: 'ZCOOL KuaiLe', -apple-system, sans-serif;
    color: #fff;
    font-size: 1.9rem;
    font-weight: 400;
    margin: 0 0 8px;
    text-shadow: 0 2px 0 rgba(0, 0, 0, 0.12);
    word-break: break-all;
}
.hero-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.hero-chip {
    color: #fff;
    font-size: 0.82rem;
    background: rgba(255, 255, 255, 0.22);
    border: 1.5px solid rgba(255, 255, 255, 0.55);
    border-radius: 999px;
    padding: 4px 12px;
}
.hero-mail {
    margin-top: 10px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #fff;
    font-size: 0.82rem;
    background: rgba(0, 0, 0, 0.08);
    border-radius: 999px;
    padding: 5px 14px;
    word-break: break-all;
}
.hero-mail:hover { background: rgba(0, 0, 0, 0.2); color: #fff; }

/* 等级进度 */
.level-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 22px 6px;
    flex-wrap: wrap;
}
.level-badge {
    font-family: 'ZCOOL KuaiLe', sans-serif;
    font-weight: 400;
    font-size: 1.05rem;
    color: #7a5310;
    background: linear-gradient(135deg, var(--u-gold), #f0b24c);
    border: 2px solid #d9a14a;
    border-radius: 999px;
    padding: 5px 18px;
    flex: none;
}
.level-track {
    flex: 1;
    min-width: 170px;
    height: 14px;
    background: #eef2f7;
    border-radius: 999px;
    overflow: hidden;
    border: 1px solid #dde5ee;
}
.level-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--u-mint), var(--u-mint-deep));
    border-radius: 999px;
    transition: width 0.8s ease;
}
.level-tip {
    width: 100%;
    font-size: 0.82rem;
    color: var(--u-ink-soft);
    padding: 0 22px 14px;
}

/* 三项核心数据 */
.hero-stats {
    display: flex;
    border-top: 1px dashed #d9e2ec;
}
.hero-stat {
    flex: 1;
    text-align: center;
    padding: 13px 8px;
    border-right: 1px dashed #d9e2ec;
}
.hero-stat:last-child { border-right: none; }
.hero-stat .num {
    font-family: 'ZCOOL KuaiLe', sans-serif;
    font-weight: 400;
    font-size: 1.55rem;
    line-height: 1.2;
}
.hero-stat .lab {
    font-size: 0.8rem;
    color: var(--u-ink-soft);
}
.hero-stat.stat-ac .num { color: var(--u-mint-deep); }
.hero-stat.stat-rank .num { color: #c9862a; }
.hero-stat.stat-submit .num { color: var(--u-sky-dark); }

/* === 面板网格 === */
.panels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}
.u-panel {
    background: #fff;
    border: 2px solid var(--u-ink);
    border-radius: 16px;
    box-shadow: 3px 3px 0 var(--u-shadow);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.u-panel.full { grid-column: 1 / -1; }
.u-panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 16px;
    border-bottom: 2px dashed rgba(61, 79, 107, 0.22);
    background: linear-gradient(135deg, #F0F6FC, #fff);
}
.u-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1rem;
    flex: none;
}
.u-icon.sky { background: linear-gradient(135deg, var(--u-sky), var(--u-sky-dark)); }
.u-icon.gold { background: linear-gradient(135deg, #f7d98e, #e8a83c); }
.u-icon.mint { background: linear-gradient(135deg, var(--u-mint), var(--u-mint-deep)); }
.u-icon.coral { background: linear-gradient(135deg, #ec9a96, #d1544f); }
.u-icon.lavender { background: linear-gradient(135deg, #c6bcef, #9684d4); }
.u-panel-title {
    font-family: 'ZCOOL KuaiLe', sans-serif;
    font-size: 1.25rem;
    font-weight: 400;
    margin: 0;
    color: var(--u-ink);
}
.u-panel-body { padding: 14px 16px; }

/* 热力日历 */
#sub_date_chart { width: 100%; height: 210px; }
.u-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 10px;
    padding: 7px 16px;
    border: 1.5px solid var(--u-sky-deep);
    color: var(--u-sky-dark);
    background: #F0F6FC;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.u-link-btn:hover { background: var(--u-sky); border-color: var(--u-sky); color: #fff; }

/* 饼图 */
.pie-wrap {
    display: flex;
    gap: 14px;
    align-items: center;
}
.pie-legend-col { flex: 1.2; min-width: 0; }
.pie-canvas-col { flex: 0.8; min-width: 140px; }
.pie-canvas-wrap {
    position: relative;
    width: 100%;
    max-width: 190px;
    aspect-ratio: 1;
    margin: 0 auto;
}
.userinfo-page #pie_chart_legend ul { padding-left: 4px !important; }
.userinfo-page #pie_chart_legend li { font-size: 0.86rem !important; margin: 7px 4px !important; }

/* 题目标签云（document.write 生成，仅覆写样式不改逻辑） */
.userinfo-page a.ui.green.basic.label {
    background: #E8FBF3 !important;
    color: var(--u-mint-deep) !important;
    border: 1.5px solid var(--u-mint-deep) !important;
    border-radius: 999px !important;
    padding: 5px 13px !important;
    margin: 0 7px 9px 0 !important;
    font-weight: 600;
    font-size: 0.9rem;
    background-image: none !important;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.userinfo-page a.ui.red.basic.label {
    background: #FDEDEA !important;
    color: #c0453f !important;
    border: 1.5px solid var(--u-coral) !important;
    border-radius: 999px !important;
    padding: 5px 13px !important;
    margin: 0 7px 9px 0 !important;
    font-weight: 600;
    font-size: 0.9rem;
    background-image: none !important;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.userinfo-page a.ui.basic.label {
    background: #F2F5F9 !important;
    color: var(--u-ink) !important;
    border: 1.5px solid #c2cdda !important;
    border-radius: 999px !important;
    padding: 5px 13px !important;
    margin: 0 7px 9px 0 !important;
    font-weight: 600;
    font-size: 0.9rem;
    background-image: none !important;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.userinfo-page a.ui.green.basic.label:hover,
.userinfo-page a.ui.red.basic.label:hover,
.userinfo-page a.ui.basic.label:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 8px rgba(61, 79, 107, 0.18);
}

/* 表格通用（移动端横向滑动） */
.u-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.userinfo-page table { margin: 0; }
.userinfo-page .u-table th {
    background: #F0F6FC;
    color: var(--u-ink);
    font-weight: 700;
    padding: 10px 13px;
    white-space: nowrap;
    border-bottom: 2px solid #d9e4ef;
}
.userinfo-page .u-table td {
    padding: 9px 13px;
    border-top: 1px solid #eef1f5;
    white-space: nowrap;
}
.userinfo-page .u-table tbody tr:hover td { background: #F8FBFE; }
.userinfo-page .u-table td a { color: var(--u-sky-dark); font-weight: 600; }
.userinfo-page .u-table td a:hover { color: var(--u-sky); }

/* === 移动端适配 === */
@media (max-width: 768px) {
    .userinfo-page { padding: 16px 10px 32px; border-radius: 18px; }
    .panels { grid-template-columns: 1fr; gap: 14px; }
    #sub_date_chart { height: 195px; }
    .pie-wrap { flex-direction: column-reverse; gap: 8px; }
    .pie-legend-col { width: 100%; }
}
@media (max-width: 640px) {
    .hero-top {
        flex-direction: column;
        text-align: center;
        gap: 14px;
        padding: 20px 16px 16px;
    }
    .avatar-img { width: 78px; height: 78px; }
    .hero-nick { font-size: 1.6rem; }
    .hero-tags { justify-content: center; }
    .level-row { padding: 14px 16px 4px; gap: 10px; }
    .level-tip { padding: 0 16px 12px; }
    .hero-stat .num { font-size: 1.3rem; }
    .hero-stat .lab { font-size: 0.72rem; }
    .u-panel-head { padding: 10px 13px; }
    .u-panel-body { padding: 12px 13px; }
}
</style>
<?php
    $calsed = '锦鲤';
    $calledid = -1;
    $acneed = [10,20,30,50,80,100,200,300,500,800,1000];
    $accall = ["萌新","小小牛","小牛","小犇","中牛","中犇","大牛","大犇","神牛","神犇"];
    for ($i = count($accall);$i > 0; $i--) {
        if ($AC < $acneed[$i]) {$calsed = $accall[$i - 1];$calledid=$i-1;}
    }
    // 仅用于进度条展示的派生值，不改变原有数据查询逻辑
    $level_pct = $AC >= 1000 ? 100 : max(2, intval($AC / 1000 * 100));
/*
    for ($i=0;$i<=11;++$i){
    	$ped[$i]=0;
    }
    $sql="select * FROM `solution` WHERE `user_id`=?";
    $result = mysql_query_cache($sql, $user);
    foreach ($result as $row) {
    	++$ped[$row['result']];
    }
*/
?>

<div class="userinfo-page">

    <!-- 个人名片 -->
    <section class="profile-hero" id="user_card">
        <div class="hero-top">
            <?php $default = ""; $grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=500"; ?>
            <?php
                // 如果email填写的是qq邮箱，取QQ头像显示
                $qq=stripos($email,"@qq.com");
                if($qq>0){
                     $qq=urlencode(substr($email,0,$qq));
                     $grav_url="https://q1.qlogo.cn/g?b=qq&nk=$qq&s=5";
                };

            ?>
            <div class="avatar-img blurring dimmable image" id="avatar_container">
                <img src="<?php echo $grav_url; ?>" alt="avatar">
            </div>
            <div class="hero-info">
                <h1 class="hero-nick"><?php echo $nick?></h1>
                <div class="hero-tags">
                    <?php if (!empty($school)) { ?><span class="hero-chip">🏫 <?php echo $school?></span><?php } ?>
                    <span class="hero-chip">🏷️ <?php echo $group_name?></span>
                </div>
                <?php if ($email != "") { ?>
                    <a class="hero-mail" href="mailto:<?php echo "Hello" ?>?body=CSPOJ">
                        <i class="envelope icon"></i><?php echo $email?>
                    </a>
                <?php } ?>
            </div>
        </div>

        <div class="level-row">
            <span class="level-badge">等级 · <?php echo $calsed;?></span>
            <div class="level-track">
                <div class="level-fill" style="width: <?php echo $level_pct;?>%"></div>
            </div>
        </div>
        <div class="level-tip">
            <?php if ($calledid >= 0 && $calledid < 9) { ?>
                距离「<?php echo $accall[$calledid+1];?>」还需 AC <?php echo $acneed[$calledid+1]-$AC;?> 题，继续加油！
            <?php } else { ?>
                已达成最高等级，刷题之路永不止步，继续保持！
            <?php } ?>
        </div>

        <div class="hero-stats">
            <div class="hero-stat stat-ac">
                <div class="num"><?php echo $AC ?></div>
                <div class="lab"><i class="check icon"></i>通过题数</div>
            </div>
            <div class="hero-stat stat-rank">
                <div class="num"><?php echo $Rank ?></div>
                <div class="lab"><i class="star icon <?php if($starred) echo "active"?>" ></i>当前排名</div>
            </div>
            <div class="hero-stat stat-submit">
                <div class="num"><?php echo $Submit ?></div>
                <div class="lab"><i class="pencil alternate icon"></i>尝试题数</div>
            </div>
        </div>
    </section>

    <div class="panels">

        <!-- 提交统计热力日历 -->
        <section class="u-panel full">
            <div class="u-panel-head">
                <span class="u-icon mint"><i class="calendar check icon"></i></span>
                <h2 class="u-panel-title"><?php echo $MSG_SUBMIT.$MSG_STATISTICS ?></h2>
            </div>
            <div class="u-panel-body">
                <div id="sub_date_chart"></div>
                <a class="u-link-btn" href="/status.php?user_id=<?php echo $user?>"><i class="search icon"></i><?php echo $MSG_SUBMIT.$MSG_LIST ?></a>
            </div>
        </section>

        <!-- 比赛记录 -->
        <section class="u-panel">
            <div class="u-panel-head">
                <span class="u-icon gold"><i class="trophy icon"></i></span>
                <h2 class="u-panel-title"><?php echo $MSG_CONTEST?></h2>
            </div>
            <div class="u-panel-body">
                <div class="u-scroll">
                <table class='striped table ui u-table'>
                <tr>
                    <th><?php echo $MSG_NUM ?></th>
                    <th><?php echo $MSG_TITLE ?></th>
                    <th><?php echo $MSG_START_TIME?></th>
                    <th><?php echo $MSG_SUBMIT ?></th>
                    <th><?php echo $MSG_AC ?></th>
                </tr>
<?php
  $sql="select c.contest_id,c.title,c.start_time,rt.tried,rt.ac  from contest c
                            inner join (select contest_id,count(distinct(problem_id)) as tried, count(distinct(if(result=4,problem_id,null))) as ac from solution
                                where user_id=? and contest_id>0
 group by contest_id order by contest_id) rt on c.contest_id=rt.contest_id and  not (c.title like '%$OJ_NOIP_KEYWORD%' or  ((c.contest_type & 20) >0 and end_time>now() )) ;";

                $contests=pdo_query($sql,$user);
                    $total=$total_ac=$cnt=0;
                    foreach($contests as $row){
                            echo "<tr><td>".++$cnt."</td>";
                        $id=0;
                            for($i=0;$i<count($row)/2;$i++){
                                if($id==0){
                                    $id=$row[$i];
                                    continue;
                                }
                                    echo "<td>";
                                    if($i==1) echo "<a href='contestrank.php?cid=$id#".htmlentities($user)."' target=_blank>".$row[$i]."</a>";
                                else echo "\t".$row[$i];
                                if($i==3) $total+=$row[$i];
                                                        if($i==4) $total_ac+=$row[$i];
                                    echo "</td>";
                            }
                            echo "</tr>";
                    }
?>
                 <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th><?php echo $total ?></th>
                                        <th><?php echo $total_ac ?></th>
                                </tr>
                                    </table>
                                </div>
                            </div>
                        </section>

        <!-- 结果统计饼图 -->
        <section class="u-panel">
            <div class="u-panel-head">
                <span class="u-icon sky"><i class="chart pie icon"></i></span>
                <h2 class="u-panel-title"><?php echo $MSG_STATISTICS?></h2>
            </div>
            <div class="u-panel-body">
                <div class="pie-wrap">
                    <div id="pie_chart_legend" class="pie-legend-col"></div>
                    <div class="pie-canvas-col">
                        <div class="pie-canvas-wrap">
                            <canvas id="pie_chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 通过的题目 -->
        <section class="u-panel">
            <div class="u-panel-head">
                <span class="u-icon mint"><i class="check circle icon"></i></span>
                <h2 class="u-panel-title">通过的题目</h2>
            </div>
            <div class="u-panel-body">
                <script language='javascript'>
                    function p(id, c) {
                        if(c>0)document.write("<a title=\"U've Passed!\" href=problem.php?id=" + id + " class=\"ui green basic label\" id=\"show-problem-id\">" + id + " </a>");
                        else document.write("<a title=\"U've Not Passed Yet!\" href=problem.php?id=" + id + " class=\"ui red basic label\" id=\"show-problem-id\">" + id + " </a>");
                    }
                    function ptot(len) {
                        document.write("<div style='text-align:right;margin-bottom:10px'><div class='ui green small horizontal statistic'><div class='value'>" + len + "</div><div class='label'>AC</div></div></div>")
                    }
                    <?php
                    $ac=array();
                    $sql = "select `problem_id`,count(1) from solution where `user_id`=? and result=4 and problem_id != 0 $not_in_noip group by `problem_id` ORDER BY `problem_id` ASC";
                    if ($ret = mysql_query_cache($sql, $user)) {
                        $len = count($ret);
                        echo "ptot($len);";
                        foreach ($ret as $row){
                            if (isset($acc_arr[$row['problem_id']]))
                                echo "p($row[0],$row[1]);";
                            else
                                echo "p($row[0],0);";
                            array_push($ac,$row[0]);
                        }
                    }
                    ?>
                </script>
            </div>
        </section>

        <!-- 未通过的题目 -->
        <section class="u-panel">
            <div class="u-panel-head">
                <span class="u-icon coral"><i class="times circle icon"></i></span>
                <h2 class="u-panel-title">未通过的题目</h2>
            </div>
            <div class="u-panel-body">
                <script language='javascript'>
                    function p(id, c) {
                        document.write("<a href=problem.php?id=" + id + " class=\"ui basic label\" id=\"show-problem-id\">" + id +
                            " </a>");
                    }
                    <?php
                    $sql = "select `sol`.`problem_id`, count(1) from solution sol where `sol`.`user_id`=? and `sol`.`result`!=4 and sol.problem_id != 0  $not_in_noip group by `sol`.`problem_id` ORDER BY `sol`.`problem_id` ASC";
                    if ($result = mysql_query_cache($sql, $user)) {
                        foreach ($result as $row)
                            if(!in_array($row[0],$ac))echo "p($row[0],$row[1]);";
                    }
                    ?>
                </script>
            </div>
        </section>

        <!-- 系统题单 -->
        <section class="u-panel full">
            <div class="u-panel-head">
                <span class="u-icon lavender"><i class="list alternate icon"></i></span>
                <h2 class="u-panel-title">系统题单</h2>
            </div>
            <div class="u-panel-body">
                <div class="u-scroll">
<?php
echo "<table class='ui striped table u-table'>";
foreach($plista as $plist){
	echo "<tr>";
	$name=$plist["name"];
	echo "<td>$name</td>";
	$list=explode(",",$plist['list']);
	foreach($list as $pid){
		if (in_array($pid,$ac)) $color="green"; else $color="red";
	 	echo "<td class='ui $color basic label'><a href=problem.php?id=$pid>".$bible[$pid]."</a></td>";
	}
	echo "</tr>";
}
echo "</table>";
?>
                </div>
            </div>
        </section>

        <!-- 近七天登录日志（仅管理员） -->
        <section class="u-panel full">
            <div class="u-panel-head">
                <span class="u-icon sky"><i class="history icon"></i></span>
                <h2 class="u-panel-title">近七天登录日志</h2>
            </div>
            <div class="u-panel-body">
                <?php
                if(isset($_SESSION[$OJ_NAME.'_'.'administrator'])){
                ?><div class="u-scroll"><table class='ui table u-table'>
                <thead><tr class=toprow><th>UserID</th><th>Password</th><th>IP</th><th>Time</th></tr></thead>
                <tbody>
                <?php
                $cnt=0;
                $log_shown=0;
                $log_cutoff = strtotime('-7 days');
                foreach($view_userinfo as $row){
                        // 仅展示近七天日志，早于截止时间的记录跳过
                        if (!empty($row[3]) && strtotime($row[3]) < $log_cutoff) continue;
                        if ($cnt)
                                echo "<tr class='oddrow'>";
                        else
                                echo "<tr class='evenrow'>";
                        for($i=0;$i<count($row)/2;$i++){
                                echo "<td>";
                                echo "\t".$row[$i];
                                echo "</td>";
                        }
                        echo "</tr>";
                        $cnt=1-$cnt;
                        $log_shown++;
                }
                if (!$log_shown)
                        echo "<tr><td colspan='4' style='text-align:center;color:#7a8ba3;white-space:normal;'>近七天暂无登录记录</td></tr>";
                ?>
                </tbody>
                </table></div>
                <?php
                }
                ?>
            </div>
        </section>

    </div>
</div>

<script>
    $(function() {
        $('#user_card .image').dimmer({
            on: 'hover'
        });
        var pie = new Chart(document.getElementById('pie_chart').getContext('2d'), {
            aspectRatio: 1,
            type: 'pie',
            data: {
                datasets: [{
                    data: [
                        <?php
                        foreach ($view_userstat as $row) {
                            echo $row[1] . ",\n";
                        }
                        ?>
                    ],
                    backgroundColor: [
                        "#2E8B6F",
                        "#e2726d",
                        "#c0453f",
                        "#e8a83c",
                        "#9684d4",
                        "#5b90cd",
                        "#d97aa0",
                        "#3d4f6b",
                        "#d9b24c",
                    ]
                }],
                labels: [
                    <?php
                    foreach ($view_userstat as $row) {
                        echo "\"" . $jresult[$row[0]] . "\",\n";
                    }
                    ?>
                ]
            },
            options: {
                responsive: true,
                legend: {
                    display: false
                },
                legendCallback: function(chart) {
                    var text = [];
                    text.push(
                        '<ul style="list-style: none; padding-left: 20px; margin-top: 0; " class="' +
                        chart.id + '-legend">');

                    var data = chart.data;
                    var datasets = data.datasets;
                    var labels = data.labels;

                    if (datasets.length) {
                        for (var i = 0; i < datasets[0].data.length; ++i) {
                            text.push(
                                '<li style="font-size: 15px; color: #666; margin:10px 20px"><span style="width: 12px; height: 12px; display: inline-block; border-radius: 50%; margin-right: 5px; background-color: ' +
                                datasets[0].backgroundColor[i] + '; "></span>');
                            if (labels[i]) {
                                text.push(labels[i]);
                                text.push(' : ' + datasets[0].data[i]);
                            }
                            text.push('</li>');
                        }
                    }

                    text.push('</ul>');
                    return text.join('');
                }
            },
        });
        document.getElementById('pie_chart_legend').innerHTML = pie.generateLegend();
    });
</script>

<?php
$sub_data = [];
$max_count = 0;
$sql = "select DATE(in_date),count(*) FROM solution WHERE user_id=? AND  in_date >= DATE_SUB(CURDATE(),INTERVAL 1 YEAR) AND result < 13 $not_in_noip GROUP BY DATE(in_date);";
$ret = mysql_query_cache($sql, $user);
foreach ($ret as $row) {
    array_push($sub_data, [$row[0], (int)$row[1]]);
    $max_count = max($max_count, (int)$row[1]);
}
// $max_count = ceil($max_count / 100) * 100;
date_default_timezone_set('PRC');
$today = date('Y-m-d', time());
$beg_time = date('Y-m-d', strtotime("-6 month"));
// echo json_encode($sub_data, false);
?>
<script  src="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE"?>/js/echarts.min.js"></script>
<!--<script src="https://cdn.staticfile.org/echarts/5.1.2/echarts.min.js"></script>-->

<script type="text/javascript">
    var chartDom = document.getElementById('sub_date_chart');
    var myChart = echarts.init(chartDom);
    var option;
    var today = new Date();

    // 根据容器宽度自适应日历格子大小，保证窄屏不溢出
    function calcCell() {
        var w = chartDom.clientWidth || (window.innerWidth - 60);
        var size = Math.max(11, Math.min(20, Math.floor((w - 66) / 27)));
        return [size, size];
    }
    var isNarrow = (chartDom.clientWidth || window.innerWidth) < 480;

    option = {
        title: {
            top: 30,
            left: 'center',
        },
        tooltip: {
            formatter: function(params) {
                return params.value[0] + '<br>提交数：' + params.value[1];
            }
        },
        visualMap: {
            min: 0,
            max: <?php echo $max_count ?>,
            show: false,
            type: 'piecewise',
            orient: 'horizontal',
            left: 'center',
            top: 10,
            inRange: {
                color: ['#a9e3c6', '#2E8B6F']
            }
        },
        calendar: {
            top: 30,
            left: isNarrow ? 30 : 40,
            right: isNarrow ? 12 : 30,
            cellSize: calcCell(),
            range: ['<?php echo $beg_time ?>', '<?php echo $today ?>'],
            itemStyle: {
                borderWidth: 0.5,

            },
            lineStyle: {
                color: '#D10E00',
                width: 1,
                opacity: 1
            },
            yearLabel: {
                show: false
            },
            dayLabel: {
                firstDay: 1,
                nameMap: 'cn',
                margin: isNarrow ? '4px' : '8px',
                color: 'gray',
                fontSize: isNarrow ? 11 : 12
            },
            monthLabel: {
                nameMap: 'cn',
                margin: isNarrow ? 10 : 15,
                fontSize: isNarrow ? 12 : 14,
                color: 'gray'
            },
            splitLine: {
                show: false
            },

        },
        series: {
            name: '提交次数',
            type: 'heatmap',
            coordinateSystem: 'calendar',
            data: <?php echo json_encode($sub_data, false); ?>,
        }
    };

    option && myChart.setOption(option);

    // 窗口尺寸变化时重算格子并重绘
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            myChart.resize();
            option.calendar.cellSize = calcCell();
            myChart.setOption(option);
        }, 150);
    });
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
