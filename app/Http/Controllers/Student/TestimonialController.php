<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    /**
     * Show the testimonial submission form for a completed enrollment.
     */
    public function create(Enrollment $enrollment)
    {
        $user = auth()->user();

        // Security: ensure the enrollment belongs to the current user
        if ($enrollment->user_id !== $user->id) {
            abort(403, 'You can only review courses you have enrolled in.');
        }

        // Ensure the course is completed
        if ($enrollment->progress < 100) {
            $firstModule = $enrollment->course->modules->first();
            $firstLesson = $firstModule?->lessons->first();
            if ($firstLesson) {
                return redirect()->route('student.learning.show', [$enrollment->course, $firstLesson])
                    ->with('warning', 'Complete the course before leaving a review.');
            }
            return redirect()->route('student.dashboard')
                ->with('warning', 'Complete the course before leaving a review.');
        }

        // Check if already reviewed
        $existing = Testimonial::where('user_id', $user->id)
            ->where('enrollment_id', $enrollment->id)
            ->first();

        if ($existing) {
            return redirect()->route('testimonials')
                ->with('info', 'You have already submitted a review for this course. It will appear once approved.');
        }

        $course = $enrollment->course;

        return view('student.testimonials.create', compact('enrollment', 'course'));
    }

    /**
     * Store a newly submitted testimonial.
     */
    public function store(Request $request, Enrollment $enrollment)
    {
        $user = auth()->user();

        if ($enrollment->user_id !== $user->id) {
            abort(403);
        }

        if ($enrollment->progress < 100) {
            return redirect()->route('student.dashboard')
                ->with('warning', 'Complete the course before leaving a review.');
        }

        // Prevent duplicates
        if (Testimonial::where('user_id', $user->id)->where('enrollment_id', $enrollment->id)->exists()) {
            return redirect()->route('testimonials')
                ->with('info', 'You have already submitted a review for this course.');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'testimonial_text' => 'required|string|min:20|max:2000',
            'job_title' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:100',
        ], [
            'testimonial_text.min' => 'Please write at least a few sentences about your experience.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $course = $enrollment->course;

        Testimonial::create([
            'student_name' => $user->full_name ?? $user->name,
            'course_taken' => $course->title,
            'graduation_year' => now()->year,
            'current_job_title' => $request->input('job_title'),
            'company' => $request->input('company'),
            'testimonial_text' => $request->input('testimonial_text'),
            'rating' => $request->input('rating'),
            'status' => 'pending',
            'is_featured' => false,
            'submitted_by' => $user->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
        ]);

        return redirect()->route('testimonials')
            ->with('success', 'Thank you! Your review has been submitted and will appear once approved by our team.');
    }
}
