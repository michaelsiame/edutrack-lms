<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstitutionPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstitutionPhotoController extends Controller
{
    public function index()
    {
        $photos = InstitutionPhoto::orderBy('display_order')->paginate(20);
        return view('admin.photos.index', compact('photos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|max:5120',
            'category' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
        ]);

        $path = $request->file('image')->store('institution-photos', 'public');

        InstitutionPhoto::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image_path' => Storage::url($path),
            'category' => $validated['category'] ?? 'general',
            'display_order' => $validated['display_order'] ?? 0,
            'is_featured' => $request->boolean('is_featured'),
            'is_featured' => true,
        ]);

        return back()->with('success', 'Photo uploaded successfully.');
    }

    public function update(Request $request, InstitutionPhoto $photo)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $photo->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? $photo->category,
            'display_order' => $validated['display_order'] ?? $photo->display_order,
            'is_featured' => $request->boolean('is_featured'),
            'is_featured' => $request->boolean('is_featured', true),
        ]);

        return back()->with('success', 'Photo updated successfully.');
    }

    public function destroy(InstitutionPhoto $photo)
    {
        if ($photo->image_path) {
            $path = str_replace('/storage/', '', $photo->image_path);
            Storage::disk('public')->delete($path);
        }
        $photo->delete();
        return back()->with('success', 'Photo deleted successfully.');
    }
}
