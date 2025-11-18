# Database Schema Implementation Guide

## Overview

This guide explains how to implement the complete EduTrack LMS database schema and ensure compatibility with your existing application code.

---

## Quick Start (For Existing Database)

If you already have a database with some tables, use this approach:

### Step 1: Apply Compatibility Fixes

Run the compatibility fix script on your existing database:

```bash
mysql -u your_username -p edutrack_lms < database/final_compatibility_fix.sql
```

**What this does:**
- Renames columns to match application expectations (`category_name` → `name`, etc.)
- Adds missing columns (`instructor_id` in courses, `user_id` in enrollments)
- Creates missing tables (`user_profiles`, `course_reviews`)
- Adds payment tracking fields to enrollments
- Converts status values to lowercase for consistency

**Safety Features:**
- Uses `IF NOT EXISTS` to prevent errors if tables already exist
- Uses `IGNORE` in INSERT statements to skip duplicate data
- Includes verification queries at the end to confirm all fixes applied

---

## Fresh Installation

If you're starting from scratch:

### Step 1: Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE IF NOT EXISTS edutrack_lms
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

### Step 2: Run Complete Schema

```bash
mysql -u your_username -p edutrack_lms < database/complete_lms_schema.sql
```

### Step 3: Apply Compatibility Layer

```bash
mysql -u your_username -p edutrack_lms < database/schema_compatibility_fix.sql
```

---

## Database Files Explained

### 1. `complete_lms_schema.sql` (88 KB, 1,515 lines)

**Purpose:** Comprehensive LMS database with all features and sample data

**Contains:**
- 35 tables covering all LMS functionality
- 500+ sample records for testing
- Role-based access control
- Complete course structure with modules, lessons, resources
- Assessment system (assignments, quizzes)
- Communication features (announcements, discussions, messages)
- Payment processing and transaction logging
- Certificates and badges system
- Analytics and activity tracking

**Sample Data Includes:**
- 3 user roles with granular permissions
- 6 course categories
- 20 courses across different subjects
- 6 instructors with specializations
- 12 students with varied profiles
- 30 course enrollments
- 40 modules with lesson content
- 50+ lessons with progress tracking
- 15 assignments and submissions
- 10 quizzes with 50 questions
- Payment records and transactions

**When to use:** Fresh installation or complete rebuild

---

### 2. `final_compatibility_fix.sql` (10 KB, 282 lines)

**Purpose:** Targeted fixes for existing databases to ensure application compatibility

**Fixes Applied:**

#### Fix #1: Course Categories
```sql
-- Application expects: course_categories.name
-- Schema has: course_categories.category_name
ALTER TABLE course_categories
CHANGE COLUMN category_name name VARCHAR(100) NOT NULL;
```

#### Fix #2: User Authentication
```sql
-- Application expects: users.password
-- Schema has: users.password_hash
ALTER TABLE users
CHANGE COLUMN password_hash password VARCHAR(255) NOT NULL;
```

#### Fix #3: Course Difficulty
```sql
-- Application expects: courses.level
-- Schema has: courses.difficulty_level
ALTER TABLE courses
CHANGE COLUMN difficulty_level level ENUM('Beginner','Intermediate','Advanced');
```

#### Fix #4: Course-Instructor Relationship
```sql
-- Application expects: courses.instructor_id (direct FK)
-- Schema has: course_instructors junction table
ALTER TABLE courses ADD COLUMN instructor_id INT NULL;

-- Populate with lead instructor
UPDATE courses c
SET instructor_id = (
    SELECT ci.instructor_id
    FROM course_instructors ci
    WHERE ci.course_id = c.id AND ci.role = 'Lead'
    LIMIT 1
);
```

#### Fix #5: Enrollment Dates
```sql
-- Application expects: enrollments.enrolled_at
-- Schema has: enrollments.enrollment_date
ALTER TABLE enrollments
CHANGE COLUMN enrollment_date enrolled_at DATE NOT NULL;
```

