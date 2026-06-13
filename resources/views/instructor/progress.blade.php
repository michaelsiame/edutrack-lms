@extends('layouts.dashboard')

@section('title','Class Progress - Edutrack LMS')
@section('page_title','Class Progress')

@section('content')
<div class="space-y-6">
    @forelse($courses as $course)
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="od-card-header">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $course->title }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $course->enrollments->count() }} student{{ $course->enrollments->count() !== 1 ? 's' : '' }} enrolled</p>
            </div>
            <a href="{{ route('instructor.courses.show', $course) }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                <i class="fas fa-arrow-right mr-1"></i>Manage Course
            </a>
        </div>

        @if($course->enrollments->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="od-table min-w-[640px]">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Enrolled</th>
                        <th>Progress</th>
                        <th class="text-right">Lessons</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($course->enrollments as $enrollment)
                    @php
                        $totalLessons = $totalLessonsPerCourse[$course->id] ?? 0;
                        $completedLessons = $lessonProgress[$enrollment->id] ?? 0;
                        $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                    {{ substr($enrollment->student->user->first_name ?? 'S', 0, 1) }}{{ substr($enrollment->student->user->last_name ?? '', 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $enrollment->student->user->full_name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="od-meta">{{ $enrollment->created_at?->diffForHumans() ?? 'N/A' }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-2.5 w-24">
                                    <div class="bg-primary-500 h-2.5 rounded-full transition-all" style="width: {{ $progressPercent }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 w-8">{{ $progressPercent }}%</span>
                            </div>
                        </td>
                        <td class="text-right text-sm text-gray-600 dark:text-gray-400">
                            {{ $completedLessons }} / {{ $totalLessons }}
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                @php $status = $enrollment->status ?? 'active'; @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $status === 'completed' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :
                                       ($status === 'dropped' ? 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400' :
                                       'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400') }}">
                                    {{ ucfirst($status) }}
                                </span>
                                @if($enrollment->mode !== 'online')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400">
                                    {{ $enrollment->modeLabel() }}
                                </span>
                                @endif
                                @if($enrollment->isInPerson() && !$enrollment->certificate_issued && !$enrollment->certificate_blocked)
                                <form action="{{ route('instructor.courses.enrollments.complete', [$course, $enrollment]) }}" method="POST" class="inline" data-confirm="Mark this in-person student complete and issue their certificate?">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 bg-success-600 hover:bg-success-700 text-white text-xs font-medium rounded transition-colors">
                                        <i class="fas fa-check-circle mr-1"></i>Mark Complete &amp; Issue Cert
                                    </button>
                                </form>
                                @elseif(!$enrollment->isInPerson() && !$enrollment->certificate_issued && $progressPercent >= 80 && !$enrollment->certificate_blocked)
                                <form action="{{ route('instructor.courses.enrollments.issue-certificate', [$course, $enrollment]) }}" method="POST" class="inline" data-confirm="Issue certificate for this student?">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 bg-secondary-600 hover:bg-secondary-700 text-white text-xs font-medium rounded transition-colors">
                                        <i class="fas fa-certificate mr-1"></i>Issue Cert
                                    </button>
                                </form>
                                @elseif($enrollment->certificate_issued)
                                <span class="text-xs text-success-600 dark:text-success-400"><i class="fas fa-check mr-1"></i>Cert Issued</span>
                                @elseif($enrollment->certificate_blocked)
                                <span class="text-xs text-danger-600 dark:text-danger-400" title="Payment pending"><i class="fas fa-lock mr-1"></i>Blocked</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <i class="fas fa-user-graduate text-2xl mb-2 text-gray-300 dark:text-gray-600"></i>
            <p class="text-sm">No students enrolled yet.</p>
        </div>
        @endif
    </div>
    @empty
    <div class="od-card p-8 text-center">
        <div class="w-20 h-20 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-user-graduate text-3xl text-primary-600 dark:text-primary-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Class Progress</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
            Create a course and enroll students to see their progress here.
        </p>
        <a href="{{ route('instructor.courses.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
            <i class="fas fa-plus mr-2"></i>Create Course
        </a>
    </div>
    @endforelse
</div>
@endsection
