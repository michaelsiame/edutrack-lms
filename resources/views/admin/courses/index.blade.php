@extends('layouts.dashboard')

@section('title','Courses - Edutrack LMS')
@section('page_title','Course Management')

@section('content')
<div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="od-card-header">
 <h3 class="od-h3">All Courses</h3>
 <span class="od-meta">{{ $courses->total() }} total</span>
 </div>
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
 <thead>
 <tr>
 <th>Course</th>
 <th>Category</th>
 <th>Instructor</th>
 <th>Status</th>
 <th>Price</th>
 <th class="text-right">Actions</th>
 </tr>
 </thead>
 <tbody>
 @forelse($courses as $course)
 <tr>
 <td>
 <span class="font-medium" style="color: var(--od-fg);">{{ $course->title }}</span>
 </td>
 <td class="od-meta">{{ $course->category?->name ??'N/A' }}</td>
 <td class="od-meta">{{ $course->instructor?->user?->name ??'N/A' }}</td>
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
 <td class="font-medium" style="color: var(--od-fg);">{{ setting('currency', 'ZMW') }} {{ number_format($course->price, 2) }}</td>
 <td class="text-right">
 @if($course->status === 'under_review')
 <form action="{{ route('admin.courses.approve', $course) }}" method="POST" class="inline">
 @csrf
 @method('PATCH')
 <button type="submit" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] text-success-600 hover:bg-success-50 dark:hover:bg-success-900/20 rounded-lg mr-1" aria-label="Approve course" title="Approve">
 <i class="fas fa-check text-sm"></i>
 </button>
 </form>
 <form action="{{ route('admin.courses.reject', $course) }}" method="POST" class="inline">
 @csrf
 @method('PATCH')
 <button type="submit" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg mr-1" aria-label="Reject course" title="Reject">
 <i class="fas fa-times text-sm"></i>
 </button>
 </form>
 @endif
 <a href="{{ route('admin.courses.show', $course) }}" class="od-btn od-btn-ghost od-btn-sm" aria-label="View course">
 <i class="fas fa-eye text-sm"></i>
 </a>
 <a href="{{ route('admin.courses.edit', $course) }}" class="od-btn od-btn-ghost od-btn-sm" aria-label="Edit course">
 <i class="fas fa-edit text-sm"></i>
 </a>
 <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="inline" data-confirm="Delete this course">
 @csrf
 @method('DELETE')
 <button type="submit" class="od-btn od-btn-ghost od-btn-sm text-danger-600 hover:text-danger-700" aria-label="Delete course">
 <i class="fas fa-trash text-sm"></i>
 </button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="od-empty-sm">No courses found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="od-card-header" style="border-top: 1px solid var(--od-border); border-bottom: none;">
 {{ $courses->links() }}
 </div>
</div>
@endsection
