# EduTrack LMS Database Schema Analysis Report

**Generated:** 2026-05-20 | **Branch:** laravel-migration | **Engine:** MariaDB 11.8.6\n
---

## 1. Executive Summary

The EduTrack LMS database is a production-grade MariaDB schema containing **55+ tables** covering the full lifecycle of a vocational training institution.\n
### Domain Breakdown

| Domain | Tables | Purpose |
|--------|--------|---------|
| Identity & Access | 7 | Users, roles, profiles, sessions, authentication |
| Course Content | 8 | Courses, categories, modules, lessons, resources, notes |
| Enrollment & Progress | 4 | Enrollments, lesson progress, payment plans, balances |
| Assessment | 8 | Quizzes, questions, attempts, answers, assignments, submissions |
| Financial | 7 | Payments, transactions, Lenco gateway, registration fees, methods |
| Communication | 6 | Notifications, announcements, discussions, messages, email queue |
| Institution | 8 | Certificates, events, team, testimonials, photos, slides, badges, contacts |
| System | 4 | Settings, activity logs, rate limits, migrations log |

### Key Business Rules Enforced in Schema

- **30% minimum payment** unlocks course access (enrollment_status -> In Progress)\n- **100% full payment** unblocks certificate issuance (certificate_blocked -> 0)\n- **Registration fee** (ZMW 150) required before enrollment (configurable)\n- **Certificate auto-generation** upon course completion (if not blocked)\n- **Unique certificate numbers** with public verification codes\n

---

## 2. Complete Table Inventory

### 2.1 Identity & Access Domain

#### users -- Core User Accounts (90 records)
**Columns:** id(PK,AI), username(UQ), email(UQ), google_id(UQ), password_hash, first_name, last_name, phone, avatar_url, status(enum:active/inactive/suspended/pending), email_verification_token, email_verification_expires, email_verified, last_login, last_login_ip, failed_login_attempts, account_locked_until, created_at, updated_at.\n**Indexes:** PK, UK_email, UK_username, UK_google_id, idx_status, idx_created_at. **FK:** None (root table).\n
#### roles -- Role Definitions (6 records)
**Columns:** id(PK,AI), role_name, description, permissions(JSON,CHECK), created_at.\n| ID | Name | Key Permissions |
|----|------|-----------------|\n| 1 | Super Admin | {all: true} |
| 2 | Admin | users CRUD, courses CRUD, reports read |
| 3 | Instructor | courses CRU, students read, grades CU |
| 4 | Student | courses read/enroll, assignments submit, quizzes take |
| 5 | Content Creator | courses/content CRU |
| 6 | Finance | payments CRU, students/enrollments read, reports read |

#### user_roles -- Role Assignments (118 records)
**Columns:** id(PK,AI), user_id(FK->users), role_id(FK->roles), assigned_at, assigned_by.\n**Indexes:** PK, UK(user_id,role_id), idx_user_id, idx_role_id.\n**ANOMALY:** Entry ID 30 has user_id=0 (orphaned assignment).\n
#### students -- Student Profile Extension (84 records)
**Columns:** id(PK,AI), user_id(UQ,FK->users,1:1), date_of_birth, gender(enum), address, city, country, postal_code, enrollment_date, total_courses_enrolled, total_courses_completed, total_certificates, created_at, updated_at.\n**ANOMALY:** Many imported students have NULL demographic data.\n
#### instructors -- Instructor Profile Extension (12 records)
**Columns:** id(PK,AI), user_id(UQ,FK->users,1:1), bio, specialization, years_experience, rating, total_students, total_courses, is_verified, created_at, updated_at.\n
#### user_profiles -- Extended User Profile (103 records)
**Columns:** id(PK,AI), user_id(FK->users), bio, phone, date_of_birth, gender, address, city, country, postal_code, avatar_url, avatar(DUPLICATE!), province, nrc_number, education_level, occupation, linkedin_url, facebook_url, twitter_url, created_at, updated_at.\n**Indexes:** PK, idx_user_id, idx_nrc_number.\n**ISSUE:** avatar_url and avatar columns are duplicates with overlapping usage.\n
#### user_sessions -- Active Session Tracking (1 record)
**Columns:** id(PK,AI), user_id(FK->users), session_token, ip_address, user_agent, expires_at, created_at, updated_at.\n
#### remember_tokens -- Remember Me Tokens
**Columns:** id(PK,AI), user_id(FK->users), token(UQ), expires_at, created_at.\n

### 2.2 Course Content Domain

