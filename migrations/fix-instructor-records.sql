-- Fix Instructor Records Migration
-- Ensures all users with instructor role have corresponding instructor records

-- First, ensure Michael Siame (user_id 6) has an instructor record
INSERT IGNORE INTO instructors (user_id, bio, specialization, is_verified, created_at, updated_at)
SELECT 6,
       'Head of ICT Department at Edutrack Computer Training College',
       'Information Technology',
       1,
       NOW(),
       NOW()
WHERE NOT EXISTS (SELECT 1 FROM instructors WHERE user_id = 6);

-- Ensure Chilala Moonga (user_id 27) has an instructor record
INSERT IGNORE INTO instructors (user_id, bio, specialization, is_verified, created_at, updated_at)
SELECT 27,
       'Principal of Edutrack Computer Training College',
       'Educational Administration',
       1,
       NOW(),
       NOW()
WHERE NOT EXISTS (SELECT 1 FROM instructors WHERE user_id = 27);

-- Create instructor records for ALL users who have the instructor role but no instructor record
INSERT IGNORE INTO instructors (user_id, bio, specialization, is_verified, created_at, updated_at)
SELECT u.id,
       CONCAT('Instructor at Edutrack Computer Training College'),
       'General',
       1,
       NOW(),
       NOW()
FROM users u
JOIN user_roles ur ON u.id = ur.user_id
WHERE ur.role_id = 3  -- Instructor role
AND u.id NOT IN (SELECT user_id FROM instructors)
ORDER BY u.id;

-- Verify results
SELECT
    u.id as user_id,
    u.name,
    u.email,
    i.id as instructor_id,
    i.bio,
    i.specialization,
    GROUP_CONCAT(r.role_name) as roles
FROM users u
LEFT JOIN instructors i ON u.id = i.user_id
JOIN user_roles ur ON u.id = ur.user_id
JOIN roles r ON ur.role_id = r.id
WHERE ur.role_id = 3
GROUP BY u.id, u.name, u.email, i.id, i.bio, i.specialization
ORDER BY u.id;
