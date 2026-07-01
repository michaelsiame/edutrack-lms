@extends('emails.layout')

@section('title', 'Payment Plan Reminder')
@section('subtitle', 'Action required to keep your LMS access')

@section('content')
<p>Dear {{ $studentName }},</p>

<table class="meta">
    <tr><td><strong>Student No.</strong></td><td>{{ $studentNumber }}</td></tr>
    <tr><td><strong>Programme</strong></td><td>{{ $programme }}</td></tr>
    @if(!empty($outstanding))
    <tr><td><strong>Outstanding Balance</strong></td><td>{{ $currency ?? 'ZMW' }} {{ number_format((float) $outstanding, 2) }}</td></tr>
    @endif
</table>

<div class="card card-warning">
    <p style="margin:0;">This is a reminder that, due to the payment plan policy, your access to the Learning Management System (LMS) will be <strong>suspended starting {{ $suspensionDate }}</strong> until the required payment is made.</p>
</div>

<p>To avoid any interruption to your studies, please make your payment on or before the date above. If you have already paid, kindly ignore this message or share your proof of payment with the office.</p>

<p class="small">If you have any questions about your payment plan, please contact the college using the details below.</p>

<p style="margin-top: 24px;">Edutrack Computer Training College</p>
@endsection
