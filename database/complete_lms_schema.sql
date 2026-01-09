-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 09, 2026 at 02:15 PM
-- Server version: 11.8.3-MariaDB-log
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `slug`, `description`, `short_description`, `category_id`, `instructor_id`, `level`, `language`, `thumbnail_url`, `video_intro_url`, `start_date`, `end_date`, `price`, `discount_price`, `duration_weeks`, `total_hours`, `max_students`, `enrollment_count`, `status`, `is_featured`, `rating`, `total_reviews`, `prerequisites`, `learning_outcomes`, `created_at`, `updated_at`) VALUES
(1, 'Certificate in Microsoft Office Suite', 'microsoft-office-suite', 'Transform your productivity with comprehensive Microsoft Office training. This industry-leading program covers the complete Office Suite including Word, Excel, PowerPoint, Publisher, and essential internet skills. Learn to create professional documents, analyze data with powerful spreadsheets, design compelling presentations, and master desktop publishing. Perfect for professionals seeking to enhance workplace efficiency, students preparing for academic success, or career changers entering the digital workplace. Our hands-on approach ensures you gain practical, job-ready skills that employers value. By course end, you\'ll confidently handle complex office tasks, automate workflows, and present information professionally.', 'Master Word, Excel, PowerPoint, Publisher & Internet skills for professional success', 1, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1587440871875-191322ee64b0?w=800', NULL, '2025-01-15', '2025-04-15', 2500.00, NULL, 8, 64.00, 30, 0, 'published', 1, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(3, 'Certificate in Digital Literacy', 'digital-literacy', 'Bridge the digital divide with essential 21st-century skills. This foundational course equips you with critical digital competencies for modern life and work. Learn professional email communication, effective internet research techniques, cloud storage management, online collaboration tools, and digital safety practices. Understand social media etiquette, basic troubleshooting, file management, and online privacy protection. Ideal for beginners, seniors transitioning to digital workplaces, or anyone looking to build confidence with technology. Our patient, step-by-step instruction ensures no one gets left behind in the digital age. Gain the digital fluency needed to thrive in today\'s connected world.', 'Essential digital skills for navigating modern technology confidently', 1, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=800', NULL, '2025-01-20', '2025-03-20', 850.00, NULL, 2, 16.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(4, 'Certificate in Record Management', 'record-management', 'Master professional records and information management systems that keep organizations running smoothly. Learn comprehensive filing systems, document control procedures, archiving best practices, and compliance with data protection regulations including GDPR. Understand records lifecycle management, retention schedules, digitization processes, and efficient retrieval systems. This course covers both physical and electronic records management, preparing you for roles in government, healthcare, legal, and corporate environments. Gain expertise in maintaining confidentiality, ensuring audit trails, and implementing secure disposal methods. Essential for administrative professionals, office managers, and those pursuing careers in information governance.', 'Professional records and information management for compliance and efficiency', 1, 1, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=800', NULL, '2025-02-15', '2025-05-15', 1500.00, NULL, 6, 48.00, 30, 0, 'published', 0, 4.50, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(5, 'Certificate in Python Programming', 'python-programming', 'Launch your programming career with Python, the world\'s most popular and versatile programming language. This comprehensive course takes you from absolute beginner to confident developer. Master Python fundamentals including variables, data types, control structures, and functions. Progress to advanced topics like object-oriented programming, file handling, error management, and popular libraries including NumPy, Pandas, and Matplotlib. Build real-world projects including data analysis tools, automation scripts, and web applications. Python\'s readability and extensive community support make it perfect for beginners, while its power suits professional developers. Ideal for aspiring programmers, data scientists, automation engineers, or anyone entering tech careers.', 'Learn Python from basics to advanced - the most in-demand programming language', 2, 2, 'Beginner', 'English', 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=800', NULL, '2025-01-10', '2025-04-10', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 1, 4.90, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(6, 'Certificate in Java Programming', 'java-programming', 'Master Java, the enterprise-standard programming language powering billions of devices worldwide. This rigorous course covers Java fundamentals through advanced enterprise development. Learn object-oriented programming principles, Java collections framework, multithreading, exception handling, and JDBC for database connectivity. Build robust, scalable applications using industry best practices. Understand design patterns, unit testing with JUnit, and modern development tools. Java\'s \"write once, run anywhere\" philosophy makes it essential for enterprise applications, Android development, and large-scale systems. Perfect for aspiring software engineers, mobile developers, or professionals transitioning into backend development roles.', 'Master Java for enterprise applications and Android development', 2, 2, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800', NULL, '2025-02-01', '2025-06-01', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(7, 'Certificate in Web Development', 'web-development', 'Build stunning, professional websites from scratch with our comprehensive full-stack web development program. Master the complete web development toolkit: HTML5 for structure, CSS3 for beautiful styling, JavaScript for interactivity, and modern frameworks for responsive design. Learn mobile-first development, CSS Grid and Flexbox layouts, JavaScript ES6+ features, DOM manipulation, and API integration. Create portfolio-worthy projects including responsive business websites, interactive web applications, and dynamic user interfaces. Understand version control with Git, browser developer tools, and deployment processes. This hands-on course prepares you for frontend developer roles or freelance web design careers. No prior experience required.', 'Build modern, responsive websites with HTML5, CSS3, JavaScript & frameworks', 2, 2, 'Beginner', 'English', 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=800', NULL, '2025-01-15', '2025-04-30', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(8, 'Certificate in Mobile App Development', 'mobile-app-development', 'Create professional mobile applications for the world\'s two dominant platforms - Android and iOS. This comprehensive program covers native mobile development using Java/Kotlin for Android and Swift for iOS. Learn mobile UI/UX principles, lifecycle management, data persistence, API integration, and app publishing processes. Build real apps including social media clients, e-commerce apps, and location-based services. Understand mobile-specific challenges like varying screen sizes, touch interfaces, push notifications, and offline functionality. Master app store submission, monetization strategies, and user analytics. Perfect for aspiring mobile developers, entrepreneurs launching apps, or web developers expanding their skillset into the lucrative mobile market.', 'Develop native iOS and Android applications professionally', 2, 2, 'Advanced', 'English', 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800', NULL, '2025-03-01', '2025-07-30', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 1, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(9, 'Certificate in Software Engineering', 'software-engineering-git', 'Learn professional software development practices that distinguish hobbyists from industry professionals. Master essential software engineering methodologies including Agile, Scrum, and DevOps. Gain expertise in version control with Git and GitHub for collaborative development, branching strategies, pull requests, and code reviews. Understand software testing principles, continuous integration/continuous deployment (CI/CD), code quality tools, and documentation best practices. Learn project management, technical communication, and team collaboration workflows used by leading tech companies. This course bridges the gap between writing code and building enterprise-grade software. Essential for junior developers preparing for professional roles or programmers seeking to adopt industry standards.', 'Professional software development with Git, testing, CI/CD & methodologies', 2, 2, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1556075798-4825dfaaf498?w=800', NULL, '2025-02-10', '2025-05-10', 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(10, 'Certificate in Data Analysis', 'data-analysis', 'Transform raw data into actionable business insights with comprehensive data analysis training. Master the complete data analytics workflow: collection, cleaning, analysis, visualization, and reporting. Learn Excel for data manipulation and pivot tables, SQL for database querying, and Python with Pandas for advanced analysis. Create compelling data visualizations using Tableau-style dashboards, statistical analysis techniques, and predictive modeling basics. Understand key performance indicators (KPIs), A/B testing, and data-driven decision making. Work with real-world datasets from business, healthcare, and finance sectors. This practical course prepares you for data analyst, business intelligence, or market research roles. No advanced math required - just curiosity and attention to detail.', 'Analyze data and create insights using Excel, SQL, Python & visualization tools', 3, 4, 'Beginner', 'English', 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800', NULL, '2025-01-20', '2025-04-20', 1500.00, NULL, 8, 64.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(11, 'Certificate in Cyber Security', 'cyber-security', 'Defend organizations against evolving cyber threats with industry-recognized cybersecurity training. This advanced program covers network security fundamentals, ethical hacking techniques, threat analysis, penetration testing, and security best practices. Learn cryptography, firewall configuration, intrusion detection systems, security auditing, and incident response protocols. Master vulnerability assessment tools, security frameworks (NIST, ISO 27001), and compliance requirements. Understand social engineering, malware analysis, and secure coding practices. Gain hands-on experience with Kali Linux, Wireshark, Metasploit, and other professional tools. This comprehensive course prepares you for security analyst, penetration tester, or security consultant roles. Help protect critical infrastructure in our increasingly connected world.', 'Advanced cybersecurity: ethical hacking, network defense & threat analysis', 3, 3, 'Advanced', 'English', 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800', NULL, '2025-02-15', '2025-06-30', 2500.00, NULL, 8, 64.00, 30, 0, 'published', 1, 4.90, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(12, 'Certificate in Database Management Systems', 'database-management', 'Master the backbone of modern applications - professional database design and management. Learn relational database concepts, SQL mastery, database design principles, and normalization techniques. Work with industry-standard systems including MySQL, PostgreSQL, and SQL Server. Understand indexing strategies, query optimization, stored procedures, triggers, and transactions. Learn database administration tasks including backup/recovery, user management, performance tuning, and security. Explore NoSQL basics and data warehousing concepts. Build real-world database projects from e-commerce platforms to content management systems. Essential for backend developers, data engineers, database administrators, or anyone working with data-intensive applications. Transform messy data into organized, efficient database systems.', 'Design, manage & optimize databases using MySQL, PostgreSQL & SQL Server', 3, 3, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800', NULL, '2025-01-25', '2025-05-25', 1500.00, NULL, 6, 48.00, 30, 0, 'published', 0, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(13, 'Certificate in Artificial Intelligence', 'ai-machine-learning', 'Explore the transformative world of Artificial Intelligence and its practical applications across industries. This introductory course demystifies AI concepts including machine learning, neural networks, natural language processing, and computer vision. Understand how AI powers recommendation systems, autonomous vehicles, virtual assistants, and medical diagnostics. Learn AI ethics, bias considerations, and societal implications. Gain hands-on experience with AI tools and platforms without deep mathematical knowledge. Explore real-world case studies from healthcare, finance, retail, and manufacturing. Perfect for business professionals, managers, entrepreneurs, or anyone curious about AI\'s impact on their industry. No programming background required - focus on understanding and applying AI strategically.', 'Understand AI fundamentals and practical applications across industries', 4, 4, 'Advanced', 'English', 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800', NULL, '2025-03-01', '2025-07-01', 850.00, NULL, 3, 24.00, 30, 0, 'published', 1, 4.90, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(14, 'Certificate in Internet of Things', 'internet-of-things', 'Build smart, connected devices with Internet of Things (IoT) technology. Learn IoT architecture, sensor integration, microcontroller programming (Arduino, Raspberry Pi), wireless communication protocols, and cloud connectivity. Understand MQTT, REST APIs, and IoT security challenges. Build practical IoT projects including smart home systems, environmental monitoring, and industrial automation solutions. Explore IoT platforms like AWS IoT, Azure IoT Hub, and ThingSpeak. Learn data collection from sensors, real-time processing, and remote device control. Perfect for electronics enthusiasts, embedded systems developers, or engineers implementing Industry 4.0 solutions. Transform everyday objects into intelligent, internet-connected devices.', 'Build smart IoT solutions with sensors, microcontrollers & cloud integration', 4, 4, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1558346490-a72e53ae2d4f?w=800', NULL, '2025-02-20', '2025-05-20', 450.00, NULL, 12, 60.00, 30, 0, 'archived', 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(15, 'Certificate in Graphic Designing', 'graphic-designing', 'Unleash your creativity with professional graphic design training using industry-standard Adobe Creative Suite. Master Photoshop for photo editing and digital art, Illustrator for vector graphics and logos, and InDesign for layouts and publications. Learn fundamental design principles including typography, color theory, composition, and visual hierarchy. Create professional materials: business cards, brochures, posters, social media graphics, and brand identities. Understand print vs. digital design requirements, file formats, and client collaboration. Build a stunning portfolio showcasing diverse design projects. Perfect for aspiring graphic designers, marketing professionals, small business owners, or creative individuals. Transform ideas into visually compelling designs that capture attention and communicate effectively.', 'Master Adobe Creative Suite for professional graphic design and branding', 5, 6, 'Beginner', 'English', 'https://images.unsplash.com/photo-1626785774625-ddcddc3445e9?w=800', NULL, '2025-01-15', '2025-04-30', 2500.00, NULL, 8, 64.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(16, 'Certificate in Digital & Content Creation', 'digital-content-creation', 'Create engaging multimedia content that educates, entertains, and converts. Master video editing, motion graphics, animation, interactive presentations, and e-learning materials. Learn industry-standard tools for video production, audio editing, screen recording, and multimedia authoring. Understand storytelling techniques, scriptwriting, storyboarding, and visual communication strategies. Create professional content for corporate training, educational institutions, YouTube channels, and social media platforms. Learn content strategy, audience engagement, and multimedia accessibility. Perfect for educators, corporate trainers, content creators, marketing professionals, or entrepreneurs. Transform complex information into captivating visual experiences that resonate with modern audiences.', 'Create engaging multimedia content: videos, animations & e-learning materials', 5, 6, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1611162616475-46b635cb6868?w=800', NULL, '2025-02-05', '2025-05-05', 950.00, NULL, 3, 24.00, 30, 0, 'published', 0, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(17, 'Certificate in Digital Marketing', 'digital-marketing', 'Master the complete digital marketing ecosystem to grow businesses online. Learn search engine optimization (SEO), social media marketing, content marketing, email campaigns, and paid advertising (Google Ads, Facebook Ads). Understand marketing analytics, conversion optimization, customer journey mapping, and ROI measurement. Create comprehensive digital marketing strategies, develop engaging content, and build effective campaigns. Master tools including Google Analytics, Facebook Business Manager, Mailchimp, and keyword research platforms. Learn influencer marketing, affiliate marketing, and marketing automation. Perfect for marketing professionals, business owners, entrepreneurs, or anyone entering the digital marketing field. Drive traffic, generate leads, and grow revenue through strategic online marketing.', 'Master SEO, social media, content marketing & digital advertising strategies', 5, 6, 'Beginner', 'English', 'https://images.unsplash.com/photo-1557838923-2985c318be48?w=800', NULL, '2025-01-20', '2025-04-20', 950.00, NULL, 3, 24.00, 30, 0, 'published', 1, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(18, 'Certificate in Entrepreneurship', 'entrepreneurship', 'Transform your business idea into reality with comprehensive entrepreneurship training. Learn business planning, market research, financial management, and growth strategies. Understand business registration, legal structures, taxation, and regulatory compliance. Master customer validation, minimum viable product development, and lean startup methodologies. Learn sales strategies, negotiation skills, and customer relationship management. Understand funding options including bootstrapping, angel investment, venture capital, and crowdfunding. Develop essential entrepreneurial skills: leadership, decision-making, risk management, and resilience. Create a comprehensive business plan investors will take seriously. Perfect for aspiring entrepreneurs, freelancers transitioning to business ownership, or intrapreneurs driving innovation within organizations. Turn your passion into a profitable venture.', 'Launch and grow your business: planning, financing, marketing & operations', 6, 5, 'Beginner', 'English', 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800', NULL, '2025-01-10', '2025-04-10', 2500.00, NULL, 11, 88.00, 30, 0, 'published', 0, 4.60, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(19, 'Certificate in Project Management', 'project-management', 'Lead successful projects using professional project management methodologies. Master PMBOK framework, Agile principles, and Scrum practices. Learn project initiation, planning, execution, monitoring, and closure. Understand scope management, time estimation, budget control, risk management, and stakeholder communication. Use project management tools including Gantt charts, Kanban boards, and collaboration platforms. Learn team leadership, conflict resolution, and change management. Understand earned value management, critical path analysis, and quality assurance. Prepare for PMP or Agile certifications. Perfect for project managers, team leaders, coordinators, or professionals managing complex initiatives. Deliver projects on time, within budget, and exceeding stakeholder expectations.', 'Professional project management: PMBOK, Agile, Scrum & leadership skills', 6, 5, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800', NULL, '2025-02-01', '2025-06-01', 2500.00, NULL, 8, 64.00, 30, 0, 'published', 1, 4.80, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(20, 'Certificate in Financial Technology', 'financial-technology', 'Explore the digital revolution transforming financial services worldwide. Understand blockchain technology, cryptocurrency fundamentals, digital payments systems, mobile money platforms, and digital banking. Learn about fintech ecosystems, regulatory technology (RegTech), peer-to-peer lending, robo-advisors, and insurtech innovations. Understand payment gateways, digital wallets, and cross-border transactions. Explore real-world case studies from M-Pesa, PayPal, Stripe, and emerging fintech startups. Learn about financial inclusion, cybersecurity in finance, and future trends. Perfect for banking professionals, entrepreneurs entering fintech, financial advisors adapting to digital trends, or anyone curious about money\'s digital future. Position yourself at the intersection of finance and technology.', 'Explore digital payments, blockchain, cryptocurrency & digital banking innovations', 6, 5, 'Advanced', 'English', 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=800', NULL, '2025-03-01', '2025-06-15', 1200.00, NULL, 3, 24.00, 30, 0, 'published', 0, 4.70, 0, NULL, NULL, '2025-11-18 22:21:01', '2025-12-18 18:05:42'),
(24, 'Certificate in ICT Support & Hardware Repair', 'ict-support-hardware-repair', 'Become a skilled ICT technician with comprehensive computer hardware training. Learn computer architecture, component identification, hardware installation, troubleshooting methodologies, and repair techniques. Master operating system installation (Windows, Linux), driver management, and system optimization. Understand common hardware problems: motherboard issues, power supply failures, storage problems, and peripheral connectivity. Learn diagnostic tools, preventive maintenance, data recovery basics, and customer service skills. Gain hands-on experience assembling PCs, upgrading components, and resolving technical issues. Understand mobile device repair fundamentals. Perfect for aspiring IT support professionals, computer technicians, or entrepreneurs starting repair businesses. Provide essential technical support that keeps organizations running smoothly.', 'Computer hardware repair, troubleshooting & technical support expertise', 1, 1, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 1, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(25, 'Certificate in Computer Studies', 'computer-studies', 'Build a solid foundation in computer science through this comprehensive introductory program. Explore fundamental computing concepts, computer architecture, operating systems, networking basics, and software applications. Understand binary systems, logic gates, algorithms, and problem-solving techniques. Learn essential applications including word processing, spreadsheets, presentations, and internet usage. Gain exposure to programming concepts, database fundamentals, and cybersecurity awareness. Perfect foundation for further ICT studies or professional certifications. Ideal for students beginning their technology journey, career changers entering IT fields, or professionals needing comprehensive computer literacy. Develop the core knowledge essential for any technology-related career path.', 'Comprehensive computer fundamentals: ideal foundation for ICT careers', 1, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800', NULL, NULL, NULL, 3850.00, NULL, 12, 96.00, 30, 0, 'published', 1, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(26, 'Certificate in Computer Science General', 'computer-science-general', 'Master both theoretical and practical aspects of computer science through this balanced curriculum. Learn computer organization, system architecture, programming fundamentals, data structures, algorithms, and software engineering principles. Understand operating systems, computer networks, database systems, and web technologies. Gain programming experience in multiple languages while learning core CS concepts like recursion, sorting algorithms, and object-oriented design. Explore hardware-software interaction, memory management, and system optimization. Perfect for students pursuing computer science careers, professionals transitioning into technical roles, or those seeking comprehensive IT knowledge. Build the versatile skillset needed for software development, systems administration, or further specialization.', 'Balanced computer science: software development & hardware fundamentals', 1, 1, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800', NULL, NULL, NULL, 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(27, 'Certificate in Information Technology', 'information-technology', 'Master enterprise IT fundamentals preparing you for professional IT positions. Learn network administration, systems infrastructure, IT security principles, and technical support procedures. Understand TCP/IP networking, routing, switching, Active Directory, and server management. Master troubleshooting methodologies, help desk operations, and customer service excellence. Learn virtualization, cloud computing basics, backup solutions, and disaster recovery. Understand IT service management (ITIL), documentation practices, and change management. Gain practical experience with Windows Server, Linux administration, and network configuration. Perfect preparation for entry-level IT roles including help desk technician, systems administrator, or network support specialist. Build the practical skills IT departments need.', 'Enterprise IT fundamentals: networking, systems administration & support', 1, 1, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 1, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(28, 'Certificate in Computer & Business Handling', 'computer-business-handling', 'Bridge technology and business operations with essential computer skills for business professionals. Master Microsoft Office Suite for business: advanced Excel for financial analysis, Word for professional documentation, PowerPoint for business presentations, and Outlook for communication management. Learn business email etiquette, calendar management, task organization, and digital collaboration. Understand basic accounting software, data entry best practices, report generation, and business communication standards. Learn office automation, time management with digital tools, and remote work technologies. Perfect for administrative assistants, office managers, receptionists, bookkeepers, or anyone handling business operations. Boost workplace productivity and professional competence.', 'Essential business computer skills for administrative and office professionals', 1, 1, 'Beginner', 'English', 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800', NULL, NULL, NULL, 1200.00, NULL, 4, 32.00, 30, 0, 'published', 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(29, 'Certificate in C++ Programming', 'cpp-programming', 'Master C++, the powerful programming language behind operating systems, game engines, and high-performance applications. Learn C++ fundamentals, pointers, memory management, and object-oriented programming. Understand templates, Standard Template Library (STL), exception handling, and file I/O. Master advanced topics including operator overloading, inheritance, polymorphism, and design patterns. Learn efficient algorithms, data structures implementation, and performance optimization. Understand modern C++ features (C++11/14/17/20). Build projects including games, system utilities, and performance-critical applications. Perfect for aspiring game developers, systems programmers, or software engineers needing high-performance computing skills. C++ powers everything from embedded systems to AAA video games.', 'Master C++ for high-performance applications, game development & systems programming', 2, 2, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800', NULL, NULL, NULL, 3000.00, NULL, 12, 96.00, 30, 0, 'published', 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(30, 'Certificate in Sales & Marketing', 'sales-marketing', 'Master the art and science of sales and marketing to drive business growth. Learn customer psychology, persuasion techniques, relationship building, and consultative selling. Understand marketing fundamentals: market segmentation, targeting, positioning, and the marketing mix. Master lead generation, sales funnel optimization, objection handling, and closing techniques. Learn customer relationship management (CRM), sales forecasting, and territory management. Understand brand building, competitive analysis, and market research methods. Develop essential skills: negotiation, presentation, communication, and customer service excellence. Create effective marketing campaigns and sales strategies. Perfect for sales professionals, marketing coordinators, business development representatives, or entrepreneurs growing their ventures. Turn prospects into loyal customers.', 'Master sales techniques, marketing strategies & customer relationship management', 6, 5, 'Beginner', 'English', 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(31, 'Certificate in Monitoring & Evaluation', 'monitoring-evaluation', 'Become an expert in program assessment and performance measurement with professional M&E training. Learn monitoring frameworks, evaluation methodologies, indicator development, and data collection techniques. Master logical frameworks, theory of change, results-based management, and impact assessment. Understand quantitative and qualitative research methods, sampling techniques, survey design, and data analysis. Learn M&E planning, reporting standards, stakeholder engagement, and lessons learned documentation. Use M&E software and tools for data visualization and reporting. Perfect for program managers, development professionals, NGO staff, government officials, or consultants working in international development, public health, education, or social programs. Demonstrate program effectiveness and drive evidence-based decision making.', 'Professional M&E: frameworks, data collection, analysis & reporting', 6, 5, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1551836022-deb4988cc6c0?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(32, 'Certificate in Purchasing & Supply', 'purchasing-supply', 'Master professional procurement and supply chain management for organizational efficiency. Learn strategic sourcing, vendor selection, contract negotiation, and supplier relationship management. Understand purchase order processing, inventory control systems, just-in-time delivery, and warehouse management. Master logistics coordination, demand forecasting, and supply chain optimization. Learn procurement ethics, tender processes, compliance requirements, and risk management. Understand cost analysis, value for money principles, and purchase budgeting. Explore e-procurement systems, supply chain software, and procurement best practices. Perfect for purchasing officers, supply chain coordinators, inventory managers, or business owners managing procurement. Reduce costs while maintaining quality and reliability.', 'Professional procurement, supply chain management & logistics expertise', 6, 5, 'Intermediate', 'English', 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(33, 'Certificate in E-Commerce & Online Business', 'ecommerce-online-business', 'Launch and grow a successful online business in the booming e-commerce market. Learn e-commerce platforms (Shopify, WooCommerce, Magento), online store setup, product listing optimization, and digital storefront design. Master payment gateway integration, shipping logistics, inventory management, and order fulfillment. Understand online customer service, returns management, and reputation building. Learn digital marketing for e-commerce: SEO, social media advertising, email marketing, and conversion optimization. Explore dropshipping, print-on-demand, and various e-commerce business models. Understand legal requirements, taxation, and international selling. Perfect for entrepreneurs starting online stores, retailers moving online, or anyone entering the e-commerce industry. Build your profitable online business empire.', 'Launch and scale your online store: e-commerce platforms & digital selling strategies', 6, 5, 'Beginner', 'English', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800', NULL, NULL, NULL, 950.00, NULL, 3, 24.00, 30, 0, 'published', 1, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42'),
(34, 'Certificate in Secretarial & Office Management', 'secretarial-office-management', 'Become an indispensable administrative professional with comprehensive secretarial and office management training. Master advanced typing skills, business correspondence, minutes taking, and professional communication. Learn office organization, filing systems, records management, and document control. Understand meeting coordination, travel arrangements, calendar management, and executive support. Master business etiquette, telephone techniques, customer service excellence, and professional image. Learn time management, priority setting, and workflow optimization. Understand office technology, database management, and basic bookkeeping. Perfect for executive assistants, office administrators, personal assistants, or professionals in administrative roles. Become the organizational backbone that enables business success.', 'Professional secretarial training: office management, typing & administrative excellence', 6, 5, 'Beginner', 'English', 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=800', NULL, NULL, NULL, 2500.00, NULL, 8, 64.00, 30, 0, 'published', 0, 0.00, 0, NULL, NULL, '2025-11-24 11:25:37', '2025-12-18 18:05:42');

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
(6, 'Business & Management', 'Business administration, entrepreneurship, project management, and professional development courses', NULL, NULL, 6, 1, '2025-11-18 22:21:01', '2025-11-18 22:21:01', '#333333');

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
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `attachments` text DEFAULT NULL,
  `status` enum('pending','processing','sent','failed') DEFAULT 'pending',
  `attempts` tinyint(3) DEFAULT 0,
  `priority` tinyint(3) DEFAULT 0,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `last_attempt` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `certificate_blocked` tinyint(1) DEFAULT 0 COMMENT 'Certificate blocked until fully paid',
  `last_accessed` timestamp NULL DEFAULT NULL,
  `total_time_spent` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `student_id`, `course_id`, `enrolled_at`, `start_date`, `progress`, `final_grade`, `enrollment_status`, `payment_status`, `amount_paid`, `completion_date`, `certificate_issued`, `certificate_blocked`, `last_accessed`, `total_time_spent`, `created_at`, `updated_at`) VALUES
(1, 8, 1, 1, '2025-01-15', '2025-01-15', 100.00, 92.50, 'Enrolled', 'completed', 250.00, '2025-04-10', 1, 1, NULL, 2880, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(2, 8, 1, 5, '2025-01-15', '2025-01-16', 75.00, NULL, 'Enrolled', 'completed', 315.00, NULL, 0, 1, NULL, 2700, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(3, 8, 1, 10, '2025-01-20', '2025-01-21', 45.00, NULL, 'Enrolled', 'completed', 360.00, NULL, 0, 1, NULL, 1620, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(4, 9, 2, 7, '2025-01-15', '2025-01-15', 100.00, 88.00, 'Enrolled', 'completed', 342.00, '2025-04-25', 1, 1, NULL, 4200, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(5, 9, 2, 15, '2025-01-15', '2025-01-16', 85.00, NULL, 'Enrolled', 'completed', 380.00, NULL, 0, 1, NULL, 2856, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(6, 9, 2, 17, '2025-01-20', '2025-01-21', 60.00, NULL, 'In Progress', 'completed', 320.00, NULL, 0, 1, NULL, 1728, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(7, 10, 3, 18, '2025-01-10', '2025-01-10', 100.00, 95.00, 'Enrolled', 'completed', 300.00, '2025-04-08', 1, 1, NULL, 2880, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(8, 10, 3, 19, '2025-02-01', '2025-02-02', 30.00, NULL, 'Enrolled', 'completed', 405.00, NULL, 0, 1, NULL, 1152, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(9, 10, 3, 1, '2025-01-15', '2025-01-15', 100.00, 87.50, 'Enrolled', 'completed', 250.00, '2025-04-12', 1, 1, NULL, 2640, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(10, 11, 4, 5, '2025-01-10', '2025-01-10', 100.00, 91.00, 'Enrolled', 'completed', 315.00, '2025-04-05', 1, 1, NULL, 3600, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(11, 11, 4, 6, '2025-02-01', '2025-02-02', 50.00, NULL, 'Enrolled', 'completed', 400.00, NULL, 0, 1, NULL, 2400, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(12, 11, 4, 9, '2025-02-10', '2025-02-11', 25.00, NULL, 'Enrolled', 'completed', 320.00, NULL, 0, 1, NULL, 720, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(13, 12, 5, 11, '2025-02-15', '2025-02-16', 40.00, NULL, 'Enrolled', 'completed', 495.00, NULL, 0, 1, NULL, 2160, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(14, 12, 5, 12, '2025-01-25', '2025-01-26', 70.00, NULL, 'Enrolled', 'completed', 400.00, NULL, 0, 1, NULL, 2688, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(16, 13, 6, 3, '2025-01-20', '2025-01-20', 35.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 672, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(17, 13, 6, 17, '2025-01-20', '2025-01-21', 40.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1152, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(18, 14, 7, 13, '2025-03-01', '2025-03-02', 15.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 720, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(19, 14, 7, 8, '2025-03-01', '2025-03-02', 20.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1200, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(20, 15, 8, 18, '2025-01-10', '2025-01-10', 90.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 2592, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(21, 15, 8, 3, '2025-01-20', '2025-01-20', 100.00, 94.00, 'Enrolled', 'pending', 0.00, '2025-03-15', 1, 1, NULL, 1920, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(22, 16, 9, 10, '2025-01-20', '2025-01-21', 65.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 2340, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(23, 16, 9, 12, '2025-01-25', '2025-01-26', 40.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1536, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(24, 17, 10, 15, '2025-01-15', '2025-01-16', 80.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 2688, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(25, 17, 10, 16, '2025-02-05', '2025-02-06', 45.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1296, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(26, 18, 11, 1, '2025-01-15', '2025-01-16', 55.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 1584, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(27, 18, 11, 4, '2025-02-15', '2025-02-16', 30.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 864, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(28, 19, 12, 5, '2025-01-20', '2025-01-21', 10.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 360, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(29, 19, 12, 3, '2025-01-20', '2025-01-21', 15.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 288, '2025-11-18 22:21:01', '2025-12-09 13:26:07'),
(30, 26, 13, 1, '2025-11-23', '2025-11-23', 0.00, NULL, 'Enrolled', 'pending', 0.00, NULL, 0, 1, NULL, 0, '2025-11-23 11:45:51', '2025-12-09 13:26:07'),
(31, 39, 33, 1, '2025-12-28', '2025-12-28', 0.00, NULL, 'In Progress', '', 0.00, NULL, 0, 1, NULL, 0, '2025-12-28 18:42:19', '2025-12-28 18:42:19'),
(32, 43, 37, 1, '2026-01-09', NULL, 0.00, NULL, 'Enrolled', 'completed', 500.00, NULL, 0, 1, NULL, 0, '2026-01-09 04:40:44', '2026-01-09 04:40:44'),
(33, 40, 34, 1, '2026-01-09', NULL, 0.00, NULL, 'Enrolled', 'completed', 500.00, NULL, 0, 1, NULL, 0, '2026-01-09 04:40:44', '2026-01-09 04:40:44'),
(34, 43, 37, 1, '2026-01-09', NULL, 0.00, NULL, 'Enrolled', 'completed', 0.00, NULL, 0, 1, NULL, 0, '2026-01-09 04:42:00', '2026-01-09 04:42:00'),
(35, 40, 34, 1, '2026-01-09', NULL, 0.00, NULL, 'Enrolled', 'completed', 0.00, NULL, 0, 1, NULL, 0, '2026-01-09 04:42:00', '2026-01-09 04:42:00');

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
(33, 31, 43, 1, 2500.00, 0.00, 'ZMW', 'partial', NULL, 'Betty - Tuition Plan', '2026-01-09 04:40:44', '2026-01-09 04:40:44'),
(34, 32, 40, 1, 2500.00, 0.00, 'ZMW', 'partial', NULL, 'Sharon - Tuition Plan', '2026-01-09 04:40:44', '2026-01-09 04:40:44'),
(35, 34, 43, 1, 2500.00, 500.00, 'ZMW', 'partial', NULL, 'Betty - Tuition Plan', '2026-01-09 04:42:00', '2026-01-09 04:42:00'),
(36, 35, 40, 1, 2500.00, 500.00, 'ZMW', 'partial', NULL, 'Sharon - Tuition Plan', '2026-01-09 04:42:00', '2026-01-09 04:42:00');

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
(10, 31, 'Instructor at Edutrack Computer Training College', 'General', NULL, NULL, NULL, 0.00, 0, 0, 1, '2025-12-25 09:40:13', '2025-12-25 09:40:13');

-- --------------------------------------------------------

--
-- Table structure for table `lenco_transactions`
--

CREATE TABLE `lenco_transactions` (
  `id` int(11) NOT NULL,
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
  `paid_at` datetime DEFAULT NULL COMMENT 'When payment was confirmed',
  `expires_at` datetime DEFAULT NULL COMMENT 'When this payment request expires',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional transaction metadata' CHECK (json_valid(`metadata`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks Lenco payment gateway transactions';

-- --------------------------------------------------------

--
-- Table structure for table `lenco_webhook_logs`
--

CREATE TABLE `lenco_webhook_logs` (
  `id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL COMMENT 'Webhook event type',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Raw webhook payload' CHECK (json_valid(`payload`)),
  `signature` varchar(255) DEFAULT NULL COMMENT 'Webhook signature',
  `signature_valid` tinyint(1) DEFAULT NULL COMMENT 'Was signature valid?',
  `processed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Has this been processed?',
  `error_message` text DEFAULT NULL COMMENT 'Error if processing failed',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Source IP address',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs incoming Lenco webhooks for debugging';

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
(20, 8, 'Responsive Design with Media Queries', 'Creating responsive layouts', 'Video', 45, 4, NULL, NULL, 0, 1, 20, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(21, 11, 'Welcome to Microsoft Office Suite', '### Welcome to the Course!\r\n\r\n**Learning Objectives:**\r\n- Understand the components of Microsoft Office Suite\r\n- Learn about Word, Excel, PowerPoint, and Publisher\r\n- Identify common use cases for each application\r\n\r\n**What You Will Learn:**\r\nIn this lesson, we provide an overview of the Microsoft Office Suite and introduce you to each application you\'ll be mastering throughout this course.\r\n\r\n**Get Started:**\r\n1. Watch the welcome video below\r\n2. Download the course syllabus (PDF)\r\n3. Download the Office Suite overview guide\r\n4. Review the getting started checklist\r\n\r\n**Note:** All detailed course materials are available as downloadable resources below the video.', 'Video', 15, 1, 'YOUR_YOUTUBE_VIDEO_ID', NULL, 1, 1, 5, '2025-12-25 19:40:26', '2025-12-25 19:40:26'),
(22, 11, 'Common Interface Elements', '### Mastering the Office Interface\r\n\r\n**Learning Objectives:**\r\n- Navigate the Ribbon interface\r\n- Use the Quick Access Toolbar\r\n- Understand the File menu (Backstage View)\r\n- Learn essential keyboard shortcuts\r\n\r\n**What You\'ll Learn:**\r\nAll Microsoft Office applications share a common interface design. Understanding these elements will help you work efficiently across Word, Excel, PowerPoint, and Publisher.\r\n\r\n**Study Materials:**\r\nDownload the interface guide (PDF) and the keyboard shortcuts reference sheet. Practice identifying interface elements in the practice workbook.\r\n\r\n**Practice Exercise:**\r\nAfter watching the video and reviewing the materials, complete the interface identification exercise (included in the practice file).', 'Video', 25, 2, 'YOUR_YOUTUBE_VIDEO_ID_2', NULL, 1, 1, 15, '2025-12-25 19:40:26', '2025-12-25 19:40:26'),
(23, 11, 'File Management Basics', '### Working with Office Files\r\n\r\n**Learning Objectives:**\r\n- Create, save, and open documents\r\n- Understand file formats (.docx, .xlsx, .pptx)\r\n- Organize files effectively\r\n- Use AutoSave and version history\r\n\r\n**Key Topics:**\r\n- Creating new documents\r\n- Saving with descriptive names\r\n- File format options\r\n- Folder organization best practices\r\n\r\n**Practice:**\r\nDownload the file management exercise kit which includes practice files and a folder structure template.', 'Video', 20, 3, 'YOUR_YOUTUBE_VIDEO_ID_3', NULL, 0, 1, 10, '2025-12-25 19:40:26', '2025-12-25 19:40:26'),
(24, 12, 'Introduction to Microsoft Word', '### Getting Started with Word\r\n\r\n**What You Will Learn:**\r\n- The Word interface and views\r\n- Creating your first document\r\n- Basic text entry and editing\r\n- Saving and printing documents\r\n\r\n**Course Materials:**\r\nThis lesson includes comprehensive PDF guides and practice documents. Download all materials before starting the video tutorial.\r\n\r\n**Practice Exercise:**\r\nComplete the \"My First Document\" exercise using the provided template.', 'Video', 30, 1, 'YOUR_YOUTUBE_VIDEO_ID_4', NULL, 0, 1, 10, '2025-12-25 19:40:26', '2025-12-25 19:40:26'),
(25, 12, 'Character and Paragraph Formatting', '### Making Your Documents Look Professional\r\n\r\n**Learning Objectives:**\r\n- Apply character formatting (font, size, color, bold, italic)\r\n- Use paragraph alignment and spacing\r\n- Work with bullets and numbering\r\n- Apply borders and shading\r\n\r\n**What\'s Included:**\r\n- Video tutorial demonstrating all formatting techniques\r\n- Formatting reference guide (PDF)\r\n- Practice documents with exercises\r\n- Professional document examples\r\n\r\n**Practice Assignment:**\r\nFormat the provided unformatted document using the techniques learned. Compare your result with the example solution.', 'Video', 40, 2, 'YOUR_YOUTUBE_VIDEO_ID_5', NULL, 0, 1, 20, '2025-12-25 19:40:26', '2025-12-25 19:40:26'),
(26, 13, 'Introduction to Microsoft Excel', '### Excel Fundamentals\r\n\r\n**Learning Objectives:**\r\n- Navigate the Excel interface\r\n- Understand workbooks, worksheets, cells, rows, and columns\r\n- Enter and edit data\r\n- Use basic Excel features\r\n\r\n**Course Materials:**\r\nThis lesson includes:\r\n- Excel interface guide (PDF)\r\n- Practice workbook with guided exercises\r\n- Sample data files for practice\r\n- Quick reference sheet\r\n\r\n**Get Started:**\r\nWatch the introduction video, then download the practice workbook and follow along with the exercises.', 'Video', 30, 1, 'YOUR_YOUTUBE_VIDEO_ID_6', NULL, 0, 1, 10, '2025-12-25 19:40:26', '2025-12-25 19:40:26'),
(27, 13, 'Basic Formulas and Functions', '### Calculating with Excel\r\n\r\n**What You Will Learn:**\r\n- Write basic formulas using arithmetic operators\r\n- Use SUM, AVERAGE, COUNT, MAX, MIN functions\r\n- Understand cell references (relative vs. absolute)\r\n- Copy formulas effectively\r\n\r\n**Practice Materials:**\r\n- Formula reference guide (PDF)\r\n- Practice workbook with exercises\r\n- Real-world examples (budget, grade book)\r\n- Answer keys for self-checking\r\n\r\n**Assignment:**\r\nComplete the formula exercises in the practice workbook. Check your answers with the provided solution file.', 'Video', 45, 2, 'YOUR_YOUTUBE_VIDEO_ID_7', NULL, 0, 1, 20, '2025-12-25 19:40:26', '2025-12-25 19:40:26');

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
(26, 27, 'Practice Exercise Solutions', 'Answer key for all formula exercises', 'Spreadsheet', 'https://example.com/materials/formula-answers.xlsx', 250, 0, '2025-12-25 19:40:26');

-- --------------------------------------------------------

--
-- Table structure for table `live_sessions`
--

CREATE TABLE `live_sessions` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `live_session_attendance`
--

CREATE TABLE `live_session_attendance` (
  `id` int(11) NOT NULL,
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
(10, 7, 'Modern JavaScript', 'ES6+ features, async programming, and APIs', 4, 600, 1, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(11, 1, 'Introduction to Microsoft Office Suite', 'Overview of Microsoft Office applications and common features', 1, 240, 1, NULL, '2025-12-25 19:40:26', '2025-12-25 19:40:26'),
(12, 1, 'Microsoft Word - Document Creation', 'Master document creation, formatting, and basic editing in Microsoft Word', 2, 480, 1, NULL, '2025-12-25 19:40:26', '2025-12-25 19:40:26'),
(13, 1, 'Microsoft Excel - Fundamentals', 'Introduction to spreadsheets, data entry, basic formulas, and formatting', 4, 540, 1, NULL, '2025-12-25 19:40:26', '2025-12-25 19:40:26');

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
(7, 10, 'Payment Successful', 'Your payment of $380.00 for Graphic Designing course was successful', 'Success', 1, NULL, '/payments/5', NULL, NULL, '2025-11-18 22:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `payment_plan_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method_id` int(11) DEFAULT NULL,
  `payment_type` enum('registration','course_fee','partial_payment') DEFAULT 'course_fee',
  `recorded_by` int(11) DEFAULT NULL COMMENT 'User ID of admin/finance who recorded cash payment',
  `payment_status` enum('Pending','Completed','Failed','Refunded','Cancelled') DEFAULT 'Pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `student_id`, `course_id`, `enrollment_id`, `payment_plan_id`, `amount`, `currency`, `payment_method_id`, `payment_type`, `recorded_by`, `payment_status`, `transaction_id`, `phone_number`, `notes`, `payment_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, 250.00, 'USD', 1, 'course_fee', NULL, 'Completed', 'TXN-2025-000001', NULL, NULL, '2025-01-15 08:30:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(2, 1, 5, 2, NULL, 315.00, 'USD', 1, 'course_fee', NULL, 'Completed', 'TXN-2025-000002', NULL, NULL, '2025-01-15 08:35:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(3, 1, 10, 3, NULL, 360.00, 'USD', 2, 'course_fee', NULL, 'Completed', 'TXN-2025-000003', NULL, NULL, '2025-01-20 10:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(4, 2, 7, 4, NULL, 342.00, 'USD', 1, 'course_fee', NULL, 'Completed', 'TXN-2025-000004', NULL, NULL, '2025-01-15 09:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(5, 2, 15, 5, NULL, 380.00, 'USD', 1, 'course_fee', NULL, 'Completed', 'TXN-2025-000005', NULL, NULL, '2025-01-15 09:10:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(6, 2, 17, 6, NULL, 320.00, 'USD', 2, 'course_fee', NULL, 'Completed', 'TXN-2025-000006', NULL, NULL, '2025-01-20 11:30:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(7, 3, 18, 7, NULL, 300.00, 'USD', 3, 'course_fee', NULL, 'Completed', 'TXN-2025-000007', NULL, NULL, '2025-01-10 14:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(8, 3, 19, 8, NULL, 405.00, 'USD', 1, 'course_fee', NULL, 'Completed', 'TXN-2025-000008', NULL, NULL, '2025-02-01 10:15:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(9, 3, 1, 9, NULL, 250.00, 'USD', 1, 'course_fee', NULL, 'Completed', 'TXN-2025-000009', NULL, NULL, '2025-01-15 12:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(10, 4, 5, 10, NULL, 315.00, 'USD', 2, 'course_fee', NULL, 'Completed', 'TXN-2025-000010', NULL, NULL, '2025-01-10 08:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(11, 4, 6, 11, NULL, 400.00, 'USD', 1, 'course_fee', NULL, 'Completed', 'TXN-2025-000011', NULL, NULL, '2025-02-01 09:30:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(12, 4, 9, 12, NULL, 320.00, 'USD', 1, 'course_fee', NULL, 'Completed', 'TXN-2025-000012', NULL, NULL, '2025-02-10 11:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(13, 5, 11, 13, NULL, 495.00, 'USD', 1, 'course_fee', NULL, 'Completed', 'TXN-2025-000013', NULL, NULL, '2025-02-15 13:45:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(14, 5, 12, 14, NULL, 400.00, 'USD', 2, 'course_fee', NULL, 'Completed', 'TXN-2025-000014', NULL, NULL, '2025-01-25 16:00:00', '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(16, 7, 13, 19, NULL, 540.00, 'USD', 1, 'course_fee', NULL, 'Pending', 'TXN-2025-000016', NULL, NULL, NULL, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(17, 13, 1, 30, NULL, 250.00, 'ZMW', NULL, 'course_fee', NULL, 'Completed', '1234', NULL, NULL, '2025-11-25 18:37:20', '2025-11-23 11:45:51', '2025-11-25 16:37:20'),
(18, 33, 1, 31, 32, 1000.00, 'ZMW', NULL, 'partial_payment', 1, 'Completed', 'TXN-20251228-039', NULL, 'Initial payment for Microsoft Office Suite - K1000', '2025-12-28 18:42:19', '2025-12-28 18:42:19', '2025-12-28 18:42:19'),
(20, 37, 1, 34, 35, 500.00, 'ZMW', NULL, 'partial_payment', 1, 'Completed', NULL, NULL, 'Tuition payment - Betty', '2026-01-09 04:42:00', '2026-01-09 04:42:00', '2026-01-09 04:42:00'),
(21, 34, 1, 35, 36, 500.00, 'ZMW', NULL, 'partial_payment', 1, 'Completed', NULL, NULL, 'Tuition payment - Sharon', '2026-01-09 04:42:00', '2026-01-09 04:42:00', '2026-01-09 04:42:00');

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
  `ip_address` varchar(45) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`attempt_id`, `quiz_id`, `student_id`, `attempt_number`, `started_at`, `submitted_at`, `score`, `status`, `time_spent_minutes`, `ip_address`, `id`, `completed_at`) VALUES
(1, 1, 1, 1, '2025-01-25 12:00:00', '2025-01-25 12:28:00', 85.00, 'Graded', 28, NULL, 1, NULL),
(2, 1, 4, 1, '2025-01-26 08:00:00', '2025-01-26 08:25:00', 92.00, 'Graded', 25, NULL, 2, NULL),
(3, 1, 4, 2, '2025-01-27 13:00:00', '2025-01-27 13:20:00', 98.00, 'Graded', 20, NULL, 3, NULL),
(4, 3, 2, 1, '2025-02-10 09:00:00', '2025-02-10 09:40:00', 88.00, 'Graded', 40, NULL, 4, NULL);

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
-- Table structure for table `registration_fees`
--

CREATE TABLE `registration_fees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 150.00,
  `currency` varchar(3) NOT NULL DEFAULT 'ZMW',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_method` enum('bank_transfer','bank_deposit') NOT NULL DEFAULT 'bank_deposit',
  `bank_reference` varchar(100) DEFAULT NULL COMMENT 'Bank deposit slip or transfer reference',
  `bank_name` varchar(100) DEFAULT NULL,
  `deposit_date` date DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL COMMENT 'Admin/Finance user who verified the payment',
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `registration_fees`
--

INSERT INTO `registration_fees` (`id`, `user_id`, `student_id`, `amount`, `currency`, `payment_status`, `payment_method`, `bank_reference`, `bank_name`, `deposit_date`, `verified_by`, `verified_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 39, 33, 150.00, 'ZMW', 'completed', 'bank_deposit', 'REG-039-001', NULL, '2025-12-28', 1, '2025-12-28 18:42:19', 'Registration fee payment for Given Mutwena', '2025-12-28 18:42:19', '2025-12-28 18:42:19'),
(2, 43, 37, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, '2026-01-09', 1, NULL, 'Registration fee paid - Betty', '2026-01-09 04:40:44', '2026-01-09 04:40:44'),
(3, 40, 34, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, '2026-01-09', 1, NULL, 'Registration fee paid - Sharon', '2026-01-09 04:40:44', '2026-01-09 04:40:44'),
(4, 43, 37, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, '2026-01-09', 1, NULL, 'Registration fee paid - Betty', '2026-01-09 04:42:00', '2026-01-09 04:42:00'),
(5, 40, 34, 150.00, 'ZMW', 'completed', 'bank_deposit', NULL, NULL, '2026-01-09', 1, NULL, 'Registration fee paid - Sharon', '2026-01-09 04:42:00', '2026-01-09 04:42:00');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

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
(5, 'Content Creator', 'Can create course content', '{\"courses\": [\"create\", \"read\", \"update\"], \"content\": [\"create\", \"update\"]}', '2025-11-18 22:21:01'),
(6, 'Finance', 'Financial operations and cash payment management', '{\"payments\": [\"create\", \"read\", \"update\"], \"students\": [\"read\"], \"enrollments\": [\"read\"], \"reports\": [\"read\"]}', '2025-11-24 11:56:11');

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
(12, 19, '2001-01-30', 'Female', NULL, 'Lusaka', 'Zambia', NULL, '2025-01-18', 0, 0, 0, '2025-11-18 22:21:01', '2025-11-18 22:21:01'),
(13, 26, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-23', 0, 0, 0, '2025-11-23 11:45:50', '2025-11-23 11:45:50'),
(14, 30, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-09', 0, 0, 0, '2025-12-09 11:30:29', '2025-12-09 11:30:29'),
(28, 34, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20', 0, 0, 0, '2025-12-20 13:21:55', '2025-12-20 13:21:55'),
(29, 35, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21', 0, 0, 0, '2025-12-21 12:57:29', '2025-12-21 12:57:29'),
(30, 36, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-22', 0, 0, 0, '2025-12-22 16:20:20', '2025-12-22 16:20:20'),
(31, 37, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-23', 0, 0, 0, '2025-12-23 07:18:25', '2025-12-23 07:18:25'),
(32, 38, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-25', 0, 0, 0, '2025-12-25 10:05:01', '2025-12-25 10:05:01'),
(33, 39, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-28', 0, 0, 0, '2025-12-28 15:13:44', '2025-12-28 15:13:44'),
(34, 40, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-29', 0, 0, 0, '2025-12-29 10:01:21', '2025-12-29 10:01:21'),
(35, 41, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-30', 0, 0, 0, '2025-12-30 12:45:53', '2025-12-30 12:45:53'),
(36, 42, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-31', 0, 0, 0, '2025-12-31 09:03:33', '2025-12-31 09:03:33'),
(37, 43, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08', 0, 0, 0, '2026-01-08 10:47:56', '2026-01-08 10:47:56');

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
(10, 'default_currency', 'USD', 'String', 'Default currency for payments', 1, '2025-11-18 22:21:01'),
(11, 'registration_fee_amount', '150.00', 'Number', 'Registration fee amount in ZMW', 1, '2025-11-24 11:56:12'),
(12, 'registration_fee_required', 'true', 'Boolean', 'Whether registration fee is required before enrollment', 1, '2025-11-24 11:56:12'),
(13, 'bank_account_name', 'EDUTRACK Computer Training College', 'String', 'Bank account name for deposits', 1, '2025-11-24 11:56:12'),
(14, 'bank_account_number', '', 'String', 'Bank account number for deposits', 1, '2025-11-24 11:56:12'),
(15, 'bank_name', '', 'String', 'Bank name for deposits', 1, '2025-11-24 11:56:12'),
(16, 'bank_branch', '', 'String', 'Bank branch for deposits', 1, '2025-11-24 11:56:12'),
(17, 'currency', 'ZMW', 'String', 'Default currency (Zambian Kwacha)', 1, '2025-11-24 11:56:12'),
(18, 'partial_payments_enabled', 'true', 'Boolean', 'Allow partial payments for course fees', 1, '2025-11-24 11:56:12'),
(19, 'certificate_requires_full_payment', 'true', 'Boolean', 'Block certificate issuance until fully paid', 1, '2025-11-24 11:56:12'),
(20, 'enrollment_min_deposit_percent', '30', 'Number', 'Minimum percentage (0-100) of course fee required to unlock content', 1, '2025-12-09 13:21:21'),
(21, 'registration_fee_amount', '150', 'Number', 'Mandatory one-time registration fee amount', 1, '2025-12-09 13:21:21');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `user_id`, `name`, `position`, `qualifications`, `image_url`, `display_order`, `created_at`) VALUES
(1, 27, 'Chilala Moonga', 'Principal & Director', 'MSc. Computer Science (DMI St. Eugene)\r\nB.ICT (The University of Zambia)\r\nDip. Education (The University of Zambia)', 'chilala.jpg', 1, '2025-12-06 09:00:22'),
(2, 29, 'Edward Musole', 'Vice Principal', 'Bachelor\'s Degree in Education (UNZA)\r\nAcademic Administration Specialist', 'edward.jpg', 2, '2025-12-06 09:00:22'),
(3, 6, 'Michael Siame', 'Head of ICT Department', 'B.ICT (Copperbelt University)\r\nStructural & Software Engineer', 'michael.jpg', 3, '2025-12-06 09:00:22'),
(4, 31, 'Anthony Nampute', 'Senior Lecturer', 'Dip. Computer Studies (UNZA)\r\nCertificate in English Language', 'anthony.jpg', 4, '2025-12-06 09:00:22'),
(5, 32, 'Inutu Simasiku', 'Admin & Procurement Officer', 'Dip. Registered Nursing\r\nCertificate in Marketing', 'inutu.jpg', 5, '2025-12-06 09:00:22'),
(6, 33, 'Nita Sichimwa', 'Student Support & Hygiene Officer', 'Nurse Assistant\r\nCert. Social Work & Community Development\r\nCertified Counselor', 'nita.jpg', 6, '2025-12-06 09:00:22'),
(7, 28, 'Witman Miyande', 'Senior Lecturer', 'B.ICT (UNZA)\r\nPROGRAMMING AND COMPUTER HARDWARE', 'witman.jpg', 7, '2025-12-12 09:37:04');

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
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `account_locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `avatar_url`, `status`, `email_verification_token`, `email_verification_expires`, `email_verified`, `last_login`, `last_login_ip`, `failed_login_attempts`, `account_locked_until`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@edutrack.edu', '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', 'System', 'Administrator', '+260900000000', NULL, 'active', NULL, NULL, 1, '2026-01-09 15:00:19', NULL, 0, NULL, '2025-11-18 22:21:01', '2026-01-09 13:00:19'),
(6, 'michael.siame', 'michael.siame@edutrack.edu', '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', 'Michael', 'Siame', '+260933567890', NULL, 'active', NULL, NULL, 1, '2025-12-25 21:53:14', NULL, 0, NULL, '2025-11-18 22:21:01', '2025-12-25 19:53:14'),
(25, 'taona', 'taona@gmail.com', '$2y$10$iJ4P8BDECzTdPhAwoP4pXOsf2rSZelFAfogVU6JCj2XfVdSVWHRlW', 'toana', 'ndlovuli', NULL, NULL, 'inactive', NULL, NULL, 0, NULL, NULL, 0, NULL, '2025-11-22 09:07:23', '2025-11-22 09:08:14'),
(26, 'jaysiame076', 'jaysiame076@gmail.com', '$2y$10$QQ0Z4AD75f/2TyPP6zdrYebKdTkhnHo3IFuCz/AT07KQD.v7pWgei', 'joe', 'siame', '', NULL, 'active', NULL, NULL, 0, '2025-12-09 11:32:59', NULL, 0, NULL, '2025-11-23 11:05:46', '2025-12-09 09:32:59'),
(27, 'marvinmoonga69', 'marvinmoonga69@gmail.com', '$2y$10$uTNe95f2nXGaFBDyrBtrQeCaYHRNQBr67kFd7Fugt2dfWZX59fQom', 'Chilala', 'Moonga', '+260979536820', NULL, 'active', NULL, NULL, 0, '2025-12-29 11:52:23', NULL, 0, NULL, '2025-12-04 20:34:20', '2025-12-29 09:52:23'),
(28, 'it', 'it@witmanmiyande.com', '$2y$10$kbm0yafbxD0Iu0Vk7uZYoOKTaqE1DTV47I7FHEFmeRESglBLmnWve', 'Witman', 'Miyande', '+260976062621', NULL, 'active', NULL, NULL, 0, '2025-12-08 13:07:32', NULL, 0, NULL, '2025-12-05 14:38:37', '2025-12-08 17:45:29'),
(29, 'edwardmusole76', 'edwardmusole76@gmail.com', '$2y$10$WAgkucanVQ4OuVJtxfZeIuH2gxPk4lH7tTmhKT0I8awfPWiBBakdC', 'Edward', 'Musole', '+260978605960', NULL, 'active', NULL, NULL, 0, '2025-12-29 12:47:56', NULL, 0, NULL, '2025-12-05 14:42:37', '2025-12-29 10:47:56'),
(30, 'siamem570', 'siamem570@gmail.com', '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', 'michael', 'siame', '+260771216339', NULL, 'active', NULL, NULL, 0, '2025-12-25 12:19:17', NULL, 0, NULL, '2025-12-09 11:30:29', '2025-12-25 10:19:17'),
(31, 'anthony.nampute', 'anthony.nampute@edutrack.edu', '$2y$10$uLoLKLK2Pcv08rJbNgPzhufsUVrhwhFR6IbSeGea6CKRuwU1NRbk.', 'Anthony', 'Nampute', NULL, NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2025-12-18 19:10:22', '2025-12-18 19:10:22'),
(32, 'inutu.simasiku', 'inutu.simasiku@edutrack.edu', '$2y$10$uLoLKLK2Pcv08rJbNgPzhufsUVrhwhFR6IbSeGea6CKRuwU1NRbk.', 'Inutu', 'Simasiku', NULL, NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2025-12-18 19:10:22', '2025-12-18 19:10:22'),
(33, 'nita.sichimwa', 'nita.sichimwa@edutrack.edu', '$2y$10$uLoLKLK2Pcv08rJbNgPzhufsUVrhwhFR6IbSeGea6CKRuwU1NRbk.', 'Nita', 'Sichimwa', NULL, NULL, 'active', NULL, NULL, 1, NULL, NULL, 0, NULL, '2025-12-18 19:10:22', '2025-12-18 19:10:22'),
(34, 'jilowahappy19', 'jilowahappy19@gmail.com', '$2y$10$BsJsXUusPmNBJmhfOOj15uzzlP.rFmjhhG.lKeZDTwcOzW62FFHne', 'Happy', 'Jilowa', '+260760054975', NULL, 'active', NULL, NULL, 0, '2025-12-20 15:22:34', NULL, 0, NULL, '2025-12-20 13:21:55', '2025-12-20 13:22:34'),
(35, 'unparalleledtvstation2.0', 'unparalleledtvstation2.0@gmail.com', '$2y$10$Uc90KkySyrdk/ALa4SYkRuOybANz/FRsFVcAiLQjrUDgMkGdi9UsW', 'michael', 'siame', '+260771216339', NULL, 'active', NULL, NULL, 0, NULL, NULL, 0, NULL, '2025-12-21 12:57:29', '2025-12-21 12:57:29'),
(36, 'moseschanda084', 'moseschanda084@gmail.com', '$2y$10$/COC.FYEkun5lNEdR7l1wePpKqi5dvtCRvr9nIXWBN3gZ6/QxZndK', 'Moses', 'Mulunda chanda', '+260971848021', NULL, 'active', NULL, NULL, 0, '2025-12-22 18:20:55', NULL, 0, NULL, '2025-12-22 16:20:20', '2025-12-22 16:20:55'),
(37, 'sampayainnocent15', 'sampayainnocent15@gmail.com', '$2y$10$smRux7NyqHoywLOV0Djiy.0ydzMqz9JoAMS/5Nwg0RN0AYtA8qM2e', 'Innocent', 'Sampaya', '+260771717517', NULL, 'active', NULL, NULL, 0, '2025-12-23 09:19:00', NULL, 0, NULL, '2025-12-23 07:18:25', '2025-12-23 07:19:00'),
(38, 'emmanuelgoma6', 'emmanuelgoma6@gmail.com', '$2y$10$G//nDjkNfzXuLGpLa6CnD.q0V4LOg4JYmwb.fJ5zq9UFz9vMWoNEa', 'Emmanuel', 'Goma', '+260967780685', NULL, 'active', NULL, NULL, 0, '2025-12-25 12:05:33', NULL, 0, NULL, '2025-12-25 10:05:01', '2025-12-25 10:05:33'),
(39, 'givenmutwena60', 'givenmutwena60@gmail.com', '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW', 'GIVEN', 'MUTWENA', '+260971077923', NULL, 'active', NULL, NULL, 0, '2026-01-02 11:36:54', NULL, 0, NULL, '2025-12-28 15:13:44', '2026-01-02 09:36:54'),
(40, 'lumbongosharon', 'lumbongosharon@gmail.com', '$2y$10$z7ha9/pweOV5FhWUJOritOdYC4aYTWtZVxP6yjNGUxaTJTehx66JK', 'SHARON', 'LUMBONGO', '+260975854731', NULL, 'active', NULL, NULL, 0, '2026-01-06 20:58:09', NULL, 0, NULL, '2025-12-29 10:01:21', '2026-01-06 18:58:09'),
(41, 'simanyangamooya', 'simanyangamooya@gmail.com', '$2y$10$TMW22nvE0bHFYMDnZfQ3nO9Ehb9rcUiFXLWOEBY5/1v5mtYDqKGlm', 'MOOYA', 'SIMANYANGA', '+260975803528', NULL, 'active', NULL, NULL, 0, '2025-12-30 14:46:43', NULL, 0, NULL, '2025-12-30 12:45:53', '2025-12-30 12:46:43'),
(42, 'najibib465', 'najibib465@hudisk.com', '$2y$10$8FQVQC8clPVMiyGEeSJDGebcAlNa42BrNKG6dDGgR7QyTVbWFtA1m', 'Doe', 'John', '', NULL, 'active', NULL, NULL, 0, '2025-12-31 11:04:08', NULL, 0, NULL, '2025-12-31 09:03:33', '2025-12-31 09:04:08'),
(43, 'bettylumbongo60', 'bettylumbongo60@gmail.com', '$2y$10$s1KFx4SOeeRUvJlV8XKxMOHzt9YqtJTTNRCzHaT00Lu9QSoW8wqXG', 'Betty', 'Lumbongo', '+260975179897', NULL, 'active', NULL, NULL, 0, '2026-01-08 13:07:43', NULL, 0, NULL, '2026-01-08 10:47:56', '2026-01-08 11:07:43');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `nrc_number` varchar(20) DEFAULT NULL,
  `education_level` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `bio`, `phone`, `date_of_birth`, `gender`, `address`, `city`, `country`, `postal_code`, `avatar_url`, `created_at`, `updated_at`, `avatar`, `province`, `nrc_number`, `education_level`, `occupation`, `linkedin_url`, `facebook_url`, `twitter_url`) VALUES
(1, 8, NULL, '+260971111111', '1998-05-15', 'Male', NULL, 'Lusaka', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 9, NULL, '+260972222222', '2000-08-22', 'Female', NULL, 'Ndola', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 10, NULL, '+260973333333', '1995-03-10', 'Male', NULL, 'Kitwe', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 11, NULL, '+260974444444', '1999-11-30', 'Female', NULL, 'Livingstone', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 12, NULL, '+260975555555', '1997-07-18', 'Male', NULL, 'Lusaka', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 13, NULL, '+260976666666', '2001-02-25', 'Female', NULL, 'Kabwe', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 14, NULL, '+260977777777', '1996-09-12', 'Male', NULL, 'Chingola', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 15, NULL, '+260978888888', '1998-12-05', 'Female', NULL, 'Lusaka', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 16, NULL, '+260979999999', '2000-04-20', 'Male', NULL, 'Solwezi', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 17, NULL, '+260970000000', '1999-06-08', 'Female', NULL, 'Mongu', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 18, NULL, '+260971234567', '1997-10-15', 'Male', NULL, 'Kasama', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 19, NULL, '+260972345678', '2001-01-30', 'Female', NULL, 'Lusaka', 'Zambia', NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 2, 'Experienced ICT trainer with 10+ years in corporate training. Microsoft Certified Professional with expertise in Office Suite and digital literacy programs.', '+260977123456', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 3, 'Full-stack developer and certified instructor with passion for teaching modern programming languages. 8 years industry experience in software development.', '+260966234567', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 4, 'Cybersecurity specialist and ethical hacker with CISSP certification. Former network administrator with extensive experience in data security.', '+260955345678', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 5, 'AI/ML researcher and data scientist with PhD in Computer Science. Published researcher with focus on practical applications of machine learning.', '+260944456789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 6, 'Business consultant and entrepreneur with MBA. Specializes in digital transformation, project management, and business strategy.', '+260933567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 7, 'Award-winning graphic designer and digital marketer. Adobe Certified Expert with 7 years experience in creative industries.', '+260922678901', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-18 23:02:36', '2025-11-18 23:02:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 26, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 27, NULL, '+260979536820', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 28, NULL, '+260976062621', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 29, NULL, '+260978605960', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-08 17:45:29', '2025-12-08 17:45:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 30, '', '+260771216339', '2002-05-03', 'Male', '1038 accra road', 'kitwe', 'Zambia', '10101', NULL, '2025-12-09 11:30:29', '2025-12-10 07:40:33', NULL, 'Copperbelt', '398943/65/1', 'Grade 12', 'technican', '', '', ''),
(47, 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 13:21:55', '2025-12-20 13:21:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 12:57:29', '2025-12-21 12:57:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-22 16:20:20', '2025-12-22 16:20:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 37, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-23 07:18:25', '2025-12-23 07:18:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 38, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-25 10:05:01', '2025-12-25 10:05:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 39, 'My name is Given mutwena,am a female.Am a primary teacher , working from kalulushi at kameme central primary school', '+260971077923', '1987-09-28', 'Female', 'Kalulushi Paterson area behind local court', 'Kalulushi', 'Zambia', '', NULL, '2025-12-28 15:13:44', '2025-12-28 15:58:19', NULL, 'Copperbelt', '103695/97/1', 'Bachelor\'s Degree', 'Teacher', '', '', ''),
(53, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-29 10:01:21', '2025-12-29 10:01:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, 41, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-30 12:45:53', '2025-12-30 12:45:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, 42, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-31 09:03:33', '2025-12-31 09:03:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 10:47:56', '2026-01-08 10:47:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
(71, 43, 4, '2026-01-08 10:47:56', NULL);

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
(11, 34, '91bfdeccc6b5f864eafec843bf557ed9b858a9caa1721076e71a9d1ff24f70af', '102.212.183.105', 'Mozilla/5.0 (Linux; Android 13; Infinix X6525 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/485.0.0.19.109;FBCX/modulariab;]', '2026-01-19 15:22:34', '2025-12-20 13:22:34', '2025-12-20 13:22:34'),
(13, 36, 'c40cdda662683aa736bed1c20bdc2be71a8090a0824cea31ec73f288720f3931', '102.145.233.11', 'Mozilla/5.0 (Linux; Android 13; Infinix X6837 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/536.0.0.46.216;]', '2026-01-21 18:20:55', '2025-12-22 16:20:55', '2025-12-22 16:20:55'),
(15, 37, '8a62872aafab3cf6be6cac47fbd87aedb48c77c42e7ae078be2ba06ae2da661b', '41.223.118.42', 'Mozilla/5.0 (Linux; Android 13; Infinix X6528 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/143.0.7499.34 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/489.0.0.12.109;FBCX/modulariab;]', '2026-01-22 09:19:00', '2025-12-23 07:19:00', '2025-12-23 07:19:00'),
(35, 39, '104f644c7e9a39acab262d5e67577c8351d341509e1e322dcd6a09ae88b5bf8e', '45.215.253.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-28 12:57:03', '2025-12-29 10:57:03', '2025-12-29 10:57:03'),
(39, 41, 'fa97413f2aed9c6a9bf39c82fdc140c4a3951c8b2c24731ee83f00d6986cc044', '41.216.86.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:46:43', '2025-12-30 12:46:43', '2025-12-30 12:46:43'),
(50, 1, 'ccf13c854b0d757c90d8336426d0a133d21936a58a24d65935ff4d0741caacce', '102.208.221.209', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-09 17:00:19', '2026-01-09 13:00:19', '2026-01-09 13:00:19');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_student_balances`
-- (See below for the actual view)
--
CREATE TABLE `v_student_balances` (
`user_id` int(11)
,`username` varchar(50)
,`full_name` varchar(101)
,`email` varchar(100)
,`student_id` int(11)
,`total_courses` bigint(21)
,`total_course_fees` decimal(32,2)
,`total_paid` decimal(32,2)
,`total_balance` decimal(33,2)
,`overall_status` varchar(11)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assign_course` (`course_id`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_assign` (`assignment_id`),
  ADD KEY `idx_sub_student` (`student_id`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`badge_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`certificate_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_courses_cat` (`category_id`),
  ADD KEY `idx_courses_inst` (`instructor_id`);

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
  ADD KEY `idx_enroll_user` (`user_id`),
  ADD KEY `idx_enroll_course` (`course_id`),
  ADD KEY `idx_enroll_student` (`student_id`);

--
-- Indexes for table `enrollment_payment_plans`
--
ALTER TABLE `enrollment_payment_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inst_user` (`user_id`);

--
-- Indexes for table `lenco_transactions`
--
ALTER TABLE `lenco_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD UNIQUE KEY `idx_reference` (`reference`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_enrollment_id` (`enrollment_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_virtual_account` (`virtual_account_number`),
  ADD KEY `idx_expires_at` (`expires_at`);

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
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lesson_resources`
--
ALTER TABLE `lesson_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `live_sessions`
--
ALTER TABLE `live_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `live_session_attendance`
--
ALTER TABLE `live_session_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_msg_sender` (`sender_id`),
  ADD KEY `idx_msg_recipient` (`recipient_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mod_course` (`course_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notif_user` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`);

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
  ADD PRIMARY KEY (`option_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_course` (`course_id`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`answer_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_att_quiz` (`quiz_id`),
  ADD KEY `idx_att_student` (`student_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`quiz_question_id`);

--
-- Indexes for table `registration_fees`
--
ALTER TABLE `registration_fees`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
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
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_team_user_link` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_users_email_search` (`email`),
  ADD KEY `idx_users_ver_token` (`email_verification_token`),
  ADD KEY `id` (`id`);

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
  ADD KEY `idx_ur_user` (`user_id`),
  ADD KEY `idx_ur_role` (`role_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `course_categories`
--
ALTER TABLE `course_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `enrollment_payment_plans`
--
ALTER TABLE `enrollment_payment_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lesson_resources`
--
ALTER TABLE `lesson_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `live_sessions`
--
ALTER TABLE `live_sessions`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `registration_fees`
--
ALTER TABLE `registration_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

-- --------------------------------------------------------

--
-- Structure for view `v_student_balances`
--
DROP TABLE IF EXISTS `v_student_balances`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u605780771_root`@`127.0.0.1` SQL SECURITY DEFINER VIEW `v_student_balances`  AS SELECT `u`.`id` AS `user_id`, `u`.`username` AS `username`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`, `u`.`email` AS `email`, `s`.`id` AS `student_id`, count(`e`.`id`) AS `total_courses`, coalesce(sum(`epp`.`total_fee`),0) AS `total_course_fees`, coalesce(sum(`epp`.`total_paid`),0) AS `total_paid`, coalesce(sum(`epp`.`total_fee` - `epp`.`total_paid`),0) AS `total_balance`, CASE WHEN sum(`epp`.`total_fee` - `epp`.`total_paid`) > 0 THEN 'Outstanding' ELSE 'Clear' END AS `overall_status` FROM (((`users` `u` join `students` `s` on(`u`.`id` = `s`.`user_id`)) left join `enrollments` `e` on(`s`.`id` = `e`.`student_id`)) left join `enrollment_payment_plans` `epp` on(`e`.`id` = `epp`.`enrollment_id`)) GROUP BY `u`.`id`, `u`.`username`, `u`.`first_name`, `u`.`last_name`, `u`.`email`, `s`.`id` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assign_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `fk_sub_assign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sub_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
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
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `fk_inst_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quiz_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `fk_att_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `DE` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`u605780771_root`@`127.0.0.1` EVENT `cleanup_expired_lenco_transactions` ON SCHEDULE EVERY 1 HOUR STARTS '2025-12-28 18:58:28' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE `lenco_transactions`
    SET `status` = 'expired', `updated_at` = NOW()
    WHERE `status` = 'pending'
    AND `expires_at` < NOW();
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
