<?php
/**
 * Course Review & Rating
 * Students can review courses they've completed
 */

require_once '../src/middleware/authenticate.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';

$courseId = $_GET['course_id'] ?? null;

if (!$courseId) {
    redirect('my-courses.php');
}

$course = Course::find($courseId);

if (!$course) {
    redirect('my-courses.php');
}

// Check if user is enrolled
$enrollment = Enrollment::getUserEnrollment(currentUserId(), $courseId);

if (!$enrollment) {
    flash('message', 'You must be enrolled in this course to leave a review', 'error');
    redirect('course.php?slug=' . $course->getSlug());
}

// Check if already reviewed
$existingReview = $db->fetchOne(
    "SELECT * FROM reviews WHERE user_id = ? AND course_id = ?",
    [currentUserId(), $courseId]
);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    
    $rating = (int)($_POST['rating'] ?? 0);
    $reviewText = trim($_POST['review_text'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        flash('message', 'Please select a rating between 1 and 5 stars', 'error');
    } elseif (empty($reviewText)) {
        flash('message', 'Please write a review', 'error');
    } else {
        if ($existingReview) {
            // Update existing review
            $sql = "UPDATE reviews SET rating = ?, review_text = ?, updated_at = NOW() WHERE id = ?";
            $db->query($sql, [$rating, $reviewText, $existingReview['id']]);
            $message = 'Your review has been updated!';
        } else {
            // Create new review
            $sql = "INSERT INTO reviews (user_id, course_id, rating, review_text, status) 
                    VALUES (?, ?, ?, ?, 'approved')";
            $db->query($sql, [currentUserId(), $courseId, $rating, $reviewText]);
            $message = 'Thank you for your review!';
        }
        
        // Update course rating
        $avgRating = $db->fetchColumn(
            "SELECT AVG(rating) FROM reviews WHERE course_id = ? AND status = 'approved'",
            [$courseId]
        );
        $ratingCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM reviews WHERE course_id = ? AND status = 'approved'",
            [$courseId]
        );
        
        $db->query(
            "UPDATE courses SET rating_avg = ?, rating_count = ? WHERE id = ?",
            [$avgRating, $ratingCount, $courseId]
        );
        
        flash('message', $message, 'success');
        redirect('course.php?slug=' . $course->getSlug());
    }
}

$page_title = 'Review Course - ' . $course->getTitle();
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4">
        
        <!-- Course Info -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="flex items-center p-6">
                <img src="<?= courseThumbnail($course->getThumbnail()) ?>" 
                     alt="<?= sanitize($course->getTitle()) ?>"
                     class="h-20 w-28 object-cover rounded">
                <div class="ml-4 flex-1">
                    <h2 class="text-xl font-bold text-gray-900"><?= sanitize($course->getTitle()) ?></h2>
                    <p class="text-sm text-gray-600 mt-1">by <?= sanitize($course->getInstructorName()) ?></p>
                </div>
            </div>
        </div>
        
        <!-- Review Form -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <?= $existingReview ? 'Update Your Review' : 'Write a Review' ?>
            </h1>
            
            <?php if ($existingReview): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-blue-800 text-sm">
                    <i class="fas fa-info-circle mr-2"></i>
                    You've already reviewed this course. You can update your review below.
                </p>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <?= csrfField() ?>
                
                <!-- Rating -->
                <div>
                    <label class="block text-lg font-medium text-gray-900 mb-3">
                        How would you rate this course?
                    </label>
                    <div class="flex items-center space-x-2" x-data="{ rating: <?= $existingReview['rating'] ?? 0 ?>, hover: 0 }">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" 
                                @click="rating = <?= $i ?>"
                                @mouseenter="hover = <?= $i ?>"
                                @mouseleave="hover = 0"
                                class="focus:outline-none">
                            <i class="fas fa-star text-4xl transition-colors"
                               :class="(hover >= <?= $i ?> || (hover === 0 && rating >= <?= $i ?>)) ? 'text-yellow-400' : 'text-gray-300'"></i>
                        </button>
                        <?php endfor; ?>
                        <input type="hidden" name="rating" :value="rating" required>
                        <span class="ml-4 text-gray-600" x-text="rating > 0 ? rating + ' stars' : 'Select rating'"></span>
                    </div>
                </div>
                
                <!-- Review Text -->
                <div>
                    <label class="block text-lg font-medium text-gray-900 mb-3">
                        Share your experience with this course
                    </label>
                    <textarea name="review_text" rows="6" required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="What did you like about this course? What could be improved?"><?= sanitize($existingReview['review_text'] ?? '') ?></textarea>
                    <p class="text-sm text-gray-500 mt-2">
                        Your review will help other students decide if this course is right for them.
                    </p>
                </div>
                
                <!-- Tips -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Tips for writing a great review:</h3>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Be specific about what you liked or didn't like</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Mention the instructor's teaching style</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Share what you learned and how it helped you</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Be honest and constructive</li>
                    </ul>
                </div>
                
                <!-- Submit -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="<?= url('course.php?slug=' . $course->getSlug()) ?>" 
                       class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <i class="fas fa-paper-plane mr-2"></i>
                        <?= $existingReview ? 'Update Review' : 'Submit Review' ?>
                    </button>
                </div>
                
            </form>
        </div>
        
        <!-- Previous Review (if updating) -->
        <?php if ($existingReview): ?>
        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <h3 class="font-bold text-gray-900 mb-4">Your Current Review</h3>
            <div class="flex items-center mb-3">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= $existingReview['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                <?php endfor; ?>
                <span class="ml-2 text-sm text-gray-600">
                    <?= timeAgo($existingReview['created_at']) ?>
                </span>
            </div>
            <p class="text-gray-700"><?= nl2br(sanitize($existingReview['review_text'])) ?></p>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<?php require_once '../src/templates/footer.php'; ?>