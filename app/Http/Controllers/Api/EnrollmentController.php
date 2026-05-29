<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentPaymentPlan;
use App\Models\RegistrationFee;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $enrollments = auth()->user()->enrollments()
            ->with('course')
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $enrollments,
        ]);
    }

    public function show(Enrollment $enrollment)
    {
        if ($enrollment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $enrollment->load(['course.modules.lessons', 'lessonProgress', 'payments']);

        return response()->json([
            'success' => true,
            'data' => $enrollment,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($request->course_id);
        $user = auth()->user();

        // Check registration fee (mandatory before any enrollment)
        $hasPaidRegistrationFee = RegistrationFee::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->exists();

        if (!$hasPaidRegistrationFee) {
            return response()->json([
                'success' => false,
                'message' => 'Please pay the K150 registration fee before enrolling.',
            ], 422);
        }

        if ($user->isEnrolledIn($course->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Already enrolled in this course',
            ], 422);
        }

        // Check course capacity (0 = unlimited)
        if ($course->max_students > 0 && $course->enrollment_count >= $course->max_students) {
            return response()->json([
                'success' => false,
                'message' => 'Course is full',
            ], 422);
        }

        $isFree = $course->price <= 0 || ($course->discount_price !== null && $course->discount_price <= 0);
        $price = $course->discount_price ?? $course->price;

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'student_id' => $user->student?->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'enrollment_status' => 'Enrolled',
            'payment_status' => $isFree ? 'completed' : 'pending',
            'amount_paid' => 0,
            'certificate_blocked' => !$isFree,
        ]);

        // Create payment plan
        EnrollmentPaymentPlan::create([
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'total_fee' => $price,
            'total_paid' => 0,
            'currency' => 'ZMW',
            'payment_status' => $isFree ? 'completed' : 'pending',
        ]);

        $course->increment('enrollment_count');

        $emailService = app(\App\Services\EmailQueueService::class);
        $emailService->sendTemplated($user->email, 'Enrollment', [
            'name' => $user->full_name,
            'course' => $course->title,
            'course_url' => route('courses.show', $course),
        ]);
        $emailService->sendNotification($user->id, 'Enrollment Confirmed', "You are now enrolled in {$course->title}", 'enrollment', route('enrollments.show', $course));

        return response()->json([
            'success' => true,
            'message' => 'Enrolled successfully',
            'data' => $enrollment->load('course'),
        ]);
    }
}
