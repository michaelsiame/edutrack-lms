@extends('layouts.dashboard')

@section('title', 'All Intakes - Edutrack LMS')
@section('page_title', 'All Intakes')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="od-card p-4">
        <form action="{{ route('admin.intakes.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course</label>
                <select name="course_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Courses</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <button type="submit" class="od-btn od-btn-secondary od-btn-sm">
                <i class="fas fa-filter mr-1"></i>Filter
            </button>
            <a href="{{ route('admin.intakes.index') }}" class="od-btn od-btn-ghost od-btn-sm">Clear</a>
        </form>
    </div>

    @if(session('success'))
        <div class="p-4 bg-success-50 border border-success-200 rounded-lg text-success-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-danger-50 border border-danger-200 rounded-lg text-danger-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Intakes Table -->
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="overflow-x-auto">
            <table class="od-table min-w-[900px]">
                <thead>
                    <tr>
                        <th>Intake</th>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Enrolled</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($intakes as $intake)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $intake->name }}</div>
                                @if($intake->is_default)
                                    <span class="text-xs text-gray-500">(Default)</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                <a href="{{ route('admin.courses.show', $intake->course) }}" class="text-primary-600 hover:text-primary-700">
                                    {{ $intake->course?->title ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $intake->course?->instructor?->user?->full_name ?? 'N/A' }}
                            </td>
                            <td class="text-sm text-gray-600 dark:text-gray-400">
                                @if($intake->start_date)
                                    {{ $intake->start_date->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = match($intake->status) {
                                        'open' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400',
                                        'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'in_progress' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400',
                                        'completed' => 'bg-navy-100 text-navy-800',
                                        default => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ ucfirst($intake->status) }}
                                </span>
                            </td>
                            <td class="text-sm">
                                <span class="font-medium">{{ $intake->enrollments_count }}</span>
                                @if($intake->max_students > 0)
                                    <span class="text-gray-400">/ {{ $intake->max_students }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.intakes.show', $intake) }}" class="od-btn od-btn-secondary od-btn-sm">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="od-empty-sm">No intakes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-end">
        {{ $intakes->links() }}
    </div>
</div>
@endsection
