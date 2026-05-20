@extends('layouts.dashboard')

@section('title', 'Notes: ' . $lesson->title . ' - Edutrack LMS')
@section('page_title', 'Lesson Notes')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('student.notes.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i>Back to Notes
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $course->title }} &bull; {{ $lesson->module->title }}</p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('student.notes.store', [$course, $lesson]) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Notes</label>
                <textarea name="content" id="content" rows="12"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Take your notes here...">{{ old('content', $note->content ?? '') }}</textarea>
                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
                    <i class="fas fa-save mr-2"></i>Save Notes
                </button>
                <a href="{{ route('student.learning.show', [$course, $lesson]) }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Back to Lesson
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
