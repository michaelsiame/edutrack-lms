<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
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

        // Check course capacity
        if ($course->enrollment_count >= $course->max_students) {
            return back()->with('error', 'This course is full.');
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'student_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'enrollment_status' => 'Enrolled',
            'payment_status' => $course->price > 0 ? 'pending' : 'completed',
            'amount_paid' => 0,
        ]);

        // Increment enrollment count
        $course->increment('enrollment_count');

        return redirect()->route('enrollments.show', $course)
            ->with('success', 'Successfully enrolled in ' . $course->title);
    }
}
