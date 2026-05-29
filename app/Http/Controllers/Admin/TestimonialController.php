<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimonial::with(['user', 'course'])->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by featured
        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('testimonial_text', 'like', "%{$search}%")
                  ->orWhere('course_taken', 'like', "%{$search}%");
            });
        }

        $testimonials = $query->paginate(20)->withQueryString();
        $stats = [
            'total' => Testimonial::count(),
            'pending' => Testimonial::where('status', 'pending')->count(),
            'approved' => Testimonial::where('status', 'approved')->count(),
            'featured' => Testimonial::where('is_featured', true)->count(),
        ];

        return view('admin.testimonials.index', compact('testimonials', 'stats'));
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'student_name' => 'required|string|max:255',
            'course_taken' => 'required|string|max:255',
            'testimonial_text' => 'required|string|max:5000',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:pending,approved,rejected',
            'is_featured' => 'nullable|boolean',
            'current_job_title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
        ]);

        $testimonial->update([
            'student_name' => $validated['student_name'],
            'course_taken' => $validated['course_taken'],
            'testimonial_text' => $validated['testimonial_text'],
            'rating' => $validated['rating'],
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured', false),
            'current_job_title' => $validated['current_job_title'] ?? null,
            'company' => $validated['company'] ?? null,
        ]);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        return back()->with('success', 'Testimonial deleted successfully.');
    }
}
