@extends('layouts.dashboard')

@section('title','Quiz Result - Edutrack LMS')
@section('page_title','Quiz Result')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-3xl mx-auto">
        <x-back-link route="enrollments.show" :routeParams="[$quiz->course]" label="Back to Course" class="mb-4" variant="od" />

        <!-- Result Hero -->
        <div class="od-card mb-6 text-center py-10 md:py-12">
            @if($attempt->isPassed())
                <div class="w-20 h-20 mx-auto mb-5 rounded-full flex items-center justify-center" style="background: var(--od-green-soft);">
                    <i class="fas fa-trophy text-3xl" style="color: var(--od-green);"></i>
                </div>
                <h1 class="od-h1 mb-2" style="color: var(--od-green);">Congratulations!</h1>
                <p class="od-lead" style="margin: 0 auto;">You passed the quiz with flying colors.</p>
            @else
                <div class="w-20 h-20 mx-auto mb-5 rounded-full flex items-center justify-center" style="background: color-mix(in oklch, var(--od-danger) 8%, transparent);">
                    <i class="fas fa-times-circle text-3xl" style="color: var(--od-danger);"></i>
                </div>
                <h1 class="od-h1 mb-2" style="color: var(--od-danger);">Quiz Not Passed</h1>
                <p class="od-lead" style="margin: 0 auto;">You didn't meet the passing score. You can retake the quiz.</p>
            @endif
        </div>

        <!-- Score Breakdown -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="od-card text-center py-5">
                <p class="od-meta mb-1">Your Score</p>
                <p class="text-3xl font-bold od-num" style="color: {{ $attempt->passed ? 'var(--od-green)' : 'var(--od-danger)' }};">
                    {{ $attempt->score }}%
                </p>
            </div>
            <div class="od-card text-center py-5">
                <p class="od-meta mb-1">Passing Score</p>
                <p class="text-3xl font-bold od-num" style="color: var(--od-fg);">{{ $quiz->passing_score ?? 60 }}%</p>
            </div>
            <div class="od-card text-center py-5">
                <p class="od-meta mb-1">Correct</p>
                <p class="text-3xl font-bold od-num" style="color: var(--od-fg);">{{ $correctCount ?? 0 }}/{{ $totalQuestions ?? 0 }}</p>
            </div>
        </div>

        <!-- Question Review -->
        <div class="space-y-4 mb-10">
            <h2 class="od-h3 flex items-center gap-2">
                <i class="fas fa-search" style="color: var(--od-navy);"></i> Question Review
            </h2>

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

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('enrollments.show', $quiz->course) }}" class="od-btn od-btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Course
            </a>
            @if(!$attempt->isPassed())
                <a href="{{ route('student.quizzes.take', $quiz) }}" class="od-btn od-btn-primary">
                    <i class="fas fa-redo"></i> Retake Quiz
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
