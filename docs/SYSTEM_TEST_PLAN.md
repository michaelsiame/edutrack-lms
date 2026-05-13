# Edutrack LMS - Comprehensive System Test Plan

## Overview
This test plan covers all major functionality of the Edutrack LMS Laravel application. Run these tests locally against `http://127.0.0.1:8000` (or your configured `APP_URL`).

## Prerequisites
1. Laravel dev server running: `cd laravel && php artisan serve`
2. Database migrated and seeded with test data
3. `.env` configured with actual API keys (email, Lenco, Google OAuth)
4. Test users exist in database:
   - **Admin**: `testadmin@edutrackzambia.com` / `TestPass123!`
   - **Instructor**: `testinstructor@edutrackzambia.com` / `TestPass123!`
   - **Student**: `teststudent@edutrackzambia.com` / `TestPass123!`
5. At least one published course exists with modules, lessons, and a price > 0
6. At least one free course exists for auto-enrollment testing

---

## Test Data Setup (if missing)

```bash
cd laravel
php artisan tinker

# Create test users if missing
\App\Models\User::firstOrCreate(['email'=>'testadmin@edutrackzambia.com'], ['name'=>'Test Admin','password'=>bcrypt('TestPass123!'),'status'=>'active']);
\App\Models\User::firstOrCreate(['email'=>'testinstructor@edutrackzambia.com'], ['name'=>'Test Instructor','password'=>bcrypt('TestPass123!'),'status'=>'active']);
\App\Models\User::firstOrCreate(['email'=>'teststudent@edutrackzambia.com'], ['name'=>'Test Student','password'=>bcrypt('TestPass123!'),'status'=>'active']);

# Assign roles
\App\Models\UserRole::firstOrCreate(['user_id'=>1,'role_id'=>1]); # Admin
\App\Models\UserRole::firstOrCreate(['user_id'=>2,'role_id'=>2]); # Instructor
\App\Models\UserRole::firstOrCreate(['user_id'=>3,'role_id'=>4]); # Student

# Ensure at least one published course exists
if (!\App\Models\Course::where('status','published')->exists()) {
    \App\Models\Course::create(['title'=>'Test Course','slug'=>'test-course','price'=>150.00,'status'=>'published','instructor_id'=>1,'category_id'=>1]);
}
```

---

## 1. PUBLIC PAGES

### 1.1 Homepage
- **URL**: `GET /`
- **Expected**: Loads without errors. Shows stats (students, courses, instructors), featured courses, hero slides, testimonials, upcoming events.
- **Verify**: No 500 errors. Dynamic data populated.

### 1.2 About Page
- **URL**: `GET /about`
- **Expected**: Loads with institution info, team members, mission/vision.

### 1.3 Courses Listing
- **URL**: `GET /courses`
- **Expected**: Lists all published courses with pagination. Filter by category works.

### 1.4 Course Detail
- **URL**: `GET /courses/{slug}` (e.g., `/courses/cybersecurity-fundamentals`)
- **Expected**: Shows course info, modules/lessons outline, instructor bio, reviews, enrollment button.
- **Verify**: Pricing displays in ZMW with `K` symbol.

### 1.5 Contact Page
- **URL**: `GET /contact`
- **Expected**: Contact form with name, email, phone, message.
- **Action**: Submit valid form.
- **Expected**: Success message. Email queued (check `storage/logs/laravel.log` or email queue table).

### 1.6 Campus Page
- **URL**: `GET /campus`
- **Expected**: Shows institution photos, facilities, address.

### 1.7 Testimonials Page
- **URL**: `GET /testimonials`
- **Expected**: Displays approved testimonials.

### 1.8 Events Page
- **URL**: `GET /events`
- **Expected**: Lists upcoming and past events.

### 1.9 FAQ Page
- **URL**: `GET /faq`
- **Expected**: FAQ accordion works.

### 1.10 Search
- **URL**: `GET /search?q=computer`
- **Expected**: Returns matching courses.

### 1.11 Certificate Verification (Public)
- **URL**: `GET /certificates/verify`
- **Expected**: Verification form loads.
- **Action**: Enter a valid certificate number.
- **Expected**: Shows certificate details if valid.

