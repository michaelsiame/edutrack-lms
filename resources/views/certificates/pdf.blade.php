{{-- This view is rendered in fragments by CertificateService::generatePdf,
     positioned at known Y coordinates. Each top-level section is wrapped in
     an HTML comment marker so the service can split them. --}}

<!-- ##header## -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<tr>
    <td style="width:42mm;">&nbsp;</td>
    <td style="width:100mm; text-align:center; vertical-align:top;">
        <span style="font-family:helvetica; font-size:22px; font-weight:bold; color:#1a1a1a; letter-spacing:1px;">EDUTRACK COMPUTER</span><br>
        <span style="font-family:helvetica; font-size:22px; font-weight:bold; color:#1a1a1a; letter-spacing:1px;">TRAINING COLLEGE</span>
    </td>
    <td style="width:44mm;">&nbsp;</td>
</tr>
</table>
<!-- ##end## -->

<!-- ##tagline## -->
<div style="text-align:center; font-family:dejavusans; color:#1e3a8a; font-size:11px; line-height:1;">
    <span style="color:#1e3a8a;">&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</span>
    <span style="color:#f26522;">&nbsp;&#9670;&nbsp;</span>
    <span style="color:#1e3a8a;">&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</span>
</div>
<div style="text-align:center; font-family:dejavuserif; font-size:11px; color:#444; font-style:italic;">A skill training college</div>
<!-- ##end## -->

<!-- ##certify## -->
<div style="text-align:center;">
    <span style="font-family:dejavusans; color:#f26522; font-size:11px;">&mdash;&nbsp;&mdash;&nbsp;&#9670;&nbsp;</span>
    <span style="font-family:helvetica; font-size:19px; font-weight:bold; color:#1e3a8a; letter-spacing:2px;">THIS IS TO CERTIFY THAT</span>
    <span style="font-family:dejavusans; color:#f26522; font-size:11px;">&nbsp;&#9670;&nbsp;&mdash;&nbsp;&mdash;</span>
</div>
<!-- ##end## -->

<!-- ##name## -->
<table cellpadding="0" cellspacing="0" border="0" style="width:75%; margin:0 auto;" align="center">
<tr>
    <td style="text-align:center; padding-bottom:1mm; border-bottom:1px solid #f26522;">
        <span style="font-family:greatvibes; font-size:54px; color:#111111;">{{ $student_name }}</span>
    </td>
</tr>
</table>
<!-- ##end## -->

<!-- ##requirement## -->
<div style="text-align:center; font-family:dejavuserif; font-size:13px; color:#333333; line-height:1.7;">
    having satisfied the requirements for the<br>award of the certificate of
</div>
<!-- ##end## -->

<!-- ##course## -->
<div style="text-align:center;">
    <span style="font-family:helvetica; font-size:30px; font-weight:bold; color:#1e3a8a; letter-spacing:1px;">{{ strtoupper($course_title) }}</span>
</div>
<!-- ##end## -->

<!-- ##classification## -->
@if($classification && $classification !== 'Pass')
<div style="text-align:center;">
    <span style="font-family:greatvibes; font-size:38px; color:#111111;">With {{ $classification }}</span>
</div>
<div style="text-align:center; font-family:dejavusans; color:#f26522; font-size:11px; line-height:1;">
    &mdash;&mdash;&mdash;&mdash;&mdash;&nbsp;&#9670;&nbsp;&mdash;&mdash;&mdash;&mdash;&mdash;
</div>
@endif
<!-- ##end## -->

<!-- ##date## -->
<div style="text-align:center; font-family:dejavuserif; font-size:13px; color:#333333; line-height:1.8;">
    Was admitted to the certificate at a Graduation<br>
    Ceremony held on the
    <span style="font-family:greatvibes; font-size:24px; color:#1e3a8a;">&nbsp;{{ $graduation_day }}{{ $graduation_suffix }}&nbsp;</span>
    day of
    <span style="font-family:greatvibes; font-size:24px; color:#1e3a8a;">&nbsp;{{ $graduation_month }}&nbsp;</span><br>
    in the year
    <span style="font-family:greatvibes; font-size:24px; color:#1e3a8a;">&nbsp;{{ $graduation_year }}&nbsp;</span>
</div>
<!-- ##end## -->

<!-- ##signatures## -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<tr>
    <td style="width:40%; text-align:center;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:80%; margin:0 auto;">
        <tr><td style="border-top:1px solid #000000; padding-top:1mm; text-align:center; font-family:dejavuserif; font-size:10px; color:#222222; font-weight:bold;">Principal</td></tr>
        </table>
    </td>
    <td style="width:20%;">&nbsp;</td>
    <td style="width:40%; text-align:center;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:80%; margin:0 auto;">
        <tr><td style="border-top:1px solid #000000; padding-top:1mm; text-align:center; font-family:dejavuserif; font-size:10px; color:#222222; font-weight:bold;">Director</td></tr>
        </table>
    </td>
</tr>
</table>
<!-- ##end## -->

<!-- ##graduate## -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<tr>
    <td style="width:40%; text-align:center;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:80%; margin:0 auto;">
        <tr><td style="border-top:1px solid #000000; padding-top:1mm; text-align:center; font-family:dejavuserif; font-size:10px; color:#222222; font-weight:bold;">Graduate's Signature</td></tr>
        </table>
    </td>
    <td style="width:20%;">&nbsp;</td>
    <td style="width:40%; text-align:center;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:80%; margin:0 auto;">
        <tr><td style="border-top:1px solid #000000; padding-top:1mm; text-align:center; font-family:dejavuserif; font-size:10px; color:#222222; font-weight:bold;">Graduate's ID No.</td></tr>
        </table>
    </td>
</tr>
</table>
<!-- ##end## -->

<!-- ##ids## -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<tr>
    <td style="width:40%; text-align:center;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:80%; margin:0 auto;">
        <tr><td style="border-top:1px solid #000000; padding-top:1mm; text-align:center; font-family:helvetica; font-size:11px; color:#000000; font-weight:bold;">{{ $certificate_number }}</td></tr>
        </table>
    </td>
    <td style="width:20%;">&nbsp;</td>
    <td style="width:40%; text-align:center;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:80%; margin:0 auto;">
        <tr><td style="border-top:1px solid #000000; padding-top:1mm; text-align:center; font-family:helvetica; font-size:11px; color:#000000; font-weight:bold;">{{ $student_number }}</td></tr>
        </table>
    </td>
</tr>
</table>
<!-- ##end## -->
