<?php
/**
 * 学校相关核心函数库
 * 用于多学校数据隔离
 */

// 全局配置：是否开启学校模式（需要在 db_info.inc.php 中设置）
if (!isset($OJ_SCHOOL_MODE)) {
    $OJ_SCHOOL_MODE = false;
}

/**
 * 获取当前用户的学校ID
 * @return int|null 学校ID，未登录返回null
 */
function getCurrentUserSchoolId() {
    global $OJ_NAME, $OJ_SCHOOL_MODE;
    
    if (!$OJ_SCHOOL_MODE) return null;
    if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])) return null;
    
    // 优先从session获取
    if (isset($_SESSION[$OJ_NAME.'_'.'school_id'])) {
        return $_SESSION[$OJ_NAME.'_'.'school_id'];
    }
    
    // 从数据库获取
    $user_id = $_SESSION[$OJ_NAME.'_'.'user_id'];
    $sql = "SELECT school_id FROM `users` WHERE user_id = ?";
    $result = pdo_query($sql, $user_id);
    
    if (!empty($result)) {
        $school_id = $result[0]['school_id'];
        $_SESSION[$OJ_NAME.'_'.'school_id'] = $school_id;
        return $school_id;
    }
    
    return null;
}

/**
 * 获取当前用户学校名称
 * @return string 学校名称
 */
function getCurrentUserSchoolName() {
    global $OJ_NAME;
    
    if (isset($_SESSION[$OJ_NAME.'_'.'school'])) {
        return $_SESSION[$OJ_NAME.'_'.'school'];
    }
    
    $school_id = getCurrentUserSchoolId();
    if (!$school_id) return '';
    
    $school = getSchoolName($school_id);
    $_SESSION[$OJ_NAME.'_'.'school'] = $school;
    return $school;
}

/**
 * 根据学校ID获取学校名称
 * @param int $school_id 学校ID
 * @return string 学校名称
 */
function getSchoolName($school_id) {
    if (!$school_id) return '';
    
    $sql = "SELECT `name` FROM `school` WHERE id = ?";
    $result = pdo_query($sql, $school_id);
    
    if (!empty($result)) {
        return $result[0]['name'];
    }
    
    return '';
}

/**
 * 检查是否为超级管理员
 * @return bool
 */
function isSuperAdmin() {
    global $OJ_NAME;
    return isset($_SESSION[$OJ_NAME.'_'.'administrator']);
}

/**
 * 检查是否为学校管理员
 * @return bool
 */
function isSchoolAdmin() {
    global $OJ_NAME;
    return isset($_SESSION[$OJ_NAME.'_'.'school_admin']);
}

/**
 * 获取当前用户角色
 * @return string 'super_admin' | 'school_admin' | 'user' | 'guest'
 */
function getCurrentUserRole() {
    if (isSuperAdmin()) return 'super_admin';
    if (isSchoolAdmin()) return 'school_admin';
    if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])) return 'user';
    return 'guest';
}

/**
 * 获取学校过滤SQL条件
 * @param string $tableAlias 表别名，如 'p', 'c', 'n'
 * @param string $idField ID字段名，默认 'school_id'
 * @param string $publicField 公开字段名，默认 'is_public'
 * @return string SQL条件片段（包含 AND）
 */
function getSchoolSQLFilter($tableAlias = '', $idField = 'school_id', $publicField = 'is_public') {
    global $OJ_SCHOOL_MODE;
    
    // 未开启学校模式，不过滤
    if (!$OJ_SCHOOL_MODE) {
        return '';
    }
    
    // 超级管理员看所有数据
    if (isSuperAdmin()) {
        return '';
    }
    
    $prefix = $tableAlias ? $tableAlias . '.' : '';
    $school_id = getCurrentUserSchoolId();
    
    // 未分配学校的用户，只能看公开数据
    if (!$school_id) {
        return " AND ({$prefix}{$publicField} = 1)";
    }
    
    // 过滤：本校数据 + 公开数据
    return " AND ({$prefix}{$idField} = {$school_id} OR {$prefix}{$publicField} = 1)";
}

/**
 * 获取学校列表（用于下拉选择）
 * @param bool $onlyActive 只显示启用的学校
 * @return array 学校列表
 */
function getSchoolList($onlyActive = true) {
    $sql = "SELECT * FROM `school`";
    if ($onlyActive) {
        $sql .= " WHERE `status` = 1";
    }
    $sql .= " ORDER BY `id` ASC";
    
    return pdo_query($sql);
}

/**
 * 获取学校详情
 * @param int $school_id 学校ID
 * @return array|null 学校信息
 */
function getSchoolInfo($school_id) {
    $sql = "SELECT * FROM `school` WHERE `id` = ?";
    $result = pdo_query($sql, $school_id);
    
    return !empty($result) ? $result[0] : null;
}

/**
 * 添加学校（超级管理员）
 * @param string $name 学校名称
 * @param string $code 学校代码
 * @param int $status 状态，默认1启用
 * @return int|false 新增的学校ID
 */
