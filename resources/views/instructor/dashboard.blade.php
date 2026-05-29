@extends('layouts.dashboard')

@section('title','Instructor Dashboard - Edutrack LMS')
@section('page_title','Instructor Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
 <div class="od-stat-card">
 <div>
 <p class="od-stat-label">My Courses</p>
 <p class="od-stat-value">{{ $stats['total_courses'] }}</p>
 </div>
 <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-book text-lg"></i>
 </div>
 </div>

 <div class="od-stat-card">
 <div>
 <p class="od-stat-label">Total Students</p>
 <p class="od-stat-value">{{ $stats['total_students'] }}</p>
 </div>
 <div class="od-stat-icon" style="background: var(--od-green-soft); color: var(--od-green);">
 <i class="fas fa-users text-lg"></i>
 </div>
 </div>

 <div class="od-stat-card">
 <div>
 <p class="od-stat-label">Rating</p>
 <p class="od-stat-value">{{ number_format($stats['average_rating'], 1) }}<span class="text-sm od-meta font-normal">/5</span></p>
 </div>
 <div class="od-stat-icon" style="background: color-mix(in oklch, var(--od-accent) 12%, transparent); color: var(--od-accent);">
 <i class="fas fa-star text-lg"></i>
 </div>
 </div>
</div>

<!-- My Courses -->
<div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="px-6 py-4 flex items-center justify-between" style="border-bottom: 1px solid var(--od-border);">
 <h3 class="od-h3"><i class="fas fa-chalkboard-teacher mr-2" style="color: var(--od-navy);"></i>My Courses</h3>
 <a href="{{ route('instructor.courses.create') }}" class="od-btn od-btn-primary od-btn-sm">
 <i class="fas fa-plus mr-1.5"></i> New Course
 </a>
 </div>
 <div class="overflow-x-auto">
 <table class="dashboard-table od-table">
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
 <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-laptop-code"></i>
 </div>
 <span class="font-medium" style="color: var(--od-fg);">{{ $course->title }}</span>
 </div>
 </td>
 <td>
 <span class="inline-flex items-center gap-1 text-sm od-meta">
 <i class="fas fa-users text-xs"></i> {{ $course->enrollments_count }}
 </span>
 </td>
 <td>
 <span class="od-badge {{ $course->status ==='published' ?'od-badge-success' :'od-badge-info' }}">
 {{ ucfirst($course->status) }}
 </span>
 </td>
 <td class="text-right">
 <div class="flex items-center justify-end gap-2">
 <a href="{{ route('instructor.courses.edit', $course) }}" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] rounded-lg transition-colors hover:bg-gray-100" style="color: var(--od-muted);" title="Edit" aria-label="Edit course">
 <i class="fas fa-pen text-sm" aria-hidden="true"></i>
 </a>
 <a href="{{ route('instructor.courses.show', $course) }}" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] rounded-lg transition-colors hover:bg-gray-100" style="color: var(--od-muted);" title="View" aria-label="View course">
 <i class="fas fa-eye text-sm" aria-hidden="true"></i>
 </a>
 <form action="{{ route('instructor.courses.destroy', $course) }}" method="POST" class="inline" onsubmit="return confirm('Delete this course?');">
 @csrf
 @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] rounded-lg transition-colors hover:bg-gray-100" style="color: var(--od-muted);" title="Delete" aria-label="Delete course">
 <i class="fas fa-trash text-sm" aria-hidden="true"></i>
 </button>
 </form>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="text-center py-10 od-meta">
 <i class="fas fa-book-open text-3xl mb-3" style="color: var(--od-border);"></i>
 <p class="text-sm">No courses yet. Create your first course to get started!</p>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</div>
@endsection
