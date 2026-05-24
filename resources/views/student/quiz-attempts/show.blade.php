@extends('layouts.dashboard')

@section('title','Attempt #' . $attempt->attempt_number . ' - ' . $attempt->quiz->title)
@section('page_title','Quiz Review')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-back-link route="student.quizzes.attempts" :routeParams="[$attempt->quiz]" :label="'Back to Attempts'" class="mb-4" />

    <!-- Score Header -->
    <x-card variant="elevated" class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $attempt->quiz->title }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Attempt #{{ $attempt->attempt_number }} &middot; {{ $attempt->submitted_at?->format('M d, Y g:i A') }}
                </p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-3xl font-bold {{ $attempt->isPassed() ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ $attempt->score }}%
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Pass: {{ $attempt->quiz->passing_score ?? 60 }}%
                    </div>
                </div>
                <x-status-badge :status="$attempt->isPassed() ? 'Passed' : 'Failed'" size="md" />
            </div>
        </div>
    </x-card>

    <!-- Question Review -->
    <div class="space-y-4">
        @foreach($attempt->answers as $index => $answer)
            @php
                $question = $answer->question;
                $isCorrect = $answer->is_correct;
            @endphp
            <x-card variant="default" class="overflow-hidden {{ $isCorrect ? 'border-l-4 border-l-success-500' : 'border-l-4 border-l-danger-500' }}">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $isCorrect ? 'bg-success-100 text-success-600 dark:bg-success-900/30 dark:text-success-400' : 'bg-danger-100 text-danger-600 dark:bg-danger-900/30 dark:text-danger-400' }} flex items-center justify-center text-sm font-bold">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white text-sm leading-relaxed">{{ $question->question_text }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $question->points }} point{{ $question->points !== 1 ? 's' : '' }}</p>

                        <div class="mt-3 space-y-2">
                            @if($question->question_type === 'Multiple Choice')
                                @foreach($question->options as $option)
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm
                                        {{ $option->is_correct ? 'bg-success-50 dark:bg-success-900/20 text-success-700 dark:text-success-300 border border-success-200 dark:border-success-800' : '' }}
                                        {{ $answer->selected_option_id == $option->id && !$option->is_correct ? 'bg-danger-50 dark:bg-danger-900/20 text-danger-700 dark:text-danger-300 border border-danger-200 dark:border-danger-800' : '' }}
                                        {{ !$option->is_correct && $answer->selected_option_id != $option->id ? 'bg-gray-50 dark:bg-gray-700/30 text-gray-600 dark:text-gray-400' : '' }}">
                                        <i class="fas {{ $option->is_correct ? 'fa-check-circle text-success-500' : ($answer->selected_option_id == $option->id && !$option->is_correct ? 'fa-times-circle text-danger-500' : 'fa-circle text-gray-300 dark:text-gray-600') }}"></i>
                                        <span>{{ $option->option_text }}</span>
                                        @if($option->is_correct)
                                            <span class="ml-auto text-xs font-semibold text-success-600 dark:text-success-400">Correct</span>
                                        @endif
                                        @if($answer->selected_option_id == $option->id && !$option->is_correct)
                                            <span class="ml-auto text-xs font-semibold text-danger-600 dark:text-danger-400">Your answer</span>
                                        @endif
                                    </div>
                                @endforeach
                            @elseif($question->question_type === 'True/False')
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-gray-700/30">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Your answer:</span>
                                    <span class="{{ $isCorrect ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }} font-semibold">{{ $answer->answer_text ?? 'Not answered' }}</span>
                                    @if(!$isCorrect)
                                        <span class="ml-auto text-success-600 dark:text-success-400 font-medium">Correct: {{ $question->options->firstWhere('is_correct', true)?->option_text ?? 'N/A' }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-gray-700/30">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Your answer:</span>
                                    <p class="mt-1 text-gray-700 dark:text-gray-300">{{ $answer->answer_text ?? 'Not answered' }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="mt-2 text-xs">
                            @if($isCorrect)
                                <span class="text-success-600 dark:text-success-400 font-semibold"><i class="fas fa-check mr-1"></i>Correct (+{{ $answer->points_earned }} pts)</span>
                            @else
                                <span class="text-danger-600 dark:text-danger-400 font-semibold"><i class="fas fa-times mr-1"></i>Incorrect (0 pts)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </x-card>
        @endforeach
    </div>

    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
        <x-button :href="route('student.quizzes.attempts', $attempt->quiz)" variant="secondary" icon="fa-arrow-left">
            Back to Attempts
        </x-button>
        @php
            $attemptsCount = \App\Models\QuizAttempt::where('quiz_id', $attempt->quiz_id)->where('student_id', auth()->user()->student?->id)->whereIn('status', ['Graded', 'Submitted'])->count();
            $canRetake = !$attempt->quiz->max_attempts || $attemptsCount < $attempt->quiz->max_attempts;
        @endphp
        @if($canRetake)
            <x-button :href="route('student.quizzes.take', $attempt->quiz)" variant="primary" icon="fa-redo">
                Retake Quiz
            </x-button>
        @endif
    </div>
</div>
@endsection
