# EduTrack LMS - Hostinger Deployment Guide

## Your Hostinger Details
- **Domain**: khaki-dunlin-812469.hostingersite.com (temporary)
- **Hosting**: Active
- **Database**: Available
- **Daily Backups**: Enabled

---

## Prerequisites

### On Your Hostinger Account:
1. **PHP Version**: Ensure PHP 8.0 or higher is enabled
   - Go to: Advanced → PHP Configuration → Select PHP 8.0+

2. **Database Access**:
   - Go to: Databases → Create new MySQL database
   - Note down: Database name, username, password

3. **File Manager or FTP/SFTP Access**:
   - File Manager: Available in Hostinger dashboard
   - Or use FTP client (FileZilla recommended)

4. **SSH Access** (if available on your plan):
   - Go to: Advanced → SSH Access

---

## Step 1: Prepare Your Local Files

### 1.1 Configure Environment File

```bash
# Copy the example environment file
cp .env.example .env
```

Edit `.env` with the following settings:

```env
# Application Settings
APP_NAME="Edutrack Computer Training College"
APP_URL="https://khaki-dunlin-812469.hostingersite.com"
APP_ENV="production"
APP_DEBUG=false
APP_TIMEZONE="Africa/Lusaka"

# Database Configuration (Get from Hostinger)
DB_HOST="localhost"
DB_NAME="your_database_name"
DB_USER="your_database_user"
DB_PASS="your_database_password"
DB_CHARSET="utf8mb4"

# Security Keys (Generate NEW ones - see below)
ENCRYPTION_KEY="your-32-character-encryption-key"
JWT_SECRET="your-64-character-jwt-secret"

# Email Configuration
MAIL_MAILER="smtp"
MAIL_HOST="smtp.hostinger.com"
MAIL_PORT=587
MAIL_ENCRYPTION="tls"
MAIL_USERNAME="your-email@yourdomain.com"
MAIL_PASSWORD="your-email-password"
MAIL_FROM_ADDRESS="your-email@yourdomain.com"
MAIL_FROM_NAME="Edutrack LMS"

# Site Settings
SITE_EMAIL="info@yourdomain.com"
SITE_PHONE="+260-XXX-XXXXXX"
SITE_ADDRESS="Your Address"
CURRENCY="ZMW"
CURRENCY_SYMBOL="K"

# Session Security
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE="Lax"

# File Upload
MAX_UPLOAD_SIZE=52428800
```

### 1.2 Generate Security Keys

Run these commands on your local machine:

```bash
# Generate ENCRYPTION_KEY (32 characters base64)
openssl rand -base64 32

# Generate JWT_SECRET (64 characters base64)
openssl rand -base64 64
```

Copy the output and paste into your `.env` file.

---

## Step 2: Prepare Database on Hostinger

### 2.1 Create Database

1. Log into Hostinger control panel
2. Navigate to: **Databases** section
3. Click: **Create new database**
4. Fill in:
   - **Database name**: `edutrack_lms` (or your choice)
   - **Username**: Auto-generated or custom
   - **Password**: Use a strong password
5. Click **Create**
6. **IMPORTANT**: Note down all credentials

### 2.2 Access phpMyAdmin

1. In Databases section, click: **Manage** next to your database
2. Click: **phpMyAdmin** button
3. You'll be logged in automatically

### 2.3 Import Database Schema

1. In phpMyAdmin, select your database from left sidebar
2. Click: **Import** tab at the top
3. Click: **Choose File**
4. Select: `/database/complete_lms_schema.sql` from your local files
5. Scroll down and click: **Import** (previously **Go**)
6. Wait for import to complete (44 tables will be created)
7. Verify: You should see 44 tables in the left sidebar

---

## Step 3: Upload Files to Hostinger

### Option A: Using File Manager (Recommended for beginners)

1. In Hostinger dashboard, go to: **Files** → **File Manager**
2. Navigate to: `public_html` directory
3. **IMPORTANT**: You have two options:

   **Option 3A: Deploy to root** (Recommended if this is your main site)
   ```
   public_html/
   ├── public/         ← All contents from /public/ folder
   ├── src/
   ├── config/
   ├── database/
   ├── storage/
   ├── vendor/
   ├── .env
   └── composer.json
   ```

   Then configure web root to: `public_html/public`

   **Option 3B: Deploy to subdirectory**
   ```
   public_html/
   └── lms/           ← Create this folder
       ├── public/
       ├── src/
       ├── config/
       └── ...
   ```

