@extends('layouts.dashboard')

@section('title','Join Live Session')
@section('page_title','Live Session')

@section('content')
<div class="max-w-md mx-auto">
    <x-back-link route="enrollments.show" :routeParams="[$session->course ?? $course]" label="Back to Course" class="mb-6" />

    <x-card variant="elevated" class="text-center py-10">
        <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-success-50 dark:bg-success-900/20 flex items-center justify-center">
            <i class="fas fa-video text-success-500 text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Join Live Session</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-8">{{ $session->description ?: 'Live class session' }}</p>

        <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-5 mb-8 text-left border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between text-sm mb-3">
                <span class="text-gray-500 dark:text-gray-400">Room ID</span>
                <span class="font-mono font-medium text-gray-900 dark:text-white bg-white dark:bg-gray-800 px-2 py-1 rounded-lg border border-gray-200 dark:border-gray-600">{{ $session->meeting_room_id }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">Your Name</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ auth()->user()->full_name }}</span>
            </div>
        </div>

        <x-button :href="'https://meet.jit.si/' . $session->meeting_room_id . '#config.startWithAudioMuted=true&config.startWithVideoMuted=true&userInfo.displayName=' . urlencode(auth()->user()->full_name)"
            variant="success" size="lg" icon="fa-sign-in-alt" target="_blank" class="w-full justify-center">
            Join Now
        </x-button>

        <p class="text-xs text-gray-400 dark:text-gray-500 mt-5">Powered by Jitsi Meet</p>
    </x-card>
</div>
@endsection
