<?php
require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');
require_once('./include/bbcode.php');
require_once('./include/memcache.php');
require_once('./include/setlang.php');
require_once('./include/curl.php');
$view_title = $MSG_SUBMIT;
$view_is_true_question_contest = false;
$view_problem_row = array();
$view_contest_problem_nav = array();
// 禁用提交页面验证码
$OJ_VCODE = false;
if (!isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    $redirect = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'index.php';
    // 如果是spa模式（在iframe中），移除&spa参数，避免跳转循环
    if (strpos($redirect, '&spa') !== false) {
        $redirect = str_replace('&spa', '', $redirect);
    }
    header("Location: loginpage.php?redirect=" . urlencode($redirect));
    exit(0);
}
$langmask = $OJ_LANGMASK;
$problem_id = 1000;
if (!empty($_SERVER['HTTP_REFERER'])) {
    $queryString = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
    if ($queryString) {
        // 将查询参数解析为关联数组
        parse_str($queryString, $queryParams);
        // 检查是否存在 cid 参数
        if (isset($queryParams['cid'])) {
            // 返回过滤后的 cid（例如：确保是整数）
            $sql = "select contest_type,end_time from contest where contest_id=?";
            $contest_type = pdo_query($sql, intval($queryParams['cid']));
            if (!empty($contest_type)) {
                $end_time = $contest_type[0][1];
                $contest_type = $contest_type[0][0];
            }
            //echo "[ $contest_type $end_time ]";
            if (($contest_type & 64) && strtotime($end_time) < time()) {
                $view_errors = "$MSG_FORBIDDEN.$MSG_UPSOLVING";
                require("template/$OJ_TEMPLATE/error.php");
                exit();
            }
        }
    }
}
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} else if (isset($_GET['cid']) && isset($_GET['pid'])) {
    $cid = intval($_GET['cid']);
    $pid = intval($_GET['pid']);
    require_once("contest-check.php");
    $view_is_true_question_contest = is_true_question_contest_title($view_title);
    $true_question_manager = isset($_SESSION[$OJ_NAME . '_' . 'administrator']) || isset($_SESSION[$OJ_NAME . '_' . "m$cid"]) || isset($_SESSION[$OJ_NAME . '_' . 'contest_creator']) || isset($_SESSION[$OJ_NAME . '_' . 'problem_editor']);
    if ($view_is_true_question_contest && !$true_question_manager) {
        $true_question_progress = contest_true_question_progress($cid, $_SESSION[$OJ_NAME . '_' . 'user_id']);
        if ($true_question_progress['completed']) {
            header("Location: contest.php?cid=$cid&auto=1");
            exit(0);
        }
    }
    $psql = "SELECT problem_id FROM contest_problem WHERE contest_id=? AND num=?";
    $data = pdo_query($psql, $cid, $pid);
    $row = $data[0];
    $problem_id = $row[0];
    if ($view_is_true_question_contest) {
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
    }
} else {
    $view_errors = "<h2>No Such Problem!</h2>";
    require("template/" . $OJ_TEMPLATE . "/error.php");
    exit(0);
}
$view_src = "";
$lastlang = 1;
if (isset($_GET['sid'])) {
    $sid = intval($_GET['sid']);
    $sql = "SELECT * FROM `solution` WHERE `solution_id`=?";
    $result = pdo_query($sql, $sid);
    $row = $result[0];
    $cid = intval($row['contest_id']);
    $sproblem_id = intval($row['problem_id']);
    $contest_id = $cid;
    if ($row && $row['user_id'] == $_SESSION[$OJ_NAME . '_' . 'user_id'])
        $ok = true;

    $need_check_using = true;
    if ($contest_id > 0) {
        $sql = "select start_time,end_time from contest where contest_id=?";
        $result = pdo_query($sql, $contest_id);
        if ($result) {
            $row = $result[0];
            $start_time = strtotime($row['start_time']);
            $end_time = strtotime($row['end_time']);
            $now = time();
            if ($end_time < $now) { // 当前提交，属于已经结束的比赛，考察是否有进行中的比赛在使用。
                $need_check_using = true;
            } else {            // 属于进行中的比赛，可以看
                $need_check_using = false;
            }
        }
    } else { //非比赛提交.考察是否有进行中的比赛在使用
        //	echo $now.'+'.$end_time;
        if (isset($_SESSION[$OJ_NAME . '_' . 'source_browser']))
            $need_check_using = false;
        else
            $need_check_using = true;
    }
    // 检查是否使用中
    //echo $now.'*'.$end_time;
    $now = date('Y-m-d H:i', time());
    $sql = "select contest_id from contest where contest_id in (select contest_id from contest_problem where problem_id=?) 
									and start_time < '$now' and end_time > '$now' ";
    if ($need_check_using) {
        //echo $sql;
        $result = pdo_query($sql, $sproblem_id);
        if (count($result) > 0 && !isset($_SESSION[$OJ_NAME . '_' . 'source_browser'])) {
            $view_errors = "<center>";
            $view_errors .= "<h3>$MSG_CONTEST_ID : " . $result[0][0] . "</h3>";
            $view_errors .= "<p> $MSG_SOURCE_NOT_ALLOWED_FOR_EXAM </p>";
            $view_errors .= "<br>";
            $view_errors .= "</center>";
            $view_errors .= "<br><br>";
            require("template/" . $OJ_TEMPLATE . "/error.php");
            exit(0);
        }

    }
    if (isset($_SESSION[$OJ_NAME . '_' . 'source_browser'])) {
        $ok = true;
    } else {


        if (isset($OJ_EXAM_CONTEST_ID)) {
            if ($cid < $OJ_EXAM_CONTEST_ID && !isset($_SESSION[$OJ_NAME . '_' . 'source_browser'])) {

                $view_errors = "<center>";
                $view_errors .= "<h3>$MSG_CONTEST_ID : " . $OJ_EXAM_CONTEST_ID . "+ </h3>";
                $view_errors .= "<p> $MSG_SOURCE_NOT_ALLOWED_FOR_EXAM </p>";
                $view_errors .= "<br>";
                $view_errors .= "</center>";
                $view_errors .= "<br><br>";
                require("template/" . $OJ_TEMPLATE . "/error.php");
                exit(0);
            }
        }
    }

    if ($ok == true) {
        $sql = "SELECT `source` FROM `source_code_user` WHERE `solution_id`=?";
        $result = pdo_query($sql, $sid);

        $row = $result[0];

        if ($row)
            $view_src = $row['source'];

        if (isset($cid) && $cid > 0) {

            $sql = "SELECT langmask FROM contest WHERE contest_id=?";

            $result = pdo_query($sql, $cid);
            $row = $result[0];

            if (count($row) > 0) {
                $_GET['langmask'] = $row['langmask'];
                $langmask = $row['langmask'];
            }
        }
        $sql = "select language from solution where solution_id=?";
        $result = pdo_query($sql, $sid);
        $row = $result[0];
        if ($row && str_contains($_SERVER['HTTP_REFERER'], "status.php"))
            $lastlang = intval($row['language']);   //Click on Edit from status.php
        else
            $lastlang = intval($_COOKIE['lastlang']);  // Switch Language from submitpage.php
    }
}

