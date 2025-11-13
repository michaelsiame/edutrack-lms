<?php
/**
 * Admin Announcements
 * Manage system announcements
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Announcement.php';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    validateCSRF();

    $announcement = Announcement::find($_GET['id']);
    if ($announcement && $announcement->delete()) {
        flash('message', 'Announcement deleted successfully', 'success');
    } else {
        flash('message', 'Failed to delete announcement', 'error');
    }
    redirect('admin/announcements/index.php');
}

// Handle publish/unpublish action
if (isset($_GET['action']) && isset($_GET['id'])) {
    validateCSRF();

    $announcement = Announcement::find($_GET['id']);
    if ($announcement) {
        if ($_GET['action'] === 'publish') {
            if ($announcement->publish()) {
                flash('message', 'Announcement published successfully', 'success');
            }
        } elseif ($_GET['action'] === 'unpublish') {
            if ($announcement->unpublish()) {
                flash('message', 'Announcement unpublished successfully', 'success');
            }
        }
    }
    redirect('admin/announcements/index.php');
}

// Get all announcements
$announcements = Announcement::getAll();

$page_title = 'Manage Announcements';
require_once '../../../src/templates/admin-header.php';
?>

<div class="flex h-screen bg-gray-100">
    <?php require_once '../../../src/templates/admin-sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm z-10">
            <div class="flex items-center justify-between px-8 py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Announcements</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage system-wide and course announcements</p>
                </div>
                <a href="<?= url('admin/announcements/create.php') ?>"
                   class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-plus mr-2"></i>Create Announcement
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-8">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <?php
                $totalCount = count($announcements);
                $publishedCount = count(array_filter($announcements, fn($a) => $a->isPublished()));
                $activeCount = count(array_filter($announcements, fn($a) => $a->isActive()));
                $urgentCount = count(array_filter($announcements, fn($a) => $a->getAnnouncementType() === 'urgent'));
                ?>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalCount ?></p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-bullhorn text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Published</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $publishedCount ?></p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Active Now</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $activeCount ?></p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-broadcast-tower text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Urgent</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $urgentCount ?></p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcements Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Announcement
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Audience
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Scope
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($announcements)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-bullhorn text-4xl text-gray-400 mb-4"></i>
                                        <p>No announcements yet.</p>
                                        <a href="<?= url('admin/announcements/create.php') ?>" class="text-primary-600 hover:text-primary-700">
                                            Create your first announcement
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($announcements as $announcement): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div>
                                                <p class="font-medium text-gray-900"><?= sanitize($announcement->getTitle()) ?></p>
                                                <p class="text-sm text-gray-500">
                                                    <?= sanitize(substr($announcement->getContent(), 0, 100)) ?>...
                                                </p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    By <?= sanitize($announcement->getCreatorName()) ?> •
                                                    <?= timeAgo($announcement->getCreatedAt()) ?>
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $color = $announcement->getTypeBadgeColor();
                                            $type = $announcement->getAnnouncementType();
                                            ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $color ?>-100 text-<?= $color ?>-800">
                                                <i class="fas fa-<?= $announcement->getTypeIcon() ?> mr-1"></i>
                                                <?= ucfirst($type) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">
                                                <?= ucfirst($announcement->getTargetAudience()) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($announcement->isGlobal()): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-globe mr-1"></i>Global
                                                </span>
                                            <?php else: ?>
                                                <span class="text-sm text-gray-600">
                                                    <?= sanitize($announcement->getCourseTitle()) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($announcement->isActive()): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>Active
                                                </span>
                                            <?php elseif ($announcement->isPublished()): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Published
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Draft
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($announcement->isExpired()): ?>
                                                <span class="block mt-1 text-xs text-red-600">
                                                    <i class="fas fa-clock mr-1"></i>Expired
                                                </span>
                                            <?php elseif ($announcement->getExpiresAt()): ?>
                                                <span class="block mt-1 text-xs text-gray-500">
                                                    Expires <?= timeAgo($announcement->getExpiresAt()) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center space-x-2">
                                                <a href="<?= url('admin/announcements/edit.php?id=' . $announcement->getId()) ?>"
                                                   class="text-blue-600 hover:text-blue-900" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <?php if ($announcement->isPublished()): ?>
                                                    <a href="<?= url('admin/announcements/index.php?action=unpublish&id=' . $announcement->getId() . '&csrf_token=' . csrf_token()) ?>"
                                                       class="text-yellow-600 hover:text-yellow-900" title="Unpublish"
                                                       onclick="return confirm('Unpublish this announcement?')">
                                                        <i class="fas fa-eye-slash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= url('admin/announcements/index.php?action=publish&id=' . $announcement->getId() . '&csrf_token=' . csrf_token()) ?>"
                                                       class="text-green-600 hover:text-green-900" title="Publish"
                                                       onclick="return confirm('Publish this announcement?')">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <a href="<?= url('admin/announcements/index.php?action=delete&id=' . $announcement->getId() . '&csrf_token=' . csrf_token()) ?>"
                                                   class="text-red-600 hover:text-red-900" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this announcement?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
