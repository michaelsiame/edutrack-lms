# Installing PHPMailer for Email Support

## The Problem
Your Email class needs PHPMailer to send emails via Gmail SMTP. Without it, it tries to use PHP's `mail()` function which doesn't work on Windows XAMPP.

## The Solution
Install PHPMailer using Composer.

---

## Step 1: Check if Composer is Installed

**On Windows (XAMPP):**
Open Command Prompt or Git Bash and run:
```bash
composer --version
```

**If you see version info** (e.g., "Composer version 2.x.x"), skip to Step 3.

**If you get an error** ("composer not found"), continue to Step 2.

---

## Step 2: Install Composer (Windows)

### Option A: Download Installer (Easiest)
1. Go to: https://getcomposer.org/download/
2. Download **Composer-Setup.exe**
3. Run the installer
4. Follow the prompts (it will auto-detect PHP from XAMPP)
5. Restart your Command Prompt after installation

### Option B: Manual Installation
1. Download: https://getcomposer.org/composer.phar
2. Save to `C:\xampp\php\composer.phar`
3. Create `C:\xampp\php\composer.bat` with this content:
   ```bat
   @echo OFF
   php "%~dp0composer.phar" %*
   ```
4. Add `C:\xampp\php` to your system PATH

---

## Step 3: Install PHPMailer

**Navigate to your project:**
```bash
cd C:\xampp\htdocs\edutrack-lms
```

**Install dependencies:**
```bash
composer install
```

You should see:
```
Installing dependencies from lock file
  - Installing phpmailer/phpmailer (v6.8.x)
  - Installing tecnickcom/tcpdf (6.6.x)
Generating autoload files
```

---

## Step 4: Verify Installation

Check that vendor directory was created:
```bash
dir vendor
```

You should see folders like:
- `vendor/phpmailer/`
- `vendor/tecnickcom/`
- `vendor/autoload.php`

---

## Step 5: Test Email Sending

**Start your PHP server:**
```bash
cd C:\xampp\htdocs\edutrack-lms
php -S localhost:8000 -t public/
```

**Visit test page:**
```
http://localhost:8000/test-setup.php
```

Scroll to the Email Configuration section and click **"Send Test Email"**.

If PHPMailer is installed correctly, you should see:
- ✅ Email Credentials Configured
- Test email form appears
- Email sends successfully using Gmail SMTP

---

## Alternative: Quick Fix Without Composer

If you can't install Composer right now, you can temporarily use a simple workaround:

**Create this file:** `src/vendor/autoload.php`

But I **strongly recommend** using Composer for proper dependency management.

---

## Common Issues

### "composer not recognized as command"
**Fix:** Restart Command Prompt after installing Composer, or use full path:
```bash
C:\xampp\php\composer.bat install
```

### "Your requirements could not be resolved"
**Fix:** Make sure you have PHP 8.0 or higher:
```bash
php -v
```

If PHP is older, download PHP 8.1 from https://windows.php.net/download/

### "proc_open() has been disabled"
**Fix:** Edit `C:\xampp\php\php.ini` and remove `proc_open` from `disable_functions`

---

## What Happens After Installation

Once PHPMailer is installed:

1. **Email class will use SMTP** (not mail() function)
2. **Gmail credentials will work** from your .env file
3. **All email features will work:**
   - Welcome emails
   - Password resets
   - Enrollment confirmations
   - Certificate delivery
   - Payment receipts

---

## For Linux/Mac Users

**Install Composer:**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**Install dependencies:**
```bash
cd /path/to/edutrack-lms
composer install
```

---

## Next Steps After PHPMailer is Installed

1. ✅ Run `test-setup.php` to verify configuration
2. ✅ Send test email to yourself
3. ✅ Test user registration (should send welcome email)
4. ✅ Test password reset (should send reset link)
5. ✅ Configure other features as needed

---

## Need Help?

If you encounter issues:
1. Check PHP version: `php -v` (must be 8.0+)
2. Check Composer version: `composer --version`
3. Try: `composer clear-cache` then `composer install` again
4. Check XAMPP error logs: `C:\xampp\apache\logs\error.log`

---

**Once PHPMailer is installed, your Gmail SMTP will work perfectly!** 🎉
