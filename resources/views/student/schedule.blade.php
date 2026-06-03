@extends('layouts.dashboard')

@section('title','My Schedule - Edutrack LMS')
@section('page_title','My Schedule')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<style>
.od-schedule-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
}
@media (min-width: 768px) {
    .od-schedule-grid {
        grid-template-columns: repeat(7, 1fr);
    }
}
.od-schedule-day {
    padding: 16px;
    min-height: 120px;
}
@media (min-width: 768px) {
    .od-schedule-day {
        min-height: 220px;
        padding: 12px 10px;
    }
}
.od-schedule-day + .od-schedule-day {
    border-top: 1px solid var(--od-border);
}
@media (min-width: 768px) {
    .od-schedule-day + .od-schedule-day {
        border-top: 0;
        border-left: 1px solid var(--od-border);
    }
}
.od-event-card {
    padding: 8px 10px;
    border-radius: 10px;
    font-size: 11px;
    line-height: 1.35;
    word-break: break-word;
}
.od-event-card + .od-event-card {
    margin-top: 6px;
}
</style>
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <x-page-header title="Weekly Schedule" :subtitle="$weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y')" variant="od" />

    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="od-schedule-grid" style="border-bottom: 1px solid var(--od-border);">
            @foreach($days as $day)
                @php
                    $isToday = now()->format('l') === $day;
                @endphp
                <div class="od-schedule-day" style="{{ $isToday ? 'background: var(--od-navy-soft);' : '' }}">
                    <h3 class="text-xs font-bold uppercase tracking-wider mb-1 text-center" style="{{ $isToday ? 'color: var(--od-navy);' : 'color: var(--od-muted);' }}">
                        {{ $day }}
                    </h3>
                    <p class="text-center text-lg font-bold mb-3 od-num" style="{{ $isToday ? 'color: var(--od-navy);' : 'color: var(--od-fg);' }}">
                        {{ $weekStart->copy()->addDays($loop->index)->format('j') }}
                    </p>

                    @if(empty($schedule[$day]))
                        <p class="text-xs text-center py-4" style="color: var(--od-muted);">No activities</p>
                    @else
                        <div>
                            @foreach($schedule[$day] as $item)
                                <div class="od-event-card cursor-default transition-colors"
                                    style="{{ $item['type'] === 'live_session' ? 'background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 15%, transparent);' : 'background: var(--od-green-soft); border: 1px solid color-mix(in oklch, var(--od-green) 15%, transparent);' }}">
                                    <div class="flex items-start gap-1.5 font-semibold"
                                        style="{{ $item['type'] === 'live_session' ? 'color: var(--od-navy);' : 'color: var(--od-green);' }}">
                                        @if($item['type'] === 'live_session')
                                            <i class="fas fa-video w-3.5 text-center mt-0.5 flex-shrink-0" aria-hidden="true"></i>
                                        @else
                                            <i class="fas fa-tasks w-3.5 text-center mt-0.5 flex-shrink-0" aria-hidden="true"></i>
                                        @endif
                                        <span>{{ $item['title'] }}</span>
                                    </div>
                                    <div class="mt-0.5 pl-5" style="color: var(--od-muted);">{{ $item['time'] }}</div>
                                    @if($item['url'])
                                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-block mt-1 pl-5 font-medium transition-colors" style="color: var(--od-navy);">
                                            Join <i class="fas fa-external-link-alt text-[10px]" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs" style="color: var(--od-muted);">
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded" style="background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 15%, transparent);"></span>
            Live Session
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded" style="background: var(--od-green-soft); border: 1px solid color-mix(in oklch, var(--od-green) 15%, transparent);"></span>
            Assignment Due
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded" style="background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 15%, transparent); opacity: 0.5;"></span>
            Today
        </div>
    </div>
</div>
@endsection
