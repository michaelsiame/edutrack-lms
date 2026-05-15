<!-- AGENTS.md - Edutrack LMS -->
# Edutrack LMS - AI Agent Documentation

## Project Overview

Edutrack LMS is a **custom PHP web application** (not based on Laravel, Symfony, or any major framework) built for Edutrack Computer Training College, a TEVETA-registered vocational training institution based in Kalomo, Zambia. The system manages online courses, student enrollments, payments, certificates, live virtual classes, assignments, quizzes, and discussions.

**Key Facts:**
- **Type**: Custom PHP 8.0+ web application with server-side rendering
- **Language**: English (all code comments and documentation)
- **Timezone**: Africa/Lusaka (CAT, UTC+2)
- **Currency**: Zambian Kwacha (ZMW)
- **Target Region**: Zambia with local mobile money integration
- **Web Server**: Apache with `mod_rewrite` required
- **Document Root**: `public/` directory

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| **Backend** | PHP 8.0+ |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ |
| **Frontend** | Tailwind CSS 3.4.1, Vanilla JavaScript, Font Awesome 6.4.0, Alpine.js, Chart.js |
| **Package Managers** | Composer, NPM (for Tailwind CSS build only) |
| **Email** | PHPMailer with Gmail SMTP |
| **PDF Generation** | Dompdf (primary), TCPDF (fallback) |
| **Session Storage** | File-based (in `storage/sessions/`) |
| **Payment Gateway** | Lenco (primary), MTN Mobile Money, Airtel Money, Zamtel Kwacha, Bank Transfer |
| **Live Video** | Jitsi Meet |
| **Auth** | Session-based + JWT for API |

### Composer Dependencies (`composer.json`)
```json
{
    "name": "edutrack/lms",
    "description": "Edutrack Computer Training College - Learning Management System",
    "type": "project",
    "require": {
        "php": ">=8.0",
        "dompdf/dompdf": "^3.1",
        "google/apiclient": "^2.18",
        "phpmailer/phpmailer": "^6.8",
        "tecnickcom/tcpdf": "^6.6"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0"
    },
    "autoload": {
        "psr-4": {
            "Edutrack\\": "src/"
        }
    },
    "config": {
        "optimize-autoloader": true,
        "sort-packages": true
    }
}
```

### NPM Dependencies (`package.json`)
```json
{
    "name": "edutrack-lms",
    "version": "1.0.0",
    "scripts": {
        "build:css": "tailwindcss -i ./src/input.css -o ./public/assets/css/tailwind.css --minify",
        "watch:css": "tailwindcss -i ./src/input.css -o ./public/assets/css/tailwind.css --watch"
    },
    "devDependencies": {
        "tailwindcss": "^3.4.1"
    }
}
```

**Important:** The `composer.lock` and `public/assets/css/tailwind.css` are committed to the repository for consistent deployments. There is no frontend bundler (Webpack, Vite, etc.).

---

## Project Structure

