# EduTrack LMS - Complete Database Schema Documentation

## Overview

This is the comprehensive database schema for the EduTrack Learning Management System (LMS). It includes all necessary tables and relationships to support a full-featured online learning platform with course management, user authentication, assessments, communication, payments, and analytics.

## Database Statistics

- **Total Tables**: 35
- **Sample Users**: 19 (1 admin, 6 instructors, 12 students)
- **Sample Courses**: 20 courses across 6 categories
- **Sample Enrollments**: 30 active enrollments
- **Database Size**: ~1,500 lines of SQL
- **Sample Data Records**: 500+ records across all tables

## Schema Architecture

### 1. User Authentication & Authorization (3 tables)

**Purpose**: Manage user accounts, roles, and permissions

#### Tables:
- **Users** - Central user authentication table
  - Stores: username, email, password_hash, profile info
  - Features: email verification, last login tracking
  - Sample: 19 users (admin, instructors, students)

- **Roles** - System roles definition
  - Roles: Super Admin, Admin, Instructor, Student, Content Creator
  - Features: JSON permissions for granular access control
  - Sample: 5 predefined roles

- **User_Roles** - Many-to-many user-role mapping
  - Supports multiple roles per user
  - Tracks who assigned roles and when

### 2. Course Management (6 tables)

**Purpose**: Core course structure and organization

#### Tables:
- **Course_Categories** (6 categories)
  - Core ICT & Digital Skills
  - Programming & Software Development
  - Data, Security & Networks
  - Emerging Technologies
  - Digital Media & Design
  - Business & Management
  - Features: hierarchical categories (parent_category_id), display ordering

- **Courses** (20 sample courses)
  - Full course metadata: title, description, pricing, dates
  - Features: difficulty levels, ratings, featured courses, enrollment counts
  - Status: Draft, Published, Archived, Under Review
  - Pricing: Regular price + optional discount price

- **Instructors** (6 sample instructors)
  - Links to Users table
  - Professional info: bio, specialization, experience, certifications
  - Metrics: rating, total students, total courses
  - Verification status for quality control

- **Students** (12 sample students)
  - Links to Users table
  - Demographics: DOB, gender, location
  - Tracking: total courses enrolled/completed, certificates earned

- **Course_Instructors** (Many-to-many)
  - Supports multiple instructors per course
  - Roles: Lead, Assistant, Guest, Mentor
  - Sample: 24 instructor-course assignments

- **Enrollments** (30 sample enrollments)
  - Student-course enrollment tracking
  - Progress tracking (0-100%)
  - Status: Enrolled, In Progress, Completed, Dropped, Expired
  - Grades, certificates, time tracking

### 3. Course Content Structure (4 tables)

**Purpose**: Organize learning materials within courses

#### Tables:
- **Modules** - Course sections/chapters
  - Organize lessons into logical groups
  - Display ordering, duration tracking
  - Unlock dates for scheduled content release
  - Sample: 10 modules for Python and Web Development courses

- **Lessons** - Individual learning units
  - Types: Video, Reading, Quiz, Assignment, Live Session, Download
  - Duration tracking, preview availability
  - Points system for gamification
  - Sample: 16 lessons across different modules

- **Lesson_Resources** - Downloadable materials
  - Attached to lessons
  - Types: PDF, Document, Video, Audio, etc.
  - Download tracking

- **Lesson_Progress** - Student lesson completion tracking
  - Status: Not Started, In Progress, Completed
  - Time spent tracking
  - Completion timestamps
  - Sample: 9 progress records showing lesson completion

### 4. Assessments & Grading (10 tables)

**Purpose**: Assignments, quizzes, and grading system

#### Tables:
- **Assignments** - Course assignments
  - Full instructions, due dates, point values
  - Late submission policies
  - File upload restrictions
  - Sample: 4 assignments

- **Assignment_Submissions** - Student submissions
  - Text and file submissions
  - Grading workflow
  - Instructor feedback
  - Late submission tracking
  - Sample: 3 graded submissions

- **Quizzes** - Quiz/exam definitions
  - Types: Practice, Graded, Midterm, Final Exam
  - Time limits, attempt limits
  - Question randomization
  - Availability windows
  - Sample: 4 quizzes

- **Questions** - Question bank
  - Types: Multiple Choice, True/False, Short Answer, Essay, Fill in Blank
  - Points, explanations
  - Sample: 6 questions

- **Question_Options** - Multiple choice options
  - Correct answer marking
  - Display ordering
  - Sample: 15 options for 5 questions

- **Quiz_Questions** - Link questions to quizzes
  - Display ordering
  - Point overrides

- **Quiz_Attempts** - Student quiz attempts
  - Attempt tracking, scoring
  - Time spent, IP address logging
  - Status tracking
  - Sample: 4 quiz attempts

