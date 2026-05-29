@extends('emails.layout')

@section('title', 'We Miss You!')
@section('subtitle', 'Keep up your learning momentum')

@section('content')
<p>Hello {{ $student->first_name ?? 'Student' }},</p>
<p>We noticed you haven't been active in your course for a while. Don't worry — you can pick up right where you left off!</p>

<div class="card card-warning">
    <div style="font-size: 16px; font-weight: 600; color: #b45309; margin-bottom: 10px;">{{ $course->title ?? 'Your Course' }}</div>
    <div style="font-size: 13px; color: #6b7280; margin-bottom: 10px;">Your current progress:</div>
    <div style="width: 100%; height: 20px; background: #e5e7eb; border-radius: 10px; overflow: hidden;">
        <div style="height: 100%; background: #f59e0b; border-radius: 10px; text-align: center; color: white; font-size: 12px; line-height: 20px; width: {{ $progress ?? 0 }}%;">{{ $progress ?? 0 }}%</div>
    </div>
    <p style="margin-top: 10px; font-size: 13px; color: #6b7280;">
        @if(($progress ?? 0) < 10)
            You're just getting started! Every expert was once a beginner.
        @elseif(($progress ?? 0) < 30)
            Great start! Keep the momentum going.
        @elseif(($progress ?? 0) < 50)
            You're making solid progress. Don't stop now!
        @else
            You're more than halfway there! Finish strong.
        @endif
    </p>
</div>

<div class="center" style="margin: 28px 0;">
    <a href="{{ url('/student/dashboard') }}" class="btn btn-warning">Continue Learning</a>
</div>

<div class="card card-success">
    <strong>Tips to Stay on Track</strong>
    <ul style="margin: 10px 0 0; padding-left: 20px; color: #166534; font-size: 13px;">
        <li>Set aside 30 minutes each day for learning</li>
        <li>Join the course discussion forum to ask questions</li>
        <li>Attend live sessions for real-time interaction</li>
        <li>Take notes as you go through each lesson</li>
    </ul>
</div>
@endsection
