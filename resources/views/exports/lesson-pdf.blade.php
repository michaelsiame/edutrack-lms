<style>
    body { font-family: 'DejaVu Serif', serif; font-size: 11pt; line-height: 1.6; color: #0a1628; }
    h1 { font-size: 18pt; font-weight: bold; color: #0a1628; margin-bottom: 6pt; border-bottom: 2pt solid #0b4f8c; padding-bottom: 6pt; }
    h2 { font-size: 14pt; font-weight: bold; color: #1a1a1a; margin-top: 14pt; margin-bottom: 6pt; }
    h3 { font-size: 12pt; font-weight: bold; color: #2a2a2a; margin-top: 10pt; margin-bottom: 4pt; }
    p { margin-bottom: 8pt; }
    ul, ol { margin-bottom: 8pt; padding-left: 20pt; }
    li { margin-bottom: 2pt; }
    pre, code { font-family: 'Courier New', monospace; font-size: 9pt; background: #f3f4f6; padding: 2pt 4pt; border-radius: 2pt; }
    pre { padding: 8pt; overflow-wrap: break-word; }
    blockquote { border-left: 3pt solid #e5e2dc; padding-left: 10pt; margin-left: 0; font-style: italic; color: #6b6f78; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8pt; font-size: 10pt; }
    th, td { border: 0.5pt solid #ccc; padding: 4pt 6pt; text-align: left; }
    th { background: #f7f6f3; font-weight: bold; }
    img { max-width: 100%; height: auto; }
    .header { text-align: center; margin-bottom: 20pt; }
    .header-logo { font-size: 10pt; color: #6b6f78; margin-bottom: 4pt; }
    .course-meta { font-size: 9pt; color: #6b6f78; margin-bottom: 16pt; }
    .footer { font-size: 8pt; color: #999; text-align: center; border-top: 0.5pt solid #ddd; padding-top: 6pt; margin-top: 20pt; }
</style>

<div class="header">
    <div class="header-logo">Edutrack Computer Training College</div>
    <h1>{{ $lesson->title }}</h1>
    <div class="course-meta">{{ $course->title }} &middot; {{ $lesson->module->title ?? '' }}</div>
</div>

<div>
    {!! $content !!}
</div>

<div class="footer">
    &copy; {{ date('Y') }} Edutrack Computer Training College. All rights reserved.
</div>
