@extends('layouts.dashboard')

@section('title', $assignment->title .' - Edutrack LMS')
@section('page_title', $assignment->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
 <!-- Assignment Details -->
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
 <div class="flex items-center justify-between mb-4">
 <div>
 <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $assignment->title }}</h2>
 <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $course->title }}</p>
 </div>
 <div class="text-right">
 @if($assignment->due_date)
 <p class="text-sm {{ $assignment->due_date->isPast() && !$submission ?'text-danger-600' :'text-gray-500' }}">
 Due: {{ $assignment->due_date->format('M d, Y \a\t h:i A') }}
 </p>
 @endif
 <p class="text-sm text-gray-500">Max Points: {{ $assignment->max_points }}</p>
 </div>
 </div>

 @if($assignment->description)
 <div class="prose dark:prose-invert max-w-none mb-4">
 <p class="text-gray-700 dark:text-gray-300">{{ $assignment->description }}</p>
 </div>
 @endif

 @if($assignment->instructions)
 <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
 <h4 class="font-medium text-gray-900 dark:text-white mb-2">Instructions</h4>
 <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $assignment->instructions }}</p>
 </div>
 @endif
 </div>

 <!-- Previous Submission -->
 @if($submission)
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Your Submission</h3>

 <div class="space-y-4">
 <div class="flex items-center gap-4">
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
 {{ $submission->status ==='Graded' ?'bg-success-100 text-success-800' : ($submission->status ==='Late' ?'bg-warning-100 text-warning-800' :'bg-primary-100 text-primary-800') }}">
 {{ $submission->status }}
 </span>
 <span class="text-sm text-gray-500">Submitted {{ $submission->submitted_at->diffForHumans() }}</span>
 </div>

 @if($submission->submission_text)
 <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
 <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $submission->submission_text }}</p>
 </div>
 @endif

 @if($submission->file_url)
 <div>
 <a href="{{ $submission->file_url }}" target="_blank"
 class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 text-sm font-medium">
 <i class="fas fa-file-download mr-2"></i>Download Submission
 </a>
 </div>
 @endif

 @if($submission->status ==='Graded')
 <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
 <div class="flex items-center gap-4 mb-3">
 <span class="text-2xl font-bold text-primary-600">{{ $submission->points_earned }}</span>
 <span class="text-gray-500">/ {{ $assignment->max_points }} points</span>
 </div>
 @if($submission->feedback)
 <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 rounded-lg p-4">
 <h4 class="font-medium text-primary-900 dark:text-primary-300 mb-1">Instructor Feedback</h4>
 <p class="text-sm text-primary-800 dark:text-primary-400">{{ $submission->feedback }}</p>
 </div>
 @endif
 </div>
 @endif
 </div>
 </div>
 @endif

 <!-- Submission Form -->
 @if(!$submission || $submission->status !=='Graded')
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
 {{ $submission ?'Resubmit Assignment' :'Submit Assignment' }}
 </h3>

 <form action="{{ route('student.assignments.submit', [$course, $assignment]) }}" method="POST" enctype="multipart/form-data">
 @csrf

 <div class="mb-4">
 <label for="submission_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Answer</label>
 <textarea name="submission_text" id="submission_text" rows="6"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="Type your answer here...">{{ old('submission_text') }}</textarea>
 @error('submission_text')
 <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
 @enderror
 </div>

 <div class="mb-6">
 <label for="submission_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Attach File (optional)</label>
 <input type="file" name="submission_file" id="submission_file"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <p class="mt-1 text-xs text-gray-500">Max file size: 50MB</p>
 @error('submission_file')
 <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
 @enderror
 </div>

 <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
 {{ $submission ?'Resubmit' :'Submit Assignment' }}
 </button>
 </form>
 </div>
 @endif
</div>
@endsection
