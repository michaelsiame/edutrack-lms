-- ============================================================================
-- EDUTRACK LMS - COMPLETE DATABASE SCHEMA
-- ============================================================================
-- Description: Comprehensive SQL script for a full-featured Learning Management System
-- Includes: Courses, Users, Content, Assessments, Communication, Payments, Analytics
-- Database: MySQL 5.7+ Compatible
-- Created: 2025-11-18
-- ============================================================================

-- Drop existing tables if they exist (in correct order to handle foreign keys)
DROP TABLE IF EXISTS Activity_Logs;
DROP TABLE IF EXISTS Lesson_Progress;
DROP TABLE IF EXISTS Student_Achievements;
DROP TABLE IF EXISTS Badges;
DROP TABLE IF EXISTS Certificates;
DROP TABLE IF EXISTS Messages;
DROP TABLE IF EXISTS Discussion_Replies;
DROP TABLE IF EXISTS Discussions;
DROP TABLE IF EXISTS Announcements;
DROP TABLE IF EXISTS Transactions;
DROP TABLE IF EXISTS Payments;
DROP TABLE IF EXISTS Quiz_Attempts;
DROP TABLE IF EXISTS Quiz_Answers;
DROP TABLE IF EXISTS Assignment_Submissions;
DROP TABLE IF EXISTS Quiz_Questions;
DROP TABLE IF EXISTS Question_Options;
DROP TABLE IF EXISTS Questions;
DROP TABLE IF EXISTS Quizzes;
DROP TABLE IF EXISTS Assignments;
DROP TABLE IF EXISTS Lesson_Resources;
DROP TABLE IF EXISTS Lessons;
DROP TABLE IF EXISTS Modules;
DROP TABLE IF EXISTS Enrollments;
DROP TABLE IF EXISTS Course_Instructors;
DROP TABLE IF EXISTS Students;
DROP TABLE IF EXISTS Instructors;
DROP TABLE IF EXISTS Courses;
DROP TABLE IF EXISTS Course_Categories;
DROP TABLE IF EXISTS User_Roles;
DROP TABLE IF EXISTS Roles;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS System_Settings;
DROP TABLE IF EXISTS Email_Templates;
DROP TABLE IF EXISTS Notifications;
DROP TABLE IF EXISTS Payment_Methods;

-- ============================================================================
-- SECTION 1: USERS, AUTHENTICATION & AUTHORIZATION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: Users
-- Purpose: Central user authentication table for all system users
-- ----------------------------------------------------------------------------
CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    avatar_url VARCHAR(255),
    status ENUM('Active', 'Inactive', 'Suspended', 'Pending') DEFAULT 'Active',
    email_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Roles
