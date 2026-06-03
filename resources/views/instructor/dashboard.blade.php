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
