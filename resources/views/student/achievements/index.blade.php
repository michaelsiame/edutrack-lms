@extends('layouts.dashboard')

@section('title','My Achievements - Edutrack LMS')
@section('page_title','My Achievements')

@section('content')
<div class="max-w-5xl mx-auto">
 <x-card class="overflow-hidden">
 <div class="p-5 md:p-6 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Achievements & Badges</h2>
 </div>

 @if($achievements->isEmpty())
 <x-empty-state icon="fa-trophy" title="No Achievements Yet" description="Complete courses, pass quizzes, and participate actively to earn badges and achievements." />
 @else
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
 @foreach($achievements as $achievement)
 <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-6 text-center hover:shadow-card-hover transition-shadow">
 <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-secondary-50 dark:bg-secondary-900/30 flex items-center justify-center">
 @if($achievement->badge?->icon)
 <i class="fas {{ $achievement->badge->icon }} text-2xl text-secondary-500 dark:text-secondary-400"></i>
 @else
 <i class="fas fa-medal text-2xl text-secondary-500 dark:text-secondary-400"></i>
 @endif
 </div>
 <h4 class="font-semibold text-gray-900 dark:text-white mb-1">{{ $achievement->badge?->name ??'Achievement' }}</h4>
 <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $achievement->badge?->description ??'' }}</p>
 <span class="text-xs text-gray-400">Earned {{ $achievement->earned_date?->diffForHumans() ??'recently' }}</span>
 </div>
 @endforeach
 </div>
 @endif
 </x-card>
</div>
@endsection
