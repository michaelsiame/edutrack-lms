-- ============================================================================
-- SCHEMA COMPATIBILITY FIX
-- ============================================================================
-- Purpose: Modify the complete LMS schema to be compatible with existing code
-- This script makes the new schema work with the existing application without
-- requiring code changes.
--
-- IMPORTANT: Run this AFTER executing complete_lms_schema.sql
-- ============================================================================

-- ----------------------------------------------------------------------------
-- STEP 1: Rename tables to lowercase (for compatibility)
-- ----------------------------------------------------------------------------

RENAME TABLE Users TO users;
RENAME TABLE Roles TO roles;
RENAME TABLE User_Roles TO user_roles;
RENAME TABLE Course_Categories TO course_categories;
RENAME TABLE Instructors TO instructors;
RENAME TABLE Courses TO courses;
RENAME TABLE Students TO students;
RENAME TABLE Course_Instructors TO course_instructors;
RENAME TABLE Enrollments TO enrollments;
RENAME TABLE Modules TO modules;
RENAME TABLE Lessons TO lessons;
RENAME TABLE Lesson_Resources TO lesson_resources;
RENAME TABLE Lesson_Progress TO lesson_progress;
RENAME TABLE Assignments TO assignments;
RENAME TABLE Assignment_Submissions TO assignment_submissions;
RENAME TABLE Quizzes TO quizzes;
RENAME TABLE Questions TO questions;
RENAME TABLE Question_Options TO question_options;
RENAME TABLE Quiz_Questions TO quiz_questions;
RENAME TABLE Quiz_Attempts TO quiz_attempts;
RENAME TABLE Quiz_Answers TO quiz_answers;
RENAME TABLE Announcements TO announcements;
RENAME TABLE Discussions TO discussions;
RENAME TABLE Discussion_Replies TO discussion_replies;
RENAME TABLE Messages TO messages;
RENAME TABLE Notifications TO notifications;
RENAME TABLE Certificates TO certificates;
RENAME TABLE Badges TO badges;
RENAME TABLE Student_Achievements TO student_achievements;
RENAME TABLE Payment_Methods TO payment_methods;
RENAME TABLE Payments TO payments;
RENAME TABLE Transactions TO transactions;
RENAME TABLE Activity_Logs TO activity_logs;
RENAME TABLE System_Settings TO system_settings;
RENAME TABLE Email_Templates TO email_templates;

-- ----------------------------------------------------------------------------
-- STEP 2: Rename primary key columns to 'id'
-- ----------------------------------------------------------------------------

-- Users table
ALTER TABLE users CHANGE COLUMN user_id id INT AUTO_INCREMENT;

-- Roles table
ALTER TABLE roles CHANGE COLUMN role_id id INT AUTO_INCREMENT;

-- User_Roles table
ALTER TABLE user_roles CHANGE COLUMN user_role_id id INT AUTO_INCREMENT;

-- Course_Categories table
ALTER TABLE course_categories CHANGE COLUMN category_id id INT AUTO_INCREMENT;

-- Instructors table
ALTER TABLE instructors CHANGE COLUMN instructor_id id INT AUTO_INCREMENT;

-- Courses table
ALTER TABLE courses CHANGE COLUMN course_id id INT AUTO_INCREMENT;

-- Students table
ALTER TABLE students CHANGE COLUMN student_id id INT AUTO_INCREMENT;

-- Course_Instructors table
ALTER TABLE course_instructors CHANGE COLUMN course_instructor_id id INT AUTO_INCREMENT;

-- Enrollments table
ALTER TABLE enrollments CHANGE COLUMN enrollment_id id INT AUTO_INCREMENT;

-- Modules table
ALTER TABLE modules CHANGE COLUMN module_id id INT AUTO_INCREMENT;

-- Lessons table
ALTER TABLE lessons CHANGE COLUMN lesson_id id INT AUTO_INCREMENT;

-- Lesson_Resources table
ALTER TABLE lesson_resources CHANGE COLUMN resource_id id INT AUTO_INCREMENT;

-- Lesson_Progress table
ALTER TABLE lesson_progress CHANGE COLUMN progress_id id INT AUTO_INCREMENT;

-- Assignments table
ALTER TABLE assignments CHANGE COLUMN assignment_id id INT AUTO_INCREMENT;

-- Assignment_Submissions table
ALTER TABLE assignment_submissions CHANGE COLUMN submission_id id INT AUTO_INCREMENT;

-- Quizzes table
ALTER TABLE quizzes CHANGE COLUMN quiz_id id INT AUTO_INCREMENT;

-- Questions table
ALTER TABLE questions CHANGE COLUMN question_id id INT AUTO_INCREMENT;

-- Question_Options table
ALTER TABLE question_options CHANGE COLUMN option_id id INT AUTO_INCREMENT;

-- Quiz_Questions table
ALTER TABLE quiz_questions CHANGE COLUMN quiz_question_id id INT AUTO_INCREMENT;

-- Quiz_Attempts table
ALTER TABLE quiz_attempts CHANGE COLUMN attempt_id id INT AUTO_INCREMENT;

