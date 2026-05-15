-- =============================================================================
-- Migration: 006_performance_indexes_and_schema_fixes.sql
-- Purpose: Fix schema bugs, add missing indexes, and remove duplicates
--          to resolve slow page loads across the LMS
-- =============================================================================

-- =============================================================================
-- 1. CRITICAL SCHEMA FIX: lesson_progress missing user_id / course_id
-- =============================================================================

-- Add user_id column (nullable for backward compatibility during transition)
SET @exists := (SELECT COUNT(*) FROM information_schema.columns
                WHERE table_schema = DATABASE()
                AND table_name = 'lesson_progress'
                AND column_name = 'user_id');
SET @sql := IF(@exists = 0,
    'ALTER TABLE lesson_progress ADD COLUMN user_id INT(11) NULL AFTER enrollment_id, ADD COLUMN course_id INT(11) NULL AFTER user_id',
    'SELECT "user_id already exists" as msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index on user_id (used by Lesson::isCompletedByUser, Lesson::getUserProgress, etc.)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'lesson_progress'
                AND index_name = 'idx_lp_user_id');
SET @sql := IF(@exists = 0,
    'ALTER TABLE lesson_progress ADD INDEX idx_lp_user_id (user_id)',
    'SELECT "idx_lp_user_id already exists" as msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index for enrollment_id + lesson_id lookups
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'lesson_progress'
                AND index_name = 'idx_lp_enrollment_lesson');
SET @sql := IF(@exists = 0,
    'ALTER TABLE lesson_progress ADD INDEX idx_lp_enrollment_lesson (enrollment_id, lesson_id)',
    'SELECT "idx_lp_enrollment_lesson already exists" as msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index for user_id + lesson_id lookups (API endpoints)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'lesson_progress'
                AND index_name = 'idx_lp_user_lesson');
