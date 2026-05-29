@extends('layouts.dashboard')

@section('title','Discussions - ' . $course->title)
@section('page_title','Course Discussions')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-4xl mx-auto">
        <x-back-link route="enrollments.show" :routeParams="[$course]" label="Back to Course" class="mb-4" variant="od" />

        <x-page-header title="Discussions" :subtitle="$course->title" actionHref="#" actionText="New Topic" actionIcon="fa-plus" variant="od" />

        <!-- New Discussion Form -->
        <div id="new-discussion-form" class="hidden mb-6">
            <div class="od-card">
                <h3 class="od-h3 mb-4">Start a New Discussion</h3>
                <form action="{{ route('student.discussions.store', $course) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <input type="text" name="title" placeholder="Topic title" required
                            class="w-full px-4 py-2.5 border rounded-xl text-sm shadow-sm"
                            style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);">
                    </div>
                    <div class="mb-4">
                        <textarea name="content" rows="4" placeholder="What's on your mind?" required
                            class="w-full px-4 py-2.5 border rounded-xl text-sm shadow-sm resize-y"
                            style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="od-btn od-btn-primary od-btn-sm"><i class="fas fa-paper-plane"></i> Post Discussion</button>
                        <button type="button" class="od-btn od-btn-ghost od-btn-sm" onclick="toggleNewDiscussion()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Discussions List -->
        <div class="space-y-4">
            @forelse($discussions as $discussion)
                <div class="od-card">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
                            {{ strtoupper(substr($discussion->creator->first_name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                @if($discussion->is_pinned)
                                    <span class="od-badge od-badge-warn">Pinned</span>
                                @endif
                                @if($discussion->is_locked)
                                    <span class="od-badge od-badge-danger">Locked</span>
                                @endif
                                <span class="od-meta">{{ $discussion->created_at->diffForHumans() }}</span>
                            </div>
                            <a href="{{ route('student.discussions.show', [$course, $discussion]) }}" class="block group">
                                <h3 class="font-semibold group-hover:opacity-70 transition-opacity" style="color: var(--od-fg);">{{ $discussion->title }}</h3>
                            </a>
                            <p class="text-sm mt-1 line-clamp-2 leading-relaxed" style="color: var(--od-muted);">{{ Str::limit(strip_tags($discussion->content), 150) }}</p>
                            <div class="flex items-center gap-4 mt-3 text-xs" style="color: var(--od-muted);">
                                <span class="flex items-center gap-1"><i class="fas fa-user text-[10px]"></i>{{ $discussion->creator->full_name }}</span>
                                <span class="flex items-center gap-1"><i class="fas fa-eye text-[10px]"></i>{{ $discussion->view_count }} views</span>
                                <span class="flex items-center gap-1"><i class="fas fa-comment text-[10px]"></i>{{ $discussion->reply_count }} replies</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="od-card">
                    <x-empty-state icon="fa-comments" title="No Discussions Yet" description="Be the first to start a conversation!" variant="od" />
                </div>
            @endforelse
        </div>

        @if($discussions->hasPages())
            <div class="mt-6">
                {{ $discussions->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function toggleNewDiscussion() {
    document.getElementById('new-discussion-form').classList.toggle('hidden');
}
</script>
@endsection
