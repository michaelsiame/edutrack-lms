<?php
/**
 * Instructor - Analytics Dashboard
 * Enhanced course performance and student engagement metrics
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Statistics.php';

$db = Database::getInstance();
$userId = currentUserId();

// Get instructor ID
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : $userId;

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
    GROUP BY c.id
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

// Calculate revenue
$revenueData = $db->fetchOne("
    SELECT
        COALESCE(SUM(c.price), 0) as total_revenue,
        COALESCE(SUM(CASE WHEN e.enrolled_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN c.price ELSE 0 END), 0) as monthly_revenue
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ? AND e.payment_status = 'completed'
", [$instructorId]);

// Get top performing students
$topStudents = $db->fetchAll("
    SELECT u.first_name, u.last_name, u.email, u.avatar_url,
           COUNT(DISTINCT e.id) as courses_enrolled,
           AVG(e.progress) as avg_progress,
           SUM(CASE WHEN e.enrollment_status = 'Completed' THEN 1 ELSE 0 END) as courses_completed
    FROM users u
    JOIN enrollments e ON u.id = e.user_id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    GROUP BY u.id
    HAVING avg_progress > 50
    ORDER BY avg_progress DESC, courses_completed DESC
    LIMIT 5
", [$instructorId]);

// Get recent activity
$recentActivity = $db->fetchAll("
    (SELECT 'enrollment' as type, e.enrolled_at as activity_date, 
            u.first_name, u.last_name, c.title as course_title, NULL as score
     FROM enrollments e
     JOIN users u ON e.user_id = u.id
     JOIN courses c ON e.course_id = c.id
     WHERE c.instructor_id = ?)
    UNION ALL
    (SELECT 'completion' as type, e.completion_date as activity_date,
            u.first_name, u.last_name, c.title as course_title, NULL as score
     FROM enrollments e
     JOIN users u ON e.user_id = u.id
     JOIN courses c ON e.course_id = c.id
     WHERE c.instructor_id = ? AND e.completion_date IS NOT NULL)
    UNION ALL
    (SELECT 'quiz' as type, qa.completed_at as activity_date,
            u.first_name, u.last_name, q.title as course_title, qa.score
     FROM quiz_attempts qa
     JOIN students st ON qa.student_id = st.id
     JOIN users u ON st.user_id = u.id
     JOIN quizzes q ON qa.quiz_id = q.id
     JOIN lessons l ON q.lesson_id = l.id
     JOIN modules m ON l.module_id = m.id
     JOIN courses c ON m.course_id = c.id
     WHERE c.instructor_id = ? AND qa.status = 'completed')
    ORDER BY activity_date DESC
    LIMIT 10
", [$instructorId, $instructorId, $instructorId]);

$page_title = 'Analytics';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
                <p class="text-gray-500 mt-1">Track your course performance and student engagement</p>
            </div>
            <div class="mt-4 md:mt-0">
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
                    <i class="fas fa-print mr-2"></i>Print Report
                </button>
            </div>
        </div>

        <!-- Overview Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Courses</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_courses'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-book text-blue-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Students</p>
                        <p class="text-2xl font-bold text-purple-600"><?= $stats['total_students'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-purple-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Revenue</p>
                        <p class="text-2xl font-bold text-green-600">K<?= number_format($revenueData['total_revenue'] ?? 0, 0) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet text-green-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Avg Rating</p>
                        <p class="text-2xl font-bold text-yellow-600"><?= number_format($stats['avg_rating'] ?? 0, 1) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-star text-yellow-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="xl:col-span-2 space-y-8">
                
                <!-- Enrollment Trend Chart -->
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Enrollment Trend (6 Months)</h2>
                    <div class="h-64">
                        <canvas id="enrollmentChart"></canvas>
                    </div>
                </div>

                <!-- Course Performance Table -->
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-900">Course Performance</h2>
                    </div>

                    <?php if (empty($courseMetrics)): ?>
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-bar text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Courses Yet</h3>
                        <p class="text-gray-500">Create your first course to see analytics.</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Course</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Enrollments</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Completions</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Progress</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Rating</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($courseMetrics as $course): ?>
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4">
                                        <a href="<?= url('instructor/course-edit.php?id=' . $course['id']) ?>" 
                                           class="font-medium text-primary-600 hover:text-primary-700">
                                            <?= htmlspecialchars($course['title']) ?>
                                        </a>
                                        <span class="ml-2 px-2 py-0.5 rounded text-xs <?= $course['status'] == 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                            <?= ucfirst($course['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold"><?= $course['total_enrollments'] ?></td>
                                    <td class="px-6 py-4">
                                        <span class="text-green-600 font-medium"><?= $course['completions'] ?></span>
                                        <?php if ($course['total_enrollments'] > 0): ?>
                                        <span class="text-xs text-gray-400 ml-1">
                                            (<?= round(($course['completions'] / $course['total_enrollments']) * 100) ?>%)
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-primary-500 h-2 rounded-full" style="width: <?= round($course['avg_progress'] ?? 0) ?>"></div>
                                            </div>
                                            <span class="text-sm"><?= round($course['avg_progress'] ?? 0) ?>%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($course['review_count'] > 0): ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                                <span class="font-medium"><?= number_format($course['avg_rating'], 1) ?></span>
                                                <span class="text-gray-400 text-sm ml-1">(<?= $course['review_count'] ?>)</span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">No reviews</span>
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

            <!-- Sidebar -->
            <div class="space-y-8">
                
                <!-- Performance Summary -->
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Performance Summary</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mr-3">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Published Courses</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900"><?= $stats['published_courses'] ?? 0 ?></span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-purple-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mr-3">
                                    <i class="fas fa-graduation-cap text-purple-600"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Total Enrollments</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900"><?= $stats['total_enrollments'] ?? 0 ?></span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-3">
                                    <i class="fas fa-trophy text-blue-600"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Completions</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900"><?= $stats['completed_enrollments'] ?? 0 ?></span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-orange-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center mr-3">
                                    <i class="fas fa-money-bill-wave text-orange-600"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Monthly Revenue</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">K<?= number_format($revenueData['monthly_revenue'] ?? 0, 0) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Top Students -->
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Top Students</h3>
                    </div>
                    <?php if (empty($topStudents)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-500 text-sm">No student data yet</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($topStudents as $student): ?>
                        <div class="p-4 hover:bg-gray-50/50 transition">
                            <div class="flex items-center gap-3">
                                <img src="<?= getGravatar($student['email']) ?>" class="w-10 h-10 rounded-full">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 text-sm truncate">
                                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500"><?= $student['courses_completed'] ?> completed</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-bold text-primary-600"><?= round($student['avg_progress']) ?>%</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Recent Activity</h3>
                    </div>
                    <?php if (empty($recentActivity)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-500 text-sm">No recent activity</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                        <?php foreach ($recentActivity as $activity): 
                            $icons = [
                                'enrollment' => ['fa-user-plus', 'text-blue-500', 'bg-blue-100'],
                                'completion' => ['fa-graduation-cap', 'text-green-500', 'bg-green-100'],
                                'quiz' => ['fa-question-circle', 'text-purple-500', 'bg-purple-100']
                            ];
                            $icon = $icons[$activity['type']] ?? $icons['enrollment'];
                        ?>
                        <div class="p-4 hover:bg-gray-50/50 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 <?= $icon[2] ?> rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas <?= $icon[0] ?> <?= $icon[1] ?> text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium"><?= htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) ?></span>
                                        <?php if ($activity['type'] === 'enrollment'): ?>
                                            enrolled in <span class="font-medium"><?= htmlspecialchars($activity['course_title']) ?></span>
                                        <?php elseif ($activity['type'] === 'completion'): ?>
                                            completed <span class="font-medium"><?= htmlspecialchars($activity['course_title']) ?></span>
                                        <?php elseif ($activity['type'] === 'quiz'): ?>
                                            scored <?= round($activity['score'], 1) ?>% on <span class="font-medium"><?= htmlspecialchars($activity['course_title']) ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1"><?= timeAgo($activity['activity_date']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('enrollmentChart');
    if (!ctx) return;

    const enrollmentData = <?= json_encode($enrollmentTrend) ?>;

    if (enrollmentData.length === 0) {
        ctx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No enrollment data available</div>';
        return;
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: enrollmentData.map(d => {
                const [year, month] = d.month.split('-');
                return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Enrollments',
                data: enrollmentData.map(d => d.enrollments),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1F2937',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { stepSize: 1 },
                    grid: { color: '#F3F4F6' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
