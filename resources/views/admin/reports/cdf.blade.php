@extends('layouts.dashboard')

@section('title','CDF Funding Report - Admin')
@section('page_title','CDF Funding Report')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <a href="{{ route('admin.reports') }}" class="od-btn od-btn-secondary od-btn-sm">&larr; Back to Reports</a>
        <a href="{{ route('admin.cdf-disbursements.create') }}" class="od-btn od-btn-primary od-btn-sm">
            <i class="fas fa-plus mr-1"></i>Record a Disbursement
        </a>
    </div>

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

    <!-- Reconciliation Summary -->
    @if($reconciliation->isNotEmpty())
    <div class="od-card mb-6" style="padding: 0; overflow: hidden;">
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100">Constituency Reconciliation</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="od-table min-w-[640px]">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left" scope="col">Constituency</th>
                        <th class="px-4 py-3 text-right" scope="col">Students</th>
                        <th class="px-4 py-3 text-right" scope="col">Expected</th>
                        <th class="px-4 py-3 text-right" scope="col">Received</th>
                        <th class="px-4 py-3 text-right" scope="col">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reconciliation as $summary)
                    <tr>
                        <td class="px-4 py-3 font-medium" style="color: var(--od-fg);">{{ $summary['constituency'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $summary['students'] }}</td>
                        <td class="px-4 py-3 text-right">{{ setting('currency', 'ZMW') }} {{ number_format($summary['expected'], 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ setting('currency', 'ZMW') }} {{ number_format($summary['received'], 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $summary['outstanding'] > 0 ? 'text-danger-600' : 'text-success-600' }}">{{ setting('currency', 'ZMW') }} {{ number_format($summary['outstanding'], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 dark:bg-gray-700/30 font-semibold">
                        <td class="px-4 py-3" style="color: var(--od-fg);">Total</td>
                        <td class="px-4 py-3 text-right">{{ $reconciliationTotals['students'] }}</td>
                        <td class="px-4 py-3 text-right">{{ setting('currency', 'ZMW') }} {{ number_format($reconciliationTotals['expected'], 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ setting('currency', 'ZMW') }} {{ number_format($reconciliationTotals['received'], 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $reconciliationTotals['outstanding'] > 0 ? 'text-danger-600' : 'text-success-600' }}">{{ setting('currency', 'ZMW') }} {{ number_format($reconciliationTotals['outstanding'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @forelse($groups as $constituency => $enrollments)
    <div class="od-card mb-6" style="padding: 0; overflow: hidden;">
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100">
                {{ $constituency }}
                <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $enrollments->count() }} enrolments)</span>
            </h3>
            <a href="{{ route('admin.cdf-disbursements.create', ['constituency' => $constituency]) }}" class="od-btn od-btn-secondary od-btn-sm">Record Disbursement</a>
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