- **Quiz_Answers** - Individual question answers
  - Answer recording and grading
  - Points earned per question

### 5. Communication & Collaboration (5 tables)

**Purpose**: Course communication and user interaction

#### Tables:
- **Announcements** - Course and system announcements
  - Types: Course, System, Urgent, General
  - Priority levels
  - Expiration dates
  - Sample: 4 announcements

- **Discussions** - Course discussion forums
  - Pinned and locked threads
  - View and reply counters
  - Sample: 4 discussion topics

- **Discussion_Replies** - Forum replies
  - Nested replies (parent_reply_id)
  - Best answer marking
  - Instructor reply tracking
  - Sample: 7 replies

- **Messages** - Private messaging
  - User-to-user communication
  - Read tracking
  - Threaded conversations
  - Sample: 4 messages

- **Notifications** - In-app notifications
  - Types: Info, Success, Warning, Error, Assignment, Grade, Announcement
  - Read status tracking
  - Action URLs
  - Sample: 7 notifications

### 6. Certificates & Achievements (3 tables)

**Purpose**: Recognition and gamification

#### Tables:
- **Certificates** - Course completion certificates
  - Unique certificate numbers
  - Verification codes
  - Expiry dates
  - Sample: 6 certificates issued

- **Badges** - Achievement badges
  - Types: Course Completion, Perfect Score, Early Bird, Participation, etc.
  - Points system
  - Achievement criteria
  - Sample: 6 badge types

- **Student_Achievements** - Badges earned by students
  - Links students to badges
  - Earned dates
  - Sample: 7 achievements earned

### 7. Payment & Billing (3 tables)

**Purpose**: Financial transactions and payment processing

#### Tables:
- **Payment_Methods** - Available payment options
  - Credit Card, Mobile Money, Bank Transfer, PayPal, Cash
  - Active status tracking
  - Sample: 5 payment methods

- **Payments** - Payment records
  - Amount, currency, status
  - Transaction IDs
  - Payment method tracking
  - Status: Pending, Completed, Failed, Refunded, Cancelled
  - Sample: 16 payments ($5,217 total revenue)

- **Transactions** - Detailed transaction log
  - Payment history and gateway responses
  - Types: Payment, Refund, Chargeback, Fee
  - Sample: 10 transaction records

### 8. Analytics & Tracking (1 table)

**Purpose**: User activity monitoring and analytics

#### Tables:
- **Activity_Logs** - Comprehensive activity tracking
  - All user actions: login, lesson views, submissions, etc.
  - IP address and user agent logging
  - Entity tracking (what was accessed/modified)
  - Sample: 10 recent activities

### 9. System Configuration (3 tables)

**Purpose**: System settings and email management

#### Tables:
- **System_Settings** - Platform configuration
  - Key-value settings storage
  - Type-safe (String, Number, Boolean, JSON)
  - Editable flags
  - Sample: 10 configuration settings

- **Email_Templates** - Email template management
  - Types: Welcome, Enrollment, Certificate, Payment, Reminder
  - HTML templates with variable placeholders
  - Sample: 5 email templates

- **Notifications** - (Covered in Communication section)

## Database Relationships

### Key Relationships:

```
Users
├── User_Roles → Roles
├── Instructors → Courses (via Course_Instructors)
├── Students → Enrollments → Courses
├── Activity_Logs
├── Messages (sender/recipient)
├── Discussions, Discussion_Replies
└── Announcements

Courses
├── Course_Categories
├── Course_Instructors → Instructors
├── Enrollments → Students
├── Modules → Lessons → Lesson_Resources
├── Assignments → Assignment_Submissions
├── Quizzes → Questions (via Quiz_Questions)
├── Discussions
├── Announcements
└── Payments

Enrollments
├── Lesson_Progress → Lessons
├── Certificates
├── Quiz_Attempts → Quiz_Answers
└── Assignment_Submissions

Students
├── Student_Achievements → Badges
└── Payments
```

## Data Distribution

### Courses by Category:
- Core ICT & Digital Skills: 4 courses
- Programming & Software Development: 5 courses
- Data, Security & Networks: 3 courses
- Emerging Technologies: 2 courses
- Digital Media & Design: 3 courses
- Business & Management: 3 courses

### Difficulty Distribution:
- Beginner: 8 courses (40%)
- Intermediate: 8 courses (40%)
- Advanced: 4 courses (20%)

### Price Range:
- Minimum: $150 (Digital Literacy)
- Maximum: $600 (AI & Machine Learning)
- Average: ~$370

### Enrollment Distribution:
- Total Enrollments: 30
- Completed Courses: 6 (20%)
- In Progress: 21 (70%)
- Just Enrolled: 3 (10%)

