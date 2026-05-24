<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Services\HtmlSanitizer;
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
            'lesson_type' => 'required|in:Video,Reading,Quiz,Assignment',
            'duration_minutes' => 'nullable|integer|min:1',
            'video_url' => 'nullable|url|max:500',
            'is_preview' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
            'linked_quiz_id' => 'nullable|exists:quizzes,id',
            'linked_assignment_id' => 'nullable|exists:assignments,id',
        ]);

        // Conditional validation based on lesson type
        if ($validated['lesson_type'] === 'Video' && empty($validated['video_url'])) {
            return back()->withInput()->withErrors(['video_url' => 'A video URL is required for video lessons.']);
        }

        if ($validated['lesson_type'] === 'Reading' && empty($validated['content'])) {
            return back()->withInput()->withErrors(['content' => 'Content is required for text/reading lessons.']);
        }

        $maxOrder = $module->lessons()->max('display_order') ?? 0;

        $lesson = Lesson::create([
            'module_id' => $module->id,
            'title' => $validated['title'],
            'content' => HtmlSanitizer::clean($validated['content'] ?? ''),
            'lesson_type' => $validated['lesson_type'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'is_preview' => $request->boolean('is_preview'),
            'display_order' => $validated['display_order'] ?? ($maxOrder + 1),
        ]);

        // Link quiz or assignment if selected
        if ($validated['lesson_type'] === 'Quiz' && !empty($validated['linked_quiz_id'])) {
            \App\Models\Quiz::where('id', $validated['linked_quiz_id'])
                ->where('course_id', $course->id)
                ->update(['lesson_id' => $lesson->id]);
        }

        if ($validated['lesson_type'] === 'Assignment' && !empty($validated['linked_assignment_id'])) {
            \App\Models\Assignment::where('id', $validated['linked_assignment_id'])
                ->where('course_id', $course->id)
                ->update(['lesson_id' => $lesson->id]);
        }

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
            'lesson_type' => 'required|in:Video,Reading,Quiz,Assignment',
            'duration_minutes' => 'nullable|integer|min:1',
            'video_url' => 'nullable|url|max:500',
            'is_preview' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
            'linked_quiz_id' => 'nullable|exists:quizzes,id',
            'linked_assignment_id' => 'nullable|exists:assignments,id',
        ]);

        // Conditional validation based on lesson type
        if ($validated['lesson_type'] === 'Video' && empty($validated['video_url'])) {
            return back()->withInput()->withErrors(['video_url' => 'A video URL is required for video lessons.']);
        }

        if ($validated['lesson_type'] === 'Reading' && empty($validated['content'])) {
            return back()->withInput()->withErrors(['content' => 'Content is required for text/reading lessons.']);
        }

        // Save current content as a version before updating
        $lesson->versions()->create([
            'content' => $lesson->content,
            'version_number' => ($lesson->versions()->max('version_number') ?? 0) + 1,
            'change_summary' => $request->input('change_summary', 'Content updated'),
            'created_by' => auth()->id(),
        ]);

        $lesson->update([
            'title' => $validated['title'],
            'content' => HtmlSanitizer::clean($validated['content'] ?? ''),
            'lesson_type' => $validated['lesson_type'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'is_preview' => $request->boolean('is_preview'),
            'display_order' => $validated['display_order'] ?? $lesson->display_order,
        ]);

        // Link quiz or assignment if selected
        if ($validated['lesson_type'] === 'Quiz' && !empty($validated['linked_quiz_id'])) {
            // Unlink any previously linked quiz for this lesson
            \App\Models\Quiz::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
            \App\Models\Quiz::where('id', $validated['linked_quiz_id'])
                ->where('course_id', $course->id)
                ->update(['lesson_id' => $lesson->id]);
        }

        if ($validated['lesson_type'] === 'Assignment' && !empty($validated['linked_assignment_id'])) {
            // Unlink any previously linked assignment for this lesson
            \App\Models\Assignment::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
            \App\Models\Assignment::where('id', $validated['linked_assignment_id'])
                ->where('course_id', $course->id)
                ->update(['lesson_id' => $lesson->id]);
        }

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
