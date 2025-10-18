<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" border="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #2E70DA 0%, #1E4A8A 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Verify Your Email</h1>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #111827; margin: 0 0 20px 0;">Hello <?= sanitize($name) ?>!</h2>
                            
                            <p style="color: #374151; line-height: 1.6; margin: 0 0 20px 0;">
                                Thank you for registering with Edutrack Computer Training College. 
                                Please verify your email address to activate your account.
                            </p>
                            
                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= sanitize($verifyUrl) ?>" style="background-color: #F6B745; color: #111827; text-decoration: none; padding: 15px 40px; border-radius: 6px; display: inline-block; font-weight: bold;">
                                            Verify Email Address
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #6B7280; line-height: 1.6; margin: 20px 0 0 0; font-size: 14px;">
                                Or copy and paste this link into your browser:<br>
                                <a href="<?= sanitize($verifyUrl) ?>" style="color: #2E70DA; word-break: break-all;"><?= sanitize($verifyUrl) ?></a>
                            </p>
                            
                            <p style="color: #9CA3AF; line-height: 1.6; margin: 30px 0 0 0; font-size: 12px;">
                                If you didn't create an account, please ignore this email.
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px; text-center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9CA3AF; margin: 0; font-size: 12px;">
                                © <?= date('Y') ?> <?= APP_NAME ?> | TEVETA: <?= TEVETA_CODE ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>