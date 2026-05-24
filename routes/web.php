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
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/campus', [HomeController::class, 'campus'])->name('campus');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/testimonials', [HomeController::class, 'testimonials'])->name('testimonials');
Route::get('/events', [HomeController::class, 'events'])->name('events');
Route::get('/events/{event}', [HomeController::class, 'showEvent'])->name('events.show');
Route::get('/terms', function () { return view('pages.terms'); })->name('terms');
Route::get('/privacy', function () { return view('pages.privacy'); })->name('privacy');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search');

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

// Email Verification
Route::get('/verify-email', [App\Http\Controllers\Auth\VerifyEmailController::class, 'notice'])->name('verification.notice');
Route::get('/verify-email/{token}', [App\Http\Controllers\Auth\VerifyEmailController::class, 'verify'])->name('verification.verify');
Route::post('/verify-email/resend', [App\Http\Controllers\Auth\VerifyEmailController::class, 'resend'])->name('verification.resend');

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

    // Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Transcript
    Route::get('/transcript/download', [App\Http\Controllers\Student\TranscriptController::class, 'download'])->name('transcript.download');

    // Enrollment
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::get('/my-courses', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/my-courses/{course}', [EnrollmentController::class, 'show'])->name('enrollments.show');

    // Checkout & Payments
    Route::get('/courses/{course}/checkout', [App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/courses/{course}/checkout', [App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/payment/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed', [App\Http\Controllers\CheckoutController::class, 'failed'])->name('payment.failed');

    // Registration Fee
    Route::get('/registration-fee', [App\Http\Controllers\RegistrationFeeController::class, 'show'])->name('registration-fee.show');
    Route::post('/registration-fee', [App\Http\Controllers\RegistrationFeeController::class, 'store'])->name('registration-fee.store');

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
});