#### course_categories -- Course Taxonomy (12 records)
**Columns:** id(PK,AI), name, description, icon, color, display_order, is_active, created_at, updated_at.\n**CRITICAL:** Duplicate Cybersecurity entries (IDs 7, 8, 12).\n
#### courses -- Course Catalog (35 records)
**Columns:** id(PK,AI), title, slug(UQ), description, short_description, category_id(FK), instructor_id(FK), thumbnail_url, price(decimal), duration_weeks, level(enum:Beginner/Intermediate/Advanced/All Levels), status(enum:draft/published/archived), is_featured, prerequisites, learning_objectives, certification_type, max_students, rating, total_reviews, total_enrolled, created_at, updated_at.\n**Indexes:** PK, UK_slug, idx_category_id, idx_instructor_id, idx_status, idx(status,is_featured).\n**FK:** fk_courses_category, fk_courses_instructor.\n
#### course_instructors -- Multi-Instructor Support (25 records)
**Columns:** id(PK,AI), course_id(FK->courses), instructor_id(FK->instructors), role(enum:Lead/Assistant/Guest/Mentor), created_at, updated_at.\n
#### modules -- Course Modules (42 records)
**Columns:** id(PK,AI), course_id(FK->courses), title, description, display_order, unlock_date, is_published, created_at, updated_at.\n**Indexes:** PK, idx_course_id.\n
#### lessons -- Individual Lessons (120 records)
**Columns:** id(PK,AI), module_id(FK->modules), title, content(longtext), lesson_type(enum:Video/Reading/Quiz/Assignment/Live Session/Download), video_url, duration_minutes, display_order, is_preview, is_mandatory, points, created_at, updated_at.\n**Indexes:** PK, idx_module_id.\n
#### lesson_resources -- Lesson Attachments (29 records)
**Columns:** id(PK,AI), lesson_id(FK->lessons), resource_type(enum:Document/Video/Audio/Link/External), title, file_url, file_size, display_order, created_at, updated_at.\n
#### lesson_notes -- Instructor Lesson Notes
**Columns:** id(PK,AI), lesson_id(FK->lessons), instructor_id(FK->instructors), notes(longtext), created_at, updated_at.\n

### 2.3 Enrollment & Progress Domain

#### enrollments -- Student Course Enrollments (51 records)
**Columns:** id(PK,AI), user_id(FK->users), student_id(FK->students), course_id(FK->courses), progress(decimal), final_grade(decimal), enrollment_status(enum:Enrolled/In Progress/Completed), payment_status(enum:Pending/Completed/Failed/Refunded/Cancelled), amount_paid(decimal), completion_date, certificate_issued, certificate_blocked, total_time_spent, created_at, updated_at.\n**Indexes:** PK, UK(user_id,course_id), idx_student_id, idx_course_id, idx_enrollment_status, idx(user_id,enrollment_status), idx(course_id,user_id).\n**FK:** fk_enroll_user, fk_enroll_student, fk_enroll_course.\n
#### lesson_progress -- Per-Lesson Progress Tracking (201 records)
**Columns:** id(PK,AI), enrollment_id(FK->enrollments), lesson_id(FK->lessons), user_id, status(enum:Not Started/In Progress/Completed), progress_percentage(decimal), time_spent_minutes, started_at, completed_at, last_accessed, created_at, updated_at.\n**Indexes:** PK, idx_enrollment_id, idx_lesson_id, idx_status, idx(enrollment_id,status), idx_last_accessed, idx_user_id, idx(enrollment_id,lesson_id), idx(user_id,lesson_id).\n
#### enrollment_payment_plans -- Installment Payment Plans (51 records)
**Columns:** id(PK,AI), enrollment_id(UQ,FK->enrollments), user_id, course_id, total_fee(decimal), total_paid(decimal), payment_status(enum:pending/partial/completed/overdue), due_date, created_at, updated_at.\n**Indexes:** PK, UK_enrollment_id, idx_user_id, idx_course_id.\n

### 2.4 Assessment Domain

#### quizzes -- Quiz Definitions (24 records)
**Columns:** id(PK,AI), course_id(FK->courses), lesson_id, title, description, quiz_type(enum:Practice/Graded/Final Exam/Midterm), time_limit_minutes, max_attempts, passing_score(decimal), randomize_questions, show_correct_answers, available_from, available_until, is_published, created_at, updated_at.\n**Indexes:** PK, idx_course_id, idx_is_published.\n
#### questions -- Reusable Question Bank (71 records)
**Columns:** question_id(PK,AI), question_type(enum:Multiple Choice/True/False/Short Answer/Essay/Fill in Blank), question_text, points(decimal), explanation, created_at, updated_at.\n
#### question_options -- Question Answer Options (278 records)
**Columns:** option_id(PK,AI), question_id(FK->questions), option_text, is_correct, display_order, created_at.\n
#### quiz_questions -- Quiz-to-Question Pivot (65 records)
**Columns:** quiz_question_id(PK,AI), quiz_id(FK->quizzes), question_id(FK->questions), display_order, points_override.\n**Indexes:** PK, UK(quiz_id,question_id), idx_question_id.\n
#### quiz_question_options -- Quiz-Specific Question Options (278 records)\n**Columns:** id(PK,AI), question_id(FK->questions), option_text, is_correct, display_order, created_at.\n**CRITICAL:** This table duplicates question_options. Both tables have identical structure and data. The QuizQuestionOption model exists but it is unclear which table is authoritative.\n
#### quiz_attempts -- Student Quiz Attempts (4 records)
**Columns:** id(PK,AI), quiz_id(FK->quizzes), student_id(FK->students), attempt_number, started_at, submitted_at, completed_at, score(decimal), status(enum:In Progress/Submitted/Graded/Abandoned), time_spent_minutes, ip_address.\n**Indexes:** PK, idx_quiz_id, idx_student_id, idx(quiz_id,student_id), idx_status.\n
#### quiz_answers -- Individual Answer Records
**Columns:** answer_id(PK,AI), attempt_id(FK->quiz_attempts), question_id(FK->questions), selected_option_id, answer_text, is_correct, points_earned(decimal), answered_at.\n**Indexes:** PK, idx_question_id, idx(attempt_id,question_id).\n
#### assignments -- Assignment Definitions (9 records)
**Columns:** id(PK,AI), course_id(FK->courses), lesson_id, title, description, max_points(decimal), passing_points(decimal), due_date, allow_late_submission, late_penalty_percent(decimal), created_at, updated_at.\n**Indexes:** PK, idx_course_id, idx_due_date.\n
#### assignment_submissions -- Student Submissions (63 records)
**Columns:** id(PK,AI), assignment_id(FK->assignments), student_id(FK->students), submission_text(longtext), file_url, status(enum:Submitted/Graded/Returned/Late), points_earned(decimal), feedback, graded_by, graded_at, is_late, created_at, updated_at.\n**Indexes:** PK, idx_assignment_id, idx_student_id, idx(assignment_id,student_id), idx_status.\n

