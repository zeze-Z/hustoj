<?php
$cache_time = 10;
$OJ_CACHE_SHARE = false;

require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/bbcode.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');
require_once('./include/setlang.php');
// 引入学校过滤函数
if (file_exists('./include/school.php')) {
    require_once('./include/school.php');
}

// 游客访问题目页面：检查是否已登录，未登录则跳转到登录页
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    $redirect = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'problemset.php';
    header("location:loginpage.php?redirect=" . urlencode($redirect));
    exit();
}
if (isset($OJ_LANG)) {
    require_once("./lang/$OJ_LANG.php");
}

$now = date("Y-m-d H:i", time());

if (isset($_GET['cid']))
    $ucid = "&cid=" . intval($_GET['cid']);
else
    $ucid = "";

$pr_flag = false;
$co_flag = false;
$view_is_true_question_contest = false;
$view_contest_problem_nav = array();
$view_latest_answer = '';

if (isset($_GET['id'])) {
    //practice
    $id = intval($_GET['id']);
    //require("oj-header.php");

    // 获取学校过滤条件
    $school_filter = getProblemSchoolFilter();
    $contest_filter = getContestSchoolFilter();

    // 调试信息
    $debug_user_id = isset($_SESSION[$OJ_NAME . '_' . 'user_id']) ? $_SESSION[$OJ_NAME . '_' . 'user_id'] : 'N/A';
    $debug_school_id = getCurrentUserSchoolId();
    $debug_is_admin = isset($_SESSION[$OJ_NAME . '_' . 'administrator']) ? 'yes' : 'no';
    $debug_free_practice = $OJ_FREE_PRACTICE ? 'yes' : 'no';
    error_log("DEBUG problem.php: user_id=$debug_user_id, school_id=$debug_school_id, is_admin=$debug_is_admin, free_practice=$debug_free_practice, school_filter=$school_filter");

    $sql = "select c.contest_id,c.title from contest c inner join contest_problem cp on c.contest_id=cp.contest_id and cp.problem_id=?  WHERE ( c.`end_time`>'$now' and c.defunct='N' ) or c.`private`='1' $contest_filter ";
    $used_in_contests = pdo_query($sql, $id);
    // 用于显示题目关联的比赛列表，也需要过滤

    if (isset($_SESSION[$OJ_NAME . '_' . 'administrator']) || isset($_SESSION[$OJ_NAME . '_' . 'problem_verifiter']) || isset($_SESSION[$OJ_NAME . '_' . 'contest_creator']) || isset($_SESSION[$OJ_NAME . '_' . 'problem_editor']))
        $sql = "SELECT * FROM `problem` A WHERE `problem_id`=?";
    else if ($OJ_FREE_PRACTICE)
        $sql = "SELECT * FROM `problem` A WHERE defunct='N' and `problem_id`=? $school_filter ";
    else
        $sql = "SELECT * FROM `problem` A WHERE `problem_id`=? AND `defunct`='N' $school_filter AND `problem_id` NOT IN (
				SELECT `problem_id` FROM `contest_problem` WHERE `contest_id` IN (
					SELECT `contest_id` FROM `contest` c WHERE ( c.`end_time`>'$now' and c.defunct='N' ) $contest_filter or c.`private`='1'
				)
			)";        //////////  people should not see the problem used in contest before they end by modifying url in browser address bar
    /////////   if you give students opportunities to test their result out side the contest ,they can bypass the penalty time of 20 mins for
    /////////   each non-AC sumbission in contest. if you give them opportunities to view problems before exam ,they will ask classmates to write
    /////////   code for them in advance, if you want to share private contest problem to practice you should modify the contest into public

    $pr_flag = true;
    error_log("DEBUG problem.php SQL: $sql, id=$id");
    $result = pdo_query($sql, $id);
    error_log("DEBUG problem.php result count: " . count($result));
} else if (isset($_GET['cid']) && isset($_GET['pid'])) {
    //contest
    $cid = intval($_GET['cid']);
    $pid = intval($_GET['pid']);
    require_once("contest-check.php");
    $view_is_true_question_contest = is_true_question_contest_title($view_title);
    $true_question_manager = isset($_SESSION[$OJ_NAME . '_' . 'administrator']) || isset($_SESSION[$OJ_NAME . '_' . "m$cid"]) || isset($_SESSION[$OJ_NAME . '_' . 'contest_creator']) || isset($_SESSION[$OJ_NAME . '_' . 'problem_editor']);
    if ($view_is_true_question_contest && isset($_SESSION[$OJ_NAME . '_' . 'user_id']) && !$true_question_manager) {
        $true_question_progress = contest_true_question_progress($cid, $_SESSION[$OJ_NAME . '_' . 'user_id']);
        if ($true_question_progress['completed']) {
            header("Location: contest.php?cid=$cid&auto=1");
            exit(0);
        }
    }
    if (isset($_SESSION[$OJ_NAME . '_' . 'administrator']) || isset($_SESSION[$OJ_NAME . '_' . 'contest_creator']) || isset($_SESSION[$OJ_NAME . '_' . 'problem_editor']))
        $sql = "SELECT langmask,private,defunct FROM `contest` WHERE `contest_id`=?";
    else
        $sql = "SELECT langmask,private,defunct FROM `contest` WHERE `defunct`='N' AND `contest_id`=? AND (`start_time`<='$now' AND ('$now'<`end_time` or private='N') ) $contest_filter";

    $result = pdo_query($sql, $cid);
    $rows_cnt = empty($result) ? 0 : count($result);
    if (empty($result) && !$OJ_FREE_PRACTICE && !isset($_SESSION[$OJ_NAME . '_administrator']) && !isset($_SESSION[$OJ_NAME . "_c" . $cid])) {
        $view_errors = "<title>$MSG_CONTEST</title><h2>No such Contest!</h2>";
        require("template/" . $OJ_TEMPLATE . "/error.php");
        exit(0);
    }

    $row = ($result[0]);
    $contest_ok = true;

    if ($row[1] && !isset($_SESSION[$OJ_NAME . '_' . 'c' . $cid]))
        $contest_ok = false;

    if ($row[2] == 'Y')
        $contest_ok = false;

    if (isset($_SESSION[$OJ_NAME . '_' . 'administrator']) || isset($_SESSION[$OJ_NAME . '_' . 'contest_creator']) || isset($_SESSION[$OJ_NAME . '_' . 'problem_editor']))
        $contest_ok = true;

    $ok_cnt = $rows_cnt == 1;
    $langmask = $row[0];

    if (!$contest_ok) {
        //not started
        $view_errors = "No such Contest!";
        require("template/" . $OJ_TEMPLATE . "/error.php");
        exit(0);
    } else {
        //started
//	$sql = "SELECT * FROM `problem` WHERE `defunct`='N' AND `problem_id`=(  // <- defunct problem not in list
//	$sql = "SELECT * FROM `problem` WHERE `problem_id`=(    // <-- defunct problem in list for contest but, not in list for practice
        $sql = "SELECT * FROM `problem` WHERE `problem_id`=(
			SELECT `problem_id` FROM `contest_problem` WHERE `contest_id`=? AND `num`=?
		)";

        $result = pdo_query($sql, $cid, $pid);
        $id = $result[0]['problem_id'];

        if ($view_is_true_question_contest && isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
            $nav_rows = pdo_query("SELECT cp.`num`, p.`title`, p.`problem_type` FROM `contest_problem` cp INNER JOIN `problem` p ON cp.`problem_id`=p.`problem_id` WHERE cp.`contest_id`=? ORDER BY cp.`num`", $cid);
            $submitted_rows = pdo_query("SELECT DISTINCT `num` FROM `solution` WHERE `contest_id`=? AND `user_id`=? AND `problem_id`>0 AND `num`>=0", $cid, $_SESSION[$OJ_NAME . '_' . 'user_id']);
            $submitted_nums = array();
            foreach ($submitted_rows as $submitted_row) {
                $submitted_nums[intval($submitted_row['num'])] = true;
            }
            foreach ($nav_rows as $nav_row) {
                $nav_num = intval($nav_row['num']);
                $view_contest_problem_nav[] = array(
                    'num' => $nav_num,
                    'title' => $nav_row['title'],
                    'problem_type' => $nav_row['problem_type'],
                    'answered' => isset($submitted_nums[$nav_num])
                );
            }
            $answer_rows = pdo_query("SELECT scu.`source` FROM `solution` s INNER JOIN `source_code_user` scu ON s.`solution_id`=scu.`solution_id` WHERE s.`contest_id`=? AND s.`user_id`=? AND s.`num`=? AND s.`problem_id`=? ORDER BY s.`solution_id` DESC LIMIT 1", $cid, $_SESSION[$OJ_NAME . '_' . 'user_id'], $pid, $id);
            if (!empty($answer_rows)) $view_latest_answer = $answer_rows[0]['source'];
        }
    }

    //public
    if (!$contest_ok) {
        $view_errors = "Not Invited!";
        require("template/" . $OJ_TEMPLATE . "/error.php");
        exit(0);
    }

    $co_flag = true;
} else {
    $view_errors = "<title>$MSG_NO_SUCH_PROBLEM</title><h2>$MSG_NO_SUCH_PROBLEM</h2>";
    require("template/" . $OJ_TEMPLATE . "/error.php");
    exit(0);
}

