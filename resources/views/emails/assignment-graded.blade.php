<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Graded</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #4f46e5; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 20px; }
        .body { padding: 30px; }
        .score { text-align: center; padding: 20px; background: #eef2ff; border-radius: 8px; margin: 20px 0; }
        .score-number { font-size: 36px; font-weight: bold; color: #4f46e5; }
        .score-label { font-size: 14px; color: #6b7280; }
        .feedback { background: #f9fafb; border-left: 4px solid #4f46e5; padding: 15px; margin-top: 20px; border-radius: 0 8px 8px 0; }
        .footer { padding: 20px 30px; text-align: center; font-size: 12px; color: #9ca3af; background: #f9fafb; }
        .btn { display: inline-block; padding: 12px 24px; background: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 500; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your Assignment Has Been Graded</h1>
        </div>
        <div class="body">
            <p>Hi {{ $studentName }},</p>
            <p>Your assignment <strong>{{ $assignmentTitle }}</strong> in <strong>{{ $courseTitle }}</strong> has been graded.</p>

            <div class="score">
                <div class="score-number">{{ $pointsEarned }} / {{ $maxPoints }}</div>
                <div class="score-label">Points Earned</div>
            </div>

            @if($feedback)
            <div class="feedback">
                <strong>Instructor Feedback:</strong><br>
                {{ $feedback }}
            </div>
            @endif

            <p style="text-align: center;">
                <a href="{{ url('/student/assignments') }}" class="btn">View Assignment</a>
            </p>
        </div>
        <div class="footer">
            Edutrack Computer Training College &bull; Kalomo, Zambia
        </div>
    </div>
</body>
</html>