---

## 2. AUTHENTICATION

### 2.1 Student Registration
- **URL**: `GET /register`
- **Action**: Fill form with new email, name, phone, password (min 8 chars, uppercase, number).
- **Expected**: Account created, logged in, redirected to student dashboard.
- **Verify**: Email verification sent (check `email_queue` table or logs).

### 2.2 Login (Student)
- **URL**: `POST /login`
- **Credentials**: `teststudent@edutrackzambia.com` / `TestPass123!`
- **Expected**: Redirect to `/student/dashboard`.

### 2.3 Login (Instructor)
- **URL**: `POST /login`
- **Credentials**: `testinstructor@edutrackzambia.com` / `TestPass123!`
- **Expected**: Redirect to `/instructor/dashboard`.

### 2.4 Login (Admin)
- **URL**: `POST /login`
- **Credentials**: `testadmin@edutrackzambia.com` / `TestPass123!`
- **Expected**: Redirect to `/admin/dashboard`.

### 2.5 Google OAuth
- **URL**: `GET /auth/google`
- **Expected**: Redirects to Google consent screen.
- **Note**: Requires valid Google OAuth credentials in `.env`. Test on local with configured redirect URI.

### 2.6 Password Reset
- **URL**: `GET /forgot-password`
- **Action**: Enter registered email.
- **Expected**: "Password reset link sent" message. Check email queue for reset token.

### 2.7 Logout
- **URL**: `POST /logout`
- **Expected**: Session cleared, redirected to home.

---

## 3. STUDENT EXPERIENCE

### 3.1 Student Dashboard
- **URL**: `GET /student/dashboard`
- **Expected**: Shows enrolled courses, progress, upcoming schedule, recent activity.

### 3.2 My Courses (Enrollments)
- **URL**: `GET /student/enrollments`
- **Expected**: Lists enrolled courses with status badges (Enrolled, In Progress, Completed).

### 3.3 Course Enrollment (Paid)
- **URL**: `GET /courses/{slug}`
- **Action**: Click "Enroll" on a paid course.
- **Expected**: Redirect to checkout page showing K150 registration fee + course fee.
- **Verify**: Registration fee is mandatory for first enrollment.

### 3.4 Course Enrollment (Free)
- **URL**: `GET /courses/{slug}`
- **Action**: Click "Enroll" on a free course.
- **Expected**: Auto-completes enrollment without payment. Status = "Enrolled".

### 3.5 Payment Flow - Lenco
- **URL**: `POST /checkout` with `payment_method=lenco`
- **Expected**: Creates Lenco transaction, redirects to Lenco checkout URL.
- **Verify**: Record created in `lenco_transactions` table with `pending` status.

### 3.6 Payment Flow - Bank Transfer
- **URL**: `POST /checkout` with `payment_method=bank_transfer`
- **Expected**: Shows bank account details (Zanaco, FNB) with instructions.

### 3.7 Learning Page
- **URL**: `GET /student/courses/{course}/learn`
- **Expected**: Shows course content sidebar with modules/lessons. Video embeds work.
- **Verify**: Progress tracking updates as lessons are marked complete.

### 3.8 Lesson Notes
- **URL**: `POST /student/notes` (AJAX)
- **Action**: Add note for a lesson.
- **Expected**: Note saved, appears in notes list.

### 3.9 Quiz Submission
- **URL**: `POST /student/quizzes/{quiz}/submit`
- **Expected**: Calculates score, stores attempt, shows result.

### 3.10 Assignment Submission
- **URL**: `POST /student/assignments/{assignment}/submit`
- **Action**: Upload file or enter text.
- **Expected**: Submission stored, status updated.

### 3.11 Discussion Forum
- **URL**: `GET /student/courses/{course}/discussions`
- **Expected**: Lists discussions for enrolled course.
- **Action**: Create new discussion.
- **Expected**: Discussion created, appears in list.
- **Action**: Reply to discussion.
- **Expected**: Reply stored, `reply_count` incremented.

### 3.12 Live Sessions (Student)
- **URL**: `GET /student/courses/{course}/live-sessions`
- **Expected**: Lists scheduled/live sessions for course.
- **Action**: Click "Join" on upcoming session.
- **Expected**: Opens Jitsi Meet room in new tab.

