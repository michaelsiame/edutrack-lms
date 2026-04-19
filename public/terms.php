<?php
/**
 * Terms of Service
 * Edutrack Computer Training College
 */

require_once '../src/bootstrap.php';

$page_title = "Terms of Service - Edutrack Computer Training College";
$page_description = "Terms and conditions for enrolling and using Edutrack Computer Training College services.";

require_once '../src/templates/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-br from-primary-600 via-blue-700 to-purple-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Terms of Service</h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Please read these terms carefully before enrolling in any course at Edutrack Computer Training College.
            </p>
            <p class="text-sm text-primary-200 mt-4">Last Updated: April 2026</p>
        </div>
    </div>
</section>

<!-- Terms Content -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Introduction -->
        <div class="prose prose-lg max-w-none mb-12">
            <p class="text-gray-600">
                These Terms of Service ("Terms") govern your enrollment, access to, and use of the educational services 
                provided by Edutrack Computer Training College ("the College," "we," "our," or "us"). By enrolling in 
                any course or using our website, you agree to be bound by these Terms.
            </p>
        </div>

        <!-- Table of Contents -->
        <div class="bg-gray-50 rounded-xl p-6 mb-12">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Contents</h2>
            <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                <li><a href="#enrollment" class="text-primary-600 hover:underline">1. Enrollment & Eligibility</a></li>
                <li><a href="#fees" class="text-primary-600 hover:underline">2. Fees & Payment</a></li>
                <li><a href="#refunds" class="text-primary-600 hover:underline">3. Refund Policy</a></li>
                <li><a href="#conduct" class="text-primary-600 hover:underline">4. Student Conduct</a></li>
                <li><a href="#intellectual" class="text-primary-600 hover:underline">5. Intellectual Property</a></li>
                <li><a href="#certification" class="text-primary-600 hover:underline">6. Certification</a></li>
                <li><a href="#termination" class="text-primary-600 hover:underline">7. Termination</a></li>
                <li><a href="#liability" class="text-primary-600 hover:underline">8. Limitation of Liability</a></li>
                <li><a href="#governing" class="text-primary-600 hover:underline">9. Governing Law</a></li>
                <li><a href="#changes" class="text-primary-600 hover:underline">10. Changes to Terms</a></li>
            </ul>
        </div>

        <!-- Section 1: Enrollment -->
        <div id="enrollment" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">1</span>
                Enrollment & Eligibility
            </h2>
            
            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Eligibility Requirements</h3>
            <p class="text-gray-600 mb-4">To enroll in our programs, you must:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>Be at least 16 years of age (parental consent required for students under 18)</li>
                <li>Hold a minimum Grade 12 certificate or equivalent qualification</li>
                <li>Provide valid identification (NRC, passport, or driver's license)</li>
                <li>Meet any course-specific prerequisites stated in the course description</li>
                <li>Have basic computer literacy for advanced technical courses</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Registration Process</h3>
            <p class="text-gray-600 mb-4">Enrollment is complete only when:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>The online or physical application form is fully completed</li>
                <li>All required documents are submitted (ID, certificates, photos)</li>
                <li>The registration fee is paid (non-refundable)</li>
                <li>You receive written confirmation of acceptance from the College</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Intake Dates</h3>
            <p class="text-gray-600">The College operates three main intakes per year: January, May, and September. 
            Specific start dates are published on our website and may be subject to change. The College reserves 
            the right to cancel or postpone courses with insufficient enrollment, with full refunds provided.</p>
        </div>

        <!-- Section 2: Fees -->
        <div id="fees" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">2</span>
                Fees & Payment
            </h2>
            
            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Fee Structure</h3>
            <p class="text-gray-600 mb-4">Course fees are published on our website and may be updated annually. 
            Fees include:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>Tuition and instruction</li>
                <li>Course materials and handouts</li>
                <li>Access to computer labs and equipment</li>
                <li>Examination fees (first attempt)</li>
                <li>TEVETA certificate processing</li>
            </ul>
            <p class="text-gray-600 mt-4">Fees <strong>do not</strong> include: Transportation, accommodation, meals, 
            international certification exam fees (Microsoft, Cisco, CompTIA), or personal computing equipment.</p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Payment Methods</h3>
            <p class="text-gray-600 mb-4">We accept payment via:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>Bank transfer to our Zanaco account</li>
                <li>MTN Mobile Money</li>
                <li>Airtel Money</li>
                <li>Cash payment at our campus (receipts provided)</li>
                <li>Lenco online payment through our website</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Payment Plans</h3>
            <p class="text-gray-600">We offer flexible installment plans:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li><strong>Standard Plan:</strong> 50% on registration, 50% after one month</li>
                <li><strong>Monthly Plan:</strong> Three equal monthly payments</li>
            </ul>
            <p class="text-gray-600 mt-4">All installments must be completed before the final examination. 
            Students with outstanding balances may not be permitted to sit for exams or receive certificates.</p>
        </div>

        <!-- Section 3: Refunds -->
        <div id="refunds" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">3</span>
                Refund Policy
            </h2>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Important:</strong> The registration fee is <strong>non-refundable</strong> in all circumstances.
                </p>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Tuition Fee Refunds</h3>
            <table class="w-full text-sm text-left text-gray-600 mb-4">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Withdrawal Timing</th>
                        <th class="px-4 py-3">Refund Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="px-4 py-3">Before course start date</td>
                        <td class="px-4 py-3 font-medium text-green-600">100% of tuition fees</td>
                    </tr>
                    <tr class="border-b">
                        <td class="px-4 py-3">Within first week of classes</td>
                        <td class="px-4 py-3 font-medium text-yellow-600">75% of tuition fees</td>
                    </tr>
                    <tr class="border-b">
                        <td class="px-4 py-3">Within second week of classes</td>
                        <td class="px-4 py-3 font-medium text-orange-600">50% of tuition fees</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3">After second week</td>
                        <td class="px-4 py-3 font-medium text-red-600">No refund</td>
                    </tr>
                </tbody>
            </table>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Refund Process</h3>
            <p class="text-gray-600 mb-4">To request a refund:</p>
            <ol class="list-decimal list-inside text-gray-600 space-y-2 ml-4">
                <li>Submit a written withdrawal request to the Registrar</li>
                <li>Provide original proof of payment</li>
                <li>Return any issued materials (if applicable)</li>
                <li>Allow 14 business days for processing</li>
            </ol>
            <p class="text-gray-600 mt-4">Refunds are processed via the original payment method. Mobile money refunds 
            may attract transaction fees deducted from the refund amount.</p>
        </div>

        <!-- Section 4: Conduct -->
        <div id="conduct" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">4</span>
                Student Conduct
            </h2>
            
            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Academic Integrity</h3>
            <p class="text-gray-600 mb-4">Students are expected to maintain the highest standards of academic honesty. 
            The following are prohibited:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>Plagiarism (submitting others' work as your own)</li>
                <li>Cheating on examinations or assignments</li>
                <li>Unauthorized collaboration on individual assessments</li>
                <li>Falsifying academic records or certificates</li>
                <li>Using unauthorized materials during exams</li>
            </ul>
            <p class="text-gray-600 mt-4">Violations will result in disciplinary action, including possible 
            suspension or expulsion without refund.</p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Campus Behavior</h3>
            <p class="text-gray-600 mb-4">On campus and in online learning environments, students must:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>Treat staff and fellow students with respect</li>
                <li>Refrain from harassment, discrimination, or bullying</li>
                <li>Follow all safety protocols and instructions</li>
                <li>Not consume alcohol or illegal substances on premises</li>
                <li>Respect College property and equipment</li>
                <li>Maintain a professional learning environment</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Attendance</h3>
            <p class="text-gray-600">Regular attendance is mandatory. Students must maintain at least 80% attendance 
            to be eligible for final examinations and certification. Absences due to illness require medical 
            documentation.</p>
        </div>

        <!-- Section 5: Intellectual Property -->
        <div id="intellectual" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">5</span>
                Intellectual Property
            </h2>
            
            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">College Materials</h3>
            <p class="text-gray-600">All course materials, lecture notes, presentations, videos, and assessments 
            are the intellectual property of Edutrack Computer Training College. These materials are provided 
            for your personal educational use only. You may not:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>Distribute materials to non-enrolled individuals</li>
                <li>Sell or commercially exploit course content</li>
                <li>Upload materials to public websites or file-sharing platforms</li>
                <li>Record lectures without prior written permission</li>
                <li>Remove copyright notices from materials</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Student Work</h3>
            <p class="text-gray-600">You retain ownership of original work you create during your studies. 
            However, by submitting assignments and projects, you grant the College a non-exclusive license to 
            use your work for educational, promotional, and accreditation purposes (with attribution).</p>
        </div>

        <!-- Section 6: Certification -->
        <div id="certification" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">6</span>
                Certification
            </h2>
            
            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Award Requirements</h3>
            <p class="text-gray-600 mb-4">To receive a certificate, students must:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>Complete all required coursework and assignments</li>
                <li>Achieve a minimum overall grade of 50%</li>
                <li>Pass all required examinations</li>
                <li>Meet the attendance requirement (80% minimum)</li>
                <li>Have no outstanding fees or library books</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Certificate Verification</h3>
            <p class="text-gray-600">All certificates issued by the College can be verified online at 
            <a href="certificate-verify.php" class="text-primary-600 hover:underline">edutrackzambia.com/certificate-verify.php</a>. 
            Certificates remain the property of the College and may be revoked if obtained through fraud or misrepresentation.</p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Transcripts</h3>
            <p class="text-gray-600">Official transcripts may be requested from the Registrar's office. 
            A processing fee applies. Transcripts will not be issued to students with outstanding financial obligations.</p>
        </div>

        <!-- Section 7: Termination -->
        <div id="termination" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">7</span>
                Termination
            </h2>
            
            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">By the Student</h3>
            <p class="text-gray-600">You may withdraw from a course at any time by submitting written notice to the Registrar. 
            Refund eligibility is determined by the Refund Policy (Section 3).</p>

            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">By the College</h3>
            <p class="text-gray-600 mb-4">The College reserves the right to terminate your enrollment immediately, without refund, for:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>Academic dishonesty or plagiarism</li>
                <li>Violent, threatening, or harassing behavior</li>
                <li>Theft or intentional damage to College property</li>
                <li>Bringing illegal substances or weapons on campus</li>
                <li>Falsifying enrollment information or documents</li>
                <li>Conviction of a criminal offense affecting suitability</li>
            </ul>
        </div>

        <!-- Section 8: Liability -->
        <div id="liability" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">8</span>
                Limitation of Liability
            </h2>
            
            <p class="text-gray-600 mb-4">To the maximum extent permitted by law:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                <li>The College is not liable for any indirect, incidental, special, or consequential damages</li>
                <li>Our total liability shall not exceed the total fees paid for the specific course in question</li>
                <li>We do not guarantee specific employment outcomes or salary levels</li>
                <li>We are not responsible for lost or stolen personal property on campus</li>
                <li>Online service availability is provided "as is" without uptime guarantees</li>
            </ul>
            <p class="text-gray-600 mt-4">Students are responsible for maintaining backups of their work and 
            for ensuring their own computer systems meet course requirements.</p>
        </div>

        <!-- Section 9: Governing Law -->
        <div id="governing" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">9</span>
                Governing Law
            </h2>
            
            <p class="text-gray-600">These Terms are governed by the laws of the Republic of Zambia. 
            Any disputes shall first be attempted to be resolved through mediation. If mediation fails, 
            disputes shall be submitted to the courts of Zambia.</p>
        </div>

        <!-- Section 10: Changes -->
        <div id="changes" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm mr-3">10</span>
                Changes to Terms
            </h2>
            
            <p class="text-gray-600">The College reserves the right to modify these Terms at any time. 
            Changes will be effective immediately upon posting to our website. Your continued enrollment 
            constitutes acceptance of the revised Terms. Material changes will be communicated via email 
            to enrolled students.</p>
        </div>

        <!-- Contact -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Questions?</h3>
            <p class="text-gray-600 mb-4">If you have any questions about these Terms, please contact us:</p>
            <div class="bg-gray-50 rounded-xl p-6">
                <p class="text-gray-600">
                    <strong>Edutrack Computer Training College</strong><br>
                    Registrar's Office<br>
                    Kalomo, Southern Province, Zambia<br><br>
                    <i class="fas fa-envelope mr-2 text-primary-600"></i>
                    <a href="mailto:registrar@edutrackzambia.com" class="text-primary-600 hover:underline">registrar@edutrackzambia.com</a><br>
                    <i class="fas fa-phone mr-2 text-primary-600"></i>
                    <a href="tel:<?= SITE_PHONE ?>" class="text-primary-600 hover:underline"><?= SITE_PHONE ?></a>
                </p>
            </div>
        </div>

    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>
