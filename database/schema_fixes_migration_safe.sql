-- ============================================================================
-- Edutrack LMS Schema Fixes - SAFE VERSION
-- Only includes changes that are verified safe for MariaDB
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

START TRANSACTION;

-- ============================================================================
-- 1. CRITICAL: Add missing AUTO_INCREMENT
-- ============================================================================
ALTER TABLE `quiz_answers`
    MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `quiz_attempts`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- ============================================================================
-- 2. CRITICAL: Create quiz_question_options table (code expects it)
-- ============================================================================
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
-- 3. CERTIFICATES: Add missing columns if they don't exist, then migrate data
-- ============================================================================
SET @add_col = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'certificates' AND COLUMN_NAME = 'user_id' AND TABLE_SCHEMA = DATABASE()
);
SET @sql = IF(@add_col = 0,
    'ALTER TABLE certificates ADD COLUMN user_id int(11) DEFAULT NULL AFTER certificate_id',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_col = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'certificates' AND COLUMN_NAME = 'course_id' AND TABLE_SCHEMA = DATABASE()
);
SET @sql = IF(@add_col = 0,
    'ALTER TABLE certificates ADD COLUMN course_id int(11) DEFAULT NULL AFTER user_id',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_col = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'certificates' AND COLUMN_NAME = 'final_score' AND TABLE_SCHEMA = DATABASE()
);
SET @sql = IF(@add_col = 0,
    'ALTER TABLE certificates ADD COLUMN final_score decimal(5,2) DEFAULT 0 AFTER verification_code',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_col = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'certificates' AND COLUMN_NAME = 'issued_at' AND TABLE_SCHEMA = DATABASE()
);
SET @sql = IF(@add_col = 0,
    'ALTER TABLE certificates ADD COLUMN issued_at datetime DEFAULT NULL AFTER final_score',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Migrate data from enrollments
UPDATE `certificates` c
JOIN `enrollments` e ON c.enrollment_id = e.id
SET c.user_id = e.user_id,
    c.course_id = e.course_id,
    c.issued_at = COALESCE(c.issued_at, CONCAT(c.issued_date, ' 00:00:00'))
WHERE c.user_id IS NULL;

-- ============================================================================
-- 4. DEDUPLICATE enrollment_payment_plans
-- ============================================================================
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

-- ============================================================================
-- 5. DEDUPLICATE user_roles
-- ============================================================================
DELETE ur FROM `user_roles` ur
JOIN (
    SELECT user_id, role_id,
           SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY assigned_at DESC, id DESC SEPARATOR ','), ',', 1) + 0 AS keeper_id
    FROM user_roles
    GROUP BY user_id, role_id
    HAVING COUNT(*) > 1
) k ON ur.user_id = k.user_id AND ur.role_id = k.role_id
WHERE ur.id != k.keeper_id;

-- ============================================================================
-- 6. ADD UNIQUE CONSTRAINTS (safe, already verified no duplicates exist)
-- ============================================================================
ALTER TABLE `enrollment_payment_plans`
    ADD UNIQUE KEY IF NOT EXISTS `uk_epp_enrollment` (`enrollment_id`);

ALTER TABLE `certificates`
    ADD UNIQUE KEY IF NOT EXISTS `uk_cert_number` (`certificate_number`);

ALTER TABLE `certificates`
    ADD UNIQUE KEY IF NOT EXISTS `uk_cert_verify` (`verification_code`);

ALTER TABLE `quiz_questions`
    ADD UNIQUE KEY IF NOT EXISTS `uk_qq_quiz_question` (`quiz_id`, `question_id`);

ALTER TABLE `user_roles`
    ADD UNIQUE KEY IF NOT EXISTS `uk_user_role` (`user_id`, `role_id`);

ALTER TABLE `payments`
    ADD UNIQUE KEY IF NOT EXISTS `uk_payments_txn` (`transaction_id`);

ALTER TABLE `users`
    ADD UNIQUE KEY IF NOT EXISTS `uk_users_email` (`email`);

ALTER TABLE `users`
    ADD UNIQUE KEY IF NOT EXISTS `uk_users_username` (`username`);

ALTER TABLE `students`
    ADD UNIQUE KEY IF NOT EXISTS `uk_students_user` (`user_id`);

ALTER TABLE `instructors`
    ADD UNIQUE KEY IF NOT EXISTS `uk_instructors_user` (`user_id`);

