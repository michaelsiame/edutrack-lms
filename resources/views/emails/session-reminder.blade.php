<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Session Reminder - Edutrack LMS</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f4f4f4; }
        .container { background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #3b82f6; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { color: #1e40af; margin: 0; font-size: 22px; }
        .header p { color: #666; margin: 5px 0 0; font-size: 14px; }
        .content { margin-bottom: 25px; }
        .session-card { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 20px 0; border-radius: 0 6px 6px 0; }
        .session-card h2 { color: #1e40af; margin-top: 0; font-size: 18px; }
        .detail { margin: 10px 0; }
        .detail strong { color: #1e40af; display: inline-block; width: 100px; }
        .btn { display: inline-block; background: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold; margin-top: 15px; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edutrack LMS</h1>
            <p>Live Session Reminder</p>
        </div>

        <div class="content">
            <p>Hello {{ $student->first_name ?? 'Student' }},</p>
            <p>This is a friendly reminder that you have an upcoming live session. Here are the details:</p>

            <div class="session-card">
                <h2>{{ $session->title ?? 'Live Session' }}</h2>
                <div class="detail"><strong>Course:</strong> {{ $course->title ?? 'Your Course' }}</div>
                <div class="detail"><strong>Date:</strong> {{ isset($session->scheduled_start_time) ? \Carbon\Carbon::parse($session->scheduled_start_time)->format('l, F j, Y') : 'TBD' }}</div>
                <div class="detail"><strong>Time:</strong> {{ isset($session->scheduled_start_time) ? \Carbon\Carbon::parse($session->scheduled_start_time)->format('g:i A') . ' CAT' : 'TBD' }}</div>
                @if(isset($session->duration_minutes))
                <div class="detail"><strong>Duration:</strong> {{ $session->duration_minutes }} minutes</div>
                @endif
                @if(isset($session->description) && $session->description)
                <div class="detail"><strong>Description:</strong> {{ $session->description }}</div>
                @endif
            </div>

            @if(isset($session->join_url) && $session->join_url)
            <p style="text-align: center;">
                <a href="{{ $session->join_url }}" class="btn">Join Live Session</a>
            </p>
            @endif

            <p style="margin-top: 20px; font-size: 13px; color: #666;">
                Please join a few minutes early to test your audio and video. If you have any issues, contact us at edutrackzambia@gmail.com or +260 770 666 937.
            </p>
        </div>

        <div class="footer">
            <p>Edutrack Computer Training College<br>Kalomo, Zambia</p>
            <p>&copy; {{ date('Y') }} Edutrack LMS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
