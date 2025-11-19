# EDUTRACK LMS - COMPREHENSIVE CODEBASE AUDIT

## Executive Summary
- **Total PHP Files:** 108 (in public/) + 41 (in src/) = 149
- **Database Tables Referenced:** 22 actively used
- **Database Schema Tables (comprehensive):** 35+
- **Config Files:** 4
- **API Endpoints:** 17+ (some duplicated)
- **Admin Pages:** 33
- **Key Finding:** Multiple duplicate/similar files that should be consolidated

---

## 1. DIRECTORY STRUCTURE OVERVIEW

```
/home/user/edutrack-lms/
├── config/                          # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── mail.php
│   └── payment.php
├── database/                        # Database schemas & migrations
│   ├── migrations/                  # Migration scripts
│   ├── complete_lms_schema.sql      # Primary comprehensive schema
│   ├── course_management_schema.sql
│   ├── add_advanced_tables.sql
│   ├── fix_autoincrement.sql
│   ├── hotfix_*.sql (3 files)
│   ├── schema_compatibility_fix.sql
│   ├── final_compatibility_fix.sql
│   ├── populate_web_dev_course.sql
│   ├── verify_database.sql
│   └── *.md (7 documentation files)
├── src/                             # Source code (application logic)
│   ├── api/
│   │   └── ApiBase.php
│   ├── bootstrap.php                # Application bootstrap
│   ├── classes/                     # Core business logic (21 classes)
│   ├── includes/                    # Utility & helper files (10 files)
│   ├── middleware/                  # Request middleware (4 files)
│   ├── templates/                   # Reusable UI components (12 files)
│   └── mail/                        # Email templates (6 files)
├── public/                          # Web-accessible files (108 files)
│   ├── index.php                    # Homepage
│   ├── login.php, register.php      # Auth pages
│   ├── dashboard.php                # Main dashboard
│   ├── admin/                       # Admin interface (33 files, 14 subdirs)
│   ├── instructor/                  # Instructor interface (6 files)
│   ├── student/                     # Student interface (4 files)
│   ├── api/                         # REST API endpoints (17 files, 2 versions)
│   ├── actions/                     # Form action handlers (3 files)
│   ├── assets/                      # Static files (CSS, JS, images)
│   └── uploads/                     # User-generated content
├── storage/                         # Runtime data
│   ├── sessions/
│   ├── cache/
│   └── backups/ (configured)
├── tests/                           # Unit tests (2 files)
├── docs/                            # Documentation
└── *.md files (9 docs at root)

```

---

## 2. PHP FILES ORGANIZED BY PURPOSE

### Configuration Files (4)
```
/home/user/edutrack-lms/config/
├── app.php                    # Application settings
├── database.php               # Database connection config
├── mail.php                   # Email configuration
└── payment.php                # Payment gateway config
```

### Core System Files (11)
```
/home/user/edutrack-lms/src/
├── bootstrap.php              # Application initialization (661 lines)
├── includes/auth.php          # Authentication logic (521 lines)
├── includes/database.php      # Database abstraction layer
├── includes/config.php        # Config loader
├── includes/security.php      # Security utilities (431 lines)
├── includes/validation.php    # Input validation (607 lines)
├── includes/functions.php     # Helper functions (556 lines)
├── includes/helpers.php       # Additional helpers
├── includes/email.php         # Email utilities (374 lines)
├── includes/access-control.php  # Role-based access
├── includes/security-headers.php  # HTTP headers
```

### Business Logic Classes (21)
```
/home/user/edutrack-lms/src/classes/
├── User.php                   # User management (460 lines)
├── Course.php                 # Course CRUD & data (501 lines)
├── Module.php                 # Course modules/sections
├── Lesson.php                 # Lesson content management
├── Assignment.php             # Assignment creation & tracking
├── Submission.php             # Assignment submissions
├── Quiz.php                   # Quiz management (407 lines)
├── Question.php               # Quiz questions
├── Enrollment.php             # Course enrollment (393 lines)
├── Progress.php               # Student progress tracking (387 lines)
├── Certificate.php            # Certificate management
├── CertificateGenerator.php   # Certificate PDF generation
├── Category.php               # Course categories
├── Announcement.php           # Course announcements (396 lines)
├── Review.php                 # Course reviews (487 lines)
├── Payment.php                # Payment processing (491 lines)
├── Invoice.php                # Invoice management
├── Notification.php           # User notifications
├── Email.php                  # Email sending (374 lines)
├── FileUpload.php             # File upload handling
└── Statistics.php             # System statistics (449 lines)
```

