<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate_number ?? 'Preview' }} - EduTrack Computer Training College</title>
    <link rel="stylesheet" href="{{ asset('assets/css/cert-fonts.css') }}">
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #f5f5f5;
            font-family: 'Montserrat', 'Inter', sans-serif;
            position: relative;
            overflow-x: hidden;
            padding: 20px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                width: 210mm;
                height: 297mm;
            }
            .cert-page {
                box-shadow: none !important;
                margin: 0 !important;
            }
            .no-print { display: none !important; }
        }

        .cert-page {
            width: 210mm;
            height: 297mm;
            background: #fff;
            position: relative;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            overflow: hidden;
        }

        /* === OPEN DESIGN CERTIFICATE SYSTEM === */
        :root {
            --cert-navy: #1e3a8a;
            --cert-navy-light: #2563eb;
            --cert-accent: #f26522;
            --cert-accent-dark: #d35400;
            --cert-gold: #d4af37;
            --cert-gold-light: #f0d878;
            --cert-text: #1a1a1a;
            --cert-text-muted: #444;
            --cert-border: #e5e7eb;
        }

        /* Border Frame */
        .frame-outer {
            position: absolute;
            top: 7mm; left: 7mm; right: 7mm; bottom: 7mm;
            border: 2px solid var(--cert-accent);
            border-radius: 2px;
        }
        .frame-inner {
            position: absolute;
            top: 9mm; left: 9mm; right: 9mm; bottom: 9mm;
            border: 3px solid var(--cert-navy);
            border-radius: 1px;
        }
        .frame-line {
            position: absolute;
            top: 11mm; left: 11mm; right: 11mm; bottom: 11mm;
            border: 1px solid var(--cert-gold);
            opacity: 0.6;
        }

        /* Corner Ornaments */
        .corner {
            position: absolute;
            width: 28mm; height: 28mm;
            z-index: 5;
        }
        .corner-tl {
            top: 11mm; left: 11mm;
            border-top: 2px solid var(--cert-accent);
            border-left: 2px solid var(--cert-accent);
        }
        .corner-tl::after {
            content: '';
            position: absolute; top: 0; left: 0;
            width: 18mm; height: 18mm;
            background: linear-gradient(135deg, rgba(242,101,34,0.12) 0%, transparent 70%);
        }
        .corner-tr {
            top: 11mm; right: 11mm;
            border-top: 2px solid var(--cert-accent);
            border-right: 2px solid var(--cert-accent);
        }
        .corner-tr::after {
            content: '';
            position: absolute; top: 0; right: 0;
            width: 18mm; height: 18mm;
            background: linear-gradient(-135deg, rgba(242,101,34,0.12) 0%, transparent 70%);
        }
        .corner-bl {
            bottom: 11mm; left: 11mm;
            border-bottom: 2px solid var(--cert-accent);
            border-left: 2px solid var(--cert-accent);
        }
        .corner-bl::after {
            content: '';
            position: absolute; bottom: 0; left: 0;
            width: 18mm; height: 18mm;
            background: linear-gradient(45deg, rgba(242,101,34,0.12) 0%, transparent 70%);
        }
        .corner-br {
            bottom: 11mm; right: 11mm;
            border-bottom: 2px solid var(--cert-accent);
            border-right: 2px solid var(--cert-accent);
        }
        .corner-br::after {
            content: '';
            position: absolute; bottom: 0; right: 0;
            width: 18mm; height: 18mm;
            background: linear-gradient(-45deg, rgba(242,101,34,0.12) 0%, transparent 70%);
        }

        /* Content Area */
        .content {
            position: absolute;
            top: 16mm; left: 24mm; right: 24mm; bottom: 58mm;
            text-align: center;
            z-index: 10;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5mm;
        }

        .logo-left {
            width: 26mm; height: 26mm;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-left .shield {
            width: 20mm; height: 24mm;
            background: var(--cert-navy);
            border-radius: 0 0 10mm 10mm;
            position: relative;
            border: 1.5px solid var(--cert-accent);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
        }
        .logo-left .shield::before {
            content: '';
            position: absolute;
            top: -5mm; left: 50%; transform: translateX(-50%);
            width: 16mm; height: 7mm;
            background: var(--cert-accent);
            border-radius: 8mm 8mm 0 0;
        }
        .shield-text {
            color: white; font-size: 6.5pt; font-weight: 700;
            line-height: 1.1; margin-top: 1.5mm; z-index: 2;
            font-family: 'Montserrat', sans-serif;
        }
        .shield-sub {
            color: var(--cert-gold); font-size: 4.5pt;
            margin-top: 0.5mm; z-index: 2;
            font-family: 'Montserrat', sans-serif;
        }
        .tagline {
            position: absolute; bottom: -3.5mm;
            font-size: 4.5pt; color: var(--cert-navy);
            font-weight: 600; width: 100%; text-align: center;
            font-family: 'Montserrat', sans-serif;
        }

        .logo-right {
            width: 26mm;
            text-align: center;
        }
        .teveta-icon {
            width: 11mm; height: 11mm;
            background: var(--cert-navy);
            border-radius: 50%;
            margin: 0 auto 1mm;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 12pt; font-weight: 800;
            position: relative;
            font-family: 'Montserrat', sans-serif;
        }
        .teveta-icon::after {
            content: '';
            position: absolute;
            top: -1.5mm; right: -1.5mm;
            width: 3.5mm; height: 3.5mm;
            background: var(--cert-accent);
            border-radius: 50%;
        }
        .teveta-name {
            font-size: 7pt; font-weight: 800;
            color: var(--cert-navy); letter-spacing: 0.5px;
            line-height: 1; font-family: 'Montserrat', sans-serif;
        }
        .teveta-sub {
            font-size: 4.5pt; color: var(--cert-navy);
            font-weight: 600; font-family: 'Montserrat', sans-serif;
        }

        /* College Title */
        .college-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 18pt; font-weight: 800;
            color: var(--cert-text);
            text-transform: uppercase;
            letter-spacing: 1.5px; line-height: 1.2;
            margin-top: 1mm;
        }
        .college-subtitle {
            font-family: 'Playfair Display', serif;
            font-size: 10pt; color: var(--cert-text-muted);
            font-style: italic; margin-top: 1mm;
        }

        /* Divider */
        .divider {
            display: flex; align-items: center;
            justify-content: center; margin: 2.5mm 0; gap: 3mm;
        }
        .divider-line {
            width: 35mm; height: 1px;
            background: var(--cert-navy);
        }
        .divider-diamond {
            width: 2.5mm; height: 2.5mm;
            background: var(--cert-accent);
            transform: rotate(45deg);
        }

        /* Certify Statement */
        .certify-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 13pt; font-weight: 700;
            color: var(--cert-navy);
            text-transform: uppercase;
            letter-spacing: 2px; margin: 3mm 0;
            display: flex; align-items: center;
            justify-content: center; gap: 4mm;
        }
        .side-decor {
            display: flex; align-items: center; gap: 1.5mm;
        }
        .dot {
            width: 1.2mm; height: 1.2mm;
            background: var(--cert-accent);
            transform: rotate(45deg);
        }
        .dot-sm { width: 0.8mm; height: 0.8mm; }

        /* Recipient */
        .recipient-name {
            font-family: 'Great Vibes', cursive;
            font-size: 32pt; color: var(--cert-text);
            margin: 3mm 0 4mm; line-height: 1.4;
        }
        .name-underline {
            width: 75mm; height: 1px;
            background: var(--cert-accent);
            margin: 0 auto 2mm;
        }

        /* Body */
        .body-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 10pt; color: var(--cert-text-muted);
            line-height: 1.5; margin: 1mm 0 2mm;
        }
        .body-text .highlight { font-weight: 600; color: var(--cert-text); }

        /* Course */
        .course-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 20pt; font-weight: 800;
            color: var(--cert-navy);
            text-transform: uppercase;
            letter-spacing: 1px; margin: 3mm 0;
            line-height: 1.2;
        }

        /* Merit */
        .merit-text {
            font-family: 'Great Vibes', cursive;
            font-size: 22pt; color: var(--cert-text);
            margin: 1.5mm 0;
        }
        .merit-underline {
            width: 45mm; height: 1px;
            background: var(--cert-accent);
            margin: 1.5mm auto;
        }

        /* Date */
        .date-section {
            font-family: 'Montserrat', sans-serif;
            font-size: 10.5pt; color: var(--cert-text-muted);
            line-height: 1.7; margin: 2.5mm 0;
        }
        .date-section span {
            font-family: 'Great Vibes', cursive;
            font-size: 16pt; color: var(--cert-text);
        }
        .date-section .date-year {
            font-family: 'Montserrat', sans-serif;
            font-size: 16pt; font-weight: 700;
        }

        /* Signatures */
        .signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin: 3mm 8mm 0;
            position: relative;
        }
        .signature-block {
            text-align: center; width: 42mm;
        }
        .signature-line {
            width: 100%; height: 1px;
            background: var(--cert-text-muted);
            margin-bottom: 1.5mm;
        }
        .signature-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 8pt; color: var(--cert-text-muted);
            font-weight: 600;
        }

        /* Seal */
        .seal-container {
            position: absolute;
            left: 50%; transform: translateX(-50%);
            bottom: 2mm; width: 24mm; height: 30mm;
        }
        .seal {
            width: 20mm; height: 20mm;
            background: var(--cert-navy);
            border-radius: 50%;
            border: 1.5px solid var(--cert-gold);
            position: relative;
            margin: 0 auto;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .seal::before {
            content: '';
            position: absolute; inset: 1mm;
            border: 1px solid var(--cert-gold);
            border-radius: 50%; opacity: 0.7;
        }
        .seal-star { color: var(--cert-gold); font-size: 10pt; }
        .seal-text {
            color: var(--cert-gold); font-size: 5pt;
            text-align: center; line-height: 1.2;
            font-family: 'Montserrat', sans-serif;
        }
        .seal-ribbon {
            position: absolute;
            bottom: -5mm; left: 50%; transform: translateX(-50%);
            width: 0; height: 0;
            border-left: 5mm solid transparent;
            border-right: 5mm solid transparent;
            border-top: 8mm solid var(--cert-accent);
        }
        .seal-ribbon::after {
            content: '';
            position: absolute;
            top: -8mm; left: -2.5mm;
            width: 0; height: 0;
            border-left: 2.5mm solid transparent;
            border-right: 2.5mm solid transparent;
            border-top: 5mm solid var(--cert-accent-dark);
        }

        /* Info Box */
        .info-box {
            position: absolute;
            bottom: 14mm; left: 24mm; right: 24mm;
            border: 1.5px solid var(--cert-accent);
            border-radius: 6px;
            padding: 3.5mm 5mm;
            display: grid;
            grid-template-columns: 1fr 1px 1fr;
            gap: 3.5mm;
            background: rgba(255,255,255,0.95);
        }
        .info-divider {
            width: 1px; background: var(--cert-accent);
            height: 100%; opacity: 0.4;
        }
        .info-col {
            display: flex; flex-direction: column; gap: 2.5mm;
        }
        .info-row {
            display: flex; align-items: center; gap: 2.5mm;
        }
        .info-icon {
            width: 7mm; height: 7mm; min-width: 7mm;
            border: 1.5px solid var(--cert-navy);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--cert-navy); font-size: 9pt;
        }
        .info-text { text-align: left; }
        .info-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 6.5pt; color: var(--cert-navy);
            font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-family: 'Montserrat', sans-serif;
            font-size: 9pt; color: var(--cert-text);
            font-weight: 700;
        }

        /* Bottom Decor */
        .bottom-decor {
            position: absolute;
            bottom: 10mm; left: 50%; transform: translateX(-50%);
            display: flex; align-items: center; gap: 1.5mm;
        }
        .bottom-decor .dot {
            width: 1.2mm; height: 1.2mm;
            background: var(--cert-accent);
            transform: rotate(45deg);
        }
        .bottom-decor .line {
            width: 12mm; height: 1px;
            background: var(--cert-navy);
        }

        /* Action Bar */
        .action-bar {
            text-align: center; padding: 16px;
            margin-top: 20px;
        }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; border-radius: 8px;
            text-decoration: none; font-size: 13px;
            font-weight: 500; cursor: pointer;
            font-family: 'Inter', sans-serif;
        }
        .btn-secondary {
            border: 1px solid #e5e7eb;
            color: #1f2937; background: #fff;
        }
        .btn-primary {
            border: 1px solid #f26522;
            background: #f26522; color: #fff;
            margin-left: 8px;
        }
        .btn-ghost {
            border: 1px solid transparent;
            background: transparent; color: #6b7280;
            margin-left: 8px;
        }
    </style>
    <base target="_blank">
