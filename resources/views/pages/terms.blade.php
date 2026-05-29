@extends('layouts.app')

@section('title', 'Terms of Service - Edutrack LMS')

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
 <h1 class="od-h1 mt-2">Terms of Service</h1>
 <p class="od-meta mt-2" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">Last updated: {{ date('F Y') }}</p>
 </div>
 </div>

 <div class="max-w-4xl mx-auto px-4 py-12">
 <div class="od-card p-8 md:p-12">
 <div class="prose max-w-none">
 <p class="lead od-meta text-base">Welcome to Edutrack Computer Training College. By accessing or using our learning management system, you agree to be bound by these Terms of Service.</p>

 <h2 class="od-h3 mt-8 mb-3">1. Acceptance of Terms</h2>
 <p class="od-meta">By registering for an account, enrolling in a course, or using any part of the Edutrack LMS platform, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.</p>

 <h2 class="od-h3 mt-8 mb-3">2. Eligibility</h2>
 <p class="od-meta">To use our services, you must:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Be at least 16 years of age or have parental/guardian consent</li>
 <li>Provide accurate and complete registration information</li>
 <li>Maintain the security of your account credentials</li>
 <li>Be a resident of Zambia or have valid authorization to study with us</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">3. Course Enrollment & Payments</h2>
 <p class="od-meta">All course fees are quoted in Zambian Kwacha (ZMW). Payment terms are as follows:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>A non-refundable registration fee of K150 is required for all new students</li>
 <li>A minimum deposit of 30% of the course fee is required to activate enrollment</li>
 <li>Full payment must be completed before certificate issuance</li>
 <li>Payments are accepted via Lenco bank transfer, MTN Mobile Money, Airtel Money, Zamtel Kwacha, or direct bank deposit</li>
 <li>Course fees are non-transferable between students</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">4. Refund Policy</h2>
 <p class="od-meta">Refund requests are subject to the following conditions:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>100% refund if requested within 7 days of enrollment and before accessing course content</li>
 <li>50% refund if requested within 14 days and less than 25% of course content has been accessed</li>
 <li>No refund after 14 days or after accessing more than 25% of course content</li>
 <li>Registration fees are non-refundable under all circumstances</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">5. Intellectual Property</h2>
 <p class="od-meta">All course content, including videos, documents, quizzes, and assignments, is the intellectual property of Edutrack Computer Training College or its licensed partners. You may not:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Download, copy, or distribute course materials for commercial purposes</li>
 <li>Share your account credentials with any other person</li>
 <li>Record, screenshot, or reproduce live sessions without written permission</li>
 <li>Use course content to create competing educational materials</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">6. Certificates</h2>
 <p class="od-meta">Certificates are issued under the following conditions:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Successful completion of all required modules and assessments</li>
 <li>Full payment of all course fees</li>
 <li>Minimum attendance requirement for live sessions (where applicable)</li>
 <li>Certificates are verified through our public verification system</li>
 <li>Certificates remain the property of Edutrack and TEVETA</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">7. Code of Conduct</h2>
 <p class="od-meta">All users must conduct themselves professionally. Prohibited behavior includes:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Harassment, discrimination, or abusive language toward instructors or students</li>
 <li>Posting inappropriate content in discussion forums</li>
 <li>Attempting to hack, disrupt, or gain unauthorized access to the platform</li>
 <li>Impersonating Edutrack staff or other users</li>
 </ul>

 <h2 class="od-h3 mt-8 mb-3">8. Account Suspension & Termination</h2>
 <p class="od-meta">Edutrack reserves the right to suspend or terminate accounts that violate these terms. In cases of termination for cause, no refund will be issued.</p>

 <h2 class="od-h3 mt-8 mb-3">9. Changes to Terms</h2>
 <p class="od-meta">We may update these Terms from time to time. Continued use of the platform after changes constitutes acceptance of the revised Terms.</p>

 <h2 class="od-h3 mt-8 mb-3">10. Contact</h2>
 <p class="od-meta">For questions about these Terms, please contact us:</p>
 <ul class="list-disc pl-6 space-y-2 od-meta">
 <li>Email: edutrackzambia@gmail.com</li>
 <li>Phone: +260 770 666 937 / +260 965 992 967</li>
 <li>Address: Kalomo, Zambia</li>
 </ul>
 </div>
 </div>
 </div>
</div>
@endsection
