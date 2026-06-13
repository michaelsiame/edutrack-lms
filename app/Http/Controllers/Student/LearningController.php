<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\LessonExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LearningController extends Controller
{
    public function show(Course $course, Lesson $lesson)
    {
        // Verify enrollment
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        // Verify payment gating
        if (!$enrollment->canAccessContent()) {
            return redirect()->route('checkout.show', $course)
                ->with('warning', 'Please complete at least a 30% deposit to access course content.');
        }

        // Verify lesson belongs to course
        if (!$lesson->module || $lesson->module->course_id !== $course->id) {
            abort(404);
        }

        // Load lesson resources, quizzes, and assignments
        $lesson->load(['resources', 'quizzes', 'assignments']);

        // Load modules with lessons
        $modules = $course->modules()
            ->with(['lessons'])
            ->orderBy('display_order')
            ->get();

        // Get lesson progress records for this enrollment
        $progressRecords = LessonProgress::where('enrollment_id', $enrollment->id)
            ->whereIn('lesson_id', $modules->flatMap->lessons->pluck('id'))
            ->get()
            ->keyBy('lesson_id');

        // Mark current lesson as completed for view
        $lessonProgress = $progressRecords->get($lesson->id);
        $lesson->is_completed = $lessonProgress && $lessonProgress->isCompleted();

        // Mark all lessons completion status for sidebar
        foreach ($modules as $module) {
            foreach ($module->lessons as $l) {
                $lp = $progressRecords->get($l->id);
                $l->is_completed = $lp && $lp->isCompleted();
            }
        }

        // Compute overall course progress
        $totalLessons = $modules->flatMap->lessons->count();
        $completedLessons = $progressRecords->filter->isCompleted()->count();
        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

        // Track lesson access
        if ($lessonProgress) {
            $lessonProgress->update(['last_accessed' => now()]);
        } else {
            LessonProgress::create([
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
                'status' => 'In Progress',
                'progress_percentage' => 0,
                'started_at' => now(),
                'last_accessed' => now(),
            ]);
        }

        // Module quiz call-to-action — surfaced when finishing a module.
        [$moduleQuiz, $moduleQuizState] = $this->resolveModuleQuiz(
            $lesson->module, $enrollment, $progressRecords
        );

        // Upcoming/live sessions for this course, keyed by the module they belong to
        // (session -> lesson -> module), so the sidebar can flag a module's live class.
        $moduleSessions = $course->liveSessions()
            ->whereIn('status', ['scheduled', 'live'])
            ->where('scheduled_end_time', '>=', now())
            ->with('lesson:id,module_id')
            ->orderBy('scheduled_start_time')
            ->get()
            ->groupBy(fn ($s) => $s->lesson?->module_id)
            ->map(fn ($group) => $group->first());

        return view('student.learning.show', compact(
            'lesson', 'course', 'modules', 'progress', 'enrollment',
            'moduleQuiz', 'moduleQuizState', 'moduleSessions'
        ));
    }

    /**
     * Find the published quiz for a module and the current student's standing
     * on it, so the lesson view can offer a "Take the Module Quiz" prompt
     * once the module's reading lessons are done.
     *
     * @return array{0: ?\App\Models\Quiz, 1: array}
     */
    protected function resolveModuleQuiz($module, $enrollment, $progressRecords): array
    {
        if (!$module) {
            return [null, []];
        }

        $module->loadMissing('lessons');

        $readingLessons = $module->lessons->where('lesson_type', '!=', 'Quiz');
        $lessonIds = $module->lessons->pluck('id');

        $quiz = \App\Models\Quiz::whereIn('lesson_id', $lessonIds)
            ->where('is_published', true)
            ->first();

        if (!$quiz) {
            return [null, []];
        }

        $studentId = auth()->user()->student?->id;
        $attempts = $studentId
            ? \App\Models\QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $studentId)
                ->get()
            : collect();
        $completed = $attempts->whereIn('status', ['Graded', 'Submitted']);
        $bestScore = $attempts->max('score');

        // Locked until every reading lesson is complete (unless already attempted).
        $remaining = $readingLessons
            ->reject(fn ($l) => optional($progressRecords->get($l->id))->isCompleted())
            ->count();

        $state = [
            'locked' => $remaining > 0 && $completed->isEmpty(),
            'remaining_lessons' => $remaining,
            'attempts_count' => $attempts->count(),
            'best_score' => $bestScore,
            'passed' => $bestScore !== null && $bestScore >= ($quiz->passing_score ?? 60),
            'can_retake' => !$quiz->max_attempts || $completed->count() < $quiz->max_attempts,
        ];

        return [$quiz, $state];
    }

    public function complete(Course $course, Lesson $lesson)
    {
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            return redirect()->route('checkout.show', $course)
                ->with('warning', 'Please complete at least a 30% deposit to access course content.');
        }

        if (!$lesson->module || $lesson->module->course_id !== $course->id) {
            abort(404);
        }

        $progress = LessonProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => 'Completed',
                'progress_percentage' => 100,
                'started_at' => now(),
                'completed_at' => now(),
                'last_accessed' => now(),
            ]
        );

        if (!$progress->wasRecentlyCreated) {
            $progress->update([
                'status' => 'Completed',
                'progress_percentage' => 100,
                'completed_at' => $progress->completed_at ?? now(),
                'last_accessed' => now(),
            ]);
        }

        // Recalculate enrollment progress and handle completion rewards atomically
        $totalLessons = $course->modules()->withCount('lessons')->get()->sum('lessons_count');
        $completedLessons = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('status', 'Completed')
            ->count();

        $enrollmentProgress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        $wasCompleted = $enrollment->enrollment_status !== 'Completed';

        DB::transaction(function () use ($enrollment, $enrollmentProgress, $wasCompleted) {
            $enrollment->update([
                'progress' => $enrollmentProgress,
                'enrollment_status' => $enrollmentProgress >= 100 ? 'Completed' : 'In Progress',
                'completion_date' => $enrollmentProgress >= 100 ? now() : null,
            ]);

            // Auto-issue certificate and badge on first completion
            if ($enrollmentProgress >= 100 && $wasCompleted) {
                $this->awardCompletionRewards($enrollment);
            }
        });

        return redirect()->route('student.learning.show', ['course' => $course, 'lesson' => $lesson])
            ->with('success', 'Lesson marked as complete!');
    }

    public function download(Course $course, Lesson $lesson)
    {
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            return redirect()->route('checkout.show', $course)
                ->with('warning', 'Please complete at least a 30% deposit to access course content.');
        }

        if (!$lesson->module || $lesson->module->course_id !== $course->id) {
            abort(404);
        }

        $service = new LessonExportService();
        $pdf = $service->generatePdf($course, $lesson);

        $filename = \Illuminate\Support\Str::slug($lesson->title) . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Auto-issue certificate and award completion badge if eligible.
     */
    protected function awardCompletionRewards($enrollment): void
    {
        // Certificate: auto-issue via service (handles race conditions internally)
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
