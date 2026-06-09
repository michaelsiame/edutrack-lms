<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate_number ?? 'Preview' }} - EduTrack Computer Training College</title>
    <link rel="stylesheet" href="{{ asset('assets/css/cert-fonts.css') }}">
    <style>
        @page { size: A4 portrait; margin: 0; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --gold: #D4952A;
            --gold-light: #E8B84A;
            --navy: #1B3A6B;
            --navy-dark: #0F2B52;
            --dark: #0A1628;
            --bg: #FFFFFF;
        }

        html, body {
            width: 100%;
            min-height: 100vh;
            background: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 24px 0;
            font-family: "Playfair Display", Georgia, "Times New Roman", serif;
        }

        .cert-sheet {
            width: 210mm;
            height: 297mm;
            background: var(--bg);
            position: relative;
            box-shadow: 0 6px 32px rgba(0,0,0,0.22);
            overflow: hidden;
            border: 20px solid var(--gold);
        }

        .cert-sheet::before {
            content: "";
            position: absolute;
            top: 14px; left: 14px; right: 14px; bottom: 14px;
            border: 10px solid var(--navy);
            pointer-events: none;
            z-index: 10;
        }

        .corner {
            position: absolute;
            width: 0; height: 0;
            z-index: 11;
            pointer-events: none;
        }
        .corner-tl {
            top: 24px; left: 24px;
            border-top: 80px solid var(--navy);
            border-right: 80px solid transparent;
        }
        .corner-tr {
            top: 24px; right: 24px;
            border-top: 80px solid var(--navy);
            border-left: 80px solid transparent;
        }
        .corner-bl {
            bottom: 24px; left: 24px;
            border-bottom: 80px solid var(--navy);
            border-right: 80px solid transparent;
        }
        .corner-br {
            bottom: 24px; right: 24px;
            border-bottom: 80px solid var(--navy);
            border-left: 80px solid transparent;
        }

        .watermark-bg {
            position: absolute;
            inset: 24px;
            z-index: 0;
            pointer-events: none;
            opacity: 0.055;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='320' height='48'%3E%3Ctext x='0' y='32' font-family='Georgia,serif' font-size='14' font-weight='700' fill='%231B3A6B' letter-spacing='1' text-transform='uppercase'%3EEdutrack Computer Training College%3C/text%3E%3C/svg%3E");
            background-repeat: repeat;
            transform: rotate(-14deg) scale(1.15);
            transform-origin: center center;
        }

        .content {
            position: relative;
            z-index: 5;
            padding: 28px 56px 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .logos-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 6px;
            padding: 0 16px;
        }
        .logo-edutrack { height: 90px; width: auto; }
        .logo-teveta { height: 48px; width: auto; }

        .institution-name {
            font-size: 27px;
            font-weight: 700;
            color: var(--navy-dark);
            text-transform: uppercase;
            letter-spacing: 2.5px;
            line-height: 1.25;
            margin-bottom: 3px;
        }

        .institution-sub {
            font-size: 16px;
            font-style: italic;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .deco-rule {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin: 6px 0;
        }
        .deco-rule .line {
            flex: 1;
            max-width: 170px;
            height: 0;
            border-top: 2px solid var(--gold);
        }
        .deco-rule .diamond {
            width: 10px; height: 10px;
            background: var(--gold);
            transform: rotate(45deg);
            flex-shrink: 0;
        }
        .deco-rule .diamond-sm {
            width: 6px; height: 6px;
            background: var(--gold-light);
            transform: rotate(45deg);
            flex-shrink: 0;
        }

        .flanked {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            width: 100%;
            margin: 8px 0 4px;
        }
        .flanked .flank {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .flanked .flank .f-line {
            width: 50px;
            height: 0;
            border-top: 2px solid var(--gold);
        }
        .flanked .flank .f-diamond {
            width: 7px; height: 7px;
            background: var(--gold);
            transform: rotate(45deg);
            flex-shrink: 0;
        }
        .flanked .flank .f-diamond-sm {
            width: 5px; height: 5px;
            background: var(--gold-light);
            transform: rotate(45deg);
            flex-shrink: 0;
        }

        .certify-text {
            font-size: 23px;
            font-weight: 700;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 3.5px;
            white-space: nowrap;
        }

        .recipient {
            font-family: "Great Vibes", "Brush Script MT", cursive;
            font-size: 56px;
            color: var(--dark);
            line-height: 1.05;
            margin: 4px 0 8px;
        }

        .body-text {
            font-size: 17.5px;
            font-style: italic;
            color: var(--dark);
            line-height: 1.5;
            max-width: 540px;
            margin: 4px 0;
        }

        .course-title {
            font-size: 38px;
            font-weight: 900;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 3px;
            line-height: 1.15;
            margin: 14px 0 6px;
        }

        .merit {
            font-family: "Great Vibes", "Brush Script MT", cursive;
            font-size: 44px;
            color: var(--dark);
            line-height: 1.05;
            margin: 4px 0 8px;
        }

        .date-text {
            font-size: 15px;
            font-style: italic;
            color: var(--dark);
            line-height: 1.4;
            max-width: 520px;
            margin: 4px 0 10px;
        }
        .date-text .ordinal {
            font-size: 12px;
            vertical-align: super;
            font-style: normal;
        }
        .date-text .year {
            font-weight: 700;
            font-style: normal;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 560px;
            margin-top: 24px;
            padding-bottom: 4px;
        }
        .sig-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 190px;
        }
        .sig-line {
            width: 100%;
            border-top: 1.5px solid var(--dark);
            margin-bottom: 5px;
        }
        .sig-label {
            font-size: 14px;
            color: var(--dark);
            font-weight: 700;
        }

        .bottom-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 620px;
            margin-top: 12px;
            padding: 0 8px;
        }
        .bottom-block {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .bottom-block.right {
            align-items: flex-end;
        }
        .bottom-label {
            font-size: 13px;
            color: var(--dark);
            font-weight: 700;
            margin-bottom: 5px;
        }
        .bottom-value {
            font-family: "Courier New", Courier, monospace;
            font-size: 18px;
            color: var(--dark);
            font-weight: 700;
        }

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
        .btn-primary { border: 1px solid var(--gold); background: var(--gold); color: #fff; margin-left: 8px; }
        .btn-ghost { border: 1px solid transparent; background: transparent; color: #6b7280; margin-left: 8px; }

        @media print {
            html, body {
                background: #fff;
                padding: 0;
                margin: 0;
            }
            .cert-sheet {
                box-shadow: none;
                margin: 0;
                page-break-inside: avoid;
                border: 20px solid var(--gold);
            }
            .cert-sheet::before {
                top: 14px; left: 14px; right: 14px; bottom: 14px;
                border: 10px solid var(--navy);
            }
            .no-print { display: none !important; }
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
        <a href="{{ route('certificates.download', $certificate) }}" target="_blank" class="btn btn-primary">
            <span>&#11015;</span> Download PDF
        </a>
        @endif
        <button onclick="window.print()" class="btn btn-ghost">
            <span>&#128424;</span> Print
        </button>
    </div>

    <div class="cert-sheet">
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        <div class="watermark-bg"></div>

        <div class="content">
            <div class="logos-row">
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="EduTrack Logo" class="logo-edutrack">
                <img src="{{ asset('assets/images/teveta-logo.png') }}" alt="TEVETA Logo" class="logo-teveta">
            </div>

            <div class="institution-name">
                EduTrack Computer<br>Training College
            </div>
            <div class="institution-sub">A skill training college</div>

            <div class="deco-rule">
                <div class="line"></div>
                <div class="diamond-sm"></div>
                <div class="diamond"></div>
                <div class="diamond-sm"></div>
                <div class="line"></div>
            </div>

            <div class="flanked">
                <div class="flank">
                    <div class="f-line"></div>
                    <div class="f-diamond-sm"></div>
                    <div class="f-diamond"></div>
                </div>
                <div class="certify-text">This is to Certify That</div>
                <div class="flank">
                    <div class="f-diamond"></div>
                    <div class="f-diamond-sm"></div>
                    <div class="f-line"></div>
                </div>
            </div>

            <div class="deco-rule">
                <div class="line"></div>
                <div class="diamond-sm"></div>
                <div class="diamond"></div>
                <div class="diamond-sm"></div>
                <div class="line"></div>
            </div>

            <div class="recipient">{{ $student_name ?? 'Student Name' }}</div>

            <p class="body-text">
                having satisfied the requirements for the award of the certificate of
            </p>

            <div class="course-title">{{ strtoupper($course_title ?? 'Course Title') }}</div>

            @if(isset($classification) && $classification && $classification !== 'Pass')
            <div class="merit">With {{ $classification }}</div>
            @else
            <div style="margin: 8px 0;"></div>
            @endif

            <div class="deco-rule">
                <div class="line"></div>
                <div class="diamond-sm"></div>
                <div class="diamond"></div>
                <div class="diamond-sm"></div>
                <div class="line"></div>
            </div>

            <p class="date-text">
                Was admitted to the certificate at a Graduation Ceremony held on the
                {{ $graduation_day ?? '1' }}<span class="ordinal"><sup>{{ $graduation_suffix ?? 'st' }}</sup></span> day of {{ $graduation_month ?? 'January' }}<br>in the year <span class="year">{{ $graduation_year ?? date('Y') }}</span>
            </p>

            <div class="signatures">
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label">Principal</div>
                </div>
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label">Director</div>
                </div>
            </div>

            <div class="bottom-row">
                <div class="bottom-block">
                    <div class="bottom-label">Graduate's Signature</div>
                    <div class="bottom-value">{{ $certificate_number ?? 'NRC 000000/00/0' }}</div>
                </div>
                <div class="bottom-block right">
                    <div class="bottom-label">Graduate's ID No.</div>
                    <div class="bottom-value">{{ $student_number ?? 'ECTC00000' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(request('download'))
    <div id="print-overlay" class="no-print" style="position: fixed; inset: 0; background: rgba(255,255,255,0.95); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'Inter', sans-serif;">
        <div style="width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top-color: #D4952A; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
        <h2 style="font-size: 20px; color: #1f2937; margin-bottom: 8px;">Preparing your certificate</h2>
        <p style="font-size: 14px; color: #6b7280;">Your print dialog will open shortly. Please select "Save as PDF" to download.</p>
    </div>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    <script>
        (function() {
            function triggerPrint() {
                var overlay = document.getElementById('print-overlay');
                if (overlay) overlay.style.display = 'none';
                window.print();
            }
            if (document.fonts) {
                document.fonts.ready.then(function() {
                    setTimeout(triggerPrint, 600);
                });
            } else {
                window.addEventListener('load', function() {
                    setTimeout(triggerPrint, 1200);
                });
            }
        })();
    </script>
    @endif

</body>
</html>
