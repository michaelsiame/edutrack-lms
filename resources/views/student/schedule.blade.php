@extends('layouts.dashboard')

@section('title','My Schedule - Edutrack LMS')
@section('page_title','My Schedule')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <x-page-header title="Weekly Schedule" :subtitle="$weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y')" variant="od" />

    <div class="od-card overflow-hidden" style="padding: 0;">
        <div class="grid grid-cols-1 md:grid-cols-7" style="border-bottom: 1px solid var(--od-border);">
            @foreach($days as $day)
                @php
                    $isToday = now()->format('l') === $day;
                @endphp
                <div class="p-4 min-h-[120px] md:min-h-[200px] {{ !$loop->last ? 'md:border-r' : '' }}" style="{{ $isToday ? 'background: var(--od-navy-soft);' : '' }} {{ !$loop->last ? 'border-color: var(--od-border);' : '' }}">
                    <h3 class="text-xs font-bold uppercase tracking-wider mb-1 text-center {{ $isToday ? '' : '' }}" style="{{ $isToday ? 'color: var(--od-navy);' : 'color: var(--od-muted);' }}">
                        {{ $day }}
                    </h3>
                    <p class="text-center text-lg font-bold mb-3 od-num {{ $isToday ? '' : '' }}" style="{{ $isToday ? 'color: var(--od-navy);' : 'color: var(--od-fg);' }}">
                        {{ $weekStart->copy()->addDays($loop->index)->format('j') }}
                    </p>

                    @if(empty($schedule[$day]))
                        <p class="text-xs text-center py-4" style="color: var(--od-muted);">No activities</p>
                    @else
                        <div class="space-y-2">
                            @foreach($schedule[$day] as $item)
                                <div class="p-2.5 rounded-xl text-xs cursor-default transition-colors"
                                    style="{{ $item['type'] === 'live_session' ? 'background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 15%, transparent);' : 'background: var(--od-green-soft); border: 1px solid color-mix(in oklch, var(--od-green) 15%, transparent);' }}">
                                    <div class="flex items-center gap-1.5 font-semibold truncate"
                                        style="{{ $item['type'] === 'live_session' ? 'color: var(--od-navy);' : 'color: var(--od-green);' }}">
                                        @if($item['type'] === 'live_session')
                                            <i class="fas fa-video w-3.5 text-center" aria-hidden="true"></i>
                                        @else
                                            <i class="fas fa-tasks w-3.5 text-center" aria-hidden="true"></i>
                                        @endif
                                        <span class="truncate">{{ $item['title'] }}</span>
                                    </div>
                                    <div class="mt-0.5 ml-5" style="color: var(--od-muted);">{{ $item['time'] }}</div>
                                    @if($item['url'])
                                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-block mt-1 ml-5 font-medium transition-colors" style="color: var(--od-navy);">
                                            Join Session <i class="fas fa-external-link-alt text-[10px]" aria-hidden="true"></i>
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
