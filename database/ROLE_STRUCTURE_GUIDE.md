# Role Structure Setup Guide

## Overview

The authentication system now uses the database's **Roles** and **User_Roles** tables instead of expecting a simple `role` column in the users table.

---

## Required Database Structure

### 1. Roles Table

Make sure you have a `roles` table with at least these roles:

```sql
SELECT * FROM roles;
```

**Expected roles:**
- `Admin` or `Super Admin` - mapped to `'admin'`
- `Instructor` - mapped to `'instructor'`
- `Student` - mapped to `'student'`

If this table is empty, insert the basic roles:

```sql
INSERT INTO roles (role_name, description, permissions) VALUES
('Admin', 'Administrative access to manage system', '{"users": ["create", "read", "update", "delete"], "courses": ["create", "read", "update", "delete"]}'),
('Instructor', 'Can create and manage courses', '{"courses": ["create", "read", "update"], "students": ["read"], "grades": ["create", "update"]}'),
('Student', 'Can enroll and access courses', '{"courses": ["read", "enroll"], "assignments": ["submit"], "quizzes": ["take"]}');
```

### 2. User_Roles Table

This junction table links users to their roles:

```sql
SELECT * FROM user_roles;
```

### 3. Assign Admin Role

Make sure your admin user has a role assigned:

```sql
-- Check if admin has a role
SELECT u.id, u.email, r.role_name
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
WHERE u.email = 'admin@edutrack.edu';
```

If the admin user has no role, assign one:

```sql
-- Get admin user ID and Admin role ID
SET @admin_user_id = (SELECT id FROM users WHERE email = 'admin@edutrack.edu');
SET @admin_role_id = (SELECT id FROM roles WHERE role_name LIKE '%Admin%' LIMIT 1);

-- Assign admin role to admin user
INSERT INTO user_roles (user_id, role_id)
VALUES (@admin_user_id, @admin_role_id)
ON DUPLICATE KEY UPDATE role_id = @admin_role_id;
```

---

## How It Works

### Login Process

When a user logs in, the system:
1. Fetches user from `users` table
2. Joins with `user_roles` and `roles` tables to get role name
3. Converts role name to simplified format:
   - `'Admin'` or `'Super Admin'` → `'admin'`
   - `'Instructor'` → `'instructor'`
   - `'Student'` → `'student'`
4. Stores role in session as `$_SESSION['user_role']`

### Registration Process

When a new user registers:
1. User is created in `users` table
2. Role is assigned via `user_roles` table
3. Default role is `'Student'` if not specified

---

## Verification

After setting up roles, verify the configuration:

```sql
-- Check all users and their roles
SELECT
    u.id,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as name,
    r.role_name,
    CASE
        WHEN r.role_name LIKE '%Admin%' THEN 'Maps to: admin'
        WHEN r.role_name LIKE '%Instructor%' THEN 'Maps to: instructor'
        WHEN r.role_name LIKE '%Student%' THEN 'Maps to: student'
        ELSE 'Maps to: student (default)'
    END as mapped_role
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
ORDER BY u.id;
```

---

## Testing

Try logging in with your admin account:
- **Email:** admin@edutrack.edu
- **Password:** admin123

You should be redirected to:
- **Admin role** → `/admin/index.php`
- **Instructor role** → `/instructor/index.php`
- **Student role** → `/dashboard.php`

---

## Troubleshooting

### Still getting "Undefined array key 'role'" error?

Check:
1. `roles` table exists and has data
2. `user_roles` table exists
3. User has a role assigned in `user_roles` table

### User has wrong role?

Update the role assignment:

```sql
-- Update user's role
UPDATE user_roles ur
SET ur.role_id = (SELECT id FROM roles WHERE role_name = 'Admin')
WHERE ur.user_id = (SELECT id FROM users WHERE email = 'admin@edutrack.edu');
```

### No roles table?

You may need to run the complete schema setup. The roles-based structure is part of the full LMS schema in `complete_lms_schema.sql`.

---

## Alternative: Use Simple Role Column

If you prefer a simpler structure with a `role` column directly in the users table, run:

```bash
mysql -u your_username -p edutrack_lms < database/hotfix_add_role_column.sql
```

This will:
- Add `role` column to users table
- Populate it automatically based on existing data
- Revert to the simpler role structure

**Note:** If you do this, you'll need to revert the code changes to use the role column instead of the tables.
