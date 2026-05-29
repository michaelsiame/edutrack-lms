@extends('layouts.dashboard')

@section('title', 'Edit Testimonial - Admin')
@section('page_title', 'Edit Testimonial')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('admin.testimonials.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Testimonials
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0" style="background: var(--od-navy); color: var(--od-surface);">
                {{ strtoupper(substr($testimonial->student_name ?? $testimonial->name, 0, 1)) }}
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Testimonial</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Submitted {{ $testimonial->created_at?->diffForHumans() ?? 'unknown' }}</p>
            </div>
        </div>

        <form action="{{ route('admin.testimonials.update', $testimonial) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <!-- Student Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student Name</label>
                    <input type="text" name="student_name" value="{{ old('student_name', $testimonial->student_name ?? $testimonial->name) }}" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                </div>

                <!-- Course -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course Taken</label>
                    <input type="text" name="course_taken" value="{{ old('course_taken', $testimonial->course_taken) }}" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                </div>

                <!-- Testimonial Text -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Testimonial</label>
                    <textarea name="testimonial_text" rows="5" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">{{ old('testimonial_text', $testimonial->testimonial_text) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating</label>
                        <select name="rating" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                            @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ old('rating', $testimonial->rating) == $i ? 'selected' : '' }}>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                            <option value="pending" {{ old('status', $testimonial->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ old('status', $testimonial->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ old('status', $testimonial->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Job Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Title <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="current_job_title" value="{{ old('current_job_title', $testimonial->current_job_title) }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                    </div>

                    <!-- Company -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="company" value="{{ old('company', $testimonial->company) }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                </div>

                <!-- Featured -->
                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ $testimonial->is_featured ? 'checked' : '' }}
                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Featured</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Featured testimonials appear on the homepage</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('admin.testimonials.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm transition-colors">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
