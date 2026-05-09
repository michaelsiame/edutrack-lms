<!--
  TEVETA Certificate PDF Template for TCPDF
  A4 Landscape — 297mm x 210mm
  
  Design: Formal Institutional Authority
  - Single master table for reliable TCPDF rendering
  - Explicit padding controls vertical rhythm (no margin overlap)
  - Navy + Gold palette for government-document prestige
  - Serif title (Times) for classical authority
  - Decorative corner brackets on the border frame
  
  TCPDF constraints: table-based layout, inline CSS only.
-->

<table cellpadding="0" cellspacing="0" style="width:100%; height:100%; background-color:#FDFCFA;">
    <tr>
        <td style="padding:10px;">
            <!-- Outer institutional border -->
            <table cellpadding="0" cellspacing="0" style="width:100%; height:100%; border:3px solid #1E4A8A; background-color:#FFFFFF;">
                <tr>
                    <td style="padding:5px;">
                        <!-- Inner gold accent border -->
                        <table cellpadding="0" cellspacing="0" style="width:100%; height:100%; border:1.5px solid #C9A227;">
                            <tr>
                                <td style="padding:16px 26px 12px 26px; vertical-align:top;">
                                    
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
                                    <table cellpadding="0" cellspacing="0" style="width:100%; margin-top:8px; margin-bottom:12px;">
                                        <tr><td style="border-top:1.5px solid #1E4A8A;">&nbsp;</td></tr>
                                    </table>
                                    
                                    <!-- ================= TITLE BLOCK ================= -->
                                    <table cellpadding="0" cellspacing="0" style="width:100%;">
                                        <tr>
                                            <td style="text-align:center; padding-bottom:8px;">
                                                <span style="font-family:times; font-size:26px; font-weight:bold; color:#1E4A8A; letter-spacing:2px; text-transform:uppercase;">
                                                    Certificate of Completion
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center; padding-bottom:16px;">
                                                <span style="font-family:helvetica; font-size:9px; color:#6B7280; text-transform:uppercase; letter-spacing:2px;">
                                                    This is to certify that
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- ================= STUDENT NAME ================= -->
                                    <table cellpadding="0" cellspacing="0" style="width:100%;">
                                        <tr>
                                            <td style="text-align:center; padding-bottom:4px;">
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
                                            <td style="text-align:center; padding-bottom:18px;">
                                                <span style="font-family:times; font-size:15px; font-weight:bold; color:#1E4A8A; letter-spacing:0.5px;">
                                                    {{course_title}}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- ================= GOLD DIVIDER ================= -->
                                    <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:16px;">
                                        <tr>
                                            <td style="width:30%;">&nbsp;</td>
                                            <td style="width:40%; border-top:2.5px solid #C9A227;">&nbsp;</td>
                                            <td style="width:30%;">&nbsp;</td>
                                        </tr>
                                    </table>
                                    
                                    <!-- ================= DATE & CERT NUMBER ================= -->
                                    <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:32px;">
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
                                    
                                    <!-- ================= SPACER (pushes signatures down) ================= -->
                                    <table cellpadding="0" cellspacing="0" style="width:100%;">
                                        <tr><td style="height:32px;">&nbsp;</td></tr>
                                    </table>
                                    
                                    <!-- ================= SIGNATURES ================= -->
                                    <table cellpadding="0" cellspacing="0" style="width:100%; margin-bottom:18px;">
                                        <tr>
                                            <td style="width:50%; text-align:center; vertical-align:bottom;">
                                                <div style="border-top:1px solid #374151; width:140px; margin:0 auto; padding-top:5px;">
                                                    <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">{{director_name}}</span><br>
                                                    <span style="font-family:helvetica; font-size:8px; color:#6B7280;">Director of Training</span>
                                                </div>
                                            </td>
                                            <td style="width:50%; text-align:center; vertical-align:bottom;">
                                                <div style="border-top:1px solid #374151; width:140px; margin:0 auto; padding-top:5px;">
                                                    <span style="font-family:helvetica; font-size:9px; font-weight:bold; color:#1F2937;">
                                                        {{instructor_name}}
                                                    </span><br>
                                                    <span style="font-family:helvetica; font-size:8px; color:#6B7280;">Course Instructor</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- ================= FOOTER ================= -->
                                    <table cellpadding="0" cellspacing="0" style="width:100%;">
                                        <tr>
                                            <td style="text-align:center; padding-bottom:3px;">
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
        </td>
    </tr>
</table>