-- ============================================================================
-- 7. ADD INDEXES (safe, IF NOT EXISTS)
-- ============================================================================
ALTER TABLE `payments`
    ADD KEY IF NOT EXISTS `idx_pay_student` (`student_id`),
    ADD KEY IF NOT EXISTS `idx_pay_course` (`course_id`),
    ADD KEY IF NOT EXISTS `idx_pay_enroll` (`enrollment_id`),
    ADD KEY IF NOT EXISTS `idx_pay_plan` (`payment_plan_id`),
    ADD KEY IF NOT EXISTS `idx_pay_status` (`payment_status`);

ALTER TABLE `certificates`
    ADD KEY IF NOT EXISTS `idx_cert_enroll` (`enrollment_id`);

ALTER TABLE `announcements`
    ADD KEY IF NOT EXISTS `idx_ann_course` (`course_id`);

ALTER TABLE `quiz_questions`
    ADD KEY IF NOT EXISTS `idx_qq_quiz` (`quiz_id`),
    ADD KEY IF NOT EXISTS `idx_qq_question` (`question_id`);

ALTER TABLE `quiz_answers`
    ADD KEY IF NOT EXISTS `idx_qa_attempt` (`attempt_id`),
    ADD KEY IF NOT EXISTS `idx_qa_question` (`question_id`);

ALTER TABLE `lesson_progress`
    ADD KEY IF NOT EXISTS `idx_lp_enroll` (`enrollment_id`),
    ADD KEY IF NOT EXISTS `idx_lp_lesson` (`lesson_id`);

ALTER TABLE `lesson_resources`
    ADD KEY IF NOT EXISTS `idx_lr_lesson` (`lesson_id`);

ALTER TABLE `live_sessions`
    ADD KEY IF NOT EXISTS `idx_ls_lesson` (`lesson_id`);

ALTER TABLE `live_session_attendance`
    ADD KEY IF NOT EXISTS `idx_lsa_session` (`session_id`),
    ADD KEY IF NOT EXISTS `idx_lsa_user` (`user_id`);

ALTER TABLE `activity_logs`
    ADD KEY IF NOT EXISTS `idx_al_user` (`user_id`),
    ADD KEY IF NOT EXISTS `idx_al_type` (`activity_type`),
    ADD KEY IF NOT EXISTS `idx_al_created` (`created_at`);

ALTER TABLE `lenco_transactions`
    ADD KEY IF NOT EXISTS `idx_lt_user` (`user_id`),
    ADD KEY IF NOT EXISTS `idx_lt_enroll` (`enrollment_id`),
    ADD KEY IF NOT EXISTS `idx_lt_course` (`course_id`);

ALTER TABLE `courses`
    ADD UNIQUE KEY IF NOT EXISTS `idx_courses_slug` (`slug`);

ALTER TABLE `users`
    ADD KEY IF NOT EXISTS `idx_users_phone` (`phone`);

ALTER TABLE `enrollment_payment_plans`
    ADD KEY IF NOT EXISTS `idx_epp_user` (`user_id`),
    ADD KEY IF NOT EXISTS `idx_epp_course` (`course_id`);

-- ============================================================================
-- 8. FIX payments.currency default to ZMW
-- ============================================================================
ALTER TABLE `payments`
    ALTER COLUMN `currency` SET DEFAULT 'ZMW';

UPDATE `payments`
SET `currency` = 'ZMW'
WHERE `currency` = 'USD' AND `amount` > 100;

-- ============================================================================
-- 9. DROP REDUNDANT INDEXES
-- ============================================================================
ALTER TABLE `lenco_transactions`
    DROP INDEX IF EXISTS `idx_reference`;

ALTER TABLE `users`
    DROP INDEX IF EXISTS `id`;

-- ============================================================================
-- 10. FIX remember_tokens FK name
-- ============================================================================
ALTER TABLE `remember_tokens` DROP FOREIGN KEY IF EXISTS `DE`;
ALTER TABLE `remember_tokens` DROP FOREIGN KEY IF EXISTS `fk_rt_user`;
ALTER TABLE `remember_tokens` ADD CONSTRAINT `fk_rt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ============================================================================
-- 11. DATA CLEANUP: Remove duplicate questions
-- ============================================================================
DELETE q FROM `questions` q
LEFT JOIN `quiz_questions` qq ON q.question_id = qq.question_id
WHERE q.question_id IN (12, 13, 14, 15, 16)
AND qq.quiz_question_id IS NULL;

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
