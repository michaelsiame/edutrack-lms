<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Read-only course preview for staff (admins and the owning instructor) so
 * they can review notes and resources exactly as a learner would — without
 * enrolling, and without writing any progress. Authorisation is the standard
 * CoursePolicy 'view' (admin OR course owner).
 */
class CourseReadController extends Controller
{
    /** Jump to the first lesson of the course. */
    public function read(Course $course)
    {
        $this->authorize('view', $course);

        $firstLesson = $course->modules()->orderBy('display_order')
            ->with(['lessons' => fn ($q) => $q->orderBy('display_order')])
            ->get()
            ->flatMap->lessons
            ->first();

        if (!$firstLesson) {
            return redirect()->route('instructor.courses.show', $course)
                ->with('info', 'This course has no lessons yet.');
        }

        return redirect()->route('staff.courses.lesson', [$course, $firstLesson]);
    }

    /** Render a single lesson read-only, with the module/lesson sidebar. */
    public function lesson(Course $course, Lesson $lesson)
    {
        $this->authorize('view', $course);

        if (!$lesson->module || $lesson->module->course_id !== $course->id) {
            abort(404);
        }

        $lesson->load(['resources', 'quizzes', 'assignments']);

        $modules = $course->modules()
            ->with(['lessons' => fn ($q) => $q->orderBy('display_order')])
            ->orderBy('display_order')
            ->get();

        // Flat lesson list for prev/next.
        $allLessons = $modules->flatMap->lessons->values();
        $currentIndex = $allLessons->search(fn ($l) => $l->id === $lesson->id);
        $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $allLessons->count() - 1
            ? $allLessons[$currentIndex + 1] : null;

        return view('staff.course-read', compact('course', 'lesson', 'modules', 'prevLesson', 'nextLesson'));
    }

    /** Download a lesson resource (no enrolment gate; staff only). */
    public function resource(Course $course, Lesson $lesson, LessonResource $resource)
    {
        $this->authorize('view', $course);

        if (!$lesson->module || $lesson->module->course_id !== $course->id) {
            abort(404);
        }
        if ($resource->lesson_id !== $lesson->id || empty($resource->file_url)) {
            abort(404);
        }

        $name = $resource->title . '.' . $resource->resource_type;
        if (Storage::disk('local')->exists($resource->file_url)) {
            return Storage::disk('local')->download($resource->file_url, $name);
        }
        if (Storage::disk('public')->exists($resource->file_url)) {
            return Storage::disk('public')->download($resource->file_url, $name);
        }

        abort(404, 'File not found.');
    }
}
