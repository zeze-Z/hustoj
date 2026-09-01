<?php $show_title="$MSG_HOME - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
$is_logged_in = isset($_SESSION[$OJ_NAME.'_user_id']);
?>
<link rel="stylesheet" href="<?php echo "template/$OJ_TEMPLATE";?>/css/slide.css">

<div class="padding" style="padding-top: 15px;">

    <!-- 游客友好提示 -->
    <?php if(!$is_logged_in) { ?>
    <div style="margin-bottom: 20px; border-radius: 8px; background: linear-gradient(135deg, #ff6b6b 0%, #ffa502 100%); padding: 16px 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center;">
                <i class="gift icon" style="color: white; font-size: 1.5em;"></i>
                <div style="margin-left: 12px;">
                    <strong style="color: white; font-size: 1.1em;">🎉 新用户注册送 6 积分，连登 7 天再得 14 积分！</strong>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 0.95em;">海量免费课件、体验编程游戏，开启教学新体验</p>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="registerpage.php" class="ui small button" style="background: white; color: #ff6b6b; font-weight: 600;">立即注册</a>
                <a href="course.php" class="ui small button" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">浏览课件</a>
            </div>
        </div>
    </div>
    <?php } ?>

    <!-- 教师入驻引导 -->
    <div style="margin-bottom: 20px; border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 16px 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center;">
                <i class="graduation icon" style="color: white; font-size: 1.5em;"></i>
                <div style="margin-left: 12px;">
                    <strong style="color: white; font-size: 1.1em;">👨‍🏫 老师您好：想给全班学生开通账号？</strong>
                    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 0.95em;">添加客服QQ，提供学生名单，3步完成批量开通</p>
                </div>
            </div>
            <a href="teacher_guide.php" class="ui small button" style="background: white; color: #667eea; font-weight: 600;">查看开通指南</a>
        </div>
    </div>

    <!-- 功能介绍轮播图 -->
    <div style="margin-bottom: 25px;">
        <div class="carousel" id="featureCarousel" style="position: relative; width: 100%; height: 280px; overflow: hidden; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
            <!-- 轮播项 0: 新用户福利 -->
            <div class="carousel-slide active" data-index="0" style="position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 0.6s ease; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #ff6b6b 0%, #ffa502 100%); z-index: 1; pointer-events: none;">
                <div style="text-align: center; color: white; padding: 40px;">
                    <i class="gift icon" style="font-size: 5em; margin-bottom: 20px;"></i>
                    <h2 style="font-size: 2em; margin: 0 0 15px 0; font-weight: 600;">新用户福利</h2>
                    <p style="font-size: 1.1em; opacity: 0.95; margin: 0 0 20px 0;">注册且连续登录送 20 积分，免费课件、编程游戏等你来体验</p>
                    <a href="registerpage.php" class="ui button inverted">立即注册</a>
                </div>
            </div>
            <!-- 轮播项 1: 课件商城 -->
            <div class="carousel-slide" data-index="1" style="position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 0.6s ease; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); z-index: 1; pointer-events: none;">
                <div style="text-align: center; color: white; padding: 40px;">
                    <i class="book icon" style="font-size: 5em; margin-bottom: 20px;"></i>
                    <h2 style="font-size: 2em; margin: 0 0 15px 0; font-weight: 600;">课件商城</h2>
                    <p style="font-size: 1.1em; opacity: 0.95; margin: 0 0 20px 0;">优质编程教育资源，助力高效教学</p>
                    <?php if(isset($_SESSION[$OJ_NAME.'_'.'teacher']) || isset($_SESSION[$OJ_NAME.'_'.'administrator'])){ ?>
                    <a href="course.php" class="ui button inverted">浏览课件</a>
                    <?php } else { ?>
                    <a href="https://docs.qq.com/doc/DUkRMQUpYemRZYkti#" target="_blank" class="ui button inverted">了解详情</a>
                    <?php } ?>
                </div>
            </div>
            <!-- 轮播项 2: 趣味游戏 -->
            <div class="carousel-slide" data-index="2" style="position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 0.6s ease; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); z-index: 1; pointer-events: none;">
                <div style="text-align: center; color: white; padding: 40px;">
                    <i class="gamepad icon" style="font-size: 5em; margin-bottom: 20px;"></i>
                    <h2 style="font-size: 2em; margin: 0 0 15px 0; font-weight: 600;">趣味游戏</h2>
                    <p style="font-size: 1.1em; opacity: 0.95; margin: 0 0 20px 0;">寓教于乐，在游戏中提升编程思维</p>
                    <a href="more.php" class="ui button inverted">开始体验</a>
                </div>
            </div>
            <!-- 轮播项 4: 海量题库 -->
            <div class="carousel-slide" data-index="4" style="position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 0.6s ease; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); z-index: 1; pointer-events: none;">
                <div style="text-align: center; color: white; padding: 40px;">
                    <i class="code icon" style="font-size: 5em; margin-bottom: 20px;"></i>
                    <h2 style="font-size: 2em; margin: 0 0 15px 0; font-weight: 600;">海量题库</h2>
                    <p style="font-size: 1.1em; opacity: 0.95; margin: 0 0 20px 0;">包含精选编程题目，从入门到竞赛全涵盖</p>
                    <a href="problemset.php" class="ui button inverted">开始刷题</a>
                </div>
            </div>
            <!-- 轮播项 3: 秒级判题 -->
            <div class="carousel-slide" data-index="3" style="position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 0.6s ease; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); z-index: 1; pointer-events: none;">
                <div style="text-align: center; color: white; padding: 40px;">
                    <i class="rocket icon" style="font-size: 5em; margin-bottom: 20px;"></i>
                    <h2 style="font-size: 2em; margin: 0 0 15px 0; font-weight: 600;">秒级判题</h2>
                    <p style="font-size: 1.1em; opacity: 0.95; margin: 0 0 20px 0;">高速判题系统，实时反馈评测结果</p>
                    <a href="status.php" class="ui button inverted">查看状态</a>
                </div>
            </div>
            <!-- 轮播项 5: 竞赛活动 -->
            <div class="carousel-slide" data-index="5" style="position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 0.6s ease; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); z-index: 1; pointer-events: none;">
                <div style="text-align: center; color: white; padding: 40px;">
                    <i class="trophy icon" style="font-size: 5em; margin-bottom: 20px;"></i>
                    <h2 style="font-size: 2em; margin: 0 0 15px 0; font-weight: 600;">竞赛活动</h2>
                    <p style="font-size: 1.1em; opacity: 0.95; margin: 0 0 20px 0;">定期举办比赛，检验学习成果</p>
                    <a href="contest.php" class="ui button inverted">参加竞赛</a>
                </div>
            </div>
            <!-- 左右箭头 -->
            <button class="carousel-prev" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); border: none; color: white; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; font-size: 1.5em; transition: background 0.3s; z-index: 100;">
                <i class="chevron left icon"></i>
            </button>
            <button class="carousel-next" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); border: none; color: white; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; font-size: 1.5em; transition: background 0.3s; z-index: 100;">
                <i class="chevron right icon"></i>
            </button>
            <!-- 指示点 -->
            <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 12px; z-index: 100;">
                <div class="carousel-dot active" data-index="0" style="width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s;"></div>
                <div class="carousel-dot" data-index="1" style="width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s;"></div>
                <div class="carousel-dot" data-index="2" style="width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s;"></div>
                <div class="carousel-dot" data-index="3" style="width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s;"></div>
                <div class="carousel-dot" data-index="4" style="width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s;"></div>
                <div class="carousel-dot" data-index="5" style="width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s;"></div>
            </div>
        </div>
    </div>

    <!-- 每日一题 -->
    <div style="margin-bottom: 25px;">
        <?php
        // 每日一题算法：基于日期选择题目
        // 使用日期作为种子，确保同一天内题目不变
        $today = date('Y-m-d');
        $seed = crc32($today);

        // 获取可用题目总数（排除已禁用、选择题/判断题、以及挂过任意竞赛的题目）
        $now = date("Y-m-d H:i", time());
        $problem_count_result = mysql_query_cache("select count(*) as count from `problem` p where p.defunct='N' and p.problem_id>0
            and p.problem_type not in ('choice_single','choice_multi','judge')
            and p.problem_id not in (select problem_id from contest_problem cp join contest c on cp.contest_id=c.contest_id where c.title like '%真题%')");
        $total_problems = $problem_count_result[0]['count'] ?? 0;

        $daily_problem = null;
        if ($total_problems > 0) {
            // 使用日期种子计算偏移量
            mt_srand($seed);
            $offset = mt_rand(0, max(0, $total_problems - 1));

            // 获取当日题目，用日期种子取对应偏移量的题目
            // offset 直接内联：pdo_query 用 execute($args) 会把绑定参数当字符串，
            // OFFSET '155' 在 MySQL 下会语法报错，故 intval 后内联（与 contest.php 分页写法一致）
            $daily_problem_result = mysql_query_cache("select p.problem_id, p.title, p.accepted, p.submit, p.source
                from problem p
                where p.defunct='N' and p.problem_id>0
                and p.problem_type not in ('choice_single','choice_multi','judge')
                and p.problem_id not in (select problem_id from contest_problem cp join contest c on cp.contest_id=c.contest_id where c.title like '%真题%')
                order by p.problem_id
                limit 1 offset " . intval($offset));

            if (!empty($daily_problem_result)) {
                $daily_problem = $daily_problem_result[0];
            }
        }
        ?>
        <?php if ($daily_problem) { ?>
        <div class="ui card" style="width: 100%; border-radius: 12px; border: 1px solid #e8e8e8; box-shadow: 0 4px 16px rgba(0,0,0,0.08);">
            <div class="content" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); border-radius: 12px 12px 0 0;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center;">
                        <i class="calendar alternate outline icon" style="font-size: 2em; color: #e74c3c;"></i>
                        <div style="margin-left: 15px;">
                            <div style="font-size: 1.1em; font-weight: 600; color: #c0392b;">每日一题</div>
                            <div style="font-size: 0.9em; color: #7f8c8d;"><?php echo $today; ?></div>
                        </div>
                    </div>
                    <span class="ui label" style="background: #e74c3c; color: white;">推荐</span>
                </div>
            </div>
            <div class="content">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div style="flex: 1; min-width: 200px;">
                        <a href="problem.php?id=<?php echo $daily_problem['problem_id']; ?>" style="font-size: 1.3em; font-weight: 600; color: #333; text-decoration: none;">
                            <?php echo htmlentities($daily_problem['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <?php if ($daily_problem['source']) { ?>
                        <div style="margin-top: 8px; color: #7f8c8d; font-size: 0.9em;">
                            <i class="tag icon"></i> <?php echo htmlentities($daily_problem['source'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <?php } ?>
                        <div style="margin-top: 12px; display: flex; gap: 20px;">
                            <span style="color: #27ae60; font-weight: 600;">
                                <i class="checkmark icon"></i> 通过: <?php echo number_format($daily_problem['accepted']); ?>
                            </span>
                            <span style="color: #3498db; font-weight: 600;">
                                <i class="send icon"></i> 提交: <?php echo number_format($daily_problem['submit']); ?>
                            </span>
                            <?php if ($daily_problem['submit'] > 0) { ?>
                            <span style="color: #9b59b6; font-weight: 600;">
                                <i class="percent icon"></i> 通过率: <?php echo round($daily_problem['accepted'] / $daily_problem['submit'] * 100, 1); ?>%
                            </span>
                            <?php } ?>
                        </div>
                    </div>
                    <a href="problem.php?id=<?php echo $daily_problem['problem_id']; ?>" class="ui button large" style="background: #e74c3c; color: white;">
                        开始挑战 <i class="right arrow icon"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- 系统统计数据卡片 -->
    <?php
    // 统计数据放大倍数 - 设置为2表示显示2倍数据
    if(!isset($OJ_STATS_MULTIPLIER)) $OJ_STATS_MULTIPLIER=1;

    // 缓存配置 - 统计数据缓存60秒，避免频繁刷新数据库
    $stats_cache_ttl = 60; // 缓存过期时间（秒）
    $stats_cache_key = $OJ_NAME . '_stats_cache';

    // 尝试从session获取缓存数据
    $use_cache = false;
    $cached_data = null;
    if (isset($_SESSION[$stats_cache_key])) {
        $cached_data = $_SESSION[$stats_cache_key];
        if (isset($cached_data['timestamp']) && (time() - $cached_data['timestamp'] < $stats_cache_ttl)) {
            $use_cache = true;
        }
    }

    if ($use_cache && $cached_data) {
        // 使用缓存数据
        $user_count = $cached_data['user_count'];
        $problem_count = $cached_data['problem_count'];
        $submit_today = $cached_data['submit_today'];
        $online_count = $cached_data['online_count'];
    } else {
        // 重新查询数据库
        // 获取总用户数
        $user_count_result = mysql_query_cache("select count(*) as count from `users` where defunct='N'");
        $user_count = $user_count_result[0]['count'] ?? 0;
        // 获取题目总数
        $problem_count_result = mysql_query_cache("select count(*) as count from `problem` where defunct='N'");
        $problem_count = $problem_count_result[0]['count'] ?? 0;
        // 获取今日提交数
        $submit_today_result = mysql_query_cache("select count(*) as count from `solution` where DATE(in_date)=CURDATE()");
        $submit_today = $submit_today_result[0]['count'] ?? 0;

        // 应用放大倍数
        $user_count = intval($user_count * $OJ_STATS_MULTIPLIER);
        $problem_count = intval($problem_count);
        $submit_today = intval($submit_today + $OJ_STATS_MULTIPLIER);

        // 模拟在线人数（零负载方案）
        // 基于用户总数的 3%-8% 作为在线人数，加上一些随机波动
        $base_online = intval($user_count * 0.05); // 基础值 5%
        $random_factor = rand(70, 130) / 100; // 70%-130% 随机波动
        $online_count = max(1, intval($base_online * $random_factor));

        // 确保在线人数看起来合理
        if ($user_count > 0 && $online_count < 5) {
            $online_count = rand(5, min(15, $user_count));
        }

        // 保存到session缓存
        $_SESSION[$stats_cache_key] = [
            'timestamp' => time(),
            'user_count' => $user_count,
            'problem_count' => $problem_count,
            'submit_today' => $submit_today,
            'online_count' => $online_count
        ];
    }
    ?>
    <div style="margin-bottom: 25px;">
        <div class="ui four cards">
            <div class="ui card" style="border-radius: 12px; border: 1px solid #e8e8e8; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                <div class="content" style="text-align: center; padding: 24px 20px;">
                    <i class="users icon" style="font-size: 2.2em; color: #667eea;"></i>
                    <div class="header" style="margin-top: 12px; font-size: 1.8em; font-weight: 600; color: #333;">
                        <?php echo number_format($user_count); ?>
                    </div>
                    <div class="meta" style="margin-top: 6px; color: #666; font-size: 0.95em;">
                        注册用户
                    </div>
                </div>
            </div>
            <div class="ui card" style="border-radius: 12px; border: 1px solid #e8e8e8; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                <div class="content" style="text-align: center; padding: 24px 20px;">
                    <i class="desktop icon" style="font-size: 2.2em; color: #764ba2;"></i>
                    <div class="header" style="margin-top: 12px; font-size: 1.8em; font-weight: 600; color: #333;">
                        <?php echo $online_count; ?>
                    </div>
                    <div class="meta" style="margin-top: 6px; color: #666; font-size: 0.95em;">
                        在线人数
                    </div>
                </div>
            </div>
            <div class="ui card" style="border-radius: 12px; border: 1px solid #e8e8e8; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                <div class="content" style="text-align: center; padding: 24px 20px;">
                    <i class="book icon" style="font-size: 2.2em; color: #f0932b;"></i>
                    <div class="header" style="margin-top: 12px; font-size: 1.8em; font-weight: 600; color: #333;">
                        <?php echo number_format($problem_count); ?>
                    </div>
                    <div class="meta" style="margin-top: 6px; color: #666; font-size: 0.95em;">
                        题目总数
                    </div>
                </div>
            </div>
            <div class="ui card" style="border-radius: 12px; border: 1px solid #e8e8e8; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                <div class="content" style="text-align: center; padding: 24px 20px;">
                    <i class="send icon" style="font-size: 2.2em; color: #4ecdc4;"></i>
                    <div class="header" style="margin-top: 12px; font-size: 1.8em; font-weight: 600; color: #333;">
                        <?php echo number_format($submit_today); ?>
                    </div>
                    <div class="meta" style="margin-top: 6px; color: #666; font-size: 0.95em;">
                        今日提交
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ui three column grid">
        <div class="eleven wide column">
            <?php
                        $sql_news = "select * FROM `news` WHERE `defunct`!='Y' AND `title`!='faqs.cn' ORDER BY `importance` desc,`time` DESC LIMIT 10";
                        $result_news = mysql_query_cache( $sql_news );
                        if ( $result_news && !empty($result_news) ) {
                        ?>
            <h4 class="ui top attached block header" style="border-radius: 12px 12px 0 0;"><i class="ui info icon"></i><?php echo $MSG_NEWS;?></h4>
            <div class="ui bottom attached segment" style="border-radius: 0 0 12px 12px;">
                <table class="ui very basic table">
                    <thead>
                        <tr>
                            <th><?php echo $MSG_TITLE;?></th>
                            <th><?php echo $MSG_CONTENTS;?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ( $result_news as $row ) {
                                $content_preview = mb_strlen($row["content"]) > 60 ? mb_substr($row["content"], 0, 60) . '...' : $row["content"];
                                echo "<tr>" . "<td style='white-space:nowrap;'>"
                                    . "<a href=\"viewnews.php?id=" . $row["news_id"] . "\" style='color: #333;'>"
                                    . $row["title"] . "</a></td>"
                                    . "<td style='color: #888;'>" . htmlspecialchars($content_preview, ENT_QUOTES, 'UTF-8') . "</td>" . "</tr>";
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
function mask_text($text) {
    if (empty($text)) return $text;
    $len = mb_strlen($text, 'UTF-8');
    if ($len <= 2) {
        return mb_substr($text, 0, 1, 'UTF-8') . '*';
    } elseif ($len <= 4) {
        return mb_substr($text, 0, 1, 'UTF-8') . '**' . mb_substr($text, -1, 1, 'UTF-8');
    } else {
        return mb_substr($text, 0, 2, 'UTF-8') . '****' . mb_substr($text, -2, 2, 'UTF-8');
    }
}

$month_id=mysql_query_cache("select solution_id from solution where  in_date<date_add(curdate(),interval -1 month) order by solution_id desc limit 1;");
if(!empty( $month_id) && isset($month_id[0][0]) ) $month_id=$month_id[0][0];else $month_id=0;
$view_month_rank=mysql_query_cache("select user_id,nick,count(distinct(problem_id)) ac from solution where solution_id>$month_id and problem_id>0  $not_in_noip_contests and user_id not in (".$OJ_RANK_HIDDEN.")  and result=4 group by user_id,nick order by ac desc limit 10");
            if ( !empty($view_month_rank) ) {
        ?>
            <h4 class="ui top attached block header" style="border-radius: 12px 12px 0 0; margin-top: 20px;"><i class="ui star icon"></i><?php echo "本月之星"?></h4>
            <div class="ui bottom attached segment" style="border-radius: 0 0 12px 12px;">
                <table class="ui very basic center aligned table" style="table-layout: fixed; ">
                    <thead>
                        <tr>
                            <th>用户名（学号）</th>
                            <th>昵称</th>
                            <th style="width: 100px;">AC数量</th>
                        </tr>
                    </thead>
                    <tbody>
        <?php
                            foreach ( $view_month_rank as $row ) {
                                $user_id = htmlentities($row[0],ENT_QUOTES,"UTF-8");
                                $nick = htmlentities($row[1],ENT_QUOTES,"UTF-8");
                                
                                if (!$is_logged_in) {
                                    $display_user_id = mask_text($user_id);
                                    $display_nick = mask_text($nick);
                                    $user_link = 'javascript:void(0);';
                                } else {
                                    $display_user_id = $user_id;
                                    $display_nick = $nick;
                                    $user_link = 'userinfo.php?user=' . $user_id;
                                }
                                
                                echo "<tr>".
                                        "<td><a target='_blank' href='{$user_link}' style='color: #333;'>⭐{$display_user_id}⭐</a></td>".
                                        "<td>{$display_nick}</td>".
                                        "<td style='color: #667eea; font-weight: 600;'>".htmlentities($row[2],ENT_QUOTES,"UTF-8")."</td>".
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

        </div>
        <div class="right floated five wide column">
            <!-- 热门题目/未解之谜 -->
            <h4 class="ui top attached block header" style="border-radius: 12px 12px 0 0;"><i class="ui fire icon" style="color: #f0932b;"></i> <?php echo $is_logged_in ? $MSG_RECENT_PROBLEM : "热门题目";?> </h4>
            <div class="ui bottom attached segment" style="border-radius: 0 0 12px 12px;">
                <table class="ui very basic center aligned table">
                    <thead>
                        <tr>
                            <th width="65%"><?php echo $MSG_TITLE;?></th>
                            <th width="35%"><?php echo $is_logged_in ? $MSG_TIME : "通过人数";?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        if($is_logged_in) {
                            // 登录用户显示"未解之谜"
                            $noip_problems=array_merge(...mysql_query_cache("select problem_id from contest c left join contest_problem cp on start_time<'$now' and end_time>'$now' and  (c.title like ? or (c.contest_type & 20) >0) and c.contest_id=cp.contest_id","%$OJ_NOIP_KEYWORD%"));
                            $noip_problems=array_unique($noip_problems);
                            $user_id=$_SESSION[$OJ_NAME."_user_id"];
                            $sql_problems = "select p.problem_id,title,max_in_date from (select problem_id,min(result) best,max(in_date) max_in_date from solution
                                    where user_id=? and result>=4 and problem_id>0 group by problem_id ) s inner join problem p on s.problem_id=p.problem_id
                                 where s.best>4 and p.problem_type not in ('choice_single','choice_multi','judge')
                                 and p.problem_id not in (select problem_id from contest_problem cp join contest c on cp.contest_id=c.contest_id where c.title like '%真题%')
                                 order by max_in_date desc  LIMIT 5";
                            $result_problems = mysql_query_cache( $sql_problems, $user_id );
                            if ( !empty($result_problems) ) {
                                $i = 1;
                                foreach ( $result_problems as $row ) {
                                    if(in_array(strval($row['problem_id']),$noip_problems)) continue;
                                    echo "<tr>"."<td style='text-align: left;'>"
                                        ."<a href=\"problem.php?id=".$row["problem_id"]."\" style='color: #333;'>"
                                        .$row["title"]."</a></td>"
                                        ."<td style='color: #888;'>".substr($row["max_in_date"],5,5)."</td>"."</tr>";
                                }
                            }
                        } else {
                            // 游客显示"热门题目"（按通过人数排序）
                            $sql_hot = "select p.problem_id,p.title,count(distinct s.user_id) as ac
                                         from problem p
                                         left join solution s on p.problem_id=s.problem_id and s.result=4
                                         where p.defunct='N' and p.problem_id>0
                                         and p.problem_type not in ('choice_single','choice_multi','judge')
                                         and p.problem_id not in (select problem_id from contest_problem cp join contest c on cp.contest_id=c.contest_id where c.title like '%真题%')
                                         group by p.problem_id,p.title
                                         having ac>0
                                         order by ac desc
                                         LIMIT 5";
                            $result_hot = mysql_query_cache( $sql_hot );
                            if ( !empty($result_hot) ) {
                                foreach ( $result_hot as $row ) {
                                    echo "<tr>"."<td style='text-align: left;'>"
                                        ."<a href=\"problem.php?id=".$row["problem_id"]."\" style='color: #333;'>"
                                        ."<span class='ui mini label' style='background: #667eea15; color: #667eea; margin-right: 6px;'>热门</span>"
                                        .$row["title"]."</a></td>"
                                        ."<td style='color: #667eea; font-weight: 600;'>".number_format($row["ac"])."</td>"."</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' style='color: #999;'>暂无热门题目</td></tr>";
                            }
                        }
                    ?>
                    </tbody>
                </table>
            </div>

            <!-- 最新题目 -->
            <h4 class="ui top attached block header" style="border-radius: 12px 12px 0 0; margin-top: 20px;"><i class="ui clock icon" style="color: #4ecdc4;"></i> 最新题目 </h4>
            <div class="ui bottom attached segment" style="border-radius: 0 0 12px 12px;">
                <table class="ui very basic center aligned table">
                    <thead>
                        <tr>
                            <th width="65%">题目</th>
                            <th width="35%">发布时间</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $sql_new = "select problem_id,title,in_date from problem p where p.defunct='N' and p.problem_id>0
                            and p.problem_type not in ('choice_single','choice_multi','judge')
                            and p.problem_id not in (select problem_id from contest_problem cp join contest c on cp.contest_id=c.contest_id where c.title like '%真题%')
                            order by p.problem_id desc LIMIT 5";
                        $result_new = mysql_query_cache( $sql_new );
                        if ( !empty($result_new) ) {
                            foreach ( $result_new as $row ) {
                                echo "<tr>"."<td style='text-align: left;'>"
                                    ."<a href=\"problem.php?id=".$row["problem_id"]."\" style='color: #333;'>"
                                    ."<span class='ui mini label' style='background: #4ecdc415; color: #4ecdc4; margin-right: 6px;'>新</span>"
                                    .$row["title"]."</a></td>"
                                    ."<td style='color: #888;'>".substr($row["in_date"],5,5)."</td>"."</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2' style='color: #999;'>暂无最新题目</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>

            <h4 class="ui top attached block header" style="border-radius: 12px 12px 0 0; margin-top: 20px;"><i class="ui search icon"></i><?php echo $MSG_SEARCH;?></h4>
            <div class="ui bottom attached segment" style="border-radius: 0 0 12px 12px;">
                <form action="problem.php" method="get">
                    <div class="ui search" style="width: 100%; ">
                        <div class="ui left icon input" style="width: 100%; ">
                            <input class="prompt" style="width: 100%; border-radius: 8px;" type="text" placeholder="<?php echo $MSG_PROBLEM_ID ;?> …" name="id">
                            <i class="search icon"></i>
                        </div>
                        <div class="results" style="width: 100%; "></div>
                    </div>
                </form>
            </div>
            <h4 class="ui top attached block header" style="border-radius: 12px 12px 0 0; margin-top: 20px;"><i class="ui calendar icon"></i><?php echo $MSG_RECENT_CONTEST ;?></h4>
            <div class="ui bottom attached center aligned segment" style="border-radius: 0 0 12px 12px;">
                <table class="ui very basic center aligned table">
                    <thead>
                        <tr>
                            <th><?php echo $MSG_CONTEST_NAME;?></th>
                            <th style="width: 100px;"><?php echo $MSG_START_TIME;?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $sql_contests = "select * FROM `contest` where defunct='N' ORDER BY `contest_id` DESC LIMIT 5";
                        $result_contests = mysql_query_cache( $sql_contests );
                        if ( $result_contests ) {
                            $i = 1;
                            foreach ( $result_contests as $row ) {
                                echo "<tr>"."<td style='text-align: left;'>"
                                    ."<a href=\"contest.php?cid=".$row["contest_id"]."\" style='color: #333;'>"
                                    .$row["title"]."</a></td>"
                                    ."<td style='color: #888;'>".substr($row["start_time"],5,5)."</td>"."</tr>";
                            }
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
    <script>
        const slides = document.querySelectorAll('#featureCarousel .carousel-slide');
        const dots = document.querySelectorAll('#featureCarousel .carousel-dot');
        const prevBtn = document.querySelector('#featureCarousel .carousel-prev');
        const nextBtn = document.querySelector('#featureCarousel .carousel-next');
        let currentIndex = 0;
        let autoPlayInterval;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.style.opacity = (i === index) ? '1' : '0';
                slide.style.pointerEvents = (i === index) ? 'auto' : 'none';
            });
            dots.forEach((dot, i) => {
                dot.style.background = (i === index) ? 'rgba(255,255,255,1)' : 'rgba(255,255,255,0.5)';
                dot.style.transform = (i === index) ? 'scale(1.2)' : 'scale(1)';
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

        // 初始化显示第一张
        showSlide(0);

        // 自动播放，5秒切换一次
        autoPlayInterval = setInterval(nextSlide, 5000);

        // 点击指示点
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                const targetIndex = parseInt(dot.dataset.index);
                if (targetIndex !== currentIndex) {
                    currentIndex = targetIndex;
                    showSlide(currentIndex);
                    clearInterval(autoPlayInterval);
                    autoPlayInterval = setInterval(nextSlide, 5000);
                }
            });
        });

        // 左右箭头点击
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                clearInterval(autoPlayInterval);
                autoPlayInterval = setInterval(nextSlide, 5000);
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                clearInterval(autoPlayInterval);
                autoPlayInterval = setInterval(nextSlide, 5000);
            });
        }

        // 鼠标悬停暂停自动播放
        const carousel = document.querySelector('#featureCarousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));
            carousel.addEventListener('mouseleave', () => autoPlayInterval = setInterval(nextSlide, 5000));
        }

        // 箭头悬停效果
        if (prevBtn) {
            prevBtn.addEventListener('mouseenter', () => prevBtn.style.background = 'rgba(255,255,255,0.35)');
            prevBtn.addEventListener('mouseleave', () => prevBtn.style.background = 'rgba(255,255,255,0.2)');
        }
        if (nextBtn) {
            nextBtn.addEventListener('mouseenter', () => nextBtn.style.background = 'rgba(255,255,255,0.35)');
            nextBtn.addEventListener('mouseleave', () => nextBtn.style.background = 'rgba(255,255,255,0.2)');
        }
    </script>
