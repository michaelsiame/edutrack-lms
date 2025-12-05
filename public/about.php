<?php
/**
 * About Us Page
 * Information about Edutrack computer training college
 */

require_once '../src/bootstrap.php';

$page_title = "About Us - Edutrack computer training college";

require_once '../src/templates/header.php';

// Fetch Team Members from Database
$team_members = [];
try {
    $stmt = $pdo->query("SELECT * FROM team_members ORDER BY display_order ASC");
    $team_members = $stmt->fetchAll();
} catch (PDOException $e) {
    // If table doesn't exist yet, we handle it gracefully or log error
    error_log("Team fetch error: " . $e->getMessage());
}
?>

<!-- Page Header -->
<section class="bg-gradient-to-br from-primary-600 via-blue-700 to-purple-800 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                About Edutrack
            </h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Empowering Zambians through quality computer training and TEVETA-certified education
            </p>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Mission -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-8 shadow-lg">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-bullseye text-white text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
                <p class="text-gray-700 leading-relaxed">
                    To provide accessible, high-quality computer training and technical education that empowers
                    individuals with industry-relevant skills, enabling them to compete effectively in the digital economy
                    and contribute to Zambia's technological advancement.
                </p>
            </div>

            <!-- Vision -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-8 shadow-lg">
                <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-eye text-white text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Vision</h2>
                <p class="text-gray-700 leading-relaxed">
                    To be Zambia's leading computer training institution, recognized for excellence in technical education,
                    innovation in teaching methodologies, and the success of our graduates in building rewarding careers
                    in the technology sector.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Leadership & Team Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Meet Our Team</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                The dedicated professionals driving excellence at Edutrack
            </p>
        </div>

        <?php if (!empty($team_members)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($team_members as $member): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 group">
                        <!-- Image Container -->
                        <div class="relative h-64 bg-gray-200 overflow-hidden">
                            <?php 
                                // Assuming images are stored in public/uploads/team/
                                $imagePath = 'uploads/team/' . $member['image_url'];
                                if ($member['image_url'] && file_exists($imagePath)): 
                            ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>" 
                                     alt="<?= htmlspecialchars($member['name']) ?>" 
                                     class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                            <?php else: ?>
                                <!-- Fallback if image not found -->
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-600 to-blue-800 text-white">
                                    <div class="text-center">
                                        <div class="text-4xl font-bold mb-2">
                                            <?= strtoupper(substr($member['name'], 0, 1)) ?>
                                        </div>
                                        <i class="fas fa-user text-2xl opacity-50"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Overlay Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-1">
                                <?= htmlspecialchars($member['name']) ?>
                            </h3>
                            <div class="text-primary-600 font-semibold text-sm uppercase tracking-wide mb-3">
                                <?= htmlspecialchars($member['position']) ?>
                            </div>
                            <div class="border-t border-gray-100 pt-3">
                                <p class="text-gray-600 text-sm italic leading-relaxed">
                                    <?= nl2br(htmlspecialchars($member['qualifications'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-100">
                <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Team details are being updated.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Core Values -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Our Core Values</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                The principles that guide everything we do
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Excellence -->
            <div class="text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-award text-blue-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Excellence</h3>
                <p class="text-gray-600">
                    We strive for excellence in every aspect of our training, ensuring the highest quality education for our students.
                </p>
            </div>

            <!-- Innovation -->
            <div class="text-center">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lightbulb text-purple-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Innovation</h3>
                <p class="text-gray-600">
                    We embrace new technologies and teaching methods to provide cutting-edge training relevant to industry needs.
                </p>
            </div>

            <!-- Integrity -->
            <div class="text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Integrity</h3>
                <p class="text-gray-600">
                    We maintain the highest standards of honesty, transparency, and ethical conduct in all our operations.
                </p>
            </div>

            <!-- Empowerment -->
            <div class="text-center">
                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-yellow-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Empowerment</h3>
                <p class="text-gray-600">
                    We empower students to reach their full potential through practical skills and career guidance.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- TEVETA Accreditation -->
<section class="py-16 bg-gradient-to-br from-yellow-50 to-orange-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-xl p-8 md:p-12">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-certificate text-yellow-600 text-5xl"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">TEVETA Accredited Institution</h2>
                    <p class="text-lg text-gray-700 mb-4">
                        Edutrack computer training college is proud to be registered and accredited by the Technical Education,
                        Vocational and Entrepreneurship Training Authority (TEVETA). This accreditation ensures that our training
                        programs meet national standards and that our graduates receive government-recognized certificates.
                    </p>
                    <div class="flex flex-wrap gap-4 mt-6">
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span>Government Recognized</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span>Industry Standards</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span>Certified Instructors</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Edutrack?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                What sets us apart from other training institutions
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Experienced Instructors -->
            <div class="bg-gray-50 rounded-lg p-6 hover:shadow-lg transition duration-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chalkboard-teacher text-primary-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Expert Instructors</h3>
                        <p class="text-gray-600">
                            Learn from industry professionals with real-world experience in their fields.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Practical Training -->
            <div class="bg-gray-50 rounded-lg p-6 hover:shadow-lg transition duration-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-laptop-code text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Hands-On Learning</h3>
                        <p class="text-gray-600">
                            Practice what you learn with practical exercises and real projects.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Modern Facilities -->
            <div class="bg-gray-50 rounded-lg p-6 hover:shadow-lg transition duration-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-building text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Modern Facilities</h3>
                        <p class="text-gray-600">
                            Access well-equipped computer labs with the latest software and hardware.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Flexible Learning -->
            <div class="bg-gray-50 rounded-lg p-6 hover:shadow-lg transition duration-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Flexible Schedules</h3>
                        <p class="text-gray-600">
                            Choose from weekday, evening, or weekend classes to fit your schedule.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Job Placement -->
            <div class="bg-gray-50 rounded-lg p-6 hover:shadow-lg transition duration-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-briefcase text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Career Support</h3>
                        <p class="text-gray-600">
                            Receive job placement assistance and career guidance after completion.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Affordable Fees -->
            <div class="bg-gray-50 rounded-lg p-6 hover:shadow-lg transition duration-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tags text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Affordable Fees</h3>
                        <p class="text-gray-600">
                            Quality education at competitive prices with flexible payment options.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-gradient-to-br from-primary-600 via-blue-700 to-purple-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">
            Ready to Start Your Learning Journey?
        </h2>
        <p class="text-xl text-primary-100 mb-8 max-w-3xl mx-auto">
            Join thousands of students who have transformed their careers with Edutrack computer training college
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="courses.php"
               class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-gray-50 transition duration-200 shadow-lg">
                <i class="fas fa-book-open mr-2"></i>
                Browse Courses
            </a>
            <a href="contact.php"
               class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-base font-medium rounded-md text-white hover:bg-white hover:text-primary-600 transition duration-200">
                <i class="fas fa-envelope mr-2"></i>
                Contact Us
            </a>
        </div>
    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>