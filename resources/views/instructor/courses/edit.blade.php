@extends('layouts.dashboard')

@section('title','Edit Course - ' . $course->title)
@section('page_title','Edit Course')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="od-card p-6">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Course</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Update the details for <strong>{{ $course->title }}</strong>.</p>
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

        <form action="{{ route('instructor.courses.update', $course) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Basic Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course Title <span class="text-danger-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $course->title) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                </div>

                <div class="md:col-span-2">
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug <span class="text-danger-500">*</span></label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $course->slug) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                </div>

                <div class="md:col-span-2">
                    <label for="short_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Short Description</label>
                    <input type="text" name="short_description" id="short_description" value="{{ old('short_description', $course->short_description) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Description</label>
                    <textarea name="description" id="description" rows="5" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description', $course->description) }}</textarea>
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category <span class="text-danger-500">*</span></label>
                    <select name="category_id" id="category_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Level</label>
                    <select name="level" id="level" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="beginner" {{ old('level', $course->level) == 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ old('level', $course->level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ old('level', $course->level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>
            </div>

            {{-- Pricing & Duration --}}
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Pricing & Duration</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (ZMW) <span class="text-danger-500">*</span></label>
                        <input type="number" name="price" id="price" value="{{ old('price', $course->price) }}" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                    </div>
                    <div>
                        <label for="discount_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Discount Price (ZMW)</label>
                        <input type="number" name="discount_price" id="discount_price" value="{{ old('discount_price', $course->discount_price) }}" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label for="duration_weeks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (Weeks)</label>
                        <input type="number" name="duration_weeks" id="duration_weeks" value="{{ old('duration_weeks', $course->duration_weeks) }}" min="1" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label for="total_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Hours</label>
                        <input type="number" name="total_hours" id="total_hours" value="{{ old('total_hours', $course->total_hours) }}" min="0" step="0.5" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label for="max_students" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Students</label>
                        <input type="number" name="max_students" id="max_students" value="{{ old('max_students', $course->max_students) }}" min="0" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <p class="text-xs text-gray-500 mt-1">0 = unlimited</p>
                    </div>
                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Language</label>
                        <input type="text" name="language" id="language" value="{{ old('language', $course->language ?? 'English') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            {{-- Dates --}}
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Course Dates</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $course->start_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $course->end_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            {{-- Thumbnail --}}
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Media</h3>
                @if($course->thumbnail_image_url)
                <div class="mb-4">
                    <p class="text-xs text-gray-500 mb-2">Current Thumbnail</p>
                    <img src="{{ $course->thumbnail_image_url }}" alt="Thumbnail" class="w-48 h-32 object-cover rounded-lg border">
                </div>
                @endif
                <div>
                    <label for="thumbnail" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Replace Thumbnail</label>
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>
                <div class="mt-4">
                    <label for="video_intro_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Intro Video URL</label>
                    <input type="url" name="video_intro_url" id="video_intro_url" value="{{ old('video_intro_url', $course->video_intro_url) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>

            {{-- Advanced --}}
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Advanced</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="prerequisites" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prerequisites</label>
                        <textarea name="prerequisites" id="prerequisites" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('prerequisites', $course->prerequisites) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label for="learning_outcomes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Learning Outcomes</label>
                        <textarea name="learning_outcomes" id="learning_outcomes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('learning_outcomes', $course->learning_outcomes) }}</textarea>
                    </div>
                    <div class="flex items-center gap-6">
                        <!-- is_featured removed: only admins can feature courses -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                @if($course->status === 'published')
                                    <option value="published" selected>Published (approved)</option>
                                    <option value="draft">Revert to Draft</option>
                                @elseif($course->status === 'under_review')
                                    <option value="draft" {{ old('status', $course->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" selected>Submit for Approval</option>
                                @else
                                    <option value="draft" {{ old('status', $course->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $course->status) == 'published' ? 'selected' : '' }}>Submit for Approval</option>
                                @endif
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Publishing requires admin approval.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('instructor.courses.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors text-sm font-medium">
                    <i class="fas fa-save mr-2"></i>Update Course
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
