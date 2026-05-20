# Laravel Migration Gap Analysis
## Original PHP System vs Laravel Migration

**Date:** May 10, 2026  
**Original System:** Custom PHP (public/, src/, config/)  
**Laravel Migration:** `laravel/` directory (Laravel 10.x)

---

## 1. PUBLIC PAGES

### ✅ Implemented in Laravel
| Page | Laravel Route/View |
|------|-------------------|
| Home | `HomeController@index` → `home.blade.php` |
| About | `HomeController@about` → `about.blade.php` |
| Contact | `HomeController@contact` → `contact.blade.php` |
| Campus | `HomeController@campus` → `campus.blade.php` |
| FAQ | `HomeController@faq` → `faq.blade.php` |
| Testimonials | `HomeController@testimonials` → `testimonials.blade.php` |
| Events | `HomeController@events` → `events.blade.php` |
| Courses List | `CourseController@index` → `courses/index.blade.php` |
| Course Detail | `CourseController@show` → `courses/show.blade.php` |
| Certificate Verify | `CertificateController@verify` → `certificates/verify.blade.php` |
| Login | `Auth\LoginController@show` → `auth/login.blade.php` |
| Register | `Auth\RegisterController@show` → `auth/register.blade.php` |
| Forgot Password | `Auth\ForgotPasswordController@show` → `auth/forgot-password.blade.php` |
| Reset Password | `Auth\ForgotPasswordController@resetForm` → `auth/reset-password.blade.php` |

### ❌ Missing in Laravel
| Page | Original File | Description |
|------|--------------|-------------|
| **Search** | `public/search.php` | Dedicated search page for courses/content |
| **Privacy Policy** | `public/privacy.php` | Legal privacy page |
| **Terms of Service** | `public/terms.php` | Legal terms page |
| **Contact Form Handler** | `public/contact.php` (POST) | Contact form submission processing |
| **Email Verification** | `public/verify-email.php` | Email verification page |
| **Newsletter Subscribe** | `public/newsletter-subscribe.php` | Newsletter signup handler |
| **Google Callback** | `public/google-callback.php` | Google OAuth callback (Laravel has controller) |
| **Certificate Preview** | `public/certificate-preview.php` | Preview certificate before download |
| **Course Preview** | `public/course-preview.php` | Public course preview (not enrolled) |
| **Course Enrollment Card** | `public/course-enrollment-card.php` | Enrollment card display |
| **Review Course** | `public/review-course.php` | Course review submission page |
| **Registration Fee** | `public/registration-fee.php` | Registration fee payment page |
| **Payment Success** | `public/payment-success.php` | Payment success page |
| **Payment Failed** | `public/payment-failed.php` | Payment failed page |
| **Lenco Checkout** | `public/lenco-checkout.php` | Lenco payment checkout flow |
| **Checkout** | `public/checkout.php` | Generic checkout page |
| **Enroll** | `public/enroll.php` | Enrollment form/page |

---

## 2. STUDENT DASHBOARD

### ✅ Implemented in Laravel
| Feature | Controller/View |
|---------|----------------|
| Dashboard | `Student\DashboardController@index` → `student/dashboard.blade.php` |
| Progress | `Student\DashboardController@progress` → `student/progress.blade.php` |
| Payments | `Student\DashboardController@payments` → `student/payments.blade.php` |
| Certificates | `Student\DashboardController@certificates` → `student/certificates.blade.php` |
| My Courses | `EnrollmentController@index` → `enrollments/index.blade.php` |
| Course Detail (enrolled) | `EnrollmentController@show` → `enrollments/show.blade.php` |
| Lesson Viewer | `Student\LearningController@show` → `student/learning/show.blade.php` |
| Mark Lesson Complete | `Student\LearningController@complete` (POST) |
| Take Quiz | `Student\QuizController@take` → `student/learning/quiz.blade.php` |
| Quiz Result | `Student\QuizController@submit` → `student/learning/quiz_result.blade.php` |

