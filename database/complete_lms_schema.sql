-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 09:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edutrack_lms`
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
(10, 1, 'login', NULL, NULL, 'Admin logged in', '10.0.0.10', NULL, '2025-11-18 22:21:01');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `course_id`, `posted_by`, `title`, `content`, `announcement_type`, `priority`, `is_published`, `published_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 5, 3, 'Welcome to Python Programming', 'Welcome to the Certificate in Python Programming! We are excited to have you join us. Please review the course syllabus and introduction materials.', 'Course', 'Normal', 1, '2025-01-08 08:00:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 7, 3, 'Project Deadline Extension', 'Due to popular request, the portfolio project deadline has been extended by 3 days. New deadline: March 23, 2025.', 'Course', 'High', 1, '2025-03-15 12:30:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, NULL, 1, 'Platform Maintenance Schedule', 'EduTrack LMS will undergo scheduled maintenance on Sunday, Feb 25 from 2:00 AM to 6:00 AM. The platform will be temporarily unavailable during this time.', 'System', 'Urgent', 1, '2025-02-20 07:00:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 11, 4, 'Guest Lecture on Ethical Hacking', 'Join us for a special guest lecture by cybersecurity expert Dr. Ahmed Khan on March 5, 2025 at 3:00 PM.', 'Course', 'High', 1, '2025-02-28 09:00:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `course_id`, `lesson_id`, `title`, `description`, `instructions`, `max_points`, `passing_points`, `due_date`, `allow_late_submission`, `late_penalty_percent`, `max_file_size_mb`, `allowed_file_types`, `created_at`, `updated_at`) VALUES
(1, 5, NULL, 'Python Basics Project', 'Create a simple calculator application', 'Build a command-line calculator that can perform basic arithmetic operations (addition, subtraction, multiplication, division). Include error handling for division by zero.', 100, 70, '2025-02-15 23:59:59', 1, 0.00, 10, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 5, NULL, 'Data Structures Assignment', 'Work with lists, dictionaries, and sets', 'Create a student management system using Python dictionaries to store student information. Implement functions to add, remove, and search students.', 100, 70, '2025-03-10 23:59:59', 1, 0.00, 10, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 7, NULL, 'Personal Portfolio Website', 'Build a responsive portfolio website', 'Create a multi-page portfolio website using HTML5, CSS3, and JavaScript. Must include: home page, about page, portfolio gallery, and contact form. Site must be fully responsive.', 150, 105, '2025-03-20 23:59:59', 0, 0.00, 10, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 11, NULL, 'Network Security Analysis', 'Perform security audit of a test network', 'Document security vulnerabilities in the provided test network environment. Submit a detailed report with findings and recommendations.', 100, 70, '2025-04-30 23:59:59', 0, 0.00, 10, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
  `is_late` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`id`, `assignment_id`, `student_id`, `submission_text`, `file_url`, `submitted_at`, `status`, `points_earned`, `feedback`, `graded_by`, `graded_at`, `attempt_number`, `is_late`) VALUES
(1, 1, 1, 'Calculator project completed. File uploaded to repository.', NULL, '2025-02-14 16:30:00', 'Graded', 95.00, 'Excellent work! Clean code and proper error handling. Well done.', 2, '2025-02-16 08:00:00', 1, 0),
(2, 1, 4, 'My calculator implementation with extended features.', NULL, '2025-02-15 18:00:00', 'Graded', 88.00, 'Good implementation. Consider adding more comments for clarity.', 2, '2025-02-17 12:30:00', 1, 0),
(3, 3, 2, 'Portfolio website completed with all requirements.', NULL, '2025-03-19 14:45:00', 'Graded', 142.00, 'Beautiful design and excellent responsive implementation!', 2, '2025-03-21 09:00:00', 1, 0);

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
(6, 'Helping Hand', 'Helped fellow students in discussions', NULL, 'Participation', 'Have at least 5 replies marked as helpful', 40, 1, '2025-11-18 22:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `issued_date` date NOT NULL,
  `certificate_url` varchar(255) DEFAULT NULL,
  `verification_code` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 1,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificate_id`, `enrollment_id`, `certificate_number`, `issued_date`, `certificate_url`, `verification_code`, `is_verified`, `expiry_date`, `created_at`) VALUES
(1, 1, 'EDTRK-2025-000001', '2025-04-10', NULL, 'VRF-001-ABCD1234', 1, NULL, '2025-11-18 22:21:01'),
(2, 4, 'EDTRK-2025-000002', '2025-04-25', NULL, 'VRF-002-EFGH5678', 1, NULL, '2025-11-18 22:21:01'),
(3, 7, 'EDTRK-2025-000003', '2025-04-08', NULL, 'VRF-003-IJKL9012', 1, NULL, '2025-11-18 22:21:01'),
(4, 9, 'EDTRK-2025-000004', '2025-04-12', NULL, 'VRF-004-MNOP3456', 1, NULL, '2025-11-18 22:21:01'),
(5, 10, 'EDTRK-2025-000005', '2025-04-05', NULL, 'VRF-005-QRST7890', 1, NULL, '2025-11-18 22:21:01'),
(6, 21, 'EDTRK-2025-000006', '2025-03-15', NULL, 'VRF-006-UVWX1234', 1, NULL, '2025-11-18 22:21:01');

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
  `status` enum('draft','published','archived','under review') DEFAULT 'draft',
  `is_featured` tinyint(1) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `prerequisites` text DEFAULT NULL,
  `learning_outcomes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `slug`, `description`, `short_description`, `category_id`, `instructor_id`, `level`, `language`, `thumbnail_url`, `video_intro_url`, `start_date`, `end_date`, `price`, `discount_price`, `duration_weeks`, `total_hours`, `max_students`, `enrollment_count`, `status`, `is_featured`, `rating`, `total_reviews`, `prerequisites`, `learning_outcomes`, `created_at`, `updated_at`) VALUES
