<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 10mm; size: 210mm 297mm; }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'DejaVu Sans', sans-serif;
    background: #fff;
    color: #1F2937;
    font-size: 11pt;
    line-height: 1.5;
  }

  /* Outer frame */
  .frame-outer {
    width: 100%;
    border: 4pt solid #F26522;
    border-collapse: collapse;
  }
  .frame-outer td {
    padding: 5pt;
    vertical-align: top;
  }

  /* Inner frame */
  .frame-inner {
    width: 100%;
    border: 2pt solid #1E4A8A;
    border-collapse: collapse;
  }
  .frame-inner td {
    padding: 18pt 22pt 14pt 22pt;
    vertical-align: top;
  }

  /* Corner images table */
  .corners-table {
    width: 100%;
    border-collapse: collapse;
  }
  .corners-table td {
    padding: 0;
    vertical-align: top;
  }

  /* Header */
  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 4pt;
  }
  .header-table td {
    vertical-align: middle;
    padding: 0;
  }
  .header-logo-left { width: 18%; text-align: left; }
  .header-logo-left img { height: 46pt; }
  .header-logo-right { width: 18%; text-align: right; }
  .header-logo-right img { height: 38pt; }
  .header-title { width: 64%; text-align: center; }
  .college-name {
    font-size: 17pt;
    font-weight: bold;
    color: #1F2937;
    letter-spacing: 0.5pt;
    text-transform: uppercase;
    line-height: 1.25;
  }
  .tagline {
    font-size: 9pt;
    color: #6B7280;
    font-style: italic;
    margin-top: 3pt;
  }

  /* Decorative divider with diamond */
  .divider-table {
    width: 100%;
    border-collapse: collapse;
    margin: 8pt 0 10pt 0;
  }
  .divider-table td {
    vertical-align: middle;
    padding: 0;
  }
  .divider-line-left { width: 28%; border-top: 0.5pt solid #1E4A8A; }
  .divider-line-right { width: 28%; border-top: 0.5pt solid #1E4A8A; }
  .divider-center {
    width: 44%;
    text-align: center;
    font-size: 8pt;
    color: #F26522;
    letter-spacing: 2pt;
  }

  /* Certify text */
  .certify-table {
    width: 100%;
    border-collapse: collapse;
    margin: 12pt 0 10pt 0;
  }
  .certify-table td {
    vertical-align: middle;
    padding: 0;
  }
  .certify-line-side { width: 10%; border-top: 0.5pt solid #F26522; }
  .certify-text {
    width: 80%;
    text-align: center;
    font-size: 13pt;
    font-weight: bold;
    color: #1E4A8A;
    letter-spacing: 1.5pt;
    text-transform: uppercase;
  }

  /* Student name */
  .student-section {
    text-align: center;
    margin: 14pt 0 8pt 0;
  }
  .student-name {
    font-family: 'DejaVu Serif', serif;
    font-size: 28pt;
    font-weight: bold;
    font-style: italic;
    color: #1F2937;
    letter-spacing: 0.5pt;
  }
  .student-underline {
    width: 55%;
    margin: 6pt auto 0 auto;
    border-top: 1pt solid #F26522;
  }

  /* Award text */
  .award-text {
    text-align: center;
    font-size: 11pt;
    color: #4B5563;
    line-height: 1.7;
    margin: 10pt 0;
  }

  /* Course */
  .course-section {
    text-align: center;
    margin: 12pt 0 6pt 0;
  }
  .course-name {
    font-size: 18pt;
    font-weight: bold;
    color: #1E4A8A;
    letter-spacing: 0.5pt;
    text-transform: uppercase;
    line-height: 1.3;
  }
  .merit-text {
    font-family: 'DejaVu Serif', serif;
    font-size: 16pt;
    font-weight: bold;
    font-style: italic;
    color: #1F2937;
    margin-top: 5pt;
  }
  .merit-line {
    width: 35%;
    margin: 6pt auto 0 auto;
    border-top: 0.5pt solid #F26522;
  }

  /* Ceremony */
  .ceremony-section {
    text-align: center;
    margin: 12pt 0 14pt 0;
  }
  .ceremony-line {
    width: 40%;
    margin: 0 auto 10pt auto;
    border-top: 0.5pt solid #F26522;
  }
  .ceremony-text {
    font-size: 11pt;
    color: #4B5563;
    line-height: 1.9;
  }
  .ceremony-text strong {
    color: #1F2937;
    font-weight: bold;
  }
  .ceremony-text em {
    font-family: 'DejaVu Serif', serif;
    font-style: italic;
    color: #1F2937;
  }
  .ceremony-text sup {
    font-size: 7pt;
    vertical-align: super;
    line-height: 0;
  }

  /* Signatures */
  .signatures-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10pt;
  }
  .signatures-table td {
    vertical-align: bottom;
    text-align: center;
    padding: 6pt 4pt;
  }
  .sig-line {
    border-top: 0.5pt solid #374151;
    width: 80%;
    margin: 0 auto 3pt auto;
  }
  .sig-label {
    font-size: 9pt;
    color: #4B5563;
  }
  .seal-box {
    text-align: center;
    vertical-align: middle;
  }
  .seal-box img {
    height: 70pt;
    width: auto;
  }

  /* Graduate row */
  .graduate-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14pt;
  }
  .graduate-table td {
    width: 50%;
    vertical-align: bottom;
    text-align: center;
    padding: 6pt 4pt;
  }

  /* Info box */
  .info-box {
    border: 1pt solid #1E4A8A;
    background-color: #F8FAFC;
    padding: 10pt 14pt;
    margin: 10pt 0 8pt 0;
  }
  .info-table {
    width: 100%;
    border-collapse: collapse;
  }
  .info-table td {
    padding: 4pt 6pt;
    vertical-align: top;
  }
  .info-divider {
    width: 2%;
    text-align: center;
    color: #1E4A8A;
    font-size: 14pt;
    vertical-align: middle;
  }
  .info-label {
    font-size: 7pt;
    font-weight: bold;
    color: #1E4A8A;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
  }
  .info-value {
    font-size: 10pt;
    font-weight: bold;
    color: #1F2937;
  }

  /* Footer */
  .footer {
    text-align: center;
    margin-top: 8pt;
  }
  .footer-text {
    font-size: 6pt;
    color: #9CA3AF;
    font-style: italic;
  }
  .footer-teveta {
    font-size: 6pt;
    color: #9CA3AF;
  }

  /* Bottom corners table */
  .bottom-corners-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 4pt;
  }
  .bottom-corners-table td {
    padding: 0;
    vertical-align: bottom;
  }