#### Fix #6: Enrollment Status
```sql
-- Application expects: enrollments.enrollment_status
-- Schema has: enrollments.status
ALTER TABLE enrollments
CHANGE COLUMN status enrollment_status ENUM(...);
```

#### Fix #7: User-Enrollment Relationship
```sql
-- Application expects: enrollments.user_id
-- Schema has: enrollments.student_id
ALTER TABLE enrollments ADD COLUMN user_id INT NOT NULL;

-- Populate from students table
UPDATE enrollments e
INNER JOIN students s ON e.student_id = s.id
SET e.user_id = s.user_id;
```

#### Fix #8: Payment Tracking
```sql
-- Application expects payment fields in enrollments table
-- Schema has separate payments table
ALTER TABLE enrollments
ADD COLUMN payment_status ENUM('pending', 'completed', 'failed', 'refunded');

ALTER TABLE enrollments
ADD COLUMN amount_paid DECIMAL(10, 2) DEFAULT 0.00;
```

#### Fix #9: User Profiles
```sql
-- Application expects: user_profiles table
-- Schema has: students and instructors tables
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say'),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    avatar_url VARCHAR(255),
    ...
);
```

#### Fix #10: Course Reviews
```sql
-- Application expects: course_reviews table
-- Schema doesn't have it
CREATE TABLE IF NOT EXISTS course_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    rating DECIMAL(2, 1) NOT NULL CHECK (rating >= 0 AND rating <= 5),
    review TEXT,
    ...
);
```

#### Fix #11: Status Value Normalization
```sql
-- Application uses lowercase: 'draft', 'published'
-- Schema uses capitalized: 'Draft', 'Published'
UPDATE courses SET status = LOWER(status);
ALTER TABLE courses
MODIFY COLUMN status ENUM('draft', 'published', 'archived', 'under review');
```

**When to use:** Existing database that needs compatibility with application code

---

### 3. `schema_compatibility_fix.sql` (16 KB, 408 lines)

**Purpose:** Transform complete schema (PascalCase) to application-compatible format (lowercase)

**Operations:**
- Renames all tables from PascalCase to lowercase (e.g., `Users` → `users`)
- Changes all primary keys to simple `id` (e.g., `user_id` → `id`)
- Applies all column renames and additions
- Creates compatibility tables

**When to use:** After running `complete_lms_schema.sql` on a fresh database

---

### 4. `COMPATIBILITY_ANALYSIS.md` (12 KB)

**Purpose:** Detailed analysis of compatibility issues between schema and application

**Contents:**
- Critical issues identified (table naming, primary keys, missing tables)
- Structural differences explained
- Impact on specific files (Course.php, User.php, Enrollment.php)
- Compatibility matrix
- Recommended solutions
- Migration strategy

**When to use:** Understanding why compatibility fixes are needed

---

## Verification After Installation

### Check All Fixes Applied

Run these queries to verify compatibility:

```sql
-- Check course_categories.name exists
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'edutrack_lms'
  AND TABLE_NAME = 'course_categories'
  AND COLUMN_NAME = 'name';

-- Check users.password exists
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'edutrack_lms'
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME = 'password';

-- Check courses.level exists
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'edutrack_lms'
  AND TABLE_NAME = 'courses'
  AND COLUMN_NAME = 'level';

-- Check courses.instructor_id exists
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'edutrack_lms'
  AND TABLE_NAME = 'courses'
  AND COLUMN_NAME = 'instructor_id';

-- Check enrollments.user_id exists
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'edutrack_lms'
  AND TABLE_NAME = 'enrollments'
  AND COLUMN_NAME = 'user_id';

-- Check user_profiles table exists
SELECT TABLE_NAME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'edutrack_lms'
  AND TABLE_NAME = 'user_profiles';

-- Check course_reviews table exists
SELECT TABLE_NAME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'edutrack_lms'
  AND TABLE_NAME = 'course_reviews';
```

All queries should return results. If any return empty, re-run the compatibility fix script.

---

## Testing the Application

### 1. Test Database Connection

