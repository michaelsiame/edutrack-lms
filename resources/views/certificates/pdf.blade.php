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

/* Outer page frame: explicit A4 size, backgrounds act as borders */
.page-frame {
    width: 210mm;
    height: 297mm;
    border-collapse: collapse;
    background: #f26522;
}
.page-frame > tbody > tr > td {
    padding: 1.5mm;
    vertical-align: top;
}
.blue-frame {
    width: 100%;
    height: 100%;
    border-collapse: collapse;
    background: #1e3a8a;
}
.blue-frame > tbody > tr > td {
    padding: 1.5mm;
    vertical-align: top;
}
.white-frame {
    width: 100%;
    height: 100%;
    border-collapse: collapse;
    background: #ffffff;
}
.white-frame > tbody > tr > td {
    padding: 12mm 14mm 8mm 14mm;
    vertical-align: top;
}

/* Content distributor pushes info box + footer to bottom */
.content-distributor {
    width: 100%;
    height: 100%;
    border-collapse: collapse;
}
.content-distributor td {
    vertical-align: top;
}
.content-distributor .spacer {
    height: 100%;
    line-height: 0;
    font-size: 0;
}


/* Header */
.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12mm;
}
.header-table td {
    vertical-align: middle;
    padding: 0;
}
.college-title {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 19pt;
    font-weight: 900;
    color: #1a1a1a;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    line-height: 1.25;
    text-align: center;
}
.college-subtitle {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 8.5pt;
    color: #444;
    font-style: italic;
    text-align: center;
    margin-top: 1mm;
}


/* Banner */
.banner-table {
    width: 100%;
    border-collapse: collapse;
    margin: 6mm 0;
}
.banner-table td {
    text-align: center;
    padding: 2mm 0;
}
.banner-text {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 17pt;
    font-weight: 900;
    color: #1e3a8a;
    letter-spacing: 2pt;
    text-transform: uppercase;
}
.banner-deco {
    color: #f26522;
    font-size: 8pt;
}

/* Student name */
.student-name {
    font-family: 'greatvibes', 'DejaVu Serif', serif;
    font-size: 38pt;
    color: #1a1a1a;
    text-align: center;
    line-height: 1.2;
    margin: 5mm 0 3mm 0;
}
.name-underline {
    width: 130mm;
    margin: 0 auto 3mm auto;
    border-top: 0.75pt solid #f26522;
    height: 0;
}

/* Body text */
.body-text {
    text-align: center;
    font-size: 9.5pt;
    color: #333;
    line-height: 1.5;
    margin: 2mm 0;
}

/* Course title - DOMINANT */
.course-title {
    text-align: center;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 22pt;
    font-weight: 900;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 1pt;
    line-height: 1.2;
    margin: 6mm 0;
}

