# Gap Analysis: Old PHP Site → Laravel App

**Date:** 2026-05-22  
**Old Site:** `public/`, `src/`, `cron/` (custom PHP 8.0+)  
**Laravel App:** `routes/web.php`, `app/Http/Controllers/`, `resources/views/` (Laravel 10.x)  

---

## Legend

| Icon | Meaning |
|------|---------|
| ✅ | Already exists in Laravel (parity) |
| ⚠️ | Partially exists / needs improvement |
| ❌ | Missing in Laravel — candidate to bring over |
| 🔄 | Exists but implemented differently |

---

## 1. PUBLIC PAGES

| Feature | Old Site | Laravel | Gap |
|---------|----------|---------|-----|
| Homepage with hero slides | `index.php` | `home.blade.php` | ✅ Parity |
| About page with team | `about.php` | `about.blade.php` | ✅ Parity |
| Contact form | `contact.php` | `contact.blade.php` | ✅ Parity |
| FAQ page | `faq.php` | `faq.blade.php` | ✅ Parity |
| Terms of Service | `terms.php` | ❌ Not found | ❌ **Missing** |
| Privacy Policy | `privacy.php` | ❌ Not found | ❌ **Missing** |
| Campus gallery | `campus.php` | `campus.blade.php` | ✅ Parity |
| Events listing | `events.php` | `events.blade.php` | ✅ Parity |
| Single event detail | `event.php?slug=` | ❌ Not found | ❌ **Missing** |
| Testimonials | `testimonials.php` | `testimonials.blade.php` | ✅ Parity |
| Course catalog | `courses.php` | `courses/index.blade.php` | ✅ Parity |
| Course detail | `course.php?id=\|slug=` | `courses/show.blade.php` | ✅ Parity |
| **Course preview (free lessons)** | `course-preview.php?course=` | ❌ Not found | ❌ **Missing — HIGH VALUE** |
| Course discussions (enrolled) | `course-discussions.php?course=` | `student/discussions/` | ✅ Parity |
| Course search | `search.php?q=` | `search.blade.php` | ✅ Parity |
| Enrollment + payment | `enroll.php` → `checkout.php` | `CheckoutController` | ✅ Parity |
| Lenco checkout page | `lenco-checkout.php` | `CheckoutController@process` | 🔄 Different flow |
| Payment success/failed | `payment-success.php`, `payment-failed.php` | `payment/success.blade.php`, `payment/failed.blade.php` | ✅ Parity |
| Registration fee (K150) | `registration-fee.php` | `RegistrationFeeController` | ✅ Parity |
| Login / Register | `login.php`, `register.php` | `Auth\LoginController`, `Auth\RegisterController` | ✅ Parity |
| Email verification | `verify-email.php` | `Auth\VerifyEmailController` | ✅ Parity |
| Password reset | `forgot-password.php`, `reset-password.php` | `Auth\ForgotPasswordController` | ✅ Parity |
| Google OAuth | `google-callback.php` | `Auth\GoogleController` | ✅ Parity |
| **Newsletter subscription** | `newsletter-subscribe.php` | Closure in `routes/web.php` | 🔄 Minimal — could enhance |
| Certificate verification | `verify-certificate.php?code=` | `CertificateController@verify` | ✅ Parity |
| **Certificate preview (HTML)** | `certificate-preview.php?id=` | `certificates/preview.blade.php` | ✅ Parity |
| 404 page | `404.php` | `errors/404.blade.php` | ✅ Parity |

### 🔴 Missing Public Pages to Bring Over
1. **`terms.blade.php`** — Terms of Service (static page, needed for legal compliance)
2. **`privacy.blade.php`** — Privacy Policy (static page, needed for legal compliance)
3. **`events/show.blade.php`** — Single event detail page with photo gallery
4. **`courses/preview.blade.php`** — **Course preview** showing free lessons before enrollment (high conversion value)

---

## 2. STUDENT DASHBOARD

