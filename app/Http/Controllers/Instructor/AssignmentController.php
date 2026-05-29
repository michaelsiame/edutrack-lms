<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\EmailQueueService;
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

        if (!empty($validated['lesson_id'])) {
            $lesson = Lesson::find($validated['lesson_id']);
            if (!$lesson || $lesson->module->course_id !== $course->id) {
                return back()->with('error', 'Invalid lesson for this course.');
            }
        }

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
     * Show edit form for an assignment.
     */
    public function edit(Course $course, Assignment $assignment)
    {
        $this->authorizeInstructor($course);

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        return view('instructor.assignments.edit', compact('course', 'assignment'));
    }

    /**
     * Update an assignment.
     */
    public function update(Request $request, Course $course, Assignment $assignment)
    {
        $this->authorizeInstructor($course);

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

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

        if (!empty($validated['lesson_id'])) {
            $lesson = Lesson::find($validated['lesson_id']);
            if (!$lesson || $lesson->module->course_id !== $course->id) {
                return back()->with('error', 'Invalid lesson for this course.');
            }
        }

        $assignment->update([
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

        return redirect()->route('instructor.assignments.index')
            ->with('success', 'Assignment updated successfully.');
    }

    /**
     * Delete an assignment.
     */
    public function destroy(Course $course, Assignment $assignment)
    {
        $this->authorizeInstructor($course);

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        $assignment->delete();

        return back()->with('success', 'Assignment deleted successfully.');
    }

    /**
     * Grade a submission.
     */
    public function grade(Request $request, Course $course, Assignment $assignment, AssignmentSubmission $submission)
    {
        $this->authorizeInstructor($course);

        if ($submission->assignment_id !== $assignment->id || $assignment->course_id !== $course->id) {
            abort(404);
        }

        $validated = $request->validate([
            'points_earned' => 'required|numeric|min:0|max:' . $assignment->max_points,
            'feedback' => 'nullable|string|max:5000',
        ]);

        $points = $validated['points_earned'];
        if ($submission->is_late && $assignment->late_penalty_percent > 0) {
            $penalty = $points * ($assignment->late_penalty_percent / 100);
            $points = max(0, $points - $penalty);
        }

        $submission->update([
            'points_earned' => $points,
            'feedback' => $validated['feedback'] ?? null,
            'status' => 'Graded',
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]);

        $enrollment = \App\Models\Enrollment::where('user_id', $submission->student?->user_id)
            ->where('course_id', $course->id)
            ->first();
        if ($enrollment) {
            app(\App\Services\GradeAggregationService::class)->recalculateFinalGrade($enrollment);
        }

        // Send notification and email to student
        try {
            $emailService = app(EmailQueueService::class);
            $emailService->sendNotification($submission->student->user_id, 'Assignment Graded', "Your submission for {$assignment->title} has been graded.", 'grade', route('assignments.show', [$course, $assignment]));

            if ($submission->student?->user?->email) {
                $subject = "Assignment Graded: {$assignment->title}";
                $body = view('emails.assignment-graded', [
                    'student' => $submission->student->user,
                    'assignment' => $assignment,
                    'submission' => $submission,
                    'course' => $course,
                ])->render();
                $emailService->queue($submission->student->user->email, $subject, $body);
            }
        } catch (\Exception $e) {
            // Silently log email failure; don't block the grading flow
            \Illuminate\Support\Facades\Log::warning('Failed to send assignment graded email: ' . $e->getMessage());
        }

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
