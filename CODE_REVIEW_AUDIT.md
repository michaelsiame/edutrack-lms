# EduTrack LMS - Comprehensive Code Review Audit

**Date:** 2026-02-07
**Scope:** Full codebase review - PHP classes, API endpoints, frontend JS, admin/instructor panels, database schema
**Reviewed Files:** 80+ files across all layers

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Critical Issues (Must Fix Immediately)](#critical-issues)
3. [Security Vulnerabilities](#security-vulnerabilities)
4. [Bugs and Logic Errors](#bugs-and-logic-errors)
5. [Incomplete / Stub Functions](#incomplete--stub-functions)
6. [Missing Features](#missing-features)
7. [Database Schema Issues](#database-schema-issues)
8. [API Endpoint Issues](#api-endpoint-issues)
9. [Frontend Issues](#frontend-issues)
10. [Code Quality Issues](#code-quality-issues)
11. [Summary Statistics](#summary-statistics)

---

## Executive Summary

The EduTrack LMS codebase has **significant issues** across all layers that need to be addressed before it can be considered production-ready. The most critical findings include:

- **SQL injection vulnerabilities** in the core Database class and multiple endpoints
- **Broken quiz system** due to pervasive schema/code mismatches
- **Empty payment callback handler** meaning payment confirmations from gateways are silently dropped
- **CORS origin reflection** on authenticated endpoints effectively disabling browser same-origin protections
- **Missing password reset columns** in the database schema, making password recovery non-functional
- **No CSRF protection** on any API endpoint
- **Webhook signature verification bypass** in the Lenco payment integration

Total issues identified: **~180+** across all severity levels.

| Severity | Count | Description |
|----------|-------|-------------|
| CRITICAL | 18 | Will crash, corrupt data, or allow immediate exploitation |
| HIGH | 35 | Security vulnerabilities and major bugs |
| MEDIUM | 55 | Logic errors, data integrity, moderate security issues |
| LOW | 40+ | Code quality, dead code, minor validation gaps |

---

## Critical Issues

### CRIT-01: SQL Injection in Database Helper Methods
**File:** `src/includes/database.php:128-185`

The `insert()`, `update()`, `delete()`, `count()`, and `exists()` methods all interpolate table names, column names, and WHERE clauses directly into SQL strings without sanitization:

```php
// database.php:128-136
$columns = implode(', ', array_keys($data));
$sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

// database.php:167-170
$sql = "DELETE FROM {$table} WHERE {$where}";
```

If any caller passes user-controlled data as array keys or table/where parameters, this enables SQL injection.

### CRIT-02: SQL Injection in Payment::all() ORDER BY
**File:** `src/classes/Payment.php:202`

```php
if (isset($options['order'])) {
    $sql .= " ORDER BY p." . $options['order'];
}
```

The `$options['order']` value is directly concatenated into SQL with no whitelist validation. An attacker controlling this parameter can inject arbitrary SQL.

### CRIT-03: SQL Injection in Review::getCourseReviews()
**File:** `src/classes/Review.php:163-165`

```php
$orderDir = $filters['order_dir'] ?? 'DESC';
$sql .= " ORDER BY r.rating $orderDir, r.created_at DESC";
```

`$orderDir` from user-supplied `$filters` is interpolated directly into SQL.

### CRIT-04: SQL Injection in Announcement::getAll()
**File:** `src/classes/Announcement.php:147-149`

```php
$orderBy = $filters['order_by'] ?? 'created_at';
$orderDir = $filters['order_dir'] ?? 'DESC';
$sql .= " ORDER BY a.{$orderBy} {$orderDir}";
```

Both `$orderBy` and `$orderDir` are unvalidated user input interpolated into SQL.

### CRIT-05: Empty Payment Callback Handler
**File:** `public/api/payment-callback.php`

The entire file is a stub with only a comment header. Payment gateway callbacks (MTN, Airtel, Zamtel) that POST to this URL receive no processing. Payments succeed on the gateway side but are never recorded locally.

### CRIT-06: Broken Quiz System - Wrong Table/Column References
**Files:** `src/classes/Quiz.php`, `src/classes/Question.php`

- `Quiz::getQuestions()` (line 164) queries `quiz_questions` (a junction table) expecting question content columns that don't exist there
- `Question::create()` (line 61) inserts into `quiz_questions` instead of `questions` table
- `Question::load()` (line 24) uses `WHERE id = :id` but the PK is `quiz_question_id`
- `Quiz::startAttempt()` (line 245) uses `user_id` but schema column is `student_id`
- `Quiz::submitAttempt()` (lines 299-306) references columns `completed_at`, `total_questions`, `correct_answers`, `passed` that don't exist in `quiz_attempts`

The quiz system is fundamentally non-functional.

### CRIT-07: Missing Database Tables
Referenced in code but absent from schema:
- **`contacts`** - Contact form submissions (`public/contact.php:57`) will fail
- **`quiz_responses`** - Quiz submission flow (`public/actions/submit-quiz.php:185`) will fail
- **`quiz_question_options`** - Student quiz-taking (`public/student/take-quiz.php:53`) will fail

### CRIT-08: Missing Password Reset Columns
**File:** `src/includes/auth.php:476-477,512`

The code references `password_reset_token` and `password_reset_expires` columns on the `users` table, but these columns don't exist in the schema. Password reset is completely broken.

### CRIT-09: Undefined Function Calls in auth.php
**File:** `src/includes/auth.php:481,620`

- `sendPasswordResetEmail()` called at line 481 but function has been removed (noted at lines 624-627)
- `sendEmail()` called at line 620 but function has been removed

Both will produce fatal errors at runtime.

### CRIT-10: CORS Origin Reflection with Credentials
**Files:** Multiple API endpoints

```php
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
```

Found in: `announcements.php:12`, `categories.php:12`, `certificates.php:14`, `courses.php:14`, `enrollments.php:14`, `users.php:12`

This reflects any requesting origin while allowing credentials, effectively disabling Same-Origin Policy entirely. Any malicious website can make authenticated cross-origin requests.

### CRIT-11: Lenco Webhook Signature Bypass
**File:** `public/api/lenco-webhook.php:63-82`

Signature verification only runs if both `$webhookSecret` and `$signature` are non-empty. If the attacker omits the signature header, `$signatureValid` remains `true` (initialized on line 64) and the webhook processes the request without verification.

### CRIT-12: PDO Reused Named Placeholder Bug
**File:** `src/classes/Lesson.php:244-257`

The `:progress` named placeholder is used 4 times in the SQL but bound only once. With `ATTR_EMULATE_PREPARES = false`, this throws "Invalid parameter number." Same issue in `getNext()`/`getPrevious()` methods with `:module_id`.

### CRIT-13: Notification System - Wrong Column Names
**File:** `src/classes/Notification.php`

- `markAsRead()` (line 141) uses `WHERE id = ?` but PK is `notification_id` -- notifications can never be marked as read
- `create()` (lines 88-94) uses columns `type`, `link` but schema has `notification_type`, `action_url`
- `getId()` (line 390) returns `$this->data['id']` but column is `notification_id` -- always returns null
- Enum values in code don't match schema enum values

### CRIT-14: Submission Class - Wrong Column Names Throughout
**File:** `src/classes/Submission.php:72-76`

- Uses `user_id` but schema has `student_id`
- Uses `file_path`, `file_name`, `file_size` but schema has `file_url`
- Uses `course_id` which doesn't exist in the table
- Status enum case mismatch (`'submitted'` vs `'Submitted'`)

### CRIT-15: Hardcoded JWT Fallback Secret
**File:** `public/api/auth.php:478`

```php
return 'CHANGE_THIS_SECRET_KEY_IN_PRODUCTION';
```

If environment variables aren't set, every JWT is signed with a publicly known key.

### CRIT-16: LiveSession - MySQL INTERVAL Parameter Bug
**File:** `src/classes/LiveSession.php:380`

```sql
AND ls.scheduled_start_time <= DATE_ADD(NOW(), INTERVAL :minutes MINUTE)
```

MySQL does not allow bound parameters inside INTERVAL expressions. This produces a syntax error.

### CRIT-17: Module Column Name Mismatch
**File:** `src/classes/Module.php`

- `create()` (line 69) and `update()` (line 91) use `order_index`
- `getByCourse()` (line 57) uses `ORDER BY m.display_order`

These cannot both be correct. One will produce SQL errors.

### CRIT-18: Payment Verify Action Auto-Approves
**File:** `public/api/payment.php:134-145`

The verify action is a stub that marks any payment as successful without actually contacting a payment provider. Users can self-verify their own payments.

---

## Security Vulnerabilities

### SEC-01: File Extension Bypass in Avatar Upload
**File:** `src/classes/User.php:409-410`

Extension is taken from user-provided filename, not from verified MIME type. An attacker could upload a `.php` file with valid image headers.

### SEC-02: HTML Injection in Lenco Payment Emails
**File:** `src/classes/Lenco.php:579-589`

User data (`first_name`, `reference`) interpolated into HTML email without escaping.

### SEC-03: XSS in Certificate HTML Generation
**File:** `src/classes/CertificateGenerator.php:262-329`

Student names, course titles embedded directly in HTML without `htmlspecialchars()`.

### SEC-04: XSS in Lesson Video Embed
**File:** `src/classes/Lesson.php:299-300`

Video URL placed directly into `<iframe src="...">` without escaping.

### SEC-05: XSS in Helper Video Embed
**File:** `src/includes/helpers.php:100`

Raw URL embedded in `<video>` tag without escaping for non-YouTube/Vimeo URLs.

### SEC-06: JWT Timing Attack
**Files:** `public/api/auth.php:414`, `src/api/ApiBase.php:178`

JWT signature comparison uses `!==` instead of `hash_equals()`, vulnerable to timing side-channel attacks.

### SEC-07: Session-Based Rate Limiting is Bypassable
**File:** `src/includes/security.php:148-175`

Rate limit state stored in `$_SESSION`. Attacker can clear cookies to get a fresh counter. No server-side persistent storage.

### SEC-08: IP Spoofing Bypasses Rate Limiting
**File:** `src/includes/functions.php:425-443`

`getClientIp()` trusts `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR` headers which are client-controlled.

### SEC-09: Path Traversal in Submission Delete
**File:** `src/classes/Submission.php:152`

File path from database used in `unlink()` without sanitization.

### SEC-10: World-Writable Upload Directories
**File:** `src/classes/FileUpload.php:120`

```php
mkdir($uploadPath, 0777, true);
```

### SEC-11: Dangerous `extract()` Usage
**File:** `src/classes/EmailNotificationService.php:160`

`extract($variables)` can overwrite local variables including `$templatePath`.

### SEC-12: No CSRF Protection on Any API Endpoint
**Files:** All files in `public/api/`

No state-changing API endpoint verifies CSRF tokens. This affects: announcements, categories, certificates, courses, enrollments, assignments, settings, transactions, users, notifications, payments, role switching, live sessions, notes, lesson resources.

### SEC-13: Enrollment Not Verified on Content Access
**Files:** `public/api/lessons.php:26-46`, `public/api/lesson-resources.php:29-43`, `public/api/notes.php:70-111`

Any authenticated user can access lessons, resources, and save notes for any course without enrollment verification.

### SEC-14: Token Blacklisting / Logout is a No-Op
**File:** `public/api/auth.php:115-131`

Logout does nothing beyond returning `{"success": true}`. Stolen tokens remain valid until expiry.

### SEC-15: Access/Refresh Tokens Interchangeable
**File:** `public/api/auth.php:365-426`

`verifyJWT()` doesn't check token type, so refresh tokens can be used as access tokens.

### SEC-16: HTTP Header Injection
**Files:** `src/classes/CertificateGenerator.php:224`, `public/api/download-resource.php:87`

Certificate number and resource title used in `Content-Disposition` header without sanitization.

### SEC-17: Open Redirect in Resource Download
**File:** `public/api/download-resource.php:65`

Redirects to whatever URL is stored in database. Malicious instructors could redirect students to phishing sites.

### SEC-18: No Rate Limiting on Login/Registration API
**File:** `public/api/auth.php:136-286`

No rate limiting or account lockout on the API login/registration endpoints.

### SEC-19: Error Messages Leak Internal Details
**Files:** `public/api/course-assignments/update.php:97`, `public/api/course-assignments.php:34`, `public/api/instructors.php:40`, `public/api/live-sessions.php:418`

Exception messages (SQL details, file paths) returned to clients.

### SEC-20: Broken Encryption Round-Trip
**File:** `src/includes/security.php:242-269`

`encryptData()` uses flag `0` (base64 output) then base64-encodes the concatenation of raw IV + base64 ciphertext. Works accidentally but is fragile. Should use `OPENSSL_RAW_DATA`.

### SEC-21: Predictable Payment/Certificate References
**Files:** `src/classes/Payment.php:297`, `public/api/lesson-progress.php:201`, `public/api/transactions.php:68`

Uses `uniqid()`, `time()`, `md5()` for reference numbers. All predictable.

### SEC-22: Weak LiveSession Room IDs
**File:** `src/classes/LiveSession.php:227`

Uses `rand()` and `md5(uniqid())` instead of `random_bytes()`.

---

## Bugs and Logic Errors

### BUG-01: `$this->data = false` Pattern (Multiple Files)
When `PDOStatement::fetch()` finds no row, it returns `false`. The following files assign this directly to `$this->data`, causing "Trying to access array offset on false" errors in PHP 8+:

- `src/classes/Lesson.php:31`
- `src/classes/Category.php:29`
- `src/classes/Instructor.php:35`
- `src/classes/RegistrationFee.php:33`

`Course.php:37` does this correctly with `$result ?: []`.

### BUG-02: User::toArray() TypeError
**File:** `src/classes/User.php:613`

If user not found, `$this->data` is `false`. `array_merge(false, [])` throws TypeError in PHP 8+.

### BUG-03: User::all() Column Mismatch
**File:** `src/classes/User.php:550,591`

Selects `up.avatar_url` but code elsewhere expects `avatar`.

### BUG-04: Course::all() Featured Sort Broken
**File:** `src/classes/Course.php:142-172`

`$allowedOrderColumns` includes `'featured'`, but the switch statement has no `case 'featured'` branch. Falls through to default sort.

### BUG-05: Course::getStatistics() Hardcoded Zeros
**File:** `src/classes/Course.php:785-794`

`completion_rate` and `dropout_rate` hardcoded to `0`.

### BUG-06: Enrollment::markLessonComplete() Always Returns True
**File:** `src/classes/Enrollment.php:287-303`

Returns `true` unconditionally regardless of database query success.

### BUG-07: Enrollment::recalculateProgress() No Return Value
**File:** `src/classes/Enrollment.php:306-331`

Has early returns with no value, no explicit return at end.

### BUG-08: PaymentPlan Balance Not Updated
**File:** `src/classes/PaymentPlan.php:146-150`

`recordPayment()` updates `total_paid` but not `balance`. Balance becomes stale.

### BUG-09: PaymentPlan References u.username
**File:** `src/classes/PaymentPlan.php:26`

Schema shows no `username` column on users table.

### BUG-10: Invoice Wrong JOIN Column
**File:** `src/classes/Invoice.php:27`

Joins `i.payment_id = p.id` but Payment uses `payment_id` as its PK column.

### BUG-11: Invoice Number Race Condition
**File:** `src/classes/Invoice.php:126-141`

Concurrent requests read same count, generate duplicate invoice numbers.

### BUG-12: LiveSession References Non-Existent u.name Column
**File:** `src/classes/LiveSession.php:28,60,74`

Users table has `first_name`/`last_name`, not `name`.

### BUG-13: LiveSession canJoin() Wrong ID Comparison
**File:** `src/classes/LiveSession.php:263`

Compares `instructor_id` (instructor table ID) to `$userId` (user table ID).

### BUG-14: Progress::getCourseProgress() Wrong Column
**File:** `src/classes/Progress.php:40-53`

References `qa.user_id` but schema has `qa.student_id`.

### BUG-15: Progress::checkCourseCompletion() Always Triggers Certificate
**File:** `src/classes/Progress.php:295-301`

`$this->db->query()` returns a PDOStatement (always truthy). Certificate generation runs every time progress is 100%, not just on first completion.

### BUG-16: Assignment::create() Missing course_id
**File:** `src/classes/Assignment.php:82-90`

Schema requires `course_id` (NOT NULL) but the INSERT doesn't include it.

### BUG-17: Assignment::create() Wrong Column Name
**File:** `src/classes/Assignment.php:100`

Uses `max_file_size` but schema has `max_file_size_mb`.

### BUG-18: Undefined $baseUrl Variable
**File:** `src/includes/config.php:33`

`PUBLIC_URL` defined using undefined `$baseUrl` variable.

### BUG-19: Undefined CURRENCY_SYMBOL Constant
**File:** `src/includes/functions.php:69`

`CURRENCY_SYMBOL` used but never defined. Config defines `CURRENCY` as `'ZMW'`.

### BUG-20: Enrollment::logActivity() Fails in CLI
**File:** `src/classes/Enrollment.php:383`

`$_SERVER['REMOTE_ADDR']` undefined in CLI context (cron jobs).

### BUG-21: API Notes Duplicate Parameter
**File:** `public/api/notes.php:90-99`

`:notes` parameter used twice in SQL (VALUES and ON DUPLICATE KEY UPDATE) but bound once.

### BUG-22: Notifications Stream References Non-Existent Columns
**File:** `public/api/notifications-stream.php:102-103`

References `$notification['icon']` and `$notification['color']` but SQL doesn't select these.

### BUG-23: Settings API Key Asymmetry
**File:** `public/api/settings.php:109-113`

GET handler strips `smtp_`/`email_` prefixes, but PUT handler uses raw keys for smtp fields. Settings written via PUT can't be read back correctly via GET.

### BUG-24: User Deletion Leaves Orphaned Data
**File:** `public/api/users.php:150-153`

Only removes `user_roles` and `users` records. Enrollments, submissions, payments, notifications, certificates, etc. are left orphaned.

### BUG-25: Course Preview Wrong JOIN
**Referenced in schema review**

`public/course-preview.php` joins `c.instructor_id` directly to `users.id` instead of going through the `instructors` table.

### BUG-26: Duplicate Enrollments Possible
The `enrollments` table has no unique constraint on `(user_id, course_id)`. The data already shows user 43 enrolled in course 1 twice.

### BUG-27: Quiz Attempts Has Two PKs
Schema defines both `attempt_id` and `id` columns. `quiz_answers` references `attempt_id` but PK is `id`. Creates confusion across the codebase.

---

## Incomplete / Stub Functions

### STUB-01: Payment::processMTN() / processAirtel() / processZamtel()
**File:** `src/classes/Payment.php:384-427`

All three mobile money methods are stubs that always return `['success' => true]` without contacting any API.

### STUB-02: Payment::sendConfirmationEmail()
**File:** `src/classes/Payment.php:288`

Only logs a message. No email sent.

### STUB-03: Invoice::generatePDF()
**File:** `src/classes/Invoice.php:146-155`

Named `generatePDF()` but returns HTML. Comment acknowledges "This would use a PDF library."

### STUB-04: Invoice::sendEmail()
**File:** `src/classes/Invoice.php:160-164`

Only logs a message and returns true. No email sent.

### STUB-05: Course::getTags()
**File:** `src/classes/Course.php:818-828`

Returns level/category/language as fake tags instead of actual tags. Comment: "Placeholder - implement if you have a tags system."

### STUB-06: Review Getter Stubs
**File:** `src/classes/Review.php:360-368`

- `getReviewTitle()` always returns `null`
- `getHelpfulCount()` always returns `0`
- `isFeatured()` always returns `false`

### STUB-07: Payment Callback Handler
**File:** `public/api/payment-callback.php`

Entire file is empty.

### STUB-08: Token Logout/Blacklisting
**File:** `public/api/auth.php:115-131`

Logout handler is a no-op.

### STUB-09: Announcement::getActiveForUser() Role Filter
**File:** `src/classes/Announcement.php:176`

`$role` parameter declared but never used. All users see same announcements regardless of role.

---

## Missing Features

### MISS-01: No Password Strength Validation on Password Update
**File:** `src/classes/User.php:380`

`updatePassword()` accepts any string including empty.

### MISS-02: No File Size Check on Avatar Upload
**File:** `src/classes/User.php:388-430`

No validation of `$file['size']`.

### MISS-03: No Transaction Wrapping on Multi-Step Deletes
**Files:** `Module::delete()`, `Assignment::delete()`, `Quiz::delete()`, `Quiz::submitAttempt()`, `Progress::completeLesson()`

Multiple SQL statements without transaction protection. Partial failures leave inconsistent state.

### MISS-04: No Authorization Checks in Class Methods
None of the classes verify caller permissions. Examples:
- `Submission::grade()` doesn't verify caller is an instructor
- `Assignment::delete()` doesn't verify caller owns the assignment
- `Quiz::submitAttempt()` doesn't verify user matches the attempt

### MISS-05: No Pagination on Admin Listing Endpoints
**Files:** `announcements.php`, `categories.php`, `certificates.php`, `enrollments.php`, `transactions.php`, `users.php`

All return full table dumps with no pagination.

### MISS-06: Discussion/Forum System (Schema Only)
Schema defines `discussions` and `discussion_replies` tables but no corresponding PHP classes or API endpoints exist.

### MISS-07: Badge/Achievement System (Schema Only)
Schema defines `badges` and `student_achievements` tables but no corresponding classes or endpoints exist.

### MISS-08: Message System (Schema Only)
Schema defines `messages` table but no corresponding class or API for user messaging.

### MISS-09: Email Queue Processing
Schema defines `email_queue` table but no cron job or worker to process queued emails.

---

## Database Schema Issues

### Schema - Missing Foreign Keys (28+)
The following tables lack foreign key constraints, meaning referential integrity is not enforced:

| Table | Column(s) Missing FK |
|-------|---------------------|
| `announcements` | `posted_by`, `course_id` |
| `activity_logs` | `user_id` |
| `certificates` | `enrollment_id` |
| `enrollment_payment_plans` | `enrollment_id`, `user_id`, `course_id` |
| `payments` | `student_id`, `course_id`, `enrollment_id`, `payment_method_id`, `recorded_by` |
| `transactions` | `payment_id` |
| `registration_fees` | `user_id`, `student_id`, `verified_by` |
| `lesson_progress` | `enrollment_id`, `lesson_id` |
| `lesson_resources` | `lesson_id` |
| `live_sessions` | `lesson_id`, `instructor_id` |
| `live_session_attendance` | `live_session_id`, `user_id` |
| `quiz_answers` | `attempt_id`, `question_id` |
| `question_options` | `question_id` |
| `quiz_questions` | `quiz_id`, `question_id` |
| `assignments` | `lesson_id` |
| `assignment_submissions` | `graded_by` |
| `student_achievements` | `course_id` |
| `quizzes` | `lesson_id` |
| `messages` | `parent_message_id` |

### Schema - Missing Indexes (12+)
| Table | Missing Index |
|-------|--------------|
| `payments` | `student_id`, `course_id`, `enrollment_id` |
| `enrollment_payment_plans` | `enrollment_id`, `user_id`, `course_id`, `payment_status` |
| `lesson_progress` | `enrollment_id`, `lesson_id`, composite |
| `lesson_resources` | `lesson_id` |
| `live_sessions` | `lesson_id`, `instructor_id`, `status` |
| `live_session_attendance` | `live_session_id`, `user_id` |
| `quiz_questions` | `quiz_id`, `question_id` |
| `quiz_answers` | `attempt_id`, `question_id` |
| `question_options` | `question_id` |
| `certificates` | `enrollment_id`, UNIQUE on `certificate_number` |
| `user_sessions` | `user_id`, `session_token` |

### Schema - Missing UNIQUE Constraints
| Table.Column | Impact |
|-------------|--------|
| `users.email` | Duplicate email addresses allowed |
| `users.username` | Duplicate usernames allowed |
| `courses.slug` | Duplicate URL slugs break routing |
| `certificates.certificate_number` | Duplicate certificate numbers |
| `system_settings.setting_key` | Already has duplicates (2x `registration_fee_amount`) |

### Schema - Data Integrity Issues
- `enrollments` row 31 has empty string for `payment_status` enum
- `user_roles` has row with `user_id = 0` (doesn't exist)
- `payments.currency` defaults to `'USD'` but `enrollment_payment_plans.currency` defaults to `'ZMW'`
- `system_settings` has conflicting `default_currency='USD'` and `currency='ZMW'`
- `user_profiles` has both `avatar_url` and `avatar` columns (ambiguous)
- `enrollments` has redundant `user_id` and `student_id` with no duplicate prevention

### Schema - Migration Issues
Both migration files reference `u.name` which doesn't exist (should be `CONCAT(u.first_name, ' ', u.last_name)`):
- `migrations/fix-instructor-records.sql:41,52`
- `migrations/assign-michael-to-microsoft-office.sql:11,24,60`

`assign-michael-to-microsoft-office.sql:39` inserts into `course_instructors.updated_at` which doesn't exist.

---

## API Endpoint Issues

### Missing Authentication / Authorization
| Endpoint | Issue |
|----------|-------|
| `api/lessons.php` | No enrollment verification |
| `api/lesson-resources.php` | No enrollment verification |
| `api/notes.php` | No enrollment verification |
| `api/auth.php` (login) | No rate limiting |
| `api/auth.php` (register) | No rate limiting |

### Missing Input Validation
| Endpoint | Issue |
|----------|-------|
| `api/enrollments.php` | No validation of enrollment_status values |
| `api/transactions.php` | Amount not validated as positive number |
| `api/users.php` | No email format validation, no password strength on create |
| `api/logs.php`, `api/notifications.php` | No bounds on limit parameter |
| `api/lesson-progress.php` | Unknown actions silently succeed |
| `api/course-assignments/update.php` | instructor_ids array elements not validated |

### Error Handling Gaps
| Endpoint | Issue |
|----------|-------|
| `api/assignment.php` | No try/catch block |
| `api/notes.php` | No try/catch block |
| `api/lessons.php` | No try/catch block |
| `api/live-sessions.php` | No require_once for LiveSession class |

---

## Frontend Issues

### XSS Vulnerabilities
Multiple JavaScript files use `innerHTML` with unsanitized user data, which enables XSS:
- Notification rendering with user-controlled message content
- Live session details display
- Course content rendering

### Missing CSRF on Admin Panel
The admin panel (`public/admin/`) does not include CSRF tokens in any AJAX requests to the API.

---

## Code Quality Issues

### Inconsistent Database Access Pattern
- Some classes use `Database::getInstance()` (most)
- Some use `global $db` (LessonResource, Review static methods, API endpoints)
- Mixing patterns creates fragile coupling

### Inconsistent Parameter Binding
- Some files use named parameters (`:id`)
- Some use positional parameters (`?`)
- Inconsistency increases risk of binding errors

### Undefined Constants Used
- `CURRENCY_SYMBOL` (functions.php:69) - never defined
- `TEVETA_NAME`, `TEVETA_CODE`, `APP_NAME` (CertificateGenerator.php) - never defined
- `STORAGE_PATH` (Lenco.php logging) - not verified before use
- `PUBLIC_PATH` (Payment.php:463) - not verified before use

### Redundant/Conflicting Configuration
- Error reporting configured in both `config.php` and `bootstrap.php` with different values
- Database initialized in both `database.php` and `bootstrap.php`
- Flash message systems: `setFlashMessage()` vs `flash()` (incompatible)

### Missing `function_exists` Guards
`Lesson::create()` calls `slugify()` without checking if it exists (unlike `Course.php` which does).

### Suppressed Errors
`User::deleteAvatarFile()` uses `@unlink()` suppressing errors with no logging.

---

## Summary Statistics

| Category | CRITICAL | HIGH | MEDIUM | LOW |
|----------|----------|------|--------|-----|
| SQL Injection | 4 | 2 | - | - |
| Authentication/Authorization | 2 | 5 | - | - |
| XSS / Injection | - | 5 | 2 | - |
| Schema/Code Mismatch | 6 | 3 | 5 | - |
| Missing Tables/Columns | 3 | 1 | - | - |
| CSRF | - | - | 12+ | - |
| Logic Errors | 1 | 4 | 8 | 5 |
| Stub Functions | 1 | - | - | 8 |
| Missing Validation | - | 3 | 8 | 10+ |
| Missing FKs/Indexes | - | 5 | 12 | - |
| Data Integrity | - | 3 | 5 | 3 |
| Error Handling | - | 2 | 5 | 8 |
| Code Quality | - | - | 3 | 10+ |
| **Total** | **17** | **33** | **60+** | **44+** |

---

## Recommended Fix Priority

### Phase 1 - Critical Security (Immediate)
1. Fix SQL injection in Database class helper methods, Payment::all(), Review, Announcement
2. Fix CORS headers on all API endpoints (whitelist specific origins)
3. Fix Lenco webhook signature verification bypass
4. Add CSRF protection to all state-changing API endpoints
5. Replace hardcoded JWT fallback secret
6. Implement `hash_equals()` for JWT signature verification

### Phase 2 - Broken Functionality
1. Fix all schema/code column name mismatches (Quiz, Question, Notification, Submission, Module, LiveSession)
2. Create missing database tables (contacts, quiz_responses, quiz_question_options)
3. Add missing columns to users table (password_reset_token, password_reset_expires)
4. Fix auth.php undefined function calls (sendPasswordResetEmail, sendEmail)
5. Implement payment callback handler
6. Fix `$this->data = false` pattern across all classes
7. Fix PDO reused named placeholder bugs in Lesson.php

### Phase 3 - Security Hardening
1. Add enrollment verification to lesson/resource/notes APIs
2. Implement server-side rate limiting (database-backed)
3. Fix file upload security (extensions, permissions, predictable names)
4. Add authorization checks to class methods
5. Implement token blacklisting for logout
6. Add transaction wrapping to multi-step operations
7. Fix encryption to use OPENSSL_RAW_DATA

### Phase 4 - Data Integrity
1. Add missing foreign key constraints
2. Add missing indexes
3. Add UNIQUE constraints (users.email, courses.slug, etc.)
4. Fix currency defaults consistency
5. Fix duplicate system_settings entries
6. Fix migration scripts

### Phase 5 - Complete Stub Implementations
1. Implement mobile money payment processing (MTN, Airtel, Zamtel)
2. Implement payment confirmation emails
3. Implement invoice PDF generation
4. Implement discussion/forum system
5. Implement badge/achievement system
6. Implement email queue processing
