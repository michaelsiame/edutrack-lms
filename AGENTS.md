<!-- AGENTS.md - Edutrack LMS -->
# Edutrack LMS - AI Agent Documentation

## Project Overview

Edutrack LMS is a **Laravel 10.x web application** for Edutrack Computer Training College, a TEVETA-registered vocational training institution based in Kalomo, Zambia. The system manages online courses, student enrollments, payments, certificates, live virtual classes, assignments, quizzes, and discussions.

**Key Facts:**
- **Type**: Laravel 10.x monolithic web application with server-side rendering (Blade) and a small REST API (Sanctum)
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
| **Backend** | PHP 8.1+, Laravel 10.x |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ |
| **Frontend** | Tailwind CSS 3.x, Alpine.js, Vite |
| **Package Manager** | Composer, npm |
| **Auth** | Laravel session + Laravel Sanctum (API) + Google OAuth (Laravel Socialite) |
| **Email** | PHPMailer with Gmail SMTP; email queue via `email_queue` table |
| **PDF Generation** | TCPDF |
| **QR Codes** | SimpleSoftwareIO Simple QR Code |
| **Payment Gateway** | Lenco (primary), MTN Mobile Money, Airtel Money, Zamtel Kwacha, Bank Transfer |
| **Live Video** | Jitsi Meet |
| **HTML Sanitization** | ezyang/htmlpurifier |

### Composer Dependencies (`composer.json`)
Key runtime dependencies include `laravel/framework ^10.0`, `laravel/sanctum ^3.2`, `laravel/socialite ^5.27`, `phpmailer/phpmailer ^6.8`, `tecnickcom/tcpdf ^6.6`, `google/apiclient ^2.18`, `guzzlehttp/guzzle ^7.2`, `simplesoftwareio/simple-qrcode ^4.2`, and `ezyang/htmlpurifier ^4.19`.

### npm Dependencies (`package.json`)
Dev dependencies include `vite ^5.0.0`, `tailwindcss ^3.4.0`, `alpinejs ^3.15.12`, `laravel-vite-plugin ^1.0.0`, `autoprefixer ^10.4.2`, and `postcss ^8.4.31`.

---

## Project Structure

```
edutrack-lms/
├── app/                           # Application logic
│   ├── Console/Commands/          # Custom Artisan commands (4 commands)
│   ├── Exceptions/                # Exception handler
│   ├── Http/
│   │   ├── Controllers/           # 49 controllers (Admin, Instructor, Student, Auth, API, Finance)
│   │   ├── Middleware/            # 11 middleware (role-based + Laravel defaults)
│   │   └── Kernel.php             # HTTP kernel with custom middleware aliases
│   ├── Jobs/                      # 3 queued jobs (email sending)
│   ├── Models/                    # 54 Eloquent models
│   ├── Policies/                  # CoursePolicy
│   ├── Providers/                 # App, Auth, Event, Route service providers
│   ├── Services/                  # 7 business-logic services
│   └── helpers.php                # Global `setting()` helper
├── bootstrap/                     # Laravel bootstrap files
├── config/                        # Laravel configuration files (15 files)
├── database/
│   ├── factories/                 # Model factories
│   ├── migrations/                # 77 migration files
│   ├── seeders/                   # 18 seeders + DatabaseSeeder
│   └── complete_lms_schema.sql    # Full schema dump for fresh installs
├── public/                        # Web document root
│   ├── assets/                    # Static CSS, JS, images, fonts, Font Awesome
│   ├── build/                     # Compiled Vite assets (manifest.json)
│   ├── uploads/                   # User-generated content (avatars, certificates, submissions, etc.)
│   └── index.php                  # Laravel entry point
├── resources/
│   ├── css/app.css                # Tailwind CSS entry (@tailwind directives)
│   ├── js/app.js                  # Alpine.js entry
│   └── views/                     # 142 Blade templates
├── routes/
│   ├── api.php                    # Sanctum-protected REST API routes
│   └── web.php                    # Web routes (340 lines)
├── storage/                       # Logs, sessions, cache, app uploads
├── tests/                         # Empty (Feature/ and Unit/ directories exist)
├── .env / .env.example            # Environment configuration
├── composer.json / composer.lock  # PHP dependencies
├── package.json / package-lock.json # Node dependencies
├── vite.config.js                 # Vite build configuration
├── artisan                        # Laravel CLI entry point
└── README.md                      # Human-readable project overview
```

---

## Architecture Patterns

