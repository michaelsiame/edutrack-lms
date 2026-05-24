@extends('layouts.dashboard')

@section('title','Discussions - ' . $course->title)
@section('page_title','Course Discussions')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-back-link route="enrollments.show" :routeParams="[$course]" label="Back to Course" class="mb-4" />

    <x-page-header title="Discussions" :subtitle="$course->title" actionHref="#" actionText="New Topic" actionIcon="fa-plus" />

    <!-- New Discussion Form -->
    <div id="new-discussion-form" class="hidden mb-6">
        <x-card variant="elevated">
            <x-slot:header>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Start a New Discussion</h3>
            </x-slot:header>

            <form action="{{ route('student.discussions.store', $course) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <input type="text" name="title" placeholder="Topic title" required
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm shadow-sm">
                </div>
                <div class="mb-4">
                    <textarea name="content" rows="4" placeholder="What's on your mind?" required
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm shadow-sm"></textarea>
                </div>
                <div class="flex gap-3">
                    <x-button type="submit" variant="primary" size="sm" icon="fa-paper-plane">Post Discussion</x-button>
                    <x-button type="button" variant="ghost" size="sm" onclick="toggleNewDiscussion()">Cancel</x-button>
                </div>
            </form>
        </x-card>
    </div>

    <!-- Discussions List -->
    <div class="space-y-4">
        @forelse($discussions as $discussion)
            <x-card variant="interactive" class="overflow-hidden">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-sm shrink-0">
                        {{ strtoupper(substr($discussion->creator->first_name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            @if($discussion->is_pinned)
                                <x-status-badge status="Pinned" size="sm" />
                            @endif
                            @if($discussion->is_locked)
                                <x-status-badge status="Locked" size="sm" />
                            @endif
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $discussion->created_at->diffForHumans() }}</span>
                        </div>
                        <a href="{{ route('student.discussions.show', [$course, $discussion]) }}" class="block group">
                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $discussion->title }}</h3>
                        </a>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ Str::limit(strip_tags($discussion->content), 150) }}</p>
                        <div class="flex items-center gap-4 mt-3 text-xs text-gray-400 dark:text-gray-500">
                            <span class="flex items-center gap-1"><i class="fas fa-user text-[10px]"></i>{{ $discussion->creator->full_name }}</span>
                            <span class="flex items-center gap-1"><i class="fas fa-eye text-[10px]"></i>{{ $discussion->view_count }} views</span>
                            <span class="flex items-center gap-1"><i class="fas fa-comment text-[10px]"></i>{{ $discussion->reply_count }} replies</span>
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <x-card variant="default">
                <x-empty-state icon="fa-comments" title="No Discussions Yet" description="Be the first to start a conversation!" />
            </x-card>
        @endforelse
    </div>

    @if($discussions->hasPages())
        <div class="mt-6">
            {{ $discussions->links() }}
        </div>
    @endif
</div>

<script>
function toggleNewDiscussion() {
    document.getElementById('new-discussion-form').classList.toggle('hidden');
}
</script>
@endsection
