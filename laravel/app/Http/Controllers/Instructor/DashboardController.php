<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $instructor = auth()->user()->instructor;

        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $stats = [
            'total_courses' => $instructor->courses()->count(),
            'total_students' => Enrollment::whereIn('course_id', $instructor->courses()->pluck('id'))->count(),
            'average_rating' => $instructor->rating,
        ];

        $courses = $instructor->courses()->withCount('enrollments')->latest()->get();

        return view('instructor.dashboard', compact('stats', 'courses'));
    }

    public function submissions()
    {
        return view('instructor.submissions');
    }

    public function analytics()
    {
        return view('instructor.analytics');
    }
}
