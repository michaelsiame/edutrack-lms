<?php
/**
 * Terms of Service
 */
require_once '../src/bootstrap.php';

$page_title = "Terms of Service - Edutrack Computer Training College";
require_once '../src/templates/header.php';
?>

<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Terms of Service</h1>
        <p class="text-sm text-gray-500 mb-8">Last updated: <?= date('F Y') ?></p>

        <div class="prose prose-lg max-w-none text-gray-700 space-y-8">

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Acceptance of Terms</h2>
                <p>
                    By creating an account on the Edutrack Learning Management System or enrolling in any course
                    offered by Edutrack Computer Training College ("Edutrack"), you agree to be bound by these
                    Terms of Service. If you do not agree with these terms, please do not use our platform.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Enrolment and Access</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li>You must provide accurate personal information during registration.</li>
                    <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
                    <li>Access to paid course content requires completed or partial payment (minimum 30% deposit).</li>
                    <li>Enrolment is personal and non-transferable.</li>
                    <li>Edutrack reserves the right to suspend accounts that violate these terms.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Fees and Payments</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li>All course fees are displayed in Zambian Kwacha (ZMW).</li>
                    <li>A minimum deposit of 30% is required to access course content.</li>
                    <li>Outstanding balances must be cleared before a certificate of completion is issued.</li>
                    <li>Payment can be made via mobile money (MTN, Airtel, Zamtel), bank transfer, or Lenco online payment.</li>
                    <li>Edutrack reserves the right to adjust course fees. Enrolled students will not be affected by price changes for their current enrolment.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Refund Policy</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Within 7 days of enrolment:</strong> Full refund if less than 20% of course content has been accessed.</li>
                    <li><strong>8-14 days after enrolment:</strong> 50% refund, provided less than 30% of course content has been accessed.</li>
                    <li><strong>After 14 days:</strong> No refund is available.</li>
                    <li>Registration fees are non-refundable.</li>
                    <li>Refund requests must be submitted in writing to <a href="mailto:<?= SITE_EMAIL ?>" class="text-primary-600 hover:underline"><?= SITE_EMAIL ?></a>.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Intellectual Property</h2>
                <p>
                    All course materials, including videos, documents, quizzes, and assignments, are the intellectual
                    property of Edutrack Computer Training College and its instructors. Students may not reproduce,
                    distribute, or share course content without written permission.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Student Conduct</h2>
                <p>Students agree to:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Complete their own assignments and quizzes without plagiarism or cheating.</li>
                    <li>Treat fellow students, instructors, and staff with respect in all interactions.</li>
                    <li>Not attempt to gain unauthorised access to other accounts or system resources.</li>
                    <li>Not share their account credentials with others.</li>
                </ul>
                <p class="mt-2">
                    Violation of conduct rules may result in suspension or permanent removal from the platform
                    without refund.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Certificates</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Certificates are issued upon successful course completion (minimum passing grade as defined per course).</li>
                    <li>TEVETA-certified courses carry the TEVETA registration code (<?= TEVETA_CODE ?>).</li>
                    <li>Certificates include a unique verification code that can be validated at our verification portal.</li>
                    <li>Certificates are non-transferable and may be revoked if obtained through fraud or academic dishonesty.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Limitation of Liability</h2>
                <p>
                    Edutrack strives to provide uninterrupted access to our platform but does not guarantee 100%
                    uptime. We are not liable for losses arising from temporary service interruptions, data loss
                    due to circumstances beyond our control, or outcomes of career decisions made based on our courses.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Changes to Terms</h2>
                <p>
                    Edutrack may update these Terms of Service from time to time. We will notify registered users
                    of significant changes via email. Continued use of the platform after changes constitutes
                    acceptance of the updated terms.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">10. Governing Law</h2>
                <p>
                    These terms are governed by and construed in accordance with the laws of the Republic of Zambia.
                    Any disputes shall be submitted to the jurisdiction of the courts of Zambia.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">11. Contact</h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p><strong>Edutrack Computer Training College</strong></p>
                    <p>Email: <a href="mailto:<?= SITE_EMAIL ?>" class="text-primary-600 hover:underline"><?= SITE_EMAIL ?></a></p>
                    <p>Phone: <a href="tel:<?= SITE_PHONE ?>" class="text-primary-600 hover:underline"><?= SITE_PHONE ?></a></p>
                    <p>Address: <?= SITE_ADDRESS ?></p>
                    <p>TEVETA Registration: <?= TEVETA_CODE ?></p>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>
