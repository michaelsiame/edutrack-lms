@extends('emails.layout')

@section('title', 'Assignment Graded')
@section('subtitle', 'Your results are in')

@section('content')
<p>Hi {{ $studentName ?? 'there' }},</p>
<p>Your assignment <strong>{{ $assignmentTitle ?? 'submission' }}</strong> in <strong>{{ $courseTitle ?? 'your course' }}</strong> has been graded.</p>

<div class="card" style="text-align: center;">
    <div style="font-size: 14px; color: #6b7280; margin-bottom: 6px;">Score</div>
    <div style="font-size: 36px; font-weight: 700; color: #1e3a5f;">{{ $pointsEarned ?? 0 }} <span style="font-size: 18px; color: #9ca3af;">/ {{ $maxPoints ?? 100 }}</span></div>
</div>

@if(!empty($feedback))
<div class="card">
    <strong>Instructor Feedback:</strong><br>
    <p style="margin: 8px 0 0; color: #4b5563;">{{ $feedback }}</p>
</div>
@endif

<div class="center" style="margin: 28px 0;">
    <a href="{{ url('/student/assignments') }}" class="btn">View Assignment</a>
</div>
@endsection
