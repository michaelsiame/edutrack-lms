-- Migration: 003_assign_existing_users_roles
-- Description: Assign roles to existing users who don't have roles yet
-- Date: 2025-11-21

-- First, ensure roles exist (from 002_seed_data.sql)
-- Then assign roles to existing users based on common patterns

-- Assign admin role to users with 'admin' in their email
INSERT INTO User_Roles (user_id, role_id)
SELECT u.user_id, r.role_id
FROM Users u
CROSS JOIN Roles r
WHERE r.role_name IN ('Admin', 'Super Admin')
  AND u.email LIKE '%admin%'
  AND NOT EXISTS (
      SELECT 1 FROM User_Roles ur WHERE ur.user_id = u.user_id
  )
LIMIT 1;

-- Assign instructor role to users with 'instructor' or 'teacher' in their email
INSERT INTO User_Roles (user_id, role_id)
SELECT u.user_id, r.role_id
FROM Users u
CROSS JOIN Roles r
WHERE r.role_name = 'Instructor'
  AND (u.email LIKE '%instructor%' OR u.email LIKE '%teacher%')
  AND NOT EXISTS (
      SELECT 1 FROM User_Roles ur WHERE ur.user_id = u.user_id
  );

-- Assign student role to all remaining users without roles
INSERT INTO User_Roles (user_id, role_id)
SELECT u.user_id, r.role_id
FROM Users u
CROSS JOIN Roles r
WHERE r.role_name = 'Student'
  AND NOT EXISTS (
      SELECT 1 FROM User_Roles ur WHERE ur.user_id = u.user_id
  );