| Feature | Old Site | Laravel | Gap |
|---------|----------|---------|-----|
| Student dashboard | `dashboard.php` | `student/dashboard.blade.php` | ✅ Parity |
| View profile | `profile.php` | `profile/show.blade.php` | ✅ Parity |
| Edit profile + avatar | `edit-profile.php` | `profile/edit.blade.php` | ✅ Parity |
| My courses | `my-courses.php` | `enrollments/index.blade.php` | ✅ Parity |
| My certificates | `my-certificates.php` | `student/certificates.blade.php` | ✅ Parity |
| My payments | `my-payments.php` | `student/payments.blade.php` | ✅ Parity |
| **Transcript download** | ❌ Not found | `Student\TranscriptController@download` | 🔄 Laravel has it, old didn't |
| Learning interface | `learn.php?course=` | `student/learning/show.blade.php` | ✅ Parity |
| Lesson viewer | `lesson.php?id=` | `student/learning/show.blade.php` | ✅ Parity |
| Assignment detail + submit | `assignment.php?id=` | `student/assignments/show.blade.php` | ✅ Parity |
| Quiz detail | `quiz.php?id=` | ❌ Not found separately | ⚠️ Part of `take.blade.php` |
| Take quiz | `student/take-quiz.php` | `student/learning/quiz.blade.php` | ✅ Parity |
| Quiz results | `quiz-result.php?attempt_id=` | `student/learning/quiz_result.blade.php` | ✅ Parity |
| Review course | `review-course.php?course_id=` | `Student\ReviewController@store` | ✅ Parity (inline) |
| Live session join | `live-session.php?session_id=` | `student/live-sessions/join.blade.php` | ✅ Parity |
| **Student hub / navigation** | `student/index.php` | ❌ Not found | ❌ **Missing — nice UX** |
| Achievements | `student/achievements.php` | `student/achievements/index.blade.php` | ✅ Parity |
| Assignments list | `student/assignments.php` | `student/assignments/index.blade.php` | ✅ Parity |
| Quiz results list | `student/quiz-results.php` | ❌ Not found | ❌ **Missing** |
| Quizzes list | `student/quizzes.php` | ❌ Not found | ❌ **Missing** |
| Schedule | `student/schedule.php` | `student/schedule.blade.php` | ✅ Parity |
| Help/FAQ | `student/help.php` | ❌ Not found | ❌ **Missing** |

### 🔴 Missing Student Features to Bring Over
1. **`student/quizzes/index.blade.php`** — List of all quizzes with attempt status (old had this)
2. **`student/quiz-results/index.blade.php`** — List of all quiz attempt results with detailed feedback
3. **`student/help.blade.php`** — Student help & documentation center
4. **`student/index.blade.php`** — Student hub / central navigation page (nice UX from old site)

---

## 3. INSTRUCTOR DASHBOARD

| Feature | Old Site | Laravel | Gap |
|---------|----------|---------|-----|
| Instructor dashboard | `instructor/index.php` | `instructor/dashboard.blade.php` | ✅ Parity |
| Course list | `instructor/courses.php` | `instructor/courses/index.blade.php` | ✅ Parity |
| Create course | `instructor/courses/create.php` | `instructor/courses/create.blade.php` | ✅ Parity |
| Edit course | `instructor/course-edit.php` | `instructor/courses/edit.blade.php` | ✅ Parity |
| **Course content builder** | `instructor/courses/modules.php?id=` | `instructor/courses/show.blade.php` | ⚠️ Old had drag-drop reordering |
| **Lesson resources upload** | `instructor/courses/lesson-resources.php?lesson_id=` | ❌ Not found | ❌ **Missing** |
| **Bulk CSV upload** | `instructor/courses/bulk-upload.php?id=` | ❌ Not found | ❌ **Missing** |
| **Course templates** | `instructor/courses/templates.php` | ❌ Not found | ❌ **Missing — HIGH VALUE** |
| Analytics | `instructor/analytics.php` | `instructor/analytics.blade.php` | ✅ Parity |
| Assignments + grading | `instructor/assignments.php` | `instructor/assignments/index.blade.php` | ✅ Parity |
| Student progress | `instructor/students.php` | `instructor/progress.blade.php` | ✅ Parity |
| Quiz management | `instructor/quizzes.php` | `instructor/quizzes/index.blade.php` | ✅ Parity |
| Live sessions | `instructor/live-sessions.php` | `instructor/live-sessions/index.blade.php` | ✅ Parity |
| **Quick actions** | `instructor/quick-actions.php` | ❌ Not found | ❌ **Missing — nice UX** |
| Help | `instructor/help.php` | ❌ Not found | ❌ **Missing** |

