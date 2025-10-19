<?php
/**
 * Email Class
 * Send templated emails
 */

class Email {
    
    private static $from;
    private static $fromName;
    
    /**
     * Initialize email settings
     */
    public static function init() {
        self::$from = config('mail.from_address', SITE_EMAIL);
        self::$fromName = config('mail.from_name', APP_NAME);
    }
    
    /**
     * Send email
     * 
     * @param string $to Recipient email
     * @param string $template Template name
     * @param array $data Template data
     * @return bool
     */
    public static function send($to, $template, $data = []) {
        self::init();
        
        $subject = self::getSubject($template, $data);
        $body = self::getBody($template, $data);
        
        return self::sendMail($to, $subject, $body);
    }
    
    /**
     * Get email subject
     */
    private static function getSubject($template, $data) {
        $subjects = [
            'welcome' => 'Welcome to ' . APP_NAME,
            'enrollment-confirmation' => 'Enrollment Confirmation - ' . ($data['course_title'] ?? 'Course'),
            'payment-success' => 'Payment Successful - ' . ($data['course_title'] ?? 'Course'),
            'payment-receipt' => 'Payment Receipt - ' . ($data['reference'] ?? ''),
            'certificate-issued' => 'Your Certificate is Ready!',
            'password-reset' => 'Password Reset Request',
            'course-completed' => 'Congratulations on Completing ' . ($data['course_title'] ?? 'the Course'),
            'assignment-graded' => 'Your Assignment Has Been Graded',
            'new-announcement' => 'New Announcement: ' . ($data['title'] ?? ''),
        ];
        
        return $subjects[$template] ?? APP_NAME . ' Notification';
    }
    
    /**
     * Get email body
     */
    private static function getBody($template, $data) {
        $templatePath = SRC_PATH . '/mail/' . $template . '.php';
        
        if (file_exists($templatePath)) {
            ob_start();
            extract($data);
            include $templatePath;
            return ob_get_clean();
        }
        
        return self::getDefaultTemplate($template, $data);
    }
    
    /**
     * Get default template (fallback)
     */
    private static function getDefaultTemplate($template, $data) {
        $message = '';
        
        switch ($template) {
            case 'welcome':
                $message = self::welcomeTemplate($data);
                break;
            case 'enrollment-confirmation':
                $message = self::enrollmentTemplate($data);
                break;
            case 'payment-success':
                $message = self::paymentSuccessTemplate($data);
                break;
            case 'certificate-issued':
                $message = self::certificateTemplate($data);
                break;
            case 'password-reset':
                $message = self::passwordResetTemplate($data);
                break;
            case 'course-completed':
                $message = self::courseCompletedTemplate($data);
                break;
            default:
                $message = self::genericTemplate($data);
        }
        
        return self::wrapInLayout($message);
    }
    
    /**
     * Welcome email template
     */
    private static function welcomeTemplate($data) {
        $name = $data['name'] ?? 'Student';
        $loginUrl = url('login.php');
        $coursesUrl = url('courses.php');
        
        return <<<HTML
        <h2 style="color: #2E70DA;">Welcome to {$GLOBALS['config']['name']}!</h2>
        <p>Dear {$name},</p>
        <p>Thank you for joining {$GLOBALS['config']['name']}. We're excited to have you as part of our learning community!</p>
        <p>Your account has been successfully created. You can now:</p>
        <ul>
            <li>Browse our TEVETA-certified courses</li>
            <li>Enroll in courses and start learning</li>
            <li>Track your progress and earn certificates</li>
            <li>Connect with instructors and fellow students</li>
        </ul>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{$loginUrl}" style="background-color: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Login to Your Account</a>
        </p>
        <p style="text-align: center;">
            <a href="{$coursesUrl}" style="color: #2E70DA;">Browse Courses</a>
        </p>
        <p>If you have any questions, feel free to contact us at {$GLOBALS['config']['site']['email']}.</p>
HTML;
    }
    
    /**
     * Enrollment confirmation template
     */
    private static function enrollmentTemplate($data) {
        $name = $data['name'] ?? 'Student';
        $courseTitle = $data['course_title'] ?? 'Course';
        $courseUrl = $data['course_url'] ?? url();
        
        return <<<HTML
        <h2 style="color: #2E70DA;">Enrollment Confirmed!</h2>
        <p>Dear {$name},</p>
        <p>Congratulations! You have been successfully enrolled in:</p>
        <div style="background-color: #f5f5f5; padding: 20px; border-left: 4px solid #2E70DA; margin: 20px 0;">
            <h3 style="margin: 0; color: #333;">{$courseTitle}</h3>
        </div>
        <p>You can now access all course materials, lessons, and resources.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{$courseUrl}" style="background-color: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Start Learning Now</a>
        </p>
        <p>Happy learning!</p>
HTML;
    }
    