SET @sql := IF(@exists = 0,
    'ALTER TABLE lesson_progress ADD INDEX idx_lp_user_lesson (user_id, lesson_id)',
    'SELECT "idx_lp_user_lesson already exists" as msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- =============================================================================
-- 2. ADD MISSING INDEXES
-- =============================================================================

-- users.status (dashboard stats, admin filtering)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'users' AND index_name = 'idx_users_status');
SET @sql := IF(@exists = 0, 'ALTER TABLE users ADD INDEX idx_users_status (status)', 'SELECT "idx_users_status already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- users.password_reset_token (password reset flow)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'users' AND index_name = 'idx_users_reset_token');
SET @sql := IF(@exists = 0, 'ALTER TABLE users ADD INDEX idx_users_reset_token (password_reset_token)', 'SELECT "idx_users_reset_token already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- users.created_at (monthly reports)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'users' AND index_name = 'idx_users_created_at');
SET @sql := IF(@exists = 0, 'ALTER TABLE users ADD INDEX idx_users_created_at (created_at)', 'SELECT "idx_users_created_at already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- enrollments.user_id + status (dashboard filtering)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'enrollments' AND index_name = 'idx_enrollments_user_status');
SET @sql := IF(@exists = 0, 'ALTER TABLE enrollments ADD INDEX idx_enrollments_user_status (user_id, enrollment_status)', 'SELECT "idx_enrollments_user_status already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- courses.status + is_featured (homepage featured courses)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'courses' AND index_name = 'idx_courses_status_featured');
SET @sql := IF(@exists = 0, 'ALTER TABLE courses ADD INDEX idx_courses_status_featured (status, is_featured)', 'SELECT "idx_courses_status_featured already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- quiz_attempts.quiz_id + student_id
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'quiz_attempts' AND index_name = 'idx_qa_quiz_student');
SET @sql := IF(@exists = 0, 'ALTER TABLE quiz_attempts ADD INDEX idx_qa_quiz_student (quiz_id, student_id)', 'SELECT "idx_qa_quiz_student already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- quiz_attempts.status
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'quiz_attempts' AND index_name = 'idx_qa_status');
SET @sql := IF(@exists = 0, 'ALTER TABLE quiz_attempts ADD INDEX idx_qa_status (status)', 'SELECT "idx_qa_status already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- quiz_answers.attempt_id + question_id
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'quiz_answers' AND index_name = 'idx_qans_attempt_question');
SET @sql := IF(@exists = 0, 'ALTER TABLE quiz_answers ADD INDEX idx_qans_attempt_question (attempt_id, question_id)', 'SELECT "idx_qans_attempt_question already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- assignment_submissions.assignment_id + student_id
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'assignment_submissions' AND index_name = 'idx_asub_assignment_student');
SET @sql := IF(@exists = 0, 'ALTER TABLE assignment_submissions ADD INDEX idx_asub_assignment_student (assignment_id, student_id)', 'SELECT "idx_asub_assignment_student already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- assignment_submissions.status
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'assignment_submissions' AND index_name = 'idx_asub_status');
SET @sql := IF(@exists = 0, 'ALTER TABLE assignment_submissions ADD INDEX idx_asub_status (status)', 'SELECT "idx_asub_status already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- transactions.payment_id
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'transactions' AND index_name = 'idx_trans_payment_id');
SET @sql := IF(@exists = 0, 'ALTER TABLE transactions ADD INDEX idx_trans_payment_id (payment_id)', 'SELECT "idx_trans_payment_id already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- transactions.transaction_type
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'transactions' AND index_name = 'idx_trans_type');
SET @sql := IF(@exists = 0, 'ALTER TABLE transactions ADD INDEX idx_trans_type (transaction_type)', 'SELECT "idx_trans_type already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- live_session_attendance.live_session_id
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'live_session_attendance' AND index_name = 'idx_lsa_session');
SET @sql := IF(@exists = 0, 'ALTER TABLE live_session_attendance ADD INDEX idx_lsa_session (live_session_id)', 'SELECT "idx_lsa_session already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- live_session_attendance.user_id
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'live_session_attendance' AND index_name = 'idx_lsa_user');
SET @sql := IF(@exists = 0, 'ALTER TABLE live_session_attendance ADD INDEX idx_lsa_user (user_id)', 'SELECT "idx_lsa_user already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- registration_fees.user_id
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'registration_fees' AND index_name = 'idx_rf_user_id');
SET @sql := IF(@exists = 0, 'ALTER TABLE registration_fees ADD INDEX idx_rf_user_id (user_id)', 'SELECT "idx_rf_user_id already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- registration_fees.payment_status
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'registration_fees' AND index_name = 'idx_rf_payment_status');
SET @sql := IF(@exists = 0, 'ALTER TABLE registration_fees ADD INDEX idx_rf_payment_status (payment_status)', 'SELECT "idx_rf_payment_status already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- user_sessions.user_id
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'user_sessions' AND index_name = 'idx_us_user_id');
SET @sql := IF(@exists = 0, 'ALTER TABLE user_sessions ADD INDEX idx_us_user_id (user_id)', 'SELECT "idx_us_user_id already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- user_sessions.session_token
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'user_sessions' AND index_name = 'idx_us_token');
SET @sql := IF(@exists = 0, 'ALTER TABLE user_sessions ADD INDEX idx_us_token (session_token)', 'SELECT "idx_us_token already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- user_sessions.expires_at
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'user_sessions' AND index_name = 'idx_us_expires');
SET @sql := IF(@exists = 0, 'ALTER TABLE user_sessions ADD INDEX idx_us_expires (expires_at)', 'SELECT "idx_us_expires already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- system_settings.setting_key
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'system_settings' AND index_name = 'idx_ss_key');
SET @sql := IF(@exists = 0, 'ALTER TABLE system_settings ADD INDEX idx_ss_key (setting_key)', 'SELECT "idx_ss_key already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- announcements.is_published + expires_at
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'announcements' AND index_name = 'idx_announce_published_expires');
SET @sql := IF(@exists = 0, 'ALTER TABLE announcements ADD INDEX idx_announce_published_expires (is_published, expires_at)', 'SELECT "idx_announce_published_expires already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- rate_limits.expires_at (cleanup queries)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'rate_limits' AND index_name = 'idx_rl_expires');
SET @sql := IF(@exists = 0, 'ALTER TABLE rate_limits ADD INDEX idx_rl_expires (expires_at)', 'SELECT "idx_rl_expires already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- =============================================================================
-- 3. ADD UNIQUE CONSTRAINTS
-- =============================================================================

-- Prevent duplicate course reviews
SET @exists := (SELECT COUNT(*) FROM information_schema.table_constraints
                WHERE table_schema = DATABASE() AND table_name = 'course_reviews' AND constraint_name = 'uk_course_reviews_course_user');
SET @sql := IF(@exists = 0,
    'ALTER TABLE course_reviews ADD CONSTRAINT uk_course_reviews_course_user UNIQUE (course_id, user_id)',
    'SELECT "uk_course_reviews_course_user already exists" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- =============================================================================
-- 4. REMOVE DUPLICATE INDEXES (waste disk space and slow writes)
-- =============================================================================

-- assignments: drop idx_assignments_course (keep idx_assign_course)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'assignments' AND index_name = 'idx_assignments_course');
SET @sql := IF(@exists = 1, 'ALTER TABLE assignments DROP INDEX idx_assignments_course', 'SELECT "idx_assignments_course not found" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- quizzes: drop idx_quizzes_course (keep idx_quiz_course)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'quizzes' AND index_name = 'idx_quizzes_course');
SET @sql := IF(@exists = 1, 'ALTER TABLE quizzes DROP INDEX idx_quizzes_course', 'SELECT "idx_quizzes_course not found" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- lenco_transactions: drop idx_lt_user (keep idx_user_id)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'lenco_transactions' AND index_name = 'idx_lt_user');
SET @sql := IF(@exists = 1, 'ALTER TABLE lenco_transactions DROP INDEX idx_lt_user', 'SELECT "idx_lt_user not found" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- lenco_transactions: drop idx_lt_enroll (keep idx_enrollment_id)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'lenco_transactions' AND index_name = 'idx_lt_enroll');
SET @sql := IF(@exists = 1, 'ALTER TABLE lenco_transactions DROP INDEX idx_lt_enroll', 'SELECT "idx_lt_enroll not found" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- lenco_transactions: drop idx_lt_course (keep idx_course_id)
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = 'lenco_transactions' AND index_name = 'idx_lt_course');
SET @sql := IF(@exists = 1, 'ALTER TABLE lenco_transactions DROP INDEX idx_lt_course', 'SELECT "idx_lt_course not found" as msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
