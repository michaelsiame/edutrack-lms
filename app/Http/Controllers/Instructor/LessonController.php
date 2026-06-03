<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        // Link quiz or assignment if selected; unlink stale references on type change
        if ($validated['lesson_type'] === 'Quiz' && !empty($validated['linked_quiz_id'])) {
            \App\Models\Quiz::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
            \App\Models\Quiz::where('id', $validated['linked_quiz_id'])
                ->where('course_id', $course->id)
                ->update(['lesson_id' => $lesson->id]);
        } elseif ($validated['lesson_type'] !== 'Quiz') {
            \App\Models\Quiz::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
        }

        if ($validated['lesson_type'] === 'Assignment' && !empty($validated['linked_assignment_id'])) {
            \App\Models\Assignment::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
            \App\Models\Assignment::where('id', $validated['linked_assignment_id'])
                ->where('course_id', $course->id)
                ->update(['lesson_id' => $lesson->id]);
        } elseif ($validated['lesson_type'] !== 'Assignment') {
            \App\Models\Assignment::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
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

        // Link quiz or assignment if selected; unlink stale references on type change
        if ($validated['lesson_type'] === 'Quiz' && !empty($validated['linked_quiz_id'])) {
            \App\Models\Quiz::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
            \App\Models\Quiz::where('id', $validated['linked_quiz_id'])
                ->where('course_id', $course->id)
                ->update(['lesson_id' => $lesson->id]);
        } elseif ($validated['lesson_type'] !== 'Quiz') {
            \App\Models\Quiz::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
        }

        if ($validated['lesson_type'] === 'Assignment' && !empty($validated['linked_assignment_id'])) {
            \App\Models\Assignment::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
            \App\Models\Assignment::where('id', $validated['linked_assignment_id'])
                ->where('course_id', $course->id)
                ->update(['lesson_id' => $lesson->id]);
        } elseif ($validated['lesson_type'] !== 'Assignment') {
            \App\Models\Assignment::where('lesson_id', $lesson->id)->update(['lesson_id' => null]);
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

    public function moveUp(Course $course, Module $module, Lesson $lesson)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id) {
            abort(403, 'Invalid lesson or module.');
        }

        $prevLesson = Lesson::where('module_id', $module->id)
            ->where('display_order', '<', $lesson->display_order)
            ->orderBy('display_order', 'desc')
            ->first();

        if ($prevLesson) {
            $temp = $lesson->display_order;
            $lesson->update(['display_order' => $prevLesson->display_order]);
            $prevLesson->update(['display_order' => $temp]);
        }

        return back();
    }

    public function moveDown(Course $course, Module $module, Lesson $lesson)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id) {
            abort(403, 'Invalid lesson or module.');
        }

        $nextLesson = Lesson::where('module_id', $module->id)
            ->where('display_order', '>', $lesson->display_order)
            ->orderBy('display_order')
            ->first();

        if ($nextLesson) {
            $temp = $lesson->display_order;
            $lesson->update(['display_order' => $nextLesson->display_order]);
            $nextLesson->update(['display_order' => $temp]);
        }

        return back();
    }

    public function bulkUpload(Request $request, Course $course)
    {
        $this->authorizeInstructor($course);

        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Could not read the uploaded file.');
        }

        // Read header
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty.');
        }

        $expectedHeader = ['module_id', 'title', 'lesson_type', 'content', 'duration_minutes', 'video_url', 'is_preview', 'display_order'];
        $normalizedHeader = array_map('strtolower', array_map('trim', $header));

        if ($normalizedHeader !== $expectedHeader) {
            fclose($handle);
            return back()->with('error', 'Invalid CSV header. Expected: ' . implode(', ', $expectedHeader));
        }

        $created = 0;
        $errors = [];
        $rowNumber = 1;

        DB::transaction(function () use ($handle, $course, &$created, &$errors, &$rowNumber) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if (count($row) !== count($expectedHeader)) {
                    $errors[] = "Row {$rowNumber}: Column count mismatch.";
                    continue;
                }

                $data = array_combine($expectedHeader, $row);
                $data = array_map('trim', $data);

                // Validate module belongs to course
                $module = Module::where('id', $data['module_id'])
                    ->where('course_id', $course->id)
                    ->first();

                if (!$module) {
                    $errors[] = "Row {$rowNumber}: Invalid module_id ({$data['module_id']}).";
                    continue;
                }

                // Basic validation
                $rowValidator = Validator::make($data, [
                    'title' => 'required|string|max:255',
                    'lesson_type' => 'required|in:Video,Reading,Quiz,Assignment',
                    'duration_minutes' => 'nullable|integer|min:1',
                    'video_url' => 'nullable|url|max:500',
                    'is_preview' => 'nullable|in:0,1,true,false,yes,no',
                ]);

                if ($rowValidator->fails()) {
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $rowValidator->errors()->all());
                    continue;
                }

                $displayOrder = is_numeric($data['display_order']) ? (int) $data['display_order'] : null;
                if (!$displayOrder) {
                    $displayOrder = ($module->lessons()->max('display_order') ?? 0) + 1;
                }

                $isPreview = in_array(strtolower($data['is_preview']), ['1', 'true', 'yes']);

                $lesson = Lesson::create([
                    'module_id' => $module->id,
                    'title' => $data['title'],
                    'content' => HtmlSanitizer::clean($data['content'] ?? ''),
                    'lesson_type' => $data['lesson_type'],
                    'duration_minutes' => $data['duration_minutes'] ?: null,
                    'video_url' => $data['video_url'] ?: null,
                    'is_preview' => $isPreview,
                    'display_order' => $displayOrder,
                ]);

                $created++;
            }
        });

        fclose($handle);

        $message = "{$created} lessons imported successfully.";
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' rows had errors: ' . implode('; ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= ' (and ' . (count($errors) - 3) . ' more)';
            }
            return back()->with('warning', $message);
        }

        return back()->with('success', $message);
    }

    protected function authorizeInstructor(Course $course): void
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this course.');
        }
    }
}
