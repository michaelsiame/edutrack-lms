@extends('layouts.dashboard')

@section('title','Attempt #' . $attempt->attempt_number . ' - ' . $attempt->quiz->title)
@section('page_title','Quiz Review')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-4xl mx-auto">
        <x-back-link route="student.quizzes.attempts" :routeParams="[$attempt->quiz]" :label="'Back to Attempts'" class="mb-4" variant="od" />

        <!-- Score Header -->
        <div class="od-card mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="od-eyebrow" style="margin-bottom: 4px;">{{ $attempt->quiz->title }}</p>
                    <h1 class="od-h2">Attempt #{{ $attempt->attempt_number }}</h1>
                    <p class="od-meta mt-1">{{ $attempt->submitted_at?->format('M d, Y g:i A') }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-3xl font-bold" style="color: {{ $attempt->isPassed() ? 'var(--od-green)' : 'var(--od-danger)' }}; font-family: var(--font-display);">
                            {{ $attempt->score }}%
                        </div>
                        <div class="od-meta">Pass: {{ $attempt->quiz->passing_score ?? 60 }}%</div>
                    </div>
                    @if($attempt->isPassed())
                        <span class="od-badge od-badge-success">Passed</span>
                    @else
                        <span class="od-badge od-badge-danger">Failed</span>
                    @endif
                </div>
            </div>
        </div>

        @unless($revealAnswers ?? true)
            <div class="od-card mb-6" style="border-left: 4px solid var(--od-accent); background: color-mix(in oklch, var(--od-accent) 6%, transparent);">
                <div class="flex items-start gap-3">
                    <i class="fas fa-lock mt-0.5" style="color: var(--od-accent);"></i>
                    <div>
                        <p class="font-medium text-sm" style="color: var(--od-fg);">Correct answers are hidden for now</p>
                        <p class="od-meta mt-1">
                            You can review your score and which questions you missed, but the correct answers stay hidden so your retries are fair.
                            @if(($attemptsLeft ?? 0) > 0)
                                You have <strong>{{ $attemptsLeft }}</strong> attempt{{ $attemptsLeft !== 1 ? 's' : '' }} left. Answers unlock once you pass or use them all.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endunless

        <!-- Question Review -->
        <div class="space-y-4">
            @foreach($attempt->answers as $index => $answer)
                @php
                    $question = $answer->question;
                    $reveal = $revealAnswers ?? true;
                    // While answers are hidden, don't colour by correctness either.
                    $isCorrect = $reveal ? $answer->is_correct : null;
                @endphp
                @php
                    // border / badge: green or red when revealing, neutral navy when hidden
                    $edge = !$reveal ? 'var(--od-navy)' : ($isCorrect ? 'var(--od-green)' : 'var(--od-danger)');
                    $badgeBg = !$reveal ? 'var(--od-navy-soft)' : ($isCorrect ? 'var(--od-green-soft)' : 'color-mix(in oklch, var(--od-danger) 10%, transparent)');
                @endphp
                <div class="od-card overflow-hidden" style="border-left: 4px solid {{ $edge }};">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold od-num"
                             style="background: {{ $badgeBg }}; color: {{ $edge }};">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm leading-relaxed" style="color: var(--od-fg);">{{ $question->question_text }}</p>
                            <p class="od-meta mt-1">{{ $question->points }} point{{ $question->points !== 1 ? 's' : '' }}</p>

                            <div class="mt-3 space-y-2">
                                @if($question->question_type === 'Multiple Choice')
                                    @foreach($question->options as $option)
                                        @php
                                            $isSelected = $answer->selected_option_id == $option->option_id;
                                            if ($reveal && $option->is_correct) {
                                                $optStyle = 'background: var(--od-green-soft); border: 1px solid color-mix(in oklch, var(--od-green) 20%, transparent); color: var(--od-green);';
                                            } elseif ($reveal && $isSelected && !$option->is_correct) {
                                                $optStyle = 'background: color-mix(in oklch, var(--od-danger) 8%, transparent); border: 1px solid color-mix(in oklch, var(--od-danger) 20%, transparent); color: var(--od-danger);';
                                            } elseif (!$reveal && $isSelected) {
                                                $optStyle = 'background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 20%, transparent); color: var(--od-navy);';
                                            } else {
                                                $optStyle = 'background: var(--od-fg-soft); color: var(--od-muted);';
                                            }
                                            $optIcon = ($reveal && $option->is_correct) ? 'fa-check-circle'
                                                : ($reveal && $isSelected && !$option->is_correct ? 'fa-times-circle'
                                                : ($isSelected ? 'fa-dot-circle' : 'fa-circle'));
                                        @endphp
                                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm" style="{{ $optStyle }}">
                                            <i class="fas {{ $optIcon }}" style="opacity: 0.6;"></i>
                                            <span>{{ $option->option_text }}</span>
                                            @if($reveal && $option->is_correct)
                                                <span class="ml-auto text-xs font-semibold">Correct</span>
                                            @endif
                                            @if($isSelected && !($reveal && $option->is_correct))
                                                <span class="ml-auto text-xs font-semibold">Your answer</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @elseif($question->question_type === 'True/False')
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm" style="background: var(--od-fg-soft); color: var(--od-muted);">
                                        <span style="color: var(--od-fg);">Your answer:</span>
                                        <span class="font-semibold" style="color: {{ !$reveal ? 'var(--od-navy)' : ($isCorrect ? 'var(--od-green)' : 'var(--od-danger)') }};">{{ $answer->answer_text ?? 'Not answered' }}</span>
                                        @if($reveal && !$isCorrect)
                                            <span class="ml-auto" style="color: var(--od-green);">Correct: {{ $question->options->firstWhere('is_correct', true)?->option_text ?? 'N/A' }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="px-3 py-2 rounded-lg text-sm" style="background: var(--od-fg-soft); color: var(--od-muted);">
                                        <span style="color: var(--od-fg);">Your answer:</span>
                                        <p class="mt-1" style="color: var(--od-fg);">{{ $answer->answer_text ?? 'Not answered' }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-2 text-xs">
                                @if(!$reveal)
                                    <span style="color: var(--od-navy);" class="font-semibold"><i class="fas fa-lock mr-1"></i>Result hidden until answers unlock</span>
                                @elseif($isCorrect)
                                    <span style="color: var(--od-green);" class="font-semibold"><i class="fas fa-check mr-1"></i>Correct (+{{ $answer->points_earned }} pts)</span>
                                @else
                                    <span style="color: var(--od-danger);" class="font-semibold"><i class="fas fa-times mr-1"></i>Incorrect (0 pts)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('student.quizzes.attempts', $attempt->quiz) }}" class="od-btn od-btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Attempts
            </a>
            @php
                $attemptsCount = \App\Models\QuizAttempt::where('quiz_id', $attempt->quiz_id)->where('student_id', auth()->user()->student?->id)->whereIn('status', ['Graded', 'Submitted'])->count();
                $canRetake = !$attempt->quiz->max_attempts || $attemptsCount < $attempt->quiz->max_attempts;
            @endphp
            @if($canRetake)
                <a href="{{ route('student.quizzes.take', $attempt->quiz) }}" class="od-btn od-btn-primary">
                    <i class="fas fa-redo"></i> Retake Quiz
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
