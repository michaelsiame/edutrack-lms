@extends('layouts.dashboard')

@section('title', 'My Profile - Edutrack LMS')
@section('page_title', 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <!-- Profile Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-3xl text-primary-600 dark:text-primary-400 font-bold">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="w-20 h-20 rounded-full object-cover">
                    @else
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->full_name }}</h2>
                    <p class="text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1 capitalize">
                        {{ $user->roles->first()?->role?->role_name ?? 'Student' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Personal Information</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Phone</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->phone ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Date of Birth</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $profile->date_of_birth?->format('M d, Y') ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Gender</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $profile->gender ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Address</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $profile->address ?? 'Not set' }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Professional</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Occupation</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $profile->occupation ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Company</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $profile->company ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">LinkedIn</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                @if($profile->linkedin_url)
                                    <a href="{{ $profile->linkedin_url }}" target="_blank" class="text-primary-600 hover:underline">View Profile</a>
                                @else
                                    Not set
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($profile->bio)
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Bio</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $profile->bio }}</p>
                </div>
            @endif

            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
                    <i class="fas fa-edit mr-2"></i>Edit Profile
                </a>
                <a href="{{ route('transcript.download') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                    <i class="fas fa-file-pdf mr-2"></i>Download Transcript
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
