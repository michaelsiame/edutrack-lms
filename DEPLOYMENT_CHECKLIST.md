# EduTrack LMS - Hostinger Deployment Checklist

## Pre-Deployment Preparation

### On Your Local Machine

- [ ] **Generate Security Keys**
  ```bash
  bash generate-keys.sh
  ```
  - Copy ENCRYPTION_KEY and JWT_SECRET

- [ ] **Prepare Environment File**
  - Copy `.env.hostinger` to `.env`
  - Fill in all `[REPLACE_WITH_xxx]` values
  - Save securely (don't commit!)

- [ ] **Install Dependencies**
  ```bash
  composer install --no-dev --optimize-autoloader
  ```

- [ ] **Create Deployment Package**
  - Option 1: Upload via FTP/SFTP
  - Option 2: Create ZIP file (exclude .git, node_modules)

---

## Hostinger Setup

### 1. PHP Configuration

- [ ] Go to: **Advanced** → **PHP Configuration**
- [ ] Select: **PHP 8.0** or higher
- [ ] Configure settings:
  ```ini
  upload_max_filesize = 50M
  post_max_size = 50M
  max_execution_time = 300
  memory_limit = 256M
  max_input_vars = 3000
  ```
- [ ] Save changes

### 2. Database Setup

- [ ] Go to: **Databases** section
- [ ] Click: **Create new database**
- [ ] Database name: `edutrack_lms` (or custom)
- [ ] Generate strong password
- [ ] **SAVE CREDENTIALS**:
  - Database name: _________________
  - Username: _________________
  - Password: _________________

### 3. Import Database Schema

- [ ] Click: **Manage** → **phpMyAdmin**
- [ ] Select your database
- [ ] Click: **Import** tab
- [ ] Upload: `database/complete_lms_schema.sql`
- [ ] Click: **Import** (Go)
- [ ] Verify: 44 tables created
- [ ] Import migration: `database/migrations/add_live_sessions.sql`

### 4. SSL Certificate

- [ ] Go to: **Security** → **SSL**
- [ ] Enable: **Free SSL** (Let's Encrypt)
- [ ] Wait: 5-15 minutes for activation
- [ ] Verify: HTTPS works

---

## File Deployment

### Upload Files

- [ ] **Method chosen**:
  - [ ] File Manager
  - [ ] FTP/SFTP
  - [ ] SSH

- [ ] **Upload location**:
  - [ ] `public_html/` (main site)
  - [ ] `public_html/lms/` (subdirectory)

- [ ] **All files uploaded**:
  - [ ] `/public/` directory
  - [ ] `/src/` directory
  - [ ] `/config/` directory
  - [ ] `/database/` directory
  - [ ] `/storage/` directory
  - [ ] `/vendor/` directory (or install via composer)
  - [ ] `.env` file
  - [ ] `composer.json`
  - [ ] All other files

### Configure Web Root

- [ ] Go to: **Website** → **Advanced**
- [ ] Find: **Document Root** setting
- [ ] Change to:
  - [ ] `public_html/public` (if deployed to root)
  - [ ] `public_html/lms/public` (if subdirectory)
- [ ] Save changes

### Set File Permissions

Using File Manager or SSH:

- [ ] `/storage/` → 755 or 775
- [ ] `/storage/logs/` → 755 or 775
- [ ] `/storage/cache/` → 755 or 775
- [ ] `/storage/sessions/` → 755 or 775
- [ ] `/storage/certificates/` → 755 or 775
- [ ] `/public/uploads/` → 755 or 775
- [ ] `/.env` → 644
- [ ] All other directories → 755
- [ ] All PHP files → 644

---

## Configuration

### Environment File

- [ ] `.env` file exists in project root
- [ ] All database credentials filled
- [ ] `APP_URL` updated to your domain
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `SESSION_SECURE=true`
- [ ] `ENCRYPTION_KEY` generated and filled
- [ ] `JWT_SECRET` generated and filled
- [ ] Email SMTP configured
- [ ] Site information updated

### .htaccess Files

- [ ] `/public/.htaccess` exists
- [ ] `/public/api/.htaccess` exists
- [ ] HTTPS redirect enabled (if SSL active)
- [ ] Security rules in place

### Database Connection

- [ ] Test database connection
- [ ] No connection errors in logs

---

## Email Setup

Choose ONE option:

### Option A: Hostinger Email

- [ ] Go to: **Emails** section
- [ ] Create email account
  - Email: _________________
  - Password: _________________
- [ ] Update `.env`:
  ```env
  MAIL_HOST="smtp.hostinger.com"
  MAIL_PORT=587
  MAIL_USERNAME="your-email@domain.com"
  MAIL_PASSWORD="your-password"
  ```

### Option B: Gmail

- [ ] Enable 2FA on Gmail
- [ ] Generate App Password
- [ ] Update `.env`:
  ```env
  MAIL_HOST="smtp.gmail.com"
  MAIL_PORT=587
  MAIL_USERNAME="your-email@gmail.com"
  MAIL_PASSWORD="16-char-app-password"
  ```

---

## Security Hardening

### Protect Sensitive Directories

- [ ] Create `.htaccess` in `/config/`:
  ```apache
  Deny from all
  ```
- [ ] Create `.htaccess` in `/storage/`:
  ```apache
  Deny from all
  ```
- [ ] Create `.htaccess` in `/database/`:
  ```apache
  Deny from all
  ```

### Verify Protection

- [ ] Try accessing: `https://yourdomain.com/../.env`
  - Should show: **403 Forbidden** or **404 Not Found**
- [ ] Try accessing: `https://yourdomain.com/../config/database.php`
  - Should show: **403 Forbidden** or **404 Not Found**

### Security Headers

- [ ] HTTPS redirect working
- [ ] Security headers in place (check `/src/includes/security-headers.php`)

---

## Create Admin Account

### Method 1: Via Database

- [ ] Open phpMyAdmin
- [ ] Select `users` table
- [ ] Click **Insert**
- [ ] Fill in:
  - `username`: admin
  - `email`: your-email@domain.com
  - `password`: (generate hash - see guide)
  - `role`: admin
  - `status`: active
  - `created_at`: CURRENT_TIMESTAMP

### Method 2: Via Registration

- [ ] Register new account on site
- [ ] Open phpMyAdmin
- [ ] Find user in `users` table
- [ ] Change `role` to `admin`

---

## Testing

### Basic Functionality

- [ ] **Homepage loads**
  - URL: https://yourdomain.com
  - No errors displayed
  - Page renders correctly

- [ ] **Login page works**
  - URL: https://yourdomain.com/login.php
  - Form displays
  - Can submit (test with admin account)

- [ ] **Registration works**
  - URL: https://yourdomain.com/register.php
  - Can create new account
  - Email verification sent (if enabled)

- [ ] **Admin dashboard accessible**
  - Login as admin
  - Dashboard loads
  - No permission errors

### Database

- [ ] Database connection successful
- [ ] 44 tables present
- [ ] Live sessions migration applied
- [ ] Sample data accessible

### File Uploads

- [ ] Upload profile picture
- [ ] File saved to `/public/uploads/`
- [ ] Image displays correctly
- [ ] No permission errors

### Email

- [ ] Send test email
- [ ] Email received (check spam folder)
- [ ] Email formatting correct
- [ ] Links in email work

### API Endpoints

- [ ] Test: `/api/auth.php`
- [ ] Test: `/api/lessons.php`
- [ ] CORS headers working
- [ ] JSON responses valid

### SSL/HTTPS

- [ ] HTTPS working
- [ ] HTTP redirects to HTTPS
- [ ] No mixed content warnings
- [ ] SSL certificate valid

---

## Performance Optimization

### Enable Caching

- [ ] Browser caching enabled (in `.htaccess`)
- [ ] Gzip compression enabled (in `.htaccess`)

### PHP OpCache

- [ ] OPcache enabled (check PHP settings)
- [ ] Settings optimized:
  ```ini
  opcache.enable=1
  opcache.memory_consumption=128
  opcache.max_accelerated_files=10000
  ```

---

## Monitoring & Maintenance

### Error Logs

- [ ] Check `/storage/logs/` for errors
- [ ] Review and fix any issues
- [ ] Verify log permissions (writable)

### Backups

- [ ] Hostinger daily backups enabled
- [ ] Test backup restoration
- [ ] Download local backup
- [ ] Schedule: _________________

### Regular Maintenance

- [ ] Monitor disk space
- [ ] Review error logs weekly
- [ ] Update dependencies monthly
- [ ] Security patches applied

---

## Post-Deployment

### Domain Configuration

If using custom domain:

- [ ] Domain purchased
- [ ] DNS configured to point to Hostinger
- [ ] Update `APP_URL` in `.env`
- [ ] SSL certificate for new domain
- [ ] Test with new domain

### Content Setup

- [ ] Create course categories
- [ ] Add initial courses
- [ ] Upload course materials
- [ ] Configure payment methods
- [ ] Set pricing

### User Management

- [ ] Create instructor accounts
- [ ] Assign course permissions
- [ ] Configure user roles
- [ ] Test enrollment flow

### Communication

- [ ] Test email notifications
- [ ] Configure announcement system
- [ ] Set up discussion forums
- [ ] Test live session scheduling

---

## Final Verification

- [ ] All features tested
- [ ] No critical errors in logs
- [ ] Performance acceptable
- [ ] Security measures in place
- [ ] Backups configured
- [ ] Monitoring setup
- [ ] Documentation reviewed
- [ ] Support contacts saved

---

## Launch

- [ ] **Production environment ready**
- [ ] **All tests passed**
- [ ] **Backups verified**
- [ ] **Team trained**
- [ ] **Support plan in place**

### Go Live!

- [ ] Announce launch
- [ ] Monitor for issues
- [ ] Respond to user feedback
- [ ] Track performance metrics

---

## Emergency Contacts

**Hostinger Support:**
- Live Chat: Available 24/7 in dashboard
- Email: _________________
- Phone: _________________

**Technical Support:**
- Developer: _________________
- Email: _________________
- Phone: _________________

---

## Rollback Plan

If critical issues occur:

1. [ ] Enable maintenance mode:
   ```env
   MAINTENANCE_MODE=true
   ```

2. [ ] Restore from backup:
   - Go to: **Backups** in Hostinger
   - Select most recent backup
   - Restore files and database

3. [ ] Review error logs
4. [ ] Fix issues
5. [ ] Test thoroughly
6. [ ] Disable maintenance mode

---

**Deployment completed:** ____ / ____ / ________

**Deployed by:** _______________________

**Notes:**
_______________________________________________________
_______________________________________________________
_______________________________________________________
