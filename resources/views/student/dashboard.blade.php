@extends('layouts.dashboard')

@section('title','Student Dashboard - Edutrack LMS')
@section('page_title','My Learning')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page min-h-full">
    <!-- Topbar -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="od-eyebrow">STUDENT PORTAL</p>
            <h1 class="od-h1">Welcome back, {{ auth()->user()->first_name ?? 'Student' }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @if(Route::has('student.notifications'))
            <a href="{{ route('student.notifications') }}" class="od-btn od-btn-ghost od-btn-sm" title="Notifications">
                <i class="fas fa-bell"></i>
            </a>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 stagger-children">
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">{{ $enrollments->count() }}</div>
                <div class="od-stat-label">Enrolled courses</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
                <i class="fas fa-book-open text-sm"></i>
            </div>
        </div>
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">{{ $enrollments->where('progress', 100)->count() }}</div>
                <div class="od-stat-label">Completed</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-green-soft); color: var(--od-green);">
                <i class="fas fa-check-circle text-sm"></i>
            </div>
        </div>
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">{{ $enrollments->where('progress', '<', 100)->where('enrollment_status', '!=', 'Dropped')->count() }}</div>
                <div class="od-stat-label">In progress</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-accent-soft); color: var(--od-accent);">
                <i class="fas fa-chart-line text-sm"></i>
            </div>
        </div>
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">{{ $certificates->count() }}</div>
                <div class="od-stat-label">Certificates</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
                <i class="fas fa-certificate text-sm"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Current Courses -->
        <div class="od-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="od-h3">Current courses</h3>
                <a href="{{ route('enrollments.index') }}" class="od-btn od-btn-ghost od-btn-sm">View all</a>
            </div>
            <div>
                @forelse($enrollments->where('progress', '<', 100)->where('enrollment_status', '!=', 'Dropped')->take(5) as $enrollment)
                    @php
                        $course = $enrollment->course;
                        $lastLesson = null;
                        if ($course && $course->modules) {
                            $allLessons = $course->modules->flatMap->lessons->sortBy('display_order');
                            $lastLesson = $allLessons->first();
                        }
                        $progressColor = $enrollment->progress >= 75 ? 'green' : ($enrollment->progress >= 40 ? 'navy' : 'accent');
                    @endphp
                    <div class="od-course-row">
                        <div class="od-course-thumb">
                            @if($course?->thumbnail_image_url)
                                <img src="{{ $course->thumbnail_image_url }}" alt="" />
                            @else
                                <i class="fas fa-book text-sm" style="color: var(--od-muted);"></i>
                            @endif
                        </div>
                        <div class="od-course-info">
                            <h4>{{ $course?->title ?? 'Unknown Course' }}</h4>
                            <p>
                                @if($enrollment->intake && !$enrollment->intake->is_default)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-primary-100 text-primary-700 mr-1">{{ $enrollment->intake->name }}</span>
                                @endif
                                @if($lastLesson)
                                    Next: {{ $lastLesson->title }}
                                @else
                                    {{ $course?->modules?->count() ?? 0 }} modules
                                @endif
                            </p>
                            @if($enrollment->intake?->learning_deadline)
                                @php
                                    $daysLeft = now()->diffInDays($enrollment->intake->learning_deadline, false);
                                @endphp
                                <p class="text-xs {{ $daysLeft <= 7 ? 'text-danger-600' : 'text-gray-500' }}">
                                    <i class="fas fa-clock mr-1"></i>
                                    @if($daysLeft > 0)
                                        {{ $daysLeft }} days left to complete
                                    @elseif($daysLeft === 0)
                                        Deadline today
                                    @else
                                        Deadline passed
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="od-course-progress">
                            <span class="od-num">{{ round($enrollment->progress) }}% complete</span>
                            <div class="od-progress-track">
                                <div class="od-progress-fill {{ $progressColor }}" style="width: {{ $enrollment->progress }}%;"></div>
                            </div>
                        </div>
                        <div class="od-course-action">
                            @if($lastLesson)
                                <a href="{{ route('student.learning.show', [$course, $lastLesson]) }}" class="od-btn od-btn-primary od-btn-sm">
                                    Continue
                                </a>
                            @else
                                <a href="{{ route('enrollments.show', $course) }}" class="od-btn od-btn-primary od-btn-sm">
                                    Open
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="od-empty">
                        <div class="od-empty-icon">
                            <i class="fas fa-book-open text-xl"></i>
                        </div>
                        <h4>No courses yet</h4>
                        <p>Enroll in a course to start your learning journey.</p>
                        <a href="{{ route('courses.index') }}" class="od-btn od-btn-primary">Browse Courses</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming & Deadlines -->
        <div class="od-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="od-h3">Upcoming deadlines</h3>
                @if(Route::has('student.schedule'))
                    <a href="{{ route('student.schedule') }}" class="od-btn od-btn-ghost od-btn-sm">Calendar</a>
                @endif
            </div>
            @php
                $deadlines = [];
                foreach ($enrollments as $enrollment) {
                    $course = $enrollment->course;
                    if (!$course) continue;
                    foreach ($course->assignments ?? [] as $assignment) {
                        if ($assignment->due_date && $assignment->due_date->isFuture()) {
                            $deadlines[] = [
                                'title' => $assignment->title,
                                'course' => $course->title,
                                'due' => $assignment->due_date,
                                'type' => 'assignment',
                            ];
                        }
                    }
                }
                usort($deadlines, fn($a, $b) => $a['due']->timestamp <=> $b['due']->timestamp);
                $deadlines = array_slice($deadlines, 0, 5);
            @endphp
            @if(count($deadlines) > 0)
                <table class="od-table">
                    <thead>
                        <tr><th>Task</th><th>Course</th><th class="num-col">Due</th></tr>
                    </thead>
                    <tbody>
                        @foreach($deadlines as $task)
                            @php
                                $daysUntil = now()->diffInDays($task['due'], false);
                                $badgeClass = $daysUntil <= 2 ? 'od-badge-warn' : 'od-badge-info';
                                $badgeText = $daysUntil <= 0 ? 'Today' : ($daysUntil == 1 ? '1 day' : $daysUntil . ' days');
                            @endphp
                            <tr>
                                <td><strong>{{ $task['title'] }}</strong></td>
                                <td>{{ $task['course'] }}</td>
                                <td class="num-col"><span class="od-badge {{ $badgeClass }}">{{ $badgeText }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="od-empty" style="padding: 32px 0;">
                    <p class="od-meta">No upcoming deadlines. You're all caught up!</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
        <!-- Recent Certificates -->
        <div class="od-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="od-h3">Recent certificates</h3>
                <a href="{{ route('student.certificates') }}" class="od-btn od-btn-ghost od-btn-sm">All certificates</a>
            </div>
            <div>
                @forelse($certificates->take(4) as $certificate)
                    <div class="od-course-row">
                        <div class="od-course-thumb" style="background: var(--od-green-soft);">
                            <i class="fas fa-award text-sm" style="color: var(--od-green);"></i>
                        </div>
                        <div class="od-course-info">
                            <h4>{{ $certificate->course?->title ?? 'Unknown Course' }}</h4>
                            <p>Issued {{ $certificate->issued_date?->format('d M Y') ?? 'N/A' }} · TEVETA Certificate</p>
                        </div>
                        <div class="od-course-action">
                            <a href="{{ route('certificates.download', $certificate) }}" class="od-btn od-btn-secondary od-btn-sm">
                                Download
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="od-empty" style="padding: 32px 0;">
                        <p class="od-meta">Complete a course to earn your first certificate.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="od-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="od-h3">Payment summary</h3>
                <a href="{{ route('student.payments') }}" class="od-btn od-btn-ghost od-btn-sm">History</a>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div style="padding: 12px; background: var(--od-fg-soft); border-radius: 10px;">
                    <div class="od-meta" style="margin-bottom: 4px;">Total paid</div>
                    <div class="od-num" style="font-size: 20px; font-weight: 600; color: var(--od-fg);">ZMW {{ number_format($totalPaid, 0) }}</div>
                </div>
                <div style="padding: 12px; background: var(--od-fg-soft); border-radius: 10px;">
                    <div class="od-meta" style="margin-bottom: 4px;">Balance due</div>
                    <div class="od-num" style="font-size: 20px; font-weight: 600; color: var(--od-fg);">ZMW {{ number_format($balanceDue, 0) }}</div>
                </div>
            </div>
            @if($payments->count() > 0)
                <table class="od-table">
                    <thead>
                        <tr><th>Date</th><th>Course</th><th class="num-col">Amount</th></tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at?->format('d M Y') ?? 'N/A' }}</td>
                                <td>{{ $payment->course?->title ?? 'N/A' }}</td>
                                <td class="num-col">ZMW {{ number_format($payment->amount, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="od-empty" style="padding: 16px 0;">
                    <p class="od-meta">No payments recorded yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Completed Courses (if any) -->
    @php
        $completed = $enrollments->where('progress', 100);
        $reviewedEnrollmentIds = \App\Models\Testimonial::where('user_id', auth()->id())->pluck('enrollment_id')->toArray();
    @endphp
    @if($completed->count() > 0)
    <div class="mt-6">
        <div class="od-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="od-h3">Completed courses</h3>
                <a href="{{ route('student.progress') }}" class="od-btn od-btn-ghost od-btn-sm">View progress</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($completed->take(3) as $enrollment)
                    @php
                        $course = $enrollment->course;
                        $hasReviewed = in_array($enrollment->id, $reviewedEnrollmentIds);
                    @endphp
                    @if($course)
                    <div class="relative flex items-center gap-4 p-4 rounded-xl" style="background: var(--od-green-soft); border: 1px solid color-mix(in oklch, var(--od-green) 20%, transparent);">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background: var(--od-green-soft);">
                            <i class="fas fa-check text-sm" style="color: var(--od-green);"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-sm truncate" style="color: var(--od-fg);">{{ $course->title }}</p>
                            <p class="text-xs" style="color: var(--od-muted);">Completed {{ $enrollment->completion_date?->diffForHumans() ?? 'recently' }}</p>
                        </div>
                        @if(!$hasReviewed)
                        <a href="{{ route('student.testimonials.create', $enrollment) }}" class="od-btn od-btn-primary od-btn-sm flex-shrink-0" title="Write a review">
                            <i class="fas fa-star mr-1"></i> Review
                        </a>
                        @else
                        <span class="od-badge od-badge-success flex-shrink-0">
                            <i class="fas fa-check mr-1"></i> Reviewed
                        </span>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
