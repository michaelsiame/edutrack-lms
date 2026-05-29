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
        $photos = InstitutionPhoto::orderBy('display_order')->orderBy('id')->paginate(20);

        // Find potential duplicates by title
        $titles = $photos->pluck('title');
        $duplicates = $titles->duplicates()->unique()->values();

        return view('admin.photos.index', compact('photos', 'duplicates'));
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
            'is_featured' => $request->boolean('is_featured', false),
            'is_active' => true,
        ]);

        return back()->with('success', 'Photo uploaded successfully.');
    }

    public function edit(InstitutionPhoto $photo)
    {
        return view('admin.photos.edit', compact('photo'));
    }

    public function update(Request $request, InstitutionPhoto $photo)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $photo->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? $photo->category,
            'display_order' => $validated['display_order'] ?? $photo->display_order,
            'is_featured' => $request->boolean('is_featured', false),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.photos.index')->with('success', 'Photo updated successfully.');
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
