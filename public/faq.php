<?php
/**
 * FAQ Page
 * Frequently Asked Questions
 */

require_once __DIR__ . '/../src/bootstrap.php';

$page_title = "FAQ - Frequently Asked Questions - Edutrack";

$faqCategories = [
    'admissions' => [
        'title' => 'Admissions & Enrollment',
        'icon' => 'fa-user-plus',
        'questions' => [
            [
                'q' => 'What are the admission requirements?',
                'a' => 'Basic requirements include: (1) Minimum Grade 12 certificate or equivalent, (2) Valid ID (NRC/Passport), (3) Completed application form, and (4) Registration fee payment. Some advanced courses may require prior computer literacy or specific subject passes.'
            ],
            [
                'q' => 'How do I apply for a course?',
                'a' => 'You can apply online through our website by creating an account and selecting your desired course, or visit our campus in Kalomo to apply in person. Our admissions team is also available via WhatsApp to guide you through the process.'
            ],
            [
                'q' => 'When are the intakes?',
                'a' => 'We have three main intakes per year: January, May, and September. Short courses and workshops may have more frequent start dates. Check our events page or contact admissions for the next available intake for your specific course.'
            ],
            [
                'q' => 'Can I switch courses after enrolling?',
                'a' => 'Yes, course transfers are possible within the first two weeks of the program start date, subject to availability and approval. A small administrative fee may apply. Please contact the registrar for assistance.'
            ],
        ]
    ],
    'payments' => [
        'title' => 'Fees & Payments',
        'icon' => 'fa-money-bill-wave',
        'questions' => [
            [
                'q' => 'How much are the course fees?',
                'a' => 'Course fees vary depending on the program. Certificate courses range from ZMW 1,500 to ZMW 4,500, while Diploma programs range from ZMW 6,000 to ZMW 12,000. Visit individual course pages for exact pricing or request a fee structure from our admissions office.'
            ],
            [
                'q' => 'Do you offer payment plans?',
                'a' => 'Yes! We understand that investing in education is significant. We offer flexible installment plans: (1) Pay 50% upfront and 50% after one month, or (2) Three equal monthly payments. Corporate-sponsored students may have different arrangements.'
            ],
            [
                'q' => 'What payment methods do you accept?',
                'a' => 'We accept: (1) Bank transfers to our Zanaco account, (2) MTN Mobile Money, (3) Airtel Money, (4) Cash payments at our campus, and (5) Lenco online payments through our website. All payments receive official receipts.'
            ],
            [
                'q' => 'Is there a refund policy?',
                'a' => 'Yes. Full refunds are available if you cancel before the course starts. If you withdraw within the first week, you receive 75% refund. After the first week, refunds are prorated based on attendance. Registration fees are non-refundable.'
            ],
            [
                'q' => 'Are there any scholarships available?',
                'a' => 'We offer limited scholarships for outstanding students and those facing financial hardship. We also have special discounts for: (1) Early bird registrations (10% off), (2) Group enrollments (3+ students), and (3) Returning students (15% off second course).'
            ],
        ]
    ],
    'programs' => [
        'title' => 'Courses & Programs',
        'icon' => 'fa-graduation-cap',
        'questions' => [
            [
                'q' => 'What courses do you offer?',
                'a' => 'We offer TEVETA-certified programs in: Cybersecurity, Web Development, Digital Marketing, Microsoft Office Specialist, Computer Hardware & Networking, Data Science, Graphic Design, and more. Visit our courses page for the complete catalog.'
            ],
            [
                'q' => 'How long are the courses?',
                'a' => 'Course duration varies: Short courses (2-4 weeks), Certificate programs (3-6 months), and Diploma programs (12-18 months). Duration depends on whether you attend full-time, part-time, or evening classes.'
            ],
            [
                'q' => 'What is the difference between Certificate and Diploma?',
                'a' => 'Certificate programs are shorter (3-6 months) focused on specific skills, ideal for quick entry into the workforce. Diploma programs are comprehensive (12-18 months) with deeper theoretical and practical training, leading to advanced positions and higher salaries.'
            ],
            [
                'q' => 'Do you offer online classes?',
                'a' => 'Yes! We offer hybrid learning options. Theory classes can be attended online via our LMS, while practical sessions are held on campus. This flexible approach allows working professionals to balance studies with their careers.'
            ],
            [
                'q' => 'Are the certifications recognized?',
                'a' => 'Absolutely! All our programs are TEVETA-accredited, meaning they meet national standards and are recognized by employers across Zambia. Some programs also include international certifications like Microsoft, Cisco, or CompTIA.'
            ],
        ]
    ],
    'career' => [
        'title' => 'Career Support',
        'icon' => 'fa-briefcase',
        'questions' => [
            [
                'q' => 'Do you help with job placement?',
                'a' => 'Yes! Our Career Services department provides: (1) Resume/CV writing workshops, (2) Interview preparation, (3) Job placement assistance with partner companies, (4) Career counseling, and (5) Alumni networking events. Our 85% placement rate speaks to our commitment.'
            ],
            [
                'q' => 'What is the job placement rate?',
                'a' => '85% of our graduates secure employment within 6 months of graduation. This includes full-time positions, internships that convert to jobs, and freelance opportunities. Many graduates start their own successful businesses.'
            ],
            [
                'q' => 'Which companies hire your graduates?',
                'a' => 'Our graduates work at: MTN Zambia, Airtel, Zambia National Commercial Bank, Stanbic, BongoHive, government ministries, NGOs, and numerous SMEs. We maintain strong partnerships with employers who trust our training quality.'
            ],
            [
                'q' => 'Can I get internship opportunities?',
                'a' => 'Yes, we facilitate internships with our partner organizations. These provide real-world experience and often lead to job offers. Internship availability varies by course and student performance.'
            ],
        ]
    ],
    'campus' => [
        'title' => 'Campus & Facilities',
        'icon' => 'fa-building',
        'questions' => [
            [
                'q' => 'Where is the campus located?',
                'a' => 'Our main campus is located in Kalomo, Southern Province, Zambia. We are easily accessible from the town center. Contact us for detailed directions or to arrange a campus tour.'
            ],
            [
                'q' => 'What facilities do you have?',
                'a' => 'Our campus features: (1) Computer lab with 20 workstations and high-speed internet, (2) Projector-equipped classroom, (3) Study area with digital resources, and (4) Secure parking. We are currently expanding to accommodate growing demand and welcome investment partnerships.'
            ],
            [
                'q' => 'What are your operating hours?',
                'a' => 'Campus is open Monday-Friday 8:00 AM - 6:00 PM, and Saturdays 9:00 AM - 1:00 PM. Classes are scheduled in morning (8:30-12:30), afternoon (14:00-17:00), and evening (17:30-20:30) sessions to accommodate different schedules.'
            ],
            [
                'q' => 'Do you provide accommodation?',
                'a' => 'While we don\'t have on-campus accommodation, we can recommend safe, affordable lodging options near the campus for students coming from outside Kalomo. Many students also arrange shared housing.'
            ],
        ]
    ],
    'certification' => [
        'title' => 'Certification',
        'icon' => 'fa-certificate',
        'questions' => [
            [
                'q' => 'What certificate will I receive?',
                'a' => 'Upon successful completion, you receive a TEVETA-recognized certificate from Edutrack. This certificate includes your qualification details, our institution code (TVA/2064), and can be verified by employers through our online certificate verification system.'
            ],
            [
                'q' => 'How can employers verify my certificate?',
                'a' => 'Employers can verify certificates on our website at /certificate-verify.php using the certificate number and graduate name. This instant verification confirms the authenticity of your qualification.'
            ],
            [
                'q' => 'Do you offer international certifications?',
                'a' => 'Yes, selected programs include preparation for international certifications such as: Microsoft Office Specialist (MOS), Cisco CCNA, CompTIA A+/Security+, and IC3 Digital Literacy. Exam fees for these are separate from tuition.'
            ],
        ]
    ],
];

