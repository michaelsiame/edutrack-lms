@extends('emails.layout')

@section('title', 'Welcome to Edutrack LMS')
@section('subtitle', 'Your journey starts here')

@section('content')
<p>Hi {{ $name ?? 'there' }},</p>
<p>Welcome to <strong>Edutrack LMS</strong>! Your account has been created successfully and you're now part of Edutrack Computer Training College.</p>

<div class="center" style="margin: 28px 0;">
    <a href="{{ $login_url ?? route('login') }}" class="btn">Log In to Your Account</a>
</div>

<p>With your account you can:</p>
<ul style="padding-left: 20px; color: #4b5563;">
    <li>Browse and enroll in professional courses</li>
    <li>Track your learning progress</li>
    <li>Join live virtual sessions</li>
    <li>Earn certificates upon completion</li>
</ul>

<p class="small" style="margin-top: 20px;">If you have any questions, reply to this email or contact us at edutrackzambia@gmail.com.</p>
@endsection
