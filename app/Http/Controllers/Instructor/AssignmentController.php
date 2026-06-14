<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use App\Services\EmailQueueService;
use App\Services\StudentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * List assignments for instructor's courses.
     */
    public function index()
    {
        $user = auth()->user();
        $instructor = $user->instructor;
        if (!$user->isAdmin() && !$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $courses = $user->isAdmin()
            ? Course::with([
                'enrollments' => fn ($query) => $query->where('enrollment_status', '!=', 'Dropped')->whereHas('user')->with('user'),
                'assignments.submissions.student.user',
            ])
                ->latest()
                ->get()
            : $instructor->courses()
                ->with([
                    'enrollments' => fn ($query) => $query->where('enrollment_status', '!=', 'Dropped')->whereHas('user')->with('user'),
                    'assignments.submissions.student.user',
                ])
                ->latest()
                ->get();

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
            $lesson = Lesson::with('module')->find($validated['lesson_id']);
            if (!$lesson || !$lesson->module || $lesson->module->course_id !== $course->id) {
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
            $lesson = Lesson::with('module')->find($validated['lesson_id']);
            if (!$lesson || !$lesson->module || $lesson->module->course_id !== $course->id) {
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
     * Record an offline mark for a student enrolled in the course.
     */
    public function recordMark(Request $request, Course $course, Assignment $assignment)
    {
        $this->authorizeInstructor($course);

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'points_earned' => 'required|numeric|min:0|max:' . $assignment->max_points,
            'feedback' => 'nullable|string|max:5000',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('enrollment_status', '!=', 'Dropped')
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'Selected user is not enrolled in this course.');
        }

        $student = $user->student;
        if (!$student) {
            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => StudentNumberService::generate((int) now()->year),
                'enrollment_date' => now(),
            ]);
        }

        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        $submission = AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'points_earned' => $validated['points_earned'],
                'feedback' => $validated['feedback'] ?? null,
                'status' => 'Graded',
                'graded_by' => auth()->id(),
                'graded_at' => now(),
                'source' => 'offline',
                'submitted_at' => $existingSubmission?->submitted_at ?? now(),
                'attempt_number' => $existingSubmission?->attempt_number ?? 1,
            ]
        );

        app(\App\Services\GradeAggregationService::class)->recalculateFinalGrade($enrollment);

        // Send notification and email to student
        try {
            $emailService = app(EmailQueueService::class);
            $emailService->sendNotification(
                $user->id,
                'Assignment Graded',
                "Your submission for {$assignment->title} has been graded.",
                'grade',
                route('student.assignments.show', [$course, $assignment])
            );

            if ($user->email) {
                $subject = "Assignment Graded: {$assignment->title}";
                $body = view('emails.assignment-graded', [
                    'studentName' => $user->first_name ?? $user->full_name,
                    'assignmentTitle' => $assignment->title,
                    'courseTitle' => $course->title,
                    'pointsEarned' => $submission->points_earned,
                    'maxPoints' => $assignment->max_points,
                    'feedback' => $submission->feedback,
                ])->render();
                $emailService->queue($user->email, $subject, $body);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send assignment graded email: ' . $e->getMessage());
        }

        return back()->with('success', "Mark recorded for {$user->full_name}.");
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

        $studentUserId = $submission->student?->user_id;
        if ($studentUserId) {
            $enrollment = \App\Models\Enrollment::where('user_id', $studentUserId)
                ->where('course_id', $course->id)
                ->first();
            if ($enrollment) {
                app(\App\Services\GradeAggregationService::class)->recalculateFinalGrade($enrollment);
            }
        }

        // Send notification and email to student
        try {
            $emailService = app(EmailQueueService::class);
            if ($studentUserId) {
                $emailService->sendNotification($studentUserId, 'Assignment Graded', "Your submission for {$assignment->title} has been graded.", 'grade', route('student.assignments.show', [$course, $assignment]));
            }

            $studentEmail = $submission->student?->user?->email;
            if ($studentEmail) {
                $subject = "Assignment Graded: {$assignment->title}";
                $body = view('emails.assignment-graded', [
                    'studentName' => $submission->student->user->first_name ?? $submission->student->user->full_name,
                    'assignmentTitle' => $assignment->title,
                    'courseTitle' => $course->title,
                    'pointsEarned' => $submission->points_earned,
                    'maxPoints' => $assignment->max_points,
                    'feedback' => $submission->feedback,
                ])->render();
                $emailService->queue($studentEmail, $subject, $body);
            }
        } catch (\Exception $e) {
            // Silently log email failure; don't block the grading flow
            \Illuminate\Support\Facades\Log::warning('Failed to send assignment graded email: ' . $e->getMessage());
        }

        return back()->with('success', 'Submission graded successfully.');
    }

    /**
     * Download a submission for instructor review.
     */
    public function downloadSubmission(Course $course, Assignment $assignment, AssignmentSubmission $submission)
    {
        $this->authorizeInstructor($course);

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        if (empty($submission->file_url)) {
            abort(404);
        }

        if (Storage::disk('local')->exists($submission->file_url)) {
            return Storage::disk('local')->download($submission->file_url);
        }

        if (str_starts_with($submission->file_url, '/storage/')) {
            $legacyPath = str_replace('/storage/', '', $submission->file_url);
            if (Storage::disk('public')->exists($legacyPath)) {
                return Storage::disk('public')->download($legacyPath);
            }
        }

        abort(404);
    }

    protected function authorizeInstructor(Course $course): void
    {
        if (auth()->user()?->isAdmin()) {
            return;
        }

        $instructor = auth()->user()->instructor;
        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this course.');
        }
    }
}
