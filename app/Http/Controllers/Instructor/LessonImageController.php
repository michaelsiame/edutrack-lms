<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LessonImageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|image|max:5120', // 5MB max
            'course_id' => 'nullable|integer',
        ]);

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