### Revenue Statistics:
- Total Revenue: $5,217 USD
- Completed Payments: 15
- Pending Payments: 1
- Average Transaction: $348

## Installation Instructions

### 1. Prerequisites
- MySQL 5.7+ or MariaDB 10.2+
- Database user with full privileges
- At least 50MB free database space

### 2. Create Database

```bash
mysql -u root -p -e "CREATE DATABASE edutrack_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 3. Execute Schema

```bash
mysql -u root -p edutrack_lms < database/complete_lms_schema.sql
```

### 4. Verify Installation

```bash
mysql -u root -p edutrack_lms -e "
SELECT table_name, table_rows
FROM information_schema.tables
WHERE table_schema = 'edutrack_lms'
ORDER BY table_name;
"
```

### 5. Run Verification Queries

The schema includes comprehensive verification queries at the end that automatically run when the script executes. These queries provide:

- Course statistics by category
- Instructor performance metrics
- Student enrollment summaries
- Course popularity and revenue analysis
- Payment and revenue summaries
- Assignment and quiz statistics
- Discussion engagement metrics
- Certificate issuance summary
- Overall platform statistics

## Sample Credentials

### Admin User
- **Username**: admin
- **Email**: admin@edutrack.edu
- **Password**: (hashed with bcrypt, use your own in production)

### Sample Instructor
- **Username**: sarah.banda
- **Email**: sarah.banda@edutrack.edu
- **Role**: Instructor

### Sample Student
- **Username**: john.tembo
- **Email**: john.tembo@email.com
- **Role**: Student

**Note**: All passwords are hashed using bcrypt. In a production environment, implement proper password reset functionality.

## Key Features

### 1. Multi-Role System
- Supports multiple roles per user
- JSON-based permissions for flexible access control
- Role assignment tracking

### 2. Comprehensive Course Management
- Hierarchical course categorization
- Multi-instructor support
- Featured courses
- Course ratings and reviews
- Discount pricing

### 3. Rich Content Structure
- Modular course organization
- Multiple lesson types
- Scheduled content release (unlock dates)
- Downloadable resources
- Preview lessons for marketing

### 4. Robust Assessment System
- Multiple assignment types
- Flexible quiz configurations
- Question bank system
- Multiple quiz attempts
- Automatic and manual grading
- Late submission policies

### 5. Integrated Communication
- Course announcements
- Discussion forums
- Private messaging
- In-app notifications
- Email templates

### 6. Gamification
- Certificate issuance
- Achievement badges
- Points system
- Progress tracking

### 7. Payment Processing
- Multiple payment methods
- Transaction logging
- Revenue tracking
- Refund support

### 8. Analytics & Reporting
- Activity logging
- Progress tracking
- Performance metrics
- Revenue analytics

## Security Features

1. **Password Security**: Bcrypt hashing for all passwords
2. **Email Verification**: Email verification status tracking
3. **Session Management**: Session timeout configuration
4. **Activity Logging**: Comprehensive audit trail with IP tracking
5. **Role-Based Access**: Granular permissions system
6. **Foreign Key Constraints**: Data integrity enforcement
7. **Status Tracking**: User account status (Active, Inactive, Suspended)

## Performance Optimizations

1. **Indexes**: Strategic indexes on frequently queried columns
   - User email and username
   - Course status and category
   - Enrollment status
   - Payment status
   - Activity logs timestamps

2. **InnoDB Engine**: Transaction support and row-level locking

3. **Efficient Queries**: Optimized verification queries with proper JOINs

4. **Cascading Deletes**: Automatic cleanup of related records

## Extension Points

### Easy to Add:

1. **Course Reviews/Ratings**
   - `course_reviews` table with rating and review text

2. **Live Sessions/Webinars**
   - `live_sessions` table with scheduling and recording

3. **Course Prerequisites**
   - Already has `prerequisites` field in Courses table

4. **Student Groups/Cohorts**
   - `student_groups` table for cohort-based learning

5. **Learning Paths**
   - `learning_paths` table grouping related courses

6. **Coupons/Discounts**
   - `coupons` table with validation rules

7. **Attendance Tracking**
   - `attendance` table for live session tracking

8. **Grade Book**
   - Aggregate view of all student assessments

9. **Course Forums (Enhanced)**
   - Tags, search, voting on discussions

10. **Mobile App Support**
    - `device_tokens` table for push notifications

## Usage Examples

### Dashboard Queries

#### Admin Dashboard
```sql
-- Total revenue this month
SELECT SUM(amount) as monthly_revenue
FROM Payments
WHERE payment_status = 'Completed'
AND MONTH(payment_date) = MONTH(CURRENT_DATE)
AND YEAR(payment_date) = YEAR(CURRENT_DATE);

