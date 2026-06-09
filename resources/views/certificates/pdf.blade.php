<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    font-family: 'DejaVu Serif', Georgia, 'Times New Roman', serif;
    color: #0A1628;
}
.script-font { font-family: 'greatvibes', 'DejaVu Serif', serif; }
</style>
</head>
<body>

<!-- Outer Gold Border -->
<table width="100%" cellpadding="0" cellspacing="0" style="width: 210mm; height: 297mm; background-color: #D4952A;">
<tr>
<td valign="top" style="padding: 1.5mm;">

    <!-- Navy Inner Border -->
    <table width="100%" cellpadding="0" cellspacing="0" style="height: 100%; background-color: #1B3A6B;">
    <tr>
    <td valign="top" style="padding: 1mm;">

        <!-- White Content Area -->
        <table width="100%" cellpadding="0" cellspacing="0" style="height: 100%; background-color: #ffffff;">
        <tr>
        <td valign="top" align="center" style="padding: 5mm 10mm 6mm 10mm;">

            <!-- Main content table with spacer -->
            <table width="100%" cellpadding="0" cellspacing="0" style="height: 100%;" align="center">

            <!-- Top content -->
            <tr><td valign="top" align="center">

                <!-- HEADER / LOGOS -->
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="22%" align="center" valign="middle">
                            <img src="{{ public_path('assets/images/logo-pdf.png') }}" style="height: 14mm; width: auto;" alt="Logo">
                        </td>
                        <td width="56%">&nbsp;</td>
                        <td width="22%" align="center" valign="middle">
                            <img src="{{ public_path('assets/images/teveta-logo.png') }}" style="height: 10mm; width: auto;" alt="Teveta">
                        </td>
                    </tr>
                </table>

                <!-- INSTITUTION NAME -->
                <div style="text-align: center; font-family: 'DejaVu Serif', Georgia, serif; font-size: 13pt; font-weight: bold; color: #0F2B52; text-transform: uppercase; letter-spacing: 2px; line-height: 1.25;">
                    EDUTRACK COMPUTER<br>TRAINING COLLEGE
                </div>
                <div style="text-align: center; font-family: 'DejaVu Serif', serif; font-size: 8pt; font-style: italic; color: #0A1628;">
                    A skill training college
                </div>

                <!-- DECO RULE -->
                <table width="80mm" align="center" cellpadding="0" cellspacing="0" style="margin: 1mm auto;">
                    <tr>
                        <td width="40%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                        <td width="5%" align="center" style="color: #E8B84A; font-size: 5pt;">&#9670;</td>
                        <td width="10%" align="center" style="color: #D4952A; font-size: 7pt;">&#9670;</td>
                        <td width="5%" align="center" style="color: #E8B84A; font-size: 5pt;">&#9670;</td>
                        <td width="40%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                    </tr>
                </table>

                <!-- CERTIFY TEXT -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin: 1mm 0;">
                    <tr>
                        <td width="18%" align="right" style="color: #E8B84A; font-size: 5pt; padding-right: 1mm;">&#9670;</td>
                        <td width="4%" align="right" style="color: #D4952A; font-size: 7pt;">&#9670;</td>
                        <td width="56%" align="center" style="font-size: 12pt; font-weight: bold; color: #1B3A6B; text-transform: uppercase; letter-spacing: 2px;">
                            THIS IS TO CERTIFY THAT
                        </td>
                        <td width="4%" align="left" style="color: #D4952A; font-size: 7pt;">&#9670;</td>
                        <td width="18%" align="left" style="color: #E8B84A; font-size: 5pt; padding-left: 1mm;">&#9670;</td>
                    </tr>
                </table>

                <!-- DECO RULE -->
                <table width="80mm" align="center" cellpadding="0" cellspacing="0" style="margin: 1mm auto;">
                    <tr>
                        <td width="40%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                        <td width="5%" align="center" style="color: #E8B84A; font-size: 5pt;">&#9670;</td>
                        <td width="10%" align="center" style="color: #D4952A; font-size: 7pt;">&#9670;</td>
                        <td width="5%" align="center" style="color: #E8B84A; font-size: 5pt;">&#9670;</td>
                        <td width="40%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                    </tr>
                </table>

                <!-- RECIPIENT NAME -->
                <div class="script-font" style="text-align: center; font-size: 24pt; color: #0A1628; line-height: 1.05; margin: 1mm 0;">
                    {{ $student_name }}
                </div>

                <!-- BODY TEXT -->
                <div style="text-align: center; font-size: 8.5pt; font-style: italic; color: #0A1628; line-height: 1.4; margin: 1mm 0;">
                    having satisfied the requirements for the award of<br>the certificate of
                </div>

                <!-- COURSE TITLE -->
                <div style="text-align: center; font-size: 16pt; font-weight: 900; color: #1B3A6B; text-transform: uppercase; letter-spacing: 2px; line-height: 1.15; margin: 1mm 0;">
                    {{ $course_title }}
                </div>

                <!-- MERIT -->
                @if(isset($classification) && $classification && $classification !== 'Pass')
                <div class="script-font" style="text-align: center; font-size: 18pt; color: #0A1628; line-height: 1.05; margin: 1mm 0;">
                    With {{ $classification }}
                </div>
                @endif

                <!-- DECO RULE -->
                <table width="80mm" align="center" cellpadding="0" cellspacing="0" style="margin: 1mm auto;">
                    <tr>
                        <td width="40%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                        <td width="5%" align="center" style="color: #E8B84A; font-size: 5pt;">&#9670;</td>
                        <td width="10%" align="center" style="color: #D4952A; font-size: 7pt;">&#9670;</td>
                        <td width="5%" align="center" style="color: #E8B84A; font-size: 5pt;">&#9670;</td>
                        <td width="40%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                    </tr>
                </table>

                <!-- DATE -->
                <div style="text-align: center; font-size: 8.5pt; font-style: italic; color: #0A1628; line-height: 1.4; margin: 1mm 0;">
                    Was admitted to the certificate at a Graduation Ceremony held<br>
                    on the <span class="script-font" style="font-size: 11pt;">{{ $graduation_day }}<sup>{{ $graduation_suffix }}</sup></span> day of <span class="script-font" style="font-size: 11pt;">{{ $graduation_month }}</span><br>
                    in the year <span style="font-weight: bold; font-style: normal;">{{ $graduation_year }}</span>
                </div>

            </td></tr>

            <!-- Spacer row to push signatures down -->
            <tr><td valign="top" style="height: 100%;">&nbsp;</td></tr>

            <!-- Bottom content -->
            <tr><td valign="bottom" align="center">

                <!-- SIGNATURES -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin: 2mm 0 1mm;">
                    <tr>
                        <td width="42%" align="center">
                            <table width="70%" cellpadding="0" cellspacing="0">
                                <tr><td style="border-top: 1.5px solid #0A1628; line-height: 1px;">&nbsp;</td></tr>
                                <tr><td align="center" style="font-size: 8pt; color: #0A1628; font-weight: bold; padding-top: 1mm;">Principal</td></tr>
                            </table>
                        </td>
                        <td width="16%">&nbsp;</td>
                        <td width="42%" align="center">
                            <table width="70%" cellpadding="0" cellspacing="0">
                                <tr><td style="border-top: 1.5px solid #0A1628; line-height: 1px;">&nbsp;</td></tr>
                                <tr><td align="center" style="font-size: 8pt; color: #0A1628; font-weight: bold; padding-top: 1mm;">Director</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- BOTTOM ROW -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 2mm;">
                    <tr>
                        <td width="50%" align="left">
                            <div style="font-size: 7.5pt; color: #0A1628; font-weight: bold; margin-bottom: 1mm;">Graduate's Signature</div>
                            <div style="font-family: 'Courier New', Courier, monospace; font-size: 9pt; color: #0A1628; font-weight: bold;">{{ $certificate_number }}</div>
                        </td>
                        <td width="50%" align="right">
                            <div style="font-size: 7.5pt; color: #0A1628; font-weight: bold; margin-bottom: 1mm;">Graduate's I.D. No.</div>
                            <div style="font-family: 'Courier New', Courier, monospace; font-size: 9pt; color: #0A1628; font-weight: bold;">{{ $student_number }}</div>
                        </td>
                    </tr>
                </table>

            </td></tr>
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
