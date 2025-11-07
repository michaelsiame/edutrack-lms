<?php
/**
 * Students Management Page  
 * View all students enrolled in instructor's courses
 */

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';

$user = User::current();
$instructorId = $user->getId();

// Get all unique students enrolled in instructor's courses
$students = $db->fetchAll("
    SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, u.created_at,
           COUNT(DISTINCT e.course_id) as enrolled_courses,
           AVG(e.progress_percentage) as avg_progress,
           COUNT(DISTINCT CASE WHEN e.status = 'completed' THEN e.id END) as completed_courses,
           MAX(e.last_accessed) as last_activity
    FROM users u
    JOIN enrollments e ON u.id = e.user_id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ? AND u.role = 'student'
    GROUP BY u.id
    ORDER BY last_activity DESC
", [$instructorId]);

$page_title = 'My Students - Instructor';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Students</h1>
                    <p class="text-gray-600 mt-2">Students enrolled in your courses</p>
                </div>
                <a href="<?= url('instructor/index.php') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= count($students) ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Active This Week</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            <?php
                            $activeCount = 0;
                            foreach ($students as $student) {
                                if ($student['last_activity'] && strtotime($student['last_activity']) > strtotime('-7 days')) {
                                    $activeCount++;
                                }
                            }
                            echo $activeCount;
                            ?>
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-user-check text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Avg Progress</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            <?php
                            $totalProgress = 0;
                            foreach ($students as $student) {
                                $totalProgress += $student['avg_progress'];
                            }
                            echo count($students) > 0 ? round($totalProgress / count($students)) : 0;
                            ?>%
                        </p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <?php if (empty($students)): ?>
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Students Yet</h3>
            <p class="text-gray-500">Students will appear here once they enroll in your courses</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Student
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Courses
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Progress
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Activity
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($students as $student): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full"
                                             src="<?= getGravatar($student['email']) ?>"
                                             alt="<?= htmlspecialchars($student['first_name']) ?>">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($student['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= $student['enrolled_courses'] ?> enrolled
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?= $student['completed_courses'] ?> completed
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2" style="max-width: 100px;">
                                        <div class="bg-primary-600 h-2 rounded-full"
                                             style="width: <?= round($student['avg_progress']) ?>%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">
                                        <?= round($student['avg_progress']) ?>%
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $student['last_activity'] ? timeAgo($student['last_activity']) : 'Never' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
