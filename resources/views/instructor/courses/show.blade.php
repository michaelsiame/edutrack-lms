@extends('layouts.dashboard')

@section('title', $course->title .' - Edutrack LMS')
@section('page_title', $course->title)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{}">
 <!-- Course Header -->
 <div class="od-card p-6">
 <div class="flex items-center justify-between mb-4">
 <div>
 <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $course->title }}</h2>
 <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $course->enrollments?->count() ?? 0 }} students enrolled</p>
 </div>
 <div class="flex items-center space-x-3">
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $course->status ==='published' ?'bg-success-100 text-success-800' :'bg-gray-100 text-gray-800' }}">
 {{ ucfirst($course->status) }}
 </span>
 @if(!$course->is_template)
 <form action="{{ route('instructor.courses.save-as-template', $course) }}" method="POST" class="inline" data-confirm="Save this course as a reusable template?">
 @csrf
 <button type="submit" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
 <i class="fas fa-clone mr-1"></i>Save as Template
 </button>
 </form>
 @endif
 <a href="{{ route('instructor.courses.intakes.index', $course) }}" class="text-sm text-success-600 hover:text-success-700 font-medium">
 <i class="fas fa-calendar-alt mr-1"></i>Intakes ({{ $course->intakes->count() }})
 </a>
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

 @if($errors->any())
 <div class="p-4 bg-danger-50 border border-danger-200 rounded-lg text-danger-700">
 <ul class="list-disc list-inside text-sm">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <!-- Bulk Upload Lessons -->
 <div class="od-card p-6">
 <div class="flex items-center justify-between mb-4">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Bulk Upload Lessons</h3>
 <button onclick="toggleBulkUpload()" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
 <i class="fas fa-upload mr-1"></i>Show Upload Form
 </button>
 </div>
 <div id="bulk-upload-form" class="hidden">
 <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
 CSV format: <code>module_id,title,lesson_type,content,duration_minutes,video_url,is_preview,display_order</code>
 <br>Lesson types: Video, Reading, Quiz, Assignment. is_preview: 0 or 1.
 </p>
 <form action="{{ route('instructor.courses.lessons.bulk-upload', $course) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
 @csrf
 <div class="flex-1 min-w-[250px]">
 <input type="file" name="csv_file" accept=".csv" required
 class="text-sm text-gray-600 dark:text-gray-300 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
 </div>
 <button type="submit" class="od-btn od-btn-primary od-btn-sm font-medium">
 <i class="fas fa-file-csv mr-1"></i>Import CSV
 </button>
 <button type="button" onclick="toggleBulkUpload()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm">Cancel</button>
 </form>
 </div>
 </div>

 <!-- Add Module -->
 <div class="od-card p-6">
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
 <button type="submit" class="od-btn od-btn-primary od-btn-sm font-medium">
 <i class="fas fa-plus mr-1"></i>Add Module
 </button>
 </form>
 </div>

 <!-- Modules & Lessons -->
 @forelse($course->modules as $module)
 <div class="od-card" style="padding: 0; overflow: hidden;">
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
 <form action="{{ route('instructor.courses.modules.move-up', [$course, $module]) }}" method="POST" class="inline">
 @csrf
 <button type="submit" class="text-sm text-gray-400 hover:text-primary-600" title="Move up">
 <i class="fas fa-arrow-up"></i>
 </button>
 </form>
 <form action="{{ route('instructor.courses.modules.move-down', [$course, $module]) }}" method="POST" class="inline">
 @csrf
 <button type="submit" class="text-sm text-gray-400 hover:text-primary-600" title="Move down">
 <i class="fas fa-arrow-down"></i>
 </button>
 </form>
 <button onclick="toggleEditModule({{ $module->id }})" class="text-sm text-gray-500 hover:text-primary-600">
 <i class="fas fa-edit"></i>
 </button>
 <form action="{{ route('instructor.courses.modules.destroy', [$course, $module]) }}" method="POST" class="inline" data-confirm="Delete this module and all its lessons">
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
 <span class="text-xs text-gray-500 dark:text-gray-400">{{ $lesson->lesson_type }}</span>
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
 <form action="{{ route('instructor.courses.modules.lessons.move-up', [$course, $module, $lesson]) }}" method="POST" class="inline">
 @csrf
 <button type="submit" class="text-xs text-gray-400 hover:text-primary-600" title="Move up">
 <i class="fas fa-arrow-up"></i>
 </button>
 </form>
 <form action="{{ route('instructor.courses.modules.lessons.move-down', [$course, $module, $lesson]) }}" method="POST" class="inline">
 @csrf
 <button type="submit" class="text-xs text-gray-400 hover:text-primary-600" title="Move down">
 <i class="fas fa-arrow-down"></i>
 </button>
 </form>
 <button onclick="toggleEditLesson({{ $lesson->id }})" class="text-xs text-gray-500 hover:text-primary-600" title="Edit lesson">
 <i class="fas fa-edit"></i>
 </button>
 <button onclick="toggleLessonResources({{ $lesson->id }})" class="text-xs text-gray-500 hover:text-success-600" title="Manage resources">
 <i class="fas fa-paperclip"></i>
 </button>
 <form action="{{ route('instructor.courses.modules.lessons.destroy', [$course, $module, $lesson]) }}" method="POST" class="inline" data-confirm="Delete this lesson">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-xs text-gray-500 hover:text-danger-600" title="Delete lesson">
 <i class="fas fa-trash"></i>
 </button>
 </form>
 </div>
 </div>

 <!-- Lesson Resources (hidden by default) -->
 <div id="lesson-resources-{{ $lesson->id }}" class="hidden mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
 @if($lesson->resources->isNotEmpty())
 <div class="mb-3">
 <h5 class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Attached Resources</h5>
 <div class="space-y-1.5">
 @foreach($lesson->resources as $res)
 <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2">
 <div class="flex items-center gap-2 min-w-0">
 <i class="fas fa-file text-gray-400 text-xs"></i>
 <span class="text-xs text-gray-700 dark:text-gray-300 truncate">{{ $res->title }}</span>
 <span class="text-[10px] text-gray-400">({{ $res->resource_type }}, {{ $res->file_size_kb }} KB)</span>
 </div>
 <form action="{{ route('instructor.courses.modules.lessons.resources.destroy', [$course, $module, $lesson, $res]) }}" method="POST" class="inline" data-confirm="Delete this resource">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-xs text-danger-500 hover:text-danger-700" title="Delete resource">
 <i class="fas fa-times"></i>
 </button>
 </form>
 </div>
 @endforeach
 </div>
 </div>
 @endif
 <form action="{{ route('instructor.courses.modules.lessons.resources.store', [$course, $module, $lesson]) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
 @csrf
 <div class="flex-1 min-w-[200px]">
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Resource Title</label>
 <input type="text" name="title" required placeholder="e.g., Course Syllabus"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div class="flex-1 min-w-[200px]">
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description (optional)</label>
 <input type="text" name="description" placeholder="Brief description"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">File (max 50MB)</label>
 <input type="file" name="resource_file" required
 class="text-xs text-gray-600 dark:text-gray-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
 </div>
 <button type="submit" class="px-3 py-2 bg-success-600 text-white text-sm rounded-lg hover:bg-success-700">
 <i class="fas fa-upload mr-1"></i>Upload
 </button>
 </form>
 </div>

 <!-- Edit Lesson Form (hidden by default) -->
 <div id="edit-lesson-{{ $lesson->id }}" class="hidden mt-3 pt-3 border-t border-gray-100 dark:border-gray-700"
      x-data="lessonForm('{{ $lesson->lesson_type }}')"
      x-init="init()">
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
 <select name="lesson_type" x-model="type" required
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="Video">Video</option>
 <option value="Reading">Text / Reading</option>
 <option value="Quiz">Quiz</option>
 <option value="Assignment">Assignment</option>
 </select>
 </div>
 </div>

 <!-- Type-specific helper banners -->
 <div x-show="type === 'Quiz'" x-cloak class="p-3 bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-lg">
 <p class="text-xs text-info-700 dark:text-info-300 flex items-start gap-2">
 <i class="fas fa-info-circle mt-0.5"></i>
 <span>Link this lesson to an existing quiz, or create the quiz separately after saving.</span>
 </p>
 </div>
 <div x-show="type === 'Assignment'" x-cloak class="p-3 bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-lg">
 <p class="text-xs text-info-700 dark:text-info-300 flex items-start gap-2">
 <i class="fas fa-info-circle mt-0.5"></i>
 <span>Link this lesson to an existing assignment, or create the assignment separately after saving.</span>
 </p>
 </div>

 <!-- Quiz Linking -->
 <div x-show="type === 'Quiz'" x-cloak>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Link to Quiz (optional)</label>
 <select name="linked_quiz_id"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="">-- Select a quiz --</option>
 @foreach($unlinkedQuizzes as $q)
 <option value="{{ $q->id }}">{{ $q->title }}</option>
 @endforeach
 </select>
 <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Only quizzes not already linked to a lesson are shown.</p>
 </div>

 <!-- Assignment Linking -->
 <div x-show="type === 'Assignment'" x-cloak>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Link to Assignment (optional)</label>
 <select name="linked_assignment_id"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="">-- Select an assignment --</option>
 @foreach($unlinkedAssignments as $a)
 <option value="{{ $a->id }}">{{ $a->title }}</option>
 @endforeach
 </select>
 <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Only assignments not already linked to a lesson are shown.</p>
 </div>

 <!-- Video URL -->
 <div x-show="showVideoUrl" x-cloak>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
 Video URL <span x-show="isVideo" class="text-danger-500">*</span>
 </label>
 <input type="url" name="video_url" value="{{ $lesson->video_url }}" :required="isVideo"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="https://youtube.com/watch?v=... or https://vimeo.com/...">
 <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Supports YouTube and Vimeo URLs.</p>
 </div>

 <!-- Content -->
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
 <span x-text="contentLabel"></span>
 <span x-show="contentRequired" class="text-danger-500">*</span>
 </label>
 <textarea name="content" id="tinymce-edit-{{ $lesson->id }}" rows="8"
 class="tinymce-editor w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">{{ $lesson->content }}</textarea>
 </div>

 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Change Summary (optional)</label>
 <input type="text" name="change_summary"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="e.g. Fixed typos, added images">
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
 <div class="flex gap-2 flex-wrap items-center">
 <button type="submit" class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">Update Lesson</button>
 <a href="{{ route('instructor.lessons.versions', [$course, $module, $lesson]) }}" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Version History</a>
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

 <div id="add-lesson-{{ $module->id }}" class="hidden mt-3"
      x-data="lessonForm('Video')"
      x-init="init()">
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
 <select name="lesson_type" x-model="type" required
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="Video">Video</option>
 <option value="Reading">Text / Reading</option>
 <option value="Quiz">Quiz</option>
 <option value="Assignment">Assignment</option>
 </select>
 </div>
 </div>

 <!-- Type-specific helper banners -->
 <div x-show="type === 'Quiz'" x-cloak class="p-3 bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-lg">
 <p class="text-xs text-info-700 dark:text-info-300 flex items-start gap-2">
 <i class="fas fa-info-circle mt-0.5"></i>
 <span>Link this lesson to an existing quiz, or create the quiz separately after saving.</span>
 </p>
 </div>
 <div x-show="type === 'Assignment'" x-cloak class="p-3 bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-lg">
 <p class="text-xs text-info-700 dark:text-info-300 flex items-start gap-2">
 <i class="fas fa-info-circle mt-0.5"></i>
 <span>Link this lesson to an existing assignment, or create the assignment separately after saving.</span>
 </p>
 </div>

 <!-- Quiz Linking -->
 <div x-show="type === 'Quiz'" x-cloak>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Link to Quiz (optional)</label>
 <select name="linked_quiz_id"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="">-- Select a quiz --</option>
 @php
 $linkedQuiz = $lesson->quizzes->first();
 @endphp
 @foreach($unlinkedQuizzes->merge($lesson->quizzes)->unique('id') as $q)
 <option value="{{ $q->id }}" {{ $linkedQuiz && $linkedQuiz->id == $q->id ? 'selected' : '' }}>{{ $q->title }}</option>
 @endforeach
 </select>
 <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Only quizzes not already linked to a lesson are shown.</p>
 </div>

 <!-- Assignment Linking -->
 <div x-show="type === 'Assignment'" x-cloak>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Link to Assignment (optional)</label>
 <select name="linked_assignment_id"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="">-- Select an assignment --</option>
 @php
 $linkedAssignment = $lesson->assignments->first();
 @endphp
 @foreach($unlinkedAssignments->merge($lesson->assignments)->unique('id') as $a)
 <option value="{{ $a->id }}" {{ $linkedAssignment && $linkedAssignment->id == $a->id ? 'selected' : '' }}>{{ $a->title }}</option>
 @endforeach
 </select>
 <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Only assignments not already linked to a lesson are shown.</p>
 </div>

 <!-- Video URL -->
 <div x-show="showVideoUrl" x-cloak>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
 Video URL <span x-show="isVideo" class="text-danger-500">*</span>
 </label>
 <input type="url" name="video_url" :required="isVideo"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="https://youtube.com/watch?v=... or https://vimeo.com/...">
 <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Supports YouTube and Vimeo URLs.</p>
 </div>

 <!-- Content -->
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
 <span x-text="contentLabel"></span>
 <span x-show="contentRequired" class="text-danger-500">*</span>
 </label>
 <textarea name="content" id="tinymce-add-{{ $module->id }}" rows="6"
 class="tinymce-editor w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
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
 <div class="od-card p-8 text-center">
 <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
 <i class="fas fa-folder-open text-2xl text-gray-400"></i>
 </div>
 <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Modules Yet</h3>
 <p class="text-gray-500 dark:text-gray-400 text-sm">Start building your course by adding your first module above.</p>
 </div>
 @endforelse