```
edutrack-lms/
├── composer.json              # PHP dependencies
├── composer.lock              # Locked dependency versions (committed)
├── package.json               # NPM dependencies (Tailwind CSS only)
├── package-lock.json          # Locked NPM dependency versions
├── tailwind.config.js         # Tailwind CSS configuration
├── .env                       # Environment configuration (NEVER commit)
├── .htaccess                  # Root Apache rewrite: forwards all requests to public/
│
├── config/                    # Configuration files
│   ├── app.php                # App settings, brand colors, TEVETA config, features
│   ├── database.php           # Database connection config
│   ├── mail.php               # SMTP configuration
│   ├── payment.php            # Payment gateway settings
│   └── google-credentials.json # Google API service account credentials
│
├── database/
│   └── complete_lms_schema.sql # Full database schema dump
│
├── migrations/                # Incremental SQL migration files (19 total)
│   ├── 002_comprehensive_fixes.sql
│   ├── 003_add_google_id_to_users.sql
│   ├── 004_add_unique_constraints.sql
│   ├── 005_add_missing_tables.sql
│   ├── add_certificates_user_course_columns.sql
│   ├── add_course_career_fields.sql
│   ├── add_lenco_collections_table.sql
│   ├── add_lenco_transactions_table.sql
│   ├── add_lenco_webhook_logs_table.sql
│   ├── add_performance_indexes.sql
│   └── ... (and 9 more)
│
├── cron/                      # Scheduled task scripts (CLI-only, secret key for web)
│   ├── process-emails.php     # Processes email_queue every 5 minutes
│   └── session-reminders.php  # Live session reminders (30min, 5min, start)
│
├── public/                    # Web document root (152 PHP files)
│   ├── .htaccess              # Apache rules: pretty URLs, security headers, gzip, caching
│   ├── index.php              # Homepage
│   ├── assets/                # CSS (Tailwind + custom), JS, images, fonts, CSV templates
│   ├── uploads/               # User-generated content (avatars, certificates, submissions, etc.)
│   ├── api/                   # REST API endpoints (30 files)
│   ├── admin/                 # Admin panel with router (index.php?page=...) + pages/ subdirectory
│   ├── instructor/            # Instructor dashboard pages
│   ├── student/               # Student-specific pages
│   └── actions/               # Form action handlers (lesson CRUD, quiz submit, etc.)
│
├── src/                       # Application source code
│   ├── bootstrap.php          # Central initialization file
│   ├── input.css              # Tailwind CSS entry point
│   ├── api/
│   │   └── ApiBase.php        # Abstract base class for all API endpoints
│   ├── classes/               # Domain model classes (32 PHP classes)
│   │   ├── User.php
│   │   ├── Course.php
│   │   ├── Enrollment.php
│   │   ├── Payment.php
│   │   ├── Lenco.php          # Lenco payment gateway integration
│   │   ├── Certificate.php
│   │   ├── Email.php
│   │   ├── EmailNotificationService.php
│   │   ├── Lesson.php
│   │   ├── Module.php
│   │   ├── Quiz.php
│   │   ├── Question.php
│   │   ├── Assignment.php
│   │   ├── Submission.php
│   │   ├── LiveSession.php
│   │   ├── Notification.php
│   │   ├── GoogleDriveService.php
│   │   ├── FileUpload.php
│   │   ├── InstitutionPhoto.php
│   │   ├── Instructor.php
│   │   ├── Invoice.php
│   │   ├── PaymentPlan.php
│   │   ├── Progress.php
│   │   ├── RegistrationFee.php
│   │   ├── Review.php
│   │   ├── Statistics.php
│   │   ├── Announcement.php
│   │   ├── Badge.php
│   │   ├── Category.php
│   │   ├── Discussion.php
│   │   ├── Event.php
│   │   └── LessonResource.php
│   ├── includes/              # Core functionality files (13 files)
│   │   ├── config.php         # Env loader, constants, helper functions (url, asset, redirect)
│   │   ├── database.php       # Singleton Database class (PDO wrapper)
│   │   ├── security.php       # CSRF, password hashing, encryption, rate limiting
│   │   ├── validation.php     # Input validation helpers
│   │   ├── functions.php      # Core helper functions
│   │   ├── helpers.php        # Additional helpers
│   │   ├── auth.php           # Authentication functions (register, login, session)
│   │   ├── email.php          # Email helper functions
│   │   ├── security-headers.php # HTTP security headers (CSP, HSTS, Permissions-Policy)
│   │   ├── access-control.php # Centralized access denied pages, requireAdmin, etc.
│   │   ├── stats.php          # Statistics helpers
│   │   ├── admin-debug.php    # Admin debugging utilities
│   │   └── email-hooks.php    # Email event hooks
│   ├── middleware/            # Access control middleware
│   │   ├── authenticate.php   # Requires login
│   │   ├── admin-only.php     # Admin role required
│   │   ├── instructor-only.php
│   │   ├── finance-only.php
│   │   └── enrolled-only.php  # Must be enrolled in course
│   ├── mail/                  # Email template files (PHP-based HTML templates)
│   └── templates/             # Reusable view components
│       ├── header.php
│       ├── footer.php
│       ├── admin-header.php
│       ├── admin-sidebar.php
│       ├── admin-footer.php
│       ├── instructor-header.php
│       ├── instructor-footer.php
│       ├── alerts.php
│       ├── breadcrumbs.php
│       ├── navigation.php
│       ├── certificate-dompdf.php
│       ├── certificate-pdf.php
│       ├── testimonials-section.php
│       └── sidebar.php
│
├── storage/                   # Writable storage (must be 755)
│   ├── backups/               # Application backups
│   ├── cache/                 # Application cache
│   ├── logs/                  # Error and access logs
│   └── sessions/              # PHP session files
│
├── scripts/                   # Utility scripts
│   ├── tools/                 # Python scripts for course content generation
│   ├── add_registration_fees.php
│   ├── add_test_user.php
│   ├── compress-images.php
│   ├── import_office_students.php
│   └── verify_office_students.php
│
├── course_materials/          # Static course content (PPTX, PDFs, Python exercises)
├── docs/                      # Project documentation (code reviews, plans, etc.)
└── .github/workflows/         # GitHub Actions CI/CD
    └── build-css.yml          # Auto-build Tailwind CSS on push to main
```

