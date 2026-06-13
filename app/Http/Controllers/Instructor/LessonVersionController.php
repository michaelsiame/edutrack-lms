<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonVersion;
use App\Models\Module;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;

class LessonVersionController extends Controller
{
    public function index(Course $course, Module $module, Lesson $lesson)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id) {
            abort(403, 'Invalid lesson or module.');
        }

        $versions = $lesson->versions()->with('creator')->paginate(20);

        return view('instructor.lessons.versions', compact('course', 'module', 'lesson', 'versions'));
    }

    public function restore(Course $course, Module $module, Lesson $lesson, LessonVersion $version)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id || $version->lesson_id !== $lesson->id) {
            abort(403, 'Invalid lesson or module.');
        }

        // Save current as version first
        $lesson->versions()->create([
            'content' => $lesson->content,
            'version_number' => ($lesson->versions()->max('version_number') ?? 0) + 1,
            'change_summary' => 'Auto-saved before restoring version #' . $version->version_number,
            'created_by' => auth()->id(),
        ]);

        $lesson->update([
            'content' => $version->content,
        ]);

        return back()->with('success', 'Lesson restored to version #' . $version->version_number);
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
