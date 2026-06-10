<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Certificate - {{ $certificate_number ?? 'Preview' }} - EduTrack Computer Training College</title>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">
<style>
  @page { size: A4 portrait; margin: 0; }

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

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
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
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
    padding: 50px 72px 44px;
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
  .logo-edutrack { height: 118px; width: auto; }
  .logo-teveta { height: 58px; width: auto; }
  .logo-spacer { width: 80px; }

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
    font-size: 17px;
    font-style: italic;
    color: var(--dark);
    margin-bottom: 14px;
  }

  .deco-rule {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    margin: 12px 0;
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
    margin: 16px 0 8px;
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
    font-size: 68px;
    color: var(--dark);
    line-height: 1.05;
    margin: 6px 0 14px;
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
    font-size: 46px;
    font-weight: 900;
    color: var(--navy);
    text-transform: uppercase;
    letter-spacing: 3px;
    line-height: 1.15;
    margin: 22px 0 8px;
  }

  .merit {
    font-family: "Great Vibes", "Brush Script MT", cursive;
    font-size: 54px;
    color: var(--dark);
    line-height: 1.05;
    margin: 4px 0 14px;
  }

  .date-text {
    font-size: 17.5px;
    font-style: italic;
    color: var(--dark);
    line-height: 1.5;
    max-width: 520px;
    margin: 8px 0 12px;
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

  .qr-block {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    margin-bottom: 10px;
  }
  .qr-block svg, .qr-block img {
    width: 76px;
    height: 76px;
    display: block;
  }
  .qr-caption {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 2px;
    color: var(--navy);
    text-transform: uppercase;
  }

  .signatures {
    display: flex;
    justify-content: space-between;
    width: 100%;
    max-width: 560px;
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
    margin-top: 26px;
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

  .verify-footer {
    margin-top: 14px;
    font-size: 10.5px;
    font-style: italic;
    color: #5A6473;
  }

  .action-bar {
    text-align: center;
    padding-bottom: 18px;
  }
  .action-bar a, .action-bar button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 8px;
    border: 1px solid var(--navy);
    background: #fff;
    color: var(--navy);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    margin: 0 4px;
  }
  .action-bar .primary {
    background: var(--navy);
    color: #fff;
  }

  @media print {
    html, body {
      background: #fff;
      padding: 0;
      margin: 0;
      display: block;
    }
    .cert-sheet {
      box-shadow: none;
      margin: 0;
      page-break-inside: avoid;
    }
    .no-print { display: none !important; }
  }
</style>
</head>
<body>

<div class="action-bar no-print">
    @auth
    <a href="{{ route('student.certificates') }}">&larr; Back to Certificates</a>
    @endauth
    @if(isset($certificate) && $certificate)
    <a class="primary" href="{{ route('certificates.download', $certificate) }}">Download PDF</a>
    @endif
    <button onclick="window.print()">Print Certificate</button>
</div>

<div class="cert-sheet">
  <div class="corner corner-tl"></div>
  <div class="corner corner-tr"></div>
  <div class="corner corner-bl"></div>
  <div class="corner corner-br"></div>

  <div class="watermark-bg"></div>

  <div class="content">
    <div class="logos-row">
      <img src="{{ asset('assets/images/logo.png') }}" alt="EduTrack Logo" class="logo-edutrack">
      @if(file_exists(public_path('assets/images/teveta-logo.png')))
      <img src="{{ asset('assets/images/teveta-logo.png') }}" alt="TEVETA Logo" class="logo-teveta">
      @else
      <div class="logo-spacer"></div>
      @endif
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

    @if(isset($classification) && $classification && strcasecmp($classification, 'Pass') !== 0)
    <div class="merit">With {{ $classification }}</div>
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
      {{ $graduation_day ?? '1' }}<span class="ordinal">{{ $graduation_suffix ?? 'st' }}</span> day of {{ $graduation_month ?? 'January' }}<br>in the year <span class="year">{{ $graduation_year ?? date('Y') }}</span>
    </p>

    <div class="qr-block">
      @if(!empty($verify_url))
        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(76)->color(15, 43, 82)->margin(0)->generate($verify_url) !!}
        <div class="qr-caption">Scan to Verify</div>
      @endif
    </div>

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
        <div class="bottom-value">{{ $national_id ?? '' }}</div>
      </div>
      <div class="bottom-block right">
        <div class="bottom-label">Graduate's ID No.</div>
        <div class="bottom-value">{{ $student_number ?? '' }}</div>
      </div>
    </div>

    @if(!empty($verification_code))
    <div class="verify-footer">
      Certificate No. {{ $certificate_number ?? '' }} &middot; Verify at {{ preg_replace('#^https?://#', '', $verify_url ?? '') }}
    </div>
    @endif
  </div>
</div>

</body>
</html>