### 2.5 Financial Domain

#### payment_methods -- Available Payment Methods (6 records)
**Columns:** payment_method_id(PK,AI), method_name, description, is_active, created_at, updated_at.\nMethods: Credit Card, Mobile Money, Bank Transfer, PayPal, Cash, Lenco Bank Transfer.\n
#### payments -- Payment Ledger (23 records)
**Columns:** payment_id(PK,AI), student_id(FK->students), course_id(FK->courses), enrollment_id(FK->enrollments), payment_plan_id, amount(decimal), payment_method_id(FK->payment_methods), payment_type(enum:registration/course_fee/partial_payment), recorded_by, payment_status(enum:Pending/Completed/Failed/Refunded/Cancelled), transaction_id(UQ), phone_number, payment_date, created_at, updated_at.\n**Indexes:** PK, UK_transaction_id, idx_transaction_id, idx_student_id, idx_course_id, idx_enrollment_id, idx_payment_method_id, idx_payment_plan_id, idx_payment_status.\n
#### transactions -- Transaction Detail Log (10 records)
**Columns:** transaction_id(PK,AI), payment_id(FK->payments), transaction_type(enum:Payment/Refund/Chargeback/Fee), amount(decimal), currency(VARCHAR(3), DEFAULT USD), gateway_response, processed_at.\n**Indexes:** PK, idx_payment_id, idx_transaction_type.\n**ISSUE:** Default currency is USD but all operations use ZMW.\n
#### lenco_transactions -- Lenco Payment Gateway Records
**Columns:** id(PK,AI), user_id(FK->users), enrollment_id(FK->enrollments), course_id(FK->courses), reference(UQ), lenco_collection_id, amount(decimal), currency(DEFAULT ZMW), status(enum:pending/completed/failed/expired/refunded), virtual_account_number, virtual_account_name, bank_name, phone, expires_at, paid_at, metadata(JSON), created_at, updated_at.\n**Indexes:** PK, UK_reference, idx_user_id, idx_enrollment_id, idx_course_id, idx_status, idx_virtual_account_number, idx_expires_at.\n
#### lenco_collections -- Lenco Virtual Account Collections
**Columns:** id(PK,AI), user_id, reference(UQ), lenco_collection_id(UQ), amount(decimal), currency(DEFAULT ZMW), status(enum:pending/completed/failed), phone, metadata(JSON), created_at, updated_at.\n**Indexes:** PK, UK_reference, UK_lenco_collection_id, idx_user_id, idx_status, idx_phone.\n**ISSUE:** No Eloquent model found for this table.\n
#### lenco_webhook_logs -- Webhook Audit Trail (156 records)
**Columns:** id(PK,AI), event_type, payload(longtext), signature, processed, error_message, created_at.\n**Indexes:** PK, idx_event_type, idx_processed, idx_created_at.\n**CRITICAL:** 150+ entries have error_message = Invalid signature (May 2026).\n
#### registration_fees -- One-Time Registration Payments (20 records)
**Columns:** id(PK,AI), user_id, student_id, amount(DEFAULT 150.00), currency(DEFAULT ZMW), payment_status(enum:pending/completed/failed/refunded), payment_method(enum:bank_transfer/bank_deposit/mobile_money), bank_reference, bank_name, deposit_date, phone_number, verified_by, verified_at, notes, created_at, updated_at.\n**Indexes:** PK, idx_phone_number, idx_user_id, idx_payment_status.\nMany are auto-imported for Microsoft Office graduates with IMPORTED bank_reference.\n

### 2.6 Communication Domain

#### notifications -- In-App Notifications (7 records)
**Columns:** notification_id(PK,AI), user_id(FK->users), type, title, message, data(JSON), is_read, read_at, created_at, updated_at.\n**Indexes:** PK, idx_user_id, idx(user_id,is_read).\n
#### announcements -- System & Course Announcements (4 records)
**Columns:** announcement_id(PK,AI), course_id(FK->courses), title, content(longtext), priority(enum:low/normal/high/urgent), is_published, expires_at, posted_by(FK->users), created_at, updated_at.\n**Indexes:** PK, idx_course_id, idx_posted_by, idx(is_published,expires_at).\n
#### discussions -- Course Forum Threads (4 records)
**Columns:** discussion_id(PK,AI), course_id(FK->courses), title, content(longtext), created_by(FK->users), is_pinned, is_locked, view_count, reply_count, created_at, updated_at.\n**Indexes:** PK, idx_course_id, idx_created_by.\n
#### discussion_replies -- Nested Forum Replies
**Columns:** reply_id(PK,AI), discussion_id(FK->discussions), user_id(FK->users), parent_reply_id(FK->discussion_replies,self-ref), content(longtext), is_instructor_reply, is_best_answer, created_at, updated_at.\n**Indexes:** PK, idx_discussion_id, idx_user_id, idx_parent_reply_id.\n
#### messages -- Direct User Messages (4 records)
**Columns:** message_id(PK,AI), sender_id(FK->users), recipient_id(FK->users), subject, content(longtext), is_read, created_at, updated_at.\n**Indexes:** PK, idx_sender_id, idx_recipient_id.\n
#### email_queue -- Asynchronous Email Queue (75 records)
**Columns:** id(PK,AI), recipient_email, recipient_name, subject, body(longtext), template, status(enum:pending/sent/failed), attempts, scheduled_at, sent_at, error_message, created_at, updated_at.\n**Indexes:** PK, idx_status, idx_scheduled_at.\n
#### email_templates -- Reusable Email Templates (5 records)
**Columns:** template_id(PK,AI), template_name, subject, body(longtext), template_type(enum:system/marketing/notification), is_active, created_at, updated_at.\n

