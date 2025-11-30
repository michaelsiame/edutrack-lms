<?php
/**
 * Admin Edit Announcement
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Announcement.php';
require_once '../../../src/classes/Course.php';

$announcementId = $_GET['id'] ?? null;

if (!$announcementId) {
    redirect(url('admin/announcements/index.php'));
}

$announcement = Announcement::find($announcementId);

if (!$announcement) {
    flash('message', 'Announcement not found', 'error');
    redirect(url('admin/announcements/index.php'));
}

// Get all courses for dropdown
$db = Database::getInstance();
$courses = $db->fetchAll("SELECT id, title FROM courses WHERE status = 'published' ORDER BY title ASC");

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $announcementType = $_POST['announcement_type'] ?? 'info';
    $targetAudience = $_POST['target_audience'] ?? 'all';
    $courseId = $_POST['course_id'] ?? null;
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $expiresAt = trim($_POST['expires_at'] ?? '');

    // Validation
    if (empty($title)) {
        $errors['title'] = 'Title is required';
    }
    if (empty($content)) {
        $errors['content'] = 'Content is required';
    }

    if (empty($errors)) {
        try {
            $announcementData = [
                'title' => $title,
                'content' => $content,
                'announcement_type' => $announcementType,
                'target_audience' => $targetAudience,
                'course_id' => $courseId ?: null,
                'is_published' => $isPublished,
                'expires_at' => $expiresAt ?: null
            ];

            if ($announcement->update($announcementData)) {
                flash('message', 'Announcement updated successfully', 'success');
                redirect('admin/announcements/index.php');
            } else {
                flash('message', 'Failed to update announcement', 'error');
            }
        } catch (Exception $e) {
            flash('message', $e->getMessage(), 'error');
        }
    }
}

$page_title = 'Edit Announcement';
require_once '../../../src/templates/admin-header.php';
?>

<div class="flex h-screen bg-gray-100">
    <?php require_once '../../../src/templates/admin-sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm z-10">
            <div class="flex items-center justify-between px-8 py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Announcement</h1>
                    <p class="text-sm text-gray-600 mt-1">Update announcement details</p>
                </div>
                <a href="<?= url('admin/announcements/index.php') ?>" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Announcements
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-8">

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h3 class="text-red-800 font-semibold mb-2">Please fix the following errors:</h3>
                <ul class="list-disc list-inside text-red-700">
                    <?php foreach ($errors as $error): ?>
                        <li><?= sanitize($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <?= csrfField() ?>

                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Basic Information</h2>

                    <div class="space-y-4">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title"
                                   value="<?= sanitize($_POST['title'] ?? $announcement->getTitle()) ?>" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['title']) ? 'border-red-500' : 'border-gray-300' ?>">
                        </div>

                        <!-- Content -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Content <span class="text-red-500">*</span>
                            </label>
                            <textarea name="content" rows="6" required
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['content']) ? 'border-red-500' : 'border-gray-300' ?>"><?= sanitize($_POST['content'] ?? $announcement->getContent()) ?></textarea>
                            <p class="text-sm text-gray-500 mt-1">The main message of the announcement</p>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Settings</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Announcement Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                            <select name="announcement_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <?php
                                $currentType = $_POST['announcement_type'] ?? $announcement->getAnnouncementType();
                                ?>
                                <option value="info" <?= $currentType == 'info' ? 'selected' : '' ?>>Info</option>
                                <option value="success" <?= $currentType == 'success' ? 'selected' : '' ?>>Success</option>
                                <option value="warning" <?= $currentType == 'warning' ? 'selected' : '' ?>>Warning</option>
                                <option value="urgent" <?= $currentType == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                            <p class="text-sm text-gray-500 mt-1">Visual style of the announcement</p>
                        </div>

                        <!-- Target Audience -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                            <select name="target_audience" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <?php
                                $currentAudience = $_POST['target_audience'] ?? $announcement->getTargetAudience();
                                ?>
                                <option value="all" <?= $currentAudience == 'all' ? 'selected' : '' ?>>All Users</option>
                                <option value="students" <?= $currentAudience == 'students' ? 'selected' : '' ?>>Students Only</option>
                                <option value="instructors" <?= $currentAudience == 'instructors' ? 'selected' : '' ?>>Instructors Only</option>
                            </select>
                            <p class="text-sm text-gray-500 mt-1">Who should see this announcement</p>
                        </div>

                        <!-- Course (Optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Course (Optional)</label>
                            <select name="course_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">Global (All Courses)</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>"
                                            <?= ($_POST['course_id'] ?? $announcement->getCourseId()) == $course['id'] ? 'selected' : '' ?>>
                                        <?= sanitize($course['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-sm text-gray-500 mt-1">Leave empty for system-wide announcement</p>
                        </div>

                        <!-- Expiration Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiration Date (Optional)</label>
                            <?php
                            $expiresAt = $_POST['expires_at'] ?? $announcement->getExpiresAt();
                            if ($expiresAt && !isset($_POST['expires_at'])) {
                                $expiresAt = date('Y-m-d\TH:i', strtotime($expiresAt));
                            }
                            ?>
                            <input type="datetime-local" name="expires_at" value="<?= htmlspecialchars($expiresAt ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <p class="text-sm text-gray-500 mt-1">When announcement should stop showing</p>
                        </div>
                    </div>

                    <!-- Publish Status -->
                    <div class="mt-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_published" value="1" class="rounded text-primary-600"
                                   <?= (isset($_POST['is_published']) || $announcement->isPublished()) ? 'checked' : '' ?>>
                            <span class="ml-2 text-sm text-gray-700">Published</span>
                        </label>
                        <p class="text-sm text-gray-500 ml-6 mt-1">
                            Uncheck to save as draft
                        </p>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>
                            <span class="font-medium">Created by:</span> <?= sanitize($announcement->getCreatorName()) ?>
                        </div>
                        <div>
                            <span class="font-medium">Created:</span> <?= date('M d, Y H:i', strtotime($announcement->getCreatedAt())) ?>
                        </div>
                        <div>
                            <span class="font-medium">Last updated:</span> <?= timeAgo($announcement->getUpdatedAt()) ?>
                        </div>
                        <?php if ($announcement->getPublishedAt()): ?>
                        <div>
                            <span class="font-medium">Published:</span> <?= date('M d, Y H:i', strtotime($announcement->getPublishedAt())) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="<?= url('admin/announcements/index.php') ?>"
                       class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <i class="fas fa-save mr-2"></i>Update Announcement
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
