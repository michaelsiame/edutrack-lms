# EduTrack LMS - Database Schema Mismatch Analysis Report

**Date:** 2025-11-19  
**Analysis Scope:** All model files in `/src/classes/`  
**Total Files Analyzed:** 21 PHP model classes  
**Critical Issues Found:** 2 major table name mismatches  
**Total Affected Locations:** 35+ code locations  

---

## Executive Summary

The EduTrack LMS codebase has **significant schema alignment issues** between the actual database tables and what the model classes are expecting. The primary issues are:

1. **Table Name Mismatch #1:** `categories` vs `course_categories` (8 locations)
2. **Table Name Mismatch #2:** `course_modules` vs `modules` (20+ locations)
3. **Column Name Mismatch:** `order_index` vs `display_order` (1 location)

These mismatches will cause database errors when:
- Creating/updating/deleting course categories
- Managing modules and lessons
- Navigating course structure
- Tracking student progress

---

## Detailed Findings

### MISMATCH #1: Category Table Name

**File:** `/home/user/edutrack-lms/src/classes/Category.php`

**Canonical Schema:** `course_categories`  
**Code Uses:** `categories` (mixed with `course_categories`)

**Affected Lines:**
| Line | Code | Issue | Severity |
|------|------|-------|----------|
| 26 | `FROM categories c` | Should be `course_categories` | HIGH |
| 52 | `FROM categories WHERE slug = ?` | Should be `course_categories` | HIGH |
| 66 | `FROM categories c` | Should be `course_categories` | HIGH |
| 91 | `FROM categories c` | Should be `course_categories` | HIGH |
| 104 | `FROM categories c` | Should be `course_categories` | HIGH |
| 115 | `INSERT INTO categories` | Should be `course_categories` | HIGH |
| 158 | `UPDATE categories SET` | Should be `course_categories` | HIGH |
| 178 | `DELETE FROM course_categories` | Actually correct here! Inconsistent | HIGH |

**Impact:**
- `Category::load()` will fail (line 26)
- `Category::findBySlug()` will fail (line 52)
- `Category::all()` will fail (line 66)
- `Category::active()` will fail (line 104)
- `Category::getActiveWithCourses()` will fail (line 91)
- `Category::create()` will fail (line 115)
- `Category::update()` will fail (line 158)
- Only `delete()` works (line 178)

**Recommendation:**
Replace all occurrences of `categories` with `course_categories` in Category.php, except line 178 which is already correct.

---

### MISMATCH #2: Modules Table Name

**Files:** 
- `/home/user/edutrack-lms/src/classes/Module.php`
- `/home/user/edutrack-lms/src/classes/Lesson.php`
- `/home/user/edutrack-lms/src/classes/Assignment.php`
- `/home/user/edutrack-lms/src/classes/Course.php`
- `/home/user/edutrack-lms/src/classes/Quiz.php`
- `/home/user/edutrack-lms/src/classes/Progress.php`

**Canonical Schema:** `modules`  
**Code Uses:** `course_modules`

**Module.php - Affected Lines:**
| Line | Code | Method |
|------|------|--------|
| 26 | `FROM course_modules m` | `load()` |
| 55 | `FROM course_modules m` | `getByCourse()` |
| 68 | `INSERT INTO course_modules` | `create()` |
| 107 | `UPDATE course_modules SET` | `update()` |
| 125 | `DELETE FROM course_modules` | `delete()` |

**Lesson.php - Affected Lines:**
| Line | Code | Method |
|------|------|--------|
| 27 | `JOIN course_modules m` | `load()` |
| 67 | `JOIN course_modules m` | `getByCourse()` |
| 157-159 | Multiple `course_modules m2` | `getNext()` |
| 180-182 | Multiple `course_modules m2` | `getPrevious()` |

**Assignment.php - Affected Lines:**
| Line | Code | Method |
|------|------|--------|
| 28 | `LEFT JOIN course_modules m` | `load()` |
| 58 | `JOIN course_modules m` | `getByCourse()` |

**Course.php - Affected Lines:**
| Line | Code | Method |
|------|------|--------|
| 309 | `SELECT * FROM course_modules` | `getModules()` |

**Progress.php - Affected Lines:**
| Line | Code | Method |
|------|------|--------|
| 26 | `JOIN course_modules m` | `getCourseProgress()` |
| 35 | `JOIN course_modules m` | `getCourseProgress()` |
| 63 | `JOIN course_modules m` | `getCourseProgress()` |
| 72 | `JOIN course_modules m` | `getCourseProgress()` |
| 196 | `JOIN course_modules m` | `getCurrentLesson()` |
| 211 | `JOIN course_modules m` | `getCurrentLesson()` |
| 236 | `JOIN course_modules m` | `getNextLesson()` |
| 279 | `JOIN course_modules m` | `checkCourseCompletion()` |
| 374 | `JOIN course_modules m` | `getCourseLessonsProgress()` |

