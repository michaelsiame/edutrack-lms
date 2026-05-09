<?php

namespace App\Http\Controllers;

use App\Models\Course;
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
}
