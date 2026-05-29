@extends('layouts.dashboard')

@section('title', 'Edit Photo - Admin')
@section('page_title', 'Edit Photo')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('admin.photos.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Photos
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <!-- Image Preview -->
        <div class="h-64 bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
            <img src="{{ $photo->image_path }}" alt="{{ $photo->title }}" class="max-h-full max-w-full object-contain">
        </div>

        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5">Edit Photo Details</h3>

            <form action="{{ route('admin.photos.update', $photo) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                        <input type="text" name="title" value="{{ old('title', $photo->title) }}" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea name="description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">{{ old('description', $photo->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                            <select name="category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                                <option value="general" {{ old('category', $photo->category) === 'general' ? 'selected' : '' }}>General</option>
                                <option value="campus" {{ old('category', $photo->category) === 'campus' ? 'selected' : '' }}>Campus</option>
                                <option value="lab" {{ old('category', $photo->category) === 'lab' ? 'selected' : '' }}>Computer Lab</option>
                                <option value="classroom" {{ old('category', $photo->category) === 'classroom' ? 'selected' : '' }}>Classroom</option>
                                <option value="event" {{ old('category', $photo->category) === 'event' ? 'selected' : '' }}>Event</option>
                                <option value="facility" {{ old('category', $photo->category) === 'facility' ? 'selected' : '' }}>Facility</option>
                                <option value="graduation" {{ old('category', $photo->category) === 'graduation' ? 'selected' : '' }}>Graduation</option>
                                <option value="workshop" {{ old('category', $photo->category) === 'workshop' ? 'selected' : '' }}>Workshop</option>
                            </select>
                        </div>

                        <!-- Display Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Display Order</label>
                            <input type="number" name="display_order" value="{{ old('display_order', $photo->display_order) }}" min="0"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lower numbers appear first</p>
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ $photo->is_featured ? 'checked' : '' }}
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Featured</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Shown in homepage gallery</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $photo->is_active ? 'checked' : '' }}
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Visible on public pages</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('admin.photos.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm transition-colors">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
