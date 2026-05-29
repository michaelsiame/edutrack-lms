<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    // Courses API
    Route::get('/courses', [App\Http\Controllers\Api\CourseController::class, 'index']);
    Route::get('/courses/{course}', [App\Http\Controllers\Api\CourseController::class, 'show']);

    // Enrollments API
    Route::get('/enrollments', [App\Http\Controllers\Api\EnrollmentController::class, 'index']);
    Route::post('/enrollments', [App\Http\Controllers\Api\EnrollmentController::class, 'store']);
    Route::get('/enrollments/{enrollment}', [App\Http\Controllers\Api\EnrollmentController::class, 'show']);

    // Progress API
    Route::post('/progress/{lesson}', [App\Http\Controllers\Api\ProgressController::class, 'update']);

    // Certificates API
    Route::get('/certificates', [App\Http\Controllers\Api\CertificateController::class, 'index']);

    // Notifications API
    Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);

    // Quiz API
    Route::get('/quizzes/{quiz}', [App\Http\Controllers\Api\QuizController::class, 'show']);
    Route::post('/quizzes/{quiz}/attempt', [App\Http\Controllers\Api\QuizController::class, 'attempt']);
    Route::post('/quizzes/{quiz}/submit', [App\Http\Controllers\Api\QuizController::class, 'submit']);

    // Assignment API
    Route::get('/assignments', [App\Http\Controllers\Api\AssignmentController::class, 'index']);
    Route::get('/assignments/{assignment}', [App\Http\Controllers\Api\AssignmentController::class, 'show']);
    Route::post('/assignments/{assignment}/submit', [App\Http\Controllers\Api\AssignmentController::class, 'submit']);
});

Route::get('/certificates/verify/{code}', [App\Http\Controllers\Api\CertificateController::class, 'verify']);

// Public promotion validation (no auth required for checking codes)
Route::post('/promotions/validate', [App\Http\Controllers\Api\PromotionController::class, 'validateCode'])->name('api.promotions.validate');

// Session heartbeat — keeps session alive during long study sessions
Route::get('/session/heartbeat', function () {
    return response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]);
})->middleware('auth');
