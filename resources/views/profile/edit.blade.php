@extends('layouts.dashboard')

@section('title','Edit Profile - Edutrack LMS')
@section('page_title','Edit Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-3xl mx-auto">
        <div class="od-card p-6">
            <h2 class="od-h2 mb-6">Edit Profile</h2>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Avatar -->
                    <div>
                        <label class="od-form-label">Profile Photo</label>
                        <div class="flex items-center gap-4 mt-1">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-bold" style="background: var(--od-accent-soft); color: var(--od-accent);">
                                @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="" class="w-16 h-16 rounded-full object-cover">
                                @else
                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                @endif
                            </div>
                            <input type="file" name="avatar" accept="image/*"
                                class="text-sm od-input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:transition-colors"
                                style="background: transparent;">
                        </div>
                        @error('avatar')
                            <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="od-form-label">First Name</label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" required
                                class="od-input">
                            @error('first_name')
                                <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="last_name" class="od-form-label">Last Name</label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" required
                                class="od-input">
                            @error('last_name')
                                <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="od-form-label">Phone</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                class="od-input">
                            @error('phone')
                                <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="date_of_birth" class="od-form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $profile->date_of_birth?->format('Y-m-d')) }}"
                                class="od-input">
                            @error('date_of_birth')
                                <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="gender" class="od-form-label">Gender</label>
                        <select name="gender" id="gender" class="od-input">
                            <option value="">-- Select --</option>
                            <option value="male" {{ old('gender', $profile->gender) ==='male' ?'selected' :'' }}>Male</option>
                            <option value="female" {{ old('gender', $profile->gender) ==='female' ?'selected' :'' }}>Female</option>
                            <option value="other" {{ old('gender', $profile->gender) ==='other' ?'selected' :'' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="address" class="od-form-label">Address</label>
                        <input type="text" name="address" id="address" value="{{ old('address', $profile->address) }}"
                            class="od-input">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="city" class="od-form-label">City</label>
                            <input type="text" name="city" id="city" value="{{ old('city', $profile->city) }}"
                                class="od-input">
                        </div>
                        <div>
                            <label for="country" class="od-form-label">Country</label>
                            <input type="text" name="country" id="country" value="{{ old('country', $profile->country) }}"
                                class="od-input">
                        </div>
                        <div>
                            <label for="postal_code" class="od-form-label">Postal Code</label>
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $profile->postal_code) }}"
                                class="od-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="occupation" class="od-form-label">Occupation</label>
                            <input type="text" name="occupation" id="occupation" value="{{ old('occupation', $profile->occupation) }}"
                                class="od-input">
                        </div>
                        <div>
                            <label for="company" class="od-form-label">Company</label>
                            <input type="text" name="company" id="company" value="{{ old('company', $profile->company) }}"
                                class="od-input">
                        </div>
                    </div>

                    <div>
                        <label for="bio" class="od-form-label">Bio</label>
                        <textarea name="bio" id="bio" rows="3" class="od-input">{{ old('bio', $profile->bio) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="linkedin_url" class="od-form-label">LinkedIn URL</label>
                            <input type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url) }}"
                                class="od-input" placeholder="https://linkedin.com/in/...">
                        </div>
                        <div>
                            <label for="twitter_url" class="od-form-label">Twitter URL</label>
                            <input type="url" name="twitter_url" id="twitter_url" value="{{ old('twitter_url', $profile->twitter_url) }}"
                                class="od-input" placeholder="https://twitter.com/...">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-4">
                        <button type="submit" class="od-btn od-btn-primary">
                            Save Changes
                        </button>
                        <a href="{{ route('profile.show') }}" class="od-btn od-btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
