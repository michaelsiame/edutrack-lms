-- ============================================================================
-- EDUTRACK LMS - COURSE MANAGEMENT DATABASE SCHEMA
-- ============================================================================
-- Description: Complete SQL script to create and populate a course management
--              system database with realistic sample data for testing
-- Database: MySQL Compatible
-- Created: 2025-11-17
-- ============================================================================

-- Drop existing tables if they exist (for clean installation)
DROP TABLE IF EXISTS Enrollments;
DROP TABLE IF EXISTS Course_Instructors;
DROP TABLE IF EXISTS Students;
DROP TABLE IF EXISTS Courses;
DROP TABLE IF EXISTS Instructors;
DROP TABLE IF EXISTS Course_Categories;

-- ============================================================================
-- SECTION 1: DATA DEFINITION LANGUAGE (DDL) - TABLE CREATION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: Course_Categories
-- Purpose: Stores course category information for grouping related courses
-- ----------------------------------------------------------------------------
CREATE TABLE Course_Categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Instructors
-- Purpose: Stores instructor/teacher information
-- ----------------------------------------------------------------------------
CREATE TABLE Instructors (
    instructor_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    bio TEXT,
    specialization VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Courses
-- Purpose: Stores detailed course information
-- ----------------------------------------------------------------------------
CREATE TABLE Courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    difficulty_level ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL DEFAULT 'Beginner',
    start_date DATE,
    end_date DATE,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    duration_weeks INT,
    max_students INT DEFAULT 30,
    status ENUM('Draft', 'Published', 'Archived') DEFAULT 'Published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES Course_Categories(category_id) ON DELETE RESTRICT,
    CONSTRAINT chk_dates CHECK (end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Students
-- Purpose: Stores student/learner information
-- ----------------------------------------------------------------------------
CREATE TABLE Students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    registration_date DATE NOT NULL,
    date_of_birth DATE,
    address TEXT,
    status ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Course_Instructors
-- Purpose: Junction table for many-to-many relationship between courses and instructors
-- ----------------------------------------------------------------------------
CREATE TABLE Course_Instructors (
    course_instructor_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    instructor_id INT NOT NULL,
    role ENUM('Lead', 'Assistant', 'Guest') DEFAULT 'Lead',
    assigned_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES Instructors(instructor_id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_instructor (course_id, instructor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: Enrollments
-- Purpose: Tracks student enrollments in courses with progress tracking
-- ----------------------------------------------------------------------------
CREATE TABLE Enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    progress DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    grade DECIMAL(5, 2),
    status ENUM('Enrolled', 'In Progress', 'Completed', 'Dropped') DEFAULT 'Enrolled',
    completion_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id),
    CONSTRAINT chk_progress CHECK (progress >= 0 AND progress <= 100),
    CONSTRAINT chk_grade CHECK (grade IS NULL OR (grade >= 0 AND grade <= 100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 2: DATA MANIPULATION LANGUAGE (DML) - DATA INSERTION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Populate Course_Categories
-- ----------------------------------------------------------------------------
INSERT INTO Course_Categories (category_name, category_description) VALUES
('Core ICT & Digital Skills', 'Fundamental computer and digital literacy courses covering essential office applications, digital tools, and basic ICT competencies'),
('Programming & Software Development', 'Programming languages, software engineering practices, web and mobile application development courses'),
('Data, Security & Networks', 'Data analysis, cybersecurity, database management, and network infrastructure courses'),
('Emerging Technologies', 'Cutting-edge technology courses including AI, machine learning, and Internet of Things'),
('Digital Media & Design', 'Creative and digital content courses covering graphic design, multimedia, and digital marketing'),
('Business & Management', 'Business administration, entrepreneurship, project management, and professional development courses');

-- ----------------------------------------------------------------------------
-- Populate Instructors
-- ----------------------------------------------------------------------------
INSERT INTO Instructors (first_name, last_name, email, phone, bio, specialization) VALUES
('James', 'Mwanza', 'james.mwanza@edutrack.edu', '+260977123456', 'Experienced ICT trainer with 10+ years in corporate training. Microsoft Certified Professional with expertise in Office Suite and digital literacy programs.', 'ICT & Digital Skills'),
('Sarah', 'Banda', 'sarah.banda@edutrack.edu', '+260966234567', 'Full-stack developer and certified instructor with passion for teaching modern programming languages. 8 years industry experience in software development.', 'Software Development'),
('Peter', 'Phiri', 'peter.phiri@edutrack.edu', '+260955345678', 'Cybersecurity specialist and ethical hacker with CISSP certification. Former network administrator with extensive experience in data security.', 'Cybersecurity & Networks'),
('Grace', 'Chanda', 'grace.chanda@edutrack.edu', '+260944456789', 'AI/ML researcher and data scientist with PhD in Computer Science. Published researcher with focus on practical applications of machine learning.', 'AI & Data Science'),
('Michael', 'Siame', 'michael.siame@edutrack.edu', '+260933567890', 'Business consultant and entrepreneur with MBA. Specializes in digital transformation, project management, and business strategy.', 'Business & Management'),
('Mercy', 'Zulu', 'mercy.zulu@edutrack.edu', '+260922678901', 'Award-winning graphic designer and digital marketer. Adobe Certified Expert with 7 years experience in creative industries.', 'Digital Media & Design');

-- ----------------------------------------------------------------------------
-- Populate Courses (20 courses across all categories)
-- ----------------------------------------------------------------------------

-- Core ICT & Digital Skills (4 courses)
INSERT INTO Courses (title, description, category_id, difficulty_level, start_date, end_date, price, duration_weeks) VALUES
('Certificate in Microsoft Office Suite', 'Comprehensive training in Word, Excel, PowerPoint, and Publisher. Learn document creation, spreadsheet analysis, presentations, and desktop publishing for professional environments.', 1, 'Beginner', '2025-01-15', '2025-04-15', 250.00, 12),
('Certificate in ICT Support', 'Hardware and software troubleshooting, system maintenance, and user support. Prepare for a career in IT support and help desk operations.', 1, 'Intermediate', '2025-02-01', '2025-05-30', 300.00, 16),
('Certificate in Digital Literacy', 'Essential digital skills for the modern workplace including email, internet research, cloud storage, online collaboration, and digital safety.', 1, 'Beginner', '2025-01-20', '2025-03-20', 150.00, 8),
('Certificate in Record Management', 'Professional records and information management systems. Learn filing systems, document control, archiving, and compliance with data protection regulations.', 1, 'Intermediate', '2025-02-15', '2025-05-15', 280.00, 12);

-- Programming & Software Development (5 courses)
INSERT INTO Courses (title, description, category_id, difficulty_level, start_date, end_date, price, duration_weeks) VALUES
('Certificate in Python Programming', 'Learn Python from basics to advanced concepts. Cover data structures, OOP, file handling, and popular libraries. Ideal for beginners and aspiring developers.', 2, 'Beginner', '2025-01-10', '2025-04-10', 350.00, 12),
('Certificate in Java Programming', 'Master Java programming with hands-on projects. Learn OOP principles, Java collections, multithreading, and enterprise application development.', 2, 'Intermediate', '2025-02-01', '2025-06-01', 400.00, 16),
('Certificate in Web Development', 'Full-stack web development using HTML5, CSS3, JavaScript, and modern frameworks. Build responsive websites and web applications from scratch.', 2, 'Beginner', '2025-01-15', '2025-04-30', 380.00, 14),
('Certificate in Mobile App Development', 'Create mobile applications for Android and iOS platforms. Learn Java/Kotlin for Android and Swift for iOS with practical app projects.', 2, 'Advanced', '2025-03-01', '2025-07-30', 500.00, 20),
('Certificate in Software Engineering & Git', 'Software development methodologies, version control with Git/GitHub, testing, CI/CD, and collaborative development practices.', 2, 'Intermediate', '2025-02-10', '2025-05-10', 320.00, 12);

-- Data, Security & Networks (3 courses)
INSERT INTO Courses (title, description, category_id, difficulty_level, start_date, end_date, price, duration_weeks) VALUES
('Certificate in Data Analysis', 'Data analysis fundamentals using Excel, SQL, and Python. Learn data cleaning, visualization, statistical analysis, and reporting techniques.', 3, 'Beginner', '2025-01-20', '2025-04-20', 360.00, 12),
('Certificate in Cyber Security', 'Comprehensive cybersecurity training covering network security, ethical hacking, threat analysis, and security best practices. Industry-recognized certification.', 3, 'Advanced', '2025-02-15', '2025-06-30', 550.00, 18),
('Certificate in Database Management Systems', 'Master database design and management using MySQL, PostgreSQL, and SQL Server. Learn SQL, normalization, optimization, and administration.', 3, 'Intermediate', '2025-01-25', '2025-05-25', 400.00, 16);

-- Emerging Technologies (2 courses)
INSERT INTO Courses (title, description, category_id, difficulty_level, start_date, end_date, price, duration_weeks) VALUES
('Certificate in AI & Machine Learning', 'Introduction to artificial intelligence and machine learning. Learn algorithms, neural networks, and practical applications using Python and TensorFlow.', 4, 'Advanced', '2025-03-01', '2025-07-01', 600.00, 16),
('Certificate in Internet of Things (IoT)', 'IoT fundamentals including sensors, microcontrollers, connectivity, and cloud integration. Build smart devices and IoT solutions.', 4, 'Intermediate', '2025-02-20', '2025-05-20', 450.00, 12);

-- Digital Media & Design (3 courses)
INSERT INTO Courses (title, description, category_id, difficulty_level, start_date, end_date, price, duration_weeks) VALUES
('Certificate in Graphic Designing', 'Professional graphic design using Adobe Photoshop, Illustrator, and InDesign. Learn design principles, typography, branding, and print/digital media.', 5, 'Beginner', '2025-01-15', '2025-04-30', 380.00, 14),
('Certificate in Digital Content Creation', 'Create engaging multimedia content for education and business. Video editing, animation, interactive presentations, and e-learning materials.', 5, 'Intermediate', '2025-02-05', '2025-05-05', 350.00, 12),
('Certificate in Digital Marketing', 'Comprehensive digital marketing strategies including SEO, social media marketing, content marketing, email campaigns, and analytics.', 5, 'Beginner', '2025-01-20', '2025-04-20', 320.00, 12);

-- Business & Management (3 courses)
INSERT INTO Courses (title, description, category_id, difficulty_level, start_date, end_date, price, duration_weeks) VALUES
('Certificate in Entrepreneurship', 'Start and grow your business with essential entrepreneurship skills. Business planning, financing, marketing, and operations management.', 6, 'Beginner', '2025-01-10', '2025-04-10', 300.00, 12),
('Certificate in Project Management', 'Professional project management methodologies including PMBOK, Agile, and Scrum. Plan, execute, and deliver successful projects.', 6, 'Intermediate', '2025-02-01', '2025-06-01', 450.00, 16),
('Certificate in Financial Technology (FinTech)', 'Explore digital payments, blockchain, cryptocurrency, mobile money, and digital banking. Understand the future of financial services.', 6, 'Advanced', '2025-03-01', '2025-06-15', 480.00, 14);

-- ----------------------------------------------------------------------------
-- Populate Students (12 students for diverse enrollment scenarios)
-- ----------------------------------------------------------------------------
INSERT INTO Students (first_name, last_name, email, phone, registration_date, date_of_birth, status) VALUES
('John', 'Tembo', 'john.tembo@email.com', '+260971111111', '2024-12-01', '1998-05-15', 'Active'),
('Mary', 'Lungu', 'mary.lungu@email.com', '+260972222222', '2024-12-05', '2000-08-22', 'Active'),
('David', 'Sakala', 'david.sakala@email.com', '+260973333333', '2024-12-10', '1995-03-10', 'Active'),
('Alice', 'Mulenga', 'alice.mulenga@email.com', '+260974444444', '2024-12-15', '1999-11-30', 'Active'),
('Robert', 'Chilufya', 'robert.chilufya@email.com', '+260975555555', '2024-12-20', '1997-07-18', 'Active'),
('Susan', 'Banda', 'susan.banda@email.com', '+260976666666', '2025-01-02', '2001-02-25', 'Active'),
('Patrick', 'Mutale', 'patrick.mutale@email.com', '+260977777777', '2025-01-05', '1996-09-12', 'Active'),
('Elizabeth', 'Phiri', 'elizabeth.phiri@email.com', '+260978888888', '2025-01-08', '1998-12-05', 'Active'),
('George', 'Kunda', 'george.kunda@email.com', '+260979999999', '2025-01-10', '2000-04-20', 'Active'),
('Jennifer', 'Musonda', 'jennifer.musonda@email.com', '+260970000000', '2025-01-12', '1999-06-08', 'Active'),
('Moses', 'Chola', 'moses.chola@email.com', '+260971234567', '2025-01-15', '1997-10-15', 'Active'),
('Ruth', 'Zimba', 'ruth.zimba@email.com', '+260972345678', '2025-01-18', '2001-01-30', 'Active');

-- ----------------------------------------------------------------------------
-- Populate Course_Instructors (Assign instructors to courses)
-- ----------------------------------------------------------------------------
INSERT INTO Course_Instructors (course_id, instructor_id, role, assigned_date) VALUES
-- Core ICT courses - James Mwanza
(1, 1, 'Lead', '2024-12-01'),
(2, 1, 'Lead', '2024-12-01'),
(3, 1, 'Lead', '2024-12-01'),
(4, 1, 'Lead', '2024-12-01'),

-- Programming courses - Sarah Banda (with Michael as assistant for Software Engineering)
(5, 2, 'Lead', '2024-12-01'),
(6, 2, 'Lead', '2024-12-01'),
(7, 2, 'Lead', '2024-12-01'),
(8, 2, 'Lead', '2024-12-01'),
(9, 2, 'Lead', '2024-12-01'),
(9, 5, 'Assistant', '2024-12-10'),

-- Data & Security courses - Peter Phiri (with Grace assisting on Data Analysis)
(10, 3, 'Assistant', '2024-12-01'),
(10, 4, 'Lead', '2024-12-01'),
(11, 3, 'Lead', '2024-12-01'),
(12, 3, 'Lead', '2024-12-01'),

-- Emerging Tech - Grace Chanda (with Peter assisting on IoT)
(13, 4, 'Lead', '2024-12-01'),
(14, 4, 'Lead', '2024-12-01'),
(14, 3, 'Assistant', '2024-12-05'),

-- Digital Media - Mercy Zulu
(15, 6, 'Lead', '2024-12-01'),
(16, 6, 'Lead', '2024-12-01'),
(17, 6, 'Lead', '2024-12-01'),

-- Business courses - Michael Siame (with James assisting on Entrepreneurship)
(18, 5, 'Lead', '2024-12-01'),
(18, 1, 'Assistant', '2024-12-10'),
(19, 5, 'Lead', '2024-12-01'),
(20, 5, 'Lead', '2024-12-01');

-- ----------------------------------------------------------------------------
-- Populate Enrollments (25+ enrollments with varied progress levels)
-- ----------------------------------------------------------------------------
INSERT INTO Enrollments (student_id, course_id, enrollment_date, progress, status, grade, completion_date) VALUES
-- Student 1: John Tembo - Multiple courses, various progress
(1, 1, '2025-01-15', 100.00, 'Completed', 92.50, '2025-04-10'),
(1, 5, '2025-01-15', 75.00, 'In Progress', NULL, NULL),
(1, 10, '2025-01-20', 45.00, 'In Progress', NULL, NULL),

-- Student 2: Mary Lungu - Web development and design focus
(2, 7, '2025-01-15', 100.00, 'Completed', 88.00, '2025-04-25'),
(2, 15, '2025-01-15', 85.00, 'In Progress', NULL, NULL),
(2, 17, '2025-01-20', 60.00, 'In Progress', NULL, NULL),

-- Student 3: David Sakala - Business and management
(3, 18, '2025-01-10', 100.00, 'Completed', 95.00, '2025-04-08'),
(3, 19, '2025-02-01', 30.00, 'In Progress', NULL, NULL),
(3, 1, '2025-01-15', 100.00, 'Completed', 87.50, '2025-04-12'),

-- Student 4: Alice Mulenga - Programming enthusiast
(4, 5, '2025-01-10', 100.00, 'Completed', 91.00, '2025-04-05'),
(4, 6, '2025-02-01', 50.00, 'In Progress', NULL, NULL),
(4, 9, '2025-02-10', 25.00, 'In Progress', NULL, NULL),

-- Student 5: Robert Chilufya - Cybersecurity focus
(5, 11, '2025-02-15', 40.00, 'In Progress', NULL, NULL),
(5, 12, '2025-01-25', 70.00, 'In Progress', NULL, NULL),
(5, 2, '2025-02-01', 55.00, 'In Progress', NULL, NULL),

-- Student 6: Susan Banda - Just started digital literacy
(6, 3, '2025-01-20', 35.00, 'In Progress', NULL, NULL),
(6, 17, '2025-01-20', 40.00, 'In Progress', NULL, NULL),

-- Student 7: Patrick Mutale - Advanced tech courses
(7, 13, '2025-03-01', 15.00, 'In Progress', NULL, NULL),
(7, 8, '2025-03-01', 20.00, 'In Progress', NULL, NULL),

-- Student 8: Elizabeth Phiri - Business starter
(8, 18, '2025-01-10', 90.00, 'In Progress', NULL, NULL),
(8, 3, '2025-01-20', 100.00, 'Completed', 94.00, '2025-03-15'),

-- Student 9: George Kunda - Data analytics path
(9, 10, '2025-01-20', 65.00, 'In Progress', NULL, NULL),
(9, 12, '2025-01-25', 40.00, 'In Progress', NULL, NULL),

-- Student 10: Jennifer Musonda - Creative professional
(10, 15, '2025-01-15', 80.00, 'In Progress', NULL, NULL),
(10, 16, '2025-02-05', 45.00, 'In Progress', NULL, NULL),

-- Student 11: Moses Chola - ICT fundamentals
(11, 1, '2025-01-15', 55.00, 'In Progress', NULL, NULL),
(11, 4, '2025-02-15', 30.00, 'In Progress', NULL, NULL),

-- Student 12: Ruth Zimba - New enrollments, just started
(12, 5, '2025-01-20', 10.00, 'Enrolled', NULL, NULL),
(12, 3, '2025-01-20', 15.00, 'Enrolled', NULL, NULL);

-- ============================================================================
-- SECTION 3: VERIFICATION QUERIES
-- ============================================================================

-- View all categories with course counts
SELECT
    cc.category_name,
    COUNT(c.course_id) as total_courses,
    AVG(c.price) as avg_price
FROM Course_Categories cc
LEFT JOIN Courses c ON cc.category_id = c.category_id
GROUP BY cc.category_id, cc.category_name
ORDER BY cc.category_id;

-- View all courses with their instructors
SELECT
    c.title,
    c.difficulty_level,
    c.price,
    cc.category_name,
    GROUP_CONCAT(CONCAT(i.first_name, ' ', i.last_name, ' (', ci.role, ')') SEPARATOR ', ') as instructors
FROM Courses c
JOIN Course_Categories cc ON c.category_id = cc.category_id
LEFT JOIN Course_Instructors ci ON c.course_id = ci.course_id
LEFT JOIN Instructors i ON ci.instructor_id = i.instructor_id
GROUP BY c.course_id
ORDER BY cc.category_name, c.title;

-- View enrollment statistics
SELECT
    s.first_name,
    s.last_name,
    COUNT(e.enrollment_id) as enrolled_courses,
    AVG(e.progress) as avg_progress,
    SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completed_courses
FROM Students s
LEFT JOIN Enrollments e ON s.student_id = e.student_id
GROUP BY s.student_id
ORDER BY enrolled_courses DESC;

-- View course popularity and completion rates
SELECT
    c.title,
    COUNT(e.enrollment_id) as total_enrollments,
    AVG(e.progress) as avg_progress,
    SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completions,
    ROUND(SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(e.enrollment_id), 2) as completion_rate
FROM Courses c
LEFT JOIN Enrollments e ON c.course_id = e.course_id
GROUP BY c.course_id
HAVING total_enrollments > 0
ORDER BY total_enrollments DESC;

-- ============================================================================
-- END OF SCRIPT
-- ============================================================================
