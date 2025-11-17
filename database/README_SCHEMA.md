# EduTrack LMS - Course Management Database Schema

## Overview
This database schema is designed to support a comprehensive course management system for the EduTrack Learning Management System. It includes complete sample data for testing all dashboard features (Admin, Instructor, and Student dashboards).

## Database Structure

### Tables

1. **Course_Categories** - Groups courses into logical categories
   - 6 categories covering all professional fields
   - Primary Key: `category_id`

2. **Instructors** - Stores instructor information
   - 6 instructors with diverse specializations
   - Primary Key: `instructor_id`
   - Unique: `email`

3. **Courses** - Detailed course information
   - 20 courses across all categories
   - Varied difficulty levels (Beginner, Intermediate, Advanced)
   - Different price points ($150 - $600)
   - Primary Key: `course_id`
   - Foreign Key: `category_id` → Course_Categories

4. **Students** - Student/learner information
   - 12 students with complete profiles
   - Primary Key: `student_id`
   - Unique: `email`

5. **Course_Instructors** - Many-to-many relationship between courses and instructors
   - Supports multiple instructors per course
   - Role-based assignments (Lead, Assistant, Guest)
   - Primary Key: `course_instructor_id`
   - Foreign Keys: `course_id` → Courses, `instructor_id` → Instructors

6. **Enrollments** - Tracks student course enrollments
   - 30 enrollment records with varied progress levels (0% - 100%)
   - Status tracking (Enrolled, In Progress, Completed, Dropped)
   - Grade recording for completed courses
   - Primary Key: `enrollment_id`
   - Foreign Keys: `student_id` → Students, `course_id` → Courses

## Data Distribution

### Course Categories (6)
1. Core ICT & Digital Skills - 4 courses
2. Programming & Software Development - 5 courses
3. Data, Security & Networks - 3 courses
4. Emerging Technologies - 2 courses
5. Digital Media & Design - 3 courses
6. Business & Management - 3 courses

### Difficulty Levels
- **Beginner**: 8 courses (40%)
- **Intermediate**: 8 courses (40%)
- **Advanced**: 4 courses (20%)

### Price Range
- Lowest: $150 (Digital Literacy)
- Highest: $600 (AI & Machine Learning)
- Average: ~$370

### Enrollment Status Distribution
- **Completed**: 7 enrollments (with grades)
- **In Progress**: 20 enrollments (various progress levels)
- **Enrolled**: 3 enrollments (just started)

## Installation Instructions

### Prerequisites
- MySQL 5.7+ or MariaDB 10.2+
- Database user with CREATE, INSERT, and SELECT privileges

### Installation Steps

1. **Create Database**:
   ```bash
   mysql -u root -p -e "CREATE DATABASE edutrack_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

2. **Execute Schema Script**:
   ```bash
   mysql -u root -p edutrack_lms < database/course_management_schema.sql
   ```

3. **Verify Installation**:
   ```bash
   mysql -u root -p edutrack_lms -e "SHOW TABLES;"
   ```

### Alternative: Using MySQL Workbench
1. Open MySQL Workbench
2. Connect to your MySQL server
3. Create new database: `CREATE DATABASE edutrack_lms;`
4. Open the SQL file: `database/course_management_schema.sql`
5. Execute the script (Lightning bolt icon or Ctrl+Shift+Enter)

## Testing the Database

The script includes verification queries at the end that will automatically run:

1. **Category Summary** - Shows all categories with course counts and average prices
2. **Course-Instructor Mapping** - Lists all courses with their assigned instructors
3. **Student Statistics** - Shows enrollment counts and progress for each student
4. **Course Popularity** - Displays enrollment numbers and completion rates

## Sample Queries for Dashboard Testing

### Admin Dashboard Queries

```sql
-- Total revenue from all enrollments
SELECT SUM(c.price) as total_revenue
FROM Enrollments e
JOIN Courses c ON e.course_id = c.course_id;

-- Active students count
SELECT COUNT(DISTINCT student_id) as active_students
FROM Enrollments
WHERE status IN ('Enrolled', 'In Progress');

