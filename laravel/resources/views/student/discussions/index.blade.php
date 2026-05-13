@extends('layouts.dashboard')

@section('title', 'Discussions - ' . $course->title)
@section('page_title', 'Course Discussions')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('enrollments.show', $course) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i>Back to Course
        </a>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Discussions</h2>
            <p class="text-sm text-gray-500">{{ $course->title }}</p>
        </div>
        <button onclick="toggleNewDiscussion()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm">
            <i class="fas fa-plus mr-1"></i>New Topic
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">{{ session('error') }}</div>
    @endif

    <!-- New Discussion Form -->
    <div id="new-discussion-form" class="hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Start a New Discussion</h3>
        <form action="{{ route('student.discussions.store', $course) }}" method="POST">
            @csrf
            <div class="mb-4">
                <input type="text" name="title" placeholder="Topic title" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mb-4">
                <textarea name="content" rows="4" placeholder="What's on your mind?" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm">Post Discussion</button>
                <button type="button" onclick="toggleNewDiscussion()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Discussions List -->
    <div class="space-y-4">
        @forelse($discussions as $discussion)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($discussion->is_pinned)
                                <span class="text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-medium"><i class="fas fa-thumbtack mr-1"></i>Pinned</span>
                            @endif
                            @if($discussion->is_locked)
                                <span class="text-xs bg-red-100 text-red-800 px-1.5 py-0.5 rounded font-medium"><i class="fas fa-lock mr-1"></i>Locked</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $discussion->created_at->diffForHumans() }}</span>
                        </div>
                        <a href="{{ route('student.discussions.show', [$course, $discussion]) }}" class="block">
                            <h3 class="font-semibold text-gray-900 dark:text-white hover:text-primary-600 transition-colors">{{ $discussion->title }}</h3>
                        </a>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ Str::limit(strip_tags($discussion->content), 150) }}</p>
                        <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
                            <span class="flex items-center"><i class="fas fa-user mr-1"></i>{{ $discussion->creator->full_name }}</span>
                            <span class="flex items-center"><i class="fas fa-eye mr-1"></i>{{ $discussion->view_count }} views</span>
                            <span class="flex items-center"><i class="fas fa-comment mr-1"></i>{{ $discussion->reply_count }} replies</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">No Discussions Yet</h3>
                <p class="text-gray-500 text-sm mt-1">Be the first to start a conversation!</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $discussions->links() }}
    </div>
</div>

<script>
function toggleNewDiscussion() {
    document.getElementById('new-discussion-form').classList.toggle('hidden');
}
</script>
@endsection
