-- ============================================================================
-- Edutrack LMS Schema Fixes Migration
-- Date: 2026-04-09
-- Issues Addressed: AUTO_INCREMENT gaps, missing UNIQUE/FK/indexes, 
--                   certificates table mismatch, payments currency, 
--                   redundant indexes, quiz table fixes
-- 
-- IMPORTANT: Run this in a transaction and TEST on a backup first.
-- Some fixes require data migration (certificates, courses.id=0).
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

START TRANSACTION;

-- ============================================================================
-- 1. CRITICAL: Add missing AUTO_INCREMENT
-- ============================================================================

-- quiz_answers.answer_id has PRIMARY KEY but no AUTO_INCREMENT
ALTER TABLE `quiz_answers`
    MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

-- ============================================================================
-- 2. CRITICAL: Ensure quiz_attempts.id has AUTO_INCREMENT
-- ============================================================================
-- The schema dump showed a redundant `attempt_id` column, but production
-- may already have the clean schema. Just ensure `id` is the auto-increment PK.
ALTER TABLE `quiz_attempts`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- ============================================================================
-- 3. CRITICAL: Create quiz_question_options table (code expects it)
-- ============================================================================
-- The codebase (student/take-quiz.php) queries `quiz_question_options` but the
-- schema only has `question_options`. Create the expected table and migrate data.
CREATE TABLE IF NOT EXISTS `quiz_question_options` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `question_id` int(11) NOT NULL,
    `option_text` text NOT NULL,
    `is_correct` tinyint(1) DEFAULT 0,
    `display_order` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_qqo_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing data from question_options if quiz_question_options is empty
INSERT INTO `quiz_question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT `question_id`, `option_text`, `is_correct`, `display_order`
FROM `question_options`
WHERE NOT EXISTS (SELECT 1 FROM `quiz_question_options` LIMIT 1);

-- ============================================================================
-- 4. CRITICAL: Fix certificates table to match Certificate.php code expectations
-- ============================================================================
-- The code expects: id, user_id, course_id, certificate_number, verification_code,
--                   final_score, issued_at
-- The schema has:   certificate_id, enrollment_id, certificate_number, issued_date,
--                   verification_code, certificate_url, is_verified, expiry_date, created_at

-- Add missing columns (keep existing ones for backward compatibility)
ALTER TABLE `certificates`
    ADD COLUMN `user_id` int(11) DEFAULT NULL AFTER `certificate_id`,
    ADD COLUMN `course_id` int(11) DEFAULT NULL AFTER `user_id`,
    ADD COLUMN `final_score` decimal(5,2) DEFAULT 0 AFTER `verification_code`,
    ADD COLUMN `issued_at` datetime DEFAULT NULL AFTER `final_score`,
    ADD KEY `idx_cert_user` (`user_id`),
    ADD KEY `idx_cert_course` (`course_id`);

-- Migrate data: populate user_id and course_id from enrollments table
UPDATE `certificates` c
JOIN `enrollments` e ON c.enrollment_id = e.id
SET c.user_id = e.user_id,
    c.course_id = e.course_id,
    c.issued_at = CONCAT(c.issued_date, ' 00:00:00')
WHERE c.user_id IS NULL;

-- ============================================================================
-- 5. HIGH: Add missing UNIQUE constraints
-- ============================================================================

-- Prevent duplicate email addresses
ALTER TABLE `users`
    ADD UNIQUE KEY `uk_users_email` (`email`);

-- Prevent duplicate usernames
ALTER TABLE `users`
    ADD UNIQUE KEY `uk_users_username` (`username`);

-- One user = one student record
ALTER TABLE `students`
    ADD UNIQUE KEY `uk_students_user` (`user_id`);

-- One user = one instructor record
ALTER TABLE `instructors`
    ADD UNIQUE KEY `uk_instructors_user` (`user_id`);

-- One enrollment = one payment plan
-- First, deduplicate: keep the plan with the most payments per enrollment
UPDATE payments p
JOIN (
    SELECT enrollment_id,
           SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY total_paid DESC, updated_at DESC, id ASC SEPARATOR ','), ',', 1) + 0 AS keeper_id
    FROM enrollment_payment_plans
    GROUP BY enrollment_id
    HAVING COUNT(*) > 1
) k ON p.enrollment_id = k.enrollment_id
SET p.payment_plan_id = k.keeper_id
WHERE p.payment_plan_id != k.keeper_id;

DELETE epp FROM enrollment_payment_plans epp
JOIN (
    SELECT enrollment_id,
           SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY total_paid DESC, updated_at DESC, id ASC SEPARATOR ','), ',', 1) + 0 AS keeper_id
    FROM enrollment_payment_plans
    GROUP BY enrollment_id
    HAVING COUNT(*) > 1
) k ON epp.enrollment_id = k.enrollment_id
WHERE epp.id != k.keeper_id;

ALTER TABLE `enrollment_payment_plans`
    ADD UNIQUE KEY `uk_epp_enrollment` (`enrollment_id`);

-- Certificate numbers must be globally unique
ALTER TABLE `certificates`
    ADD UNIQUE KEY `uk_cert_number` (`certificate_number`);

-- Verification codes must be globally unique
ALTER TABLE `certificates`
    ADD UNIQUE KEY `uk_cert_verify` (`verification_code`);

-- Prevent assigning same question to same quiz twice
ALTER TABLE `quiz_questions`
    ADD UNIQUE KEY `uk_qq_quiz_question` (`quiz_id`, `question_id`);

-- Prevent duplicate role assignments
ALTER TABLE `user_roles`
    ADD UNIQUE KEY `uk_user_role` (`user_id`, `role_id`);

-- Prevent duplicate transaction IDs in payments
ALTER TABLE `payments`
    ADD UNIQUE KEY `uk_payments_txn` (`transaction_id`);

-- ============================================================================
-- 6. HIGH: Add missing foreign keys
-- ============================================================================

-- certificates -> users, courses
ALTER TABLE `certificates`
    ADD CONSTRAINT `fk_cert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_cert_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

