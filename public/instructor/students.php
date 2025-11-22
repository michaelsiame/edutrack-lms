<?php
/**
 * Instructor - Students Management
 * View and manage students enrolled in instructor's courses
 */

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';

$db = Database::getInstance();
$instructorId = currentUserId();

// Get filter parameters
$courseFilter = $_GET['course'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

// Get instructor's courses for filter
$courses = $db->fetchAll("
    SELECT id, title FROM courses
    WHERE instructor_id = ?
    ORDER BY title
", [$instructorId]);

// Build query
$where = ["c.instructor_id = ?"];
$params = [$instructorId];

if ($courseFilter) {
    $where[] = "e.course_id = ?";
    $params[] = $courseFilter;
}

if ($search) {
    $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Get total count
$totalStudents = (int) $db->fetchColumn("
    SELECT COUNT(DISTINCT u.id)
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    $whereClause
", $params);

$totalPages = ceil($totalStudents / $perPage);
$offset = ($page - 1) * $perPage;

// Get students
$students = $db->fetchAll("
    SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, u.created_at,
           COUNT(DISTINCT e.id) as enrolled_courses,
           AVG(e.progress) as avg_progress,
           SUM(CASE WHEN e.enrollment_status = 'Completed' THEN 1 ELSE 0 END) as completed_courses
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    $whereClause
    GROUP BY u.id, u.first_name, u.last_name, u.email, u.created_at
    ORDER BY u.first_name, u.last_name
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

// Statistics
$stats = [
    'total_students' => $totalStudents,
    'active_enrollments' => (int) $db->fetchColumn("
        SELECT COUNT(*) FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE c.instructor_id = ? AND e.enrollment_status IN ('Enrolled', 'In Progress')
    ", [$instructorId]),
    'completed' => (int) $db->fetchColumn("
        SELECT COUNT(*) FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE c.instructor_id = ? AND e.enrollment_status = 'Completed'
    ", [$instructorId]),
];

$page_title = 'My Students - Instructor';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-users text-primary-600 mr-3"></i>My Students
            </h1>
            <p class="text-gray-600 mt-2">Students enrolled in your courses</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-600">Total Students</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= $stats['total_students'] ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-600">Active Enrollments</p>
                <p class="text-3xl font-bold text-green-600 mt-1"><?= $stats['active_enrollments'] ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-600">Completed</p>
                <p class="text-3xl font-bold text-purple-600 mt-1"><?= $stats['completed'] ?></p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= $courseFilter == $course['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Name or email...">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <?php if ($courseFilter || $search): ?>
                    <a href="students.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if (empty($students)): ?>
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Students Found</h3>
            <p class="text-gray-600">No students match your search criteria.</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courses</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($students as $student): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="<?= getGravatar($student['email']) ?>" class="h-10 w-10 rounded-full mr-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($student['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4"><?= $student['enrolled_courses'] ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: <?= round($student['avg_progress'] ?? 0) ?>%"></div>
                                </div>
                                <span class="text-sm"><?= round($student['avg_progress'] ?? 0) ?>%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <?= $student['completed_courses'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= date('M d, Y', strtotime($student['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-700">
                Showing <?= ($offset + 1) ?> to <?= min($offset + $perPage, $totalStudents) ?> of <?= $totalStudents ?>
            </p>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&course=<?= $courseFilter ?>&search=<?= urlencode($search) ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&course=<?= $courseFilter ?>&search=<?= urlencode($search) ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
