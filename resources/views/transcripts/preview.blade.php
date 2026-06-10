<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Academic Transcript - {{ $student_name ?? 'Student Name' }} - EduTrack Computer Training College</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
  @page { size: A4 portrait; margin: 12mm; }

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  :root {
    --navy: #1B3A6B;
    --navy-light: #2A4A7A;
    --dark: #1a1a2e;
    --gray: #4a5568;
    --light-gray: #f7fafc;
    --border: #e2e8f0;
    --gold: #D4952A;
  }

  html, body {
    width: 100%;
    min-height: 100vh;
    background: #e8e8e8;
    font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 9.5pt;
    line-height: 1.5;
    color: var(--dark);
  }

  .sheet {
    width: 210mm;
    min-height: 297mm;
    background: #fff;
    margin: 20px auto;
    padding: 14mm 16mm;
    position: relative;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
  }

  /* ===== HEADER ===== */
  .header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 3px solid var(--navy);
    padding-bottom: 12px;
    margin-bottom: 16px;
  }
  .header-left {
    display: flex;
    align-items: center;
    gap: 14px;
  }
  .header-logo {
    height: 56px;
    width: auto;
  }
  .header-text h1 {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 18pt;
    font-weight: 700;
    color: var(--navy);
    line-height: 1.2;
    letter-spacing: 0.3px;
  }
  .header-text .sub {
    font-size: 8.5pt;
    color: var(--gray);
    margin-top: 2px;
  }
  .header-right {
    text-align: right;
    font-size: 8pt;
    color: var(--gray);
    line-height: 1.6;
  }
  .header-right .teveta {
    font-weight: 700;
    color: var(--navy);
    font-size: 8.5pt;
  }

  .doc-title {
    text-align: center;
    margin: 10px 0 14px;
  }
  .doc-title h2 {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 16pt;
    font-weight: 700;
    color: var(--navy);
    letter-spacing: 1px;
    text-transform: uppercase;
  }
  .doc-title .sub {
    font-size: 8pt;
    color: var(--gray);
    margin-top: 2px;
  }

  /* ===== STUDENT INFO ===== */
  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 24px;
    background: var(--light-gray);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 10px 14px;
    margin-bottom: 14px;
  }
  .info-row {
    display: flex;
    gap: 6px;
  }
  .info-row .label {
    font-weight: 600;
    color: var(--navy);
    min-width: 110px;
    font-size: 8.5pt;
  }
  .info-row .value {
    color: var(--dark);
    font-size: 8.5pt;
  }

  /* ===== SUMMARY BAR ===== */
  .summary-bar {
    display: flex;
    justify-content: space-between;
    background: var(--navy);
    color: #fff;
    padding: 8px 16px;
    border-radius: 4px;
    margin-bottom: 14px;
  }
  .summary-item {
    text-align: center;
  }
  .summary-item .num {
    font-size: 14pt;
    font-weight: 700;
    line-height: 1.1;
  }
  .summary-item .lbl {
    font-size: 7.5pt;
    opacity: 0.85;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* ===== COURSE SECTION ===== */
  .course-section {
    margin-bottom: 16px;
    page-break-inside: avoid;
  }
  .course-header {
    background: var(--navy-light);
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px 4px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .course-header .title {
    font-weight: 700;
    font-size: 9.5pt;
  }
  .course-header .meta {
    font-size: 8pt;
    opacity: 0.9;
    text-align: right;
  }

  .course-meta-bar {
    display: flex;
    gap: 16px;
    padding: 6px 12px;
    background: #edf2f7;
    border-left: 1px solid var(--border);
    border-right: 1px solid var(--border);
    font-size: 8pt;
    color: var(--gray);
  }
  .course-meta-bar span {
    display: flex;
    gap: 4px;
  }
  .course-meta-bar strong {
    color: var(--navy);
  }

  /* ===== TABLES ===== */
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5pt;
  }
  th {
    background: var(--navy);
    color: #fff;
    font-weight: 600;
    text-align: left;
    padding: 5px 8px;
    font-size: 8pt;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  td {
    padding: 4px 8px;
    border-bottom: 1px solid var(--border);
    vertical-align: top;
  }
  tr:nth-child(even) td {
    background: #fafbfc;
  }
  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .font-bold { font-weight: 700; }
  .text-navy { color: var(--navy); }

  .grade-A { color: #276749; font-weight: 700; }
  .grade-B { color: #2f855a; font-weight: 700; }
  .grade-C { color: #c05621; font-weight: 700; }
  .grade-D { color: #c53030; font-weight: 700; }
  .grade-F { color: #9b2c2c; font-weight: 700; }

  .course-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 12px;
    background: var(--light-gray);
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 4px 4px;
    font-size: 8.5pt;
  }
  .course-footer .final {
    font-weight: 700;
    color: var(--navy);
  }
  .course-footer .classification {
    background: var(--gold);
    color: #fff;
    padding: 2px 10px;
    border-radius: 3px;
    font-weight: 700;
    font-size: 8pt;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* ===== GRADING SCALE ===== */
  .scale-box {
    margin-top: 14px;
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 10px 14px;
  }
  .scale-box h4 {
    font-size: 8.5pt;
    color: var(--navy);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .scale-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 4px;
    font-size: 7.5pt;
  }
  .scale-item {
    text-align: center;
    padding: 3px;
    background: var(--light-gray);
    border-radius: 3px;
  }
  .scale-item .grade {
    font-weight: 700;
    color: var(--navy);
  }

  /* ===== FOOTER ===== */
  .transcript-footer {
    margin-top: 20px;
    padding-top: 12px;
    border-top: 2px solid var(--navy);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
  }
  .footer-note {
    font-size: 7.5pt;
    color: var(--gray);
    line-height: 1.5;
    max-width: 65%;
  }
  .signatures {
    display: flex;
    gap: 24px;
    text-align: center;
  }
  .sig-box {
    min-width: 90px;
  }
  .sig-line {
    border-top: 1px solid var(--dark);
    margin-bottom: 3px;
    padding-top: 3px;
  }
  .sig-label {
    font-size: 7pt;
    color: var(--gray);
    font-weight: 600;
  }

  /* ===== PRINT ===== */
  @media print {
    html, body { background: #fff; }
    .sheet {
      box-shadow: none;
      margin: 0;
      padding: 10mm 12mm;
    }
    .no-print { display: none !important; }
    .course-section { page-break-inside: avoid; }
  }

  /* ===== ACTION BAR ===== */
  .action-bar {
    text-align: center;
    padding: 14px;
  }
  .action-bar a, .action-bar button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 6px;
    border: 1px solid var(--navy);
    background: #fff;
    color: var(--navy);
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    margin: 0 4px;
  }
  .action-bar .primary {
    background: var(--navy);
    color: #fff;
  }
</style>
</head>
<body>

<div class="action-bar no-print">
  <a href="{{ route('student.dashboard') }}">&larr; Back to Dashboard</a>
  @if(isset($user) && $user)
  <a class="primary" href="{{ route('transcript.download') }}">Download PDF</a>
  @endif
  <button onclick="window.print()">Print Transcript</button>
</div>

<div class="sheet">

  <!-- HEADER -->
  <div class="header">
    <div class="header-left">
      <img src="{{ asset('assets/images/logo.png') }}" alt="EduTrack Logo" class="header-logo">
      <div class="header-text">
        <h1>EduTrack Computer<br>Training College</h1>
        <div class="sub">A TEVETA-Registered Vocational Training Institution</div>
      </div>
    </div>
    <div class="header-right">
      <div>Kalomo, Zambia</div>
      <div>+260 770 666 937</div>
      <div class="teveta">TEVETA Reg: TVA/2064</div>
    </div>
  </div>

  <!-- TITLE -->
  <div class="doc-title">
    <h2>Official Academic Transcript</h2>
    <div class="sub">This document certifies the academic record of the named student</div>
  </div>

  <!-- STUDENT INFO -->
  <div class="info-grid">
    <div class="info-row">
      <span class="label">Student Name:</span>
      <span class="value font-bold">{{ $student_name ?? 'Catherine Namakanda' }}</span>
    </div>
    <div class="info-row">
      <span class="label">Student Number:</span>
      <span class="value">{{ $student_number ?? '26Edu249580' }}</span>
    </div>
    <div class="info-row">
      <span class="label">NRC Number:</span>
      <span class="value">{{ $national_id ?? 'NRC 249580/11/3' }}</span>
    </div>
    <div class="info-row">
      <span class="label">Date of Birth:</span>
      <span class="value">{{ $date_of_birth ?? '15 May 1998' }}</span>
    </div>
    <div class="info-row">
      <span class="label">Email:</span>
      <span class="value">{{ $email ?? 'catherine@example.com' }}</span>
    </div>
    <div class="info-row">
      <span class="label">Phone:</span>
      <span class="value">{{ $phone ?? '+260 97 123 4567' }}</span>
    </div>
    <div class="info-row">
      <span class="label">Date of Issue:</span>
      <span class="value">{{ $issue_date ?? date('F d, Y') }}</span>
    </div>
    <div class="info-row">
      <span class="label">Transcript Ref:</span>
      <span class="value">{{ $transcript_ref ?? 'TRX-' . date('Ymd') . '-001' }}</span>
    </div>
  </div>

  <!-- SUMMARY BAR -->
  <div class="summary-bar">
    <div class="summary-item">
      <div class="num">{{ $total_courses ?? '3' }}</div>
      <div class="lbl">Total Courses</div>
    </div>
    <div class="summary-item">
      <div class="num">{{ $completed_courses ?? '1' }}</div>
      <div class="lbl">Completed</div>
    </div>
    <div class="summary-item">
      <div class="num">{{ $total_credits ?? '32' }}</div>
      <div class="lbl">Duration (Weeks)</div>
    </div>
  </div>

@php
$demo_enrollments = $enrollments ?? [
    [
        'course_code' => 'ECTC-001',
        'course_title' => 'Certificate in Microsoft Office Suite',
        'level' => 'Beginner',
        'credits' => 8,
        'enrolled_at' => 'January 2025',
        'completion_date' => 'April 2025',
        'status' => 'Completed',
        'final_grade' => 'A',
        'final_score' => '92.5%',
        'classification' => 'Distinction',
        'certificate_number' => 'EDTRK-2026-100039',
        'assessments' => [
            ['code' => 'M01', 'title' => 'Microsoft Word Mastery — Word Practical Project', 'type' => 'Assignment', 'score' => 95, 'max' => 100, 'percentage' => '95%', 'grade' => 'A', 'credits' => '-'],
            ['code' => 'M01', 'title' => 'Microsoft Word Mastery — Module Quiz', 'type' => 'Graded', 'score' => 88, 'max' => 100, 'percentage' => '88%', 'grade' => 'B+', 'credits' => '-'],
            ['code' => 'M02', 'title' => 'Microsoft Excel Mastery — Excel Budget Project', 'type' => 'Assignment', 'score' => 92, 'max' => 100, 'percentage' => '92%', 'grade' => 'A', 'credits' => '-'],
            ['code' => 'M02', 'title' => 'Microsoft Excel Mastery — Module Quiz', 'type' => 'Graded', 'score' => 85, 'max' => 100, 'percentage' => '85%', 'grade' => 'B+', 'credits' => '-'],
            ['code' => 'M03', 'title' => 'PowerPoint & Publisher — Final Assessment', 'type' => 'Final Exam', 'score' => 96, 'max' => 100, 'percentage' => '96%', 'grade' => 'A', 'credits' => '-'],
        ],
    ],
    [
        'course_code' => 'ECTC-005',
        'course_title' => 'Certificate in Python Programming',
        'level' => 'Beginner',
        'credits' => 12,
        'enrolled_at' => 'May 2026',
        'completion_date' => 'In Progress',
        'status' => 'In Progress',
        'final_grade' => 'B',
        'final_score' => '75.0%',
        'classification' => null,
        'certificate_number' => null,
        'assessments' => [
            ['code' => 'M01', 'title' => 'Introduction to Python — Control Flow Quiz', 'type' => 'Graded', 'score' => 78, 'max' => 100, 'percentage' => '78%', 'grade' => 'B', 'credits' => '-'],
            ['code' => 'M02', 'title' => 'Data Types and Variables — Data Structures Assignment', 'type' => 'Assignment', 'score' => 82, 'max' => 100, 'percentage' => '82%', 'grade' => 'B+', 'credits' => '-'],
            ['code' => 'M03', 'title' => 'Control Flow — Python Basics Quiz', 'type' => 'Practice', 'score' => 65, 'max' => 100, 'percentage' => '65%', 'grade' => 'C+', 'credits' => '-'],
        ],
    ],
];
@endphp

  <!-- COURSE SECTIONS -->
  @foreach($demo_enrollments as $enrollment)
  <div class="course-section">
    <div class="course-header">
      <div class="title">{{ $enrollment['course_code'] }} — {{ $enrollment['course_title'] }}</div>
      <div class="meta">
        Level: {{ $enrollment['level'] }} | Duration: {{ $enrollment['credits'] }} weeks
      </div>
    </div>
    <div class="course-meta-bar">
      <span><strong>Enrolled:</strong> {{ $enrollment['enrolled_at'] }}</span>
      <span><strong>Completed:</strong> {{ $enrollment['completion_date'] }}</span>
      <span><strong>Status:</strong> {{ $enrollment['status'] }}</span>
      @if(!empty($enrollment['certificate_number']))
      <span><strong>Cert No:</strong> {{ $enrollment['certificate_number'] }}</span>
      @endif
    </div>

    <table>
      <thead>
        <tr>
          <th style="width: 8%">Code</th>
          <th style="width: 32%">Module / Assessment</th>
          <th style="width: 12%">Type</th>
          <th style="width: 10%" class="text-center">Score</th>
          <th style="width: 10%" class="text-center">Max</th>
          <th style="width: 10%" class="text-center">%</th>
          <th style="width: 10%" class="text-center">Grade</th>
          <th style="width: 8%" class="text-center">Duration</th>
        </tr>
      </thead>
      <tbody>
        @foreach($enrollment['assessments'] as $item)
        <tr>
          <td class="text-navy font-bold">{{ $item['code'] ?? '-' }}</td>
          <td>{{ $item['title'] ?? 'Assessment' }}</td>
          <td style="color: var(--gray);">{{ $item['type'] ?? 'Assignment' }}</td>
          <td class="text-center font-bold">{{ $item['score'] ?? '85' }}</td>
          <td class="text-center">{{ $item['max'] ?? '100' }}</td>
          <td class="text-center">{{ $item['percentage'] ?? '85%' }}</td>
          <td class="text-center {{ $item['grade'] === '-' ? '' : 'grade-' . ($item['grade'][0] ?? 'B') }}">{{ $item['grade'] ?? 'B+' }}</td>
          <td class="text-center">{{ $item['credits'] ?? '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="course-footer">
      <div>
        @if($enrollment['status'] === 'Completed')
        <strong>Final Grade:</strong>
        <span class="final">{{ $enrollment['final_grade'] }} ({{ $enrollment['final_score'] }})</span>
        @else
        <strong>Status:</strong>
        <span class="final" style="color: var(--gray);">{{ $enrollment['status'] }} ({{ $enrollment['final_score'] }})</span>
        @endif
      </div>
      @if(!empty($enrollment['classification']) && $enrollment['status'] === 'Completed' && strcasecmp($enrollment['classification'], 'Pass') !== 0)
      <div class="classification">{{ $enrollment['classification'] }}</div>
      @endif
    </div>
  </div>
  @endforeach

  <!-- GRADING SCALE -->
  <div class="scale-box">
    <h4>Grading Scale</h4>
    <div class="scale-grid">
      <div class="scale-item"><div class="grade">A</div><div>90-100%</div><div>Distinction</div></div>
      <div class="scale-item"><div class="grade">B+</div><div>80-89%</div><div>Merit</div></div>
      <div class="scale-item"><div class="grade">B</div><div>70-79%</div><div>Merit</div></div>
      <div class="scale-item"><div class="grade">C+</div><div>60-69%</div><div>Credit</div></div>
      <div class="scale-item"><div class="grade">C</div><div>50-59%</div><div>Pass</div></div>
      <div class="scale-item"><div class="grade">D</div><div>Below 50%</div><div>Fail</div></div>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="transcript-footer">
    <div class="footer-note">
      This transcript is an official record of academic achievement issued by EduTrack Computer Training College.
      It certifies that the above-named student has completed the listed modules and assessments to the standard indicated.
      <br><br>
      Verify authenticity at: <strong>{{ url('/certificates/verify') }}</strong> &middot; Ref: {{ $transcript_ref ?? 'TRX-' . date('Ymd') . '-001' }}
    </div>
    <div class="signatures">
      <div class="sig-box">
        <div class="sig-line">Principal</div>
        <div class="sig-label">Principal</div>
      </div>
      <div class="sig-box">
        <div class="sig-line">Registrar</div>
        <div class="sig-label">Registrar</div>
      </div>
    </div>
  </div>

</div>

</body>
</html>
