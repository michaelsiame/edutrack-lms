@extends('layouts.dashboard')

@section('title','My Achievements - Edutrack LMS')
@section('page_title','My Achievements')

@section('content')
<div class="max-w-5xl mx-auto">
    <x-page-header title="Achievements & Badges" subtitle="Track your accomplishments and earned recognition" />

    @if($achievements->isEmpty())
        <x-card variant="elevated">
            <x-empty-state icon="fa-trophy" title="No Achievements Yet" description="Complete courses, pass quizzes, and participate actively to earn badges and achievements." />
        </x-card>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($achievements as $achievement)
                <x-card variant="interactive" class="text-center py-8 group relative overflow-hidden">
                    <!-- Glow effect on hover -->
                    <div class="absolute inset-0 bg-secondary-500/0 group-hover:bg-secondary-500/5 dark:group-hover:bg-secondary-500/10 transition-colors duration-500"></div>

                    <div class="relative">
                        <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-secondary-50 dark:bg-secondary-900/20 flex items-center justify-center border-4 border-secondary-100 dark:border-secondary-900/30 group-hover:scale-110 group-hover:border-secondary-300 dark:group-hover:border-secondary-700 transition-all duration-300">
                            @if($achievement->badge?->icon)
                                <i class="fas {{ $achievement->badge->icon }} text-3xl text-secondary-500 dark:text-secondary-400"></i>
                            @else
                                <i class="fas fa-medal text-3xl text-secondary-500 dark:text-secondary-400"></i>
                            @endif
                        </div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-1 text-lg">{{ $achievement->badge?->name ?? 'Achievement' }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 px-4">{{ $achievement->badge?->description ?? '' }}</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            <i class="far fa-calendar-alt mr-1.5"></i>Earned {{ $achievement->earned_date?->diffForHumans() ?? 'recently' }}
                        </span>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</div>
@endsection
