@extends('layouts.dashboard')

@section('title', $discussion->title .' - Discussions')
@section('page_title','Discussion')

@section('content')
<div class="max-w-4xl mx-auto">
 <div class="mb-4">
 <a href="{{ route('student.discussions.index', $course) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
 <i class="fas fa-arrow-left mr-1"></i>Back to Discussions
 </a>
 </div>

 <!-- Original Post -->
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-6">
 <div class="flex items-start gap-4">
 <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-sm flex-shrink-0">
 {{ strtoupper(substr($discussion->creator->first_name, 0, 1)) }}
 </div>
 <div class="flex-1">
 <div class="flex items-center gap-2 mb-1">
 <span class="font-medium text-gray-900 dark:text-white">{{ $discussion->creator->full_name }}</span>
 @if($discussion->creator->isInstructor())
 <span class="text-xs bg-primary-100 text-primary-800 px-1.5 py-0.5 rounded">Instructor</span>
 @endif
 <span class="text-xs text-gray-400">{{ $discussion->created_at->diffForHumans() }}</span>
 </div>
 <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $discussion->title }}</h1>
 <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
 {!! nl2br(e($discussion->content)) !!}
 </div>
 </div>
 </div>
 </div>

 <!-- Replies -->
 <div class="mb-6">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $discussion->replies->count() }} Replies</h3>

 <div class="space-y-4">
 @foreach($discussion->replies->whereNull('parent_reply_id') as $reply)
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
 <div class="flex items-start gap-4">
 <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 font-bold text-xs flex-shrink-0">
 {{ strtoupper(substr($reply->user->first_name, 0, 1)) }}
 </div>
 <div class="flex-1">
 <div class="flex items-center gap-2 mb-1">
 <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $reply->user->full_name }}</span>
 @if($reply->is_instructor_reply)
 <span class="text-xs bg-primary-100 text-primary-800 px-1.5 py-0.5 rounded">Instructor</span>
 @endif
 @if($reply->is_best_answer)
 <span class="text-xs bg-success-100 text-success-800 px-1.5 py-0.5 rounded"><i class="fas fa-check mr-1"></i>Best Answer</span>
 @endif
 <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
 </div>
 <div class="text-sm text-gray-700 dark:text-gray-300">
 {!! nl2br(e($reply->content)) !!}
 </div>

 <!-- Reply Form -->
 @if(!$discussion->is_locked)
 <button onclick="toggleReplyForm({{ $reply->reply_id }})" class="text-xs text-primary-600 hover:text-primary-700 mt-2 font-medium">
 <i class="fas fa-reply mr-1"></i>Reply
 </button>
 <div id="reply-form-{{ $reply->reply_id }}" class="hidden mt-3">
 <form action="{{ route('student.discussions.reply', [$course, $discussion]) }}" method="POST">
 @csrf
 <input type="hidden" name="parent_reply_id" value="{{ $reply->reply_id }}">
 <textarea name="content" rows="2" placeholder="Write a reply..." required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
 <div class="flex gap-2 mt-2">
 <button type="submit" class="px-3 py-1.5 bg-primary-600 text-white text-xs rounded hover:bg-primary-700">Post Reply</button>
 <button type="button" onclick="toggleReplyForm({{ $reply->reply_id }})" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300">Cancel</button>
 </div>
 </form>
 </div>
 @endif

 <!-- Child Replies -->
 @foreach($reply->childReplies as $childReply)
 <div class="mt-3 ml-8 pl-4 border-l-2 border-gray-200 dark:border-gray-600">
 <div class="flex items-center gap-2 mb-1">
 <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $childReply->user->full_name }}</span>
 @if($childReply->is_instructor_reply)
 <span class="text-xs bg-primary-100 text-primary-800 px-1 py-0.5 rounded">Instructor</span>
 @endif
 <span class="text-xs text-gray-400">{{ $childReply->created_at->diffForHumans() }}</span>
 </div>
 <div class="text-xs text-gray-600 dark:text-gray-400">
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
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
 <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Post a Reply</h3>
 <form action="{{ route('student.discussions.reply', [$course, $discussion]) }}" method="POST">
 @csrf
 <textarea name="content" rows="4" placeholder="Share your thoughts..." required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
 <button type="submit" class="mt-3 px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">Post Reply</button>
 </form>
 </div>
 @else
 <div class="bg-danger-50 border border-danger-200 rounded-xl p-6 text-center">
 <i class="fas fa-lock text-danger-400 text-2xl mb-2"></i>
 <p class="text-danger-700 font-medium">This discussion is locked. No new replies can be posted.</p>
 </div>
 @endif
</div>

<script>
function toggleReplyForm(replyId) {
 document.getElementById('reply-form-' + replyId).classList.toggle('hidden');
}
</script>
@endsection
