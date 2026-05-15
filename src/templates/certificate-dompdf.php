<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 0; size: 210mm 297mm; }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'DejaVu Sans', sans-serif;
    background: #fff;
    width: 210mm;
    height: 297mm;
    position: relative;
    color: #1F2937;
  }

  /* Outer orange border */
  .outer-border {
    position: absolute;
    top: 6mm; left: 6mm; right: 6mm; bottom: 6mm;
    border: 2mm solid #F26522;
  }

  /* Inner blue border */
  .inner-border {
    position: absolute;
    top: 10mm; left: 10mm; right: 10mm; bottom: 10mm;
    border: 1mm solid #1E4A8A;
  }

  /* Corner triangles using border hack (works in dompdf) */
  .corner {
    position: absolute;
    width: 0; height: 0;
    border-style: solid;
  }
  .corner-tl {
    top: 6mm; left: 6mm;
    border-width: 12mm 12mm 0 0;
    border-color: #F26522 transparent transparent transparent;
  }
  .corner-tr {
    top: 6mm; right: 6mm;
    border-width: 0 12mm 12mm 0;
    border-color: transparent #F26522 transparent transparent;
  }
  .corner-bl {
    bottom: 6mm; left: 6mm;
    border-width: 12mm 0 0 12mm;
    border-color: transparent transparent transparent #F26522;
  }
  .corner-br {
    bottom: 6mm; right: 6mm;
    border-width: 0 0 12mm 12mm;
    border-color: transparent transparent #F26522 transparent;
  }

  /* Main content area */
  .content {
    position: absolute;
    top: 16mm; left: 16mm; right: 16mm; bottom: 16mm;
    padding: 8mm 12mm;
  }

  /* Header */
  .header-table {
    width: 100%;
    margin-bottom: 4mm;
  }
  .header-table td {
    vertical-align: middle;
  }
  .header-logo { width: 20%; text-align: left; }
  .header-logo img { height: 18mm; }
  .header-title { width: 60%; text-align: center; }
  .header-teveta { width: 20%; text-align: right; }
  .header-teveta img { height: 14mm; }

  .college-name {
    font-size: 16pt;
    font-weight: bold;
    color: #1F2937;
    letter-spacing: 0.5pt;
    text-transform: uppercase;
    line-height: 1.2;
  }

  .tagline {
    font-size: 8pt;
    color: #6B7280;
    font-style: italic;
    text-align: center;
    margin-bottom: 6mm;
  }

  /* Decorative rule */
  .rule-table {
    width: 100%;
    margin-bottom: 4mm;
  }
  .rule-table td {
    vertical-align: middle;
  }
  .rule-line {
    border-top: 0.5pt solid #1E4A8A;
  }
  .rule-diamond {
    width: 20%;
    text-align: center;
    font-size: 8pt;
    color: #F26522;
  }

  /* Certification statement */
  .certify-table {
    width: 100%;
    margin-bottom: 4mm;
  }
  .certify-table td {
    vertical-align: middle;
  }
  .certify-line {
    border-top: 0.5pt solid #F26522;
  }
  .certify-text {
    text-align: center;
    font-size: 11pt;
    font-weight: bold;
    color: #1E4A8A;
    letter-spacing: 1.5pt;
    text-transform: uppercase;
  }

  /* Student name */
  .student-name {
    text-align: center;
    font-family: serif;
    font-size: 24pt;
    font-weight: bold;
    font-style: italic;
    color: #1F2937;
    margin-bottom: 2mm;
  }

  .name-underline {
    width: 50%;
    margin: 0 auto 4mm auto;
    border-top: 0.5pt solid #F26522;
  }

  /* Award text */
  .award-text {
    text-align: center;
    font-size: 9pt;
    color: #4B5563;
    line-height: 1.5;
    margin-bottom: 4mm;
  }

  /* Course name */
  .course-name {
    text-align: center;
    font-size: 15pt;
    font-weight: bold;
    color: #1E4A8A;
    letter-spacing: 0.5pt;
    text-transform: uppercase;
    margin-bottom: 1mm;
  }

  .merit-text {
    text-align: center;
    font-family: serif;
    font-size: 13pt;
    font-weight: bold;
    font-style: italic;
    color: #1F2937;
    margin-bottom: 4mm;
  }

  /* Decorative line */
  .center-rule {
    width: 40%;
    margin: 0 auto 4mm auto;
    border-top: 0.5pt solid #F26522;
  }

  /* Ceremony text */
  .ceremony-text {
    text-align: center;
    font-size: 9pt;
    color: #4B5563;
    line-height: 1.6;
    margin-bottom: 8mm;
  }

  /* Signatures */
  .sig-table {
    width: 100%;
    margin-bottom: 3mm;
  }
  .sig-table td {
    vertical-align: bottom;
    text-align: center;
  }
  .sig-line {
    border-top: 0.5pt solid #374151;
    width: 40mm;
    margin: 0 auto;
    padding-top: 1mm;
  }
  .sig-label {
    font-size: 7pt;
    color: #4B5563;
  }

  .seal-box {
    text-align: center;
  }
  .seal-inner {
    display: inline-block;
    border: 1.5pt solid #1E4A8A;
    background-color: #1E4A8A;
    padding: 4mm 5mm;
  }
  .seal-text {
    font-size: 10pt;
    font-weight: bold;
    color: #C9A227;
  }
  .seal-label {
    font-size: 5pt;
    color: #1E4A8A;
    font-weight: bold;
    margin-top: 0.5mm;
  }

  /* Bottom info box */
  .info-box {
    width: 100%;
    border: 1pt solid #1E4A8A;
    background-color: #F8FAFC;
    padding: 4mm 5mm;
    margin-bottom: 3mm;
  }
  .info-table {
    width: 100%;
  }
  .info-table td {
    vertical-align: top;
  }
  .info-divider {
    width: 2%;
    text-align: center;
    color: #1E4A8A;
    font-size: 12pt;
  }
  .info-label {
    font-size: 6pt;
    font-weight: bold;
    color: #1E4A8A;
    text-transform: uppercase;
  }
  .info-value {
    font-size: 8pt;
    font-weight: bold;
    color: #1F2937;
  }

  /* Footer */
  .footer-text {
    text-align: center;
    font-size: 5pt;
    color: #9CA3AF;
    font-style: italic;
  }
  .footer-teveta {
    text-align: center;
    font-size: 5pt;
    color: #9CA3AF;
  }
