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
    background: #E87722;
}
</style>
</head>
<body>

{{--
    LOGO PATHS (absolute paths required by TCPDF):
      public_path('images/edutrack-logo.png')  — EduTrack shield logo
      public_path('images/teveta-logo.png')    — TEVETA logo
    Place both files in /public/images/ or adjust the paths below.
--}}

<!-- ═══ ORANGE OUTER FRAME ═══ -->
<table cellpadding="0" cellspacing="0" border="0" style="width:210mm; background:#E87722; border-collapse:collapse;">
<tr><td style="padding:5mm;">

<!-- ═══ BLUE INNER FRAME ═══ -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; background:#1B3A8C; border-collapse:collapse;">
<tr><td style="padding:3.5mm;">

<!-- ═══ WHITE CONTENT AREA ═══ -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; background:#FFFFFF; border-collapse:collapse;">
<tr><td style="padding:6mm 7mm 5mm 7mm; vertical-align:top;">

<!-- ──────────────────────────────────────── -->
<!-- HEADER: EduTrack Logo | Name | TEVETA    -->
<!-- ──────────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:2mm;">
<tr>
    <td style="width:20%; vertical-align:middle; text-align:center;">
        <img src="{{ public_path('images/edutrack-logo.png') }}"
             style="width:30mm; height:auto;" />
    </td>
    <td style="width:60%; vertical-align:middle; text-align:center;">
        <div style="font-size:22px; font-weight:bold; color:#1B3A8C;
                    text-transform:uppercase; letter-spacing:1px; line-height:1.35;">
            EDUTRACK COMPUTER<br>TRAINING COLLEGE
        </div>
    </td>
    <td style="width:20%; vertical-align:middle; text-align:center;">
        <img src="{{ public_path('images/teveta-logo.png') }}"
             style="width:25mm; height:auto;" />
    </td>
</tr>
</table>

<!-- GOLD DIVIDER -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:2mm;">
<tr>
    <td style="border-top:1.5px solid #C89D3C; height:1px;"></td>
    <td style="width:16px; text-align:center; vertical-align:middle; color:#C89D3C;
               font-size:11px; padding:0 3px;">&#9670;</td>
    <td style="border-top:1.5px solid #C89D3C; height:1px;"></td>
</tr>
</table>

<!-- SUBTITLE -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:4mm;">
<tr><td style="text-align:center; font-size:10px; color:#666666;
               font-style:italic; letter-spacing:1px;">A skill training college</td></tr>
</table>

<!-- ──────────────────────────────────────────── -->
<!-- "THIS IS TO CERTIFY THAT" banner             -->
<!-- ──────────────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="0"
       style="width:100%; margin-bottom:4mm;
              border-top:2px solid #E87722; border-bottom:2px solid #E87722;">
<tr>
<td style="padding:3mm 0; text-align:center; vertical-align:middle;">
    <span style="color:#C89D3C; font-size:9px; letter-spacing:-1px;">&#10145;&#10145;&#9830;&nbsp;</span><span style="font-size:12px; font-weight:bold; color:#1B3A8C; letter-spacing:3px; text-transform:uppercase;">THIS IS TO CERTIFY THAT</span><span style="color:#C89D3C; font-size:9px; letter-spacing:-1px;">&nbsp;&#9830;&#10144;&#10144;</span>
</td>
</tr>
</table>

<!-- STUDENT NAME with gold underline -->
<table cellpadding="0" cellspacing="0" border="0"
       style="width:80%; margin-bottom:4mm;" align="center">
<tr>
    <td style="text-align:center; padding-bottom:2mm; border-bottom:1.5px solid #C89D3C;">
        <span style="font-size:38px; font-style:italic; color:#111111;
                     font-family:'DejaVu Serif'; letter-spacing:1px;">{{ $student_name }}</span>
    </td>
</tr>
</table>

<!-- BODY TEXT -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:3mm;">
<tr><td style="text-align:center; font-size:11px; color:#333333; line-height:1.9;">
    having satisfied the requirements for the<br>award of the certificate of
</td></tr>
</table>

<!-- COURSE TITLE -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:2mm;">
<tr><td style="text-align:center;">
    <span style="font-size:26px; font-weight:bold; color:#1B3A8C;
                 text-transform:uppercase; letter-spacing:1px; line-height:1.3;">{{ strtoupper($course_title) }}</span>
</td></tr>
</table>

<!-- CLASSIFICATION (only if not plain Pass) -->
@if($classification && $classification !== 'Pass')
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:1mm;">
<tr><td style="text-align:center;">
    <span style="font-size:20px; font-style:italic; font-weight:bold; color:#1A1A1A;
                 font-family:'DejaVu Serif';">With {{ $classification }}</span>
</td></tr>
</table>
@endif

<!-- SMALL CENTRED GOLD DIVIDER -->
<table cellpadding="0" cellspacing="0" border="0"
       style="width:55%; margin:3mm auto 3mm auto;">
