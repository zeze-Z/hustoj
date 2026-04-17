<?php
require_once("admin-header.php");
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$result = ['ok' => false, 'error' => 'Unknown action'];

try {
    switch ($action) {
        case 'save_exam': {
            $data = json_decode(file_get_contents('php://input'), true);
            $eid = intval($data['eid'] ?? 0);
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $total_score = intval($data['total_score'] ?? 100);
            $duration_min = intval($data['duration_min'] ?? 60);
            $start_time = $data['start_time'] ?? date('Y-m-d H:i:s');
            $end_time = $data['end_time'] ?? date('Y-m-d H:i:s', strtotime('+7 days'));
            $school_id = intval($data['school_id'] ?? 0) ?: null;
            $is_public = ($data['is_public'] ?? 'Y') == 'Y' ? 'Y' : 'N';

            if ($title === '') {
                $result = ['ok' => false, 'error' => '标题不能为空'];
                break;
            }

            if ($eid > 0) {
                $sql = "UPDATE exam SET title=?,description=?,total_score=?,duration_min=?,start_time=?,end_time=?,school_id=?,is_public=? WHERE exam_id=?";
                pdo_query($sql, $title, $description, $total_score, $duration_min, $start_time, $end_time, $school_id, $is_public, $eid);
                $result = ['ok' => true, 'eid' => $eid];
            } else {
                $sql = "INSERT INTO exam(title,description,total_score,duration_min,start_time,end_time,school_id,is_public,creator_id) VALUES(?,?,?,?,?,?,?,?,?)";
                pdo_query($sql, $title, $description, $total_score, $duration_min, $start_time, $end_time, $school_id, $is_public, 'admin');
                $result = ['ok' => true, 'eid' => intval(@reset(pdo_query("SELECT LAST_INSERT_ID()")[0]))];
            }
            break;
        }

        case 'search_problems': {
            $kw = trim($_GET['kw'] ?? '');
            $type = trim($_GET['type'] ?? '');
            $level = trim($_GET['level'] ?? '');

            $cond = "defunct='N' AND is_public='Y'";
            $params = [];
            if ($kw !== '') {
                $cond .= " AND (title LIKE ? OR source LIKE ?)";
                $params[] = "%$kw%";
                $params[] = "%$kw%";
            }
            if ($type !== '') { $cond .= " AND problem_type=?"; $params[] = $type; }
            if ($level !== '') { $cond .= " AND level=?"; $params[] = $level; }

            $sql = "SELECT problem_id, title, source, level, problem_type FROM problem WHERE $cond ORDER BY problem_id DESC LIMIT 100";
            $all_rows = pdo_query($sql, ...$params);
            $result = $all_rows ?: [];
            break;
        }

        case 'add_problems': {
            $data = json_decode(file_get_contents('php://input'), true);
            $exam_id = intval($data['exam_id'] ?? 0);
            $problem_ids = $data['problem_ids'] ?? [];
            if (!$exam_id || empty($problem_ids)) {
                $result = ['ok' => false, 'error' => '参数错误'];
                break;
            }
            $max_row = pdo_query("SELECT COALESCE(MAX(num),0) FROM exam_problem WHERE exam_id=?", $exam_id);
            $num = intval($max_row[0][0]);

            foreach ($problem_ids as $pid) {
                $pid = intval($pid);
                if (!$pid) continue;
                $exist = pdo_query("SELECT ep_id FROM exam_problem WHERE exam_id=? AND problem_id=?", $exam_id, $pid);
                if (!empty($exist)) continue;
                $prob = pdo_query("SELECT problem_type FROM problem WHERE problem_id=?", $pid);
                $score = (!empty($prob) && $prob[0]['problem_type'] != 'programming') ? 5 : 10;
                $num++;
                pdo_query("INSERT INTO exam_problem(exam_id, problem_id, score, num) VALUES(?,?,?,?)", $exam_id, $pid, $score, $num);
            }
            $result = ['ok' => true];
            break;
        }

        case 'remove_problem': {
            $data = json_decode(file_get_contents('php://input'), true);
            $ep_id = intval($data['ep_id'] ?? 0);
            if ($ep_id > 0) {
                pdo_query("DELETE FROM exam_problem WHERE ep_id=?", $ep_id);
                $result = ['ok' => true];
            }
            break;
        }

        case 'update_score': {
            $data = json_decode(file_get_contents('php://input'), true);
            $ep_id = intval($data['ep_id'] ?? 0);
            $score = intval($data['score'] ?? 0);
            if ($ep_id > 0 && $score > 0) {
                pdo_query("UPDATE exam_problem SET score=? WHERE ep_id=?", $score, $ep_id);
                $result = ['ok' => true];
            }
            break;
        }

        default:
            $result = ['ok' => false, 'error' => 'Unknown action: ' . $action];
    }
} catch (Exception $e) {
    $result = ['ok' => false, 'error' => $e->getMessage()];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
