<?php
ini_set("display_errors", "On");  //set this to "On" for debugging  ,especially when no reason blank shows up.
require_once ("admin-header.php");
if (!(isset($_SESSION[$OJ_NAME . '_' . 'administrator']) || isset($_SESSION[$OJ_NAME . '_problem_importer']))) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}
if (isset($OJ_LANG)) {
    require_once ("../lang/$OJ_LANG.php");
}
require_once ("../include/const.inc.php");
require_once ("../include/my_func.inc.php");
if (file_exists("../include/school.php")) {
    require_once ("../include/school.php");
}

function get_extension($file) {
    $info = pathinfo($file);
    return $info['extension'];
}

// 初始密码：默认"学号后6位"，学号不足6位则取整个学号
function default_password($user_id) {
    $len = mb_strlen($user_id, 'utf-8');
    return $len >= 6 ? mb_substr($user_id, $len - 6, 6, 'utf-8') : $user_id;
}

// 判断学号是否已存在
function user_exists($user_id) {
    $sql = "SELECT `user_id` FROM `users` WHERE `user_id` = ?";
    return !empty(pdo_query($sql, $user_id));
}

function import_user($filename, $target_school_id, $target_school_name) {
    global $OJ_EXPIRY_DAYS, $OJ_SCHOOL_MODE;
    static $nick_altered = false;  // 确保整个请求内昵称扩列只 ALTER 一次
    static $cred_block = 0;        // 多次导入时保证账号清单 textarea id 唯一
    $check = false;
    $gbk = false;
    $stats = array('success' => 0, 'skip' => 0, 'fail' => 0);
    $fail_list = array();
    $skip_list = array();
    $cred_list = array();
    $max_nick_len = 20;  // users.nick 默认长度上限，超出需扩列

    if (($h = fopen("{$filename}", "r")) !== FALSE) {
        // 文件中的每一行数据都被转换为我们调用的单个数组$data
        // 数组的每个元素以逗号分隔
        $bom = fread($h, 3);  // 文件有BOM，跳过这三个字节
        if ($bom !== "\xEF\xBB\xBF") {
            fseek($h, 0);  // 文件没有BOM，退回文件头部
        }

        while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {
            // 跳过空行（避免 PHP 8.1+ 对 null 的弃用告警，以及误计入失败统计）
            if (count($data) == 1 && ($data[0] === null || $data[0] === '')) {
                continue;
            }
            // 跳过表头（兼容UTF-8 / GBK 两种编码）
            if (!$check) {
                if ($data[0] == "学号") {
                    $check = true;
                    $gbk = false;
                    continue;
                } else if (iconv("gbk", "utf-8", $data[0]) == "学号") {
                    $check = true;
                    $gbk = true;
                    continue;
                }
            }
            if (!$check) {
                echo "<h1>请用下载的模板填写，保存为UTF-8编码。</h1>";
                break;
            }

            $user_id = mb_trim($data[0]);
            $nick = isset($data[1]) ? trim($data[1]) : "";
            $password = isset($data[2]) ? trim($data[2]) : "";
            $school = isset($data[3]) ? trim($data[3]) : "";
            $email = isset($data[4]) ? trim($data[4]) : "";
            $group_name = isset($data[5]) ? trim($data[5]) : "";
            $expiry_date = isset($data[6]) ? trim($data[6]) : "";
            if ($gbk) {
                $nick = iconv("gbk", "utf-8", $nick);
                $school = iconv("gbk", "utf-8", $school);
                $group_name = iconv("gbk", "utf-8", $group_name);
                $expiry_date = iconv("gbk", "utf-8", $expiry_date);
            }

            // 学号必填
            if (empty($user_id)) {
                $stats['fail']++;
                $fail_list[] = array('user_id' => '', 'reason' => '学号为空');
                continue;
            }
            // 用户名合法性
            if (!is_valid_user_name($user_id)) {
                $stats['fail']++;
                $fail_list[] = array('user_id' => $user_id, 'reason' => '学号不合法（仅支持字母/数字/_/-）');
                continue;
            }
            // 重复处理：学号已存在则跳过，不覆盖原账号
            if (user_exists($user_id)) {
                $stats['skip']++;
                $skip_list[] = $user_id;
                continue;
            }
            // 姓名留空则用学号兜底
            if (empty($nick)) $nick = $user_id;
            // 初始密码：密码列留空则默认"学号后6位"
            $plain_pwd = empty($password) ? default_password($user_id) : $password;
            $password = pwGen($plain_pwd);
            // 学校：学校模式下统一归属所选目标学校，保证 school_id 与展示名一致
            if ($OJ_SCHOOL_MODE) {
                $school = $target_school_name;
                $school_id = intval($target_school_id);
            } else {
                $school_id = 0;
            }
            // 有效期：留空默认平台规则；纯数字按"天数偏移"处理；非法值回退默认
            if (empty($expiry_date)) {
                $expiry_date = add_days($OJ_EXPIRY_DAYS);
            } elseif (is_numeric($expiry_date)) {
                $expiry_date = add_days($expiry_date);
            } elseif (!is_date($expiry_date)) {
                $expiry_date = add_days($OJ_EXPIRY_DAYS);
            }
            // 昵称超长自动扩列：先记录最大长度，导入完成后统一 ALTER 一次（沿用原逻辑但避免反复 ALTER）
            $nick_len = mb_strlen($nick, 'utf-8');
            if ($nick_len > $max_nick_len) {
                $max_nick_len = $nick_len;
            }

            $ip = "127.0.0.1";
            $sql = "INSERT INTO `users`("
                    . "`user_id`,`email`,`ip`,`accesstime`,`password`,`reg_time`,`nick`,`school`,`school_id`,`role`,`group_name`,`defunct`,`expiry_date`)"
                    . "VALUES(?,?,?,NOW(),?,NOW(),?,?,?,'student',?,'N',?)";
            $ret = pdo_query($sql, $user_id, $email, $ip, $password, $nick, $school, $school_id, $group_name, $expiry_date);
            if ($ret === -1 || $ret === false) {
                $stats['fail']++;
                $fail_list[] = array('user_id' => $user_id, 'reason' => '数据库写入失败');
            } else {
                $stats['success']++;
                $cred_list[] = array('user_id' => $user_id, 'password' => $plain_pwd);
            }
        }
        // 昵称列扩容：仅在确实超长时执行一次
        if ($max_nick_len > 20 && !$nick_altered) {
            $nick_altered = true;
            $longer = "ALTER TABLE `users` MODIFY COLUMN `nick` varchar($max_nick_len) NULL DEFAULT '' ";
            pdo_query($longer);
        }
        // 关闭文件
        fclose($h);
    }

    // ===== 结果汇总 =====
    echo "<div style='padding: 15px; background: #eafaf1; border: 1px solid #a9dfbf; border-radius: 6px; margin: 15px 0;'>";
    echo "<b>导入完成：</b>成功 <b>{$stats['success']}</b> 人 ｜ 跳过 <b>{$stats['skip']}</b> 人（已存在）｜ 失败 <b>{$stats['fail']}</b> 人";
    echo "</div>";

    // 已存在列表
    if (!empty($skip_list)) {
        echo "<h3>已存在（未覆盖，共 " . count($skip_list) . " 个）：</h3><ul>";
        foreach ($skip_list as $uid) {
            echo "<li>" . htmlentities($uid, ENT_QUOTES, 'UTF-8') . " 账号已存在</li>";
        }
        echo "</ul>";
    }
    // 失败列表
    if (!empty($fail_list)) {
        echo "<h3>导入失败（共 " . count($fail_list) . " 个）：</h3><ul>";
        foreach ($fail_list as $f) {
            echo "<li>" . htmlentities(($f['user_id'] !== '' ? $f['user_id'] : '(空)'), ENT_QUOTES, 'UTF-8') . "：{$f['reason']}</li>";
        }
        echo "</ul>";
    }
    // 账号清单（含初始密码，可复制回传老师）
    if (!empty($cred_list)) {
        $cred_block++;
        $ta_id = 'credList_' . $cred_block;  // 多次导入（如 zip）时 id 唯一，避免重复
        echo "<h3>账号清单（含初始密码，可直接复制回传老师）：</h3>";
        $lines = array();
        foreach ($cred_list as $c) {
            $lines[] = "{$c['user_id']},{$c['password']}";
        }
        $txt = implode("\n", $lines);
        echo "<textarea id='$ta_id' rows='10' style='width:100%; font-family: monospace; font-size: 13px;'>" . htmlentities($txt, ENT_QUOTES, 'UTF-8') . "</textarea>";
        echo "<br><button type='button' class='btn btn-default btn-sm' onclick='copyCredList(\"$ta_id\")' style='margin-top: 8px;'>复制账号清单</button>";
        echo "<script>
        function copyCredList(id){
            var ta = document.getElementById(id);
            ta.select();
            try { document.execCommand('copy'); } catch (err) {}
            alert('账号清单已复制');
        }
        </script>";
    }
}

