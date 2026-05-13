@extends('layouts.dashboard')

@section('title', 'Join Live Session')
@section('page_title', 'Live Session')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ url()->previous() }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 text-center">
        <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-video text-green-600 dark:text-green-400 text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Join Live Session</h2>
        <p class="text-gray-500 mb-6">{{ $session->description ?: 'Live class session' }}</p>

        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6 text-left max-w-sm mx-auto">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-gray-500">Room ID:</span>
                <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $session->meeting_room_id }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Your Name:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ auth()->user()->full_name }}</span>
            </div>
        </div>

        <a href="https://meet.jit.si/{{ $session->meeting_room_id }}#config.startWithAudioMuted=true&config.startWithVideoMuted=true&userInfo.displayName={{ urlencode(auth()->user()->full_name) }}"
            target="_blank"
            class="inline-flex items-center px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold text-lg transition-colors">
            <i class="fas fa-sign-in-alt mr-2"></i>Join Now
        </a>

        <p class="text-xs text-gray-400 mt-4">Powered by Jitsi Meet</p>
    </div>
</div>
@endsection
