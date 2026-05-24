@extends('layouts.dashboard')

@section('title', 'Version History - ' . $lesson->title)
@section('page_title', 'Version History')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-back-link route="instructor.courses.show" :routeParams="[$course]" label="Back to Course" class="mb-4" />

    <x-card variant="elevated" class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $course->title }} / {{ $module->title }}
                </p>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $versions->total() }} version{{ $versions->total() !== 1 ? 's' : '' }}
            </span>
        </div>
    </x-card>

    <div class="space-y-4">
        @forelse($versions as $version)
            <x-card variant="default">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-bold text-gray-900 dark:text-white">Version #{{ $version->version_number }}</span>
                            <span class="text-xs text-gray-400">&middot; {{ $version->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                        @if($version->change_summary)
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $version->change_summary }}</p>
                        @endif
                        @if($version->creator)
                            <p class="text-xs text-gray-400 mt-1">by {{ $version->creator->first_name }} {{ $version->creator->last_name }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="button" onclick="document.getElementById('preview-{{ $version->id }}').classList.toggle('hidden')" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Preview
                        </button>
                        <form action="{{ route('instructor.lessons.versions.restore', [$course, $module, $lesson, $version]) }}" method="POST" onsubmit="return confirm('Restore this version? Current content will be saved as a new version.')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="px-3 py-1.5 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Restore
                            </button>
                        </form>
                    </div>
                </div>

                <div id="preview-{{ $version->id }}" class="hidden mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="lesson-content text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded-lg p-4 max-h-96 overflow-y-auto">
                        {!! \App\Services\HtmlSanitizer::clean($version->content) !!}
                    </div>
                </div>
            </x-card>
        @empty
            <x-card variant="default" class="text-center py-10">
                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-history text-gray-400"></i>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm">No versions saved yet. Versions are created automatically when you update a lesson.</p>
            </x-card>
        @endforelse
    </div>

    @if($versions->hasPages())
        <div class="mt-6">
            {{ $versions->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/lesson-content.css') }}">
@endpush