if (isset($_FILES["fps"])) {
    // CSRF 校验（CLAUDE.md 强制：管理后台表单必须 check_post_key）
    require_once ("../include/check_post_key.php");
    if ($_FILES["fps"]["error"] > 0) {
        echo "&nbsp;&nbsp;- Error: " . $_FILES["fps"]["error"] . "File size is too big, change in PHP.ini<br />";
    } else {
        $target_school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;
        $target_school_name = "";
        if ($target_school_id > 0 && function_exists('getSchoolInfo')) {
            $school_info = getSchoolInfo($target_school_id);
            // 学校必须存在且启用，避免产生 school_id 与展示名不一致
            if (!empty($school_info) && intval($school_info['status']) == 1) {
                $target_school_name = $school_info['name'];
            } else {
                $target_school_id = 0;
            }
        }
        // 学校模式下必须选择有效（启用中）的目标学校
        if ($OJ_SCHOOL_MODE && $target_school_id <= 0) {
            echo "<h1>请选择有效（启用中）的目标学校后再导入。</h1>";
            exit(1);
        }
        $tempfile = $_FILES["fps"]["tmp_name"];
        if (get_extension($_FILES["fps"]["name"]) == "zip") {
            echo "&nbsp;&nbsp;- zip file, only fps/xml files in root dir are supported";
            $resource = zip_open($tempfile);
            $tempfile = tempnam("/tmp", "fps");
            while ($dir_resource = zip_read($resource)) {
                if (zip_entry_open($resource, $dir_resource)) {
                    $file_name = zip_entry_name($dir_resource);
                    $file_path = substr($file_name, 0, strrpos($file_name, "/"));
                    if (!is_dir($file_name)) {
                        $file_size = zip_entry_filesize($dir_resource);
                        $file_content = zip_entry_read($dir_resource, $file_size);
                        file_put_contents($tempfile, $file_content);
                        import_user($tempfile, $target_school_id, $target_school_name);
                    }
                    zip_entry_close($dir_resource);
                }
            }
            zip_close($resource);
            unlink($_FILES["fps"]["tmp_name"]);
        } else {
            import_user($tempfile, $target_school_id, $target_school_name);
            unlink($_FILES["fps"]["tmp_name"]);
        }
    }
} else {
    $school_list = array();
    if (function_exists('getSchoolList')) {
        $school_list = getSchoolList(true);
    }
?>

<br>
<br>
<h1>导入用户csv文件</h1>
<?php if ($OJ_SCHOOL_MODE) { ?>
<div class="alert alert-warning">当前为<strong>学校隔离模式</strong>，批量导入的账号将统一归属到所选目标学校，角色默认为「学生」。</div>
<?php } ?>
    <form class='form-inline' action='user_import.php' method=post enctype="multipart/form-data">
      <?php if ($OJ_SCHOOL_MODE) { ?>
      <div class='form-group'>
        <label>目标学校：</label>
        <select name='school_id' class='form-control' required>
          <option value='0'>请选择学校</option>
          <?php foreach ($school_list as $school) { ?>
          <option value='<?php echo intval($school['id']); ?>'><?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8'); ?></option>
          <?php } ?>
        </select>
      </div>
      <?php } ?>
      <div class='form-group'>
        <label>名单文件：</label>
        <input class='form-control' type=file name='fps' required>
      </div>
      <br><br>
      <center>
      <div class='form-group'>
        <button class='btn btn-default btn-sm' type=submit>Upload to HUSTOJ</button>
      </div>
      </center>
      <?php require_once ("../include/set_post_key.php"); ?>
    </form>
<h2><a href="users.csv">下载模板</a></h2>
<h3>请用下载的模板填写，保存为UTF-8编码。密码列可留空，默认初始密码为「学号后6位」（学号不足6位取整个学号）；重复学号不会覆盖原账号，将跳过并提示「xxx账号已存在」。</h3>
<?php
} ?>