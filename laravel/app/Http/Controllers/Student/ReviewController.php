<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $user = auth()->user();

        // Must be enrolled and course completed or in progress
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('enrollment_status', ['In Progress', 'Completed'])
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'You must be enrolled in this course to leave a review.');
        }

        $validated = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'required|string|min:10|max:2000',
        ]);

        CourseReview::updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'],
            ]
        );

        // Update course average rating
        $avgRating = CourseReview::where('course_id', $course->id)->avg('rating');
        $totalReviews = CourseReview::where('course_id', $course->id)->count();
        $course->update([
            'rating' => $avgRating,
            'total_reviews' => $totalReviews,
        ]);

        return back()->with('success', 'Thank you for your review!');
    }
}
