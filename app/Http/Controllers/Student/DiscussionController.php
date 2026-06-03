<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Services\EmailQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscussionController extends Controller
{
    protected EmailQueueService $emailService;

    public function __construct(EmailQueueService $emailService)
    {
        $this->emailService = $emailService;
    }

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

        $discussion = Discussion::create([
            'course_id' => $course->id,
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'view_count' => 0,
            'reply_count' => 0,
        ]);

        // Notify course instructor about new discussion
        $this->notifyInstructorOfDiscussion($course, $discussion);

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
        $isInstructor = $user->isInstructor();

        $reply = DiscussionReply::create([
            'discussion_id' => $discussion->discussion_id,
            'parent_reply_id' => $validated['parent_reply_id'] ?? null,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'is_instructor_reply' => $isInstructor,
            'is_best_answer' => false,
        ]);

        $discussion->increment('reply_count');

        // Notify discussion creator (if not replying to own discussion)
        if ($discussion->created_by !== $user->id) {
            $this->emailService->sendNotification(
                $discussion->created_by,
                'New Reply: ' . $discussion->title,
                $user->full_name . ' replied to your discussion.',
                'info',
                route('student.discussions.show', [$course, $discussion])
            );
        }

        // Notify parent reply author (if replying to a reply, and not own reply)
        if (!empty($validated['parent_reply_id'])) {
            $parentReply = DiscussionReply::find($validated['parent_reply_id']);
            if ($parentReply && $parentReply->user_id !== $user->id) {
                $this->emailService->sendNotification(
                    $parentReply->user_id,
                    'New Reply to Your Comment',
                    $user->full_name . ' replied to your comment on "' . $discussion->title . '".',
                    'info',
                    route('student.discussions.show', [$course, $discussion])
                );
            }
        }

        return redirect()->route('student.discussions.show', [$course, $discussion])->with('success', 'Reply posted.');
    }

    public function updateReply(Request $request, Course $course, Discussion $discussion, DiscussionReply $reply)
    {
        $this->authorizeAccess($course);

        if ($reply->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You can only edit your own replies.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $reply->update(['content' => $validated['content']]);

        return redirect()->route('student.discussions.show', [$course, $discussion])->with('success', 'Reply updated.');
    }

    public function destroyReply(Course $course, Discussion $discussion, DiscussionReply $reply)
    {
        $this->authorizeAccess($course);

        if ($reply->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You can only delete your own replies.');
        }

        // Recursively delete child replies and count total deletions
        $deletedCount = $this->countChildReplies($reply) + 1; // +1 for the parent reply itself

        $this->deleteChildReplies($reply);
        $reply->delete();

        // Decrement by total deleted count, but not below zero
        $discussion->reply_count = max(0, $discussion->reply_count - $deletedCount);
        $discussion->save();

        return redirect()->route('student.discussions.show', [$course, $discussion])->with('success', 'Reply deleted.');
    }

    private function authorizeAccess(Course $course)
    {
        $user = auth()->user();
        $isAdminOrFinance = $user->isAdmin() || $user->isFinance();
        $isInstructor = $user->isInstructor() && $course->instructor_id === $user->instructor?->id;
        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();

        // Allow: admins, finance, course instructor, or enrolled students with access
        if (!$isAdminOrFinance && !$isInstructor && !$enrollment) {
            abort(403, 'You must be enrolled in this course to access discussions.');
        }

        if ($enrollment && !$enrollment->canAccessContent() && !$isAdminOrFinance && !$isInstructor) {
            abort(403, 'Please complete at least a 30% deposit to participate in discussions.');
        }
    }

    private function deleteChildReplies(DiscussionReply $reply): void
    {
        foreach ($reply->childReplies as $child) {
            $this->deleteChildReplies($child);
            $child->delete();
        }
    }

    private function countChildReplies(DiscussionReply $reply): int
    {
        $count = 0;
        foreach ($reply->childReplies as $child) {
            $count += 1 + $this->countChildReplies($child);
        }
        return $count;
    }

    private function notifyInstructorOfDiscussion(Course $course, Discussion $discussion): void
    {
        $instructor = $course->instructor?->user;
        if (!$instructor || $instructor->id === $discussion->created_by) {
            return;
        }

        $this->emailService->sendNotification(
            $instructor->id,
            'New Discussion in ' . $course->title,
            auth()->user()->full_name . ' started a new discussion: "' . $discussion->title . '"',
            'info',
            route('student.discussions.show', [$course, $discussion])
        );
    }
}