```bash
php -r "
require 'config/database.php';
require 'src/includes/database.php';
if (Database::testConnection()) {
    echo 'Database connection successful!\n';
} else {
    echo 'Database connection failed!\n';
}
"
```

### 2. Test User Authentication

Try logging in with sample users:
- **Admin:** admin@edutrack.com / admin123
- **Instructor:** sarah.johnson@edutrack.com / instructor123
- **Student:** john.doe@student.com / student123

### 3. Test Course Queries

```php
<?php
require 'config/database.php';
require 'src/includes/database.php';
require 'src/classes/Course.php';

$course = new Course(1);
echo "Course: " . $course->title . "\n";
echo "Instructor: " . $course->instructor_name . "\n";
echo "Category: " . $course->category_name . "\n";
echo "Level: " . $course->level . "\n";
```

### 4. Test Enrollments

```php
<?php
require 'config/database.php';
require 'src/includes/database.php';

$db = Database::getInstance();
$enrollments = $db->fetchAll("
    SELECT e.*, c.title, u.first_name, u.last_name
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    LIMIT 5
");

foreach ($enrollments as $enrollment) {
    echo $enrollment['first_name'] . " enrolled in " . $enrollment['title'] . "\n";
}
```

---

## Common Issues and Solutions

### Issue 1: Foreign Key Constraints Fail

**Error:** `Cannot add or update a child row: a foreign key constraint fails`

**Solution:** Ensure you're running scripts in the correct order:
1. Complete schema first (creates all tables)
2. Compatibility fix second (modifies structure)

### Issue 2: Column 'X' doesn't exist

**Error:** `Unknown column 'category_name' in 'field list'`

**Solution:** Run the compatibility fix script. This means the schema has the new structure but the fix wasn't applied.

### Issue 3: Table names case-sensitive

**Error:** `Table 'edutrack_lms.Users' doesn't exist`

**Solution:**
- On Linux/Unix: MySQL is case-sensitive. Use lowercase table names.
- Run compatibility fix to rename all tables to lowercase.

### Issue 4: Duplicate column errors

**Error:** `Duplicate column name 'instructor_id'`

**Solution:** The fix has already been applied. Safe to ignore or use `IF NOT EXISTS` clauses.

---

## Database Schema Architecture

### Normalized Design Principles

1. **Users Table:** Central authentication and basic info
2. **Students/Instructors Tables:** Role-specific extended information
3. **Courses Table:** Core course data with category and instructor FKs
4. **Course_Instructors:** Junction table for multi-instructor support
5. **Enrollments:** Links students to courses with progress tracking
6. **Modules/Lessons:** Hierarchical content structure
7. **Assessments:** Assignments and quizzes separated by type
8. **User_Profiles:** Compatibility layer for unified profile access

### Key Relationships

```
Users (1) ─────→ (N) Students
Users (1) ─────→ (N) Instructors
Users (1) ─────→ (N) Enrollments
Users (1) ─────→ (N) Course_Reviews

Courses (1) ───→ (N) Enrollments
Courses (1) ───→ (N) Modules
Courses (1) ───→ (N) Course_Instructors
Courses (N) ───→ (1) Course_Categories
Courses (N) ───→ (1) Instructors [via instructor_id - compatibility]

Modules (1) ───→ (N) Lessons
Lessons (1) ───→ (N) Lesson_Progress
Lessons (1) ───→ (N) Assignments
Lessons (1) ───→ (N) Quizzes

Enrollments (1) → (N) Payments
Enrollments (1) → (1) Certificates [on completion]
```

---

## Performance Optimization

### Indexes Created

All foreign keys have indexes automatically. Additional indexes:

```sql
-- Frequently queried columns
CREATE INDEX idx_courses_status ON courses(status);
CREATE INDEX idx_courses_level ON courses(level);
CREATE INDEX idx_enrollments_status ON enrollments(enrollment_status);
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_course_slug ON courses(slug);
```

### Query Optimization Tips