require_once __DIR__ . '/../src/templates/header.php';
?>

<!-- Page Header -->
<section class="bg-primary-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-question-circle mr-3"></i>Frequently Asked Questions
            </h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Find answers to common questions about admissions, courses, payments, and more.
                Can't find what you're looking for? Contact our admissions team.
            </p>
        </div>
    </div>
</section>

<!-- Quick Contact Banner -->
<section class="py-6 bg-yellow-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-center gap-6 text-center">
            <span class="text-gray-900 font-semibold">Still have questions?</span>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', SITE_PHONE) ?>" target="_blank" 
               class="inline-flex items-center px-6 py-2 bg-gray-900 text-white rounded-full font-medium hover:bg-gray-800 transition">
                <i class="fab fa-whatsapp mr-2"></i> Chat on WhatsApp
            </a>
            <a href="tel:<?= SITE_PHONE ?>" 
               class="inline-flex items-center px-6 py-2 bg-white text-gray-900 rounded-full font-medium hover:bg-gray-100 transition">
                <i class="fas fa-phone mr-2"></i> Call Us
            </a>
        </div>
    </div>
</section>

<!-- FAQ Categories -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Category Navigation -->
        <div class="flex flex-wrap justify-center gap-3 mb-12">
            <button onclick="showCategory('all')" class="category-btn active px-4 py-2 rounded-full bg-primary-600 text-white font-medium transition" data-category="all">
                All Questions
            </button>
            <?php foreach ($faqCategories as $key => $category): ?>
            <button onclick="showCategory('<?= $key ?>')" class="category-btn px-4 py-2 rounded-full bg-white text-gray-700 font-medium hover:bg-gray-100 transition border border-gray-200" data-category="<?= $key ?>">
                <i class="fas <?= $category['icon'] ?> mr-2"></i><?= $category['title'] ?>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- FAQ Accordion -->
        <div class="max-w-4xl mx-auto space-y-6" id="faq-container">
            <?php foreach ($faqCategories as $key => $category): ?>
            <div class="faq-category" data-category="<?= $key ?>">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas <?= $category['icon'] ?> text-primary-600"></i>
                    </div>
                    <?= $category['title'] ?>
                </h2>
                
                <div class="space-y-4">
                    <?php foreach ($category['questions'] as $index => $qa): ?>
                    <div class="faq-item bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <button onclick="toggleFaq(this)" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition">
                            <span class="font-semibold text-gray-900 pr-4"><?= htmlspecialchars($qa['q']) ?></span>
                            <i class="fas fa-chevron-down text-gray-400 transform transition-transform duration-300 flex-shrink-0"></i>
                        </button>
                        <div class="faq-answer hidden px-6 pb-4">
                            <div class="pt-2 border-t border-gray-100 text-gray-600 leading-relaxed">
                                <?= nl2br(htmlspecialchars($qa['a'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
    </div>
</section>

<!-- Still Need Help -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Still Need Help?</h2>
        <p class="text-gray-600 mb-8">Our admissions team is ready to answer your questions and guide you through the enrollment process.</p>
        
        <!-- Primary Contact (Admissions) -->
        <div class="bg-primary-50 border border-primary-200 rounded-xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-primary-900 mb-4 flex items-center">
                <i class="fas fa-star text-yellow-500 mr-2"></i> Admissions (Primary Contact)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="tel:<?= SITE_PHONE ?>" class="flex items-center p-4 bg-white rounded-lg hover:shadow transition">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-phone text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Call / WhatsApp</p>
                        <p class="font-semibold text-gray-900"><?= SITE_PHONE ?></p>
                    </div>
                </a>
                <a href="mailto:<?= SITE_EMAIL ?>" class="flex items-center p-4 bg-white rounded-lg hover:shadow transition">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-envelope text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-semibold text-gray-900 text-sm"><?= SITE_EMAIL ?></p>
                    </div>
                </a>
            </div>
            <p class="text-sm text-primary-700 mt-4">
                <i class="fas fa-clock mr-1"></i> Available Mon–Fri, 08:00–17:00 CAT. We typically reply within 4 business hours.
            </p>
        </div>

        <!-- Secondary Contact (Support) -->
        <div class="bg-gray-50 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Student Support</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="tel:<?= SITE_PHONE2 ?>" class="flex items-center p-4 bg-white rounded-lg hover:shadow transition">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-headset text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Support Line</p>
                        <p class="font-semibold text-gray-900"><?= SITE_PHONE2 ?></p>
                    </div>
                </a>
                <a href="mailto:<?= SITE_ALT_EMAIL ?>" class="flex items-center p-4 bg-white rounded-lg hover:shadow transition">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-envelope text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Support Email</p>
                        <p class="font-semibold text-gray-900 text-sm"><?= SITE_ALT_EMAIL ?></p>
                    </div>
                </a>
                <a href="/contact.php" class="flex items-center p-4 bg-white rounded-lg hover:shadow transition">
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-map-marker-alt text-orange-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Visit Campus</p>
                        <p class="font-semibold text-gray-900">Kalomo, Zambia</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<script>
function toggleFaq(button) {
    const answer = button.nextElementSibling;
    const icon = button.querySelector('.fa-chevron-down');
    const isHidden = answer.classList.contains('hidden');
    
    // Close all other answers
    document.querySelectorAll('.faq-answer').forEach(el => {
        el.classList.add('hidden');
    });
    document.querySelectorAll('.fa-chevron-down').forEach(el => {
        el.style.transform = 'rotate(0deg)';
    });
    
    // Toggle current
    if (isHidden) {
        answer.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    }
}

function showCategory(category) {
    // Update buttons
    document.querySelectorAll('.category-btn').forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.add('bg-primary-600', 'text-white');
            btn.classList.remove('bg-white', 'text-gray-700', 'hover:bg-gray-100');
        } else {
            btn.classList.remove('bg-primary-600', 'text-white');
            btn.classList.add('bg-white', 'text-gray-700', 'hover:bg-gray-100');
        }
    });
    
    // Show/hide categories
    document.querySelectorAll('.faq-category').forEach(cat => {
        if (category === 'all' || cat.dataset.category === category) {
            cat.style.display = 'block';
        } else {
            cat.style.display = 'none';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
