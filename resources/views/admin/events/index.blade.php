@extends('layouts.dashboard')

@section('title','Events - Admin')
@section('page_title','Events')

@section('content')
<div class="max-w-6xl mx-auto">
 @if(session('success'))
 <div class="mb-4 p-4 od-toast-success">{{ session('success') }}</div>
 @endif

 <!-- Create Form -->
 <div class="od-card p-6 mb-6">
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
 <div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
 <thead >
 <tr>
 <th class="px-4 py-3 text-left" scope="col">Event</th>
 <th class="px-4 py-3 text-left" scope="col">Date</th>
 <th class="px-4 py-3 text-left" scope="col">Status</th>
 <th class="px-4 py-3 text-right" scope="col">Actions</th>
 </tr>
 </thead>
 <tbody >
 @forelse($events as $event)
 <tr >
 <td class="px-4 py-3">
 <div class="flex items-center gap-3">
 @if($event->cover_image)
 <img src="{{ $event->cover_image }}" alt="{{ $event->title }} cover" class="w-10 h-10 rounded object-cover">
 @endif
 <div>
 <div class="font-medium" style="color: var(--od-fg);">{{ $event->title }}</div>
 <div class="text-xs text-gray-500">{{ $event->location }}</div>
 </div>
 </div>
 </td>
 <td class="px-4 py-3 text-gray-500">{{ $event->event_date->format('M d, Y h:i A') }}</td>
 <td class="px-4 py-3">
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
 {{ $event->status === 'upcoming' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : ($event->status === 'cancelled' ? 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400' : 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400') }}">
 {{ ucfirst($event->status) }}
 </span>
 @if($event->is_featured)
 <span class="ml-1 text-xs bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 px-1.5 py-0.5 rounded">Featured</span>
 @endif
 </td>
 <td class="px-4 py-3 text-right">
 <button onclick="toggleEditEvent({{ $event->id }})" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg mr-1" aria-label="Edit event">
 <i class="fas fa-edit" aria-hidden="true"></i>
 </button>
 <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline" data-confirm="Delete this event">
 @csrf
 @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-danger-600 hover:text-danger-700 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg" aria-label="Delete event">
 <i class="fas fa-trash" aria-hidden="true"></i>
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
 class="od-input">
 <input type="datetime-local" name="event_date" value="{{ $event->event_date->format('Y-m-d\TH:i') }}" required
 class="od-input">
 <input type="text" name="location" value="{{ $event->location }}"
 class="od-input">
 </div>
 <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
 <select name="status" class="od-input">
 <option value="upcoming" {{ $event->status ==='upcoming' ?'selected' :'' }}>Upcoming</option>
 <option value="ongoing" {{ $event->status ==='ongoing' ?'selected' :'' }}>Ongoing</option>
 <option value="completed" {{ $event->status ==='completed' ?'selected' :'' }}>Completed</option>
 <option value="cancelled" {{ $event->status ==='cancelled' ?'selected' :'' }}>Cancelled</option>
 </select>
 <input type="text" name="category" value="{{ $event->category }}"
 class="od-input">
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="is_featured" value="1" {{ $event->is_featured ?'checked' :'' }}
 class="w-4 h-4 text-primary-600 mr-2 accent-primary-600">
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
 <td colspan="4" class="od-empty-sm">No events yet.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
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