### 2.7 Institution Domain

#### certificates -- Issued Certificates (18 records)
**Columns:** certificate_id(PK,AI), user_id(FK->users), course_id(FK->courses), enrollment_id(FK->enrollments), certificate_number(UQ), issued_date, verification_code(UQ), final_grade(decimal), classification, graduation_ceremony_date, issued_at, is_verified, expiry_date, created_at.\n**Indexes:** PK, UK_certificate_number, UK_verification_code, idx_user_id, idx_course_id, idx_enrollment_id, idx_issued_date.\n**Note:** Model has UPDATED_AT = null (no updated_at column).\n
#### badges -- Gamification Badges (6 records)
**Columns:** badge_id(PK,AI), name, description, icon, criteria, created_at, updated_at.\n
#### student_achievements -- Badge Earned Records (7 records)
**Columns:** achievement_id(PK,AI), student_id(FK->students), badge_id(FK->badges), course_id, earned_date, description.\n**Indexes:** PK, idx_student_id, idx_badge_id.\n
#### events -- Institution Events (0 records)
**Columns:** id(PK,AI), title, slug(UQ), description(longtext), event_date, location, image_url, status(enum:upcoming/ongoing/completed/cancelled), is_featured, created_by(FK->users), created_at, updated_at.\n**Indexes:** PK, UK_slug, idx_created_by, idx_status, idx_event_date, idx_is_featured.\n
#### event_images -- Event Photo Galleries (0 records)
**Columns:** id(PK,AI), event_id(FK->events), image_url, caption, display_order, created_at, updated_at.\n**Indexes:** PK, idx(event_id,display_order).\n
#### team_members -- Staff Directory (7 records)
**Columns:** id(PK,AI), user_id(FK->users), name, position, qualifications, image_url, display_order, created_at.\n**Indexes:** PK, idx_user_id.\n
#### testimonials -- Graduate Testimonials (0 records)
**Columns:** id(PK,AI), student_name, student_photo, course_taken, graduation_year, current_job_title, company, testimonial_text, rating, is_featured, status(enum:pending/approved/rejected), submitted_by(FK->users), created_at, updated_at.\n**Indexes:** PK, idx_status, idx_is_featured, idx_rating, idx_submitted_by.\n
#### hero_slides -- Homepage Carousel (6 records)
**Columns:** id(PK,AI), title, subtitle, description, image_path, cta_text, cta_link, secondary_cta_text, secondary_cta_link, display_order, is_active, created_by(FK->users), created_at, updated_at.\n**Indexes:** PK, idx_created_by, idx_is_active, idx_display_order.\n
#### institution_photos -- Campus Photo Gallery (12 records)
**Columns:** id(PK,AI), title, description, image_path, category, display_order, is_featured, uploaded_by(FK->users), created_at, updated_at.\n**Indexes:** PK, idx_uploaded_by, idx_category, idx_is_featured, idx_display_order.\n**ISSUE:** InstitutionPhoto model has duplicate is_featured in fillable and casts.\n
#### contacts -- Contact Form Submissions (23 records)
**Columns:** id(PK,AI), name, email, phone, subject, message, is_read, created_at, updated_at.\n**Indexes:** PK, idx_created_at, idx_is_read.\n**Note:** Mostly spam/test submissions.\n

### 2.8 System Domain

#### system_settings -- Application Configuration (21 records)
**Columns:** setting_id(PK,AI), setting_key, setting_value, setting_type(enum:String/Number/Boolean/JSON), description, is_editable, updated_at.\n**Indexes:** PK, idx_setting_key.\n**ISSUE:** Duplicate registration_fee_amount keys (IDs 11 and 21) with different values (150.00 vs 150).\n
#### activity_logs -- Audit Trail (13 records)
**Columns:** log_id(BIGINT,PK,AI), user_id, activity_type, description, ip_address, user_agent, created_at.\n**Indexes:** PK, idx_user_id, idx_activity_type, idx_created_at.\n
#### rate_limits -- API Rate Limiting (1 record)
**Columns:** id(PK,AI), identifier(UQ), attempt_count, expires_at.\n**Indexes:** PK, UK_identifier, idx_expires_at.\n
#### migrations_log -- Manual SQL Patch Tracking (18 entries)
**Columns:** id(PK,AI), filename(UQ), executed_at, status(enum:success/failed), error_message.\n**Indexes:** PK, UK_filename.\n**Note:** 3 failed patches recorded.\n

### 2.9 Newsletter & Content Tables

