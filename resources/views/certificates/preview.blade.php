<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate_number ?? 'Preview' }} - EduTrack Computer Training College</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@400;600;700;800&family=Open+Sans:wght@400;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #f5f5f5;
            font-family: 'Open Sans', sans-serif;
            position: relative;
            overflow-x: hidden;
            padding: 20px;
        }

        @media print {
            html, body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            body {
                background: #fff;
                padding: 0;
                width: 210mm;
                height: 297mm;
            }
            .cert-page {
                box-shadow: none !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }

        .cert-page {
            width: 210mm;
            height: 297mm;
            background-color: #fff;
            background-image: url("{{ asset('assets/images/cert-watermark.png') }}");
            background-repeat: repeat;
            background-size: 90mm auto;
            position: relative;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            overflow: hidden;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Outer orange border */
        .outer-border {
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 3px solid #f26522;
        }

        /* Inner blue border */
        .inner-border {
            position: absolute;
            top: 10mm;
            left: 10mm;
            right: 10mm;
            bottom: 10mm;
            border: 4px solid #1e3a8a;
        }

        /* Corner decorations */
        .corner {
            position: absolute;
            width: 35mm;
            height: 35mm;
            z-index: 5;
        }

        .corner-tl {
            top: 10mm;
            left: 10mm;
            border-top: 3px solid #f26522;
            border-left: 3px solid #f26522;
            background: linear-gradient(135deg, rgba(242,101,34,0.15) 0%, transparent 60%);
        }
        .corner-tl::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            border-left: 25mm solid #f26522;
            border-bottom: 25mm solid transparent;
        }

        .corner-tr {
            top: 10mm;
            right: 10mm;
            border-top: 3px solid #f26522;
            border-right: 3px solid #f26522;
            background: linear-gradient(-135deg, rgba(242,101,34,0.15) 0%, transparent 60%);
        }
        .corner-tr::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            border-right: 25mm solid #f26522;
            border-bottom: 25mm solid transparent;
        }

        .corner-bl {
            bottom: 10mm;
            left: 10mm;
            border-bottom: 3px solid #f26522;
            border-left: 3px solid #f26522;
            background: linear-gradient(45deg, rgba(242,101,34,0.15) 0%, transparent 60%);
        }
        .corner-bl::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            border-left: 25mm solid #f26522;
            border-top: 25mm solid transparent;
        }

        .corner-br {
            bottom: 10mm;
            right: 10mm;
            border-bottom: 3px solid #f26522;
            border-right: 3px solid #f26522;
            background: linear-gradient(-45deg, rgba(242,101,34,0.15) 0%, transparent 60%);
        }
        .corner-br::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            border-right: 25mm solid #f26522;
            border-top: 25mm solid transparent;
        }

        /* Main content area */
        .content {
            position: absolute;
            top: 18mm;
            left: 22mm;
            right: 22mm;
            bottom: 18mm;
            text-align: center;
            z-index: 10;
        }

        /* Header logos area */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4mm;
            position: relative;
        }

        .logo-left, .logo-right {
            width: 28mm;
            height: 28mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* EduTrack Logo (left) */
        .logo-left .shield {
            width: 22mm;
            height: 26mm;
            background: #1e3a8a;
            border-radius: 0 0 11mm 11mm;
            position: relative;
            border: 2px solid #f26522;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .logo-left .shield::before {
            content: '';
            position: absolute;
            top: -6mm;
            left: 50%;
            transform: translateX(-50%);
            width: 18mm;
            height: 8mm;
            background: #f26522;
            border-radius: 9mm 9mm 0 0;
        }
        .logo-left .shield-text {
            color: white;
            font-size: 7pt;
            font-weight: 700;
            line-height: 1.1;
            margin-top: 2mm;
            z-index: 2;
        }
        .logo-left .shield-sub {
            color: #f26522;
            font-size: 5pt;
            margin-top: 1mm;
            z-index: 2;
        }
        .logo-left .tagline {
            position: absolute;
            bottom: -4mm;
            font-size: 5pt;
            color: #1e3a8a;
            font-weight: 600;
            width: 100%;
            text-align: center;
        }

        /* TEVETA Logo (right) */
        .logo-right .teveta {
            text-align: center;
        }
        .logo-right .teveta-icon {
            width: 12mm;
            height: 12mm;
            background: #1e3a8a;
            border-radius: 50%;
            margin: 0 auto 1mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14pt;
            font-weight: 800;
            position: relative;
        }
        .logo-right .teveta-icon::after {
            content: '';
            position: absolute;
            top: -2mm;
            right: -2mm;
            width: 4mm;
            height: 4mm;
            background: #f26522;
            border-radius: 50%;
        }
        .logo-right .teveta-name {
            font-size: 8pt;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            line-height: 1;
        }
        .logo-right .teveta-sub {
            font-size: 5pt;
            color: #1e3a8a;
            font-weight: 600;
        }

        /* College Title */
        .college-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 22pt;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.2;
            margin-top: 2mm;
        }
        .college-subtitle {
            font-family: 'Playfair Display', serif;
            font-size: 12pt;
            color: #444;
            font-style: italic;
            margin-top: 2mm;
        }

        /* Decorative divider */
        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4mm 0;
            gap: 3mm;
        }
        .divider-line {
            width: 40mm;
            height: 1px;
            background: #1e3a8a;
        }
        .divider-diamond {
            width: 3mm;
            height: 3mm;
            background: #f26522;
            transform: rotate(45deg);
        }

        /* Certify text */
        .certify-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 16pt;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 6mm 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4mm;
        }
        .certify-text .side-decor {
            display: flex;
            align-items: center;
            gap: 2mm;
        }
        .certify-text .dot {
            width: 1.5mm;
            height: 1.5mm;
            background: #f26522;
            transform: rotate(45deg);
        }

        /* Recipient Name */
        .recipient-name {
            font-family: 'Great Vibes', cursive;
            font-size: 42pt;
            color: #1a1a1a;
            margin: 4mm 0;
            line-height: 1.2;
        }
        .name-underline {
            width: 80mm;
            height: 1px;
            background: #f26522;
            margin: 2mm auto;
        }

        /* Body text */
        .body-text {
            font-size: 12pt;
            color: #333;
            line-height: 1.6;
            margin: 4mm 0;
        }
        .body-text .highlight {
            font-weight: 600;
        }

        /* Course Title */
        .course-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 26pt;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 5mm 0;
            line-height: 1.2;
        }

        /* Merit text */
        .merit-text {
            font-family: 'Great Vibes', cursive;
            font-size: 28pt;
            color: #1a1a1a;
            margin: 3mm 0;
        }
        .merit-underline {
            width: 50mm;
            height: 1px;
            background: #f26522;
            margin: 2mm auto;
        }

        /* Date section */
        .date-section {
            font-size: 12pt;
            color: #333;
            line-height: 1.8;
            margin: 5mm 0;
        }
        .date-section .date-day {
            font-family: 'Great Vibes', cursive;
            font-size: 18pt;
            color: #1a1a1a;
        }
        .date-section .date-month {
            font-family: 'Great Vibes', cursive;
            font-size: 18pt;
            color: #1a1a1a;
        }
        .date-section .date-year {
            font-family: 'Montserrat', sans-serif;
            font-size: 18pt;
            font-weight: 700;
            color: #1a1a1a;
        }

        /* Signatures area */
        .signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin: 8mm 10mm 0;
            position: relative;
        }
        .signature-block {
            text-align: center;
            width: 45mm;
        }
        .signature-line {
            width: 100%;
            height: 1px;
            background: #333;
            margin-bottom: 2mm;
        }
        .signature-label {
            font-size: 9pt;
            color: #333;
            font-weight: 600;
        }

        /* Center seal */
        .seal-container {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 5mm;
            width: 35mm;
            height: 45mm;
        }
        .seal {
            width: 30mm;
            height: 30mm;
            background: #1e3a8a;
            border-radius: 50%;
            border: 2px solid #d4af37;
            position: relative;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .seal::before {
            content: '';
            position: absolute;
            inset: 2mm;
            border: 1px solid #d4af37;
            border-radius: 50%;
        }
        .seal-star {
            color: #d4af37;
            font-size: 16pt;
        }
        .seal-ribbon {
            position: absolute;
            bottom: -8mm;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 8mm solid transparent;
            border-right: 8mm solid transparent;
            border-top: 12mm solid #f26522;
        }
        .seal-ribbon::after {
            content: '';
            position: absolute;
            top: -12mm;
            left: -4mm;
            width: 0;
            height: 0;
            border-left: 4mm solid transparent;
            border-right: 4mm solid transparent;
            border-top: 8mm solid #d35400;
        }

        /* Bottom info box */
        .info-box {
            position: absolute;
            bottom: 22mm;
            left: 22mm;
            right: 22mm;
            border: 2px solid #f26522;
            border-radius: 8px;
            padding: 4mm 6mm;
            display: grid;
            grid-template-columns: 1fr 1px 1fr;
            gap: 4mm;
            background: rgba(255,255,255,0.9);
        }
        .info-divider {
            width: 1px;
            background: #f26522;
            height: 100%;
        }
        .info-col {
            display: flex;
            flex-direction: column;
            gap: 3mm;
        }
        .info-row {
            display: flex;
            align-items: center;
            gap: 3mm;
        }
        .info-icon {
            width: 8mm;
            height: 8mm;
            min-width: 8mm;
            border: 1.5px solid #1e3a8a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e3a8a;
            font-size: 10pt;
        }
        .info-text {
            text-align: left;
        }
        .info-label {
            font-size: 7pt;
            color: #1e3a8a;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 10pt;
            color: #1a1a1a;
            font-weight: 700;
        }

        /* Bottom center decoration */
        .bottom-decor {
            position: absolute;
            bottom: 14mm;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 2mm;
        }
        .bottom-decor .dot {
            width: 1.5mm;
            height: 1.5mm;
            background: #f26522;
            transform: rotate(45deg);
        }
        .bottom-decor .line {
            width: 15mm;
            height: 1px;
            background: #1e3a8a;
        }

        /* Action bar */
        .action-bar {
            text-align: center;
            padding: 16px;
            margin-top: 20px;
        }
    </style>
    <base target="_blank">
</head>
<body>

    <!-- Action Bar -->
    <div class="action-bar no-print">
        <a href="{{ route('student.certificates') }}" class="od-btn od-btn-secondary od-btn-sm" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;border:1px solid var(--od-border);color:var(--od-fg);text-decoration:none;font-size:13px;font-weight:500;">
            <i class="fas fa-arrow-left"></i> Back to Certificates
        </a>
        @if(isset($certificate) && $certificate)
        <a href="{{ route('certificates.download', $certificate) }}" class="od-btn od-btn-primary od-btn-sm" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;border:1px solid var(--od-accent);background:var(--od-accent);color:var(--od-surface);text-decoration:none;font-size:13px;font-weight:500;margin-left:8px;">
            <i class="fas fa-download"></i> Download PDF
        </a>
        @endif
        <button onclick="window.print()" class="od-btn od-btn-ghost od-btn-sm" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;border:1px solid transparent;background:transparent;color:var(--od-muted);text-decoration:none;font-size:13px;font-weight:500;margin-left:8px;cursor:pointer;">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

    <div class="cert-page">
        <div class="outer-border"></div>
        <div class="inner-border"></div>

        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        <div class="content">
            <!-- Header with Logos -->
            <div class="header">
                <div class="logo-left">
                    <div style="position: relative;">
                        <div class="shield">
                            <div class="shield-text">EduTrack</div>
                            <div class="shield-sub">Excel Through<br>Education</div>
                        </div>
                        <div class="tagline">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
                    </div>
                </div>

                <div style="flex: 1;">
                    <div class="college-title">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
                    <div class="divider">
                        <div class="divider-line"></div>
                        <div class="divider-diamond"></div>
                        <div class="divider-line"></div>
                    </div>
                    <div class="college-subtitle">A skill training college</div>
                </div>

                <div class="logo-right">
                    <div class="teveta">
                        <div class="teveta-icon">T</div>
                        <div class="teveta-name">TEVETA</div>
                        <div class="teveta-sub">ACCREDITED</div>
                    </div>
                </div>
            </div>

            <!-- Certify Statement -->
            <div class="certify-text">
                <div class="side-decor">
                    <div class="dot"></div>
                    <div class="dot" style="width: 1mm; height: 1mm;"></div>
                    <div class="dot"></div>
                </div>
                THIS IS TO CERTIFY THAT
                <div class="side-decor">
                    <div class="dot"></div>
                    <div class="dot" style="width: 1mm; height: 1mm;"></div>
                    <div class="dot"></div>
                </div>
            </div>

            <!-- Recipient -->
            <div class="recipient-name">{{ $student_name ?? 'Student Name' }}</div>
            <div class="name-underline"></div>

            <!-- Body -->
            <div class="body-text">
                having satisfied the requirements for the<br>
                award of the certificate of
            </div>

            <!-- Course -->
            <div class="course-title">{{ strtoupper($course_title ?? 'Course Title') }}</div>

            <!-- Merit -->
            @if(isset($classification) && $classification && $classification !== 'Pass')
            <div class="merit-text">With {{ $classification }}</div>
            <div class="merit-underline"></div>
            @endif

            <!-- Date -->
            <div class="date-section">
                was admitted to the certificate at a Graduation<br>
                Ceremony held on the <span class="date-day">{{ $graduation_day ?? '1' }}<sup>{{ $graduation_suffix ?? 'st' }}</sup></span> day of <span class="date-month">{{ $graduation_month ?? 'January' }}</span><br>
                in the year <span class="date-year">{{ $graduation_year ?? date('Y') }}</span>
            </div>

            <!-- Signatures -->
            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Principal</div>
                    <div style="margin-top: 6mm;">
                        <div class="signature-line"></div>
                        <div class="signature-label">Graduate's Signature</div>
                    </div>
                </div>

                <div class="seal-container">
                    <div class="seal">
                        <div style="color: #d4af37; font-size: 8pt; text-align: center; line-height: 1.2;">
                            &#9733;<br>
                            <span style="font-size: 6pt;">EXCELLENCE</span>
                        </div>
                    </div>
                    <div class="seal-ribbon"></div>
                </div>

                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Director</div>
                    <div style="margin-top: 6mm;">
                        <div class="signature-line"></div>
                        <div class="signature-label">Graduate's I.D. No.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Info Box -->
        <div class="info-box">
            <div class="info-col">
                <div class="info-row">
                    <div class="info-icon">&#127891;</div>
                    <div class="info-text">
                        <div class="info-label">Student Number</div>
                        <div class="info-value">{{ $student_number ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">&#128196;</div>
                    <div class="info-text">
                        <div class="info-label">Certificate Number</div>
                        <div class="info-value">{{ $certificate_number ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <div class="info-divider"></div>

            <div class="info-col">
                <div class="info-row">
                    <div class="info-icon">&#128197;</div>
                    <div class="info-text">
                        <div class="info-label">Date of Graduation</div>
                        <div class="info-value">{{ ($graduation_day ?? '1') . ($graduation_suffix ?? 'st') . ' ' . ($graduation_month ?? 'January') . ' ' . ($graduation_year ?? date('Y')) }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">&#127942;</div>
                    <div class="info-text">
                        <div class="info-label">Course</div>
                        <div class="info-value">{{ $course_title ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-decor">
            <div class="line"></div>
            <div class="dot"></div>
            <div class="dot" style="width: 2mm; height: 2mm;"></div>
            <div class="dot"></div>
            <div class="line"></div>
        </div>
    </div>
</body>
</html>
