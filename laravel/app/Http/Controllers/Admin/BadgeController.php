<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index()
    {
        $badges = Badge::orderBy('badge_name')->get();
        return view('admin.badges.index', compact('badges'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'badge_name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'badge_icon_url' => 'nullable|string|max:255',
            'badge_type' => 'required|string|in:completion,streak,achievement,participation',
            'criteria' => 'nullable|string|max:255',
            'points' => 'required|integer|min:0',
        ]);

        Badge::create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()->route('admin.badges.index')->with('success', 'Badge created.');
    }

    public function toggle(Badge $badge)
    {
        $badge->update(['is_active' => !$badge->is_active]);
        return redirect()->route('admin.badges.index')->with('success', 'Badge status updated.');
    }

    public function destroy(Badge $badge)
    {
        $badge->delete();
        return redirect()->route('admin.badges.index')->with('success', 'Badge deleted.');
    }
}
