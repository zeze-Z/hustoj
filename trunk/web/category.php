<?php
////////////////////////////Common head
$cache_time = 10;
$OJ_CACHE_SHARE = false;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/curl.php');
require_once('./include/memcache.php');
require_once('./include/setlang.php');
$view_title = "Welcome To Online Judge";
$result = false;
///////////////////////////MAIN	

/**
 * 获取题目分类信息并生成分类标签显示
 * 从problem表中获取所有有效的题目来源(source)，处理后生成带颜色标签的分类显示
 */
$view_category = "";
$sql = "select distinct source "
    . "FROM `problem` where defunct='N' order by source "
    . "LIMIT 5000";
$result = mysql_query_cache($sql);//mysql_escape_string($sql));
$category = array();

/**
 * 遍历查询结果，将每个题目的source字段按空格分割，并处理URL类型的分类
 */
foreach ($result as $row) {
    $cate = explode(" ", $row['source']);
    foreach ($cate as $cat) {
        $cat = trim($cat);
        if (mb_ereg("^http", $cat)) {
            $cat = get_domain($cat);
        }
        array_push($category, trim($cat));
    }

}
$category = array_unique($category);
sort($category);

/**
 * 根据查询结果生成分类标签HTML内容
 * 如果没有分类数据则显示提示信息，否则生成带颜色主题的分类标签链接
 */
if (!$result) {
    $view_category = "<h3>No Category Now!</h3>";
} else {
    // 扩展颜色主题
    $extended_colors = array(
        'primary' => '#667eea',
        'success' => '#27ae60',
        'info' => '#3498db',
        'warning' => '#f39c12',
        'danger' => '#e74c3c',
        'purple' => '#9b59b6',
        'teal' => '#1abc9c',
        'orange' => '#e67e22',
        'pink' => '#e91e63',
        'cyan' => '#00bcd4',
        'lime' => '#8bc34a',
        'indigo' => '#3f51b5'
    );
    $color_keys = array_keys($extended_colors);

    $view_category .= "<div style='word-wrap:break-word;'>";
    foreach ($category as $cat) {
        if (trim($cat) == "") continue;
        $hash_num = hexdec(substr(md5($cat), 0, 7));
        $color_idx = $hash_num % count($color_keys);
        $color_key = $color_keys[$color_idx];
        $color_val = $extended_colors[$color_key];

        // 使用Semantic UI标签样式但添加自定义颜色
        $view_category .= "<a class='ui label' style='display: inline-block; margin:6px; padding: 8px 16px; background: {$color_val}15; color: {$color_val}; border: 1px solid {$color_val}30; font-size: 0.95em; transition: all 0.2s; cursor: pointer;'
            onmouseover=\"this.style.background='{$color_val}25'; this.style.transform='scale(1.05)';\"
            onmouseout=\"this.style.background='{$color_val}15'; this.style.transform='scale(1)';\"
            href='problemset.php?search=" . htmlentities(urlencode($cat), ENT_QUOTES, 'utf-8') . "'>" . htmlentities($cat, ENT_QUOTES, 'utf-8') . "</a> ";
    }

    $view_category .= "</div>";
}

/////////////////////////Template
require("template/" . $OJ_TEMPLATE . "/category.php");
/////////////////////////Common foot
if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');

