<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" border="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #2E70DA 0%, #1E4A8A 100%); padding: 40px 30px; text-align: center;">
                            <div style="background-color: rgba(255,255,255,0.1); width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                <span style="color: #F6B745; font-size: 40px;">🔑</span>
                            </div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Password Reset Request</h1>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #111827; margin: 0 0 20px 0;">Hello <?= sanitize($name) ?>!</h2>
                            
                            <p style="color: #374151; line-height: 1.6; margin: 0 0 20px 0;">
                                We received a request to reset the password for your Edutrack account. 
                                Click the button below to create a new password.
                            </p>
                            
                            <div style="background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin: 20px 0;">
                                <p style="color: #92400E; margin: 0; font-size: 14px;">
                                    <strong>⚠️ Security Notice:</strong> This link will expire in 1 hour.
                                </p>
                            </div>
                            
                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= sanitize($resetUrl) ?>" style="background-color: #F6B745; color: #111827; text-decoration: none; padding: 15px 40px; border-radius: 6px; display: inline-block; font-weight: bold; font-size: 16px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #6B7280; line-height: 1.6; margin: 20px 0 0 0; font-size: 14px;">
                                Or copy and paste this link into your browser:<br>
                                <a href="<?= sanitize($resetUrl) ?>" style="color: #2E70DA; word-break: break-all;"><?= sanitize($resetUrl) ?></a>
                            </p>
                            
                            <div style="background-color: #F3F4F6; border-radius: 6px; padding: 20px; margin: 30px 0;">
                                <p style="color: #374151; margin: 0 0 10px 0; font-weight: bold; font-size: 14px;">
                                    Didn't request this?
                                </p>
                                <p style="color: #6B7280; margin: 0; font-size: 14px; line-height: 1.5;">
                                    If you didn't request a password reset, you can safely ignore this email. 
                                    Your password will not be changed.
                                </p>
                            </div>
                            
                            <p style="color: #6B7280; line-height: 1.6; margin: 30px 0 0 0; font-size: 14px;">
                                For security reasons, we recommend:
                            </p>
                            <ul style="color: #6B7280; line-height: 1.6; margin: 10px 0 0 0; padding-left: 20px; font-size: 14px;">
                                <li>Using a strong, unique password</li>
                                <li>Not sharing your password with anyone</li>
                                <li>Changing your password regularly</li>
                            </ul>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6B7280; margin: 0 0 10px 0; font-size: 14px;">
                                <strong><?= APP_NAME ?></strong><br>
                                TEVETA Registration: <?= TEVETA_CODE ?>
                            </p>
                            <p style="color: #9CA3AF; margin: 0 0 15px 0; font-size: 12px;">
                                <?= SITE_ADDRESS ?><br>
                                <?= SITE_PHONE ?> | <?= SITE_EMAIL ?>
                            </p>
                            <p style="color: #9CA3AF; margin: 0; font-size: 11px;">
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