#### newsletter_subscribers -- Email Newsletter List
**Columns:** id(PK,AI), email(UQ), name, is_active, subscribed_at, created_at, updated_at.\n
#### course_reviews -- Course Ratings & Reviews (0 records)
**Columns:** id(PK,AI), course_id(FK->courses), user_id(FK->users), rating(decimal), review, created_at, updated_at.\n**Indexes:** PK, UK(course_id,user_id), idx_course_id, idx_user_id.\n

---

## 3. Entity-Relationship Diagram (Text)

`
users (90) ----< user_roles (118) >---- roles (6)
   | 1:1
   |--- user_profiles (103)
   | 1:1
   |--- students (84) ----< enrollments (51) >---- courses (35)
   |                         |
   |                         |--- lesson_progress (201)
   |                         |--- enrollment_payment_plans (51)
   |                         |--- certificates (18)
   |                         |--- payments (23) ---- transactions (10)
   |
   | 1:1
   |--- instructors (12) ----< courses (35) >---- course_categories (12)
   |                         |
   |                         |--- modules (42) ---- lessons (120)
   |                         |                      |
   |                         |                      |--- lesson_resources (29)
   |                         |                      |--- quizzes (24)
   |                         |                      |--- assignments (9)
   |                         |                      |--- live_sessions
   |                         |
   |                         |--- course_instructors (25)
   |                         |--- announcements (4)
   |                         |--- discussions (4)
   |
   |---< notifications (7)
   |---< messages (4)
   |---< activity_logs (13)
   |---< user_sessions (1)
   |---< remember_tokens

quizzes (24) ----< quiz_questions (65) >---- questions (71)
   |                                          |
   |                                          |--- question_options (278)
   |                                          |--- quiz_question_options (278) [DUPLICATE]
   |
   |---< quiz_attempts (4) ----< quiz_answers

assignments (9) ----< assignment_submissions (63)

lenco_transactions ----< lenco_collections
   |
   |---< lenco_webhook_logs (156)

events ----< event_images
badges ----< student_achievements (7)
discussions ----< discussion_replies
email_queue <---- email_templates (5)
`

---

## 4. Model-to-Table Mapping

| Model | Table | PK | Key Relationships | Key Methods |
|-------|-------|----|-------------------|-------------|
| User | users | id | roles, profile, student, instructor, enrollments, payments, certificates | isAdmin[1,2], isInstructor[3], isFinance[6], isStudent[4] |
| Role | roles | id | users | -- |
| UserRole | user_roles | id | user, role | getRoleNameAttribute() |
| Student | students | id | user, enrollments, registrationFees, achievements | -- |
| Instructor | instructors | id | user, courses, liveSessions | -- |
| UserProfile | user_profiles | id | user | -- |
| UserSession | user_sessions | id | user | isExpired() |
| RememberToken | remember_tokens | id | user | isExpired() |
| Course | courses | id | category, instructor, modules, enrollments, quizzes, assignments, payments, reviews | published(), featured() |
| CourseCategory | course_categories | id | courses | -- |
| CourseInstructor | course_instructors | id | course, instructor | -- |
| Module | modules | id | course, lessons | ordered by display_order |
| Lesson | lessons | id | module, quizzes, assignments, liveSessions, lessonProgress, resources | -- |
| LessonResource | lesson_resources | id | lesson | -- |
| LessonNote | lesson_notes | id | lesson, instructor | -- |
| LessonProgress | lesson_progress | id | enrollment, lesson | isCompleted() |
| Enrollment | enrollments | id | user, student, course, certificate, payments, lessonProgress, paymentPlan | isFullyPaid() |
| EnrollmentPaymentPlan | enrollment_payment_plans | id | enrollment | getBalanceAttribute() |
| Certificate | certificates | certificate_id | user, course, enrollment | getVerificationUrlAttribute(), no updated_at |
| Quiz | quizzes | id | course, questions, attempts | -- |
| Question | questions | question_id | options, quizzes | -- |
| QuestionOption | question_options | id | question | -- |
| QuizQuestionOption | quiz_question_options | id | question | no updated_at |
| QuizAttempt | quiz_attempts | id | quiz, student, answers | isPassed() |
| QuizAnswer | quiz_answers | answer_id | attempt, question | -- |
| Assignment | assignments | id | course, submissions | -- |
| AssignmentSubmission | assignment_submissions | id | assignment, student, grader | -- |
| Payment | payments | payment_id | student, course, enrollment, paymentMethod, recordedBy | completed(), pending() |
| PaymentMethod | payment_methods | payment_method_id | payments | no updated_at |
| Transaction | transactions | transaction_id | payment | -- |
| LencoTransaction | lenco_transactions | id | user, enrollment, course | -- |
| LencoWebhookLog | lenco_webhook_logs | id | -- | -- |
| RegistrationFee | registration_fees | id | user, student | -- |
| Notification | notifications | notification_id | user | markAsRead() |
| Announcement | announcements | announcement_id | course, postedBy | published() |
| Discussion | discussions | discussion_id | course, createdBy, replies | -- |
| DiscussionReply | discussion_replies | reply_id | discussion, user, parentReply | -- |
| Message | messages | message_id | sender, recipient | -- |
| EmailQueue | email_queue | id | -- | -- |
| EmailTemplate | email_templates | template_id | -- | -- |
| Badge | badges | badge_id | achievements | -- |
| StudentAchievement | student_achievements | achievement_id | student, badge, course | -- |
| Event | events | id | createdBy, images | -- |
| HeroSlide | hero_slides | id | -- | active() |
| InstitutionPhoto | institution_photos | id | -- | active(), featured() |
| TeamMember | team_members | id | user | -- |
| Testimonial | testimonials | id | submittedBy | -- |
| Contact | contacts | id | -- | -- |
| CourseReview | course_reviews | id | course, user | -- |
| NewsletterSubscriber | newsletter_subscribers | id | -- | -- |
| SystemSetting | system_settings | setting_id | -- | -- |
| ActivityLog | activity_logs | log_id | user | -- |

