# Edutrack LMS - AI Agent Documentation

## Project Overview

Edutrack LMS is a **Learning Management System** built for Edutrack Computer Training College, a TEVETA-registered vocational training institution based in Kalomo, Zambia. The system manages online courses, student enrollments, payments, certificates, and live virtual classes.

**Key Facts:**
- **Type**: Custom PHP web application (not based on Laravel/Symfony)
- **Language**: English (all code comments and documentation)
- **Timezone**: Africa/Lusaka (CAT, UTC+2)
- **Currency**: Zambian Kwacha (ZMW)
- **Target Region**: Zambia with local mobile money integration

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| **Backend** | PHP 8.0+ |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ |
| **Frontend** | Tailwind CSS, Vanilla JavaScript, Font Awesome |
| **Package Manager** | Composer |
| **Web Server** | Apache (mod_rewrite required) |
| **Session Storage** | File-based (in `storage/sessions/`) |

### Composer Dependencies
```json
{
    "google/apiclient": "^2.18",      // Google OAuth & Drive integration
    "phpmailer/phpmailer": "^6.8",    // SMTP email sending
    "tecnickcom/tcpdf": "^6.6"        // PDF certificate generation
}
```

---

## Project Structure

```
edutrack-lms/
├── composer.json          # PHP dependencies
├── .env                   # Environment configuration (NEVER commit)
├── .htaccess             # Apache rewrite rules (root → public/)
├── config/               # Configuration files
│   ├── app.php           # App settings, colors, features
│   ├── database.php      # Database connection config
│   ├── mail.php          # SMTP configuration
│   └── payment.php       # Payment gateway settings
├── database/             # SQL schemas
│   └── complete_lms_schema.sql
├── migrations/           # Database migration files
├── cron/                 # Scheduled task scripts
│   ├── process-emails.php
│   └── session-reminders.php
├── storage/              # Writable storage
│   ├── cache/            # Application cache
│   ├── logs/             # Error and access logs
│   └── sessions/         # PHP session files
├── src/                  # Application source code
│   ├── bootstrap.php     # Central initialization file
│   ├── api/              # API base classes
│   ├── classes/          # Domain model classes (27 classes)
│   ├── includes/         # Core functionality files
│   ├── mail/             # Email template files
│   ├── middleware/       # Access control middleware
│   └── templates/        # Reusable view components
├── public/               # Web document root
│   ├── index.php         # Homepage
│   ├── assets/           # CSS, JS, images
│   ├── uploads/          # User-generated content
│   ├── api/              # REST API endpoints
│   ├── admin/            # Admin panel pages
│   ├── instructor/       # Instructor dashboard
│   ├── student/          # Student-specific pages
│   └── actions/          # Form action handlers
└── vendor/               # Composer dependencies
```

---

## Architecture Patterns

### 1. Bootstrap Initialization
All PHP files MUST include the bootstrap file:
```php
require_once '../src/bootstrap.php';
```

This loads:
- Composer autoloader
- Security headers
- Configuration constants
- Database connection (singleton)
- Authentication functions
- CSRF protection
- Session management

### 2. Database Access Pattern
Singleton Database class with PDO:
```php
$db = Database::getInstance();
$results = $db->fetchAll("SELECT * FROM courses WHERE status = ?", ['published']);
```

### 3. Class-Based Models
Domain models in `src/classes/`:
- `User` - User accounts and profiles
- `Course` - Course content management
- `Enrollment` - Student enrollments
- `Payment` / `Lenco` - Payment processing
- `Certificate` / `CertificateGenerator` - Certificate management
- `Email` / `EmailNotificationService` - Email handling
- `Lesson`, `Module`, `Quiz`, `Assignment` - Learning content

### 4. Middleware Pattern
Role-based access control in `src/middleware/`:
- `authenticate.php` - Requires login
- `admin-only.php` - Admin role required
- `instructor-only.php` - Instructor role required
- `finance-only.php` - Finance staff required
- `enrolled-only.php` - Must be enrolled in course