### 3.13 Schedule
- **URL**: `GET /student/schedule`
- **Expected**: Weekly calendar showing lessons, live sessions, assignment deadlines.

### 3.14 Progress
- **URL**: `GET /student/progress`
- **Expected**: Shows overall progress across all enrolled courses with percentages.

### 3.15 Certificates
- **URL**: `GET /student/certificates`
- **Expected**: Lists earned certificates.
- **Action**: Click "Download".
- **Expected**: PDF generated with TEVETA branding, unique certificate number.
- **Verify**: Certificate NOT auto-issued. Must be manually issued by admin/instructor.

### 3.16 Transcript
- **URL**: `GET /student/transcript`
- **Action**: Click "Download Transcript".
- **Expected**: PDF generated with course history, grades, cumulative stats.

### 3.17 Payments
- **URL**: `GET /student/payments`
- **Expected**: Lists all payment history with amounts, methods, statuses.

### 3.18 Notes
- **URL**: `GET /student/notes`
- **Expected**: All lesson notes organized by course.

### 3.19 Achievements/Badges
- **URL**: `GET /student/achievements`
- **Expected**: Displays earned badges with criteria.

### 3.20 Reviews
- **URL**: `POST /courses/{course}/reviews`
- **Action**: Submit rating (1-5) and comment.
- **Expected**: Review stored, appears on course page.

### 3.21 Profile Edit
- **URL**: `GET /profile/edit`
- **Action**: Update name, phone, avatar.
- **Expected**: Profile updated, changes reflected immediately.

---

## 4. INSTRUCTOR EXPERIENCE

### 4.1 Instructor Dashboard
- **URL**: `GET /instructor/dashboard`
- **Expected**: Shows courses, student count, revenue, recent submissions.

### 4.2 Course Management
- **URL**: `GET /instructor/courses`
- **Expected**: Lists instructor's courses.

### 4.3 Create Course
- **URL**: `POST /instructor/courses`
- **Action**: Create course with title, description, price, category, thumbnail.
- **Expected**: Course created with `draft` status.

### 4.4 Module CRUD
- **URL**: `POST /instructor/courses/{course}/modules`
- **Action**: Create module with title and display order.
- **Expected**: Module created, appears in course outline.

### 4.5 Lesson CRUD
- **URL**: `POST /instructor/courses/{course}/modules/{module}/lessons`
- **Action**: Create lesson (Video, Reading, Quiz, Assignment, Live Session, Download).
- **Expected**: Lesson created, appears in module.
- **Verify**: Video URL accepts YouTube/Vimeo/BunnyCDN.

### 4.6 Quiz Creation
- **URL**: `POST /instructor/courses/{course}/quizzes`
- **Action**: Create quiz with multiple choice, true/false, short answer questions.
- **Expected**: Quiz and questions stored correctly.

### 4.7 Assignment Creation
- **URL**: `POST /instructor/courses/{course}/assignments`
- **Action**: Create assignment with title, description, due date, max points.
- **Expected**: Assignment created, students can see it.

### 4.8 Grade Submissions
- **URL**: `GET /instructor/assignments/{assignment}/submissions`
- **Action**: View submissions, enter grade and feedback.
- **Expected**: Grade stored, student notified.

### 4.9 Live Sessions (Instructor)
- **URL**: `GET /instructor/courses/{course}/live-sessions`
- **Expected**: Lists sessions for course.
- **Action**: Schedule new session with lesson, room ID, start/end time.
- **Expected**: Session created, appears in student view.

### 4.10 Analytics
- **URL**: `GET /instructor/analytics`
- **Expected**: Shows course enrollment trends, revenue, completion rates.

---

## 5. ADMIN EXPERIENCE

### 5.1 Admin Dashboard
- **URL**: `GET /admin/dashboard`
- **Expected**: Shows system stats, recent enrollments, payments, user counts.

### 5.2 User Management
- **URL**: `GET /admin/users`
- **Expected**: Paginated user list with roles.
- **Action**: Edit user role, deactivate account.
- **Expected**: Changes saved, access updated.

