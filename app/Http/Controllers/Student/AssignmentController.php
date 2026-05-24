<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * List assignments for student's enrolled courses.
     */
    public function index()
    {
        $user = auth()->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->whereIn('enrollment_status', ['Enrolled', 'In Progress'])
            ->with('course.assignments')
            ->get();

        $assignments = collect();
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->course->assignments as $assignment) {
                $assignment->enrollment = $enrollment;
                $assignment->submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $user->id)
                    ->first();
                $assignments->push($assignment);
            }
        }

        return view('student.assignments.index', compact('assignments'));
    }

    /**
     * Show assignment detail and submission form.
     */
    public function show(Course $course, Assignment $assignment)
    {
        $user = auth()->user();

        // Verify enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $user->id)
            ->first();

        return view('student.assignments.show', compact('course', 'assignment', 'submission'));
    }

    /**
     * Submit an assignment.
     */
    public function submit(Request $request, Course $course, Assignment $assignment)
    {
        $user = auth()->user();

        // Verify enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        $validated = $request->validate([
            'submission_text' => 'nullable|string|max:10000',
            'submission_file' => 'nullable|file|max:51200', // 50MB max
        ]);

        $fileUrl = null;
        if ($request->hasFile('submission_file')) {
            $file = $request->file('submission_file');
            $path = $file->store('assignment-submissions/' . $assignment->id, 'public');
            $fileUrl = Storage::url($path);
        }

        $isLate = $assignment->due_date && now()->isAfter($assignment->due_date);

        $maxAttempt = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $user->id)
            ->max('attempt_number') ?? 0;

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
            'submission_text' => $validated['submission_text'] ?? null,
            'file_url' => $fileUrl,
            'submitted_at' => now(),
            'status' => $isLate ? 'Late' : 'Submitted',
            'is_late' => $isLate,
            'attempt_number' => $maxAttempt + 1,
        ]);

        return redirect()->route('student.assignments.show', [$course, $assignment])
            ->with('success', 'Assignment submitted successfully.');
    }
}
