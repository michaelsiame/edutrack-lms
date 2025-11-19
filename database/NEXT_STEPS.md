# Next Steps - Complete LMS Setup

## Current Status ✅

You've successfully completed:
- ✅ User authentication working
- ✅ Role-based access control configured
- ✅ Dashboard loading without errors
- ✅ Database compatibility fixes applied

---

## Step 1: Verify Your Database

Run the verification script to see what you have:

```bash
mysql -u root -p edutrack_lms < database/verify_database.sql
```

Or in phpMyAdmin:
1. Open phpMyAdmin → `edutrack_lms` database → SQL tab
2. Copy and paste the contents of `database/verify_database.sql`
3. Click **Go**

This will show:
- All existing tables
- Record counts for each table
- Your users and their roles
- Existing courses and enrollments

---

## Step 2: Choose Your Setup Path

Based on what you see from the verification, choose one of these paths:

### Path A: I Have Basic Tables (users, courses, categories)

**You have:** users, roles, user_roles, courses, course_categories, instructors, students, enrollments

**You need:** modules, lessons, assignments, quizzes, certificates, etc.

**Action:** Run the table expansion script (we'll create this next)

### Path B: I Have Very Few Tables

**You have:** Only users table or just a few tables

**You need:** Complete database schema

**Action:** Run the complete LMS schema

### Path C: I Want to Start Fresh

**You want:** Clean install with sample data for testing

**Action:** Drop and recreate database with complete schema

---

## Step 3A: Expand Existing Database (Path A)

If you have basic tables and want to add advanced features:

```bash
mysql -u root -p edutrack_lms < database/add_advanced_tables.sql
```

This adds:
- Content structure (modules, lessons, resources)
- Assessment system (assignments, quizzes, questions)
- Progress tracking (lesson_progress, quiz_attempts)
- Communication (announcements, discussions, messages)
- Certificates and badges
- Notifications system
- Analytics and activity logs

---

## Step 3B: Install Complete Schema (Path B)

If you have few tables and want everything:

```bash
mysql -u root -p edutrack_lms < database/complete_lms_schema.sql
```

**Warning:** This will create 35 tables. Make sure to backup first!

```bash
# Backup first
mysqldump -u root -p edutrack_lms > backup_before_complete_schema.sql

# Then install
mysql -u root -p edutrack_lms < database/complete_lms_schema.sql
```

---

## Step 3C: Fresh Install with Sample Data (Path C)

If you want to start completely fresh:

```bash
# Drop existing database (WARNING: deletes all data!)
mysql -u root -p -e "DROP DATABASE IF EXISTS edutrack_lms;"

# Create fresh database
mysql -u root -p -e "CREATE DATABASE edutrack_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Install complete schema with sample data
mysql -u root -p edutrack_lms < database/complete_lms_schema.sql

# Apply compatibility fixes
mysql -u root -p edutrack_lms < database/final_compatibility_fix.sql
```

---

## Step 4: Verify Core Features Work

After completing your chosen path, test these features:

### Test 1: Login as Admin
- **URL:** http://localhost/edutrack-lms/public/login.php
- **Email:** admin@edutrack.edu
- **Password:** admin123
- **Expected:** Redirect to admin dashboard

### Test 2: Browse Courses
- **URL:** http://localhost/edutrack-lms/public/courses.php
- **Expected:** See list of courses with categories

### Test 3: View Dashboard
- **URL:** http://localhost/edutrack-lms/public/dashboard.php
- **Expected:** See statistics, enrolled courses, profile card

### Test 4: Profile and Settings
- **URL:** http://localhost/edutrack-lms/public/profile.php
- **Expected:** See user profile with avatar and details

---

## Step 5: Add Sample Data (If Needed)

If you installed the basic schema without sample data, add some test content:

### Add Course Categories

```sql
INSERT INTO course_categories (name, description, slug, icon) VALUES
('Web Development', 'Learn modern web development technologies', 'web-development', 'fa-code'),
('Digital Marketing', 'Master digital marketing strategies', 'digital-marketing', 'fa-bullhorn'),
('Data Science', 'Data analysis and machine learning', 'data-science', 'fa-chart-line'),
('Graphic Design', 'Design principles and tools', 'graphic-design', 'fa-paint-brush'),
('Microsoft Office', 'Office productivity software', 'microsoft-office', 'fa-file-excel');
```

### Add a Test Course

```sql
INSERT INTO courses (
    title, slug, description, category_id, level,
    price, duration_hours, status, thumbnail
) VALUES (
    'Introduction to Web Development',
    'intro-web-development',
    'Learn HTML, CSS, and JavaScript fundamentals',
    (SELECT id FROM course_categories WHERE slug = 'web-development'),
    'Beginner',
    0.00,
    40,
    'published',
    'assets/images/courses/web-dev.jpg'
);
```

### Enroll Admin in a Course

```sql
-- Get IDs
SET @user_id = (SELECT id FROM users WHERE email = 'admin@edutrack.edu');
SET @course_id = (SELECT id FROM courses LIMIT 1);

-- Create enrollment
INSERT INTO enrollments (
    user_id, course_id, enrollment_status,
    enrolled_at, progress_percentage
) VALUES (
    @user_id, @course_id, 'In Progress',
    NOW(), 25.0
);
```

---

## Step 6: Configure Application Settings

### Update .env File (if exists)

Check if you have a `.env` file in the root directory. If not, create one:

```env
# Database
DB_HOST=localhost
DB_NAME=edutrack_lms
DB_USER=root
DB_PASS=

# Application
APP_NAME="EduTrack LMS"
APP_URL=http://localhost/edutrack-lms/public
APP_DEBUG=true

# Email (for later)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
```

### Check config/database.php

Ensure database credentials match your setup.

---

## Step 7: Test Advanced Features (If Installed)

If you installed the complete schema:

### Test Course Content
1. Go to a course page
2. Click "Start Learning" or "Continue"
3. Should see modules and lessons

### Test Assignments (If table exists)
1. Navigate to a course with assignments
2. Should see assignment list
3. Try submitting an assignment

### Test Quizzes (If table exists)
1. Go to a course with quizzes
2. Start a quiz
3. Answer questions and submit

### Test Certificates (If table exists)
1. Complete a course 100%
2. Check "My Certificates" page
3. Should see generated certificate

---

## Common Issues and Solutions

### Issue: "Table doesn't exist" errors

**Cause:** You're trying to access features whose tables weren't created

**Solution:**
- Run `database/verify_database.sql` to see which tables exist
- Install missing tables using the appropriate script
- Or wrap those features in try-catch (already done for dashboard)

### Issue: Courses page is empty

**Cause:** No courses in database

**Solution:** Add sample courses using the SQL in Step 5

### Issue: Can't enroll in courses

**Cause:** Missing enrollments table or wrong column names

**Solution:** Verify table structure matches:
```sql
DESCRIBE enrollments;
-- Should have: id, user_id, course_id, enrollment_status, enrolled_at, progress_percentage
```

### Issue: Dashboard shows all zeros

**Cause:** No enrollment data exists

**Solution:** Either:
1. Add test enrollments (see Step 5)
2. This is normal for a fresh install - create some courses and enroll!

---

## Recommended Next Steps for Production

After getting everything working:

1. **Change Default Passwords**
   ```sql
   -- Update admin password
   UPDATE users
   SET password_hash = '$2y$10$YOUR_NEW_HASHED_PASSWORD'
   WHERE email = 'admin@edutrack.edu';
   ```

2. **Disable Debug Mode**
   - Set `APP_DEBUG = false` in config

3. **Configure Email**
   - Set up SMTP for password resets
   - Configure email verification

4. **Add Real Content**
   - Create actual courses
   - Upload course materials
   - Set up instructor accounts

5. **Configure Backups**
   ```bash
   # Daily backup cron job
   0 2 * * * mysqldump -u root -p edutrack_lms > /backups/edutrack_$(date +\%Y\%m\%d).sql
   ```

---

## Need Help?

### Check Logs
- **PHP Errors:** `C:\xampp\apache\logs\error.log`
- **MySQL Errors:** `C:\xampp\mysql\data\*.err`
- **Application Logs:** `storage/logs/` (if configured)

### Verify Database
```bash
mysql -u root -p edutrack_lms < database/verify_database.sql
```

### Re-run Compatibility Fixes
```bash
mysql -u root -p edutrack_lms < database/final_compatibility_fix.sql
```

---

## Summary

**Current Achievement:**
- ✅ Authentication system working
- ✅ Role-based access control
- ✅ Dashboard functional
- ✅ Database structure compatible

**Next Actions:**
1. Run `verify_database.sql` to see what you have
2. Choose setup path (A, B, or C)
3. Add sample data if needed
4. Test all features
5. Add real content

**You're now ready to build a fully functional LMS!** 🎉
