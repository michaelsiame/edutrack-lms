@extends('layouts.dashboard')

@section('title', $enrollment->course->title .' - Edutrack LMS')
@section('page_title', $enrollment->course->title)

@section('content')
<div class="container mx-auto px-4 py-6">
 <div class="mb-6">
 <a href="{{ route('enrollments.index') }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
 <i class="fas fa-arrow-left mr-1"></i> Back to My Courses
 </a>
 </div>

 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
 <!-- Main Content -->
 <div class="lg:col-span-2 space-y-6">
 <!-- Course Header -->
 <div class="bg-white rounded-lg shadow p-6">
 <div class="flex flex-col md:flex-row gap-6">
 @if($enrollment->course->thumbnail_url)
 <img src="{{ $enrollment->course->thumbnail_url }}" alt="{{ $enrollment->course->title }}" class="w-full md:w-48 h-32 object-cover rounded-lg">
 @else
 <div class="w-full md:w-48 h-32 bg-primary-100 rounded-lg flex items-center justify-center">
 <i class="fas fa-book text-primary-600 text-4xl"></i>
 </div>
 @endif
 <div class="flex-1">
 <h1 class="text-2xl font-bold text-gray-900">{{ $enrollment->course->title }}</h1>
 <p class="text-gray-600 mt-1">{{ $enrollment->course->category->name ??'General' }}</p>
 <div class="flex items-center mt-3 space-x-4 text-sm text-gray-500">
 <span><i class="fas fa-chalkboard-teacher mr-1"></i> {{ $enrollment->course->instructor->user->full_name ?? $enrollment->course->instructor->user->username ??'TBA' }}</span>
 <span><i class="fas fa-clock mr-1"></i> {{ $enrollment->course->duration_weeks ??'N/A' }} weeks</span>
 </div>
 <div class="mt-4">
 <div class="flex items-center justify-between text-sm mb-1">
 <span class="text-gray-600">Progress</span>
 <span class="font-semibold text-primary-600">{{ $enrollment->progress ?? 0 }}%</span>
 </div>
 <div class="w-full bg-gray-200 rounded-full h-2.5">
 <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{ $enrollment->progress ?? 0 }}%"></div>
 </div>
 </div>

 @php
 $firstLesson = $enrollment->course->modules->flatMap->lessons->first();
 @endphp
 @if($firstLesson)
 <a href="{{ route('student.learning.show', [$enrollment->course, $firstLesson]) }}"
 class="mt-4 inline-flex items-center px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium transition">
 <i class="fas fa-play-circle mr-2"></i>
 {{ $enrollment->progress > 0 ?'Continue Learning' :'Start Learning' }}
 </a>
 @endif
 </div>
 </div>
 </div>

 <!-- Course Content -->
 <div class="bg-white rounded-lg shadow p-6">
 <h2 class="text-lg font-bold text-gray-900 mb-4">Course Content</h2>
 <div class="space-y-4">
 @forelse($enrollment->course->modules ?? [] as $module)
 <div class="border rounded-lg overflow-hidden">
 <div class="bg-gray-50 px-4 py-3 font-medium text-gray-900">
 {{ $module->title }}
 </div>
 <div class="divide-y divide-gray-100">
 @foreach($module->lessons ?? [] as $lesson)
 <a href="{{ route('student.learning.show', [$enrollment->course, $lesson]) }}" class="flex items-center px-4 py-3 hover:bg-gray-50 transition">
 <div class="mr-3">
 @if($lesson->is_completed ?? false)
 <i class="fas fa-check-circle text-success-500"></i>
 @else
 <i class="far fa-circle text-gray-400"></i>
 @endif
 </div>
 <span class="flex-1 text-gray-700">{{ $lesson->title }}</span>
 @if($lesson->duration_minutes)
 <span class="text-sm text-gray-500">{{ $lesson->duration_minutes }} min</span>
 @endif
 </a>
 @endforeach
 </div>
 </div>
 @empty
 <p class="text-gray-500 text-center py-8">No modules available yet.</p>
 @endforelse
 </div>
 </div>
 </div>

 <!-- Sidebar -->
 <div class="space-y-6">
 <!-- Enrollment Status -->
 <div class="bg-white rounded-lg shadow p-6">
 <h3 class="font-bold text-gray-900 mb-4">Enrollment Details</h3>
 <div class="space-y-3 text-sm">
 <div class="flex justify-between">
 <span class="text-gray-600">Status</span>
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
 {{ $enrollment->enrollment_status ==='completed' ?'bg-success-100 text-success-800' :'bg-primary-100 text-primary-800' }}">
 {{ ucfirst($enrollment->enrollment_status) }}
 </span>
 </div>
 <div class="flex justify-between">
 <span class="text-gray-600">Enrolled On</span>
 <span class="text-gray-900">{{ $enrollment->enrolled_at?->format('M d, Y') }}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-gray-600">Completed On</span>
 <span class="text-gray-900">{{ $enrollment->completion_date?->format('M d, Y') ??'In Progress' }}</span>
 </div>
 </div>
 </div>

 <!-- Course Community -->
 <div class="bg-white rounded-lg shadow p-6">
 <h3 class="font-bold text-gray-900 mb-4">Course Community</h3>
 <div class="space-y-2">
 <a href="{{ route('student.discussions.index', $enrollment->course) }}" class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 border border-gray-200">
 <i class="fas fa-comments w-5 text-primary-600"></i> Discussions
 </a>
 <a href="{{ route('student.live-sessions.index', $enrollment->course) }}" class="flex items-center px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 border border-gray-200">
 <i class="fas fa-video w-5 text-danger-600"></i> Live Sessions
 </a>
 </div>
 </div>

 <!-- Certificate -->
 @if($enrollment->enrollment_status ==='completed')
 <div class="bg-white rounded-lg shadow p-6">
 <h3 class="font-bold text-gray-900 mb-4">Certificate</h3>
 <p class="text-sm text-gray-600 mb-4">Congratulations! You have earned a certificate for this course.</p>
 <a href="{{ route('certificates.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
 <i class="fas fa-certificate mr-2"></i> View Certificate
 </a>
 </div>
 @endif
 </div>
 </div>
</div>
@endsection
