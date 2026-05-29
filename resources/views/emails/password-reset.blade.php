@extends('emails.layout')

@section('title', 'Password Reset Request')
@section('subtitle', 'Secure your account')

@section('content')
<p>Hello {{ $user->first_name ?? 'there' }},</p>
<p>You recently requested to reset your password for your Edutrack LMS account. Click the button below to reset it:</p>

<div class="center" style="margin: 28px 0;">
    <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
</div>

<div class="card card-warning">
    <strong>Security notice:</strong> This password reset link will expire in 60 minutes. If you did not request a password reset, please ignore this email or contact support if you have concerns.
</div>

<p class="small" style="margin-top: 20px;">If the button doesn't work, copy and paste this link into your browser:</p>
<p class="small" style="word-break: break-all; color: #6b7280;">{{ $resetUrl }}</p>
@endsection
