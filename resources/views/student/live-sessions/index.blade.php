@extends('layouts.dashboard')

@section('title','Live Sessions - ' . $course->title)
@section('page_title','Live Sessions')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-back-link route="enrollments.show" :routeParams="[$course]" label="Back to Course" class="mb-4" />

    <x-page-header title="Live Sessions" :subtitle="$course->title" />

    <!-- Live Now Banner -->
    @php $liveSession = $sessions->firstWhere('status','live'); @endphp
    @if($liveSession)
        <x-card variant="default" class="mb-6 bg-danger-50 dark:bg-danger-900/10 border-danger-200 dark:border-danger-800">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-danger-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-danger-500"></span>
                    </span>
                    <div>
                        <h3 class="font-bold text-danger-800 dark:text-danger-300 flex items-center gap-2">
                            LIVE NOW
                        </h3>
                        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $liveSession->description ?: 'Live class session' }}</p>
                    </div>
                </div>
                <x-button :href="route('student.live-sessions.join', $liveSession)" variant="danger" icon="fa-video" target="_blank">
                    Join Now
                </x-button>
            </div>
        </x-card>
    @endif

    <!-- Sessions List -->
    <div class="space-y-4">
        @forelse($sessions as $session)
            <x-card variant="interactive" class="overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            @if($session->isLive())
                                <x-status-badge status="Live" size="sm" pulse />
                            @elseif($session->isUpcoming())
                                <x-status-badge status="Upcoming" size="sm" />
                            @else
                                <x-status-badge status="Completed" size="sm" />
                            @endif
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $session->scheduled_start_time->format('M j, Y g:i A') }}</span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $session->description ?: 'Live class session' }}</p>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-400 dark:text-gray-500">
                            <span class="flex items-center gap-1"><i class="fas fa-clock"></i>{{ $session->scheduled_start_time->diffInMinutes($session->scheduled_end_time) }} min</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($session->isLive())
                            <x-button :href="route('student.live-sessions.join', $session)" variant="danger" size="sm" icon="fa-video" target="_blank">
                                Join
                            </x-button>
                        @elseif($session->isUpcoming())
                            <span class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm rounded-xl font-medium cursor-not-allowed">
                                Starts {{ $session->scheduled_start_time->diffForHumans() }}
                            </span>
                        @else
                            @if($session->recording_url)
                                <x-button :href="$session->recording_url" variant="primary" size="sm" icon="fa-play" target="_blank">
                                    Recording
                                </x-button>
                            @else
                                <span class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm rounded-xl font-medium">Ended</span>
                            @endif
                        @endif
                    </div>
                </div>
            </x-card>
        @empty
            <x-card variant="default">
                <x-empty-state icon="fa-video" title="No Live Sessions" description="Your instructor hasn't scheduled any live sessions yet." />
            </x-card>
        @endforelse
    </div>
</div>
@endsection
