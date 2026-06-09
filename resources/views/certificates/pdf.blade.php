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
<td valign="top" style="padding: 5.3mm;">

    <!-- Navy Inner Border -->
    <table width="100%" cellpadding="0" cellspacing="0" style="height: 100%; background-color: #1B3A6B;">
    <tr>
    <td valign="top" style="padding: 3.5mm;">

        <!-- White Content Area -->
        <table width="100%" cellpadding="0" cellspacing="0" style="height: 100%; background-color: #ffffff;">
        <tr>
        <td valign="top" style="padding: 8mm 14mm 6mm 14mm;">

            <!-- HEADER / LOGOS -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 2mm;">
                <tr>
                    <td width="25%" align="center" valign="middle">
                        <img src="{{ public_path('assets/images/logo-pdf.png') }}" style="height: 24mm; width: auto;" alt="Logo">
                    </td>
                    <td width="50%">&nbsp;</td>
                    <td width="25%" align="center" valign="middle">
                        <img src="{{ public_path('assets/images/teveta-logo.png') }}" style="height: 16mm; width: auto;" alt="Teveta">
                    </td>
                </tr>
            </table>

            <!-- INSTITUTION NAME -->
            <div style="text-align: center; font-family: 'DejaVu Serif', Georgia, serif; font-size: 18pt; font-weight: bold; color: #0F2B52; text-transform: uppercase; letter-spacing: 2px; line-height: 1.25;">
                EDUTRACK COMPUTER<br>TRAINING COLLEGE
            </div>
            <div style="text-align: center; font-family: 'DejaVu Serif', serif; font-size: 10pt; font-style: italic; color: #0A1628; margin-top: 1mm;">
                A skill training college
            </div>

            <!-- DECO RULE -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 3mm 0;">
                <tr>
                    <td width="28%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                    <td width="6%" align="center" style="color: #E8B84A; font-size: 6pt;">&#9670;</td>
                    <td width="8%" align="center" style="color: #D4952A; font-size: 8pt;">&#9670;</td>
                    <td width="6%" align="center" style="color: #E8B84A; font-size: 6pt;">&#9670;</td>
                    <td width="28%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                </tr>
            </table>

            <!-- CERTIFY TEXT -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 2mm 0;">
                <tr>
                    <td width="20%" align="right" style="color: #E8B84A; font-size: 6pt; padding-right: 2mm;">&#9670;</td>
                    <td width="5%" align="right" style="color: #D4952A; font-size: 8pt;">&#9670;</td>
                    <td width="50%" align="center" style="font-size: 14pt; font-weight: bold; color: #1B3A6B; text-transform: uppercase; letter-spacing: 3px;">
                        THIS IS TO CERTIFY THAT
                    </td>
                    <td width="5%" align="left" style="color: #D4952A; font-size: 8pt;">&#9670;</td>
                    <td width="20%" align="left" style="color: #E8B84A; font-size: 6pt; padding-left: 2mm;">&#9670;</td>
                </tr>
            </table>

            <!-- DECO RULE -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 2mm 0 3mm;">
                <tr>
                    <td width="28%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                    <td width="6%" align="center" style="color: #E8B84A; font-size: 6pt;">&#9670;</td>
                    <td width="8%" align="center" style="color: #D4952A; font-size: 8pt;">&#9670;</td>
                    <td width="6%" align="center" style="color: #E8B84A; font-size: 6pt;">&#9670;</td>
                    <td width="28%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                </tr>
            </table>

            <!-- RECIPIENT NAME -->
            <div class="script-font" style="text-align: center; font-size: 36pt; color: #0A1628; line-height: 1.05;">
                {{ $student_name }}
            </div>

            <!-- BODY TEXT -->
            <div style="text-align: center; font-size: 10pt; font-style: italic; color: #0A1628; line-height: 1.5; margin: 3mm 0;">
                having satisfied the requirements for the award of<br>the certificate of
            </div>

            <!-- COURSE TITLE -->
            <div style="text-align: center; font-size: 24pt; font-weight: 900; color: #1B3A6B; text-transform: uppercase; letter-spacing: 3px; line-height: 1.15; margin: 3mm 0;">
                {{ $course_title }}
            </div>

            <!-- MERIT -->
            @if(isset($classification) && $classification && $classification !== 'Pass')
            <div class="script-font" style="text-align: center; font-size: 28pt; color: #0A1628; line-height: 1.05; margin: 2mm 0;">
                With {{ $classification }}
            </div>
            @endif

            <!-- DECO RULE -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 3mm 0;">
                <tr>
                    <td width="28%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                    <td width="6%" align="center" style="color: #E8B84A; font-size: 6pt;">&#9670;</td>
                    <td width="8%" align="center" style="color: #D4952A; font-size: 8pt;">&#9670;</td>
                    <td width="6%" align="center" style="color: #E8B84A; font-size: 6pt;">&#9670;</td>
                    <td width="28%" style="border-top: 1.5px solid #D4952A; line-height: 1px;">&nbsp;</td>
                </tr>
            </table>

            <!-- DATE -->
            <div style="text-align: center; font-size: 10pt; font-style: italic; color: #0A1628; line-height: 1.5; margin: 3mm 0;">
                Was admitted to the certificate at a Graduation Ceremony held<br>
                on the <span class="script-font" style="font-size: 14pt;">{{ $graduation_day }}<sup>{{ $graduation_suffix }}</sup></span> day of <span class="script-font" style="font-size: 14pt;">{{ $graduation_month }}</span><br>
                in the year <span style="font-weight: bold; font-style: normal;">{{ $graduation_year }}</span>
            </div>

            <!-- SIGNATURES -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 8mm 0 4mm;">
                <tr>
                    <td width="42%" align="center">
                        <div style="border-top: 1.5px solid #0A1628; width: 85%; margin: 0 auto; line-height: 1px;">&nbsp;</div>
                        <div style="font-size: 9pt; color: #0A1628; font-weight: bold; padding-top: 1.5mm; text-align: center;">Principal</div>
                    </td>
                    <td width="16%">&nbsp;</td>
                    <td width="42%" align="center">
                        <div style="border-top: 1.5px solid #0A1628; width: 85%; margin: 0 auto; line-height: 1px;">&nbsp;</div>
                        <div style="font-size: 9pt; color: #0A1628; font-weight: bold; padding-top: 1.5mm; text-align: center;">Director</div>
                    </td>
                </tr>
            </table>

            <!-- BOTTOM ROW -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 5mm;">
                <tr>
                    <td width="50%" align="left">
                        <div style="font-size: 8pt; color: #0A1628; font-weight: bold; margin-bottom: 1.5mm;">Graduate's Signature</div>
                        <div style="font-family: 'Courier New', Courier, monospace; font-size: 10pt; color: #0A1628; font-weight: bold;">{{ $certificate_number }}</div>
                    </td>
                    <td width="50%" align="right">
                        <div style="font-size: 8pt; color: #0A1628; font-weight: bold; margin-bottom: 1.5mm;">Graduate's I.D. No.</div>
                        <div style="font-family: 'Courier New', Courier, monospace; font-size: 10pt; color: #0A1628; font-weight: bold;">{{ $student_number }}</div>
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
