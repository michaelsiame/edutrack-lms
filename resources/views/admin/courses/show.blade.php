@extends('layouts.dashboard')

@section('title', $course->title . ' - Edutrack LMS')
@section('page_title', $course->title)

@section('breadcrumbs')
    <span class="opacity-50">/</span>
    <a href="{{ route('admin.courses.index') }}" class="hover:underline">Courses</a>
    <span class="opacity-50">/</span>
    <span style="color: var(--od-fg);" class="font-medium">{{ $course->title }}</span>
@endsection

@section('content')
@php
    $lessonCount = $course->modules->flatMap->lessons->count();
    $moduleCount = $course->modules->count();
    $enrolCount = $course->enrollments()->where('enrollment_status', '!=', 'Dropped')->count();
@endphp
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="od-card p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $course->status === 'published' ? 'bg-success-100 text-success-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($course->status) }}</span>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $course->title }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $course->category?->name ?? 'Uncategorised' }} · {{ $course->duration_weeks ?? '—' }} weeks · ZMW {{ number_format($course->price, 2) }}</p>
            </div>
            <a href="{{ route('admin.courses.edit', $course) }}" class="od-btn od-btn-secondary od-btn-sm">
                <i class="fas fa-edit"></i> Edit details
            </a>
        </div>

        {{-- Stat strip --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
            @foreach([
                ['fa-layer-group', $moduleCount, 'Modules'],
                ['fa-file-lines', $lessonCount, 'Lessons'],
                ['fa-user-graduate', $enrolCount, 'Students'],
                ['fa-money-bill-wave', 'ZMW '.number_format($course->price,0), 'Price'],
            ] as [$icon, $val, $label])
                <div class="rounded-xl p-3" style="background: var(--od-bg);">
                    <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs mb-1"><i class="fas {{ $icon }}"></i> {{ $label }}</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $val }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Quick actions + outline + details --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">
            <div class="od-card p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Manage this course</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach([
                        [route('instructor.courses.show', $course), 'fa-layer-group', 'var(--od-navy)', 'Build content', 'Add & edit modules and lessons'],
                        [route('staff.courses.read', $course), 'fa-book-open', 'var(--od-green)', 'Read / preview', 'See the course as a learner'],
                        [route('instructor.courses.gradebook', $course), 'fa-table', 'var(--od-accent)', 'Gradebook', 'Marks across all students'],
                        [route('instructor.courses.intakes.index', $course), 'fa-calendar-alt', 'var(--od-navy)', 'Intakes', 'Manage cohorts & capacity'],
                        [route('instructor.live-sessions.index', $course), 'fa-video', 'var(--od-danger)', 'Live sessions', 'Schedule classes'],
                        [route('admin.enrollments.index').'?course='.$course->id, 'fa-user-graduate', 'var(--od-green)', 'Enrolments', 'Students on this course'],
                    ] as [$url, $icon, $color, $title, $desc])
                        <a href="{{ $url }}" class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-400 hover:shadow-sm transition">
                            <span class="inline-flex items-center justify-center rounded-lg shrink-0" style="width:38px;height:38px;background:color-mix(in oklch, {{ $color }}, transparent 88%);color:{{ $color }};"><i class="fas {{ $icon }}"></i></span>
                            <span>
                                <span class="block font-medium text-sm text-gray-900 dark:text-white">{{ $title }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $desc }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="od-card p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Course outline</h3>
                @forelse($course->modules->sortBy('display_order') as $module)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <span class="font-medium text-sm text-gray-900 dark:text-white">{{ $module->title }}</span>
                        <span class="text-xs text-gray-400">{{ $module->lessons->count() }} lessons</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No modules yet. <a href="{{ route('instructor.courses.show', $course) }}" class="text-primary-600 hover:underline">Build content →</a></p>
                @endforelse
            </div>
        </div>

        <div class="od-card p-6 h-fit">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Details</h3>
            <div class="space-y-3 text-sm">
                @foreach([
                    ['Category', $course->category?->name ?? 'N/A'],
                    ['Instructor', $course->instructor?->user?->full_name ?? 'TBA'],
                    ['Duration', ($course->duration_weeks ?? '—').' weeks'],
                    ['Level', $course->level ?? 'N/A'],
                    ['Price', 'ZMW '.number_format($course->price, 2)],
                    ['Intakes', $course->intakes->count()],
                ] as [$k, $v])
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2 last:border-0">
                        <span class="text-gray-500 dark:text-gray-400">{{ $k }}</span>
                        <span class="font-medium text-gray-900 dark:text-white text-right">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
