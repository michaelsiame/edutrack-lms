# Email Configuration Guide for Edutrack LMS

## Current Status

Your email system is currently in **development mode**, which means:
- ✅ Emails are **logged** to the error log instead of being sent
- ✅ Payment approvals and other operations **won't fail** due to email errors
- ⚠️ **No actual emails** are being sent to users

## Production Email Setup

To send real emails in production, you have **two options**:

### Option 1: Install PHPMailer (Recommended)

PHPMailer provides robust SMTP support and works with your Gmail configuration.

**Steps:**

1. **Install Composer** (if not already installed):
   - Download from: https://getcomposer.org/download/
   - Or use XAMPP's composer if available

2. **Navigate to your project directory:**
   ```bash
   cd C:\xampp\htdocs\edutrack-lms
   ```

3. **Install PHPMailer:**
   ```bash
   composer require phpmailer/phpmailer
   ```

4. **Verify installation:**
   - Check that `vendor/` directory exists
   - Check that `vendor/autoload.php` exists

5. **Test email sending:**
   - Try approving a payment in the admin panel
   - Check Apache error logs for successful email confirmation

**Your Gmail SMTP settings are already configured in `.env`:**
```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=edutrackzambia@gmail.com
MAIL_PASSWORD=jtxi srbv vrtr gxau
MAIL_ENCRYPTION=tls
```

**Important Gmail Note:**
- The password `jtxi srbv vrtr gxau` appears to be an App Password (which is correct)
- Make sure 2-Step Verification is enabled on your Gmail account
- App Passwords are the recommended way to use Gmail SMTP

### Option 2: Configure PHP's Native mail() Function

If you can't use Composer, configure XAMPP's mail settings:

**Steps:**

1. **Open `php.ini`** (usually at `C:\xampp\php\php.ini`)

2. **Find and update these settings:**
   ```ini
   [mail function]
   SMTP = smtp.gmail.com
   smtp_port = 587
   sendmail_from = edutrackzambia@gmail.com
   sendmail_path = "C:\xampp\sendmail\sendmail.exe -t"
   ```

3. **Configure sendmail** (`C:\xampp\sendmail\sendmail.ini`):
   ```ini
   [sendmail]
   smtp_server=smtp.gmail.com
   smtp_port=587
   auth_username=edutrackzambia@gmail.com
   auth_password=jtxi srbv vrtr gxau
   force_sender=edutrackzambia@gmail.com
   ```

4. **Restart Apache**

**Note:** Option 1 (PHPMailer) is more reliable and easier to configure.

## How It Works Now (Development Mode)

When an email is triggered (e.g., payment approval), the system:

1. **Detects development mode** (from `APP_DEBUG=true` in `.env`)
2. **Logs the email details** to Apache error log:
   ```
   === EMAIL SENT (DEV MODE) ===
   To: student@example.com
   Subject: Payment Approved - Enrollment Confirmed
   Body Preview: ...
   Note: Install PHPMailer via 'composer install' for actual email sending
   ```
3. **Returns success** so the payment approval completes normally

## Checking Email Logs

**Apache Error Log Location:**
- Windows/XAMPP: `C:\xampp\apache\logs\error.log`
- Look for lines starting with `=== EMAIL SENT (DEV MODE) ===`

**Example log entry:**
```
[Wed Nov 05 15:15:28.023093 2025] [php:notice] [pid 8240:tid 2000]
=== EMAIL SENT (DEV MODE) ===
To: siamem570@gmail.com
Subject: Payment Approved - Enrollment Confirmed
Body Preview: Name: John Doe, Course Title: Web Development...
```

## Testing Email in Production

Once you've installed PHPMailer:

1. **Approve a test payment** in admin panel
2. **Check recipient's inbox** for the confirmation email
3. **Check spam folder** if not in inbox
4. **Review Apache logs** for any errors

## Troubleshooting

### PHPMailer Not Working

Check error logs for specific errors:
```
Email: PHPMailer initialization failed, using fallback: [error message]
```

Common issues:
- **Gmail blocking:** Enable "Less secure app access" or use App Password
- **Firewall:** Port 587 might be blocked
- **Wrong credentials:** Double-check username/password in `.env`

### Gmail Specific Issues

If Gmail SMTP fails:
1. Verify 2-Step Verification is enabled
2. Generate a new App Password at: https://myaccount.google.com/apppasswords
3. Update `MAIL_PASSWORD` in `.env` with the new App Password
4. Remove spaces from the App Password (e.g., `jtxi srbv vrtr gxau` → `jtxisrbvvrtrgxau`)

### Still Having Issues?

Set `APP_DEBUG=true` in `.env` to see detailed error messages in logs.

## Production Checklist

Before going live:

- [ ] Install PHPMailer via Composer
- [ ] Test email sending with a real email address
- [ ] Verify emails don't go to spam
- [ ] Set `APP_ENV=production` in `.env` (when ready)
- [ ] Keep `APP_DEBUG=false` in production (for security)

## Current Configuration Summary

- **Mode:** Development (emails logged, not sent)
- **SMTP Provider:** Gmail (configured but not active)
- **Email Address:** edutrackzambia@gmail.com
- **Next Step:** Install PHPMailer to enable email sending

---

**Need Help?**
Check Apache error logs at: `C:\xampp\apache\logs\error.log`
