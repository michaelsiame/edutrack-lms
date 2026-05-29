<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page {
    size: 210mm 297mm;
    margin: 0;
}
body {
    margin: 0;
    padding: 0;
    font-family: 'DejaVu Serif', Georgia, 'Times New Roman', serif;
}
</style>
</head>
<body>

<table cellpadding="0" cellspacing="0" border="0" style="width:210mm; background:#f26522; border-collapse:collapse;">
<tr><td style="padding:2mm;">

<table cellpadding="0" cellspacing="0" border="0" style="width:100%; background:#1e3a8a; border-collapse:collapse;">
<tr><td style="padding:1mm;">

<table cellpadding="0" cellspacing="0" border="0" style="width:100%; background:#FFFFFF; border-collapse:collapse;">
<tr><td style="padding:3mm 5mm 2mm 5mm; vertical-align:top;">

<!-- HEADER -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:1mm;">
<tr>
    <td style="width:20%; vertical-align:top; text-align:center;">
        <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
        <tr>
            <td style="background:#1e3a8a; border-radius:0 0 20px 20px; border:1.5px solid #f26522; padding:4px 3px 3px 3px; text-align:center;">
                <div style="color:#fff; font-size:6px; font-weight:700; line-height:1;">EduTrack</div>
                <div style="color:#f26522; font-size:4px; line-height:1; margin-top:1px;">Excel Through<br>Education</div>
            </td>
        </tr>
        </table>
        <div style="font-size:4px; color:#1e3a8a; font-weight:600; margin-top:1px; text-align:center; line-height:1.1;">EDUTRACK COMPUTER TRAINING COLLEGE</div>
    </td>
    <td style="width:60%; vertical-align:middle; text-align:center;">
        <div style="font-family:'DejaVu Serif'; font-size:18px; font-weight:bold; color:#1a1a1a; text-transform:uppercase; letter-spacing:1px; line-height:1.25;">
            EDUTRACK COMPUTER<br>TRAINING COLLEGE
        </div>
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin:1mm 0;">
        <tr>
            <td style="border-top:1px solid #1e3a8a; height:1px; width:35%;"></td>
            <td style="width:30%; text-align:center; vertical-align:middle; color:#f26522; font-size:8px;">&#9670;</td>
            <td style="border-top:1px solid #1e3a8a; height:1px; width:35%;"></td>
        </tr>
        </table>
        <div style="font-family:'DejaVu Serif'; font-size:8px; color:#444; font-style:italic;">A skill training college</div>
    </td>
    <td style="width:20%; vertical-align:top; text-align:center;">
        <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
        <tr>
            <td style="width:20px; height:20px; background:#1e3a8a; border-radius:50%; text-align:center; vertical-align:middle;">
                <span style="color:#fff; font-size:10px; font-weight:800;">T</span>
            </td>
        </tr>
        </table>
        <div style="font-size:6px; font-weight:800; color:#1e3a8a; letter-spacing:0.5px; line-height:1; margin-top:1px;">TEVETA</div>
        <div style="font-size:4px; color:#1e3a8a; font-weight:600;">ACCREDITED</div>
    </td>
</tr>
</table>

<!-- CERTIFY BANNER -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:2mm; border-top:1.5px solid #f26522; border-bottom:1.5px solid #f26522;">
<tr>
<td style="padding:2mm 0; text-align:center; vertical-align:middle;">
    <span style="color:#f26522; font-size:7px;">&#10145;&#10145;&#9830;&nbsp;</span>
    <span style="font-size:11px; font-weight:bold; color:#1e3a8a; letter-spacing:2px; text-transform:uppercase;">THIS IS TO CERTIFY THAT</span>
    <span style="color:#f26522; font-size:7px;">&nbsp;&#9830;&#10144;&#10144;</span>
</td>
</tr>
</table>

<!-- STUDENT NAME -->
<table cellpadding="0" cellspacing="0" border="0" style="width:75%; margin-bottom:2mm;" align="center">
<tr>
    <td style="text-align:center; padding-bottom:1.5mm; border-bottom:1px solid #f26522;">
        <span style="font-size:32px; font-style:italic; color:#111111; font-family:'DejaVu Serif'; letter-spacing:1px;">{{ $student_name }}</span>
    </td>
</tr>
</table>

<!-- BODY TEXT -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:2mm;">
<tr><td style="text-align:center; font-size:10px; color:#333333; line-height:1.8;">
    having satisfied the requirements for the<br>award of the certificate of
</td></tr>
</table>

