<?php
/**
 * Course Discussions & Q&A
 */

require_once '../src/middleware/authenticate.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';

$courseSlug = $_GET['course'] ?? null;

if (!$courseSlug) {
    redirect('my-courses.php');
}

$course = Course::findBySlug($courseSlug);

if (!$course) {
    redirect('my-courses.php');
}

// Check if user is enrolled
$isEnrolled = Enrollment::isEnrolled(currentUserId(), $course->getId());

if (!$isEnrolled && !hasRole(['instructor', 'admin'])) {
    flash('message', 'You must be enrolled in this course to access discussions', 'error');
    redirect('course.php?slug=' . $courseSlug);
}

// Handle new discussion post
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    validateCSRF();
    
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $lessonId = $_POST['lesson_id'] ?? null;
    
    if (empty($title)) {
        flash('message', 'Please enter a title', 'error');
    } elseif (empty($content)) {
        flash('message', 'Please enter your question or comment', 'error');
    } else {
        $sql = "INSERT INTO discussions (course_id, lesson_id, user_id, title, content) 
                VALUES (?, ?, ?, ?, ?)";
        $db->query($sql, [$course->getId(), $lessonId, currentUserId(), $title, $content]);
        
        flash('message', 'Your discussion has been posted!', 'success');
        redirect($_SERVER['REQUEST_URI']);
    }
}

// Handle reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'reply') {
    validateCSRF();
    
    $parentId = $_POST['parent_id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    
    if (empty($content)) {
        flash('message', 'Please enter your reply', 'error');
    } elseif ($parentId) {
        $sql = "INSERT INTO discussions (course_id, user_id, parent_id, content) 
                VALUES (?, ?, ?, ?)";
        $db->query($sql, [$course->getId(), currentUserId(), $parentId, $content]);
        
        flash('message', 'Reply posted!', 'success');
        redirect($_SERVER['REQUEST_URI'] . '#discussion-' . $parentId);
    }
}

