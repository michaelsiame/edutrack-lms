<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LessonImageController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        // Must be an instructor or admin
        if (!$user || (!$user->isInstructor() && !$user->isAdmin())) {
            return response()->json(['error' => 'Unauthorized. Instructor access required.'], 403);
        }

        $validated = $request->validate([
            'file' => 'required|image|max:5120', // 5MB max
            'course_id' => 'nullable|integer|exists:courses,id',
        ]);

        // If course_id is provided, verify the instructor owns the course (admins bypass)
        if (!empty($validated['course_id'])) {
            $course = Course::find($validated['course_id']);
            if (!$user->isAdmin() && (!$course || $course->instructor_id !== $user->instructor?->id)) {
                return response()->json(['error' => 'Unauthorized. You do not own this course.'], 403);
            }
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowedExtensions)) {
            return response()->json(['error' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions)], 400);
        }

        $courseId = $validated['course_id'] ?? 'general';
        $filename = Str::uuid() . '.' . $extension;
        $path = 'lesson-images/' . $courseId . '/' . $filename;

        $file->storeAs('lesson-images/' . $courseId, $filename, 'public');

        $url = asset('storage/' . $path);

        return response()->json(['location' => $url]);
    }
}
