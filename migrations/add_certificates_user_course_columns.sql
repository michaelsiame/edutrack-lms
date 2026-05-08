-- Migration: Add user_id, course_id, final_score, issued_at to certificates table
-- Date: 2026-05-08
-- Reason: Complete schema alignment between dbschema.sql and complete_lms_schema.sql

-- Add missing columns (only if they don't exist)
ALTER TABLE `certificates`
  ADD COLUMN IF NOT EXISTS `user_id` int(11) DEFAULT NULL AFTER `certificate_id`,
  ADD COLUMN IF NOT EXISTS `course_id` int(11) DEFAULT NULL AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `final_score` decimal(5,2) DEFAULT 0.00 AFTER `verification_code`,
  ADD COLUMN IF NOT EXISTS `issued_at` datetime DEFAULT NULL AFTER `final_score`;

-- Populate user_id and course_id from enrollments
UPDATE `certificates` c
JOIN `enrollments` e ON c.enrollment_id = e.id
SET c.user_id = e.user_id,
    c.course_id = e.course_id;

-- Populate issued_at from issued_date
UPDATE `certificates`
SET `issued_at` = CONCAT(`issued_date`, ' 00:00:00')
WHERE `issued_at` IS NULL AND `issued_date` IS NOT NULL;

-- Populate final_score from enrollment final_grade
UPDATE `certificates` c
JOIN `enrollments` e ON c.enrollment_id = e.id
SET c.final_score = e.final_grade
WHERE c.final_score = 0.00 AND e.final_grade IS NOT NULL;
