<?php
/**
 * Admin Issue Certificate
 * Manually issue certificates to students
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Certificate.php';
require_once '../../../src/classes/User.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Enrollment.php';

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    
    $userId = $_POST['user_id'] ?? null;
    $courseId = $_POST['course_id'] ?? null;
    
    if (!$userId) {
        $errors['user_id'] = 'Please select a student';
    }
    
    if (!$courseId) {
        $errors['course_id'] = 'Please select a course';
    }
    
    if (empty($errors)) {
        // Check if certificate already exists
        $existing = Certificate::getByUserAndCourse($userId, $courseId);
        
        if ($existing) {
            $errors['general'] = 'Certificate already exists for this student and course';
        } else {
            // Check if student is enrolled
            if (!Enrollment::isEnrolled($userId, $courseId)) {
                $errors['general'] = 'Student must be enrolled in the course first';
            } else {
                // Generate certificate
                $certificate = Certificate::generate($userId, $courseId);
                
                if ($certificate) {
                    flash('message', 'Certificate issued successfully!', 'success');
                    redirect(url('admin/certificates/index.php'));
                } else {
                    $errors['general'] = 'Failed to generate certificate. Student may not have completed the course.';
                }
            }
        }
    }
}

// Get all students
$students = User::all('student');

// Get all courses
$courses = Course::all(['order' => 'title ASC']);

$page_title = 'Issue Certificate';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-certificate text-primary-600 mr-2"></i>
                    Issue Certificate
                </h1>
                <p class="text-gray-600 mt-1">Manually issue a certificate to a student</p>
            </div>
            <a href="<?= url('admin/certificates/index.php') ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <!-- Info Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Certificate Requirements</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Student must be enrolled in the course</li>
                    <li>Course completion is recommended (but not required for manual issuance)</li>
                    <li>A unique certificate number will be generated automatically</li>
                    <li>The certificate PDF will be generated and emailed to the student</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Error Alert -->
    <?php if (!empty($errors['general'])): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
            <p class="text-sm text-red-800"><?= $errors['general'] ?></p>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <form method="POST" class="p-6">
            <?= csrfField() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Student Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Student <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" id="user_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['user_id']) ? 'border-red-500' : '' ?>">
                        <option value="">-- Select Student --</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>" <?= ($_POST['user_id'] ?? '') == $student['id'] ? 'selected' : '' ?>>
                                <?= sanitize($student['first_name'] . ' ' . $student['last_name']) ?> 
                                (<?= sanitize($student['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['user_id'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['user_id'] ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Course Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Course <span class="text-red-500">*</span>
                    </label>
                    <select name="course_id" id="course_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['course_id']) ? 'border-red-500' : '' ?>">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= ($_POST['course_id'] ?? '') == $course['id'] ? 'selected' : '' ?>>
                                <?= sanitize($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['course_id'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['course_id'] ?></p>
                    <?php endif; ?>
                </div>
                
            </div>
            
            <!-- Enrollment Check Info -->
            <div id="enrollment-info" class="mt-6 hidden">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-700">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Checking enrollment status...
                    </p>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                    <i class="fas fa-certificate mr-2"></i>
                    Issue Certificate
                </button>
                <a href="<?= url('admin/certificates/index.php') ?>" class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                    Cancel
                </a>
            </div>
            
        </form>
    </div>
    
    <!-- Quick Issue (Recently Completed) -->
    <?php
    $recentlyCompleted = $db->fetchAll("
        SELECT e.id as enrollment_id, e.user_id, e.course_id,
               u.first_name, u.last_name,
               c.title as course_title
        FROM enrollments e
        JOIN users u ON e.user_id = u.id
        JOIN courses c ON e.course_id = c.id
        LEFT JOIN certificates cert ON cert.enrollment_id = e.id
        WHERE e.enrollment_status = 'completed'
        AND cert.certificate_id IS NULL
        ORDER BY e.completed_at DESC
        LIMIT 10
    ");
    ?>
    
    <?php if (!empty($recentlyCompleted)): ?>
    <div class="mt-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-graduation-cap text-primary-600 mr-2"></i>
            Recently Completed (No Certificate)
        </h2>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recentlyCompleted as $item): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">
                                <?= sanitize($item['first_name'] . ' ' . $item['last_name']) ?>
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900"><?= sanitize($item['course_title']) ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="user_id" value="<?= $item['user_id'] ?>">
                                <input type="hidden" name="course_id" value="<?= $item['course_id'] ?>">
                                <button type="submit" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
                                    <i class="fas fa-certificate mr-1"></i>Issue Certificate
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<script>
// Check enrollment status when selections change
document.getElementById('user_id').addEventListener('change', checkEnrollment);
document.getElementById('course_id').addEventListener('change', checkEnrollment);

function checkEnrollment() {
    const userId = document.getElementById('user_id').value;
    const courseId = document.getElementById('course_id').value;
    const infoDiv = document.getElementById('enrollment-info');
    
    if (userId && courseId) {
        infoDiv.classList.remove('hidden');
        
        fetch(`<?= url('api/check-enrollment.php') ?>?user_id=${userId}&course_id=${courseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.enrolled) {
                    infoDiv.innerHTML = `
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>
                                Student is enrolled. Progress: ${data.progress}%
                            </p>
                        </div>
                    `;
                } else {
                    infoDiv.innerHTML = `
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Warning: Student is not enrolled in this course
                            </p>
                        </div>
                    `;
                }
            })
            .catch(() => {
                infoDiv.classList.add('hidden');
            });
    } else {
        infoDiv.classList.add('hidden');
    }
}
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>