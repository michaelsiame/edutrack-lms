@extends('layouts.dashboard')

@section('title', $discussion->title . ' - Discussions')
@section('page_title','Discussion')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-back-link route="student.discussions.index" label="Back to Discussions" class="mb-4" />

    <!-- Original Post -->
    <x-card variant="elevated" class="mb-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-sm shrink-0">
                {{ strtoupper(substr($discussion->creator->first_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $discussion->creator->full_name }}</span>
                    @if($discussion->creator->isInstructor())
                        <x-status-badge status="Instructor" size="sm" />
                    @endif
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $discussion->created_at->diffForHumans() }}</span>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $discussion->title }}</h1>
                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                    {!! nl2br(e($discussion->content)) !!}
                </div>
            </div>
        </div>
    </x-card>

    <!-- Replies -->
    <div class="mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-comments text-primary-500"></i>
            {{ $discussion->replies->count() }} Replies
        </h3>

        <div class="space-y-4">
            @foreach($discussion->replies->whereNull('parent_reply_id') as $reply)
                <x-card variant="default" class="overflow-hidden">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 font-bold text-xs shrink-0">
                            {{ strtoupper(substr($reply->user->first_name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="font-semibold text-gray-900 dark:text-white text-sm">{{ $reply->user->full_name }}</span>
                                @if($reply->is_instructor_reply)
                                    <x-status-badge status="Instructor" size="sm" />
                                @endif
                                @if($reply->is_best_answer)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400">
                                        <i class="fas fa-check mr-1"></i>Best Answer
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                {!! nl2br(e($reply->content)) !!}
                            </div>

                            @if(!$discussion->is_locked)
                                <button onclick="toggleReplyForm({{ $reply->reply_id }})" class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 mt-3 font-medium transition-colors">
                                    <i class="fas fa-reply mr-1"></i>Reply
                                </button>
                                <div id="reply-form-{{ $reply->reply_id }}" class="hidden mt-3">
                                    <form action="{{ route('student.discussions.reply', [$course, $discussion]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="parent_reply_id" value="{{ $reply->reply_id }}">
                                        <textarea name="content" rows="2" placeholder="Write a reply..." required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm"></textarea>
                                        <div class="flex gap-2 mt-2">
                                            <x-button type="submit" variant="primary" size="sm">Post Reply</x-button>
                                            <x-button type="button" variant="ghost" size="sm" onclick="toggleReplyForm({{ $reply->reply_id }})">Cancel</x-button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            <!-- Child Replies -->
                            @foreach($reply->childReplies as $childReply)
                                <div class="mt-4 ml-6 pl-4 border-l-2 border-gray-200 dark:border-gray-600">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="font-semibold text-gray-900 dark:text-white text-xs">{{ $childReply->user->full_name }}</span>
                                        @if($childReply->is_instructor_reply)
                                            <x-status-badge status="Instructor" size="sm" />
                                        @endif
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $childReply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                        {!! nl2br(e($childReply->content)) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    </div>

    <!-- Reply to Discussion -->
    @if(!$discussion->is_locked)
        <x-card variant="default">
            <x-slot:header>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Post a Reply</h3>
            </x-slot:header>
            <form action="{{ route('student.discussions.reply', [$course, $discussion]) }}" method="POST">
                @csrf
                <textarea name="content" rows="4" placeholder="Share your thoughts..." required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm shadow-sm"></textarea>
                <div class="mt-3">
                    <x-button type="submit" variant="primary" icon="fa-paper-plane">Post Reply</x-button>
                </div>
            </form>
        </x-card>
    @else
        <x-card variant="default" class="bg-danger-50 dark:bg-danger-900/10 border-danger-200 dark:border-danger-800 text-center py-8">
            <i class="fas fa-lock text-danger-400 text-2xl mb-3"></i>
            <p class="text-danger-700 dark:text-danger-300 font-medium">This discussion is locked. No new replies can be posted.</p>
        </x-card>
    @endif
</div>

<script>
function toggleReplyForm(replyId) {
    document.getElementById('reply-form-' + replyId).classList.toggle('hidden');
}
</script>
@endsection
