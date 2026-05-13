@extends('layouts.dashboard')

@section('title', 'Events - Admin')
@section('page_title', 'Events')

@section('content')
<div class="max-w-6xl mx-auto">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{{ session('success') }}</div>
    @endif

    <!-- Create Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create New Event</h3>
        <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                    <input type="text" name="title" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Date</label>
                    <input type="datetime-local" name="event_date" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                    <input type="text" name="location"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <input type="text" name="category" placeholder="e.g. workshop, seminar"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excerpt</label>
                <input type="text" name="excerpt"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"></textarea>
            </div>
            <div class="mt-4 flex items-center gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*"
                        class="text-sm dark:text-white">
                </div>
                <label class="flex items-center text-gray-700 dark:text-gray-300 mt-6">
                    <input type="checkbox" name="is_featured" value="1"
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="ml-2">Featured on Homepage</span>
                </label>
                <button type="submit" class="ml-auto px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">Create Event</button>
            </div>
        </form>
    </div>

    <!-- Events List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left">Event</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($events as $event)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($event->cover_image)
                                    <img src="{{ $event->cover_image }}" alt="" class="w-10 h-10 rounded object-cover">
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $event->title }}</div>
                                    <div class="text-xs text-gray-500">{{ $event->location }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $event->event_date->format('M d, Y h:i A') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $event->status === 'upcoming' ? 'bg-green-100 text-green-800' : ($event->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ ucfirst($event->status) }}
                            </span>
                            @if($event->is_featured)
                                <span class="ml-1 text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">Featured</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button onclick="toggleEditEvent({{ $event->id }})" class="text-primary-600 hover:text-primary-700 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline" onsubmit="return confirm('Delete this event?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <!-- Edit Form -->
                    <tr id="edit-event-{{ $event->id }}" class="hidden bg-gray-50 dark:bg-gray-700/30">
                        <td colspan="4" class="px-4 py-4">
                            <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <input type="text" name="title" value="{{ $event->title }}" required
                                        class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                                    <input type="datetime-local" name="event_date" value="{{ $event->event_date->format('Y-m-d\TH:i') }}" required
                                        class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                                    <input type="text" name="location" value="{{ $event->location }}"
                                        class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                                    <select name="status" class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                                        <option value="upcoming" {{ $event->status === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                        <option value="ongoing" {{ $event->status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                        <option value="completed" {{ $event->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $event->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    <input type="text" name="category" value="{{ $event->category }}"
                                        class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                                    <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="is_featured" value="1" {{ $event->is_featured ? 'checked' : '' }}
                                            class="w-4 h-4 text-primary-600 mr-2">
                                        Featured
                                    </label>
                                </div>
                                <textarea name="description" rows="2" class="w-full mt-2 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:text-white">{{ $event->description }}</textarea>
                                <div class="flex gap-2 mt-2">
                                    <button type="submit" class="px-3 py-1.5 bg-primary-600 text-white text-xs rounded hover:bg-primary-700">Update</button>
                                    <button type="button" onclick="toggleEditEvent({{ $event->id }})" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">No events yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $events->links() }}
    </div>
</div>

<script>
function toggleEditEvent(eventId) {
    document.getElementById('edit-event-' + eventId).classList.toggle('hidden');
}
</script>
@endsection
