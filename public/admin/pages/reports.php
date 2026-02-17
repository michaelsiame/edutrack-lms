<?php
/**
 * Reports & Analytics Page
 * Comprehensive reporting dashboard for admin
 * Provides: $db (Database), $currency, $settings from admin/index.php
 */

// ============================================
// KPI SUMMARY DATA
// ============================================

$totalRevenue = $db->fetchColumn("
    SELECT COALESCE(SUM(amount), 0)
    FROM payments
    WHERE payment_status = 'Completed'
") ?: 0;

$monthlyRevenue = $db->fetchColumn("
    SELECT COALESCE(SUM(amount), 0)
    FROM payments
    WHERE payment_status = 'Completed'
      AND MONTH(payment_date) = MONTH(NOW())
      AND YEAR(payment_date) = YEAR(NOW())
") ?: 0;

$totalStudents = $db->fetchColumn("
    SELECT COUNT(DISTINCT u.id)
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    WHERE r.role_name = 'Student'
") ?: 0;

$completionRate = $db->fetchColumn("
    SELECT ROUND(
        COUNT(CASE WHEN enrollment_status = 'Completed' THEN 1 END) * 100.0
        / NULLIF(COUNT(*), 0),
    1)
    FROM enrollments
") ?: 0;

$activeEnrollments = $db->fetchColumn("
    SELECT COUNT(*)
    FROM enrollments
    WHERE enrollment_status = 'In Progress'
") ?: 0;

$certificatesIssued = $db->fetchColumn("
    SELECT COUNT(*) FROM certificates
") ?: 0;

// ============================================
// ENROLLMENT TRENDS (Last 6 Months)
// ============================================

$enrollmentTrends = $db->fetchAll("
    SELECT
        DATE_FORMAT(enrolled_at, '%Y-%m') AS month_key,
        COUNT(*) AS new_enrollments,
        COUNT(CASE WHEN enrollment_status = 'Completed' THEN 1 END) AS completions
    FROM enrollments
    WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m')
    ORDER BY month_key ASC
");

// Calculate running totals
$runningTotal = $db->fetchColumn("
    SELECT COUNT(*)
    FROM enrollments
    WHERE enrolled_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)
") ?: 0;

foreach ($enrollmentTrends as &$trend) {
    $runningTotal += (int)$trend['new_enrollments'];
    $trend['running_total'] = $runningTotal;
}
unset($trend);

// ============================================
// TOP PERFORMING COURSES
// ============================================

$topCourses = $db->fetchAll("
    SELECT
        c.title AS course_title,
        CONCAT(u.first_name, ' ', u.last_name) AS instructor_name,
        COUNT(DISTINCT e.id) AS enrollment_count,
        COUNT(DISTINCT CASE WHEN e.enrollment_status = 'Completed' THEN e.id END) AS completion_count,
        ROUND(AVG(e.progress), 1) AS avg_progress,
        COALESCE(SUM(DISTINCT p.total_revenue), 0) AS revenue
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN users u ON c.instructor_id = u.id
    LEFT JOIN (
        SELECT course_id, SUM(amount) AS total_revenue
        FROM payments
        WHERE payment_status = 'Completed'
        GROUP BY course_id
    ) p ON c.id = p.course_id
    GROUP BY c.id, c.title, u.first_name, u.last_name
    ORDER BY enrollment_count DESC
    LIMIT 10
");

// ============================================
// STUDENT PROGRESS DISTRIBUTION
// ============================================

$progressDistribution = $db->fetchAll("
    SELECT
        CASE
            WHEN progress >= 0 AND progress < 25 THEN '0-25%'
            WHEN progress >= 25 AND progress < 50 THEN '25-50%'
            WHEN progress >= 50 AND progress < 75 THEN '50-75%'
            WHEN progress >= 75 AND progress <= 100 THEN '75-100%'
        END AS progress_range,
        COUNT(*) AS student_count
    FROM enrollments
    GROUP BY
        CASE
            WHEN progress >= 0 AND progress < 25 THEN '0-25%'
            WHEN progress >= 25 AND progress < 50 THEN '25-50%'
            WHEN progress >= 50 AND progress < 75 THEN '50-75%'
            WHEN progress >= 75 AND progress <= 100 THEN '75-100%'
        END
    ORDER BY MIN(progress) ASC
");

// ============================================
// REVENUE BY COURSE
// ============================================

$revenueByCourse = $db->fetchAll("
    SELECT
        c.title AS course_name,
        COUNT(p.payment_id) AS total_payments,
        COALESCE(SUM(CASE WHEN p.payment_status = 'Completed' THEN p.amount ELSE 0 END), 0) AS completed_revenue,
        COALESCE(SUM(CASE WHEN p.payment_status = 'Pending' THEN p.amount ELSE 0 END), 0) AS pending_revenue
    FROM courses c
    JOIN payments p ON c.id = p.course_id
    GROUP BY c.id, c.title
    ORDER BY completed_revenue DESC
    LIMIT 10
");

// ============================================
// INSTRUCTOR PERFORMANCE
// ============================================

$instructorPerformance = $db->fetchAll("
    SELECT
        CONCAT(u.first_name, ' ', u.last_name) AS instructor_name,
        COUNT(DISTINCT c.id) AS courses_taught,
        COUNT(DISTINCT e.id) AS total_students,
        ROUND(AVG(cr.rating), 1) AS avg_rating,
        COALESCE(SUM(CASE WHEN p.payment_status = 'Completed' THEN p.amount ELSE 0 END), 0) AS total_revenue
    FROM users u
    JOIN courses c ON u.id = c.instructor_id
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN course_reviews cr ON c.id = cr.course_id
    LEFT JOIN payments p ON c.id = p.course_id
    GROUP BY u.id, u.first_name, u.last_name
    ORDER BY total_students DESC
");

// ============================================
// RECENT ACTIVITY LOG
// ============================================

$recentActivities = $db->fetchAll("
    SELECT
        al.activity_type,
        al.description,
        al.created_at,
        CONCAT(u.first_name, ' ', u.last_name) AS user_name
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 20
");

// Progress bar colors
$progressColors = [
    '0-25%'  => ['bg' => 'bg-red-500', 'light' => 'bg-red-100', 'text' => 'text-red-700'],
    '25-50%' => ['bg' => 'bg-yellow-500', 'light' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
    '50-75%' => ['bg' => 'bg-blue-500', 'light' => 'bg-blue-100', 'text' => 'text-blue-700'],
    '75-100%'=> ['bg' => 'bg-green-500', 'light' => 'bg-green-100', 'text' => 'text-green-700'],
];

$maxStudentCount = 1;
foreach ($progressDistribution as $pd) {
    if ((int)$pd['student_count'] > $maxStudentCount) {
        $maxStudentCount = (int)$pd['student_count'];
    }
}
?>

<div class="space-y-6">

    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Reports &amp; Analytics</h2>
        <p class="text-gray-500 text-sm mt-1">Comprehensive overview of platform performance, enrollments, revenue, and student progress</p>
    </div>

    <!-- ================================================== -->
    <!-- KPI Summary Cards -->
    <!-- ================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- Total Revenue -->
        <div class="bg-white p-5 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Total Revenue</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5"><?= htmlspecialchars($currency) ?> <?= htmlspecialchars(number_format($totalRevenue, 2)) ?></p>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white p-5 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Monthly Revenue</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5"><?= htmlspecialchars($currency) ?> <?= htmlspecialchars(number_format($monthlyRevenue, 2)) ?></p>
                </div>
            </div>
        </div>

        <!-- Total Students -->
        <div class="bg-white p-5 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-purple-100 text-purple-600 rounded-lg">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Total Students</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5"><?= htmlspecialchars(number_format($totalStudents)) ?></p>
                </div>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="bg-white p-5 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-teal-100 text-teal-600 rounded-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Completion Rate</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5"><?= htmlspecialchars($completionRate) ?>%</p>
                </div>
            </div>
        </div>

        <!-- Active Enrollments -->
        <div class="bg-white p-5 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-yellow-100 text-yellow-600 rounded-lg">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Active Enrollments</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5"><?= htmlspecialchars(number_format($activeEnrollments)) ?></p>
                </div>
            </div>
        </div>

        <!-- Certificates Issued -->
        <div class="bg-white p-5 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-indigo-100 text-indigo-600 rounded-lg">
                    <i class="fas fa-certificate"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Certificates Issued</p>
                    <p class="text-lg font-bold text-gray-800 mt-0.5"><?= htmlspecialchars(number_format($certificatesIssued)) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- Enrollment Trends (Last 6 Months) -->
    <!-- ================================================== -->
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-chart-line text-blue-500 mr-2"></i>Enrollment Trends (Last 6 Months)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Month</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">New Enrollments</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Completions</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Running Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (!empty($enrollmentTrends)): ?>
                        <?php foreach ($enrollmentTrends as $trend): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-800">
                                    <?= htmlspecialchars(date('F Y', strtotime($trend['month_key'] . '-01'))) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        <?= htmlspecialchars(number_format($trend['new_enrollments'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <?= htmlspecialchars(number_format($trend['completions'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-700">
                                    <?= htmlspecialchars(number_format($trend['running_total'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                                <p>No enrollment data available for the last 6 months</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- Top Performing Courses -->
    <!-- ================================================== -->
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i>Top Performing Courses
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Instructor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Enrollments</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Completions</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Avg Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (!empty($topCourses)): ?>
                        <?php foreach ($topCourses as $index => $course): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500"><?= $index + 1 ?></td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($course['course_title']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($course['instructor_name'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        <?= htmlspecialchars(number_format($course['enrollment_count'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <?= htmlspecialchars(number_format($course['completion_count'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: <?= min(100, (float)$course['avg_progress']) ?>%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600"><?= htmlspecialchars($course['avg_progress'] ?? '0') ?>%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                    <?= htmlspecialchars($currency) ?> <?= htmlspecialchars(number_format($course['revenue'], 2)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                                <p>No course data available</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- Student Progress Overview -->
    <!-- ================================================== -->
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-tasks text-indigo-500 mr-2"></i>Student Progress Overview
            </h3>
        </div>
        <div class="p-6">
            <?php if (!empty($progressDistribution)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <?php foreach ($progressDistribution as $pd): ?>
                        <?php
                            $range = $pd['progress_range'] ?? 'Unknown';
                            $colors = $progressColors[$range] ?? ['bg' => 'bg-gray-500', 'light' => 'bg-gray-100', 'text' => 'text-gray-700'];
                        ?>
                        <div class="<?= $colors['light'] ?> rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold <?= $colors['text'] ?>"><?= htmlspecialchars(number_format($pd['student_count'])) ?></p>
                            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($range) ?> progress</p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Visual bar representation -->
                <div class="space-y-3">
                    <?php foreach ($progressDistribution as $pd): ?>
                        <?php
                            $range = $pd['progress_range'] ?? 'Unknown';
                            $colors = $progressColors[$range] ?? ['bg' => 'bg-gray-500', 'light' => 'bg-gray-100', 'text' => 'text-gray-700'];
                            $barWidth = ($maxStudentCount > 0) ? ((int)$pd['student_count'] / $maxStudentCount) * 100 : 0;
                        ?>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600 w-20 text-right"><?= htmlspecialchars($range) ?></span>
                            <div class="flex-1 bg-gray-100 rounded-full h-5">
                                <div class="<?= $colors['bg'] ?> h-5 rounded-full flex items-center justify-end pr-2" style="width: <?= max(5, $barWidth) ?>%">
                                    <span class="text-xs text-white font-medium"><?= htmlspecialchars($pd['student_count']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                    <p>No enrollment progress data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- Revenue by Course -->
    <!-- ================================================== -->
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>Revenue by Course
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total Payments</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Completed Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pending Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (!empty($revenueByCourse)): ?>
                        <?php foreach ($revenueByCourse as $index => $rc): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500"><?= $index + 1 ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800"><?= htmlspecialchars($rc['course_name']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        <?= htmlspecialchars(number_format($rc['total_payments'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-green-600">
                                    <?= htmlspecialchars($currency) ?> <?= htmlspecialchars(number_format($rc['completed_revenue'], 2)) ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-yellow-600">
                                    <?= htmlspecialchars($currency) ?> <?= htmlspecialchars(number_format($rc['pending_revenue'], 2)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                                <p>No revenue data available</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- Instructor Performance -->
    <!-- ================================================== -->
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-chalkboard-teacher text-purple-500 mr-2"></i>Instructor Performance
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Instructor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Courses Taught</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total Students</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Avg Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (!empty($instructorPerformance)): ?>
                        <?php foreach ($instructorPerformance as $inst): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-sm">
                                            <?= htmlspecialchars(strtoupper(substr($inst['instructor_name'] ?? 'U', 0, 1))) ?>
                                        </div>
                                        <span class="text-sm font-medium text-gray-800"><?= htmlspecialchars($inst['instructor_name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars(number_format($inst['courses_taught'])) ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        <?= htmlspecialchars(number_format($inst['total_students'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($inst['avg_rating']): ?>
                                        <div class="flex items-center gap-1">
                                            <i class="fas fa-star text-yellow-400 text-xs"></i>
                                            <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($inst['avg_rating']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">No ratings</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                    <?= htmlspecialchars($currency) ?> <?= htmlspecialchars(number_format($inst['total_revenue'], 2)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                                <p>No instructor data available</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- Recent Activity Log -->
    <!-- ================================================== -->
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-history text-gray-500 mr-2"></i>Recent Activity Log
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (!empty($recentActivities)): ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <?php
                                $actionColors = [
                                    'login' => 'bg-blue-100 text-blue-700',
                                    'logout' => 'bg-gray-100 text-gray-700',
                                    'lesson_view' => 'bg-indigo-100 text-indigo-700',
                                    'lesson_complete' => 'bg-green-100 text-green-700',
                                    'assignment_submit' => 'bg-purple-100 text-purple-700',
                                    'grade_assignment' => 'bg-yellow-100 text-yellow-700',
                                    'discussion_post' => 'bg-teal-100 text-teal-700',
                                    'enrollment' => 'bg-cyan-100 text-cyan-700',
                                    'payment' => 'bg-emerald-100 text-emerald-700',
                                ];
                                $actionClass = $actionColors[$activity['activity_type']] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-xs">
                                            <?= htmlspecialchars(strtoupper(substr($activity['user_name'] ?? 'U', 0, 1))) ?>
                                        </div>
                                        <span class="text-sm font-medium text-gray-800"><?= htmlspecialchars($activity['user_name'] ?? 'Unknown') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $actionClass ?>">
                                        <?= htmlspecialchars(str_replace('_', ' ', ucfirst($activity['activity_type'] ?? 'N/A'))) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-md truncate">
                                    <?= htmlspecialchars($activity['description'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                    <?= htmlspecialchars(date('M j, Y H:i', strtotime($activity['created_at']))) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                                <p>No recent activity found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
