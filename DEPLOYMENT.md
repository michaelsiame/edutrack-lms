# EduTrack LMS Deployment Guide

## Recommended Hosting Setup (Hostinger)

### Step 1: Purchase Hosting
1. Go to [Hostinger.com](https://www.hostinger.com)
2. Choose **Premium Shared Hosting** (~$2.99/month)
3. Register a domain (e.g., edutrack.co.zm or edutrack.com)

### Step 2: Access cPanel
1. Login to Hostinger dashboard
2. Click "Manage" on your hosting plan
3. Access cPanel

### Step 3: Create Database
1. In cPanel, go to **MySQL Databases**
2. Create new database: `edutrack_db`
3. Create database user with strong password
4. Grant ALL PRIVILEGES to user on database
5. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

### Step 4: Upload Files
1. In cPanel, open **File Manager**
2. Navigate to `public_html` folder
3. Delete default files
4. Upload all EduTrack files EXCEPT:
   - `.git` folder
   - `node_modules` (if any)
   - `.env` file
   - `CODEBASE_ANALYSIS.md`
   - `QUICK_REFERENCE.md`

**File Structure After Upload:**
```
public_html/
├── public/          (web root)
│   ├── index.php
│   ├── course.php
│   ├── search.php
│   └── ...
├── src/
│   ├── bootstrap.php
│   ├── classes/
│   ├── includes/
│   └── templates/
├── uploads/
└── vendor/ (if using Composer)
```

### Step 5: Configure Web Root
1. In Hostinger dashboard, go to **Advanced > PHP Configuration**
2. Set **Document Root** to: `/public_html/public`

   This ensures `public/index.php` is accessible at `yourdomain.com/`

### Step 6: Import Database
1. In cPanel, open **phpMyAdmin**
2. Select your `edutrack_db` database
3. Click **Import** tab
4. Upload your SQL dump file
5. Click **Go** to import

### Step 7: Configure Application
1. In File Manager, edit `/public_html/src/includes/config.php`
2. Update database credentials:

```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'EduTrack LMS');

// Upload Paths
define('UPLOAD_PATH', '/home/your_username/public_html/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Environment
define('ENVIRONMENT', 'production'); // Change from 'development' to 'production'
define('DEBUG_MODE', false); // IMPORTANT: Set to false in production
```

### Step 8: Set File Permissions
In File Manager or via SSH:
```bash
chmod 755 /public_html/public
chmod 755 /public_html/src
chmod 777 /public_html/uploads
chmod 777 /public_html/uploads/courses
chmod 777 /public_html/uploads/courses/thumbnails
chmod 777 /public_html/uploads/users
chmod 777 /public_html/uploads/certificates
chmod 644 /public_html/src/includes/config.php
```

### Step 9: Create .htaccess (if not exists)
Create `/public_html/public/.htaccess`:

```apache
# Security
Options -Indexes
ServerSignature Off

# PHP Configuration
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
php_value max_input_time 300

# URL Rewriting (if you want clean URLs later)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Remove .php extension (optional)
    # RewriteCond %{REQUEST_FILENAME} !-d
    # RewriteCond %{REQUEST_FILENAME}\.php -f
    # RewriteRule ^(.*)$ $1.php [NC,L]
</IfModule>

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "(config\.php|\.env)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Step 10: SSL Certificate
1. In Hostinger panel, go to **Security > SSL**
2. Enable **Free SSL Certificate** (Let's Encrypt)
3. Wait 5-10 minutes for activation
4. Force HTTPS redirection (via .htaccess above)

### Step 11: Create Admin Account
1. Visit: `https://yourdomain.com/admin/login.php`
2. If no admin exists, create one via phpMyAdmin:

```sql
INSERT INTO users (first_name, last_name, email, password, role, status, created_at)
VALUES (
    'Admin',
    'User',
    'admin@yourdomain.com',
    -- Use password_hash('your_password', PASSWORD_DEFAULT)
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    'admin',
    'active',
    NOW()
);
```

**IMPORTANT:** Change the default password immediately after first login!

### Step 12: Test Application
Visit these URLs to verify:
- ✅ Homepage: `https://yourdomain.com`
- ✅ Courses: `https://yourdomain.com/courses.php`
- ✅ Search: `https://yourdomain.com/search.php`
- ✅ Admin: `https://yourdomain.com/admin/`

---

## Alternative Setup (DigitalOcean VPS)

For more control and better performance:

### 1. Create Droplet
```bash
# Choose Ubuntu 22.04 LTS
# Select $6/month plan
# Choose datacenter: Frankfurt (closest to Zambia)
```

### 2. SSH into Server
```bash
ssh root@your_server_ip
```

### 3. Install LAMP Stack
```bash
# Update system
apt update && apt upgrade -y

# Install Apache
apt install apache2 -y

# Install MySQL
apt install mysql-server -y
mysql_secure_installation

# Install PHP 8.1
apt install php8.1 php8.1-cli php8.1-common php8.1-mysql php8.1-zip \
    php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath -y

# Enable Apache modules
a2enmod rewrite
systemctl restart apache2
```

### 4. Configure MySQL
```bash
mysql -u root -p

CREATE DATABASE edutrack_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'edutrack_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON edutrack_db.* TO 'edutrack_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Upload Files
```bash
# From your local machine:
scp -r /path/to/edutrack-lms root@your_server_ip:/var/www/html/edutrack

# Or use Git:
ssh root@your_server_ip
cd /var/www/html
git clone https://github.com/yourusername/edutrack-lms.git edutrack
```

### 6. Configure Apache
```bash
nano /etc/apache2/sites-available/edutrack.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/edutrack/public

    <Directory /var/www/html/edutrack/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/edutrack_error.log
    CustomLog ${APACHE_LOG_DIR}/edutrack_access.log combined
</VirtualHost>
```

Enable site:
```bash
a2ensite edutrack.conf
systemctl reload apache2
```

### 7. Set Permissions
```bash
chown -R www-data:www-data /var/www/html/edutrack
chmod -R 755 /var/www/html/edutrack
chmod -R 777 /var/www/html/edutrack/uploads
```

### 8. Install SSL (Let's Encrypt)
```bash
apt install certbot python3-certbot-apache -y
certbot --apache -d yourdomain.com -d www.yourdomain.com
```

---

## Video Storage Setup (Optional)

### Option 1: YouTube (Recommended for Free)
1. Create YouTube channel for your institution
2. Upload course videos as **Unlisted** or **Private**
3. Get embed codes
4. Store YouTube video IDs in `promo_video_url` field

### Option 2: Bunny.net (Cost-Effective CDN)
1. Sign up at bunny.net
2. Create storage zone (~€1/month)
3. Upload videos via FTP/API
4. Use Bunny Stream for HLS streaming
5. Update video URLs in database

### Option 3: Cloudflare R2
1. Sign up for Cloudflare account
2. Create R2 bucket (10GB free)
3. Generate API keys
4. Upload videos programmatically
5. Use signed URLs for protected content

---

## Post-Deployment Checklist

- [ ] Database connected successfully
- [ ] Admin login working
- [ ] File uploads working (test course thumbnail)
- [ ] Email configuration (for notifications)
- [ ] SMTP settings (use Gmail SMTP or Mailgun)
- [ ] SSL certificate active (HTTPS)
- [ ] Backup strategy configured
- [ ] Error reporting disabled in production
- [ ] Strong admin passwords set
- [ ] File permissions correct (not 777 except uploads)
- [ ] PHP version 7.4+ confirmed
- [ ] MySQL version 5.7+ confirmed
- [ ] Test enrollment flow
- [ ] Test payment integration (if applicable)
- [ ] Configure cron jobs for automated tasks

---

## Monitoring & Maintenance

### Backups
**Automated Daily Backups:**
```bash
# Add to crontab (crontab -e)
0 2 * * * /usr/local/bin/backup-edutrack.sh
```

Create `/usr/local/bin/backup-edutrack.sh`:
```bash
#!/bin/bash
BACKUP_DIR="/home/backups/edutrack"
DATE=$(date +%Y%m%d_%H%M%S)

# Database backup
mysqldump -u edutrack_user -p'password' edutrack_db | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/edutrack/uploads

# Keep only last 7 days
find $BACKUP_DIR -mtime +7 -delete
```

### Monitoring
- Enable Google Analytics
- Set up uptime monitoring (UptimeRobot - free)
- Monitor disk space
- Monitor database size

---

## Cost Summary

### Budget Option (Shared Hosting)
```
Hostinger Premium:     $2.99/month
Domain (.com):         $12/year
YouTube Storage:       FREE
Cloudflare CDN:        FREE
--------------------------------
TOTAL:                 ~$48/year
```

### Growth Option (VPS)
```
DigitalOcean Droplet:  $6/month
Domain:                $12/year
Bunny.net CDN:         €1-5/month
Backups:               $1/month
--------------------------------
TOTAL:                 ~$100/year
```

---

## Support & Troubleshooting

### Common Issues:

**1. 500 Internal Server Error**
- Check Apache error logs: `tail -f /var/log/apache2/error.log`
- Verify file permissions
- Check .htaccess syntax

**2. Database Connection Failed**
- Verify credentials in config.php
- Ensure MySQL service is running: `systemctl status mysql`
- Check MySQL user privileges

**3. File Upload Fails**
- Check upload directory permissions: `chmod 777 /path/to/uploads`
- Verify PHP upload limits in php.ini
- Check disk space: `df -h`

**4. Session Issues**
- Verify session directory permissions
- Check PHP session configuration

### Get Help:
- EduTrack Documentation: (link to your docs)
- Hostinger Support: 24/7 live chat
- DigitalOcean Community: community.digitalocean.com

---

## Security Best Practices

1. **Never commit credentials to Git**
2. **Use environment variables for sensitive data**
3. **Keep software updated** (PHP, MySQL, Apache)
4. **Use strong passwords** (minimum 16 characters)
5. **Enable fail2ban** (for VPS) to prevent brute force
6. **Regular backups** (automated daily)
7. **Monitor logs** for suspicious activity
8. **Use HTTPS everywhere**
9. **Sanitize all user inputs** (already implemented)
10. **Regular security audits**

---

Good luck with your deployment! 🚀
