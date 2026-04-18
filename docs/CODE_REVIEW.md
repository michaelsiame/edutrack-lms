# EduTrack LMS - Comprehensive Code Review (v2 - Updated)

**Date:** 2026-04-18
**Based on:** Latest main branch (commit e9656ed)

## System Overview

**Tech Stack:** PHP 8.0+ (custom framework), MySQL/MariaDB, Tailwind CSS (CDN), Alpine.js, Chart.js, Font Awesome 6.4.0, PHPMailer, TCPDF, Google OAuth, Lenco Payment Gateway.

**Architecture:** Server-side rendered PHP with file-based routing, session-based auth, PDO database layer (singleton pattern), role-based access control (admin/instructor/student).

---

## PREVIOUSLY REPORTED ISSUES - NOW FIXED

The following issues from the v1 review have been resolved:

| Issue | Status | How Fixed |
|-------|--------|-----------|
| `learning.js` not loaded in `learn.php` | FIXED | Script tag added at learn.php:704, variables exported at lines 697-701 |
| JS calls `/api/progress.php` (nonexistent) | FIXED | Changed to `/api/lesson-progress.php` in both course.js and learning.js |
| `toggleModuleSection()` missing event param | FIXED | Function signature now includes `event` parameter |
| `u.name` column referenced (doesn't exist) | FIXED | Changed to `CONCAT(u.first_name, ' ', u.last_name)` |
| Wrong instructor JOIN (users instead of instructors table) | FIXED | All files now use correct `LEFT JOIN instructors i ON c.instructor_id = i.id LEFT JOIN users u ON i.user_id = u.id` |
| Two incompatible flash message systems | FIXED | `setFlashMessage()` now delegates to `flash()`, with backward-compatible fallback in `getFlashMessage()` |
| Admin sidebar broken links (10 broken) | FIXED | All links now use `?page=` format; unimplemented pages (categories, reviews, certificates, reports) commented out |
| `markAllRead()` HTTP method mismatch | FIXED | POST method + `mark_all_as_read` action works correctly with API handler |
| Missing email notifications (announcements & password reset) | FIXED | Email sending implemented in `quick-actions.php` and `users_handler.php` with templates |
| `course.php` only accepted `?id=` | FIXED | Now handles both `?id=` and `?slug=` parameters |
| Missing authorization on bulk grade | FIXED | `bulkGrade()` now verifies instructor owns the course |
| No 404 page | FIXED | `public/404.php` created |
| No breadcrumbs | FIXED | `src/templates/breadcrumbs.php` created and used in learn.php |
| Color palette inconsistency across templates | FIXED | Shared `public/assets/js/tailwind-config.js` created; loaded by all 3 header templates |

---

## CRITICAL BUGS (Will cause runtime errors)

### BUG-1: Orphaned JavaScript closing braces in `header.php` - BREAKS ALL PAGES

**File:** `src/templates/header.php:61-64`

The shared Tailwind config was extracted to `tailwind-config.js`, but the closing braces of the old inline config were left behind:

```html
<script src="<?= asset('js/tailwind-config.js') ?>"></script>
                }
            }
        }
    </script>
```

Lines 61-64 contain stray `}` characters followed by a `</script>` tag that has no matching `<script>` open tag. This produces invalid HTML/JavaScript on **every page** that uses the main header template (all public-facing pages).

**Impact:** Browsers may throw JS errors, potentially breaking subsequent script execution. The `</script>` tag could close a previously opened script tag prematurely.

**Fix:** Delete lines 61-64 entirely. The config is already loaded via the external JS file on line 60.

---

### BUG-2: Wrong enrollment status enum value in announcement emails

**File:** `public/instructor/quick-actions.php:109`

```php
WHERE e.course_id = ? AND e.enrollment_status IN ('Active', 'In Progress', 'Completed')
```

The `enrollment_status` enum in the database is defined as:
```sql
`enrollment_status` enum('Enrolled','In Progress','Completed','Dropped','Expired')
```

There is **no 'Active' value** - it should be `'Enrolled'`. Students in the default enrollment state will NOT receive announcement emails because the query filters them out.

**Fix:** Change `'Active'` to `'Enrolled'`:
```php
WHERE e.course_id = ? AND e.enrollment_status IN ('Enrolled', 'In Progress', 'Completed')
```

---

### BUG-3: Tailwind CDN not version-pinned in 2 of 3 header templates

| Template | CDN URL | Pinned? |
|----------|---------|---------|
| `src/templates/header.php:48` | `cdn.tailwindcss.com/3.4.1` | YES |
| `src/templates/admin-header.php:17` | `cdn.tailwindcss.com` | **NO** |
| `src/templates/instructor-header.php:31` | `cdn.tailwindcss.com` | **NO** |

Admin and instructor pages will auto-update to whatever Tailwind version is latest. If Tailwind releases a breaking change (e.g., v4), these pages will break without any code change.

**Fix:** Change both to `https://cdn.tailwindcss.com/3.4.1` to match `header.php`.

---

## HIGH SEVERITY ISSUES

### HIGH-1: 5 empty JavaScript files (all 0 bytes)

| File | Size | Loaded By | Impact |
|------|------|-----------|--------|
| `public/assets/js/main.js` | 0 bytes | Removed from footer.php (good) | None currently - comment says "add back when needed" |
| `public/assets/js/auth.js` | 0 bytes | Not loaded anywhere | Dead file |
| `public/assets/js/admin.js` | 0 bytes | Not loaded anywhere | Admin JS features missing |
| `public/assets/js/quiz.js` | 0 bytes | Not loaded anywhere | Quiz client-side features missing |
| `public/assets/js/video-player.js` | 0 bytes | Not loaded anywhere | Video player features missing |

**Impact:** These files suggest planned functionality that was never implemented:
- `quiz.js` - Client-side quiz timer, auto-save, question navigation
- `admin.js` - Admin panel interactivity (currently relies on inline JS in admin templates)
- `video-player.js` - Custom video player controls beyond basic HTML5
- `auth.js` - Client-side form validation for login/register

**Fix:** Either implement the intended functionality or delete the empty files to avoid confusion. For `quiz.js`, the student take-quiz page uses inline JS for the timer, which could be extracted here.

---

### HIGH-2: 2 empty CSS files (all 0 bytes)

| File | Size | Impact |
|------|------|--------|
| `public/assets/css/main.css` | 0 bytes | Not loaded (removed from header.php) |
| `public/assets/css/responsive.css` | 0 bytes | Not loaded anywhere |

**Fix:** Delete these empty files or populate them.

---

### HIGH-3: N+1 query in quiz page - fires N extra queries for question options

**File:** `public/student/take-quiz.php:51-57`

```php
foreach ($questions as &$question) {
    $question['options'] = $db->fetchAll("
        SELECT * FROM quiz_question_options WHERE question_id = ?
        ORDER BY id ASC
    ", [$question['id']]);
}
```

A quiz with 20 questions fires 20+ additional DB queries.

**Fix:** Fetch all options in one query and group by question_id in PHP:
```php
$allOptions = $db->fetchAll("
    SELECT * FROM quiz_question_options
    WHERE question_id IN (" . implode(',', array_column($questions, 'id')) . ")
    ORDER BY question_id, id ASC
");
// Group by question_id
$optionsByQuestion = [];
foreach ($allOptions as $opt) {
    $optionsByQuestion[$opt['question_id']][] = $opt;
}
foreach ($questions as &$question) {
    $question['options'] = $optionsByQuestion[$question['id']] ?? [];
}
```

---

### HIGH-4: N+1 query in my-courses.php - fires N extra queries for module completion

**File:** `public/my-courses.php:64-75`

```php
foreach ($enrollments as &$enrollment) {
    $enrollment['modules'] = $db->fetchAll("...", [$enrollment['id'], $enrollment['course_id']]);
}
```

With 20 enrollments, this fires 20 additional queries.

**Fix:** Fetch all module completion data in a single query using `WHERE course_id IN (...)` and group in PHP.

---

### HIGH-5: Performance - 4 separate COUNT queries for assignment stats

**File:** `public/student/assignments.php:56-82`

```php
$counts = [
    'all' => count($db->fetchAll(...)),    // Full table scan
    'pending' => count($db->fetchAll(...)),  // Full table scan
    'submitted' => count($db->fetchAll(...)), // Full table scan
    'graded' => count($db->fetchAll(...))    // Full table scan
];
```

Fetches ALL rows 4 times just to count them. Same issue in `public/student/quizzes.php:52-73` (3 separate queries).

**Fix:** Use SQL `COUNT()` with `CASE WHEN` in a single query:
```php
$counts = $db->fetchOne("
    SELECT
        COUNT(*) as all_count,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
        SUM(CASE WHEN status = 'graded' THEN 1 ELSE 0 END) as graded
    FROM assignments a
    JOIN enrollments e ON ...
    WHERE e.user_id = ?
", [$userId]);
```

---

### HIGH-6: Google Fonts not loaded in admin template

**File:** `src/templates/admin-header.php`

The admin header does NOT load Google Fonts (Inter), but `header.php:54` and `instructor-header.php:36` both do. The admin CSS references `font-family: 'Inter', sans-serif` but without loading the font, it falls back to system fonts.

**Fix:** Add Google Fonts import to `admin-header.php`:
```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

---

### HIGH-7: Admin pages not implemented (Categories, Reviews, Certificates, Reports)

These admin features are mentioned in the sidebar (currently commented out) but have no implementation:

1. **Categories management** - No `admin/pages/categories.php`
2. **Reviews management** - No `admin/pages/reviews.php`
3. **Certificates management** - No `admin/pages/certificates.php`
4. **Reports/Analytics** - No `admin/pages/reports.php`

These are important admin features for a production LMS.

**Fix:** Create these page files with CRUD functionality:
- `categories.php` - CRUD for course categories (currently managed only via DB)
- `reviews.php` - View/moderate course reviews
- `certificates.php` - View issued certificates, manage templates
- `reports.php` - System analytics, enrollment trends, revenue reports

---

## MEDIUM SEVERITY ISSUES

### MED-1: Admin dashboard `system_settings` query pattern mismatch

**File:** `public/admin/index.php:29`

```php
$settings = $db->fetchOne("SELECT * FROM system_settings WHERE setting_id = 1");
```

But the settings page (`admin/pages/settings.php:47-50`) treats the table as key-value pairs with `setting_key`/`setting_value` columns. The `setting_id = 1` approach assumes a single-row design that conflicts with the key-value pattern used everywhere else.

**Fix:** Use consistent key-value lookup:
```php
$settingsRows = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings");
$settings = array_column($settingsRows, 'setting_value', 'setting_key');
$currency = $settings['default_currency'] ?? 'ZMW';
```

---

### MED-2: `learn.php` sidebar missing lesson completion indicators

The lesson sidebar in `learn.php` shows lesson titles but has NO visual indicator for completed lessons. The `lesson_progress` data exists in the DB and is queried for other purposes, but the sidebar doesn't show checkmarks or completion state.

**Fix:** Query `lesson_progress` for the enrollment and add a completion checkmark icon next to completed lessons in the sidebar.

---

### MED-3: Instructor courses page has redundant instructor ID check

**File:** `public/instructor/courses.php:60-61`

```php
$whereConditions = ["(c.instructor_id = ? OR c.instructor_id = ?)"];
$params = [$instructorId, $userId];
```

This checks both `$instructorId` (from instructors table) and `$userId` (from users table). After `Instructor::getOrCreate()`, `$instructorId` is the correct instructors table ID. The `$userId` fallback is wrong because `courses.instructor_id` references `instructors.id`, not `users.id`. If they happen to match numerically, it works by accident.

**Fix:** Remove the redundant condition:
```php
$whereConditions = ["c.instructor_id = ?"];
$params = [$instructorId];
```

---

### MED-4: No lesson completion tracking on sidebar in learn.php

Students can't see which lessons they've already completed in the course sidebar. There's no checkmark, color change, or progress indicator per lesson.

**Fix:** Query lesson_progress for the current enrollment and render completion icons next to each lesson in the sidebar.

---

### MED-5: Profile page missing null coalescing for stats

**File:** `public/profile.php:22-27`

```php
'active_courses' => $studentStats['in_progress_courses'],
'completed_courses' => $studentStats['completed_courses'],
```

If `Statistics::getStudentStats()` returns an array missing a key, this throws a PHP notice/warning. The dashboard page (`dashboard.php`) correctly uses `?? 0` for the same fields.

**Fix:** Add `?? 0` to each stat:
```php
'active_courses' => $studentStats['in_progress_courses'] ?? 0,
```

---

### MED-6: `student/help.php` has placeholder phone number

**File:** `public/student/help.php:270`

```html
<a href="tel:+260XXXXXXXX">
```

This is a non-functional placeholder that users might try to call. Same issue in `src/mail/payment-received.php:134` with `+260 XXX XXX XXX`.

**Fix:** Pull contact info from `system_settings` table dynamically, or replace with actual support phone number.

---

### MED-7: Inconsistent date/time formatting across pages

Different pages format dates differently:
- `timeAgo()` - relative time (dashboards)
- `formatDate()` - absolute date (profile)
- Raw `date()` calls (instructor dashboard)
- `DateTime::format()` (learn.php live sessions)

No single standard is followed.

**Fix:** Establish a formatting convention and use helper functions consistently.

---

### MED-8: `.env` file committed to repository

The `.env` file containing production database credentials, SMTP passwords, API keys, and encryption secrets is tracked in git. This is a major security risk if the repository is public or shared.

**Fix:** Add `.env` to `.gitignore` and remove from tracking with `git rm --cached .env`. Rotate all exposed credentials.

---

## LOW SEVERITY ISSUES

### LOW-1: Office temporary files committed to repo

Files `~$Topic_2_Operating_Systems.pptx` and `~$Topic_3_Programming_Logic.pptx` are Microsoft Office lock/temporary files that should never be committed.

**Fix:** Delete them and add `~$*` to `.gitignore`.

---

### LOW-2: Python `__pycache__` committed to repo

`__pycache__/generate_all_module1_presentations.cpython-312.pyc` is a compiled Python bytecode file.

**Fix:** Delete and add `__pycache__/` to `.gitignore`.

---

### LOW-3: No dark mode support

`whatsapp-button.css` has `@media (prefers-color-scheme: dark)` but no other part of the system supports dark mode. Users with system dark mode see a dark WhatsApp button on an otherwise light page.

**Fix:** Either implement dark mode consistently or remove the partial support in whatsapp-button.css.

---

### LOW-4: No loading skeleton/states for admin AJAX modals

Admin pages that fetch user/course data via AJAX (edit modals) show no loading indicator during the fetch. The modal opens empty and then populates.

**Fix:** Add a loading spinner inside modals during AJAX fetches.

---

### LOW-5: `main.js` loaded in footer.php is commented out but comment is misleading

**File:** `src/templates/footer.php:219`
```html
<!-- Main JS file removed - was empty. Add back when needed. -->
```

This is fine for now, but the empty `main.js` file still exists on disk causing confusion.

**Fix:** Delete the empty file.

---

## ADMIN PAGES - MISSING FUNCTIONALITY

These admin features need to be built for a complete system:

| Feature | Status | Priority |
|---------|--------|----------|
| Categories CRUD | NOT IMPLEMENTED | High - needed to manage course categories |
| Reviews moderation | NOT IMPLEMENTED | Medium - needed to manage/moderate reviews |
| Certificates management | NOT IMPLEMENTED | Medium - needed to view/manage issued certs |
| Reports & Analytics | NOT IMPLEMENTED | High - needed for admin insights |
| System email logs | NOT IMPLEMENTED | Low - useful for debugging email issues |
| Audit trail / activity logs viewer | NOT IMPLEMENTED | Low - data exists in activity_logs table |

---

## SECURITY NOTES (from existing SYSTEM_REVIEW.md)

These were previously documented in `docs/SYSTEM_REVIEW.md` but remain relevant:

1. **`.env` committed to repo** - Contains production DB credentials, API keys, SMTP passwords
2. **Missing authorization on quiz/assignment submissions** - Any authenticated user can potentially submit for any student
3. **Payment webhook idempotency** - Duplicate webhooks could double-credit payments
4. **Session not regenerated on role switch** - Session fixation risk

---

## PRIORITY ACTION PLAN

### Phase 1: Critical (Fix immediately - things are broken)
1. **Remove orphaned JS braces from header.php** (BUG-1) - Delete lines 61-64 - 1 file, 30 seconds
2. **Fix 'Active' -> 'Enrolled' enum value** in quick-actions.php:109 (BUG-2) - 1 file, 30 seconds
3. **Pin Tailwind CDN version** in admin-header.php and instructor-header.php (BUG-3) - 2 files

### Phase 2: High Priority (Fix before production)
4. **Fix N+1 queries** in take-quiz.php, my-courses.php, assignments.php, quizzes.php (HIGH-3/4/5) - 4 files
5. **Add Google Fonts** to admin-header.php (HIGH-6) - 1 file
6. **Delete empty JS/CSS files** or implement them (HIGH-1/2) - 7 files
7. **Build missing admin pages** - categories, reviews, certificates, reports (HIGH-7) - 4 new files

### Phase 3: Medium Priority (Improve quality)
8. Fix admin settings query pattern (MED-1) - 1 file
9. Add lesson completion indicators to learn.php sidebar (MED-2/4)
10. Fix redundant instructor ID check (MED-3) - 1 file
11. Fix profile stats null safety (MED-5) - 1 file
12. Replace placeholder contact info (MED-6) - 2 files
13. Standardize date formatting (MED-7)
14. Remove `.env` from git tracking (MED-8)

### Phase 4: Cleanup
15. Delete Office temp files and `__pycache__` (LOW-1/2)
16. Add loading states for admin modals (LOW-4)
17. Delete empty `main.js` file (LOW-5)
