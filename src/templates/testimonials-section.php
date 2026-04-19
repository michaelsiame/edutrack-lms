<?php
/**
 * Testimonials Section Template
 * Reusable testimonials display component
 * 
 * Usage: require_once '../src/templates/testimonials-section.php';
 * 
 * This template fetches approved featured testimonials from the database
 * and displays them in an attractive carousel/grid format.
 */

// Fetch testimonials if not already provided
if (!isset($testimonials)) {
    $db = Database::getInstance();
    $testimonials = $db->fetchAll(
        "SELECT * FROM testimonials 
         WHERE status = 'approved' 
         ORDER BY is_featured DESC, rating DESC, created_at DESC 
         LIMIT 6"
    );
}

if (empty($testimonials)) {
    return; // Don't display section if no testimonials
}
?>

<!-- Testimonials Section -->
<section class="py-20 bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-500 text-gray-900 mb-4">
                <i class="fas fa-heart mr-2"></i> Student Success Stories
            </span>
            <h2 class="text-3xl md:text-4xl font-bold mb-4">What Our Graduates Say</h2>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                Join thousands of successful graduates who transformed their careers with Edutrack
            </p>
        </div>
        
        <!-- Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-16">
            <div class="text-center">
                <div class="text-4xl md:text-5xl font-bold text-yellow-400 mb-2">5,000+</div>
                <div class="text-gray-400">Graduates</div>
            </div>
            <div class="text-center">
                <div class="text-4xl md:text-5xl font-bold text-yellow-400 mb-2">85%</div>
                <div class="text-gray-400">Job Placement Rate</div>
            </div>
            <div class="text-center">
                <div class="text-4xl md:text-5xl font-bold text-yellow-400 mb-2">4.8</div>
                <div class="text-gray-400">Average Rating</div>
            </div>
            <div class="text-center">
                <div class="text-4xl md:text-5xl font-bold text-yellow-400 mb-2">50+</div>
                <div class="text-gray-400">Partner Companies</div>
            </div>
        </div>
        
        <!-- Testimonials Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="testimonials-grid">
            <?php foreach ($testimonials as $index => $testimonial): 
                $delay = ($index % 3) * 100;
            ?>
            <div class="testimonial-card group bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 hover:bg-opacity-20 transition-all duration-300 transform hover:-translate-y-2"
                 style="animation-delay: <?= $delay ?>ms">
                <!-- Quote Icon -->
                <div class="mb-4">
                    <i class="fas fa-quote-left text-4xl text-yellow-400 opacity-50"></i>
                </div>
                
                <!-- Rating -->
                <div class="flex gap-1 mb-4">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star text-sm <?= $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-600' ?>"></i>
                    <?php endfor; ?>
                </div>
                
                <!-- Testimonial Text -->
                <p class="text-gray-200 mb-6 line-clamp-4 leading-relaxed">
                    "<?= htmlspecialchars($testimonial['testimonial_text']) ?>"
                </p>
                
                <!-- Author Info -->
                <div class="flex items-center gap-4 pt-4 border-t border-white border-opacity-20">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-gray-900 font-bold text-xl">
                        <?php if ($testimonial['student_photo'] && file_exists(__DIR__ . '/../../public/uploads/testimonials/' . $testimonial['student_photo'])): ?>
                        <img src="/uploads/testimonials/<?= htmlspecialchars($testimonial['student_photo']) ?>" 
                             alt="<?= htmlspecialchars($testimonial['student_name']) ?>"
                             class="w-full h-full object-cover rounded-full">
                        <?php else: ?>
                        <?= strtoupper(substr($testimonial['student_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="font-semibold text-white"><?= htmlspecialchars($testimonial['student_name']) ?></div>
                        <div class="text-sm text-yellow-400"><?= htmlspecialchars($testimonial['course_taken']) ?></div>
                        <?php if ($testimonial['current_job_title']): ?>
                        <div class="text-xs text-gray-400 mt-1">
                            Now: <?= htmlspecialchars($testimonial['current_job_title']) ?>
                            <?php if ($testimonial['company']): ?>
                            at <?= htmlspecialchars($testimonial['company']) ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Consent Note -->
                <div class="mt-3 pt-3 border-t border-white border-opacity-10 text-center">
                    <span class="text-xs text-gray-400">
                        <i class="fas fa-check-circle mr-1"></i>Shared with permission
                    </span>
                </div>
                
                <!-- Year Badge -->
                <?php if ($testimonial['graduation_year']): ?>
                <div class="absolute top-4 right-4">
                    <span class="text-xs bg-white bg-opacity-20 text-white px-2 py-1 rounded-full">
                        <?= $testimonial['graduation_year'] ?> Graduate
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- View All Link -->
        <div class="text-center mt-12">
            <a href="/testimonials.php" class="inline-flex items-center text-yellow-400 font-semibold hover:text-yellow-300 transition">
                View All Success Stories <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mt-12">
            <a href="/courses.php" class="inline-flex items-center justify-center px-8 py-4 bg-yellow-500 text-gray-900 rounded-lg font-semibold hover:bg-yellow-600 transition">
                <i class="fas fa-graduation-cap mr-2"></i> Start Your Journey
            </a>
            <a href="/contact.php" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white rounded-lg font-semibold hover:bg-white hover:text-gray-900 transition">
                <i class="fas fa-phone mr-2"></i> Talk to Admissions
            </a>
        </div>
    </div>
</section>

<style>
.testimonial-card {
    position: relative;
    animation: fadeInUp 0.6s ease-out forwards;
    opacity: 0;
    transform: translateY(30px);
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
