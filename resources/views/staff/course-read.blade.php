@extends('layouts.dashboard')

@section('title', $lesson->title . ' — ' . $course->title)
@section('page_title', 'Course Preview')

@section('breadcrumbs')
    <span class="opacity-50">/</span>
    <a href="{{ auth()->user()->isAdmin() ? route('admin.courses.index') : route('instructor.courses.index') }}" class="hover:underline">Courses</a>
    <span class="opacity-50">/</span>
    <span style="color: var(--od-fg);" class="font-medium">{{ $course->title }} — Read</span>
@endsection


@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/lesson-content.css') }}">
<style>
    .od-read-layout { display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start; }
    @media (min-width: 1025px) {
        .od-read-sidebar { position: sticky; top: 16px; max-height: calc(100vh - 32px); overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; }
    }
    @media (max-width: 1024px) { .od-read-layout { grid-template-columns: 1fr; } }
    .od-read-lessonlink { display:block; padding:7px 12px; border-radius:8px; font-size:13px; text-decoration:none; color:var(--od-fg); }
    .od-read-lessonlink:hover { background: var(--od-bg); }
    .od-read-lessonlink.active { background: var(--od-navy-soft); color: var(--od-navy); font-weight:600; }
    .od-lesson-content img { max-width: 100%; border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <p class="od-eyebrow" style="margin:0;">Staff preview · read-only</p>
            <h1 class="od-h2" style="margin:0;">{{ $course->title }}</h1>
        </div>
        <a href="{{ route('instructor.courses.show', $course) }}" class="od-btn od-btn-secondary od-btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Course Builder
        </a>
    </div>

    <div class="od-read-layout">
        {{-- Sidebar: modules & lessons (collapsible on mobile so content shows first) --}}
        <aside class="od-read-sidebar od-card" style="padding:14px;" x-data="{ nav: false }">
            <button type="button" @click="nav = !nav" class="lg:hidden w-full flex items-center justify-between">
                <span class="od-eyebrow" style="margin:0;">Course content</span>
                <i class="fas fa-chevron-down transition-transform" :class="nav ? 'rotate-180' : ''" style="color:var(--od-muted);"></i>
            </button>
            <p class="od-eyebrow hidden lg:block" style="margin:0 0 8px;">Course content</p>
            <div :class="nav ? 'block' : 'hidden'" class="lg:block mt-3 lg:mt-0">
                @foreach($modules as $mod)
                    <div class="mb-3">
                        <div class="text-sm font-semibold mb-1" style="color:var(--od-fg);">{{ $mod->title }}</div>
                        @forelse($mod->lessons as $l)
                            <a href="{{ route('staff.courses.lesson', [$course, $l]) }}"
                               class="od-read-lessonlink {{ $l->id === $lesson->id ? 'active' : '' }}">
                                <i class="fas {{ $l->lesson_type === 'Quiz' ? 'fa-clipboard-question' : ($l->lesson_type === 'Video' ? 'fa-play-circle' : ($l->lesson_type === 'Assignment' ? 'fa-file-pen' : 'fa-file-lines')) }} mr-1" style="opacity:.6;"></i>
                                {{ $l->title }}
                            </a>
                        @empty
                            <p class="od-meta" style="padding:4px 12px;">No lessons.</p>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </aside>

        {{-- Main: lesson content --}}
        <div class="space-y-5">
            <div class="od-card">
                <p class="od-eyebrow" style="margin:0 0 2px;">{{ $lesson->module->title }} · {{ $lesson->lesson_type }}</p>
                <h2 class="od-h2" style="margin:0 0 16px;">{{ $lesson->title }}</h2>

                @if($lesson->lesson_type === 'Video' && $lesson->embedUrl())
                    <div class="od-video-wrap mb-4">
                        <iframe src="{{ $lesson->embedUrl() }}" allowfullscreen title="Lesson video"></iframe>
                    </div>
                @endif

                @if(!empty($lesson->content))
                    <div class="od-lesson-content lesson-content">
                        {!! \App\Services\HtmlSanitizer::clean($lesson->content) !!}
                    </div>
                @else
                    <p class="od-meta">This lesson has no written notes.</p>
                @endif

                {{-- Resources --}}
                @if($lesson->resources->count())
                    <div class="mt-6">
                        <p class="od-eyebrow mb-2">Resources</p>
                        @foreach($lesson->resources as $resource)
                            <a href="{{ route('staff.courses.resource', [$course, $lesson, $resource]) }}"
                               class="od-resource">
                                <i class="fas fa-file" style="color: var(--od-navy);"></i>
                                <span class="flex-1 truncate">{{ $resource->title }}</span>
                                <span class="od-meta hidden sm:inline">{{ strtoupper($resource->resource_type) }}</span>
                                <i class="fas fa-download" style="color: var(--od-muted);"></i>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Linked quizzes/assignments (info only) --}}
                @if($lesson->quizzes->count() || $lesson->assignments->count())
                    <div class="mt-6">
                        <p class="od-eyebrow mb-2">Assessments on this lesson</p>
                        @foreach($lesson->quizzes as $q)
                            <div class="od-resource"><i class="fas fa-clipboard-question" style="color:var(--od-accent);"></i><span class="flex-1">{{ $q->title }} <span class="od-meta">(Quiz)</span></span></div>
                        @endforeach
                        @foreach($lesson->assignments as $a)
                            <div class="od-resource"><i class="fas fa-file-pen" style="color:var(--od-accent);"></i><span class="flex-1">{{ $a->title }} <span class="od-meta">(Assignment)</span></span></div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Prev / Next --}}
            <div class="flex items-center justify-between">
                @if($prevLesson)
                    <a href="{{ route('staff.courses.lesson', [$course, $prevLesson]) }}" class="od-btn od-btn-secondary od-btn-sm"><i class="fas fa-arrow-left"></i> Previous</a>
                @else <span></span> @endif
                @if($nextLesson)
                    <a href="{{ route('staff.courses.lesson', [$course, $nextLesson]) }}" class="od-btn od-btn-primary od-btn-sm">Next <i class="fas fa-arrow-right"></i></a>
                @else <span></span> @endif
            </div>
        </div>
    </div>
</div>
@endsection
