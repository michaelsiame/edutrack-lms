<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'status' => 'nullable|in:Not Started,In Progress,Completed',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'time_spent_minutes' => 'nullable|integer|min:0',
        ]);

        $user = auth()->user();
        $enrollment = $user->enrollments()
            ->where('course_id', $lesson->module->course_id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            return response()->json(['success' => false, 'message' => 'Payment required. Minimum 30% deposit needed.'], 403);
        }

        $progress = LessonProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => $request->status ?? 'In Progress',
                'progress_percentage' => $request->progress_percentage ?? 0,
                'time_spent_minutes' => \DB::raw("COALESCE(time_spent_minutes, 0) + " . ($request->time_spent_minutes ?? 0)),
                'last_accessed' => now(),
            ]
        );

        if ($request->status === 'Completed' && !$progress->completed_at) {
            $progress->update(['completed_at' => now()]);
        }

        // Recalculate enrollment progress
        $this->updateEnrollmentProgress($enrollment);

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    protected function updateEnrollmentProgress($enrollment): void
    {
        $totalLessons = $enrollment->course->lessons()->count();
        $completedLessons = $enrollment->lessonProgress()->where('status', 'Completed')->count();

        $progress = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;
        $wasCompleted = $enrollment->enrollment_status !== 'Completed';

        $enrollment->update([
            'progress' => $progress,
            'enrollment_status' => $progress >= 100 ? 'Completed' : 'In Progress',
            'completion_date' => $progress >= 100 ? now() : null,
        ]);

        // Auto-issue certificate and badge on first completion
        if ($progress >= 100 && $wasCompleted) {
            $this->awardCompletionRewards($enrollment);
        }
    }

    /**
     * Auto-issue certificate and award completion badge if eligible.
     */
    protected function awardCompletionRewards($enrollment): void
    {
        try {
            $service = new \App\Services\CertificateService();
            $certificate = $service->issueCertificate($enrollment);

            if ($certificate) {
                $service->sendCertificateNotification($certificate);
            }
        } catch (\Throwable $e) {
            \Log::error('Auto-certificate failed: ' . $e->getMessage());
        }

        // Badge: find active completion badge and award it
        $badge = \App\Models\Badge::where('badge_type', 'Course Completion')
            ->where('is_active', true)
            ->first();

        if ($badge && $enrollment->student_id) {
            $alreadyHas = \App\Models\StudentAchievement::where('student_id', $enrollment->student_id)
                ->where('badge_id', $badge->badge_id)
                ->where('course_id', $enrollment->course_id)
                ->exists();

            if (!$alreadyHas) {
                \App\Models\StudentAchievement::create([
                    'student_id' => $enrollment->student_id,
                    'badge_id' => $badge->badge_id,
                    'course_id' => $enrollment->course_id,
                    'earned_date' => now(),
                    'description' => 'Completed ' . $enrollment->course->title,
                ]);
            }
        }
    }
}
