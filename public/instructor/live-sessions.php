<?php
/**
 * Instructor - Live Sessions Management
 * Modern live class scheduling and management
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/User.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Lesson.php';
require_once '../../src/classes/LiveSession.php';

$db = Database::getInstance();
$user = User::current();
if (!$user) {
    redirect(url('login.php'));
    exit;
}

$userId = $user->getId();

// Get instructor ID
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' && $instructorId) {
        // Combine date and time
        $scheduledDate = $_POST['scheduled_date'] ?? '';
        $scheduledTime = $_POST['scheduled_time'] ?? '';
        
        if ($scheduledDate && $scheduledTime) {
            $data = [
                'lesson_id' => $_POST['lesson_id'] ?? null,
                'instructor_id' => $instructorId,
                'scheduled_start_time' => "$scheduledDate $scheduledTime:00",
                'duration_minutes' => $_POST['duration_minutes'] ?? 60,
                'description' => $_POST['description'] ?? '',
                'max_participants' => $_POST['max_participants'] ?: null,
                'allow_recording' => isset($_POST['allow_recording']) ? 1 : 0,
                'enable_chat' => isset($_POST['enable_chat']) ? 1 : 0,
                'enable_screen_share' => isset($_POST['enable_screen_share']) ? 1 : 0
            ];
            
            $sessionId = LiveSession::create($data);
            if ($sessionId) {
                flash('message', 'Live session scheduled successfully!', 'success');
            } else {
                flash('message', 'Failed to schedule session', 'error');
            }
        }
    }
    
    if ($action === 'cancel' && isset($_POST['session_id'])) {
        $session = LiveSession::find($_POST['session_id']);
        if ($session && $session->update(['status' => 'cancelled'])) {
            flash('message', 'Session cancelled successfully', 'success');
        } else {
            flash('message', 'Failed to cancel session', 'error');
        }
    }
    
    redirect($_SERVER['REQUEST_URI']);
}

// Get instructor's live sessions
$sessions = $instructorId ? LiveSession::getByInstructor($instructorId) : [];
$upcomingSessions = $instructorId ? LiveSession::getByInstructor($instructorId, 'scheduled') : [];

// Get instructor's courses for creating new sessions
$courses = [];
if ($instructorId) {
    $courses = $db->fetchAll("
        SELECT c.* FROM courses c
        WHERE c.instructor_id = ? OR c.instructor_id = ?
        AND c.status = 'published'
        ORDER BY c.title ASC
    ", [$instructorId, $userId]);
}

// Group sessions by status
$groupedSessions = [
    'live' => [],
    'scheduled' => [],
    'ended' => [],
    'cancelled' => []
];

foreach ($sessions as $session) {
    $groupedSessions[$session['status']][] = $session;
}

$page_title = 'Live Sessions';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Live Sessions</h1>
                <p class="text-gray-500 mt-1">Schedule and manage your virtual classes</p>
            </div>
            <div class="mt-4 md:mt-0">
                <button onclick="openModal('createModal')" 
                        class="inline-flex items-center px-5 py-2.5 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-500/30">
                    <i class="fas fa-video mr-2"></i>Schedule New Session
                </button>
            </div>
        </div>

        <!-- Live Now Banner -->
        <?php if (!empty($groupedSessions['live'])): ?>
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-2xl p-6 text-white mb-8 shadow-lg shadow-red-500/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mr-4 animate-pulse">
                        <i class="fas fa-broadcast-tower text-3xl"></i>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 bg-white/20 rounded-full text-sm font-medium mb-2">
                            <span class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></span>
                            LIVE NOW
                        </span>
                        <h2 class="text-xl font-bold"><?= htmlspecialchars($groupedSessions['live'][0]['lesson_title']) ?></h2>
                        <p class="text-white/80"><?= htmlspecialchars($groupedSessions['live'][0]['course_title']) ?></p>
                    </div>
                </div>
                <a href="<?= url('live-session.php?session_id=' . $groupedSessions['live'][0]['id']) ?>" 
                   target="_blank"
                   class="px-6 py-3 bg-white text-red-600 font-bold rounded-xl hover:bg-gray-100 transition">
                    <i class="fas fa-play mr-2"></i>Join Session
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upcoming Sessions Alert -->
        <?php if (!empty($upcomingSessions)): ?>
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-8">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900">Upcoming Sessions</h3>
                    <p class="text-blue-700">You have <?= count($upcomingSessions) ?> session(s) scheduled</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sessions Grid -->
        <?php if (empty($sessions)): ?>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-video text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Live Sessions Yet</h3>
            <p class="text-gray-500 mb-6">Schedule your first live session to start teaching in real-time!</p>
            <button onclick="openModal('createModal')" 
                    class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-500/30">
                <i class="fas fa-plus mr-2"></i>Schedule Your First Session
            </button>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php foreach ($sessions as $session): 
                $startTime = new DateTime($session['scheduled_start_time']);
                $endTime = new DateTime($session['scheduled_end_time']);
                $now = new DateTime();
                $isLive = $session['status'] === 'live';
                $isUpcoming = $session['status'] === 'scheduled';
                $isPast = $session['status'] === 'ended';
                $isCancelled = $session['status'] === 'cancelled';
                
                $statusColors = [
                    'live' => 'bg-red-100 text-red-700 border-red-200',
                    'scheduled' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'ended' => 'bg-gray-100 text-gray-700 border-gray-200',
                    'cancelled' => 'bg-red-50 text-red-600 border-red-100'
                ];
            ?>
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden hover:shadow-card-hover transition <?= $isCancelled ? 'opacity-60' : '' ?>">
                <!-- Header with Status -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-xs font-medium border <?= $statusColors[$session['status']] ?>">
                        <?= $isLive ? '● LIVE' : ucfirst($session['status']) ?>
                    </span>
                    <?php if (!$isCancelled && !$isPast): ?>
                    <div class="text-sm text-gray-500">
                        <i class="far fa-clock mr-1"></i>
                        <?= $startTime->format('g:i A') ?> - <?= $endTime->format('g:i A') ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="p-6">
                    <!-- Date Badge -->
                    <div class="flex items-start gap-4 mb-4">
                        <div class="flex-shrink-0 text-center bg-gray-50 rounded-xl p-3 min-w-[4rem]">
                            <div class="text-xs font-bold text-primary-600 uppercase"><?= $startTime->format('M') ?></div>
                            <div class="text-2xl font-bold text-gray-900"><?= $startTime->format('j') ?></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 text-lg mb-1 truncate"><?= htmlspecialchars($session['lesson_title']) ?></h3>
                            <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($session['course_title']) ?></p>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-hourglass-half w-5 text-gray-400"></i>
                            <span><?= $session['duration_minutes'] ?> minutes</span>
                        </div>
                        <?php if ($session['max_participants']): ?>
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-users w-5 text-gray-400"></i>
                            <span>Max <?= $session['max_participants'] ?> participants</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($session['description']): ?>
                        <p class="text-sm text-gray-600 mt-3 line-clamp-2"><?= htmlspecialchars($session['description']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-4 border-t border-gray-100">
                        <?php if ($isLive || $isUpcoming): ?>
                        <a href="<?= url('live-session.php?session_id=' . $session['id']) ?>" 
                           target="_blank"
                           class="flex-1 px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition text-sm font-medium text-center <?= $isUpcoming && $startTime > $now ? 'opacity-50 cursor-not-allowed' : '' ?>">
                            <i class="fas fa-video mr-1"></i><?= $isLive ? 'Join' : 'Start' ?>
                        </a>
                        <?php endif; ?>
                        
                        <button onclick="viewDetails(<?= $session['id'] ?>)" 
                                class="px-4 py-2 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition text-sm font-medium">
                            <i class="fas fa-eye"></i>
                        </button>
                        
                        <?php if ($isUpcoming): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('Cancel this session?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                            <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-xl hover:bg-red-50 transition text-sm font-medium">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Create Session Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
            <h3 class="text-lg font-bold text-gray-900">Schedule New Live Session</h3>
            <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form method="POST" class="p-6 space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Course *</label>
                <select name="course_id" required onchange="loadLessons(this.value)"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                    <option value="">Select a course</option>
                    <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lesson *</label>
                <select name="lesson_id" id="lesson_id" required disabled
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 disabled:opacity-50">
                    <option value="">Select a course first</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <input type="date" name="scheduled_date" required min="<?= date('Y-m-d') ?>"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Time *</label>
                    <input type="time" name="scheduled_time" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Duration *</label>
                <select name="duration_minutes" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                    <option value="30">30 minutes</option>
                    <option value="45">45 minutes</option>
                    <option value="60" selected>1 hour</option>
                    <option value="90">1.5 hours</option>
                    <option value="120">2 hours</option>
                    <option value="180">3 hours</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3" placeholder="What will be covered in this session?"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Max Participants</label>
                <input type="number" name="max_participants" min="1"
                       placeholder="Leave empty for unlimited"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
            </div>

            <!-- Options -->
            <div class="space-y-3 pt-2">
                <label class="flex items-center">
                    <input type="checkbox" name="allow_recording" checked class="w-4 h-4 text-primary-600 rounded border-gray-300">
                    <span class="ml-2 text-sm text-gray-700">Allow session recording</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="enable_chat" checked class="w-4 h-4 text-primary-600 rounded border-gray-300">
                    <span class="ml-2 text-sm text-gray-700">Enable chat</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="enable_screen_share" checked class="w-4 h-4 text-primary-600 rounded border-gray-300">
                    <span class="ml-2 text-sm text-gray-700">Enable screen sharing</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal('createModal')" 
                        class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 font-medium transition">
                    <i class="fas fa-calendar-plus mr-2"></i>Schedule Session
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Session Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
            <h3 class="text-lg font-bold text-gray-900">Session Details</h3>
            <button onclick="closeModal('detailsModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="detailsContent" class="p-6">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<script>
const sessions = <?= json_encode($sessions) ?>;

async function loadLessons(courseId) {
    const lessonSelect = document.getElementById('lesson_id');
    
    if (!courseId) {
        lessonSelect.disabled = true;
        lessonSelect.innerHTML = '<option value="">Select a course first</option>';
        return;
    }
    
    lessonSelect.disabled = true;
    lessonSelect.innerHTML = '<option value="">Loading...</option>';
    
    try {
        const response = await fetch(`<?= url('api/lessons.php') ?>?course_id=${courseId}`);
        const data = await response.json();
        
        if (data.success && data.lessons) {
            lessonSelect.innerHTML = '<option value="">Select a lesson</option>';
            data.lessons.forEach(lesson => {
                const option = document.createElement('option');
                option.value = lesson.id;
                option.textContent = lesson.title;
                lessonSelect.appendChild(option);
            });
            lessonSelect.disabled = false;
        } else {
            lessonSelect.innerHTML = '<option value="">No lessons found</option>';
        }
    } catch (error) {
        console.error('Error loading lessons:', error);
        lessonSelect.innerHTML = '<option value="">Error loading lessons</option>';
    }
}

function viewDetails(sessionId) {
    const session = sessions.find(s => s.id == sessionId);
    if (!session) return;
    
    const startTime = new Date(session.scheduled_start_time);
    
    document.getElementById('detailsContent').innerHTML = `
        <div class="space-y-4">
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-sm text-gray-500 mb-1">Lesson</p>
                <p class="font-semibold text-gray-900">${escapeHtml(session.lesson_title)}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-sm text-gray-500 mb-1">Course</p>
                <p class="font-semibold text-gray-900">${escapeHtml(session.course_title)}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm text-gray-500 mb-1">Scheduled</p>
                    <p class="font-semibold text-gray-900">${startTime.toLocaleString()}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm text-gray-500 mb-1">Duration</p>
                    <p class="font-semibold text-gray-900">${session.duration_minutes} minutes</p>
                </div>
            </div>
            ${session.meeting_url ? `
            <div>
                <p class="text-sm text-gray-500 mb-2">Meeting Link</p>
                <div class="flex gap-2">
                    <input type="text" value="${session.meeting_url}" readonly 
                           class="flex-1 px-4 py-2 bg-gray-100 rounded-xl text-sm" onclick="this.select()">
                    <button onclick="navigator.clipboard.writeText('${session.meeting_url}'); showToast('Copied to clipboard', 'success');" 
                            class="px-4 py-2 bg-primary-100 text-primary-600 rounded-xl hover:bg-primary-200 transition">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    openModal('detailsModal');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
