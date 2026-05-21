@extends('layouts.dashboard')

@section('title','Edit Course - Edutrack LMS')
@section('page_title','Edit Course')

@section('content')
<div class="max-w-3xl mx-auto">
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit: {{ $course->title }}</h3>
 <p class="text-gray-500 dark:text-gray-400">Course editing form will be implemented here.</p>
 <a href="{{ route('admin.courses.index') }}" class="mt-4 inline-flex items-center text-primary-600 hover:underline">
 <i class="fas fa-arrow-left mr-2"></i>Back to Courses
 </a>
 </div>
</div>
@endsection