<tr>
    <td style="border-top:1px solid #C89D3C; height:1px;"></td>
    <td style="width:14px; text-align:center; vertical-align:middle;
               color:#C89D3C; font-size:10px; padding:0 3px;">&#9670;</td>
    <td style="border-top:1px solid #C89D3C; height:1px;"></td>
</tr>
</table>

<!-- GRADUATION DATE -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:5mm;">
<tr><td style="text-align:center; font-size:11px; color:#333333; line-height:1.9;">
    was admitted to the certificate at a Graduation<br>
    Ceremony held on the
    <span style="font-size:14px; font-weight:bold; color:#1B3A8C;">&nbsp;{{ $graduation_day }}<sup style="font-size:7px;">{{ $graduation_suffix }}</sup>&nbsp;</span>
    day of
    <span style="font-size:14px; font-weight:bold; font-style:italic; color:#1B3A8C;">&nbsp;{{ $graduation_month }}&nbsp;</span><br>
    in the year
    <span style="font-size:20px; font-weight:bold; color:#1B3A8C;">&nbsp;{{ $graduation_year }}&nbsp;</span>
</td></tr>
</table>

<!-- ──────────────────────────────────────────── -->
<!-- SIGNATURES + SEAL ROW                        -->
<!-- ──────────────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:5mm;">
<tr>

    <!-- Principal + Graduate's Signature -->
    <td style="width:30%; vertical-align:bottom; text-align:center; padding-bottom:3mm;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr><td style="border-top:1px solid #000000; width:80%; padding-top:2mm; text-align:center; margin:0 auto;">
            &nbsp;
        </td></tr>
        <tr><td style="text-align:center; font-size:8px; color:#333333; font-weight:bold;">Principal</td></tr>
        </table>
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-top:9mm;">
        <tr><td style="border-top:1px solid #000000; width:80%; padding-top:2mm; text-align:center;">
            &nbsp;
        </td></tr>
        <tr><td style="text-align:center; font-size:8px; color:#333333; font-weight:bold;">Graduate's Signature</td></tr>
        </table>
    </td>

    <!-- Seal (centre) -->
    <td style="width:40%; text-align:center; vertical-align:middle; padding-bottom:3mm;">
        <!-- Blue circular badge approximation -->
        <table cellpadding="0" cellspacing="0" border="3" bordercolor="#1B3A8C"
               style="width:72px; height:72px; margin:0 auto; background:#1B3A8C; border-collapse:collapse;">
        <tr>
            <td style="text-align:center; vertical-align:middle; padding:4px 2px;">
                <div style="color:#C89D3C; font-size:13px; line-height:1.2;">&#9733;</div>
                <div style="font-size:6px; font-weight:bold; color:#C89D3C;
                            letter-spacing:0.5px; line-height:1.5; text-transform:uppercase;">
                    EDUTRACK<br>COMPUTER<br>COLLEGE
                </div>
                <div style="color:#C89D3C; font-size:8px; line-height:1;">&#9830;</div>
            </td>
        </tr>
        </table>
        <!-- Orange ribbon below -->
        <table cellpadding="0" cellspacing="0" border="0"
               style="width:30px; margin:0 auto; border-collapse:collapse;">
        <tr>
            <td style="background:#E87722; width:14px; height:18px;
                       border-right:1px solid #FFFFFF;"></td>
            <td style="background:#E87722; width:14px; height:18px;"></td>
        </tr>
        </table>
    </td>

    <!-- Director + Graduate's ID -->
    <td style="width:30%; vertical-align:bottom; text-align:center; padding-bottom:3mm;">
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr><td style="border-top:1px solid #000000; width:80%; padding-top:2mm; text-align:center;">
            &nbsp;
        </td></tr>
        <tr><td style="text-align:center; font-size:8px; color:#333333; font-weight:bold;">Director</td></tr>
        </table>
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-top:9mm;">
        <tr><td style="border-top:1px solid #000000; width:80%; padding-top:2mm; text-align:center;">
            &nbsp;
        </td></tr>
        <tr><td style="text-align:center; font-size:8px; color:#333333; font-weight:bold;">Graduate's I.D. No.</td></tr>
        </table>
    </td>

</tr>
</table>

<!-- ──────────────────────────────────────────── -->
<!-- BOTTOM INFO BAR  (2 × 2 grid)               -->
<!-- ──────────────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="1" bordercolor="#E87722"
       style="width:100%; background:#FDFBF7; border-collapse:collapse;">
