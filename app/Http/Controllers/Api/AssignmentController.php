<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    /**
     * List assignments for the authenticated student's enrolled courses.
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
            if (!$enrollment->canAccessContent()) {
                continue;
            }

            foreach ($enrollment->course->assignments as $assignment) {
                $assignment->submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $user->student?->id)
                    ->latest('submitted_at')
                    ->first();
                $assignments->push($assignment);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    /**
     * Show assignment details.
     */
    public function show(Assignment $assignment)
    {
        $user = auth()->user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $assignment->course_id)
            ->first();

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'Not enrolled'], 403);
        }

        if (!$enrollment->canAccessContent()) {
            return response()->json(['success' => false, 'message' => 'Payment required. Minimum 30% deposit needed.'], 403);
        }

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $user->student?->id)
            ->latest('submitted_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'assignment' => $assignment,
                'submission' => $submission,
            ],
        ]);
    }

    /**
     * Submit an assignment.
     */
    public function submit(Request $request, Assignment $assignment)
    {
        $user = auth()->user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $assignment->course_id)
            ->first();

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'Not enrolled'], 403);
        }

        if (!$enrollment->canAccessContent()) {
            return response()->json(['success' => false, 'message' => 'Payment required. Minimum 30% deposit needed.'], 403);
        }

        $isLate = $assignment->due_date && now()->isAfter($assignment->due_date);
        if ($isLate && !$assignment->allow_late_submission) {
            return response()->json(['success' => false, 'message' => 'Late submissions are not accepted for this assignment.'], 422);
        }

        $allowedTypes = $assignment->allowed_file_types
            ? explode(',', $assignment->allowed_file_types)
            : ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
        $maxSizeKb = ($assignment->max_file_size_mb ?? 50) * 1024;

        $validator = Validator::make($request->all(), [
            'submission_text' => 'nullable|string|max:10000',
            'submission_file' => ['nullable', 'file', 'max:' . $maxSizeKb, 'mimes:' . implode(',', $allowedTypes)],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        if (empty($validated['submission_text']) && !$request->hasFile('submission_file')) {
            return response()->json(['success' => false, 'message' => 'Please provide either text or a file submission.'], 422);
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

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $user->student?->id,
            'submission_text' => $validated['submission_text'] ?? null,
            'file_url' => $fileUrl,
            'submitted_at' => now(),
            'status' => $isLate ? 'Late' : 'Submitted',
            'is_late' => $isLate,
            'attempt_number' => $maxAttempt + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assignment submitted successfully.',
            'data' => $submission,
        ]);
    }
}