1. **Use JOINs efficiently:**
```sql
-- Good: Retrieve only needed columns
SELECT c.id, c.title, cat.name as category
FROM courses c
JOIN course_categories cat ON c.category_id = cat.id;

-- Bad: SELECT * joins
SELECT * FROM courses c JOIN course_categories cat ...
```

2. **Leverage instructor_id for simple queries:**
```sql
-- Fast: Direct FK lookup
SELECT * FROM courses WHERE instructor_id = 1;

-- Slower: Junction table join
SELECT c.* FROM courses c
JOIN course_instructors ci ON c.id = ci.course_id
WHERE ci.instructor_id = 1;
```

3. **Index lesson progress queries:**
```sql
-- Already indexed on (student_id, lesson_id)
SELECT * FROM lesson_progress
WHERE student_id = 1 AND lesson_id = 5;
```

---

## Data Migration (If Needed)

If you have existing data in a different schema:

### 1. Export Current Data

```bash
mysqldump -u root -p edutrack_lms > backup_$(date +%Y%m%d).sql
```

### 2. Create Mapping Script

Create a custom migration script to map old schema to new:

```sql
-- Example: Migrate old users to new structure
INSERT INTO users (email, password, first_name, last_name, role, created_at)
SELECT email, password_hash, fname, lname, user_role, registration_date
FROM old_users_table;

-- Create student records for users with role 'student'
INSERT INTO students (user_id, date_of_birth, gender)
SELECT u.id, old_u.dob, old_u.gender
FROM users u
JOIN old_users_table old_u ON u.email = old_u.email
WHERE u.role = 'student';
```

### 3. Verify Data Integrity

```sql
-- Check all users have corresponding student/instructor records
SELECT COUNT(*) FROM users WHERE role = 'student'
  AND id NOT IN (SELECT user_id FROM students);

-- Should return 0
```

---

## Maintenance

### Backup Schedule

```bash
# Daily backup
0 2 * * * mysqldump -u backup_user -p edutrack_lms | gzip > /backups/edutrack_$(date +\%Y\%m\%d).sql.gz

# Weekly full backup
0 3 * * 0 mysqldump -u backup_user -p --all-databases | gzip > /backups/full_$(date +\%Y\%m\%d).sql.gz
```

### Regular Maintenance

```sql
-- Analyze tables monthly
ANALYZE TABLE courses, enrollments, users, lessons, lesson_progress;

-- Optimize tables quarterly
OPTIMIZE TABLE courses, enrollments, users;

-- Check for orphaned records
SELECT e.* FROM enrollments e
LEFT JOIN users u ON e.user_id = u.id
WHERE u.id IS NULL;
```

---

## Support and Troubleshooting

### Debug Mode

Enable database logging in `src/includes/database.php`:

```php
// Check logs
tail -f storage/logs/database.log
```

### Query Profiling

```sql
SET profiling = 1;

-- Run your query
SELECT * FROM courses WHERE status = 'published';

-- Show profile
SHOW PROFILES;
SHOW PROFILE FOR QUERY 1;
```

### Contact

For issues or questions:
1. Check `COMPATIBILITY_ANALYSIS.md` for detailed explanations
2. Review `COMPLETE_SCHEMA_README.md` for table descriptions
3. Examine sample queries in the schema files

---

## Changelog

### Version 1.0 (2025-11-18)
- Initial complete LMS schema with 35 tables
- Sample data for testing (500+ records)
- Comprehensive compatibility layer
- Support for multi-instructor courses
- Payment tracking and transaction logging
- Role-based access control
- Certificate and badge system

### Fixes Applied
- Fixed column count mismatch in Modules INSERT (line 1030)
- Added missing Web Development lessons for FK constraints
- Renamed tables to lowercase for Linux compatibility
- Changed all PKs to simple 'id' format
- Added user_profiles and course_reviews tables
- Implemented dual-key support (student_id + user_id in enrollments)
- Added instructor_id to courses for simplified queries

---

## License

This database schema is part of the EduTrack LMS system.
Proprietary - All rights reserved.

---

**Document Version:** 1.0
**Last Updated:** 2025-11-18
**Status:** Production Ready
