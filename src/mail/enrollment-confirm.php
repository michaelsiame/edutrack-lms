<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Confirmation</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); padding: 40px 30px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 10px;">✓</div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                Enrollment Confirmed!
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #d4f5dc; font-size: 16px;">
                                You're ready to start learning
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 24px; font-weight: 600;">
                                Hi <?= htmlspecialchars($first_name) ?>! 🎓
                            </h2>

                            <p style="margin: 0 0 16px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Congratulations! You have been successfully enrolled in:
                            </p>

                            <!-- Course Info Card -->
                            <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #28a745; padding: 25px; margin: 25px 0; border-radius: 8px;">
                                <h3 style="margin: 0 0 15px 0; color: #28a745; font-size: 20px; font-weight: 600;">
                                    <?= htmlspecialchars($course_title) ?>
                                </h3>
                                <table width="100%" cellpadding="8" cellspacing="0">
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; width: 40%;">
                                            <strong>Start Date:</strong>
                                        </td>
                                        <td style="color: #333333; font-size: 14px;">
                                            <?= htmlspecialchars($start_date) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666666; font-size: 14px;">
                                            <strong>Course Fee:</strong>
                                        </td>
                                        <td style="color: #333333; font-size: 14px;">
                                            <?= htmlspecialchars($currency) ?> <?= htmlspecialchars($course_price) ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <p style="margin: 0 0 25px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Your learning journey starts now! Access your course materials, watch video lessons, complete assignments, and interact with instructors and fellow students.
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="<?= htmlspecialchars($course_url) ?>"
                                           style="display: inline-block; padding: 16px 40px; background-color: #28a745; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);">
                                            Start Learning Now
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
                                <h3 style="margin: 0 0 15px 0; color: #333333; font-size: 18px; font-weight: 600;">
                                    📚 Getting Started Tips
                                </h3>
                                <ul style="margin: 0; padding-left: 20px; color: #555555; font-size: 15px; line-height: 1.8;">
                                    <li>Review the course syllabus and learning objectives</li>
                                    <li>Set aside dedicated time for studying each week</li>
                                    <li>Participate actively in discussions and forums</li>
                                    <li>Don't hesitate to ask questions to your instructor</li>
                                    <li>Complete assignments and quizzes on time</li>
                                </ul>
                            </div>

                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.5;">
                                    <strong>💡 Pro Tip:</strong> Download the course materials to access them offline and track your progress regularly to stay motivated!
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                                Questions about your enrollment? We're here to help!
                            </p>
                            <p style="margin: 0 0 20px 0; color: #28a745; font-size: 14px; font-weight: 600;">
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
