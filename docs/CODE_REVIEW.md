# EduTrack LMS - Comprehensive Code Review

## System Overview

**Tech Stack:** PHP 8.0+ (custom framework), MySQL/MariaDB, Tailwind CSS (CDN), Alpine.js, Chart.js, Font Awesome 6.4.0, PHPMailer, TCPDF, Google OAuth, Lenco Payment Gateway.

**Architecture:** Server-side rendered PHP with file-based routing, session-based auth, PDO database layer (singleton pattern), role-based access control (admin/instructor/student).

---

## CRITICAL BUGS (Will cause runtime errors)

### BUG-0: `learning.js` is never loaded on the learning page

The file `public/assets/js/learning.js` (480 lines of code) contains all the learning interface JavaScript - progress tracking, video tracking, note-taking, module toggling - but it is **never included via a `<script>` tag** in `public/learn.php` or any template. All that code is completely dead.

**Fix:** Add `<script src="/assets/js/learning.js"></script>` to `learn.php` before the closing `</body>` tag, AFTER the variables are exported (see BUG-0b).

### BUG-0b: `learning.js` uses undefined `courseId` and `lessonId` variables

Even if `learning.js` were loaded, it references `courseId` and `lessonId` JavaScript variables that are never defined. These PHP variables exist in `learn.php` but are never exported to JavaScript scope.

**Affected lines in `public/assets/js/learning.js`:**
- Lines 61-62: `fetch('/api/progress.php', { body: JSON.stringify({ lesson_id: lessonId, course_id: courseId }) })`
- Lines 107-108: Same pattern for time tracking
- Lines 316-317: Same pattern for note saving

**Fix:** Add a script block in `learn.php` BEFORE loading `learning.js`:
```html
<script>
const courseId = <?= (int)$courseId ?>;
const lessonId = <?= (int)$lessonId ?>;
</script>
```

### BUG-0c: `learning.js` and `course.js` call `/api/progress.php` which does NOT exist

Both JS files make fetch calls to `/api/progress.php`, but this endpoint does not exist. The actual endpoint is `/api/lesson-progress.php`.

**Affected files:**
- `public/assets/js/course.js:382` - `fetch('/api/progress.php')`
- `public/assets/js/learning.js:56` - `fetch('/api/progress.php')`
- `public/assets/js/learning.js:102` - `fetch('/api/progress.php')`

**Fix:** Change all references from `/api/progress.php` to `/api/lesson-progress.php` (or create `/api/progress.php` as an alias).

### BUG-0d: `event` not passed as parameter in `toggleModuleSection()`

In `public/assets/js/learning.js:218`:
```javascript
function toggleModuleSection(moduleId) {
    const icon = event.currentTarget.querySelector('.module-toggle-icon');
```

The function accepts `moduleId` but then references `event.currentTarget` without `event` being a parameter. This will throw `ReferenceError: event is not defined`.

**Fix:** Change signature to `function toggleModuleSection(moduleId, event)` and update all callers, or use a different approach to find the icon.

### BUG-1: `u.name` column does not exist in `users` table

The `users` table has `first_name` and `last_name` but NO `name` column. Several queries reference `u.name` which will throw SQL errors at runtime.

**Affected files:**
- `public/learn.php:118` - `u.name as instructor_name` in live sessions sidebar query
- `public/learn.php:404` - `u.name as instructor_name` in lesson live session detail query
- `public/api/instructors.php:18` - `u.name` in instructor listing
- `public/api/instructors.php:28` - `ORDER BY u.name ASC`

**Fix:** Replace `u.name` with `CONCAT(u.first_name, ' ', u.last_name)` in all affected queries.

---

### BUG-2: Wrong JOIN on `courses.instructor_id` - joins to `users.id` instead of `instructors.id`

The `courses.instructor_id` column has a foreign key to `instructors.id` (confirmed in schema: `fk_courses_instructor`), NOT to `users.id`. Multiple files incorrectly do `LEFT JOIN users u ON c.instructor_id = u.id` which will return wrong instructor names (or NULL) whenever an instructor's `instructors.id` differs from their `users.id`.

