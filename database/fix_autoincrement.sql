-- ============================================================================
-- CRITICAL FIX: Restore AUTO_INCREMENT for Core Tables
-- ============================================================================
-- Issue: courses, enrollments, and instructors tables lost AUTO_INCREMENT
-- This script restores proper AUTO_INCREMENT values
-- ============================================================================

USE edutrack_lms;

-- Fix 1: Restore AUTO_INCREMENT for courses table
ALTER TABLE courses MODIFY id INT NOT NULL AUTO_INCREMENT;
ALTER TABLE courses AUTO_INCREMENT = 21;

-- Fix 2: Restore AUTO_INCREMENT for enrollments table
ALTER TABLE enrollments MODIFY id INT NOT NULL AUTO_INCREMENT;
ALTER TABLE enrollments AUTO_INCREMENT = 30;

-- Fix 3: Restore AUTO_INCREMENT for instructors table
ALTER TABLE instructors MODIFY id INT NOT NULL AUTO_INCREMENT;
ALTER TABLE instructors AUTO_INCREMENT = 7;

-- Verify the fixes
SELECT '=== AUTO_INCREMENT VALUES ===' as '';

SELECT
    TABLE_NAME,
    AUTO_INCREMENT,
    CASE
        WHEN AUTO_INCREMENT IS NOT NULL THEN '✓ Fixed'
        ELSE '✗ Still broken'
    END as Status
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'edutrack_lms'
AND TABLE_NAME IN ('courses', 'enrollments', 'instructors');

-- Show sample data to confirm everything works
SELECT '=== SAMPLE COURSES ===' as '';
SELECT id, title, status, level, instructor_id
FROM courses
LIMIT 5;

SELECT '=== SAMPLE ENROLLMENTS ===' as '';
SELECT id, user_id, course_id, enrollment_status, progress
FROM enrollments
LIMIT 5;

SELECT '=== SAMPLE INSTRUCTORS ===' as '';
SELECT id, user_id, specialization
FROM instructors
LIMIT 3;

SELECT '✓ AUTO_INCREMENT restoration complete!' AS Result;
