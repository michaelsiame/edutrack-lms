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

        // Check registration fee (mandatory K150 before any enrollment)
        $hasPaidRegistrationFee = RegistrationFee::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->exists();

        if (!$hasPaidRegistrationFee) {
            return redirect()->route('registration-fee.show')
                ->with('warning', 'Please pay the K150 registration fee before enrolling in any course.');
        }

        // Wrap enrollment creation in transaction with row lock to prevent duplicates
        $enrollment = \DB::transaction(function () use ($user, $course, $request) {
            // Re-check enrollment with lock to prevent race conditions
            $existing = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            // Determine intake
            $intake = null;
            if ($course->hasMultipleIntakes()) {
                $request->validate(['intake_id' => 'required|exists:intakes,id']);
                $intake = Intake::lockForUpdate()->find($request->intake_id);

                if (!$intake || $intake->course_id !== $course->id) {
                    throw new \Exception('Invalid intake selected.');
                }

                if (!$intake->canEnroll()) {
                    throw new \Exception('This intake is not open for enrollment.');
                }
            } else {
                $intake = $course->defaultIntake;
            }

            if (!$intake) {
                throw new \Exception('No intake available for this course.');
            }

            // Check intake capacity with lock (0 = unlimited)
            if ($intake->max_students > 0 && $intake->enrollment_count >= $intake->max_students) {
                throw new \Exception('This intake is full.');
            }

            $price = $intake->effective_price ?? $course->discount_price ?? $course->price ?? 0;
            $isFree = $price <= 0;

            // Ensure student record exists
            $student = $user->student;
            if (!$student) {
                $student = \App\Models\Student::create([
                    'user_id' => $user->id,
                    'enrollment_date' => now(),
                ]);
            }

            // Create enrollment
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'student_id' => $student->id,
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
            $intake->increment('enrollment_count');
            $course->increment('enrollment_count');

            return $enrollment;
        });

        // If returned existing enrollment, redirect accordingly
        if ($enrollment->wasRecentlyCreated === false) {
            return redirect()->route('enrollments.show', $course)
                ->with('info', 'You are already enrolled in this course.');
        }

        $emailService = app(\App\Services\EmailQueueService::class);
        $emailService->sendTemplated($user->email, 'Enrollment', [
            'name' => $user->full_name,
            'course' => $course->title,
            'course_url' => route('courses.show', $course),
        ]);
        $emailService->sendNotification($user->id, 'Enrollment Confirmed', "You are now enrolled in {$course->title}", 'enrollment', route('enrollments.show', $course));

        $isFree = $enrollment->payment_status === 'completed';

        if ($isFree) {
            return redirect()->route('enrollments.show', $course)
                ->with('success', 'Successfully enrolled in ' . $course->title . '. You can start learning now!');
        }

        // For paid courses, redirect to checkout
        return redirect()->route('checkout.show', ['course' => $course, 'intake' => $enrollment->intake_id])
            ->with('info', 'Enrollment created. Please complete your payment to access the course content.');
    }
}