---

## 5. Core Data Flows

### 5.1 Student Enrollment & Learning Lifecycle

`
1. users (register/login)
   |
   |---> students (profile extension)
   |
   |---> registration_fees (pay ZMW 150 one-time fee)
   |
   |---> enrollments + enrollment_payment_plans
   |       |
   |       |---> payments (trigger: after_payment_insert)
   |       |       |
   |       |       |---> 30% paid -> enrollment_status = In Progress
   |       |       |
   |       |       |---> 100% paid -> certificate_blocked = 0
   |       |
   |       |---> lesson_progress (per-lesson tracking)
   |       |
   |       |---> quiz_attempts + quiz_answers
   |       |
   |       |---> assignment_submissions
   |       |
   |       |---> enrollment completion -> certificates (if not blocked)
   |
   |---> notifications (throughout lifecycle)
`

### 5.2 Course Content Creation Lifecycle

`
instructors
   |
   |---> courses (status: draft -> published/archived)
   |       |
   |       |---> modules (ordered by display_order)
   |       |       |
   |       |       |---> lessons (Video/Reading/Quiz/Assignment/Live Session/Download)
   |       |               |
   |       |               |---> lesson_resources
   |       |               |---> quizzes + questions + options
   |       |               |---> assignments
   |       |               |---> live_sessions
   |       |
   |       |---> announcements
   |       |---> discussions
   |
   |---> live_sessions + live_session_attendance
`

### 5.3 Financial Transaction Flow

`
students
   |
   |---> registration_fees (ZMW 150, verified by admin)
   |
   |---> enrollments
   |       |
   |       |---> payments (ledger entry)
   |       |       |
   |       |       |---> transactions (gateway detail)
   |       |       |
   |       |       |---> lenco_transactions (virtual account)
   |       |       |       |
   |       |       |       |---> lenco_collections
   |       |       |       |
   |       |       |       |---> lenco_webhook_logs (async confirmation)
   |       |       |
   |       |       |---> enrollment_payment_plans (balance update)
   |       |
   |       |---> v_student_balances (view: aggregated balances)
`


---

## 6. Triggers, Views & Events

### 6.1 Trigger: after_payment_insert

**Type:** AFTER INSERT on payments\n**Purpose:** Enforce business rules for payment milestones\n**Logic:**\n1. Updates enrollment_payment_plans.total_paid\n2. If total_paid >= 30% of total_fee: enrollment.enrollment_status = In Progress\n3. If total_paid >= 100% of total_fee: enrollment.certificate_blocked = 0\n**Impact:** All payment processing MUST go through this trigger or manually replicate its logic.\n
### 6.2 View: v_student_balances

**Purpose:** Aggregated financial summary per student\n**Logic:** JOINs users -> students -> enrollments -> enrollment_payment_plans, GROUPs by user, calculates total_course_fees, total_paid, total_balance, and overall_status (Outstanding/Clear).\n
### 6.3 Event: cleanup_expired_lenco_transactions

**Schedule:** Every 1 hour\n**Purpose:** Mark pending Lenco transactions as expired when past their expires_at\n`sql\nUPDATE lenco_transactions\nSET status = expired, updated_at = NOW()\nWHERE status = pending AND expires_at < NOW();\n`\n

---

## 7. Data Quality Audit

### 7.1 CRITICAL Issues

| Severity | Issue | Table | Impact | Evidence |
|----------|-------|-------|--------|----------|
| CRITICAL | Role ID mismatch | User.php vs UserRole.php | Authorization failures | UserRole.php maps 2=Instructor,3=Finance but roles table has 3=Instructor,6=Finance |
| CRITICAL | Duplicate question options tables | question_options + quiz_question_options | Data inconsistency | Both tables have identical structure and 278 identical records |
| CRITICAL | Lenco webhook failures | lenco_webhook_logs | Payment confirmations lost | 150+ entries with Invalid signature in May 2026 |

### 7.2 HIGH Priority Issues

| Severity | Issue | Table | Impact | Evidence |
|----------|-------|-------|--------|----------|
| HIGH | Duplicate categories | course_categories | UX confusion | 3 rows named Cybersecurity (IDs 7, 8, 12) |
| HIGH | Failed migrations | migrations_log | Schema drift | 3 failed patches recorded |
| HIGH | Duplicate avatar columns | user_profiles | Data inconsistency | Both avatar_url and avatar columns exist |
| HIGH | Default USD currency | transactions | Reporting confusion | Default currency = USD but operations use ZMW |
| HIGH | Duplicate setting keys | system_settings | Config ambiguity | Two registration_fee_amount entries (IDs 11, 21) |

### 7.3 MEDIUM Priority Issues

