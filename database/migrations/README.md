# Database Migrations

## Current Migration: Fix 'status' Column Error

### Problem
The application was throwing this error:
```
Fatal error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'status' in 'where clause'
```

This occurred because:
- The `enrollments` table has a column called `enrollment_status`
- Some code (Statistics.php) queries for a column called `status`
- The columns didn't match, causing the query to fail

### Solution
Instead of changing all the code, we've added a `status` column to the `enrollments` table that:
1. Has the same enum values as `enrollment_status`
2. Stays synchronized with `enrollment_status` via database triggers
3. Allows both column names to work in queries

### How to Apply

#### Method 1: Using phpMyAdmin (Recommended for XAMPP users)
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select the `edutrack_lms` database from the left sidebar
3. Click on the **SQL** tab at the top
4. Open the file `APPLY_THIS_FIX.sql` in a text editor
5. Copy the entire contents
6. Paste into the SQL query box in phpMyAdmin
7. Click **Go** to execute

#### Method 2: Using MySQL Command Line
```bash
mysql -u root -p edutrack_lms < APPLY_THIS_FIX.sql
```

### What Gets Created
1. **New Column**: `status` in the `enrollments` table
2. **Data Migration**: Copies all existing `enrollment_status` values to `status`
3. **INSERT Trigger**: Keeps both columns in sync when inserting new records
4. **UPDATE Trigger**: Keeps both columns in sync when updating records

### Testing
After applying the migration:
1. Visit the student dashboard: http://localhost/edutrack-lms/public/dashboard.php
2. The error should be gone
3. Student statistics should display correctly

### Rollback (if needed)
If you need to undo this migration:
```sql
-- Remove the triggers
DROP TRIGGER IF EXISTS enrollments_status_insert;
DROP TRIGGER IF EXISTS enrollments_status_update;

-- Remove the status column
ALTER TABLE enrollments DROP COLUMN status;
```

## Future Migrations
Place any future SQL migration files in this directory with descriptive names and dates.
