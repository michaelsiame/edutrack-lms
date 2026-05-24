@extends('layouts.dashboard')

@section('title','Student Dashboard - Edutrack LMS')
@section('page_title','My Learning')

@section('content')
<!-- Welcome Banner -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 to-primary-700 p-6 md:p-8 mb-8 text-white shadow-md">
    <div class="relative z-10 max-w-2xl">
        <h2 class="text-xl md:text-2xl font-bold mb-2">Welcome back, {{ auth()->user()->first_name ?? 'Student' }}!</h2>
        <p class="text-primary-100 text-sm md:text-base leading-relaxed">
            Continue your learning journey and achieve your goals. You have
            <span class="font-semibold text-white">{{ $enrollments->where('enrollment_status', 'In Progress')->count() }}</span>
            course{{ $enrollments->where('enrollment_status', 'In Progress')->count() !== 1 ? 's' : '' }} in progress.
        </p>
    </div>
    <div class="absolute right-0 top-0 h-full w-1/3 opacity-10 pointer-events-none">
        <i class="fas fa-graduation-cap text-9xl absolute -right-4 -top-4"></i>
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <x-stat-card icon="fa-book-open" iconColor="primary" :value="$enrollments->count()" label="Courses" :href="route('enrollments.index')" />
    <x-stat-card icon="fa-check-circle" iconColor="success" :value="$enrollments->where('progress', 100)->count()" label="Completed" :href="route('student.progress')" />
    <x-stat-card icon="fa-certificate" iconColor="secondary" :value="$certificates->count()" label="Certificates" :href="route('student.certificates')" />
    <x-stat-card icon="fa-chart-line" iconColor="purple" :value="($enrollments->count() > 0 ? round($enrollments->avg('progress')) : 0) . '%'" label="Avg Progress" :href="route('student.progress')" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- My Courses -->
    <x-card variant="interactive" class="xl:col-span-2 overflow-hidden">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <i class="fas fa-book text-primary-500"></i>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">My Courses</h3>
            </div>
            <x-slot:headerAction>
                <a href="{{ route('enrollments.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors">
                    View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </x-slot:headerAction>
        </x-slot:header>

        <div class="divide-y divide-gray-100 dark:divide-gray-700 -mx-5 md:-mx-6">
            @forelse($enrollments->take(5) as $enrollment)
                @php $firstLesson = $enrollment->course?->modules?->flatMap->lessons->first(); @endphp
                <a href="{{ $firstLesson ? route('student.learning.show', [$enrollment->course, $firstLesson]) : route('enrollments.show', $enrollment->course) }}"
                   class="block px-5 md:px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                            {{ $enrollment->course?->title ?? 'Unknown' }}
                        </p>
                        <x-status-badge :status="$enrollment->progress == 100 ? 'Completed' : $enrollment->enrollment_status" size="sm" />
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <x-progress-bar :value="$enrollment->progress" size="sm" :color="$enrollment->progress == 100 ? 'success' : 'primary'" />
                        </div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 w-10 text-right">{{ round($enrollment->progress) }}%</span>
                    </div>
                    @if($enrollment->last_accessed)
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">
                            Last accessed {{ $enrollment->last_accessed->diffForHumans() }}
                        </p>
                    @endif
                </a>
            @empty
                <div class="px-5 md:px-6 py-12 text-center">
                    <x-empty-state icon="fa-book-open" title="No Courses Yet" description="Enroll in a course to start learning." actionText="Browse Courses" actionRoute="courses.index" />
                </div>
            @endforelse
        </div>
    </x-card>

    <!-- Right Column: Certificates + Activity -->
    <div class="space-y-6">
        <!-- Certificates -->
        <x-card variant="interactive" class="overflow-hidden">
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <i class="fas fa-certificate text-secondary-400"></i>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Certificates</h3>
                </div>
                <x-slot:headerAction>
                    <a href="{{ route('student.certificates') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors">
                        View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </x-slot:headerAction>
            </x-slot:header>

            <div class="divide-y divide-gray-100 dark:divide-gray-700 -mx-5 md:-mx-6">
                @forelse($certificates->take(4) as $certificate)
                    <div class="px-5 md:px-6 py-3.5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-lg bg-secondary-50 dark:bg-secondary-900/30 flex items-center justify-center text-secondary-500 dark:text-secondary-400 flex-shrink-0">
                                <i class="fas fa-award text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $certificate->course?->title ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $certificate->issued_date?->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('certificates.download', $certificate) }}" class="p-2 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors shrink-0" title="Download">
                            <i class="fas fa-download text-sm"></i>
                        </a>
                    </div>
                @empty
                    <div class="px-5 md:px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <i class="fas fa-certificate text-2xl mb-2 text-gray-300 dark:text-gray-600"></i>
                        <p>No certificates yet.</p>
                    </div>
                @endforelse
            </div>
        </x-card>

        <!-- Recent Activity -->
        <x-card variant="default" class="overflow-hidden">
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <i class="fas fa-bolt text-warning-500"></i>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Recent Activity</h3>
                </div>
            </x-slot:header>

            @php
                $activities = [];
                $notifications = auth()->user()->notifications()->latest()->take(5)->get();
                foreach ($notifications as $n) {
                    $iconMap = [
                        'Success' => ['fa-check-circle', 'success'],
                        'Grade'   => ['fa-star', 'warning'],
                        'Assignment' => ['fa-file-alt', 'primary'],
                        'Warning' => ['fa-exclamation-triangle', 'warning'],
                        'Info'    => ['fa-info-circle', 'primary'],
                    ];
                    $activities[] = [
                        'icon' => $iconMap[$n->notification_type][0] ?? 'fa-bell',
                        'iconColor' => $iconMap[$n->notification_type][1] ?? 'primary',
                        'title' => $n->title,
                        'description' => $n->message,
                        'url' => $n->action_url,
                        'time' => $n->created_at->diffForHumans(),
                    ];
                }
            @endphp

            <div class="px-1">
                <x-activity-feed :items="$activities" />
            </div>
        </x-card>
    </div>
</div>
@endsection
