# EduTrack LMS - System Review & Instructor Dashboard Analysis
## Review Date: December 25, 2025
## Focus: Michael Siame as Instructor for Microsoft Office Package

---

## EXECUTIVE SUMMARY

After comprehensive review of the EduTrack LMS system and specifically testing the instructor dashboard for Michael Siame managing the "Certificate in Microsoft Office Suite" course, **critical issues have been identified** that prevent proper functionality.

**Status**: 🔴 **CRITICAL ISSUES FOUND** - System requires immediate fixes

**Key Finding**: The newly created course assignment system (via `course_instructors` table) is **NOT integrated** with the instructor dashboard pages, meaning instructors assigned to courses via the admin panel cannot access those courses.

---

## SYSTEM ARCHITECTURE OVERVIEW

### Technology Stack
- **Backend**: PHP 8.0+, MySQL/MariaDB 11.8.3
- **Frontend**: Vanilla PHP/HTML + React Admin Dashboard
- **Database**: 65+ tables, InnoDB engine
- **Authentication**: Session-based with role-based access control

### Key Components
- **27+ Model Classes**: User, Course, Instructor, Module, Lesson, etc.
- **Middleware**: Role-based access control (instructor-only.php, admin-only.php)
- **8 Instructor Pages**: Dashboard, Courses, Assignments, Quizzes, Students, Analytics, Live Sessions
- **7 Module/Lesson Actions**: CRUD handlers for course content

---

## CRITICAL ISSUES DISCOVERED

### 🔴 ISSUE #1: Course Assignment System Not Integrated

**Problem**: The course assignment API created in `/public/api/course-assignments/` allows admins to assign instructors to courses via the `course_instructors` junction table, but **none of the instructor dashboard pages check this table**.

**Impact**:
- Instructors assigned via the admin panel **cannot see or access** their assigned courses
- Only courses where `courses.instructor_id` matches work
- The Microsoft Office course has `instructor_id = 1`, so Michael Siame (who would need a different instructor_id) cannot access it

**Affected Files**:
- `/public/instructor/index.php` (line 159)
- `/public/instructor/courses.php` (lines 66-86)
- `/public/instructor/course-edit.php` (lines 70-80)
- `/public/instructor/courses/modules.php` (lines 32-43)
- All other instructor pages that query courses

**Current Query Pattern** (courses.php, line 72):
```php
WHERE c.instructor_id = ? OR c.instructor_id = ?
```

**Should Be**:
```php
WHERE c.instructor_id IN (?, ?)
OR c.id IN (SELECT course_id FROM course_instructors WHERE instructor_id = ?)
```

---

### 🔴 ISSUE #2: Inconsistent Instructor ID Handling

**Problem**: Different pages handle missing instructor records differently:

**index.php (line 105)**:
```php
$instructorId = $instructorRecord ? $instructorRecord['id'] : $userId;  // Fallback to user_id
```

**courses.php (line 54)**:
```php
$instructorId = $instructorRecord ? $instructorRecord['id'] : null;  // Set to null
```

**Impact**:
- Inconsistent behavior across pages
- Dashboard might show stats while course list is empty
- Confusing user experience

**Example Scenario**:
- Michael Siame (user_id 6) logs in without instructor record
- Dashboard uses user_id=6 as fallback, shows wrong stats
- Courses page uses null, shows no courses at all

---

### 🟡 ISSUE #3: Dual ID System Legacy Support

**Problem**: System tries to support both modern (`instructors.id`) and legacy (`users.id`) as instructor_id values.

**Code Pattern** (appears in multiple files):
```php
$canEdit = hasRole('admin') ||
           ($instructorId && $course->getInstructorId() == $instructorId) ||
           ($course->getInstructorId() == $userId);  // Legacy fallback
```

**Impact**:
- Code complexity and maintenance burden
- Potential security risks if assumptions are wrong
- Confusion about which ID to use

**Recommendation**: Complete migration, remove legacy support

---

### 🟡 ISSUE #4: Missing Auto-Creation in Key Areas

**Problem**: Only `course-edit.php` automatically creates instructor records (lines 82-87):

```php
if (!$instructorId) {
    require_once '../../src/classes/Instructor.php';
    $instructor = Instructor::getOrCreate($userId);
    $instructorId = $instructor->getId();
}
```

But `courses.php`, `index.php`, and other pages don't do this.

**Impact**:
- Inconsistent behavior
- User might not be able to view courses until they try to edit one
- Migration script must be run manually

---

### 🟢 ISSUE #5: Course Data from Database

