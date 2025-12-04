# EduTrack LMS - Quick Start Guide for Hostinger

## 🚀 Fast Deployment (30 minutes)

Your Hostinger details:
- **Site**: khaki-dunlin-812469.hostingersite.com
- **Hosting**: Active ✓
- **SSL**: Configure after upload

---

## Step 1: Prepare Database (5 min)

1. **Create Database in Hostinger**
   - Dashboard → **Databases** → **Create new database**
   - Name: `edutrack_lms`
   - Save credentials securely

2. **Import Schema**
   - Click **Manage** → **phpMyAdmin**
   - Select your database
   - **Import** tab
   - Upload: `database/complete_lms_schema.sql`
   - Wait for 44 tables to create

---

## Step 2: Configure Settings (5 min)

1. **Generate Security Keys**
   ```bash
   # On your computer:
   openssl rand -base64 32    # Copy this for ENCRYPTION_KEY
   openssl rand -base64 64    # Copy this for JWT_SECRET
   ```

2. **Setup Environment File**
   ```bash
   cp .env.hostinger .env
   ```

3. **Edit .env and fill in**:
   ```env
   # Database (from Step 1)
   DB_NAME="your_database_name"
   DB_USER="your_database_user"
   DB_PASS="your_database_password"

   # Security (from Step 2.1)
   ENCRYPTION_KEY="paste-32-char-key-here"
   JWT_SECRET="paste-64-char-key-here"

   # Domain
   APP_URL="https://khaki-dunlin-812469.hostingersite.com"
   ```

---

## Step 3: Upload Files (10 min)

### Option A: FTP (Faster)
1. Get FTP credentials: Dashboard → **Files** → **FTP Accounts**
2. Use FileZilla:
   - Host: Your FTP host
   - Username: Your FTP username
   - Password: Your FTP password
3. Upload entire project to `public_html/`

### Option B: File Manager
1. Dashboard → **Files** → **File Manager**
2. Create ZIP of your project
3. Upload ZIP to `public_html/`
4. Right-click → **Extract**

---

## Step 4: Install Dependencies (5 min)

### If you have SSH:
```bash
ssh your-username@your-host
cd public_html
composer install --no-dev --optimize-autoloader
```

### No SSH? Install locally first:
```bash
# On your computer:
composer install --no-dev --optimize-autoloader
# Then upload the /vendor/ folder
```

---

## Step 5: Configure Hosting (3 min)

1. **Set Web Root**
   - Dashboard → **Website** → **Advanced**
   - **Document Root**: Change to `public_html/public`
   - Save

2. **Configure PHP** (Advanced → PHP Configuration)
   ```ini
   upload_max_filesize = 50M
   post_max_size = 50M
   max_execution_time = 300
   memory_limit = 256M
   ```

3. **Enable SSL**
   - Dashboard → **Security** → **SSL**
   - Enable **Free SSL**
   - Wait 5-15 minutes

---

## Step 6: Set Permissions (2 min)

In File Manager, set these to **755**:
- `/storage/logs/`
- `/storage/cache/`
- `/storage/sessions/`
- `/public/uploads/`

---

## Step 7: Create Admin Account (2 min)

1. Go to phpMyAdmin
2. Select `users` table → **Insert**
3. Fill in:
   ```
   username: admin
   email: your-email@domain.com
   password: [See below for hash]
   role: admin
   status: active
   ```

4. **Generate Password Hash**:
   - Create temporary file: `public/hash.php`
   ```php
   <?php echo password_hash('YourPassword123!', PASSWORD_DEFAULT); ?>
   ```
   - Visit: yourdomain.com/hash.php
   - Copy the hash
   - Paste in password field
   - **Delete hash.php immediately!**

---

## Step 8: Test! (3 min)

Visit your site:
```
https://khaki-dunlin-812469.hostingersite.com
```

Test these:
- [ ] Homepage loads
- [ ] Login works (admin account)
- [ ] Dashboard accessible
- [ ] No errors in browser console

---

## 🎉 You're Live!

### Next Steps:

1. **Setup Email**
   - Dashboard → **Emails** → Create account
   - Update `.env`:
     ```env
     MAIL_HOST="smtp.hostinger.com"
     MAIL_USERNAME="your-email@domain.com"
     MAIL_PASSWORD="your-password"
     ```

2. **Add Content**
   - Login as admin
   - Create course categories
   - Add your first course

3. **Connect Custom Domain** (Optional)
   - Purchase domain
   - Point DNS to Hostinger
   - Update `APP_URL` in `.env`

---

## Troubleshooting

**500 Error?**
- Check `.env` exists and is configured
- Verify database credentials
- Check `/storage/logs/` for errors

**Blank Page?**
- Enable errors temporarily in `.env`:
  ```env
  APP_DEBUG=true
  ```
- Check error logs

**CSS Not Loading?**
- Verify web root is `public_html/public`
- Check `.htaccess` exists in `/public/`
- Clear browser cache

**Database Connection Failed?**
- Double-check credentials in `.env`
- Test in phpMyAdmin first
- Ensure `DB_HOST="localhost"`

---

## Need Detailed Help?

See full guides:
- **HOSTINGER_DEPLOYMENT_GUIDE.md** - Complete instructions
- **DEPLOYMENT_CHECKLIST.md** - Step-by-step checklist

---

## Support

**Hostinger:**
- Live chat: 24/7 in dashboard
- Docs: https://support.hostinger.com

**Questions?**
Check error logs in `/storage/logs/`

---

**Deployment time:** ~30 minutes
**Difficulty:** Beginner-friendly

Good luck! 🚀
