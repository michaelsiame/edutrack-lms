<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: 297mm 210mm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Serif', Georgia, 'Times New Roman', serif;
        }
        .page {
            width: 297mm;
            height: 210mm;
            position: relative;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0" border="0" style="width:297mm; height:210mm; background-color:#FFFFFF;">
    <tr>
        <td style="padding:8mm;">
            <!-- Outer Orange Border -->
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; height:100%; border:4px solid #F26522;">
                <tr>
                    <td style="padding:2mm;">
                        <!-- Inner Blue Border -->
                        <table cellpadding="0" cellspacing="0" border="0" style="width:100%; height:100%; border:2px solid #1E3A8A;">
                            <tr>
                                <td style="padding:6mm; position:relative;">
                                    <!-- Corner Decorations -->
                                    <div style="position:absolute; top:0; left:0; width:0; height:0; border-left:25px solid #F26522; border-bottom:25px solid transparent;"></div>
                                    <div style="position:absolute; top:0; right:0; width:0; height:0; border-right:25px solid #F26522; border-bottom:25px solid transparent;"></div>
                                    <div style="position:absolute; bottom:0; left:0; width:0; height:0; border-left:25px solid #F26522; border-top:25px solid transparent;"></div>
                                    <div style="position:absolute; bottom:0; right:0; width:0; height:0; border-right:25px solid #F26522; border-top:25px solid transparent;"></div>

                                    <!-- Header Row: Logo + Title + TEVETA Logo -->
                                    <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:4mm;">
                                        <tr>
                                            <td style="width:20%; text-align:center; vertical-align:middle;">
                                                @if(file_exists(public_path('assets/images/logo-sm.png')))
                                                    <img src="{{ public_path('assets/images/logo-sm.png') }}" style="height:55px;" />
                                                @else
                                                    <div style="font-size:10px; color:#1E3A8A; font-weight:bold;">EduTrack</div>
                                                @endif
                                            </td>
                                            <td style="width:60%; text-align:center; vertical-align:middle;">
                                                <div style="font-size:22px; font-weight:bold; color:#1E3A8A; letter-spacing:2px; line-height:1.2;">EDUTRACK COMPUTER</div>
                                                <div style="font-size:22px; font-weight:bold; color:#1E3A8A; letter-spacing:2px; line-height:1.2;">TRAINING COLLEGE</div>
                                                <table cellpadding="0" cellspacing="0" border="0" style="width:60%; margin:2mm auto;">
                                                    <tr>
                                                        <td style="border-bottom:1px solid #F26522; width:40%;"></td>
                                                        <td style="width:20%; text-align:center; color:#F26522; font-size:8px;">&#9670; &#9670; &#9670;</td>
                                                        <td style="border-bottom:1px solid #F26522; width:40%;"></td>
                                                    </tr>
                                                </table>
                                                <div style="font-size:11px; color:#555; font-style:italic;">A skill training college</div>
                                            </td>
                                            <td style="width:20%; text-align:center; vertical-align:middle;">
                                                @if(file_exists(public_path('assets/images/teveta-logo-sm.png')))
                                                    <img src="{{ public_path('assets/images/teveta-logo-sm.png') }}" style="height:50px;" />
                                                @else
                                                    <div style="font-size:9px; color:#1E3A8A; font-weight:bold;">TEVETA</div>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Certification Text -->
                                    <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-top:5mm; margin-bottom:3mm;">
                                        <tr>
                                            <td style="width:25%; text-align:right; vertical-align:middle;">
                                                <div style="border-bottom:1px solid #F26522; width:80%; display:inline-block;"></div>
                                            </td>
                                            <td style="width:50%; text-align:center; vertical-align:middle;">
                                                <div style="font-size:16px; font-weight:bold; color:#1E3A8A; letter-spacing:3px;">THIS IS TO CERTIFY THAT</div>
                                            </td>
                                            <td style="width:25%; text-align:left; vertical-align:middle;">
                                                <div style="border-bottom:1px solid #F26522; width:80%; display:inline-block;"></div>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Student Name -->
                                    <div style="text-align:center; margin:4mm 0;">
                                        <div style="font-size:32px; font-family:'DejaVu Serif', Georgia, serif; font-style:italic; color:#000; border-bottom:2px solid #F26522; display:inline-block; padding:0 20mm 2mm 20mm;">
                                            {{ $student_name }}
                                        </div>
                                    </div>

                                    <!-- Body Text -->
                                    <div style="text-align:center; font-size:11px; color:#333; line-height:1.6; margin:3mm 0;">
                                        having satisfied the requirements for the<br>
                                        award of the certificate of
                                    </div>

                                    <!-- Course Title -->
                                    <div style="text-align:center; margin:3mm 0;">
                                        <div style="font-size:24px; font-weight:bold; color:#1E3A8A; text-transform:uppercase; letter-spacing:2px;">
                                            {{ strtoupper($course_title) }}
                                        </div>
                                    </div>

                                    <!-- Classification -->
                                    @if($classification)
                                    <div style="text-align:center; margin:2mm 0;">
                                        <div style="font-size:20px; font-family:'DejaVu Serif', Georgia, serif; font-style:italic; color:#000;">
                                            With {{ $classification }}
                                        </div>
                                        <table cellpadding="0" cellspacing="0" border="0" style="width:30%; margin:2mm auto;">
                                            <tr>
                                                <td style="border-bottom:1px solid #F26522; width:40%;"></td>
                                                <td style="width:20%; text-align:center; color:#F26522; font-size:6px;">&#9670;</td>
                                                <td style="border-bottom:1px solid #F26522; width:40%;"></td>
                                            </tr>
                                        </table>
                                    </div>
                                    @endif

                                    <!-- Date Text -->
                                    <div style="text-align:center; font-size:11px; color:#333; line-height:1.6; margin:3mm 0;">
                                        was admitted to the certificate at a Graduation<br>
                                        Ceremony held on the <span style="font-size:14px; font-weight:bold; color:#1E3A8A;">&nbsp;&nbsp;{{ $graduation_day }}<sup style="font-size:8px;">th</sup>&nbsp;&nbsp;</span> day of <span style="font-size:14px; font-weight:bold; color:#1E3A8A; font-style:italic;">&nbsp;&nbsp;{{ $graduation_month }}&nbsp;&nbsp;</span><br>
                                        in the year <span style="font-size:16px; font-weight:bold; color:#1E3A8A;">&nbsp;&nbsp;{{ $graduation_year }}&nbsp;&nbsp;</span>
                                    </div>

                                    <!-- Signatures and Seal Row -->
                                    <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-top:5mm; margin-bottom:4mm;">
                                        <tr>
                                            <!-- Left Signatures -->
                                            <td style="width:30%; text-align:center; vertical-align:bottom;">
                                                <div style="border-top:1px solid #333; width:70%; margin:0 auto; padding-top:2mm;">
                                                    <div style="font-size:9px; color:#333;">Principal</div>
                                                </div>
                                                <div style="border-top:1px solid #333; width:70%; margin:8mm auto 0 auto; padding-top:2mm;">
                                                    <div style="font-size:9px; color:#333;">Graduate's Signature</div>
                                                </div>
                                            </td>

                                            <!-- Center Seal -->
                                            <td style="width:40%; text-align:center; vertical-align:middle;">
                                                <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                                                    <tr>
                                                        <td style="width:60px; height:60px; background-color:#1E3A8A; border-radius:50%; text-align:center; vertical-align:middle; border:3px solid #C9A227;">
                                                            <div style="color:#C9A227; font-size:24px;">&#9733;</div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align:center;">
                                                            <div style="width:0; height:0; border-left:15px solid transparent; border-right:15px solid transparent; border-top:20px solid #F26522; margin:0 auto;"></div>
                                                            <div style="width:0; height:0; border-left:15px solid transparent; border-right:15px solid transparent; border-top:20px solid #F26522; margin:0 auto;"></div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>

                                            <!-- Right Signatures -->
                                            <td style="width:30%; text-align:center; vertical-align:bottom;">
                                                <div style="border-top:1px solid #333; width:70%; margin:0 auto; padding-top:2mm;">
                                                    <div style="font-size:9px; color:#333;">Director</div>
                                                </div>
                                                <div style="border-top:1px solid #333; width:70%; margin:8mm auto 0 auto; padding-top:2mm;">
                                                    <div style="font-size:9px; color:#333;">Graduate's I.D. No.</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Bottom Info Bar -->
                                    <table cellpadding="0" cellspacing="0" border="0" style="width:95%; margin:4mm auto 0 auto; border:2px solid #F26522; border-radius:8px;">
                                        <tr>
                                            <td style="padding:4mm;">
                                                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                                                    <tr>
                                                        <!-- Left Column -->
                                                        <td style="width:48%; vertical-align:top;">
                                                            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:3mm;">
                                                                <tr>
                                                                    <td style="width:30px; text-align:center; vertical-align:middle;">
                                                                        <div style="width:24px; height:24px; border:2px solid #1E3A8A; border-radius:50%; text-align:center; line-height:20px; color:#1E3A8A; font-size:12px;">&#127891;</div>
                                                                    </td>
                                                                    <td style="padding-left:3mm;">
                                                                        <div style="font-size:8px; color:#1E3A8A; font-weight:bold; text-transform:uppercase;">Student Number</div>
                                                                        <div style="font-size:11px; color:#000; font-weight:bold;">{{ $student_number }}</div>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                                                                <tr>
                                                                    <td style="width:30px; text-align:center; vertical-align:middle;">
                                                                        <div style="width:24px; height:24px; border:2px solid #1E3A8A; border-radius:50%; text-align:center; line-height:20px; color:#1E3A8A; font-size:12px;">&#128196;</div>
                                                                    </td>
                                                                    <td style="padding-left:3mm;">
                                                                        <div style="font-size:8px; color:#1E3A8A; font-weight:bold; text-transform:uppercase;">Certificate Number</div>
                                                                        <div style="font-size:11px; color:#000; font-weight:bold;">{{ $certificate_number }}</div>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>

                                                        <!-- Divider -->
                                                        <td style="width:4%; text-align:center; vertical-align:middle;">
                                                            <div style="border-left:1px solid #F26522; height:80%; margin:0 auto;"></div>
                                                            <div style="color:#F26522; font-size:8px; margin:1mm 0;">&#9670;</div>
                                                        </td>

                                                        <!-- Right Column -->
                                                        <td style="width:48%; vertical-align:top;">
                                                            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom:3mm;">
                                                                <tr>
                                                                    <td style="width:30px; text-align:center; vertical-align:middle;">
                                                                        <div style="width:24px; height:24px; border:2px solid #1E3A8A; border-radius:50%; text-align:center; line-height:20px; color:#1E3A8A; font-size:12px;">&#128197;</div>
                                                                    </td>
                                                                    <td style="padding-left:3mm;">
                                                                        <div style="font-size:8px; color:#1E3A8A; font-weight:bold; text-transform:uppercase;">Date of Graduation</div>
                                                                        <div style="font-size:11px; color:#000; font-weight:bold;">{{ $graduation_day }}<sup>th</sup> {{ $graduation_month }} {{ $graduation_year }}</div>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                                                                <tr>
                                                                    <td style="width:30px; text-align:center; vertical-align:middle;">
                                                                        <div style="width:24px; height:24px; border:2px solid #1E3A8A; border-radius:50%; text-align:center; line-height:20px; color:#1E3A8A; font-size:12px;">&#127941;</div>
                                                                    </td>
                                                                    <td style="padding-left:3mm;">
                                                                        <div style="font-size:8px; color:#1E3A8A; font-weight:bold; text-transform:uppercase;">Course</div>
                                                                        <div style="font-size:11px; color:#000; font-weight:bold;">{{ $course_title }}</div>
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
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
