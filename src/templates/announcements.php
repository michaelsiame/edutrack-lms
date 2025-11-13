<?php
/**
 * Announcements Display Component
 * Shows active announcements for the current user
 *
 * Usage:
 * - Include this file in dashboards
 * - Optionally set $courseId before including to show course-specific announcements
 */

require_once __DIR__ . '/../classes/Announcement.php';

// Get current user info
$userId = currentUserId();
$userRole = currentUserRole();

// Get course ID if provided
$announcementCourseId = $courseId ?? null;

// Fetch active announcements for this user
$announcements = Announcement::getActiveForUser($userId, $userRole, $announcementCourseId);

if (!empty($announcements)):
?>

<div class="mb-6 space-y-4">
    <?php foreach ($announcements as $announcement): ?>
        <?php
        $type = $announcement->getAnnouncementType();
        $colorClasses = [
            'info' => 'bg-blue-50 border-blue-200 text-blue-900',
            'success' => 'bg-green-50 border-green-200 text-green-900',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-900',
            'urgent' => 'bg-red-50 border-red-200 text-red-900'
        ];
        $iconClasses = [
            'info' => 'text-blue-600',
            'success' => 'text-green-600',
            'warning' => 'text-yellow-600',
            'urgent' => 'text-red-600'
        ];
        $colorClass = $colorClasses[$type] ?? $colorClasses['info'];
        $iconClass = $iconClasses[$type] ?? $iconClasses['info'];
        ?>

        <div class="border rounded-lg p-4 <?= $colorClass ?>"
             x-data="{ expanded: false }">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-<?= $announcement->getTypeIcon() ?> text-xl <?= $iconClass ?>"></i>
                </div>
                <div class="ml-3 flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold"><?= sanitize($announcement->getTitle()) ?></h3>
                            <?php if (!$announcement->isGlobal()): ?>
                                <p class="text-xs opacity-75 mt-1">
                                    <i class="fas fa-book mr-1"></i><?= sanitize($announcement->getCourseTitle()) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <button @click="expanded = !expanded"
                                class="ml-4 text-sm hover:underline focus:outline-none">
                            <span x-show="!expanded">Show</span>
                            <span x-show="expanded" x-cloak>Hide</span>
                        </button>
                    </div>

                    <div x-show="expanded" x-collapse x-cloak class="mt-3">
                        <div class="prose prose-sm max-w-none">
                            <?= nl2br(sanitize($announcement->getContent())) ?>
                        </div>
                        <p class="text-xs opacity-75 mt-3">
                            Posted <?= timeAgo($announcement->getCreatedAt()) ?>
                            <?php if ($announcement->getExpiresAt()): ?>
                                • Expires <?= timeAgo($announcement->getExpiresAt()) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
// Include Alpine.js if not already loaded
if (!defined('ALPINE_JS_LOADED')):
    define('ALPINE_JS_LOADED', true);
?>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<?php endif; ?>

<?php endif; ?>
