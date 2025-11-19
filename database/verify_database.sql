-- ============================================================================
-- DATABASE VERIFICATION SCRIPT
-- ============================================================================
-- Purpose: Check which tables exist and what data you have
-- Run this to see the current state of your database
-- ============================================================================

USE edutrack_lms;

-- Show all tables in the database
SELECT '=== EXISTING TABLES ===' as '';
SHOW TABLES;

-- Count records in key tables
SELECT '=== TABLE RECORD COUNTS ===' as '';

SELECT 'users' as table_name, COUNT(*) as record_count FROM users
UNION ALL
SELECT 'roles', COUNT(*) FROM roles
UNION ALL
SELECT 'user_roles', COUNT(*) FROM user_roles
UNION ALL
SELECT 'course_categories', COUNT(*) FROM course_categories
UNION ALL
SELECT 'courses', COUNT(*) FROM courses
UNION ALL
SELECT 'instructors', COUNT(*) FROM instructors
UNION ALL
SELECT 'students', COUNT(*) FROM students
UNION ALL
SELECT 'enrollments', COUNT(*) FROM enrollments;

-- Check optional tables (may not exist)
SELECT '=== OPTIONAL TABLES (may show errors if missing) ===' as '';

SELECT 'modules' as table_name, COUNT(*) as record_count FROM modules
UNION ALL
SELECT 'lessons', COUNT(*) FROM lessons
UNION ALL
SELECT 'assignments', COUNT(*) FROM assignments
UNION ALL
SELECT 'quizzes', COUNT(*) FROM quizzes
UNION ALL
SELECT 'user_profiles', COUNT(*) FROM user_profiles
UNION ALL
SELECT 'course_reviews', COUNT(*) FROM course_reviews
UNION ALL
SELECT 'certificates', COUNT(*) FROM certificates
UNION ALL
SELECT 'notifications', COUNT(*) FROM notifications;

-- Show sample users with their roles
SELECT '=== USERS AND THEIR ROLES ===' as '';
SELECT
    u.id,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as name,
    r.role_name,
    u.status,
    u.created_at
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
ORDER BY u.id;

-- Show courses
SELECT '=== COURSES ===' as '';
SELECT
    c.id,
    c.title,
    cat.name as category,
    c.level,
    c.status,
    c.created_at
FROM courses c
LEFT JOIN course_categories cat ON c.category_id = cat.id
ORDER BY c.id
LIMIT 10;

-- Show enrollments
SELECT '=== ENROLLMENTS ===' as '';
SELECT
    e.id,
    u.email as student_email,
    c.title as course_title,
    e.enrollment_status,
    e.progress_percentage,
    e.enrolled_at
FROM enrollments e
JOIN users u ON e.user_id = u.id
JOIN courses c ON e.course_id = c.id
ORDER BY e.enrolled_at DESC
LIMIT 10;

SELECT '=== VERIFICATION COMPLETE ===' as '';
