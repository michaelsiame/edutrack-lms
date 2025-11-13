# EduTrack LMS - Credentials & External Services Setup Guide

## Table of Contents
1. [YouTube Video Integration](#1-youtube-video-integration)
2. [Email Configuration (Gmail SMTP)](#2-email-configuration-gmail-smtp)
3. [Payment Gateway Setup](#3-payment-gateway-setup)
4. [Social Media Links](#4-social-media-links)
5. [Optional Services](#5-optional-services)
6. [Security Configuration](#6-security-configuration)
7. [Testing](#7-testing)

---

## 1. YouTube Video Integration

### Why YouTube?
- ✅ **FREE** unlimited storage
- ✅ **FREE** unlimited bandwidth
- ✅ Reliable global streaming
- ✅ Mobile-optimized playback
- ✅ No server load
- ✅ Professional video player

### Setup Steps:

#### Step 1: Create YouTube Channel
1. Go to [YouTube.com](https://youtube.com)
2. Sign in with your Google account
3. Click on your profile icon → **Create a channel**
4. Choose **Business/Brand** name: "Edutrack Computer Training"
5. Complete channel setup

#### Step 2: Upload Course Videos
1. Click **Create** → **Upload video**
2. Select your video file
3. Fill in details:
   - **Title**: Clear, descriptive title
   - **Description**: Course overview
   - **Visibility**: Choose **Unlisted** (recommended)
     - **Unlisted**: Only people with link can view (recommended for course videos)
     - **Public**: Anyone can find and watch
     - **Private**: Only you and specific people can watch

4. Click **Save**

#### Step 3: Get Video Embed Link
**Method 1: Direct YouTube URL** (Easiest)
```
1. Go to your video on YouTube
2. Copy the URL from browser address bar
   Example: https://www.youtube.com/watch?v=dQw4w9WgXcQ
3. Paste this URL directly in your course's "Promo Video URL" field
```

**Method 2: Embed Code**
```
1. Click "Share" under video
2. Click "Embed"
3. Copy the entire URL inside src="..."
4. Paste in course promo_video_url field
```

#### Step 4: Using Videos in EduTrack
```php
// The app automatically handles YouTube URLs!
// Just paste the YouTube URL in the database:

Course Table:
├── promo_video_url: "https://youtube.com/watch?v=VIDEO_ID"

// The helper function getVideoEmbed() automatically:
// - Detects YouTube URL
// - Extracts video ID
// - Creates responsive embed
```

#### Example: Adding Promo Video to Course
```sql
UPDATE courses
SET promo_video_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
WHERE id = 1;
```

Or via Instructor Dashboard:
1. Go to **Instructor → Courses → Edit Course**
2. Find "Promotional Video URL" field
3. Paste YouTube URL: `https://www.youtube.com/watch?v=VIDEO_ID`
4. Save

### YouTube API Key (Optional - Advanced Features)

**When do you need it?**
- Auto-fetch video duration
- Get video thumbnails automatically
- Display view counts
- Verify video availability

**How to get it:**
```
1. Go to: https://console.cloud.google.com
2. Create new project: "EduTrack LMS"
3. Enable APIs & Services → "YouTube Data API v3"
4. Credentials → Create Credentials → API Key
5. Copy API key
6. Add to .env:
   YOUTUBE_API_KEY="AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
```

**Important:** Restrict your API key!
```
1. Edit API key
2. Application restrictions: HTTP referrers
3. Add your domain: https://yourdomain.com/*
4. API restrictions: YouTube Data API v3
5. Save
```

---

## 2. Email Configuration (Gmail SMTP)

### Why Configure Email?
- Send welcome emails to new users
- Password reset links
- Enrollment confirmations
- Certificate delivery
- Payment receipts
- Assignment notifications

### Gmail SMTP Setup (FREE)

#### Step 1: Enable 2-Factor Authentication
```
1. Go to: https://myaccount.google.com/security
2. Find "2-Step Verification"
3. Click "Get Started"
4. Follow the prompts to enable 2FA
```

#### Step 2: Generate App Password
```
1. Go to: https://myaccount.google.com/apppasswords
2. In "Select app": Choose "Mail"
3. In "Select device": Choose "Other (custom name)"
4. Enter: "EduTrack LMS"
5. Click "Generate"
6. Copy the 16-character password (looks like: xxxx xxxx xxxx xxxx)
```

#### Step 3: Update .env File
```bash
MAIL_MAILER="smtp"
MAIL_HOST="smtp.gmail.com"
MAIL_PORT=587
MAIL_ENCRYPTION="tls"
MAIL_USERNAME="your-email@gmail.com"
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"    # Your App Password (remove spaces)
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Edutrack LMS"
```

#### Step 4: Test Email
```php
// Test via PHP:
<?php
require_once 'src/bootstrap.php';
sendEmail('test@example.com', 'Test Email', 'This is a test email from EduTrack!');
?>
```

### Alternative Email Providers

#### Mailgun (Recommended for Production)
```
Free: 5,000 emails/month
Cost: $0.80 per 1,000 emails after

Setup:
1. Sign up: https://mailgun.com
2. Add domain and verify DNS
3. Get SMTP credentials
4. Update .env:
   MAIL_HOST="smtp.mailgun.org"
   MAIL_USERNAME="postmaster@yourdomain.com"
   MAIL_PASSWORD="your-mailgun-password"
```

#### SendGrid
```
Free: 100 emails/day
Cost: $15/month for 40,000 emails

Setup:
1. Sign up: https://sendgrid.com
2. Create API key
3. Update .env:
   MAIL_HOST="smtp.sendgrid.net"
   MAIL_USERNAME="apikey"
   MAIL_PASSWORD="SG.your-api-key"
```

---

## 3. Payment Gateway Setup

### Zambian Mobile Money Integration

#### MTN Mobile Money

**Sign Up:**
1. Visit: https://momodeveloper.mtn.com
2. Create developer account
3. Create new app
4. Subscribe to Collections API

**Configuration:**
```bash
MTN_API_URL="https://api.mtn.com/collection"
MTN_API_KEY="your-api-key"
MTN_API_SECRET="your-api-secret"
MTN_SUBSCRIPTION_KEY="your-subscription-key"
```

**Test Mode:**
- Use sandbox credentials first
- Test with: Sandbox MSISDN: 46733123453

**Go Live:**
- Complete KYC verification
- Get production credentials
- Update .env with production keys

#### Airtel Money

**Sign Up:**
1. Visit: https://openapi.airtel.africa
2. Register as merchant
3. Submit business documents
4. Get API credentials

**Configuration:**
```bash
AIRTEL_API_URL="https://openapi.airtel.africa"
AIRTEL_CLIENT_ID="your-client-id"
AIRTEL_CLIENT_SECRET="your-client-secret"
```

#### Zamtel Kwacha

**Sign Up:**
1. Contact Zamtel Business Services: +260-211-254000
2. Request API integration
3. Complete merchant registration

**Configuration:**
```bash
ZAMTEL_API_URL="provided-by-zamtel"
ZAMTEL_MERCHANT_ID="your-merchant-id"
ZAMTEL_API_KEY="your-api-key"
```

### Bank Transfer (Manual Payment)

**Update bank details in:**
`config/payment.php` → `bank_transfer` → `banks` array

```php
'banks' => [
    [
        'name' => 'Zanaco',
        'account_name' => 'Your Institution Name',
        'account_number' => 'YOUR-ACCOUNT-NUMBER',
        'branch' => 'Cairo Road Branch',
        'swift_code' => 'ZANAZMLX'
    ]
]
```

---

## 4. Social Media Links

### Setup Social Media Presence

#### Facebook Page
```
1. Create Facebook Business Page
2. Go to page settings → Copy page URL
3. Add to .env:
   FACEBOOK_URL="https://facebook.com/yourpage"
```

#### YouTube Channel
```
1. Use same channel from video setup
2. Get channel URL from YouTube Studio
3. Add to .env:
   YOUTUBE_URL="https://youtube.com/c/yourchannel"
```

#### Twitter/X
```
1. Create Twitter account
2. Copy profile URL
3. Add to .env:
   TWITTER_URL="https://twitter.com/yourhandle"
```

#### Instagram
```
1. Create Instagram Business account
2. Link to Facebook page
3. Add to .env:
   INSTAGRAM_URL="https://instagram.com/yourhandle"
```

---

## 5. Optional Services

### Google Analytics (Website Traffic)
```
1. Go to: https://analytics.google.com
2. Create new property
3. Get Measurement ID (G-XXXXXXXXXX)
4. Add to .env:
   GOOGLE_ANALYTICS_ID="G-XXXXXXXXXX"
```

### Facebook Pixel (Marketing)
```
1. Go to: https://business.facebook.com/events_manager
2. Create pixel
3. Get Pixel ID
4. Add to .env:
   FACEBOOK_PIXEL_ID="XXXXXXXXXXXXXXXX"
```

### Bunny CDN (Video Hosting Alternative)
```
Cost: €1/month + bandwidth
Best for: Self-hosted video streaming

1. Sign up: https://bunny.net
2. Create storage zone
3. Create stream library
4. Get credentials:
   BUNNY_CDN_URL="your-stream-url.b-cdn.net"
   BUNNY_API_KEY="your-api-key"
```

---

## 6. Security Configuration

### Generate Security Keys

#### Encryption Key
```bash
# On Linux/Mac:
openssl rand -base64 32

# Output example:
GF8jK3nN9mP2qR5tU7vW0xY1zA3bC4dE5fG6hH8iI9jJ

# Add to .env:
ENCRYPTION_KEY="GF8jK3nN9mP2qR5tU7vW0xY1zA3bC4dE5fG6hH8iI9jJ"
```

#### JWT Secret
```bash
# On Linux/Mac:
openssl rand -base64 64

# Add to .env:
JWT_SECRET="your-generated-jwt-secret"
```

### SSL Certificate (Production)

**Free SSL with Let's Encrypt:**
```bash
# On VPS/Server:
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

**Update .env for Production:**
```bash
APP_ENV="production"
APP_DEBUG=false
SESSION_SECURE=true     # Requires HTTPS
```

---

## 7. Testing

### Test Checklist

#### ✅ Database Connection
```php
// Test in: public/test-db.php
<?php
require_once '../src/bootstrap.php';
$db = Database::getInstance();
echo "Database connected successfully!";
?>
```

#### ✅ Email Sending
```php
// Test in: public/test-email.php
<?php
require_once '../src/bootstrap.php';
$result = sendEmail('your-email@gmail.com', 'Test', 'Testing email configuration');
echo $result ? 'Email sent!' : 'Email failed!';
?>
```

#### ✅ YouTube Video Embedding
```
1. Add YouTube URL to any course promo_video_url
2. View course page
3. Video should embed and play correctly
```

#### ✅ File Upload
```
1. Login as instructor
2. Create new course
3. Upload thumbnail image
4. Should save to: public/uploads/courses/thumbnails/
```

#### ✅ Payment Gateway (Test Mode)
```
1. Use sandbox/test credentials
2. Enroll in paid course
3. Process test payment
4. Verify payment recorded in database
```

---

## Production Deployment Checklist

### Before Going Live:

- [ ] Database credentials configured
- [ ] Email SMTP configured and tested
- [ ] YouTube channel created with course videos
- [ ] Payment gateways configured (if accepting payments)
- [ ] Social media links added
- [ ] Security keys generated
- [ ] SSL certificate installed
- [ ] APP_ENV set to "production"
- [ ] APP_DEBUG set to false
- [ ] Strong admin password set
- [ ] File permissions configured (755 for dirs, 644 for files)
- [ ] Backup strategy configured
- [ ] Domain DNS configured
- [ ] Test all critical flows:
  - [ ] User registration
  - [ ] Email notifications
  - [ ] Course enrollment
  - [ ] Video playback
  - [ ] Payment processing
  - [ ] Certificate generation

---

## Quick Reference

### Essential Credentials Priority:

**Priority 1 (Must Have):**
1. ✅ Database credentials
2. ✅ Site email address
3. ✅ Admin login credentials

**Priority 2 (Recommended):**
4. ✅ Gmail SMTP (for email notifications)
5. ✅ YouTube channel (for course videos)
6. ✅ Social media links

**Priority 3 (Optional):**
7. ⭕ Payment gateway credentials
8. ⭕ Google Analytics
9. ⭕ YouTube API key

### Minimum Setup for Development:
```bash
# .env file (minimum)
DB_NAME="edutrack_lms"
DB_USER="root"
DB_PASS=""
SITE_EMAIL="admin@example.com"
APP_URL="http://localhost/edutrack-lms/public"
```

### Minimum Setup for Production:
```bash
# .env file (production minimum)
DB_NAME="edutrack_lms"
DB_USER="edutrack_user"
DB_PASS="strong-password-here"
SITE_EMAIL="info@yourdomain.com"
APP_URL="https://yourdomain.com"
MAIL_USERNAME="your-email@gmail.com"
MAIL_PASSWORD="your-app-password"
YOUTUBE_URL="https://youtube.com/c/yourchannel"
ENCRYPTION_KEY="generated-key-here"
JWT_SECRET="generated-secret-here"
APP_ENV="production"
APP_DEBUG=false
SESSION_SECURE=true
```

---

## Support & Resources

### Documentation Links:
- YouTube API: https://developers.google.com/youtube/v3
- Gmail SMTP: https://support.google.com/mail/answer/7126229
- MTN MoMo: https://momodeveloper.mtn.com/documentation
- Let's Encrypt: https://letsencrypt.org/getting-started/

### Common Issues:

**"Email sending failed"**
- Check Gmail App Password is correct (no spaces)
- Verify 2FA is enabled
- Check MAIL_PORT (587 for TLS)

**"YouTube video not embedding"**
- Verify video is not Private
- Use full YouTube URL with /watch?v=
- Check video URL is in promo_video_url field

**"Payment gateway error"**
- Verify API credentials are correct
- Check if using sandbox vs production keys
- Ensure callback URLs are whitelisted

---

## Need Help?

If you encounter issues:
1. Check error logs: `storage/logs/error.log`
2. Verify .env file exists and has correct values
3. Test each service individually
4. Check server requirements (PHP 7.4+, MySQL 5.7+)

Good luck with your deployment! 🚀
