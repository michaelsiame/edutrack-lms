@extends('layouts.dashboard')

@section('title', $discussion->title . ' - Discussions')
@section('page_title','Discussion')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    @php
        $isCourseInstructor = auth()->check() && auth()->user()->isInstructor() && $course->instructor_id === auth()->user()->instructor?->id;
    @endphp

    <div class="max-w-4xl mx-auto">
        <x-back-link route="student.discussions.index" :routeParams="[$course]" label="Back to Discussions" class="mb-4" variant="od" />

        <!-- Original Post -->
        <div class="od-card mb-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
                    {{ strtoupper(substr($discussion->creator->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="font-semibold" style="color: var(--od-fg);">{{ $discussion->creator->full_name }}</span>
                        @if($discussion->creator->isInstructor())
                            <span class="od-badge od-badge-info">Instructor</span>
                        @endif
                        @if($discussion->is_pinned)
                            <span class="od-badge od-badge-warn"><i class="fas fa-thumbtack mr-1"></i>Pinned</span>
                        @endif
                        @if($discussion->is_locked)
                            <span class="od-badge od-badge-danger"><i class="fas fa-lock mr-1"></i>Locked</span>
                        @endif
                        <span class="od-meta">{{ $discussion->created_at->diffForHumans() }}</span>
                    </div>
                    <h1 class="od-h2 mb-3">{{ $discussion->title }}</h1>
                    <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed" style="color: var(--od-muted);">
                        {!! nl2br(e($discussion->content)) !!}
                    </div>
                    @if($isCourseInstructor)
                        <div class="flex flex-wrap items-center gap-3 mt-4 pt-3" style="border-top: 1px solid var(--od-border);">
                            <form action="{{ route('instructor.discussions.pin', [$course, $discussion]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="od-btn od-btn-ghost od-btn-sm">
                                    <i class="fas fa-thumbtack mr-1"></i>{{ $discussion->is_pinned ? 'Unpin' : 'Pin' }}
                                </button>
                            </form>
                            <form action="{{ route('instructor.discussions.lock', [$course, $discussion]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="od-btn od-btn-ghost od-btn-sm">
                                    <i class="fas fa-lock mr-1"></i>{{ $discussion->is_locked ? 'Unlock' : 'Lock' }}
                                </button>
                            </form>
                            <form action="{{ route('instructor.discussions.destroy', [$course, $discussion]) }}" method="POST" class="inline" data-confirm="Delete this discussion and all its replies?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="od-btn od-btn-ghost od-btn-sm text-danger-600 hover:text-danger-700">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Replies -->
        <div class="mb-6">
            <h3 class="od-h3 mb-4 flex items-center gap-2">
                <i class="fas fa-comments" style="color: var(--od-navy);"></i>
                {{ $discussion->replies->count() }} Replies
            </h3>

            <div class="space-y-4">
                @foreach($discussion->replies->whereNull('parent_reply_id') as $reply)
                    <div class="od-card">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0" style="background: var(--od-fg-soft); color: var(--od-muted);">
                                {{ strtoupper(substr($reply->user->first_name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="font-semibold text-sm" style="color: var(--od-fg);">{{ $reply->user->full_name }}</span>
                                    @if($reply->is_instructor_reply)
                                        <span class="od-badge od-badge-info">Instructor</span>
                                    @endif
                                    @if($reply->is_best_answer)
                                        <span class="od-badge od-badge-success"><i class="fas fa-check mr-1"></i>Best Answer</span>
                                    @endif
                                    <span class="od-meta">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-sm leading-relaxed" style="color: var(--od-fg);">
                                    {!! nl2br(e($reply->content)) !!}
                                </div>

                                <div class="flex flex-wrap items-center gap-3 mt-3">
                                    @if(!$discussion->is_locked)
                                        <button onclick="toggleReplyForm({{ $reply->reply_id }})" class="text-xs font-medium transition-colors" style="color: var(--od-navy);">
                                            <i class="fas fa-reply mr-1"></i>Reply
                                        </button>
                                    @endif
                                    @if($isCourseInstructor)
                                        <form action="{{ route('instructor.discussions.best-answer', [$course, $discussion, $reply]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium transition-colors {{ $reply->is_best_answer ? 'text-green-600' : 'text-gray-500 hover:text-green-600' }}">
                                                <i class="fas fa-check-circle mr-1"></i>{{ $reply->is_best_answer ? 'Remove Best Answer' : 'Mark Best Answer' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                    <div id="reply-form-{{ $reply->reply_id }}" class="hidden mt-3">
                                        <form action="{{ route('student.discussions.reply', [$course, $discussion]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="parent_reply_id" value="{{ $reply->reply_id }}">
                                            <textarea name="content" rows="2" placeholder="Write a reply..." required
                                                class="w-full px-3 py-2 border rounded-xl text-sm shadow-sm resize-y"
                                                style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);"></textarea>
                                            <div class="flex gap-2 mt-2">
                                                <button type="submit" class="od-btn od-btn-primary od-btn-sm">Post Reply</button>
                                                <button type="button" class="od-btn od-btn-ghost od-btn-sm" onclick="toggleReplyForm({{ $reply->reply_id }})">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                <!-- Child Replies -->
                                @foreach($reply->childReplies as $childReply)
                                    <div class="mt-4 ml-6 pl-4" style="border-left: 2px solid var(--od-border);">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="font-semibold text-xs" style="color: var(--od-fg);">{{ $childReply->user->full_name }}</span>
                                            @if($childReply->is_instructor_reply)
                                                <span class="od-badge od-badge-info">Instructor</span>
                                            @endif
                                            <span class="od-meta">{{ $childReply->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-xs leading-relaxed" style="color: var(--od-muted);">
                                            {!! nl2br(e($childReply->content)) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Reply to Discussion -->
        @if(!$discussion->is_locked)
            <div class="od-card">
                <h3 class="od-h3 mb-4">Post a Reply</h3>
                <form action="{{ route('student.discussions.reply', [$course, $discussion]) }}" method="POST">
                    @csrf
                    <textarea name="content" rows="4" placeholder="Share your thoughts..." required
                        class="w-full px-4 py-3 border rounded-xl text-sm shadow-sm resize-y"
                        style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);"></textarea>
                    <div class="mt-3">
                        <button type="submit" class="od-btn od-btn-primary"><i class="fas fa-paper-plane"></i> Post Reply</button>
                    </div>
                </form>
            </div>
        @else
            <div class="od-card text-center py-8" style="background: color-mix(in oklch, var(--od-danger) 5%, transparent); border-color: color-mix(in oklch, var(--od-danger) 20%, transparent);">
                <i class="fas fa-lock text-2xl mb-3" style="color: var(--od-danger);"></i>
                <p class="font-medium" style="color: var(--od-danger);">This discussion is locked. No new replies can be posted.</p>
            </div>
        @endif
    </div>
</div>

<script>
function toggleReplyForm(replyId) {
    document.getElementById('reply-form-' + replyId).classList.toggle('hidden');
}
</script>
@endsection
