# 🚀 Hostinger Deployment Guide - EduTrack LMS Admin Panel

This guide will help you deploy the EduTrack LMS with the new admin panel database integration on Hostinger.

## ✅ Prerequisites Checklist

Before starting, ensure you have:
- [ ] Hostinger account with active hosting plan
- [ ] Domain name configured (e.g., edutrackzambia.com)
- [ ] Database created in Hostinger
- [ ] SSH/FTP access to your hosting
- [ ] Git installed (optional, for deployment)

---

## 📋 Step-by-Step Deployment

### Step 1: Database Setup on Hostinger

1. **Login to Hostinger Control Panel** (hPanel)

2. **Create/Verify Database:**
   - Go to: **Websites** → **Your Domain** → **Databases** → **MySQL Databases**
   - Your database details:
     ```
     Database Name: u605780771_edutrack_lms
     Username: u605780771_root
     Password: /L4e*U4DndVx
     Host: localhost
     Port: 3306
     ```
   - ✅ These are already in your `.env` file!

3. **Import Database Schema:**
   - Click on **phpMyAdmin** next to your database
   - Select your database: `u605780771_edutrack_lms`
   - Go to **Import** tab
   - Upload file: `database/complete_lms_schema.sql`
   - Click **Go**
   - ✅ Wait for success message

4. **Verify Tables:**
   - In phpMyAdmin, check that all 45+ tables are created
   - Look for: users, courses, enrollments, transactions, etc.

---

### Step 2: Upload Files to Hostinger

#### Option A: Using File Manager (Recommended for beginners)

1. **Access File Manager:**
   - Go to: **Websites** → **Your Domain** → **File Manager**

2. **Navigate to public_html:**
   - This is your web root directory

3. **Upload Files:**
   - Click **Upload** button
   - Upload ALL project files EXCEPT:
     - `.git/` folder
     - `node_modules/`
     - `.env.example`
     - `README.md`

4. **Set Correct Structure:**
   Your `public_html` should look like:
   ```
   public_html/
   ├── config/
   ├── database/
   ├── public/          ← This is important!
   │   ├── admin/
   │   ├── api/
   │   ├── assets/
   │   └── index.php
   ├── src/
   ├── storage/
   ├── .env
   └── .htaccess
   ```

#### Option B: Using Git (Recommended for advanced users)

1. **SSH into Hostinger:**
   ```bash
   ssh u605780771@yourdomain.com
   ```

2. **Navigate to web root:**
   ```bash
   cd public_html
   ```

3. **Clone repository:**
   ```bash
   git clone https://github.com/michaelsiame/edutrack-lms.git .
   git checkout claude/fix-admin-database-9QilA
   ```

4. **Copy environment file:**
   ```bash
   cp .env.example .env
   nano .env  # Edit with your Hostinger details
   ```

---

### Step 3: Configure Web Root

**CRITICAL:** Hostinger needs to serve from the `/public` directory!

1. **Access Hostinger Control Panel**

2. **Update Website Root:**
   - Go to: **Websites** → **Your Domain** → **Advanced** → **Website Root**
   - Change from: `/public_html`
   - Change to: `/public_html/public`
   - Click **Save**

   **OR** if you can't change web root:

3. **Alternative: Move Files:**
   ```bash
   # Move everything from public/ to root
   mv public/* ./
   mv public/.htaccess ./
   # Then delete empty public folder
   rm -rf public/
   ```

---

### Step 4: Set File Permissions

Run these commands via SSH or use File Manager's permissions tool:

```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make storage directories writable
chmod -R 775 storage/
chmod -R 775 storage/logs/
chmod -R 775 storage/sessions/
chmod -R 775 storage/cache/

# Make uploads directory writable
chmod -R 775 public/uploads/
```

---

### Step 5: Configure Environment Variables

Your `.env` file should have:

```env
# Application
APP_NAME="Edutrack Computer Training College"
APP_URL="https://edutrackzambia.com"  ← Your actual domain!
APP_ENV="production"
APP_DEBUG=false

# Database (Already configured!)
DB_HOST="localhost"
DB_PORT="3306"
DB_NAME="u605780771_edutrack_lms"
DB_USER="u605780771_root"
DB_PASS="/L4e*U4DndVx"
DB_CHARSET="utf8mb4"

# Session (Important for admin panel!)
SESSION_NAME="edutrack_session"
SESSION_LIFETIME=7200
SESSION_SECURE=true  ← MUST be true for HTTPS!
SESSION_HTTPONLY=true
SESSION_SAMESITE="Lax"

# Email (Already configured with Gmail!)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=edutrackzambia@gmail.com
MAIL_PASSWORD="jtxi srbv vrtr gxau"
MAIL_ENCRYPTION=tls
```

**VERIFY:** Make sure `APP_URL` matches your domain!

---

### Step 6: Verify .htaccess Files

#### Main .htaccess (in web root or /public/)