### 1. MVC with Service Layer
Controllers handle HTTP concerns and delegate business logic to **Services** in `app/Services/`:
- `CertificateService` — PDF generation, certificate numbering, verification codes
- `LencoPaymentService` — Payment initialization, webhook handling, polling, status mapping
- `EmailQueueService` — Queued email dispatch via `email_queue` table
- `InvoiceService` — Invoice generation
- `PaymentVerificationService` — Manual payment verification
- `LessonExportService` — Lesson content export
- `HtmlSanitizer` — Input sanitization with HTMLPurifier

### 2. Eloquent Models
54 models in `app/Models/` follow standard Laravel conventions. Key models:
- `User` — Authenticatable with roles, soft deletes, `password_hash` field (hashed via bcrypt)
- `Course` — Soft deletes, scopes `published()` and `featured()`, formatted price accessor
- `Enrollment` — Tracks student progress, payment status, certificate blocking
- `Payment` / `LencoTransaction` / `LencoWebhookLog` — Payment pipeline
- `Certificate` — TEVETA-accredited certificates with verification codes
- `Setting` / `SystemSetting` — Dual setting systems (new settings table + legacy system_settings)

### 3. Role-Based Middleware
Custom middleware aliases registered in `app/Http/Kernel.php`:
- `admin` — Super Admin or Admin (roles 1 or 2)
- `instructor` — Instructor role (3)
- `finance` — Finance role (6)
- `student` — Student role (4)
- `enrolled` — Must be enrolled in the specific course

The `User` model provides convenience methods: `isAdmin()`, `isInstructor()`, `isFinance()`, `isStudent()`, `hasRole(int)`, `isEnrolledIn(int)`.

### 4. Database Pattern
- **Migrations**: 77 files in `database/migrations/`. The project also provides `database/complete_lms_schema.sql` for fresh installations.
- **Seeders**: 18 seeders including demo data and a `MigrateLegacyData` seeder for migrating from the old custom PHP schema (`edutrack_legacy` database).
- All queries use Eloquent or Query Builder (prepared statements).

### 5. Route Organization
- `routes/web.php`: 340 lines. Public pages, auth (session + Google OAuth), role-prefixed dashboards (`/admin`, `/instructor`, `/student`, `/finance`), and the Lenco webhook endpoint.
- `routes/api.php`: 45 lines. Sanctum-protected API for courses, enrollments, progress, certificates, notifications, and quizzes. Plus public certificate verification and promotion validation.

### 6. Frontend Build
Vite compiles `resources/css/app.css` and `resources/js/app.js` into `public/build/`. Tailwind CSS is processed via PostCSS. Alpine.js handles lightweight frontend interactivity.

---

## User Roles & Permissions

Roles are stored in the `roles` table and linked via `user_roles`:

| Role | ID | Capabilities |
|------|-----|--------------|
| **Super Admin** | 1 | Full system access |
| **Admin** | 2 | Administrative access (users, courses, payments, settings) |
| **Instructor** | 3 | Create/edit courses, grade assignments, manage live sessions, view analytics |
| **Student** | 4 | Enroll, take lessons, submit assignments, take quizzes |
| **Content Creator** | 5 | Create course content |
| **Finance** | 6 | Manage payments, invoices, financial reports |

---

## Configuration

### Environment Variables (`.env`)
Standard Laravel `.env` with Edutrack-specific additions:

```bash
# Application
APP_NAME="Edutrack LMS"
APP_ENV=production|development
APP_DEBUG=false|true
APP_URL=https://edutrackzambia.com
APP_TIMEZONE=Africa/Lusaka
APP_CURRENCY=ZMW
APP_LOCALE=en

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edutrack-lms
DB_USERNAME=root
DB_PASSWORD=

# Mail (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=edutrackzambia@gmail.com
MAIL_PASSWORD="app_specific_password"

# Google OAuth
GOOGLE_CLIENT_ID="..."
GOOGLE_CLIENT_SECRET="..."
GOOGLE_REDIRECT_URI="..."

# Payment (Lenco)
LENCO_API_KEY="..."
LENCO_SECRET_KEY="..."
LENCO_WEBHOOK_SECRET="..."
```

### Config Files (`config/`)
- `config/app.php` — Standard Laravel app config with Edutrack additions: `currency` => `ZMW`, feature toggles array.
- `config/services.php` — Contains `google` and `lenco` service configurations.
- `config/filesystems.php` — Includes a custom disk `public_uploads` pointing to `public/uploads`.
- Other configs are standard Laravel 10 defaults.

