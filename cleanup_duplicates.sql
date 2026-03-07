-- Step 1: Identify duplicate enrollments
SELECT 
    user_id, 
    course_id, 
    COUNT(*) as duplicate_count,
    GROUP_CONCAT(id ORDER BY id) as enrollment_ids
FROM enrollments 
GROUP BY user_id, course_id 
HAVING COUNT(*) > 1;

-- Step 2: View details of duplicates (run after step 1 confirms duplicates exist)
-- SELECT * FROM enrollments 
-- WHERE (user_id, course_id) IN (
--     SELECT user_id, course_id 
--     FROM enrollments 
--     GROUP BY user_id, course_id 
--     HAVING COUNT(*) > 1
-- )
-- ORDER BY user_id, course_id, id;
