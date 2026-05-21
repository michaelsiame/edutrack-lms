@extends('layouts.dashboard')

@section('title','Enrollments - Admin')
@section('page_title','Enrollments')

@section('content')
<div class="max-w-6xl mx-auto">
 @if(session('success'))
 <div class="mb-4 p-4 bg-success-50 border border-success-200 rounded-lg text-success-700">{{ session('success') }}</div>
 @endif

 <!-- Filters -->
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 mb-6">
 <form action="{{ route('admin.enrollments.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Course</label>
 <select name="course"
 class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 <option value="">All Courses</option>
 @foreach($courses as $course)
 <option value="{{ $course->id }}" {{ request('course') == $course->id ?'selected' :'' }}>{{ $course->title }}</option>
 @endforeach
 </select>
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Status</label>
 <select name="status"
 class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 <option value="">All</option>
 <option value="Enrolled" {{ request('status') ==='Enrolled' ?'selected' :'' }}>Enrolled</option>
 <option value="In Progress" {{ request('status') ==='In Progress' ?'selected' :'' }}>In Progress</option>
 <option value="Completed" {{ request('status') ==='Completed' ?'selected' :'' }}>Completed</option>
 <option value="Dropped" {{ request('status') ==='Dropped' ?'selected' :'' }}>Dropped</option>
 </select>
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Search</label>
 <input type="text" name="search" value="{{ request('search') }}" placeholder="Student name/email"
 class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 </div>
 <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium">Filter</button>
 <a href="{{ route('admin.enrollments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Clear</a>
 </form>
 </div>

 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <table class="w-full text-sm">
 <thead class="bg-gray-50 dark:bg-gray-700/50">
 <tr>
 <th class="px-4 py-3 text-left">Student</th>
 <th class="px-4 py-3 text-left">Course</th>
 <th class="px-4 py-3 text-left">Status</th>
 <th class="px-4 py-3 text-left">Progress</th>
 <th class="px-4 py-3 text-left">Payment</th>
 <th class="px-4 py-3 text-right">Actions</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
 @forelse($enrollments as $enrollment)
 <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
 <td class="px-4 py-3">
 <div class="font-medium text-gray-900 dark:text-white">{{ $enrollment->user?->full_name ??'Unknown' }}</div>
 <div class="text-xs text-gray-500">{{ $enrollment->user?->email ??'-' }}</div>
 </td>
 <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $enrollment->course->title }}</td>
 <td class="px-4 py-3">
 @php
 $statusClass = match($enrollment->enrollment_status) {'Completed' =>'bg-success-100 text-success-800','In Progress' =>'bg-primary-100 text-primary-800','Dropped' =>'bg-danger-100 text-danger-800',
 default =>'bg-gray-100 text-gray-800'
 };
 @endphp
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClass }}">
 </span>
 </td>
 <td class="px-4 py-3">
 <div class="w-24 bg-gray-200 rounded-full h-1.5">
 <div class="bg-primary-600 h-1.5 rounded-full" style="width: {{ $enrollment->progress }}%"></div>
 </div>
 <span class="text-xs text-gray-500">{{ $enrollment->progress }}%</span>
 </td>
 <td class="px-4 py-3">
 <div class="text-xs">
 <span class="{{ $enrollment->isFullyPaid() ?'text-success-600' :'text-warning-600' }}">
 K{{ number_format($enrollment->amount_paid, 2) }}
 </span>
 <span class="text-gray-400">/ K{{ number_format($enrollment->course->discount_price ?? $enrollment->course->price, 2) }}</span>
 </div>
 @if($enrollment->certificate_blocked)
 <span class="text-xs text-danger-500">Cert blocked</span>
 @endif
 </td>
 <td class="px-4 py-3 text-right">
 <button onclick="toggleEditEnrollment({{ $enrollment->id }})" class="text-primary-600 hover:text-primary-700 mr-3">
 <i class="fas fa-edit"></i>
 </button>
 <form action="{{ route('admin.enrollments.destroy', $enrollment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this enrollment?')">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-danger-600 hover:text-danger-700">
 <i class="fas fa-trash"></i>
 </button>
 </form>
 </td>
 </tr>
 <!-- Edit Form -->
 <tr id="edit-enrollment-{{ $enrollment->id }}" class="hidden bg-gray-50 dark:bg-gray-700/30">
 <td colspan="6" class="px-4 py-4">
 <form action="{{ route('admin.enrollments.update', $enrollment) }}" method="POST" class="flex items-end gap-3">
 @csrf
 @method('PUT')
 <div>
 <label class="block text-xs text-gray-500 mb-1">Status</label>
 <select name="enrollment_status" required
 class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 <option value="Enrolled" {{ $enrollment->enrollment_status ==='Enrolled' ?'selected' :'' }}>Enrolled</option>
 <option value="In Progress" {{ $enrollment->enrollment_status ==='In Progress' ?'selected' :'' }}>In Progress</option>
 <option value="Completed" {{ $enrollment->enrollment_status ==='Completed' ?'selected' :'' }}>Completed</option>
 <option value="Dropped" {{ $enrollment->enrollment_status ==='Dropped' ?'selected' :'' }}>Dropped</option>
 <option value="Expired" {{ $enrollment->enrollment_status ==='Expired' ?'selected' :'' }}>Expired</option>
 </select>
 </div>
 <div>
 <label class="block text-xs text-gray-500 mb-1">Progress %</label>
 <input type="number" name="progress" value="{{ $enrollment->progress }}" min="0" max="100" step="0.01"
 class="w-24 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-xs text-gray-500 mb-1">Grade</label>
 <input type="number" name="final_grade" value="{{ $enrollment->final_grade }}" min="0" max="100" step="0.01"
 class="w-24 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 </div>
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300 pb-2">
 <input type="checkbox" name="certificate_blocked" value="1" {{ $enrollment->certificate_blocked ?'checked' :'' }}
 class="w-4 h-4 text-primary-600 mr-2">
 Block Certificate
 </label>
 <button type="submit" class="px-3 py-2 bg-primary-600 text-white text-sm rounded hover:bg-primary-700">Save</button>
 <button type="button" onclick="toggleEditEnrollment({{ $enrollment->id }})" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">Cancel</button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="px-4 py-8 text-center text-gray-500">No enrollments found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
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