function addSchool($name, $code, $status = 1) {
    $sql = "INSERT INTO `school` (`name`, `code`, `status`) VALUES (?, ?, ?)";
    $result = pdo_query($sql, $name, $code, $status);
    
    if ($result) {
        global $dbh;
        return $dbh->lastInsertId();
    }
    return false;
}

/**
 * 更新学校信息
 * @param int $school_id 学校ID
 * @param string $name 学校名称
 * @param string $code 学校代码
 * @param int $status 状态
 * @return bool
 */
function updateSchool($school_id, $name, $code, $status = 1) {
    $sql = "UPDATE `school` SET `name` = ?, `code` = ?, `status` = ? WHERE `id` = ?";
    $result = pdo_query($sql, $name, $code, $status, $school_id);
    return $result !== false;
}

/**
 * 删除学校（超级管理员）
 * @param int $school_id 学校ID
 * @return bool
 */
function deleteSchool($school_id) {
    // 检查是否有用户关联
    $sql = "SELECT COUNT(*) as cnt FROM `users` WHERE `school_id` = ?";
    $result = pdo_query($sql, $school_id);
    
    if (!empty($result) && $result[0]['cnt'] > 0) {
        return false; // 有关联用户无法删除
    }
    
    $sql = "DELETE FROM `school` WHERE `id` = ?";
    $result = pdo_query($sql, $school_id);
    return $result !== false;
}

/**
 * 设置用户学校（超级管理员/学校管理员）
 * @param string $user_id 用户ID
 * @param int $school_id 学校ID
 * @return bool
 */
function setUserSchool($user_id, $school_id) {
    $school_name = getSchoolName($school_id);
    $sql = "UPDATE `users` SET `school_id` = ?, `school` = ? WHERE `user_id` = ?";
    $result = pdo_query($sql, $school_id, $school_name, $user_id);
    
    // 清除该用户的session缓存
    if (isset($_SESSION[$OJ_NAME.'_'.'user_id']) && $_SESSION[$OJ_NAME.'_'.'user_id'] == $user_id) {
        $_SESSION[$OJ_NAME.'_'.'school_id'] = $school_id;
        $_SESSION[$OJ_NAME.'_'.'school'] = $school_name;
    }
    
    return $result !== false;
}

/**
 * 检查用户是否有权限访问指定数据
 * @param int $data_school_id 数据所属学校ID
 * @param bool $is_public 数据是否公开
 * @return bool
 */
function canAccessData($data_school_id, $is_public = false) {
    global $OJ_SCHOOL_MODE;
    
    // 未开启学校模式，可访问
    if (!$OJ_SCHOOL_MODE) {
        return true;
    }
    
    // 超级管理员可访问
    if (isSuperAdmin()) {
        return true;
    }
    
    // 公开数据可访问
    if ($is_public) {
        return true;
    }
    
    // 本校数据可访问
    $school_id = getCurrentUserSchoolId();
    if ($school_id && $school_id == $data_school_id) {
        return true;
    }
    
    return false;
}

/**
 * 获取题目过滤条件（用于SQL）
 * @return string
 */
function getProblemSchoolFilter() {
    return getSchoolSQLFilter('p', 'school_id', 'is_public');
}

/**
 * 获取比赛过滤条件
 * @return string
 */
function getContestSchoolFilter() {
    return getSchoolSQLFilter('c', 'school_id', 'is_public');
}

/**
 * 获取新闻过滤条件
 * @return string
 */
function getNewsSchoolFilter() {
    return getSchoolSQLFilter('n', 'school_id', 'is_public');
}

/**
 * 获取用户过滤条件
 * @return string
 */
function getUserSchoolFilter() {
    global $OJ_SCHOOL_MODE;
    
    if (!$OJ_SCHOOL_MODE) {
        return '';
    }
    
    if (isSuperAdmin()) {
        return '';
    }
    
    $school_id = getCurrentUserSchoolId();
    if (!$school_id) {
        return ' AND 1=0'; // 无学校用户看不到任何用户
    }
    
    return " AND school_id = $school_id";
}

/**
 * 获取提交记录过滤条件（用于SQL）
 * 根据用户学校过滤：只能看到本校用户的提交
 * @return string SQL条件片段（包含 AND）
 */
function getSolutionSchoolFilter() {
    global $OJ_SCHOOL_MODE;
    
    // 未开启学校模式，不过滤
    if (!$OJ_SCHOOL_MODE) {
        return '';
    }
    
    // 超级管理员看所有
    if (isSuperAdmin()) {
        return '';
    }
    
    $school_id = getCurrentUserSchoolId();
    
    // 未分配学校的普通用户，看不到其他用户的提交（或可看公开的）
    if (!$school_id) {
        return " AND users.school_id IS NULL";
    }
    
    // 过滤：本校用户
    return " AND users.school_id = $school_id";
}
