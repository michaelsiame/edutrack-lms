@extends('layouts.dashboard')

@section('title','Notes: ' . $lesson->title . ' - Edutrack LMS')
@section('page_title','Lesson Notes')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-3xl mx-auto">
        <x-back-link route="student.notes.index" label="Back to Notes" class="mb-4" variant="od" />

        <div class="od-card">
            <div class="mb-5">
                <p class="od-eyebrow" style="margin-bottom: 4px;">{{ $course->title }} &bull; {{ $lesson->module->title }}</p>
                <h1 class="od-h2">{{ $lesson->title }}</h1>
            </div>

            <form action="{{ route('student.notes.store', [$course, $lesson]) }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label for="content" class="block text-sm font-medium mb-1.5" style="color: var(--od-fg);">Your Notes</label>
                    <textarea name="content" id="content" rows="14"
                        class="w-full px-4 py-3 border rounded-xl text-sm leading-relaxed shadow-sm resize-y"
                        style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);"
                        placeholder="Take your notes here...">{{ old('content', $note->content ?? '') }}</textarea>
                    @error('content')
                        <p class="mt-1.5 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <button type="submit" class="od-btn od-btn-primary">
                        <i class="fas fa-save"></i> Save Notes
                    </button>
                    <a href="{{ route('student.learning.show', [$course, $lesson]) }}" class="od-btn od-btn-ghost od-btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Lesson
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
