<?php
/**
 * Email Class
 * Handles email sending with templates
 *
 * Automatically falls back to PHP mail() if PHPMailer is not installed
 */

class Email {
    private $mailer = null;
    private $from;
    private $fromName;
    private $usePHPMailer = false;

    public function __construct() {
        // Try to load PHPMailer if available
        // Check root vendor directory first, then src/vendor as fallback
        $rootVendor = dirname(__DIR__, 2) . '/vendor/autoload.php';
        $srcVendor = dirname(__DIR__) . '/vendor/autoload.php';

        $vendorAutoload = file_exists($rootVendor) ? $rootVendor : $srcVendor;

        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;

            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $this->initializePHPMailer();
            } else {
                $this->initializeFallback();
            }
        } else {
            $this->initializeFallback();
        }
    }

    /**
     * Initialize PHPMailer
     */
    private function initializePHPMailer() {
        try {
            $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $this->usePHPMailer = true;

            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = getenv('MAIL_USERNAME');
            $this->mailer->Password = getenv('MAIL_PASSWORD');
            $this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = getenv('MAIL_PORT') ?: 587;

            // From
            $this->from = getenv('MAIL_FROM_ADDRESS') ?: getenv('MAIL_USERNAME');
            $this->fromName = getenv('MAIL_FROM_NAME') ?: (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS');

            $this->mailer->setFrom($this->from, $this->fromName);
            $this->mailer->isHTML(true);

            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Email: Using PHPMailer SMTP");
            }
        } catch (\Exception $e) {
            // PHPMailer initialization failed, fall back to native mail
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Email: PHPMailer initialization failed, using fallback: " . $e->getMessage());
            }
            $this->initializeFallback();
        }
    }

    /**
     * Initialize fallback (native PHP mail)
     */
    private function initializeFallback() {
        $this->usePHPMailer = false;
        $this->from = getenv('MAIL_FROM_ADDRESS') ?: 'noreply@edutrack.com';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS');

        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log("Email: Using native PHP mail() function");
        }
    }

    /**
     * Static helper to send email (for backward compatibility)
     */
    public static function sendMail($to, $subject, $body = '', $attachments = []) {
        $emailInstance = new self();
        if (is_array($body)) {
            $htmlBody = "<h2>Notification</h2>";
            foreach ($body as $key => $value) {
                $htmlBody .= "<p><strong>" . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ":</strong> " . htmlspecialchars($value) . "</p>";
            }
            $body = $htmlBody;
        }
        return $emailInstance->send($to, $subject, $body, $attachments);
    }

    /**
     * Send email
     */
    public function send($to, $subject, $body, $attachments = []) {
        if (!$this->isValidEmail($to)) {
            error_log("Email Error: Invalid recipient email: " . $to);
            return false;
        }

        if ($this->usePHPMailer) {
            return $this->sendWithPHPMailer($to, $subject, $body, $attachments);
        } else {
            return $this->sendWithNativeMail($to, $subject, $body);
        }
    }

    private function sendWithPHPMailer($to, $subject, $body, $attachments = []) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->wrapTemplate($body, $subject);
            $this->mailer->AltBody = strip_tags($body);

            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $this->mailer->addAttachment($attachment);
                }
            }

            $result = $this->mailer->send();
            return $result;
        } catch (\Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            return $this->sendWithNativeMail($to, $subject, $body);
        }
    }

    private function sendWithNativeMail($to, $subject, $body) {
        if ((defined('APP_ENV') && APP_ENV === 'development') || (defined('APP_DEBUG') && APP_DEBUG)) {
            error_log("=== EMAIL SENT (DEV MODE) === To: $to | Subject: $subject");
            return true;
        }

        $wrappedBody = $this->wrapTemplate($body, $subject);
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->from . '>',
            'Reply-To: ' . $this->from,
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($to, $subject, $wrappedBody, implode("\r\n", $headers));
    }

    private function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // =========================================================================
    //                            TEMPLATE METHODS
    // =========================================================================

    /**
     * Send welcome email
     */
    public function sendWelcome($user) {
        $subject = "Welcome to " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS');
        $firstName = is_object($user) ? ($user->first_name ?? 'Student') : ($user['first_name'] ?? 'Student');
        $email = is_object($user) ? ($user->email ?? null) : ($user['email'] ?? null);

        if (!$email) return false;

        $body = "
        <h2>Welcome to " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS') . ", " . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . "!</h2>
        <p>Thank you for joining our learning community. We're excited to have you here!</p>
        <div style='background: #f5f5f5; padding: 20px; margin: 20px 0; border-left: 4px solid #2E70DA;'>
            <h3>Get Started:</h3>
            <ul style='margin: 10px 0; padding-left: 20px;'>
                <li>Browse our TEVETA-certified courses</li>
                <li>Complete your profile</li>
                <li>Start learning and earn certificates</li>
            </ul>
        </div>
        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . $this->url('courses.php') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Browse Courses</a>
        </p>
        <p>Happy learning!<br>The " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS') . " Team</p>";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send Enrollment Confirmation Email
     */
    public function sendEnrollmentConfirmation($user, $course) {
        $subject = "Enrollment Confirmed - " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS');
        
        $firstName = is_object($user) ? ($user->first_name ?? 'Student') : ($user['first_name'] ?? 'Student');
        $email = is_object($user) ? ($user->email ?? null) : ($user['email'] ?? null);
        
        $courseTitle = is_object($course) ? ($course->title ?? 'Course') : ($course['title'] ?? 'Course');
        $startDate = is_object($course) ? ($course->start_date ?? null) : ($course['start_date'] ?? null);

        if (!$email) return false;

        $body = "
        <h2>Enrollment Confirmed!</h2>
        <p>Hi " . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ",</p>
        <p>You have successfully enrolled in the following course:</p>

        <div style='background: #f5f5f5; padding: 20px; margin: 20px 0; border-left: 4px solid #28a745;'>
            <h3 style='margin-top:0; color:#28a745;'>" . htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') . "</h3>
            " . ($startDate ? "<p><strong>Start Date:</strong> " . date('F j, Y', strtotime($startDate)) . "</p>" : "") . "
            <p>Get ready to start your learning journey!</p>
        </div>

        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . $this->url('dashboard.php') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Go to Dashboard
            </a>
        </p>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send Payment Confirmation Email
     */
    public function sendPaymentConfirmation($user, $payment) {
        $subject = "Payment Receipt - " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS');
        
        $firstName = is_object($user) ? ($user->first_name ?? 'Student') : ($user['first_name'] ?? 'Student');
        $email = is_object($user) ? ($user->email ?? null) : ($user['email'] ?? null);
        
        // Handle payment object or array
        $amount = is_object($payment) ? ($payment->amount ?? 0) : ($payment['amount'] ?? 0);
        $currency = is_object($payment) ? ($payment->currency ?? 'ZMW') : ($payment['currency'] ?? 'ZMW');
        $txnId = is_object($payment) ? ($payment->transaction_id ?? 'N/A') : ($payment['transaction_id'] ?? 'N/A');
        $date = is_object($payment) ? ($payment->created_at ?? date('Y-m-d')) : ($payment['created_at'] ?? date('Y-m-d'));

        if (!$email) return false;

        $formattedAmount = number_format($amount, 2);

        $body = "
        <h2>Payment Received</h2>
        <p>Hi " . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ",</p>
        <p>This email confirms that we have received your payment.</p>

        <div style='background: #f8f9fa; padding: 20px; border: 1px solid #e9ecef; border-radius: 5px; margin: 20px 0;'>
            <table width='100%' cellpadding='5'>
                <tr>
                    <td style='color: #666;'>Transaction ID:</td>
                    <td><strong>" . htmlspecialchars($txnId, ENT_QUOTES, 'UTF-8') . "</strong></td>
                </tr>
                <tr>
                    <td style='color: #666;'>Date:</td>
                    <td>" . date('F j, Y', strtotime($date)) . "</td>
                </tr>
                <tr>
                    <td style='color: #666;'>Amount:</td>
                    <td style='font-size: 18px; color: #2E70DA;'><strong>{$currency} {$formattedAmount}</strong></td>
                </tr>
            </table>
        </div>

        <p>Thank you for choosing " . (defined('APP_NAME') ? APP_NAME : 'Edutrack') . ".</p>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send Certificate Issued Email
     */
    public function sendCertificateIssued($user, $certificate) {
        $subject = "Congratulations! Your Certificate is Ready";
        
        $firstName = is_object($user) ? ($user->first_name ?? 'Student') : ($user['first_name'] ?? 'Student');
        $email = is_object($user) ? ($user->email ?? null) : ($user['email'] ?? null);
        
        $certCode = is_object($certificate) ? ($certificate->certificate_code ?? '') : ($certificate['certificate_code'] ?? '');
        $courseName = is_object($certificate) ? ($certificate->course_name ?? 'the course') : ($certificate['course_name'] ?? 'the course');
        
        if (!$email) return false;

        $body = "
        <h2>Congratulations, " . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . "!</h2>
        <p>You have successfully completed <strong>" . htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8') . "</strong>.</p>
        <p>Your official certificate has been generated and is ready for download.</p>

        <div style='text-align: center; margin: 30px 0;'>
            <img src='" . $this->url('images/certificate-icon.png') . "' alt='Certificate' style='width: 64px; margin-bottom: 15px;'><br>
            <a href='" . $this->url('student/certificates.php') . "' style='background: #F6B745; color: #212529; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>
                Download Certificate
            </a>
        </div>

        <p style='font-size: 12px; color: #666; text-align: center;'>Certificate ID: " . htmlspecialchars($certCode, ENT_QUOTES, 'UTF-8') . "</p>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send Course Completed Email
     */
    public function sendCourseCompleted($user, $course) {
        $subject = "Course Completed - " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS');
        
        $firstName = is_object($user) ? ($user->first_name ?? 'Student') : ($user['first_name'] ?? 'Student');
        $email = is_object($user) ? ($user->email ?? null) : ($user['email'] ?? null);
        
        $courseTitle = is_object($course) ? ($course->title ?? 'Course') : ($course['title'] ?? 'Course');

        if (!$email) return false;

        $body = "
        <h2>Well Done!</h2>
        <p>Hi " . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ",</p>
        <p>Congratulations on completing the course <strong>" . htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') . "</strong>!</p>

        <p>We hope you enjoyed the learning experience. You can now:</p>
        <ul>
            <li>View your final grades</li>
            <li>Download your certificate (if applicable)</li>
            <li>Enroll in your next course</li>
        </ul>

        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . $this->url('dashboard.php') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                View Results
            </a>
        </p>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send assignment graded notification email
     */
    public function sendAssignmentGraded($user, $submission) {
        $subject = "Assignment Graded - " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS');
        $firstName = is_object($user) ? ($user->first_name ?? 'Student') : ($user['first_name'] ?? 'Student');
        $email = is_object($user) ? ($user->email ?? null) : ($user['email'] ?? null);

        if (!$email) return false;

        // Handle submission object or array
        $assignmentTitle = is_object($submission) 
            ? ($submission->assignment_title ?? 'Assignment') 
            : ($submission['assignment_title'] ?? 'Assignment');

        $pointsEarned = is_object($submission) 
            ? ($submission->points_earned ?? 0) 
            : ($submission['points_earned'] ?? 0);

        $maxPoints = is_object($submission) 
            ? ($submission->max_points ?? 100) 
            : ($submission['max_points'] ?? 100);

        $feedback = is_object($submission) 
            ? ($submission->feedback ?? '') 
            : ($submission['feedback'] ?? '');

        $percentage = $maxPoints > 0 ? round(($pointsEarned / $maxPoints) * 100, 1) : 0;

        $body = "
        <h2>Your Assignment Has Been Graded</h2>
        <p>Hi " . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ",</p>
        <p>Your instructor has graded your assignment submission.</p>

        <div style='background: #f5f5f5; padding: 20px; margin: 20px 0; border-left: 4px solid #2E70DA;'>
            <h3>" . htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8') . "</h3>
            <p style='font-size: 24px; font-weight: bold; color: #2E70DA; margin: 10px 0;'>
                Score: {$pointsEarned} / {$maxPoints} ({$percentage}%)
            </p>
        </div>
        ";

        if ($feedback) {
            $body .= "
            <div style='background: #fff3cd; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                <h4 style='margin: 0 0 10px 0;'>Instructor Feedback:</h4>
                <p style='margin: 0; white-space: pre-wrap;'>" . htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8') . "</p>
            </div>";
        }

        $body .= "
        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . $this->url('dashboard.php') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>View Your Dashboard</a>
        </p>
        <p>Keep up the great work!<br>The " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS') . " Team</p>";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset($user, $resetToken) {
        $subject = "Reset Your Password";
        $firstName = is_object($user) ? ($user->first_name ?? 'User') : ($user['first_name'] ?? 'User');
        $email = is_object($user) ? ($user->email ?? null) : ($user['email'] ?? null);

        if (!$email) return false;

        $resetUrl = $this->url('reset-password.php?token=' . urlencode($resetToken));

        $body = "
        <h2>Password Reset Request</h2>
        <p>Hi " . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ",</p>
        <p>We received a request to reset your password. Click the button below to create a new password:</p>
        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
        </p>
        <p>Or copy and paste this link into your browser:<br>
        <a href='" . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . "</a></p>
        <p><strong>This link will expire in 1 hour.</strong></p>";

        return $this->send($email, $subject, $body);
    }

    /**
     * Helper function for URL generation
     */
    private function url($path = '') {
        if (function_exists('url')) {
            return url($path);
        }
        $baseUrl = defined('APP_URL') ? APP_URL : 'http://localhost';
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Wrap content in email template
     */
    private function wrapTemplate($content, $subject) {
        $appName = defined('APP_NAME') ? APP_NAME : 'Edutrack LMS';
        $siteEmail = defined('SITE_EMAIL') ? SITE_EMAIL : $this->from;

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>" . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . "</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f4f4; padding: 20px 0;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                            <tr>
                                <td style='background: linear-gradient(135deg, #2E70DA 0%, #1E5FBD 100%); padding: 30px; text-align: center;'>
                                    <h1 style='color: white; margin: 0; font-size: 28px;'>" . htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') . "</h1>
                                    <p style='color: #F6B745; margin: 5px 0 0 0; font-size: 14px;'>TEVETA REGISTERED Training</p>
                                </td>
                            </tr>
                            <tr><td style='padding: 40px 30px;'>$content</td></tr>
                            <tr>
                                <td style='background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e9ecef;'>
                                    <p style='margin: 0 0 10px 0; font-size: 14px; color: #6c757d;'>" . htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') . "<br>" . htmlspecialchars($siteEmail, ENT_QUOTES, 'UTF-8') . "</p>
                                    <p style='margin: 10px 0 0 0; font-size: 12px; color: #adb5bd;'>© " . date('Y') . " " . htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') . ". All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }
}