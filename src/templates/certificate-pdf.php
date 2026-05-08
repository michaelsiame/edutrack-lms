<!--
  Certificate PDF Template for TCPDF
  
  Design your certificate here using HTML with inline CSS.
  TCPDF supports: tables, images, fonts, colors, borders, basic padding/margin.
  
  TCPDF does NOT support: flexbox, grid, margin:auto, complex positioning.
  Use TABLE-based layout for reliable rendering.
  
  Placeholders (replaced by Certificate::generatePDF()):
  {{logo_path}}        - Path to institution logo
  {{teveta_logo_path}} - Path to TEVETA logo
  {{teveta_code}}      - TEVETA institution code
  {{student_name}}     - Student full name (UPPERCASE)
  {{course_title}}     - Course name
  {{completion_date}}  - e.g. "January 15, 2026"
  {{certificate_number}} - e.g. "EDTRK-202605-00001"
  {{verify_url}}       - Public verification URL
  {{director_name}}    - Director name (or "Michael Siame" default)
  {{instructor_name}}  - Course instructor name
-->

<table cellpadding="0" cellspacing="0" style="width:100%; height:100%; border:3px solid #2E70DA;">
    <tr>
        <td style="padding:6px;">
            <table cellpadding="0" cellspacing="0" style="width:100%; height:100%; border:2px solid #F6B745; background-color:#FFFFFF;">
                <tr>
                    <td style="padding:25px;">
                        
                        <!-- Header Row: Logo | Institution | TEVETA Logo -->
                        <table style="width:100%; margin-bottom:15px;">
                            <tr>
                                <td style="width:20%; text-align:left; vertical-align:middle;">
                                    <img src="{{logo_path}}" style="width:55px;">
                                </td>
                                <td style="width:60%; text-align:center; vertical-align:middle;">
                                    <div style="font-family:helvetica; font-size:20px; font-weight:bold; color:#1E4A8A; line-height:1.2;">
                                        EDUTRACK COMPUTER<br>TRAINING COLLEGE
                                    </div>
                                    <div style="font-family:helvetica; font-size:9px; color:#6B7280; margin-top:4px;">
                                        TEVETA Registered Institution &mdash; {{teveta_code}}
                                    </div>
                                </td>
                                <td style="width:20%; text-align:right; vertical-align:middle;">
                                    <img src="{{teveta_logo_path}}" style="width:55px;">
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Certificate Title -->
                        <div style="text-align:center; margin-top:25px; margin-bottom:5px;">
                            <span style="font-family:helvetica; font-size:32px; font-weight:bold; color:#D97706; letter-spacing:2px;">
                                CERTIFICATE OF COMPLETION
                            </span>
                        </div>
                        <div style="text-align:center; margin-bottom:25px;">
                            <span style="font-family:helvetica; font-size:11px; color:#9CA3AF;">
                                This is to certify that
                            </span>
                        </div>
                        
                        <!-- Student Name -->
                        <div style="text-align:center; margin-bottom:6px;">
                            <span style="font-family:helvetica; font-size:10px; color:#6B7280; text-transform:uppercase; letter-spacing:3px;">
                                Has successfully completed
                            </span>
                        </div>
                        <div style="text-align:center; margin-bottom:20px;">
                            <span style="font-family:helvetica; font-size:26px; font-weight:bold; color:#1F2937;">
                                {{student_name}}
                            </span>
                        </div>
                        
                        <!-- Course Name -->
                        <div style="text-align:center; margin-bottom:4px;">
                            <span style="font-family:helvetica; font-size:10px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                                Course
                            </span>
                        </div>
                        <div style="text-align:center; margin-bottom:20px;">
                            <span style="font-family:helvetica; font-size:16px; font-weight:bold; color:#2E70DA;">
                                {{course_title}}
                            </span>
                        </div>
                        
                        <!-- Divider -->
                        <table style="width:100%; margin-bottom:20px;">
                            <tr>
                                <td style="width:40%;">&nbsp;</td>
                                <td style="width:20%; border-top:2px solid #F6B745;">&nbsp;</td>
                                <td style="width:40%;">&nbsp;</td>
                            </tr>
                        </table>
                        
                        <!-- Details -->
                        <div style="text-align:center; margin-bottom:30px;">
                            <span style="font-family:helvetica; font-size:10px; color:#6B7280;">
                                Completed on <span style="font-weight:bold; color:#1F2937;">{{completion_date}}</span>
                                &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                                Certificate No: <span style="font-weight:bold; color:#1F2937;">{{certificate_number}}</span>
                            </span>
                        </div>
                        
                        <!-- Signatures -->
                        <table style="width:100%; margin-top:20px;">
                            <tr>
                                <td style="width:50%; text-align:center; vertical-align:top;">
                                    <div style="border-top:1px solid #1F2937; width:160px; margin:0 auto; padding-top:6px;">
                                        <span style="font-family:helvetica; font-size:10px; font-weight:bold; color:#1F2937;">{{director_name}}</span><br>
                                        <span style="font-family:helvetica; font-size:9px; color:#6B7280;">Director of Training</span>
                                    </div>
                                </td>
                                <td style="width:50%; text-align:center; vertical-align:top;">
                                    <div style="border-top:1px solid #1F2937; width:160px; margin:0 auto; padding-top:6px;">
                                        <span style="font-family:helvetica; font-size:10px; font-weight:bold; color:#1F2937;">{{instructor_name}}</span><br>
                                        <span style="font-family:helvetica; font-size:9px; color:#6B7280;">Course Instructor</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Footer -->
                        <div style="text-align:center; margin-top:30px;">
                            <span style="font-family:helvetica; font-size:8px; color:#9CA3AF; font-style:italic;">
                                Verify this certificate at: {{verify_url}}
                            </span>
                        </div>
                        
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