// Public certificate preview (Tailwind CSS browser view)
Route::get('/certificate-preview/{certificate?}', [CertificateController::class, 'preview'])->name('certificates.preview');

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

    // Announcements
    Route::resource('announcements', App\Http\Controllers\Admin\AnnouncementController::class)->except(['show']);

    // Institution Photos
    Route::get('/photos', [App\Http\Controllers\Admin\InstitutionPhotoController::class, 'index'])->name('photos.index');
    Route::post('/photos', [App\Http\Controllers\Admin\InstitutionPhotoController::class, 'store'])->name('photos.store');
    Route::put('/photos/{photo}', [App\Http\Controllers\Admin\InstitutionPhotoController::class, 'update'])->name('photos.update');
    Route::delete('/photos/{photo}', [App\Http\Controllers\Admin\InstitutionPhotoController::class, 'destroy'])->name('photos.destroy');

    // Events
    Route::get('/events', [App\Http\Controllers\Admin\EventController::class, 'index'])->name('events.index');
    Route::post('/events', [App\Http\Controllers\Admin\EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [App\Http\Controllers\Admin\EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [App\Http\Controllers\Admin\EventController::class, 'destroy'])->name('events.destroy');

    // Enrollments
    Route::get('/enrollments', [App\Http\Controllers\Admin\EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::put('/enrollments/{enrollment}', [App\Http\Controllers\Admin\EnrollmentController::class, 'update'])->name('enrollments.update');
    Route::delete('/enrollments/{enrollment}', [App\Http\Controllers\Admin\EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

    // Email Templates
    Route::get('/templates', [App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates', [App\Http\Controllers\Admin\EmailTemplateController::class, 'store'])->name('templates.store');
    Route::put('/templates/{template}', [App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('templates.update');
    Route::delete('/templates/{template}', [App\Http\Controllers\Admin\EmailTemplateController::class, 'destroy'])->name('templates.destroy');

    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
    Route::get('/reports/export/{type}', [AdminDashboardController::class, 'exportReport'])->name('reports.export');
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminDashboardController::class, 'updateSettings'])->name('settings.update');

    // Badges & Achievements
    Route::get('/badges', [App\Http\Controllers\Admin\BadgeController::class, 'index'])->name('badges.index');
    Route::post('/badges', [App\Http\Controllers\Admin\BadgeController::class, 'store'])->name('badges.store');
    Route::post('/badges/{badge}/toggle', [App\Http\Controllers\Admin\BadgeController::class, 'toggle'])->name('badges.toggle');
    Route::delete('/badges/{badge}', [App\Http\Controllers\Admin\BadgeController::class, 'destroy'])->name('badges.destroy');

    // Newsletter Subscribers
    Route::get('/newsletter', [App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
    Route::post('/newsletter/{subscriber}/toggle', [App\Http\Controllers\Admin\NewsletterController::class, 'toggle'])->name('newsletter.toggle');
    Route::delete('/newsletter/{subscriber}', [App\Http\Controllers\Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');
});

/*
|--------------------------------------------------------------------------
| Instructor Routes
|--------------------------------------------------------------------------
*/

Route::prefix('instructor')->middleware(['auth', 'instructor'])->name('instructor.')->group(function () {
    Route::get('/dashboard', [InstructorDashboardController::class, 'index'])->name('dashboard');
    Route::resource('courses', App\Http\Controllers\Instructor\CourseController::class);

    // Module CRUD
    Route::post('/courses/{course}/modules', [App\Http\Controllers\Instructor\ModuleController::class, 'store'])->name('courses.modules.store');
    Route::put('/courses/{course}/modules/{module}', [App\Http\Controllers\Instructor\ModuleController::class, 'update'])->name('courses.modules.update');
    Route::delete('/courses/{course}/modules/{module}', [App\Http\Controllers\Instructor\ModuleController::class, 'destroy'])->name('courses.modules.destroy');

    // Lesson CRUD
    Route::post('/courses/{course}/modules/{module}/lessons', [App\Http\Controllers\Instructor\LessonController::class, 'store'])->name('courses.modules.lessons.store');
    Route::put('/courses/{course}/modules/{module}/lessons/{lesson}', [App\Http\Controllers\Instructor\LessonController::class, 'update'])->name('courses.modules.lessons.update');
    Route::delete('/courses/{course}/modules/{module}/lessons/{lesson}', [App\Http\Controllers\Instructor\LessonController::class, 'destroy'])->name('courses.modules.lessons.destroy');

    // Lesson Versions
    Route::get('/courses/{course}/modules/{module}/lessons/{lesson}/versions', [App\Http\Controllers\Instructor\LessonVersionController::class, 'index'])->name('lessons.versions');
    Route::put('/courses/{course}/modules/{module}/lessons/{lesson}/versions/{version}/restore', [App\Http\Controllers\Instructor\LessonVersionController::class, 'restore'])->name('lessons.versions.restore');

    // Lesson Resources
    Route::post('/courses/{course}/modules/{module}/lessons/{lesson}/resources', [App\Http\Controllers\Instructor\LessonResourceController::class, 'store'])->name('courses.modules.lessons.resources.store');
    Route::delete('/courses/{course}/modules/{module}/lessons/{lesson}/resources/{resource}', [App\Http\Controllers\Instructor\LessonResourceController::class, 'destroy'])->name('courses.modules.lessons.resources.destroy');

    // Assignments
    Route::get('/assignments', [App\Http\Controllers\Instructor\AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/courses/{course}/assignments', [App\Http\Controllers\Instructor\AssignmentController::class, 'store'])->name('courses.assignments.store');
    Route::post('/courses/{course}/assignments/{assignment}/submissions/{submission}/grade', [App\Http\Controllers\Instructor\AssignmentController::class, 'grade'])->name('courses.assignments.grade');

    Route::get('/submissions', [InstructorDashboardController::class, 'submissions'])->name('submissions');
    Route::get('/progress', [InstructorDashboardController::class, 'progress'])->name('progress');
    Route::get('/analytics', [InstructorDashboardController::class, 'analytics'])->name('analytics');

    // Live Sessions
    Route::get('/courses/{course}/live-sessions', [App\Http\Controllers\Instructor\LiveSessionController::class, 'index'])->name('live-sessions.index');
    Route::post('/courses/{course}/live-sessions', [App\Http\Controllers\Instructor\LiveSessionController::class, 'store'])->name('live-sessions.store');
    Route::delete('/courses/{course}/live-sessions/{session}', [App\Http\Controllers\Instructor\LiveSessionController::class, 'destroy'])->name('live-sessions.destroy');

    // Quizzes
    Route::post('/upload/lesson-image', [App\Http\Controllers\Instructor\LessonImageController::class, 'store'])->name('upload.lesson-image');

    Route::resource('quizzes', App\Http\Controllers\Instructor\QuizController::class);

    // Quiz Questions
    Route::get('/quizzes/{quiz}/questions/create', [App\Http\Controllers\Instructor\QuestionController::class, 'create'])->name('quizzes.questions.create');
    Route::post('/quizzes/{quiz}/questions', [App\Http\Controllers\Instructor\QuestionController::class, 'store'])->name('quizzes.questions.store');
    Route::get('/quizzes/{quiz}/questions/{question}/edit', [App\Http\Controllers\Instructor\QuestionController::class, 'edit'])->name('quizzes.questions.edit');
    Route::put('/quizzes/{quiz}/questions/{question}', [App\Http\Controllers\Instructor\QuestionController::class, 'update'])->name('quizzes.questions.update');
    Route::delete('/quizzes/{quiz}/questions/{question}', [App\Http\Controllers\Instructor\QuestionController::class, 'destroy'])->name('quizzes.questions.destroy');

    // Quiz Attempts & Grading
    Route::get('/quizzes/{quiz}/attempts', [App\Http\Controllers\Instructor\QuizController::class, 'attempts'])->name('quizzes.attempts');
    Route::get('/quizzes/{quiz}/attempts/{attempt}/grade', [App\Http\Controllers\Instructor\QuizController::class, 'grade'])->name('quizzes.attempts.grade');
    Route::post('/quizzes/{quiz}/attempts/{attempt}/grade', [App\Http\Controllers\Instructor\QuizController::class, 'saveGrades'])->name('quizzes.attempts.grade.save');

    // Certificates
    Route::post('/courses/{course}/enrollments/{enrollment}/issue-certificate', [InstructorDashboardController::class, 'issueCertificate'])->name('courses.enrollments.issue-certificate');
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
    Route::get('/payments/{payment}/receipt', [StudentDashboardController::class, 'downloadReceipt'])->name('payments.receipt');
    Route::get('/certificates', [StudentDashboardController::class, 'certificates'])->name('certificates');

    // Learning
    Route::get('/courses/{course}/lessons/{lesson}', [App\Http\Controllers\Student\LearningController::class, 'show'])->name('learning.show');
    Route::post('/courses/{course}/lessons/{lesson}/complete', [App\Http\Controllers\Student\LearningController::class, 'complete'])->name('learning.complete');

    // Lesson Resources Download
    Route::get('/courses/{course}/lessons/{lesson}/resources/{resource}/download', [App\Http\Controllers\Student\LessonResourceController::class, 'download'])->name('learning.resources.download');

    // Quizzes
    Route::get('/quizzes', [App\Http\Controllers\Student\QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/{quiz}/take', [App\Http\Controllers\Student\QuizController::class, 'take'])->name('quizzes.take');
    Route::get('/courses/{course}/lessons/{lesson}/download', [App\Http\Controllers\Student\LearningController::class, 'download'])->name('learning.download');
    Route::match(['get', 'post'], '/quizzes/{quiz}/submit', [App\Http\Controllers\Student\QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/quizzes/{quiz}/attempts', [App\Http\Controllers\Student\QuizController::class, 'attempts'])->name('quizzes.attempts');
    Route::get('/quiz-attempts/{attempt}', [App\Http\Controllers\Student\QuizController::class, 'showAttempt'])->name('quizzes.attempt');

    // Assignments
    Route::get('/assignments', [App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/courses/{course}/assignments/{assignment}', [App\Http\Controllers\Student\AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/courses/{course}/assignments/{assignment}/submit', [App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('assignments.submit');

    // Notes
    Route::get('/notes', [App\Http\Controllers\Student\NoteController::class, 'index'])->name('notes.index');
    Route::get('/courses/{course}/lessons/{lesson}/notes', [App\Http\Controllers\Student\NoteController::class, 'show'])->name('notes.show');
    Route::post('/courses/{course}/lessons/{lesson}/notes', [App\Http\Controllers\Student\NoteController::class, 'store'])->name('notes.store');

    // Schedule
    Route::get('/schedule', [App\Http\Controllers\Student\ScheduleController::class, 'index'])->name('schedule');

    // Reviews
    Route::post('/courses/{course}/reviews', [App\Http\Controllers\Student\ReviewController::class, 'store'])->name('reviews.store');

    // Discussions
    Route::get('/courses/{course}/discussions', [App\Http\Controllers\Student\DiscussionController::class, 'index'])->name('discussions.index');
    Route::get('/courses/{course}/discussions/{discussion}', [App\Http\Controllers\Student\DiscussionController::class, 'show'])->name('discussions.show');
    Route::post('/courses/{course}/discussions', [App\Http\Controllers\Student\DiscussionController::class, 'store'])->name('discussions.store');
    Route::post('/courses/{course}/discussions/{discussion}/reply', [App\Http\Controllers\Student\DiscussionController::class, 'reply'])->name('discussions.reply');

    // Live Sessions
    Route::get('/courses/{course}/live-sessions', [App\Http\Controllers\Student\LiveSessionController::class, 'index'])->name('live-sessions.index');
    Route::get('/live-sessions/{session}/join', [App\Http\Controllers\Student\LiveSessionController::class, 'join'])->name('live-sessions.join');

    // Achievements
    Route::get('/achievements', [App\Http\Controllers\Student\AchievementController::class, 'index'])->name('achievements.index');
});

/*
|--------------------------------------------------------------------------
| Finance Routes
|--------------------------------------------------------------------------
*/

Route::prefix('finance')->middleware(['auth', 'finance'])->name('finance.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Finance\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [App\Http\Controllers\Finance\DashboardController::class, 'transactions'])->name('transactions');
    Route::get('/payments', [App\Http\Controllers\Finance\DashboardController::class, 'payments'])->name('payments');
    Route::post('/payments/{payment}/verify', [App\Http\Controllers\Finance\DashboardController::class, 'verify'])->name('payments.verify');
    Route::get('/invoices', [App\Http\Controllers\Finance\DashboardController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}/download', [App\Http\Controllers\Finance\DashboardController::class, 'downloadInvoice'])->name('invoices.download');
});

/*
|--------------------------------------------------------------------------
| Payment Webhook
|--------------------------------------------------------------------------
*/

Route::post('/lenco/webhook', [App\Http\Controllers\Payment\LencoWebhookController::class, 'handle'])->name('lenco.webhook');

// Public Newsletter Subscription
Route::post('/newsletter/subscribe', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate(['email' => 'required|email|max:255']);
    \App\Models\NewsletterSubscriber::updateOrCreate(
        ['email' => $validated['email']],
        ['is_active' => true, 'subscribed_at' => now(), 'unsubscribed_at' => null]
    );
    return response()->json(['success' => true]);
})->name('newsletter.subscribe');
