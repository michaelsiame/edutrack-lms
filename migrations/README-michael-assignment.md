# Fix: Assign Michael Siame to Microsoft Office Course

## Problem
Michael Siame (username: `siamem570`, user_id: 6) cannot see the "Certificate in Microsoft Office Suite" course in his instructor dashboard, even though he should have access to it.

## Root Cause
The database shows:
- Michael has an instructor record (instructor_id = 10)
- The Microsoft Office course (course_id = 1) has `instructor_id = 1` in the `courses` table
- The `course_instructors` table has an assignment with instructor_id = 5 (wrong person)
- **Michael (instructor_id = 10) is NOT assigned to course 1 in the `course_instructors` table**

## Solution
Apply the migration SQL to assign Michael to the course:

```bash
mysql -u root -p edutrack_lms < migrations/assign-michael-to-microsoft-office.sql
```

### What the Migration Does:
1. Verifies Michael's instructor record exists (instructor_id = 10, user_id = 6)
2. Shows current assignments for Microsoft Office course
3. Removes incorrect assignments (instructor_id = 5)
4. Assigns Michael as Lead instructor for course 1
5. Verifies the fix was successful

## Expected Result
After running the migration:
- Michael Siame logs in with username `siamem570`
- His instructor dashboard will show "Certificate in Microsoft Office Suite"
- He can edit, manage modules, view students, create assignments, etc.

## Verification
After applying the migration, verify with:

```sql
-- Check Michael's assignment
SELECT
    c.title as course,
    u.name as instructor,
    ci.role,
    ci.assigned_date
FROM course_instructors ci
JOIN instructors i ON ci.instructor_id = i.id
JOIN users u ON i.user_id = u.id
JOIN courses c ON ci.course_id = c.id
WHERE i.user_id = 6;
```

Expected output:
```
+------------------------------------+--------------+------+---------------+
| course                             | instructor   | role | assigned_date |
+------------------------------------+--------------+------+---------------+
| Certificate in Microsoft Office... | Michael Siame| Lead | 2025-12-25   |
+------------------------------------+--------------+------+---------------+
```

## Login Credentials
- **Username**: `siamem570`
- **User ID**: 6
- **Instructor ID**: 10 (after migration)
- **Role**: Instructor
- **Position**: Head of ICT

## Files Modified in This Fix
- None - this is a DATA fix, not a CODE fix
- Previous commits already fixed the code to properly check the `course_instructors` table
- This migration only fixes the missing database assignment

## Related Documentation
- `/docs/INSTRUCTOR_DASHBOARD_REVIEW.md` - Complete system review
- `/docs/INSTRUCTOR_COURSE_ASSIGNMENT.md` - Assignment guide
- `/migrations/fix-instructor-records.sql` - Creates instructor records for all users

## Code Changes (Already Committed)
The following files were previously updated to support the `course_instructors` table:
- `/public/instructor/courses.php` - Now checks `course_instructors` table
- `/public/api/course-assignments.php` - Returns assignments with computed `is_lead`
- `/public/api/course-assignments/update.php` - Uses `role` field instead of `is_lead`
