# Edutrack LMS

Edutrack LMS is a Laravel 10.x web application for Edutrack Computer Training College, a TEVETA-registered vocational training institution based in Kalomo, Zambia.

## Technology Stack

| Component | Technology |
|-----------|------------|
| **Backend** | PHP 8.1+, Laravel 10.x |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ |
| **Frontend** | Tailwind CSS 3.x, Alpine.js, Vite |
| **Auth** | Session-based + Google OAuth (Laravel Socialite) |
| **Payments** | Lenco (primary), MTN/Airtel/Zamtel Mobile Money, Bank Transfer |
| **Email** | PHPMailer with Gmail SMTP |
| **PDF** | TCPDF |
| **QR Codes** | Simple QR Code |

## Local Setup (XAMPP)

### Prerequisites
- XAMPP with PHP 8.1+ and MySQL/MariaDB
- Composer
- Node.js 18+ and npm

### Installation

1. **Clone or extract** the project into your XAMPP `htdocs/` folder:
   ```
   C:\xampp\htdocs\edutrack-lms\
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node dependencies and build assets:**
   ```bash
   npm install
   npm run build
   ```

4. **Environment configuration:**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

   Edit `.env` and set your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=edutrack-lms
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Database setup:**
   - Import `database/complete_lms_schema.sql` into your MySQL server
   - Mark migrations as run (if starting fresh with existing schema):
     ```bash
     php artisan migrate:status
     ```
   - If migrations table is missing, run:
     ```bash
     php artisan migrate
     ```

6. **Storage link:**
   ```bash
   php artisan storage:link
   ```

7. **Access the application:**
   ```
   http://localhost/edutrack-lms/public/
   ```

   Or configure an Apache virtual host pointing to the `public/` directory.

### Default Admin Account
If the database was imported from `complete_lms_schema.sql`, existing users are preserved.
- Email: `admin@edutrack.edu`
- Password: (same as before migration - bcrypt hashes are compatible)

## User Roles

| Role | ID | Access |
|------|-----|--------|
| Super Admin | 1 | Full system access |
| Admin | 2 | Administrative access |
| Instructor | 3 | Create/manage courses |
| Student | 4 | Enroll and learn |
| Content Creator | 5 | Create course content |
| Finance | 6 | Financial operations |

## Key Features

- **Course Management** - Categories, modules, lessons, quizzes, assignments
- **Enrollment System** - Student enrollment with payment tracking
- **Payment Processing** - Lenco integration, mobile money, bank transfer
- **Certificates** - Auto-generated PDF certificates with verification
- **Live Sessions** - Jitsi Meet integration for virtual classes
- **Discussions** - Course discussion forums
- **Assignments** - File/text submissions with instructor grading
- **Notifications** - In-app and email notifications
- **Admin Panel** - User management, financial reports, settings

## Project Structure

```
edutrack-lms/
├── app/                  # Application logic
│   ├── Http/Controllers/ # Controllers (Admin, Instructor, Student, Auth, Api)
│   ├── Models/           # Eloquent models
│   └── Services/         # Business logic services
├── config/               # Laravel configuration
├── database/
│   ├── migrations/       # Database migrations
│   └── seeders/          # Data seeders
├── public/               # Web document root
│   ├── assets/           # CSS, JS, images
│   ├── uploads/          # User-generated content
│   └── build/            # Compiled Vite assets
├── resources/
│   ├── views/            # Blade templates
│   ├── css/              # Tailwind CSS entry
│   └── js/               # Alpine.js entry
├── routes/
│   ├── web.php           # Web routes
│   └── api.php           # API routes
└── storage/              # Logs, sessions, cache
```

## Useful Commands

```bash
# Start development server
php artisan serve

# Watch assets during development
npm run dev

# Build production assets
npm run build

# Clear caches
php artisan optimize:clear

# Check route list
php artisan route:list

# Run migrations
php artisan migrate

# Check migration status
php artisan migrate:status
```

## License

Proprietary - Edutrack Computer Training College
