-- ============================================
-- PREVIEW: See which enrollments will be kept vs deleted
-- ============================================
-- Run this first to see what the cleanup will do

SELECT 
    'ENROLLMENTS THAT WILL BE KEPT (Best Progress)' as status,
    e.id, 
    e.user_id, 
    e.course_id, 
    e.enrollment_status,
    e.progress,
    e.enrolled_at,
    'KEEP' as action
FROM enrollments e
INNER JOIN (
    SELECT user_id, course_id, MAX(progress) as max_progress
    FROM enrollments
    GROUP BY user_id, course_id
) best ON e.user_id = best.user_id 
      AND e.course_id = best.course_id 
      AND e.progress = best.max_progress
WHERE (e.user_id, e.course_id) IN (
    SELECT user_id, course_id 
    FROM enrollments 
    GROUP BY user_id, course_id 
    HAVING COUNT(*) > 1
)
GROUP BY e.user_id, e.course_id
HAVING e.id = MAX(e.id)  -- If same progress, keep the one with highest ID

UNION ALL

SELECT 
    'ENROLLMENTS THAT WILL BE DELETED' as status,
    e.id, 
    e.user_id, 
    e.course_id, 
    e.enrollment_status,
    e.progress,
    e.enrolled_at,
    'DELETE' as action
FROM enrollments e
WHERE (e.user_id, e.course_id) IN (
    SELECT user_id, course_id 
    FROM enrollments 
    GROUP BY user_id, course_id 
    HAVING COUNT(*) > 1
)
AND e.id NOT IN (
    -- Subquery to get IDs of enrollments to keep
    SELECT keep_id FROM (
        SELECT MAX(id) as keep_id
        FROM enrollments e1
        WHERE NOT EXISTS (
            SELECT 1 
            FROM enrollments e2 
            WHERE e2.user_id = e1.user_id 
              AND e2.course_id = e1.course_id 
              AND (
                  e2.progress > e1.progress 
                  OR (e2.progress = e1.progress AND e2.id > e1.id)
              )
        )
        GROUP BY user_id, course_id
    ) as keepers
)

ORDER BY user_id, course_id, action DESC, id;
