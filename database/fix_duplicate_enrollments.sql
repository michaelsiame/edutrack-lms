-- ============================================
-- FIX DUPLICATE ENROLLMENTS
-- ============================================
-- This script will:
-- 1. Keep the "best" enrollment for each user/course pair
-- 2. Delete duplicate enrollments
-- 3. Add the UNIQUE constraint

-- First, let's see what we're dealing with
SELECT 
    '=== DUPLICATE ENROLLMENTS ===' as info;

SELECT 
    user_id, 
    course_id, 
    COUNT(*) as duplicate_count,
    GROUP_CONCAT(id ORDER BY id) as enrollment_ids
FROM enrollments 
GROUP BY user_id, course_id 
HAVING COUNT(*) > 1;

-- ============================================
-- STEP 1: Create a temporary table to store the IDs of enrollments to KEEP
-- We keep the enrollment with:
-- 1. Highest progress (most complete)
-- 2. Most recent enrollment date (if progress is equal)
-- 3. Highest ID (most recent, if all else equal)
-- ============================================
CREATE TEMPORARY TABLE enrollments_to_keep AS
SELECT MAX(id) as keep_id
FROM enrollments e1
WHERE NOT EXISTS (
    SELECT 1 
    FROM enrollments e2 
    WHERE e2.user_id = e1.user_id 
      AND e2.course_id = e1.course_id 
      AND (
          -- Keep the one with higher progress
          e2.progress > e1.progress 
          OR (
              e2.progress = e1.progress 
              AND (
                  -- Or keep the one with later enrollment date
                  e2.enrolled_at > e1.enrolled_at
                  OR (
                      e2.enrolled_at = e1.enrolled_at 
                      AND e2.id > e1.id  -- Or just keep the higher ID
                  )
              )
          )
      )
)
GROUP BY user_id, course_id;

-- ============================================
-- STEP 2: Delete duplicate enrollments (keep only the "best" ones)
-- ============================================
DELETE FROM enrollments
WHERE id NOT IN (SELECT keep_id FROM enrollments_to_keep);

-- ============================================
-- STEP 3: Clean up
-- ============================================
DROP TEMPORARY TABLE IF EXISTS enrollments_to_keep;

-- ============================================
-- STEP 4: Now add the UNIQUE constraint
-- ============================================
ALTER TABLE enrollments 
ADD CONSTRAINT uk_enrollments_user_course 
UNIQUE (user_id, course_id);

-- Verify constraint was added
SELECT 
    '=== CONSTRAINT ADDED SUCCESSFULLY ===' as info;

SHOW INDEX FROM enrollments WHERE Key_name = 'uk_enrollments_user_course';
