@extends('layouts.dashboard')

@section('title', 'Institution Photos - Admin')
@section('page_title', 'Institution Photos')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Info Banner -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-semibold mb-1">Where these photos appear</p>
                <ul class="list-disc list-inside space-y-0.5 text-blue-700 dark:text-blue-300">
                    <li><strong>Homepage Gallery</strong> — Featured photos are highlighted in the homepage carousel</li>
                    <li><strong>About Page</strong> — All active photos appear in the campus showcase section</li>
                    <li><strong>Inactive</strong> photos are hidden from public view but kept in the admin</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Duplicate Warning -->
    @if($duplicates->count() > 0)
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400 mt-0.5"></i>
            <div class="text-sm text-amber-800 dark:text-amber-200">
                <p class="font-semibold mb-1">Duplicate photos detected</p>
                <p class="text-amber-700 dark:text-amber-300">You have multiple copies of: {{ $duplicates->implode(', ') }}. Consider deleting duplicates below.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Upload Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-cloud-upload-alt text-primary-500"></i>
            Upload New Photo
        </h3>
        <form action="{{ route('admin.photos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Photo</label>
                    <input type="file" name="image" accept="image/*" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                    <input type="text" name="title" required placeholder="e.g. Main Campus Building"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                        <option value="general">General</option>
                        <option value="campus">Campus</option>
                        <option value="lab">Computer Lab</option>
                        <option value="classroom">Classroom</option>
                        <option value="event">Event</option>
                        <option value="facility">Facility</option>
                        <option value="graduation">Graduation</option>
                        <option value="workshop">Workshop</option>
                    </select>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1"
                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="ml-2">Featured</span>
                    </label>
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm transition-colors">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Photo Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($photos as $photo)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden group {{ !$photo->is_active ? 'opacity-60' : '' }}">
            <!-- Image -->
            <div class="relative h-44 overflow-hidden bg-gray-100 dark:bg-gray-900">
                <img src="{{ $photo->image_path }}" alt="{{ $photo->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @if(!$photo->is_active)
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                    <span class="text-white text-xs font-semibold px-2 py-1 bg-black/60 rounded">INACTIVE</span>
                </div>
                @endif
                @if($photo->is_featured)
                <div class="absolute top-2 left-2">
                    <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-medium">
                        <i class="fas fa-star mr-0.5"></i>Featured
                    </span>
                </div>
                @endif
            </div>

            <!-- Info -->
            <div class="p-4">
                <h4 class="font-medium text-gray-900 dark:text-white text-sm truncate" title="{{ $photo->title }}">{{ $photo->title }}</h4>
                @if($photo->category)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 capitalize">{{ $photo->category }}</p>
                @endif

                <!-- Badges Row -->
                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                    @if($photo->is_active)
                    <span class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded">Active</span>
                    @else
                    <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">Inactive</span>
                    @endif
                    <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">#{{ $photo->display_order }}</span>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 mt-3">
                    <a href="{{ route('admin.photos.edit', $photo) }}"
                        class="flex-1 py-1.5 px-3 bg-primary-50 text-primary-700 text-xs rounded-lg hover:bg-primary-100 font-medium text-center transition-colors">
                        <i class="fas fa-pen mr-1"></i> Edit
                    </a>
                    <form action="{{ route('admin.photos.destroy', $photo) }}" method="POST" class="flex-1" onsubmit="return confirm('Delete &quot;{{ $photo->title }}&quot;? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-1.5 px-3 bg-red-50 text-red-700 text-xs rounded-lg hover:bg-red-100 font-medium transition-colors">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16 text-gray-400">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-images text-2xl"></i>
            </div>
            <p class="text-gray-500 dark:text-gray-400">No photos uploaded yet.</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Upload your first campus photo above.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $photos->links() }}
    </div>

    <!-- Public Preview Link -->
    <div class="flex justify-center pt-4">
        <a href="{{ route('about') }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
            <i class="fas fa-external-link-alt"></i>
            Preview how photos look on the About page
        </a>
    </div>
</div>
@endsection