### API Endpoints (17 files - WITH DUPLICATES)
```
/home/user/edutrack-lms/public/api/
├── index.php                  # API documentation (62 lines)
├── auth.php                   # Authentication (483 lines) ⚠️ DUPLICATE OF v1/auth.php
├── assigment.php              # Assignment API (253 lines) - TYPO IN NAME
├── assignment.php             # ❌ MISSING (only in classes/)
├── quiz.php                   # Stub file (9 lines) ⚠️ INCOMPLETE
├── courses.php                # Stub file (8 lines) ⚠️ INCOMPLETE
├── enroll.php                 # Stub file (8 lines) ⚠️ INCOMPLETE
├── progress.php               # Stub file (9 lines) ⚠️ INCOMPLETE
├── upload.php                 # Stub file (8 lines) ⚠️ INCOMPLETE
├── payment.php                # Payment API (172 lines)
├── payment-callback.php       # Payment webhook (6 lines)
├── notifications.php          # Notifications (288 lines) ⚠️ DUPLICATE OF v1/notifications.php
├── lesson-notes.php           # Lesson notes (219 lines)
├── lesson-progress.php        # Progress tracking (216 lines)
├── notes.php                  # Note management (137 lines)
└── v1/
    ├── index.php              # API v1 docs (76 lines)
    ├── auth.php               # Auth v1 (483 lines) ⚠️ DUPLICATE OF ../auth.php
    └── notifications.php      # Notifications v1 (288 lines) ⚠️ DUPLICATE OF ../notifications.php
```

### Admin Interface (33 files in 13 subdirectories)
```
/home/user/edutrack-lms/public/admin/
├── index.php                  # Admin dashboard
├── login.php                  # Admin login
├── debug.php                  # Debug tools ⚠️ REMOVE FOR PRODUCTION
├── test-page.php              # Test page ⚠️ REMOVE FOR PRODUCTION
├── simple-test.php            # Simple test ⚠️ REMOVE FOR PRODUCTION
├── fix-check.php              # Database fix tool
├── check-structure.php        # Structure check
├── courses/                   # Course management
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── delete.php
│   └── modules.php
├── students/                  # Student management
│   ├── index.php
│   ├── view.php
│   └── enrollments.php
├── instructors/               # Instructor management
│   ├── index.php
│   ├── create.php
│   └── edit.php
├── announcements/             # Announcements
│   ├── index.php
│   ├── create.php
│   └── edit.php
├── categories/                # Category management
│   ├── index.php
│   ├── create.php
│   └── edit.php
├── payments/                  # Payment management
│   ├── index.php
│   ├── reports.php
│   └── verify.php
├── enrollments/               # Enrollment management
│   └── index.php
├── certificates/              # Certificate management
│   ├── index.php
│   ├── issue.php
│   └── templates.php
├── reviews/                   # Review management
│   └── index.php
├── settings/                  # System settings
│   ├── index.php
│   ├── email.php
│   └── payment-methods.php
├── users/                     # User management
│   ├── index.php
│   └── create.php
└── reports/                   # Reporting
    ├── index.php
    ├── enrollments.php
    └── revenue.php
```

### Student/Instructor Interfaces
```
/home/user/edutrack-lms/public/
├── student/
│   ├── assignments.php
│   ├── quizzes.php
│   ├── quiz-results.php
│   ├── submit-assignment.php
│   └── take-quiz.php
├── instructor/
│   ├── index.php
│   ├── courses.php
│   ├── course-edit.php
│   ├── assignments.php
│   ├── analytics.php
│   └── students.php
```

