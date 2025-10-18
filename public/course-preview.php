<?php
/**
 * Course Preview Page
 * Preview free lessons before enrollment
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/classes/Course.php';

$db = Database::getInstance();

// Get lesson or course
$lessonId = $_GET['lesson'] ?? null;
$courseSlug = $_GET['course'] ?? null;

if ($lessonId) {
    // Preview specific lesson
    $lesson = $db->query("SELECT l.*, m.course_id, c.title as course_title, c.slug as course_slug
                          FROM lessons l
                          JOIN modules m ON l.module_id = m.id
                          JOIN courses c ON m.course_id = c.id
                          WHERE l.id = :id AND l.is_preview = 1",
                          ['id' => $lessonId])->fetch();
    
    if (!$lesson) {
        setFlashMessage('Preview not available for this lesson', 'error');
        redirect('courses.php');
    }
    
    $course = Course::find($lesson['course_id']);
    
} elseif ($courseSlug) {
    // Show first preview lesson
    $course = Course::findBySlug($courseSlug);
    
    if (!$course) {
        redirect('courses.php');
    }
    
    $lesson = $db->query("SELECT l.*, m.course_id
                          FROM lessons l
                          JOIN modules m ON l.module_id = m.id
                          WHERE m.course_id = :course_id AND l.is_preview = 1
                          ORDER BY m.order_index ASC, l.order_index ASC
                          LIMIT 1",
                          ['course_id' => $course->getId()])->fetch();
    
    if (!$lesson) {
        setFlashMessage('No preview available for this course', 'info');
        redirect('course.php?slug=' . $course->getSlug());
    }
} else {
    redirect('courses.php');
}

$page_title = 'Preview: ' . htmlspecialchars($lesson['title']) . ' - Edutrack';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-900">
    
    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="<?= $course->getUrl() ?>" 
                       class="text-gray-300 hover:text-white">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Course
                    </a>
                    <div class="border-l border-gray-600 pl-4">
                        <div class="text-sm text-gray-400">Previewing</div>
                        <div class="text-white font-semibold"><?= htmlspecialchars($lesson['title']) ?></div>
                    </div>
                </div>
                <a href="<?= $course->getEnrollUrl() ?>" 
                   class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition">
                    <i class="fas fa-shopping-cart mr-2"></i> Enroll Now
                </a>
            </div>
        </div>
    </div>
    
    <!-- Video Player -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Main Video Area -->
            <div class="lg:col-span-2">
                <div class="bg-black rounded-lg overflow-hidden mb-6" style="aspect-ratio: 16/9;">
                    <?php if ($lesson['lesson_type'] == 'video' && $lesson['video_url']): ?>
                        <?php if (strpos($lesson['video_url'], 'youtube.com') !== false || strpos($lesson['video_url'], 'youtu.be') !== false): ?>
                            <!-- YouTube Video -->
                            <iframe src="<?= getYoutubeEmbedUrl($lesson['video_url']) ?>" 
                                    class="w-full h-full"
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                            </iframe>
                        <?php elseif (strpos($lesson['video_url'], 'vimeo.com') !== false): ?>
                            <!-- Vimeo Video -->
                            <iframe src="<?= getVimeoEmbedUrl($lesson['video_url']) ?>" 
                                    class="w-full h-full"
                                    frameborder="0" 
                                    allow="autoplay; fullscreen; picture-in-picture" 
                                    allowfullscreen>
                            </iframe>
                        <?php else: ?>
                            <!-- Direct Video -->
                            <video controls class="w-full h-full">
                                <source src="<?= htmlspecialchars($lesson['video_url']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full bg-gray-800 text-white">
                            <div class="text-center">
                                <i class="fas fa-video text-6xl mb-4 text-gray-600"></i>
                                <p class="text-xl">Video preview not available</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Lesson Info -->
                <div class="bg-gray-800 rounded-lg p-6 text-white">
                    <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($lesson['title']) ?></h1>
                    
                    <?php if ($lesson['description']): ?>
                    <div class="text-gray-300 mb-6">
                        <?= nl2br(htmlspecialchars($lesson['description'])) ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Course Info -->
                    <div class="border-t border-gray-700 pt-4">
                        <div class="text-sm text-gray-400 mb-2">From the course:</div>
                        <a href="<?= $course->getUrl() ?>" 
                           class="text-xl font-bold text-white hover:text-primary-400">
                            <?= htmlspecialchars($course->getTitle()) ?>
                        </a>
                        <div class="mt-4 flex items-center space-x-6 text-sm text-gray-400">
                            <span><i class="fas fa-clock mr-2"></i><?= $course->getDuration() ?> hours</span>
                            <span><i class="fas fa-signal mr-2"></i><?= ucfirst($course->getLevel()) ?></span>
                            <span><i class="fas fa-user mr-2"></i><?= number_format($course->getTotalStudents()) ?> students</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                
                <!-- Preview Notice -->
                <div class="bg-secondary-500 text-gray-900 rounded-lg p-6 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-eye text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-bold text-lg mb-2">Preview Mode</h3>
                            <p class="text-sm mb-4">
                                You're viewing a free preview. Enroll to access all lessons and earn your certificate.
                            </p>
                            <a href="<?= $course->getEnrollUrl() ?>" 
                               class="block w-full bg-gray-900 text-white text-center py-2 rounded-lg font-semibold hover:bg-gray-800 transition">
                                Enroll Now - <?= $course->getFormattedPrice() ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Course Modules -->
                <div class="bg-gray-800 rounded-lg overflow-hidden">
                    <div class="bg-gray-700 px-6 py-4 border-b border-gray-600">
                        <h3 class="font-bold text-white">Course Content</h3>
                    </div>
                    
                    <div class="max-h-96 overflow-y-auto">
                        <?php
                        $modules = $course->getModules();
                        foreach ($modules as $module):
                            $moduleLessons = $db->query(
                                "SELECT * FROM lessons WHERE module_id = :module_id ORDER BY order_index ASC",
                                ['module_id' => $module['id']]
                            )->fetchAll();
                        ?>
                        <div class="border-b border-gray-700">
                            <div class="px-6 py-4 bg-gray-750">
                                <h4 class="font-semibold text-white text-sm">
                                    <?= htmlspecialchars($module['title']) ?>
                                </h4>
                            </div>
                            <ul class="divide-y divide-gray-700">
                                <?php foreach ($moduleLessons as $moduleLesson): ?>
                                <li class="px-6 py-3 <?= $moduleLesson['id'] == $lesson['id'] ? 'bg-gray-700' : '' ?>">
                                    <?php if ($moduleLesson['is_preview']): ?>
                                    <a href="<?= url('course-preview.php?lesson=' . $moduleLesson['id']) ?>" 
                                       class="flex items-center justify-between text-gray-300 hover:text-white">
                                        <div class="flex items-center">
                                            <i class="fas fa-play-circle mr-3 <?= $moduleLesson['id'] == $lesson['id'] ? 'text-primary-500' : '' ?>"></i>
                                            <span class="text-sm"><?= htmlspecialchars($moduleLesson['title']) ?></span>
                                        </div>
                                        <?php if ($moduleLesson['duration']): ?>
                                        <span class="text-xs text-gray-500"><?= $moduleLesson['duration'] ?>m</span>
                                        <?php endif; ?>
                                    </a>
                                    <?php else: ?>
                                    <div class="flex items-center justify-between text-gray-500">
                                        <div class="flex items-center">
                                            <i class="fas fa-lock mr-3"></i>
                                            <span class="text-sm"><?= htmlspecialchars($moduleLesson['title']) ?></span>
                                        </div>
                                        <?php if ($moduleLesson['duration']): ?>
                                        <span class="text-xs"><?= $moduleLesson['duration'] ?>m</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- What You Get -->
                <div class="bg-gray-800 rounded-lg p-6 mt-6 text-white">
                    <h3 class="font-bold mb-4">What you'll get:</h3>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <span><?= $course->getTotalLessons() ?> lessons</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <span><?= $course->getDuration() ?> hours of content</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <span>Lifetime access</span>
                        </li>
                        <?php if ($course->hasCertificate()): ?>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <span>Certificate of completion</span>
                        </li>
                        <?php endif; ?>
                        <?php if ($course->isTeveta()): ?>
                        <li class="flex items-start">
                            <i class="fas fa-check text-secondary-500 mt-1 mr-3"></i>
                            <span class="font-semibold">TEVETA Certification</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
            </div>
            
        </div>
    </div>
    
</div>

<?php require_once '../src/templates/footer.php'; ?>