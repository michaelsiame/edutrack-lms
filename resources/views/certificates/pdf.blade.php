<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page {
    size: 210mm 297mm;
    margin: 0;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Sans', 'DejaVu Serif', Arial, sans-serif;
    font-size: 9pt;
    color: #1a1a1a;
}

/* Frame using nested divs with padding (mPDF-friendly) */
.frame-orange {
    width: 210mm;
    height: 297mm;
    background: #f26522;
    padding: 2mm;
}
.frame-blue {
    width: 100%;
    height: 100%;
    background: #1e3a8a;
    padding: 2mm;
}
.frame-white {
    width: 100%;
    height: 100%;
    background: #ffffff;
    padding: 10mm 14mm 8mm 14mm;
}

/* Header */
.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 3mm;
}
.header-table td {
    vertical-align: middle;
    padding: 0;
}
.college-title {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 16pt;
    font-weight: 800;
    color: #1a1a1a;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    line-height: 1.2;
    text-align: center;
}
.college-subtitle {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 9pt;
    color: #444;
    font-style: italic;
    text-align: center;
    margin-top: 1mm;
}

/* Divider */
.divider-wrap {
    text-align: center;
    margin: 1.5mm 0;
}
.divider-table {
    width: 70%;
    margin: 0 auto;
    border-collapse: collapse;
}
.divider-table td {
    border-top: 0.5pt solid #1e3a8a;
    height: 0;
}
.divider-diamond {
    color: #f26522;
    font-size: 7pt;
    text-align: center;
    width: 6mm;
}

/* Banner */
.banner-table {
    width: 100%;
    border-collapse: collapse;
    margin: 3mm 0;
}
.banner-table td {
    text-align: center;
    padding: 1.5mm 0;
}
.banner-text {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 12pt;
    font-weight: 700;
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
    font-family: 'greatvibes', 'DejaVu Serif', serif;
    font-size: 30pt;
    color: #1a1a1a;
    text-align: center;
    line-height: 1.2;
    margin: 2mm 0 1mm 0;
}
.name-underline {
    width: 80mm;
    margin: 0 auto 2mm auto;
    border-top: 0.75pt solid #f26522;
    height: 0;
}

/* Body text */
.body-text {
    text-align: center;
    font-size: 9.5pt;
    color: #333;
    line-height: 1.6;
    margin: 2mm 0;
}

/* Course title */
.course-title {
    text-align: center;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 18pt;
    font-weight: 800;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 1pt;
    line-height: 1.2;
    margin: 2mm 0;
}

/* Classification */
.classification {
    font-family: 'greatvibes', 'DejaVu Serif', serif;
    font-size: 20pt;
    color: #1a1a1a;
    text-align: center;
    line-height: 1.2;
    margin: 2mm 0 1mm 0;
}
.merit-underline {
    width: 45mm;
    margin: 0 auto;
    border-top: 0.75pt solid #f26522;
    height: 0;
}
.merit-diamond {
    text-align: center;
    color: #f26522;
    font-size: 6pt;
    margin-top: -1.5mm;
}
.merit-diamond span {
    background: #fff;
    padding: 0 2mm;
}

/* Date */
.date-section {
    text-align: center;
    font-size: 9.5pt;
    color: #333;
    line-height: 1.6;
    margin: 3mm 0;
}
.date-script {
    font-family: 'greatvibes', 'DejaVu Serif', serif;
    font-size: 15pt;
    color: #1a1a1a;
}
.date-year {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 15pt;
    font-weight: 700;
    color: #1a1a1a;
}
sup { font-size: 6pt; }

/* Signatures */
.sig-table {
    width: 100%;
    border-collapse: collapse;
    margin: 4mm 0;
}
.sig-table td {
    vertical-align: bottom;
    padding: 0;
}
.sig-item {
    text-align: center;
    margin-bottom: 5mm;
}
.sig-line {
    border-top: 0.5pt solid #333;
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
    width: 24mm;
    height: auto;
    display: block;
    margin: 0 auto;
}

/* Info box */
.info-box {
    border: 1.5pt solid #f26522;
    border-radius: 4pt;
    padding: 3mm 4mm;
    margin: 3mm auto 0 auto;
    width: 95%;
}
.info-table {
    width: 100%;
    border-collapse: collapse;
}
.info-table td {
    vertical-align: middle;
}
.info-icon-img {
    width: 7mm;
    height: 7mm;
    display: block;
}
.info-label {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6pt;
    color: #1e3a8a;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3pt;
    margin-bottom: 0.5mm;
}
.info-value {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9pt;
    color: #1a1a1a;
    font-weight: 700;
}
.info-sep {
    width: 6mm;
    text-align: center;
    vertical-align: middle;
}
.sep-v {
    border-right: 0.5pt solid #f26522;
    height: 8mm;
    width: 0;
    margin: 0 auto;
    opacity: 0.5;
}
.sep-diamond {
    color: #f26522;
    font-size: 8pt;
    line-height: 1;
    margin: 1mm 0;
}

/* Bottom decor */
.bottom-decor {
    text-align: center;
    margin-top: 3mm;
}
.bottom-table {
    width: 30%;
    margin: 0 auto;
    border-collapse: collapse;
}
.bottom-table td {
    border-top: 0.5pt solid #1e3a8a;
    height: 0;
}
.bottom-diamond {
    color: #f26522;
    font-size: 7pt;
    text-align: center;
    width: 5mm;
}

