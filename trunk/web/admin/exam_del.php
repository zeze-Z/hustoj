<?php
require_once("admin-header.php");

$eid = intval($_GET['eid'] ?? 0);
if (!$eid) {
    header("Location: exam_list.php?msg=参数错误");
    exit;
}

// 软删除试卷（保留历史数据用于审计）
pdo_query("UPDATE exam SET defunct='Y' WHERE exam_id=?", $eid);

// 如需物理删除关联数据，可启用以下语句（不建议）
// pdo_query("DELETE FROM exam_problem WHERE exam_id=?", $eid);
// pdo_query("DELETE FROM exam_attend WHERE exam_id=?", $eid);
// pdo_query("DELETE FROM exam_result WHERE exam_id=?", $eid);

header("Location: exam_list.php?msg=删除成功");
exit;
?>