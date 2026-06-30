@extends('layouts.dashboard')

@section('title', $lesson->title . ' - ' . $course->title)
@section('page_title', $lesson->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<style>
    /* Override dashboard bg for learning pages */
    .od-learn-page { background: var(--od-bg); }
    .od-learn-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
        padding: 24px 0 48px;
        align-items: start; /* let the sticky sidebar size to its own content, not the grid row */
    }
    /* Desktop: module nav stays put while notes scroll, and scrolls on its own when long */
    @media (min-width: 1025px) {
        .od-learn-sidebar {
            position: sticky;
            top: 64px; /* clears the sticky topnav */
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            overscroll-behavior: contain; /* scrolling the nav doesn't bleed into the page */
            padding-right: 6px;
            scrollbar-width: thin;
        }
        .od-learn-sidebar::-webkit-scrollbar { width: 8px; }
        .od-learn-sidebar::-webkit-scrollbar-thumb {
            background: var(--od-border);
            border-radius: 4px;
        }
    }
    @media (max-width: 1024px) {
        .od-learn-layout { grid-template-columns: 1fr; }
        .od-learn-sidebar { display: none; }
        .od-learn-sidebar.open {
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
    .od-lesson-content img { max-width: 100%; height: auto; border-radius: 10px; margin: 1em 0; }
    .od-lesson-content blockquote {
        border-left: 3px solid var(--od-accent);
        padding-left: 1em;
        margin: 1em 0;
        color: var(--od-muted);
    }
    /* Prevent horizontal overflow on mobile from code, tables and long links */
    .od-learn-layout > * { min-width: 0; }
    .od-lesson-content { overflow-wrap: anywhere; word-break: break-word; }
    .od-lesson-content pre {
        overflow-x: auto;
        max-width: 100%;
        white-space: pre-wrap;
        word-break: break-word;
        background: var(--od-fg-soft, #f3f4f6);
        padding: 12px;
        border-radius: 8px;
    }
    .od-lesson-content code { overflow-wrap: anywhere; }
    .od-lesson-content a { overflow-wrap: anywhere; }
    .od-lesson-content table {
        display: block;
        max-width: 100%;
        overflow-x: auto;
        border-collapse: collapse;
    }
    .od-lesson-content table td, .od-lesson-content table th {
        border: 1px solid var(--od-border);
        padding: 6px 10px;
    }
    /* Keep embedded media from overflowing on small screens */
    .od-lesson-content iframe,
    .od-lesson-content video,
    .od-lesson-content embed,
    .od-lesson-content object {
        max-width: 100%;
        height: auto;
    }
</style>
@endpush

@section('content')
@php
    // Compute prev/next lessons
    $allLessons = collect();
    foreach ($modules as $mod) {
        foreach ($mod->lessons as $l) {
            $allLessons->push($l);
        }
    }
    $currentIndex = $allLessons->search(fn($l) => $l->id === $lesson->id);
    $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
    $nextLesson = $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;
    $module = $lesson->module;
    $moduleIndex = $modules->search(fn($m) => $m->id === $module->id);
@endphp

<div class="od-learn-page -m-4 md:-m-6 lg:-m-8">
    <!-- Sticky Topnav -->
    <header class="od-topnav">
        <div class="container od-topnav-inner" style="max-width: 1200px; margin: 0 auto;">
            <div class="flex items-center gap-4" style="min-width:0;flex:1;">
                <a href="{{ route('home') }}" class="logo" style="display:flex;align-items:center;gap:8px;font-family:var(--font-display);font-size:16px;font-weight:600;color:var(--od-fg);text-decoration:none;flex-shrink:0;">
                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="EduTrack" style="height:28px;width:auto;">
                </a>
                <span class="course-title" style="font-size:14px;font-weight:500;color:var(--od-muted);min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $course->title }} · {{ $module->title }}, {{ $lesson->title }}
                </span>
            </div>
            <div class="flex items-center gap-3" style="flex-shrink:0;">
                <span class="od-progress-pill">
                    <span class="od-num">{{ $progress }}</span>% complete
                </span>
                <a href="{{ route('student.dashboard') }}" class="od-btn od-btn-ghost od-btn-sm hidden sm:inline-flex">Dashboard</a>
                <button class="lg:hidden od-btn od-btn-ghost od-btn-sm" onclick="document.getElementById('learnSidebar').classList.toggle('open')" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Learning Layout -->
    <main class="container od-learn-layout" style="max-width: 1200px; margin: 0 auto; padding: 0 24px;">
        <!-- Sidebar -->
        <aside class="od-learn-sidebar" id="learnSidebar">
            <div class="flex items-center justify-between mb-4">
                <p class="od-eyebrow" style="margin:0;">Course content</p>
                <button class="lg:hidden od-btn od-btn-ghost od-btn-sm" onclick="document.getElementById('learnSidebar').classList.remove('open')">Close</button>
            </div>

            @foreach($modules as $mod)
                @php
                    $modLessons = $mod->lessons;
                    $completedInMod = $modLessons->where('is_completed', true)->count();
                    $totalInMod = $modLessons->count();
                    $isActiveModule = $mod->id === $module->id;
                @endphp
                @php $modSession = ($moduleSessions ?? collect())->get($mod->id); @endphp
                <div class="od-module">
                    <div class="od-module-header" style="{{ $isActiveModule ? 'background: var(--od-navy-soft); color: var(--od-navy);' : '' }}">
                        <span>{{ $mod->title }}</span>
                        <span class="module-num">{{ $completedInMod }}/{{ $totalInMod }}</span>
                    </div>
                    @if($modSession)
                        <a href="{{ $modSession->isLive() ? route('student.live-sessions.join', $modSession) : route('student.live-sessions.index', $course) }}"
                           @if($modSession->isLive()) target="_blank" @endif
                           class="od-module-live"
                           style="display:flex;align-items:center;gap:6px;padding:6px 12px;font-size:12px;text-decoration:none;{{ $modSession->isLive() ? 'color:var(--od-danger);font-weight:600;' : 'color:var(--od-navy);' }}">
                            @if($modSession->isLive())
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background: var(--od-danger);"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2" style="background: var(--od-danger);"></span>
                                </span>
                                Live class — Join now
                            @else
                                <i class="fas fa-video"></i>
                                Live class · {{ $modSession->scheduled_start_time->format('M j, g:i A') }}
                            @endif
                        </a>
                    @endif
                    <ul class="od-lesson-list">
                        @foreach($modLessons as $l)
                            <li class="{{ $l->is_completed ? 'completed' : '' }} {{ $l->id === $lesson->id ? 'active' : '' }}">
                                <a href="{{ route('student.learning.show', ['course' => $course, 'lesson' => $l]) }}">
                                    <span class="icon">
                                        @if($l->is_completed)
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        @else
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        @endif
                                    </span>
                                    {{ $l->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </aside>

        <!-- Main Content -->
        <div class="space-y-6">
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
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('student.learning.download', [$course, $lesson]) }}" class="od-btn od-btn-secondary od-btn-sm">
                            <i class="fas fa-download"></i> PDF
                        </a>
                        @if($lesson->duration_minutes)
                            <span class="od-btn od-btn-ghost od-btn-sm" style="cursor:default;">
                                <i class="fas fa-clock"></i> {{ $lesson->duration_minutes }} min
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Quiz-specific content -->
                @if($lesson->lesson_type === 'Quiz')
                    @if($lesson->quizzes->isNotEmpty())
                        @php $quiz = $lesson->quizzes->first(); @endphp
                        <div class="mb-5 p-5 rounded-xl" style="background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 20%, transparent);">
                            <h3 class="font-semibold mb-1" style="color: var(--od-navy);">{{ $quiz->title }}</h3>
                            @if($quiz->description)
                                <p class="text-sm mb-3" style="color: var(--od-navy); opacity: 0.8;">{{ $quiz->description }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 text-xs" style="color: var(--od-navy); opacity: 0.8;">
                                @if($quiz->time_limit_minutes)
                                    <span class="inline-flex items-center gap-1"><i class="fas fa-clock"></i> {{ $quiz->time_limit_minutes }} min</span>
                                @endif
                                @if($quiz->max_attempts)
                                    <span class="inline-flex items-center gap-1"><i class="fas fa-redo"></i> {{ $quiz->max_attempts }} attempt{{ $quiz->max_attempts > 1 ? 's' : '' }}</span>
                                @endif
                                <span class="inline-flex items-center gap-1"><i class="fas fa-percentage"></i> Pass: {{ $quiz->passing_score }}%</span>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('student.quizzes.take', $quiz) }}" class="od-btn od-btn-navy od-btn-sm">
                                    <i class="fas fa-play"></i> Start Quiz
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mb-5 p-4 rounded-xl" style="background: var(--od-fg-soft);">
                            <p class="text-sm" style="color: var(--od-muted);">This quiz lesson does not have a linked quiz yet. Check back later.</p>
                        </div>
                    @endif
                @endif

                <!-- Assignment-specific content -->
                @if($lesson->lesson_type === 'Assignment')
                    @if($lesson->assignments->isNotEmpty())
                        @php $assignment = $lesson->assignments->first(); @endphp
                        <div class="mb-5 p-5 rounded-xl" style="background: var(--od-accent-soft); border: 1px solid color-mix(in oklch, var(--od-accent) 20%, transparent);">
                            <h3 class="font-semibold mb-1" style="color: color-mix(in oklch, var(--od-accent) 70%, black);">{{ $assignment->title }}</h3>
                            @if($assignment->description)
                                <p class="text-sm mb-3" style="color: color-mix(in oklch, var(--od-accent) 60%, black);">{{ $assignment->description }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 text-xs" style="color: color-mix(in oklch, var(--od-accent) 60%, black);">
                                @if($assignment->due_date)
                                    <span class="inline-flex items-center gap-1"><i class="fas fa-calendar-alt"></i> Due: {{ $assignment->due_date->format('M d, Y') }}</span>
                                @endif
                                @if($assignment->max_points)
                                    <span class="inline-flex items-center gap-1"><i class="fas fa-star"></i> {{ $assignment->max_points }} points</span>
                                @endif
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('student.assignments.show', [$course, $assignment]) }}" class="od-btn od-btn-primary od-btn-sm">
                                    <i class="fas fa-external-link-alt"></i> View Assignment
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mb-5 p-4 rounded-xl" style="background: var(--od-fg-soft);">
                            <p class="text-sm" style="color: var(--od-muted);">This assignment lesson does not have a linked assignment yet. Check back later.</p>
                        </div>
                    @endif
                @endif

                <!-- Lesson Content -->
                @if($lesson->content)
                    <div class="od-lesson-content text-gray-700 dark:text-gray-300">
                        {!! \App\Services\HtmlSanitizer::clean($lesson->content) !!}
                    </div>
                @endif

                <!-- Resources -->
                @if($lesson->resources->isNotEmpty())
                    <div class="mt-6 pt-6" style="border-top: 1px solid var(--od-border);">
                        <h3 class="text-sm font-semibold mb-3 flex items-center gap-2" style="color: var(--od-fg);">
                            <i class="fas fa-paperclip" style="color: var(--od-muted);"></i> Lesson Resources
                        </h3>
                        @foreach($lesson->resources as $resource)
                            <a href="{{ route('student.learning.resources.download', [$course, $lesson, $resource]) }}"
                               class="od-resource">
                                <i class="fas fa-file" style="color: var(--od-navy);"></i>
                                <span class="flex-1 truncate">{{ $resource->title }}</span>
                                <span class="od-meta hidden sm:inline">{{ strtoupper($resource->resource_type) }} · {{ $resource->file_size_kb }} KB</span>
                                <i class="fas fa-download" style="color: var(--od-muted);"></i>
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Notes Button -->
                <div class="mt-5 flex items-center gap-3">
                    <a href="{{ route('student.notes.show', [$course, $lesson]) }}" class="od-btn od-btn-ghost od-btn-sm">
                        <i class="fas fa-sticky-note"></i> Take Notes
                    </a>
                </div>
            </div>

            <!-- Module Quiz call-to-action -->
            @if($moduleQuiz)
                @php
                    $mqAccent = $moduleQuizState['passed'] ? 'var(--od-green)' : ($moduleQuizState['locked'] ? 'var(--od-muted)' : 'var(--od-accent)');
                @endphp
                <div class="od-card mt-5" style="border-left: 4px solid {{ $mqAccent }};">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex items-center justify-center rounded-lg shrink-0"
                                  style="width:42px;height:42px;background:var(--od-navy-soft);color:var(--od-navy);">
                                <i class="fas fa-clipboard-question"></i>
                            </span>
                            <div>
                                <p class="od-eyebrow" style="margin:0 0 2px;">{{ $lesson->module->title }}</p>
                                <h3 class="od-h3" style="margin:0;">{{ $moduleQuiz->title }}</h3>
                                <p class="od-meta" style="margin-top:2px;">
                                    Pass {{ $moduleQuiz->passing_score ?? 60 }}%
                                    @if($moduleQuiz->time_limit_minutes) · {{ $moduleQuiz->time_limit_minutes }} min @endif
                                    @if($moduleQuizState['best_score'] !== null)
                                        · Best: <strong style="color: {{ $moduleQuizState['passed'] ? 'var(--od-green)' : 'var(--od-fg)' }};">{{ $moduleQuizState['best_score'] }}%</strong>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if($moduleQuizState['locked'])
                                <span class="od-btn od-btn-secondary od-btn-sm" style="pointer-events:none;opacity:.7;">
                                    <i class="fas fa-lock"></i> Finish {{ $moduleQuizState['remaining_lessons'] }} more lesson{{ $moduleQuizState['remaining_lessons'] !== 1 ? 's' : '' }}
                                </span>
                            @elseif($moduleQuizState['passed'])
                                <div class="flex items-center gap-2">
                                    <span class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium" style="background: var(--od-green-soft); color: var(--od-green);">
                                        <i class="fas fa-circle-check"></i> Passed
                                    </span>
                                    @if($moduleQuizState['can_retake'])
                                        <a href="{{ route('student.quizzes.take', $moduleQuiz) }}" class="od-btn od-btn-ghost od-btn-sm">Retake</a>
                                    @endif
                                </div>
                            @elseif($moduleQuizState['can_retake'])
                                <a href="{{ route('student.quizzes.take', $moduleQuiz) }}" class="od-btn od-btn-navy od-btn-sm">
                                    <i class="fas fa-play"></i> {{ $moduleQuizState['attempts_count'] > 0 ? 'Retake Quiz' : 'Take the Quiz' }}
                                </a>
                            @else
                                <a href="{{ route('student.quizzes.attempts', $moduleQuiz) }}" class="od-btn od-btn-ghost od-btn-sm">View Attempts</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Lesson Navigation -->
            <div class="od-lesson-nav">
                @if($prevLesson)
                    <a href="{{ route('student.learning.show', ['course' => $course, 'lesson' => $prevLesson]) }}" class="od-btn od-btn-secondary od-btn-sm">
                        <i class="fas fa-arrow-left"></i> Previous
                    </a>
                @else
                    <span></span>
                @endif

                @if($enrollment->isInPerson())
                    {{-- In-person learners are assessed and progressed by their instructor in class. --}}
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium" style="background: var(--od-navy-soft); color: var(--od-navy);">
                        <i class="fas fa-chalkboard-teacher"></i> In-person class — your instructor records your progress
                    </div>
                @elseif(!$lesson->is_completed && !in_array($lesson->lesson_type, ['Quiz', 'Assignment']))
                    <form action="{{ route('student.learning.complete', ['course' => $course, 'lesson' => $lesson]) }}" method="POST" id="completeForm">
                        @csrf
                        <button type="submit" class="od-btn od-btn-success od-btn-sm">
                            <i class="fas fa-check"></i> Mark as Complete
                        </button>
                    </form>
                @elseif($lesson->is_completed)
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium" style="background: var(--od-green-soft); color: var(--od-green);">
                        <i class="fas fa-check-circle"></i> Lesson Completed
                    </div>
                @endif

                @if($nextLesson)
                    <a href="{{ route('student.learning.show', ['course' => $course, 'lesson' => $nextLesson]) }}" class="od-btn od-btn-primary od-btn-sm">
                        Next <i class="fas fa-arrow-right"></i>
                    </a>
                @else
                    <span></span>
                @endif
            </div>
        </div>
    </main>
</div>

@php
$hasReviewedCourse = $progress >= 100 && auth()->check()
    ? \App\Models\Testimonial::where('user_id', auth()->id())->where('course_id', $course->id)->exists()
    : true;
@endphp

<!-- Celebration Overlay (shown when course is completed) -->
@if(session('success') && str_contains(session('success'), 'complete') && $progress >= 100)
<div class="od-celebrate show" id="celebrateOverlay">
    <div class="od-celebrate-card">
        <div class="od-celebrate-icon">
            <i class="fas fa-trophy text-2xl"></i>
        </div>
        <h2 class="od-h2 mb-2">Course Complete!</h2>
        <p class="od-lead mb-6" style="margin: 0 auto 24px;">Congratulations on completing <strong>{{ $course->title }}</strong>. You've earned a certificate!</p>
        <div class="flex flex-col gap-3">
            <a href="{{ route('student.certificates') }}" class="od-btn od-btn-primary">
                <i class="fas fa-certificate"></i> View Certificate
            </a>
            @if(!$hasReviewedCourse)
            <a href="{{ route('student.testimonials.create', $enrollment) }}" class="od-btn od-btn-secondary">
                <i class="fas fa-star"></i> Write a Review
            </a>
            @endif
            <a href="{{ route('student.dashboard') }}" class="od-btn od-btn-ghost">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Auto-hide celebration overlay on click outside
    document.getElementById('celebrateOverlay')?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('show');
    });

    // Close sidebar on mobile when clicking a lesson link
    document.querySelectorAll('.od-learn-sidebar a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                document.getElementById('learnSidebar').classList.remove('open');
            }
        });
    });

    // Remember and restore scroll position inside a lesson so students continue
    // reading exactly where they left off when they come back.
    // The dashboard layout scrolls on <main>, not on window.
    (function () {
        const key = 'edutrack-lesson-scroll-' + {{ $course->id }} + '-' + {{ $lesson->id }};
        const scroller = document.querySelector('main') || window;

        function currentScroll() {
            return scroller === window
                ? (window.scrollY || document.documentElement.scrollTop)
                : scroller.scrollTop;
        }

        function saveScroll() {
            try {
                localStorage.setItem(key, String(currentScroll()));
            } catch (e) {}
        }

        function restoreScroll() {
            try {
                const saved = localStorage.getItem(key);
                if (saved !== null) {
                    const top = parseInt(saved, 10);
                    if (scroller === window) {
                        window.scrollTo({ top: top, behavior: 'instant' });
                    } else {
                        scroller.scrollTop = top;
                    }
                }
            } catch (e) {}
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(restoreScroll, 150);
            });
        } else {
            setTimeout(restoreScroll, 150);
        }

        let scrollTimer;
        scroller.addEventListener('scroll', function () {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(saveScroll, 250);
        }, { passive: true });

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') saveScroll();
        });
        window.addEventListener('beforeunload', saveScroll);
    })();
</script>
@endpush