</style>
</head>
<body>

<table class="frame-outer" cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <table class="frame-inner" cellpadding="0" cellspacing="0">
        <tr>
          <td>

            <!-- Top Corners -->
            <table class="corners-table" cellpadding="0" cellspacing="0">
              <tr>
                <td style="width:28pt; text-align:left;">
                  <img src="{{corner_tl}}" style="width:26pt; height:26pt; display:block;">
                </td>
                <td></td>
                <td style="width:28pt; text-align:right;">
                  <img src="{{corner_tr}}" style="width:26pt; height:26pt; display:block; margin-left:auto;">
                </td>
              </tr>
            </table>

            <!-- Header -->
            <table class="header-table" cellpadding="0" cellspacing="0">
              <tr>
                <td class="header-logo-left">
                  <img src="{{logo_path}}" alt="Edutrack">
                </td>
                <td class="header-title">
                  <div class="college-name">Edutrack Computer<br>Training College</div>
                  <div class="tagline">A skill training college</div>
                </td>
                <td class="header-logo-right">
                  <img src="{{teveta_logo_path}}" alt="TEVETA">
                </td>
              </tr>
            </table>

            <!-- Divider -->
            <table class="divider-table" cellpadding="0" cellspacing="0">
              <tr>
                <td class="divider-line-left"></td>
                <td class="divider-center">&#9670; &#9670; &#9670;</td>
                <td class="divider-line-right"></td>
              </tr>
            </table>

            <!-- Certify -->
            <table class="certify-table" cellpadding="0" cellspacing="0">
              <tr>
                <td class="certify-line-side"></td>
                <td class="certify-text">This is to certify that</td>
                <td class="certify-line-side"></td>
              </tr>
            </table>

            <!-- Student name -->
            <div class="student-section">
              <div class="student-name">{{student_name}}</div>
              <div class="student-underline"></div>
            </div>

            <!-- Award -->
            <div class="award-text">
              having satisfied the requirements for the<br>
              award of the certificate of
            </div>

            <!-- Course -->
            <div class="course-section">
              <div class="course-name">{{course_title}}</div>
              <div class="merit-text">{{merit_text}}</div>
              <div class="merit-line"></div>
            </div>

            <!-- Ceremony -->
            <div class="ceremony-section">
              <div class="ceremony-line"></div>
              <div class="ceremony-text">
                was admitted to the certificate at a Graduation Ceremony<br>
                held on the {{formal_date_html}}
              </div>
            </div>

            <!-- Signatures -->
            <table class="signatures-table" cellpadding="0" cellspacing="0">
              <tr>
                <td style="width:30%;">
                  <div class="sig-line"></div>
                  <div class="sig-label">Principal</div>
                </td>
                <td style="width:40%;" class="seal-box">
                  <img src="{{seal_path}}" alt="Official Seal">
                </td>
                <td style="width:30%;">
                  <div class="sig-line"></div>
                  <div class="sig-label">Director</div>
                </td>
              </tr>
            </table>

            <table class="graduate-table" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <div class="sig-line"></div>
                  <div class="sig-label">Graduate's Signature</div>
                </td>
                <td>
                  <div class="sig-line"></div>
                  <div class="sig-label">Graduate's I.D. No.</div>
                </td>
              </tr>
            </table>

            <!-- Info box -->
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
                  <td style="padding-top:6pt;">
                    <div class="info-label">Certificate Number</div>
                    <div class="info-value">{{certificate_number}}</div>
                  </td>
                  <td class="info-divider">|</td>
                  <td style="padding-top:6pt;">
                    <div class="info-label">Course</div>
                    <div class="info-value">{{course_title}}</div>
                  </td>
                </tr>
              </table>
            </div>

            <!-- Bottom Corners -->
            <table class="bottom-corners-table" cellpadding="0" cellspacing="0">
              <tr>
                <td style="width:28pt; text-align:left;">
                  <img src="{{corner_bl}}" style="width:26pt; height:26pt; display:block;">
                </td>
                <td></td>
                <td style="width:28pt; text-align:right;">
                  <img src="{{corner_br}}" style="width:26pt; height:26pt; display:block; margin-left:auto;">
                </td>
              </tr>
            </table>

            <!-- Footer -->
            <div class="footer">
              <div class="footer-text">Verify authenticity at {{verify_url}}</div>
              <div class="footer-teveta">Issued under the authority of the Technical Education, Vocational and Entrepreneurship Training Authority (TEVETA) of Zambia.</div>
            </div>

          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

</body>
</html>