if (!$view_src && $view_is_true_question_contest && isset($cid) && isset($pid) && isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) {
    $last_rows = pdo_query("SELECT s.`solution_id`, s.`language`, scu.`source` FROM `solution` s INNER JOIN `source_code_user` scu ON s.`solution_id`=scu.`solution_id` WHERE s.`contest_id`=? AND s.`user_id`=? AND s.`num`=? AND s.`problem_id`=? ORDER BY s.`solution_id` DESC LIMIT 1", $cid, $_SESSION[$OJ_NAME . '_' . 'user_id'], $pid, $problem_id);
    if (!empty($last_rows)) {
        $view_src = $last_rows[0]['source'];
        $lastlang = intval($last_rows[0]['language']);
    }
}

if (isset($id))
    $problem_id = $id;

$view_sample_input = "1 2";
$view_sample_output = "3";
$spj = 0;
$sample_sql = "SELECT * FROM problem WHERE problem_id = ?";
$remote_oj = "";
if (isset($sample_sql)) {
    //echo $sample_sql;
    if (isset($_GET['id'])) {
        $result = pdo_query($sample_sql, $id);
    } else {
        $result = pdo_query($sample_sql, $problem_id);
    }
    if ($result == false) {
        $view_errors = "<h2>No Such Problem!</h2>";
        require("template/" . $OJ_TEMPLATE . "/error.php");
        exit(0);
    }
    $row = $result[0];
    $view_problem_row = $row;
    $view_sample_input = $row['sample_input'];
    $view_sample_output = $row['sample_output'];
    $problem_id = $row['problem_id'];
    $spj = $row['spj'];
    $remote_oj = $row['remote_oj'];
    if ($spj > 1) $OJ_ACE_EDITOR = false;
}