**Affected files (WRONG join):**
- `src/classes/Course.php:33` - `load()` method
- `src/classes/Course.php:84` - `all()` method
- `src/classes/Statistics.php:449` - stats query
- `public/admin/pages/courses.php:27` - admin course listing
- `public/my-certificates.php:30` - certificate display
- `public/course-preview.php:26` - course preview

**Correct pattern (already used in some files):**
```php
LEFT JOIN instructors i ON c.instructor_id = i.id
LEFT JOIN users u ON i.user_id = u.id
```

**Files that do it correctly (reference these):**
- `public/dashboard.php:39-40`
- `public/instructor/index.php:36-37`
- `public/my-courses.php:49-50`

---

### BUG-3: Two incompatible flash message systems

The codebase has TWO different flash message APIs that store data in different session keys:

1. `setFlashMessage($message, $type)` at `src/includes/functions.php:31` - stores in `$_SESSION[$type]`
2. `flash($key, $message, $type)` at `src/includes/functions.php:322` - stores in `$_SESSION['flash'][$key]`

Pages using `setFlashMessage()` (e.g., `course.php`, `checkout.php`, `edit-profile.php`, `enroll.php`, `lenco-checkout.php`) store messages in a different location than pages using `flash()` (e.g., `learn.php`, `dashboard.php`, `login.php`, `assignment.php`, `take-quiz.php`, `download-certificate.php`).

If the display template only reads from one system, messages set by the other system are silently lost.

**Fix:** Consolidate to a single flash message API. Make `setFlashMessage()` a wrapper around `flash()` or vice versa. Update the display template (`alerts.php`) to check both storage locations, then gradually migrate all callers to one function.

---

## HIGH SEVERITY ISSUES

### HIGH-0: Admin sidebar has 10 broken links

`src/templates/admin-sidebar.php` uses old-style directory URLs (`admin/users/index.php`) but the actual admin panel uses query parameter routing (`admin/index.php?page=users`). All sidebar links are broken.

**Broken links -> Correct URLs:**
| Sidebar Link (BROKEN) | Correct URL |
|----------------------|-------------|
| `admin/users/index.php` | `admin/index.php?page=users` |
| `admin/courses/index.php` | `admin/index.php?page=courses` |
| `admin/enrollments/index.php` | `admin/index.php?page=enrollments` |
| `admin/payments/index.php` | `admin/index.php?page=financials` |
| `admin/announcements/index.php` | `admin/index.php?page=announcements` |
| `admin/settings/index.php` | `admin/index.php?page=settings` |
| `admin/categories/index.php` | **NOT IMPLEMENTED** (no handler exists) |
| `admin/certificates/index.php` | **NOT IMPLEMENTED** (no handler exists) |
| `admin/reviews/index.php` | **NOT IMPLEMENTED** (no handler exists) |
| `admin/reports/index.php` | **NOT IMPLEMENTED** (no handler exists) |

**Fix:** Update all sidebar hrefs to use `?page=` format. For categories, certificates, reviews, and reports: either create the page handlers in `public/admin/pages/` and add them to the valid pages list in `admin/index.php:23`, or hide these menu items until implemented.

### HIGH-0b: 5 empty JavaScript files (3 more than initially noted)

Not just `auth.js` and `main.js` - there are **5 total empty JS files**:

| File | Loaded By | Impact |
|------|-----------|--------|
| `public/assets/js/main.js` | `footer.php:219` (every page) | Unnecessary HTTP request on every page |
| `public/assets/js/auth.js` | Not loaded (dead file) | None currently |
| `public/assets/js/admin.js` | Not loaded (dead file) | Admin JS features missing |
| `public/assets/js/quiz.js` | Not loaded (dead file) | Quiz timer/validation features missing |
| `public/assets/js/video-player.js` | Not loaded (dead file) | Video player features missing |