-- Purpose: Define system roles (Admin, Instructor, Student, etc.)
-- ----------------------------------------------------------------------------
CREATE TABLE Roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: User_Roles
-- Purpose: Junction table for many-to-many user-role relationships
-- ----------------------------------------------------------------------------
CREATE TABLE User_Roles (
    user_role_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES Roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES Users(user_id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 2: COURSE MANAGEMENT (Enhanced from original schema)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: Course_Categories
-- ----------------------------------------------------------------------------
CREATE TABLE Course_Categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    parent_category_id INT NULL,
    icon_url VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_category_id) REFERENCES Course_Categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Instructors
-- ----------------------------------------------------------------------------
CREATE TABLE Instructors (
    instructor_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    specialization VARCHAR(100),
    years_experience INT,
    education TEXT,
    certifications TEXT,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    total_students INT DEFAULT 0,
    total_courses INT DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_rating CHECK (rating >= 0 AND rating <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Courses
-- ----------------------------------------------------------------------------
CREATE TABLE Courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(250) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    category_id INT NOT NULL,
    difficulty_level ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL DEFAULT 'Beginner',
    language VARCHAR(50) DEFAULT 'English',
    thumbnail_url VARCHAR(255),
    video_intro_url VARCHAR(255),
    start_date DATE,
    end_date DATE,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    discount_price DECIMAL(10, 2),
    duration_weeks INT,
    total_hours DECIMAL(5, 2),
    max_students INT DEFAULT 30,
    enrollment_count INT DEFAULT 0,
    status ENUM('Draft', 'Published', 'Archived', 'Under Review') DEFAULT 'Draft',
    is_featured BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    prerequisites TEXT,
    learning_outcomes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES Course_Categories(category_id) ON DELETE RESTRICT,
    CONSTRAINT chk_dates CHECK (end_date >= start_date),
    CONSTRAINT chk_course_rating CHECK (rating >= 0 AND rating <= 5),
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Students
-- ----------------------------------------------------------------------------
CREATE TABLE Students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say'),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    enrollment_date DATE NOT NULL,
    total_courses_enrolled INT DEFAULT 0,
    total_courses_completed INT DEFAULT 0,
    total_certificates INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Course_Instructors
-- ----------------------------------------------------------------------------
CREATE TABLE Course_Instructors (
    course_instructor_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    instructor_id INT NOT NULL,
    role ENUM('Lead', 'Assistant', 'Guest', 'Mentor') DEFAULT 'Lead',
    assigned_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES Instructors(instructor_id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_instructor (course_id, instructor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Enrollments
-- ----------------------------------------------------------------------------
CREATE TABLE Enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    start_date DATE,
    progress DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    final_grade DECIMAL(5, 2),
    status ENUM('Enrolled', 'In Progress', 'Completed', 'Dropped', 'Expired') DEFAULT 'Enrolled',
    completion_date DATE,
    certificate_issued BOOLEAN DEFAULT FALSE,
    last_accessed TIMESTAMP NULL,
    total_time_spent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id),
    CONSTRAINT chk_progress CHECK (progress >= 0 AND progress <= 100),
    CONSTRAINT chk_final_grade CHECK (final_grade IS NULL OR (final_grade >= 0 AND final_grade <= 100)),
    INDEX idx_status (status),
    INDEX idx_student (student_id),
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 3: COURSE CONTENT STRUCTURE
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: Modules
-- Purpose: Course sections/chapters that group lessons
-- ----------------------------------------------------------------------------
CREATE TABLE Modules (
    module_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    display_order INT NOT NULL DEFAULT 0,
    duration_minutes INT,
    is_published BOOLEAN DEFAULT TRUE,
    unlock_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    INDEX idx_course_order (course_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Lessons
-- Purpose: Individual learning units within modules
-- ----------------------------------------------------------------------------
CREATE TABLE Lessons (
    lesson_id INT PRIMARY KEY AUTO_INCREMENT,
    module_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT,
    lesson_type ENUM('Video', 'Reading', 'Quiz', 'Assignment', 'Live Session', 'Download') DEFAULT 'Reading',
    duration_minutes INT,
    display_order INT NOT NULL DEFAULT 0,
    video_url VARCHAR(255),
    video_duration INT,
    is_preview BOOLEAN DEFAULT FALSE,
    is_mandatory BOOLEAN DEFAULT TRUE,
    points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES Modules(module_id) ON DELETE CASCADE,
    INDEX idx_module_order (module_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Lesson_Resources
-- Purpose: Downloadable resources attached to lessons
-- ----------------------------------------------------------------------------
CREATE TABLE Lesson_Resources (
    resource_id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    resource_type ENUM('PDF', 'Document', 'Spreadsheet', 'Presentation', 'Video', 'Audio', 'Archive', 'Other') NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_size_kb INT,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES Lessons(lesson_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Lesson_Progress
-- Purpose: Track individual lesson completion by students
-- ----------------------------------------------------------------------------
CREATE TABLE Lesson_Progress (
    progress_id INT PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT NOT NULL,
    lesson_id INT NOT NULL,
    status ENUM('Not Started', 'In Progress', 'Completed') DEFAULT 'Not Started',
    progress_percentage DECIMAL(5, 2) DEFAULT 0.00,
    time_spent_minutes INT DEFAULT 0,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    last_accessed TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES Enrollments(enrollment_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES Lessons(lesson_id) ON DELETE CASCADE,
    UNIQUE KEY unique_lesson_progress (enrollment_id, lesson_id),
    INDEX idx_enrollment (enrollment_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 4: ASSESSMENTS & GRADING
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: Assignments
-- Purpose: Course assignments and projects
-- ----------------------------------------------------------------------------
CREATE TABLE Assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    lesson_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    instructions LONGTEXT,
    max_points INT NOT NULL DEFAULT 100,
    passing_points INT NOT NULL DEFAULT 60,
    due_date DATETIME,
    allow_late_submission BOOLEAN DEFAULT FALSE,
    late_penalty_percent DECIMAL(5, 2) DEFAULT 0.00,
    max_file_size_mb INT DEFAULT 10,
    allowed_file_types VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES Lessons(lesson_id) ON DELETE SET NULL,
    INDEX idx_course (course_id),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Assignment_Submissions
-- Purpose: Student assignment submissions and grading
-- ----------------------------------------------------------------------------
CREATE TABLE Assignment_Submissions (
    submission_id INT PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text LONGTEXT,
    file_url VARCHAR(255),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Submitted', 'Graded', 'Returned', 'Late') DEFAULT 'Submitted',
    points_earned DECIMAL(5, 2),
    feedback TEXT,
    graded_by INT,
    graded_at TIMESTAMP NULL,
    attempt_number INT DEFAULT 1,
    is_late BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (assignment_id) REFERENCES Assignments(assignment_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES Instructors(instructor_id) ON DELETE SET NULL,
    INDEX idx_assignment (assignment_id),
    INDEX idx_student (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Quizzes
-- Purpose: Course quizzes and exams
-- ----------------------------------------------------------------------------
CREATE TABLE Quizzes (
    quiz_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    lesson_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    quiz_type ENUM('Practice', 'Graded', 'Final Exam', 'Midterm') DEFAULT 'Graded',
    time_limit_minutes INT,
    max_attempts INT DEFAULT 1,
    passing_score DECIMAL(5, 2) DEFAULT 60.00,
    randomize_questions BOOLEAN DEFAULT FALSE,
    show_correct_answers BOOLEAN DEFAULT FALSE,
    available_from DATETIME,
    available_until DATETIME,
    is_published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES Lessons(lesson_id) ON DELETE SET NULL,
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Questions
-- Purpose: Question bank for quizzes
-- ----------------------------------------------------------------------------
CREATE TABLE Questions (
    question_id INT PRIMARY KEY AUTO_INCREMENT,
    question_type ENUM('Multiple Choice', 'True/False', 'Short Answer', 'Essay', 'Fill in Blank') NOT NULL,
    question_text TEXT NOT NULL,
    points INT DEFAULT 1,
    explanation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Question_Options
-- Purpose: Multiple choice options for questions
-- ----------------------------------------------------------------------------
CREATE TABLE Question_Options (
    option_id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES Questions(question_id) ON DELETE CASCADE,
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Quiz_Questions
-- Purpose: Link questions to quizzes
-- ----------------------------------------------------------------------------
CREATE TABLE Quiz_Questions (
    quiz_question_id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question_id INT NOT NULL,
    display_order INT DEFAULT 0,
    points_override INT,
    FOREIGN KEY (quiz_id) REFERENCES Quizzes(quiz_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES Questions(question_id) ON DELETE CASCADE,
    UNIQUE KEY unique_quiz_question (quiz_id, question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Quiz_Attempts
-- Purpose: Track student quiz attempts
-- ----------------------------------------------------------------------------
CREATE TABLE Quiz_Attempts (
    attempt_id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    attempt_number INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    submitted_at TIMESTAMP NULL,
    score DECIMAL(5, 2),
    status ENUM('In Progress', 'Submitted', 'Graded', 'Abandoned') DEFAULT 'In Progress',
    time_spent_minutes INT,
    ip_address VARCHAR(45),
    FOREIGN KEY (quiz_id) REFERENCES Quizzes(quiz_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id),
    INDEX idx_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Quiz_Answers
-- Purpose: Student answers to quiz questions
-- ----------------------------------------------------------------------------
CREATE TABLE Quiz_Answers (
    answer_id INT PRIMARY KEY AUTO_INCREMENT,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option_id INT,
    answer_text TEXT,
    is_correct BOOLEAN,
    points_earned DECIMAL(5, 2),
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES Quiz_Attempts(attempt_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES Questions(question_id) ON DELETE CASCADE,
    FOREIGN KEY (selected_option_id) REFERENCES Question_Options(option_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 5: COMMUNICATION & COLLABORATION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: Announcements
-- Purpose: Course and system announcements
-- ----------------------------------------------------------------------------
CREATE TABLE Announcements (
    announcement_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    posted_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    announcement_type ENUM('Course', 'System', 'Urgent', 'General') DEFAULT 'General',
    priority ENUM('Low', 'Normal', 'High', 'Urgent') DEFAULT 'Normal',
    is_published BOOLEAN DEFAULT TRUE,
    published_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Discussions
-- Purpose: Course discussion forums
-- ----------------------------------------------------------------------------
CREATE TABLE Discussions (
    discussion_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    created_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    reply_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_pinned (is_pinned)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Discussion_Replies
-- Purpose: Replies to discussion threads
-- ----------------------------------------------------------------------------
CREATE TABLE Discussion_Replies (
    reply_id INT PRIMARY KEY AUTO_INCREMENT,
    discussion_id INT NOT NULL,
    parent_reply_id INT,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    is_instructor_reply BOOLEAN DEFAULT FALSE,
    is_best_answer BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (discussion_id) REFERENCES Discussions(discussion_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_reply_id) REFERENCES Discussion_Replies(reply_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX idx_discussion (discussion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Messages
-- Purpose: Private messaging between users
-- ----------------------------------------------------------------------------
CREATE TABLE Messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    subject VARCHAR(200),
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    parent_message_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_message_id) REFERENCES Messages(message_id) ON DELETE CASCADE,
    INDEX idx_recipient (recipient_id),
    INDEX idx_sender (sender_id),
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 6: CERTIFICATES & ACHIEVEMENTS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: Certificates
-- Purpose: Course completion certificates
-- ----------------------------------------------------------------------------
CREATE TABLE Certificates (
    certificate_id INT PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT NOT NULL,
    certificate_number VARCHAR(50) NOT NULL UNIQUE,
    issued_date DATE NOT NULL,
    certificate_url VARCHAR(255),
    verification_code VARCHAR(100) UNIQUE,
    is_verified BOOLEAN DEFAULT TRUE,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES Enrollments(enrollment_id) ON DELETE CASCADE,
    INDEX idx_verification_code (verification_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Badges
-- Purpose: Achievement badges and awards
-- ----------------------------------------------------------------------------
CREATE TABLE Badges (
    badge_id INT PRIMARY KEY AUTO_INCREMENT,
    badge_name VARCHAR(100) NOT NULL,
    description TEXT,
    badge_icon_url VARCHAR(255),
    badge_type ENUM('Course Completion', 'Perfect Score', 'Early Bird', 'Participation', 'Streak', 'Custom') DEFAULT 'Custom',
    criteria TEXT,
    points INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Student_Achievements
-- Purpose: Track badges earned by students
-- ----------------------------------------------------------------------------
CREATE TABLE Student_Achievements (
    achievement_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    badge_id INT NOT NULL,
    course_id INT,
    earned_date DATE NOT NULL,
    description TEXT,
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES Badges(badge_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE SET NULL,
    INDEX idx_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 7: PAYMENT & BILLING
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: Payment_Methods
-- Purpose: Available payment methods
-- ----------------------------------------------------------------------------
CREATE TABLE Payment_Methods (
    payment_method_id INT PRIMARY KEY AUTO_INCREMENT,
    method_name VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Payments
-- Purpose: Payment records for course enrollments
-- ----------------------------------------------------------------------------
CREATE TABLE Payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_method_id INT,
    payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded', 'Cancelled') DEFAULT 'Pending',
    transaction_id VARCHAR(100) UNIQUE,
    payment_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES Enrollments(enrollment_id) ON DELETE SET NULL,
    FOREIGN KEY (payment_method_id) REFERENCES Payment_Methods(payment_method_id) ON DELETE SET NULL,
    INDEX idx_student (student_id),
    INDEX idx_status (payment_status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Transactions
-- Purpose: Detailed transaction log for accounting
-- ----------------------------------------------------------------------------
CREATE TABLE Transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NOT NULL,
    transaction_type ENUM('Payment', 'Refund', 'Chargeback', 'Fee') DEFAULT 'Payment',
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    gateway_response TEXT,
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES Payments(payment_id) ON DELETE CASCADE,
    INDEX idx_payment (payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 8: ANALYTICS & TRACKING
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: Activity_Logs
-- Purpose: Track all user activities for analytics
-- ----------------------------------------------------------------------------
CREATE TABLE Activity_Logs (
    log_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    activity_type VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 9: SYSTEM CONFIGURATION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: System_Settings
-- Purpose: System-wide configuration settings
-- ----------------------------------------------------------------------------
CREATE TABLE System_Settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('String', 'Number', 'Boolean', 'JSON') DEFAULT 'String',
    description TEXT,
    is_editable BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Email_Templates
-- Purpose: Email templates for system notifications
-- ----------------------------------------------------------------------------
CREATE TABLE Email_Templates (
    template_id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(100) NOT NULL UNIQUE,
    subject VARCHAR(200) NOT NULL,
    body LONGTEXT NOT NULL,
    template_type ENUM('Welcome', 'Enrollment', 'Certificate', 'Payment', 'Reminder', 'Custom') DEFAULT 'Custom',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Notifications
-- Purpose: In-app notifications for users
-- ----------------------------------------------------------------------------
CREATE TABLE Notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('Info', 'Success', 'Warning', 'Error', 'Assignment', 'Grade', 'Announcement') DEFAULT 'Info',
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    action_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 10: DATA POPULATION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Populate Roles
-- ----------------------------------------------------------------------------
INSERT INTO Roles (role_name, description, permissions) VALUES
('Super Admin', 'Full system access and control', '{"all": true}'),
('Admin', 'Administrative access to manage system', '{"users": ["create", "read", "update", "delete"], "courses": ["create", "read", "update", "delete"], "reports": ["read"]}'),
('Instructor', 'Can create and manage courses', '{"courses": ["create", "read", "update"], "students": ["read"], "grades": ["create", "update"]}'),
('Student', 'Can enroll and access courses', '{"courses": ["read", "enroll"], "assignments": ["submit"], "quizzes": ["take"]}'),
('Content Creator', 'Can create course content', '{"courses": ["create", "read", "update"], "content": ["create", "update"]}');

-- ----------------------------------------------------------------------------
-- Populate Users (Admin, Instructors, Students)
-- ----------------------------------------------------------------------------
INSERT INTO Users (username, email, password_hash, first_name, last_name, phone, status, email_verified) VALUES
-- Admin user
('admin', 'admin@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', '+260900000000', 'Active', TRUE),

-- Instructors (6)
('james.mwanza', 'james.mwanza@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James', 'Mwanza', '+260977123456', 'Active', TRUE),
('sarah.banda', 'sarah.banda@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Banda', '+260966234567', 'Active', TRUE),
('peter.phiri', 'peter.phiri@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peter', 'Phiri', '+260955345678', 'Active', TRUE),
('grace.chanda', 'grace.chanda@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace', 'Chanda', '+260944456789', 'Active', TRUE),
('michael.siame', 'michael.siame@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Siame', '+260933567890', 'Active', TRUE),
('mercy.zulu', 'mercy.zulu@edutrack.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mercy', 'Zulu', '+260922678901', 'Active', TRUE),

-- Students (12)
('john.tembo', 'john.tembo@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Tembo', '+260971111111', 'Active', TRUE),
('mary.lungu', 'mary.lungu@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mary', 'Lungu', '+260972222222', 'Active', TRUE),
('david.sakala', 'david.sakala@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David', 'Sakala', '+260973333333', 'Active', TRUE),
('alice.mulenga', 'alice.mulenga@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Mulenga', '+260974444444', 'Active', TRUE),
('robert.chilufya', 'robert.chilufya@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert', 'Chilufya', '+260975555555', 'Active', TRUE),
('susan.banda', 'susan.banda@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Susan', 'Banda', '+260976666666', 'Active', TRUE),
('patrick.mutale', 'patrick.mutale@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Patrick', 'Mutale', '+260977777777', 'Active', TRUE),
('elizabeth.phiri', 'elizabeth.phiri@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Elizabeth', 'Phiri', '+260978888888', 'Active', TRUE),
('george.kunda', 'george.kunda@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'George', 'Kunda', '+260979999999', 'Active', TRUE),
('jennifer.musonda', 'jennifer.musonda@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jennifer', 'Musonda', '+260970000000', 'Active', TRUE),
('moses.chola', 'moses.chola@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Moses', 'Chola', '+260971234567', 'Active', TRUE),
('ruth.zimba', 'ruth.zimba@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ruth', 'Zimba', '+260972345678', 'Active', TRUE);

-- ----------------------------------------------------------------------------
-- Assign User Roles
-- ----------------------------------------------------------------------------
INSERT INTO User_Roles (user_id, role_id, assigned_by) VALUES
-- Admin
(1, 1, 1),
-- Instructors
(2, 3, 1),
(3, 3, 1),
(4, 3, 1),
(5, 3, 1),
(6, 3, 1),
(7, 3, 1),
-- Students
(8, 4, 1),
(9, 4, 1),
(10, 4, 1),
(11, 4, 1),
(12, 4, 1),
(13, 4, 1),
(14, 4, 1),
(15, 4, 1),
(16, 4, 1),
(17, 4, 1),
(18, 4, 1),
(19, 4, 1);

-- ----------------------------------------------------------------------------
-- Populate Course Categories
-- ----------------------------------------------------------------------------
INSERT INTO Course_Categories (category_name, category_description, display_order, is_active) VALUES
('Core ICT & Digital Skills', 'Fundamental computer and digital literacy courses covering essential office applications, digital tools, and basic ICT competencies', 1, TRUE),
('Programming & Software Development', 'Programming languages, software engineering practices, web and mobile application development courses', 2, TRUE),
('Data, Security & Networks', 'Data analysis, cybersecurity, database management, and network infrastructure courses', 3, TRUE),
('Emerging Technologies', 'Cutting-edge technology courses including AI, machine learning, and Internet of Things', 4, TRUE),
('Digital Media & Design', 'Creative and digital content courses covering graphic design, multimedia, and digital marketing', 5, TRUE),
('Business & Management', 'Business administration, entrepreneurship, project management, and professional development courses', 6, TRUE);

-- ----------------------------------------------------------------------------
-- Populate Instructors
-- ----------------------------------------------------------------------------
INSERT INTO Instructors (user_id, bio, specialization, years_experience, rating, is_verified) VALUES
(2, 'Experienced ICT trainer with 10+ years in corporate training. Microsoft Certified Professional with expertise in Office Suite and digital literacy programs.', 'ICT & Digital Skills', 10, 4.85, TRUE),
(3, 'Full-stack developer and certified instructor with passion for teaching modern programming languages. 8 years industry experience in software development.', 'Software Development', 8, 4.92, TRUE),
(4, 'Cybersecurity specialist and ethical hacker with CISSP certification. Former network administrator with extensive experience in data security.', 'Cybersecurity & Networks', 12, 4.78, TRUE),
(5, 'AI/ML researcher and data scientist with PhD in Computer Science. Published researcher with focus on practical applications of machine learning.', 'AI & Data Science', 6, 4.95, TRUE),
(6, 'Business consultant and entrepreneur with MBA. Specializes in digital transformation, project management, and business strategy.', 'Business & Management', 15, 4.80, TRUE),
(7, 'Award-winning graphic designer and digital marketer. Adobe Certified Expert with 7 years experience in creative industries.', 'Digital Media & Design', 7, 4.88, TRUE);

-- ----------------------------------------------------------------------------
-- Populate Courses (20 courses)
-- ----------------------------------------------------------------------------
INSERT INTO Courses (title, slug, description, short_description, category_id, difficulty_level, language, price, discount_price, duration_weeks, total_hours, start_date, end_date, status, is_featured, rating) VALUES
-- Core ICT & Digital Skills (4 courses)
('Certificate in Microsoft Office Suite', 'microsoft-office-suite', 'Comprehensive training in Word, Excel, PowerPoint, and Publisher. Learn document creation, spreadsheet analysis, presentations, and desktop publishing for professional environments.', 'Master Word, Excel, PowerPoint & Publisher', 1, 'Beginner', 'English', 250.00, NULL, 12, 48, '2025-01-15', '2025-04-15', 'Published', TRUE, 4.7),
('Certificate in ICT Support', 'ict-support', 'Hardware and software troubleshooting, system maintenance, and user support. Prepare for a career in IT support and help desk operations.', 'IT support and help desk training', 1, 'Intermediate', 'English', 300.00, 270.00, 16, 64, '2025-02-01', '2025-05-30', 'Published', FALSE, 4.6),
('Certificate in Digital Literacy', 'digital-literacy', 'Essential digital skills for the modern workplace including email, internet research, cloud storage, online collaboration, and digital safety.', 'Essential digital skills for everyone', 1, 'Beginner', 'English', 150.00, NULL, 8, 32, '2025-01-20', '2025-03-20', 'Published', TRUE, 4.8),
('Certificate in Record Management', 'record-management', 'Professional records and information management systems. Learn filing systems, document control, archiving, and compliance with data protection regulations.', 'Professional records management', 1, 'Intermediate', 'English', 280.00, NULL, 12, 48, '2025-02-15', '2025-05-15', 'Published', FALSE, 4.5),

-- Programming & Software Development (5 courses)
('Certificate in Python Programming', 'python-programming', 'Learn Python from basics to advanced concepts. Cover data structures, OOP, file handling, and popular libraries. Ideal for beginners and aspiring developers.', 'Master Python programming', 2, 'Beginner', 'English', 350.00, 315.00, 12, 60, '2025-01-10', '2025-04-10', 'Published', TRUE, 4.9),
('Certificate in Java Programming', 'java-programming', 'Master Java programming with hands-on projects. Learn OOP principles, Java collections, multithreading, and enterprise application development.', 'Complete Java development course', 2, 'Intermediate', 'English', 400.00, NULL, 16, 80, '2025-02-01', '2025-06-01', 'Published', FALSE, 4.7),
('Certificate in Web Development', 'web-development', 'Full-stack web development using HTML5, CSS3, JavaScript, and modern frameworks. Build responsive websites and web applications from scratch.', 'Build modern web applications', 2, 'Beginner', 'English', 380.00, 342.00, 14, 70, '2025-01-15', '2025-04-30', 'Published', TRUE, 4.8),
('Certificate in Mobile App Development', 'mobile-app-development', 'Create mobile applications for Android and iOS platforms. Learn Java/Kotlin for Android and Swift for iOS with practical app projects.', 'iOS and Android app development', 2, 'Advanced', 'English', 500.00, NULL, 20, 100, '2025-03-01', '2025-07-30', 'Published', TRUE, 4.6),
('Certificate in Software Engineering & Git', 'software-engineering-git', 'Software development methodologies, version control with Git/GitHub, testing, CI/CD, and collaborative development practices.', 'Professional software engineering', 2, 'Intermediate', 'English', 320.00, NULL, 12, 48, '2025-02-10', '2025-05-10', 'Published', FALSE, 4.7),

-- Data, Security & Networks (3 courses)
('Certificate in Data Analysis', 'data-analysis', 'Data analysis fundamentals using Excel, SQL, and Python. Learn data cleaning, visualization, statistical analysis, and reporting techniques.', 'Become a data analyst', 3, 'Beginner', 'English', 360.00, NULL, 12, 60, '2025-01-20', '2025-04-20', 'Published', TRUE, 4.8),
('Certificate in Cyber Security', 'cyber-security', 'Comprehensive cybersecurity training covering network security, ethical hacking, threat analysis, and security best practices. Industry-recognized certification.', 'Advanced cybersecurity training', 3, 'Advanced', 'English', 550.00, 495.00, 18, 90, '2025-02-15', '2025-06-30', 'Published', TRUE, 4.9),
('Certificate in Database Management Systems', 'database-management', 'Master database design and management using MySQL, PostgreSQL, and SQL Server. Learn SQL, normalization, optimization, and administration.', 'Database design and management', 3, 'Intermediate', 'English', 400.00, NULL, 16, 64, '2025-01-25', '2025-05-25', 'Published', FALSE, 4.6),

-- Emerging Technologies (2 courses)
('Certificate in AI & Machine Learning', 'ai-machine-learning', 'Introduction to artificial intelligence and machine learning. Learn algorithms, neural networks, and practical applications using Python and TensorFlow.', 'AI and ML fundamentals', 4, 'Advanced', 'English', 600.00, 540.00, 16, 80, '2025-03-01', '2025-07-01', 'Published', TRUE, 4.9),
('Certificate in Internet of Things', 'internet-of-things', 'IoT fundamentals including sensors, microcontrollers, connectivity, and cloud integration. Build smart devices and IoT solutions.', 'Build IoT solutions', 4, 'Intermediate', 'English', 450.00, NULL, 12, 60, '2025-02-20', '2025-05-20', 'Published', FALSE, 4.7),

-- Digital Media & Design (3 courses)
('Certificate in Graphic Designing', 'graphic-designing', 'Professional graphic design using Adobe Photoshop, Illustrator, and InDesign. Learn design principles, typography, branding, and print/digital media.', 'Master graphic design tools', 5, 'Beginner', 'English', 380.00, NULL, 14, 56, '2025-01-15', '2025-04-30', 'Published', TRUE, 4.8),
('Certificate in Digital Content Creation', 'digital-content-creation', 'Create engaging multimedia content for education and business. Video editing, animation, interactive presentations, and e-learning materials.', 'Multimedia content creation', 5, 'Intermediate', 'English', 350.00, 315.00, 12, 48, '2025-02-05', '2025-05-05', 'Published', FALSE, 4.6),
('Certificate in Digital Marketing', 'digital-marketing', 'Comprehensive digital marketing strategies including SEO, social media marketing, content marketing, email campaigns, and analytics.', 'Complete digital marketing', 5, 'Beginner', 'English', 320.00, NULL, 12, 48, '2025-01-20', '2025-04-20', 'Published', TRUE, 4.7),

-- Business & Management (3 courses)
('Certificate in Entrepreneurship', 'entrepreneurship', 'Start and grow your business with essential entrepreneurship skills. Business planning, financing, marketing, and operations management.', 'Start your own business', 6, 'Beginner', 'English', 300.00, NULL, 12, 48, '2025-01-10', '2025-04-10', 'Published', FALSE, 4.6),
('Certificate in Project Management', 'project-management', 'Professional project management methodologies including PMBOK, Agile, and Scrum. Plan, execute, and deliver successful projects.', 'Professional project management', 6, 'Intermediate', 'English', 450.00, 405.00, 16, 64, '2025-02-01', '2025-06-01', 'Published', TRUE, 4.8),
('Certificate in Financial Technology', 'financial-technology', 'Explore digital payments, blockchain, cryptocurrency, mobile money, and digital banking. Understand the future of financial services.', 'FinTech fundamentals', 6, 'Advanced', 'English', 480.00, NULL, 14, 56, '2025-03-01', '2025-06-15', 'Published', FALSE, 4.7);

-- ----------------------------------------------------------------------------
-- Populate Students
-- ----------------------------------------------------------------------------
INSERT INTO Students (user_id, date_of_birth, gender, city, country, enrollment_date) VALUES
(8, '1998-05-15', 'Male', 'Lusaka', 'Zambia', '2024-12-01'),
(9, '2000-08-22', 'Female', 'Ndola', 'Zambia', '2024-12-05'),
(10, '1995-03-10', 'Male', 'Kitwe', 'Zambia', '2024-12-10'),
(11, '1999-11-30', 'Female', 'Livingstone', 'Zambia', '2024-12-15'),
(12, '1997-07-18', 'Male', 'Lusaka', 'Zambia', '2024-12-20'),
(13, '2001-02-25', 'Female', 'Kabwe', 'Zambia', '2025-01-02'),
(14, '1996-09-12', 'Male', 'Chingola', 'Zambia', '2025-01-05'),
(15, '1998-12-05', 'Female', 'Lusaka', 'Zambia', '2025-01-08'),
(16, '2000-04-20', 'Male', 'Solwezi', 'Zambia', '2025-01-10'),
(17, '1999-06-08', 'Female', 'Mongu', 'Zambia', '2025-01-12'),
(18, '1997-10-15', 'Male', 'Kasama', 'Zambia', '2025-01-15'),
(19, '2001-01-30', 'Female', 'Lusaka', 'Zambia', '2025-01-18');

-- ----------------------------------------------------------------------------
-- Assign Instructors to Courses
-- ----------------------------------------------------------------------------
INSERT INTO Course_Instructors (course_id, instructor_id, role, assigned_date) VALUES
-- Core ICT - James Mwanza (instructor_id 1)
(1, 1, 'Lead', '2024-12-01'),
(2, 1, 'Lead', '2024-12-01'),
(3, 1, 'Lead', '2024-12-01'),
(4, 1, 'Lead', '2024-12-01'),

-- Programming - Sarah Banda (instructor_id 2)
(5, 2, 'Lead', '2024-12-01'),
(6, 2, 'Lead', '2024-12-01'),
(7, 2, 'Lead', '2024-12-01'),
(8, 2, 'Lead', '2024-12-01'),
(9, 2, 'Lead', '2024-12-01'),
(9, 5, 'Assistant', '2024-12-10'),

-- Data & Security - Peter Phiri (instructor_id 3), Grace Chanda (instructor_id 4)
(10, 4, 'Lead', '2024-12-01'),
(10, 3, 'Assistant', '2024-12-05'),
(11, 3, 'Lead', '2024-12-01'),
(12, 3, 'Lead', '2024-12-01'),

-- Emerging Tech - Grace Chanda (instructor_id 4)
(13, 4, 'Lead', '2024-12-01'),
(14, 4, 'Lead', '2024-12-01'),
(14, 3, 'Assistant', '2024-12-05'),

-- Digital Media - Mercy Zulu (instructor_id 6)
(15, 6, 'Lead', '2024-12-01'),
(16, 6, 'Lead', '2024-12-01'),
(17, 6, 'Lead', '2024-12-01'),

-- Business - Michael Siame (instructor_id 5)
(18, 5, 'Lead', '2024-12-01'),
(18, 1, 'Assistant', '2024-12-10'),
(19, 5, 'Lead', '2024-12-01'),
(20, 5, 'Lead', '2024-12-01');

-- ----------------------------------------------------------------------------
-- Populate Enrollments (30 enrollments with varied progress)
-- ----------------------------------------------------------------------------
INSERT INTO Enrollments (student_id, course_id, enrollment_date, start_date, progress, final_grade, status, completion_date, certificate_issued, total_time_spent) VALUES
-- Student 1: John Tembo
(1, 1, '2025-01-15', '2025-01-15', 100.00, 92.50, 'Completed', '2025-04-10', TRUE, 2880),
(1, 5, '2025-01-15', '2025-01-16', 75.00, NULL, 'In Progress', NULL, FALSE, 2700),
(1, 10, '2025-01-20', '2025-01-21', 45.00, NULL, 'In Progress', NULL, FALSE, 1620),

-- Student 2: Mary Lungu
(2, 7, '2025-01-15', '2025-01-15', 100.00, 88.00, 'Completed', '2025-04-25', TRUE, 4200),
(2, 15, '2025-01-15', '2025-01-16', 85.00, NULL, 'In Progress', NULL, FALSE, 2856),
(2, 17, '2025-01-20', '2025-01-21', 60.00, NULL, 'In Progress', NULL, FALSE, 1728),

-- Student 3: David Sakala
(3, 18, '2025-01-10', '2025-01-10', 100.00, 95.00, 'Completed', '2025-04-08', TRUE, 2880),
(3, 19, '2025-02-01', '2025-02-02', 30.00, NULL, 'In Progress', NULL, FALSE, 1152),
(3, 1, '2025-01-15', '2025-01-15', 100.00, 87.50, 'Completed', '2025-04-12', TRUE, 2640),

-- Student 4: Alice Mulenga
(4, 5, '2025-01-10', '2025-01-10', 100.00, 91.00, 'Completed', '2025-04-05', TRUE, 3600),
(4, 6, '2025-02-01', '2025-02-02', 50.00, NULL, 'In Progress', NULL, FALSE, 2400),
(4, 9, '2025-02-10', '2025-02-11', 25.00, NULL, 'In Progress', NULL, FALSE, 720),

-- Student 5: Robert Chilufya
(5, 11, '2025-02-15', '2025-02-16', 40.00, NULL, 'In Progress', NULL, FALSE, 2160),
(5, 12, '2025-01-25', '2025-01-26', 70.00, NULL, 'In Progress', NULL, FALSE, 2688),
(5, 2, '2025-02-01', '2025-02-02', 55.00, NULL, 'In Progress', NULL, FALSE, 2112),

-- Student 6: Susan Banda
(6, 3, '2025-01-20', '2025-01-20', 35.00, NULL, 'In Progress', NULL, FALSE, 672),
(6, 17, '2025-01-20', '2025-01-21', 40.00, NULL, 'In Progress', NULL, FALSE, 1152),

-- Student 7: Patrick Mutale
(7, 13, '2025-03-01', '2025-03-02', 15.00, NULL, 'Enrolled', NULL, FALSE, 720),
(7, 8, '2025-03-01', '2025-03-02', 20.00, NULL, 'Enrolled', NULL, FALSE, 1200),

-- Student 8: Elizabeth Phiri
(8, 18, '2025-01-10', '2025-01-10', 90.00, NULL, 'In Progress', NULL, FALSE, 2592),
(8, 3, '2025-01-20', '2025-01-20', 100.00, 94.00, 'Completed', '2025-03-15', TRUE, 1920),

-- Student 9: George Kunda
(9, 10, '2025-01-20', '2025-01-21', 65.00, NULL, 'In Progress', NULL, FALSE, 2340),
(9, 12, '2025-01-25', '2025-01-26', 40.00, NULL, 'In Progress', NULL, FALSE, 1536),

-- Student 10: Jennifer Musonda
(10, 15, '2025-01-15', '2025-01-16', 80.00, NULL, 'In Progress', NULL, FALSE, 2688),
(10, 16, '2025-02-05', '2025-02-06', 45.00, NULL, 'In Progress', NULL, FALSE, 1296),

-- Student 11: Moses Chola
(11, 1, '2025-01-15', '2025-01-16', 55.00, NULL, 'In Progress', NULL, FALSE, 1584),
(11, 4, '2025-02-15', '2025-02-16', 30.00, NULL, 'In Progress', NULL, FALSE, 864),

-- Student 12: Ruth Zimba
(12, 5, '2025-01-20', '2025-01-21', 10.00, NULL, 'Enrolled', NULL, FALSE, 360),
(12, 3, '2025-01-20', '2025-01-21', 15.00, NULL, 'Enrolled', NULL, FALSE, 288);

-- ----------------------------------------------------------------------------
-- Populate Modules (Sample modules for Python course - course_id 5)
-- ----------------------------------------------------------------------------
INSERT INTO Modules (course_id, title, description, display_order, duration_minutes, is_published) VALUES
(5, 'Introduction to Python', 'Getting started with Python programming, installation, and basic syntax', 1, 300, TRUE),
(5, 'Data Types and Variables', 'Understanding Python data types, variables, and operators', 2, 360, TRUE),
(5, 'Control Flow', 'Conditional statements, loops, and flow control in Python', 3, 420, TRUE),
(5, 'Functions and Modules', 'Creating functions, working with modules and packages', 4, 480, TRUE),
(5, 'Object-Oriented Programming', 'Classes, objects, inheritance, and OOP principles', 5, 540, TRUE),
(5, 'File Handling and Exceptions', 'Reading/writing files and error handling', 6, 360, TRUE);

-- Sample modules for Web Development (course_id 7)
INSERT INTO Modules (course_id, title, description, display_order, duration_minutes, is_published) VALUES
(7, 'HTML5 Fundamentals', 'Introduction to HTML5 structure, elements, and semantic markup', 1, 400, TRUE),
(7, 'CSS3 Styling', 'Styling web pages with CSS3, layouts, and responsive design', 2, 480, TRUE),
(7, 'JavaScript Basics', 'JavaScript fundamentals, DOM manipulation, and events', 3, 540, TRUE),
(7, 'Modern JavaScript', 'ES6+ features, async programming, and APIs', 4, 600, TRUE);

-- ----------------------------------------------------------------------------
-- Populate Lessons (Sample lessons for Python modules)
-- ----------------------------------------------------------------------------
INSERT INTO Lessons (module_id, title, content, lesson_type, duration_minutes, display_order, is_preview, points) VALUES
-- Module 1: Introduction to Python
(1, 'Welcome to Python Programming', 'Introduction to the course and what you will learn', 'Video', 15, 1, TRUE, 5),
(1, 'Installing Python and IDE Setup', 'Step-by-step guide to install Python and set up your development environment', 'Video', 30, 2, TRUE, 10),
(1, 'Your First Python Program', 'Writing and running your first "Hello World" program', 'Reading', 20, 3, FALSE, 10),
(1, 'Python Syntax Basics', 'Understanding Python syntax, indentation, and comments', 'Video', 25, 4, FALSE, 15),

-- Module 2: Data Types
(2, 'Numbers in Python', 'Working with integers, floats, and complex numbers', 'Video', 30, 1, FALSE, 15),
(2, 'Strings and String Methods', 'String manipulation and built-in string methods', 'Video', 35, 2, FALSE, 15),
(2, 'Lists and Tuples', 'Understanding ordered collections in Python', 'Reading', 40, 3, FALSE, 20),
(2, 'Dictionaries and Sets', 'Working with key-value pairs and unique collections', 'Video', 35, 4, FALSE, 20),

-- Module 3: Control Flow
(3, 'If-Else Statements', 'Conditional logic and decision making', 'Video', 30, 1, FALSE, 15),
(3, 'For Loops', 'Iterating over sequences with for loops', 'Video', 35, 2, FALSE, 15),
(3, 'While Loops', 'Using while loops for repeated execution', 'Reading', 25, 3, FALSE, 15),
(3, 'Control Flow Quiz', 'Test your understanding of control flow', 'Quiz', 30, 4, FALSE, 25);

-- ----------------------------------------------------------------------------
-- Populate Assignments (Sample assignments)
-- ----------------------------------------------------------------------------
INSERT INTO Assignments (course_id, title, description, instructions, max_points, passing_points, due_date, allow_late_submission) VALUES
(5, 'Python Basics Project', 'Create a simple calculator application', 'Build a command-line calculator that can perform basic arithmetic operations (addition, subtraction, multiplication, division). Include error handling for division by zero.', 100, 70, '2025-02-15 23:59:59', TRUE),
(5, 'Data Structures Assignment', 'Work with lists, dictionaries, and sets', 'Create a student management system using Python dictionaries to store student information. Implement functions to add, remove, and search students.', 100, 70, '2025-03-10 23:59:59', TRUE),
(7, 'Personal Portfolio Website', 'Build a responsive portfolio website', 'Create a multi-page portfolio website using HTML5, CSS3, and JavaScript. Must include: home page, about page, portfolio gallery, and contact form. Site must be fully responsive.', 150, 105, '2025-03-20 23:59:59', FALSE),
(11, 'Network Security Analysis', 'Perform security audit of a test network', 'Document security vulnerabilities in the provided test network environment. Submit a detailed report with findings and recommendations.', 100, 70, '2025-04-30 23:59:59', FALSE);

-- ----------------------------------------------------------------------------
-- Populate Assignment Submissions (Sample submissions)
-- ----------------------------------------------------------------------------
INSERT INTO Assignment_Submissions (assignment_id, student_id, submission_text, submitted_at, status, points_earned, feedback, graded_by, graded_at, is_late) VALUES
(1, 1, 'Calculator project completed. File uploaded to repository.', '2025-02-14 18:30:00', 'Graded', 95.00, 'Excellent work! Clean code and proper error handling. Well done.', 2, '2025-02-16 10:00:00', FALSE),
(1, 4, 'My calculator implementation with extended features.', '2025-02-15 20:00:00', 'Graded', 88.00, 'Good implementation. Consider adding more comments for clarity.', 2, '2025-02-17 14:30:00', FALSE),
(3, 2, 'Portfolio website completed with all requirements.', '2025-03-19 16:45:00', 'Graded', 142.00, 'Beautiful design and excellent responsive implementation!', 2, '2025-03-21 11:00:00', FALSE);

-- ----------------------------------------------------------------------------
-- Populate Quizzes
-- ----------------------------------------------------------------------------
INSERT INTO Quizzes (course_id, title, description, quiz_type, time_limit_minutes, max_attempts, passing_score, randomize_questions, show_correct_answers) VALUES
(5, 'Python Basics Quiz', 'Test your knowledge of Python fundamentals', 'Practice', 30, 3, 70.00, TRUE, TRUE),
(5, 'Python Midterm Exam', 'Comprehensive midterm covering modules 1-3', 'Midterm', 60, 1, 70.00, TRUE, FALSE),
(7, 'HTML & CSS Quiz', 'Assessment of HTML5 and CSS3 knowledge', 'Graded', 45, 2, 75.00, TRUE, TRUE),
(11, 'Cybersecurity Final Exam', 'Comprehensive final examination', 'Final Exam', 120, 1, 75.00, TRUE, FALSE);

-- ----------------------------------------------------------------------------
-- Populate Questions (Sample quiz questions)
-- ----------------------------------------------------------------------------
INSERT INTO Questions (question_type, question_text, points, explanation) VALUES
('Multiple Choice', 'What is the correct file extension for Python files?', 2, 'Python files use the .py extension'),
('Multiple Choice', 'Which of the following is a mutable data type in Python?', 2, 'Lists are mutable, while tuples and strings are immutable'),
('True/False', 'Python is a compiled language.', 1, 'Python is an interpreted language, not compiled'),
('Multiple Choice', 'What does HTML stand for?', 2, 'HTML stands for HyperText Markup Language'),
('Multiple Choice', 'Which CSS property is used to change text color?', 2, 'The color property is used to change text color'),
('Short Answer', 'Explain the difference between a list and a tuple in Python.', 5, 'Lists are mutable and use square brackets, tuples are immutable and use parentheses');

-- ----------------------------------------------------------------------------
-- Populate Question Options
-- ----------------------------------------------------------------------------
INSERT INTO Question_Options (question_id, option_text, is_correct, display_order) VALUES
-- Question 1: Python file extension
(1, '.pyth', FALSE, 1),
(1, '.py', TRUE, 2),
(1, '.pt', FALSE, 3),
(1, '.python', FALSE, 4),

-- Question 2: Mutable data type
(2, 'String', FALSE, 1),
(2, 'Tuple', FALSE, 2),
(2, 'List', TRUE, 3),
(2, 'Integer', FALSE, 4),

-- Question 3: Python compiled (True/False)
(3, 'True', FALSE, 1),
(3, 'False', TRUE, 2),

-- Question 4: HTML stands for
(4, 'HyperText Markup Language', TRUE, 1),
(4, 'High Tech Modern Language', FALSE, 2),
(4, 'Home Tool Markup Language', FALSE, 3),
(4, 'Hyperlinks and Text Markup Language', FALSE, 4),

-- Question 5: CSS color property
(5, 'font-color', FALSE, 1),
(5, 'text-color', FALSE, 2),
(5, 'color', TRUE, 3),
(5, 'foreground-color', FALSE, 4);

-- ----------------------------------------------------------------------------
-- Populate Quiz_Questions (Link questions to quizzes)
-- ----------------------------------------------------------------------------
INSERT INTO Quiz_Questions (quiz_id, question_id, display_order) VALUES
(1, 1, 1),
(1, 2, 2),
(1, 3, 3),
(3, 4, 1),
(3, 5, 2);

-- ----------------------------------------------------------------------------
-- Populate Quiz Attempts (Sample student quiz attempts)
-- ----------------------------------------------------------------------------
INSERT INTO Quiz_Attempts (quiz_id, student_id, attempt_number, started_at, submitted_at, score, status, time_spent_minutes) VALUES
(1, 1, 1, '2025-01-25 14:00:00', '2025-01-25 14:28:00', 85.00, 'Graded', 28),
(1, 4, 1, '2025-01-26 10:00:00', '2025-01-26 10:25:00', 92.00, 'Graded', 25),
(1, 4, 2, '2025-01-27 15:00:00', '2025-01-27 15:20:00', 98.00, 'Graded', 20),
(3, 2, 1, '2025-02-10 11:00:00', '2025-02-10 11:40:00', 88.00, 'Graded', 40);

-- ----------------------------------------------------------------------------
-- Populate Lesson Progress (Track student lesson completion)
-- ----------------------------------------------------------------------------
INSERT INTO Lesson_Progress (enrollment_id, lesson_id, status, progress_percentage, time_spent_minutes, started_at, completed_at) VALUES
-- Student 1 (enrollment_id 1) in Python course
(1, 1, 'Completed', 100.00, 15, '2025-01-15 09:00:00', '2025-01-15 09:15:00'),
(1, 2, 'Completed', 100.00, 30, '2025-01-15 09:20:00', '2025-01-15 09:50:00'),
(1, 3, 'Completed', 100.00, 20, '2025-01-15 10:00:00', '2025-01-15 10:20:00'),
(1, 4, 'Completed', 100.00, 25, '2025-01-15 10:30:00', '2025-01-15 10:55:00'),

-- Student 2 (enrollment_id 4) in Web Dev course
(4, 13, 'Completed', 100.00, 35, '2025-01-15 14:00:00', '2025-01-15 14:35:00'),
(4, 14, 'Completed', 100.00, 40, '2025-01-15 15:00:00', '2025-01-15 15:40:00'),

-- Student 4 (enrollment_id 10) in Python course
(10, 1, 'Completed', 100.00, 15, '2025-01-10 08:00:00', '2025-01-10 08:15:00'),
(10, 2, 'Completed', 100.00, 32, '2025-01-10 08:30:00', '2025-01-10 09:02:00'),
(10, 3, 'Completed', 100.00, 22, '2025-01-10 09:15:00', '2025-01-10 09:37:00');

-- ----------------------------------------------------------------------------
-- Populate Announcements
-- ----------------------------------------------------------------------------
INSERT INTO Announcements (course_id, posted_by, title, content, announcement_type, priority, is_published, published_at) VALUES
(5, 3, 'Welcome to Python Programming', 'Welcome to the Certificate in Python Programming! We are excited to have you join us. Please review the course syllabus and introduction materials.', 'Course', 'Normal', TRUE, '2025-01-08 10:00:00'),
(7, 3, 'Project Deadline Extension', 'Due to popular request, the portfolio project deadline has been extended by 3 days. New deadline: March 23, 2025.', 'Course', 'High', TRUE, '2025-03-15 14:30:00'),
(NULL, 1, 'Platform Maintenance Schedule', 'EduTrack LMS will undergo scheduled maintenance on Sunday, Feb 25 from 2:00 AM to 6:00 AM. The platform will be temporarily unavailable during this time.', 'System', 'Urgent', TRUE, '2025-02-20 09:00:00'),
(11, 4, 'Guest Lecture on Ethical Hacking', 'Join us for a special guest lecture by cybersecurity expert Dr. Ahmed Khan on March 5, 2025 at 3:00 PM.', 'Course', 'High', TRUE, '2025-02-28 11:00:00');

-- ----------------------------------------------------------------------------
-- Populate Discussions
-- ----------------------------------------------------------------------------
INSERT INTO Discussions (course_id, created_by, title, content, is_pinned, view_count, reply_count) VALUES
(5, 8, 'Best Python IDE for beginners?', 'I am new to Python and wondering what IDE you all recommend for beginners. I have heard about PyCharm, VS Code, and IDLE. What do you think?', FALSE, 45, 8),
(5, 11, 'Help with assignment 1', 'I am stuck on the calculator project. How do I handle the division by zero error properly?', FALSE, 23, 5),
(7, 9, 'Responsive design best practices', 'Can anyone share tips on making websites truly responsive? I am struggling with mobile layouts.', FALSE, 38, 12),
(11, 12, 'Career paths in cybersecurity', 'What are the different career paths available in cybersecurity? Looking for guidance.', TRUE, 67, 15);

-- ----------------------------------------------------------------------------
-- Populate Discussion Replies
-- ----------------------------------------------------------------------------
INSERT INTO Discussion_Replies (discussion_id, parent_reply_id, user_id, content, is_instructor_reply) VALUES
(1, NULL, 3, 'For beginners, I recommend starting with VS Code. It is lightweight, free, and has excellent Python support with extensions. PyCharm is great but can be overwhelming at first.', TRUE),
(1, NULL, 10, 'I use VS Code and love it! The Python extension is amazing.', FALSE),
(1, NULL, 11, 'Thanks! I will try VS Code.', FALSE),
(2, NULL, 3, 'Use a try-except block to catch the ZeroDivisionError. Check the lesson on exception handling for examples.', TRUE),
(2, NULL, 8, 'You can also check if the divisor is zero before performing the division.', FALSE),
(3, NULL, 7, 'Learn about CSS media queries and mobile-first design approach. Start designing for mobile and then scale up.', FALSE),
(3, NULL, 3, 'Great advice! Also check out CSS Grid and Flexbox for modern layout techniques.', TRUE);

-- ----------------------------------------------------------------------------
-- Populate Messages (Sample private messages)
-- ----------------------------------------------------------------------------
INSERT INTO Messages (sender_id, recipient_id, subject, content, is_read, read_at) VALUES
(8, 3, 'Question about Quiz 1', 'Hi Sarah, I have a question about question 5 on the Python basics quiz. Could you clarify what is being asked?', TRUE, '2025-01-26 09:30:00'),
(3, 8, 'Re: Question about Quiz 1', 'Hi John, question 5 is asking about mutable vs immutable data types. Review the lesson on data types for more details.', TRUE, '2025-01-26 14:00:00'),
(9, 2, 'Thank you!', 'Thank you for the excellent feedback on my portfolio project!', TRUE, '2025-03-22 10:15:00'),
(10, 4, 'Study group for final exam', 'Hey David, are you interested in forming a study group for the entrepreneurship final exam?', FALSE, NULL);

-- ----------------------------------------------------------------------------
-- Populate Certificates
-- ----------------------------------------------------------------------------
INSERT INTO Certificates (enrollment_id, certificate_number, issued_date, verification_code, is_verified) VALUES
(1, 'EDTRK-2025-000001', '2025-04-10', 'VRF-001-ABCD1234', TRUE),
(4, 'EDTRK-2025-000002', '2025-04-25', 'VRF-002-EFGH5678', TRUE),
(7, 'EDTRK-2025-000003', '2025-04-08', 'VRF-003-IJKL9012', TRUE),
(9, 'EDTRK-2025-000004', '2025-04-12', 'VRF-004-MNOP3456', TRUE),
(10, 'EDTRK-2025-000005', '2025-04-05', 'VRF-005-QRST7890', TRUE),
(21, 'EDTRK-2025-000006', '2025-03-15', 'VRF-006-UVWX1234', TRUE);

-- ----------------------------------------------------------------------------
-- Populate Badges
-- ----------------------------------------------------------------------------
INSERT INTO Badges (badge_name, description, badge_type, criteria, points, is_active) VALUES
('First Course Complete', 'Awarded for completing your first course', 'Course Completion', 'Complete any course with passing grade', 50, TRUE),
('Perfect Score', 'Achieved a perfect score on a quiz or assignment', 'Perfect Score', 'Score 100% on any graded assessment', 100, TRUE),
('Early Bird', 'Submitted assignment before the due date', 'Early Bird', 'Submit assignment at least 24 hours before deadline', 25, TRUE),
('Active Participant', 'Actively participated in course discussions', 'Participation', 'Post at least 10 discussion messages', 30, TRUE),
('Speed Learner', 'Completed a course faster than average', 'Course Completion', 'Complete course 20% faster than average completion time', 75, TRUE),
('Helping Hand', 'Helped fellow students in discussions', 'Participation', 'Have at least 5 replies marked as helpful', 40, TRUE);

-- ----------------------------------------------------------------------------
-- Populate Student Achievements
-- ----------------------------------------------------------------------------
INSERT INTO Student_Achievements (student_id, badge_id, course_id, earned_date, description) VALUES
(1, 1, 1, '2025-04-10', 'Completed Certificate in Microsoft Office Suite'),
(1, 3, 1, '2025-02-14', 'Submitted Python calculator assignment early'),
(4, 1, 5, '2025-04-05', 'Completed Certificate in Python Programming'),
(4, 2, 5, '2025-01-27', 'Perfect score on Python basics quiz (attempt 2)'),
(2, 1, 7, '2025-04-25', 'Completed Certificate in Web Development'),
(2, 3, 7, '2025-03-19', 'Submitted portfolio project early'),
(3, 1, 18, '2025-04-08', 'Completed Certificate in Entrepreneurship');

-- ----------------------------------------------------------------------------
-- Populate Payment Methods
-- ----------------------------------------------------------------------------
INSERT INTO Payment_Methods (method_name, description, is_active) VALUES
('Credit Card', 'Visa, Mastercard, American Express', TRUE),
('Mobile Money', 'MTN Mobile Money, Airtel Money', TRUE),
('Bank Transfer', 'Direct bank transfer', TRUE),
('PayPal', 'PayPal payment gateway', TRUE),
('Cash', 'Cash payment at office', TRUE);

-- ----------------------------------------------------------------------------
-- Populate Payments
-- ----------------------------------------------------------------------------
INSERT INTO Payments (student_id, course_id, enrollment_id, amount, currency, payment_method_id, payment_status, transaction_id, payment_date) VALUES
(1, 1, 1, 250.00, 'USD', 1, 'Completed', 'TXN-2025-000001', '2025-01-15 08:30:00'),
(1, 5, 2, 315.00, 'USD', 1, 'Completed', 'TXN-2025-000002', '2025-01-15 08:35:00'),
(1, 10, 3, 360.00, 'USD', 2, 'Completed', 'TXN-2025-000003', '2025-01-20 10:00:00'),
(2, 7, 4, 342.00, 'USD', 1, 'Completed', 'TXN-2025-000004', '2025-01-15 09:00:00'),
(2, 15, 5, 380.00, 'USD', 1, 'Completed', 'TXN-2025-000005', '2025-01-15 09:10:00'),
(2, 17, 6, 320.00, 'USD', 2, 'Completed', 'TXN-2025-000006', '2025-01-20 11:30:00'),
(3, 18, 7, 300.00, 'USD', 3, 'Completed', 'TXN-2025-000007', '2025-01-10 14:00:00'),
(3, 19, 8, 405.00, 'USD', 1, 'Completed', 'TXN-2025-000008', '2025-02-01 10:15:00'),
(3, 1, 9, 250.00, 'USD', 1, 'Completed', 'TXN-2025-000009', '2025-01-15 12:00:00'),
(4, 5, 10, 315.00, 'USD', 2, 'Completed', 'TXN-2025-000010', '2025-01-10 08:00:00'),
(4, 6, 11, 400.00, 'USD', 1, 'Completed', 'TXN-2025-000011', '2025-02-01 09:30:00'),
(4, 9, 12, 320.00, 'USD', 1, 'Completed', 'TXN-2025-000012', '2025-02-10 11:00:00'),
(5, 11, 13, 495.00, 'USD', 1, 'Completed', 'TXN-2025-000013', '2025-02-15 13:45:00'),
(5, 12, 14, 400.00, 'USD', 2, 'Completed', 'TXN-2025-000014', '2025-01-25 16:00:00'),
(5, 2, 15, 270.00, 'USD', 1, 'Completed', 'TXN-2025-000015', '2025-02-01 10:30:00'),
(7, 13, 19, 540.00, 'USD', 1, 'Pending', 'TXN-2025-000016', NULL);

-- ----------------------------------------------------------------------------
-- Populate Transactions
-- ----------------------------------------------------------------------------
INSERT INTO Transactions (payment_id, transaction_type, amount, currency, processed_at) VALUES
(1, 'Payment', 250.00, 'USD', '2025-01-15 08:30:15'),
(2, 'Payment', 315.00, 'USD', '2025-01-15 08:35:22'),
(3, 'Payment', 360.00, 'USD', '2025-01-20 10:00:45'),
(4, 'Payment', 342.00, 'USD', '2025-01-15 09:00:18'),
(5, 'Payment', 380.00, 'USD', '2025-01-15 09:10:33'),
(6, 'Payment', 320.00, 'USD', '2025-01-20 11:30:27'),
(7, 'Payment', 300.00, 'USD', '2025-01-10 14:00:51'),
(8, 'Payment', 405.00, 'USD', '2025-02-01 10:15:39'),
(9, 'Payment', 250.00, 'USD', '2025-01-15 12:00:12'),
(10, 'Payment', 315.00, 'USD', '2025-01-10 08:00:55');

-- ----------------------------------------------------------------------------
-- Populate Activity Logs (Sample recent activities)
-- ----------------------------------------------------------------------------
INSERT INTO Activity_Logs (user_id, activity_type, entity_type, entity_id, description, ip_address) VALUES
(8, 'login', NULL, NULL, 'User logged in', '192.168.1.100'),
(8, 'lesson_view', 'lesson', 1, 'Viewed lesson: Welcome to Python Programming', '192.168.1.100'),
(8, 'lesson_complete', 'lesson', 1, 'Completed lesson: Welcome to Python Programming', '192.168.1.100'),
(9, 'login', NULL, NULL, 'User logged in', '192.168.1.105'),
(9, 'assignment_submit', 'assignment', 3, 'Submitted assignment: Personal Portfolio Website', '192.168.1.105'),
(3, 'login', NULL, NULL, 'Instructor logged in', '10.0.0.50'),
(3, 'grade_assignment', 'assignment', 3, 'Graded assignment for student', '10.0.0.50'),
(11, 'login', NULL, NULL, 'User logged in', '192.168.1.120'),
(11, 'discussion_post', 'discussion', 2, 'Posted new discussion topic', '192.168.1.120'),
(1, 'login', NULL, NULL, 'Admin logged in', '10.0.0.10');

-- ----------------------------------------------------------------------------
-- Populate System Settings
-- ----------------------------------------------------------------------------
INSERT INTO System_Settings (setting_key, setting_value, setting_type, description, is_editable) VALUES
('site_name', 'EduTrack LMS', 'String', 'Website name displayed throughout the platform', TRUE),
('site_email', 'info@edutrack.edu', 'String', 'Main contact email for the platform', TRUE),
('max_file_upload_size', '10', 'Number', 'Maximum file upload size in MB', TRUE),
('allow_student_discussion', 'true', 'Boolean', 'Allow students to create discussion topics', TRUE),
('certificate_auto_generate', 'true', 'Boolean', 'Automatically generate certificates upon course completion', TRUE),
('session_timeout_minutes', '30', 'Number', 'User session timeout in minutes', TRUE),
('enable_email_notifications', 'true', 'Boolean', 'Enable email notifications for users', TRUE),
('platform_version', '1.0.0', 'String', 'Current platform version', FALSE),
('maintenance_mode', 'false', 'Boolean', 'Enable maintenance mode', TRUE),
('default_currency', 'USD', 'String', 'Default currency for payments', TRUE);

-- ----------------------------------------------------------------------------
-- Populate Email Templates
-- ----------------------------------------------------------------------------
INSERT INTO Email_Templates (template_name, subject, body, template_type, is_active) VALUES
('welcome_email', 'Welcome to EduTrack LMS!', '<h1>Welcome {{first_name}}!</h1><p>Thank you for joining EduTrack LMS. We are excited to have you on board.</p><p>You can now access all your courses from your dashboard.</p>', 'Welcome', TRUE),
('enrollment_confirmation', 'Course Enrollment Confirmation', '<h1>Enrollment Confirmed</h1><p>Dear {{first_name}},</p><p>You have successfully enrolled in <strong>{{course_title}}</strong>.</p><p>Course starts on: {{start_date}}</p>', 'Enrollment', TRUE),
('certificate_issued', 'Your Certificate is Ready!', '<h1>Congratulations {{first_name}}!</h1><p>Your certificate for <strong>{{course_title}}</strong> is now available.</p><p>Certificate Number: {{certificate_number}}</p><p>Download your certificate from your dashboard.</p>', 'Certificate', TRUE),
('payment_confirmation', 'Payment Received', '<h1>Payment Confirmation</h1><p>Dear {{first_name}},</p><p>We have received your payment of {{amount}} {{currency}} for <strong>{{course_title}}</strong>.</p><p>Transaction ID: {{transaction_id}}</p>', 'Payment', TRUE),
('assignment_deadline_reminder', 'Assignment Due Soon', '<h1>Reminder: Assignment Due</h1><p>Hi {{first_name}},</p><p>This is a reminder that your assignment <strong>{{assignment_title}}</strong> is due on {{due_date}}.</p>', 'Reminder', TRUE);

-- ----------------------------------------------------------------------------
-- Populate Notifications (Recent in-app notifications)
-- ----------------------------------------------------------------------------
INSERT INTO Notifications (user_id, title, message, notification_type, is_read, action_url) VALUES
(8, 'Welcome to EduTrack!', 'Your account has been successfully created. Start exploring courses now!', 'Success', TRUE, '/dashboard'),
(8, 'Assignment Graded', 'Your assignment "Python Basics Project" has been graded. Score: 95/100', 'Grade', TRUE, '/courses/5/assignments/1'),
(9, 'New Announcement', 'Project deadline has been extended to March 23, 2025', 'Announcement', TRUE, '/courses/7/announcements'),
(11, 'Assignment Due Soon', 'Assignment "Data Structures Assignment" is due in 3 days', 'Warning', FALSE, '/courses/5/assignments/2'),
(4, 'Certificate Ready', 'Your certificate for Python Programming is ready for download!', 'Success', TRUE, '/certificates/5'),
(2, 'New Reply to Your Discussion', 'Sarah Banda replied to your discussion about responsive design', 'Info', FALSE, '/courses/7/discussions/3'),
(10, 'Payment Successful', 'Your payment of $380.00 for Graphic Designing course was successful', 'Success', TRUE, '/payments/5');

-- ============================================================================
-- SECTION 11: VERIFICATION AND ANALYSIS QUERIES
-- ============================================================================

-- View comprehensive course statistics
SELECT
    cc.category_name,
    COUNT(DISTINCT c.course_id) as total_courses,
    COUNT(DISTINCT e.enrollment_id) as total_enrollments,
    AVG(c.price) as avg_price,
    SUM(CASE WHEN c.is_featured THEN 1 ELSE 0 END) as featured_courses,
    AVG(c.rating) as avg_rating
FROM Course_Categories cc
LEFT JOIN Courses c ON cc.category_id = c.category_id
LEFT JOIN Enrollments e ON c.course_id = e.course_id
GROUP BY cc.category_id, cc.category_name
ORDER BY total_enrollments DESC;

-- View instructor performance metrics
SELECT
    u.first_name,
    u.last_name,
    u.email,
    i.specialization,
    i.rating as instructor_rating,
    COUNT(DISTINCT ci.course_id) as courses_taught,
    COUNT(DISTINCT e.student_id) as total_students,
    AVG(c.rating) as avg_course_rating
FROM Instructors i
JOIN Users u ON i.user_id = u.user_id
LEFT JOIN Course_Instructors ci ON i.instructor_id = ci.instructor_id AND ci.role = 'Lead'
LEFT JOIN Courses c ON ci.course_id = c.course_id
LEFT JOIN Enrollments e ON c.course_id = e.course_id
GROUP BY i.instructor_id
ORDER BY total_students DESC;

-- View student enrollment and progress summary
SELECT
    u.first_name,
    u.last_name,
    u.email,
    s.city,
    COUNT(DISTINCT e.enrollment_id) as total_enrollments,
    AVG(e.progress) as avg_progress,
    SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completed_courses,
    SUM(CASE WHEN e.certificate_issued THEN 1 ELSE 0 END) as certificates_earned,
    COUNT(DISTINCT sa.achievement_id) as badges_earned
FROM Students s
JOIN Users u ON s.user_id = u.user_id
LEFT JOIN Enrollments e ON s.student_id = e.student_id
LEFT JOIN Student_Achievements sa ON s.student_id = sa.student_id
GROUP BY s.student_id
ORDER BY completed_courses DESC, avg_progress DESC;

-- View course popularity and revenue analysis
SELECT
    c.title,
    c.price,
    c.discount_price,
    c.difficulty_level,
    c.rating,
    COUNT(DISTINCT e.enrollment_id) as total_enrollments,
    AVG(e.progress) as avg_student_progress,
    SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completions,
    ROUND(SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(e.enrollment_id), 0), 2) as completion_rate,
    SUM(p.amount) as total_revenue
FROM Courses c
LEFT JOIN Enrollments e ON c.course_id = e.course_id
LEFT JOIN Payments p ON c.course_id = p.course_id AND p.payment_status = 'Completed'
GROUP BY c.course_id
HAVING total_enrollments > 0
ORDER BY total_revenue DESC, total_enrollments DESC;

-- View payment and revenue summary
SELECT
    pm.method_name,
    COUNT(p.payment_id) as total_transactions,
    SUM(CASE WHEN p.payment_status = 'Completed' THEN 1 ELSE 0 END) as successful_payments,
    SUM(CASE WHEN p.payment_status = 'Pending' THEN 1 ELSE 0 END) as pending_payments,
    SUM(CASE WHEN p.payment_status = 'Failed' THEN 1 ELSE 0 END) as failed_payments,
    SUM(CASE WHEN p.payment_status = 'Completed' THEN p.amount ELSE 0 END) as total_revenue,
    p.currency
FROM Payment_Methods pm
LEFT JOIN Payments p ON pm.payment_method_id = p.payment_method_id
GROUP BY pm.payment_method_id, p.currency
ORDER BY total_revenue DESC;

-- View assignment submission and grading statistics
SELECT
    c.title as course_title,
    a.title as assignment_title,
    a.max_points,
    COUNT(asub.submission_id) as total_submissions,
    SUM(CASE WHEN asub.status = 'Graded' THEN 1 ELSE 0 END) as graded_submissions,
    AVG(asub.points_earned) as avg_score,
    SUM(CASE WHEN asub.is_late THEN 1 ELSE 0 END) as late_submissions
FROM Assignments a
JOIN Courses c ON a.course_id = c.course_id
LEFT JOIN Assignment_Submissions asub ON a.assignment_id = asub.assignment_id
GROUP BY a.assignment_id
ORDER BY c.title, a.title;

-- View discussion engagement metrics
SELECT
    c.title as course_title,
    COUNT(DISTINCT d.discussion_id) as total_discussions,
    SUM(d.reply_count) as total_replies,
    SUM(d.view_count) as total_views,
    AVG(d.reply_count) as avg_replies_per_discussion
FROM Courses c
LEFT JOIN Discussions d ON c.course_id = d.course_id
GROUP BY c.course_id
HAVING total_discussions > 0
ORDER BY total_discussions DESC;

-- View certificate issuance summary
SELECT
    c.title as course_title,
    COUNT(cert.certificate_id) as certificates_issued,
    MIN(cert.issued_date) as first_certificate_date,
    MAX(cert.issued_date) as last_certificate_date
FROM Certificates cert
JOIN Enrollments e ON cert.enrollment_id = e.enrollment_id
JOIN Courses c ON e.course_id = c.course_id
GROUP BY c.course_id
ORDER BY certificates_issued DESC;

-- Overall platform statistics
SELECT
    'Total Users' as metric,
    COUNT(*) as value
FROM Users
UNION ALL
SELECT
    'Total Students',
    COUNT(*)
FROM Students
UNION ALL
SELECT
    'Total Instructors',
    COUNT(*)
FROM Instructors
UNION ALL
SELECT
    'Total Courses',
    COUNT(*)
FROM Courses
WHERE status = 'Published'
UNION ALL
SELECT
    'Total Enrollments',
    COUNT(*)
FROM Enrollments
UNION ALL
SELECT
    'Completed Courses',
    COUNT(*)
FROM Enrollments
WHERE status = 'Completed'
UNION ALL
SELECT
    'Total Revenue (USD)',
    ROUND(SUM(amount), 2)
FROM Payments
WHERE payment_status = 'Completed'
UNION ALL
SELECT
    'Certificates Issued',
    COUNT(*)
FROM Certificates
UNION ALL
SELECT
    'Active Discussions',
    COUNT(*)
FROM Discussions;

-- ============================================================================
-- END OF COMPLETE LMS SCHEMA
-- ============================================================================
