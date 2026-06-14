@extends('emails.layout')

@section('title', 'Quiz Result Recorded')
@section('subtitle', 'Your result is in')

@section('content')
<p>Hi {{ $studentName ?? 'there' }},</p>
<p>Your result for <strong>{{ $quizTitle ?? 'a quiz' }}</strong> in <strong>{{ $courseTitle ?? 'your course' }}</strong> has been recorded.</p>

<div class="card {{ ($passed ?? false) ? 'card-success' : 'card-warning' }}" style="text-align: center;">
    <div style="font-size: 14px; color: #6b7280; margin-bottom: 6px;">Score</div>
    <div style="font-size: 36px; font-weight: 700; color: {{ ($passed ?? false) ? '#059669' : '#b45309' }};">{{ $score ?? 0 }}%</div>
    <div style="font-size: 13px; margin-top: 4px; color: #6b7280;">Pass mark: {{ $passingScore ?? 60 }}% — {{ ($passed ?? false) ? 'Passed' : 'Not yet passed' }}</div>
</div>

<div class="center" style="margin: 28px 0;">
    <a href="{{ url('/student/quizzes') }}" class="btn">View My Quizzes</a>
</div>

<p class="small">If you have any questions about this result, contact us at {{ config('edutrack.email') }}.</p>
@endsection
