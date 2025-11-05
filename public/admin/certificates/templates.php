<?php
/**
 * Admin Certificate Templates
 * Manage certificate templates and settings
 */

require_once '../../../src/middleware/admin-only.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Invalid request', 'error');
        redirect(url('admin/certificates/templates.php'));
    }

    $action = $_POST['action'] ?? null;

    if ($action == 'update_settings') {
        // Update certificate settings (in practice, you'd store these in a settings table)
        $settings = [
            'institution_name' => trim($_POST['institution_name'] ?? ''),
            'signatory_name' => trim($_POST['signatory_name'] ?? ''),
            'signatory_title' => trim($_POST['signatory_title'] ?? ''),
            'certificate_footer' => trim($_POST['certificate_footer'] ?? ''),
        ];

        // Store in session for now (in production, use database)
        $_SESSION['certificate_settings'] = $settings;
        
        flash('message', 'Certificate settings updated successfully', 'success');
        redirect(url('admin/certificates/templates.php'));
    }
}

// Get current settings
$settings = $_SESSION['certificate_settings'] ?? [
    'institution_name' => 'Edutrack computer training college',
    'signatory_name' => 'Director',
    'signatory_title' => 'Chief Executive Officer',
    'certificate_footer' => 'This certificate verifies the successful completion of the course requirements.',
];

// Get certificate statistics
$stats = [
    'total_issued' => (int) $db->fetchColumn("SELECT COUNT(*) FROM certificates"),
    'this_month' => (int) $db->fetchColumn("SELECT COUNT(*) FROM certificates WHERE issued_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
    'pending' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE status = 'completed' AND id NOT IN (SELECT enrollment_id FROM certificates WHERE enrollment_id IS NOT NULL)"),
];

// Get recent certificates
$recentCertificates = $db->fetchAll("
    SELECT c.*, 
           u.first_name, u.last_name,
           co.title as course_title
    FROM certificates c
    JOIN users u ON c.user_id = u.id
    JOIN courses co ON c.course_id = co.id
    ORDER BY c.issued_at DESC
    LIMIT 10
");

$page_title = 'Certificate Templates';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container-fluid px-4 py-6">

    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Certificate Templates</h1>
            <p class="text-gray-600 mt-1">Manage certificate design and settings</p>
        </div>
        <a href="<?= url('admin/certificates/index.php') ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-certificate mr-2"></i>View All Certificates
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-certificate text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Issued</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_issued']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-calendar text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">This Month</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['this_month']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Pending Issuance</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['pending']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Settings Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Certificate Settings</h2>
                </div>
                <div class="p-6">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="update_settings">

                        <div class="space-y-6">
                            <!-- Institution Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Institution Name
                                </label>
                                <input type="text" name="institution_name" 
                                       value="<?= htmlspecialchars($settings['institution_name']) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Name displayed at the top of certificates</p>
                            </div>

                            <!-- Signatory Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Signatory Name
                                </label>
                                <input type="text" name="signatory_name" 
                                       value="<?= htmlspecialchars($settings['signatory_name']) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Name of the person signing certificates</p>
                            </div>

                            <!-- Signatory Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Signatory Title
                                </label>
                                <input type="text" name="signatory_title" 
                                       value="<?= htmlspecialchars($settings['signatory_title']) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Title/position of the signatory</p>
                            </div>

                            <!-- Certificate Footer -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Certificate Footer Text
                                </label>
                                <textarea name="certificate_footer" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                          required><?= htmlspecialchars($settings['certificate_footer']) ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Text displayed at the bottom of certificates</p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex space-x-3">
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-save mr-2"></i>Save Settings
                                </button>
                                <button type="button" onclick="previewCertificate()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                    <i class="fas fa-eye mr-2"></i>Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Certificate Preview -->
            <div class="bg-white rounded-lg shadow mt-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Certificate Preview</h2>
                </div>
                <div class="p-6">
                    <div class="border-8 border-double border-blue-600 p-8 bg-gradient-to-br from-blue-50 to-white">
                        <div class="text-center">
                            <div class="mb-6">
                                <i class="fas fa-certificate text-6xl text-yellow-500 mb-4"></i>
                                <h1 class="text-4xl font-bold text-blue-900 mb-2">
                                    <?= htmlspecialchars($settings['institution_name']) ?>
                                </h1>
                                <p class="text-lg text-gray-600">Certificate of Completion</p>
                            </div>

                            <div class="my-8 text-lg text-gray-700">
                                <p class="mb-4">This is to certify that</p>
                                <p class="text-3xl font-bold text-blue-900 my-6">[Student Name]</p>
                                <p class="mb-4">has successfully completed</p>
                                <p class="text-2xl font-bold text-blue-900 my-6">[Course Title]</p>
                                <p class="mb-4">on [Completion Date]</p>
                            </div>

                            <div class="mt-12 pt-8 border-t-2 border-gray-300 flex justify-between items-end">
                                <div class="text-left">
                                    <div class="border-t-2 border-gray-800 pt-2 mb-2">
                                        <p class="font-bold text-gray-900"><?= htmlspecialchars($settings['signatory_name']) ?></p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($settings['signatory_title']) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Certificate Number</p>
                                    <p class="font-mono text-gray-900">[CERT-XXXXX]</p>
                                </div>
                            </div>

                            <div class="mt-6 text-xs text-gray-500 text-center">
                                <?= htmlspecialchars($settings['certificate_footer']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Certificates -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Recently Issued</h2>
            </div>
            <div class="p-6">
                <?php if (empty($recentCertificates)): ?>
                    <p class="text-center text-gray-500 py-8">No certificates issued yet</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentCertificates as $cert): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-certificate text-yellow-500 text-2xl"></i>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <?= htmlspecialchars($cert['course_title']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?= date('M d, Y', strtotime($cert['issued_at'])) ?>
                                    </p>
                                    <p class="text-xs text-gray-400 font-mono mt-1">
                                        <?= $cert['certificate_number'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="<?= url('admin/certificates/index.php') ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                            View all certificates →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
function previewCertificate() {
    const preview = document.querySelector('.border-double');
    preview.scrollIntoView({ behavior: 'smooth', block: 'center' });
    preview.classList.add('ring-4', 'ring-blue-300');
    setTimeout(() => {
        preview.classList.remove('ring-4', 'ring-blue-300');
    }, 2000);
}
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
