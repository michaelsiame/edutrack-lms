<?php
/**
 * Testimonials Page
 * Display all student success stories
 */

require_once __DIR__ . '/../src/bootstrap.php';

$db = Database::getInstance();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get total count
$totalTestimonials = $db->fetchColumn(
    "SELECT COUNT(*) FROM testimonials WHERE status = 'approved'"
);

// Get testimonials
$testimonials = $db->fetchAll(
    "SELECT * FROM testimonials 
     WHERE status = 'approved' 
     ORDER BY is_featured DESC, created_at DESC 
     LIMIT ? OFFSET ?",
    [$perPage, $offset]
);

$totalPages = ceil($totalTestimonials / $perPage);

// Get stats
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_graduates,
        AVG(rating) as avg_rating,
        COUNT(DISTINCT course_taken) as total_courses
     FROM testimonials WHERE status = 'approved'"
);

$page_title = "Student Success Stories - Edutrack Testimonials";

require_once __DIR__ . '/../src/templates/header.php';
?>

<!-- Page Header -->
<section class="bg-primary-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-heart mr-3"></i>Student Success Stories
            </h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Real stories from real graduates who transformed their careers through Edutrack
            </p>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-12 bg-yellow-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl font-bold text-gray-900"><?= number_format($stats['total_graduates']) ?>+</div>
                <div class="text-gray-800">Success Stories</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-900"><?= number_format($stats['avg_rating'], 1) ?></div>
                <div class="text-gray-800">Average Rating</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-900">85%</div>
                <div class="text-gray-800">Job Placement</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-900"><?= $stats['total_courses'] ?>+</div>
                <div class="text-gray-800">Courses</div>
            </div>
        </div>
    </div>
</section>

<!-- Impact CTA Section -->
<section class="py-16 bg-gray-900">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Join Our Growing Community of Graduates</h2>
            <p class="text-gray-300 text-lg mb-8 max-w-2xl mx-auto">
                Thousands of Zambians have built successful careers in technology through Edutrack.
                From complete beginners to industry professionals, our graduates are making a difference.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?= url('courses.php') ?>" class="px-6 py-3 bg-yellow-500 text-gray-900 rounded-lg font-semibold hover:bg-yellow-600 transition">
                    Start Your Journey
                </a>
                <a href="<?= url('contact.php') ?>" class="px-6 py-3 border-2 border-white text-white rounded-lg font-semibold hover:bg-white hover:text-gray-900 transition">
                    Contact Admissions
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Grid -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">What Our Graduates Say</h2>
        
        <?php if (!empty($testimonials)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($testimonials as $testimonial): ?>
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                <!-- Rating -->
                <div class="flex gap-1 mb-4">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star text-sm <?= $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                    <?php endfor; ?>
                </div>
                
                <!-- Quote -->
                <div class="mb-4 flex-1">
                    <i class="fas fa-quote-left text-2xl text-gray-200 mb-2"></i>
                    <p class="text-gray-600 italic line-clamp-6">
                        "<?= htmlspecialchars($testimonial['testimonial_text']) ?>"
                    </p>
                </div>
                
                <!-- Author -->
                <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                    <div class="w-12 h-12 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold">
                        <?php if ($testimonial['student_photo']): ?>
                        <img src="/uploads/testimonials/<?= htmlspecialchars($testimonial['student_photo']) ?>" 
                             alt="<?= htmlspecialchars($testimonial['student_name']) ?>"
                             class="w-full h-full object-cover rounded-full">
                        <?php else: ?>
                        <?= strtoupper(substr($testimonial['student_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($testimonial['student_name']) ?></div>
                        <div class="text-sm text-primary-600"><?= htmlspecialchars($testimonial['course_taken']) ?></div>
                        <?php if ($testimonial['current_job_title']): ?>
                        <div class="text-xs text-gray-500">
                            Now: <?= htmlspecialchars($testimonial['current_job_title']) ?>
                            <?php if ($testimonial['company']): ?>
                            at <?= htmlspecialchars($testimonial['company']) ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($testimonial['graduation_year']): ?>
                <div class="mt-3 text-right">
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                        <?= $testimonial['graduation_year'] ?> Graduate
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-12">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <a href="?page=<?= max(1, $page - 1) ?>" 
                   class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === 1 || $i === $totalPages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                    <a href="?page=<?= $i ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50 <?= $page == $i ? 'z-10 bg-primary-50 border-primary-500 text-primary-600' : 'text-gray-500' ?>">
                        <?= $i ?>
                    </a>
                    <?php elseif ($i === $page - 2 || $i === $page + 2): ?>
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        ...
                    </span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <a href="?page=<?= min($totalPages, $page + 1) ?>" 
                   class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900">No testimonials yet</h3>
            <p class="text-gray-600">Be the first to share your success story!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Submit Testimonial CTA -->
<section class="py-16 bg-primary-600 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Are You an Edutrack Graduate?</h2>
        <p class="text-xl text-primary-100 mb-8">
            Share your success story and inspire future students. Your journey could be the motivation someone needs.
        </p>
        <a href="/contact.php?subject=Testimonial Submission" class="inline-flex items-center px-8 py-4 bg-yellow-500 text-gray-900 rounded-lg font-bold hover:bg-yellow-600 transition">
            <i class="fas fa-pen mr-2"></i> Submit Your Story
        </a>
    </div>
</section>

<style>
.line-clamp-6 {
    display: -webkit-box;
    -webkit-line-clamp: 6;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