/* Classification - supporting element */
.classification {
    font-family: 'greatvibes', 'DejaVu Serif', serif;
    font-size: 16pt;
    color: #1a1a1a;
    text-align: center;
    line-height: 1.2;
    margin: 2mm 0 1mm 0;
}
.merit-underline {
    width: 50mm;
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

/* Date - restructured for visual impact */
.date-section {
    text-align: center;
    font-size: 9.5pt;
    color: #333;
    line-height: 1.5;
    margin: 8mm 0;
}
.date-script {
    font-family: 'greatvibes', 'DejaVu Serif', serif;
    font-size: 16pt;
    color: #1a1a1a;
}
.date-year {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 20pt;
    font-weight: 700;
    color: #1a1a1a;
    display: block;
    margin-top: 2mm;
}
sup { font-size: 6pt; }

/* Signatures */
.sig-table {
    width: 100%;
    border-collapse: collapse;
    margin: 12mm 0;
}
.sig-table td {
    vertical-align: bottom;
    padding: 0;
}
.sig-item {
    text-align: center;
    margin-bottom: 6mm;
}
.sig-line {
    border-top: 0.8pt solid #333;
    width: 65%;
    margin: 0 auto 1mm auto;
    height: 0;
}
.sig-label {
    font-size: 8pt;
    color: #333;
    font-weight: 600;
}
.seal-img {
    width: 38mm;
    height: auto;
    display: block;
    margin: 0 auto;
}

/* Info box */
.info-box {
    border: 1.5pt solid #f26522;
    border-radius: 4pt;
    padding: 3mm 4mm;
    margin: 0 auto;
    width: 95%;
}
.info-table {
    width: 100%;
    border-collapse: collapse;
}
.info-table td {
    vertical-align: top;
}
.info-icon-img {
    width: 8mm;
    height: auto;
    display: block;
}
.info-table .info-table td {
    vertical-align: middle;
}
.info-label {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6pt;
    color: #1e3a8a;
    font-weight: 800;
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


/* Footer */
.footer-text {
    text-align: center;
    font-size: 6.5pt;
    color: #666;
    margin-top: 4mm;
}
</style>
</head>
<body>

<table class="page-frame">
<tr>
<td>

<table class="blue-frame">
<tr>
<td>

<table class="white-frame">
<tr>
<td>

<table class="content-distributor">
<tr>
<td>

    <!-- HEADER -->
    <table class="header-table">
    <tr>
        <td width="20%" style="text-align:center;">
            <img src="{{ public_path('assets/images/logo-pdf.png') }}" style="height:20mm;width:auto;display:block;margin:0 auto;" alt="">
        </td>
        <td width="60%" style="text-align:center;">
            <div class="college-title">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
            <div class="college-subtitle">TEVETA Registered Institution &mdash; TVA/2064</div>
        </td>
        <td width="20%" style="text-align:right;">
            <img src="{{ public_path('assets/images/teveta-logo.png') }}" style="height:20mm;width:auto;display:inline-block;" alt="">
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
        has successfully completed the requirements<br>
        for the award of this certificate in
    </div>

    <!-- COURSE TITLE -->
    <div class="course-title">{{ $course_title }}</div>

    <!-- CLASSIFICATION -->
    <div class="classification">{{ $classification }}</div>
    <div class="merit-underline"></div>
    <div class="merit-diamond"><span>&#9670;</span></div>

    <!-- DATE -->
    <div class="date-section">
        was admitted to this certificate at a Graduation Ceremony held on the<br><br>
        <span class="date-script">{{ $graduation_day }}<sup>{{ $graduation_suffix }}</sup></span>
        &nbsp;&nbsp;day of&nbsp;&nbsp;
        <span class="date-script">{{ $graduation_month }}</span><br>
        <span class="date-year">{{ $graduation_year }}</span>
    </div>

    <!-- SIGNATURES + SEAL -->
    <table class="sig-table">
    <tr>
        <td width="30%">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="text-align:center;padding-bottom:6mm;">
                        <div class="sig-line"></div>
                        <div class="sig-label">Principal</div>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;padding-bottom:6mm;">
                        <div class="sig-line"></div>
                        <div class="sig-label">Graduate's Signature</div>
                    </td>
                </tr>
            </table>
        </td>
        <td width="40%" style="text-align:center;vertical-align:bottom;padding-top:4mm;">
            <img src="{{ public_path('assets/images/certificate-seal.png') }}" class="seal-img" alt="">
        </td>
        <td width="30%">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="text-align:center;padding-bottom:6mm;">
                        <div class="sig-line"></div>
                        <div class="sig-label">Director</div>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;padding-bottom:6mm;">
                        <div style="border-bottom:0.8pt solid #333;width:85%;margin:0 auto 1mm auto;padding-bottom:1mm;font-size:8pt;font-weight:700;color:#1a1a1a;min-height:3.5mm;">
                            {{ $nrc_number ?? '' }}
                        </div>
                        <div class="sig-label">Graduate's I.D. No.</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    </table>

</td>
</tr>
<tr>
<td class="spacer"></td>
</tr>
<tr>
<td style="vertical-align:bottom;">

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


    <!-- FOOTER -->
    <div class="footer-text">
        Verification Code: <strong>{{ $verification_code }}</strong> | Verify at {{ config('app.url') }}/certificates/verify
    </div>

</td>
</tr>
</table>

</td>
</tr>
</table>

</td>
</tr>
</table>

</td>
</tr>
</table>

</body>
</html>