Should contain:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Redirect to HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Route everything except existing files to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

#### API .htaccess (/public/api/.htaccess)

Should already exist - verify it's uploaded!

---

### Step 7: Create Admin User

You need an admin account to access the admin panel!

#### Option A: Using phpMyAdmin

1. Go to phpMyAdmin
2. Select `u605780771_edutrack_lms` database
3. Go to **SQL** tab
4. Run this query:

```sql
-- Insert admin user
INSERT INTO users (username, email, first_name, last_name, password_hash, status, email_verified, created_at)
VALUES (
    'admin@edutrackzambia.com',
    'admin@edutrackzambia.com',
    'Admin',
    'User',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password: "password"
    'Active',
    1,
    NOW()
);

-- Get the user ID (it will show in the result)
SET @user_id = LAST_INSERT_ID();

-- Assign admin role (role_id 1 = Admin)
INSERT INTO user_roles (user_id, role_id, assigned_at)
VALUES (@user_id, 1, NOW());
```

**Default Login:**
- Email: `admin@edutrackzambia.com`
- Password: `password`

**⚠️ CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN!**

#### Option B: Create via Registration (if enabled)

1. Go to: `https://yourdomain.com/register.php`
2. Register a new account
3. Go to phpMyAdmin
4. Update the user's role:
   ```sql
   INSERT INTO user_roles (user_id, role_id, assigned_at)
   SELECT id, 1, NOW() FROM users WHERE email = 'your@email.com';
   ```

---

### Step 8: Test the Admin Panel

1. **Navigate to Admin Panel:**
   ```
   https://edutrackzambia.com/admin/
   ```

2. **Login with Admin Credentials**

3. **Check Browser Console:**
   - Press F12 → Console tab
   - Look for any errors
   - Network tab: Check if API calls succeed (200 status)

4. **Test Each Feature:**
   - [ ] Dashboard loads
   - [ ] Users page shows database users
   - [ ] Courses page shows database courses
   - [ ] Can create a new user
   - [ ] Can update a course
   - [ ] Can create an enrollment
   - [ ] Transactions show
   - [ ] Announcements work
   - [ ] Settings load

---

## 🔍 Troubleshooting Common Issues

### Issue 1: "500 Internal Server Error"

**Symptoms:** White screen or 500 error

**Solutions:**
1. Check error logs:
   - Hostinger: Go to **File Manager** → `error_log` (in root)
   - Or: `storage/logs/database.log`

2. Check file permissions:
   ```bash
   chmod -R 755 .
   chmod -R 775 storage/
   ```

3. Check `.env` file exists and has correct database credentials

4. Enable error display temporarily (`.env`):
   ```env
   APP_DEBUG=true
   APP_ENV=development
   ```
   **⚠️ Disable after fixing!**

---

### Issue 2: Admin Panel Shows Mock Data (Not Real Database)

**Symptoms:** Admin panel works but shows sample data, not your real courses/users

**Solutions:**

1. **Check API endpoints are accessible:**
   ```
   https://yourdomain.com/api/users.php
   ```
   Should return JSON (might show permission error if not logged in)

2. **Check browser console:**
   - F12 → Network tab
   - Filter by "XHR"
   - Look for API calls
   - Check if they're returning 200 or errors

3. **Verify session is working:**
   ```php
   <?php
   // Create test file: public/test-session.php
   session_start();
   echo "Session ID: " . session_id();
   var_dump($_SESSION);
   ```
   Access: `https://yourdomain.com/test-session.php`

4. **Check admin middleware:**
   - Make sure you're logged in as admin
   - Check `user_roles` table has your user assigned to role_id = 1

---

### Issue 3: "CORS Error" in Browser Console

**Symptoms:**
```
Access to fetch at 'https://yourdomain.com/api/users.php' from origin
'https://yourdomain.com' has been blocked by CORS policy
```

**Solutions:**

