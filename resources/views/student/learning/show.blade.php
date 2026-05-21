@extends('layouts.dashboard')

@section('title', $lesson->title .' -' . $course->title)
@section('page_title', $lesson->title)

@section('content')
<div class="max-w-5xl mx-auto">
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
 <!-- Main Content -->
 <div class="lg:col-span-2 space-y-6">
 <!-- Lesson Video/Content -->
 <x-card :padding="false" class="overflow-hidden">
 @if($lesson->video_url)
 <div class="aspect-video bg-black">
 <iframe src="{{ $lesson->video_url }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
 </div>
 @else
 <div class="aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
 <div class="text-center">
 <i class="fas fa-book-open text-4xl text-gray-400 mb-2"></i>
 <p class="text-gray-500">Text-based lesson</p>
 </div>
 </div>
 @endif
 </x-card>

 <!-- Lesson Info -->
 <x-card>
 <div class="flex justify-between items-start mb-4">
 <div>
 <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>
 <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ $lesson->module->course->title }} / {{ $lesson->module->title }}</p>
 </div>
 @if($lesson->duration_minutes)
 <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
 <i class="fas fa-clock mr-1"></i> {{ $lesson->duration_minutes }} min
 </span>
 @endif
 </div>

 <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
 {!! nl2br(e($lesson->content)) !!}
 </div>

 @if($lesson->attachments)
 <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
 <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Attachments</h3>
 <a href="{{ $lesson->attachments }}" target="_blank" class="inline-flex items-center text-primary-600 hover:text-primary-800 font-medium">
 <i class="fas fa-download mr-2"></i> Download Resource
 </a>
 </div>
 @endif

 <div class="mt-4 flex items-center gap-3">
 <a href="{{ route('student.notes.show', [$course, $lesson]) }}"
 class="inline-flex items-center px-4 py-2 bg-warning-50 dark:bg-warning-900/20 text-warning-700 dark:text-warning-300 rounded-xl hover:bg-warning-100 dark:hover:bg-warning-900/30 font-medium text-sm transition">
 <i class="fas fa-sticky-note mr-2"></i>Take Notes
 </a>
 </div>
 </x-card>
 </div>

 <!-- Sidebar -->
 <div class="space-y-6">
 <!-- Progress -->
 <x-card>
 <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Course Progress</h3>
 <x-progress-bar :value="$progress ?? 0" size="md" />
 <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $progress ?? 0 }}% Complete</p>
 </x-card>

 <!-- Module Navigation -->
 <x-card class="overflow-hidden">
 <div class="p-4 border-b border-gray-100 dark:border-gray-700">
 <h3 class="font-medium text-gray-900 dark:text-white">Course Content</h3>
 </div>
 <div class="max-h-96 overflow-y-auto">
 @foreach($modules as $mod)
 <div class="p-4">
 <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ $mod->title }}</h4>
 <div class="space-y-1">
 @foreach($mod->lessons as $l)
 <a href="{{ route('student.learning.show', ['course' => $course,'lesson' => $l]) }}" 
 class="flex items-center px-2 py-1.5 text-sm rounded-lg {{ $l->id === $lesson->id ?'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' :'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
 @if($l->is_completed)
 <i class="fas fa-check-circle text-success-500 mr-2 w-4"></i>
 @elseif($l->video_url)
 <i class="fas fa-play-circle text-gray-400 mr-2 w-4"></i>
 @else
 <i class="fas fa-file-alt text-gray-400 mr-2 w-4"></i>
 @endif
 {{ $l->title }}
 </a>
 @endforeach
 </div>
 </div>
 @endforeach
 </div>
 </x-card>

 <!-- Mark Complete -->
 @if(!$lesson->is_completed)
 <form action="{{ route('student.learning.complete', ['course' => $course,'lesson' => $lesson]) }}" method="POST">
 @csrf
 <button type="submit" class="w-full bg-primary-600 text-white px-4 py-3 rounded-xl hover:bg-primary-700 font-medium transition">
 <i class="fas fa-check mr-2"></i>Mark as Complete
 </button>
 </form>
 @else
 <div class="bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-xl p-4 text-center">
 <i class="fas fa-check-circle text-success-600 text-2xl mb-2"></i>
 <p class="text-success-800 dark:text-success-300 font-medium">Lesson Completed</p>
 </div>
 @endif
 </div>
 </div>
</div>
@endsection
