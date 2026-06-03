@extends('layouts.dashboard')

@section('title', $course->title . ' - Intakes - Edutrack LMS')
@section('page_title', 'Intakes: ' . $course->title)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('instructor.courses.show', $course) }}" class="text-sm text-primary-600 hover:text-primary-700">
                <i class="fas fa-arrow-left mr-1"></i>Back to Course
            </a>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">Course Intakes</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage enrollment periods and cohorts for this course.</p>
        </div>
        <a href="{{ route('instructor.courses.intakes.create', $course) }}" class="od-btn od-btn-primary od-btn-sm">
            <i class="fas fa-plus mr-1.5"></i>New Intake
        </a>
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
            <table class="od-table min-w-[768px]">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Students</th>
                        <th>Price</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($intakes as $intake)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $intake->name }}</div>
                                @if($intake->is_default)
                                    <span class="text-xs text-gray-500">(Default / Evergreen)</span>
                                @endif
                            </td>
                            <td class="text-sm text-gray-600 dark:text-gray-400">
                                @if($intake->start_date)
                                    <div>Start: {{ $intake->start_date->format('M d, Y') }}</div>
                                @endif
                                @if($intake->application_deadline)
                                    <div>Deadline: {{ $intake->application_deadline->format('M d, Y') }}</div>
                                @endif
                                @if(!$intake->start_date && !$intake->application_deadline)
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
                                @if($intake->is_full)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-danger-100 text-danger-700 ml-1">FULL</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                <span class="font-medium">{{ $intake->enrollments_count }}</span>
                                @if($intake->max_students > 0)
                                    <span class="text-gray-400">/ {{ $intake->max_students }}</span>
                                @endif
                                @if($intake->spots_remaining !== null)
                                    <div class="text-xs text-success-600">{{ $intake->spots_remaining }} spots left</div>
                                @endif
                            </td>
                            <td class="text-sm text-gray-600 dark:text-gray-400">
                                @if($intake->price_override)
                                    <span class="font-medium">ZMW {{ number_format($intake->price_override, 2) }}</span>
                                    <div class="text-xs text-gray-400">Overrides course price</div>
                                @else
                                    <span class="text-gray-400">Course default</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!$intake->is_default)
                                        <a href="{{ route('instructor.courses.intakes.edit', [$course, $intake]) }}" class="p-1.5 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Edit">
                                            <i class="fas fa-pen text-sm"></i>
                                        </a>
                                        @if($intake->status === 'open')
                                            <form action="{{ route('instructor.courses.intakes.close', [$course, $intake]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-warning-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Close Intake">
                                                    <i class="fas fa-lock text-sm"></i>
                                                </button>
                                            </form>
                                        @elseif($intake->status === 'closed')
                                            <form action="{{ route('instructor.courses.intakes.reopen', [$course, $intake]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-success-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Reopen Intake">
                                                    <i class="fas fa-lock-open text-sm"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('instructor.courses.intakes.destroy', [$course, $intake]) }}" method="POST" class="inline" data-confirm="Delete this intake?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-danger-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Delete">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="od-empty-sm">No intakes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
