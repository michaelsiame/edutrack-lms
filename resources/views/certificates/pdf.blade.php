<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 10px;
    color: #1a1a1a;
}
table {
    border-collapse: collapse;
}
img {
    border: 0;
}
</style>
</head>
<body>

<!-- OUTER ORANGE FRAME -->
<table width="210" height="297" cellpadding="0" cellspacing="0" border="0" style="background-color:#f26522;">
<tr><td style="padding:3.5mm;">

<!-- INNER BLUE FRAME -->
<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#1e3a8a;">
<tr><td style="padding:2mm;">

<!-- WHITE CONTENT AREA -->
<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff;">
<tr><td style="padding:5mm 8mm 5mm 8mm; vertical-align:top;">

<!-- ==================== HEADER ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
    <!-- Logo left -->
    <td width="28%" style="vertical-align:middle; text-align:center;">
        <img src="{{ public_path('assets/images/logo-sm.png') }}" style="width:22mm; height:auto;" alt="">
        <div style="font-size:6px; font-weight:700; color:#1e3a8a; text-align:center; line-height:1.3;">Excel Through Education</div>
        <div style="font-size:4.5px; color:#555; text-align:center; line-height:1.4; font-weight:600; text-transform:uppercase;">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
    </td>
    <!-- Center title -->
    <td width="44%" style="vertical-align:middle; text-align:center; padding:0 3mm;">
        <div style="font-size:18px; font-weight:bold; color:#1a1a1a; text-transform:uppercase; letter-spacing:0.5px; line-height:1.2;">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:1.5mm 0;">
            <tr>
                <td width="45%" style="border-top:0.5px solid #1e3a8a;">&nbsp;</td>
                <td width="10%" style="text-align:center; color:#f26522; font-size:8px;">&#9670;</td>
                <td width="45%" style="border-top:0.5px solid #1e3a8a;">&nbsp;</td>
            </tr>
        </table>
        <div style="font-size:8px; color:#444; font-style:italic;">A skill training college</div>
    </td>
    <!-- TEVETA right -->
    <td width="28%" style="vertical-align:middle; text-align:right;">
        <table cellpadding="0" cellspacing="0" border="0" style="display:inline-table; border:1px solid #1e3a8a; border-radius:2px;">
            <tr>
                <td style="padding:2px 4px;">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="vertical-align:middle; padding-right:2px;">
                                <div style="width:12px; height:12px; background:#f26522; border-radius:50%; text-align:center; line-height:12px; color:#fff; font-size:8px; font-weight:900;">7</div>
                            </td>
                            <td style="vertical-align:middle;">
                                <div style="font-size:8px; font-weight:900; color:#1e3a8a; letter-spacing:0.5px; line-height:1;">TEVETA</div>
                                <div style="font-size:4.5px; color:#555; font-weight:600; text-transform:uppercase; letter-spacing:0.2px; line-height:1.3;">Computer<br>Education</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- ==================== CERTIFY BANNER ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:3mm; border-top:1px solid #f26522; border-bottom:1px solid #f26522;">
<tr>
    <td width="25%" style="text-align:right; color:#f26522; font-size:7px; padding:1.5mm 0;">&#8594;&#8594;&#9830;</td>
    <td width="50%" style="text-align:center; font-size:13px; font-weight:900; color:#1e3a8a; letter-spacing:1.5px; text-transform:uppercase; padding:1.5mm 0;">THIS IS TO CERTIFY THAT</td>
    <td width="25%" style="text-align:left; color:#f26522; font-size:7px; padding:1.5mm 0;">&#9830;&#8592;&#8592;</td>
</tr>
</table>

<!-- ==================== STUDENT NAME ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:4mm;">
<tr>
    <td style="text-align:center; padding-bottom:2mm; border-bottom:1px solid #f26522;">
        <div style="font-family:'Times', 'Times New Roman', serif; font-size:28px; font-style:italic; color:#111; line-height:1.2;">{{ $student_name }}</div>
    </td>
</tr>
</table>

<!-- ==================== BODY TEXT ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:3mm;">
<tr>
    <td style="text-align:center; font-size:10px; color:#333; line-height:1.8;">
        having satisfied the requirements for the<br>
        award of the certificate of
    </td>
