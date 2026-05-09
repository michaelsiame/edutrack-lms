<!--
  TEVETA Certificate PDF Template for TCPDF
  A4 Landscape (297 x 210 mm) · Table-based · Inline CSS only
  
  Design: Formal Institutional Authority
  - Border-based corner brackets (no custom fonts needed)
  - Navy + Gold palette
  - Serif title for classical authority
  
  Placeholders replaced by Certificate::generatePDF():
  {{logo_path}}            - Edutrack logo (small, ~150px)
  {{teveta_logo_path}}     - TEVETA logo (small, ~200px)
  {{teveta_code}}          - TEVETA institution code
  {{student_name}}         - Student full name (UPPERCASE)
  {{course_title}}         - Course name
  {{completion_date}}      - e.g. "January 15, 2026"
  {{certificate_number}}   - e.g. "EDUTRACK-202605-00001"
  {{verify_url}}           - Public verification URL
  {{director_name}}        - Director name
  {{instructor_name}}      - Instructor name (may be empty)
  {{director_signature}}   - Director signature <img> or empty string
  {{instructor_signature}} - Instructor signature <img> or empty string
  {{qr_code}}              - QR code <img> or empty string
-->

<table cellpadding="0" cellspacing="0" style="width:100%; height:100%; background-color:#FDFCFA;">
  <tr>
    <td style="padding:10px;">

      <!-- ===== OUTER NAVY BORDER ===== -->
      <table cellpadding="0" cellspacing="0" style="width:100%; height:100%; border:3px solid #1E4A8A; background-color:#FFFFFF;">
        <tr>
          <td style="padding:12px 16px 10px 16px; vertical-align:top;">

            <!-- ===== DECORATIVE GOLD FRAME (border-based corners) ===== -->
            <table cellpadding="0" cellspacing="0" style="width:100%; height:100%;">
              <!-- TOP ROW: corner brackets via borders -->
              <tr>
                <td style="width:24px; height:24px; border-top:2px solid #C9A227; border-left:2px solid #C9A227;">&nbsp;</td>
                <td style="border-top:2px solid #C9A227; height:24px;">&nbsp;</td>
                <td style="width:24px; height:24px; border-top:2px solid #C9A227; border-right:2px solid #C9A227;">&nbsp;</td>
              </tr>
              <!-- MIDDLE ROW -->
              <tr>
                <td style="border-left:2px solid #C9A227; width:24px;">&nbsp;</td>
                <td style="padding:8px 14px 0 14px; vertical-align:top;">

                  <!-- ================= HEADER ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <td style="width:15%; text-align:left; vertical-align:middle;">
                        <img src="{{logo_path}}" style="width:44px; height:auto;">
                      </td>
                      <td style="width:70%; text-align:center; vertical-align:middle;">
                        <div style="font-family:helvetica; font-size:10px; font-weight:bold; color:#1E4A8A; letter-spacing:1.5px; text-transform:uppercase; line-height:1.3;">
                          Edutrack Computer Training College
                        </div>
                        <div style="font-family:helvetica; font-size:7px; color:#6B7280; margin-top:2px; letter-spacing:0.5px;">
                          TEVETA Registered Institution — Code {{teveta_code}}
                        </div>
                      </td>
                      <td style="width:15%; text-align:right; vertical-align:middle;">
                        <img src="{{teveta_logo_path}}" style="width:66px; height:auto;">
                      </td>
                    </tr>
                  </table>

                  <!-- Header rule -->
                  <table cellpadding="0" cellspacing="0" style="width:100%; margin-top:6px; margin-bottom:10px;">
                    <tr><td style="border-top:1.5px solid #1E4A8A;">&nbsp;</td></tr>
                  </table>

                  <!-- ================= TITLE ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <td style="text-align:center; padding-bottom:6px;">
                        <span style="font-family:times; font-size:26px; font-weight:bold; color:#1E4A8A; letter-spacing:2px; text-transform:uppercase;">
                          Certificate of Completion
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td style="text-align:center; padding-bottom:12px;">
                        <span style="font-family:helvetica; font-size:9px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                          This is to certify that
                        </span>
                      </td>
                    </tr>
                  </table>

                  <!-- ================= STUDENT NAME ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <td style="text-align:center; padding-bottom:3px;">
                        <span style="font-family:times; font-size:22px; font-weight:bold; color:#1F2937; letter-spacing:0.5px;">
                          {{student_name}}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td style="text-align:center; padding-bottom:14px;">
                        <span style="font-family:helvetica; font-size:9px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                          Has successfully completed the course
                        </span>
                      </td>
                    </tr>
                  </table>

                  <!-- ================= COURSE TITLE ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <td style="text-align:center; padding-bottom:16px;">
                        <span style="font-family:times; font-size:15px; font-weight:bold; color:#1E4A8A; letter-spacing:0.5px;">
                          {{course_title}}
                        </span>
                      </td>
                    </tr>
                  </table>

                  <!-- ================= GOLD DIVIDER ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:14px;">
                    <tr>
                      <td style="width:30%;">&nbsp;</td>
                      <td style="width:40%; border-top:2.5px solid #C9A227;">&nbsp;</td>
                      <td style="width:30%;">&nbsp;</td>
                    </tr>
                  </table>

                  <!-- ================= DATE & CERT NUMBER ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:20px;">
                    <tr>
                      <td style="text-align:center;">
                        <span style="font-family:helvetica; font-size:9px; color:#4B5563;">
                          <strong>Completed:</strong> {{completion_date}}
                          &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                          <strong>Certificate No:</strong> {{certificate_number}}
                        </span>
                      </td>
                    </tr>
                  </table>

                  <!-- ================= SPACER ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr><td style="height:24px;">&nbsp;</td></tr>
                  </table>

                  <!-- ================= SIGNATURES ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:14px;">
                    <tr>
                      <!-- Director -->
                      <td style="width:50%; text-align:center; vertical-align:bottom;">
                        {{director_signature}}
                        <div style="border-top:1px solid #374151; width:140px; margin:0 auto; padding-top:4px;">
                          <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{director_name}}</span><br>
                          <span style="font-family:helvetica; font-size:8px; color:#6B7280;">Director of Training</span>
                        </div>
                      </td>
                      <!-- Instructor -->
                      <td style="width:50%; text-align:center; vertical-align:bottom;">
                        {{instructor_signature}}
                        <div style="border-top:1px solid #374151; width:140px; margin:0 auto; padding-top:4px;">
                          <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{instructor_name}}</span><br>
                          <span style="font-family:helvetica; font-size:8px; color:#6B7280;">Course Instructor</span>
                        </div>
                      </td>
                    </tr>
                  </table>

                  <!-- ================= FOOTER ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <td style="text-align:center; padding-bottom:2px;">
                        {{qr_code}}
                        <span style="font-family:helvetica; font-size:7px; color:#9CA3AF; font-style:italic;">
                          Verify authenticity at {{verify_url}}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td style="text-align:center;">
                        <span style="font-family:helvetica; font-size:7px; color:#9CA3AF;">
                          Issued under the authority of the Technical Education, Vocational and Entrepreneurship Training Authority (TEVETA) of Zambia.
                        </span>
                      </td>
                    </tr>
                  </table>

                </td>
                <td style="border-right:2px solid #C9A227; width:24px;">&nbsp;</td>
              </tr>
              <!-- BOTTOM ROW: corner brackets via borders -->
              <tr>
                <td style="width:24px; height:24px; border-bottom:2px solid #C9A227; border-left:2px solid #C9A227;">&nbsp;</td>
                <td style="border-bottom:2px solid #C9A227; height:24px;">&nbsp;</td>
                <td style="width:24px; height:24px; border-bottom:2px solid #C9A227; border-right:2px solid #C9A227;">&nbsp;</td>
              </tr>
            </table>
            <!-- END decorative frame -->

          </td>
        </tr>
      </table>
      <!-- END navy border -->

    </td>
  </tr>
</table>
