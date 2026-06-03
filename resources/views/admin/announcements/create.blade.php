@extends('layouts.dashboard')

@section('title','New Announcement - Admin')
@section('page_title','New Announcement')

@section('content')
<div class="max-w-3xl mx-auto">
 <div class="od-card p-6">
 <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Create Announcement</h2>

 <form action="{{ route('admin.announcements.store') }}" method="POST">
 @csrf

 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
 <input type="text" name="title" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>

 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content</label>
 <textarea name="content" rows="5" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course (optional)</label>
 <select name="course_id"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="">All Students</option>
 @foreach($courses as $course)
 <option value="{{ $course->id }}">{{ $course->title }}</option>
 @endforeach
 </select>
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
 <select name="announcement_type" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="general">General</option>
 <option value="course">Course</option>
 <option value="system">System</option>
 <option value="urgent">Urgent</option>
 </select>
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
 <select name="priority" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="low">Low</option>
 <option value="normal" selected>Normal</option>
 <option value="high">High</option>
 <option value="urgent">Urgent</option>
 </select>
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Publish Date</label>
 <input type="datetime-local" name="published_at"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry Date</label>
 <input type="datetime-local" name="expires_at"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 </div>

 <div class="mb-6">
 <label class="flex items-center text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="is_published" value="1" checked
 class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
 <span class="ml-2">Publish immediately</span>
 </label>
 </div>

 <div class="flex gap-3">
 <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">Create</button>
 <a href="{{ route('admin.announcements.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">Cancel</a>
 </div>
 </form>
 </div>
</div>
@endsection
