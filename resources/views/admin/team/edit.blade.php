@extends('layouts.dashboard')

@section('title', 'Edit Team Member - Admin')
@section('page_title', 'Edit Team Member')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('admin.team.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Team Members
        </a>
    </div>

    <div class="od-card" style="padding: 0; overflow: hidden;">
        <!-- Photo Preview -->
        <div class="h-56 bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
            @if($member->image_url && file_exists(public_path('uploads/team/' . $member->image_url)))
            <img src="{{ asset('uploads/team/' . $member->image_url) }}" alt="{{ $member->name }}" class="max-h-full max-w-full object-contain">
            @else
            <div class="text-center text-gray-400">
                <i class="fas fa-user text-4xl mb-2"></i>
                <p class="text-sm">No photo uploaded</p>
            </div>
            @endif
        </div>

        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5">Edit Team Member</h3>

            <form action="{{ route('admin.team.update', $member) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <!-- Linked User -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Linked User <span class="text-gray-400 font-normal">(optional)</span></label>
                        <select name="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">-- No linked user --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $member->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name ?? $user->name }} ({{ $user->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $member->name) }}" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Position -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Position</label>
                        <input type="text" name="position" value="{{ old('position', $member->position) }}" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                    </div>

                    <!-- Qualifications -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Qualifications <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea name="qualifications" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">{{ old('qualifications', $member->qualifications) }}</textarea>
                    </div>

                    <!-- Display Order -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Display Order</label>
                        <input type="number" name="display_order" value="{{ old('display_order', $member->display_order) }}" min="0"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lower numbers appear first on the About page</p>
                    </div>

                    <!-- Photo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Replace Photo <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="file" name="image" accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty to keep current photo. Recommended: 400x500px portrait.</p>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('admin.team.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm transition-colors">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
