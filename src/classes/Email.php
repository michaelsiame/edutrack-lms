<?php
/**
 * Email Class
 * Handles email sending with templates
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Email {
    private $mailer;
    private $from;
    private $fromName;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = getenv('MAIL_USERNAME');
        $this->mailer->Password = getenv('MAIL_PASSWORD');
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = getenv('MAIL_PORT') ?: 587;
        
        // From
        $this->from = getenv('MAIL_FROM_ADDRESS') ?: getenv('MAIL_USERNAME');
        $this->fromName = getenv('MAIL_FROM_NAME') ?: APP_NAME;
        
        $this->mailer->setFrom($this->from, $this->fromName);
        $this->mailer->isHTML(true);
    }
    
    /**
     * Send email
     */
    public function send($to, $subject, $body, $attachments = []) {
        try {
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
            
            // Clear recipients for next email
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            return $result;
        } catch (Exception $e) {
            error_log("Email Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Send welcome email
     */
    public function sendWelcome($user) {
        $subject = "Welcome to " . APP_NAME;
        
        $body = "
        <h2>Welcome to " . APP_NAME . ", " . htmlspecialchars($user['first_name']) . "!</h2>
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
            <a href='" . url('courses.php') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Browse Courses
            </a>
        </p>
        
        <p>If you have any questions, feel free to contact us at " . SITE_EMAIL . "</p>
        
        <p>Happy learning!<br>The " . APP_NAME . " Team</p>
        ";
        
        return $this->send($user['email'], $subject, $body);
    }
    
    /**
     * Send enrollment confirmation
     */
    public function sendEnrollmentConfirmation($user, $course) {
        $subject = "Enrolled in " . $course->getTitle();
        
        $body = "
        <h2>Congratulations, " . htmlspecialchars($user['first_name']) . "!</h2>
        <p>You have successfully enrolled in <strong>" . htmlspecialchars($course->getTitle()) . "</strong>.</p>
        
        <div style='background: #f5f5f5; padding: 20px; margin: 20px 0;'>
            <h3>Course Details:</h3>
            <p><strong>Title:</strong> " . htmlspecialchars($course->getTitle()) . "</p>
            <p><strong>Level:</strong> " . ucfirst($course->getLevel()) . "</p>
            <p><strong>Duration:</strong> " . ($course->getDurationHours() ?? 'Self-paced') . " hours</p>
            " . ($course->isTeveta() ? "<p><strong>TEVETA Certified:</strong> Yes ✓</p>" : "") . "
        </div>
        
        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . url('learn.php?course=' . $course->getSlug()) . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Start Learning Now
            </a>
        </p>
        
        <p>Happy learning!</p>
        ";
        
        return $this->send($user['email'], $subject, $body);
    }
    
    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation($user, $payment) {
        $subject = "Payment Confirmation - " . $payment->getCourseTitle();
        
        $body = "
        <h2>Payment Confirmed!</h2>
        <p>Dear " . htmlspecialchars($user['first_name']) . ",</p>
        <p>Your payment has been successfully processed and confirmed.</p>
        
        <div style='background: #f5f5f5; padding: 20px; margin: 20px 0; border-left: 4px solid #10B981;'>
            <h3>Payment Details:</h3>
            <p><strong>Transaction ID:</strong> " . htmlspecialchars($payment->getTransactionId()) . "</p>
            <p><strong>Course:</strong> " . htmlspecialchars($payment->getCourseTitle()) . "</p>
            <p><strong>Amount:</strong> " . CURRENCY_SYMBOL . number_format($payment->getAmount(), 2) . "</p>
            <p><strong>Payment Method:</strong> " . ucfirst($payment->getPaymentMethod()) . "</p>
            <p><strong>Date:</strong> " . date('F j, Y g:i A', strtotime($payment->getPaidAt())) . "</p>
        </div>
        
        <p>You can now access your course and start learning.</p>
        
        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . url('my-courses.php') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Go to My Courses
            </a>
        </p>
        
        <p>Thank you for your purchase!</p>
        ";
        
        return $this->send($user['email'], $subject, $body);
    }
    
    /**
     * Send certificate issued notification
     */
    public function sendCertificateIssued($user, $certificate) {
        $subject = "Your TEVETA Certificate is Ready!";
        
        $body = "
        <h2>Congratulations, " . htmlspecialchars($user['first_name']) . "!</h2>
        <p>You have successfully completed <strong>" . htmlspecialchars($certificate->getCourseTitle()) . "</strong> and earned your TEVETA-certified certificate!</p>
        
        <div style='background: #f5f5f5; padding: 20px; margin: 20px 0; border-left: 4px solid #F6B745;'>
            <h3>Certificate Details:</h3>
            <p><strong>Certificate Number:</strong> " . htmlspecialchars($certificate->getCertificateNumber()) . "</p>
            <p><strong>Verification Code:</strong> " . htmlspecialchars($certificate->getVerificationCode()) . "</p>
            <p><strong>Final Grade:</strong> " . round($certificate->getFinalGrade(), 1) . "%</p>
            <p><strong>Issued:</strong> " . date('F j, Y', strtotime($certificate->getIssuedAt())) . "</p>
        </div>
        
        <p>Your certificate is attached to this email. You can also download it anytime from your dashboard.</p>
        
        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . url('my-certificates.php') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                View My Certificates
            </a>
        </p>
        
        <p>To verify your certificate online, visit:<br>
        <a href='" . url('verify-certificate.php?code=' . $certificate->getVerificationCode()) . "'>" . url('verify-certificate.php?code=' . $certificate->getVerificationCode()) . "</a></p>
        
        <p>Congratulations on your achievement!</p>
        ";
        
        return $this->send($user['email'], $subject, $body);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordReset($user, $resetToken) {
        $subject = "Reset Your Password";
        
        $resetUrl = url('reset-password.php?token=' . $resetToken);
        
        $body = "
        <h2>Password Reset Request</h2>
        <p>Hi " . htmlspecialchars($user['first_name']) . ",</p>
        <p>We received a request to reset your password. Click the button below to create a new password:</p>
        
        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . $resetUrl . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Reset Password
            </a>
        </p>
        
        <p>Or copy and paste this link into your browser:<br>
        <a href='" . $resetUrl . "'>" . $resetUrl . "</a></p>
        
        <p><strong>This link will expire in 1 hour.</strong></p>
        
        <p>If you didn't request this password reset, please ignore this email or contact us if you have concerns.</p>
        ";
        
        return $this->send($user['email'], $subject, $body);
    }
    
    /**
     * Send assignment graded notification
     */
    public function sendAssignmentGraded($user, $submission) {
        $assignment = $submission->getAssignment();
        $subject = "Assignment Graded: " . $assignment->getTitle();
        
        $body = "
        <h2>Your Assignment Has Been Graded</h2>
        <p>Hi " . htmlspecialchars($user['first_name']) . ",</p>
        <p>Your instructor has graded your assignment submission.</p>
        
        <div style='background: #f5f5f5; padding: 20px; margin: 20px 0;'>
            <h3>Grading Details:</h3>
            <p><strong>Assignment:</strong> " . htmlspecialchars($assignment->getTitle()) . "</p>
            <p><strong>Course:</strong> " . htmlspecialchars($assignment->getCourseTitle()) . "</p>
            <p><strong>Score:</strong> " . $submission->getPoints() . " / " . $assignment->getMaxPoints() . "</p>
            " . ($submission->getFeedback() ? "<p><strong>Feedback:</strong><br>" . nl2br(htmlspecialchars($submission->getFeedback())) . "</p>" : "") . "
        </div>
        
        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . url('assignment-result.php?id=' . $submission->getId()) . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                View Detailed Results
            </a>
        </p>
        ";
        
        return $this->send($user['email'], $subject, $body);
    }
    
    /**
     * Send course completion notification
     */
    public function sendCourseCompleted($user, $course) {
        $subject = "Congratulations! Course Completed: " . $course->getTitle();
        
        $body = "
        <h2>🎉 Congratulations, " . htmlspecialchars($user['first_name']) . "!</h2>
        <p>You have successfully completed <strong>" . htmlspecialchars($course->getTitle()) . "</strong>!</p>
        
        <div style='background: linear-gradient(135deg, #2E70DA 0%, #10B981 100%); padding: 30px; margin: 20px 0; border-radius: 10px; color: white; text-align: center;'>
            <h3 style='color: white; margin: 0;'>Course Completed!</h3>
            <p style='font-size: 18px; margin: 10px 0;'>" . htmlspecialchars($course->getTitle()) . "</p>
        </div>
        
        " . ($course->hasCertificate() ? "
        <p>Your TEVETA certificate is being generated and will be sent to you shortly.</p>
        " : "") . "
        
        <p style='text-align: center; margin: 30px 0;'>
            <a href='" . url('my-courses.php') . "' style='background: #2E70DA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Explore More Courses
            </a>
        </p>
        
        <p>Keep up the great work!</p>
        ";
        
        return $this->send($user['email'], $subject, $body);
    }
    
    /**
     * Wrap content in email template
     */
    private function wrapTemplate($content, $subject) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>" . htmlspecialchars($subject) . "</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f4f4; padding: 20px 0;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                            <!-- Header -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #2E70DA 0%, #1E5FBD 100%); padding: 30px; text-align: center;'>
                                    <h1 style='color: white; margin: 0; font-size: 28px;'>" . APP_NAME . "</h1>
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
                                        " . APP_NAME . "<br>
                                        " . SITE_ADDRESS . "<br>
                                        " . SITE_PHONE . " | " . SITE_EMAIL . "
                                    </p>
                                    <p style='margin: 10px 0 0 0; font-size: 12px; color: #adb5bd;'>
                                        © " . date('Y') . " " . APP_NAME . ". All rights reserved.
                                    </p>
                                    <p style='margin: 10px 0 0 0; font-size: 12px;'>
                                        <a href='" . url() . "' style='color: #2E70DA; text-decoration: none;'>Visit Website</a> |
                                        <a href='" . url('contact.php') . "' style='color: #2E70DA; text-decoration: none;'>Contact Us</a>
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