### ❌ Missing in Laravel
| Feature | Original File | Description |
|---------|--------------|-------------|
| **Student Hub** | `public/student/index.php` | Central hub with quick stats, shortcuts |
| **Achievements** | `public/student/achievements.php` | Badge/achievement display |
| **Assignments** | `public/student/assignments.php` | View and submit assignments |
| **Schedule** | `public/student/schedule.php` | Weekly schedule view |
| **Quiz Results History** | `public/student/quiz-results.php` | All quiz attempts history |
| **Take Quiz (dedicated)** | `public/student/take-quiz.php` | Full quiz taking interface |
| **Help** | `public/student/help.php` | Student help/docs page |
| **Notes** | `public/api/lesson-notes.php` | Student lesson notes |
| **Onboarding Checklist** | `public/dashboard.php` | New student onboarding steps |
| **Weekly Activity Chart** | `public/dashboard.php` | Visual activity tracking |
| **Invoice Download** | Original invoices | Student invoice access |
| **Notifications** | `public/api/notifications.php` | Notification system |
| **Edit Profile** | `public/edit-profile.php` | Profile editing |
| **Profile** | `public/profile.php` | Public profile view |
| **Download Certificate** | `public/download-certificate.php` | Certificate download |
| **My Certificates** | `public/my-certificates.php` | Certificate list (old) |
| **My Payments** | `public/my-payments.php` | Payment history (old) |
| **My Courses** | `public/my-courses.php` | Course list (old) |

---

## 3. INSTRUCTOR DASHBOARD

### ✅ Implemented in Laravel
| Feature | Controller/View |
|---------|----------------|
| Dashboard | `Instructor\DashboardController@index` → `instructor/dashboard.blade.php` |
| Submissions | `Instructor\DashboardController@submissions` → `instructor/submissions.blade.php` |
| Analytics | `Instructor\DashboardController@analytics` → `instructor/analytics.blade.php` |
| Course List | `Instructor\CourseController@index` → `instructor/courses/index.blade.php` |
| Course Create | `Instructor\CourseController@create` → `instructor/courses/create.blade.php` |
| Course Edit | `Instructor\CourseController@edit` → `instructor/courses/edit.blade.php` |
| Course Show | `Instructor\CourseController@show` → `instructor/courses/show.blade.php` |
| Quiz List | `Instructor\CourseController` → `instructor/quizzes/index.blade.php` |
| Quiz Create | `Instructor\CourseController` → `instructor/quizzes/create.blade.php` |

### ❌ Missing in Laravel
| Feature | Original File | Description |
|---------|--------------|-------------|
| **Assignments Management** | `public/instructor/assignments.php` | Create/grade assignments |
| **Live Sessions** | `public/instructor/live-sessions.php` | Schedule/manage live sessions |
| **Students** | `public/instructor/students.php` | View enrolled students |
| **Quick Actions** | `public/instructor/quick-actions.php` | Quick action shortcuts |
| **Help** | `public/instructor/help.php` | Instructor help/docs |
| **Course Edit (detailed)** | `public/instructor/course-edit.php` | Detailed course editing |
| **Module Management** | `public/actions/instructor/module-*.php` | CRUD for course modules |
| **Lesson Management** | `public/actions/instructor/lesson-*.php` | CRUD for lessons |
| **Lesson Resources** | `public/instructor/courses/lesson-resources.php` | Manage lesson resources |
| **Bulk Upload** | `public/instructor/courses/bulk-upload.php` | Bulk content upload |
| **Templates** | `public/instructor/courses/templates.php` | Course templates |
| **Bulk Upload** | `public/instructor/courses/bulk-upload.php` | Bulk content import |
| **Assignment Grading** | `public/api/course-assignments/` | Grade submissions |
| **Student Analytics** | `public/instructor/students.php` | Per-student progress tracking |
| **Student Analytics** | Per-student progress tracking |

---

## 4. ADMIN DASHBOARD