---

## Build and Deployment

### Local Setup Commands
```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies and build assets
npm install
npm run build

# 3. Environment setup
copy .env.example .env
php artisan key:generate
# Edit .env with database credentials and service keys

# 4. Database setup
# Option A: Import full schema dump
mysql -u root -p edutrack-lms < database/complete_lms_schema.sql
# Option B: Run migrations (if starting from scratch)
php artisan migrate

# 5. Link storage directory
php artisan storage:link

# 6. Optional: seed demo data
php artisan db:seed
```

### Development Commands
```bash
# Start Laravel dev server
php artisan serve

# Watch frontend assets during development
npm run dev

# Build production frontend assets
npm run build

# Clear all caches
php artisan optimize:clear

# List all routes
php artisan route:list

# Check migration status
php artisan migrate:status
```

### Server Requirements
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- Composer and Node.js 18+ with npm
- SSL certificate (HTTPS required for Google OAuth and secure cookies)
- `mbstring`, `openssl`, `pdo_mysql`, `gd` PHP extensions recommended

### Document Root
Apache must be configured to point to the `public/` directory. Do not serve from the project root.

### Cron Jobs / Scheduled Commands
The following custom Artisan commands should be run on a schedule:

```bash
# Process email queue every 5 minutes
php artisan email:process --limit=50

# Poll Lenco for pending payments every 15 minutes
php artisan lenco:poll-payments --hours=24 --limit=50

# Send session and progress reminders daily
php artisan reminders:send
```

These can be added to the system crontab or triggered via HTTP if CLI cron is unavailable.

---

## Code Style Guidelines

Observed conventions across the codebase:

- **Indentation**: 4 spaces (no tabs)
- **PHP Tags**: Always use `<?php`
- **Comments**: PHPDoc format for functions and classes
- **Naming**:
  - Classes: PascalCase (e.g., `LencoPaymentService`, `CertificateService`)
  - Functions/Methods: camelCase (e.g., `initializePayment`, `generatePdf`)
  - Variables: camelCase or snake_case (mixed; Eloquent attributes typically snake_case)
  - Constants: UPPER_CASE
  - Database tables: snake_case, plural (e.g., `user_profiles`, `live_sessions`)
  - Database columns: snake_case (e.g., `created_at`, `password_hash`)
- **PHP 8.1+ Features**: Typed properties, `match` expressions, union types, named arguments, arrow functions, and constructor property promotion are used where appropriate.
- **Laravel Conventions**:
  - Controllers extend `App\Http\Controllers\Controller`
  - Models extend `Illuminate\Database\Eloquent\Model`
  - Form requests validated with `$request->validate([...])`
  - Redirects with flash messages: `return redirect()->route('...')->with('success', '...')`

### Adding New Pages
1. Create a controller in the appropriate `app/Http/Controllers/` subdirectory.
2. Add routes in `routes/web.php` with the appropriate middleware group.
3. Create Blade templates in `resources/views/` following existing directory structure.
4. Use `Route::get('/path', [Controller::class, 'method'])->name('route.name');`

### Database Queries
Always use Eloquent or Query Builder:
```php
// Good
User::where('email', $email)->first();
DB::table('users')->where('email', $email)->first();

// Bad - Never do this
DB::raw("SELECT * FROM users WHERE email = '$email'")
```

### Form Handling
Laravel's CSRF protection is enabled by default for web routes. Use `@csrf` in Blade forms.

---

## Testing

**Current State: No automated test suite is configured.**

- `phpunit/phpunit` is listed in `require-dev` in `composer.json`.
- There is no `phpunit.xml` or `phpunit.xml.dist` file.
- There are zero `*.php` test files in `tests/` (the `Feature/` and `Unit/` directories are empty).
- The `.gitignore` excludes `/phpunit.xml` and `/.phpunit.result.cache`.

### Manual Testing Strategy
Test these user flows manually:
1. **Student Flow**: Registration → Email Verification → Login → Browse Courses → Enrollment → Payment → Learning → Quiz → Assignment → Certificate
2. **Instructor Flow**: Login → Create Course → Add Modules/Lessons → Create Quiz → Review Submissions → Grade Assignments → Schedule Live Session
3. **Admin Flow**: Login → User Management → Course Approval → Financial Reports → Payment Verification → Announcements → Settings
4. **Finance Flow**: Login → View Transactions → Verify Payments → Manage Invoices