Usage:
```php
require_once '../src/middleware/authenticate.php';
```

---

## User Roles & Permissions

| Role | ID | Capabilities |
|------|-----|--------------|
| **Admin** | 1 | Full system access, user management, financial reports |
| **Instructor** | 2 | Create/edit courses, grade assignments, view analytics |
| **Finance** | 3 | Manage payments, invoices, financial reports |
| **Student** | 4 | Enroll in courses, take lessons, submit assignments |

User roles are stored in `user_roles` table linked to `users` table.

---

## Key Features

### Course Management
- Course creation with modules and lessons
- Video content support (YouTube, Vimeo, BunnyCDN)
- File resource uploads
- Quiz and assignment creation
- Progress tracking

### Payment Integration
- **Lenco** payment gateway (primary)
- **MTN Mobile Money** (Zambia)
- **Airtel Money** (Zambia)
- Manual bank transfer verification

### Certificates
- Auto-generated PDF certificates using TCPDF
- TEVETA accreditation branding
- Unique certificate numbers with verification
- Public certificate verification page

### Live Sessions
- **Jitsi Meet** integration for virtual classes
- Scheduled live sessions per course
- Recording support

### Email System
- PHPMailer with Gmail SMTP
- Email queue system for bulk sending
- Templates: welcome, enrollment confirmation, password reset, certificate issued
- Cron job processes queue every 5 minutes

### Google Integration
- Google OAuth for login/signup
- Google Drive for file storage

---

## Security Considerations

### Implemented Protections
- **CSRF Tokens**: All forms require `csrf_token` field
- **Rate Limiting**: 5 login attempts per 15 minutes
- **Password Requirements**: Min 8 chars, uppercase, number required
- **Encryption**: AES-256 for sensitive data
- **JWT**: For API authentication
- **Session Security**: HttpOnly, Secure, SameSite=Lax cookies
- **SQL Injection**: All queries use prepared statements
- **XSS Protection**: Output escaping with `htmlspecialchars()`

### Security Headers (src/includes/security-headers.php)
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin

### Important Security Notes
1. **Delete `public/install.php` after installation**
2. **Protect `.env` file** - contains credentials
3. **Secure `storage/` directory** - contains logs and sessions
4. **Regenerate keys** in production (ENCRYPTION_KEY, JWT_SECRET)

---

## Configuration

### Environment Variables (.env)
Key variables that must be configured:

```bash
# Application
APP_ENV=production|development
APP_DEBUG=false|true
APP_URL=https://edutrackzambia.com

# Database
DB_HOST=localhost
DB_NAME=edutrack_lms
DB_USER=root
DB_PASS=secret

# Security (generate new for production)
ENCRYPTION_KEY="..."
JWT_SECRET="..."

# Email (Gmail SMTP)
MAIL_USERNAME=edutrackzambia@gmail.com
MAIL_PASSWORD="app_specific_password"

# Google OAuth
GOOGLE_CLIENT_ID="..."
GOOGLE_CLIENT_SECRET="..."

# Payment (Lenco)
LENCO_SANDBOX_API_KEY="..."
LENCO_SANDBOX_SECRET_KEY="..."
```

---

## Database Schema

Key tables (from `database/complete_lms_schema.sql`):

| Table | Purpose |
|-------|---------|
| `users` | Core user accounts |
| `user_profiles` | Extended user information |
| `user_roles` | Role assignments |
| `courses` | Course content |
| `course_categories` | Course categorization |
| `modules` | Course modules |
| `lessons` | Individual lessons |
| `enrollments` | Student course enrollments |
| `payments` / `transactions` | Payment records |
| `certificates` | Issued certificates |
| `assignments` / `assignment_submissions` | Coursework |
| `quizzes` / `questions` / `quiz_attempts` | Quiz system |
| `announcements` | System/course announcements |
| `activity_logs` | Audit trail |
| `email_queue` | Pending emails |

---

## Development Guidelines

