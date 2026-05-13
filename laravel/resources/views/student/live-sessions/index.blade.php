@extends('layouts.dashboard')

@section('title', 'Live Sessions - ' . $course->title)
@section('page_title', 'Live Sessions')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('enrollments.show', $course) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i>Back to Course
        </a>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Live Sessions</h2>
            <p class="text-sm text-gray-500">{{ $course->title }}</p>
        </div>
    </div>

    <!-- Live Now Banner -->
    @php $liveSession = $sessions->firstWhere('status', 'live'); @endphp
    @if($liveSession)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-red-600 rounded-full animate-pulse"></div>
                    <div>
                        <h3 class="font-bold text-red-800">LIVE NOW</h3>
                        <p class="text-sm text-red-600">{{ $liveSession->description }}</p>
                    </div>
                </div>
                <a href="{{ route('student.live-sessions.join', $liveSession) }}" target="_blank"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                    <i class="fas fa-video mr-2"></i>Join Now
                </a>
            </div>
        </div>
    @endif

    <!-- Sessions List -->
    <div class="space-y-4">
        @forelse($sessions as $session)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            @if($session->isLive())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <span class="w-1.5 h-1.5 bg-red-600 rounded-full mr-1 animate-pulse"></span>LIVE
                                </span>
                            @elseif($session->isUpcoming())
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full font-medium">Upcoming</span>
                            @else
                                <span class="text-xs bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full font-medium">Completed</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $session->scheduled_start_time->format('M j, Y g:i A') }}</span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $session->description ?: 'Live class session' }}</p>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                            <span><i class="fas fa-clock mr-1"></i>{{ $session->scheduled_start_time->diffInMinutes($session->scheduled_end_time) }} min</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($session->isLive())
                            <a href="{{ route('student.live-sessions.join', $session) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 font-medium">
                                <i class="fas fa-video mr-1"></i>Join
                            </a>
                        @elseif($session->isUpcoming())
                            <span class="px-4 py-2 bg-gray-100 text-gray-500 text-sm rounded-lg font-medium cursor-not-allowed">
                                Starts {{ $session->scheduled_start_time->diffForHumans() }}
                            </span>
                        @else
                            @if($session->recording_url)
                                <a href="{{ $session->recording_url }}" target="_blank" class="px-4 py-2 bg-blue-100 text-blue-700 text-sm rounded-lg hover:bg-blue-200 font-medium">
                                    <i class="fas fa-play mr-1"></i>Recording
                                </a>
                            @else
                                <span class="px-4 py-2 bg-gray-100 text-gray-500 text-sm rounded-lg font-medium">Ended</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-video text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">No Live Sessions</h3>
                <p class="text-gray-500 text-sm mt-1">Your instructor hasn't scheduled any live sessions yet.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