---

## Architecture Patterns

### 1. Bootstrap Initialization
Every PHP file that is not an API endpoint MUST include the bootstrap file:
```php
require_once '../src/bootstrap.php';
```

This loads in order:
- Composer autoloader
- Security headers (`src/includes/security-headers.php`)
- `includes/config.php` (env vars, constants)
- `includes/database.php` (Database singleton)
- `includes/security.php` (CSRF, password functions)
- `includes/validation.php`
- `includes/functions.php`
- `includes/helpers.php`
- `includes/auth.php`
- `includes/email.php`
- `templates/alerts.php`
- SPL autoloader for `src/classes/`
- Session initialization
- CSRF token generation
- Session validation

### 2. Database Access Pattern
Singleton `Database` class with PDO (in `src/includes/database.php`):
```php
$db = Database::getInstance();
$results = $db->fetchAll("SELECT * FROM courses WHERE status = ?", ['published']);
```

The class provides:
- `query($sql, $params)` - prepared statements
- `fetchAll($sql, $params)`
- `fetchOne($sql, $params)`
- `fetchColumn($sql, $params)`
- `insert($table, $data)` - with identifier validation
- `update($table, $data, $where, $whereParams)`
- `delete($table, $where, $params)`
- `count($table, $where, $params)`
- `exists($table, $where, $params)`
- `beginTransaction()`, `commit()`, `rollback()`
- Auto-reconnect on "MySQL server has gone away" (error 2006)

A global `$pdo` is also available for backward compatibility: `$pdo = $db->getConnection();`

### 3. Class-Based Models
Domain models in `src/classes/` use active-record-style loading:
- Constructor accepts an optional ID and auto-loads from DB
- Static `find($id)` and `findByXxx()` methods
- Classes: `User`, `Course`, `Enrollment`, `Payment`, `Lenco`, `Certificate`, `Email`, `EmailNotificationService`, `Lesson`, `Module`, `Quiz`, `Question`, `Assignment`, `Submission`, `LiveSession`, `Notification`, `GoogleDriveService`, `FileUpload`, `InstitutionPhoto`, `Instructor`, `Invoice`, `PaymentPlan`, `Progress`, `RegistrationFee`, `Review`, `Statistics`, `Announcement`, `Badge`, `Category`, `Discussion`, `Event`, `LessonResource`

### 4. Middleware Pattern
Role-based access control in `src/middleware/`:
```php
require_once '../src/middleware/authenticate.php';
require_once '../src/middleware/admin-only.php';
```

Middleware files are self-contained and load their own dependencies (they do not assume bootstrap has run).

### 5. API Pattern
API endpoints in `public/api/` typically extend `ApiBase` (in `src/api/ApiBase.php`), which provides:
- JSON response handling
- Request data parsing (GET, POST, JSON, form-data)
- Authentication (JWT Bearer token or session)
- CORS handling
- Standard response methods: `successResponse()`, `errorResponse()`, `validationErrorResponse()`, `notFoundResponse()`, `forbiddenResponse()`
- Validation helper: `validate(['field' => 'required|email|min:3'])`
- Role checks: `requireRole()`, `requireAdmin()`, `requireInstructor()`

### 6. Admin Router Pattern
The admin panel uses a router at `public/admin/index.php`:
- Reads `?page=` parameter
- Validates against a `$validPages` array
- Includes the corresponding page from `public/admin/pages/`

---

## User Roles & Permissions

Roles are stored in the `roles` table and linked via `user_roles`. Users can have multiple roles and switch between them via `User::switchRole()`. The active role is stored in `$_SESSION['active_role']`.

| Role | ID | Capabilities |
|------|-----|--------------|
| **Admin** | 1 | Full system access, user management, financial reports, settings |
| **Instructor** | 2 | Create/edit courses, grade assignments, view analytics, manage live sessions |
| **Finance** | 3 | Manage payments, invoices, financial reports |
| **Student** | 4 | Enroll in courses, take lessons, submit assignments, take quizzes |

