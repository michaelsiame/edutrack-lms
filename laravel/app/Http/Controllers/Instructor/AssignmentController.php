<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * List assignments for instructor's courses.
     */
    public function index()
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $courses = $instructor->courses()->with('assignments.submissions')->latest()->get();
        return view('instructor.assignments.index', compact('courses'));
    }

    /**
     * Create assignment for a course.
     */
    public function store(Request $request, Course $course)
    {
        $this->authorizeInstructor($course);

        $validated = $request->validate([
            'lesson_id' => 'nullable|exists:lessons,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'max_points' => 'nullable|integer|min:1',
            'passing_points' => 'nullable|integer|min:0',
            'due_date' => 'nullable|date',
            'allow_late_submission' => 'nullable|boolean',
            'late_penalty_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        Assignment::create([
            'course_id' => $course->id,
            'lesson_id' => $validated['lesson_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'max_points' => $validated['max_points'] ?? 100,
            'passing_points' => $validated['passing_points'] ?? 60,
            'due_date' => $validated['due_date'] ?? null,
            'allow_late_submission' => $request->boolean('allow_late_submission'),
            'late_penalty_percent' => $validated['late_penalty_percent'] ?? 0,
        ]);

        return back()->with('success', 'Assignment created successfully.');
    }

    /**
     * Grade a submission.
     */
    public function grade(Request $request, Course $course, Assignment $assignment, AssignmentSubmission $submission)
    {
        $this->authorizeInstructor($course);

        if ($submission->assignment_id !== $assignment->id) {
            abort(403, 'Invalid submission.');
        }

        $validated = $request->validate([
            'points_earned' => 'required|numeric|min:0|max:' . $assignment->max_points,
            'feedback' => 'nullable|string|max:5000',
        ]);

        $submission->update([
            'points_earned' => $validated['points_earned'],
            'feedback' => $validated['feedback'] ?? null,
            'status' => 'Graded',
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]);

        return back()->with('success', 'Submission graded successfully.');
    }

    protected function authorizeInstructor(Course $course): void
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this course.');
        }
    }
}