// Get lessons for filter
$lessons = $db->fetchAll("
    SELECT l.id, l.title, m.title as module_title
    FROM lessons l
    JOIN course_modules m ON l.module_id = m.id
    WHERE m.course_id = ?
    ORDER BY m.order_index, l.order_index
", [$course->getId()]);

// Filter
$lessonFilter = $_GET['lesson'] ?? '';

// Get discussions
$where = ['d.course_id = ? AND d.parent_id IS NULL'];
$params = [$course->getId()];

if ($lessonFilter) {
    $where[] = 'd.lesson_id = ?';
    $params[] = $lessonFilter;
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

$discussions = $db->fetchAll("
    SELECT d.*, 
           u.first_name, u.last_name, u.email, u.role,
           l.title as lesson_title,
           (SELECT COUNT(*) FROM discussions WHERE parent_id = d.id) as reply_count
    FROM discussions d
    JOIN users u ON d.user_id = u.id
    LEFT JOIN lessons l ON d.lesson_id = l.id
    {$whereClause}
    ORDER BY d.is_pinned DESC, d.created_at DESC
", $params);

$page_title = 'Discussions - ' . $course->getTitle();
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-comments text-primary-600 mr-2"></i>
                        Course Discussions
                    </h1>
                    <p class="text-gray-600 mt-1"><?= sanitize($course->getTitle()) ?></p>
                </div>
                <div class="flex gap-3">
                    <a href="<?= url('learn.php?course=' . $courseSlug) ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Course
                    </a>
                    <button onclick="document.getElementById('newDiscussionModal').classList.remove('hidden')" 
                            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <i class="fas fa-plus mr-2"></i>New Discussion
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Filter -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" class="flex items-center space-x-4">
                <input type="hidden" name="course" value="<?= sanitize($courseSlug) ?>">
                <label class="text-sm font-medium text-gray-700">Filter by lesson:</label>
                <select name="lesson" onchange="this.form.submit()" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    <option value="">All Lessons</option>
                    <?php 
                    $currentModule = '';
                    foreach ($lessons as $lesson): 
                        if ($currentModule != $lesson['module_title']) {
                            if ($currentModule) echo '</optgroup>';
                            echo '<optgroup label="' . sanitize($lesson['module_title']) . '">';
                            $currentModule = $lesson['module_title'];
                        }
                    ?>
                        <option value="<?= $lesson['id'] ?>" <?= $lessonFilter == $lesson['id'] ? 'selected' : '' ?>>
                            <?= sanitize($lesson['title']) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if ($currentModule) echo '</optgroup>'; ?>
                </select>
                <?php if ($lessonFilter): ?>
                    <a href="?course=<?= sanitize($courseSlug) ?>" class="text-sm text-gray-600 hover:text-gray-900">
                        <i class="fas fa-times mr-1"></i>Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Discussions List -->
        <?php if (empty($discussions)): ?>
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-comments text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No discussions yet</h3>
            <p class="text-gray-600 mb-6">Be the first to start a discussion!</p>
            <button onclick="document.getElementById('newDiscussionModal').classList.remove('hidden')" 
                    class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Start a Discussion
            </button>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($discussions as $discussion): ?>
            <div id="discussion-<?= $discussion['id'] ?>" class="bg-white rounded-lg shadow hover:shadow-md transition">
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <img src="<?= getGravatar($discussion['email']) ?>" class="h-12 w-12 rounded-full">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-lg">
                                        <?php if ($discussion['is_pinned']): ?>
                                            <i class="fas fa-thumbtack text-primary-600 mr-2"></i>
                                        <?php endif; ?>
                                        <?= sanitize($discussion['title']) ?>
                                    </h3>
                                    <div class="flex items-center space-x-3 text-sm text-gray-600 mt-1">
                                        <span class="font-medium"><?= sanitize($discussion['first_name'] . ' ' . $discussion['last_name']) ?></span>
                                        <?php if ($discussion['role'] == 'instructor' || $discussion['role'] == 'admin'): ?>
                                            <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded text-xs font-semibold">
                                                <?= ucfirst($discussion['role']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span>•</span>
                                        <span><?= timeAgo($discussion['created_at']) ?></span>
                                        <?php if ($discussion['lesson_title']): ?>
                                            <span>•</span>
                                            <span class="text-primary-600"><?= sanitize($discussion['lesson_title']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($discussion['is_answered']): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                    <i class="fas fa-check-circle mr-1"></i>Answered
                                </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-gray-700 mt-3 whitespace-pre-wrap"><?= nl2br(sanitize($discussion['content'])) ?></p>
                            <div class="flex items-center space-x-4 mt-4">
                                <button onclick="toggleReplies(<?= $discussion['id'] ?>)" class="text-sm text-primary-600 hover:text-primary-700">
                                    <i class="fas fa-reply mr-1"></i>
                                    <?= $discussion['reply_count'] ?> <?= pluralize($discussion['reply_count'], 'Reply', 'Replies') ?>
                                </button>
                                <button onclick="showReplyForm(<?= $discussion['id'] ?>)" class="text-sm text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-comment mr-1"></i>Reply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reply Form -->
                <div id="reply-form-<?= $discussion['id'] ?>" class="hidden border-t border-gray-200 p-6 bg-gray-50">
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="reply">
                        <input type="hidden" name="parent_id" value="<?= $discussion['id'] ?>">
                        <textarea name="content" rows="3" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                  placeholder="Write your reply..."></textarea>
                        <div class="flex justify-end space-x-2 mt-3">
                            <button type="button" onclick="hideReplyForm(<?= $discussion['id'] ?>)" 
                                    class="px-4 py-2 text-gray-600 hover:text-gray-900">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Post Reply
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Replies -->
                <div id="replies-<?= $discussion['id'] ?>" class="hidden border-t border-gray-200">
                    <?php
                    $replies = $db->fetchAll("
                        SELECT d.*, u.first_name, u.last_name, u.email, u.role
                        FROM discussions d
                        JOIN users u ON d.user_id = u.id
                        WHERE d.parent_id = ?
                        ORDER BY d.created_at ASC
                    ", [$discussion['id']]);
                    
                    if (!empty($replies)):
                        foreach ($replies as $reply):
                    ?>
                    <div class="p-6 bg-gray-50 border-b border-gray-200 last:border-b-0">
                        <div class="flex items-start space-x-3">
                            <img src="<?= getGravatar($reply['email']) ?>" class="h-10 w-10 rounded-full">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 text-sm">
                                    <span class="font-medium text-gray-900"><?= sanitize($reply['first_name'] . ' ' . $reply['last_name']) ?></span>
                                    <?php if ($reply['role'] == 'instructor' || $reply['role'] == 'admin'): ?>
                                        <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded text-xs font-semibold">
                                            <?= ucfirst($reply['role']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-gray-500">•</span>
                                    <span class="text-gray-500"><?= timeAgo($reply['created_at']) ?></span>
                                </div>
                                <p class="text-gray-700 mt-2 whitespace-pre-wrap"><?= nl2br(sanitize($reply['content'])) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- New Discussion Modal -->
<div id="newDiscussionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Start a New Discussion</h3>
            <button onclick="document.getElementById('newDiscussionModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="What's your question or topic?">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Related Lesson (Optional)</label>
                    <select name="lesson_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">General Discussion</option>
                        <?php 
                        $currentModule = '';
                        foreach ($lessons as $lesson): 
                            if ($currentModule != $lesson['module_title']) {
                                if ($currentModule) echo '</optgroup>';
                                echo '<optgroup label="' . sanitize($lesson['module_title']) . '">';
                                $currentModule = $lesson['module_title'];
                            }
                        ?>
                            <option value="<?= $lesson['id'] ?>"><?= sanitize($lesson['title']) ?></option>
                        <?php endforeach; ?>
                        <?php if ($currentModule) echo '</optgroup>'; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Question or Comment</label>
                    <textarea name="content" rows="6" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                              placeholder="Be specific and provide details..."></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="document.getElementById('newDiscussionModal').classList.add('hidden')" 
                        class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Post Discussion
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleReplies(id) {
    const repliesDiv = document.getElementById('replies-' + id);
    repliesDiv.classList.toggle('hidden');
}

function showReplyForm(id) {
    document.getElementById('reply-form-' + id).classList.remove('hidden');
}

function hideReplyForm(id) {
    document.getElementById('reply-form-' + id).classList.add('hidden');
}
</script>

<?php require_once '../src/templates/footer.php'; ?>