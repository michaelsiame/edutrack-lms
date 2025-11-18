-- ============================================================================
-- HOTFIX: Fix User Status Values to Lowercase
-- ============================================================================
-- Purpose: Convert user status values from 'Active' to 'active'
-- Run this IMMEDIATELY to fix login "account suspended" error
-- ============================================================================

USE edutrack_lms;

-- Update all existing status values to lowercase
UPDATE users SET status = LOWER(status);

-- Modify the ENUM to use lowercase values
ALTER TABLE users
MODIFY COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active';

-- Verify the fix
SELECT
    email,
    status,
    CASE
        WHEN status = 'active' THEN '✓ Can login'
        WHEN status = 'inactive' THEN '✗ Account inactive'
        WHEN status = 'suspended' THEN '✗ Account suspended'
        ELSE '? Unknown status'
    END as login_status
FROM users
WHERE email LIKE '%@edutrack.edu'
ORDER BY email;

SELECT '✓ User status values normalized to lowercase' AS result;
