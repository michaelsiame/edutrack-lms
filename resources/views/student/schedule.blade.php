@extends('layouts.dashboard')

@section('title','My Schedule - Edutrack LMS')
@section('page_title','My Schedule')

@section('content')
<div class="max-w-5xl mx-auto">
    <x-page-header title="Weekly Schedule" :subtitle="$weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y')" />

    <x-card variant="default" class="overflow-hidden" :padding="false">
        <div class="grid grid-cols-1 md:grid-cols-7 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-700">
            @foreach($days as $day)
                @php
                    $isToday = now()->format('l') === $day;
                @endphp
                <div class="p-4 min-h-[120px] md:min-h-[200px] {{ $isToday ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1 text-center {{ $isToday ? 'text-primary-600 dark:text-primary-400' : '' }}">
                        {{ $day }}
                    </h3>
                    <p class="text-center text-lg font-bold text-gray-900 dark:text-white mb-3 {{ $isToday ? 'text-primary-600 dark:text-primary-400' : '' }}">
                        {{ $weekStart->copy()->addDays($loop->index)->format('j') }}
                    </p>

                    @if(empty($schedule[$day]))
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-4">No activities</p>
                    @else
                        <div class="space-y-2">
                            @foreach($schedule[$day] as $item)
                                <div class="p-2.5 rounded-xl text-xs cursor-default transition-colors
                                    {{ $item['type'] === 'live_session' ? 'bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 hover:bg-primary-100 dark:hover:bg-primary-900/30' : 'bg-success-50 dark:bg-success-900/20 border border-success-100 dark:border-success-800 hover:bg-success-100 dark:hover:bg-success-900/30' }}">
                                    <div class="flex items-center gap-1.5 font-semibold {{ $item['type'] === 'live_session' ? 'text-primary-700 dark:text-primary-300' : 'text-success-700 dark:text-success-300' }}">
                                        @if($item['type'] === 'live_session')
                                            <i class="fas fa-video w-3.5 text-center" aria-hidden="true"></i>
                                        @else
                                            <i class="fas fa-tasks w-3.5 text-center" aria-hidden="true"></i>
                                        @endif
                                        <span class="truncate">{{ $item['title'] }}</span>
                                    </div>
                                    <div class="text-gray-500 dark:text-gray-400 mt-0.5 ml-5">{{ $item['time'] }}</div>
                                    @if($item['url'])
                                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-block mt-1 ml-5 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium transition-colors">
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
    </x-card>

    <!-- Legend -->
    <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-primary-50 border border-primary-100 dark:bg-primary-900/20 dark:border-primary-800"></span>
            Live Session
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-success-50 border border-success-100 dark:bg-success-900/20 dark:border-success-800"></span>
            Assignment Due
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-primary-50/50 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-800/50"></span>
            Today
        </div>
    </div>
</div>
@endsection