4. **Upload all files**:
   - Click: **Upload Files**
   - Select ALL files from your local project
   - Upload to your chosen location
   - This may take several minutes

5. **Extract if uploaded as ZIP**:
   - If you uploaded as ZIP, right-click the ZIP file
   - Select: **Extract**

### Option B: Using FTP/SFTP (Faster for large files)

1. Get FTP credentials from Hostinger:
   - Go to: **Files** → **FTP Accounts**
   - Create FTP account or use existing

2. Use FileZilla or similar FTP client:
   ```
   Host: ftp.yourdomain.com
   Username: your_ftp_username
   Password: your_ftp_password
   Port: 21 (FTP) or 22 (SFTP)
   ```

3. Upload all project files to `public_html/` or subdirectory

---

## Step 4: Install Dependencies

### If you have SSH access:

```bash
# Connect via SSH
ssh your-username@your-server

# Navigate to project directory
cd public_html

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Or if composer not available, use:
php ~/composer.phar install --no-dev --optimize-autoloader
```

### If you DON'T have SSH access:

1. **On your local machine**, run:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. Upload the generated `/vendor/` folder to Hostinger using File Manager or FTP

---

## Step 5: Configure Web Root

### Important: Point domain to /public/ directory

1. In Hostinger control panel, go to: **Website** → **Advanced**
2. Find: **Document Root** or **Web Root** setting
3. Change from: `public_html`
4. Change to: `public_html/public` (if deployed to root)
5. Or: `public_html/lms/public` (if deployed to subdirectory)
6. Save changes

**Why?** The `/public/` directory is the only directory that should be web-accessible for security.

---

## Step 6: Set File Permissions

### Using File Manager:

1. Navigate to each directory below
2. Right-click → **Permissions** (or **Change Permissions**)
3. Set permissions as follows:

```bash
# Directories that need to be writable:
/storage/           → 755 or 775
/storage/logs/      → 755 or 775
/storage/cache/     → 755 or 775
/storage/sessions/  → 755 or 775
/storage/certificates/ → 755 or 775
/public/uploads/    → 755 or 775

# Configuration file (protect from public access)
/.env               → 644 (readable only by owner)

# All other directories
/public/            → 755
/src/               → 755
/config/            → 755
```

### Using SSH (if available):

```bash
cd public_html

# Set writable directories
chmod -R 755 storage/
chmod -R 755 public/uploads/

# Protect sensitive files
chmod 644 .env
chmod 644 config/*.php
```

---

## Step 7: Configure .htaccess Files

### 7.1 Main .htaccess (in /public/)

Verify `/public/.htaccess` exists with this content:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redirect to HTTPS (if SSL enabled)
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Handle front controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Disable directory browsing
Options -Indexes

# Set default charset
AddDefaultCharset UTF-8

# PHP settings
<IfModule mod_php.c>
    php_value upload_max_filesize 50M
    php_value post_max_size 50M
    php_value max_execution_time 300
    php_value memory_limit 256M
</IfModule>
```

### 7.2 API .htaccess (in /public/api/)

Verify `/public/api/.htaccess` exists:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Handle API routing
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ $1.php [L,QSA]
</IfModule>

# Enable CORS
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization"
</IfModule>
```

---

## Step 8: Configure PHP Settings

1. Go to Hostinger: **Advanced** → **PHP Configuration**
2. Adjust these settings:

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 256M
max_input_vars = 3000
```

3. Save changes

---

## Step 9: Enable SSL Certificate (HTTPS)

1. In Hostinger dashboard, go to: **Security** → **SSL**
2. If not already enabled, click: **Install SSL**
3. Choose: **Free SSL** (Let's Encrypt)
4. Wait for provisioning (usually 5-15 minutes)
5. Once active, update `.env`:
   ```env
   APP_URL="https://khaki-dunlin-812469.hostingersite.com"
   SESSION_SECURE=true
   ```

---

## Step 10: Run Database Migration

### If you have SSH access:

```bash
cd public_html
php run-migration.php
```

### Without SSH access:

1. Create a temporary file: `public/run-migration-web.php`

```php
<?php
require_once '../src/bootstrap.php';