</head>
<body>

    <!-- Action Bar -->
    <div class="action-bar no-print">
        <a href="{{ route('student.certificates') }}" class="btn btn-secondary">
            <span>&#8592;</span> Back to Certificates
        </a>
        @if(isset($certificate) && $certificate)
        <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-primary">
            <span>&#11015;</span> Download PDF
        </a>
        @endif
        <button onclick="window.print()" class="btn btn-ghost">
            <span>&#128424;</span> Print
        </button>
    </div>

    <div class="cert-page">
        <div class="frame-outer"></div>
        <div class="frame-inner"></div>
        <div class="frame-line"></div>

        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        <div class="content">
            <!-- Header -->
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
                    <div class="teveta-icon">T</div>
                    <div class="teveta-name">TEVETA</div>
                    <div class="teveta-sub">ACCREDITED</div>
                </div>
            </div>

            <!-- Certify Statement -->
            <div class="certify-text">
                <div class="side-decor">
                    <div class="dot"></div>
                    <div class="dot dot-sm"></div>
                    <div class="dot"></div>
                </div>
                THIS IS TO CERTIFY THAT
                <div class="side-decor">
                    <div class="dot"></div>
                    <div class="dot dot-sm"></div>
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
                Ceremony held on the <span>{{ $graduation_day ?? '1' }}<sup>{{ $graduation_suffix ?? 'st' }}</sup></span> day of <span>{{ $graduation_month ?? 'January' }}</span><br>
                in the year <span class="date-year">{{ $graduation_year ?? date('Y') }}</span>
            </div>

            <!-- Signatures -->
            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Principal</div>
                    <div style="margin-top: 3mm;">
                        <div class="signature-line"></div>
                        <div class="signature-label">Graduate's Signature</div>
                    </div>
                </div>

                <div class="seal-container">
                    <div class="seal">
                        <div class="seal-text">
                            <span style="font-size: 10pt;">&#9733;</span><br>
                            EXCELLENCE
                        </div>
                    </div>
                    <div class="seal-ribbon"></div>
                </div>

                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Director</div>
                    <div style="margin-top: 3mm;">
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
            <div class="dot" style="width: 1.8mm; height: 1.8mm;"></div>
            <div class="dot"></div>
            <div class="line"></div>
        </div>
    </div>
</body>
</html>
