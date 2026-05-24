# Edutrack LMS — Comprehensive System Architecture Document

> **Purpose:** This document exists so that any AI agent (or human developer) can understand the full Edutrack LMS system, its dashboards, its data flows, and its current state — and therefore offer meaningful improvements.

> **Last Updated:** May 2026  
> **Branch:** `laravel-migration`  
> **Framework:** Laravel 10.x  
> **PHP:** 8.1+  
> **DB:** MariaDB 11.8.6

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Technology Stack](#2-technology-stack)
3. [Project Structure](#3-project-structure)
4. [Architecture Patterns](#4-architecture-patterns)
5. [Role System & Authorization](#5-role-system--authorization)
6. [Database Schema Overview](#6-database-schema-overview)
7. [Admin Dashboard](#7-admin-dashboard)
8. [Instructor Dashboard](#8-instructor-dashboard)
9. [Student Dashboard](#9-student-dashboard)
10. [Finance Dashboard](#10-finance-dashboard)
11. [Core Services](#11-core-services)
12. [Key Workflows](#12-key-workflows)
13. [Known Issues & Technical Debt](#13-known-issues--technical-debt)
14. [Recent Changes & Fixes](#14-recent-changes--fixes)
15. [Areas for Improvement](#15-areas-for-improvement)

---

## 1. Executive Summary

Edutrack LMS is a custom Laravel 10 web application built for **Edutrack Computer Training College**, a TEVETA-registered vocational training institution in Kalomo, Zambia. It manages online courses, student enrollments, payments (ZMW), certificates, live virtual classes (Jitsi Meet), assignments, quizzes with auto-grading, discussions, and announcements.

The system serves **four distinct user roles**: Admin, Instructor, Student, and Finance. Each has a dedicated dashboard with role-specific navigation rendered server-side into a shared Tailwind CSS + Alpine.js layout.

---

## 2. Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.1+, Laravel 10.x |
| **Database** | MariaDB 11.8.6 (MySQL-compatible) |
| **Frontend** | Tailwind CSS (pre-built, no build step), Alpine.js (CDN), Font Awesome 6, Chart.js |
| **Rich Text** | TinyMCE 7 Community (self-hosted, GPL) |
| **Auth** | Laravel Session + Google OAuth (Socialite) + custom email verification |
| **Payments** | Lenco (primary gateway), manual bank/mobile money |
| **Email** | PHPMailer + Gmail SMTP + queued `email_queue` table |
| **PDF** | TCPDF (certificates, invoices, lesson exports, transcripts) |
| **Video** | Jitsi Meet (live sessions), YouTube/Vimeo embeds (lessons) |
| **HTML Sanitization** | HTMLPurifier (ezyang/htmlpurifier v4.19) |
| **Queue** | Database-backed (`email_queue` table) or sync |
| **Storage** | Laravel `storage/app/public/` with symbolic link |

**No frontend build step.** Tailwind is pre-compiled to `public/assets/css/tailwind.css`. Alpine.js is loaded via CDN.

---

## 3. Project Structure

```
edutrack-lms/
├── app/
│   ├── Http/Controllers/      # Admin/, Instructor/, Student/, Finance/, Auth/, Payment/
│   ├── Http/Middleware/       # Admin, Instructor, Student, Finance, Enrolled
│   ├── Models/                # 67 Eloquent models
│   ├── Services/              # Certificate, EmailQueue, HtmlSanitizer, Invoice, LencoPayment, LessonExport, PaymentVerification
│   └── Providers/
├── config/                    # app, database, mail, payment (PHP arrays)
├── database/
│   ├── complete_lms_schema.sql   # Full schema dump (50+ tables)
│   └── migrations/            # Incremental SQL files
├── public/
│   ├── assets/                # CSS, JS, images, fonts
│   ├── uploads/               # User-generated content
│   └── index.php
├── resources/
│   ├── views/
│   │   ├── layouts/dashboard.blade.php   # Shared layout
│   │   ├── components/         # Reusable Blade components
│   │   ├── admin/              # Admin views
│   │   ├── instructor/         # Instructor views
│   │   ├── student/            # Student views
│   │   └── emails/             # Email templates
│   └── views/errors/
├── routes/web.php             # All web routes
└── docs/                      # Project documentation
```

---

## 4. Architecture Patterns

### Shared Dashboard Layout
All role dashboards use `resources/views/layouts/dashboard.blade.php`:
- **Alpine.js** sidebar (`sidebarOpen`, `darkMode` in `localStorage`)
- **260px fixed sidebar**, collapsible on mobile with overlay
- **Role-aware nav** rendered server-side
- **Top bar**: hamburger, page title yield, dark mode toggle, user dropdown
- **Toast notifications** via `<x-toast>` (success/error/warning/info)

### Reusable Blade Components
| Component | Purpose |
|-----------|---------|
| `<x-card>` | Container with variants (default/elevated/bordered/interactive) |
| `<x-button>` | 7 variants (primary, secondary, danger, warning, success, ghost, outline), 3 sizes |
| `<x-stat-card>` | Dashboard metric with icon, trend, link |
| `<x-status-badge>` | Color-coded pill for ~20 statuses |
| `<x-progress-bar>` | Accessible progress with label |
| `<x-data-table>` | Responsive table wrapper with empty state |
| `<x-empty-state>` | Centered illustration + CTA |
| `<x-activity-feed>` | Timeline with vertical connector |

### Content Sanitization
All rich text content (lessons, announcements, etc.) passes through `HtmlSanitizer::clean()` before storage. Uses HTMLPurifier with a safe whitelist.

### No Policies/Gates
Authorization is **purely middleware-based**. `AuthServiceProvider` has an empty `$policies` array. No Laravel Gates are defined. Access control happens via:
1. Route middleware (`auth`, `admin`, `instructor`, `student`, `finance`)
2. Controller-level ownership checks (`authorizeInstructor()`)
3. Inline role checks (`auth()->user()->isAdmin()`)

### Dual Settings System
- **`Setting` model** (modern): key-value with caching
- **`SystemSetting` model** (legacy): key-value with type casting
- Admin `updateSettings()` writes to **both** to keep public pages in sync

---

## 5. Role System & Authorization

### Role IDs
| ID | Role | Middleware |
|----|------|------------|
| 1 | Super Admin | `admin` |
| 2 | Admin | `admin` |
| 3 | Instructor | `instructor` |
| 4 | Student | `student` |
| 5 | Content Creator | *(no dedicated middleware)* |
| 6 | Finance | `finance` |

### Middleware
```php
AdminMiddleware:      auth()->user()->isAdmin()      // checks roles 1 OR 2
InstructorMiddleware: auth()->user()->isInstructor() // checks role 3
StudentMiddleware:    auth()->user()->isStudent()    // checks role 4
FinanceMiddleware:    auth()->user()->isFinance()    // checks role 6
EnrolledMiddleware:   auth()->check() + isEnrolledIn($courseId)
```

> **Discrepancy:** `AGENTS.md` says role ID 2 is "Instructor" but the actual code uses role ID 3 for instructors. `isAdmin()` returns true for roles 1 and 2, meaning two distinct admin-level roles exist.

### Ownership Checks
Every instructor controller has a protected `authorizeInstructor(Course $course)` method:
```php
$instructor = auth()->user()->instructor;
if (!$instructor || $course->instructor_id !== $instructor->id) {
    abort(403, 'You do not own this course.');
}
```

---

## 6. Database Schema Overview

### Key Tables (~50 tables)

#### Identity & Auth
- `users` — Core accounts (username, email, google_id, password_hash, status, email_verified)
- `user_profiles` — Extended info (DOB, address, emergency contact, social links)
- `user_roles` — Many-to-many pivot (user_id, role_id)
- `roles` — Role definitions with JSON `permissions`
- `students` / `instructors` — Role-specific profiles linked to `users`

#### Courses & Content
- `course_categories` — Course categorization
- `courses` — Full course metadata (title, slug, price, discount_price, status, instructor_id)
- `modules` — Course modules with `display_order`
- `lessons` — Individual lessons (type enum: `Video`, `Reading`, `Quiz`, `Assignment`, `Live Session`, `Download`)
- `lesson_resources` — File attachments per lesson
- `lesson_versions` — Auto-saved content history
- `lesson_notes` — Student notes per lesson
- `lesson_progress` — Binary completion tracking per enrollment

#### Enrollments & Payments
- `enrollments` — Student course enrollments (progress, payment_status, certificate_blocked, certificate_issued)
- `enrollment_payment_plans` — Payment plans per enrollment
- `payments` — Payment records (PK: `payment_id`, status enum: Pending/Completed/Failed/Refunded/Cancelled)
- `payment_methods` — Available methods
- `invoices` — Generated invoices with auto-numbering
- `registration_fees` — One-time ZMW 150 registration fee
- `transactions` — Generic transaction log
- `lenco_transactions` — Lenco-specific gateway records
- `lenco_webhook_logs` — Webhook audit trail

#### Assessments
- `quizzes` — Quiz definitions (time_limit, max_attempts, passing_score, randomize, publish dates)
- `questions` — Question bank (5 types: Multiple Choice, True/False, Short Answer, Essay, Fill in Blank)
- `quiz_questions` — Pivot with `display_order` and `points_override`
- `question_options` — Answer options for questions
- `quiz_question_options` — **Duplicate/separate option table** (potential schema issue)
- `quiz_attempts` — Student attempts (status: In Progress / Submitted / Graded)
- `quiz_answers` — Individual answers per attempt

#### Assignments
- `assignments` — Assignment definitions (max_points, due_date, late_penalty)
- `assignment_submissions` — Student submissions (text + file, graded status)

#### Certificates
- `certificates` — Issued certificates (unique certificate_number, verification_code, classification)

#### Communications
- `announcements` — System/course announcements (type & priority enums)
- `discussions` / `discussion_replies` — Course forums with nested replies
- `notifications` — In-app notifications per user
- `messages` — Internal messaging with threading
- `email_queue` / `email_templates` — Queued email system

#### Live Sessions
- `live_sessions` — Jitsi Meet session scheduling
- `live_session_attendance` — Join/leave tracking

#### Gamification & CMS
- `badges` / `student_achievements` — Gamification
- `hero_slides` — Homepage carousel
- `institution_photos` — Campus gallery
- `events` — Public events
- `testimonials` — Student testimonials
- `team_members` — Staff display
- `contacts` — Contact form submissions
- `newsletter_subscribers` — Email subscriptions

#### Settings
- `settings` — Modern key-value settings (cached)
- `system_settings` — Legacy key-value settings

### Schema Patterns
- **Currency:** ZMW, `decimal(10,2)`
- **Custom PKs:** Many tables use custom PKs (`payment_id`, `certificate_id`, `template_id`, `question_id`, etc.) instead of default `id`
- **No timestamps:** Many tables disable timestamps (`certificates`, `email_queue`, `quiz_attempts`, `quiz_answers`, `question_options`, `quiz_question_options`, etc.)
- **No soft deletes:** Not used anywhere
- **Cascade deletes:** Present on `enrollments` and `certificates`

---

## 7. Admin Dashboard

### Controllers (11 files in `app/Http/Controllers/Admin/`)

| Controller | Purpose |
|------------|---------|
| `DashboardController` | Stats, settings, CSV exports |
| `CourseController` | Full CRUD for courses |
| `UserController` | Full CRUD for users (bcrypt passwords) |
| `PaymentController` | Manual payment recording CRUD |
| `EnrollmentController` | Enrollment listing with inline edit (status, progress, grade, certificate block) |
| `AnnouncementController` | Announcement CRUD (course-scoped or general) |
| `EmailTemplateController` | Email template management |
| `EventController` | Institution events with cover image uploads |
| `InstitutionPhotoController` | Campus photo gallery |
| `BadgeController` | Gamification badge CRUD |
| `NewsletterController` | Subscriber management |

### Dashboard Metrics (`index()`)
| Metric | Calculation |
|--------|-------------|
| `total_users` | `User::count()` |
| `total_courses` | `Course::count()` |
| `total_enrollments` | `Enrollment::count()` |
| `total_revenue` | `Payment::where('payment_status', 'Completed')->sum('amount')` |
| `pending_payments` | `Payment::where('payment_status', 'Pending')->count()` |
| `recent_enrollments` | Last 10 with user & course |
| `recent_payments` | Last 10 with student & course |

### Settings Managed
- General: `app_name`, `app_email`, `app_phone`, `app_address`
- Payment: `currency`, `min_deposit_percent`, `registration_fee`
- Certificates: `certificate_enabled`

### Reports & Exports
- CSV exports for enrollments, payments, courses
- Streamed directly via `php://output` (no queue or file storage)
- Date range filtering on all exports

### Route Group
```php
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () { ... });
```

---

## 8. Instructor Dashboard

### Controllers (11 files in `app/Http/Controllers/Instructor/`)

| Controller | Methods | Purpose |
|------------|---------|---------|
| `DashboardController` | `index()`, `submissions()`, `progress()`, `analytics()`, `issueCertificate()` | Main hub + analytics + certificate issuing |
| `CourseController` | Full CRUD | Course management with thumbnail uploads |
| `ModuleController` | `store()`, `update()`, `destroy()` | Module CRUD within courses |
| `LessonController` | `store()`, `update()`, `destroy()` | Lesson CRUD with type-aware validation |
| `LessonVersionController` | `index()`, `restore()` | Version history & restore |
| `LessonResourceController` | `store()`, `destroy()` | File attachments (max 50MB, whitelist) |
| `LessonImageController` | `store()` | TinyMCE AJAX image upload |
| `QuizController` | Full CRUD + `attempts()`, `grade()`, `saveGrades()` | Quiz management + manual grading |
| `QuestionController` | `create()`, `store()`, `edit()`, `update()`, `destroy()` | 5 question types with dynamic Alpine.js forms |
| `AssignmentController` | `index()`, `store()`, `grade()` | Assignment creation + inline grading |
| `LiveSessionController` | `index()`, `store()`, `destroy()` | Jitsi Meet session scheduling |

### Dashboard Metrics (`index()`)
| Metric | Calculation |
|--------|-------------|
| `total_courses` | `$instructor->courses()->count()` |
| `total_students` | Enrollments in instructor's courses |
| `average_rating` | `$instructor->rating` (from instructors table) |

### Submissions Page
- **Alpine.js tabs** showing Assignment Submissions and Quiz Attempts
- Paginated 20 per page
- Shows student name, status, score, and grade action

### Progress Page
- Per-course student progress tables
- Progress bar per student
- **Certificate issue button** when progress ≥ 80%, not already issued, and not blocked

### Analytics Page
- `totalStudents` / `totalEnrollments`
- `completionRate` = completed / total enrollments
- `avgQuizScore` = average of quiz attempts for instructor's courses
- `monthlyEnrollments` = grouped by `DATE_FORMAT(created_at, "%Y-%m")`, last 12 months
- **Chart.js** line chart for monthly enrollments

### Quiz System
1. **Create Quiz** → select course, title, passing score, time limit, max attempts
2. **Add Questions** → 5 types with dynamic forms:
   - Multiple Choice (add/remove options, radio for correct)
   - True/False (auto-populates True/False)
   - Short Answer (text field, case-insensitive auto-grade reference)
   - Essay (textarea, instructor provides rubric/sample answer)
   - Fill in Blank (options-based)
3. **Auto-grading**: MC by option_id, TF by text match, SA/FB by `correct_answer` case-insensitive, Essay = manual
4. **Manual Grading**: Per-answer point editing, recalculates total score

### Assignment System
1. **Create Assignment** → title, description, instructions, max points, due date, late submission flag
2. **Submissions** → inline on assignments page with grade form
3. **Grading** → points + feedback, updates status to Graded

### Course Builder (`courses.show`)
- Add modules with display order
- Add lessons per module (Video/Reading/Quiz/Assignment)
- **Dynamic forms**: Video requires URL, Reading requires content, Quiz/Assignment show helper banners
- **TinyMCE** rich text with image upload
- **Versioning**: Every update auto-saves previous content
- **Resources**: File uploads per lesson

### Route Group
```php
Route::prefix('instructor')->middleware(['auth', 'instructor'])->name('instructor.')->group(function () { ... });
```

---

## 9. Student Dashboard

### Controllers (12 files in `app/Http/Controllers/Student/`)

| Controller | Methods | Purpose |
|------------|---------|---------|
| `DashboardController` | `index()`, `progress()`, `payments()`, `certificates()`, `downloadReceipt()` | Main hub + stats |
| `LearningController` | `show()`, `complete()`, `download()` | Core lesson player + progress tracking |
| `QuizController` | `index()`, `take()`, `submit()`, `attempts()`, `showAttempt()` | Quiz lifecycle |
| `AssignmentController` | `index()`, `show()`, `submit()` | Assignment submission |
| `DiscussionController` | `index()`, `show()`, `store()`, `reply()` | Course forums |
| `NoteController` | `index()`, `show()`, `store()` | Per-lesson notes |
| `LiveSessionController` | `index()`, `join()` | Jitsi Meet sessions |
| `AchievementController` | `index()` | Earned badges |
| `ReviewController` | `store()` | Course ratings/reviews |
| `ScheduleController` | `index()` | Weekly calendar |
| `TranscriptController` | `download()` | Academic transcript PDF |
| `LessonResourceController` | `download()` | Secure resource downloads |

### Dashboard Metrics (`index()`)
| Metric | Calculation |
|--------|-------------|
| `courses` | `$enrollments->count()` |
| `completed` | `$enrollments->where('progress', 100)->count()` |
| `certificates` | `$certificates->count()` |
| `avg_progress` | `$enrollments->avg('progress')` rounded |

### Learning Flow
1. **Access**: `/student/courses/{course}/lessons/{lesson}`
2. **Enrollment check**: Must be enrolled in the course
3. **Lesson validation**: `$lesson->module->course_id === $course->id`
4. **Progress loading**: Loads all modules → lessons → marks completion from `LessonProgress`
5. **Course progress**: `completedLessons / totalLessons * 100`
6. **Access tracking**: Creates/updates `LessonProgress` with `last_accessed`

### Lesson Types (Student View)
| Type | Rendered As |
|------|-------------|
| **Video** | YouTube/Vimeo iframe via `Lesson::embedUrl()` |
| **Reading** | Sanitized HTML content |
| **Quiz** | Quiz info card (time limit, attempts, passing score) + "Start Quiz" button |
| **Assignment** | Assignment info card (due date, max points) + "View Assignment" button |

### Mark Complete
- POST to `/student/courses/{course}/lessons/{lesson}/complete`
- Creates/updates `LessonProgress` → `Completed`, `progress_percentage = 100`
- **Recalculates enrollment progress** and updates `enrollment_status`
- **Quiz/Assignment lessons do NOT show "Mark Complete"** — completion is handled by taking the quiz/submitting the assignment

### Quiz Taking Flow
1. **Index**: Lists all quizzes from enrolled courses with attempts, best score, pass/fail
2. **Take**:
   - Checks `max_attempts` against completed attempts
   - Reuses existing `In Progress` attempt or creates new one
   - Timer with `remainingSeconds`; auto-submits with score 0 if expired
3. **Submit**:
   - Clears previous answers for the attempt
   - **Auto-grading by type**:
     - MC: option_id vs `is_correct`
     - TF: case-insensitive text match
     - SA/FB: case-insensitive exact match vs `correct_answer`
     - Essay: `is_correct = false`, `points_earned = 0`
   - Score = `($earnedPoints / $totalPoints) * 100`
   - Status = `'Submitted'` if essay present, else `'Graded'`
4. **Review**: Color-coded options (green=correct, red=wrong, neutral=other)

### Assignment Submission Flow
1. **Index**: Assignments across all enrolled courses with due dates, status, points
2. **Show**: Details + previous submission (with grade/feedback if graded) + submission form
3. **Submit**:
   - Validates text (nullable, max 10,000) and file (nullable, max 50MB)
   - Stores file to `storage/app/public/assignment-submissions/{assignment_id}`
   - Checks lateness: `due_date && now()->isAfter($due_date)`
   - Status = `Late` or `Submitted`
   - **Note:** Does not support multiple resubmissions properly — always creates with `attempt_number = 1`

### Enrollment & Payment Flow
1. **Duplicate check**: `isEnrolledIn($course->id)` prevents re-enrollment
2. **Registration fee gate**: Requires completed ZMW 150 `RegistrationFee` before any enrollment
3. **Capacity check**: Blocks if `$course->enrollment_count >= $course->max_students`
4. **Price**: Uses `discount_price` if set, otherwise `price`. Free if `<= 0`
5. **Enrollment creation**:
   - `enrollment_status = 'Enrolled'`
   - `payment_status = 'completed'` (free) or `'pending'` (paid)
   - `certificate_blocked = !$isFree`
6. **Payment plan**: Creates `EnrollmentPaymentPlan`
7. **Redirect**: Free → course page; Paid → checkout

### Route Group
```php
Route::prefix('student')->middleware(['auth', 'student'])->name('student.')->group(function () { ... });
```

---

## 10. Finance Dashboard

### Controllers (2 files)

| Controller | Methods | Purpose |
|------------|---------|---------|
| `Finance\DashboardController` | `index()`, `transactions()`, `payments()`, `verify()`, `invoices()`, `downloadInvoice()` | Revenue stats, payment verification, invoice management |
| `Payment\LencoWebhookController` | `handle()` | Receives and processes Lenco webhooks |

### Dashboard Metrics (`index()`)
- Total revenue, pending amount, today's revenue, monthly revenue
- Recent payments (last 20)
- 6-month revenue chart data

### Payment Verification
- `verify(Payment $payment)`:
  - Uses `PaymentVerificationService`
  - Marks payment as `Completed`
  - Updates enrollment status (30% deposit rule, 100% certificate rule)
  - Generates invoice via `InvoiceService`

### Route Group
```php
Route::prefix('finance')->middleware(['auth', 'finance'])->name('finance.')->group(function () { ... });
```

---

## 11. Core Services

### `CertificateService`
- Generates certificate numbers: `NRC {national_id}` format
- Generates student numbers: `YYEdu######`
- Generates 32-char verification codes
- Issues certificates for enrollments (blocks if `certificate_blocked`)
- Generates PDFs via TCPDF using `certificates.pdf` view
- Computes graduation date with ordinal suffixes

### `EmailQueueService`
- PHPMailer with Gmail SMTP (STARTTLS, port 587)
- Config from `config/mail.php`
- `queue()`: Creates `EmailQueue` record; sends immediately if `queue.default === 'sync'`
- `processQueue($limit = 50)`: Batch processes pending emails, retries up to 3 times
- `sendImmediate()`: Direct send bypassing queue

### `HtmlSanitizer`
- HTMLPurifier wrapper with safe whitelist
- Allows: p, div, h1-h6, table, iframe (YouTube/Vimeo), img, a, ul/ol/li, etc.
- Caches purifier config for performance

### `InvoiceService`
- Auto-numbering: `INV-YYYY-#####`
- Calculates discounts from course prices
- Generates PDFs via TCPDF using `invoices.pdf` view

### `LencoPaymentService`
- Environment-aware: sandbox vs live API
- `initializePayment()`: POSTs to Lenco `/v1/payments`
- `verifyPayment()`: GETs status from Lenco
- `processWebhook()`: Maps events (`payment.success` → completed, etc.), updates records
- `updateEnrollmentPaymentStatus()`:
  - **30% deposit rule**: If ≥30% paid and status is "Enrolled", upgrades to "In Progress"
  - **100% certificate rule**: `certificate_blocked = true` until fully paid
- `validateWebhookSignature()`: HMAC-SHA256 with `LENCO_SECRET_KEY`

### `PaymentVerificationService`
- Manual verification for finance staff
- Same 30%/100% logic as Lenco
- Triggers invoice generation

### `LessonExportService`
- Exports lessons as PDFs via TCPDF
- Sanitizes HTML before rendering

---

## 12. Key Workflows

### Payment Processing (Lenco)
1. Student checks out → `CheckoutController::process()` creates pending Payment + LencoTransaction
2. Redirects to Lenco checkout URL
3. Lenco sends webhook → `LencoWebhookController::handle()`
4. Validates HMAC-SHA256 signature
5. Logs to `lenco_webhook_logs`
6. `LencoPaymentService::processWebhook()` updates status
7. `updateEnrollmentPaymentStatus()` applies 30%/100% rules

### Certificate Issuing
1. Instructor clicks "Issue Certificate" on progress page
2. `InstructorDashboardController::issueCertificate()` validates ownership
3. Blocks if `certificate_blocked` or already issued
4. `CertificateService::issueCertificate($enrollment)`:
   - Generates certificate number, student number, verification code
   - Creates `Certificate` record
   - Sets `enrollment.certificate_issued = true`
5. Student can download PDF from certificates page

### Email Sending
1. Controller calls `EmailQueueService::queue()` or `sendImmediate()`
2. If queued: record created in `email_queue` with status `pending`
3. Cron or manual trigger calls `processQueue()`
4. Fetches pending emails ordered by priority DESC, created_at ASC
5. Sends via PHPMailer, updates status to `sent` or `failed`
6. Retries up to 3 attempts

---

## 13. Known Issues & Technical Debt

### Schema Issues
1. **Duplicate option tables**: `question_options` AND `quiz_question_options` both store answer options. May be redundant.
2. **Custom PKs**: Many tables use non-standard PKs (`payment_id`, `certificate_id`, etc.) which complicates Laravel conventions.
3. **No soft deletes**: Any delete is permanent. No recovery mechanism.
4. **No timestamps**: Many tables disable timestamps, losing audit trails.
5. **Enum mismatches**: Legacy data may have different enum values than current code expects.

### Role System Issues
1. **`isAdmin()` checks roles 1 AND 2**: Means two admin-level roles exist. Role ID 2 may have been intended as Instructor in legacy docs but is treated as admin in code.
2. **`LiveSessionController` checks `role_id == 3`** while `InstructorMiddleware` uses `isInstructor()` — potential inconsistency if role IDs shift.

### Authorization Issues
1. **No Laravel Policies/Gates**: All auth is middleware-based. No fine-grained model-level authorization.
2. **No ownership policy**: Instructors manually check `$course->instructor_id` in every controller.

### Data Integrity Issues
1. **Quiz-Assignment lesson linking**: Quizzes and assignments have `lesson_id` FKs, but the UI for linking them to lessons is manual/separate. Instructors create quiz lessons and quizzes independently.
2. **Assignment resubmissions**: `AssignmentController::submit()` always sets `attempt_number = 1` instead of incrementing.
3. **Lesson type storage**: Previously stored with `ucfirst()` creating mismatches with DB enum. Recently fixed to match enum exactly (`Video`, `Reading`, `Quiz`, `Assignment`).

### Frontend Issues
1. **No build step**: Tailwind CSS is pre-compiled; any custom styles require manual rebuild.
2. **Mixed JS approaches**: Some views use Alpine.js, others use vanilla JS for toggle forms.
3. **TinyMCE init on hidden editors**: Can cause issues when editors are inside `x-show` containers.

### Payment Issues
1. **Manual payment recording**: Admin can create payments directly with no gateway reconciliation.
2. **CSV exports**: Streamed directly with no queue; could timeout on large datasets.
3. **No refund workflow**: Refunded status exists in enum but no dedicated refund process.

### Email Issues
1. **Gmail SMTP dependency**: Hard dependency on Gmail; no fallback mailer.
2. **No email scheduling**: `scheduled_at` field exists but may not be actively used.

---

## 14. Recent Changes & Fixes

### Quiz Question CRUD (Recently Built)
- `QuestionController` with full CRUD
- Dynamic Alpine.js forms for 5 question types
- Auto-grading logic for all types + manual grading for Essay
- `questions` table: added `correct_answer` text column for SA/FB
- `question_options`: `$timestamps = false`
- `quiz_questions` pivot: no timestamps

### Instructor DashboardController Fixes
- `progress()`: Fixed `Enrollment::student()` relationship (`User::class` → `Student::class`)
- `progress()`: Fixed `LessonProgress` query (no `course_id` column)
- `progress()`: Fixed `AssignmentSubmission` query (no `course_id` column)
- `analytics()`: Fixed `status` → `enrollment_status` enum mismatch
- `submissions()`: Fixed blade views to use `$submission->student->user->full_name`

### Lesson Creation UI (Recently Updated)
- Dynamic Alpine.js forms: type selection changes visible fields
- Video lessons require `video_url`
- Reading lessons require `content`
- Quiz/Assignment show helper banners
- Fixed `ucfirst()` bug — now stores exact DB enum values

### Student Learning View (Recently Updated)
- Type-aware rendering: Video embed, Reading content, Quiz card, Assignment card
- Added `Lesson::embedUrl()` for YouTube/Vimeo conversion
- Quiz/Assignment lessons hide "Mark Complete" button
- Sidebar icons reflect lesson type

### Button Component
- Added `warning` and `success` variants

---

## 15. Areas for Improvement

### High Priority
1. **Unified role system**: Clarify role IDs 1/2/3 discrepancy. Document which role is which.
2. **Laravel Policies**: Implement model policies for Course, Quiz, Assignment, etc. to replace manual ownership checks.
3. **Quiz-Assignment lesson linking**: Build UI to select/create a quiz or assignment directly from the lesson form.
4. **Assignment resubmissions**: Fix `attempt_number` increment logic.
5. **Duplicate option tables**: Consolidate `question_options` and `quiz_question_options`.

### Medium Priority
6. **Soft deletes**: Add soft deletes to key models (courses, lessons, users) to prevent accidental data loss.
7. **Frontend build**: Consider Vite build for Tailwind to enable JIT and purging.
8. **Email provider fallback**: Add fallback mailer configuration beyond Gmail SMTP.
9. **Queue worker**: Implement proper Laravel queue workers instead of sync/batch processing.
10. **API rate limiting**: Add rate limiting to public endpoints (certificate verify, contact form).

### Low Priority
11. **Dark mode consistency**: Some views may not fully support dark mode.
12. **Mobile responsiveness**: Test all instructor forms on mobile; some tables may overflow.
13. **Chart caching**: Cache analytics chart data instead of recalculating on every request.
14. **Activity log utilization**: `activity_logs` table exists but may not be populated by all actions.
15. **Notification system**: `notifications` table exists but may be underutilized; consider real-time notifications.

---

*End of System Architecture Document*