</div>

<script src="{{ asset('assets/js/tinymce/tinymce.min.js') }}"></script>
<script>
function lessonForm(initialType) {
    return {
        type: initialType,
        get isVideo() { return this.type === 'Video'; },
        get isReading() { return this.type === 'Reading'; },
        get isQuiz() { return this.type === 'Quiz'; },
        get isAssignment() { return this.type === 'Assignment'; },
        get showVideoUrl() { return this.isVideo; },
        get contentLabel() {
            if (this.isVideo) return 'Supplementary Notes / Description';
            if (this.isReading) return 'Lesson Content';
            if (this.isQuiz) return 'Pre-Quiz Instructions';
            if (this.isAssignment) return 'Assignment Instructions';
            return 'Content';
        },
        get contentRequired() { return this.isReading; },
        init() {
            // Watcher is handled by x-model on the select
        }
    };
}

function toggleEditModule(moduleId) {
 const el = document.getElementById('edit-module-' + moduleId);
 el.classList.toggle('hidden');
}

function toggleEditLesson(lessonId) {
 const el = document.getElementById('edit-lesson-' + lessonId);
 el.classList.toggle('hidden');
 if (!el.classList.contains('hidden')) {
     const editorId = 'tinymce-edit-' + lessonId;
     if (!tinymce.get(editorId)) {
         initTinyMCE('#' + editorId);
     }
 }
}

