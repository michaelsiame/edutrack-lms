<?php
/**
 * Admin Payment Received Email Template
 * Variables: admin_name, student_name, student_email, course_title, amount, currency, payment_method, reference_number, transaction_id, payment_status, payment_date, payment_type, payment_url
 */
?>
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2 style="color: #2E70DA;">Payment Received</h2>
    
    <p>Hello <?= htmlspecialchars($admin_name) ?>,</p>
    
    <p>A payment has been successfully processed:</p>
    
    <div style="background: #f8f9fa; padding: 20px; border-left: 4px solid #F6B745; margin: 20px 0;">
        <div style="text-align: center; margin-bottom: 20px;">
            <span style="font-size: 36px; font-weight: bold; color: #2E70DA;">
                <?= htmlspecialchars($currency) ?> <?= htmlspecialchars($amount) ?>
            </span>
            <br>
            <span style="background: #d4edda; color: #155724; padding: 3px 10px; border-radius: 3px; font-size: 12px; text-transform: uppercase;">
                <?= htmlspecialchars($payment_status) ?>
            </span>
        </div>
        
        <table width="100%" cellpadding="5" style="border-collapse: collapse;">
            <tr>
                <td style="color: #666; width: 140px;"><strong>Student:</strong></td>
                <td><?= htmlspecialchars($student_name) ?></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Email:</strong></td>
                <td><a href="mailto:<?= htmlspecialchars($student_email) ?>" style="color: #2E70DA;"><?= htmlspecialchars($student_email) ?></a></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Course:</strong></td>
                <td><?= htmlspecialchars($course_title) ?></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Payment Method:</strong></td>
                <td><?= htmlspecialchars($payment_method) ?></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Reference:</strong></td>
                <td><code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;"><?= htmlspecialchars($reference_number) ?></code></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Transaction ID:</strong></td>
                <td><code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;"><?= htmlspecialchars($transaction_id) ?></code></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Payment Type:</strong></td>
                <td><?= htmlspecialchars(ucfirst($payment_type)) ?></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Date:</strong></td>
                <td><?= htmlspecialchars($payment_date) ?></td>
            </tr>
        </table>
    </div>
    
    <p style="text-align: center; margin: 30px 0;">
        <a href="<?= htmlspecialchars($payment_url) ?>" 
           style="background: #F6B745; color: #212529; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            View Financial Dashboard
        </a>
    </p>
    
    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">
    <p style="color: #666; font-size: 12px;">
        This is an automated notification from EduTrack LMS.<br>
        You are receiving this because you are an administrator or finance officer.
    </p>
</div>