### ✅ Implemented in Laravel
| Feature | Controller/View |
|---------|----------------|
| Dashboard | `Admin\DashboardController@index` → `admin/dashboard.blade.php` |
| Reports | `Admin\DashboardController@reports` → `admin/reports.blade.php` |
| Settings | `Admin\DashboardController@settings` → `admin/settings.blade.php` |
| User List | `Admin\UserController@index` → `admin/users/index.blade.php` |
| User Create | `Admin\UserController@create` → `admin/users/create.blade.php` |
| User Edit | `Admin\UserController@edit` → `admin/users/edit.blade.php` |
| User Show | `Admin\UserController@show` → `admin/users/show.blade.php` |
| Course List | `Admin\CourseController@index` → `admin/courses/index.blade.php` |
| Course Create | `Admin\CourseController@create` → `admin/courses/create.blade.php` |
| Course Edit | `Admin\CourseController@edit` → `admin/courses/edit.blade.php` |
| Course Show | `Admin\CourseController@show` → `admin/courses/show.blade.php` |
| Payment List | `Admin\PaymentController@index` → `admin/payments/index.blade.php` |
| Payment Create | `Admin\PaymentController@create` → `admin/payments/create.blade.php` |
| Payment Edit | `Admin\PaymentController@edit` → `admin/payments/edit.blade.php` |
| Payment Show | `Admin\PaymentController@show` → `admin/payments/show.blade.php` |

### ❌ Missing in Laravel
| Feature | Original File | Description |
|---------|--------------|-------------|
| **Announcements** | `public/admin/pages/announcements.php` | Manage announcements |
| **Company Profile** | `public/admin/pages/company-profile.php` | Institution profile settings |
| **Enrollments** | `public/admin/pages/enrollments.php` | Manage student enrollments |
| **Financials** | `public/admin/pages/financials.php` | Financial reports/management |
| **Modules** | `public/admin/pages/modules.php` | Cross-course module management |
| **Institution Photos** | `public/admin/institution-photos.php` | Manage campus photos |
| **Events** | `public/admin/events.php` | Manage events |
| **Help** | `public/admin/help.php` | Admin help/docs |
| **Handlers** | `public/admin/handlers/*.php` | AJAX handlers for admin |
| **Email Templates** | `public/api/settings.php` | Email template management |
| **System Logs** | `public/api/logs.php` | Activity logs |
| **Live Session Admin** | Scheduling and management |
| **Badges/Achievements** | Badge creation and assignment |
| **Course Reviews** | Review moderation |
| **FAQ Management** | FAQ CRUD (no table exists yet) |
| **Testimonial Management** | Approve/reject testimonials |
| **Team Members** | Staff management |
| **System Settings UI** | Settings management interface |
| **Admin Handlers** | `public/admin/handlers/*.php` | AJAX CRUD handlers (users, courses, modules, financials) |
| **Company Profile Update** | `public/actions/admin/company-profile-update.php` | Institution branding update |
| **Photo Upload** | `public/actions/admin/photo-upload.php` | Campus photo upload handler |
| **Event Create** | `public/actions/admin/event-create.php` | Event creation handler |

---

## 5. FINANCE DASHBOARD

### ✅ Implemented in Laravel
| Feature | Controller/View |
|---------|----------------|
| Dashboard | `Finance\DashboardController@index` → `finance/dashboard.blade.php` |
| Transactions | `Finance\DashboardController@transactions` → `finance/transactions.blade.php` |
| Invoices | `Finance\DashboardController@invoices` → `finance/invoices.blade.php` |

### ⚠️ Partially Implemented
| Feature | Status | Note |
|---------|--------|------|
| Payments Page | Route exists but view is `finance/index.blade.php` (may be placeholder) |

---

## 6. API ENDPOINTS

### ✅ Implemented in Laravel
| Endpoint | Controller |
|----------|-----------|
| Courses API | `Api\CourseController` |
| Enrollments API | `Api\EnrollmentController` |
| Progress API | `Api\ProgressController` |
| Certificates API | `Api\CertificateController` |
| Notifications API | `Api\NotificationController` |
| Quizzes API | `Api\QuizController` |

