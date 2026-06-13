@extends('emails.layout')

@section('title', 'Live Session Reminder')
@section('subtitle', 'Don\'t miss your upcoming session')

@section('content')
<p>Hello {{ $student->first_name ?? 'Student' }},</p>
<p>This is a friendly reminder that you have an upcoming live session. Here are the details:</p>

<div class="card">
    <div style="font-size: 16px; font-weight: 600; color: #1e3a5f; margin-bottom: 12px;">{{ $session->title ?? 'Live Session' }}</div>
    <table style="width: 100%; font-size: 14px; color: #4b5563;">
        <tr><td style="padding: 4px 0; width: 100px;"><strong>Course:</strong></td><td>{{ $course->title ?? 'Your Course' }}</td></tr>
        <tr><td style="padding: 4px 0;"><strong>Date:</strong></td><td>{{ isset($session->scheduled_start_time) ? \Carbon\Carbon::parse($session->scheduled_start_time)->format('l, F j, Y') : 'TBD' }}</td></tr>
        <tr><td style="padding: 4px 0;"><strong>Time:</strong></td><td>{{ isset($session->scheduled_start_time) ? \Carbon\Carbon::parse($session->scheduled_start_time)->format('g:i A') . ' CAT' : 'TBD' }}</td></tr>
        @if(isset($session->duration_minutes))
        <tr><td style="padding: 4px 0;"><strong>Duration:</strong></td><td>{{ $session->duration_minutes }} minutes</td></tr>
        @endif
    </table>
    @if(isset($session->description) && $session->description)
    <p style="margin-top: 12px; font-size: 13px; color: #6b7280;">{{ $session->description }}</p>
    @endif
</div>

@if(isset($session->join_url) && $session->join_url)
<div class="center" style="margin: 28px 0;">
    <a href="{{ $session->join_url }}" class="btn">Join Live Session</a>
</div>
@endif

<p class="small">Please join a few minutes early to test your audio and video. If you have any issues, contact us at {{ config('edutrack.email') }} or {{ config('edutrack.phone') }}.</p>
@endsection