### Code Style
- **Indentation**: 4 spaces
- **PHP Tags**: Always use `<?php` (short tags avoided)
- **Comments**: PHPDoc format for functions
- **Naming**: 
  - Classes: PascalCase (e.g., `CertificateGenerator`)
  - Functions: camelCase (e.g., `registerUser`)
  - Variables: snake_case (e.g., `$user_id`)
  - Constants: UPPER_CASE (e.g., `APP_DEBUG`)

### Adding New Pages
1. Create PHP file in appropriate `public/` subdirectory
2. Include bootstrap: `require_once '../src/bootstrap.php'`
3. Add middleware if needed: `require_once '../src/middleware/authenticate.php'`
4. Include templates: `require_once '../src/templates/header.php'`
5. Close with: `require_once '../src/templates/footer.php'`

### Database Queries
Always use prepared statements:
```php
// Good
$db->query("SELECT * FROM users WHERE email = ?", [$email]);

// Bad - Never do this
$db->query("SELECT * FROM users WHERE email = '$email'");
```

### Form Handling
Always validate CSRF:
```php
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die('Invalid CSRF token');
}
```

---

## Testing

### Manual Testing
No automated test suite is currently configured. Test manually:

1. **User Flows**: Registration → Login → Course Enrollment → Payment → Learning
2. **Instructor Flows**: Create Course → Add Content → Review Submissions
3. **Admin Flows**: User Management → Financial Reports → Settings

### Debug Mode
Enable in `.env`:
```bash
APP_DEBUG=true
APP_ENV=development
```

This enables:
- Detailed error messages
- SQL query logging
- Debug logging to `storage/logs/`

---

## Deployment

### Server Requirements
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite enabled
- Composer for dependency installation
- SSL certificate (HTTPS required for security headers)

### Deployment Steps
1. Upload files to web server
2. Run `composer install --no-dev --optimize-autoloader`
3. Copy `.env.example` to `.env` and configure
4. Import `database/complete_lms_schema.sql`
5. Ensure `storage/` and `public/uploads/` are writable (755)
6. Configure Apache to point to `public/` directory
7. Delete `public/install.php`
8. Set up cron jobs (see below)

### Cron Jobs
Add to crontab for email processing:
```bash
# Process email queue every 5 minutes
*/5 * * * * /usr/bin/php /path/to/cron/process-emails.php

# Session reminders daily at 8am
0 8 * * * /usr/bin/php /path/to/cron/session-reminders.php
```

---

## Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check `storage/logs/php-errors.log`
- Verify `.env` file exists and is readable
- Check file permissions on `storage/` (must be writable)

**Database Connection Failed**
- Verify DB credentials in `.env`
- Check MySQL service is running
- Confirm database exists

**Emails Not Sending**
- Check Gmail app-specific password is correct
- Review `storage/logs/cron-email.log`
- Verify cron job is running

**File Uploads Failing**
- Check `uploads/` directory permissions (755)
- Verify PHP `upload_max_filesize` setting
- Check available disk space

---

## External Services

| Service | Purpose | Configuration |
|---------|---------|---------------|
| Gmail SMTP | Email sending | MAIL_* env vars |
| Google OAuth | Social login | GOOGLE_CLIENT_* |
| Lenco | Payment processing | LENCO_* env vars |
| Jitsi Meet | Video conferencing | config/app.php |
| YouTube API | Video embedding | YOUTUBE_API_KEY |

---

## File Statistics
- **Total PHP Lines**: ~18,700
- **Classes**: 27 domain models
- **API Endpoints**: 25+ REST endpoints
- **Database Tables**: 30+ tables
- **Public Pages**: 40+ user-facing pages

---

## Contact & Support
- **Organization**: Edutrack Computer Training College
- **Location**: Kalomo, Zambia
- **Email**: edutrackzambia@gmail.com
- **Phone**: +260 770 666 937

---

*Last Updated: March 2026*
*For AI Agent Use - Keep this document current with code changes*