// Run migration
$migration = file_get_contents('../database/migrations/add_live_sessions.sql');
$db = Database::getInstance();

try {
    $db->query($migration);
    echo "Migration completed successfully!";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}

// DELETE THIS FILE AFTER RUNNING
echo "<br><br><strong>IMPORTANT: Delete this file now for security!</strong>";
?>
```

2. Access: `https://yourdomain.com/run-migration-web.php`
3. **IMMEDIATELY DELETE** this file after running

---

## Step 11: Test Your Deployment

### 11.1 Basic Tests

1. **Visit homepage**:
   ```
   https://khaki-dunlin-812469.hostingersite.com
   ```
   Should load without errors

2. **Test database connection**:
   - If homepage loads, database is connected

3. **Test login page**:
   ```
   https://khaki-dunlin-812469.hostingersite.com/login.php
   ```

4. **Test registration**:
   ```
   https://khaki-dunlin-812469.hostingersite.com/register.php
   ```

### 11.2 Check Error Logs

1. In File Manager, navigate to: `/storage/logs/`
2. Check for any error files
3. If errors exist, review and fix

### 11.3 Test File Uploads

1. Create a test account
2. Upload a profile picture
3. Verify file appears in `/public/uploads/`

---

## Step 12: Create Admin Account

### Option A: Via Database (phpMyAdmin)

1. Go to phpMyAdmin
2. Select your database
3. Click: `users` table
4. Click: **Insert** tab
5. Fill in:
   ```
   username: admin
   email: your-email@domain.com
   password: (Use password_hash generator)
   role: admin
   status: active
   created_at: CURRENT_TIMESTAMP
   ```

6. For password, use this PHP code to generate hash:
   ```php
   <?php echo password_hash('YourPassword123!', PASSWORD_DEFAULT); ?>
   ```

### Option B: Via Registration (if enabled)

1. Register a new account
2. Go to phpMyAdmin
3. Find the user in `users` table
4. Change `role` from `student` to `admin`

---

## Step 13: Security Hardening

### 13.1 Protect Sensitive Directories

Create `.htaccess` in these directories:

**In `/config/.htaccess`**:
```apache
Deny from all
```

**In `/storage/.htaccess`**:
```apache
Deny from all
```

**In `/database/.htaccess`**:
```apache
Deny from all
```

### 13.2 Verify .env Protection

1. Try accessing: `https://yourdomain.com/../.env`
2. Should show **403 Forbidden** or **404 Not Found**
3. If accessible, contact Hostinger support

### 13.3 Enable Additional Security

Add to `/public/.htaccess`:

```apache
# Protect sensitive files
<FilesMatch "^\.env|composer\.json|composer\.lock|\.git">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevent PHP execution in uploads
<Directory "uploads">
    php_flag engine off
</Directory>
```

---

## Step 14: Configure Email (Optional)

### Using Hostinger Email:

1. Go to: **Emails** section in Hostinger
2. Create email account (e.g., `noreply@yourdomain.com`)
3. Get SMTP settings
4. Update `.env`:

```env
MAIL_HOST="smtp.hostinger.com"
MAIL_PORT=587
MAIL_ENCRYPTION="tls"
MAIL_USERNAME="noreply@yourdomain.com"
MAIL_PASSWORD="your-email-password"
```

### Using Gmail:

1. Enable 2-factor authentication on Gmail
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Update `.env`:

```env
MAIL_HOST="smtp.gmail.com"
MAIL_PORT=587
MAIL_ENCRYPTION="tls"
MAIL_USERNAME="youremail@gmail.com"
MAIL_PASSWORD="your-16-char-app-password"
```

---

## Step 15: Setup Automated Backups

### Daily Database Backups:

Hostinger provides daily backups by default. To create manual backups:

1. Go to: **Backups** section
2. Click: **Create Backup**
3. Select: Database + Files
4. Download backup to local machine periodically

### Automated Backup Script (if SSH available):

Create `/backup-database.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/user/backups"
DB_NAME="edutrack_lms"
DB_USER="your_db_user"
DB_PASS="your_db_pass"

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/backup_$DATE.sql
gzip $BACKUP_DIR/backup_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
```

Add to cron (Hostinger: Advanced → Cron Jobs):
```
0 2 * * * /home/user/backup-database.sh
```

---

## Troubleshooting