### 🔴 Missing Instructor Features to Bring Over
1. **`instructor/courses/{course}/resources.blade.php`** — Upload/manage downloadable lesson resources (files attached to lessons)
2. **`instructor/courses/bulk-upload.blade.php`** — Bulk CSV upload of lessons/resources
3. **`instructor/courses/templates.blade.php`** — **Pre-built course templates for quick starts** (high value)
4. **`instructor/quick-actions.blade.php`** — Fast-access forms for common tasks (nice UX)
5. **`instructor/help.blade.php`** — Instructor help & documentation

---

## 4. ADMIN DASHBOARD

| Feature | Old Site | Laravel | Gap |
|---------|----------|---------|-----|
| Admin dashboard | `admin/index.php` | `admin/dashboard.blade.php` | ✅ Parity |
| Users management | `admin/pages/users.php` | `admin/users/index.blade.php` | ✅ Parity |
| Courses management | `admin/pages/courses.php` | `admin/courses/index.blade.php` | ✅ Parity |
| Enrollments | `admin/pages/enrollments.php` | `admin/enrollments/index.blade.php` | ✅ Parity |
| Financials | `admin/pages/financials.php` | `finance/dashboard.blade.php` | 🔄 Moved to dedicated finance |
| Modules & lessons | `admin/pages/modules.php` | `admin/courses/show.blade.php` | ⚠️ Old had drag-drop reordering |
| Announcements | `admin/pages/announcements.php` | `admin/announcements/index.blade.php` | ✅ Parity |
| Settings | `admin/pages/settings.php` | `admin/settings.blade.php` | ✅ Parity |
| Events | `admin/events.php` | `admin/events/index.blade.php` | ✅ Parity |
| Institution photos | `admin/institution-photos.php` | `admin/photos/index.blade.php` | ✅ Parity |
| **Company profile** | `admin/pages/company-profile.php` | ❌ Not found | ❌ **Missing** |
| **Help center** | `admin/help.php` | ❌ Not found | ❌ **Missing** |

### 🔴 Missing Admin Features to Bring Over
1. **`admin/company-profile.blade.php`** — View current company config (read-only summary)
2. **`admin/help.blade.php`** — Admin help & documentation center

---

## 5. FINANCE DASHBOARD

| Feature | Old Site | Laravel | Gap |
|---------|----------|---------|-----|
| Finance functions | `admin/pages/financials.php` | `finance/dashboard.blade.php` | ✅ Dedicated finance dashboard exists |
| Revenue stats | `admin/pages/financials.php` | `finance/dashboard.blade.php` | ✅ Parity |
| Payments list + verify | `admin/handlers/financials_handler.php` | `finance/payments.blade.php` | ✅ Parity |
| Invoices | `admin/pages/financials.php` | `finance/invoices.blade.php` | ✅ Parity |
| Transactions | ❌ (API only) | `finance/transactions.blade.php` | ✅ Laravel has more |
| **Revenue export** | `admin/pages/financials.php` | `admin/reports.blade.php` | ⚠️ Old had more granular export |

---

## 6. API ENDPOINTS

