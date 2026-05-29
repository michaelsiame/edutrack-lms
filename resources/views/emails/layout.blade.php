<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Edutrack LMS')</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; color: #374151; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #1e3a5f; padding: 32px 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; letter-spacing: -0.01em; }
        .header p { color: rgba(255,255,255,0.8); margin: 6px 0 0; font-size: 14px; }
        .body { padding: 32px 30px; }
        .body p { margin: 0 0 14px; }
        .card { background: #f8fafc; border-left: 4px solid #1e3a5f; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .card-success { border-left-color: #059669; background: #ecfdf5; }
        .card-warning { border-left-color: #d97706; background: #fffbeb; }
        .btn { display: inline-block; padding: 12px 28px; background: #1e3a5f; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 500; font-size: 15px; margin-top: 10px; }
        .btn-success { background: #059669; }
        .btn-warning { background: #d97706; }
        .footer { padding: 24px 30px; text-align: center; font-size: 12px; color: #9ca3af; background: #f9fafb; border-top: 1px solid #e5e7eb; }
        .footer a { color: #6b7280; text-decoration: none; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }
        .small { font-size: 13px; color: #6b7280; }
        .center { text-align: center; }
        table.meta { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.meta td { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        table.meta td:last-child { text-align: right; font-weight: 500; }
        @media only screen and (max-width: 480px) {
            body { padding: 10px; }
            .header, .body, .footer { padding-left: 20px; padding-right: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edutrack LMS</h1>
            <p>@yield('subtitle', 'Computer Training College')</p>
        </div>
        <div class="body">
            @yield('content')
        </div>
        <div class="footer">
            <p><strong>Edutrack Computer Training College</strong><br>Kalomo, Zambia</p>
            <p>edutrackzambia@gmail.com &bull; +260 770 666 937</p>
            <p style="margin-top: 10px;">&copy; {{ date('Y') }} Edutrack LMS. All rights reserved.</p>
            @yield('footer_extra')
        </div>
    </div>
</body>
</html>
