<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentPaymentPlan;
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

        // Check course capacity
        if ($course->enrollment_count >= $course->max_students) {
            return back()->with('error', 'This course is full.');
        }

        $isFree = $course->price <= 0 || ($course->discount_price !== null && $course->discount_price <= 0);
        $price = $course->discount_price ?? $course->price;

        // Create enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'student_id' => $user->id,
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

        // Increment enrollment count
        $course->increment('enrollment_count');

        if ($isFree) {
            return redirect()->route('enrollments.show', $course)
                ->with('success', 'Successfully enrolled in ' . $course->title . '. You can start learning now!');
        }

        // For paid courses, redirect to checkout
        return redirect()->route('checkout.show', $course)
            ->with('info', 'Enrollment created. Please complete your payment to access the course content.');
    }
}