-- Course completion rate
SELECT
    ROUND(SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as completion_rate
FROM Enrollments;
```

### Instructor Dashboard Queries

```sql
-- Courses taught by specific instructor (e.g., instructor_id = 2)
SELECT c.title, COUNT(e.enrollment_id) as enrolled_students
FROM Course_Instructors ci
JOIN Courses c ON ci.course_id = c.course_id
LEFT JOIN Enrollments e ON c.course_id = e.course_id
WHERE ci.instructor_id = 2
GROUP BY c.course_id;

-- Student progress in instructor's courses
SELECT
    s.first_name,
    s.last_name,
    c.title,
    e.progress,
    e.status
FROM Enrollments e
JOIN Students s ON e.student_id = s.student_id
JOIN Courses c ON e.course_id = c.course_id
JOIN Course_Instructors ci ON c.course_id = ci.course_id
WHERE ci.instructor_id = 2
ORDER BY c.title, e.progress DESC;
```

### Student Dashboard Queries

```sql
-- Student's enrolled courses (e.g., student_id = 1)
SELECT
    c.title,
    c.difficulty_level,
    e.enrollment_date,
    e.progress,
    e.status,
    CONCAT(i.first_name, ' ', i.last_name) as instructor
FROM Enrollments e
JOIN Courses c ON e.course_id = c.course_id
JOIN Course_Instructors ci ON c.course_id = ci.course_id AND ci.role = 'Lead'
JOIN Instructors i ON ci.instructor_id = i.instructor_id
WHERE e.student_id = 1;

-- Available courses not yet enrolled
SELECT
    c.title,
    c.description,
    c.price,
    c.difficulty_level,
    cc.category_name
FROM Courses c
JOIN Course_Categories cc ON c.category_id = cc.category_id
WHERE c.course_id NOT IN (
    SELECT course_id FROM Enrollments WHERE student_id = 1
)
ORDER BY cc.category_name, c.title;
```

## Data Characteristics for Testing

### Realistic Testing Scenarios

1. **Students with varying engagement**:
   - High performers (100% completion, excellent grades)
   - Active learners (multiple courses in progress)
   - New students (just enrolled, minimal progress)

2. **Course popularity**:
   - Popular courses (multiple enrollments)
   - New courses (few or no enrollments)
   - Courses at different stages (ongoing, completed, upcoming)

3. **Instructor workload**:
   - Primary instructors (lead role on multiple courses)
   - Assistant instructors (supporting roles)
   - Varied specializations

4. **Revenue distribution**:
   - Different price points
   - Various enrollment numbers
   - Completed vs. in-progress courses

## Schema Features

### Data Integrity
- Foreign key constraints ensure referential integrity
- Unique constraints prevent duplicate records
- Check constraints validate data ranges (progress 0-100, grades 0-100)
- Cascading deletes maintain consistency

### Timestamps
- All tables include `created_at` and `updated_at` timestamps
- Automatic timestamp updates on record modifications

### Flexibility
- Enum types for status fields allow easy filtering
- Nullable fields support optional information
- Many-to-many relationships support complex scenarios

### Performance
- Primary keys on all tables
- Indexed foreign keys for efficient joins
- InnoDB engine for transaction support

## Next Steps

1. **Extend the Schema**:
   - Add modules/lessons tables for course content
   - Add assignments and assessments tables
   - Add certificates and achievements tables
   - Add payment and transaction tracking

2. **Create Views**:
   - Dashboard summary views
   - Reporting views for analytics
   - Student progress tracking views

3. **Add Stored Procedures**:
   - Enrollment processing
   - Grade calculation
   - Progress updates
   - Certificate generation

4. **Implement Triggers**:
   - Auto-update completion dates
   - Calculate course statistics
   - Send notifications on status changes

## Support

For issues or questions about the database schema, please refer to the main EduTrack LMS documentation or contact the development team.

---

**Version**: 1.0
**Created**: 2025-11-17
**Database**: MySQL 5.7+ Compatible