**Microsoft Office Course Details** (from database/complete_lms_schema.sql):
```
Course ID: 1
Title: Certificate in Microsoft Office Suite
Slug: microsoft-office-suite
Instructor ID: 1  ← THIS IS THE PROBLEM!
Category ID: 1
Level: Beginner
Price: 2500.00 ZMW
Status: published
```

**Michael Siame's Expected Data**:
```
User ID: 6
Username: michaelsiame570
Expected Instructor ID: Will be created by migration, likely ID 8 or higher
Team Member ID: 3
Position: Head of ICT
```

**The Gap**:
- Course has instructor_id = 1
- Michael needs to be assigned via `course_instructors` table
- But instructor dashboard doesn't check `course_instructors` table!

---

## INSTRUCTOR DASHBOARD PAGES ANALYSIS

### 1. Main Dashboard (`/public/instructor/index.php`)

**Purpose**: Overview, stats, recent activity

**Query Issues**:
- Line 159: `WHERE c.instructor_id = ?` - Only checks direct assignment
- Line 145: Pending assignments query - Only checks direct assignment
- Line 130: Recent enrollments - Only checks direct assignment

**Verdict**: ❌ **Will NOT show courses assigned via course_instructors table**

---

### 2. Courses List (`/public/instructor/courses.php`)

**Purpose**: View all instructor's courses

**Query Issues**:
- Lines 66-74: Checks both `instructor_id = ?` and `instructor_id = ?` (instructorId and userId)
- Does NOT check `course_instructors` table
- Inconsistent handling when `instructorId` is null

**Verdict**: ❌ **Will NOT show courses assigned via course_instructors table**

---

### 3. Course Edit (`/public/instructor/course-edit.php`)

**Purpose**: Edit course details

**Security Check**:
- Lines 73-75: Ownership verification
- Auto-creates instructor record if missing (good!)
- Does NOT check `course_instructors` table

**Verdict**: ❌ **Will DENY access to courses assigned via course_instructors table**

---

### 4. Modules Management (`/public/instructor/courses/modules.php`)

**Purpose**: Manage course structure (modules and lessons)

**Security Check**:
- Lines 36-38: Same ownership pattern
- Does NOT check `course_instructors` table

**Verdict**: ❌ **Will DENY access to courses assigned via course_instructors table**

---

### 5. Students (`/public/instructor/students.php`)

**Analysis Not Shown**: Likely same issue

**Verdict**: ❌ **Assumed broken**

---

### 6. Assignments (`/public/instructor/assignments.php`)

**Analysis Not Shown**: Likely same issue

**Verdict**: ❌ **Assumed broken**

---

##REQUIRED FIXES

### Fix #1: Integrate course_instructors Table

**ALL instructor dashboard queries must be updated** to check the `course_instructors` table.

**New Query Pattern**:
```sql
SELECT c.*, ...
FROM courses c
LEFT JOIN course_instructors ci ON c.id = ci.course_id
WHERE c.instructor_id IN (?, ?)  -- Direct assignment (new and legacy)
   OR ci.instructor_id = ?        -- Multi-instructor assignment
GROUP BY c.id
```

**Files to Update**:
1. `/public/instructor/index.php` - 5 queries
2. `/public/instructor/courses.php` - 1 main query
3. `/public/instructor/course-edit.php` - ownership check
4. `/public/instructor/courses/modules.php` - ownership check
5. `/public/instructor/students.php` - enrollment query
6. `/public/instructor/assignments.php` - submissions query
7. `/public/instructor/quizzes.php` - quiz query
8. `/public/instructor/analytics.php` - stats query
9. `/src/classes/Statistics.php` - `getInstructorStats()` method

---

### Fix #2: Standardize Instructor ID Handling

**Decision**: Always create instructor record if missing

**Implementation**:
```php
// At the top of EVERY instructor page:
$db = Database::getInstance();
$userId = currentUserId();

// Always get or create instructor record
require_once '../../src/classes/Instructor.php';
$instructor = Instructor::getOrCreate($userId);
$instructorId = $instructor->getId();
```

This ensures consistent behavior across all pages.

---

### Fix #3: Run Database Migration

**Required Action**: Apply the migration script

```bash
mysql -u root -p edutrack_lms < migrations/fix-instructor-records.sql
```

**What It Does**:
- Creates instructor record for Michael Siame (user_id 6)
- Creates instructor record for Chilala Moonga (user_id 27)
- Creates instructor records for ALL users with instructor role

---

### Fix #4: Assign Michael to Microsoft Office Course

**Two Options**:

