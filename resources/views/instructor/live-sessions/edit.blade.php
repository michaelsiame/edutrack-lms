@extends('layouts.dashboard')

@section('title','Edit Live Session - ' . $course->title)
@section('page_title','Edit Live Session')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('instructor.live-sessions.index', $course) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i>Back to Live Sessions
        </a>
    </div>

    <div class="od-card p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Edit Live Session</h2>

        <form action="{{ route('instructor.live-sessions.update', [$course, $session]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lesson</label>
                    <select name="lesson_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        @foreach($course->lessons as $lesson)
                            <option value="{{ $lesson->id }}" {{ $session->lesson_id == $lesson->id ? 'selected' : '' }}>
                                {{ $lesson->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Room ID</label>
                    <input type="text" name="meeting_room_id" value="{{ $session->meeting_room_id }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">External meeting link (optional)</label>
                    <input type="url" name="meeting_url" value="{{ $session->meeting_url }}" placeholder="https://meet.google.com/..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-400 mt-1">If set, students join this link instead of the built-in room.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Time</label>
                    <input type="datetime-local" name="scheduled_start_time" required
                           value="{{ $session->scheduled_start_time->format('Y-m-d\TH:i') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Time</label>
                    <input type="datetime-local" name="scheduled_end_time" required
                           value="{{ $session->scheduled_end_time->format('Y-m-d\TH:i') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="What will this session cover?"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">{{ $session->description }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Participants</label>
                <input type="number" name="max_participants" value="{{ $session->max_participants }}" min="1"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            </div>

            <div class="flex items-center gap-4 mb-6">
                <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="enable_chat" value="1" {{ $session->enable_chat ? 'checked' : '' }} class="mr-2 rounded">
                    Enable Chat
                </label>
                <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="enable_screen_share" value="1" {{ $session->enable_screen_share ? 'checked' : '' }} class="mr-2 rounded">
                    Screen Share
                </label>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="od-btn od-btn-primary od-btn-sm font-medium text-sm">Update Session</button>
                <a href="{{ route('instructor.live-sessions.index', $course) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
