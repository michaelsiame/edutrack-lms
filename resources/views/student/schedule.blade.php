@extends('layouts.dashboard')

@section('title', 'My Schedule - Edutrack LMS')
@section('page_title', 'My Schedule')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Weekly Schedule</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-7 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-700">
            @foreach($days as $day)
                <div class="p-4 min-h-[200px]">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 text-center">{{ $day }}</h3>

                    @if(empty($schedule[$day]))
                        <p class="text-xs text-gray-400 text-center py-4">No activities</p>
                    @else
                        <div class="space-y-2">
                            @foreach($schedule[$day] as $item)
                                <div class="p-2 rounded-lg text-xs
                                    {{ $item['type'] === 'live_session' ? 'bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800' : 'bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800' }}">
                                    <div class="font-medium {{ $item['type'] === 'live_session' ? 'text-primary-700 dark:text-primary-300' : 'text-green-700 dark:text-green-300' }}">
                                        {{ $item['title'] }}
                                    </div>
                                    <div class="text-gray-500 dark:text-gray-400 mt-0.5">{{ $item['time'] }}</div>
                                    @if($item['url'])
                                        <a href="{{ $item['url'] }}" target="_blank" class="inline-block mt-1 text-primary-600 hover:underline">
                                            Join Session <i class="fas fa-external-link-alt text-[10px]"></i>
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
</div>
@endsection
