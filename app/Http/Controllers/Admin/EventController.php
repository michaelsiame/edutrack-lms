<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::latest('event_date')->paginate(15);
        return view('admin.events.index', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:50',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|max:5120',
            'is_featured' => 'nullable|boolean',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'excerpt' => $validated['excerpt'] ?? null,
            'category' => $validated['category'] ?? null,
            'event_date' => $validated['event_date'],
            'location' => $validated['location'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'status' => $validated['status'],
        ];

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('events', 'public');
            $data['cover_image'] = Storage::url($path);
        }

        Event::create($data);

        return back()->with('success', 'Event created successfully.');
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:50',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|max:5120',
            'is_featured' => 'nullable|boolean',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'excerpt' => $validated['excerpt'] ?? null,
            'category' => $validated['category'] ?? null,
            'event_date' => $validated['event_date'],
            'location' => $validated['location'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'status' => $validated['status'],
        ];

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $event->cover_image));
            }
            $path = $request->file('cover_image')->store('events', 'public');
            $data['cover_image'] = Storage::url($path);
        }

        $event->update($data);

        return back()->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        if ($event->cover_image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $event->cover_image));
        }
        $event->delete();
        return back()->with('success', 'Event deleted successfully.');
    }
}
