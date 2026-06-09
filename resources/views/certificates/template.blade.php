{{-- Static "template" version of the certificate, used only for generating
     the background PNG that the runtime TCPDF service overlays dynamic text on.
     Renders with empty dynamic fields so the template has fixed design only. --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate Template</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Open+Sans:wght@400;600;700;800&family=Roboto+Condensed:wght@700&display=swap" rel="stylesheet">
<style>
    @page { size: A4 portrait; margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { width: 210mm; height: 297mm; margin: 0; padding: 0; font-family: 'Open Sans', sans-serif; }

    .page { position: relative; width: 210mm; height: 297mm; overflow: hidden; background: #fff; }

    /* --- Background watermark: tiled "Edutrack Computer Training College" --- */
    .watermark {
        position: absolute;
        top: 14mm; left: 14mm; right: 14mm; bottom: 14mm;
        overflow: hidden;
        opacity: 0.18;
        color: #1e3a8a;
        font-size: 6.5pt;
        line-height: 1.2;
        font-family: 'Open Sans', sans-serif;
        font-weight: 700;
        z-index: 0;
        white-space: nowrap;
        word-spacing: 1px;
    }
    .watermark span { display: inline-block; padding-right: 4px; }

    /* --- Frames --- */
    .frame-orange { position: absolute; top: 6mm; left: 6mm; right: 6mm; bottom: 6mm; border: 1.8mm solid #f26522; z-index: 1; }
    .frame-gold   { position: absolute; top: 9mm; left: 9mm; right: 9mm; bottom: 9mm; border: 0.3mm solid #d4af37; z-index: 2; }
    .frame-blue   { position: absolute; top: 12mm; left: 12mm; right: 12mm; bottom: 12mm; border: 1.8mm solid #1e3a8a; z-index: 3; }

    /* --- Corner triangles --- */
    .corner { position: absolute; width: 0; height: 0; z-index: 4; }
    .corner.tl { top: 6mm; left: 6mm; border-top: 22mm solid #f26522; border-right: 22mm solid transparent; }
    .corner.tr { top: 6mm; right: 6mm; border-top: 22mm solid #f26522; border-left: 22mm solid transparent; }
    .corner.bl { bottom: 6mm; left: 6mm; border-bottom: 22mm solid #f26522; border-right: 22mm solid transparent; }
    .corner.br { bottom: 6mm; right: 6mm; border-bottom: 22mm solid #f26522; border-left: 22mm solid transparent; }

    /* --- Header (logos + college name) --- */
    .header { position: absolute; top: 16mm; left: 16mm; right: 16mm; height: 32mm; z-index: 5; display: flex; align-items: center; }
    .header .logo-l { width: 32mm; height: 32mm; }
    .header .logo-l img { width: 100%; height: auto; }
    .header .title { flex: 1; text-align: center; font-family: 'Roboto Condensed', 'Open Sans', sans-serif; font-weight: 700; font-size: 22pt; line-height: 1.1; color: #1a1a1a; letter-spacing: 1px; }
    .header .logo-r { width: 38mm; text-align: right; }
    .header .logo-r img { width: 38mm; height: auto; }

    /* --- Decorative divider above "A skill training college" --- */
    .divider { position: absolute; left: 0; right: 0; text-align: center; z-index: 5; }
    .divider .line { display: inline-block; vertical-align: middle; width: 40mm; height: 0.4mm; background: #1e3a8a; }
    .divider .diamond { display: inline-block; vertical-align: middle; width: 2.5mm; height: 2.5mm; background: #f26522; transform: rotate(45deg); margin: 0 4mm; }

    .tagline { position: absolute; top: 52mm; left: 0; right: 0; text-align: center; font-family: 'Open Sans', serif; font-size: 12pt; color: #444; z-index: 5; }

    /* --- "THIS IS TO CERTIFY THAT" line --- */
    .certify { position: absolute; top: 62mm; left: 0; right: 0; text-align: center; font-family: 'Open Sans', sans-serif; font-weight: 700; font-size: 18pt; letter-spacing: 2px; color: #1e3a8a; z-index: 5; }
    .certify .side { display: inline-block; width: 30mm; height: 0.4mm; background: #f26522; vertical-align: middle; margin: 0 8mm; }
    .certify .diamond-mini { display: inline-block; width: 2mm; height: 2mm; background: #f26522; transform: rotate(45deg); vertical-align: middle; margin: 0 2mm; }

    /* --- Body text static --- */
    .body-static { position: absolute; top: 110mm; left: 30mm; right: 30mm; text-align: center; font-family: 'Open Sans', serif; font-size: 13pt; line-height: 1.7; color: #333; z-index: 5; }

    .merit-decor { position: absolute; top: 160mm; left: 0; right: 0; text-align: center; z-index: 5; }
    .merit-decor .line { display: inline-block; width: 28mm; height: 0.35mm; background: #f26522; vertical-align: middle; margin: 0 3mm; }
    .merit-decor .diamond { display: inline-block; width: 2.4mm; height: 2.4mm; background: #f26522; transform: rotate(45deg); vertical-align: middle; }

    .date-static { position: absolute; top: 170mm; left: 30mm; right: 30mm; text-align: center; font-family: 'Open Sans', serif; font-size: 13pt; line-height: 1.8; color: #333; z-index: 5; }

    /* --- Name underline only (text overlaid by TCPDF) --- */
    .name-underline { position: absolute; top: 102mm; left: 35mm; right: 35mm; height: 0.5mm; background: #f26522; z-index: 5; }

    /* --- Signature row: lines + labels --- */
    .sig-block { position: absolute; left: 22mm; right: 22mm; bottom: 24mm; z-index: 5; }
    .sig-row { display: flex; justify-content: space-between; margin-bottom: 6mm; }
    .sig-col { width: 70mm; text-align: center; }
    .sig-line { width: 100%; height: 0.4mm; background: #000; margin-bottom: 1.5mm; }
    .sig-label { font-family: 'Open Sans', serif; font-size: 10pt; font-weight: 700; color: #222; }
</style>
</head>
<body>
<div class="page">
    {{-- Watermark layer: many copies of the tiled text --}}
    <div class="watermark">
        @for($i = 0; $i < 80; $i++)
            <span>Edutrack Computer Training College </span><span>Edutrack Computer Training College </span><span>Edutrack Computer Training College </span><span>Edutrack Computer Training College </span><span>Edutrack Computer Training College </span><span>Edutrack Computer Training College </span><span>Edutrack Computer Training College </span><span>Edutrack Computer Training College </span><span>Edutrack Computer Training College </span><span>Edutrack Computer Training College </span><br>
        @endfor
    </div>

    {{-- Frames + corners --}}
    <div class="frame-orange"></div>
    <div class="frame-gold"></div>
    <div class="frame-blue"></div>
    <div class="corner tl"></div>
    <div class="corner tr"></div>
    <div class="corner bl"></div>
    <div class="corner br"></div>

    {{-- Header --}}
    <div class="header">
        <div class="logo-l"><img src="{{ public_path('assets/images/logo-pdf.png') }}" alt=""></div>
        <div class="title">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
        <div class="logo-r"><img src="{{ public_path('assets/images/teveta-logo.png') }}" alt=""></div>
    </div>

    {{-- Divider --}}
    <div class="divider" style="top: 44mm;">
        <span class="line"></span><span class="diamond"></span><span class="line"></span>
    </div>

    {{-- Tagline --}}
    <div class="tagline">A skill training college</div>

    {{-- Certify --}}
    <div class="certify">
        <span class="side"></span>THIS IS TO CERTIFY THAT<span class="side"></span>
    </div>

    {{-- Name underline (text overlaid) --}}
    <div class="name-underline"></div>

    {{-- "having satisfied the requirements" static text --}}
    <div class="body-static">
        having satisfied the requirements for the<br>award of the certificate of
    </div>

    {{-- The "With X" classification + its underline diamond, and the date
         paragraph, are drawn at runtime by TCPDF so they only appear when
         applicable and the cursive day/month/year inline correctly. --}}

    {{-- Signature block --}}
    <div class="sig-block">
        <div class="sig-row">
            <div class="sig-col"><div class="sig-line"></div><div class="sig-label">Principal</div></div>
            <div class="sig-col"><div class="sig-line"></div><div class="sig-label">Director</div></div>
        </div>
        <div class="sig-row">
            <div class="sig-col"><div class="sig-line"></div><div class="sig-label">Graduate's Signature</div></div>
            <div class="sig-col"><div class="sig-line"></div><div class="sig-label">Graduate's ID No.</div></div>
        </div>
        <div class="sig-row" style="margin-bottom: 0;">
            <div class="sig-col"><div class="sig-line"></div></div>
            <div class="sig-col"><div class="sig-line"></div></div>
        </div>
    </div>
</div>
</body>
</html>
