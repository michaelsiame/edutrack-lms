<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $instructor = $user->instructor;
        if (!$user->isAdmin() && !$instructor) {
            abort(403, 'Instructor profile not found.');
        }
        $courses = $user->isAdmin()
            ? Course::withCount('enrollments')->latest()->get()
            : $instructor->courses()->withCount('enrollments')->latest()->get();
        return view('instructor.courses.index', compact('courses'));
    }

    public function create()
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

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
        if ($wasPublished && $course->status === 'under_review') {
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

    public function saveAsTemplate(Course $course)
    {
        $this->authorize('update', $course);

        $template = $course->replicate();
        $template->title = $course->title . ' (Template)';
        $template->slug = $course->slug . '-template-' . time();
        $template->status = 'draft';
        $template->is_template = true;
        $template->template_source_id = $course->id;
        $template->enrollment_count = 0;
        $template->rating = null;
        $template->total_reviews = 0;
        $template->save();

        // Clone modules and lessons
        foreach ($course->modules as $module) {
            $newModule = $module->replicate();
            $newModule->course_id = $template->id;
            $newModule->save();

            foreach ($module->lessons as $lesson) {
                $newLesson = $lesson->replicate();
                $newLesson->module_id = $newModule->id;
                $newLesson->save();
            }
        }

        return redirect()->route('instructor.courses.index')
            ->with('success', 'Course saved as template: ' . $template->title);
    }

    public function createFromTemplate(Request $request)
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $templates = Course::where('is_template', true)
            ->where(function ($q) use ($instructor) {
                $q->where('instructor_id', $instructor->id)
                  ->orWhereNull('instructor_id');
            })
            ->withCount('modules')
            ->latest()
            ->get();

        $selectedTemplate = null;
        if ($request->has('template_id')) {
            $selectedTemplate = Course::where('is_template', true)
                ->with(['modules.lessons'])
                ->find($request->template_id);
        }

        $categories = CourseCategory::orderBy('name')->get();

        return view('instructor.courses.create-from-template', compact('templates', 'selectedTemplate', 'categories'));
    }

    public function storeFromTemplate(Request $request)
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $validated = $request->validate([
            'template_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses',
            'category_id' => 'required|exists:course_categories,id',
            'price' => 'required|numeric|min:0',
        ]);

        $template = Course::where('is_template', true)
            ->where('id', $validated['template_id'])
            ->with(['modules.lessons'])
            ->firstOrFail();

        $newCourse = DB::transaction(function () use ($template, $validated, $instructor, $request) {
            $course = $template->replicate();
            $course->title = $validated['title'];
            $course->slug = $validated['slug'];
            $course->category_id = $validated['category_id'];
            $course->price = $validated['price'];
            $course->instructor_id = $instructor->id;
            $course->status = 'draft';
            $course->is_template = false;
            $course->template_source_id = $template->id;
            $course->enrollment_count = 0;
            $course->rating = null;
            $course->total_reviews = 0;
            $course->save();

            foreach ($template->modules as $module) {
                $newModule = $module->replicate();
                $newModule->course_id = $course->id;
                $newModule->save();

                foreach ($module->lessons as $lesson) {
                    $newLesson = $lesson->replicate();
                    $newLesson->module_id = $newModule->id;
                    $newLesson->save();
                }
            }

            return $course;
        });

        return redirect()->route('instructor.courses.show', $newCourse)
            ->with('success', 'Course created from template successfully.');
    }
}
