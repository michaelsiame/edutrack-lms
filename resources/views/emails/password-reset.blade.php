<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
 <title>Password Reset - Edutrack LMS</title>
 <style>
 body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
 .container { max-width: 600px; margin: 0 auto; padding: 20px; }
 .header { background: #4f46e5; color: white; padding: 20px; text-align: center; }
 .content { background: #f9fafb; padding: 30px; }
 .button { display: inline-block; padding: 12px 24px; background: #4f46e5; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
 .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
 </style>
</head>
<body>
 <div class="container">
 <div class="header">
 <h1>Password Reset</h1>
 </div>
 <div class="content">
 <p>Hello {{ $user->first_name }},</p>
 <p>You recently requested to reset your password for your Edutrack LMS account. Click the button below to reset it:</p>
 <p style="text-align: center;">
 <a href="{{ $resetUrl }}" class="button">Reset Password</a>
 </p>
 <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
 <p>This password reset link will expire in 60 minutes.</p>
 </div>
 <div class="footer">
 <p>Edutrack Computer Training College<br>Kalomo, Zambia</p>
 </div>
 </div>
</body>
</html>
