<?php
/**
 * Instructor Live Sessions Management
 */

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/User.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Lesson.php';
require_once '../../src/classes/LiveSession.php';

$user = User::current();
if (!$user) {
    redirect(url('login.php'));
    exit;
}

$userId = $user->getId();

// Get instructor ID from instructors table
$db = Database::getInstance();
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : null;

// Handle different views
$view = $_GET['view'] ?? 'list';
$sessionId = $_GET['session_id'] ?? null;
$lessonId = $_GET['lesson_id'] ?? null;

// Get instructor's live sessions
if ($instructorId) {
    $sessions = LiveSession::getByInstructor($instructorId);
    $upcomingSessions = LiveSession::getByInstructor($instructorId, 'scheduled');
} else {
    $sessions = [];
    $upcomingSessions = [];
}

// Get instructor's courses for creating new sessions
if ($instructorId) {
    $courses = $db->fetchAll("
        SELECT c.*
        FROM courses c
        WHERE c.instructor_id = ? OR c.instructor_id = ?
        ORDER BY c.title ASC
    ", [$instructorId, $userId]);
} else {
    $courses = [];
}

$pageTitle = 'Live Sessions Management';
require_once '../../src/templates/instructor-header.php';
?>

<style>
.session-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    background: white;
    transition: box-shadow 0.2s;
}

