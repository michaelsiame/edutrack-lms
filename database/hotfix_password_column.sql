-- ============================================================================
-- HOTFIX: Reverse Incorrect password_hash Rename
-- ============================================================================
-- Purpose: Fix the incorrectly renamed password column back to password_hash
-- Run this IMMEDIATELY if you've already run final_compatibility_fix.sql
-- ============================================================================

USE edutrack_lms;

-- Check if 'password' column exists and rename it back to 'password_hash'
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'edutrack_lms'
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'password'
);

-- If 'password' exists, rename it back to 'password_hash'
SET @sql = IF(@column_exists > 0,
    'ALTER TABLE users CHANGE COLUMN password password_hash VARCHAR(255) NOT NULL',
    'SELECT "Column already named password_hash - no fix needed" AS status'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the fix
SELECT
    CASE
        WHEN COLUMN_NAME = 'password_hash' THEN '✓ FIXED: Column is now password_hash'
        ELSE '✗ ERROR: Column name is incorrect'
    END AS result
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'edutrack_lms'
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME IN ('password', 'password_hash');