/* Footer */
.footer-text {
    text-align: center;
    font-size: 6.5pt;
    color: #666;
    margin-top: 2mm;
}
</style>
</head>
<body>

<div class="frame-orange">
<div class="frame-blue">
<div class="frame-white">

    <!-- HEADER -->
    <table class="header-table">
    <tr>
        <td width="22%" style="text-align:center;">
            <img src="{{ public_path('assets/images/logo.png') }}" style="width:16mm;height:auto;display:block;margin:0 auto;" alt="">
            <div style="font-size:6pt;font-weight:700;color:#1e3a8a;text-align:center;line-height:1.2;">Excel Through Education</div>
        </td>
        <td width="56%">
            <div class="college-title">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
            <div class="divider-wrap">
                <table class="divider-table"><tr><td></td><td class="divider-diamond">&#9670;</td><td></td></tr></table>
            </div>
            <div class="college-subtitle">A skill training college</div>
        </td>
        <td width="22%" style="text-align:right;">
            <img src="{{ public_path('assets/images/teveta-logo.png') }}" style="width:20mm;height:auto;display:inline-block;" alt="">
        </td>
    </tr>
    </table>

    <!-- CERTIFY BANNER -->
    <table class="banner-table">
    <tr>
        <td width="15%" class="banner-deco" style="text-align:right;">&#8594;&#8594;&#9830;</td>
        <td width="70%"><span class="banner-text">THIS IS TO CERTIFY THAT</span></td>
        <td width="15%" class="banner-deco" style="text-align:left;">&#9830;&#8592;&#8592;</td>
    </tr>
    </table>

    <!-- STUDENT NAME -->
    <div class="student-name">{{ $student_name }}</div>
    <div class="name-underline"></div>

    <!-- BODY TEXT -->
    <div class="body-text">
        having satisfied the requirements for the<br>
        award of the certificate of
    </div>

    <!-- COURSE TITLE -->
    <div class="course-title">{{ $course_title }}</div>

    <!-- CLASSIFICATION -->
    <div class="classification">{{ $classification }}</div>
    <div class="merit-underline"></div>
    <div class="merit-diamond"><span>&#9670;</span></div>

    <!-- DATE -->
    <div class="date-section">
        was admitted to the certificate at a Graduation Ceremony held on the&nbsp;
        <span class="date-script">{{ $graduation_day }}<sup>{{ $graduation_suffix }}</sup></span>&nbsp;
        day of&nbsp;
        <span class="date-script">{{ $graduation_month }}</span>&nbsp;
        in the year&nbsp;
        <span class="date-year">{{ $graduation_year }}</span>
    </div>

    <!-- SIGNATURES + SEAL -->
    <table class="sig-table">
    <tr>
        <td width="30%">
            <div class="sig-item">
                <div class="sig-line"></div>
                <div class="sig-label">Principal</div>
            </div>
            <div class="sig-item">
                <div class="sig-line"></div>
                <div class="sig-label">Graduate's Signature</div>
            </div>
        </td>
        <td width="40%" style="text-align:center;vertical-align:bottom;">
            <img src="{{ public_path('assets/images/certificate-seal.png') }}" class="seal-img" alt="">
        </td>
        <td width="30%">
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

    <!-- INFO BOX -->
    <div class="info-box">
    <table class="info-table">
    <tr>
        <td width="47%">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="9mm"><img src="{{ public_path('assets/images/cert-icons/icon-student.png') }}" class="info-icon-img" alt=""></td>
                    <td style="padding-left:2mm;">
                        <div class="info-label">Student Number</div>
                        <div class="info-value">{{ $student_number }}</div>
                    </td>
                </tr>
                <tr><td colspan="2" style="height:2mm;"></td></tr>
                <tr>
                    <td width="9mm"><img src="{{ public_path('assets/images/cert-icons/icon-cert.png') }}" class="info-icon-img" alt=""></td>
                    <td style="padding-left:2mm;">
                        <div class="info-label">Certificate Number</div>
                        <div class="info-value">{{ $certificate_number }}</div>
                    </td>
                </tr>
            </table>
        </td>
        <td width="6%" class="info-sep">
            <div class="sep-v"></div>
            <div class="sep-diamond">&#9670;</div>
            <div class="sep-v"></div>
        </td>
        <td width="47%">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="9mm"><img src="{{ public_path('assets/images/cert-icons/icon-date.png') }}" class="info-icon-img" alt=""></td>
                    <td style="padding-left:2mm;">
                        <div class="info-label">Date of Graduation</div>
                        <div class="info-value">{{ $graduation_day }}{{ $graduation_suffix }} {{ $graduation_month }} {{ $graduation_year }}</div>
                    </td>
                </tr>
                <tr><td colspan="2" style="height:2mm;"></td></tr>
                <tr>
                    <td width="9mm"><img src="{{ public_path('assets/images/cert-icons/icon-course.png') }}" class="info-icon-img" alt=""></td>
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

    <!-- BOTTOM DECOR -->
    <div class="bottom-decor">
        <table class="bottom-table">
            <tr><td></td><td class="bottom-diamond">&#9670;</td><td></td></tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer-text">
        Verification Code: <strong>{{ $verification_code }}</strong> | Verify at {{ config('app.url') }}/certificates/verify
    </div>

</div>
</div>
</div>

</body>
</html>