### Core Pages (40+ public-facing pages)
```
Key pages:
├── index.php (661 lines)          # Homepage
├── login.php                       # Login
├── register.php                    # Registration
├── dashboard.php (565 lines)       # User dashboard
├── courses.php                     # Course listing
├── course.php (483 lines)          # Course detail
├── course-preview.php              # Course preview
├── lesson.php                      # Lesson view
├── learn.php (423 lines)           # Learning interface
├── quiz.php                        # Take quiz
├── assignment.php                  # View assignment
├── take-quiz.php                   # Quiz interface
├── quiz-result.php                 # Quiz results
├── checkout.php (419 lines)        # Payment checkout
├── enroll.php                      # Enrollment
├── edit-profile.php (514 lines)    # Profile editing
├── verify-email.php                # Email verification
├── forgot-password.php             # Password reset
├── reset-password.php              # Reset password form
├── my-courses.php                  # Student courses
├── my-certificates.php             # Student certificates
├── certificate-verify.php          # Verify certificate
├── verify-certificate.php          # Verify certificate page
├── download-certificate.php        # Download PDF
├── review-course.php               # Course reviews
├── course-discussions.php (367 lines)  # Course discussions
├── contact.php (363 lines)         # Contact form
├── search.php                      # Search
├── about.php                       # About page
├── profile.php                     # Public profile
└── payment-success/failure pages   # Payment callbacks
```

### Form Action Handlers (3)
```
/home/user/edutrack-lms/public/actions/
├── mark-lesson-complete.php        # Mark lesson done
├── submit-assignment.php           # Submit assignment
└── submit-quiz.php                 # Submit quiz
```

### Middleware (4)
```
/home/user/edutrack-lms/src/middleware/
├── authenticate.php                # Login check
├── admin-only.php                  # Admin check
├── instructor-only.php             # Instructor check
└── enrolled-only.php               # Enrollment check
```

### Templates/Components (12)
```
/home/user/edutrack-lms/src/templates/
├── header.php
├── footer.php
├── admin-header.php
├── admin-footer.php
├── admin-sidebar.php
├── instructor-header.php
├── instructor-footer.php
├── navigation.php
├── sidebar.php
├── alerts.php
└── announcements.php
```

### Email Templates (6)
```
/home/user/edutrack-lms/src/mail/
├── welcome.php                     # Welcome email
├── verify-email.php                # Email verification
├── enrollment-confirm.php          # Enrollment confirmation
├── reset-password.php              # Password reset
├── payment-received.php            # Payment confirmation
└── certificate-issued.php          # Certificate notification
```

### Test Files (2)
```
/home/user/edutrack-lms/tests/
├── CourseTest.php
└── UserTest.php
```

---

## 3. DATABASE-RELATED FILES

### Schema Files (11 SQL files)
```
/home/user/edutrack-lms/database/
├── complete_lms_schema.sql        # MAIN SCHEMA - All 35 tables
├── course_management_schema.sql   # Secondary schema reference
├── add_advanced_tables.sql        # Advanced features (badges, etc.)
├── schema_compatibility_fix.sql   # Compatibility adjustments
├── final_compatibility_fix.sql    # Final fixes
├── fix_autoincrement.sql          # AUTO_INCREMENT restoration
├── hotfix_add_role_column.sql     # Role column addition
├── hotfix_password_column.sql     # Password column fix
├── hotfix_user_status.sql         # Status column fix
├── populate_web_dev_course.sql    # Sample data
└── verify_database.sql            # Verification script
```

### Migration Scripts (5)
```
/home/user/edutrack-lms/database/migrations/
├── README.md
├── README_APPLY_CATEGORIES.md
├── create_categories_table.sql
├── add_status_column_to_enrollments.sql
└── APPLY_THIS_FIX.sql
```

### Documentation (7 files)
```
/home/user/edutrack-lms/database/
├── README_SCHEMA.md                    # Schema overview
├── COMPLETE_SCHEMA_README.md           # Detailed schema docs
├── DATABASE_STATUS.md                  # Current status
├── IMPLEMENTATION_GUIDE.md             # Setup instructions
├── NEXT_STEPS.md                       # Next actions
├── FINAL_SETUP_GUIDE.md                # Final setup
└── ROLE_STRUCTURE_GUIDE.md             # Role setup
```

---

## 4. DUPLICATE & REDUNDANT FILES

### HIGH PRIORITY - EXACT DUPLICATES (CONSOLIDATE)

**1. API Authentication (IDENTICAL)**
- `/public/api/auth.php` (483 lines)
- `/public/api/v1/auth.php` (483 lines)
- **Action:** Keep ONE, delete duplicate. Update routes to use single endpoint.

