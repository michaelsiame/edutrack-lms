@extends('layouts.dashboard')

@section('title', 'Create Course from Template - Edutrack LMS')
@section('page_title', 'Create from Template')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('instructor.courses.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i>Back to Courses
        </a>
    </div>

    <div class="od-card p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Create Course from Template</h2>

        @if(!$selectedTemplate)
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Select a template to get started. The template's modules and lessons will be copied to your new course.</p>

            @if($templates->isNotEmpty())
                <div class="space-y-3">
                    @foreach($templates as $template)
                        <a href="{{ route('instructor.courses.create-from-template', ['template_id' => $template->id]) }}"
                           class="block p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 transition-colors"
                           style="background: var(--od-surface);">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $template->title }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $template->modules_count }} modules · Created {{ $template->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <span class="text-primary-600 text-sm font-medium">
                                    Use Template <i class="fas fa-arrow-right ml-1"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10">
                    <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Templates Available</h3>
                    <p class="text-gray-500 text-sm">Save an existing course as a template to reuse its structure.</p>
                </div>
            @endif
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Creating from: <strong class="text-gray-900 dark:text-white">{{ $selectedTemplate->title }}</strong>
                ({{ $selectedTemplate->modules->count() }} modules, {{ $selectedTemplate->modules->sum(fn($m) => $m->lessons->count()) }} lessons)
            </p>

            <form action="{{ route('instructor.courses.store-from-template') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="template_id" value="{{ $selectedTemplate->id }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course Title</label>
                    <input type="text" name="title" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                           placeholder="e.g., Advanced Web Development">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug</label>
                    <input type="text" name="slug" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                           placeholder="advanced-web-development">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select name="category_id" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price (ZMW)</label>
                    <input type="number" name="price" required min="0" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                           placeholder="1500">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="od-btn od-btn-primary od-btn-sm font-medium">
                        <i class="fas fa-plus mr-1"></i>Create Course
                    </button>
                    <a href="{{ route('instructor.courses.create-from-template') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm">Choose Different Template</a>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
