@extends('layouts.dashboard')

@section('title', 'My Achievements')
@section('page_title', 'My Achievements')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Badges & Achievements</h2>
        <div class="text-sm text-gray-500">
            Total Points: <span class="font-semibold text-primary-600">{{ $achievements->sum(fn($a) => $a->badge?->points ?? 0) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($achievements as $achievement)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-900 flex items-center justify-center flex-shrink-0">
                        <i class="{{ $achievement->badge?->badge_icon_url ?? 'fas fa-medal' }} text-amber-600 dark:text-amber-400 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $achievement->badge?->badge_name ?? 'Badge' }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $achievement->badge?->description }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-xs bg-primary-100 text-primary-800 px-2 py-0.5 rounded">{{ $achievement->badge?->points ?? 0 }} pts</span>
                            <span class="text-xs text-gray-400">{{ $achievement->earned_date?->format('M d, Y') }}</span>
                        </div>
                        @if($achievement->course)
                            <p class="text-xs text-gray-400 mt-1"><i class="fas fa-book mr-1"></i>{{ $achievement->course->title }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-medal text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">No Achievements Yet</h3>
                <p class="text-gray-500 text-sm mt-1">Complete courses and participate to earn badges!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
