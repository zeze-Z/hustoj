<?php
$OJ_CACHE_SHARE = false;
$cache_time = 30;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once("./include/my_func.inc.php");
require_once('./include/setlang.php');
require_once('./include/memcache.php');
// 引入学校过滤函数
if (file_exists('./include/school.php')) {
    require_once('./include/school.php');
}
$now = date('Y-m-d H:i', time());

$view_title = $MSG_RANKLIST;
if (!isset($OJ_RANK_HIDDEN)) $OJ_RANK_HIDDEN = "'admin','zhblue'";

$scope = "";
if (isset($_GET['scope']))
    $scope = $_GET['scope'];
if ($scope != "" && $scope != 'd' && $scope != 'w' && $scope != 'm')
    $scope = 'y';
$where = "";
$param = array();
if (isset($_GET['prefix'])) {
    $prefix = $_GET['prefix'];
    $where = "where user_id like ? and user_id not in (" . $OJ_RANK_HIDDEN . ") and defunct='N' ";
    array_push($param, $prefix . "%");
} else {
    $where = "where user_id not in (" . $OJ_RANK_HIDDEN . ") and defunct='N' ";
}
if (isset($_GET['group_name']) && !empty($_GET['group_name'])) {
    $group_name = $_GET['group_name'];
    $where .= "and group_name like ? ";
    array_push($param, $group_name . '%');
}
$rank = 0;

// 学校隔离过滤：超级管理员全量；有学校仅看本校；无学校的登录用户仅看本人；未登录无数据
$school_filter = '';
$school_param = array();
// scope 分支（日/周/月/年排行）子查询内使用的过滤，必须作用于子查询内部，避免全局取样截断本校数据
$scope_filter = '';
$self_user_id = '';
if (function_exists('getCurrentUserRole') && function_exists('getCurrentUserSchoolId')) {
    $role = getCurrentUserRole();
    if ($role !== 'super_admin') {
        $school_id = getCurrentUserSchoolId();
        if ($school_id) {
            // 本校用户：只看本校排行
            $school_filter = ' AND school_id = ' . intval($school_id);
            $scope_filter = ' and user_id in (select user_id from users where defunct=\'N\' and school_id = ' . intval($school_id) . ') ';
        } elseif ($role !== 'guest') {
            // 无学校的登录用户：仅能看本人
            $self_user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];
            $school_filter = ' AND user_id = ? ';
            $school_param[] = $self_user_id;
            $scope_filter = ' and user_id = ? ';
        } else {
            // 未登录：看不到任何数据
            $school_filter = ' AND 1=0';
            $scope_filter = ' and 1=0 ';
        }
    }
} else {
    // 学校相关函数不存在时，退回原有过滤逻辑（返回片段基于 users 表，子查询内同样适用）
    $school_filter = getUserSchoolFilter();
    if ($school_filter !== '') {
        $scope_filter = " and user_id in (select user_id from users where defunct='N' $school_filter) ";
    }
}

$sql = "SELECT count(1) as `mycount` FROM `users` where defunct='N' $school_filter";
if (!empty($school_param)) {
    $result = pdo_query($sql, $school_param);
} else {
    $result = mysql_query_cache($sql);
}
$row = $result[0];
$view_total = $row['mycount'];


if (isset($_GET ['start']))
    $rank = intval($_GET ['start']);

if (isset($OJ_LANG)) {
    require_once("./lang/$OJ_LANG.php");
}
$page_size = 50;
//$rank = intval ( $_GET ['start'] );
if ($rank < 0)
    $rank = 0;

$sql = "SELECT `user_id`,`nick`,`solved`,`submit`,group_name,starred FROM `users` $where $school_filter ORDER BY `solved` DESC,submit,reg_time  LIMIT  " . strval($rank) . ",$page_size";
// 占位符顺序与 SQL 一致：prefix/group_name（$where 内）在前，学校过滤（仅本人）在后
$sql_param = array_merge($param, $school_param);