<!-- COURSE TITLE -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:1mm;">
<tr><td style="text-align:center;">
    <span style="font-size:22px; font-weight:bold; color:#1e3a8a; text-transform:uppercase; letter-spacing:1px; line-height:1.2;">{{ strtoupper($course_title) }}</span>
</td></tr>
</table>

<!-- CLASSIFICATION -->
@if($classification && $classification !== 'Pass')
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:1mm;">
<tr><td style="text-align:center;">
    <span style="font-size:16px; font-style:italic; font-weight:bold; color:#1A1A1A; font-family:'DejaVu Serif';">With {{ $classification }}</span>
</td></tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" style="width:45%; margin:1.5mm auto;" align="center">
<tr>
    <td style="border-top:1px solid #f26522; height:1px;"></td>
    <td style="width:12px; text-align:center; vertical-align:middle; color:#f26522; font-size:9px;">&#9670;</td>
    <td style="border-top:1px solid #f26522; height:1px;"></td>
</tr>
</table>
@endif

<!-- GRADUATION DATE -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:3mm;">
<tr><td style="text-align:center; font-size:10px; color:#333333; line-height:1.8;">
    was admitted to the certificate at a Graduation<br>
    Ceremony held on the
    <span style="font-size:13px; font-weight:bold; color:#1e3a8a;">&nbsp;{{ $graduation_day }}<sup style="font-size:6px;">{{ $graduation_suffix }}</sup>&nbsp;</span>
    day of
    <span style="font-size:13px; font-weight:bold; font-style:italic; color:#1e3a8a;">&nbsp;{{ $graduation_month }}&nbsp;</span><br>
    in the year
    <span style="font-size:18px; font-weight:bold; color:#1e3a8a;">&nbsp;{{ $graduation_year }}&nbsp;</span>
</td></tr>
</table>

<!-- SIGNATURES + SEAL -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:3mm;">
<tr>
    <td style="width:30%; vertical-align:bottom; text-align:center; padding-bottom:2mm;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr><td style="border-top:1px solid #000000; width:80%; padding-top:1.5mm; text-align:center;">&nbsp;</td></tr>
        <tr><td style="text-align:center; font-size:7px; color:#333333; font-weight:bold;">Principal</td></tr>
        </table>
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-top:6mm;">
        <tr><td style="border-top:1px solid #000000; width:80%; padding-top:1.5mm; text-align:center;">&nbsp;</td></tr>
        <tr><td style="text-align:center; font-size:7px; color:#333333; font-weight:bold;">Graduate's Signature</td></tr>
        </table>
    </td>
    <td style="width:40%; text-align:center; vertical-align:middle; padding-bottom:2mm;">
        <table cellpadding="0" cellspacing="0" border="3" bordercolor="#1e3a8a" style="width:64px; height:64px; margin:0 auto; background:#1e3a8a; border-collapse:collapse;">
        <tr>
            <td style="text-align:center; vertical-align:middle; padding:3px 2px;">
                <div style="color:#d4af37; font-size:11px; line-height:1.1;">&#9733;</div>
                <div style="font-size:5px; font-weight:bold; color:#d4af37; letter-spacing:0.3px; line-height:1.3; text-transform:uppercase;">EXCELLENCE</div>
                <div style="color:#d4af37; font-size:7px; line-height:1;">&#9830;</div>
            </td>
        </tr>
        </table>
        <table cellpadding="0" cellspacing="0" border="0" style="width:26px; margin:0 auto; border-collapse:collapse;">
        <tr>
            <td style="background:#f26522; width:12px; height:14px; border-right:1px solid #FFFFFF;"></td>
            <td style="background:#f26522; width:12px; height:14px;"></td>
        </tr>
        </table>
    </td>
    <td style="width:30%; vertical-align:bottom; text-align:center; padding-bottom:2mm;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr><td style="border-top:1px solid #000000; width:80%; padding-top:1.5mm; text-align:center;">&nbsp;</td></tr>
        <tr><td style="text-align:center; font-size:7px; color:#333333; font-weight:bold;">Director</td></tr>
        </table>
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-top:6mm;">
        <tr><td style="border-top:1px solid #000000; width:80%; padding-top:1.5mm; text-align:center;">&nbsp;</td></tr>
        <tr><td style="text-align:center; font-size:7px; color:#333333; font-weight:bold;">Graduate's I.D. No.</td></tr>
        </table>
    </td>
</tr>
</table>

