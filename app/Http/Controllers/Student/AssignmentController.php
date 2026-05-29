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
            ->whereIn('enrollment_status', ['Enrolled', 'In Progress', 'Completed'])
            ->with('course.assignments')
            ->get();

        $assignments = collect();
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->course->assignments as $assignment) {
                $assignment->enrollment = $enrollment;
                $assignment->submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $user->student?->id)
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

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        // Verify enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            return redirect()->route('checkout.show', $course)
                ->with('warning', 'Please complete at least a 30% deposit to access assignments.');
        }

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $user->student?->id)
            ->latest('submitted_at')
            ->first();

        return view('student.assignments.show', compact('course', 'assignment', 'submission'));
    }

    /**
     * Submit an assignment.
     */
    public function submit(Request $request, Course $course, Assignment $assignment)
    {
        $user = auth()->user();

        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        // Verify enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            abort(403, 'Please complete at least a 30% deposit to submit assignments.');
        }

        $isLate = $assignment->due_date && now()->isAfter($assignment->due_date);
        if ($isLate && !$assignment->allow_late_submission) {
            return back()->with('error', 'Late submissions are not accepted for this assignment.');
        }

        $allowedTypes = $assignment->allowed_file_types
            ? explode(',', $assignment->allowed_file_types)
            : ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
        $maxSizeKb = ($assignment->max_file_size_mb ?? 50) * 1024;

        $validated = $request->validate([
            'submission_text' => 'nullable|string|max:10000',
            'submission_file' => ['nullable', 'file', 'max:' . $maxSizeKb, 'mimes:' . implode(',', $allowedTypes)],
        ]);

        if (empty($validated['submission_text']) && !$request->hasFile('submission_file')) {
            return back()->with('error', 'Please provide either text or a file submission.');
        }

        $fileUrl = null;
        if ($request->hasFile('submission_file')) {
            $file = $request->file('submission_file');
            $path = $file->store('assignment-submissions/' . $assignment->id, 'public');
            $fileUrl = Storage::url($path);
        }

        $maxAttempt = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $user->student?->id)
            ->max('attempt_number') ?? 0;

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $user->student?->id,
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