if ($scope) {
    $s = "";
    switch ($scope) {
        case 'd':
            $s = date('Y') . '-' . date('m') . '-' . date('d');
            break;
        case 'w':
            $monday = mktime(0, 0, 0, date("m"), date("d") - (date("w") + 6) % 7, date("Y"));
            $s = date('Y-m-d', $monday);
            break;
        case 'm':
            $s = date('Y') . '-' . date('m') . '-01';;
            break;
        default :
            $s = date('Y') . '-01-01';
    }
    $last_id = mysql_query_cache("select solution_id from solution where  in_date<str_to_date('$s','%Y-%m-%d') order by solution_id desc limit 1;");
    if (!empty($last_id) && is_array($last_id)) $last_id = $last_id[0][0]; else $last_id = 0;
    if ($self_user_id !== '') {
        $view_total = pdo_query("select count(distinct(user_id)) from solution where solution_id>$last_id $scope_filter", $self_user_id)[0][0];
    } else {
        $view_total = mysql_query_cache("select count(distinct(user_id)) from solution where solution_id>$last_id $scope_filter")[0][0];
    }
    $sql = "SELECT users.`user_id`,`nick`,s.`solved`,t.`submit`,group_name,starred FROM `users`
                                        inner join
                                        (select count(distinct (problem_id)) solved ,user_id from solution
                                               where solution_id>$last_id and user_id not in (" . $OJ_RANK_HIDDEN . ") and problem_id>0 and result=4 and first_time=1 $scope_filter
					       group by user_id order by solved desc limit " . strval($rank) . ",$page_size) s
                                        on users.user_id=s.user_id
                                        inner join
                                        (select count( problem_id) submit ,user_id from solution
                                                where solution_id > $last_id $scope_filter
                                                group by user_id order by submit desc ) t
                                        on users.user_id=t.user_id
                                        and users.user_id not in (" . $OJ_RANK_HIDDEN . ") and defunct='N'
                                ORDER BY s.`solved` DESC,t.submit,reg_time  LIMIT  0,50
                         ";
    // 自本人场景：s、t 两个子查询各有一个占位符，参数按出现顺序提供两份
    $sql_param = ($self_user_id !== '') ? array($self_user_id, $self_user_id) : array();
//                      echo $sql;
}


if (!empty($sql_param)) {
    $result = pdo_query($sql, $sql_param);
} else {
    $result = mysql_query_cache($sql);
}
if ($result) $rows_cnt = count($result);
else $rows_cnt = 0;
$view_rank = array();
$i = 0;
for ($i = 0; $i < $rows_cnt; $i++) {

    $row = $result[$i];

    $rank++;

    $view_rank[$i][0] = $rank;
    $view_rank[$i][1] = "<a href='userinfo.php?user=" . htmlentities($row['user_id'], ENT_QUOTES, "UTF-8") . "'>" . $row['user_id'] . "</a>";
    if (isset($row['starred']) && $row['starred'] > 0) $view_rank[$i][1] = "⭐" . $view_rank[$i][1] . "<span title='用同名账户给hustoj项目加星，可以点亮此星' >⭐</span>";     //github starred rewarding
    $view_rank[$i][2] = "<div class=center>" . htmlentities($row['nick'], ENT_QUOTES, "UTF-8") . "</div>";
    $view_rank[$i][3] = "<div class=center>" . htmlentities($row['group_name'], ENT_QUOTES, "UTF-8") . "</div>";
    $view_rank[$i][4] = "<div class=center><a href='status.php?user_id=" . htmlentities($row['user_id'], ENT_QUOTES, "UTF-8") . "&jresult=4'>" . $row['solved'] . "</a>" . "</div>";
    $view_rank[$i][5] = "<div class=center><a href='status.php?user_id=" . htmlentities($row['user_id'], ENT_QUOTES, "UTF-8") . "'>" . $row['submit'] . "</a>" . "</div>";

    if ($row['submit'] == 0)
        $view_rank[$i][6] = "0.00%";
    else
        $view_rank[$i][6] = sprintf("%.02lf%%", 100 * $row['solved'] / $row['submit']);

//                      $i++;
}


/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/ranklist.php");
/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>


