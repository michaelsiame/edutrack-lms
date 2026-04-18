-- ============================================
-- SIMPLE DUPLICATE CLEANUP (Compatible with older MySQL)
-- ============================================

-- STEP 1: See how many duplicates exist
SELECT 
    COUNT(*) as total_duplicates,
    COUNT(DISTINCT CONCAT(user_id, '-', course_id)) as unique_combinations
FROM enrollments 
WHERE (user_id, course_id) IN (
    SELECT user_id, course_id 
    FROM enrollments 
    GROUP BY user_id, course_id 
    HAVING COUNT(*) > 1
);

-- STEP 2: Create backup of duplicates (optional but recommended)
CREATE TABLE IF NOT EXISTS enrollments_duplicates_backup AS
SELECT * FROM enrollments 
WHERE (user_id, course_id) IN (
    SELECT user_id, course_id 
    FROM enrollments 
    GROUP BY user_id, course_id 
    HAVING COUNT(*) > 1
);

-- STEP 3: Delete duplicates, keeping the one with highest ID (most recent)
-- This assumes higher ID = more recent enrollment
DELETE e1 FROM enrollments e1
INNER JOIN enrollments e2 
    ON e1.user_id = e2.user_id 
    AND e1.course_id = e2.course_id 
    AND e1.id < e2.id;

-- STEP 4: Verify no duplicates remain
SELECT 
    'Remaining duplicates (should be 0):' as check_status,
    COUNT(*) as count
FROM (
    SELECT user_id, course_id 
    FROM enrollments 
    GROUP BY user_id, course_id 
    HAVING COUNT(*) > 1
) as dupes;

-- STEP 5: Add UNIQUE constraint
ALTER TABLE enrollments 
ADD CONSTRAINT uk_enrollments_user_course 
UNIQUE (user_id, course_id);

SELECT 'Cleanup complete! UNIQUE constraint added.' as result;
