@extends('layouts.dashboard')

@section('title','Quiz Attempts - ' . $quiz->title)
@section('page_title','Quiz Attempts')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-4xl mx-auto">
        <x-back-link route="student.quizzes.index" label="Back to Quizzes" class="mb-4" variant="od" />

        <!-- Quiz Info -->
        <div class="od-card mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <p class="od-eyebrow" style="margin-bottom: 4px;">{{ $quiz->course->title }}</p>
                    <h1 class="od-h2">{{ $quiz->title }}</h1>
                    <p class="od-meta mt-1">Pass: {{ $quiz->passing_score ?? 60 }}% &bull; {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : 'No time limit' }}</p>
                </div>
                @if($attempts->isNotEmpty())
                    <div class="text-right">
                        <div class="od-stat-value od-num">{{ $attempts->max('score') ?? 0 }}%</div>
                        <div class="od-stat-label">Best Score</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Attempts -->
        <div class="space-y-4">
            @forelse($attempts as $attempt)
                <div class="od-card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-lg font-bold shrink-0 od-num"
                             style="background: {{ $attempt->isPassed() ? 'var(--od-green-soft)' : 'color-mix(in oklch, var(--od-danger) 10%, transparent)' }}; color: {{ $attempt->isPassed() ? 'var(--od-green)' : 'var(--od-danger)' }};">
                            #{{ $attempt->attempt_number }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold" style="color: var(--od-fg);">{{ $attempt->score ?? 0 }}%</span>
                                @if($attempt->isPassed())
                                    <span class="od-badge od-badge-success">Passed</span>
                                @else
                                    <span class="od-badge od-badge-danger">Failed</span>
                                @endif
                            </div>
                            <p class="od-meta">
                                {{ $attempt->submitted_at?->format('M d, Y g:i A') ?? 'N/A' }}
                                @if($attempt->time_spent_minutes)
                                    &bull; {{ $attempt->time_spent_minutes }} min
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('student.quizzes.attempt', $attempt) }}" class="od-btn od-btn-secondary od-btn-sm">
                        <i class="fas fa-eye"></i> Review
                    </a>
                </div>
            @empty
                <div class="od-card">
                    <x-empty-state icon="fa-history" title="No Attempts Yet" description="You haven't taken this quiz. Start your first attempt now." variant="od" />
                </div>
            @endforelse
        </div>

        @php
            $canRetake = !$quiz->max_attempts || $attempts->count() < $quiz->max_attempts;
        @endphp
        @if($canRetake)
            <div class="mt-8 text-center">
                <a href="{{ route('student.quizzes.take', $quiz) }}" class="od-btn od-btn-primary od-btn-lg">
                    <i class="fas fa-redo"></i> Retake Quiz
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