Role checks in code:
- `isAdmin()` - global function in `config.php`
- `$user->hasRole('admin')` - method on User class
- `$_SESSION['user_role']` / `$_SESSION['active_role']` - stored in session

---

## Database Schema

The complete schema is in `database/complete_lms_schema.sql` (~4,500 lines, 50+ tables/views). Key tables:

| Table | Purpose |
|-------|---------|
| `users` | Core user accounts |
| `user_profiles` | Extended user information |
| `user_roles` | Role assignments |
| `students` | Student-specific records |
| `instructors` | Instructor records linked to users |
| `courses` | Course content and metadata |
| `course_categories` | Course categorization |
| `modules` | Course modules |
| `lessons` | Individual lessons |
| `lesson_progress` | Student lesson completion tracking |
| `lesson_resources` | Files attached to lessons |
| `enrollments` | Student course enrollments |
| `enrollment_payment_plans` | Payment plans per enrollment |
| `payments` / `transactions` | Payment records |
| `lenco_transactions` | Lenco gateway transactions |
| `lenco_collections` | Lenco V2 mobile money collections |
| `lenco_webhook_logs` | Lenco webhook audit trail |
| `certificates` | Issued certificates |
| `assignments` / `assignment_submissions` | Coursework |
| `quizzes` / `questions` / `quiz_attempts` / `quiz_answers` | Quiz system |
| `live_sessions` / `live_session_attendance` | Virtual class sessions |
| `discussions` / `discussion_replies` | Course discussions |
| `announcements` | System/course announcements |
| `notifications` | In-app notifications |
| `email_queue` / `email_templates` | Email queue system |
| `badges` / `student_achievements` | Gamification |
| `activity_logs` | Audit trail |
| `registration_fees` | Registration fee payments |
| `rate_limits` | Rate limiting data |
| `user_sessions` | Session tracking |
| `invoices` | Invoice records |
| `payment_plans` | Global payment plan templates |
| `events` / `newsletter_subscribers` | Events and newsletter |
| `hero_slides` / `institution_photos` / `company_profiles` / `contacts` | CMS content |
| `v_student_balances` | Database view for student balances |

---

## Configuration

### Environment Variables (`.env`)
The `.env` file is parsed manually in `src/includes/config.php` (no DotEnv library). Key variables:

```bash
# Application
APP_ENV=production|development
APP_DEBUG=false|true
APP_URL=https://edutrackzambia.com
APP_TIMEZONE=Africa/Lusaka

# Database
DB_HOST=localhost
DB_NAME=edutrack_lms
DB_USER=root
DB_PASS=secret

# Security (generate new for production)
ENCRYPTION_KEY="..."
JWT_SECRET="..."

# Email (Gmail SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=edutrackzambia@gmail.com
MAIL_PASSWORD="app_specific_password"

# Google OAuth
GOOGLE_CLIENT_ID="..."
GOOGLE_CLIENT_SECRET="..."

# Payment (Lenco)
LENCO_SANDBOX_API_KEY="..."
LENCO_SANDBOX_SECRET_KEY="..."
LENCO_LIVE_API_KEY="..."
LENCO_LIVE_SECRET_KEY="..."
LENCO_WEBHOOK_SECRET="..."

# Cron
CRON_SECRET_KEY="change_this_secret"
```

**Note:** There is no `.env.example` file in the repository. Create one from the `.env` file if needed.

### Config Files (`config/`)
- `app.php` - Returns a PHP array with app settings, brand colors (primary `#2E70DA`, secondary `#F6B745`), TEVETA config, video platforms, Jitsi Meet, session/security, rate limiting, pagination.
- `database.php` - Returns a PHP array with connection settings and backup config.
- `mail.php` - Returns a PHP array with SMTP/sendmail/log mailers, templates, rate limiting.
- `payment.php` - Returns a PHP array with Lenco, MTN, Airtel, Zamtel, bank transfer settings.

### Tailwind CSS Configuration (`tailwind.config.js`)
- Custom color palette: primary, secondary, success, warning, danger
- Font family: Inter
- Custom shadows: soft, card, card-hover
- Content paths: `public/**/*.php`, `src/templates/**/*.php`, `src/includes/**/*.php`, `public/assets/js/**/*.js`

