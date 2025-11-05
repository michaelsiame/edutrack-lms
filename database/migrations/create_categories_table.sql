-- Create categories table for course categorization
-- Run this migration in phpMyAdmin SQL tab after selecting edutrack_lms database

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `color`, `display_order`, `is_active`) VALUES
('Web Development', 'web-development', 'Learn to build websites and web applications', 'fas fa-code', '#3B82F6', 1, 1),
('Mobile Development', 'mobile-development', 'Build mobile apps for iOS and Android', 'fas fa-mobile-alt', '#10B981', 2, 1),
('Data Science', 'data-science', 'Analytics, machine learning, and AI', 'fas fa-chart-line', '#8B5CF6', 3, 1),
('Graphic Design', 'graphic-design', 'Design tools and principles', 'fas fa-palette', '#F59E0B', 4, 1),
('Business', 'business', 'Business skills and entrepreneurship', 'fas fa-briefcase', '#EF4444', 5, 1),
('Marketing', 'marketing', 'Digital marketing and social media', 'fas fa-bullhorn', '#EC4899', 6, 1),
('IT & Software', 'it-software', 'Software development and IT skills', 'fas fa-laptop-code', '#6366F1', 7, 1),
('Office Productivity', 'office-productivity', 'MS Office, productivity tools', 'fas fa-file-alt', '#14B8A6', 8, 1);

-- Add category_id column to courses table if it doesn't exist
ALTER TABLE `courses`
ADD COLUMN IF NOT EXISTS `category_id` int(11) DEFAULT NULL AFTER `instructor_id`,
ADD KEY IF NOT EXISTS `category_id` (`category_id`);

-- Update existing courses to have a default category (Web Development)
UPDATE `courses` SET `category_id` = 1 WHERE `category_id` IS NULL;
