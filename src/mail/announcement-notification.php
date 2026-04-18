<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Announcement</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2E70DA 0%, #1e4a9c 100%); padding: 40px 30px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 10px;">📢</div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                New Announcement
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #d4e5f7; font-size: 16px;">
                                From your instructor
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
                                Your instructor has posted a new announcement in:
                            </p>

                            <!-- Course Info Card -->
                            <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #2E70DA; padding: 25px; margin: 25px 0; border-radius: 8px;">
                                <h3 style="margin: 0 0 15px 0; color: #2E70DA; font-size: 20px; font-weight: 600;">
                                    <?= htmlspecialchars($course_title) ?>
                                </h3>
                            </div>

                            <!-- Announcement Content -->
                            <div style="background-color: #ffffff; border-left: 4px solid #2E70DA; padding: 20px; margin: 25px 0; border-radius: 4px;">
                                <h4 style="margin: 0 0 10px 0; color: #333333; font-size: 18px; font-weight: 600;">
                                    <?= htmlspecialchars($announcement_title) ?>
                                </h4>
                                <p style="margin: 0; color: #555555; font-size: 15px; line-height: 1.6;">
                                    <?= nl2br(htmlspecialchars($announcement_content)) ?>
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="<?= htmlspecialchars($course_url) ?>"
                                           style="display: inline-block; padding: 16px 40px; background-color: #2E70DA; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; box-shadow: 0 2px 4px rgba(46, 112, 218, 0.3);">
                                            View in Course
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
                                Questions? We're here to help!
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
