@extends('layouts.dashboard')

@section('title', 'Team Members - Admin')
@section('page_title', 'Team Members')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Info Banner -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-semibold mb-1">Where team members appear</p>
                <p class="text-blue-700 dark:text-blue-300">Team members are displayed on the <strong>About page</strong> in the "Meet Our Team" section. They appear in the order set by <strong>Display Order</strong> (lowest first).</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">All Team Members</h3>
        <a href="{{ route('admin.team.create') }}" class="od-btn od-btn-primary od-btn-sm font-medium text-sm transition-colors">
            <i class="fas fa-plus mr-1"></i> Add Member
        </a>
    </div>

    <!-- Members Table -->
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3 font-medium">Photo</th>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Position</th>
                        <th class="px-4 py-3 font-medium">Order</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody >
                    @forelse($members as $member)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                @if($member->image_url && file_exists(public_path('uploads/team/' . $member->image_url)))
                                <img src="{{ asset('uploads/team/' . $member->image_url) }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <i class="fas fa-user"></i>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $member->name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $member->position }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $member->display_order }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.team.edit', $member) }}" class="px-3 py-1.5 bg-primary-50 text-primary-700 text-xs rounded-lg hover:bg-primary-100 font-medium transition-colors">
                                    <i class="fas fa-pen mr-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.team.destroy', $member) }}" method="POST" data-confirm="Remove {{ $member->name }} from the team">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-700 text-xs rounded-lg hover:bg-red-100 font-medium transition-colors">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <p class="od-meta">No team members yet.</p>
                            <a href="{{ route('admin.team.create') }}" class="text-primary-600 hover:text-primary-700 text-sm mt-2 inline-block">Add your first team member</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $members->links() }}
    </div>

    <div class="flex justify-center pt-2">
        <a href="{{ route('about') }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
            <i class="fas fa-external-link-alt"></i>
            Preview team on the About page
        </a>
    </div>
</div>
@endsection