### ❌ Missing in Laravel
| Endpoint | Original File | Description |
|----------|--------------|-------------|
| **Auth API** | `public/api/auth.php` | Login/register API |
| **Announcements API** | `public/api/announcements.php` | CRUD announcements |
| **Assignment API** | `public/api/assignment.php` | Assignment operations |
| **Badges API** | `public/api/badges.php` | Badge operations |
| **Categories API** | `public/api/categories.php` | Course categories |
| **Course Assignments API** | `public/api/course-assignments.php` | Assignment management |
| **Discussions API** | `public/api/discussions.php` | Discussion forum |
| **Download Resource** | `public/api/download-resource.php` | File downloads |
| **Instructors API** | `public/api/instructors.php` | Instructor data |
| **Lenco Payment API** | `public/api/lenco-payment.php` | Payment initiation |
| **Lenco Webhook** | `Payment\LencoWebhookController@handle` | ✅ Already implemented |
| **Lesson Notes API** | `public/api/lesson-notes.php` | Student notes |
| **Lesson Progress API** | `public/api/lesson-progress.php` | Progress tracking |
| **Lesson Resources API** | `public/api/lesson-resources.php` | Resource management |
| **Lessons API** | `public/api/lessons.php` | Lesson data |
| **Live Sessions API** | `public/api/live-sessions.php` | Live session data |
| **Logs API** | `public/api/logs.php` | System logs |
| **Notes API** | `public/api/notes.php` | General notes |
| **Notifications Stream** | `public/api/notifications-stream.php` | SSE notifications |
| **Payment API** | `public/api/payment.php` | Payment operations |
| **Payment Callback** | `public/api/payment-callback.php` | Payment callbacks |
| **Settings API** | `public/api/settings.php` | System settings |
| **Switch Role** | `public/api/switch-role.php` | Role switching |
| **Transactions API** | `public/api/transactions.php` | Transaction data |
| **Users API** | `public/api/users.php` | User management |

---

## 7. ACTION HANDLERS

### ✅ Implemented in Laravel
| Action | Controller |
|--------|-----------|
| Submit Quiz | `Student\QuizController@submit` |
| Mark Lesson Complete | `Student\LearningController@complete` |

### ❌ Missing in Laravel
| Action | Original File | Description |
|--------|--------------|-------------|
| **Create Lesson** | `public/actions/instructor/lesson-create.php` |
| **Update Lesson** | `public/actions/instructor/lesson-update.php` |
| **Delete Lesson** | `public/actions/instructor/lesson-delete.php` |
| **Create Module** | `public/actions/instructor/module-create.php` |
| **Update Module** | `public/actions/instructor/module-update.php` |
| **Delete Module** | `public/actions/instructor/module-delete.php` |
| **Submit Assignment** | `public/actions/submit-assignment.php` |

---

## 8. PAYMENT SYSTEM

### ✅ Implemented in Laravel
| Feature | Status |
|---------|--------|
| Lenco Webhook | `Payment\LencoWebhookController` |
| Payment Model/CRUD (admin) | `Admin\PaymentController` |
| Payment records display | Student payments page |

### ❌ Missing in Laravel
| Feature | Original File | Description |
|---------|--------------|-------------|
| **Registration Fee Flow** | `public/registration-fee.php` | K150 mandatory fee before any enrollment |
| **30% Deposit Rule** | `Enrollment::create()` | 30% minimum deposit unlocks course content |
| **100% Certificate Rule** | `Lenco::processWebhook()` | 100% payment unlocks certificate download |
| **Lenco Payment Initiation** | `public/api/lenco-payment.php` | Start Lenco payment |
| **Lenco Checkout Page** | `public/lenco-checkout.php` | Checkout UI |
| **Payment Success Page** | `public/payment-success.php` |
| **Payment Failed Page** | `public/payment-failed.php` |
| **MTN Mobile Money** | Original supported |
| **Airtel Money** | Original supported |
| **Zamtel Money** | Original supported |
| **Bank Transfer** | Original supported |
| **Registration Fee** | `public/registration-fee.php` | Before enrollment |
| **Payment Plans** | Installment payments |

