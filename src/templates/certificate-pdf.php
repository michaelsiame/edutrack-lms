<!--
  TEVETA Certificate – TCPDF Template
  A4 Landscape (297 x 210 mm) · Table‑based · Inline CSS only
  
  Improvements:
  - Decorative gold frame using box‑drawing corner brackets (┌ ┐ └ ┘)
  - Cleaner vertical rhythm and typography scale
  - Optional signature images and QR code
  - All original variables preserved
  - Requires DejaVu Sans for corner glyphs
-->

<table cellpadding="0" cellspacing="0" style="width:100%; height:100%; background-color:#FDFCFA;">
  <tr>
    <td style="padding:10px;">

      <!-- ===== OUTER NAVY BORDER ===== -->
      <table cellpadding="0" cellspacing="0" style="width:100%; height:100%; border:3px solid #1E4A8A; background-color:#FFFFFF;">
        <tr>
          <td style="padding:12px 16px 10px 16px; vertical-align:top;">

            <!-- ===== DECORATIVE GOLD FRAME (corner brackets + rules) ===== -->
            <table cellpadding="0" cellspacing="0" style="width:100%; height:100%;">
              <!-- TOP ROW: left bracket, top rule, right bracket -->
              <tr>
                <td style="width:30px; text-align:left; vertical-align:top; font-size:30px; line-height:1; color:#C9A227; font-family:'DejaVu Sans', sans-serif;">┌</td>
                <td style="border-top:1.5px solid #C9A227; height:1px;"></td>
                <td style="width:30px; text-align:right; vertical-align:top; font-size:30px; line-height:1; color:#C9A227; font-family:'DejaVu Sans', sans-serif;">┐</td>
              </tr>
              <!-- MIDDLE ROW: left rule, content, right rule -->
              <tr>
                <td style="border-left:1.5px solid #C9A227; width:30px;"></td>
                <td style="padding:8px 12px 0 12px; vertical-align:top;">

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
                  <table cellpadding="0" cellspacing="0" style="width:100%; margin-top:6px; margin-bottom:8px;">
                    <tr><td style="border-top:1.5px solid #1E4A8A;">&nbsp;</td></tr>
                  </table>

                  <!-- ================= TITLE ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <td style="text-align:center; padding-bottom:4px;">
                        <span style="font-family:times; font-size:26px; font-weight:bold; color:#1E4A8A; letter-spacing:2px; text-transform:uppercase;">
                          Certificate of Completion
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td style="text-align:center; padding-bottom:10px;">
                        <span style="font-family:helvetica; font-size:9px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                          This is to certify that
                        </span>
                      </td>
                    </tr>
                  </table>

                  <!-- ================= STUDENT NAME ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <td style="text-align:center; padding-bottom:2px;">
                        <span style="font-family:times; font-size:23px; font-weight:bold; color:#1F2937; letter-spacing:0.5px;">
                          {{student_name}}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td style="text-align:center; padding-bottom:12px;">
                        <span style="font-family:helvetica; font-size:9px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                          Has successfully completed the course
                        </span>
                      </td>
                    </tr>
                  </table>

                  <!-- ================= COURSE TITLE ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <td style="text-align:center; padding-bottom:14px;">
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

                  <!-- ================= FLEXIBLE SPACER (pushes signatures down) ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr><td style="height:20px;">&nbsp;</td></tr>
                  </table>

                  <!-- ================= SIGNATURES ================= -->
                  <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
                    <tr>
                      <!-- Director -->
                      <td style="width:50%; text-align:center; vertical-align:bottom;">
                        {{#director_signature_path}}
                        <img src="{{director_signature_path}}" style="max-height:40px; display:block; margin:0 auto 3px;">
                        {{/director_signature_path}}
                        <div style="border-top:1px solid #374151; width:140px; margin:0 auto; padding-top:4px;">
                          <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{director_name}}</span><br>
                          <span style="font-family:helvetica; font-size:8px; color:#6B7280;">Director of Training</span>
                        </div>
                      </td>
                      <!-- Instructor -->
                      <td style="width:50%; text-align:center; vertical-align:bottom;">
                        {{#instructor_signature_path}}
                        <img src="{{instructor_signature_path}}" style="max-height:40px; display:block; margin:0 auto 3px;">
                        {{/instructor_signature_path}}
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
                        {{#qr_code_path}}
                        <img src="{{qr_code_path}}" style="width:45px; height:45px; vertical-align:middle; margin-right:6px;">
                        {{/qr_code_path}}
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

                </td> <!-- end content cell -->
                <td style="border-right:1.5px solid #C9A227; width:30px;"></td>
              </tr>
              <!-- BOTTOM ROW: left bracket, bottom rule, right bracket -->
              <tr>
                <td style="width:30px; text-align:left; vertical-align:bottom; font-size:30px; line-height:1; color:#C9A227; font-family:'DejaVu Sans', sans-serif;">└</td>
                <td style="border-bottom:1.5px solid #C9A227; height:1px;"></td>
                <td style="width:30px; text-align:right; vertical-align:bottom; font-size:30px; line-height:1; color:#C9A227; font-family:'DejaVu Sans', sans-serif;">┘</td>
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