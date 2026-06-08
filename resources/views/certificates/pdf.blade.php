<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page {
    size: 297mm 210mm;
    margin: 0;
}
* { margin: 0; padding: 0; }
body {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 10pt;
    color: #1a1a1a;
}

/* Main page container */
.page {
    width: 297mm;
    height: 210mm;
    background: #f26522;
    padding: 4mm;
}
.inner {
    width: 100%;
    height: 100%;
    background: #1e3a8a;
    padding: 2.5mm;
}
.content {
    width: 100%;
    height: 100%;
    background: #ffffff;
    padding: 8mm 12mm;
}

/* Header */
.header-table {
    width: 100%;
    border-collapse: collapse;
}
.header-table td {
    vertical-align: middle;
    padding: 0;
}
.college-name {
    font-size: 18pt;
    font-weight: bold;
    color: #1a1a1a;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    line-height: 1.2;
    text-align: center;
}
.tagline {
    font-size: 9pt;
    color: #444;
    font-style: italic;
    text-align: center;
    margin-top: 1mm;
}
.teveta-box {
    border: 1.5pt solid #1e3a8a;
    border-radius: 2pt;
    padding: 2mm 3mm;
    display: inline-block;
}
.teveta-name {
    font-size: 10pt;
    font-weight: 900;
    color: #1e3a8a;
    letter-spacing: 0.5pt;
}
.teveta-sub {
    font-size: 5.5pt;
    color: #555;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3pt;
}
.teveta-icon {
    width: 5mm;
    height: 5mm;
    background: #f26522;
    border-radius: 50%;
    text-align: center;
    line-height: 5mm;
    color: #fff;
    font-size: 7pt;
    font-weight: 900;
}

/* Divider */
.divider-table {
    width: 100%;
    margin: 1.5mm 0;
}
.divider-table td {
    border-top: 0.5pt solid #1e3a8a;
    height: 0;
}
.divider-diamond {
    color: #f26522;
    font-size: 8pt;
    text-align: center;
    width: 8mm;
}

/* Banner */
.banner-table {
    width: 100%;
    border-collapse: collapse;
    border-top: 1pt solid #f26522;
    border-bottom: 1pt solid #f26522;
    margin: 3mm 0;
}
.banner-table td {
    padding: 2mm 0;
    text-align: center;
}
.banner-text {
    font-size: 13pt;
    font-weight: 900;
    color: #1e3a8a;
    letter-spacing: 1.5pt;
    text-transform: uppercase;
}
.banner-deco {
    color: #f26522;
    font-size: 8pt;
}

/* Student name */
.student-name {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 30pt;
    font-style: italic;
    color: #111;
    text-align: center;
    border-bottom: 1pt solid #f26522;
    padding-bottom: 2mm;
    margin: 2mm auto 3mm auto;
    width: 85%;
}

/* Body */
.body-text {
    text-align: center;
    font-size: 11pt;
    color: #333;
    line-height: 1.8;
    margin: 2mm 0;
}

/* Course */
.course-title {
    text-align: center;
    font-size: 20pt;
    font-weight: 900;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 1pt;
    line-height: 1.2;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    margin: 2mm 0;
}

/* Classification */
.classification {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 20pt;
    font-style: italic;
    color: #111;
    font-weight: bold;
    text-align: center;
    margin: 2mm 0;
}
.class-divider {
    width: 35%;
    margin: 0 auto;
    border-top: 1pt solid #f26522;
    position: relative;
}
.class-diamond {
    color: #f26522;
    font-size: 7pt;
    text-align: center;
    margin-top: -2.5mm;
}

/* Graduation */
.grad-section {
    text-align: center;
    font-size: 11pt;
    color: #333;
    line-height: 1.8;
    margin: 3mm 0;
}
.g-day {
    font-size: 14pt;
    font-weight: bold;
    color: #1e3a8a;
}
.g-month {
    font-size: 14pt;
    font-weight: bold;
    font-style: italic;
    color: #1e3a8a;
}
.g-year {
    font-size: 20pt;
    font-weight: bold;
    color: #1e3a8a;
}
sup { font-size: 6pt; }

