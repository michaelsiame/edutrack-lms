<?php
/**
 * Instructor Discussion Moderation
 * Allows instructors to manage course discussions: pin, answer, hide, delete, reply
 */
require_once '../../src/bootstrap.php';
require_once '../../src/middleware/instructor-only.php';

$user = User::current();
$userId = $user->getId();

// Get instructor's courses
$instructorCourses = $db->fetchAll("
    SELECT c.id, c.title, c.slug
    FROM courses c
    JOIN course_instructors ci ON c.id = ci.course_id
    WHERE ci.instructor_id = (SELECT id FROM instructors WHERE user_id = ?)
    ORDER BY c.title
", [$userId]);

$courseIds = array_column($instructorCourses, 'id');

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($courseIds)) {
    validateCSRF();
    $action = $_POST['action'] ?? '';
    $discussionId = (int)($_POST['discussion_id'] ?? 0);

    // Verify discussion belongs to instructor's course
    if ($discussionId) {
        $disc = $db->fetchOne("SELECT id, course_id FROM discussions WHERE id = ?", [$discussionId]);
        if (!$disc || !in_array($disc['course_id'], $courseIds)) {
            flash('message', 'Unauthorized action', 'error');
            redirect($_SERVER['REQUEST_URI']);
        }
    }

    switch ($action) {
        case 'pin':
            $current = $db->fetchColumn("SELECT is_pinned FROM discussions WHERE id = ?", [$discussionId]);
            $db->query("UPDATE discussions SET is_pinned = ? WHERE id = ?", [$current ? 0 : 1, $discussionId]);
            flash('message', $current ? 'Discussion unpinned' : 'Discussion pinned', 'success');
            break;

        case 'mark_answered':
            $current = $db->fetchColumn("SELECT is_answered FROM discussions WHERE id = ?", [$discussionId]);
            $db->query("UPDATE discussions SET is_answered = ? WHERE id = ?", [$current ? 0 : 1, $discussionId]);
            flash('message', $current ? 'Marked as unanswered' : 'Marked as answered', 'success');
            break;

        case 'hide':
            $reason = trim($_POST['hidden_reason'] ?? 'Hidden by instructor');
            $db->query("UPDATE discussions SET is_hidden = 1, hidden_by = ?, hidden_reason = ? WHERE id = ?", [$userId, $reason, $discussionId]);
            flash('message', 'Discussion hidden', 'success');
            break;

        case 'unhide':
            $db->query("UPDATE discussions SET is_hidden = 0, hidden_by = NULL, hidden_reason = NULL WHERE id = ?", [$discussionId]);
            flash('message', 'Discussion restored', 'success');
            break;

        case 'delete':
            $db->query("DELETE FROM discussions WHERE id = ? OR parent_id = ?", [$discussionId, $discussionId]);
            flash('message', 'Discussion and replies deleted', 'success');
            break;

        case 'reply':
            $content = trim($_POST['content'] ?? '');
            $courseId = (int)($_POST['course_id'] ?? 0);
            if ($content && $discussionId && in_array($courseId, $courseIds)) {
                $db->query(
                    "INSERT INTO discussions (course_id, user_id, parent_id, content, created_at) VALUES (?, ?, ?, ?, NOW())",
                    [$courseId, $userId, $discussionId, $content]
                );
                flash('message', 'Reply posted', 'success');
            }
            break;
    }

    redirect($_SERVER['REQUEST_URI']);
}

// Filters
$courseFilter = (int)($_GET['course_id'] ?? 0);
$statusFilter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

// Build query
$where = ['d.parent_id IS NULL'];
$params = [];

if (!empty($courseIds)) {
    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
    $where[] = "d.course_id IN ($placeholders)";
    $params = array_merge($params, $courseIds);
} else {
    $where[] = '1=0';
}

if ($courseFilter && in_array($courseFilter, $courseIds)) {
    $where[] = 'd.course_id = ?';
    $params[] = $courseFilter;
}

switch ($statusFilter) {
    case 'unanswered': $where[] = 'd.is_answered = 0'; break;
    case 'pinned': $where[] = 'd.is_pinned = 1'; break;
    case 'hidden': $where[] = 'd.is_hidden = 1'; break;
    default: $where[] = 'd.is_hidden = 0'; break; // 'all' hides hidden by default
}

