-- =====================================================
-- CRITICAL FIX: Add role column to users table
-- =====================================================
-- Issue: Code expects users.role but canonical schema doesn't have it
-- This adds the role column to align code with database
-- =====================================================

-- Add role column to users table
ALTER TABLE `users`
ADD COLUMN `role` ENUM('student','instructor','admin','super_admin') DEFAULT 'student'
AFTER `status`;

-- Populate role column from user_roles table (if exists)
UPDATE users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
SET u.role = CASE
    WHEN r.role_name = 'Super Admin' THEN 'super_admin'
    WHEN r.role_name = 'Admin' THEN 'admin'
    WHEN r.role_name = 'Instructor' THEN 'instructor'
    WHEN r.role_name = 'Student' THEN 'student'
    ELSE 'student'
END
WHERE ur.user_id IS NOT NULL;

-- Add index for performance
CREATE INDEX idx_users_role ON users(role);

-- Verify the fix
SELECT role, COUNT(*) as count FROM users GROUP BY role;