---

## Build and Deployment

### Tailwind CSS Build
```bash
# Build CSS for production (minified)
npm run build:css

# Watch for changes during development
npm run watch:css
```

- **Input:** `src/input.css`
- **Output:** `public/assets/css/tailwind.css` (committed to repo)
- The application also loads Tailwind via CDN in some templates for development convenience.

### PHP Dependencies
```bash
composer install
```

### Installation / Setup Commands
```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies (for Tailwind build)
npm install

# 3. Configure environment
# Create .env from existing config or documentation

# 4. Import database
mysql -u root -p edutrack_lms < database/complete_lms_schema.sql

# 5. Apply any pending migrations (manual process - run SQL files in migrations/)

# 6. Ensure writable directories
chmod -R 755 storage/
chmod -R 755 public/uploads/

# 7. Configure Apache to point to public/ directory
```

### Server Requirements
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- Composer for dependency installation
- Node.js and NPM (for Tailwind CSS builds)
- SSL certificate (HTTPS required for security headers and Google OAuth)
- `mbstring`, `openssl`, `pdo_mysql`, `gd` PHP extensions recommended

### GitHub Actions CI/CD
`.github/workflows/build-css.yml`:
- **Trigger:** Push to `main` when `src/**`, `public/**/*.php`, `src/templates/**/*.php`, `tailwind.config.js`, or `package.json` change
- **Runner:** `ubuntu-latest` with Node.js 20
- **Steps:** `npm install` -> `npm run build:css` -> auto-commit `public/assets/css/tailwind.css` with `[skip ci]`

**No PHP testing, linting, or deployment workflows exist.**

### Cron Jobs
Add to crontab for production:
```bash
# Process email queue every 5 minutes
*/5 * * * * /usr/bin/php /path/to/cron/process-emails.php

# Session reminders daily at 8am (more frequent scheduling recommended)
0 8 * * * /usr/bin/php /path/to/cron/session-reminders.php
```

Cron scripts can also be triggered via HTTP GET with `?key=CRON_SECRET_KEY` for hosts without CLI cron access.

---

## Code Style Guidelines

Observed conventions across the codebase:

- **Indentation**: 4 spaces (no tabs)
- **PHP Tags**: Always use `<?php` (short tags avoided)
- **Comments**: PHPDoc format for functions and classes
- **Naming**:
  - Classes: PascalCase (e.g., `EmailNotificationService`, `GoogleDriveService`)
  - Functions: camelCase (e.g., `registerUser`, `validateSession`)
  - Variables: snake_case (e.g., `$user_id`, `$course_id`)
  - Constants: UPPER_CASE (e.g., `APP_DEBUG`, `PRIMARY_COLOR`)
  - Database tables: snake_case, plural (e.g., `user_profiles`, `live_sessions`)
  - Database columns: snake_case (e.g., `created_at`, `password_hash`)

**No automated code quality tools are configured.** There is no PHP-CS-Fixer, PHP_CodeSniffer, ESLint, or Prettier setup. No pre-commit hooks are configured.

### Adding New Pages
1. Create PHP file in appropriate `public/` subdirectory
2. Include bootstrap: `require_once '../src/bootstrap.php';`
3. Add middleware if needed: `require_once '../src/middleware/authenticate.php';`
4. Include templates: `require_once '../src/templates/header.php';`
5. Close with: `require_once '../src/templates/footer.php';`

### Database Queries
Always use prepared statements via the `Database` class:
```php
// Good
$db = Database::getInstance();
$db->query("SELECT * FROM users WHERE email = ?", [$email]);

// Bad - Never do this
$db->query("SELECT * FROM users WHERE email = '$email'");
```

### Form Handling
Always validate CSRF:
```php
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die('CSRF token validation failed');
}
```

---

## Testing

**Current State: No automated test suite is configured.**

- `phpunit/phpunit` is listed in `require-dev` in `composer.json` and installed in `vendor/`.
- There is no `phpunit.xml` or `phpunit.xml.dist` file at the project root.
- There are no `*.test.php` or `*Test.php` files outside `vendor/`.
- There is no `tests/` directory.
- The `.gitignore` explicitly excludes `/phpunit.xml` and `/.phpunit.result.cache`.

