<?php
/**
 * Student Help & Guide
 * Quick reference for students using the LMS
 */

require_once '../../src/bootstrap.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = User::current();

$page_title = "Help & Guide - Student";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900">Student Help Center</h1>
            <p class="text-gray-600 mt-2">Quick answers to common questions</p>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <a href="#getting-started" class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
                <i class="fas fa-rocket text-blue-500 text-2xl mb-2"></i>
                <p class="font-medium text-gray-800 text-sm">Getting Started</p>
            </a>
            <a href="#courses" class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
                <i class="fas fa-book text-green-500 text-2xl mb-2"></i>
                <p class="font-medium text-gray-800 text-sm">My Courses</p>
            </a>
            <a href="#assignments" class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
                <i class="fas fa-file-alt text-orange-500 text-2xl mb-2"></i>
                <p class="font-medium text-gray-800 text-sm">Assignments</p>
            </a>
            <a href="#support" class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
                <i class="fas fa-headset text-purple-500 text-2xl mb-2"></i>
                <p class="font-medium text-gray-800 text-sm">Get Support</p>
            </a>
        </div>

        <!-- Getting Started -->
        <section id="getting-started" class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-rocket text-blue-500 mr-3"></i>
                Getting Started
            </h2>
            
            <div class="space-y-4 text-gray-600">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">How do I access my courses?</h3>
                    <p>After logging in, click on <strong>"My Courses"</strong> in the navigation menu or from the Student Hub. You'll see all your enrolled courses with progress indicators.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">What is the Student Hub?</h3>
                    <p>The Student Hub is your central dashboard showing quick stats, recent activity, and shortcuts to all features. Access it anytime from the main navigation.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">How do I update my profile?</h3>
                    <p>Click on your name or avatar in the top right corner, then select <strong>"Profile"</strong> or <strong>"Settings"</strong> to update your information and upload a profile picture.</p>
                </div>
            </div>
        </section>

        <!-- Courses -->
        <section id="courses" class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-book text-green-500 mr-3"></i>
                Taking Courses
            </h2>
            
            <div class="space-y-4 text-gray-600">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">How do I start a course?</h3>
                    <p>Go to <strong>My Courses</strong> and click the <strong>"Continue"</strong> or <strong>"Start Course"</strong> button on any course card. This will take you to the course content.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">How is my progress tracked?</h3>
                    <p>Progress is automatically tracked as you:</p>
                    <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                        <li>Watch video lessons (90% completion required)</li>
                        <li>Mark reading lessons as complete</li>
                        <li>Submit quizzes and assignments</li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Can I download course materials?</h3>
                    <p>Yes! Look for the <strong>"Resources"</strong> or <strong>"Downloads"</strong> section within each lesson. Instructors may provide PDFs, slides, or other materials you can download.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">What happens when I complete a course?</h3>
                    <p>Once you finish all lessons and requirements, you'll automatically receive a <strong>Certificate of Completion</strong> that you can download and share. Find it in the "My Certificates" section.</p>
                </div>
            </div>
        </section>

        <!-- Assignments -->
        <section id="assignments" class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-file-alt text-orange-500 mr-3"></i>
                Assignments & Quizzes
            </h2>
            
            <div class="space-y-4 text-gray-600">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">How do I submit an assignment?</h3>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Go to <strong>Student → Assignments</strong> or find the assignment in your course</li>
                        <li>Click on the assignment to view details</li>
                        <li>Upload your file or enter text in the submission box</li>
                        <li>Click <strong>"Submit Assignment"</strong></li>
                    </ol>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Can I retake a quiz?</h3>
                    <p>This depends on your instructor's settings. Some quizzes allow multiple attempts, while others may be limited to one try. Check the quiz instructions before starting.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">When will I receive my grades?</h3>
                    <p><strong>Quizzes:</strong> Usually graded immediately (for multiple choice).</p>
                    <p><strong>Assignments:</strong> Instructors typically grade within 3-5 business days. You'll receive a notification when grading is complete.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">What if I miss a deadline?</h3>
                    <p>Late submissions may still be accepted but could receive penalties. Contact your instructor as soon as possible if you need an extension.</p>
                </div>
            </div>
        </section>

        <!-- Certificates -->
        <section id="certificates" class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-certificate text-yellow-500 mr-3"></i>
                Certificates
            </h2>
            
            <div class="space-y-4 text-gray-600">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">How do I get my certificate?</h3>
                    <p>Complete all required lessons and assessments in a course. Once finished, your certificate will be automatically generated and available in <strong>"My Certificates"</strong>.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Can I share my certificate?</h3>
                    <p>Yes! You can download your certificate as a PDF and share it on LinkedIn, social media, or include it in your resume.</p>
                </div>
            </div>
        </section>

        <!-- Payments -->
        <section id="payments" class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-credit-card text-red-500 mr-3"></i>
                Payments & Fees
            </h2>
            
            <div class="space-y-4 text-gray-600">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">How do I pay course fees?</h3>
                    <p>Go to <strong>"My Payments"</strong> to view your balance and make payments. We accept various payment methods including bank transfer and mobile money.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">What is the registration fee?</h3>
                    <p>There's a one-time registration fee for new students. This must be paid before you can access courses. Contact administration for payment plan options.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">Can I get a refund?</h3>
                    <p>Refund policies vary by course. Generally, refunds may be available within 7 days of enrollment if you haven't completed more than 30% of the course. Contact support for refund requests.</p>
                </div>
            </div>
        </section>

        <!-- Troubleshooting -->
        <section id="troubleshooting" class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-wrench text-gray-500 mr-3"></i>
                Troubleshooting
            </h2>
            
            <div class="space-y-4">
                <details class="group border rounded-lg overflow-hidden">
                    <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                        <span class="font-medium text-gray-800">Video won't play</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="p-4 text-gray-600 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Check your internet connection</li>
                            <li>Try a different browser (Chrome, Firefox, Safari)</li>
                            <li>Clear your browser cache</li>
                            <li>Disable browser extensions that might block videos</li>
                            <li>Ensure your browser is up to date</li>
                        </ul>
                    </div>
                </details>

                <details class="group border rounded-lg overflow-hidden">
                    <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                        <span class="font-medium text-gray-800">Can't log in</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="p-4 text-gray-600 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Double-check your email address</li>
                            <li>Make sure Caps Lock is off</li>
                            <li>Try resetting your password using "Forgot Password"</li>
                            <li>Clear browser cookies and try again</li>
                        </ul>
                    </div>
                </details>

                <details class="group border rounded-lg overflow-hidden">
                    <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                        <span class="font-medium text-gray-800">Assignment submission failed</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="p-4 text-gray-600 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Check file size (max 50MB for most files)</li>
                            <li>Ensure file type is allowed (PDF, DOC, etc.)</li>
                            <li>Try a different browser</li>
                            <li>Check your internet connection is stable</li>
                        </ul>
                    </div>
                </details>

                <details class="group border rounded-lg overflow-hidden">
                    <summary class="flex items-center justify-between p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                        <span class="font-medium text-gray-800">Progress not updating</span>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="p-4 text-gray-600 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Refresh the page to see updated progress</li>
                            <li>For videos, ensure you watch at least 90%</li>
                            <li>For readings, click "Mark as Complete" button</li>
                            <li>Contact instructor if issue persists</li>
                        </ul>
                    </div>
                </details>
            </div>
        </section>

        <!-- Support -->
        <section id="support" class="bg-blue-600 rounded-xl p-8 text-center text-white">
            <h2 class="text-2xl font-bold mb-3">Still Need Help?</h2>
            <p class="text-blue-100 mb-6">
                Can't find what you're looking for? Our support team is ready to assist you.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="mailto:<?= SITE_EMAIL ?>" class="inline-flex items-center px-6 py-3 bg-white text-blue-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-envelope mr-2"></i>Email Support
                </a>
                <a href="tel:<?= SITE_PHONE ?>" class="inline-flex items-center px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition">
                    <i class="fas fa-phone mr-2"></i>Call Us
                </a>
            </div>
            <p class="text-blue-200 text-sm mt-4">
                Support hours: Monday-Friday, 8:00 AM - 5:00 PM CAT
            </p>
        </section>
    </div>
</div>

<?php require_once '../../src/templates/footer.php'; ?>