1. **Verify .htaccess in /public/api/**
   Should have:
   ```apache
   Header set Access-Control-Allow-Origin "*"
   Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
   Header set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
   ```

2. **Check if mod_headers is enabled:**
   - Hostinger has it enabled by default
   - If not, contact Hostinger support

---

### Issue 4: "Database Connection Error"

**Symptoms:** Can't connect to database

**Solutions:**

1. **Verify database credentials in `.env`:**
   ```env
   DB_HOST="localhost"  ← Must be "localhost" on Hostinger!
   DB_NAME="u605780771_edutrack_lms"
   DB_USER="u605780771_root"
   DB_PASS="/L4e*U4DndVx"
   ```

2. **Test database connection:**
   Create file: `public/test-db.php`
   ```php
   <?php
   require_once '../src/bootstrap.php';

   try {
       $db = Database::getInstance();
       $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
       echo "✅ Database connected! User count: " . $result['count'];
   } catch (Exception $e) {
       echo "❌ Database error: " . $e->getMessage();
   }
   ```
   Access: `https://yourdomain.com/test-db.php`

3. **Check database exists in Hostinger:**
   - phpMyAdmin → Check database is there
   - Check tables are imported

---

### Issue 5: Admin Can't Login

**Symptoms:** Invalid credentials or permission denied

**Solutions:**

1. **Verify admin user exists:**
   ```sql
   SELECT u.*, r.name as role_name
   FROM users u
   LEFT JOIN user_roles ur ON u.id = ur.user_id
   LEFT JOIN roles r ON ur.role_id = r.id
   WHERE u.email = 'admin@edutrackzambia.com';
   ```

2. **Reset admin password:**
   ```sql
   UPDATE users
   SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
   WHERE email = 'admin@edutrackzambia.com';
   ```
   Password will be: `password`

3. **Ensure admin role assigned:**
   ```sql
   INSERT INTO user_roles (user_id, role_id, assigned_at)
   SELECT id, 1, NOW() FROM users
   WHERE email = 'admin@edutrackzambia.com'
   AND id NOT IN (SELECT user_id FROM user_roles WHERE role_id = 1);
   ```

---

## 🛡️ Security Checklist (Before Going Live)

- [ ] Change default admin password
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_ENV=production` in `.env`
- [ ] SSL Certificate active (HTTPS)
- [ ] Set `SESSION_SECURE=true` in `.env`
- [ ] Remove or secure test files (`test-db.php`, `test-session.php`)
- [ ] Check file permissions (not 777 anywhere)
- [ ] Verify `.env` file is NOT publicly accessible
- [ ] Test all forms for CSRF protection
- [ ] Review error logs regularly
- [ ] Set up automatic backups in Hostinger

---

## 📊 Verify Everything Works

### Quick Test Checklist

1. **Frontend:**
   - [ ] Homepage loads: `https://yourdomain.com`
   - [ ] Can view courses
   - [ ] Can register new account
   - [ ] Can login

2. **Admin Panel:**
   - [ ] Admin panel loads: `https://yourdomain.com/admin/`
   - [ ] Dashboard shows real statistics
   - [ ] Users page shows database users (not mock data)
   - [ ] Can create new user
   - [ ] Courses page shows real courses
   - [ ] Can edit a course
   - [ ] Enrollments show real data
   - [ ] Can create enrollment
   - [ ] Transactions visible
   - [ ] Categories show with course counts
   - [ ] Can create announcement
   - [ ] Settings load and save
   - [ ] Activity logs visible

3. **API Endpoints:**
   Test each endpoint (while logged in as admin):
   - [ ] `https://yourdomain.com/api/users.php` - Returns JSON
   - [ ] `https://yourdomain.com/api/courses.php` - Returns JSON
   - [ ] `https://yourdomain.com/api/enrollments.php` - Returns JSON
   - [ ] `https://yourdomain.com/api/transactions.php` - Returns JSON
   - [ ] `https://yourdomain.com/api/categories.php` - Returns JSON
   - [ ] `https://yourdomain.com/api/announcements.php` - Returns JSON
   - [ ] `https://yourdomain.com/api/certificates.php` - Returns JSON
   - [ ] `https://yourdomain.com/api/settings.php` - Returns JSON
   - [ ] `https://yourdomain.com/api/logs.php` - Returns JSON

---

## 📞 Getting Help

### Check Logs First:

1. **Hostinger Error Log:**
   - File Manager → `error_log` (in root directory)

2. **Application Logs:**
   - `storage/logs/database.log`
   - `storage/logs/app.log`

3. **Browser Console:**
   - F12 → Console tab (for JavaScript errors)
   - F12 → Network tab (for API errors)

### If Still Stuck:

1. **Hostinger Support:**
   - 24/7 live chat
   - Email: support@hostinger.com

2. **Check Documentation:**
   - `/public/api/ADMIN_API.md` - API documentation
   - `/ADMIN_INTEGRATION_SUMMARY.md` - Integration guide

---

## 🎯 Expected Results

After following this guide, you should have:

✅ **Working Website** at `https://edutrackzambia.com`
✅ **Functional Admin Panel** at `https://edutrackzambia.com/admin/`
✅ **Real Database Integration** - No more mock data!
✅ **All CRUD Operations Working:**
   - Create users, courses, enrollments
   - Update and delete records
   - View transactions and logs
✅ **Proper Authentication** - Admin-only access to admin panel
✅ **Secure Configuration** - HTTPS, secure sessions, protected files

---

## 📚 Additional Resources

- [Hostinger Knowledge Base](https://support.hostinger.com/)
- [PHP on Hostinger](https://support.hostinger.com/en/articles/1583223-how-to-use-php-on-hostinger)
- [MySQL on Hostinger](https://support.hostinger.com/en/collections/1628844-databases)

---

**🎉 Congratulations! Your EduTrack LMS Admin Panel is now fully integrated with the database and running on Hostinger!**
