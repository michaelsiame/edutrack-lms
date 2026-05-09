<!--
  TEVETA Certificate PDF Template for TCPDF
  A4 Landscape — 297mm x 210mm
  
  Design: Swiss / Formal Authority
  - Strict grid alignment
  - Strong sans-serif typography (Helvetica)
  - High contrast, restrained color palette
  - Institutional blue + formal gold accents
  
  TCPDF constraints: table-based layout, inline CSS only.
  No flexbox, grid, or margin:auto.
-->

<table cellpadding="0" cellspacing="0" style="width:100%; height:100%; border:4px solid #1E4A8A; background-color:#FFFFFF;">
    <tr>
        <td style="padding:8px;">
            <table cellpadding="0" cellspacing="0" style="width:100%; height:100%; border:1.5px solid #C9A227; background-color:#FFFFFF;">
                <tr>
                    <td style="padding:18px 28px 22px 28px; vertical-align:top;">
                        
                        <!-- Header: Logo | Institution | TEVETA Logo -->
                        <table style="width:100%; margin-bottom:12px;">
                            <tr>
                                <td style="width:18%; text-align:left; vertical-align:middle;">
                                    <img src="{{logo_path}}" style="width:50px; height:auto;">
                                </td>
                                <td style="width:64%; text-align:center; vertical-align:middle;">
                                    <div style="font-family:helvetica; font-size:11px; font-weight:bold; color:#1E4A8A; letter-spacing:1.5px; text-transform:uppercase; line-height:1.3;">
                                        Edutrack Computer Training College
                                    </div>
                                    <div style="font-family:helvetica; font-size:8px; color:#6B7280; margin-top:3px; letter-spacing:0.5px;">
                                        TEVETA Registered Institution — Code {{teveta_code}}
                                    </div>
                                </td>
                                <td style="width:18%; text-align:right; vertical-align:middle;">
                                    <img src="{{teveta_logo_path}}" style="width:75px; height:auto;">
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Decorative Rule -->
                        <table style="width:100%; margin-bottom:10px;">
                            <tr>
                                <td style="border-top:1.5px solid #1E4A8A;">&nbsp;</td>
                            </tr>
                        </table>
                        
                        <!-- Certificate Title -->
                        <table style="width:100%; margin-bottom:6px;">
                            <tr>
                                <td style="text-align:center;">
                                    <span style="font-family:helvetica; font-size:26px; font-weight:bold; color:#1E4A8A; letter-spacing:3px; text-transform:uppercase;">
                                        Certificate of Completion
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Subtitle -->
                        <table style="width:100%; margin-bottom:16px;">
                            <tr>
                                <td style="text-align:center;">
                                    <span style="font-family:helvetica; font-size:9px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                                        This is to certify that
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Student Name -->
                        <table style="width:100%; margin-bottom:6px;">
                            <tr>
                                <td style="text-align:center;">
                                    <span style="font-family:helvetica; font-size:22px; font-weight:bold; color:#1F2937; letter-spacing:1px;">
                                        {{student_name}}
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Achievement Label -->
                        <table style="width:100%; margin-bottom:14px;">
                            <tr>
                                <td style="text-align:center;">
                                    <span style="font-family:helvetica; font-size:9px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                                        Has successfully completed the course
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Course Title -->
                        <table style="width:100%; margin-bottom:18px;">
                            <tr>
                                <td style="text-align:center;">
                                    <span style="font-family:helvetica; font-size:15px; font-weight:bold; color:#1E4A8A; letter-spacing:0.5px;">
                                        {{course_title}}
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Gold Divider -->
                        <table style="width:100%; margin-bottom:14px;">
                            <tr>
                                <td style="width:35%;">&nbsp;</td>
                                <td style="width:30%; border-top:2px solid #C9A227;">&nbsp;</td>
                                <td style="width:35%;">&nbsp;</td>
                            </tr>
                        </table>
                        
                        <!-- Date & Certificate Number -->
                        <table style="width:100%; margin-bottom:22px;">
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
                        <table style="width:100%; margin-top:8px;">
                            <tr>
                                <td style="width:50%; text-align:center; vertical-align:top;">
                                    <div style="border-top:1px solid #1F2937; width:150px; margin:0 auto; padding-top:5px;">
                                        <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{director_name}}</span><br>
                                        <span style="font-family:helvetica; font-size:8px; color:#6B7280;">Director of Training</span>
                                    </div>
                                </td>
                                <td style="width:50%; text-align:center; vertical-align:top;">
                                    <div style="border-top:1px solid #1F2937; width:150px; margin:0 auto; padding-top:5px;">
                                        <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{instructor_name}}</span><br>
                                        <span style="font-family:helvetica; font-size:8px; color:#6B7280;">Course Instructor</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Footer: Verification & Accreditation -->
                        <table style="width:100%; margin-top:18px;">
                            <tr>
                                <td style="text-align:center;">
                                    <span style="font-family:helvetica; font-size:7px; color:#9CA3AF; font-style:italic;">
                                        Verify authenticity at {{verify_url}}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:center; padding-top:4px;">
                                    <span style="font-family:helvetica; font-size:7px; color:#9CA3AF;">
                                        This certificate is issued under the authority of the Technical Education, Vocational and Entrepreneurship Training Authority (TEVETA) of Zambia.
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
