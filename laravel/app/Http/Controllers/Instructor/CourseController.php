<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

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
        return view('instructor.courses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:course_categories,id',
            'price' => 'required|numeric|min:0',
            'duration_weeks' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published',
        ]);

        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $validated['instructor_id'] = $instructor->id;
        Course::create($validated);

        return redirect()->route('instructor.courses.index')->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        $this->authorizeInstructor($course);
        $course->load(['modules.lessons', 'enrollments.student']);
        return view('instructor.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $this->authorizeInstructor($course);
        $course->load('category');
        return view('instructor.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeInstructor($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug,' . $course->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:course_categories,id',
            'price' => 'required|numeric|min:0',
            'duration_weeks' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published',
        ]);

        $course->update($validated);

        return redirect()->route('instructor.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $this->authorizeInstructor($course);
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
