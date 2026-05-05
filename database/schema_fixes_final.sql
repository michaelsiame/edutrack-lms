-- ============================================================================
-- Edutrack LMS Schema Fixes - FINAL
-- Based on actual production schema (May 2026 dump)
-- All operations are idempotent (safe to run multiple times)
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

START TRANSACTION;

-- ============================================================================
-- 1. CERTIFICATES: Add missing columns if not present, migrate data
-- ============================================================================
ALTER TABLE `certificates`
    ADD COLUMN IF NOT EXISTS `final_score` decimal(5,2) DEFAULT 0 AFTER `verification_code`,
    ADD COLUMN IF NOT EXISTS `issued_at` datetime DEFAULT NULL AFTER `final_score`;

UPDATE `certificates` c
JOIN `enrollments` e ON c.enrollment_id = e.id
SET c.user_id = e.user_id,
    c.course_id = e.course_id,
    c.issued_at = COALESCE(c.issued_at, CONCAT(c.issued_date, ' 00:00:00'))
WHERE c.user_id IS NULL OR c.issued_at IS NULL;

-- ============================================================================
-- 2. CERTIFICATES: Add missing FK on enrollment_id
-- ============================================================================
ALTER TABLE `certificates` DROP FOREIGN KEY IF EXISTS `fk_cert_enroll`;
ALTER TABLE `certificates` ADD CONSTRAINT `fk_cert_enroll` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE;

-- ============================================================================
-- 3. DEDUPLICATE enrollment_payment_plans (safe even if already clean)
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
-- 4. DEDUPLICATE user_roles (safe even if already clean)
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
-- 5. FIX payments.currency default to ZMW
-- ============================================================================
ALTER TABLE `payments` ALTER COLUMN `currency` SET DEFAULT 'ZMW';
UPDATE `payments` SET `currency` = 'ZMW' WHERE `currency` = 'USD' AND `amount` > 100;

-- ============================================================================
-- 6. FIX remember_tokens FK name (DE -> fk_rt_user)
-- ============================================================================
ALTER TABLE `remember_tokens` DROP FOREIGN KEY IF EXISTS `DE`;
ALTER TABLE `remember_tokens` DROP FOREIGN KEY IF EXISTS `fk_rt_user`;
ALTER TABLE `remember_tokens` ADD CONSTRAINT `fk_rt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ============================================================================
-- 7. DROP redundant indexes
-- ============================================================================
ALTER TABLE `lenco_transactions` DROP INDEX IF EXISTS `idx_reference`;
ALTER TABLE `users` DROP INDEX IF EXISTS `id`;

-- ============================================================================
-- 8. ADD missing indexes (IF NOT EXISTS)
-- ============================================================================
ALTER TABLE `activity_logs`
    ADD KEY IF NOT EXISTS `idx_al_user` (`user_id`),
    ADD KEY IF NOT EXISTS `idx_al_type` (`activity_type`),
    ADD KEY IF NOT EXISTS `idx_al_created` (`created_at`);

ALTER TABLE `announcements`
    ADD KEY IF NOT EXISTS `idx_ann_course` (`course_id`);

ALTER TABLE `payments`
    ADD KEY IF NOT EXISTS `idx_pay_plan` (`payment_plan_id`),
    ADD KEY IF NOT EXISTS `idx_pay_status` (`payment_status`);

ALTER TABLE `enrollment_payment_plans`
    ADD KEY IF NOT EXISTS `idx_epp_user` (`user_id`),
    ADD KEY IF NOT EXISTS `idx_epp_course` (`course_id`);

ALTER TABLE `lenco_transactions`
    ADD KEY IF NOT EXISTS `idx_lt_user` (`user_id`),
    ADD KEY IF NOT EXISTS `idx_lt_enroll` (`enrollment_id`),
    ADD KEY IF NOT EXISTS `idx_lt_course` (`course_id`);

ALTER TABLE `courses`
    ADD UNIQUE KEY IF NOT EXISTS `idx_courses_slug` (`slug`);

-- ============================================================================
-- 9. DATA CLEANUP: Remove orphaned duplicate questions
-- ============================================================================
DELETE q FROM `questions` q
LEFT JOIN `quiz_questions` qq ON q.question_id = qq.question_id
WHERE q.question_id IN (12, 13, 14, 15, 16)
AND qq.quiz_question_id IS NULL;

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
