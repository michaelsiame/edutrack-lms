<?php
/**
 * Student Schedule / Learning Calendar
 * Shows upcoming assignments, lessons, live sessions, and study schedule
 */

require_once '../../src/bootstrap.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = User::current();
$userId = $user->getId();

// Get current month/year from query params or use current
$month = (int)($_GET['month'] ?? date('n'));
$year = (int)($_GET['year'] ?? date('Y'));

// Ensure valid month/year
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

// Calendar calculations
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$startingDay = date('w', $firstDayOfMonth); // 0 = Sunday
$monthName = date('F', $firstDayOfMonth);

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

// Get upcoming assignments
$upcomingAssignments = $db->fetchAll("
    SELECT a.*, c.title as course_title, c.slug as course_slug,
           DATEDIFF(a.due_date, CURDATE()) as days_until_due
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.user_id = ?
    AND MONTH(a.due_date) = ? AND YEAR(a.due_date) = ?
    AND a.id NOT IN (
        SELECT assignment_id FROM assignment_submissions WHERE student_id = e.student_id
    )
    ORDER BY a.due_date ASC
", [$userId, $month, $year]);

// Get live sessions
$liveSessions = $db->fetchAll("
    SELECT ls.*, l.title as lesson_title, c.title as course_title, c.slug as course_slug,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name
    FROM live_sessions ls
    JOIN lessons l ON ls.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id
    JOIN instructors i ON ls.instructor_id = i.id
    JOIN users u ON i.user_id = u.id
    WHERE e.user_id = ?
    AND MONTH(ls.scheduled_start_time) = ? AND YEAR(ls.scheduled_start_time) = ?
    AND ls.status IN ('scheduled', 'live', 'in_progress')
    ORDER BY ls.scheduled_start_time ASC
", [$userId, $month, $year]);

// Get enrolled course start/end dates
$courseDates = $db->fetchAll("
    SELECT c.title, c.start_date, c.end_date, c.slug
    FROM courses c
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.user_id = ?
    AND (MONTH(c.start_date) = ? OR MONTH(c.end_date) = ?)
    AND (YEAR(c.start_date) = ? OR YEAR(c.end_date) = ?)
", [$userId, $month, $month, $year, $year]);

// Group events by date
$eventsByDate = [];

foreach ($upcomingAssignments as $assignment) {
    $date = date('Y-m-d', strtotime($assignment['due_date']));
    $eventsByDate[$date][] = [
        'type' => 'assignment',
        'title' => $assignment['title'],
        'course' => $assignment['course_title'],
        'time' => date('g:i A', strtotime($assignment['due_date'])),
        'urgent' => $assignment['days_until_due'] <= 2,
        'data' => $assignment
    ];
}

foreach ($liveSessions as $session) {
    $date = date('Y-m-d', strtotime($session['scheduled_start_time']));
    $eventsByDate[$date][] = [
        'type' => 'live_session',
        'title' => $session['lesson_title'],
        'course' => $session['course_title'],
        'time' => date('g:i A', strtotime($session['scheduled_start_time'])),
        'duration' => $session['duration_minutes'],
        'instructor' => $session['instructor_name'],
        'status' => $session['status'],
        'data' => $session
    ];
}

// Today's upcoming items
$today = date('Y-m-d');
$todayEvents = $eventsByDate[$today] ?? [];

// Upcoming week items
$weekEvents = [];
for ($i = 1; $i <= 7; $i++) {
    $date = date('Y-m-d', strtotime("+$i days"));
    if (isset($eventsByDate[$date])) {
        foreach ($eventsByDate[$date] as $event) {
            $event['date'] = $date;
            $weekEvents[] = $event;
        }
    }
}

$page_title = "My Schedule - Edutrack";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Schedule</h1>
                <p class="text-gray-600 mt-2">Stay on track with your learning journey</p>
            </div>
            <a href="<?= url('student/assignments.php') ?>" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-tasks mr-2"></i>View All Assignments
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Calendar Column -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <!-- Calendar Header -->
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800"><?= $monthName ?> <?= $year ?></h2>
                        <div class="flex items-center gap-2">
                            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" 
                               class="p-2 hover:bg-gray-100 rounded-lg transition">
                                <i class="fas fa-chevron-left text-gray-600"></i>
                            </a>
                            <a href="?month=<?= date('n') ?>&year=<?= date('Y') ?>" 
                               class="px-3 py-1.5 text-sm font-medium bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                                Today
                            </a>
                            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" 
                               class="p-2 hover:bg-gray-100 rounded-lg transition">
                                <i class="fas fa-chevron-right text-gray-600"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="p-6">
                        <!-- Day Headers -->
                        <div class="grid grid-cols-7 gap-1 mb-2">
                            <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day): ?>
                            <div class="text-center text-sm font-medium text-gray-500 py-2">
                                <?= $day ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Calendar Days -->
                        <div class="grid grid-cols-7 gap-1">
                            <?php
                            // Empty cells for days before month starts
                            for ($i = 0; $i < $startingDay; $i++) {
                                echo '<div class="h-24 bg-gray-50 rounded-lg"></div>';
                            }

                            // Days of the month
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $isToday = $date === date('Y-m-d');
                                $hasEvents = isset($eventsByDate[$date]);
                                $events = $eventsByDate[$date] ?? [];
                                
                                $assignmentCount = count(array_filter($events, fn($e) => $e['type'] === 'assignment'));
                                $sessionCount = count(array_filter($events, fn($e) => $e['type'] === 'live_session'));
                                $hasUrgent = count(array_filter($events, fn($e) => ($e['type'] === 'assignment' && ($e['urgent'] ?? false))));
                            ?>
                            <div class="h-24 border rounded-lg p-1 <?= $isToday ? 'bg-blue-50 border-blue-300' : 'bg-white border-gray-100 hover:bg-gray-50' ?>">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium <?= $isToday ? 'text-blue-700' : 'text-gray-700' ?>">
                                        <?= $day ?>
                                    </span>
                                    <?php if ($isToday): ?>
                                    <span class="text-xs bg-blue-600 text-white px-1.5 py-0.5 rounded">Today</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($hasEvents): ?>
                                <div class="space-y-1">
                                    <?php if ($assignmentCount > 0): ?>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 rounded-full <?= $hasUrgent ? 'bg-red-500' : 'bg-orange-500' ?>"></div>
                                        <span class="text-xs text-gray-600 truncate"><?= $assignmentCount ?> assignment<?= $assignmentCount > 1 ? 's' : '' ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($sessionCount > 0): ?>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                                        <span class="text-xs text-gray-600 truncate"><?= $sessionCount ?> session<?= $sessionCount > 1 ? 's' : '' ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                            <span class="text-gray-600">Assignment Due</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <span class="text-gray-600">Urgent (< 2 days)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                            <span class="text-gray-600">Live Session</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="space-y-6">
                
                <!-- Today's Schedule -->
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">Today's Schedule</h3>
                    </div>
                    <div class="p-4">
                        <?php if (!empty($todayEvents)): ?>
                        <div class="space-y-3">
                            <?php foreach ($todayEvents as $event): ?>
                            <div class="flex items-start gap-3 p-3 rounded-lg <?= $event['type'] === 'assignment' ? ($event['urgent'] ? 'bg-red-50' : 'bg-orange-50') : 'bg-purple-50' ?>">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0
                                    <?= $event['type'] === 'assignment' ? ($event['urgent'] ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600') : 'bg-purple-100 text-purple-600' ?>">
                                    <i class="fas <?= $event['type'] === 'assignment' ? 'fa-file-alt' : 'fa-video' ?>"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800 text-sm truncate"><?= sanitize($event['title']) ?></p>
                                    <p class="text-xs text-gray-500"><?= sanitize($event['course']) ?></p>
                                    <p class="text-xs <?= $event['type'] === 'assignment' && $event['urgent'] ? 'text-red-600 font-medium' : 'text-gray-400' ?>">
                                        <?= $event['time'] ?>
                                        <?php if ($event['type'] === 'assignment' && $event['urgent']): ?>
                                        • Due Soon!
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-6">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <p class="text-gray-600 text-sm">No tasks due today!</p>
                            <p class="text-gray-400 text-xs mt-1">Enjoy your free time</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upcoming Week -->
                <?php if (!empty($weekEvents)): ?>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">This Week</h3>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                        <?php 
                        $displayed = 0;
                        foreach ($weekEvents as $event): 
                            if ($displayed >= 5) break;
                            $displayed++;
                            $dayName = date('D', strtotime($event['date']));
                            $dayNum = date('j', strtotime($event['date']));
                        ?>
                        <div class="px-5 py-3 flex items-center hover:bg-gray-50">
                            <div class="w-12 text-center mr-4">
                                <p class="text-xs text-gray-500 uppercase"><?= $dayName ?></p>
                                <p class="text-lg font-bold text-gray-800"><?= $dayNum ?></p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 text-sm truncate"><?= sanitize($event['title']) ?></p>
                                <p class="text-xs text-gray-500"><?= $event['time'] ?></p>
                            </div>
                            <div class="w-2 h-2 rounded-full <?= $event['type'] === 'assignment' ? 'bg-orange-500' : 'bg-purple-500' ?> ml-2"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($weekEvents) > 5): ?>
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 text-center">
                        <span class="text-sm text-gray-500">+<?= count($weekEvents) - 5 ?> more events</span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">This Month</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-alt text-orange-600"></i>
                                </div>
                                <span class="text-gray-600">Assignments Due</span>
                            </div>
                            <span class="font-bold text-gray-800"><?= count($upcomingAssignments) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-video text-purple-600"></i>
                                </div>
                                <span class="text-gray-600">Live Sessions</span>
                            </div>
                            <span class="font-bold text-gray-800"><?= count($liveSessions) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Study Tips -->
                <div class="bg-blue-500 rounded-xl shadow-sm overflow-hidden text-white p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-lightbulb text-xl"></i>
                        </div>
                        <h3 class="font-bold">Study Tip</h3>
                    </div>
                    <p class="text-blue-100 text-sm leading-relaxed">
                        Break your study sessions into 25-minute focused intervals (Pomodoro technique) with 5-minute breaks in between. This helps maintain concentration and prevents burnout.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../src/templates/footer.php'; ?>
