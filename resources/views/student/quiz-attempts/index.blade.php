@extends('layouts.dashboard')

@section('title','Quiz Attempts - ' . $quiz->title)
@section('page_title','Quiz Attempts')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-back-link route="student.quizzes.index" label="Back to Quizzes" class="mb-4" />

    <!-- Quiz Info Card -->
    <x-card variant="elevated" class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $quiz->title }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $quiz->course->title }} &middot; Pass: {{ $quiz->passing_score ?? 60 }}% &middot; {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : 'No time limit' }}
                </p>
            </div>
            @if($attempts->isNotEmpty())
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attempts->max('score') ?? 0 }}%</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Best Score</div>
                </div>
            @endif
        </div>
    </x-card>

    <!-- Attempts List -->
    <div class="space-y-4">
        @forelse($attempts as $attempt)
            <x-card variant="interactive" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl {{ $attempt->isPassed() ? 'bg-success-50 text-success-600 dark:bg-success-900/20 dark:text-success-400' : 'bg-danger-50 text-danger-600 dark:bg-danger-900/20 dark:text-danger-400' }} flex items-center justify-center text-lg font-bold shrink-0">
                        #{{ $attempt->attempt_number }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $attempt->score ?? 0 }}%</span>
                            <x-status-badge :status="$attempt->isPassed() ? 'Passed' : 'Failed'" size="sm" />
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $attempt->submitted_at?->format('M d, Y g:i A') ?? 'N/A' }}
                            @if($attempt->time_spent_minutes)
                                &middot; {{ $attempt->time_spent_minutes }} min
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <x-button :href="route('student.quizzes.attempt', $attempt)" variant="outline" icon="fa-eye" size="sm">
                        Review
                    </x-button>
                </div>
            </x-card>
        @empty
            <x-card variant="default">
                <x-empty-state icon="fa-history" title="No Attempts Yet" description="You haven't taken this quiz. Start your first attempt now." />
            </x-card>
        @endforelse
    </div>

    @php
        $canRetake = !$quiz->max_attempts || $attempts->count() < $quiz->max_attempts;
    @endphp
    @if($canRetake)
        <div class="mt-8 text-center">
            <x-button :href="route('student.quizzes.take', $quiz)" variant="primary" size="lg" icon="fa-redo">
                Retake Quiz
            </x-button>
        </div>
    @endif
</div>
@endsection
