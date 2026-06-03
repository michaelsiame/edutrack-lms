@extends('layouts.dashboard')

@section('title', 'Edit Quiz - ' . $quiz->title)
@section('page_title', 'Edit Quiz')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="od-card p-6">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Quiz</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Update quiz settings.</p>
        </div>

        @if($errors->any())
        <div class="bg-danger-100 border border-danger-400 text-danger-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc pl-5 text-sm">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('instructor.quizzes.update', $quiz) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course</label>
                <select name="course_id" id="course_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ old('course_id', $quiz->course_id) == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quiz Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $quiz->title) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description', $quiz->description) }}</textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="passing_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Passing Score (%)</label>
                    <input type="number" name="passing_score" id="passing_score" value="{{ old('passing_score', $quiz->passing_score) }}" min="0" max="100" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                </div>
                <div>
                    <label for="time_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time Limit (min)</label>
                    <input type="number" name="time_limit" id="time_limit" value="{{ old('time_limit', $quiz->time_limit_minutes) }}" min="1" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label for="max_attempts" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Attempts</label>
                    <input type="number" name="max_attempts" id="max_attempts" value="{{ old('max_attempts', $quiz->max_attempts) }}" min="1" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $quiz->is_published) ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                <label for="is_published" class="ml-2 block text-sm text-gray-900 dark:text-white">Publish immediately</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('instructor.quizzes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors text-sm font-medium">
                    <i class="fas fa-save mr-2"></i>Update Quiz
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
