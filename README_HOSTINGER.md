# EduTrack LMS - Hostinger Deployment

## 📋 Overview

Complete deployment package for hosting EduTrack LMS on Hostinger.

**Your Hosting Details:**
- Domain: `khaki-dunlin-812469.hostingersite.com`
- Status: Active ✓
- Backups: Daily ✓

---

## 📚 Documentation Files

### Quick Start (Recommended)
**`QUICK_START_HOSTINGER.md`** - Fast 30-minute deployment guide
- Perfect for beginners
- Step-by-step instructions
- Minimal configuration
- Get live quickly

### Complete Guide
**`HOSTINGER_DEPLOYMENT_GUIDE.md`** - Comprehensive deployment manual
- Detailed instructions for every step
- Security hardening guide
- Performance optimization
- Troubleshooting section
- Email configuration
- SSL setup
- Custom domain configuration

### Checklist
**`DEPLOYMENT_CHECKLIST.md`** - Printable deployment checklist
- Track your progress
- Don't miss any steps
- Pre-deployment preparation
- Post-deployment verification
- Rollback plan

---

## 🚀 Quick Deployment (30 min)

### 1. Database Setup (5 min)
```bash
# In Hostinger: Databases → Create Database
# Import: database/complete_lms_schema.sql
# Save credentials!
```

### 2. Configure Environment (5 min)
```bash
# Generate keys
openssl rand -base64 32  # ENCRYPTION_KEY
openssl rand -base64 64  # JWT_SECRET

# Copy and edit
cp .env.hostinger .env
# Fill in database credentials and keys
```

### 3. Upload Files (10 min)
- Use FTP or File Manager
- Upload to `public_html/`
- Set web root to `public_html/public`

### 4. Install & Test (10 min)
```bash
# If SSH available:
composer install --no-dev

# Or upload /vendor/ folder

# Visit: yourdomain.com/install.php
# Follow web installer
```

✅ **Done!** Your LMS is live!

---

## 📁 New Files Added

### Configuration
- `.env.hostinger` - Production environment template
- `generate-keys.sh` - Security key generator

### Documentation
- `HOSTINGER_DEPLOYMENT_GUIDE.md` - Complete deployment manual
- `QUICK_START_HOSTINGER.md` - Quick start guide
- `DEPLOYMENT_CHECKLIST.md` - Deployment checklist
- `README_HOSTINGER.md` - This file

### Tools
- `public/install.php` - Web-based installer (delete after use!)

---

## 🔧 System Requirements

- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.3+
- **Web Server**: Apache with mod_rewrite
- **Storage**: 500 MB minimum
- **Memory**: 256 MB PHP memory limit

---

## 📊 What's Included

### Application Features
- ✅ Student enrollment system
- ✅ Course management
- ✅ Lesson tracking
- ✅ Assignment submissions
- ✅ Quiz system
- ✅ Certificate generation
- ✅ Payment integration (MTN, Airtel, Zamtel)
- ✅ Live sessions
- ✅ Discussion forums
- ✅ Email notifications
- ✅ Admin dashboard
- ✅ Instructor portal
- ✅ Analytics and reporting

### Security Features
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ Rate limiting
- ✅ Secure session handling
- ✅ Password hashing (bcrypt)
- ✅ Input validation
- ✅ Security headers

---

## 🔒 Security Checklist

Before going live:

- [ ] Generate NEW encryption keys
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Enable SSL certificate
- [ ] Set `SESSION_SECURE=true`
- [ ] Protect `.env` file
- [ ] Delete `install.php` after use
- [ ] Review file permissions
- [ ] Test backup restoration
- [ ] Check error logs

---

## 📧 Email Configuration

### Option 1: Hostinger Email (Recommended)
```env
MAIL_HOST="smtp.hostinger.com"
MAIL_PORT=587
MAIL_USERNAME="your-email@yourdomain.com"
MAIL_PASSWORD="your-password"
```

### Option 2: Gmail
```env
MAIL_HOST="smtp.gmail.com"
MAIL_PORT=587
MAIL_USERNAME="your-email@gmail.com"
MAIL_PASSWORD="your-app-password"
```

See `HOSTINGER_DEPLOYMENT_GUIDE.md` for detailed setup.

---

## 💳 Payment Gateways

### Supported in Zambia
- **MTN Mobile Money** - Configure in `.env`
- **Airtel Money** - Configure in `.env`
- **Zamtel Kwacha** - Configure in `.env`
- **Bank Transfer** - Manual verification

See `config/payment.php` for configuration options.

---

## 🎯 Post-Deployment Tasks

