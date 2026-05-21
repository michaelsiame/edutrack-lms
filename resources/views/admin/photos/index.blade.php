@extends('layouts.dashboard')

@section('title','Institution Photos - Admin')
@section('page_title','Institution Photos')

@section('content')
<div class="max-w-6xl mx-auto">
 @if(session('success'))
 <div class="mb-4 p-4 bg-success-50 border border-success-200 rounded-lg text-success-700">{{ session('success') }}</div>
 @endif

 <!-- Upload Form -->
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-6">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Upload New Photo</h3>
 <form action="{{ route('admin.photos.store') }}" method="POST" enctype="multipart/form-data">
 @csrf
 <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
 <div class="md:col-span-1">
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Photo</label>
 <input type="file" name="image" accept="image/*" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
 <input type="text" name="title" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
 <input type="text" name="category" placeholder="e.g. campus, lab"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 <div class="flex items-center gap-3">
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="is_featured" value="1"
 class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
 <span class="ml-2">Featured</span>
 </label>
 <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm">Upload</button>
 </div>
 </div>
 </form>
 </div>

 <!-- Photo Grid -->
 <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
 @forelse($photos as $photo)
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <img src="{{ $photo->image_path }}" alt="{{ $photo->title }}" class="w-full h-40 object-cover">
 <div class="p-3">
 <h4 class="font-medium text-gray-900 dark:text-white text-sm">{{ $photo->title }}</h4>
 <div class="flex items-center gap-2 mt-2">
 @if($photo->is_featured)
 <span class="text-xs bg-warning-100 text-warning-800 px-1.5 py-0.5 rounded">Featured</span>
 @endif
 @if(!$photo->is_active)
 <span class="text-xs bg-gray-100 text-gray-800 px-1.5 py-0.5 rounded">Inactive</span>
 @endif
 </div>
 <form action="{{ route('admin.photos.update', $photo) }}" method="POST" class="mt-2 space-y-2">
 @csrf
 @method('PUT')
 <input type="text" name="title" value="{{ $photo->title }}" class="w-full px-2 py-1 text-xs border rounded dark:bg-gray-700 dark:text-white">
 <div class="flex items-center gap-2">
 <label class="flex items-center text-xs text-gray-600 dark:text-gray-400">
 <input type="checkbox" name="is_featured" value="1" {{ $photo->is_featured ?'checked' :'' }}
 class="mr-1 w-3 h-3">
 Featured
 </label>
 <label class="flex items-center text-xs text-gray-600 dark:text-gray-400">
 <input type="checkbox" name="is_active" value="1" {{ $photo->is_active ?'checked' :'' }}
 class="mr-1 w-3 h-3">
 Active
 </label>
 </div>
 <div class="flex gap-2">
 <button type="submit" class="flex-1 py-1 bg-primary-600 text-white text-xs rounded hover:bg-primary-700">Save</button>
 <form action="{{ route('admin.photos.destroy', $photo) }}" method="POST" class="inline" onsubmit="return confirm('Delete this photo?')">
 @csrf
 @method('DELETE')
 <button type="submit" class="py-1 px-2 bg-danger-600 text-white text-xs rounded hover:bg-danger-700">
 <i class="fas fa-trash"></i>
 </button>
 </form>
 </div>
 </form>
 </div>
 </div>
 @empty
 <div class="col-span-full text-center py-12 text-gray-500">
 <i class="fas fa-images text-4xl mb-4"></i>
 <p>No photos uploaded yet.</p>
 </div>
 @endforelse
 </div>

 <div class="mt-4">
 {{ $photos->links() }}
 </div>
</div>
@endsection
