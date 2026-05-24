<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonResource;
use Illuminate\Support\Facades\Storage;

class LessonResourceController extends Controller
{
    public function download(Course $course, Lesson $lesson, LessonResource $resource)
    {
        // Verify student is enrolled
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->where('enrollment_status', '!=', 'Dropped')
            ->first();

        if (!$enrollment) {
            abort(403, 'You must be enrolled in this course to download resources.');
        }

        if ($resource->lesson_id !== $lesson->id) {
            abort(404);
        }

        if (!$resource->file_url || !Storage::disk('public')->exists($resource->file_url)) {
            abort(404, 'File not found.');
        }

        $resource->increment('download_count');

        return Storage::disk('public')->download($resource->file_url, $resource->title . '.' . $resource->resource_type);
    }
}
