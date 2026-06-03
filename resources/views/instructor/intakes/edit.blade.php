@extends('layouts.dashboard')

@section('title', 'Edit Intake - ' . $course->title . ' - Edutrack LMS')
@section('page_title', 'Edit Intake')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('instructor.courses.intakes.index', $course) }}" class="text-sm text-primary-600 hover:text-primary-700">
            <i class="fas fa-arrow-left mr-1"></i>Back to Intakes
        </a>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">Edit Intake</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $intake->name }}</p>
    </div>

    <div class="od-card p-6">
        <form action="{{ route('instructor.courses.intakes.update', [$course, $intake]) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Intake Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" id="name" required value="{{ old('name', $intake->name) }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                @error('name')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $intake->start_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    @error('start_date')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $intake->end_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    @error('end_date')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="application_deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Application Deadline</label>
                    <input type="date" name="application_deadline" id="application_deadline" value="{{ old('application_deadline', $intake->application_deadline?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    @error('application_deadline')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="learning_deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Learning Deadline</label>
                    <input type="date" name="learning_deadline" id="learning_deadline" value="{{ old('learning_deadline', $intake->learning_deadline?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    @error('learning_deadline')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="max_students" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Students</label>
                    <input type="number" name="max_students" id="max_students" min="0" value="{{ old('max_students', $intake->max_students) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        placeholder="0 = unlimited">
                    @error('max_students')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="price_override" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price Override (ZMW)</label>
                    <input type="number" name="price_override" id="price_override" min="0" step="0.01" value="{{ old('price_override', $intake->price_override) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Leave blank for course default">
                    @error('price_override')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-danger-500">*</span></label>
                    <select name="status" id="status" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <option value="draft" {{ old('status', $intake->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="open" {{ old('status', $intake->status) === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ old('status', $intake->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="in_progress" {{ old('status', $intake->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ old('status', $intake->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Display Order</label>
                    <input type="number" name="display_order" id="display_order" min="0" value="{{ old('display_order', $intake->display_order) }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                        placeholder="0">
                    @error('display_order')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="od-btn od-btn-primary">
                    <i class="fas fa-save mr-1.5"></i>Update Intake
                </button>
                <a href="{{ route('instructor.courses.intakes.index', $course) }}" class="od-btn od-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
