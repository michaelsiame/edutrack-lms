@extends('layouts.dashboard')

@section('title','My Courses - Edutrack LMS')
@section('page_title','My Courses')

@section('content')
<div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="od-card-header">
 <h3 class="od-h3">My Courses</h3>
 <div class="flex items-center gap-2">
 <a href="{{ route('instructor.courses.create-from-template') }}" class="od-btn od-btn-secondary od-btn-sm">
 <i class="fas fa-clone mr-1.5"></i> From Template
 </a>
 <a href="{{ route('instructor.courses.create') }}" class="od-btn od-btn-primary od-btn-sm">
 <i class="fas fa-plus mr-1.5"></i> New Course
 </a>
 </div>
 </div>
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
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
 <span class="font-medium" style="color: var(--od-fg);">{{ $course->title }}</span>
 </td>
 <td class="od-meta">{{ $course->enrollments_count }}</td>
 <td>
 @php
 $statusClass = match($course->status) {
 'published' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400',
 'under_review' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400',
 default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
 };
 $statusLabel = $course->status === 'under_review' ? 'Pending Approval' : ucfirst($course->status);
 @endphp
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
 {{ $statusLabel }}
 </span>
 </td>
 <td class="text-right">
 <div class="flex items-center justify-end gap-2">
 <a href="{{ route('instructor.courses.edit', $course) }}" class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
 <i class="fas fa-pen text-sm"></i>
 </a>
 <a href="{{ route('instructor.courses.show', $course) }}" class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
 <i class="fas fa-eye text-sm"></i>
 </a>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="od-empty-sm">No courses yet.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</div>
@endsection