### HIGH-1: Empty CSS files (dead includes)

These files are loaded but contain no code:

- `public/assets/js/auth.js` - **EMPTY** (1 line, no content)
- `public/assets/js/main.js` - **EMPTY** (1 line, no content)

Any functionality that was supposed to be in these files (client-side auth validation, global JS utilities) is missing.

**Fix:** Either implement the intended functionality or remove the `<script>` includes from templates to avoid unnecessary HTTP requests.

---

### HIGH-2: Empty CSS files

- `public/assets/css/main.css` - **0 bytes** (loaded in `header.php:54`)
- `public/assets/css/responsive.css` - **0 bytes** (created but may be referenced)

**Fix:** Either populate with needed styles or remove the `<link>` tags from templates.

---

### HIGH-3: Inconsistent primary color palette across templates

Each header template defines a DIFFERENT primary color in its Tailwind config:

| Template | Primary-500 | Secondary-500 |
|----------|------------|---------------|
| `src/templates/header.php:69` | `#2E70DA` | `#F6B745` |
| `src/templates/admin-header.php:24` | `#2E70DA` (partial palette) | - |
| `src/templates/instructor-header.php:43` | `#3B82F6` (different!) | `#F59E0B` (different!) |

**Impact:** Instructor pages will have noticeably different blue and amber colors vs the main site and admin panel.

**Fix:** Create a single shared Tailwind config object (either as a PHP include that outputs JS, or a shared JS file) so all three headers use identical color values.

---

### HIGH-4: Unpinned CDN versions - production instability risk

- **Tailwind CSS:** `https://cdn.tailwindcss.com` - No version pinned. Any Tailwind update could break the UI in production.
- **Alpine.js:** `@3.x.x` - Auto-updates to latest 3.x minor/patch.

**Fix:** Pin to specific versions:
- Tailwind: Use a build step or pin version in URL
- Alpine.js: Pin to exact version (e.g., `alpinejs@3.13.5`)

---

### HIGH-5: `markAllRead()` function in dashboard uses wrong API contract

In `public/dashboard.php:569-573`, the `markAllRead()` function sends:
```javascript
body: JSON.stringify({ action: 'mark_all_read' })
```

But the Notifications API (`public/api/notifications.php`) handles `mark_all_read` on `PUT/PATCH` methods, NOT `POST`. The dashboard sends a `POST` request, so this action likely hits the wrong handler.

**Fix:** Either change the fetch method to `PUT` or add `mark_all_read` handling to the POST handler in the notifications API.

---

### HIGH-6: `quick-actions.php:98` - TODO: Email notifications not sent for announcements

```php
function sendAnnouncement($courseId, $message, $instructorId, $db) {
    $db->query(...); // Creates DB record
    // TODO: Send email notifications to enrolled students  <-- NOT IMPLEMENTED
}
```

Instructors think they're notifying students, but only a DB record is created. Students never receive emails.

**Fix:** Call `EmailNotificationService` to send emails to enrolled students after creating the announcement record.

---

### HIGH-7: `admin/handlers/users_handler.php:166` - TODO: Password reset email not sent

When an admin resets a user's password, the new password is set but the email notification is not sent:
```php
// TODO: Send email to user with new password
```

**Fix:** Implement email notification using `Email` class to send the new credentials to the user.

---

### HIGH-8: Student help page has placeholder phone number

`public/student/help.php:270`:
```html
<a href="tel:+260XXXXXXXX">
```

This is a non-functional placeholder that users might try to call.

**Fix:** Replace with actual support phone number from system settings or hide the phone link if not configured.

---

## MEDIUM SEVERITY ISSUES

### MED-1: N+1 query problem in `my-courses.php`

`public/my-courses.php:64-75` - After fetching all enrollments, there's a loop that runs a separate query per enrollment to get module completion data:

```php
foreach ($enrollments as &$enrollment) {
    $enrollment['modules'] = $db->fetchAll("...", [$enrollment['id'], $enrollment['course_id']]);
}
```

