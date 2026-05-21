@extends('layouts.dashboard')

@section('title','Student Dashboard - Edutrack LMS')
@section('page_title','My Learning')

@section('content')
<!-- Welcome Banner -->
<div class="bg-primary-600 rounded-2xl p-6 mb-8 text-white relative overflow-hidden">
 <div class="relative z-10">
 <h2 class="text-xl font-bold mb-1">Welcome back, {{ auth()->user()->first_name ??'Student' }}!</h2>
 <p class="text-primary-100 text-sm">Continue your learning journey and achieve your goals.</p>
 </div>
 <div class="absolute right-0 top-0 h-full w-1/3 opacity-10">
 <i class="fas fa-graduation-cap text-9xl absolute -right-4 -top-4"></i>
 </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
 <x-stat-card icon="fa-book-open" iconColor="primary" :value="$enrollments->count()" label="Courses" :href="route('enrollments.index')" />
 <x-stat-card icon="fa-check-circle" iconColor="success" :value="$enrollments->where('progress', 100)->count()" label="Completed" :href="route('student.progress')" />
 <x-stat-card icon="fa-certificate" iconColor="warning" :value="$certificates->count()" label="Certificates" :href="route('student.certificates')" />
 <x-stat-card icon="fa-chart-line" iconColor="purple" :value="($enrollments->count() > 0 ? round($enrollments->avg('progress')) : 0) .'%'" label="Avg Progress" :href="route('student.progress')" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
 <!-- My Courses -->
 <x-card hover class="overflow-hidden">
 <div class="flex items-center justify-between mb-5">
 <h3 class="text-base font-semibold text-gray-800 dark:text-white">
 <i class="fas fa-book text-primary-500 mr-2"></i>My Courses
 </h3>
 <a href="{{ route('enrollments.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
 </div>
 <div class="divide-y divide-gray-100 dark:divide-gray-700 -mx-5 md:-mx-6">
 @forelse($enrollments->take(5) as $enrollment)
 @php $firstLesson = $enrollment->course?->modules?->flatMap->lessons->first(); @endphp
 <a href="{{ $firstLesson ? route('student.learning.show', [$enrollment->course, $firstLesson]) : route('enrollments.show', $enrollment->course) }}" class="block px-5 md:px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
 <div class="flex items-center justify-between mb-2">
 <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $enrollment->course?->title ??'Unknown' }}</p>
 <span class="text-xs font-medium {{ $enrollment->progress == 100 ?'text-success-600 dark:text-success-400' :'text-primary-600 dark:text-primary-400' }}">
 {{ $enrollment->progress == 100 ?'Completed' : $enrollment->enrollment_status }}
 </span>
 </div>
 <x-progress-bar :value="$enrollment->progress" size="sm" />
 </a>
 @empty
 <div class="px-5 md:px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
 <i class="fas fa-book-open text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
 <p>No enrolled courses yet.</p>
 <a href="{{ route('courses.index') }}" class="text-primary-600 dark:text-primary-400 hover:underline mt-1 inline-block">Browse courses</a>
 </div>
 @endforelse
 </div>
 </x-card>

 <!-- Certificates -->
 <x-card hover class="overflow-hidden">
 <div class="flex items-center justify-between mb-5">
 <h3 class="text-base font-semibold text-gray-800 dark:text-white">
 <i class="fas fa-certificate text-secondary-400 mr-2"></i>My Certificates
 </h3>
 <a href="{{ route('student.certificates') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
 </div>
 <div class="divide-y divide-gray-100 dark:divide-gray-700 -mx-5 md:-mx-6">
 @forelse($certificates->take(5) as $certificate)
 <div class="px-5 md:px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
 <div class="flex items-center gap-3">
 <div class="w-10 h-10 rounded-xl bg-secondary-50 dark:bg-secondary-900/30 flex items-center justify-center text-secondary-500 dark:text-secondary-400 flex-shrink-0">
 <i class="fas fa-award"></i>
 </div>
 <div>
 <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $certificate->course?->title ??'Unknown' }}</p>
 <p class="text-xs text-gray-500 dark:text-gray-400">{{ $certificate->issued_date?->format('M d, Y') }}</p>
 </div>
 </div>
 <a href="{{ route('certificates.download', $certificate) }}" class="p-2 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl transition-colors" title="Download">
 <i class="fas fa-download"></i>
 </a>
 </div>
 @empty
 <div class="px-5 md:px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
 <i class="fas fa-certificate text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
 <p>No certificates yet.</p>
 <p class="text-xs mt-1">Complete a course to earn one!</p>
 </div>
 @endforelse
 </div>
 </x-card>
</div>
@endsection
