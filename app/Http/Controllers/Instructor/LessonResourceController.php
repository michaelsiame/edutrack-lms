<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonResourceController extends Controller
{
    public function store(Request $request, Course $course, Module $module, Lesson $lesson)
    {
        $this->authorizeInstructor($course);

        if ($lesson->module_id !== $module->id || $module->course_id !== $course->id) {
            abort(403, 'Invalid lesson for this course.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'resource_file' => 'required|file|max:51200', // 50MB max
        ]);

        $file = $request->file('resource_file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mp3'];
        if (!in_array($extension, $allowedExtensions)) {
            return back()->with('error', 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions));
        }

        $path = $file->store('lesson-resources/' . $course->id, 'local');
        $fileSizeKb = round($file->getSize() / 1024);

        LessonResource::create([
            'lesson_id' => $lesson->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'resource_type' => $extension,
            'file_url' => $path,
            'file_size_kb' => $fileSizeKb,
            'download_count' => 0,
        ]);

        return back()->with('success', 'Resource uploaded successfully.');
    }

    public function destroy(Course $course, Module $module, Lesson $lesson, LessonResource $resource)
    {
        $this->authorizeInstructor($course);

        if ($resource->lesson_id !== $lesson->id) {
            abort(403);
        }

        if ($resource->file_url) {
            if (Storage::disk('local')->exists($resource->file_url)) {
                Storage::disk('local')->delete($resource->file_url);
            } elseif (Storage::disk('public')->exists($resource->file_url)) {
                Storage::disk('public')->delete($resource->file_url);
            }
        }

        $resource->delete();

        return back()->with('success', 'Resource deleted successfully.');
    }

    protected function authorizeInstructor(Course $course): void
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this course.');
        }
    }
}
