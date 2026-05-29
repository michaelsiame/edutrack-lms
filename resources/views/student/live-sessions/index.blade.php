@extends('layouts.dashboard')

@section('title','Live Sessions - ' . $course->title)
@section('page_title','Live Sessions')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-4xl mx-auto">
        <x-back-link route="enrollments.show" :routeParams="[$course]" label="Back to Course" class="mb-4" variant="od" />

        <x-page-header title="Live Sessions" :subtitle="$course->title" variant="od" />

        <!-- Live Now Banner -->
        @php $liveSession = $sessions->firstWhere('status','live'); @endphp
        @if($liveSession)
            <div class="od-card mb-6" style="background: color-mix(in oklch, var(--od-danger) 5%, transparent); border-color: color-mix(in oklch, var(--od-danger) 20%, transparent);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background: var(--od-danger);"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3" style="background: var(--od-danger);"></span>
                        </span>
                        <div>
                            <h3 class="font-bold flex items-center gap-2" style="color: var(--od-danger);">LIVE NOW</h3>
                            <p class="text-sm" style="color: var(--od-danger); opacity: 0.8;">{{ $liveSession->description ?: 'Live class session' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('student.live-sessions.join', $liveSession) }}" target="_blank" class="od-btn od-btn-primary" style="background: var(--od-danger); border-color: var(--od-danger);">
                        <i class="fas fa-video"></i> Join Now
                    </a>
                </div>
            </div>
        @endif

        <!-- Sessions List -->
        <div class="space-y-4">
            @forelse($sessions as $session)
                <div class="od-card">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                @if($session->isLive())
                                    <span class="od-badge od-badge-danger">Live</span>
                                @elseif($session->isUpcoming())
                                    <span class="od-badge od-badge-info">Upcoming</span>
                                @else
                                    <span class="od-badge od-badge-success">Completed</span>
                                @endif
                                <span class="od-meta">{{ $session->scheduled_start_time->format('M j, Y g:i A') }}</span>
                            </div>
                            <p class="text-sm leading-relaxed" style="color: var(--od-fg);">{{ $session->description ?: 'Live class session' }}</p>
                            <div class="flex items-center gap-4 mt-2 text-xs" style="color: var(--od-muted);">
                                <span class="flex items-center gap-1"><i class="fas fa-clock"></i>{{ $session->scheduled_start_time->diffInMinutes($session->scheduled_end_time) }} min</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($session->isLive())
                                <a href="{{ route('student.live-sessions.join', $session) }}" target="_blank" class="od-btn od-btn-danger od-btn-sm">
                                    <i class="fas fa-video"></i> Join
                                </a>
                            @elseif($session->isUpcoming())
                                <span class="od-btn od-btn-ghost od-btn-sm" style="cursor: not-allowed;">
                                    Starts {{ $session->scheduled_start_time->diffForHumans() }}
                                </span>
                            @else
                                @if($session->recording_url)
                                    <a href="{{ $session->recording_url }}" target="_blank" class="od-btn od-btn-navy od-btn-sm">
                                        <i class="fas fa-play"></i> Recording
                                    </a>
                                @else
                                    <span class="od-btn od-btn-ghost od-btn-sm" style="cursor: not-allowed;">Ended</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="od-card">
                    <x-empty-state icon="fa-video" title="No Live Sessions" description="Your instructor hasn't scheduled any live sessions yet." variant="od" />
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
