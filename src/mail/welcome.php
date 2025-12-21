<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Edutrack LMS</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2E70DA 0%, #1a4fa0 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                Welcome to Edutrack!
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #e8f1ff; font-size: 16px;">
                                Computer Training College
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 24px; font-weight: 600;">
                                Hello <?= htmlspecialchars($first_name) ?>! 👋
                            </h2>

                            <p style="margin: 0 0 16px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Thank you for joining <strong>Edutrack Computer Training College</strong>! We're excited to have you as part of our learning community.
                            </p>

                            <p style="margin: 0 0 16px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Your account has been successfully created and you're all set to start your learning journey with us.
                            </p>

                            <div style="background-color: #f8f9fa; border-left: 4px solid #2E70DA; padding: 20px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0 0 10px 0; color: #333333; font-size: 14px; font-weight: 600;">
                                    Your Login Email:
                                </p>
                                <p style="margin: 0; color: #2E70DA; font-size: 16px; font-weight: 600;">
                                    <?= htmlspecialchars($email) ?>
                                </p>
                            </div>

                            <p style="margin: 0 0 25px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Ready to explore our courses and start learning? Click the button below to access your dashboard.
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="<?= htmlspecialchars($login_url) ?>"
                                           style="display: inline-block; padding: 16px 40px; background-color: #2E70DA; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; box-shadow: 0 2px 4px rgba(46, 112, 218, 0.3);">
                                            Access Your Dashboard
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
                                <h3 style="margin: 0 0 15px 0; color: #333333; font-size: 18px; font-weight: 600;">
                                    What's Next?
                                </h3>
                                <ul style="margin: 0; padding-left: 20px; color: #555555; font-size: 15px; line-height: 1.8;">
                                    <li>Browse our comprehensive course catalog</li>
                                    <li>Complete your profile to get personalized recommendations</li>
                                    <li>Enroll in courses that match your interests</li>
                                    <li>Start learning at your own pace</li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                                Need help getting started? Contact our support team.
                            </p>
                            <p style="margin: 0 0 20px 0; color: #2E70DA; font-size: 14px; font-weight: 600;">
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