---

## 9. CERTIFICATE SYSTEM

### ✅ Implemented in Laravel
| Feature | Status |
|---------|--------|
| PDF Generation (TCPDF) | `CertificateService` + `certificates/pdf.blade.php` |
| Verification Page | `CertificateController@verify` |
| Download | `CertificateController@download` |
| Certificate List | Student certificates page |

### ❌ Missing in Laravel
| Feature | Description |
|---------|-------------|
| **Auto-Issue on Completion** | Automatically issue when course complete |
| **Certificate Preview** | Preview before download |
| **Certificate Settings** | Admin certificate configuration |

---

## 10. LIVE SESSIONS / JITSI

### ❌ Completely Missing in Laravel
| Feature | Original File | Description |
|---------|--------------|-------------|
| **Live Session Model** | ✅ Model exists but unused |
| **Live Session Routes** | `public/api/live-sessions.php` |
| **Live Session Controller** | None |
| **Jitsi Integration** | Embedded in original |
| **Session Scheduling** | Instructor can schedule |
| **Attendance Tracking** | `public/api/live-session-attendance.php` |
| **Session Reminders** | `cron/session-reminders.php` |

---

## 11. DISCUSSIONS / FORUM

### ❌ Completely Missing in Laravel
| Feature | Original File | Description |
|---------|--------------|-------------|
| **Discussion Model** | ✅ Model exists but unused |
| **Discussion Routes** | `public/api/discussions.php` |
| **Course Discussions** | `public/course-discussions.php` |
| **Discussion Replies** | Nested replies |
| **Best Answer** | Mark best answer |
| **Pinned/Locked** | Moderation features |

---

## 12. NOTIFICATIONS & EMAIL

### ✅ Implemented in Laravel
| Feature | Status |
|---------|--------|
| Password Reset Email | `emails/password-reset.blade.php` |
| PHPMailer Integration | `config/mail.php` |

### ❌ Missing in Laravel
| Feature | Original File | Description |
|---------|--------------|-------------|
| **Email Queue** | `cron/process-emails.php` | Background email processing |
| **Email Templates** | `public/api/settings.php` | Template management |
| **Bulk Email** | Send to all students |
| **Notification Stream** | `public/api/notifications-stream.php` | Real-time notifications |
| **Notification Center** | View all notifications |
| **SMS Notifications** | Via Africa's Talking or similar |

---

## 13. PRIORITY MATRIX

### 🔴 CRITICAL (Breaks Core Workflows)
| # | Gap | Impact |
|---|-----|--------|
| 1 | **Payment Flow** | Students cannot pay/enroll |
| 2 | **Assignment System** | No submission or grading |
| 3 | **Instructor Lesson/Module CRUD** | Cannot build course content |
| 4 | **Live Sessions** | No virtual classes |
| 5 | **Discussions** | No student interaction |
| 6 | **Email Verification** | Security/account verification |
| 7 | **Contact Form Handler** | Contact page doesn't work |

### 🟠 HIGH (Major Feature Gaps)
| # | Gap | Impact |
|---|-----|--------|
| 8 | **Admin Announcements** | Cannot communicate with users |
| 9 | **Admin Institution Photos** | Cannot manage campus gallery |
| 10 | **Admin Events Management** | Cannot manage events |
| 11 | **Admin Enrollments** | Cannot manage enrollments |
| 12 | **Admin Email Templates** | Cannot customize emails |
| 13 | **Student Achievements/Badges** | Gamification missing |
| 14 | **Student Schedule** | No weekly schedule view |
| 15 | **Student Notes** | Cannot take lesson notes |
| 16 | **Profile Edit** | Cannot update profile |
| 17 | **Course Reviews** | No review system |
| 18 | **Search Page** | No dedicated search |