With 20 enrollments, this fires 20 additional queries.

**Fix:** Fetch all module completion data in a single query using `WHERE course_id IN (...)` and group in PHP.

---

### MED-2: N+1 query in `learn.php` for lesson progress

`public/learn.php:80-87` - Lessons are fetched per-module in a loop:
```php
foreach ($modules as $module) {
    $lessonsGrouped[$module['id']] = $db->fetchAll("...", [$module['id']]);
}
```

**Fix:** Fetch all lessons for the course in a single query, then group by `module_id` in PHP.

---

### MED-3: Course detail page only accepts `?id=` but some links use `?slug=`

`public/course.php:14` only reads `$_GET['id']`:
```php
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
```

But `public/instructor/index.php:316` generates links with `?slug=`:
```php
url('course.php?slug=' . $course['slug'])
```

This will result in a redirect to `courses.php` with "Invalid course link" because `$courseId` will be 0.

**Fix:** Add slug support to `course.php`:
```php
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$courseId && isset($_GET['slug'])) {
    $course = Course::findBySlug($_GET['slug']);
    // ...
}
```

---

### MED-4: `take-quiz.php` uses `?id=` but `learn.php:294` links to `?quiz_id=`

`public/learn.php:294`:
```php
<a href="<?= url('take-quiz.php?quiz_id=' . $quiz['id']) ?>">
```

But `public/student/take-quiz.php:20` reads:
```php
$quizId = $_GET['id'] ?? null;
```

Also, there are TWO `take-quiz.php` files:
- `public/take-quiz.php` (root level)
- `public/student/take-quiz.php` (student directory)

The learn page links to root-level `take-quiz.php` with `?quiz_id=` param, but that file likely expects `?id=`. Meanwhile the student version at `student/take-quiz.php` reads `$_GET['id']`.

**Fix:** Standardize the parameter name and ensure the correct file is being linked.

---

### MED-5: `assignment.php` vs `student/submit-assignment.php` routing confusion

There are assignment pages at two different paths:
- `public/assignment.php` - Assignment detail view
- `public/student/submit-assignment.php` - Submission form

But `learn.php:326` links to:
```php
url('assignment.php?id=' . $assignment['id'])
```

Students need to navigate from view to submit, but the flow between these two pages should be verified.

---

### MED-6: Google Fonts only loaded in instructor template

