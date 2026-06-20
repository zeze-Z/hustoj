<?php
/**
 * 根据是否有关键词POST请求设置缓存时间
 * 有关键词时缓存时间短，无关键词时缓存时间长
 */
if (isset($_POST['keyword']))
    $cache_time = 1;
else
    $cache_time = 10;

/**
 * 设置缓存共享标志，当前设置为false
 * 注释掉的代码原本用于在cid或my参数存在时禁用缓存
 */
$OJ_CACHE_SHARE = false;//!(isset($_GET['cid'])||isset($_GET['my']));

/**
 * 包含必要的系统文件
 * 包括缓存、数据库、内存缓存、自定义函数、常量和语言设置
 */
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/memcache.php');
require_once('./include/my_func.inc.php');
require_once('./include/const.inc.php');
require_once('./include/setlang.php');
require_once('./include/bbcode.php');
// 引入学校过滤函数
if (file_exists('./include/school.php')) {
    require_once('./include/school.php');
}

/**
 * 设置页面标题为竞赛标题
 */
$view_title = $MSG_CONTEST;

/**
 * 获取当前时间戳
 */
$now = time();

/**
 * 处理竞赛详情页面
 * 当存在cid参数时，显示特定竞赛的问题列表
 */
if (isset($_GET['cid'])) {

    require_once("contest-check.php");
    $view_is_true_question_contest = is_true_question_contest_title($view_title);

    if ($view_is_true_question_contest && isset($_SESSION[$OJ_NAME . '_' . 'user_id']) && !empty($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
        $true_question_user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
        $view_true_question_progress = contest_true_question_progress($cid, $true_question_user_id);
        if (isset($_GET['auto']) && $view_true_question_progress['next_num'] !== null) {
            $next_num = $view_true_question_progress['next_num'];
            // 查询下一题的题型，编程题跳转到submitpage.php，其他题型跳转到problem.php
            $next_type_row = pdo_query("SELECT p.`problem_type` FROM `contest_problem` cp INNER JOIN `problem` p ON cp.`problem_id`=p.`problem_id` WHERE cp.`contest_id`=? AND cp.`num`=?", $cid, $next_num);
            $next_problem_type = !empty($next_type_row) ? $next_type_row[0]['problem_type'] : '';
            if ($next_problem_type === 'programming') {
                $langmask_row = pdo_query("SELECT `langmask` FROM `contest` WHERE `contest_id`=?", $cid);
                $langmask = !empty($langmask_row) ? $langmask_row[0]['langmask'] : $OJ_LANGMASK;
                header("Location: submitpage.php?cid=$cid&pid=$next_num&langmask=$langmask");
            } else {
                header("Location: problem.php?cid=$cid&pid=$next_num");
            }
            exit(0);
        }
        if ($view_true_question_progress['completed']) {
            $view_true_question_completed = true;
            $view_true_question_score = contest_true_question_score($cid, $true_question_user_id);
        }
    } else if (isset($_GET['auto']) && $view_is_true_question_contest) {
        $redirect = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'contest.php?cid=' . $cid . '&auto=1';
        header("Location: loginpage.php?redirect=" . urlencode($redirect));
        exit(0);
    }

    /**
     * 查询竞赛相关问题信息
     * 使用内连接获取问题标题、ID、来源和竞赛问题编号
     */
    $sql = "select p.title,p.problem_id,p.source,p.problem_type,cp.num as pnum,cp.c_accepted accepted,cp.c_submit submit from problem p inner join contest_problem cp on p.problem_id = cp.problem_id and cp.contest_id=$cid order by cp.num";
    $result = mysql_query_cache($sql);
    $view_problemset = array();
    $pids = array_column($result, 'problem_id');
    if (!empty($pids)) $pids = implode(",", $pids);
    $cnt = 0;

    /**
     * 判断是否为NOIP竞赛或竞赛是否锁定
     * 检查竞赛是否在进行中且包含NOIP关键词或被锁定
     */
    $noip = (time() < $end_time) && (stripos($view_title, $OJ_NOIP_KEYWORD) !== false || contest_locked($cid, 16));
    $hide_others = contest_locked($cid, 8);

    /**
     * 管理员、竞赛管理员、源码浏览器或竞赛创建者不受NOIP限制
     */
    if (isset($_SESSION[$OJ_NAME . '_' . "administrator"]) ||
        isset($_SESSION[$OJ_NAME . '_' . "m$cid"]) ||
        isset($_SESSION[$OJ_NAME . '_' . "source_browser"]) ||
        isset($_SESSION[$OJ_NAME . '_' . "contest_creator"])
    ) $noip = false;

    /**
     * 遍历结果集，构建问题列表
     * 根据竞赛状态和用户权限设置问题显示内容
     */
    foreach ($result as $row) {
        $problem_type_map = array(
            'programming' => '编程题',
            'choice_single' => '单选题',
            'choice_multi' => '多选题',
            'judge' => '判断题'
        );
        $problem_type = isset($problem_type_map[$row['problem_type']]) ? $problem_type_map[$row['problem_type']] : $row['problem_type'];
        $col = 0;
        if (!$view_is_true_question_contest) {
            $view_problemset[$cnt][$col] = "";
            if (isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
                $ac = check_ac($cid, $cnt, $noip);
                $sub = "";
                if ($ac != "") $sub = "?";
                if ($noip)
                    $view_problemset[$cnt][$col] = "$sub";
                else
                    $view_problemset[$cnt][$col] = "$ac";

            }
            $col++;
        }

        $problem_label = $view_is_true_question_contest ? ($cnt + 1) : $PID[$cnt];
        if ($now < $end_time) { //竞赛进行中
            $view_problemset[$cnt][$col] = "<a href='problem.php?cid=$cid&pid=$cnt'>" . $problem_label . "</a>";
            $view_problemset[$cnt][$col + 1] = "<a href='problem.php?cid=$cid&pid=$cnt'>" . $row['title'] . "</a>";
        } else {               //竞赛结束
            //检查问题是否会在其他竞赛中使用
            $tpid = intval($row['problem_id']);
            $sql = "SELECT `problem_id` FROM `problem` WHERE `problem_id`=? AND `problem_id` IN (
				SELECT `problem_id` FROM `contest_problem` WHERE `contest_id` IN (
					SELECT `contest_id` FROM `contest` WHERE (`defunct`='N' AND now()<`start_time`)
				)
			)";

            $tresult = pdo_query($sql, $tpid);

            if (intval($tresult) != 0 && !isset($_SESSION[$OJ_NAME . '_' . "m$cid"])) {
                //如果问题将在其他私有竞赛中使用，不向其他教师和学生显示
                $view_problemset[$cnt][$col] = $problem_label; //竞赛结束后隐藏标题
                $view_problemset[$cnt][$col + 1] = '--using in another private contest--';
            } else {
                $view_problemset[$cnt][$col] = "<a href='problem.php?id=" . $row['problem_id'] . "'>" . $problem_label . "</a>";
                if ($contest_ok)
                    $view_problemset[$cnt][$col + 1] = "<a href='problem.php?cid=$cid&pid=$cnt'>" . $row['title'] . "</a>";
                else
                    $view_problemset[$cnt][$col + 1] = $row['title'];
            }
        }
        $col += 2;

        if ($view_is_true_question_contest) {
            $view_problemset[$cnt][$col] = htmlentities($problem_type, ENT_QUOTES, 'UTF-8');
            $col++;
        }

        //$view_problemset[$cnt][$col] = $row['source'];

        /**
         * 根据NOIP或隐藏设置决定是否显示接受和提交数量
         * 管理员不受限制
         */
        if (($noip || $hide_others) && !(isset($_SESSION[$OJ_NAME . 'm' . $cid]) || isset($_SESSION[$OJ_NAME . '_administrator']))) {
            $view_problemset[$cnt][$col] = "<span class=red>?</span>";
            $view_problemset[$cnt][$col + 1] = "<span class=red>?</span>";
        } else {
            $view_problemset[$cnt][$col] = $row['accepted'];
            $view_problemset[$cnt][$col + 1] = $row['submit'];
        }


        $cnt++;
    }
} else {
    /**
     * 处理竞赛列表页面
     * 当不存在cid参数时，显示竞赛列表
     */
    $page = 1;
    if (isset($_GET['page']))
        $page = intval($_GET['page']);

    $page_cnt = 25;
    $pstart = $page_cnt * $page - $page_cnt;
    $pend = $page_cnt;
    $keyword = "";
    $keyword_text = "";

    if (isset($_REQUEST['keyword'])) {
        $keyword_text = trim($_REQUEST['keyword']);
        if ($keyword_text !== "") {
            $keyword = "%" . $keyword_text . "%";
        }
    }

    //echo "$keyword";
    $mycontests = "";
    $wheremy = "";

    /**
     * 获取当前用户参与的竞赛列表
     * 用于显示"我的竞赛"功能
     */
    if (isset($_SESSION[$OJ_NAME . '_user_id'])) {
        $sql = "select distinct contest_id from solution where contest_id>0 and user_id=?";
        $result = pdo_query($sql, $_SESSION[$OJ_NAME . '_user_id']);

        foreach ($result as $row) {
            if (intval($row['contest_id']) > 0)
                $mycontests .= "," . $row['contest_id'];
        }

        $len = mb_strlen($OJ_NAME . '_');
        $user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];

        if ($user_id) {
            // 已登录的
            $sql = "SELECT * FROM `privilege` WHERE `user_id`=?";
            $result = pdo_query($sql, $user_id);

            // 刷新各种权限
            foreach ($result as $row) {
                if (isset($row['valuestr'])) {
                    $_SESSION[$OJ_NAME . '_' . $row['rightstr']] = $row['valuestr'];
                } else {
                    $_SESSION[$OJ_NAME . '_' . $row['rightstr']] = true;
                }
            }
            if (isset($_SESSION[$OJ_NAME . '_vip'])) {  // VIP mark can access all [VIP] marked contest
                $sql = "select contest_id from contest where title like '%[VIP]%'";
                $result = pdo_query($sql);
                foreach ($result as $row) {
                    $_SESSION[$OJ_NAME . '_c' . $row['contest_id']] = true;
                }
            };
        }

        foreach ($_SESSION as $key => $value) {
            if ((mb_substr($key, $len, 1) == 'm' || mb_substr($key, $len, 1) == 'c') && intval(mb_substr($key, $len + 1)) > 0) {
                //echo substr($key,1)."<br>";
                $mycontests .= "," . intval(mb_substr($key, $len + 1));
            }
        }

        //echo "=====>$mycontests<====";

        if (strlen($mycontests) > 0)
            $mycontests = substr($mycontests, 1);
        if (isset($_GET['my']) && $mycontests != "")
            if (isset($_GET['my'])) $wheremy = " and( c.contest_id in ($mycontests) or c.user_id='" . $_SESSION[$OJ_NAME . '_user_id'] . "')";
    }

    // 添加学校过滤条件
    $school_filter = getContestSchoolFilter();
    $where_sql = "c.defunct='N' $school_filter $wheremy";
    if ($keyword) {
        $where_sql .= " AND c.title LIKE ?";
    }

    $count_sql = "SELECT count(1) FROM contest c WHERE $where_sql";
    if ($keyword) {
        $rows = pdo_query($count_sql, $keyword);
    } else {
        $rows = pdo_query($count_sql);
    }
    $total = 0;
    if ($rows) {
        $total = intval($rows[0][0]);
    }

    $view_total_page = max(1, intval(ceil($total / $page_cnt)));

    $sql = "SELECT c.* FROM contest c WHERE $where_sql ORDER BY c.contest_id DESC";
    $sql .= " limit " . strval($pstart) . "," . strval($pend);

    if ($keyword) {
        $result = pdo_query($sql, $keyword);
    } else {
        $result = mysql_query_cache($sql);
    }

    $view_contest = array();
    $i = 0;

    /**
     * 遍历竞赛结果，构建竞赛列表
     * 根据竞赛状态（已结束、待开始、进行中）设置不同的显示内容
     */
    foreach ($result as $row) {
        $view_contest[$i][0] = $row['contest_id'];

        if (trim($row['title']) == "")
            $row['title'] = $MSG_CONTEST . $row['contest_id'];

        $contest_id = intval($row['contest_id']);
        $contest_href = "contest.php?cid=" . $contest_id;
        $is_manager_view = isset($_SESSION[$OJ_NAME . '_' . 'administrator']) ||
            isset($_SESSION[$OJ_NAME . '_' . 'm' . $contest_id]) ||
            isset($_SESSION[$OJ_NAME . '_' . 'contest_creator']) ||
            (isset($_SESSION[$OJ_NAME . '_' . 'user_id']) && $_SESSION[$OJ_NAME . '_' . 'user_id'] == $row['user_id']);
        $view_contest[$i][1] = "<a href='" . $contest_href . "'>" . $row['title'] . "</a>";
        $start_time = strtotime($row['start_time']);
        $end_time = strtotime($row['end_time']);
        $now = time();

        $length = $end_time - $start_time;
        $left = $end_time - $now;

        if ($end_time <= $now) {
            //已结束
            $view_contest[$i][2] = "<span class=text-muted>$MSG_Ended</span>" . " " . "<span class=text-muted>" . $row['end_time'] . "</span>";

        } else if ($now < $start_time) {
            //待开始
            $view_contest[$i][2] = "<span class=text-success>$MSG_Start</span>" . " " . $row['start_time'] . "&nbsp;";
            $view_contest[$i][2] .= "<span class=text-success>$MSG_TotalTime</span>" . " " . formatTimeLength($length);
        } else {
            //进行中
            $view_contest[$i][2] = "<span class=text-danger>$MSG_Running</span>" . " " . $row['start_time'] . "&nbsp;";
            $view_contest[$i][2] .= "<span class=text-danger>$MSG_LeftTime</span>" . " " . formatTimeLength($left) . "</span>";
        }

        $private = intval($row['private']);
        if ($private == 0)
            $view_contest[$i][4] = "<span class=text-primary>$MSG_Public</span>";
        else
            $view_contest[$i][5] = "<span class=text-danger>$MSG_Private</span>";

        $view_contest[$i][6] = $row['user_id'];

        $i++;
    }
}

/////////////////////////Template
/**
 * 根据参数加载相应的模板文件
 * 有cid参数时加载竞赛模板，否则加载竞赛集模板
 */
if (isset($_GET['cid']))
    require("template/" . $OJ_TEMPLATE . "/contest.php");
else
    require("template/" . $OJ_TEMPLATE . "/contestset.php");
/////////////////////////Common foot
/**
 * 包含缓存结束文件（如果存在）
 */
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>