</tr>
</table>

<!-- ==================== COURSE TITLE ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:2mm;">
<tr>
    <td style="text-align:center; font-size:18px; font-weight:900; color:#1e3a8a; text-transform:uppercase; letter-spacing:1px; line-height:1.2; font-family:'DejaVu Sans', Arial, sans-serif;">
        {{ $course_title }}
    </td>
</tr>
</table>

<!-- ==================== CLASSIFICATION ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:3mm;">
<tr>
    <td style="text-align:center;">
        <div style="font-family:'Times', 'Times New Roman', serif; font-size:20px; font-style:italic; color:#111; font-weight:bold; line-height:1.2;">{{ $classification }}</div>
        <table width="40%" cellpadding="0" cellspacing="0" border="0" style="margin:1mm auto 0 auto;">
            <tr>
                <td width="45%" style="border-top:0.5px solid #f26522;">&nbsp;</td>
                <td width="10%" style="text-align:center; color:#f26522; font-size:7px;">&#9670;</td>
                <td width="45%" style="border-top:0.5px solid #f26522;">&nbsp;</td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- ==================== GRADUATION DATE ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:3mm;">
<tr>
    <td style="text-align:center; font-size:10px; color:#333; line-height:1.8;">
        was admitted to the certificate at a Graduation<br>
        Ceremony held on the
        <span style="font-size:14px; font-weight:bold; color:#1e3a8a;">&nbsp;{{ $graduation_day }}<sup style="font-size:6px;">{{ $graduation_suffix }}</sup>&nbsp;</span>
        day of
        <span style="font-size:14px; font-weight:bold; font-style:italic; color:#1e3a8a;">&nbsp;{{ $graduation_month }}&nbsp;</span><br>
        in the year
        <span style="font-size:20px; font-weight:bold; color:#1e3a8a;">&nbsp;{{ $graduation_year }}&nbsp;</span>
    </td>
</tr>
</table>

<!-- ==================== SIGNATURES + SEAL ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:4mm;">
<tr>
    <!-- Left signatures -->
    <td width="30%" style="vertical-align:middle;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td style="text-align:center; padding-bottom:5mm;">
                    <div style="border-top:0.5px solid #555; width:80%; margin:0 auto;">&nbsp;</div>
                    <div style="font-size:7px; color:#333; font-weight:600; margin-top:1mm;">Principal</div>
                </td>
            </tr>
            <tr>
                <td style="text-align:center;">
                    <div style="border-top:0.5px solid #555; width:80%; margin:0 auto;">&nbsp;</div>
                    <div style="font-size:7px; color:#333; font-weight:600; margin-top:1mm;">Graduate's Signature</div>
                </td>
            </tr>
        </table>
    </td>
    <!-- Center seal -->
    <td width="40%" style="vertical-align:middle; text-align:center;">
        <img src="{{ public_path('assets/images/certificate-seal.png') }}" style="width:28mm; height:auto;" alt="">
        <table cellpadding="0" cellspacing="0" border="0" style="margin:1mm auto 0 auto;">
            <tr>
                <td style="width:5mm; height:6mm; background:#f26522; text-align:center; vertical-align:middle;">&nbsp;</td>
                <td style="width:1mm;">&nbsp;</td>
                <td style="width:5mm; height:6mm; background:#f26522; text-align:center; vertical-align:middle;">&nbsp;</td>
            </tr>
        </table>
    </td>
    <!-- Right signatures -->
    <td width="30%" style="vertical-align:middle;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td style="text-align:center; padding-bottom:5mm;">
                    <div style="border-top:0.5px solid #555; width:80%; margin:0 auto;">&nbsp;</div>
                    <div style="font-size:7px; color:#333; font-weight:600; margin-top:1mm;">Director</div>
                </td>
            </tr>
            <tr>
                <td style="text-align:center;">
                    <div style="border-top:0.5px solid #555; width:80%; margin:0 auto;">&nbsp;</div>
                    <div style="font-size:7px; color:#333; font-weight:600; margin-top:1mm;">Graduate's I.D. No.</div>
                </td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- ==================== INFO BAR ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:4mm; border:1.5px solid #f26522; border-radius:3px;">
