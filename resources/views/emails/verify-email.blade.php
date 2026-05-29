@extends('emails.layout')

@section('title', 'Verify Your Email Address')
@section('subtitle', 'One quick step to get started')

@section('content')
<p>Hi {{ $user->first_name ?? 'there' }},</p>
<p>Thank you for registering with Edutrack LMS. Please verify your email address to activate your account and access all features.</p>

<div class="center" style="margin: 28px 0;">
    <a href="{{ $verificationUrl ?? route('verification.verify', ['token' => $token]) }}" class="btn">Verify Email Address</a>
</div>

<p class="small">Or copy and paste this link into your browser:</p>
<p class="small" style="word-break: break-all; color: #6b7280;">{{ $verificationUrl ?? route('verification.verify', ['token' => $token]) }}</p>

<div class="card card-warning" style="margin-top: 24px;">
    <strong>Important:</strong> This verification link will expire in 24 hours. If you did not create this account, you can safely ignore this email.
</div>
@endsection