| Severity | Issue | Table | Impact | Evidence |
|----------|-------|-------|--------|----------|
| MEDIUM | Orphaned user_role | user_roles | Invalid reference | Entry ID 30 has user_id=0 |
| MEDIUM | Spam contacts | contacts | Data pollution | 23 submissions, many spam/test |
| MEDIUM | Incomplete student data | students | Missing demographics | Many imported students have NULL fields |
| MEDIUM | Duplicate fillable/casts | InstitutionPhoto model | Code quality | is_featured listed twice |
| MEDIUM | Missing model | lenco_collections | Cannot use Eloquent | Table exists but no model |

### 7.4 Data Statistics Summary

| Table | Records | Notes |
|-------|---------|-------|
| users | 90 | Mix of staff, students, test accounts |
| students | 84 | Many with incomplete profiles |
| instructors | 12 | |
| courses | 35 | |
| modules | 42 | |
| lessons | 120 | |
| enrollments | 51 | |
| lesson_progress | 201 | |
| quizzes | 24 | |
| questions | 71 | |
| question_options | 278 | |
| quiz_question_options | 278 | DUPLICATE of above |
| quiz_attempts | 4 | Low engagement |
| assignments | 9 | |
| assignment_submissions | 63 | |
| payments | 23 | |
| transactions | 10 | |
| certificates | 18 | |
| registration_fees | 20 | Many auto-imported |
| lenco_webhook_logs | 156 | 150+ invalid signatures |
| contacts | 23 | Mostly spam |
| email_queue | 75 | |
| announcements | 4 | |
| discussions | 4 | Low engagement |
| notifications | 7 | |
| activity_logs | 13 | |
| hero_slides | 6 | |
| institution_photos | 12 | |
| team_members | 7 | |
| events | 0 | Empty |
| event_images | 0 | Empty |
| testimonials | 0 | Empty |
| badges | 6 | |
| student_achievements | 7 | |
| course_reviews | 0 | Empty |
| messages | 4 | |
| newsletter_subscribers | 0 | Empty |

---

## 8. Migration History

### 8.1 Laravel Migration Files (55 files)

All migrations follow the pattern 2024_01_01_0000XX_create_*_table.php (core tables) and 2026_05_* (recent additions).\n
**Core Migrations (2024-01-01 batch):**\n1. personal_access_tokens (Laravel Sanctum)\n2. users\n3. course_categories\n4. instructors\n5. courses\n6. modules\n7. lessons\n8. enrollments\n9. certificates\n10. payments\n11. quizzes\n12. assignments\n13. user_roles\n14. user_profiles\n15. questions\n16. quiz_questions\n17. question_options\n18. quiz_attempts\n19. quiz_answers\n20. assignment_submissions\n21. announcements\n22. activity_logs\n23. email_queue\n24. payment_methods\n25. live_sessions\n26. live_session_attendance\n27. lesson_progress\n28. course_reviews\n29. enrollment_payment_plans\n30. notifications\n31. lenco_transactions\n32. lenco_webhook_logs\n33. system_settings\n34. students\n35. roles\n36. remember_tokens\n37. registration_fees\n38. badges\n39. contacts\n40. course_instructors\n41. discussions\n42. discussion_replies\n43. email_templates\n44. lesson_resources\n45. messages\n46. quiz_question_options\n47. student_achievements\n48. team_members\n49. transactions\n50. user_sessions\n51. testimonials (2024-01-02)\n52. events (2024-01-02)\n53. hero_slides (2024-01-02)\n\n**Recent Migrations (2026):**\n54. institution_photos\n55. add_classification_to_certificates\n56. lesson_notes\n57. newsletter_subscribers\n58. add_remember_token_to_users\n\n### 8.2 Manual SQL Patches (migrations_log)

| Filename | Status | Error |
|----------|--------|-------|
| 001_create_lenco_tables.sql | success | -- |
| 002_comprehensive_fixes.sql | FAILED | Duplicate entry registration_fee_amount |
| 003_add_google_id_to_users.sql | success | -- |
| 004_add_unique_constraints.sql | FAILED | Unknown column user_id in users |
| add_certificates_user_course_columns.sql | success | -- |
| add_course_career_fields.sql | success | -- |
| add_lenco_collections_table.sql | success | -- |
| add_lenco_transactions_table.sql | success | -- |
| add_lenco_webhook_logs_table.sql | success | -- |
| add_performance_indexes.sql | success | -- |
| add_phone_number_to_registration_fees.sql | success | -- |
| assign-michael-to-microsoft-office.sql | FAILED | Unknown column u.name |
| create_migrations_log.sql | success | -- |
| create_quiz_question_options_table.sql | success | -- |
| create_system_settings_table.sql | success | -- |
| create_v_student_balances_view.sql | success | -- |
| fix_enrollment_payment_plans.sql | success | -- |
| seed_payment_methods.sql | success | -- |

---

## 9. Critical Issues & Recommendations

### 9.1 Must Fix Immediately

