@extends('layouts.dashboard')

@section('title','My Profile - Edutrack LMS')
@section('page_title','My Profile')

@section('content')
<div class="max-w-4xl mx-auto">
 <x-card class="overflow-hidden">
 <!-- Profile Header -->
 <div class="p-6 border-b border-gray-100 dark:border-gray-700">
 <div class="flex items-center gap-4">
 <div class="w-20 h-20 rounded-2xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-3xl text-primary-600 dark:text-primary-400 font-bold">
 @if($user->avatar_url)
 <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="w-20 h-20 rounded-2xl object-cover">
 @else
 {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
 @endif
 </div>
 <div>
 <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->full_name }}</h2>
 <p class="text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400 mt-1 capitalize">
 {{ $user->roles->first()?->role?->role_name ??'Student' }}
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
 <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->phone ??'Not set' }}</dd>
 </div>
 <div class="flex justify-between">
 <dt class="text-sm text-gray-500">Date of Birth</dt>
 <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $profile->date_of_birth?->format('M d, Y') ??'Not set' }}</dd>
 </div>
 <div class="flex justify-between">
 <dt class="text-sm text-gray-500">Gender</dt>
 <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $profile->gender ??'Not set' }}</dd>
 </div>
 <div class="flex justify-between">
 <dt class="text-sm text-gray-500">Address</dt>
 <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $profile->address ??'Not set' }}</dd>
 </div>
 </dl>
 </div>

 <div>
 <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Professional</h3>
 <dl class="space-y-2">
 <div class="flex justify-between">
 <dt class="text-sm text-gray-500">Occupation</dt>
 <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $profile->occupation ??'Not set' }}</dd>
 </div>
 <div class="flex justify-between">
 <dt class="text-sm text-gray-500">Company</dt>
 <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $profile->company ??'Not set' }}</dd>
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

 <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-3">
 <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition">
 <i class="fas fa-edit mr-2"></i>Edit Profile
 </a>
 <a href="{{ route('transcript.download') }}" class="inline-flex items-center px-5 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition">
 <i class="fas fa-file-pdf mr-2"></i>Download Transcript
 </a>
 </div>
 </div>
 </x-card>
</div>
@endsection