-- New enrollments this week
SELECT COUNT(*) as weekly_enrollments
FROM Enrollments
WHERE enrollment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY);

-- Top performing courses
SELECT c.title, COUNT(e.enrollment_id) as enrollments, AVG(e.progress) as avg_progress
FROM Courses c
JOIN Enrollments e ON c.course_id = e.course_id
GROUP BY c.course_id
ORDER BY enrollments DESC
LIMIT 5;
```

#### Instructor Dashboard
```sql
-- My courses and students (for instructor_id = 2)
SELECT
    c.title,
    COUNT(DISTINCT e.student_id) as total_students,
    AVG(e.progress) as avg_progress,
    SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completed
FROM Course_Instructors ci
JOIN Courses c ON ci.course_id = c.course_id
LEFT JOIN Enrollments e ON c.course_id = e.course_id
WHERE ci.instructor_id = 2 AND ci.role = 'Lead'
GROUP BY c.course_id;

-- Pending assignment submissions to grade
SELECT
    s.first_name,
    s.last_name,
    a.title,
    asub.submitted_at
FROM Assignment_Submissions asub
JOIN Assignments a ON asub.assignment_id = a.assignment_id
JOIN Students s ON asub.student_id = s.student_id
JOIN Course_Instructors ci ON a.course_id = ci.course_id
WHERE ci.instructor_id = 2
AND asub.status = 'Submitted'
ORDER BY asub.submitted_at ASC;
```

#### Student Dashboard
```sql
-- My courses and progress (for student_id = 1)
SELECT
    c.title,
    c.difficulty_level,
    e.progress,
    e.status,
    e.final_grade,
    CONCAT(i.first_name, ' ', i.last_name) as instructor
FROM Enrollments e
JOIN Courses c ON e.course_id = c.course_id
JOIN Course_Instructors ci ON c.course_id = ci.course_id AND ci.role = 'Lead'
JOIN Instructors inst ON ci.instructor_id = inst.instructor_id
JOIN Users i ON inst.user_id = i.user_id
WHERE e.student_id = 1
ORDER BY e.enrollment_date DESC;

-- Upcoming assignments
SELECT
    c.title as course_title,
    a.title as assignment_title,
    a.due_date,
    a.max_points,
    DATEDIFF(a.due_date, CURRENT_DATE) as days_remaining
FROM Enrollments e
JOIN Assignments a ON e.course_id = a.course_id
JOIN Courses c ON e.course_id = c.course_id
WHERE e.student_id = 1
AND a.due_date >= CURRENT_DATE
AND NOT EXISTS (
    SELECT 1 FROM Assignment_Submissions asub
    WHERE asub.assignment_id = a.assignment_id
    AND asub.student_id = e.student_id
)
ORDER BY a.due_date ASC;
```

## Maintenance

### Regular Tasks

1. **Backup Database** (Daily)
   ```bash
   mysqldump -u root -p edutrack_lms > edutrack_backup_$(date +%Y%m%d).sql
   ```

2. **Clean Old Activity Logs** (Monthly)
   ```sql
   DELETE FROM Activity_Logs
   WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
   ```

3. **Update Course Statistics** (Weekly)
   ```sql
   UPDATE Courses c
   SET enrollment_count = (
       SELECT COUNT(*) FROM Enrollments WHERE course_id = c.course_id
   );
   ```

4. **Archive Completed Courses** (Quarterly)
   ```sql
   UPDATE Courses
   SET status = 'Archived'
   WHERE end_date < DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)
   AND status = 'Published';
   ```

## Troubleshooting

### Common Issues

1. **Foreign Key Constraint Errors**
   - Ensure parent records exist before inserting child records
   - Check cascade delete settings

2. **Character Encoding Issues**
   - Database and tables must use utf8mb4
   - Connection should specify utf8mb4 charset

3. **Slow Queries**
   - Add indexes on frequently searched columns
   - Use EXPLAIN to analyze query performance
   - Consider partitioning large tables (Activity_Logs)

## Version History

- **v1.0.0** (2025-11-18) - Initial complete schema release
  - 35 tables
  - 500+ sample records
  - Comprehensive verification queries
  - Full documentation

## Contributing

When modifying the schema:

1. Always create migration scripts (don't modify this base schema)
2. Document all changes
3. Update sample data accordingly
4. Test foreign key constraints
5. Update this documentation

## License

This database schema is part of the EduTrack LMS project.

## Support

For questions or issues:
- Review this documentation
- Check the verification queries for examples
- Examine the sample data for usage patterns

---

**Generated**: 2025-11-18
**Database**: MySQL 5.7+ Compatible
**Total Size**: ~1,515 lines of SQL
**Sample Records**: 500+ records across 35 tables