<tr>
<td style="padding:4mm 5mm;">

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tr>

        <!-- ── LEFT COLUMN ── -->
        <td style="width:46%; vertical-align:top;">

            <!-- Student Number -->
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:3mm;">
            <tr>
                <td style="width:11mm; vertical-align:middle; text-align:center;">
                    <table cellpadding="0" cellspacing="0" border="2" bordercolor="#1B3A8C"
                           style="width:9mm; height:9mm; background:#EEF2FF; border-collapse:collapse;" align="center">
                    <tr><td style="text-align:center; vertical-align:middle;
                                   font-size:10px; color:#1B3A8C; font-weight:bold;">&#9673;</td></tr>
                    </table>
                </td>
                <td style="padding-left:2mm; vertical-align:top;">
                    <div style="font-size:7px; font-weight:bold; color:#1B3A8C;
                                text-transform:uppercase; letter-spacing:0.5px;">Student Number</div>
                    <div style="font-size:11px; font-weight:bold; color:#000000;
                                margin-top:0.5mm;">{{ $student_number }}</div>
                </td>
            </tr>
            </table>

            <!-- Certificate Number -->
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
            <tr>
                <td style="width:11mm; vertical-align:middle; text-align:center;">
                    <table cellpadding="0" cellspacing="0" border="2" bordercolor="#1B3A8C"
                           style="width:9mm; height:9mm; background:#EEF2FF; border-collapse:collapse;" align="center">
                    <tr><td style="text-align:center; vertical-align:middle;
                                   font-size:10px; color:#1B3A8C; font-weight:bold;">&#9635;</td></tr>
                    </table>
                </td>
                <td style="padding-left:2mm; vertical-align:top;">
                    <div style="font-size:7px; font-weight:bold; color:#1B3A8C;
                                text-transform:uppercase; letter-spacing:0.5px;">Certificate Number</div>
                    <div style="font-size:11px; font-weight:bold; color:#000000;
                                margin-top:0.5mm;">{{ $certificate_number }}</div>
                </td>
            </tr>
            </table>

        </td>

        <!-- ── CENTER DIVIDER ── -->
        <td style="width:8%; text-align:center; vertical-align:middle;">
            <table cellpadding="0" cellspacing="0" border="0"
                   style="width:20px; height:100%; margin:0 auto;">
            <tr>
                <td style="height:18px; border-right:1.5px solid #E87722;">&nbsp;</td>
            </tr>
            <tr>
                <td style="text-align:center; padding:2mm 0;
                           color:#E87722; font-size:13px; font-weight:bold;">&#9670;</td>
            </tr>
            <tr>
                <td style="height:18px; border-right:1.5px solid #E87722;">&nbsp;</td>
            </tr>
            </table>
        </td>

        <!-- ── RIGHT COLUMN ── -->
        <td style="width:46%; vertical-align:top;">

            <!-- Date of Graduation -->
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:3mm;">
            <tr>
                <td style="width:11mm; vertical-align:middle; text-align:center;">
                    <table cellpadding="0" cellspacing="0" border="2" bordercolor="#1B3A8C"
                           style="width:9mm; height:9mm; background:#EEF2FF; border-collapse:collapse;" align="center">
                    <tr><td style="text-align:center; vertical-align:middle;
                                   font-size:10px; color:#1B3A8C; font-weight:bold;">&#9632;</td></tr>
                    </table>
                </td>
                <td style="padding-left:2mm; vertical-align:top;">
                    <div style="font-size:7px; font-weight:bold; color:#1B3A8C;
                                text-transform:uppercase; letter-spacing:0.5px;">Date of Graduation</div>
                    <div style="font-size:11px; font-weight:bold; color:#000000; margin-top:0.5mm;">
                        {{ $graduation_day }}<sup style="font-size:6px;">{{ $graduation_suffix }}</sup>
                        {{ $graduation_month }} {{ $graduation_year }}
                    </div>
                </td>
            </tr>
            </table>

            <!-- Course -->
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
            <tr>
                <td style="width:11mm; vertical-align:middle; text-align:center;">
                    <table cellpadding="0" cellspacing="0" border="2" bordercolor="#1B3A8C"
                           style="width:9mm; height:9mm; background:#EEF2FF; border-collapse:collapse;" align="center">
                    <tr><td style="text-align:center; vertical-align:middle;
                                   font-size:10px; color:#C89D3C; font-weight:bold;">&#9733;</td></tr>
                    </table>
                </td>
                <td style="padding-left:2mm; vertical-align:top;">
                    <div style="font-size:7px; font-weight:bold; color:#1B3A8C;
                                text-transform:uppercase; letter-spacing:0.5px;">Course</div>
                    <div style="font-size:11px; font-weight:bold; color:#000000;
                                margin-top:0.5mm;">{{ $course_title }}</div>
                </td>
            </tr>
            </table>

        </td>

    </tr>
    </table>

</td>
</tr>
</table>

<!-- BOTTOM GOLD DIVIDER -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-top:3mm;">
<tr>
    <td style="border-top:1px solid #C89D3C; height:1px;"></td>
    <td style="width:16px; text-align:center; vertical-align:middle;
               color:#C89D3C; font-size:12px; padding:0 3px;">&#9670;</td>
    <td style="border-top:1px solid #C89D3C; height:1px;"></td>
</tr>
</table>

<!-- ═══ END CONTENT ═══ -->
</td></tr>
</table>

</td></tr>
</table>

</td></tr>
</table>

</body>
</html>