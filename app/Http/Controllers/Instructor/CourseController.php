<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }
        $courses = $instructor->courses()->withCount('enrollments')->latest()->get();
        return view('instructor.courses.index', compact('courses'));
    }

    public function create()
    {
        $categories = CourseCategory::orderBy('name')->get();
        return view('instructor.courses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:course_categories,id',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'duration_weeks' => 'nullable|integer|min:1',
            'total_hours' => 'nullable|numeric|min:0',
            'max_students' => 'nullable|integer|min:0',
            'language' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'video_intro_url' => 'nullable|url|max:500',
            'prerequisites' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $validated['instructor_id'] = $instructor->id;
        $validated['is_featured'] = false; // Instructors cannot self-feature courses

        // Instructors cannot publish directly; requests go to admin review
        $wasPublished = ($validated['status'] ?? '') === 'published';
        if ($wasPublished) {
            $validated['status'] = 'under_review';
        }

        if ($request->hasFile('thumbnail')) {
            $request->validate(['thumbnail' => 'image|mimes:jpg,jpeg,png,webp|max:5120']);
            $validated['thumbnail_url'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        Course::create($validated);

        $message = 'Course created successfully.';
        if ($wasPublished) {
            $message .= ' It has been submitted for admin approval.';
        }

        return redirect()->route('instructor.courses.index')->with('success', $message);
    }

    public function show(Course $course)
    {
        $this->authorize('view', $course);
        $course->load(['modules.lessons', 'enrollments.student']);

        // Load unlinked quizzes and assignments for the lesson linking dropdowns
        $unlinkedQuizzes = \App\Models\Quiz::where('course_id', $course->id)
            ->whereNull('lesson_id')
            ->orderBy('title')
            ->get();

        $unlinkedAssignments = \App\Models\Assignment::where('course_id', $course->id)
            ->whereNull('lesson_id')
            ->orderBy('title')
            ->get();

        return view('instructor.courses.show', compact('course', 'unlinkedQuizzes', 'unlinkedAssignments'));
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        $course->load('category');
        $categories = CourseCategory::orderBy('name')->get();
        return view('instructor.courses.edit', compact('course', 'categories'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug,' . $course->id,
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:course_categories,id',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'duration_weeks' => 'nullable|integer|min:1',
            'total_hours' => 'nullable|numeric|min:0',
            'max_students' => 'nullable|integer|min:0',
            'language' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'video_intro_url' => 'nullable|url|max:500',
            'prerequisites' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $validated['is_featured'] = false; // Instructors cannot self-feature courses

        // Instructors cannot publish directly; requests go to admin review
        // But preserve 'published' if already approved — don't send approved courses back to review
        $wasPublished = ($validated['status'] ?? '') === 'published';
        if ($wasPublished && $course->status !== 'published') {
            $validated['status'] = 'under_review';
        } elseif ($course->status === 'published' && $validated['status'] !== 'published') {
            // Instructor chose to revert published course to draft
            $validated['status'] = 'draft';
        }

        if ($request->hasFile('thumbnail')) {
            $request->validate(['thumbnail' => 'image|mimes:jpg,jpeg,png,webp|max:5120']);
            if ($course->thumbnail_url) {
                Storage::disk('public')->delete($course->thumbnail_url);
            }
            $validated['thumbnail_url'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course->update($validated);

        $message = 'Course updated successfully.';
        if ($wasPublished) {
            $message .= ' It has been submitted for admin approval.';
        }

        return redirect()->route('instructor.courses.index')->with('success', $message);
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $course->delete();

        return redirect()->route('instructor.courses.index')->with('success', 'Course deleted successfully.');
    }

    protected function authorizeInstructor(Course $course): void
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this course.');
        }
    }
}
