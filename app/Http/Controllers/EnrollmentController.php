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
                // Active or completed enrolment: nothing to do.
                if ($existing->enrollment_status !== 'Dropped') {
                    return $existing;
                }

                // Re-enrolment: a dropped student rejoins via a currently-open
                // intake. We reactivate the same enrolment row (the unique
                // (user, course) constraint allows only one) so their history
                // and any prior payments are preserved.
                $intake = $this->resolveIntake($course, $request);

                $existing->update([
                    'intake_id' => $intake->id,
                    'enrollment_status' => 'Enrolled',
                    'enrolled_at' => now(),
                ]);
                $intake->increment('enrollment_count');
                $existing->was_reactivated = true;

                return $existing;
            }

            $intake = $this->resolveIntake($course, $request);

            $price = $intake->effective_price ?? $course->discount_price ?? $course->price ?? 0;
            $isFree = $price <= 0;

            // Ensure student record exists
            $student = $user->student;
            if (!$student) {
                $student = \App\Models\Student::create([
                    'user_id' => $user->id,
                    'student_number' => \App\Services\StudentNumberService::generate((int) now()->year),
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

        // Already enrolled (active/completed) — nothing changed.
        if ($enrollment->wasRecentlyCreated === false && empty($enrollment->was_reactivated)) {
            return redirect()->route('enrollments.show', $course)
                ->with('info', 'You are already enrolled in this course.');
        }

        // Re-enrolment of a previously dropped student.
        if (!empty($enrollment->was_reactivated)) {
            if ($enrollment->payment_status === 'completed') {
                return redirect()->route('enrollments.show', $course)
                    ->with('success', 'Welcome back! You have re-enrolled in ' . $course->title . '.');
            }
            return redirect()->route('checkout.show', ['course' => $course, 'intake' => $enrollment->intake_id])
                ->with('info', 'Welcome back! Please complete your payment to resume the course.');
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

    /**
     * Resolve and validate the intake a student is enrolling into.
     * Must be called inside the enrolment DB transaction (uses row locks).
     */
    protected function resolveIntake(Course $course, Request $request): Intake
    {
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

        // Capacity check (0 = unlimited)
        if ($intake->max_students > 0 && $intake->enrollment_count >= $intake->max_students) {
            throw new \Exception('This intake is full.');
        }

        return $intake;
    }
}
