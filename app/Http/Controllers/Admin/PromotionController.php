<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create()
    {
        $courses = Course::where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('admin.promotions.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promotions,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0.01',
            'max_uses' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
            'applicable_courses' => 'nullable|array',
            'applicable_courses.*' => 'exists:courses,id',
            'min_order_amount' => 'nullable|numeric|min:0',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['applicable_courses'] = $request->input('applicable_courses', []);
        $validated['used_count'] = 0;

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage discount cannot exceed 100%'])->withInput();
        }

        Promotion::create($validated);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion created successfully.');
    }

    public function show(Promotion $promotion)
    {
        return view('admin.promotions.show', compact('promotion'));
    }

    public function edit(Promotion $promotion)
    {
        $courses = Course::where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('admin.promotions.edit', compact('promotion', 'courses'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('promotions')->ignore($promotion->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0.01',
            'max_uses' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
            'applicable_courses' => 'nullable|array',
            'applicable_courses.*' => 'exists:courses,id',
            'min_order_amount' => 'nullable|numeric|min:0',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['applicable_courses'] = $request->input('applicable_courses', []);

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage discount cannot exceed 100%'])->withInput();
        }

        $promotion->update($validated);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion updated successfully.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion deleted successfully.');
    }
}
