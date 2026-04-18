<?php
/**
 * Instructor Quick Actions
 * Fast access to common teaching tasks
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/User.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Instructor.php';

$db = Database::getInstance();
$user = User::current();
$userId = $user->getId();

$instructor = Instructor::getOrCreate($userId);
$instructorId = $instructor->getId();

// Handle quick actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    $action = $_POST['quick_action'] ?? '';
    
    switch ($action) {
        case 'duplicate_lesson':
            $lessonId = $_POST['lesson_id'] ?? null;
            $targetModule = $_POST['target_module'] ?? null;
            if ($lessonId && $targetModule) {
                duplicateLesson($lessonId, $targetModule, $db);
                flash('message', 'Lesson duplicated successfully', 'success');
            }
            break;
            
        case 'send_announcement':
            $courseId = $_POST['course_id'] ?? null;
            $message = $_POST['message'] ?? '';
            if ($courseId && $message) {
                sendAnnouncement($courseId, $message, $userId, $db);
                flash('message', 'Announcement sent to students', 'success');
            }
            break;
            
        case 'grade_all':
            $submissionIds = $_POST['submission_ids'] ?? [];
            $points = $_POST['points'] ?? [];
            bulkGrade($submissionIds, $points, $db, $userId);
            flash('message', 'Submissions graded successfully', 'success');
            break;
            
        case 'update_progress':
            $enrollmentIds = $_POST['enrollment_ids'] ?? [];
            $progress = $_POST['progress'] ?? [];
            bulkUpdateProgress($enrollmentIds, $progress, $db, $userId);
            flash('message', 'Student progress updated', 'success');
            break;
    }
    
    redirect($_SERVER['REQUEST_URI']);
}

function duplicateLesson($lessonId, $targetModuleId, $db) {
    // Get original lesson
    $lesson = $db->fetchOne("SELECT * FROM lessons WHERE id = ?", [$lessonId]);
    if (!$lesson) return;
    
    // Get next display order
    $maxOrder = $db->fetchColumn("SELECT MAX(display_order) FROM lessons WHERE module_id = ?", [$targetModuleId]);
    $displayOrder = ($maxOrder !== null) ? $maxOrder + 1 : 0;
    
    // Insert duplicated lesson
    $db->query(
        "INSERT INTO lessons (module_id, title, slug, description, lesson_type, video_url, content, duration, display_order, is_preview) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $targetModuleId,
            $lesson['title'] . ' (Copy)',
            slugify($lesson['title'] . ' Copy'),
            $lesson['description'],
            $lesson['lesson_type'],
            $lesson['video_url'],
            $lesson['content'],
            $lesson['duration'],
            $displayOrder,
            $lesson['is_preview']
        ]
    );
}

function sendAnnouncement($courseId, $message, $instructorId, $db) {
    // Create announcement record
    $db->query(
        "INSERT INTO announcements (course_id, title, content, created_by, created_at) VALUES (?, ?, ?, ?, NOW())",
        [$courseId, 'Quick Announcement', $message, $instructorId]
    );
    
    // Send email notifications to enrolled students
    require_once '../../src/classes/Email.php';
    
    // Get course info
    $course = $db->fetchOne("SELECT title, slug FROM courses WHERE id = ?", [$courseId]);
    
    // Get enrolled students
    $students = $db->fetchAll("
        SELECT u.id, u.first_name, u.email 
        FROM users u
        JOIN enrollments e ON u.id = e.user_id
        WHERE e.course_id = ? AND e.enrollment_status IN ('Active', 'In Progress', 'Completed')
    ", [$courseId]);
    
    foreach ($students as $student) {
        $email = new Email();
        
        // Prepare template variables
        $first_name = $student['first_name'];
        $course_title = $course['title'];
        $announcement_title = 'Quick Announcement';
        $announcement_content = $message;
        $course_url = url('learn.php?course=' . urlencode($course['slug']));
        
        // Get email template
        ob_start();
        include '../../src/mail/announcement-notification.php';
        $body = ob_get_clean();
        
        $email->send($student['email'], 'New Announcement: ' . $course['title'], $body);
    }
}

function bulkGrade($submissionIds, $points, $db, $instructorId) {
    foreach ($submissionIds as $id) {
        if (isset($points[$id])) {
            // Verify instructor owns the course for this submission
            $ownsSubmission = $db->fetchOne("
                SELECT 1 FROM assignment_submissions asub
                JOIN assignments a ON asub.assignment_id = a.id
                JOIN courses c ON a.course_id = c.id
                WHERE asub.id = ? AND c.instructor_id = ?
            ", [$id, $instructorId]);
            
            if ($ownsSubmission) {
                $db->query(
                    "UPDATE assignment_submissions SET points_earned = ?, status = 'graded', graded_at = NOW() WHERE id = ?",
                    [$points[$id], $id]
                );
            }
        }
    }
}

function bulkUpdateProgress($enrollmentIds, $progress, $db, $instructorId) {
    foreach ($enrollmentIds as $id) {
        if (isset($progress[$id])) {
            // Verify instructor owns the course for this enrollment
            $ownsEnrollment = $db->fetchOne("
                SELECT 1 FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.id = ? AND c.instructor_id = ?
            ", [$id, $instructorId]);
            
            if ($ownsEnrollment) {
                $progressValue = max(0, min(100, (int)$progress[$id]));
                $status = $progressValue >= 100 ? 'Completed' : ($progressValue > 0 ? 'In Progress' : 'Enrolled');
                
                $db->query(
                    "UPDATE enrollments SET progress = ?, enrollment_status = ? WHERE id = ?",
                    [$progressValue, $status, $id]
                );
            }
        }
    }
}

// Get instructor's data for quick actions
$courses = $db->fetchAll("
    SELECT id, title FROM courses 
    WHERE instructor_id = ? AND status = 'published'
    ORDER BY title
", [$instructorId]);

$pendingSubmissions = $db->fetchAll("
    SELECT asub.*, a.title as assignment_title, a.max_points,
           c.title as course_title, u.first_name, u.last_name, u.email
    FROM assignment_submissions asub
    JOIN assignments a ON asub.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN students st ON asub.student_id = st.id
    JOIN users u ON st.user_id = u.id
    WHERE c.instructor_id = ? AND asub.status = 'submitted'
    ORDER BY asub.submitted_at DESC
    LIMIT 5
", [$instructorId]);

$recentEnrollments = $db->fetchAll("
    SELECT e.*, c.title as course_title, u.first_name, u.last_name, u.email
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 10
", [$instructorId]);

$page_title = 'Quick Actions';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-bolt text-yellow-500 mr-3"></i>Quick Actions
                </h1>
                <p class="text-gray-500 mt-1">Fast access to common teaching tasks</p>
            </div>
        </div>

        <!-- Quick Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Quick Announcement -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-bullhorn text-blue-600 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Send Announcement</h3>
                <p class="text-sm text-gray-500 mb-4">Quick message to all students in a course</p>
                <button onclick="openModal('announcementModal')" 
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    Send Now
                </button>
            </div>

            <!-- Grade Pending -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-check-double text-green-600 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Grade Submissions</h3>
                <p class="text-sm text-gray-500 mb-4"><?= count($pendingSubmissions) ?> pending submissions</p>
                <a href="<?= url('instructor/assignments.php') ?>" 
                   class="block w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium text-center">
                    Start Grading
                </a>
            </div>

            <!-- Update Progress -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Update Progress</h3>
                <p class="text-sm text-gray-500 mb-4">Bulk update student completion</p>
                <button onclick="openModal('progressModal')"
                        class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                    Update Now
                </button>
            </div>

            <!-- Duplicate Content -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-copy text-orange-600 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Duplicate Content</h3>
                <p class="text-sm text-gray-500 mb-4">Copy lessons between modules</p>
                <button onclick="openModal('duplicateModal')"
                        class="w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition text-sm font-medium">
                    Duplicate
                </button>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Pending Submissions -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Pending Submissions</h3>
                    <a href="<?= url('instructor/assignments.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                        View All
                    </a>
                </div>
                <?php if (empty($pendingSubmissions)): ?>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500 text-sm">All caught up! No pending submissions.</p>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($pendingSubmissions as $sub): ?>
                    <div class="p-4 hover:bg-gray-50/50 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="<?= getGravatar($sub['email']) ?>" class="w-10 h-10 rounded-full">
                                <div>
                                    <p class="font-medium text-gray-900 text-sm"><?= htmlspecialchars($sub['assignment_title']) ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?= htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']) ?> • 
                                        <?= htmlspecialchars($sub['course_title']) ?>
                                    </p>
                                </div>
                            </div>
                            <a href="<?= url('instructor/assignments.php?submission=' . $sub['id']) ?>" 
                               class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-medium hover:bg-green-200 transition">
                                Grade
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Enrollments -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Recent Enrollments</h3>
                    <a href="<?= url('instructor/students.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                        View All
                    </a>
                </div>
                <?php if (empty($recentEnrollments)): ?>
                <div class="p-8 text-center">
                    <p class="text-gray-500 text-sm">No recent enrollments</p>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    <?php foreach ($recentEnrollments as $enrollment): ?>
                    <div class="p-4 hover:bg-gray-50/50 transition">
                        <div class="flex items-center gap-3">
                            <img src="<?= getGravatar($enrollment['email']) ?>" class="w-10 h-10 rounded-full">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 text-sm">
                                    <?= htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?>
                                </p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($enrollment['course_title']) ?></p>
                            </div>
                            <span class="text-xs text-gray-400"><?= timeAgo($enrollment['enrolled_at']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Send Announcement Modal -->
<div id="announcementModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Send Quick Announcement</h3>
            <button onclick="closeModal('announcementModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-6 space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="quick_action" value="send_announcement">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                <select name="course_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                    <option value="">Select course...</option>
                    <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea name="message" rows="4" required
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                          placeholder="Enter your announcement..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('announcementModal')"
                        class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium transition">
                    <i class="fas fa-paper-plane mr-2"></i>Send
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Progress Modal -->
<div id="progressModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Bulk Update Progress</h3>
            <button onclick="closeModal('progressModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">
                Use the <a href="<?= url('instructor/students.php') ?>" class="text-primary-600">Students page</a> 
                to update progress for individual students.
            </p>
            <button onclick="closeModal('progressModal')" 
                    class="w-full px-4 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition">
                Go to Students Page
            </button>
        </div>
    </div>
</div>

<!-- Duplicate Content Modal -->
<div id="duplicateModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Duplicate Content</h3>
            <button onclick="closeModal('duplicateModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">
                To duplicate lessons between modules, use the content manager and click the 
                <i class="fas fa-copy text-gray-400"></i> icon on any lesson.
            </p>
            <a href="<?= url('instructor/courses.php') ?>" 
               class="block w-full px-4 py-3 bg-orange-600 text-white rounded-xl hover:bg-orange-700 font-medium transition text-center">
                Go to Course Content
            </a>
        </div>
    </div>
</div>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
