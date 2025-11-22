<?php
/**
 * Instructor - Analytics Dashboard
 * View course performance and student engagement metrics
 */

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Statistics.php';

$db = Database::getInstance();
$instructorId = currentUserId();

// Get instructor stats
$stats = Statistics::getInstructorStats($instructorId);

// Get course-specific metrics
$courseMetrics = $db->fetchAll("
    SELECT c.id, c.title, c.slug, c.status, c.price, c.created_at,
           COUNT(DISTINCT e.id) as total_enrollments,
           COUNT(DISTINCT CASE WHEN e.enrollment_status = 'Completed' THEN e.id END) as completions,
           AVG(e.progress) as avg_progress,
           AVG(cr.rating) as avg_rating,
           COUNT(DISTINCT cr.id) as review_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN course_reviews cr ON c.id = cr.course_id
    WHERE c.instructor_id = ?
    GROUP BY c.id, c.title, c.slug, c.status, c.price, c.created_at
    ORDER BY total_enrollments DESC
", [$instructorId]);

// Monthly enrollment trend (last 6 months)
$enrollmentTrend = $db->fetchAll("
    SELECT DATE_FORMAT(e.enrolled_at, '%Y-%m') as month,
           COUNT(*) as enrollments
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    AND e.enrolled_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(e.enrolled_at, '%Y-%m')
    ORDER BY month ASC
", [$instructorId]);

// Calculate revenue from enrollments
$revenueData = $db->fetchOne("
    SELECT
        COALESCE(SUM(c.price), 0) as total_revenue,
        COALESCE(SUM(CASE WHEN e.enrolled_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN c.price ELSE 0 END), 0) as monthly_revenue
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ? AND e.payment_status = 'completed'
", [$instructorId]);

$page_title = 'Analytics - Instructor';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-chart-line text-primary-600 mr-3"></i>Analytics
            </h1>
            <p class="text-gray-600 mt-2">Track your course performance and student engagement</p>
        </div>

        <!-- Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= $stats['total_courses'] ?></p>
                    </div>
                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-book text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-purple-600 mt-1"><?= $stats['total_students'] ?></p>
                    </div>
                    <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Revenue</p>
                        <p class="text-3xl font-bold text-green-600 mt-1">K<?= number_format($revenueData['total_revenue'] ?? 0, 2) ?></p>
                    </div>
                    <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Avg Rating</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-1">
                            <?= number_format($stats['avg_rating'] ?? 0, 1) ?>
                            <i class="fas fa-star text-lg"></i>
                        </p>
                    </div>
                    <div class="h-12 w-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-star text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Enrollment Trend Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Enrollment Trend (6 Months)</h2>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="enrollmentChart"></canvas>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Performance Summary</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <span class="text-gray-700">Published Courses</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900"><?= $stats['published_courses'] ?></span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-graduation-cap text-purple-500 text-xl mr-3"></i>
                            <span class="text-gray-700">Total Enrollments</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900"><?= $stats['total_enrollments'] ?></span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-trophy text-yellow-500 text-xl mr-3"></i>
                            <span class="text-gray-700">Course Completions</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900"><?= $stats['completed_enrollments'] ?? 0 ?></span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-money-bill-wave text-green-500 text-xl mr-3"></i>
                            <span class="text-gray-700">This Month Revenue</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">K<?= number_format($revenueData['monthly_revenue'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Performance Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Course Performance</h2>
            </div>

            <?php if (empty($courseMetrics)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-chart-bar text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Courses Yet</h3>
                <p class="text-gray-600">Create your first course to see analytics.</p>
                <a href="<?= url('instructor/courses/create.php') ?>" class="mt-4 inline-block px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-plus mr-2"></i>Create Course
                </a>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($courseMetrics as $course): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="<?= url('instructor/course-edit.php?id=' . $course['id']) ?>" class="text-primary-600 hover:text-primary-900 font-medium">
                                    <?= htmlspecialchars($course['title']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($course['status'] == 'published'): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Published</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"><?= ucfirst($course['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 font-medium"><?= $course['total_enrollments'] ?></td>
                            <td class="px-6 py-4"><?= $course['completions'] ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-primary-600 h-2 rounded-full" style="width: <?= round($course['avg_progress'] ?? 0) ?>%"></div>
                                    </div>
                                    <span class="text-sm"><?= round($course['avg_progress'] ?? 0) ?>%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($course['review_count'] > 0): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="font-medium"><?= number_format($course['avg_rating'], 1) ?></span>
                                        <span class="text-gray-500 text-sm ml-1">(<?= $course['review_count'] ?>)</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">No reviews</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartElement = document.getElementById('enrollmentChart');
    if (!chartElement) return;

    const enrollmentData = <?= json_encode($enrollmentTrend) ?>;

    if (enrollmentData.length === 0) {
        chartElement.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No enrollment data available</div>';
        return;
    }

    new Chart(chartElement.getContext('2d'), {
        type: 'line',
        data: {
            labels: enrollmentData.map(d => d.month),
            datasets: [{
                label: 'Enrollments',
                data: enrollmentData.map(d => d.enrollments),
                borderColor: '#2E70DA',
                backgroundColor: 'rgba(46, 112, 218, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
