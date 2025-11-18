-- ============================================================================
-- HOTFIX: Add role Column to users Table
-- ============================================================================
-- Purpose: Add missing 'role' column that auth.php expects
-- Run this IMMEDIATELY to fix login "Undefined array key 'role'" error
-- ============================================================================

USE edutrack_lms;

-- Step 1: Add role column to users table
ALTER TABLE users
ADD COLUMN IF NOT EXISTS role ENUM('admin', 'instructor', 'student') DEFAULT 'student' AFTER phone;

-- Step 2: Populate role based on email and related tables
-- Set admin role for admin users
UPDATE users
SET role = 'admin'
WHERE email LIKE '%admin%' OR username = 'admin';

-- Set instructor role for users who are in the instructors table
UPDATE users u
INNER JOIN instructors i ON u.id = i.user_id
SET u.role = 'instructor';

-- Set student role for users who are in the students table
UPDATE users u
INNER JOIN students s ON u.id = s.user_id
SET u.role = 'student'
WHERE u.role IS NULL OR u.role = 'student';

-- Step 3: Add index for performance
ALTER TABLE users
ADD INDEX IF NOT EXISTS idx_role (role);

-- Verification: Show all users with their roles
SELECT
    id,
    email,
    CONCAT(first_name, ' ', last_name) as full_name,
    role,
    status,
    CASE
        WHEN role = 'admin' THEN '✓ Administrator'
        WHEN role = 'instructor' THEN '✓ Instructor'
        WHEN role = 'student' THEN '✓ Student'
        ELSE '? Unknown role'
    END as role_description
FROM users
ORDER BY
    FIELD(role, 'admin', 'instructor', 'student'),
    email;

SELECT '✓ Role column added and populated successfully' AS result;