`src/templates/instructor-header.php:86` loads Google Fonts (Inter):
```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

But `header.php` and `admin-header.php` do NOT load this font, yet they reference `Inter` in their CSS. This means the font might render as a fallback (system sans-serif) on most pages but load correctly on instructor pages, creating visual inconsistency.

**Fix:** Load Google Fonts consistently across all templates, or remove the reference and rely on the system font stack.

---

### MED-7: Admin dashboard queries `system_settings` with `setting_id = 1` but table uses key-value pairs

`public/admin/index.php:29`:
```php
$settings = $db->fetchOne("SELECT * FROM system_settings WHERE setting_id = 1");
```

But `admin/pages/settings.php:47-50` treats the table as key-value pairs (`setting_key`, `setting_value`). The `setting_id = 1` approach only works if there's a single row with that ID, which conflicts with the key-value pattern used elsewhere.

**Fix:** Use key-value lookup pattern consistently:
```php
$settingsRows = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings");
$settings = array_column($settingsRows, 'setting_value', 'setting_key');
```

---

### MED-8: `enroll.php:75` - Redirect uses `$course->getSlug()` but `course.php` only accepts `?id=`

```php
redirect('course.php?slug=' . $course->getSlug());
```

As noted in MED-3, `course.php` doesn't support `?slug=` parameter.

---

### MED-9: `bulkGrade` and `bulkUpdateProgress` in quick-actions.php lack authorization

`public/instructor/quick-actions.php:101-120` - The bulk grade and bulk update functions don't verify that the instructor owns the courses/submissions being modified. An instructor could potentially grade another instructor's assignments by manipulating form data.

**Fix:** Add ownership verification - join against `courses.instructor_id` before updating.

---

### MED-10: Charts use CDN script loaded inline

`public/dashboard.php:532` loads Chart.js via CDN inline:
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

This should be in the header/footer template or version-pinned.

---

## LOW SEVERITY / UI/UX ISSUES

### LOW-1: No lesson completion tracking in learn.php sidebar

The lesson sidebar in `learn.php` shows lesson titles but has no visual indicator (checkmark, color change) for completed lessons. The `lesson_progress` data exists in the DB but isn't queried or rendered in the sidebar.

**Fix:** Query `lesson_progress` for the enrollment and add a completion indicator (checkmark icon, green text, or strikethrough) next to completed lessons.

---

### LOW-2: Inconsistent date/time formatting

Different pages format dates differently:
- `timeAgo()` - relative time (used in dashboards)
- `formatDate()` - absolute date (used in profile)
- PHP `date()` - raw format (used in instructor dashboard)
- JavaScript `DateTime` - varies by page

**Fix:** Create a standard date formatting utility and use it consistently.

---

### LOW-3: No loading skeleton/states for AJAX-heavy admin pages

Admin pages that fetch data via AJAX (user edit modal, course edit modal) show no loading indicator while data is being fetched. The modal opens empty and then populates.

**Fix:** Add loading spinners or skeleton screens inside modals during AJAX fetches.

---

### LOW-4: Student dashboard "Continue Learning" - missing empty progress handling

`public/dashboard.php:320`:
```php
style="width: <?= round($course['progress_percentage']) ? round($course['progress_percentage']) : 0 ?>%"
```

The ternary is redundant (`round(0)` is falsy, returns `0` which then goes to the else branch returning `0`). More importantly, `null` progress would also work but the logic is unnecessarily complex.

**Fix:** Simplify to `<?= round($course['progress_percentage'] ?? 0) ?>%`

---

### LOW-5: Profile page missing null coalescing for stats

`public/profile.php:22-27` - Stats array accesses don't use `??` operator:
```php
'active_courses' => $studentStats['in_progress_courses'],
```

If `Statistics::getStudentStats()` returns an array missing a key, this will throw a notice/warning.

**Fix:** Add `?? 0` to each stat access, as done in `dashboard.php`.

---

### LOW-6: `about.php` and `contact.php` - placeholder content

`public/student/help.php:270` has `+260XXXXXXXX` placeholder. Similar placeholders may exist in `about.php` contact details. The payment email template `src/mail/payment-received.php:134` also has `+260 XXX XXX XXX`.

**Fix:** Pull contact info from `system_settings` table dynamically.

---

### LOW-7: No 404 error page

There's no custom 404 page. Invalid URLs return Apache's default 404 or the PHP default error.

**Fix:** Create `public/404.php` with branded error page and configure `.htaccess` `ErrorDocument 404`.

---

### LOW-8: No breadcrumb navigation

Inner pages (course detail, lesson view, assignment view, quiz) have no breadcrumb trail to help users understand where they are in the hierarchy.

**Fix:** Add breadcrumbs to key inner pages: `Course > Module > Lesson`, `Courses > Course Name > Quiz`.

---

### LOW-9: Admin sidebar links may point to non-existent pages

The admin sidebar (`src/templates/admin-sidebar.php`) contains links for Categories, Reviews, Certificates, and Reports pages. These may be routed through the admin index.php page system, but not all corresponding page files exist in `/public/admin/pages/`.

**Fix:** Verify each sidebar link resolves to a working page. Create stub pages or hide unimplemented menu items.

---

### LOW-10: Dark mode not implemented

`whatsapp-button.css` has `@media (prefers-color-scheme: dark)` support, but no other part of the system supports dark mode. Users with system dark mode enabled will see a dark WhatsApp button on an otherwise light page.

---

## SECURITY NOTES (from existing SYSTEM_REVIEW.md + new findings)

These were previously documented in `SYSTEM_REVIEW.md` but are worth highlighting:

1. **`.env` committed to repo** - Contains production DB credentials, API keys, SMTP passwords
2. **Missing authorization on quiz/assignment submissions** - Any authenticated user can submit for any student
3. **Payment webhook idempotency** - Duplicate webhooks could double-credit payments
4. **SQL injection in validation helpers** - `validateUnique()` and `validateExists()` may have injection vectors
5. **Session not regenerated on role switch** - Session fixation risk
6. **Path traversal in file operations** - File upload/download paths not fully sanitized

---

## ARCHITECTURE IMPROVEMENTS (for future consideration)

1. **No autoloading for many classes** - Multiple `require_once` statements at the top of each file. Consider using Composer's PSR-4 autoloader consistently.
2. **No templating engine** - Raw PHP mixing logic and HTML. Consider extracting to a thin template layer.
3. **No CSRF on all AJAX endpoints** - Some API endpoints don't verify CSRF tokens for authenticated sessions.
4. **Mixed `htmlspecialchars()` and `sanitize()`** - Two different escaping functions used inconsistently.
5. **No database migrations runner** - Migrations are raw SQL files run manually.

---

## PRIORITY ACTION PLAN

### Phase 1: Critical Bugs (Must fix - system is broken without these)
1. **Load `learning.js` in `learn.php`** and export `courseId`/`lessonId` to JS (BUG-0, BUG-0b) - 1 file
2. **Fix `/api/progress.php` endpoint** - either create it or update JS to use `/api/lesson-progress.php` (BUG-0c) - 2-3 files
3. **Fix `toggleModuleSection()` event parameter** (BUG-0d) - 1 file
4. **Fix `u.name` references** - change to `CONCAT(u.first_name, ' ', u.last_name)` (BUG-1) - 4 files
5. **Fix wrong instructor JOIN** - change `LEFT JOIN users u ON c.instructor_id = u.id` to go through `instructors` table (BUG-2) - 6 files
6. **Consolidate flash message systems** into one API (BUG-3) - ~30 files
7. **Fix admin sidebar broken links** - update all hrefs to `?page=` format (HIGH-0) - 1 file

### Phase 2: High Priority (Fix before production)
8. **Fix `markAllRead()` HTTP method** - change from POST to PUT (HIGH-5) - 1 file
9. **Fix `?slug=` vs `?id=` parameter mismatches** (MED-3, MED-4, MED-8) - 3-4 files
10. **Implement missing email notifications** in quick-actions and user handler (HIGH-6, HIGH-7) - 2 files
11. **Clean up empty JS/CSS files** - remove dead `<script>` and `<link>` includes (HIGH-0b, HIGH-1) - 3+ files
12. **Unify color palette** across all three header templates (HIGH-3) - 3 files
13. **Pin CDN versions** for Tailwind and Alpine.js (HIGH-4) - 3 files
14. **Fix placeholder contact info** (HIGH-8, LOW-6) - 3+ files
15. **Create missing admin pages** (categories, certificates, reviews, reports) or hide menu items (HIGH-0) - 4 new page files

### Phase 3: Medium Priority (Improve quality)
16. Fix N+1 queries in my-courses.php and learn.php (MED-1, MED-2) - 2 files
17. Add authorization to bulk grade/update operations (MED-9) - 1 file
18. Standardize admin settings query pattern (MED-7) - 1 file
19. Add lesson completion indicators to learn.php sidebar (LOW-1)
20. Populate `quiz.js` with client-side quiz logic (timer, validation) or use inline JS
21. Populate `video-player.js` with reusable video player code
22. Add 404 error page (LOW-7)
23. Add breadcrumb navigation to inner pages (LOW-8)

### Phase 4: Polish
24. Loading states/skeletons for admin AJAX modals (LOW-3)
25. Consistent date formatting utility (LOW-2)
26. Profile stats null safety (LOW-5)
27. Load Google Fonts consistently across all templates (MED-6)
28. Implement dark mode consistently or remove partial support
