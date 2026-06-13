<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function pin(Course $course, Discussion $discussion)
    {
        $this->authorizeInstructor($course);

        if ($discussion->course_id !== $course->id) {
            abort(404);
        }

        $discussion->update(['is_pinned' => !$discussion->is_pinned]);

        $status = $discussion->is_pinned ? 'pinned' : 'unpinned';
        return back()->with('success', "Discussion {$status}.");
    }

    public function lock(Course $course, Discussion $discussion)
    {
        $this->authorizeInstructor($course);

        if ($discussion->course_id !== $course->id) {
            abort(404);
        }

        $discussion->update(['is_locked' => !$discussion->is_locked]);

        $status = $discussion->is_locked ? 'locked' : 'unlocked';
        return back()->with('success', "Discussion {$status}.");
    }

    public function destroy(Course $course, Discussion $discussion)
    {
        $this->authorizeInstructor($course);

        if ($discussion->course_id !== $course->id) {
            abort(404);
        }

        // Recursively delete all replies first
        foreach ($discussion->replies()->whereNull('parent_reply_id')->get() as $reply) {
            $this->deleteChildReplies($reply);
            $reply->delete();
        }

        $discussion->delete();

        return redirect()->route('student.discussions.index', $course)
            ->with('success', 'Discussion deleted.');
    }

    public function markBestAnswer(Course $course, Discussion $discussion, DiscussionReply $reply)
    {
        $this->authorizeInstructor($course);

        if ($discussion->course_id !== $course->id || $reply->discussion_id !== $discussion->discussion_id) {
            abort(404);
        }

        // Unmark any existing best answer on this discussion
        $discussion->replies()->where('is_best_answer', true)->update(['is_best_answer' => false]);

        // Toggle: if the clicked reply was already best answer, unmark it; otherwise mark it
        $wasBest = $reply->is_best_answer;
        $reply->update(['is_best_answer' => !$wasBest]);

        $status = !$wasBest ? 'marked as best answer' : 'unmarked';
        return back()->with('success', "Reply {$status}.");
    }

    private function deleteChildReplies(DiscussionReply $reply): void
    {
        foreach ($reply->childReplies as $child) {
            $this->deleteChildReplies($child);
            $child->delete();
        }
    }

    protected function authorizeInstructor(Course $course): void
    {
        if (auth()->user()?->isAdmin()) {
            return;
        }

        $instructor = auth()->user()->instructor;
        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this course.');
        }
    }
}
