@extends('layouts.dashboard')

@section('title','Announcements - Admin')
@section('page_title','Announcements')

@section('content')
<div class="max-w-6xl mx-auto">
 @if(session('success'))
 <div class="mb-4 p-4 od-toast-success">{{ session('success') }}</div>
 @endif

 <div class="flex items-center justify-between mb-6">
 <h2 class="text-xl font-bold text-gray-900 dark:text-white">All Announcements</h2>
 <a href="{{ route('admin.announcements.create') }}" class="od-btn od-btn-primary od-btn-sm font-medium text-sm">
 <i class="fas fa-plus mr-1"></i>New Announcement
 </a>
 </div>

 <div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
 <thead >
 <tr>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Title</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Type</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Priority</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Status</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Posted</th>
 <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300" scope="col">Actions</th>
 </tr>
 </thead>
 <tbody >
 @forelse($announcements as $announcement)
 <tr >
 <td class="px-4 py-3">
 <div class="font-medium" style="color: var(--od-fg);">{{ $announcement->title }}</div>
 @if($announcement->course)
 <div class="text-xs text-gray-500">{{ $announcement->course->title }}</div>
 @endif
 </td>
 <td class="px-4 py-3">
 <span class="text-xs capitalize text-gray-600 dark:text-gray-400">{{ $announcement->announcement_type }}</span>
 </td>
 <td class="px-4 py-3">
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
 {{ $announcement->priority === 'urgent' ? 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400' : ($announcement->priority === 'high' ? 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300') }}">
 {{ $announcement->priority }}
 </span>
 </td>
 <td class="px-4 py-3">
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
 {{ $announcement->is_published ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
 {{ $announcement->is_published ? 'Published' : 'Draft' }}
 </span>
 </td>
 <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $announcement->created_at->diffForHumans() }}</td>
 <td class="px-4 py-3 text-right">
 <a href="{{ route('admin.announcements.edit', $announcement) }}" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg mr-1" aria-label="Edit announcement">
 <i class="fas fa-edit" aria-hidden="true"></i>
 </a>
 <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="inline" data-confirm="Delete this announcement">
 @csrf
 @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-danger-600 hover:text-danger-700 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg" aria-label="Delete announcement">
 <i class="fas fa-trash" aria-hidden="true"></i>
 </button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No announcements yet.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 <div class="mt-4">
 {{ $announcements->links() }}
 </div>
</div>
@endsection
