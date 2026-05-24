@extends('layouts.dashboard')

@section('title', $assignment->title . ' - Edutrack LMS')
@section('page_title', $assignment->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-back-link route="student.assignments.index" label="Back to Assignments" />

    <!-- Assignment Details -->
    <x-card variant="elevated">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $assignment->title }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $course->title }}</p>
            </div>
            <div class="flex flex-col items-start sm:items-end gap-1">
                @if($assignment->due_date)
                    <div class="flex items-center gap-1.5 text-sm {{ $assignment->due_date->isPast() && !$submission ? 'text-danger-600 dark:text-danger-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                        <i class="far fa-calendar-alt"></i>
                        Due {{ $assignment->due_date->format('M d, Y \a\t h:i A') }}
                    </div>
                @endif
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Max Points: <span class="font-semibold text-gray-900 dark:text-white">{{ $assignment->max_points }}</span>
                </div>
            </div>
        </div>

        @if($assignment->description)
            <div class="prose dark:prose-invert max-w-none text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                <p>{{ $assignment->description }}</p>
            </div>
        @endif

        @if($assignment->instructions)
            <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2 text-sm flex items-center gap-2">
                    <i class="fas fa-info-circle text-primary-500"></i>Instructions
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap leading-relaxed">{{ $assignment->instructions }}</p>
            </div>
        @endif
    </x-card>

    <!-- Previous Submission -->
    @if($submission)
        <x-card variant="default">
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-primary-500"></i>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Your Submission</h3>
                </div>
                <x-slot:headerAction>
                    <x-status-badge :status="$submission->status" size="sm" />
                </x-slot:headerAction>
            </x-slot:header>

            <div class="space-y-4">
                <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                    <i class="far fa-clock"></i>
                    Submitted {{ $submission->submitted_at->diffForHumans() }}
                </div>

                @if($submission->submission_text)
                    <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ $submission->submission_text }}</p>
                    </div>
                @endif

                @if($submission->file_url)
                    <div>
                        <x-button :href="$submission->file_url" variant="outline" icon="fa-file-download" size="sm" target="_blank">
                            Download Submission
                        </x-button>
                    </div>
                @endif

                @if($submission->status === 'Graded')
                    <div class="mt-4 pt-5 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-baseline gap-2 mb-3">
                            <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $submission->points_earned }}</span>
                            <span class="text-gray-500 dark:text-gray-400">/ {{ $assignment->max_points }} points</span>
                        </div>
                        @if($submission->feedback)
                            <div class="bg-primary-50 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-800 rounded-xl p-4">
                                <h4 class="font-semibold text-primary-900 dark:text-primary-300 mb-1 text-sm flex items-center gap-2">
                                    <i class="fas fa-comment-alt text-primary-500"></i>Instructor Feedback
                                </h4>
                                <p class="text-sm text-primary-800 dark:text-primary-400 leading-relaxed">{{ $submission->feedback }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </x-card>
    @endif

    <!-- Submission Form -->
    @if(!$submission || $submission->status !== 'Graded')
        <x-card variant="default">
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <i class="fas fa-paper-plane text-primary-500"></i>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">{{ $submission ? 'Resubmit Assignment' : 'Submit Assignment' }}</h3>
                </div>
            </x-slot:header>

            <form action="{{ route('student.assignments.submit', [$course, $assignment]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-5">
                    <label for="submission_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Your Answer</label>
                    <textarea name="submission_text" id="submission_text" rows="6"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm leading-relaxed shadow-sm"
                        placeholder="Type your answer here...">{{ old('submission_text') }}</textarea>
                    @error('submission_text')
                        <p class="mt-1.5 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="submission_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Attach File (optional)</label>
                    <div class="relative">
                        <input type="file" name="submission_file" id="submission_file"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm shadow-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-300">
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Max file size: 50MB</p>
                    @error('submission_file')
                        <p class="mt-1.5 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-button type="submit" variant="primary" icon="fa-paper-plane">
                    {{ $submission ? 'Resubmit Assignment' : 'Submit Assignment' }}
                </x-button>
            </form>
        </x-card>
    @endif
</div>
@endsection
