<!--
  Edutrack Certificate PDF Template for TCPDF
  A4 Portrait (210 x 297 mm)

  TCPDF-safe rules:
  - No height on <td> elements (causes hangs)
  - No <sup> tags (not supported)
  - Max 2 levels of table nesting
  - Simple inline CSS only
  - No border-radius

  Placeholders:
  {{logo_path}} {{teveta_logo_path}} {{teveta_code}} {{student_name}}
  {{course_title}} {{completion_date}} {{formal_date}} {{certificate_number}}
  {{verify_url}} {{director_name}} {{principal_name}} {{instructor_name}}
  {{director_signature}} {{instructor_signature}} {{qr_code}}
  {{student_number}} {{merit_text}} {{graduate_id}}
-->

<table cellpadding="0" cellspacing="0" style="width:100%; border:3px solid #F26522; background-color:#FFFFFF;">
  <tr>
    <td style="padding:5px;">

      <table cellpadding="0" cellspacing="0" style="width:100%; border:1.5px solid #1E4A8A;">
        <tr>
          <td style="padding:20px 22px 16px 22px;">

            <!-- Header: Logos + College Name -->
            <table cellpadding="0" cellspacing="0" style="width:100%;">
              <tr>
                <td style="width:18%; text-align:left; vertical-align:middle;">
                  <img src="{{logo_path}}" style="width:48px; height:auto;">
                </td>
                <td style="width:64%; text-align:center; vertical-align:middle;">
                  <div style="font-family:helvetica; font-size:12px; font-weight:bold; color:#1F2937; letter-spacing:1px; text-transform:uppercase; line-height:1.3;">
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

            <!-- Decorative rule -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-top:10px; margin-bottom:16px;">
              <tr>
                <td style="width:20%; border-top:1px solid #1E4A8A;"></td>
                <td style="width:60%; text-align:center; vertical-align:middle;">
                  <span style="font-family:helvetica; font-size:9px; color:#F26522;">*</span>
                  <span style="font-family:helvetica; font-size:9px; color:#1E4A8A;">*</span>
                  <span style="font-family:helvetica; font-size:9px; color:#F26522;">*</span>
                </td>
                <td style="width:20%; border-top:1px solid #1E4A8A;"></td>
              </tr>
            </table>

            <!-- Certification statement -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:12px;">
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
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:8px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:22px; font-weight:bold; font-style:italic; color:#1F2937; letter-spacing:0.5px;">
                    {{student_name}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Decorative underline -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:12px;">
              <tr>
                <td style="width:25%;"></td>
                <td style="width:50%; border-top:1px solid #F26522;"></td>
                <td style="width:25%;"></td>
              </tr>
            </table>

            <!-- Award text -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
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
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:6px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:helvetica; font-size:15px; font-weight:bold; color:#1E4A8A; letter-spacing:0.5px; text-transform:uppercase;">
                    {{course_title}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Merit / Distinction -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:13px; font-weight:bold; font-style:italic; color:#1F2937;">
                    {{merit_text}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Decorative line -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:12px;">
              <tr>
                <td style="width:30%;"></td>
                <td style="width:40%; border-top:1px solid #F26522;"></td>
                <td style="width:30%;"></td>
              </tr>
              <tr>
                <td style="width:30%;"></td>
                <td style="width:40%; text-align:center; padding-top:2px;">
                  <span style="font-family:helvetica; font-size:7px; color:#F26522;">*</span>
                </td>
                <td style="width:30%;"></td>
              </tr>
            </table>

            <!-- Graduation date -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:20px;">
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
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
              <tr>
                <td style="width:33%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:110px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:7px; color:#4B5563;">Principal</span>
                  </div>
                </td>
                <td style="width:34%; text-align:center; vertical-align:middle;">
                  <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                    <tr>
                      <td style="border:2px solid #1E4A8A; background-color:#1E4A8A; text-align:center; vertical-align:middle; padding:8px;">
                        <span style="font-family:helvetica; font-size:10px; color:#C9A227; font-weight:bold;">SEAL</span>
                      </td>
                    </tr>
                    <tr>
                      <td style="text-align:center; padding-top:1px;">
                        <span style="font-family:helvetica; font-size:5px; color:#1E4A8A; font-weight:bold;">SEAL</span>
                      </td>
                    </tr>
                  </table>
                </td>
                <td style="width:33%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:110px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:7px; color:#4B5563;">Director</span>
                  </div>
                </td>
              </tr>
            </table>

            <!-- Graduate signature & ID row -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:20px;">
              <tr>
                <td style="width:50%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:130px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:7px; color:#4B5563;">Graduate's Signature</span>
                  </div>
                </td>
                <td style="width:50%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:130px; margin:0 auto; padding-top:4px;">
                    <span style="font-family:helvetica; font-size:7px; color:#4B5563;">Graduate's I.D. No.</span>
                  </div>
                </td>
              </tr>
            </table>

            <!-- Bottom info box -->
            <table cellpadding="0" cellspacing="0" style="width:100%; border:1px solid #1E4A8A; background-color:#F8FAFC; margin-bottom:12px;">
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
