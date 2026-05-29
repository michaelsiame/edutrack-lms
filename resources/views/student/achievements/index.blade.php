@extends('layouts.dashboard')

@section('title','My Achievements - Edutrack LMS')
@section('page_title','My Achievements')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <x-page-header title="Achievements & Badges" subtitle="Track your accomplishments and earned recognition" variant="od" />

    @if($achievements->isEmpty())
        <div class="od-card">
            <x-empty-state icon="fa-trophy" title="No Achievements Yet" description="Complete courses, pass quizzes, and participate actively to earn badges and achievements." variant="od" />
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($achievements as $achievement)
                <div class="od-card text-center py-8 group relative overflow-hidden">
                    <!-- Hover glow -->
                    <div class="absolute inset-0 transition-colors duration-500" style="background: color-mix(in oklch, var(--od-accent) 0%, transparent);"
                         onmouseover="this.style.background='color-mix(in oklch, var(--od-accent) 5%, transparent)'"
                         onmouseout="this.style.background='color-mix(in oklch, var(--od-accent) 0%, transparent)'"></div>

                    <div class="relative">
                        <div class="w-20 h-20 mx-auto mb-5 rounded-full flex items-center justify-center border-4 transition-all duration-300 group-hover:scale-110"
                             style="background: var(--od-accent-soft); border-color: color-mix(in oklch, var(--od-accent) 20%, transparent);">
                            @if($achievement->badge?->icon)
                                <i class="fas {{ $achievement->badge->icon }} text-3xl" style="color: var(--od-accent);"></i>
                            @else
                                <i class="fas fa-medal text-3xl" style="color: var(--od-accent);"></i>
                            @endif
                        </div>
                        <h4 class="font-bold text-lg mb-1" style="color: var(--od-fg);">{{ $achievement->badge?->name ?? 'Achievement' }}</h4>
                        <p class="text-sm mb-4 px-4" style="color: var(--od-muted);">{{ $achievement->badge?->description ?? '' }}</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium od-num" style="background: var(--od-fg-soft); color: var(--od-muted);">
                            <i class="far fa-calendar-alt mr-1.5"></i>Earned {{ $achievement->earned_date?->diffForHumans() ?? 'recently' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