### Manual Testing Strategy
Test these user flows manually:
1. **Student Flow**: Registration -> Email Verification -> Login -> Browse Courses -> Enrollment -> Payment -> Learning -> Quiz -> Assignment -> Certificate
2. **Instructor Flow**: Login -> Create Course -> Add Modules/Lessons -> Create Quiz -> Review Submissions -> Grade Assignments -> Schedule Live Session
3. **Admin Flow**: Login -> User Management -> Course Approval -> Financial Reports -> Payment Verification -> Announcements -> Settings

### Debug Mode
Enable in `.env`:
```bash
APP_DEBUG=true
APP_ENV=development
```
This enables:
- Detailed error messages
- Display errors on screen
- SQL query logging
- Debug logging to `storage/logs/`

---

## Security Considerations

### Implemented Protections
- **CSRF Tokens**: All forms require `csrf_token` field. Use `csrfField()` to generate HTML, `verifyCsrfToken()` or `requireCsrfToken()` to validate. API requests check `X-CSRF-Token` header.
- **Rate Limiting**: 5 login attempts per 15 minutes (configurable), backed by `rate_limits` table with session fallback.
- **Password Requirements**: Min 8 chars, uppercase, number, and special character required (configurable via `validatePasswordStrength()`).
- **Password Hashing**: `password_hash()` with `PASSWORD_DEFAULT`.
- **Encryption**: AES-256-CBC for sensitive data.
- **JWT**: For API authentication (`JWT_SECRET` required).
- **Session Security**: HttpOnly, Secure (when HTTPS), SameSite=Lax cookies. Session ID regenerated every 30 minutes. Browser fingerprint validation.
- **SQL Injection**: All queries use prepared statements. The `Database` class validates table/column names for `insert()`/`update()`/`delete()`.
- **XSS Protection**: Output escaping with `htmlspecialchars()`, `sanitizeInput()`, `xssClean()`.
- **File Upload Protection**: Allowed extensions whitelist, max size limits (50MB default), MIME check, PHP injection detection. PHP execution disabled in uploads via `.htaccess`.

### Security Headers (`src/includes/security-headers.php` and `public/.htaccess`)
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- **Content Security Policy (CSP)**:
  - `default-src 'self'`
  - `script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tailwindcss.com https://meet.jit.si`
  - `style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com`
  - `img-src 'self' data: https: http:`
  - `font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com`
  - `connect-src 'self' https://meet.jit.si`
  - `frame-src 'self' https://www.youtube.com https://player.vimeo.com https://meet.jit.si`
  - `frame-ancestors 'none'`
- **Permissions-Policy**: `microphone=(self "https://meet.jit.si"), camera=(self "https://meet.jit.si")`
- **HSTS**: `max-age=31536000; includeSubDomains` when HTTPS is on

### `.htaccess` Protections
- `public/.htaccess` denies access to `.env`, `.json`, `.lock`, `.md`, `.sql`, `.sqlite`
- Denies PHP execution in `/uploads/`
- Blocks direct access to `/vendor/`, `/src/`, `/config/`, `/database/`, `/migrations/`, `/cron/`
- Gzip compression via `mod_deflate` for text-based assets
- Browser caching via `mod_expires` (images 1 month, CSS/JS 1 week, fonts 1 month)
- PHP settings: `display_errors off`, `upload_max_filesize 64M`, `post_max_size 64M`, `max_execution_time 300`

### CORS
Whitelist-based CORS in `src/includes/security.php`:
- Production: `https://edutrackzambia.com` and `www` variant
- Development: localhost variants allowed
- Preflight OPTIONS handled automatically

### Important Security Notes
1. **Delete `public/install.php` after installation** (if present).
2. **Protect `.env` file** - contains credentials. It is blocked by `.htaccess` but verify server config.
3. **Secure `storage/` directory** - contains logs and sessions. Must be writable (755) but outside web root access.
4. **Regenerate keys** in production (`ENCRYPTION_KEY`, `JWT_SECRET`, `CRON_SECRET_KEY`).
5. **Google Drive folder ID is hardcoded** in `config/app.php` (`GOOGLE_DRIVE_FOLDER_ID`).
6. **Cron scripts have a default fallback secret** (`change_this_secret`) if `CRON_SECRET_KEY` is not set in `.env`.

---

## Key Features and Integrations

