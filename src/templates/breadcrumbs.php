<?php
/**
 * Breadcrumb Navigation Component
 * Usage: 
 *   $breadcrumbs = [
 *       ['label' => 'Home', 'url' => '/'],
 *       ['label' => 'Courses', 'url' => '/courses.php'],
 *       ['label' => 'Current Page']
 *   ];
 *   require_once '../src/templates/breadcrumbs.php';
 */

if (empty($breadcrumbs)) {
    return;
}
?>

<nav class="bg-gray-50 border-b border-gray-200" aria-label="Breadcrumb">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <ol class="flex items-center space-x-2 text-sm">
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <?php if ($index > 0): ?>
                    <li class="text-gray-400">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </li>
                <?php endif; ?>
                
                <li>
                    <?php if (isset($crumb['url']) && $index < count($breadcrumbs) - 1): ?>
                        <a href="<?= url($crumb['url']) ?>" 
                           class="text-gray-600 hover:text-primary-600 transition flex items-center">
                            <?php if ($index === 0): ?>
                                <i class="fas fa-home mr-1"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($crumb['label']) ?>
                        </a>
                    <?php else: ?>
                        <span class="text-gray-900 font-medium" aria-current="page">
                            <?= htmlspecialchars($crumb['label']) ?>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
