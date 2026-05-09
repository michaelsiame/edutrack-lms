<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::published()->with(['category', 'instructor.user']);

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

        $courses = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    public function show(Course $course)
    {
        if ($course->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
            ], 404);
        }

        $course->load(['instructor.user', 'category', 'modules.lessons', 'reviews.user']);

        return response()->json([
            'success' => true,
            'data' => $course,
        ]);
    }
}
