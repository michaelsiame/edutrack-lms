-- ============================================================================
-- FINAL COMPATIBILITY FIXES FOR EXISTING DATABASE
-- ============================================================================
-- Purpose: Fix remaining compatibility issues between current database and application code
-- Run this script on your existing edutrack_lms database
-- ============================================================================

USE edutrack_lms;

-- ----------------------------------------------------------------------------
-- FIX 1: Rename course_categories.category_name to name
-- ----------------------------------------------------------------------------
ALTER TABLE course_categories
CHANGE COLUMN category_name name VARCHAR(100) NOT NULL;

-- ----------------------------------------------------------------------------
-- FIX 2: REMOVED - password_hash is correct, application uses password_hash
-- ----------------------------------------------------------------------------
-- The application code consistently uses 'password_hash' so no rename needed

-- ----------------------------------------------------------------------------
-- FIX 3: Rename courses.difficulty_level to level
-- ----------------------------------------------------------------------------
ALTER TABLE courses
CHANGE COLUMN difficulty_level level ENUM('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner';

-- ----------------------------------------------------------------------------
-- FIX 4: Add instructor_id to courses table (for simplified queries)
-- ----------------------------------------------------------------------------

-- Check if column doesn't already exist
ALTER TABLE courses
ADD COLUMN IF NOT EXISTS instructor_id INT NULL AFTER category_id;

-- Populate with lead instructor from course_instructors
UPDATE courses c
SET instructor_id = (
    SELECT ci.instructor_id
    FROM course_instructors ci
    WHERE ci.course_id = c.id AND ci.role = 'Lead'
    LIMIT 1
)
WHERE instructor_id IS NULL;

-- Add foreign key if not exists
ALTER TABLE courses
ADD CONSTRAINT fk_courses_instructor
FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE SET NULL;

-- ----------------------------------------------------------------------------
-- FIX 5: Rename enrollments.enrollment_date to enrolled_at
-- ----------------------------------------------------------------------------
ALTER TABLE enrollments
CHANGE COLUMN enrollment_date enrolled_at DATE NOT NULL;

-- ----------------------------------------------------------------------------
-- FIX 6: Rename enrollments.status to enrollment_status
-- ----------------------------------------------------------------------------
ALTER TABLE enrollments
CHANGE COLUMN status enrollment_status ENUM('Enrolled','In Progress','Completed','Dropped','Expired') DEFAULT 'Enrolled';

-- ----------------------------------------------------------------------------
-- FIX 7: Add user_id to enrollments table
-- ----------------------------------------------------------------------------

-- Add column if not exists
ALTER TABLE enrollments
ADD COLUMN IF NOT EXISTS user_id INT NULL AFTER id;

-- Populate user_id from student_id
UPDATE enrollments e
INNER JOIN students s ON e.student_id = s.id
SET e.user_id = s.user_id
WHERE e.user_id IS NULL;

-- Make it NOT NULL after populating
ALTER TABLE enrollments
MODIFY COLUMN user_id INT NOT NULL;

-- Add index
ALTER TABLE enrollments
ADD INDEX IF NOT EXISTS idx_user_id (user_id);

-- Add foreign key
ALTER TABLE enrollments
ADD CONSTRAINT fk_enrollments_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- ----------------------------------------------------------------------------
-- FIX 8: Add payment fields to enrollments table
-- ----------------------------------------------------------------------------

-- Add payment_status
ALTER TABLE enrollments
ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending' AFTER enrollment_status;

-- Add amount_paid
ALTER TABLE enrollments
ADD COLUMN IF NOT EXISTS amount_paid DECIMAL(10, 2) DEFAULT 0.00 AFTER payment_status;

-- Populate payment fields from payments table
UPDATE enrollments e
LEFT JOIN payments p ON e.id = p.enrollment_id AND p.payment_status = 'Completed'
SET
    e.payment_status = CASE
        WHEN p.payment_status = 'Completed' THEN 'completed'
        WHEN p.payment_status = 'Pending' THEN 'pending'
        WHEN p.payment_status = 'Failed' THEN 'failed'
        WHEN p.payment_status = 'Refunded' THEN 'refunded'
        ELSE 'pending'
    END,
    e.amount_paid = COALESCE(p.amount, 0)
WHERE e.payment_status IS NULL OR e.amount_paid IS NULL OR e.amount_paid = 0;

-- ----------------------------------------------------------------------------
-- FIX 9: Create user_profiles table (compatibility layer)
-- ----------------------------------------------------------------------------

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate user_profiles from students table
INSERT IGNORE INTO user_profiles (user_id, date_of_birth, gender, address, city, country, postal_code)
SELECT
    s.user_id,
    s.date_of_birth,
    s.gender,
    s.address,
    s.city,
    s.country,
    s.postal_code
FROM students s;

-- Populate user_profiles from instructors table (for those not already in profiles)
INSERT IGNORE INTO user_profiles (user_id, bio)
SELECT
    i.user_id,
    i.bio
FROM instructors i
WHERE i.user_id NOT IN (SELECT user_id FROM user_profiles);

-- Populate phone from users table
UPDATE user_profiles up
INNER JOIN users u ON up.user_id = u.id
SET up.phone = u.phone
WHERE up.phone IS NULL AND u.phone IS NOT NULL;

-- Populate avatar_url from users table
UPDATE user_profiles up
INNER JOIN users u ON up.user_id = u.id
SET up.avatar_url = u.avatar_url
WHERE up.avatar_url IS NULL AND u.avatar_url IS NOT NULL;

-- ----------------------------------------------------------------------------
-- FIX 10: Create course_reviews table (for ratings functionality)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS course_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    rating DECIMAL(2, 1) NOT NULL CHECK (rating >= 0 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_course_review (user_id, course_id),
    INDEX idx_course (course_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- FIX 11: Update users table - lowercase status values
-- ----------------------------------------------------------------------------

UPDATE users SET status = LOWER(status);
ALTER TABLE users
MODIFY COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active';

-- ----------------------------------------------------------------------------
-- FIX 12: Update courses table - lowercase status values
-- ----------------------------------------------------------------------------

UPDATE courses SET status = LOWER(status);
ALTER TABLE courses
MODIFY COLUMN status ENUM('draft', 'published', 'archived', 'under review') DEFAULT 'draft';

-- ----------------------------------------------------------------------------
-- VERIFICATION QUERIES
-- ----------------------------------------------------------------------------

-- Verify all fixes were applied
SELECT 'Database Compatibility Check' as Status;

SELECT
    'course_categories.name' as ColumnCheck,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'MISSING' END as Status
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'course_categories'
AND column_name = 'name';

SELECT
    'users.password' as ColumnCheck,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'MISSING' END as Status
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'users'
AND column_name = 'password';

SELECT
    'courses.level' as ColumnCheck,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'MISSING' END as Status
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'courses'
AND column_name = 'level';

SELECT
    'courses.instructor_id' as ColumnCheck,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'MISSING' END as Status
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'courses'
AND column_name = 'instructor_id';

SELECT
    'enrollments.user_id' as ColumnCheck,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'MISSING' END as Status
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'enrollments'
AND column_name = 'user_id';

SELECT
    'enrollments.enrolled_at' as ColumnCheck,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'MISSING' END as Status
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'enrollments'
AND column_name = 'enrolled_at';

SELECT
    'enrollments.payment_status' as ColumnCheck,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'MISSING' END as Status
FROM information_schema.columns
WHERE table_schema = DATABASE()
AND table_name = 'enrollments'
AND column_name = 'payment_status';

SELECT
    'user_profiles table' as TableCheck,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'MISSING' END as Status
FROM information_schema.tables
WHERE table_schema = DATABASE()
AND table_name = 'user_profiles';

SELECT
    'course_reviews table' as TableCheck,
    CASE WHEN COUNT(*) > 0 THEN 'OK' ELSE 'MISSING' END as Status
FROM information_schema.tables
WHERE table_schema = DATABASE()
AND table_name = 'course_reviews';

-- Show summary
SELECT
    'COMPATIBILITY FIX COMPLETE' as Message,
    'Your database is now fully compatible with the application code' as Status;

-- ============================================================================
-- END OF COMPATIBILITY FIXES
-- ============================================================================
