@extends('layouts.dashboard')

@section('title','Edit Course - Edutrack LMS')
@section('page_title','Edit Course')

@section('content')
<div class="max-w-3xl mx-auto">
 <div class="od-card">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-bold text-gray-900 dark:text-white">Edit: {{ $course->title }}</h2>
 </div>

 <form action="{{ route('admin.courses.update', $course) }}" method="POST" class="p-6 space-y-6">
 @csrf
 @method('PUT')

 @if($errors->any())
 <div class="p-4 od-toast-error border rounded-lg text-sm">
 <ul class="list-disc list-inside space-y-1">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title <span class="text-red-500">*</span></label>
 <input type="text" name="title" value="{{ old('title', $course->title) }}" required maxlength="255"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug <span class="text-red-500">*</span></label>
 <input type="text" name="slug" value="{{ old('slug', $course->slug) }}" required maxlength="255"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 </div>
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
 <textarea name="description" rows="3"
 class="rich-editor w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">{{ old('description', $course->description) }}</textarea>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category <span class="text-red-500">*</span></label>
 <select name="category_id" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="">Select Category</option>
 @foreach($categories as $category)
 <option value="{{ $category->id }}" {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
 @endforeach
 </select>
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instructor <span class="text-red-500">*</span></label>
 <select name="instructor_id" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="">Select Instructor</option>
 @foreach($instructors as $instructor)
 <option value="{{ $instructor->id }}" {{ old('instructor_id', $course->instructor_id) == $instructor->id ? 'selected' : '' }}>{{ $instructor->user?->full_name ?? 'Instructor #' . $instructor->id }}</option>
 @endforeach
 </select>
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price (ZMW) <span class="text-red-500">*</span></label>
 <input type="number" name="price" value="{{ old('price', $course->price) }}" required step="0.01" min="0"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Price (ZMW)</label>
 <input type="number" name="discount_price" value="{{ old('discount_price', $course->discount_price) }}" step="0.01" min="0"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (weeks)</label>
 <input type="number" name="duration_weeks" value="{{ old('duration_weeks', $course->duration_weeks) }}" min="1"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Level</label>
 <select name="level"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="">Select Level</option>
 <option value="Beginner" {{ old('level', $course->level) == 'Beginner' ? 'selected' : '' }}>Beginner</option>
 <option value="Intermediate" {{ old('level', $course->level) == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
 <option value="Advanced" {{ old('level', $course->level) == 'Advanced' ? 'selected' : '' }}>Advanced</option>
 <option value="All Levels" {{ old('level', $course->level) == 'All Levels' ? 'selected' : '' }}>All Levels</option>
 </select>
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span></label>
 <select name="status" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="draft" {{ old('status', $course->status) == 'draft' ? 'selected' : '' }}>Draft</option>
 <option value="under_review" {{ old('status', $course->status) == 'under_review' ? 'selected' : '' }}>Under Review</option>
 <option value="published" {{ old('status', $course->status) == 'published' ? 'selected' : '' }}>Published</option>
 <option value="archived" {{ old('status', $course->status) == 'archived' ? 'selected' : '' }}>Archived</option>
 </select>
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thumbnail URL</label>
 <input type="url" name="thumbnail_url" value="{{ old('thumbnail_url', $course->thumbnail_url) }}"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
 placeholder="https://example.com/image.jpg">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Video Intro URL</label>
 <input type="url" name="video_intro_url" value="{{ old('video_intro_url', $course->video_intro_url) }}"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
 placeholder="https://youtube.com/watch?v=...">
 </div>
 </div>

 <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
 <button type="submit" class="px-4 py-2 od-btn od-btn-primary text-sm">Update Course</button>
 <a href="{{ route('admin.courses.index') }}" class="px-4 py-2 od-btn od-btn-ghost text-sm">Cancel</a>
 </div>
 </form>
 </div>
</div>
@endsection

@push('scripts')
    @include('partials.rich-editor')
@endpush
