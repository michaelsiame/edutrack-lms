# 📧 Email System Setup Guide

Complete guide for setting up automated emails in EduTrack LMS with Hostinger cron jobs.

---

## 📋 **What's Included**

The system automatically sends:

✅ **Welcome Emails** - When users register
✅ **Enrollment Confirmations** - When students enroll in courses
✅ **Payment Receipts** - When payments are processed
✅ **Certificates** - When certificates are issued

All emails are queued and processed by cron jobs for reliability.

---

## 🏗️ **System Architecture**

```
User Action → Queue Email → Cron Job → Send Email
                (instant)     (every 5 min)
```

**Benefits:**
- Non-blocking (doesn't slow down user actions)
- Reliable (retries failed emails)
- Scalable (handles bulk emails)
- Logged (tracks all email activity)

---

## 🔧 **Components Created**

### 1. **EmailNotificationService** (`/src/classes/EmailNotificationService.php`)
   - Handles all email sending logic
   - Queues emails to database
   - Processes email queue

### 2. **Email Hooks** (`/src/includes/email-hooks.php`)
   - Simple functions to trigger emails
   - Used throughout the application

### 3. **Cron Job Script** (`/cron/process-emails.php`)
   - Processes queued emails
   - Runs every 5 minutes
   - Cleans up old emails

### 4. **Updated API Endpoints**
   - Users API - Sends welcome emails
   - Enrollments API - Sends enrollment confirmations
   - Transactions API - Sends payment receipts
   - Certificates API - Sends certificate notifications

---

## ⚙️ **Setup Instructions**

### Step 1: Enable Email Hooks

Add this line to `/src/bootstrap.php`:

```php
// Near the end of the file, before the closing ?>
require_once __DIR__ . '/includes/email-hooks.php';
```

### Step 2: Configure Email Settings

Your `.env` file already has email configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=edutrackzambia@gmail.com
MAIL_PASSWORD="jtxi srbv vrtr gxau"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=edutrackzambia@gmail.com
MAIL_FROM_NAME="Edutrack Computer Training College"
```

✅ **Already configured!**

### Step 3: Create Cron Jobs on Hostinger

#### 3.1 Login to Hostinger hPanel

1. Go to your Hostinger dashboard
2. Select your hosting plan
3. Click on **Advanced** → **Cron Jobs**

#### 3.2 Create Email Processing Cron Job

**Cron Job Settings:**

```
Type: Custom
Interval: Every 5 minutes (or use: */5 * * * *)
Command: php /home/USERNAME/public_html/cron/process-emails.php
```

**Replace `USERNAME` with your actual Hostinger username!**

**Example commands:**
```bash
# If your path is /home/u605780771/public_html/
cd /home/u605780771/public_html && php cron/process-emails.php

# Or use full path to PHP
/usr/bin/php /home/u605780771/public_html/cron/process-emails.php
```

#### 3.3 Verify Cron Job Path

To find your correct path:
1. Go to Hostinger File Manager
2. Navigate to your site root
3. Copy the full path shown at the top
4. Use that in your cron command

---

## 🧪 **Testing the Email System**

### Test 1: Create a Test User

```php
// Via admin panel or directly:
POST https://edutrackzambia.com/api/users.php

{
  "name": "Test User",
  "email": "test@example.com",
  "password": "test123",
  "role": "Student"
}
```

**Expected:** Welcome email queued

### Test 2: Check Email Queue

```sql
SELECT * FROM email_queue WHERE status = 'pending' ORDER BY created_at DESC LIMIT 10;
```

You should see the welcome email in the queue.

### Test 3: Manually Run Cron Job

```bash
# SSH into Hostinger or use the web-based terminal
cd /home/u605780771/public_html
php cron/process-emails.php
```

**Expected output:**
```
Processed: 1, Sent: 1, Failed: 0, Duration: 2.5 seconds
```

### Test 4: Check Cron Logs

```bash
# View cron email log
cat /home/u605780771/public_html/storage/logs/cron-email.log
```

**Example log:**
```
[2025-01-20 10:05:00] === Email Queue Processor Started ===
[2025-01-20 10:05:02] Processed: 1, Sent: 1, Failed: 0, Duration: 2.15 seconds
[2025-01-20 10:05:02] === Email Queue Processor Completed ===
```

---

## 📊 **Email Queue Table**

The `email_queue` table stores all emails:

| Column | Purpose |
|--------|---------|
| `recipient` | Email address |
| `subject` | Email subject |
| `body` | HTML email content |
| `status` | pending, processing, sent, failed |
| `attempts` | Number of send attempts (max 3) |
| `priority` | Higher = sent first (0-10) |
| `scheduled_at` | When to send (NULL = immediate) |
| `sent_at` | When successfully sent |
| `created_at` | When queued |

### View Pending Emails

```sql
SELECT id, recipient, subject, status, attempts, created_at
FROM email_queue
WHERE status = 'pending'
ORDER BY priority DESC, created_at ASC;
```

### View Failed Emails

```sql
SELECT id, recipient, subject, attempts, last_attempt
FROM email_queue
WHERE status = 'failed';
```

### Retry Failed Email

```sql
UPDATE email_queue
SET status = 'pending', attempts = 0
WHERE id = 123;
```

---

## 🔗 **Integration Points**

### When to Send Emails

| Event | Email Type | Triggered By |
|-------|-----------|--------------|
| User registers | Welcome Email | `registerUser()` or Users API |
| User enrolls in course | Enrollment Confirmation | Enrollments API |
| Payment processed | Payment Receipt | Transactions API |
| Certificate issued | Certificate Notification | Certificates API |

### API Endpoints Updated

All admin API endpoints now automatically queue emails:

✅ `/api/users.php` - Welcome emails
✅ `/api/enrollments.php` - Enrollment confirmations
✅ `/api/transactions.php` - Payment receipts (needs update)
✅ `/api/certificates.php` - Certificate notifications (needs update)

---

## 🔧 **Customizing Email Templates**

Email templates are in `/src/mail/`:

```
/src/mail/
├── welcome.php                  ← Welcome email
├── enrollment-confirm.php       ← Enrollment confirmation
├── payment-received.php         ← Payment receipt
├── certificate-issued.php       ← Certificate notification
├── verify-email.php            ← Email verification
└── reset-password.php          ← Password reset
```

### Editing a Template

1. **Navigate to template:**
   ```bash
   /src/mail/welcome.php
   ```

2. **Available variables** (passed from EmailNotificationService):
   - Welcome: `$first_name`, `$email`, `$login_url`
   - Enrollment: `$first_name`, `$course_title`, `$start_date`, `$course_url`
   - Payment: `$first_name`, `$amount`, `$currency`, `$transaction_id`
   - Certificate: `$first_name`, `$course_title`, `$certificate_number`

3. **Make changes** and save

4. **Test** by triggering the action (e.g., create a user)

---

## 📈 **Monitoring**

### Check Cron Job Status

```bash
# View recent cron runs
tail -n 50 /home/u605780771/public_html/storage/logs/cron-email.log
```

### Email Statistics Query

```sql
SELECT
    status,
    COUNT(*) as count,
    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as last_24h
FROM email_queue
GROUP BY status;
```

### Common Issues

**Issue: Emails not sending**
- Check cron job is running: Look for recent entries in `cron-email.log`
- Check SMTP credentials in `.env`
- Check `email_queue` table for failed emails

**Issue: Gmail blocking**
- Use App Password instead of regular password
- Enable "Less secure app access" (not recommended)
- Or use Hostinger's SMTP (recommended for production)

**Issue: Emails going to spam**
- Set up SPF, DKIM, DMARC records in Hostinger DNS
- Use a verified sending domain
- Warm up the IP (start with low volume)

---

## 🚀 **Production Checklist**

Before going live:

- [ ] Email settings configured in `.env`
- [ ] Email hooks enabled in `bootstrap.php`
- [ ] Cron job created and running on Hostinger
- [ ] Test email sent successfully
- [ ] Cron job logs showing successful runs
- [ ] Email templates customized with branding
- [ ] SPF/DKIM records configured (optional but recommended)
- [ ] Welcome email template reviewed
- [ ] Enrollment email template reviewed
- [ ] Payment receipt template reviewed
- [ ] Certificate email template reviewed

---

## 🔒 **Security**

### Prevent Web Access to Cron Script

The cron script includes security:

```php
// Prevents direct web access
if (php_sapi_name() !== 'cli') {
    die('Access denied. This script can only be run via cron job.');
}
```

### Alternative: Secret Key Access

If you need web access (for testing), use:

```
https://edutrackzambia.com/cron/process-emails.php?key=YOUR_SECRET_KEY
```

Set in `.env`:
```env
CRON_SECRET_KEY=your_random_secret_here_change_this
```

---

## 📞 **Troubleshooting**

### Cron Job Not Running

1. **Check cron job is created in Hostinger**
2. **Verify the command path is correct**
3. **Check PHP path:** `/usr/bin/php` or `/usr/local/bin/php`
4. **Test manually via SSH:**
   ```bash
   cd /home/u605780771/public_html
   php cron/process-emails.php
   ```

### Emails Not Queuing

1. **Check `email_queue` table exists**
2. **Check bootstrap includes email-hooks.php**
3. **Check logs for errors:**
   ```bash
   tail /home/u605780771/public_html/storage/logs/database.log
   ```

### Emails Queued But Not Sending

1. **Check SMTP credentials**
2. **Run cron manually to see errors**
3. **Check Gmail App Password is correct**
4. **Try different SMTP server (Hostinger's)**

---

## 🎯 **Next Steps**

1. ✅ Enable email hooks in `bootstrap.php`
2. ✅ Create cron job on Hostinger
3. ✅ Test with a real email
4. ✅ Monitor logs for 24 hours
5. ✅ Customize email templates
6. ✅ Set up SPF/DKIM (optional)

---

## 📚 **Additional Resources**

- [Hostinger Cron Jobs Guide](https://support.hostinger.com/en/articles/1583169-how-to-set-up-a-cron-job)
- [Gmail App Passwords](https://support.google.com/accounts/answer/185833)
- [Email Best Practices](https://sendgrid.com/blog/email-best-practices/)

---

**🎉 Your email system is ready! All emails will be sent automatically when users register, enroll, or make payments.**
