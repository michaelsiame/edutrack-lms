@extends('layouts.dashboard')

@section('title', $lesson->title . ' - ' . $course->title)
@section('page_title', $lesson->title)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Lesson Video / Type Header -->
            <x-card :padding="false" class="overflow-hidden shadow-md">
                @if($lesson->lesson_type === 'Video')
                    @if($lesson->embedUrl())
                        <div class="aspect-video bg-black">
                            <iframe src="{{ $lesson->embedUrl() }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen title="Lesson video: {{ $lesson->title }}"></iframe>
                        </div>
                    @else
                        <div class="aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                    <i class="fas fa-video text-2xl text-gray-400 dark:text-gray-500"></i>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">No video URL set for this lesson.</p>
                            </div>
                        </div>
                    @endif
                @elseif($lesson->lesson_type === 'Quiz')
                    <div class="aspect-video bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/20 flex items-center justify-center">
                        <div class="text-center px-6">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-primary-100 dark:bg-primary-800 flex items-center justify-center">
                                <i class="fas fa-clipboard-list text-2xl text-primary-600 dark:text-primary-400"></i>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 font-medium">Quiz Lesson</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Test your knowledge with this quiz.</p>
                        </div>
                    </div>
                @elseif($lesson->lesson_type === 'Assignment')
                    <div class="aspect-video bg-gradient-to-br from-warning-50 to-warning-100 dark:from-warning-900/30 dark:to-warning-800/20 flex items-center justify-center">
                        <div class="text-center px-6">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-warning-100 dark:bg-warning-800 flex items-center justify-center">
                                <i class="fas fa-tasks text-2xl text-warning-600 dark:text-warning-400"></i>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 font-medium">Assignment Lesson</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Complete the assigned task and submit your work.</p>
                        </div>
                    </div>
                @else
                    <div class="aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                <i class="fas fa-book-open text-2xl text-gray-400 dark:text-gray-500"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">Reading Lesson</p>
                        </div>
                    </div>
                @endif
            </x-card>

            <!-- Lesson Info -->
            <x-card variant="default">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                            <span class="font-medium text-primary-600 dark:text-primary-400">{{ $lesson->module->course->title }}</span>
                            <span class="mx-1">/</span>
                            <span>{{ $lesson->module->title }}</span>
                        </p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('student.learning.download', [$course, $lesson]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <i class="fas fa-download"></i>Download PDF
                        </a>
                    </div>
                    @if($lesson->duration_minutes)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 shrink-0 self-start">
                            <i class="fas fa-clock text-gray-400"></i>
                            {{ $lesson->duration_minutes }} min
                        </span>
                    @endif
                </div>

                <!-- Quiz-specific content -->
                @if($lesson->lesson_type === 'Quiz')
                    @if($lesson->quizzes->isNotEmpty())
                        @php $quiz = $lesson->quizzes->first(); @endphp
                        <div class="mb-5 p-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl">
                            <h3 class="font-semibold text-primary-800 dark:text-primary-300 mb-1">{{ $quiz->title }}</h3>
                            @if($quiz->description)
                                <p class="text-sm text-primary-700 dark:text-primary-400 mb-3">{{ $quiz->description }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 text-xs text-primary-700 dark:text-primary-400">
                                @if($quiz->time_limit_minutes)
                                    <span class="inline-flex items-center gap-1"><i class="fas fa-clock"></i> {{ $quiz->time_limit_minutes }} min</span>
                                @endif
                                @if($quiz->max_attempts)
                                    <span class="inline-flex items-center gap-1"><i class="fas fa-redo"></i> {{ $quiz->max_attempts }} attempt{{ $quiz->max_attempts > 1 ? 's' : '' }}</span>
                                @endif
                                <span class="inline-flex items-center gap-1"><i class="fas fa-percentage"></i> Pass: {{ $quiz->passing_score }}%</span>
                            </div>
                            <div class="mt-4">
                                <x-button :href="route('student.quizzes.take', $quiz)" variant="primary" icon="fa-play" size="md">
                                    Start Quiz
                                </x-button>
                            </div>
                        </div>
                    @else
                        <div class="mb-5 p-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl">
                            <p class="text-sm text-gray-600 dark:text-gray-400">This quiz lesson does not have a linked quiz yet. Check back later.</p>
                        </div>
                    @endif
                @endif

                <!-- Assignment-specific content -->
                @if($lesson->lesson_type === 'Assignment')
                    @if($lesson->assignments->isNotEmpty())
                        @php $assignment = $lesson->assignments->first(); @endphp
                        <div class="mb-5 p-4 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-xl">
                            <h3 class="font-semibold text-warning-800 dark:text-warning-300 mb-1">{{ $assignment->title }}</h3>
                            @if($assignment->description)
                                <p class="text-sm text-warning-700 dark:text-warning-400 mb-3">{{ $assignment->description }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 text-xs text-warning-700 dark:text-warning-400">
                                @if($assignment->due_date)
                                    <span class="inline-flex items-center gap-1"><i class="fas fa-calendar-alt"></i> Due: {{ $assignment->due_date->format('M d, Y') }}</span>
                                @endif
                                @if($assignment->max_points)
                                    <span class="inline-flex items-center gap-1"><i class="fas fa-star"></i> {{ $assignment->max_points }} points</span>
                                @endif
                            </div>
                            <div class="mt-4">
                                <x-button :href="route('student.assignments.show', [$course, $assignment])" variant="warning" icon="fa-external-link-alt" size="md">
                                    View Assignment
                                </x-button>
                            </div>
                        </div>
                    @else
                        <div class="mb-5 p-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl">
                            <p class="text-sm text-gray-600 dark:text-gray-400">This assignment lesson does not have a linked assignment yet. Check back later.</p>
                        </div>
                    @endif
                @endif

                <!-- Lesson Content -->
                @if($lesson->content)
                    <div class="lesson-content text-gray-700 dark:text-gray-300">
                        {!! \App\Services\HtmlSanitizer::clean($lesson->content) !!}
                    </div>
                @endif

                @if($lesson->resources->isNotEmpty())
                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <i class="fas fa-paperclip text-gray-400"></i>Lesson Resources
                        </h3>
                        <div class="space-y-2">
                            @foreach($lesson->resources as $resource)
                                <a href="{{ route('student.learning.resources.download', [$course, $lesson, $resource]) }}"
                                   class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/30 rounded-lg flex items-center justify-center shrink-0">
                                            <i class="fas fa-file text-primary-600 dark:text-primary-400 text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $resource->title }}</p>
                                            @if($resource->description)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $resource->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:inline">{{ strtoupper($resource->resource_type) }} &middot; {{ $resource->file_size_kb }} KB</span>
                                        <x-button variant="primary" size="sm" icon="fa-download" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                            Download
                                        </x-button>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-5 flex items-center gap-3">
                    <x-button :href="route('student.notes.show', [$course, $lesson])" variant="ghost" icon="fa-sticky-note" size="sm">
                        Take Notes
                    </x-button>
                </div>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Progress -->
            <x-card variant="default">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Course Progress</h3>
                <x-progress-bar :value="$progress ?? 0" size="md" showLabel />
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $progress ?? 0 }}% Complete</p>
            </x-card>

            <!-- Module Navigation -->
            <x-card variant="default" class="overflow-hidden" :padding="false">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Course Content</h3>
                </div>
                <div class="max-h-[28rem] overflow-y-auto">
                    @foreach($modules as $mod)
                        <div class="p-4 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ $mod->title }}</h4>
                            <div class="space-y-0.5">
                                @foreach($mod->lessons as $l)
                                    <a href="{{ route('student.learning.show', ['course' => $course, 'lesson' => $l]) }}"
                                       class="flex items-center px-2.5 py-2 text-sm rounded-lg transition-colors {{ $l->id === $lesson->id ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                        @if($l->is_completed)
                                            <i class="fas fa-check-circle text-success-500 mr-2.5 w-4 text-center"></i>
                                        @elseif($l->lesson_type === 'Video')
                                            <i class="fas fa-play-circle text-gray-400 mr-2.5 w-4 text-center"></i>
                                        @elseif($l->lesson_type === 'Quiz')
                                            <i class="fas fa-clipboard-list text-gray-400 mr-2.5 w-4 text-center"></i>
                                        @elseif($l->lesson_type === 'Assignment')
                                            <i class="fas fa-tasks text-gray-400 mr-2.5 w-4 text-center"></i>
                                        @else
                                            <i class="fas fa-file-alt text-gray-400 mr-2.5 w-4 text-center"></i>
                                        @endif
                                        <span class="truncate">{{ $l->title }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <!-- Mark Complete -->
            @if(!$lesson->is_completed && !in_array($lesson->lesson_type, ['Quiz', 'Assignment']))
                <form action="{{ route('student.learning.complete', ['course' => $course, 'lesson' => $lesson]) }}" method="POST">
                    @csrf
                    <x-button type="submit" variant="success" size="lg" icon="fa-check" class="w-full justify-center">
                        Mark as Complete
                    </x-button>
                </form>
            @elseif($lesson->is_completed)
                <x-card variant="default" class="bg-success-50 dark:bg-success-900/10 border-success-200 dark:border-success-800 text-center py-5">
                    <i class="fas fa-check-circle text-success-500 text-2xl mb-2"></i>
                    <p class="text-success-800 dark:text-success-300 font-semibold text-sm">Lesson Completed</p>
                </x-card>
            @endif
        </div>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/lesson-content.css') }}">
@endpush
@endsection