    /**
     * Payment success template
     */
    private static function paymentSuccessTemplate($data) {
        $name = $data['name'] ?? 'Student';
        $courseTitle = $data['course_title'] ?? 'Course';
        $amount = $data['amount'] ?? 'Amount';
        $reference = $data['reference'] ?? 'N/A';
        $courseUrl = $data['course_url'] ?? url();
        
        return <<<HTML
        <h2 style="color: #10B981;">Payment Successful!</h2>
        <p>Dear {$name},</p>
        <p>Your payment has been successfully processed. Thank you for your purchase!</p>
        <div style="background-color: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 8px;"><strong>Course:</strong></td>
                    <td style="padding: 8px;">{$courseTitle}</td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>Amount:</strong></td>
                    <td style="padding: 8px;">{$amount}</td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>Reference:</strong></td>
                    <td style="padding: 8px;">{$reference}</td>
                </tr>
            </table>
        </div>
        <p>You now have full access to the course and all its materials.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{$courseUrl}" style="background-color: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Access Course</a>
        </p>
HTML;
    }
    
    /**
     * Certificate issued template
     */
    private static function certificateTemplate($data) {
        $name = $data['name'] ?? 'Student';
        $courseTitle = $data['course_title'] ?? 'Course';
        $certificateUrl = $data['certificate_url'] ?? url();
        
        return <<<HTML
        <h2 style="color: #F6B745;">🎓 Your Certificate is Ready!</h2>
        <p>Dear {$name},</p>
        <p>Congratulations on successfully completing <strong>{$courseTitle}</strong>!</p>
        <p>Your TEVETA-certified certificate is now available for download.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{$certificateUrl}" style="background-color: #F6B745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Download Certificate</a>
        </p>
        <p>This certificate can be verified online and demonstrates your achievement in completing this course.</p>
        <p>Keep up the great work!</p>
HTML;
    }
    
    /**
     * Password reset template
     */
    private static function passwordResetTemplate($data) {
        $name = $data['name'] ?? 'User';
        $resetUrl = $data['reset_url'] ?? url();
        
        return <<<HTML
        <h2 style="color: #2E70DA;">Password Reset Request</h2>
        <p>Dear {$name},</p>
        <p>We received a request to reset your password. Click the button below to create a new password:</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{$resetUrl}" style="background-color: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>
        </p>
        <p>This link will expire in 1 hour for security reasons.</p>
        <p>If you didn't request this, please ignore this email and your password will remain unchanged.</p>
HTML;
    }
    
    /**
     * Course completed template
     */
    private static function courseCompletedTemplate($data) {
        $name = $data['name'] ?? 'Student';
        $courseTitle = $data['course_title'] ?? 'Course';
        $score = $data['score'] ?? 0;
        
        return <<<HTML
        <h2 style="color: #10B981;">🎉 Congratulations!</h2>
        <p>Dear {$name},</p>
        <p>You have successfully completed <strong>{$courseTitle}</strong>!</p>
        <p>Final Score: <strong>{$score}%</strong></p>
        <p>Your certificate is being processed and will be available shortly.</p>
        <p>Keep learning and growing with us!</p>
HTML;
    }
    
    /**
     * Generic template
     */
    private static function genericTemplate($data) {
        $message = $data['message'] ?? 'You have a new notification.';
        return "<p>{$message}</p>";
    }
    
    /**
     * Wrap content in email layout
     */
    private static function wrapInLayout($content) {
        $logoUrl = url('assets/images/logo.png');
        $year = date('Y');
        $siteUrl = url();
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$GLOBALS['config']['name']}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #2E70DA; padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: white; font-size: 24px;">{$GLOBALS['config']['name']}</h1>
                            <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">TEVETA Certified Training</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            {$content}
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #666; font-size: 14px;">
                                <strong>{$GLOBALS['config']['name']}</strong><br>
                                {$GLOBALS['config']['site']['address']}<br>
                                {$GLOBALS['config']['site']['phone']} | {$GLOBALS['config']['site']['email']}
                            </p>
                            <p style="margin: 15px 0 0 0; color: #999; font-size: 12px;">
                                © {$year} {$GLOBALS['config']['name']}. All rights reserved.<br>
                                <a href="{$siteUrl}" style="color: #2E70DA; text-decoration: none;">Visit Website</a>
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
    
    /**
     * Send mail using PHP mail() or SMTP
     */
    private static function sendMail($to, $subject, $body) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . self::$fromName . ' <' . self::$from . '>',
            'Reply-To: ' . self::$from,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Check if using SMTP
        if (config('mail.driver') == 'smtp') {
            return self::sendSMTP($to, $subject, $body);
        }
        
        // Use PHP mail()
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
    
    /**
     * Send via SMTP
     */
    private static function sendSMTP($to, $subject, $body) {
        // This would use PHPMailer or similar
        // For now, falling back to mail()
        
        try {
            // If PHPMailer is available
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                $mail->isSMTP();
                $mail->Host = config('mail.host');
                $mail->SMTPAuth = true;
                $mail->Username = config('mail.username');
                $mail->Password = config('mail.password');
                $mail->SMTPSecure = config('mail.encryption', 'tls');
                $mail->Port = config('mail.port', 587);
                
                $mail->setFrom(self::$from, self::$fromName);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body;
                
                return $mail->send();
            }
        } catch (Exception $e) {
            error_log('Email error: ' . $e->getMessage());
            return false;
        }
        
        return false;
    }
}