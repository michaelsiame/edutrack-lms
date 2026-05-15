<!--
  Edutrack Certificate PDF Template for TCPDF
  A4 Portrait (210 x 297 mm)

  TCPDF-safe rules:
  - No height on <td> elements (causes hangs)
  - No <sup> tags (not supported well)
  - Max 2 levels of table nesting
  - Simple inline CSS only
  - No border-radius
  - position: absolute is not supported

  Placeholders:
  {{logo_path}} {{teveta_logo_path}} {{teveta_code}} {{student_name}}
  {{course_title}} {{completion_date}} {{formal_date}} {{certificate_number}}
  {{verify_url}} {{director_name}} {{principal_name}} {{instructor_name}}
  {{director_signature}} {{instructor_signature}} {{qr_code}}
  {{student_number}} {{merit_text}} {{graduate_id}}
  {{seal_path}} {{corner_tl}} {{corner_tr}} {{corner_bl}} {{corner_br}}
-->

<table cellpadding="0" cellspacing="0" style="width:100%; border:3px solid #F26522; background-color:#FFFFFF;">
  <tr>
    <td style="padding:4px;">

      <table cellpadding="0" cellspacing="0" style="width:100%; border:1.5px solid #1E4A8A;">
        <tr>
          <td style="padding:18px 20px 14px 20px;">

            <!-- Top Corners -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:2px;">
              <tr>
                <td style="width:28px; text-align:left; vertical-align:top;">
                  <img src="{{corner_tl}}" style="width:26px; height:26px;">
                </td>
                <td style="width:100%;"></td>
                <td style="width:28px; text-align:right; vertical-align:top;">
                  <img src="{{corner_tr}}" style="width:26px; height:26px;">
                </td>
              </tr>
            </table>

            <!-- Header: Logos + College Name -->
            <table cellpadding="0" cellspacing="0" style="width:100%;">
              <tr>
                <td style="width:18%; text-align:left; vertical-align:middle;">
                  <img src="{{logo_path}}" style="width:48px; height:auto;">
                </td>
                <td style="width:64%; text-align:center; vertical-align:middle;">
                  <div style="font-family:helvetica; font-size:11px; font-weight:bold; color:#1F2937; letter-spacing:1px; text-transform:uppercase; line-height:1.3;">
                    Edutrack Computer<br>Training College
                  </div>
                  <div style="font-family:helvetica; font-size:7px; color:#6B7280; margin-top:2px; letter-spacing:0.5px;">
                    A skill training college
                  </div>
                </td>
                <td style="width:18%; text-align:right; vertical-align:middle;">
                  <img src="{{teveta_logo_path}}" style="width:52px; height:auto;">
                </td>
              </tr>
            </table>

            <!-- Decorative rule with diamonds -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-top:8px; margin-bottom:12px;">
              <tr>
                <td style="width:28%; border-top:1px solid #1E4A8A;"></td>
                <td style="width:44%; text-align:center; vertical-align:middle;">
                  <span style="font-family:helvetica; font-size:8px; color:#F26522;">&#9670; &#9670; &#9670;</span>
                </td>
                <td style="width:28%; border-top:1px solid #1E4A8A;"></td>
              </tr>
            </table>

            <!-- Certification statement -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
              <tr>
                <td style="width:10%; border-top:1px solid #F26522; vertical-align:middle;"></td>
                <td style="width:80%; text-align:center; vertical-align:middle;">
                  <span style="font-family:helvetica; font-size:10px; font-weight:bold; color:#1E4A8A; letter-spacing:1.5px; text-transform:uppercase;">
                    This is to certify that
                  </span>
                </td>
                <td style="width:10%; border-top:1px solid #F26522; vertical-align:middle;"></td>
              </tr>
            </table>

            <!-- Student name -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:6px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:22px; font-weight:bold; font-style:italic; color:#1F2937; letter-spacing:0.5px;">
                    {{student_name}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Decorative underline -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
              <tr>
                <td style="width:25%;"></td>
                <td style="width:50%; border-top:1px solid #F26522;"></td>
                <td style="width:25%;"></td>
              </tr>
            </table>

            <!-- Award text -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:8px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:9px; color:#4B5563; line-height:1.5;">
                    having satisfied the requirements for the<br>
                    award of the certificate of
                  </span>
                </td>
              </tr>
            </table>

            <!-- Course title -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:4px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:helvetica; font-size:15px; font-weight:bold; color:#1E4A8A; letter-spacing:0.5px; text-transform:uppercase;">
                    {{course_title}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Merit / Distinction -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:8px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:13px; font-weight:bold; font-style:italic; color:#1F2937;">
                    {{merit_text}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Decorative line -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
              <tr>
                <td style="width:32%;"></td>
                <td style="width:36%; border-top:1px solid #F26522;"></td>
                <td style="width:32%;"></td>
              </tr>
              <tr>
                <td style="width:32%;"></td>
                <td style="width:36%; text-align:center; padding-top:2px;">
                  <span style="font-family:helvetica; font-size:7px; color:#F26522;">&#9670;</span>
                </td>
                <td style="width:32%;"></td>
              </tr>
            </table>

            <!-- Graduation date -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:9px; color:#4B5563; line-height:1.6;">
                    was admitted to the certificate at a Graduation Ceremony<br>
                    held on the <strong>{{formal_date}}</strong>
                  </span>
                </td>
              </tr>
            </table>

            <!-- Signatures row -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:8px;">
              <tr>
                <td style="width:30%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:100px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:7px; color:#4B5563;">Principal</span>
                  </div>
                </td>
                <td style="width:40%; text-align:center; vertical-align:middle;">
                  <img src="{{seal_path}}" style="height:65px; width:auto;">
                </td>
                <td style="width:30%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:100px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:7px; color:#4B5563;">Director</span>
                  </div>
                </td>
              </tr>
            </table>

            <!-- Graduate signature & ID row -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
              <tr>
                <td style="width:50%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:120px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:7px; color:#4B5563;">Graduate's Signature</span>
                  </div>
                </td>
                <td style="width:50%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:120px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:7px; color:#4B5563;">Graduate's I.D. No.</span>
                  </div>
                </td>
              </tr>
            </table>

            <!-- Bottom info box -->
            <table cellpadding="0" cellspacing="0" style="width:100%; border:1px solid #1E4A8A; background-color:#F8FAFC; margin-bottom:10px;">
              <tr>
                <td style="padding:8px 10px;">
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <td style="width:46%; vertical-align:top;">
                        <span style="font-family:helvetica; font-size:6px; font-weight:bold; color:#1E4A8A; text-transform:uppercase;">Student Number</span><br>
                        <span style="font-family:helvetica; font-size:8px; font-weight:bold; color:#1F2937;">{{student_number}}</span>
                      </td>
                      <td style="width:8%; text-align:center; vertical-align:middle;">
                        <span style="font-family:helvetica; font-size:10px; color:#1E4A8A;">|</span>
                      </td>
                      <td style="width:46%; vertical-align:top;">
                        <span style="font-family:helvetica; font-size:6px; font-weight:bold; color:#1E4A8A; text-transform:uppercase;">Date of Graduation</span><br>
                        <span style="font-family:helvetica; font-size:8px; font-weight:bold; color:#1F2937;">{{completion_date}}</span>
                      </td>
                    </tr>
                    <tr>
                      <td style="width:46%; vertical-align:top; padding-top:6px;">
                        <span style="font-family:helvetica; font-size:6px; font-weight:bold; color:#1E4A8A; text-transform:uppercase;">Certificate Number</span><br>
                        <span style="font-family:helvetica; font-size:8px; font-weight:bold; color:#1F2937;">{{certificate_number}}</span>
                      </td>
                      <td style="width:8%; text-align:center; vertical-align:middle;">
                        <span style="font-family:helvetica; font-size:10px; color:#1E4A8A;">|</span>
                      </td>
                      <td style="width:46%; vertical-align:top; padding-top:6px;">
                        <span style="font-family:helvetica; font-size:6px; font-weight:bold; color:#1E4A8A; text-transform:uppercase;">Course</span><br>
                        <span style="font-family:helvetica; font-size:8px; font-weight:bold; color:#1F2937;">{{course_title}}</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Bottom Corners -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-top:2px;">
              <tr>
                <td style="width:28px; text-align:left; vertical-align:bottom;">
                  <img src="{{corner_bl}}" style="width:26px; height:26px;">
                </td>
                <td style="width:100%;"></td>
                <td style="width:28px; text-align:right; vertical-align:bottom;">
                  <img src="{{corner_br}}" style="width:26px; height:26px;">
                </td>
              </tr>
            </table>

            <!-- Footer -->
            <table cellpadding="0" cellspacing="0" style="width:100%;">
              <tr>
                <td style="text-align:center; padding-bottom:1px;">
                  {{qr_code}}
                  <span style="font-family:helvetica; font-size:5px; color:#9CA3AF; font-style:italic;">
                    Verify authenticity at {{verify_url}}
                  </span>
                </td>
              </tr>
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:helvetica; font-size:5px; color:#9CA3AF;">
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
