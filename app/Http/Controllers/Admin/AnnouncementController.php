<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with(['course', 'poster'])
            ->latest()
            ->paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $courses = Course::published()->orderBy('title')->get();
        return view('admin.announcements.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'course_id' => 'nullable|exists:courses,id',
            'announcement_type' => 'required|in:general,course,system,urgent',
            'priority' => 'required|in:low,normal,high,urgent',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
        ]);

        Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'course_id' => $validated['course_id'] ?? null,
            'posted_by' => auth()->id(),
            'announcement_type' => $validated['announcement_type'],
            'priority' => $validated['priority'],
            'is_published' => $request->boolean('is_published'),
            'published_at' => $validated['published_at'] ?? now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        $courses = Course::published()->orderBy('title')->get();
        return view('admin.announcements.edit', compact('announcement', 'courses'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'course_id' => 'nullable|exists:courses,id',
            'announcement_type' => 'required|in:general,course,system,urgent',
            'priority' => 'required|in:low,normal,high,urgent',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'course_id' => $validated['course_id'] ?? null,
            'announcement_type' => $validated['announcement_type'],
            'priority' => $validated['priority'],
            'is_published' => $request->boolean('is_published'),
            'published_at' => $validated['published_at'] ?? $announcement->published_at,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Announcement deleted successfully.');
    }
}
