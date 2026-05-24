@extends('layouts.dashboard')

@section('title','My Notes - Edutrack LMS')
@section('page_title','My Notes')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-page-header title="My Notes" subtitle="Notes you have taken across all lessons" />

    <x-card variant="default" class="overflow-hidden">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <i class="fas fa-sticky-note text-warning-500"></i>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">All Notes</h3>
            </div>
        </x-slot:header>

        @if($notes->isEmpty())
            <x-empty-state icon="fa-sticky-note" title="No Notes Yet" description="Start taking notes while learning. Notes are saved per lesson." />
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-700 -mx-5 md:-mx-6">
                @foreach($notes as $note)
                    <div class="px-5 md:px-6 py-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $note->lesson->title }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $note->course->title }} &bull; Updated {{ $note->updated_at->diffForHumans() }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2 leading-relaxed">{{ $note->content }}</p>
                            </div>
                            <x-button :href="route('student.notes.show', [$note->course, $note->lesson])" variant="ghost" size="sm" icon="fa-pen" class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                Edit
                            </x-button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($notes->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $notes->links() }}
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