### 5.3 Course Management
- **URL**: `GET /admin/courses`
- **Expected**: All courses listed.
- **Action**: Approve/publish course, feature course.
- **Expected**: Status updates reflected on public site.

### 5.4 Enrollment Management
- **URL**: `GET /admin/enrollments`
- **Expected**: All enrollments listed.
- **Action**: Manually enroll student, change status.
- **Expected**: Enrollment updated.

### 5.5 Payment Management
- **URL**: `GET /admin/payments`
- **Expected**: All payments listed.
- **Action**: Verify bank transfer payment.
- **Expected**: Status updated to `completed`, student access unlocked.

### 5.6 Finance Dashboard
- **URL**: `GET /finance/dashboard`
- **Expected**: Revenue charts, pending payments, reconciliation.

### 5.7 Announcements
- **URL**: `GET /admin/announcements`
- **Action**: Create announcement (global or course-specific).
- **Expected**: Announcement visible on relevant dashboards.

### 5.8 Events
- **URL**: `GET /admin/events`
- **Action**: Create event with title, date, description, featured flag.
- **Expected**: Event appears on public events page.

### 5.9 Institution Photos
- **URL**: `GET /admin/photos`
- **Action**: Upload campus/facility photos.
- **Expected**: Photos appear on campus page.

### 5.10 Email Templates
- **URL**: `GET /admin/templates`
- **Action**: Edit email templates (welcome, enrollment, payment, certificate).
- **Expected**: Template saved, used in future emails.

### 5.11 Badges
- **URL**: `GET /admin/badges`
- **Action**: Create badge with icon, criteria, points.
- **Expected**: Badge available for achievement assignment.

### 5.12 Newsletter
- **URL**: `GET /admin/newsletter`
- **Expected**: Lists subscribers.
- **Verify**: Public newsletter signup works (`POST /newsletter/subscribe`).

### 5.13 Reports
- **URL**: `GET /admin/reports`
- **Expected**: Enrollment reports, revenue reports, student progress exports.

### 5.14 Settings
- **URL**: `GET /admin/settings`
- **Action**: Update site info, currency, contact details.
- **Expected**: Changes reflected across site.

---

## 6. PAYMENT INTEGRATION

### 6.1 Lenco Webhook
- **URL**: `POST /lenco/webhook`
- **Headers**: `X-Lenco-Signature: {valid_hmac}`
- **Payload**: Valid payment success payload.
- **Expected**: Transaction updated, enrollment status updated (30% deposit = `In Progress`, 100% = `Completed`).
- **Verify**: Webhook logged in `lenco_webhook_logs`.

### 6.2 Lenco Webhook Invalid Signature
- **URL**: `POST /lenco/webhook`
- **Headers**: `X-Lenco-Signature: invalid`
- **Expected**: 403 response, transaction NOT updated.

### 6.3 Payment Plan (30% Deposit)
- **Action**: Pay 30% of course fee.
- **Expected**: Enrollment status = `In Progress`. Student can access content.
- **Verify**: `certificate_blocked = true`.

### 6.4 Payment Plan (Full Payment)
- **Action**: Pay 100% of course fee.
- **Expected**: Enrollment status = `Completed`. `certificate_blocked = false`.

---

## 7. EMAIL SYSTEM

### 7.1 Email Queue Processing
- **Command**: `php artisan email:send` or check cron job
- **Expected**: Queued emails sent via Gmail SMTP.
- **Verify**: No errors in `storage/logs/laravel.log`.

### 7.2 Welcome Email
- **Trigger**: New user registration.
- **Expected**: Welcome email sent to user.

### 7.3 Enrollment Confirmation
- **Trigger**: Successful enrollment.
- **Expected**: Email with course details sent.

### 7.4 Payment Receipt
- **Trigger**: Successful payment.
- **Expected**: Email with payment details sent.

### 7.5 Certificate Issued
- **Trigger**: Admin manually issues certificate.
- **Expected**: Email with download link sent.

---

## 8. SECURITY

### 8.1 CSRF Protection
- **Action**: Submit any form without CSRF token.
- **Expected**: 419 Page Expired error.
- **Verify**: `newsletter/subscribe` is exempted (should work without token).

