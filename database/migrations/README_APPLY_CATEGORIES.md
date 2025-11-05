# Apply Categories Table Migration

## IMPORTANT: Run this migration first to fix the admin dashboard

The admin dashboard is currently failing because the `categories` table is missing.

## How to Apply

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select the `edutrack_lms` database from the left sidebar
3. Click on the "SQL" tab at the top
4. Open the file: `database/migrations/create_categories_table.sql`
5. Copy ALL the SQL content from that file
6. Paste it into the SQL query box in phpMyAdmin
7. Click "Go" button to execute

## What This Migration Does

- Creates the `categories` table for course categorization
- Adds 8 default categories (Web Development, Mobile Development, etc.)
- Adds `category_id` column to `courses` table
- Updates existing courses to use the default "Web Development" category

## After Applying

After running this migration, the admin dashboard pages should work:
- Admin Courses page will load without errors
- Categories management will be functional
- Course filtering by category will work

## Other Pending Migrations

Don't forget to also apply:
- `APPLY_THIS_FIX.sql` - Adds status column to enrollments table
