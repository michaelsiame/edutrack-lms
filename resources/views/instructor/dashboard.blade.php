@extends('layouts.dashboard')

@section('title','Instructor Dashboard - Edutrack LMS')
@section('page_title','Instructor Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8 stagger-children">
    <div class="od-stat-card">
        <div>
            <p class="od-stat-label">My Courses</p>
            <p class="od-stat-value od-num">{{ $stats['total_courses'] }}</p>
        </div>
        <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
            <i class="fas fa-book text-lg"></i>
        </div>
    </div>

    <div class="od-stat-card">
        <div>
            <p class="od-stat-label">Total Students</p>
            <p class="od-stat-value od-num">{{ $stats['total_students'] }}</p>
        </div>
        <div class="od-stat-icon" style="background: var(--od-green-soft); color: var(--od-green);">
            <i class="fas fa-users text-lg"></i>
        </div>
    </div>

    <div class="od-stat-card">
        <div>
            <p class="od-stat-label">Rating</p>
            <p class="od-stat-value od-num">{{ number_format($stats['average_rating'], 1) }}<span class="text-sm od-meta font-normal">/5</span></p>
        </div>
        <div class="od-stat-icon" style="background: var(--od-accent-soft); color: var(--od-accent);">
            <i class="fas fa-star text-lg"></i>
        </div>
    </div>
</div>

<!-- Pending Review -->
@if($pendingCount > 0)
<div class="od-card mb-8" style="border-left: 4px solid var(--od-accent);">
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="od-eyebrow" style="margin:0 0 4px;">ACTION REQUIRED</p>
            <h3 class="od-h3">Submissions pending review ({{ $pendingCount }})</h3>
        </div>
        <a href="{{ route('instructor.submissions') }}" class="od-btn od-btn-ghost od-btn-sm">View all</a>
    </div>
    <div class="space-y-3">
        @foreach($pendingAssignments as $submission)
            <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--od-fg-soft);">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold" style="background: var(--od-navy-soft); color: var(--od-navy);">
                        {{ substr($submission->student->user->first_name ?? 'S', 0, 1) }}{{ substr($submission->student->user->last_name ?? '', 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium text-sm truncate" style="color: var(--od-fg);">{{ $submission->student->user->full_name ?? 'Unknown' }}</p>
                        <p class="text-xs" style="color: var(--od-muted);">{{ $submission->assignment->title ?? 'Assignment' }} · {{ $submission->assignment->course->title ?? 'Course' }}</p>
                    </div>
                </div>
                <a href="{{ route('instructor.courses.assignments.grade', [$submission->assignment->course, $submission->assignment, $submission]) }}" class="od-btn od-btn-primary od-btn-sm flex-shrink-0">
                    Grade
                </a>
            </div>
        @endforeach
        @foreach($pendingQuizzes as $attempt)
            <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--od-fg-soft);">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold" style="background: var(--od-accent-soft); color: var(--od-accent);">
                        {{ substr($attempt->student->user->first_name ?? 'S', 0, 1) }}{{ substr($attempt->student->user->last_name ?? '', 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium text-sm truncate" style="color: var(--od-fg);">{{ $attempt->student->user->full_name ?? 'Unknown' }}</p>
                        <p class="text-xs" style="color: var(--od-muted);">{{ $attempt->quiz->title ?? 'Quiz' }} · {{ $attempt->quiz->course->title ?? 'Course' }}</p>
                    </div>
                </div>
                <a href="{{ route('instructor.quizzes.attempts.grade', [$attempt->quiz, $attempt]) }}" class="od-btn od-btn-primary od-btn-sm flex-shrink-0">
                    Grade
                </a>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="mb-8">
    <p class="od-eyebrow mb-3">QUICK ACTIONS</p>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('instructor.courses.create') }}" class="od-btn od-btn-primary od-btn-sm"><i class="fas fa-plus mr-1.5"></i> New Course</a>
        <a href="{{ route('instructor.submissions') }}" class="od-btn od-btn-success od-btn-sm"><i class="fas fa-clipboard-check mr-1.5"></i> Review Submissions</a>
        <a href="{{ route('instructor.progress') }}" class="od-btn od-btn-secondary od-btn-sm"><i class="fas fa-chart-line mr-1.5"></i> Student Progress</a>
        @if($courses->isNotEmpty())
        <a href="{{ route('instructor.live-sessions.index', $courses->first()) }}" class="od-btn od-btn-ghost od-btn-sm"><i class="fas fa-video mr-1.5"></i> Live Sessions</a>
        @endif
    </div>
</div>

<!-- My Courses -->
<div class="od-card" style="padding: 0; overflow: hidden;">
    <div class="od-card-header">
        <h3 class="od-h3"><i class="fas fa-chalkboard-teacher mr-2" style="color: var(--od-navy);"></i>My Courses</h3>
        <a href="{{ route('instructor.courses.create') }}" class="od-btn od-btn-primary od-btn-sm">
            <i class="fas fa-plus mr-1.5"></i> New Course
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="od-table min-w-[640px]">
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
                            <div class="od-icon-box od-icon-box-md" style="background: var(--od-navy-soft); color: var(--od-navy);">
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
                        <span class="od-badge {{ $course->status === 'published' ? 'od-badge-success' : 'od-badge-info' }}">
                            {{ ucfirst($course->status) }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('instructor.courses.edit', $course) }}" class="od-btn od-btn-ghost od-btn-sm" title="Edit" aria-label="Edit course">
                                <i class="fas fa-pen text-sm" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('instructor.courses.show', $course) }}" class="od-btn od-btn-ghost od-btn-sm" title="View" aria-label="View course">
                                <i class="fas fa-eye text-sm" aria-hidden="true"></i>
                            </a>
                            <form action="{{ route('instructor.courses.destroy', $course) }}" method="POST" class="inline" data-confirm="Delete this course?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="od-btn od-btn-ghost od-btn-sm text-danger-600 hover:text-danger-700" title="Delete" aria-label="Delete course">
                                    <i class="fas fa-trash text-sm" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-10">
                        <div class="od-empty-sm">
                            <i class="fas fa-book-open text-3xl"></i>
                            <p class="text-sm">No courses yet. Create your first course to get started!</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Replace native confirm with custom modal for delete actions
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', e => {
        e.preventDefault();
        const message = form.dataset.confirm;
        if (window.confirmModal) {
            window.confirmModal(message, () => form.submit());
        } else if (confirm(message)) {
            form.submit();
        }
    });
});
</script>
@endpush