/* Signatures */
.sig-table {
    width: 100%;
    border-collapse: collapse;
    margin: 4mm 0;
}
.sig-table td {
    vertical-align: top;
    padding: 0;
}
.sig-item {
    text-align: center;
    margin-bottom: 6mm;
}
.sig-line {
    border-top: 0.5pt solid #555;
    width: 70%;
    margin: 0 auto 1mm auto;
    height: 0;
}
.sig-label {
    font-size: 8pt;
    color: #333;
    font-weight: 600;
}
.seal-img {
    width: 26mm;
    height: auto;
    display: block;
    margin: 0 auto;
}

/* Info bar */
.info-bar {
    border: 1.5pt solid #f26522;
    border-radius: 3pt;
    padding: 3mm 5mm;
    margin-top: 3mm;
}
.info-table {
    width: 100%;
    border-collapse: collapse;
}
.info-table td {
    vertical-align: middle;
}
.info-icon {
    width: 9mm;
    height: 9mm;
    border-radius: 50%;
    background: #EEF2FF;
    border: 1pt solid #1e3a8a;
    text-align: center;
    line-height: 9mm;
    font-size: 8pt;
    font-weight: bold;
    color: #1e3a8a;
}
.info-icon.gold {
    color: #f26522;
    border-color: #f26522;
}
.info-label {
    font-size: 6.5pt;
    font-weight: 900;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.3pt;
    margin-bottom: 0.5mm;
}
.info-value {
    font-size: 10pt;
    font-weight: bold;
    color: #111;
    line-height: 1.2;
}
.info-sep {
    width: 8mm;
    text-align: center;
    vertical-align: middle;
}
.sep-v {
    border-right: 0.5pt solid #f26522;
    height: 8mm;
    width: 0;
    margin: 0 auto;
}
.sep-diamond {
    color: #f26522;
    font-size: 10pt;
    line-height: 1;
    margin: 1mm 0;
}

/* Footer */
.footer {
    margin-top: 3mm;
}
.footer-line {
    border-top: 0.5pt solid #f26522;
    text-align: center;
    position: relative;
}
.footer-diamond {
    color: #f26522;
    font-size: 8pt;
    background: #fff;
    padding: 0 2mm;
    display: inline-block;
    margin-top: -2.5mm;
}
.verify-text {
    font-size: 7pt;
    color: #666;
    text-align: center;
    margin-top: 1mm;
}
</style>
</head>
<body>

<div class="page">
<div class="inner">
<div class="content">

<!-- HEADER -->
<table class="header-table">
<tr>
    <td width="25%" style="text-align:center;">
        <img src="{{ public_path('assets/images/logo-sm.png') }}" style="width:18mm;height:auto;display:block;margin:0 auto;" alt="">
        <div style="font-size:7pt;font-weight:700;color:#1e3a8a;text-align:center;line-height:1.3;">Excel Through Education</div>
        <div style="font-size:5pt;color:#555;text-align:center;line-height:1.3;font-weight:600;text-transform:uppercase;">EDUTRACK COMPUTER TRAINING COLLEGE</div>
    </td>
    <td width="50%">
        <div class="college-name">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
        <table class="divider-table"><tr><td></td><td class="divider-diamond">&#9670;</td><td></td></tr></table>
        <div class="tagline">A skill training college</div>
    </td>
    <td width="25%" style="text-align:right;">
        <table class="teveta-box" cellpadding="0" cellspacing="0">
            <tr>
                <td><div class="teveta-icon">7</div></td>
                <td style="padding-left:1.5mm;">
                    <div class="teveta-name">TEVETA</div>
                    <div class="teveta-sub">Computer<br>Education</div>
                </td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- CERTIFY BANNER -->
<table class="banner-table">
<tr>
    <td width="20%" class="banner-deco" style="text-align:right;">&#8594;&#8594;&#9830;</td>
    <td width="60%"><span class="banner-text">THIS IS TO CERTIFY THAT</span></td>
    <td width="20%" class="banner-deco" style="text-align:left;">&#9830;&#8592;&#8592;</td>