-- Quiz_Answers table
ALTER TABLE quiz_answers CHANGE COLUMN answer_id id INT AUTO_INCREMENT;

-- Announcements table
ALTER TABLE announcements CHANGE COLUMN announcement_id id INT AUTO_INCREMENT;

-- Discussions table
ALTER TABLE discussions CHANGE COLUMN discussion_id id INT AUTO_INCREMENT;

-- Discussion_Replies table
ALTER TABLE discussion_replies CHANGE COLUMN reply_id id INT AUTO_INCREMENT;

-- Messages table
ALTER TABLE messages CHANGE COLUMN message_id id INT AUTO_INCREMENT;

-- Notifications table
ALTER TABLE notifications CHANGE COLUMN notification_id id INT AUTO_INCREMENT;

-- Certificates table
ALTER TABLE certificates CHANGE COLUMN certificate_id id INT AUTO_INCREMENT;

-- Badges table
ALTER TABLE badges CHANGE COLUMN badge_id id INT AUTO_INCREMENT;

-- Student_Achievements table
ALTER TABLE student_achievements CHANGE COLUMN achievement_id id INT AUTO_INCREMENT;

-- Payment_Methods table
ALTER TABLE payment_methods CHANGE COLUMN payment_method_id id INT AUTO_INCREMENT;

-- Payments table
ALTER TABLE payments CHANGE COLUMN payment_id id INT AUTO_INCREMENT;

-- Transactions table
ALTER TABLE transactions CHANGE COLUMN transaction_id id INT AUTO_INCREMENT;

-- Activity_Logs table
ALTER TABLE activity_logs CHANGE COLUMN log_id id BIGINT AUTO_INCREMENT;

-- System_Settings table
ALTER TABLE system_settings CHANGE COLUMN setting_id id INT AUTO_INCREMENT;

-- Email_Templates table
ALTER TABLE email_templates CHANGE COLUMN template_id id INT AUTO_INCREMENT;

-- ----------------------------------------------------------------------------
-- STEP 3: Rename category_name to name (for compatibility)
-- ----------------------------------------------------------------------------

ALTER TABLE course_categories CHANGE COLUMN category_name name VARCHAR(100) NOT NULL;

-- ----------------------------------------------------------------------------
-- STEP 4: Rename password_hash to password in users table
-- ----------------------------------------------------------------------------

ALTER TABLE users CHANGE COLUMN password_hash password VARCHAR(255) NOT NULL;

-- ----------------------------------------------------------------------------
-- STEP 5: Add instructor_id to courses table (for simplified queries)
-- ----------------------------------------------------------------------------

-- Add the column
ALTER TABLE courses ADD COLUMN instructor_id INT NULL AFTER category_id;

-- Update with lead instructor from course_instructors
UPDATE courses c
SET instructor_id = (
    SELECT ci.instructor_id
    FROM course_instructors ci
    WHERE ci.course_id = c.id AND ci.role = 'Lead'
    LIMIT 1
);

-- Add foreign key
ALTER TABLE courses
ADD CONSTRAINT fk_courses_instructor
FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE SET NULL;

-- ----------------------------------------------------------------------------
-- STEP 6: Rename difficulty_level to level in courses table
-- ----------------------------------------------------------------------------

ALTER TABLE courses CHANGE COLUMN difficulty_level level ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL DEFAULT 'Beginner';

-- ----------------------------------------------------------------------------
-- STEP 7: Create user_profiles table (compatibility layer)
-- ----------------------------------------------------------------------------

CREATE TABLE user_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say'),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate user_profiles from students table
INSERT INTO user_profiles (user_id, date_of_birth, gender, address, city, country, postal_code)
SELECT
    s.user_id,
    s.date_of_birth,
    s.gender,
    s.address,
    s.city,
    s.country,
    s.postal_code
FROM students s;

-- Populate user_profiles from instructors table (for instructors not already in profiles)
INSERT INTO user_profiles (user_id, bio, phone)
SELECT
    i.user_id,
    i.bio,
    (SELECT phone FROM users WHERE id = i.user_id)
FROM instructors i
WHERE i.user_id NOT IN (SELECT user_id FROM user_profiles);

-- ----------------------------------------------------------------------------
-- STEP 8: Add user_id to enrollments (in addition to student_id)
-- ----------------------------------------------------------------------------

ALTER TABLE enrollments ADD COLUMN user_id INT NULL AFTER id;

-- Populate user_id from student_id
UPDATE enrollments e
INNER JOIN students s ON e.student_id = s.id
SET e.user_id = s.user_id;

-- Make it NOT NULL now that it's populated
ALTER TABLE enrollments MODIFY COLUMN user_id INT NOT NULL;

-- Add index for performance
ALTER TABLE enrollments ADD INDEX idx_user_id (user_id);

-- Add foreign key
ALTER TABLE enrollments
ADD CONSTRAINT fk_enrollments_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- ----------------------------------------------------------------------------
-- STEP 9: Rename enrollment columns for compatibility
-- ----------------------------------------------------------------------------

