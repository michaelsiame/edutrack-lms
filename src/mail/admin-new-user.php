<?php
/**
 * Admin New User Registration Email Template
 * Variables: admin_name, user_name, user_email, user_phone, user_roles, registration_date, user_profile_url
 */
?>
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2 style="color: #2E70DA;">New User Registration</h2>
    
    <p>Hello <?= htmlspecialchars($admin_name) ?>,</p>
    
    <p>A new user has just registered on the platform:</p>
    
    <div style="background: #f8f9fa; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;">
        <table width="100%" cellpadding="5" style="border-collapse: collapse;">
            <tr>
                <td style="color: #666; width: 140px;"><strong>Name:</strong></td>
                <td><?= htmlspecialchars($user_name) ?></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Email:</strong></td>
                <td><a href="mailto:<?= htmlspecialchars($user_email) ?>" style="color: #2E70DA;"><?= htmlspecialchars($user_email) ?></a></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Phone:</strong></td>
                <td><?= htmlspecialchars($user_phone) ?></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Role:</strong></td>
                <td><?= htmlspecialchars($user_roles) ?></td>
            </tr>
            <tr>
                <td style="color: #666;"><strong>Registered:</strong></td>
                <td><?= htmlspecialchars($registration_date) ?></td>
            </tr>
        </table>
    </div>
    
    <p style="text-align: center; margin: 30px 0;">
        <a href="<?= htmlspecialchars($user_profile_url) ?>" 
           style="background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
            View User Profile
        </a>
    </p>
    
    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">
    <p style="color: #666; font-size: 12px;">
        This is an automated notification from EduTrack LMS.<br>
        You are receiving this because you are an administrator.
    </p>
</div>