**2. Notifications API (IDENTICAL)**
- `/public/api/notifications.php` (288 lines)
- `/public/api/v1/notifications.php` (288 lines)
- **Action:** Consolidate versions. Implement proper API versioning strategy.

**3. API Index/Documentation**
- `/public/api/index.php` (62 lines)
- `/public/api/v1/index.php` (76 lines)
- **Action:** Merge or establish single entry point.

### MEDIUM PRIORITY - INCOMPLETE STUBS (FINISH OR REMOVE)

**4. Incomplete API Endpoints (9 lines each)**
- `/public/api/quiz.php` - Only 9 lines, stub file
- `/public/api/progress.php` - Only 9 lines, stub file
- `/public/api/courses.php` - Only 8 lines, stub file
- `/public/api/enroll.php` - Only 8 lines, stub file
- `/public/api/upload.php` - Only 8 lines, stub file
- `/public/api/payment-callback.php` - Only 6 lines, stub file
- **Action:** Either complete these or remove them. Use existing class files instead.

**5. Note Management Confusion**
- `/public/api/notes.php` (137 lines)
- `/public/api/lesson-notes.php` (219 lines)
- **Action:** Determine if different or duplicate. Consolidate if similar.

### LOW PRIORITY - DEVELOPMENT TOOLS (REMOVE FOR PRODUCTION)

**6. Debug/Test Pages**
- `/public/admin/debug.php` - Debugging interface
- `/public/admin/test-page.php` - Test harness
- `/public/admin/simple-test.php` - Simple test
- **Action:** Remove before going to production (security risk).

**7. Fix/Check Utility Pages**
- `/public/admin/fix-check.php` - Database fix tool
- `/public/admin/check-structure.php` - Structure checker
- **Action:** Document purpose. Move to secure admin area or dev environment only.

### POTENTIAL ISSUES

**8. Naming Typo**
- `/public/api/assigment.php` ⚠️ TYPO - Should be "assignment"
- **Action:** Rename or refactor to correct spelling.

---

## 5. COMPREHENSIVE DATABASE TABLE REFERENCES

### Tables ACTIVELY USED IN CODE (22 tables)
Extracted from all class files and API endpoints:

**1. Core User Management (3 tables)**
- `users` - User accounts (id, email, password_hash, first_name, last_name, status, role, created_at)
- `user_profiles` - Extended user info (user_id, avatar, bio, phone, etc.)
- `roles` - User roles

**2. Course Management (5 tables)**
- `courses` - Course data (id, title, slug, description, category_id, price, status, etc.)
- `course_categories` - Category lookup (id, name, slug, parent_id)
- `course_modules` - Module organization (id, course_id, title, order)
- `lessons` - Lesson content (id, module_id, title, description, video_url, duration)
- `course_reviews` - Student reviews (id, course_id, user_id, rating, review_text)

**3. Enrollment & Progress (4 tables)**
- `enrollments` - Course enrollments (id, user_id, course_id, status, enrolled_at, progress_percentage)
- `lesson_progress` - Lesson completion (id, user_id, lesson_id, status, completed_at)
- `activity_logs` - User activity tracking (id, user_id, action, created_at)
- `certificates` - Issued certificates (id, user_id, course_id, certificate_number, issued_at)

**4. Assessments (5 tables)**
- `assignments` - Assignment data (id, course_id, lesson_id, title, description, due_date)
- `assignment_submissions` - Student submissions (id, user_id, assignment_id, file_path, status, submitted_at)
- `quizzes` - Quiz creation (id, course_id, title, total_questions, passing_score)
- `quiz_questions` - Quiz questions (id, quiz_id, question_text, question_type)
- `quiz_attempts` - Quiz attempts (id, user_id, quiz_id, score, total_score, completed_at)

**5. Communication (3 tables)**
- `announcements` - Course announcements (id, course_id, title, content, created_at)
- `notifications` - User notifications (id, user_id, title, message, is_read, created_at)
- `lesson_notes` - Student notes (id, user_id, lesson_id, note_text, created_at)

**6. Payments (2 tables)**
- `payments` - Payment records (id, user_id, enrollment_id, amount, status, created_at)
- `invoices` - Invoice records (id, user_id, course_id, amount, status)

