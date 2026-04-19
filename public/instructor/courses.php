<?php
/**
 * Instructor - Courses Management
 * Enhanced course management with class controls
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Statistics.php';
require_once '../../src/classes/Instructor.php';

$db = Database::getInstance();
$user = User::current();
$userId = $user->getId();

// Get instructor ID
$instructor = Instructor::getOrCreate($userId);
$instructorId = $instructor->getId();

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    validateCSRF();
    $courseIds = $_POST['course_ids'] ?? [];
    $action = $_POST['bulk_action'];
    
    if (!empty($courseIds)) {
        switch ($action) {
            case 'publish':
                foreach ($courseIds as $id) {
                    $db->query("UPDATE courses SET status = 'published' WHERE id = ? AND instructor_id = ?", [$id, $instructorId]);
                }
                flash('message', 'Selected courses published successfully', 'success');
                break;
            case 'unpublish':
                foreach ($courseIds as $id) {
                    $db->query("UPDATE courses SET status = 'draft' WHERE id = ? AND instructor_id = ?", [$id, $instructorId]);
                }
                flash('message', 'Selected courses unpublished', 'success');
                break;
            case 'delete':
                foreach ($courseIds as $id) {
                    // Soft delete - set status to archived
                    $db->query("UPDATE courses SET status = 'archived' WHERE id = ? AND instructor_id = ?", [$id, $instructorId]);
                }
                flash('message', 'Selected courses archived', 'success');
                break;
        }
    }
    redirect($_SERVER['REQUEST_URI']);
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

// Build query
$whereConditions = ["(c.instructor_id = ? OR c.instructor_id = ?)"];
$params = [$instructorId, $userId];

if ($statusFilter) {
    $whereConditions[] = "c.status = ?";
    $params[] = $statusFilter;
}

if ($searchQuery) {
    $whereConditions[] = "(c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

// Get total count
$totalCourses = (int) $db->fetchColumn("
    SELECT COUNT(*) FROM courses c
    $whereClause
", $params);

$totalPages = ceil($totalCourses / $perPage);
$offset = ($page - 1) * $perPage;

// Get courses with full metrics
$courses = $db->fetchAll("
    SELECT c.*, cat.name as category_name, cat.color as category_color,
           COUNT(DISTINCT e.id) as total_students,
           COUNT(DISTINCT m.id) as module_count,
           COUNT(DISTINCT l.id) as lesson_count,
           COUNT(DISTINCT a.id) as assignment_count,
           COUNT(DISTINCT q.id) as quiz_count,
           AVG(e.progress) as avg_progress,
           AVG(cr.rating) as avg_rating,
           COUNT(DISTINCT cr.id) as review_count,
           MAX(e.enrolled_at) as last_enrollment
    FROM courses c
    LEFT JOIN course_categories cat ON c.category_id = cat.id
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN modules m ON c.id = m.course_id
    LEFT JOIN lessons l ON m.id = l.module_id
    LEFT JOIN assignments a ON c.id = a.course_id
    LEFT JOIN quizzes q ON l.id = q.lesson_id
    LEFT JOIN course_reviews cr ON c.id = cr.course_id
    $whereClause
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

// Get statistics
$stats = $db->fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
        SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived
    FROM courses
    WHERE instructor_id = ? OR instructor_id = ?
", [$instructorId, $userId]);

$page_title = 'My Courses';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Courses</h1>
                <p class="text-gray-500 mt-1">Manage your courses and class content</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <a href="<?= url('instructor/courses/create.php') ?>" 
                   class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-500/30">
                    <i class="fas fa-plus mr-2"></i>Create Course
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-layer-group text-gray-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Published</p>
                        <p class="text-2xl font-bold text-green-600"><?= $stats['published'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Drafts</p>
                        <p class="text-2xl font-bold text-yellow-600"><?= $stats['draft'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-edit text-yellow-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Archived</p>
                        <p class="text-2xl font-bold text-gray-600"><?= $stats['archived'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-archive text-gray-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>"
                               placeholder="Search courses..."
                               class="w-full pl-11 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                <div class="flex gap-3">
                    <select name="status" onchange="this.form.submit()"
                            class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                        <option value="">All Status</option>
                        <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                    <?php if ($statusFilter || $searchQuery): ?>
                    <a href="courses.php" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition">
                        <i class="fas fa-times mr-1"></i>Clear
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Courses Grid -->
        <?php if (empty($courses)): ?>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-book-open text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Courses Found</h3>
            <p class="text-gray-500 mb-6 max-w-md mx-auto">
                <?= $searchQuery || $statusFilter ? 'No courses match your search criteria.' : 'Create your first course to start teaching and managing your classes.' ?>
            </p>
            <?php if (!$searchQuery && !$statusFilter): ?>
            <a href="<?= url('instructor/courses/create.php') ?>" 
               class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-500/30">
                <i class="fas fa-plus mr-2"></i> Create Your First Course
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php foreach ($courses as $course): 
                $statusColors = [
                    'published' => 'bg-green-100 text-green-700 border-green-200',
                    'draft' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'archived' => 'bg-gray-100 text-gray-700 border-gray-200'
                ];
                $statusColor = $statusColors[$course['status']] ?? $statusColors['draft'];
            ?>
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden group hover:shadow-card-hover transition-all duration-300">
                <!-- Thumbnail -->
                <div class="relative h-48 overflow-hidden">
                    <?php if (!empty($course['thumbnail'])): ?>
                    <img src="<?= url('uploads/' . $course['thumbnail']) ?>" 
                         alt="<?= htmlspecialchars($course['title']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                    <div class="w-full h-full bg-primary-500 flex items-center justify-center">
                        <i class="fas fa-book text-white text-5xl"></i>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Status Badge -->
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= $statusColor ?>">
                            <?= ucfirst($course['status']) ?>
                        </span>
                    </div>
                    
                    <!-- Quick Actions Overlay -->
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                        <a href="<?= url('instructor/course-edit.php?id=' . $course['id']) ?>" 
                           class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-gray-900 hover:bg-primary-50 transition"
                           title="Edit Course">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= url('instructor/courses/modules.php?id=' . $course['id']) ?>" 
                           class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-gray-900 hover:bg-primary-50 transition"
                           title="Manage Content">
                            <i class="fas fa-list"></i>
                        </a>
                        <a href="<?= url('course.php?slug=' . $course['slug']) ?>" 
                           target="_blank"
                           class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-gray-900 hover:bg-primary-50 transition"
                           title="Preview">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-5">
                    <!-- Category -->
                    <?php if ($course['category_name']): ?>
                    <span class="text-xs font-medium text-primary-600 bg-primary-50 px-2 py-1 rounded-lg">
                        <?= htmlspecialchars($course['category_name']) ?>
                    </span>
                    <?php endif; ?>
                    
                    <!-- Title -->
                    <h3 class="font-bold text-gray-900 text-lg mt-2 mb-2 line-clamp-2 group-hover:text-primary-600 transition">
                        <?= htmlspecialchars($course['title']) ?>
                    </h3>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-4 gap-2 mb-4 text-center">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <i class="fas fa-users text-purple-500 text-sm mb-1"></i>
                            <p class="text-xs text-gray-500">Students</p>
                            <p class="font-semibold text-gray-900"><?= $course['total_students'] ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <i class="fas fa-folder text-blue-500 text-sm mb-1"></i>
                            <p class="text-xs text-gray-500">Modules</p>
                            <p class="font-semibold text-gray-900"><?= $course['module_count'] ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <i class="fas fa-play text-green-500 text-sm mb-1"></i>
                            <p class="text-xs text-gray-500">Lessons</p>
                            <p class="font-semibold text-gray-900"><?= $course['lesson_count'] ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <i class="fas fa-star text-yellow-500 text-sm mb-1"></i>
                            <p class="text-xs text-gray-500">Rating</p>
                            <p class="font-semibold text-gray-900">
                                <?= $course['avg_rating'] ? number_format($course['avg_rating'], 1) : '-' ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <?php if ($course['avg_progress']): ?>
                    <div class="mb-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Avg. Progress</span>
                            <span><?= round($course['avg_progress']) ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all" style="width: <?= round($course['avg_progress']) ?>"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                        <a href="<?= url('instructor/course-edit.php?id=' . $course['id']) ?>" 
                           class="flex-1 px-4 py-2 bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-100 transition text-sm font-medium text-center">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <a href="<?= url('instructor/courses/modules.php?id=' . $course['id']) ?>" 
                           class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium text-center">
                            <i class="fas fa-list mr-1"></i>Content
                        </a>
                        <a href="<?= url('course.php?slug=' . $course['slug']) ?>" 
                           target="_blank"
                           class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm font-medium"
                           title="Preview">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="mt-8 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Showing <?= (($page - 1) * $perPage) + 1 ?> - <?= min($page * $perPage, $totalCourses) ?> of <?= $totalCourses ?> courses
            </p>
            <div class="flex items-center gap-2">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&status=<?= $statusFilter ?>&search=<?= urlencode($searchQuery) ?>" 
                   class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-chevron-left mr-1"></i> Previous
                </a>
                <?php endif; ?>
                
                <div class="flex items-center gap-1">
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?= $i ?>&status=<?= $statusFilter ?>&search=<?= urlencode($searchQuery) ?>" 
                       class="w-10 h-10 flex items-center justify-center rounded-lg <?= $i === $page ? 'bg-primary-600 text-white' : 'border border-gray-200 hover:bg-gray-50' ?> transition">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                </div>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&status=<?= $statusFilter ?>&search=<?= urlencode($searchQuery) ?>" 
                   class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
