<?php
/**
 * Certificate Verification
 * Public page to verify TEVETA certificates
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';

$verificationCode = $_GET['code'] ?? null;
$certificateNumber = $_GET['number'] ?? null;
$certificate = null;
$error = null;

// Handle verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' || $verificationCode || $certificateNumber) {
    $searchCode = $verificationCode ?? $_POST['verification_code'] ?? null;
    $searchNumber = $certificateNumber ?? $_POST['certificate_number'] ?? null;
    
    if ($searchCode || $searchNumber) {
        $where = [];
        $params = [];
        
        if ($searchCode) {
            $where[] = 'c.verification_code = ?';
            $params[] = $searchCode;
        }
        
        if ($searchNumber) {
            $where[] = 'c.certificate_number = ?';
            $params[] = $searchNumber;
        }
        
        $whereClause = implode(' OR ', $where);
        
        $certificate = $db->fetchOne("
            SELECT 
                c.*,
                u.first_name as student_first_name,
                u.last_name as student_last_name,
                co.title as course_title,
                co.duration_hours,
                co.is_teveta,
                cat.name as category_name,
                i.first_name as instructor_first_name,
                i.last_name as instructor_last_name
            FROM certificates c
            JOIN users u ON c.user_id = u.id
            JOIN courses co ON c.course_id = co.id
            LEFT JOIN course_categories cat ON co.category_id = cat.id
            LEFT JOIN users i ON co.instructor_id = i.id
            WHERE {$whereClause}
            LIMIT 1
        ", $params);
        
        if (!$certificate) {
            $error = 'Certificate not found. Please check the verification code or certificate number and try again.';
        }
    } else {
        $error = 'Please enter a verification code or certificate number.';
    }
}

$page_title = 'Verify Certificate';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        
        <!-- Page Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-primary-100 rounded-full mb-4">
                <i class="fas fa-certificate text-primary-600 text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-3">Certificate Verification</h1>
            <p class="text-lg text-gray-600">
                Verify the authenticity of TEVETA certificates issued by <?= APP_NAME ?>
            </p>
        </div>
        
        <!-- Search Form -->
        <?php if (!$certificate): ?>
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Enter Certificate Details</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl mr-3"></i>
                    <p class="text-red-800"><?= sanitize($error) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Certificate Number
                    </label>
                    <input type="text" name="certificate_number" 
                           value="<?= sanitize($certificateNumber ?? $_POST['certificate_number'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="e.g., CERT-2024-001">
                </div>
                
                <div class="text-center text-sm text-gray-500 font-medium">OR</div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Verification Code
                    </label>
                    <input type="text" name="verification_code" 
                           value="<?= sanitize($verificationCode ?? $_POST['verification_code'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="e.g., ABC123XYZ">
                    <p class="text-xs text-gray-500 mt-2">
                        The verification code can be found at the bottom of the certificate
                    </p>
                </div>
                
                <button type="submit" 
                        class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 font-semibold transition">
                    <i class="fas fa-search mr-2"></i>Verify Certificate
                </button>
            </form>
        </div>
        <?php else: ?>
        
        <!-- Verification Result -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
            
            <!-- Success Header -->
            <div class="bg-green-600 text-white p-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-3">
                    <i class="fas fa-check-circle text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold mb-2">Certificate Verified!</h2>
                <p class="text-green-100">This is an authentic certificate issued by <?= APP_NAME ?></p>
            </div>
            
            <!-- Certificate Details -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Student Name</h3>
                            <p class="text-lg font-semibold text-gray-900">
                                <?= sanitize($certificate['student_first_name'] . ' ' . $certificate['student_last_name']) ?>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Course Completed</h3>
                            <p class="text-lg font-semibold text-gray-900">
                                <?= sanitize($certificate['course_title']) ?>
                            </p>
                            <?php if ($certificate['category_name']): ?>
                            <p class="text-sm text-gray-600 mt-1">
                                <?= sanitize($certificate['category_name']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Instructor</h3>
                            <p class="text-gray-900">
                                <?= sanitize(trim($certificate['instructor_first_name'] . ' ' . $certificate['instructor_last_name'])) ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Certificate Number</h3>
                            <p class="text-lg font-mono font-semibold text-gray-900">
                                <?= sanitize($certificate['certificate_number']) ?>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Issue Date</h3>
                            <p class="text-gray-900">
                                <?= formatDate($certificate['issue_date'], 'F j, Y') ?>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Completion Date</h3>
                            <p class="text-gray-900">
                                <?= formatDate($certificate['completion_date'], 'F j, Y') ?>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Final Score</h3>
                            <p class="text-lg font-semibold text-green-600">
                                <?= $certificate['final_score'] ?>%
                            </p>
                        </div>
                    </div>
                    
                </div>
                
                <!-- TEVETA Badge -->
                <?php if ($certificate['is_teveta']): ?>
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-secondary-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-certificate text-secondary-600 text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">TEVETA Certified Course</p>
                            <p class="text-sm text-gray-600">
                                This course is registered with the Technical Education, Vocational and Entrepreneurship Training Authority (TEVETA)
                            </p>
                            <?php if ($certificate['teveta_certificate_number']): ?>
                            <p class="text-sm text-gray-900 mt-1">
                                TEVETA Certificate: <span class="font-mono"><?= sanitize($certificate['teveta_certificate_number']) ?></span>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Course Duration -->
                <?php if ($certificate['duration_hours']): ?>
                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center space-x-2 text-gray-700">
                        <i class="fas fa-clock"></i>
                        <span class="font-medium">Course Duration:</span>
                        <span><?= $certificate['duration_hours'] ?> hours</span>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
        </div>
        
        <!-- Actions -->
        <div class="text-center space-x-4">
            <a href="<?= url('verify-certificate.php') ?>" 
               class="inline-block px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-search mr-2"></i>Verify Another Certificate
            </a>
            <button onclick="window.print()" 
                    class="inline-block px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                <i class="fas fa-print mr-2"></i>Print Verification
            </button>
        </div>
        
        <?php endif; ?>
        
        <!-- Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
            <h3 class="font-bold text-blue-900 mb-3">
                <i class="fas fa-info-circle mr-2"></i>About Certificate Verification
            </h3>
            <ul class="text-sm text-blue-800 space-y-2">
                <li><i class="fas fa-check text-blue-600 mr-2"></i>All certificates issued by <?= APP_NAME ?> can be verified using this page</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Verification confirms the certificate is authentic and has not been altered</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>TEVETA-certified courses meet national training standards</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>If you suspect a certificate is fraudulent, please contact us immediately</li>
            </ul>
        </div>
        
        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="font-bold text-gray-900 mb-4">Need Help?</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-envelope text-primary-600 mt-1"></i>
                    <div>
                        <p class="font-medium text-gray-900">Email</p>
                        <a href="mailto:<?= SITE_EMAIL ?>" class="text-primary-600 hover:text-primary-700">
                            <?= SITE_EMAIL ?>
                        </a>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-phone text-primary-600 mt-1"></i>
                    <div>
                        <p class="font-medium text-gray-900">Phone</p>
                        <a href="tel:<?= SITE_PHONE ?>" class="text-primary-600 hover:text-primary-700">
                            <?= SITE_PHONE ?>
                        </a>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-map-marker-alt text-primary-600 mt-1"></i>
                    <div>
                        <p class="font-medium text-gray-900">Address</p>
                        <p class="text-gray-600"><?= SITE_ADDRESS ?></p>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>