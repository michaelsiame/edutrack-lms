@extends('layouts.dashboard')

@section('title','Notes: ' . $lesson->title . ' - Edutrack LMS')
@section('page_title','Lesson Notes')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-back-link route="student.notes.index" label="Back to Notes" class="mb-4" />

    <x-card variant="elevated">
        <x-slot:header>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $course->title }} &bull; {{ $lesson->module->title }}</p>
            </div>
        </x-slot:header>

        <form action="{{ route('student.notes.store', [$course, $lesson]) }}" method="POST">
            @csrf
            <div class="mb-5">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Your Notes</label>
                <textarea name="content" id="content" rows="14"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm leading-relaxed shadow-sm resize-y"
                    placeholder="Take your notes here...">{{ old('content', $note->content ?? '') }}</textarea>
                @error('content')
                    <p class="mt-1.5 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <x-button type="submit" variant="primary" icon="fa-save">Save Notes</x-button>
                <x-button :href="route('student.learning.show', [$course, $lesson])" variant="ghost" size="sm" icon="fa-arrow-left">
                    Back to Lesson
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
