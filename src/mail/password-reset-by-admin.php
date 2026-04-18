<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%); padding: 40px 30px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 10px;">🔐</div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                Password Reset
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #f8d7da; font-size: 16px;">
                                Your password has been reset by an administrator
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 24px; font-weight: 600;">
                                Hi <?= htmlspecialchars($first_name) ?>!
                            </h2>

                            <p style="margin: 0 0 16px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                An administrator has reset your password for the Edutrack LMS. Here are your new login credentials:
                            </p>

                            <!-- Credentials Card -->
                            <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #dc3545; padding: 25px; margin: 25px 0; border-radius: 8px;">
                                <h3 style="margin: 0 0 15px 0; color: #dc3545; font-size: 18px; font-weight: 600;">
                                    Your New Password
                                </h3>
                                <table width="100%" cellpadding="8" cellspacing="0">
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; width: 40%;">
                                            <strong>Email:</strong>
                                        </td>
                                        <td style="color: #333333; font-size: 14px;">
                                            <?= htmlspecialchars($email) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666666; font-size: 14px;">
                                            <strong>New Password:</strong>
                                        </td>
                                        <td style="color: #dc3545; font-size: 16px; font-weight: 600; font-family: monospace; background: #fff3cd; padding: 8px; border-radius: 4px;">
                                            <?= htmlspecialchars($new_password) ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.5;">
                                    <strong>⚠️ Important:</strong> For security reasons, please change your password immediately after logging in. Go to your Profile page and select "Change Password".
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="<?= htmlspecialchars($login_url) ?>"
                                           style="display: inline-block; padding: 16px 40px; background-color: #dc3545; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);">
                                            Login Now
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                                Didn't request this password reset? Contact support immediately.
                            </p>
                            <p style="margin: 0 0 20px 0; color: #dc3545; font-size: 14px; font-weight: 600;">
                                support@edutrackzambia.com
                            </p>
                            <p style="margin: 0; color: #999999; font-size: 12px; line-height: 1.5;">
                                © <?= date('Y') ?> Edutrack Computer Training College. All rights reserved.<br>
                                This is an automated email. Please do not reply to this message.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
