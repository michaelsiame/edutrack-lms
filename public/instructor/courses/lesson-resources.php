<?php
/**
 * Instructor - Manage Lesson Resources
 * Upload and manage downloadable resources for a specific lesson
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Lesson.php';
require_once '../../../src/classes/LessonResource.php';
require_once '../../../src/classes/Instructor.php';

$db = Database::getInstance();
$userId = currentUserId();

// Get lesson ID
$lessonId = $_GET['lesson_id'] ?? null;

if (!$lessonId) {
    flash('message', 'Lesson ID is required', 'error');
    redirect(url('instructor/courses.php'));
}

// Get lesson
$lesson = Lesson::find($lessonId);

if (!$lesson) {
    flash('message', 'Lesson not found', 'error');
    redirect(url('instructor/courses.php'));
}

// Get course
$courseId = $lesson->getCourseId();
$course = Course::find($courseId);

// Verify ownership
$instructor = Instructor::getOrCreate($userId);
$instructorId = $instructor->getId();

$courseInstructorId = $course->getInstructorId();
$isAssignedViaTable = $db->fetchOne("
    SELECT 1 FROM course_instructors
    WHERE course_id = ? AND instructor_id = ?
", [$courseId, $instructorId]);

$canEdit = hasRole('admin') ||
           ($courseInstructorId == $instructorId) ||
           ($courseInstructorId == $userId) ||
           ($isAssignedViaTable !== null);

if (!$canEdit) {
    flash('message', 'You do not have permission to edit this lesson', 'error');
    redirect(url('instructor/courses.php'));
}

// Get existing resources
$resources = LessonResource::getByLesson($lessonId);

$page_title = 'Manage Resources - ' . $lesson->getTitle();
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-600 mb-4">
                <a href="<?= url('instructor/courses.php') ?>" class="hover:text-primary-600">Courses</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <a href="<?= url('instructor/courses/modules.php?id=' . $courseId) ?>" class="hover:text-primary-600">
                    <?= htmlspecialchars($course->getTitle()) ?>
                </a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-900">Resources</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-download text-primary-600 mr-3"></i>Lesson Resources
            </h1>
            <p class="text-gray-600"><?= htmlspecialchars($lesson->getTitle()) ?></p>
        </div>

        <!-- Upload New Resource -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-cloud-upload-alt text-primary-600 mr-2"></i>Upload New Resource
            </h2>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="switchTab('file')" id="tab-file" class="tab-button active border-b-2 border-primary-600 py-4 px-1 text-sm font-medium text-primary-600">
                        <i class="fas fa-upload mr-2"></i>Upload File
                    </button>
                    <button onclick="switchTab('url')" id="tab-url" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        <i class="fas fa-link mr-2"></i>Link External File
                    </button>
                </nav>
            </div>

            <!-- Upload File Form -->
            <form id="uploadFileForm" class="tab-content" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">
                <input type="hidden" name="upload_type" value="file">

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File to Upload *</label>
                        <input type="file" name="file" id="file" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <p class="text-xs text-gray-500 mt-1">Max size: 50MB (100MB for videos). Supported: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP, MP4, MP3</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resource Title *</label>
                            <input type="text" name="title" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                   placeholder="e.g., Excel Practice Workbook">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resource Type *</label>
                            <select name="resource_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                <option value="PDF">PDF Document</option>
                                <option value="Document">Word Document</option>
                                <option value="Spreadsheet">Excel Spreadsheet</option>
                                <option value="Presentation">PowerPoint Presentation</option>
                                <option value="Video">Video File</option>
                                <option value="Audio">Audio File</option>
                                <option value="Archive">ZIP Archive</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                  placeholder="Briefly describe this resource and how students should use it"></textarea>
                    </div>

                    <div>
                        <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            <i class="fas fa-upload mr-2"></i>Upload Resource
                        </button>
                    </div>
                </div>
            </form>

            <!-- Link External File Form -->
            <form id="linkUrlForm" class="tab-content hidden">
                <?= csrfField() ?>
                <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">
                <input type="hidden" name="upload_type" value="url">

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File URL *</label>
                        <input type="url" name="file_url" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                               placeholder="https://drive.google.com/file/d/...">
                        <p class="text-xs text-gray-500 mt-1">
                            Link to Google Drive, Dropbox, OneDrive, or any publicly accessible file
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resource Title *</label>
                            <input type="text" name="title" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                   placeholder="e.g., Advanced Excel Guide">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resource Type *</label>
                            <select name="resource_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                <option value="PDF">PDF Document</option>
                                <option value="Document">Word Document</option>
                                <option value="Spreadsheet">Excel Spreadsheet</option>
                                <option value="Presentation">PowerPoint Presentation</option>
                                <option value="Video">Video File</option>
                                <option value="Audio">Audio File</option>
                                <option value="Archive">ZIP Archive</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                  placeholder="Briefly describe this resource"></textarea>
                    </div>

                    <div>
                        <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            <i class="fas fa-link mr-2"></i>Add External Link
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Existing Resources -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-list text-primary-600 mr-2"></i>Existing Resources
            </h2>

            <?php if (empty($resources)): ?>
            <div class="text-center py-12">
                <i class="fas fa-folder-open text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Resources Yet</h3>
                <p class="text-gray-600">Upload or link your first resource using the form above</p>
            </div>
            <?php else: ?>
            <div class="space-y-3" id="resourcesList">
                <?php foreach ($resources as $resource):
                    $resourceObj = LessonResource::find($resource['id']);
                    $iconClass = $resourceObj->getIconClass();
                    $fileSize = $resourceObj->getFormattedFileSize();
                ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition" data-resource-id="<?= $resource['id'] ?>">
                    <div class="flex items-center flex-1">
                        <i class="fas <?= $iconClass ?> text-3xl mr-4"></i>
                        <div>
                            <h4 class="font-semibold text-gray-900">
                                <?= htmlspecialchars($resource['title']) ?>
                            </h4>
                            <?php if ($resource['description']): ?>
                            <p class="text-sm text-gray-600 mt-1">
                                <?= htmlspecialchars($resource['description']) ?>
                            </p>
                            <?php endif; ?>
                            <div class="flex items-center mt-2 text-xs text-gray-500 space-x-4">
                                <span>
                                    <i class="fas fa-file mr-1"></i>
                                    <?= htmlspecialchars($resource['resource_type']) ?>
                                </span>
                                <?php if ($fileSize !== 'Unknown'): ?>
                                <span>
                                    <i class="fas fa-hdd mr-1"></i>
                                    <?= $fileSize ?>
                                </span>
                                <?php endif; ?>
                                <span>
                                    <i class="fas fa-download mr-1"></i>
                                    <?= number_format($resource['download_count']) ?> downloads
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="<?= $resourceObj->getDownloadUrl() ?>" target="_blank"
                           class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                        <button onclick="deleteResource(<?= $resource['id'] ?>)"
                                class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
// Tab switching
function switchTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'border-primary-600', 'text-primary-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    document.getElementById('tab-' + tab).classList.add('active', 'border-primary-600', 'text-primary-600');
    document.getElementById('tab-' + tab).classList.remove('border-transparent', 'text-gray-500');

    // Update forms
    document.querySelectorAll('.tab-content').forEach(form => {
        form.classList.add('hidden');
    });

    if (tab === 'file') {
        document.getElementById('uploadFileForm').classList.remove('hidden');
    } else {
        document.getElementById('linkUrlForm').classList.remove('hidden');
    }
}

// Handle file upload
document.getElementById('uploadFileForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';

    try {
        const response = await fetch('<?= url('api/lesson-resources.php') ?>', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Resource uploaded successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to upload resource'));
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('An error occurred while uploading the file');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Handle external URL linking
document.getElementById('linkUrlForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';

    try {
        const response = await fetch('<?= url('api/lesson-resources.php') ?>', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Resource linked successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to link resource'));
        }
    } catch (error) {
        console.error('Link error:', error);
        alert('An error occurred while linking the file');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Delete resource
async function deleteResource(resourceId) {
    if (!confirm('Are you sure you want to delete this resource? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('<?= url('api/lesson-resources.php') ?>?id=' + resourceId, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            // Remove from DOM
            const element = document.querySelector(`[data-resource-id="${resourceId}"]`);
            if (element) {
                element.remove();
            }

            // Check if empty
            const resourcesList = document.getElementById('resourcesList');
            if (resourcesList && resourcesList.children.length === 0) {
                window.location.reload();
            }

            alert('Resource deleted successfully');
        } else {
            alert('Error: ' + (data.message || 'Failed to delete resource'));
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting the resource');
    }
}
</script>

<?php require_once '../../../src/templates/footer.php'; ?>
