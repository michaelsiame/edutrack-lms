<?php
/**
 * Privacy Policy
 */
require_once __DIR__ . '/../src/bootstrap.php';

$page_title = "Privacy Policy - Edutrack Computer Training College";
require_once __DIR__ . '/../src/templates/header.php';
?>

<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
        <p class="text-sm text-gray-500 mb-8">Last updated: <?= date('F Y') ?></p>

        <div class="prose prose-lg max-w-none text-gray-700 space-y-8">

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Introduction</h2>
                <p>
                    Edutrack Computer Training College ("Edutrack", "we", "us") is committed to protecting the privacy
                    of our students, instructors, and website visitors. This policy explains how we collect, use, store,
                    and protect your personal information in compliance with the Data Protection Act No. 3 of 2021 of Zambia.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Information We Collect</h2>
                <p>We collect the following categories of personal data:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li><strong>Identity data:</strong> Full name, National Registration Card (NRC) number, date of birth, profile photo.</li>
                    <li><strong>Contact data:</strong> Email address, phone number, postal address.</li>
                    <li><strong>Account data:</strong> Username, password (stored encrypted), role, login history.</li>
                    <li><strong>Education data:</strong> Enrolment records, course progress, quiz scores, assignments, certificates issued.</li>
                    <li><strong>Financial data:</strong> Payment records, transaction references, mobile money phone numbers. We do not store full card numbers.</li>
                    <li><strong>Technical data:</strong> IP address, browser type, device information, session cookies.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. How We Use Your Information</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li>To create and manage your student or instructor account.</li>
                    <li>To process course enrolments and payments.</li>
                    <li>To track your learning progress and issue certificates.</li>
                    <li>To communicate important updates, announcements, and support responses.</li>
                    <li>To improve our courses, platform, and services.</li>
                    <li>To comply with TEVETA reporting requirements.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Third-Party Services</h2>
                <p>We share limited data with the following third parties:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li><strong>Lenco:</strong> Payment processing (transaction details only).</li>
                    <li><strong>Google:</strong> OAuth login (email and name only, if you choose Google sign-in).</li>
                    <li><strong>TEVETA:</strong> Student records for accreditation compliance, as required by law.</li>
                </ul>
                <p class="mt-2">We do not sell your personal information to any third party.</p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Data Retention</h2>
                <p>
                    We retain your personal data for as long as your account is active, plus 5 years after your last
                    course completion for certification verification purposes. Financial records are retained for 7 years
                    as required by Zambian tax regulations. You may request deletion of your account at any time.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Your Rights</h2>
                <p>Under the Data Protection Act 2021, you have the right to:</p>
                <ul class="list-disc pl-6 space-y-2 mt-2">
                    <li>Access the personal data we hold about you.</li>
                    <li>Request correction of inaccurate data.</li>
                    <li>Request deletion of your data (subject to legal retention requirements).</li>
                    <li>Object to processing of your data for marketing purposes.</li>
                    <li>Receive your data in a portable format.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Security</h2>
                <p>
                    We implement industry-standard security measures including encrypted passwords (bcrypt),
                    CSRF protection, session management, and HTTPS encryption to protect your data. Access to
                    personal data is restricted to authorised staff on a need-to-know basis.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Cookies</h2>
                <p>
                    We use essential session cookies to keep you logged in and manage your preferences.
                    We do not use third-party advertising cookies. Analytics cookies (if enabled) are used
                    only to understand how visitors use our site so we can improve it.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Contact Us</h2>
                <p>
                    For privacy-related enquiries or to exercise your data rights, contact us:
                </p>
                <div class="bg-gray-50 rounded-lg p-4 mt-3">
                    <p><strong>Data Protection Officer</strong></p>
                    <p>Edutrack Computer Training College</p>
                    <p>Email: <a href="mailto:<?= SITE_EMAIL ?>" class="text-primary-600 hover:underline"><?= SITE_EMAIL ?></a></p>
                    <p>Phone: <a href="tel:<?= SITE_PHONE ?>" class="text-primary-600 hover:underline"><?= SITE_PHONE ?></a></p>
                    <p>Address: <?= SITE_ADDRESS ?></p>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
