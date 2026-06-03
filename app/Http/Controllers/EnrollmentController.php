<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentPaymentPlan;
use App\Models\Intake;
use App\Models\RegistrationFee;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = auth()->user()->enrollments()
            ->with('course')
            ->latest()
            ->paginate(10);

        return view('enrollments.index', compact('enrollments'));
    }

    public function show(Course $course)
    {
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->with('course.modules.lessons')
            ->firstOrFail();

        return view('enrollments.show', compact('enrollment', 'course'));
    }

    public function store(Request $request, Course $course)
    {
        $user = auth()->user();

        // Check if already enrolled
        if ($user->isEnrolledIn($course->id)) {
            return redirect()->route('enrollments.show', $course)
                ->with('info', 'You are already enrolled in this course.');
        }

        // Check registration fee (mandatory K150 before any enrollment)
        $hasPaidRegistrationFee = RegistrationFee::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->exists();

        if (!$hasPaidRegistrationFee) {
            return redirect()->route('registration-fee.show')
                ->with('warning', 'Please pay the K150 registration fee before enrolling in any course.');
        }

        // Determine intake
        $intake = null;
        if ($course->hasMultipleIntakes()) {
            $request->validate(['intake_id' => 'required|exists:intakes,id']);
            $intake = Intake::find($request->intake_id);

            if (!$intake || $intake->course_id !== $course->id) {
                return back()->with('error', 'Invalid intake selected.');
            }

            if (!$intake->canEnroll()) {
                return back()->with('error', 'This intake is not open for enrollment.');
            }
        } else {
            $intake = $course->defaultIntake;
        }

        if (!$intake) {
            return back()->with('error', 'No intake available for this course.');
        }

        // Check intake capacity (0 = unlimited)
        if ($intake->is_full) {
            return back()->with('error', 'This intake is full.');
        }

        $price = $intake->effective_price ?? $course->discount_price ?? $course->price ?? 0;
        $isFree = $price <= 0;

        // Create enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'student_id' => $user->student?->id,
            'course_id' => $course->id,
            'intake_id' => $intake->id,
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

        // Increment counts
        $intake->incrementEnrollmentCount();
        $course->increment('enrollment_count');

        $emailService = app(\App\Services\EmailQueueService::class);
        $emailService->sendTemplated($user->email, 'Enrollment', [
            'name' => $user->full_name,
            'course' => $course->title,
            'course_url' => route('courses.show', $course),
        ]);
        $emailService->sendNotification($user->id, 'Enrollment Confirmed', "You are now enrolled in {$course->title}", 'enrollment', route('enrollments.show', $course));

        if ($isFree) {
            return redirect()->route('enrollments.show', $course)
                ->with('success', 'Successfully enrolled in ' . $course->title . '. You can start learning now!');
        }

        // For paid courses, redirect to checkout
        return redirect()->route('checkout.show', ['course' => $course, 'intake' => $intake->id])
            ->with('info', 'Enrollment created. Please complete your payment to access the course content.');
    }
}
