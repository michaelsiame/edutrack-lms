<?php
/**
 * Instructor - Bulk Content Upload
 * Upload multiple lessons, resources, and content at once
 */

require_once '../../../src/bootstrap.php';
require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Lesson.php';
require_once '../../../src/classes/Instructor.php';

$db = Database::getInstance();
$userId = currentUserId();

// Get instructor ID
$instructor = Instructor::getOrCreate($userId);
$instructorId = $instructor->getId();

// Get course ID
$courseId = $_GET['id'] ?? null;
if (!$courseId) {
    flash('message', 'Course ID is required', 'error');
    redirect(url('instructor/courses.php'));
}

$course = Course::find($courseId);
if (!$course) {
    flash('message', 'Course not found', 'error');
    redirect(url('instructor/courses.php'));
}

// Verify ownership
$canEdit = hasRole('admin') || $course->getInstructorId() == $instructorId || $course->getInstructorId() == $userId;
if (!$canEdit) {
    flash('message', 'Permission denied', 'error');
    redirect(url('instructor/courses.php'));
}

$errors = [];
$uploadResults = [];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    $uploadType = $_POST['upload_type'] ?? '';
    
    switch ($uploadType) {
        case 'zip':
            handleZipUpload($_FILES['zip_file'] ?? null, $courseId, $db, $errors, $uploadResults);
            break;
        case 'csv':
            handleCsvUpload($_FILES['csv_file'] ?? null, $courseId, $db, $errors, $uploadResults);
            break;
        case 'videos':
            handleVideoUrls($_POST['video_data'] ?? '', $courseId, $db, $errors, $uploadResults);
            break;
        case 'resources':
            handleResourceUpload($_FILES['resources'] ?? [], $courseId, $db, $errors, $uploadResults);
            break;
    }
    
    if (empty($errors) && !empty($uploadResults)) {
        flash('message', 'Content uploaded successfully! Created ' . count($uploadResults) . ' items.', 'success');
        redirect(url('instructor/courses/modules.php?id=' . $courseId));
    }
}

function handleZipUpload($file, $courseId, $db, &$errors, &$results) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please select a valid ZIP file';
        return;
    }
    
    $zip = new ZipArchive();
    $tmpPath = $file['tmp_name'];
    
    if ($zip->open($tmpPath) !== TRUE) {
        $errors[] = 'Failed to open ZIP file';
        return;
    }
    
    $extractPath = sys_get_temp_dir() . '/bulk_upload_' . uniqid() . '/';
    mkdir($extractPath, 0755, true);
    $zip->extractTo($extractPath);
    $zip->close();
    
    // Process extracted files
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($extractPath),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $currentModule = null;
    foreach ($items as $item) {
        if ($item->isDir() || $item->getFilename()[0] === '.') continue;
        
        $relativePath = str_replace($extractPath, '', $item->getPathname());
        $parts = explode('/', $relativePath);
        
        if (count($parts) >= 2) {
            $moduleName = $parts[0];
            $fileName = $parts[1];
            
            // Create or get module
            $module = $db->fetchOne("SELECT id FROM modules WHERE course_id = ? AND title = ?", 
                [$courseId, $moduleName]);
            
            if (!$module) {
                $db->query("INSERT INTO modules (course_id, title, display_order) VALUES (?, ?, ?)",
                    [$courseId, $moduleName, getNextModuleOrder($courseId, $db)]);
                $moduleId = $db->lastInsertId();
            } else {
                $moduleId = $module['id'];
            }
            
            // Create lesson based on file type
            $lessonData = processFileToLesson($item->getPathname(), $fileName, $moduleId, $db);
            if ($lessonData) {
                $results[] = $lessonData;
            }
        }
    }
    
    // Cleanup
    rrmdir($extractPath);
}

function handleCsvUpload($file, $courseId, $db, &$errors, &$results) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please select a valid CSV file';
        return;
    }
    
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        $errors[] = 'Failed to read CSV file';
        return;
    }
    
    $headers = fgetcsv($handle);
    $expectedHeaders = ['module', 'lesson_title', 'lesson_type', 'video_url', 'duration', 'description'];
    
    if (array_diff($expectedHeaders, array_map('strtolower', $headers))) {
        $errors[] = 'CSV headers do not match expected format. Required: ' . implode(', ', $expectedHeaders);
        fclose($handle);
        return;
    }
    
    $rowNum = 1;
    while (($row = fgetcsv($handle)) !== FALSE) {
        $rowNum++;
        $data = array_combine($headers, $row);
        
        if (empty($data['module']) || empty($data['lesson_title'])) {
            continue;
        }
        
        // Get or create module
        $module = $db->fetchOne("SELECT id FROM modules WHERE course_id = ? AND title = ?", 
            [$courseId, $data['module']]);
        
        if (!$module) {
            $db->query("INSERT INTO modules (course_id, title, display_order) VALUES (?, ?, ?)",
                [$courseId, $data['module'], getNextModuleOrder($courseId, $db)]);
            $moduleId = $db->lastInsertId();
        } else {
            $moduleId = $module['id'];
        }
        
        // Create lesson
        $lessonData = [
            'module_id' => $moduleId,
            'title' => $data['lesson_title'],
            'slug' => slugify($data['lesson_title']),
            'lesson_type' => $data['lesson_type'] ?? 'video',
            'video_url' => $data['video_url'] ?? '',
            'duration' => $data['duration'] ? (int)$data['duration'] : null,
            'description' => $data['description'] ?? '',
            'display_order' => getNextLessonOrder($moduleId, $db),
            'is_preview' => 0
        ];
        
        $lessonId = Lesson::create($lessonData);
        if ($lessonId) {
            $results[] = ['type' => 'lesson', 'title' => $data['lesson_title'], 'module' => $data['module']];
        }
    }
    
    fclose($handle);
}