### 🟡 MEDIUM (Nice to Have)
| # | Gap | Impact |
|---|-----|--------|
| 19 | **Privacy/Terms Pages** | Legal compliance |
| 20 | **Newsletter** | Marketing |
| 21 | **Certificate Auto-Issue** | Manual process required |
| 22 | **Instructor Bulk Upload** | Content import |
| 23 | **Instructor Templates** | Reusable content |
| 24 | **System Logs Viewer** | Debugging/audit |
| 25 | **Role Switching** | Multi-role users |
| 26 | **Help Pages** | Documentation |

---

## 14. DATA MODEL GAPS

### Tables That Exist But Have No UI
| Table | Laravel Model | Missing UI |
|-------|--------------|-----------|
| `announcements` | `Announcement` | Admin CRUD, student display |
| `discussions` | `Discussion` | Forum UI |
| `discussion_replies` | `DiscussionReply` | Reply UI |
| `live_sessions` | `LiveSession` | Scheduling, joining |
| `live_session_attendance` | `LiveSessionAttendance` | Attendance tracking |
| `badges` | `Badge` | Admin CRUD, student display |
| `email_templates` | `EmailTemplate` | Admin editor |
| `assignment_submissions` | `AssignmentSubmission` | Grading UI |
| `course_reviews` | `CourseReview` | Review form, display |
| `notifications` | `Notification` | Notification center |
| `messages` | `Message` | Messaging UI |
| `team_members` | `TeamMember` | About page display |
| `instructors` | `Instructor` | Instructor profiles |

### Tables Missing (No Model, No Migration)
| Table | Description |
|-------|-------------|
| `facilities` | Campus facilities |
| `faqs` | FAQ content |
| `testimonials` | Testimonial management |
| `contacts` | Contact form submissions |
| `newsletter_subscribers` | Newsletter signups |

---

## 15. RECOMMENDED IMPLEMENTATION ORDER

### Phase 1: Core Functionality (Critical)
1. Payment flow (registration fee + Lenco checkout + success/failure pages)
2. 30% deposit / 100% certificate business rules
3. Instructor lesson/module CRUD
4. Assignment submission + grading
5. Email verification
6. Contact form handler

### Phase 2: Student Experience (High)
6. Profile edit page
7. Student notes
8. Student schedule
9. Course review system
10. Search page

### Phase 3: Admin Tools (High)
11. Announcements management
12. Institution photos management
13. Events management
14. Enrollment management
15. Email template editor

### Phase 4: Engagement (Medium)
16. Discussions/forum
17. Live sessions (Jitsi)
18. Badges/achievements
19. Newsletter system
20. Certificate auto-issue
21. User profile extended fields (avatar, bio, NRC)
22. Invoice generation
23. Google Drive file storage

---

## 16. CRITICAL BUSINESS RULES TO PRESERVE

| # | Rule | Original Implementation |
|---|------|------------------------|
| 1 | **Registration Fee** | K150 mandatory before any course enrollment (`RegistrationFee::hasPaid()`) |
| 2 | **30% Deposit** | Minimum 30% payment unlocks course content access |
| 3 | **100% for Certificates** | Full payment required; `certificate_blocked = 0` only when balance <= 0 |
| 4 | **Auto-Enroll Free Courses** | Free courses auto-complete enrollment without payment |
| 5 | **Payment Plan Auto-Creation** | `enrollment_payment_plans` created automatically on enrollment |
| 6 | **Certificate Number Format** | `EDUTRACK-YYYYMM-00001` with advisory MySQL lock |
| 7 | **Rate Limiting** | Login: 5 attempts/15 min; Email: 10/min, 100/hour |
| 8 | **Multi-Role Switching** | Users with multiple roles can switch via `$_SESSION['active_role']` |
| 9 | **File Upload Security** | Max 50MB, whitelist extensions, `.htaccess` blocks PHP execution |
| 10 | **CSRF on All Forms** | Every form requires CSRF token validation |

---

*Generated by gap analysis comparing original PHP system and Laravel migration.*
