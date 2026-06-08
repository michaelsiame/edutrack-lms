@extends('layouts.dashboard')

@section('title','My Profile - Edutrack LMS')
@section('page_title','My Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-4xl mx-auto">
        <div class="od-card overflow-hidden" style="padding: 0;">
            <!-- Profile Header -->
            <div class="p-6" style="border-bottom: 1px solid var(--od-border);">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-bold" style="background: var(--od-navy-soft); color: var(--od-navy);">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="w-20 h-20 rounded-2xl object-cover">
                        @else
                            {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <h2 class="od-h2">{{ $user->full_name }}</h2>
                        <p class="od-meta">{{ $user->email }}</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1 od-num" style="background: var(--od-navy-soft); color: var(--od-navy);">
                            {{ $user->roles->first()?->role?->role_name ??'Student' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="od-eyebrow mb-3">Personal Information</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <dt style="color: var(--od-muted);">Phone</dt>
                                <dd class="font-medium" style="color: var(--od-fg);">{{ $user->phone ??'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt style="color: var(--od-muted);">NRC Number</dt>
                                <dd class="font-medium" style="color: var(--od-fg);">{{ $profile->nrc_number ??'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt style="color: var(--od-muted);">Date of Birth</dt>
                                <dd class="font-medium" style="color: var(--od-fg);">{{ $profile->date_of_birth?->format('M d, Y') ??'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt style="color: var(--od-muted);">Gender</dt>
                                <dd class="font-medium capitalize" style="color: var(--od-fg);">{{ $profile->gender ??'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt style="color: var(--od-muted);">Address</dt>
                                <dd class="font-medium" style="color: var(--od-fg);">{{ $profile->address ??'Not set' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="od-eyebrow mb-3">Professional</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <dt style="color: var(--od-muted);">Occupation</dt>
                                <dd class="font-medium" style="color: var(--od-fg);">{{ $profile->occupation ??'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt style="color: var(--od-muted);">Company</dt>
                                <dd class="font-medium" style="color: var(--od-fg);">{{ $profile->company ??'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt style="color: var(--od-muted);">LinkedIn</dt>
                                <dd class="font-medium">
                                    @if($profile->linkedin_url)
                                        <a href="{{ $profile->linkedin_url }}" target="_blank" class="font-medium" style="color: var(--od-navy);">View Profile</a>
                                    @else
                                        <span style="color: var(--od-muted);">Not set</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if($profile->bio)
                    <div class="mt-6">
                        <h3 class="od-eyebrow mb-2">Bio</h3>
                        <p class="text-sm" style="color: var(--od-muted);">{{ $profile->bio }}</p>
                    </div>
                @endif

                <div class="mt-6 pt-6 flex flex-wrap gap-3" style="border-top: 1px solid var(--od-border);">
                    <a href="{{ route('profile.edit') }}" class="od-btn od-btn-primary">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                    <a href="{{ route('transcript.download') }}" class="od-btn od-btn-secondary">
                        <i class="fas fa-file-pdf mr-2"></i>Download Transcript
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
