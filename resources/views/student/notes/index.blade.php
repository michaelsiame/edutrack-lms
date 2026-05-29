@extends('layouts.dashboard')

@section('title','My Notes - Edutrack LMS')
@section('page_title','My Notes')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <x-page-header title="My Notes" subtitle="Notes you have taken across all lessons" variant="od" />

    <div class="od-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="od-h3">All Notes</h3>
        </div>

        @if($notes->isEmpty())
            <x-empty-state icon="fa-sticky-note" title="No Notes Yet" description="Start taking notes while learning. Notes are saved per lesson." variant="od" />
        @else
            <div>
                @foreach($notes as $note)
                    <div class="od-course-row group">
                        <div class="od-course-thumb" style="background: var(--od-accent-soft);">
                            <i class="fas fa-sticky-note text-sm" style="color: var(--od-accent);"></i>
                        </div>
                        <div class="od-course-info">
                            <h4>{{ $note->lesson->title }}</h4>
                            <p class="od-meta">{{ $note->course->title }} &bull; Updated {{ $note->updated_at->diffForHumans() }}</p>
                            <p class="text-sm mt-1 line-clamp-2 leading-relaxed" style="color: var(--od-muted);">{{ $note->content }}</p>
                        </div>
                        <div class="od-course-action">
                            <a href="{{ route('student.notes.show', [$note->course, $note->lesson]) }}" class="od-btn od-btn-ghost od-btn-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($notes->hasPages())
                <div class="mt-4 pt-4" style="border-top: 1px solid var(--od-border);">
                    {{ $notes->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