| Feature | Old Site | Laravel | Gap |
|---------|----------|---------|-----|
| REST API router | `api/index.php` | `routes/api.php` | ✅ Parity |
| Announcements API | `api/announcements.php` | ❌ Not found in API routes | ❌ **Missing** |
| Assignment API | `api/assignment.php` | ❌ Not found in API routes | ❌ **Missing** |
| Auth API (JWT) | `api/auth.php` | Sanctum `/api/user` | 🔄 Different auth |
| Badges API | `api/badges.php` | ❌ Not found in API routes | ❌ **Missing** |
| Categories API | `api/categories.php` | ❌ Not found in API routes | ❌ **Missing** |
| Certificates API | `api/certificates.php` | `Api\CertificateController` | ✅ Parity |
| Course assignments API | `api/course-assignments.php` | ❌ Not found | ❌ **Missing** |
| Courses API | `api/courses.php` | `Api\CourseController` | ✅ Parity |
| Discussions API | `api/discussions.php` | ❌ Not found in API routes | ❌ **Missing** |
| Download resource API | `api/download-resource.php?id=` | ❌ Not found | ❌ **Missing** |
| Enrollments API | `api/enrollments.php` | `Api\EnrollmentController` | ✅ Parity |
| Instructors API | `api/instructors.php` | ❌ Not found in API routes | ❌ **Missing** |
| Lenco payment API | `api/lenco-payment.php` | `LencoPaymentService` | 🔄 Service-based |
| Lenco webhook | `api/lenco-webhook.php` | `Payment\LencoWebhookController` | ✅ Parity |
| Lesson notes API | `api/lesson-notes.php` | ❌ Not found in API routes | ❌ **Missing** |
| Lesson progress API | `api/lesson-progress.php` | `Api\ProgressController` | ✅ Parity |
| Lesson resources API | `api/lesson-resources.php` | ❌ Not found in API routes | ❌ **Missing** |
| Lessons API | `api/lessons.php` | ❌ Not found in API routes | ❌ **Missing** |
| Live sessions API | `api/live-sessions.php` | ❌ Not found in API routes | ❌ **Missing** |
| Logs API | `api/logs.php` | ❌ Not found in API routes | ❌ **Missing** |
| Notifications API | `api/notifications.php` | `Api\NotificationController` | ✅ Parity |
| **SSE notifications stream** | `api/notifications-stream.php` | ❌ Not found | ❌ **Missing — HIGH VALUE** |
| Payment callback | `api/payment-callback.php` | `CheckoutController` | 🔄 Different flow |
| Payments API | `api/payment.php` | ❌ Not found in API routes | ❌ **Missing** |
| Settings API | `api/settings.php` | ❌ Not found in API routes | ❌ **Missing** |
| Switch role API | `api/switch-role.php` | ❌ Not found | ❌ **Missing** |
| Transactions API | `api/transactions.php` | ❌ Not found in API routes | ❌ **Missing** |
| Users API | `api/users.php` | ❌ Not found in API routes | ❌ **Missing** |

### 🔴 Missing API Endpoints to Bring Over
1. **`/api/notifications/stream`** — **SSE endpoint for real-time notifications** (the old site had this!)
2. **`/api/announcements`** — Announcements CRUD
3. **`/api/assignments`** — Assignment operations
4. **`/api/badges`** — Badges & achievements
5. **`/api/categories`** — Course categories
6. **`/api/discussions`** — Discussion threads & replies
7. **`/api/download-resource`** — Secure download of lesson resources
8. **`/api/instructors`** — List all instructors
9. **`/api/lesson-notes`** — Save/retrieve student notes
10. **`/api/lesson-resources`** — Upload/manage lesson resources
11. **`/api/lessons`** — Get lessons by course/module
12. **`/api/live-sessions`** — Schedule, join, attendance
13. **`/api/logs`** — Activity logs
14. **`/api/payments`** — Payment operations
15. **`/api/settings`** — System settings
16. **`/api/switch-role`** — Role switching for multi-role users
17. **`/api/transactions`** — Financial transactions
18. **`/api/users`** — Users CRUD with roles

---

## 7. EMAIL TEMPLATES

