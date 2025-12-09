<?php
/**
 * Course Enrollment Card Component
 * Sticky card for course enrollment
 * Include this file in course.php
 */
?>

<div class="bg-white rounded-lg shadow-xl overflow-hidden sticky top-4">
    <!-- Course Thumbnail -->
    <img src="<?= $course->getThumbnailUrl() ?>" 
         alt="<?= htmlspecialchars($course->getTitle()) ?>"
         class="w-full h-48 object-cover">
    
    <div class="p-6">
        
        <!-- Price -->
        <div class="mb-6">
            <div class="text-3xl font-bold text-gray-900 mb-1">
                <?= $course->getFormattedPrice() ?>
            </div>
            <?php if (!$course->isFree()): ?>
            <div class="text-sm text-gray-600">One-time payment</div>
            <?php endif; ?>
        </div>
        
        <!-- Enroll Button -->
        <?php if ($isEnrolled): ?>
        <a href="<?= url('learn.php?course=' . $course->getSlug()) ?>" 
           class="block w-full bg-green-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:bg-green-700 transition mb-3">
            <i class="fas fa-play-circle mr-2"></i> Continue Learning
        </a>
        <?php else: ?>
        <a href="<?= $course->getEnrollUrl() ?>" 
           class="block w-full bg-primary-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:bg-primary-700 transition mb-3">
            <i class="fas fa-shopping-cart mr-2"></i> Enroll Now
        </a>
        <?php endif; ?>
        
        <!-- Preview Button -->
        <?php if (!$isEnrolled): ?>
        <a href="<?= url('course-preview.php?course=' . $course->getSlug()) ?>" 
           class="block w-full border-2 border-gray-300 text-gray-700 text-center py-3 px-6 rounded-lg font-semibold hover:border-gray-400 transition mb-6">
            <i class="fas fa-eye mr-2"></i> Preview This Course
        </a>
        <?php endif; ?>
        
        <!-- Course Includes -->
        <div class="border-t border-gray-200 pt-6">
            <h3 class="font-bold text-gray-900 mb-4">This course includes:</h3>
            <ul class="space-y-3">
                <li class="flex items-start">
                    <i class="fas fa-video text-primary-600 mt-1 mr-3"></i>
                    <span class="text-gray-700"><?= $course->getDuration() ?> hours on-demand video</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-file-alt text-primary-600 mt-1 mr-3"></i>
                    <span class="text-gray-700"><?= $totalLessons ?> lessons</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-infinity text-primary-600 mt-1 mr-3"></i>
                    <span class="text-gray-700">Full lifetime access</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-mobile-alt text-primary-600 mt-1 mr-3"></i>
                    <span class="text-gray-700">Access on mobile and desktop</span>
                </li>
                <?php if ($course->hasCertificate()): ?>
                <li class="flex items-start">
                    <i class="fas fa-certificate text-primary-600 mt-1 mr-3"></i>
                    <span class="text-gray-700">Certificate of completion</span>
                </li>
                <?php endif; ?>
                <?php if ($course->isTeveta()): ?>
                <li class="flex items-start">
                    <i class="fas fa-award text-secondary-600 mt-1 mr-3"></i>
                    <span class="text-gray-700 font-semibold">TEVETA REGISTERED</span>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Share Course -->
        <div class="border-t border-gray-200 pt-6 mt-6">
            <h3 class="font-bold text-gray-900 mb-4">Share this course:</h3>
            <div class="flex space-x-2">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($course->getUrl()) ?>" 
                   target="_blank"
                   class="flex-1 bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($course->getUrl()) ?>&text=<?= urlencode($course->getTitle()) ?>" 
                   target="_blank"
                   class="flex-1 bg-sky-500 text-white text-center py-2 rounded hover:bg-sky-600">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($course->getUrl()) ?>" 
                   target="_blank"
                   class="flex-1 bg-blue-700 text-white text-center py-2 rounded hover:bg-blue-800">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <button onclick="copyToClipboard('<?= $course->getUrl() ?>')" 
                        class="flex-1 bg-gray-600 text-white text-center py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-link"></i>
                </button>
            </div>
        </div>
        
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    }, function() {
        alert('Failed to copy link');
    });
}
</script>