ALTER TABLE enrollments CHANGE COLUMN enrollment_date enrolled_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE enrollments CHANGE COLUMN status enrollment_status ENUM('Enrolled', 'In Progress', 'Completed', 'Dropped', 'Expired') DEFAULT 'Enrolled';

-- Add missing columns expected by application
ALTER TABLE enrollments ADD COLUMN payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending' AFTER enrollment_status;
ALTER TABLE enrollments ADD COLUMN amount_paid DECIMAL(10, 2) DEFAULT 0.00 AFTER payment_status;

-- Populate payment_status and amount_paid from payments table
UPDATE enrollments e
LEFT JOIN payments p ON e.id = p.enrollment_id AND p.payment_status = 'Completed'
SET
    e.payment_status = CASE
        WHEN p.payment_status = 'Completed' THEN 'completed'
        WHEN p.payment_status = 'Pending' THEN 'pending'
        WHEN p.payment_status = 'Failed' THEN 'failed'
        WHEN p.payment_status = 'Refunded' THEN 'refunded'
        ELSE 'pending'
    END,
    e.amount_paid = COALESCE(p.amount, 0);

-- ----------------------------------------------------------------------------
-- STEP 10: Create course_reviews table (for ratings functionality)
-- ----------------------------------------------------------------------------

CREATE TABLE course_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    rating DECIMAL(2, 1) NOT NULL CHECK (rating >= 0 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_course_review (user_id, course_id),
    INDEX idx_course (course_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- STEP 11: Update courses table status values to lowercase
-- ----------------------------------------------------------------------------

UPDATE courses SET status = LOWER(status);
ALTER TABLE courses MODIFY COLUMN status ENUM('draft', 'published', 'archived', 'under_review') DEFAULT 'draft';

-- ----------------------------------------------------------------------------
-- STEP 12: Add course slug if missing
-- ----------------------------------------------------------------------------

-- Check if slug column exists, if not add it
-- ALTER TABLE courses ADD COLUMN slug VARCHAR(250) NOT NULL UNIQUE AFTER title;

-- Generate slugs for existing courses (convert title to slug format)
UPDATE courses
SET slug = LOWER(REPLACE(REPLACE(REPLACE(title, ' ', '-'), '&', 'and'), '  ', '-'))
WHERE slug IS NULL OR slug = '';

-- ----------------------------------------------------------------------------
-- STEP 13: Rename foreign key columns in related tables
-- ----------------------------------------------------------------------------

-- Update all tables to use simple id references
ALTER TABLE user_roles CHANGE COLUMN user_id user_id INT NOT NULL;
ALTER TABLE user_roles CHANGE COLUMN role_id role_id INT NOT NULL;

ALTER TABLE courses CHANGE COLUMN category_id category_id INT NOT NULL;

ALTER TABLE course_instructors CHANGE COLUMN course_id course_id INT NOT NULL;
ALTER TABLE course_instructors CHANGE COLUMN instructor_id instructor_id INT NOT NULL;

ALTER TABLE enrollments CHANGE COLUMN course_id course_id INT NOT NULL;

ALTER TABLE modules CHANGE COLUMN course_id course_id INT NOT NULL;

ALTER TABLE lessons CHANGE COLUMN module_id module_id INT NOT NULL;

-- ----------------------------------------------------------------------------
-- STEP 14: Update ENUM values to lowercase where needed
-- ----------------------------------------------------------------------------

-- Course status is already updated above

-- Enrollment status
UPDATE enrollments SET enrollment_status = LOWER(enrollment_status);

-- ----------------------------------------------------------------------------
-- Verification Query
-- ----------------------------------------------------------------------------

-- Verify table structures
SELECT
    'Tables Renamed' as step,
    COUNT(*) as count
FROM information_schema.tables
WHERE table_schema = DATABASE()
AND table_name IN ('users', 'courses', 'enrollments', 'course_categories');

-- Verify column renames
SELECT
    'Primary Keys Renamed' as step,
    COUNT(*) as count
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND column_name = 'id'
AND column_key = 'PRI';

-- ============================================================================
-- NOTES FOR DEVELOPERS
-- ============================================================================

/*
After running this script, the database will be compatible with the existing
application code. The following changes were made:

1. All table names converted to lowercase
2. All primary keys renamed to 'id'
3. course_categories.category_name → name
4. users.password_hash → password
5. courses.difficulty_level → level
6. Added courses.instructor_id for simplified queries
7. Created user_profiles compatibility table
8. Added enrollments.user_id alongside student_id
9. Renamed enrollments.enrollment_date → enrolled_at
10. Renamed enrollments.status → enrollment_status
11. Added payment fields to enrollments table
12. Created course_reviews table
13. Updated status ENUM values to lowercase
14. Added course slug generation

The database now maintains both the new schema's normalized structure AND
compatibility with the existing application code.

IMPORTANT:
- This is a compatibility layer approach
- Consider refactoring the application code to use the proper normalized schema
- This script maintains data integrity while allowing the app to function
*/

-- ============================================================================
-- END OF COMPATIBILITY FIX
-- ============================================================================
