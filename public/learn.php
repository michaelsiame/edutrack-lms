<?php
/**
 * Course Learning Interface
 * Main page for taking courses
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/middleware/authenticate.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Lesson.php';
require_once '../src/classes/Module.php';
require_once '../src/classes/Progress.php';
require_once '../src/classes/Enrollment.php';

$courseSlug = $_GET['course'] ?? null;
$lessonId = $_GET['lesson'] ?? null;

if (!$courseSlug) {
    redirect('my-courses.php');
}

// Get course
$course = Course::findBySlug($courseSlug);
if (!$course) {
    setFlashMessage('Course not found', 'error');
    redirect('my-courses.php');
}

// Check enrollment
$userId = $_SESSION['user_id'];
if (!Enrollment::isEnrolled($userId, $course->getId())) {
    setFlashMessage('You must enroll in this course first', 'error');
    redirect('course.php?slug=' . $courseSlug);
}

// Get progress tracker
$progress = new Progress();

// Get current lesson
if ($lessonId) {
    $currentLesson = Lesson::find($lessonId);
    if (!$currentLesson || $currentLesson->getCourseId() != $course->getId()) {
        $currentLesson = $progress->getCurrentLesson($userId, $course->getId());
    }
} else {
    $currentLesson = $progress->getCurrentLesson($userId, $course->getId());
}

if (!$currentLesson) {
    setFlashMessage('No lessons available in this course', 'info');
    redirect('course.php?slug=' . $courseSlug);
}

// Get all modules and lessons
$modules = Module::getByCourse($course->getId());
$courseProgress = $progress->getCourseProgress($userId, $course->getId());

// Get next and previous lessons
$nextLesson = $currentLesson->getNext();
$prevLesson = $currentLesson->getPrevious();

// Update last accessed
$progress->updateLastAccessed($userId, $course->getId());

// Check if current lesson is completed
$isCompleted = $currentLesson->isCompletedByUser($userId);
$lessonProgress = $currentLesson->getUserProgress($userId);

$page_title = htmlspecialchars($currentLesson->getTitle()) . ' - ' . htmlspecialchars($course->getTitle());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/learning.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900">

<!-- Learning Header -->
<header class="bg-gray-800 border-b border-gray-700 sticky top-0 z-50">
    <div class="flex items-center justify-between px-4 py-3">
        
        <!-- Left: Course Info -->
        <div class="flex items-center space-x-4 flex-1 min-w-0">
            <a href="<?= url('my-courses.php') ?>" 
               class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="text-white font-semibold truncate text-sm md:text-base">
                    <?= htmlspecialchars($course->getTitle()) ?>
                </h1>
                <div class="flex items-center space-x-3 text-xs text-gray-400 mt-1">
                    <span><?= $courseProgress['completed_lessons'] ?? 0 ?> / <?= $courseProgress['total_lessons'] ?? 0 ?> lessons</span>
                    <span><?= round($courseProgress['progress_percentage'] ?? 0) ?>% complete</span>
                </div>
            </div>
        </div>
        
        <!-- Right: Actions -->
        <div class="flex items-center space-x-3">
            <!-- Progress Bar -->
            <div class="hidden md:block w-32">
                <div class="bg-gray-700 rounded-full h-2">
                    <div class="bg-primary-500 h-2 rounded-full transition-all" 
                         style="width: <?= round($courseProgress['progress_percentage'] ?? 0) ?>%"></div>
                </div>
            </div>
            
            <!-- Notes Toggle -->
            <button onclick="toggleNotes()" 
                    class="text-gray-400 hover:text-white transition hidden md:block"
                    title="Toggle Notes">
                <i class="fas fa-sticky-note"></i>
            </button>
            
            <!-- Sidebar Toggle -->
            <button onclick="toggleSidebar()" 
                    class="text-gray-400 hover:text-white transition lg:hidden">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
    </div>
</header>

<div class="flex h-[calc(100vh-60px)]">
    
    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto">
        
        <!-- Video/Content Area -->
        <div class="bg-black" style="aspect-ratio: 16/9; max-height: 70vh;">
            <?php if ($currentLesson->isVideo()): ?>
                <!-- Video Player -->
                <div id="video-container" class="w-full h-full">
                    <?= $currentLesson->getVideoEmbed() ?>
                </div>
            <?php elseif ($currentLesson->isArticle()): ?>
                <!-- Article Content -->
                <div class="w-full h-full flex items-center justify-center bg-gray-800">
                    <div class="text-center text-white p-8">
                        <i class="fas fa-file-alt text-6xl mb-4"></i>
                        <p class="text-xl">Article Content</p>
                        <p class="text-gray-400 text-sm mt-2">Scroll down to read</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center bg-gray-800">
                    <div class="text-center text-white p-8">
                        <i class="fas fa-book-open text-6xl mb-4"></i>
                        <p class="text-xl">Lesson Content</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Lesson Controls -->
        <div class="bg-gray-800 border-t border-gray-700 p-4">
            <div class="flex items-center justify-between">
                
                <!-- Previous Lesson -->
                <?php if ($prevLesson): ?>
                <a href="<?= url('learn.php?course=' . $courseSlug . '&lesson=' . $prevLesson->getId()) ?>" 
                   class="flex items-center text-gray-300 hover:text-white transition">
                    <i class="fas fa-chevron-left mr-2"></i>
                    <span class="hidden md:inline">Previous Lesson</span>
                </a>
                <?php else: ?>
                <div></div>
                <?php endif; ?>
                
                <!-- Mark Complete Button -->
                <div class="flex items-center space-x-3">
                    <?php if ($isCompleted): ?>
                    <button class="bg-green-600 text-white px-6 py-2 rounded-lg flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Completed
                    </button>
                    <?php else: ?>
                    <button onclick="markComplete(<?= $currentLesson->getId() ?>)" 
                            id="mark-complete-btn"
                            class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition flex items-center">
                        <i class="fas fa-check mr-2"></i>
                        Mark as Complete
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- Next Lesson -->
                <?php if ($nextLesson): ?>
                <a href="<?= url('learn.php?course=' . $courseSlug . '&lesson=' . $nextLesson->getId()) ?>" 
                   class="flex items-center text-gray-300 hover:text-white transition">
                    <span class="hidden md:inline">Next Lesson</span>
                    <i class="fas fa-chevron-right ml-2"></i>
                </a>
                <?php else: ?>
                <div></div>
                <?php endif; ?>
                
            </div>
        </div>
        
        <!-- Lesson Content -->
        <div class="bg-gray-900 text-white p-6">
            
            <!-- Lesson Header -->
            <div class="max-w-4xl mx-auto mb-8">
                <h2 class="text-3xl font-bold mb-3"><?= htmlspecialchars($currentLesson->getTitle()) ?></h2>
                
                <?php if ($currentLesson->getDescription()): ?>
                <p class="text-gray-400 text-lg"><?= nl2br(htmlspecialchars($currentLesson->getDescription())) ?></p>
                <?php endif; ?>
                
                <div class="flex items-center space-x-6 mt-4 text-sm text-gray-500">
                    <?php if ($currentLesson->getDuration()): ?>
                    <span><i class="fas fa-clock mr-2"></i><?= $currentLesson->getDuration() ?> minutes</span>
                    <?php endif; ?>
                    <span><i class="fas fa-book-open mr-2"></i><?= $currentLesson->getModuleTitle() ?></span>
                </div>
            </div>
            
            <!-- Article Content -->
            <?php if ($currentLesson->isArticle() && $currentLesson->getContent()): ?>
            <div class="max-w-4xl mx-auto prose prose-invert prose-lg mb-8">
                <?= $currentLesson->getContent() ?>
            </div>
            <?php endif; ?>
            
            <!-- Attachments -->
            <?php 
            $attachments = $currentLesson->getAttachments();
            if (!empty($attachments)): 
            ?>
            <div class="max-w-4xl mx-auto mb-8">
                <h3 class="text-xl font-bold mb-4">Resources & Downloads</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($attachments as $attachment): ?>
                    <a href="<?= htmlspecialchars($attachment['url']) ?>" 
                       target="_blank"
                       class="flex items-center p-4 bg-gray-800 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-file-download text-primary-500 text-2xl mr-4"></i>
                        <div>
                            <div class="font-semibold"><?= htmlspecialchars($attachment['name']) ?></div>
                            <?php if (isset($attachment['size'])): ?>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($attachment['size']) ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Notes Section -->
            <div id="notes-section" class="max-w-4xl mx-auto mb-8 hidden">
                <h3 class="text-xl font-bold mb-4">My Notes</h3>
                <div class="bg-gray-800 rounded-lg p-4">
                    <textarea id="lesson-notes" 
                              class="w-full bg-gray-700 text-white rounded-lg p-4 min-h-32 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                              placeholder="Take notes for this lesson..."></textarea>
                    <button onclick="saveNotes()" 
                            class="mt-3 bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition">
                        <i class="fas fa-save mr-2"></i> Save Notes
                    </button>
                </div>
            </div>
            
        </div>
        
    </main>
    
    <!-- Sidebar (Course Curriculum) -->
    <aside id="curriculum-sidebar" 
           class="w-80 bg-gray-800 border-l border-gray-700 overflow-y-auto hidden lg:block">
        
        <div class="p-4 border-b border-gray-700">
            <h3 class="text-white font-bold text-lg">Course Content</h3>
            <p class="text-gray-400 text-sm mt-1">
                <?= count($modules) ?> modules • <?= $courseProgress['total_lessons'] ?? 0 ?> lessons
            </p>
        </div>
        
        <div class="divide-y divide-gray-700">
            <?php foreach ($modules as $moduleData): 
                $module = new Module($moduleData['id']);
                $moduleLessons = $module->getLessons();
                $moduleProgress = $progress->getModuleProgress($userId, $module->getId());
            ?>
            <div class="module-section">
                <!-- Module Header -->
                <button onclick="toggleModuleSection(<?= $module->getId() ?>)" 
                        class="w-full flex items-center justify-between p-4 hover:bg-gray-750 transition text-left">
                    <div class="flex-1">
                        <h4 class="text-white font-semibold text-sm"><?= htmlspecialchars($module->getTitle()) ?></h4>
                        <div class="text-xs text-gray-400 mt-1">
                            <?= $moduleProgress['completed'] ?> / <?= $moduleProgress['total'] ?> • 
                            <?= $moduleProgress['percentage'] ?>% complete
                        </div>
                    </div>
                    <i class="fas fa-chevron-down module-toggle-icon text-gray-400"></i>
                </button>
                
                <!-- Module Lessons -->
                <div id="module-<?= $module->getId() ?>" class="module-lessons">
                    <?php foreach ($moduleLessons as $lesson): 
                        $lessonCompleted = Lesson::find($lesson['id'])->isCompletedByUser($userId);
                        $isCurrent = $lesson['id'] == $currentLesson->getId();
                    ?>
                    <a href="<?= url('learn.php?course=' . $courseSlug . '&lesson=' . $lesson['id']) ?>" 
                       class="flex items-center p-4 pl-8 hover:bg-gray-750 transition <?= $isCurrent ? 'bg-gray-750 border-l-4 border-primary-500' : '' ?>">
                        <div class="flex-shrink-0 mr-3">
                            <?php if ($lessonCompleted): ?>
                            <i class="fas fa-check-circle text-green-500"></i>
                            <?php elseif ($isCurrent): ?>
                            <i class="fas fa-play-circle text-primary-500"></i>
                            <?php else: ?>
                            <i class="far fa-circle text-gray-500"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-white text-sm <?= $isCurrent ? 'font-semibold' : '' ?>">
                                <?= htmlspecialchars($lesson['title']) ?>
                            </div>
                            <?php if ($lesson['duration']): ?>
                            <div class="text-xs text-gray-500"><?= $lesson['duration'] ?> min</div>
                            <?php endif; ?>
                        </div>
                        <?php if ($lesson['lesson_type'] == 'video'): ?>
                        <i class="fas fa-play-circle text-gray-500 ml-2"></i>
                        <?php elseif ($lesson['lesson_type'] == 'article'): ?>
                        <i class="fas fa-file-alt text-gray-500 ml-2"></i>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
    </aside>
    
</div>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" 
     class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"
     onclick="toggleSidebar()"></div>

<script src="<?= asset('js/learning.js') ?>"></script>
<script>
const courseId = <?= $course->getId() ?>;
const lessonId = <?= $currentLesson->getId() ?>;
const userId = <?= $userId ?>;

// Mark lesson as complete
function markComplete(lessonId) {
    fetch('<?= url('api/progress.php') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'mark_complete',
            lesson_id: lessonId,
            course_id: courseId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('mark-complete-btn').outerHTML = 
                '<button class="bg-green-600 text-white px-6 py-2 rounded-lg flex items-center">' +
                '<i class="fas fa-check-circle mr-2"></i>Completed</button>';
            
            // Reload page to update progress
            setTimeout(() => location.reload(), 1000);
        }
    });
}

// Toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.getElementById('curriculum-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    sidebar.classList.toggle('hidden');
    sidebar.classList.toggle('fixed');
    sidebar.classList.toggle('inset-y-0');
    sidebar.classList.toggle('right-0');
    sidebar.classList.toggle('z-50');
    overlay.classList.toggle('hidden');
}

// Toggle module sections
function toggleModuleSection(moduleId) {
    const section = document.getElementById('module-' + moduleId);
    const icon = event.currentTarget.querySelector('.module-toggle-icon');
    
    section.classList.toggle('hidden');
    icon.classList.toggle('fa-chevron-down');
    icon.classList.toggle('fa-chevron-up');
}

// Toggle notes
function toggleNotes() {
    document.getElementById('notes-section').classList.toggle('hidden');
}

// Save notes
function saveNotes() {
    const notes = document.getElementById('lesson-notes').value;
    
    fetch('<?= url('api/notes.php') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            lesson_id: lessonId,
            course_id: courseId,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notes saved successfully!');
        }
    });
}

// Load existing notes
fetch('<?= url('api/notes.php?lesson_id=' . $currentLesson->getId()) ?>')
    .then(response => response.json())
    .then(data => {
        if (data.notes) {
            document.getElementById('lesson-notes').value = data.notes;
        }
    });
</script>

</body>
</html>