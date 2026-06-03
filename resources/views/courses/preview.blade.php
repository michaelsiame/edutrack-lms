@extends('layouts.app')

@section('title', $lesson->title . ' - ' . $course->title)
@section('meta_description', 'Preview ' . $lesson->title . ' from ' . $course->title . ' — free lesson available before enrollment.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<style>
    .od-preview-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
        padding: 24px 0 48px;
    }
    @media (max-width: 1024px) {
        .od-preview-layout { grid-template-columns: 1fr; }
        .od-preview-sidebar { display: none; }
        .od-preview-sidebar.open {
            display: block;
            position: fixed;
            inset: 0;
            z-index: 60;
            background: var(--od-bg);
            padding: 24px;
            overflow-y: auto;
        }
    }
    .od-lesson-content h1, .od-lesson-content h2, .od-lesson-content h3 {
        color: var(--od-fg);
        margin-top: 1.5em;
        margin-bottom: 0.5em;
    }
    .od-lesson-content p { margin-bottom: 1em; line-height: 1.7; }
    .od-lesson-content ul, .od-lesson-content ol { margin-bottom: 1em; padding-left: 1.5em; }
    .od-lesson-content img { max-width: 100%; border-radius: 10px; margin: 1em 0; }
    .od-lesson-content blockquote {
        border-left: 3px solid var(--od-accent);
        padding-left: 1em;
        margin: 1em 0;
        color: var(--od-muted);
    }
    .od-preview-banner {
        background: linear-gradient(135deg, var(--od-navy) 0%, color-mix(in oklch, var(--od-navy) 70%, var(--od-accent)) 100%);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .od-preview-locked {
        opacity: 0.5;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
@php
    $module = $lesson->module;
@endphp

<div style="background: var(--od-bg); min-height: 100vh;">
    <!-- Sticky Topnav -->
    <header style="position: sticky; top: 0; z-index: 50; background: var(--od-surface); border-bottom: 1px solid var(--od-border); backdrop-filter: blur(8px);">
        <div class="container flex items-center justify-between gap-4" style="max-width: 1200px; margin: 0 auto; padding: 12px 24px;">
            <div class="flex items-center gap-4">
                <a href="{{ route('courses.show', $course) }}" class="logo" style="display:flex;align-items:center;gap:8px;font-family:var(--font-display);font-size:16px;font-weight:600;color:var(--od-fg);text-decoration:none;">
                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="EduTrack" style="height:28px;width:auto;">
                </a>
                <span class="hidden sm:inline" style="font-size:14px;font-weight:500;color:var(--od-muted);max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $course->title }} · {{ $module->title }}, {{ $lesson->title }}
                </span>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium" style="background: var(--od-green-soft); color: var(--od-green);">
                    <i class="fas fa-eye"></i> Free Preview
                </span>
                <button class="lg:hidden od-btn od-btn-ghost od-btn-sm" onclick="document.getElementById('previewSidebar').classList.toggle('open')" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Layout -->
    <main class="container od-preview-layout" style="max-width: 1200px; margin: 0 auto; padding: 0 24px;">
        <!-- Sidebar -->
        <aside class="od-preview-sidebar" id="previewSidebar">
            <div class="flex items-center justify-between mb-4">
                <p class="od-eyebrow" style="margin:0;">Course content</p>
                <button class="lg:hidden od-btn od-btn-ghost od-btn-sm" onclick="document.getElementById('previewSidebar').classList.remove('open')">Close</button>
            </div>

            @foreach($course->modules as $mod)
                @php
                    $isActiveModule = $mod->id === $module->id;
                @endphp
                <div class="od-module">
                    <div class="od-module-header" style="{{ $isActiveModule ? 'background: var(--od-navy-soft); color: var(--od-navy);' : '' }}">
                        <span>{{ $mod->title }}</span>
                        <span class="module-num">{{ $mod->lessons->count() }}</span>
                    </div>
                    <ul class="od-lesson-list">
                        @foreach($mod->lessons as $l)
                            @php
                                $isActive = $l->id === $lesson->id;
                                $isPreview = $l->is_preview;
                            @endphp
                            <li class="{{ $isActive ? 'active' : '' }} {{ !$isPreview ? 'od-preview-locked' : '' }}">
                                @if($isPreview)
                                    <a href="{{ route('courses.preview', ['course' => $course, 'lesson' => $l]) }}">
                                        <span class="icon">
                                            @if($isActive)
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                            @else
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                            @endif
                                        </span>
                                        {{ $l->title }}
                                    </a>
                                @else
                                    <span class="flex items-center gap-2 px-3 py-2 text-sm" style="color: var(--od-muted);">
                                        <i class="fas fa-lock text-xs"></i>
                                        {{ $l->title }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </aside>

        <!-- Main Content -->
        <div class="space-y-6">
            <!-- Preview Banner -->
            <div class="od-preview-banner">
                <div>
                    <p class="font-semibold text-sm"><i class="fas fa-unlock-alt mr-2"></i>You're viewing a free preview</p>
                    <p class="text-xs opacity-90 mt-1">Enroll to unlock all lessons, quizzes, assignments, and earn a certificate.</p>
                </div>
                @if($isEnrolled)
                    <a href="{{ route('student.learning.show', ['course' => $course, 'lesson' => $lesson]) }}" class="od-btn od-btn-success od-btn-sm shrink-0">
                        <i class="fas fa-play mr-1"></i> Continue Learning
                    </a>
                @else
                    @auth
                        @php
                            $hasPaidRegFee = \App\Models\RegistrationFee::where('user_id', auth()->id())->where('payment_status','completed')->exists();
                        @endphp
                        @if($hasPaidRegFee)
                            <form action="{{ route('enrollments.store', $course) }}" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit" class="od-btn od-btn-primary od-btn-sm">
                                    <i class="fas fa-user-plus mr-1"></i> Enroll Now
                                </button>
                            </form>
                        @else
                            <a href="{{ route('registration-fee.show') }}" class="od-btn od-btn-primary od-btn-sm shrink-0">
                                <i class="fas fa-lock mr-1"></i> Pay Registration Fee
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}" class="od-btn od-btn-primary od-btn-sm shrink-0">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login to Enroll
                        </a>
                    @endauth
                @endif
            </div>

            <!-- Video / Header Card -->
            @if($lesson->lesson_type === 'Video')
                @if($lesson->embedUrl())
                    <div class="od-video-wrap">
                        <iframe src="{{ $lesson->embedUrl() }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen title="Lesson video: {{ $lesson->title }}"></iframe>
                    </div>
                @else
                    <div class="od-video-wrap" style="display:grid;place-items:center;background:var(--od-fg-soft);">
                        <div class="text-center p-8">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full flex items-center justify-center" style="background:var(--od-fg-soft);">
                                <i class="fas fa-video text-2xl" style="color:var(--od-muted);"></i>
                            </div>
                            <p style="color:var(--od-muted);font-weight:500;">No video URL set for this lesson.</p>
                        </div>
                    </div>
                @endif
            @elseif(in_array($lesson->lesson_type, ['Quiz', 'Assignment']))
                <div class="od-card" style="padding:48px;text-align:center;">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" style="background: {{ $lesson->lesson_type === 'Quiz' ? 'var(--od-navy-soft)' : 'var(--od-accent-soft)' }};">
                        <i class="fas {{ $lesson->lesson_type === 'Quiz' ? 'fa-clipboard-list' : 'fa-tasks' }} text-2xl" style="color: {{ $lesson->lesson_type === 'Quiz' ? 'var(--od-navy)' : 'var(--od-accent)' }};"></i>
                    </div>
                    <h2 class="od-h2 mb-2">{{ $lesson->title }}</h2>
                    <p class="od-lead" style="max-width:50ch;margin:0 auto 24px;">
                        {{ $lesson->lesson_type === 'Quiz' ? 'Test your knowledge with this quiz.' : 'Complete the assigned task and submit your work.' }}
                    </p>
                    <p class="text-sm" style="color: var(--od-muted);"><i class="fas fa-lock mr-1"></i> Enroll to access this {{ strtolower($lesson->lesson_type) }}</p>
                </div>
            @endif

            <!-- Lesson Info Card -->
            <div class="od-card">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
                    <div>
                        <p class="od-eyebrow" style="margin-bottom:4px;">{{ $module->title }}</p>
                        <h1 class="od-h2">{{ $lesson->title }}</h1>
                        <p class="od-meta mt-1">{{ $course->title }}</p>
                    </div>
                    @if($lesson->duration_minutes)
                        <span class="od-btn od-btn-ghost od-btn-sm shrink-0" style="cursor:default;">
                            <i class="fas fa-clock"></i> {{ $lesson->duration_minutes }} min
                        </span>
                    @endif
                </div>

                <!-- Lesson Content -->
                @if($lesson->content)
                    <div class="od-lesson-content text-gray-700 dark:text-gray-300">
                        {!! \App\Services\HtmlSanitizer::clean($lesson->content) !!}
                    </div>
                @endif

                <!-- Resources (preview only — no download links) -->
                @if($lesson->resources->isNotEmpty())
                    <div class="mt-6 pt-6" style="border-top: 1px solid var(--od-border);">
                        <h3 class="text-sm font-semibold mb-3 flex items-center gap-2" style="color: var(--od-fg);">
                            <i class="fas fa-paperclip" style="color: var(--od-muted);"></i> Lesson Resources
                        </h3>
                        @foreach($lesson->resources as $resource)
                            <div class="od-resource" style="opacity: 0.6; cursor: not-allowed;">
                                <i class="fas fa-file" style="color: var(--od-navy);"></i>
                                <span class="flex-1 truncate">{{ $resource->title }}</span>
                                <span class="od-meta hidden sm:inline">{{ strtoupper($resource->resource_type) }}</span>
                                <span class="text-xs" style="color: var(--od-muted);"><i class="fas fa-lock mr-1"></i> Locked</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Navigation between preview lessons only -->
            @php
                $allPreviewLessons = collect();
                foreach ($course->modules as $mod) {
                    foreach ($mod->lessons as $l) {
                        if ($l->is_preview) {
                            $allPreviewLessons->push($l);
                        }
                    }
                }
                $currentIndex = $allPreviewLessons->search(fn($l) => $l->id === $lesson->id);
                $prevPreview = $currentIndex > 0 ? $allPreviewLessons[$currentIndex - 1] : null;
                $nextPreview = $currentIndex < $allPreviewLessons->count() - 1 ? $allPreviewLessons[$currentIndex + 1] : null;
            @endphp
            <div class="od-lesson-nav">
                @if($prevPreview)
                    <a href="{{ route('courses.preview', ['course' => $course, 'lesson' => $prevPreview]) }}" class="od-btn od-btn-secondary od-btn-sm">
                        <i class="fas fa-arrow-left"></i> Previous Preview
                    </a>
                @else
                    <span></span>
                @endif

                @if($nextPreview)
                    <a href="{{ route('courses.preview', ['course' => $course, 'lesson' => $nextPreview]) }}" class="od-btn od-btn-primary od-btn-sm">
                        Next Preview <i class="fas fa-arrow-right"></i>
                    </a>
                @else
                    <span></span>
                @endif
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    // Close sidebar on mobile when clicking a lesson link
    document.querySelectorAll('.od-preview-sidebar a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                document.getElementById('previewSidebar').classList.remove('open');
            }
        });
    });
</script>
@endpush