$solution_file = "$OJ_DATA/$problem_id/solution.name";
if (file_exists($solution_file)) {
    $solution_name = file_get_contents($solution_file);
} else {
    $solution_name = false;
}


if (!$view_src) {
    if (isset($_COOKIE['lastlang']) && $_COOKIE['lastlang'] != "undefined") {
        $lastlang = intval($_COOKIE['lastlang']);
    } else {
        $sql = "SELECT language FROM solution WHERE user_id=? ORDER BY solution_id DESC LIMIT 1";
        $result = pdo_query($sql, $_SESSION[$OJ_NAME . '_' . 'user_id']);

        if (count($result) > 0) {
            $lastlang = $result[0][0];
        } else {
            $lastlang = 1;   // 默认语言 default language : 0=C  1=C++ 3=Java  6=Python
        }
        //echo "last=$lastlang";
    }
    $template_file = "$OJ_DATA/$problem_id/template." . $language_ext[$lastlang];

    if (file_exists($template_file)) {
        $view_src = file_get_contents($template_file);
    } else if ($spj > 1 && file_exists("$OJ_DATA/$problem_id/template.c")) {
        $view_src = file_get_contents("$OJ_DATA/$problem_id/template.c");
    } else if ($spj == 2 && file_exists("$OJ_DATA/$problem_id/test.in")) {
        $total = file_get_contents("$OJ_DATA/$problem_id/test.in");
        $total = intval($total);
        $view_src = "";
        for ($i = 1; $i <= $total; $i++) {
            $view_src .= $i . "\n";
        }
    }
	if(($view_src=="") && isset($_SESSION[$OJ_NAME.'_administrator']) && file_exists("$OJ_DATA/$problem_id/Main.c")){
			//管理员自动加载可能的c标程
			$view_src = file_get_contents( "$OJ_DATA/$problem_id/Main.c" );
		    $lastlang = intval($_COOKIE['lastlang']);
	}else if(($view_src=="") && isset($_SESSION[$OJ_NAME.'_administrator']) && file_exists("$OJ_DATA/$problem_id/Main.cc")){
			//管理员自动加载可能的c++标程
			$view_src = file_get_contents( "$OJ_DATA/$problem_id/Main.cc" );
		    $lastlang = intval($_COOKIE['lastlang']);
	}

}

$sql = "SELECT count(1) FROM `solution` WHERE result<4";
$result = pdo_query($sql);

$row = $result[0];

// 禁用验证码，不根据提交数量自动启用
// if ($row[0] > 10) {
//     $OJ_VCODE = true;
// }

/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/submitpage.php");
/////////////////////////Common foot
?>
