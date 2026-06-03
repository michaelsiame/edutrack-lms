@extends('layouts.dashboard')

@section('title', 'Testimonials - Admin')
@section('page_title', 'Testimonials')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Info Banner -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-semibold mb-1">How testimonials work</p>
                <p class="text-blue-700 dark:text-blue-300">Students submit testimonials after completing courses. <strong>Pending</strong> testimonials need approval before appearing publicly. <strong>Featured</strong> testimonials appear on the homepage and testimonials page.</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="od-card p-4 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total</div>
        </div>
        <div class="od-card p-4 text-center">
            <div class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pending</div>
        </div>
        <div class="od-card p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Approved</div>
        </div>
        <div class="od-card p-4 text-center">
            <div class="text-2xl font-bold text-primary-600">{{ $stats['featured'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Featured</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="od-card p-4">
        <form action="{{ route('admin.testimonials.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, course, or text..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Featured</label>
                <select name="featured" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured Only</option>
                    <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Not Featured</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'featured']))
            <a href="{{ route('admin.testimonials.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Testimonials Table -->
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3 font-medium">Student</th>
                        <th class="px-4 py-3 font-medium">Course</th>
                        <th class="px-4 py-3 font-medium">Rating</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Featured</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody >
                    @forelse($testimonials as $t)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0" style="background: var(--od-navy); color: var(--od-surface);">
                                    {{ strtoupper(substr($t->student_name ?? $t->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium" style="color: var(--od-fg);">{{ $t->student_name ?? $t->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($t->testimonial_text, 50) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $t->course_taken }}</td>
                        <td class="px-4 py-3">
                            <div class="flex text-amber-400 text-xs">
                                @for($i = 0; $i < 5; $i++)
                                <i class="fas fa-star{{ $i < $t->rating ? '' : '-half-alt' }}"></i>
                                @endfor
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($t->status === 'approved')
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Approved</span>
                            @elseif($t->status === 'pending')
                            <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Pending</span>
                            @else
                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Rejected</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($t->is_featured)
                            <span class="text-xs bg-primary-100 text-primary-700 px-2 py-0.5 rounded-full font-medium"><i class="fas fa-star mr-0.5"></i>Yes</span>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.testimonials.edit', $t) }}" class="px-3 py-1.5 bg-primary-50 text-primary-700 text-xs rounded-lg hover:bg-primary-100 font-medium transition-colors">
                                    <i class="fas fa-pen mr-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.testimonials.destroy', $t) }}" method="POST" data-confirm="Delete this testimonial">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-700 text-xs rounded-lg hover:bg-red-100 font-medium transition-colors">
                                        <i class="fas fa-trash mr-1"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-comment-alt text-2xl"></i>
                            </div>
                            <p class="od-meta">No testimonials yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $testimonials->links() }}
    </div>

    <div class="flex justify-center pt-2">
        <a href="{{ route('testimonials') }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
            <i class="fas fa-external-link-alt"></i>
            Preview testimonials on public page
        </a>
    </div>
</div>
@endsection
