<!--
  TEVETA Certificate PDF Template for TCPDF
  A4 Landscape (297 x 210 mm)
  
  TCPDF-safe rules:
  - No height:100% on nested tables
  - Max 2 levels of table nesting
  - No empty cells with borders
  - Simple inline CSS only
  
  Placeholders:
  {{logo_path}} {{teveta_logo_path}} {{teveta_code}} {{student_name}}
  {{course_title}} {{completion_date}} {{certificate_number}}
  {{verify_url}} {{director_name}} {{instructor_name}}
  {{director_signature}} {{instructor_signature}} {{qr_code}}
-->

<table cellpadding="0" cellspacing="0" style="width:100%; border:3px solid #1E4A8A; background-color:#FFFFFF;">
  <tr>
    <td style="padding:14px;">

      <!-- Inner gold frame -->
      <table cellpadding="0" cellspacing="0" style="width:100%; border:1.5px solid #C9A227;">
        <tr>
          <td style="padding:18px 22px 14px 22px;">

            <!-- Header -->
            <table cellpadding="0" cellspacing="0" style="width:100%;">
              <tr>
                <td style="width:16%; text-align:left; vertical-align:middle;">
                  <img src="{{logo_path}}" style="width:42px; height:auto;">
                </td>
                <td style="width:68%; text-align:center; vertical-align:middle;">
                  <div style="font-family:helvetica; font-size:10px; font-weight:bold; color:#1E4A8A; letter-spacing:1.5px; text-transform:uppercase; line-height:1.3;">
                    Edutrack Computer Training College
                  </div>
                  <div style="font-family:helvetica; font-size:7px; color:#6B7280; margin-top:2px; letter-spacing:0.5px;">
                    TEVETA Registered Institution — Code {{teveta_code}}
                  </div>
                </td>
                <td style="width:16%; text-align:right; vertical-align:middle;">
                  <img src="{{teveta_logo_path}}" style="width:64px; height:auto;">
                </td>
              </tr>
            </table>

            <!-- Header rule -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-top:8px; margin-bottom:14px;">
              <tr><td style="border-top:1.5px solid #1E4A8A;"></td></tr>
            </table>

            <!-- Title -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:6px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:26px; font-weight:bold; color:#1E4A8A; letter-spacing:2px; text-transform:uppercase;">
                    Certificate of Completion
                  </span>
                </td>
              </tr>
            </table>

            <!-- Subtitle -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:helvetica; font-size:9px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                    This is to certify that
                  </span>
                </td>
              </tr>
            </table>

            <!-- Student name -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:4px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:22px; font-weight:bold; color:#1F2937; letter-spacing:0.5px;">
                    {{student_name}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Achievement label -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:14px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:helvetica; font-size:9px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                    Has successfully completed the course
                  </span>
                </td>
              </tr>
            </table>

            <!-- Course title -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:20px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:15px; font-weight:bold; color:#1E4A8A; letter-spacing:0.5px;">
                    {{course_title}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Gold divider -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
              <tr>
                <td style="width:30%;"></td>
                <td style="width:40%; border-top:2.5px solid #C9A227;"></td>
                <td style="width:30%;"></td>
              </tr>
            </table>

            <!-- Date & cert number -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:50px;">
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

            <!-- Signatures -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:14px;">
              <tr>
                <td style="width:50%; text-align:center; vertical-align:bottom;">
                  {{director_signature}}
                  <div style="border-top:1px solid #374151; width:140px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{director_name}}</span><br>
                    <span style="font-family:helvetica; font-size:8px; color:#6B7280;">Director of Training</span>
                  </div>
                </td>
                <td style="width:50%; text-align:center; vertical-align:bottom;">
                  {{instructor_signature}}
                  <div style="border-top:1px solid #374151; width:140px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{instructor_name}}</span><br>
                    <span style="font-family:helvetica; font-size:8px; color:#6B7280;">Course Instructor</span>
                  </div>
                </td>
              </tr>
            </table>

            <!-- Footer -->
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
        </tr>
      </table>

    </td>
  </tr>
</table>