| Template | Old Site | Laravel | Gap |
|----------|----------|---------|-----|
| Welcome email | `src/mail/welcome.php` | ❌ Not found | ❌ **Missing** |
| Verify email | `src/mail/verify-email.php` | `emails/password-reset.blade.php` | ⚠️ Only password reset exists |
| Reset password | `src/mail/reset-password.php` | `emails/password-reset.blade.php` | ✅ Parity |
| Admin reset password | `src/mail/password-reset-by-admin.php` | ❌ Not found | ❌ **Missing** |
| Enrollment confirm | `src/mail/enrollment-confirm.php` | ❌ Not found | ❌ **Missing** |
| Payment received | `src/mail/payment-received.php` | ❌ Not found | ❌ **Missing** |
| Certificate issued | `src/mail/certificate-issued.php` | ❌ Not found | ❌ **Missing** |
| Announcement notification | `src/mail/announcement-notification.php` | ❌ Not found | ❌ **Missing** |
| Admin new user alert | `src/mail/admin-new-user.php` | ❌ Not found | ❌ **Missing** |
| Admin enrollment alert | `src/mail/admin-enrollment.php` | ❌ Not found | ❌ **Missing** |
| Admin payment alert | `src/mail/admin-payment.php` | ❌ Not found | ❌ **Missing** |

### 🔴 Missing Email Templates to Bring Over
All 10 email templates from the old site are missing in Laravel. The `EmailQueueService` exists but only sends password resets.

---

## 8. CRON JOBS

| Cron Job | Old Site | Laravel | Gap |
|----------|----------|---------|-----|
| Process email queue (5 min) | `cron/process-emails.php` | ❌ Not found | ❌ **Missing** |
| Session reminders (daily 8am) | `cron/session-reminders.php` | ❌ Not found | ❌ **Missing** |

### 🔴 Missing Cron Jobs to Bring Over
1. **`app/Console/Commands/ProcessEmailQueue.php`** + `Kernel.php` schedule — Process `email_queue` table every 5 minutes
2. **`app/Console/Commands/SessionReminders.php`** + `Kernel.php` schedule — Send live session reminders (30min, 5min, start)

---

## 9. DOMAIN MODEL CLASSES (Old → Laravel Eloquent)

| Old Class | Laravel Model | Status |
|-----------|---------------|--------|
| `Announcement` | `Announcement` | ✅ Parity |
| `Assignment` | `Assignment` | ✅ Parity |
| `Badge` | `Badge` | ✅ Parity |
| `Category` | `CourseCategory` | ✅ Parity |
| `Certificate` | `Certificate` | ✅ Parity |
| `Course` | `Course` | ✅ Parity |
| `Discussion` | `Discussion` + `DiscussionReply` | ✅ Parity |
| `Email` | `EmailQueue` | ✅ Parity |
| `Enrollment` | `Enrollment` | ✅ Parity |
| `Event` | `Event` | ✅ Parity |
| `FileUpload` | — | ❌ Utility class missing |
| `GoogleDriveService` | — | ❌ Service exists but not wired |
| `InstitutionPhoto` | `InstitutionPhoto` | ✅ Parity |
| `Instructor` | `Instructor` | ✅ Parity |
| `Invoice` | `Invoice` | ✅ Parity |
| `Lenco` | `LencoTransaction` + `LencoWebhookLog` | ✅ Parity |
| `Lesson` | `Lesson` | ✅ Parity |
| `LessonResource` | `LessonResource` | ✅ Parity |
| `LiveSession` | `LiveSession` + `LiveSessionAttendance` | ✅ Parity |
| `Module` | `Module` | ✅ Parity |
| `Notification` | `Notification` | ✅ Parity |
| `Payment` | `Payment` | ✅ Parity |
| `PaymentPlan` | `EnrollmentPaymentPlan` | ✅ Parity |
| `Progress` | `LessonProgress` | ✅ Parity |
| `Question` | `Question` + `QuestionOption` | ✅ Parity |
| `Quiz` | `Quiz` | ✅ Parity |
| `RegistrationFee` | `RegistrationFee` | ✅ Parity |
| `Review` | `CourseReview` | ✅ Parity |
| `Statistics` | — | ❌ No dedicated stats class |
| `Submission` | `AssignmentSubmission` | ✅ Parity |
| `User` | `User` | ✅ Parity |

