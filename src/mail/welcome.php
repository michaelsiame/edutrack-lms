<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Edutrack</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" border="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2E70DA 0%, #1E4A8A 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Welcome to Edutrack!</h1>
                            <p style="color: #F6B745; margin: 10px 0 0 0; font-size: 14px; font-weight: bold;">TEVETA Certified Institution</p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #111827; margin: 0 0 20px 0; font-size: 24px;">Hello <?= sanitize($name) ?>!</h2>
                            
                            <p style="color: #374151; line-height: 1.6; margin: 0 0 20px 0;">
                                Thank you for registering with <strong>Edutrack Computer Training College</strong>. 
                                We're excited to have you join our community of learners!
                            </p>
                            
                            <p style="color: #374151; line-height: 1.6; margin: 0 0 20px 0;">
                                As a TEVETA-registered institution, we're committed to providing you with high-quality, 
                                government-recognized training that will help transform your future.
                            </p>
                            
                            <div style="background-color: #EBF4FF; border-left: 4px solid #2E70DA; padding: 20px; margin: 30px 0;">
                                <h3 style="color: #2E70DA; margin: 0 0 15px 0; font-size: 18px;">What's Next?</h3>
                                <ul style="color: #374151; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>Browse our TEVETA-certified courses</li>
                                    <li>Enroll in courses that match your goals</li>
                                    <li>Start learning at your own pace</li>
                                    <li>Earn government-recognized certificates</li>
                                </ul>
                            </div>
                            
                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= APP_URL ?>" style="background-color: #F6B745; color: #111827; text-decoration: none; padding: 15px 40px; border-radius: 6px; display: inline-block; font-weight: bold; font-size: 16px;">
                                            Start Learning Now
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #6B7280; line-height: 1.6; margin: 30px 0 0 0; font-size: 14px;">
                                If you have any questions, feel free to contact our support team at 
                                <a href="mailto:<?= SITE_EMAIL ?>" style="color: #2E70DA;"><?= SITE_EMAIL ?></a>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6B7280; margin: 0 0 10px 0; font-size: 14px;">
                                <strong><?= APP_NAME ?></strong><br>
                                TEVETA Registration: <?= TEVETA_CODE ?>
                            </p>
                            <p style="color: #9CA3AF; margin: 0; font-size: 12px;">
                                <?= SITE_ADDRESS ?><br>
                                <?= SITE_PHONE ?> | <?= SITE_EMAIL ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>