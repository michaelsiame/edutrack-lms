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
        $vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

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
     *
     * @param string $to Recipient email
     * @param string $subject Email subject or template name
     * @param string|array $body Email body HTML or template data
     * @param array $attachments File paths for attachments
     * @return bool Success status
     */
    public static function sendMail($to, $subject, $body = '', $attachments = []) {
        $emailInstance = new self();

        // If body is an array, it might be template data - for now just ignore it
        // Future enhancement: implement template system
        if (is_array($body)) {
            // Build a simple email body from the data
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
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param array $attachments File paths for attachments
     * @return bool Success status
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

    /**
     * Send email using PHPMailer
     */
    private function sendWithPHPMailer($to, $subject, $body, $attachments = []) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->wrapTemplate($body, $subject);
            $this->mailer->AltBody = strip_tags($body);

            // Add attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $this->mailer->addAttachment($attachment);
                }
            }

            $result = $this->mailer->send();

            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Email sent successfully to: " . $to);
            }

            return $result;
        } catch (\Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());

            // Try fallback
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Attempting fallback to native mail()");
            }
            return $this->sendWithNativeMail($to, $subject, $body);
        }
    }

    /**
     * Send email using native PHP mail()
     */
    private function sendWithNativeMail($to, $subject, $body) {
        $wrappedBody = $this->wrapTemplate($body, $subject);

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->from . '>',
            'Reply-To: ' . $this->from,
            'X-Mailer: PHP/' . phpversion()
        ];

        $result = mail($to, $subject, $wrappedBody, implode("\r\n", $headers));

        if ($result) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Email sent successfully (native mail) to: " . $to);
            }
        } else {
            error_log("Email Error: Failed to send email to " . $to);
        }

        return $result;
    }

    /**
     * Validate email address
     */
    private function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Send welcome email
     */
    public function sendWelcome($user) {
        $subject = "Welcome to " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS');

        $firstName = isset($user['first_name']) ? $user['first_name'] : 'Student';
        $email = isset($user['email']) ? $user['email'] : null;

        if (!$email) {
            error_log("Email Error: No email provided for sendWelcome");
            return false;
        }

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
            <a href='" . $this->url('courses.php') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Browse Courses
            </a>
        </p>

        <p>If you have any questions, feel free to contact us at " . (defined('SITE_EMAIL') ? SITE_EMAIL : $this->from) . "</p>

        <p>Happy learning!<br>The " . (defined('APP_NAME') ? APP_NAME : 'Edutrack LMS') . " Team</p>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset($user, $resetToken) {
        $subject = "Reset Your Password";

        $firstName = isset($user['first_name']) ? $user['first_name'] : 'User';
        $email = isset($user['email']) ? $user['email'] : null;

        if (!$email) {
            error_log("Email Error: No email provided for sendPasswordReset");
            return false;
        }

        $resetUrl = $this->url('reset-password.php?token=' . urlencode($resetToken));

        $body = "
        <h2>Password Reset Request</h2>
        <p>Hi " . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ",</p>
        <p>We received a request to reset your password. Click the button below to create a new password:</p>

        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Reset Password
            </a>
        </p>

        <p>Or copy and paste this link into your browser:<br>
        <a href='" . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . "</a></p>

        <p><strong>This link will expire in 1 hour.</strong></p>

        <p>If you didn't request this password reset, please ignore this email or contact us if you have concerns.</p>
        ";

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
        $siteAddress = defined('SITE_ADDRESS') ? SITE_ADDRESS : '';
        $sitePhone = defined('SITE_PHONE') ? SITE_PHONE : '';
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
                            <!-- Header -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #2E70DA 0%, #1E5FBD 100%); padding: 30px; text-align: center;'>
                                    <h1 style='color: white; margin: 0; font-size: 28px;'>" . htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') . "</h1>
                                    <p style='color: #F6B745; margin: 5px 0 0 0; font-size: 14px;'>TEVETA Certified Training</p>
                                </td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style='padding: 40px 30px;'>
                                    $content
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e9ecef;'>
                                    <p style='margin: 0 0 10px 0; font-size: 14px; color: #6c757d;'>
                                        " . htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') . "<br>
                                        " . ($siteAddress ? htmlspecialchars($siteAddress, ENT_QUOTES, 'UTF-8') . "<br>" : "") . "
                                        " . ($sitePhone ? htmlspecialchars($sitePhone, ENT_QUOTES, 'UTF-8') . " | " : "") . htmlspecialchars($siteEmail, ENT_QUOTES, 'UTF-8') . "
                                    </p>
                                    <p style='margin: 10px 0 0 0; font-size: 12px; color: #adb5bd;'>
                                        © " . date('Y') . " " . htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') . ". All rights reserved.
                                    </p>
                                    <p style='margin: 10px 0 0 0; font-size: 12px;'>
                                        <a href='" . $this->url() . "' style='color: #2E70DA; text-decoration: none;'>Visit Website</a> |
                                        <a href='" . $this->url('contact.php') . "' style='color: #2E70DA; text-decoration: none;'>Contact Us</a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }
}
