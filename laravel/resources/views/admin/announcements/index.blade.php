@extends('layouts.dashboard')

@section('title', 'Announcements - Admin')
@section('page_title', 'Announcements')

@section('content')
<div class="max-w-6xl mx-auto">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">All Announcements</h2>
        <a href="{{ route('admin.announcements.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm">
            <i class="fas fa-plus mr-1"></i>New Announcement
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Title</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Type</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Priority</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Posted</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($announcements as $announcement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $announcement->title }}</div>
                            @if($announcement->course)
                                <div class="text-xs text-gray-500">{{ $announcement->course->title }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs capitalize">{{ $announcement->announcement_type }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $announcement->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($announcement->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $announcement->priority }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $announcement->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $announcement->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $announcement->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="text-primary-600 hover:text-primary-700 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Delete this announcement?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No announcements yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $announcements->links() }}
    </div>
</div>
@endsection
