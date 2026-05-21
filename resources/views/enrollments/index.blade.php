@extends('layouts.dashboard')

@section('title','My Courses - Edutrack LMS')
@section('page_title','My Courses')

@section('content')
<div class="max-w-5xl mx-auto">
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <div class="p-6 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Enrolled Courses</h2>
 </div>

 @if($enrollments->isEmpty())
 <div class="p-12 text-center">
 <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
 <i class="fas fa-book-open text-3xl text-gray-400"></i>
 </div>
 <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Courses Yet</h3>
 <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">You haven't enrolled in any courses yet.</p>
 <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium transition">
 <i class="fas fa-search mr-2"></i>Browse Courses
 </a>
 </div>
 @else
 <div class="divide-y divide-gray-100 dark:divide-gray-700">
 @foreach($enrollments as $enrollment)
 @php $firstLesson = $enrollment->course?->modules?->flatMap->lessons->first(); @endphp
 <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
 <div class="flex-1 min-w-0">
 <h4 class="font-medium text-gray-900 dark:text-white truncate">{{ $enrollment->course?->title ??'Unknown' }}</h4>
 <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
 Enrolled {{ $enrollment->enrolled_at?->format('M d, Y') }}
 </p>
 <div class="flex items-center gap-3 mt-2">
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
 @if($enrollment->enrollment_status ==='Completed') bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300
 @elseif($enrollment->enrollment_status ==='In Progress') bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300
 @elseif($enrollment->enrollment_status ==='Dropped') bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300
 @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
 @endif">
 {{ $enrollment->enrollment_status }}
 </span>
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
 {{ $enrollment->payment_status ==='completed' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300' :'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-300' }}">
 {{ ucfirst($enrollment->payment_status) }}
 </span>
 </div>
 </div>

 <div class="flex items-center gap-4 sm:w-auto">
 <div class="flex-1 sm:w-32">
 <div class="flex items-center justify-between text-xs mb-1">
 <span class="text-gray-500 dark:text-gray-400">Progress</span>
 <span class="font-medium text-gray-700 dark:text-gray-300">{{ number_format($enrollment->progress, 0) }}%</span>
 </div>
 <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
 <div class="bg-primary-600 h-2 rounded-full transition-all duration-500" style="width: {{ $enrollment->progress }}%"></div>
 </div>
 </div>
 <a href="{{ $firstLesson ? route('student.learning.show', [$enrollment->course, $firstLesson]) : route('enrollments.show', $enrollment->course) }}"
 class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 font-medium transition shrink-0">
 {{ $enrollment->progress > 0 ?'Continue' :'Start' }}
 <i class="fas fa-arrow-right ml-2 text-xs"></i>
 </a>
 </div>
 </div>
 </div>
 @endforeach
 </div>

 <div class="p-4 border-t border-gray-100 dark:border-gray-700">
 {{ $enrollments->links() }}
 </div>
 @endif
 </div>
</div>
@endsection
