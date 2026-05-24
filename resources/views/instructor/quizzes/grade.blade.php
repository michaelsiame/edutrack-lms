@extends('layouts.dashboard')

@section('title', 'Grade Attempt - ' . $quiz->title)
@section('page_title', 'Grade Submission')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="mb-6">
        <a href="{{ route('instructor.quizzes.attempts', $quiz) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <i class="fas fa-arrow-left mr-1"></i>Back to Submissions
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">Grade Submission</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $attempt->student->user->full_name ?? 'Unknown' }} &bull; Attempt #{{ $attempt->attempt_number }} &bull; {{ $attempt->submitted_at?->format('M d, Y H:i') }}
        </p>
    </div>

    @if(session('success'))
    <div class="p-4 bg-success-50 border border-success-200 rounded-lg text-success-700">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('instructor.quizzes.attempts.grade.save', [$quiz, $attempt]) }}" method="POST" class="space-y-6">
        @csrf

        @foreach($attempt->answers as $answer)
        @php $question = $answer->question; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 px-2 py-0.5 rounded">Q{{ $loop->iteration }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $question->question_type }} &bull; {{ $question->points }} pts</span>
                    </div>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $question->question_text }}</p>
                </div>
                <div class="text-right shrink-0">
                    <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Points Earned</label>
                    <input type="number" name="grades[{{ $answer->id }}]" value="{{ old('grades.' . $answer->id, $answer->points_earned) }}" min="0" max="{{ $question->points }}" step="0.01"
                        class="w-24 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white text-center font-semibold">
                    <span class="text-xs text-gray-400">/ {{ $question->points }}</span>
                </div>
            </div>

            {{-- Show student's answer --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-3">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Student's Answer</p>
                @if($question->question_type === 'Multiple Choice' || $question->question_type === 'True/False')
                    <p class="text-sm text-gray-800 dark:text-gray-200">
                        @if($answer->selected_option_id && $question->options)
                            {{ $question->options->firstWhere('option_id', $answer->selected_option_id)?->option_text ?? 'N/A' }}
                        @else
                            {{ $answer->answer_text ?? 'No answer' }}
                        @endif
                        @if($answer->is_correct)
                            <span class="ml-2 text-success-600"><i class="fas fa-check-circle"></i> Correct</span>
                        @else
                            <span class="ml-2 text-danger-600"><i class="fas fa-times-circle"></i> Incorrect</span>
                        @endif
                    </p>
                @else
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $answer->answer_text ?: '(No answer provided)' }}</p>
                @endif
            </div>

            {{-- Show correct answer / rubric for reference --}}
            @if($question->correct_answer)
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                <p class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">
                    {{ $question->question_type === 'Essay' ? 'Grading Rubric / Sample Answer' : 'Correct Answer' }}
                </p>
                <p class="text-sm text-blue-800 dark:text-blue-300 whitespace-pre-wrap">{{ $question->correct_answer }}</p>
            </div>
            @endif

            @if($question->explanation)
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">
                <i class="fas fa-info-circle mr-1"></i>{{ $question->explanation }}
            </div>
            @endif
        </div>
        @endforeach

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="flex gap-2">
                @if($prevAttempt)
                    <a href="{{ route('instructor.quizzes.attempts.grade', [$quiz, $prevAttempt]) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Previous Submission
                    </a>
                @endif
                @if($nextAttempt)
                    <a href="{{ route('instructor.quizzes.attempts.grade', [$quiz, $nextAttempt]) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                        Next Submission<i class="fas fa-arrow-right ml-2"></i>
                    </a>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ route('instructor.quizzes.attempts', $quiz) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors text-sm font-medium">
                    <i class="fas fa-save mr-2"></i>Save Grades
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
