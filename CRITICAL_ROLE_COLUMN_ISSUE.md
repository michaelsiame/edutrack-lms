# CRITICAL: Role Column Schema Mismatch

**Status:** 🔴 **BLOCKING** - Causing 500 errors
**Severity:** HIGH
**Impact:** Admin dashboard, user management, statistics all failing

---

## The Problem

Your canonical database schema uses a **normalized role structure**:
- `roles` table - stores role definitions
- `user_roles` table - maps users to roles (many-to-many)
- `users` table - **NO role column**

However, the codebase expects a **denormalized structure**:
- `users.role` column - direct role assignment (DOES NOT EXIST in canonical schema)

---

## Impact Analysis

**Files Affected:** 20+ locations across the codebase

### 1. Statistics Class (src/classes/Statistics.php)
```php
// Line 26
SELECT COUNT(*) FROM users WHERE role = 'student'  // ❌ FAILS

// Line 34
SELECT COUNT(*) FROM users WHERE role = 'instructor'  // ❌ FAILS

// Line 42
SELECT COUNT(*) FROM users WHERE role = 'admin'  // ❌ FAILS
```

### 2. Admin Dashboard (public/admin/index.php)
```php
// Line 29
SELECT id, first_name, last_name, email, role, created_at
FROM users  // ❌ role column doesn't exist
```

### 3. User Management (public/admin/users/index.php)
```php
// Lines 87-89
SELECT COUNT(*) FROM users WHERE role = 'student'  // ❌ FAILS
SELECT COUNT(*) FROM users WHERE role = 'instructor'  // ❌ FAILS
SELECT COUNT(*) FROM users WHERE role = 'admin'  // ❌ FAILS
```

### 4. Student Management (public/admin/students/index.php)
```php
// Lines 80-83
WHERE role = 'student'  // ❌ FAILS
WHERE role = 'student' AND status = 'active'  // ❌ FAILS
WHERE role = 'student' AND status = 'inactive'  // ❌ FAILS
WHERE role = 'student' AND status = 'suspended'  // ❌ FAILS
```

### 5. Course Creation (public/admin/courses/create.php)
```php
// Line 105
SELECT id, first_name, last_name, email
FROM users WHERE role IN ('instructor', 'admin')  // ❌ FAILS
```

**Total:** 20+ query failures causing 500 errors

---

## Solution Options

### Option 1: Add role Column (RECOMMENDED - Quick Fix)

**Pros:**
- ✅ Fixes all errors immediately
- ✅ No code changes needed
- ✅ Simpler queries (better performance)
- ✅ Matches existing hotfix approach

**Cons:**
- ⚠️ Denormalized (role stored in two places)
- ⚠️ Deviates from canonical schema

**SQL Fix:**
```bash
mysql -u root -p edutrack_lms < database/CRITICAL_FIX_add_role_column.sql
```

**File:** `/home/user/edutrack-lms/database/CRITICAL_FIX_add_role_column.sql`

---

### Option 2: Update All Queries to Use JOINs (Proper Normalization)

**Pros:**
- ✅ Matches canonical schema
- ✅ Properly normalized
- ✅ Single source of truth for roles

**Cons:**
- ⚠️ Requires updating 20+ files
- ⚠️ More complex queries (JOINs everywhere)
- ⚠️ Potential performance impact
- ⚠️ Higher risk of bugs

**Example Query Changes:**
```php
// OLD (simple but fails):
SELECT COUNT(*) FROM users WHERE role = 'student'

// NEW (complex but correct):
SELECT COUNT(DISTINCT u.id)
FROM users u
INNER JOIN user_roles ur ON u.id = ur.user_id
INNER JOIN roles r ON ur.role_id = r.id
WHERE r.role_name = 'Student'
```

Would require updating:
- Statistics.php (3 queries)
- admin/index.php (1 query)
- admin/users/index.php (3 queries)
- admin/students/index.php (5 queries)
- admin/students/view.php (1 query)
- admin/courses/create.php (1 query)
- Plus any other files

---

## Login Redirect Issue

The login redirect loop is likely caused by role-checking middleware failing:

**File:** `src/middleware/admin-only.php`
**Issue:** Trying to check `$_SESSION['role']` or `$user->role` which doesn't exist

---

## Recommended Action Plan

### Immediate (NOW):
1. **Run the SQL fix:**
   ```bash
   mysql -u root -p edutrack_lms < database/CRITICAL_FIX_add_role_column.sql
   ```

2. **Verify:**
   ```sql
   DESCRIBE users;  -- Should show 'role' column
   SELECT role, COUNT(*) FROM users GROUP BY role;
   ```

3. **Test:**
   - Visit `/admin/index.php` - should load without 500 error
   - Login should work correctly
   - Admin dashboard should display stats

### Long-term (Optional):
- Decide if you want to refactor to use normalized role structure
- Update canonical schema documentation to include role column
- Or update all code to use user_roles JOINs

---

## Files Created

1. **CRITICAL_FIX_add_role_column.sql** - SQL script to add role column
2. **CRITICAL_ROLE_COLUMN_ISSUE.md** - This file (documentation)

---

## Next Steps

Run this command now to fix the 500 errors:

```bash
cd /home/user/edutrack-lms
mysql -u root -p edutrack_lms < database/CRITICAL_FIX_add_role_column.sql
```

Then refresh your admin panel and try logging in again.