-- question_options -> questions
ALTER TABLE `question_options`
    ADD CONSTRAINT `fk_opt_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

-- quiz_questions -> quizzes, questions
ALTER TABLE `quiz_questions`
    ADD CONSTRAINT `fk_qq_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_qq_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

-- quiz_answers -> quiz_attempts, questions
ALTER TABLE `quiz_answers`
    ADD CONSTRAINT `fk_qa_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_qa_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

-- quiz_question_options -> questions
ALTER TABLE `quiz_question_options`
    ADD CONSTRAINT `fk_qqo_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

-- announcements -> users (posted_by)
ALTER TABLE `announcements`
    ADD CONSTRAINT `fk_ann_poster` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- payments -> students, courses, enrollments, payment_methods
ALTER TABLE `payments`
    ADD CONSTRAINT `fk_pay_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_pay_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_pay_enroll` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_pay_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`) ON DELETE SET NULL;

-- lesson_progress -> users, lessons
ALTER TABLE `lesson_progress`
    ADD CONSTRAINT `fk_lp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_lp_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

-- lesson_resources -> lessons
ALTER TABLE `lesson_resources`
    ADD CONSTRAINT `fk_lr_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

-- live_sessions -> courses, instructors
ALTER TABLE `live_sessions`
    ADD CONSTRAINT `fk_ls_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_ls_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE;

-- live_session_attendance -> live_sessions, users
ALTER TABLE `live_session_attendance`
    ADD CONSTRAINT `fk_lsa_session` FOREIGN KEY (`session_id`) REFERENCES `live_sessions` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_lsa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- email_queue -> users
ALTER TABLE `email_queue`
    ADD CONSTRAINT `fk_eq_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- registration_fees -> users
ALTER TABLE `registration_fees`
    ADD CONSTRAINT `fk_rf_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- transactions -> payments
ALTER TABLE `transactions`
    ADD CONSTRAINT `fk_txn_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE CASCADE;

-- ============================================================================
-- 7. HIGH: Add missing indexes on foreign key columns
-- ============================================================================

-- payments
ALTER TABLE `payments`
    ADD KEY `idx_pay_student` (`student_id`),
    ADD KEY `idx_pay_course` (`course_id`),
    ADD KEY `idx_pay_enroll` (`enrollment_id`),
    ADD KEY `idx_pay_plan` (`payment_plan_id`),
    ADD KEY `idx_pay_status` (`payment_status`);

-- certificates
ALTER TABLE `certificates`
    ADD KEY `idx_cert_enroll` (`enrollment_id`);

-- announcements
ALTER TABLE `announcements`
    ADD KEY `idx_ann_course` (`course_id`);

-- quiz_questions
ALTER TABLE `quiz_questions`
    ADD KEY `idx_qq_quiz` (`quiz_id`),
    ADD KEY `idx_qq_question` (`question_id`);

-- quiz_answers
ALTER TABLE `quiz_answers`
    ADD KEY `idx_qa_attempt` (`attempt_id`),
    ADD KEY `idx_qa_question` (`question_id`);

-- lesson_progress
ALTER TABLE `lesson_progress`
    ADD KEY `idx_lp_user` (`user_id`),
    ADD KEY `idx_lp_lesson` (`lesson_id`);

-- lesson_resources
ALTER TABLE `lesson_resources`
    ADD KEY `idx_lr_lesson` (`lesson_id`);

-- live_sessions
ALTER TABLE `live_sessions`
    ADD KEY `idx_ls_course` (`course_id`);

-- live_session_attendance
ALTER TABLE `live_session_attendance`
    ADD KEY `idx_lsa_session` (`session_id`),
    ADD KEY `idx_lsa_user` (`user_id`);

-- activity_logs
ALTER TABLE `activity_logs`
    ADD KEY `idx_al_user` (`user_id`),
    ADD KEY `idx_al_type` (`activity_type`),
    ADD KEY `idx_al_created` (`created_at`);

-- lenco_transactions
ALTER TABLE `lenco_transactions`
    ADD KEY `idx_lt_user` (`user_id`),
    ADD KEY `idx_lt_enroll` (`enrollment_id`),
    ADD KEY `idx_lt_course` (`course_id`);

-- ============================================================================
-- 8. MEDIUM: Fix payments.currency default to ZMW
-- ============================================================================
ALTER TABLE `payments`
    ALTER COLUMN `currency` SET DEFAULT 'ZMW';

-- Also update existing USD records that should be ZMW (if they have ZMW amounts)
UPDATE `payments`
SET `currency` = 'ZMW'
WHERE `currency` = 'USD' AND `amount` > 100;
-- Note: Adjust the amount threshold based on your data. ZMW course fees are typically >100.

-- ============================================================================
-- 9. MEDIUM: Add missing performance indexes
-- ============================================================================

-- courses.slug is used for public URL lookups
ALTER TABLE `courses`
    ADD UNIQUE KEY `idx_courses_slug` (`slug`);

-- users.phone is used for mobile money lookups
ALTER TABLE `users`
    ADD KEY `idx_users_phone` (`phone`);

-- enrollment_payment_plans lookup by user/course
ALTER TABLE `enrollment_payment_plans`
    ADD KEY `idx_epp_user` (`user_id`),
    ADD KEY `idx_epp_course` (`course_id`);

-- ============================================================================
-- 10. MEDIUM: Drop redundant indexes
-- ============================================================================

-- lenco_transactions has TWO unique indexes on the same column
ALTER TABLE `lenco_transactions`
    DROP INDEX `idx_reference`;

-- users.id already has PRIMARY KEY; the extra KEY is redundant
ALTER TABLE `users`
    DROP INDEX `id`;

-- ============================================================================
-- 11. LOW: Rename poorly named FK constraint
-- ============================================================================
ALTER TABLE `remember_tokens`
    DROP FOREIGN KEY `DE`;
ALTER TABLE `remember_tokens`
    ADD CONSTRAINT `fk_rt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ============================================================================