### Immediate (Day 1)
1. Delete `public/install.php`
2. Create admin account
3. Test login/registration
4. Verify email sending
5. Check error logs

### Week 1
1. Configure payment gateways
2. Create course categories
3. Add first courses
4. Upload course materials
5. Invite instructors
6. Test enrollment flow

### Ongoing
1. Monitor error logs weekly
2. Update dependencies monthly
3. Test backups regularly
4. Review user activity
5. Optimize performance

---

## 🐛 Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check `.env` file exists
- Verify file permissions (755/644)
- Review error logs in `/storage/logs/`

**Database Connection Failed**
- Verify credentials in `.env`
- Check database exists
- Ensure `DB_HOST="localhost"`

**CSS/JS Not Loading**
- Verify web root is `/public/`
- Check `.htaccess` exists
- Clear browser cache

**Email Not Sending**
- Test SMTP credentials
- Check firewall/port 587
- Review mail logs

See `HOSTINGER_DEPLOYMENT_GUIDE.md` for complete troubleshooting guide.

---

## 📞 Support

### Hostinger Support
- **Live Chat**: 24/7 in dashboard
- **Knowledge Base**: https://support.hostinger.com
- **Ticket System**: Available in dashboard

### Technical Resources
- Error Logs: `/storage/logs/`
- Documentation: See files above
- Database: phpMyAdmin in Hostinger

---

## 🔄 Update Process

To update the LMS in the future:

1. **Backup Everything**
   - Download files via FTP
   - Export database via phpMyAdmin
   - Save `.env` file

2. **Upload New Files**
   - Replace old files (except `.env`)
   - Keep `/public/uploads/` folder

3. **Run Migrations**
   - Check `/database/migrations/` for new files
   - Import via phpMyAdmin

4. **Test**
   - Verify functionality
   - Check error logs
   - Test critical features

---

## 📈 Performance Tips

### Enable Caching
- OPcache (in PHP settings)
- Browser caching (in `.htaccess`)
- Gzip compression (in `.htaccess`)

### Optimize Database
- Regular OPTIMIZE TABLE
- Index commonly queried fields
- Archive old data

### CDN (Optional)
- Use Cloudflare (free)
- Bunny CDN for videos
- Image optimization

See performance section in `HOSTINGER_DEPLOYMENT_GUIDE.md`

---

## 🎓 Next Steps

### Connect Custom Domain
1. Purchase domain (Hostinger or external)
2. Point DNS to Hostinger
3. Update `APP_URL` in `.env`
4. Enable SSL for new domain

### Customize Branding
1. Edit colors in `/config/app.php`
2. Upload logo in admin panel
3. Customize email templates in `/src/mail/`
4. Update TEVETA information in `.env`

### Launch Marketing
1. Create landing page
2. Add course catalog
3. Set up payment methods
4. Create promotional content
5. Invite first students

---

## 📝 File Structure

```
edutrack-lms/
├── public/              ← WEB ROOT (point domain here)
│   ├── index.php
│   ├── install.php      ← DELETE AFTER INSTALLATION!
│   └── uploads/         ← Writable
├── src/                 ← Application code
├── config/              ← Configuration files
├── database/            ← SQL files
├── storage/             ← Logs, cache (writable)
├── .env                 ← Your configuration (create from .env.hostinger)
└── vendor/              ← Composer dependencies
```

---

## ✅ Deployment Checklist Summary

Pre-deployment:
- [ ] Database created and imported
- [ ] `.env` configured
- [ ] Security keys generated
- [ ] Dependencies installed

Deployment:
- [ ] Files uploaded
- [ ] Web root configured
- [ ] File permissions set
- [ ] SSL enabled

Post-deployment:
- [ ] Admin account created
- [ ] `install.php` deleted
- [ ] Email tested
- [ ] Backups configured

---

## 🎉 Ready to Deploy?

1. **Quick Start**: Read `QUICK_START_HOSTINGER.md`
2. **Full Guide**: Read `HOSTINGER_DEPLOYMENT_GUIDE.md`
3. **Track Progress**: Use `DEPLOYMENT_CHECKLIST.md`

**Deployment time**: 30-60 minutes
**Skill level**: Beginner-friendly

---

## 📄 License & Support

This is a custom Learning Management System for Edutrack Computer Training College.

For technical support during deployment:
- Check documentation files
- Review error logs
- Contact Hostinger support
- Refer to troubleshooting guide

---

**Good luck with your deployment! 🚀**

**Your LMS will be live at**: https://khaki-dunlin-812469.hostingersite.com