function handleVideoUrls($videoData, $courseId, $db, &$errors, &$results) {
    $lines = explode("\n", trim($videoData));
    $moduleId = $_POST['target_module'] ?? null;
    
    if (!$moduleId) {
        $errors[] = 'Please select a target module';
        return;
    }
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Format: Title | URL | Duration
        $parts = array_map('trim', explode('|', $line));
        
        $title = $parts[0] ?? 'Untitled Lesson';
        $url = $parts[1] ?? $line;
        $duration = isset($parts[2]) ? (int)$parts[2] : null;
        
        $lessonData = [
            'module_id' => $moduleId,
            'title' => $title,
            'slug' => slugify($title),
            'lesson_type' => 'video',
            'video_url' => $url,
            'duration' => $duration,
            'display_order' => getNextLessonOrder($moduleId, $db),
            'is_preview' => 0
        ];
        
        $lessonId = Lesson::create($lessonData);
        if ($lessonId) {
            $results[] = ['type' => 'lesson', 'title' => $title];
        }
    }
}

function handleResourceUpload($files, $courseId, $db, &$errors, &$results) {
    // Implementation for multiple file uploads
    if (empty($files['name'][0])) {
        $errors[] = 'Please select files to upload';
        return;
    }
    
    $moduleId = $_POST['target_module'] ?? null;
    if (!$moduleId) {
        $errors[] = 'Please select a target module';
        return;
    }
    
    require_once '../../../src/classes/FileUpload.php';
    require_once '../../../src/classes/LessonResource.php';
    
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        
        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
        
        $uploader = new FileUpload($file, 'courses/resources');
        $uploader->setAllowedTypes(['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'txt']);
        $uploader->setMaxSize(50 * 1024 * 1024); // 50MB
        
        $result = $uploader->upload();
        if ($result) {
            // Create a resource lesson
            $title = pathinfo($file['name'], PATHINFO_FILENAME);
            $lessonData = [
                'module_id' => $moduleId,
                'title' => $title,
                'slug' => slugify($title),
                'lesson_type' => 'text',
                'description' => 'Download resource: ' . $file['name'],
                'display_order' => getNextLessonOrder($moduleId, $db),
                'is_preview' => 0
            ];
            
            $lessonId = Lesson::create($lessonData);
            if ($lessonId) {
                // Add resource record
                LessonResource::create([
                    'lesson_id' => $lessonId,
                    'title' => $file['name'],
                    'file_path' => $result['filepath'],
                    'file_type' => $result['type'],
                    'file_size' => $result['size']
                ]);
                
                $results[] = ['type' => 'resource', 'title' => $file['name']];
            }
        }
    }
}

function getNextModuleOrder($courseId, $db) {
    $max = $db->fetchColumn("SELECT MAX(display_order) FROM modules WHERE course_id = ?", [$courseId]);
    return ($max !== null) ? $max + 1 : 0;
}

function getNextLessonOrder($moduleId, $db) {
    $max = $db->fetchColumn("SELECT MAX(display_order) FROM lessons WHERE module_id = ?", [$moduleId]);
    return ($max !== null) ? $max + 1 : 0;
}

function processFileToLesson($filepath, $filename, $moduleId, $db) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $title = pathinfo($filename, PATHINFO_FILENAME);
    
    $lessonData = [
        'module_id' => $moduleId,
        'title' => $title,
        'slug' => slugify($title),
        'display_order' => getNextLessonOrder($moduleId, $db),
        'is_preview' => 0
    ];
    
    switch ($extension) {
        case 'mp4':
        case 'webm':
        case 'mov':
            $lessonData['lesson_type'] = 'video';
            // Move to public directory and create URL
            break;
            
        case 'pdf':
        case 'doc':
        case 'docx':
        case 'ppt':
        case 'pptx':
            $lessonData['lesson_type'] = 'text';
            $lessonData['description'] = 'Resource: ' . $filename;
            break;
            
        case 'md':
        case 'txt':
            $lessonData['lesson_type'] = 'text';
            $lessonData['content'] = file_get_contents($filepath);
            break;
            
        default:
            return null;
    }
    
    $lessonId = Lesson::create($lessonData);
    return $lessonId ? ['type' => 'lesson', 'title' => $title] : null;
}

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object))
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        rmdir($dir);
    }
}

