<?php
/**
 * Edutrack computer training college
 * Alert/Notification Components
 */

/**
 * Display alert message
 * 
 * @param string $message Alert message
 * @param string $type Alert type (success, error, warning, info)
 * @param bool $dismissible Can be dismissed
 */
function displayAlert($message, $type = 'info', $dismissible = true) {
    $alertColors = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-500',
            'text' => 'text-green-800',
            'icon' => 'fa-check-circle',
            'iconColor' => 'text-green-600'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-500',
            'text' => 'text-red-800',
            'icon' => 'fa-exclamation-circle',
            'iconColor' => 'text-red-600'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-500',
            'text' => 'text-yellow-800',
            'icon' => 'fa-exclamation-triangle',
            'iconColor' => 'text-yellow-600'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-500',
            'text' => 'text-blue-800',
            'icon' => 'fa-info-circle',
            'iconColor' => 'text-blue-600'
        ]
    ];
    
    $colors = $alertColors[$type] ?? $alertColors['info'];
    
    ?>
    <div class="<?= $colors['bg'] ?> border-l-4 <?= $colors['border'] ?> p-4 rounded mb-4 <?= $dismissible ? 'relative' : '' ?>" 
         role="alert"
         <?= $dismissible ? 'x-data="{ show: true }" x-show="show"' : '' ?>>
        <div class="flex items-start">
            <i class="fas <?= $colors['icon'] ?> <?= $colors['iconColor'] ?> mr-3 mt-1"></i>
            <div class="flex-1">
                <p class="<?= $colors['text'] ?> font-medium"><?= $message ?></p>
            </div>
            <?php if ($dismissible): ?>
            <button @click="show = false" class="<?= $colors['text'] ?> hover:opacity-75 ml-3">
                <i class="fas fa-times"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Display success alert
 */
function successAlert($message, $dismissible = true) {
    displayAlert($message, 'success', $dismissible);
}

/**
 * Display error alert
 */
function errorAlert($message, $dismissible = true) {
    displayAlert($message, 'error', $dismissible);
}

/**
 * Display warning alert
 */
function warningAlert($message, $dismissible = true) {
    displayAlert($message, 'warning', $dismissible);
}

/**
 * Display info alert
 */
function infoAlert($message, $dismissible = true) {
    displayAlert($message, 'info', $dismissible);
}

/**
 * Display validation errors
 * 
 * @param array $errors Validation errors array
 */
function displayValidationErrors($errors) {
    if (empty($errors)) {
        return;
    }
    ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4" role="alert">
        <div class="flex items-start">
            <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-1"></i>
            <div class="flex-1">
                <p class="text-red-800 font-medium mb-2">Please correct the following errors:</p>
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    <?php foreach ($errors as $field => $fieldErrors): ?>
                        <?php if (is_array($fieldErrors)): ?>
                            <?php foreach ($fieldErrors as $error): ?>
                                <li><?= sanitize($error) ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><?= sanitize($fieldErrors) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Display TEVETA badge
 */
function teveta_badge() {
    ?>
    <div class="inline-flex items-center px-3 py-1 rounded-full bg-secondary-100 text-secondary-900 text-sm font-semibold">
        <i class="fas fa-certificate mr-2"></i>
        TEVETA REGISTERED
    </div>
    <?php
}

/**
 * Display loading spinner
 * 
 * @param string $text Loading text
 */
function loadingSpinner($text = 'Loading...') {
    ?>
    <div class="flex items-center justify-center py-8">
        <div class="text-center">
            <i class="fas fa-spinner fa-spin text-primary-600 text-4xl mb-3"></i>
            <p class="text-gray-600"><?= sanitize($text) ?></p>
        </div>
    </div>
    <?php
}

/**
 * Display empty state
 * 
 * @param string $icon FontAwesome icon class
 * @param string $title Empty state title
 * @param string $message Empty state message
 * @param string|null $actionUrl Action button URL
 * @param string|null $actionText Action button text
 */
function emptyState($icon, $title, $message, $actionUrl = null, $actionText = null) {
    ?>
    <div class="text-center py-12 px-4">
        <i class="fas <?= $icon ?> text-gray-400 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= sanitize($title) ?></h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto"><?= sanitize($message) ?></p>
        <?php if ($actionUrl && $actionText): ?>
            <a href="<?= $actionUrl ?>" class="btn-primary px-6 py-3 rounded-md inline-block">
                <?= sanitize($actionText) ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Display confirmation modal trigger
 * 
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $message Modal message
 * @param string $confirmText Confirm button text
 * @param string $confirmAction Confirm action (JavaScript or URL)
 */
function confirmationModal($id, $title, $message, $confirmText = 'Confirm', $confirmAction = '') {
    ?>
    <div id="<?= $id ?>" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.away="document.getElementById('<?= $id ?>').classList.add('hidden')">
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= sanitize($title) ?></h3>
                    <p class="text-gray-600"><?= sanitize($message) ?></p>
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="document.getElementById('<?= $id ?>').classList.add('hidden')" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button onclick="<?= $confirmAction ?>; document.getElementById('<?= $id ?>').classList.add('hidden')" 
                        class="btn-primary px-4 py-2 rounded-md">
                    <?= sanitize($confirmText) ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Display toast notification (requires JavaScript)
 * 
 * @param string $message Toast message
 * @param string $type Toast type
 */
function toastNotification($message, $type = 'info') {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('<?= addslashes($message) ?>', '<?= $type ?>');
        });
    </script>
    <?php
}