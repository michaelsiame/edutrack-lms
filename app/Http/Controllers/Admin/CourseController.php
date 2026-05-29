<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['category', 'instructor.user'])->latest()->paginate(20);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $categories = \App\Models\CourseCategory::orderBy('name')->get();
        $instructors = \App\Models\Instructor::with('user')->get();
        return view('admin.courses.create', compact('categories', 'instructors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:course_categories,id',
            'instructor_id' => 'required|exists:instructors,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'duration_weeks' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,archived,under_review',
        ]);

        Course::create($validated);

        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        $course->load(['category', 'instructor.user', 'modules.lessons']);
        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $course->load(['category', 'instructor.user']);
        $categories = \App\Models\CourseCategory::orderBy('name')->get();
        $instructors = \App\Models\Instructor::with('user')->get();
        return view('admin.courses.edit', compact('course', 'categories', 'instructors'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug,' . $course->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:course_categories,id',
            'instructor_id' => 'required|exists:instructors,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'duration_weeks' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,archived,under_review',
        ]);

        $course->update($validated);

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }

    /**
     * Approve a course that is under review.
     */
    public function approve(Course $course)
    {
        if ($course->status !== 'under_review') {
            return back()->with('info', 'This course is not pending approval.');
        }

        $course->update(['status' => 'published']);

        return back()->with('success', 'Course approved and published successfully.');
    }

    /**
     * Reject a course that is under review.
     */
    public function reject(Course $course)
    {
        if ($course->status !== 'under_review') {
            return back()->with('info', 'This course is not pending approval.');
        }

        $course->update(['status' => 'draft']);

        return back()->with('success', 'Course rejected and returned to draft.');
    }
}
