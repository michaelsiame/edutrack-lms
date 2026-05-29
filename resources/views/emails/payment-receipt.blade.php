@extends('emails.layout')

@section('title', 'Payment Receipt')
@section('subtitle', 'Thank you for your payment')

@section('content')
<p>Hi {{ $name ?? 'there' }},</p>
<p>We have received your payment for <strong>{{ $course ?? 'your course' }}</strong>. Here are your receipt details:</p>

<table class="meta">
    <tr>
        <td>Amount Paid</td>
        <td>ZMW {{ $amount ?? '0.00' }}</td>
    </tr>
    <tr>
        <td>Course</td>
        <td>{{ $course ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>Date</td>
        <td>{{ $date ?? now()->format('F d, Y') }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td style="color: #059669;">Completed</td>
    </tr>
</table>

<div class="card card-success">
    Your enrollment is now fully activated. You can access all course content, quizzes, assignments, and live sessions.
</div>

<div class="center" style="margin: 28px 0;">
    <a href="{{ url('/student/dashboard') }}" class="btn btn-success">Go to Dashboard</a>
</div>

<p class="small">If you have any questions about this payment, reply to this email with your receipt details.</p>
@endsection
