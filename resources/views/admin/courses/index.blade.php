@extends('layouts.dashboard')

@section('title','Courses - Edutrack LMS')
@section('page_title','Course Management')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
 <h3 class="text-base font-semibold text-gray-800 dark:text-white">All Courses</h3>
 <span class="text-sm text-gray-500 dark:text-gray-400">{{ $courses->total() }} total</span>
 </div>
 <div class="overflow-x-auto">
 <table class="dashboard-table">
 <thead>
 <tr>
 <th>Course</th>
 <th>Category</th>
 <th>Instructor</th>
 <th>Status</th>
 <th>Price</th>
 </tr>
 </thead>
 <tbody>
 @forelse($courses as $course)
 <tr>
 <td>
 <span class="font-medium text-gray-900 dark:text-white">{{ $course->title }}</span>
 </td>
 <td class="text-gray-600 dark:text-gray-400">{{ $course->category?->name ??'N/A' }}</td>
 <td class="text-gray-600 dark:text-gray-400">{{ $course->instructor?->user?->name ??'N/A' }}</td>
 <td>
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $course->status ==='published' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
 {{ ucfirst($course->status) }}
 </span>
 </td>
 <td class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($course->price, 2) }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="text-center py-10 text-gray-500 dark:text-gray-400">No courses found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
 {{ $courses->links() }}
 </div>
</div>
@endsection
