<?php
/**
 * Admin New Enrollment Email Template
 * Variables: admin_name, student_name, student_email, student_phone, course_title, course_price, currency, enrollment_status, payment_status, enrollment_date, enrollment_url
 */
?>
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2 style="color: #2E70DA;">New Course Enrollment</h2>
    
    <p>Hello <?= htmlspecialchars($admin_name) ?>,</p>
    
    <p>A student has just enrolled in a course:</p>
    
    <div style="background: #f8f9fa; padding: 20px; border-left: 4px solid #10B981; margin: 20px 0;">
        <h3 style="margin: 0 0 15px 0; color: #333;"><?= htmlspecialchars($course_title) ?></h3>
        
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
                <td style="color: #666;"><strong>Phone:</strong></td>
                <td><?= htmlspecialchars($student_phone) ?></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Course Price:</strong></td>
                <td style="font-size: 18px; color: #2E70DA; font-weight: bold;">
                    <?= htmlspecialchars($currency) ?> <?= htmlspecialchars($course_price) ?>
                </td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Payment Status:</strong></td>
                <td>
                    <span style="background: <?= $payment_status === 'completed' ? '#d4edda' : '#fff3cd' ?>; 
                                 color: <?= $payment_status === 'completed' ? '#155724' : '#856404' ?>; 
                                 padding: 3px 10px; border-radius: 3px; font-size: 12px; text-transform: uppercase;">
                        <?= htmlspecialchars($payment_status) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Enrollment Status:</strong></td>
                <td><?= htmlspecialchars($enrollment_status) ?></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Enrolled:</strong></td>
                <td><?= htmlspecialchars($enrollment_date) ?></td>
            </tr>
        </table>
    </div>
    
    <p style="text-align: center; margin: 30px 0;">
        <a href="<?= htmlspecialchars($enrollment_url) ?>" 
           style="background: #10B981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
            View Enrollment Details
        </a>
    </p>
    
    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">
    <p style="color: #666; font-size: 12px;">
        This is an automated notification from EduTrack LMS.<br>
        You are receiving this because you are an administrator.
    </p>
</div>