// Get modules for dropdown
$modules = Module::getByCourse($courseId);

$page_title = 'Bulk Upload - ' . $course->getTitle();
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <a href="<?= url('instructor/courses.php') ?>" class="hover:text-primary-600">Courses</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="<?= url('instructor/course-edit.php?id=' . $courseId) ?>" class="hover:text-primary-600"><?= htmlspecialchars($course->getTitle()) ?></a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 font-medium">Bulk Upload</span>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-cloud-upload-alt text-primary-600 mr-3"></i>Bulk Content Upload
                </h1>
                <p class="text-gray-500 mt-1">Upload multiple lessons and resources at once</p>
            </div>
            <a href="<?= url('instructor/courses/modules.php?id=' . $courseId) ?>" 
               class="px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Content
            </a>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <h3 class="text-red-800 font-semibold mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>Upload Errors
            </h3>
            <ul class="list-disc list-inside text-red-700 text-sm">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Upload Options Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- ZIP Upload -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-file-archive text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Upload ZIP Archive</h3>
                        <p class="text-sm text-gray-500">Organized folder structure</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Upload a ZIP file with folders as modules and files as lessons.
                    Supports videos, PDFs, and text files.
                </p>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="upload_type" value="zip">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-primary-500 transition cursor-pointer" 
                         onclick="document.getElementById('zip_input').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">Click to select ZIP file</p>
                        <input type="file" id="zip_input" name="zip_file" accept=".zip" class="hidden" required 
                               onchange="this.form.submit()">
                    </div>
                </form>
            </div>

            <!-- CSV Upload -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-file-csv text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Import from CSV</h3>
                        <p class="text-sm text-gray-500">Spreadsheet import</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Import lesson structure from a CSV file. Columns: module, lesson_title, lesson_type, video_url, duration, description
                </p>
                <a href="<?= url('assets/templates/bulk_upload_template.csv') ?>" 
                   class="text-sm text-primary-600 hover:text-primary-700 mb-4 inline-block">
                    <i class="fas fa-download mr-1"></i>Download Template
                </a>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="upload_type" value="csv">
                    <input type="file" name="csv_file" accept=".csv" class="w-full text-sm" required 
                           onchange="this.form.submit()">
                </form>
            </div>

            <!-- Video URLs -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fab fa-youtube text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Bulk Video URLs</h3>
                        <p class="text-sm text-gray-500">Paste multiple URLs</p>
                    </div>
                </div>
                <form method="POST" class="space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="upload_type" value="videos">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Module</label>
                        <select name="target_module" required class="w-full px-3 py-2 border border-gray-200 rounded-lg">
                            <option value="">Select module...</option>
                            <?php foreach ($modules as $module): ?>
                            <option value="<?= $module['id'] ?>"><?= htmlspecialchars($module['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Video URLs (one per line)
                            <span class="text-gray-400 font-normal">Format: Title | URL | Duration(min)</span>
                        </label>
                        <textarea name="video_data" rows="5" required
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm"
                                  placeholder="Lesson 1: Introduction | https://youtube.com/watch?v=... | 10
Lesson 2: Getting Started | https://youtube.com/watch?v=... | 15"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-plus mr-2"></i>Add Videos
                    </button>
                </form>
            </div>

            <!-- Resource Files -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-folder-open text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Upload Resources</h3>
                        <p class="text-sm text-gray-500">PDFs, Docs, PPTs</p>
                    </div>
                </div>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="upload_type" value="resources">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Module</label>
                        <select name="target_module" required class="w-full px-3 py-2 border border-gray-200 rounded-lg">
                            <option value="">Select module...</option>
                            <?php foreach ($modules as $module): ?>
                            <option value="<?= $module['id'] ?>"><?= htmlspecialchars($module['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center">
                        <i class="fas fa-upload text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">Select multiple files</p>
                        <input type="file" name="resources[]" multiple 
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.txt"
                               class="w-full text-sm mt-2">
                    </div>
                    
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-upload mr-2"></i>Upload Resources
                    </button>
                </form>
            </div>

        </div>

        <!-- Instructions -->
        <div class="mt-8 bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-6 text-white">
            <h3 class="font-bold text-lg mb-4">
                <i class="fas fa-lightbulb text-yellow-400 mr-2"></i>Pro Tips
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="space-y-2">
                    <p class="flex items-start">
                        <i class="fas fa-check text-green-400 mt-1 mr-2"></i>
                        <span>Name your files clearly - they'll become lesson titles</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-check text-green-400 mt-1 mr-2"></i>
                        <span>Use folders in ZIP to auto-create modules</span>
                    </p>
                </div>
                <div class="space-y-2">
                    <p class="flex items-start">
                        <i class="fas fa-check text-green-400 mt-1 mr-2"></i>
                        <span>Maximum file size: 100MB for ZIP, 50MB for individual files</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-check text-green-400 mt-1 mr-2"></i>
                        <span>Supported formats: MP4, PDF, DOC, PPT, TXT, MD</span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../../../src/templates/instructor-footer.php'; ?>
