@extends('emails.layout')

@section('title', 'Enrollment Confirmed')
@section('subtitle', 'You\'re all set to learn')

@section('content')
<p>Hi {{ $name ?? 'there' }},</p>
<p>Great news! You have successfully enrolled in <strong>{{ $course ?? 'your course' }}</strong>. We're excited to have you on this learning journey.</p>

<div class="card card-success">
    <strong>What happens next?</strong><br>
    Access your course materials, join live sessions, and track your progress from your student dashboard.
</div>

<div class="center" style="margin: 28px 0;">
    <a href="{{ $course_url ?? url('/student/dashboard') }}" class="btn btn-success">Start Learning</a>
</div>

<p class="small">Need help getting started? Contact our support team at edutrackzambia@gmail.com or +260 770 666 937.</p>
@endsection
