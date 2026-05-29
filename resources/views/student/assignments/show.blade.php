@extends('layouts.dashboard')

@section('title', $assignment->title . ' - Edutrack LMS')
@section('page_title', $assignment->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <x-back-link route="student.assignments.index" label="Back to Assignments" class="mb-4" variant="od" />

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Assignment Details -->
        <div class="od-card">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5">
                <div>
                    <p class="od-eyebrow" style="margin-bottom: 4px;">{{ $course->title }}</p>
                    <h1 class="od-h2">{{ $assignment->title }}</h1>
                </div>
                <div class="flex flex-col items-start sm:items-end gap-1">
                    @if($assignment->due_date)
                        <div class="flex items-center gap-1.5 text-sm od-num {{ $assignment->due_date->isPast() && !$submission ? 'font-semibold' : '' }}" style="{{ $assignment->due_date->isPast() && !$submission ? 'color: var(--od-danger);' : 'color: var(--od-muted);' }}">
                            <i class="far fa-calendar-alt"></i>
                            Due {{ $assignment->due_date->format('M d, Y \a\t h:i A') }}
                        </div>
                    @endif
                    <div class="text-sm" style="color: var(--od-muted);">
                        Max Points: <span class="font-semibold" style="color: var(--od-fg);">{{ $assignment->max_points }}</span>
                    </div>
                </div>
            </div>

            @if($assignment->description)
                <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed mb-4" style="color: var(--od-muted);">
                    <p>{{ $assignment->description }}</p>
                </div>
            @endif

            @if($assignment->instructions)
                <div class="p-4 rounded-xl" style="background: var(--od-fg-soft); border: 1px solid var(--od-border);">
                    <h4 class="font-semibold text-sm flex items-center gap-2 mb-2" style="color: var(--od-fg);">
                        <i class="fas fa-info-circle" style="color: var(--od-navy);"></i>Instructions
                    </h4>
                    <p class="text-sm leading-relaxed" style="color: var(--od-muted); white-space: pre-wrap;">{{ $assignment->instructions }}</p>
                </div>
            @endif
        </div>

        <!-- Previous Submission -->
        @if($submission)
            <div class="od-card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="od-h3">Your Submission</h3>
                    @if($submission->status === 'Graded')
                        <span class="od-badge od-badge-success">Graded</span>
                    @elseif($submission->status === 'Submitted')
                        <span class="od-badge od-badge-info">Submitted</span>
                    @endif
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-sm" style="color: var(--od-muted);">
                        <i class="far fa-clock"></i>
                        Submitted {{ $submission->submitted_at->diffForHumans() }}
                    </div>

                    @if($submission->submission_text)
                        <div class="p-4 rounded-xl" style="background: var(--od-fg-soft); border: 1px solid var(--od-border);">
                            <p class="text-sm leading-relaxed" style="color: var(--od-fg); white-space: pre-wrap;">{{ $submission->submission_text }}</p>
                        </div>
                    @endif

                    @if($submission->file_url)
                        <div>
                            <a href="{{ $submission->file_url }}" target="_blank" class="od-btn od-btn-secondary od-btn-sm">
                                <i class="fas fa-file-download"></i> Download Submission
                            </a>
                        </div>
                    @endif

                    @if($submission->status === 'Graded')
                        <div class="mt-4 pt-5" style="border-top: 1px solid var(--od-border);">
                            <div class="flex items-baseline gap-2 mb-3">
                                <span class="text-3xl font-bold" style="color: var(--od-navy); font-family: var(--font-display);">{{ $submission->points_earned }}</span>
                                <span style="color: var(--od-muted);">/ {{ $assignment->max_points }} points</span>
                            </div>
                            @if($submission->feedback)
                                <div class="p-4 rounded-xl" style="background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 20%, transparent);">
                                    <h4 class="font-semibold text-sm flex items-center gap-2 mb-1" style="color: var(--od-navy);">
                                        <i class="fas fa-comment-alt"></i>Instructor Feedback
                                    </h4>
                                    <p class="text-sm leading-relaxed" style="color: var(--od-navy);">{{ $submission->feedback }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Submission Form -->
        @if(!$submission || $submission->status !== 'Graded')
            <div class="od-card">
                <h3 class="od-h3 mb-5">{{ $submission ? 'Resubmit Assignment' : 'Submit Assignment' }}</h3>

                <form action="{{ route('student.assignments.submit', [$course, $assignment]) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-5">
                        <label for="submission_text" class="block text-sm font-medium mb-1.5" style="color: var(--od-fg);">Your Answer</label>
                        <textarea name="submission_text" id="submission_text" rows="6"
                            class="w-full px-4 py-3 border rounded-xl text-sm leading-relaxed shadow-sm resize-y"
                            style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);"
                            placeholder="Type your answer here...">{{ old('submission_text') }}</textarea>
                        @error('submission_text')
                            <p class="mt-1.5 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="submission_file" class="block text-sm font-medium mb-1.5" style="color: var(--od-fg);">Attach File (optional)</label>
                        <input type="file" name="submission_file" id="submission_file"
                            class="w-full px-4 py-3 border rounded-xl text-sm shadow-sm"
                            style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);">
                        <p class="mt-1.5 text-xs" style="color: var(--od-muted);">Max file size: 50MB</p>
                        @error('submission_file')
                            <p class="mt-1.5 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="od-btn od-btn-primary">
                        <i class="fas fa-paper-plane"></i> {{ $submission ? 'Resubmit Assignment' : 'Submit Assignment' }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
