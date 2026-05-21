@extends('layouts.dashboard')

@section('title','Live Sessions -' . $course->title)
@section('page_title','Live Sessions')

@section('content')
<div class="max-w-4xl mx-auto">
 <div class="mb-4">
 <a href="{{ route('instructor.courses.show', $course) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
 <i class="fas fa-arrow-left mr-1"></i>Back to Course
 </a>
 </div>

 <div class="flex items-center justify-between mb-6">
 <div>
 <h2 class="text-xl font-bold text-gray-900 dark:text-white">Live Sessions</h2>
 <p class="text-sm text-gray-500">{{ $course->title }}</p>
 </div>
 <button onclick="toggleCreateForm()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm">
 <i class="fas fa-plus mr-1"></i>Schedule Session
 </button>
 </div>

 @if(session('success'))
 <div class="mb-4 p-4 bg-success-50 border border-success-200 rounded-lg text-success-700">{{ session('success') }}</div>
 @endif
 @if(session('error'))
 <div class="mb-4 p-4 bg-danger-50 border border-danger-200 rounded-lg text-danger-700">{{ session('error') }}</div>
 @endif

 <!-- Create Form -->
 <div id="create-form" class="hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-6">
 <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Schedule New Live Session</h3>
 <form action="{{ route('instructor.live-sessions.store', $course) }}" method="POST">
 @csrf
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lesson</label>
 <select name="lesson_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 @foreach($course->lessons as $lesson)
 <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
 @endforeach
 </select>
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Room ID</label>
 <input type="text" name="meeting_room_id" value="edutrack-{{ $course->id }}-{{ now()->format('Ymd') }}" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Time</label>
 <input type="datetime-local" name="scheduled_start_time" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Time</label>
 <input type="datetime-local" name="scheduled_end_time" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 </div>
 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
 <textarea name="description" rows="2" placeholder="What will this session cover?"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"></textarea>
 </div>
 <div class="flex items-center gap-4 mb-4">
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="enable_chat" value="1" checked class="mr-2 rounded">
 Enable Chat
 </label>
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="enable_screen_share" value="1" checked class="mr-2 rounded">
 Screen Share
 </label>
 </div>
 <div class="flex gap-2">
 <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm">Schedule</button>
 <button type="button" onclick="toggleCreateForm()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm">Cancel</button>
 </div>
 </form>
 </div>

 <!-- Sessions List -->
 <div class="space-y-4">
 @forelse($sessions as $session)
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
 <div class="flex items-start justify-between">
 <div class="flex-1">
 <div class="flex items-center gap-2 mb-2">
 @if($session->isLive())
 <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800">
 <span class="w-1.5 h-1.5 bg-danger-600 rounded-full mr-1 animate-pulse"></span>LIVE
 </span>
 @elseif($session->isUpcoming())
 <span class="text-xs bg-primary-100 text-primary-800 px-2 py-0.5 rounded-full font-medium">Upcoming</span>
 @else
 <span class="text-xs bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full font-medium">Completed</span>
 @endif
 <span class="text-xs text-gray-400">{{ $session->scheduled_start_time->format('M j, Y g:i A') }}</span>
 </div>
 <p class="text-sm text-gray-700 dark:text-gray-300">{{ $session->description }}</p>
 <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
 <span><i class="fas fa-clock mr-1"></i>{{ $session->scheduled_start_time->diffInMinutes($session->scheduled_end_time) }} min</span>
 <span><i class="fas fa-users mr-1"></i>{{ $session->max_participants ??'Unlimited' }}</span>
 <span><i class="fas fa-video mr-1"></i>{{ $session->meeting_room_id }}</span>
 </div>
 </div>
 <div class="flex items-center gap-2">
 @if($session->isLive() || $session->isUpcoming())
 <a href="{{ route('student.live-sessions.join', $session) }}" target="_blank" class="px-3 py-1.5 bg-success-600 text-white text-xs rounded-lg hover:bg-success-700 font-medium">
 <i class="fas fa-video mr-1"></i>Join
 </a>
 @endif
 <form action="{{ route('instructor.live-sessions.destroy', [$course, $session]) }}" method="POST" onsubmit="return confirm('Delete this session?')">
 @csrf @method('DELETE')
 <button type="submit" class="px-3 py-1.5 bg-danger-100 text-danger-700 text-xs rounded-lg hover:bg-danger-200 font-medium">
 <i class="fas fa-trash"></i>
 </button>
 </form>
 </div>
 </div>
 </div>
 @empty
 <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
 <i class="fas fa-video text-4xl text-gray-300 mb-4"></i>
 <h3 class="text-lg font-medium text-gray-900 dark:text-white">No Live Sessions</h3>
 <p class="text-gray-500 text-sm mt-1">Schedule your first live class for this course.</p>
 </div>
 @endforelse
 </div>
</div>

<script>
function toggleCreateForm() {
 document.getElementById('create-form').classList.toggle('hidden');
}
</script>
@endsection