---

## 10. PRIORITIZED MIGRATION LIST

### 🔴 P0 — Critical / High Business Value

| # | Feature | Why | Effort |
|---|---------|-----|--------|
| 1 | **Terms & Privacy pages** | Legal compliance for launch | Low |
| 2 | **Email templates (10 total)** | `EmailQueueService` exists but has nothing to send | Medium |
| 3 | **Cron jobs** (email queue + session reminders) | Core operational infrastructure | Low |
| 4 | **Course preview page** | High conversion — lets visitors try before buying | Medium |
| 5 | **SSE real-time notifications** | Old site had live notifications; Laravel is polling-only | Medium |
| 6 | **Lesson resources upload/download** | Students can't download course materials | Medium |

### 🟡 P1 — Should Have

| # | Feature | Why | Effort |
|---|---------|-----|--------|
| 7 | **Student quizzes list + quiz results list** | Students can't see all their quiz history | Low |
| 8 | **Student help page** | Reduces support burden | Low |
| 9 | **Instructor course templates** | Speeds up course creation | Medium |
| 10 | **Instructor bulk CSV upload** | Speeds up content creation | Medium |
| 11 | **Instructor lesson resources management** | Instructors can't attach files to lessons | Medium |
| 12 | **Single event detail page** | Events currently have no detail view | Low |
| 13 | **API endpoints** (discussions, lessons, live-sessions, etc.) | Needed for mobile app / SPA frontend | Medium-High |
| 14 | **Role switcher** (`/api/switch-role`) | Multi-role users can't switch contexts | Low |

### 🟢 P2 — Nice to Have

| # | Feature | Why | Effort |
|---|---------|-----|--------|
| 15 | **Student hub / navigation page** | Nice UX from old site | Low |
| 16 | **Instructor quick actions** | Fast-access forms for common tasks | Low |
| 17 | **Admin company profile view** | Read-only summary of config | Low |
| 18 | **Admin / Instructor / Student help pages** | Documentation centers | Low |
| 19 | **Drag-drop module/lesson reordering** | Old site had this; Laravel uses manual ordering | Medium |
| 20 | **Activity logs view** (admin) | Audit trail visibility | Low |

---

## 11. FILES TO REFERENCE FROM OLD SITE

When implementing missing features, these old files contain the working logic:

```
public/terms.php                          → resources/views/pages/terms.blade.php
public/privacy.php                        → resources/views/pages/privacy.blade.php
public/event.php                          → resources/views/events/show.blade.php
public/course-preview.php                 → resources/views/courses/preview.blade.php
public/student/quizzes.php                → resources/views/student/quizzes/index.blade.php
public/student/quiz-results.php           → resources/views/student/quiz-results/index.blade.php
public/student/help.php                   → resources/views/student/help.blade.php
public/instructor/courses/templates.php   → resources/views/instructor/courses/templates.blade.php
public/instructor/courses/bulk-upload.php → resources/views/instructor/courses/bulk-upload.blade.php
public/instructor/courses/lesson-resources.php → resources/views/instructor/courses/resources.blade.php
public/instructor/quick-actions.php       → resources/views/instructor/quick-actions.blade.php
public/instructor/help.php                → resources/views/instructor/help.blade.php
public/admin/pages/company-profile.php    → resources/views/admin/company-profile.blade.php
public/admin/help.php                     → resources/views/admin/help.blade.php

src/mail/*.php                            → resources/views/emails/*.blade.php
cron/process-emails.php                   → app/Console/Commands/ProcessEmailQueue.php
cron/session-reminders.php                → app/Console/Commands/SessionReminders.php

public/api/notifications-stream.php       → routes/api.php SSE endpoint
public/api/switch-role.php                → app/Http/Controllers/Api/AuthController.php
public/api/download-resource.php          → app/Http/Controllers/Api/ResourceController.php
```

---

*Analysis based on commit `83fababf` (old PHP) vs current `laravel-migration` branch.*
