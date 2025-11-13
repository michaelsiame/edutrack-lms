# Deploying EduTrack LMS + SMS to edutrackzambia.com
## Multi-Site Hosting Guide

---

## 📋 What We're Building

```
edutrackzambia.com
├── Main Website (Landing page)
├── lms.edutrackzambia.com (Learning Management System)
└── sms.edutrackzambia.com (School Management System)
```

---

## Step 1: Choose & Purchase Hosting

### Recommended: Hostinger Business Plan

**Visit:** https://www.hostinger.com

1. Click **"Web Hosting"** → **"Business"**
2. Choose billing period:
   - 12 months: $3.99/month (save 20%)
   - 24 months: $3.49/month (save 30%)
   - 48 months: $2.99/month (save 40%)
3. Click **"Add to Cart"**

**At checkout:**
- ✅ Use existing domain: Enter **edutrackzambia.com**
- ✅ Or register new domain (if you don't have it yet)

**Payment methods:**
- Credit/Debit Card (Visa, Mastercard)
- PayPal
- Mobile Money (if available in Zambia)

**Total Cost:**
- Business Plan: ~K1,200/year
- Domain (if buying): ~K300/year
- **TOTAL: ~K1,500/year** (K125/month)

---

## Step 2: Initial Hosting Setup

**After purchase, you'll receive:**
1. Welcome email with login details
2. Control panel (hPanel) access
3. Nameserver information

**Login to Hostinger:**
1. Go to: https://hpanel.hostinger.com
2. Enter your credentials
3. You'll see your hosting dashboard

---

## Step 3: Configure Domain & Subdomains

### A) If Domain is at Another Registrar (e.g., GoDaddy, Namecheap)

**Point domain to Hostinger:**
1. Login to your domain registrar
2. Find **DNS/Nameservers** settings
3. Change nameservers to:
   ```
   ns1.dns-parking.com
   ns2.dns-parking.com
   ```
4. Save changes (takes 24-48 hours to propagate)

### B) If Domain is at Hostinger

Already configured! Skip to next step.

### C) Create Subdomains

**In Hostinger hPanel:**

1. Go to **"Domains"** → **"Subdomains"**

2. Create **LMS subdomain:**
   - Subdomain: `lms`
   - Domain: `edutrackzambia.com`
   - Document Root: `/public_html/lms`
   - Click **"Create"**

3. Create **SMS subdomain:**
   - Subdomain: `sms`
   - Domain: `edutrackzambia.com`
   - Document Root: `/public_html/sms`
   - Click **"Create"**

**Result:**
- ✅ lms.edutrackzambia.com → /public_html/lms
- ✅ sms.edutrackzambia.com → /public_html/sms
- ✅ edutrackzambia.com → /public_html

---

## Step 4: Upload Files

### Option A: File Manager (Easy)

**Upload LMS:**
1. In hPanel, click **"File Manager"**
2. Navigate to `/public_html/lms`
3. Click **"Upload Files"**
4. Upload all EduTrack LMS files
5. If you have a zip file:
   - Upload the zip
   - Right-click → **Extract**
   - Move contents to `/public_html/lms`

**Upload SMS:**
1. Navigate to `/public_html/sms`
2. Upload all SMS files
3. Extract if needed

### Option B: FTP (For Large Files)

**FTP Credentials (from Hostinger):**
- Host: `ftp.edutrackzambia.com`
- Username: From hPanel → FTP Accounts
- Password: Set in FTP Accounts
- Port: 21

**Using FileZilla:**
1. Download FileZilla: https://filezilla-project.org
2. Connect with credentials above
3. Upload:
   - EduTrack LMS → `/public_html/lms`
   - SMS → `/public_html/sms`

**File Structure After Upload:**
```
/public_html/
├── index.html (main landing page)
├── lms/
│   ├── public/
│   │   ├── index.php
│   │   ├── courses.php
│   │   └── ...
│   ├── src/
│   ├── config/
│   └── .env
└── sms/
    ├── (your SMS files)
    └── ...
```

---

## Step 5: Create Databases

### Create LMS Database

1. In hPanel, go to **"Databases"** → **"MySQL Databases"**
2. Click **"Create New Database"**
   - Database name: `edutrack_lms`
   - Click **"Create"**
3. **Create Database User:**
   - Username: `edutrack_lms_user`
   - Password: Generate strong password (save it!)
   - Click **"Create"**
4. **Assign User to Database:**
   - Select database: `edutrack_lms`
   - Select user: `edutrack_lms_user`
   - Privileges: **All Privileges**
   - Click **"Add"**

### Create SMS Database

Repeat above for SMS:
- Database: `edutrack_sms`
- User: `edutrack_sms_user`
- Password: (generate and save)

**Note down:**
```
LMS Database:
- DB Host: localhost
- DB Name: edutrack_lms
- DB User: edutrack_lms_user
- DB Pass: [your generated password]

SMS Database:
- DB Host: localhost
- DB Name: edutrack_sms
- DB User: edutrack_sms_user
- DB Pass: [your generated password]
```

---

## Step 6: Import Database

### Import LMS Database

1. In hPanel, go to **"Databases"** → **"phpMyAdmin"**
2. Login (credentials shown on screen)
3. Click database **"edutrack_lms"** in left sidebar
4. Click **"Import"** tab
5. Click **"Choose File"** → Select your SQL dump
6. Scroll down, click **"Go"**
7. Wait for success message

### Import SMS Database

Repeat for SMS database.

---

## Step 7: Configure .env Files

### Configure LMS .env

**Via File Manager:**
1. Navigate to `/public_html/lms/`
2. Find `.env` file (or create from `.env.example`)
3. Right-click → **Edit**

**Update these values:**
```bash
# Application
APP_NAME="Edutrack Computer Training College"
APP_URL=https://lms.edutrackzambia.com
APP_ENV=production
APP_DEBUG=false  # IMPORTANT: false in production!
APP_TIMEZONE=Africa/Lusaka

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=edutrack_lms
DB_USER=edutrack_lms_user
DB_PASS=your-database-password-here
DB_CHARSET=utf8mb4

# Site
SITE_EMAIL=edutrackzambia@gmail.com
SITE_PHONE=+260-979-536-820
SITE_ADDRESS="Kalomo, Zambia"

# Mail (Gmail SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=edutrackzambia@gmail.com
MAIL_PASSWORD=your-app-password-here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=edutrackzambia@gmail.com

# Security Keys (generate new ones!)
ENCRYPTION_KEY=generate-with-openssl-rand-base64-32
JWT_SECRET=generate-with-openssl-rand-base64-64

# Session
SESSION_SECURE=true  # Enable for HTTPS
SESSION_HTTPONLY=true
```

**Save the file**

### Configure SMS .env

Repeat for SMS with appropriate values.

---

## Step 8: Set File Permissions

**In File Manager:**

Set permissions for writable directories:

**For LMS:**
```
/public_html/lms/storage/          → 755
/public_html/lms/storage/logs/     → 755
/public_html/lms/storage/sessions/ → 755
/public_html/lms/public/uploads/   → 755
```

**Right-click folder → Permissions:**
- Owner: Read, Write, Execute (7)
- Group: Read, Execute (5)
- Public: Read, Execute (5)
- = **755**

---

## Step 9: Configure Document Root

**For LMS (Important!):**

The web root should point to `public/` folder:

1. In hPanel, go to **"Advanced"** → **"PHP Configuration"**
2. Find **"Document Root"** setting
3. For `lms.edutrackzambia.com`:
   - Set to: `/public_html/lms/public`
4. For `sms.edutrackzambia.com`:
   - Set to: `/public_html/sms/public` (if SMS uses same structure)

**This ensures:**
- ✅ Only public files are accessible
- ✅ .env and config files are protected
- ✅ Clean URLs (no /public/ in URL)

---

## Step 10: Install SSL Certificates (Free HTTPS)

**In hPanel:**

1. Go to **"Security"** → **"SSL"**
2. You'll see all domains/subdomains:
   - edutrackzambia.com
   - lms.edutrackzambia.com
   - sms.edutrackzambia.com

3. For each, click **"Install SSL"**
4. Choose **"Free SSL (Let's Encrypt)"**
5. Click **"Install"**
6. Wait 5-10 minutes for activation

**Force HTTPS:**

Create/edit `.htaccess` in each root:

**File: /public_html/lms/public/.htaccess**
```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Protect sensitive files
<FilesMatch "^\.env$">
    Order allow,deny
    Deny from all
</FilesMatch>

# PHP Settings
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
```

---

## Step 11: Create Landing Page

**File: /public_html/index.html**

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edutrack Computer Training College - Zambia</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2E70DA 0%, #1E4A8A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            width: 100%;
        }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        .header h1 {
            font-size: 3em;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        .systems {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .system-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        .system-card:hover {
            transform: translateY(-10px);
        }
        .system-card h2 {
            color: #2E70DA;
            font-size: 2em;
            margin-bottom: 20px;
        }
        .system-card p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: #F6B745;
            color: #111;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #D89E2E;
        }
        .footer {
            text-align: center;
            color: white;
            margin-top: 50px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Edutrack Computer Training College</h1>
            <p>Quality Education in Kalomo, Zambia</p>
        </div>

        <div class="systems">
            <div class="system-card">
                <h2>📚 Learning Portal</h2>
                <p>Access courses, watch video lessons, complete assignments, and earn TEVETA-certified certificates.</p>
                <a href="https://lms.edutrackzambia.com" class="btn">Student Login</a>
            </div>

            <div class="system-card">
                <h2>🏫 School Management</h2>
                <p>Manage student records, track attendance, process grades, and handle school administration.</p>
                <a href="https://sms.edutrackzambia.com" class="btn">Staff Login</a>
            </div>
        </div>

        <div class="footer">
            <p>📞 +260-979-536-820 | 📧 edutrackzambia@gmail.com</p>
            <p>Kalomo, Zambia</p>
        </div>
    </div>
</body>
</html>
```

---

## Step 12: Test Everything

### Test LMS
```
✅ Visit: https://lms.edutrackzambia.com
✅ Homepage loads correctly
✅ Login page works
✅ Create test account
✅ Browse courses
✅ Upload test course thumbnail
✅ Send test email
```

### Test SMS
```
✅ Visit: https://sms.edutrackzambia.com
✅ Login works
✅ Dashboard loads
✅ Core features functional
```

### Test Main Site
```
✅ Visit: https://edutrackzambia.com
✅ Both system links work
✅ SSL/HTTPS working (green padlock)
```

---

## Step 13: Post-Deployment Checklist

### Security
- [ ] APP_DEBUG=false in production .env
- [ ] Strong database passwords
- [ ] .env file not publicly accessible
- [ ] SSL certificates installed and working
- [ ] File permissions set correctly (755/644)
- [ ] Delete test files (test-setup.php, test-email.php, check-credentials.php)

### Email
- [ ] Gmail SMTP working
- [ ] Test welcome email
- [ ] Test password reset
- [ ] Test enrollment confirmation

### Backups
- [ ] Enable automated backups in Hostinger
- [ ] Download initial backup locally
- [ ] Schedule weekly backups

### Performance
- [ ] Enable Cloudflare (optional, free CDN)
- [ ] Test page load speeds
- [ ] Optimize images if needed

### DNS
- [ ] Domain resolves correctly
- [ ] All subdomains working
- [ ] HTTPS redirects working

---

## Troubleshooting Common Issues

### "Database connection failed"
**Fix:**
1. Check .env database credentials
2. Verify user has privileges in phpMyAdmin
3. Confirm database exists

### "500 Internal Server Error"
**Fix:**
1. Check file permissions
2. Review .htaccess file
3. Check PHP error logs in hPanel
4. Ensure document root is correct

### "Page not found" or shows directory listing
**Fix:**
1. Set document root to `/public` folder
2. Ensure index.php exists
3. Check .htaccess configuration

### SSL not working
**Fix:**
1. Wait 10-15 minutes after installation
2. Clear browser cache
3. Force HTTPS in .htaccess
4. Check SSL status in hPanel

### Emails not sending
**Fix:**
1. Install PHPMailer: `composer install` via SSH
2. Verify Gmail credentials in .env
3. Check SMTP settings (port 587, TLS)
4. Test with test-email.php before deleting

---

## Maintenance Tasks

### Daily
- Monitor error logs
- Check email notifications working

### Weekly
- Review backup status
- Check disk space usage
- Monitor student enrollments

### Monthly
- Update PHP version if available
- Review security logs
- Database optimization (via phpMyAdmin)

---

## Scaling Up Later

**When you outgrow shared hosting:**

1. **Upgrade Hostinger Plan** (easiest)
   - Business → Cloud Startup
   - More resources, same interface

2. **Move to VPS** (more control)
   - DigitalOcean/Hetzner
   - Full server control
   - Can handle 5,000+ students

3. **Add Services:**
   - Bunny CDN for video delivery
   - Redis for caching
   - Load balancing

---

## Support Resources

**Hostinger:**
- 24/7 Live Chat: https://hpanel.hostinger.com
- Knowledge Base: https://support.hostinger.com
- Video Tutorials: YouTube "Hostinger Tutorials"

**EduTrack:**
- Documentation in project files
- DEPLOYMENT.md
- CREDENTIALS_SETUP.md

**Community:**
- PHP.net Documentation
- Stack Overflow
- GitHub Issues

---

## Cost Summary

```
Initial Setup:
├── Hostinger Business: K1,200/year
├── Domain (if needed): K300/year
└── TOTAL Year 1: K1,500

Ongoing (Annual):
├── Hosting renewal: K1,200
├── Domain renewal: K300
└── TOTAL: K1,500/year (K125/month)

Optional Add-ons:
├── Bunny CDN: K50-200/month (if videos grow)
├── Premium email: Included with Hostinger
└── Backups: Included with Hostinger
```

---

**You're now ready to deploy both systems to edutrackzambia.com!** 🚀

**Next:** Follow steps 1-13 above, and you'll have:
- ✅ Main website at edutrackzambia.com
- ✅ LMS at lms.edutrackzambia.com
- ✅ SMS at sms.edutrackzambia.com
- ✅ All with free SSL (HTTPS)
- ✅ Professional setup for K1,500/year

Good luck with your deployment!