function toggleAddLesson(moduleId) {
 const el = document.getElementById('add-lesson-' + moduleId);
 el.classList.toggle('hidden');
 if (!el.classList.contains('hidden')) {
     const editorId = 'tinymce-add-' + moduleId;
     if (!tinymce.get(editorId)) {
         initTinyMCE('#' + editorId);
     }
 }
}

function toggleLessonResources(lessonId) {
 const el = document.getElementById('lesson-resources-' + lessonId);
 el.classList.toggle('hidden');
}

function toggleBulkUpload() {
 const el = document.getElementById('bulk-upload-form');
 el.classList.toggle('hidden');
}

function initTinyMCE(selector) {
    const isDark = document.documentElement.classList.contains('dark');
    tinymce.init({
        selector: selector,
        height: 350,
        menubar: false,
        plugins: 'advlist autolink lists link image media table codesample fullscreen wordcount searchreplace code preview',
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table codesample | fullscreen code preview',
        content_style: 'body { font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #374151; } body.dark { color: #d1d5db; }',
        skin: isDark ? 'oxide-dark' : 'oxide',
        content_css: isDark ? 'dark' : 'default',
        images_upload_handler: function (blobInfo, progress) {
            return new Promise(function (resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.withCredentials = true;
                xhr.open('POST', '{{ route('instructor.upload.lesson-image') }}');
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.onload = function() {
                    if (xhr.status !== 200) { reject('HTTP ' + xhr.status); return; }
                    var json = JSON.parse(xhr.responseText);
                    if (!json || typeof json.location !== 'string') { reject('Invalid response'); return; }
                    resolve(json.location);
                };
                xhr.onerror = function() { reject('Upload failed'); };
                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            });
        },
        automatic_uploads: true,
        file_picker_types: 'image',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
    });
}

// Initialize any visible editors on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tinymce-editor').forEach(function(el) {
        if (el.offsetParent !== null) {
            initTinyMCE('#' + el.id);
        }
    });
});
</script>
@endsection
