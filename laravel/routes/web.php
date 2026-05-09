<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/certificates/verify/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'show'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'show'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
    Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Google OAuth
|--------------------------------------------------------------------------
*/

Route::get('/auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Enrollment
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::get('/my-courses', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/my-courses/{course}', [EnrollmentController::class, 'show'])->name('enrollments.show');

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('courses', App\Http\Controllers\Admin\CourseController::class);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::resource('payments', App\Http\Controllers\Admin\PaymentController::class);
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
});

/*
|--------------------------------------------------------------------------
| Instructor Routes
|--------------------------------------------------------------------------
*/

Route::prefix('instructor')->middleware(['auth', 'instructor'])->name('instructor.')->group(function () {
    Route::get('/dashboard', [InstructorDashboardController::class, 'index'])->name('dashboard');
    Route::resource('courses', App\Http\Controllers\Instructor\CourseController::class);
    Route::resource('lessons', App\Http\Controllers\Instructor\LessonController::class);
    Route::resource('quizzes', App\Http\Controllers\Instructor\QuizController::class);
    Route::resource('assignments', App\Http\Controllers\Instructor\AssignmentController::class);
    Route::get('/submissions', [InstructorDashboardController::class, 'submissions'])->name('submissions');
    Route::get('/analytics', [InstructorDashboardController::class, 'analytics'])->name('analytics');
});

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/

Route::prefix('student')->middleware(['auth', 'student'])->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/progress', [StudentDashboardController::class, 'progress'])->name('progress');
    Route::get('/payments', [StudentDashboardController::class, 'payments'])->name('payments');
    Route::get('/certificates', [StudentDashboardController::class, 'certificates'])->name('certificates');
});

/*
|--------------------------------------------------------------------------
| Finance Routes
|--------------------------------------------------------------------------
*/

Route::prefix('finance')->middleware(['auth', 'finance'])->name('finance.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Finance\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [App\Http\Controllers\Finance\DashboardController::class, 'transactions'])->name('transactions');
    Route::get('/invoices', [App\Http\Controllers\Finance\DashboardController::class, 'invoices'])->name('invoices');
});

/*
|--------------------------------------------------------------------------
| Payment Webhook
|--------------------------------------------------------------------------
*/

Route::post('/lenco/webhook', [App\Http\Controllers\Payment\LencoWebhookController::class, 'handle'])->name('lenco.webhook');