.session-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.session-status {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-scheduled { background: #dbeafe; color: #1e40af; }
.status-live { background: #dcfce7; color: #166534; animation: pulse 2s infinite; }
.status-ended { background: #f3f4f6; color: #6b7280; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-primary {
    background: #2E70DA;
    color: white;
}

.btn-primary:hover {
    background: #1E4A8A;
}

.btn-success {
    background: #10B981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-danger {
    background: #EF4444;
    color: white;
}

.btn-danger:hover {
    background: #DC2626;
}

.btn-secondary {
    background: #6B7280;
    color: white;
}

.btn-secondary:hover {
    background: #4B5563;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #2E70DA;
    box-shadow: 0 0 0 3px rgba(46, 112, 218, 0.1);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 2rem;
    border-radius: 0.5rem;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    margin-bottom: 1.5rem;
}

.modal-header h2 {
    margin: 0;
    color: #111827;
}

.close {
    float: right;
    font-size: 1.5rem;
    font-weight: bold;
    color: #6b7280;
    cursor: pointer;
}

.close:hover {
    color: #111827;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f3f4f6;
}

.info-label {
    font-weight: 500;
    color: #6b7280;
}

.info-value {
    color: #111827;
}

.alert {
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.alert-info {
    background: #dbeafe;
    color: #1e40af;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
}

.alert-warning {
    background: #fef3c7;
    color: #92400e;
}
</style>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Live Sessions</h1>
                <button class="btn btn-primary" onclick="showCreateModal()">
                    <i class="fas fa-plus"></i> Schedule New Live Session
                </button>
            </div>

            <?php if (!empty($upcomingSessions)): ?>
            <div class="alert alert-info">
                <strong><i class="fas fa-info-circle"></i> Upcoming Sessions:</strong>
                You have <?php echo count($upcomingSessions); ?> upcoming live session(s) scheduled.
            </div>
            <?php endif; ?>

            <!-- Sessions List -->
            <div class="row">
                <?php if (empty($sessions)): ?>
                <div class="col-12">
                    <div class="session-card text-center">
                        <i class="fas fa-video fa-3x text-muted mb-3"></i>
                        <h4>No Live Sessions Yet</h4>
                        <p class="text-muted">Schedule your first live session to start teaching in real-time!</p>
                        <button class="btn btn-primary" onclick="showCreateModal()">
                            Schedule Your First Live Session
                        </button>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                    <?php
                    $startTime = new DateTime($session['scheduled_start_time']);
                    $endTime = new DateTime($session['scheduled_end_time']);
                    $now = new DateTime();
                    $isUpcoming = $startTime > $now;
                    $isPast = $endTime < $now;
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="session-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="mb-0"><?php echo htmlspecialchars($session['lesson_title']); ?></h5>
                                <span class="session-status status-<?php echo $session['status']; ?>">
                                    <?php echo ucfirst($session['status']); ?>
                                </span>
                            </div>

                            <p class="text-muted mb-2">
                                <i class="fas fa-book"></i>
                                <?php echo htmlspecialchars($session['course_title']); ?>
                            </p>

                            <div class="info-row">
                                <span class="info-label"><i class="far fa-calendar"></i> Date:</span>
                                <span class="info-value"><?php echo $startTime->format('M d, Y'); ?></span>
                            </div>

                            <div class="info-row">
                                <span class="info-label"><i class="far fa-clock"></i> Time:</span>
                                <span class="info-value">
                                    <?php echo $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A'); ?>
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-hourglass-half"></i> Duration:</span>
                                <span class="info-value"><?php echo $session['duration_minutes']; ?> min</span>
                            </div>

                            <?php if (!empty($session['description'])): ?>
                            <p class="mt-2 mb-2 text-muted">
                                <small><?php echo htmlspecialchars(substr($session['description'], 0, 100)); ?>...</small>
                            </p>
                            <?php endif; ?>

                            <div class="mt-3 d-flex gap-2">
                                <?php if ($session['status'] === 'scheduled' || $session['status'] === 'live'): ?>
                                <a href="<?php echo url("live-session.php?session_id={$session['id']}"); ?>"
                                   class="btn btn-success btn-sm flex-fill" target="_blank">
                                    <i class="fas fa-video"></i> Start/Join
                                </a>
                                <?php endif; ?>

                                <button class="btn btn-secondary btn-sm" onclick="viewSessionDetails(<?php echo $session['id']; ?>)">
                                    <i class="fas fa-eye"></i> Details
                                </button>

                                <?php if ($session['status'] === 'scheduled'): ?>
                                <button class="btn btn-danger btn-sm" onclick="cancelSession(<?php echo $session['id']; ?>)">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Session Modal -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" onclick="closeCreateModal()">&times;</span>
            <h2>Schedule New Live Session</h2>
        </div>
        <form id="createSessionForm">
            <div class="form-group">
                <label for="course_id">Select Course *</label>
                <select id="course_id" name="course_id" class="form-control" required onchange="loadLessons(this.value)">
                    <option value="">-- Select a course --</option>
                    <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="lesson_id">Select Lesson *</label>
                <select id="lesson_id" name="lesson_id" class="form-control" required disabled>
                    <option value="">-- Select a course first --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="scheduled_date">Date *</label>
                <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" required
                       min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="scheduled_time">Start Time *</label>
                <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="duration_minutes">Duration (minutes) *</label>
                <select id="duration_minutes" name="duration_minutes" class="form-control" required>
                    <option value="30">30 minutes</option>
                    <option value="45">45 minutes</option>
                    <option value="60" selected>1 hour</option>
                    <option value="90">1.5 hours</option>
                    <option value="120">2 hours</option>
                    <option value="180">3 hours</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="What will be covered in this live session?"></textarea>
            </div>

            <div class="form-group">
                <label for="max_participants">Max Participants (optional)</label>
                <input type="number" id="max_participants" name="max_participants" class="form-control"
                       placeholder="Leave empty for unlimited">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="allow_recording" checked> Allow session recording
                </label>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="enable_chat" checked> Enable chat
                </label>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="enable_screen_share" checked> Enable screen sharing
                </label>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-calendar-plus"></i> Schedule Session
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Session Details Modal -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" onclick="closeDetailsModal()">&times;</span>
            <h2>Session Details</h2>
        </div>
        <div id="sessionDetailsContent">
            Loading...
        </div>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('createModal').classList.add('show');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.remove('show');
    document.getElementById('createSessionForm').reset();
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.remove('show');
}

async function loadLessons(courseId) {
    const lessonSelect = document.getElementById('lesson_id');

    if (!courseId) {
        lessonSelect.disabled = true;
        lessonSelect.innerHTML = '<option value="">-- Select a course first --</option>';
        return;
    }

    lessonSelect.disabled = true;
    lessonSelect.innerHTML = '<option value="">Loading lessons...</option>';

    try {
        const response = await fetch(`<?php echo url('api/lessons.php'); ?>?course_id=${courseId}`);
        const data = await response.json();

        if (data.success && data.lessons) {
            lessonSelect.innerHTML = '<option value="">-- Select a lesson --</option>';
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

document.getElementById('createSessionForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    // Combine date and time
    data.scheduled_start_time = `${data.scheduled_date} ${data.scheduled_time}:00`;
    delete data.scheduled_date;
    delete data.scheduled_time;

    // Convert checkboxes to boolean
    data.allow_recording = formData.has('allow_recording') ? 1 : 0;
    data.enable_chat = formData.has('enable_chat') ? 1 : 0;
    data.enable_screen_share = formData.has('enable_screen_share') ? 1 : 0;

    try {
        const response = await fetch('<?php echo url('api/live-sessions.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'create', ...data })
        });

        const result = await response.json();

        if (result.success) {
            alert('Live session scheduled successfully!');
            closeCreateModal();
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to schedule session'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to schedule session. Please try again.');
    }
});

async function viewSessionDetails(sessionId) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('sessionDetailsContent');

    modal.classList.add('show');
    content.innerHTML = 'Loading...';

    try {
        const response = await fetch(`<?php echo url('api/live-sessions.php'); ?>?action=get&session_id=${sessionId}`);
        const data = await response.json();

        if (data.success && data.session) {
            const s = data.session;
            const attendanceCount = data.attendance_count || 0;

            content.innerHTML = `
                <div class="info-row">
                    <span class="info-label">Course:</span>
                    <span class="info-value">${s.course_title}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lesson:</span>
                    <span class="info-value">${s.lesson_title}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="session-status status-${s.status}">${s.status}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Scheduled:</span>
                    <span class="info-value">${new Date(s.scheduled_start_time).toLocaleString()}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Duration:</span>
                    <span class="info-value">${s.duration_minutes} minutes</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Attendance:</span>
                    <span class="info-value">${attendanceCount} participant(s)</span>
                </div>
                ${s.description ? `<div class="mt-3"><strong>Description:</strong><p>${s.description}</p></div>` : ''}
                ${s.recording_url ? `<div class="mt-3"><strong>Recording:</strong><br><a href="${s.recording_url}" target="_blank">View Recording</a></div>` : ''}
                <div class="mt-4">
                    <strong>Meeting Link:</strong><br>
                    <input type="text" class="form-control" value="${s.meeting_url}" readonly onclick="this.select()">
                    <small class="text-muted">Share this link with your students</small>
                </div>
            `;
        } else {
            content.innerHTML = '<p>Error loading session details.</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        content.innerHTML = '<p>Error loading session details.</p>';
    }
}

async function cancelSession(sessionId) {
    if (!confirm('Are you sure you want to cancel this live session?')) {
        return;
    }

    try {
        const response = await fetch('<?php echo url('api/live-sessions.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'cancel',
                session_id: sessionId
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('Session cancelled successfully!');
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to cancel session'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to cancel session. Please try again.');
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
}
</script>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