-- 12. CRITICAL: Fix courses.id = 0 (Cybersecurity course)
-- ============================================================================
-- IMPORTANT: This is a data migration. The course with id=0 must be reassigned
-- to a proper auto-incremented ID. All foreign key references must follow.
-- 
-- First, find the next available course ID
-- Then update courses.id = 0 to that new ID
-- Then update all referencing tables
-- 
-- Uncomment and run this section manually after verifying no new inserts are happening:

/*
SET @new_course_id = (SELECT MAX(id) + 1 FROM courses);

UPDATE `courses` SET `id` = @new_course_id WHERE `id` = 0;
UPDATE `modules` SET `course_id` = @new_course_id WHERE `course_id` = 0;
UPDATE `lessons` SET `course_id` = @new_course_id WHERE `course_id` = 0;
UPDATE `quizzes` SET `course_id` = @new_course_id WHERE `course_id` = 0;
UPDATE `assignments` SET `course_id` = @new_course_id WHERE `course_id` = 0;
UPDATE `course_reviews` SET `course_id` = @new_course_id WHERE `course_id` = 0;
UPDATE `discussions` SET `course_id` = @new_course_id WHERE `course_id` = 0;
UPDATE `enrollments` SET `course_id` = @new_course_id WHERE `course_id` = 0;
UPDATE `payments` SET `course_id` = @new_course_id WHERE `course_id` = 0;
UPDATE `lenco_transactions` SET `course_id` = @new_course_id WHERE `course_id` = 0;
UPDATE `enrollment_payment_plans` SET `course_id` = @new_course_id WHERE `course_id` = 0;
*/

-- ============================================================================
-- 13. DATA CLEANUP: Remove duplicate questions from cybersecurity insertion
-- ============================================================================
-- Questions 12-16 are duplicates of 7-11 (same text, from dual insertion).
-- Only remove if they are NOT linked to any quiz via quiz_questions.

DELETE q FROM `questions` q
LEFT JOIN `quiz_questions` qq ON q.question_id = qq.question_id
WHERE q.question_id IN (12, 13, 14, 15, 16)
AND qq.quiz_question_id IS NULL;

-- ============================================================================
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- VERIFICATION QUERIES (run these after migration to confirm)
-- ============================================================================

-- Check for duplicate emails
-- SELECT email, COUNT(*) FROM users GROUP BY email HAVING COUNT(*) > 1;

-- Check for duplicate usernames
-- SELECT username, COUNT(*) FROM users GROUP BY username HAVING COUNT(*) > 1;

-- Check for duplicate certificate numbers
-- SELECT certificate_number, COUNT(*) FROM certificates GROUP BY certificate_number HAVING COUNT(*) > 1;

-- Check quiz_answers auto_increment
-- SHOW COLUMNS FROM quiz_answers WHERE Field = 'answer_id';

-- Check quiz_attempts no longer has attempt_id
-- SHOW COLUMNS FROM quiz_attempts;