</style>
</head>
<body>

<div class="outer-border"></div>
<div class="inner-border"></div>
<div class="corner corner-tl"></div>
<div class="corner corner-tr"></div>
<div class="corner corner-bl"></div>
<div class="corner corner-br"></div>

<div class="content">

  <!-- Header -->
  <table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
      <td class="header-logo">
        <img src="{{logo_path}}" alt="Edutrack">
      </td>
      <td class="header-title">
        <div class="college-name">Edutrack Computer<br>Training College</div>
      </td>
      <td class="header-teveta">
        <img src="{{teveta_logo_path}}" alt="TEVETA">
      </td>
    </tr>
  </table>

  <!-- Tagline -->
  <div class="tagline">A skill training college</div>

  <!-- Decorative rule -->
  <table class="rule-table" cellpadding="0" cellspacing="0">
    <tr>
      <td class="rule-line" style="width:25%;"></td>
      <td class="rule-diamond">* * *</td>
      <td class="rule-line" style="width:25%;"></td>
    </tr>
  </table>

  <!-- Certification statement -->
  <table class="certify-table" cellpadding="0" cellspacing="0">
    <tr>
      <td class="certify-line" style="width:10%;"></td>
      <td class="certify-text" style="width:80%;">This is to certify that</td>
      <td class="certify-line" style="width:10%;"></td>
    </tr>
  </table>

  <!-- Student name -->
  <div class="student-name">{{student_name}}</div>
  <div class="name-underline"></div>

  <!-- Award text -->
  <div class="award-text">
    having satisfied the requirements for the<br>
    award of the certificate of
  </div>

  <!-- Course name -->
  <div class="course-name">{{course_title}}</div>
  <div class="merit-text">{{merit_text}}</div>

  <!-- Decorative line -->
  <div class="center-rule"></div>

  <!-- Ceremony text -->
  <div class="ceremony-text">
    was admitted to the certificate at a Graduation Ceremony<br>
    held on the <strong>{{formal_date}}</strong>
  </div>

  <!-- Signatures row -->
  <table class="sig-table" cellpadding="0" cellspacing="0">
    <tr>
      <td style="width:33%;">
        <div class="sig-line"></div>
        <div class="sig-label">Principal</div>
      </td>
      <td style="width:34%; vertical-align:middle;">
        <div class="seal-box">
          <div class="seal-inner">
            <span class="seal-text">SEAL</span>
          </div>
          <div class="seal-label">SEAL</div>
        </div>
      </td>
      <td style="width:33%;">
        <div class="sig-line"></div>
        <div class="sig-label">Director</div>
      </td>
    </tr>
  </table>

  <!-- Graduate signature & ID row -->
  <table class="sig-table" cellpadding="0" cellspacing="0">
    <tr>
      <td style="width:50%;">
        <div class="sig-line"></div>
        <div class="sig-label">Graduate's Signature</div>
      </td>
      <td style="width:50%;">
        <div class="sig-line"></div>
        <div class="sig-label">Graduate's I.D. No.</div>
      </td>
    </tr>
  </table>

  <!-- Bottom info box -->
  <div class="info-box">
    <table class="info-table" cellpadding="0" cellspacing="0">
      <tr>
        <td style="width:46%;">
          <div class="info-label">Student Number</div>
          <div class="info-value">{{student_number}}</div>
        </td>
        <td class="info-divider">|</td>
        <td style="width:46%;">
          <div class="info-label">Date of Graduation</div>
          <div class="info-value">{{completion_date}}</div>
        </td>
      </tr>
      <tr>
        <td style="padding-top:3mm;">
          <div class="info-label">Certificate Number</div>
          <div class="info-value">{{certificate_number}}</div>
        </td>
        <td class="info-divider">|</td>
        <td style="padding-top:3mm;">
          <div class="info-label">Course</div>
          <div class="info-value">{{course_title}}</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Footer -->
  <div class="footer-text">Verify authenticity at {{verify_url}}</div>
  <div class="footer-teveta">Issued under the authority of the Technical Education, Vocational and Entrepreneurship Training Authority (TEVETA) of Zambia.</div>

</div>
</body>
</html>
