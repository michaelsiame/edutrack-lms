<!--
  Edutrack Certificate PDF Template for TCPDF
  A4 Portrait (210 x 297 mm)

  TCPDF-safe rules:
  - No height:100% on nested tables
  - Max 2 levels of table nesting
  - No empty cells with borders
  - Simple inline CSS only
  - No border-radius

  Placeholders:
  {{logo_path}} {{teveta_logo_path}} {{teveta_code}} {{student_name}}
  {{course_title}} {{completion_date}} {{certificate_number}}
  {{verify_url}} {{director_name}} {{instructor_name}}
  {{director_signature}} {{instructor_signature}} {{qr_code}}
  {{student_number}} {{merit_text}} {{principal_name}} {{graduate_id}} {{formal_date}}
-->

<!-- Main certificate frame -->
<table cellpadding="0" cellspacing="0" style="width:100%; border:4px solid #F26522; background-color:#FFFFFF;">
  <tr>
    <td style="padding:6px;">

      <!-- Inner blue frame -->
      <table cellpadding="0" cellspacing="0" style="width:100%; border:2px solid #1E4A8A;">
        <tr>
          <td style="padding:18px 20px 14px 20px;">

            <!-- Header: Logos + College Name -->
            <table cellpadding="0" cellspacing="0" style="width:100%;">
              <tr>
                <td style="width:18%; text-align:left; vertical-align:middle;">
                  <img src="{{logo_path}}" style="width:50px; height:auto;">
                </td>
                <td style="width:64%; text-align:center; vertical-align:middle;">
                  <div style="font-family:helvetica; font-size:13px; font-weight:bold; color:#1F2937; letter-spacing:1px; text-transform:uppercase; line-height:1.3;">
                    Edutrack Computer<br>Training College
                  </div>
                  <div style="font-family:helvetica; font-size:8px; color:#6B7280; margin-top:3px; letter-spacing:0.5px;">
                    A skill training college
                  </div>
                </td>
                <td style="width:18%; text-align:right; vertical-align:middle;">
                  <img src="{{teveta_logo_path}}" style="width:55px; height:auto;">
                </td>
              </tr>
            </table>

            <!-- Decorative rule -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-top:10px; margin-bottom:14px;">
              <tr>
                <td style="width:20%; border-top:1px solid #1E4A8A;"></td>
                <td style="width:60%; text-align:center; vertical-align:middle;">
                  <span style="font-family:helvetica; font-size:10px; color:#F26522;">&#9670;</span>
                  <span style="font-family:helvetica; font-size:10px; color:#1E4A8A;">&#9670;</span>
                  <span style="font-family:helvetica; font-size:10px; color:#F26522;">&#9670;</span>
                </td>
                <td style="width:20%; border-top:1px solid #1E4A8A;"></td>
              </tr>
            </table>

            <!-- Certification statement -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
              <tr>
                <td style="width:8%; border-top:1px solid #F26522; vertical-align:middle;"></td>
                <td style="width:84%; text-align:center; vertical-align:middle;">
                  <span style="font-family:helvetica; font-size:11px; font-weight:bold; color:#1E4A8A; letter-spacing:1.5px; text-transform:uppercase;">
                    This is to certify that
                  </span>
                </td>
                <td style="width:8%; border-top:1px solid #F26522; vertical-align:middle;"></td>
              </tr>
            </table>

            <!-- Student name -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:8px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:24px; font-weight:bold; font-style:italic; color:#1F2937; letter-spacing:0.5px;">
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
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:10px; color:#4B5563; line-height:1.5;">
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
                  <span style="font-family:helvetica; font-size:16px; font-weight:bold; color:#1E4A8A; letter-spacing:0.5px; text-transform:uppercase;">
                    {{course_title}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Merit / Distinction -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:14px; font-weight:bold; font-style:italic; color:#1F2937;">
                    {{merit_text}}
                  </span>
                </td>
              </tr>
            </table>

            <!-- Decorative line -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:10px;">
              <tr>
                <td style="width:30%;"></td>
                <td style="width:40%; border-top:1px solid #F26522;"></td>
                <td style="width:30%;"></td>
              </tr>
              <tr>
                <td style="width:30%;"></td>
                <td style="width:40%; text-align:center; padding-top:2px;">
                  <span style="font-family:helvetica; font-size:8px; color:#F26522;">&#9670;</span>
                </td>
                <td style="width:30%;"></td>
              </tr>
            </table>

            <!-- Graduation date -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:times; font-size:10px; color:#4B5563; line-height:1.6;">
                    was admitted to the certificate at a Graduation Ceremony<br>
                    held on the <strong>{{formal_date}}</strong>
                  </span>
                </td>
              </tr>
            </table>

            <!-- Signatures row -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:6px;">
              <tr>
                <!-- Principal -->
                <td style="width:32%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:120px; margin:0 auto; padding-top:6px;">
                    <span style="font-family:helvetica; font-size:8px; color:#4B5563;">Principal</span>
                  </div>
                </td>
                <!-- Seal / Badge -->
                <td style="width:36%; text-align:center; vertical-align:middle;">
                  <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                    <tr>
                      <td style="width:60px; height:60px; border:2px solid #1E4A8A; background-color:#1E4A8A; text-align:center; vertical-align:middle;">
                        <span style="font-family:helvetica; font-size:20px; color:#C9A227;">&#9733;</span>
                      </td>
                    </tr>
                    <tr>
                      <td style="text-align:center; padding-top:2px;">
                        <span style="font-family:helvetica; font-size:6px; color:#1E4A8A; font-weight:bold;">SEAL</span>
                      </td>
                    </tr>
                  </table>
                </td>
                <!-- Director -->
                <td style="width:32%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:120px; margin:0 auto; padding-top:6px;">
                    <span style="font-family:helvetica; font-size:8px; color:#4B5563;">Director</span>
                  </div>
                </td>
              </tr>
            </table>

            <!-- Graduate signature & ID row -->
            <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
              <tr>
                <td style="width:50%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:140px; margin:0 auto; padding-top:6px;">
                    <span style="font-family:helvetica; font-size:8px; color:#4B5563;">Graduate's Signature</span>
                  </div>
                </td>
                <td style="width:50%; text-align:center; vertical-align:bottom;">
                  <div style="border-top:1px solid #374151; width:140px; margin:0 auto; padding-top:6px;">
                    <span style="font-family:helvetica; font-size:8px; color:#4B5563;">Graduate's I.D. No.</span>
                  </div>
                </td>
              </tr>
            </table>

            <!-- Bottom info box -->
            <table cellpadding="0" cellspacing="0" style="width:100%; border:1.5px solid #1E4A8A; background-color:#F8FAFC; margin-bottom:10px;">
              <tr>
                <td style="padding:10px 14px;">
                  <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                      <!-- Left column -->
                      <td style="width:48%; vertical-align:top;">
                        <table cellpadding="0" cellspacing="0" style="width:100%;">
                          <tr>
                            <td style="padding-bottom:8px;">
                              <span style="font-family:helvetica; font-size:7px; font-weight:bold; color:#1E4A8A; text-transform:uppercase;">Student Number</span><br>
                              <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{student_number}}</span>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <span style="font-family:helvetica; font-size:7px; font-weight:bold; color:#1E4A8A; text-transform:uppercase;">Certificate Number</span><br>
                              <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{certificate_number}}</span>
                            </td>
                          </tr>
                        </table>
                      </td>
                      <!-- Divider -->
                      <td style="width:4%; text-align:center; vertical-align:middle;">
                        <div style="border-left:1px solid #1E4A8A; height:40px; margin:0 auto;"></div>
                      </td>
                      <!-- Right column -->
                      <td style="width:48%; vertical-align:top;">
                        <table cellpadding="0" cellspacing="0" style="width:100%;">
                          <tr>
                            <td style="padding-bottom:8px;">
                              <span style="font-family:helvetica; font-size:7px; font-weight:bold; color:#1E4A8A; text-transform:uppercase;">Date of Graduation</span><br>
                              <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{completion_date}}</span>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <span style="font-family:helvetica; font-size:7px; font-weight:bold; color:#1E4A8A; text-transform:uppercase;">Course</span><br>
                              <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{course_title}}</span>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Footer -->
            <table cellpadding="0" cellspacing="0" style="width:100%;">
              <tr>
                <td style="text-align:center; padding-bottom:2px;">
                  {{qr_code}}
                  <span style="font-family:helvetica; font-size:6px; color:#9CA3AF; font-style:italic;">
                    Verify authenticity at {{verify_url}}
                  </span>
                </td>
              </tr>
              <tr>
                <td style="text-align:center;">
                  <span style="font-family:helvetica; font-size:6px; color:#9CA3AF;">
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