if ($search) {
    $where[] = "(d.title LIKE ? OR d.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

$discussions = $db->fetchAll("
    SELECT d.*, u.first_name, u.last_name, u.email,
           COALESCE(r.role_name, 'Student') as role,
           c.title as course_title, c.slug as course_slug,
           l.title as lesson_title,
           (SELECT COUNT(*) FROM discussions WHERE parent_id = d.id) as reply_count
    FROM discussions d
    JOIN users u ON d.user_id = u.id
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    JOIN courses c ON d.course_id = c.id
    LEFT JOIN lessons l ON d.lesson_id = l.id
    $whereClause
    ORDER BY d.is_pinned DESC, d.created_at DESC
    LIMIT 50
", $params);

// Stats
$statsBase = !empty($courseIds) ? "course_id IN (" . implode(',', array_fill(0, count($courseIds), '?')) . ")" : '1=0';
$statsParams = $courseIds;
$totalDiscussions = $db->fetchColumn("SELECT COUNT(*) FROM discussions WHERE parent_id IS NULL AND $statsBase", $statsParams);
$unansweredCount = $db->fetchColumn("SELECT COUNT(*) FROM discussions WHERE parent_id IS NULL AND is_answered = 0 AND is_hidden = 0 AND $statsBase", $statsParams);
$pinnedCount = $db->fetchColumn("SELECT COUNT(*) FROM discussions WHERE parent_id IS NULL AND is_pinned = 1 AND $statsBase", $statsParams);
$hiddenCount = $db->fetchColumn("SELECT COUNT(*) FROM discussions WHERE parent_id IS NULL AND is_hidden = 1 AND $statsBase", $statsParams);

$page_title = 'Discussion Moderation';
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-comments text-primary-600 mr-2"></i>Discussion Moderation
            </h1>
            <p class="text-gray-600 mt-1">Manage and moderate discussions across your courses</p>
        </div>

        <?php $flashMsg = getFlash('message'); if ($flashMsg): ?>
            <div class="mb-4 px-4 py-3 rounded-lg <?= $flashMsg['type'] === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                <i class="fas <?= $flashMsg['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mr-2"></i>
                <?= htmlspecialchars($flashMsg['message'] ?? $flashMsg) ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl shadow-sm border">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg"><i class="fas fa-comments"></i></div>
                    <div><p class="text-2xl font-bold"><?= $totalDiscussions ?></p><p class="text-xs text-gray-500">Total</p></div>
                </div>
            </div>
            <a href="?filter=unanswered" class="bg-white p-4 rounded-xl shadow-sm border hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-orange-100 text-orange-600 rounded-lg"><i class="fas fa-question-circle"></i></div>
                    <div><p class="text-2xl font-bold"><?= $unansweredCount ?></p><p class="text-xs text-gray-500">Unanswered</p></div>
                </div>
            </a>
            <a href="?filter=pinned" class="bg-white p-4 rounded-xl shadow-sm border hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 text-purple-600 rounded-lg"><i class="fas fa-thumbtack"></i></div>
                    <div><p class="text-2xl font-bold"><?= $pinnedCount ?></p><p class="text-xs text-gray-500">Pinned</p></div>
                </div>
            </a>
            <a href="?filter=hidden" class="bg-white p-4 rounded-xl shadow-sm border hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 text-red-600 rounded-lg"><i class="fas fa-eye-slash"></i></div>
                    <div><p class="text-2xl font-bold"><?= $hiddenCount ?></p><p class="text-xs text-gray-500">Hidden</p></div>
                </div>
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-center">
                <select name="course_id" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    <option value="">All Courses</option>
                    <?php foreach ($instructorCourses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $courseFilter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="filter" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Visible</option>
                    <option value="unanswered" <?= $statusFilter === 'unanswered' ? 'selected' : '' ?>>Unanswered</option>
                    <option value="pinned" <?= $statusFilter === 'pinned' ? 'selected' : '' ?>>Pinned</option>
                    <option value="hidden" <?= $statusFilter === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                </select>
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search discussions..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-filter mr-1"></i>Filter
                </button>
                <?php if ($courseFilter || $statusFilter !== 'all' || $search): ?>
                    <a href="discussions.php" class="text-gray-600 hover:text-gray-800 px-3 py-2"><i class="fas fa-times mr-1"></i>Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Discussions -->
        <?php if (empty($discussions)): ?>
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <i class="fas fa-comments text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No discussions found</h3>
                <p class="text-gray-600">There are no discussions matching your filters.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($discussions as $d): ?>
                    <div class="bg-white rounded-xl shadow-sm border <?= $d['is_hidden'] ? 'border-l-4 border-l-orange-400 bg-orange-50' : '' ?> hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <img src="<?= getGravatar($d['email']) ?>" class="h-12 w-12 rounded-full flex-shrink-0" alt="">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div>
                                            <h3 class="font-semibold text-gray-900 text-lg">
                                                <?php if ($d['is_pinned']): ?><i class="fas fa-thumbtack text-purple-600 mr-1"></i><?php endif; ?>
                                                <?= htmlspecialchars($d['title'] ?? 'Reply') ?>
                                            </h3>
                                            <div class="flex items-center gap-2 text-sm text-gray-500 mt-1 flex-wrap">
                                                <span class="font-medium text-gray-700"><?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?></span>
                                                <?php if (strtolower($d['role']) !== 'student'): ?>
                                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-medium"><?= htmlspecialchars($d['role']) ?></span>
                                                <?php endif; ?>
                                                <span>&bull;</span>
                                                <span><?= timeAgo($d['created_at']) ?></span>
                                                <span>&bull;</span>
                                                <span class="text-primary-600"><?= htmlspecialchars($d['course_title']) ?></span>
                                                <?php if ($d['lesson_title']): ?>
                                                    <span>&bull;</span>
                                                    <span class="text-gray-400"><?= htmlspecialchars($d['lesson_title']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <?php if ($d['is_answered']): ?>
                                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                                    <i class="fas fa-check-circle mr-1"></i>Answered
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($d['is_hidden']): ?>
                                                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium">
                                                    <i class="fas fa-eye-slash mr-1"></i>Hidden
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <p class="text-gray-700 mt-3 whitespace-pre-wrap"><?= nl2br(htmlspecialchars(mb_substr($d['content'], 0, 300))) ?><?= mb_strlen($d['content']) > 300 ? '...' : '' ?></p>

                                    <?php if ($d['is_hidden'] && $d['hidden_reason']): ?>
                                        <div class="mt-2 text-sm text-orange-700 bg-orange-100 px-3 py-2 rounded-lg">
                                            <i class="fas fa-info-circle mr-1"></i>Hidden reason: <?= htmlspecialchars($d['hidden_reason']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Action Bar -->
                                    <div class="flex items-center gap-2 mt-4 flex-wrap">
                                        <span class="text-sm text-gray-500 mr-2">
                                            <i class="fas fa-reply mr-1"></i><?= $d['reply_count'] ?> <?= $d['reply_count'] == 1 ? 'reply' : 'replies' ?>
                                        </span>

                                        <form method="POST" class="inline"><?= csrfField() ?>
                                            <input type="hidden" name="action" value="pin">
                                            <input type="hidden" name="discussion_id" value="<?= $d['id'] ?>">
                                            <button type="submit" class="px-3 py-1 text-xs rounded-lg border <?= $d['is_pinned'] ? 'bg-purple-100 text-purple-700 border-purple-200' : 'text-gray-600 hover:bg-gray-100' ?>">
                                                <i class="fas fa-thumbtack mr-1"></i><?= $d['is_pinned'] ? 'Unpin' : 'Pin' ?>
                                            </button>
                                        </form>

                                        <form method="POST" class="inline"><?= csrfField() ?>
                                            <input type="hidden" name="action" value="mark_answered">
                                            <input type="hidden" name="discussion_id" value="<?= $d['id'] ?>">
                                            <button type="submit" class="px-3 py-1 text-xs rounded-lg border <?= $d['is_answered'] ? 'bg-green-100 text-green-700 border-green-200' : 'text-gray-600 hover:bg-gray-100' ?>">
                                                <i class="fas fa-check-circle mr-1"></i><?= $d['is_answered'] ? 'Unanswer' : 'Answer' ?>
                                            </button>
                                        </form>

                                        <?php if ($d['is_hidden']): ?>
                                            <form method="POST" class="inline"><?= csrfField() ?>
                                                <input type="hidden" name="action" value="unhide">
                                                <input type="hidden" name="discussion_id" value="<?= $d['id'] ?>">
                                                <button type="submit" class="px-3 py-1 text-xs rounded-lg border text-orange-600 hover:bg-orange-50">
                                                    <i class="fas fa-eye mr-1"></i>Unhide
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button onclick="showHideForm(<?= $d['id'] ?>)" class="px-3 py-1 text-xs rounded-lg border text-orange-600 hover:bg-orange-50">
                                                <i class="fas fa-eye-slash mr-1"></i>Hide
                                            </button>
                                        <?php endif; ?>

                                        <button onclick="toggleReplyForm(<?= $d['id'] ?>)" class="px-3 py-1 text-xs rounded-lg border text-blue-600 hover:bg-blue-50">
                                            <i class="fas fa-comment mr-1"></i>Reply
                                        </button>

                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this discussion and all replies?')"><?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="discussion_id" value="<?= $d['id'] ?>">
                                            <button type="submit" class="px-3 py-1 text-xs rounded-lg border text-red-600 hover:bg-red-50">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </button>
                                        </form>

                                        <button onclick="toggleReplies(<?= $d['id'] ?>)" class="px-3 py-1 text-xs rounded-lg border text-gray-600 hover:bg-gray-100 ml-auto">
                                            <i class="fas fa-chevron-down mr-1" id="replies-icon-<?= $d['id'] ?>"></i>Show Replies
                                        </button>
                                    </div>

                                    <!-- Hide Form (hidden by default) -->
                                    <div id="hide-form-<?= $d['id'] ?>" class="hidden mt-3 p-3 bg-orange-50 rounded-lg border border-orange-200">
                                        <form method="POST" class="flex gap-2">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="hide">
                                            <input type="hidden" name="discussion_id" value="<?= $d['id'] ?>">
                                            <input type="text" name="hidden_reason" placeholder="Reason for hiding..." required class="flex-1 px-3 py-2 border rounded-lg text-sm">
                                            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg text-sm hover:bg-orange-700">Hide</button>
                                            <button type="button" onclick="document.getElementById('hide-form-<?= $d['id'] ?>').classList.add('hidden')" class="px-3 py-2 text-gray-600 text-sm">Cancel</button>
                                        </form>
                                    </div>

                                    <!-- Reply Form (hidden by default) -->
                                    <div id="reply-form-<?= $d['id'] ?>" class="hidden mt-3 p-3 bg-gray-50 rounded-lg border">
                                        <form method="POST">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="reply">
                                            <input type="hidden" name="discussion_id" value="<?= $d['id'] ?>">
                                            <input type="hidden" name="course_id" value="<?= $d['course_id'] ?>">
                                            <textarea name="content" rows="3" required class="w-full px-3 py-2 border rounded-lg text-sm mb-2" placeholder="Write your reply as instructor..."></textarea>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="toggleReplyForm(<?= $d['id'] ?>)" class="px-4 py-2 text-gray-600 text-sm">Cancel</button>
                                                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700">Post Reply</button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Replies Section (hidden by default) -->
                                    <div id="replies-<?= $d['id'] ?>" class="hidden mt-4 border-t pt-4 space-y-3">
                                        <?php
                                        $replies = $db->fetchAll("
                                            SELECT d.*, u.first_name, u.last_name, u.email,
                                                   COALESCE(r.role_name, 'Student') as role
                                            FROM discussions d
                                            JOIN users u ON d.user_id = u.id
                                            LEFT JOIN user_roles ur ON u.id = ur.user_id
                                            LEFT JOIN roles r ON ur.role_id = r.id
                                            WHERE d.parent_id = ?
                                            ORDER BY d.created_at ASC
                                        ", [$d['id']]);

                                        if (empty($replies)): ?>
                                            <p class="text-sm text-gray-500 text-center py-2">No replies yet</p>
                                        <?php else:
                                            foreach ($replies as $reply): ?>
                                                <div class="flex gap-3 p-3 bg-gray-50 rounded-lg">
                                                    <img src="<?= getGravatar($reply['email']) ?>" class="h-8 w-8 rounded-full flex-shrink-0" alt="">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2 text-sm">
                                                            <span class="font-medium text-gray-900"><?= htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']) ?></span>
                                                            <?php if (strtolower($reply['role']) !== 'student'): ?>
                                                                <span class="px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded text-xs"><?= htmlspecialchars($reply['role']) ?></span>
                                                            <?php endif; ?>
                                                            <span class="text-gray-400"><?= timeAgo($reply['created_at']) ?></span>
                                                        </div>
                                                        <p class="text-gray-700 text-sm mt-1 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach;
                                        endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleReplies(id) {
    var el = document.getElementById('replies-' + id);
    el.classList.toggle('hidden');
    var icon = document.getElementById('replies-icon-' + id);
    icon.classList.toggle('fa-chevron-down');
    icon.classList.toggle('fa-chevron-up');
}

function toggleReplyForm(id) {
    document.getElementById('reply-form-' + id).classList.toggle('hidden');
}

function showHideForm(id) {
    document.getElementById('hide-form-' + id).classList.remove('hidden');
}
</script>

<?php require_once '../../src/templates/footer.php'; ?>
