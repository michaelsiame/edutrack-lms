@extends('emails.layout')

@section('title', 'Certificate Issued')
@section('subtitle', 'Congratulations on your achievement')

@section('content')
<p>Hi {{ $name ?? 'there' }},</p>
<p>Congratulations! You have successfully completed <strong>{{ $course ?? 'your course' }}</strong> and your certificate has been issued.</p>

<div class="card" style="text-align: center;">
    <div style="font-size: 14px; color: #6b7280; margin-bottom: 6px;">Certificate Number</div>
    <div style="font-size: 22px; font-weight: 700; color: #1e3a5f; letter-spacing: 0.5px;">{{ $certificate_number ?? 'N/A' }}</div>
</div>

<p>This certificate verifies your achievement and can be shared with employers or added to your professional profile. You can download it anytime from your account.</p>

<div class="center" style="margin: 28px 0;">
    <a href="{{ $download_url ?? url('/student/certificates') }}" class="btn btn-success">Download Certificate</a>
</div>

<div class="card card-success">
    <strong>Verify your certificate:</strong> Anyone can verify the authenticity of your certificate using the certificate number above at our verification page.
</div>
@endsection
