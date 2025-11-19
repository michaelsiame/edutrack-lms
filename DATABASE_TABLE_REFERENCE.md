# EDUTRACK LMS - DATABASE TABLE REFERENCE

## Quick Reference: Tables Active in Code

### Complete List of Database Tables (22 ACTIVELY USED)

```
CATEGORY: USER MANAGEMENT
├── users                    ✅ Core - authentication, profiles
├── user_profiles           ✅ Extended user data
└── roles                   ⚠️ Defined but usage unclear

CATEGORY: COURSES & CONTENT  
├── courses                 ✅ Core - course listings, details
├── course_categories       ✅ Core - category organization
├── course_modules          ✅ Core - module/section structure
├── lessons                 ✅ Core - lesson content
└── course_reviews          ⚠️ Defined but limited usage

CATEGORY: ENROLLMENT & PROGRESS
├── enrollments             ✅ Core - student course enrollment
├── lesson_progress         ✅ Core - lesson completion tracking
├── activity_logs           ✅ Active - user activity tracking
└── certificates            ✅ Core - issued certificates

CATEGORY: ASSESSMENTS
├── assignments             ✅ Core - assignment management
├── assignment_submissions  ✅ Core - student submissions
├── quizzes                 ✅ Core - quiz management
├── quiz_questions          ✅ Core - quiz questions
└── quiz_attempts           ✅ Core - student quiz attempts

CATEGORY: COMMUNICATION
├── announcements           ✅ Active - course announcements
├── notifications           ✅ Active - user notifications
└── lesson_notes            ✅ Active - student notes

CATEGORY: PAYMENTS
├── payments                ✅ Core - payment records
└── invoices                ⚠️ Defined but may not be used

CATEGORY: SYSTEM
└── email_queue             ✅ Active - email queue system
```

---

## Table Dependency Map

```
users
 ├── user_profiles
 ├── enrollments
 │   ├── courses
 │   │   ├── course_categories
 │   │   ├── course_modules
 │   │   │   └── lessons
 │   │   │       ├── assignments
 │   │   │       │   └── assignment_submissions
 │   │   │       ├── quizzes
 │   │   │       │   ├── quiz_questions
 │   │   │       │   └── quiz_attempts
 │   │   │       ├── lesson_progress
 │   │   │       ├── lesson_notes
 │   │   │       └── course_reviews
 │   │   └── payments
 │   └── certificates
 ├── announcements
 ├── notifications
 ├── activity_logs
 └── lesson_notes
```

---

## File-to-Table Mapping

### Classes (src/classes/)
| Class | Primary Table | Related Tables |
|-------|--------------|----------------|
| User.php | users | user_profiles, activity_logs |
| Course.php | courses | course_categories, course_modules, enrollments, course_reviews |
| Lesson.php | lessons | course_modules, lesson_progress, assignments, quizzes |
| Module.php | course_modules | lessons, courses |
| Assignment.php | assignments | assignment_submissions, lessons |
| Submission.php | assignment_submissions | assignments, users |
| Quiz.php | quizzes | quiz_questions, quiz_attempts, lessons |
| Question.php | quiz_questions | quizzes |
| Enrollment.php | enrollments | courses, users, lesson_progress, certificates |
| Progress.php | lesson_progress | enrollments, lessons |
| Certificate.php | certificates | users, courses, enrollments |
| Category.php | categories | courses (via category_id) |
| Announcement.php | announcements | courses |
| Review.php | course_reviews | courses, users |
| Payment.php | payments | users, enrollments, invoices |
| Invoice.php | invoices | users, courses |
| Notification.php | notifications | users |
| Email.php | email_queue | - |
| Statistics.php | All tables (read-only) | - |

### API Endpoints (public/api/)
| API File | Tables Used | Status |
|----------|-----------|--------|
| auth.php | users, roles | ✅ Active |
| v1/auth.php | users, roles | ⚠️ Duplicate |
| assigment.php* | assignments, assignment_submissions | ✅ Active (typo in name) |
| lesson-notes.php | lesson_notes | ✅ Active |
| lesson-progress.php | lesson_progress, enrollments | ✅ Active |
| notes.php | lesson_notes | ✅ Active |
| payment.php | payments, enrollments | ✅ Active |
| notifications.php | notifications | ✅ Active |
| v1/notifications.php | notifications | ⚠️ Duplicate |
| quiz.php | quizzes | ⚠️ Stub |
| courses.php | courses | ⚠️ Stub |
| enroll.php | enrollments | ⚠️ Stub |
| progress.php | lesson_progress | ⚠️ Stub |
| upload.php | - | ⚠️ Stub |

### Admin Pages (public/admin/)
| Section | Tables Used | Files |
|---------|-----------|-------|
| courses/ | courses, course_modules, lessons | create, edit, delete, index, modules |
| students/ | users, enrollments, lesson_progress | index, view, enrollments |
| instructors/ | users (instructor role) | create, edit, index |
| announcements/ | announcements | create, edit, index |
| categories/ | course_categories | create, edit, index |
| payments/ | payments, invoices | index, reports, verify |
| enrollments/ | enrollments | index |
| certificates/ | certificates | index, issue, templates |
| reviews/ | course_reviews | index |
| users/ | users, roles | create, index |
| settings/ | system_settings, email_templates | index, email, payment-methods |

---

## SQL Verification Queries

