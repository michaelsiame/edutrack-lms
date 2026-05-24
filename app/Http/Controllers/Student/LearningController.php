<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\LessonExportService;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    public function show(Course $course, Lesson $lesson)
    {
        // Verify enrollment
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        // Verify lesson belongs to course
        if ($lesson->module->course_id !== $course->id) {
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

        return view('student.learning.show', compact('lesson', 'course', 'modules', 'progress', 'enrollment'));
    }

    public function complete(Course $course, Lesson $lesson)
    {
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        if ($lesson->module->course_id !== $course->id) {
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

        // Recalculate enrollment progress
        $totalLessons = $course->modules()->withCount('lessons')->get()->sum('lessons_count');
        $completedLessons = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('status', 'Completed')
            ->count();

        $enrollmentProgress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        $enrollment->update([
            'progress' => $enrollmentProgress,
            'enrollment_status' => $enrollmentProgress >= 100 ? 'completed' : 'active',
        ]);

        return redirect()->route('student.learning.show', ['course' => $course, 'lesson' => $lesson])
            ->with('success', 'Lesson marked as complete!');
    }

    public function download(Course $course, Lesson $lesson)
    {
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->firstOrFail();

        if ($lesson->module->course_id !== $course->id) {
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
}
