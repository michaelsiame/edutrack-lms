# Edutrack Computer Training College - Learning Management System

![TEVETA Certified](https://img.shields.io/badge/TEVETA-Certified-F6B745?style=for-the-badge)
![Version](https://img.shields.io/badge/Version-1.0.0-2E70DA?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

## 📖 About

Edutrack Computer Training College is a comprehensive Learning Management System (LMS) designed specifically for TEVETA-registered computer training institutions in Zambia. The platform enables students to enroll in courses, track their progress, take assessments, and receive government-recognized TEVETA certificates upon completion.

### Key Features

- 🎓 **TEVETA-Certified Courses** - All courses are registered with TEVETA
- 👥 **Multi-Role Support** - Students, Instructors, and Administrators
- 📚 **Course Management** - Create and manage courses with modules and lessons
- 🎥 **Video Learning** - Support for YouTube, Vimeo, and self-hosted videos
- 📝 **Assessments** - Quizzes and assignments with auto-grading
- 💳 **Payment Integration** - Mobile Money (MTN, Airtel) and Bank Transfer
- 🎖️ **Certificates** - Automated TEVETA certificate generation
- 📊 **Progress Tracking** - Real-time student progress monitoring
- 💬 **Course Discussions** - Q&A and discussion forums
- ⭐ **Reviews & Ratings** - Course feedback system
- 📱 **Responsive Design** - Mobile-friendly interface
- 🔒 **Secure** - CSRF protection, password hashing, rate limiting

## 🎨 Brand Identity

### Colors
- **Primary Blue**: `#2E70DA` - Main brand color
- **Accent Gold**: `#F6B745` - TEVETA certification, CTAs
- **White**: `#FFFFFF` - Backgrounds, text

## 🚀 Quick Start

### Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer (optional)
- Node.js & NPM (optional)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/edutrack-lms.git
   cd edutrack-lms
   ```

2. **Create the database**
   ```bash
   mysql -u root -p
   ```
   ```sql
   CREATE DATABASE edutrack_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;
   ```

3. **Import database schema**
   ```bash
   mysql -u root -p edutrack_lms < database/schema.sql
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   nano .env
   ```
   
   Update the following in `.env`:
   - Database credentials
   - Site information
   - Mail settings
   - Payment gateway keys

5. **Set file permissions**
   ```bash
   chmod -R 755 public/
   chmod -R 777 public/uploads/
   chmod -R 777 storage/logs/
   chmod -R 777 storage/cache/
   chmod -R 777 storage/sessions/
   chmod 600 .env
   ```

6. **Start development server**
   ```bash
   php -S localhost:8000 -t public/
   ```

7. **Access the application**
   - Open browser: `http://localhost:8000`
   - Default admin login:
     - Email: `admin@edutrack.zm`
     - Password: `admin123` (change immediately!)

## 📁 Project Structure

```
edutrack-lms/
├── public/              # Web-accessible files
│   ├── admin/          # Admin panel
│   ├── instructor/     # Instructor panel
│   ├── api/           # API endpoints
│   ├── assets/        # CSS, JS, images
│   └── uploads/       # User uploads
├── src/
│   ├── includes/      # Core functionality
│   ├── classes/       # PHP classes
│   ├── templates/     # Reusable templates
│   ├── middleware/    # Authentication middleware
│   └── mail/          # Email templates
├── config/            # Configuration files
├── database/          # Database schemas
├── storage/           # Logs, cache, sessions
└── docs/              # Documentation
```

## 🔧 Configuration

### Environment Variables

Key environment variables in `.env`:

```env
# Database
DB_HOST=localhost
DB_NAME=edutrack_lms
DB_USER=root
DB_PASS=your_password

# Application
APP_NAME="Edutrack Computer Training College"
APP_URL=http://localhost:8000
APP_ENV=development
APP_DEBUG=true

# TEVETA
TEVETA_INSTITUTION_CODE=TEVETA/XXX/2024

# Mail (Gmail SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

### Payment Integration

#### MTN Mobile Money
1. Register for MTN MoMo API
2. Add credentials to `.env`
3. Enable in admin panel

#### Airtel Money
1. Register for Airtel Money API
2. Add credentials to `.env`
3. Enable in admin panel

### Video Hosting

**Recommended: YouTube (Free)**
1. Upload videos as "Unlisted"
2. Copy video URL
3. Paste in course lesson

**Alternative: Vimeo**
1. Create Vimeo account
2. Upload videos
3. Add API token to `.env`

## 👥 User Roles

### Student
- Browse and enroll in courses
- Watch video lessons
- Take quizzes and assignments
- Track progress
- Download certificates

### Instructor
- Create and manage courses
- Upload content
- Create assessments
- Grade assignments
- View student analytics

### Administrator
- Full system access
- User management
- Payment verification
- Certificate issuance
- System settings

## 🔐 Security Features

- Password hashing (bcrypt)
- CSRF token protection
- XSS prevention
- SQL injection prevention
- Rate limiting
- Session security
- File upload validation
- Input sanitization

## 📊 Database

### Core Tables
- `users` - User accounts
- `courses` - Course information
- `enrollments` - Student enrollments
- `lessons` - Course lessons
- `quizzes` - Assessments
- `payments` - Payment records
- `certificates` - TEVETA certificates

See `database/schema.sql` for complete database structure.

## 🎓 TEVETA Integration

This system is designed to comply with TEVETA requirements:

1. All courses are TEVETA-registered
2. Certificates include TEVETA registration numbers
3. Certificate verification system
4. Proper record keeping
5. Student progress tracking

## 📱 API Endpoints

```
POST   /api/auth.php           - Authentication
GET    /api/courses.php        - List courses
POST   /api/enroll.php         - Enroll in course
GET    /api/progress.php       - Get progress
POST   /api/quiz.php           - Submit quiz
POST   /api/upload.php         - Upload files
```

## 🧪 Testing

### Manual Testing
1. Register new student account
2. Enroll in free course
3. Complete lessons
4. Take quiz
5. Check certificate

### Admin Testing
1. Login as admin
2. Create new course
3. Add modules and lessons
4. Publish course
5. Verify enrollment

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Update `APP_URL` to production URL
- [ ] Change default admin password
- [ ] Configure SSL certificate
- [ ] Set up automated backups
- [ ] Configure email service
- [ ] Test payment gateways
- [ ] Optimize database
- [ ] Enable caching

### Server Requirements
- PHP 8.0+
- MySQL 8.0+
- 2GB RAM minimum
- 20GB storage minimum
- SSL certificate (Let's Encrypt)

## 📝 License

This project is proprietary software owned by Edutrack Computer Training College.

## 👨‍💻 Development Team

- **Project Lead**: [Your Name]
- **Backend Developer**: [Your Name]
- **Frontend Developer**: [Your Name]
- **Database Administrator**: [Your Name]

## 📞 Support

- **Email**: support@edutrack.zm
- **Phone**: +260-XXX-XXX-XXX
- **Website**: https://edutrack.zm

## 🙏 Acknowledgments

- TEVETA - Technical Education, Vocational and Entrepreneurship Training Authority
- TailwindCSS - CSS Framework
- Font Awesome - Icons
- Alpine.js - JavaScript Framework

## 📈 Roadmap

### Version 1.1 (Q1 2025)
- [ ] Mobile app (iOS/Android)
- [ ] Live classes integration
- [ ] Advanced analytics
- [ ] Gamification

### Version 1.2 (Q2 2025)
- [ ] AI-powered recommendations
- [ ] Multi-language support
- [ ] Offline course access
- [ ] Social learning features

---

**Made with ❤️ in Zambia**

**TEVETA Certified Institution**