### Debug Mode
Enable in `.env`:
```bash
APP_DEBUG=true
APP_ENV=local
```
This enables Laravel's detailed error pages (Ignition) and debug logging.

---

## Security Considerations

### Implemented Protections
- **CSRF Tokens**: Laravel's built-in CSRF protection on all web routes.
- **Authentication**: Session-based with bcrypt password hashing. The `User` model stores passwords in `password_hash`.
- **API Auth**: Laravel Sanctum for API token authentication.
- **Google OAuth**: Via Laravel Socialite; `google_id` stored on users table.
- **Role-Based Access**: Custom middleware (`admin`, `instructor`, `finance`, `student`, `enrolled`) applied to route groups.
- **SQL Injection**: Protected by Eloquent/Query Builder prepared statements.
- **XSS Protection**: Output escaping with `{{ }}` in Blade; `HtmlPurifier` service for rich content sanitization.
- **File Uploads**: Allowed extensions whitelist; uploads stored in `public/uploads/` organized by type.
- **Webhook Security**: Lenco webhooks validate HMAC-SHA256 signatures using `LENCO_WEBHOOK_SECRET`.
- **Rate Limiting**: Default Laravel API throttle applied to API routes.

### Important Security Notes
1. **Protect `.env` file** — contains credentials. Blocked by `.htaccess` and server config, but verify.
2. **Secure `storage/` directory** — must be writable (755) but outside direct web access where possible.
3. **Google OAuth credentials** — ensure redirect URIs are exact matches in Google Cloud Console.
4. **Lenco webhook secret** — must match the value configured in the Lenco dashboard.
5. **Default admin account** — If using seeded/demo data, change default credentials immediately in production.

---

## Custom Artisan Commands

| Command | Purpose |
|---------|---------|
| `php artisan migrate:data {--source-db=} {--batch=} {--table=}` | Migrate data from a legacy `edutrack_legacy` database into the Laravel schema. |
| `php artisan lenco:poll-payments {--hours=24} {--limit=50}` | Poll pending Lenco transactions for status updates (fallback when webhooks fail). |
| `php artisan email:process {--limit=50}` | Send pending emails from the `email_queue` table. |
| `php artisan reminders:send` | Queue email reminders for upcoming live sessions and students with low progress. |

---

## External Services

| Service | Purpose | Configuration |
|---------|---------|---------------|
| Gmail SMTP | Email sending | `MAIL_*` env vars |
| Google OAuth | Social login | `GOOGLE_CLIENT_*` env vars |
| Lenco | Payment processing | `LENCO_*` env vars + `config/services.php` |
| Jitsi Meet | Video conferencing | Configured in views/admin settings |
| YouTube API | Video embedding | `YOUTUBE_API_KEY` env var |

---

## Troubleshooting

### 500 Internal Server Error
- Check `storage/logs/laravel.log`
- Verify `.env` file exists and `APP_KEY` is generated
- Ensure `storage/` and `bootstrap/cache/` are writable
- Run `composer install` and `npm run build`

### Database Connection Failed
- Verify DB credentials in `.env`
- Check MySQL/MariaDB service is running
- Confirm database exists and user has privileges

### Emails Not Sending
- Check Gmail app-specific password is correct
- Verify `MAIL_MAILER=smtp` and Gmail settings in `.env`
- Ensure `php artisan email:process` is running on schedule
- Check `storage/logs/laravel.log` for PHPMailer errors

### File Uploads Failing
- Check `public/uploads/` directory permissions (writable by web server)
- Verify PHP `upload_max_filesize` and `post_max_size` settings
- Check available disk space

### Pretty URLs / Routing Not Working
- Ensure Apache `mod_rewrite` is enabled
- Verify `.htaccess` exists in `public/`
- Check `AllowOverride All` is set in Apache virtual host config

### Vite / CSS Not Loading
- Run `npm run build` to generate `public/build/manifest.json`
- Ensure `VITE_APP_URL` or `APP_URL` in `.env` is correct
- Check that `public/build/` directory exists and contains assets

---

## File Statistics (Approximate)
- **Total PHP Lines**: ~35,000+
- **Eloquent Models**: 54
- **Controllers**: 49
- **Blade Templates**: 142
- **Database Migrations**: 77
- **Database Seeders**: 18
- **API Endpoints**: 15+ REST endpoints
- **Custom Artisan Commands**: 4
- **Custom Middleware**: 6 role-based + 5 standard

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
