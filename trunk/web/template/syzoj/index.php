<?php $show_title="$MSG_HOME - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
$is_logged_in = isset($_SESSION[$OJ_NAME.'_user_id']);
?>
<link rel="stylesheet" href="<?php echo "template/$OJ_TEMPLATE";?>/css/slide.css">

<!-- 游客友好提示 -->
<?php if(!$is_logged_in) { ?>
<div class="ui message info" style="margin-top: 20px; border-radius: 8px; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border: 1px solid #667eea;">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <i class="info circle icon" style="color: #667eea; font-size: 1.5em;"></i>
            <strong style="color: #333; margin-left: 10px;">欢迎来到 <?php echo $OJ_NAME; ?>！</strong>
            <p style="margin: 8px 0 0 0; color: #666;">您当前以游客身份浏览，可以查看题目和新闻。如需提交代码，请先<a href="loginpage.php" style="color: #667eea; font-weight: 600;">登录</a>或<a href="registerpage.php" style="color: #667eea; font-weight: 600;">注册</a>。</p>
        </div>
        <div>
            <a href="loginpage.php" class="ui small blue button">登录</a>
            <?php if(isset($OJ_REGISTER)&&$OJ_REGISTER){ ?>
            <a href="registerpage.php" class="ui small primary button" style="background: #667eea; border-color: #667eea;">注册</a>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>

<div class="padding">
    <div class="ui three column grid">
        <div class="eleven wide column">
            <?php
                        $sql_news = "select * FROM `news` WHERE `defunct`!='Y' AND `title`!='faqs.cn' ORDER BY `importance` desc,`time` DESC LIMIT 10";
                        $result_news = mysql_query_cache( $sql_news );
                        if ( $result_news && !empty($result_news) ) {
                        ?>
<h4 class="ui top attached block header"><i class="ui info icon"></i><?php echo $MSG_NEWS;?></h4>
            <div class="ui bottom attached segment">
                <table class="ui very basic table">
                    <thead>
                        <tr>
                            <th><?php echo $MSG_TITLE;?></th>
                            <th><?php echo $MSG_TIME;?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ( $result_news as $row ) {
                                echo "<tr>" . "<td>"
                                    . "<a href=\"viewnews.php?id=" . $row["news_id"] . "\">"
                                    . $row["title"] . "</a></td>"
                                    . "<td>" . $row["time"] . "</td>" . "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
			<?php
if(isset($pages) && $pages>1 ){
  echo "<div style='display:inline;'>";
  echo "<nav class='center'>";
  echo "<ul class='pagination pagination-sm'>";
  echo "<li class='page-item'><a href='index.php?page=".(strval(1))."'>&lt;&lt;</a></li>";
  echo "<li class='page-item'><a href='index.php?page=".($page==1?strval(1):strval($page-1))."'>&lt;</a></li>";
  for($i=$spage; $i<=$epage; $i++){
    echo "<li class='".($page==$i?"active ":"")."page-item'><a title='go to page' href='index.php?page=".$i."'>".$i."</a></li>";
  }
  echo "<li class='page-item'><a href='index.php?page=".($page==$pages?strval($page):strval($page+1))."'>&gt;</a></li>";
  echo "<li class='page-item'><a href='index.php?page=".(strval($pages))."'>&gt;&gt;</a></li>";
  echo "</ul>";
  echo "</nav>";
  echo "</div>";
}
?>

<?php
                        }
                        ?>
<?php
/* 本月之星  */
$month_id=mysql_query_cache("select solution_id from solution where  in_date<date_add(curdate(),interval -1 month) order by solution_id desc limit 1;");
if(!empty( $month_id) && isset($month_id[0][0]) ) $month_id=$month_id[0][0];else $month_id=0;
$view_month_rank=mysql_query_cache("select user_id,nick,count(distinct(problem_id)) ac from solution where solution_id>$month_id and problem_id>0  $not_in_noip_contests and user_id not in (".$OJ_RANK_HIDDEN.")  and result=4 group by user_id,nick order by ac desc limit 10");
            if ( !empty($view_month_rank) ) {
        ?>
            <h4 class="ui top attached block header"><i class="ui star icon"></i><?php echo "本月之星"?></h4>
            <div class="ui bottom attached segment">
                <table class="ui very basic center aligned table" style="table-layout: fixed; ">
                    <thead>
                        <tr>
                            <th>用户名（学号）</th>
                            <th>昵称</th>
                            <th>AC数量</th>
                        </tr>
                    </thead>
                    <tbody>
        <?php
                            foreach ( $view_month_rank as $row ) {
                                    echo "<tr>".
                                            "<td><a target='_blank' href='userinfo.php?user=".htmlentities($row[0],ENT_QUOTES,"UTF-8")."'>⭐".htmlentities($row[0],ENT_QUOTES,"UTF-8")."⭐</a></td>".
                                            "<td>".($row[1])."</td>".
                                            "<td>".($row[2])."</td>".
                                            "</tr>";
                            }
        ?>
                    </tbody>
                </table>
            </div>
        <?php
            }
/* 本月之星  */
?>

            <!-- 移除了永远不会执行的冗余代码块 -->

            <!-- 系统统计数据卡片（游客和登录用户都能看到） -->
            <?php
            if(!isset($OJ_ONLINE)) $OJ_ONLINE=false;
            // 获取在线人数
            $online_count = 0;
            if($OJ_ONLINE) {
                require_once($path_fix.'include/online.php');
                $on = new online();
                $online_count = $on->onlineCount();
            }
            // 获取总用户数
            $user_count_result = mysql_query_cache("select count(*) as count from `users` where defunct!='N'");
            $user_count = $user_count_result[0]['count'] ?? 0;
            // 获取题目总数
            $problem_count_result = mysql_query_cache("select count(*) as count from `problem` where defunct='N'");
            $problem_count = $problem_count_result[0]['count'] ?? 0;
            // 获取今日提交数
            $submit_today_result = mysql_query_cache("select count(*) as count from `solution` where DATE(in_date)=CURDATE()");
            $submit_today = $submit_today_result[0]['count'] ?? 0;
            ?>
            <div style="margin-top: 20px;">
                <div class="ui four cards">
                    <div class="ui card" style="border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div class="content" style="text-align: center; padding: 20px;">
                            <i class="users icon" style="font-size: 2em; color: #667eea;"></i>
                            <div class="header" style="margin-top: 10px; font-size: 1.5em; font-weight: 600; color: #333;">
                                <?php echo number_format($user_count); ?>
                            </div>
                            <div class="meta" style="margin-top: 5px; color: #666;">
                                注册用户
                            </div>
                        </div>
                    </div>
                    <div class="ui card" style="border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div class="content" style="text-align: center; padding: 20px;">
                            <i class="desktop icon" style="font-size: 2em; color: #764ba2;"></i>
                            <div class="header" style="margin-top: 10px; font-size: 1.5em; font-weight: 600; color: #333;">
                                <?php echo $online_count; ?>
                            </div>
                            <div class="meta" style="margin-top: 5px; color: #666;">
                                在线人数
                            </div>
                        </div>
                    </div>
                    <div class="ui card" style="border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div class="content" style="text-align: center; padding: 20px;">
                            <i class="book icon" style="font-size: 2em; color: #f0932b;"></i>
                            <div class="header" style="margin-top: 10px; font-size: 1.5em; font-weight: 600; color: #333;">
                                <?php echo number_format($problem_count); ?>
                            </div>
                            <div class="meta" style="margin-top: 5px; color: #666;">
                                题目总数
                            </div>
                        </div>
                    </div>
                    <div class="ui card" style="border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div class="content" style="text-align: center; padding: 20px;">
                            <i class="send icon" style="font-size: 2em; color: #4ecdc4;"></i>
                            <div class="header" style="margin-top: 10px; font-size: 1.5em; font-weight: 600; color: #333;">
                                <?php echo number_format($submit_today); ?>
                            </div>
                            <div class="meta" style="margin-top: 5px; color: #666;">
                                今日提交
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="right floated five wide column">
            <h4 class="ui top attached block header"><i class="ui rss icon"></i> <?php echo $is_logged_in ? $MSG_RECENT_PROBLEM : "热门题目";?> </h4>
            <div class="ui bottom attached segment">
                <table class="ui very basic center aligned table">
                    <thead>
                        <tr>
                            <th width="60%"><?php echo $MSG_TITLE;?></th>
                            <th width="40%"><?php echo $is_logged_in ? $MSG_TIME : "通过人数";?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        if($is_logged_in) {
                            // 登录用户显示"未解之谜"
                            $noip_problems=array_merge(...mysql_query_cache("select problem_id from contest c left join contest_problem cp on start_time<'$now' and end_time>'$now' and  (c.title like ? ? or (c.contest_type & 20) >0) and c.contest_id=cp.contest_id","%$OJ_NOIP_KEYWORD%"));
                            $noip_problems=array_unique($noip_problems);
                            $user_id=$_SESSION[$OJ_NAME."_user_id"];
                            $sql_problems = "select p.problem_id,title,max_in_date from (select problem_id,min(result) best,max(in_date) max_in_date from solution
                                    where user_id=? and result>=4 and problem_id>0 group by problem_id ) s inner join problem p on s.problem_id=p.problem_id
                                 where s.best>4 order by max_in_date desc  LIMIT 5";
                            $result_problems = mysql_query_cache( $sql_problems ,$user_id);
                            if ( !empty($result_problems) ) {
                                $i = 1;
                                foreach ( $result_problems as $row ) {
                                    if(in_array(strval($row['problem_id']),$noip_problems)) continue;
                                    echo "<tr>"."<td>"
                                        ."<a href=\"problem.php?id=".$row["problem_id"]."\">"
                                        .$row["title"]."</a></td>"
                                        ."<td>".substr($row["max_in_date"],5,5)."</td>"."</tr>";
                                }
                            }
                        } else {
                            // 游客显示"热门题目"（按通过人数排序）
                            $sql_hot = "select p.problem_id,p.title,count(distinct s.user_id) as ac
                                         from problem p
                                         left join solution s on p.problem_id=s.problem_id and s.result=4
                                         where p.defunct='N' and p.problem_id>0
                                         group by p.problem_id,p.title
                                         having ac>0
                                         order by ac desc
                                         LIMIT 5";
                            $result_hot = mysql_query_cache( $sql_hot );
                            if ( !empty($result_hot) ) {
                                foreach ( $result_hot as $row ) {
                                    echo "<tr>"."<td>"
                                        ."<a href=\"problem.php?id=".$row["problem_id"]."\">"
                                        ."<span class='ui mini label' style='background: #667eea15; color: #667eea;'>热门</span> "
                                        .$row["title"]."</a></td>"
                                        ."<td>".number_format($row["ac"])."</td>"."</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' style='color: #999;'>暂无热门题目</td></tr>";
                            }
                        }
                    ?>
                    </tbody>
                </table>
            </div>
            <h4 class="ui top attached block header"><i class="ui search icon"></i><?php echo $MSG_SEARCH;?></h4>
            <div class="ui bottom attached segment">
                <form action="problem.php" method="get">
                    <div class="ui search" style="width: 100%; ">
                        <div class="ui left icon input" style="width: 100%; ">
                            <input class="prompt" style="width: 100%; " type="text" placeholder="<?php echo $MSG_PROBLEM_ID ;?> …" name="id">
                            <i class="search icon"></i>
                        </div>
                        <div class="results" style="width: 100%; "></div>
                    </div>
                </form>
            </div>
            <h4 class="ui top attached block header"><i class="ui calendar icon"></i><?php echo $MSG_RECENT_CONTEST ;?></h4>
            <div class="ui bottom attached center aligned segment">
                <table class="ui very basic center aligned table">
                    <thead>
                        <tr>
                            <th><?php echo $MSG_CONTEST_NAME;?></th>
                            <th><?php echo $MSG_START_TIME;?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $sql_contests = "select * FROM `contest` where defunct='N' ORDER BY `contest_id` DESC LIMIT 5";
                        $result_contests = mysql_query_cache( $sql_contests );
                        if ( $result_contests ) {
                            $i = 1;
                            foreach ( $result_contests as $row ) {
                                echo "<tr>"."<td>"
                                    ."<a href=\"contest.php?cid=".$row["contest_id"]."\">"
                                    .$row["title"]."</a></td>"
                                    ."<td>".substr($row["start_time"],5,5)."</td>"."</tr>";
                            }
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 轮播图展示系统亮点（放在顶部，游客和登录用户都能看到） -->
<div style="margin-top: 20px; margin-bottom: 20px;">
    <?php
    // 检查是否有轮播图，如果没有则显示默认内容
    $has_slideshow = file_exists("image/slide1.jpg");
    if($has_slideshow) {
        echo '<div class="carousel" style="position: relative; width: 100%; height: 300px; overflow: hidden; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">';
        for($i=1; $i<=3; $i++) {
            if(file_exists("image/slide$i.jpg")) {
                $active = $i==1 ? 'active' : '';
                echo "<div class='carousel-slide $active' style='position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 0.5s ease; background-size: cover; background-position: center; background-image: url(image/slide$i.jpg);'>";
                echo "</div>";
            }
        }
        echo '<div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px;">';
        for($i=1; $i<=3; $i++) {
            if(file_exists("image/slide$i.jpg")) {
                $active = $i==1 ? 'active' : '';
                echo "<div class='carousel-dot $active' data-index='$i' style='width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s;'></div>";
            }
        }
        echo '</div>';
        echo '</div>';
    } else {
        // 如果没有轮播图，显示默认的系统介绍
        echo '<div class="ui four cards" style="margin-top: 0;">';
        echo '<div class="ui card" style="border-radius: 8px; border: 1px solid #e0e0e0; background: linear-gradient(135deg, #667eea15 0%, #fff 100%);">';
        echo '<div class="content" style="text-align: center; padding: 20px;">';
        echo '<i class="code icon" style="font-size: 2.5em; color: #667eea;"></i>';
        echo '<div class="header" style="margin-top: 10px; font-weight: 600;">海量题库</div>';
        echo '<div class="meta" style="margin-top: 8px; color: #666;">包含精选题目，从入门到竞赛全涵盖</div>';
        echo '</div></div>';

        echo '<div class="ui card" style="border-radius: 8px; border: 1px solid #e0e0e0; background: linear-gradient(135deg, #764ba215 0%, #fff 100%);">';
        echo '<div class="content" style="text-align: center; padding: 20px;">';
        echo '<i class="rocket icon" style="font-size: 2.5em; color: #764ba2;"></i>';
        echo '<div class="header" style="margin-top: 10px; font-weight: 600;">秒级判题</div>';
        echo '<div class="meta" style="margin-top: 8px; color: #666;">高速判题系统，实时反馈结果</div>';
        echo '</div></div>';

        echo '<div class="ui card" style="border-radius: 8px; border: 1px solid #e0e0e0; background: linear-gradient(135deg, #f0932b15 0%, #fff 100%);">';
        echo '<div class="content" style="text-align: center; padding: 20px;">';
        echo '<i class="graduation cap icon" style="font-size: 2.5em; color: #f0932b;"></i>';
        echo '<div class="header" style="margin-top: 10px; font-weight: 600;">学习路径</div>';
        echo '<div class="meta" style="margin-top: 8px; color: #666;">科学分阶，助力循序渐进学习</div>';
        echo '</div></div>';

        echo '<div class="ui card" style="border-radius: 8px; border: 1px solid #e0e0e0; background: linear-gradient(135deg, #4ecdc415 0%, #fff 100%);">';
        echo '<div class="content" style="text-align: center; padding: 20px;">';
        echo '<i class="trophy icon" style="font-size: 2.5em; color: #4ecdc4;"></i>';
        echo '<div class="header" style="margin-top: 10px; font-weight: 600;">竞赛活动</div>';
        echo '<div class="meta" style="margin-top: 8px; color: #666;">定期举办比赛，检验学习成果</div>';
        echo '</div></div>';

        echo '</div>';
    }
    ?>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
<?php if($has_slideshow){ ?>
    <script>
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        let currentIndex = 0;
        let autoPlayInterval;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });
            dots.forEach((dot, i) => {
                if (i === index) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % slides.length;
            showSlide(currentIndex);
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + slides.length) % slides.length;
            showSlide(currentIndex);
        }

        // 自动播放，调整为 5 秒切换一次
        autoPlayInterval = setInterval(nextSlide, 5000); 

        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                const targetIndex = parseInt(dot.dataset.index);
                if (targetIndex!== currentIndex) {
                    currentIndex = targetIndex;
                    showSlide(currentIndex);
                    clearInterval(autoPlayInterval);
                    autoPlayInterval = setInterval(nextSlide, 5000);
                }
            });
        });

        // 鼠标悬停暂停自动播放
        const carousel = document.querySelector('.carousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));
            carousel.addEventListener('mouseleave', () => autoPlayInterval = setInterval(nextSlide, 5000));
        }
    </script>
<?php } ?>
