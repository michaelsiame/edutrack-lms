<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    public function index()
    {
        $members = TeamMember::with('user')->orderBy('display_order')->paginate(20);
        return view('admin.team.index', compact('members'));
    }

    public function create()
    {
        $users = User::whereHas('roles', fn($q) => $q->whereIn('role_id', [1, 2, 3, 6]))
            ->orderBy('first_name')
            ->get();
        return view('admin.team.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'qualifications' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:5120',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'user_id' => $validated['user_id'] ?? null,
            'name' => $validated['name'],
            'position' => $validated['position'],
            'qualifications' => $validated['qualifications'] ?? null,
            'display_order' => $validated['display_order'] ?? 0,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('team', 'public_uploads');
            $data['image_url'] = basename($path);
        }

        TeamMember::create($data);

        return redirect()->route('admin.team.index')->with('success', 'Team member added successfully.');
    }

    public function edit(TeamMember $member)
    {
        $users = User::whereHas('roles', fn($q) => $q->whereIn('role_id', [1, 2, 3, 6]))
            ->orderBy('first_name')
            ->get();
        return view('admin.team.edit', compact('member', 'users'));
    }

    public function update(Request $request, TeamMember $member)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'qualifications' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:5120',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'user_id' => $validated['user_id'] ?? null,
            'name' => $validated['name'],
            'position' => $validated['position'],
            'qualifications' => $validated['qualifications'] ?? null,
            'display_order' => $validated['display_order'] ?? $member->display_order,
        ];

        if ($request->hasFile('image')) {
            // Delete old image
            if ($member->image_url) {
                Storage::disk('public_uploads')->delete('team/' . $member->image_url);
            }
            $path = $request->file('image')->store('team', 'public_uploads');
            $data['image_url'] = basename($path);
        }

        $member->update($data);

        return redirect()->route('admin.team.index')->with('success', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $member)
    {
        if ($member->image_url) {
            Storage::disk('public_uploads')->delete('team/' . $member->image_url);
        }
        $member->delete();
        return back()->with('success', 'Team member removed successfully.');
    }
}