**Total: 21 occurrences across 6 files**

**Impact:**
- Course module retrieval will fail
- Lesson navigation will fail
- Progress tracking will fail
- Student can't access course structure

---

### MISMATCH #3: Column Name - display_order vs order_index

**File:** `/home/user/edutrack-lms/src/classes/Progress.php`

**Canonical Schema Column:** `display_order` (in modules table)  
**Code Uses:** Mixed usage

**Affected Lines:**
| Line | Code | Issue |
|------|------|-------|
| 213 | `ORDER BY m.display_order ASC` | Correct ✓ |
| 239 | `ORDER BY m.display_order ASC` | Correct ✓ |
| 377 | `ORDER BY m.order_index, l.order_index` | Should be `m.display_order` |

**Impact:**
- Line 377 in `getCourseLessonsProgress()` will fail with "Unknown column 'm.order_index'"
- Student progress tracking won't work correctly

---

## Summary Table

| Issue | Table | Files Affected | Locations | Severity |
|-------|-------|----------------|-----------|----------|
| Name: `categories` → `course_categories` | course_categories | 1 | 8 | HIGH |
| Name: `course_modules` → `modules` | modules | 6 | 21 | HIGH |
| Column: `order_index` → `display_order` | modules | 1 | 1 | MEDIUM |
| **TOTAL** | **2 tables** | **7 files** | **30 locations** | **HIGH** |

---

## Recommended Fixes

### Fix #1: Category.php
**Find and replace all 7 occurrences** (excluding line 178 which is correct):
```
Find: FROM categories
Replace: FROM course_categories

Find: INSERT INTO categories
Replace: INSERT INTO course_categories

Find: UPDATE categories SET
Replace: UPDATE course_categories SET
```

### Fix #2: Module.php (5 occurrences)
**Find and replace:**
```
Find: course_modules
Replace: modules
```

### Fix #3: Lesson.php (4+ occurrences)
**Find and replace:**
```
Find: course_modules
Replace: modules
```

### Fix #4: Assignment.php (2 occurrences)
**Find and replace:**
```
Find: course_modules
Replace: modules
```

### Fix #5: Course.php (1 occurrence)
**Find and replace:**
```
Find: course_modules
Replace: modules
```

### Fix #6: Progress.php (9 occurrences + 1 column name)
**Find and replace:**
```
Find: course_modules
Replace: modules

Find: ORDER BY m.order_index, l.order_index (line 377)
Replace: ORDER BY m.display_order, l.display_order
```

### Fix #7: Quiz.php
**Verify course ID lookup** - may need similar fixes for loading quiz course info

---

## Testing Recommendations

After applying fixes, test:

1. **Category Operations**
   - View all categories
   - Create new category
   - Update category
   - Delete category

2. **Module Operations**
   - View course modules
   - Create module
   - Update module
   - Delete module

3. **Lesson Navigation**
   - Get next/previous lesson
   - Navigate course lessons
   - View lesson progress

4. **Progress Tracking**
   - Calculate course progress percentage
   - Track time spent
   - Mark lessons complete

5. **Assignment/Quiz**
   - Retrieve assignments for course
   - Retrieve quizzes for course
   - Calculate student progress

---

## Files Requiring Updates

1. **CRITICAL:** `/home/user/edutrack-lms/src/classes/Category.php` (7 changes)
2. **CRITICAL:** `/home/user/edutrack-lms/src/classes/Module.php` (5 changes)
3. **CRITICAL:** `/home/user/edutrack-lms/src/classes/Lesson.php` (4 changes)
4. **HIGH:** `/home/user/edutrack-lms/src/classes/Progress.php` (10 changes)
5. **HIGH:** `/home/user/edutrack-lms/src/classes/Assignment.php` (2 changes)
6. **MEDIUM:** `/home/user/edutrack-lms/src/classes/Course.php` (1 change)
7. **VERIFY:** `/home/user/edutrack-lms/src/classes/Quiz.php` (check for similar issues)

---

## Verification

The correct table names are defined in:
- `/home/user/edutrack-lms/database/schema_compatibility_fix.sql` (lines 15-49)
- `/home/user/edutrack-lms/database/complete_lms_schema.sql` (original PascalCase names)

