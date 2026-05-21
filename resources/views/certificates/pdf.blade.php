<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { size: 210mm 297mm; margin: 0; }
body { margin: 0; padding: 0; font-family:'DejaVu Serif', Georgia,'Times New Roman', serif; background: #FFFFFF; }
</style>
</head>
<body>

<!-- Page container -->
<table cellpadding="0" cellspacing="0" border="0" style="width:210mm; height:297mm; background:#FDFBF7;">
<tr><td style="padding:8mm;">

<!-- Outer gold border -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; height:100%; border:4px solid #D89E2E;">
<tr><td style="padding:3mm;">

<!-- Inner blue border -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; height:100%; border:2px solid #1E3A8A; background:#FFFFFF;">
<tr><td style="padding:6mm 8mm 5mm 8mm; vertical-align:top;">

<!-- Header row: Logo | Title | TEVETA -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:4mm;">
<tr>
<td style="width:18%; text-align:center; vertical-align:middle;">
@if(file_exists(public_path('assets/images/logo-sm.png')))
<img src="{{ public_path('assets/images/logo-sm.png') }}" style="height:46px;" />
@else
<div style="font-size:10px; color:#1E3A8A; font-weight:bold;">EduTrack</div>
@endif
</td>
<td style="width:64%; text-align:center; vertical-align:middle;">
<div style="font-size:20px; font-weight:bold; color:#1E3A8A; letter-spacing:2px; line-height:1.3;">EDUTRACK COMPUTER</div>
<div style="font-size:20px; font-weight:bold; color:#1E3A8A; letter-spacing:2px; line-height:1.3;">TRAINING COLLEGE</div>
<table cellpadding="0" cellspacing="0" border="0" style="width:55%; margin:2mm auto 1mm auto;">
<tr>
<td style="border-bottom:1.5px solid #D89E2E; width:42%;"></td>
<td style="width:16%; text-align:center; color:#D89E2E; font-size:8px; white-space:nowrap; padding:0 1mm;">&#9733; &#9733; &#9733;</td>
<td style="border-bottom:1.5px solid #D89E2E; width:42%;"></td>
</tr>
</table>
<div style="font-size:9px; color:#666666; font-style:italic; letter-spacing:1px;">A Registered Skill Training College</div>
</td>
<td style="width:18%; text-align:center; vertical-align:middle;">
@if(file_exists(public_path('assets/images/teveta-logo-sm.png')))
<img src="{{ public_path('assets/images/teveta-logo-sm.png') }}" style="height:42px;" />
@else
<div style="font-size:10px; color:#1E3A8A; font-weight:bold;">TEVETA</div>
@endif
</td>
</tr>
</table>

<!-- Gold divider line -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:4mm;">
<tr>
<td style="border-bottom:1.5px solid #D89E2E;"></td>
<td style="width:20px; text-align:center; color:#D89E2E; font-size:10px; padding:0 2mm;">&#9670;</td>
<td style="border-bottom:1.5px solid #D89E2E;"></td>
</tr>
</table>

<!-- Certification text -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:3mm;">
<tr>
<td style="width:20%; vertical-align:middle;"><div style="border-bottom:1px solid #D89E2E;"></div></td>
<td style="width:60%; text-align:center; vertical-align:middle;">
<div style="font-size:14px; font-weight:bold; color:#1E3A8A; letter-spacing:3px;">THIS IS TO CERTIFY THAT</div>
</td>
<td style="width:20%; vertical-align:middle;"><div style="border-bottom:1px solid #D89E2E;"></div></td>
</tr>
</table>

<!-- Student Name -->
<div style="text-align:center; margin:2mm 0 3mm 0;">
<div style="font-size:32px; font-family:'DejaVu Serif', Georgia, serif; font-style:italic; color:#111111; border-bottom:2px solid #D89E2E; display:inline-block; padding:0 12mm 2mm 12mm; line-height:1.2;">
{{ $student_name }}
</div>
</div>

<!-- Body -->
<div style="text-align:center; font-size:10px; color:#444444; line-height:1.8; margin:2mm 0 2mm 0;">
having satisfied the requirements for the<br>
award of the certificate of
</div>

<!-- Course Title -->
<div style="text-align:center; margin:2mm 0 2mm 0;">
<div style="font-size:22px; font-weight:bold; color:#1E3A8A; text-transform:uppercase; letter-spacing:1px; line-height:1.3;">
{{ strtoupper($course_title) }}
</div>
</div>

<!-- Classification -->
@if($classification)
<div style="text-align:center; margin:2mm 0 1mm 0;">
<div style="font-size:20px; font-family:'DejaVu Serif', Georgia, serif; font-style:italic; font-weight:bold; color:#111111;">
With {{ $classification }}
</div>
<table cellpadding="0" cellspacing="0" border="0" style="width:22%; margin:2mm auto;">
<tr>
<td style="border-bottom:1px solid #D89E2E; width:42%;"></td>
<td style="width:16%; text-align:center; color:#D89E2E; font-size:8px;">&#9670;</td>
<td style="border-bottom:1px solid #D89E2E; width:42%;"></td>
</tr>
</table>
</div>
@endif

<!-- Date -->
<div style="text-align:center; font-size:10px; color:#444444; line-height:1.8; margin:2mm 0 3mm 0;">
was admitted to the certificate at a Graduation<br>
Ceremony held on the
<span style="font-size:14px; font-weight:bold; color:#1E3A8A;">&nbsp;{{ $graduation_day }}<sup style="font-size:7px;">{{ $graduation_suffix ?? 'th' }}</sup>&nbsp;</span>
day of
<span style="font-size:14px; font-weight:bold; color:#1E3A8A; font-style:italic;">&nbsp;{{ $graduation_month }}&nbsp;</span><br>
in the year
<span style="font-size:18px; font-weight:bold; color:#1E3A8A;">&nbsp;{{ $graduation_year }}&nbsp;</span>
</div>

<!-- Signatures and Seal -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-top:3mm; margin-bottom:4mm;">
<tr>
<!-- Principal -->
<td style="width:28%; text-align:center; vertical-align:bottom; padding-bottom:2mm;">
<div style="border-top:1px solid #333333; width:80%; margin:0 auto 1mm auto; padding-top:2mm;">
<div style="font-size:8px; color:#333333;">Principal</div>
</div>
<div style="border-top:1px solid #333333; width:80%; margin:8mm auto 0 auto; padding-top:2mm;">
<div style="font-size:8px; color:#333333;">Graduate's Signature</div>
</div>
</td>

<!-- Seal -->
<td style="width:44%; text-align:center; vertical-align:middle;">
<img src="{{ public_path('assets/images/certificate-seal.png') }}" style="height:90px; width:auto;" />
</td>

<!-- Director -->
<td style="width:28%; text-align:center; vertical-align:bottom; padding-bottom:2mm;">
<div style="border-top:1px solid #333333; width:80%; margin:0 auto 1mm auto; padding-top:2mm;">
<div style="font-size:8px; color:#333333;">Director</div>
</div>
<div style="border-top:1px solid #333333; width:80%; margin:8mm auto 0 auto; padding-top:2mm;">
<div style="font-size:8px; color:#333333;">Graduate's I.D. No.</div>
</div>
</td>
</tr>
</table>

<!-- Bottom Info Bar -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; border:1.5px solid #D89E2E; background:#FDFBF7;">
<tr>
<td style="padding:3mm 4mm;">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<tr>
<!-- Left column -->
<td style="width:46%; vertical-align:top;">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:2mm;">
<tr>
<td style="width:24px; text-align:center; vertical-align:middle;">
<div style="width:20px; height:20px; border:1.5px solid #1E3A8A; text-align:center; line-height:17px; color:#1E3A8A; font-size:10px; font-weight:bold;">S</div>
</td>
<td style="padding-left:2mm;">
<div style="font-size:7px; color:#1E3A8A; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">Student Number</div>
<div style="font-size:11px; color:#000000; font-weight:bold;">{{ $student_number }}</div>
</td>
</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<tr>
<td style="width:24px; text-align:center; vertical-align:middle;">
<div style="width:20px; height:20px; border:1.5px solid #1E3A8A; text-align:center; line-height:17px; color:#1E3A8A; font-size:10px; font-weight:bold;">C</div>
</td>
<td style="padding-left:2mm;">
<div style="font-size:7px; color:#1E3A8A; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">Certificate Number</div>
<div style="font-size:11px; color:#000000; font-weight:bold;">{{ $certificate_number }}</div>
</td>
</tr>
</table>
</td>

<!-- Center divider -->
<td style="width:8%; text-align:center; vertical-align:middle;">
<table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
<tr><td style="height:14px; border-left:1px solid #D89E2E;"></td></tr>
<tr><td style="text-align:center; color:#D89E2E; font-size:8px; padding:1px 0;">&#9670;</td></tr>
<tr><td style="height:14px; border-left:1px solid #D89E2E;"></td></tr>
</table>
</td>

<!-- Right column -->
<td style="width:46%; vertical-align:top;">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:2mm;">
<tr>
<td style="width:24px; text-align:center; vertical-align:middle;">
<div style="width:20px; height:20px; border:1.5px solid #1E3A8A; text-align:center; line-height:17px; color:#1E3A8A; font-size:10px; font-weight:bold;">D</div>
</td>
<td style="padding-left:2mm;">
<div style="font-size:7px; color:#1E3A8A; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">Date of Graduation</div>
<div style="font-size:11px; color:#000000; font-weight:bold;">{{ $graduation_day }}<sup style="font-size:6px;">{{ $graduation_suffix ?? 'th' }}</sup> {{ $graduation_month }} {{ $graduation_year }}</div>
</td>
</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<tr>
<td style="width:24px; text-align:center; vertical-align:middle;">
<div style="width:20px; height:20px; border:1.5px solid #1E3A8A; text-align:center; line-height:17px; color:#1E3A8A; font-size:10px; font-weight:bold;">R</div>
</td>
<td style="padding-left:2mm;">
<div style="font-size:7px; color:#1E3A8A; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">Course</div>
<div style="font-size:11px; color:#000000; font-weight:bold;">{{ $course_title }}</div>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>

</td></tr></table><!-- /inner blue border -->
</td></tr></table><!-- /outer gold border -->
</td></tr></table><!-- /page container -->

</body>
</html>
