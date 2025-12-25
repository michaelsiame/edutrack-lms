-- Migration: Assign Michael Siame to Microsoft Office Suite Course
-- Date: 2025-12-25
-- Purpose: Fix missing course assignment for Michael Siame (instructor_id=10) to Course 1

-- Step 1: Verify Michael's instructor record exists
SELECT 'Verifying Michael Siame instructor record...' as status;
SELECT
    i.id as instructor_id,
    i.user_id,
    u.username,
    u.name,
    u.email
FROM instructors i
JOIN users u ON i.user_id = u.id
WHERE i.user_id = 6;

-- Step 2: Show current assignments for Microsoft Office course (before fix)
SELECT '\nCurrent assignments for Microsoft Office course (BEFORE):' as status;
SELECT
    ci.id,
    ci.course_id,
    ci.instructor_id,
    ci.role,
    u.name as instructor_name
FROM course_instructors ci
JOIN instructors i ON ci.instructor_id = i.id
JOIN users u ON i.user_id = u.id
WHERE ci.course_id = 1;

-- Step 3: Remove incorrect assignments (keep only Michael's if it exists)
SELECT '\nRemoving incorrect assignments...' as status;
DELETE FROM course_instructors
WHERE course_id = 1 AND instructor_id NOT IN (
    SELECT id FROM instructors WHERE user_id = 6
);

-- Step 4: Insert Michael's assignment as Lead instructor (only if not exists)
SELECT '\nAssigning Michael Siame as Lead instructor...' as status;
INSERT INTO course_instructors (course_id, instructor_id, role, assigned_date, created_at, updated_at)
SELECT
    1 as course_id,
    i.id as instructor_id,
    'Lead' as role,
    CURDATE() as assigned_date,
    NOW() as created_at,
    NOW() as updated_at
FROM instructors i
WHERE i.user_id = 6
  AND NOT EXISTS (
      SELECT 1 FROM course_instructors
      WHERE course_id = 1 AND instructor_id = i.id
  );

-- Step 5: Verify the fix
SELECT '\nFinal assignments for Microsoft Office course (AFTER):' as status;
SELECT
    c.id as course_id,
    c.title as course_title,
    ci.role,
    u.name as instructor_name,
    u.email as instructor_email,
    ci.assigned_date
FROM course_instructors ci
JOIN instructors i ON ci.instructor_id = i.id
JOIN users u ON i.user_id = u.id
JOIN courses c ON ci.course_id = c.id
WHERE ci.course_id = 1;

SELECT '\n✓ Migration complete! Michael Siame should now see the Microsoft Office course in his instructor dashboard.' as status;
