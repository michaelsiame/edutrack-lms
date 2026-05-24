@extends('layouts.dashboard')

@section('title','Instructor Dashboard - Edutrack LMS')
@section('page_title','Instructor Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
 <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 dark:text-gray-400">My Courses</p>
 <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_courses'] }}</p>
 </div>
 <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
 <i class="fas fa-book text-primary-600 dark:text-primary-400 text-lg"></i>
 </div>
 </div>
 </div>

 <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Students</p>
 <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_students'] }}</p>
 </div>
 <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
 <i class="fas fa-users text-success-600 dark:text-success-400 text-lg"></i>
 </div>
 </div>
 </div>

 <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rating</p>
 <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['average_rating'], 1) }}<span class="text-sm text-gray-400 font-normal">/5</span></p>
 </div>
 <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-xl flex items-center justify-center">
 <i class="fas fa-star text-warning-600 dark:text-warning-400 text-lg"></i>
 </div>
 </div>
 </div>
</div>

<!-- My Courses -->
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
 <h3 class="text-base font-semibold text-gray-800 dark:text-white">
 <i class="fas fa-chalkboard-teacher text-primary-500 mr-2"></i>My Courses
 </h3>
 <a href="{{ route('instructor.courses.create') }}" class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
 <i class="fas fa-plus mr-1.5"></i> New Course
 </a>
 </div>
 <div class="overflow-x-auto">
 <table class="dashboard-table">
 <thead>
 <tr>
 <th>Course</th>
 <th>Students</th>
 <th>Status</th>
 <th class="text-right">Actions</th>
 </tr>
 </thead>
 <tbody>
 @forelse($courses as $course)
 <tr>
 <td>
 <div class="flex items-center gap-3">
 <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
 <i class="fas fa-laptop-code"></i>
 </div>
 <span class="font-medium text-gray-900 dark:text-white">{{ $course->title }}</span>
 </div>
 </td>
 <td>
 <span class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
 <i class="fas fa-users text-xs"></i> {{ $course->enrollments_count }}
 </span>
 </td>
 <td>
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $course->status ==='published' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
 {{ ucfirst($course->status) }}
 </span>
 </td>
 <td class="text-right">
 <div class="flex items-center justify-end gap-2">
 <a href="{{ route('instructor.courses.edit', $course) }}" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Edit" aria-label="Edit course">
 <i class="fas fa-pen text-sm" aria-hidden="true"></i>
 </a>
 <a href="{{ route('instructor.courses.show', $course) }}" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="View" aria-label="View course">
 <i class="fas fa-eye text-sm" aria-hidden="true"></i>
 </a>
 <form action="{{ route('instructor.courses.destroy', $course) }}" method="POST" class="inline" onsubmit="return confirm('Delete this course?');">
 @csrf
 @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-gray-400 hover:text-danger-600 dark:hover:text-danger-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Delete" aria-label="Delete course">
 <i class="fas fa-trash text-sm" aria-hidden="true"></i>
 </button>
 </form>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="text-center py-10 text-gray-500 dark:text-gray-400">
 <i class="fas fa-book-open text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
 <p class="text-sm">No courses yet. Create your first course to get started!</p>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</div>
@endsection