#### 1. Role ID Consistency Crisis
**Problem:** UserRole.php getRoleNameAttribute() maps 2=Instructor and 3=Finance, but the roles table defines 3=Instructor and 6=Finance. User.php correctly checks 3=Instructor and 6=Finance.\n**Impact:** Any code using UserRole display names will show wrong roles. Authorization checks relying on display names will fail.\n**Fix:** Update UserRole.php to match the roles table:\n`php\nreturn match (->role_id) {\n    1 => Super Admin,\n    2 => Admin,\n    3 => Instructor,  // Was 2 - WRONG\n    4 => Student,\n    5 => Content Creator,\n    6 => Finance,     // Was 3 - WRONG\n    default => Unknown,\n};\n`\n
#### 2. Eliminate Duplicate quiz_question_options Table
**Problem:** Both question_options and quiz_question_options exist with identical data.\n**Fix:** Drop quiz_question_options and use question_options exclusively. Update or remove the QuizQuestionOption model.\n`sql\nDROP TABLE quiz_question_options;\n`\n
#### 3. Fix Lenco Webhook Signature Validation
**Problem:** 150+ webhook entries show Invalid signature. Payments are not being confirmed.\n**Likely Causes:** LENCO_WEBHOOK_SECRET mismatch, wrong algorithm, or sandbox/live key confusion.\n**Fix:** Verify webhook secret in .env matches Lenco dashboard and review signature verification logic.\n
### 9.2 Should Fix Soon

#### 4. Clean Up Duplicate Categories\n`sql\nUPDATE courses SET category_id = 7 WHERE category_id IN (8, 12);\nDELETE FROM course_categories WHERE id IN (8, 12);\n`\n
#### 5. Re-run Failed Migrations\n- Fix 002_comprehensive_fixes.sql: Use INSERT IGNORE or ON DUPLICATE KEY UPDATE\n- Fix 004_add_unique_constraints.sql: Verify column names against actual schema\n- Fix assign-michael-to-microsoft-office.sql: Replace u.name with CONCAT(u.first_name, , u.last_name)\n
#### 6. Fix Default Currency\n`sql\nALTER TABLE transactions MODIFY currency VARCHAR(3) DEFAULT ZMW;\nUPDATE transactions SET currency = ZMW WHERE currency = USD;\n`\n
#### 7. Consolidate Avatar Columns\n`sql\nUPDATE user_profiles SET avatar = avatar_url WHERE avatar IS NULL AND avatar_url IS NOT NULL;\nALTER TABLE user_profiles DROP COLUMN avatar_url;\n`\n
#### 8. Clean Up Orphaned Records\n`sql\nDELETE FROM user_roles WHERE user_id = 0;\nDELETE FROM contacts WHERE email LIKE %test% OR message LIKE %test%;\n`\n
### 9.3 Nice to Have\n
9. Create Eloquent Model for lenco_collections if actively used.\n10. Add FK constraint for registration_fees.student_id.\n11. Standardize primary key naming (some use id, others use table_id).\n12. Add missing indexes: lessons.lesson_type, quiz_attempts.completed_at, assignment_submissions.graded_by.\n

---

## Appendix A: Enum Values Reference

| Table | Column | Values |
|-------|--------|--------|
| users | status | active, inactive, suspended, pending |
| students | gender | Male, Female, Other, Prefer not to say |
| courses | level | Beginner, Intermediate, Advanced, All Levels |
| courses | status | draft, published, archived |
| lessons | lesson_type | Video, Reading, Quiz, Assignment, Live Session, Download |
| enrollments | enrollment_status | Enrolled, In Progress, Completed |
| enrollments | payment_status | Pending, Completed, Failed, Refunded, Cancelled |
| enrollment_payment_plans | payment_status | pending, partial, completed, overdue |
| quizzes | quiz_type | Practice, Graded, Final Exam, Midterm |
| questions | question_type | Multiple Choice, True/False, Short Answer, Essay, Fill in Blank |
| quiz_attempts | status | In Progress, Submitted, Graded, Abandoned |
| assignment_submissions | status | Submitted, Graded, Returned, Late |
| payments | payment_status | Pending, Completed, Failed, Refunded, Cancelled |
| payments | payment_type | registration, course_fee, partial_payment |
| registration_fees | payment_status | pending, completed, failed, refunded |
| registration_fees | payment_method | bank_transfer, bank_deposit, mobile_money |
| lenco_transactions | status | pending, completed, failed, expired, refunded |
| lenco_collections | status | pending, completed, failed |
| announcements | priority | low, normal, high, urgent |
| events | status | upcoming, ongoing, completed, cancelled |
| testimonials | status | pending, approved, rejected |
| email_queue | status | pending, sent, failed |
| email_templates | template_type | system, marketing, notification |
| system_settings | setting_type | String, Number, Boolean, JSON |
| transactions | transaction_type | Payment, Refund, Chargeback, Fee |
| course_instructors | role | Lead, Assistant, Guest, Mentor |
\n## Appendix B: Foreign Key Quick Reference

Key FK chains (Child -> Parent):\n- enrollments -> users, students, courses\n- lessons -> modules -> courses -> course_categories, instructors\n- payments -> students, courses, enrollments, payment_methods\n| - lenco_transactions -> users, courses, enrollments\n| - certificates -> users, courses, enrollments\n| - assignment_submissions -> assignments -> courses, students\n| - quiz_attempts -> quizzes -> courses, students\n| - quiz_answers -> quiz_attempts, questions\n| - quiz_questions -> quizzes, questions -> question_options\n| - discussion_replies -> discussions -> courses, users (self-ref: parent_reply_id)\n| - messages -> users (sender, recipient)\n| - notifications, activity_logs -> users\n| - live_sessions -> lessons, instructors\n| - live_session_attendance -> live_sessions, users\n| - course_reviews -> courses, users\n| - student_achievements -> students, badges\n| - event_images -> events -> users (created_by)\n| - hero_slides, institution_photos -> users (created_by/uploaded_by)\n| - testimonials, team_members -> users\n\n---\n\n*End of Report*\n