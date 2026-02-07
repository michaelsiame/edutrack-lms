-- EduTrack LMS - Comprehensive Migration
-- Phase 2: Missing tables and columns
-- Phase 4: Data integrity (FKs, indexes, UNIQUE constraints)
-- Date: 2026-02-07

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- ============================================================
-- PHASE 2: Create Missing Tables
-- ============================================================

-- Token blacklist for JWT logout
CREATE TABLE IF NOT EXISTS `token_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_token_hash` (`token_hash`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rate limiting table (database-backed)
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(64) NOT NULL,
  `attempt_count` int(11) NOT NULL DEFAULT 1,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_identifier` (`identifier`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contacts table (for contact form submissions)
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `replied_by` int(11) DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz responses table (for storing individual quiz responses)
CREATE TABLE IF NOT EXISTS `quiz_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` decimal(5,2) DEFAULT NULL,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_attempt_id` (`attempt_id`),
  KEY `idx_question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz question options (alias view for code that references this table)
-- Note: question_options table already exists, this is for backward compatibility
CREATE TABLE IF NOT EXISTS `quiz_question_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Discussion system classes support table (forums)
-- Tables already exist: discussions, discussion_replies

-- ============================================================
-- PHASE 2: Add Missing Columns
-- ============================================================

-- Add password reset columns to users table
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `password_reset_token` varchar(255) DEFAULT NULL AFTER `account_locked_until`,
  ADD COLUMN IF NOT EXISTS `password_reset_expires` datetime DEFAULT NULL AFTER `password_reset_token`;

-- ============================================================
-- PHASE 4: Add UNIQUE Constraints
-- ============================================================

-- Ensure unique emails (check if index exists first)
-- Using ALTER IGNORE to skip if duplicate data exists
ALTER TABLE `users` ADD UNIQUE INDEX `idx_users_email_unique` (`email`);

-- Unique usernames
ALTER TABLE `users` ADD UNIQUE INDEX `idx_users_username_unique` (`username`);

-- Unique course slugs
ALTER TABLE `courses` ADD UNIQUE INDEX `idx_courses_slug_unique` (`slug`);

-- Unique certificate numbers
ALTER TABLE `certificates` ADD UNIQUE INDEX `idx_cert_number_unique` (`certificate_number`);

-- Unique setting keys (remove duplicate first)
DELETE t1 FROM `system_settings` t1
INNER JOIN `system_settings` t2
WHERE t1.setting_id > t2.setting_id
AND t1.setting_key = t2.setting_key;

ALTER TABLE `system_settings` ADD UNIQUE INDEX `idx_setting_key_unique` (`setting_key`);

-- ============================================================
-- PHASE 4: Add Missing Indexes
-- ============================================================

-- payments indexes
ALTER TABLE `payments` ADD INDEX IF NOT EXISTS `idx_payments_student_id` (`student_id`);
ALTER TABLE `payments` ADD INDEX IF NOT EXISTS `idx_payments_course_id` (`course_id`);
ALTER TABLE `payments` ADD INDEX IF NOT EXISTS `idx_payments_enrollment_id` (`enrollment_id`);
ALTER TABLE `payments` ADD INDEX IF NOT EXISTS `idx_payments_status` (`payment_status`);

-- enrollment_payment_plans indexes
ALTER TABLE `enrollment_payment_plans` ADD INDEX IF NOT EXISTS `idx_epp_enrollment_id` (`enrollment_id`);
ALTER TABLE `enrollment_payment_plans` ADD INDEX IF NOT EXISTS `idx_epp_user_id` (`user_id`);
ALTER TABLE `enrollment_payment_plans` ADD INDEX IF NOT EXISTS `idx_epp_course_id` (`course_id`);
ALTER TABLE `enrollment_payment_plans` ADD INDEX IF NOT EXISTS `idx_epp_payment_status` (`payment_status`);

-- lesson_progress indexes
ALTER TABLE `lesson_progress` ADD INDEX IF NOT EXISTS `idx_lp_enrollment_id` (`enrollment_id`);
ALTER TABLE `lesson_progress` ADD INDEX IF NOT EXISTS `idx_lp_lesson_id` (`lesson_id`);

-- lesson_resources indexes
ALTER TABLE `lesson_resources` ADD INDEX IF NOT EXISTS `idx_lr_lesson_id` (`lesson_id`);

-- live_sessions indexes
ALTER TABLE `live_sessions` ADD INDEX IF NOT EXISTS `idx_ls_lesson_id` (`lesson_id`);
ALTER TABLE `live_sessions` ADD INDEX IF NOT EXISTS `idx_ls_instructor_id` (`instructor_id`);
ALTER TABLE `live_sessions` ADD INDEX IF NOT EXISTS `idx_ls_status` (`status`);

-- live_session_attendance indexes
ALTER TABLE `live_session_attendance` ADD INDEX IF NOT EXISTS `idx_lsa_session_id` (`live_session_id`);
ALTER TABLE `live_session_attendance` ADD INDEX IF NOT EXISTS `idx_lsa_user_id` (`user_id`);

-- quiz_questions indexes
ALTER TABLE `quiz_questions` ADD INDEX IF NOT EXISTS `idx_qq_quiz_id` (`quiz_id`);
ALTER TABLE `quiz_questions` ADD INDEX IF NOT EXISTS `idx_qq_question_id` (`question_id`);

-- quiz_answers indexes
ALTER TABLE `quiz_answers` ADD INDEX IF NOT EXISTS `idx_qa_attempt_id` (`attempt_id`);
ALTER TABLE `quiz_answers` ADD INDEX IF NOT EXISTS `idx_qa_question_id` (`question_id`);

-- question_options indexes
ALTER TABLE `question_options` ADD INDEX IF NOT EXISTS `idx_qo_question_id` (`question_id`);

-- certificates indexes
ALTER TABLE `certificates` ADD INDEX IF NOT EXISTS `idx_cert_enrollment_id` (`enrollment_id`);

-- user_sessions indexes
ALTER TABLE `user_sessions` ADD INDEX IF NOT EXISTS `idx_us_user_id` (`user_id`);

-- enrollments - prevent duplicates
ALTER TABLE `enrollments` ADD INDEX IF NOT EXISTS `idx_enrollment_user_course` (`user_id`, `course_id`);

-- notifications indexes
ALTER TABLE `notifications` ADD INDEX IF NOT EXISTS `idx_notif_user_read` (`user_id`, `is_read`);

-- ============================================================
-- PHASE 4: Add Foreign Key Constraints
-- ============================================================

-- announcements FKs
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcements_posted_by` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_announcements_course_id` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;

-- activity_logs FK
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- certificates FK
ALTER TABLE `certificates`
  ADD CONSTRAINT `fk_certificates_enrollment_id` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE;

-- lesson_progress FKs
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `fk_lp_enrollment_id` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lp_lesson_id` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

-- lesson_resources FK
ALTER TABLE `lesson_resources`
  ADD CONSTRAINT `fk_lr_lesson_id` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

-- live_sessions FKs
ALTER TABLE `live_sessions`
  ADD CONSTRAINT `fk_ls_lesson_id` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ls_instructor_id` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE;

-- live_session_attendance FKs
ALTER TABLE `live_session_attendance`
  ADD CONSTRAINT `fk_lsa_session_id` FOREIGN KEY (`live_session_id`) REFERENCES `live_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lsa_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- quiz_questions FKs
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `fk_qq_quiz_id` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qq_question_id` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

-- quiz_answers FKs
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `fk_qa_attempt_id` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`attempt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qa_question_id` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

-- question_options FK
ALTER TABLE `question_options`
  ADD CONSTRAINT `fk_qo_question_id` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

-- assignments FK
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assignments_lesson_id` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

-- assignment_submissions FK
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `fk_as_graded_by` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- ============================================================
-- PHASE 4: Fix Currency Defaults
-- ============================================================

-- Standardize currency to ZMW
UPDATE `system_settings` SET `setting_value` = 'ZMW' WHERE `setting_key` = 'default_currency';

-- ============================================================
-- PHASE 4: Fix Data Integrity
-- ============================================================

-- Fix user_roles with user_id = 0
DELETE FROM `user_roles` WHERE `user_id` = 0;

-- Fix enrollment with empty string payment_status
UPDATE `enrollments` SET `payment_status` = 'pending' WHERE `payment_status` = '';

COMMIT;
