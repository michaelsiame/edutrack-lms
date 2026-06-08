<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@font-face {
    font-family: 'DancingScript';
    src: url('/home/claude/fonts/DancingScript.ttf');
}
@page { size: 210mm 297mm; margin: 0; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    width: 210mm;
    height: 297mm;
    font-family: 'DejaVu Serif', Georgia, serif;
    background: #fff;
    overflow: hidden;
}

.frame-outer {
    width: 210mm;
    height: 297mm;
    background: #f26522;
    padding: 3.5mm;
    position: relative;
}
.corner {
    position: absolute;
    width: 22mm;
    height: 22mm;
    z-index: 10;
}
.corner-tl { top: 0; left: 0; }
.corner-tr { top: 0; right: 0; }
.corner-bl { bottom: 0; left: 0; }
.corner-br { bottom: 0; right: 0; }

.frame-blue {
    width: 100%;
    height: 100%;
    background: #1e3a8a;
    padding: 2mm;
}
.frame-white {
    width: 100%;
    height: 100%;
    background: #ffffff;
    overflow: hidden;
}
.content {
    width: 100%;
    height: 100%;
    padding: 5mm 9mm 5mm 9mm;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
}

/* ── HEADER ── */
.header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.header-center { flex: 1; text-align: center; padding: 0 4mm; }
.college-name {
    font-size: 22px;
    font-weight: bold;
    color: #1a1a1a;
    text-transform: uppercase;
    letter-spacing: 1px;
    line-height: 1.2;
    margin-bottom: 2mm;
}
.header-divider { display: flex; align-items: center; margin: 1.5mm 0; }
.hdline { flex: 1; height: 1px; background: #1e3a8a; }
.hddiamond { color: #f26522; font-size: 11px; padding: 0 4px; }
.tagline { font-size: 10px; color: #444; font-style: italic; margin-top: 1mm; }

.logo-teveta { width: 28mm; flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; }
.teveta-box {
    border: 1.5px solid #1e3a8a;
    border-radius: 3px;
    padding: 2px 5px 2px 4px;
    display: flex;
    align-items: center;
    gap: 3px;
}
.teveta-icon {
    width: 16px; height: 16px;
    background: #f26522;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.teveta-icon span { color: #fff; font-size: 10px; font-weight: 900; }
.teveta-name { font-size: 9px; font-weight: 900; color: #1e3a8a; letter-spacing: 1px; line-height: 1; }
.teveta-sub { font-size: 5px; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.3; }

/* ── CERTIFY BANNER ── */
.certify-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 2mm 0;
    width: 100%;
    border-top: 1.5px solid #f26522;
    border-bottom: 1.5px solid #f26522;
}
.banner-deco { color: #f26522; font-size: 9px; }
.banner-text {
    font-size: 15px;
    font-weight: 900;
    color: #1e3a8a;
    letter-spacing: 2.5px;
    text-transform: uppercase;
}

/* ── STUDENT NAME ── */
.name-section { text-align: center; width: 85%; }
.student-name {
    font-family: 'DancingScript', cursive;
    font-size: 46px;
    color: #111;
    line-height: 1.15;
    padding-bottom: 2mm;
    border-bottom: 1.5px solid #f26522;
}

/* ── BODY TEXT ── */
.body-text { text-align: center; font-size: 11.5px; color: #333; line-height: 2; }

/* ── COURSE TITLE ── */
.course-title {
    text-align: center;
    font-size: 27px;
    font-weight: 900;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    line-height: 1.15;
    font-family: 'DejaVu Sans', Arial, sans-serif;
}

/* ── CLASSIFICATION ── */
.classification-wrap { text-align: center; width: 100%; }
.classification {
    font-family: 'DancingScript', cursive;
    font-size: 28px;
    color: #111;
    font-weight: bold;
    line-height: 1.2;
}
.class-divider { display: flex; align-items: center; width: 40%; margin: 1.5mm auto 0 auto; }
.cdline { flex: 1; height: 1px; background: #f26522; }
.cddot { color: #f26522; font-size: 10px; padding: 0 4px; }

/* ── GRADUATION DATE ── */
.grad-section { text-align: center; font-size: 11.5px; color: #333; line-height: 2.1; }
.g-day { font-size: 16px; font-weight: bold; color: #1e3a8a; }
.g-month { font-size: 16px; font-weight: bold; font-style: italic; color: #1e3a8a; }
.g-year { font-size: 24px; font-weight: bold; color: #1e3a8a; }
sup { font-size: 7px; }

/* ── SIGNATURES + MEDAL ── */
.sig-section { width: 100%; display: flex; align-items: center; justify-content: space-between; }
.sig-half { width: 30%; display: flex; flex-direction: column; gap: 6mm; }
.sig-item { text-align: center; }
.sig-line { display: block; width: 75%; margin: 0 auto 2px auto; border-top: 1px solid #555; }
.sig-label { font-size: 8.5px; color: #333; font-weight: 600; }
.medal-col { width: 40%; text-align: center; }
.medal-outer {
    width: 76px; height: 76px;
    border-radius: 50%;
    background: radial-gradient(circle at 35% 35%, #2a52c0, #1e3a8a 60%, #162e72);
    border: 3.5px solid #d4af37;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 0 1.5px #b8962e, inset 0 0 10px rgba(0,0,0,0.3);
}
.medal-star { color: #d4af37; font-size: 20px; line-height: 1; margin-bottom: 1px; }
.medal-word { color: #d4af37; font-size: 6.5px; font-weight: bold; letter-spacing: 0.3px; line-height: 1; }
.medal-wreath { display: flex; align-items: center; gap: 2px; margin-top: 1px; }
.medal-wreath span { color: #d4af37; font-size: 9px; }

/* ── INFO BAR ── */
.info-bar {
    width: 100%;
    border: 2px solid #f26522;
    border-radius: 5px;
    padding: 3mm 4mm;
}
.info-row { display: flex; align-items: stretch; }
.info-half { flex: 1; display: flex; flex-direction: column; gap: 2.5mm; }
.info-item { display: flex; align-items: center; gap: 2.5mm; }
.info-icon-circle {
    width: 11mm; height: 11mm;
    border-radius: 50%;
    background: #EEF2FF;
    border: 1.5px solid #1e3a8a;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 12px;
    color: #1e3a8a;
}
.info-label { font-size: 6.5px; font-weight: 900; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 0.8mm; }
.info-value { font-size: 12.5px; font-weight: bold; color: #111; line-height: 1.1; }
.info-sep { width: 10mm; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.sep-line { flex: 1; width: 1px; background: #f26522; }
.sep-diamond { color: #f26522; font-size: 12px; line-height: 1; }

/* ── FOOTER ── */
.footer-div { display: flex; align-items: center; width: 100%; }
.fdline { flex: 1; height: 1px; background: #f26522; }
.fddiamond { color: #f26522; font-size: 12px; padding: 0 4px; }
</style>
</head>
<body>
<div class="frame-outer">

  <!-- CORNERS -->
  <svg class="corner corner-tl" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
    <polygon points="0,0 80,0 0,80" fill="#f26522"/>
    <polygon points="0,0 60,0 0,60" fill="#1e3a8a"/>
    <polygon points="0,0 38,0 0,38" fill="#f26522"/>
    <line x1="0" y1="80" x2="80" y2="0" stroke="#fff" stroke-width="2"/>
  </svg>
  <svg class="corner corner-tr" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
    <polygon points="80,0 80,80 0,0" fill="#f26522"/>
    <polygon points="80,0 80,60 20,0" fill="#1e3a8a"/>
    <polygon points="80,0 80,38 42,0" fill="#f26522"/>
    <line x1="0" y1="0" x2="80" y2="80" stroke="#fff" stroke-width="2"/>
  </svg>
  <svg class="corner corner-bl" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
    <polygon points="0,80 0,0 80,80" fill="#f26522"/>
    <polygon points="0,80 0,20 60,80" fill="#1e3a8a"/>
    <polygon points="0,80 0,42 38,80" fill="#f26522"/>
    <line x1="0" y1="0" x2="80" y2="80" stroke="#fff" stroke-width="2"/>
  </svg>
  <svg class="corner corner-br" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
    <polygon points="80,80 0,80 80,0" fill="#f26522"/>
    <polygon points="80,80 20,80 80,20" fill="#1e3a8a"/>
    <polygon points="80,80 42,80 80,42" fill="#f26522"/>
    <line x1="0" y1="80" x2="80" y2="0" stroke="#fff" stroke-width="2"/>
  </svg>

  <div class="frame-blue">
    <div class="frame-white">
      <div class="content">

        <!-- HEADER -->
        <div class="header">
          <div style="width:28mm;flex-shrink:0;">
            <svg width="54" height="54" viewBox="0 0 52 60" xmlns="http://www.w3.org/2000/svg" style="display:block;margin:0 auto 1.5mm auto;">
              <path d="M26,1 L50,10 L50,38 Q50,52 26,59 Q2,52 2,38 L2,10 Z" fill="#f26522"/>
              <path d="M26,5 L46,13 L46,37 Q46,49 26,55 Q6,49 6,37 L6,13 Z" fill="#1e3a8a"/>
              <rect x="17" y="28" width="18" height="16" fill="white" rx="1"/>
              <polygon points="13,29 26,18 39,29" fill="white"/>
              <rect x="22" y="32" width="8" height="12" fill="#1e3a8a"/>
              <rect x="2" y="1" width="48" height="10" rx="2" fill="#f26522"/>
              <text x="26" y="9.5" font-family="DejaVu Sans" font-size="7" font-weight="900" fill="white" text-anchor="middle">EduTrack®</text>
            </svg>
            <div style="font-size:6.5px;font-weight:700;color:#1e3a8a;text-align:center;line-height:1.2;">Excel Through Education</div>
            <div style="font-size:4.5px;color:#555;text-align:center;line-height:1.4;margin-top:1px;font-weight:600;text-transform:uppercase;">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
          </div>

          <div class="header-center">
            <div class="college-name">EDUTRACK COMPUTER<br>TRAINING COLLEGE</div>
            <div class="header-divider">
              <div class="hdline"></div>
              <div class="hddiamond">&#9670;</div>
              <div class="hdline"></div>
            </div>
            <div class="tagline">A skill training college</div>
          </div>

          <div class="logo-teveta">
            <div class="teveta-box">
              <div class="teveta-icon"><span>7</span></div>
              <div>
                <div class="teveta-name">TEVETA</div>
                <div class="teveta-sub">Computer<br>Education</div>
              </div>
            </div>
          </div>
        </div>

        <!-- CERTIFY BANNER -->
        <div class="certify-banner">
          <span class="banner-deco">&#8594;&#8594;&#9830;</span>
          <span class="banner-text">THIS IS TO CERTIFY THAT</span>
          <span class="banner-deco">&#9830;&#8592;&#8592;</span>
        </div>

        <!-- STUDENT NAME -->
        <div class="name-section">
          <div class="student-name">Catherine Namakanda</div>
        </div>

        <!-- BODY TEXT -->
        <div class="body-text">
          having satisfied the requirements for the<br>
          award of the certificate of
        </div>

        <!-- COURSE TITLE -->
        <div class="course-title">GENERAL BASIC COMPUTING</div>

        <!-- CLASSIFICATION -->
        <div class="classification-wrap">
          <div class="classification">With Merit</div>
          <div class="class-divider">
            <div class="cdline"></div>
            <div class="cddot">&#9670;</div>
            <div class="cdline"></div>
          </div>
        </div>

        <!-- GRADUATION DATE -->
        <div class="grad-section">
          was admitted to the certificate&nbsp; at a Graduation<br>
          Ceremony held on the
          <span class="g-day">&nbsp;27<sup>th</sup>&nbsp;</span>
          day of
          <span class="g-month">&nbsp;March&nbsp;</span><br>
          in the year
          <span class="g-year">&nbsp;2026&nbsp;</span>
        </div>

        <!-- SIGNATURES + MEDAL -->
        <div class="sig-section">
          <div class="sig-half">
            <div class="sig-item">
              <span class="sig-line"></span>
              <div class="sig-label">Principal</div>
            </div>
            <div class="sig-item">
              <span class="sig-line"></span>
              <div class="sig-label">Graduate's Signature</div>
            </div>
          </div>
          <div class="medal-col">
            <div class="medal-outer">
              <div class="medal-star">&#9733;</div>
              <div class="medal-word">EXCELLENCE</div>
              <div class="medal-wreath">
                <span>&#10050;</span>
                <span style="font-size:7px;">&#9830;</span>
                <span>&#10050;</span>
              </div>
            </div>
            <div style="display:flex;justify-content:center;gap:4px;margin-top:0;">
              <div style="width:16px;height:22px;background:#f26522;clip-path:polygon(0 0,100% 0,100% 75%,50% 100%,0 75%);"></div>
              <div style="width:16px;height:22px;background:#f26522;clip-path:polygon(0 0,100% 0,100% 75%,50% 100%,0 75%);"></div>
            </div>
          </div>
          <div class="sig-half">
            <div class="sig-item">
              <span class="sig-line"></span>
              <div class="sig-label">Director</div>
            </div>
            <div class="sig-item">
              <span class="sig-line"></span>
              <div class="sig-label">Graduate's I.D. No.</div>
            </div>
          </div>
        </div>

        <!-- INFO BAR -->
        <div class="info-bar">
          <div class="info-row">
            <div class="info-half">
              <div class="info-item">
                <div class="info-icon-circle">&#127891;</div>
                <div>
                  <div class="info-label">Student Number</div>
                  <div class="info-value">26Edu249580</div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon-circle">&#128196;</div>
                <div>
                  <div class="info-label">Certificate Number</div>
                  <div class="info-value">NRC 2495807/1/1</div>
                </div>
              </div>
            </div>
            <div class="info-sep">
              <div class="sep-line"></div>
              <div class="sep-diamond">&#9670;</div>
              <div class="sep-line"></div>
            </div>
            <div class="info-half">
              <div class="info-item">
                <div class="info-icon-circle">&#128197;</div>
                <div>
                  <div class="info-label">Date of Graduation</div>
                  <div class="info-value">27<sup>th</sup> March 2026</div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon-circle" style="color:#f26522;">&#127941;</div>
                <div>
                  <div class="info-label">Course</div>
                  <div class="info-value">General Basic Computing</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- FOOTER -->
        <div class="footer-div">
          <div class="fdline"></div>
          <div class="fddiamond">&#9670;</div>
          <div class="fdline"></div>
        </div>

      </div>
    </div>
  </div>
</div>
</body>
</html>