**7. Email Queue (1 table)**
- `email_queue` - Pending emails (id, recipient, subject, body, status, created_at)

---

## 6. COMPLETE DATABASE SCHEMA (35+ Tables)

The `complete_lms_schema.sql` defines these tables:

**Authentication & Authorization (4)**
1. Users - Central user table
2. Roles - Role definitions
3. User_Roles - User-to-role mapping
4. System_Settings - System configuration

**Course Management (9)**
5. Course_Categories - Course categories
6. Courses - Course records
7. Instructors - Instructor profiles
8. Course_Instructors - Multi-instructor support
9. Students - Student profiles
10. Modules - Course modules/sections
11. Lessons - Lesson content
12. Lesson_Resources - Attachments/resources
13. Lesson_Progress - Student progress

**Assessments (7)**
14. Assignments - Assignment details
15. Assignment_Submissions - Student submissions
16. Quizzes - Quiz creation
17. Questions - Question bank (universal)
18. Question_Options - Multiple choice options
19. Quiz_Questions - Questions in quizzes
20. Quiz_Attempts - Student quiz attempts
21. Quiz_Answers - Student quiz answers

**Communication (4)**
22. Announcements - Announcements
23. Discussions - Discussion threads
24. Discussion_Replies - Discussion replies
25. Messages - Direct messages

**Achievements & Certificates (3)**
26. Certificates - Issued certificates
27. Badges - Badge definitions
28. Student_Achievements - Achievements earned

**Payments & Transactions (3)**
29. Payment_Methods - Configured payment methods
30. Payments - Payment records
31. Transactions - Transaction history

**System & Utilities (5+)**
32. Activity_Logs - Activity tracking
33. Email_Templates - Email templates
34. Notifications - User notifications
35. Enrollments - Enrollment records

---

## 7. DATABASE CONFIGURATION

### Configuration File
**Location:** `/home/user/edutrack-lms/config/database.php`

```php
Database Connection: MySQL
Driver: PDO (MySQL)
Host: localhost (configurable via DB_HOST env)
Port: 3306 (configurable)
Database: edutrack_lms
Charset: utf8mb4
Collation: utf8mb4_unicode_ci
Engine: InnoDB
Strict Mode: Enabled
```

### Database Connection Handler
**Location:** `/home/user/edutrack-lms/src/includes/database.php`
- Singleton pattern implementation
- Prepared statements (PDO)
- Error logging
- Query execution wrapper

### Data Access Pattern
- Classes use `Database::getInstance()` singleton
- Methods like `query()`, `fetchOne()`, `fetchAll()`, `fetchColumn()`
- Parameterized queries for SQL injection prevention

---

## 8. KEY FINDINGS & RECOMMENDATIONS

### 🔴 HIGH PRIORITY ISSUES

1. **Duplicate API Files (3 pairs)**
   - `/api/auth.php` ≈ `/api/v1/auth.php`
   - `/api/notifications.php` ≈ `/api/v1/notifications.php`
   - Recommendation: Implement proper API versioning or consolidate

2. **Incomplete API Stubs (6 files)**
   - Files with 6-9 lines that don't implement functionality
   - Recommendation: Complete or remove these files

3. **Development Tools in Production**
   - `admin/debug.php`, `admin/test-page.php`, `admin/simple-test.php`
   - Recommendation: Remove or restrict access before deployment

### 🟡 MEDIUM PRIORITY ISSUES

4. **Database AUTO_INCREMENT Issues**
   - Status: Already noted in database documentation
   - Fix: Run `/database/fix_autoincrement.sql`

5. **Naming Typo in API**
   - `assigment.php` should be `assignment.php`
   - Recommendation: Rename file for consistency

6. **Notes API Ambiguity**
   - Two similar files: `notes.php` and `lesson-notes.php`
   - Recommendation: Review if different or consolidate

### 🟢 GOOD PRACTICES OBSERVED

✅ Proper database abstraction layer
✅ Prepared statements used throughout
✅ Class-based organization
✅ Separate templates from logic
✅ Middleware for security checks
✅ Configuration externalization
✅ Email template separation
✅ Comprehensive schema documentation

---

## 9. DATABASE TABLE USAGE MAP

