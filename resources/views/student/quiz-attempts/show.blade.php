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

        <!-- Question Review -->
        <div class="space-y-4">
            @foreach($attempt->answers as $index => $answer)
                @php
                    $question = $answer->question;
                    $isCorrect = $answer->is_correct;
                @endphp
                <div class="od-card overflow-hidden" style="border-left: 4px solid {{ $isCorrect ? 'var(--od-green)' : 'var(--od-danger)' }};">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold od-num"
                             style="background: {{ $isCorrect ? 'var(--od-green-soft)' : 'color-mix(in oklch, var(--od-danger) 10%, transparent)' }}; color: {{ $isCorrect ? 'var(--od-green)' : 'var(--od-danger)' }};">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm leading-relaxed" style="color: var(--od-fg);">{{ $question->question_text }}</p>
                            <p class="od-meta mt-1">{{ $question->points }} point{{ $question->points !== 1 ? 's' : '' }}</p>

                            <div class="mt-3 space-y-2">
                                @if($question->question_type === 'Multiple Choice')
                                    @foreach($question->options as $option)
                                        @php
                                            $isSelected = $answer->selected_option_id == $option->id;
                                            $optClass = '';
                                            $optStyle = '';
                                            if ($option->is_correct) {
                                                $optStyle = 'background: var(--od-green-soft); border: 1px solid color-mix(in oklch, var(--od-green) 20%, transparent); color: var(--od-green);';
                                            } elseif ($isSelected && !$option->is_correct) {
                                                $optStyle = 'background: color-mix(in oklch, var(--od-danger) 8%, transparent); border: 1px solid color-mix(in oklch, var(--od-danger) 20%, transparent); color: var(--od-danger);';
                                            } else {
                                                $optStyle = 'background: var(--od-fg-soft); color: var(--od-muted);';
                                            }
                                        @endphp
                                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm" style="{{ $optStyle }}">
                                            <i class="fas {{ $option->is_correct ? 'fa-check-circle' : ($isSelected && !$option->is_correct ? 'fa-times-circle' : 'fa-circle') }}" style="opacity: 0.6;"></i>
                                            <span>{{ $option->option_text }}</span>
                                            @if($option->is_correct)
                                                <span class="ml-auto text-xs font-semibold">Correct</span>
                                            @endif
                                            @if($isSelected && !$option->is_correct)
                                                <span class="ml-auto text-xs font-semibold">Your answer</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @elseif($question->question_type === 'True/False')
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm" style="background: var(--od-fg-soft); color: var(--od-muted);">
                                        <span style="color: var(--od-fg);">Your answer:</span>
                                        <span class="font-semibold" style="color: {{ $isCorrect ? 'var(--od-green)' : 'var(--od-danger)' }};">{{ $answer->answer_text ?? 'Not answered' }}</span>
                                        @if(!$isCorrect)
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
                                @if($isCorrect)
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
