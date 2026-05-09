# Edutrack LMS - Laravel Migration

This directory contains the Laravel migration of the Edutrack LMS project.

## Migration Status

### Completed
- [x] Laravel skeleton setup
- [x] Core migrations (13 tables)
- [x] Eloquent models with relationships
- [x] Role-based middleware
- [x] Route structure (web, api, console)
- [x] Core controllers
- [x] Base Blade layout with Tailwind CSS
- [x] Authentication views (login, register)

### Pending
- [ ] Remaining 43+ migrations (questions, quiz_attempts, assignment_submissions, etc.)
- [ ] Service providers configuration
- [ ] Google OAuth integration setup
- [ ] Lenco payment integration
- [ ] Certificate PDF generation service
- [ ] Email notification system
- [ ] Queue jobs
- [ ] Testing suite
- [ ] Data migration scripts

## Setup Instructions

1. Install dependencies:
```bash
composer install
npm install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Generate application key:
```bash
php artisan key:generate
```

4. Configure database in `.env`

5. Run migrations:
```bash
php artisan migrate
```

6. Build assets:
```bash
npm run build
```

7. Start development server:
```bash
php artisan serve
```

## Architecture

### Models
- `User` - Authentication and roles
- `Course` - Course content
- `Enrollment` - Student enrollments
- `Certificate` - PDF certificates
- `Payment` - Payment records
- `Instructor` - Instructor profiles
- `Module` - Course modules
- `Lesson` - Individual lessons
- `Quiz` / `Assignment` - Assessments

### Middleware
- `AdminMiddleware` - Admin access
- `InstructorMiddleware` - Instructor access
- `FinanceMiddleware` - Finance access
- `StudentMiddleware` - Student access
- `EnrolledMiddleware` - Course enrollment check

### Controllers
Organized by role:
- `App\Http\Controllers\Auth\*` - Authentication
- `App\Http\Controllers\Admin\*` - Admin panel
- `App\Http\Controllers\Instructor\*` - Instructor dashboard
- `App\Http\Controllers\Student\*` - Student dashboard
- `App\Http\Controllers\Finance\*` - Finance panel
- `App\Http\Controllers\Api\*` - API endpoints

## Migration Plan

1. **Phase 1: Foundation** (Current)
   - Laravel structure
   - Core migrations and models
   - Auth system
   - Basic views

2. **Phase 2: Features**
   - Course management
   - Enrollment system
   - Payment integration
   - Certificate generation

3. **Phase 3: Advanced**
   - Quiz system
   - Assignment system
   - Live sessions
   - Notifications

4. **Phase 4: Production**
   - Data migration
   - Testing
   - Performance optimization
   - Deployment
