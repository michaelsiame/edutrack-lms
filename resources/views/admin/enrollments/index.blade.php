@extends('layouts.dashboard')

@section('title','Enrollments - Admin')
@section('page_title','Enrollments')

@section('content')
<div class="max-w-6xl mx-auto">
 @if(session('success'))
 <div class="mb-4 p-4 od-toast-success">{{ session('success') }}</div>
 @endif
 @if(session('error'))
 <div class="mb-4 p-4 od-toast-danger">{{ session('error') }}</div>
 @endif

 <div class="flex justify-end mb-4">
 <a href="{{ route('admin.enrollments.create') }}" class="od-btn od-btn-primary od-btn-sm">
 <i class="fas fa-user-plus mr-1"></i> Enrol a Student
 </a>
 </div>

 <!-- Filters -->
 <div class="od-card p-4 mb-6">
 <form action="{{ route('admin.enrollments.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
 <div>
 <label class="od-form-label">Course</label>
 <select name="course"
 class="od-input">
 <option value="">All Courses</option>
 @foreach($courses as $course)
 <option value="{{ $course->id }}" {{ request('course') == $course->id ?'selected' :'' }}>{{ $course->title }}</option>
 @endforeach
 </select>
 </div>
 <div>
 <label class="od-form-label">Status</label>
 <select name="status"
 class="od-input">
 <option value="">All</option>
 <option value="Enrolled" {{ request('status') ==='Enrolled' ?'selected' :'' }}>Enrolled</option>
 <option value="In Progress" {{ request('status') ==='In Progress' ?'selected' :'' }}>In Progress</option>
 <option value="Completed" {{ request('status') ==='Completed' ?'selected' :'' }}>Completed</option>
 <option value="Dropped" {{ request('status') ==='Dropped' ?'selected' :'' }}>Dropped</option>
 </select>
 </div>
 <div>
 <label class="od-form-label">From</label>
 <input type="date" name="from" value="{{ request('from') }}"
 class="od-input">
 </div>
 <div>
 <label class="od-form-label">To</label>
 <input type="date" name="to" value="{{ request('to') }}"
 class="od-input">
 </div>
 <div>
 <label class="od-form-label">Search</label>
 <input type="text" name="search" value="{{ request('search') }}" placeholder="Student name/email"
 class="od-input">
 </div>
 <button type="submit" class="od-btn od-btn-primary od-btn-sm">Filter</button>
 <a href="{{ route('admin.enrollments.index') }}" class="od-btn od-btn-secondary od-btn-sm">Clear</a>
 <a href="{{ route('admin.reports.export', 'enrollments') }}?{{ http_build_query(request()->only(['from','to','status','course'])) }}" class="od-btn od-btn-success od-btn-sm">
 <i class="fas fa-download mr-1"></i>CSV
 </a>
 </form>
 </div>

 <div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
 <thead >
 <tr>
 <th class="px-4 py-3 text-left" scope="col">Student</th>
 <th class="px-4 py-3 text-left" scope="col">Course</th>
 <th class="px-4 py-3 text-left" scope="col">Status</th>
 <th class="px-4 py-3 text-left" scope="col">Progress</th>
 <th class="px-4 py-3 text-left" scope="col">Payment</th>
 <th class="px-4 py-3 text-right" scope="col">Actions</th>
 </tr>
 </thead>
 <tbody >
 @forelse($enrollments as $enrollment)
 <tr >
 <td class="px-4 py-3">
 <div class="font-medium" style="color: var(--od-fg);">{{ $enrollment->user?->full_name ??'Unknown' }}</div>
 <div class="text-xs text-gray-500">{{ $enrollment->user?->email ??'-' }}</div>
 </td>
 <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $enrollment->course->title }}</td>
 <td class="px-4 py-3">
 @php
 $statusClass = match($enrollment->enrollment_status) {
 'Completed' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400',
 'In Progress' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400',
 'Dropped' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400',
 default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
 };
 $modeClass = match($enrollment->mode) {
 'in_person' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400',
 'hybrid' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400',
 default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
 };
 @endphp
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClass }}">
 {{ $enrollment->enrollment_status }}
 </span>
 @if($enrollment->mode !== 'online')
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $modeClass }} ml-1">
 {{ $enrollment->modeLabel() }}
 </span>
 @endif
 </td>
 <td class="px-4 py-3">
 <div class="w-24 bg-gray-200 rounded-full h-1.5">
 <div class="bg-primary-600 h-1.5 rounded-full" style="width: {{ $enrollment->progress }}%"></div>
 </div>
 <span class="text-xs text-gray-500">{{ $enrollment->progress }}%</span>
 </td>
 <td class="px-4 py-3">
 <div class="text-xs">
 <span class="{{ $enrollment->isFullyPaid() ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400' }}">
 {{ setting('currency', 'ZMW') }} {{ number_format($enrollment->amount_paid, 2) }}
 </span>
 <span class="text-gray-400 dark:text-gray-500">/ {{ setting('currency', 'ZMW') }} {{ number_format($enrollment->course->discount_price ?? $enrollment->course->price, 2) }}</span>
 </div>
 @if($enrollment->certificate_blocked)
 <span class="text-xs text-danger-500">Cert blocked</span>
 @endif
 </td>
 <td class="px-4 py-3 text-right">
 <button onclick="toggleEditEnrollment({{ $enrollment->id }})" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg mr-1" aria-label="Edit enrollment">
 <i class="fas fa-edit" aria-hidden="true"></i>
 </button>
 <form action="{{ route('admin.enrollments.destroy', $enrollment) }}" method="POST" class="inline" data-confirm="Delete this enrollment">
 @csrf
 @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-danger-600 hover:text-danger-700 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg" aria-label="Delete enrollment">
 <i class="fas fa-trash" aria-hidden="true"></i>
 </button>
 </form>
 </td>
 </tr>
 <!-- Edit Form -->
 <tr id="edit-enrollment-{{ $enrollment->id }}" class="hidden bg-gray-50 dark:bg-gray-700/30">
 <td colspan="6" class="px-4 py-4">
 <form action="{{ route('admin.enrollments.update', $enrollment) }}" method="POST" class="flex flex-wrap items-end gap-3">
 @csrf
 @method('PUT')
 <div>
 <label class="od-form-label">Status</label>
 <select name="enrollment_status" required
 class="od-input">
 <option value="Enrolled" {{ $enrollment->enrollment_status ==='Enrolled' ?'selected' :'' }}>Enrolled</option>
 <option value="In Progress" {{ $enrollment->enrollment_status ==='In Progress' ?'selected' :'' }}>In Progress</option>
 <option value="Completed" {{ $enrollment->enrollment_status ==='Completed' ?'selected' :'' }}>Completed</option>
 <option value="Dropped" {{ $enrollment->enrollment_status ==='Dropped' ?'selected' :'' }}>Dropped</option>
 <option value="Expired" {{ $enrollment->enrollment_status ==='Expired' ?'selected' :'' }}>Expired</option>
 </select>
 </div>
 <div>
 <label class="od-form-label">Mode</label>
 <select name="mode" required class="od-input">
 <option value="online" {{ $enrollment->mode ==='online' ?'selected' :'' }}>Online</option>
 <option value="in_person" {{ $enrollment->mode ==='in_person' ?'selected' :'' }}>In-Person</option>
 <option value="hybrid" {{ $enrollment->mode ==='hybrid' ?'selected' :'' }}>Hybrid</option>
 </select>
 </div>
 <div>
 <label class="od-form-label">Progress %</label>
 <input type="number" name="progress" value="{{ $enrollment->progress }}" min="0" max="100" step="0.01"
 class="od-input w-24">
 </div>
 <div>
 <label class="od-form-label">Grade</label>
 <input type="number" name="final_grade" value="{{ $enrollment->final_grade }}" min="0" max="100" step="0.01"
 class="od-input w-24">
 </div>
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300 pb-2">
 <input type="checkbox" name="certificate_blocked" value="1" {{ $enrollment->certificate_blocked ?'checked' :'' }}
 class="w-4 h-4 text-primary-600 mr-2 accent-primary-600">
 Block Certificate
 </label>
 <button type="submit" class="od-btn od-btn-primary od-btn-sm">Save</button>
 <button type="button" onclick="toggleEditEnrollment({{ $enrollment->id }})" class="od-btn od-btn-secondary od-btn-sm">Cancel</button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="od-empty-sm">No enrollments found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 <div class="mt-4">
 {{ $enrollments->links() }}
 </div>
</div>

<script>
function toggleEditEnrollment(id) {
 document.getElementById('edit-enrollment-' + id).classList.toggle('hidden');
}
</script>
@endsection
