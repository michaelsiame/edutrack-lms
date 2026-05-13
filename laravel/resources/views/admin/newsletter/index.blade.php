@extends('layouts.dashboard')

@section('title', 'Newsletter Subscribers')
@section('page_title', 'Newsletter Subscribers')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Newsletter Subscribers</h2>
        <div class="text-sm text-gray-500">
            <span class="font-semibold text-green-600">{{ $subscribers->where('is_active', true)->count() }}</span> active /
            <span class="font-semibold">{{ $subscribers->count() }}</span> total
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Subscribed</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($subscribers as $sub)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $sub->email }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $sub->name ?: '-' }}</td>
                            <td class="px-4 py-3">
                                @if($sub->is_active)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Unsubscribed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $sub->subscribed_at ? $sub->subscribed_at->format('M d, Y') : '-' }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('admin.newsletter.toggle', $sub) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-primary-600 hover:text-primary-700 mr-2">
                                        {{ $sub->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.newsletter.destroy', $sub) }}" method="POST" class="inline" onsubmit="return confirm('Delete subscriber?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No subscribers yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $subscribers->links() }}
    </div>
</div>
@endsection
