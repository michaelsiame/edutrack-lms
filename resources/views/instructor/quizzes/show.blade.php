@extends('layouts.dashboard')

@section('title', $quiz->title . ' - Quiz')
@section('page_title', 'Quiz Details')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $quiz->title }}</h1>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $quiz->is_published ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $quiz->is_published ? 'Published' : 'Draft' }}
                    </span>
                </div>
                <p class="text-gray-600 dark:text-gray-400">{{ $quiz->course->title }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('instructor.quizzes.attempts', $quiz) }}" class="inline-flex items-center px-3 py-2 bg-secondary-600 hover:bg-secondary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-clipboard-check mr-2"></i>Grade Submissions
                </a>
                <a href="{{ route('instructor.quizzes.edit', $quiz) }}" class="inline-flex items-center px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-pen mr-2"></i>Edit
                </a>
                <form action="{{ route('instructor.quizzes.destroy', $quiz) }}" method="POST" class="inline" onsubmit="return confirm('Delete this quiz?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-danger-600 hover:bg-danger-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Questions</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $quiz->questions_count ?? 0 }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Passing Score</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $quiz->passing_score }}%</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Time Limit</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $quiz->time_limit_minutes ?? 'None' }} {{ $quiz->time_limit_minutes ? 'min' : '' }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Max Attempts</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $quiz->max_attempts ?? 1 }}</p>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if($quiz->description)
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Description</h3>
        <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $quiz->description }}</p>
    </div>
    @endif

    {{-- Questions --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-list-ol text-primary-500 mr-2"></i>Questions
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $quiz->questions->count() }} total</span>
                <a href="{{ route('instructor.quizzes.questions.create', $quiz) }}" class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1.5"></i>Add Question
                </a>
            </div>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($quiz->questions as $index => $question)
            <div class="p-6">
                <div class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-7 h-7 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-full flex items-center justify-center text-sm font-bold">{{ $index + 1 }}</span>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-gray-900 dark:text-white font-medium">{{ $question->question_text }}</p>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $question->points }} pts</span>
                                <a href="{{ route('instructor.quizzes.questions.edit', [$quiz, $question]) }}" class="p-1.5 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors" title="Edit">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                <form action="{{ route('instructor.quizzes.questions.destroy', [$quiz, $question]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this question?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg transition-colors" title="Delete">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-3 space-y-2">
                            @foreach($question->options as $option)
                            <div class="flex items-center gap-2 text-sm {{ $option->is_correct ? 'text-success-600 dark:text-success-400 font-semibold' : 'text-gray-600 dark:text-gray-400' }}">
                                <i class="fas {{ $option->is_correct ? 'fa-check-circle' : 'fa-circle' }} text-xs"></i>
                                {{ $option->option_text }}
                            </div>
                            @endforeach
                        </div>
                        @if($question->explanation)
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic"><i class="fas fa-info-circle mr-1"></i>{{ $question->explanation }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <i class="fas fa-question-circle text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                <p class="text-sm">No questions added yet.</p>
                <a href="{{ route('instructor.quizzes.questions.create', $quiz) }}" class="mt-3 inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1.5"></i>Add Your First Question
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
