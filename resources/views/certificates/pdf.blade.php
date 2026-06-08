<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page {
    size: A4;
    margin: 0;
    padding: 0;
}
html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    color: #1a1a1a;
}
.script-font { font-family: 'greatvibes', 'DejaVu Serif', serif; }
</style>
</head>
<body>

<!-- Outer Orange Border -->
<table width="100%" cellpadding="0" cellspacing="0" style="width: 210mm; height: 297mm; background-color: #f26522;">
<tr>
<td valign="top" style="padding: 1.5mm;">
    
    <!-- Inner Blue Border -->
    <table width="100%" cellpadding="0" cellspacing="0" style="height: 100%; background-color: #1e3a8a;">
    <tr>
    <td valign="top" style="padding: 1.5mm;">
        
        <!-- Center White Content Area -->
        <table width="100%" cellpadding="0" cellspacing="0" style="height: 100%; background-color: #ffffff;">
        <tr>
        <td valign="top" style="padding: 8mm 14mm 3mm 14mm;">

            <!-- HEADER -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 15px;">
                <tr>
                    <td width="20%" align="center">
                        <img src="{{ public_path('assets/images/logo-pdf.png') }}" style="height: 20mm; width: auto;" alt="Logo">
                    </td>
                    <td width="60%" align="center">
                        <span style="font-size: 19pt; font-weight: 900; color: #1a1a1a; line-height: 1.2;">EDUTRACK COMPUTER<br>TRAINING COLLEGE</span><br>
                        <span style="font-family: 'DejaVu Serif', serif; font-size: 8.5pt; color: #444; font-style: italic;">TEVETA Registered Institution &mdash; TVA/2064</span>
                    </td>
                    <td width="20%" align="right">
                        <img src="{{ public_path('assets/images/teveta-logo.png') }}" style="height: 20mm; width: auto;" alt="Teveta">
                    </td>
                </tr>
            </table>

            <!-- CERTIFY BANNER -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 15px 0;">
                <tr>
                    <td width="15%" align="right" style="color: #f26522; font-size: 9pt;">&#8594;&#8594;&#9830;</td>
                    <td width="70%" align="center" style="font-size: 17pt; font-weight: 900; color: #1e3a8a; letter-spacing: 2px;">THIS IS TO CERTIFY THAT</td>
                    <td width="15%" align="left" style="color: #f26522; font-size: 9pt;">&#9830;&#8592;&#8592;</td>
                </tr>
            </table>

            <!-- STUDENT NAME -->
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" class="script-font" style="font-size: 38pt; color: #1a1a1a;">
                        {{ $student_name }}
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top: 5px;">
                        <table width="130mm" align="center" cellpadding="0" cellspacing="0">
                            <tr><td style="border-top: 1px solid #f26522; font-size: 0; line-height: 0;">&nbsp;</td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- BODY TEXT -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 15px;">
                <tr>
                    <td align="center" style="font-size: 9.5pt; color: #333; line-height: 1.5;">
                        has successfully completed the requirements<br>
                        for the award of this certificate in
                    </td>
                </tr>
            </table>

            <!-- COURSE TITLE -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 10px 0;">
                <tr>
                    <td align="center" style="font-size: 22pt; font-weight: 900; color: #1e3a8a; letter-spacing: 1px;">
                        {{ $course_title }}
                    </td>
                </tr>
            </table>

            <!-- CLASSIFICATION -->
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" class="script-font" style="font-size: 16pt; color: #1a1a1a;">
                        {{ $classification }}
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top: 5px;">
                        <table width="50mm" align="center" cellpadding="0" cellspacing="0">
                            <tr><td style="border-top: 1px solid #f26522; font-size: 0; line-height: 0;">&nbsp;</td></tr>
                        </table>
                        <div style="color: #f26522; font-size: 8pt; margin-top: -6px;"><span style="background: #fff; padding: 0 4px;">&#9670;</span></div>
                    </td>
                </tr>
            </table>

            <!-- DATE -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 15px 0;">
                <tr>
                    <td align="center" style="font-size: 9.5pt; color: #333; line-height: 1.6;">
                        was admitted to this certificate at a Graduation Ceremony held on the<br><br>
                        <span class="script-font" style="font-size: 16pt; color: #1a1a1a;">{{ $graduation_day }}<sup>{{ $graduation_suffix }}</sup></span>
                        &nbsp;&nbsp;day of&nbsp;&nbsp;
                        <span class="script-font" style="font-size: 16pt; color: #1a1a1a;">{{ $graduation_month }}</span><br>
                        <span style="font-size: 20pt; font-weight: 700; color: #1a1a1a;">{{ $graduation_year }}</span>
                    </td>
                </tr>
            </table>

            <!-- SIGNATURES + SEAL (Crash-Proof Layout) -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                <tr>
                    <!-- LEFT COLUMN (Graduate & Principal) -->
                    <td width="30%" valign="bottom">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr><td style="height: 25px; font-size: 0;">&nbsp;</td></tr>
                            <tr><td style="border-top: 1px solid #333; font-size: 0; line-height: 0;">&nbsp;</td></tr>
                            <tr><td align="center" style="font-size: 8pt; font-weight: 600; color: #333; padding-top: 4px;">Graduate's Signature</td></tr>
                            
                            <tr><td style="height: 15px; font-size: 0;">&nbsp;</td></tr> <!-- Spacing -->
                            
                            <tr><td style="height: 25px; font-size: 0;">&nbsp;</td></tr>
                            <tr><td style="border-top: 1px solid #333; font-size: 0; line-height: 0;">&nbsp;</td></tr>
                            <tr><td align="center" style="font-size: 8pt; font-weight: 600; color: #333; padding-top: 4px;">Principal</td></tr>
                        </table>
                    </td>

                    <!-- CENTER COLUMN (Seal) -->
                    <td width="40%" align="center" valign="bottom">
                        <img src="{{ public_path('assets/images/certificate-seal.png') }}" style="width: 38mm; height: auto;" alt="Seal">
                    </td>

                    <!-- RIGHT COLUMN (NRC & Director) -->
                    <td width="30%" valign="bottom">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <!-- NRC Section (Safely handles empty string variables) -->
                            <tr>
                                <td align="center" valign="bottom" style="height: 25px; font-size: 9pt; font-weight: bold; color: #1a1a1a;">
                                    {!! !empty($nrc_number) ? e($nrc_number) : '&nbsp;' !!}
                                </td>
                            </tr>
                            <tr><td style="border-top: 1px solid #333; font-size: 0; line-height: 0;">&nbsp;</td></tr>
                            <tr><td align="center" style="font-size: 8pt; font-weight: 600; color: #333; padding-top: 4px;">Graduate's I.D. No. (NRC)</td></tr>
                            
                            <tr><td style="height: 15px; font-size: 0;">&nbsp;</td></tr> <!-- Spacing -->
                            
                            <!-- Director Section -->
                            <tr><td style="height: 25px; font-size: 0;">&nbsp;</td></tr>
                            <tr><td style="border-top: 1px solid #333; font-size: 0; line-height: 0;">&nbsp;</td></tr>
                            <tr><td align="center" style="font-size: 8pt; font-weight: 600; color: #333; padding-top: 4px;">Director</td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- INFO BOX -->
            <table width="96%" align="center" cellpadding="0" cellspacing="0" style="border: 2px solid #f26522; margin-top: 15px;">
                <tr>
                    <td style="padding: 10px;">
                        
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <!-- Left Info -->
                                <td width="48%">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="30" valign="top"><img src="{{ public_path('assets/images/cert-icons/icon-student.png') }}" style="width: 8mm;" alt="Icon"></td>
                                            <td valign="top" style="padding-left: 5px;">
                                                <span style="font-size: 6pt; font-weight: 800; color: #1e3a8a;">STUDENT NUMBER</span><br>
                                                <span style="font-size: 9pt; font-weight: 700; color: #1a1a1a;">{{ $student_number }}</span>
                                            </td>
                                        </tr>
                                        <tr><td colspan="2" style="height: 8px; font-size: 0;">&nbsp;</td></tr>
                                        <tr>
                                            <td width="30" valign="top"><img src="{{ public_path('assets/images/cert-icons/icon-cert.png') }}" style="width: 8mm;" alt="Icon"></td>
                                            <td valign="top" style="padding-left: 5px;">
                                                <span style="font-size: 6pt; font-weight: 800; color: #1e3a8a;">CERTIFICATE NUMBER</span><br>
                                                <span style="font-size: 9pt; font-weight: 700; color: #1a1a1a;">{{ $certificate_number }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                
                                <!-- Center Divider -->
                                <td width="4%" align="center" valign="middle">
                                    <div style="color: #f26522; font-size: 8pt;">&#9670;</div>
                                </td>
                                
                                <!-- Right Info -->
                                <td width="48%">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="30" valign="top"><img src="{{ public_path('assets/images/cert-icons/icon-date.png') }}" style="width: 8mm;" alt="Icon"></td>
                                            <td valign="top" style="padding-left: 5px;">
                                                <span style="font-size: 6pt; font-weight: 800; color: #1e3a8a;">DATE OF GRADUATION</span><br>
                                                <span style="font-size: 9pt; font-weight: 700; color: #1a1a1a;">{{ $graduation_day }}{{ $graduation_suffix }} {{ $graduation_month }} {{ $graduation_year }}</span>
                                            </td>
                                        </tr>
                                        <tr><td colspan="2" style="height: 8px; font-size: 0;">&nbsp;</td></tr>
                                        <tr>
                                            <td width="30" valign="top"><img src="{{ public_path('assets/images/cert-icons/icon-course.png') }}" style="width: 8mm;" alt="Icon"></td>
                                            <td valign="top" style="padding-left: 5px;">
                                                <span style="font-size: 6pt; font-weight: 800; color: #1e3a8a;">COURSE</span><br>
                                                <span style="font-size: 9pt; font-weight: 700; color: #1a1a1a;">{{ $course_title }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

            <!-- FOOTER -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 15px;">
                <tr>
                    <td align="center" style="font-size: 6.5pt; color: #666;">
                        Verification Code: <strong>{{ $verification_code }}</strong> | Verify at {{ config('app.url') }}/certificates/verify
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

</body>
</html>