(1, 'Certificate in Microsoft Office Suite', 'microsoft-office-suite', 'Comprehensive training in Word, Excel, PowerPoint, and Publisher. Learn document creation, spreadsheet analysis, presentations, and desktop publishing for professional environments.', 'Master Word, Excel, PowerPoint & Publisher', 1, 1, 'Beginner', 'English', NULL, NULL, '2025-01-15', '2025-04-15', 250.00, NULL, 12, 48.00, 30, 0, 'published', 1, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(3, 'Certificate in Digital Literacy', 'digital-literacy', 'Essential digital skills for the modern workplace including email, internet research, cloud storage, online collaboration, and digital safety.', 'Essential digital skills for everyone', 1, 1, 'Beginner', 'English', NULL, NULL, '2025-01-20', '2025-03-20', 150.00, NULL, 8, 32.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(4, 'Certificate in Record Management', 'record-management', 'Professional records and information management systems. Learn filing systems, document control, archiving, and compliance with data protection regulations.', 'Professional records management', 1, 1, 'Intermediate', 'English', NULL, NULL, '2025-02-15', '2025-05-15', 280.00, NULL, 12, 48.00, 30, 0, 'published', 0, 4.50, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(5, 'Certificate in Python Programming', 'python-programming', 'Learn Python from basics to advanced concepts. Cover data structures, OOP, file handling, and popular libraries. Ideal for beginners and aspiring developers.', 'Master Python programming', 2, 2, 'Beginner', 'English', NULL, NULL, '2025-01-10', '2025-04-10', 350.00, 315.00, 12, 60.00, 30, 0, 'published', 1, 4.90, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(6, 'Certificate in Java Programming', 'java-programming', 'Master Java programming with hands-on projects. Learn OOP principles, Java collections, multithreading, and enterprise application development.', 'Complete Java development course', 2, 2, 'Intermediate', 'English', NULL, NULL, '2025-02-01', '2025-06-01', 400.00, NULL, 16, 80.00, 30, 0, 'published', 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(7, 'Certificate in Web Development', 'web-development', 'Full-stack web development using HTML5, CSS3, JavaScript, and modern frameworks. Build responsive websites and web applications from scratch.', 'Build modern web applications', 2, 2, 'Beginner', 'English', NULL, NULL, '2025-01-15', '2025-04-30', 380.00, 342.00, 14, 70.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(8, 'Certificate in Mobile App Development', 'mobile-app-development', 'Create mobile applications for Android and iOS platforms. Learn Java/Kotlin for Android and Swift for iOS with practical app projects.', 'iOS and Android app development', 2, 2, 'Advanced', 'English', NULL, NULL, '2025-03-01', '2025-07-30', 500.00, NULL, 20, 100.00, 30, 0, 'published', 1, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(9, 'Certificate in Software Engineering & Git', 'software-engineering-git', 'Software development methodologies, version control with Git/GitHub, testing, CI/CD, and collaborative development practices.', 'Professional software engineering', 2, 2, 'Intermediate', 'English', NULL, NULL, '2025-02-10', '2025-05-10', 320.00, NULL, 12, 48.00, 30, 0, 'published', 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(10, 'Certificate in Data Analysis', 'data-analysis', 'Data analysis fundamentals using Excel, SQL, and Python. Learn data cleaning, visualization, statistical analysis, and reporting techniques.', 'Become a data analyst', 3, 4, 'Beginner', 'English', NULL, NULL, '2025-01-20', '2025-04-20', 360.00, NULL, 12, 60.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(11, 'Certificate in Cyber Security', 'cyber-security', 'Comprehensive cybersecurity training covering network security, ethical hacking, threat analysis, and security best practices. Industry-recognized certification.', 'Advanced cybersecurity training', 3, 3, 'Advanced', 'English', NULL, NULL, '2025-02-15', '2025-06-30', 550.00, 495.00, 18, 90.00, 30, 0, 'published', 1, 4.90, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(12, 'Certificate in Database Management Systems', 'database-management', 'Master database design and management using MySQL, PostgreSQL, and SQL Server. Learn SQL, normalization, optimization, and administration.', 'Database design and management', 3, 3, 'Intermediate', 'English', NULL, NULL, '2025-01-25', '2025-05-25', 400.00, NULL, 16, 64.00, 30, 0, 'published', 0, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(13, 'Certificate in AI & Machine Learning', 'ai-machine-learning', 'Introduction to artificial intelligence and machine learning. Learn algorithms, neural networks, and practical applications using Python and TensorFlow.', 'AI and ML fundamentals', 4, 4, 'Advanced', 'English', NULL, NULL, '2025-03-01', '2025-07-01', 600.00, 540.00, 16, 80.00, 30, 0, 'published', 1, 4.90, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(14, 'Certificate in Internet of Things', 'internet-of-things', 'IoT fundamentals including sensors, microcontrollers, connectivity, and cloud integration. Build smart devices and IoT solutions.', 'Build IoT solutions', 4, 4, 'Intermediate', 'English', NULL, NULL, '2025-02-20', '2025-05-20', 450.00, NULL, 12, 60.00, 30, 0, 'published', 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(15, 'Certificate in Graphic Designing', 'graphic-designing', 'Professional graphic design using Adobe Photoshop, Illustrator, and InDesign. Learn design principles, typography, branding, and print/digital media.', 'Master graphic design tools', 5, 6, 'Beginner', 'English', NULL, NULL, '2025-01-15', '2025-04-30', 380.00, NULL, 14, 56.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(16, 'Certificate in Digital Content Creation', 'digital-content-creation', 'Create engaging multimedia content for education and business. Video editing, animation, interactive presentations, and e-learning materials.', 'Multimedia content creation', 5, 6, 'Intermediate', 'English', NULL, NULL, '2025-02-05', '2025-05-05', 350.00, 315.00, 12, 48.00, 30, 0, 'published', 0, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(17, 'Certificate in Digital Marketing', 'digital-marketing', 'Comprehensive digital marketing strategies including SEO, social media marketing, content marketing, email campaigns, and analytics.', 'Complete digital marketing', 5, 6, 'Beginner', 'English', NULL, NULL, '2025-01-20', '2025-04-20', 320.00, NULL, 12, 48.00, 30, 0, 'published', 1, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(18, 'Certificate in Entrepreneurship', 'entrepreneurship', 'Start and grow your business with essential entrepreneurship skills. Business planning, financing, marketing, and operations management.', 'Start your own business', 6, 5, 'Beginner', 'English', NULL, NULL, '2025-01-10', '2025-04-10', 300.00, NULL, 12, 48.00, 30, 0, 'published', 0, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(19, 'Certificate in Project Management', 'project-management', 'Professional project management methodologies including PMBOK, Agile, and Scrum. Plan, execute, and deliver successful projects.', 'Professional project management', 6, 5, 'Intermediate', 'English', NULL, NULL, '2025-02-01', '2025-06-01', 450.00, 405.00, 16, 64.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(20, 'Certificate in Financial Technology', 'financial-technology', 'Explore digital payments, blockchain, cryptocurrency, mobile money, and digital banking. Understand the future of financial services.', 'FinTech fundamentals', 6, 5, 'Advanced', 'English', NULL, NULL, '2025-03-01', '2025-06-15', 480.00, NULL, 14, 56.00, 30, 0, 'published', 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 23:02:35');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_categories`
--

INSERT INTO `course_categories` (`id`, `name`, `category_description`, `parent_category_id`, `icon_url`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Core ICT & Digital Skills', 'Fundamental computer and digital literacy courses covering essential office applications, digital tools, and basic ICT competencies', NULL, NULL, 1, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 'Programming & Software Development', 'Programming languages, software engineering practices, web and mobile application development courses', NULL, NULL, 2, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 'Data, Security & Networks', 'Data analysis, cybersecurity, database management, and network infrastructure courses', NULL, NULL, 3, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 'Emerging Technologies', 'Cutting-edge technology courses including AI, machine learning, and Internet of Things', NULL, NULL, 4, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 'Digital Media & Design', 'Creative and digital content courses covering graphic design, multimedia, and digital marketing', NULL, NULL, 5, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 'Business & Management', 'Business administration, entrepreneurship, project management, and professional development courses', NULL, NULL, 6, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
(1, 1, 1, 'Lead', '2024-12-01', '2025-11-18 22:21:01'),
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
(24, 20, 5, 'Lead', '2024-12-01', '2025-11-18 22:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `course_reviews`
--

CREATE TABLE `course_reviews` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL CHECK (`rating` >= 0 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discussions`
--

INSERT INTO `discussions` (`discussion_id`, `course_id`, `created_by`, `title`, `content`, `is_pinned`, `is_locked`, `view_count`, `reply_count`, `created_at`, `updated_at`) VALUES
(1, 5, 8, 'Best Python IDE for beginners?', 'I am new to Python and wondering what IDE you all recommend for beginners. I have heard about PyCharm, VS Code, and IDLE. What do you think?', 0, 0, 45, 8, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 5, 11, 'Help with assignment 1', 'I am stuck on the calculator project. How do I handle the division by zero error properly?', 0, 0, 23, 5, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 7, 9, 'Responsive design best practices', 'Can anyone share tips on making websites truly responsive? I am struggling with mobile layouts.', 0, 0, 38, 12, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 11, 12, 'Career paths in cybersecurity', 'What are the different career paths available in cybersecurity? Looking for guidance.', 1, 0, 67, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
(7, 3, NULL, 3, 'Great advice! Also check out CSS Grid and Flexbox for modern layout techniques.', 1, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `template_id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `template_type` enum('Welcome','Enrollment','Certificate','Payment','Reminder','Custom') DEFAULT 'Custom',
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
  `enrolled_at` date NOT NULL,
  `start_date` date DEFAULT NULL,
  `progress` decimal(5,2) NOT NULL DEFAULT 0.00,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `enrollment_status` enum('Enrolled','In Progress','Completed','Dropped','Expired') DEFAULT 'Enrolled',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `completion_date` date DEFAULT NULL,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `last_accessed` timestamp NULL DEFAULT NULL,
  `total_time_spent` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `student_id`, `course_id`, `enrolled_at`, `start_date`, `progress`, `final_grade`, `enrollment_status`, `payment_status`, `amount_paid`, `completion_date`, `certificate_issued`, `last_accessed`, `total_time_spent`, `created_at`, `updated_at`) VALUES
(1, 8, 1, 1, '2025-01-15', '2025-01-15', 100.00, 92.50, 'Completed', 'completed', 250.00, '2025-04-10', 1, NULL, 2880, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(2, 8, 1, 5, '2025-01-15', '2025-01-16', 75.00, NULL, 'In Progress', 'completed', 315.00, NULL, 0, NULL, 2700, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(3, 8, 1, 10, '2025-01-20', '2025-01-21', 45.00, NULL, 'In Progress', 'completed', 360.00, NULL, 0, NULL, 1620, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(4, 9, 2, 7, '2025-01-15', '2025-01-15', 100.00, 88.00, 'Completed', 'completed', 342.00, '2025-04-25', 1, NULL, 4200, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(5, 9, 2, 15, '2025-01-15', '2025-01-16', 85.00, NULL, 'In Progress', 'completed', 380.00, NULL, 0, NULL, 2856, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(6, 9, 2, 17, '2025-01-20', '2025-01-21', 60.00, NULL, 'In Progress', 'completed', 320.00, NULL, 0, NULL, 1728, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(7, 10, 3, 18, '2025-01-10', '2025-01-10', 100.00, 95.00, 'Completed', 'completed', 300.00, '2025-04-08', 1, NULL, 2880, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(8, 10, 3, 19, '2025-02-01', '2025-02-02', 30.00, NULL, 'In Progress', 'completed', 405.00, NULL, 0, NULL, 1152, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(9, 10, 3, 1, '2025-01-15', '2025-01-15', 100.00, 87.50, 'Completed', 'completed', 250.00, '2025-04-12', 1, NULL, 2640, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(10, 11, 4, 5, '2025-01-10', '2025-01-10', 100.00, 91.00, 'Completed', 'completed', 315.00, '2025-04-05', 1, NULL, 3600, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(11, 11, 4, 6, '2025-02-01', '2025-02-02', 50.00, NULL, 'In Progress', 'completed', 400.00, NULL, 0, NULL, 2400, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(12, 11, 4, 9, '2025-02-10', '2025-02-11', 25.00, NULL, 'In Progress', 'completed', 320.00, NULL, 0, NULL, 720, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(13, 12, 5, 11, '2025-02-15', '2025-02-16', 40.00, NULL, 'In Progress', 'completed', 495.00, NULL, 0, NULL, 2160, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(14, 12, 5, 12, '2025-01-25', '2025-01-26', 70.00, NULL, 'In Progress', 'completed', 400.00, NULL, 0, NULL, 2688, '2025-11-18 22:21:01', '2025-11-18 23:02:36'),
(16, 13, 6, 3, '2025-01-20', '2025-01-20', 35.00, NULL, 'In Progress', 'pending', 0.00, NULL, 0, NULL, 672, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(17, 13, 6, 17, '2025-01-20', '2025-01-21', 40.00, NULL, 'In Progress', 'pending', 0.00, NULL, 0, NULL, 1152, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(18, 14, 7, 13, '2025-03-01', '2025-03-02', 15.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, NULL, 720, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(19, 14, 7, 8, '2025-03-01', '2025-03-02', 20.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, NULL, 1200, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(20, 15, 8, 18, '2025-01-10', '2025-01-10', 90.00, NULL, 'In Progress', 'pending', 0.00, NULL, 0, NULL, 2592, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(21, 15, 8, 3, '2025-01-20', '2025-01-20', 100.00, 94.00, 'Completed', 'pending', 0.00, '2025-03-15', 1, NULL, 1920, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(22, 16, 9, 10, '2025-01-20', '2025-01-21', 65.00, NULL, 'In Progress', 'pending', 0.00, NULL, 0, NULL, 2340, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(23, 16, 9, 12, '2025-01-25', '2025-01-26', 40.00, NULL, 'In Progress', 'pending', 0.00, NULL, 0, NULL, 1536, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(24, 17, 10, 15, '2025-01-15', '2025-01-16', 80.00, NULL, 'In Progress', 'pending', 0.00, NULL, 0, NULL, 2688, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(25, 17, 10, 16, '2025-02-05', '2025-02-06', 45.00, NULL, 'In Progress', 'pending', 0.00, NULL, 0, NULL, 1296, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(26, 18, 11, 1, '2025-01-15', '2025-01-16', 55.00, NULL, 'In Progress', 'pending', 0.00, NULL, 0, NULL, 1584, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(27, 18, 11, 4, '2025-02-15', '2025-02-16', 30.00, NULL, 'In Progress', 'pending', 0.00, NULL, 0, NULL, 864, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(28, 19, 12, 5, '2025-01-20', '2025-01-21', 10.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, NULL, 360, '2025-11-18 22:21:01', '2025-11-18 23:02:35'),
(29, 19, 12, 3, '2025-01-20', '2025-01-21', 15.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, NULL, 288, '2025-11-18 22:21:01', '2025-11-18 23:02:35');

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
) ;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `user_id`, `bio`, `specialization`, `years_experience`, `education`, `certifications`, `rating`, `total_students`, `total_courses`, `is_verified`, `created_at`, `updated_at`) VALUES
(1, 2, 'Experienced ICT trainer with 10+ years in corporate training. Microsoft Certified Professional with expertise in Office Suite and digital literacy programs.', 'ICT & Digital Skills', 10, NULL, NULL, 4.85, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 3, 'Full-stack developer and certified instructor with passion for teaching modern programming languages. 8 years industry experience in software development.', 'Software Development', 8, NULL, NULL, 4.92, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 4, 'Cybersecurity specialist and ethical hacker with CISSP certification. Former network administrator with extensive experience in data security.', 'Cybersecurity & Networks', 12, NULL, NULL, 4.78, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 5, 'AI/ML researcher and data scientist with PhD in Computer Science. Published researcher with focus on practical applications of machine learning.', 'AI & Data Science', 6, NULL, NULL, 4.95, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 6, 'Business consultant and entrepreneur with MBA. Specializes in digital transformation, project management, and business strategy.', 'Business & Management', 15, NULL, NULL, 4.80, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 7, 'Award-winning graphic designer and digital marketer. Adobe Certified Expert with 7 years experience in creative industries.', 'Digital Media & Design', 7, NULL, NULL, 4.88, 0, 0, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `module_id`, `title`, `content`, `lesson_type`, `duration_minutes`, `display_order`, `video_url`, `video_duration`, `is_preview`, `is_mandatory`, `points`, `created_at`, `updated_at`) VALUES
(1, 1, 'Welcome to Python Programming', 'Introduction to the course and what you will learn', 'Video', 15, 1, NULL, NULL, 1, 1, 5, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 1, 'Installing Python and IDE Setup', 'Step-by-step guide to install Python and set up your development environment', 'Video', 30, 2, NULL, NULL, 1, 1, 10, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 1, 'Your First Python Program', 'Writing and running your first \"Hello World\" program', 'Reading', 20, 3, NULL, NULL, 0, 1, 10, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 1, 'Python Syntax Basics', 'Understanding Python syntax, indentation, and comments', 'Video', 25, 4, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 2, 'Numbers in Python', 'Working with integers, floats, and complex numbers', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 2, 'Strings and String Methods', 'String manipulation and built-in string methods', 'Video', 35, 2, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 2, 'Lists and Tuples', 'Understanding ordered collections in Python', 'Reading', 40, 3, NULL, NULL, 0, 1, 20, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(8, 2, 'Dictionaries and Sets', 'Working with key-value pairs and unique collections', 'Video', 35, 4, NULL, NULL, 0, 1, 20, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(9, 3, 'If-Else Statements', 'Conditional logic and decision making', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(10, 3, 'For Loops', 'Iterating over sequences with for loops', 'Video', 35, 2, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(11, 3, 'While Loops', 'Using while loops for repeated execution', 'Reading', 25, 3, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(12, 3, 'Control Flow Quiz', 'Test your understanding of control flow', 'Quiz', 30, 4, NULL, NULL, 0, 1, 25, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(13, 7, 'Introduction to HTML5', 'Overview of HTML5 and its new features', 'Video', 25, 1, NULL, NULL, 1, 1, 10, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(14, 7, 'HTML Document Structure', 'Understanding the basic structure of an HTML document', 'Video', 30, 2, NULL, NULL, 1, 1, 10, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(15, 7, 'Semantic HTML Elements', 'Using semantic tags like header, nav, article, section', 'Reading', 35, 3, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(16, 7, 'Forms and Input Elements', 'Creating forms with various input types', 'Video', 40, 4, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(17, 8, 'CSS Basics and Selectors', 'Introduction to CSS syntax and selectors', 'Video', 30, 1, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(18, 8, 'Box Model and Layout', 'Understanding the CSS box model', 'Video', 35, 2, NULL, NULL, 0, 1, 15, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(19, 8, 'Flexbox Layout', 'Modern layout with Flexbox', 'Reading', 40, 3, NULL, NULL, 0, 1, 20, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(20, 8, 'Responsive Design with Media Queries', 'Creating responsive layouts', 'Video', 45, 4, NULL, NULL, 0, 1, 20, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
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

INSERT INTO `lesson_progress` (`id`, `enrollment_id`, `lesson_id`, `status`, `progress_percentage`, `time_spent_minutes`, `started_at`, `completed_at`, `last_accessed`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Completed', 100.00, 15, '2025-01-15 07:00:00', '2025-01-15 07:15:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 1, 2, 'Completed', 100.00, 30, '2025-01-15 07:20:00', '2025-01-15 07:50:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 1, 3, 'Completed', 100.00, 20, '2025-01-15 08:00:00', '2025-01-15 08:20:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 1, 4, 'Completed', 100.00, 25, '2025-01-15 08:30:00', '2025-01-15 08:55:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 4, 13, 'Completed', 100.00, 35, '2025-01-15 12:00:00', '2025-01-15 12:35:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 4, 14, 'Completed', 100.00, 40, '2025-01-15 13:00:00', '2025-01-15 13:40:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 10, 1, 'Completed', 100.00, 15, '2025-01-10 06:00:00', '2025-01-10 06:15:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(8, 10, 2, 'Completed', 100.00, 32, '2025-01-10 06:30:00', '2025-01-10 07:02:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(9, 10, 3, 'Completed', 100.00, 22, '2025-01-10 07:15:00', '2025-01-10 07:37:00', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `title`, `description`, `display_order`, `duration_minutes`, `is_published`, `unlock_date`, `created_at`, `updated_at`) VALUES
(1, 5, 'Introduction to Python', 'Getting started with Python programming, installation, and basic syntax', 1, 300, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 5, 'Data Types and Variables', 'Understanding Python data types, variables, and operators', 2, 360, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 5, 'Control Flow', 'Conditional statements, loops, and flow control in Python', 3, 420, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 5, 'Functions and Modules', 'Creating functions, working with modules and packages', 4, 480, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 5, 'Object-Oriented Programming', 'Classes, objects, inheritance, and OOP principles', 5, 540, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 5, 'File Handling and Exceptions', 'Reading/writing files and error handling', 6, 360, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 7, 'HTML5 Fundamentals', 'Introduction to HTML5 structure, elements, and semantic markup', 1, 400, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(8, 7, 'CSS3 Styling', 'Styling web pages with CSS3, layouts, and responsive design', 2, 480, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(9, 7, 'JavaScript Basics', 'JavaScript fundamentals, DOM manipulation, and events', 3, 540, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(10, 7, 'Modern JavaScript', 'ES6+ features, async programming, and APIs', 4, 600, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `notification_type`, `is_read`, `read_at`, `action_url`, `created_at`) VALUES
(1, 8, 'Welcome to EduTrack!', 'Your account has been successfully created. Start exploring courses now!', 'Success', 1, NULL, '/dashboard', '2025-11-18 22:21:01'),
(2, 8, 'Assignment Graded', 'Your assignment \"Python Basics Project\" has been graded. Score: 95/100', 'Grade', 1, NULL, '/courses/5/assignments/1', '2025-11-18 22:21:01'),
(3, 9, 'New Announcement', 'Project deadline has been extended to March 23, 2025', 'Announcement', 1, NULL, '/courses/7/announcements', '2025-11-18 22:21:01'),
(4, 11, 'Assignment Due Soon', 'Assignment \"Data Structures Assignment\" is due in 3 days', 'Warning', 0, NULL, '/courses/5/assignments/2', '2025-11-18 22:21:01'),
(5, 4, 'Certificate Ready', 'Your certificate for Python Programming is ready for download!', 'Success', 1, NULL, '/certificates/5', '2025-11-18 22:21:01'),
(6, 2, 'New Reply to Your Discussion', 'Sarah Banda replied to your discussion about responsive design', 'Info', 0, NULL, '/courses/7/discussions/3', '2025-11-18 22:21:01'),
(7, 10, 'Payment Successful', 'Your payment of $380.00 for Graphic Designing course was successful', 'Success', 1, NULL, '/payments/5', '2025-11-18 22:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method_id` int(11) DEFAULT NULL,
  `payment_status` enum('Pending','Completed','Failed','Refunded','Cancelled') DEFAULT 'Pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `student_id`, `course_id`, `enrollment_id`, `amount`, `currency`, `payment_method_id`, `payment_status`, `transaction_id`, `payment_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 250.00, 'USD', 1, 'Completed', 'TXN-2025-000001', '2025-01-15 08:30:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 1, 5, 2, 315.00, 'USD', 1, 'Completed', 'TXN-2025-000002', '2025-01-15 08:35:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 1, 10, 3, 360.00, 'USD', 2, 'Completed', 'TXN-2025-000003', '2025-01-20 10:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 2, 7, 4, 342.00, 'USD', 1, 'Completed', 'TXN-2025-000004', '2025-01-15 09:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 2, 15, 5, 380.00, 'USD', 1, 'Completed', 'TXN-2025-000005', '2025-01-15 09:10:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 2, 17, 6, 320.00, 'USD', 2, 'Completed', 'TXN-2025-000006', '2025-01-20 11:30:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 3, 18, 7, 300.00, 'USD', 3, 'Completed', 'TXN-2025-000007', '2025-01-10 14:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(8, 3, 19, 8, 405.00, 'USD', 1, 'Completed', 'TXN-2025-000008', '2025-02-01 10:15:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(9, 3, 1, 9, 250.00, 'USD', 1, 'Completed', 'TXN-2025-000009', '2025-01-15 12:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(10, 4, 5, 10, 315.00, 'USD', 2, 'Completed', 'TXN-2025-000010', '2025-01-10 08:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(11, 4, 6, 11, 400.00, 'USD', 1, 'Completed', 'TXN-2025-000011', '2025-02-01 09:30:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(12, 4, 9, 12, 320.00, 'USD', 1, 'Completed', 'TXN-2025-000012', '2025-02-10 11:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(13, 5, 11, 13, 495.00, 'USD', 1, 'Completed', 'TXN-2025-000013', '2025-02-15 13:45:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(14, 5, 12, 14, 400.00, 'USD', 2, 'Completed', 'TXN-2025-000014', '2025-01-25 16:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(16, 7, 13, 19, 540.00, 'USD', 1, 'Pending', 'TXN-2025-000016', NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
(5, 'Cash', 'Cash payment at office', 1, '2025-11-18 22:21:01');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `question_type`, `question_text`, `points`, `explanation`, `created_at`, `updated_at`) VALUES
(1, 'Multiple Choice', 'What is the correct file extension for Python files?', 2, 'Python files use the .py extension', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 'Multiple Choice', 'Which of the following is a mutable data type in Python?', 2, 'Lists are mutable, while tuples and strings are immutable', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 'True/False', 'Python is a compiled language.', 1, 'Python is an interpreted language, not compiled', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 'Multiple Choice', 'What does HTML stand for?', 2, 'HTML stands for HyperText Markup Language', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 'Multiple Choice', 'Which CSS property is used to change text color?', 2, 'The color property is used to change text color', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 'Short Answer', 'Explain the difference between a list and a tuple in Python.', 5, 'Lists are mutable and use square brackets, tuples are immutable and use parentheses', '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
(18, 5, 'foreground-color', 0, 4);

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `quiz_type`, `time_limit_minutes`, `max_attempts`, `passing_score`, `randomize_questions`, `show_correct_answers`, `available_from`, `available_until`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 5, NULL, 'Python Basics Quiz', 'Test your knowledge of Python fundamentals', 'Practice', 30, 3, 70.00, 1, 1, NULL, NULL, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 5, NULL, 'Python Midterm Exam', 'Comprehensive midterm covering modules 1-3', 'Midterm', 60, 1, 70.00, 1, 0, NULL, NULL, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 7, NULL, 'HTML & CSS Quiz', 'Assessment of HTML5 and CSS3 knowledge', 'Graded', 45, 2, 75.00, 1, 1, NULL, NULL, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 11, NULL, 'Cybersecurity Final Exam', 'Comprehensive final examination', 'Final Exam', 120, 1, 75.00, 1, 0, NULL, NULL, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `attempt_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attempt_number` int(11) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `status` enum('In Progress','Submitted','Graded','Abandoned') DEFAULT 'In Progress',
  `time_spent_minutes` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`attempt_id`, `quiz_id`, `student_id`, `attempt_number`, `started_at`, `submitted_at`, `score`, `status`, `time_spent_minutes`, `ip_address`) VALUES
(1, 1, 1, 1, '2025-01-25 12:00:00', '2025-01-25 12:28:00', 85.00, 'Graded', 28, NULL),
(2, 1, 4, 1, '2025-01-26 08:00:00', '2025-01-26 08:25:00', 92.00, 'Graded', 25, NULL),
(3, 1, 4, 2, '2025-01-27 13:00:00', '2025-01-27 13:20:00', 98.00, 'Graded', 20, NULL),
(4, 3, 2, 1, '2025-02-10 09:00:00', '2025-02-10 09:40:00', 88.00, 'Graded', 40, NULL);

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
(5, 3, 5, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `permissions`, `created_at`) VALUES
(1, 'Super Admin', 'Full system access and control', '{\"all\": true}', '2025-11-18 22:21:01'),
(2, 'Admin', 'Administrative access to manage system', '{\"users\": [\"create\", \"read\", \"update\", \"delete\"], \"courses\": [\"create\", \"read\", \"update\", \"delete\"], \"reports\": [\"read\"]}', '2025-11-18 22:21:01'),
(3, 'Instructor', 'Can create and manage courses', '{\"courses\": [\"create\", \"read\", \"update\"], \"students\": [\"read\"], \"grades\": [\"create\", \"update\"]}', '2025-11-18 22:21:01'),
(4, 'Student', 'Can enroll and access courses', '{\"courses\": [\"read\", \"enroll\"], \"assignments\": [\"submit\"], \"quizzes\": [\"take\"]}', '2025-11-18 22:21:01'),
(5, 'Content Creator', 'Can create course content', '{\"courses\": [\"create\", \"read\", \"update\"], \"content\": [\"create\", \"update\"]}', '2025-11-18 22:21:01');

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
(1, 8, '1998-05-15', 'Male', NULL, 'Lusaka', 'Zambia', NULL, '2024-12-01', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 9, '2000-08-22', 'Female', NULL, 'Ndola', 'Zambia', NULL, '2024-12-05', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 10, '1995-03-10', 'Male', NULL, 'Kitwe', 'Zambia', NULL, '2024-12-10', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 11, '1999-11-30', 'Female', NULL, 'Livingstone', 'Zambia', NULL, '2024-12-15', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 12, '1997-07-18', 'Male', NULL, 'Lusaka', 'Zambia', NULL, '2024-12-20', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 13, '2001-02-25', 'Female', NULL, 'Kabwe', 'Zambia', NULL, '2025-01-02', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 14, '1996-09-12', 'Male', NULL, 'Chingola', 'Zambia', NULL, '2025-01-05', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(8, 15, '1998-12-05', 'Female', NULL, 'Lusaka', 'Zambia', NULL, '2025-01-08', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(9, 16, '2000-04-20', 'Male', NULL, 'Solwezi', 'Zambia', NULL, '2025-01-10', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(10, 17, '1999-06-08', 'Female', NULL, 'Mongu', 'Zambia', NULL, '2025-01-12', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(11, 18, '1997-10-15', 'Male', NULL, 'Kasama', 'Zambia', NULL, '2025-01-15', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(12, 19, '2001-01-30', 'Female', NULL, 'Lusaka', 'Zambia', NULL, '2025-01-18', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01');

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
(7, 3, 1, 18, '2025-04-08', 'Completed Certificate in Entrepreneurship');

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
(1, 'site_name', 'EduTrack LMS', 'String', 'Website name displayed throughout the platform', 1, '2025-11-18 22:21:01'),
(2, 'site_email', 'info@edutrack.edu', 'String', 'Main contact email for the platform', 1, '2025-11-18 22:21:01'),
(3, 'max_file_upload_size', '10', 'Number', 'Maximum file upload size in MB', 1, '2025-11-18 22:21:01'),
(4, 'allow_student_discussion', 'true', 'Boolean', 'Allow students to create discussion topics', 1, '2025-11-18 22:21:01'),
(5, 'certificate_auto_generate', 'true', 'Boolean', 'Automatically generate certificates upon course completion', 1, '2025-11-18 22:21:01'),
(6, 'session_timeout_minutes', '30', 'Number', 'User session timeout in minutes', 1, '2025-11-18 22:21:01'),
(7, 'enable_email_notifications', 'true', 'Boolean', 'Enable email notifications for users', 1, '2025-11-18 22:21:01'),
(8, 'platform_version', '1.0.0', 'String', 'Current platform version', 0, '2025-11-18 22:21:01'),
(9, 'maintenance_mode', 'false', 'Boolean', 'Enable maintenance mode', 1, '2025-11-18 22:21:01'),
(10, 'default_currency', 'USD', 'String', 'Default currency for payments', 1, '2025-11-18 22:21:01');

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
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended','pending') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `avatar_url`, `status`, `email_verified`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@edutrack.edu', '$2y$10$uLoLKLK2Pcv08rJbNgPzhufsUVrhwhFR6IbSeGea6CKRuwU1NRbk.', 'System', 'Administrator', '+260900000000', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 23:23:52'),
(2, 'james.mwanza', 'james.mwanza@edutrack.edu', '$2y$10$uLoLKLK2Pcv08rJbNgPzhufsUVrhwhFR6IbSeGea6CKRuwU1NRbk.', 'James', 'Mwanza', '+260977123456', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-22 11:31:51'),
(3, 'sarah.banda', 'sarah.banda@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Banda', '+260966234567', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 'peter.phiri', 'peter.phiri@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peter', 'Phiri', '+260955345678', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 'grace.chanda', 'grace.chanda@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace', 'Chanda', '+260944456789', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 'michael.siame', 'michael.siame@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Siame', '+260933567890', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 'mercy.zulu', 'mercy.zulu@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mercy', 'Zulu', '+260922678901', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(8, 'john.tembo', 'john.tembo@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Tembo', '+260971111111', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(9, 'mary.lungu', 'mary.lungu@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mary', 'Lungu', '+260972222222', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(10, 'david.sakala', 'david.sakala@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David', 'Sakala', '+260973333333', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(11, 'alice.mulenga', 'alice.mulenga@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Mulenga', '+260974444444', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(12, 'robert.chilufya', 'robert.chilufya@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert', 'Chilufya', '+260975555555', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(13, 'susan.banda', 'susan.banda@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Susan', 'Banda', '+260976666666', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(14, 'patrick.mutale', 'patrick.mutale@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Patrick', 'Mutale', '+260977777777', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(15, 'elizabeth.phiri', 'elizabeth.phiri@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Elizabeth', 'Phiri', '+260978888888', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(16, 'george.kunda', 'george.kunda@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'George', 'Kunda', '+260979999999', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(17, 'jennifer.musonda', 'jennifer.musonda@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jennifer', 'Musonda', '+260970000000', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(18, 'moses.chola', 'moses.chola@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Moses', 'Chola', '+260971234567', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(19, 'ruth.zimba', 'ruth.zimba@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ruth', 'Zimba', '+260972345678', NULL, 'active', 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(25, 'taona', 'taona@gmail.com', '$2y$10$iJ4P8BDECzTdPhAwoP4pXOsf2rSZelFAfogVU6JCj2XfVdSVWHRlW', 'toana', 'ndlovuli', NULL, NULL, 'inactive', 0, NULL, '2025-11-22 09:07:23', '2025-11-22 09:08:14');

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
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `bio`, `phone`, `date_of_birth`, `gender`, `address`, `city`, `country`, `postal_code`, `avatar_url`, `created_at`, `updated_at`) VALUES
(1, 8, NULL, '+260971111111', '1998-05-15', 'Male', NULL, 'Lusaka', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(2, 9, NULL, '+260972222222', '2000-08-22', 'Female', NULL, 'Ndola', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(3, 10, NULL, '+260973333333', '1995-03-10', 'Male', NULL, 'Kitwe', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(4, 11, NULL, '+260974444444', '1999-11-30', 'Female', NULL, 'Livingstone', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(5, 12, NULL, '+260975555555', '1997-07-18', 'Male', NULL, 'Lusaka', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(6, 13, NULL, '+260976666666', '2001-02-25', 'Female', NULL, 'Kabwe', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(7, 14, NULL, '+260977777777', '1996-09-12', 'Male', NULL, 'Chingola', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(8, 15, NULL, '+260978888888', '1998-12-05', 'Female', NULL, 'Lusaka', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(9, 16, NULL, '+260979999999', '2000-04-20', 'Male', NULL, 'Solwezi', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(10, 17, NULL, '+260970000000', '1999-06-08', 'Female', NULL, 'Mongu', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(11, 18, NULL, '+260971234567', '1997-10-15', 'Male', NULL, 'Kasama', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(12, 19, NULL, '+260972345678', '2001-01-30', 'Female', NULL, 'Lusaka', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(16, 2, 'Experienced ICT trainer with 10+ years in corporate training. Microsoft Certified Professional with expertise in Office Suite and digital literacy programs.', '+260977123456', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(17, 3, 'Full-stack developer and certified instructor with passion for teaching modern programming languages. 8 years industry experience in software development.', '+260966234567', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(18, 4, 'Cybersecurity specialist and ethical hacker with CISSP certification. Former network administrator with extensive experience in data security.', '+260955345678', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(19, 5, 'AI/ML researcher and data scientist with PhD in Computer Science. Published researcher with focus on practical applications of machine learning.', '+260944456789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(20, 6, 'Business consultant and entrepreneur with MBA. Specializes in digital transformation, project management, and business strategy.', '+260933567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36'),
(21, 7, 'Award-winning graphic designer and digital marketer. Adobe Certified Expert with 7 years experience in creative industries.', '+260922678901', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36');

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
(6, 6, 3, '2025-11-18 22:21:01', 1),
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
(25, 25, 2, '2025-11-22 09:07:23', NULL);

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
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `posted_by` (`posted_by`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_published` (`is_published`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `graded_by` (`graded_by`),
  ADD KEY `idx_assignment` (`assignment_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_status` (`status`);

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
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD UNIQUE KEY `verification_code` (`verification_code`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `idx_verification_code` (`verification_code`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `fk_courses_instructor` (`instructor_id`);

--
-- Indexes for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`name`),
  ADD KEY `parent_category_id` (`parent_category_id`);

--
-- Indexes for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_course_instructor` (`course_id`,`instructor_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_course_review` (`user_id`,`course_id`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `discussions`
--
ALTER TABLE `discussions`
  ADD PRIMARY KEY (`discussion_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_pinned` (`is_pinned`);

--
-- Indexes for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `parent_reply_id` (`parent_reply_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_discussion` (`discussion_id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD UNIQUE KEY `template_name` (`template_name`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `idx_status` (`enrollment_status`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_module_order` (`module_id`,`display_order`);

--
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lesson_progress` (`enrollment_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `idx_enrollment` (`enrollment_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `lesson_resources`
--
ALTER TABLE `lesson_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `parent_message_id` (`parent_message_id`),
  ADD KEY `idx_recipient` (`recipient_id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_order` (`course_id`,`display_order`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_status` (`payment_status`),
  ADD KEY `idx_transaction` (`transaction_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`);

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
  ADD KEY `idx_question` (`question_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `idx_course` (`course_id`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `attempt_id` (`attempt_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `selected_option_id` (`selected_option_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_quiz` (`quiz_id`),
  ADD KEY `idx_student` (`student_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`quiz_question_id`),
  ADD UNIQUE KEY `unique_quiz_question` (`quiz_id`,`question_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `student_achievements`
--
ALTER TABLE `student_achievements`
  ADD PRIMARY KEY (`achievement_id`),
  ADD KEY `badge_id` (`badge_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_student` (`student_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_payment` (`payment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `badge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_categories`
--
ALTER TABLE `course_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `course_instructors`
--
ALTER TABLE `course_instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
-- AUTO_INCREMENT for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lesson_resources`
--
ALTER TABLE `lesson_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `quiz_question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `student_achievements`
--
ALTER TABLE `student_achievements`
  MODIFY `achievement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `instructors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`),
  ADD CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD CONSTRAINT `course_categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `course_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD CONSTRAINT `course_instructors_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_instructors_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD CONSTRAINT `course_reviews_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussions`
--
ALTER TABLE `discussions`
  ADD CONSTRAINT `discussions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD CONSTRAINT `discussion_replies_ibfk_1` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`discussion_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_replies_ibfk_2` FOREIGN KEY (`parent_reply_id`) REFERENCES `discussion_replies` (`reply_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussion_replies_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `instructors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `lesson_progress_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_resources`
--
ALTER TABLE `lesson_resources`
  ADD CONSTRAINT `lesson_resources_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`message_id`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`) ON DELETE SET NULL;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `quiz_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`attempt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_answers_ibfk_3` FOREIGN KEY (`selected_option_id`) REFERENCES `question_options` (`option_id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_achievements`
--
ALTER TABLE `student_achievements`
  ADD CONSTRAINT `student_achievements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_achievements_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`badge_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_achievements_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
