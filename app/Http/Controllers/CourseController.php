<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::published();

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        $courses = $query->paginate(12);

        return view('courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        if ($course->status !== 'published') {
            abort(404);
        }

        $course->load(['instructor.user', 'category', 'modules.lessons']);

        $isEnrolled = auth()->check() ? auth()->user()->isEnrolledIn($course->id) : false;

        return view('courses.show', compact('course', 'isEnrolled'));
    }

    /**
     * Show a preview lesson to non-enrolled visitors.
     * Only lessons marked as is_preview = true are accessible.
     */
    public function previewLesson(Course $course, Lesson $lesson)
    {
        if ($course->status !== 'published') {
            abort(404);
        }

        if (!$lesson->is_preview) {
            abort(403, 'This lesson is not available for preview.');
        }

        if (!$lesson->module || $lesson->module->course_id !== $course->id) {
            abort(404);
        }

        $course->load(['modules.lessons']);
        $lesson->load(['resources', 'quizzes', 'assignments']);

        // Determine if user is already enrolled (to show appropriate CTA)
        $isEnrolled = auth()->check() ? auth()->user()->isEnrolledIn($course->id) : false;

        return view('courses.preview', compact('course', 'lesson', 'isEnrolled'));
    }
}
