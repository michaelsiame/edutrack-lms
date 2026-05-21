@extends('layouts.dashboard')

@section('title','My Notes - Edutrack LMS')
@section('page_title','My Notes')

@section('content')
<div class="max-w-4xl mx-auto">
 <x-card class="overflow-hidden">
 <div class="p-5 md:p-6 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-semibold text-gray-900 dark:text-white">My Notes</h2>
 </div>

 @if($notes->isEmpty())
 <x-empty-state icon="fa-sticky-note" title="No Notes Yet" description="Start taking notes while learning. Notes are saved per lesson." />
 @else
 <div class="divide-y divide-gray-100 dark:divide-gray-700">
 @foreach($notes as $note)
 <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
 <div class="flex items-center justify-between mb-2">
 <div>
 <h4 class="font-medium text-gray-900 dark:text-white">{{ $note->lesson->title }}</h4>
 <p class="text-xs text-gray-500 dark:text-gray-400">
 {{ $note->course->title }} &bull; {{ $note->updated_at->diffForHumans() }}
 </p>
 </div>
 <a href="{{ route('student.notes.show', [$note->course, $note->lesson]) }}"
 class="text-sm text-primary-600 hover:text-primary-700 font-medium">
 View/Edit
 </a>
 </div>
 <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $note->content }}</p>
 </div>
 @endforeach
 </div>
 <div class="p-4 border-t border-gray-100 dark:border-gray-700">
 {{ $notes->links() }}
 </div>
 @endif
 </x-card>
</div>
@endsection
