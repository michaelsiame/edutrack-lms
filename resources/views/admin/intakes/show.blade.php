@extends('layouts.dashboard')

@section('title', $intake->name . ' - Edutrack LMS')
@section('page_title', 'Intake Details')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.intakes.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
                <i class="fas fa-arrow-left mr-1"></i>Back to Intakes
            </a>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $intake->name }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('admin.courses.show', $intake->course) }}" class="text-primary-600 hover:text-primary-700">{{ $intake->course?->title }}</a>
                · {{ $intake->course?->instructor?->user?->full_name ?? 'No Instructor' }}
            </p>
        </div>
        @php
            $statusClass = match($intake->status) {
                'open' => 'bg-success-100 text-success-800',
                'closed' => 'bg-gray-100 text-gray-800',
                'in_progress' => 'bg-primary-100 text-primary-800',
                'completed' => 'bg-navy-100 text-navy-800',
                default => 'bg-warning-100 text-warning-800',
            };
        @endphp
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $statusClass }}">
            {{ ucfirst($intake->status) }}
        </span>
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

    <!-- Intake Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="od-card p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Start Date</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $intake->start_date?->format('M d, Y') ?? 'Not set' }}
            </div>
        </div>
        <div class="od-card p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Application Deadline</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $intake->application_deadline?->format('M d, Y') ?? 'Not set' }}
            </div>
        </div>
        <div class="od-card p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Price</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                @if($intake->price_override)
                    ZMW {{ number_format($intake->price_override, 2) }}
                @else
                    ZMW {{ number_format($intake->course?->discount_price ?? $intake->course?->price ?? 0, 2) }}
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="od-card p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Capacity</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $intake->enrollments_count }}
                @if($intake->max_students > 0)
                    <span class="text-gray-400 font-normal">/ {{ $intake->max_students }}</span>
                @else
                    <span class="text-gray-400 font-normal">(unlimited)</span>
                @endif
            </div>
        </div>
        <div class="od-card p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Learning Deadline</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $intake->learning_deadline?->format('M d, Y') ?? 'Not set' }}
            </div>
        </div>
        <div class="od-card p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Display Order</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $intake->display_order }}</div>
        </div>
    </div>

    <!-- Enrolled Students -->
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="od-h3">Enrolled Students ({{ $intake->enrollments->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="od-table min-w-[640px]">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Enrolled</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Payment</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($intake->enrollments as $enrollment)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $enrollment->user?->full_name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $enrollment->user?->email }}</div>
                            </td>
                            <td class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $enrollment->enrolled_at?->format('M d, Y') ?? 'N/A' }}
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ match($enrollment->enrollment_status) {
                                        'Completed' => 'bg-success-100 text-success-800',
                                        'In Progress' => 'bg-primary-100 text-primary-800',
                                        'Dropped' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-warning-100 text-warning-800',
                                    } }}">
                                    {{ $enrollment->enrollment_status }}
                                </span>
                            </td>
                            <td class="text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary-500 rounded-full" style="width: {{ $enrollment->progress }}%"></div>
                                    </div>
                                    <span class="text-xs">{{ round($enrollment->progress) }}%</span>
                                </div>
                            </td>
                            <td class="text-sm">
                                @if($enrollment->isFullyPaid())
                                    <span class="text-success-600 font-medium">Paid</span>
                                @else
                                    <span class="text-warning-600">ZMW {{ number_format($enrollment->amount_paid, 2) }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <form action="{{ route('admin.intakes.transfer-student', $intake) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
                                    <select name="target_intake_id" required class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
                                        <option value="">Transfer to...</option>
                                        @foreach($intake->course->intakes->where('id', '!=', $intake->id)->where('status', '!=', 'completed') as $otherIntake)
                                            <option value="{{ $otherIntake->id }}">{{ $otherIntake->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="text-xs px-2 py-1 bg-primary-600 text-white rounded hover:bg-primary-700">
                                        Move
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="od-empty-sm">No students enrolled yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
