-- Migration: Review System Features
-- Adds student_number, departments, and settings table fixes
-- Date: 2026-02-16

-- ============================================
-- 1. Add student_number column to students table
-- ============================================
ALTER TABLE `students` ADD COLUMN `student_number` VARCHAR(20) DEFAULT NULL AFTER `user_id`;
ALTER TABLE `students` ADD UNIQUE INDEX `idx_student_number` (`student_number`);

-- ============================================
-- 2. Create departments table
-- ============================================
CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(10) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `head_of_department` INT(11) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_dept_code` (`code`),
  KEY `fk_dept_head` (`head_of_department`),
  CONSTRAINT `fk_dept_head` FOREIGN KEY (`head_of_department`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default departments
INSERT INTO `departments` (`name`, `code`, `description`) VALUES
('Information & Communication Technology', 'ICT', 'Core ICT programs including computer literacy, networking, and system administration'),
('Software Development', 'SWD', 'Programming, web development, mobile apps, and software engineering'),
('Data Science & Analytics', 'DSA', 'Data analysis, machine learning, business intelligence, and statistics'),
('Cybersecurity', 'SEC', 'Network security, ethical hacking, digital forensics, and compliance'),
('Digital Media & Design', 'DMD', 'Graphic design, video production, UI/UX, and multimedia'),
('Business & Entrepreneurship', 'BUS', 'Business management, digital marketing, and entrepreneurship');

-- Add department_id to courses
ALTER TABLE `courses` ADD COLUMN `department_id` INT(11) DEFAULT NULL AFTER `category_id`;
ALTER TABLE `courses` ADD KEY `fk_course_department` (`department_id`);

-- ============================================
-- 3. Create settings table (simple single-row) if not exists
-- ============================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `site_name` VARCHAR(200) DEFAULT 'EduTrack LMS',
  `currency` VARCHAR(10) DEFAULT 'ZMW',
  `support_email` VARCHAR(200) DEFAULT NULL,
  `support_phone` VARCHAR(50) DEFAULT NULL,
  `enable_registration` TINYINT(1) DEFAULT 1,
  `maintenance_mode` TINYINT(1) DEFAULT 0,
  `student_id_prefix` VARCHAR(10) DEFAULT 'EST',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`id`, `site_name`, `currency`, `student_id_prefix`) VALUES (1, 'EduTrack LMS', 'ZMW', 'EST');

-- ============================================
-- 4. Add moderation columns to discussions
-- ============================================
ALTER TABLE `discussions` ADD COLUMN `is_hidden` TINYINT(1) DEFAULT 0 AFTER `is_answered`;
ALTER TABLE `discussions` ADD COLUMN `hidden_by` INT(11) DEFAULT NULL AFTER `is_hidden`;
ALTER TABLE `discussions` ADD COLUMN `hidden_reason` VARCHAR(255) DEFAULT NULL AFTER `hidden_by`;

-- ============================================
-- 5. Generate student numbers for existing students
-- ============================================
-- This will be handled by PHP code on first run

COMMIT;
