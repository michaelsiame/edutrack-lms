<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscussionController extends Controller
{
    public function index(Course $course)
    {
        $this->authorizeAccess($course);
        $discussions = $course->discussions()
            ->with('creator')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('student.discussions.index', compact('course', 'discussions'));
    }

    public function show(Course $course, Discussion $discussion)
    {
        $this->authorizeAccess($course);
        $discussion->load(['creator', 'replies' => function ($q) {
            $q->with(['user', 'childReplies.user'])->whereNull('parent_reply_id')->orderBy('created_at');
        }]);
        $discussion->increment('view_count');
        return view('student.discussions.show', compact('course', 'discussion'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeAccess($course);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        Discussion::create([
            'course_id' => $course->id,
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'view_count' => 0,
            'reply_count' => 0,
        ]);

        return redirect()->route('student.discussions.index', $course)->with('success', 'Discussion posted successfully.');
    }

    public function reply(Request $request, Course $course, Discussion $discussion)
    {
        $this->authorizeAccess($course);
        if ($discussion->is_locked) {
            return back()->with('error', 'This discussion is locked.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'parent_reply_id' => 'nullable|exists:discussion_replies,reply_id',
        ]);

        $user = auth()->user();
        $isInstructor = $user->roles()->where('role_id', 2)->exists();

        DiscussionReply::create([
            'discussion_id' => $discussion->discussion_id,
            'parent_reply_id' => $validated['parent_reply_id'] ?? null,
            'user_id' => $user->user_id,
            'content' => $validated['content'],
            'is_instructor_reply' => $isInstructor,
            'is_best_answer' => false,
        ]);

        $discussion->increment('reply_count');

        return redirect()->route('student.discussions.show', [$course, $discussion])->with('success', 'Reply posted.');
    }

    private function authorizeAccess(Course $course)
    {
        $enrolled = auth()->user()->enrollments()->where('course_id', $course->id)->exists();
        $isStaff = auth()->user()->roles()->whereIn('role_id', [1, 2])->exists();
        if (!$enrolled && !$isStaff) {
            abort(403, 'You must be enrolled in this course.');
        }
    }
}