### Payment Processing
- **Lenco** is the primary payment gateway (bank transfer via virtual accounts + V2 mobile money collections).
- Supports sandbox and live modes.
- Webhook endpoint: `public/api/lenco-webhook.php`
- Additional methods: MTN Mobile Money, Airtel Money, Zamtel Kwacha, manual bank transfer.
- Payment callbacks: `public/api/payment-callback.php`
- Currency: ZMW (symbol `K`)
- **30% minimum payment rule**: The `Enrollment` class enforces that students must pay at least 30% before course content is unlocked (`canAccessContent()`). Certificates require 100% payment (`canDownloadCertificate()`).

### Email System
- PHPMailer with Gmail SMTP.
- Email queue system (`email_queue` table) for bulk/reliable sending.
- Templates in `src/mail/`: welcome, enrollment confirmation, password reset, certificate issued, payment received, announcement notification.
- Cron job processes queue every 5 minutes.
- Rate limiting: 10 per minute, 100 per hour.

### Certificates
- Auto-generated PDF certificates using **Dompdf** (primary) with **TCPDF** fallback.
- TEVETA accreditation branding.
- Unique certificate numbers with public verification page (`certificate-verify.php`).
- Certificates stored in `public/uploads/certificates/generated/`.

### Live Sessions
- Jitsi Meet integration for virtual classes (domain: `meet.jit.si`, room prefix: `edutrack_zm`).
- Scheduled live sessions per course.
- Attendance tracking.
- Recording support (configured in Jitsi settings).
- Reminder notifications at 30 minutes, 5 minutes, and at start time.

### Google Integration
- Google OAuth for login/signup (`public/google-callback.php`).
- Google Drive for file storage (`GoogleDriveService` class).
- Drive folder ID hardcoded in `config/app.php`.

### File Uploads
- Max size: 50MB (configurable).
- Allowed images: jpg, jpeg, png, gif, webp.
- Allowed documents: pdf, doc, docx, xls, xlsx, ppt, pptx, txt.
- Upload paths organized by type: `uploads/courses/thumbnails/`, `uploads/assignments/submissions/`, `uploads/users/avatars/`, `uploads/payments/proofs/`, `uploads/certificates/generated/`.

---

## External Services

| Service | Purpose | Configuration |
|---------|---------|---------------|
| Gmail SMTP | Email sending | `MAIL_*` env vars |
| Google OAuth | Social login | `GOOGLE_CLIENT_*` env vars |
| Google Drive | File storage | `config/app.php` + `config/google-credentials.json` |
| Lenco | Payment processing | `LENCO_*` env vars |
| Jitsi Meet | Video conferencing | `config/app.php` |
| YouTube API | Video embedding | `YOUTUBE_API_KEY` env var |

---

## Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check `storage/logs/php-errors.log`
- Verify `.env` file exists and is readable
- Check file permissions on `storage/` (must be writable)
- Ensure `vendor/autoload.php` exists (run `composer install`)

**Database Connection Failed**
- Verify DB credentials in `.env`
- Check MySQL/MariaDB service is running
- Confirm database exists and user has privileges

**Emails Not Sending**
- Check Gmail app-specific password is correct
- Review `storage/logs/cron-email.log`
- Verify cron job is running or trigger manually via URL with secret key

**File Uploads Failing**
- Check `uploads/` directory permissions (755)
- Verify PHP `upload_max_filesize` and `post_max_size` settings
- Check available disk space

**Pretty URLs / Routing Not Working**
- Ensure Apache `mod_rewrite` is enabled
- Verify `.htaccess` files exist in root and `public/`
- Check `AllowOverride All` is set in Apache virtual host config

**Tailwind CSS Styles Missing**
- Run `npm run build:css` to regenerate `public/assets/css/tailwind.css`
- Check that `tailwind.config.js` content paths include your new files

---

## File Statistics (Approximate)
- **Total PHP Lines**: ~30,000+
- **Classes**: 32 domain models
- **API Endpoints**: 30 REST endpoints
- **Database Tables**: 50+ tables and views
- **Public Pages**: 152 PHP files
- **Migrations**: 19 SQL migration files
- **Includes**: 13 core framework files

---

## Contact & Support
- **Organization**: Edutrack Computer Training College
- **Location**: Kalomo, Zambia
- **Email**: edutrackzambia@gmail.com / edutrackcomputertrainingschool@gmail.com
- **Phone**: +260 770 666 937 / +260 965 992 967
- **TEVETA Registration**: TVA/2064

---

*Last Updated: May 2026*
*For AI Agent Use - Keep this document current with code changes*
