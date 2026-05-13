<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function store(Request $request, Course $course, Module $module)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id) {
            abort(403, 'Module does not belong to this course.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'lesson_type' => 'required|in:video,text,quiz,assignment',
            'duration_minutes' => 'nullable|integer|min:1',
            'video_url' => 'nullable|url|max:500',
            'is_preview' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $maxOrder = $module->lessons()->max('display_order') ?? 0;

        Lesson::create([
            'module_id' => $module->id,
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'lesson_type' => $validated['lesson_type'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'is_preview' => $request->boolean('is_preview'),
            'display_order' => $validated['display_order'] ?? ($maxOrder + 1),
        ]);

        return back()->with('success', 'Lesson created successfully.');
    }

    public function update(Request $request, Course $course, Module $module, Lesson $lesson)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id) {
            abort(403, 'Invalid lesson or module.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'lesson_type' => 'required|in:video,text,quiz,assignment',
            'duration_minutes' => 'nullable|integer|min:1',
            'video_url' => 'nullable|url|max:500',
            'is_preview' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $lesson->update([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'lesson_type' => $validated['lesson_type'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'is_preview' => $request->boolean('is_preview'),
            'display_order' => $validated['display_order'] ?? $lesson->display_order,
        ]);

        return back()->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Course $course, Module $module, Lesson $lesson)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id) {
            abort(403, 'Invalid lesson or module.');
        }

        $lesson->delete();

        return back()->with('success', 'Lesson deleted successfully.');
    }

    protected function authorizeInstructor(Course $course): void
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this course.');
        }
    }
}
