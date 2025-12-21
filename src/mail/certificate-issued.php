<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Issued</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #fd7e14 0%, #dc6502 100%); padding: 40px 30px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 10px;">🏆</div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                Congratulations!
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #ffe5d0; font-size: 16px;">
                                Your Certificate is Ready
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 24px; font-weight: 600;">
                                Well Done, <?= htmlspecialchars($first_name) ?>! 🎉
                            </h2>

                            <p style="margin: 0 0 16px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                We are thrilled to inform you that you have successfully completed the course and your certificate of completion has been issued!
                            </p>

                            <p style="margin: 0 0 25px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                This is a significant achievement and a testament to your dedication and hard work throughout the course.
                            </p>

                            <!-- Certificate Info Card -->
                            <div style="background: linear-gradient(135deg, #fff8f0 0%, #ffe8d1 100%); border: 3px solid #fd7e14; padding: 30px; margin: 25px 0; border-radius: 8px; text-align: center;">
                                <div style="font-size: 40px; margin-bottom: 15px;">📜</div>
                                <h3 style="margin: 0 0 20px 0; color: #fd7e14; font-size: 22px; font-weight: 700;">
                                    Certificate of Completion
                                </h3>
                                <p style="margin: 0 0 10px 0; color: #333333; font-size: 18px; font-weight: 600;">
                                    <?= htmlspecialchars($course_title) ?>
                                </p>
                                <div style="margin: 20px 0; padding: 20px; background-color: #ffffff; border-radius: 6px;">
                                    <table width="100%" cellpadding="8" cellspacing="0">
                                        <tr>
                                            <td style="color: #666666; font-size: 13px; text-align: left;">
                                                <strong>Certificate Number:</strong>
                                            </td>
                                            <td style="color: #fd7e14; font-size: 14px; font-weight: 600; text-align: right;">
                                                <?= htmlspecialchars($certificate_number) ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #666666; font-size: 13px; text-align: left;">
                                                <strong>Issued Date:</strong>
                                            </td>
                                            <td style="color: #333333; font-size: 14px; text-align: right;">
                                                <?= htmlspecialchars($issued_date) ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #666666; font-size: 13px; text-align: left;">
                                                <strong>Verification Code:</strong>
                                            </td>
                                            <td style="color: #333333; font-size: 14px; font-weight: 600; text-align: right; font-family: monospace;">
                                                <?= htmlspecialchars($verification_code) ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div style="background-color: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #0c5460; font-size: 14px; line-height: 1.5;">
                                    <strong>🔒 Certificate Verification:</strong> Your certificate can be verified by anyone using the verification code above at our verification portal.
                                </p>
                            </div>

                            <!-- CTA Buttons -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 5px;">
                                                    <a href="<?= htmlspecialchars($download_url) ?>"
                                                       style="display: inline-block; padding: 16px 30px; background-color: #fd7e14; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: 600; box-shadow: 0 2px 4px rgba(253, 126, 20, 0.3);">
                                                        📥 Download Certificate
                                                    </a>
                                                </td>
                                                <td style="padding: 5px;">
                                                    <a href="<?= htmlspecialchars($verify_url) ?>"
                                                       style="display: inline-block; padding: 16px 30px; background-color: #ffffff; color: #fd7e14; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: 600; border: 2px solid #fd7e14;">
                                                        🔍 Verify Certificate
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
                                <h3 style="margin: 0 0 15px 0; color: #333333; font-size: 18px; font-weight: 600;">
                                    📋 What's Next?
                                </h3>
                                <ul style="margin: 0; padding-left: 20px; color: #555555; font-size: 15px; line-height: 1.8;">
                                    <li><strong>Share Your Achievement:</strong> Add your certificate to LinkedIn, social media, or your resume</li>
                                    <li><strong>Continue Learning:</strong> Explore our advanced courses to further develop your skills</li>
                                    <li><strong>Join Our Community:</strong> Connect with fellow graduates and industry professionals</li>
                                    <li><strong>Leave a Review:</strong> Help others by sharing your learning experience</li>
                                </ul>
                            </div>

                            <div style="background-color: #f8f9fa; padding: 20px; margin: 25px 0; border-radius: 6px; text-align: center;">
                                <p style="margin: 0 0 10px 0; color: #333333; font-size: 15px;">
                                    💼 <strong>Boost Your Career</strong>
                                </p>
                                <p style="margin: 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                    Add this certificate to your professional profile and showcase your new skills to potential employers. You've earned it!
                                </p>
                            </div>

                            <p style="margin: 25px 0 0 0; color: #555555; font-size: 16px; line-height: 1.6; text-align: center; font-style: italic;">
                                "Education is the most powerful weapon which you can use to change the world."<br>
                                <span style="color: #999999; font-size: 14px;">- Nelson Mandela</span>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                                Questions about your certificate?
                            </p>
                            <p style="margin: 0 0 20px 0; color: #fd7e14; font-size: 14px; font-weight: 600;">
                                certificates@edutrackzambia.com
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
