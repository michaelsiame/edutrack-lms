<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
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

        if ($user->isEnrolledIn($course->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Already enrolled in this course',
            ], 422);
        }

        if ($course->enrollment_count >= $course->max_students) {
            return response()->json([
                'success' => false,
                'message' => 'Course is full',
            ], 422);
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

        $course->increment('enrollment_count');

        return response()->json([
            'success' => true,
            'message' => 'Enrolled successfully',
            'data' => $enrollment->load('course'),
        ]);
    }
}