<!-- BOTTOM INFO BAR -->
<table cellpadding="0" cellspacing="0" border="1" bordercolor="#f26522" style="width:100%; background:#fdf8f3; border-collapse:collapse;">
<tr>
<td style="padding:2mm 3mm;">
    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tr>
        <td style="width:46%; vertical-align:top;">
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:1.5mm;">
            <tr>
                <td style="width:10mm; vertical-align:middle; text-align:center;">
                    <table cellpadding="0" cellspacing="0" border="1" bordercolor="#1e3a8a" style="width:8mm; height:8mm; background:#EEF2FF; border-collapse:collapse;" align="center">
                    <tr><td style="text-align:center; vertical-align:middle; font-size:9px; color:#1e3a8a; font-weight:bold;">&#9673;</td></tr>
                    </table>
                </td>
                <td style="padding-left:1.5mm; vertical-align:top;">
                    <div style="font-size:6px; font-weight:bold; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.3px;">Student Number</div>
                    <div style="font-size:10px; font-weight:bold; color:#000000; margin-top:0.3mm;">{{ $student_number }}</div>
                </td>
            </tr>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
            <tr>
                <td style="width:10mm; vertical-align:middle; text-align:center;">
                    <table cellpadding="0" cellspacing="0" border="1" bordercolor="#1e3a8a" style="width:8mm; height:8mm; background:#EEF2FF; border-collapse:collapse;" align="center">
                    <tr><td style="text-align:center; vertical-align:middle; font-size:9px; color:#1e3a8a; font-weight:bold;">&#9635;</td></tr>
                    </table>
                </td>
                <td style="padding-left:1.5mm; vertical-align:top;">
                    <div style="font-size:6px; font-weight:bold; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.3px;">Certificate Number</div>
                    <div style="font-size:10px; font-weight:bold; color:#000000; margin-top:0.3mm;">{{ $certificate_number }}</div>
                </td>
            </tr>
            </table>
        </td>
        <td style="width:8%; text-align:center; vertical-align:middle;">
            <table cellpadding="0" cellspacing="0" border="0" style="width:16px; height:100%; margin:0 auto;">
            <tr><td style="height:14px; border-right:1px solid #f26522;">&nbsp;</td></tr>
            <tr><td style="text-align:center; padding:1.5mm 0; color:#f26522; font-size:11px; font-weight:bold;">&#9670;</td></tr>
            <tr><td style="height:14px; border-right:1px solid #f26522;">&nbsp;</td></tr>
            </table>
        </td>
        <td style="width:46%; vertical-align:top;">
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:1.5mm;">
            <tr>
                <td style="width:10mm; vertical-align:middle; text-align:center;">
                    <table cellpadding="0" cellspacing="0" border="1" bordercolor="#1e3a8a" style="width:8mm; height:8mm; background:#EEF2FF; border-collapse:collapse;" align="center">
                    <tr><td style="text-align:center; vertical-align:middle; font-size:9px; color:#1e3a8a; font-weight:bold;">&#9632;</td></tr>
                    </table>
                </td>
                <td style="padding-left:1.5mm; vertical-align:top;">
                    <div style="font-size:6px; font-weight:bold; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.3px;">Date of Graduation</div>
                    <div style="font-size:10px; font-weight:bold; color:#000000; margin-top:0.3mm;">
                        {{ $graduation_day }}<sup style="font-size:5px;">{{ $graduation_suffix }}</sup>
                        {{ $graduation_month }} {{ $graduation_year }}
                    </div>
                </td>
            </tr>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
            <tr>
                <td style="width:10mm; vertical-align:middle; text-align:center;">
                    <table cellpadding="0" cellspacing="0" border="1" bordercolor="#1e3a8a" style="width:8mm; height:8mm; background:#EEF2FF; border-collapse:collapse;" align="center">
                    <tr><td style="text-align:center; vertical-align:middle; font-size:9px; color:#f26522; font-weight:bold;">&#9733;</td></tr>
                    </table>
                </td>
                <td style="padding-left:1.5mm; vertical-align:top;">
                    <div style="font-size:6px; font-weight:bold; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.3px;">Course</div>
                    <div style="font-size:10px; font-weight:bold; color:#000000; margin-top:0.3mm;">{{ $course_title }}</div>
                </td>
            </tr>
            </table>
        </td>
    </tr>
    </table>
</td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-top:1.5mm;">
<tr>
    <td style="border-top:1px solid #f26522; height:1px;"></td>
    <td style="width:14px; text-align:center; vertical-align:middle; color:#f26522; font-size:11px; padding:0 2px;">&#9670;</td>
    <td style="border-top:1px solid #f26522; height:1px;"></td>
</tr>
</table>

</td></tr>
</table>

</td></tr>
</table>

</td></tr>
</table>

</body>
</html>