| Table Name | Used In Classes | API Endpoints | Admin Pages | Status |
|---|---|---|---|---|
| users | User, Enrollment, Progress | auth, auth v1 | users/, students/ | ✅ Active |
| courses | Course, Module, Lesson, Quiz, Assignment | courses (stub) | courses/ | ✅ Active |
| enrollments | Enrollment, Progress, Statistics | enroll (stub) | enrollments/ | ✅ Active |
| lessons | Lesson, Progress, Assignment | lesson-progress | - | ✅ Active |
| course_categories | Course, Category | courses (stub) | categories/ | ✅ Active |
| assignments | Assignment, Quiz | assigment | - | ✅ Active |
| assignment_submissions | Submission, Assignment | assigment | - | ✅ Active |
| quizzes | Quiz, Question, Progress | quiz (stub) | - | ✅ Active |
| quiz_questions | Question, Quiz | quiz (stub) | - | ✅ Active |
| quiz_attempts | Quiz, Progress | quiz (stub) | - | ✅ Active |
| certificates | Certificate, Statistics | - | certificates/ | ✅ Active |
| payments | Payment, Invoice | payment | payments/ | ✅ Active |
| announcements | Announcement | - | announcements/ | ✅ Active |
| notifications | Notification | notifications | - | ✅ Active |
| lesson_notes | Progress | notes, lesson-notes | - | ✅ Active |
| course_reviews | Review, Course | - | reviews/ | ✅ Active |
| user_profiles | User | - | - | ✅ Active |
| course_modules | Module, Course | - | courses/modules.php | ✅ Active |
| activity_logs | Enrollment, User | - | - | ✅ Active |
| invoices | Invoice, Payment | - | - | ✅ Active |
| email_queue | Email | - | - | ✅ Active |

---

## 10. FILE COUNT SUMMARY

```
Total PHP Files: 149
├── config/ : 4
├── src/ : 41
│   ├── classes/ : 21
│   ├── includes/ : 10
│   ├── middleware/ : 4
│   ├── templates/ : 12
│   ├── mail/ : 6
│   └── api/ : 1
└── public/ : 108
    ├── admin/ : 33
    ├── instructor/ : 6
    ├── student/ : 4
    ├── api/ : 17
    ├── actions/ : 3
    └── root-level pages : 45

Database Schema Files: 11 SQL files
Database Documentation: 7 Markdown files
Tests: 2 PHP files
Configuration Files: 4
Total Files to Review: 180+
```

---

## 11. SCHEMA ALIGNMENT NOTES

### Already In Code
These tables are actively used in the codebase:
- users (with password_hash column)
- user_profiles
- courses
- course_categories
- enrollments
- lessons
- course_modules
- assignments
- assignment_submissions
- quizzes
- quiz_questions
- quiz_attempts
- certificates
- payments
- announcements
- notifications
- activity_logs

### In Schema But May Not Be Fully Used
- course_reviews (used in Course class but not extensively)
- invoices (Payment class references)
- email_queue (Email class references)
- quiz_answers (Quiz schema, may not be fully used)
- course_instructors (junction table)
- instructors (table exists but may not be used)

### In Schema But Unlikely To Be Used
- discussions, discussion_replies
- messages
- badges, student_achievements
- payment_methods
- transactions
- email_templates
- roles, user_roles

---

## RECOMMENDATIONS FOR SCHEMA ALIGNMENT AUDIT

1. **Verify Active Table Usage** - Run the provided grep commands to confirm which schema tables are actually used
2. **Check Foreign Keys** - Ensure all FK relationships match the schema definition
3. **Review Column Names** - Some code uses snake_case (lesson_notes) vs schema might use different naming
4. **Validate Data Types** - Ensure columns match expected types (especially enums and decimal fields)
5. **Check Missing Columns** - If code references columns not in schema, schema may be incomplete
6. **Consolidate Duplicate Files** - Remove API duplicates and incomplete stubs
7. **Remove Dev Tools** - Delete debug.php, test-page.php, simple-test.php before production
8. **Fix AUTO_INCREMENT** - Run the provided SQL fix
9. **Rename Typo** - Change assigment.php to assignment.php
10. **Document API Versioning** - Clarify whether /api/v1/ is actively used or should be removed

