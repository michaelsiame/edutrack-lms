@extends('layouts.dashboard')

@section('title','Analytics - Edutrack LMS')
@section('page_title','Course Analytics')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="od-stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="od-meta">Total Students</p>
                    <p class="od-stat-value od-num mt-1">{{ $totalStudents }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-primary-600 dark:text-primary-400 text-lg"></i>
                </div>
            </div>
        </div>
        <div class="od-stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="od-meta">Enrollments</p>
                    <p class="od-stat-value od-num mt-1">{{ $totalEnrollments }}</p>
                </div>
                <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-plus text-success-600 dark:text-success-400 text-lg"></i>
                </div>
            </div>
        </div>
        <div class="od-stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="od-meta">Completion Rate</p>
                    <p class="od-stat-value od-num mt-1">{{ $completionRate }}%</p>
                </div>
                <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-pie text-warning-600 dark:text-warning-400 text-lg"></i>
                </div>
            </div>
        </div>
        <div class="od-stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="od-meta">Avg Quiz Score</p>
                    <p class="od-stat-value od-num mt-1">{{ number_format($avgQuizScore, 1) }}%</p>
                </div>
                <div class="w-12 h-12 bg-secondary-100 dark:bg-secondary-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-star text-secondary-600 dark:text-secondary-400 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Enrollments Chart Data --}}
    <div class="od-card p-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-chart-bar text-primary-500 mr-2"></i>Monthly Enrollments
        </h3>
        @if($monthlyEnrollments->isNotEmpty())
        <div class="space-y-3">
            @php $maxCount = $monthlyEnrollments->max('count'); @endphp
            @foreach($monthlyEnrollments as $month)
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 dark:text-gray-400 w-20 flex-shrink-0">{{ \Carbon\Carbon::parse($month->month)->format('M Y') }}</span>
                <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-6 overflow-hidden">
                    <div class="bg-primary-500 h-full rounded-full flex items-center justify-end pr-2" style="width: {{ $maxCount > 0 ? ($month->count / $maxCount * 100) : 0 }}%">
                        <span class="text-xs text-white font-medium">{{ $month->count }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p class="text-sm">No enrollment data available yet.</p>
        </div>
        @endif
    </div>

    {{-- Monthly Enrollments Chart --}}
    <div class="od-card p-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-chart-line text-primary-500 mr-2"></i>Monthly Enrollments
        </h3>
        <div class="h-64">
            <canvas id="enrollmentChart"></canvas>
        </div>
    </div>

    {{-- Per-Course Breakdown --}}
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-book text-primary-500 mr-2"></i>Course Performance
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="od-table min-w-[640px]">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Students</th>
                        <th>Lessons</th>
                        <th>Rating</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td>
                            <span class="font-medium" style="color: var(--od-fg);">{{ $course->title }}</span>
                        </td>
                        <td class="text-sm text-gray-600 dark:text-gray-400">{{ $course->enrollments_count }}</td>
                        <td class="text-sm text-gray-600 dark:text-gray-400">{{ $course->lessons_count }}</td>
                        <td>
                            <div class="flex items-center gap-1 text-sm">
                                <i class="fas fa-star text-warning-500 text-xs"></i>
                                <span class="text-gray-900 dark:text-white font-medium">{{ number_format($course->reviews_avg_rating ?? 0, 1) }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $course->status === 'published' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="od-empty-sm">
                            <i class="fas fa-chart-line text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                            <p class="text-sm">No courses yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.min.js') }}"></script>
<script>
const ctx = document.getElementById('enrollmentChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($monthlyEnrollments->pluck('month')->map(fn($m) => \Carbon\Carbon::parse($m.'-01')->format('M Y'))) !!},
        datasets: [{
            label: 'Enrollments',
            data: {!! json_encode($monthlyEnrollments->pluck('count')) !!},
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3,
            pointRadius: 4,
            pointBackgroundColor: 'rgba(59, 130, 246, 1)',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, precision: 0 }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>
@endpush