<tr>
    <td style="padding:3mm 3mm;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <!-- Left column -->
                <td width="46%" style="vertical-align:top;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="10mm" style="vertical-align:middle;">
                                <div style="width:9mm; height:9mm; border-radius:50%; background:#EEF2FF; border:1px solid #1e3a8a; text-align:center; line-height:9mm; font-size:10px; color:#1e3a8a;">&#127891;</div>
                            </td>
                            <td style="vertical-align:middle; padding-left:2mm;">
                                <div style="font-size:5.5px; font-weight:900; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:0.5mm;">Student Number</div>
                                <div style="font-size:10px; font-weight:bold; color:#111; line-height:1.1;">{{ $student_number }}</div>
                            </td>
                        </tr>
                        <tr><td colspan="2" style="height:2.5mm;">&nbsp;</td></tr>
                        <tr>
                            <td width="10mm" style="vertical-align:middle;">
                                <div style="width:9mm; height:9mm; border-radius:50%; background:#EEF2FF; border:1px solid #1e3a8a; text-align:center; line-height:9mm; font-size:10px; color:#1e3a8a;">&#128196;</div>
                            </td>
                            <td style="vertical-align:middle; padding-left:2mm;">
                                <div style="font-size:5.5px; font-weight:900; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:0.5mm;">Certificate Number</div>
                                <div style="font-size:10px; font-weight:bold; color:#111; line-height:1.1;">{{ $certificate_number }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <!-- Separator -->
                <td width="8%" style="vertical-align:middle; text-align:center;">
                    <table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr><td style="border-right:0.5px solid #f26522;">&nbsp;</td></tr>
                        <tr><td style="text-align:center; color:#f26522; font-size:8px; padding:1mm 0;">&#9670;</td></tr>
                        <tr><td style="border-right:0.5px solid #f26522;">&nbsp;</td></tr>
                    </table>
                </td>
                <!-- Right column -->
                <td width="46%" style="vertical-align:top;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="10mm" style="vertical-align:middle;">
                                <div style="width:9mm; height:9mm; border-radius:50%; background:#EEF2FF; border:1px solid #1e3a8a; text-align:center; line-height:9mm; font-size:10px; color:#1e3a8a;">&#128197;</div>
                            </td>
                            <td style="vertical-align:middle; padding-left:2mm;">
                                <div style="font-size:5.5px; font-weight:900; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:0.5mm;">Date of Graduation</div>
                                <div style="font-size:10px; font-weight:bold; color:#111; line-height:1.1;">{{ $graduation_day }}{{ $graduation_suffix }} {{ $graduation_month }} {{ $graduation_year }}</div>
                            </td>
                        </tr>
                        <tr><td colspan="2" style="height:2.5mm;">&nbsp;</td></tr>
                        <tr>
                            <td width="10mm" style="vertical-align:middle;">
                                <div style="width:9mm; height:9mm; border-radius:50%; background:#EEF2FF; border:1px solid #1e3a8a; text-align:center; line-height:9mm; font-size:10px; color:#f26522;">&#127941;</div>
                            </td>
                            <td style="vertical-align:middle; padding-left:2mm;">
                                <div style="font-size:5.5px; font-weight:900; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:0.5mm;">Course</div>
                                <div style="font-size:10px; font-weight:bold; color:#111; line-height:1.1;">{{ $course_title }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- ==================== FOOTER ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:3mm;">
<tr>
    <td width="45%" style="border-top:0.5px solid #f26522;">&nbsp;</td>
    <td width="10%" style="text-align:center; color:#f26522; font-size:8px;">&#9670;</td>
    <td width="45%" style="border-top:0.5px solid #f26522;">&nbsp;</td>
</tr>
</table>

<!-- ==================== VERIFICATION ==================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:2mm;">
<tr>
    <td style="text-align:center; font-size:7px; color:#666;">
        Verification Code: <strong>{{ $verification_code }}</strong> | Verify at {{ config('app.url') }}/certificates/verify
    </td>
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