if (count($result) != 1) {
    $view_errors = "";

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        if (count($used_in_contests) > 0) {

            if (!(isset($OJ_EXAM_CONTEST_ID) || isset($OJ_ON_SITE_CONTEST_ID))) {
                $contest_name = htmlentities($used_in_contests[0][1], ENT_QUOTES, 'UTF-8');
                $contest_url = "contest.php?cid=" . intval($used_in_contests[0][0]);
                $view_errors = "
                    <div style='text-align:center; max-width:600px; margin:0 auto; padding:30px 20px;'>
                        <i class='lock icon' style='font-size:4em; color:#e74c3c; margin-bottom:20px;'></i>
                        <h2 style='color:#333; margin-bottom:15px;'>这道题正在比赛中</h2>
                        <p style='color:#666; font-size:1.1em; line-height:1.6; margin-bottom:25px;'>
                            该题目已经用于私有比赛 <strong>\"" . $contest_name . "\"</strong>，暂时无法单独练习。<br>
                            你可以进入比赛页面，在对应比赛中查看和作答。
                        </p>
                        <div style='display:flex; gap:15px; justify-content:center; flex-wrap:wrap;'>
                            <a href='" . $contest_url . "' class='ui button large' style='background:#e74c3c; color:white;'>
                                <i class='trophy icon'></i> 前往比赛
                            </a>
                            <a href='problemset.php' class='ui button large basic'>
                                <i class='list icon'></i> 浏览题单
                            </a>
                        </div>
                    </div>";
            }

        } else {
            $view_title = "<title>$MSG_NO_SUCH_PROBLEM!</title>";
            $view_errors .= "<h2>$MSG_NO_SUCH_PROBLEM!</h2>";
        }
    } else {
        $view_title = "<title>$MSG_NO_SUCH_PROBLEM!</title>";
        $view_errors .= "<h2>$MSG_NO_SUCH_PROBLEM!</h2>";
    }
    if (!(isset($_SESSION[$OJ_NAME . '_administrator']) || isset($_SESSION[$OJ_NAME . '_problem_editor']))) {
        require("template/" . $OJ_TEMPLATE . "/error.php");
        exit(0);
    }
} else {
    $row = $result[0];
    $view_title = $row['title'];
}
$flag = false;
if (isset($OJ_NOIP_KEYWORD) && $OJ_NOIP_KEYWORD) {
    //检查当前题目是不是在NOIP模式比赛中，如果是则不显示AC数量 2020.7.11 by ivan_zhou
    //$now =  date('Y-m-d H:i', time());
    $sql = "select 1 from `contest_problem` where (`problem_id`= ? ) and `contest_id` IN (select `contest_id` from `contest` where `start_time` < ? and `end_time` > ? and `title` like ?)";
    $rrs = pdo_query($sql, $id, $now, $now, "%$OJ_NOIP_KEYWORD%");
    $flag = !empty($rrs);
}
if ($flag || problem_locked($id, 28)) {
    $row['accepted'] = '<font color="red"> ? </font>';
    $row['submit'] = '<font color="red"> ? </font>';

    // 使用$OJ_NOIP_TISHI 条件语句确定是否显示提示信息
    if (isset($OJ_NOIP_HINT) && $OJ_NOIP_HINT) {
        //$row['hint'] = $MSG_NOIP_NOHINT;
    } else if (!(isset($_SESSION[$OJ_NAME . '_administrator']) || isset($_SESSION[$OJ_NAME . '_contest_creator']))) {
        $row['hint'] = $MSG_NOIP_NOHINT;
    }
}

$solution_file = "$OJ_DATA/$id/output.name";

if (file_exists($solution_file)) {
    // 读取文件内容
    $content = file_get_contents($solution_file);

    // 提取文件名部分（去掉扩展名）
    $filename = pathinfo($content, PATHINFO_FILENAME);

}
//if($row['spj']<=1) $row['description']=aaiw($row['description']);
/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/problem.php");
/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>