</tr>
</table>

<!-- STUDENT NAME -->
<div class="student-name">{{ $student_name }}</div>

<!-- BODY TEXT -->
<div class="body-text">
    having satisfied the requirements for the<br>
    award of the certificate of
</div>

<!-- COURSE TITLE -->
<div class="course-title">{{ $course_title }}</div>

<!-- CLASSIFICATION -->
<div class="classification">{{ $classification }}</div>
<div class="class-divider"></div>
<div class="class-diamond">&#9670;</div>

<!-- GRADUATION DATE -->
<div class="grad-section">
    was admitted to the certificate at a Graduation Ceremony held on the
    <span class="g-day">&nbsp;{{ $graduation_day }}<sup>{{ $graduation_suffix }}</sup>&nbsp;</span>
    day of
    <span class="g-month">&nbsp;{{ $graduation_month }}&nbsp;</span>
    in the year
    <span class="g-year">&nbsp;{{ $graduation_year }}&nbsp;</span>
</div>

<!-- SIGNATURES + SEAL -->
<table class="sig-table">
<tr>
    <td width="28%">
        <div class="sig-item">
            <div class="sig-line"></div>
            <div class="sig-label">Principal</div>
        </div>
        <div class="sig-item">
            <div class="sig-line"></div>
            <div class="sig-label">Graduate's Signature</div>
        </div>
    </td>
    <td width="44%" style="text-align:center;vertical-align:middle;">
        <img src="{{ public_path('assets/images/certificate-seal.png') }}" class="seal-img" alt="">
    </td>
    <td width="28%">
        <div class="sig-item">
            <div class="sig-line"></div>
            <div class="sig-label">Director</div>
        </div>
        <div class="sig-item">
            <div class="sig-line"></div>
            <div class="sig-label">Graduate's I.D. No.</div>
        </div>
    </td>
</tr>
</table>

<!-- INFO BAR -->
<div class="info-bar">
<table class="info-table">
<tr>
    <td width="46%">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="11mm"><div class="info-icon">SN</div></td>
                <td style="padding-left:2mm;">
                    <div class="info-label">Student Number</div>
                    <div class="info-value">{{ $student_number }}</div>
                </td>
            </tr>
            <tr><td colspan="2" style="height:2mm;"></td></tr>
            <tr>
                <td width="11mm"><div class="info-icon">CN</div></td>
                <td style="padding-left:2mm;">
                    <div class="info-label">Certificate Number</div>
                    <div class="info-value">{{ $certificate_number }}</div>
                </td>
            </tr>
        </table>
    </td>
    <td width="8%" class="info-sep">
        <div class="sep-v"></div>
        <div class="sep-diamond">&#9670;</div>
        <div class="sep-v"></div>
    </td>
    <td width="46%">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="11mm"><div class="info-icon">DG</div></td>
                <td style="padding-left:2mm;">
                    <div class="info-label">Date of Graduation</div>
                    <div class="info-value">{{ $graduation_day }}{{ $graduation_suffix }} {{ $graduation_month }} {{ $graduation_year }}</div>
                </td>
            </tr>
            <tr><td colspan="2" style="height:2mm;"></td></tr>
            <tr>
                <td width="11mm"><div class="info-icon gold">CR</div></td>
                <td style="padding-left:2mm;">
                    <div class="info-label">Course</div>
                    <div class="info-value">{{ $course_title }}</div>
                </td>
            </tr>
        </table>
    </td>
</tr>
</table>
</div>

<!-- FOOTER -->
<div class="footer">
    <div class="footer-line">
        <span class="footer-diamond">&#9670;</span>
    </div>
    <div class="verify-text">
        Verification Code: <strong>{{ $verification_code }}</strong> | Verify at {{ config('app.url') }}/certificates/verify
    </div>
</div>

</div>
</div>
</div>

</body>
</html>
