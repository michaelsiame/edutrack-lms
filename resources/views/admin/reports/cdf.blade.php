@extends('layouts.dashboard')

@section('title','CDF Funding Report - Admin')
@section('page_title','CDF Funding Report')

@section('content')
<div class="max-w-6xl mx-auto">
    <a href="{{ route('admin.reports') }}" class="od-btn od-btn-secondary od-btn-sm mb-4">&larr; Back to Reports</a>

    <!-- Filters -->
    <div class="od-card p-4 mb-6">
        <form action="{{ route('admin.reports.cdf') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="od-form-label">Constituency</label>
                <input type="text" name="constituency" value="{{ request('constituency') }}" class="od-input" placeholder="Search constituency">
            </div>
            <div>
                <label class="od-form-label">Course</label>
                <select name="course" class="od-input">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ request('course') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="od-form-label">Status</label>
                <select name="status" class="od-input">
                    <option value="">All</option>
                    <option value="Enrolled" {{ request('status') === 'Enrolled' ? 'selected' : '' }}>Enrolled</option>
                    <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Dropped" {{ request('status') === 'Dropped' ? 'selected' : '' }}>Dropped</option>
                </select>
            </div>
            <button type="submit" class="od-btn od-btn-primary od-btn-sm">Filter</button>
            <a href="{{ route('admin.reports.cdf') }}" class="od-btn od-btn-secondary od-btn-sm">Clear</a>
            <a href="{{ route('admin.reports.cdf.export') }}?{{ http_build_query(request()->only(['constituency','course','status'])) }}" class="od-btn od-btn-success od-btn-sm">
                <i class="fas fa-download mr-1"></i>CSV
            </a>
        </form>
    </div>

    @forelse($groups as $constituency => $enrollments)
    <div class="od-card mb-6" style="padding: 0; overflow: hidden;">
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100">
                {{ $constituency }}
                <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $enrollments->count() }} enrolments)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="od-table min-w-[640px]">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left" scope="col">Student</th>
                        <th class="px-4 py-3 text-left" scope="col">Course</th>
                        <th class="px-4 py-3 text-left" scope="col">Status</th>
                        <th class="px-4 py-3 text-right" scope="col">Amount Paid</th>
                        <th class="px-4 py-3 text-left" scope="col">Sponsor Ref</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrollments as $enrollment)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium" style="color: var(--od-fg);">{{ $enrollment->user?->full_name ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $enrollment->user?->email ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $enrollment->course?->title ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $enrollment->enrollment_status }}</td>
                        <td class="px-4 py-3 text-right">{{ setting('currency', 'ZMW') }} {{ number_format($enrollment->amount_paid, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $enrollment->sponsor_reference ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="od-card p-8 text-center text-gray-500 dark:text-gray-400">
        No CDF-funded enrollments found.
    </div>
    @endforelse
</div>
@endsection
