<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Reminder - Edutrack LMS</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f4f4f4; }
        .container { background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #f59e0b; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { color: #b45309; margin: 0; font-size: 22px; }
        .header p { color: #666; margin: 5px 0 0; font-size: 14px; }
        .content { margin-bottom: 25px; }
        .progress-card { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0; border-radius: 0 6px 6px 0; }
        .progress-bar { width: 100%; height: 20px; background: #e5e7eb; border-radius: 10px; overflow: hidden; margin: 15px 0; }
        .progress-fill { height: 100%; background: #f59e0b; border-radius: 10px; text-align: center; color: white; font-size: 12px; line-height: 20px; }
        .btn { display: inline-block; background: #f59e0b; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold; margin-top: 15px; }
        .tips { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 6px; margin-top: 20px; }
        .tips h3 { color: #15803d; margin-top: 0; font-size: 15px; }
        .tips ul { margin: 10px 0 0; padding-left: 20px; color: #166534; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edutrack LMS</h1>
            <p>We Miss You!</p>
        </div>

        <div class="content">
            <p>Hello {{ $student->first_name ?? 'Student' }},</p>
            <p>We noticed you haven't been active in your course for a while. Don't worry — you can pick up right where you left off!</p>

            <div class="progress-card">
                <h2 style="color: #b45309; margin-top: 0;">{{ $course->title ?? 'Your Course' }}</h2>
                <p style="margin: 5px 0; color: #666;">Your current progress:</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $progress ?? 0 }}%;">{{ $progress ?? 0 }}%</div>
                </div>
                <p style="font-size: 13px; color: #666; margin-top: 10px;">
                    @if(($progress ?? 0) < 10)
                        You're just getting started! Every expert was once a beginner.
                    @elseif(($progress ?? 0) < 30)
                        Great start! Keep the momentum going.
                    @elseif(($progress ?? 0) < 50)
                        You're making solid progress. Don't stop now!
                    @else
                        You're more than halfway there! Finish strong.
                    @endif
                </p>
            </div>

            <p style="text-align: center;">
                <a href="{{ url('/student/dashboard') }}" class="btn">Continue Learning</a>
            </p>

            <div class="tips">
                <h3>Tips to Stay on Track</h3>
                <ul>
                    <li>Set aside 30 minutes each day for learning</li>
                    <li>Join the course discussion forum to ask questions</li>
                    <li>Attend live sessions for real-time interaction</li>
                    <li>Take notes as you go through each lesson</li>
                </ul>
            </div>

            <p style="margin-top: 20px; font-size: 13px; color: #666;">
                Need help? Reply to this email or contact us at edutrackzambia@gmail.com. We're here to support you every step of the way.
            </p>
        </div>

        <div class="footer">
            <p>Edutrack Computer Training College<br>Kalomo, Zambia</p>
            <p>&copy; {{ date('Y') }} Edutrack LMS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
