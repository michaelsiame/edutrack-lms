@extends('layouts.dashboard')

@section('title', 'Student Dashboard - Edutrack LMS')
@section('page_title', 'My Learning')

@section('content')
<!-- Welcome Banner -->
<div class="bg-gradient-to-r from-primary-600 to-blue-700 rounded-xl p-6 mb-8 text-white relative overflow-hidden">
    <div class="relative z-10">
        <h2 class="text-xl font-bold mb-1">Welcome back, {{ auth()->user()->first_name ?? 'Student' }}!</h2>
        <p class="text-blue-100 text-sm">Continue your learning journey and achieve your goals.</p>
    </div>
    <div class="absolute right-0 top-0 h-full w-1/3 opacity-10">
        <i class="fas fa-graduation-cap text-9xl absolute -right-4 -top-4"></i>
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                <i class="fas fa-book-open"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $enrollments->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Courses</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center text-green-600 dark:text-green-400">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $enrollments->where('progress', 100)->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center text-amber-600 dark:text-amber-400">
                <i class="fas fa-certificate"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $certificates->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Certificates</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center text-purple-600 dark:text-purple-400">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                @php
                    $avgProgress = $enrollments->count() > 0 ? round($enrollments->avg('progress')) : 0;
                @endphp
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $avgProgress }}%</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Avg Progress</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <!-- My Courses -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                <i class="fas fa-book text-primary-500 mr-2"></i>My Courses
            </h3>
            <a href="{{ route('enrollments.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($enrollments->take(5) as $enrollment)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $enrollment->course?->title ?? 'Unknown' }}</p>
                        <span class="text-xs font-medium {{ $enrollment->progress == 100 ? 'text-green-600 dark:text-green-400' : 'text-primary-600 dark:text-primary-400' }}">
                            {{ $enrollment->progress == 100 ? 'Completed' : $enrollment->enrollment_status }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full transition-all duration-500" style="width: {{ $enrollment->progress }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400 w-10 text-right">{{ number_format($enrollment->progress, 0) }}%</span>
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-book-open text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                    <p>No enrolled courses yet.</p>
                    <a href="{{ route('courses.index') }}" class="text-primary-600 dark:text-primary-400 hover:underline mt-1 inline-block">Browse courses</a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Certificates -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                <i class="fas fa-certificate text-yellow-500 mr-2"></i>My Certificates
            </h3>
            <a href="{{ route('student.certificates') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($certificates->take(5) as $certificate)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                            <i class="fas fa-award"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $certificate->course?->title ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $certificate->issued_date?->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('certificates.download', $certificate) }}" class="p-2 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-certificate text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                    <p>No certificates yet.</p>
                    <p class="text-xs mt-1">Complete a course to earn one!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
