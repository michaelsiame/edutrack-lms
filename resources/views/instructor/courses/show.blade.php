@extends('layouts.dashboard')

@section('title', $course->title .' - Edutrack LMS')
@section('page_title', $course->title)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
 <!-- Course Header -->
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
 <div class="flex items-center justify-between mb-4">
 <div>
 <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $course->title }}</h2>
 <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $course->enrollments?->count() ?? 0 }} students enrolled</p>
 </div>
 <div class="flex items-center space-x-3">
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $course->status ==='published' ?'bg-success-100 text-success-800' :'bg-gray-100 text-gray-800' }}">
 {{ ucfirst($course->status) }}
 </span>
 <a href="{{ route('instructor.live-sessions.index', $course) }}" class="text-sm text-danger-600 hover:text-danger-700 font-medium">
 <i class="fas fa-video mr-1"></i>Live Sessions
 </a>
 <a href="{{ route('instructor.courses.edit', $course) }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
 <i class="fas fa-edit mr-1"></i>Edit Details
 </a>
 </div>
 </div>
 </div>

 @if(session('success'))
 <div class="p-4 bg-success-50 border border-success-200 rounded-lg text-success-700">
 {{ session('success') }}
 </div>
 @endif

 @if(session('error'))
 <div class="p-4 bg-danger-50 border border-danger-200 rounded-lg text-danger-700">
 {{ session('error') }}
 </div>
 @endif

 <!-- Add Module -->
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add New Module</h3>
 <form action="{{ route('instructor.courses.modules.store', $course) }}" method="POST" class="flex items-end gap-4">
 @csrf
 <div class="flex-1">
 <label for="module_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Module Title</label>
 <input type="text" name="title" id="module_title" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="e.g., Introduction to the Course">
 </div>
 <div class="w-24">
 <label for="module_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order</label>
 <input type="number" name="display_order" id="module_order" min="0"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="1">
 </div>
 <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
 <i class="fas fa-plus mr-1"></i>Add Module
 </button>
 </form>
 </div>

 <!-- Modules & Lessons -->
 @forelse($course->modules as $module)
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <!-- Module Header -->
 <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
 <div class="flex items-center justify-between">
 <div class="flex items-center gap-3">
 <span class="w-8 h-8 bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 rounded-lg flex items-center justify-center text-sm font-bold">
 {{ $module->display_order }}
 </span>
 <div>
 <h4 class="font-semibold text-gray-900 dark:text-white">{{ $module->title }}</h4>
 <p class="text-xs text-gray-500 dark:text-gray-400">{{ $module->lessons->count() }} lessons</p>
 </div>
 </div>
 <div class="flex items-center gap-2">
 <button onclick="toggleEditModule({{ $module->id }})" class="text-sm text-gray-500 hover:text-primary-600">
 <i class="fas fa-edit"></i>
 </button>
 <form action="{{ route('instructor.courses.modules.destroy', [$course, $module]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this module and all its lessons?')">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-sm text-gray-500 hover:text-danger-600">
 <i class="fas fa-trash"></i>
 </button>
 </form>
 </div>
 </div>

 <!-- Edit Module Form (hidden by default) -->
 <div id="edit-module-{{ $module->id }}" class="hidden mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
 <form action="{{ route('instructor.courses.modules.update', [$course, $module]) }}" method="POST" class="flex items-end gap-4">
 @csrf
 @method('PUT')
 <div class="flex-1">
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
 <input type="text" name="title" value="{{ $module->title }}" required
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div class="w-20">
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Order</label>
 <input type="number" name="display_order" value="{{ $module->display_order }}" min="0"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <button type="submit" class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">Update</button>
 <button type="button" onclick="toggleEditModule({{ $module->id }})" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300">Cancel</button>
 </form>
 </div>
 </div>

 <!-- Lessons List -->
 <div class="divide-y divide-gray-100 dark:divide-gray-700">
 @forelse($module->lessons as $lesson)
 <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
 <div class="flex items-center justify-between">
 <div class="flex items-center gap-3">
 <span class="w-6 h-6 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded flex items-center justify-center text-xs">
 {{ $lesson->display_order }}
 </span>
 <div>
 <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $lesson->title }}</p>
 <div class="flex items-center gap-2 mt-0.5">
 <span class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $lesson->lesson_type }}</span>
 @if($lesson->duration_minutes)
 <span class="text-xs text-gray-400">&bull; {{ $lesson->duration_minutes }} min</span>
 @endif
 @if($lesson->is_preview)
 <span class="text-xs text-success-600 bg-success-50 px-1.5 py-0.5 rounded">Preview</span>
 @endif
 </div>
 </div>
 </div>
 <div class="flex items-center gap-2">
 <button onclick="toggleEditLesson({{ $lesson->id }})" class="text-xs text-gray-500 hover:text-primary-600">
 <i class="fas fa-edit"></i>
 </button>
 <form action="{{ route('instructor.courses.modules.lessons.destroy', [$course, $module, $lesson]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this lesson?')">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-xs text-gray-500 hover:text-danger-600">
 <i class="fas fa-trash"></i>
 </button>
 </form>
 </div>
 </div>

 <!-- Edit Lesson Form (hidden by default) -->
 <div id="edit-lesson-{{ $lesson->id }}" class="hidden mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
 <form action="{{ route('instructor.courses.modules.lessons.update', [$course, $module, $lesson]) }}" method="POST" class="space-y-3">
 @csrf
 @method('PUT')
 <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
 <input type="text" name="title" value="{{ $lesson->title }}" required
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Type</label>
 <select name="lesson_type" required
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="video" {{ $lesson->lesson_type ==='video' ?'selected' :'' }}>Video</option>
 <option value="text" {{ $lesson->lesson_type ==='text' ?'selected' :'' }}>Text</option>
 <option value="quiz" {{ $lesson->lesson_type ==='quiz' ?'selected' :'' }}>Quiz</option>
 <option value="assignment" {{ $lesson->lesson_type ==='assignment' ?'selected' :'' }}>Assignment</option>
 </select>
 </div>
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Video URL (optional)</label>
 <input type="url" name="video_url" value="{{ $lesson->video_url }}"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="https://youtube.com/watch?v=...">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Content</label>
 <textarea name="content" rows="3"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">{{ $lesson->content }}</textarea>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Duration (min)</label>
 <input type="number" name="duration_minutes" value="{{ $lesson->duration_minutes }}" min="1"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Order</label>
 <input type="number" name="display_order" value="{{ $lesson->display_order }}" min="0"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div class="flex items-center pt-5">
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="is_preview" value="1" {{ $lesson->is_preview ?'checked' :'' }}
 class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
 <span class="ml-2">Free Preview</span>
 </label>
 </div>
 </div>
 <div class="flex gap-2">
 <button type="submit" class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">Update Lesson</button>
 <button type="button" onclick="toggleEditLesson({{ $lesson->id }})" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300">Cancel</button>
 </div>
 </form>
 </div>
 </div>
 @empty
 <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">
 No lessons in this module yet.
 </div>
 @endforelse
 </div>

 <!-- Add Lesson -->
 <div class="p-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700">
 <button onclick="toggleAddLesson({{ $module->id }})" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
 <i class="fas fa-plus mr-1"></i>Add Lesson
 </button>

 <div id="add-lesson-{{ $module->id }}" class="hidden mt-3">
 <form action="{{ route('instructor.courses.modules.lessons.store', [$course, $module]) }}" method="POST" class="space-y-3">
 @csrf
 <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
 <input type="text" name="title" required
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="Lesson title">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Type</label>
 <select name="lesson_type" required
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="video">Video</option>
 <option value="text">Text</option>
 <option value="quiz">Quiz</option>
 <option value="assignment">Assignment</option>
 </select>
 </div>
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Video URL (optional)</label>
 <input type="url" name="video_url"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="https://youtube.com/watch?v=...">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Content</label>
 <textarea name="content" rows="2"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="Lesson content or description..."></textarea>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Duration (min)</label>
 <input type="number" name="duration_minutes" min="1"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="15">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Order</label>
 <input type="number" name="display_order" min="0"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="1">
 </div>
 <div class="flex items-center pt-5">
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="is_preview" value="1"
 class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
 <span class="ml-2">Free Preview</span>
 </label>
 </div>
 </div>
 <div class="flex gap-2">
 <button type="submit" class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">Create Lesson</button>
 <button type="button" onclick="toggleAddLesson({{ $module->id }})" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300">Cancel</button>
 </div>
 </form>
 </div>
 </div>
 </div>
 @empty
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 text-center">
 <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
 <i class="fas fa-folder-open text-2xl text-gray-400"></i>
 </div>
 <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Modules Yet</h3>
 <p class="text-gray-500 dark:text-gray-400 text-sm">Start building your course by adding your first module above.</p>
 </div>
 @endforelse
</div>

<script>
function toggleEditModule(moduleId) {
 const el = document.getElementById('edit-module-' + moduleId);
 el.classList.toggle('hidden');
}

function toggleEditLesson(lessonId) {
 const el = document.getElementById('edit-lesson-' + lessonId);
 el.classList.toggle('hidden');
}

function toggleAddLesson(moduleId) {
 const el = document.getElementById('add-lesson-' + moduleId);
 el.classList.toggle('hidden');
}
</script>
@endsection