**Option A: Via Admin Dashboard (Recommended)**
1. Log in as admin
2. Navigate to "Course Assignments"
3. Find "Certificate in Microsoft Office Suite"
4. Click "Manage Assignments"
5. Select Michael Siame
6. Optionally set him as lead instructor
7. Save

**Option B: Via Direct SQL**
```sql
-- First, get Michael's instructor_id after running migration
SELECT id FROM instructors WHERE user_id = 6;  -- Let's say it returns 8

-- Then assign him to course 1
INSERT INTO course_instructors (course_id, instructor_id, is_lead, assigned_at)
VALUES (1, 8, 1, NOW());
```

---

## IMMEDIATE ACTION PLAN

### Phase 1: Database Setup ✅ (Completed)
- [x] Migration script created
- [x] Course assignment API created
- [x] Admin interface created

### Phase 2: Integration (REQUIRED NOW) 🔴
- [ ] Update all instructor dashboard queries to check `course_instructors`
- [ ] Standardize instructor ID handling across all pages
- [ ] Test all instructor pages with course_instructors assignments

### Phase 3: Deployment 🟡
- [ ] Run migration script on production database
- [ ] Assign Michael Siame to Microsoft Office course
- [ ] Test end-to-end workflow
- [ ] Monitor for errors

---

## TESTING CHECKLIST FOR MICHAEL SIAME

Once fixes are applied:

### Pre-Test Setup
- [ ] Migration script applied
- [ ] Michael has instructor record in database
- [ ] Michael assigned to "Microsoft Office Suite" via course_instructors

### Test Cases
1. **Login**: ✓ Michael can log in with credentials
2. **Dashboard**: ✓ See Microsoft Office course in "My Courses" section
3. **Courses List**: ✓ See Microsoft Office course in courses list
4. **Course Edit**: ✓ Can click and edit Microsoft Office course details
5. **Modules**: ✓ Can access and manage modules/lessons for the course
6. **Students**: ✓ Can see enrolled students (if any)
7. **Assignments**: ✓ Can create and grade assignments
8. **Analytics**: ✓ Can see course statistics
9. **Stats**: ✓ Dashboard shows correct course count and student count
10. **Permissions**: ✓ Cannot edit courses NOT assigned to him

---

## SECURITY CONSIDERATIONS

### Current Issues
- Dual ID system could allow unintended access
- Missing validation in some ownership checks
- No audit log for instructor assignments

### Recommendations
1. Add activity logging for course access
2. Implement strict ownership validation everywhere
3. Remove legacy user_id fallback after migration
4. Add unit tests for ownership verification

---

## PERFORMANCE NOTES

### Potential Bottlenecks
- `course_instructors` JOIN adds query complexity
- Multiple subqueries in dashboard stats
- No caching for instructor course list

### Recommendations
1. Add database index on `course_instructors.instructor_id`
2. Implement Redis caching for course lists
3. Use eager loading for related data
4. Consider materialized view for instructor stats

---

## CONCLUSION

The EduTrack LMS system has a **solid foundation** but the newly created course assignment feature is **not integrated** with the instructor dashboard.

**Current State**: ❌ Michael Siame CANNOT access Microsoft Office course
**After Fixes**: ✅ Michael Siame CAN fully manage the course

**Estimated Fix Time**: 2-3 hours for complete integration
**Priority**: 🔴 **CRITICAL** - Required for system functionality

---

## RECOMMENDATIONS FOR USER

1. **IMMEDIATE**: Apply the fixes outlined in this document
2. **SHORT TERM**: Complete migration away from dual ID system
3. **MEDIUM TERM**: Add comprehensive test suite
4. **LONG TERM**: Consider course ownership refactoring

---

## APPENDIX: Helpful Database Queries

### Check Michael's Records
```sql
-- Check user account
SELECT * FROM users WHERE email LIKE '%siame%' OR name LIKE '%siame%';

-- Check instructor record
SELECT * FROM instructors WHERE user_id = 6;

-- Check assigned courses
SELECT c.title, ci.is_lead, ci.assigned_at
FROM course_instructors ci
JOIN courses c ON ci.course_id = c.id
WHERE ci.instructor_id = (SELECT id FROM instructors WHERE user_id = 6);

-- Check roles
SELECT r.role_name
FROM user_roles ur
JOIN roles r ON ur.role_id = r.id
WHERE ur.user_id = 6;
```

### Check Course Assignments
```sql
-- See all instructors for Microsoft Office course
SELECT i.id as instructor_id, u.name, ci.is_lead
FROM course_instructors ci
JOIN instructors i ON ci.instructor_id = i.id
JOIN users u ON i.user_id = u.id
WHERE ci.course_id = 1;
```
