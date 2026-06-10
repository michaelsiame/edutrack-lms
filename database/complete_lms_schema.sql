-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 10, 2026 at 12:02 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u605780771_edutrack_lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `activity_type`, `entity_type`, `entity_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 8, 'login', NULL, NULL, 'User logged in', '192.168.1.100', NULL, '2025-11-18 22:21:01'),
(2, 8, 'lesson_view', 'lesson', 1, 'Viewed lesson: Welcome to Python Programming', '192.168.1.100', NULL, '2025-11-18 22:21:01'),
(3, 8, 'lesson_complete', 'lesson', 1, 'Completed lesson: Welcome to Python Programming', '192.168.1.100', NULL, '2025-11-18 22:21:01'),
(4, 9, 'login', NULL, NULL, 'User logged in', '192.168.1.105', NULL, '2025-11-18 22:21:01'),
(5, 9, 'assignment_submit', 'assignment', 3, 'Submitted assignment: Personal Portfolio Website', '192.168.1.105', NULL, '2025-11-18 22:21:01'),
(6, 3, 'login', NULL, NULL, 'Instructor logged in', '10.0.0.50', NULL, '2025-11-18 22:21:01'),
(7, 3, 'grade_assignment', 'assignment', 3, 'Graded assignment for student', '10.0.0.50', NULL, '2025-11-18 22:21:01'),
(8, 11, 'login', NULL, NULL, 'User logged in', '192.168.1.120', NULL, '2025-11-18 22:21:01'),
(9, 11, 'discussion_post', 'discussion', 2, 'Posted new discussion topic', '192.168.1.120', NULL, '2025-11-18 22:21:01'),
(10, 1, 'login', NULL, NULL, 'Admin logged in', '10.0.0.10', NULL, '2025-11-18 22:21:01'),
(11, 68, 'enrollment', 'course', 11, 'Enrolled in course (Pending Deposit)', '45.214.180.198', NULL, '2026-03-16 23:36:01'),
(12, 56, 'enrollment', 'course', 7, 'Enrolled in course (Pending Deposit)', '165.58.129.54', NULL, '2026-03-17 16:57:36'),
(13, 90, 'enrollment', 'course', 27, 'Enrolled in course (Pending Deposit)', '41.223.116.241', NULL, '2026-05-13 15:55:59'),
(14, 91, 'login', 'user', 91, 'Test instructor logged in', '127.0.0.1', NULL, '2026-05-23 12:45:00'),
(15, 91, 'login', 'user', 91, 'Test instructor logged in', '127.0.0.1', NULL, '2026-05-23 12:45:24'),
(16, 91, 'login', 'user', 91, 'Test instructor logged in', '127.0.0.1', NULL, '2026-05-23 12:45:37'),
(17, 91, 'login', 'user', 91, 'Test instructor logged in', '127.0.0.1', NULL, '2026-05-23 12:46:07'),
(18, 91, 'login', 'user', 91, 'Test instructor logged in', '127.0.0.1', NULL, '2026-05-23 12:46:38'),
(19, 91, 'login', 'user', 91, 'Test instructor logged in', '127.0.0.1', NULL, '2026-05-23 12:46:50');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `posted_by` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `announcement_type` enum('Course','System','Urgent','General') DEFAULT 'General',
  `priority` enum('Low','Normal','High','Urgent') DEFAULT 'Normal',
  `is_published` tinyint(1) DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `course_id`, `posted_by`, `title`, `content`, `announcement_type`, `priority`, `is_published`, `published_at`, `expires_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 3, 'Welcome to Python Programming', 'Welcome to the Certificate in Python Programming! We are excited to have you join us. Please review the course syllabus and introduction materials.', 'Course', 'Normal', 1, '2025-01-08 08:00:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(2, 7, 3, 'Project Deadline Extension', 'Due to popular request, the portfolio project deadline has been extended by 3 days. New deadline: March 23, 2025.', 'Course', 'High', 1, '2025-03-15 12:30:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(3, NULL, 1, 'Platform Maintenance Schedule', 'EduTrack LMS will undergo scheduled maintenance on Sunday, Feb 25 from 2:00 AM to 6:00 AM. The platform will be temporarily unavailable during this time.', 'System', 'Urgent', 1, '2025-02-20 07:00:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(4, 11, 4, 'Guest Lecture on Ethical Hacking', 'Join us for a special guest lecture by cybersecurity expert Dr. Ahmed Khan on March 5, 2025 at 3:00 PM.', 'Course', 'High', 1, '2025-02-28 09:00:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(5, 1, 91, 'Welcome to the Course!', '<p>Welcome everyone! I am excited to have you in this course. Please make sure to complete all lessons and assignments on time.</p>', 'General', 'Normal', 1, '2026-05-08 12:37:35', NULL, '2026-05-08 12:37:35', '2026-05-23 12:37:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `instructions` longtext DEFAULT NULL,
  `max_points` int(11) NOT NULL DEFAULT 100,
  `passing_points` int(11) NOT NULL DEFAULT 60,
  `due_date` datetime DEFAULT NULL,
  `allow_late_submission` tinyint(1) DEFAULT 0,
  `late_penalty_percent` decimal(5,2) DEFAULT 0.00,
  `max_file_size_mb` int(11) DEFAULT 10,
  `allowed_file_types` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `course_id`, `lesson_id`, `title`, `description`, `instructions`, `max_points`, `passing_points`, `due_date`, `allow_late_submission`, `late_penalty_percent`, `max_file_size_mb`, `allowed_file_types`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, NULL, 'Python Basics Project', 'Create a simple calculator application', 'Build a command-line calculator that can perform basic arithmetic operations (addition, subtraction, multiplication, division). Include error handling for division by zero.', 100, 70, '2025-02-15 23:59:59', 1, 0.00, 10, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(2, 5, NULL, 'Data Structures Assignment', 'Work with lists, dictionaries, and sets', 'Create a student management system using Python dictionaries to store student information. Implement functions to add, remove, and search students.', 100, 70, '2025-03-10 23:59:59', 1, 0.00, 10, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(3, 7, NULL, 'Personal Portfolio Website', 'Build a responsive portfolio website', 'Create a multi-page portfolio website using HTML5, CSS3, and JavaScript. Must include: home page, about page, portfolio gallery, and contact form. Site must be fully responsive.', 150, 105, '2025-03-20 23:59:59', 0, 0.00, 10, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(4, 11, NULL, 'Network Security Analysis', 'Perform security audit of a test network', 'Document security vulnerabilities in the provided test network environment. Submit a detailed report with findings and recommendations.', 100, 70, '2025-04-30 23:59:59', 0, 0.00, 10, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(5, 1, NULL, 'Test 1', 'Test 1 assessment for Microsoft Office Suite', NULL, 100, 60, '2026-05-08 14:47:43', 0, 0.00, 10, NULL, '2026-05-08 14:47:43', '2026-05-08 14:47:43', NULL),
(6, 1, NULL, 'Microsoft Word', 'Microsoft Word assessment for Microsoft Office Suite', NULL, 100, 60, '2026-05-08 14:47:43', 0, 0.00, 10, NULL, '2026-05-08 14:47:43', '2026-05-08 14:47:43', NULL),
(7, 1, NULL, 'Microsoft Excel', 'Microsoft Excel assessment for Microsoft Office Suite', NULL, 100, 60, '2026-05-08 14:47:43', 0, 0.00, 10, NULL, '2026-05-08 14:47:43', '2026-05-08 14:47:43', NULL),
(8, 1, NULL, 'Microsoft Publisher & PowerPoint', 'Microsoft Publisher & PowerPoint assessment for Microsoft Office Suite', NULL, 100, 60, '2026-05-08 14:47:43', 0, 0.00, 10, NULL, '2026-05-08 14:47:43', '2026-05-08 14:47:43', NULL),
(9, 1, NULL, 'IT & Networks', 'IT & Networks assessment for Microsoft Office Suite', NULL, 100, 60, '2026-05-08 14:47:43', 0, 0.00, 10, NULL, '2026-05-08 14:47:43', '2026-05-08 14:47:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submission_text` longtext DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Submitted','Graded','Returned','Late') DEFAULT 'Submitted',
  `points_earned` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL,
  `attempt_number` int(11) DEFAULT 1,
  `is_late` tinyint(1) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`id`, `assignment_id`, `student_id`, `submission_text`, `file_url`, `submitted_at`, `status`, `points_earned`, `feedback`, `graded_by`, `graded_at`, `attempt_number`, `is_late`, `deleted_at`) VALUES
(1, 1, 1, 'Calculator project completed. File uploaded to repository.', NULL, '2025-02-14 16:30:00', 'Graded', 95.00, 'Excellent work! Clean code and proper error handling. Well done.', 2, '2025-02-16 08:00:00', 1, 0, NULL),
(2, 1, 4, 'My calculator implementation with extended features.', NULL, '2025-02-15 18:00:00', 'Graded', 88.00, 'Good implementation. Consider adding more comments for clarity.', 2, '2025-02-17 12:30:00', 1, 0, NULL),
(3, 3, 2, 'Portfolio website completed with all requirements.', NULL, '2025-03-19 14:45:00', 'Graded', 142.00, 'Beautiful design and excellent responsive implementation!', 2, '2025-03-21 09:00:00', 1, 0, NULL),
(4, 5, 72, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 100.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(5, 6, 72, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 100.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(6, 7, 72, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 98.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(7, 8, 72, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 100.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(8, 9, 72, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 89.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(9, 5, 77, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 96.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(10, 6, 77, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 94.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(11, 7, 77, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 98.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(12, 8, 77, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 84.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(13, 9, 77, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 88.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(14, 5, 71, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 93.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(15, 6, 71, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 96.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(16, 7, 71, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 98.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(17, 8, 71, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 74.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(18, 9, 71, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 83.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(19, 5, 73, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 83.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(20, 6, 73, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 90.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(21, 7, 73, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 98.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(22, 8, 73, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 72.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(23, 9, 73, NULL, NULL, '2026-05-08 15:03:43', 'Graded', 77.00, NULL, 1, '2026-05-08 15:03:43', 1, 0, NULL),
(24, 5, 79, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 82.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(25, 6, 79, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 96.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(26, 7, 79, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 94.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(27, 8, 79, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 74.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(28, 9, 79, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 72.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(29, 5, 80, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 87.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(30, 6, 80, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 94.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(31, 7, 80, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 86.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(32, 8, 80, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 70.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(33, 9, 80, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 80.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(34, 5, 78, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 76.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(35, 6, 78, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 90.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(36, 7, 78, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 96.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(37, 8, 78, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 76.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(38, 9, 78, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 71.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(39, 5, 81, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 69.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(40, 6, 81, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 92.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(41, 7, 81, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 90.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(42, 8, 81, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 80.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(43, 9, 81, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 68.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(44, 5, 74, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 89.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(45, 6, 74, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 94.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(46, 7, 74, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 88.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(47, 8, 74, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 58.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(48, 9, 74, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 68.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(49, 5, 75, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 73.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(50, 6, 75, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 92.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(51, 7, 75, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 92.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(52, 8, 75, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 76.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(53, 9, 75, NULL, NULL, '2026-05-08 15:03:44', 'Graded', 57.00, NULL, 1, '2026-05-08 15:03:44', 1, 0, NULL),
(54, 5, 76, NULL, NULL, '2026-05-08 15:03:45', 'Graded', 50.00, NULL, 1, '2026-05-08 15:03:45', 1, 0, NULL),
(55, 6, 76, NULL, NULL, '2026-05-08 15:03:45', 'Graded', 62.00, NULL, 1, '2026-05-08 15:03:45', 1, 0, NULL),
(56, 7, 76, NULL, NULL, '2026-05-08 15:03:45', 'Graded', 68.00, NULL, 1, '2026-05-08 15:03:45', 1, 0, NULL),
(57, 8, 76, NULL, NULL, '2026-05-08 15:03:45', 'Graded', 68.00, NULL, 1, '2026-05-08 15:03:45', 1, 0, NULL),
(58, 9, 76, NULL, NULL, '2026-05-08 15:03:45', 'Graded', 73.00, NULL, 1, '2026-05-08 15:03:45', 1, 0, NULL),
(59, 5, 82, NULL, NULL, '2026-05-08 15:20:22', 'Graded', 80.00, NULL, 1, '2026-05-08 15:20:22', 1, 0, NULL),
(60, 6, 82, NULL, NULL, '2026-05-08 15:20:22', 'Graded', 85.00, NULL, 1, '2026-05-08 15:20:22', 1, 0, NULL),
(61, 7, 82, NULL, NULL, '2026-05-08 15:20:22', 'Graded', 90.00, NULL, 1, '2026-05-08 15:20:22', 1, 0, NULL),
(62, 8, 82, NULL, NULL, '2026-05-08 15:20:22', 'Graded', 75.00, NULL, 1, '2026-05-08 15:20:22', 1, 0, NULL),
(63, 9, 82, NULL, NULL, '2026-05-08 15:20:22', 'Graded', 88.00, NULL, 1, '2026-05-08 15:20:22', 1, 0, NULL),
(64, 1, 82, 'Completed the Python Basics project. Created a calculator app using functions and loops.', 'uploads/assignments/test_user_python_project.zip', '2026-05-15 07:56:21', 'Graded', 85.00, 'Great work! Good use of functions. Could improve variable naming.', 6, '2026-05-16 07:56:21', 1, 0, NULL),
(65, 2, 82, 'Data structures assignment - implemented lists, dictionaries, and sets with practical examples.', NULL, '2026-05-20 07:56:21', 'Submitted', NULL, NULL, NULL, NULL, 1, 0, NULL),
(66, 6, 82, 'Microsoft Word practical project completed.', 'uploads/assignments/test_user_word_project.docx', '2026-05-08 07:56:21', 'Graded', 92.00, 'Excellent formatting and use of styles.', 6, '2026-05-09 07:56:21', 1, 0, NULL),
(67, 7, 82, 'Excel budget project with formulas and charts.', 'uploads/assignments/test_user_excel_project.xlsx', '2026-05-11 07:56:21', 'Returned', 78.00, 'Good effort but some formulas are incorrect. Please review VLOOKUP section.', 6, '2026-05-12 07:56:21', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `badge_id` int(11) NOT NULL,
  `badge_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `badge_icon_url` varchar(255) DEFAULT NULL,
  `badge_type` enum('Course Completion','Perfect Score','Early Bird','Participation','Streak','Custom') DEFAULT 'Custom',
  `criteria` text DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`badge_id`, `badge_name`, `description`, `badge_icon_url`, `badge_type`, `criteria`, `points`, `is_active`, `created_at`) VALUES
(1, 'First Course Complete', 'Awarded for completing your first course', NULL, 'Course Completion', 'Complete any course with passing grade', 50, 1, '2025-11-18 22:21:01'),
(2, 'Perfect Score', 'Achieved a perfect score on a quiz or assignment', NULL, 'Perfect Score', 'Score 100% on any graded assessment', 100, 1, '2025-11-18 22:21:01'),
(3, 'Early Bird', 'Submitted assignment before the due date', NULL, 'Early Bird', 'Submit assignment at least 24 hours before deadline', 25, 1, '2025-11-18 22:21:01'),
(4, 'Active Participant', 'Actively participated in course discussions', NULL, 'Participation', 'Post at least 10 discussion messages', 30, 1, '2025-11-18 22:21:01'),
(5, 'Speed Learner', 'Completed a course faster than average', NULL, 'Course Completion', 'Complete course 20% faster than average completion time', 75, 1, '2025-11-18 22:21:01'),
(6, 'Helping Hand', 'Helped fellow students in discussions', NULL, 'Participation', 'Have at least 5 replies marked as helpful', 40, 1, '2025-11-18 22:21:01'),
(7, 'First Course Completed', 'Completed your first course on Edutrack', 'badge-first-course.png', 'Course Completion', 'Complete any course', 100, 1, '2026-05-23 11:56:07');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `intake_name` varchar(255) DEFAULT NULL,
  `enrollment_id` int(11) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `issued_date` date NOT NULL,
  `verification_code` varchar(100) DEFAULT NULL,
  `final_score` decimal(5,2) DEFAULT 0.00,
  `issued_at` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 1,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificate_id`, `user_id`, `course_id`, `intake_name`, `enrollment_id`, `certificate_number`, `issued_date`, `verification_code`, `final_score`, `issued_at`, `is_verified`, `expiry_date`, `created_at`) VALUES
(1, 8, 1, NULL, 1, 'EDTRK-2025-000001', '2025-04-10', 'VRF-001-ABCD1234', 0.00, '2025-04-10 00:00:00', 1, NULL, '2025-11-18 22:21:01'),
(2, 9, 7, NULL, 4, 'EDTRK-2025-000002', '2025-04-25', 'VRF-002-EFGH5678', 0.00, '2025-04-25 00:00:00', 1, NULL, '2025-11-18 22:21:01'),
(3, 10, 18, NULL, 7, 'EDTRK-2025-000003', '2025-04-08', 'VRF-003-IJKL9012', 0.00, '2025-04-08 00:00:00', 1, NULL, '2025-11-18 22:21:01'),
(4, 10, 1, NULL, 9, 'EDTRK-2025-000004', '2025-04-12', 'VRF-004-MNOP3456', 0.00, '2025-04-12 00:00:00', 1, NULL, '2025-11-18 22:21:01'),
(5, 11, 5, NULL, 10, 'EDTRK-2025-000005', '2025-04-05', 'VRF-005-QRST7890', 0.00, '2025-04-05 00:00:00', 1, NULL, '2025-11-18 22:21:01'),
(6, 15, 3, NULL, 21, 'EDTRK-2025-000006', '2025-03-15', 'VRF-006-UVWX1234', 0.00, '2025-03-15 00:00:00', 1, NULL, '2025-11-18 22:21:01'),
(7, 78, 1, NULL, 39, 'EDTRK-2026-100039', '2026-05-08', 'VRF-139-9C509B3A', 97.40, '2026-05-08 15:09:03', 1, NULL, '2026-05-08 15:09:03'),
(8, 83, 1, NULL, 40, 'EDTRK-2026-100040', '2026-05-08', 'VRF-140-D4DE494F', 92.00, '2026-05-08 15:09:03', 1, NULL, '2026-05-08 15:09:03'),
(9, 77, 1, NULL, 41, 'EDTRK-2026-100041', '2026-05-08', 'VRF-141-E87AC841', 88.80, '2026-05-08 15:09:03', 1, NULL, '2026-05-08 15:09:03'),
(10, 79, 1, NULL, 42, 'EDTRK-2026-100042', '2026-05-08', 'VRF-142-4D668F4B', 84.00, '2026-05-08 15:09:04', 1, NULL, '2026-05-08 15:09:04'),
(11, 85, 1, NULL, 43, 'EDTRK-2026-100043', '2026-05-08', 'VRF-143-C87545D6', 83.60, '2026-05-08 15:09:04', 1, NULL, '2026-05-08 15:09:04'),
(12, 86, 1, NULL, 44, 'EDTRK-2026-100044', '2026-05-08', 'VRF-144-230A46A9', 83.40, '2026-05-08 15:09:04', 1, NULL, '2026-05-08 15:09:04'),
(13, 84, 1, NULL, 45, 'EDTRK-2026-100045', '2026-05-08', 'VRF-145-F8DE372C', 81.80, '2026-05-08 15:09:04', 1, NULL, '2026-05-08 15:09:04'),
(14, 87, 1, NULL, 46, 'EDTRK-2026-100046', '2026-05-08', 'VRF-146-6235A06E', 79.80, '2026-05-08 15:09:04', 1, NULL, '2026-05-08 15:09:04'),
(15, 80, 1, NULL, 47, 'EDTRK-2026-100047', '2026-05-08', 'VRF-147-CB541394', 79.40, '2026-05-08 15:09:04', 1, NULL, '2026-05-08 15:09:04'),
(16, 81, 1, NULL, 48, 'EDTRK-2026-100048', '2026-05-08', 'VRF-148-E8A683F9', 78.00, '2026-05-08 15:09:05', 1, NULL, '2026-05-08 15:09:05'),
(17, 82, 1, NULL, 49, 'EDTRK-2026-100049', '2026-05-08', 'VRF-149-89859207', 64.20, '2026-05-08 15:09:05', 1, NULL, '2026-05-08 15:09:05'),
(18, 88, 1, NULL, 50, 'EDTRK-2026-100050', '2026-05-08', 'VRF-150-5ECC1A26', 83.60, '2026-05-08 15:20:22', 1, NULL, '2026-05-08 15:20:22');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 'Donaldvem', 'no.reply.MarcusMaes@gmail.com', '87647721245', 'general', 'Greetings! edutrackzambia.com \r\n \r\nDid you know that it is possible to send appeal perfectly lawfully and wholly? \r\nWhen such letters are sent, no personal data is used, and messages are sent to forms specifically designed to receive messages and appeals securely. Messages sent with Feedback Forms are not regarded as spam, as they are seen as crucial. \r\nWe offer you the chance to try out our service for free. \r\nWe are able to transmit up to 50,000 messages in your name. \r\n \r\nThe cost of sending one million messages is $59. \r\n \r\nThis message was automatically generated. \r\n \r\nContact us. \r\nTelegram - https://t.me/FeedbackFormEU \r\nSkype  live:contactform_18 \r\nWhatsApp - +375259112693 \r\nWhatsApp  https://wa.me/+375259112693 \r\nWe only use chat for communication.', 0, '2026-02-18 02:46:24'),
(2, 'AndrewTok', 'no.reply.Lars-OlofMoore@gmail.com', '81795162781', 'general', 'Hey there! edutrackzambia.com \r\n \r\nDid you know that it is possible to send message whollСѓ in lawful manner? \r\nWhen such commercial offers are sent, no personal data is used and messages are sent to forms that are specifically designed to receive messages and appeals in an efficient manner. Feedback Forms guarantee that messages won\'t be marked as spam, since they are considered important. \r\nWe offer you the chance to try out our service for free. \r\nOn your behalf, we can deliver up to 50,000 messages. \r\n \r\nThe cost of sending one million messages is $59. \r\n \r\nThis letter is automatically generated. \r\n \r\nContact us. \r\nTelegram - https://t.me/FeedbackFormEU \r\nWhatsApp - +375259112693 \r\nWhatsApp  https://wa.me/+375259112693 \r\nWe only use chat for communication.', 0, '2026-02-20 18:15:42'),
(3, 'Jayrn Smith', 'reyna.giuseppe@gmail.com', '7867119371', 'technical', 'Hi, it’s Jayrn.\r\n\r\nYou are about to master Magnetic Marketing—the system where customers hunt you down, and you never have to “get slapped” by the exhaustion of cold prospecting again. \r\n\r\nThis methodology is built upon Dan Kennedy’s 34-year proven “Magnetic Marketing” system—a strategic machine that has generated over $65 million in sales across 200 different niches. \r\n\r\nThe final result is a business that operates in a “competitive vacuum,” insulating you from price-shopping and the sting of rejection. \r\n\r\nHowever, before you can add new strategies, you must perform an aggressive removal of the strategic waste and “excess equipment” currently sabotaging your growth.\r\n\r\nLearn More: https://marketersmentor.com/attracting-buyers.php?refer=edutrackzambia.com\r\n\r\nJayrn\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\nUnsubscribe: \r\nhttps://marketersmentor.com/unsubscribe.php?d=edutrackzambia.com', 0, '2026-03-08 16:38:48'),
(4, 'Danielnaway', 'jacksrenome@gmx.com', '88699319786', 'general', 'Cvbnifwjidhwfijwoj ihiwdqjfjewhifhqwhfqwuifhuieh uhuifhqwidjqwihiqwufgewygh uiheuifhqwuifgquehdqwui edutrackzambia.com', 0, '2026-03-08 21:13:42'),
(5, 'Jayrn Smith', 'dorris.tracy@yahoo.com', '7040089607', 'admission', 'Hi, it’s Jayrn.\r\n\r\nThe \"Cyclone of Change\" is hitting every industry right now—shaking up technology, media, customer preferences, and competition. \r\n\r\nMost business owners are reacting like \"fragile saplings\" in a storm, certain to be victims of the next big shift.\r\n\r\n\"But you don’t have to be one of them.\"\r\n\r\nI’m sending you a powerful resource from Dan Kennedy that breaks down exactly how to become a \"mighty oak\" that remains deeply rooted while others are being overwhelmed. \r\n\r\nInside this Magnetic Marketing Letter, you’ll discover:\r\n\r\n* The 5 Methods to Master Change: These are the specific strategies Dan uses to make change work for him, rather than letting change put him to work.\r\n\r\n* Two Master Keys to Riches: Two secret principles that Dan considers the most valuable advice he has shared in over a year.\r\n\r\n* The $440,000 Test: How one client spent $20,000 on a simple delivery change (without changing a single word of copy) and brought in nearly half a million dollars.\r\n\r\n* The \"Goldilocks Approach\" to Progress: Why leaping too fast to embrace change can lead you off a cliff, but resisting it too long will get you run over.\r\n\r\n* The \"Middled-Out\" Pyramid: Why you must change who you sell to right now as middle-income earners get \"slaughtered\" by current economic trends.\r\n\r\nYou’ll also read the incredible story of \"Preston Schmidli\", who went from \"selling his own plasma for gas money\" to building a growth machine generating \"nearly $5 million\" in less than seven years by using these exact tactics.\r\n\r\nDon’t let yourself be overwhelmed by circumstances when you can choose to master them instead.\r\n\r\nDownload/Read here: https://marketersmentor.com/magnetic-marketing-letter.php?refer=edutrackzambia.com\r\n\r\nTo your success,\r\n\r\nJayrn\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\nUnsubscribe: \r\nhttps://marketersmentor.com/unsubscribe.php?d=edutrackzambia.com', 0, '2026-03-12 15:59:42'),
(6, 'Jayrn Smith', 'veronica.ballard@googlemail.com', '718618595', 'payment', 'Hi, it’s Jayrn.\r\n\r\nDo you feel like your audience is just drifting past your offers? \r\n\r\nIt’s called the \"Zombie Scroll,\" and if you don’t know how to shake them out of it, your business is essentially invisible.\r\n\r\nI’m sending you the latest issue of the Behind The Scenes Marketing Secrets Letter, and it’s a masterclass in what Russell Brunson calls \"Pattern Interrupt Profits\". \r\n\r\nThis issue is packed with strategies to help you stand out in any market, no matter how saturated it is. Here is a look at what’s inside:\r\n\r\n*The \"Podcast VSL\" Secret: Discover the new format that is replacing traditional Video Sales Letters. One marketer used a simple page that looked like a podcast episode and saw a 6X return on ad spend, despite the page not even being \"structured\" for sales.\r\n\r\n*The \"Avenger Team\" Framework: Russell reveals why most entrepreneurs fail because they hire people just like them (Starters). Learn how to use personality assessments to find your \"Finishers\"—the people who actually deliver on the promises you make.\r\n\r\n*Heath Wilcock’s Top 10 Breakthroughs: From why \"subscriptions are king\" to the power of the MIFGE (Most Incredible Free Gift Ever) that sent Russell’s Voxer into a meltdown with new sign-ups.\r\n\r\n*Avoiding \"Sally Stupid\": Dan Kennedy shares a \"Classic Kennedy\" warning about how big, dumb corporate entities kill marketing genius and why you must remain ruthlessly entrepreneurial as you grow.\r\n\r\n*The $100 Shift: How one member went from a \"depressed zombie\" in a 9-to-5 job to building a thriving funnel agency by \"paying for speed\".\r\n\r\nThis is actually the last-ever physical print issue of this newsletter as it transitions to a deeper, video-based training format inside the member’s area. \r\n\r\nDon\'t let your marketing become just another \"pattern\" that people ignore. Learn how to disrupt the scroll and become the only person your audience wants to buy from.\r\n\r\nLearn details here: https://marketersmentor.com/buying-only-from-you.php?refer=edutrackzambia.com\r\n\r\nTo your success,\r\n\r\nJayrn\r\n\r\n*P.S.* If you’ve ever felt \"dumb\" because you aren\'t organized, check out page 25. Russell shares his own personality profile (he has almost \"zero conscientiousness\") and explains how he stopped trying to be an organizer and started building a billion-dollar company instead. It’s a total game-changer for your self-confidence.\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\nUnsubscribe: \r\nhttps://marketersmentor.com/unsubscribe.php?d=edutrackzambia.com', 0, '2026-03-14 21:10:56'),
(7, 'Jayrn Smith', 'adeline.churchill@gmail.com', '562735174', 'general', 'Hi, it’s Jayrn.\r\n\r\nDo you want to stop chasing fleeting tactics and finally build a predictable, consistent flood of high-quality customers?\r\n\r\nIf so, you’re going to love this: https://marketersmentor.com/nobsletter.php?refer=edutrackzambia.com\r\n\r\nDan Kennedy, the \"Renegade Millionaire Maker\" who has guided the empires of marketing legends like Ryan Deiss and Frank Kern, has teamed up with Russell Brunson to open his private vault for the first time. \r\n\r\nTogether, they are revealing the exact frameworks that generated 95% of the revenue across millions of analyzed funnels.\r\n\r\nIf you’ve been suffering from \"ADHD Marketing\"—hopping from one social media trend to another while your ad budget disappears with no ROI—you need to see this:\r\n\r\nhttps://marketersmentor.com/nobsletter.php?refer=edutrackzambia.com\r\n\r\nRight now, you can \"test-drive\" their combined wisdom for 30 days and claim a $19,997 value stack of bonuses—including a massive 653-page physical swipe file of the world\'s most profitable funnels—simply by saying \"maybe\". \r\n\r\nThis is the end of the \"tactic-hopping\" nightmare and the beginning of a business that runs like clockwork.\r\n\r\nTo multiplying your leverage,\r\nJayrn\r\n\r\n\r\n\r\nMy Blog:\r\nhttps://www.jayrn.com\r\nUnsubscribe: \r\nhttps://marketersmentor.com/unsubscribe.php?d=edutrackzambia.com', 0, '2026-03-17 06:50:20'),
(8, 'JorgeVex', 'tomaszlech80@yahoo.com', '88362112265', 'general', 'We offer financing and refinancing solutions for projects, businesses, and private individuals. \r\n \r\nWe are not angel investors and we operate with full transparency. \r\n \r\nOwn capital is mandatory for minimum 10% from the total requested ! \r\n \r\nLoan amounts range from €1 million to €25 million, with terms up to 15 years. \r\n \r\nInterest rates vary between 3% and 3.6%, depending on the amount and duration. \r\n \r\nIf you are seeking reliable funding, we are ready to assist. \r\n \r\nFor more information, please contact us: \r\nEmail: info@venelpark.nl \r\nPhone: +31 629 106 017', 0, '2026-03-26 10:59:45'),
(9, 'BrianDew', 'cheronkaylive@gmail.com', '87238216962', 'general', 'A $12,500.00 Daily Prize Worth Waking Up For https://telegra.ph/Win-a-1250000-prize-every-day-Only-we-have-this--Message-ID-584064-03-25', 0, '2026-03-26 12:31:10'),
(10, 'DavidFlupe', 'no.reply.FredericDeVries@gmail.com', '83898946594', 'general', 'Hey! edutrackzambia.com, \r\nYour page appeared while I was exploring the internet. \r\nWe provide a tool for sending outreach messages to websites. \r\nOur platform supports organized communication with website owners. \r\n  \r\n  \r\nFeel free to contact us if you would like details. \r\n \r\nThanks for your attention. \r\nContact us. \r\nTelegram - https://t.me/FeedbackFormEU \r\nWhatsApp - +375259112693 \r\nWhatsApp  https://wa.me/+375259112693', 0, '2026-03-27 04:05:52'),
(11, 'Joanna Riggs', 'joannariggs211@gmail.com', '405326713', 'admission', 'Hi,\r\n\r\nI just visited edutrackzambia.com and wondered if you\'ve ever considered an impactful video to advertise your business? Our videos can generate impressive results on both your website and across social media.\r\n\r\nOur prices start from just $195 (USD).\r\n\r\nLet me know if you\'re interested in seeing samples of our previous work.\r\n\r\nRegards,\r\nJoanna\r\n\r\nUnsubscribe: https://unsubscribe.video/unsubscribe.php?d=edutrackzambia.com', 0, '2026-03-27 19:05:23'),
(12, 'Gemma Marshall', 'gemmamarshall811@gmail.com', '7932997345', 'payment', 'Hi,\r\n\r\nWe run a hands-on agency that helps clients\' Instagram accounts build authority and reach new audiences. Rather than just \"adding numbers,\" we focus on tangible benefits:\r\n\r\n1. Cheaper than Ads: We deliver targeted eyes on your profile for a fraction of the cost of running Instagram Ads.\r\n2. Real Community: We target users genuinely interested in your niche, leading to higher engagement and potential sales.\r\n3. 100% Account Safety: We don\'t use bots. Our team performs every action manually on actual smartphones, keeping your account secure.\r\n4. Consistent Results: Expect 300+ new, high-quality followers every month who actually stick around.\r\n\r\nI\'d be happy to forward you some further information if that would be of interest?\r\n\r\nKind Regards,\r\nGemma\r\n\r\nP.S. If you don\'t have a profile yet, we can handle the full setup and optimization for you.\r\n\r\nhttps://unsubscribe.social/unsubscribe.php?d=edutrackzambia.com', 0, '2026-03-31 10:57:06'),
(13, 'BrianDew', 'joserodriguezpala40@gmail.com', '86583861311', 'general', 'IMPORTANT MESSAGE! 1.3426 BTC IS PENDING YOUR ACTION WITHDRAW INSTANTLY https://i8.ae/gSAci', 0, '2026-04-03 00:21:05'),
(14, 'Jimmyskeli', 'mandichnicholas7@gmail.com', '82726269673', 'general', 'URGENT! 1.3426 BTC IS RESERVED WITHDRAW BEFORE CANCELLATION https://t2k.in/ZRxuS', 0, '2026-04-04 23:21:43'),
(15, 'Joanna Riggs', 'joannariggs278@gmail.com', '45303325', 'technical', 'Hi,\r\n\r\nI just visited edutrackzambia.com and wondered if you\'ve ever considered an impactful video to advertise your business? Our videos can generate impressive results on both your website and across social media.\r\n\r\nOur videos cost just $195 (USD) for a 30 second video ($239 for 60 seconds) and include a full script, voice-over and video.\r\n\r\nI can show you some previous videos we\'ve done if you want me to send some over. Let me know if you\'re interested in seeing samples of our previous work.\r\n\r\nRegards,\r\nJoanna\r\n\r\nUnsubscribe: https://unsubscribe.video/unsubscribe.php?d=edutrackzambia.com', 0, '2026-04-05 12:03:09'),
(16, 'Regina Peebles', 'regina.peebles80@outlook.com', '6044739800', 'payment', 'Hi,\r\n\r\nI came across edutrackzambia.com and wanted to run something by you.\r\n\r\nMy team recently finished an 81-page YouTube guide specifically for website owners—whether you’re launching from scratch or trying to scale an existing channel in 2026.\r\n\r\nWe’ve just moved the guide to a \"Pay What You Want\" model. It’s valued at $28, but I wanted to make sure it’s accessible to fellow site owners regardless of their budget. You can grab it for the full price, the price of a coffee, or even $0 if things are tight right now.\r\n\r\nIf you’d like to see if the system fits your plans for this year, you can check it out here: https://furtherinfo.info/youtube\r\n\r\nRegina', 0, '2026-04-05 14:05:34'),
(17, 'Jayrn Smith', 'charmain.stainforth@msn.com', '725807508', 'general', 'Hi, it’s Jayrn.\r\n\r\nEvery market has one rule: He who can spend the most to acquire a customer, wins. But here’s the question nobody answers: How do you actually do it?\r\n\r\nIn this video, Darcy Juarez walk through the single number that separates the amateurs from the market dominators—Maximum Allowable Cost Per Acquisition. \r\n\r\nGet this wrong, and you’ll bleed cash. Get it right, and you’ll buy customers at scale while your competitors are stuck Googling cheaper ad hacks.\r\n\r\nWatch it here: https://marketersmentor.com/crush-your-competition.php?refer=edutrackzambia.com\r\n\r\n\r\nTo multiplying your leverage,\r\nJayrn\r\n\r\nP.S.: I’m Jayrn, a digital marketer and e-commerce seller with a passion for sharing knowledge. I share proven strategies, tips, and resources to help you grow your online business.\r\n\r\n\r\n\r\nMy Blog:\r\nhttps://www.jayrn.com\r\nUnsubscribe: \r\nhttps://marketersmentor.com/unsubscribe.php?d=edutrackzambia.com', 0, '2026-04-18 11:08:24'),
(18, 'Jayrn Smith', 'scotty.briseno@outlook.com', '9249488814', 'general', 'Hey, it’s Jayrn.\r\n\r\nThere’s a pattern I keep seeing…\r\n\r\nPeople who *work hard*, try different strategies, even invest in tools…\r\n\r\n…but still don’t see consistent results.\r\n\r\nIt’s not because they’re lazy.\r\nIt’s not because they’re unlucky.\r\n\r\nIt’s because they’re following **disconnected advice**.\r\n\r\nOne strategy here.\r\nAnother tactic there.\r\n\r\nNo real understanding of what actually drives revenue.\r\n\r\nAnd when you don’t understand the “why”…\r\n\r\nYou’re stuck guessing.\r\n\r\n---\r\n\r\nThat’s exactly where I was.\r\n\r\nUntil I started studying something different:\r\n\r\nNot surface-level tactics…\r\n\r\n…but the **actual thinking behind successful marketing campaigns**.\r\n\r\nThat’s when things finally started to click.\r\n\r\n---\r\n\r\nIf you want to see what I mean, take a look at this:\r\n\r\n������ https://marketersmentor.com/NO-BS-Letter.php?refer=edutrackzambia.com\r\n\r\nEven just reading the page will shift how you think about marketing.\r\n\r\nMore tomorrow.\r\n\r\n—\r\nJayrn\r\n\r\nP.S.: I’m Jayrn, a digital marketer and e-commerce seller with a passion for sharing knowledge. I share proven strategies, tips, and resources to help you grow your online business.\r\n\r\n\r\n\r\nMy Blog:\r\nhttps://www.jayrn.com\r\nUnsubscribe: \r\nhttps://marketersmentor.com/unsubscribe.php?d=edutrackzambia.com', 0, '2026-04-23 17:31:28'),
(19, 'MUJAHID ALI', 'mjhdmnhs@gmail.com', '+33759415055', 'payment', 'Hi Sir I have been purchase a mobile in france bondy but i did\'nt recived my packege but whenever i track my packege they said packege already deliverd but i don\'t get any call any sms any package please clear my parsal...', 0, '2026-04-24 07:08:43'),
(20, 'MichaelPek', 'jacksrenome@gmx.com', '87499932667', 'general', 'YyErjcwdkdjwjjwjjdwjddjwsjf ndsaKAqwdweihduncbbwebidaa iudwnishqwuvdwqihbfvweuiojsqjqioqdefiw dwqsqwijbfiewdncbhvdifqhioqsjnqw edutrackzambia.com', 0, '2026-05-06 04:34:02'),
(21, 'SamuelRer', 'yourmail@gmail.com', '88897564112', 'general', 'This professional campaign titled \'The Path You Make\' was published in United States in February, 2018. It was created for the brand: Delta Airlines, by ad agency: Digitas. This Film medium campaign is related to the Transport industry and contains 1 media asset. It was submitted about 8 years ago. \r\nhttps://www.adsoftheworld.com/campaigns/the-path-you-make', 0, '2026-05-08 18:27:36'),
(22, 'Donnellcique', 'dowell637@gmail.com', '84525656159', 'general', 'THE $27,000,000 JACKPOT IS A TROPHY FOR TENACITY https://hiurls.com/EWbLr \r\n \r\n \r\n \r\n \r\n \r\nBATCH ID: c0to6n2z4b1l7r7um6tf5e6m9h3s2m7iq3rm0h1n3a0e6f6zb1uj6d4q2n7w0j3bl7om2j6w1q3i7w8fd1aw5u2i9x6n3g3jn8fn5x0m3p4k7j0q', 0, '2026-05-18 12:27:58'),
(23, 'Donnellcique', 'ramy.t.trade@gmail.com', '82778214969', 'general', 'IMPORTANT! Your 1.3426 BTC is Confirmed Withdraw https://orb.tl/ytUGB \r\n \r\n \r\n \r\n \r\n \r\nVALUE: j7px9g9h3i3q9e0lq6tn2c0k7p4b7e0mt6xf0g3a2o5l5r7bm4pt2k7r8d7x3a1ke9af4r4m0x4e7s0mo5ds4p8c6w4a6p4oc7ci5j4f4w1k7l6n', 0, '2026-05-19 03:04:24'),
(24, 'Gemma Marshall', 'gemmamarshall811@gmail.com', '46923259', 'Instagram Growth', 'Hi,\r\n\r\nI was just looking at edutrackzambia.com and wanted to ask: are you looking to scale your Instagram presence right now?\r\n\r\nWe help brands like yours add 300+ targeted Instagram followers every month using manual outreach and ads. We can grow your existing page or even build a brand-new profile from scratch for you if you\'d prefer a fresh start.\r\n\r\nWould you like me to send over some more info on how it works?\r\n\r\nThanks for your time,\r\nGemma', 0, '2026-06-05 09:52:11'),
(25, 'Joanna Holden', 'joannaholden1981@gmail.com', '2691664503', 'Google Ads setup for edutrackzambia.com', 'Hi,\r\n\r\nI was looking over edutrackzambia.com and wanted to see if you\'ve ever looked into using Google Ads to capture search traffic in your market.\r\n\r\nWe’ve been managing these campaigns for over 20 years (back when it was still called AdWords). It’s usually the fastest way to step ahead of your competitors and secure top-tier visibility on Google.\r\n\r\nAre you open to a quick, text-based example of how this would look for edutrackzambia.com?\r\n\r\nRegards,\r\nJoanna', 0, '2026-06-10 09:01:53');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(250) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `level` enum('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
  `language` varchar(50) DEFAULT 'English',
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `video_intro_url` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `duration_weeks` int(11) DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT NULL,
  `max_students` int(11) DEFAULT 30,
  `enrollment_count` int(11) DEFAULT 0,
  `status` enum('draft','published','archived','under_review') NOT NULL DEFAULT 'draft',
  `is_template` tinyint(1) NOT NULL DEFAULT 0,
  `template_source_id` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `prerequisites` text DEFAULT NULL,
  `learning_outcomes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `slug`, `description`, `short_description`, `category_id`, `instructor_id`, `level`, `language`, `thumbnail_url`, `video_intro_url`, `start_date`, `end_date`, `price`, `discount_price`, `duration_weeks`, `total_hours`, `max_students`, `enrollment_count`, `status`, `is_template`, `template_source_id`, `is_featured`, `rating`, `total_reviews`, `prerequisites`, `learning_outcomes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(0, 'Cybersecurity Fundamentals', 'cybersecurity-fundamentals', '<p>This comprehensive cybersecurity course prepares you for entry-level roles in the rapidly growing field of cybersecurity. You will learn fundamental concepts, network security, threat detection, ethical hacking basics, and security operations.</p>\n    <p>By the end of this course, you will understand how to protect systems, detect threats, and respond to security incidents using industry-standard tools and frameworks.</p>', 'Master cybersecurity fundamentals and protect digital assets from cyber threats', 7, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800', NULL, NULL, NULL, 4500.00, NULL, 12, 96.00, 30, 0, 'published', 0, NULL, 1, 0.00, 0, 'Basic computer literacy, Understanding of operating systems (Windows/Linux)', 'Understand cybersecurity principles and the threat landscape|Identify and mitigate common network vulnerabilities|Implement security controls and defense strategies|Detect and respond to security incidents|Understand ethical hacking basics|Apply security frameworks like NIST', '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(1, 'Certificate in Microsoft Office Suite', 'microsoft-office-suite', 'Transform your productivity with comprehensive Microsoft Office training. This industry-leading program covers the complete Office Suite including Word, Excel, PowerPoint, Publisher, and essential internet skills. Learn to create professional documents, analyze data with powerful spreadsheets, design compelling presentations, and master desktop publishing. Perfect for professionals seeking to enhance workplace efficiency, students preparing for academic success, or career changers entering the digital workplace. Our hands-on approach ensures you gain practical, job-ready skills that employers value. By course end, you\'ll confidently handle complex office tasks, automate workflows, and present information professionally.', 'Master Word, Excel, PowerPoint, Publisher & Internet skills for professional success', 1, 13, 'Beginner', 'English', 'https://images.unsplash.com/photo-1587440871875-191322ee64b0?w=800', NULL, '2025-01-15', '2025-04-15', 2500.00, NULL, 8, 64.00, 30, 18, 'published', 0, NULL, 1, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2026-05-23 11:41:01', NULL),
(3, 'Certificate in Digital Literacy', 'digital-literacy', 'Bridge the digital divide with essential 21st-century skills. This foundational course equips you with critical digital competencies for modern life and work. Learn professional email communication, effective internet research techniques, cloud storage management, online collaboration tools, and digital safety practices. Understand social media etiquette, basic troubleshooting, file management, and online privacy protection. Ideal for beginners, seniors transitioning to digital workplaces, or anyone looking to build confidence with technology. Our patient, step-by-step instruction ensures no one gets left behind in the digital age. Gain the digital fluency needed to thrive in today\'s connected world.', 'Essential digital skills for navigating modern technology confidently', 1, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=800', NULL, '2025-01-20', '2025-03-20', 850.00, NULL, 2, 16.00, 30, 0, 'published', 0, NULL, 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(4, 'Certificate in Record Management', 'record-management', 'Master professional records and information management systems that keep organizations running smoothly. Learn comprehensive filing systems, document control procedures, archiving best practices, and compliance with data protection regulations including GDPR. Understand records lifecycle management, retention schedules, digitization processes, and efficient retrieval systems. This course covers both physical and electronic records management, preparing you for roles in government, healthcare, legal, and corporate environments. Gain expertise in maintaining confidentiality, ensuring audit trails, and implementing secure disposal methods. Essential for administrative professionals, office managers, and those pursuing careers in information governance.', 'Professional records and information management for compliance and efficiency', 1, 1, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=800', NULL, '2025-02-15', '2025-05-15', 1500.00, NULL, 6, 48.00, 30, 1, 'published', 0, NULL, 0, 4.50, 0, NULL, NULL, '2025-11-18 22:21:01', '2026-05-27 08:04:19', NULL),
(5, 'Certificate in Python Programming', 'python-programming', 'Launch your programming career with Python, the world\'s most popular and versatile programming language. This comprehensive course takes you from absolute beginner to confident developer. Master Python fundamentals including variables, data types, control structures, and functions. Progress to advanced topics like object-oriented programming, file handling, error management, and popular libraries including NumPy, Pandas, and Matplotlib. Build real-world projects including data analysis tools, automation scripts, and web applications. Python\'s readability and extensive community support make it perfect for beginners, while its power suits professional developers. Ideal for aspiring programmers, data scientists, automation engineers, or anyone entering tech careers.', 'Learn Python from basics to advanced - the most in-demand programming language', 2, 2, 'Beginner', 'English', 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=800', NULL, '2025-01-10', '2025-04-10', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, NULL, 1, 4.90, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(6, 'Certificate in Java Programming', 'java-programming', 'Master Java, the enterprise-standard programming language powering billions of devices worldwide. This rigorous course covers Java fundamentals through advanced enterprise development. Learn object-oriented programming principles, Java collections framework, multithreading, exception handling, and JDBC for database connectivity. Build robust, scalable applications using industry best practices. Understand design patterns, unit testing with JUnit, and modern development tools. Java\'s \"write once, run anywhere\" philosophy makes it essential for enterprise applications, Android development, and large-scale systems. Perfect for aspiring software engineers, mobile developers, or professionals transitioning into backend development roles.', 'Master Java for enterprise applications and Android development', 2, 2, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800', NULL, '2025-02-01', '2025-06-01', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, NULL, 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(7, 'Certificate in Web Development', 'web-development', 'Build stunning, professional websites from scratch with our comprehensive full-stack web development program. Master the complete web development toolkit: HTML5 for structure, CSS3 for beautiful styling, JavaScript for interactivity, and modern frameworks for responsive design. Learn mobile-first development, CSS Grid and Flexbox layouts, JavaScript ES6+ features, DOM manipulation, and API integration. Create portfolio-worthy projects including responsive business websites, interactive web applications, and dynamic user interfaces. Understand version control with Git, browser developer tools, and deployment processes. This hands-on course prepares you for frontend developer roles or freelance web design careers. No prior experience required.', 'Build modern, responsive websites with HTML5, CSS3, JavaScript & frameworks', 2, 2, 'Beginner', 'English', 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=800', NULL, '2025-01-15', '2025-04-30', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, NULL, 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(8, 'Certificate in Mobile App Development', 'mobile-app-development', 'Create professional mobile applications for the world\'s two dominant platforms - Android and iOS. This comprehensive program covers native mobile development using Java/Kotlin for Android and Swift for iOS. Learn mobile UI/UX principles, lifecycle management, data persistence, API integration, and app publishing processes. Build real apps including social media clients, e-commerce apps, and location-based services. Understand mobile-specific challenges like varying screen sizes, touch interfaces, push notifications, and offline functionality. Master app store submission, monetization strategies, and user analytics. Perfect for aspiring mobile developers, entrepreneurs launching apps, or web developers expanding their skillset into the lucrative mobile market.', 'Develop native iOS and Android applications professionally', 2, 2, 'Advanced', 'English', 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800', NULL, '2025-03-01', '2025-07-30', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, NULL, 1, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(9, 'Certificate in Software Engineering', 'software-engineering-git', 'Learn professional software development practices that distinguish hobbyists from industry professionals. Master essential software engineering methodologies including Agile, Scrum, and DevOps. Gain expertise in version control with Git and GitHub for collaborative development, branching strategies, pull requests, and code reviews. Understand software testing principles, continuous integration/continuous deployment (CI/CD), code quality tools, and documentation best practices. Learn project management, technical communication, and team collaboration workflows used by leading tech companies. This course bridges the gap between writing code and building enterprise-grade software. Essential for junior developers preparing for professional roles or programmers seeking to adopt industry standards.', 'Professional software development with Git, testing, CI/CD & methodologies', 2, 2, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1556075798-4825dfaaf498?w=800', NULL, '2025-02-10', '2025-05-10', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, NULL, 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(10, 'Certificate in Data Analysis', 'data-analysis', 'Transform raw data into actionable business insights with comprehensive data analysis training. Master the complete data analytics workflow: collection, cleaning, analysis, visualization, and reporting. Learn Excel for data manipulation and pivot tables, SQL for database querying, and Python with Pandas for advanced analysis. Create compelling data visualizations using Tableau-style dashboards, statistical analysis techniques, and predictive modeling basics. Understand key performance indicators (KPIs), A/B testing, and data-driven decision making. Work with real-world datasets from business, healthcare, and finance sectors. This practical course prepares you for data analyst, business intelligence, or market research roles. No advanced math required - just curiosity and attention to detail.', 'Analyze data and create insights using Excel, SQL, Python & visualization tools', 3, 4, 'Beginner', 'English', 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800', NULL, '2025-01-20', '2025-04-20', 1500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(11, 'Certificate in Cyber Security', 'cyber-security', 'Defend organizations against evolving cyber threats with industry-recognized cybersecurity training. This advanced program covers network security fundamentals, ethical hacking techniques, threat analysis, penetration testing, and security best practices. Learn cryptography, firewall configuration, intrusion detection systems, security auditing, and incident response protocols. Master vulnerability assessment tools, security frameworks (NIST, ISO 27001), and compliance requirements. Understand social engineering, malware analysis, and secure coding practices. Gain hands-on experience with Kali Linux, Wireshark, Metasploit, and other professional tools. This comprehensive course prepares you for security analyst, penetration tester, or security consultant roles. Help protect critical infrastructure in our increasingly connected world.', 'Advanced cybersecurity: ethical hacking, network defense & threat analysis', 3, 3, 'Advanced', 'English', 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800', NULL, '2025-02-15', '2025-06-30', 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 1, 4.90, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(12, 'Certificate in Database Management Systems', 'database-management', 'Master the backbone of modern applications - professional database design and management. Learn relational database concepts, SQL mastery, database design principles, and normalization techniques. Work with industry-standard systems including MySQL, PostgreSQL, and SQL Server. Understand indexing strategies, query optimization, stored procedures, triggers, and transactions. Learn database administration tasks including backup/recovery, user management, performance tuning, and security. Explore NoSQL basics and data warehousing concepts. Build real-world database projects from e-commerce platforms to content management systems. Essential for backend developers, data engineers, database administrators, or anyone working with data-intensive applications. Transform messy data into organized, efficient database systems.', 'Design, manage & optimize databases using MySQL, PostgreSQL & SQL Server', 3, 3, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800', NULL, '2025-01-25', '2025-05-25', 1500.00, NULL, 6, 48.00, 30, 0, 'published', 0, NULL, 0, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(13, 'Certificate in Artificial Intelligence', 'ai-machine-learning', 'Explore the transformative world of Artificial Intelligence and its practical applications across industries. This introductory course demystifies AI concepts including machine learning, neural networks, natural language processing, and computer vision. Understand how AI powers recommendation systems, autonomous vehicles, virtual assistants, and medical diagnostics. Learn AI ethics, bias considerations, and societal implications. Gain hands-on experience with AI tools and platforms without deep mathematical knowledge. Explore real-world case studies from healthcare, finance, retail, and manufacturing. Perfect for business professionals, managers, entrepreneurs, or anyone curious about AI\'s impact on their industry. No programming background required - focus on understanding and applying AI strategically.', 'Understand AI fundamentals and practical applications across industries', 4, 4, 'Advanced', 'English', 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800', NULL, '2025-03-01', '2025-07-01', 850.00, NULL, 3, 24.00, 30, 0, 'published', 0, NULL, 1, 4.90, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(14, 'Certificate in Internet of Things', 'internet-of-things', 'Build smart, connected devices with Internet of Things (IoT) technology. Learn IoT architecture, sensor integration, microcontroller programming (Arduino, Raspberry Pi), wireless communication protocols, and cloud connectivity. Understand MQTT, REST APIs, and IoT security challenges. Build practical IoT projects including smart home systems, environmental monitoring, and industrial automation solutions. Explore IoT platforms like AWS IoT, Azure IoT Hub, and ThingSpeak. Learn data collection from sensors, real-time processing, and remote device control. Perfect for electronics enthusiasts, embedded systems developers, or engineers implementing Industry 4.0 solutions. Transform everyday objects into intelligent, internet-connected devices.', 'Build smart IoT solutions with sensors, microcontrollers & cloud integration', 4, 4, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1558346490-a72e53ae2d4f?w=800', NULL, '2025-02-20', '2025-05-20', 450.00, NULL, 12, 60.00, 30, 0, 'archived', 0, NULL, 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(15, 'Certificate in Graphic Designing', 'graphic-designing', 'Unleash your creativity with professional graphic design training using industry-standard Adobe Creative Suite. Master Photoshop for photo editing and digital art, Illustrator for vector graphics and logos, and InDesign for layouts and publications. Learn fundamental design principles including typography, color theory, composition, and visual hierarchy. Create professional materials: business cards, brochures, posters, social media graphics, and brand identities. Understand print vs. digital design requirements, file formats, and client collaboration. Build a stunning portfolio showcasing diverse design projects. Perfect for aspiring graphic designers, marketing professionals, small business owners, or creative individuals. Transform ideas into visually compelling designs that capture attention and communicate effectively.', 'Master Adobe Creative Suite for professional graphic design and branding', 5, 6, 'Beginner', 'English', 'https://images.unsplash.com/photo-1626785774625-ddcddc3445e9?w=800', NULL, '2025-01-15', '2025-04-30', 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(16, 'Certificate in Digital & Content Creation', 'digital-content-creation', 'Create engaging multimedia content that educates, entertains, and converts. Master video editing, motion graphics, animation, interactive presentations, and e-learning materials. Learn industry-standard tools for video production, audio editing, screen recording, and multimedia authoring. Understand storytelling techniques, scriptwriting, storyboarding, and visual communication strategies. Create professional content for corporate training, educational institutions, YouTube channels, and social media platforms. Learn content strategy, audience engagement, and multimedia accessibility. Perfect for educators, corporate trainers, content creators, marketing professionals, or entrepreneurs. Transform complex information into captivating visual experiences that resonate with modern audiences.', 'Create engaging multimedia content: videos, animations & e-learning materials', 5, 6, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1611162616475-46b635cb6868?w=800', NULL, '2025-02-05', '2025-05-05', 950.00, NULL, 3, 24.00, 30, 0, 'published', 0, NULL, 0, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(17, 'Certificate in Digital Marketing', 'digital-marketing', 'Master the complete digital marketing ecosystem to grow businesses online. Learn search engine optimization (SEO), social media marketing, content marketing, email campaigns, and paid advertising (Google Ads, Facebook Ads). Understand marketing analytics, conversion optimization, customer journey mapping, and ROI measurement. Create comprehensive digital marketing strategies, develop engaging content, and build effective campaigns. Master tools including Google Analytics, Facebook Business Manager, Mailchimp, and keyword research platforms. Learn influencer marketing, affiliate marketing, and marketing automation. Perfect for marketing professionals, business owners, entrepreneurs, or anyone entering the digital marketing field. Drive traffic, generate leads, and grow revenue through strategic online marketing.', 'Master SEO, social media, content marketing & digital advertising strategies', 5, 6, 'Beginner', 'English', 'https://images.unsplash.com/photo-1557838923-2985c318be48?w=800', NULL, '2025-01-20', '2025-04-20', 950.00, NULL, 3, 24.00, 30, 0, 'published', 0, NULL, 1, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(18, 'Certificate in Entrepreneurship', 'entrepreneurship', 'Transform your business idea into reality with comprehensive entrepreneurship training. Learn business planning, market research, financial management, and growth strategies. Understand business registration, legal structures, taxation, and regulatory compliance. Master customer validation, minimum viable product development, and lean startup methodologies. Learn sales strategies, negotiation skills, and customer relationship management. Understand funding options including bootstrapping, angel investment, venture capital, and crowdfunding. Develop essential entrepreneurial skills: leadership, decision-making, risk management, and resilience. Create a comprehensive business plan investors will take seriously. Perfect for aspiring entrepreneurs, freelancers transitioning to business ownership, or intrapreneurs driving innovation within organizations. Turn your passion into a profitable venture.', 'Launch and grow your business: planning, financing, marketing & operations', 6, 5, 'Beginner', 'English', 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800', NULL, '2025-01-10', '2025-04-10', 2500.00, NULL, 11, 88.00, 30, 0, 'published', 0, NULL, 0, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(19, 'Certificate in Project Management', 'project-management', 'Lead successful projects using professional project management methodologies. Master PMBOK framework, Agile principles, and Scrum practices. Learn project initiation, planning, execution, monitoring, and closure. Understand scope management, time estimation, budget control, risk management, and stakeholder communication. Use project management tools including Gantt charts, Kanban boards, and collaboration platforms. Learn team leadership, conflict resolution, and change management. Understand earned value management, critical path analysis, and quality assurance. Prepare for PMP or Agile certifications. Perfect for project managers, team leaders, coordinators, or professionals managing complex initiatives. Deliver projects on time, within budget, and exceeding stakeholder expectations.', 'Professional project management: PMBOK, Agile, Scrum & leadership skills', 6, 5, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800', NULL, '2025-02-01', '2025-06-01', 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(20, 'Certificate in Financial Technology', 'financial-technology', 'Explore the digital revolution transforming financial services worldwide. Understand blockchain technology, cryptocurrency fundamentals, digital payments systems, mobile money platforms, and digital banking. Learn about fintech ecosystems, regulatory technology (RegTech), peer-to-peer lending, robo-advisors, and insurtech innovations. Understand payment gateways, digital wallets, and cross-border transactions. Explore real-world case studies from M-Pesa, PayPal, Stripe, and emerging fintech startups. Learn about financial inclusion, cybersecurity in finance, and future trends. Perfect for banking professionals, entrepreneurs entering fintech, financial advisors adapting to digital trends, or anyone curious about money\'s digital future. Position yourself at the intersection of finance and technology.', 'Explore digital payments, blockchain, cryptocurrency & digital banking innovations', 6, 5, 'Advanced', 'English', 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=800', NULL, '2025-03-01', '2025-06-15', 1200.00, NULL, 3, 24.00, 30, 0, 'published', 0, NULL, 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42', NULL),
(24, 'Certificate in ICT Support & Hardware Repair', 'ict-support-hardware-repair', 'Become a skilled ICT technician with comprehensive computer hardware training. Learn computer architecture, component identification, hardware installation, troubleshooting methodologies, and repair techniques. Master operating system installation (Windows, Linux), driver management, and system optimization. Understand common hardware problems: motherboard issues, power supply failures, storage problems, and peripheral connectivity. Learn diagnostic tools, preventive maintenance, data recovery basics, and customer service skills. Gain hands-on experience assembling PCs, upgrading components, and resolving technical issues. Understand mobile device repair fundamentals. Perfect for aspiring IT support professionals, computer technicians, or entrepreneurs starting repair businesses. Provide essential technical support that keeps organizations running smoothly.', 'Computer hardware repair, troubleshooting & technical support expertise', 1, 1, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 1, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(25, 'Certificate in Computer Studies', 'computer-studies', 'Build a solid foundation in computer science through this comprehensive introductory program. Explore fundamental computing concepts, computer architecture, operating systems, networking basics, and software applications. Understand binary systems, logic gates, algorithms, and problem-solving techniques. Learn essential applications including word processing, spreadsheets, presentations, and internet usage. Gain exposure to programming concepts, database fundamentals, and cybersecurity awareness. Perfect foundation for further ICT studies or professional certifications. Ideal for students beginning their technology journey, career changers entering IT fields, or professionals needing comprehensive computer literacy. Develop the core knowledge essential for any technology-related career path.', 'Comprehensive computer fundamentals: ideal foundation for ICT careers', 1, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800', NULL, NULL, NULL, 3850.00, NULL, 12, 96.00, 30, 0, 'published', 0, NULL, 1, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(26, 'Certificate in Computer Science General', 'computer-science-general', 'Master both theoretical and practical aspects of computer science through this balanced curriculum. Learn computer organization, system architecture, programming fundamentals, data structures, algorithms, and software engineering principles. Understand operating systems, computer networks, database systems, and web technologies. Gain programming experience in multiple languages while learning core CS concepts like recursion, sorting algorithms, and object-oriented design. Explore hardware-software interaction, memory management, and system optimization. Perfect for students pursuing computer science careers, professionals transitioning into technical roles, or those seeking comprehensive IT knowledge. Build the versatile skillset needed for software development, systems administration, or further specialization.', 'Balanced computer science: software development & hardware fundamentals', 1, 1, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800', NULL, NULL, NULL, 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, NULL, 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(27, 'Certificate in Information Technology', 'information-technology', 'Master enterprise IT fundamentals preparing you for professional IT positions. Learn network administration, systems infrastructure, IT security principles, and technical support procedures. Understand TCP/IP networking, routing, switching, Active Directory, and server management. Master troubleshooting methodologies, help desk operations, and customer service excellence. Learn virtualization, cloud computing basics, backup solutions, and disaster recovery. Understand IT service management (ITIL), documentation practices, and change management. Gain practical experience with Windows Server, Linux administration, and network configuration. Perfect preparation for entry-level IT roles including help desk technician, systems administrator, or network support specialist. Build the practical skills IT departments need.', 'Enterprise IT fundamentals: networking, systems administration & support', 1, 1, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 1, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(28, 'Certificate in Computer & Business Handling', 'computer-business-handling', 'Bridge technology and business operations with essential computer skills for business professionals. Master Microsoft Office Suite for business: advanced Excel for financial analysis, Word for professional documentation, PowerPoint for business presentations, and Outlook for communication management. Learn business email etiquette, calendar management, task organization, and digital collaboration. Understand basic accounting software, data entry best practices, report generation, and business communication standards. Learn office automation, time management with digital tools, and remote work technologies. Perfect for administrative assistants, office managers, receptionists, bookkeepers, or anyone handling business operations. Boost workplace productivity and professional competence.', 'Essential business computer skills for administrative and office professionals', 1, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800', NULL, NULL, NULL, 1200.00, NULL, 4, 32.00, 30, 0, 'published', 0, NULL, 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(29, 'Certificate in C++ Programming', 'cpp-programming', 'Master C++, the powerful programming language behind operating systems, game engines, and high-performance applications. Learn C++ fundamentals, pointers, memory management, and object-oriented programming. Understand templates, Standard Template Library (STL), exception handling, and file I/O. Master advanced topics including operator overloading, inheritance, polymorphism, and design patterns. Learn efficient algorithms, data structures implementation, and performance optimization. Understand modern C++ features (C++11/14/17/20). Build projects including games, system utilities, and performance-critical applications. Perfect for aspiring game developers, systems programmers, or software engineers needing high-performance computing skills. C++ powers everything from embedded systems to AAA video games.', 'Master C++ for high-performance applications, game development & systems programming', 2, 2, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800', NULL, NULL, NULL, 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, NULL, 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(30, 'Certificate in Sales & Marketing', 'sales-marketing', 'Master the art and science of sales and marketing to drive business growth. Learn customer psychology, persuasion techniques, relationship building, and consultative selling. Understand marketing fundamentals: market segmentation, targeting, positioning, and the marketing mix. Master lead generation, sales funnel optimization, objection handling, and closing techniques. Learn customer relationship management (CRM), sales forecasting, and territory management. Understand brand building, competitive analysis, and market research methods. Develop essential skills: negotiation, presentation, communication, and customer service excellence. Create effective marketing campaigns and sales strategies. Perfect for sales professionals, marketing coordinators, business development representatives, or entrepreneurs growing their ventures. Turn prospects into loyal customers.', 'Master sales techniques, marketing strategies & customer relationship management', 6, 5, 'Beginner', 'English', 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(31, 'Certificate in Monitoring & Evaluation', 'monitoring-evaluation', 'Become an expert in program assessment and performance measurement with professional M&E training. Learn monitoring frameworks, evaluation methodologies, indicator development, and data collection techniques. Master logical frameworks, theory of change, results-based management, and impact assessment. Understand quantitative and qualitative research methods, sampling techniques, survey design, and data analysis. Learn M&E planning, reporting standards, stakeholder engagement, and lessons learned documentation. Use M&E software and tools for data visualization and reporting. Perfect for program managers, development professionals, NGO staff, government officials, or consultants working in international development, public health, education, or social programs. Demonstrate program effectiveness and drive evidence-based decision making.', 'Professional M&E: frameworks, data collection, analysis & reporting', 6, 5, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1551836022-deb4988cc6c0?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(32, 'Certificate in Purchasing & Supply', 'purchasing-supply', 'Master professional procurement and supply chain management for organizational efficiency. Learn strategic sourcing, vendor selection, contract negotiation, and supplier relationship management. Understand purchase order processing, inventory control systems, just-in-time delivery, and warehouse management. Master logistics coordination, demand forecasting, and supply chain optimization. Learn procurement ethics, tender processes, compliance requirements, and risk management. Understand cost analysis, value for money principles, and purchase budgeting. Explore e-procurement systems, supply chain software, and procurement best practices. Perfect for purchasing officers, supply chain coordinators, inventory managers, or business owners managing procurement. Reduce costs while maintaining quality and reliability.', 'Professional procurement, supply chain management & logistics expertise', 6, 5, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(33, 'Certificate in E-Commerce & Online Business', 'ecommerce-online-business', 'Launch and grow a successful online business in the booming e-commerce market. Learn e-commerce platforms (Shopify, WooCommerce, Magento), online store setup, product listing optimization, and digital storefront design. Master payment gateway integration, shipping logistics, inventory management, and order fulfillment. Understand online customer service, returns management, and reputation building. Learn digital marketing for e-commerce: SEO, social media advertising, email marketing, and conversion optimization. Explore dropshipping, print-on-demand, and various e-commerce business models. Understand legal requirements, taxation, and international selling. Perfect for entrepreneurs starting online stores, retailers moving online, or anyone entering the e-commerce industry. Build your profitable online business empire.', 'Launch and scale your online store: e-commerce platforms & digital selling strategies', 6, 5, 'Beginner', 'English', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800', NULL, NULL, NULL, 950.00, NULL, 3, 24.00, 30, 0, 'published', 0, NULL, 1, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(34, 'Certificate in Secretarial & Office Management', 'secretarial-office-management', 'Become an indispensable administrative professional with comprehensive secretarial and office management training. Master advanced typing skills, business correspondence, minutes taking, and professional communication. Learn office organization, filing systems, records management, and document control. Understand meeting coordination, travel arrangements, calendar management, and executive support. Master business etiquette, telephone techniques, customer service excellence, and professional image. Learn time management, priority setting, and workflow optimization. Understand office technology, database management, and basic bookkeeping. Perfect for executive assistants, office administrators, personal assistants, or professionals in administrative roles. Become the organizational backbone that enables business success.', 'Professional secretarial training: office management, typing & administrative excellence', 6, 5, 'Beginner', 'English', 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, NULL, 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42', NULL),
(35, 'Cybersecurity Fundamentals', 'cybersecurity-fundamentals', '<p>This comprehensive cybersecurity course prepares you for entry-level roles in the rapidly growing field of cybersecurity. You will learn fundamental concepts, network security, threat detection, ethical hacking basics, and security operations.</p><p>By the end of this course, you will understand how to protect systems, detect threats, and respond to security incidents using industry-standard tools and frameworks.</p>', 'Master cybersecurity fundamentals and protect digital assets from cyber threats', 12, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800', NULL, NULL, NULL, 4500.00, NULL, 12, 96.00, 30, 0, 'draft', 0, NULL, 1, 0.00, 0, 'Basic computer literacy, Understanding of operating systems (Windows/Linux)', 'Understand cybersecurity principles and the threat landscape|Identify and mitigate common network vulnerabilities|Implement security controls and defense strategies|Detect and respond to security incidents|Understand ethical hacking basics|Apply security frameworks like NIST', '2026-05-04 21:14:27', '2026-05-04 23:15:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_categories`
--

CREATE TABLE `course_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_description` text DEFAULT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `icon_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `color` varchar(20) DEFAULT '#333333'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_categories`
--

INSERT INTO `course_categories` (`id`, `name`, `category_description`, `parent_category_id`, `icon_url`, `display_order`, `is_active`, `created_at`, `updated_at`, `color`) VALUES
(1, 'Core ICT & Digital Skills', 'Fundamental computer and digital literacy courses covering essential office applications, digital tools, and basic ICT competencies', NULL, NULL, 1, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', '#333333'),
(2, 'Programming & Software Development', 'Programming languages, software engineering practices, web and mobile application development courses', NULL, NULL, 2, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', '#333333'),
(3, 'Data, Security & Networks', 'Data analysis, cybersecurity, database management, and network infrastructure courses', NULL, NULL, 3, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', '#333333'),
(4, 'Emerging Technologies', 'Cutting-edge technology courses including AI, machine learning, and Internet of Things', NULL, NULL, 4, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', '#333333'),
(5, 'Digital Media & Design', 'Creative and digital content courses covering graphic design, multimedia, and digital marketing', NULL, NULL, 5, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', '#333333'),
(6, 'Business & Management', 'Business administration, entrepreneurship, project management, and professional development courses', NULL, NULL, 6, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', '#333333'),
(7, 'Cybersecurity', 'Learn to protect systems, networks, and data from cyber threats', NULL, 'fa-shield-alt', 4, 1, '2026-05-04 18:42:11', '2026-05-04 18:42:11', '#DC2626'),
(8, 'Cybersecurity', 'Learn to protect systems, networks, and data from cyber threats', NULL, 'fa-shield-alt', 4, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', '#DC2626'),
(12, 'Cybersecurity', 'Learn to protect systems, networks, and data from cyber threats', NULL, 'fa-shield-alt', 4, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', '#DC2626');

-- --------------------------------------------------------

--
-- Table structure for table `course_instructors`
--

CREATE TABLE `course_instructors` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `role` enum('Lead','Assistant','Guest','Mentor') DEFAULT 'Lead',
  `assigned_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_instructors`
--

INSERT INTO `course_instructors` (`id`, `course_id`, `instructor_id`, `role`, `assigned_date`, `created_at`) VALUES
(3, 3, 1, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(4, 4, 1, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(5, 5, 2, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(6, 6, 2, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(7, 7, 2, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(8, 8, 2, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(9, 9, 2, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(10, 9, 5, 'Assistant', '2024-12-10', '2025-11-18 22:21:01'),
(11, 10, 4, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(12, 10, 3, 'Assistant', '2024-12-05', '2025-11-18 22:21:01'),
(13, 11, 3, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(14, 12, 3, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(15, 13, 4, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(16, 14, 4, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(17, 14, 3, 'Assistant', '2024-12-05', '2025-11-18 22:21:01'),
(18, 15, 6, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(19, 16, 6, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(20, 17, 6, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(21, 18, 5, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(22, 18, 1, 'Assistant', '2024-12-10', '2025-11-18 22:21:01'),
(23, 19, 5, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(24, 20, 5, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
(25, 1, 5, 'Lead', '2025-12-25', '2025-12-25 09:46:06');

-- --------------------------------------------------------

--
-- Table structure for table `course_reviews`
--

CREATE TABLE `course_reviews` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `course_reviews`
--

INSERT INTO `course_reviews` (`id`, `course_id`, `user_id`, `rating`, `review`, `created_at`, `updated_at`) VALUES
(1, 1, 88, 5.0, 'Excellent course! The instructor explains everything clearly and the materials are very helpful.', '2026-05-11 12:46:50', '2026-05-23 12:46:50');

-- --------------------------------------------------------

--
-- Table structure for table `discussions`
--

CREATE TABLE `discussions` (
  `discussion_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `reply_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discussions`
--

INSERT INTO `discussions` (`discussion_id`, `course_id`, `created_by`, `title`, `content`, `is_pinned`, `is_locked`, `view_count`, `reply_count`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 8, 'Best Python IDE for beginners?', 'I am new to Python and wondering what IDE you all recommend for beginners. I have heard about PyCharm, VS Code, and IDLE. What do you think?', 0, 0, 45, 8, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(2, 5, 11, 'Help with assignment 1', 'I am stuck on the calculator project. How do I handle the division by zero error properly?', 0, 0, 23, 5, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(3, 7, 9, 'Responsive design best practices', 'Can anyone share tips on making websites truly responsive? I am struggling with mobile layouts.', 0, 0, 38, 12, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(4, 11, 12, 'Career paths in cybersecurity', 'What are the different career paths available in cybersecurity? Looking for guidance.', 1, 0, 67, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `discussion_replies`
--

CREATE TABLE `discussion_replies` (
  `reply_id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `parent_reply_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_instructor_reply` tinyint(1) DEFAULT 0,
  `is_best_answer` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discussion_replies`
--

INSERT INTO `discussion_replies` (`reply_id`, `discussion_id`, `parent_reply_id`, `user_id`, `content`, `is_instructor_reply`, `is_best_answer`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 3, 'For beginners, I recommend starting with VS Code. It is lightweight, free, and has excellent Python support with extensions. PyCharm is great but can be overwhelming at first.', 1, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 1, NULL, 10, 'I use VS Code and love it! The Python extension is amazing.', 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 1, NULL, 11, 'Thanks! I will try VS Code.', 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 2, NULL, 3, 'Use a try-except block to catch the ZeroDivisionError. Check the lesson on exception handling for examples.', 1, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 2, NULL, 8, 'You can also check if the divisor is zero before performing the division.', 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 3, NULL, 7, 'Learn about CSS media queries and mobile-first design approach. Start designing for mobile and then scale up.', 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 3, NULL, 3, 'Great advice! Also check out CSS Grid and Flexbox for modern layout techniques.', 1, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(8, 1, NULL, 88, 'I recommend VS Code for beginners. It has great Python support and plenty of extensions.', 0, 0, '2026-05-12 07:58:25', '2026-05-12 07:58:25'),
(9, 2, NULL, 88, 'I am stuck on the loops section. Can someone explain list comprehensions?', 0, 0, '2026-05-17 07:58:25', '2026-05-17 07:58:25'),
(10, 1, NULL, 88, 'Update: I also tried PyCharm Community Edition and it is very good for learning.', 0, 0, '2026-05-14 07:58:25', '2026-05-14 07:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `attachments` text DEFAULT NULL,
  `status` enum('pending','processing','sent','failed') DEFAULT 'pending',
  `attempts` tinyint(4) DEFAULT 0,
  `priority` tinyint(4) DEFAULT 0,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `last_attempt` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_queue`
--

INSERT INTO `email_queue` (`id`, `recipient`, `subject`, `body`, `attachments`, `status`, `attempts`, `priority`, `scheduled_at`, `sent_at`, `last_attempt`, `created_at`) VALUES
(26, 'marvinmoonga69@gmail.com', 'New User Registration - Susan Kasoka', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Susan Kasoka</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:kasokasusan574@gmail.com\" style=\"color: #2E70DA;\">kasokasusan574@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260978705185</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 12, 2026 11:27 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=72\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-12 13:27:06', '2026-04-12 13:27:05', '2026-04-12 13:27:03'),
(27, 'edwardmusole76@gmail.com', 'New User Registration - Susan Kasoka', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Susan Kasoka</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:kasokasusan574@gmail.com\" style=\"color: #2E70DA;\">kasokasusan574@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260978705185</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 12, 2026 11:27 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=72\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-12 13:27:06', '2026-04-12 13:27:06', '2026-04-12 13:27:03'),
(28, 'michael.siame@edutrack.edu', 'New User Registration - Susan Kasoka', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Susan Kasoka</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:kasokasusan574@gmail.com\" style=\"color: #2E70DA;\">kasokasusan574@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260978705185</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 12, 2026 11:27 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=72\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-12 13:27:07', '2026-04-12 13:27:06', '2026-04-12 13:27:03'),
(29, 'inutu.simasiku@edutrack.edu', 'New User Registration - Susan Kasoka', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Susan Kasoka</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:kasokasusan574@gmail.com\" style=\"color: #2E70DA;\">kasokasusan574@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260978705185</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 12, 2026 11:27 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=72\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-12 13:27:08', '2026-04-12 13:27:07', '2026-04-12 13:27:03'),
(30, 'nita.sichimwa@edutrack.edu', 'New User Registration - Susan Kasoka', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Susan Kasoka</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:kasokasusan574@gmail.com\" style=\"color: #2E70DA;\">kasokasusan574@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260978705185</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 12, 2026 11:27 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=72\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-12 13:27:08', '2026-04-12 13:27:08', '2026-04-12 13:27:03'),
(31, 'marvinmoonga69@gmail.com', 'New User Registration - Kekelwa Kekelwa', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Kekelwa Kekelwa</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:ikacanap@gmail.com\" style=\"color: #2E70DA;\">ikacanap@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975784430</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 15, 2026 10:21 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=73\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-15 12:22:05', '2026-04-15 12:22:04', '2026-04-15 12:22:00'),
(32, 'edwardmusole76@gmail.com', 'New User Registration - Kekelwa Kekelwa', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Kekelwa Kekelwa</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:ikacanap@gmail.com\" style=\"color: #2E70DA;\">ikacanap@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975784430</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 15, 2026 10:21 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=73\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-15 12:22:05', '2026-04-15 12:22:05', '2026-04-15 12:22:00'),
(33, 'michael.siame@edutrack.edu', 'New User Registration - Kekelwa Kekelwa', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Kekelwa Kekelwa</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:ikacanap@gmail.com\" style=\"color: #2E70DA;\">ikacanap@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975784430</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 15, 2026 10:21 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=73\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-15 12:22:06', '2026-04-15 12:22:05', '2026-04-15 12:22:00'),
(34, 'inutu.simasiku@edutrack.edu', 'New User Registration - Kekelwa Kekelwa', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Kekelwa Kekelwa</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:ikacanap@gmail.com\" style=\"color: #2E70DA;\">ikacanap@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975784430</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 15, 2026 10:21 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=73\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-15 12:22:06', '2026-04-15 12:22:06', '2026-04-15 12:22:00'),
(35, 'nita.sichimwa@edutrack.edu', 'New User Registration - Kekelwa Kekelwa', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Kekelwa Kekelwa</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:ikacanap@gmail.com\" style=\"color: #2E70DA;\">ikacanap@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975784430</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 15, 2026 10:21 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=73\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-15 12:22:07', '2026-04-15 12:22:06', '2026-04-15 12:22:00'),
(36, 'marvinmoonga69@gmail.com', 'New User Registration - Wilfred Mweemba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wilfred Mweemba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wilfredmweemba12345@gmail.com\" style=\"color: #2E70DA;\">wilfredmweemba12345@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260972584450</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 26, 2026 4:56 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=75\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-26 06:57:04', '2026-04-26 06:57:03', '2026-04-26 06:56:44'),
(37, 'edwardmusole76@gmail.com', 'New User Registration - Wilfred Mweemba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wilfred Mweemba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wilfredmweemba12345@gmail.com\" style=\"color: #2E70DA;\">wilfredmweemba12345@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260972584450</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 26, 2026 4:56 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=75\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-26 06:57:04', '2026-04-26 06:57:04', '2026-04-26 06:56:44'),
(38, 'michael.siame@edutrack.edu', 'New User Registration - Wilfred Mweemba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wilfred Mweemba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wilfredmweemba12345@gmail.com\" style=\"color: #2E70DA;\">wilfredmweemba12345@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260972584450</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 26, 2026 4:56 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=75\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-26 06:57:05', '2026-04-26 06:57:04', '2026-04-26 06:56:44'),
(39, 'inutu.simasiku@edutrack.edu', 'New User Registration - Wilfred Mweemba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wilfred Mweemba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wilfredmweemba12345@gmail.com\" style=\"color: #2E70DA;\">wilfredmweemba12345@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260972584450</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 26, 2026 4:56 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=75\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-26 06:57:05', '2026-04-26 06:57:05', '2026-04-26 06:56:44'),
(40, 'nita.sichimwa@edutrack.edu', 'New User Registration - Wilfred Mweemba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wilfred Mweemba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wilfredmweemba12345@gmail.com\" style=\"color: #2E70DA;\">wilfredmweemba12345@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260972584450</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>April 26, 2026 4:56 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=75\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-04-26 06:57:06', '2026-04-26 06:57:05', '2026-04-26 06:56:44'),
(41, 'michael.siame@edutrack.edu', 'New User Registration - Taonga Tembo', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Taonga Tembo</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:taongatembo167@gmail.com\" style=\"color: #2E70DA;\">taongatembo167@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779033041</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:11 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=77\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:12:05', '2026-05-08 14:12:05', '2026-05-08 14:11:18'),
(42, 'marvinmoonga69@gmail.com', 'New User Registration - Taonga Tembo', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Taonga Tembo</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:taongatembo167@gmail.com\" style=\"color: #2E70DA;\">taongatembo167@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779033041</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:11 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=77\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:12:05', '2026-05-08 14:12:05', '2026-05-08 14:11:18'),
(43, 'edwardmusole76@gmail.com', 'New User Registration - Taonga Tembo', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Taonga Tembo</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:taongatembo167@gmail.com\" style=\"color: #2E70DA;\">taongatembo167@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779033041</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:11 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=77\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:12:06', '2026-05-08 14:12:06', '2026-05-08 14:11:18'),
(44, 'inutu.simasiku@edutrack.edu', 'New User Registration - Taonga Tembo', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Taonga Tembo</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:taongatembo167@gmail.com\" style=\"color: #2E70DA;\">taongatembo167@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779033041</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:11 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=77\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:12:06', '2026-05-08 14:12:06', '2026-05-08 14:11:18'),
(45, 'nita.sichimwa@edutrack.edu', 'New User Registration - Taonga Tembo', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Taonga Tembo</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:taongatembo167@gmail.com\" style=\"color: #2E70DA;\">taongatembo167@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779033041</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:11 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=77\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:12:07', '2026-05-08 14:12:07', '2026-05-08 14:11:18'),
(46, 'michael.siame@edutrack.edu', 'New User Registration - Luyando Mumbe Muchimba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Luyando Mumbe Muchimba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:luyando356@gmail.com\" style=\"color: #2E70DA;\">luyando356@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975215720</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:12 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=78\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:13:03', '2026-05-08 14:13:03', '2026-05-08 14:12:26'),
(47, 'marvinmoonga69@gmail.com', 'New User Registration - Luyando Mumbe Muchimba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Luyando Mumbe Muchimba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:luyando356@gmail.com\" style=\"color: #2E70DA;\">luyando356@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975215720</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:12 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=78\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:13:04', '2026-05-08 14:13:04', '2026-05-08 14:12:26'),
(48, 'edwardmusole76@gmail.com', 'New User Registration - Luyando Mumbe Muchimba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Luyando Mumbe Muchimba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:luyando356@gmail.com\" style=\"color: #2E70DA;\">luyando356@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975215720</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:12 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=78\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:13:04', '2026-05-08 14:13:04', '2026-05-08 14:12:26');
INSERT INTO `email_queue` (`id`, `recipient`, `subject`, `body`, `attachments`, `status`, `attempts`, `priority`, `scheduled_at`, `sent_at`, `last_attempt`, `created_at`) VALUES
(49, 'inutu.simasiku@edutrack.edu', 'New User Registration - Luyando Mumbe Muchimba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Luyando Mumbe Muchimba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:luyando356@gmail.com\" style=\"color: #2E70DA;\">luyando356@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975215720</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:12 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=78\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:13:05', '2026-05-08 14:13:05', '2026-05-08 14:12:26'),
(50, 'nita.sichimwa@edutrack.edu', 'New User Registration - Luyando Mumbe Muchimba', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Luyando Mumbe Muchimba</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:luyando356@gmail.com\" style=\"color: #2E70DA;\">luyando356@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260975215720</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:12 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=78\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:13:05', '2026-05-08 14:13:05', '2026-05-08 14:12:26'),
(51, 'michael.siame@edutrack.edu', 'New User Registration - Chintu Chiinda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Chintu Chiinda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:chintuchiinda01@gmail.com\" style=\"color: #2E70DA;\">chintuchiinda01@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260976788089</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:16 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=79\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:17:02', '2026-05-08 14:17:01', '2026-05-08 14:16:52'),
(52, 'marvinmoonga69@gmail.com', 'New User Registration - Chintu Chiinda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Chintu Chiinda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:chintuchiinda01@gmail.com\" style=\"color: #2E70DA;\">chintuchiinda01@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260976788089</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:16 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=79\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:17:02', '2026-05-08 14:17:02', '2026-05-08 14:16:52'),
(53, 'edwardmusole76@gmail.com', 'New User Registration - Chintu Chiinda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Chintu Chiinda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:chintuchiinda01@gmail.com\" style=\"color: #2E70DA;\">chintuchiinda01@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260976788089</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:16 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=79\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:17:03', '2026-05-08 14:17:02', '2026-05-08 14:16:52'),
(54, 'inutu.simasiku@edutrack.edu', 'New User Registration - Chintu Chiinda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Chintu Chiinda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:chintuchiinda01@gmail.com\" style=\"color: #2E70DA;\">chintuchiinda01@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260976788089</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:16 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=79\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:17:03', '2026-05-08 14:17:03', '2026-05-08 14:16:52'),
(55, 'nita.sichimwa@edutrack.edu', 'New User Registration - Chintu Chiinda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Chintu Chiinda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:chintuchiinda01@gmail.com\" style=\"color: #2E70DA;\">chintuchiinda01@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260976788089</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:16 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=79\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:17:04', '2026-05-08 14:17:03', '2026-05-08 14:16:52'),
(56, 'michael.siame@edutrack.edu', 'New User Registration - Wane Mary', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wane Mary</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wanengambi12@icloud.com\" style=\"color: #2E70DA;\">wanengambi12@icloud.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779297663</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:22 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=83\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:23:04', '2026-05-08 14:23:03', '2026-05-08 14:22:18'),
(57, 'marvinmoonga69@gmail.com', 'New User Registration - Wane Mary', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wane Mary</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wanengambi12@icloud.com\" style=\"color: #2E70DA;\">wanengambi12@icloud.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779297663</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:22 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=83\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:23:04', '2026-05-08 14:23:04', '2026-05-08 14:22:18'),
(58, 'edwardmusole76@gmail.com', 'New User Registration - Wane Mary', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wane Mary</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wanengambi12@icloud.com\" style=\"color: #2E70DA;\">wanengambi12@icloud.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779297663</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:22 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=83\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:23:07', '2026-05-08 14:23:04', '2026-05-08 14:22:18'),
(59, 'inutu.simasiku@edutrack.edu', 'New User Registration - Wane Mary', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wane Mary</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wanengambi12@icloud.com\" style=\"color: #2E70DA;\">wanengambi12@icloud.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779297663</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:22 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=83\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:23:08', '2026-05-08 14:23:07', '2026-05-08 14:22:18'),
(60, 'nita.sichimwa@edutrack.edu', 'New User Registration - Wane Mary', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Wane Mary</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:wanengambi12@icloud.com\" style=\"color: #2E70DA;\">wanengambi12@icloud.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260779297663</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:22 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=83\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:23:08', '2026-05-08 14:23:08', '2026-05-08 14:22:18'),
(61, 'michael.siame@edutrack.edu', 'New User Registration - Catherine Namakanda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Catherine Namakanda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:cathynamakanda75@gmail.com\" style=\"color: #2E70DA;\">cathynamakanda75@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260766635170</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:24 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=84\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:25:04', '2026-05-08 14:25:03', '2026-05-08 14:24:58'),
(62, 'marvinmoonga69@gmail.com', 'New User Registration - Catherine Namakanda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Catherine Namakanda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:cathynamakanda75@gmail.com\" style=\"color: #2E70DA;\">cathynamakanda75@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260766635170</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:24 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=84\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:25:04', '2026-05-08 14:25:04', '2026-05-08 14:24:58'),
(63, 'edwardmusole76@gmail.com', 'New User Registration - Catherine Namakanda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Catherine Namakanda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:cathynamakanda75@gmail.com\" style=\"color: #2E70DA;\">cathynamakanda75@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260766635170</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:24 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=84\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:25:05', '2026-05-08 14:25:04', '2026-05-08 14:24:58'),
(64, 'inutu.simasiku@edutrack.edu', 'New User Registration - Catherine Namakanda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Catherine Namakanda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:cathynamakanda75@gmail.com\" style=\"color: #2E70DA;\">cathynamakanda75@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260766635170</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:24 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=84\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:25:09', '2026-05-08 14:25:05', '2026-05-08 14:24:58'),
(65, 'nita.sichimwa@edutrack.edu', 'New User Registration - Catherine Namakanda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Catherine Namakanda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:cathynamakanda75@gmail.com\" style=\"color: #2E70DA;\">cathynamakanda75@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260766635170</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:24 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=84\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:25:10', '2026-05-08 14:25:09', '2026-05-08 14:24:58'),
(66, 'michael.siame@edutrack.edu', 'New User Registration - Fragester Mudenda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Fragester Mudenda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:fragestermudenda46@gmail.com\" style=\"color: #2E70DA;\">fragestermudenda46@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260773137696</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:38 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=85\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:39:05', '2026-05-08 14:39:04', '2026-05-08 14:38:39'),
(67, 'marvinmoonga69@gmail.com', 'New User Registration - Fragester Mudenda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Fragester Mudenda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:fragestermudenda46@gmail.com\" style=\"color: #2E70DA;\">fragestermudenda46@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260773137696</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:38 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=85\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:39:05', '2026-05-08 14:39:05', '2026-05-08 14:38:39'),
(68, 'edwardmusole76@gmail.com', 'New User Registration - Fragester Mudenda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Fragester Mudenda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:fragestermudenda46@gmail.com\" style=\"color: #2E70DA;\">fragestermudenda46@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260773137696</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:38 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=85\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:39:06', '2026-05-08 14:39:05', '2026-05-08 14:38:39'),
(69, 'inutu.simasiku@edutrack.edu', 'New User Registration - Fragester Mudenda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Fragester Mudenda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:fragestermudenda46@gmail.com\" style=\"color: #2E70DA;\">fragestermudenda46@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260773137696</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:38 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=85\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:39:06', '2026-05-08 14:39:06', '2026-05-08 14:38:39'),
(70, 'nita.sichimwa@edutrack.edu', 'New User Registration - Fragester Mudenda', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New User Registration</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A new user has just registered on the platform:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;\">\n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Name:</strong></td>\n                <td>Fragester Mudenda</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:fragestermudenda46@gmail.com\" style=\"color: #2E70DA;\">fragestermudenda46@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>+260773137696</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Role:</strong></td>\n                <td>Student</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Registered:</strong></td>\n                <td>May 8, 2026 12:38 PM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/users.php?action=view&amp;id=85\" \n           style=\"background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View User Profile\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'sent', 0, 10, NULL, '2026-05-08 14:39:07', '2026-05-08 14:39:06', '2026-05-08 14:38:39');
INSERT INTO `email_queue` (`id`, `recipient`, `subject`, `body`, `attachments`, `status`, `attempts`, `priority`, `scheduled_at`, `sent_at`, `last_attempt`, `created_at`) VALUES
(71, 'michael.siame@edutrack.edu', 'New Enrollment - Certificate in Information Technology', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New Course Enrollment</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A student has just enrolled in a course:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #10B981; margin: 20px 0;\">\n        <h3 style=\"margin: 0 0 15px 0; color: #333;\">Certificate in Information Technology</h3>\n        \n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Student:</strong></td>\n                <td>Mary Maseleni</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:maselenimary854@gmail.com\" style=\"color: #2E70DA;\">maselenimary854@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>Not provided</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Course Price:</strong></td>\n                <td style=\"font-size: 18px; color: #2E70DA; font-weight: bold;\">\n                    ZMW 2,500.00                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Payment Status:</strong></td>\n                <td>\n                    <span style=\"background: #fff3cd; \n                                 color: #856404; \n                                 padding: 3px 10px; border-radius: 3px; font-size: 12px; text-transform: uppercase;\">\n                        pending                    </span>\n                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrollment Status:</strong></td>\n                <td>Enrolled</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrolled:</strong></td>\n                <td>May 13, 2026 12:00 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/enrollments.php?action=view&amp;id=51\" \n           style=\"background: #10B981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View Enrollment Details\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'pending', 0, 10, NULL, NULL, NULL, '2026-05-13 15:55:59'),
(72, 'marvinmoonga69@gmail.com', 'New Enrollment - Certificate in Information Technology', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New Course Enrollment</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A student has just enrolled in a course:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #10B981; margin: 20px 0;\">\n        <h3 style=\"margin: 0 0 15px 0; color: #333;\">Certificate in Information Technology</h3>\n        \n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Student:</strong></td>\n                <td>Mary Maseleni</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:maselenimary854@gmail.com\" style=\"color: #2E70DA;\">maselenimary854@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>Not provided</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Course Price:</strong></td>\n                <td style=\"font-size: 18px; color: #2E70DA; font-weight: bold;\">\n                    ZMW 2,500.00                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Payment Status:</strong></td>\n                <td>\n                    <span style=\"background: #fff3cd; \n                                 color: #856404; \n                                 padding: 3px 10px; border-radius: 3px; font-size: 12px; text-transform: uppercase;\">\n                        pending                    </span>\n                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrollment Status:</strong></td>\n                <td>Enrolled</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrolled:</strong></td>\n                <td>May 13, 2026 12:00 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/enrollments.php?action=view&amp;id=51\" \n           style=\"background: #10B981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View Enrollment Details\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'pending', 0, 10, NULL, NULL, NULL, '2026-05-13 15:55:59'),
(73, 'edwardmusole76@gmail.com', 'New Enrollment - Certificate in Information Technology', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New Course Enrollment</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A student has just enrolled in a course:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #10B981; margin: 20px 0;\">\n        <h3 style=\"margin: 0 0 15px 0; color: #333;\">Certificate in Information Technology</h3>\n        \n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Student:</strong></td>\n                <td>Mary Maseleni</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:maselenimary854@gmail.com\" style=\"color: #2E70DA;\">maselenimary854@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>Not provided</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Course Price:</strong></td>\n                <td style=\"font-size: 18px; color: #2E70DA; font-weight: bold;\">\n                    ZMW 2,500.00                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Payment Status:</strong></td>\n                <td>\n                    <span style=\"background: #fff3cd; \n                                 color: #856404; \n                                 padding: 3px 10px; border-radius: 3px; font-size: 12px; text-transform: uppercase;\">\n                        pending                    </span>\n                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrollment Status:</strong></td>\n                <td>Enrolled</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrolled:</strong></td>\n                <td>May 13, 2026 12:00 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/enrollments.php?action=view&amp;id=51\" \n           style=\"background: #10B981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View Enrollment Details\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'pending', 0, 10, NULL, NULL, NULL, '2026-05-13 15:55:59'),
(74, 'inutu.simasiku@edutrack.edu', 'New Enrollment - Certificate in Information Technology', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New Course Enrollment</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A student has just enrolled in a course:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #10B981; margin: 20px 0;\">\n        <h3 style=\"margin: 0 0 15px 0; color: #333;\">Certificate in Information Technology</h3>\n        \n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Student:</strong></td>\n                <td>Mary Maseleni</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:maselenimary854@gmail.com\" style=\"color: #2E70DA;\">maselenimary854@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>Not provided</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Course Price:</strong></td>\n                <td style=\"font-size: 18px; color: #2E70DA; font-weight: bold;\">\n                    ZMW 2,500.00                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Payment Status:</strong></td>\n                <td>\n                    <span style=\"background: #fff3cd; \n                                 color: #856404; \n                                 padding: 3px 10px; border-radius: 3px; font-size: 12px; text-transform: uppercase;\">\n                        pending                    </span>\n                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrollment Status:</strong></td>\n                <td>Enrolled</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrolled:</strong></td>\n                <td>May 13, 2026 12:00 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/enrollments.php?action=view&amp;id=51\" \n           style=\"background: #10B981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View Enrollment Details\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'pending', 0, 10, NULL, NULL, NULL, '2026-05-13 15:55:59'),
(75, 'nita.sichimwa@edutrack.edu', 'New Enrollment - Certificate in Information Technology', '<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">\n    <h2 style=\"color: #2E70DA;\">New Course Enrollment</h2>\n    \n    <p>Hello Admin,</p>\n    \n    <p>A student has just enrolled in a course:</p>\n    \n    <div style=\"background: #f8f9fa; padding: 20px; border-left: 4px solid #10B981; margin: 20px 0;\">\n        <h3 style=\"margin: 0 0 15px 0; color: #333;\">Certificate in Information Technology</h3>\n        \n        <table width=\"100%\" cellpadding=\"5\" style=\"border-collapse: collapse;\">\n            <tr>\n                <td style=\"color: #666; width: 140px;\"><strong>Student:</strong></td>\n                <td>Mary Maseleni</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Email:</strong></td>\n                <td><a href=\"mailto:maselenimary854@gmail.com\" style=\"color: #2E70DA;\">maselenimary854@gmail.com</a></td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Phone:</strong></td>\n                <td>Not provided</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Course Price:</strong></td>\n                <td style=\"font-size: 18px; color: #2E70DA; font-weight: bold;\">\n                    ZMW 2,500.00                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Payment Status:</strong></td>\n                <td>\n                    <span style=\"background: #fff3cd; \n                                 color: #856404; \n                                 padding: 3px 10px; border-radius: 3px; font-size: 12px; text-transform: uppercase;\">\n                        pending                    </span>\n                </td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrollment Status:</strong></td>\n                <td>Enrolled</td>\n            </tr>\n            <tr>\n                <td style=\"color: #666;\"><strong>Enrolled:</strong></td>\n                <td>May 13, 2026 12:00 AM</td>\n            </tr>\n        </table>\n    </div>\n    \n    <p style=\"text-align: center; margin: 30px 0;\">\n        <a href=\"https://edutrackzambia.com/admin/pages/enrollments.php?action=view&amp;id=51\" \n           style=\"background: #10B981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;\">\n            View Enrollment Details\n        </a>\n    </p>\n    \n    <hr style=\"border: none; border-top: 1px solid #e9ecef; margin: 30px 0;\">\n    <p style=\"color: #666; font-size: 12px;\">\n        This is an automated notification from EduTrack LMS.<br>\n        You are receiving this because you are an administrator.\n    </p>\n</div>\n', NULL, 'pending', 0, 10, NULL, NULL, NULL, '2026-05-13 15:55:59'),
(76, 'marvinmoonga69@gmail.com', 'Verify your email address', '<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\"utf-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Verify Your Email Address</title>\n    <style>\n        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; color: #374151; line-height: 1.6; }\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }\n        .header { background: #1e3a5f; padding: 32px 30px; text-align: center; }\n        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; letter-spacing: -0.01em; }\n        .header p { color: rgba(255,255,255,0.8); margin: 6px 0 0; font-size: 14px; }\n        .body { padding: 32px 30px; }\n        .body p { margin: 0 0 14px; }\n        .card { background: #f8fafc; border-left: 4px solid #1e3a5f; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }\n        .card-success { border-left-color: #059669; background: #ecfdf5; }\n        .card-warning { border-left-color: #d97706; background: #fffbeb; }\n        .btn { display: inline-block; padding: 12px 28px; background: #1e3a5f; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 500; font-size: 15px; margin-top: 10px; }\n        .btn-success { background: #059669; }\n        .btn-warning { background: #d97706; }\n        .footer { padding: 24px 30px; text-align: center; font-size: 12px; color: #9ca3af; background: #f9fafb; border-top: 1px solid #e5e7eb; }\n        .footer a { color: #6b7280; text-decoration: none; }\n        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }\n        .small { font-size: 13px; color: #6b7280; }\n        .center { text-align: center; }\n        table.meta { width: 100%; border-collapse: collapse; margin: 16px 0; }\n        table.meta td { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }\n        table.meta td:last-child { text-align: right; font-weight: 500; }\n        @media only screen and (max-width: 480px) {\n            body { padding: 10px; }\n            .header, .body, .footer { padding-left: 20px; padding-right: 20px; }\n        }\n    </style>\n</head>\n<body>\n    <div class=\"container\">\n        <div class=\"header\">\n            <h1>Edutrack LMS</h1>\n            <p>One quick step to get started</p>\n        </div>\n        <div class=\"body\">\n            <p>Hi Chilala,</p>\n<p>Thank you for registering with Edutrack LMS. Please verify your email address to activate your account and access all features.</p>\n\n<div class=\"center\" style=\"margin: 28px 0;\">\n    <a href=\"https://edutrackzambia.com/verify-email/fe89aff3b6f7149d5500abccdbf6e02fa0f24e34571742b3d8210392f44311dd\" class=\"btn\">Verify Email Address</a>\n</div>\n\n<p class=\"small\">Or copy and paste this link into your browser:</p>\n<p class=\"small\" style=\"word-break: break-all; color: #6b7280;\">https://edutrackzambia.com/verify-email/fe89aff3b6f7149d5500abccdbf6e02fa0f24e34571742b3d8210392f44311dd</p>\n\n<div class=\"card card-warning\" style=\"margin-top: 24px;\">\n    <strong>Important:</strong> This verification link will expire in 24 hours. If you did not create this account, you can safely ignore this email.\n</div>\n        </div>\n        <div class=\"footer\">\n            <p><strong>Edutrack Computer Training College</strong><br>Kalomo, Zambia</p>\n            <p>edutrackzambia@gmail.com &bull; +260 770 666 937</p>\n            <p style=\"margin-top: 10px;\">&copy; 2026 Edutrack LMS. All rights reserved.</p>\n                    </div>\n    </div>\n</body>\n</html>\n', NULL, 'sent', 1, 0, '2026-06-05 11:28:10', '2026-06-05 11:28:10', NULL, '2026-06-05 11:28:10'),
(77, 'marvinmoonga69@gmail.com', 'Verify your email address', '<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\"utf-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Verify Your Email Address</title>\n    <style>\n        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; color: #374151; line-height: 1.6; }\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }\n        .header { background: #1e3a5f; padding: 32px 30px; text-align: center; }\n        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; letter-spacing: -0.01em; }\n        .header p { color: rgba(255,255,255,0.8); margin: 6px 0 0; font-size: 14px; }\n        .body { padding: 32px 30px; }\n        .body p { margin: 0 0 14px; }\n        .card { background: #f8fafc; border-left: 4px solid #1e3a5f; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }\n        .card-success { border-left-color: #059669; background: #ecfdf5; }\n        .card-warning { border-left-color: #d97706; background: #fffbeb; }\n        .btn { display: inline-block; padding: 12px 28px; background: #1e3a5f; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 500; font-size: 15px; margin-top: 10px; }\n        .btn-success { background: #059669; }\n        .btn-warning { background: #d97706; }\n        .footer { padding: 24px 30px; text-align: center; font-size: 12px; color: #9ca3af; background: #f9fafb; border-top: 1px solid #e5e7eb; }\n        .footer a { color: #6b7280; text-decoration: none; }\n        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }\n        .small { font-size: 13px; color: #6b7280; }\n        .center { text-align: center; }\n        table.meta { width: 100%; border-collapse: collapse; margin: 16px 0; }\n        table.meta td { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }\n        table.meta td:last-child { text-align: right; font-weight: 500; }\n        @media only screen and (max-width: 480px) {\n            body { padding: 10px; }\n            .header, .body, .footer { padding-left: 20px; padding-right: 20px; }\n        }\n    </style>\n</head>\n<body>\n    <div class=\"container\">\n        <div class=\"header\">\n            <h1>Edutrack LMS</h1>\n            <p>One quick step to get started</p>\n        </div>\n        <div class=\"body\">\n            <p>Hi Chilala,</p>\n<p>Thank you for registering with Edutrack LMS. Please verify your email address to activate your account and access all features.</p>\n\n<div class=\"center\" style=\"margin: 28px 0;\">\n    <a href=\"https://www.edutrackzambia.com/verify-email/96c1723cd57241736211adb75d65b3db7fe01032461f1d35961c64d5434a6e7e\" class=\"btn\">Verify Email Address</a>\n</div>\n\n<p class=\"small\">Or copy and paste this link into your browser:</p>\n<p class=\"small\" style=\"word-break: break-all; color: #6b7280;\">https://www.edutrackzambia.com/verify-email/96c1723cd57241736211adb75d65b3db7fe01032461f1d35961c64d5434a6e7e</p>\n\n<div class=\"card card-warning\" style=\"margin-top: 24px;\">\n    <strong>Important:</strong> This verification link will expire in 24 hours. If you did not create this account, you can safely ignore this email.\n</div>\n        </div>\n        <div class=\"footer\">\n            <p><strong>Edutrack Computer Training College</strong><br>Kalomo, Zambia</p>\n            <p>edutrackzambia@gmail.com &bull; +260 770 666 937</p>\n            <p style=\"margin-top: 10px;\">&copy; 2026 Edutrack LMS. All rights reserved.</p>\n                    </div>\n    </div>\n</body>\n</html>\n', NULL, 'sent', 1, 0, '2026-06-05 13:25:32', '2026-06-05 13:25:32', NULL, '2026-06-05 13:25:32');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `template_id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `template_type` varchar(50) DEFAULT 'Custom',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`template_id`, `template_name`, `subject`, `body`, `template_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'welcome_email', 'Welcome to EduTrack LMS!', '<h1>Welcome {{first_name}}!</h1><p>Thank you for joining EduTrack LMS. We are excited to have you on board.</p><p>You can now access all your courses from your dashboard.</p>', 'Welcome', 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 'enrollment_confirmation', 'Course Enrollment Confirmation', '<h1>Enrollment Confirmed</h1><p>Dear {{first_name}},</p><p>You have successfully enrolled in <strong>{{course_title}}</strong>.</p><p>Course starts on: {{start_date}}</p>', 'Enrollment', 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 'certificate_issued', 'Your Certificate is Ready!', '<h1>Congratulations {{first_name}}!</h1><p>Your certificate for <strong>{{course_title}}</strong> is now available.</p><p>Certificate Number: {{certificate_number}}</p><p>Download your certificate from your dashboard.</p>', 'Certificate', 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 'payment_confirmation', 'Payment Received', '<h1>Payment Confirmation</h1><p>Dear {{first_name}},</p><p>We have received your payment of {{amount}} {{currency}} for <strong>{{course_title}}</strong>.</p><p>Transaction ID: {{transaction_id}}</p>', 'Payment', 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 'assignment_deadline_reminder', 'Assignment Due Soon', '<h1>Reminder: Assignment Due</h1><p>Hi {{first_name}},</p><p>This is a reminder that your assignment <strong>{{assignment_title}}</strong> is due on {{due_date}}.</p>', 'Reminder', 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `intake_id` int(11) DEFAULT NULL,
  `enrolled_at` date NOT NULL,
  `start_date` date DEFAULT NULL,
  `progress` decimal(5,2) NOT NULL DEFAULT 0.00,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `enrollment_status` enum('Enrolled','In Progress','Completed','Dropped','Expired') DEFAULT 'Enrolled',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `completion_date` date DEFAULT NULL,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `certificate_blocked` tinyint(1) DEFAULT 0 COMMENT 'Certificate blocked until fully paid',
  `last_accessed` timestamp NULL DEFAULT NULL,
  `total_time_spent` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `student_id`, `course_id`, `intake_id`, `enrolled_at`, `start_date`, `progress`, `final_grade`, `enrollment_status`, `payment_status`, `amount_paid`, `completion_date`, `certificate_issued`, `certificate_blocked`, `last_accessed`, `total_time_spent`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 8, 1, 1, 2, '2025-01-15', '2025-01-15', 100.00, 92.50, 'Enrolled', 'completed', 250.00, '2025-04-10', 1, 1, NULL, 2880, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(2, 8, 1, 5, 5, '2025-01-15', '2025-01-16', 75.00, NULL, 'Enrolled', 'completed', 315.00, NULL, 0, 1, NULL, 2700, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(3, 8, 1, 10, 10, '2025-01-20', '2025-01-21', 45.00, NULL, 'Enrolled', 'completed', 360.00, NULL, 0, 1, NULL, 1620, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(4, 9, 2, 7, 7, '2025-01-15', '2025-01-15', 100.00, 88.00, 'Enrolled', 'completed', 342.00, '2025-04-25', 1, 1, NULL, 4200, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(5, 9, 2, 15, 15, '2025-01-15', '2025-01-16', 85.00, NULL, 'Enrolled', 'completed', 380.00, NULL, 0, 1, NULL, 2856, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(6, 9, 2, 17, 17, '2025-01-20', '2025-01-21', 60.00, NULL, 'In Progress', 'completed', 320.00, NULL, 0, 1, NULL, 1728, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(7, 10, 3, 18, 18, '2025-01-10', '2025-01-10', 100.00, 95.00, 'Enrolled', 'completed', 300.00, '2025-04-08', 1, 1, NULL, 2880, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(8, 10, 3, 19, 19, '2025-02-01', '2025-02-02', 30.00, NULL, 'Enrolled', 'completed', 405.00, NULL, 0, 1, NULL, 1152, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(9, 10, 3, 1, 2, '2025-01-15', '2025-01-15', 100.00, 87.50, 'Enrolled', 'completed', 250.00, '2025-04-12', 1, 1, NULL, 2640, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(10, 11, 4, 5, 5, '2025-01-10', '2025-01-10', 100.00, 91.00, 'Enrolled', 'completed', 315.00, '2025-04-05', 1, 1, NULL, 3600, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(11, 11, 4, 6, 6, '2025-02-01', '2025-02-02', 50.00, NULL, 'Enrolled', 'completed', 400.00, NULL, 0, 1, NULL, 2400, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(12, 11, 4, 9, 9, '2025-02-10', '2025-02-11', 25.00, NULL, 'Enrolled', 'completed', 320.00, NULL, 0, 1, NULL, 720, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(13, 12, 5, 11, 11, '2025-02-15', '2025-02-16', 40.00, NULL, 'Enrolled', 'completed', 495.00, NULL, 0, 1, NULL, 2160, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(14, 12, 5, 12, 12, '2025-01-25', '2025-01-26', 70.00, NULL, 'Enrolled', 'completed', 400.00, NULL, 0, 1, NULL, 2688, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(16, 13, 6, 3, 3, '2025-01-20', '2025-01-20', 35.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 672, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(17, 13, 6, 17, 17, '2025-01-20', '2025-01-21', 40.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1152, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(18, 14, 7, 13, 13, '2025-03-01', '2025-03-02', 15.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 720, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(19, 14, 7, 8, 8, '2025-03-01', '2025-03-02', 20.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1200, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(20, 15, 8, 18, 18, '2025-01-10', '2025-01-10', 90.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 2592, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(21, 15, 8, 3, 3, '2025-01-20', '2025-01-20', 100.00, 94.00, 'Enrolled', 'pending', 0.00, '2025-03-15', 1, 1, NULL, 1920, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(22, 16, 9, 10, 10, '2025-01-20', '2025-01-21', 65.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 2340, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(23, 16, 9, 12, 12, '2025-01-25', '2025-01-26', 40.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1536, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(24, 17, 10, 15, 15, '2025-01-15', '2025-01-16', 80.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 2688, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(25, 17, 10, 16, 16, '2025-02-05', '2025-02-06', 45.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1296, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(26, 18, 11, 1, 2, '2025-01-15', '2025-01-16', 55.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1584, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(27, 18, 11, 4, 4, '2025-02-15', '2025-02-16', 30.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 864, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(28, 19, 12, 5, 5, '2025-01-20', '2025-01-21', 10.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 360, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(29, 19, 12, 3, 3, '2025-01-20', '2025-01-21', 15.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 288, '2025-11-18 22:21:01', '2026-06-02 11:24:14', NULL),
(30, 26, 13, 1, 2, '2025-11-23', '2025-11-23', 0.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 0, '2025-11-23 11:45:51', '2026-06-02 11:24:14', NULL),
(31, 39, 33, 1, 2, '2025-12-28', '2025-12-28', 0.00, NULL, 'In Progress', '', 0.00, NULL, 0, 1, NULL, 0, '2025-12-28 18:42:19', '2026-06-02 11:24:14', NULL),
(34, 43, 37, 1, 2, '2026-01-09', NULL, 0.00, NULL, 'Enrolled', 'completed', 0.00, NULL, 0, 1, NULL, 0, '2026-01-09 04:42:00', '2026-06-02 11:24:14', NULL),
(35, 40, 34, 1, 2, '2026-01-09', NULL, 0.00, NULL, 'Enrolled', 'completed', 0.00, NULL, 0, 1, NULL, 0, '2026-01-09 04:42:00', '2026-06-02 11:24:14', NULL),
(37, 68, 62, 11, 11, '2026-03-16', '2026-03-16', 0.00, NULL, 'Enrolled', 'completed', 100.00, NULL, 0, 1, NULL, 0, '2026-03-16 21:36:01', '2026-06-02 11:24:14', NULL),
(38, 56, 50, 7, 7, '2026-03-17', '2026-03-17', 0.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 0, '2026-03-17 14:57:36', '2026-06-02 11:24:14', NULL),
(39, 78, 72, 1, 2, '2026-05-08', '2026-05-08', 100.00, 97.40, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:47:43', '2026-06-02 11:24:14', NULL),
(40, 83, 77, 1, 2, '2026-05-08', '2026-05-08', 100.00, 92.00, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(41, 77, 71, 1, 2, '2026-05-08', '2026-05-08', 100.00, 88.80, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(42, 79, 73, 1, 2, '2026-05-08', '2026-05-08', 100.00, 84.00, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(43, 85, 79, 1, 2, '2026-05-08', '2026-05-08', 100.00, 83.60, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(44, 86, 80, 1, 2, '2026-05-08', '2026-05-08', 100.00, 83.40, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(45, 84, 78, 1, 2, '2026-05-08', '2026-05-08', 100.00, 81.80, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(46, 87, 81, 1, 2, '2026-05-08', '2026-05-08', 100.00, 79.80, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(47, 80, 74, 1, 2, '2026-05-08', '2026-05-08', 100.00, 79.40, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(48, 81, 75, 1, 2, '2026-05-08', '2026-05-08', 100.00, 78.00, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(49, 82, 76, 1, 2, '2026-05-08', '2026-05-08', 100.00, 64.20, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 14:56:55', '2026-06-02 11:24:14', NULL),
(50, 88, 82, 1, 2, '2026-05-08', '2026-05-08', 100.00, 83.60, 'Completed', 'completed', 2500.00, '2026-05-08', 1, 0, NULL, 0, '2026-05-08 15:20:22', '2026-06-02 11:24:14', NULL),
(51, 90, 84, 27, 24, '2026-05-13', '2026-05-13', 0.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 0, '2026-05-13 13:55:59', '2026-06-02 11:24:14', NULL),
(52, 88, 82, 5, 5, '2026-05-09', '2026-05-09', 65.00, NULL, 'In Progress', 'completed', 3000.00, NULL, 0, 0, '2026-05-22 07:56:21', 720, '2026-05-09 07:56:21', '2026-06-02 11:24:14', NULL),
(53, 88, 82, 7, 7, '2026-05-16', '2026-05-16', 30.00, NULL, 'In Progress', 'pending', 500.00, NULL, 0, 0, '2026-05-22 07:56:21', 180, '2026-05-16 07:56:21', '2026-06-02 11:24:14', NULL),
(54, 88, 82, 11, 11, '2026-05-22', '2026-05-22', 5.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 0, '2026-05-22 07:56:21', 45, '2026-05-22 07:56:21', '2026-06-02 11:24:14', NULL),
(55, 88, 82, 3, 3, '2026-05-23', '2026-05-23', 0.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 0, NULL, 0, '2026-05-23 07:56:21', '2026-06-02 11:24:14', NULL),
(56, 91, 85, 1, 2, '2026-05-03', NULL, 75.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 0, NULL, 0, '2026-05-23 12:46:07', '2026-06-02 11:24:14', NULL),
(58, 88, 82, 4, 4, '2026-05-27', NULL, 0.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 0, '2026-05-27 08:04:19', '2026-06-02 11:24:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_payment_plans`
--

CREATE TABLE `enrollment_payment_plans` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `total_fee` decimal(10,2) NOT NULL COMMENT 'Full course fee',
  `total_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) GENERATED ALWAYS AS (`total_fee` - `total_paid`) STORED,
  `currency` varchar(3) NOT NULL DEFAULT 'ZMW',
  `payment_status` enum('pending','partial','completed','overdue') DEFAULT 'pending',
  `due_date` date DEFAULT NULL COMMENT 'Final payment due date',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollment_payment_plans`
--

INSERT INTO `enrollment_payment_plans` (`id`, `enrollment_id`, `user_id`, `course_id`, `total_fee`, `total_paid`, `currency`, `payment_status`, `due_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 8, 1, 2500.00, 250.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(2, 2, 8, 5, 3000.00, 315.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(3, 3, 8, 10, 1500.00, 360.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(4, 4, 9, 7, 3000.00, 342.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(5, 5, 9, 15, 2500.00, 380.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(6, 6, 9, 17, 950.00, 320.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(7, 7, 10, 18, 2500.00, 300.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(8, 8, 10, 19, 2500.00, 405.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(9, 9, 10, 1, 2500.00, 250.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(10, 10, 11, 5, 3000.00, 315.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(11, 11, 11, 6, 3000.00, 400.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(12, 12, 11, 9, 3000.00, 320.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(13, 13, 12, 11, 2500.00, 495.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(14, 14, 12, 12, 1500.00, 400.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(15, 16, 13, 3, 850.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(16, 17, 13, 17, 950.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(17, 18, 14, 13, 850.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(18, 19, 14, 8, 3000.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(19, 20, 15, 18, 2500.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(20, 21, 15, 3, 850.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(21, 22, 16, 10, 1500.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(22, 23, 16, 12, 1500.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(23, 24, 17, 15, 2500.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(24, 25, 17, 16, 950.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(25, 26, 18, 1, 2500.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(26, 27, 18, 4, 1500.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(27, 28, 19, 5, 3000.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(28, 29, 19, 3, 850.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(29, 30, 26, 1, 2500.00, 0.00, 'ZMW', 'partial', NULL, NULL, '2025-12-09 13:26:07', '2025-12-09 13:26:07'),
(32, 31, 39, 1, 2500.00, 1000.00, 'ZMW', 'partial', NULL, 'Payment plan for Microsoft Office Suite', '2025-12-28 18:42:19', '2025-12-28 18:42:19'),
(34, 32, 40, 1, 2500.00, 0.00, 'ZMW', 'partial', NULL, 'Sharon - Tuition Plan', '2026-01-09 04:40:44', '2026-01-09 04:40:44'),
(35, 34, 43, 1, 2500.00, 500.00, 'ZMW', 'partial', NULL, 'Betty - Tuition Plan', '2026-01-09 04:42:00', '2026-01-09 04:42:00'),
(36, 35, 40, 1, 2500.00, 500.00, 'ZMW', 'partial', NULL, 'Sharon - Tuition Plan', '2026-01-09 04:42:00', '2026-01-09 04:42:00'),
(37, 37, 68, 11, 2500.00, 0.00, 'ZMW', 'pending', NULL, NULL, '2026-03-16 21:36:01', '2026-03-16 21:36:01'),
(38, 38, 56, 7, 3000.00, 0.00, 'ZMW', 'pending', NULL, NULL, '2026-03-17 14:57:36', '2026-03-17 14:57:36'),
(39, 39, 78, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:47:43', '2026-05-08 14:47:43'),
(40, 40, 83, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(41, 41, 77, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(42, 42, 79, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(43, 43, 85, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(44, 44, 86, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(45, 45, 84, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(46, 46, 87, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(47, 47, 80, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(48, 48, 81, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(49, 49, 82, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 14:56:55', '2026-05-08 14:56:55'),
(50, 50, 88, 1, 2500.00, 2500.00, 'ZMW', 'completed', NULL, NULL, '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(51, 51, 90, 27, 2500.00, 0.00, 'ZMW', 'pending', NULL, NULL, '2026-05-13 13:55:59', '2026-05-13 13:55:59'),
(52, 58, 88, 4, 1500.00, 0.00, 'ZMW', 'pending', NULL, NULL, '2026-05-27 08:04:19', '2026-05-27 08:04:19');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `story` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_images`
--

CREATE TABLE `event_images` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hero_slides`
--

CREATE TABLE `hero_slides` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `cta_text` varchar(100) DEFAULT 'Get Started',
  `cta_link` varchar(255) DEFAULT 'courses.php',
  `secondary_cta_text` varchar(100) DEFAULT NULL,
  `secondary_cta_link` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `title`, `subtitle`, `description`, `image_path`, `cta_text`, `cta_link`, `secondary_cta_text`, `secondary_cta_link`, `is_active`, `display_order`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Launch Your Tech Career', 'With Industry-Recognized Skills', 'Join 5,000+ Zambians who transformed their lives through TEVETA-certified programs in Cybersecurity, Web Development, and Digital Marketing.', 'hero-slide-1.jpg', 'Explore Courses', 'courses.php', 'Visit Campus', 'campus.php', 1, 1, 1, '2026-05-08 18:23:12', '2026-05-08 18:23:12'),
(2, 'State-of-the-Art Computer Labs', 'Learn on Modern Equipment', 'Our facilities feature the latest hardware and software to ensure you gain practical experience with industry-standard tools.', 'hero-slide-2.jpg', 'Take a Tour', 'campus.php', 'View Programs', 'courses.php', 1, 2, 1, '2026-05-08 18:23:12', '2026-05-08 18:23:12'),
(3, 'Your Success is Our Mission', '85% Job Placement Rate', 'Our graduates work at top companies like MTN, Airtel, and Zambia National Commercial Bank. Start your journey to a rewarding tech career today.', 'hero-slide-3.jpg', 'Apply Now', 'register.php', 'Contact Us', 'contact.php', 1, 3, 1, '2026-05-08 18:23:12', '2026-05-08 18:23:12'),
(4, 'Launch Your Tech Career', 'With Industry-Recognized Skills', 'Join 5,000+ Zambians who transformed their lives through TEVETA-certified programs in Cybersecurity, Web Development, and Digital Marketing.', 'hero-slide-1.jpg', 'Explore Courses', 'courses.php', 'Visit Campus', 'campus.php', 1, 1, 1, '2026-05-08 18:23:41', '2026-05-08 18:23:41'),
(5, 'State-of-the-Art Computer Labs', 'Learn on Modern Equipment', 'Our facilities feature the latest hardware and software to ensure you gain practical experience with industry-standard tools.', 'hero-slide-2.jpg', 'Take a Tour', 'campus.php', 'View Programs', 'courses.php', 1, 2, 1, '2026-05-08 18:23:41', '2026-05-08 18:23:41'),
(6, 'Your Success is Our Mission', '85% Job Placement Rate', 'Our graduates work at top companies like MTN, Airtel, and Zambia National Commercial Bank. Start your journey to a rewarding tech career today.', 'hero-slide-3.jpg', 'Apply Now', 'register.php', 'Contact Us', 'contact.php', 1, 3, 1, '2026-05-08 18:23:41', '2026-05-08 18:23:41');

-- --------------------------------------------------------

--
-- Table structure for table `institution_photos`
--

CREATE TABLE `institution_photos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('campus','classroom','lab','event','faculty','student_life') DEFAULT 'campus',
  `image_path` varchar(255) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institution_photos`
--

INSERT INTO `institution_photos` (`id`, `title`, `description`, `category`, `image_path`, `is_featured`, `is_active`, `display_order`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(1, 'Main Campus Building', 'The welcoming entrance to Edutrack Computer Training College in Kalomo', 'campus', 'campus-main.jpg', 1, 1, 1, 1, '2026-05-08 18:23:12', '2026-05-08 18:23:12'),
(2, 'Computer Lab 1', 'Our primary computer lab with 30 workstations for hands-on learning', 'lab', 'lab-1.jpg', 1, 1, 2, 1, '2026-05-08 18:23:12', '2026-05-08 18:23:12'),
(3, 'Classroom Setting', 'Interactive learning environment with projector and modern teaching aids', 'classroom', 'classroom-1.jpg', 0, 1, 3, 1, '2026-05-08 18:23:12', '2026-05-08 18:23:12'),
(4, 'Student Workshop', 'Students participating in a practical cybersecurity workshop', 'event', 'event-workshop.jpg', 1, 1, 4, 1, '2026-05-08 18:23:12', '2026-05-08 18:23:12'),
(5, 'Graduation Ceremony', 'Celebrating our 2024 graduates and their achievements', 'event', 'graduation-2024.jpg', 1, 1, 5, 1, '2026-05-08 18:23:12', '2026-05-08 18:23:12'),
(6, 'Library & Study Area', 'Quiet space for students to study and access digital resources', 'campus', 'library.jpg', 0, 1, 6, 1, '2026-05-08 18:23:12', '2026-05-08 18:23:12');

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `years_experience` int(11) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `certifications` text DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_students` int(11) DEFAULT 0,
  `total_courses` int(11) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `user_id`, `bio`, `specialization`, `years_experience`, `education`, `certifications`, `rating`, `total_students`, `total_courses`, `is_verified`, `created_at`, `updated_at`) VALUES
(1, 2, 'Experienced ICT trainer with 10+ years in corporate training. Microsoft Certified Professional with expertise in Office Suite and digital literacy programs.', 'ICT & Digital Skills', 10, NULL, NULL, 4.85, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 3, 'Full-stack developer and certified instructor with passion for teaching modern programming languages. 8 years industry experience in software development.', 'Software Development', 8, NULL, NULL, 4.92, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 4, 'Cybersecurity specialist and ethical hacker with CISSP certification. Former network administrator with extensive experience in data security.', 'Cybersecurity & Networks', 12, NULL, NULL, 4.78, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 5, 'AI/ML researcher and data scientist with PhD in Computer Science. Published researcher with focus on practical applications of machine learning.', 'AI & Data Science', 6, NULL, NULL, 4.95, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 6, 'Business consultant and entrepreneur with MBA. Specializes in digital transformation, project management, and business strategy.', 'Business & Management', 15, NULL, NULL, 4.80, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 7, 'Award-winning graphic designer and digital marketer. Adobe Certified Expert with 7 years experience in creative industries.', 'Digital Media & Design', 7, NULL, NULL, 4.88, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 30, NULL, NULL, NULL, NULL, NULL, 0.00, 0, 0, 0, '2025-12-25 08:20:56', '2025-12-25 08:20:56'),
(8, 27, 'Principal of Edutrack Computer Training College', 'Educational Administration', NULL, NULL, NULL, 0.00, 0, 0, 1, '2025-12-25 09:40:13', '2025-12-25 09:40:13'),
(9, 28, 'Instructor at Edutrack Computer Training College', 'General', NULL, NULL, NULL, 0.00, 0, 0, 1, '2025-12-25 09:40:13', '2025-12-25 09:40:13'),
(10, 31, 'Instructor at Edutrack Computer Training College', 'General', NULL, NULL, NULL, 0.00, 0, 0, 1, '2025-12-25 09:40:13', '2025-12-25 09:40:13'),
(12, 1, NULL, NULL, NULL, NULL, NULL, 0.00, 0, 0, 0, '2026-01-09 19:06:16', '2026-01-09 19:06:16'),
(13, 91, 'Experienced instructor with expertise in computer training and software development.', 'Microsoft Office, Python Programming, Cybersecurity', 8, 'BSc Computer Science, TEVETA Certified Instructor', 'Microsoft Certified Trainer, CompTIA Security+', 4.80, 150, 3, 1, '2026-05-23 11:41:01', '2026-05-23 11:41:01');

-- --------------------------------------------------------

--
-- Table structure for table `intakes`
--

CREATE TABLE `intakes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `application_deadline` date DEFAULT NULL,
  `learning_deadline` date DEFAULT NULL,
  `max_students` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `enrollment_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `price_override` decimal(10,2) DEFAULT NULL,
  `status` enum('draft','open','closed','in_progress','completed') NOT NULL DEFAULT 'draft',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `intakes`
--

INSERT INTO `intakes` (`id`, `course_id`, `name`, `start_date`, `end_date`, `application_deadline`, `learning_deadline`, `max_students`, `enrollment_count`, `price_override`, `status`, `is_default`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 0, 'Cybersecurity Fundamentals (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(2, 1, 'Certificate in Microsoft Office Suite (Default)', '2025-01-15', '2025-04-15', '2025-04-15', NULL, 30, 18, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(3, 3, 'Certificate in Digital Literacy (Default)', '2025-01-20', '2025-03-20', '2025-03-20', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(4, 4, 'Certificate in Record Management (Default)', '2025-02-15', '2025-05-15', '2025-05-15', NULL, 30, 1, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(5, 5, 'Certificate in Python Programming (Default)', '2025-01-10', '2025-04-10', '2025-04-10', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(6, 6, 'Certificate in Java Programming (Default)', '2025-02-01', '2025-06-01', '2025-06-01', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(7, 7, 'Certificate in Web Development (Default)', '2025-01-15', '2025-04-30', '2025-04-30', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(8, 8, 'Certificate in Mobile App Development (Default)', '2025-03-01', '2025-07-30', '2025-07-30', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(9, 9, 'Certificate in Software Engineering (Default)', '2025-02-10', '2025-05-10', '2025-05-10', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(10, 10, 'Certificate in Data Analysis (Default)', '2025-01-20', '2025-04-20', '2025-04-20', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(11, 11, 'Certificate in Cyber Security (Default)', '2025-02-15', '2025-06-30', '2025-06-30', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(12, 12, 'Certificate in Database Management Systems (Default)', '2025-01-25', '2025-05-25', '2025-05-25', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(13, 13, 'Certificate in Artificial Intelligence (Default)', '2025-03-01', '2025-07-01', '2025-07-01', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(14, 14, 'Certificate in Internet of Things (Default)', '2025-02-20', '2025-05-20', '2025-05-20', NULL, 30, 0, NULL, 'draft', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(15, 15, 'Certificate in Graphic Designing (Default)', '2025-01-15', '2025-04-30', '2025-04-30', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(16, 16, 'Certificate in Digital & Content Creation (Default)', '2025-02-05', '2025-05-05', '2025-05-05', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(17, 17, 'Certificate in Digital Marketing (Default)', '2025-01-20', '2025-04-20', '2025-04-20', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(18, 18, 'Certificate in Entrepreneurship (Default)', '2025-01-10', '2025-04-10', '2025-04-10', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(19, 19, 'Certificate in Project Management (Default)', '2025-02-01', '2025-06-01', '2025-06-01', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(20, 20, 'Certificate in Financial Technology (Default)', '2025-03-01', '2025-06-15', '2025-06-15', NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(21, 24, 'Certificate in ICT Support & Hardware Repair (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(22, 25, 'Certificate in Computer Studies (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(23, 26, 'Certificate in Computer Science General (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(24, 27, 'Certificate in Information Technology (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(25, 28, 'Certificate in Computer & Business Handling (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(26, 29, 'Certificate in C++ Programming (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(27, 30, 'Certificate in Sales & Marketing (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(28, 31, 'Certificate in Monitoring & Evaluation (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(29, 32, 'Certificate in Purchasing & Supply (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(30, 33, 'Certificate in E-Commerce & Online Business (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(31, 34, 'Certificate in Secretarial & Office Management (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'open', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14'),
(32, 35, 'Cybersecurity Fundamentals (Default)', NULL, NULL, NULL, NULL, 30, 0, NULL, 'draft', 1, 0, '2026-06-02 20:24:14', '2026-06-02 20:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` int(10) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'ZMW',
  `payment_method` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('draft','sent','paid','cancelled') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `payment_id`, `student_id`, `course_id`, `invoice_number`, `amount`, `discount`, `tax`, `total`, `currency`, `payment_method`, `description`, `invoice_date`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 24, 82, 5, 'INV-2026-00001', 3000.00, 0.00, 0.00, 3000.00, 'ZMW', 'N/A', 'Payment for: Certificate in Python Programming', '2026-05-23', NULL, 'paid', '2026-05-23 09:18:00', '2026-05-23 09:18:00'),
(2, 25, 82, 7, 'INV-2026-00002', 500.00, 0.00, 0.00, 500.00, 'ZMW', 'N/A', 'Payment for: Certificate in Web Development', '2026-05-23', NULL, 'paid', '2026-05-23 09:23:13', '2026-05-23 09:23:13');

-- --------------------------------------------------------

--
-- Table structure for table `lenco_collections`
--

CREATE TABLE `lenco_collections` (
  `id` int(11) NOT NULL,
  `lenco_collection_id` varchar(100) DEFAULT NULL COMMENT 'Lenco collection ID',
  `reference` varchar(100) NOT NULL COMMENT 'Our reference',
  `lenco_reference` varchar(100) DEFAULT NULL COMMENT 'Lenco reference',
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'ZMW',
  `phone` varchar(20) NOT NULL COMMENT 'Customer phone number',
  `country` varchar(2) NOT NULL DEFAULT 'ZM',
  `status` enum('pending','pay-offline','successful','failed') DEFAULT 'pending',
  `operator_transaction_id` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'registration_fee' COMMENT 'registration_fee or course_payment',
  `fee` decimal(10,2) DEFAULT NULL,
  `settlement_status` varchar(20) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `metadata` text DEFAULT NULL COMMENT 'JSON metadata',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lenco_transactions`
--

CREATE TABLE `lenco_transactions` (
  `id` int(11) NOT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference` varchar(100) NOT NULL COMMENT 'Unique payment reference (LENCO-XXXX-TIMESTAMP)',
  `user_id` int(11) NOT NULL COMMENT 'FK to users table',
  `enrollment_id` int(11) DEFAULT NULL COMMENT 'FK to enrollments table',
  `course_id` int(11) DEFAULT NULL COMMENT 'FK to courses table',
  `amount` decimal(15,2) NOT NULL COMMENT 'Expected payment amount',
  `currency` varchar(3) NOT NULL DEFAULT 'ZMW' COMMENT 'Currency code (ZMW, NGN, USD)',
  `virtual_account_number` varchar(50) DEFAULT NULL COMMENT 'Lenco virtual account number',
  `virtual_account_bank` varchar(100) DEFAULT NULL COMMENT 'Bank name for virtual account',
  `virtual_account_name` varchar(255) DEFAULT NULL COMMENT 'Account name displayed to payer',
  `lenco_account_id` varchar(100) DEFAULT NULL COMMENT 'Lenco internal account ID',
  `lenco_transaction_id` varchar(100) DEFAULT NULL COMMENT 'Lenco transaction ID (set on completion)',
  `status` enum('pending','successful','failed','expired','reversed') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL COMMENT 'When payment was confirmed',
  `expires_at` datetime DEFAULT NULL COMMENT 'When this payment request expires',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional transaction metadata',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `lenco_webhook_logs`
--

CREATE TABLE `lenco_webhook_logs` (
  `id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL COMMENT 'Webhook event type',
  `lenco_transaction_id` varchar(255) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Raw webhook payload',
  `signature` varchar(255) DEFAULT NULL COMMENT 'Webhook signature',
  `signature_valid` tinyint(1) DEFAULT NULL COMMENT 'Was signature valid?',
  `processed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Has this been processed?',
  `error_message` text DEFAULT NULL COMMENT 'Error if processing failed',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Source IP address',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `lenco_webhook_logs`
--

INSERT INTO `lenco_webhook_logs` (`id`, `event_type`, `lenco_transaction_id`, `payload`, `signature`, `signature_valid`, `processed`, `error_message`, `ip_address`, `created_at`) VALUES
(1, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 06:23:32'),
(2, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 06:24:32'),
(3, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 06:26:32'),
(4, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 06:31:32'),
(5, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 06:41:32'),
(6, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 06:56:32'),
(7, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:26:32'),
(8, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:32:08'),
(9, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:32:08'),
(10, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:33:08'),
(11, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:33:08'),
(12, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:35:08'),
(13, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:35:08'),
(14, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:40:08'),
(15, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:40:08'),
(16, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:50:08'),
(17, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:50:48'),
(18, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 07:56:32'),
(19, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 08:05:08'),
(20, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 08:05:08'),
(21, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 08:26:32'),
(22, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 08:35:08'),
(23, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 08:35:48'),
(24, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 08:56:32'),
(25, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 09:05:08'),
(26, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 09:05:08'),
(27, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 09:26:32'),
(28, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 09:35:08'),
(29, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 09:35:08'),
(30, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 09:56:32'),
(31, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 10:05:08'),
(32, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 10:05:08'),
(33, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 10:26:32'),
(34, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 10:35:10'),
(35, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 10:35:10'),
(36, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 10:56:32'),
(37, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 11:05:08'),
(38, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 11:05:08'),
(39, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 11:26:32'),
(40, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 11:35:08'),
(41, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 11:35:43'),
(42, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 11:56:32'),
(43, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 12:05:08'),
(44, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 12:05:08'),
(45, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 12:26:32'),
(46, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 12:35:09'),
(47, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 12:35:09'),
(48, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 12:56:32'),
(49, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 13:05:08'),
(50, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 13:05:08'),
(51, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 13:26:32'),
(52, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 13:35:08'),
(53, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 13:35:08'),
(54, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 13:56:32'),
(55, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 14:05:08'),
(56, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 14:05:08'),
(57, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 14:26:32'),
(58, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 14:35:08'),
(59, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 14:35:08'),
(60, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 14:56:32'),
(61, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 15:05:08'),
(62, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 15:05:08'),
(63, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 15:26:32'),
(64, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 15:35:08'),
(65, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 15:35:08'),
(66, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 15:56:32'),
(67, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 16:05:08'),
(68, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 16:05:08'),
(69, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 16:26:32'),
(70, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 16:35:08'),
(71, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 16:35:08'),
(72, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 16:56:32'),
(73, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 17:05:08'),
(74, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 17:05:08'),
(75, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 17:26:32'),
(76, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 17:35:08');
INSERT INTO `lenco_webhook_logs` (`id`, `event_type`, `lenco_transaction_id`, `payload`, `signature`, `signature_valid`, `processed`, `error_message`, `ip_address`, `created_at`) VALUES
(77, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 17:35:08'),
(78, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 17:56:32'),
(79, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 18:05:08'),
(80, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 18:05:08'),
(81, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 18:26:32'),
(82, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 18:35:08'),
(83, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 18:35:08'),
(84, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 18:56:32'),
(85, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 19:05:08'),
(86, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 19:05:08'),
(87, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 19:26:32'),
(88, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 19:35:08'),
(89, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 19:35:08'),
(90, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 19:56:32'),
(91, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 20:05:08'),
(92, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 20:05:48'),
(93, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 20:26:32'),
(94, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 20:35:08'),
(95, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 20:35:08'),
(96, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 20:56:32'),
(97, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 21:05:08'),
(98, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 21:05:08'),
(99, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 21:26:32'),
(100, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 21:35:09'),
(101, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 21:35:48'),
(102, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 21:56:32'),
(103, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 22:05:11'),
(104, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 22:05:42'),
(105, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 22:26:32'),
(106, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 22:35:08'),
(107, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 22:35:08'),
(108, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 22:56:32'),
(109, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 23:05:08'),
(110, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 23:05:08'),
(111, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 23:26:32'),
(112, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 23:35:09'),
(113, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 23:35:44'),
(114, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-16 23:56:32'),
(115, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 00:05:08'),
(116, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 00:05:08'),
(117, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 00:26:32'),
(118, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 00:35:08'),
(119, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 00:35:08'),
(120, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 00:56:32'),
(121, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 01:05:08'),
(122, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 01:05:08'),
(123, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 01:26:32'),
(124, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 01:35:08'),
(125, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 01:35:08'),
(126, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 01:56:32'),
(127, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 02:05:08'),
(128, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 02:05:08'),
(129, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 02:26:32'),
(130, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 02:35:08'),
(131, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 02:35:08'),
(132, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 02:56:32'),
(133, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 03:05:08'),
(134, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 03:05:40'),
(135, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 03:26:32'),
(136, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 03:35:08'),
(137, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 03:35:08'),
(138, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 03:56:32'),
(139, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 04:05:08'),
(140, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 04:05:08'),
(141, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 04:26:32'),
(142, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 04:35:09'),
(143, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 04:35:09'),
(144, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 04:56:32'),
(145, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 05:05:08'),
(146, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 05:05:08'),
(147, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 05:26:32'),
(148, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 05:35:08'),
(149, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 05:35:08'),
(150, 'incoming', NULL, '{\"event\":\"transaction.credit\",\"data\":{\"id\":\"e72c9c99-f5e7-42b5-8fb3-d3f410991393\",\"amount\":\"100.00\",\"currency\":\"ZMW\",\"narration\":\"RAYTON MUDENDA MP260516.0823.H17548\",\"type\":\"credit\",\"datetime\":\"2026-05-16T06:23:32.100Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":\"121.50\"}}', 'bfe434ae5ef990c910e22752cfe5ef99337ba307bfa80cee812028f8d6afc277ac5e5d888ca38f82c2295911656a006ffd050f2bebde8454d64d4087ca4d83ec', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 05:56:32'),
(151, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 06:05:08'),
(152, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 06:05:08');
INSERT INTO `lenco_webhook_logs` (`id`, `event_type`, `lenco_transaction_id`, `payload`, `signature`, `signature_valid`, `processed`, `error_message`, `ip_address`, `created_at`) VALUES
(153, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 06:35:08'),
(154, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 06:35:08'),
(155, 'incoming', NULL, '{\"event\":\"transaction.debit\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"currency\":\"ZMW\",\"narration\":\"yeah / 2613602412\",\"type\":\"debit\",\"datetime\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"balance\":null}}', '37a4d4f0c9922e555abfeb819e1566d6f9142257220e22cac066e9872d7e5fc67eea5da2f025c067365996d2a1883be2c50da3cb48c554deafc06ee59ab11acb', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 07:05:08'),
(156, 'incoming', NULL, '{\"event\":\"transfer.successful\",\"data\":{\"id\":\"81873b7e-4d2f-4ded-b3b4-e10e5891b0a8\",\"amount\":\"110.00\",\"fee\":\"8.50\",\"currency\":\"ZMW\",\"narration\":\"yeah\",\"initiatedAt\":\"2026-05-16T07:32:05.356Z\",\"completedAt\":\"2026-05-16T07:32:08.179Z\",\"accountId\":\"0831d8d3-e2c6-4127-bcb0-7f897a4cdd4d\",\"creditAccount\":{\"type\":\"mobile-money\",\"accountName\":\"siame michael\",\"phone\":\"0771216339\",\"operator\":\"airtel\",\"country\":\"zm\"},\"status\":\"successful\",\"reasonForFailure\":null,\"reference\":null,\"lencoReference\":\"2613602412\",\"extraData\":{\"nipSessionId\":null},\"source\":\"banking-app\"}}', '7c7cd76353b6c105e450b09e0097eafb1e0dfd00fb9e6cded4cc3af3982ae51f972a2a877e3eaf4fae8413a0966bd5a29e40379846651a48d3a6f17f6700d4db', 0, 0, 'Invalid signature', '209.38.164.16', '2026-05-17 07:05:08');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` longtext DEFAULT NULL,
  `lesson_type` enum('Video','Reading','Quiz','Assignment','Live Session','Download') DEFAULT 'Reading',
  `duration_minutes` int(11) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `video_url` varchar(255) DEFAULT NULL,
  `video_duration` int(11) DEFAULT NULL,
  `is_preview` tinyint(1) DEFAULT 0,
  `is_mandatory` tinyint(1) DEFAULT 1,
  `points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `module_id`, `title`, `content`, `lesson_type`, `duration_minutes`, `display_order`, `video_url`, `video_duration`, `is_preview`, `is_mandatory`, `points`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Welcome to Python Programming', 'Introduction to the course and what you will learn', 'Video', 15, 1, NULL, NULL, 1, 1, 5, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(2, 1, 'Installing Python and IDE Setup', 'Step-by-step guide to install Python and set up your development environment', 'Video', 30, 2, NULL, NULL, 1, 1, 10, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(3, 1, 'Your First Python Program', 'Writing and running your first \"Hello World\" program', 'Reading', 20, 3, NULL, NULL, 0, 1, 10, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(4, 1, 'Python Syntax Basics', 'Understanding Python syntax, indentation, and comments', 'Video', 25, 4, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(5, 2, 'Numbers in Python', 'Working with integers, floats, and complex numbers', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(6, 2, 'Strings and String Methods', 'String manipulation and built-in string methods', 'Video', 35, 2, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(7, 2, 'Lists and Tuples', 'Understanding ordered collections in Python', 'Reading', 40, 3, NULL, NULL, 0, 1, 20, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(8, 2, 'Dictionaries and Sets', 'Working with key-value pairs and unique collections', 'Video', 35, 4, NULL, NULL, 0, 1, 20, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(9, 3, 'If-Else Statements', 'Conditional logic and decision making', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(10, 3, 'For Loops', 'Iterating over sequences with for loops', 'Video', 35, 2, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(11, 3, 'While Loops', 'Using while loops for repeated execution', 'Reading', 25, 3, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(12, 3, 'Control Flow Quiz', 'Test your understanding of control flow', 'Quiz', 30, 4, NULL, NULL, 0, 1, 25, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(13, 7, 'Introduction to HTML5', 'Overview of HTML5 and its new features', 'Video', 25, 1, NULL, NULL, 1, 1, 10, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(14, 7, 'HTML Document Structure', 'Understanding the basic structure of an HTML document', 'Video', 30, 2, NULL, NULL, 1, 1, 10, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(15, 7, 'Semantic HTML Elements', 'Using semantic tags like header, nav, article, section', 'Reading', 35, 3, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(16, 7, 'Forms and Input Elements', 'Creating forms with various input types', 'Video', 40, 4, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(17, 8, 'CSS Basics and Selectors', 'Introduction to CSS syntax and selectors', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(18, 8, 'Box Model and Layout', 'Understanding the CSS box model', 'Video', 35, 2, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(19, 8, 'Flexbox Layout', 'Modern layout with Flexbox', 'Reading', 40, 3, NULL, NULL, 0, 1, 20, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(20, 8, 'Responsive Design with Media Queries', 'Creating responsive layouts', 'Video', 45, 4, NULL, NULL, 0, 1, 20, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(28, 20, 'Word Module Notes (Download)', '<h3>Course Material</h3><p>Please download the <strong>Complete Word Mastery Guide</strong> from the resources section below. This PDF covers all topics in this module.</p>', 'Reading', NULL, 1, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(29, 20, 'Getting Started & Text Basics', 'Overview of the interface and basic text entry.', 'Video', NULL, 2, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(30, 20, 'Formatting & Paragraph Layouts', 'Mastering fonts, colors, spacing, and lists.', 'Video', NULL, 3, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(31, 20, 'Working with Objects & Tables', 'Inserting images, shapes, and organizing data in tables.', 'Video', NULL, 4, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(32, 20, 'Word Practical Project', 'Create a professional CV using the skills learned.', 'Assignment', NULL, 5, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(33, 21, 'Excel Module Notes (Download)', '<h3>Course Material</h3><p>Please download the <strong>Complete Excel Mastery Guide</strong> from the resources section below. This PDF covers all topics in this module.</p>', 'Reading', NULL, 1, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(34, 21, 'Interface & Cell Basics', 'Understanding rows, columns, and data entry.', 'Video', NULL, 2, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(35, 21, 'Essential Formulas & Functions', 'Learning SUM, AVERAGE, and basic arithmetic formulas.', 'Video', NULL, 3, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(36, 21, 'Data Analysis & Charts', 'Visualizing data with charts and sorting information.', 'Video', NULL, 4, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(37, 21, 'Excel Budget Project', 'Create a monthly budget spreadsheet with formulas.', 'Assignment', NULL, 5, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(38, 22, 'PPT & Publisher Notes (Download)', '<h3>Course Material</h3><p>Please download the <strong>Presentation & Design Guide</strong> from the resources section below. This PDF covers both PowerPoint and Publisher.</p>', 'Reading', NULL, 1, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(39, 22, 'PowerPoint Essentials', 'Creating slides, adding content, and using themes.', 'Video', NULL, 2, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(40, 22, 'Transitions & Presenting', 'Animating slides and managing the presenter view.', 'Video', NULL, 3, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(41, 22, 'Publisher & Graphic Design', 'Creating brochures and flyers using Publisher.', 'Video', NULL, 4, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(42, 22, 'Final Course Assessment', 'Final exam covering Word, Excel, PowerPoint, and Publisher.', 'Quiz', NULL, 5, NULL, NULL, 0, 1, 0, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(43, 23, 'What is Cybersecurity?', '<h2>What is Cybersecurity?</h2>\n<p>Cybersecurity is the practice of protecting systems, networks, and programs from digital attacks. These cyberattacks are usually aimed at accessing, changing, or destroying sensitive information; extorting money from users; or interrupting normal business processes.</p>\n<h3>Why Cybersecurity Matters</h3>\n<ul>\n<li><strong>Data Protection:</strong> Organizations hold vast amounts of sensitive data</li>\n<li><strong>Financial Impact:</strong> Cybercrime costs the global economy billions annually</li>\n<li><strong>National Security:</strong> Critical infrastructure needs protection</li>\n<li><strong>Personal Privacy:</strong> Individuals need protection from identity theft</li>\n</ul>\n<h3>The CIA Triad</h3>\n<p>The foundation of cybersecurity is built on three principles:</p>\n<ul>\n<li><strong>Confidentiality:</strong> Ensuring information is accessible only to authorized users</li>\n<li><strong>Integrity:</strong> Maintaining the accuracy and completeness of data</li>\n<li><strong>Availability:</strong> Ensuring systems and data are accessible when needed</li>\n</ul>\n<h3>Key Terminology</h3>\n<ul>\n<li><strong>Asset:</strong> Anything of value to an organization (data, systems, hardware)</li>\n<li><strong>Vulnerability:</strong> A weakness that could be exploited</li>\n<li><strong>Threat:</strong> A potential danger to an asset</li>\n<li><strong>Risk:</strong> The likelihood and impact of a threat exploiting a vulnerability</li>\n<li><strong>Exploit:</strong> A method used to take advantage of a vulnerability</li>\n</ul>', 'Video', 30, 1, NULL, NULL, 1, 1, 10, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(44, 23, 'The Cyber Threat Landscape', '<h2>The Cyber Threat Landscape</h2>\n<p>Understanding who attacks systems and why is crucial for effective defense.</p>\n<h3>Types of Threat Actors</h3>\n<ul>\n<li><strong>Script Kiddies:</strong> Unskilled attackers using existing tools</li>\n<li><strong>Hacktivists:</strong> Attackers motivated by political or social causes</li>\n<li><strong>Cybercriminals:</strong> Organized groups seeking financial gain</li>\n<li><strong>State-Sponsored Actors:</strong> Nation-state backed attackers</li>\n<li><strong>Insider Threats:</strong> Malicious or negligent employees</li>\n</ul>\n<h3>Common Attack Motivations</h3>\n<ul>\n<li>Financial gain (ransomware, fraud)</li>\n<li>Political influence (election interference)</li>\n<li>Corporate espionage (intellectual property theft)</li>\n<li>Disruption (DDoS attacks on critical services)</li>\n</ul>\n<h3>Statistics in Zambia and Africa</h3>\n<p>Africa is experiencing rapid digital transformation, making cybersecurity increasingly important. Mobile money platforms, government services, and businesses all face growing cyber threats.</p>', 'Video', 25, 2, NULL, NULL, 0, 1, 10, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(45, 23, 'Cybersecurity Career Paths', '<h2>Cybersecurity Career Paths</h2>\n<p>The cybersecurity field offers diverse career opportunities with strong demand globally and in Zambia.</p>\n<h3>Entry-Level Roles</h3>\n<ul>\n<li><strong>Security Analyst:</strong> Monitor systems for threats and vulnerabilities</li>\n<li><strong>Junior Penetration Tester:</strong> Test systems for vulnerabilities</li>\n<li><strong>SOC Analyst:</strong> Work in Security Operations Centers monitoring alerts</li>\n</ul>\n<h3>Mid-Level Roles</h3>\n<ul>\n<li><strong>Security Engineer:</strong> Design and implement security solutions</li>\n<li><strong>Incident Responder:</strong> Handle security breaches and incidents</li>\n<li><strong>Security Consultant:</strong> Advise organizations on security posture</li>\n</ul>\n<h3>Advanced Roles</h3>\n<ul>\n<li><strong>Security Architect:</strong> Design enterprise security infrastructure</li>\n<li><strong>CISO:</strong> Lead organization-wide security strategy</li>\n</ul>\n<h3>Certifications to Consider</h3>\n<ul>\n<li>CompTIA Security+ (Entry level)</li>\n<li>CEH - Certified Ethical Hacker</li>\n<li>CISSP - Certified Information Systems Security Professional</li>\n</ul>', 'Video', 20, 3, NULL, NULL, 0, 1, 10, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(46, 23, 'Module 1: Knowledge Check', '<h2>Module 1 Knowledge Check</h2>\n<p>Test your understanding of cybersecurity fundamentals with this practice quiz.</p>', 'Quiz', 20, 4, NULL, NULL, 0, 1, 25, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(47, 24, 'The OSI Model', '<h2>The OSI Model Explained</h2>\n<p>The Open Systems Interconnection (OSI) model is a conceptual framework that standardizes network communication into seven layers.</p>\n<h3>The Seven Layers</h3>\n<table class=\"w-full border-collapse border\">\n<tr><th class=\"border p-2\">Layer</th><th class=\"border p-2\">Name</th><th class=\"border p-2\">Function</th></tr>\n<tr><td class=\"border p-2\">7</td><td class=\"border p-2\">Application</td><td class=\"border p-2\">HTTP, FTP, SMTP - User interfaces</td></tr>\n<tr><td class=\"border p-2\">6</td><td class=\"border p-2\">Presentation</td><td class=\"border p-2\">Data formatting, encryption</td></tr>\n<tr><td class=\"border p-2\">5</td><td class=\"border p-2\">Session</td><td class=\"border p-2\">Session management</td></tr>\n<tr><td class=\"border p-2\">4</td><td class=\"border p-2\">Transport</td><td class=\"border p-2\">TCP, UDP - Reliable delivery</td></tr>\n<tr><td class=\"border p-2\">3</td><td class=\"border p-2\">Network</td><td class=\"border p-2\">IP, routing - Logical addressing</td></tr>\n<tr><td class=\"border p-2\">2</td><td class=\"border p-2\">Data Link</td><td class=\"border p-2\">MAC addresses, switches</td></tr>\n<tr><td class=\"border p-2\">1</td><td class=\"border p-2\">Physical</td><td class=\"border p-2\">Cables, signals, hardware</td></tr>\n</table>\n<h3>Why the OSI Model Matters for Security</h3>\n<p>Understanding the OSI model helps security professionals identify where attacks occur and implement security controls at appropriate layers.</p>', 'Video', 35, 1, NULL, NULL, 1, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(48, 24, 'TCP/IP and Network Protocols', '<h2>TCP/IP and Network Protocols</h2>\n<p>The TCP/IP model is the practical implementation of network communication used on the internet today.</p>\n<h3>Key Protocols and Their Security Implications</h3>\n<ul>\n<li><strong>HTTP (Port 80):</strong> Unencrypted web traffic - vulnerable to interception</li>\n<li><strong>HTTPS (Port 443):</strong> Encrypted web traffic using TLS/SSL</li>\n<li><strong>FTP (Port 21):</strong> File transfer - sends credentials in plaintext</li>\n<li><strong>SSH (Port 22):</strong> Secure remote access - encrypted alternative to Telnet</li>\n<li><strong>DNS (Port 53):</strong> Domain resolution - target for cache poisoning</li>\n<li><strong>SMTP (Port 25):</strong> Email sending - often exploited for spam</li>\n</ul>\n<h3>Network Segmentation</h3>\n<p>Dividing networks into segments (VLANs) limits the spread of attacks. Critical systems should be isolated from general user networks.</p>', 'Video', 30, 2, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(49, 24, 'Network Security Basics', '<h2>Network Security Basics</h2>\n<p>Protecting network infrastructure is the first line of defense against cyber attacks.</p>\n<h3>Common Network Attacks</h3>\n<ul>\n<li><strong>Man-in-the-Middle (MitM):</strong> Intercepting communication between parties</li>\n<li><strong>ARP Spoofing:</strong> Falsifying ARP messages to redirect traffic</li>\n<li><strong>DNS Spoofing:</strong> Corrupting DNS cache to redirect users</li>\n<li><strong>Packet Sniffing:</strong> Capturing and analyzing network traffic</li>\n</ul>\n<h3>Network Security Controls</h3>\n<ul>\n<li><strong>Network Access Control (NAC):</strong> Controls device access to the network</li>\n<li><strong>Virtual LANs (VLANs):</strong> Segment networks logically</li>\n<li><strong>VPNs:</strong> Encrypt traffic over public networks</li>\n<li><strong>Network Monitoring:</strong> Continuous traffic analysis for anomalies</li>\n</ul>\n<h3>Wireshark Basics</h3>\n<p>Wireshark is a free network protocol analyzer used to capture and inspect network traffic. It is essential for network troubleshooting and security analysis.</p>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(50, 24, 'Module 2: Knowledge Check', '<h2>Module 2 Knowledge Check</h2>\n<p>Test your networking knowledge with this practice quiz.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(51, 25, 'Types of Malware', '<h2>Types of Malware</h2>\n<p>Malware (malicious software) is any program designed to harm or exploit systems.</p>\n<h3>Common Malware Types</h3>\n<ul>\n<li><strong>Virus:</strong> Self-replicating code that attaches to legitimate programs</li>\n<li><strong>Worm:</strong> Self-spreading malware that doesn\'t need a host program</li>\n<li><strong>Trojan:</strong> Malware disguised as legitimate software</li>\n<li><strong>Ransomware:</strong> Encrypts files and demands payment for decryption</li>\n<li><strong>Spyware:</strong> Secretly monitors user activity</li>\n<li><strong>Adware:</strong> Displays unwanted advertisements</li>\n<li><strong>Rootkit:</strong> Hides malicious activity deep in the operating system</li>\n<li><strong>Keylogger:</strong> Records keystrokes to steal passwords</li>\n</ul>\n<h3>Famous Malware Examples</h3>\n<ul>\n<li><strong>WannaCry (2017):</strong> Ransomware that affected 200,000+ computers globally</li>\n<li><strong>Stuxnet (2010):</strong> First known cyberweapon targeting industrial systems</li>\n<li><strong>Emotet:</strong> Banking trojan turned malware distribution platform</li>\n</ul>', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(52, 25, 'Social Engineering Attacks', '<h2>Social Engineering Attacks</h2>\n<p>Social engineering exploits human psychology rather than technical vulnerabilities.</p>\n<h3>Common Social Engineering Techniques</h3>\n<ul>\n<li><strong>Phishing:</strong> Fraudulent emails impersonating trusted entities</li>\n<li><strong>Spear Phishing:</strong> Targeted phishing against specific individuals</li>\n<li><strong>Whaling:</strong> Phishing targeting high-level executives</li>\n<li><strong>Pretexting:</strong> Creating a fabricated scenario to gain information</li>\n<li><strong>Baiting:</strong> Leaving infected USB drives in public places</li>\n<li><strong>Quid Pro Quo:</strong> Offering a service in exchange for information</li>\n<li><strong>Tailgating:</strong> Following someone into a restricted area</li>\n</ul>\n<h3>Red Flags of Phishing Emails</h3>\n<ul>\n<li>Urgent or threatening language</li>\n<li>Requests for personal information</li>\n<li>Suspicious sender addresses</li>\n<li>Poor grammar and spelling</li>\n<li>Unexpected attachments</li>\n</ul>', 'Video', 30, 2, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(53, 25, 'Attack Vectors and Exploits', '<h2>Attack Vectors and Exploits</h2>\n<p>Understanding how attackers gain access helps in building effective defenses.</p>\n<h3>Common Attack Vectors</h3>\n<ul>\n<li><strong>Software Vulnerabilities:</strong> Unpatched systems and applications</li>\n<li><strong>Weak Passwords:</strong> Easily guessable or reused credentials</li>\n<li><strong>Insider Threats:</strong> Malicious or negligent employees</li>\n<li><strong>Supply Chain:</strong> Attacking through third-party vendors</li>\n<li><strong>Wireless Networks:</strong> Unsecured Wi-Fi networks</li>\n</ul>\n<h3>The Cyber Kill Chain</h3>\n<ol>\n<li><strong>Reconnaissance:</strong> Gathering information about the target</li>\n<li><strong>Weaponization:</strong> Creating the attack payload</li>\n<li><strong>Delivery:</strong> Transmitting the weapon to the target</li>\n<li><strong>Exploitation:</strong> Triggering the vulnerability</li>\n<li><strong>Installation:</strong> Establishing persistent access</li>\n<li><strong>Command and Control:</strong> Remote control of compromised systems</li>\n<li><strong>Actions on Objectives:</strong> Achieving the attacker\'s goal</li>\n</ol>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(54, 25, 'Module 3: Knowledge Check', '<h2>Module 3 Knowledge Check</h2>\n<p>Test your knowledge of cyber threats and attacks.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(55, 26, 'Firewalls and Network Defense', '<h2>Firewalls and Network Defense</h2>\n<p>Firewalls are the primary defense mechanism for network security, controlling traffic based on security rules.</p>\n<h3>Types of Firewalls</h3>\n<ul>\n<li><strong>Packet-Filtering Firewall:</strong> Inspects packets based on IP/port rules (Layer 3-4)</li>\n<li><strong>Stateful Inspection:</strong> Tracks active connections and makes decisions based on connection state</li>\n<li><strong>Proxy Firewall:</strong> Acts as intermediary between internal and external networks (Layer 7)</li>\n<li><strong>Next-Generation Firewall (NGFW):</strong> Includes IDS/IPS, application awareness, and threat intelligence</li>\n</ul>\n<h3>Firewall Rules Best Practices</h3>\n<ul>\n<li>Default deny - block everything not explicitly allowed</li>\n<li>Principle of least privilege - only allow necessary traffic</li>\n<li>Regular rule review and cleanup</li>\n</ul>\n<h3>IDS vs IPS</h3>\n<ul>\n<li><strong>IDS (Intrusion Detection System):</strong> Monitors and alerts on suspicious activity</li>\n<li><strong>IPS (Intrusion Prevention System):</strong> Actively blocks detected threats in real-time</li>\n</ul>', 'Video', 35, 1, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(56, 26, 'Encryption and Cryptography', '<h2>Encryption and Cryptography</h2>\n<p>Cryptography protects data confidentiality and integrity through mathematical algorithms.</p>\n<h3>Types of Encryption</h3>\n<ul>\n<li><strong>Symmetric Encryption:</strong> Same key for encryption and decryption (AES, DES)</li>\n<li><strong>Asymmetric Encryption:</strong> Public/private key pair (RSA, ECC)</li>\n<li><strong>Hashing:</strong> One-way function producing fixed-size output (SHA-256)</li>\n</ul>\n<h3>Digital Certificates and PKI</h3>\n<ul>\n<li><strong>Certificate Authority (CA):</strong> Issues and validates certificates</li>\n<li><strong>SSL/TLS Certificates:</strong> Enable HTTPS for secure websites</li>\n</ul>\n<h3>Practical Applications</h3>\n<ul>\n<li>HTTPS for secure web browsing</li>\n<li>VPN encryption for remote access</li>\n<li>Full disk encryption (BitLocker, FileVault)</li>\n</ul>', 'Video', 40, 2, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(57, 26, 'Access Control and Authentication', '<h2>Access Control and Authentication</h2>\n<p>Controlling who can access resources is fundamental to security.</p>\n<h3>Authentication Factors</h3>\n<ul>\n<li><strong>Something you know:</strong> Passwords, PINs</li>\n<li><strong>Something you have:</strong> Smart cards, tokens, mobile phones</li>\n<li><strong>Something you are:</strong> Biometrics (fingerprint, face, iris)</li>\n</ul>\n<h3>Multi-Factor Authentication (MFA)</h3>\n<p>MFA requires two or more authentication factors. It significantly reduces account compromise risk.</p>\n<h3>Access Control Models</h3>\n<ul>\n<li><strong>RBAC:</strong> Access based on user roles</li>\n<li><strong>MAC:</strong> System-enforced based on security labels</li>\n<li><strong>DAC:</strong> Resource owner controls access</li>\n</ul>\n<h3>Password Security Best Practices</h3>\n<ul>\n<li>Minimum 12 characters with complexity</li>\n<li>Use password managers</li>\n<li>Unique passwords for each account</li>\n</ul>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(58, 26, 'Module 4: Knowledge Check', '<h2>Module 4 Knowledge Check</h2>\n<p>Test your knowledge of security controls and defense.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(59, 27, 'OWASP Top 10 Vulnerabilities', '<h2>OWASP Top 10 Vulnerabilities</h2>\n<p>The OWASP Top 10 is a standard awareness document representing the most critical security risks to web applications.</p>\n<h3>OWASP Top 10 (2021)</h3>\n<ol>\n<li><strong>A01: Broken Access Control:</strong> Restrictions on authenticated users are not properly enforced</li>\n<li><strong>A02: Cryptographic Failures:</strong> Sensitive data exposed due to weak or missing encryption</li>\n<li><strong>A03: Injection:</strong> Untrusted data sent to interpreters (SQL, NoSQL, OS command)</li>\n<li><strong>A04: Insecure Design:</strong> Fundamental design flaws in the application</li>\n<li><strong>A05: Security Misconfiguration:</strong> Default configurations, incomplete setups</li>\n<li><strong>A06: Vulnerable Components:</strong> Using outdated or vulnerable libraries</li>\n<li><strong>A07: Authentication Failures:</strong> Flaws in authentication mechanisms</li>\n<li><strong>A08: Data Integrity Failures:</strong> Untrusted code or data without verification</li>\n<li><strong>A09: Logging Failures:</strong> Insufficient logging and monitoring</li>\n<li><strong>A10: Server-Side Request Forgery (SSRF):</strong> Server making unauthorized requests</li>\n</ol>', 'Video', 40, 1, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(60, 27, 'SQL Injection and XSS', '<h2>SQL Injection and Cross-Site Scripting (XSS)</h2>\n<p>These are two of the most common and dangerous web application vulnerabilities.</p>\n<h3>SQL Injection (SQLi)</h3>\n<p>SQL Injection occurs when untrusted user input is concatenated into SQL queries. The best defense is using parameterized queries (prepared statements).</p>\n<h3>Cross-Site Scripting (XSS)</h3>\n<p>XSS allows attackers to inject malicious scripts into web pages viewed by other users:</p>\n<ul>\n<li><strong>Stored XSS:</strong> Malicious script stored on the server</li>\n<li><strong>Reflected XSS:</strong> Script in URL parameters reflected in page response</li>\n<li><strong>DOM-based XSS:</strong> Client-side JavaScript manipulates DOM unsafely</li>\n</ul>\n<h4>Prevention</h4>\n<ul>\n<li>Output encoding (HTML, JavaScript, URL)</li>\n<li>Content Security Policy (CSP) headers</li>\n<li>Input validation</li>\n</ul>', 'Video', 40, 2, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(61, 27, 'Secure Coding Practices', '<h2>Secure Coding Practices</h2>\n<p>Writing secure code from the start prevents vulnerabilities before they reach production.</p>\n<h3>Input Validation</h3>\n<ul>\n<li>Validate all input on the server side</li>\n<li>Use whitelist validation (accept known good)</li>\n<li>Validate data type, length, format, and range</li>\n</ul>\n<h3>Security Headers</h3>\n<ul>\n<li><strong>Content-Security-Policy:</strong> Prevents XSS and data injection</li>\n<li><strong>X-Frame-Options:</strong> Prevents clickjacking</li>\n<li><strong>Strict-Transport-Security:</strong> Enforces HTTPS</li>\n</ul>\n<h3>Authentication and Session Management</h3>\n<ul>\n<li>Use strong, proven authentication libraries</li>\n<li>Implement secure session handling (HttpOnly, Secure, SameSite cookies)</li>\n<li>Never expose stack traces or database errors to users</li>\n</ul>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(62, 27, 'Module 5: Knowledge Check', '<h2>Module 5 Knowledge Check</h2>\n<p>Test your web security knowledge.</p>', 'Quiz', 30, 4, NULL, NULL, 0, 1, 25, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(63, 28, 'Introduction to Ethical Hacking', '<h2>Introduction to Ethical Hacking</h2>\n<p>Ethical hacking involves authorized attempts to gain unauthorized access to systems to identify security weaknesses.</p>\n<h3>White Hat vs Black Hat vs Gray Hat</h3>\n<ul>\n<li><strong>White Hat:</strong> Authorized hackers who help organizations improve security</li>\n<li><strong>Black Hat:</strong> Malicious hackers who exploit vulnerabilities for personal gain</li>\n<li><strong>Gray Hat:</strong> Hackers who operate without authorization but without malicious intent</li>\n</ul>\n<h3>Legal and Ethical Considerations</h3>\n<ul>\n<li>Always have written authorization (Rules of Engagement)</li>\n<li>Define scope and boundaries clearly</li>\n<li>Report all findings responsibly</li>\n<li>Do not cause damage or disruption</li>\n</ul>\n<h3>Penetration Testing Types</h3>\n<ul>\n<li><strong>Black Box:</strong> No prior knowledge of the target</li>\n<li><strong>White Box:</strong> Full knowledge of systems and architecture</li>\n<li><strong>Gray Box:</strong> Partial knowledge (most common)</li>\n</ul>', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(64, 28, 'Reconnaissance and Scanning', '<h2>Reconnaissance and Scanning</h2>\n<p>Information gathering is the first and most critical phase of ethical hacking.</p>\n<h3>Passive Reconnaissance</h3>\n<ul>\n<li><strong>OSINT:</strong> Publicly available information</li>\n<li><strong>Google Dorking:</strong> Advanced search techniques</li>\n<li><strong>Whois and DNS:</strong> Domain registration and DNS records</li>\n</ul>\n<h3>Active Reconnaissance</h3>\n<ul>\n<li><strong>Port Scanning:</strong> Identifying open ports and services (Nmap)</li>\n<li><strong>Service Enumeration:</strong> Identifying software versions</li>\n</ul>\n<h3>Nmap Basics</h3>\n<pre class=\"bg-gray-100 p-3 rounded\"><code>nmap -sS target.com        # TCP SYN scan\nnmap -sV target.com        # Service version detection\nnmap -O target.com         # OS detection</code></pre>\n<h3>Vulnerability Scanning</h3>\n<ul>\n<li><strong>Nessus:</strong> Comprehensive vulnerability scanner</li>\n<li><strong>OpenVAS:</strong> Open-source vulnerability scanner</li>\n<li><strong>Nikto:</strong> Web server vulnerability scanner</li>\n</ul>', 'Video', 40, 2, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(65, 28, 'Vulnerability Exploitation Basics', '<h2>Vulnerability Exploitation Basics</h2>\n<p>Understanding exploitation helps defenders understand what they are protecting against.</p>\n<h3>Exploit Databases and Resources</h3>\n<ul>\n<li><strong>Exploit-DB:</strong> Archive of public exploits</li>\n<li><strong>CVE:</strong> Standardized vulnerability identifiers</li>\n</ul>\n<h3>Metasploit Framework</h3>\n<p>Metasploit is the world\'s most used penetration testing framework with exploits, payloads, and auxiliary modules.</p>\n<h3>Responsible Disclosure</h3>\n<p>When vulnerabilities are discovered:</p>\n<ol>\n<li>Notify the organization privately</li>\n<li>Allow reasonable time for remediation</li>\n<li>Coordinate public disclosure</li>\n<li>Never exploit for personal gain</li>\n</ol>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(66, 28, 'Module 6: Knowledge Check', '<h2>Module 6 Knowledge Check</h2>\n<p>Test your ethical hacking knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(67, 29, 'Incident Response Process', '<h2>Incident Response Process</h2>\n<p>An effective incident response plan minimizes damage and recovery time when security breaches occur.</p>\n<h3>NIST Incident Response Lifecycle</h3>\n<ol>\n<li><strong>Preparation:</strong> Establish policies, tools, and trained response team</li>\n<li><strong>Detection and Analysis:</strong> Identify and assess security incidents</li>\n<li><strong>Containment:</strong> Limit the scope and impact of the incident</li>\n<li><strong>Eradication:</strong> Remove threats and vulnerabilities</li>\n<li><strong>Recovery:</strong> Restore systems to normal operation</li>\n<li><strong>Post-Incident Activity:</strong> Learn and improve</li>\n</ol>\n<h3>Incident Classification</h3>\n<ul>\n<li><strong>Severity Levels:</strong> Critical, High, Medium, Low</li>\n<li><strong>Incident Types:</strong> Malware, Unauthorized Access, Data Breach, DDoS</li>\n</ul>\n<h3>First Response Priorities</h3>\n<ul>\n<li>Preserve evidence</li>\n<li>Contain the threat</li>\n<li>Document everything</li>\n<li>Escalate appropriately</li>\n</ul>', 'Video', 35, 1, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(68, 29, 'Digital Forensics Fundamentals', '<h2>Digital Forensics Fundamentals</h2>\n<p>Digital forensics involves collecting, preserving, and analyzing digital evidence.</p>\n<h3>Forensics Principles</h3>\n<ul>\n<li><strong>Evidence Integrity:</strong> Maintain chain of custody</li>\n<li><strong>Documentation:</strong> Record every action taken</li>\n<li><strong>Repeatability:</strong> Results must be reproducible</li>\n</ul>\n<h3>Evidence Collection</h3>\n<ul>\n<li><strong>Live Data:</strong> Running processes, network connections, memory</li>\n<li><strong>Disk Images:</strong> Bit-for-bit copies of storage media</li>\n<li><strong>Log Files:</strong> System, application, and security logs</li>\n</ul>\n<h3>Forensics Tools</h3>\n<ul>\n<li><strong>Autopsy:</strong> Open-source digital forensics platform</li>\n<li><strong>Volatility:</strong> Memory forensics framework</li>\n</ul>', 'Video', 35, 2, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(69, 29, 'Incident Reporting and Documentation', '<h2>Incident Reporting and Documentation</h2>\n<p>Proper documentation is essential for legal, compliance, and improvement purposes.</p>\n<h3>Incident Report Components</h3>\n<ul>\n<li><strong>Executive Summary:</strong> High-level overview for leadership</li>\n<li><strong>Timeline:</strong> Chronological sequence of events</li>\n<li><strong>Technical Details:</strong> Indicators of compromise, affected systems</li>\n<li><strong>Impact Assessment:</strong> Data, financial, and reputational impact</li>\n<li><strong>Root Cause Analysis:</strong> How the incident occurred</li>\n<li><strong>Recommendations:</strong> Preventive measures</li>\n</ul>\n<h3>Regulatory Requirements in Zambia</h3>\n<p>Organizations must be aware of the Data Protection Act requirements for data breach notification.</p>', 'Video', 25, 3, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(70, 29, 'Module 7: Knowledge Check', '<h2>Module 7 Knowledge Check</h2>\n<p>Test your incident response knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(71, 30, 'Introduction to SIEM', '<h2>Introduction to SIEM</h2>\n<p>Security Information and Event Management (SIEM) systems collect and analyze security data from across an organization.</p>\n<h3>What SIEM Does</h3>\n<ul>\n<li><strong>Log Collection:</strong> Aggregates logs from firewalls, servers, applications</li>\n<li><strong>Correlation:</strong> Identifies patterns across multiple data sources</li>\n<li><strong>Alerting:</strong> Generates alerts based on predefined rules</li>\n<li><strong>Dashboards:</strong> Visualizes security posture</li>\n</ul>\n<h3>Popular SIEM Tools</h3>\n<ul>\n<li><strong>Splunk:</strong> Enterprise SIEM with powerful search capabilities</li>\n<li><strong>Microsoft Sentinel:</strong> Cloud-native SIEM and SOAR</li>\n<li><strong>Elastic Stack (ELK):</strong> Open-source log analysis platform</li>\n<li><strong>Wazuh:</strong> Open-source security monitoring</li>\n</ul>', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(72, 30, 'Log Analysis and Monitoring', '<h2>Log Analysis and Monitoring</h2>\n<p>Effective log analysis is crucial for detecting and investigating security incidents.</p>\n<h3>Important Log Sources</h3>\n<ul>\n<li><strong>Operating System Logs:</strong> Windows Event Logs, Linux Syslog</li>\n<li><strong>Firewall Logs:</strong> Connection attempts, blocked traffic</li>\n<li><strong>Web Server Logs:</strong> HTTP requests, errors, access patterns</li>\n<li><strong>Authentication Logs:</strong> Login attempts, privilege changes</li>\n</ul>\n<h3>What to Look For</h3>\n<ul>\n<li>Multiple failed login attempts (brute force)</li>\n<li>Logins outside business hours</li>\n<li>Unusual data transfer volumes</li>\n<li>Known malicious IP addresses</li>\n<li>Missing or modified log files</li>\n</ul>', 'Video', 30, 2, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(73, 30, 'SOC Operations and Workflow', '<h2>SOC Operations and Workflow</h2>\n<p>A Security Operations Center (SOC) is a centralized function that monitors and responds to security incidents.</p>\n<h3>SOC Tiers</h3>\n<ul>\n<li><strong>Tier 1 (Alert Triage):</strong> Initial alert review and prioritization</li>\n<li><strong>Tier 2 (Incident Response):</strong> Investigation and containment</li>\n<li><strong>Tier 3 (Threat Hunting):</strong> Proactive threat identification</li>\n</ul>\n<h3>Key SOC Metrics</h3>\n<ul>\n<li><strong>MTTD (Mean Time to Detect):</strong> Average time to detect threats</li>\n<li><strong>MTTR (Mean Time to Respond):</strong> Average time to respond to incidents</li>\n</ul>\n<h3>MITRE ATT&CK Framework</h3>\n<p>MITRE ATT&CK is a globally accessible knowledge base of adversary tactics and techniques used for threat modeling and detection.</p>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(74, 30, 'Module 8: Knowledge Check', '<h2>Module 8 Knowledge Check</h2>\n<p>Test your SOC knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(75, 31, 'Security Frameworks and Standards', '<h2>Security Frameworks and Standards</h2>\n<p>Security frameworks provide structured approaches to managing cybersecurity risks.</p>\n<h3>NIST Cybersecurity Framework</h3>\n<p>The NIST CSF provides a policy framework of computer security guidance:</p>\n<ul>\n<li><strong>Identify:</strong> Understand and manage cybersecurity risk</li>\n<li><strong>Protect:</strong> Implement safeguards to ensure service delivery</li>\n<li><strong>Detect:</strong> Implement activities to identify events</li>\n<li><strong>Respond:</strong> Take action on detected incidents</li>\n<li><strong>Recover:</strong> Restore capabilities after incidents</li>\n</ul>\n<h3>ISO 27001</h3>\n<p>International standard for information security management systems (ISMS).</p>\n<h3>Other Important Standards</h3>\n<ul>\n<li><strong>CIS Controls:</strong> 20 prioritized security controls</li>\n<li><strong>PCI DSS:</strong> Payment card industry security standard</li>\n</ul>', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(76, 31, 'Risk Management', '<h2>Risk Management</h2>\n<p>Risk management is the process of identifying, assessing, and controlling threats to an organization.</p>\n<h3>Risk Assessment Process</h3>\n<ol>\n<li><strong>Asset Identification:</strong> What needs protection?</li>\n<li><strong>Threat Identification:</strong> What could go wrong?</li>\n<li><strong>Vulnerability Assessment:</strong> What weaknesses exist?</li>\n<li><strong>Risk Calculation:</strong> Risk = Likelihood x Impact</li>\n</ol>\n<h3>Risk Treatment Options</h3>\n<ul>\n<li><strong>Accept:</strong> Acknowledge and bear the risk</li>\n<li><strong>Mitigate:</strong> Reduce likelihood or impact</li>\n<li><strong>Transfer:</strong> Insurance or outsourcing</li>\n<li><strong>Avoid:</strong> Eliminate the risk source</li>\n</ul>', 'Video', 30, 2, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(77, 31, 'Compliance and Audit', '<h2>Compliance and Audit</h2>\n<p>Compliance ensures organizations meet regulatory and industry security requirements.</p>\n<h3>Types of Audits</h3>\n<ul>\n<li><strong>Internal Audit:</strong> Conducted by organization\'s own audit team</li>\n<li><strong>External Audit:</strong> Independent third-party assessment</li>\n<li><strong>Regulatory Audit:</strong> Government-mandated compliance check</li>\n</ul>\n<h3>Zambia Data Protection Act</h3>\n<p>Organizations in Zambia must comply with the Data Protection Act which requires:</p>\n<ul>\n<li>Lawful processing of personal data</li>\n<li>Data subject rights (access, correction, deletion)</li>\n<li>Data breach notification requirements</li>\n</ul>', 'Video', 25, 3, NULL, NULL, 0, 1, 15, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(78, 31, 'Module 9: Knowledge Check', '<h2>Module 9 Knowledge Check</h2>\n<p>Test your GRC knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(79, 32, 'Capstone Project Overview', '<h2>Capstone Project Overview</h2>\n<p>This capstone project allows you to apply all the skills you have learned throughout the course to a real-world scenario.</p>\n<h3>Project Scenario</h3>\n<p>You are hired as a Junior Security Analyst for a small financial services company in Lusaka. The company has 50 employees and processes mobile money transactions. They have recently experienced a phishing attack and want to improve their security posture.</p>\n<h3>Your Tasks</h3>\n<ol>\n<li><strong>Risk Assessment:</strong> Identify and assess key risks to the organization</li>\n<li><strong>Security Policy:</strong> Create an acceptable use policy</li>\n<li><strong>Network Design:</strong> Propose a secure network architecture</li>\n<li><strong>Incident Response Plan:</strong> Develop a basic incident response plan</li>\n<li><strong>Security Awareness:</strong> Create a training outline for employees</li>\n</ol>', 'Assignment', 45, 1, NULL, NULL, 0, 1, 100, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(80, 32, 'Career Preparation and Next Steps', '<h2>Career Preparation and Next Steps</h2>\n<p>Congratulations on completing the Cybersecurity Fundamentals course!</p>\n<h3>What You Have Learned</h3>\n<ul>\n<li>Core cybersecurity principles (CIA Triad, threat landscape)</li>\n<li>Networking fundamentals and security</li>\n<li>Malware, social engineering, and attack vectors</li>\n<li>Security controls (firewalls, encryption, access control)</li>\n<li>Web application security and secure coding</li>\n<li>Ethical hacking basics and methodology</li>\n<li>Incident response and digital forensics</li>\n<li>Security operations and SIEM</li>\n<li>Governance, risk, and compliance</li>\n</ul>\n<h3>Recommended Next Steps</h3>\n<ol>\n<li><strong>Hands-On Practice:</strong> Set up a home lab with virtual machines</li>\n<li><strong>Online Platforms:</strong> TryHackMe, Hack The Box, PortSwigger Web Security Academy</li>\n<li><strong>Certification Path:</strong> Consider CompTIA Security+</li>\n<li><strong>Networking:</strong> Join cybersecurity communities and attend local events</li>\n</ol>', 'Video', 25, 2, NULL, NULL, 0, 1, 10, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(81, 32, 'Final Assessment', '<h2>Final Assessment</h2>\n<p>Comprehensive final examination covering all modules.</p>', 'Quiz', 60, 3, NULL, NULL, 0, 1, 150, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(82, 33, 'What is Cybersecurity?', '<h2>What is Cybersecurity?</h2><p>Cybersecurity is the practice of protecting systems, networks, and programs from digital attacks.</p><h3>The CIA Triad</h3><ul><li><strong>Confidentiality:</strong> Ensuring information is accessible only to authorized users</li><li><strong>Integrity:</strong> Maintaining the accuracy and completeness of data</li><li><strong>Availability:</strong> Ensuring systems and data are accessible when needed</li></ul><h3>Key Terminology</h3><ul><li><strong>Asset:</strong> Anything of value to an organization</li><li><strong>Vulnerability:</strong> A weakness that could be exploited</li><li><strong>Threat:</strong> A potential danger to an asset</li><li><strong>Risk:</strong> The likelihood and impact of a threat exploiting a vulnerability</li></ul>', 'Video', 30, 1, NULL, NULL, 1, 1, 10, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(83, 33, 'The Cyber Threat Landscape', '<h2>The Cyber Threat Landscape</h2><h3>Types of Threat Actors</h3><ul><li><strong>Script Kiddies:</strong> Unskilled attackers using existing tools</li><li><strong>Hacktivists:</strong> Attackers motivated by political or social causes</li><li><strong>Cybercriminals:</strong> Organized groups seeking financial gain</li><li><strong>State-Sponsored Actors:</strong> Nation-state backed attackers</li><li><strong>Insider Threats:</strong> Malicious or negligent employees</li></ul><h3>Statistics in Zambia and Africa</h3><p>Africa is experiencing rapid digital transformation, making cybersecurity increasingly important.</p>', 'Video', 25, 2, NULL, NULL, 0, 1, 10, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(84, 33, 'Cybersecurity Career Paths', '<h2>Cybersecurity Career Paths</h2><h3>Entry-Level Roles</h3><ul><li><strong>Security Analyst:</strong> Monitor systems for threats</li><li><strong>Junior Penetration Tester:</strong> Test systems for vulnerabilities</li><li><strong>SOC Analyst:</strong> Work in Security Operations Centers</li></ul><h3>Certifications</h3><ul><li>CompTIA Security+</li><li>CEH - Certified Ethical Hacker</li><li>CISSP</li></ul>', 'Video', 20, 3, NULL, NULL, 0, 1, 10, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(85, 33, 'Module 1: Knowledge Check', '<h2>Module 1 Knowledge Check</h2><p>Test your understanding of cybersecurity fundamentals.</p>', 'Quiz', 20, 4, NULL, NULL, 0, 1, 25, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(86, 34, 'The OSI Model', '<h2>The OSI Model</h2><table class=\"w-full border-collapse border\"><tr><th class=\"border p-2\">Layer</th><th class=\"border p-2\">Name</th><th class=\"border p-2\">Function</th></tr><tr><td class=\"border p-2\">7</td><td class=\"border p-2\">Application</td><td class=\"border p-2\">HTTP, FTP, SMTP</td></tr><tr><td class=\"border p-2\">6</td><td class=\"border p-2\">Presentation</td><td class=\"border p-2\">Data formatting</td></tr><tr><td class=\"border p-2\">5</td><td class=\"border p-2\">Session</td><td class=\"border p-2\">Session management</td></tr><tr><td class=\"border p-2\">4</td><td class=\"border p-2\">Transport</td><td class=\"border p-2\">TCP, UDP</td></tr><tr><td class=\"border p-2\">3</td><td class=\"border p-2\">Network</td><td class=\"border p-2\">IP, routing</td></tr><tr><td class=\"border p-2\">2</td><td class=\"border p-2\">Data Link</td><td class=\"border p-2\">MAC addresses</td></tr><tr><td class=\"border p-2\">1</td><td class=\"border p-2\">Physical</td><td class=\"border p-2\">Cables, signals</td></tr></table>', 'Video', 35, 1, NULL, NULL, 1, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(87, 34, 'TCP/IP and Network Protocols', '<h2>TCP/IP and Network Protocols</h2><ul><li><strong>HTTP (Port 80):</strong> Unencrypted web traffic</li><li><strong>HTTPS (Port 443):</strong> Encrypted web traffic</li><li><strong>FTP (Port 21):</strong> File transfer - credentials in plaintext</li><li><strong>SSH (Port 22):</strong> Secure remote access</li><li><strong>DNS (Port 53):</strong> Domain resolution</li><li><strong>SMTP (Port 25):</strong> Email sending</li></ul>', 'Video', 30, 2, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(88, 34, 'Network Security Basics', '<h2>Network Security Basics</h2><h3>Common Network Attacks</h3><ul><li><strong>Man-in-the-Middle (MitM):</strong> Intercepting communication</li><li><strong>ARP Spoofing:</strong> Falsifying ARP messages</li><li><strong>DNS Spoofing:</strong> Corrupting DNS cache</li><li><strong>Packet Sniffing:</strong> Capturing network traffic</li></ul><h3>Wireshark Basics</h3><p>Wireshark is a free network protocol analyzer used to capture and inspect network traffic.</p>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(89, 34, 'Module 2: Knowledge Check', '<h2>Module 2 Knowledge Check</h2><p>Test your networking knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(90, 35, 'Types of Malware', '<h2>Types of Malware</h2><ul><li><strong>Virus:</strong> Self-replicating code attaching to programs</li><li><strong>Worm:</strong> Self-spreading, no host needed</li><li><strong>Trojan:</strong> Disguised as legitimate software</li><li><strong>Ransomware:</strong> Encrypts files, demands payment</li><li><strong>Spyware:</strong> Secretly monitors activity</li><li><strong>Keylogger:</strong> Records keystrokes</li></ul><h3>Famous Examples</h3><ul><li><strong>WannaCry (2017):</strong> 200,000+ computers affected</li><li><strong>Stuxnet (2010):</strong> First known cyberweapon</li></ul>', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(91, 35, 'Social Engineering Attacks', '<h2>Social Engineering Attacks</h2><ul><li><strong>Phishing:</strong> Fraudulent emails</li><li><strong>Spear Phishing:</strong> Targeted phishing</li><li><strong>Whaling:</strong> Targeting executives</li><li><strong>Tailgating:</strong> Following someone into secure areas</li></ul><h3>Red Flags</h3><ul><li>Urgent or threatening language</li><li>Requests for personal information</li><li>Suspicious sender addresses</li></ul>', 'Video', 30, 2, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(92, 35, 'Attack Vectors and Exploits', '<h2>Attack Vectors and Exploits</h2><h3>The Cyber Kill Chain</h3><ol><li>Reconnaissance</li><li>Weaponization</li><li>Delivery</li><li>Exploitation</li><li>Installation</li><li>Command and Control</li><li>Actions on Objectives</li></ol>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(93, 35, 'Module 3: Knowledge Check', '<h2>Module 3 Knowledge Check</h2><p>Test your knowledge of cyber threats.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(94, 36, 'Firewalls and Network Defense', '<h2>Firewalls and Network Defense</h2><h3>Types of Firewalls</h3><ul><li><strong>Packet-Filtering:</strong> IP/port rules</li><li><strong>Stateful Inspection:</strong> Tracks connections</li><li><strong>Proxy Firewall:</strong> Intermediary</li><li><strong>NGFW:</strong> Includes IDS/IPS</li></ul><h3>IDS vs IPS</h3><ul><li><strong>IDS:</strong> Detects and alerts</li><li><strong>IPS:</strong> Actively blocks</li></ul>', 'Video', 35, 1, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(95, 36, 'Encryption and Cryptography', '<h2>Encryption and Cryptography</h2><ul><li><strong>Symmetric:</strong> Same key (AES)</li><li><strong>Asymmetric:</strong> Public/private pair (RSA)</li><li><strong>Hashing:</strong> One-way (SHA-256)</li></ul><h3>PKI</h3><ul><li><strong>CA:</strong> Issues certificates</li><li><strong>SSL/TLS:</strong> Enables HTTPS</li></ul>', 'Video', 40, 2, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL);
INSERT INTO `lessons` (`id`, `module_id`, `title`, `content`, `lesson_type`, `duration_minutes`, `display_order`, `video_url`, `video_duration`, `is_preview`, `is_mandatory`, `points`, `created_at`, `updated_at`, `deleted_at`) VALUES
(96, 36, 'Access Control and Authentication', '<h2>Access Control and Authentication</h2><h3>Authentication Factors</h3><ul><li>Something you know (password)</li><li>Something you have (token)</li><li>Something you are (biometric)</li></ul><h3>MFA</h3><p>Multi-Factor Authentication significantly reduces account compromise.</p><h3>RBAC</h3><p>Role-Based Access Control grants permissions based on user roles.</p>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(97, 36, 'Module 4: Knowledge Check', '<h2>Module 4 Knowledge Check</h2><p>Test your knowledge of security controls.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(98, 37, 'OWASP Top 10 Vulnerabilities', '<h2>OWASP Top 10 (2021)</h2><ol><li>Broken Access Control</li><li>Cryptographic Failures</li><li>Injection</li><li>Insecure Design</li><li>Security Misconfiguration</li><li>Vulnerable Components</li><li>Authentication Failures</li><li>Data Integrity Failures</li><li>Logging Failures</li><li>SSRF</li></ol>', 'Video', 40, 1, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(99, 37, 'SQL Injection and XSS', '<h2>SQL Injection and XSS</h2><h3>SQL Injection</h3><p>Occurs when untrusted input is concatenated into SQL queries. Defense: parameterized queries.</p><h3>XSS Types</h3><ul><li><strong>Stored:</strong> Script stored on server</li><li><strong>Reflected:</strong> Script in URL</li><li><strong>DOM-based:</strong> Client-side manipulation</li></ul>', 'Video', 40, 2, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(100, 37, 'Secure Coding Practices', '<h2>Secure Coding Practices</h2><ul><li>Validate all input server-side</li><li>Use whitelist validation</li><li>Output encoding</li><li>Use security headers (CSP, X-Frame-Options)</li><li>Never expose stack traces</li></ul>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(101, 37, 'Module 5: Knowledge Check', '<h2>Module 5 Knowledge Check</h2><p>Test your web security knowledge.</p>', 'Quiz', 30, 4, NULL, NULL, 0, 1, 25, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(102, 38, 'Introduction to Ethical Hacking', '<h2>Introduction to Ethical Hacking</h2><h3>Hat Types</h3><ul><li><strong>White Hat:</strong> Authorized, helps improve security</li><li><strong>Black Hat:</strong> Malicious, illegal</li><li><strong>Gray Hat:</strong> Unauthorized but not malicious</li></ul><h3>Testing Types</h3><ul><li><strong>Black Box:</strong> No prior knowledge</li><li><strong>White Box:</strong> Full knowledge</li><li><strong>Gray Box:</strong> Partial knowledge</li></ul>', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(103, 38, 'Reconnaissance and Scanning', '<h2>Reconnaissance and Scanning</h2><h3>Passive</h3><ul><li>OSINT</li><li>Google Dorking</li><li>Whois/DNS records</li></ul><h3>Active</h3><ul><li>Port scanning (Nmap)</li><li>Service enumeration</li></ul><h3>Nmap</h3><pre>nmap -sS target.com\nnmap -sV target.com</pre>', 'Video', 40, 2, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(104, 38, 'Vulnerability Exploitation Basics', '<h2>Vulnerability Exploitation Basics</h2><h3>Resources</h3><ul><li>Exploit-DB</li><li>CVE</li></ul><h3>Metasploit</h3><p>World\'s most used penetration testing framework.</p><h3>Responsible Disclosure</h3><ol><li>Notify privately</li><li>Allow time to fix</li><li>Coordinate disclosure</li></ol>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(105, 38, 'Module 6: Knowledge Check', '<h2>Module 6 Knowledge Check</h2><p>Test your ethical hacking knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(106, 39, 'Incident Response Process', '<h2>Incident Response Process</h2><h3>NIST Lifecycle</h3><ol><li>Preparation</li><li>Detection and Analysis</li><li>Containment</li><li>Eradication</li><li>Recovery</li><li>Post-Incident Activity</li></ol>', 'Video', 35, 1, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(107, 39, 'Digital Forensics Fundamentals', '<h2>Digital Forensics</h2><h3>Principles</h3><ul><li>Evidence integrity</li><li>Documentation</li><li>Repeatability</li></ul><h3>Tools</h3><ul><li>Autopsy</li><li>Volatility</li></ul>', 'Video', 35, 2, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(108, 39, 'Incident Reporting and Documentation', '<h2>Incident Reporting</h2><h3>Components</h3><ul><li>Executive summary</li><li>Timeline</li><li>Technical details</li><li>Impact assessment</li><li>Root cause analysis</li><li>Recommendations</li></ul><h3>Zambia DPA</h3><p>Data breach notification requirements.</p>', 'Video', 25, 3, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(109, 39, 'Module 7: Knowledge Check', '<h2>Module 7 Knowledge Check</h2><p>Test your incident response knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(110, 40, 'Introduction to SIEM', '<h2>Introduction to SIEM</h2><h3>What SIEM Does</h3><ul><li>Log collection</li><li>Correlation</li><li>Alerting</li><li>Dashboards</li></ul><h3>Tools</h3><ul><li>Splunk</li><li>Microsoft Sentinel</li><li>Elastic Stack (ELK)</li><li>Wazuh</li></ul>', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(111, 40, 'Log Analysis and Monitoring', '<h2>Log Analysis</h2><h3>Log Sources</h3><ul><li>OS logs</li><li>Firewall logs</li><li>Web server logs</li><li>Authentication logs</li></ul><h3>What to Look For</h3><ul><li>Multiple failed logins</li><li>Off-hours logins</li><li>Unusual data transfers</li><li>Known malicious IPs</li></ul>', 'Video', 30, 2, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(112, 40, 'SOC Operations and Workflow', '<h2>SOC Operations</h2><h3>Tiers</h3><ul><li><strong>Tier 1:</strong> Alert triage</li><li><strong>Tier 2:</strong> Incident response</li><li><strong>Tier 3:</strong> Threat hunting</li></ul><h3>Metrics</h3><ul><li>MTTD - Mean Time to Detect</li><li>MTTR - Mean Time to Respond</li></ul><h3>MITRE ATT&CK</h3><p>Knowledge base of adversary tactics and techniques.</p>', 'Video', 35, 3, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(113, 40, 'Module 8: Knowledge Check', '<h2>Module 8 Knowledge Check</h2><p>Test your SOC knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(114, 41, 'Security Frameworks and Standards', '<h2>Security Frameworks</h2><h3>NIST CSF</h3><ul><li>Identify</li><li>Protect</li><li>Detect</li><li>Respond</li><li>Recover</li></ul><h3>ISO 27001</h3><p>Information Security Management Systems.</p><h3>CIS Controls</h3><p>20 prioritized security controls.</p>', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(115, 41, 'Risk Management', '<h2>Risk Management</h2><h3>Assessment</h3><ol><li>Asset identification</li><li>Threat identification</li><li>Vulnerability assessment</li><li>Risk calculation: Likelihood x Impact</li></ol><h3>Treatment</h3><ul><li>Accept</li><li>Mitigate</li><li>Transfer</li><li>Avoid</li></ul>', 'Video', 30, 2, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(116, 41, 'Compliance and Audit', '<h2>Compliance and Audit</h2><h3>Audit Types</h3><ul><li>Internal</li><li>External</li><li>Regulatory</li></ul><h3>Zambia DPA</h3><ul><li>Lawful processing</li><li>Data subject rights</li><li>Breach notification</li></ul>', 'Video', 25, 3, NULL, NULL, 0, 1, 15, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(117, 41, 'Module 9: Knowledge Check', '<h2>Module 9 Knowledge Check</h2><p>Test your GRC knowledge.</p>', 'Quiz', 25, 4, NULL, NULL, 0, 1, 25, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(118, 42, 'Capstone Project Overview', '<h2>Capstone Project</h2><p>You are hired as a Junior Security Analyst for a company in Lusaka. They experienced a phishing attack and want to improve security.</p><h3>Tasks</h3><ol><li>Risk Assessment</li><li>Security Policy</li><li>Network Design</li><li>Incident Response Plan</li><li>Security Awareness Training</li></ol>', 'Assignment', 45, 1, NULL, NULL, 0, 1, 100, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(119, 42, 'Career Preparation and Next Steps', '<h2>Career Preparation</h2><h3>Next Steps</h3><ol><li>Set up a home lab</li><li>TryHackMe, Hack The Box</li><li>CompTIA Security+</li><li>Join cybersecurity communities</li></ol>', 'Video', 25, 2, NULL, NULL, 0, 1, 10, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(120, 42, 'Final Assessment', '<h2>Final Assessment</h2><p>Comprehensive final examination covering all modules.</p>', 'Quiz', 60, 3, NULL, NULL, 0, 1, 150, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_notes`
--

CREATE TABLE `lesson_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `content` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_notes`
--

INSERT INTO `lesson_notes` (`id`, `user_id`, `lesson_id`, `course_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 88, 28, 1, 'Remember: Ctrl+B for bold, Ctrl+I for italic, Ctrl+U for underline.', '2026-05-23 11:53:46', '2026-05-23 11:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `lesson_id` int(11) NOT NULL,
  `status` enum('Not Started','In Progress','Completed') DEFAULT 'Not Started',
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `time_spent_minutes` int(11) DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_accessed` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_progress`
--

INSERT INTO `lesson_progress` (`id`, `enrollment_id`, `user_id`, `course_id`, `lesson_id`, `status`, `progress_percentage`, `time_spent_minutes`, `started_at`, `completed_at`, `last_accessed`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 1, 'Completed', 100.00, 15, '2025-01-15 07:00:00', '2025-01-15 07:15:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 1, NULL, NULL, 2, 'Completed', 100.00, 30, '2025-01-15 07:20:00', '2025-01-15 07:50:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 1, NULL, NULL, 3, 'Completed', 100.00, 20, '2025-01-15 08:00:00', '2025-01-15 08:20:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 1, NULL, NULL, 4, 'Completed', 100.00, 25, '2025-01-15 08:30:00', '2025-01-15 08:55:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 4, NULL, NULL, 13, 'Completed', 100.00, 35, '2025-01-15 12:00:00', '2025-01-15 12:35:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 4, NULL, NULL, 14, 'Completed', 100.00, 40, '2025-01-15 13:00:00', '2025-01-15 13:40:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 10, NULL, NULL, 1, 'Completed', 100.00, 15, '2025-01-10 06:00:00', '2025-01-10 06:15:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(8, 10, NULL, NULL, 2, 'Completed', 100.00, 32, '2025-01-10 06:30:00', '2025-01-10 07:02:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(9, 10, NULL, NULL, 3, 'Completed', 100.00, 22, '2025-01-10 07:15:00', '2025-01-10 07:37:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(22, 39, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(23, 39, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(24, 39, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(25, 39, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(26, 39, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(27, 39, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(28, 39, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(29, 39, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(30, 39, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(31, 39, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(32, 39, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(33, 39, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(34, 39, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(35, 39, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(36, 39, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(37, 40, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(38, 40, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(39, 40, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(40, 40, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(41, 40, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(42, 40, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(43, 40, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(44, 40, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(45, 40, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(46, 40, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(47, 40, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(48, 40, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(49, 40, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(50, 40, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(51, 40, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(52, 41, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(53, 41, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(54, 41, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(55, 41, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(56, 41, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(57, 41, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(58, 41, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(59, 41, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(60, 41, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(61, 41, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(62, 41, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(63, 41, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(64, 41, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(65, 41, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(66, 41, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(67, 42, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(68, 42, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(69, 42, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(70, 42, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(71, 42, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(72, 42, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(73, 42, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(74, 42, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(75, 42, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(76, 42, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(77, 42, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(78, 42, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(79, 42, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(80, 42, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(81, 42, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(82, 43, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(83, 43, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(84, 43, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(85, 43, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43', '2026-05-08 15:03:43'),
(86, 43, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(87, 43, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(88, 43, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(89, 43, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(90, 43, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(91, 43, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(92, 43, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(93, 43, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(94, 43, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(95, 43, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(96, 43, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(97, 44, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(98, 44, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(99, 44, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(100, 44, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(101, 44, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(102, 44, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(103, 44, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(104, 44, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(105, 44, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(106, 44, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(107, 44, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(108, 44, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(109, 44, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(110, 44, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(111, 44, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(112, 45, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(113, 45, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(114, 45, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(115, 45, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(116, 45, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(117, 45, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(118, 45, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(119, 45, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(120, 45, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(121, 45, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(122, 45, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(123, 45, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(124, 45, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(125, 45, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(126, 45, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(127, 46, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(128, 46, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(129, 46, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(130, 46, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(131, 46, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(132, 46, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(133, 46, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(134, 46, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(135, 46, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(136, 46, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(137, 46, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(138, 46, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(139, 46, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(140, 46, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(141, 46, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(142, 47, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(143, 47, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(144, 47, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(145, 47, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(146, 47, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(147, 47, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(148, 47, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(149, 47, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(150, 47, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(151, 47, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(152, 47, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(153, 47, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(154, 47, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(155, 47, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(156, 47, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(157, 48, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(158, 48, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(159, 48, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(160, 48, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(161, 48, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(162, 48, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(163, 48, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(164, 48, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(165, 48, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(166, 48, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(167, 48, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(168, 48, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(169, 48, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(170, 48, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(171, 48, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(172, 49, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(173, 49, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(174, 49, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(175, 49, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(176, 49, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(177, 49, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(178, 49, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(179, 49, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(180, 49, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44', '2026-05-08 15:03:44'),
(181, 49, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45'),
(182, 49, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45'),
(183, 49, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45'),
(184, 49, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45'),
(185, 49, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45'),
(186, 49, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45', '2026-05-08 15:03:45'),
(187, 50, NULL, NULL, 28, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-20 20:59:40', '2026-05-08 15:20:22', '2026-05-20 20:59:40'),
(188, 50, NULL, NULL, 29, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(189, 50, NULL, NULL, 30, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(190, 50, NULL, NULL, 31, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(191, 50, NULL, NULL, 32, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(192, 50, NULL, NULL, 33, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(193, 50, NULL, NULL, 34, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(194, 50, NULL, NULL, 35, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(195, 50, NULL, NULL, 36, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(196, 50, NULL, NULL, 37, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(197, 50, NULL, NULL, 38, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(198, 50, NULL, NULL, 39, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(199, 50, NULL, NULL, 40, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(200, 50, NULL, NULL, 41, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(201, 50, NULL, NULL, 42, 'Completed', 100.00, 15, '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22', '2026-05-08 15:20:22'),
(202, 50, 88, 1, 28, 'Completed', 100.00, 39, '2026-05-03 07:56:21', '2026-05-04 07:56:21', '2026-05-23 09:03:41', '2026-05-03 07:56:21', '2026-05-23 09:03:41'),
(203, 50, 88, 1, 29, 'Completed', 100.00, 56, '2026-05-04 07:56:21', '2026-05-05 07:56:21', '2026-05-13 07:56:21', '2026-05-04 07:56:21', '2026-05-13 07:56:21'),
(204, 50, 88, 1, 30, 'Completed', 100.00, 39, '2026-05-05 07:56:21', '2026-05-06 07:56:21', '2026-05-13 07:56:21', '2026-05-05 07:56:21', '2026-05-13 07:56:21'),
(205, 50, 88, 1, 31, 'Completed', 100.00, 49, '2026-05-06 07:56:21', '2026-05-07 07:56:21', '2026-05-13 07:56:21', '2026-05-06 07:56:21', '2026-05-13 07:56:21'),
(206, 50, 88, 1, 32, 'Completed', 100.00, 45, '2026-05-07 07:56:21', '2026-05-08 07:56:21', '2026-05-13 07:56:21', '2026-05-07 07:56:21', '2026-05-13 07:56:21'),
(207, 50, 88, 1, 33, 'Completed', 100.00, 29, '2026-05-08 07:56:21', '2026-05-09 07:56:21', '2026-05-13 07:56:21', '2026-05-08 07:56:21', '2026-05-13 07:56:21'),
(208, 50, 88, 1, 34, 'Completed', 100.00, 22, '2026-05-09 07:56:21', '2026-05-10 07:56:21', '2026-05-13 07:56:21', '2026-05-09 07:56:21', '2026-05-13 07:56:21'),
(209, 50, 88, 1, 35, 'Completed', 100.00, 21, '2026-05-10 07:56:21', '2026-05-11 07:56:21', '2026-05-13 07:56:21', '2026-05-10 07:56:21', '2026-05-13 07:56:21'),
(210, 50, 88, 1, 36, 'Completed', 100.00, 17, '2026-05-11 07:56:21', '2026-05-12 07:56:21', '2026-05-13 07:56:21', '2026-05-11 07:56:21', '2026-05-13 07:56:21'),
(211, 50, 88, 1, 37, 'Completed', 100.00, 41, '2026-05-12 07:56:21', '2026-05-13 07:56:21', '2026-05-13 07:56:21', '2026-05-12 07:56:21', '2026-05-13 07:56:21'),
(212, 50, 88, 1, 38, 'Completed', 100.00, 31, '2026-05-13 07:56:21', '2026-05-14 07:56:21', '2026-05-13 07:56:21', '2026-05-13 07:56:21', '2026-05-13 07:56:21'),
(213, 50, 88, 1, 39, 'Completed', 100.00, 36, '2026-05-14 07:56:21', '2026-05-15 07:56:21', '2026-05-13 07:56:21', '2026-05-14 07:56:21', '2026-05-13 07:56:21'),
(214, 50, 88, 1, 40, 'Completed', 100.00, 47, '2026-05-15 07:56:21', '2026-05-16 07:56:21', '2026-05-13 07:56:21', '2026-05-15 07:56:21', '2026-05-13 07:56:21'),
(215, 50, 88, 1, 41, 'Completed', 100.00, 35, '2026-05-16 07:56:21', '2026-05-17 07:56:21', '2026-05-13 07:56:21', '2026-05-16 07:56:21', '2026-05-13 07:56:21'),
(216, 50, 88, 1, 42, 'Completed', 100.00, 43, '2026-05-17 07:56:21', '2026-05-18 07:56:21', '2026-05-13 07:56:21', '2026-05-17 07:56:21', '2026-05-13 07:56:21'),
(217, 52, 88, 5, 1, 'Completed', 100.00, 72, '2026-05-09 07:56:21', '2026-05-10 07:56:21', '2026-05-23 10:47:36', '2026-05-09 07:56:21', '2026-05-23 10:47:36'),
(218, 52, 88, 5, 2, 'Completed', 100.00, 55, '2026-05-10 07:56:21', '2026-05-11 07:56:21', '2026-05-21 07:56:21', '2026-05-10 07:56:21', '2026-05-21 07:56:21'),
(219, 52, 88, 5, 3, 'Completed', 100.00, 70, '2026-05-11 07:56:21', '2026-05-12 07:56:21', '2026-05-21 07:56:21', '2026-05-11 07:56:21', '2026-05-21 07:56:21'),
(220, 52, 88, 5, 4, 'Completed', 100.00, 58, '2026-05-12 07:56:21', '2026-05-13 07:56:21', '2026-05-21 07:56:21', '2026-05-12 07:56:21', '2026-05-21 07:56:21'),
(221, 52, 88, 5, 5, 'Completed', 100.00, 72, '2026-05-13 07:56:21', '2026-05-14 07:56:21', '2026-05-21 07:56:21', '2026-05-13 07:56:21', '2026-05-21 07:56:21'),
(222, 52, 88, 5, 6, 'Completed', 100.00, 69, '2026-05-14 07:56:21', '2026-05-15 07:56:21', '2026-05-21 07:56:21', '2026-05-14 07:56:21', '2026-05-21 07:56:21'),
(223, 52, 88, 5, 7, 'Completed', 100.00, 39, '2026-05-15 07:56:21', '2026-05-16 07:56:21', '2026-05-21 07:56:21', '2026-05-15 07:56:21', '2026-05-21 07:56:21'),
(224, 52, 88, 5, 8, 'In Progress', 45.00, 25, '2026-05-22 07:56:21', NULL, '2026-05-23 05:56:21', '2026-05-22 07:56:21', '2026-05-23 05:56:21');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_resources`
--

CREATE TABLE `lesson_resources` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `resource_type` enum('PDF','Document','Spreadsheet','Presentation','Video','Audio','Archive','Other') NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `file_size_kb` int(11) DEFAULT NULL,
  `download_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_resources`
--

INSERT INTO `lesson_resources` (`id`, `lesson_id`, `title`, `description`, `resource_type`, `file_url`, `file_size_kb`, `download_count`, `created_at`) VALUES
(1, 21, 'Course Syllabus (PDF)', 'Complete course outline including modules, lessons, and assessment schedule', 'PDF', 'https://example.com/materials/office-suite-syllabus.pdf', 250, 0, '2025-12-25 19:40:26'),
(2, 21, 'Office Suite Overview Guide', 'Comprehensive PDF guide introducing all Office applications with screenshots', 'PDF', 'https://example.com/materials/office-overview-guide.pdf', 1500, 0, '2025-12-25 19:40:26'),
(3, 21, 'Getting Started Checklist', 'Step-by-step checklist to prepare for the course', 'PDF', 'https://example.com/materials/getting-started-checklist.pdf', 150, 0, '2025-12-25 19:40:26'),
(4, 22, 'Office Interface Guide (PDF)', 'Visual guide with labeled screenshots of the Office interface', 'PDF', 'https://example.com/materials/interface-guide.pdf', 800, 0, '2025-12-25 19:40:26'),
(5, 22, 'Keyboard Shortcuts Reference', 'Complete list of essential Office keyboard shortcuts', 'PDF', 'https://example.com/materials/office-shortcuts.pdf', 200, 0, '2025-12-25 19:40:26'),
(6, 22, 'Interface Practice Workbook', 'Interactive exercise to identify and practice using interface elements', 'Document', 'https://example.com/materials/interface-practice.docx', 150, 0, '2025-12-25 19:40:26'),
(7, 23, 'File Management Guide (PDF)', 'Best practices for naming, organizing, and backing up Office files', 'PDF', 'https://example.com/materials/file-management-guide.pdf', 500, 0, '2025-12-25 19:40:26'),
(8, 23, 'Practice Files Package', 'Sample files for practicing file operations', 'Archive', 'https://example.com/materials/practice-files.zip', 2500, 0, '2025-12-25 19:40:26'),
(9, 23, 'Folder Structure Template', 'Recommended folder organization system for Office documents', 'Document', 'https://example.com/materials/folder-template.docx', 100, 0, '2025-12-25 19:40:26'),
(10, 24, 'Word Basics Guide (PDF)', 'Complete beginner\'s guide to Microsoft Word with screenshots', 'PDF', 'https://example.com/materials/word-basics-guide.pdf', 2000, 0, '2025-12-25 19:40:26'),
(11, 24, 'Practice Document Template', 'Starter template for your first Word document', 'Document', 'https://example.com/materials/first-document-template.docx', 50, 0, '2025-12-25 19:40:26'),
(12, 24, 'Word Exercise - My First Document', 'Guided exercise with step-by-step instructions', 'Document', 'https://example.com/materials/word-exercise-1.docx', 120, 0, '2025-12-25 19:40:26'),
(13, 24, 'Exercise Answer Key', 'Completed example of the practice exercise', 'Document', 'https://example.com/materials/word-exercise-1-answers.docx', 150, 0, '2025-12-25 19:40:26'),
(14, 25, 'Formatting Reference Guide (PDF)', 'Quick reference for all Word formatting options', 'PDF', 'https://example.com/materials/word-formatting-guide.pdf', 1200, 0, '2025-12-25 19:40:26'),
(15, 25, 'Unformatted Practice Document', 'Document to practice formatting techniques', 'Document', 'https://example.com/materials/format-practice.docx', 80, 0, '2025-12-25 19:40:26'),
(16, 25, 'Formatted Example', 'Professionally formatted version showing best practices', 'Document', 'https://example.com/materials/format-example.docx', 120, 0, '2025-12-25 19:40:26'),
(17, 25, 'Professional Document Templates', 'Collection of formatted document templates', 'Archive', 'https://example.com/materials/word-templates.zip', 800, 0, '2025-12-25 19:40:26'),
(18, 26, 'Excel Interface Guide (PDF)', 'Visual guide to Excel interface with labeled components', 'PDF', 'https://example.com/materials/excel-interface-guide.pdf', 1000, 0, '2025-12-25 19:40:26'),
(19, 26, 'Practice Workbook - Getting Started', 'Guided exercises for Excel beginners', 'Spreadsheet', 'https://example.com/materials/excel-practice-1.xlsx', 150, 0, '2025-12-25 19:40:26'),
(20, 26, 'Sample Data Files', 'Various data sets for practice exercises', 'Archive', 'https://example.com/materials/excel-sample-data.zip', 500, 0, '2025-12-25 19:40:26'),
(21, 26, 'Excel Quick Reference (PDF)', 'One-page cheat sheet for Excel basics', 'PDF', 'https://example.com/materials/excel-quick-reference.pdf', 200, 0, '2025-12-25 19:40:26'),
(22, 27, 'Formula Reference Guide (PDF)', 'Complete guide to basic Excel formulas with examples', 'PDF', 'https://example.com/materials/excel-formulas-guide.pdf', 1500, 0, '2025-12-25 19:40:26'),
(23, 27, 'Formula Practice Workbook', 'Exercises covering all basic formulas and functions', 'Spreadsheet', 'https://example.com/materials/formula-practice.xlsx', 200, 0, '2025-12-25 19:40:26'),
(24, 27, 'Budget Template with Formulas', 'Real-world example: Monthly budget spreadsheet', 'Spreadsheet', 'https://example.com/materials/budget-template.xlsx', 120, 0, '2025-12-25 19:40:26'),
(25, 27, 'Grade Book Example', 'Grade calculation spreadsheet demonstrating formulas', 'Spreadsheet', 'https://example.com/materials/gradebook-example.xlsx', 150, 0, '2025-12-25 19:40:26'),
(26, 27, 'Practice Exercise Solutions', 'Answer key for all formula exercises', 'Spreadsheet', 'https://example.com/materials/formula-answers.xlsx', 250, 0, '2025-12-25 19:40:26'),
(27, 28, 'Complete Word Mastery Guide (PDF)', 'Full module notes covering interface, formatting, layout, and tables.', 'PDF', 'https://drive.google.com/file/d/18NQIttk_FSbTIt2cmzTG5mkUus0gK-4D/view?usp=drive_link', NULL, 0, '2026-01-10 14:40:43'),
(28, 33, 'Complete Excel Mastery Guide (PDF)', 'Full module notes covering basics, formulas, functions, and charts.', 'PDF', 'https://drive.google.com/file/d/1J5FyYwxVuhyc7w3VCMOWsMXzqvgh6BwK/view?usp=sharing', NULL, 0, '2026-01-10 14:40:43'),
(29, 38, 'Presentation & Design Guide (PDF)', 'Full module notes covering PowerPoint slides and Publisher designs.', 'PDF', 'https://drive.google.com/file/d/1CSlWRdUVH1IDw8XsZlSUVtEknWo6RWL3/view?usp=sharing', NULL, 0, '2026-01-10 14:40:43');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_versions`
--

CREATE TABLE `lesson_versions` (
  `id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `content` longtext DEFAULT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `change_summary` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `live_sessions`
--

CREATE TABLE `live_sessions` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `intake_id` int(11) DEFAULT NULL,
  `instructor_id` int(11) NOT NULL,
  `meeting_room_id` varchar(255) NOT NULL,
  `scheduled_start_time` datetime NOT NULL,
  `scheduled_end_time` datetime NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 60,
  `status` enum('scheduled','live','ended','cancelled') NOT NULL DEFAULT 'scheduled',
  `max_participants` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `recording_url` varchar(500) DEFAULT NULL,
  `moderator_password` varchar(100) DEFAULT NULL,
  `participant_password` varchar(100) DEFAULT NULL,
  `allow_recording` tinyint(1) NOT NULL DEFAULT 1,
  `auto_start_recording` tinyint(1) NOT NULL DEFAULT 0,
  `enable_chat` tinyint(1) NOT NULL DEFAULT 1,
  `enable_screen_share` tinyint(1) NOT NULL DEFAULT 1,
  `buffer_minutes_before` int(11) NOT NULL DEFAULT 15,
  `buffer_minutes_after` int(11) NOT NULL DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `live_sessions`
--

INSERT INTO `live_sessions` (`id`, `lesson_id`, `intake_id`, `instructor_id`, `meeting_room_id`, `scheduled_start_time`, `scheduled_end_time`, `duration_minutes`, `status`, `max_participants`, `description`, `recording_url`, `moderator_password`, `participant_password`, `allow_recording`, `auto_start_recording`, `enable_chat`, `enable_screen_share`, `buffer_minutes_before`, `buffer_minutes_after`, `created_at`, `updated_at`) VALUES
(1, 28, 2, 13, 'edutrack-word-intro', '2026-05-25 13:44:37', '2026-05-25 14:44:37', 60, 'scheduled', 30, 'Live walkthrough of Word interface and basic formatting', NULL, NULL, NULL, 1, 0, 1, 1, 15, 30, '2026-05-23 11:44:37', '2026-06-02 11:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `live_session_attendance`
--

CREATE TABLE `live_session_attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `live_session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` datetime NOT NULL,
  `left_at` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT 0,
  `is_moderator` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `parent_message_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `recipient_id`, `subject`, `content`, `is_read`, `read_at`, `parent_message_id`, `created_at`) VALUES
(1, 8, 3, 'Question about Quiz 1', 'Hi Sarah, I have a question about question 5 on the Python basics quiz. Could you clarify what is being asked?', 1, '2025-01-26 07:30:00', NULL, '2025-11-18 22:21:01'),
(2, 3, 8, 'Re: Question about Quiz 1', 'Hi John, question 5 is asking about mutable vs immutable data types. Review the lesson on data types for more details.', 1, '2025-01-26 12:00:00', NULL, '2025-11-18 22:21:01'),
(3, 9, 2, 'Thank you!', 'Thank you for the excellent feedback on my portfolio project!', 1, '2025-03-22 08:15:00', NULL, '2025-11-18 22:21:01'),
(4, 10, 4, 'Study group for final exam', 'Hey David, are you interested in forming a study group for the entrepreneurship final exam?', 0, NULL, NULL, '2025-11-18 22:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2024_01_01_000001_create_users_table', 1),
(2, '2024_01_01_000002_create_course_categories_table', 1),
(3, '2024_01_01_000003_create_instructors_table', 1),
(4, '2024_01_01_000004_create_courses_table', 1),
(5, '2024_01_01_000005_create_modules_table', 1),
(6, '2024_01_01_000006_create_lessons_table', 1),
(7, '2024_01_01_000007_create_enrollments_table', 1),
(8, '2024_01_01_000008_create_certificates_table', 1),
(9, '2024_01_01_000009_create_payments_table', 1),
(10, '2024_01_01_000010_create_quizzes_table', 1),
(11, '2024_01_01_000011_create_assignments_table', 1),
(12, '2024_01_01_000012_create_user_roles_table', 1),
(13, '2024_01_01_000013_create_user_profiles_table', 1),
(14, '2024_01_01_000014_create_questions_table', 1),
(15, '2024_01_01_000015_create_quiz_questions_table', 1),
(16, '2024_01_01_000016_create_question_options_table', 1),
(17, '2024_01_01_000017_create_quiz_attempts_table', 1),
(18, '2024_01_01_000018_create_quiz_answers_table', 1),
(19, '2024_01_01_000019_create_assignment_submissions_table', 1),
(20, '2024_01_01_000020_create_announcements_table', 1),
(21, '2024_01_01_000021_create_activity_logs_table', 1),
(22, '2024_01_01_000022_create_email_queue_table', 1),
(23, '2024_01_01_000023_create_payment_methods_table', 1),
(24, '2024_01_01_000024_create_live_sessions_table', 1),
(25, '2024_01_01_000025_create_live_session_attendance_table', 1),
(26, '2024_01_01_000026_create_lesson_progress_table', 1),
(27, '2024_01_01_000027_create_course_reviews_table', 1),
(28, '2024_01_01_000028_create_enrollment_payment_plans_table', 1),
(29, '2024_01_01_000029_create_notifications_table', 1),
(30, '2024_01_01_000030_create_lenco_transactions_table', 1),
(31, '2024_01_01_000031_create_lenco_webhook_logs_table', 1),
(32, '2024_01_01_000032_create_system_settings_table', 1),
(33, '2024_01_01_000033_create_students_table', 1),
(34, '2024_01_01_000034_create_roles_table', 1),
(35, '2024_01_01_000035_create_remember_tokens_table', 1),
(36, '2024_01_01_000036_create_registration_fees_table', 1),
(37, '2024_01_01_000037_create_badges_table', 1),
(38, '2024_01_01_000038_create_contacts_table', 1),
(39, '2024_01_01_000039_create_course_instructors_table', 1),
(40, '2024_01_01_000040_create_discussions_table', 1),
(41, '2024_01_01_000041_create_discussion_replies_table', 1),
(42, '2024_01_01_000042_create_email_templates_table', 1),
(43, '2024_01_01_000043_create_lesson_resources_table', 1),
(44, '2024_01_01_000044_create_messages_table', 1),
(45, '2024_01_01_000045_create_quiz_question_options_table', 1),
(46, '2024_01_01_000046_create_student_achievements_table', 1),
(47, '2024_01_01_000047_create_team_members_table', 1),
(48, '2024_01_01_000048_create_transactions_table', 1),
(49, '2024_01_01_000049_create_user_sessions_table', 1),
(50, '2024_01_02_000001_create_testimonials_table', 1),
(51, '2024_01_02_000002_create_events_table', 1),
(52, '2024_01_02_000003_create_hero_slides_table', 1),
(53, '2026_05_09_234622_create_institution_photos_table', 1),
(54, '2026_05_10_083128_add_classification_to_certificates_table', 1),
(57, '2019_12_14_000001_create_personal_access_tokens_table', 2),
(58, '2026_05_20_215557_add_remember_token_to_users_table', 3),
(59, '2026_05_11_121305_create_lesson_notes_table', 4),
(60, '2026_05_11_140140_create_newsletter_subscribers_table', 5),
(61, '2026_05_22_165043_create_invoices_table', 6),
(62, '2026_05_22_171522_create_settings_table', 7),
(63, '2026_05_22_204058_add_unique_index_to_system_settings_setting_key', 8),
(65, '2026_05_23_101104_fix_user_payments_relationship', 9),
(66, '2026_05_23_130918_create_lesson_versions_table', 10),
(67, '2026_05_23_182506_add_correct_answer_to_questions_table', 11),
(68, '2026_05_23_213718_add_soft_deletes_to_core_tables', 12),
(69, '2026_05_24_211541_add_user_and_course_id_to_testimonials_table', 13),
(71, '2026_05_24_220918_create_promotions_table', 14),
(72, '2026_05_24_221914_add_promotion_fields_to_payments_table', 15),
(73, '2026_05_25_113442_add_is_active_to_institution_photos', 16),
(74, '2026_05_25_125500_add_job_columns_to_testimonials', 17),
(75, '2026_05_25_130000_fix_testimonials_job_title', 18),
(76, '2026_05_25_130100_add_missing_user_profile_columns', 18),
(77, '2026_05_25_130200_fix_events_columns', 18),
(78, '2026_05_25_131500_fix_lenco_columns', 19),
(79, '2026_05_25_132000_add_mobile_fields_to_registration_fees', 20),
(80, '2026_05_25_133000_add_lenco_tx_to_registration_fees', 21),
(81, '2026_05_25_154010_make_payments_course_id_nullable', 22),
(82, '2026_05_27_000000_fix_lenco_transactions_columns', 23),
(83, '2026_05_27_121134_add_payment_method_and_phone_to_lenco_transactions', 23),
(84, '2026_05_27_143702_fix_course_status_enum_under_review', 23),
(85, '2026_05_27_151911_add_points_override_to_quiz_questions', 23),
(86, '2026_05_27_151912_add_soft_deletes_to_questions', 23),
(87, '2026_05_27_151913_add_soft_deletes_to_assignment_submissions', 23),
(88, '2026_05_27_153202_fix_quiz_attempts_student_id_fk', 23),
(89, '2026_05_27_153203_fix_assignment_submissions_student_id_fk', 23),
(90, '2026_05_27_153204_fix_notifications_column_names', 23),
(91, '2026_05_27_153205_fix_email_template_enum_values', 23),
(92, '2026_05_27_153206_add_unsubscribed_at_to_newsletter_subscribers', 23),
(93, '2026_05_27_171904_fix_live_session_attendance_id_autoincrement', 23),
(95, '2026_05_28_213615_add_certificates_indexes', 24),
(97, '2026_05_31_010224_add_is_template_to_courses', 25),
(101, '2026_06_01_100000_create_intakes_table', 26),
(102, '2026_06_01_100001_add_intake_id_to_enrollments', 26),
(103, '2026_06_01_100002_add_intake_id_to_live_sessions', 26),
(104, '2026_06_01_100003_add_intake_name_to_certificates', 26),
(105, '2026_06_01_100004_seed_default_intakes', 26),
(106, '2025_06_04_000001_add_missing_indexes', 27),
(107, '2026_06_08_161500_add_nrc_number_to_user_profiles_table', 28);

-- --------------------------------------------------------

--
-- Table structure for table `migrations_log`
--

CREATE TABLE `migrations_log` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `executed_at` timestamp NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 1,
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations_log`
--

INSERT INTO `migrations_log` (`id`, `filename`, `executed_at`, `success`, `error_message`) VALUES
(1, '002_comprehensive_fixes.sql', '2026-04-18 19:24:29', 0, 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'registration_fee_amount\' for key \'idx_setting_key_unique\''),
(2, '003_add_google_id_to_users.sql', '2026-04-18 18:29:49', 1, ''),
(3, '004_add_unique_constraints.sql', '2026-04-18 19:24:29', 0, 'SQLSTATE[42000]: Syntax error or access violation: 1072 Key column \'user_id\' doesn\'t exist in table'),
(4, 'add_course_career_fields.sql', '2026-04-18 18:29:49', 1, ''),
(5, 'add_lenco_collections_table.sql', '2026-04-18 18:29:49', 1, ''),
(6, 'add_phone_number_to_registration_fees.sql', '2026-04-18 18:29:49', 1, ''),
(7, 'add_testimonial_consent.sql', '2026-04-18 18:29:49', 1, ''),
(8, 'assign-michael-to-microsoft-office.sql', '2026-04-18 19:24:29', 0, 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'u.name\' in \'SELECT\''),
(9, 'create_events_table.sql', '2026-04-18 18:29:49', 1, ''),
(10, 'create_institution_photos_table.sql', '2026-04-18 18:29:49', 1, ''),
(11, 'create_newsletter_table.sql', '2026-04-18 18:29:49', 1, ''),
(12, 'create_testimonials_table.sql', '2026-04-18 18:29:49', 1, ''),
(13, 'fix-instructor-records.sql', '2026-04-18 18:29:49', 1, ''),
(16, 'add_lenco_transactions_table.sql', '2026-04-18 19:24:29', 1, ''),
(17, 'add_lenco_webhook_logs_table.sql', '2026-04-18 19:24:29', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `duration_minutes` int(11) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `unlock_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 'Introduction to Python', 'Getting started with Python programming, installation, and basic syntax', 1, 300, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(2, 5, 'Data Types and Variables', 'Understanding Python data types, variables, and operators', 2, 360, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(3, 5, 'Control Flow', 'Conditional statements, loops, and flow control in Python', 3, 420, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(4, 5, 'Functions and Modules', 'Creating functions, working with modules and packages', 4, 480, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(5, 5, 'Object-Oriented Programming', 'Classes, objects, inheritance, and OOP principles', 5, 540, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(6, 5, 'File Handling and Exceptions', 'Reading/writing files and error handling', 6, 360, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(7, 7, 'HTML5 Fundamentals', 'Introduction to HTML5 structure, elements, and semantic markup', 1, 400, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(8, 7, 'CSS3 Styling', 'Styling web pages with CSS3, layouts, and responsive design', 2, 480, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(9, 7, 'JavaScript Basics', 'JavaScript fundamentals, DOM manipulation, and events', 3, 540, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(10, 7, 'Modern JavaScript', 'ES6+ features, async programming, and APIs', 4, 600, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(20, 1, 'Microsoft Word Mastery', 'Comprehensive guide to document creation, formatting, and professional layout.', 1, NULL, 1, NULL, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(21, 1, 'Microsoft Excel Mastery', 'Complete training on spreadsheets, formulas, functions, and data analysis.', 2, NULL, 1, NULL, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(22, 1, 'Presentation & Design (PowerPoint + Publisher)', 'Mastering visual presentations and desktop publishing for print.', 3, NULL, 1, NULL, '2026-01-10 14:31:51', '2026-01-10 14:31:51', NULL),
(23, 8, 'Module 1: Introduction to Cybersecurity', 'Understanding the cybersecurity landscape, key concepts, and career paths', 1, 225, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(24, 8, 'Module 2: Networking Fundamentals', 'OSI model, TCP/IP, network protocols, and basic network security', 2, 300, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(25, 8, 'Module 3: Cyber Threats and Attacks', 'Types of malware, attack vectors, social engineering, and threat actors', 3, 285, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(26, 8, 'Module 4: Security Controls and Defense', 'Firewalls, IDS/IPS, encryption, access control, and defense strategies', 4, 330, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(27, 8, 'Module 5: Web Application Security', 'OWASP Top 10, secure coding, and web vulnerability assessment', 5, 345, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(28, 8, 'Module 6: Ethical Hacking Basics', 'Penetration testing methodology, reconnaissance, and vulnerability scanning', 6, 315, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(29, 8, 'Module 7: Incident Response and Forensics', 'Incident handling process, digital forensics fundamentals, and reporting', 7, 285, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(30, 8, 'Module 8: Security Operations (SOC)', 'SIEM tools, log analysis, monitoring, and SOC workflows', 8, 285, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(31, 8, 'Module 9: Governance, Risk, and Compliance', 'Security frameworks, risk management, and compliance standards', 9, 255, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(32, 8, 'Module 10: Capstone Project', 'Apply your skills to a real-world cybersecurity scenario', 10, 135, 1, NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(33, 35, 'Module 1: Introduction to Cybersecurity', 'Understanding the cybersecurity landscape, key concepts, and career paths', 1, 225, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(34, 35, 'Module 2: Networking Fundamentals', 'OSI model, TCP/IP, network protocols, and basic network security', 2, 300, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(35, 35, 'Module 3: Cyber Threats and Attacks', 'Types of malware, attack vectors, social engineering, and threat actors', 3, 285, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(36, 35, 'Module 4: Security Controls and Defense', 'Firewalls, IDS/IPS, encryption, access control, and defense strategies', 4, 330, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(37, 35, 'Module 5: Web Application Security', 'OWASP Top 10, secure coding, and web vulnerability assessment', 5, 345, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(38, 35, 'Module 6: Ethical Hacking Basics', 'Penetration testing methodology, reconnaissance, and vulnerability scanning', 6, 315, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(39, 35, 'Module 7: Incident Response and Forensics', 'Incident handling process, digital forensics fundamentals, and reporting', 7, 285, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(40, 35, 'Module 8: Security Operations (SOC)', 'SIEM tools, log analysis, monitoring, and SOC workflows', 8, 285, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(41, 35, 'Module 9: Governance, Risk, and Compliance', 'Security frameworks, risk management, and compliance standards', 9, 255, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(42, 35, 'Module 10: Capstone Project', 'Apply your skills to a real-world cybersecurity scenario', 10, 135, 1, NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unsubscribed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('Info','Success','Warning','Error','Assignment','Grade','Announcement') DEFAULT 'Info',
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `notification_type`, `is_read`, `read_at`, `action_url`, `icon`, `color`, `created_at`) VALUES
(1, 8, 'Welcome to EduTrack!', 'Your account has been successfully created. Start exploring courses now!', 'Success', 1, NULL, '/dashboard', NULL, NULL, '2025-11-18 22:21:01'),
(2, 8, 'Assignment Graded', 'Your assignment \"Python Basics Project\" has been graded. Score: 95/100', 'Grade', 1, NULL, '/courses/5/assignments/1', NULL, NULL, '2025-11-18 22:21:01'),
(3, 9, 'New Announcement', 'Project deadline has been extended to March 23, 2025', 'Announcement', 1, NULL, '/courses/7/announcements', NULL, NULL, '2025-11-18 22:21:01'),
(4, 11, 'Assignment Due Soon', 'Assignment \"Data Structures Assignment\" is due in 3 days', 'Warning', 0, NULL, '/courses/5/assignments/2', NULL, NULL, '2025-11-18 22:21:01'),
(5, 4, 'Certificate Ready', 'Your certificate for Python Programming is ready for download!', 'Success', 1, NULL, '/certificates/5', NULL, NULL, '2025-11-18 22:21:01'),
(6, 2, 'New Reply to Your Discussion', 'Sarah Banda replied to your discussion about responsive design', 'Info', 0, NULL, '/courses/7/discussions/3', NULL, NULL, '2025-11-18 22:21:01'),
(7, 10, 'Payment Successful', 'Your payment of $380.00 for Graphic Designing course was successful', 'Success', 1, NULL, '/payments/5', NULL, NULL, '2025-11-18 22:21:01'),
(8, 88, 'Welcome to Edutrack!', 'Your account has been created. Start exploring courses.', 'Info', 1, '2026-05-03 07:58:25', '/courses', 'fa-user', 'blue', '2026-05-03 07:58:25'),
(9, 88, 'Enrollment Confirmed', 'You have successfully enrolled in Certificate in Microsoft Office Suite.', 'Success', 1, '2026-05-04 07:58:25', '/student/courses/1', 'fa-check', 'green', '2026-05-04 07:58:25'),
(10, 88, 'Payment Received', 'Your payment of ZMW 2,500 for Microsoft Office Suite has been received.', 'Success', 1, '2026-05-05 07:58:25', '/student/payments', 'fa-credit-card', 'green', '2026-05-05 07:58:25'),
(11, 88, 'New Assignment', 'Microsoft Word assignment is now available. Due in 7 days.', 'Assignment', 1, '2026-05-07 07:58:25', '/student/assignments', 'fa-file-alt', 'orange', '2026-05-07 07:58:25'),
(12, 88, 'Assignment Graded', 'Your Microsoft Word assignment has been graded: 92/100.', 'Grade', 1, '2026-05-09 07:58:25', '/student/assignments', 'fa-star', 'yellow', '2026-05-09 07:58:25'),
(13, 88, 'Assignment Returned', 'Your Microsoft Excel assignment has been returned for revision.', 'Grade', 0, NULL, '/student/assignments', 'fa-exclamation-circle', 'red', '2026-05-12 07:58:25'),
(14, 88, 'Enrollment Confirmed', 'You have successfully enrolled in Certificate in Python Programming.', 'Success', 1, '2026-05-09 07:58:25', '/student/courses/5', 'fa-check', 'green', '2026-05-09 07:58:25'),
(15, 88, 'New Discussion Reply', 'Someone replied to your post in \"Best Python IDE for beginners?\"', 'Info', 0, NULL, '/student/discussions/1', 'fa-comment', 'blue', '2026-05-15 07:58:25'),
(16, 88, 'Quiz Results', 'You scored 100% on Python Basics Quiz!', 'Success', 1, '2026-05-13 07:58:25', '/student/quizzes/1', 'fa-trophy', 'gold', '2026-05-13 07:58:25'),
(17, 88, 'Course Completed', 'Congratulations! You have completed Certificate in Microsoft Office Suite.', 'Success', 1, '2026-05-11 07:58:25', '/student/certificates', 'fa-graduation-cap', 'purple', '2026-05-11 07:58:25'),
(18, 88, 'Certificate Issued', 'Your certificate for Microsoft Office Suite is ready for download.', 'Success', 1, '2026-05-11 07:58:25', '/student/certificates', 'fa-certificate', 'green', '2026-05-11 07:58:25'),
(19, 88, 'Payment Reminder', 'Your payment for HTML & CSS course is partially pending. ZMW 1,000 remaining.', 'Warning', 0, NULL, '/student/payments', 'fa-exclamation-triangle', 'orange', '2026-05-20 07:58:25'),
(20, 88, 'New Live Session Scheduled', 'Your instructor has scheduled a new live session on Microsoft Word basics.', 'Info', 0, NULL, NULL, NULL, NULL, '2026-05-22 12:40:10');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `payment_plan_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'ZMW',
  `payment_method_id` int(11) DEFAULT NULL,
  `payment_type` enum('registration','course_fee','partial_payment') DEFAULT 'course_fee',
  `recorded_by` int(11) DEFAULT NULL COMMENT 'User ID of admin/finance who recorded cash payment',
  `payment_status` enum('Pending','Completed','Failed','Refunded','Cancelled') DEFAULT 'Pending',
  `promotion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `transaction_id` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `student_id`, `course_id`, `enrollment_id`, `payment_plan_id`, `amount`, `currency`, `payment_method_id`, `payment_type`, `recorded_by`, `payment_status`, `promotion_id`, `discount_amount`, `transaction_id`, `phone_number`, `notes`, `payment_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 2, 7, 4, NULL, 342.00, 'ZMW', 1, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000004', NULL, NULL, '2025-01-15 09:00:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(5, 2, 15, 5, NULL, 380.00, 'ZMW', 1, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000005', NULL, NULL, '2025-01-15 09:10:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(6, 2, 17, 6, NULL, 320.00, 'ZMW', 2, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000006', NULL, NULL, '2025-01-20 11:30:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(7, 3, 18, 7, NULL, 300.00, 'ZMW', 3, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000007', NULL, NULL, '2025-01-10 14:00:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(8, 3, 19, 8, NULL, 405.00, 'ZMW', 1, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000008', NULL, NULL, '2025-02-01 10:15:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(9, 3, 1, 9, NULL, 250.00, 'ZMW', 1, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000009', NULL, NULL, '2025-01-15 12:00:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(10, 4, 5, 10, NULL, 315.00, 'ZMW', 2, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000010', NULL, NULL, '2025-01-10 08:00:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(11, 4, 6, 11, NULL, 400.00, 'ZMW', 1, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000011', NULL, NULL, '2025-02-01 09:30:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(12, 4, 9, 12, NULL, 320.00, 'ZMW', 1, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000012', NULL, NULL, '2025-02-10 11:00:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(13, 5, 11, 13, NULL, 495.00, 'ZMW', 1, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000013', NULL, NULL, '2025-02-15 13:45:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(14, 5, 12, 14, NULL, 400.00, 'ZMW', 2, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TXN-2025-000014', NULL, NULL, '2025-01-25 16:00:00', '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(16, 7, 13, 19, NULL, 540.00, 'ZMW', 1, 'course_fee', NULL, 'Pending', NULL, 0.00, 'TXN-2025-000016', NULL, NULL, NULL, '2025-11-18 22:21:01', '2026-05-05 09:55:27', NULL),
(17, 13, 1, 30, NULL, 250.00, 'ZMW', NULL, 'course_fee', NULL, 'Completed', NULL, 0.00, '1234', NULL, NULL, '2025-11-25 18:37:20', '2025-11-23 11:45:51', '2025-11-25 16:37:20', NULL),
(18, 33, 1, 31, 32, 1000.00, 'ZMW', NULL, 'partial_payment', 1, 'Completed', NULL, 0.00, 'TXN-20251228-039', NULL, 'Initial payment for Microsoft Office Suite - K1000', '2025-12-28 18:42:19', '2025-12-28 18:42:19', '2025-12-28 18:42:19', NULL),
(20, 37, 1, 34, 35, 500.00, 'ZMW', NULL, 'partial_payment', 1, 'Completed', NULL, 0.00, NULL, NULL, 'Tuition payment - Betty', '2026-01-09 04:42:00', '2026-01-09 04:42:00', '2026-01-09 04:42:00', NULL),
(21, 34, 1, 35, 36, 500.00, 'ZMW', NULL, 'partial_payment', 1, 'Completed', NULL, 0.00, NULL, NULL, 'Tuition payment - Sharon', '2026-01-09 04:42:00', '2026-01-09 04:42:00', '2026-01-09 04:42:00', NULL),
(22, 68, 11, NULL, NULL, 100.00, 'ZMW', 2, '', NULL, 'Completed', NULL, 0.00, NULL, NULL, NULL, '2026-03-16 23:37:27', '2026-03-16 23:37:27', '2026-03-16 21:37:27', NULL),
(23, 56, 7, NULL, NULL, 1500.00, 'ZMW', 5, '', NULL, 'Completed', NULL, 0.00, NULL, NULL, NULL, '2026-03-17 16:56:52', '2026-03-17 16:56:52', '2026-03-17 14:56:52', NULL),
(24, 82, 5, 52, NULL, 3000.00, 'ZMW', NULL, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TEST-PY-001', '+260770666937', NULL, '2026-05-09 09:56:21', '2026-05-09 07:56:21', '2026-05-09 07:56:21', NULL),
(25, 82, 7, 53, NULL, 500.00, 'ZMW', NULL, 'partial_payment', NULL, 'Completed', NULL, 0.00, 'TEST-HT-001', '+260770666937', NULL, '2026-05-16 09:56:21', '2026-05-16 07:56:21', '2026-05-16 07:56:21', NULL),
(26, 82, 1, 50, NULL, 2500.00, 'ZMW', NULL, 'course_fee', NULL, 'Completed', NULL, 0.00, 'TEST-MS-001', '+260770666937', NULL, '2026-05-09 09:56:21', '2026-05-09 07:56:21', '2026-05-09 07:56:21', NULL),
(28, 82, 3, 55, NULL, 850.00, 'ZMW', NULL, 'course_fee', NULL, 'Pending', NULL, 0.00, 'EDU-88-3-1779733154', '0771216339', NULL, NULL, '2026-05-25 18:19:14', '2026-05-25 18:19:14', NULL),
(29, 82, 3, 55, NULL, 850.00, 'ZMW', 2, 'course_fee', NULL, 'Failed', NULL, 0.00, 'EDU-88-3-1779735011', '0771216339', NULL, NULL, '2026-05-25 18:50:11', '2026-05-25 18:50:12', NULL),
(30, 82, 3, 55, NULL, 850.00, 'ZMW', 2, 'course_fee', NULL, 'Failed', NULL, 0.00, 'EDU-88-3-1779742576', '0771216339', NULL, NULL, '2026-05-25 20:56:16', '2026-05-25 20:56:18', NULL),
(31, 82, 4, 58, NULL, 375.00, 'ZMW', 2, 'partial_payment', NULL, 'Failed', NULL, 0.00, 'EDU-88-4-1779871010', NULL, NULL, NULL, '2026-05-27 08:36:50', '2026-05-27 08:36:51', NULL);

--
-- Triggers `payments`
--
DELIMITER $$
CREATE TRIGGER `after_payment_insert` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    DECLARE v_total_fee DECIMAL(10,2);
    DECLARE v_total_paid DECIMAL(10,2);
    DECLARE v_min_percent INT DEFAULT 30; -- Fallback value
    
    -- 1. Update the Payment Plan Balance
    IF NEW.payment_plan_id IS NOT NULL AND NEW.payment_status = 'Completed' THEN
        
        -- Get current fee and previously paid amount
        SELECT total_fee, total_paid INTO v_total_fee, v_total_paid 
        FROM enrollment_payment_plans 
        WHERE id = NEW.payment_plan_id;
        
        -- Update the Plan
        UPDATE `enrollment_payment_plans`
        SET 
            `total_paid` = `total_paid` + NEW.amount,
            `updated_at` = NOW(),
            `payment_status` = CASE 
                WHEN (`total_paid` + NEW.amount) >= `total_fee` THEN 'completed'
                ELSE 'partial'
            END
        WHERE `id` = NEW.payment_plan_id;
        
        -- 2. Check the 30% Logic to Unlock Course
        -- Calculate new total paid
        SET v_total_paid = v_total_paid + NEW.amount;
        
        -- Update Enrollment Status based on 30% Rule
        -- 'In Progress' means they have access. 'Enrolled' means waiting for deposit.
        IF (v_total_paid / v_total_fee) * 100 >= 30 THEN
            UPDATE `enrollments` 
            SET `enrollment_status` = 'In Progress' 
            WHERE `id` = NEW.enrollment_id AND `enrollment_status` = 'Enrolled';
        END IF;

        -- 3. Check Full Payment to Unblock Certificate
        IF v_total_paid >= v_total_fee THEN
            UPDATE `enrollments` 
            SET `certificate_blocked` = 0 
            WHERE `id` = NEW.enrollment_id;
        END IF;

    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_method_id` int(11) NOT NULL,
  `method_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`payment_method_id`, `method_name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Credit Card', 'Visa, Mastercard, American Express', 1, '2025-11-18 22:21:01'),
(2, 'Mobile Money', 'MTN Mobile Money, Airtel Money', 1, '2025-11-18 22:21:01'),
(3, 'Bank Transfer', 'Direct bank transfer', 1, '2025-11-18 22:21:01'),
(4, 'PayPal', 'PayPal payment gateway', 1, '2025-11-18 22:21:01'),
(5, 'Cash', 'Cash payment at office', 1, '2025-11-18 22:21:01'),
(6, 'Lenco Bank Transfer', 'Pay via bank transfer using Lenco virtual accounts', 1, '2025-12-28 18:58:28');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed_amount') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `max_uses` int(10) UNSIGNED DEFAULT NULL,
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `applicable_courses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `code`, `name`, `description`, `discount_type`, `discount_value`, `max_uses`, `used_count`, `starts_at`, `ends_at`, `is_active`, `applicable_courses`, `min_order_amount`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'WINTER2026', 'winter intake', 'blah blah blah', 'percentage', 50.00, NULL, 1, '2026-05-25 09:07:00', '2026-05-29 09:08:00', 1, '[]', NULL, 1, '2026-05-25 09:08:26', '2026-05-27 08:36:50');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `question_type` enum('Multiple Choice','True/False','Short Answer','Essay','Fill in Blank') NOT NULL,
  `question_text` text NOT NULL,
  `points` int(11) DEFAULT 1,
  `explanation` text DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `correct_answer`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Multiple Choice', 'What is the correct file extension for Python files?', 2, 'Python files use the .py extension', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(2, 'Multiple Choice', 'Which of the following is a mutable data type in Python?', 2, 'Lists are mutable, while tuples and strings are immutable', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(3, 'True/False', 'Python is a compiled language.', 1, 'Python is an interpreted language, not compiled', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(4, 'Multiple Choice', 'What does HTML stand for?', 2, 'HTML stands for HyperText Markup Language', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(5, 'Multiple Choice', 'Which CSS property is used to change text color?', 2, 'The color property is used to change text color', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(6, 'Short Answer', 'Explain the difference between a list and a tuple in Python.', 5, 'Lists are mutable and use square brackets, tuples are immutable and use parentheses', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(7, 'Multiple Choice', 'What does the \"C\" in the CIA Triad stand for?', 1, 'The CIA Triad consists of Confidentiality, Integrity, and Availability - the three core principles of cybersecurity.', NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(8, 'Multiple Choice', 'Which of the following is NOT a type of threat actor?', 1, 'System Administrators are typically defenders, not threat actors.', NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(9, 'Multiple Choice', 'A weakness in a system that could be exploited is called a(n):', 1, 'A vulnerability is a weakness in a system. A threat is a potential danger. Risk is the combination of threat and vulnerability.', NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(10, 'Multiple Choice', 'What is the primary motivation of cybercriminals?', 1, 'Cybercriminals are primarily motivated by financial gain through activities like ransomware, fraud, and data theft.', NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(11, 'Multiple Choice', 'Which principle ensures data is accessible when needed?', 1, 'Availability ensures that systems and data are accessible to authorized users when they need them.', NULL, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(12, 'Multiple Choice', 'What does the \"C\" in the CIA Triad stand for?', 1, 'The CIA Triad consists of Confidentiality, Integrity, and Availability.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(13, 'Multiple Choice', 'Which of the following is NOT a type of threat actor?', 1, 'System Administrators are defenders, not threat actors.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(14, 'Multiple Choice', 'A weakness in a system that could be exploited is called a(n):', 1, 'A vulnerability is a weakness in a system.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(15, 'Multiple Choice', 'What is the primary motivation of cybercriminals?', 1, 'Cybercriminals are primarily motivated by financial gain.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(16, 'Multiple Choice', 'Which principle ensures data is accessible when needed?', 1, 'Availability ensures systems and data are accessible.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(17, 'Multiple Choice', 'At which OSI layer does routing occur?', 1, 'Routing occurs at Layer 3 (Network Layer).', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(18, 'Multiple Choice', 'Which protocol uses port 443 by default?', 1, 'HTTPS uses port 443.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(19, 'Multiple Choice', 'What does ARP stand for?', 1, 'ARP maps IP addresses to MAC addresses.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(20, 'Multiple Choice', 'Which is the encrypted alternative to Telnet?', 1, 'SSH provides encrypted remote access.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(21, 'Multiple Choice', 'Network segmentation using VLANs primarily helps with:', 1, 'VLANs segment networks to contain breaches.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(22, 'Multiple Choice', 'Which malware type encrypts files and demands payment?', 1, 'Ransomware encrypts files and demands payment.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(23, 'Multiple Choice', 'What is phishing?', 1, 'Phishing is a fraudulent attempt to obtain sensitive information.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(24, 'Multiple Choice', 'In the Cyber Kill Chain, what comes after \"Delivery\"?', 1, 'The phases are: Reconnaissance -> Weaponization -> Delivery -> Exploitation.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(25, 'Multiple Choice', 'Which of the following is a social engineering technique?', 1, 'Tailgating is a physical social engineering technique.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(26, 'Multiple Choice', 'What is the primary purpose of a keylogger?', 1, 'A keylogger records keystrokes to capture passwords.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(27, 'Multiple Choice', 'Which encryption type uses the same key for encryption and decryption?', 1, 'Symmetric encryption uses a single shared key.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(28, 'Multiple Choice', 'What does MFA stand for?', 1, 'Multi-Factor Authentication requires two or more verification factors.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(29, 'Multiple Choice', 'In RBAC, access is determined by:', 1, 'Role-Based Access Control grants access based on user roles.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(30, 'Multiple Choice', 'Which system actively blocks detected threats in real-time?', 1, 'IPS actively blocks threats while IDS only detects and alerts.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(31, 'Multiple Choice', 'TLS is used for:', 1, 'TLS encrypts web traffic and enables HTTPS.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(32, 'Multiple Choice', 'Which OWASP Top 10 item involves untrusted data sent to interpreters?', 1, 'Injection flaws occur when untrusted data is sent to interpreters.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(33, 'Multiple Choice', 'What is the best defense against SQL Injection?', 1, 'Parameterized queries separate SQL code from data.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(34, 'Multiple Choice', 'Which XSS type stores the malicious script on the server?', 1, 'Stored (Persistent) XSS stores scripts permanently on the server.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(35, 'Multiple Choice', 'Which header prevents clickjacking attacks?', 1, 'X-Frame-Options controls whether a page can be displayed in frames.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(36, 'Multiple Choice', 'What does CSP stand for in web security?', 1, 'Content Security Policy helps prevent XSS and data injection.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(37, 'Multiple Choice', 'Which type of hacker is authorized to test systems?', 1, 'White Hat hackers have authorization to test and improve security.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(38, 'Multiple Choice', 'What does OSINT stand for?', 1, 'OSINT refers to intelligence gathered from publicly available sources.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(39, 'Multiple Choice', 'Which tool is commonly used for port scanning?', 1, 'Nmap is the industry-standard tool for port scanning.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(40, 'Multiple Choice', 'In a Black Box test, the tester has:', 1, 'Black Box testing simulates an external attacker with no prior knowledge.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(41, 'Multiple Choice', 'What does CVE stand for?', 1, 'CVE provides standardized identifiers for known vulnerabilities.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(42, 'Multiple Choice', 'Which NIST phase involves removing threats and vulnerabilities?', 1, 'Eradication removes threats and eliminates vulnerabilities.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(43, 'Multiple Choice', 'What is the primary purpose of chain of custody?', 1, 'Chain of custody ensures digital evidence is admissible in court.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(44, 'Multiple Choice', 'Which tool is used for memory forensics?', 1, 'Volatility is an open-source memory forensics framework.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(45, 'Multiple Choice', 'What should be done FIRST when responding to an incident?', 1, 'Preserving evidence is critical for investigation.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(46, 'Multiple Choice', 'A bit-for-bit copy of storage media is called a:', 1, 'A disk image preserves all data including deleted files.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(47, 'Multiple Choice', 'What does SIEM stand for?', 1, 'SIEM collects, correlates, and analyzes security events.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(48, 'Multiple Choice', 'Which SOC tier is responsible for initial alert review?', 1, 'Tier 1 analysts handle initial alert triage.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(49, 'Multiple Choice', 'MTTD stands for:', 1, 'MTTD measures average time to detect threats.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(50, 'Multiple Choice', 'Which of the following is an open-source SIEM tool?', 1, 'Wazuh is an open-source security monitoring platform.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(51, 'Multiple Choice', 'What is the MITRE ATT&CK framework used for?', 1, 'MITRE ATT&CK is used for threat modeling and detection.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(52, 'Multiple Choice', 'The NIST CSF consists of how many core functions?', 1, 'NIST CSF has 5 core functions.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(53, 'Multiple Choice', 'ISO 27001 is a standard for:', 1, 'ISO 27001 is for Information Security Management Systems.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(54, 'Multiple Choice', 'Risk is calculated as:', 1, 'Risk = Likelihood x Impact.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(55, 'Multiple Choice', 'Which risk treatment involves transferring risk to a third party?', 1, 'Risk transfer moves financial consequence to another party.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(56, 'Multiple Choice', 'Which NIST function involves taking action on detected incidents?', 1, 'The Respond function includes taking action on incidents.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(57, 'Multiple Choice', 'The three principles of the CIA Triad are:', 1, 'Confidentiality, Integrity, and Availability.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(58, 'Multiple Choice', 'Which layer of the OSI model handles routing?', 1, 'Layer 3 (Network Layer).', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(59, 'Multiple Choice', 'Which malware type does NOT need a host program?', 1, 'Worms are self-replicating and do not need a host.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(60, 'Multiple Choice', 'Which encryption algorithm is symmetric?', 1, 'AES is symmetric. RSA and ECC are asymmetric.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(61, 'Multiple Choice', 'What is the primary defense against SQL Injection?', 1, 'Parameterized queries are the most effective defense.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(62, 'Multiple Choice', 'In a Black Box penetration test, the tester has:', 1, 'No prior knowledge.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(63, 'Multiple Choice', 'Which phase of incident response involves limiting damage?', 1, 'Containment isolates the incident.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(64, 'Multiple Choice', 'What does a SIEM system primarily do?', 1, 'Collects and analyzes security events.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(65, 'Multiple Choice', 'The NIST Cybersecurity Framework has how many functions?', 1, 'NIST CSF has 5 functions.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(66, 'Multiple Choice', 'Which access control model grants permissions based on user roles?', 1, 'RBAC grants permissions based on roles.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(67, 'Multiple Choice', 'What is the purpose of a honeypot?', 1, 'A honeypot attracts and detects attackers.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(68, 'Multiple Choice', 'Which social engineering technique involves following someone into a secure area?', 1, 'Tailgating follows someone into restricted areas.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(69, 'Multiple Choice', 'TLS is used to:', 1, 'TLS encrypts web traffic for HTTPS.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(70, 'Multiple Choice', 'What does OWASP stand for?', 1, 'Open Web Application Security Project.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(71, 'Multiple Choice', 'Risk is best defined as:', 1, 'Risk = Likelihood x Impact.', NULL, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(75, 'Multiple Choice', 'What is the capital of Zambia?', 2, 'Lusaka is the capital and largest city of Zambia.', NULL, '2026-05-23 15:42:48', '2026-05-23 15:42:48', NULL),
(76, 'Short Answer', 'What is the capital of Zambia?', 2, 'Lusaka is the capital.', 'Lusaka', '2026-05-23 16:38:19', '2026-05-23 16:38:19', NULL),
(77, 'Essay', 'Explain the importance of computer literacy in modern Zambia.', 5, 'Look for discussion of employment, education, and digital divide.', 'Should mention: job opportunities, access to information, online services, education, and economic growth.', '2026-05-23 16:38:19', '2026-05-23 16:38:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `option_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`option_id`, `question_id`, `option_text`, `is_correct`, `display_order`) VALUES
(1, 1, '.pyth', 0, 1),
(2, 1, '.py', 1, 2),
(3, 1, '.pt', 0, 3),
(4, 1, '.python', 0, 4),
(5, 2, 'String', 0, 1),
(6, 2, 'Tuple', 0, 2),
(7, 2, 'List', 1, 3),
(8, 2, 'Integer', 0, 4),
(9, 3, 'True', 0, 1),
(10, 3, 'False', 1, 2),
(11, 4, 'HyperText Markup Language', 1, 1),
(12, 4, 'High Tech Modern Language', 0, 2),
(13, 4, 'Home Tool Markup Language', 0, 3),
(14, 4, 'Hyperlinks and Text Markup Language', 0, 4),
(15, 5, 'font-color', 0, 1),
(16, 5, 'text-color', 0, 2),
(17, 5, 'color', 1, 3),
(18, 5, 'foreground-color', 0, 4),
(19, 7, 'Control', 0, 1),
(20, 7, 'Confidentiality', 1, 2),
(21, 7, 'Certification', 0, 3),
(22, 7, 'Compliance', 0, 4),
(23, 8, 'Script Kiddie', 0, 1),
(24, 8, 'Cybercriminal', 0, 2),
(25, 8, 'System Administrator', 1, 3),
(26, 8, 'State-Sponsored Actor', 0, 4),
(27, 9, 'Threat', 0, 1),
(28, 9, 'Risk', 0, 2),
(29, 9, 'Vulnerability', 1, 3),
(30, 9, 'Exploit', 0, 4),
(31, 10, 'Political change', 0, 1),
(32, 10, 'Social justice', 0, 2),
(33, 10, 'Financial gain', 1, 3),
(34, 10, 'Personal recognition', 0, 4),
(35, 11, 'Confidentiality', 0, 1),
(36, 11, 'Integrity', 0, 2),
(37, 11, 'Availability', 1, 3),
(38, 11, 'Authentication', 0, 4),
(39, 12, 'Control', 0, 1),
(40, 12, 'Confidentiality', 1, 2),
(41, 12, 'Certification', 0, 3),
(42, 12, 'Compliance', 0, 4),
(43, 13, 'Script Kiddie', 0, 1),
(44, 13, 'Cybercriminal', 0, 2),
(45, 13, 'System Administrator', 1, 3),
(46, 13, 'State-Sponsored Actor', 0, 4),
(47, 14, 'Threat', 0, 1),
(48, 14, 'Risk', 0, 2),
(49, 14, 'Vulnerability', 1, 3),
(50, 14, 'Exploit', 0, 4),
(51, 15, 'Political change', 0, 1),
(52, 15, 'Social justice', 0, 2),
(53, 15, 'Financial gain', 1, 3),
(54, 15, 'Personal recognition', 0, 4),
(55, 16, 'Confidentiality', 0, 1),
(56, 16, 'Integrity', 0, 2),
(57, 16, 'Availability', 1, 3),
(58, 16, 'Authentication', 0, 4),
(59, 17, 'Layer 2', 0, 1),
(60, 17, 'Layer 3', 1, 2),
(61, 17, 'Layer 4', 0, 3),
(62, 17, 'Layer 7', 0, 4),
(63, 18, 'HTTP', 0, 1),
(64, 18, 'FTP', 0, 2),
(65, 18, 'HTTPS', 1, 3),
(66, 18, 'SSH', 0, 4),
(67, 19, 'Address Resolution Protocol', 1, 1),
(68, 19, 'Advanced Routing Protocol', 0, 2),
(69, 19, 'Application Resource Protocol', 0, 3),
(70, 19, 'Automatic Response Procedure', 0, 4),
(71, 20, 'FTP', 0, 1),
(72, 20, 'SSH', 1, 2),
(73, 20, 'HTTP', 0, 3),
(74, 20, 'SMTP', 0, 4),
(75, 21, 'Increasing internet speed', 0, 1),
(76, 21, 'Limiting attack spread', 1, 2),
(77, 21, 'Reducing cable costs', 0, 3),
(78, 21, 'Improving wireless signal', 0, 4),
(79, 22, 'Virus', 0, 1),
(80, 22, 'Worm', 0, 2),
(81, 22, 'Ransomware', 1, 3),
(82, 22, 'Spyware', 0, 4),
(83, 23, 'A network scanning technique', 0, 1),
(84, 23, 'A fraudulent attempt to obtain sensitive information', 1, 2),
(85, 23, 'A type of firewall', 0, 3),
(86, 23, 'A password hashing method', 0, 4),
(87, 24, 'Reconnaissance', 0, 1),
(88, 24, 'Weaponization', 0, 2),
(89, 24, 'Exploitation', 1, 3),
(90, 24, 'Installation', 0, 4),
(91, 25, 'SQL Injection', 0, 1),
(92, 25, 'Tailgating', 1, 2),
(93, 25, 'Buffer Overflow', 0, 3),
(94, 25, 'ARP Spoofing', 0, 4),
(95, 26, 'Encrypt files', 0, 1),
(96, 26, 'Record keystrokes', 1, 2),
(97, 26, 'Scan networks', 0, 3),
(98, 26, 'Block websites', 0, 4),
(99, 27, 'Asymmetric', 0, 1),
(100, 27, 'Symmetric', 1, 2),
(101, 27, 'Hashing', 0, 3),
(102, 27, 'Public-key', 0, 4),
(103, 28, 'Multi-Factor Authentication', 1, 1),
(104, 28, 'Managed Firewall Access', 0, 2),
(105, 28, 'Multi-Function Application', 0, 3),
(106, 28, 'Modular Framework Architecture', 0, 4),
(107, 29, 'User attributes', 0, 1),
(108, 29, 'Resource ownership', 0, 2),
(109, 29, 'User roles', 1, 3),
(110, 29, 'Security labels', 0, 4),
(111, 30, 'IDS', 0, 1),
(112, 30, 'IPS', 1, 2),
(113, 30, 'Firewall', 0, 3),
(114, 30, 'Proxy', 0, 4),
(115, 31, 'File compression', 0, 1),
(116, 31, 'Secure web communication', 1, 2),
(117, 31, 'Database indexing', 0, 3),
(118, 31, 'Email routing', 0, 4),
(119, 32, 'Broken Access Control', 0, 1),
(120, 32, 'Cryptographic Failures', 0, 2),
(121, 32, 'Injection', 1, 3),
(122, 32, 'Insecure Design', 0, 4),
(123, 33, 'Input validation only', 0, 1),
(124, 33, 'Parameterized queries', 1, 2),
(125, 33, 'Firewall rules', 0, 3),
(126, 33, 'URL encoding', 0, 4),
(127, 34, 'Reflected XSS', 0, 1),
(128, 34, 'Stored XSS', 1, 2),
(129, 34, 'DOM-based XSS', 0, 3),
(130, 34, 'Blind XSS', 0, 4),
(131, 35, 'Content-Security-Policy', 0, 1),
(132, 35, 'X-Frame-Options', 1, 2),
(133, 35, 'X-XSS-Protection', 0, 3),
(134, 35, 'Strict-Transport-Security', 0, 4),
(135, 36, 'Cross-Site Protection', 0, 1),
(136, 36, 'Content Security Policy', 1, 2),
(137, 36, 'Client-Side Protocol', 0, 3),
(138, 36, 'Certificate Signing Process', 0, 4),
(139, 37, 'Black Hat', 0, 1),
(140, 37, 'White Hat', 1, 2),
(141, 37, 'Gray Hat', 0, 3),
(142, 37, 'Script Kiddie', 0, 4),
(143, 38, 'Open Source Intelligence', 1, 1),
(144, 38, 'Operating System Integration', 0, 2),
(145, 38, 'Online Security Intelligence', 0, 3),
(146, 38, 'Organizational Security Interface', 0, 4),
(147, 39, 'Wireshark', 0, 1),
(148, 39, 'Nmap', 1, 2),
(149, 39, 'Metasploit', 0, 3),
(150, 39, 'Burp Suite', 0, 4),
(151, 40, 'Full knowledge', 0, 1),
(152, 40, 'No prior knowledge', 1, 2),
(153, 40, 'Partial knowledge', 0, 3),
(154, 40, 'Source code access', 0, 4),
(155, 41, 'Common Vulnerability Enumeration', 0, 1),
(156, 41, 'Common Vulnerabilities and Exposures', 1, 2),
(157, 41, 'Critical Vulnerability Entry', 0, 3),
(158, 41, 'Computer Virus Encyclopedia', 0, 4),
(159, 42, 'Containment', 0, 1),
(160, 42, 'Eradication', 1, 2),
(161, 42, 'Recovery', 0, 3),
(162, 42, 'Detection', 0, 4),
(163, 43, 'Speed up investigation', 0, 1),
(164, 43, 'Ensure evidence admissibility', 1, 2),
(165, 43, 'Reduce costs', 0, 3),
(166, 43, 'Identify attackers', 0, 4),
(167, 44, 'Nmap', 0, 1),
(168, 44, 'Volatility', 1, 2),
(169, 44, 'Wireshark', 0, 3),
(170, 44, 'Nessus', 0, 4),
(171, 45, 'Format affected systems', 0, 1),
(172, 45, 'Preserve evidence', 1, 2),
(173, 45, 'Notify the media', 0, 3),
(174, 45, 'Update antivirus', 0, 4),
(175, 46, 'Snapshot', 0, 1),
(176, 46, 'Disk Image', 1, 2),
(177, 46, 'Backup', 0, 3),
(178, 46, 'Archive', 0, 4),
(179, 47, 'Security Information and Event Management', 1, 1),
(180, 47, 'System Intelligence and Event Monitoring', 0, 2),
(181, 47, 'Secure Internet and Email Management', 0, 3),
(182, 47, 'System Integration and Enterprise Monitoring', 0, 4),
(183, 48, 'Tier 1', 1, 1),
(184, 48, 'Tier 2', 0, 2),
(185, 48, 'Tier 3', 0, 3),
(186, 48, 'Tier 4', 0, 4),
(187, 49, 'Mean Time to Detect', 1, 1),
(188, 49, 'Maximum Time to Detection', 0, 2),
(189, 49, 'Minimum Technical Threat Duration', 0, 3),
(190, 49, 'Managed Threat Transfer Delay', 0, 4),
(191, 50, 'Splunk', 0, 1),
(192, 50, 'QRadar', 0, 2),
(193, 50, 'Wazuh', 1, 3),
(194, 50, 'Microsoft Sentinel', 0, 4),
(195, 51, 'Network routing', 0, 1),
(196, 51, 'Threat modeling and detection', 1, 2),
(197, 51, 'Password management', 0, 3),
(198, 51, 'Data encryption', 0, 4),
(199, 52, '3', 0, 1),
(200, 52, '4', 0, 2),
(201, 52, '5', 1, 3),
(202, 52, '6', 0, 4),
(203, 53, 'Payment processing', 0, 1),
(204, 53, 'Information security management', 1, 2),
(205, 53, 'Network routing', 0, 3),
(206, 53, 'Software development', 0, 4),
(207, 54, 'Threat + Vulnerability', 0, 1),
(208, 54, 'Likelihood x Impact', 1, 2),
(209, 54, 'Asset Value - Cost', 0, 3),
(210, 54, 'Threat - Control', 0, 4),
(211, 55, 'Accept', 0, 1),
(212, 55, 'Mitigate', 0, 2),
(213, 55, 'Transfer', 1, 3),
(214, 55, 'Avoid', 0, 4),
(215, 56, 'Identify', 0, 1),
(216, 56, 'Protect', 0, 2),
(217, 56, 'Detect', 0, 3),
(218, 56, 'Respond', 1, 4),
(219, 57, 'Control, Integrity, Authentication', 0, 1),
(220, 57, 'Confidentiality, Integrity, Availability', 1, 2),
(221, 57, 'Certification, Implementation, Assessment', 0, 3),
(222, 57, 'Compliance, Investigation, Analysis', 0, 4),
(223, 58, 'Data Link', 0, 1),
(224, 58, 'Network', 1, 2),
(225, 58, 'Transport', 0, 3),
(226, 58, 'Application', 0, 4),
(227, 59, 'Virus', 0, 1),
(228, 59, 'Worm', 1, 2),
(229, 59, 'Trojan', 0, 3),
(230, 59, 'Rootkit', 0, 4),
(231, 60, 'RSA', 0, 1),
(232, 60, 'AES', 1, 2),
(233, 60, 'ECC', 0, 3),
(234, 60, 'DSA', 0, 4),
(235, 61, 'Input validation', 0, 1),
(236, 61, 'Parameterized queries', 1, 2),
(237, 61, 'Web Application Firewall', 0, 3),
(238, 61, 'URL encoding', 0, 4),
(239, 62, 'Full knowledge of systems', 0, 1),
(240, 62, 'No prior knowledge', 1, 2),
(241, 62, 'Source code access', 0, 3),
(242, 62, 'Administrator credentials', 0, 4),
(243, 63, 'Detection', 0, 1),
(244, 63, 'Containment', 1, 2),
(245, 63, 'Eradication', 0, 3),
(246, 63, 'Recovery', 0, 4),
(247, 64, 'Encrypt network traffic', 0, 1),
(248, 64, 'Collect and analyze security events', 1, 2),
(249, 64, 'Block malicious websites', 0, 3),
(250, 64, 'Manage user passwords', 0, 4),
(251, 65, '3', 0, 1),
(252, 65, '4', 0, 2),
(253, 65, '5', 1, 3),
(254, 65, '6', 0, 4),
(255, 66, 'DAC', 0, 1),
(256, 66, 'MAC', 0, 2),
(257, 66, 'RBAC', 1, 3),
(258, 66, 'ABAC', 0, 4),
(259, 67, 'Store backups', 0, 1),
(260, 67, 'Attract and detect attackers', 1, 2),
(261, 67, 'Encrypt data', 0, 3),
(262, 67, 'Speed up networks', 0, 4),
(263, 68, 'Phishing', 0, 1),
(264, 68, 'Pretexting', 0, 2),
(265, 68, 'Tailgating', 1, 3),
(266, 68, 'Baiting', 0, 4),
(267, 69, 'Compress files', 0, 1),
(268, 69, 'Encrypt web traffic', 1, 2),
(269, 69, 'Scan for viruses', 0, 3),
(270, 69, 'Route packets', 0, 4),
(271, 70, 'Open Web Application Security Project', 1, 1),
(272, 70, 'Online Web Attack and Security Protocol', 0, 2),
(273, 70, 'Operational Web Application Standard Procedure', 0, 3),
(274, 70, 'Open Web Authentication Security Program', 0, 4),
(275, 71, 'Threat + Vulnerability', 0, 1),
(276, 71, 'Likelihood x Impact', 1, 2),
(277, 71, 'Asset x Control', 0, 3),
(278, 71, 'Cost + Benefit', 0, 4),
(279, 75, 'Ndola', 0, 1),
(280, 75, 'Lusaka', 1, 2),
(281, 75, 'Kitwe', 0, 3),
(282, 75, 'Livingstone', 0, 4);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `quiz_type` enum('Practice','Graded','Final Exam','Midterm') DEFAULT 'Graded',
  `time_limit_minutes` int(11) DEFAULT NULL,
  `max_attempts` int(11) DEFAULT 1,
  `passing_score` decimal(5,2) DEFAULT 60.00,
  `randomize_questions` tinyint(1) DEFAULT 0,
  `show_correct_answers` tinyint(1) DEFAULT 0,
  `available_from` datetime DEFAULT NULL,
  `available_until` datetime DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, NULL, 'Python Basics Quiz', 'Test your knowledge of Python fundamentals', 'Practice', 30, 3, 70.00, 1, 1, NULL, NULL, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(2, 5, NULL, 'Python Midterm Exam', 'Comprehensive midterm covering modules 1-3', 'Midterm', 60, 1, 70.00, 1, 0, NULL, NULL, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(3, 7, NULL, 'HTML & CSS Quiz', 'Assessment of HTML5 and CSS3 knowledge', 'Graded', 45, 2, 75.00, 1, 1, NULL, NULL, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(4, 11, NULL, 'Cybersecurity Final Exam', 'Comprehensive final examination', 'Final Exam', 120, 1, 75.00, 1, 0, NULL, NULL, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', NULL),
(5, 8, NULL, 'Module 1 Quiz: Introduction to Cybersecurity', 'Test your understanding of cybersecurity fundamentals', 'Graded', 20, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(6, 8, NULL, 'Module 2 Quiz: Networking Fundamentals', 'Test your networking knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(7, 8, NULL, 'Module 3 Quiz: Cyber Threats and Attacks', 'Test your knowledge of cyber threats', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(8, 8, NULL, 'Module 4 Quiz: Security Controls and Defense', 'Test your knowledge of security controls', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(9, 8, NULL, 'Module 5 Quiz: Web Application Security', 'Test your web security knowledge', 'Graded', 30, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(10, 8, NULL, 'Module 6 Quiz: Ethical Hacking Basics', 'Test your ethical hacking knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(11, 8, NULL, 'Module 7 Quiz: Incident Response and Forensics', 'Test your incident response knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(12, 8, NULL, 'Module 8 Quiz: Security Operations', 'Test your SOC knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(13, 8, NULL, 'Module 9 Quiz: Governance, Risk, and Compliance', 'Test your GRC knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(14, 8, NULL, 'Final Assessment: Cybersecurity Fundamentals', 'Comprehensive final assessment covering all modules', 'Final Exam', 60, 2, 75.00, 1, 0, NULL, NULL, 1, '2026-05-04 18:50:46', '2026-05-04 18:50:46', NULL),
(15, 35, NULL, 'Module 1 Quiz: Introduction to Cybersecurity', 'Test your understanding of cybersecurity fundamentals', 'Graded', 20, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(16, 35, NULL, 'Module 2 Quiz: Networking Fundamentals', 'Test your networking knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(17, 35, NULL, 'Module 3 Quiz: Cyber Threats and Attacks', 'Test your knowledge of cyber threats', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(18, 35, NULL, 'Module 4 Quiz: Security Controls and Defense', 'Test your knowledge of security controls', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(19, 35, NULL, 'Module 5 Quiz: Web Application Security', 'Test your web security knowledge', 'Graded', 30, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(20, 35, NULL, 'Module 6 Quiz: Ethical Hacking Basics', 'Test your ethical hacking knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(21, 35, NULL, 'Module 7 Quiz: Incident Response and Forensics', 'Test your incident response knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(22, 35, NULL, 'Module 8 Quiz: Security Operations', 'Test your SOC knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(23, 35, NULL, 'Module 9 Quiz: Governance, Risk, and Compliance', 'Test your GRC knowledge', 'Graded', 25, 3, 70.00, 1, 1, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(24, 35, NULL, 'Final Assessment: Cybersecurity Fundamentals', 'Comprehensive final assessment covering all modules', 'Final Exam', 60, 2, 75.00, 1, 0, NULL, NULL, 1, '2026-05-04 21:14:27', '2026-05-04 21:14:27', NULL),
(25, 1, NULL, 'practise', 'sssss', 'Graded', 30, 1, 60.00, 0, 0, NULL, NULL, 1, '2026-05-23 13:05:31', '2026-05-23 13:05:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `answer_id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` decimal(5,2) DEFAULT NULL,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_answers`
--

INSERT INTO `quiz_answers` (`answer_id`, `attempt_id`, `question_id`, `selected_option_id`, `answer_text`, `is_correct`, `points_earned`, `answered_at`) VALUES
(1, 5, 1, 2, NULL, 1, 10.00, '2026-05-13 08:01:21'),
(2, 5, 2, 7, NULL, 1, 10.00, '2026-05-13 08:01:21'),
(3, 5, 3, 10, NULL, 1, 10.00, '2026-05-13 08:01:21'),
(4, 6, 1, 1, NULL, 0, 0.00, '2026-05-14 07:59:21'),
(5, 6, 2, 5, NULL, 0, 0.00, '2026-05-14 07:59:21'),
(6, 6, 3, 9, NULL, 0, 0.00, '2026-05-14 07:59:21'),
(7, 7, 4, 11, NULL, 1, 10.00, '2026-05-18 08:00:21'),
(8, 7, 5, 17, NULL, 1, 10.00, '2026-05-18 08:00:21'),
(9, 8, 1, NULL, NULL, 0, 0.00, '2026-05-23 10:22:17'),
(10, 8, 2, NULL, NULL, 0, 0.00, '2026-05-23 10:22:17'),
(11, 8, 3, NULL, 'True', 0, 0.00, '2026-05-23 10:22:17'),
(12, 10, 75, NULL, NULL, 0, 0.00, '2026-05-24 16:03:34'),
(13, 10, 76, NULL, 'lusaka', 1, 2.00, '2026-05-24 16:03:34'),
(14, 10, 77, NULL, 'i dont know', 0, 0.00, '2026-05-24 16:03:34');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attempt_number` int(11) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `status` enum('In Progress','Submitted','Graded','Abandoned') DEFAULT 'In Progress',
  `time_spent_minutes` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`quiz_id`, `student_id`, `attempt_number`, `started_at`, `submitted_at`, `score`, `status`, `time_spent_minutes`, `ip_address`, `id`, `completed_at`) VALUES
(1, 1, 1, '2025-01-25 12:00:00', '2025-01-25 12:28:00', 85.00, 'Graded', 28, NULL, 1, NULL),
(1, 4, 1, '2025-01-26 08:00:00', '2025-01-26 08:25:00', 92.00, 'Graded', 25, NULL, 2, NULL),
(1, 4, 2, '2025-01-27 13:00:00', '2025-01-27 13:20:00', 98.00, 'Graded', 20, NULL, 3, NULL),
(3, 2, 1, '2025-02-10 09:00:00', '2025-02-10 09:40:00', 88.00, 'Graded', 40, NULL, 4, NULL),
(1, 82, 1, '2026-05-13 07:56:21', '2026-05-13 08:11:21', 100.00, 'Graded', 15, '127.0.0.1', 5, '2026-05-13 10:11:21'),
(1, 82, 2, '2026-05-14 07:56:21', '2026-05-14 08:04:21', 33.33, 'Graded', 8, '127.0.0.1', 6, '2026-05-14 10:04:21'),
(3, 82, 1, '2026-05-18 07:56:21', '2026-05-18 08:08:21', 100.00, 'Graded', 12, '127.0.0.1', 7, '2026-05-18 10:08:21'),
(1, 82, 3, '2026-05-23 10:21:45', '2026-05-23 10:22:17', 0.00, 'Graded', 0, '127.0.0.1', 8, '2026-05-23 12:22:17'),
(2, 82, 1, '2026-05-23 10:47:16', NULL, NULL, 'In Progress', NULL, '127.0.0.1', 9, NULL),
(25, 82, 1, '2026-05-24 16:03:10', '2026-05-24 16:03:34', 22.22, 'Submitted', 0, '127.0.0.1', 10, '2026-05-24 18:03:34');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `quiz_question_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `points_override` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`quiz_question_id`, `quiz_id`, `question_id`, `display_order`, `points_override`) VALUES
(1, 1, 1, 1, NULL),
(2, 1, 2, 2, NULL),
(3, 1, 3, 3, NULL),
(4, 3, 4, 1, NULL),
(5, 3, 5, 2, NULL),
(6, 15, 12, 1, NULL),
(7, 15, 13, 2, NULL),
(8, 15, 14, 3, NULL),
(9, 15, 15, 4, NULL),
(10, 15, 16, 5, NULL),
(11, 16, 17, 1, NULL),
(12, 16, 18, 2, NULL),
(13, 16, 19, 3, NULL),
(14, 16, 20, 4, NULL),
(15, 16, 21, 5, NULL),
(16, 17, 22, 1, NULL),
(17, 17, 23, 2, NULL),
(18, 17, 24, 3, NULL),
(19, 17, 25, 4, NULL),
(20, 17, 26, 5, NULL),
(21, 18, 27, 1, NULL),
(22, 18, 28, 2, NULL),
(23, 18, 29, 3, NULL),
(24, 18, 30, 4, NULL),
(25, 18, 31, 5, NULL),
(26, 19, 32, 1, NULL),
(27, 19, 33, 2, NULL),
(28, 19, 34, 3, NULL),
(29, 19, 35, 4, NULL),
(30, 19, 36, 5, NULL),
(31, 20, 37, 1, NULL),
(32, 20, 38, 2, NULL),
(33, 20, 39, 3, NULL),
(34, 20, 40, 4, NULL),
(35, 20, 41, 5, NULL),
(36, 21, 42, 1, NULL),
(37, 21, 43, 2, NULL),
(38, 21, 44, 3, NULL),
(39, 21, 45, 4, NULL),
(40, 21, 46, 5, NULL),
(41, 22, 47, 1, NULL),
(42, 22, 48, 2, NULL),
(43, 22, 49, 3, NULL),
(44, 22, 50, 4, NULL),
(45, 22, 51, 5, NULL),
(46, 23, 52, 1, NULL),
(47, 23, 53, 2, NULL),
(48, 23, 54, 3, NULL),
(49, 23, 55, 4, NULL),
(50, 23, 56, 5, NULL),
(51, 24, 57, 1, NULL),
(52, 24, 58, 2, NULL),
(53, 24, 59, 3, NULL),
(54, 24, 60, 4, NULL),
(55, 24, 61, 5, NULL),
(56, 24, 62, 6, NULL),
(57, 24, 63, 7, NULL),
(58, 24, 64, 8, NULL),
(59, 24, 65, 9, NULL),
(60, 24, 66, 10, NULL),
(61, 24, 67, 11, NULL),
(62, 24, 68, 12, NULL),
(63, 24, 69, 13, NULL),
(64, 24, 70, 14, NULL),
(65, 24, 71, 15, NULL),
(66, 25, 75, 1, 2),
(67, 25, 76, 2, 2),
(68, 25, 77, 3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_question_options`
--

CREATE TABLE `quiz_question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_question_options`
--

INSERT INTO `quiz_question_options` (`id`, `question_id`, `option_text`, `is_correct`, `display_order`, `created_at`) VALUES
(1, 1, '.pyth', 0, 1, '2026-05-05 07:36:29'),
(2, 1, '.py', 1, 2, '2026-05-05 07:36:29'),
(3, 1, '.pt', 0, 3, '2026-05-05 07:36:29'),
(4, 1, '.python', 0, 4, '2026-05-05 07:36:29'),
(5, 2, 'String', 0, 1, '2026-05-05 07:36:29'),
(6, 2, 'Tuple', 0, 2, '2026-05-05 07:36:29'),
(7, 2, 'List', 1, 3, '2026-05-05 07:36:29'),
(8, 2, 'Integer', 0, 4, '2026-05-05 07:36:29'),
(9, 3, 'True', 0, 1, '2026-05-05 07:36:29'),
(10, 3, 'False', 1, 2, '2026-05-05 07:36:29'),
(11, 4, 'HyperText Markup Language', 1, 1, '2026-05-05 07:36:29'),
(12, 4, 'High Tech Modern Language', 0, 2, '2026-05-05 07:36:29'),
(13, 4, 'Home Tool Markup Language', 0, 3, '2026-05-05 07:36:29'),
(14, 4, 'Hyperlinks and Text Markup Language', 0, 4, '2026-05-05 07:36:29'),
(15, 5, 'font-color', 0, 1, '2026-05-05 07:36:29'),
(16, 5, 'text-color', 0, 2, '2026-05-05 07:36:29'),
(17, 5, 'color', 1, 3, '2026-05-05 07:36:29'),
(18, 5, 'foreground-color', 0, 4, '2026-05-05 07:36:29'),
(19, 7, 'Control', 0, 1, '2026-05-05 07:36:29'),
(20, 7, 'Confidentiality', 1, 2, '2026-05-05 07:36:29'),
(21, 7, 'Certification', 0, 3, '2026-05-05 07:36:29'),
(22, 7, 'Compliance', 0, 4, '2026-05-05 07:36:29'),
(23, 8, 'Script Kiddie', 0, 1, '2026-05-05 07:36:29'),
(24, 8, 'Cybercriminal', 0, 2, '2026-05-05 07:36:29'),
(25, 8, 'System Administrator', 1, 3, '2026-05-05 07:36:29'),
(26, 8, 'State-Sponsored Actor', 0, 4, '2026-05-05 07:36:29'),
(27, 9, 'Threat', 0, 1, '2026-05-05 07:36:29'),
(28, 9, 'Risk', 0, 2, '2026-05-05 07:36:29'),
(29, 9, 'Vulnerability', 1, 3, '2026-05-05 07:36:29'),
(30, 9, 'Exploit', 0, 4, '2026-05-05 07:36:29'),
(31, 10, 'Political change', 0, 1, '2026-05-05 07:36:29'),
(32, 10, 'Social justice', 0, 2, '2026-05-05 07:36:29'),
(33, 10, 'Financial gain', 1, 3, '2026-05-05 07:36:29'),
(34, 10, 'Personal recognition', 0, 4, '2026-05-05 07:36:29'),
(35, 11, 'Confidentiality', 0, 1, '2026-05-05 07:36:29'),
(36, 11, 'Integrity', 0, 2, '2026-05-05 07:36:29'),
(37, 11, 'Availability', 1, 3, '2026-05-05 07:36:29'),
(38, 11, 'Authentication', 0, 4, '2026-05-05 07:36:29'),
(39, 12, 'Control', 0, 1, '2026-05-05 07:36:29'),
(40, 12, 'Confidentiality', 1, 2, '2026-05-05 07:36:29'),
(41, 12, 'Certification', 0, 3, '2026-05-05 07:36:29'),
(42, 12, 'Compliance', 0, 4, '2026-05-05 07:36:29'),
(43, 13, 'Script Kiddie', 0, 1, '2026-05-05 07:36:29'),
(44, 13, 'Cybercriminal', 0, 2, '2026-05-05 07:36:29'),
(45, 13, 'System Administrator', 1, 3, '2026-05-05 07:36:29'),
(46, 13, 'State-Sponsored Actor', 0, 4, '2026-05-05 07:36:29'),
(47, 14, 'Threat', 0, 1, '2026-05-05 07:36:29'),
(48, 14, 'Risk', 0, 2, '2026-05-05 07:36:29'),
(49, 14, 'Vulnerability', 1, 3, '2026-05-05 07:36:29'),
(50, 14, 'Exploit', 0, 4, '2026-05-05 07:36:29'),
(51, 15, 'Political change', 0, 1, '2026-05-05 07:36:29'),
(52, 15, 'Social justice', 0, 2, '2026-05-05 07:36:29'),
(53, 15, 'Financial gain', 1, 3, '2026-05-05 07:36:29'),
(54, 15, 'Personal recognition', 0, 4, '2026-05-05 07:36:29'),
(55, 16, 'Confidentiality', 0, 1, '2026-05-05 07:36:29'),
(56, 16, 'Integrity', 0, 2, '2026-05-05 07:36:29'),
(57, 16, 'Availability', 1, 3, '2026-05-05 07:36:29'),
(58, 16, 'Authentication', 0, 4, '2026-05-05 07:36:29'),
(59, 17, 'Layer 2', 0, 1, '2026-05-05 07:36:29'),
(60, 17, 'Layer 3', 1, 2, '2026-05-05 07:36:29'),
(61, 17, 'Layer 4', 0, 3, '2026-05-05 07:36:29'),
(62, 17, 'Layer 7', 0, 4, '2026-05-05 07:36:29'),
(63, 18, 'HTTP', 0, 1, '2026-05-05 07:36:29'),
(64, 18, 'FTP', 0, 2, '2026-05-05 07:36:29'),
(65, 18, 'HTTPS', 1, 3, '2026-05-05 07:36:29'),
(66, 18, 'SSH', 0, 4, '2026-05-05 07:36:29'),
(67, 19, 'Address Resolution Protocol', 1, 1, '2026-05-05 07:36:29'),
(68, 19, 'Advanced Routing Protocol', 0, 2, '2026-05-05 07:36:29'),
(69, 19, 'Application Resource Protocol', 0, 3, '2026-05-05 07:36:29'),
(70, 19, 'Automatic Response Procedure', 0, 4, '2026-05-05 07:36:29'),
(71, 20, 'FTP', 0, 1, '2026-05-05 07:36:29'),
(72, 20, 'SSH', 1, 2, '2026-05-05 07:36:29'),
(73, 20, 'HTTP', 0, 3, '2026-05-05 07:36:29'),
(74, 20, 'SMTP', 0, 4, '2026-05-05 07:36:29'),
(75, 21, 'Increasing internet speed', 0, 1, '2026-05-05 07:36:29'),
(76, 21, 'Limiting attack spread', 1, 2, '2026-05-05 07:36:29'),
(77, 21, 'Reducing cable costs', 0, 3, '2026-05-05 07:36:29'),
(78, 21, 'Improving wireless signal', 0, 4, '2026-05-05 07:36:29'),
(79, 22, 'Virus', 0, 1, '2026-05-05 07:36:29'),
(80, 22, 'Worm', 0, 2, '2026-05-05 07:36:29'),
(81, 22, 'Ransomware', 1, 3, '2026-05-05 07:36:29'),
(82, 22, 'Spyware', 0, 4, '2026-05-05 07:36:29'),
(83, 23, 'A network scanning technique', 0, 1, '2026-05-05 07:36:29'),
(84, 23, 'A fraudulent attempt to obtain sensitive information', 1, 2, '2026-05-05 07:36:29'),
(85, 23, 'A type of firewall', 0, 3, '2026-05-05 07:36:29'),
(86, 23, 'A password hashing method', 0, 4, '2026-05-05 07:36:29'),
(87, 24, 'Reconnaissance', 0, 1, '2026-05-05 07:36:29'),
(88, 24, 'Weaponization', 0, 2, '2026-05-05 07:36:29'),
(89, 24, 'Exploitation', 1, 3, '2026-05-05 07:36:29'),
(90, 24, 'Installation', 0, 4, '2026-05-05 07:36:29'),
(91, 25, 'SQL Injection', 0, 1, '2026-05-05 07:36:29'),
(92, 25, 'Tailgating', 1, 2, '2026-05-05 07:36:29'),
(93, 25, 'Buffer Overflow', 0, 3, '2026-05-05 07:36:29'),
(94, 25, 'ARP Spoofing', 0, 4, '2026-05-05 07:36:29'),
(95, 26, 'Encrypt files', 0, 1, '2026-05-05 07:36:29'),
(96, 26, 'Record keystrokes', 1, 2, '2026-05-05 07:36:29'),
(97, 26, 'Scan networks', 0, 3, '2026-05-05 07:36:29'),
(98, 26, 'Block websites', 0, 4, '2026-05-05 07:36:29'),
(99, 27, 'Asymmetric', 0, 1, '2026-05-05 07:36:29'),
(100, 27, 'Symmetric', 1, 2, '2026-05-05 07:36:29'),
(101, 27, 'Hashing', 0, 3, '2026-05-05 07:36:29'),
(102, 27, 'Public-key', 0, 4, '2026-05-05 07:36:29'),
(103, 28, 'Multi-Factor Authentication', 1, 1, '2026-05-05 07:36:29'),
(104, 28, 'Managed Firewall Access', 0, 2, '2026-05-05 07:36:29'),
(105, 28, 'Multi-Function Application', 0, 3, '2026-05-05 07:36:29'),
(106, 28, 'Modular Framework Architecture', 0, 4, '2026-05-05 07:36:29'),
(107, 29, 'User attributes', 0, 1, '2026-05-05 07:36:29'),
(108, 29, 'Resource ownership', 0, 2, '2026-05-05 07:36:29'),
(109, 29, 'User roles', 1, 3, '2026-05-05 07:36:29'),
(110, 29, 'Security labels', 0, 4, '2026-05-05 07:36:29'),
(111, 30, 'IDS', 0, 1, '2026-05-05 07:36:29'),
(112, 30, 'IPS', 1, 2, '2026-05-05 07:36:29'),
(113, 30, 'Firewall', 0, 3, '2026-05-05 07:36:29'),
(114, 30, 'Proxy', 0, 4, '2026-05-05 07:36:29'),
(115, 31, 'File compression', 0, 1, '2026-05-05 07:36:29'),
(116, 31, 'Secure web communication', 1, 2, '2026-05-05 07:36:29'),
(117, 31, 'Database indexing', 0, 3, '2026-05-05 07:36:29'),
(118, 31, 'Email routing', 0, 4, '2026-05-05 07:36:29'),
(119, 32, 'Broken Access Control', 0, 1, '2026-05-05 07:36:29'),
(120, 32, 'Cryptographic Failures', 0, 2, '2026-05-05 07:36:29'),
(121, 32, 'Injection', 1, 3, '2026-05-05 07:36:29'),
(122, 32, 'Insecure Design', 0, 4, '2026-05-05 07:36:29'),
(123, 33, 'Input validation only', 0, 1, '2026-05-05 07:36:29'),
(124, 33, 'Parameterized queries', 1, 2, '2026-05-05 07:36:29'),
(125, 33, 'Firewall rules', 0, 3, '2026-05-05 07:36:29'),
(126, 33, 'URL encoding', 0, 4, '2026-05-05 07:36:29'),
(127, 34, 'Reflected XSS', 0, 1, '2026-05-05 07:36:29'),
(128, 34, 'Stored XSS', 1, 2, '2026-05-05 07:36:29'),
(129, 34, 'DOM-based XSS', 0, 3, '2026-05-05 07:36:29'),
(130, 34, 'Blind XSS', 0, 4, '2026-05-05 07:36:29'),
(131, 35, 'Content-Security-Policy', 0, 1, '2026-05-05 07:36:29'),
(132, 35, 'X-Frame-Options', 1, 2, '2026-05-05 07:36:29'),
(133, 35, 'X-XSS-Protection', 0, 3, '2026-05-05 07:36:29'),
(134, 35, 'Strict-Transport-Security', 0, 4, '2026-05-05 07:36:29'),
(135, 36, 'Cross-Site Protection', 0, 1, '2026-05-05 07:36:29'),
(136, 36, 'Content Security Policy', 1, 2, '2026-05-05 07:36:29'),
(137, 36, 'Client-Side Protocol', 0, 3, '2026-05-05 07:36:29'),
(138, 36, 'Certificate Signing Process', 0, 4, '2026-05-05 07:36:29'),
(139, 37, 'Black Hat', 0, 1, '2026-05-05 07:36:29'),
(140, 37, 'White Hat', 1, 2, '2026-05-05 07:36:29'),
(141, 37, 'Gray Hat', 0, 3, '2026-05-05 07:36:29'),
(142, 37, 'Script Kiddie', 0, 4, '2026-05-05 07:36:29'),
(143, 38, 'Open Source Intelligence', 1, 1, '2026-05-05 07:36:29'),
(144, 38, 'Operating System Integration', 0, 2, '2026-05-05 07:36:29'),
(145, 38, 'Online Security Intelligence', 0, 3, '2026-05-05 07:36:29'),
(146, 38, 'Organizational Security Interface', 0, 4, '2026-05-05 07:36:29'),
(147, 39, 'Wireshark', 0, 1, '2026-05-05 07:36:29'),
(148, 39, 'Nmap', 1, 2, '2026-05-05 07:36:29'),
(149, 39, 'Metasploit', 0, 3, '2026-05-05 07:36:29'),
(150, 39, 'Burp Suite', 0, 4, '2026-05-05 07:36:29'),
(151, 40, 'Full knowledge', 0, 1, '2026-05-05 07:36:29'),
(152, 40, 'No prior knowledge', 1, 2, '2026-05-05 07:36:29'),
(153, 40, 'Partial knowledge', 0, 3, '2026-05-05 07:36:29'),
(154, 40, 'Source code access', 0, 4, '2026-05-05 07:36:29'),
(155, 41, 'Common Vulnerability Enumeration', 0, 1, '2026-05-05 07:36:29'),
(156, 41, 'Common Vulnerabilities and Exposures', 1, 2, '2026-05-05 07:36:29'),
(157, 41, 'Critical Vulnerability Entry', 0, 3, '2026-05-05 07:36:29'),
(158, 41, 'Computer Virus Encyclopedia', 0, 4, '2026-05-05 07:36:29'),
(159, 42, 'Containment', 0, 1, '2026-05-05 07:36:29'),
(160, 42, 'Eradication', 1, 2, '2026-05-05 07:36:29'),
(161, 42, 'Recovery', 0, 3, '2026-05-05 07:36:29'),
(162, 42, 'Detection', 0, 4, '2026-05-05 07:36:29'),
(163, 43, 'Speed up investigation', 0, 1, '2026-05-05 07:36:29'),
(164, 43, 'Ensure evidence admissibility', 1, 2, '2026-05-05 07:36:29'),
(165, 43, 'Reduce costs', 0, 3, '2026-05-05 07:36:29'),
(166, 43, 'Identify attackers', 0, 4, '2026-05-05 07:36:29'),
(167, 44, 'Nmap', 0, 1, '2026-05-05 07:36:29'),
(168, 44, 'Volatility', 1, 2, '2026-05-05 07:36:29'),
(169, 44, 'Wireshark', 0, 3, '2026-05-05 07:36:29'),
(170, 44, 'Nessus', 0, 4, '2026-05-05 07:36:29'),
(171, 45, 'Format affected systems', 0, 1, '2026-05-05 07:36:29'),
(172, 45, 'Preserve evidence', 1, 2, '2026-05-05 07:36:29'),
(173, 45, 'Notify the media', 0, 3, '2026-05-05 07:36:29'),
(174, 45, 'Update antivirus', 0, 4, '2026-05-05 07:36:29'),
(175, 46, 'Snapshot', 0, 1, '2026-05-05 07:36:29'),
(176, 46, 'Disk Image', 1, 2, '2026-05-05 07:36:29'),
(177, 46, 'Backup', 0, 3, '2026-05-05 07:36:29'),
(178, 46, 'Archive', 0, 4, '2026-05-05 07:36:29'),
(179, 47, 'Security Information and Event Management', 1, 1, '2026-05-05 07:36:29'),
(180, 47, 'System Intelligence and Event Monitoring', 0, 2, '2026-05-05 07:36:29'),
(181, 47, 'Secure Internet and Email Management', 0, 3, '2026-05-05 07:36:29'),
(182, 47, 'System Integration and Enterprise Monitoring', 0, 4, '2026-05-05 07:36:29'),
(183, 48, 'Tier 1', 1, 1, '2026-05-05 07:36:29'),
(184, 48, 'Tier 2', 0, 2, '2026-05-05 07:36:29'),
(185, 48, 'Tier 3', 0, 3, '2026-05-05 07:36:29'),
(186, 48, 'Tier 4', 0, 4, '2026-05-05 07:36:29'),
(187, 49, 'Mean Time to Detect', 1, 1, '2026-05-05 07:36:29'),
(188, 49, 'Maximum Time to Detection', 0, 2, '2026-05-05 07:36:29'),
(189, 49, 'Minimum Technical Threat Duration', 0, 3, '2026-05-05 07:36:29'),
(190, 49, 'Managed Threat Transfer Delay', 0, 4, '2026-05-05 07:36:29'),
(191, 50, 'Splunk', 0, 1, '2026-05-05 07:36:29'),
(192, 50, 'QRadar', 0, 2, '2026-05-05 07:36:29'),
(193, 50, 'Wazuh', 1, 3, '2026-05-05 07:36:29'),
(194, 50, 'Microsoft Sentinel', 0, 4, '2026-05-05 07:36:29'),
(195, 51, 'Network routing', 0, 1, '2026-05-05 07:36:29'),
(196, 51, 'Threat modeling and detection', 1, 2, '2026-05-05 07:36:29'),
(197, 51, 'Password management', 0, 3, '2026-05-05 07:36:29'),
(198, 51, 'Data encryption', 0, 4, '2026-05-05 07:36:29'),
(199, 52, '3', 0, 1, '2026-05-05 07:36:29'),
(200, 52, '4', 0, 2, '2026-05-05 07:36:29'),
(201, 52, '5', 1, 3, '2026-05-05 07:36:29'),
(202, 52, '6', 0, 4, '2026-05-05 07:36:29'),
(203, 53, 'Payment processing', 0, 1, '2026-05-05 07:36:29'),
(204, 53, 'Information security management', 1, 2, '2026-05-05 07:36:29'),
(205, 53, 'Network routing', 0, 3, '2026-05-05 07:36:29'),
(206, 53, 'Software development', 0, 4, '2026-05-05 07:36:29'),
(207, 54, 'Threat + Vulnerability', 0, 1, '2026-05-05 07:36:29'),
(208, 54, 'Likelihood x Impact', 1, 2, '2026-05-05 07:36:29'),
(209, 54, 'Asset Value - Cost', 0, 3, '2026-05-05 07:36:29'),
(210, 54, 'Threat - Control', 0, 4, '2026-05-05 07:36:29'),
(211, 55, 'Accept', 0, 1, '2026-05-05 07:36:29'),
(212, 55, 'Mitigate', 0, 2, '2026-05-05 07:36:29'),
(213, 55, 'Transfer', 1, 3, '2026-05-05 07:36:29'),
(214, 55, 'Avoid', 0, 4, '2026-05-05 07:36:29'),
(215, 56, 'Identify', 0, 1, '2026-05-05 07:36:29'),
(216, 56, 'Protect', 0, 2, '2026-05-05 07:36:29'),
(217, 56, 'Detect', 0, 3, '2026-05-05 07:36:29'),
(218, 56, 'Respond', 1, 4, '2026-05-05 07:36:29'),
(219, 57, 'Control, Integrity, Authentication', 0, 1, '2026-05-05 07:36:29'),
(220, 57, 'Confidentiality, Integrity, Availability', 1, 2, '2026-05-05 07:36:29'),
(221, 57, 'Certification, Implementation, Assessment', 0, 3, '2026-05-05 07:36:29'),
(222, 57, 'Compliance, Investigation, Analysis', 0, 4, '2026-05-05 07:36:29'),
(223, 58, 'Data Link', 0, 1, '2026-05-05 07:36:29'),
(224, 58, 'Network', 1, 2, '2026-05-05 07:36:29'),
(225, 58, 'Transport', 0, 3, '2026-05-05 07:36:29'),
(226, 58, 'Application', 0, 4, '2026-05-05 07:36:29'),
(227, 59, 'Virus', 0, 1, '2026-05-05 07:36:29'),
(228, 59, 'Worm', 1, 2, '2026-05-05 07:36:29'),
(229, 59, 'Trojan', 0, 3, '2026-05-05 07:36:29'),
(230, 59, 'Rootkit', 0, 4, '2026-05-05 07:36:29'),
(231, 60, 'RSA', 0, 1, '2026-05-05 07:36:29'),
(232, 60, 'AES', 1, 2, '2026-05-05 07:36:29'),
(233, 60, 'ECC', 0, 3, '2026-05-05 07:36:29'),
(234, 60, 'DSA', 0, 4, '2026-05-05 07:36:29'),
(235, 61, 'Input validation', 0, 1, '2026-05-05 07:36:29'),
(236, 61, 'Parameterized queries', 1, 2, '2026-05-05 07:36:29'),
(237, 61, 'Web Application Firewall', 0, 3, '2026-05-05 07:36:29'),
(238, 61, 'URL encoding', 0, 4, '2026-05-05 07:36:29'),
(239, 62, 'Full knowledge of systems', 0, 1, '2026-05-05 07:36:29'),
(240, 62, 'No prior knowledge', 1, 2, '2026-05-05 07:36:29'),
(241, 62, 'Source code access', 0, 3, '2026-05-05 07:36:29'),
(242, 62, 'Administrator credentials', 0, 4, '2026-05-05 07:36:29'),
(243, 63, 'Detection', 0, 1, '2026-05-05 07:36:29'),
(244, 63, 'Containment', 1, 2, '2026-05-05 07:36:29'),
(245, 63, 'Eradication', 0, 3, '2026-05-05 07:36:29'),
(246, 63, 'Recovery', 0, 4, '2026-05-05 07:36:29'),
(247, 64, 'Encrypt network traffic', 0, 1, '2026-05-05 07:36:29'),
(248, 64, 'Collect and analyze security events', 1, 2, '2026-05-05 07:36:29'),
(249, 64, 'Block malicious websites', 0, 3, '2026-05-05 07:36:29'),
(250, 64, 'Manage user passwords', 0, 4, '2026-05-05 07:36:29'),
(251, 65, '3', 0, 1, '2026-05-05 07:36:29'),
(252, 65, '4', 0, 2, '2026-05-05 07:36:29'),
(253, 65, '5', 1, 3, '2026-05-05 07:36:29'),
(254, 65, '6', 0, 4, '2026-05-05 07:36:29'),
(255, 66, 'DAC', 0, 1, '2026-05-05 07:36:29'),
(256, 66, 'MAC', 0, 2, '2026-05-05 07:36:29'),
(257, 66, 'RBAC', 1, 3, '2026-05-05 07:36:29'),
(258, 66, 'ABAC', 0, 4, '2026-05-05 07:36:29'),
(259, 67, 'Store backups', 0, 1, '2026-05-05 07:36:29'),
(260, 67, 'Attract and detect attackers', 1, 2, '2026-05-05 07:36:29'),
(261, 67, 'Encrypt data', 0, 3, '2026-05-05 07:36:29'),
(262, 67, 'Speed up networks', 0, 4, '2026-05-05 07:36:29'),
(263, 68, 'Phishing', 0, 1, '2026-05-05 07:36:29'),
(264, 68, 'Pretexting', 0, 2, '2026-05-05 07:36:29'),
(265, 68, 'Tailgating', 1, 3, '2026-05-05 07:36:29'),
(266, 68, 'Baiting', 0, 4, '2026-05-05 07:36:29'),
(267, 69, 'Compress files', 0, 1, '2026-05-05 07:36:29'),
(268, 69, 'Encrypt web traffic', 1, 2, '2026-05-05 07:36:29'),
(269, 69, 'Scan for viruses', 0, 3, '2026-05-05 07:36:29'),
(270, 69, 'Route packets', 0, 4, '2026-05-05 07:36:29'),
(271, 70, 'Open Web Application Security Project', 1, 1, '2026-05-05 07:36:29'),
(272, 70, 'Online Web Attack and Security Protocol', 0, 2, '2026-05-05 07:36:29'),
(273, 70, 'Operational Web Application Standard Procedure', 0, 3, '2026-05-05 07:36:29'),
(274, 70, 'Open Web Authentication Security Program', 0, 4, '2026-05-05 07:36:29'),
(275, 71, 'Threat + Vulnerability', 0, 1, '2026-05-05 07:36:29'),
(276, 71, 'Likelihood x Impact', 1, 2, '2026-05-05 07:36:29'),
(277, 71, 'Asset x Control', 0, 3, '2026-05-05 07:36:29'),
(278, 71, 'Cost + Benefit', 0, 4, '2026-05-05 07:36:29');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `identifier` varchar(64) NOT NULL,
  `attempt_count` int(11) NOT NULL DEFAULT 1,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `identifier`, `attempt_count`, `expires_at`) VALUES
(9, 'd1a8361d33008069afbfc8d68c5602fa', 1, '2026-05-15 20:18:11');

-- --------------------------------------------------------

--
-- Table structure for table `registration_fees`
--

CREATE TABLE `registration_fees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 150.00,
  `currency` varchar(3) NOT NULL DEFAULT 'ZMW',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_method` enum('bank_transfer','bank_deposit','mobile_money') NOT NULL DEFAULT 'bank_deposit',
  `lenco_transaction_id` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `mobile_provider` varchar(20) DEFAULT NULL,
  `mobile_reference` varchar(100) DEFAULT NULL,
  `bank_reference` varchar(100) DEFAULT NULL COMMENT 'Bank deposit slip or transfer reference',
  `bank_name` varchar(100) DEFAULT NULL,
  `deposit_date` date DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL COMMENT 'Mobile money phone number',
  `verified_by` int(11) DEFAULT NULL COMMENT 'Admin/Finance user who verified the payment',
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `registration_fees`
--

INSERT INTO `registration_fees` (`id`, `user_id`, `student_id`, `amount`, `currency`, `payment_status`, `payment_method`, `lenco_transaction_id`, `reference`, `mobile_provider`, `mobile_reference`, `bank_reference`, `bank_name`, `deposit_date`, `phone_number`, `verified_by`, `verified_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 39, 33, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'REG-039-001', NULL, '2025-12-28', NULL, 1, '2025-12-28 18:42:19', 'Registration fee payment for Given Mutwena', '2025-12-28 18:42:19', '2025-12-28 18:42:19'),
(2, 43, 37, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09', NULL, 1, NULL, 'Registration fee paid - Betty', '2026-01-09 04:40:44', '2026-01-09 04:40:44'),
(3, 40, 34, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09', NULL, 1, NULL, 'Registration fee paid - Sharon', '2026-01-09 04:40:44', '2026-01-09 04:40:44'),
(4, 43, 37, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09', NULL, 1, NULL, 'Registration fee paid - Betty', '2026-01-09 04:42:00', '2026-01-09 04:42:00'),
(5, 40, 34, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09', NULL, 1, NULL, 'Registration fee paid - Sharon', '2026-01-09 04:42:00', '2026-01-09 04:42:00'),
(6, 68, 62, 100.00, 'ZMW', 'completed', 'mobile_money', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-16', NULL, 1, '2026-03-16 23:11:21', NULL, '2026-03-16 21:11:21', '2026-03-16 21:11:21'),
(7, 56, 50, 100.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17', NULL, 1, '2026-03-17 16:35:41', NULL, '2026-03-17 14:35:41', '2026-03-17 14:35:41'),
(8, 78, 72, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(9, 83, 77, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(10, 77, 71, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(11, 79, 73, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(12, 85, 79, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(13, 86, 80, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(14, 84, 78, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(15, 87, 81, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(16, 80, 74, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(17, 81, 75, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(18, 82, 76, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(19, 88, 82, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, 'IMPORTED', 'Admin Import', NULL, NULL, 1, '2026-05-08 15:36:43', 'Auto-imported for Microsoft Office graduates', '2026-05-08 15:36:43', '2026-05-08 15:36:43'),
(20, 90, 84, 100.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-13', NULL, 1, '2026-05-13 15:15:03', NULL, '2026-05-13 13:15:03', '2026-05-13 13:15:03'),
(21, 1, NULL, 150.00, 'ZMW', 'pending', 'mobile_money', NULL, NULL, 'airtel', NULL, NULL, NULL, NULL, '+260900000000', NULL, NULL, NULL, '2026-05-25 12:36:24', '2026-05-25 12:36:24');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `permissions`, `created_at`) VALUES
(1, 'Super Admin', 'Full system access and control', '{\"all\": true}', '2025-11-18 22:21:01'),
(2, 'Admin', 'Administrative access to manage system', '{\"users\": [\"create\", \"read\", \"update\", \"delete\"], \"courses\": [\"create\", \"read\", \"update\", \"delete\"], \"reports\": [\"read\"]}', '2025-11-18 22:21:01'),
(3, 'Instructor', 'Can create and manage courses', '{\"courses\": [\"create\", \"read\", \"update\"], \"students\": [\"read\"], \"grades\": [\"create\", \"update\"]}', '2025-11-18 22:21:01'),
(4, 'Student', 'Can enroll and access courses', '{\"courses\": [\"read\", \"enroll\"], \"assignments\": [\"submit\"], \"quizzes\": [\"take\"]}', '2025-11-18 22:21:01'),
(5, 'Content Creator', 'Can create course content', '{\"courses\": [\"create\", \"read\", \"update\"], \"content\": [\"create\", \"update\"]}', '2025-11-18 22:21:01'),
(6, 'Finance', 'Financial operations and cash payment management', '{\"payments\": [\"create\", \"read\", \"update\"], \"students\": [\"read\"], \"enrollments\": [\"read\"], \"reports\": [\"read\"]}', '2025-11-24 11:56:11');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `type` varchar(255) NOT NULL DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'Edutrack Computer Training College', 'general', 'string', '2026-05-22 18:36:54', '2026-05-22 18:36:54'),
(2, 'app_email', 'edutrackzambia@gmail.com', 'general', 'string', '2026-05-22 18:36:54', '2026-05-22 18:36:54'),
(3, 'app_phone', '+260 770 666 937 / +260 965 992 967', 'general', 'string', '2026-05-22 18:36:54', '2026-05-22 18:36:54'),
(4, 'app_address', 'Kalomo, Zambia', 'general', 'string', '2026-05-22 18:36:54', '2026-05-22 18:36:54'),
(5, 'currency', 'ZMW', 'general', 'string', '2026-05-22 18:36:54', '2026-05-25 12:15:01'),
(6, 'registration_fee', '150', 'payment', 'float', '2026-05-22 18:36:54', '2026-05-22 18:36:54'),
(7, 'min_deposit_percent', '30', 'payment', 'integer', '2026-05-22 18:36:54', '2026-05-22 18:36:54'),
(8, 'certificate_enabled', '1', 'certificate', 'boolean', '2026-05-22 18:36:54', '2026-05-25 12:15:46'),
(9, 'logo_url', '', 'general', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(10, 'social_facebook', '', 'social', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(11, 'social_twitter', '', 'social', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(12, 'social_linkedin', '', 'social', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(13, 'social_instagram', '', 'social', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(14, 'social_youtube', '', 'social', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(15, 'social_whatsapp', '', 'social', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(16, 'meta_description', '', 'seo', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(17, 'meta_keywords', '', 'seo', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(18, 'footer_about', '', 'content', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(19, 'hero_title', '', 'homepage', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(20, 'hero_subtitle', '', 'homepage', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(21, 'next_intake_date', '', 'homepage', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(22, 'next_intake_label', '', 'homepage', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(23, 'opening_hours', 'Monday - Friday: 8:00 AM - 5:00 PM', 'homepage', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(24, 'bank_name', '', 'payment', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(25, 'bank_account_name', '', 'payment', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(26, 'bank_account_number', '', 'payment', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01'),
(27, 'maintenance_mode', '0', 'system', 'boolean', '2026-05-25 12:15:01', '2026-05-25 12:15:46'),
(28, 'enable_email_notifications', '1', 'system', 'boolean', '2026-05-25 12:15:01', '2026-05-25 12:15:46'),
(29, 'google_login_enabled', '1', 'system', 'boolean', '2026-05-25 12:15:01', '2026-05-25 12:15:46'),
(30, 'lenco_enabled', '1', 'system', 'boolean', '2026-05-25 12:15:01', '2026-05-25 12:15:46'),
(31, 'registration_fee_required', '1', 'payment', 'boolean', '2026-05-25 12:15:01', '2026-05-25 12:15:46'),
(32, 'teveta_registration_number', '', 'certificate', 'string', '2026-05-25 12:15:01', '2026-05-25 12:15:01');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `enrollment_date` date NOT NULL,
  `total_courses_enrolled` int(11) DEFAULT 0,
  `total_courses_completed` int(11) DEFAULT 0,
  `total_certificates` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `date_of_birth`, `gender`, `address`, `city`, `country`, `postal_code`, `enrollment_date`, `total_courses_enrolled`, `total_courses_completed`, `total_certificates`, `created_at`, `updated_at`) VALUES
(1, 8, '1998-05-15', 'Male', NULL, 'Lusaka', 'Zambia', NULL, '2024-12-01', 3, 0, 1, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(2, 9, '2000-08-22', 'Female', NULL, 'Ndola', 'Zambia', NULL, '2024-12-05', 3, 0, 1, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(3, 10, '1995-03-10', 'Male', NULL, 'Kitwe', 'Zambia', NULL, '2024-12-10', 3, 0, 2, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(4, 11, '1999-11-30', 'Female', NULL, 'Livingstone', 'Zambia', NULL, '2024-12-15', 3, 0, 1, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(5, 12, '1997-07-18', 'Male', NULL, 'Lusaka', 'Zambia', NULL, '2024-12-20', 2, 0, 0, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(6, 13, '2001-02-25', 'Female', NULL, 'Kabwe', 'Zambia', NULL, '2025-01-02', 2, 0, 0, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(7, 14, '1996-09-12', 'Male', NULL, 'Chingola', 'Zambia', NULL, '2025-01-05', 2, 0, 0, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(8, 15, '1998-12-05', 'Female', NULL, 'Lusaka', 'Zambia', NULL, '2025-01-08', 2, 0, 1, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(9, 16, '2000-04-20', 'Male', NULL, 'Solwezi', 'Zambia', NULL, '2025-01-10', 2, 0, 0, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(10, 17, '1999-06-08', 'Female', NULL, 'Mongu', 'Zambia', NULL, '2025-01-12', 2, 0, 0, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(11, 18, '1997-10-15', 'Male', NULL, 'Kasama', 'Zambia', NULL, '2025-01-15', 2, 0, 0, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(12, 19, '2001-01-30', 'Female', NULL, 'Lusaka', 'Zambia', NULL, '2025-01-18', 2, 0, 0, '2025-11-18 22:21:01', '2026-06-08 11:06:40'),
(13, 26, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-23', 1, 0, 0, '2025-11-23 11:45:50', '2026-06-08 11:06:40'),
(14, 30, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-09', 0, 0, 0, '2025-12-09 11:30:29', '2025-12-09 11:30:29'),
(28, 34, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20', 0, 0, 0, '2025-12-20 13:21:55', '2025-12-20 13:21:55'),
(29, 35, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21', 0, 0, 0, '2025-12-21 12:57:29', '2025-12-21 12:57:29'),
(30, 36, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-22', 0, 0, 0, '2025-12-22 16:20:20', '2025-12-22 16:20:20'),
(31, 37, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-23', 0, 0, 0, '2025-12-23 07:18:25', '2025-12-23 07:18:25'),
(32, 38, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-25', 0, 0, 0, '2025-12-25 10:05:01', '2025-12-25 10:05:01'),
(33, 39, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-28', 1, 0, 0, '2025-12-28 15:13:44', '2026-06-08 11:06:40'),
(34, 40, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-29', 1, 0, 0, '2025-12-29 10:01:21', '2026-06-08 11:06:40'),
(35, 41, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-30', 0, 0, 0, '2025-12-30 12:45:53', '2025-12-30 12:45:53'),
(36, 42, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-31', 0, 0, 0, '2025-12-31 09:03:33', '2025-12-31 09:03:33'),
(37, 43, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08', 1, 0, 0, '2026-01-08 10:47:56', '2026-06-08 11:06:40'),
(38, 44, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-11', 0, 0, 0, '2026-01-11 17:02:07', '2026-01-11 17:02:07'),
(39, 45, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13', 0, 0, 0, '2026-01-13 16:02:31', '2026-01-13 16:02:31'),
(40, 46, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13', 0, 0, 0, '2026-01-13 16:04:02', '2026-01-13 16:04:02'),
(41, 47, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-21', 0, 0, 0, '2026-01-21 20:00:01', '2026-01-21 20:00:01'),
(42, 48, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-04', 0, 0, 0, '2026-02-04 12:42:22', '2026-02-04 12:42:22'),
(43, 49, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-14', 0, 0, 0, '2026-02-14 02:09:56', '2026-02-14 02:09:56'),
(44, 50, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-14', 0, 0, 0, '2026-02-14 09:01:12', '2026-02-14 09:01:12'),
(45, 51, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-17', 0, 0, 0, '2026-02-17 18:48:45', '2026-02-17 18:48:45'),
(46, 52, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18', 0, 0, 0, '2026-02-18 09:13:07', '2026-02-18 09:13:07'),
(47, 53, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20', 0, 0, 0, '2026-02-20 11:11:55', '2026-02-20 11:11:55'),
(48, 54, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22', 0, 0, 0, '2026-02-22 17:15:58', '2026-02-22 17:15:58'),
(49, 55, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-23', 0, 0, 0, '2026-02-23 10:27:32', '2026-02-23 10:27:32'),
(50, 56, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-25', 1, 0, 0, '2026-02-25 18:15:00', '2026-06-08 11:06:40'),
(51, 57, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-26', 0, 0, 0, '2026-02-26 20:06:15', '2026-02-26 20:06:15'),
(52, 58, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-02', 0, 0, 0, '2026-03-02 22:26:34', '2026-03-02 22:26:34'),
(53, 59, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-04', 0, 0, 0, '2026-03-04 09:22:55', '2026-03-04 09:22:55'),
(54, 60, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-04', 0, 0, 0, '2026-03-04 09:48:00', '2026-03-04 09:48:00'),
(55, 61, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-04', 0, 0, 0, '2026-03-04 14:21:12', '2026-03-04 14:21:12'),
(56, 62, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-06', 0, 0, 0, '2026-03-06 20:26:45', '2026-03-06 20:26:45'),
(57, 63, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-07', 0, 0, 0, '2026-03-07 12:28:33', '2026-03-07 12:28:33'),
(58, 64, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-07', 0, 0, 0, '2026-03-07 12:56:28', '2026-03-07 12:56:28'),
(59, 65, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-13', 0, 0, 0, '2026-03-13 09:46:59', '2026-03-13 09:46:59'),
(60, 66, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14', 0, 0, 0, '2026-03-14 12:46:43', '2026-03-14 12:46:43'),
(61, 67, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15', 0, 0, 0, '2026-03-15 13:13:55', '2026-03-15 13:13:55'),
(62, 68, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-16', 1, 0, 0, '2026-03-16 12:45:20', '2026-06-08 11:06:40'),
(63, 69, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-20', 0, 0, 0, '2026-03-20 10:44:56', '2026-03-20 10:44:56'),
(64, 70, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26', 0, 0, 0, '2026-03-26 11:30:42', '2026-03-26 11:30:42'),
(65, 71, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-29', 0, 0, 0, '2026-03-29 09:00:10', '2026-03-29 09:00:10'),
(66, 72, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12', 0, 0, 0, '2026-04-12 11:27:02', '2026-04-12 11:27:02'),
(67, 73, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 0, 0, 0, '2026-04-15 10:21:59', '2026-04-15 10:21:59'),
(68, 74, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-23', 0, 0, 0, '2026-04-23 08:10:47', '2026-04-23 08:10:47'),
(69, 75, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-26', 0, 0, 0, '2026-04-26 04:56:43', '2026-04-26 04:56:43'),
(70, 76, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-30', 0, 0, 0, '2026-04-30 12:33:28', '2026-04-30 12:33:28'),
(71, 77, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:11:18', '2026-06-08 11:06:40'),
(72, 78, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:12:26', '2026-06-08 11:06:40'),
(73, 79, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:16:52', '2026-06-08 11:06:40'),
(74, 80, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:16:59', '2026-06-08 11:06:40'),
(75, 81, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:19:00', '2026-06-08 11:06:40'),
(76, 82, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:19:44', '2026-06-08 11:06:40'),
(77, 83, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:22:17', '2026-06-08 11:06:40'),
(78, 84, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:24:57', '2026-06-08 11:06:40'),
(79, 85, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:38:39', '2026-06-08 11:06:40'),
(80, 86, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 12:46:55', '2026-06-08 11:06:40'),
(81, 87, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 1, 1, 1, '2026-05-08 14:47:43', '2026-06-08 11:06:40'),
(82, 88, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08', 6, 1, 1, '2026-05-08 15:20:22', '2026-06-08 11:06:40'),
(83, 89, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-11', 0, 0, 0, '2026-05-11 09:41:30', '2026-05-11 09:41:30'),
(84, 90, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-12', 1, 0, 0, '2026-05-12 16:26:41', '2026-06-08 11:06:40'),
(85, 91, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-23', 1, 0, 0, '2026-05-23 11:41:01', '2026-06-08 11:06:40');

-- --------------------------------------------------------

--
-- Table structure for table `student_achievements`
--

CREATE TABLE `student_achievements` (
  `achievement_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `earned_date` date NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_achievements`
--

INSERT INTO `student_achievements` (`achievement_id`, `student_id`, `badge_id`, `course_id`, `earned_date`, `description`) VALUES
(1, 1, 1, 1, '2025-04-10', 'Completed Certificate in Microsoft Office Suite'),
(2, 1, 3, 1, '2025-02-14', 'Submitted Python calculator assignment early'),
(3, 4, 1, 5, '2025-04-05', 'Completed Certificate in Python Programming'),
(4, 4, 2, 5, '2025-01-27', 'Perfect score on Python basics quiz (attempt 2)'),
(5, 2, 1, 7, '2025-04-25', 'Completed Certificate in Web Development'),
(6, 2, 3, 7, '2025-03-19', 'Submitted portfolio project early'),
(7, 3, 1, 18, '2025-04-08', 'Completed Certificate in Entrepreneurship'),
(8, 82, 1, 1, '2026-05-11', 'Completed first course'),
(9, 82, 2, 1, '2026-05-11', 'Scored 90% or above on final assessment'),
(10, 82, 3, NULL, '2026-05-13', 'Active community participant'),
(11, 82, 7, 1, '2026-05-13', 'Completed Certificate in Microsoft Office Suite');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('String','Number','Boolean','JSON') DEFAULT 'String',
  `description` text DEFAULT NULL,
  `is_editable` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_at`) VALUES
(1, 'site_name', 'Edutrack Computer Training College', 'String', 'Institution name', 1, '2026-05-22 18:36:54'),
(2, 'site_email', 'edutrackzambia@gmail.com', 'String', 'Primary contact email', 1, '2026-05-22 18:36:54'),
(3, 'max_file_upload_size', '10', 'Number', 'Maximum file upload size in MB', 1, '2025-11-18 22:21:01'),
(4, 'allow_student_discussion', 'true', 'Boolean', 'Allow students to create discussion topics', 1, '2025-11-18 22:21:01'),
(5, 'certificate_auto_generate', 'true', 'Boolean', 'Automatically generate certificates upon course completion', 1, '2025-11-18 22:21:01'),
(6, 'session_timeout_minutes', '30', 'Number', 'User session timeout in minutes', 1, '2025-11-18 22:21:01'),
(7, 'enable_email_notifications', '1', 'Boolean', 'Enable email notifications for users', 1, '2026-05-25 12:15:01'),
(8, 'platform_version', '1.0.0', 'String', 'Current platform version', 0, '2025-11-18 22:21:01'),
(9, 'maintenance_mode', '0', 'Boolean', 'Enable maintenance mode', 1, '2026-05-25 12:15:46'),
(10, 'default_currency', 'ZMW', 'String', 'Default currency', 1, '2026-05-22 18:36:54'),
(12, 'registration_fee_required', '1', 'Boolean', 'Whether registration fee is required before enrollment', 1, '2026-05-25 12:15:01'),
(13, 'bank_account_name', '', 'String', 'Bank account name for deposits', 1, '2026-05-25 12:15:01'),
(14, 'bank_account_number', '', 'String', 'Bank account number for deposits', 1, '2025-11-24 11:56:12'),
(15, 'bank_name', '', 'String', 'Bank name for deposits', 1, '2025-11-24 11:56:12'),
(16, 'bank_branch', '', 'String', 'Bank branch for deposits', 1, '2025-11-24 11:56:12'),
(17, 'currency', 'ZMW', 'String', 'Default currency (Zambian Kwacha)', 1, '2025-11-24 11:56:12'),
(18, 'partial_payments_enabled', 'true', 'Boolean', 'Allow partial payments for course fees', 1, '2025-11-24 11:56:12'),
(19, 'certificate_requires_full_payment', 'true', 'Boolean', 'Block certificate issuance until fully paid', 1, '2025-11-24 11:56:12'),
(20, 'enrollment_min_deposit_percent', '30', 'Number', 'Minimum percentage (0-100) of course fee required to unlock content', 1, '2025-12-09 13:21:21'),
(21, 'registration_fee_amount', '150', 'Number', 'Mandatory one-time registration fee amount', 1, '2025-12-09 13:21:21'),
(22, 'site_phone', '+260 770 666 937 / +260 965 992 967', 'String', 'Contact phone numbers', 1, '2026-05-22 18:36:54'),
(23, 'site_address', 'Kalomo, Zambia', 'String', 'Physical address', 1, '2026-05-22 18:36:54'),
(24, 'teveta_registration', 'TVA/2064', 'String', 'TEVETA registration number', 0, '2026-05-22 18:36:54'),
(25, 'next_intake_date', '', 'String', NULL, 1, '2026-05-25 12:15:01'),
(26, 'next_intake_label', '', 'String', NULL, 1, '2026-05-25 12:15:01'),
(27, 'google_login_enabled', '1', 'Boolean', NULL, 1, '2026-05-25 12:15:01'),
(28, 'lenco_enabled', '1', 'Boolean', NULL, 1, '2026-05-25 12:15:01'),
(29, 'teveta_registration_number', '', 'String', NULL, 1, '2026-05-25 12:15:01');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `qualifications` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `user_id`, `name`, `position`, `qualifications`, `image_url`, `display_order`, `created_at`) VALUES
(1, 27, 'Chilala Moonga', 'Principal & Director', 'MSc. Computer Science (DMI St. Eugene)\r\nB.ICT (The University of Zambia)\r\nDip. Education (The University of Zambia)', 'chilala.jpg', 1, '2025-12-06 09:00:22'),
(2, 29, 'Edward Musole', 'Vice Principal', 'Bachelor\'s Degree in Education (UNZA)\r\nAcademic Administration Specialist', 'edward.jpg', 2, '2025-12-06 09:00:22'),
(3, 6, 'Michael Siame', 'Head of ICT Department', 'B.ICT (Copperbelt University)\r\nStructural & Software Engineer', 'michael.jpg', 3, '2025-12-06 09:00:22'),
(4, 31, 'Anthony Nampute', 'Senior Lecturer', 'Dip. Computer Studies (UNZA)\r\nCertificate in English Language', 'anthony.jpg', 4, '2025-12-06 09:00:22'),
(5, 32, 'Inutu Simasiku', 'Admin & Procurement Officer', 'Dip. Registered Nursing\r\nCertificate in Marketing', 'inutu.jpg', 5, '2025-12-06 09:00:22'),
(6, 33, 'Nita Sichimwa', 'Student Support & Hygiene Officer', 'Nurse Assistant\r\nCert. Social Work & Community Development\r\nCertified Counselor', 'nita.jpg', 6, '2025-12-06 09:00:22');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `student_photo` varchar(255) DEFAULT NULL,
  `course_taken` varchar(255) NOT NULL,
  `graduation_year` int(11) DEFAULT NULL,
  `current_job_title` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `testimonial_text` text NOT NULL,
  `rating` int(11) DEFAULT 5,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_by` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `student_name`, `student_photo`, `course_taken`, `graduation_year`, `current_job_title`, `company`, `testimonial_text`, `rating`, `is_featured`, `status`, `submitted_by`, `user_id`, `course_id`, `enrollment_id`, `created_at`, `updated_at`) VALUES
(1, 'Test User', NULL, 'Certificate in Microsoft Office Suite', 2026, NULL, NULL, 'learnt alot and looking forward to applying my skills in the real world', 4, 1, 'approved', 88, 88, 1, 50, '2026-05-25 07:27:55', '2026-05-25 11:04:06');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `transaction_type` enum('Payment','Refund','Chargeback','Fee') DEFAULT 'Payment',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `gateway_response` text DEFAULT NULL,
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `payment_id`, `transaction_type`, `amount`, `currency`, `gateway_response`, `processed_at`) VALUES
(1, 1, 'Payment', 250.00, 'USD', NULL, '2025-01-15 06:30:15'),
(2, 2, 'Payment', 315.00, 'USD', NULL, '2025-01-15 06:35:22'),
(3, 3, 'Payment', 360.00, 'USD', NULL, '2025-01-20 08:00:45'),
(4, 4, 'Payment', 342.00, 'USD', NULL, '2025-01-15 07:00:18'),
(5, 5, 'Payment', 380.00, 'USD', NULL, '2025-01-15 07:10:33'),
(6, 6, 'Payment', 320.00, 'USD', NULL, '2025-01-20 09:30:27'),
(7, 7, 'Payment', 300.00, 'USD', NULL, '2025-01-10 12:00:51'),
(8, 8, 'Payment', 405.00, 'USD', NULL, '2025-02-01 08:15:39'),
(9, 9, 'Payment', 250.00, 'USD', NULL, '2025-01-15 10:00:12'),
(10, 10, 'Payment', 315.00, 'USD', NULL, '2025-01-10 06:00:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended','pending') DEFAULT 'active',
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `account_locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `google_id`, `password_hash`, `remember_token`, `first_name`, `last_name`, `phone`, `avatar_url`, `status`, `email_verification_token`, `email_verification_expires`, `email_verified`, `last_login`, `last_login_ip`, `failed_login_attempts`, `account_locked_until`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'admin@edutrackzambia.com', NULL, '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', NULL, 'System', 'Administrator', '+260900000000', NULL, 'active', NULL, NULL, 1, '2026-05-14 11:36:38', NULL, 0, NULL, '2025-11-18 22:21:01', '2026-05-22 19:15:06', NULL),
(6, 'michael.siame', 'michael.siame@edutrack.edu', NULL, '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', NULL, 'Michael', 'Siame', '+260933567890', NULL, 'active', NULL, NULL, 1, '2025-12-25 21:53:14', NULL, 0, NULL, '2025-11-18 22:21:01', '2025-12-25 19:53:14', NULL),
(25, 'taona', 'taona@gmail.com', NULL, '$2y$10$iJ4P8BDECzTdPhAwoP4pXOsf2rSZelFAfogVU6JCj2XfVdSVWHRlW', NULL, 'toana', 'ndlovuli', NULL, NULL, 'inactive', NULL, NULL, 0, NULL, NULL, 0, NULL, '2025-11-22 09:07:23', '2025-11-22 09:08:14', NULL),
(26, 'jaysiame076', 'jaysiame076@gmail.com', NULL, '$2y$10$QQ0Z4AD75f/2TyPP6zdrYebKdTkhnHo3IFuCz/AT07KQD.v7pWgei', NULL, 'joe', 'siame', '', NULL, 'active', NULL, NULL, 0, '2025-12-09 11:32:59', NULL, 0, NULL, '2025-11-23 11:05:46', '2025-12-09 09:32:59', NULL),
(27, 'marvinmoonga69', 'marvinmoonga69@gmail.com', NULL, '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', 't12AJUshqgSa8nxHrcIxvww6gSXFsdvJxEcrTk8i52AXQewQbqnLk1YtImXj', 'Chilala', 'Moonga', '+260979536820', NULL, 'active', NULL, NULL, 0, '2026-05-14 20:12:13', NULL, 0, NULL, '2025-12-04 20:34:20', '2026-06-05 11:25:21', NULL),
(28, 'it', 'it@witmanmiyande.com', NULL, '$2y$10$kbm0yafbxD0Iu0Vk7uZYoOKTaqE1DTV47I7FHEFmeRESglBLmnWve', NULL, 'Witman', 'Miyande', '+260976062621', NULL, 'active', NULL, NULL, 0, '2025-12-08 13:07:32', NULL, 0, NULL, '2025-12-05 14:38:37', '2025-12-08 17:45:29', NULL),
(29, 'edwardmusole76', 'edwardmusole76@gmail.com', NULL, '$2y$10$WAgkucanVQ4OuVJtxfZeIuH2gxPk4lH7tTmhKT0I8awfPWiBBakdC', NULL, 'Edward', 'Musole', '+260978605960', NULL, 'active', NULL, NULL, 0, '2025-12-29 12:47:56', NULL, 0, NULL, '2025-12-05 14:42:37', '2025-12-29 10:47:56', NULL),
(30, 'siamem570', 'siamem570@gmail.com', NULL, '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', NULL, 'michael', 'siame', '+260771216339', NULL, 'active', NULL, NULL, 0, '2026-03-16 22:27:06', NULL, 0, NULL, '2025-12-09 11:30:29', '2026-03-16 20:27:06', NULL),
(31, 'anthony.nampute', 'anthony.nampute@edutrack.edu', NULL, '$2y$10$uLoLKLK2Pcv08rJbNgPzhufsUVrhwhFR6IbSeGea6CKRuwU1NRbk.', NULL, 'Anthony', 'Nampute', NULL, NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2025-12-18 19:10:22', '2025-12-18 19:10:22', NULL),
(32, 'inutu.simasiku', 'inutu.simasiku@edutrack.edu', NULL, '$2y$10$uLoLKLK2Pcv08rJbNgPzhufsUVrhwhFR6IbSeGea6CKRuwU1NRbk.', NULL, 'Inutu', 'Simasiku', NULL, NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2025-12-18 19:10:22', '2025-12-18 19:10:22', NULL),
(33, 'nita.sichimwa', 'nita.sichimwa@edutrack.edu', NULL, '$2y$10$uLoLKLK2Pcv08rJbNgPzhufsUVrhwhFR6IbSeGea6CKRuwU1NRbk.', NULL, 'Nita', 'Sichimwa', NULL, NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2025-12-18 19:10:22', '2025-12-18 19:10:22', NULL),
(34, 'jilowahappy19', 'jilowahappy19@gmail.com', NULL, '$2y$10$BsJsXUusPmNBJmhfOOj15uzzlP.rFmjhhG.lKeZDTwcOzW62FFHne', NULL, 'Happy', 'Jilowa', '+260760054975', NULL, 'active', NULL, NULL, 0, '2025-12-20 15:22:34', NULL, 0, NULL, '2025-12-20 13:21:55', '2025-12-20 13:22:34', NULL),
(35, 'unparalleledtvstation2.0', 'unparalleledtvstation2.0@gmail.com', NULL, '$2y$10$Uc90KkySyrdk/ALa4SYkRuOybANz/FRsFVcAiLQjrUDgMkGdi9UsW', NULL, 'michael', 'siame', '+260771216339', NULL, 'active', NULL, NULL, 0, NULL, NULL, 0, NULL, '2025-12-21 12:57:29', '2025-12-21 12:57:29', NULL),
(36, 'moseschanda084', 'moseschanda084@gmail.com', NULL, '$2y$10$/COC.FYEkun5lNEdR7l1wePpKqi5dvtCRvr9nIXWBN3gZ6/QxZndK', NULL, 'Moses', 'Mulunda chanda', '+260971848021', NULL, 'active', NULL, NULL, 0, '2025-12-22 18:20:55', NULL, 0, NULL, '2025-12-22 16:20:20', '2025-12-22 16:20:55', NULL),
(37, 'sampayainnocent15', 'sampayainnocent15@gmail.com', NULL, '$2y$10$smRux7NyqHoywLOV0Djiy.0ydzMqz9JoAMS/5Nwg0RN0AYtA8qM2e', NULL, 'Innocent', 'Sampaya', '+260771717517', NULL, 'active', NULL, NULL, 0, '2025-12-23 09:19:00', NULL, 0, NULL, '2025-12-23 07:18:25', '2025-12-23 07:19:00', NULL),
(38, 'emmanuelgoma6', 'emmanuelgoma6@gmail.com', NULL, '$2y$10$G//nDjkNfzXuLGpLa6CnD.q0V4LOg4JYmwb.fJ5zq9UFz9vMWoNEa', NULL, 'Emmanuel', 'Goma', '+260967780685', NULL, 'active', NULL, NULL, 0, '2025-12-25 12:05:33', NULL, 0, NULL, '2025-12-25 10:05:01', '2025-12-25 10:05:33', NULL),
(39, 'givenmutwena60', 'givenmutwena60@gmail.com', NULL, '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', NULL, 'GIVEN', 'MUTWENA', '+260971077923', NULL, 'active', NULL, NULL, 0, '2026-02-18 03:47:53', NULL, 0, NULL, '2025-12-28 15:13:44', '2026-02-18 01:47:53', NULL),
(40, 'lumbongosharon', 'lumbongosharon@gmail.com', NULL, '$2y$10$z7ha9/pweOV5FhWUJOritOdYC4aYTWtZVxP6yjNGUxaTJTehx66JK', NULL, 'SHARON', 'LUMBONGO', '+260975854731', NULL, 'active', NULL, NULL, 0, '2026-01-10 20:40:08', NULL, 0, NULL, '2025-12-29 10:01:21', '2026-01-10 18:40:08', NULL),
(41, 'simanyangamooya', 'simanyangamooya@gmail.com', NULL, '$2y$10$TMW22nvE0bHFYMDnZfQ3nO9Ehb9rcUiFXLWOEBY5/1v5mtYDqKGlm', NULL, 'MOOYA', 'SIMANYANGA', '+260975803528', NULL, 'active', NULL, NULL, 0, '2025-12-30 14:46:43', NULL, 0, NULL, '2025-12-30 12:45:53', '2025-12-30 12:46:43', NULL),
(42, 'najibib465', 'najibib465@hudisk.com', NULL, '$2y$10$8FQVQC8clPVMiyGEeSJDGebcAlNa42BrNKG6dDGgR7QyTVbWFtA1m', NULL, 'Doe', 'John', '', NULL, 'active', NULL, NULL, 0, '2025-12-31 11:04:08', NULL, 0, NULL, '2025-12-31 09:03:33', '2025-12-31 09:04:08', NULL),
(43, 'bettylumbongo60', 'bettylumbongo60@gmail.com', NULL, '$2y$10$s1KFx4SOeeRUvJlV8XKxMOHzt9YqtJTTNRCzHaT00Lu9QSoW8wqXG', NULL, 'Betty', 'Lumbongo', '+260975179897', NULL, 'active', NULL, NULL, 0, '2026-03-25 08:16:28', NULL, 0, NULL, '2026-01-08 10:47:56', '2026-03-25 06:16:28', NULL),
(44, 'chikombichilobe', 'chikombichilobe@gmail.com', NULL, '$2y$10$ksBUfZYnFagnGCFAcCwyjet0bXfWLkipATUDlSXQ1zSSJc.cHBV86', NULL, 'Chilobe', 'Chikombi', '+260777615153', NULL, 'active', NULL, NULL, 0, NULL, NULL, 0, NULL, '2026-01-11 17:02:07', '2026-01-11 17:02:07', NULL),
(45, 'oscarchinyemba', 'oscarchinyemba@gmail.com', NULL, '$2y$10$2Q07DAXzZ4PQSCO/cC7cVeZYAUkrUKpph3DqfYZ1tg/IJXjDW/yF.', NULL, 'Oscar Mukwakwa', 'Chinyemba', '+260975812995', NULL, 'active', NULL, NULL, 0, '2026-01-13 18:03:04', NULL, 0, NULL, '2026-01-13 16:02:31', '2026-01-13 16:03:04', NULL),
(46, 'mutintaschinyemba', 'mutintaschinyemba@gmail.com', NULL, '$2y$10$jzfxcM7td1SAcJ/0CeNCCObTSWV.q8qRBwCxpoZJA1KVjAbmYR82a', NULL, 'Mutinta', 'Simwami', '+260979578041', NULL, 'active', NULL, NULL, 0, NULL, NULL, 0, NULL, '2026-01-13 16:04:02', '2026-01-13 16:04:02', NULL),
(47, 'Eunicechola2001', 'Eunicechola2001@gmail.com', '106231105485482941827', '$2y$10$7vXazCAcNErbHUBG/JVsHutPYiNWerQsr.1KejkPiM0d9AnIRl4rW', NULL, 'Eunice', 'Chola', '+260761835168', NULL, 'active', NULL, NULL, 1, '2026-02-26 23:04:42', NULL, 0, NULL, '2026-01-21 20:00:01', '2026-02-26 21:04:42', NULL),
(48, 'LweendoChizyuka7', 'LweendoChizyuka7@gmail.com', NULL, '$2y$10$WwLYFWrAKxpcLkonsCl6L.4UEC1eG9UbrVfjg4gv/utMIZ5QWVxg.', NULL, 'Lweendo', 'Chizyuka', '+260976396235', NULL, 'active', NULL, NULL, 0, '2026-02-04 14:43:04', NULL, 0, NULL, '2026-02-04 12:42:22', '2026-02-04 12:43:04', NULL),
(49, 'cetronmichelo', 'cetronmichelo@gmail.com', '102123028416500529258', '$2y$10$ELKpFIbHn4oBJNukHOyQXO0dL2ew9hzL.wvjHLJZLgsIHY6lGCqu2', NULL, 'Cetron', 'Michelo', '+260974194846', NULL, 'active', NULL, NULL, 1, '2026-02-24 08:59:33', NULL, 0, NULL, '2026-02-14 02:09:56', '2026-02-24 06:59:33', NULL),
(50, 'choolwelubaya1', 'choolwelubaya1@gmail.com', NULL, '$2y$10$m1wtqrAHzw0oGfNz9sbOReBgm8r8lWhvepO370YramoQyJC.15J9S', NULL, 'Choolwe', 'Lubaya', '+260770602779', NULL, 'active', NULL, NULL, 0, '2026-02-18 11:08:45', NULL, 0, NULL, '2026-02-14 09:01:12', '2026-02-18 09:08:45', NULL),
(51, 'mubangajames45', 'mubangajames45@gmail.com', NULL, '$2y$10$FWVkEgoSjZ33wG3jQTubDum8rXJ9LMcuOO3mTU8WDcSowmbqbeJTK', NULL, 'James', 'Mubanga', '+260767248479', NULL, 'active', NULL, NULL, 0, '2026-02-19 20:56:42', NULL, 0, NULL, '2026-02-17 18:48:45', '2026-02-19 18:56:42', NULL),
(52, 'edutrackzambia', 'edutrackzambia@gmail.com', '106695613944625113591', '$2y$10$Ps1P1UXhCjl9OEVGD9VDuePvp4S6sf4DTRewfYWMx2KHqRDFfxwOy', NULL, 'EdutrackZambia', '', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-02-18 09:13:07', '2026-02-18 09:13:07', NULL),
(53, 'Hestchilala1', 'Hestchilala1@gmail.com', NULL, '$2y$10$s0VyKX1solGrALrgDMwl7e5NQVDjco/PjXEjPoCsjUBXcwiqoMbri', NULL, 'Hest', 'Chilala', '+260973113441', NULL, 'active', NULL, NULL, 0, '2026-02-20 13:12:49', NULL, 0, NULL, '2026-02-20 11:11:55', '2026-02-20 11:12:49', NULL),
(54, 'musabakaderrick8', 'musabakaderrick8@gmail.com', NULL, '$2y$10$6u1HEzoZZFv4.jRteqpDyOKWjeo5KsHHLC7apxcu/PQLHQLZn/VvC', NULL, 'Musabaka', 'Derrick', '+260973838490', NULL, 'active', NULL, NULL, 0, '2026-02-22 19:16:25', NULL, 0, NULL, '2026-02-22 17:15:58', '2026-02-22 17:16:25', NULL),
(55, 'ackimchikwama02', 'ackimchikwama02@gmail.com', '109526682246921667099', '$2y$10$AXcskk980gX38QtBPg4jBu/janFuGliCTyLQB5Azc3skzz.B4iRyO', NULL, 'Ackim', 'chikwama', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-02-23 10:27:32', '2026-02-23 10:27:32', NULL),
(56, 'MUNSANJEPROTEST', 'MUNSANJEPROTEST@GMAIL.COM', '107774059359999201430', '$2y$10$ChE1sHck5TBCwD47m64Ik.GECv.4.sN0/U.djm1DrEh4dXz5b0abG', NULL, 'Protest', 'Munsanje', '+260974030032', NULL, 'active', NULL, NULL, 1, '2026-04-14 12:28:33', NULL, 0, NULL, '2026-02-25 18:15:00', '2026-04-14 10:28:33', NULL),
(57, 'sowetoagric', 'sowetoagric@gmail.com', NULL, '$2y$10$UbRtwN70lYtaDT0wo..SZu40U.Y9pxsJSlE43aec6GtcmCxmRHBhq', NULL, 'Mwendalubi', 'Chikwikwi', '+260979576624', NULL, 'active', NULL, NULL, 0, '2026-02-26 22:07:38', NULL, 0, NULL, '2026-02-26 20:06:15', '2026-02-26 20:07:38', NULL),
(58, 'viintempest', 'viintempest@gmail.com', NULL, '$2y$10$W1ba.uYujd5WtSA1mOFE4er8LC0mnGjig1H7Jwy9eKCCRA7qwLYc6', NULL, 'Vin', 'Tempest', '+260978554567', NULL, 'active', NULL, NULL, 0, '2026-03-03 00:47:07', NULL, 0, NULL, '2026-03-02 22:26:34', '2026-03-02 22:47:07', NULL),
(59, 'mhector22', 'mhector22@gmail.com', '101028432775067894685', '$2y$10$1WZXb6kvNbi6uw/fuaJSReHSUHVeg6Ui.TRS9aAFFImUFqhZRZ1dm', NULL, 'Mr', 'Hector', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-03-04 09:22:55', '2026-03-04 09:22:55', NULL),
(60, 'raymonddingy', 'raymonddingy@gmail.com', '117571386338898160228', '$2y$10$6NIs8sv44IAAySsbuuXe0uiRHtKRtVMATUPNajZFR8XlTW8GViSCC', NULL, 'Raymond', 'Daka', '', NULL, 'active', NULL, NULL, 1, '2026-03-04 14:44:27', NULL, 0, NULL, '2026-03-04 09:48:00', '2026-03-04 12:44:27', NULL),
(61, 'charitybanda776', 'charitybanda776@gmail.com', NULL, '$2y$10$6WNHrGAT8GvsiEJm8nILMONk8mSq0K6xXCWinxPdZfSyfDh75ZIBK', NULL, 'Fostina', 'Banda', '+260973101290', NULL, 'active', NULL, NULL, 0, '2026-03-04 16:27:33', NULL, 0, NULL, '2026-03-04 14:21:12', '2026-03-04 14:27:33', NULL),
(62, 'mwewanicodemus06', 'mwewanicodemus06@gmail.com', '106547058305327026644', '$2y$10$.o.0ChnLKUmlTYqSVtmrpuMS86aeU0//QOo7.6rCDlpLp9rSZOYz6', NULL, 'Nicodemus', 'Mwewa', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-03-06 20:26:45', '2026-03-06 20:26:45', NULL),
(63, 'horizonshoptracker', 'horizonshoptracker@gmail.com', '105296253506533627254', '$2y$10$5r1EeTn6LU1FMRDvb6CNiO7OPYnYeLwgLrbJCur19p9wUqZyJAZS2', NULL, 'horizonshoptracker', '', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-03-07 12:28:33', '2026-03-07 12:28:33', NULL),
(64, 'nyumbapeza', 'nyumbapeza@gmail.com', '103131314637301659877', '$2y$10$AwUJPbz.RA4qaEW4DpTiSe/ZNU1d9ssjaE.Wnmfx5CIx61D3QKDDm', NULL, 'peza', 'nyumba', '', NULL, 'active', NULL, NULL, 1, '2026-03-07 16:32:57', NULL, 0, NULL, '2026-03-07 12:56:28', '2026-03-07 14:32:57', NULL),
(65, 'nyambeclifford5', 'nyambeclifford5@gmail.com', NULL, '$2y$10$t1JCVA2p8oKkgwnj4xUO0.SBbAXhiIDFmlq9ZY354kA/4qAmkD0Fu', NULL, 'Clifford', 'Nyambe', '+260974849113', NULL, 'active', NULL, NULL, 0, '2026-03-13 11:52:46', NULL, 0, NULL, '2026-03-13 09:46:59', '2026-03-13 09:52:46', NULL),
(66, 'evansmusonda0168', 'evansmusonda0168@gmail.com', '115760171153278312335', '$2y$10$KYLsVI/ObS9o5G4cPVc0pup4gZFpYdQ99vkI2f3Y7DiW8R/DmWN8i', NULL, 'Evans', 'Musonda', '0970163351', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-03-14 12:46:43', '2026-03-14 13:07:32', NULL),
(67, 'emmanuelchikunda', 'emmanuelchikunda@gmail.com', '113646911319627230668', '$2y$10$YWW8I3d0X28jGz.7u6/5M.x0RIrG4.1X5AUYVmPwy/hLSYPIW8EXi', NULL, 'emmanuel', 'chikunda', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-03-15 13:13:55', '2026-03-15 13:13:55', NULL),
(68, 'ntikishaj320', 'ntikishaj320@gmail.com', NULL, '$2y$10$wZQkzfa40LzFTisyH1JVx.hjF1JvKTPQmjWxjl4JYZXxfiJDiFrWm', NULL, 'JAMES', 'NTIKISHA', '+260966889296', NULL, 'active', NULL, NULL, 0, '2026-03-31 20:25:00', NULL, 0, NULL, '2026-03-16 12:45:20', '2026-03-31 18:25:00', NULL),
(69, 'patricknyinganji', 'patricknyinganji@gmail.com', '106197182392604323451', '$2y$10$lZ5nOTsZi6fPlJdrBNgBvuq/K11ADh46/zHodJqtj3W5GYqwy2Pga', NULL, 'Patrick', 'Nyinganji', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-03-20 10:44:56', '2026-03-20 10:44:56', NULL),
(70, 'mordecaikabangu22', 'mordecaikabangu22@gmail.com', NULL, '$2y$10$W.lLn6/WRC.tzEKLsIbHf./uTIUccMNcOuo0T9wHFhwxwpjf2/N8.', NULL, 'mordecai', 'kabangu', '+260573068176', NULL, 'active', NULL, NULL, 0, NULL, NULL, 0, NULL, '2026-03-26 11:30:42', '2026-03-26 11:30:42', NULL),
(71, 'aaronchuma74', 'aaronchuma74@gmail.com', '109514703032195426487', '$2y$10$MpHrvARtCdx4CXvX4ZvFbuPzqqQl2LK3TJJMAdIs8.ZJBk5alkYCa', NULL, 'Aaron', 'chuma', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-03-29 09:00:10', '2026-03-29 09:00:10', NULL),
(72, 'kasokasusan574', 'kasokasusan574@gmail.com', NULL, '$2y$10$r8E6eyyJd/JLDxJRgjeZ8.ynHO0AKbjbQlKZRCUGaWHhJoCHN3adS', NULL, 'Susan', 'Kasoka', '+260978705185', NULL, 'active', NULL, NULL, 0, '2026-04-12 13:27:36', NULL, 0, NULL, '2026-04-12 11:27:02', '2026-04-12 11:27:36', NULL),
(73, 'ikacanap', 'ikacanap@gmail.com', NULL, '$2y$10$zdcTP61bLu4ZJh5362aw7OSqw4.PekMJ4Ax7l4agbHwvlN0OEIwkK', NULL, 'Kekelwa', 'Kekelwa', '+260975784430', NULL, 'active', NULL, NULL, 0, '2026-04-15 12:22:17', NULL, 0, NULL, '2026-04-15 10:21:59', '2026-04-15 10:22:17', NULL),
(74, 'zuluyunia', 'zuluyunia@gmail.com', '116697723807193109678', '$2y$10$JXhxWWWurdERghNKMdi6.OxynPCFo9/DkTD/yGCF/SCHFRdvJ9KGq', NULL, 'Yunia', 'Zulu', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-04-23 08:10:47', '2026-04-23 08:10:47', NULL),
(75, 'wilfredmweemba12345', 'wilfredmweemba12345@gmail.com', NULL, '$2y$10$WLVee9FIhr2CCDZaFQzNo..X1LPwm..fbwxeaGXhXrHoYH4TvaZri', NULL, 'Wilfred', 'Mweemba', '+260972584450', NULL, 'active', NULL, NULL, 0, '2026-04-26 06:57:07', NULL, 0, NULL, '2026-04-26 04:56:43', '2026-04-26 04:57:07', NULL),
(76, 'stephenkafweku', 'stephenkafweku@gmail.com', '109580197860006785098', '$2y$10$21cO.jH6.vbWPv9/x7YFn.tXpc9UU1himG9n0Tw/xBaBpXAKWgs9G', NULL, 'Stephen', 'Kafweku', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-04-30 12:33:28', '2026-04-30 12:33:28', NULL),
(77, 'taongatembo167', 'taongatembo167@gmail.com', NULL, '$2y$10$2HHJlvBwC0GeqCL258YOIemnnaSa6jIllkuPdq/V5EiqNq8RWMISm', NULL, 'Taonga', 'Tembo', '+260779033041', NULL, 'active', NULL, NULL, 0, '2026-05-08 14:14:15', NULL, 0, NULL, '2026-05-08 12:11:18', '2026-05-08 12:14:15', NULL),
(78, 'luyando356', 'luyando356@gmail.com', NULL, '$2y$10$khvRzYfIZKv3Vc.h.8lc9.GiOa9iYqTk/OKNcaAM5iAMvU0TsFEfu', NULL, 'Luyando Mumbe', 'Muchimba', '+260975215720', NULL, 'active', NULL, NULL, 0, '2026-05-15 22:03:11', NULL, 0, NULL, '2026-05-08 12:12:26', '2026-05-15 20:03:11', NULL),
(79, 'chintuchiinda01', 'chintuchiinda01@gmail.com', NULL, '$2y$10$1uAckp4.jefF1GVeYeXR.e3aCmS3nKhAz8sXHRUkWSY9CBnezlHs6', NULL, 'Chintu', 'Chiinda', '+260976788089', NULL, 'active', NULL, NULL, 0, '2026-05-12 11:09:53', NULL, 0, NULL, '2026-05-08 12:16:52', '2026-05-12 09:09:53', NULL),
(80, 'wankietrust08', 'wankietrust08@gmail.com', '107750983156068961383', '$2y$10$NVtUB/Osxdhj4kVGXqzzJewd89NQFhfV6tY4LgOqFGVkTcz18nmx.', NULL, 'Trust', 'Wankie', '', NULL, 'active', NULL, NULL, 1, '2026-05-08 14:40:21', NULL, 0, NULL, '2026-05-08 12:16:59', '2026-05-08 12:40:21', NULL),
(81, 'luyandodabali0', 'luyandodabali0@gmail.com', '114514853630568794611', '$2y$10$0w9cSLP1kgRpjspfr/9WS.TOaguQS5pC87yzbgWeR6Vq1a0Q1LxQS', NULL, 'Luyando', 'Dabali', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-05-08 12:19:00', '2026-05-08 12:19:00', NULL),
(82, 'abhisikaubwe', 'abhisikaubwe@gmail.com', '102122395373050519487', '$2y$10$w4CdWNFmuJoCr3cXZf80C./.f7Apgv69vISIJ.FJRJ8JfucqwHZb2', NULL, 'Abhi', 'Sikaubwe', '', NULL, 'active', NULL, NULL, 1, '2026-05-10 10:18:24', NULL, 0, NULL, '2026-05-08 12:19:44', '2026-05-10 08:18:24', NULL),
(83, 'wanengambi12', 'wanengambi12@icloud.com', NULL, '$2y$10$cQmiF1eu23iGF5.RdRJ7peWc9vwSJq1NynJ4OrplaFOkHooBCbGeO', NULL, 'Wane', 'Mary', '+260779297663', NULL, 'active', NULL, NULL, 0, '2026-05-08 14:24:34', NULL, 0, NULL, '2026-05-08 12:22:17', '2026-05-08 12:24:34', NULL),
(84, 'cathynamakanda75', 'cathynamakanda75@gmail.com', NULL, '$2y$10$WY.dcJcc3pIetoZmPSC7Mem1DDrquYO2.jfxo4KFzLN84mCRQdHqW', NULL, 'Catherine', 'Namakanda', '+260766635170', NULL, 'active', NULL, NULL, 0, '2026-05-11 22:59:40', NULL, 0, NULL, '2026-05-08 12:24:57', '2026-05-11 20:59:40', NULL),
(85, 'fragestermudenda46', 'fragestermudenda46@gmail.com', NULL, '$2y$10$G3Y6MsNAovpiLFByVeoJGuAKW2DkKk0Zw.qnKJuNGiSzISAEQMLni', NULL, 'Fragester', 'Mudenda', '+260773137696', NULL, 'active', NULL, NULL, 0, '2026-05-09 20:57:06', NULL, 0, NULL, '2026-05-08 12:38:39', '2026-05-09 18:57:06', NULL),
(86, 'lishebelajoyce', 'lishebelajoyce@gmail.com', '114562181971949631777', '$2y$10$tdt46skZDQ7/OpXlrmmwRe1fCTa9DDZbgqUbbD2vf6LGFNpe9g0g6', NULL, 'Joyce', 'Lishebela', '', NULL, 'active', NULL, NULL, 1, '2026-05-13 13:55:43', NULL, 0, NULL, '2026-05-08 12:46:55', '2026-05-13 11:55:43', NULL),
(87, 'patricia.siamukopa', 'patricia.siamukopa@student.edutrack.edu', NULL, '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', NULL, 'Patricia', 'Siamukopa', NULL, NULL, 'inactive', NULL, NULL, 0, NULL, NULL, 0, NULL, '2026-05-08 14:47:43', '2026-05-22 19:15:06', NULL),
(88, 'testuser', 'testuser@edutrack.edu', NULL, '$2y$10$OpR3oP3xhNb7m93AOklNjerKmIRoMAVNbxVY73S3QL6xdwjpIuily', 'l20lCfOTxb6Eo8IwPcNk4otPjWgLMyMNkC7zU11e924sXmxSTDIlLNW76eCU', 'Michael', 'Siame', '0771216339', NULL, 'active', NULL, NULL, 1, '2026-05-15 19:18:30', NULL, 0, NULL, '2026-05-08 15:20:22', '2026-06-04 22:49:06', NULL),
(89, 'dicksonchangwe6', 'dicksonchangwe6@gmail.com', '108102239233611028894', '$2y$10$nP2ndbj28eDx7UbVwEBXm.BOfs71ZmOigmqBl8u1.rK624JFuonhe', NULL, 'DICKSON', 'CHANGWE', '', NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-05-11 09:41:30', '2026-05-11 09:41:30', NULL),
(90, 'maselenimary854', 'maselenimary854@gmail.com', '103272139780537937302', '$2y$10$b7vc5viRZaU0FKVsJX/lZuTpCKojZ1SChFSGzGoVRtuYvW7RpK7QW', NULL, 'Mary', 'Maseleni', '', NULL, 'active', NULL, NULL, 1, '2026-05-19 11:07:37', NULL, 0, NULL, '2026-05-12 16:26:41', '2026-05-19 09:07:37', NULL),
(91, 'testinstructor', 'testinstructor@edutrack.edu', NULL, '$2y$12$ZahKrD/0E1YG23Y63cuTzefUWPupaj4MbWGE/KXqAsgtyDsPI6v4G', NULL, 'Test', 'Instructor', NULL, NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2026-05-23 11:39:38', '2026-05-23 11:39:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `nrc_number` varchar(20) DEFAULT NULL,
  `education_level` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `bio`, `phone`, `date_of_birth`, `gender`, `address`, `city`, `country`, `postal_code`, `emergency_contact_name`, `emergency_contact_phone`, `avatar_url`, `created_at`, `updated_at`, `avatar`, `province`, `nrc_number`, `education_level`, `occupation`, `company`, `linkedin_url`, `facebook_url`, `twitter_url`) VALUES
(1, 8, NULL, '+260971111111', '1998-05-15', 'Male', NULL, 'Lusaka', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 9, NULL, '+260972222222', '2000-08-22', 'Female', NULL, 'Ndola', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 10, NULL, '+260973333333', '1995-03-10', 'Male', NULL, 'Kitwe', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 11, NULL, '+260974444444', '1999-11-30', 'Female', NULL, 'Livingstone', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 12, NULL, '+260975555555', '1997-07-18', 'Male', NULL, 'Lusaka', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 13, NULL, '+260976666666', '2001-02-25', 'Female', NULL, 'Kabwe', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 14, NULL, '+260977777777', '1996-09-12', 'Male', NULL, 'Chingola', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 15, NULL, '+260978888888', '1998-12-05', 'Female', NULL, 'Lusaka', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 16, NULL, '+260979999999', '2000-04-20', 'Male', NULL, 'Solwezi', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 17, NULL, '+260970000000', '1999-06-08', 'Female', NULL, 'Mongu', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 18, NULL, '+260971234567', '1997-10-15', 'Male', NULL, 'Kasama', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 19, NULL, '+260972345678', '2001-01-30', 'Female', NULL, 'Lusaka', 'Zambia', NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 2, 'Experienced ICT trainer with 10+ years in corporate training. Microsoft Certified Professional with expertise in Office Suite and digital literacy programs.', '+260977123456', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 3, 'Full-stack developer and certified instructor with passion for teaching modern programming languages. 8 years industry experience in software development.', '+260966234567', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 4, 'Cybersecurity specialist and ethical hacker with CISSP certification. Former network administrator with extensive experience in data security.', '+260955345678', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 5, 'AI/ML researcher and data scientist with PhD in Computer Science. Published researcher with focus on practical applications of machine learning.', '+260944456789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 6, 'Business consultant and entrepreneur with MBA. Specializes in digital transformation, project management, and business strategy.', '+260933567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 7, 'Award-winning graphic designer and digital marketer. Adobe Certified Expert with 7 years experience in creative industries.', '+260922678901', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 26, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 27, NULL, '+260979536820', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 28, NULL, '+260976062621', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 29, NULL, '+260978605960', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 30, '', '+260771216339', '2002-05-03', 'Male', '1038 accra road', 'kitwe', 'Zambia', '10101', NULL, NULL, NULL, '2025-12-09 11:30:29', '2025-12-10 07:40:33', NULL, 'Copperbelt', '398943/65/1', 'Grade 12', 'technican', NULL, '', '', ''),
(47, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 13:21:55', '2025-12-20 13:21:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 12:57:29', '2025-12-21 12:57:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-22 16:20:20', '2025-12-22 16:20:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 37, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-23 07:18:25', '2025-12-23 07:18:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 38, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-25 10:05:01', '2025-12-25 10:05:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 39, 'My name is Given mutwena,am a female.Am a primary teacher , working from kalulushi at kameme central primary school', '+260971077923', '1987-09-28', 'Female', 'Kalulushi Paterson area behind local court', 'Kalulushi', 'Zambia', '', NULL, NULL, NULL, '2025-12-28 15:13:44', '2025-12-28 15:58:19', NULL, 'Copperbelt', '103695/97/1', 'Bachelor\'s Degree', 'Teacher', NULL, '', '', ''),
(53, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-29 10:01:21', '2025-12-29 10:01:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, 41, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-30 12:45:53', '2025-12-30 12:45:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, 42, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-31 09:03:33', '2025-12-31 09:03:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 10:47:56', '2026-01-08 10:47:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-11 17:02:07', '2026-01-11 17:02:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(58, 45, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 16:02:31', '2026-01-13 16:02:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(59, 46, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 16:04:02', '2026-01-13 16:04:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-21 20:00:01', '2026-02-26 21:04:42', 'https://lh3.googleusercontent.com/a/ACg8ocKHiy_RMU5WoyphteJFf34DW9Jq8j8_DDaN5esXfNaUUmDKI4LG=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, 48, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-04 12:42:22', '2026-02-04 12:42:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, 49, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-14 02:09:56', '2026-02-24 06:59:33', 'https://lh3.googleusercontent.com/a/ACg8ocJeuzHeoAhgwgbYVXtQHyPnn2fMxN6BxTuXYwNOT8HzR_-Jrhqv=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, 50, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-14 09:01:12', '2026-02-14 09:01:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, 51, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-17 18:48:45', '2026-02-17 18:48:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 09:13:07', '2026-02-18 09:13:07', 'https://lh3.googleusercontent.com/a/ACg8ocJg38ZI5n-yG4ArZnmCvB5bh34XqSM3l80cCouaS5aB_tafnQ=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 53, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 11:11:55', '2026-02-20 11:11:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 54, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 17:15:58', '2026-02-22 17:15:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(68, 55, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-23 10:27:32', '2026-02-23 10:27:32', 'https://lh3.googleusercontent.com/a/ACg8ocJGszCBl0lsdLuRN7cLa7IUvcwjrBRNIKKLNW6r6MIqqUOlAg=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(69, 56, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-25 18:15:00', '2026-03-31 16:51:45', 'https://lh3.googleusercontent.com/a/ACg8ocIPQ8FPMca1hblkJekhhq7IJEhgPpsfc9p_3pE7ci9RngOkaulnXw=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-26 20:06:15', '2026-02-26 20:06:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, 58, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-02 22:26:34', '2026-03-02 22:50:21', 'user_58_1772491821.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(72, 59, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-04 09:22:55', '2026-03-04 09:22:55', 'https://lh3.googleusercontent.com/a/ACg8ocKD0jrEgFWamPlW8iwGmsuAICK7KDJG_8crF0VXOgmRxcf601Jx=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(73, 60, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-04 09:48:00', '2026-03-04 09:48:00', 'https://lh3.googleusercontent.com/a/ACg8ocLsHDY0DPEc8fgyPX7wr5WbszSCWEs8fv4w-98ACYeOxMkL0IXj=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(74, 61, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-04 14:21:12', '2026-03-04 14:21:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(75, 62, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-06 20:26:45', '2026-03-06 20:26:45', 'https://lh3.googleusercontent.com/a/ACg8ocLwymIKGS4MiQY1oDyR62rqln6UZrvKmODNgoDfd_WQNTQRSA=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(76, 63, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-07 12:28:33', '2026-03-07 12:28:33', 'https://lh3.googleusercontent.com/a/ACg8ocLFCDYRyyRF2OGlvBEIRg_qJs0_lLs8bF3e13wvI1tPj3ytxQ=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(77, 64, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-07 12:56:28', '2026-03-07 12:56:28', 'https://lh3.googleusercontent.com/a/ACg8ocLoO2tH7NPraWIH0Raeg8VU-lhvyXiwmtIQ8CLdC-8fSYaOrQ=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(78, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-13 09:46:59', '2026-03-13 09:46:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(79, 66, 'EXCELLENT', '0970163351', '2000-03-20', 'Male', 'Chazanga', 'Lusaka', 'Zambia', '10101', NULL, NULL, NULL, '2026-03-14 12:46:43', '2026-03-14 13:08:58', 'user_66_1773493487.jpg', 'Lusaka', '297089/45/1', 'Grade 12', 'PTS INSURANCE', NULL, '', '', ''),
(80, 67, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15 13:13:55', '2026-03-15 13:13:55', 'https://lh3.googleusercontent.com/a/ACg8ocIlHoVUQdzk4iJX8ZCsRG5OPUB8hso5sAKsbuZd-J9BVD8qXJbT=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(81, 68, '', '+260966889296', '0000-00-00', 'Male', '', '', 'Zambia', '', NULL, NULL, NULL, '2026-03-16 12:45:20', '2026-03-17 03:14:28', NULL, 'Eastern', '', 'Bachelor\'s Degree', '', NULL, '', '', ''),
(82, 69, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-20 10:44:56', '2026-03-20 10:44:56', 'https://lh3.googleusercontent.com/a/ACg8ocLNcebFaUh76rl_36l2djnL6DDgBxy3Ct0_bBkUrhOqSXx_fw=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(83, 70, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 11:30:42', '2026-03-26 11:30:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(84, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-29 09:00:10', '2026-03-29 09:00:10', 'https://lh3.googleusercontent.com/a/ACg8ocLn7sUrb2CPxKmp4RCDBOanAbZ978LCOvVKLFELF4lusARjZg=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(85, 72, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 11:27:02', '2026-04-12 11:27:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(86, 73, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 10:21:59', '2026-04-15 10:21:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(87, 74, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-23 08:10:47', '2026-04-23 08:10:47', 'https://lh3.googleusercontent.com/a/ACg8ocJJ-foyPEj5GkFbkLFmUXcayP3cEuDXXCyNoatjTicpms7RfYGg=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(88, 75, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-26 04:56:43', '2026-04-26 04:56:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(89, 76, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-30 12:33:28', '2026-04-30 12:33:28', 'https://lh3.googleusercontent.com/a/ACg8ocLmkE9yq7oD135zrVTof34dH26n9YrVlk3LGVnLfOdJGftzcR0=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(90, 77, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:11:18', '2026-05-08 12:11:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(91, 78, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:12:26', '2026-05-08 12:12:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(92, 79, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:16:52', '2026-05-08 12:16:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(93, 80, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:16:59', '2026-05-08 12:16:59', 'https://lh3.googleusercontent.com/a/ACg8ocL5jASA0G9yjn9o7cneXLMMS0iwVMR0IoE6xddTfG5HgfYgdw=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(94, 81, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:19:00', '2026-05-08 12:19:00', 'https://lh3.googleusercontent.com/a/ACg8ocJLSGCv3ramt_U-9pxufo3TaSsi_MxVWYCNq-AwgC6h0prL_g=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(95, 82, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:19:44', '2026-05-08 12:19:44', 'https://lh3.googleusercontent.com/a/ACg8ocJlfYdR9CzG6ag6HXfs8v7lOMOzJ-vRwATrMC77tD5cawLNvg=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(96, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:22:17', '2026-05-08 12:22:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(97, 84, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:24:57', '2026-05-08 12:24:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(98, 85, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:38:39', '2026-05-08 12:38:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(99, 86, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 12:46:55', '2026-05-08 12:46:55', 'https://lh3.googleusercontent.com/a/ACg8ocIRHsx4T3M7WKNEEW_rq6LciDWwizWiOc_RjquzY-rbJE_btA=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 14:47:43', '2026-05-08 14:47:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(101, 88, NULL, NULL, '2002-05-03', NULL, '1038 accra road', 'kitwe', 'Zambia', '10101', NULL, NULL, NULL, '2026-05-08 15:20:22', '2026-06-08 16:36:15', NULL, NULL, '398943/65/1', NULL, NULL, 'horizon garages', NULL, NULL, NULL),
(102, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-11 09:41:30', '2026-05-11 09:41:30', 'https://lh3.googleusercontent.com/a/ACg8ocLzYDrYyeaYVaVyJ_2D-67NdpbzFNbFV6ekRPztD_A1HdndTVA=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(103, 90, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-12 16:26:41', '2026-05-12 16:26:41', 'https://lh3.googleusercontent.com/a/ACg8ocLxVlsZJvZYFF0TUg6QbetxEJFSn6J1zX5k9Dwhjv8L02-RUA=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `assigned_at`, `assigned_by`) VALUES
(1, 1, 1, '2025-11-18 22:21:01', 1),
(2, 2, 3, '2025-11-18 22:21:01', 1),
(3, 3, 3, '2025-11-18 22:21:01', 1),
(4, 4, 3, '2025-11-18 22:21:01', 1),
(5, 5, 3, '2025-11-18 22:21:01', 1),
(7, 7, 3, '2025-11-18 22:21:01', 1),
(8, 8, 4, '2025-11-18 22:21:01', 1),
(9, 9, 4, '2025-11-18 22:21:01', 1),
(10, 10, 4, '2025-11-18 22:21:01', 1),
(11, 11, 4, '2025-11-18 22:21:01', 1),
(12, 12, 4, '2025-11-18 22:21:01', 1),
(13, 13, 4, '2025-11-18 22:21:01', 1),
(14, 14, 4, '2025-11-18 22:21:01', 1),
(15, 15, 4, '2025-11-18 22:21:01', 1),
(16, 16, 4, '2025-11-18 22:21:01', 1),
(17, 17, 4, '2025-11-18 22:21:01', 1),
(18, 18, 4, '2025-11-18 22:21:01', 1),
(19, 19, 4, '2025-11-18 22:21:01', 1),
(25, 25, 2, '2025-11-22 09:07:23', NULL),
(26, 26, 4, '2025-11-23 11:05:46', NULL),
(27, 27, 2, '2025-12-08 17:45:29', NULL),
(28, 28, 4, '2025-12-08 17:45:29', NULL),
(29, 29, 4, '2025-12-08 17:45:29', NULL),
(30, 0, 4, '2025-12-09 11:25:06', NULL),
(31, 30, 3, '2025-12-09 11:30:29', NULL),
(53, 27, 1, '2025-12-18 19:11:42', NULL),
(54, 27, 3, '2025-12-18 19:11:42', NULL),
(55, 29, 2, '2025-12-18 19:11:42', NULL),
(56, 6, 2, '2025-12-18 19:11:42', NULL),
(57, 6, 3, '2025-12-18 19:11:42', NULL),
(58, 31, 3, '2025-12-18 19:11:42', NULL),
(59, 28, 3, '2025-12-18 19:11:42', NULL),
(60, 32, 6, '2025-12-18 19:11:42', NULL),
(61, 33, 2, '2025-12-18 19:11:42', NULL),
(62, 34, 4, '2025-12-20 13:21:55', NULL),
(63, 35, 4, '2025-12-21 12:57:29', NULL),
(64, 36, 4, '2025-12-22 16:20:20', NULL),
(65, 37, 4, '2025-12-23 07:18:25', NULL),
(66, 38, 4, '2025-12-25 10:05:01', NULL),
(67, 39, 4, '2025-12-28 15:13:44', NULL),
(68, 40, 4, '2025-12-29 10:01:21', NULL),
(69, 41, 4, '2025-12-30 12:45:53', NULL),
(70, 42, 4, '2025-12-31 09:03:33', NULL),
(71, 43, 4, '2026-01-08 10:47:56', NULL),
(72, 44, 4, '2026-01-11 17:02:07', NULL),
(73, 45, 4, '2026-01-13 16:02:31', NULL),
(74, 46, 4, '2026-01-13 16:04:02', NULL),
(75, 47, 4, '2026-01-21 20:00:01', NULL),
(76, 48, 4, '2026-02-04 12:42:22', NULL),
(77, 49, 4, '2026-02-14 02:09:56', NULL),
(78, 50, 4, '2026-02-14 09:01:12', NULL),
(79, 51, 4, '2026-02-17 18:48:45', NULL),
(80, 52, 4, '2026-02-18 09:13:07', NULL),
(81, 53, 4, '2026-02-20 11:11:55', NULL),
(82, 54, 4, '2026-02-22 17:15:58', NULL),
(83, 55, 4, '2026-02-23 10:27:32', NULL),
(84, 56, 4, '2026-02-25 18:15:00', NULL),
(85, 57, 4, '2026-02-26 20:06:15', NULL),
(86, 58, 4, '2026-03-02 22:26:34', NULL),
(87, 59, 4, '2026-03-04 09:22:55', NULL),
(88, 60, 4, '2026-03-04 09:48:00', NULL),
(89, 61, 4, '2026-03-04 14:21:12', NULL),
(90, 62, 4, '2026-03-06 20:26:45', NULL),
(91, 63, 4, '2026-03-07 12:28:33', NULL),
(92, 64, 4, '2026-03-07 12:56:28', NULL),
(93, 65, 4, '2026-03-13 09:46:59', NULL),
(94, 66, 4, '2026-03-14 12:46:43', NULL),
(95, 67, 4, '2026-03-15 13:13:55', NULL),
(96, 68, 4, '2026-03-16 12:45:20', NULL),
(97, 69, 4, '2026-03-20 10:44:56', NULL),
(98, 70, 4, '2026-03-26 11:30:42', NULL),
(99, 71, 4, '2026-03-29 09:00:10', NULL),
(100, 72, 4, '2026-04-12 11:27:02', NULL),
(101, 73, 4, '2026-04-15 10:21:59', NULL),
(102, 74, 4, '2026-04-23 08:10:47', NULL),
(103, 75, 4, '2026-04-26 04:56:43', NULL),
(104, 76, 4, '2026-04-30 12:33:28', NULL),
(105, 77, 4, '2026-05-08 12:11:18', NULL),
(106, 78, 4, '2026-05-08 12:12:26', NULL),
(107, 79, 4, '2026-05-08 12:16:52', NULL),
(108, 80, 4, '2026-05-08 12:16:59', NULL),
(109, 81, 4, '2026-05-08 12:19:00', NULL),
(110, 82, 4, '2026-05-08 12:19:44', NULL),
(111, 83, 4, '2026-05-08 12:22:17', NULL),
(112, 84, 4, '2026-05-08 12:24:57', NULL),
(113, 85, 4, '2026-05-08 12:38:39', NULL),
(114, 86, 4, '2026-05-08 12:46:55', NULL),
(115, 87, 4, '2026-05-08 14:47:43', 1),
(116, 88, 4, '2026-05-08 15:20:22', 1),
(117, 89, 4, '2026-05-11 09:41:30', NULL),
(118, 90, 4, '2026-05-12 16:26:41', NULL),
(120, 91, 3, '2026-05-23 11:41:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `ip_address`, `user_agent`, `expires_at`, `created_at`, `updated_at`) VALUES
(206, 90, '6395104652e067e1be0e36bbb842b17c58c0e25f56c304808027f0971be3b434', '102.212.183.105', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', '2026-05-19 13:07:37', '2026-05-19 09:07:37', '2026-05-19 09:07:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_al_user` (`user_id`),
  ADD KEY `idx_al_type` (`activity_type`),
  ADD KEY `idx_al_created` (`created_at`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `fk_ann_poster` (`posted_by`),
  ADD KEY `idx_ann_course` (`course_id`),
  ADD KEY `idx_announce_published_expires` (`is_published`,`expires_at`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assign_course` (`course_id`),
  ADD KEY `idx_assignments_due_date` (`due_date`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_assign` (`assignment_id`),
  ADD KEY `idx_sub_student` (`student_id`),
  ADD KEY `idx_asub_assignment_student` (`assignment_id`,`student_id`),
  ADD KEY `idx_asub_status` (`status`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`badge_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD UNIQUE KEY `uk_cert_number` (`certificate_number`),
  ADD UNIQUE KEY `uk_cert_verify` (`verification_code`),
  ADD KEY `idx_cert_user` (`user_id`),
  ADD KEY `idx_cert_course` (`course_id`),
  ADD KEY `fk_cert_enroll` (`enrollment_id`),
  ADD KEY `idx_certificates_issued` (`issued_date`),
  ADD KEY `certificates_verification_code_index` (`verification_code`),
  ADD KEY `certificates_user_course_index` (`user_id`,`course_id`),
  ADD KEY `certificates_enrollment_idx` (`enrollment_id`),
  ADD KEY `certificates_number_idx` (`certificate_number`),
  ADD KEY `certificates_verify_idx` (`verification_code`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contacts_created_at` (`created_at`),
  ADD KEY `idx_contacts_is_read` (`is_read`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_courses_cat` (`category_id`),
  ADD KEY `idx_courses_inst` (`instructor_id`),
  ADD KEY `idx_courses_status` (`status`),
  ADD KEY `idx_courses_status_featured` (`status`,`is_featured`),
  ADD KEY `courses_template_source_id_foreign` (`template_source_id`);

--
-- Indexes for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ci_course` (`course_id`),
  ADD KEY `idx_ci_inst` (`instructor_id`);

--
-- Indexes for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_course_reviews_course_user` (`course_id`,`user_id`),
  ADD KEY `idx_review_course` (`course_id`),
  ADD KEY `idx_review_user` (`user_id`);

--
-- Indexes for table `discussions`
--
ALTER TABLE `discussions`
  ADD PRIMARY KEY (`discussion_id`),
  ADD KEY `idx_disc_course` (`course_id`),
  ADD KEY `idx_disc_user` (`created_by`);

--
-- Indexes for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `idx_reply_disc` (`discussion_id`),
  ADD KEY `idx_reply_user` (`user_id`),
  ADD KEY `idx_reply_parent` (`parent_reply_id`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `scheduled_at` (`scheduled_at`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`template_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_enrollments_user_course` (`user_id`,`course_id`),
  ADD KEY `idx_enroll_user` (`user_id`),
  ADD KEY `idx_enroll_course` (`course_id`),
  ADD KEY `idx_enroll_student` (`student_id`),
  ADD KEY `idx_enrollments_status` (`enrollment_status`),
  ADD KEY `idx_enrollments_user_status` (`user_id`,`enrollment_status`),
  ADD KEY `idx_enrollments_course_user` (`course_id`,`user_id`),
  ADD KEY `enrollments_intake_id_index` (`intake_id`),
  ADD KEY `enrollments_user_course_idx` (`user_id`,`course_id`),
  ADD KEY `enrollments_status_idx` (`enrollment_status`,`payment_status`);

--
-- Indexes for table `enrollment_payment_plans`
--
ALTER TABLE `enrollment_payment_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_epp_enrollment` (`enrollment_id`),
  ADD KEY `idx_epp_user` (`user_id`),
  ADD KEY `idx_epp_course` (`course_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_featured` (`is_featured`);

--
-- Indexes for table `event_images`
--
ALTER TABLE `event_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_order` (`event_id`,`display_order`);

--
-- Indexes for table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_order` (`display_order`);

--
-- Indexes for table `institution_photos`
--
ALTER TABLE `institution_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_order` (`display_order`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_instructors_user` (`user_id`),
  ADD KEY `idx_inst_user` (`user_id`);

--
-- Indexes for table `intakes`
--
ALTER TABLE `intakes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intakes_course_id_status_index` (`course_id`,`status`),
  ADD KEY `intakes_is_default_index` (`is_default`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_payment_id_index` (`payment_id`),
  ADD KEY `invoices_student_id_index` (`student_id`),
  ADD KEY `invoices_course_id_index` (`course_id`);

--
-- Indexes for table `lenco_collections`
--
ALTER TABLE `lenco_collections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reference` (`reference`),
  ADD UNIQUE KEY `uk_lenco_collection_id` (`lenco_collection_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_phone` (`phone`);

--
-- Indexes for table `lenco_transactions`
--
ALTER TABLE `lenco_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_enrollment_id` (`enrollment_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_virtual_account` (`virtual_account_number`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `lenco_tx_reference_idx` (`reference`),
  ADD KEY `lenco_tx_enrollment_status_idx` (`enrollment_id`,`status`);

--
-- Indexes for table `lenco_webhook_logs`
--
ALTER TABLE `lenco_webhook_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_processed` (`processed`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_less_mod` (`module_id`);

--
-- Indexes for table `lesson_notes`
--
ALTER TABLE `lesson_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lesson_notes_user_id_lesson_id_unique` (`user_id`,`lesson_id`);

--
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lp_lesson` (`lesson_id`),
  ADD KEY `idx_lesson_progress_status` (`status`),
  ADD KEY `idx_lesson_progress_enrollment_status` (`enrollment_id`,`status`),
  ADD KEY `idx_lesson_progress_last_accessed` (`last_accessed`),
  ADD KEY `idx_lp_user_id` (`user_id`),
  ADD KEY `idx_lp_enrollment_lesson` (`enrollment_id`,`lesson_id`),
  ADD KEY `idx_lp_user_lesson` (`user_id`,`lesson_id`);

--
-- Indexes for table `lesson_resources`
--
ALTER TABLE `lesson_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lr_lesson` (`lesson_id`);

--
-- Indexes for table `lesson_versions`
--
ALTER TABLE `lesson_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_versions_created_by_foreign` (`created_by`),
  ADD KEY `lesson_versions_lesson_id_version_number_index` (`lesson_id`,`version_number`);

--
-- Indexes for table `live_sessions`
--
ALTER TABLE `live_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ls_lesson` (`lesson_id`),
  ADD KEY `fk_ls_instructor` (`instructor_id`),
  ADD KEY `live_sessions_intake_id_index` (`intake_id`);

--
-- Indexes for table `live_session_attendance`
--
ALTER TABLE `live_session_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lsa_session` (`live_session_id`),
  ADD KEY `idx_lsa_user` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_msg_sender` (`sender_id`),
  ADD KEY `idx_msg_recipient` (`recipient_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations_log`
--
ALTER TABLE `migrations_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_filename` (`filename`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mod_course` (`course_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `newsletter_subscribers_email_unique` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notif_user` (`user_id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `uk_payments_txn` (`transaction_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `fk_pay_student` (`student_id`),
  ADD KEY `fk_pay_course` (`course_id`),
  ADD KEY `fk_pay_enroll` (`enrollment_id`),
  ADD KEY `fk_pay_method` (`payment_method_id`),
  ADD KEY `idx_pay_plan` (`payment_plan_id`),
  ADD KEY `idx_pay_status` (`payment_status`),
  ADD KEY `payments_promotion_id_foreign` (`promotion_id`),
  ADD KEY `payments_enrollment_status_idx` (`enrollment_id`,`payment_status`),
  ADD KEY `payments_transaction_idx` (`transaction_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promotions_code_unique` (`code`),
  ADD KEY `promotions_created_by_foreign` (`created_by`),
  ADD KEY `promotions_is_active_starts_at_ends_at_index` (`is_active`,`starts_at`,`ends_at`),
  ADD KEY `promotions_code_index` (`code`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `fk_opt_question` (`question_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_course` (`course_id`),
  ADD KEY `idx_quizzes_published` (`is_published`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `fk_qa_question` (`question_id`),
  ADD KEY `idx_qans_attempt_question` (`attempt_id`,`question_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_att_quiz` (`quiz_id`),
  ADD KEY `idx_att_student` (`student_id`),
  ADD KEY `idx_qa_quiz_student` (`quiz_id`,`student_id`),
  ADD KEY `idx_qa_status` (`status`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`quiz_question_id`),
  ADD UNIQUE KEY `uk_qq_quiz_question` (`quiz_id`,`question_id`),
  ADD KEY `fk_qq_question` (`question_id`);

--
-- Indexes for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qqo_question` (`question_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_identifier` (`identifier`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `registration_fees`
--
ALTER TABLE `registration_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_registration_fees_phone` (`phone_number`),
  ADD KEY `idx_rf_user_id` (`user_id`),
  ADD KEY `idx_rf_payment_status` (`payment_status`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_students_user` (`user_id`),
  ADD KEY `idx_st_user` (`user_id`);

--
-- Indexes for table `student_achievements`
--
ALTER TABLE `student_achievements`
  ADD PRIMARY KEY (`achievement_id`),
  ADD KEY `idx_ach_student` (`student_id`),
  ADD KEY `idx_ach_badge` (`badge_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `system_settings_setting_key_unique` (`setting_key`),
  ADD KEY `idx_ss_key` (`setting_key`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_team_user_link` (`user_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `testimonials_user_enrollment_unique` (`user_id`,`enrollment_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `fk_testimonials_user` (`submitted_by`),
  ADD KEY `testimonials_course_id_foreign` (`course_id`),
  ADD KEY `testimonials_enrollment_id_foreign` (`enrollment_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_trans_payment_id` (`payment_id`),
  ADD KEY `idx_trans_type` (`transaction_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_users_email` (`email`),
  ADD UNIQUE KEY `uk_users_username` (`username`),
  ADD UNIQUE KEY `idx_users_google_id` (`google_id`),
  ADD KEY `idx_users_email_search` (`email`),
  ADD KEY `idx_users_ver_token` (`email_verification_token`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_created_at` (`created_at`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_profile_nrc` (`nrc_number`),
  ADD KEY `idx_up_user` (`user_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_role` (`user_id`,`role_id`),
  ADD KEY `idx_ur_user` (`user_id`),
  ADD KEY `idx_ur_role` (`role_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_us_user_id` (`user_id`),
  ADD KEY `idx_us_token` (`session_token`),
  ADD KEY `idx_us_expires` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `badge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `course_categories`
--
ALTER TABLE `course_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `course_instructors`
--
ALTER TABLE `course_instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `course_reviews`
--
ALTER TABLE `course_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discussions`
--
ALTER TABLE `discussions`
  MODIFY `discussion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `enrollment_payment_plans`
--
ALTER TABLE `enrollment_payment_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_images`
--
ALTER TABLE `event_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `institution_photos`
--
ALTER TABLE `institution_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `intakes`
--
ALTER TABLE `intakes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lenco_collections`
--
ALTER TABLE `lenco_collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lenco_transactions`
--
ALTER TABLE `lenco_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lenco_webhook_logs`
--
ALTER TABLE `lenco_webhook_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `lesson_notes`
--
ALTER TABLE `lesson_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT for table `lesson_resources`
--
ALTER TABLE `lesson_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `lesson_versions`
--
ALTER TABLE `lesson_versions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `live_sessions`
--
ALTER TABLE `live_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `live_session_attendance`
--
ALTER TABLE `live_session_attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `migrations_log`
--
ALTER TABLE `migrations_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=283;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `quiz_question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=279;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `registration_fees`
--
ALTER TABLE `registration_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_ann_poster` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assign_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sub_assign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `fk_cert_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cert_enroll` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_template_source_id_foreign` FOREIGN KEY (`template_source_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_courses_category` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD CONSTRAINT `fk_ci_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ci_inst` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD CONSTRAINT `fk_review_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussions`
--
ALTER TABLE `discussions`
  ADD CONSTRAINT `fk_disc_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_disc_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD CONSTRAINT `fk_reply_disc` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`discussion_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reply_parent` FOREIGN KEY (`parent_reply_id`) REFERENCES `discussion_replies` (`reply_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reply_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enroll_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enroll_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_images`
--
ALTER TABLE `event_images`
  ADD CONSTRAINT `event_images_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD CONSTRAINT `hero_slides_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `institution_photos`
--
ALTER TABLE `institution_photos`
  ADD CONSTRAINT `institution_photos_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `fk_inst_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `intakes`
--
ALTER TABLE `intakes`
  ADD CONSTRAINT `intakes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lenco_transactions`
--
ALTER TABLE `lenco_transactions`
  ADD CONSTRAINT `fk_lenco_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_lenco_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_lenco_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `fk_less_mod` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `fk_lp_enroll` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lp_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_resources`
--
ALTER TABLE `lesson_resources`
  ADD CONSTRAINT `fk_lr_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_versions`
--
ALTER TABLE `lesson_versions`
  ADD CONSTRAINT `lesson_versions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lesson_versions_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `live_sessions`
--
ALTER TABLE `live_sessions`
  ADD CONSTRAINT `fk_ls_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ls_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_msg_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `fk_mod_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_pay_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pay_enroll` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pay_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pay_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_promotion_id_foreign` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `fk_opt_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quiz_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `fk_qa_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qa_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `fk_att_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `fk_qq_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qq_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD CONSTRAINT `fk_qqo_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `fk_rt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_st_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_achievements`
--
ALTER TABLE `student_achievements`
  ADD CONSTRAINT `fk_ach_badge` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`badge_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ach_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `fk_team_user_link` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `fk_testimonials_user` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `testimonials_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `testimonials_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `testimonials_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
