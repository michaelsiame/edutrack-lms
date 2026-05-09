@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Lesson Video/Content -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if($lesson->video_url)
                    <div class="aspect-video bg-black">
                        <iframe src="{{ $lesson->video_url }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                    </div>
                @else
                    <div class="aspect-video bg-gray-100 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-book-open text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-500">Text-based lesson</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Lesson Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $lesson->title }}</h1>
                        <p class="text-gray-600 mt-1">{{ $lesson->module->course->title }} / {{ $lesson->module->title }}</p>
                    </div>
                    @if($lesson->duration_minutes)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-clock mr-1"></i> {{ $lesson->duration_minutes }} min
                        </span>
                    @endif
                </div>

                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($lesson->content)) !!}
                </div>

                @if($lesson->attachments)
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Attachments</h3>
                        <a href="{{ $lesson->attachments }}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                            <i class="fas fa-download mr-2"></i> Download Resource
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Progress -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Course Progress</h3>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progress ?? 0 }}%"></div>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ $progress ?? 0 }}% Complete</p>
            </div>

            <!-- Module Navigation -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h3 class="font-medium text-gray-900">Course Content</h3>
                </div>
                <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                    @foreach($modules as $mod)
                        <div class="p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">{{ $mod->title }}</h4>
                            <div class="space-y-1">
                                @foreach($mod->lessons as $l)
                                    <a href="{{ route('student.learning.show', ['course' => $course, 'lesson' => $l]) }}" 
                                       class="flex items-center px-2 py-1.5 text-sm rounded {{ $l->id === $lesson->id ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                                        @if($l->is_completed)
                                            <i class="fas fa-check-circle text-green-500 mr-2 w-4"></i>
                                        @elseif($l->video_url)
                                            <i class="fas fa-play-circle text-gray-400 mr-2 w-4"></i>
                                        @else
                                            <i class="fas fa-file-alt text-gray-400 mr-2 w-4"></i>
                                        @endif
                                        {{ $l->title }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Mark Complete -->
            @if(!$lesson->is_completed)
                <form action="{{ route('student.learning.complete', ['course' => $course, 'lesson' => $lesson]) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 font-medium">
                        <i class="fas fa-check mr-2"></i>Mark as Complete
                    </button>
                </form>
            @else
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
                    <p class="text-green-800 font-medium">Lesson Completed</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