### Check if Tables Exist
```sql
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA='edutrack_lms' AND TABLE_NAME IN (
  'users', 'courses', 'enrollments', 'lessons', 'assignments',
  'assignment_submissions', 'quizzes', 'quiz_questions', 'quiz_attempts',
  'certificates', 'payments', 'announcements', 'notifications',
  'lesson_progress', 'activity_logs', 'user_profiles', 'course_categories',
  'course_modules', 'course_reviews', 'invoices', 'email_queue'
);
```

### Check AUTO_INCREMENT Status
```sql
SELECT TABLE_NAME, AUTO_INCREMENT 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA='edutrack_lms' 
AND TABLE_NAME IN ('courses', 'enrollments', 'instructors');
```

### Check Foreign Key Relationships
```sql
SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA='edutrack_lms' AND CONSTRAINT_NAME != 'PRIMARY';
```

---

## Column Naming Conventions Used in Code

### User Tables
- `id` (primary key)
- `user_id` (in extended/junction tables)
- `email`, `password_hash`, `first_name`, `last_name`
- `status` (enum: active, inactive, suspended)
- `created_at`, `updated_at` (timestamps)

### Course Tables
- `id`, `course_id` (primary key)
- `title`, `slug`, `description`
- `category_id` (foreign key)
- `instructor_id` (foreign key to users)
- `status` (enum: draft, published, archived)
- `price`, `discount_price` (decimal)

### Enrollment/Progress
- `id`, `enrollment_id`
- `user_id`, `course_id`, `student_id` (all used)
- `status`, `enrollment_status` (field naming varies)
- `progress_percentage`, `progress` (field naming varies)
- `enrolled_at`, `created_at` (timestamps)

### Assessment Tables
- `id`, `quiz_id`, `question_id`, `attempt_id`
- `score`, `total_score`, `passing_score`
- `completed_at`, `submitted_at` (timestamps)

---

## Data Type Patterns

### IDs & Foreign Keys
- Primary Keys: INT AUTO_INCREMENT
- Foreign Keys: INT NOT NULL
- Status columns: ENUM or VARCHAR

### Timestamps
- Default format: DATETIME or TIMESTAMP
- Most tables have: created_at, updated_at
- Some have: enrolled_at, submitted_at, completed_at

### Monetary Values
- Format: DECIMAL(10, 2)
- Used in: price, discount_price, amount, total_amount

### Ratings/Scores
- Format: DECIMAL(3, 2) or INT
- Range: 0-5 (ratings), variable (scores)

### Text Fields
- Titles: VARCHAR(200)
- Descriptions: TEXT
- Email: VARCHAR(100)
- Slugs: VARCHAR(250)

---

## Known Issues to Address

### 1. Column Name Variations
The code uses inconsistent naming:
- `student_id` vs `user_id` in enrollments
- `status` vs `enrollment_status` in enrollments
- `progress_percentage` vs `progress`

**Impact:** May cause data mapping issues

### 2. Table Case Sensitivity
- Schema uses PascalCase: `User_Profiles`, `Course_Categories`
- Code uses lowercase: `user_profiles`, `course_categories`
- MySQL is case-insensitive on Linux but sensitive on some systems

**Impact:** Potential portability issues

### 3. Missing Fields in Code Usage
Some tables referenced in schema may not have all expected columns:
- Check `quizzes` table for `total_questions`, `passing_score`
- Check `lessons` for `video_url`, `duration`
- Check `assignments` for `due_date`

**Impact:** Need to verify schema matches code expectations

### 4. Unused Tables
Several schema tables aren't used in current code:
- `discussions`, `discussion_replies`, `messages`
- `badges`, `student_achievements`
- `payment_methods`, `transactions`
- `email_templates`
- `roles`, `user_roles` (in schema but role storage unclear)

**Impact:** May be for future features or legacy

---

## Recommended Next Steps for Schema Alignment

1. **Run Table Existence Check**
   ```bash
   mysql -u root -p edutrack_lms < database/verify_database.sql
   ```

2. **Apply AUTO_INCREMENT Fix**
   ```bash
   mysql -u root -p edutrack_lms < database/fix_autoincrement.sql
   ```

3. **Validate Column Names**
   - Check if lowercase table names work
   - Test case-sensitive queries

4. **Verify Foreign Keys**
   - Run the FK verification query above
   - Check for orphaned records

5. **Test Data Integrity**
   - Run CourseTest.php and UserTest.php
   - Check for constraint violations

6. **Document Field Mapping**
   - Create mapping for inconsistent field names
   - Update code if schema field names change

---

## Performance Considerations

### Indexes Present
Based on schema, these should have indexes:
- users.email
- users.username
- courses.slug
- courses.status
- courses.category_id
- enrollments.user_id, course_id
- lesson_progress.user_id, lesson_id

### Recommended Indexes (Check if missing)
```sql
CREATE INDEX idx_lesson_progress_user_id ON lesson_progress(user_id);
CREATE INDEX idx_lesson_progress_lesson_id ON lesson_progress(lesson_id);
CREATE INDEX idx_assignment_submissions_user_id ON assignment_submissions(user_id);
CREATE INDEX idx_quiz_attempts_user_id ON quiz_attempts(user_id);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
```

---

**Last Updated:** 2025-11-19
**Status:** Complete & Ready for Schema Alignment Audit

