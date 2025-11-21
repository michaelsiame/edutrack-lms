<?php
/**
 * Admin Reports Dashboard
 * Overview of system reports and analytics
 */

require_once '../../../src/includes/admin-debug.php';
require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Statistics.php';

// Date range filters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Get statistics for the date range
$stats = Statistics::getAdminDashboard();

// Get enrollment trends (last 30 days)
$enrollmentTrends = $db->fetchAll("
    SELECT DATE(enrolled_at) as date, COUNT(*) as count
    FROM enrollments
    WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(enrolled_at)
    ORDER BY date ASC
");

// Get revenue trends (last 30 days)
$revenueTrends = $db->fetchAll("
    SELECT DATE(created_at) as date, SUM(amount) as revenue
    FROM payments
    WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

// Top performing courses
$topCourses = $db->fetchAll("
    SELECT c.id, c.title, c.thumbnail,
           COUNT(DISTINCT e.id) as enrollment_count,
           SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END) as revenue,
           AVG(CASE WHEN r.rating IS NOT NULL THEN r.rating ELSE NULL END) as avg_rating,
           COUNT(DISTINCT r.id) as review_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN payments p ON c.id = p.course_id
    LEFT JOIN reviews r ON c.id = r.course_id
    WHERE c.status = 'published'
    GROUP BY c.id
    ORDER BY enrollment_count DESC
    LIMIT 10
");

// Recent enrollments
$recentEnrollments = $db->fetchAll("
    SELECT e.*, c.title as course_title, u.first_name, u.last_name, u.email
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    ORDER BY e.enrolled_at DESC
    LIMIT 10
");

$page_title = 'Reports & Analytics';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container-fluid px-4 py-6">

    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
            <p class="text-gray-600 mt-1">Comprehensive system insights and performance metrics</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= url('admin/reports/enrollments.php') ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-graduation-cap mr-2"></i>Enrollment Report
            </a>
            <a href="<?= url('admin/reports/revenue.php') ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-dollar-sign mr-2"></i>Revenue Report
            </a>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Students</p>
                    <p class="text-3xl font-bold mt-2"><?= number_format($stats['users']['students']) ?></p>
                    <p class="text-blue-100 text-xs mt-2">Active learners</p>
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <i class="fas fa-users text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Total Revenue</p>
                    <p class="text-3xl font-bold mt-2">ZMW <?= number_format($stats['revenue']['total']) ?></p>
                    <p class="text-green-100 text-xs mt-2">All time earnings</p>
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <i class="fas fa-dollar-sign text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Total Enrollments</p>
                    <p class="text-3xl font-bold mt-2"><?= number_format($stats['enrollments']['total']) ?></p>
                    <p class="text-purple-100 text-xs mt-2">Course registrations</p>
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <i class="fas fa-graduation-cap text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Active Courses</p>
                    <p class="text-3xl font-bold mt-2"><?= number_format($stats['courses']['published']) ?></p>
                    <p class="text-orange-100 text-xs mt-2">Published courses</p>
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <i class="fas fa-book text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Enrollment Trends Chart -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Enrollment Trends (Last 30 Days)</h2>
            </div>
            <div class="p-6">
                <canvas id="enrollmentChart" height="200"></canvas>
            </div>
        </div>

        <!-- Revenue Trends Chart -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Revenue Trends (Last 30 Days)</h2>
            </div>
            <div class="p-6">
                <canvas id="revenueChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Performing Courses -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Top Performing Courses</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reviews</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($topCourses as $course): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <?php if ($course['thumbnail']): ?>
                                <img src="<?= htmlspecialchars($course['thumbnail']) ?>" 
                                     alt="" class="h-10 w-10 rounded object-cover">
                                <?php else: ?>
                                <div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas fa-book text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($course['title']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($course['enrollment_count']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ZMW <?= number_format($course['revenue'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($course['avg_rating']): ?>
                            <div class="flex items-center">
                                <span class="text-yellow-400 mr-1">★</span>
                                <span class="text-sm text-gray-900"><?= number_format($course['avg_rating'], 1) ?></span>
                            </div>
                            <?php else: ?>
                            <span class="text-sm text-gray-400">No ratings</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($course['review_count']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Enrollments -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Recent Enrollments</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recentEnrollments as $enrollment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?>
                            </div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($enrollment['email']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($enrollment['course_title']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($enrollment['status'] == 'active'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Active</span>
                            <?php elseif ($enrollment['status'] == 'completed'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"><?= ucfirst($enrollment['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $enrollment['progress_percentage'] ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-600"><?= round($enrollment['progress_percentage']) ?>%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('M d, Y', strtotime($enrollment['enrolled_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Enrollment Trends Chart
const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
const enrollmentChart = new Chart(enrollmentCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(fn($e) => date('M d', strtotime($e['date'])), $enrollmentTrends)) ?>,
        datasets: [{
            label: 'Enrollments',
            data: <?= json_encode(array_map(fn($e) => $e['count'], $enrollmentTrends)) ?>,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Revenue Trends Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($r) => date('M d', strtotime($r['date'])), $revenueTrends)) ?>,
        datasets: [{
            label: 'Revenue (ZMW)',
            data: <?= json_encode(array_map(fn($r) => $r['revenue'], $revenueTrends)) ?>,
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