### 8.2 Rate Limiting
- **Action**: Attempt login 6+ times with wrong password.
- **Expected**: Account locked for 15 minutes.

### 8.3 Role-Based Access
- **Action**: Access `/admin/dashboard` as student.
- **Expected**: 403 Forbidden.
- **Action**: Access `/instructor/dashboard` as student.
- **Expected**: 403 Forbidden.

### 8.4 Enrollment Middleware
- **Action**: Access `/student/courses/{course}/discussions` without enrollment.
- **Expected**: 403 Forbidden.

### 8.5 XSS Prevention
- **Action**: Submit form with `<script>alert('xss')</script>`.
- **Expected**: Script escaped in output.

### 8.6 SQL Injection
- **Action**: Search with `' OR 1=1 --`.
- **Expected**: No error, treated as literal search term.

---

## 9. API ENDPOINTS

### 9.1 Courses API
- **URL**: `GET /api/courses`
- **Expected**: JSON list of published courses.

### 9.2 Enrollment API
- **URL**: `POST /api/enrollments` (with auth token)
- **Expected**: Creates enrollment, returns JSON response.

### 9.3 Progress API
- **URL**: `GET /api/progress`
- **Expected**: Returns student progress data.

### 9.4 Quiz API
- **URL**: `POST /api/quizzes/{quiz}/submit`
- **Expected**: Returns score and feedback.

---

## 10. EDGE CASES & BUG REGRESSIONS

### 10.1 Course with 0 Modules/Lessons
- **URL**: `GET /courses/{slug}` for course with no modules.
- **Expected**: Page loads without errors, shows "No content yet" message.

### 10.2 Empty Search Results
- **URL**: `GET /search?q=xyznonexistent`
- **Expected**: "No results found" message.

### 10.3 Invalid Course Slug
- **URL**: `GET /courses/invalid-slug-12345`
- **Expected**: 404 page.

### 10.4 Expired Live Session
- **URL**: `GET /student/courses/{course}/live-sessions` with past session.
- **Expected**: Session marked "Completed", join button hidden.

### 10.5 Double Enrollment
- **Action**: Enroll in same course twice.
- **Expected**: Error message "Already enrolled" or ignored gracefully.

### 10.6 Password Reset Expired Token
- **Action**: Click password reset link after 24+ hours.
- **Expected**: "Token expired" message.

---

## Expected Test Results Summary

| Category | Tests | Must Pass |
|----------|-------|-----------|
| Public Pages | 11 | 11 |
| Authentication | 7 | 7 |
| Student Experience | 21 | 21 |
| Instructor Experience | 10 | 10 |
| Admin Experience | 14 | 14 |
| Payment Integration | 4 | 4 |
| Email System | 5 | 5 |
| Security | 6 | 6 |
| API Endpoints | 4 | 4 |
| Edge Cases | 6 | 6 |
| **Total** | **88** | **88** |

---

## Test Completion Checklist

- [ ] All public pages load without 500 errors
- [ ] Login works for all 3 roles (Admin, Instructor, Student)
- [ ] Registration creates account and sends email
- [ ] Student can enroll in free course without payment
- [ ] Student can enroll in paid course with Lenco checkout
- [ ] Payment webhook updates enrollment status correctly
- [ ] 30% deposit unlocks content (`In Progress`)
- [ ] 100% payment unlocks certificate (`Completed`, `certificate_blocked=false`)
- [ ] Certificate is NOT auto-issued (manual only)
- [ ] Instructor can CRUD courses, modules, lessons, quizzes, assignments
- [ ] Instructor can schedule live sessions
- [ ] Student can join live sessions (Jitsi)
- [ ] Discussion forum works (create, reply, nested replies)
- [ ] Badges display on student achievements page
- [ ] Newsletter signup works from public site
- [ ] Admin can manage all entities (users, courses, payments, announcements)
- [ ] Email templates editable by admin
- [ ] All emails send successfully via Gmail SMTP
- [ ] CSRF protection active on all forms
- [ ] Role-based access control prevents unauthorized access
- [ ] TEVETA references removed from all public pages (only on certificate PDF)
