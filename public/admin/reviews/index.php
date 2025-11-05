<?php
/**
 * Admin Reviews Management
 * Manage and moderate course reviews
 */

require_once '../../../src/middleware/admin-only.php';

// Handle review actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Invalid request', 'error');
        redirect(url('admin/reviews/index.php'));
    }

    $action = $_POST['action'] ?? null;
    $reviewId = $_POST['review_id'] ?? null;

    if ($action == 'approve' && $reviewId) {
        $db->update('reviews', ['status' => 'approved'], 'id = ?', [$reviewId]);
        flash('message', 'Review approved successfully', 'success');
    } elseif ($action == 'reject' && $reviewId) {
        $db->update('reviews', ['status' => 'rejected'], 'id = ?', [$reviewId]);
        flash('message', 'Review rejected', 'success');
    } elseif ($action == 'delete' && $reviewId) {
        $db->delete('reviews', 'id = ?', [$reviewId]);
        flash('message', 'Review deleted successfully', 'success');
    }

    redirect(url('admin/reviews/index.php'));
}

// Filters
$status = $_GET['status'] ?? 'pending';
$courseId = $_GET['course_id'] ?? '';
$rating = $_GET['rating'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$sql = "SELECT r.*, 
        c.title as course_title,
        u.first_name, u.last_name, u.email
        FROM reviews r
        JOIN courses c ON r.course_id = c.id
        JOIN users u ON r.user_id = u.id
        WHERE 1=1";

$params = [];

if ($status && $status != 'all') {
    $sql .= " AND r.status = ?";
    $params[] = $status;
}

if ($courseId) {
    $sql .= " AND r.course_id = ?";
    $params[] = $courseId;
}

if ($rating) {
    $sql .= " AND r.rating = ?";
    $params[] = $rating;
}

// Get total count
$countSql = "SELECT COUNT(*) FROM ($sql) as count_table";
$totalReviews = (int) $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalReviews / $perPage);

// Get reviews
$sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$reviews = $db->fetchAll($sql, $params);

// Get statistics
$stats = [
    'total' => (int) $db->fetchColumn("SELECT COUNT(*) FROM reviews"),
    'pending' => (int) $db->fetchColumn("SELECT COUNT(*) FROM reviews WHERE status = 'pending'"),
    'approved' => (int) $db->fetchColumn("SELECT COUNT(*) FROM reviews WHERE status = 'approved'"),
    'rejected' => (int) $db->fetchColumn("SELECT COUNT(*) FROM reviews WHERE status = 'rejected'"),
];

// Get courses for filter
$courses = $db->fetchAll("SELECT id, title FROM courses ORDER BY title");

$page_title = 'Manage Reviews';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container-fluid px-4 py-6">

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Reviews Management</h1>
        <p class="text-gray-600 mt-1">Moderate and manage course reviews</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Reviews</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['pending']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Approved</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['approved']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Rejected</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['rejected']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                    <select name="course_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>" <?= $courseId == $course['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                    <select name="rating" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Ratings</option>
                        <option value="5" <?= $rating == '5' ? 'selected' : '' ?>>5 Stars</option>
                        <option value="4" <?= $rating == '4' ? 'selected' : '' ?>>4 Stars</option>
                        <option value="3" <?= $rating == '3' ? 'selected' : '' ?>>3 Stars</option>
                        <option value="2" <?= $rating == '2' ? 'selected' : '' ?>>2 Stars</option>
                        <option value="1" <?= $rating == '1' ? 'selected' : '' ?>>1 Star</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="<?= url('admin/reviews/index.php') ?>" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="space-y-4">
        <?php if (empty($reviews)): ?>
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-star text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500">No reviews found</p>
        </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <!-- Review Header -->
                            <div class="flex items-center space-x-4 mb-3">
                                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold">
                                        <?= strtoupper(substr($review['first_name'], 0, 1) . substr($review['last_name'], 0, 1)) ?>
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">
                                        <?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>
                                    </h3>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                                        <span><?= htmlspecialchars($review['course_title']) ?></span>
                                        <span>•</span>
                                        <span><?= date('M d, Y', strtotime($review['created_at'])) ?></span>
                                    </div>
                                </div>
                                
                                <!-- Status Badge -->
                                <?php if ($review['status'] == 'approved'): ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                <?php elseif ($review['status'] == 'pending'): ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                <?php endif; ?>
                            </div>

                            <!-- Star Rating -->
                            <div class="flex items-center mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                <?php endfor; ?>
                                <span class="ml-2 text-sm text-gray-600"><?= $review['rating'] ?>.0</span>
                            </div>

                            <!-- Review Text -->
                            <?php if ($review['comment']): ?>
                            <div class="prose max-w-none">
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="ml-6 flex flex-col space-y-2">
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                
                                <?php if ($review['status'] != 'approved'): ?>
                                <button type="submit" name="action" value="approve"
                                        class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 w-full">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($review['status'] != 'rejected'): ?>
                                <button type="submit" name="action" value="reject"
                                        class="px-4 py-2 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 w-full">
                                    <i class="fas fa-ban mr-1"></i>Reject
                                </button>
                                <?php endif; ?>
                                
                                <button type="submit" name="action" value="delete"
                                        onclick="return confirm('Are you sure you want to delete this review?')"
                                        class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 w-full">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalReviews) ?> of <?= $totalReviews ?> reviews
        </div>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&course_id=<?= urlencode($courseId) ?>&rating=<?= urlencode($rating) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Previous
            </a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&course_id=<?= urlencode($courseId) ?>&rating=<?= urlencode($rating) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-50' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&course_id=<?= urlencode($courseId) ?>&rating=<?= urlencode($rating) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Next
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
