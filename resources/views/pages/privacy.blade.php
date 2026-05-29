@extends('layouts.app')

@section('title', 'Privacy Policy - Edutrack LMS')

@push('styles')
<style>
.od-public-header { background: var(--od-navy); color: var(--od-surface); }
</style>
@endpush

@section('content')
<div class="min-h-screen" style="background: var(--od-bg);">
 <div class="od-public-header py-16">
 <div class="max-w-4xl mx-auto px-4 text-center">
 <p class="od-eyebrow" style="color: var(--od-accent);">LEGAL</p>
 <h1 class="od-h1 mt-2">Privacy Policy</h1>
 <p class="od-meta mt-2" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">Last updated: {{ date('F Y') }}</p>
 </div>
 </div>

 <div class="max-w-4xl mx-auto px-4 py-12">
 <div class="od-card p-8 md:p-12">
 <div class="prose max-w-none">
 <p class="lead od-meta text-base">Edutrack Computer Training College ("Edutrack," "we," "us," or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, store, and protect your personal information when you use our Learning Management System.</p>

 <h2 class="od-h3 mt-8 mb-3">1. Information We Collect</h2>
 <p class="od-meta">We collect the following types of information:</p>

 <h3 class="text-lg font-medium mt-4 mb-2" style="color: var(--od-fg);">1.1 Personal Information</h3>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Full name, email address, and phone number</li>
 <li>National Registration Card (NRC) number or passport details</li>
 <li>Physical address and emergency contact information</li>
 <li>Profile photo and bio (optional)</li>
 </ul>

 <h3 class="text-lg font-medium mt-4 mb-2" style="color: var(--od-fg);">1.2 Academic Information</h3>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Course enrollments and completion status</li>
 <li>Assignment submissions and quiz scores</li>
 <li>Lesson progress and time spent learning</li>
 <li>Certificates and transcripts issued</li>
 </ul>

 <h3 class="text-lg font-medium mt-4 mb-2" style="color: var(--od-fg);">1.3 Payment Information</h3>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Payment method preferences</li>
 <li>Transaction history and receipt numbers</li>
 <li>Payment status (completed, pending, failed)</li>
 <li><strong>Note:</strong> We do not store full credit card or mobile money PIN details. Payments are processed securely through Lenco and mobile money providers.</li>
 </ul>

 <h3 class="text-lg font-medium mt-4 mb-2" style="color: var(--od-fg);">1.4 Technical Information</h3>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>IP address and browser type</li>
 <li>Device information and operating system</li>
 <li>Login timestamps and session activity</li>
 <li>Cookie data for authentication and preferences</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">2. How We Use Your Information</h2>
 <p class="od-meta">We use your information for the following purposes:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li><strong>Account management:</strong> To create and maintain your user account</li>
 <li><strong>Course delivery:</strong> To provide access to enrolled courses, track progress, and issue certificates</li>
 <li><strong>Communication:</strong> To send course updates, session reminders, payment notifications, and system alerts</li>
 <li><strong>Payment processing:</strong> To process course fees, registration fees, and generate invoices</li>
 <li><strong>Analytics:</strong> To improve our courses and platform based on aggregated usage data</li>
 <li><strong>Compliance:</strong> To meet TEVETA reporting requirements and other legal obligations</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">3. Information Sharing</h2>
 <p class="od-meta">We do not sell your personal information. We may share your information only in the following circumstances:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li><strong>With instructors:</strong> Instructors can see the names, emails, and progress of students enrolled in their courses</li>
 <li><strong>With payment processors:</strong> Lenco, MTN, Airtel, and Zamtel receive only the information necessary to process payments</li>
 <li><strong>With TEVETA:</strong> We may share aggregated enrollment and completion data for accreditation purposes</li>
 <li><strong>Legal requirements:</strong> We may disclose information if required by Zambian law or court order</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">4. Data Storage & Security</h2>
 <p class="od-meta">We take data security seriously:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>All data is stored on secure servers with regular backups</li>
 <li>Passwords are hashed using industry-standard algorithms</li>
 <li>Sensitive data is encrypted at rest and in transit (SSL/TLS)</li>
 <li>Access to student data is restricted to authorized staff only</li>
 <li>We conduct regular security audits and vulnerability assessments</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">5. Data Retention</h2>
 <p class="od-meta">We retain your information for as long as necessary to provide our services:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Active accounts: Data retained while your account is active</li>
 <li>Inactive accounts: Data retained for 2 years after last login, then anonymized</li>
 <li>Academic records: Retained indefinitely for TEVETA compliance and transcript verification</li>
 <li>Payment records: Retained for 7 years per Zambian tax requirements</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">6. Your Rights</h2>
 <p class="od-meta">As a user, you have the right to:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li><strong>Access:</strong> Request a copy of the personal data we hold about you</li>
 <li><strong>Correction:</strong> Update inaccurate or incomplete information through your profile</li>
 <li><strong>Deletion:</strong> Request deletion of your account (subject to academic record retention requirements)</li>
 <li><strong>Portability:</strong> Request export of your learning data and certificates</li>
 <li><strong>Opt-out:</strong> Unsubscribe from non-essential communications at any time</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">7. Cookies</h2>
 <p class="od-meta">We use cookies to:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Maintain your login session</li>
 <li>Remember your preferences (theme, language)</li>
 <li>Analyze platform usage to improve our services</li>
 </ul>
 <p class="od-meta">You can disable cookies in your browser settings, but this may affect your ability to use certain features of the platform.</p>

 <h2 class="od-h3 mt-8 mb-3">8. Third-Party Services</h2>
 <p class="od-meta">We integrate with the following third-party services:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li><strong>Lenco:</strong> Payment processing (bank transfers)</li>
 <li><strong>MTN / Airtel / Zamtel:</strong> Mobile money payments</li>
 <li><strong>Google:</strong> OAuth login and Drive storage (optional)</li>
 <li><strong>Jitsi Meet:</strong> Live video sessions</li>
 </ul>
 <p class="od-meta">Each of these services has its own privacy policy governing their use of your data.</p>

 <h2 class="od-h3 mt-8 mb-3">9. Children's Privacy</h2>
 <p class="od-meta">Our platform is not intended for children under 16. If we discover that a child under 16 has provided personal information without parental consent, we will delete that information promptly.</p>

 <h2 class="od-h3 mt-8 mb-3">10. Changes to This Policy</h2>
 <p class="od-meta">We may update this Privacy Policy periodically. We will notify you of significant changes via email or through the platform. Continued use after changes constitutes acceptance of the updated policy.</p>

 <h2 class="od-h3 mt-8 mb-3">11. Contact Us</h2>
 <p class="od-meta">If you have questions or concerns about this Privacy Policy or your data, please contact us:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Email: edutrackzambia@gmail.com</li>
 <li>Phone: +260 770 666 937 / +260 965 992 967</li>
 <li>Address: Kalomo, Zambia</li>
 <li>Data Protection Officer: edutrackcomputertrainingschool@gmail.com</li>
 </ul>
 </div>
 </div>
 </div>
</div>
@endsection
