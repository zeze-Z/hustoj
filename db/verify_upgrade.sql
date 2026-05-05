-- =============================================
-- 数据库升级验证脚本
-- 功能：检查V1.0之后所有升级涉及的字段是否已正确变更
-- =============================================

-- =============================================
-- V1.0 (2026-03-31): 课件商城模块
-- =============================================
SELECT '=== V1.0: 课件商城模块 ===' AS check_section;

-- 检查 course 表字段
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'title 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'title';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'description 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'description';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'price 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'price';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'subject_id 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'subject_id';

-- 检查 course_subject 表
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course_subject 表' AS table_name,
    '存在' AS column_name
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_subject';

-- 检查 course_order 表
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course_order 表' AS table_name,
    'user_id 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_order' AND COLUMN_NAME = 'user_id';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course_order 表' AS table_name,
    'course_id 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_order' AND COLUMN_NAME = 'course_id';

-- =============================================
-- V1.0 (2026-04-11): 多学校隔离功能
-- =============================================
SELECT '=== V1.0: 多学校隔离功能 ===' AS check_section;

-- 检查 school 表
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'school 表' AS table_name,
    '存在' AS column_name
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'school';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'school 表' AS table_name,
    'code 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'school' AND COLUMN_NAME = 'code';

-- 检查 users 表扩展
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'users 表' AS table_name,
    'school_id 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'school_id';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'users 表' AS table_name,
    'school 字段长度100' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'school' AND CHARACTER_MAXIMUM_LENGTH >= 100;

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'users 表' AS table_name,
    'idx_school_id 索引' AS column_name
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_school_id';

-- 检查 problem 表扩展
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'problem 表' AS table_name,
    'school_id 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'problem' AND COLUMN_NAME = 'school_id';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'problem 表' AS table_name,
    'is_public 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'problem' AND COLUMN_NAME = 'is_public';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'problem 表' AS table_name,
    'idx_school_id 索引' AS column_name
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'problem' AND INDEX_NAME = 'idx_school_id';

-- 检查 contest 表扩展
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'contest 表' AS table_name,
    'school_id 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contest' AND COLUMN_NAME = 'school_id';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'contest 表' AS table_name,
    'is_public 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contest' AND COLUMN_NAME = 'is_public';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'contest 表' AS table_name,
    'idx_school_id 索引' AS column_name
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contest' AND INDEX_NAME = 'idx_school_id';

-- 检查 news 表扩展
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'news 表' AS table_name,
    'school_id 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'news' AND COLUMN_NAME = 'school_id';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'news 表' AS table_name,
    'is_public 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'news' AND COLUMN_NAME = 'is_public';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'news 表' AS table_name,
    'idx_school_id 索引' AS column_name
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'news' AND INDEX_NAME = 'idx_school_id';

-- =============================================
-- V1.1 (2026-04-19): 选择题功能 + 考试模块
-- =============================================
SELECT '=== V1.1: 选择题功能 + 考试模块 ===' AS check_section;

-- 检查 problem 表扩展（选择题）
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'problem 表' AS table_name,
    'problem_type 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'problem' AND COLUMN_NAME = 'problem_type';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'problem 表' AS table_name,
    'options 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'problem' AND COLUMN_NAME = 'options';

-- 检查 solution 表扩展（考试模块）
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'solution 表' AS table_name,
    'exam_id 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solution' AND COLUMN_NAME = 'exam_id';

-- 注意：V1.1将pass_rate从decimal(4,3)改为decimal(5,2)
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'solution 表' AS table_name,
    'pass_rate 字段类型 decimal(5,2)' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solution' AND COLUMN_NAME = 'pass_rate' AND NUMERIC_PRECISION = 5 AND NUMERIC_SCALE = 2;

-- 检查 problem.title 字段类型（TEXT）
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'problem 表' AS table_name,
    'title 字段类型 TEXT' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'problem' AND COLUMN_NAME = 'title' AND COLUMN_TYPE = 'text';

-- 检查 exam 相关表
SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'exam 表' AS table_name,
    '存在' AS column_name
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exam';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'exam_problem 表' AS table_name,
    '存在' AS column_name
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exam_problem';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'exam_attend 表' AS table_name,
    '存在' AS column_name
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exam_attend';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'exam_result 表' AS table_name,
    '存在' AS column_name
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exam_result';

SELECT 
    CASE WHEN COUNT(*) > 0 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'exam_result 表' AS table_name,
    'uk_exam_user_problem 唯一索引' AS column_name
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exam_result' AND INDEX_NAME = 'uk_exam_user_problem' AND NON_UNIQUE = 0;

-- =============================================
-- V1.2 (2026-05-01): 课件预览付费改造
-- =============================================
SELECT '=== V1.2: 课件预览付费改造 ===' AS check_section;

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'courseware_full_preview_url 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'courseware_full_preview_url';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'lesson_plan_full_preview_url 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'lesson_plan_full_preview_url';

-- =============================================
-- V1.3 (2026-05-05): 删除提取码字段
-- =============================================
SELECT '=== V1.3: 删除提取码字段 ===' AS check_section;

SELECT 
    CASE WHEN COUNT(*) = 0 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'courseware_code 应已删除' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'courseware_code';

SELECT 
    CASE WHEN COUNT(*) = 0 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'lesson_plan_code 应已删除' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'lesson_plan_code';

-- =============================================
-- V1.4 (2026-05-05): 多权限体系升级
-- =============================================
SELECT '=== V1.4: 多权限体系升级 ===' AS check_section;

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'preview_price 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'preview_price';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course 表' AS table_name,
    'source_price 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'source_price';

SELECT 
    CASE WHEN COUNT(*) = 1 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course_order 表' AS table_name,
    'license_type 字段' AS column_name
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_order' AND COLUMN_NAME = 'license_type';

SELECT 
    CASE WHEN COUNT(*) > 0 THEN '✅ PASS' ELSE '❌ FAIL' END AS status,
    'course_order 表' AS table_name,
    'uk_user_course_license 唯一索引' AS column_name
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_order' AND INDEX_NAME = 'uk_user_course_license' AND NON_UNIQUE = 0;

-- =============================================
-- 汇总统计
-- =============================================
SELECT '=== 升级验证完成 ===' AS result;
