<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); padding: 40px 30px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 10px;">💳</div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                Payment Received
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #e4d9f7; font-size: 16px;">
                                Thank you for your payment
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 24px; font-weight: 600;">
                                Dear <?= htmlspecialchars($first_name) ?>,
                            </h2>

                            <p style="margin: 0 0 25px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                We have successfully received your payment. This email serves as your official receipt for this transaction.
                            </p>

                            <!-- Receipt Card -->
                            <div style="border: 2px solid #6f42c1; border-radius: 8px; overflow: hidden; margin: 25px 0;">
                                <div style="background-color: #6f42c1; padding: 15px; text-align: center;">
                                    <h3 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 600;">
                                        PAYMENT RECEIPT
                                    </h3>
                                </div>
                                <div style="padding: 25px; background-color: #f8f9fa;">
                                    <table width="100%" cellpadding="10" cellspacing="0">
                                        <tr style="border-bottom: 1px solid #e0e0e0;">
                                            <td style="color: #666666; font-size: 14px; padding: 12px 0;">
                                                <strong>Transaction ID:</strong>
                                            </td>
                                            <td style="color: #333333; font-size: 14px; text-align: right; padding: 12px 0;">
                                                <?= htmlspecialchars($transaction_id) ?>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #e0e0e0;">
                                            <td style="color: #666666; font-size: 14px; padding: 12px 0;">
                                                <strong>Amount Paid:</strong>
                                            </td>
                                            <td style="color: #6f42c1; font-size: 20px; font-weight: 700; text-align: right; padding: 12px 0;">
                                                <?= htmlspecialchars($currency) ?> <?= htmlspecialchars($amount) ?>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #e0e0e0;">
                                            <td style="color: #666666; font-size: 14px; padding: 12px 0;">
                                                <strong>Payment Method:</strong>
                                            </td>
                                            <td style="color: #333333; font-size: 14px; text-align: right; padding: 12px 0;">
                                                <?= htmlspecialchars($payment_method) ?>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #e0e0e0;">
                                            <td style="color: #666666; font-size: 14px; padding: 12px 0;">
                                                <strong>Date & Time:</strong>
                                            </td>
                                            <td style="color: #333333; font-size: 14px; text-align: right; padding: 12px 0;">
                                                <?= htmlspecialchars($transaction_date) ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #666666; font-size: 14px; padding: 12px 0;">
                                                <strong>Description:</strong>
                                            </td>
                                            <td style="color: #333333; font-size: 14px; text-align: right; padding: 12px 0;">
                                                <?= htmlspecialchars($description) ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #155724; font-size: 14px; line-height: 1.5;">
                                    <strong>✓ Payment Confirmed:</strong> This payment has been successfully processed and credited to your account.
                                </p>
                            </div>

                            <p style="margin: 25px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Please keep this receipt for your records. If you need an official invoice, you can download it from your account dashboard.
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="<?= htmlspecialchars($receipt_url) ?>"
                                           style="display: inline-block; padding: 16px 40px; background-color: #6f42c1; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; box-shadow: 0 2px 4px rgba(111, 66, 193, 0.3);">
                                            Download Receipt
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
                                <h3 style="margin: 0 0 15px 0; color: #333333; font-size: 16px; font-weight: 600;">
                                    Important Information:
                                </h3>
                                <ul style="margin: 0; padding-left: 20px; color: #666666; font-size: 14px; line-height: 1.8;">
                                    <li>This receipt is automatically generated and is valid without signature</li>
                                    <li>Please allow 24-48 hours for payment to reflect in your account</li>
                                    <li>Keep this receipt for tax purposes and future reference</li>
                                    <li>For any discrepancies, contact us within 30 days</li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                                Questions about this payment?
                            </p>
                            <p style="margin: 0 0 20px 0; color: #6f42c1; font-size: 14px; font-weight: 600;">
                                billing@edutrackzambia.com | +260 XXX XXX XXX
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