### Issue: 500 Internal Server Error

**Solutions:**
1. Check PHP error logs in `/storage/logs/`
2. Verify `.env` file exists and is configured
3. Check file permissions (755 for directories, 644 for files)
4. Verify PHP version is 8.0+
5. Check `.htaccess` syntax
6. Enable error display temporarily:
   ```env
   APP_DEBUG=true
   ```

### Issue: Database Connection Failed

**Solutions:**
1. Verify database credentials in `.env`
2. Check database exists in phpMyAdmin
3. Verify `DB_HOST` is `localhost`
4. Test connection in phpMyAdmin first
5. Check database user has proper privileges

### Issue: Blank White Page

**Solutions:**
1. Check PHP error logs
2. Verify `src/bootstrap.php` exists
3. Check file permissions
4. Enable error display:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

### Issue: CSS/JS Not Loading

**Solutions:**
1. Check web root is pointing to `/public/`
2. Verify `.htaccess` in `/public/` exists
3. Check file permissions on `/public/assets/`
4. Clear browser cache
5. Check for mixed content (HTTP vs HTTPS)

### Issue: File Uploads Failing

**Solutions:**
1. Check `/public/uploads/` permissions (755 or 775)
2. Verify PHP upload settings:
   ```ini
   upload_max_filesize = 50M
   post_max_size = 50M
   ```
3. Check disk space on server
4. Verify web server user can write to uploads directory

### Issue: Email Not Sending

**Solutions:**
1. Verify SMTP credentials in `.env`
2. Test SMTP connection separately
3. Check spam folders
4. Verify port 587 is not blocked
5. Try different MAIL_ENCRYPTION: `tls` or `ssl`
6. Check email logs in `/storage/logs/`

---

## Performance Optimization

### Enable OPcache (if available):

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Enable Browser Caching:

Add to `/public/.htaccess`:

```apache
# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### Enable Gzip Compression:

Add to `/public/.htaccess`:

```apache
# Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## Post-Deployment Checklist

- [ ] All files uploaded successfully
- [ ] Database imported (44 tables)
- [ ] `.env` file configured with production settings
- [ ] Composer dependencies installed (`/vendor/` exists)
- [ ] File permissions set correctly
- [ ] SSL certificate active (HTTPS working)
- [ ] Web root pointing to `/public/` directory
- [ ] Admin account created
- [ ] Login/registration working
- [ ] File uploads working
- [ ] Email sending working (if configured)
- [ ] Error logs empty or minimal
- [ ] Backups configured
- [ ] `APP_DEBUG=false` in production
- [ ] Security headers configured
- [ ] Performance optimizations applied

---

## Important Security Notes

### Never Commit to Git:
- `.env` file
- `/vendor/` directory
- `/storage/logs/` contents
- `/public/uploads/` user files

### Regular Maintenance:
- Review error logs weekly
- Update dependencies monthly: `composer update`
- Monitor disk space usage
- Test backups regularly
- Review user accounts for suspicious activity

### Keep Updated:
- PHP version
- Composer dependencies
- Security patches

---

## Need Help?

### Hostinger Support:
- Live chat available 24/7
- Knowledge base: https://support.hostinger.com
- Ticket system in dashboard

### Common Resources:
- PHP Manual: https://www.php.net/manual/
- Composer: https://getcomposer.org/doc/
- TCPDF: https://tcpdf.org/

---

## Next Steps After Deployment

1. **Connect Custom Domain**:
   - Purchase domain from Hostinger or external registrar
   - Point domain to your hosting
   - Update `.env` with new domain

2. **Configure Payment Gateways**:
   - MTN Mobile Money
   - Airtel Money
   - Zamtel Kwacha
   - Bank transfers

3. **Customize Branding**:
   - Update colors in `/config/app.php`
   - Upload logo
   - Customize email templates

4. **Create Course Content**:
   - Login as admin
   - Create course categories
   - Add courses
   - Upload lessons

5. **Invite Instructors**:
   - Create instructor accounts
   - Assign course permissions

---

## Deployment Complete! 🎉

Your EduTrack LMS should now be live at:
**https://khaki-dunlin-812469.hostingersite.com**

Remember to:
- Keep your `.env` file secure
- Monitor error logs regularly
- Test all functionality
- Setup regular backups
- Keep dependencies updated

**Good luck with your Learning Management System!**
