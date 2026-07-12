<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Update the default email templates to use a branded, responsive layout
     * and consistent {{first_name}} placeholders.
     */
    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        $templates = [
            'welcome_email' => [
                'subject' => 'Welcome to Edutrack LMS!',
                'body' => $this->layout(
                    'Welcome Aboard!',
                    '<p>Hi {{first_name}},</p>'
                    . '<p>Thank you for creating an account with <strong>Edutrack Computer Training College</strong>. We are excited to support your learning journey.</p>'
                    . '<div class="card">'
                    . '<p style="margin:0;">You can log in to your dashboard to browse courses, manage enrollments, and track your progress.</p>'
                    . '</div>'
                    . '<p class="center"><a href="{{login_url}}" class="btn">Login to Your Dashboard</a></p>'
                    . '<p class="small">If the button above does not work, copy and paste this link into your browser: {{login_url}}</p>'
                ),
            ],
            'enrollment_confirmation' => [
                'subject' => 'Course Enrollment Confirmation',
                'body' => $this->layout(
                    'Enrollment Confirmed',
                    '<p>Hi {{first_name}},</p>'
                    . '<p>Great news! You have been successfully enrolled in <strong>{{course_title}}</strong>.</p>'
                    . '<div class="card card-success">'
                    . '<table class="meta">'
                    . '<tr><td>Course</td><td>{{course_title}}</td></tr>'
                    . '<tr><td>Start Date</td><td>{{start_date}}</td></tr>'
                    . '</table>'
                    . '</div>'
                    . '<p class="center"><a href="{{course_url}}" class="btn btn-success">Access Course</a></p>'
                    . '<p class="small">If the button above does not work, copy and paste this link into your browser: {{course_url}}</p>'
                ),
            ],
            'certificate_issued' => [
                'subject' => 'Your Certificate is Ready!',
                'body' => $this->layout(
                    'Congratulations!',
                    '<p>Hi {{first_name}},</p>'
                    . '<p>You have successfully completed <strong>{{course_title}}</strong>. Your certificate is now available for download.</p>'
                    . '<div class="card card-success">'
                    . '<table class="meta">'
                    . '<tr><td>Certificate Number</td><td>{{certificate_number}}</td></tr>'
                    . '<tr><td>Course</td><td>{{course_title}}</td></tr>'
                    . '</table>'
                    . '</div>'
                    . '<p class="center"><a href="{{download_url}}" class="btn btn-success">Download Certificate</a></p>'
                    . '<p class="small">If the button above does not work, copy and paste this link into your browser: {{download_url}}</p>'
                ),
            ],
            'payment_confirmation' => [
                'subject' => 'Payment Received',
                'body' => $this->layout(
                    'Payment Confirmation',
                    '<p>Hi {{first_name}},</p>'
                    . '<p>Thank you! We have received your payment for <strong>{{course_title}}</strong>.</p>'
                    . '<div class="card card-success">'
                    . '<table class="meta">'
                    . '<tr><td>Amount Paid</td><td>{{currency}} {{amount}}</td></tr>'
                    . '<tr><td>Transaction ID</td><td>{{transaction_id}}</td></tr>'
                    . '<tr><td>Date</td><td>{{date}}</td></tr>'
                    . '</table>'
                    . '</div>'
                    . '<p class="small">If you have any questions, please contact our finance office at edutrackzambia@gmail.com or +260 770 666 937.</p>'
                ),
            ],
            'assignment_deadline_reminder' => [
                'subject' => 'Assignment Due Soon',
                'body' => $this->layout(
                    'Reminder: Assignment Due',
                    '<p>Hi {{first_name}},</p>'
                    . '<p>This is a friendly reminder that your assignment <strong>{{assignment_title}}</strong> is due soon.</p>'
                    . '<div class="card card-warning">'
                    . '<table class="meta">'
                    . '<tr><td>Assignment</td><td>{{assignment_title}}</td></tr>'
                    . '<tr><td>Due Date</td><td>{{due_date}}</td></tr>'
                    . '</table>'
                    . '</div>'
                    . '<p>Please log in to your dashboard to submit it on time.</p>'
                ),
            ],
        ];

        foreach ($templates as $name => $template) {
            DB::table('email_templates')
                ->where('template_name', $name)
                ->update([
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Revert to the original plain HTML templates.
     */
    public function down(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        $revert = [
            'welcome_email' => '<h1>Welcome {{first_name}}!</h1><p>Thank you for joining EduTrack LMS. We are excited to have you on board.</p><p>You can now access all your courses from your dashboard.</p>',
            'enrollment_confirmation' => '<h1>Enrollment Confirmed</h1><p>Dear {{first_name}},</p><p>You have successfully enrolled in <strong>{{course_title}}</strong>.</p><p>Course starts on: {{start_date}}</p>',
            'certificate_issued' => '<h1>Congratulations {{first_name}}!</h1><p>Your certificate for <strong>{{course_title}}</strong> is now available.</p><p>Certificate Number: {{certificate_number}}</p><p>Download your certificate from your dashboard.</p>',
            'payment_confirmation' => '<h1>Payment Confirmation</h1><p>Dear {{first_name}},</p><p>We have received your payment of {{amount}} {{currency}} for <strong>{{course_title}}</strong>.</p><p>Transaction ID: {{transaction_id}}</p>',
            'assignment_deadline_reminder' => '<h1>Reminder: Assignment Due</h1><p>Hi {{first_name}},</p><p>This is a reminder that your assignment <strong>{{assignment_title}}</strong> is due on {{due_date}}.</p>',
        ];

        foreach ($revert as $name => $body) {
            DB::table('email_templates')
                ->where('template_name', $name)
                ->update(['body' => $body, 'updated_at' => now()]);
        }
    }

    /**
     * Wrap email content in the branded Edutrack layout.
     */
    protected function layout(string $title, string $content): string
    {
        return '<!DOCTYPE html>'
            . '<html>'
            . '<head>'
            . '<meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            . '<title>' . e($title) . ' - Edutrack LMS</title>'
            . '<style>'
            . 'body{font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f3f4f6;margin:0;padding:20px;color:#374151;line-height:1.6;}'
            . '.container{max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.05);}'
            . '.header{background:#1e3a5f;padding:32px 30px;text-align:center;}'
            . '.header h1{color:#ffffff;margin:0;font-size:22px;font-weight:600;letter-spacing:-0.01em;}'
            . '.header p{color:rgba(255,255,255,0.8);margin:6px 0 0;font-size:14px;}'
            . '.body{padding:32px 30px;}'
            . '.body p{margin:0 0 14px;}'
            . '.card{background:#f8fafc;border-left:4px solid #1e3a5f;padding:20px;margin:20px 0;border-radius:0 8px 8px 0;}'
            . '.card-success{border-left-color:#059669;background:#ecfdf5;}'
            . '.card-warning{border-left-color:#d97706;background:#fffbeb;}'
            . '.btn{display:inline-block;padding:12px 28px;background:#1e3a5f;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:500;font-size:15px;margin-top:10px;}'
            . '.btn-success{background:#059669;}'
            . '.btn-warning{background:#d97706;}'
            . '.footer{padding:24px 30px;text-align:center;font-size:12px;color:#9ca3af;background:#f9fafb;border-top:1px solid #e5e7eb;}'
            . '.footer a{color:#6b7280;text-decoration:none;}'
            . '.divider{border:none;border-top:1px solid #e5e7eb;margin:24px 0;}'
            . '.small{font-size:13px;color:#6b7280;}'
            . '.center{text-align:center;}'
            . 'table.meta{width:100%;border-collapse:collapse;margin:16px 0;}'
            . 'table.meta td{padding:10px 0;border-bottom:1px solid #e5e7eb;}'
            . 'table.meta td:last-child{text-align:right;font-weight:500;}'
            . '@media only screen and (max-width:480px){body{padding:10px;}.header,.body,.footer{padding-left:20px;padding-right:20px;}}'
            . '</style>'
            . '</head>'
            . '<body>'
            . '<div class="container">'
            . '<div class="header">'
            . '<h1>Edutrack LMS</h1>'
            . '<p>Computer Training College</p>'
            . '</div>'
            . '<div class="body">'
            . $content
            . '</div>'
            . '<div class="footer">'
            . '<p><strong>Edutrack Computer Training College</strong><br>Kalomo, Zambia</p>'
            . '<p>edutrackzambia@gmail.com &bull; +260 770 666 937</p>'
            . '<p style="margin-top:10px;">&copy; ' . date('Y') . ' Edutrack LMS. All rights reserved.</p>'
            . '</div>'
            . '</div>'
            . '</body>'
            . '</html>';
    }
};
