<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate_number ?? 'Preview' }} - EduTrack Computer Training College</title>
    <link rel="stylesheet" href="{{ asset('assets/css/cert-fonts.css') }}">
    <style>
        @page { size: A4 portrait; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            width: 210mm; min-height: 297mm; margin: 0 auto;
            background: #f5f5f5;
            font-family: 'Montserrat', 'Inter', sans-serif;
            padding: 20px;
        }
        @media print {
            body { background: #fff; padding: 0; width: 210mm; height: 297mm; }
            .cert-page { box-shadow: none !important; margin: 0 !important; }
            .no-print { display: none !important; }
        }

        .cert-page {
            width: 210mm; height: 297mm; background: #fff;
            position: relative; margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            overflow: hidden;
            display: flex; flex-direction: column;
        }

        /* Frame borders */
        .frame-outer {
            position: absolute; top: 7mm; left: 7mm; right: 7mm; bottom: 7mm;
            border: 2px solid #f26522; border-radius: 2px; pointer-events: none;
        }
        .frame-inner {
            position: absolute; top: 9mm; left: 9mm; right: 9mm; bottom: 9mm;
            border: 3px solid #1e3a8a; border-radius: 1px; pointer-events: none;
        }
        .frame-line {
            position: absolute; top: 11mm; left: 11mm; right: 11mm; bottom: 11mm;
            border: 1px solid #d4af37; opacity: 0.5; pointer-events: none;
        }

        /* Corner ornaments */
        .corner { position: absolute; width: 28mm; height: 28mm; z-index: 5; }
        .corner-tl { top: 11mm; left: 11mm; border-top: 2px solid #f26522; border-left: 2px solid #f26522; }
        .corner-tl::after { content: ''; position: absolute; top: 0; left: 0; width: 18mm; height: 18mm; background: linear-gradient(135deg, rgba(242,101,34,0.12) 0%, transparent 70%); }
        .corner-tr { top: 11mm; right: 11mm; border-top: 2px solid #f26522; border-right: 2px solid #f26522; }
        .corner-tr::after { content: ''; position: absolute; top: 0; right: 0; width: 18mm; height: 18mm; background: linear-gradient(-135deg, rgba(242,101,34,0.12) 0%, transparent 70%); }
        .corner-bl { bottom: 11mm; left: 11mm; border-bottom: 2px solid #f26522; border-left: 2px solid #f26522; }
        .corner-bl::after { content: ''; position: absolute; bottom: 0; left: 0; width: 18mm; height: 18mm; background: linear-gradient(45deg, rgba(242,101,34,0.12) 0%, transparent 70%); }
        .corner-br { bottom: 11mm; right: 11mm; border-bottom: 2px solid #f26522; border-right: 2px solid #f26522; }
        .corner-br::after { content: ''; position: absolute; bottom: 0; right: 0; width: 18mm; height: 18mm; background: linear-gradient(-45deg, rgba(242,101,34,0.12) 0%, transparent 70%); }

        /* Main content area - uses flex, NOT absolute positioning */
        .content {
            flex: 1;
            padding: 18mm 24mm 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 10;
            position: relative;
        }

        /* Header */
        .header {
            display: flex; justify-content: space-between;
            align-items: flex-start; width: 100%;
            margin-bottom: 4mm;
        }
        .logo-left {
            width: 24mm; display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .shield {
            width: 18mm; height: 22mm; background: #1e3a8a;
            border-radius: 0 0 9mm 9mm; position: relative;
            border: 1.5px solid #f26522;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
        }
        .shield::before {
            content: ''; position: absolute;
            top: -4.5mm; left: 50%; transform: translateX(-50%);
            width: 14mm; height: 6mm; background: #f26522;
            border-radius: 7mm 7mm 0 0;
        }
        .shield-text { color: white; font-size: 6pt; font-weight: 700; line-height: 1; margin-top: 1mm; z-index: 2; font-family: 'Montserrat', sans-serif; }
        .shield-sub { color: #d4af37; font-size: 4pt; margin-top: 0.5mm; z-index: 2; font-family: 'Montserrat', sans-serif; text-align: center; }
        .tagline {
            position: absolute; bottom: -3mm; left: 50%; transform: translateX(-50%);
            font-size: 4pt; color: #1e3a8a; font-weight: 600;
            width: 100%; text-align: center; font-family: 'Montserrat', sans-serif;
            white-space: nowrap;
        }

        .header-center { flex: 1; text-align: center; padding: 0 4mm; }
        .college-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 15pt; font-weight: 800; color: #1a1a1a;
            text-transform: uppercase; letter-spacing: 1.5px; line-height: 1.2;
        }
        .divider {
            display: flex; align-items: center; justify-content: center;
            margin: 2mm 0; gap: 2mm;
        }
        .divider-line { width: 30mm; height: 1px; background: #1e3a8a; }
        .divider-diamond { width: 2mm; height: 2mm; background: #f26522; transform: rotate(45deg); }
        .college-subtitle {
            font-family: 'Playfair Display', serif;
            font-size: 9pt; color: #444; font-style: italic;
        }

        .logo-right { width: 24mm; text-align: center; }
        .teveta-icon {
            width: 10mm; height: 10mm; background: #1e3a8a; border-radius: 50%;
            margin: 0 auto 1mm; display: flex; align-items: center; justify-content: center;
            color: white; font-size: 10pt; font-weight: 800; position: relative;
            font-family: 'Montserrat', sans-serif;
        }
        .teveta-icon::after {
            content: ''; position: absolute;
            top: -1.5mm; right: -1.5mm; width: 3mm; height: 3mm;
            background: #f26522; border-radius: 50%;
        }
        .teveta-name { font-size: 6.5pt; font-weight: 800; color: #1e3a8a; letter-spacing: 0.5px; font-family: 'Montserrat', sans-serif; }
        .teveta-sub { font-size: 4pt; color: #1e3a8a; font-weight: 600; font-family: 'Montserrat', sans-serif; }

        /* Certify statement */
        .certify-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 12pt; font-weight: 700; color: #1e3a8a;
            text-transform: uppercase; letter-spacing: 2px;
            margin: 3mm 0; display: flex; align-items: center; gap: 3mm;
        }
        .side-decor { display: flex; align-items: center; gap: 1mm; }
        .dot { width: 1mm; height: 1mm; background: #f26522; transform: rotate(45deg); }
        .dot-sm { width: 0.7mm; height: 0.7mm; }

        /* Recipient */
        .recipient-name {
            font-family: 'Great Vibes', cursive;
            font-size: 28pt; color: #1a1a1a;
            margin: 1mm 0 0.5mm; line-height: 1.3;
        }
        .name-underline {
            width: 70mm; height: 1px;
            background: #f26522; margin: 0 auto 2mm;
        }

        /* Body text */
        .body-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 9.5pt; color: #333; line-height: 1.5;
            margin: 1mm 0 2mm;
        }

        /* Course */
        .course-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 17pt; font-weight: 800; color: #1e3a8a;
            text-transform: uppercase; letter-spacing: 1px;
            margin: 1mm 0; line-height: 1.2;
        }

        /* Merit */
        .merit-text {
            font-family: 'Great Vibes', cursive;
            font-size: 20pt; color: #1a1a1a; margin: 1mm 0;
        }
        .merit-underline {
            width: 40mm; height: 1px;
            background: #f26522; margin: 0.5mm auto 1mm;
        }

        /* Date */
        .date-section {
            font-family: 'Montserrat', sans-serif;
            font-size: 9.5pt; color: #333; line-height: 1.6; margin: 2mm 0;
        }
        .date-section span { font-family: 'Great Vibes', cursive; font-size: 15pt; color: #1a1a1a; }
        .date-section .date-year { font-family: 'Montserrat', sans-serif; font-size: 15pt; font-weight: 700; }

        /* Signatures */
        .signatures-area {
            width: 100%; display: flex;
            justify-content: space-between; align-items: flex-end;
            margin-top: auto;
            padding-bottom: 2mm;
        }
        .signature-block {
            text-align: center; width: 40mm;
        }
        .signature-line {
            width: 100%; height: 1px;
            background: #333; margin-bottom: 1mm;
        }
        .signature-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 7.5pt; color: #333; font-weight: 600;
        }
        .signature-block .sub-sig {
            margin-top: 3mm;
        }

        /* Seal */
        .seal-wrap {
            display: flex; flex-direction: column;
            align-items: center; justify-content: flex-end;
        }
        .seal {
            width: 18mm; height: 18mm; background: #1e3a8a;
            border-radius: 50%; border: 1.5px solid #d4af37;
            position: relative; display: flex;
            align-items: center; justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .seal::before {
            content: ''; position: absolute; inset: 1mm;
            border: 1px solid #d4af37; border-radius: 50%; opacity: 0.7;
        }
        .seal-text {
            color: #d4af37; font-size: 5pt;
            text-align: center; line-height: 1.1;
            font-family: 'Montserrat', sans-serif;
        }
        .seal-text span { font-size: 9pt; }
        .seal-ribbon {
            width: 0; height: 0;
            border-left: 5mm solid transparent;
            border-right: 5mm solid transparent;
            border-top: 7mm solid #f26522;
            margin-top: -2mm;
        }

        /* Info Box */
        .info-box {
            width: calc(100% - 48mm);
            margin: 0 auto 12mm;
            border: 1.5px solid #f26522;
            border-radius: 6px;
            padding: 3mm 5mm;
            display: grid;
            grid-template-columns: 1fr 1px 1fr;
            gap: 3mm;
            background: rgba(255,255,255,0.95);
        }
        .info-divider { width: 1px; background: #f26522; height: 100%; opacity: 0.4; }
        .info-col { display: flex; flex-direction: column; gap: 2mm; }
        .info-row { display: flex; align-items: center; gap: 2mm; }
        .info-icon {
            width: 6.5mm; height: 6.5mm; min-width: 6.5mm;
            border: 1.5px solid #1e3a8a; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #1e3a8a; font-size: 8pt;
        }
        .info-text { text-align: left; }
        .info-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 6pt; color: #1e3a8a;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .info-value {
            font-family: 'Montserrat', sans-serif;
            font-size: 8.5pt; color: #1a1a1a; font-weight: 700;
        }

        /* Bottom decor */
        .bottom-decor {
            display: flex; align-items: center; gap: 1.5mm;
            margin-bottom: 10mm;
        }
        .bottom-decor .line { width: 12mm; height: 1px; background: #1e3a8a; }
        .bottom-decor .dot { width: 1.2mm; height: 1.2mm; background: #f26522; transform: rotate(45deg); }

        /* Action bar */
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
        .btn-secondary { border: 1px solid #e5e7eb; color: #1f2937; background: #fff; }
        .btn-primary { border: 1px solid #f26522; background: #f26522; color: #fff; margin-left: 8px; }
        .btn-ghost { border: 1px solid transparent; background: transparent; color: #6b7280; margin-left: 8px; }
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
                    <div class="shield">
                        <div class="shield-text">EduTrack</div>
                        <div class="shield-sub">Excel<br>Through<br>Education</div>
                    </div>
                    <div class="tagline">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
                </div>

                <div class="header-center">
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
            <div class="signatures-area">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Principal</div>
                    <div class="sub-sig">
                        <div class="signature-line"></div>
                        <div class="signature-label">Graduate's Signature</div>
                    </div>
                </div>

                <div class="seal-wrap">
                    <div class="seal">
                        <div class="seal-text">
                            <span>&#9733;</span><br>
                            EXCELLENCE
                        </div>
                    </div>
                    <div class="seal-ribbon"></div>
                </div>

                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Director</div>
                    <div class="sub-sig">
                        <div class="signature-line"></div>
                        <div class="signature-label">Graduate's I.D. No.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Box (outside content, positioned at bottom via margin) -->
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
